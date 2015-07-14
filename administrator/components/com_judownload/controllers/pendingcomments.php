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


class JUDownloadControllerPendingComments extends JControllerAdmin
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_PENDING_COMMENTS';

	
	public function getModel($name = 'PendingComment', $prefix = 'JUDownloadModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function approve()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		$app = JFactory::getApplication();
		$cid = $app->input->get('cid', array(), 'array');

		$model = $this->getModel();
		
		$total = $model->approve($cid);
		if ($total)
		{
			$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_APPROVED', $total));
		}
		else
		{
			$this->setMessage($model->getError());
		}
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}
}
