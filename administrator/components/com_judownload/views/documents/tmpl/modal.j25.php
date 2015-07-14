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

JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$app = JFactory::getApplication();
$function = $app->input->get('function', 'jSelectDocument');
?>
<div class="jubootstrap">
	<form action="<?php echo JRoute::_('index.php?option=com_judownload&&view=documents&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1'); ?>"
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
				<div class="pull-right hidden-phone">
					<select name="filter_published" class="input-medium" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
						<option value="1" <?php echo $this->state->get('filter.published') === '1' ? "selected" : ""; ?>><?php echo JText::_('JPUBLISHED'); ?></option>
						<option value="0" <?php echo $this->state->get('filter.published') === '0' ? "selected" : ""; ?>><?php echo JText::_('JUNPUBLISHED'); ?></option>
					</select>
				</div>

				<div class="pull-right hidden-phone">
					<select name="filter_featured" class="input-medium" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SELECT_FEATURED'); ?></option>
						<option value="1" <?php echo $this->state->get('filter.featured') === '1' ? "selected" : ""; ?>><?php echo JText::_('COM_JUDOWNLOAD_FEATURED'); ?></option>
						<option value="0" <?php echo $this->state->get('filter.featured') === '0' ? "selected" : ""; ?>><?php echo JText::_('COM_JUDOWNLOAD_UNFEATURED'); ?></option>
					</select>
				</div>

				<div class="pull-right hidden-phone">
					<select name="filter_catid" class="input-medium" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SELECT_CATEGORY'); ?></option>
						<?php
						$options = JUDownloadHelper::getCategoryOptions();
						echo JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.category'));
						?>
					</select>
				</div>

				<div class="pull-right hidden-phone">
					<select name="filter_licenseid" class="input-medium" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SELECT_LICENSE'); ?></option>
						<?php
						$options = JUDownloadHelper::getLicenseOptions();
						echo JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.license'));
						?>
					</select>
				</div>

				<div class="pull-right hidden-phone">
					<select name="filter_access" class="input-medium" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS'); ?></option>
						<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access')); ?>
					</select>
				</div>
			</div>
		</fieldset>

		<div class="clr"></div>

		<table class="table table-striped adminlist">
			<thead>
			<tr>
				<th style="width: 25%">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TITLE', 'd.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width: 15%">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_CATEGORY', 'c.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width: 15%">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_LICENSE', 'l.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width: 15%">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_CREATED_BY', 'd.created_by', $listDirn, $listOrder); ?>
				</th>
				<th style="width: 10%">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ACCESS', 'd.access', $listDirn, $listOrder); ?>
				</th>
				<th style="width: 15%">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_CREATED', 'd.created', $listDirn, $listOrder); ?>
				</th>
				<th style="width: 5%;" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'd.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>

			<tfoot>
				<tr>
					<td colspan="7">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>

			<tbody>
			<?php foreach ($this->items AS $i => $item) : ?>

				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes(str_replace(array("\r", "\n"), "", $item->title)));?>', '<?php echo $item->icon;?>');">
							<?php echo $this->escape($item->title); ?>
						</a>
					</td>
					<td class="center">
						<?php echo $this->escape($item->category_title); ?>
					</td>
					<td class="center">
						<?php echo $this->escape($item->license_title); ?>
					</td>
					<td class="center">
						<?php echo $this->escape($item->created_by); ?>
					</td>
					<td class="center">
						<?php echo $this->escape($item->access_level); ?>
					</td>
					<td class="center nowrap">
						<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
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
	</form>
</div>