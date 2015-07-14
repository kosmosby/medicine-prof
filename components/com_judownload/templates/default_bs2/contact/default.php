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
			<div class="control-group">
				<?php echo $field->label; ?>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php
		} ?>

		<?php
		if ($this->requireCaptcha)
		{
			echo JUDownloadFrontHelperCaptcha::getCaptcha();
		} ?>

		<div class="control-group">
			<label class="control-label"></label>

			<div class="controls">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('contact.send')">
					<?php echo JText::_('COM_JUDOWNLOAD_SUBMIT'); ?>
				</button>
				<button type="button" class="btn" onclick="Joomla.submitbutton('contact.cancel')">
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