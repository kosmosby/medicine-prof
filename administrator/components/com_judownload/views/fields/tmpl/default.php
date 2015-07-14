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
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'field.ordering');
$priority = ($listOrder == 'field.priority');
$backend_list_view_ordering = ($listOrder == 'field.backend_list_view_ordering');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_judownload&task=fields.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'data-list', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<script type="text/javascript">
	var isJoomla3x = <?php echo JUDownloadHelper::isJoomla3x() ? 1 : 0 ?>;
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
				if(isJoomla3x){
					jQuery('body > .tooltip.fade').remove();
					jQuery(el).tooltip({"html": true,"container": "body"});
					jQuery(self).parent().html(el);
				}else{
					var title = el.get('title');
					if (title) {
						var parts = title.split('::', 2);
						el.store('tip:title', parts[0]);
						el.store('tip:text', parts[1]);
					}
					jQuery('body > .tip-wrap').remove();
					JTooltips = new Tips($$(el), {maxTitleChars: 50, fixed: false});
					jQuery(self).parent().html(el);
				}
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
				if(isJoomla3x){
					jQuery('body > .tooltip.fade').remove();
					jQuery(el).tooltip({"html": true,"container": "body"});
					jQuery(self).parent().html(el);
				}else{
					var title = el.get('title');
					if (title) {
						var parts = title.split('::', 2);
						el.store('tip:title', parts[0]);
						el.store('tip:text', parts[1]);
					}
					jQuery('body > .tip-wrap').remove();
					JTooltips = new Tips($$(el), {maxTitleChars: 50, fixed: false});
					jQuery(self).parent().html(el);
				}
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
				if(isJoomla3x){
					jQuery('body > .tooltip.fade').remove();
					jQuery(el).tooltip({"html": true,"container": "body"});
					jQuery(self).parent().html(el);
				}else{
					var title = el.get('title');
					if (title) {
						var parts = title.split('::', 2);
						el.store('tip:title', parts[0]);
						el.store('tip:text', parts[1]);
					}
					jQuery('body > .tip-wrap').remove();
					JTooltips = new Tips($$(el), {maxTitleChars: 50, fixed: false});
					jQuery(self).parent().html(el);
				}
			});
	}
</script>

<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

<div id="iframe-help"></div>

<form
	action="<?php echo JRoute::_('index.php?option=com_judownload&view=fields'); ?>"
	method="post" name="adminForm" id="adminForm">
<div id="j-main-container" class="span12">
<?php

echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
?>
<?php if (empty($this->items)) : ?>
	<div class="alert alert-no-items">
		<?php echo JText::_('COM_JUDOWNLOAD_NO_MATCHING_RESULTS'); ?>
	</div>
