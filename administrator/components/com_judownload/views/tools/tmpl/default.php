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

JHtml::_('behavior.multiselect');
JHtml::_('behavior.tooltip');
?>
<div class="jubootstrap">
	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<div id="position-icon">
		<?php if (JUDownloadHelper::checkGroupPermission("tools.rebuildcategorytree"))
		{
			?>
			<div class="cpanel">
				<div class="icon-wrapper">
					<div class="icon">
						<a href="index.php?option=com_judownload&task=tools.rebuildcategorytree">
							<img alt="<?php echo JText::_('COM_JUDOWNLOAD_REBUILD_CATEGORY_TREE'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/rebuild-tree.png" />
							<span><?php echo JText::_('COM_JUDOWNLOAD_REBUILD_CATEGORY_TREE'); ?></span>
						</a>
					</div>
				</div>
			</div>
		<?php
		} ?>

		<?php if (JUDownloadHelper::checkGroupPermission("tools.rebuildcommenttree"))
		{
			?>
			<div class="cpanel">
				<div class="icon-wrapper">
					<div class="icon">
						<a href="index.php?option=com_judownload&view=tools&layout=rebuildcommenttree">
							<img alt="<?php echo JText::_('COM_JUDOWNLOAD_REBUILD_COMMENT_TREE'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/rebuild-tree.png" />
							<span><?php echo JText::_('COM_JUDOWNLOAD_REBUILD_COMMENT_TREE'); ?></span>
						</a>
					</div>
				</div>
			</div>
		<?php
		} ?>

		<?php if (JUDownloadHelper::checkGroupPermission("tools.resizeimages"))
		{
			?>
			<div class="cpanel">
				<div class="icon-wrapper">
					<div class="icon">
						<a href="index.php?option=com_judownload&view=tools&layout=resizeimages">
							<img alt="<?php echo JText::_('COM_JUDOWNLOAD_RESIZE_IMAGES'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/resize-image.png" />
							<span><?php echo JText::_('COM_JUDOWNLOAD_RESIZE_IMAGES'); ?></span>
						</a>
					</div>
				</div>
			</div>
		<?php
		} ?>

		<?php if (JUDownloadHelper::checkGroupPermission("tools.rebuildrating"))
		{
			?>
			<div class="cpanel">
				<div class="icon-wrapper">
					<div class="icon">
						<a href="index.php?option=com_judownload&view=tools&layout=rebuildrating">
							<img alt="<?php echo JText::_('COM_JUDOWNLOAD_REBUILD_RATING'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/rebuild-rating.png" />
							<span><?php echo JText::_('COM_JUDOWNLOAD_REBUILD_RATING'); ?></span>
						</a>
					</div>
				</div>
			</div>
		<?php
		} ?>

		<?php if (JUDownloadHelper::checkGroupPermission("tools.batchimportfiles") && JUDLPROVERSION)
		{
			?>
			<div class="cpanel">
				<div class="icon-wrapper">
					<div class="icon">
						<a href="index.php?option=com_judownload&view=tools&layout=batchimportfiles">
							<img alt="<?php echo JText::_('COM_JUDOWNLOAD_BATCH_IMPORT_FILES'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/import-file.png" />
							<span><?php echo JText::_('COM_JUDOWNLOAD_BATCH_IMPORT_FILES'); ?></span>
						</a>
					</div>
				</div>
			</div>
		<?php
		} ?>

		<?php if (JUDownloadHelper::checkGroupPermission("tools.uploadmultiimages") && JUDLPROVERSION)
		{
			?>
			<div class="cpanel">
				<div class="icon-wrapper">
					<div class="icon">
						<a href="index.php?option=com_judownload&view=tools&layout=batchimportimages">
							<img alt="<?php echo JText::_('COM_JUDOWNLOAD_BATCH_IMPORT_IMAGES'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/import-image.png" />
							<span><?php echo JText::_('COM_JUDOWNLOAD_BATCH_IMPORT_IMAGES'); ?></span>
						</a>
					</div>
				</div>
			</div>
		<?php
		} ?>

		<div class="cpanel">
			<div class="icon-wrapper">
				<div class="icon">
					<a href="index.php?option=com_judownload&view=tools&layout=information">
						<img alt="<?php echo JText::_('COM_JUDOWNLOAD_INFORMATION'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/info.png" />
						<span><?php echo JText::_('COM_JUDOWNLOAD_INFORMATION'); ?></span>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>