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
<script type="text/javascript">
	Joomla.submitbutton = function()
	{
		var form = document.getElementById('adminForm');

		if (form.install_package.value == ""){
			alert("<?php echo JText::_('COM_JUDOWNLOAD_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE', true); ?>");
		}
		else
		{
			jQuery('#loading').css('display', 'block');

			form.installtype.value = 'upload';
			form.submit();
		}
	};

	Joomla.submitbutton3 = function()
	{
		var form = document.getElementById('adminForm');

		
		if (form.install_directory.value == ""){
			alert("<?php echo JText::_('COM_JUDOWNLOAD_MSG_INSTALL_PLEASE_SELECT_A_DIRECTORY', true); ?>");
		}
		else
		{
			jQuery('#loading').css('display', 'block');

			form.installtype.value = 'folder';
			form.submit();
		}
	};

	Joomla.submitbutton4 = function()
	{
		var form = document.getElementById('adminForm');

		
		if (form.install_url.value == "" || form.install_url.value == "http://"){
			alert("<?php echo JText::_('COM_JUDOWNLOAD_MSG_INSTALL_ENTER_A_URL', true); ?>");
		}
		else
		{
			jQuery('#loading').css('display', 'block');

			form.installtype.value = 'url';
			form.submit();
		}
	};

	
	jQuery(document).ready(function($) {
		var outerDiv = $('#adminForm');

		$('<div id="loading"></div>')
			.css("background", "rgba(255, 255, 255, .8) url('../media/jui/img/ajax-loader.gif') 50% 15% no-repeat")
			.css("top", outerDiv.position().top - $(window).scrollTop())
			.css("left", outerDiv.position().left - $(window).scrollLeft())
			.css("width", outerDiv.width())
			.css("height", outerDiv.height())
			.css("position", "fixed")
			.css("opacity", "0.80")
			.css("-ms-filter", "progid:DXImageTransform.Microsoft.Alpha(Opacity = 80)")
			.css("filter", "alpha(opacity = 80)")
			.css("display", "none")
			.appendTo(outerDiv);
	});
</script>

<div class="jubootstrap">

	<div id="iframe-help"></div>

	<form action="#" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'upload')); ?>

		<?php JDispatcher::getInstance()->trigger('onInstallerViewBeforeFirstTab', array()); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'upload', JText::_('COM_JUDOWNLOAD_UPLOAD_PACKAGE_FILE', true)); ?>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('COM_JUDOWNLOAD_UPLOAD_INSTALL_JUDOWNLOAD_PLUGIN'); ?></legend>
			<div class="control-group">
				<label for="install_package" class="control-label"><?php echo JText::_('COM_JUDOWNLOAD_PACKAGE_FILE'); ?></label>
				<div class="controls">
					<input class="input_box" id="install_package" name="install_package" type="file" size="57" />
				</div>
			</div>
			<div class="form-actions">
				<input class="btn btn-primary" type="button" value="<?php echo JText::_('COM_JUDOWNLOAD_UPLOAD_AND_INSTALL'); ?>" onclick="Joomla.submitbutton()" />
			</div>
		</fieldset>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'directory', JText::_('COM_JUDOWNLOAD_INSTALL_FROM_DIRECTORY', true)); ?>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('COM_JUDOWNLOAD_INSTALL_FROM_DIRECTORY'); ?></legend>
			<div class="control-group">
				<label for="install_directory" class="control-label"><?php echo JText::_('COM_JUDOWNLOAD_INSTALL_DIRECTORY'); ?></label>
				<div class="controls">
					<?php
						$app = JFactory::getApplication();
						$value = $app->getUserState('com_installer.install.install_directory', $app->get('tmp_path'));
					?>
					<input type="text" id="install_directory" name="install_directory" class="span5 input_box" size="70" value="<?php echo $value; ?>" />
				</div>
			</div>
			<div class="form-actions">
				<input type="button" class="btn btn-primary" value="<?php echo JText::_('COM_JUDOWNLOAD_INSTALL_BUTTON'); ?>" onclick="Joomla.submitbutton3()" />
			</div>
		</fieldset>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'url', JText::_('COM_JUDOWNLOAD_INSTALL_FROM_URL', true)); ?>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('COM_JUDOWNLOAD_INSTALL_FROM_URL'); ?></legend>
			<div class="control-group">
				<label for="install_url" class="control-label"><?php echo JText::_('COM_JUDOWNLOAD_INSTALL_URL'); ?></label>
				<div class="controls">
					<input type="text" id="install_url" name="install_url" class="span5 input_box" size="70" value="http://" />
				</div>
			</div>
			<div class="form-actions">
				<input type="button" class="btn btn-primary" value="<?php echo JText::_('COM_JUDOWNLOAD_INSTALL_BUTTON'); ?>" onclick="Joomla.submitbutton4()" />
			</div>
		</fieldset>

		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php JDispatcher::getInstance()->trigger('onInstallerViewAfterLastTab', array()); ?>

		<input type="hidden" name="task" value="plugin.install" />
		<input type="hidden" name="installtype" value="upload" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>