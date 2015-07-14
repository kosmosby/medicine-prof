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
<div id="judl-files" class="judl-file-box clearfix">
<form name="judl-form-files" id="judl-form-files" method="post"
      action="#">
<?php
if (!isset($this->item->fields['external_link']) || $this->item->fields['external_link']->value == '')
{
	?>
	<table class="table table-striped table-bordered">
	<thead>
	<tr>
		<?php
		if ($this->params->get('allow_zip_file', 1) || (count($this->item->files) == 1) ||
			(isset($this->item->fields['external_link']) && $this->item->fields['external_link']->value != '')
		)
		{ ?>
			<th class="file-id" style="width: 5%">
				<input type="checkbox" name="toggle" id="judl-file-check-all" checked="checked"/>
			</th>
		<?php
		} ?>
		<th class="file-name">
			<?php echo JText::_('COM_JUDOWNLOAD_FIELD_FILE_NAME'); ?>
		</th>
		<th class="file-filesize hidden-phone" style="width: 15%">
			<?php echo JText::_('COM_JUDOWNLOAD_FIELD_FILE_SIZE'); ?>
		</th>
		<th class="file-filetype hidden-phone" style="width: 15%">
			<?php echo JText::_('COM_JUDOWNLOAD_FIELD_FILE_MIME_TYPE'); ?>
		</th>
		<th class="file-downloads hidden-phone" style="width: 15%">
			<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOWNLOADS'); ?>
		</th>
		<th class="file-download" style="width: 15%">
			<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
		</th>
	</tr>
	</thead>

	<tbody>
	<?php
	foreach ($this->item->files AS $key => $file)
	{
		$this->item->file = $file;
		?>
		<tr>
			<?php
			if ($this->params->get('allow_zip_file', 1) || (count($this->item->files) == 1) ||
				(isset($this->item->fields['external_link']) && $this->item->fields['external_link']->value != '')
			)
			{
				?>
				<td>
					<input type="checkbox" checked="checked" name="file_id[]" id="judl-cb<?php echo $key; ?>"
					       value="<?php echo $file->id; ?>"/>
				</td>
			<?php
			} ?>
			<td>
				<a href="#" id="judl-file-title-id-<?php echo $file->id; ?>" class="judl-file-info-modal"
				   title="<?php echo $file->title; ?>"><?php echo $file->rename; ?></a>
				<!-- File modal -->
				<div id="judl-file-info-<?php echo $file->id; ?>" class="modal hide fade" tabindex="-1"
				     role="dialog" aria-labelledby="judl-file-info-label-<?php echo $file->id; ?>"
				     aria-hidden="true">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3 id="judl-file-info-label-<?php echo $file->id; ?>"
						    class="modal-title"><?php echo $file->rename; ?>
						</h3>
					</div>
					<div class="modal-body">
						<table class="table table-striped table-bordered">
							<thead>
							<tr>
								<th><?php echo JText::_('COM_JUDOWNLOAD_FIELD'); ?></th>
								<th><?php echo JText::_('COM_JUDOWNLOAD_VALUE'); ?></th>
							</tr>
							</thead>

							<tbody>
							<tr>
								<td><?php echo JText::_('COM_JUDOWNLOAD_FIELD_FILE_NAME'); ?></td>
								<td><?php echo $file->rename; ?></td>
							</tr>
							<?php
							if ($file->title !== '')
							{
								?>
								<tr>
									<td><?php echo JText::_('COM_JUDOWNLOAD_FIELD_FILE_TITLE'); ?></td>
									<td><?php echo $file->title; ?></td>
								</tr>
							<?php
							}
							?>
							<?php
							if ($file->description !== '')
							{
								?>
								<tr>
									<td><?php echo JText::_('COM_JUDOWNLOAD_FIELD_DESCRIPTION'); ?></td>
									<td><?php echo $file->description; ?></td>
								</tr>
							<?php
							}
							?>
							<tr>
								<td><?php echo JText::_('COM_JUDOWNLOAD_FIELD_FILE_SIZE'); ?></td>
								<td><?php echo $file->size; ?></td>
							</tr>
							<?php
							if ($file->mime_type !== '')
							{
								?>
								<tr>
									<td><?php echo JText::_('COM_JUDOWNLOAD_FIELD_FILE_MIME_TYPE'); ?></td>
									<td><?php echo $file->mime_type; ?></td>
								</tr>
							<?php
							}
							?>
							<tr>
								<td><?php echo JText::_('COM_JUDOWNLOAD_FIELD_CREATED'); ?></td>
								<td><?php echo $file->created; ?></td>
							</tr>
							<?php
							if (intval($file->modified))
							{
								?>
								<tr>
									<td><?php echo JText::_('COM_JUDOWNLOAD_FIELD_MODIFIED'); ?></td>
									<td><?php echo $file->modified; ?></td>
								</tr>
							<?php
							}
							?>
							<tr>
								<td><?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOWNLOADS'); ?></td>
								<td><?php echo $file->downloads; ?></td>
							</tr>
							<?php
							if ($file->md5_checksum !== '')
							{
								?>
								<tr>
									<td><?php echo JText::_('COM_JUDOWNLOAD_FIELD_MD5_CHECKSUM'); ?></td>
									<td><?php echo $file->md5_checksum; ?></td>
								</tr>
							<?php
							}
							?>
							<?php
							if ($file->crc32_checksum !== '')
							{
								?>
								<tr>
									<td><?php echo JText::_('COM_JUDOWNLOAD_FIELD_CRC32_CHECKSUM'); ?></td>
									<td><?php echo $file->crc32_checksum; ?></td>
								</tr>
							<?php
							}
							?>
							</tbody>
						</table>
					</div>
				</div>
			</td>
			<td class="hidden-phone">
				<?php echo $file->size; ?>
			</td>
			<td class="hidden-phone">
				<?php echo $file->mime_type; ?>
			</td>
			<td class="hidden-phone">
				<?php echo $file->downloads; ?>
			</td>

			<td class="judl-list-download">
				<?php
					echo $this->loadTemplate('download_single_button');
				?>
			</td>
		</tr>
	<?php
	} ?>
	</tbody>
	</table>
<?php
}

