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

$html = "<div id=\"" . $this->getId() . "_wrap\" class=\"document-icon\" style='margin-bottom: 5px'>";
if ($icon_src)
{
	$html .= "<a class='modal' rel=\"{handler: 'iframe', size: {x: 500, y: 400}}\" href='" . JUri::root(true) . "/index.php?option=com_judownload&view=defaulticons&type=icon&tmpl=component' >
					<img class=\"icon-src hasTooltip\" title=\"" . JText::_('COM_JUDOWNLOAD_CLICK_TO_SELECT_ICON_FROM_LIBRARY') . "\" src=\"" . $icon_src . "\" />
					<input class=\"icon-value\" type=\"hidden\" value=\"" . $value . "\" name=\"" . $this->getName() . "\" data-ori-image-url=\"" . $icon_src . "\" data-ori-image-value=\"" . $value . "\">
				</a>";
	$html .= "<div class=\"action\">";
	if ($value && !$this->isRequired())
	{
		$html .= "<a href=\"#\" class=\"remove-icon\"><i class=\"icon-trash\"></i> " . JText::_('COM_JUDOWNLOAD_REMOVE') . "</a>";
	}
	$html .= "<a href=\"#\" class=\"revert-icon hidden\"><i class=\"icon-undo\"></i> " . JText::_('COM_JUDOWNLOAD_REVERT') . "</a>";
	$html .= "</div>";
}

$html .= "<div class=\"upload-icon\" style=\"\">";
$html .= "<input id=\"" . $this->getId() . "\" name=\"" . $this->getId() . "_icon\" " . $this->getAttribute(null, null, "input") . " />";
$html .= "<div class=\"clearfix\"><i>" . JText::_('COM_JUDOWNLOAD_MAX_UPLOAD_FILESIZE') . " <strong>" . $max_upload . "</strong></i></div>";
$html .= "</div>";

$html .= "</div>";

echo $html;
?>