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

if ($value)
{
	$html = "<ul " . $this->getAttribute(null, null, "output") . ">";
	foreach ($value AS $tag)
	{
		$tagLink = JRoute::_(JUDownloadHelperRoute::getTagRoute($tag->id));
		$html .= '<li><a href="' . $tagLink . '">' . $tag->title . '</a></li>';
	}
	$html .= "</ul>";

	echo $html;
}
?>