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
<div id="judl-container"
     class="jubootstrap component judl-container judl-doc-view doc-default cat-id-<?php echo $this->item->cat_id; ?> <?php echo isset($this->tl_catid) ? 'tlcat-id-' . $this->tl_catid : ""; ?> <?php echo $this->item->class_sfx; ?> <?php echo $this->pageclass_sfx; ?>">
	<h2 class="doc-title">
		<span><?php echo $this->item->fields['title']->getOutput(array("view" => "details", "template" => $this->template)); ?></span>
	</h2>

	<div class="changelogs clearfix">
		<h3 class="title"><?php echo JText::_('COM_JUDOWNLOAD_CHANGELOGS'); ?></h3>
		<?php foreach ($this->item->changelogs AS $changelogItem)
		{
			?>
			<div class="changelog-row">
				<div class="changelog-head">
					<span class="changelog-version"><i
							class="fa fa-info-circle"></i> <?php echo "<span>" . JText::_('COM_JUDOWNLOAD_FIELD_VERSION') . ":</span> " . $changelogItem->version; ?></span>
					<span class="changelog-date"><i
							class="fa fa-calendar"></i> <?php echo "<span>" . JText::_('COM_JUDOWNLOAD_FIELD_DATE') . ":</span> " . $changelogItem->date; ?></span>
				</div>
				<div class="changelog-desc"><?php echo $changelogItem->description; ?></div>
			</div>
		<?php
		} ?>
	</div>
</div>