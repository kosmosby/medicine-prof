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


class JUDownloadControllerFields extends JControllerAdmin
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_FIELDS';

	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('priorityup', 'repriority');
		$this->registerTask('prioritydown', 'repriority');
		$this->registerTask('blvup', 'reblvorder');
		$this->registerTask('blvdown', 'reblvorder');
	}

	
	public function getModel($name = 'Field', $prefix = 'JUDownloadModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	
	public function savepriority()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		
		$app      = JFactory::getApplication();
		$pks      = $app->input->post->get('cid', null, 'array');
		$priority = $app->input->post->get('priority', null, 'array');

		
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($priority);

		
		$model = $this->getModel();

		
		$return = $model->savepriority($pks, $priority);

		if ($return === false)
		{
			
			$message = JText::sprintf('COM_JUDOWNLOAD_SAVE_FIELD_PRIORITY_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');

			return false;
		}
		else
		{
			
			$this->setMessage(JText::_('COM_JUDOWNLOAD_SAVE_FIELD_PRIORITY_SUCCESSFULLY'));
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

			return true;
		}
	}

	
	public function repriority()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		
		$app = JFactory::getApplication();
		$ids = $app->input->post->get('cid', null, 'array');
		$inc = ($this->getTask() == 'priorityup') ? -1 : +1;

		$model  = $this->getModel();
		$return = $model->repriority($ids, $inc);
		if ($return === false)
		{
			
			$message = JText::sprintf('COM_JUDOWNLOAD_REORDER_PRIORITY_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');

			return false;
		}
		else
		{
			
			$message = JText::_('COM_JUDOWNLOAD_REORDER_PRIORITY_SUCCESSFULLY');
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);

			return true;
		}
	}

	
	public function saveblvorder()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		
		$app         = JFactory::getApplication();
		$pks         = $app->input->post->get('cid', null, 'array');
		$bvlordering = $app->input->post->get('backend_list_view_ordering', null, 'array');

		
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($bvlordering);

		
		$model = $this->getModel();

		
		$return = $model->saveblvorder($pks, $bvlordering);

		if ($return === false)
		{
			
			$message = JText::sprintf('COM_JUDOWNLOAD_SAVE_BLV_ORDERING_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');

			return false;
		}
		else
		{
			
			$this->setMessage(JText::_('COM_JUDOWNLOAD_SAVE_BLV_ORDERING_SUCCESSFULLY'));
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

			return true;
		}
	}

	
	public function reblvorder()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		
		$app = JFactory::getApplication();
		$ids = $app->input->post->get('cid', null, 'array');
		$inc = ($this->getTask() == 'blvup') ? -1 : +1;

		$model  = $this->getModel();
		$return = $model->reblvorder($ids, $inc);
		if ($return === false)
		{
			
			$message = JText::sprintf('COM_JUDOWNLOAD_REORDER_BLV_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');

			return false;
		}
		else
		{
			
			$message = JText::_('COM_JUDOWNLOAD_REORDER_BLV_SUCCESSFULLY');
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);

			return true;
		}
	}

	public function changeValue()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		$app    = JFactory::getApplication();
		$id     = $app->input->getInt('id', 0);
		$column = $app->input->get('column', '');
		$value  = $app->input->getInt('value', 0);
		$value  = $value == 0 ? 0 : 1;
		if (!$id || !$column)
		{
			die();
		}

		$model  = $this->getModel();
		$result = $model->changeValue($id, $column, $value);
		JUDownloadHelper::obCleanData();
		if ($result)
		{
			echo $result;
		}

		exit;
	}

	public function changePriorityDirection()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		$app   = JFactory::getApplication();
		$id    = $app->input->getInt('id', 0);
		$value = $app->input->get('value', 'asc');
		$value = $value == 'asc' ? 'asc' : 'desc';
		if (!$id)
		{
			die();
		}
		$model  = $this->getModel();
		$result = $model->changePriorityDirection($id, $value);
		JUDownloadHelper::obCleanData();
		if ($result)
		{
			echo $result;
		}

		exit;
	}

	public function changeBLVorder()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		$app   = JFactory::getApplication();
		$id    = $app->input->getInt('id', 0);
		$value = $app->input->getInt('value', 0);
		$data  = array(0, 1, 2);
		$value = JArrayHelper::getValue($data, $value, 0, 'int');
		if (!$id)
		{
			die();
		}
		$model  = $this->getModel();
		$result = $model->changeBLVorder($id, $value);
		JUDownloadHelper::obCleanData();
		if ($result)
		{
			echo $result;
		}

		exit;
	}
}