<?php else : ?>
	<div style="overflow: auto;">
	<table class="table hasSticky j3x table-striped adminlist" id="data-list">
	<thead>
	<tr>
		<th style="min-width: 10px !important;" class="nowrap center hidden-phone">
			<?php echo JHtml::_('searchtools.sort', '', 'field.ordering', $listDirn, $listOrder, null, 'asc', 'COM_JUDOWNLOAD_FIELD_ORDERING', 'icon-menu-2'); ?>
		</th>
		<th style="min-width: 10px !important;" class="center hidden-phone">
			<?php echo JHtml::_('grid.checkall'); ?>
		</th>
		<th style="min-width: 200px !important;" class="nowrap stickycolumn">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_CAPTION', 'field.caption', $listDirn, $listOrder); ?>
		</th>
		<th style="min-width: 150px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_GROUP', 'field.group_id', $listDirn, $listOrder); ?>
		</th>
		<th style="min-width: 100px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_FIELD_TYPE', 'plg.title', $listDirn, $listOrder); ?>
		</th>
		<th style="min-width: 80px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_STATE', 'field.published', $listDirn, $listOrder); ?>
		</th>
		<th style="min-width: 80px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_REQUIRED', 'field.required', $listDirn, $listOrder); ?>
		</th>
		<th style="min-width: 80px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_LIST_VIEW', 'field.list_view', $listDirn, $listOrder); ?>
		</th>
		<th style="min-width: 80px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_DETAILS_VIEW', 'field.details_view', $listDirn, $listOrder); ?>
		</th>
		<th style="min-width: 80px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_HIDE_CAPTION', 'field.hide_caption', $listDirn, $listOrder); ?>
		</th>
		<th style="min-width: 80px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_SIMPLE_SEARCH', 'field.simple_search', $listDirn, $listOrder); ?>
		</th>
		<th style="min-width: 80px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_ADVANCED_SEARCH', 'field.advanced_search', $listDirn, $listOrder); ?>
		</th>
        <th style="min-width: 80px !important;" class="nowrap">
            <?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_FILTER_SEARCH', 'field.filter_search', $listDirn, $listOrder); ?>
        </th>
		<th style="min-width: 80px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_ALLOW_PRIORITY', 'field.allow_priority', $listDirn, $listOrder); ?>
		</th>
		<th style="min-width: <?php echo $priority ? "150" : "80"; ?>px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_PRIORITY', 'field.priority', $listDirn, $listOrder); ?>
			<?php if ($priority) : ?>
				<?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'fields.savepriority'); ?>
			<?php endif; ?>
		</th>
		<th style="min-width: 80px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_PRIORITY_DIRECTION', 'field.priority_direction', $listDirn, $listOrder); ?>
		</th>
		<th style="min-width: 80px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_FRONTEND_ORDERING', 'field.frontend_ordering', $listDirn, $listOrder); ?>
		</th>
		<th style="min-width: 80px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_BACKEND_LIST_VIEW', 'field.backend_list_view', $listDirn, $listOrder); ?>
		</th>
		<th style="min-width: <?php echo $backend_list_view_ordering ? "150" : "80"; ?>px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_BACKEND_LIST_VIEW_ORDERING', 'field.backend_list_view_ordering', $listDirn, $listOrder); ?>
			<?php if ($backend_list_view_ordering) : ?>
				<?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'fields.saveblvorder'); ?>
			<?php endif; ?>
		</th>
		<th style="min-width: 80px !important;" class="nowrap">
			<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'field.id', $listDirn, $listOrder); ?>
		</th>
	</tr>
	</thead>

	<tfoot>
	<tr>
		<td colspan="21"><?php echo $this->pagination->getListFooter(); ?></td>
	</tr>
	</tfoot>

	<tbody>
	<?php
	foreach ($this->items AS $i => $item) :
		$canCheckin      = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
		$canEdit         = $user->authorise('core.edit', 'com_judownload.field.' . $item->id) && $this->groupCanDoManage;
		$canEditOwn      = $user->authorise('core.edit.own', 'com_judownload.field.' . $item->id) && $item->created_by == $userId && $this->groupCanDoManage;
		$canChange       = $user->authorise('core.edit.state', 'com_judownload.field.' . $item->id) && $canCheckin && $this->groupCanDoManage;
		$ignored_options = explode(",", $item->ignored_options);
		?>
		<tr class="row<?php echo $i % 2; ?>">
		<td class="order nowrap center hidden-phone">
			<?php
			$iconClass = '';
			if (!$canChange)
			{
				$iconClass = ' inactive';
			}
			elseif (!$saveOrder)
			{
				$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
			}
			?>
			<span class="sortable-handler<?php echo $iconClass ?>">
										<i class="icon-menu"></i>
									</span>
			<?php if ($canChange && $saveOrder) : ?>
				<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
			<?php endif; ?>
		</td>
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
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=field.edit&amp;id=' . $item->id); ?>">
					<?php echo $item->caption; ?>
				</a>
			<?php
			}
			else
			{
				?>
				<?php echo $item->caption; ?>
			<?php
			} ?>
			<p class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?></p>
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
		<td>
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
		<td>
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
		<td>
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
		<td>
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
		<td>
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
		<td>
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
		<td>
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
        <td>
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
		<td>
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
					<input type="text" name="priority[]" size="5"
						value="<?php echo $item->priority; ?>" class="text-area-order" />
				<?php else : ?>
					<?php echo $item->priority; ?>
				<?php endif; ?>
			<?php
			else :
				echo $item->priority;
			endif; ?>
		</td>
		<td>
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
		<td>
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
		<td>
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
					<input type="text" name="priority[]" size="5"
						value="<?php echo $item->backend_list_view_ordering; ?>" class="text-area-order" />
				<?php else : ?>
					<?php echo $item->backend_list_view_ordering; ?>
				<?php endif; ?>
			<?php
			else :
				echo $item->backend_list_view_ordering;
			endif; ?>
		</td>
		<td>
			<?php echo $item->id; ?>
		</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
	</div>
<?php endif; ?>

<div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</div>
</form>