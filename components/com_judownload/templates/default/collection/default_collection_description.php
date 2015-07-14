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

<div class="judl-collection-info clearfix">
	<?php if (isset($this->item->icon_url) && $this->item->icon_url)
	{
		?>
		<div class="collection-image">
			<img src="<?php echo $this->item->icon_url; ?>"
			     width="<?php echo $this->width; ?>"
			     height="<?php echo $this->height; ?>"
			     alt="<?php echo htmlentities($this->item->title, ENT_QUOTES); ?>"/>
		</div>
	<?php
	} ?>

	<h2 class="collection-title">
		<?php echo $this->item->title; ?>
	</h2>

	<?php
	if ($this->item->description)
	{
		?>
		<div class="collection-desc">
			<?php echo $this->item->description; ?>
		</div>
	<?php
	}
	?>
</div>