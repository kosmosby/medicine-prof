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
$app = JFactory::getApplication();
$doc_id = $app->input->get("doc_id", "");
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$sortFields = $this->getSortFields();
$ordering = ($listOrder == 'cm.lft');
$saveOrder = ($listOrder == 'cm.lft' && $listDirn == 'asc');
$originalOrders = array();
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

	<?php echo JUDownloadHelper::getMenu($app->input->get('view')); ?>

	<div id="iframe-help"></div>

	<form
		action="<?php echo JRoute::_('index.php?option=com_judownload&view=comments'); ?>"
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

		<table class="table table-striped adminlist">
			<thead>
			<tr>
				<th style="width:2%" class="center hidden-phone"><input type="checkbox"
						onclick="Joomla.checkAll(this)" title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value=""
						name="checkall-toggle" /></th>
				<th style="width:15%">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TITLE', 'cm.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width:15%">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_DOCUMENT_TITLE', 'd.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_USERNAME', 'ua.name', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_EMAIL', 'ua.email', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TOTAL_VOTES', 'cm.total_votes', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_HELPFUL_VOTES', 'cm.helpful_votes', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_CREATED', 'cm.created', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_IP_ADDRESS', 'cm.ip_address', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_PUBLISHED', 'cm.published', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_REPORTS', 'total_reports', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_SUBSCRIPTIONS', 'total_subscriptions', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'cm.lft', $listDirn, $listOrder); ?>
					<?php if ($saveOrder) : ?>
						<?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'comments.saveorder'); ?>
					<?php endif; ?>
				</th>
				<th style="width:3%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'cm.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<td colspan="14"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
			</tfoot>

			<tbody>
			<?php
			foreach ($this->items AS $i => $item) :
				$orderkey   = array_search($item->id, $this->ordering[$item->parent_id]);
				$canEdit    = $user->authorise('core.edit', 'com_judownload') && $this->groupCanDoManage;
				$canEditOwn = $user->authorise('core.edit.own', 'com_judownload') && $item->user_id == $user->id && $this->groupCanDoManage;
				$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0;
				$canChange  = $user->authorise('core.edit.state', 'com_judownload') && $canCheckin && $this->groupCanDoManage;
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>

					<td>
						<?php if ($item->checked_out) : ?>
							<?php
								echo JHtml::_('jgrid.checkedout', $i, $item->checked_out_name, $item->checked_out_time, 'comments.', $canCheckin || $user->authorise('core.manage', 'com_checkin'));
							?>
						<?php endif; ?>
						<?php if ($canEdit || $canEditOwn)
						{
							echo "<span class=\"gi\">" . str_repeat('|—', $item->level - 1) . "</span>";
							?>
							<a href="index.php?option=com_judownload&task=comment.edit&id=<?php echo $item->id; ?>">
								<?php echo $item->title; ?>
							</a>
						<?php
						}
						else
						{
							echo $item->title;
						}?>
					</td>
					<td>
						<a href="index.php?option=com_judownload&task=document.edit&id=<?php echo $item->doc_id; ?>">
							<?php echo $item->document_title; ?>
						</a>
					</td>
					<td>
						<?php
						if ($item->author_name)
						{
							echo $item->author_name;
						}
						else
						{
							echo JText::_('COM_JUDOWNLOAD_GUEST') . ": " . $item->guest_name;
						}
						?>
					</td>
					<td>
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
					<td class="center">
						<?php echo $item->total_votes; ?>
					</td>
					<td class="center">
						<?php echo $item->helpful_votes; ?>
					</td>
					<td>
						<?php echo $item->created; ?>
					</td>
					<td>
						<?php
						if ($item->ip_address)
						{
							echo "<a href='http://whois.domaintools.com/" . $item->ip_address . "' target='_blank'>" . $item->ip_address . "</a>";
						};
						?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'comments.', true, 'cb'); ?>
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
					<td class="order">
						<?php if ($canChange) : ?>
							<?php if ($saveOrder) : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, isset($this->ordering[$item->parent_id][$orderkey - 1]), 'comments.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, isset($this->ordering[$item->parent_id][$orderkey + 1]), 'comments.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php endif; ?>
							<?php $disabled = $saveOrder ? '' : 'disabled="disabled"'; ?>
							<input type="text" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" <?php echo $disabled ?> class="text-area-order" />
							<?php $originalOrders[] = $orderkey + 1; ?>
						<?php else : ?>
							<?php echo $orderkey + 1; ?>
						<?php endif; ?>
					</td>
					<td class="center">
						<?php echo $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>" />
			<input type="hidden" name="doc_id" id="doc_id" value="<?php echo $doc_id; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>