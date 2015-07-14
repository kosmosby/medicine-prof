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

<div class="judl-cat-info clearfix <?php echo $this->category->featured ? 'cat-featured' : ''; ?>">
	<?php if (isset($this->category->images->detail_image) && $this->category->images->detail_image && $this->params->get('category_show_image', 1))
	{
		?>
		<div class="cat-image">
			<img src="<?php echo $this->category->images->detail_image_src; ?>"
			     width="<?php echo $this->category->images->detail_image_width; ?>px"
			     height="<?php echo $this->category->images->detail_image_height; ?>px"
			     title="<?php echo $this->category->images->detail_image_caption; ?>"
			     alt="<?php echo $this->category->images->detail_image_alt; ?>"/>
		</div>
	<?php
	} ?>

	<h2 class="cat-title"><?php echo $this->category->title; ?></h2>

	<?php
	if ($this->category->params->get('show_description', 1) && $this->category->description)
	{
		?>
		<div class="cat-desc">
			<?php echo $this->category->description; ?>
		</div>
	<?php
	}
	?>
</div>