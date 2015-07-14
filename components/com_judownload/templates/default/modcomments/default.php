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
<div id="judl-container" class="jubootstrap component judl-container view-modcomments">
	<h2 class="judl-view-title"><?php echo JText::_('COM_JUDOWNLOAD_MANAGE_COMMENTS'); ?></h2>

	<?php if (!is_array($this->items) || !count($this->items))
	{
		?>
		<div class="alert alert-block">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<?php echo JText::_('COM_JUDOWNLOAD_NO_COMMENT'); ?>
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
				<th style="width:5%" class="center">
					<input type="checkbox" name="judl-cbAll" id="judl-cbAll" value=""/>
				</th>
				<th class="center">
					<?php echo JText::_('COM_JUDOWNLOAD_FIELD_TITLE'); ?>
				</th>
				<th style="width:20%" class="center">
					<?php echo JText::_('COM_JUDOWNLOAD_FIELD_PARENT'); ?>
				</th>
				<th style="width:15%" class="center">
					<?php echo JText::_('COM_JUDOWNLOAD_FIELD_GUEST_NAME'); ?>
				</th>
				<th style="width:15%" class="center">
					<?php echo JText::_('COM_JUDOWNLOAD_FIELD_CREATED'); ?>
				</th>
				<th style="width:10%" class="center">
					<?php echo JText::_('COM_JUDOWNLOAD_FIELD_PUBLISHED'); ?>
				</th>
				<th style="width:5%" class="center">
					<?php echo JText::_('COM_JUDOWNLOAD_FIELD_ID'); ?>
				</th>
			</tr>
			</thead>

			<tbody>
			<?php
			if (is_array($this->items) && count($this->items))
			{
				// @todo recheck hosting
				require_once JPATH_SITE . '/components/com_judownload/models/document.php';
				$documentModel = JModelLegacy::getInstance('Document', 'JUDownloadModel');

				foreach ($this->items AS $i => $item)
				{
					$canEdit = JUDownloadFrontHelperModerator::checkModeratorCanDoWithComment($item->id, 'comment_edit');
					?>
					<tr>
						<td class="center">
							<input type="checkbox" class="judl-cb" name="cid[]"
							       value="<?php echo $item->id; ?>" id="judl-cb-<?php echo $i; ?>"/>
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

									echo '<a class="hasTooltip btn btn-default btn-xs" title="' . $tooltip . '" href="' . $item->checkout_link . '"><i class="fa fa-lock"></i></a>';
								}
								else
								{
									echo '<a class="btn btn-default btn-xs"><i class="fa fa-lock"></i></a>';
								}
							}
							?>
							<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level - 1); ?>

							<?php
							if ($canEdit)
							{
								?>
								<a href="<?php echo JRoute::_('index.php?option=com_judownload&task=modcomment.edit&id=' . $item->id); ?>">
									<?php echo $item->title; ?>
								</a>
							<?php
							}
							else
							{
								echo $item->title;
							}
							?>

							<a href="<?php echo JRoute::_('index.php?option=com_judownload&view=commenttree&id=' . $item->id . '&tmpl=component'); ?>"
							   rel="{handler: 'iframe', size: {x: 570, y: 500}}" class="modal judl-comment-tree">
								<i class="fa fa-sitemap"></i>
							</a>
						</td>

						<td>
							<?php if ($item->level == 1)
							{
								$limitStart = $documentModel->getLimitStartForComment($item->id);
								?>
								<a href="<?php echo JRoute::_(JUDownloadHelperRoute::getDocumentRoute($item->doc_id) . '&limitstart=' . $limitStart . '&resetfilter=1' . '#comment-item-' . $item->id); ?>">
									<?php echo $item->document_title; ?></a>
							<?php
							}
							elseif ($item->level > 1)
							{
								$parentCommentObject = JUDownloadFrontHelperComment::getCommentObject($item->parent_id, 'cm.id, cm.title');
								$limitStart          = $documentModel->getLimitStartForComment($parentCommentObject->id);
								?>
								<a href="<?php echo JRoute::_(JUDownloadHelperRoute::getDocumentRoute($item->doc_id)); ?>">
									<?php echo $item->document_title; ?>
								</a>
								<span> / </span>
								<a target="_blank"
								   href="<?php echo JRoute::_(JUDownloadHelperRoute::getDocumentRoute($item->doc_id) . '&limitstart=' . $limitStart . '&resetfilter=1' . '#comment-item-' . $parentCommentObject->id); ?>">
									<?php echo $parentCommentObject->title; ?>
								</a>
							<?php
							} ?>
						</td>

						<td class="center">
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

						<td class="center">
							<?php echo $item->created; ?>
						</td>

						<td class="center">
							<?php if ($item->published == 1)
							{
								?>
								<a href="#" id="judl-comment-publish-<?php echo $i; ?>"
								   class="judl-comment-publish">
									<i class="fa fa-check"></i>
								</a>
							<?php
							}
							else
							{
								?>
								<a href="#" id="judl-comment-unpublish-<?php echo $i; ?>"
								   class="judl-comment-unpublish">
									<i class="fa fa-close"></i>
								</a>
							<?php
							} ?>
						</td>

						<td class="center">
							<?php echo $item->id; ?>
						</td>
					</tr>
				<?php
				}
			}
			?>
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