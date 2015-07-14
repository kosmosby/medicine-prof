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
<div id="judl-comments" class="judl-comments clearfix">
<?php
if ($this->item->params->get('access-comment'))
{
	?>
	<div class="comment-form-container clearfix">
		<form name="judl-comment-form" id="judl-comment-form" class="form-validate comment-form form-horizontal" method="post" action="">
			<fieldset>
				<legend><?php echo JText::_('COM_JUDOWNLOAD_LEAVE_COMMENT'); ?></legend>
				<div class="judl-comment-wrapper clearfix">
					<!-- div.judl-comment -->
					<div class="comment-message-container"></div>

					<div class="judl-comment">
						<p class="note-required">
							<?php echo JText::sprintf('COM_JUDOWNLOAD_ALL_FIELDS_HAVE_STAR_ARE_REQUIRED', '<span class="required">*</span>'); ?>
						</p>

						<div class="comment-header">
							<div class="comment-user-info col-md-8">
								<div class="form-group">
									<label class="control-label col-sm-2"
									       for="comment-title"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_TITLE'); ?>
										<span class="required">*</span></label>

									<div class="col-sm-10">
										<input name="title" class="required comment-title" id="comment-title"
										       type="text" value="<?php echo $this->title; ?>"/>
									</div>
								</div>

								<?php
								if ($this->user->get('guest'))
								{
									?>
									<div class="form-group">
										<label class="control-label col-sm-2"
										       for="comment-name"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_NAME'); ?>
											<span class="required">*</span></label>

										<div class="col-sm-10">
											<input name="guest_name" class="required" id="comment-name"
											       type="text" value="<?php echo $this->name; ?>"/>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-sm-2"
										       for="comment-email"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_EMAIL'); ?>
											<span class="required">*</span></label>

										<div class="col-sm-10">
											<input name="guest_email" class="email required" id="comment-email"
											       type="text" value="<?php echo $this->email; ?>"/>
										</div>
									</div>
								<?php
								}
								else
								{
									?>
									<div class="form-group">
										<label class="control-label col-sm-2"
										       for="comment-name"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_NAME'); ?>
											<span class="required">*</span></label>

										<div class="col-sm-10">
											<input name="guest_name" type="text" id="comment-name"
											       value="<?php echo $this->user->name; ?>" readonly="readonly"/>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-sm-2"
										       for="comment-email"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_EMAIL'); ?>
											<span class="required ">*</span></label>

										<div class="col-sm-10">
											<input name="guest_email" type="text" id="comment-email"
											       value="<?php echo $this->user->email; ?>" readonly="readonly"/>
										</div>
									</div>
								<?php
								} ?>

								<?php
								if ($this->params->get('filter_comment_language', 0))
								{
									?>
									<div class="form-group">
										<label class="control-label col-sm-2"
										       for="comment-language"><?php echo JText::_('COM_JUDOWNLOAD_LANGUAGE'); ?>
											<span class="required">*</span></label>

										<div class="col-sm-10">
											<select name="comment_language" class="required" id="comment-language">
												<?php echo JHtml::_('select.options', $this->langArray, 'value', 'text', $this->language); ?>
											</select>
										</div>
									</div>
								<?php
								} ?>

								<?php
								if ($this->website_field_in_comment_form)
								{
									$require = ($this->website_field_in_comment_form == 1) ? '' : '<span class=required>*</span>';
									$class   = ($this->website_field_in_comment_form == 1) ? '' : 'required';
									?>
									<div class="form-group">
										<label class="control-label col-sm-2"
										       for="comment-website"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT_WEBSITE'); ?><?php echo $require; ?></label>

										<div class="col-sm-10">
											<input name="website" class="url <?php echo $class; ?>" id="comment-website"
											       type="text" value="<?php echo $this->website; ?>"/>
										</div>
									</div>
								<?php
								}
								?>
							</div>

							<?php
							if (isset($this->item->fields['rating']) && $this->item->fields['rating']->canView())
							{
								echo '<div class="comment-rating col-md-4">';
								echo $this->item->fields['rating']->getOutput(array("view" => "details", "template" => $this->template, "type" => "comment_form", "prefixId"=>"form-"));
								echo '</div>';
							}
							?>
						</div>

						<div class="comment-body">
							<div class="comment-row">
								<label for="comment-editor"><?php echo JText::_('COM_JUDOWNLOAD_COMMENT'); ?>
									<span class="required">*</span></label>
									<textarea name="comment" class="required validate-comment comment-editor form-control" id="comment-editor"
										rows="8"><?php echo $this->comment; ?></textarea>
							</div>
							<?php
							if (JUDLPROVERSION && $this->params->get('can_subscribe_own_comment', 1))
							{
								?>
								<div class="comment-row">
									<label class="comment-subscribe-lbl" for="comment-subscribe">
										<input name="subscribe" class="comment-subscribe" id="comment-subscribe"
										       type="checkbox" value="1"/>
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
					</div>
					<!--end div.judl-comment -->

					<div class="comment-form-submit clearfix">
						<button type="button" class="btn btn-default btn-primary"
							onclick="Joomla.submitbutton('document.addComment', 'judl-comment-form');"><?php echo JText::_('COM_JUDOWNLOAD_SUBMIT'); ?>
						</button>
						<button name="comment-reset" type="reset"
						        class="btn btn-default"><?php echo JText::_('COM_JUDOWNLOAD_RESET'); ?></button>
					</div>

					<div>
						<input name="task" value="" type="hidden"/>
						<input name="parent_id" value="<?php echo $this->root_comment->id; ?>" type="hidden"/>
						<input name="doc_id" value="<?php echo $this->item->id; ?>" type="hidden"/>
						<?php echo JHtml::_('form.token'); ?>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
<?php
}
?>
<input name="min-comment-characters" value="<?php echo $this->min_comment_characters ?>" type="hidden"/>
<input name="max-comment-characters" value="<?php echo $this->max_comment_characters ?>" type="hidden"/>

<?php
if ($this->item->comment->total_comments_no_filter)
{
	?>
	<h3 class="total-comments clearfix"><?php echo JText::plural('COM_JUDOWNLOAD_N_COMMENT', $this->item->comment->total_comments); ?></h3>

	<form name="judl-comment-filter-sort-form" class="comment-filter-sort-form clearfix" method="POST"
	      action="<?php echo JRoute::_(JUDownloadHelperRoute::getDocumentRoute($this->item->id)); ?>">
		<div class="filter-sort pull-right">
			<?php
			if ($this->params->get('filter_comment_language', 0))
			{
				?>
				<select name="filter_lang" onchange="this.form.submit()" class="input-medium">
					<?php echo JHtml::_('select.options', $this->langArray, 'value', 'text', $this->list_lang_comment); ?>
				</select>
			<?php
			}

			if($this->params->get('filter_comment_rating', 1))
			{
				?>
				<select name="star_filter" onchange="this.form.submit()" class="input-small">
					<?php echo JHtml::_('select.options', $this->filter_comment_stars, 'value', 'text', $this->filter_comment_star); ?>
				</select>
			<?php
			}

			if($this->params->get('show_comment_direction', 1))
			{
				?>
				<select name="filter_order" onchange="this.form.submit()" class="input-medium">
					<?php echo JHtml::_('select.options', $this->order_comment_name_array, 'value', 'text', $this->list_order_comment); ?>
				</select>
				<select name="filter_order_Dir" onchange="this.form.submit()" class="input-small">
					<?php echo JHtml::_('select.options', $this->order_comment_dir_array, 'value', 'text', $this->list_dir_comment); ?>
				</select>
			<?php
			}

			if ($this->params->get('show_comment_pagination', 0))
			{
				echo $this->item->comment->pagination->getLimitBox();
			} ?>
		</div>
	</form>
	<?php
	if ($this->item->comment->total_comments > 0)
	{
		echo $this->loadTemplate('comment_default_recursive');
		?>
		<input type="hidden" id="token" value="<?php echo $this->token ?>">
		<?php
		if ($this->item->comment->pagination->total > $this->item->comment->pagination->limit)
		{
			?>
			<div class="pagination clearfix">
				<?php echo $this->item->comment->pagination->getPagesLinks(); ?>
			</div>
		<?php
		}
		?>
	<?php
	}
	?>
<?php
} ?>
</div>
