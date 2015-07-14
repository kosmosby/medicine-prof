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

if ($this->approvalForm)
{
	// @todo recheck hosting
	require_once JPATH_SITE . '/components/com_judownload/models/modpendingdocuments.php';
	JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_judownload/models');
	$modelModPendingDocuments = JModelLegacy::getInstance('ModPendingDocuments','JUDownloadModel');
	$totalNext     = $modelModPendingDocuments->getTotalDocumentsModCanApprove('next', $this->item->id);
	$totalPrevious = $modelModPendingDocuments->getTotalDocumentsModCanApprove('prev', $this->item->id);
	?>
	<div class="judl-submit-buttons">
		<button type="button" class="btn btn-default btn-primary" onclick="Joomla.submitbutton('modpendingdocument.save')">
			<i class="fa fa-save"></i> <?php echo JText::_('COM_JUDOWNLOAD_SUBMIT'); ?>
		</button>
		<button type="button" class="btn btn-default" onclick="Joomla.submitbutton('modpendingdocument.cancel');">
			<?php echo JText::_('COM_JUDOWNLOAD_CANCEL'); ?>
		</button>
	</div>

	<div class="judl-approval-container clearfix">
		<div class="judl-approval-inner">
			<?php if ($totalPrevious)
			{
				?>
				<button type="button" class="judl-previous btn btn-default btn-info"
				        onclick="Joomla.submitbutton('modpendingdocument.saveAndPrev')">
					<i class="fa fa-arrow-circle-o-left"></i>
					<?php echo JText::sprintf('COM_JUDOWNLOAD_SAVE_AND_PREV_N', $totalPrevious); ?>
				</button>
			<?php
			} ?>

			<div class="judl-approval-options">
				<div class="judl-option-approve-item input-prepend input-append pull-left">
					<span class="add-on">
						<input type="radio" name="approval_option" value="ignore" checked id="ignore-document"/>
					</span>
					<label for="ignore-document" class="btn btn-default">
						<i class="fa fa-question-circle"></i>
						<?php echo JText::_('COM_JUDOWNLOAD_IGNORE'); ?>
					</label>
				</div>
				<div class="judl-option-approve-item input-prepend input-append pull-left">
					<span class="add-on">
						<input type="radio" name="approval_option" value="approve" id="approval-document"/>
					</span>
					<label for="approval-document" class="btn btn-default btn-success">
						<i class="fa fa-check-circle"></i>
						<?php echo JText::_('COM_JUDOWNLOAD_APPROVE'); ?>
					</label>
				</div>
				<div class="judl-option-approve-item input-prepend input-append pull-left">
					<span class="add-on">
						<input type="radio" name="approval_option" value="delete" id="reject-document"/>
					</span>
					<label for="reject-document" class="btn btn-danger">
						<i class="fa fa-times"></i>
						<?php echo JText::_('COM_JUDOWNLOAD_REJECT'); ?>
					</label>
				</div>
				<div class="clr"></div>
			</div>

			<?php if ($totalNext)
			{
				?>
				<button type="button" class="judl-next btn btn-default btn-info"
				        onclick="Joomla.submitbutton('modpendingdocument.saveAndNext')">
					<?php echo JText::sprintf('COM_JUDOWNLOAD_SAVE_AND_NEXT_N', $totalNext); ?>
					<i class="fa fa-arrow-circle-o-right"></i>
				</button>
			<?php
			} ?>
		</div>
	</div>
<?php
}
else
{
	?>
	<div class="judl-submit-buttons">
		<button type="button" class="btn btn-default btn-primary" onclick="Joomla.submitbutton('form.save')">
			<i class="fa fa-save"></i> <?php echo JText::_('COM_JUDOWNLOAD_SUBMIT'); ?>
		</button>

		<button type="button" class="btn btn-default" onclick="Joomla.submitbutton('form.cancel')">
			<?php echo JText::_('COM_JUDOWNLOAD_CANCEL'); ?>
		</button>
	</div>
<?php
}
?>
