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

$this->model->uploadFileScript($this->item->id, "#judl-files");
$file_directory     = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/");
?>
<fieldset class="adminform">
	<div id="judl-files" class="judl-files">
		<ul id="file-list" class="file-list nav clearfix">
			<?php
			if ($this->files)
			{
				$canChangeDownloads = '';
				if(!JFactory::getUser()->authorise('core.admin', 'com_judownload'))
				{
					$canChangeDownloads = 'readonly="readonly"';
				}

				foreach ($this->files AS $key => $file)
				{
					if($file['id'])
					{
						$file_exists = JFile::exists($file_directory . $this->item->id . "/" . $file['file_name']);
					}
					?>
					<li>
						<div class="file-item">
							<span class="move"><i class="fa fa-ellipsis-v"></i></span>

							<div class="file-actions file-row">
								<?php
								if($file['id'])
								{
									if ($file_exists)
									{
										?>
										<a href="<?php echo JRoute::_("index.php?option=com_judownload&task=download.download&file_id=" . $file['id'] . "&" . JSession::getFormToken() . "=1"); ?>"
										   target="_blank">
											<span class="btn btn-default btn-xs">
												<i class="fa fa-download"></i>
												<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
											</span>
										</a>
									<?php
									}
									else
									{
										?>
										<span class="btn btn-default btn-xs"
										      title="<?php echo JText::_('COM_JUDOWNLOAD_MISSING_FILE'); ?>">
		                                    <i class="fa fa-warning"></i>
											<?php echo JText::_('COM_JUDOWNLOAD_MISSING'); ?>
		                                </span>
									<?php
									}?>
									<span class="btn btn-default btn-xs replace-file"><i class="fa fa-files-o"></i> <?php echo JText::_('COM_JUDOWNLOAD_REPLACE'); ?></span>
								<?php
								}?>
								<span class="<?php echo $file['published'] ? 'publish' : 'unpublish'; ?> btn btn-default btn-xs" data-iconpublish="fa fa-check" data-iconunpublish="fa fa-close">
									<?php
									if($file['published'])
									{
										?>
										<i class="fa fa-check"></i> <?php echo JText::_('COM_JUDOWNLOAD_PUBLISH'); ?>
									<?php
									}
									else
									{
										?>
										<i class="fa fa-close"></i> <?php echo JText::_('COM_JUDOWNLOAD_PUBLISH'); ?>
									<?php
									}?>
								</span>
								<input type="hidden" name="judlfiles[<?php echo $key; ?>][published]" class="file-published-value" value="<?php echo $file['published'] ? 1 : 0 ?>" />

								<span class="remove btn btn-default btn-xs" data-iconremove="fa fa-trash-o" data-iconunremove="fa fa-undo">
									<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_JUDOWNLOAD_DELETE'); ?>
								</span>
								<input type="hidden" name="judlfiles[<?php echo $key; ?>][remove]" class="file-remove-value" value="0" />
							</div>

							<div class="file-row">
                                <div class="file-name-info input-append">
                                    <input name="judlfiles[<?php echo $key; ?>][rename]" id="file-name-<?php echo $key; ?>" class="file-name validate-filename required" type="text" value="<?php echo $file['rename'] ?>" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FILE_NAME'); ?>"/>
                                    <span class="file-size add-on" title="<?php echo JText::_('COM_JUDOWNLOAD_MIME_TYPE') . ': ' . $file['mime_type']; ?>"><?php echo JUDownloadHelper::formatBytes($file['size']); ?></span>
	                                <input type="hidden" name="judlfiles[<?php echo $key; ?>][size]" class="file-size-value" value="<?php echo $file['size']; ?>" />
                                </div>
								<?php
								if($file['id'])
								{?>
									<div class="file-replace-uploader" style="display: none">
										<div class="upload-message"><?php echo JText::_("COM_JUDOWNLOAD_YOUR_BROWSER_DOESNT_HAVE_FLASH_SILVERLIGHT_OR_HTML5_SUPPORT"); ?></div>
										<div class="upload-progress progress progress-striped active" style="margin-bottom: 5px;">
											<div class="bar">
												<div class="upload-file-name"></div>
											</div>
										</div>
										<div class="file-replace-container">
											<span class="pickfiles btn btn-default btn-xs"><i class="fa fa-plus"></i> <?php echo JText::_('COM_JUDOWNLOAD_SELECT_FILE'); ?></span>
											<span class="uploadfiles disabled btn btn-default btn-xs"><i class="fa fa-upload"></i> <?php echo JText::_('COM_JUDOWNLOAD_UPLOAD'); ?></span>
										</div>
										<input type="hidden" name="judlfiles[<?php echo $key; ?>][replace]" class="file-replace" value="<?php echo isset($file['replace']) ? $file['replace'] : ''; ?>" />
									</div>
								<?php
								}?>
								<input type="hidden" name="judlfiles[<?php echo $key; ?>][mime_type]" class="file-mimetype" value="<?php echo $file['mime_type']; ?>" />
								<label style="display: none" for="file-name-<?php echo $key; ?>"><?php echo JText::_('COM_JUDOWNLOAD_INVALID_FILE_NAME'); ?></label>
							</div>

							<div class="file-row">
								<input name="judlfiles[<?php echo $key; ?>][title]" class="file-title" type="text" value="<?php echo $file['title']; ?>" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FILE_TITLE'); ?>"/>
								<input name="judlfiles[<?php echo $key; ?>][downloads]" <?php echo $canChangeDownloads; ?> class="file-downloads" type="text" value="<?php echo $file['downloads']; ?>" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOADS'); ?>" title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOADS'); ?>"/>
							</div>

							<div class="file-row">
								<textarea class="file-description" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_DESCRIPTION'); ?>" name="judlfiles[<?php echo $key; ?>][description]" rows="2"><?php echo $file['description']; ?></textarea>
							</div>
                            <?php
                            if(!$file['id'])
                            { ?>
                                <input type="hidden" name="judlfiles[<?php echo $key; ?>][file_name]" class="file-filename" value="<?php echo $file['file_name']; ?>" />
                            <?php
                            } ?>
							<input type="hidden" name="judlfiles[<?php echo $key; ?>][id]" class="file-id" value="<?php echo $file['id']; ?>" />
						</div>
					</li>
				<?php
				}
			}
			?>
		</ul>

		<?php echo JHtml::_('bootstrap.startTabSet', 'document-files-' . $this->item->id, array('active' => 'file-uploader')); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'document-files-' . $this->item->id, 'file-uploader', JText::_('COM_JUDOWNLOAD_UPLOADER_TAB')); ?>
		<div id="judl-uploader" style="margin: 10px;">
			<p><?php echo JText::_("COM_JUDOWNLOAD_YOUR_BROWSER_DOESNT_HAVE_FLASH_SILVERLIGHT_OR_HTML5_SUPPORT"); ?></p>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php
		if(JUDownloadFrontHelperPermission::canUploadFromUrl($this->item->id))
		{
			echo JHtml::_('bootstrap.addTab', 'document-files-' . $this->item->id, 'remote', JText::_('COM_JUDOWNLOAD_FIELD_REMOTE_UPLOAD')); ?>
			<div id="remote-file" class="remote-file">
				<div class="progress">
					<div class="progress-bar progress-bar-striped div-active div-percent" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
						<span class="sr-only div-percent-text"></span>
					</div>
				</div>
				<div class="clearfix">
					<input type="text" class="source-url input-xlarge" size="78" value="" placeholder="<?php echo JText::_("COM_JUDOWNLOAD_FILE_URL"); ?>" />
					<input class="btn btn-default btn-primary process-remote-file" type="button" value="<?php echo JText::_("COM_JUDOWNLOAD_TRANSFER_FILE"); ?>" />
					<input class="btn btn-default cancel-remote-file" type="button" value="<?php echo JText::_("COM_JUDOWNLOAD_CANCEL"); ?>" />
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab');
		} ?>
		<?php echo JHtml::_('bootstrap.endTabset'); ?>

		<div id="require-upload-file-wrapper" style="display: none;">
			<?php
			$requiredFile = $this->params->get("document_require_file", 1);
			if ($requiredFile && !$this->files)
			{ ?>
				<label for="require-upload-file"><?php echo JText::_("COM_JUDOWNLOAD_PLEASE_UPLOAD_A_FILE"); ?></label>
				<input id="require-upload-file" type="file" multiple="" class="required" />
			<?php
			} ?>
		</div>
	</div>
</fieldset>