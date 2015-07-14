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

// If is_numeric -> search by value range
if ($this->params->get("is_numeric", 0))
{
	$this->setAttribute("class", "form-control", "search");

	$defaultValueFrom = isset($defaultValue["from"]) ? $defaultValue["from"] : "";
	$this->setAttribute("value", $defaultValueFrom, "search");
	$html = "<div class=\"row\" >";
	$html .= "<div class=\"col-sm-6\" >";
	$html .= "<div class=\"input-group\">";
	$html .= "<span class=\"input-group-btn from\">";
	$html .= "<span class=\"btn btn-default\">".JText::_('COM_JUDOWNLOAD_FROM')."</span>";
	$html .= "</span>";
	$html .= "<input id=\"" . $this->getId() . "_from\" name=\"" . $this->getName() . "[from]\" " . $this->getAttribute(null, null, "search") . " />";
	$html .= "</div>";
	$html .= "</div>";

	$defaultValueTo = isset($defaultValue["to"]) ? $defaultValue["to"] : "";
	$this->setAttribute("value", $defaultValueTo, "search");
	$html .= "<div class=\"col-sm-6\" >";
	$html .= "<div class=\"input-group\">";
	$html .= "<span class=\"input-group-btn to\">";
	$html .= "<span class=\"btn btn-default\">".JText::_('COM_JUDOWNLOAD_TO')."</span>";
	$html .= "</span>";
	$html .= "<input id=\"" . $this->getId() . "_to\" name=\"" . $this->getName() . "[to]\" " . $this->getAttribute(null, null, "search") . " />";
	$html .= "</div>";
	$html .= "</div>";
	$html .= "</div>";
}
else
{
	if ($this->params->get("placeholder", ""))
	{
		$placeholder = htmlspecialchars($this->params->get("placeholder", ""), ENT_COMPAT, 'UTF-8');
		$this->setAttribute("placeholder", $placeholder, "input");
	}

	$this->setAttribute("value", htmlspecialchars($defaultValue, ENT_COMPAT, 'UTF-8'), "search");

	$html = "<input id=\"" . $this->getId() . "\" name=\"" . $this->getName() . "\" " . $this->getAttribute(null, null, "search") . " />";
}

echo $html;
?>