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
$ignore = $app->input->getInt("id", 0);
$doc_id = $app->input->getInt("doc_id", 0);
JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('script', 'system/multiselect.js', false, true);

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$function = $app->input->get('function', 'jSelectDocument');
?>
<div class="judownload-manager">
	<form action="<?php echo JRoute::_('index.php?option=com_judownload&&view=comments&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1'); ?>"
		method="post" name="adminForm" id="adminForm">
		<fieldset id="filter-bar">
			<div class="filter-search fltlft">
				<label class="filter-search-lbl element-invisible" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
				<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FILTER_SEARCH'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					title="<?php echo JText::_('COM_JUDOWNLOAD_FILTER_SEARCH'); ?>" />
				<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
				<button type="button" onclick="if (window.parent) window.parent.<?php echo $function; ?>(0 , '<?php echo $this->rootComment->title; ?>', 0);"><?php echo $this->rootComment->title; ?></button>
			</div>

			<div class="filter-select fltrt">
				<select name="filter_published" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
					<option value="1" <?php echo $this->state->get('filter.published') === '1' ? "selected" : ""; ?>><?php echo JText::_('COM_JUDOWNLOAD_PUBLISHED'); ?></option>
					<option value="0" <?php echo $this->state->get('filter.published') === '0' ? "selected" : ""; ?>><?php echo JText::_('COM_JUDOWNLOAD_UNPUBLISHED'); ?></option>
				</select>
			</div>
		</fieldset>

		<div class="clr"></div>

		<table class="adminlist">
			<thead>
			<tr>
				<th style="width:25%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TITLE', 'cm.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width:20%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_DOCUMENT_TITLE', 'd.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width:20%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_USERNAME', 'ua.name', $listDirn, $listOrder); ?>
				</th>
				<th style="width:20%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_EMAIL', 'ua.email', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TOTAL_VOTES', 'cm.total_votes', $listDirn, $listOrder); ?>
				</th>
				<th style="width:20%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_CREATED', 'cm.created', $listDirn, $listOrder); ?>
				</th>
				<th style="width:3%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'cm.id', $listDirn, $listOrder); ?>
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
					<td>
						<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>', '<?php echo (int) $item->level; ?>');">
							<?php echo $this->escape(str_repeat('-.', $item->level) . $item->title); ?></a>
					</td>
					<td class="center">
						<?php echo($item->document_title); ?>
					</td>
					<td class="center">
						<?php
						if ($item->author_name)
						{
							echo $item->author_name;
						}
						else
						{
							echo $item->guest_name;
						}
						?>
					</td>
					<td class="center">
						<?php
						if ($item->author_email)
						{
							echo $item->author_email;
						}
						else
						{
							echo $item->guest_email;
						}
						?>
					</td>
					<td>
						<?php echo $item->total_votes; ?>
					</td>
					<td>
						<?php echo $item->created; ?>
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
			<input type="hidden" name="doc_id" id="doc_id" value="<?php echo $doc_id; ?>" />
			<input type="hidden" name="id" id="id" value="<?php echo $ignore; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>