// Show password input if document has password, user can download, but has not enter password or wrong password
if ($this->item->params->get('access-download') && !$this->item->params->get('valid-password') && $this->item->allow_enter_password)
{
	?>
	<div class="judl-download-password pull-left">
		<div class="input-append">
			<input type="password" name="download_password" id="download_password" class="input-medium"
			       value=""
			       placeholder="<?php echo JText::_("COM_JUDOWNLOAD_PASSWORD"); ?>"/>
			<button id="judl-submit-password" class="btn">
				<i class="fa fa-unlock-alt"></i> <?php echo JText::_("COM_JUDOWNLOAD_UNLOCK"); ?>
			</button>
		</div>
	</div>
<?php
}

// Show download multi files button or external link button or direct error messages
echo $this->loadTemplate('download_multi_button');

if(JUDLPROVERSION && count($this->item->versions) > 1)
{
	?>
	<div class="pull-right">
		<a href="<?php echo JRoute::_(JUDownloadHelperRoute::getDocumentRoute($this->item->id) . '&layout=versions'); ?>">
			<span class="btn btn-mini"><?php echo JText::_('COM_JUDOWNLOAD_OLDER_VERSIONS'); ?> <i class="fa fa-angle-right"></i></span>
		</a>
	</div>
<?php
}

// Show modal
if($this->item->params->get('access-download'))
{
	// Show license modal if need to confirm license and has license
	if (isset($this->item->fields['confirm_license']) && $this->item->fields['confirm_license']->value
		&& isset($this->item->fields['license_id']) && $this->item->fields['license_id']->value
	)
	{
		?>
		<!-- Modal license -->
		<div id="judl-document-license" class="modal hide fade" tabindex="-1" role="dialog"
		     aria-labelledby="judl-document-license-label" aria-hidden="true">
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
				<a href="#" id="judl-accept-license" class="btn btn-primary"
				   data-id="<?php echo $this->item->id; ?>"
				   data-title="<?php echo $this->item->title; ?>"
				   data-downloads="<?php echo $this->item->downloads; ?>">
					<?php echo JText::_("COM_JUDOWNLOAD_AGREE_AND_DOWNLOAD"); ?>
				</a>
				<button class="btn" data-dismiss="modal" aria-hidden="true">
					<?php echo JText::_("COM_JUDOWNLOAD_CANCEL"); ?>
				</button>
			</div>
		</div>
	<?php
	}
}
else
{
	// Show error message modal
	if($this->display_download_rule_msg == "modal")
	{
		?>
		<!-- Modal download error messages -->
		<div id="judl-rule-msg" class="modal hide fade" tabindex="-1" role="dialog"
		     aria-labelledby="judl-download-rule-messages-label" aria-hidden="true">
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
				<button class="btn" data-dismiss="modal" aria-hidden="true">
					<?php echo JText::_("COM_JUDOWNLOAD_CLOSE"); ?>
				</button>
			</div>
		</div>
	<?php
	}
}
?>
	<div>
		<input type="hidden" name="doc_id" value="<?php echo $this->item->id; ?>"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="return" value="<?php echo base64_encode(urlencode(JUri::getInstance())); ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
</div>