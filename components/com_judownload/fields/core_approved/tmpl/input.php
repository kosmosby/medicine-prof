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

if ($options)
{
	$this->addAttribute("class", "radio btn-group", "input");

	$html = "<fieldset id=\"" . $this->getId() . "\" " . $this->getAttribute(null, null, "input") . ">";

	foreach ($options AS $key => $option)
	{
		if ($option->text == strtoupper($option->text))
		{
			$text = JText::_($option->text);
		}
		else
		{
			$text = $option->text;
		}
		$text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');

		$this->setAttribute("value", htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8'), "input");

		if ($option->value == $value)
		{
			$this->setAttribute("checked", "checked", "input");
		}
		else
		{
			$this->setAttribute("checked", null, "input");
		}

		
		$tmpDoc = JUDownloadHelper::getTempDocument($this->doc_id);
		if (is_object($tmpDoc))
		{
			$this->setAttribute("disabled", "disabled", "input");
		}
		else
		{
			$this->setAttribute("disabled", null, "input");
			if (is_object($this->doc) && $this->doc->approved < 0)
			{
				$this->setAttribute("disabled", "disabled", "input");
			}
		}

		$html .= "<input id=\"" . $this->getId() . $key . "\" name=\"" . $this->getName() . "\" " . $this->getAttribute(null, null, "input") . " /> <label for=\"" . $this->getId() . $key . "\">$text</label>";
	}

	if (is_object($this->doc) && $this->doc->approved < 0)
	{
		$html .= "<input type=\"hidden\" name=\"" . $this->getName() . "\" value=\"" . $value . "\" />";
	}
	$html .= "</fieldset>";

	echo $html;
}
?>