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
<div id="judl-container" class="jubootstrap component judl-container view-advsearch <?php echo $this->pageclass_sfx; ?>">
	<?php
	$app = JFactory::getApplication();
	if ($app->input->getInt('advancedsearch', 0) || !is_null($app->input->get('limitstart')))
	{
		?>

		<div class="pull-right">
			<a class="hasTooltip btn btn-default" title="<?php echo JText::_('COM_JUDOWNLOAD_BACK_TO_SEARCH_FORM'); ?>" href="<?php echo JRoute::_(JUDownloadHelperRoute::getAdvsearchRoute()); ?>">
				<i class="fa fa-undo"></i>
			</a>
		</div>

		<h2><?php echo JText::_('COM_JUDOWNLOAD_SEARCH_RESULTS'); ?></h2>

		<?php
		if (!count($this->items))
		{
			?>
			<div class="alert alert-no-items"><?php echo JText::_('COM_JUDOWNLOAD_NO_ITEM_FOUND'); ?></div>
		<?php
		} ?>

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
	}
	else
	{
		echo $this->loadTemplate('form');
	}
	?>
</div>
