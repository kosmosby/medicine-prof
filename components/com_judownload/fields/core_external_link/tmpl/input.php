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

$html = '';

$file_detect_html = '';

if ($file_detect == 1)
{
	$file_detect_html = "<span class=\"add-on pull-left btn btn-success\" title=\"" . JText::_("COM_JUDOWNLOAD_INTERNAL_FILE") . "\" ><i class=\"icon-flag\"></i></span>";
}
elseif ($file_detect == 2)
{
	$file_detect_html = "<span class=\"add-on pull-left btn btn-warning\" title=\"" . JText::_("COM_JUDOWNLOAD_MISSING_FILE") . "\" ><i class=\"icon-warning\"></i></span>";
}

$this->addAttribute("style", "margin: 0;", "input");
$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
$this->setAttribute("value", $value, "input");

$input = '<input name="' . $this->getName() . '" id="' . $this->getId() . '"' . ' ' . $this->getAttribute(null, null, "input") . ' />';

if ($this->params->get("show_go_button", 1))
{
	$html .= "<div class=\"input-append\">";
	$html .= $input . $file_detect_html;
	$html .= "<button type=\"button\" class=\"btn\" onclick=\"javascript:if(document.getElementById('" . $this->getId() . "').value) window.open(document.getElementById('" . $this->getId() . "').value);\">" . JText::_('COM_JUDOWNLOAD_GO') . "</button>";
	$html .= "</div>";
}
else
{
	$html .= $input . $file_detect_html;
}

echo $html;

?>