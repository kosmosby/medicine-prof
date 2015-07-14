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


jimport('joomla.application.component.modeladmin');


class JUDownloadModelComment extends JModelAdmin
{
	
	public function publish(& $pks, $value = 1)
	{
		$table = $this->getTable('Comment', 'JUDownloadTable');
		
		$new_pks = array();
		while (!empty($pks))
		{
			
			$pk        = array_shift($pks);
			$new_pks[] = $pk;
			$table->reset();

			
			if (!$table->load($pk))
			{
				if ($error = $table->getError())
				{
					
					$this->setError($error);
				}

				return false;
			}

			
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('id');
			$query->from('#__judownload_comments');
			$query->where('lft > ' . (int) $table->lft);
			$query->where('rgt < ' . (int) $table->rgt);
			$db->setQuery($query);
			$childIds = $db->loadColumn();

			
			foreach ($childIds AS $childId)
			{
				if (!in_array($childId, $pks))
				{
					array_push($pks, $childId);
				}
			}
			$commentId = $pk;
			if (!parent::publish($pk, $value))
			{
				return false;
			}

			
			JUDownloadFrontHelperMail::sendEmailByEvent('comment.editstate', $commentId);

			
			$logData = array(
				'user_id'   => $table->user_id,
				'event'     => 'comment.editstate',
				'item_id'   => $commentId,
				'doc_id'    => $table->doc_id,
				'value'     => $value,
				'reference' => ''
			);

			JUDownloadFrontHelperLog::addLog($logData);
		}

		$pks = $new_pks;

		return true;
	}

	
	public function getTable($type = 'comment', $prefix = 'JUDownloadTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	
	public function getForm($data = array(), $loadData = true)
	{
		
		$form = $this->loadForm('com_judownload.comment', 'comment', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		if ($data)
		{
			$data = (object) $data;
		}
		else
		{
			$data = $this->getItem();
		}

		
		if (!$this->canEditState($data))
		{
			
			$form->setFieldAttribute('published', 'disabled', 'true');
			
			
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		if ($data->id)
		{
			
			
			$form->setFieldAttribute('approved', 'filter', 'unset');
		}

		return $form;
	}

	
	protected function loadFormData()
	{
		
		$data = JFactory::getApplication()->getUserState('com_judownload.edit.comment.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}

		if (JUDownloadHelper::isJoomla3x())
		{
			$this->preprocessData('com_judownload.comment', $data);
		}

		return $data;
	}

	
	protected function prepareTable($table)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
		if (empty($table->id))
		{
			if (!$table->created)
			{
				$table->created = $date->toSql();
			}

			if (!$table->guest_name || !$table->guest_email)
			{
				$table->user_id = $user->id;
			}

			if (!$table->ip_address)
			{
				$table->ip_address = JUDownloadFrontHelper::getIpAddress();
			}
		}
		else
		{
			$table->modified_by = $user->id;
			$table->modified    = $date->toSql();
		}
	}

	
	public function save($data)
	{
		
		$dispatcher = JDispatcher::getInstance();
		$table      = $this->getTable();
		$pk         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew      = true;
		$app        = JFactory::getApplication();

		
		JPluginHelper::importPlugin('content');

		
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		
		if ($table->parent_id != $data['parent_id'] || $data['id'] == 0)
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}

		
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		
		if (isset($data['rules']))
		{
			$rules = new JAccessRules($data['rules']);
			$table->setRules($rules);
		}

		
		$this->prepareTable($table);

		
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		
		$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));
		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		
		$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));

		
		

		$this->setState($this->getName() . '.id', $table->id);

		
		$this->cleanCache();

		
		if (!$isNew && $app->isSite())
		{
			$user    = JFactory::getUser();
			$logData = array(
				'user_id'   => $user->id,
				'event'     => 'comment.edit',
				'item_id'   => $table->id,
				'doc_id'    => $table->doc_id,
				'value'     => 0,
				'reference' => '',
			);

			JUDownloadFrontHelperLog::addLog($logData);
		}

		return $table->id;
	}

	
	public function saveorder($idArray = null, $lft_array = null)
	{
		
		$table = $this->getTable();

		if (!$table->saveorder($idArray, $lft_array))
		{
			$this->setError($table->getError());

			return false;
		}

		
		$this->cleanCache();

		return true;
	}

	
	protected function canDelete($record)
	{

		$rootComment = JUDownloadFrontHelperComment::getRootComment();
		if (isset($record->id) && $record->id == $rootComment->id)
		{
			return false;
		}

		$app = JFactory::getApplication();
		
		if ($app->isSite())
		{
			return JUDownloadFrontHelperPermission::canDeleteComment($record->id);
		}

		return parent::canDelete($record);
	}

	
	protected function canEditState($record)
	{
		$rootComment = JUDownloadFrontHelperComment::getRootComment();
		if (isset($record->id) && $record->id == $rootComment->id)
		{
			return false;
		}

		$app = JFactory::getApplication();
		
		if ($app->isSite())
		{
			$modCanEditState = JUDownloadFrontHelperModerator::checkModeratorCanDoWithComment($record->id, 'comment_edit_state');
			if ($modCanEditState)
			{
				return true;
			}

			return false;
		}

		return parent::canEditState($record);
	}

}
