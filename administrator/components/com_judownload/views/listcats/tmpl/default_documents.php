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

$app = JFactory::getApplication();
$user = JFactory::getUser();
$userId = $user->id;
$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
$cat_id = $app->input->getInt('cat_id', $rootCat->id);
$listOrder_file = $this->escape($this->state->get('list.ordering'));
$listDirn_file = $this->escape($this->state->get('list.direction'));
$fieldIds = $app->getUserState("com_judownload.listcats." . $cat_id . ".fields", array());
?>
<div class="clearfix">
	<div class="input-append pull-left">
		<label for="filter_search" class="filter-search-lbl element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
		<input type="text" name="filter_search" size="40" id="filter_search"
			placeholder="<?php echo JText::_('COM_JUDOWNLOAD_SEARCH_BY_DOCUMENT_NAME'); ?>"
			value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
			title="<?php echo JText::_('COM_JUDOWNLOAD_SEARCH_BY_DOCUMENT_NAME'); ?>" />
		<button class="btn" rel="tooltip" type="submit"
			title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
		<button class="btn" rel="tooltip" type="button"
			title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
			onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
	</div>

	<div class="pull-right">
		<select name="sortTable" id="sortTable" class="input-medium"
			title="<?php echo JText::_('COM_JUDOWNLOAD_SORT_BY'); ?>"
			onchange="Joomla.orderTable()">
			<option value="default"><?php echo JText::_('COM_JUDOWNLOAD_SORT_BY_DEFAULT'); ?></option>
			<?php echo JHtml::_('select.options', $this->model->dropdown_fields_selected, 'value', 'text', $listOrder_file); ?>
		</select>

		<select name="directionTable" id="directionTable"
			title="<?php echo JText::_('JFIELD_ORDERING_DESC'); ?>"
			class="input-medium" onchange="Joomla.orderTable()">
			<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
			<option value="asc"
				<?php
				if ($listDirn_file == 'asc')
				{
					echo 'selected="selected"';
				}
				?>>
				<?php echo JText::_('COM_JUDOWNLOAD_ASC'); ?>
			</option>
			<option value="desc"
				<?php
				if ($listDirn_file == 'desc')
				{
					echo 'selected="selected"';
				}
				?>>
				<?php echo JText::_('COM_JUDOWNLOAD_DESC'); ?>
			</option>
		</select>

		<?php
			if(JUDownloadHelper::isJoomla3x()){
				echo $this->pagination->getLimitBox();
			}
		?>
	</div>
</div>

<div class="clearfix">
	<div class="custom-layout pull-left">
		<?php
		echo JHtml::_("select.genericlist", $this->model->dropdown_fields, 'fields[]', 'multiple="multiple" title="' . JText::_('COM_JUDOWNLOAD_DISPLAYED_FIELDS') . '"', 'value', 'text', $fieldIds, 'fields');
		?>
		<div class="pull-left">
			<button type="button" class="btn btn-mini" onclick="document.getElementById('apply_layout').value = 1; Joomla.submitform();">
				<i class="icon-ok"></i> <?php echo JText::_('COM_JUDOWNLOAD_APPLY'); ?>
			</button>
			<button type="button" class="btn btn-mini" onclick="document.getElementById('reset_layout').value = 1; Joomla.submitform();">
				<i class="icon-undo"></i> <?php echo JText::_('COM_JUDOWNLOAD_RESET'); ?>
			</button>
			<input type="hidden" name="apply_layout" value="0" id="apply_layout" />
			<input type="hidden" name="reset_layout" value="0" id="reset_layout" />
		</div>
	</div>
</div>

