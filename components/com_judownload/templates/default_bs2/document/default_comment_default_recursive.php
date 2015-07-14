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

require_once JPATH_SITE . '/components/com_judownload/helpers/avatar.php';
require_once JPATH_SITE . '/components/com_judownload/helpers/timeago.php';
$timeAgo = new TimeAgo();
$parent_id = $this->item->comment->parent_id;
?>
<ul class="comment-list clearfix">
	<?php
	foreach ($this->item->comment->items AS $this->commentObj)
	{
		if ($this->commentObj->parent_id == $parent_id)
		{
			?>
			<li class="comment-item <?php echo ($this->commentObj->published == 1) ? 'published' : 'unpublished'; ?> level-<?php echo $this->commentObj->level; ?>"
			    id="comment-item-<?php echo $this->commentObj->id; ?>">
				<div itemscope="" itemtype="http://schema.org/Review">
					<div id="<?php echo "comment-box-".$this->commentObj->id; ?>" class="comment-box clearfix">
						<div class="comment-user">
							<?php
							$avatar = new JUDownloadAvatarHelper();
							$userAvatar = $avatar->getAvatar(JFactory::getUser($this->commentObj->user_id), $this->params);
							if ($this->commentObj->user_id > 0)
							{
								$userComment = JFactory::getUser($this->commentObj->user_id);
								$userName    = $userComment->get('name');
							}
							else
							{
								$userName = $this->commentObj->guest_name;
							}
							?>
							<img class="comment-avatar" itemprop="image" alt="<?php echo $userName; ?>"
							     src="<?php echo $userAvatar; ?>"/>

							<h3 class="comment-username" itemprop="creator" itemscope=""
							    itemtype="http://schema.org/Person">
								<span itemprop="name">
									<?php
									echo $userName;
									?>
								</span>
							</h3>
							<?php
							if (isset($this->item->fields['rating']) && $this->commentObj->parent_id == $this->root_comment->id)
							{
								echo $this->item->fields['rating']->getOutput(array("view" => "details", "template" => $this->template, "type" => "comment", "comment_object" => $this->commentObj));
							}
							?>
						</div>
						<!-- /.comment-user -->

						<div class="comment-text">
							<h4 class="comment-title" itemprop="name"><?php echo $this->commentObj->title; ?></h4>

							<div class="comment-metadata clearfix">
								<div itemprop="datePublished" class="comment-created">
									<i class="fa fa-calendar"></i>
									<?php echo JText::_('COM_JUDOWNLOAD_POST_ON') . ": " . $timeAgo->inWords(JHtml::_('date', $this->commentObj->created, 'Y-m-d H:i:s')); ?>
								</div>

								<?php if ($this->commentObj->website != '')
								{
									?>
									<div class="comment-website">
										<?php
										echo JText::_('COM_JUDOWNLOAD_COMMENT_WEBSITE') . ": " . $this->commentObj->website;
										?>
									</div>
								<?php
								} ?>
								<!-- /.comment-rating -->
							</div>
							<!-- /.comment-metadata -->
							<div class="comment-content" itemprop="description">
								<?php echo $this->commentObj->comment; ?>
							</div>
							<div class="comment-vote">
								<?php
									if ($this->commentObj->can_vote)
									{
										$vote_btn_html = '';
										// Allow vote down, only show vote button if have not voted yet
										if ($this->params->get('allow_vote_down_comment', 1))
										{
											if (!$this->commentObj->voted_value)
											{
												$vote_btn_html .= '<span id="comment-vote-' . $this->commentObj->id . '" class="comment-vote-action btn-group">';
												$vote_btn_html .= '<button class="vote-up btn btn-min" title="' . JText::_('COM_JUDOWNLOAD_VOTE_UP') . '"
																	onclick="judlVoteComment(\''.$this->commentObj->id.'\', \'up\', \''.JSession::getFormToken().'\', this); return false;">
																	<i class="fa fa-thumbs-o-up"></i>
																</button>';
												$vote_btn_html .= '<button class="vote-down btn btn-mini" title="' . JText::_('COM_JUDOWNLOAD_VOTE_DOWN') . '"
																	onclick="judlVoteComment(\''.$this->commentObj->id.'\', \'down\', \''.JSession::getFormToken().'\', this); return false;">
																	<i class="fa fa-thumbs-o-down"></i>
																</button>';
												$vote_btn_html .= '</span>';
											}
										}
										// Not allow vote down(only like or unlike)
										else
										{
											$vote_btn_html .= '<span id="comment-vote-' . $this->commentObj->id . '" class="comment-vote-action">';
											// If have dislike, or have not voted yet -> show like button
											if ($this->commentObj->voted_value == -1 || !$this->commentObj->voted_value)
											{
												$vote_btn_html .= '<button class="vote-up btn btn-mini" onclick="return false;">
																	<i class="fa fa-thumbs-o-up"></i> ' . JText::_('COM_JUDOWNLOAD_LIKE') . '
																</button>';
												$vote_btn_html .= '<button class="vote-down btn btn-mini" onclick="return false;" style="display: none">
																	<i class="fa fa fa-times"></i> ' . JText::_('COM_JUDOWNLOAD_UNLIKE') . '
																</button>';
											}
											// If have like, show unlike button
											elseif ($this->commentObj->voted_value == 1)
											{
												$vote_btn_html .= '<button class="vote-down btn btn-mini" onclick="return false;">
																	<i class="fa fa fa-times"></i> ' . JText::_('COM_JUDOWNLOAD_UNLIKE') . '
																</button>';
												$vote_btn_html .= '<button class="vote-up btn btn-mini" onclick="return false;" style="display: none">
																	<i class="fa fa-thumbs-o-up"></i> ' . JText::_('COM_JUDOWNLOAD_LIKE') . '
																</button>';
											}
											$vote_btn_html .= '</span>';
										}

										echo $vote_btn_html;
									}
									echo '<span class="vote-result">';
									echo JText::sprintf('COM_JUDOWNLOAD_N_HELPFUL_VOTES_N_TOTAL_VOTES', $this->commentObj->helpful_votes, $this->commentObj->total_votes);
									echo '</span>';
								?>
							</div>

							<div class="comment-actions clearfix">
								<?php
								if ($this->commentObj->can_reply)
								{
									?>
									<a class="btn btn-mini comment-reply" href="#"
									   id="comment-reply-<?php echo $this->commentObj->id; ?>">
										<i class="fa fa-reply"></i> <?php echo JText::_('COM_JUDOWNLOAD_REPLY_COMMENT'); ?>
									</a>
								<?php
								}

								if($this->item->params->get('access-comment') || $this->commentObj->can_reply)
								{
									?>
									<a class="btn btn-mini comment-quote" href="#"
									   id="comment-quote-<?php echo $this->commentObj->id; ?>">
										<i class="fa fa-quote-left"></i> <?php echo JText::_('COM_JUDOWNLOAD_QUOTE'); ?>
									</a>
								<?php
								}

								if ($this->commentObj->can_report)
								{
									?>
									<a class="btn btn-mini comment-report"
									   href="<?php echo JRoute::_('index.php?option=com_judownload&view=report&comment_id=' . $this->commentObj->id); ?>">
										<i class="fa fa-warning"></i> <?php echo JText::_('COM_JUDOWNLOAD_REPORT'); ?>
									</a>
								<?php
								}

								if (JUDLPROVERSION && $this->commentObj->can_subscribe)
								{
									if ($this->commentObj->is_subscriber)
									{
										?>
										<a class="judl-unsubscribe-comment btn btn-mini" href="<?php echo $this->commentObj->subscribe_link; ?>">
											<i class="fa fa-bookmark-o"></i> <?php echo JText::_('COM_JUDOWNLOAD_UNSUBSCRIBE_COMMENT'); ?>
										</a>
									<?php
									}
									else
									{
										?>
										<a class="judl-subscribe-comment btn btn-mini" href="<?php echo $this->commentObj->subscribe_link; ?>">
											<i class="fa fa-bookmark"></i> <?php echo JText::_('COM_JUDOWNLOAD_SUBSCRIBE_COMMENT'); ?>
										</a>
									<?php
									}
								}

								if ($this->commentObj->can_edit)
								{
									if ($this->commentObj->checked_out)
									{
										if ($this->commentObj->checkout_link)
										{
											$checkedOutUser = JFactory::getUser($this->commentObj->checked_out);
											$checkedOutTime = JHtml::_('date', $this->commentObj->checked_out_time);
											$tooltip  = JText::_('COM_JUDOWNLOAD_EDIT_COMMENT');
											$tooltip .= '<br/>';
											$tooltip .= JText::sprintf('COM_JUDOWNLOAD_CHECKED_OUT_BY', $checkedOutUser->name) . ' <br /> ' . $checkedOutTime;

											echo '<a class="hasTooltip btn btn-mini" title="' . $tooltip . '" href="' . $this->commentObj->checkout_link . '"><i class="fa fa-lock"></i> ' . JText::_('COM_JUDOWNLOAD_EDIT_COMMENT') . '</a>';
										}
										else
										{
											echo '<span class="btn btn-mini"><i class="fa fa-lock"></i> ' . JText::_('COM_JUDOWNLOAD_EDIT_COMMENT') . '</span>';
										}
									}
									else
									{
										?>
										<a class="btn btn-mini comment-edit" href="#"
										   id="comment-edit-<?php echo $this->commentObj->id; ?>">
											<i class="fa fa-edit"></i> <?php echo JText::_('COM_JUDOWNLOAD_EDIT_COMMENT'); ?>
										</a>
									<?php
									}
								}

								if ($this->commentObj->can_delete)
								{
									?>
									<a class="btn btn-mini comment-delete" role="button" class="btn" data-toggle="modal"
									   href="#judl-comment-delete-alert-<?php echo $this->commentObj->id; ?>">
										<i class="fa fa-times"></i> <?php echo JText::_('COM_JUDOWNLOAD_DELETE_COMMENT'); ?>
									</a>
									<!-- Modal alert delete document -->
									<div id="judl-comment-delete-alert-<?php echo $this->commentObj->id; ?>"
									     class="modal hide fade" tabindex="-1" role="dialog"
									     aria-labelledby="judl-comment-delete-alert-label-<?php echo $this->commentObj->id; ?>" aria-hidden="true">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h3 id="judl-comment-delete-alert-label-<?php echo $this->commentObj->id; ?>" class="modal-title">
												<?php echo JText::_('COM_JUDOWNLOAD_DELETE_COMMENT_ALERT'); ?>
											</h3>
										</div>
										<div class="modal-body">
											<?php echo JText::sprintf('COM_JUDOWNLOAD_DELETE_COMMENT_X_CONFIRM', $this->commentObj->title); ?>
										</div>
										<div class="modal-footer">
											<a href="<?php echo $this->commentObj->link_delete; ?>"
											        class="btn btn-primary"><?php echo JText::_("COM_JUDOWNLOAD_DELETE"); ?>
											</a>
											<button class="btn" data-dismiss="modal" aria-hidden="true">
												<?php echo JText::_("COM_JUDOWNLOAD_CANCEL"); ?>
											</button>
										</div>
									</div>
								<?php
								}
								?>
							</div>
						</div>
						<!-- /.comment-primary -->
						<?php
						if($this->commentObj->can_edit && $this->commentObj->checked_out == 0){
							echo $this->loadTemplate('comment_edit');
						}

						if ($this->commentObj->can_reply)
						{
							echo $this->loadTemplate('comment_default_replyform');
						}
						?>
					</div>
					<?php
					// Load comment recursive if is not leaf
					if (($this->commentObj->rgt > $this->commentObj->lft + 1))
					{
						$totalChildComments = $this->model->getTotalChildComments($this->commentObj->id);
						if($totalChildComments)
						{
							$this->item->comment->parent_id = $this->commentObj->id;
							echo $this->loadTemplate('comment_default_recursive');
						}
					}
					?>
				</div>
				<!-- /.comment-content -->
			</li>
		<?php
		}
	}
	?>
</ul>