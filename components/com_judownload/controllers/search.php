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

jimport('joomla.application.component.controllerform');

class JUDownloadControllerSearch extends JControllerForm
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_SEARCH';

	public function search()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$app        = JFactory::getApplication();
		$searchWord = $app->input->post->getString('searchword', '');
		$cat_id     = $app->input->post->getInt('cat_id', 0);
		$sub_cat    = $app->input->post->getInt('sub_cat', 0);
		
		$searchWord = JUDownloadFrontHelper::UrlEncode($searchWord);

		$this->setRedirect(JRoute::_(JUDownloadHelperRoute::getSearchRoute($cat_id, $sub_cat, $searchWord), false));
	}
}