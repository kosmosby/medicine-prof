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


jimport('joomla.application.component.controlleradmin');


class JUDownloadControllerComments extends JControllerAdmin
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_COMMENTS';

	
	public function getModel($name = 'Comment', $prefix = 'JUDownloadModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function back()
	{
		$app    = JFactory::getApplication();
		$doc_id = $app->input->getInt('doc_id', 0);
		$cat_id = JUDownloadFrontHelperCategory::getRootCategory()->id;
		if ($doc_id)
		{
			$docObj = JUDownloadHelper::getDocumentById($doc_id);
			if (isset($docObj->cat_id) && $docObj->cat_id)
			{
				$cat_id = $docObj->cat_id;
			}
		}

		$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=$cat_id");
	}

	
	public function saveorder()
	{
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		
		$app           = JFactory::getApplication();
		$order         = $app->input->post->get('order', null, 'array');
		$originalOrder = explode(',', $app->input->post->get('original_order_values', null, 'string'));

		
		if (!($order === $originalOrder))
		{
			parent::saveorder();
		}
		else
		{
			
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

			return true;
		}
	}
}
