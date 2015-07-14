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

$noFollow = $this->params->get("use_nofollow", 1) ? 'rel="nofollow"' : '';

$html = "<div " . $this->getAttribute(null, null, "output") . " >";
if ($this->params->get("open_in", "_blank") == "_blank" || $this->params->get("open_in", "_blank") == "_self")
{
	$target = 'target="' . $this->params->get("open_in", "_blank") . '"';
	$html .= '<a href="' . JRoute::_($link) . '" ' . $noFollow . ' ' . $target . '>' . $linkText . '</a>';
}
elseif ($this->params->get("open_in", "_blank") == "popup")
{
	$popup_width  = $this->params->get("popup_width", "800");
	$popup_height = $this->params->get("popup_height", "500");
	$script       = '
				function openlink_' . $this->id . '(self, $this) {
					var top = (screen.height - ' . (int) $popup_height . ')/2;
					var left = (screen.width - ' . (int) $popup_width . ')/2;
					var new_window = window.open(\'' . $link . '\', \'' . $this->getId() . '\', \'width=' . $popup_width . ', height=' . $popup_height . ', top=\'+top+\', left=\'+left+\', scrollbars=yes\');
					new_window.focus();
				}';
	JFactory::getDocument()->addScriptDeclaration($script);
	$html .= '<a href="' . JRoute::_($link) . '" ' . $noFollow . ' onclick="openlink_' . $this->id . '(); return false;">' . $linkText . '</a>';
}
elseif ($this->params->get("open_in", "_blank") == "lightbox")
{
	JHTML::_('behavior.modal');
	$popup_width  = $this->params->get("popup_width", "800");
	$popup_height = $this->params->get("popup_height", "500");
	$script       = '
				function openlink_' . $this->id . '(self, $this) {
					SqueezeBox.open("' . $link . '", {
						handler: "iframe",
						size   : {
							x: ' . $popup_width . ',
							y: ' . $popup_height . '
						}
					});
				}';
	JFactory::getDocument()->addScriptDeclaration($script);
	$html .= '<a href="' . JRoute::_($link) . '" ' . $noFollow . ' onclick="openlink_' . $this->id . '(); return false;">' . $linkText . '</a>';
}

if ($this->params->get("show_link_counter_output", 0))
{
	$counter = $this->getCounter();
	if (!is_null($counter))
	{
		$html .= '<span class="visit-counter">' . JText::plural('COM_JUDOWNLOAD_N_VISIT', $counter) . '</span>';
	}
}

$html .= "</div>";

echo $html;

?>