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

if ($value == '*' || $value == '')
{
	$value = JText::_('JALL_LANGUAGE');
}
elseif ($value)
{
	$langArr = explode("-", $value);
	$this->setAttribute("style", "background: url(media/mod_languages/images/" . $langArr[0] . ".gif) no-repeat; padding-left:20px", "output");
}

echo '<span ' . $this->getAttribute(null, null, "output") . '>' . $value . '</span>';
?>