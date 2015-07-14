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
		console.log(task);
		if (task == 'contact.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};
</script>

<div id="judl-container" class="jubootstrap component judl-container view-contact">
	<h4><?php echo JText::_('COM_JUDOWNLOAD_CONTACT_DOCUMENT_OWNER') . ': ' . $this->document->title; ?></h4>
	<hr/>
	<form method="POST" action="#" name="adminForm" id="adminForm" class="form-validate form-horizontal">
		<?php foreach ($this->form->getFieldset('contact') AS $key => $field)
		{
			?>
			<div class="form-group">
				<div class="col-sm-2">
					<?php echo $field->label; ?>
				</div>
				<div class="col-sm-10">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php
		} ?>

		<?php
		if ($this->requireCaptcha)
		{
			?>
			<div class="form-group">
				<label for="security_code" class="control-label col-sm-2">
					<?php echo JText::_('COM_JUDOWNLOAD_CAPTCHA'); ?><span style="color: red">*</span>
				</label>
				<div class="col-sm-10">
					<?php echo JUDownloadFrontHelperCaptcha::getCaptcha(false, null, false); ?>
				</div>
			</div>
			<?php
		} ?>

		<div class="form-group">
			<label class="control-label col-sm-2"></label>

			<div class="col-sm-10">
				<button type="button" class="btn btn-default btn-primary" onclick="Joomla.submitbutton('contact.send')">
					<?php echo JText::_('COM_JUDOWNLOAD_SUBMIT'); ?>
				</button>
				<button type="button" class="btn btn-default" onclick="Joomla.submitbutton('contact.cancel')">
					<?php echo JText::_('COM_JUDOWNLOAD_CANCEL'); ?>
				</button>
			</div>
		</div>

		<div>
			<?php if ($this->docId > 0) : ?>
				<input type="hidden" name="jform[doc_id]" value="<?php echo $this->docId ?>"/>
			<?php endif ?>
			<input type="hidden" name="task" value=""/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>