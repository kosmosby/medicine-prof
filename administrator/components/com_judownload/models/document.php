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


class JUDownloadModelDocument extends JModelAdmin
{
	protected $cache = array();
	
	protected $pluginsCanEdit = array();

	
	public function getTable($type = 'Document', $prefix = 'JUDownloadTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}


	public function getFormDefault($data = array(), $loadData = true)
	{
		
		JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_judownload/models/forms');
		JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_judownload/models/fields');
		$document_xml_path = JPath::find(JForm::addFormPath(), 'document.xml');
		$document_xml      = JFactory::getXML($document_xml_path, true);

		$form = new SimpleXMLElement($document_xml->asXML());

		return $form;
	}

	
	public function getForm($data = array(), $loadData = true)
	{
		$storeId = md5(__METHOD__ . "::" . serialize($data) . "::" . (int) $loadData);
		if (!isset($this->cache[$storeId]))
		{
			
			if ($data)
			{
				$data = (object) $data;
			}
			else
			{
				$data = $this->getItem();
			}

			
			JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_judownload/models/forms');
			JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_judownload/models/fields');
			$document_xml_path = JPath::find(JForm::addFormPath(), 'document.xml');
			$document_xml      = JFactory::getXML($document_xml_path, true);

			if ($data->id)
			{
				$templateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfDocument($data->id);
				$templateFolder      = trim($templateStyleObject->folder);
				if ($templateFolder)
				{
					$template_path = JPATH_SITE . "/components/com_judownload/templates/" . $templateFolder . "/" . $templateFolder . '.xml';
					if (JFile::exists($template_path))
					{
						$template_xml = JFactory::getXML($template_path, true);
						if ($template_xml->doc_config)
						{
							foreach ($template_xml->doc_config->children() AS $child)
							{
								$template_params_xpath = $document_xml->xpath('//fieldset[@name="template_params"]');
								JUDownloadHelper::appendXML($template_params_xpath[0], $child);
							}
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

			
			$globalconfig_path = JPath::find(JForm::addFormPath(), 'globalconfig.xml');
			$globalconfig_xml  = JFactory::getXML($globalconfig_path, true);

			$display_params_fields_xpath = $globalconfig_xml->xpath('//fields[@name="display_params"]/fields[@name="doc"]');
			$display_params_xml          = $display_params_fields_xpath[0];
			if ($display_params_xml)
			{
				foreach ($display_params_xml->children() AS $child)
				{
					$display_params_xpath = $document_xml->xpath('//fields[@name="display_params"]');
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

						
						if ($xml->doc_config)
						{
							$ruleXml             = new SimpleXMLElement('<fields name="' . $folder . '"></fields>');
							$plugin_params_xpath = $document_xml->xpath('//fields[@name="plugin_params"]');
							JUDownloadHelper::appendXML($plugin_params_xpath[0], $ruleXml);
							$total_fieldsets = 0;
							foreach ($xml->doc_config->children() AS $child)
							{
								$total_fieldsets++;
								$child->addAttribute('plugin_name', $folder);
								$jplugin_xpath = $document_xml->xpath('//fields[@name="' . $folder . '"]');
								JUDownloadHelper::appendXML($jplugin_xpath[0], $child);
							}

							if ($total_fieldsets)
							{
								$pluginLabel                   = $xml->doc_config->attributes()->label ? $xml->doc_config->attributes()->label : $element->name;
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

			$form = $this->loadForm('com_judownload.document', $document_xml->asXML(), array('control' => 'jform', 'load_data' => $loadData));

			
			if (!$loadData)
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select("field.field_name");
				$query->from("#__judownload_fields AS field");
				$query->join("LEFT", "#__judownload_plugins AS plg ON field.plugin_id = plg.id");
				$query->where("field.group_id = 1 AND field.field_name != ''");
				$db->setQuery($query);
				$fieldNames = $db->loadColumn();
				foreach ($fieldNames AS $fieldName)
				{
					$form->removeField($fieldName);
				}
			}

			if (empty($form))
			{
				$this->cache[$storeId] = false;
			}

			$this->cache[$storeId] = $form;
		}

		return $this->cache[$storeId];
	}

	
	public function getScript()
	{
		return 'administrator/components/com_judownload/models/forms/document.js';
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
			

			
			$table->modified = $date->toSql();

			
			$table->modified_by = $user->id;
		}

		
		$table->access = 1;

		
		$table->language = '*';

		
	}

	
	protected function canDelete($record)
	{
		$user = JFactory::getUser();
		$app  = JFactory::getApplication();
		
		if ($app->isSite())
		{
			return JUDownloadFrontHelperPermission::canDeleteDocument($record->id);
		}

		$canDelete = $user->authorise('judl.document.delete', $this->option . '.document.' . (int) $record->id);
		if (!$canDelete)
		{
			if ($user->id)
			{
				if ($user->id == $record->created_by)
				{
					$canDeleteOwn = $user->authorise('judl.document.delete.own', $this->option . '.document.' . (int) $record->id);
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
		$app = JFactory::getApplication();
		
		if ($app->isSite())
		{
			return JUDownloadFrontHelperPermission::canEditStateDocument($record);
		}

		
		return true;
	}

	
	public function getItem($pk = null)
	{
		$storeId = md5(__METHOD__ . "::" . $pk);
		if (!isset($this->cache[$storeId]))
		{
			$item = parent::getItem($pk);

			$item->cat_id = 0;

			if ($item->id)
			{
				$template_params = new JRegistry;
				$template_params->loadString($item->template_params);
				$item->template_params = $template_params->toArray();

				$plugin_params = new JRegistry;
				$plugin_params->loadString($item->plugin_params);
				$item->plugin_params = $plugin_params->toArray();

				$item->description = trim($item->fulltext) != '' ? $item->introtext . "<hr id=\"system-readmore\" />" . $item->fulltext : $item->introtext;

				$docObj = JUDownloadHelper::getDocumentById($item->id);
				if ($docObj)
				{
					$item->cat_id = $docObj->cat_id;
				}

				
				$registry = new JRegistry;
				$registry->loadString($item->metadata);
				$item->metadata = $registry->toArray();
			}

			$this->cache[$storeId] = $item;
		}

		return $this->cache[$storeId];
	}

	
	protected function loadFormData()
	{
		
		$data = JFactory::getApplication()->getUserState('com_judownload.edit.document.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		if (JUDownloadHelper::isJoomla3x())
		{
			$this->preprocessData('com_judownload.document', $data);
		}

		return $data;
	}

	public function canUpload($filePath, $trimPath = true)
	{
		$params = JUDownloadHelper::getParams();

		$legal_upload_extensions = $params->get('legal_upload_extensions', 'bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,zip,rar');

		$max_upload_file_size = (int) ($params->get('max_upload_file_size', 10) * 1024 * 1024);

		$check_mime = $params->get('check_mime_uploaded_file', 0);

		$legal_mime = $params->get('legal_mime_types', 'image/jpeg,image/gif,image/png,image/bmp,application/x-shockwave-flash,application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/zip,application/zip,application/x-rar-compressed');

		$ignored_extensions = $params->get('ignored_extensions', '');

		$image_extensions = $params->get('image_extensions', 'bmp,gif,jpg,png');

		
		$file             = array();
		$file['tmp_name'] = $filePath;
		$file['name']     = basename($file['tmp_name']);
		if ($trimPath)
		{
			$file['name'] = substr($file['name'], 0, -5);
		}

		$file['size'] = filesize($file['tmp_name']);
		$error        = array();

		return JUDownloadFrontHelperPermission::canUpload($file, $error, $legal_upload_extensions, $max_upload_file_size, $check_mime, $legal_mime, $ignored_extensions, $image_extensions);
	}

	
	public function save($dataInput)
	{
		$app = JFactory::getApplication();

		
		$fieldsData = $dataInput['fieldsData'];
		$data       = $dataInput['data'];
		

		
		$dispatcher = JDispatcher::getInstance();
		$table      = $this->getTable();
		$key        = $table->getKeyName();
		$pk         = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew      = true;

		
		$categoriesField = new JUDownloadFieldCore_categories();
		$newMainCatId    = $fieldsData[$categoriesField->id]['main'];

		
		JPluginHelper::importPlugin('content');

		$tableBeforeSave = null;

		
		try
		{
			
			if ($pk > 0)
			{
				$table->load($pk);

				
				$tableBeforeSave = clone $table;

				$isNew = false;
			}

			
			$saveDocumentStoreCategoryField = $this->saveDocumentStoreCategoryField($isNew, $pk, $fieldsData, $newMainCatId);
			if (!$saveDocumentStoreCategoryField)
			{
				return false;
			}

			
			$this->saveDocumentPrepareSave($data, $table, $isNew);

			
			$this->saveDocumentPrepareTemplateParams($data, $pk, $isNew, $newMainCatId);

			
			$this->saveDocumentPreparePluginParams($data, $pk, $isNew);

			
			$data['cat_id'] = $fieldsData[$categoriesField->id]['main'];
			
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

			
			$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, $table, $isNew));
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

			
			$this->saveDocumentVersions($dataInput, $table, $fieldsData, $isNew, $newMainCatId, $tableBeforeSave);

			
			$this->saveDocumentFiles($dataInput, $table);

			
			$this->saveDocumentChangeLogs($dataInput, $table);

			
			$this->saveDocumentRelated($dataInput, $table);

			
			$this->saveDocumentFields($fieldsData, $table, $isNew);

			if (!$isNew)
			{
				$publishedField = new JUDownloadFieldCore_published();
				if (isset($fieldsData[$publishedField->id]) && $fieldsData[$publishedField->id] != $tableBeforeSave->published)
				{
					$context = $this->option . '.' . $this->name;
					$pks     = array($table->id);
					$value   = $fieldsData[$publishedField->id];
					
					$dispatcher->trigger($this->event_change_state, array($context, $pks, $value));
				}

				$approvedField = new JUDownloadFieldCore_approved();
				if (isset($fieldsData[$approvedField->id]) && $fieldsData[$approvedField->id] != $tableBeforeSave->approved)
				{
					$context = $this->option . '.' . $this->name;
					$pks     = array($table->id);
					$value   = $fieldsData[$approvedField->id];
					
					$dispatcher->trigger('onContentApprove', array($context, $pks, $value));
				}
			}

			
			$this->cleanCache();

			
			$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, $table, $isNew));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$pkName = $table->getKeyName();

		if ($app->isAdmin())
		{
			if (isset($table->$pkName))
			{
				$this->setState($this->getName() . '.id', $table->$pkName);
			}
			$this->setState($this->getName() . '.new', $isNew);
		}
		else
		{
			if (isset($table->$pkName))
			{
				$this->setState('document.id', $table->$pkName);
			}
			$this->setState('document.new', $isNew);
		}

		
		$this->saveDocumentAddLog($table, $isNew);

		
		$this->saveDocumentSendEmail($table, $isNew, $fieldsData, $tableBeforeSave);

		return true;
	}

	
	public function saveDocumentStoreCategoryField($isNew, $pk, $fieldsData, $newMainCatId)
	{
		
		if (!$isNew)
		{
			$categoriesField = new JUDownloadFieldCore_categories(null, $pk);

			if (($this->getDocumentSubmitType($pk) == 'submit' && $categoriesField->canSubmit())
				|| ($this->getDocumentSubmitType($pk) == 'edit' && $categoriesField->canEdit())
			)
			{
				$categoriesField->is_new = $isNew;
				$categoriesFieldValue    = $fieldsData[$categoriesField->id];
				$saveFieldCategory       = $categoriesField->storeValue($categoriesFieldValue);
				if ($saveFieldCategory)
				{
					$documentObject = JUDownloadHelper::getDocumentById($pk);
					$mainCatIdDB    = $documentObject->cat_id;

					
					if ($mainCatIdDB != $newMainCatId)
					{
						$fieldGroupIdDB = JUDownloadHelper::getCategoryById($mainCatIdDB)->fieldgroup_id;
						$fieldGroupId   = JUDownloadHelper::getCategoryById($newMainCatId)->fieldgroup_id;

						if ($fieldGroupId != $fieldGroupIdDB)
						{
							JUDownloadHelper::deleteFieldValuesOfDocument($pk);
						}
					}
				}
				else
				{
					$this->setError('COM_JUDOWNLOAD_FAIL_TO_SAVE_CATEGORY_FIELD');

					return false;
				}
			}
		}

		return true;
	}

	
	public function saveDocumentPrepareTemplateParams(&$data, $pk, $isNew, $newMainCatId)
	{
		$removeTemplateParams = false;

		if (!$isNew)
		{
			

			
			$oldTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfDocument($pk);
			$styleId                = $data['style_id'];
			if ($styleId == -2)
			{
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getDefaultTemplateStyle();
			}
			elseif ($styleId == -1)
			{
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($newMainCatId);

			}
			else
			{
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleObject($styleId);
			}

			if ($oldTemplateStyleObject->template_id != $newTemplateStyleObject->template_id)
			{
				$data['template_params'] = "";
				$removeTemplateParams    = true;
			}
		}
		


		
		if (!$removeTemplateParams && isset($data['template_params']) && is_array($data['template_params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($data['template_params']);
			$data['template_params'] = $registry->toString();
		}
	}

	
	public function saveDocumentPreparePluginParams(&$data, $pk, $isNew)
	{
		$db = JFactory::getDbo();

		
		$db_plugin_params = array();
		if (!isset($data['plugin_params']) || !is_array($data['plugin_params']))
		{
			$data['plugin_params'] = array();
		}

		if (!$isNew)
		{
			$db->setQuery("SELECT plugin_params FROM #__judownload_documents WHERE id = " . $pk);
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
		$registry      = new JRegistry;
		$registry->loadArray($plugin_params);
		$data['plugin_params'] = $registry->toString();
	}

	
	public static function saveDocumentPrepareSave(&$data, $table, $isNew)
	{
		$app = JFactory::getApplication();

		

		if ($app->isAdmin())
		{
			

			
			if ($isNew)
			{
				$data['approved'] = 1;
				
				$data['published'] = 1;
			}
			else
			{
				$data['approved']  = $table->approved;
				$data['published'] = $table->published;
			}
		}
		else
		{
			
			if (isset($data['approved']))
			{
				$data['approved'] = $data['approved'];
			}

			if (isset($data['published']))
			{
				$data['published'] = $data['published'];
			}
		}

		$data['style_id'] = isset($data['style_id']) ? $data['style_id'] : -1;
	}

	
	public function saveDocumentVersions($dataInput, $table, $fieldsData, $isNew, $newMainCatId, $tableBeforeSave = null)
	{
		$versions = $dataInput['versions'];
		$docId    = $table->id;
		$app      = JFactory::getApplication();
		$params   = JUDownloadHelper::getParams($newMainCatId);
		$db       = JFactory::getDbo();

		$file_directory     = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/");
		$file_directory_tmp = $file_directory . "tmp/";
		if (!is_writable($file_directory_tmp))
		{
			$file_directory_tmp = sys_get_temp_dir() . "/plupload/";
		}

		if (JUDLPROVERSION && !$isNew && $params->get('store_old_file_versions', 1))
		{
			
			
			$versionField = new JUDownloadFieldCore_version();
			if (version_compare($tableBeforeSave->version, $fieldsData[$versionField->id]) != 0)
			{
				
				$query = "SELECT * FROM #__judownload_files WHERE doc_id = " . $docId;
				$db->setQuery($query);
				$fileObjectList = $db->loadObjectList();
				foreach ($fileObjectList AS $fileObj)
				{
					
					$query = $db->getQuery(true);
					$query->select('*')
						->from('#__judownload_versions')
						->where('file_id = ' . $fileObj->id)
						->where('date < ' . $db->quote($table->updated))
						->where('file_path != ""')
						->order('date DESC, version DESC');
					$db->setQuery($query, 0, 1);
					$nearestVersion = $db->loadObject();

					
					$storeFile = false;
					if (!$nearestVersion)
					{
						$storeFile = true;
					}
					else
					{
						$currentVersionFile = $file_directory . $docId . '/' . $fileObj->file_name;
						$nearestVersionFile = $file_directory . $docId . '/' . $nearestVersion->file_path;
						
						
						if (hash_file('md5', $currentVersionFile) != hash_file('md5', $nearestVersionFile))
						{
							$storeFile = true;
						}
					}

					if ($storeFile)
					{
						$versionFolder = JFolder::makeSafe($table->version);
						if (!$versionFolder)
						{
							$versionFolder = 'versions';
						}

						if (!JFolder::exists($file_directory . $docId . "/" . $versionFolder . "/"))
						{
							$file_index = $file_directory . $docId . "/" . $versionFolder . "/index.html";
							$buffer     = "<!DOCTYPE html><title></title>";
							JFile::write($file_index, $buffer);
						}

						
						$src      = $file_directory . $docId . "/" . $fileObj->file_name;
						$fileName = $fileObj->file_name;
						$dest     = $file_directory . $docId . "/" . $versionFolder . "/" . $fileName;
						
						while (JFile::exists($dest))
						{
							$fileName = md5($fileObj->file_name . JUDownloadHelper::generateRandomString(10)) . "." . JFile::getExt($fileObj->file_name);
							$dest     = $file_directory . $docId . "/" . $versionFolder . "/" . $fileName;
						}
						JFile::copy($src, $dest);

						
						$versionTable                = JTable::getInstance("Version", "JUDownloadTable");
						$version_downloads_check_arr = array('doc_id' => $docId, 'file_id' => $fileObj->id, 'version' => $table->version);
						if ($versionTable->load($version_downloads_check_arr))
						{
							$versionTable->date           = intval($table->updated) ? $table->updated : $table->created;
							$versionTable->size           = $fileObj->size;
							$versionTable->md5_checksum   = $fileObj->md5_checksum;
							$versionTable->crc32_checksum = $fileObj->crc32_checksum;
							$versionTable->file_path      = $versionFolder . "/" . $fileName;
							$versionTable->store();
						}
						else
						{
							$versionTable->bind($version_downloads_check_arr);
							$versionTable->id             = 0;
							$versionTable->date           = intval($table->updated) ? $table->updated : $table->created;
							$versionTable->size           = $fileObj->size;
							$versionTable->md5_checksum   = $fileObj->md5_checksum;
							$versionTable->crc32_checksum = $fileObj->crc32_checksum;
							$versionTable->file_path      = $versionFolder . "/" . $fileName;
							$versionTable->store();
						}
					}
				}
			}
		}

		
		if (JUDLPROVERSION && $app->isAdmin())
		{
			
			$versionTable = JTable::getInstance("Version", "JUDownloadTable");
			foreach ($versions AS $versionKey => $version)
			{
				if ($versionTable->load($versionKey))
				{
					if ($version['remove'])
					{
						
						$srcFile = $file_directory . $versionTable->doc_id . '/' . $versionTable->file_path;
						JFile::delete($srcFile);

						
						$versionTable->delete();

						
						if ($version['replace'])
						{
							$filePath = $file_directory_tmp . $version['replace'];
							if (JFile::exists($filePath))
							{
								JFile::delete($filePath);
							}
						}
					}
					elseif ($version['replace'])
					{
						
						$oldFile = $file_directory . $versionTable->doc_id . '/' . $versionTable->file_path;
						JFile::delete($oldFile);

						$versionFolder = JFolder::makeSafe($versionTable->version);
						if (!$versionFolder)
						{
							$versionFolder = 'versions';
						}

						if (!JFolder::exists($file_directory . $versionTable->doc_id . "/" . $versionFolder . "/"))
						{
							$file_index = $file_directory . $versionTable->doc_id . "/" . $versionFolder . "/index.html";
							$buffer     = "<!DOCTYPE html><title></title>";
							JFile::write($file_index, $buffer);
						}

						$tmpFilePath = $file_directory_tmp . $version['replace'];

						$versionTable->size = filesize($tmpFilePath);

						if (($params->get('auto_generate_md5_checksum', 2) == 2 && $versionTable->size <= $params->get("max_filesize_auto_generate_checksum", 100) * 1024 * 1024) || $params->get('auto_generate_md5_checksum', 2) == 1)
						{
							$versionTable->md5_checksum = hash_file('md5', $tmpFilePath);
						}

						if (($params->get('auto_generate_crc32_checksum', 2) == 2 && $versionTable->size <= $params->get("max_filesize_auto_generate_checksum", 100) * 1024 * 1024) || $params->get('auto_generate_crc32_checksum', 2) == 1)
						{
							$versionTable->crc32_checksum = hash_file('crc32b', $tmpFilePath);
						}

						
						do
						{
							$version['replace'] = md5($version['replace'] . JUDownloadHelper::generateRandomString(10)) . "." . JFile::getExt($version['replace']);
							$newFilePath        = $versionFolder . "/" . $version['replace'];
							$dest               = $file_directory . $versionTable->doc_id . "/" . $newFilePath;
						} while (JFile::exists($dest));

						$versionTable->file_path = $newFilePath;
						$versionTable->store();

						JFile::move($tmpFilePath, $dest);
					}
				}
			}
			
		}
	}

	
	public function saveDocumentFiles($dataInput, $table)
	{
		$files = $dataInput['files'];

		if ($files)
		{
			$docId = $table->id;
			$db    = JFactory::getDbo();
			$user  = JFactory::getUser();
			$date  = JFactory::getDate();

			$file_directory     = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/");
			$file_directory_tmp = $file_directory . "tmp/";
			if (!is_writable($file_directory_tmp))
			{
				$file_directory_tmp = sys_get_temp_dir() . "/plupload/";
			}

			$fileTable = JTable::getInstance("File", "JUDownloadTable");

			if (!JFolder::exists($file_directory . $docId . "/"))
			{
				$file_index = $file_directory . $docId . "/index.html";
				$buffer     = "<!DOCTYPE html><title></title>";
				JFile::write($file_index, $buffer);
			}

			$ordering = 0;
			foreach ($files AS $file)
			{
				$fileTable->reset();
				
				if ($file['id'] && $fileTable->load($file['id']))
				{
					
					if ($file['remove'])
					{
						$srcFile = $file_directory . $fileTable->doc_id . '/' . $fileTable->file_name;
						JFile::delete($srcFile);

						
						$query = "DELETE FROM #__judownload_versions WHERE doc_id = " . $fileTable->doc_id . " AND file_id = " . $fileTable->id;
						$db->setQuery($query);
						$db->execute();

						$fileTable->delete();
					}
					
					else
					{
						$ordering++;
						
						$_file                = array();
						$_file['rename']      = $file['rename'];
						$_file['title']       = $file['title'];
						$_file['description'] = $file['description'];
						
						if ($user->authorise('core.admin', 'com_judownload'))
						{
							$_file['downloads'] = $file['downloads'];
						}
						$_file['published'] = $file['published'];
						$_file['ordering']  = $ordering;

						
						if ($file['replace'])
						{
							
							$filePath = $file_directory_tmp . $file['replace'];
							if (JFile::exists($filePath))
							{
								$_file['file_name'] = $file['replace'];
								$_file['mime_type'] = $file['mime_type'];
								$_file['modified']  = $date->toSql();
								$this->addFileInfo($_file, $file_directory_tmp, $file_directory);

								$oldFile = $file_directory . $docId . "/" . $fileTable->file_name;
								if (JFile::exists($oldFile))
								{
									JFile::delete($oldFile);
								}

								$dest = $file_directory . $docId . "/" . $_file['file_name'];
								JFile::move($filePath, $dest);
							}
						}
						$fileTable->bind($_file);
						$fileTable->store();
					}
				}
				
				else
				{
					if (!$file['remove'])
					{
						$filePath = $file_directory_tmp . $file['file_name'];
						unset($file['remove']);
						$file['id']      = 0;
						$file['doc_id']  = $docId;
						$file['created'] = $date->toSql();
						$ordering++;
						$file['ordering'] = $ordering;
						
						if (!$user->authorise('core.admin', 'com_judownload'))
						{
							$file['downloads'] = 0;
						}

						$this->addFileInfo($file, $file_directory_tmp, $file_directory);

						$fileTable->bind($file);
						$fileTable->store();
						
						if (JFile::exists($filePath))
						{
							$dest = $file_directory . $docId . "/" . $file['file_name'];
							JFile::move($filePath, $dest);
						}
					}
					else
					{
						$filePath = $file_directory_tmp . $file['file_name'];
						if (JFile::exists($filePath))
						{
							JFile::delete($filePath);
						}
					}
				}
			}
		}
	}

	
	public function saveDocumentChangeLogs($dataInput, $table)
	{
		$changeLogs = $dataInput['changelogs'];

		if ($changeLogs)
		{
			$docId             = $table->id;
			$changeLogTable    = JTable::getInstance("Changelog", "JUDownloadTable");
			$changeLogOrdering = 0;
			foreach ($changeLogs AS $changeLog)
			{
				$changeLogTable->reset();
				
				if ($changeLog['id'] && $changeLogTable->load(array("id" => $changeLog['id'], "doc_id" => $docId)))
				{
					
					if ($changeLog['remove'] || ($changeLog['version'] === "" && $changeLog['description'] === ""))
					{
						$changeLogTable->delete();
					}
					
					else
					{
						unset($changeLog['remove']);
						$changeLogOrdering++;
						$changeLog['ordering'] = $changeLogOrdering;
						$changeLogTable->bind($changeLog);
						$changeLogTable->store();
					}
				}
				
				else
				{
					$changeLog['id']     = 0;
					$changeLog['doc_id'] = $docId;
					if (!$changeLog['remove'] && ($changeLog['version'] !== "" || $changeLog['description'] !== ""))
					{
						unset($changeLog['remove']);
						$changeLogOrdering++;
						$changeLog['ordering'] = $changeLogOrdering;
						$changeLogTable->bind($changeLog);
						$changeLogTable->store();
					}
				}
			}
		}
	}

	
	public function saveDocumentRelated($dataInput, $table)
	{
		$relatedDocIds = $dataInput['related_documents'];

		if ($relatedDocIds)
		{
			$docId = $table->id;

			
			$documentsRelationTable = JTable::getInstance("DocumentsRelation", "JUDownloadTable");
			foreach ($relatedDocIds AS $relatedDocOrdering => $relatedDocId)
			{
				
				if ($documentsRelationTable->load(array("doc_id" => $docId, "doc_id_related" => $relatedDocId), true))
				{
					$documentsRelationTable->ordering = $relatedDocOrdering + 1;
					$documentsRelationTable->store();
				}
				
				else
				{
					$documentsRelationTable->bind(array("id" => 0, "doc_id" => $docId, "doc_id_related" => $relatedDocId, "ordering" => $relatedDocOrdering + 1), true);
					$documentsRelationTable->store();
				}
			}

			$db = JFactory::getDbo();

			
			$query = "SELECT doc_id_related FROM #__judownload_documents_relations WHERE doc_id = " . $docId;
			$db->setQuery($query);
			$relatedDocIdsDb = $db->loadColumn();

			$removeRelatedDocIds = array_diff($relatedDocIdsDb, $relatedDocIds);
			if ($removeRelatedDocIds)
			{
				$query = "DELETE FROM #__judownload_documents_relations WHERE doc_id = " . $docId . " AND doc_id_related IN (" . implode(",", $removeRelatedDocIds) . ")";
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	
	public function saveDocumentFields($fieldsData, $table, $isNew)
	{
		$params = JUDownloadHelper::getParams();
		$app    = JFactory::getApplication();

		$docId    = $table->id;
		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();
		$key      = $table->getKeyName();

		
		if ($isNew)
		{
			$categoriesField = JUDownloadFrontHelperField::getField('cat_id', $docId);

			if (($this->getDocumentSubmitType($docId, $table, $isNew) == 'submit' && $categoriesField->canSubmit())
				|| ($this->getDocumentSubmitType($docId, $table, $isNew) == 'edit' && $categoriesField->canEdit())
			)
			{
				$categoriesField->is_new = $isNew;
				$categoriesFieldValue    = $fieldsData[$categoriesField->id];
				$saveFieldCategory       = $categoriesField->storeValue($categoriesFieldValue);
				if (!$saveFieldCategory)
				{
					$this->setError('COM_JUDOWNLOAD_FAIL_TO_SAVE_CATEGORY_FIELD');

					

					$this->delete($docId);

					return false;
				}
			}
		}

		
		$form                      = $this->getFormDefault();
		$xml_field_name_publishing = array();

		$elementsInPublishing = $form->xpath('//fieldset[@name="publishing"]/field | //field[@fieldset="publishing"]');

		foreach ($elementsInPublishing AS $elementsInPublishingKey => $elementsInPublishingVal)
		{
			$elementInPublishing         = $elementsInPublishingVal->attributes();
			$xml_field_name_publishing[] = (string) $elementInPublishing['name'];
		}

		
		$query = $db->getQuery(true);
		$query->select("field.*");
		$query->select("plg.folder");
		$query->from("#__judownload_fields AS field");
		$query->join("", "#__judownload_fields_groups AS field_group ON field.group_id = field_group.id");
		$query->join("", "#__judownload_plugins AS plg ON field.plugin_id = plg.id");
		$query->join("", "#__judownload_categories AS c ON (c.fieldgroup_id = field.group_id OR field.group_id = 1)");
		$query->join("", "#__judownload_documents_xref AS dxref ON (dxref.cat_id = c.id AND dxref.main = 1)");
		$query->join("", "#__judownload_documents AS d ON dxref.doc_id = d.id");
		$query->where("field.published = 1");
		$query->where('field.publish_up <= ' . $db->quote($nowDate));
		$query->where('(field.publish_down = ' . $db->quote($nullDate) . ' OR field.publish_down > ' . $db->quote($nowDate) . ')');
		$query->where("field_group.published = 1");
		$query->where("d.id = $docId");
		
		$query->where("field.field_name != '" . $key . "'");

		if ($app->isSite() && !$params->get('submit_form_show_tab_publishing', 0))
		{
			if (!empty($xml_field_name_publishing))
			{
				$query->where('field.field_name NOT IN (' . implode(',', $db->quote($xml_field_name_publishing)) . ')');
			}
		}

		
		$query->where("field.field_name != 'cat_id'");
		if (!JUDLPROVERSION)
		{
			$query->where("field.field_name != 'approved'");
			$query->where("field.field_name != 'approved_by'");
			$query->where("field.field_name != 'approved_time'");
		}
		$query->group('field.id');
		$query->order('field.ordering ASC');
		$db->setQuery($query);
		$fields = $db->loadObjectList();
		
		foreach ($fields AS $field)
		{
			$fieldObj = JUDownloadFrontHelperField::getField($field, $table);
			
			if (($this->getDocumentSubmitType($docId, $table, $isNew) == 'submit' && $fieldObj->canSubmit())
				|| ($this->getDocumentSubmitType($docId, $table, $isNew) == 'edit' && $fieldObj->canEdit())
			)
			{
				$fieldObj->fields_data = $fieldsData;
				$fieldValue            = isset($fieldsData[$field->id]) ? $fieldsData[$field->id] : "";
				
				$fieldObj->is_new = $isNew;
				$fieldObj->storeValue($fieldValue);
			}
		}
	}

	
	public function saveDocumentAddLog($table, $isNew)
	{
		$app = JFactory::getApplication();

		
		if ($app->isSite())
		{
			$user = JFactory::getUser();

			if ($isNew)
			{
				
				$logData = array(
					'user_id'   => $user->id,
					'event'     => 'document.create',
					'item_id'   => $table->id,
					'doc_id'    => $table->id,
					'value'     => 0,
					'reference' => '',
				);
			}
			else
			{
				
				$logData = array(
					'user_id'   => $user->id,
					'event'     => 'document.edit',
					'item_id'   => $table->id,
					'doc_id'    => $table->id,
					'value'     => 0,
					'reference' => '',
				);
			}

			JUDownloadFrontHelperLog::addLog($logData);
		}
	}

	
	public function saveDocumentSendEmail($table, $isNew, $fieldsData, $tableBeforeSave = null)
	{
		$app = JFactory::getApplication();

		
		if ($app->isSite())
		{
			if ($isNew)
			{
				JUDownloadFrontHelperMail::sendEmailByEvent('document.create', $table->id);
			}
			else
			{
				$versionField = new JUDownloadFieldCore_version();
				if (isset($fieldsData[$versionField->id]) && version_compare($tableBeforeSave->version, $fieldsData[$versionField->id]) != 0)
				{
					
					JUDownloadFrontHelperMail::sendEmailByEvent('document.update', $table->id);
				}

				JUDownloadFrontHelperMail::sendEmailByEvent('document.edit', $table->id);
			}
		}
	}

	
	public function addFileInfo(&$file, $fileDirectoryTMP, $fileDirectory)
	{
		$params   = JUDownloadHelper::getParams();
		$filePath = $fileDirectoryTMP . $file['file_name'];
		
		if (function_exists('finfo_open'))
		{
			$finfo             = finfo_open(FILEINFO_MIME);
			$file['mime_type'] = finfo_file($finfo, $filePath);
			finfo_close($finfo);
		}
		elseif (function_exists('mime_content_type'))
		{
			$file['mime_type'] = mime_content_type($file['tmp_name']);
		}

		$file['size'] = filesize($filePath);

		if (($params->get('auto_generate_md5_checksum', 2) == 2 && $file['size'] <= $params->get("max_filesize_auto_generate_checksum", 100) * 1024 * 1024) || $params->get('auto_generate_md5_checksum', 2) == 1)
		{
			$file['md5_checksum'] = hash_file('md5', $filePath);
		}
		else
		{
			$file['md5_checksum'] = '';
		}

		if (($params->get('auto_generate_crc32_checksum', 2) == 2 && $file['size'] <= $params->get("max_filesize_auto_generate_checksum", 100) * 1024 * 1024) || $params->get('auto_generate_crc32_checksum', 2) == 1)
		{
			$file['crc32_checksum'] = hash_file('crc32b', $filePath);
		}
		else
		{
			$file['crc32_checksum'] = '';
		}

		
		do
		{
			$file['file_name'] = md5($file['file_name'] . JUDownloadHelper::generateRandomString(10)) . "." . JFile::getExt($file['file_name']);
			$dest              = $fileDirectory . $file['doc_id'] . "/" . $file['file_name'];
		} while (JFile::exists($dest));
	}

	
	public function copyDocuments($document_id_arr, $tocat_id_arr, $copy_option_arr, $tmp_doc = false, &$filesStoreMap = array(), &$versionsStoreMap = array(), &$fieldsData = array())
	{
		$dispatcher = JDispatcher::getInstance();
		JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_judownload/tables");
		$db       = JFactory::getDbo();
		$user     = JFactory::getUser();
		$catTable = JTable::getInstance("Category", "JUDownloadTable");
		$table    = $this->getTable();
		if (empty($document_id_arr))
		{
			return false;
		}

		if (empty($tocat_id_arr))
		{
			return false;
		}

		set_time_limit(0);

		$assetTable        = JTable::getInstance('Asset', 'JTable');
		$fileTable         = JTable::getInstance("File", "JUDownloadTable");
		$commentTable      = JTable::getInstance("Comment", "JUDownloadTable");
		$reportTable       = JTable::getInstance("Report", "JUDownloadTable");
		$subscriptionTable = JTable::getInstance("Subscription", "JUDownloadTable");
		$changelogTable    = JTable::getInstance("Changelog", "JUDownloadTable");
		$logTable          = JTable::getInstance("Log", "JUDownloadTable");
		$versionTable      = JTable::getInstance("Version", "JUDownloadTable");

		$total_copied_documents = 0;
		foreach ($tocat_id_arr AS $tocat_id)
		{
			$catTable->reset();
			if (!$catTable->load($tocat_id))
			{
				continue;
			}

			
			$assetName   = 'com_judownload.category.' . (int) $tocat_id;
			$canDoCreate = $user->authorise('judl.document.create', $assetName);
			if (!$canDoCreate)
			{
				JError::raiseWarning(401, JText::sprintf('COM_JUDOWNLOAD_CAN_NOT_CREATE_DOCUMENT_IN_THIS_CATEGORY', $catTable->title));
				continue;
			}

			
			foreach ($document_id_arr AS $doc_id)
			{
				$table->reset();
				if (!$table->load($doc_id))
				{
					continue;
				}

				$oldTable = $table;

				$table->id = 0;
				
				$table->cat_id = $tocat_id;
				
				do
				{
					$query = $db->getQuery(true);
					$query->SELECT('COUNT(*)');
					$query->FROM('#__judownload_documents AS d');
					$query->JOIN('', '#__judownload_documents_xref AS dxref ON dxref.doc_id = d.id');
					$query->JOIN('', '#__judownload_categories AS c ON dxref.cat_id = c.id');
					$query->WHERE('c.id = ' . $tocat_id);
					$query->WHERE('d.alias = "' . $table->alias . '"');
					$db->setQuery($query);
					$sameAliasDocument = $db->loadResult();

					if ($sameAliasDocument)
					{
						$table->title = JString::increment($table->title);
						$table->alias = JApplication::stringURLSafe(JString::increment($table->alias, 'dash'));
					}
				} while ($sameAliasDocument);

				
				if ($table->style_id == -1)
				{
					$old_cat_id = JUDownloadFrontHelperCategory::getMainCategoryId($doc_id);
					
					if ($old_cat_id != $tocat_id)
					{
						$oldTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($old_cat_id);
						$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($tocat_id);
						if ($oldTemplateStyleObject->template_id != $newTemplateStyleObject->template_id)
						{
							if (in_array('keep_template_params', $copy_option_arr) && $tmp_doc == false)
							{
								$table->style_id = $oldTemplateStyleObject->style_id;
							}
							else
							{
								if ($tmp_doc == false)
								{
									$table->template_params = '';
								}
							}
						}
					}
				}

				
				if (!in_array('copy_downloads', $copy_option_arr) && $tmp_doc == false)
				{
					$table->downloads = 0;
				}

				
				if (!in_array('copy_rates', $copy_option_arr) && $tmp_doc == false)
				{
					$table->rating      = 0;
					$table->total_votes = 0;
				}

				
				if (!in_array('copy_hits', $copy_option_arr) && $tmp_doc == false)
				{
					$table->hits = 0;
				}

				
				if (in_array('copy_permission', $copy_option_arr))
				{
					$assetTable->reset();
					if ($assetTable->loadByName('com_judownload.document.' . $doc_id))
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

				if (!$table->check())
				{
					continue;
				}

				
				$result = $dispatcher->trigger('onContentBeforeCopy', array($this->option . '.' . $this->name, $table, $oldTable, $copy_option_arr));

				if (in_array(false, $result, true))
				{
					$this->setError($table->getError());

					return false;
				}

				if ($table->store())
				{
					$table->checkIn();
					$total_copied_documents++;
				}
				else
				{
					continue;
				}

				$newDocId = $table->id;

				
				if ($table->icon)
				{
					$ori_icon_name = $table->icon;
					$new_icon_name = $newDocId . substr($ori_icon_name, strpos($ori_icon_name, '_'));
					$query         = "UPDATE #__judownload_documents SET icon = '" . $new_icon_name . "' WHERE id=" . $newDocId;
					$db->setQuery($query);
					$db->execute();

					$icon_directory = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("document_icon_directory", "media/com_judownload/images/document/");
					if (JFile::exists($icon_directory . $ori_icon_name))
					{
						JFile::copy($icon_directory . $ori_icon_name, $icon_directory . $new_icon_name);
					}

					if (JFile::exists($icon_directory . "original/" . $ori_icon_name))
					{
						JFile::copy($icon_directory . "original/" . $ori_icon_name, $icon_directory . "original/" . $new_icon_name);
					}
				}

				
				$query = "INSERT INTO #__judownload_documents_xref (doc_id, cat_id, main) VALUES($newDocId, $tocat_id, 1)";
				$db->setQuery($query);
				$db->execute();

				
				$query = "SELECT * FROM #__judownload_tags_xref WHERE doc_id=" . $doc_id . " ORDER BY ordering ASC";
				$db->setQuery($query);
				$tags = $db->loadObjectList();
				if (!empty($tags))
				{
					foreach ($tags AS $tag)
					{
						$query = "INSERT INTO #__judownload_tags_xref (tag_id, doc_id, ordering) VALUES (" . $tag->tag_id . ", " . $newDocId . ", " . $tag->ordering . ")";
						$db->setQuery($query);
						$db->execute();
					}
				}

				
				$ori_fieldgroup_id = JUDownloadHelper::getFieldGroupIdByDocId($doc_id);

				$copy_extra_fields = in_array("copy_extra_fields", $copy_option_arr);
				if ($copy_extra_fields)
				{
					$copy_extra_fields = $ori_fieldgroup_id == $catTable->fieldgroup_id ? true : false;
				}

				$query = $db->getQuery(true);
				$query->select("field.*");
				$query->from("#__judownload_fields AS field");
				$query->select("plg.folder");
				$query->join("", "#__judownload_plugins AS plg ON field.plugin_id = plg.id");
				if ($copy_extra_fields && $ori_fieldgroup_id)
				{
					$query->where("field.group_id IN (1, $ori_fieldgroup_id)");
				}
				else
				{
					$query->where("field.group_id = 1");
				}
				$db->setQuery($query);
				$fields = $db->loadObjectList();

				foreach ($fields AS $field)
				{
					$fieldObj = JUDownloadFrontHelperField::getField($field, $doc_id);
					$fieldObj->onCopy($newDocId, $fieldsData);
				}

				
				if (in_array('copy_files', $copy_option_arr))
				{
					$query = "SELECT * FROM #__judownload_files WHERE doc_id = " . $doc_id;
					$db->setQuery($query);
					$files = $db->loadObjectList();
					if ($files)
					{
						foreach ($files AS $file)
						{
							$fileTable->reset();
							if ($fileTable->bind($file) && $fileTable->check())
							{
								$fileTable->id     = 0;
								$fileTable->doc_id = $newDocId;
								if ($fileTable->store())
								{
									
									$filesStoreMap[$file->id] = $fileTable->id;

									$query = "SELECT id FROM #__judownload_versions WHERE file_id = " . $file->id;
									$db->setQuery($query);
									$oldFileVersionIds = $db->loadColumn();
									foreach ($oldFileVersionIds AS $oldFileVersionId)
									{
										
										if ($versionTable->load($oldFileVersionId))
										{
											$versionTable->id      = 0;
											$versionTable->doc_id  = $newDocId;
											$versionTable->file_id = $fileTable->id;
											$versionTable->store();
											
											$versionsStoreMap[$oldFileVersionId] = $versionTable->id;
										}
									}
								}
							}
							else
							{
								continue;
							}
						}
					}

					$file_directory = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/");
					$ori_directory  = JPath::clean($file_directory . $doc_id);
					$new_directory  = JPath::clean($file_directory . $newDocId);
					if (JFolder::exists($ori_directory))
					{
						JFolder::copy($ori_directory, $new_directory);
					}
				}

				
				$query = "SELECT id FROM #__judownload_versions WHERE doc_id = " . $doc_id . " AND file_id = 0";
				$db->setQuery($query);
				$oldDocVersionIds = $db->loadColumn();
				foreach ($oldDocVersionIds AS $oldDocVersionId)
				{
					if ($versionTable->load($oldDocVersionId))
					{
						$versionTable->id     = 0;
						$versionTable->doc_id = $newDocId;
						$versionTable->store();
						
						$versionsStoreMap[$oldDocVersionId] = $versionTable->id;
					}
				}

				
				if (in_array('copy_changelogs', $copy_option_arr))
				{
					$query = "SELECT * FROM #__judownload_changelogs WHERE `doc_id` = $doc_id";
					$db->setQuery($query);
					$changelogs = $db->loadObjectList();
					if ($changelogs)
					{
						foreach ($changelogs AS $changelog)
						{
							$changelogTable->reset();
							if ($changelogTable->bind($changelog) && $changelogTable->check())
							{
								$changelogTable->id     = 0;
								$changelogTable->doc_id = $newDocId;
								$changelogTable->store();
							}
							else
							{
								continue;
							}
						}
					}
				}

				
				if (in_array('copy_related_documents', $copy_option_arr))
				{
					$query = "INSERT INTO `#__judownload_documents_relations` (doc_id, doc_id_related, ordering) SELECT $newDocId, doc_id_related, ordering FROM `#__judownload_documents_relations` WHERE doc_id = $doc_id";
					$db->setQuery($query);
					$db->execute();
				}

				
				if (in_array('copy_rates', $copy_option_arr))
				{
					$ratingMapping = array();

					$query = "SELECT * FROM #__judownload_rating WHERE doc_id = $doc_id";
					$db->setQuery($query);
					$ratings = $db->loadObjectList();
					if (count($ratings))
					{
						$criteriagroup_id = JUDownloadHelper::getCriteriaGroupIdByDocId($doc_id);
						foreach ($ratings AS $rating)
						{
							$oldRatingId    = $rating->id;
							$rating->id     = 0;
							$rating->doc_id = $newDocId;

							if ($db->insertObject('#__judownload_rating', $rating, 'id'))
							{
								if (JUDownloadHelper::hasMultiRating() && $criteriagroup_id && $criteriagroup_id == $catTable->criteriagroup_id)
								{
									
									JUDownloadMultiRating::copyCriteriaValue($rating->id, $oldRatingId);
								}

								
								$ratingMapping[$oldRatingId] = $rating->id;
							}
						}
					}
				}

				
				if (in_array('copy_comments', $copy_option_arr))
				{
					$query = "SELECT id FROM #__judownload_comments WHERE doc_id=" . $doc_id . " AND parent_id = 1";
					$db->setQuery($query);
					$commentIds = $db->loadColumn();
					
					$commentMapping = array();
					WHILE (!empty($commentIds))
					{
						
						$commentId = array_shift($commentIds);
						
						$query = "SELECT id FROM #__judownload_comments WHERE doc_id=" . $doc_id . " AND parent_id = $commentId";
						$db->setQuery($query);
						$_commentIds = $db->loadColumn();
						foreach ($_commentIds AS $_commentId)
						{
							
							if (!in_array($_commentId, $commentIds))
							{
								array_push($commentIds, $_commentId);
							}
						}

						
						$commentTable->load($commentId, true);
						$commentTable->id        = 0;
						$commentTable->doc_id    = $newDocId;
						$commentTable->parent_id = isset($commentMapping[$commentTable->parent_id]) ? $commentMapping[$commentTable->parent_id] : 0;
						
						if (in_array('copy_rates', $copy_option_arr))
						{
							$commentTable->rating_id = isset($ratingMapping[$commentTable->rating_id]) ? $ratingMapping[$commentTable->rating_id] : 0;
						}
						$commentTable->store();

						$new_comment_id = $commentTable->id;
						
						$commentMapping[$commentId] = $new_comment_id;

						
						$query = "SELECT * FROM #__judownload_reports WHERE `item_id` = $commentId AND `type` = 'comment'";
						$db->setQuery($query);
						$reports = $db->loadObjectList();
						if ($reports)
						{
							foreach ($reports AS $report)
							{
								$reportTable->reset();
								if ($reportTable->bind($report) && $reportTable->check())
								{
									$reportTable->id      = 0;
									$reportTable->item_id = $new_comment_id;
									$reportTable->store();
								}
								else
								{
									continue;
								}
							}
						}

						
						$query = "SELECT * FROM #__judownload_subscriptions WHERE `item_id` = $commentId AND `type` = 'comment'";
						$db->setQuery($query);
						$subscriptions = $db->loadObjectList();
						if ($subscriptions)
						{
							foreach ($subscriptions AS $subscription)
							{
								$subscriptionTable->reset();
								if ($subscriptionTable->bind($subscription) && $subscriptionTable->check())
								{
									$subscriptionTable->id      = 0;
									$subscriptionTable->item_id = $new_comment_id;
									$subscriptionTable->store();
								}
								else
								{
									continue;
								}
							}
						}
					}
				}

				
				if (in_array('copy_reports', $copy_option_arr))
				{
					
					$query = "SELECT * FROM #__judownload_reports WHERE `item_id` = $doc_id AND `type` = 'document'";
					$db->setQuery($query);
					$reports = $db->loadObjectList();
					if ($reports)
					{
						foreach ($reports AS $report)
						{
							$reportTable->reset();
							if ($reportTable->bind($report) && $reportTable->check())
							{
								$reportTable->id      = 0;
								$reportTable->item_id = $newDocId;
								$reportTable->store();
							}
							else
							{
								continue;
							}
						}
					}
				}

				
				if (in_array('copy_subscriptions', $copy_option_arr))
				{
					$query = "SELECT * FROM #__judownload_subscriptions WHERE `item_id` = $doc_id AND `type` = 'document'";
					$db->setQuery($query);
					$subscriptions = $db->loadObjectList();
					if ($subscriptions)
					{
						foreach ($subscriptions AS $subscription)
						{
							$subscriptionTable->reset();
							if ($subscriptionTable->bind($subscription) && $subscriptionTable->check())
							{
								$subscriptionTable->id      = 0;
								$subscriptionTable->item_id = $newDocId;
								$subscriptionTable->store();
							}
							else
							{
								continue;
							}
						}
					}
				}

				
				if (in_array('copy_logs', $copy_option_arr))
				{
					$query = "SELECT * FROM #__judownload_logs WHERE (`doc_id` = $doc_id)";
					$db->setQuery($query);
					$logs = $db->loadObjectList();
					if ($logs)
					{
						foreach ($logs AS $log)
						{
							$logTable->reset();
							if ($logTable->bind($log) && $logTable->check())
							{
								$logTable->id      = 0;
								$logTable->item_id = $newDocId;
								$logTable->doc_id  = $newDocId;
								$logTable->store();
							}
							else
							{
								continue;
							}
						}
					}
				}

				
				if ($tmp_doc)
				{
					return $newDocId;
				}

				
				$this->cleanCache();

				
				$dispatcher->trigger('onContentAfterCopy', array($this->option . '.' . $this->name, $table, $oldTable, $copy_option_arr));
			}
		}

		return $total_copied_documents;
	}

	
	public function copyAndMap($docArr, $catArr, $copyOptionsArr, &$filesMustMap, &$versionsMustMap, $case = null, &$fieldsData = array())
	{
		$filesStoreMap    = array();
		$versionsStoreMap = array();
		
		$documentIdToCopy     = (int) $docArr[0];
		$documentObjectToCopy = JUDownloadHelper::getDocumentById($documentIdToCopy);
		$copiedDocumentId     = $this->copyDocuments($docArr, $catArr, $copyOptionsArr, $tmp = true, $filesStoreMap, $versionsStoreMap, $fieldsData);
		$copiedDocumentObject = JUDownloadHelper::getDocumentById($copiedDocumentId);

		

		if ($case == 'save2copy')
		{
			$titleField = new JUDownloadFieldCore_title();
			$aliasField = new JUDownloadFieldCore_alias();

			if ($fieldsData[$aliasField->id] == $documentObjectToCopy->alias)
			{
				$fieldsData[$aliasField->id] = $copiedDocumentObject->alias;
				if ($fieldsData[$titleField->id] == $documentObjectToCopy->title)
				{
					$fieldsData[$titleField->id] = $copiedDocumentObject->title;
				}
			}
		}

		$db = JFactory::getDbo();
		
		
		
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
		$tableFile = JTable::getInstance('File', 'JUDownloadTable');

		foreach ($filesMustMap AS $keyFilesMustMap => $valueFilesMustMap)
		{
			$oldFileId = $valueFilesMustMap['id'];
			if ($tableFile->load($oldFileId))
			{
				if (isset($filesStoreMap[$oldFileId]))
				{
					$filesMustMap[$keyFilesMustMap]['id'] = $filesStoreMap[$oldFileId];
				}
				else
				{
					unset($filesMustMap[$keyFilesMustMap]);
				}
			}
		}

		
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
		$tableVersion       = JTable::getInstance('Version', 'JUDownloadTable');
		$newVersionsMustMap = array();

		foreach ($versionsMustMap as $versionKey => $versionVal)
		{
			$tableVersion->load($versionKey);
			$newFileId = $filesStoreMap[$tableVersion->file_id];

			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__judownload_versions');
			$query->where('file_id = ' . $newFileId);
			$query->where('doc_id = ' . $copiedDocumentObject->id);
			$query->where('version = ' . $db->quote($tableVersion->version));
			$db->setQuery($query);
			$newVersion = $db->loadObject();

			if ($newVersion->id)
			{
				$newVersionsMustMap[$newVersion->id] = $versionsMustMap[$versionKey];
			}
		}

		$versionsMustMap = $newVersionsMustMap;

		
		return $copiedDocumentObject->id;
	}

	
	public function moveDocuments($document_id_arr, $tocat_id, $move_option_arr = array())
	{
		$dispatcher = JDispatcher::getInstance();
		$user       = JFactory::getUser();
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
		$catTable = JTable::getInstance("Category", "JUDownloadTable");
		if ($tocat_id)
		{
			if (!$catTable->load($tocat_id))
			{
				JError::raiseWarning(500, JText::_('COM_JUDOWNLOAD_TARGET_CATEGORY_NOT_FOUND'));

				return false;
			}

			$table     = $this->getTable();
			$db        = JFactory::getDbo();
			$assetName = 'com_judownload.category.' . (int) $tocat_id;
			
			$query = 'SELECT id FROM #__assets WHERE name="' . $assetName . '"';
			$db->setQuery($query);
			$tocat_asset_id = $db->loadResult();
			$canCreate      = $user->authorise('judl.document.create', $assetName);
			if (!$canCreate)
			{
				JError::raiseError(100, JText::sprintf('COM_JUDOWNLOAD_CAN_NOT_CREATE_DOCUMENT_IN_THIS_CATEGORY', $catTable->title));

				return false;
			}
		}
		else
		{
			JError::raiseWarning(500, JText::_('COM_JUDOWNLOAD_NO_TARGET_CATEGORY_SELECTED'));

			return false;
		}

		if (empty($document_id_arr))
		{
			JError::raiseError(100, JText::_('COM_JUDOWNLOAD_NO_ITEM_SELECTED'));

			return false;
		}

		set_time_limit(0);

		$moved_documents = array();
		foreach ($document_id_arr AS $doc_id)
		{
			if (!$table->load($doc_id))
			{
				continue;
			}
			$assetName = 'com_judownload.document.' . (int) $doc_id;
			$canDoEdit = $user->authorise('judl.document.edit', $assetName);
			if (!$canDoEdit)
			{
				if (!$user->id)
				{
					JError::raiseWarning(100, JText::sprintf('COM_JUDOWNLOAD_YOU_DONT_HAVE_PERMISSION_TO_ACCESS_DOCUMENT', $table->title));
					continue;
				}
				else
				{
					if (($user->id == $table->created_by))
					{
						$canDoEditOwn = $user->authorise('judl.document.edit.own', $assetName);
						if (!$canDoEditOwn)
						{
							JError::raiseWarning(100, JText::sprintf('COM_JUDOWNLOAD_YOU_DONT_HAVE_PERMISSION_TO_ACCESS_DOCUMENT', $table->title));
							continue;
						}
					}
				}
			}

			
			$query = "SELECT cat_id FROM #__judownload_documents_xref WHERE doc_id = " . $doc_id . " AND main=1";
			$db->setQuery($query);
			$cat_id = $db->loadResult();
			
			if ($tocat_id == $cat_id)
			{
				continue;
			}

			
			$result = $dispatcher->trigger($this->onContentBeforeMove, array($this->option . '.' . $this->name, $table, $tocat_id, $move_option_arr));
			if (in_array(false, $result, true))
			{
				$this->setError($table->getError());

				return false;
			}

			
			if ($table->style_id == -1)
			{
				$oldTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($cat_id);
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($tocat_id);
				if ($oldTemplateStyleObject->template_id != $newTemplateStyleObject->template_id)
				{
					if (in_array('keep_template_params', $move_option_arr))
					{
						$table->style_id = $oldTemplateStyleObject->id;
					}
					else
					{
						
						$query = "UPDATE #__judownload_documents SET template_params = '' WHERE id=" . $doc_id;
						$db->setQuery($query);
						$db->execute();
					}
				}
			}


			
			$query = "SELECT COUNT(*) FROM #__judownload_documents_xref WHERE cat_id=" . $tocat_id . " AND doc_id=" . $doc_id . " AND main=0";
			$db->setQuery($query);
			$is_secondary_cat = $db->loadResult();

			
			if ($is_secondary_cat)
			{
				
				$query = "DELETE FROM #__judownload_documents_xref WHERE doc_id=" . $doc_id . " AND main=1";
				$db->setQuery($query);
				$db->execute();

				
				$query = "UPDATE #__judownload_documents_xref SET main=1 WHERE cat_id=" . $tocat_id . " AND doc_id=" . $doc_id;
				$db->setQuery($query);
				$db->execute();
			}
			
			else
			{
				
				$query = "UPDATE #__judownload_documents_xref SET cat_id=" . $tocat_id . " WHERE doc_id=" . $doc_id . " AND main=1";
				$db->setQuery($query);
				$db->execute();
			}

			if (in_array('keep_permission', $move_option_arr))
			{
				
				$query = 'UPDATE #__assets SET `parent_id` = ' . $tocat_asset_id . ' WHERE name="com_judownload.document.' . $doc_id . '"';
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				
				$query = 'UPDATE #__assets SET `parent_id` = ' . $tocat_asset_id . ', `rules` = "{}" WHERE name="com_judownload.document.' . $doc_id . '"';
				$db->setQuery($query);
				$db->execute();
			}

			$moved_documents[] = $doc_id;

			
			$this->cleanCache();

			
			$dispatcher->trigger($this->onContentAfterMove, array($this->option . '.' . $this->name, $table, $tocat_id, $move_option_arr));
		}

		$total_moved_documents = count($moved_documents);
		if ($total_moved_documents)
		{
			$old_field_groupid = JUDownloadHelper::getCategoryById($cat_id)->fieldgroup_id;
			$new_field_groupid = JUDownloadHelper::getCategoryById($tocat_id)->fieldgroup_id;
			$keep_extra_fields = in_array("keep_extra_fields", $move_option_arr);
			if ($keep_extra_fields)
			{
				$keep_extra_fields = $old_field_groupid == $new_field_groupid ? true : false;
			}

			
			if (!$keep_extra_fields)
			{
				foreach ($moved_documents AS $doc_id)
				{
					
					JUDownloadHelper::deleteFieldValuesOfDocument($doc_id);
				}
			}

			$old_criteria_groupid = JUDownloadHelper::getCategoryById($cat_id)->criteriagroup_id;
			$new_criteria_groupid = JUDownloadHelper::getCategoryById($tocat_id)->criteriagroup_id;
			$keep_rates           = in_array("keep_rates", $move_option_arr);
			if ($keep_rates)
			{
				$keep_rates = $old_criteria_groupid == $new_criteria_groupid ? true : false;
			}

			if (!$keep_rates)
			{
				JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_judownload/tables");
				$ratingTable = JTable::getInstance("Rating", "JUDownloadTable");
				foreach ($moved_documents AS $doc_id)
				{
					$query = "SELECT id FROM #__judownload_rating WHERE doc_id = " . $doc_id;
					$db->setQuery($query);
					$ratingIds = $db->loadColumn();
					foreach ($ratingIds AS $ratingId)
					{
						$ratingTable->delete($ratingId);
					}
				}
			}
		}

		return $total_moved_documents;
	}

	
	

	
	

	
	public function remoteFile()
	{
		$app = JFactory::getApplication();

		if (!$this->isValidUploadURL() || !JUDownloadFrontHelperPermission::canUploadFromUrl($app->input->get('doc_id', null)))
		{
			$result            = array();
			$result['success'] = 0;
			$result['alert']   = JText::_('COM_JUDOWNLOAD_YOU_ARE_NOT_AUTHORIZED_TO_UPLOAD_FILE');

			JUDownloadHelper::obCleanData();
			echo json_encode($result);
			exit;
		}

		$type   = $app->input->get('type', '');
		$result = array();

		$file_directory     = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/");
		$file_directory_tmp = $file_directory . "tmp/";

		
		if (!JFolder::exists($file_directory_tmp))
		{
			JFolder::create($file_directory_tmp);
			$file_index = $file_directory_tmp . 'index.html';
			$buffer     = "<!DOCTYPE html><title></title>";
			JFile::write($file_index, $buffer);
		}

		if (!is_writable($file_directory_tmp))
		{
			$file_directory_tmp = sys_get_temp_dir() . "/plupload/";
		}

		if ($type === 'loadtransfer')
		{
			$source_url = $app->input->get('source_url', '', 'string');
			if (!$source_url)
			{
				$result['success'] = 0;
				$result['alert']   = JText::_('COM_JUDOWNLOAD_SOURCE_URL_IS_MISSING');
			}

			$file = $this->loadTransfer($source_url);
			if ($file)
			{
				$file_name      = end(explode("/", $source_url));
				$file_extension = end(explode(".", $file_name));
				do
				{
					$target_name = md5(JUDownloadHelper::generateRandomString(10)) . "." . $file_extension;
				} while (JFile::exists($file_directory_tmp . $target_name));

				$result['success']     = 1;
				$result['size']        = (int) $file[1];
				$result['type']        = (string) $file[2];
				$result['name']        = $file_name;
				$result['target_name'] = $target_name;
			}
			else
			{
				$result['success'] = 0;
				$result['alert']   = JText::_('COM_JUDOWNLOAD_INVALID_FILE');
			}
		}
		elseif ($type === 'transferfile')
		{
			$target_name = $app->input->get('target_name', '', 'string');
			$source_url  = $app->input->get('source_url', '', 'string');

			if (!$source_url)
			{
				$result['success'] = 0;
				$result['alert']   = JText::_('COM_JUDOWNLOAD_INVALID_SOURCE_URL');
			}
			elseif (!$target_name)
			{
				$result['success'] = 0;
				$result['alert']   = JText::_('COM_JUDOWNLOAD_INVALID_TARGET_NAME');
			}
			elseif ($this->remoteDownload($source_url, $file_directory_tmp . $target_name, 1024))
			{
				$result['success'] = 1;
				$result['alert']   = JText::_('COM_JUDOWNLOAD_DOWNLOAD_REMOTE_FILE_SUCCESSFULLY');
			}
			else
			{
				$result['success'] = 0;
				$result['alert']   = JText::_('COM_JUDOWNLOAD_FAIL_TO_DOWNLOAD_REMOTE_FILE');
			}
		}
		elseif ($type === 'getprocess')
		{
			$target_name  = $app->input->get('target_name', '', 'string');
			$file_size    = $app->input->getInt('file_size', 0);
			$current_size = filesize($file_directory_tmp . $target_name);

			if ($current_size === false || !$file_size)
			{
				$result['success'] = 0;
				$result['alert']   = JText::_('COM_JUDOWNLOAD_FAIL_TO_GET_FILE_TRANSFER_STATUS');
			}
			else
			{
				if ($current_size >= $file_size)
				{
					$percent = 100;
				}
				else
				{
					$percent = round(($current_size / $file_size) * 100);
				}
				$result['success'] = 1;
				$result['alert']   = '';
				$result['percent'] = $percent;
			}
		}

		
		if ($type === 'cancel')
		{
			$target_name = $app->input->get('target_name', '', 'string');
			if (JFile::exists($file_directory_tmp . $target_name))
			{
				JFile::delete($file_directory_tmp . $target_name);
			}
		}

		JUDownloadHelper::obCleanData();
		echo json_encode($result);
		exit;
	}

	
	public function isValidUploadURL()
	{
		$app  = JFactory::getApplication();
		$time = $app->input->getInt('time', 0);
		$code = $app->input->get('code', '');

		if (!$time || !$code)
		{
			return false;
		}

		$secret = JFactory::getConfig()->get('secret');
		if ($code != md5($time . $secret))
		{
			return false;
		}

		
		$liveTimeUrl = 60 * 60 * 5;
		if ((time() - $time) > $liveTimeUrl)
		{
			return false;
		}

		return true;
	}

	public function getPlugins()
	{
		return $this->pluginsCanEdit;
	}

	
	public function validateFields($fieldsData, $docId)
	{
		$app    = JFactory::getApplication();
		$params = JUDownloadHelper::getParams();

		
		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();
		$error    = false;

		$isNew = $docId == 0 ? true : false;

		$categoriesField = JUDownloadFrontHelperField::getField('cat_id', $docId);

		if (($this->getDocumentSubmitType($docId) == 'submit' && $categoriesField->canSubmit())
			|| ($this->getDocumentSubmitType($docId) == 'edit' && $categoriesField->canEdit())
		)
		{
			$fieldValueCategories         = $fieldsData[$categoriesField->id];
			$categoriesField->is_new      = $isNew;
			$categoriesField->fields_data = $fieldsData;
			$fieldValueCategories         = $categoriesField->filterField($fieldValueCategories);
			$valid                        = $categoriesField->PHPValidate($fieldValueCategories);

			if ($valid === true)
			{
				$fieldsData[$categoriesField->id] = $fieldValueCategories;
				$catId                            = $fieldsData[$categoriesField->id]['main'];
			}
			
			else
			{
				$this->setError($valid);
				if ($isNew)
				{
					return false;
				}
				$catId = JUDownloadFrontHelperCategory::getMainCategoryId($docId);
				$error = true;
				unset($fieldsData[$categoriesField->id]);
			}
		}
		else
		{
			$catId = JUDownloadFrontHelperCategory::getMainCategoryId($docId);
		}

		
		$form                      = $this->getFormDefault();
		$xml_field_name_publishing = array();

		$elementsInPublishing = $form->xpath('//fieldset[@name="publishing"]/field | //field[@fieldset="publishing"]');

		foreach ($elementsInPublishing AS $elementsInPublishingKey => $elementsInPublishingVal)
		{
			$elementInPublishing         = $elementsInPublishingVal->attributes();
			$xml_field_name_publishing[] = (string) $elementInPublishing['name'];
		}

		
		$query = $db->getQuery(true);
		$query->select("field.*");
		$query->from("#__judownload_fields AS field");
		$query->select("plg.folder");
		$query->join("", "#__judownload_plugins AS plg ON field.plugin_id = plg.id");
		$query->join("", "#__judownload_fields_groups AS field_group ON field_group.id = field.group_id");
		$query->join("", "#__judownload_categories AS c ON (c.fieldgroup_id = field.group_id OR field.group_id = 1 )");
		$query->where("field_group.published = 1");
		$query->where("field.published = 1");
		$query->where('field.publish_up <= ' . $db->quote($nowDate));
		$query->where('(field.publish_down = ' . $db->quote($nullDate) . ' OR field.publish_down >= ' . $db->quote($nowDate) . ')');
		$query->where("(c.id = " . $catId . " OR field.group_id = 1)");
		
		$query->where("field.field_name != 'cat_id'");
		if ($app->isSite() && !$params->get('submit_form_show_tab_publishing', 0))
		{
			if (!empty($xml_field_name_publishing))
			{
				$query->where('field.field_name NOT IN (' . implode(',', $db->quote($xml_field_name_publishing)) . ')');
			}
		}
		$query->group('field.id');
		$db->setQuery($query);
		$fields = $db->loadObjectList();

		
		foreach ($fields AS $field)
		{
			$fieldObj = JUDownloadFrontHelperField::getField($field, $docId);
			
			if (($this->getDocumentSubmitType($docId) == 'submit' && $fieldObj->canSubmit())
				|| ($this->getDocumentSubmitType($docId) == 'edit' && $fieldObj->canEdit())
			)
			{
				$fieldValue            = isset($fieldsData[$field->id]) ? $fieldsData[$field->id] : null;
				$fieldObj->is_new      = $isNew;
				$fieldObj->fields_data = $fieldsData;
				$fieldValue            = $fieldObj->filterField($fieldValue);
				$valid                 = $fieldObj->PHPValidate($fieldValue);
				
				if ($valid === true)
				{
					$fieldsData[$field->id] = $fieldValue;
				}
				else
				{
					$error = true;
					unset($fieldsData[$field->id]);
					$this->setError($valid);
				}
			}
		}

		if ($error)
		{
			return false;
		}
		else
		{
			return $fieldsData;
		}
	}

	
	public function validateFiles($files, $docId)
	{
		$app    = JFactory::getApplication();
		$params = JUDownloadHelper::getParams();

		$file_directory     = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/");
		$file_directory_tmp = $file_directory . "tmp/";
		if (!is_writable($file_directory_tmp))
		{
			$file_directory_tmp = sys_get_temp_dir() . "/plupload/";
		}

		$_fileRenames = array();

		$maxUploadFile   = 1;
		$totalValidFiles = 0;

		$fileIds = array();
		if ($docId)
		{
			$db    = JFactory::getDbo();
			$query = 'SELECT id FROM #__judownload_files WHERE doc_id = ' . (int) $docId;
			$db->setQuery($query);
			$fileIds = (array) $db->loadColumn();
		}

		foreach ($files AS $key => $file)
		{
			
			if ($docId && $file['id'] && !in_array($file['id'], $fileIds))
			{
				unset($files[$key]);
				continue;
			}

			
			if ($file['remove'])
			{
				continue;
			}

			
			if (!$file['id'])
			{
				$file['file_name'] = JFile::makeSafe($file['file_name']);
			}

			
			if (($maxUploadFile > 0 && $totalValidFiles > $maxUploadFile) || (!$file['id'] && !JFile::exists($file_directory_tmp . $file['file_name'])))
			{
				unset($files[$key]);
				continue;
			}

			
			$regex = '/^[^\\/\:\*\?"<>\|]+$/i';
			if (!preg_match($regex, $file['rename']))
			{
				$this->setError(JText::sprintf('COM_JUDOWNLOAD_INVALID_FILENAME', $file['rename']));

				return false;
			}

			
			if (in_array($file['rename'], $_fileRenames))
			{
				$this->setError(JText::sprintf('COM_JUDOWNLOAD_FILENAME_DUPLICATED', $file['rename']));

				return false;
			}

			$_fileRenames[] = $file['rename'];

			$totalValidFiles++;
		}

		
		if ($params->get('document_require_file', 1) && !$totalValidFiles && ($app->isSite() && $params->get('submit_form_show_tab_file', 0)))
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_YOU_HAVE_TO_UPLOAD_AT_LEAST_ONE_FILE'));

			return false;
		}

		return $files;
	}

	
	public function getCoreFields($fieldSet)
	{
		$db                   = JFactory::getDbo();
		$app                  = JFactory::getApplication();
		$docId                = $app->input->getInt('id', 0);
		$form                 = $this->getForm();
		$xml_field_name_array = $sorted_field_arr = array();
		
		foreach ($form->getFieldset($fieldSet) AS $key => $field)
		{
			
			if ($field->fieldname != 'cat_id')
			{
				$xml_field_name_array[] = $field->fieldname;
			}
		}

		if ($xml_field_name_array)
		{
			
			$query = $db->getQuery(true);
			$query->select("field.*, plg.folder FROM #__judownload_fields AS field");
			$query->join("LEFT", "#__judownload_plugins AS plg ON field.plugin_id = plg.id");
			if ($fieldSet == "details")
			{
				$query->where("(field.field_name IN ('" . implode("', '", $xml_field_name_array) . "') OR (field.group_id = 1 AND field.field_name = ''))");
			}
			else
			{
				$query->where("field.field_name IN ('" . implode("', '", $xml_field_name_array) . "')");
			}
			$query->where("field.field_name != 'cat_id' AND plg.folder != 'core_gallery'");
			$query->order("field.ordering, field.id ASC");

			$db->setQuery($query);
			$fields = $db->loadObjectList();

			$ordering = 0;
			if ($fields)
			{
				foreach ($fields AS $keyField => $field)
				{
					$fieldObj = JUDownloadFrontHelperField::getField($field, $docId);
					if (($this->getDocumentSubmitType($docId) == 'submit' && $fieldObj->canSubmit())
						|| ($this->getDocumentSubmitType($docId) == 'edit' && $fieldObj->canEdit())
					)
					{
						$ordering++;
						$sorted_field_arr[$ordering] = $fieldObj;
					}

					
					$index_field_name = array_search($field->field_name, $xml_field_name_array);
					if ($index_field_name !== false)
					{
						unset($xml_field_name_array[$index_field_name]);
					}
				}
			}

			
			if ($xml_field_name_array)
			{
				foreach ($xml_field_name_array AS $field_name)
				{
					$ordering++;
					$sorted_field_arr[$ordering] = $field_name;
				}
			}
		}

		return $sorted_field_arr;
	}

	
	public function getExtraFields()
	{
		return array();
	}

	public function getFiles()
	{
		$app    = JFactory::getApplication();
		$doc_id = $app->input->getInt('id', 0);

		$files = $app->getUserState("com_judownload.edit.document.files", array());
		if ($files)
		{
			$db = JFactory::getDbo();
			foreach ($files AS $key => $file)
			{
				if ($file['id'] > 0)
				{
					$query = "SELECT file_name, size, mime_type FROM #__judownload_files WHERE id = " . $file['id'];
					$db->setQuery($query);
					$result = $db->loadObject();
					
					$files[$key]['file_name'] = $result->file_name;
					
					if (!isset($files[$key]['size']))
					{
						$files[$key]['size'] = $result->size;
					}

					
					if (!isset($files[$key]['mime_type']))
					{
						$files[$key]['mime_type'] = $result->mime_type;
					}
				}
			}
		}
		elseif ($doc_id)
		{
			$db    = JFactory::getDbo();
			$query = "SELECT * FROM #__judownload_files WHERE doc_id = " . $doc_id . " ORDER BY ordering";
			$db->setQuery($query);
			$files = $db->loadAssocList();
		}

		return $files;
	}

	public function getChangelogs()
	{
		$app    = JFactory::getApplication();
		$doc_id = $app->input->getInt('id', 0);

		$changelogs = $app->getUserState("com_judownload.edit.document.changelogs", array());
		if (!$changelogs && $doc_id)
		{
			$db    = JFactory::getDbo();
			$query = "SELECT * FROM #__judownload_changelogs WHERE doc_id = " . $doc_id . " ORDER BY ordering";
			$db->setQuery($query);
			$changelogs = $db->loadAssocList();
		}

		return $changelogs;
	}

	public function getVersions()
	{
		return array();
	}

	public function getRelatedDocuments()
	{
		$app                = JFactory::getApplication();
		$doc_id             = $app->input->getInt('id', 0);
		$relatedDocumentIds = $app->getUserState("com_judownload.edit.document.related_documents", array());
		$related_documents  = array();
		if ($relatedDocumentIds)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('d.id, d.title, d.icon');
			$query->from('#__judownload_documents AS d');
			$query->where('d.id IN (' . implode(',', $relatedDocumentIds) . ')');
			$db->setQuery($query);
			$related_documents = $db->loadObjectList();
		}
		elseif ($doc_id)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('d.id, d.title, d.icon');
			$query->from('#__judownload_documents_relations AS drel');
			$query->join('INNER', '#__judownload_documents AS d ON drel.doc_id_related = d.id');
			$query->where('drel.doc_id = ' . $doc_id);
			$query->order('drel.ordering ASC');
			$db->setQuery($query);
			$related_documents = $db->loadObjectList();
		}

		if ($related_documents)
		{
			foreach ($related_documents AS $document)
			{
				$document->icon_src = JUDownloadHelper::getDocumentIcon($document->icon);
			}
		}

		return $related_documents;
	}

	
	public function getGalleryField()
	{
		$app          = JFactory::getApplication();
		$docId        = $app->input->getInt('id', 0);
		$galleryField = JUDownloadFrontHelperField::getField('gallery', $docId);
		if (($this->getDocumentSubmitType($docId) == 'submit' && $galleryField->canSubmit())
			|| ($this->getDocumentSubmitType($docId) == 'edit' && $galleryField->canEdit())
		)
		{
			return $galleryField;
		}

		return null;
	}

	
	public function feature(&$pks, $value = 1)
	{
		$app = JFactory::getApplication();
		
		if ($app->isSite())
		{
			return false;
		}

		
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

	
	public function _uploader()
	{
		
		error_reporting(0);

		
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		

		
		@set_time_limit(10 * 60);

		
		

		
		$targetDir = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/") . "tmp";

		$cleanupTargetDir = true; 
		$maxFileAge       = 5 * 3600; 

		
		if (!JFolder::exists($targetDir))
		{
			JFolder::create($targetDir);
			$indexHtml = $targetDir . 'index.html';
			$buffer    = "<!DOCTYPE html><title></title>";
			JFile::write($indexHtml, $buffer);
		}

		
		if (!is_writable($targetDir))
		{
			$targetDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "plupload";
			

			
			if (!file_exists($targetDir))
			{
				@mkdir($targetDir);
			}
		}

		
		if (isset($_REQUEST["name"]))
		{
			$fileName = $_REQUEST["name"];
		}
		elseif (!empty($_FILES))
		{
			$fileName = $_FILES["file"]["name"];
		}
		else
		{
			$fileName = uniqid("file_");
		}

		$filePath = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

		
		$chunk  = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

		
		if ($cleanupTargetDir)
		{
			if (!is_dir($targetDir) || !$dir = opendir($targetDir))
			{
				die('{"OK" : 0, "error" : {"code": 100, "message": "Failed to open temp directory."}}');
			}

			while (($file = readdir($dir)) !== false)
			{
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

				
				if ($tmpfilePath == "{$filePath}.part")
				{
					continue;
				}

				
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge))
				{
					JFile::delete($tmpfilePath);
				}
			}
			closedir($dir);
		}

		
		if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb"))
		{
			die('{"OK" : 0, "error" : {"code": 102, "message": "Failed to open output stream."}}');
		}

		if (!empty($_FILES))
		{
			if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"]))
			{
				die('{"OK" : 0, "error" : {"code": 103, "message": "Failed to move uploaded file."}}');
			}

			
			if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb"))
			{
				die('{"OK" : 0, "error" : {"code": 101, "message": "Failed to open input stream."}}');
			}
		}
		else
		{
			if (!$in = @fopen("php://input", "rb"))
			{
				die('{"OK" : 0, "error" : {"code": 101, "message": "Failed to open input stream."}}');
			}
		}

		while ($buff = fread($in, 4096))
		{
			fwrite($out, $buff);
		}

		@fclose($out);
		@fclose($in);

		
		if (!$chunks || $chunk == $chunks - 1)
		{
			
			rename("{$filePath}.part", $filePath);
		}

		
		die('{"OK" : 1}');
	}

	
	public function uploader()
	{
		
		error_reporting(0);

		JLoader::register('PluploadHandler', JPATH_SITE . '/components/com_judownload/helpers/pluploadhandler.php');

		
		$targetDir = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/") . "tmp";

		$cleanupTargetDir = true; 
		$maxFileAge       = 5 * 3600; 

		
		$this->cleanup($targetDir, $maxFileAge);

		
		if (!JFolder::exists($targetDir))
		{
			JFolder::create($targetDir);
			$indexHtml = $targetDir . 'index.html';
			$buffer    = "<!DOCTYPE html><title></title>";
			JFile::write($indexHtml, $buffer);
		}

		
		if (!is_writable($targetDir))
		{
			$targetDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "plupload";
			

			
			if (!file_exists($targetDir))
			{
				@mkdir($targetDir);
			}
		}

		PluploadHandler::no_cache_headers();
		PluploadHandler::cors_headers();

		if (!PluploadHandler::handle(array(
			'target_dir'    => $targetDir,
			'cleanup'       => $cleanupTargetDir,
			'max_file_age'  => $maxFileAge,
			'cb_check_file' => array(__CLASS__, 'canUpload'),
		))
		)
		{
			die(json_encode(array(
				'OK'    => 0,
				'error' => array(
					'code'    => PluploadHandler::get_error_code(),
					'message' => PluploadHandler::get_error_message()
				)
			)));
		}
		else
		{
			die(json_encode(array('OK' => 1)));
		}
	}

	
	private function cleanup($tmpDir, $maxFileAge = 18000)
	{
		
		if (JFolder::exists($tmpDir))
		{
			foreach (glob($tmpDir . '/*.*') AS $tmpFile)
			{
				if (basename($tmpFile) == 'index.html' || (time() - filemtime($tmpFile) < $maxFileAge))
				{
					continue;
				}

				if (is_dir($tmpFile))
				{
					JFolder::delete($tmpFile);
				}
				else
				{
					JFile::delete($tmpFile);
				}
			}
		}
	}

	public function uploadFileScript($doc_id = null, $selector = "#judl-files")
	{
		$params               = JUDownloadHelper::getParams(null, $doc_id);
		$max_upload_file_size = (int) $params->get("max_upload_file_size", 10) * 1024 * 1024;
		$post_max_size        = JUDownloadHelper::getPostMaxSize();
		
		if ($max_upload_file_size < $post_max_size)
		{
			$runtimes = 'html5,flash,silverlight,html4';
		}
		else
		{
			$runtimes = 'html5,silverlight,html4';
		}

		$chunk_size = JUDownloadHelper::getPostMaxSize() - 4000;

		$max_upload_files = 1;

		$legal_upload_extensions = $params->get("legal_upload_extensions", "bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,zip,rar");
		$legal_upload_extensions = str_replace("\n", ",", trim($legal_upload_extensions));

		$check_mime_uploaded_file = (int) $params->get("check_mime_uploaded_file", 0);
		$legal_mime_types         = $check_mime_uploaded_file ? $params->get("legal_mime_types", 'image/jpeg,image/gif,image/png,image/bmp,application/x-shockwave-flash,application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/zip,application/zip') : "";
		if ($legal_mime_types)
		{
			$legal_mime_types = str_replace("\n", ",", trim($legal_mime_types));
		}

		$requiredFile = $params->get("document_require_file", 1);

		$time     = time();
		$secret   = JFactory::getConfig()->get('secret');
		$code     = md5($time . $secret);
		$document = JFactory::getDocument();
		$app      = JFactory::getApplication();
		$script   = '
			jQuery(document).ready(function ($) {
				var options = {
					doc_id                   : ' . (int) $doc_id . ',
					juri_root                : "' . JUri::root() . '",
					juri_base                : "' . JUri::base() . '",
					runtimes                 : "' . $runtimes . '",
					chunk_size               : "' . $chunk_size . '",
					max_upload_file_size     : ' . $max_upload_file_size . ',
					max_upload_files         : ' . $max_upload_files . ',
					extensions               : "' . $legal_upload_extensions . '",
					mime_types               : "' . $legal_mime_types . '",
					is_required              : ' . (int) $requiredFile . ',
					time                     : ' . $time . ',
					code                     : "' . $code . '",
					is_site                  : ' . (int) $app->isSite() . '
				};
				$("' . $selector . '").files(options);
			});';
		$document->addScriptDeclaration($script);
	}

	
	public function getDocumentSubmitType($documentId, $documentObject = null, $isNew = null)
	{
		
		if (!is_null($isNew))
		{
			if ($isNew)
			{
				return 'submit';
			}
		}

		if ($documentId == 0)
		{
			return 'submit';
		}

		if (!is_object($documentObject))
		{
			$documentObject = JUDownloadHelper::getDocumentById($documentId);
		}

		
		if ($documentObject->approved == 0)
		{
			return 'submit';
		}
		
		else
		{
			return 'edit';
		}
	}
}
