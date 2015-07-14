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
<?php
if (!isset($this->item->fields['external_link']) || $this->item->fields['external_link']->value == '')
{
	?>
	<table class="table table-striped table-bordered">
	<thead>
	<tr>
		<th class="file-name"  width="40%">
			<?php echo JText::_('COM_JUDOWNLOAD_FIELD_FILE_NAME'); ?>
		</th>
		<th class="file-filesize hidden-xs hidden-sm" width="20%">
			<?php echo JText::_('COM_JUDOWNLOAD_FIELD_FILE_SIZE'); ?>
		</th>
		<th class="file-filetype hidden-xs hidden-sm" width="20%">
			<?php echo JText::_('COM_JUDOWNLOAD_FIELD_FILE_MIME_TYPE'); ?>
		</th>
		<th class="file-downloads hidden-xs hidden-sm" width="20%">
			<?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOWNLOADS'); ?>
		</th>
	</tr>
	</thead>

	<tbody>
	<?php
	foreach ($this->item->files AS $key => $file)
	{
		?>
		<tr>
			<td>
				<a href="#" id="judl-file-title-id-<?php echo $file->id; ?>" class="judl-file-info-modal"
				   title="<?php echo $file->title; ?>"><?php echo $file->rename; ?></a>
				<!-- File modal -->
				<div id="judl-file-info-<?php echo $file->id; ?>" class="modal fade" tabindex="-1"
				     role="dialog" aria-labelledby="judl-file-info-label-<?php echo $file->id; ?>"
				     aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
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
					</div>
				</div>
			</td>
			<td class="hidden-xs hidden-sm">
				<?php echo $file->size; ?>
			</td>
			<td class="hidden-xs hidden-sm">
				<?php echo $file->mime_type; ?>
			</td>
			<td class="hidden-xs hidden-sm">
				<?php echo $file->downloads; ?>
			</td>
		</tr>
	<?php
	} ?>
	</tbody>
	</table>
<?php
} ?>
</div>