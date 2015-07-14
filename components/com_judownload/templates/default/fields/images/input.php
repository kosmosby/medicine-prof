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
<!-- New image template -->
<script id="newimage-template-<?php echo $this->getId(); ?>" type="text/x-handlebars-template">
	<li>
		<input type="file" name="{{field_id}}_new_images[]" id="{{field_id}}_{{id}}">
		<a class="btn btn-mini btn-xs btn-danger newimage-remove"><i class="fa fa-minus-circle"></i> <?php echo JText::_('COM_JUDOWNLOAD_REMOVE'); ?></a>
	</li>
</script>

<!-- Image form template -->
<script id="imageform-template-<?php echo $this->getId(); ?>" type="text/x-handlebars-template">
	<div id="{{ef_id}}-img-element-data-form" class="img-element-data-form" style="float: left">
		<div class="form-horizontal" style="margin: 10px">
			<div class="form-group">
				<label for="imgtitle" class="control-label col-xs-2"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_TITLE'); ?></label>
				<div class="col-xs-10">
					<input type="text" name="imgtitle" class="imgtitle" value="{{image.title}}" size="50" />
				</div>
			</div>
			<div class="form-group">
				<label for="imgdescription" class="control-label col-xs-2"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_DESCRIPTION'); ?></label>
				<div class="col-xs-10">
					<textarea rows="5" cols="47" name="imgdescription" class="imgdescription" style="width: auto; height: auto" >{{image.description}}</textarea>
				</div>
			</div>
			<div class="form-group">
				<label for="imgdescription" class="control-label col-xs-2"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_PUBLISHED'); ?></label>
				<div class="col-xs-10">
					<input name="imgpublished" class="imgpublished" type="checkbox" {{checked}} value="1" />
				</div>
			</div>
			<div class="form-group" style="margin-bottom: 0">
				<div class="col-xs-offset-2 col-xs-10" >
					<input class="btn" onclick="updateEFImageData('{{ef_id}}'); return false;" type="button" value="<?php echo JText::_("COM_JUDOWNLOAD_UPDATE"); ?>" />
					<input class="btn" onclick="eFImageFormClose('{{ef_id}}'); return false;" type="button" value="<?php echo JText::_("COM_JUDOWNLOAD_CANCEL"); ?>" />
				</div>
			</div>
		</div>
	</div>
</script>

<?php
$html = "<div id=\"" . $this->getId() . "\" " . $this->getAttribute(null, null, "input") . ">";
$html .= "<ul class=\"image-list nav\">";
$image_url = JUri::root() . JUDownloadFrontHelper::getDirectory("field_attachment_directory", "media/com_judownload/field_attachments/", true) . "images/" . $this->id . "_" . $this->doc_id . "/";
if ($images)
{
	foreach ($images AS $key => $image)
	{
		$link          = $image_url . $image->name;
		$publish_class = $image->published == 0 ? "unpublished" : "";
		$html .= "<li>";
		$html .= "<div class=\"img-element $publish_class\">";
		$html .= "<img class=\"img-item\" alt=\"Image\" src=\"" . $link . "\" />";
		$html .= "<span class=\"view-image\" title=\"" . JText::_('COM_JUDOWNLOAD_VIEW_IMAGE') . "\">" . JText::_('COM_JUDOWNLOAD_VIEW_IMAGE') . "</span>";
		if ($publish_class)
		{
			$html .= '<span class="published-image" name="published" title="' . JText::_('COM_JUDOWNLOAD_CLICK_TO_UNPUBLISH') . '">' . JText::_('COM_JUDOWNLOAD_PUBLISHED') . '</span>';
		}
		else
		{
			$html .= '<span class="published-image" name="published" title="' . JText::_('COM_JUDOWNLOAD_CLICK_TO_PUBLISH') . '">' . JText::_('COM_JUDOWNLOAD_UNPUBLISHED') . '</span>';
		}
		$html .= "<span class=\"remove-image\" title=\"" . JText::_('COM_JUDOWNLOAD_REMOVE_IMAGE') . "\">" . JText::_('COM_JUDOWNLOAD_REMOVE_IMAGE') . "</span>";
		$html .= "<span class=\"edit-image\" title=\"" . JText::_('COM_JUDOWNLOAD_EDIT_IMAGE') . "\"></span>";
		$html .= "<input type=\"hidden\" class=\"image-name-value\" value=\"" . htmlspecialchars($image->name, ENT_COMPAT, 'UTF-8') . "\" name=\"" . $this->getName() . "[$key][name]\" />";
		$html .= "<input type=\"hidden\" class=\"remove-image-value\" value=\"0\" name=\"" . $this->getName() . "[$key][remove]\" />";
		$html .= "<input type=\"hidden\" class=\"published-image-value\" value=\"" . $image->published . "\" name=\"" . $this->getName() . "[$key][published]\" />";
		$html .= "<input type=\"hidden\" class=\"title-image-value\" value=\"" . htmlspecialchars($image->title, ENT_QUOTES) . "\" name=\"" . $this->getName() . "[$key][title]\" />";
		$html .= "<input type=\"hidden\" class=\"description-image-value\" value=\"" . htmlspecialchars($image->description, ENT_QUOTES) . "\" name=\"" . $this->getName() . "[$key][description]\" />";
		$html .= "<div class=\"remove-image-mask\" style=\"display: none;\"></div>";
		$html .= "</div>";
		$html .= "</li>";
	}
}
$html .= "</ul>";

$html .= "<ul class=\"images-browser\" >";
if (!$images && $this->isRequired())
{
	if ($this->doc_id)
	{
		$requiredClass = 'class="required validate-images"';
	}
	else
	{
		$requiredClass = 'class="validate-images"';
	}

	$html .= "<li>
					<label style=\"display:none;\" for=\"" . $this->getId() . "_id\">" . JText::_('COM_JUDOWNLOAD_SELECT_FILE') . "</label>
					<input name=\"" . $this->getId() . "_new_images[]\" id=\"" . $this->getId() . "_id\" type=\"file\" $requiredClass />
			 </li>";
}
$html .= "</ul>";
$html .= "<a class=\"btn btn-default btn-xs btn-primary add-image\"><i class=\"fa fa-plus\"></i> " . JText::_('COM_JUDOWNLOAD_ADD_IMAGE') . "</a>";
$html .= "<div class=\"squeezebox-placeholder\" style=\"display: none;\"></div>";
$html .= "</div>";

echo $html;

?>