<div id="doc-table-wrapper" style="overflow: auto;">
	<table class="table table-striped adminlist">
		<thead>
		<tr>
			<th style="min-width: 40px !important; width: 40px !important;" class="hidden-phone">
				<input type="checkbox" onclick="doc_checkAll(<?php echo count($this->items); ?>)" title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value="" name="doc_toggle" />
			</th>
			<?php
			foreach ($this->model->fields_use AS $field)
			{
				if ($field->field_name != "")
				{
					switch ($field->field_name)
					{
						case "id":
							echo '<th style="width: 50px !important;" class="nowrap">';
							echo JHtml::_('grid.sort', $field->caption, $field->id, $listDirn_file, $listOrder_file);
							echo '</th>';
							break;
						case "title":
							echo '<th style="min-width: 250px !important;" class="nowrap">';
							echo JHtml::_('grid.sort', $field->caption, $field->id, $listDirn_file, $listOrder_file);
							echo '</th>';
							break;
						case "cat_id":
							echo '<th style="min-width: 200px !important;" class="nowrap">';
							echo JHtml::_('grid.sort', $field->caption, $field->id, $listDirn_file, $listOrder_file);
							echo '</th>';
							break;
						case "description":
						case "comments":
						case "reports":
						case "subscriptions":
							echo '<th style="min-width: 100px !important;" class="nowrap">';
							echo JHtml::_('grid.sort', $field->caption, $field->id, $listDirn_file, $listOrder_file);
							echo '</th>';
							break;
						default:
							echo '<th style="min-width: 80px !important;" class="nowrap">';
							echo JHtml::_('grid.sort', $field->caption, $field->id, $listDirn_file, $listOrder_file);
							echo '</th>';
							break;
					}
				}
				else
				{
					echo '<th style="min-width: 80px !important;" class="nowrap">';
					echo JHtml::_('grid.sort', $field->caption, $field->id, $listDirn_file, $listOrder_file);
					echo '</th>';
				}
			}
			?>
		</tr>
		</thead>

		<tfoot>
		<tr>
			<td colspan="<?php echo count($this->model->fields_use) + 1; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>

		<tbody>
		<?php
		foreach ($this->items AS $i => $item)
		{
			$canEdit    = $user->authorise('judl.document.edit', 'com_judownload.document.' . $item->id) && $this->catGroupCanDoManage;
			$canCheckin = $user->authorise('core.manage','com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
			$canEditOwn = $user->authorise('judl.document.edit.own', 'com_judownload.document.' . $item->id) && $item->created_by == $userId && $this->catGroupCanDoManage;
			$canChange  = $canCheckin && $this->catGroupCanDoManage;
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="hidden-phone">
					<?php if ($item->main)
					{
						?>
						<input type="checkbox" onclick="doc_isChecked(this.checked);" value="<?php echo $item->id; ?>" name="documentid[]" id="doc<?php echo $i; ?>" />
					<?php
					} ?>
				</td>
				<?php
				foreach ($this->model->fields_use AS $field)
				{
					echo '<td>';
					switch ($field->field_name)
					{
						case "title" :
							if (!$item->main)
							{
								
								$main_cat_id = JUDownloadHelper::getDocumentById($item->id)->cat_id;
								?>
								<a href="<?php echo JRoute::_('index.php?option=com_judownload&view=listcats&cat_id=' . $main_cat_id); ?>"><?php echo JUDownloadHelper::generateCategoryPath($main_cat_id); ?> </a> >
							<?php
							}

							if ($item->checked_out)
							{
								echo JHtml::_('jgrid.checkedout', $i, $item->checked_out_name, $item->checked_out_time, 'documents.', $canCheckin || $user->authorise('core.manage', 'com_checkin'), 'doc');
							}

							if ($canEdit || $canEditOwn)
							{
								?>
								<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=document.edit&amp;id=' . $item->id); ?>">
									<?php echo $item->title; ?>
								</a>
							<?php
							}
							else
							{
								echo $item->title;
							}

							if ($this->model->hasDocumentPending($item->id))
							{
								?>
								<span class="has-pending-document"><?php echo JText::_('COM_JUDOWNLOAD_HAS_PENDING_DOCUMENT'); ?></span>
							<?php
							}
							?>
							<p class="<?php echo JUDownloadHelper::isJoomla3x() ? "small" : "smallsub";?>"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?></p>
							<?php
							break;
						case "published" :
							if ($item->main)
							{
								echo JHtml::_('jgrid.published', $item->published, $i, 'documents.', $canChange, 'doc', $item->publish_up, $item->publish_down);
							}
							else
							{
								echo JHtml::_('jgrid.published', $item->published, $i, 'documents.', false, 'doc', $item->publish_up, $item->publish_down);
							}
							break;
						case "featured" :
							if ($item->main)
							{
								
								echo JHtml::_('judownloadadministrator.featured', $item->featured, $i, $canChange, 'documents', 'doc');
							}
							else
							{
								echo JHtml::_('judownloadadministrator.featured', $item->featured, $i, false);
							}
							break;
						default:
							$field = JUDownloadFrontHelperField::getField($field, $item->id);
							echo $field->getBackendOutput();
							break;
					}
					echo '</td>';
				}
				?>
			</tr>
		<?php
		} ?>
		</tbody>
	</table>
</div>