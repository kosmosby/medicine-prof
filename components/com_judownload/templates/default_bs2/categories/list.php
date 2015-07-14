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
<div id="judl-container" class="jubootstrap component judl-container view-categories layout-list <?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading'))
	{
		?>
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	<?php
	}

	if ($this->params->get('all_categories_show_category_title', 1))
	{
		?>
		<h2 class="cat-title"><?php echo $this->category->title; ?></h2>
	<?php
	}

	if ($this->category->total_childs > 0)
	{
		echo $this->loadTemplate('categories');
	}
	?>
</div>		
