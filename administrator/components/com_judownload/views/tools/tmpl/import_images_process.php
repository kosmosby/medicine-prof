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

<div class="jubootstrap">
	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<form id="adminForm" name="adminForm" action="#" method="post">
		<fieldset class="row-fluid">
			<legend><?php echo JText::_('COM_JUDOWNLOAD_PROCESS'); ?></legend>
			<div id="process_info" style="display: none;" class="span6">
				<?php echo JText::_("COM_JUDOWNLOAD_PROCESSED"); ?> <span id="processed"></span> /
				<span id="total"></span>
			</div>
			<div class="progress progress-striped span12">
				<div class="bar" id="bar"></div>
			</div>
			<div class="span6">
				<ul id="import_image_errors">
				</ul>
			</div>
		</fieldset>
		<input type="hidden" name="task" value="" />
	</form>
</div>
