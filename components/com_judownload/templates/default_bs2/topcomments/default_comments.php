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
<ul class="comment-list">
	<?php
	$timeAgo = new TimeAgo();
	$avatar = new JUDownloadAvatarHelper ();

	foreach ($this->items AS $commentObj)
	{
		$userAvatar = $avatar->getAvatar(JFactory::getUser($commentObj->user_id), $this->params);
		?>
		<li class="comment-item level-<?php echo $commentObj->level; ?>"
		    id="comment-item-<?php echo $commentObj->id; ?>">
			<div itemscope="" itemtype="http://schema.org/Review">
				<div class="comment-box clearfix">
					<div class="comment-user">
						<img class="comment-avatar" itemprop="image" src="<?php echo $userAvatar; ?>" alt="Avatar"
							 style="max-width: <?php echo $this->params->get("avatar_width", 120); ?>px;max-height: <?php echo $this->params->get("avatar_height", 120); ?>px;"/>

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
								}
								?>
							</span>
						</h3>

						<?php
						if ($commentObj->parent_id == $this->root_comment->id)
						{
							$fieldRating = JUDownloadFrontHelperField::getField('rating', $commentObj->doc_id);
							echo $fieldRating->getOutput(array('view' => 'details', 'template' => $this->template, 'type' => 'comment', 'comment_object' => $commentObj));
						}
						?>
					</div>

					<div class="comment-text">
						<h4 class="comment-title" itemprop="name"><?php echo $commentObj->title; ?></h4>

						<div class="comment-metadata clearfix">
							<div class="comment-created" itemprop="datePublished">
								<i class="fa fa-calendar"></i> <?php echo JText::_('COM_JUDOWNLOAD_POST_ON') . ': ' . $timeAgo->inWords(JHtml::_('date', $commentObj->created, 'Y-m-d H:i:s')); ?>
							</div>
						</div>
						<?php
						$commentObj->comment = JUDownloadFrontHelper::BBCode2Html($commentObj->comment);
						$commentObj->comment = JUDownloadFrontHelperComment::parseCommentText($commentObj->comment, $commentObj->doc_id);
						?>
						<div class="comment-content" itemprop="description">
							<?php echo $commentObj->comment; ?>
						</div>
					</div>
				</div>
			</div>
		</li>
	<?php
	} ?>
</ul>