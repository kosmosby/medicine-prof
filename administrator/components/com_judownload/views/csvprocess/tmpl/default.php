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

JHtml::_('behavior.multiselect');
JHtml::_('behavior.tooltip');
?>
<div class="jubootstrap">
	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<div id="position-icon">
		<?php if (JUDownloadHelper::checkGroupPermission("csvprocess.import"))
		{
			?>
			<div class="cpanel">
				<div class="icon-wrapper" style="width: auto">
					<div class="icon">
						<a href="index.php?option=com_judownload&view=csvprocess&layout=import">
							<img alt="<?php echo JText::_('COM_JUDOWNLOAD_CSV_IMPORT'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/csv-import.png" />
							<span><?php echo JText::_('COM_JUDOWNLOAD_CSV_IMPORT'); ?></span>
						</a>
					</div>
				</div>
			</div>
		<?php
		} ?>

		<?php if (JUDownloadHelper::checkGroupPermission("csvprocess.export"))
		{
			?>
			<div class="cpanel">
				<div class="icon-wrapper" style="width: auto">
					<div class="icon">
						<a href="index.php?option=com_judownload&view=csvprocess&layout=export">
							<img alt="<?php echo JText::_('COM_JUDOWNLOAD_CSV_EXPORT'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/csv-export.png" />
							<span><?php echo JText::_('COM_JUDOWNLOAD_CSV_EXPORT'); ?></span>
						</a>
					</div>
				</div>
			</div>
		<?php
		} ?>
	</div>
</div>