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
<div class="navigation clearfix">
	<ul class="pager">
		<?php
		if ($this->item->prev_item)
		{
			?>
			<li class="pull-left">
				<a href="<?php echo $this->item->prev_item->link; ?>"
				   title="<?php echo $this->item->prev_item->title ?>"><?php echo JText::_('COM_JUDOWNLOAD_PREV'); ?></a>
			</li>
		<?php
		} ?>

		<?php
		if ($this->item->next_item)
		{
			?>
			<li class="pull-right">
				<a href="<?php echo $this->item->next_item->link; ?>"
				   title="<?php echo $this->item->next_item->title ?>"><?php echo JText::_('COM_JUDOWNLOAD_NEXT'); ?></a>
			</li>
		<?php
		} ?>
	</ul>
</div>