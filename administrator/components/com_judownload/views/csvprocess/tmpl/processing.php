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
			<legend><?php echo JText::_('COM_JUDOWNLOAD_IMPORT_CSV_PROCESS'); ?></legend>

			<div id="process_info" class="span6">
				<span id="process_state"><?php echo JText::_("COM_JUDOWNLOAD_IMPORT_CSV_PROCESSING"); ?></span> <span id="processed" style="display: none;"></span>
				<span id="total" style="display: none;"></span>
			</div>
			<div class="progress progress-striped span12">
				<div class="bar" id="bar"></div>
			</div>
			<div class="span6">
				<ul id="import_messages">
				</ul>
			</div>
		</fieldset>
		<input type="hidden" name="task" value="" />
	</form>
</div>
