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

$license = $value;

$this->setAttribute("href", JRoute::_(JUDownloadHelperRoute::getLicenseRoute($license->id), false), "output");
$this->setAttribute("title", $license->title, "output");

$html = '<a ' . $this->getAttribute(null, null, "output") . '>';
$html .= $license->title;
$html .= '</a>';

echo $html;
?>