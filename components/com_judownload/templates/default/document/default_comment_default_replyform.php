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

$title = $name = $email = $website = $comment = '';
$hiddenForm = true;
if (isset($this->form['parent_id']) && $this->form['parent_id'] == $this->commentObj->id)
{
	$title      = $this->form['title'];
	$name       = $this->form['guest_name'];
	$email      = $this->form['guest_email'];
	$comment    = $this->form['comment'];
	$website    = (isset($this->form['website'])) ? $this->form['website'] : '';
	$hiddenForm = false;
}
?>
<div class="comment-reply-wrapper <?php echo $hiddenForm ? 'hidden' : ''; ?> "
     id="comment-reply-wrapper-<?php echo $this->commentObj->id; ?>">
	<form name="judl-comment-reply-form-<?php echo $this->commentObj->id; ?>"
	      id="judl-comment-reply-form-<?php echo $this->commentObj->id; ?>" class="form-validate reply-form form-horizontal" method="POST"
	      action="">
		<fieldset>
			<legend><?php echo JText::_('COM_JUDOWNLOAD_REPLY_COMMENT'); ?></legend>
			<div class="judl-comment-wrapper clearfix">
				<div class="comment-message-container"></div>
				<div class="judl-comment">
					<p class="note-required">
						<?php echo JText::sprintf('COM_JUDOWNLOAD_ALL_FIELDS_HAVE_STAR_ARE_REQUIRED', '<span class="required">*</span>'); ?>
					</p>

					<div class="comment-header">
						<div class="form-group">
							<label class="control-label col-sm-2"
							       for="comment-reply-title-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_TITLE'); ?>
								<span class="required">*</span></label>

							<div class="col-sm-10">
								<input name="title" class="required comment-title" id="comment-reply-title-<?php echo $this->commentObj->id; ?>"
								       type="text" value="<?php echo $title; ?>"/>
							</div>
						</div>
						<?php if ($this->user->get('guest'))
						{
							?>
							<div class="form-group">
								<label class="control-label col-sm-2"
								       for="comment-reply-name-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_NAME'); ?>
									<span class="required">*</span></label>

								<div class="col-sm-10">
									<input name="guest_name" class="required" type="text" id="comment-reply-name-<?php echo $this->commentObj->id; ?>"
									       value="<?php echo $name; ?>"/>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2"
								       for="comment-reply-email-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_EMAIL'); ?>
									<span class="required">*</span></label>

								<div class="col-sm-10">
									<input name="guest_email" class="email required" type="text" id="comment-reply-email-<?php echo $this->commentObj->id; ?>"
									       value="<?php echo $email; ?>"/>
								</div>
							</div>
						<?php
						}
						else
						{
							?>
							<div class="form-group">
								<label class="control-label col-sm-2"
								       for="comment-reply-name-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_NAME'); ?>
									<span class="required">*</span></label>

								<div class="col-sm-10">
									<input name="guest_name" type="text" id="comment-reply-name-<?php echo $this->commentObj->id; ?>"
									       value="<?php echo $this->user->name; ?>" readonly="readonly"/>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2"
								       for="comment-reply-email-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_EMAIL'); ?>
									<span class="required ">*</span></label>

								<div class="col-sm-10">
									<input name="guest_email" type="text" id="comment-reply-email-<?php echo $this->commentObj->id; ?>"
									       value="<?php echo $this->user->email; ?>" readonly="readonly"/>
								</div>
							</div>
						<?php
						} ?>

						<?php if ($this->website_field_in_comment_form)
						{
							$require = ($this->website_field_in_comment_form == 1) ? '' : '<span class=required>*</span>';
							$class   = ($this->website_field_in_comment_form == 1) ? '' : 'required';
							?>
							<div class="form-group">
								<label class="control-label col-sm-2"
								       for="comment-reply-website-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_WEBSITE'); ?><?php echo $require; ?></label>

								<div class="col-sm-10">
									<input type="text" name="website" id="comment-reply-website-<?php echo $this->commentObj->id; ?>"
									       class="url <?php echo $class; ?>" value="<?php echo $website; ?>"/>
								</div>
							</div>
						<?php
						} ?>
					</div>

					<div class="comment-body">
						<div class="comment-row">
							<label for="comment-reply-editor-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT'); ?><span class="required">*</span></label>
						</div>
						<div class="comment-row">
								<textarea class="required validate-comment comment-editor form-control"
									id="comment-reply-editor-<?php echo $this->commentObj->id; ?>"
									name="comment" rows="8"><?php echo $comment; ?></textarea>
						</div>

						<?php if (JUDLPROVERSION && $this->params->get('can_subscribe_own_comment', 1))
						{
							?>
							<div class="comment-row">
								<label class="comment-subscribe-lbl"
								       for="comment-reply-subscribe-<?php echo $this->commentObj->id ?>">
									<input name="subscribe" id="comment-reply-subscribe-<?php echo $this->commentObj->id ?>"
									       class="comment-subscribe" type="checkbox" value="1"/>
									<?php echo JText::_('COM_JUDOWNLOAD_COMMENT_SUBSCRIBE'); ?>
								</label>
							</div>
						<?php
						}

						if (JUDownloadFrontHelperPermission::showCaptchaWhenComment($this->item->id))
						{
							echo JUDownloadFrontHelperCaptcha::getCaptcha(true);
						}
						?>
					</div>

					<div class="comment-form-submit clearfix">
						<button type="button" class="btn btn-default btn-primary"
							onclick="Joomla.submitbutton('document.addComment', 'judl-comment-reply-form-<?php echo $this->commentObj->id; ?>');"><?php echo JText::_('COM_JUDOWNLOAD_SUBMIT'); ?>
						</button>
						<input type="reset" name="reply-reset" class="btn btn-default"
						       value="<?php echo JText::_('COM_JUDOWNLOAD_RESET'); ?>">
					</div>
					<div>
						<input type="hidden" name="task" value=""/>
						<input type="hidden" name="parent_id" value="<?php echo $this->commentObj->id; ?>"/>
						<input type="hidden" name="doc_id" value="<?php echo $this->item->id; ?>"/>
						<?php echo JHtml::_('form.token'); ?>
					</div>
				</div>
			</div>
		</fieldset>
	</form>
</div>