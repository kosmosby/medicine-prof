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


class JUDownloadControllerCategory extends JControllerForm
{
	
	protected $text_prefix = 'COM_JUDOWNLOAD_CATEGORY';

	
	protected function allowAdd($data = array())
	{
		
		$user       = JFactory::getUser();
		$app        = JFactory::getApplication();
		$jInput     = $app->input;
		$categoryId = JArrayHelper::getValue($data, 'parent_id', $jInput->get('parent_id'), 'int');
		$allow      = null;

		if ($categoryId)
		{
			
			$allow = $user->authorise('judl.category.create', 'com_judownload.category.' . $categoryId);
		}

		if ($allow === null)
		{
			
			return parent::allowAdd();
		}
		else
		{
			return $allow;
		}
	}

	
	protected function allowEdit($data = array(), $key = 'id')
	{
		
		$recordId     = (int) isset($data[$key]) ? $data[$key] : 0;
		$user         = JFactory::getUser();
		$userId       = $user->get('id');
		$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();
		if ($recordId && $recordId == $rootCategory->id)
		{
			return false;
		}

		
		if ($user->authorise('judl.category.edit', 'com_judownload.category.' . $recordId))
		{
			return true;
		}

		
		
		if ($user->authorise('judl.category.edit.own', 'com_judownload.category.' . $recordId))
		{
			
			$ownerId = (int) isset($data['created_by']) ? $data['created_by'] : 0;
			if (empty($ownerId) && $recordId)
			{
				
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->created_by;
			}

			
			if ($ownerId == $userId)
			{
				return true;
			}
		}

		
		return parent::allowEdit($data, $key);
	}

	
	public function save($key = null, $urlVar = null)
	{
		$app  = JFactory::getApplication();
		$task = $app->input->get('task');
		$form = $app->input->post->get("jform", array(), 'array');

		$cat_id = $form['parent_id'];
		parent::save($key = null, $urlVar = null);
		if ($task == 'save')
		{
			if (!$cat_id)
			{
				$this->setRedirect("index.php?option=com_judownload&view=listcats");
			}
			else
			{
				$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=" . $cat_id);
			}
		}
	}

	
	public function cancel($key = null)
	{
		parent::cancel($key = null);
		$app           = JFactory::getApplication();
		$rootCat       = JUDownloadFrontHelperCategory::getRootCategory();
		$parent_cat_id = $app->input->getInt('parent_id', $rootCat->id);
		$cat_id        = $app->input->getInt('id', 0);
		if ($cat_id)
		{
			$parent_cat_id = JUDownloadHelper::getCategoryById($cat_id)->parent_id;
		}
		$this->setRedirect("index.php?option=com_judownload&view=listcats&cat_id=" . $parent_cat_id);
	}

	public function getFieldGroup()
	{
		$model = $this->getModel();
		JUDownloadHelper::obCleanData();
		echo $model->getFieldGroup();
		exit;
	}

	public function checkCriteriaGroupChange()
	{
		$app    = JFactory::getApplication();
		$model  = $this->getModel();
		$jInput = $app->input;
		$data   = array();

		$data['id']                     = $jInput->post->getInt('id');
		$data['parent_id']              = $jInput->post->getInt('parent_id');
		$data['selected_criteriagroup'] = $jInput->post->getInt('selected_criteriagroup');

		$result = $model->checkCriteriaGroupChange($data);

		JUDownloadHelper::obCleanData();
		$result = json_encode($result);
		echo $result;
		exit;
	}

	public function checkFieldGroupChange()
	{
		$app    = JFactory::getApplication();
		$model  = $this->getModel();
		$jInput = $app->input;
		$data   = array();

		$data['id']                  = $jInput->post->getInt('id');
		$data['parent_id']           = $jInput->post->getInt('parent_id');
		$data['selected_fieldgroup'] = $jInput->post->getInt('selected_fieldgroup');

		$result = $model->checkFieldGroupChange($data);

		JUDownloadHelper::obCleanData();
		$result = json_encode($result);
		echo $result;
		exit;
	}

	public function checkTemplateChange()
	{
		$app    = JFactory::getApplication();
		$model  = $this->getModel();
		$jInput = $app->input;
		$data   = array();

		$data['id']        = $jInput->post->getInt('id');
		$data['parent_id'] = $jInput->post->getInt('parent_id');
		$data['style_id']  = $jInput->post->getInt('style_id');

		$result = $model->checkTemplateChange($data);

		JUDownloadHelper::obCleanData();
		$result = json_encode($result);
		echo $result;
		exit;
	}

	public function updateInheritField()
	{
		$app    = JFactory::getApplication();
		$model  = $this->getModel();
		$jInput = $app->input;
		$data   = array();

		$data['id']        = $jInput->post->getInt('id');
		$data['parent_id'] = $jInput->post->getInt('parent_id');

		$result = $model->updateInheritField($data);

		JUDownloadHelper::obCleanData();
		$result = json_encode($result);
		echo $result;
		exit;
	}

	public function checkInheritedDataWhenChangeParentCat()
	{
		$app    = JFactory::getApplication();
		$model  = $this->getModel();
		$jInput = $app->input;
		$data   = array();

		$data['id']                     = $jInput->post->getInt('id');
		$data['parent_id']              = $jInput->post->getInt('parent_id');
		$data['selected_fieldgroup']    = $jInput->post->getInt('selected_fieldgroup');
		$data['selected_criteriagroup'] = $jInput->post->getInt('selected_criteriagroup');
		$data['style_id']               = $jInput->post->getInt('style_id');

		$result = $model->checkInheritedDataWhenChangeParentCat($data);

		JUDownloadHelper::obCleanData();
		$result = json_encode($result);
		echo $result;
		exit;
	}

	
	public function edit($key = null, $urlVar = null)
	{
		$app      = JFactory::getApplication();
		$cat_id   = $app->input->getInt('id', 0);
		$category = JUDownloadHelper::getCategoryById($cat_id);
		if ($category->parent_id == 0)
		{
			$this->setRedirect("index.php?option=com_judownload&view=listcats", JText::_('COM_JUDOWNLOAD_YOU_CAN_NOT_EDIT_ROOT_CATEGORY'), "error");
		}
		else
		{
			parent::edit($key = null, $urlVar = null);
		}
	}

	
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		$app    = JFactory::getApplication();
		if ($app->input->getInt('parent_id', 0))
		{
			$append .= '&parent_id=' . $app->input->getInt('parent_id', 0);
		}

		return $append;
	}
}
