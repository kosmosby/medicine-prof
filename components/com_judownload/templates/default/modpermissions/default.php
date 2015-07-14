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
<div id="judl-container" class="jubootstrap component judl-container view-modpermissions">
	<h2 class="judl-view-title"><?php echo JText::_('COM_JUDOWNLOAD_MODERATOR_PERMISSIONS'); ?></h2>
	<?php
	if (count($this->items) > 0)
	{
		?>
		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<th class="center">
					<?php echo JText::_('COM_JUDOWNLOAD_CATEGORIES'); ?>
				</th>

				<th style="width: 80px" class="center">
					<?php echo JText::_('COM_JUDOWNLOAD_VIEW_DETAIL'); ?>
				</th>
			</tr>
			</thead>

			<tbody>
			<?php foreach ($this->items AS $i => $item)
			{
				?>
				<tr>
					<td>
						<?php
						echo $item->assignedCategories;
						?>
					</td>

					<td class="center">
						<a href="<?php echo JRoute::_(JUDownloadHelperRoute::getModeratorPermissionRoute($item->id)); ?>"
						   title="<?php echo JText::_('COM_JUDOWNLOAD_VIEW_PERMISSION_DETAIL'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_DETAIL'); ?></a>
					</td>
				</tr>
			<?php
			} ?>
			</tbody>
		</table>
	<?php
	} ?>
</div>