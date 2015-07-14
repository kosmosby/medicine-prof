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

$comment_parent_id = $this->comment_parent_id;
?>
<div class="judl-comments">
	<ul class="comment-list">
		<?php
		$avatar = new JUDownloadAvatarHelper();

		foreach ($this->items AS $commentObj)
		{
			if ($commentObj->parent_id == $comment_parent_id)
			{
				?>
				<li class="comment-item level-<?php echo $commentObj->level; ?>"
				    id="comment-item-<?php echo $commentObj->id; ?>">
					<div itemscope="" itemtype="http://schema.org/Review">
						<div class="comment-box clearfix">
							<div class="comment-user">
								<?php
								$userAvatar = $avatar->getAvatar(JFactory::getUser($commentObj->user_id), $this->params);
								?>
								<img class="comment-avatar" itemprop="image" alt="Avatar"
								     src="<?php echo $userAvatar; ?>"/>

								<h3 class="comment-username" itemprop="creator" itemscope=""
								    itemtype="http://schema.org/Person">
									<span itemprop="name">
									<?php
									if ($commentObj->user_id > 0)
									{
										$userComment = JFactory::getUser($commentObj->user_id);
										echo $userComment->get('name');
									}
									else
									{
										echo $commentObj->guest_name;
									} ?>
									</span>
								</h3>
								<?php
								if ($commentObj->parent_id == $this->root_comment->id)
								{
									$fieldRating = JUDownloadFrontHelperField::getField('rating', $commentObj->doc_id);

									echo $fieldRating->getOutput(array('view' => 'details', 'template' => $this->template, 'type' => 'comment', 'comment_object' => $commentObj));
								} ?>
							</div>
							<!-- /.comment-user -->

							<div class="comment-text">
								<div class="judl-metadata clearfix">
									<h4 class="comment-title" itemprop="name"><?php echo $commentObj->title; ?></h4>

									<div itemprop="datePublished" class="comment-created">
										<?php echo JText::_('COM_JUDOWNLOAD_POST_ON') . " : " . $commentObj->createdAgo; ?>
									</div>

									<?php if ($commentObj->website != '')
									{
										?>
										<div class="comment-website">
											<?php echo JText::_('COM_JUDOWNLOAD_COMMENT_WEBSITE') . " : " . $commentObj->website; ?>
										</div>
									<?php
									} ?>

								</div>
								<!-- /.judl-metadata -->

								<?php
									$commentObj->comment = JUDownloadFrontHelper::BBCode2Html($commentObj->comment);
									$commentObj->comment = JUDownloadFrontHelperComment::parseCommentText($commentObj->comment, $this->doc_id);
								?>

								<div class="see-more" itemprop="description">
									<?php echo $commentObj->comment; ?>
								</div>
							</div>
						</div>
						<?php
						$totalChildComments = $this->getModel()->getTotalChildComments($commentObj->id);
						if ($totalChildComments > 0)
						{
							$this->comment_parent_id = $commentObj->id;
							echo $this->loadTemplate('comments');
						} ?>
					</div>
					<!-- /.comment-content -->
				</li>
			<?php
			}
		}
		?>
	</ul>
</div>