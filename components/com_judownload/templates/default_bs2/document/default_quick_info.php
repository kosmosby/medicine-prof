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
<div class="quick-info">
	<?php if (isset($this->item->fields['comments']) && $this->item->fields['comments']->canView())
	{
		?>
		<div class="judl-field comment clearfix">
			<div class="caption">
				<i class="fa fa-comments-o"></i> <?php echo $this->item->fields['comments']->getCaption(); ?>
			</div>
			<div
				class="value"><?php echo $this->item->fields['comments']->getDisplayPrefixText() . ' ' . $this->item->fields['comments']->getOutput(array("view" => "details", "template" => $this->template)) . ' ' . $this->item->fields['comments']->getDisplaySuffixText(); ?>
			</div>
		</div>
	<?php
	} ?>

	<?php if (isset($this->item->fields['downloads']) && $this->item->fields['downloads']->canView())
	{
		?>
		<div class="judl-field download clearfix">
			<div class="caption">
				<i class="fa fa-download"></i> <?php echo $this->item->fields['downloads']->getCaption(); ?>
			</div>
			<div
				class="value"><?php echo $this->item->fields['downloads']->getDisplayPrefixText() . ' ' . $this->item->fields['downloads']->getOutput(array("view" => "details", "template" => $this->template)) . ' ' . $this->item->fields['downloads']->getDisplaySuffixText(); ?>
			</div>
		</div>
	<?php
	} ?>

	<?php if (isset($this->item->fields['hits']) && $this->item->fields['hits']->canView())
	{
		?>
		<div class="judl-field hits clearfix">
			<div class="caption">
				<i class="fa fa-eye"></i> <?php echo $this->item->fields['hits']->getCaption(); ?>
			</div>
			<div
				class="value"><?php echo $this->item->fields['hits']->getDisplayPrefixText() . ' ' . $this->item->fields['hits']->getOutput(array("view" => "details", "template" => $this->template)) . ' ' . $this->item->fields['hits']->getDisplaySuffixText(); ?>
			</div>
		</div>
	<?php
	} ?>

	<?php
	if (isset($this->item->fields['rating']) && $this->item->fields['rating']->canView())
	{
		?>
		<div class="judl-field rating clearfix">
			<div class="caption">
				<i class="fa fa-star-half-o"></i> <?php echo $this->item->fields['rating']->getCaption(); ?>
			</div>
			<div
				class="value"><?php echo $this->item->fields['rating']->getDisplayPrefixText() . ' ' . $this->item->fields['rating']->getOutput(array("view" => "details", "template" => $this->template, "type" => "details_view")) . ' ' . $this->item->fields['rating']->getDisplaySuffixText(); ?>
			</div>
		</div>
	<?php
	} ?>
</div>