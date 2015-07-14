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


class JUDownloadModelField extends JModelAdmin
{
	
	protected function allowEdit($data = array(), $key = 'id')
	{
		
		$user = JFactory::getUser();

		return JFactory::getUser()->authorise('core.edit', 'com_judownload.field.' . ((int) isset($data[$key]) ? $data[$key] : 0))
		|| (JFactory::getUser()->authorise('core.edit.own', 'com_judownload.field.' . ((int) isset($data[$key]))) && $data['created_by'] == $user->id);
	}

	
	protected function canEditState($record)
	{
		$user = JFactory::getUser();
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_judownload.field.' . $record->id);
		}
		elseif (!empty($record->group_id))
		{
			return $user->authorise('core.edit.state', 'com_judownload.fieldgroup.' . (int) $record->group_id);
		}
		else
		{
			return parent::canEditState($record);
		}
	}

	
	public function getTable($type = 'Field', $prefix = 'JUDownloadTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	
	public function getForm($data = array(), $loadData = true)
	{
		
		if ($data)
		{
			$data = (object) $data;
		}
		else
		{
			$data = $this->getItem();
		}

		
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$field_xml_path = JPath::find(JForm::addFormPath(), 'field.xml');
		$field_xml      = JFactory::getXML($field_xml_path, true);

		
		if ($data->plugin_id)
		{
			$db    = JFactory::getDbo();
			$query = 'SELECT folder, type' .
				' FROM #__judownload_plugins' .
				' WHERE (id =' . $data->plugin_id . ')';
			$db->setQuery($query);
			$pluginObj = $db->loadObject();

			if ($pluginObj && $pluginObj->folder)
			{
				$folder   = strtolower(str_replace(' ', '', $pluginObj->folder));
				$xml_file = JPATH_SITE . "/components/com_judownload/fields/" . $folder . "/" . $folder . '.xml';

				if (JFile::exists($xml_file))
				{
					
					$field_plugin_xml = JFactory::getXML($xml_file);
					if ($field_plugin_xml->config)
					{
						
						foreach ($field_plugin_xml->config->children() AS $child)
						{
							$field_params_xpath = $field_xml->xpath('//fieldset[@name="params"]');
							JUDownloadHelper::appendXML($field_params_xpath[0], $child);
						}

						
						if ($field_plugin_xml->languages->count())
						{
							foreach ($field_plugin_xml->languages->children() AS $language)
							{
								$languageFile = (string) $language;
								
								$first_pos       = strpos($languageFile, '.');
								$last_pos        = strrpos($languageFile, '.');
								$languageExtName = substr($languageFile, $first_pos + 1, $last_pos - $first_pos - 1);

								
								$client = JApplicationHelper::getClientInfo((string) $language->attributes()->client, true);
								$path   = isset($client->path) ? $client->path : JPATH_BASE;

								JUDownloadFrontHelperLanguage::loadLanguageFile($languageExtName, $path);
							}
						}
					}
				}
			}
		}

		
		$form = $this->loadForm('com_judownload.field', $field_xml->asXML(), array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		$ignored_options = explode(",", $data->ignored_options);
		foreach ($ignored_options AS $ignored_option)
		{
			$form->setFieldAttribute($ignored_option, 'disabled', 'true');
			$form->setFieldAttribute($ignored_option, 'filter', 'unset');
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
		return 'administrator/components/com_judownload/models/forms/field.js';
	}

	
	protected function loadFormData()
	{
		
		$data = JFactory::getApplication()->getUserState('com_judownload.edit.field.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}

		if (JUDownloadHelper::isJoomla3x())
		{
			$this->preprocessData('com_judownload.field', $data);
		}

		return $data;
	}

	
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		$item->xml = null;

		if ($item->id)
		{
			
			$registry = new JRegistry;
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();

			$db    = JFactory::getDbo();
			$query = 'SELECT folder' .
				' FROM #__judownload_plugins' .
				' WHERE (id =' . $item->plugin_id . ')';
			$db->setQuery($query);
			$folder = $db->loadResult();
			$folder = strtolower(str_replace(' ', '', $folder));
			if ($folder)
			{
				
				$xml_file = JPATH_SITE . "/components/com_judownload/fields/" . $folder . "/" . $folder . '.xml';
				if (file_exists($xml_file))
				{
					$item->xml = JFactory::getXML($xml_file);
				}
			}
		}

		return $item;
	}

	
	protected function getReorderConditions($table)
	{
		$condition   = array();
		$condition[] = 'group_id = ' . (int) $table->group_id;

		return $condition;
	}

	
	protected function prepareTable($table)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if ($table->published == 1 && intval($table->publish_up) == 0)
		{
			$table->publish_up = JFactory::getDate()->toSql();
		}

		if (empty($table->id))
		{
			if (!$table->created_by)
			{
				$table->created_by = $user->id;
			}

			if (!$table->created)
			{
				$table->created = $date->toSql();
			}

			
			$db = JFactory::getDbo();
			$db->setQuery('SELECT MAX(ordering) FROM #__judownload_fields WHERE group_id =' . $table->group_id);
			$max             = $db->loadResult();
			$table->ordering = $max + 1;

			
			$db = JFactory::getDbo();
			$db->setQuery('SELECT MAX(priority) FROM #__judownload_fields');
			$max             = $db->loadResult();
			$table->priority = $max + 1;

			$db = JFactory::getDbo();
			$db->setQuery('SELECT MAX(backend_list_view_ordering) FROM #__judownload_fields');
			$max                               = $db->loadResult();
			$table->backend_list_view_ordering = $max + 1;
		}
		else
		{
			$table->modified_by = $user->id;
			$table->modified    = $date->toSql();
		}
	}

	
	protected function canDelete($record)
	{
		if ($record->group_id == 1)
		{
			$db    = JFactory::getDbo();
			$query = "SELECT `core` FROM #__judownload_plugins WHERE id = " . (int) $record->plugin_id;
			$db->setQuery($query);
			$result = $db->loadObject();
			if ($result->core == 1)
			{
				JError::raiseWarning(500, JText::sprintf('COM_JUDOWNLOAD_CAN_NOT_DELETE_FIELD_X_BECAUSE_IT_IS_DEFAULT_FIELD', $record->caption));

				return false;
			}
		}

		$user = JFactory::getUser();

		return $user->authorise('core.delete', $this->option . '.field.' . (int) $record->id);
	}

	
	public function save($data)
	{
		if(!$data['id'])
		{
			return false;
		}
		
		$app            = JFactory::getApplication();
		$jform          = $app->input->post->get('jform', array(), 'array');
		$data['params'] = isset($jform['params']) ? $jform['params'] : null;

		$table = $this->getTable();
		
		if ($table->load($data['id']) && ($table->plugin_id == $data['plugin_id']))
		{
			$fieldClass     = JUDownloadFrontHelperField::getField($data['id']);
			$data           = $fieldClass->onSave($data);
			$data['params'] = json_encode($data['params']);
		}
		
		else
		{
			$data['params'] = "";
		}

		if (parent::save($data))
		{
			$table = $this->getTable();
			$table->reorder("group_id = " . (int) $data['group_id']);
			$table->repriority();
			$table->reblvorder();

			return true;
		}

		return false;
	}

	public function changeValue(&$pk, $column, $value)
	{
		$table = $this->getTable();
		$db    = $this->getDbo();
		if ($table->load($pk))
		{
			if (!$this->checkout())
			{
				return false;
			}

			switch ($column)
			{
				case 'published':
					$canEdit = $this->canEditState($table);
					break;
				default:
					$canEdit = $this->allowEdit(array('id' => $pk, 'created_by' => $table->created_by));
					break;
			}

			if (!$canEdit)
			{
				
				return false;
			}
			$query = "UPDATE #__judownload_fields SET " . $db->quoteName($column) . " = $value WHERE id = " . $pk;
			$db->setQuery($query);

			if ($db->execute())
			{
				$table->checkIn();
				JHtml::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_judownload/helpers/html");

				return JHtml::_('judownloadadministrator.changAjaxValue', $pk, $column, $value, true);
			}
		}

		return false;
	}

	public function changeBLVorder(&$pk, $value)
	{
		$table = $this->getTable();
		$db    = $this->getDbo();
		if ($table->load($pk))
		{
			if (!$this->checkout())
			{
				return false;
			}

			if (!$this->allowEdit(array('id' => $pk, 'created_by' => $table->created_by)))
			{
				return false;
			}

			$query = "UPDATE #__judownload_fields SET backend_list_view = $value WHERE id = " . $pk;
			$db->setQuery($query);
			if ($db->execute())
			{
				$table->checkIn();
				$path = JPATH_ADMINISTRATOR . "/components/com_judownload/helpers/html";
				JHtml::addIncludePath($path);

				return JHtml::_('judownloadadministrator.changeAjaxBLVorder', $pk, $value, true);
			}
		}

		return false;
	}

	public function changePriorityDirection(&$pk, $value)
	{
		$table = $this->getTable();
		$db    = $this->getDbo();
		if ($table->load($pk))
		{
			if (!$this->checkout())
			{
				return false;
			}

			if (!$this->allowEdit(array('id' => $pk, 'created_by' => $table->created_by)))
			{
				return false;
			}

			$query = "UPDATE #__judownload_fields SET priority_direction = " . $db->quote($value) . " WHERE id = " . $pk;
			$db->setQuery($query);
			if ($db->execute())
			{
				$table->checkIn();
				$path = JPATH_ADMINISTRATOR . "/components/com_judownload/helpers/html";
				JHtml::addIncludePath($path);

				return JHtml::_('judownloadadministrator.priorityDirection', $pk, $value, true);
			}
		}

		return false;
	}

	
	protected function getRePriorityConditions($table)
	{
		$condition = array();

		return $condition;
	}

	
	public function rePriority($pks, $delta = 0)
	{
		
		$table  = $this->getTable();
		$pks    = (array) $pks;
		$result = true;

		$allowed = true;

		foreach ($pks AS $i => $pk)
		{
			$table->reset();

			if ($table->load($pk) && $this->checkout($pk))
			{
				
				if (!$this->canEditState($table))
				{
					
					unset($pks[$i]);
					$this->checkin($pk);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
					$allowed = false;
					continue;
				}

				$where = array();
				$where = $this->getRepriorityConditions($table);

				if (!$table->movePriority($delta, $where))
				{
					$this->setError($table->getError());
					unset($pks[$i]);
					$result = false;
				}

				$this->checkin($pk);
			}
			else
			{
				$this->setError($table->getError());
				unset($pks[$i]);
				$result = false;
			}
		}

		if ($allowed === false && empty($pks))
		{
			$result = null;
		}

		
		if ($result == true)
		{
			$this->cleanCache();
		}

		return $result;
	}

	
	public function savePriority($pks = null, $priority = null)
	{
		
		$table      = $this->getTable();
		$conditions = array();

		if (empty($pks))
		{
			return JError::raiseWarning(500, JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
		}

		
		foreach ($pks AS $i => $pk)
		{
			$table->load((int) $pk);

			
			if (!$this->canEditState($table))
			{
				
				unset($pks[$i]);
				JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			}
			elseif ($table->priority != $priority[$i])
			{
				$table->priority = $priority[$i];

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}

				
				$condition = $this->getRepriorityConditions($table);
				$found     = false;

				foreach ($conditions AS $cond)
				{
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				}

				if (!$found)
				{
					$key          = $table->getKeyName();
					$conditions[] = array($table->$key, $condition);
				}
			}
		}

		
		foreach ($conditions AS $cond)
		{
			$table->load($cond[0]);
			$table->rePriority($cond[1]);
		}

		
		$this->cleanCache();

		return true;
	}

	
	protected function getReBLVOrderConditions($table)
	{
		$condition = array();

		return $condition;
	}

	
	public function reBLVOrder($pks, $delta = 0)
	{
		
		$table  = $this->getTable();
		$pks    = (array) $pks;
		$result = true;

		$allowed = true;

		foreach ($pks AS $i => $pk)
		{
			$table->reset();

			if ($table->load($pk) && $this->checkout($pk))
			{
				
				if (!$this->canEditState($table))
				{
					
					unset($pks[$i]);
					$this->checkin($pk);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
					$allowed = false;
					continue;
				}

				$where = $this->getReBLVorderConditions($table);

				if (!$table->moveblvordering($delta, $where))
				{
					$this->setError($table->getError());
					unset($pks[$i]);
					$result = false;
				}

				$this->checkin($pk);
			}
			else
			{
				$this->setError($table->getError());
				unset($pks[$i]);
				$result = false;
			}
		}

		if ($allowed === false && empty($pks))
		{
			$result = null;
		}

		
		if ($result == true)
		{
			$this->cleanCache();
		}

		return $result;
	}

	
	public function saveBLVOrder($pks = null, $blvordering = null)
	{
		
		$table      = $this->getTable();
		$conditions = array();

		if (empty($pks))
		{
			return JError::raiseWarning(500, JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
		}

		
		foreach ($pks AS $i => $pk)
		{
			$table->load((int) $pk);

			
			if (!$this->canEditState($table))
			{
				
				unset($pks[$i]);
				JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			}
			elseif ($table->backend_list_view_ordering != $blvordering[$i])
			{
				$table->backend_list_view_ordering = $blvordering[$i];

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}
				
				$condition = $this->getReBLVorderConditions($table);
				$found     = false;

				foreach ($conditions AS $cond)
				{
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				}

				if (!$found)
				{
					$key          = $table->getKeyName();
					$conditions[] = array($table->$key, $condition);
				}
			}
		}

		
		foreach ($conditions AS $cond)
		{
			$table->load($cond[0]);
			$table->reblvorder($cond[1]);
		}

		
		$this->cleanCache();

		return true;
	}

	
	public function publish(&$pks, $value = 1)
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
				if (!$this->canEditState($table))
				{
					
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));

					return false;
				}

				if ($table->ignored_options)
				{
					$ignored_options = explode(",", $table->ignored_options);
					if (in_array("published", $ignored_options))
					{
						$e = new JException(JText::_('COM_JUDOWNLOAD_THIS_FIELD_CAN_NOT_BE_PUBLISHED'));
						$this->setError($e);

						return false;
					}
				}
			}
		}

		
		if (!$table->publish($pks, $value, $user->get('id')))
		{
			$this->setError($table->getError());

			return false;
		}

		$context = $this->option . '.' . $this->name;

		
		$result = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		
		$this->cleanCache();

		return true;
	}
}
