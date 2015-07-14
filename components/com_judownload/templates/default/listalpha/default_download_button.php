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

if ($this->item->total_files > 0 || (isset($fields['external_link']) && $fields['external_link']->value != ''))
{
	if ($this->item->params->get('access-download'))
	{
		if ((isset($fields['license_id']) && $fields['license_id']->value) && (isset($fields['confirm_license']) && $fields['confirm_license']->value))
		{
			$classConfirmLicense = 'confirm-license';
			?>
			<div id="<?php echo 'judl-license-' . $this->item->id; ?>" class="modal fade" tabindex="-1"
			     role="dialog"
			     aria-labelledby="judl-doc-license-label" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h3 id="judl-doc-license-label" class="modal-title">
								<?php echo JText::sprintf('COM_JUDOWNLOAD_LICENSE_CONFIRMATION', $fields['license_id']->value->title); ?>
							</h3>
						</div>
						<div class="modal-body">
							<?php echo $fields['license_id']->value->description; ?>
						</div>
						<div class="modal-footer">
							<a href="<?php echo $this->item->download_link; ?>"
							   id="judl-accept-license-<?php echo $this->item->id; ?>"
							   class="judl-accept-license btn btn-default btn-primary"
							   data-id="<?php echo $this->item->id; ?>"
							   data-title="<?php echo $this->item->title; ?>"
							   data-downloads="<?php echo $this->item->downloads; ?>">
								<?php echo JText::_("COM_JUDOWNLOAD_AGREE_AND_DOWNLOAD"); ?>
							</a>
							<button class="btn btn-default" data-dismiss="modal" aria-hidden="true">
								<?php echo JText::_("COM_JUDOWNLOAD_CANCEL"); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		<?php
		}
		else
		{
			$classConfirmLicense = '';
		}

		if ($this->item->params->get('valid-password'))
		{
			?>
			<a href="<?php echo $this->item->download_link; ?>" id="<?php echo 'btn-download-' . $this->item->id; ?>"
			   class="hasTooltip btn btn-default judl-btn-download judl-accept-download <?php echo $classConfirmLicense; ?>"
			   target="<?php echo $this->external_download_link_target; ?>"
			   title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>"
			   data-id="<?php echo $this->item->id; ?>"
			   data-title="<?php echo $this->item->title; ?>"
			   data-downloads="<?php echo $this->item->downloads; ?>">
				<i class="fa fa-download"></i>
			</a>
		<?php
		}
		else
		{
			if ($this->item->allow_enter_password)
			{
				?>
				<a href="#" id="<?php echo 'btn-download-' . $this->item->id; ?>"
				   class="hasTooltip btn btn-default judl-btn-download download-unlock <?php echo $classConfirmLicense; ?>"
				   title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>">
					<i class="fa fa-download"></i>
				</a>
			<?php
			}
			else
			{
				?>
				<a href="#" id="<?php echo 'btn-download-' . $this->item->id; ?>"
				   class="hasTooltip btn btn-default judl-btn-download download-locked"
				   title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>">
					<i class="fa fa-lock"></i>
				</a>
				<div id="<?php echo 'judl-locked-alert-' . $this->item->id; ?>" class="modal fade"
				     tabindex="-1"
				     role="dialog" aria-labelledby="judl-enter-password-label" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h3 id="judl-enter-password-label" class="modal-title">
									<?php echo JText::_('COM_JUDOWNLOAD_YOU_CAN_NOT_ENTER_PASSWORD'); ?>
								</h3>
							</div>
							<div class="modal-body">
								<div class="alert alert-error">
									<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD_PASSWORD_FORM_HAS_BEEN_LOCKED'); ?>
								</div>
							</div>
							<div class="modal-footer">
								<button class="btn btn-default" data-dismiss="modal" aria-hidden="true">
									<?php echo JText::_("COM_JUDOWNLOAD_CLOSE"); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			<?php
			}
		}
	}
	else
	{
		if (isset($this->item->error_msg))
		{
			if ($this->display_download_rule_msg == "show_msg")
			{
				?>
				<!--Div for show download rule messages -->
				<div class="judl-rule-msg">
					<ul class="judl-rule-error-messages">
						<?php
						foreach ($this->item->error_msg AS $errorMessage)
						{
							?>
							<li><?php echo $errorMessage; ?></li>
						<?php
						} ?>
					</ul>
				</div>
			<?php
			}
			elseif ($this->display_download_rule_msg == "redirect")
			{
				?>
				<a href="<?php echo $this->item->download_link; ?>"
				   id="<?php echo 'btn-download-' . $this->item->id; ?>"
				   class="hasTooltip btn btn-default judl-btn-download"
				   title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>">
					<i class="fa fa-download"></i>
				</a>
			<?php
			}
			elseif ($this->display_download_rule_msg == "modal")
			{
				?>
				<a href="#" id="<?php echo 'btn-download-' . $this->item->id; ?>"
				   class="hasTooltip btn btn-default judl-btn-download error-rule"
				   title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>">
					<i class="fa fa-download"></i>
				</a>

				<div id="judl-rule-msg-<?php echo $this->item->id; ?>"
				     class="modal fade" tabindex="-1" role="dialog"
				     aria-labelledby="judl-rule-msg-label" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h3 id="judl-rule-msg-label" class="modal-title">
									<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD_RULE_MESSAGES'); ?>
								</h3>
							</div>
							<div class="modal-body">
								<div class="judl-rule-msg">
									<ul class="judl-rule-error-messages">
										<?php
										foreach ($this->item->error_msg AS $errorMessage)
										{
											?>
											<li><?php echo $errorMessage; ?></li>
										<?php
										} ?>
									</ul>
								</div>
							</div>
							<div class="modal-footer">
								<button class="btn btn-default" data-dismiss="modal" aria-hidden="true">
									<?php echo JText::_("COM_JUDOWNLOAD_CLOSE"); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			<?php
			}
		}
	}
}?>