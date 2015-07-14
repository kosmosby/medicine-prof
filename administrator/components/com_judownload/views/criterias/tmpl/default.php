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
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'ordering');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_judownload&task=criterias.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'data-list', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

<div id="iframe-help"></div>

<form
	action="<?php echo JRoute::_('index.php?option=com_judownload&view=criterias'); ?>"
	method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="span12">
		<?php
		
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('COM_JUDOWNLOAD_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped adminlist" id="data-list">
				<thead>
				<tr>
					<th style="width:2%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', '', 'ordering', $listDirn, $listOrder, null, 'asc', 'COM_JUDOWNLOAD_FIELD_ORDERING', 'icon-menu-2'); ?>
					</th>
					<th style="width:2%" class="hidden-phone">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th style="width:20%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_title', 'title', $listDirn, $listOrder); ?>
					</th>
					<th style="width:20%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_GROUP_NAME', 'group_name', $listDirn, $listOrder); ?>
					</th>
					<th style="width:20%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_WEIGHTS', 'weights', $listDirn, $listOrder); ?>
					</th>
					<th style="width:10%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_REQUIRED', 'required', $listDirn, $listOrder); ?>
					</th>
					<th style="width:10%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_STATE', 'published', $listDirn, $listOrder); ?>
					</th>
					<th style="width:3%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'id', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>

				<tfoot>
				<tr>
					<td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
				</tfoot>

				<tbody>
				<?php
				$criteriagroupCandoManage = JUDownloadHelper::checkGroupPermission("criteriagroup.edit");
				foreach ($this->items AS $i => $item) :
					$canEdit    = $user->authorise('core.edit',       'com_judownload') && $this->groupCanDoManage;
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
					$canEditOwn = $user->authorise('core.edit.own',   'com_judownload') && $item->created_by == $userId && $this->groupCanDoManage;
					$canChange  = $user->authorise('core.edit.state', 'com_judownload') && $canCheckin && $this->groupCanDoManage;
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="order nowrap center hidden-phone">
							<?php
							$iconClass = '';
							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
							elseif (!$saveOrder)
							{
								$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
							}
							?>
							<span class="sortable-handler<?php echo $iconClass ?>">
									<i class="icon-menu"></i>
								</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
							<?php endif; ?>
						</td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php
								echo JHtml::_('jgrid.checkedout', $i, $item->checked_out_name, $item->checked_out_time, 'criterias.', $canCheckin || $user->authorise('core.manage', 'com_checkin'));
								?>
							<?php endif; ?>
							<?php if ($canEdit || $canEditOwn)
							{
								?>
								<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=criteria.edit&amp;id=' . $item->id); ?>">
									<?php echo $item->title; ?>
								</a>
							<?php
							}
							else
							{
								?>
								<?php echo $item->title; ?>
							<?php } ?>
						</td>
						<td>
							<?php if ($criteriagroupCandoManage)
							{
								?>
								<a href="<?php echo 'index.php?option=com_judownload&amp;task=criteriagroup.edit&amp;id=' . $item->group_id; ?>"><?php echo $item->group_name; ?></a>
							<?php
							}
							else
							{
								echo $item->group_name;
							} ?>
						</td>
						<td>
							<?php echo $item->weights; ?>
						</td>
						<td>
							<?php echo JHtml::_('judownloadadministrator.criteriaRequired', $item->required, $i, $canChange); ?>
						</td>
						<td>
							<?php echo JHtml::_('jgrid.published', $item->published, $i, 'criterias.', true, 'cb'); ?>
						</td>
						<td>
							<?php echo $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>