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
<div id="judl-container" class="jubootstrap component judl-container view-tag <?php echo $this->pageclass_sfx; ?>">
	<div class="pull-right">
		<?php
		if ($this->show_feed)
		{
			?>
			<a class="hasTooltip btn btn-default" href="<?php echo $this->rss_link; ?>"
			   title="<?php echo JText::_('COM_JUDOWNLOAD_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>">
				<i class="fa fa-rss"></i>
			</a>
		<?php
		}
		?>
	</div>

	<?php
	if ($this->params->get('show_page_heading') && $this->params->get('page_heading'))
	{
		?>
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	<?php
	} ?>

	<div class="judl-tag-info clearfix">
		<h2 class="tag-title"><?php echo JText::sprintf('COM_JUDOWNLOAD_TAG_X', $this->tag->title); ?></h2>

		<div class="tag-desc">
			<?php echo $this->tag->description; ?>
		</div>
	</div>

	<?php
	if (count($this->items))
	{
		echo $this->loadTemplate('documents');
	}
	?>
</div>