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
$totalChangeLogs = count($this->item->changelogs);
//Show the last changelog
$changelogItem = $this->item->changelogs[0];
?>
<div class="changelogs clearfix">
	<h3 class="title">
		<?php echo JText::_('COM_JUDOWNLOAD_CHANGELOGS'); ?>
		<?php
		if ($totalChangeLogs > 1)
		{
			?>
				<span class="see-all">
					<i class="fa fa-chevron-right"></i>
					<a href="<?php echo JRoute::_(JUDownloadHelperRoute::getDocumentRoute($this->item->id) . '&layout=changelogs'); ?>" class="">
						<?php
						echo JText::_('COM_JUDOWNLOAD_SEE_COMPLETE_CHANGELOGS');
						?>
					</a>
				</span>
		<?php
		}
		?>
	</h3>

	<div class="changelogs-wrapper">
		<div class="changelog-row">
			<div class="changelog-head">
				<span class="changelog-version"><i
						class="fa fa-info-circle"></i> <?php echo "<span>" . JText::_('COM_JUDOWNLOAD_FIELD_VERSION') . ":</span> " . $changelogItem->version; ?></span>
				<span class="changelog-date"><i
						class="fa fa-calendar"></i> <?php echo "<span>" . JText::_('COM_JUDOWNLOAD_FIELD_DATE') . ":</span> " . $changelogItem->date; ?></span>
			</div>
			<div class="changelog-desc"><?php echo $changelogItem->description; ?></div>
		</div>
	</div>
</div>