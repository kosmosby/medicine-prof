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
<div class="pagination-wrap clearfix">
	<div class="limitbox">
		<div class="display-number">
			<?php echo JText::_('COM_JUDOWNLOAD_PAGINATION_DISPLAY'); ?>
		</div>
		<?php echo $this->pagination->getLimitBox(); ?>
	</div>

	<?php
	if ($this->pagination->get('pages.total') > 1)
	{
		?>
		<div class="pages-links">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>

		<div class="counter">
			<?php echo $this->pagination->getPagesCounter(); ?>
		</div>
	<?php
	} ?>
</div>