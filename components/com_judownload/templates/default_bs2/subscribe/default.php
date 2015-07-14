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
		if (task == 'subscribe.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};
</script>

<div id="judl-container" class="jubootstrap component judl-container view-subscribe">
	<h4><?php echo JText::sprintf("COM_JUDOWNLOAD_SUBSCRIBE_DOCUMENT_X", $this->document->title); ?></h4>
	<hr/>
	<form method="POST" action="#" name="adminForm" id="adminForm" class="form-validate form-horizontal">
		<div class="control-group">
			<label class="control-label" for="inputUsername">
				<?php echo JText::_('COM_JUDOWNLOAD_NAME'); ?>
				<span style="color: red">*</span>
			</label>

			<div class="controls">
				<input type="text" class="required" name="jform[username]" value="" id="inputUsername" size="32"/>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="inputEmail">
				<?php echo JText::_('COM_JUDOWNLOAD_EMAIL'); ?>
				<span style="color: red">*</span>
			</label>

			<div class="controls">
				<input type="text" class="required email" name="jform[email]" value="" id="inputEmail" size="32"/>
			</div>
		</div>

		<?php echo JUDownloadFrontHelperCaptcha::getCaptcha(); ?>

		<div class="form-group">
			<label class="control-label col-sm-2"></label>

			<div class="col-sm-10">
				<button type="button" class="btn btn-default btn-primary" onclick="Joomla.submitbutton('subscribe.save')">
					<?php echo JText::_('COM_JUDOWNLOAD_SUBMIT'); ?>
				</button>
				<button type="button" class="btn btn-default"  onclick="Joomla.submitbutton('subscribe.cancel')">
					<?php echo JText::_('COM_JUDOWNLOAD_CANCEL'); ?>
				</button>
			</div>
		</div>

		<div>
			<?php echo JHtml::_('form.token'); ?>
			<input type="hidden" name="jform[doc_id]" value="<?php echo $this->docId; ?>" />
			<input type="hidden" name="task" value="" />
		</div>
	</form>
</div>