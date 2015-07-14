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

JHtml::addIncludePath(JPATH_SITE . '/components/com_judownload/helpers/html');
?>
<div id="judl-container" class="jubootstrap component judl-container view-tags">
	<h2><?php echo JText::_('COM_JUDOWNLOAD_TAGS'); ?></h2>

	<form name="judl-form-tags" id="judl-form-tags" class="judl-form-tags" method="post" action="#">
		<div class="sort-pagination clearfix">
			<div class="pull-right">
				<div id="sort" class="judl-sort">
					<select name="filter_order" class="judl-order-sort input-medium" onchange="this.form.submit()">
						<?php echo JHtml::_('select.options', $this->order_name_array, 'value', 'text', $this->listOrder); ?>
					</select>
					<select name="filter_order_Dir" class="judl-order-dir input-small" onchange="this.form.submit()">
						<?php echo JHtml::_('select.options', $this->order_dir_array, 'value', 'text', $this->listDirn); ?>
					</select>
				</div>
				<div class="pagination-wrap">
					<div class="limitbox">
						<div
							class="display-number"><?php echo JText::_('COM_JUDOWNLOAD_PAGINATION_DISPLAY'); ?></div>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="container-fluid">
			<div class="row-fluid">
				<?php foreach ($this->items AS $key => $item)
				{
				?>
				<div class="span6">
					<i class="fa fa-tag"></i>
					<a href="<?php echo JRoute::_('index.php?option=com_judownload&view=tag&id=' . $item->id . '&Itemid=' . JUDownloadHelperRoute::findItemId(array('tag' => array($item->id)))); ?>">
						<?php echo $item->title; ?><span> (<?php echo $item->total_documents; ?>)</span></a>
				</div>
				<?php
				$key++;
				if (($key % 2) == 0 && $key < count($this->items))
				{
				?>
			</div>
			<div class="row-fluid">
				<?php
				} ?>

				<?php
				} ?>
			</div>
		</div>

		<div class="pagination-wrap clearfix">
			<?php
			if ($this->pagination->get('pages.total') > 1)
			{
				?>
				<div class="pagination">
					<?php echo $this->pagination->getPagesLinks(); ?>
				</div>

				<div class="counter">
					<?php echo $this->pagination->getPagesCounter(); ?>
				</div>
			<?php
			} ?>
		</div>
	</form>
</div>