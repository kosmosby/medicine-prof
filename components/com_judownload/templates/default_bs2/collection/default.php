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

$item_class = "";
if ($this->item->featured)
{
	$item_class .= " featured";
}
?>
<div id="judl-container" class="jubootstrap component judl-container view-collection <?php echo $item_class; ?>">
	<div class="pull-right">
		<?php if (isset($this->edit_link))
		{
			?>
			<a class="hasTooltip btn" href="<?php echo $this->edit_link; ?>"
			   title="<?php echo JText::_("COM_JUDOWNLOAD_EDIT"); ?>">
				<i class="fa fa-edit"></i>
			</a>
		<?php
		} ?>

		<?php
		if (isset($this->delete_link))
		{
			?>
			<a class="hasTooltip btn" href="<?php echo $this->delete_link; ?>"
			   title="<?php echo JText::_("COM_JUDOWNLOAD_DELETE"); ?>">
				<i class="fa fa-trash-o"></i>
			</a>
		<?php
		} ?>

		<?php
		if ($this->show_feed)
		{
			?>
			<a class="hasTooltip btn" href="<?php echo $this->rss_link; ?>"
			   title="<?php echo JText::_('COM_JUDOWNLOAD_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>">
				<i class="fa fa-rss"></i>
			</a>
		<?php
		}
		?>
	</div>

	<?php
	echo $this->loadTemplate('collection_description');

	if (count($this->items))
	{
		echo $this->loadTemplate('documents');
	}
	?>
</div>