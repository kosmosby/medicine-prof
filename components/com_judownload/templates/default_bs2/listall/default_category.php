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
<div class="judl-cat clearfix">
	<h2 class="cat-title"><?php echo JText::sprintf('COM_JUDOWNLOAD_LIST_ALL_DOCUMENTS', $this->category->title); ?></h2>
</div>

<div class="clearfix">
	<a class="btn btn-primary btn-mini pull-left" href="#"
	   onclick="javascript:jQuery('.filter-form').slideToggle('300'); return false;">
		<i class="fa fa-filter"></i> <?php echo JText::_('COM_JUDOWNLOAD_FILTER'); ?>
	</a>
	<a class="btn btn-mini pull-right"
	   href="<?php echo JUDownloadHelperRoute::getCategoryRoute($this->category->id); ?>">
		<i class="fa fa-folder-open"></i> <?php echo JText::_('COM_JUDOWNLOAD_THIS_CATEGORY'); ?>
	</a>
</div>