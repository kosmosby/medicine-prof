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
$doc_id = $this->escape($this->state->get('doc_id'));
$filter_author = $this->escape($this->state->get('filter.author'));
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$sortFields = $this->getSortFields();
$saveOrder = $listOrder == 'ordering';
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
</script>

<div class="jubootstrap">
	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<form
		action="<?php echo JRoute::_('index.php?option=com_judownload&view=pendingcomments'); ?>"
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
				<div class="btn-group pull-left">
					<label for="filter-author" class="element-invisible"><?php echo JText::_('COM_JUDOWNLOAD_SORT_BY'); ?></label>
					<select name="filter_author" id="filter-author" class="input-medium"
						onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('COM_JUDOWNLOAD_FILTER_BY_AUTHOR'); ?></option>
						<option value="guest_name" <?php echo $filter_author == "guest_name" ? "selected" : "" ?>><?php echo JText::_('COM_JUDOWNLOAD_GUEST'); ?></option>
						<option value="user_id" <?php echo $filter_author == "user_id" ? "selected" : "" ?>><?php echo JText::_('COM_JUDOWNLOAD_USER'); ?></option>
					</select>
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
							?>>
							<?php echo JText::_('COM_JUDOWNLOAD_DESC'); ?>
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
		<?php
		if (empty($this->items))
		{
			?>
			<div class="alert alert-no-items">
				<?php echo JText::_('COM_JUDOWNLOAD_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php
		}
		else
		{
		?>
		<table class="table table-striped adminlist">
			<thead>
			<tr>
				<th style="width:2%" class="hidden-phone"><input type="checkbox"
						onclick="Joomla.checkAll(this)" title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value=""
						name="checkall-toggle" /></th>
				<th style="width:20%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TITLE', 'cm.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width:15%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_DOCUMENT_TITLE', 'd.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_USERNAME', 'ua.username', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_PARENT', 'cm.parent_id', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_CREATED', 'cm.created', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_REPORTS', 'total_reports', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_SUBSCRIPTIONS', 'total_subscriptions', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JText::_('COM_JUDOWNLOAD_FIELD_APPROVE_COMMENT'); ?>
				</th>
				<th style="width:3%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'cm.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
			</tfoot>

			<tbody>
			<?php
			$ordering = ($listOrder == 'ordering');
			foreach ($this->items AS $i => $item) :
				$canEdit    = $user->authorise('core.edit',       'com_judownload') && $this->groupCanDoManage;
				$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0;
				$canEditOwn = $user->authorise('core.edit.own',   'com_judownload') && $item->user_id == $user->id && $this->groupCanDoManage;
				$canChange  = $user->authorise('core.edit.state', 'com_judownload') && $canCheckin && $this->groupCanDoManage;
				$author     = $item->author_name ? $item->author_name : JText::_('COM_JUDOWNLOAD_GUEST') . ": " . $item->guest_name;
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>

					<td>
						<?php if ($item->checked_out) : ?>
							<?php
							echo JHtml::_('jgrid.checkedout', $i, $item->checked_out_name, $item->checked_out_time, 'pendingcomments.', $canCheckin || $user->authorise('core.manage', 'com_checkin'));
							?>
						<?php endif; ?>
						<?php if ($canEdit || $canEditOwn)
						{
							?>
							<a href="<?php echo JRoute::_('index.php?option=com_judownload&task=comment.edit&id=' . $item->id . '&approve=1'); ?>"><?php echo $item->title; ?></a>
						<?php
						}
						else
						{
							echo $item->title;
						}
						?>
					</td>
					<td>
						<?php if ($item->document_id)
						{
							echo '<a href="index.php?option=com_judownload&task=document.edit&id=' . $item->document_id . '" >' . $item->document_title . '</a>';
						}
						?>
					</td>
					<td>
						<?php echo $author; ?>
					</td>
					<td>
						<?php echo $item->parent; ?>
					</td>
					<td class="center">
						<?php echo $item->created; ?>
					</td>
					<td class="center">
						<?php
						if ($item->total_reports)
						{
							echo '<a href="index.php?option=com_judownload&view=reports&comment_id=' . $item->id . '" title="' . JText::_("COM_JUDOWNLOAD_VIEW_REPORTS") . '">' . JText::plural("COM_JUDOWNLOAD_N_REPORTS", $item->total_reports) . '</a>';
						} ?>
					</td>
					<td class="center">
						<?php
						if ($item->total_subscriptions)
						{
							echo '<a href="index.php?option=com_judownload&view=subscriptions&comment_id=' . $item->id . '" title="' . JText::_("COM_JUDOWNLOAD_VIEW_SUBSCRIPTIONS") . '">' . JText::plural("COM_JUDOWNLOAD_N_SUBSCRIPTIONS", $item->total_subscriptions) . '</a>';
						} ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('judownloadadministrator.commentApproved', $item->approved, $i, $canChange, 'pendingcomments'); ?>
					</td>
					<td class="center">
						<?php echo $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		} ?>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<input type="hidden" name="doc_id" id="doc_id" value="<?php echo $doc_id; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>