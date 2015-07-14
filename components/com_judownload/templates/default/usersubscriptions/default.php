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

?>
<div id="judl-container" class="jubootstrap component judl-container view-usersubscriptions">

	<?php
	if ($this->params->get('show_page_heading') && $this->params->get('page_heading'))
	{
		?>
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	<?php
	} ?>

	<h2 class="title"><?php echo JText::sprintf('COM_JUDOWNLOAD_USER_SUBSCRIPTIONS_HEADING', $this->userSubscriptionsUserName); ?></h2>

	<form action="#" method="post" name="judl-form-subscriptions" id="judl-form-subscriptions" class="judl-form-subscriptions">
		<div class="sort-pagination clearfix">
			<?php
			if ($this->isOwnDashboard)
			{?>
				<div class="pull-left">
					<button type="button" class="btn btn-default btn-primary btn-xs" onclick="Joomla.submitbutton('usersubscriptions.unsubscribe');">
						<i class="fa fa-bookmark-o"></i> <?php echo JText::_('COM_JUDOWNLOAD_UNSUBSCRIBE_SELECTED_ITEMS'); ?>
					</button>
				</div>
			<?php
			} ?>

			<div class="pull-right">
				<select class="input-medium sort-by" name="filter_order" onchange="this.form.submit()">
					<?php echo JHtml::_('select.options', $this->orderNameArray, 'value', 'text', $this->listOrder); ?>
				</select>
				<select class="input-medium sort-direction" name="filter_order_Dir" onchange="this.form.submit()">
					<?php echo JHtml::_('select.options', $this->orderDirArray, 'value', 'text', $this->listDirn); ?>
				</select>
			</div>
		</div>

		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<?php if ($this->isOwnDashboard): ?>
					<th>
						<input type="checkbox" onclick="Joomla.checkAll(this)"
						       title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value=""
						       name="checkall-toggle"/></th>
				<?php endif ?>
				<th><?php echo JText::_('COM_JUDOWNLOAD_FIELD_TITLE'); ?></th>
				<th><?php echo JText::_('COM_JUDOWNLOAD_FIELD_TYPE'); ?></th>
				<?php if ($this->isOwnDashboard): ?>
					<th><?php echo JText::_('COM_JUDOWNLOAD_ACTION'); ?></th>
				<?php endif ?>
			</tr>
			</thead>

			<tbody>
			<?php
			if (count($this->items) > 0)
			{
				foreach ($this->items AS $key => $value)
				{
					$type = $value->type;
					if ($type == 'document')
					{
						$title   = $value->document_title;
						$linkDoc = JRoute::_(JUDownloadHelperRoute::getDocumentRoute($value->item_id));
					}

					if ($type == 'comment')
					{
						$title   = $value->comment_title;
						$linkDoc = JRoute::_(JUDownloadHelperRoute::getDocumentRoute($value->document_id));
					}

					if ($this->isOwnDashboard)
					{
						$unsubscribeLink = JRoute::_('index.php?option=com_judownload&task=usersubscriptions.unsubscribe&sub_id=' . $value->id
							. '&' . $this->token . '=1');
					}
					?>
					<?php if ($title)
					{
						?>
						<tr>
							<?php
							if ($this->isOwnDashboard)
							{?>
								<td>
									<?php echo JHtml::_('grid.id', $key, $value->id); ?>
								</td>
							<?php
							} ?>
							<td class="title">
								<a href="<?php echo $linkDoc ?>"><?php echo $title; ?></a>
							</td>
							<td>
								<?php echo ucfirst($type) ?>
							</td>
							<?php
							if ($this->isOwnDashboard)
							{ ?>
								<td>
									<a href="<?php echo $unsubscribeLink ?>"
									   class="btn btn-default btn-xs"><i class="fa fa-bookmark-o"></i> <?php echo JText::_('COM_JUDOWNLOAD_UNSUBSCRIBE'); ?>
									</a>
								</td>
							<?php
							} ?>
						</tr>
					<?php
					} ?>
				<?php
				}
			}?>
			</tbody>
		</table>

		<div class="pagination-wrap clearfix">
			<?php
			if ($this->params->get('show_pagination', 1) == 2)
			{
				?>
				<div class="limitbox">
					<div class="display-number">
						<?php echo JText::_('COM_JUDOWNLOAD_PAGINATION_DISPLAY'); ?>
					</div>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			<?php
			}
			?>

			<?php
			if ($this->pagination->get('pages.total') > 1)
			{
				?>
				<div class="pages-links">
					<?php echo $this->pagination->getPagesLinks(); ?>
				</div>

				<div class="counter">
					<?php echo $this->pagination->getPagesCounter(); ?>
				</div>
			<?php
			} ?>
		</div>

		<div>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>