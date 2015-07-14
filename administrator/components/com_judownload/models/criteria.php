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


class JUDownloadModelCriteria extends JModelAdmin
{

	
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

			if (!$table->created_by)
			{
				$table->created_by = $user->id;
			}

			
			$db = JFactory::getDbo();
			$db->setQuery('SELECT MAX(ordering) FROM #__judownload_criterias WHERE group_id = ' . $table->group_id);
			$max             = $db->loadResult();
			$table->ordering = $max + 1;
		}
		else
		{
			$table->modified_by = $user->id;
			$table->modified    = $date->toSql();
		}
	}

	
	public function getTable($type = 'Criteria', $prefix = 'JUDownloadTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	
	public function getForm($data = array(), $loadData = true)
	{
		
		$form = $this->loadForm('com_judownload.criteria', 'criteria', array('control' => 'jform', 'load_data' => $loadData));
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
			
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');
			
			
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	
	public function getScript()
	{
		return 'administrator/components/com_judownload/models/forms/criteria.js';
	}

	
	protected function loadFormData()
	{
		
		$data = JFactory::getApplication()->getUserState('com_judownload.edit.criteria.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}

		if (JUDownloadHelper::isJoomla3x())
		{
			$this->preprocessData('com_judownload.criteria', $data);
		}

		return $data;
	}

	
	protected function getReorderConditions($table)
	{
		$condition   = array();
		$condition[] = 'group_id = ' . (int) $table->group_id;

		return $condition;
	}

	
	public function required(&$pks, $value = 1)
	{
		
		$dispatcher = JDispatcher::getInstance();
		$user       = JFactory::getUser();
		$table      = $this->getTable();
		$pks        = (array) $pks;

		
		JPluginHelper::importPlugin('content');

		
		foreach ($pks AS $i => $pk)
		{
			$table->reset();

			if ($table->load($pk))
			{
				if (!$user->authorise('core.edit', $this->option))
				{
					
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));

					return false;
				}
			}
		}

		
		if (!$table->required($pks, $value, $user->get('id')))
		{
			$this->setError($table->getError());

			return false;
		}

		$context = $this->option . '.' . $this->name;

		
		$result = $dispatcher->trigger($this->event_after_save, array($context, $pks, $value));

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		
		$this->cleanCache();

		return true;
	}

	
	public function getComponents()
	{
		$db   = $this->getDbo();
		$user = JFactory::getUser();
		if ($user->id <= 0)
		{
			return null;
		}

		$query = $db->getQuery(true);
		$query->select('cm.component, cm.section');

		$query->from('#__jucomment_comments AS cm');

		
		$query->where('cm.user_id = ' . $user->id);
		$query->where('cm.level > 0');
		$query->where('cm.approved = 1');
		$query->group('cm.component, cm.section');
		$query->order('cm.component, cm.section');
		$db->setQuery($query);

		$items   = $db->loadObjectList();
		$options = array();
		foreach ($items as $item)
		{
			if ($item->section != '')
			{
				$options[$item->component . '.' . $item->section] = '|-- ' . $item->section;
			}
			else
			{
				$JUCMApplication           = JUComment::loadApplication($item->component);
				$options[$item->component] = $JUCMApplication->getComponentName();
			}
		}

		array_unshift($options, array('' => JText::_('Select component')));

		return $options;
	}
}
