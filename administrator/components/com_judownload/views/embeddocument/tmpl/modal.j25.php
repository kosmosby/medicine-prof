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
JHtml::_('script', 'system/multiselect.js', false, true);

$app = JFactory::getApplication();
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$field_display = $this->state->get('field_display', array('title', 'icon', 'introtext', 'tag'));
$function   = $app->input->get('function', 'jSelectFile');
$ename      = $app->input->get('e_name', 'jform_articletext', 'string');
?>
<div class="jubootstrap">

	<div id="iframe-help"></div>

	<div class="judownload-manager">
		<form action="<?php echo JRoute::_('index.php?option=com_judownload&view=embeddocument&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1'); ?>"
			method="post" name="adminForm" id="adminForm">
			<div class="clearfix">
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
				<div class="filter-select pull-left">
					<select style="margin-left: 0" name="filter_featured" class="input-medium" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SELECT_FEATURED'); ?></option>
						<option value="1" <?php echo $this->state->get('filter.featured') === '1' ? "selected" : ""; ?>><?php echo JText::_('COM_JUDOWNLOAD_FEATURED'); ?></option>
						<option value="0" <?php echo $this->state->get('filter.featured') === '0' ? "selected" : ""; ?>><?php echo JText::_('COM_JUDOWNLOAD_UNFEATURED'); ?></option>
					</select>

					<select name="filter_catid" class="input-medium" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SELECT_CATEGORY'); ?></option>
						<?php
						$options = JUDownloadHelper::getCategoryOptions();
						echo JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.catid'));
						?>
					</select>

					<select name="filter_licenseid" class="input-medium" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SELECT_LICENSE'); ?></option>
						<?php
						$options = JUDownloadHelper::getLicenseOptions();
						echo JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.license'));
						?>
					</select>

					<select name="filter_access" class="input-medium" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS'); ?></option>
						<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access')); ?>
					</select>
				</div>
			</div>

			<div class="filter-select row-fluid" id="options">
				<div class="filter-select row-fluid" id="options">
					<div class="span4">
						<label class="checkbox"><input type="checkbox" name="link_icon" checked="checked" id="link-icon" value="" /><?php echo JText::_('COM_JUDOWNLOAD_LINK_ICON'); ?></label>
						<label class="checkbox"><input type="checkbox" name="link_title" checked="checked" id="link-title" value="" /><?php echo JText::_('COM_JUDOWNLOAD_LINK_TITLE'); ?></label>
						<a class="btn" name="insert" id="insert" onclick="return InsertDocument('<?php echo $ename; ?>');"><?php echo JText::_('COM_JUDOWNLOAD_INSERT_ALL_SELECTED'); ?></a>
					</div>
					<div class="span4">
						<div class="control-group" >
							<div class="controls" >
								<?php
								echo JHtml::_('select.genericlist', $this->getFieldDisplay(), 'field_display[]',
									'class="inputbox hasTooltip"  multiple="multiple" style="width: 100%" title="'.JText::_('COM_JUDOWNLOAD_DISPLAYED_FIELDS').'"',
									'value', 'text', $field_display, 'field_display');
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="clr"></div>

			<table class="adminlist" id="document-list">
				<thead>
				<tr>
					<th style="width:2%" class="center hidden-phone">
						<input type="checkbox" onclick="Joomla.checkAll(this)" title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value="" name="checkall-toggle" />
					</th>
					<th class="title">
						<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TITLE', 'd.title', $listDirn, $listOrder); ?>
					</th>
					<th style="width:20%">
						<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ACCESS', 'd.access', $listDirn, $listOrder); ?>
					</th>
					<th style="width:15%">
						<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_CATEGORY', 'c.title', $listDirn, $listOrder); ?>
					</th>
					<th style="width:15%">
						<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_CREATED', 'd.created', $listDirn, $listOrder); ?>
					</th>
					<th style="width: 1%;" class="nowrap">
						<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'd.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>

				<tfoot>
				<tr>
					<td colspan="6">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
				</tfoot>

				<tbody>
				<?php foreach ($this->items AS $i => $item) : ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center hidden-phone"><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
						<td>
							<label for="cb<?php echo $i; ?>"><?php echo $this->escape($item->title); ?></label>
						</td>
						<td class="center">
							<?php echo $this->escape($item->access_title); ?>
						</td>
						<td class="center">
							<?php echo $this->escape($item->category_title); ?>
						</td>
						<td class="center nowrap">
							<?php echo JHtml::_('date', $item->created, 'Y-m-d H:i:s'); ?>
						</td>

						<td class="center">
							<?php echo (int) $item->id; ?>
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
			<br />
			<a class="btn" name="insert" id="insert" onclick="return InsertDocument('<?php echo $ename; ?>');"><?php echo JText::_('COM_JUDOWNLOAD_INSERT_ALL_SELECTED'); ?></a>
		</form>
	</div>
</div>