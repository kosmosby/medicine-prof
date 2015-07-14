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


if (is_array($values))
{
	if (!count($values))
	{
		return "";
	}

	$html = "<ul class='nav' " . $this->getAttribute(null, null, "output") . ">";
	foreach ($values AS $value)
	{
		
		if ($this->params->get("tag_search", 0))
		{
			$item = "<a href =\"" . JRoute::_("index.php?option=com_judownload&view=searchby&field_id=" . $this->id . "&value=" . JUDownloadFrontHelper::UrlEncode($value), false) . "\">" . $value . "</a>";
		}
		else
		{
			$item = $value;
		}

		$html .= "<li>" . $item . "</li>";
	}
	$html .= "</ul>";
}

else
{
	if ($values === "")
	{
		return "";
	}

	if ($this->params->get("is_numeric", 0))
	{
		$totalNumbers  = $this->params->get("digits_in_total", 11);
		$decimals      = $this->params->get("digits_after_decimal", 2);
		$dec_point     = $this->params->get("dec_point", ".");
		$thousands_sep = $this->params->get("use_thousands_sep", 0) ? $this->params->get("thousands_sep", ",") : "";
		
		$html_values = $this->numberFormat($values, $totalNumbers, $decimals, $dec_point, $thousands_sep);
	}
	elseif ($this->params->get("tag_search", 0))
	{
		
		if (strpos($values, "|") !== false)
		{
			$values = explode("|", $values);
		}
		elseif (strpos($values, ",") !== false)
		{
			$values = explode(",", $values);
		}

		if ($values)
		{
			$items  = array();
			$values = (array) $values;
			foreach ($values AS $value)
			{
				if ($value)
				{
					
					$items[] = "<span><a href =\"" . JRoute::_("index.php?option=com_judownload&view=searchby&field_id=" . $this->id . "&value=" . JUDownloadFrontHelper::UrlEncode($value)) . "\">" . $value . "</a></span>";
				}
			}
			$html_values = implode("<span class='divider'>, </span>", $items);
		}
		else
		{
			$html_values = "";
		}
	}
	else
	{
		$html_values = $values;
	}

	
	$html_values = $this->params->get("prepend_value", "") . $html_values . $this->params->get("append_value", "");
	if ($this->params->get("parse_plugin", 0))
	{
		$html_values = JHtml::_('content.prepare', $html_values);
	}

	$html = "<div " . $this->getAttribute(null, null, "output") . " >";
	$html .= $html_values;
	$html .= "</div>";
}

echo $html;
?>