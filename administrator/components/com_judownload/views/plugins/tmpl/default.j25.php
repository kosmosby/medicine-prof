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

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root(true).'/administrator/components/com_judownload/assets/fix_j25/fix.bootstrap.css');

$user = JFactory::getUser();
$userId = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$filterType = $this->escape($this->state->get('filter.type'));
$sortFields = $this->getSortFields();
?>
<script type="text/javascript">

    jQuery(document).ready(function($){

	var taskSubmit = null;

	Joomla.submitbutton = function(task)
	{
		if (task == 'plugins.delete')
		{
			taskSubmit = task;
			var pluginIDs = [];

			jQuery("#adminForm input:checkbox:checked[id^='cb']").map(function(){
				pluginIDs.push(jQuery(this).val());
			});

			var objPost = {};
			objPost.pluginIDs = pluginIDs;

			jQuery.ajax({
				type: "POST",
				url : "index.php?option=com_judownload&task=plugins.checkDeleteTemplateHasChild",
				data: objPost
			}).done(function (data) {
				var data = jQuery.parseJSON(data);

				if (data) {
					if(data.status == 1){
						jQuery('#alertTemplateModal .modal-body').html(data.message);
						jQuery('#alertTemplateModal').modal();
					}else{
						Joomla.submitform(task, document.getElementById('adminForm'));
					}
				}
			});

		}else{
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};


		$('#confirmDeleteTemplate').on('click',function(e){
			e.preventDefault();
			var task = taskSubmit;
			Joomla.submitform(task, document.getElementById('adminForm'));
		});
	});

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
</script>

<div class="jubootstrap">
	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<form action="<?php echo JRoute::_('index.php?option=com_judownload&view=plugins'); ?>" method="post" name="adminForm" id="adminForm">

	<div id="alertTemplateModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="alertTemplateModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="alertTemplateModalLabel"><?php echo JText::_('COM_JUDOWNLOAD_DELETE_TEMPLATE_WARNING'); ?></h3>
		</div>
		<div class="modal-body">

		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_JUDOWNLOAD_CLOSE'); ?></button>
			<button id="confirmDeleteTemplate" class="btn btn-primary"><?php echo JText::_('COM_JUDOWNLOAD_CONFIRM_DELETE'); ?></button>
		</div>
	</div>

		<fieldset id="filter-bar">
			<div class="filter-search input-append pull-left">
				<label for="filter_search" class="filter-search-lbl element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
				<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FILTER_SEARCH'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					title="<?php echo JText::_('COM_JUDOWNLOAD_FILTER_SEARCH_DESC'); ?>" />
				<button class="btn" rel="tooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button class="btn" rel="tooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>

			<div class="filter-select">
				<div class="pull-right hidden-phone">
					<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
					<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
						<option value="asc" <?php if ($listDirn == 'asc')
						{
							echo 'selected="selected"';
						} ?>><?php echo JText::_('COM_JUDOWNLOAD_ASC'); ?></option>
						<option value="desc" <?php if ($listDirn == 'desc')
						{
							echo 'selected="selected"';
						} ?>><?php echo JText::_('COM_JUDOWNLOAD_DESC'); ?></option>
					</select>
				</div>

				<div class="pull-right">
					<label for="sortTable" class="element-invisible"><?php echo JText::_('COM_JUDOWNLOAD_SORT_BY'); ?></label>
					<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SORT_BY'); ?></option>
						<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
					</select>
				</div>

				<div class="pull-right hidden-phone">
					<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_FILTER_TYPE'); ?></label>
					<select name="filter_type" id="filter_type" class="input-medium" onchange="Joomla.submitform()">
						<option value=""><?php echo JText::_('COM_JUDOWNLOAD_FILTER_TYPE'); ?></option>
						<option value="field" <?php if ($filterType == 'field')
						{
							echo 'selected="selected"';
						} ?>><?php echo JText::_('COM_JUDOWNLOAD_FILTER_FIELD'); ?></option>
						<option value="template" <?php if ($filterType == 'template')
						{
							echo 'selected="selected"';
						} ?>><?php echo JText::_('COM_JUDOWNLOAD_FILTER_TEMPLATE'); ?></option>
						<option value="plugin" <?php if ($filterType == 'plugin')
						{
							echo 'selected="selected"';
						} ?>><?php echo JText::_('COM_JUDOWNLOAD_FILTER_PLUGIN'); ?></option>
					</select>
				</div>
			</div>
		</fieldset>

		<div class="clearfix"></div>

		<table class="table table-striped adminlist">
			<thead>
			<tr>
				<th style="width:2%" class="center hidden-phone">
					<input type="checkbox" onclick="Joomla.checkAll(this)" title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value="" name="checkall-toggle" />
				</th>
				<th style="width:15%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TITLE', 'plg.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TYPE', 'plg.type', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_AUTHOR', 'plg.author', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_EMAIL', 'plg.email', $listDirn, $listOrder); ?>
				</th>
				<th style="width:15%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_WEBSITE', 'plg.website', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_DATE', 'plg.date', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_VERSION', 'plg.version', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_FOLDER', 'plg.folder', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_CORE', 'plg.core', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_DEFAULT', 'plg.default', $listDirn, $listOrder); ?>
				</th>
				<th style="width:3%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'plg.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<td colspan="12"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
			</tfoot>

			<tbody>
			<?php foreach ($this->items AS $i => $item):
				$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0;
				$canEdit    = $user->authorise('core.edit',       'com_judownload') && $this->groupCanDoManage;
				$canChange  = $user->authorise('core.edit.state', 'com_judownload') && $canCheckin && $this->groupCanDoManage;
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center hidden-phone">
						<?php
						if (!$item->default)
						{
							echo '<input type="checkbox"  id="cb' . $i . '" name="cid[]" value="' . $item->id
								. '" onclick="Joomla.isChecked(this.checked);" title="' . JText::sprintf('JGRID_CHECKBOX_ROW_N', ($i + 1)) . '" />';
						}
						else
						{
							echo '<input disabled type="checkbox" name="cid[]" value="' . $item->id
								. '" onclick="Joomla.isChecked(this.checked);" title="' . JText::sprintf('JGRID_CHECKBOX_ROW_N', ($i + 1)) . '" />';
						}
						?>
					</td>
					<td>
						<?php
						if ($item->checked_out)
						{
							echo JHtml::_('jgrid.checkedout', $i, $item->checked_out_name, $item->checked_out_time, 'plugins.', $canCheckin || $user->authorise('core.manage', 'com_checkin'));
						}
						?>
						<?php if ($canEdit)
						{
							?>
							<a href="<?php echo JRoute::_('index.php?option=com_judownload&task=plugin.edit&id=' . $item->id); ?>"><?php echo $item->title; ?></a>
						<?php
						}
						else
						{
							echo $item->title;
						} ?>
					</td>
					<td>
						<?php echo $item->type; ?>
					</td>
					<td>
						<?php echo $item->author; ?>
					</td>
					<td>
						<?php echo $item->email; ?>
					</td>
					<td>
						<?php echo $item->website; ?>
					</td>
					<td>
						<?php echo $item->date; ?>
					</td>
					<td>
						<?php echo $item->version; ?>
					</td>
					<td>
						<?php echo $item->folder; ?>
					</td>
					<td class="center">
						<?php echo JHtml::_("grid.boolean", $i, $item->core); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_("grid.boolean", $i, $item->default); ?>
					</td>
					<td>
						<?php echo $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>