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
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE', true); ?>");
		}
		else
		{
			form.installtype.value = 'upload';
			form.submit();
		}
	};

	Joomla.submitbutton3 = function()
	{
		var form = document.getElementById('adminForm');

		
		if (form.install_directory.value == ""){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_DIRECTORY', true); ?>");
		}
		else
		{
			form.installtype.value = 'folder';
			form.submit();
		}
	};

	Joomla.submitbutton4 = function()
	{
		var form = document.getElementById('adminForm');

		
		if (form.install_url.value == "" || form.install_url.value == "http://"){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL', true); ?>");
		}
		else
		{
			form.installtype.value = 'url';
			form.submit();
		}
	};
</script>

<div class="jubootstrap">

	<div id="iframe-help"></div>

	<form action="#" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-horizontal">
		<div class="width-70 fltlft">
			<fieldset class="uploadform">
				<legend><?php echo JText::_('COM_JUDOWNLOAD_UPLOAD_PACKAGE_FILE'); ?></legend>
				<label for="install_package"><?php echo JText::_('COM_JUDOWNLOAD_PACKAGE_FILE'); ?></label>
				<input class="input_box" id="install_package" name="install_package" type="file" size="57" />
				<input class="button" type="button" value="<?php echo JText::_('COM_JUDOWNLOAD_UPLOAD_AND_INSTALL'); ?>" onclick="Joomla.submitbutton()" />
			</fieldset>
			<div class="clr"></div>
			<fieldset class="uploadform">
				<legend><?php echo JText::_('COM_JUDOWNLOAD_INSTALL_FROM_DIRECTORY'); ?></legend>
				<label for="install_directory"><?php echo JText::_('COM_JUDOWNLOAD_INSTALL_DIRECTORY'); ?></label>
				<?php
					$app = JFactory::getApplication();
					$value = $app->getUserState('com_installer.install.install_directory', $app->get('tmp_path'));
				?>
				<input type="text" id="install_directory" name="install_directory" class="input_box" size="70" value="<?php echo $value; ?>" />			</fieldset>
			<div class="clr"></div>
			<fieldset class="uploadform">
				<legend><?php echo JText::_('COM_JUDOWNLOAD_INSTALL_FROM_URL'); ?></legend>
				<label for="install_url"><?php echo JText::_('COM_JUDOWNLOAD_INSTALL_URL'); ?></label>
				<input type="text" id="install_url" name="install_url" class="input_box" size="70" value="http://" />
				<input type="button" class="button" value="<?php echo JText::_('COM_JUDOWNLOAD_INSTALL_BUTTON'); ?>" onclick="Joomla.submitbutton4()" />
			</fieldset>
			<input type="hidden" name="installtype" value="upload" />
			<input type="hidden" name="task" value="plugin.install" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>