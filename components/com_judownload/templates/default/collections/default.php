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

<div id="judl-container" class="jubootstrap component judl-container view-collections">
	<?php
	if (isset($this->createLink))
	{
		?>
		<div class="pull-right">
			<a class="btn btn-default hasTooltip" href="<?php echo $this->createLink; ?>"
			   title="<?php echo JText::_('COM_JUDOWNLOAD_ADD_COLLECTION'); ?>">
				<i class="fa fa-file-o"></i>
			</a>
		</div>
	<?php
	}
	?>

	<h2>
		<?php echo JText::sprintf('COM_JUDOWNLOAD_COLLECTIONS_CREATED_BY', $this->collectionUserName); ?>
	</h2>

	<?php
	if (count($this->items))
	{
		echo $this->loadTemplate('collections');
	}
	?>
</div>