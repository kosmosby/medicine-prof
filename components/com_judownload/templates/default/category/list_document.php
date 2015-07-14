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

$item_class = "judl-doc";
if ($this->item->label_new)
{
	$item_class .= " new";
}

if ($this->item->label_updated)
{
	$item_class .= " updated";
}

if ($this->item->label_hot)
{
	$item_class .= " hot";
}

if ($this->item->label_featured)
{
	$item_class .= " featured";
}

// Get all list view fields, also add 'external_link', 'license_id', 'confirm_license'
$additionFields = array();
if (trim($this->item->external_link))
{
	$additionFields[] = 'external_link';
}

if ($this->item->license_id && $this->item->confirm_license)
{
	$additionFields[] = 'license_id';
	$additionFields[] = 'confirm_license';
}

$specialFields = JUDownloadFrontHelperField::getFields($this->item, null, array("created", "title", "version", "featured", "downloads"), array(), $additionFields);
?>

<tr class="<?php echo $item_class; ?>">
<?php
if ($this->params->get('allow_zip_file', 1) && $this->params->get('allow_download_multi_docs', 0))
{ ?>
	<td class="id">
		<?php
		if ($this->item->total_files > 0 || (isset($specialFields['external_link']) && !empty($specialFields['external_link'])))
		{
			$canDownloadDocument = JUDownloadFrontHelperPermission::canDownloadDocument($this->item->id, false);
			if (!$canDownloadDocument)
			{ ?>
				<input type="checkbox" name="doc_id[]" class="judl-cb-disabled" disabled
				       value="<?php echo $this->item->id; ?>" id="cb<?php echo $this->index; ?>"/>
			<?php
			}
			else
			{
				if ($this->item->params->get('valid-password'))
				{ ?>
					<input type="checkbox" checked="checked" name="doc_id[]" class="judl-cb"
					       value="<?php echo $this->item->id; ?>" id="cb<?php echo $this->index; ?>"/>
				<?php
				}
				else
				{ ?>
					<input type="checkbox" name="doc_id[]" class="judl-cb-disabled" disabled
					       value="<?php echo $this->item->id; ?>" id="cb<?php echo $this->index; ?>"/>
				<?php
				} ?>
			<?php
			} ?>
		<?php
		}
		else
		{ ?>
			<input type="checkbox" name="doc_id[]" class="judl-cb-disabled" disabled
			       value="<?php echo $this->item->id; ?>"
			       id="cb<?php echo $this->index; ?>"/>
		<?php
		} ?>
	</td>
<?php
} ?>

<td class="title">
	<?php
	$titleField = isset($specialFields['title']) ? $specialFields['title'] : null;
	if ($titleField && $titleField->canView())
	{
		?>
		<div class="title-wrapper">
			<h3 class="doc-title">
				<?php
				echo $titleField->getDisplayPrefixText() . " " . $titleField->getOutput(array("view" => "list", "template" => $this->template)) . " " . $titleField->getDisplaySuffixText();

				if ($this->item->label_new)
				{
					?>
					<span class="label label-new"><?php echo JText::_('COM_JUDOWNLOAD_NEW'); ?></span>
				<?php
				}
				if ($this->item->label_updated)
				{
					?>
					<span class="label label-updated"><?php echo JText::_('COM_JUDOWNLOAD_UPDATED'); ?></span>
				<?php
				}
				if ($this->item->label_hot)
				{
					?>
					<span class="label label-hot"><?php echo JText::_('COM_JUDOWNLOAD_HOT'); ?></span>
				<?php
				}
				if ($this->item->label_featured)
				{
					?>
					<span class="label label-featured"><?php echo JText::_('COM_JUDOWNLOAD_FEATURED'); ?></span>
				<?php
				} ?>
			</h3>
	<?php
	}

	echo $this->loadTemplate('private_actions');
	?>
		</div>
</td>

<td class="created">
	<?php $createdField = isset($specialFields['created']) ? $specialFields['created'] : null;
	if ($createdField && $createdField->canView(array("view" => "list")))
	{
		?>
		<div class="value">
			<?php echo $createdField->getDisplayPrefixText() . " " . $createdField->getOutput(array("view" => "list", "template" => $this->template)) . " " . $createdField->getDisplaySuffixText(); ?>
		</div>
	<?php
	} ?>
</td>

<td class="version">
	<?php
	$versionField = isset($specialFields['version']) ? $specialFields['version'] : null;
	if ($versionField && $versionField->canView(array("view" => "list")))
	{
		?>
		<span class="value">
			<?php echo $versionField->getDisplayPrefixText() . " " . $versionField->getOutput(array("view" => "list", "template" => $this->template)) . " " . $versionField->getDisplaySuffixText(); ?>
		</span>
	<?php
	} ?>
</td>

<td class="downloads">
	<?php $downloadsField = isset($specialFields['downloads']) ? $specialFields['downloads'] : null;
	if ($downloadsField && $downloadsField->canView(array("view" => "list")))
	{
		?>
		<span class="value">
			<?php echo $downloadsField->getDisplayPrefixText() . " " . $downloadsField->getOutput(array("view" => "list", "template" => $this->template)) . " " . $downloadsField->getDisplaySuffixText(); ?>
		</span>
	<?php
	} ?>
</td>

<?php
if ($this->params->get('show_download_btn_in_listview', 1))
{
	?>
	<td class="download">
		<?php
			echo $this->loadTemplate('download_button');
		?>
	</td>
<?php
}
?>
</tr>