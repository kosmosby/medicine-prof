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
<div id="judl-container" class="jubootstrap component judl-container view-modpendingcomments">
	<h2 class="judl-view-title"><?php echo JText::_('COM_JUDOWNLOAD_PENDING_COMMENTS'); ?></h2>

	<?php if (!count($this->items))
	{ ?>
		<div class="alert alert-block">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<?php echo JText::_('COM_JUDOWNLOAD_NO_PENDING_COMMENT'); ?>
		</div>
	<?php
	} ?>

	<form name="judl-form-comments" id="judl-form-comments" class="judl-form" method="post" action="#">

		<?php
			echo $this->loadTemplate('header');
		?>

		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<th style="width:5%">
					<input type="checkbox" name="judl-cbAll" id="judl-cbAll" value=""/>
				</th>
				<th>
					<?php echo JText::_('COM_JUDOWNLOAD_FIELD_TITLE'); ?>
				</th>
				<th style="width:30%">
					<?php echo JText::_('COM_JUDOWNLOAD_FIELD_PARENT'); ?>
				</th>
				<th style="width:15%">
					<?php echo JText::_('COM_JUDOWNLOAD_FIELD_GUEST_NAME'); ?>
				</th>
				<th style="width:15%">
					<?php echo JText::_('COM_JUDOWNLOAD_FIELD_CREATED'); ?>
				</th>
				<th style="width:5%">
					<?php echo JText::_('COM_JUDOWNLOAD_FIELD_ID'); ?>
				</th>
			</tr>
			</thead>

			<tbody>
			<?php
			if (count($this->items))
			{
				?>
				<?php foreach ($this->items AS $i => $item)
				{
					?>
					<tr>
						<td>
							<input type="checkbox" checked="checked" class="judl-cb" name="cid[]"
							       value="<?php echo $item->id; ?>" id="judl-cb-<?php echo $item->id; ?>"/>
						</td>
						<td>
							<?php
							if ($item->checked_out)
							{
								if ($item->checkout_link)
								{
									$checkedOutUser = JFactory::getUser($item->checked_out);
									$checkedOutTime = JHtml::_('date', $item->checked_out_time);
									$tooltip  = JText::_('COM_JUDOWNLOAD_EDIT_COMMENT');
									$tooltip .= '<br/>';
									$tooltip .= JText::sprintf('COM_JUDOWNLOAD_CHECKED_OUT_BY', $checkedOutUser->name) . ' <br /> ' . $checkedOutTime;

									echo '<a class="hasTooltip btn btn-mini" title="' . $tooltip . '" href="' . $item->checkout_link . '"><i class="fa fa-lock"></i></a>';
								}
								else
								{
									echo '<a class="btn btn-mini"><i class="fa fa-lock"></i></a>';
								}
							}
							?>
							<a href="<?php echo JRoute::_('index.php?option=com_judownload&task=modpendingcomment.edit&id=' . $item->id); ?>">
								<?php echo $item->title; ?>
							</a>
						</td>
						<td>
							<a href="<?php echo JRoute::_(JUDownloadHelperRoute::getDocumentRoute($item->doc_id)); ?>">
								<?php echo $item->document_title; ?></a>
							<?php if ($item->level > 1)
							{
								?>
								<span class="divider"> > </span>
								<a target="_blank"
								   href="<?php echo JRoute::_(JUDownloadHelperRoute::getDocumentRoute($item->doc_id)) . "#comment-item-" . $item->parent_id; ?>">
									<?php echo $item->parent_title; ?></a>
							<?php
							} ?>
						</td>
						<td>
							<?php
							if ($item->user_id > 0)
							{
								$userComment = JFactory::getUser($item->user_id);
								echo $userComment->get('name');
							}
							else
							{
								echo $item->guest_name;
							}
							?>
						</td>
						<td>
							<?php echo $item->created; ?>
						</td>
						<td>
							<?php echo $item->id; ?>
						</td>
					</tr>
				<?php
				}
			} ?>
			</tbody>
		</table>

		<?php
			echo $this->loadTemplate('footer');
		?>

		<div>
			<input type="hidden" name="task" value=""/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>