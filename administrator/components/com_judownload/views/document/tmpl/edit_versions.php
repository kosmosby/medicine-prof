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

if(JUDLPROVERSION)
{
	if ($this->versions)
	{
		$file_directory = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/");
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$('.judl-versions').versions();
			});
		</script>

		<div class="judl-versions">
			<table class="table table-striped table-hover">
				<thead>
				<tr>
					<th style="width: 40%">
						<?php echo JText::_('COM_JUDOWNLOAD_FILE_NAME'); ?>
					</th>
					<th style="width: 15%">
						<?php echo JText::_('COM_JUDOWNLOAD_FILE_SIZE'); ?>
					</th>
					<th style="width: 15%">
						<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOADS'); ?>
					</th>
					<th style="width: 30%">
						<?php echo JText::_('COM_JUDOWNLOAD_FILE_ACTIONS'); ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$thisVersion = '';
				foreach ($this->versions AS $version)
				{
					if ($version->file_path)
					{
						$file_exists = JFile::exists($file_directory . $this->item->id . "/" . $version->file_path);
					}

					
					if ($thisVersion != $version->version || $version->file_id == 0)
					{
						$thisVersion = $version->version;
						echo '<tr class="info">';
						echo '<td colspan="2"><strong>' . JText::_('COM_JUDOWNLOAD_VERSION') . ' ' . $version->version . '</strong> <em><small>(' . JHtml::date($version->date, 'l, d F Y H:i:s') . ')</small></em></td>';
						echo '<td>' . ($version->file_id == 0 ? $version->downloads : '') . '</td>';
						echo '<td></td>';
						echo '</tr>';
					}

					
					if ($version->file_id > 0)
					{
						echo '<tr class="file-version-item">';
						echo '<td>' . $version->rename . '</td>';
						echo '<td class="file-size">' . (intval($version->size) ? $version->size : '-') . '</td>';
						echo '<td>' . $version->downloads . '</td>';
						echo '<td>';
						?>
						<div class="file-actions">
							<?php
							if ($this->params->get('store_old_file_versions', 1))
							{
								if ($version->file_path)
								{
									if ($file_exists)
									{
										?>
										<a href="index.php?option=com_judownload&task=document.downloadFile&fileId=<?php echo $version->file_id; ?>&version=<?php echo $version->version; ?>&<?php echo JSession::getFormToken(); ?>=1"
										   target="_blank">
										<span class="btn btn-mini hasTooltip"
										      title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD') . ' ' . $version->rename . ' - ' . JText::_('COM_JUDOWNLOAD_VERSION') . ': ' . $version->version; ?>">
											<i class="icon-download"></i>
											<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
										</span>
										</a>
									<?php
									}
									else
									{
										?>
										<span class="btn btn-mini hasTooltip"
										      title="<?php echo JText::_('COM_JUDOWNLOAD_MISSING_FILE'); ?>">
											<i class="icon-pending"></i>
											<?php echo JText::_('COM_JUDOWNLOAD_MISSING'); ?>
									</span>
									<?php
									}
								}
								else
								{
									?>
									<span class="btn btn-mini hasTooltip disabled"
									      title="<?php echo JText::_('COM_JUDOWNLOAD_FILE_IS_NOT_CHANGED'); ?>">
											<i class="icon-ok"></i>
										<?php echo JText::_('COM_JUDOWNLOAD_FILE_NO_CHANGE'); ?>
								</span>
								<?php
								} ?>
								<span class="btn btn-mini replace-file">
								<i class="icon-copy"></i><?php echo JText::_('COM_JUDOWNLOAD_REPLACE'); ?>
							</span>
							<?php
							} ?>
							<span class="remove btn btn-mini" data-iconremove="icon-trash"
							      data-iconunremove="icon-undo">
							<i class="icon-trash"></i><?php echo JText::_('COM_JUDOWNLOAD_DELETE'); ?>
						</span>
							<input type="hidden" name="versions[<?php echo $version->id; ?>][remove]"
							       class="version-remove-value"
							       value="0"/>
						</div>
						<div class="file-replace-uploader" style="display: none">
							<div
								class="upload-message"><?php echo JText::_("COM_JUDOWNLOAD_YOUR_BROWSER_DOESNT_HAVE_FLASH_SILVERLIGHT_OR_HTML5_SUPPORT"); ?></div>
							<div class="upload-progress progress progress-striped active" style="margin-bottom: 5px;">
								<div class="bar">
									<div class="upload-file-name"></div>
								</div>
							</div>
							<div class="file-replace-container">
							<span class="pickfiles btn btn-mini"><i
									class="icon-new"></i> <?php echo JText::_('COM_JUDOWNLOAD_SELECT_FILE'); ?></span>
							<span class="uploadfiles disabled btn btn-mini"><i
									class="icon-upload"></i> <?php echo JText::_('COM_JUDOWNLOAD_UPLOAD'); ?></span>
							</div>
							<input type="hidden" name="versions[<?php echo $version->id; ?>][replace]"
							       class="file-replace"
							       value="<?php echo $version->replace; ?>"/>
						</div>
						<?php
						echo '</td>';
						echo '</tr>';
					}
				}
				?>
				</tbody>
			</table>
		</div>
	<?php
	}
}
else
{
	echo '<div class="alert alert-success">';
	echo '<p>File version allow you to detect changed files when changing version of document and store old version of changed files, then user can download previous version of files/document.</p>';
	echo '<p>It also help to static download times of files, document on each version.</p>';
	echo '<p>Please upgrade to <a href="http://www.joomultra.com/ju-download-comparison.html">Pro Version</a> to use this feature</p>';
	echo '</div>';
}
?>
