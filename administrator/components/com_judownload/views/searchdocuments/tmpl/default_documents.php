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

		<select name="filter_category" class="inputbox" onchange="this.form.submit()">
			<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY'); ?></option>
			<?php
			$options = JUDownloadHelper::getCategoryOptions();
			echo JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.category'));
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

<div style="position:relative;">
	<div style="overflow:auto;">
		<table class="table table-striped table-hover adminlist">
			<thead>
			<tr>
				<th style="min-width: 34px !important;width: 34px !important;max-width:34px;" class="hidden-phone">
					<input type="checkbox" onclick="doc_checkAll(<?php echo count($this->items); ?>)" title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value="" name="doc_toggle" />
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TITLE', 'title', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_CATEGORY', 'cat_id', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_STATE', 'published', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_FEATURED', 'featured', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ACCESS', 'access', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_CREATED_BY', 'created_by', $listDirn, $listOrder); ?>
				</th>
				<th style="width: 5%;">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TOTAL_DOWNLOADS', 'downloads', $listDirn, $listOrder); ?>
				</th>
				<th style="width: 5%;">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_COMMENTS', 'comments', $listDirn, $listOrder); ?>
				</th>
				<th style="width: 5%;">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_REPORTS', 'reports', $listDirn, $listOrder); ?>
				</th>
				<th style="width: 5%;">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_SUBSCRIPTIONS', 'subscriptions', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_LANGUAGE', 'language', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<td colspan="13">
					<div class="search-result clearfix">
						<?php echo $this->pagination->getResultsCounter(); ?>
					</div>
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>

			<tbody>
			<?php
			if ($this->items)
			{
				foreach ($this->items AS $i => $item) :
					$canEdit    = $user->authorise('judl.document.edit', 'com_judownload.document.'.$item->id);
					$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0;
					$canEditOwn = $user->authorise('judl.document.edit.own', 'com_judownload.document.'.$item->id) && $item->created_by == $user->id;
					$canChange  = $canCheckin;
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="hidden-phone">
							<input type="checkbox" onclick="doc_isChecked(this.checked);" value="<?php echo $item->id; ?>" name="documentid[]" id="doc<?php echo $i; ?>" />
						</td>
						<td style="min-width:150px !important;/*position:absolute;left:0;*/">
							<?php if ($item->checked_out) : ?>
								<?php
								$user = JFactory::getUser($item->checked_out);
								echo JHtml::_('jgrid.checkedout', $i, $user->username, $item->checked_out_time, 'documents.', $canCheckin);
								?>
							<?php endif; ?>

							<?php if ($canEdit || $canEditOwn)
							{
								?>
								<a href="<?php echo JRoute::_('index.php?option=com_judownload&task=document.edit&id=' . $item->id); ?>">
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
							<?php echo JUDownloadHelper::generateCategoryPath($item->category_id, " > ", true, true); ?>
						</td>
						<td>
							<?php
							echo JHtml::_('jgrid.published', $item->published, $i, 'documents.', $canChange, 'cb', $item->publish_up, $item->publish_down);
							?>
						</td>
						<td>
							<?php
							echo JHtml::_('judownloadadministrator.featured', $item->featured, $i, $canChange);
							?>
						</td>
						<td>
							<?php echo $item->access_title; ?>
						</td>
						<td>
							<?php echo $item->created_by_name; ?>
						</td>
						<td>
							<?php echo $item->downloads; ?>
						</td>

						<td>
							<a href="index.php?option=com_judownload&view=comments&doc_id=<?php echo $item->id; ?>" title="view comments"><?php echo $item->comments; ?></a>
						</td>
						<td>
							<a href="index.php?option=com_judownload&view=reports&doc_id=<?php echo $item->id; ?>" title="view reports"><?php echo $item->reports; ?></a>
						</td>
						<td>
							<a href="index.php?option=com_judownload&view=subscriptions&doc_id=<?php echo $item->id; ?>" title="view subscriptions"><?php echo $item->subscriptions; ?></a>
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
				<?php endforeach;
			} ?>
			</tbody>
		</table>
	</div>
</div>