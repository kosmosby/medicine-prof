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

if ($this->isDetailsView($options))
{
	$document = JFactory::getDocument();
	$script   = "jQuery(document).ready(function($){
                    $('input[type=radio].star').rating({starWidth:" . $this->starWidth . "});
                });";
	switch ($options->get('type', 'default'))
	{
		default:
		case 'details_view':
			$document->addScriptDeclaration($script);
			echo $this->fetch('details_view.php', $className);
			break;
		case 'comment_form':
			$document->addScriptDeclaration($script);
			echo $this->fetch('comment_form.php', $className);
			break;
		case 'comment':
			echo $this->fetch('comment.php', $className);
			break;
	}
}
else
{
	echo $this->fetch('list_view.php', $className);
}
?>