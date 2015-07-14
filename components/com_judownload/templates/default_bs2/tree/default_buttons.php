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
<?php
if ($this->category->can_submit_doc && $this->params->get('show_submit_document_btn_in_category', 1))
{
	echo '<a class="hasTooltip btn" title="' . JText::_('COM_JUDOWNLOAD_ADD_DOCUMENT') . '" href="' . $this->category->submit_doc_link . '"><i class="fa fa-file-o"></i></a>';
}

if ($this->show_feed)
{
	?>
	<a class="hasTooltip btn" href="<?php echo $this->rss_link; ?>"
	   title="<?php echo JText::_('COM_JUDOWNLOAD_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>">
		<i class="fa fa-rss"></i>
	</a>
<?php
}

if ($this->category->id != 1)
{
	?>
	<a class="hasTooltip btn"
	   href="<?php echo JRoute::_(JUDownloadHelperRoute::getCategoryRoute($this->category->parent_id)); ?>"
	   title="<?php echo JText::_('COM_JUDOWNLOAD_UP_TO_PARENT_CATEGORY'); ?>">
		<i class="fa fa-chevron-up"></i>
	</a>
<?php
} ?>