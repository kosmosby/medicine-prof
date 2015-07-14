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
JHtml::_('behavior.modal');

$user = JFactory::getUser();
$userId = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$sortFields = $this->getSortFields();
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

	<form action="<?php echo JRoute::_('index.php?option=com_judownload&view=styles'); ?>" method="post" name="adminForm" id="adminForm">
		<fieldset id="filter-bar">
			<div class="filter-search input-append pull-left">
				<label class="filter-search-lbl element-invisible" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
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
					<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
						<option value="asc" <?php if ($listDirn == 'asc')
						{
							echo 'selected="selected"';
						} ?>><?php echo JText::_('COM_JUDOWNLOAD_ASC'); ?>
						</option>
						<option value="desc" <?php if ($listDirn == 'desc')
						{
							echo 'selected="selected"';
						} ?>><?php echo JText::_('COM_JUDOWNLOAD_DESC'); ?>
						</option>
					</select>
				</div>

				<div class="pull-right">
					<label for="sortTable" class="element-invisible"><?php echo JText::_('COM_JUDOWNLOAD_SORT_BY'); ?></label>
					<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
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
				<th style="width:2%" class="center hidden-phone">
					<input type="checkbox" onclick="Joomla.checkAll(this)" title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value="" name="checkall-toggle" />
				</th>
				<th style="width:25%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TEMPLATE_STYLE', 'style.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_DEFAULT', 'style.home', $listDirn, $listOrder); ?>
				</th>
				<th style="width:15%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TEMPLATE', 'plg.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'style.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<td colspan="5"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
			</tfoot>

			<tbody>
			<?php
			foreach ($this->items AS $i => $item):
				$canEdit    = $user->authorise('core.edit',       'com_judownload') && $this->groupCanDoManage;
				$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0;
				$canEditOwn = $user->authorise('core.edit.own',   'com_judownload') && $item->created_by == $user->id && $this->groupCanDoManage;
				$canChange  = $user->authorise('core.edit.state', 'com_judownload') && $canCheckin && $this->groupCanDoManage;
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center hidden-phone"><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
					<td>
						<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level-1); ?>
						<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $user->username, $item->checked_out_time, 'styles.', $canCheckin || $user->authorise('core.manage', 'com_checkin')); ?>
						<?php endif; ?>
						<?php if ($canEdit || $canEditOwn)
						{
							?>
							<?php if($user->authorise('core.admin','com_judownload')){ ?>
								<a target="_blank" href="<?php echo JUri::root().'index.php?option=com_judownload&view=category&id=1&tplStyle='.(int) $item->id ?>" class="jgrid">
									<i class="icon-eye-open"></i></a>
							<?php } ?>
							<a href="<?php echo JRoute::_('index.php?option=com_judownload&task=style.edit&id=' . $item->id, false); ?>">
								<?php echo $item->title; ?>
							</a>
						<?php
						}
						else
						{
							echo $item->title;
						}
						?>
					</td>
					<td class="center">
						<?php if ($item->home == '0' || $item->home == '1'):
							$enableIsDefault = false;
							?>
							<?php echo JHtml::_('jgrid.isdefault', $item->home != '0', $i, 'styles.', $canChange && $item->home != '1' && $enableIsDefault);?>
						<?php elseif ($canChange):?>
							<a href="<?php echo JRoute::_('index.php?option=com_judownload&task=styles.unsetDefault&cid[]='.$item->id.'&'.JSession::getFormToken().'=1');?>">
								<?php echo JHtml::_('image', 'mod_languages/'.$item->image.'.gif', $item->language_title, array('title' => JText::sprintf('COM_JUDOWNLOAD_UNSET_DEFAULT_IN_LANGUAGE_X', $item->language_title)), true);?>
							</a>
						<?php else:?>
							<?php echo JHtml::_('image', 'mod_languages/'.$item->image.'.gif', $item->language_title, array('title' => $item->language_title), true);?>
						<?php endif;?>
					</td>
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_judownload&view=template&id=' . (int) $item->template_id . '&file=' . base64_encode('home')); ?>">
							<?php echo $item->template_title; ?>
						</a>
					</td>
					<td class="center"><?php echo $item->id; ?></td>
				</tr>
			<?php
			endforeach; ?>
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