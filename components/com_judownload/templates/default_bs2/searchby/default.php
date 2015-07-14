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

<div id="judl-container" class="jubootstrap component judl-container view-searchby <?php echo $this->pageclass_sfx; ?>">
	<?php
	if ($this->params->get('show_page_heading') && $this->params->get('page_heading'))
	{
		?>
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	<?php
	} ?>

	<h2><?php echo JText::sprintf('COM_JUDOWNLOAD_SEARCH_BY_FIELD_X_VALUE_Y', $this->item->getCaption(), $this->text); ?></h2>
	<?php
	if (count($this->items))
	{
		?>
		<div class="results-counter">
			<?php echo $this->pagination->getResultsCounter(); ?>
		</div>
		<?php
		echo $this->loadTemplate('documents');
	}
	?>
</div>
