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

if ($this->app->input->getInt('approve', 0) == 1)
{
	$totalNextPendingDocuments = JUDownloadHelper::getTotalPendingDocuments('next', $this->item->id);
	$totalPrevPendingDocuments = JUDownloadHelper::getTotalPendingDocuments('prev', $this->item->id);
	?>
	<div class="approval">
		<div class="approval-inner">
			<?php if ($totalPrevPendingDocuments > 0)
			{ ?>
				<button class="judl-previous btn btn-info" onclick="Joomla.submitbutton('pendingdocument.saveAndPrev')">
					<i class="icon-arrow-left-2"></i>
					<?php echo JText::sprintf('COM_JUDOWNLOAD_SAVE_AND_PREV_N', $totalPrevPendingDocuments); ?>
				</button>
			<?php
			} ?>

			<div class="judl-approval-options">
				<div class="judl-option-approve-item input-prepend input-append pull-left">
					<span class="add-on">
						<input type="radio" name="approval_option" value="ignore" checked id="ignore-document" />
					</span>
					<label for="ignore-document" class="btn">
						<i class="icon-question"></i>
						<?php echo JText::_('COM_JUDOWNLOAD_IGNORE'); ?>
					</label>
				</div>
				<div class="judl-option-approve-item input-prepend input-append pull-left">
					<span class="add-on">
						<input type="radio" name="approval_option" value="approve" id="approval-document" />
					</span>
					<label for="approval-document" class="btn btn-success">
						<i class="icon-checkmark-2"></i>
						<?php echo JText::_('COM_JUDOWNLOAD_APPROVE'); ?>
					</label>
				</div>
				<div class="judl-option-approve-item input-prepend input-append pull-left">
					<span class="add-on">
						<input type="radio" name="approval_option" value="delete" id="reject-document" />
					</span>
					<label for="reject-document" class="btn btn-danger">
						<i class="icon-cancel"></i>
						<?php echo JText::_('COM_JUDOWNLOAD_REJECT'); ?>
					</label>
				</div>
				<div class="clr"></div>
			</div>

			<?php if ($totalNextPendingDocuments > 0)
			{
				?>
				<button class="judl-next btn btn-info" onclick="Joomla.submitbutton('pendingdocument.saveAndNext')">
					<?php echo JText::sprintf('COM_JUDOWNLOAD_SAVE_AND_NEXT_N', $totalNextPendingDocuments); ?>
					<i class="icon-arrow-right-2"></i>
				</button>
			<?php
			} ?>
		</div>
	</div>

	<div class="clr"></div>
<?php
}
?>