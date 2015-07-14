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
	$html = "<fieldset id=\"" . $this->getId() . "\" class=\"checkboxes " . $this->getInputClass() . "\">";
	$html .= "<ul class='nav'>";

	$number_columns = $this->params->get("number_columns", 0);
	foreach ($options AS $key => $option)
	{
		if ($number_columns)
		{
			$width = 100 / (int) $number_columns;
			$html .= '<li class="" style="width: ' . $width . '%; float: left; clear: none;" >';
		}
		else
		{
			$html .= "<li>";
		}

		if ($option->text == strtoupper($option->text))
		{
			$text = JText::_($option->text);
		}
		else
		{
			$text = $option->text;
		}

		$text = htmlspecialchars($text);

		$this->setAttribute("value", htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8'), "input");

		if (in_array($option->value, $value))
		{
			$this->setAttribute("checked", "checked", "input");
		}
		else
		{
			$this->setAttribute("checked", null, "input");
		}

		if ((isset($option->disabled) && $option->disabled))
		{
			$this->setAttribute("disabled", "disabled", "input");
		}
		else
		{
			$this->setAttribute("disabled", null, "input");
		}

		$html .= "<div class=\"checkbox\">";
		$html .= "<label for=\"" . $this->getId() . $key . "\">";
		$html .= "<input id=\"" . $this->getId() . $key . "\" name=\"" . $this->getName() . "\" " . $this->getAttribute(null, null, "input") . " />";
		$html .= " " . $text;
		$html .= "</label>";
		$html .= "</div>";
		$html .= "</li>";
	}

	$html .= "</ul>";
	$html .= "</fieldset>";

	echo $html;
}
?>