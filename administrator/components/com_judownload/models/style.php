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

class JUDownloadModelStyle extends JModelAdmin
{

	
	public function getTable($type = 'Style', $prefix = 'JUDownloadTable', $config = array())
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
		$style_xml_path = JPath::find(JForm::addFormPath(), strtolower('style') . '.xml');
		$style_xml      = JFactory::getXML($style_xml_path, true);

		
		if (isset($data->template_id) && $data->template_id)
		{
			$styleObject = JUDownloadFrontHelperTemplate::getTemplateStyleObject($data->id);
			$folder      = $styleObject->folder;
			$folder      = strtolower(str_replace(' ', '', $folder));

			if ($folder)
			{
				$xml_file = JPath::clean(JPATH_SITE . "/components/com_judownload/templates/" . $folder . "/" . $folder . '.xml');
				if (JFile::exists($xml_file))
				{
					$xml = JFactory::getXML($xml_file);

					if ($xml->config)
					{
						foreach ($xml->config->children() AS $child)
						{
							$style_params_xpath = $style_xml->xpath('//fieldset[@name="params"]');
							JUDownloadHelper::appendXML($style_params_xpath[0], $child);
						}

						
						if ($xml->languages->count())
						{
							foreach ($xml->languages->children() AS $language)
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

		
		$form = $this->loadForm('com_judownload.style', $style_xml->asXML(), array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		$app = JFactory::getApplication();
		$id  = $app->input->get('id', 0);
		if ($id)
		{
			
			$form->setFieldAttribute('template_id', 'disabled', 'true');

			
			
			$form->setFieldAttribute('template_id', 'filter', 'unset');

			if (isset($data->home) && $data->home == 1)
			{
				
				$form->setFieldAttribute('home', 'disabled', 'true');
			}
		}

		return $form;
	}


	
	protected function loadFormData()
	{
		
		$data = JFactory::getApplication()->getUserState('com_judownload.edit.style.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}

		if (JUDownloadHelper::isJoomla3x())
		{
			$this->preprocessData('com_judownload.style', $data);
		}

		return $data;
	}

	
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		$item->xml = null;

		if ($item->id)
		{
			$styleObject = JUDownloadFrontHelperTemplate::getTemplateStyleObject($item->id);
			$folder      = trim($styleObject->folder);
			$folder      = strtolower(str_replace(' ', '', $folder));
			if ($folder)
			{
				
				$xml_file = JPath::clean(JPATH_SITE . "/components/com_judownload/templates/" . $folder . "/" . $folder . '.xml');
				if (file_exists($xml_file))
				{
					$item->xml = JFactory::getXML($xml_file);
				}
			}
		}

		return $item;
	}

	
	public function duplicate(&$pks)
	{
		$user = JFactory::getUser();

		
		if (!$user->authorise('core.create', 'com_judownload'))
		{
			throw new Exception(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();

		foreach ($pks AS $pk)
		{
			if ($table->load($pk, true))
			{
				
				$table->id = 0;

				
				$table->home = 0;

				
				$table->default = 0;

				$table->setLocation($table->parent_id, 'last-child');

				
				$table->title = $this->generateNewTitleForStyle($table->title);

				if (!$table->check() || !$table->store())
				{
					throw new Exception($table->getError());
				}
			}
			else
			{
				throw new Exception($table->getError());
			}
		}

		
		$this->cleanCache();

		return true;
	}


	
	protected function  generateNewTitleForStyle($title)
	{
		
		$table = $this->getTable();
		while ($table->load(array('title' => $title)))
		{
			$title = JString::increment($title);
		}

		return $title;
	}


	
	public function save($data)
	{
		$app                     = JFactory::getApplication();
		$categoryArrayAddToStyle = $data['categories'] ? $data['categories'] : array();
		unset($data['categories']);

		
		$dispatcher = JDispatcher::getInstance();
		$table      = $this->getTable();
		$key        = $table->getKeyName();
		$pk         = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew      = true;
		$db         = JFactory::getDbo();

		$defaultTemplateStyleObject = JUDownloadFrontHelperTemplate::getDefaultTemplateStyle();

		
		JPluginHelper::importPlugin('content');

		
		try
		{
			
			if ($pk > 0)
			{
				$table->load($pk);

				
				if ($table->home == 1)
				{
					$data["home"] = 1;
				}

				$templateId = $table->template_id;

				$isNew = false;
			}
			else
			{
				$templateId = $data["template_id"];
			}

			if ($data["home"] == 1 && $table->home != 1)
			{
				if ($templateId != $defaultTemplateStyleObject->template_id)
				{
					if ($data['changeTemplateStyleAction'] == 1)
					{
						
						JUDownloadFrontHelperTemplate::updateStyleIdForCatDocUsingDefaultStyle($defaultTemplateStyleObject->id);
						$app->enqueueMessage(JText::sprintf('COM_JUDOWNLOAD_DEFAULT_TEMPLATE_STYLE_HAS_BEEN_CHANGED_TO_X', $defaultTemplateStyleObject->title), 'Notice');
					}
					else
					{
						
						JUDownloadFrontHelperTemplate::removeTemplateParamsOfCatDocUsingDefaultStyle();
					}
				}
			}

			if ($data['home'])
			{
				if ($data['home'] != 1)
				{
					if ($defaultTemplateStyleObject->template_id != $templateId)
					{
						$data['home'] = 0;
					}
				}
			}

			
			$query = $db->getQuery(true);
			$query->select("tpl.*");
			$query->select("plg.title,plg.folder");
			$query->from("#__judownload_templates AS tpl");
			$query->join("", "#__judownload_plugins AS plg ON plg.id = tpl.plugin_id");
			$query->where("tpl.id = " . $templateId);
			$db->setQuery($query);
			$templateObject = $db->loadObject();
			if (!is_object($templateObject))
			{
				$this->setError(JText::_("COM_JUDOWNLOAD_TEMPLATE_DOES_NOT_EXIST"));

				return false;
			}

			
			$query = $db->getQuery(true);
			$query->select("id");
			$query->from("#__judownload_template_styles");
			$query->where("template_id = " . $templateObject->parent_id);
			$db->setQuery($query);
			$styleParentObjectList = $db->loadColumn();

			$parentStyleId = (int) $data["parent_id"];
			if (is_array($styleParentObjectList) && !empty($styleParentObjectList))
			{
				if (!in_array($parentStyleId, $styleParentObjectList))
				{
					$this->setError(JText::_("COM_JUDOWNLOAD_INVALID_PARENT_STYLE"));

					return false;
				}
			}
			else
			{
				if ($templateObject->parent_id == 1)
				{
					$this->setError(JText::_("COM_JUDOWNLOAD_PARENT_STYLE_DOES_NOT_EXIST_YOU_NEED_TO_CREATE_ROOT_STYLE"));

					return false;
				}
				$query = $db->getQuery(true);
				$query->select("tpl.*");
				$query->select("plg.title,plg.folder");
				$query->from("#__judownload_templates AS tpl");
				$query->join("", "#__judownload_plugins AS plg ON plg.id = tpl.plugin_id");
				$query->where("tpl.id = " . $templateObject->parent_id);
				$db->setQuery($query);
				$templateParentObject = $db->loadObject();

				$this->setError(JText::_("COM_JUDOWNLOAD_PARENT_STYLE_DOES_NOT_EXIST") . $templateParentObject->title);

				return false;
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

			
			if ($table->home)
			{
				
				$query = $db->getQuery(true);
				$query->update("#__judownload_template_styles");
				$query->set("home = 0");
				$query->where("home = " . $db->quote($table->home));
				$query->where("id != " . $table->id);
				$db->setQuery($query);
				$db->execute();
			}

			
			$query = $db->getQuery(true);
			$query->select('id');
			$query->from('#__judownload_categories');
			$query->where('style_id =' . $table->id);
			$db->setQuery($query);
			$categoryArrayAssignedToStyle = $db->loadColumn();

			
			$categoryArrayRemovedStyle = array_diff($categoryArrayAssignedToStyle, $categoryArrayAddToStyle);
			if (!empty($categoryArrayRemovedStyle))
			{
				foreach ($categoryArrayRemovedStyle AS $categoryIdRemovedStyle)
				{
					
					$query = $db->getQuery(true);
					$query->update('#__judownload_categories');
					$query->set('style_id = -2');
					if ($defaultTemplateStyleObject->template_id != $table->template_id)
					{
						$query->set('template_params = ""');
					}
					$query->where('parent_id = 0');
					$query->where('id = ' . $categoryIdRemovedStyle);
					$db->setQuery($query);
					$db->execute();

					
					$query = $db->getQuery(true);
					$query->update('#__judownload_categories');
					$query->set('style_id = -1');
					$query->where('parent_id != 0');
					$query->where('id = ' . $categoryIdRemovedStyle);
					$db->setQuery($query);
					$db->execute();
				}

				foreach ($categoryArrayRemovedStyle AS $categoryIdRemovedStyle)
				{
					$templateStyleOfCategoryObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($categoryIdRemovedStyle);
					if ($templateStyleOfCategoryObject->template_id != $table->template_id)
					{
						$query = $db->getQuery(true);
						$query->update('#__judownload_categories');
						$query->set('template_params = ""');
						$query->where('style_id = -1');
						$query->where('parent_id != 0');
						$query->where('id = ' . $categoryIdRemovedStyle);
						$db->setQuery($query);
						$db->execute();

						JUDownloadFrontHelperTemplate::removeTemplateParamsOfInheritedStyleCatDoc($categoryIdRemovedStyle);
					}
				}
			}

			
			if (!empty($categoryArrayAddToStyle))
			{
				foreach ($categoryArrayAddToStyle AS $categoryIdAddToStyle)
				{
					$query = $db->getQuery(true);
					$query->update('#__judownload_categories');
					$query->set('style_id = ' . $table->id);
					$query->where('style_id != ' . $table->id);
					$query->where('id = ' . $categoryIdAddToStyle);
					$db->setQuery($query);
					$db->execute();

					JUDownloadFrontHelperTemplate::removeTemplateParamsOfInheritedStyleCatDoc($categoryIdAddToStyle);
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
		
		if (($table->id) == 0)
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


	public function checkChangeHomeStyle($data)
	{
		$result = array('status' => 0, 'message' => '');

		if ($data['home'] == 1)
		{
			$styleId = $data['id'];
			if ($styleId > 0)
			{
				$styleObject = JUDownloadFrontHelperTemplate::getTemplateStyleObject($styleId);
				$templateId  = $styleObject->template_id;
			}
			else
			{
				$templateId = $data['template_id'];
			}

			$defaultStyle = JUDownloadFrontHelperTemplate::getDefaultTemplateStyle();
			if ($defaultStyle->template_id != $templateId)
			{
				$result['status']  = 1;
				$result['message'] = JText::_('COM_JUDOWNLOAD_CHANGE_DEFAULT_TEMPLATE_STYLE_MESSAGE');
			}
		}

		return $result;
	}


	
	public function setDefault($cid)
	{
		$app              = JFactory::getApplication();
		$jInput           = $app->input;
		$keepStyleDefault = $jInput->post->getInt('changeTemplateStyleAction', 1);

		$db = JFactory::getDbo();

		$styleObject                = JUDownloadFrontHelperTemplate::getTemplateStyleObject($cid);
		$defaultTemplateStyleObject = JUDownloadFrontHelperTemplate::getDefaultTemplateStyle();

		if ($styleObject->template_id != $defaultTemplateStyleObject->template_id)
		{
			if ($keepStyleDefault == 1)
			{
				
				JUDownloadFrontHelperTemplate::updateStyleIdForCatDocUsingDefaultStyle($defaultTemplateStyleObject->id);
			}
			else
			{
				
				JUDownloadFrontHelperTemplate::removeTemplateParamsOfCatDocUsingDefaultStyle();
			}
		}

		$query = $db->getQuery(true);
		$query->update('#__judownload_template_styles');
		$query->set('home = 0');
		$query->where('home = 1');
		$db->setQuery($query);
		$db->execute();

		$query = $db->getQuery(true);
		$query->update('#__judownload_template_styles');
		$query->set('home = 1');
		$query->where('id = ' . (int) $cid);
		$db->setQuery($query);
		$db->execute();
	}

	
	public function unsetDefault($id = 0)
	{
		$user  = JFactory::getUser();
		$db    = $this->getDbo();
		$table = $this->getTable();

		
		if (!$user->authorise('core.edit.state', 'com_judownload'))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}

		$styleId = (int) $id;

		$table->load($styleId);

		if ($table->home == '1')
		{
			throw new Exception(JText::_('COM_JUDOWNLOAD_CAN_NOT_UNSET_DEFAULT_STYLE'));
		}

		
		$query = $db->getQuery(true);
		$query->update("#__judownload_template_styles");
		$query->set("home = 0");
		$query->where("id = " . $styleId);
		$db->setQuery($query);
		$db->execute();

		
		$this->cleanCache();

		return true;
	}

}