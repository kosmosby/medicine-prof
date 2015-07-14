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

if ($this->params->get('allow_zip_file', 1) || (count($this->item->files) == 1) ||
	(isset($this->item->fields['external_link']) && $this->item->fields['external_link']->value != '')
)
{
	// We will show button download or password input field after showed list files of document
	if ($this->item->params->get('access-download'))
	{
		// We have download right
		if ($this->item->params->get('valid-password'))
		{
			// We input valid password if document has password or this document hasn't password
			?>
			<div class="btn-download-container pull-left">
				<?php
				// Document has valid external_link
				if (isset($this->item->fields['external_link']) && $this->item->fields['external_link']->value != '')
				{
					// Document has valid external_link and must confirm license before download
					if (isset($this->item->fields['confirm_license']) && $this->item->fields['confirm_license']->value
						&& isset($this->item->fields['license_id']) && $this->item->fields['license_id']->value
					)
					{
						?>
						<!-- button show license -->
						<a href="<?php echo $this->item->download_link; ?>"
						   class="btn external" title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD_SELECTED_FILES'); ?>" data-toggle="modal">
							<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
						</a>
					<?php
					}
					// Document has valid external_link and does not need to confirm license before download
					else
					{
						?>
						<a href="<?php echo $this->item->download_link; ?>"
						   id="btn-download-<?php echo $this->item->id; ?>" class="btn judl-btn-download judl-accept-download"
						   title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD_SELECTED_FILES'); ?>"
						   data-id="<?php echo $this->item->id; ?>"
						   data-title="<?php echo $this->item->title; ?>"
						   data-downloads="<?php echo $this->item->downloads; ?>">
							<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
						</a>
					<?php
					}
				}
				// Download internal files
				else
				{
					// Confirm license before download
					if (isset($this->item->fields['confirm_license']) && $this->item->fields['confirm_license']->value
						&& isset($this->item->fields['license_id']) && $this->item->fields['license_id']->value
					)
					{
						?>
						<a href="#judl-document-license" id="judl-count-files"
						   class="btn confirm-license judl-download-multi-files" title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD_SELECTED_FILES'); ?>" data-toggle="modal">
							<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
						</a>
					<?php
					}
					// Download without confirm license
					else
					{
						?>
						<button id="judl-count-files"
						        class="btn judl-accept-download judl-download-multi-files"></button>
					<?php
					}
				}
				?>
			</div>
		<?php
		}
	}
	// Show error download message or redirect to error page
	else
	{
		?>
		<div class="error-msg-container">
			<?php
			if (isset($this->item->error_msg))
			{
				// Show error messages
				if ($this->display_download_rule_msg == "show_msg")
				{
					?>
					<div class="judl-rule-msg">
						<ul class="judl-rule-error-messages">
							<?php
							foreach ($item->error_msg AS $errorMessage)
							{
								?>
								<li><?php echo $errorMessage; ?></li>
							<?php
							} ?>
						</ul>
					</div>
				<?php
				}
				// Redirect to error message page
				elseif ($this->display_download_rule_msg == "redirect")
				{
					?>
					<a href="#" id="judl-count-files" class="btn judl-download-multi-files" title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD_SELECTED_FILES'); ?>">
						<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
					</a>
				<?php
				}
				// Show error messages in modal box
				elseif ($this->display_download_rule_msg == "modal")
				{
					?>
					<a href="#judl-rule-msg" class="btn" data-toggle="modal" title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD_SELECTED_FILES'); ?>">
						<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
					</a>
				<?php
				}
			}
			?>
		</div>
	<?php
	}
}