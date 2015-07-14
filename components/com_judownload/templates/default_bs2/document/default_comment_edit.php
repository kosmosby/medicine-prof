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

$dataEditComment = array();
$dataEditComment['title'] = '';
$dataEditComment['guest_name'] = '';
$dataEditComment['guest_email'] = '';
$dataEditComment['title'] = $this->commentObj->title;
$dataEditComment['language'] = '';
$dataEditComment['website'] = '';
if ($this->commentObj->user_id > 0)
{
	$user = JFactory::getUser($this->commentObj->user_id);
	if (is_object($user))
	{
		$dataEditComment['guest_name']  = $user->get('name');
		$dataEditComment['guest_email'] = $user->get('email');
	}
}
else
{
	$dataEditComment['guest_name']  = $this->commentObj->guest_name;
	$dataEditComment['guest_email'] = $this->commentObj->guest_email;
}

if ($this->commentObj->language)
{
	$dataEditComment['language'] = $this->commentObj->language;
}

if ($this->commentObj->website)
{
	$dataEditComment['website'] = $this->commentObj->website;
}

$dataEditComment['comment'] = $this->commentObj->comment_edit;
?>
<div class="comment-edit-wrapper hidden"
     id="comment-edit-wrapper-<?php echo $this->commentObj->id; ?>">
	<form name="edit_form" id="judl-comment-edit-form-<?php echo $this->commentObj->id; ?>" class="form-validate comment-form comment-edit-form form-horizontal"
		method="POST" action="">
		<fieldset>
			<legend><?php echo JText::_('COM_JUDOWNLOAD_EDIT_COMMENT'); ?></legend>
			<div class="judl-comment-wrapper clearfix">
				<div class="comment-message-container"></div>
				<div class="judl-comment">
					<p class="note-required">
						<?php echo JText::sprintf('COM_JUDOWNLOAD_ALL_FIELDS_HAVE_STAR_ARE_REQUIRED', '<span class="required">*</span>'); ?>
					</p>

					<div class="comment-header">
						<div class="comment-user-info span8">
							<div class="control-group">
								<label class="control-label"
								       for="comment-title-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_TITLE'); ?>
									<span class="required">*</span></label>

								<div class="controls">
									<input name="title" class="required comment-title" id="comment-title-<?php echo $this->commentObj->id; ?>"
									       type="text" value="<?php echo $dataEditComment['title']; ?>"/>
								</div>
							</div>

							<?php
							if ($this->user->get('guest'))
							{
								?>
								<div class="control-group">
									<label class="control-label"
									       for="comment-name-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_NAME'); ?>
										<span class="required">*</span></label>

									<div class="controls">
										<input name="guest_name" class="required" id="comment-name-<?php echo $this->commentObj->id; ?>" type="text"
										       value="<?php echo $dataEditComment['guest_name']; ?>"/>
									</div>
								</div>

								<div class="control-group">
									<label class="control-label"
									       for="comment-email-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_EMAIL'); ?>
										<span class="required">*</span></label>

									<div class="controls">
										<input name="guest_email" class="email required" id="comment-email-<?php echo $this->commentObj->id; ?>" type="text"
										       value="<?php echo $dataEditComment['guest_email']; ?>"/>
									</div>
								</div>
							<?php
							}
							else
							{ ?>
								<div class="control-group">
									<label class="control-label"
									       for="comment-name-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_NAME'); ?>
										<span class="required">*</span></label>

									<div class="controls">
										<input name="guest_name" type="text" id="comment-name-<?php echo $this->commentObj->id; ?>"
										       value="<?php echo $dataEditComment['guest_name']; ?>" readonly="readonly"/>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label"
									       for="comment-email-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_EMAIL'); ?>
										<span class="required ">*</span></label>

									<div class="controls">
										<input name="guest_email" type="text" id="comment-email-<?php echo $this->commentObj->id; ?>"
										       value="<?php echo $dataEditComment['guest_email']; ?>" readonly="readonly"/>
									</div>
								</div>
							<?php
							} ?>

							<div class="control-group">
								<label class="control-label"
								       for="comment-language-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_LANGUAGE'); ?>
									<span class="required">*</span></label>

								<div class="controls">
									<select name="comment_language" class="required" id="comment-language-<?php echo $this->commentObj->id; ?>">
										<?php echo JHtml::_('select.options', $this->langArray, 'value', 'text', $dataEditComment['language']); ?>
									</select>
								</div>
							</div>

							<?php if ($this->website_field_in_comment_form)
							{
								$require = ($this->website_field_in_comment_form == 1) ? '' : '<span class=required>*</span>';
								$class   = ($this->website_field_in_comment_form == 1) ? '' : 'required';
								?>
								<div class="control-group">
									<label class="control-label"
									       for="comment-website-<?php echo $this->commentObj->id; ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_WEBSITE'); ?><?php echo $require; ?></label>

									<div class="controls">
										<input type="text" name="website"
										       id="comment-website-<?php echo $this->commentObj->id; ?>"
										       class="url <?php echo $class; ?>"
										       value="<?php echo $dataEditComment['website']; ?>"/>
									</div>
								</div>
							<?php } ?>

						</div>
						<?php
						if (isset($this->item->fields['rating']) && $this->item->fields['rating']->canView())
						{
							echo '<div class="comment-rating span4">';
							echo $this->item->fields['rating']->getOutput(array("view" => "details", "template" => $this->template, "type" => "comment_form", "comment_object" => $this->commentObj));
							echo '</div>';
						}
						?>
					</div>

					<div class="comment-body">
						<div class="comment-row">
							<label for="comment-edit-editor-<?php echo $this->commentObj->id ?>"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT'); ?><span class="required">*</span></label>
						</div>
						<div class="comment-row">
								<textarea class="required validate-comment comment-editor"
									id="comment-edit-editor-<?php echo $this->commentObj->id; ?>"
									name="comment" rows="8"><?php echo $dataEditComment['comment']; ?></textarea>
						</div>
						<?php
						if ($this->commentObj->is_subscriber)
						{
							?>
							<div class="comment-row">
								<label class="comment-subscribe-lbl"
								       for="comment-subscribe-<?php echo $this->commentObj->id ?>">
									<input name="subscribe" id="comment-subscribe-<?php echo $this->commentObj->id ?>"
									       class="comment-subscribe" checked="checked"
									       type="checkbox" value="1"/>
									<?php echo JText::_('COM_JUDOWNLOAD_COMMENT_SUBSCRIBE'); ?>
								</label>
							</div>
						<?php
						}
						else
						{
							if ($this->params->get('can_subscribe_own_comment', 1))
							{
								?>
								<div class="comment-row">
									<label class="comment-subscribe-lbl"
									       for="comment-subscribe-<?php echo $this->commentObj->id ?>">
										<input name="subscribe" id="comment-subscribe-<?php echo $this->commentObj->id ?>"
										       class="comment-subscribe" type="checkbox" value="1"/>
										<?php echo JText::_('COM_JUDOWNLOAD_COMMENT_SUBSCRIBE'); ?>
									</label>
								</div>
							<?php
							}
						}
						?>
					</div>

					<div class="comment-form-submit clearfix">
						<button type="button" class="btn btn-primary"
							onclick="Joomla.submitbutton('document.updateComment', 'judl-comment-edit-form-<?php echo $this->commentObj->id; ?>');"><?php echo JText::_('COM_JUDOWNLOAD_SUBMIT'); ?>
						</button>
						<button id="cancel-edit-comment-<?php echo $this->commentObj->id; ?>" name="comment-reset"
						        class="btn btn-danger cancel-edit-comment"><?php echo JText::_('COM_JUDOWNLOAD_CANCEL'); ?></button>
					</div>

					<div>
						<input name="task" value="document.updateComment" type="hidden"/>
						<input type="hidden" name="comment_id" value="<?php echo $this->commentObj->id?>" />
						<input type="hidden" name="doc_id" value="<?php echo $this->commentObj->doc_id?>" />
						<?php echo JHtml::_('form.token'); ?>
					</div>
				</div>
			</div>
		</fieldset>
	</form>
</div>