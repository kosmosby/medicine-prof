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
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
?>

<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'report.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};
</script>

<div id="judl-container" class="jubootstrap component judl-container view-report">
	<?php if (isset($this->comment))
	{
		?>
		<h2><?php echo JText::sprintf("COM_JUDOWNLOAD_REPORT_COMMENT_X", $this->comment->title); ?></h2>
	<?php
	}
	else
	{
		?>
		<h2><?php echo JText::sprintf("COM_JUDOWNLOAD_REPORT_DOCUMENT_X", $this->document->title); ?></h2>
	<?php
	} ?>
	<hr/>
	<form method="POST" name="adminForm" id="adminForm" action="#" class="form-validate form-horizontal">
		<?php
		if ($this->user->get('guest'))
		{
			?>
			<div class="control-group">
				<label class="control-label" for="report-username">
					<?php echo JText::_('COM_JUDOWNLOAD_NAME'); ?>
					<span class="star">*</span>
				</label>

				<div class="controls">
					<input type="text" class="required" name="jform[username]" value="" id="report-username"/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="report-email">
					<?php echo JText::_('COM_JUDOWNLOAD_EMAIL'); ?>
					<span class="star">*</span>
				</label>

				<div class="controls">
					<input type="text" class="required email" name="jform[email]" value="" id="report-email"/>
				</div>
			</div>
		<?php
		}
		else
		{
			?>
			<div class="control-group">
				<label class="control-label" for="report-username">
					<?php echo JText::_('COM_JUDOWNLOAD_NAME'); ?>
					<span class="star">*</span>
				</label>

				<div class="controls">
					<input type="text" class="required" name="jform[username]"
					       value="<?php echo $this->user->name; ?>" id="report-username" readonly="readonly"/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="report-email">
					<?php echo JText::_('COM_JUDOWNLOAD_EMAIL'); ?>
					<span class="star">*</span>
				</label>

				<div class="controls">
					<input type="text" class="required email" name="jform[email]"
					       value="<?php echo $this->user->email; ?>" id="report-email" readonly="readonly"/>
				</div>
			</div>
		<?php
		}
		?>

		<div class="control-group">
			<label class="control-label" for="report-subject"><?php echo JText::_('COM_JUDOWNLOAD_SUBJECT'); ?>
				<span class="star">*</span>
			</label>

			<div class="controls">
				<?php
				if (count($this->subject) > 0)
				{
					$beginSubject  = array('' => JText::_('COM_JUDOWNLOAD_SELECT'));
					$otherSubject  = array('other' => JText::_('COM_JUDOWNLOAD_OTHER'));
					$reportSubject = array_merge($beginSubject, $this->subject, $otherSubject);
					?>
					<select name="jform[subject]" class="subject required" id="report-subject">
						<?php echo JHtml::_('select.options', $reportSubject, 'value', 'text', ''); ?>
					</select>
				<?php
				}
				else
				{
					?>
					<input type="text" name="jform[subject]" class="subject required" id="report-subject"/>
				<?php
				}
				?>
			</div>
		</div>

		<div class="control-group" id="other" style="display:none">
			<label class="control-label" for="report-other-subject">
				<?php echo JText::_('COM_JUDOWNLOAD_OTHER_SUBJECT'); ?>
				<span class="star">*</span>
			</label>

			<div class="controls">
				<input type="text" name="jform[other-subject]" class="other-subject" id="report-other-subject"/>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="report-content">
				<?php echo JText::_('COM_JUDOWNLOAD_CONTENT'); ?>
				<span class="star">*</span>
			</label>

			<div class="controls">
				<textarea name="jform[report]" cols="7" rows="5" class="required" id="report-content"></textarea>
			</div>
		</div>

		<?php
		if ($this->requireCaptcha)
		{
			?>
			<?php echo JUDownloadFrontHelperCaptcha::getCaptcha(); ?>
		<?php
		}
		?>
		<div class="control-group">
			<label class="control-label">
			</label>

			<div class="controls">
				<button type="button" class="btn btn-primary submit-report"
				        onclick="Joomla.submitbutton('report.save')"><?php echo JText::_('COM_JUDOWNLOAD_SUBMIT'); ?></button>
				<button type="button" class="btn cancel submit-report"
				        onclick="Joomla.submitbutton('report.cancel')"><?php echo JText::_('COM_JUDOWNLOAD_CANCEL'); ?></button>
			</div>
		</div>

		<div>
			<input type="hidden" name="jform[doc_id]" value="<?php echo $this->docId; ?>"/>
			<?php
			if ($this->commentId > 0)
			{
				?>
				<input type="hidden" name="jform[comment_id]" value="<?php echo $this->commentId; ?>"/>
			<?php
			}
			?>
			<input type="hidden" name="task" value=""/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>