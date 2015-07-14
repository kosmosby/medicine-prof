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
?>

<div id="judl-container" class="jubootstrap component judl-container view-downloaderror">
	<?php
	$app = JFactory::getApplication();
	$return = $app->input->get('return', null, 'base64');
	if (empty($return) || !JUri::isInternal(urldecode(base64_decode($return))))
	{
		$returnPage = JUri::base();
	}
	else
	{
		$returnPage = urldecode(base64_decode($return));
	}
	?>
	<?php echo JText::sprintf('COM_JUDOWNLOAD_YOU_CAN_CLICK_HERE_TO_GO_BACK_TO_LAST_PAGE', '<a href="' . $returnPage . '">' . JText::_('COM_JUDOWNLOAD_CLICK_HERE') . '</a>'); ?>
</div>
