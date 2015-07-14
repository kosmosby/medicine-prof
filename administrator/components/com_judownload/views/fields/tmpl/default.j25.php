<?php
/**
 * ------------------------------------------------------------------------
 * JUDownload for Joomla 2.5, 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2015 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$category = $this->escape($this->state->get('filter.category'));
$sortFields = $this->getSortFields();
$saveOrder = ($listOrder == 'field.ordering');
$priority = ($listOrder == 'field.priority');
$backend_list_view_ordering = ($listOrder == 'field.backend_list_view_ordering');
$ordering = ($listOrder == 'field.ordering');
?>
<script type="text/javascript">
	Joomla.orderTable = function () {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}

	function changeValue(self, id, column, value) {
		jQuery.ajax({
			type: "POST",
			url : "<?php echo JUri::Base().'index.php?option=com_judownload&tmpl=component&task=fields.changeValue'; ?>",
			data: "id=" + id + "&column=" + column + "&value=" + value + "&<?php echo JSession::getFormToken(); ?>=1"
		})
			.done(function (msg) {
				el = jQuery(msg)[0];
				var title = el.get('title');
				if (title) {
					var parts = title.split('::', 2);
					el.store('tip:title', parts[0]);
					el.store('tip:text', parts[1]);
				}
				jQuery('body > .tip-wrap').remove();
				JTooltips = new Tips($$(el), {maxTitleChars: 50, fixed: false});
				jQuery(self).parent().html(el);
			});
	}

	function changeBLVorder(self, id, value) {
		jQuery.ajax({
			type: "POST",
			url : "<?php echo JUri::Base().'index.php?option=com_judownload&tmpl=component&task=fields.changeBLVorder'; ?>",
			data: "id=" + id + "&value=" + value + "&<?php echo JSession::getFormToken(); ?>=1"
		})
			.done(function (msg) {
				el = jQuery(msg)[0];
				var title = el.get('title');
				if (title) {
					var parts = title.split('::', 2);
					el.store('tip:title', parts[0]);
					el.store('tip:text', parts[1]);
				}
				jQuery('body > .tip-wrap').remove();
				JTooltips = new Tips($$(el), {maxTitleChars: 50, fixed: false});
				jQuery(self).parent().html(el);
			});
	}

	function changePriorityDirection(self, id, value) {
		jQuery.ajax({
			type: "POST",
			url : "<?php echo JUri::Base().'index.php?option=com_judownload&tmpl=component&task=fields.changePriorityDirection'; ?>",
			data: "id=" + id + "&value=" + value + "&<?php echo JSession::getFormToken(); ?>=1"
		})
			.done(function (msg) {
				el = jQuery(msg)[0];
				var title = el.get('title');
				if (title) {
					var parts = title.split('::', 2);
					el.store('tip:title', parts[0]);
					el.store('tip:text', parts[1]);
				}
				jQuery('body > .tip-wrap').remove();
				JTooltips = new Tips($$(el), {maxTitleChars: 50, fixed: false});
				jQuery(self).parent().html(el);
			});
	}
</script>

<div class="jubootstrap">

<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

<div id="iframe-help"></div>

<form
	action="<?php echo JRoute::_('index.php?option=com_judownload&view=fields'); ?>"
	method="post" name="adminForm" id="adminForm">
<fieldset id="filter-bar">
	<div class="filter-search input-append pull-left">
		<label for="filter_search" class="filter-search-lbl element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
		<input type="text" name="filter_search" id="filter_search" class="input-medium"
			placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FILTER_SEARCH'); ?>"
			value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
			title="<?php echo JText::_('COM_JUDOWNLOAD_FILTER_SEARCH_DESC'); ?>" />
		<button class="btn" rel="tooltip" type="submit"
			title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
		<button class="btn" rel="tooltip" type="button"
			title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
			onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
	</div>

	<div class="filter-select">
		<?php
		    $options = JUDownloadHelper::getFieldGroupOptions(false, false);
			array_unshift($options, array('value' => '', 'text' => JText::_('COM_JUDOWNLOAD_ALL_GROUPS')));
		?>
		<div class="pull-right hidden-phone">
			<?php echo JHtml::_('select.genericlist', $options, 'filter_group', 'onchange="Joomla.submitform();" class="input-medium"', 'value', 'text', $this->escape($this->state->get('filter.group_id', '')), "filter_group"); ?>
		</div>
        <?php
            $options = JUDownloadHelper::getPluginOptions('field', 0);
            array_unshift($options, array('value' => '', 'text' => JText::_('COM_JUDOWNLOAD_SELECT_FIELD_TYPE')));
        ?>
        <div class="pull-right hidden-phone">
            <?php echo JHtml::_('select.genericlist', $options, 'filter_plugin', 'onchange="Joomla.submitform();" class="input-medium"', 'value', 'text', $this->escape($this->state->get('filter.plugin_id', '')), "filter_plugin"); ?>
        </div>
		<div class="pull-right hidden-phone">
			<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
			<select name="directionTable" id="directionTable"
				class="input-medium" onchange="Joomla.orderTable()">
				<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
				<option value="asc"
					<?php
					if ($listDirn == 'asc')
					{
						echo 'selected="selected"';
					}
					?>>
					<?php echo JText::_('COM_JUDOWNLOAD_ASC'); ?>
				</option>
				<option value="desc"
					<?php
					if ($listDirn == 'desc')
					{
						echo 'selected="selected"';
					}
					?>><?php echo JText::_('COM_JUDOWNLOAD_DESC'); ?>
				</option>
			</select>
		</div>

		<div class="pull-right">
			<label for="sortTable" class="element-invisible"><?php echo JText::_('COM_JUDOWNLOAD_SORT_BY'); ?></label>
			<select name="sortTable" id="sortTable" class="input-medium"
				onchange="Joomla.orderTable()">
				<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SORT_BY'); ?></option>
				<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
			</select>
		</div>
	</div>
</fieldset>

<div class="clearfix"></div>

<div style="overflow: auto;">
<table class="table hasSticky table-striped adminlist" style="width: 100%">
<thead>
<tr>
	<th style="min-width: 30px;" class="center hidden-phone"><input type="checkbox"
			onclick="Joomla.checkAll(this)" title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value=""
			name="checkall-toggle" /></th>
	<th style="min-width: 200px !important;" class="nowrap stickycolumn">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_CAPTION', 'field.caption', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: 150px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_GROUP', 'field.group_id', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: 100px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_FIELD_TYPE', 'plg.title', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: 80px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_STATE', 'field.published', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: 80px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_REQUIRED', 'field.required', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: 80px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_LIST_VIEW', 'field.list_view', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: 80px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_DETAILS_VIEW', 'field.details_view', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: 80px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_HIDE_CAPTION', 'field.hide_caption', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: 80px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_SIMPLE_SEARCH', 'field.simple_search', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: 80px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ADVANCED_SEARCH', 'field.advanced_search', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: 80px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_FILTER_SEARCH', 'field.filter_search', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: <?php echo ($saveOrder && $this->groupCanDoManage) ? "150" : "80"; ?>px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ORDERING', 'field.ordering', $listDirn, $listOrder); ?>
		<?php if ($saveOrder && $this->groupCanDoManage) : ?>
			<?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'fields.saveorder'); ?>
		<?php endif; ?>
	</th>
	<th style="min-width: 80px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ALLOW_PRIORITY', 'field.allow_priority', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: <?php echo $priority ? "150" : "90"; ?>px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_PRIORITY', 'field.priority', $listDirn, $listOrder); ?>
		<?php if ($priority) : ?>
			<?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'fields.savepriority'); ?>
		<?php endif; ?>
	</th>
	<th style="min-width: 80px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_PRIORITY_DIRECTION', 'field.priority_direction', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: 80px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_FRONTEND_ORDERING', 'field.frontend_ordering', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: 80px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_BACKEND_LIST_VIEW', 'field.backend_list_view', $listDirn, $listOrder); ?>
	</th>
	<th style="min-width: <?php echo $backend_list_view_ordering ? "150" : "80"; ?>px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_BACKEND_LIST_VIEW_ORDERING', 'field.backend_list_view_ordering', $listDirn, $listOrder); ?>
		<?php if ($backend_list_view_ordering) : ?>
			<?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'fields.saveblvorder'); ?>
		<?php endif; ?>
	</th>
	<th style="min-width: 80px !important;" class="nowrap">
		<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'field.id', $listDirn, $listOrder); ?>
	</th>
</tr>
</thead>

<tfoot>
<tr>
	<td colspan="22"><?php echo $this->pagination->getListFooter(); ?></td>
</tr>
</tfoot>

<tbody>
<?php
foreach ($this->items AS $i => $item) :
	$canCheckin      = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
	$canEdit         = $user->authorise('core.edit', 'com_judownload.field.' . $item->id) && $canCheckin && $this->groupCanDoManage;
	$canEditOwn      = $user->authorise('core.edit.own', 'com_judownload.field.' . $item->id) && $canCheckin && $item->created_by == $userId && $this->groupCanDoManage;
	$canChange       = $user->authorise('core.edit.state', 'com_judownload.field.' . $item->id) && $canCheckin && $this->groupCanDoManage;
	$ignored_options = explode(",", $item->ignored_options);
	?>
	<tr class="row<?php echo $i % 2; ?>">
	<td class="center hidden-phone">
		<?php echo JHtml::_('grid.id', $i, $item->id); ?>
	</td>
	<th>
		<?php if ($item->checked_out) : ?>
			<?php
			echo JHtml::_('jgrid.checkedout', $i, $item->checked_out_name, $item->checked_out_time, 'fields.', $canCheckin || $user->authorise('core.manage', 'com_checkin'));
			?>
		<?php endif; ?>
		<?php if ($canEdit || $canEditOwn)
		{
			?>
			<a href="<?php echo $item->actionlink; ?>"><?php echo $item->caption; ?></a>
		<?php
		}
		else
		{
			?>
			<?php echo $item->caption; ?>
		<?php
		} ?>
		<p class="smallsub"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?></p>
	</th>
	<td>
		<?php
		if(JUDLPROVERSION)
		{
			?>
			<a href="index.php?option=com_judownload&task=fieldgroup.edit&id=<?php echo $item->field_group_id; ?>">
				<?php echo $item->field_group_name; ?>
			</a>
		<?php
		}
		else
		{
			echo $item->field_group_name;
		} ?>
	</td>
	<td>
		<a href="index.php?option=com_judownload&task=plugin.edit&id=<?php echo $item->plugin_id; ?>">
			<?php echo $item->plg_title; ?>
		</a>
	</td>
	<td class="center">
		<?php
		if (in_array("published", $ignored_options))
		{
            echo JHtml::_('jgrid.published', $item->published, $i, 'fields.', false, 'cb', $item->publish_up, $item->publish_down);
		}
		else
		{
            echo JHtml::_('jgrid.published', $item->published, $i, 'fields.', $canChange, 'cb', $item->publish_up, $item->publish_down);
		}
		?>
	</td>
	<td class="center">
		<?php
		if (in_array("required", $ignored_options))
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'required', $item->required, false);
		}
		else
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'required', $item->required, $canEdit || $canEditOwn);
		}
		?>
	</td>
	<td class="center">
		<?php
		if (in_array("list_view", $ignored_options))
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'list_view', $item->list_view, false);
		}
		else
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'list_view', $item->list_view, $canEdit || $canEditOwn);
		}
		?>
	</td>

	<td class="center">
		<?php
		if (in_array("details_view", $ignored_options))
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'details_view', $item->details_view, false);
		}
		else
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'details_view', $item->details_view, $canEdit || $canEditOwn);
		}
		?>
	</td>
	<td class="center">
		<?php
		if (in_array("hide_caption", $ignored_options))
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'hide_caption', $item->hide_caption, false);
		}
		else
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'hide_caption', $item->hide_caption, $canEdit || $canEditOwn);
		}
		?>
	</td>
	<td class="center">
		<?php
		if (in_array("simple_search", $ignored_options))
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'simple_search', $item->simple_search, false);
		}
		else
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'simple_search', $item->simple_search, $canEdit || $canEditOwn);
		}
		?>
	</td>
	<td class="center">
		<?php
		if (in_array("advanced_search", $ignored_options))
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'advanced_search', $item->advanced_search, false);
		}
		else
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'advanced_search', $item->advanced_search, $canEdit || $canEditOwn);
		}
		?>
	</td>
    <td class="center">
		<?php
		if (in_array("filter_search", $ignored_options))
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'filter_search', $item->filter_search, false);
		}
		else
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'filter_search', $item->filter_search, $canEdit || $canEditOwn);
		}
		?>
	</td>
	<td class="order">
		<?php if ($canChange) : ?>
			<?php if ($saveOrder) : ?>
				<?php if ($listDirn == 'asc') : ?>
					<span><?php echo $this->pagination->orderUpIcon($i, ($item->group_id == @$this->items[$i - 1]->group_id), 'fields.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
					<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->group_id == @$this->items[$i + 1]->group_id), 'fields.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
				<?php elseif ($listDirn == 'desc') : ?>
					<span><?php echo $this->pagination->orderUpIcon($i, ($item->group_id == @$this->items[$i - 1]->group_id), 'fields.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
					<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->group_id == @$this->items[$i + 1]->group_id), 'fields.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
				<?php endif; ?>
			<?php endif; ?>
			<?php $disabled = $saveOrder ? '' : 'disabled="disabled"'; ?>
			<input type="text" name="order[]" size="5"
				value="<?php echo $item->ordering; ?>" <?php echo $disabled ?>
				class="text-area-order" />
		<?php else : ?>
			<?php echo $item->ordering; ?>
		<?php endif; ?>
	</td>

	<td class="center">
		<?php
		if (in_array("allow_priority", $ignored_options))
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'allow_priority', $item->allow_priority, false);
		}
		else
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'allow_priority', $item->allow_priority, $canEdit || $canEditOwn);
		}
		?>
	</td>
	<td class="order">
		<?php if ($canEdit || $canEditOwn) : ?>
			<?php if ($priority) : ?>
				<?php if ($listDirn == 'asc') : ?>
					<span><?php echo $this->pagination->orderUpIcon($i, true, 'fields.priorityup', 'JLIB_HTML_MOVE_UP', $priority); ?></span>
					<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'fields.prioritydown', 'JLIB_HTML_MOVE_DOWN', $priority); ?></span>
				<?php elseif ($listDirn == 'desc') : ?>
					<span><?php echo $this->pagination->orderUpIcon($i, true, 'fields.prioritydown', 'JLIB_HTML_MOVE_UP', $priority); ?></span>
					<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'fields.priorityup', 'JLIB_HTML_MOVE_DOWN', $priority); ?></span>
				<?php endif; ?>
			<?php endif; ?>
			<?php $disabled = $priority ? '' : 'disabled="disabled"'; ?>
			<input type="text" name="priority[]" size="5"
				value="<?php echo $item->priority; ?>" <?php echo $disabled ?>
				class="text-area-order" />
		<?php
		else :
			echo $item->priority;
		endif; ?>
	</td>
	<td class="center">
		<?php
		if (in_array("priority_direction", $ignored_options))
		{
			echo JHtml::_('judownloadadministrator.priorityDirection', $item->id, $item->priority_direction, false);
		}
		else
		{
			echo JHtml::_('judownloadadministrator.priorityDirection', $item->id, $item->priority_direction, $canEdit || $canEditOwn);
		}
		?>
	</td>
	<td class="center">
		<?php
		if (in_array("frontend_ordering", $ignored_options))
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'frontend_ordering', $item->frontend_ordering, false);
		}
		else
		{
			echo JHtml::_('judownloadadministrator.changAjaxValue', $item->id, 'frontend_ordering', $item->frontend_ordering, $canEdit || $canEditOwn);
		}
		?>
	</td>
	<td class="center">
		<?php
		if (in_array("backend_list_view", $ignored_options))
		{
			echo JHtml::_('judownloadadministrator.changeAjaxBLVorder', $item->id, $item->backend_list_view, false);
		}
		else
		{
			echo JHtml::_('judownloadadministrator.changeAjaxBLVorder', $item->id, $item->backend_list_view, $canEdit || $canEditOwn);
		}
		?>
	</td>
	<td class="order">
		<?php if ($canEdit || $canEditOwn) : ?>
			<?php if ($backend_list_view_ordering) : ?>
				<?php if ($listDirn == 'asc') : ?>
					<span><?php echo $this->pagination->orderUpIcon($i, true, 'fields.blvup', 'JLIB_HTML_MOVE_UP', $backend_list_view_ordering); ?></span>
					<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'fields.blvdown', 'JLIB_HTML_MOVE_DOWN', $backend_list_view_ordering); ?></span>
				<?php elseif ($listDirn == 'desc') : ?>
					<span><?php echo $this->pagination->orderUpIcon($i, true, 'fields.blvdown', 'JLIB_HTML_MOVE_UP', $backend_list_view_ordering); ?></span>
					<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'fields.blvup', 'JLIB_HTML_MOVE_DOWN', $backend_list_view_ordering); ?></span>
				<?php endif; ?>
			<?php endif; ?>
			<?php $disabled = $backend_list_view_ordering ? '' : 'disabled="disabled"'; ?>
			<input type="text" name="backend_list_view_ordering[]" size="5"
				value="<?php echo $item->backend_list_view_ordering; ?>" <?php echo $disabled ?>
				class="text-area-order" />
		<?php
		else :
			echo $item->backend_list_view_ordering;
		endif; ?>
	</td>
	<td class="center">
		<?php echo $item->id; ?>
	</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>
</div>