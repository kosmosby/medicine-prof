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

class JUDownloadControllerForms extends JControllerAdmin
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_DOCUMENTS';

	
	public function getModel($name = 'Form', $prefix = 'JUDownloadModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	
	public function publish()
	{
		
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

		
		$cid   = JFactory::getApplication()->input->get('id', array(), 'array');
		$data  = array('publish' => 1, 'unpublish' => 0);
		$task  = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid))
		{
			JError::raiseWarning(500, JText::_('COM_JUDOWNLOAD_NO_ITEM_SELECTED'));
		}
		else
		{
			
			$model = $this->getModel();

			
			JArrayHelper::toInteger($cid);

			
			if (!$model->publish($cid, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				if ($value == 1)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
				}
				elseif ($value == 0)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';

				}

				foreach ($cid AS $id)
				{
					
					JUDownloadFrontHelperMail::sendEmailByEvent('document.editstate', $id);
				}

				$this->setMessage(JText::plural($ntext, count($cid)));

				
				$user = JFactory::getUser();
				foreach ($cid AS $id)
				{
					$logData = array(
						'user_id'   => $user->id,
						'event'     => 'document.editstate',
						'item_id'   => $id,
						'doc_id'    => $id,
						'value'     => $value,
						'reference' => '',
					);

					JUDownloadFrontHelperLog::addLog($logData);
				}
			}
		}

		$this->setRedirect(JRoute::_($this->getReturnPage()));
	}

	
	public function checkin()
	{
		
		JSession::checkToken('get') or JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		
		$ids = JFactory::getApplication()->input->get('id', array(), 'array');

		$model  = $this->getModel();
		$return = $model->checkin($ids);
		if ($return === false)
		{
			
			$message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$this->setRedirect($this->getReturnPage(), $message, 'error');

			return false;
		}
		else
		{
			
			$message = JText::plural($this->text_prefix . '_N_ITEMS_CHECKED_IN', count($ids));
			$this->setRedirect($this->getReturnPage(), $message);

			return true;
		}
	}

	public function delete()
	{
		
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

		
		$cid = JFactory::getApplication()->input->get('id', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseWarning(500, JText::_('COM_JUDOWNLOAD_NO_ITEM_SELECTED'));
			$this->setRedirect($this->getReturnPage());
		}
		else
		{
			
			$model = $this->getModel();

			
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			
			if ($model->delete($cid))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError());
			}

			$isDocumentPublished = JUDownloadFrontHelperDocument::isDocumentPublished($cid[0]);
			if ($isDocumentPublished)
			{
				$mainCategoryId = JUDownloadFrontHelperCategory::getMainCategoryId($cid[0]);
				$this->setRedirect(JRoute::_(JUDownloadHelperRoute::getCategoryRoute($mainCategoryId), false));
			}
			else
			{
				$this->setRedirect($this->getReturnPage());
			}
		}
	}

	protected function getReturnPage()
	{
		$app    = JFactory::getApplication();
		$return = $app->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(urldecode(base64_decode($return))))
		{
			return JUri::base();
		}
		else
		{
			return urldecode(base64_decode($return));
		}
	}
}
