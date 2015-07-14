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
	class="jubootstrap component judl-container view-category layout-list category-<?php echo $this->category->id; ?> <?php echo isset($this->tl_catid) ? 'tlcat-id-' . $this->tl_catid : ""; ?> <?php echo $this->category->class_sfx; ?> <?php echo $this->pageclass_sfx; ?>">
	<div class="pull-right">
		<?php
			echo $this->loadTemplate('buttons');
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
	}

	echo $this->loadTemplate('category_description');

	if (count($this->related_cats))
	{
		echo $this->loadTemplate('related_categories');
	}

	if (count($this->subcategories))
	{
		echo $this->loadTemplate('sub_categories');
	}

	if (count($this->items) && $this->category->show_item)
	{
		echo $this->loadTemplate('documents');
	}
	?>
</div>