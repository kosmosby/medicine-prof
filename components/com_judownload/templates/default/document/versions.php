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

if($this->item->versions)
{
	$file_directory = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/");
	?>
	<div id="judl-container"
	     class="jubootstrap component judl-container judl-doc-view doc-default cat-id-<?php echo $this->item->cat_id; ?> <?php echo isset($this->tl_catid) ? 'tlcat-id-' . $this->tl_catid : ""; ?> <?php echo $this->item->class_sfx; ?> <?php echo $this->pageclass_sfx; ?>">
		<div class="judl-versions">
			<table class="table table-striped table-hover">
				<thead>
				<tr>
					<th style="width: 50%">
						<?php echo JText::_('COM_JUDOWNLOAD_FILE_NAME'); ?>
					</th>
					<th style="width: 15%">
						<?php echo JText::_('COM_JUDOWNLOAD_FILE_SIZE'); ?>
					</th>
					<th class="hidden-xs" style="width: 15%">
						<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOADS'); ?>
					</th>
					<?php
					if ($this->params->get('store_old_file_versions', 1))
					{
						?>
						<th style="width: 20%">
							<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
						</th>
					<?php
					}?>
				</tr>
				</thead>
				<tbody>
				<?php
				$thisVersion = '';
				foreach ($this->item->versions AS $version)
				{
					$this->item->version = $version;

					if ($version->file_path)
					{
						$file_exists = JFile::exists($file_directory . $this->item->id . "/" . $version->file_path);
					}

					// Show document version info if version changed or file_id == 0
					if ($thisVersion != $version->version || $version->file_id == 0)
					{
						$thisVersion = $version->version;
						echo '<tr class="info">';
						echo '<td colspan="2"><strong>' . JText::_('COM_JUDOWNLOAD_VERSION') . ' ' . $version->version . '</strong> <em><small>(' . JHtml::date($version->date, 'l, d F Y H:i:s') . ')</small></em></td>';
						echo '<td>' . ($version->file_id == 0 ? $version->downloads : '') . '</td>';
						// Only show download document if has file, no download for document use external link only
						echo '<td>' . (count($this->item->files) ? $this->loadTemplate('document_download_button') : '') . '</td>';
						echo '</tr>';
					}

					// File version info
					if ($version->file_id > 0)
					{
						echo '<tr class="file-version-item">';
						echo '<td>' . $version->rename . '</td>';
						echo '<td class="file-size">' . (intval($version->size) ? $version->size : '-') . '</td>';
						echo '<td class="hidden-xs">' . $version->downloads . '</td>';
						if ($this->params->get('store_old_file_versions', 1))
						{
							echo '<td>';
							if ($version->file_path)
							{
								if ($file_exists)
								{
									echo $this->loadTemplate('file_download_button');
								}
								else
								{
									?>
									<span class="btn btn-default btn-xs hasTooltip"
									      title="<?php echo JText::_('COM_JUDOWNLOAD_MISSING_FILE'); ?>">
										<i class="fa fa-warning"></i>
										<?php echo JText::_('COM_JUDOWNLOAD_MISSING'); ?>
									</span>
								<?php
								}
							}
							else
							{
								?>
								<span class="btn btn-default btn-xs hasTooltip"
								      title="<?php echo JText::_('COM_JUDOWNLOAD_FILE_IS_NOT_CHANGED'); ?>">
									<i class="fa fa-check"></i>
									<?php echo JText::_('COM_JUDOWNLOAD_FILE_NO_CHANGE'); ?>
								</span>
							<?php
							}
							echo '</td>';
						}
						echo '</tr>';
					}
				}
				?>
				</tbody>
			</table>
		</div>
	</div>

	<?php
	// Show modal
	if ($this->item->params->get('access-download'))
	{
		// Show license modal if need to confirm license and has license
		if (isset($this->item->fields['confirm_license']) && $this->item->fields['confirm_license']->value
			&& isset($this->item->fields['license_id']) && $this->item->fields['license_id']->value
		)
		{
			?>
			<!-- Modal license -->
			<div id="judl-document-license" class="modal fade" tabindex="-1" role="dialog"
			     aria-labelledby="judl-document-license-label" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h3 id="judl-document-license-label" class="modal-title">
								<?php echo JText::sprintf('COM_JUDOWNLOAD_LICENSE_CONFIRMATION', $this->item->fields['license_id']->value->title); ?>
							</h3>
						</div>
						<div class="modal-body">
							<?php echo $this->item->fields['license_id']->value->description; ?>
						</div>
						<div class="modal-footer">
							<a href="#" id="judl-accept-license" class="btn btn-default btn-primary"
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
	}
	else
	{
		// Show error message modal
		if ($this->display_download_rule_msg == "modal")
		{
			?>
			<!-- Modal download error messages -->
			<div id="judl-rule-msg" class="modal fade" tabindex="-1" role="dialog"
			     aria-labelledby="judl-download-rule-messages-label" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h3 id="judl-download-rule-messages-label" class="modal-title">
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
?>