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

echo '<input id="' . $this->getId() . '" name="' . $this->getName() . '" ' . $this->getAttribute(null, null, "input") . ' />';

?>