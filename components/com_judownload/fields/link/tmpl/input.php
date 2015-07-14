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

$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
$this->setAttribute("value", $value, "input");

if ($this->params->get("show_go_button", 1))
{
	$html = "<div class=\"input-append\">";
	$html .= "<input id=\"" . $this->getId() . "\" name=\"" . $this->getName() . "\" " . $this->getAttribute(null, null, "input") . "/>";
	$html .= "<button type=\"button\" class=\"btn\" onclick=\"javascript:if(document.getElementById('" . $this->getId() . "').value) window.open(document.getElementById('" . $this->getId() . "').value);\">" . JText::_('COM_JUDOWNLOAD_GO') . "</button>";
	$html .= "</div>";
}
else
{
	$html = "<input id=\"" . $this->getId() . "\" name=\"" . $this->getName() . "\" " . $this->getAttribute(null, null, "input") . "/>";
}


if ($this->params->get("show_link_counter_input", 0))
{
	$counter = $this->getCounter();
	if (!is_null($counter))
	{
		$html .= '<span class="visit-counter">' . JText::plural('COM_JUDOWNLOAD_N_VISIT', $counter) . '</span>';
	}
}

echo $html;

?>