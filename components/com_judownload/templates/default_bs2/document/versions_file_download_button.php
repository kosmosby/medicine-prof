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

$fileDownloadLink = JRoute::_('index.php?option=com_judownload&task=download.download&file_id=' . $this->item->version->file_id . '&version=' . $this->item->version->version . '&' . $this->token . '=1');
$fileDownloadLink .= '&amp;return=' . base64_encode(urlencode(JUri::getInstance()));
if ($this->item->params->get('access-download'))
{
	if ($this->item->params->get('valid-password'))
	{
		$downloadFileTitle = JText::_('COM_JUDOWNLOAD_DOWNLOAD') . ' ' . $this->item->version->rename . ' - ' . JText::_('COM_JUDOWNLOAD_VERSION') . ': ' . $this->item->version->version;
		if (isset($this->item->fields['confirm_license']) && $this->item->fields['confirm_license']->value
			&& isset($this->item->fields['license_id']) && $this->item->fields['license_id']->value
		)
		{
			?>
			<a href="<?php echo $fileDownloadLink; ?>" title="<?php echo $downloadFileTitle; ?>"
			   role="button"
			   class="btn btn-mini file-active-license hasTooltip" data-toggle="modal">
				<i class="fa fa-download"></i> <?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
			</a>
		<?php
		}
		else
		{
			?>
			<a href="<?php echo $fileDownloadLink; ?>" title="<?php echo $downloadFileTitle; ?>"
			   class="btn btn-mini judl-accept-download hasTooltip"
			   data-id="<?php echo $this->item->id; ?>"
			   data-title="<?php echo $this->item->title; ?>"
			   data-downloads="<?php echo $this->item->downloads; ?>">
				<i class="fa fa-download"></i> <?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
			</a>
		<?php
		}
	}
	else
	{
		?>
		<a href="#" class="btn btn-mini disabled">
			<i class="fa fa-download"></i> <?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
		</a>
	<?php
	}
}
else
{
	if (isset($this->item->error_msg))
	{
		if ($this->display_download_rule_msg == "redirect")
		{
			?>
			<a href="<?php echo $fileDownloadLink; ?>" class="btn btn-mini"
			   title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>">
				<i class="fa fa-download"></i> <?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
			</a>
		<?php
		}
		elseif ($this->display_download_rule_msg == "modal")
		{
			?>
			<a href="#judl-rule-msg" class="btn btn-mini"
			   title="<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>" data-toggle="modal">
				<i class="fa fa-download"></i> <?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
			</a>
		<?php
		}
	}
} ?>