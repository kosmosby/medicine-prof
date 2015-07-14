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
<!-- New file template -->
<script id="newfile-template-<?php echo $this->getId(); ?>" type="text/x-handlebars-template">
	<li>
		<input type="file" name="{{field_id}}_new_files[]" id="{{field_id}}_{{id}}">
		<a class="btn btn-mini btn-xs btn-danger newfile-remove"><i class="fa fa-minus-circle"></i> <?php echo JText::_('COM_JUDOWNLOAD_REMOVE'); ?></a>
	</li>
</script>

<?php
$html = "<div id=\"" . $this->getId() . "\" " . $this->getAttribute(null, null, "input") . ">";
$html .= "<ul class=\"file-list nav\">";
if ($files)
{
	foreach ($files AS $key => $file)
	{
		$html .= "<li>";
		$html .= "<a class=\"drag-icon\"></a>";
		$html .= "<div class=\"input-append\">";
		$html .= "<input name=\"" . $this->getName() . "[$key][name]\" type=\"text\" size=\"" . $this->params->get('size', 32) . "\" class=\"file-name required validate-filename\" value=\"" . htmlspecialchars($file->name, ENT_COMPAT, 'UTF-8') . "\" />";
		$html .= "<input type=\"hidden\" class=\"file-remove-value\" name=\"" . $this->getName() . "[$key][remove]\" value=\"0\" checked=\"checked\" />";
		$html .= "<i class=\"add-on file-remove remove fa fa-trash-o\" data-iconremove=\"fa fa-trash-o\" data-iconunremove=\"fa fa-undo\"></i>";
		$html .= "</div>";
		$html .= "<input type=\"hidden\"  name=\"" . $this->getName() . "[$key][id]\" value=\"" . $file->id . "\" />";
		$html .= "</li>";
	}
}
$html .= "</ul>";

$html .= "<ul class=\"files-browser\" >";
if (!$files && $this->isRequired())
{
	if ($this->doc_id)
	{
		$requiredClass = 'class="required"';
	}
	else
	{
		$requiredClass = '';
	}

	$html .= "<li>
				<label style=\"display: none;\" for=\"" . $this->getId() . "_id\">" . JText::_('COM_JUDOWNLOAD_SELECT_FILE') . "</label>
				<input name=\"" . $this->getId() . "_new_files[]\" id=\"" . $this->getId() . "_id\" type=\"file\" $requiredClass />
			</li>";
}
$html .= "</ul>";

// Only show when edit document
if ($files && $this->params->get("show_download_counter_input", 0))
{
	$counter = $this->getCounter();
	if (!is_null($counter))
	{
		$html .= '<span class="download-counter">' . JText::plural('COM_JUDOWNLOAD_N_DOWNLOAD', $counter) . '</span>';
	}
}

$html .= "<a class=\"btn btn-default btn-xs btn-primary add-file\"><i class=\"fa fa-plus\"></i> " . JText::_('COM_JUDOWNLOAD_ADD_FILE') . "</a>";

$html .= "</div>";

echo $html;

?>