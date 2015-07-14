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

$html = array();
foreach ($value AS $category)
{
	$html[] = '<a href="' . JRoute::_(JUDownloadHelperRoute::getCategoryRoute($category->id)) . '">' . $category->title . '</a>';
}

$this->setAttribute("style", "display: inline;", "output");

echo '<div ' . $this->getAttribute(null, null, "output") . '>';
echo implode("<span>, </span>", $html);
echo '</div>';

?>