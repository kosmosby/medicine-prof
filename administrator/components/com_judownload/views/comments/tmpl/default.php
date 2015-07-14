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
$app = JFactory::getApplication();
$doc_id = $app->input->get("doc_id", "");
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$sortFields = $this->getSortFields();
$saveOrder 	= ($listOrder == 'cm.lft' && strtolower($listDirn) == 'asc');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_judownload&task=comments.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'data-list', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
}
?>

<?php echo JUDownloadHelper::getMenu($app->input->get('view')); ?>

<div id="iframe-help"></div>

<form
	action="<?php echo JRoute::_('index.php?option=com_judownload&view=comments'); ?>"
	method="post" name="adminForm" id="adminForm">
<div id="j-main-container" class="span12">
	<?php
	
	echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array("filterButton" => false)));
	?>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('COM_JUDOWNLOAD_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<table class="table table-striped adminlist" id="data-list">
			<thead>
			<tr>
				<th width="1%" class="nowrap center hidden-phone">
					<?php echo JHtml::_('searchtools.sort', '', 'cm.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
				</th>
				<th width="1%" class="center hidden-phone">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
				<th style="width:15%">
					<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_TITLE', 'cm.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width:15%">
					<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_DOCUMENT_TITLE', 'd.title', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_USERNAME', 'ua.name', $listDirn, $listOrder); ?>
				</th>
				<th style="width:10%" class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_EMAIL', 'ua.email', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_TOTAL_VOTES', 'cm.total_votes', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_HELPFUL_VOTES', 'cm.helpful_votes', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_CREATED', 'cm.created', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_IP_ADDRESS', 'cm.ip_address', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_PUBLISHED', 'cm.published', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_REPORTS', 'total_reports', $listDirn, $listOrder); ?>
				</th>
				<th style="width:5%" class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_SUBSCRIPTIONS', 'total_subscriptions', $listDirn, $listOrder); ?>
				</th>
				<th style="width:3%" class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'cm.id', $listDirn, $listOrder); ?>
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


				
				if ($item->level > 1)
				{
					$parentsStr = "";
					$_currentParentId = $item->parent_id;
					$parentsStr = " " . $_currentParentId;
					for ($i2 = 0; $i2 < $item->level; $i2++)
					{
						foreach ($this->ordering as $k => $v)
						{
							$v = implode("-", $v);
							$v = "-" . $v . "-";
							if (strpos($v, "-" . $_currentParentId . "-") !== false)
							{
								$parentsStr .= " " . $k;
								$_currentParentId = $k;
								break;
							}
						}
					}
				}
				else
				{
					$parentsStr = "";
				}
				?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->parent_id; ?>" item-id="<?php echo $item->id ?>" parents="<?php echo $parentsStr ?>" level="<?php echo $item->level ?>">
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
							<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" />
						<?php endif; ?>
					</td>
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
					<td>
						<?php echo $item->total_votes; ?>
					</td>
					<td>
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
					<td>
						<?php
						if ($item->total_reports)
						{
							echo '<a href="index.php?option=com_judownload&view=reports&comment_id=' . $item->id . '" title="' . JText::_("COM_JUDOWNLOAD_VIEW_REPORTS") . '">' . JText::plural("COM_JUDOWNLOAD_N_REPORTS", $item->total_reports) . '</a>';
						} ?>
					</td>
					<td>
						<?php
						if ($item->total_subscriptions)
						{
							echo '<a href="index.php?option=com_judownload&view=subscriptions&comment_id=' . $item->id . '" title="' . JText::_("COM_JUDOWNLOAD_VIEW_SUBSCRIPTIONS") . '">' . JText::plural("COM_JUDOWNLOAD_N_SUBSCRIPTIONS", $item->total_subscriptions) . '</a>';
						} ?>
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
		<input type="hidden" name="doc_id" id="doc_id" value="<?php echo $doc_id; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</div>
</form>