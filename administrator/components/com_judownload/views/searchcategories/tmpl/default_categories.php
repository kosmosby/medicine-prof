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

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

?>
<fieldset id="filter-bar">
	<div class="filter-select fltrt">
		<select name="filter_state" class="inputbox" onchange="this.form.submit()">
			<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
			<?php
			$optionsStatus = array();
			$optionsStatus[] = JHtml::_('select.option', '1', 'JPUBLISHED');
			$optionsStatus[] = JHtml::_('select.option', '0', 'JUNPUBLISHED');
			$optionsStatus[] = JHtml::_('select.option', '*', 'JALL');
			?>
			<?php echo JHtml::_('select.options', $optionsStatus, 'value', 'text', $this->state->get('filter.state'), true); ?>
		</select>

		<select name="filter_parent" class="inputbox" onchange="this.form.submit()">
			<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SELECT_PARENT_CATEGORY'); ?></option>
			<?php
			$options = JUDownloadHelper::getCategoryOptions();
			echo JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.parent'));
			?>
		</select>

		<select name="filter_access" class="inputbox" onchange="this.form.submit()">
			<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS'); ?></option>
			<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access')); ?>
		</select>
		<select name="filter_language" class="inputbox" onchange="this.form.submit()">
			<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?></option>
			<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language')); ?>
		</select>
	</div>
</fieldset>

<div class="clearfix"></div>

<div style="position: relative;">
	<div style="overflow: auto;">
		<table class="table table-striped table-hover adminlist">
			<thead>
			<tr>
				<th style="min-width: 50px !important;width: 50px !important;max-width:50px;" class="hidden-phone">
					<input type="checkbox" onclick="Joomla.checkAll(this)" title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value="" name="checkall-toggle" />
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TITLE', 'c.title', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_PARENT', 'c.parent_id', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ACCESS', 'c.access', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_FIELDGROUP_ID', 'c.fieldgroup_id', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TOTAL_DOCUMENTS', 'total_documents', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_FEATURED', 'c.featured', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_STATE', 'c.published', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_LANGUAGE', 'c.language', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'c.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<td colspan="10">
					<div class="search-result clearfix">
						<?php echo $this->pagination->getResultsCounter(); ?>
					</div>
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>

			<tbody>
			<?php
			foreach ($this->items AS $i => $item) :
				$canEdit    = $user->authorise('judl.category.edit',       'com_judownload.category.'.$item->id);
				$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0;
				$canEditOwn = $user->authorise('judl.category.edit.own',   'com_judownload.category.'.$item->id) && $item->created_by == $user->id;
				$canChange  = $user->authorise('judl.category.edit.state', 'com_judownload.category.'.$item->id) && $canCheckin;
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						<a href="index.php?option=com_judownload&view=listcats&cat_id=<?php echo $item->id; ?>"><img width="18" height="18" border="0" onmouseout="this.src='<?php echo JUri::root(); ?>components/com_judownload/assets/dtree/img/folder.gif'" onmouseover="this.src='<?php echo JUri::root(); ?>components/com_judownload/assets/dtree/img/folderopen.gif'" name="img0" src="<?php echo JUri::root(); ?>components/com_judownload/assets/dtree/img/folder.gif" style="float:left" /></a>

					</td>
					<td style="min-width:310px !important;">
						<?php if ($item->checked_out) : ?>
							<?php
							$user = JFactory::getUser($item->checked_out);
							echo JHtml::_('jgrid.checkedout', $i, $user->username, $item->checked_out_time, 'categories.', $canCheckin);
							?>
						<?php endif; ?>

						<?php if ($canEdit || $canEditOwn)
						{
							?>
							<a href="<?php echo 'index.php?option=com_judownload&amp;task=category.edit&amp;id=' . $item->id; ?>">
								<?php echo $item->title; ?>
							</a>
						<?php
						}
						else
						{
							?>
							<?php echo $item->title; ?>
						<?php
						} ?>
						<p class="<?php echo JUDownloadHelper::isJoomla3x() ? "small" : "smallsub";?>"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?></p>
					</td>

					<td>
						<?php echo JUDownloadHelper::generateCategoryPath($item->parent_id, " > ", true, true); ?>
					</td>
					<td>
						<?php echo $item->access; ?>
					</td>
					<td>
						<?php echo '<a href="index.php?option=com_judownload&task=fieldgroup.edit&id=' . $item->fieldgroup_id . '">' . $item->fieldgroup_name . '</a>'; ?>
					</td>
					<td>
						<?php echo (int) $item->total_documents; ?>
					</td>
					<td>
						<?php
						echo JHtml::_('judownloadadministrator.featured', $item->featured, $i, $canChange, 'category');
						?>
					</td>
					<td>
						<?php
						echo JHtml::_('jgrid.published', $item->published, $i, 'categories.', $canChange, 'cb', $item->publish_up, $item->publish_down);
						?>
					</td>

					<td>
						<?php
						echo $item->language;
						?>
					</td>
					<td>
						<?php echo $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>