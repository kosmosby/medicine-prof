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

$html = '<input id="' . $this->getId() . '_name" ' . $this->getAttribute(null, null, "input") . '/>';
$html .= '<input type="hidden" id="' . $this->getId() . '" name="' . $this->getName() . '" value="' . $user->id . '" />';

echo $html;
?>