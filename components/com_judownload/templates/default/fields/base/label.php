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

$title = "";
$class = "control-label col-sm-2";

if ($this->description)
{
	if (JUDownloadHelper::isJoomla3x())
	{
		$class .= " hasTooltip";
		$separator = "<br/>";
	}
	else
	{
		$separator = "::";
		$class .= " hasTip";
	}
	// If write description as UPPERCASE -> translate it
	if ($this->description == strtoupper($this->description))
	{
		$description = JText::_($this->description);
	}
	else
	{
		$description = $this->description;
	}

	$title = htmlspecialchars('<strong>' . trim($this->getCaption(), ':') . '</strong>' . $separator . $description, ENT_COMPAT, 'UTF-8');
}

$this->addAttribute("class", $class, "label");
$this->setAttribute("for", $this->getId(), "label");
$this->setAttribute("title", $title, "label");

$html = "<label id=\"".$this->getId()."-lbl\" " . $this->getAttribute(null, null, "label") . ">";

if ($this->hide_caption)
{
	// Still put caption as hidden, so js validate can working properly
	$html .= "<span style=\"display: none;\">" . $this->getCaption(true) . "</span>";
}
elseif ($required && $this->isRequired())
{
	$html .= $this->getCaption() . "<span class=\"star\">&#160;*</span>";
}
else
{
	$html .= $this->getCaption();
}

$html .= "</label>";

echo $html;
?>