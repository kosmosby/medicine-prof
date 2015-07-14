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
<div class="doc-meta clearfix">
	<?php if (isset($this->item->fields['publish_up']) && $this->item->fields['publish_up']->canView())
	{
		?>
		<div class="meta-date">
			<div class="caption">
				<span class="fa fa-calendar"
				      title="<?php echo $this->item->fields['publish_up']->getCaption(); ?>"></span>
			</div>
			<div
				class="value"><?php echo $this->item->fields['publish_up']->getDisplayPrefixText() . ' ' . $this->item->fields['publish_up']->getOutput(array("view" => "details", "template" => $this->template)) . ' ' . $this->item->fields['publish_up']->getDisplaySuffixText(); ?></div>
		</div>
	<?php
	} ?>

	<?php if (isset($this->item->fields['created_by']) && $this->item->fields['created_by']->canView())
	{
		?>
		<div class="meta-user" itemtype='http://schema.org/Person' itemscope="">
			<div class="caption">
				<span class="fa fa-user" title="<?php echo $this->item->fields['created_by']->getCaption(); ?>"></span>
			</div>
			<div
				class="value"><?php echo $this->item->fields['created_by']->getDisplayPrefixText() . ' ' . $this->item->fields['created_by']->getOutput(array("view" => "details", "template" => $this->template)) . ' ' . $this->item->fields['created_by']->getDisplaySuffixText(); ?></div>
		</div>
	<?php
	} ?>

	<?php if (isset($this->item->fields['cat_id']) && $this->item->fields['cat_id']->canView())
	{
		?>
		<div class="meta-category">
			<div class="caption">
				<span class="fa fa-folder-open" title="<?php echo $this->item->fields['cat_id']->getCaption(); ?>"></span>
			</div>

			<?php echo $this->item->fields['cat_id']->getDisplayPrefixText() . ' ' . $this->item->fields['cat_id']->getOutput(array("view" => "details", "template" => $this->template)) . ' ' . $this->item->fields['cat_id']->getDisplaySuffixText(); ?>

		</div>
	<?php
	} ?>
</div>