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


class JUDownloadControllerCriterias extends JControllerAdmin
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_CRITERIAS';

	
	public function __construct($config = array())
	{
		parent::__construct($config);

		
		$this->registerTask('unrequired', 'required');
	}

	
	public function getModel($name = 'Criteria', $prefix = 'JUDownloadModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	
	public function required()
	{
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		$app   = JFactory::getApplication();
		$cid   = $app->input->get('cid', array(), 'array');
		$data  = array('required' => 1, 'unrequired' => 0);
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

			
			if (!$model->required($cid, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				if ($value == 1)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_REQUIRED';
				}
				elseif ($value == 0)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNREQUIRED';
				}
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}

		$extension    = $app->input->get('extension');
		$extensionURL = ($extension) ? '&extension=' . $extension : '';
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false));
	}

	public function delete()
	{
		$app = JFactory::getApplication();
		$cid = $app->input->get('cid', array(), 'array');
		
		if (!empty($cid))
		{
			$db    = JFactory::getDbo();
			$query = "SELECT COUNT(*) FROM #__judownload_criterias_values WHERE criteria_id IN(" . implode(',', $cid) . ")";
			$db->setQuery($query);
			$result = $db->loadResult();
			if ($result)
			{
				JError::raiseNotice(500, "Please Rebuild rating !");
			}
		}

		parent::delete();
	}
}
