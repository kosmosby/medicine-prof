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


class JUDownloadModelCategory extends JModelAdmin
{

	
	protected $pluginsCanEdit = array();

	
	public function getTable($type = 'Category', $prefix = 'JUDownloadTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	
	protected function prepareTable($table)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if ($table->published == 1 && intval($table->publish_up) == 0)
		{
			$table->publish_up = $date->toSql();
		}

		
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

	
	public function getItem($pk = null)
	{
		$pk      = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$storeId = md5(__METHOD__ . "::" . $pk);
		if (!isset($this->cache[$storeId]))
		{
			$item = parent::getItem($pk);

			if ($item && $item->id)
			{
				
				$params = new JRegistry;
				$params->loadString($item->template_params);
				$item->template_params = $params->toArray();

				$registry = new JRegistry;
				$registry->loadString($item->images);
				$item->images = $registry->toArray();

				$item->description = trim($item->fulltext) != '' ? $item->introtext . "<hr id=\"system-readmore\" />" . $item->fulltext : $item->introtext;

				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('cat_id_related');
				$query->from('#__judownload_categories_relations');
				$query->where('cat_id = ' . $item->id);
				$db->setQuery($query);
				$related_cats   = $db->loadColumn();
				$item->rel_cats = '';
				if ($related_cats)
				{
					$item->rel_cats = implode(",", $related_cats);
				}

				$registry = new JRegistry;
				$registry->loadString($item->config_params);
				$item->config_params = $registry->toArray();

				
				$item->config_params_db = $item->config_params;

				$rule = new JRegistry;
				$rule->loadString($item->plugin_params);
				$item->plugin_params = $rule->toArray();

				
				$registry = new JRegistry;
				$registry->loadString($item->metadata);
				$item->metadata = $registry->toArray();
			}

			$this->cache[$storeId] = $item;
		}

		return $this->cache[$storeId];
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
		$category_xml_path = JPath::find(JForm::addFormPath(), 'category.xml');
		$category_xml      = JFactory::getXML($category_xml_path, true);
		if ($data->id)
		{
			$templateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($data->id);
			$templateFolder      = trim($templateStyleObject->folder);
			if ($templateFolder)
			{
				$template_path = JPATH_SITE . "/components/com_judownload/templates/" . $templateFolder . "/" . $templateFolder . '.xml';
				if (JFile::exists($template_path))
				{
					$template_xml = JFactory::getXML($template_path, true);
					if ($template_xml->cat_config)
					{
						foreach ($template_xml->cat_config->children() AS $child)
						{
							$template_params_xpath = $category_xml->xpath('//fieldset[@name="template_params"]');
							JUDownloadHelper::appendXML($template_params_xpath[0], $child);
						}

						
						if ($template_xml->languages->count())
						{
							foreach ($template_xml->languages->children() AS $language)
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

		
		$globalconfig_path = JPath::find(JForm::addFormPath(), 'globalconfig.xml');
		$globalconfig_xml  = JFactory::getXML($globalconfig_path, true);
		if ($globalconfig_xml)
		{
			foreach ($globalconfig_xml->children() AS $child)
			{
				$config_params_xpath = $category_xml->xpath('//fields[@name="config_params"]');
				if (isset($config_params_xpath[0]))
				{
					JUDownloadHelper::appendXML($config_params_xpath[0], $child, true);
				}
			}
		}

		
		$display_params_fields_xpath = $globalconfig_xml->xpath('//fields[@name="display_params"]/fields[@name="cat"]');
		$display_params_xml          = $display_params_fields_xpath[0];
		if ($display_params_xml)
		{
			foreach ($display_params_xml->children() AS $child)
			{
				$display_params_xpath = $category_xml->xpath('//fields[@name="display_params"]');
				JUDownloadHelper::appendXML($display_params_xpath[0], $child, false, true);
			}
		}

		
		$plugin_dir = JPATH_SITE . "/plugins/judownload/";
		$db         = JFactory::getDbo();
		$query      = "SELECT * FROM #__extensions WHERE type = 'plugin' AND folder = 'judownload' AND enabled = 1 ORDER BY ordering ASC";
		$db->setQuery($query);
		$elements = $db->loadObjectList();
		if ($elements)
		{
			foreach ($elements AS $index => $element)
			{
				$folder    = $element->element;
				$file_path = $plugin_dir . $folder . "/$folder.xml";
				
				if (JFile::exists($file_path) && JUDownloadHelper::canEditJUDownloadPluginParams($folder, $index) === true)
				{
					$xml = JFactory::getXML($file_path, true);

					
					if ($xml->cat_config)
					{
						$ruleXml             = new SimpleXMLElement('<fields name="' . $folder . '"></fields>');
						$plugin_params_xpath = $category_xml->xpath('//fields[@name="plugin_params"]');
						JUDownloadHelper::appendXML($plugin_params_xpath[0], $ruleXml);
						$total_fieldsets = 0;
						foreach ($xml->cat_config->children() AS $child)
						{
							$total_fieldsets++;
							$child->addAttribute('plugin_name', $folder);
							$jplugin_xpath = $category_xml->xpath('//fields[@name="' . $folder . '"]');
							JUDownloadHelper::appendXML($jplugin_xpath[0], $child);
						}

						if ($total_fieldsets)
						{
							$pluginLabel                   = $xml->cat_config->attributes()->label ? $xml->cat_config->attributes()->label : $element->name;
							$this->pluginsCanEdit[$folder] = array('label' => $pluginLabel, 'total_fieldsets' => $total_fieldsets);
						}

						
						if (isset($xml->languages))
						{
							JUDownloadFrontHelperLanguage::loadLanguageFile($xml->languages, JPATH_ADMINISTRATOR);
						}
					}
				}
			}
		}

		
		$form = $this->loadForm('com_judownload.category', $category_xml->asXML(), array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		
		if (!$this->canEditState($data))
		{
			
			$form->setFieldAttribute('featured', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');

			
			
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
		}

		return $form;
	}

	
	public function getScript()
	{
		return 'administrator/components/com_judownload/models/forms/category.js';
	}

	
	protected function loadFormData()
	{
		
		$data = JFactory::getApplication()->getUserState('com_judownload.edit.category.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}

		
		$params = JUDownloadHelper::getParams($data->id);
		if (isset($data->config_params) && $data->config_params)
		{
			$params->loadArray($data->config_params);
			$data->config_params = $params->toArray();
		}

		if (JUDownloadHelper::isJoomla3x())
		{
			$this->preprocessData('com_judownload.category', $data);
		}

		return $data;
	}

	
	public function feature(&$pks, $value = 1)
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
			}
		}

		
		if (!$table->feature($pks, $value, $user->get('id')))
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

	
	protected function canDelete($record)
	{
		$user         = JFactory::getUser();
		$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();
		if ($record->id && $record->id == $rootCategory->id)
		{
			return false;
		}

		$canDelete = $user->authorise('judl.category.delete', $this->option . '.category.' . (int) $record->id);

		if (!$canDelete)
		{
			if ($user->id)
			{
				if ($user->id == $record->created_by)
				{
					$canDeleteOwn = $user->authorise('judl.category.delete.own', $this->option . '.category.' . (int) $record->id);
					if ($canDeleteOwn)
					{
						return $canDeleteOwn;
					}
				}
			}
		}

		return $canDelete;
	}

	
	protected function canEditState($record)
	{
		$user         = JFactory::getUser();
		$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();
		if ($record->id && $record->id == $rootCategory->id)
		{
			return false;
		}

		
		if (!empty($record->id))
		{
			return $user->authorise('judl.category.edit.state', 'com_judownload.category.' . (int) $record->id);
		}
		
		elseif (!empty($record->parent_id))
		{
			return $user->authorise('judl.category.edit.state', 'com_judownload.category.' . (int) $record->parent_id);
		}
		
		else
		{
			return parent::canEditState($record);
		}
	}

	public function getPlugins()
	{
		return $this->pluginsCanEdit;
	}

	
	public function removeTemplateParamsOfInheritedStyleCatDoc($categoryId)
	{
		return JUDownloadFrontHelperTemplate::removeTemplateParamsOfInheritedStyleCatDoc($categoryId);
	}

	
	public function checkCriteriaGroupChange($data)
	{
		
		$table = $this->getTable();
		$pk    = $data['id'];

		$result = array('criteriaGroupChanged' => 0, 'criteriaGroupMessage' => '');

		
		if ($pk > 0)
		{
			$table->load($pk);

			$newCategoryObject = JUDownloadHelper::getCategoryByID($data['parent_id']);

			if ($table->fieldgroup_id)
			{
				
				if ($data['selected_criteriagroup'] == -1)
				{
					$newCriteriaGroupId = $newCategoryObject->criteriagroup_id;
				}
				else
				{
					
					$newCriteriaGroupId = $data['selected_criteriagroup'];
				}

				if ($table->criteriagroup_id != $newCriteriaGroupId)
				{
					$result['criteriaGroupChanged'] = 1;

					$criteriaGroupObject    = JUDownloadFrontHelperCriteria::getCriteriaGroupById($table->criteriagroup_id);
					$newCriteriaGroupObject = JUDownloadFrontHelperCriteria::getCriteriaGroupById($newCriteriaGroupId);

					$result['criteriaGroupMessage'] = JText::sprintf('COM_JUDOWNLOAD_CRITERIA_GROUP_IS_CHANGED_FROM_X_TO_X',
						$criteriaGroupObject->name ? $criteriaGroupObject->name : JText::_('COM_JUDOWNLOAD_NONE'),
						$newCriteriaGroupObject->name ? $newCriteriaGroupObject->name : JText::_('COM_JUDOWNLOAD_NONE'));
				}
			}

		}

		return $result;
	}

	
	public function checkFieldGroupChange($data)
	{
		
		$table = $this->getTable();
		$pk    = $data['id'];

		$result = array('fieldGroupChanged' => 0, 'fieldGroupMessage' => '');

		
		if ($pk > 0)
		{
			$table->load($pk);

			$newCategoryObject = JUDownloadHelper::getCategoryByID($data['parent_id']);

			if ($table->fieldgroup_id)
			{
				
				if ($data['selected_fieldgroup'] == -1)
				{
					$newFieldGroupId = $newCategoryObject->fieldgroup_id;
				}
				else
				{
					
					$newFieldGroupId = $data['selected_fieldgroup'];
				}

				if ($table->fieldgroup_id != $newFieldGroupId)
				{
					$result['fieldGroupChanged'] = 1;

					$fieldGroupObject    = JUDownloadFrontHelperField::getFieldGroupById($table->fieldgroup_id);
					$newFieldGroupObject = JUDownloadFrontHelperField::getFieldGroupById($newFieldGroupId);

					$result['fieldGroupMessage'] = JText::sprintf('COM_JUDOWNLOAD_FIELD_GROUP_IS_CHANGED_FROM_X_TO_X',
						$fieldGroupObject->name ? $fieldGroupObject->name : JText::_('COM_JUDOWNLOAD_NONE'),
						$newFieldGroupObject->name ? $newFieldGroupObject->name : JText::_('COM_JUDOWNLOAD_NONE'));
				}
			}
		}

		return $result;
	}

	
	public function checkTemplateChange($data)
	{
		
		$table = $this->getTable();
		$pk    = $data['id'];

		$result = array('templateStyleChanged' => 0, 'templateStyleMessage' => '');

		
		if ($pk > 0)
		{
			$table->load($pk);

			$oldTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($table->id);

			if ($data['style_id'] == -2)
			{
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getDefaultTemplateStyle();
			}
			elseif ($data['style_id'] == -1)
			{
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($data['parent_id']);
			}
			else
			{
				
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleObject($data['style_id']);
			}

			if ($oldTemplateStyleObject->template_id != $newTemplateStyleObject->template_id)
			{
				$result['templateStyleChanged'] = 1;

				$result['templateStyleMessage'] = JText::sprintf('COM_JUDOWNLOAD_STYLE_IS_CHANGED_FROM_X_TO_X',
					$oldTemplateStyleObject->title ? $oldTemplateStyleObject->title : JText::_('COM_JUDOWNLOAD_NONE'),
					$newTemplateStyleObject->title ? $newTemplateStyleObject->title : JText::_('COM_JUDOWNLOAD_NONE'));
			}
		}

		return $result;
	}

	public function updateInheritField($data)
	{
		
		$result = array('message_fieldgroup'    => JText::_('COM_JUDOWNLOAD_INHERIT'),
		                'message_criteriagroup' => JText::_('COM_JUDOWNLOAD_INHERIT'),
		                'message_style'         => JText::_('COM_JUDOWNLOAD_INHERIT')
		);

		$newCategoryObject = JUDownloadHelper::getCategoryByID($data['parent_id']);

		$newFieldGroupId = $newCategoryObject->fieldgroup_id;
		$fieldGroupText  = JText::_('COM_JUDOWNLOAD_NONE');
		if ($newFieldGroupId > 0)
		{
			$newFieldGroupObject = JUDownloadFrontHelperField::getFieldGroupById($newFieldGroupId);
			if ($newFieldGroupObject->name)
			{
				$fieldGroupText = $newFieldGroupObject->name;
			}
		}

		$newCriteriaGroupId = $newCategoryObject->criteriagroup_id;
		$criteriaGroupText  = JText::_('COM_JUDOWNLOAD_NONE');
		if ($newCriteriaGroupId > 0)
		{
			$newCriteriaGroupObject = JUDownloadFrontHelperCriteria::getCriteriaGroupById($newCriteriaGroupId);
			if ($newCriteriaGroupObject->name)
			{
				$criteriaGroupText = $newCriteriaGroupObject->name;
			}
		}

		$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($data['parent_id']);

		$result['message_fieldgroup']    = JText::_('COM_JUDOWNLOAD_INHERIT') . ' (' . $fieldGroupText . ')';
		$result['message_criteriagroup'] = JText::_('COM_JUDOWNLOAD_INHERIT') . ' (' . $criteriaGroupText . ')';
		$result['message_style']         = JText::_('COM_JUDOWNLOAD_INHERIT') . ' (' . $newTemplateStyleObject->title . ' [' . $newTemplateStyleObject->template_title . ' ]' . ')';

		return $result;
	}


	
	public function checkInheritedDataWhenChangeParentCat($data)
	{
		
		$pk = $data['id'];

		$result = array('status'               => 0,
		                'fieldGroupChanged'    => 0, 'fieldGroupMessage' => '',
		                'criteriaGroupChanged' => 0, 'criteriaGroupMessage' => '',
		                'templateStyleChanged' => 0, 'templateStyleMessage' => '');

		
		if ($pk > 0)
		{
			$table = $this->getTable();
			$table->load($pk);
			$newCategoryObject = JUDownloadHelper::getCategoryByID($data['parent_id']);

			
			if ($data['selected_fieldgroup'] == -1 && $table->selected_fieldgroup == -1)
			{
				
				if ($table->parent_id != $data['parent_id'])
				{
					
					$newFieldGroupId = $newCategoryObject->fieldgroup_id;

					if ($table->fieldgroup_id != $newFieldGroupId)
					{
						$result['status']            = 1;
						$result['fieldGroupChanged'] = 1;

						
						$fieldGroupObject    = JUDownloadFrontHelperField::getFieldGroupById($table->fieldgroup_id);
						$newFieldGroupObject = JUDownloadFrontHelperField::getFieldGroupById($newFieldGroupId);

						$result['fieldGroupMessage'] = JText::sprintf('COM_JUDOWNLOAD_INHERITED_FIELD_GROUP_WILL_BE_CHANGED_FROM_X_TO_X',
							$fieldGroupObject->name ? $fieldGroupObject->name : JText::_('COM_JUDOWNLOAD_NONE'),
							$newFieldGroupObject->name ? $newFieldGroupObject->name : JText::_('COM_JUDOWNLOAD_NONE'));
					}
				}
			}

			
			if ($data['selected_criteriagroup'] == -1 && $table->selected_criteriagroup == -1)
			{
				
				if ($table->parent_id != $data['parent_id'])
				{
					$newCriteriaGroupId = $newCategoryObject->criteriagroup_id;

					
					if ($table->criteriagroup_id != $newCriteriaGroupId)
					{
						$result['status']               = 1;
						$result['criteriaGroupChanged'] = 1;
						
						$criteriaGroupObject    = JUDownloadFrontHelperCriteria::getCriteriaGroupById($table->criteriagroup_id);
						$newCriteriaGroupObject = JUDownloadFrontHelperCriteria::getCriteriaGroupById($newCriteriaGroupId);

						$result['criteriaGroupMessage'] = JText::sprintf('COM_JUDOWNLOAD_INHERITED_CRITERIA_GROUP_WILL_BE_CHANGED_FROM_X_TO_X',
							$criteriaGroupObject->name ? $criteriaGroupObject->name : JText::_('COM_JUDOWNLOAD_NONE'),
							$newCriteriaGroupObject->name ? $newCriteriaGroupObject->name : JText::_('COM_JUDOWNLOAD_NONE'));
					}
				}
			}

			$oldTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($table->id);

			
			if ($data['style_id'] == -1 && $table->style_id == -1)
			{
				
				if ($table->parent_id != $data['parent_id'])
				{
					$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($data['parent_id']);

					
					if ($oldTemplateStyleObject->template_id != $newTemplateStyleObject->template_id)
					{
						$result['status']               = 1;
						$result['templateStyleChanged'] = 1;

						$result['templateStyleMessage'] = JText::sprintf('COM_JUDOWNLOAD_INHERITED_STYLE_WILL_BE_CHANGED_FROM_X_TO_X',
							$oldTemplateStyleObject->title ? $oldTemplateStyleObject->title : JText::_('COM_JUDOWNLOAD_NONE'),
							$newTemplateStyleObject->title ? $newTemplateStyleObject->title : JText::_('COM_JUDOWNLOAD_NONE'));
					}
				}
			}
		}

		return $result;
	}


	
	public function saveCategoryPrepareFieldGroup($pk, $newParentObject, $table, &$data)
	{
		$app = JFactory::getApplication();

		if ($pk > 0)
		{
			
			if ($data['selected_fieldgroup'] == -1)
			{
				
				if ($table->selected_fieldgroup == -1)
				{
					
					if ($table->parent_id == $data['parent_id'])
					{
						
						$data['fieldgroup_id'] = $table->fieldgroup_id;
					}
					else
					{
						
						$newFieldGroupId = $newParentObject->fieldgroup_id;

						if ($table->fieldgroup_id == $newFieldGroupId)
						{
							
							$data['fieldgroup_id'] = $table->fieldgroup_id;
						}
						else
						{
							if ($data['changeFieldGroupAction'] == 1)
							{
								
								$data['fieldgroup_id']       = $table->fieldgroup_id;
								$data['selected_fieldgroup'] = $table->fieldgroup_id;
								$fieldGroupObject            = JUDownloadFrontHelperField::getFieldGroupById($table->fieldgroup_id);
								$app->enqueueMessage(JText::sprintf('COM_JUDOWNLOAD_INHERITED_FIELD_GROUP_HAS_BEEN_CHANGED_TO_X', $fieldGroupObject->name ? $fieldGroupObject->name : JText::_("COM_JUDOWNLOAD_NONE")), 'Notice');
							}
							else
							{
								$data['fieldgroup_id'] = $newParentObject->fieldgroup_id;
							}
						}
					}
				}
				else
				{
					
					$data['fieldgroup_id'] = $newParentObject->fieldgroup_id;
				}
			}
			else
			{
				
				$data['fieldgroup_id'] = $data['selected_fieldgroup'];
			}
		}
		else
		{
			
			if ($data['selected_fieldgroup'] == -1)
			{
				$data['fieldgroup_id'] = $newParentObject->fieldgroup_id;
			}
			else
			{
				$data['fieldgroup_id'] = $data['selected_fieldgroup'];
			}
		}
	}


	
	public function saveCategoryPrepareCriteriaGroup($pk, $newParentObject, $table, &$data)
	{
		$app = JFactory::getApplication();

		if ($pk > 0)
		{
			
			if ($data['selected_criteriagroup'] == -1)
			{
				
				if ($table->selected_criteriagroup == -1)
				{
					
					if ($table->parent_id == $data['parent_id'])
					{
						
						$data['criteriagroup_id'] = $table->criteriagroup_id;
					}
					else
					{
						
						$newCriteriaGroupId = $newParentObject->criteriagroup_id;

						if ($table->criteriagroup_id == $newCriteriaGroupId)
						{
							
							$data['criteriagroup_id'] = $table->criteriagroup_id;
						}
						else
						{
							if ($data['changeCriteriaGroupAction'] == 1)
							{
								
								$data['criteriagroup_id']       = $table->criteriagroup_id;
								$data['selected_criteriagroup'] = $table->criteriagroup_id;
								$criteriaGroupObject            = JUDownloadFrontHelperCriteria::getCriteriaGroupById($table->criteriagroup_id);
								$app->enqueueMessage(JText::sprintf('COM_JUDOWNLOAD_INHERITED_CRITERIA_GROUP_HAS_BEEN_CHANGED_TO_X', $criteriaGroupObject->name ? $criteriaGroupObject->name : JText::_("COM_JUDOWNLOAD_NONE")), 'Notice');
							}
							else
							{
								$data['criteriagroup_id'] = $newParentObject->criteriagroup_id;
							}
						}
					}
				}
				else
				{
					
					$data['criteriagroup_id'] = $newParentObject->criteriagroup_id;
				}
			}
			else
			{
				
				$data['criteriagroup_id'] = $data['selected_criteriagroup'];
			}
		}
		else
		{
			
			if ($data['selected_criteriagroup'] == -1)
			{
				$data['criteriagroup_id'] = $newParentObject->criteriagroup_id;
			}
			else
			{
				$data['criteriagroup_id'] = $data['selected_criteriagroup'];
			}
		}
	}


	
	public function saveCategoryPrepareTemplateParams($pk, $table, &$data)
	{
		$app = JFactory::getApplication();

		if ($pk > 0)
		{
			$oldTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($table->id);

			if ($data['style_id'] == -2)
			{
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getDefaultTemplateStyle();
			}
			elseif ($data['style_id'] == -1)
			{
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($data['parent_id']);
				if ($table->style_id == -1)
				{
					if ($table->parent_id != $data['parent_id'])
					{
						if ($oldTemplateStyleObject->template_id != $newTemplateStyleObject->template_id)
						{
							if ($data['changeTemplateStyleAction'] == 1)
							{
								$data['style_id']       = $oldTemplateStyleObject->id;
								$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleObject($data['style_id']);
								$app->enqueueMessage(JText::sprintf('COM_JUDOWNLOAD_INHERITED_TEMPLATE_STYLE_HAS_BEEN_CHANGED_TO_X', $newTemplateStyleObject->title ? $newTemplateStyleObject->title : JText::_("COM_JUDOWNLOAD_NONE")), 'Notice');
							}
						}
					}
				}
			}
			else
			{
				
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleObject($data['style_id']);
			}
		}

		
		if (isset($data['template_params']) && is_array($data['template_params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($data['template_params']);
			$data['template_params'] = (string) $registry;
		}

		if ($pk > 0)
		{
			if ($oldTemplateStyleObject->template_id != $newTemplateStyleObject->template_id)
			{
				$data['template_params'] = '';
			}
		}
	}


	
	public function saveCategoryPreparePluginParam(&$data)
	{
		$db = JFactory::getDbo();

		
		$db_plugin_params = array();
		if (!isset($data['plugin_params']) || !is_array($data['plugin_params']))
		{
			$data['plugin_params'] = array();
		}

		if ($data['id'] > 0)
		{
			$db->setQuery("SELECT plugin_params FROM #__judownload_categories WHERE id = " . $data['id']);
			$rule_str      = $db->loadResult();
			$rule_registry = new JRegistry;
			$rule_registry->loadString($rule_str);
			$db_plugin_params = $rule_registry->toArray();
		}

		if (!empty($db_plugin_params))
		{
			$db->setQuery("SELECT element FROM #__extensions WHERE type='plugin' AND folder='judownload'");
			$rule_plugin = $db->loadColumn();

			foreach ($db_plugin_params AS $key => $value)
			{
				
				if (!in_array($key, $rule_plugin))
				{
					unset($db_plugin_params[$key]);
				}

				
				if (array_key_exists($key, $data['plugin_params']))
				{
					unset($db_plugin_params[$key]);
				}
			}
		}

		$plugin_params = array_merge($db_plugin_params, $data['plugin_params']);

		$registry = new JRegistry;
		$registry->loadArray($plugin_params);
		$data['plugin_params'] = (string) $registry;
	}


	
	public function saveCategoryPrepareDescription(&$data)
	{
		
		if (isset($data['description']))
		{
			$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
			$tagPos  = preg_match($pattern, $data['description']);

			if ($tagPos == 0)
			{
				$data['introtext'] = $data['description'];
				$data['fulltext']  = '';
			}
			else
			{
				list ($data['introtext'], $data['fulltext']) = preg_split($pattern, $data['description'], 2);
			}
		}
		unset($data['description']);
	}


	
	public function saveCategoryPrepareConfigParams(&$data)
	{
		
		
		$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
		if ($data['parent_id'] == $rootCat->id && isset($data['config_params']) && is_array($data['config_params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($data['config_params']);
			$data['config_params'] = (string) $registry;
		}
		else
		{
			$data['config_params'] = '';
		}
	}


	
	public function save($data)
	{
		set_time_limit(0);

		
		$dispatcher = JDispatcher::getInstance();
		$table      = $this->getTable();
		$key        = $table->getKeyName();
		$pk         = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew      = true;

		
		JPluginHelper::importPlugin('content');

		$newParentObject = JUDownloadHelper::getCategoryByID($data['parent_id']);

		$tableBeforeSave = null;

		
		try
		{
			
			if ($pk > 0)
			{
				$table->load($pk);

				
				$tableBeforeSave = clone $table;

				$oldTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($table->id);

				$isNew = false;
			}

			$this->saveCategoryPrepareFieldGroup($pk, $newParentObject, $table, $data);

			$this->saveCategoryPrepareCriteriaGroup($pk, $newParentObject, $table, $data);

			$this->saveCategoryPrepareTemplateParams($pk, $table, $data);

			$this->saveCategoryPreparePluginParam($data);

			$this->saveCategoryPrepareDescription($data);

			$this->saveCategoryPrepareConfigParams($data);

			
			if ($table->parent_id != $data['parent_id'] || $data['id'] == 0)
			{
				$table->setLocation($data['parent_id'], 'last-child');
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

			$this->saveCategoryChangeFieldGroup($tableBeforeSave, $table, $isNew);

			$this->saveCategoryChangeCriteriaGroup($tableBeforeSave, $table, $isNew);

			if (!$isNew)
			{
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($table->id);

				if (isset($oldTemplateStyleObject))
				{
					if ($oldTemplateStyleObject->template_id != $newTemplateStyleObject->template_id)
					{
						$this->removeTemplateParamsOfInheritedStyleCatDoc($table->id);
					}
				}
			}

			$this->saveCategoryFieldOrdering($table);

			$this->saveCategoryImage($pk, $table, $data);

			$this->saveCategoryRelation($table);

			$this->saveCategoryModerator($table);

			
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


	
	public function saveCategoryChangeFieldGroup($tableBeforeSave, $table, $isNew)
	{
		if (!$isNew)
		{
			$db = JFactory::getDbo();

			
			if ($tableBeforeSave->fieldgroup_id != $table->fieldgroup_id)
			{
				

				
				$docId_arr = JUDownloadHelper::getDocumentIdsByCatId($table->id);
				foreach ($docId_arr AS $docId)
				{
					JUDownloadHelper::deleteFieldValuesOfDocument($docId);
				}

				
				$query = "DELETE FROM #__judownload_fields_ordering WHERE item_id = $table->id AND type = 'category'";
				$db->setQuery($query);
				$db->execute();

				
				JUDownloadHelper::changeInheritedFieldGroupId($table->id, $table->fieldgroup_id);
			}
		}
	}


	
	public function saveCategoryChangeCriteriaGroup($tableBeforeSave, $table, $isNew)
	{
		if (!$isNew)
		{
			
			if ($tableBeforeSave->criteriagroup_id != $table->criteriagroup_id)
			{
				

				
				
				

				
				JUDownloadHelper::changeInheritedCriteriaGroupId($table->id, $table->criteriagroup_id);
			}
		}
	}


	
	public function saveCategoryFieldOrdering($table)
	{
		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();

		
		if ($table->field_ordering_type == 1)
		{
			$fields_ordering = $app->input->post->get("fields_ordering", array(), 'array');
			if ($fields_ordering)
			{
				$fieldsOrderingTable = JTable::getInstance("FieldOrdering", "JUDownloadTable");
				
				$query = "SELECT id FROM #__judownload_fields WHERE group_id = 1 OR group_id = " . $table->fieldgroup_id;
				$db->setQuery($query);
				$field_ids = $db->loadColumn();
				$ordering  = 0;
				foreach ($fields_ordering AS $key => $field_id)
				{
					if (in_array($field_id, $field_ids))
					{
						$ordering++;
						$fieldsOrderingTable->reset();
						if ($fieldsOrderingTable->load(array("item_id" => $table->id, "type" => "category", "field_id" => $field_id)))
						{
							$fieldsOrderingTable->bind(array("ordering" => $ordering));
						}
						else
						{
							$fieldsOrderingTable->bind(array("id" => 0, "item_id" => $table->id, "type" => "category", "field_id" => $field_id, "ordering" => $ordering));
						}
						$fieldsOrderingTable->store();
					}
				}
			}
		}
	}


	
	public function saveCategoryImage($pk, $table, &$data)
	{
		$app    = JFactory::getApplication();
		$jInput = $app->input;

		if ($pk > 0)
		{
			
			if ($jInput->post->getInt('remove_jform_images_intro_image', 0) == 1 || ($jInput->post->getInt('remove_jform_images_detail_image', 0) == 1 && $jInput->post->getInt('use_detail_image', 0) == 1))
			{
				
				$intro_image_dir                       = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("category_intro_image_directory", "media/com_judownload/images/category/intro/") . $data['images']['intro_image'];
				$ori_intro_image_dir                   = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("category_intro_image_directory", "media/com_judownload/images/category/intro/") . 'original/' . $data['images']['intro_image'];
				$data['images']['intro_image']         = '';
				$data['images']['intro_image_alt']     = '';
				$data['images']['intro_image_caption'] = '';
				JFile::delete($ori_intro_image_dir);
				JFile::delete($intro_image_dir);
			}

			if ($jInput->post->getInt('remove_jform_images_detail_image', 0) == 1)
			{
				
				$full_image_dir                         = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("category_detail_image_directory", "media/com_judownload/images/category/detail/") . $data['images']['detail_image'];
				$ori_full_image_dir                     = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("category_detail_image_directory", "media/com_judownload/images/category/detail/") . "original/" . $data['images']['detail_image'];
				$data['images']['detail_image']         = '';
				$data['images']['detail_image_alt']     = '';
				$data['images']['detail_image_caption'] = '';
				JFile::delete($full_image_dir);
				JFile::delete($ori_full_image_dir);
			}
		}

		
		$mime_types                 = array("image/jpeg", "image/pjpeg", "image/png", "image/gif", "image/bmp", "image/x-windows-bmp");
		$num_files_failed_mime_type = 0;
		$images                     = $app->input->files->get('images', array());
		$old_ori_intro_image_path   = (isset($data['images']['intro_image']) && $data['images']['intro_image']) ?
			JUDownloadFrontHelper::getDirectory("category_intro_image_directory", "media/com_judownload/images/category/intro/") . 'original/' . $data['images']['intro_image'] : '';
		$old_intro_image_path       = $old_ori_intro_image_path ?
			JUDownloadFrontHelper::getDirectory("category_intro_image_directory", "media/com_judownload/images/category/intro/") . $data['images']['intro_image'] : '';
		$old_ori_detail_image_path  = (isset($data['images']['detail_image']) && $data['images']['detail_image']) ?
			JUDownloadFrontHelper::getDirectory("category_detail_image_directory", "media/com_judownload/images/category/detail/") . 'original/' . $data['images']['detail_image'] : '';
		$old_detail_image_path      = $old_ori_detail_image_path ?
			JUDownloadFrontHelper::getDirectory("category_detail_image_directory", "media/com_judownload/images/category/detail/") . $data['images']['detail_image'] : '';

		foreach ($images AS $key => $image)
		{
			if ($image['name'])
			{
				if (!in_array($image['type'], $mime_types))
				{
					$num_files_failed_mime_type++;
					continue;
				}
				$info          = pathinfo($image['name']);
				$replace       = array('id' => $table->id, 'document' => '', 'category' => $table->title, 'image_name' => $info['filename']);
				$image['name'] = JUDownloadHelper::parseImageNameByTags($replace, 'category', $table->id, null) . '.' . $info['extension'];
				
				if ($key == 'intro' && $app->input->post->get('use_detail_image', 0) != 1)
				{
					$new_ori_image_path = JUDownloadFrontHelper::getDirectory("category_intro_image_directory", "media/com_judownload/images/category/intro/") . 'original/' . $image['name'];
					$new_image_path     = JUDownloadFrontHelper::getDirectory("category_intro_image_directory", "media/com_judownload/images/category/intro/") . $image['name'];

					if (!JFile::upload($image['tmp_name'], JPATH_ROOT . "/" . $new_ori_image_path) ||
						!JUDownloadHelper::renderImages(JPATH_ROOT . "/" . $new_ori_image_path, JPATH_ROOT . "/" . $new_image_path, "category_intro", true, $table->id)
					)
					{
						unset($images['intro']);
					}
					else
					{
						$delete_old_intro_image        = ($data['images']['intro_image'] && $data['images']['intro_image'] !== $image['name']) ? true : false;
						$data['images']['intro_image'] = $image['name'];
					}

					if ($delete_old_intro_image)
					{
						if ($old_intro_image_path && JFile::exists(JPATH_ROOT . "/" . $old_intro_image_path))
						{
							JFile::delete(JPATH_ROOT . "/" . $old_ori_intro_image_path);
							JFile::delete(JPATH_ROOT . "/" . $old_intro_image_path);
						}
					}
				}

				
				if ($key == 'detail')
				{
					$new_ori_image_path = JUDownloadFrontHelper::getDirectory("category_detail_image_directory", "media/com_judownload/images/category/detail/") . 'original/' . $image['name'];
					$new_image_path     = JUDownloadFrontHelper::getDirectory("category_detail_image_directory", "media/com_judownload/images/category/detail/") . $image['name'];
					if (!JFile::upload($image['tmp_name'], JPATH_ROOT . "/" . $new_ori_image_path) ||
						!JUDownloadHelper::renderImages(JPATH_ROOT . "/" . $new_ori_image_path, JPATH_ROOT . "/" . $new_image_path, "category_detail", true, $table->id)
					)
					{
						unset($images['detail']);
					}
					else
					{
						if ($data['images']['detail_image'] && $data['images']['detail_image'] !== $image['name'])
						{
							if ($old_detail_image_path && JFile::exists(JPATH_ROOT . "/" . $old_detail_image_path))
							{
								JFile::delete(JPATH_ROOT . "/" . $old_ori_detail_image_path);
								JFile::delete(JPATH_ROOT . "/" . $old_detail_image_path);
							}
						}

						$data['images']['detail_image'] = $image['name'];
					}
				}
			}
		}

		if ($num_files_failed_mime_type)
		{
			JError::raise(
				E_NOTICE,
				500,
				JText::plural('COM_JUDOWNLOAD_N_IMAGES_ARE_NOT_VALID_MIMETYPE', $num_files_failed_mime_type, implode(",", $mime_types))
			);
		}

		
		if ($app->input->post->get('use_detail_image', 0) == 1 && $data['images']['detail_image'])
		{
			$old_ori_detail_image_path = JUDownloadFrontHelper::getDirectory("category_detail_image_directory", "media/com_judownload/images/category/detail/") . 'original/' . $data['images']['detail_image'];
			$new_ori_intro_image_path  = JUDownloadFrontHelper::getDirectory("category_intro_image_directory", "media/com_judownload/images/category/intro/") . 'original/' . $data['images']['detail_image'];
			$new_image_path            = JUDownloadFrontHelper::getDirectory("category_intro_image_directory", "media/com_judownload/images/category/intro/") . $data['images']['detail_image'];

			if (JFile::copy(JPATH_ROOT . "/" . $old_ori_detail_image_path, JPATH_ROOT . "/" . $new_ori_intro_image_path) &&
				JUDownloadHelper::renderImages(JPATH_ROOT . "/" . $new_ori_intro_image_path, JPATH_ROOT . "/" . $new_image_path, 'category_intro', true, $table->id)
			)
			{
				if ($data['images']['intro_image'] && $data['images']['intro_image'] !== $data['images']['detail_image'])
				{
					if (JFile::exists(JPATH_ROOT . "/" . $old_ori_intro_image_path))
					{
						JFile::delete(JPATH_ROOT . "/" . $old_ori_intro_image_path);
					}
					if (JFile::exists(JPATH_ROOT . "/" . $old_intro_image_path))
					{
						JFile::delete(JPATH_ROOT . "/" . $old_intro_image_path);
					}
				}

				$data['images']['intro_image'] = $data['images']['detail_image'];
			}
		}

		if (!empty($data['images']))
		{
			$registry = new JRegistry;
			$registry->loadArray($data['images']);
			$table->images = (string) $registry;
			$table->store();
		}
	}


	
	public function saveCategoryRelation($table)
	{
		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();

		
		$relCategories      = $app->input->post->get("relcategories", array(), 'array');
		$relCategoriesTable = JTable::getInstance("CategoriesRelation", "JUDownloadTable");
		foreach ($relCategories AS $order => $relcategory)
		{
			if ($relCategoriesTable->load(array("cat_id" => $table->id, "cat_id_related" => $relcategory), true))
			{
				$relCategoriesTable->ordering = $order + 1;
				$relCategoriesTable->store();
			}
			else
			{
				$relCategoriesTable->bind(array("id" => 0, "cat_id" => $table->id, "cat_id_related" => $relcategory, "ordering" => $order + 1), true);
				$relCategoriesTable->store();
			}
		}

		$query = $db->getQuery(true);
		$query->select('cat_id_related');
		$query->from('#__judownload_categories_relations');
		$query->where('cat_id = ' . $table->id);
		$db->setQuery($query);
		$ori_rel_cats = $db->loadColumn();

		$removed_rel_cats = array_diff($ori_rel_cats, $relCategories);
		if ($removed_rel_cats)
		{
			$query = "DELETE FROM #__judownload_categories_relations WHERE cat_id_related IN(" . implode(",", $removed_rel_cats) . ")";
			$db->setQuery($query);
			$db->execute();
		}
	}


	
	public function getModeratorManageCategory($categoryId, $moderatorId)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('m.*');
		$query->from('#__judownload_moderators AS m');
		$query->join('INNER', '#__judownload_moderators_xref AS mx ON m.id = mx.mod_id');
		$query->where('m.id=' . $moderatorId);
		$query->where('mx.cat_id =' . $categoryId);
		$db->setQuery($query);

		return $db->loadAssoc();
	}


	
	public function cloneModerator($categoryId, $moderatorId, $moderatorPermissions)
	{
		$db   = JFactory::getDbo();
		$user = JFactory::getUser();

		
		
		$dataInsert = $moderatorPermissions;

		$moderatorTable    = JTable::getInstance('Moderator', 'JUDownloadTable');
		$keyModeratorTable = $moderatorTable->getKeyName();

		$moderatorTable->load($moderatorId);

		$query = $db->getQuery(true);
		$query->select('rules');
		$query->from('#__assets');
		$query->where('id = ' . $moderatorTable->asset_id);
		$db->setQuery($query);
		$accessRules = $db->loadResult();

		$dataInsert['rules'] = json_decode($accessRules);

		$moderatorTable->asset_id = 0;

		$dataInsert[$keyModeratorTable] = 0;
		$dataInsert['created_by']       = $user->id;
		$dataInsert['created']          = JFactory::getDate()->toSql();

		$moderatorTable->bind($dataInsert);

		$moderatorTable->check();

		$moderatorTable->store();

		
		$query = $db->getQuery(true);
		$query->update('#__judownload_moderators_xref');
		$query->set('mod_id =' . $moderatorTable->id);
		$query->where('cat_id =' . $categoryId);
		$query->where('mod_id =' . $moderatorId);
		$db->setQuery($query);
		$db->execute();
	}


	
	public function saveCategoryModerator($table)
	{
		$app = JFactory::getApplication();

		
		$db                         = JFactory::getDbo();
		$moderatorPermissionsSubmit = $app->input->get('mod', array(), 'array');

		foreach ($moderatorPermissionsSubmit AS $moderatorId => $moderatorPermissions)
		{
			$dataBeforeSave = $this->getModeratorManageCategory($table->id, $moderatorId);

			$diff = false;
			foreach ($moderatorPermissions AS $permissionName => $permissionValue)
			{
				if (isset($dataBeforeSave[$permissionName]))
				{
					if ($moderatorPermissions[$permissionName] != $dataBeforeSave[$permissionName])
					{
						$diff = true;
					}
				}
			}

			if ($diff == true)
			{
				
				$query = $db->getQuery(true);
				$query->select('COUNT(*)');
				$query->from('#__judownload_moderators_xref');
				$query->where('mod_id =' . $moderatorId);
				$db->setQuery($query);
				$totalCategoryModeratorManage = $db->loadResult();

				
				
				if ($totalCategoryModeratorManage > 1)
				{
					
					$this->cloneModerator($table->id, $moderatorId, $moderatorPermissions);
				}
				else
				{
					
					$moderatorTable = JTable::getInstance('Moderator', 'JUDownloadTable');

					$moderatorTable->load($moderatorId);

					$moderatorTable->bind($moderatorPermissions);

					$moderatorTable->check();

					$moderatorTable->store();
				}
			}
		}
	}


	
	protected function getReorderConditions($table)
	{
		$condition   = array();
		$condition[] = 'parent_id = ' . (int) $table->parent_id;

		return $condition;
	}

	
	protected function generateNewTitle($parent_id, $alias, $title)
	{
		
		$table = $this->getTable();
		while ($table->load(array('alias' => $alias, 'parent_id' => $parent_id)))
		{
			$title = JString::increment($title);
			$alias = JString::increment($alias, 'dash');
		}

		return array($title, $alias);
	}

	
	public function copyCats($cat_id_arr, $tocat_id_arr, $copy_option_arr)
	{
		if (empty($cat_id_arr))
		{
			JError::raiseWarning(100, JText::_('COM_JUDOWNLOAD_NO_SOURCE_CATEGORY_SELECTED'));

			return false;
		}

		if (empty($tocat_id_arr))
		{
			JError::raiseWarning(100, JText::_('COM_JUDOWNLOAD_NO_TARGET_CATEGORY_SELECTED'));

			return false;
		}

		set_time_limit(0);

		$documentModel     = JModelLegacy::getInstance("Document", "JUDownloadModel");
		$user              = JFactory::getUser();
		$db                = JFactory::getDbo();
		$table             = $this->getTable();
		$cat_id_arr_cloned = $cat_id_arr;

		$total_copied_categories = 0;
		foreach ($tocat_id_arr AS $tocat_id)
		{
			$tocat_obj = $table->load($tocat_id, true);
			if (!$tocat_obj)
			{
				continue;
			}

			$assetName   = 'com_judownload.category.' . (int) $tocat_id;
			$candoCreate = $user->authorise('judl.category.create', $assetName);
			if (!$candoCreate)
			{
				JError::raiseWarning(401, JText::sprintf('COM_JUDOWNLOAD_CAN_NOT_CREATE_CATEGORY', $table->title));
				continue;
			}

			
			$query = $db->getQuery(true);
			$query->select('COUNT(*)');
			$query->from('#__judownload_categories');
			$db->setQuery($query);
			$count = $db->loadResult();

			$mapping = $mapping_field_group = $mapping_criteria_group = array();
			while (!empty($cat_id_arr) && $count > 0)
			{
				$cat_id = array_shift($cat_id_arr);
				$table->reset();
				if (!$table->load($cat_id))
				{
					continue;
				}

				
				if (in_array('copy_subcategories', $copy_option_arr))
				{
					
					$query = $db->getQuery(true);
					$query->select('id');
					$query->from('#__judownload_categories');
					$query->where('lft > ' . (int) $table->lft);
					$query->where('rgt < ' . (int) $table->rgt);
					$query->order('lft');
					$db->setQuery($query);
					$childIds = $db->loadColumn();

					
					foreach ($childIds AS $childId)
					{
						if (!in_array($childId, $cat_id_arr))
						{
							array_push($cat_id_arr, $childId);
						}
					}
				}

				
				$old_cat_id        = $table->id;
				$old_parent_cat_id = $table->parent_id;
				$table->id         = 0;
				
				$table->parent_id        = isset($mapping[$table->parent_id]) ? $mapping[$table->parent_id] : $tocat_id;
				$table->checked_out      = 0;
				$table->checked_out_time = "0000-00-00 00:00:00";

				
				$table->setLocation($table->parent_id, 'last-child');
				
				$this->prepareTable($table);

				list($title, $alias) = $this->generateNewTitle($table->parent_id, $table->alias, $table->title);
				$table->title = $title;
				$table->alias = $alias;

				if ($table->style_id == -1)
				{
					
					if ($old_parent_cat_id != $table->parent_id)
					{
						$oldTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($old_parent_cat_id);
						$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($table->parent_id);
						if ($oldTemplateStyleObject->template_id != $newTemplateStyleObject->template_id)
						{
							if (in_array('keep_template_params', $copy_option_arr))
							{
								$table->style_id = $oldTemplateStyleObject->id;
							}
							else
							{
								$table->template_params = '';
							}
						}
					}
				}

				
				if (in_array('copy_cat_permission', $copy_option_arr))
				{
					$assetTable = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
					$assetTable->reset();
					if ($assetTable->loadByName('com_judownload.category.' . $cat_id))
					{
						$table->setRules($assetTable->rules);
					}
					else
					{
						$table->setRules('{}');
					}
				}
				else
				{
					$table->setRules('{}');
				}

				
				
				$keep_field_group = true;
				if (isset($mapping_field_group[$table->parent_id]))
				{
					if ($table->selected_fieldgroup == -1)
					{
						if ($table->fieldgroup_id != $mapping_field_group[$table->parent_id])
						{
							$keep_field_group = false;
						}
						$table->fieldgroup_id = $mapping_field_group[$table->parent_id];
					}
					
				}
				else
				{
					if (in_array('copy_extra_fields', $copy_option_arr))
					{
						if ($table->selected_fieldgroup == -1)
						{
							$table->selected_fieldgroup = $table->fieldgroup_id;
						}
					}
					else
					{
						if ($table->fieldgroup_id != 0)
						{
							$keep_field_group = false;
						}
						$table->selected_fieldgroup = $table->fieldgroup_id = 0;
					}
				}

				
				
				if (isset($mapping_criteria_group[$table->parent_id]))
				{
					if ($table->selected_criteriagroup == -1)
					{
						$table->criteriagroup_id = $mapping_criteria_group[$table->parent_id];
					}
					
				}
				else
				{
					if (in_array('copy_rates', $copy_option_arr))
					{
						if ($table->selected_criteriagroup == -1)
						{
							$table->selected_criteriagroup = $table->criteriagroup_id;
						}
					}
					else
					{
						$table->selected_criteriagroup = $table->criteriagroup_id = 0;
					}
				}

				$table->check();
				if (!$table->store())
				{
					continue;
				}

				$new_cat_id = $table->id;

				
				if ($keep_field_group)
				{
					$query = "INSERT INTO #__judownload_fields_ordering(`item_id`, `type`, `field_id`, `ordering`)
								SELECT $new_cat_id, `type`, `field_id`, `ordering` FROM #__judownload_fields_ordering WHERE `item_id` = $old_cat_id AND `type` = 'category'";
					$db->setQuery($query);
					$db->execute();
				}

				
				$images = json_decode($table->images);
				if ($images->intro_image)
				{
					$newImgFile          = $new_cat_id . substr($images->intro_image, strpos($images->intro_image, '_'));
					$intro_image_dir     = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("category_intro_image_directory", "media/com_judownload/images/category/intro/");
					$ori_intro_image_dir = $intro_image_dir . 'original/';
					if (JFile::exists($intro_image_dir))
					{
						JFile::copy($images->intro_image, $newImgFile, $intro_image_dir);
						JFile::copy($images->intro_image, $newImgFile, $ori_intro_image_dir);
					}
					$images->intro_image = $newImgFile;
				}

				if ($images->detail_image)
				{
					$newImgFile           = $new_cat_id . substr($images->detail_image, strpos($images->detail_image, '_'));
					$detail_image_dir     = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("category_detail_image_directory", "'media/com_judownload/images/category/detail/");
					$ori_detail_image_dir = $detail_image_dir . "original/";
					if (JFile::exists($detail_image_dir))
					{
						JFile::copy($images->detail_image, $newImgFile, $detail_image_dir);
						JFile::copy($images->detail_image, $newImgFile, $ori_detail_image_dir);
					}
					$images->detail_image = $newImgFile;
				}

				$registry = new JRegistry($images);

				$query = $db->getQuery(true);
				$query->update('#__judownload_categories')->set("images = " . $db->quote($registry->toString()))->where("id=$new_cat_id");
				$db->setQuery($query);
				$db->execute();

				
				if (in_array('copy_related_categories', $copy_option_arr))
				{
					$query = "INSERT INTO `#__judownload_categories_relations` (cat_id, cat_id_related, ordering) SELECT $new_cat_id, cat_id_related, ordering FROM `#__judownload_categories_relations` WHERE cat_id = $cat_id";
					$db->setQuery($query);
					$db->execute();
				}

				
				if (in_array('copy_documents', $copy_option_arr))
				{
					$query = "SELECT d.id FROM #__judownload_documents_xref AS dxref JOIN #__judownload_documents AS d ON dxref.doc_id = d.id WHERE dxref.cat_id =" . $cat_id . " AND  dxref.main=1 ORDER BY d.alias";
					$db->setQuery($query);
					$docIds = $db->loadColumn();
					$documentModel->copyDocuments($docIds, (array) $new_cat_id, $copy_option_arr);
				}

				$total_copied_categories++;
				$count--;
				
				$mapping[$old_cat_id]                = $new_cat_id;
				$mapping_field_group[$new_cat_id]    = $table->fieldgroup_id;
				$mapping_criteria_group[$new_cat_id] = $table->criteriagroup_id;
			}

			$cat_id_arr = $cat_id_arr_cloned;
		}

		return $total_copied_categories;
	}

	
	public function moveCats($cat_id_arr, $tocat_id, $move_option_arr)
	{
		$tocat_id   = (int) $tocat_id;
		$cat_id_arr = (array) $cat_id_arr;

		if (empty($cat_id_arr))
		{
			JError::raiseWarning(100, JText::_('COM_JUDOWNLOAD_NO_SOURCE_CATEGORY_SELECTED'));

			return false;
		}

		if (empty($tocat_id))
		{
			JError::raiseWarning(100, JText::_('COM_JUDOWNLOAD_NO_TARGET_CATEGORY_SELECTED'));

			return false;
		}


		$user = JFactory::getUser();

		$table = $this->getTable();

		if (!$table->load($tocat_id))
		{
			JError::raiseWarning(500, JText::_('COM_JUDOWNLOAD_TARGET_CATEGORY_NOT_FOUND'));

			return false;
		}

		$assetName   = 'com_judownload.category.' . (int) $tocat_id;
		$candoCreate = $user->authorise('judl.category.create', $assetName);
		if (!$candoCreate)
		{
			JError::raiseError(100, JText::sprintf('COM_JUDOWNLOAD_CAN_NOT_CREATE_CATEGORY_IN_CATEGORY_X', $table->title));

			return false;
		}

		set_time_limit(0);

		$total_moved_categories = 0;
		foreach ($cat_id_arr AS $cat_id)
		{
			if (!$table->load($cat_id, true))
			{
				continue;
			}

			$assetName = 'com_judownload.category.' . (int) $cat_id;
			$candoEdit = $user->authorise('judl.category.edit', $assetName);
			if (!$candoEdit)
			{
				JError::raiseWarning(100, JText::_('COM_JUDOWNLOAD_YOU_DONT_HAVE_PERMISSION_TO_EDIT_CAT'));
				continue;
			}

			
			if ($this->isChildCategory($cat_id, $tocat_id) || $tocat_id == $table->parent_id)
			{
				continue;
			}

			$table->setLocation($tocat_id, 'last-child');

			

			if (in_array('keep_extra_fields', $move_option_arr))
			{
				
				
				if ($table->selected_fieldgroup == -1)
				{
					$tocat_obj = JUDownloadHelper::getCategoryByID($tocat_id);
					if ($table->fieldgroup_id != $tocat_obj->fieldgroup_id)
					{
						$table->selected_fieldgroup = $table->fieldgroup_id;
					}
				}
			}
			
			else
			{
				if ($table->fieldgroup_id != 0)
				{
					JUDownloadHelper::changeInheritedFieldGroupId($table->id, 0);
					
					$query = "DELETE FROM #__judownload_fields_ordering WHERE `item_id` = " . $table->id . " AND `type` = 'category'";
					$db    = JFactory::getDbo();
					$db->setQuery($query);
					$db->execute();
				}
				$table->selected_fieldgroup = $table->fieldgroup_id = 0;
			}

			
			if (in_array('keep_rates', $move_option_arr))
			{
				
				
				if ($table->selected_criteriagroup == -1)
				{
					$tocat_obj = JUDownloadHelper::getCategoryByID($tocat_id);
					if ($table->criteriagroup_id != $tocat_obj->criteriagroup_id)
					{
						$table->selected_criteriagroup = $table->criteriagroup_id;
					}
				}
			}
			
			else
			{
				$table->selected_criteriagroup = $table->criteriagroup_id = 0;
				JUDownloadHelper::changeInheritedCriteriagroupId($table->id, $table->criteriagroup_id);
			}


			

			
			if ($table->style_id == -1)
			{
				
				if ($table->parent_id != $tocat_id)
				{
					$oldTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($table->id);
					$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($tocat_id);
					if ($oldTemplateStyleObject->template_id != $newTemplateStyleObject->template_id)
					{
						if (in_array('keep_template_params', $move_option_arr))
						{
							$table->style_id = $oldTemplateStyleObject->id;
						}
						else
						{
							$table->template_params = '';
							JUDownloadFrontHelperTemplate::removeTemplateParamsOfInheritedStyleCatDoc($table->id);
						}
					}
				}
			}

			if ($table->store())
			{
				$total_moved_categories++;
			}
		}

		return $total_moved_categories;
	}

	
	protected function isChildCategory($parentCatId, $childCatId)
	{
		$db    = JFactory::getDbo();
		$query = "SELECT id FROM #__judownload_categories WHERE parent_id = " . $parentCatId;
		$db->setQuery($query);
		$catIds  = $db->loadColumn();
		$isChild = false;
		if ($catIds)
		{
			if (in_array($childCatId, $catIds))
			{
				return true;
			}
			else
			{
				foreach ($catIds AS $catid)
				{
					$isChild = $this->isChildCategory($catid, $childCatId);
					if ($isChild)
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	public function fastAdd()
	{
		$app        = JFactory::getApplication();
		$cat_names  = preg_split('/\n/', $app->input->post->get('cat_names', '', 'string'));
		$cat_id     = $app->input->getInt('cat_id', 0);
		$app        = JFactory::getApplication();
		$added_cats = array();
		if ($cat_id && $cat_names)
		{
			foreach ($cat_names AS $key => $cat_name)
			{
				$cat_name = trim($cat_name);
				if (!$cat_name)
				{
					unset($cat_names[$key]);
					continue;
				}

				$category_model                 = JModelLegacy::getInstance("Category", "JUDownloadModel");
				$data                           = array();
				$data['id']                     = 0;
				$data['title']                  = $cat_name;
				$data['parent_id']              = $cat_id;
				$data['published']              = $app->input->getInt("published", 0);
				$data['access']                 = 1;
				$data['selected_fieldgroup']    = -1;
				$data['selected_criteriagroup'] = -1;
				$data['show_item']              = 1;
				$data['style_id']               = -1;
				$data['template_params']        = '';
				$data['plugin_params']          = '';
				$data['field_ordering_type']    = 0;
				$data['images']                 = '';
				$data['language']               = '*';


				if ($category_model->save($data))
				{
					$added_cats[] = $cat_names[$key];
					unset($cat_names[$key]);
				}
			}
		}

		if (count($added_cats))
		{
			$app->setUserState('com_judownload.categories.fastaddsuccess', JText::sprintf('COM_JUDOWNLOAD_N_CATEGORIES_ADDED', count($added_cats)));
		}

		if (count($cat_names))
		{
			$app->setUserState('com_judownload.categories.fastadderror', JText::sprintf('COM_JUDOWNLOAD_CATEGORIES_FAILED_TO_ADD', implode(", ", $cat_names)));
		}

		
		$script = "window.addEventListener('load', function() {
						if (window.parent) {
							window.parent.location.reload(true);
							window.parent.SqueezeBox.close();
						}
					});";

		$document = JFactory::getDocument();
		$document->addScriptDeclaration($script);
	}

	
	public function delete(&$pks)
	{
		
		$dispatcher = JDispatcher::getInstance();
		$pks        = (array) $pks;
		$table      = $this->getTable();
		
		JPluginHelper::importPlugin('content');
		
		$modelDocument   = JModelLegacy::getInstance('Document', 'JUDownloadModel');
		$documentTable   = JTable::getInstance("Document", "JUDownloadTable");
		$canDeleteCatIds = $canNotDeleteCatIds = $canDeleteDocIds = $canNotDeleteDocIds = array();
		$db              = JFactory::getDbo();

		while (!empty($pks))
		{
			$pk = array_shift($pks);
			$table->reset();
			if ($table->load($pk))
			{
				$deleteCurrentCat = true;
				if ($this->canDelete($table))
				{
					$query = $db->getQuery(true);
					$query->SELECT('doc_id');
					$query->FROM('#__judownload_documents_xref');
					$query->WHERE('cat_id=' . $pk . ' AND main=1');
					$db->setQuery($query);
					$docIds = $db->loadColumn();
					if ($docIds)
					{
						foreach ($docIds AS $docId)
						{
							$documentTable->reset();
							if ($documentTable->load($docId))
							{
								if ($modelDocument->canDelete($documentTable))
								{
									$canDeleteDocIds[] = $docId;
								}
								else
								{
									$canNotDeleteDocIds[] = $docId;
									$deleteCurrentCat     = false;
								}
							}
						}
					}
				}
				else
				{
					$deleteCurrentCat = false;
				}

				if (!$deleteCurrentCat)
				{
					$canNotDeleteCatIds[] = $pk;
				}
				else
				{
					$canDeleteCatIds[] = $pk;
				}

				
				$query = $db->getQuery(true);
				$query->select('id');
				$query->from('#__judownload_categories');
				$query->where('lft > ' . (int) $table->lft);
				$query->where('rgt < ' . (int) $table->rgt);
				$query->order('lft');
				$db->setQuery($query);
				$childIds = $db->loadColumn();

				
				foreach ($childIds AS $childId)
				{
					if (!in_array($childId, $pks))
					{
						array_push($pks, $childId);
					}
				}
			}
		}

		if ($canNotDeleteCatIds)
		{
			foreach ($canNotDeleteCatIds AS $caNotDeleteCatId)
			{
				$categories = $table->getPath($caNotDeleteCatId);
				if ($categories)
				{
					foreach ($categories AS $category)
					{
						$canDeleteCatIdIndex = array_search($category->id, $canDeleteCatIds);
						if ($canDeleteCatIdIndex !== false)
						{
							unset($canDeleteCatIds[$canDeleteCatIdIndex]);
							$canNotDeleteCatIds[] = $category->id;
						}
					}
				}
			}
		}

		
		$canDeleteDocIds = array_unique($canDeleteDocIds);
		if ($canDeleteDocIds)
		{
			foreach ($canDeleteDocIds AS $docId)
			{
				if (!$documentTable->delete($docId))
				{
					$this->setError($documentTable->getError());

					return false;
				}
			}
		}

		
		$canDeleteCatIds = array_unique($canDeleteCatIds);
		if ($canDeleteCatIds)
		{
			foreach ($canDeleteCatIds AS $pk)
			{
				if ($table->load($pk))
				{
					$context = $this->option . '.' . $this->name;
					
					$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));
					if (in_array(false, $result, true))
					{
						$this->setError($table->getError());

						return false;
					}

					
					if (!$table->delete($pk, false))
					{
						$this->setError($table->getError());

						return false;
					}
					else
					{
						
						$query = "DELETE FROM #__judownload_categories_relations WHERE cat_id=" . $pk;
						$db->setQuery($query);
						$db->execute();

						
						$query = "DELETE FROM #__judownload_documents_xref WHERE main=0 AND cat_id=" . $pk;
						$db->setQuery($query);
						$db->execute();

						
						$query = "SELECT mod_id FROM #__judownload_moderators_xref WHERE cat_id = " . $pk;
						$db->setQuery($query);
						$moderatorIds = $db->loadColumn();
						$query        = "DELETE FROM #__judownload_moderators_xref WHERE cat_id=" . $pk;
						$db->setQuery($query);
						$db->execute();
						if ($moderatorIds)
						{
							$modTable = JTable::getInstance("Moderator", "JUDownloadTable");
							foreach ($moderatorIds AS $moderatorId)
							{
								$query = $db->getQuery(true);
								$query->select('COUNT(*)');
								$query->from('#__judownload_moderators AS m');
								$query->join('', '#__judownload_moderators_xref AS mxref ON m.id = mxref.mod_id');
								$query->where('m.id =' . $moderatorId);
								$db->setQuery($query);
								$totalModeratedCats = $db->loadResult();
								if ($totalModeratedCats <= 0)
								{
									$modTable->reset();
									if ($modTable->load(array($moderatorId)))
									{
										$modTable->delete();
									}
								}
							}
						}

						
						$images = json_decode($table->images);
						if (is_object($images))
						{
							if ($images->intro_image)
							{
								$intro_image     = JUDownloadFrontHelper::getDirectory('category_intro_image_directory', 'media/com_judownload/images/category/intro/') . $images->intro_image;
								$ori_intro_image = JUDownloadFrontHelper::getDirectory('category_intro_image_directory', 'media/com_judownload/images/category/intro/') . "original/" . $images->intro_image;
								if (JFile::exists(JPATH_ROOT . "/" . $ori_intro_image))
								{
									JFile::delete(JPATH_ROOT . "/" . $ori_intro_image);
								}
								if (JFile::exists(JPATH_ROOT . "/" . $intro_image))
								{
									JFile::delete(JPATH_ROOT . "/" . $intro_image);
								}
							}
							if ($images->detail_image)
							{
								$detail_image     = JUDownloadFrontHelper::getDirectory('category_detail_image_directory', 'media/com_judownload/images/category/detail/') . $images->detail_image;
								$ori_detail_image = JUDownloadFrontHelper::getDirectory('category_detail_image_directory', 'media/com_judownload/images/category/detail/') . "original/" . $images->detail_image;
								if (JFile::exists(JPATH_ROOT . "/" . $detail_image))
								{
									JFile::delete(JPATH_ROOT . "/" . $detail_image);
								}
								if (JFile::exists(JPATH_ROOT . "/" . $ori_detail_image))
								{
									JFile::delete(JPATH_ROOT . "/" . $ori_detail_image);
								}
							}
						}


						
						if ($table->level == 0 || $table->level == 1)
						{
							$query = "DELETE FROM #__judownload_emails_xref WHERE cat_id = " . $table->id;
							$db->setQuery($query);
							$db->execute();
						}

						
						JUDownloadHelper::deleteLogs('category', $pk);

						
						$query = "DELETE FROM #__judownload_fields_ordering WHERE `item_id` = " . $table->id . " AND `type` = 'category'";
						$db->setQuery($query);
						$db->execute();

						$dispatcher->trigger($this->event_after_delete, array($context, $table));
					}
				}
			}
		}

		if ($canDeleteCatIds)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::plural('COM_JUDOWNLOAD_CATEGORIES_N_ITEMS_DELETED', count($canDeleteCatIds)));
		}

		$canNotDeleteCatIds = array_unique($canNotDeleteCatIds);
		if ($canNotDeleteCatIds)
		{
			JError::raiseWarning(500, JText::plural('COM_JUDOWNLOAD_CATEGORIES_N_ITEMS_NOT_DELETED', count($canNotDeleteCatIds)));
		}

		
		$this->cleanCache();

		return true;
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

	public function getFieldGroup()
	{
		$app    = JFactory::getApplication();
		$cat_id = $app->input->getInt("cat_id", 0);
		if (!$cat_id)
		{
			return false;
		}

		$category = JUDownloadHelper::getCategoryById($cat_id);
		if ($category)
		{
			return $category->fieldgroup_id;
		}
		else
		{
			return "";
		}
	}


	
	public function getModeratorsManageCategory($categoryId)
	{
		if (!$categoryId)
		{
			return null;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('m.*, mx.cat_id, u.username, u.name');
		$query->from('#__judownload_moderators_xref AS mx');
		$query->join('INNER', '#__judownload_moderators AS m ON m.id = mx.mod_id');
		$query->join('INNER', '#__users AS u ON u.id = m.user_id');
		$query->where('mx.cat_id = ' . $categoryId);
		$db->setQuery($query);
		$moderators = $db->loadObjectList();

		return $moderators;
	}

	
	public function getModeratorRightSelectOption($moderator, $right, $canChange)
	{
		$isDisable = $canChange ? "" : "disabled";
		$html      = '<select name="mod' . "[" . $moderator->id . "]" . "[" . $right . "]" . '" ' . $isDisable . ' class="input-mini">';

		if ($moderator->$right == 1)
		{
			$html .= '<option value="0">' . JText::_('JNO') . '</option>';
			$html .= '<option value="1" selected="selected">' . JText::_('JYES') . '</option>';
		}
		else
		{
			$html .= '<option value="0" selected="selected">' . JText::_('JNO') . '</option>';
			$html .= '<option value="1">' . JText::_('JYES') . '</option>';
		}
		$html .= '</select>';

		return $html;
	}

	
	public function loadCategories()
	{
		$app     = JFactory::getApplication();
		$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
		$type    = $app->input->get('type', '');
		$catId   = $app->input->getInt('id', 0);
		$catObj  = JUDownloadHelper::getCategoryById($catId);
		$params  = JUDownloadHelper::getParams($catId);
		$date    = JFactory::getDate();
		$back    = false;
		if ($catId != 0)
		{
			
			if ($type == 'category')
			{
				$back = true;
			}
			
			elseif (($catId == $rootCat->id && $params->get('allow_add_doc_to_root', 0)) || $catId != $rootCat->id)
			{
				$back = true;
			}
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->SELECT('title, id, published, parent_id, publish_up, publish_down');
		$query->FROM('#__judownload_categories');
		$query->WHERE('parent_id = ' . $catId);
		$query->ORDER('lft');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$html = "";
		if ($back)
		{
			$html .= '<option value="' . $catObj->parent_id . '" data-update="noupdate">' . JText::_('COM_JUDOWNLOAD_BACK_TO_PARENT_CATEGORY') . '</option>';
		}

		foreach ($rows AS $row)
		{
			if ($type == 'document')
			{
				$canDoCreate = JUDownloadFrontHelperPermission::canSubmitDocument($row->id);
				if (!$canDoCreate)
				{
					continue;
				}
			}

			if ($row->published != 1 || ($row->publish_up > $date->toSql()) || ($row->publish_down > $db->getNullDate() && $row->publish_down <= $date->toSql()))
			{
				$row->title = "[" . $row->title . "]";
			}
			$html .= "<option value=\"" . $row->id . "\">" . $row->title . "</option>";
		}

		$data['path'] = JUDownloadHelper::generateCategoryPath($catId, 'li');
		$data['html'] = $html;

		return json_encode($data);
	}

	
	public function docChangeCategory()
	{
		$app = JFactory::getApplication();

		if ($app->input->get('action', '') == 'update-maincat')
		{
			$ori_cat_id                   = $app->input->getInt('ori_cat_id', 0);
			$ori_cat                      = JUDownloadHelper::getCategoryById($ori_cat_id);
			$data['ori_field_group_id']   = 0;
			$data['new_field_group_id']   = 0;
			$data['new_field_group_name'] = "";
			$data['path']                 = "";
			if ($ori_cat)
			{
				$data['ori_field_group_id'] = $ori_cat->fieldgroup_id;
			}

			$new_cat_id = $app->input->getInt('new_cat_id', 0);
			$rootCat    = JUDownloadFrontHelperCategory::getRootCategory();
			$params     = JUDownloadHelper::getParams();
			if ($rootCat->id == $new_cat_id && !$params->get('allow_add_doc_to_root', 0))
			{
				return "";
			}

			$new_cat = JUDownloadHelper::getCategoryById($new_cat_id);
			if ($new_cat)
			{
				$db    = JFactory::getDbo();
				$query = "SELECT id, name FROM #__judownload_fields_groups WHERE id = " . $new_cat->fieldgroup_id . " AND published = 1";
				$db->setQuery($query);
				$fieldgroup = $db->loadObject();
				if ($fieldgroup)
				{
					$data['new_field_group_id']   = $fieldgroup->id;
					$data['new_field_group_name'] = $fieldgroup->name;
				}
				$data['path'] = JUDownloadHelper::generateCategoryPath($new_cat_id);
			}

			if ($data['ori_field_group_id'] != $data['new_field_group_id'])
			{
				$data['msg_field_group'] = JText::_('COM_JUDOWNLOAD_CHANGE_MAIN_CATEGORY_CAUSE_CHANGE_FIELD_GROUP_WARNING');
			}

			$documentId            = $app->input->getInt('id', 0);
			$data['message_style'] = JText::_('COM_JUDOWNLOAD_INHERIT');
			if ($documentId)
			{
				$documentObject = JUDownloadHelper::getDocumentById($documentId);
				if ($documentObject->style_id == -1)
				{
					$oldStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($documentId->cat_id);
					$newStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($new_cat->id);
					if ($oldStyleObject->template_id != $newStyleObject->template_id)
					{
						$data['msg_style'] = JText::_('COM_JUDOWNLOAD_CHANGE_MAIN_CATEGORY_CAUSE_CHANGE_TEMPLATE_WARNING');
					}
				}
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($new_cat->id);
				$data['message_style']  = JText::_('COM_JUDOWNLOAD_INHERIT') . ' (' . $newTemplateStyleObject->title . ' [' . $newTemplateStyleObject->template_title . ' ]' . ')';
			}

			return json_encode($data);
		}
		elseif ($app->input->getInt('action', '') == 'insert_secondary_cat')
		{
			$cat_id_str = $app->input->get('secondary_cat_id', '', 'string');
			$html       = '';
			if (!empty($cat_id_str))
			{
				$cat_id_arr = explode(",", $cat_id_str);
				foreach ($cat_id_arr AS $key => $cat_id)
				{
					$html .= "<li id=\"cat-" . $cat_id . "\"><a class=\"drag-icon\"></a><span>" . JUDownloadHelper::generateCategoryPath($cat_id) . "</span><a href=\"#\" onclick=\"return false\" class=\"remove-secondary-cat\" ><i class=\"icon-minus fa fa-minus-circle\"></i> " . JText::_('COM_JUDOWNLOAD_REMOVE') . "</a></li>";
				}
			}

			return $html;
		}
	}
}