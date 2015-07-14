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

class JUDownloadControllerCSVProcess extends JControllerForm
{

	public function import()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();

		
		$assignedColumns = $app->getUserState('csv_assigned_columns');


		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__judownload_fields')
			->where('field_name = \'title\' OR field_name=\'id\'');
		$db->setQuery($query);
		$requiredFields = $db->loadColumn();

		
		if (!in_array($requiredFields[0], $assignedColumns) && !in_array($requiredFields[1], $assignedColumns))
		{
			$app->enqueueMessage(JText::_("COM_JUDOWNLOAD_ID_OR_TITLE_FIELD_IS_REQUIRED"), 'error');
			$this->setRedirect('index.php?option=com_judownload&view=csvprocess', JText::_('COM_JUDOWNLOAD_IMPORT_CSV_FAILED'), 'error');

			return false;
		}

		
		$this->setRedirect('index.php?option=com_judownload&view=csvprocess&layout=processing');

		return true;
	}

	public function importProcessing()
	{
		$model = $this->getModel();

		$result = $model->importCSV();
		JUDownloadHelper::obCleanData();
		echo json_encode($result);
		exit;
	}

	public function export()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel();
		$model->export();

		return true;
	}

	public function getModel($name = 'CSVProcess', $prefix = 'JUDownloadModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	
	public function load()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel();
		
		$mappedColumns = $model->loadCSVColumns();

		if ($mappedColumns === false)
		{
			return $this->back();
		}

		
		
		$view = $this->getView('csvprocess', 'html');
		$view->assignRef('mapped_columns', $mappedColumns);
		$view->setLayout('fields_mapping');
		$view->setModel($model, true);
		$view->display();

		return $this;
	}

	
	public function config()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel();
		$model->getAssignedColumns();

		$view = $this->getView('csvprocess', 'html');
		$view->setLayout('config');
		$view->setModel($model, true);
		$view->display();

		return true;
	}

	
	public function review()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$model  = $this->getModel();
		$review = $model->getDefaultConfigs();
		if ($review === false)
		{
			return $this->back();
		}
		$view = $this->getView('csvprocess', 'html');
		$view->assignRef('review', $review);
		$view->setModel($model, true);
		$view->setLayout('review');
		$view->display();

		return true;
	}

	public function back()
	{
		$app = JFactory::getApplication();

		if (trim($app->getUserState("csv_import_dir")) && JFolder::exists($app->getUserState("csv_import_dir")))
		{
			JFolder::delete($app->getUserState("csv_import_dir"));
		}
		$this->setRedirect(JRoute::_('index.php?option=com_judownload&view=csvprocess', false));

		return true;
	}

	public function getGroupIdsByCats()
	{
		$db  = JFactory::getDbo();
		$app = JFactory::getApplication();

		$cats = $app->input->get('cats', array(), 'array');

		$groupIds = array();
		foreach ($cats AS $cat)
		{
			$db->setQuery("SELECT fieldgroup_id FROM #__judownload_categories WHERE id = " . $cat);
			if ($db->loadResult())
			{
				$groupIds[] = $db->loadResult();
			}
		}
		$groupIds = array_unique($groupIds);

		JUDownloadHelper::obCleanData();
		echo json_encode($groupIds);
		exit;
	}
}