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


class JUDownloadModelCriteriaGroup extends JModelAdmin
{
	
	public function getTable($type = 'CriteriaGroup', $prefix = 'JUDownloadTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	
	public function getForm($data = array(), $loadData = true)
	{
		
		$form = $this->loadForm('com_judownload.criteriagroup', 'criteriagroup', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	
	public function getScript()
	{
		return 'administrator/components/com_judownload/models/forms/criteriagroup.js';
	}

	
	protected function loadFormData()
	{
		
		$data = JFactory::getApplication()->getUserState('com_judownload.edit.criteriagroup.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}

		if (JUDownloadHelper::isJoomla3x())
		{
			$this->preprocessData('com_judownload.criteriagroup', $data);
		}

		return $data;
	}

	
	public function save($data)
	{
		
		$dispatcher = JDispatcher::getInstance();
		$table      = $this->getTable();
		$key        = $table->getKeyName();
		$pk         = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew      = true;

		
		JPluginHelper::importPlugin('content');

		
		try
		{
			
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}

			
			if (!$table->bind($data))
			{
				$this->setError($table->getError());

				return false;
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

			
			if (!isset($data['assigntocats']))
			{
				$data['assigntocats'] = array();
			}

			$db    = JFactory::getDbo();
			$query = "SELECT id FROM #__judownload_categories WHERE criteriagroup_id =" . $table->id;
			$db->setQuery($query);
			$assignedCats   = $db->loadColumn();
			$unassignedCats = array_diff($assignedCats, $data['assigntocats']);
			
			if (!empty($unassignedCats))
			{
				$query = "UPDATE #__judownload_categories SET selected_criteriagroup = 0, criteriagroup_id = 0 WHERE id IN (" . implode(',', $unassignedCats) . ")";
				$db->setQuery($query);
				$db->execute();

				foreach ($unassignedCats AS $unassignedCat)
				{
					
					
					$query = "DELETE FROM #__judownload_criterias_values WHERE rating_id IN" .
						"\n (SELECT r.id FROM" .
						"\n #__judownload_rating AS r" .
						"\n JOIN #__judownload_documents AS d" .
						"\n ON d.id = r.doc_id " .
						"\n JOIN #__judownload_documents_xref AS dxref" .
						"\n ON (" .
						"\n d.id = dxref.doc_id" .
						"\n AND dxref.main = 1" .
						"\n )" .
						"\n WHERE dxref.cat_id = " . $unassignedCat . ")";
					$db->setQuery($query);
					$db->execute();

					JUDownloadHelper::changeInheritedCriteriaGroupId($unassignedCat, 0);
				}
			}

			$catsToAddNewCriteriaGroup = array_diff($data['assigntocats'], $assignedCats);

			if ($catsToAddNewCriteriaGroup)
			{
				$query = "UPDATE #__judownload_categories SET selected_criteriagroup = $table->id, criteriagroup_id = $table->id  WHERE id IN (" . implode(',', $catsToAddNewCriteriaGroup) . ")";
				$db->setQuery($query);
				$db->execute();
				foreach ($catsToAddNewCriteriaGroup AS $catToAddNewCriteriaGroup)
				{
					JUDownloadHelper::changeInheritedCriteriaGroupId($catToAddNewCriteriaGroup, $table->id);
				}
			}

			
			$this->cleanCache();

			
			$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
		}
		$this->setState($this->getName() . '.new', $isNew);

		return true;

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

			if (!$table->created_by)
			{
				$table->created_by = $user->id;
			}
		}
		else
		{
			$table->modified_by = $user->id;
			$table->modified    = $date->toSql();
		}
	}
}
