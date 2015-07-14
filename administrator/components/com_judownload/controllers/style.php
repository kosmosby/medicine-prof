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

class JUDownloadControllerStyle extends JControllerForm
{

	public function checkChangeHomeStyle()
	{
		$app    = JFactory::getApplication();
		$jInput = $app->input;

		$model = $this->getModel();

		$data                = array();
		$data['id']          = $jInput->post->getInt('id');
		$data['template_id'] = $jInput->post->getInt('template_id');
		$data['home']        = $jInput->post->getInt('home');

		$result = $model->checkChangeHomeStyle($data);

		JUDownloadHelper::obCleanData();
		$result = json_encode($result);
		echo $result;

		exit;
	}

	public function changeTemplateId()
	{
		$app        = JFactory::getApplication();
		$jInput     = $app->input;
		$templateId = $jInput->post->getInt("value");

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('parent_id');
		$query->from('#__judownload_templates');
		$query->where('id = ' . $templateId);
		$db->setQuery($query);
		$templateParentId = $db->loadResult();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->select('title AS text');
		$query->select('id AS value');
		$query->from('#__judownload_template_styles');
		$query->where('template_id =' . $templateParentId);
		$query->order('lft ASC');
		$db->setQuery($query);
		$styleObjectList = $db->loadObjectList();

		$html = "";
		$html .= "<option value=\"\">" . JText::_('COM_JUDOWNLOAD_SELECT_PARENT_TEMPLATE') . "</option>";
		if (!empty($styleObjectList))
		{
			foreach ($styleObjectList AS $styleObject)
			{
				$html .= "<option value=\"" . $styleObject->value . "\">" . $styleObject->text . "</option>";
			}
		}

		JUDownloadHelper::obCleanData();
		echo $html;
		exit;
	}

	
	protected function allowAdd($data = array())
	{
		$user = JFactory::getUser();

		return $user->authorise('core.create', 'com_judownload');
	}

	
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user     = JFactory::getUser();
		$userId   = $user->get('id');
		$asset    = 'com_judownload';

		
		if ($user->authorise('core.edit', $asset))
		{
			return true;
		}

		
		
		if ($user->authorise('core.edit.own', $asset))
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
		
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		
		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();
		$model = $this->getModel();
		$table = $model->getTable();
		$data  = $app->input->post->get('jform', array(), 'array');

		$checkin = property_exists($table, 'checked_out');
		$context = "$this->option.edit.$this->context";
		$task    = $this->getTask();

		
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		$recordId = $app->input->getInt($urlVar, 0);

		if (!$this->checkEditId($context, $recordId))
		{
			
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		
		$data[$key] = $recordId;

		
		if ($task == 'save2copy')
		{
			
			if ($checkin && $model->checkin($data[$key]) === false)
			{
				
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);

				return false;
			}

			
			$data[$key] = 0;
			$task       = 'apply';
		}

		
		if (!$this->allowSave($data, $key))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		
		
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		
		if (!$model->save($data))
		{
			
			$app->setUserState($context . '.data', $data);

			
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		
		if ($checkin && $model->checkin($data[$key]) === false)
		{
			
			$app->setUserState($context . '.data', $data);

			
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		$this->setMessage(
			JText::_(
				($lang->hasKey($this->text_prefix . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS')
					? $this->text_prefix
					: 'JLIB_APPLICATION') . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
			)
		);

		
		switch ($task)
		{
			case 'apply':
				
				$recordId = $model->getState($this->context . '.id');
				$this->holdEditId($context, $recordId);
				$app->setUserState($context . '.data', null);
				$model->checkout($recordId);

				
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);
				break;

			case 'save2new':
				
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend(null, $urlVar), false
					)
				);
				break;

			default:
				
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_list
						. $this->getRedirectToListAppend(), false
					)
				);
				break;
		}

		
		$this->postSaveHook($model, $data);

		return true;
	}

}