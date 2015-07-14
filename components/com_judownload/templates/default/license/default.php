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
<div id="judl-container" class="jubootstrap component judl-container view-license">
	<h2 class="license-title"><?php echo $this->item->title; ?></h2>
	<?php
		echo $this->item->event->afterDisplayTitle;
	?>

	<?php
	echo $this->item->event->beforeDisplayContent;
	?>

	<div class="license-desc">
		<?php echo $this->item->description; ?>
	</div>

	<?php
		echo $this->item->event->afterDisplayContent;
	?>
</div>