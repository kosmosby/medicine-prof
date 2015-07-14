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

class JUDownloadModelCsvprocess extends JModelAdmin
{
	public $write_to_log_field = true;

	public $errors = array(
		0 => 'COM_JUDOWNLOAD_CSV_PROCESS_SKIP_IMPORT_BY_USER',
		1 => 'COM_JUDOWNLOAD_CSV_PROCESS_MISSING_REQUIRED_FIELDS_ERROR',
		2 => 'COM_JUDOWNLOAD_CSV_PROCESS_INSERT_DOCUMENT_ERROR'
	);

	public $errorInfo = array();

	public $field_name_id_array = array();

	public $messages = array(
		'message' => array(),
		'error'   => array(),
		'warning' => array(),
	);

	public function __construct($config = array())
	{
		parent::__construct($config);
		$app = JFactory::getApplication();

		
		$this->field_name_id_array = $app->getUserState('field_name_id_array', array());

		if (empty($this->field_name_id_array))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('field_name, id')
				->from('#__judownload_fields');

			$db->setQuery($query);
			$this->field_name_id_array = $db->loadAssocList('field_name', 'id');

			$app->setUserState('field_name_id_array', $this->field_name_id_array);
		}
	}

	
	public function getForm($data = array(), $loadData = true)
	{
		
		$form = $this->loadForm('com_judownload.csvconfig', 'csvconfig', array('control' => 'jform', 'load_data' => $loadData), true);
		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	
	public function getExportForm($data = array(), $loadData = true)
	{
		
		$form = $this->loadForm('com_judownload.csvexport', 'csvexport', array('control' => 'jform', 'load_data' => $loadData), true);
		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	
	public function getFieldsHaveFieldClass($type = 'core', $categoryId = null)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('field.id, field.field_name, field.caption, field.group_id');
		$query->from('#__judownload_fields AS field');

		switch ($type)
		{
			case "core":
				$query->where('field.group_id = 1');
				$query->where('field.field_name != \'cat_id\'');
				$query->where('field.field_name != \'comments\'');
				break;

			case "extra":
				if ($categoryId)
				{
					$query->join('', '#__judownload_categories AS c ON c.fieldgroup_id = field.group_id');
					$query->where('c.id = ' . $categoryId);
				}
				else
				{
					$query->where('field.group_id > 1');
				}
				break;
		}
		$query->order('ordering');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	
	public function getDocumentTableFieldsName($exceptedFields = null)
	{
		$db    = JFactory::getDbo();
		$query = 'SHOW COLUMNS FROM #__judownload_documents';

		$db->setQuery($query);
		$fieldsName = $db->loadColumn();

		
		$introtext = array_search('introtext', $fieldsName);
		unset($fieldsName[$introtext]);

		$fulltext = array_search('fulltext', $fieldsName);
		unset($fieldsName[$fulltext]);

		
		
		$assetId = array_search('asset_id', $fieldsName);
		unset($fieldsName[$assetId]);

		if (empty($exceptedFields))
		{
			return $fieldsName;
		}
		else
		{
			$fieldsNameNotExceptedFields = array();

			foreach ($fieldsName AS $fieldName)
			{
				$hasFieldClass = false;
				foreach ($exceptedFields AS $field)
				{
					if ($field->field_name == $fieldName)
					{
						$hasFieldClass = true;
						break;
					}
				}

				if (!$hasFieldClass)
				{
					$fieldsNameNotExceptedFields[] = $fieldName;
				}
			}

			return $fieldsNameNotExceptedFields;
		}

	}

	

	
	

	
	public function getAssignedColumns()
	{
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		
		$assignedColumns = $app->input->get('assign', array(), 'array');

		if (!empty($assignedColumns))
		{
			$db                  = JFactory::getDbo();
			$assignedColumnsName = array();

			foreach ($assignedColumns AS $key => $field)
			{
				if (is_numeric($field))
				{
					$query = $db->getQuery(true);
					$query->select('caption')
						->from('#__judownload_fields')
						->where('id = ' . $field);

					$db->setQuery($query);
					$fieldCaption = $db->loadResult();

					$assignedColumnsName[$key] = $fieldCaption;
				}
				else
				{
					$assignedColumnsName[$key] = $field;
				}
			}

			$app->setUserState('csv_assigned_columns', $assignedColumns);
			$app->setUserState('csv_assigned_columns_name', $assignedColumnsName);
		}

		return true;
	}

	
	public function getDefaultConfigs()
	{
		$app    = JFactory::getApplication();
		$config = $app->input->post->get('jform', array(), 'array');

		$files        = $app->input->files->get('jform', array(), 'array');
		$default_icon = $files['default_icon'];

		
		if (!empty($default_icon['name']))
		{
			
			$tmpDirIcon = $app->getUserState('csv_import_dir');
			$fileName   = $default_icon['name'];
			$fileDes    = $tmpDirIcon . $fileName;
			if (!JFile::upload($default_icon['tmp_name'], $fileDes))
			{
				$this->setError(JText::_("COM_JUDOWNLOAD_CSV_PROCESS_FAIL_TO_UPLOAD_DEFAULT_ICON"));

				return false;
			}

			$config['default_icon'] = JPath::clean($fileDes);
		}

		
		$saveOptions = $app->input->post->get('save_options', '', '');

		if ($saveOptions)
		{
			$config['save_options'] = $saveOptions;
		}

		
		$review = array(
			'csv_columns'               => $app->getUserState('csv_columns'),
			'csv_assigned_columns_name' => $app->getUserState('csv_assigned_columns_name'),
			'config'                    => $config,
			'csv_assigned_columns'      => $app->getUserState('csv_assigned_columns'),
		);

		$app->setUserState("csv_config", $config);

		
		$logFileName = "com_judownload.log." . date('Y-m-d H-i-s') . '.csv';
		$app->setUserState("csv_log_file_name", $logFileName);

		return $review;
	}

	
	public function importCSV()
	{
		$csvData = $this->combineCSVColumnNameWithColumnValue();

		$app = JFactory::getApplication();

		$start  = $app->input->getInt('start', 0);
		$config = $app->getUserState("csv_config");

		$this->import($csvData, $config, $start);

		$totalCSVRows = $app->getUserState('csv_total_rows');

		
		if ($start + $config['limit'] >= $totalCSVRows)
		{
			if (JFolder::exists($app->getUserState("csv_import_dir")))
			{
				JFolder::delete($app->getUserState("csv_import_dir"));
			}
		}

		
		$messages = $this->messages;

		return array(
			'processed' => $config['limit'],
			'error'     => $messages['error'],
			'message'   => $messages['message'],
			'warning'   => $messages['warning'],
			'total'     => $totalCSVRows
		);
	}

	
	public function combineCSVColumnNameWithColumnValue()
	{
		if (JUDownloadHelper::hasCSVPlugin())
		{
			$JUDownloadCsv = new JUDownloadCSV($this);
			$result        = $JUDownloadCsv->combineCSVColumnNameWithColumnValue();

			return $result;
		}

		return false;
	}

	
	public function import($importData, $config = array(), $start = 0)
	{
		if (empty($importData))
		{
			return false;
		}

		$insertedUpdatedIds = array();
		$indexRow           = $start + 1;

		foreach ($importData AS $index => $data)
		{
			$indexRow++;

			if (is_object($data))
			{
				$data = get_object_vars($data);
			}

			
			$importType = $this->getImportType($data, $config);

			
			
			
			if (!is_numeric($importType))
			{
				
				$this->addLog(0, $indexRow, $importType, 'message');

				continue;
			}

			$isInsert = $importType == 1 ? true : false;

			
			if (empty($data['main_cat']))
			{
				
				
				if (!$isInsert)
				{
					$data['main_cat'] = JUDownloadFrontHelperCategory::getMainCategoryId($data['id']);
				}
				elseif (!empty($config['main_cat']))
				{
					$data['main_cat'] = $config['main_cat'];
				}
				else
				{
					$this->addLog(2, $index, JText::_('COM_JUDOWNLOAD_CSV_PROCESS_EMPTY_CAT'));

					continue;
				}
			}

			
			
			$data[$this->field_name_id_array['cat_id']] = array('main' => $data['main_cat'], 'secondary' => '');

			
			$isValidData = $this->validateData($data, $isInsert);

			
			if ($isValidData !== true)
			{
				
				$this->addLog(1, $indexRow, $isValidData);

				continue;
			}


			
			$data = $this->prepareDataToImport($data, $isInsert, $config);

			
			$result = $this->insertUpdateDocument($data, $isInsert);

			
			if ($result['doc_id'] > 0)
			{
				$insertedUpdatedIds[] = $result['doc_id'];
			}

			
			if (!empty($result['messages']))
			{
				$this->addLog(2, $indexRow, $result['messages']);
			}
		}

		if ($this->write_to_log_field)
		{
			
			$this->writeToLogFile();
		}

		return $insertedUpdatedIds;
	}

	
	public function getImportType(&$data, $config)
	{
		
		$idFieldId = $this->field_name_id_array['id'];

		if (empty($data[$idFieldId]))
		{
			$docId = 0;

			$type = 1;
		}
		else
		{
			$docId = $data[$idFieldId];

			$document = JUDownloadHelper::getDocumentById($docId);

			if (!$document)
			{
				$docId = 0;

				$type = 1;
			}
			else
			{
				
				$saveOption = isset($config['save_options']) ? $config['save_options'] : 'keep';

				switch ($saveOption)
				{
					
					case 'skip':
						$type = JText::_('COM_JUDOWNLOAD_CSV_PROCESS_SKIP_DOCUMENT_MESSAGE');
						break;

					
					case 'replace':
						$type = 0;
						break;

					
					case 'keep':
					default:
						$docId = 0;

						$type = 1;
						break;
				}
			}

		}

		$data[$idFieldId] = $docId;

		
		$data['id'] = $docId;

		return $type;
	}

	
	public function validateData(&$data, $isInsert, $checkRequire = false)
	{
		
		foreach ($data AS $key => $value)
		{
			if ($key == 'ignore' || $key == 'asset_id')
			{
				unset($data[$key]);
			}
		}

		
		$mainCatId = $data['main_cat'];

		$catObj = JUDownloadHelper::getCategoryById($mainCatId);
		if (!$catObj)
		{
			return JText::_('COM_JUDOWNLOAD_CSV_PROCESS_INVALID_CAT');
		}

		$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
		$params  = JUDownloadHelper::getParams();
		if ($data['main_cat'] == $rootCat->id && !$params->get('allow_add_doc_to_root', 0))
		{
			return JText::_('COM_JUDOWNLOAD_CSV_PROCESS_CANT_INSERT_ROOT_CAT');
		}

		
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select("field.*");
		$query->from("#__judownload_fields AS field");
		$query->select("plg.folder");
		$query->join("", "#__judownload_plugins AS plg ON field.plugin_id = plg.id");
		$query->join("", "#__judownload_fields_groups AS field_group ON field_group.id = field.group_id");
		
		$query->where("field.field_name != 'cat_id'");
		
		$query->where("field.field_name != 'id'");
		$query->group('field.id');
		$query->order('field.id ASC');
		$db->setQuery($query);
		$fields = $db->loadObjectList();

		foreach ($fields AS $field)
		{
			if (isset($data[$field->id]))
			{
				$fieldObj              = JUDownloadFrontHelperField::getField($field, $data['id']);
				$fieldObj->is_new_doc  = $isInsert;
				$fieldObj->fields_data = $data;

				if ($field->field_name == 'description')
				{
					$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
					$tagPos  = preg_match($pattern, $data[$field->id]);

					if ($tagPos == 0)
					{
						$introtext = $data[$field->id];
						$fulltext  = '';
					}
					else
					{
						list ($introtext, $fulltext) = preg_split($pattern, $data[$field->id], 2);
					}

					$fieldValue            = new stdClass();
					$fieldValue->introtext = $introtext;
					$fieldValue->fulltext  = $fulltext;

				}
				else
				{
					$fieldValue = $data[$field->id];
				}

				
				$fieldValue = $fieldObj->parseValue($fieldValue);
				
				$fieldValue = $fieldObj->filterField($fieldValue);
				
				$valid = $fieldObj->PHPValidate($fieldValue);

				if ($valid !== true)
				{
					
					if ($checkRequire)
					{
						
						return $valid;
					}
					else
					{
						
						$data[$field->id] = '';
					}
				}
				else
				{
					$data[$field->id] = $fieldValue;
				}
			}
		}

		return true;
	}

	
	public function prepareDataToImport($data, $isInsert, $config = array())
	{
		$db = JFactory::getDbo();

		
		
		$defaultData = array('cat_id' => $data['main_cat']);

		
		if ($isInsert)
		{
			$user   = JFactory::getUser();
			$offset = $user->getParam('timezone', JFactory::getConfig()->get('offset'));

			
			
			if (!empty($config['publish_up']))
			{
				$defaultData['publish_up'] = JFactory::getDate($config['publish_up'], $offset)->toSql();
			}

			
			if (!empty($config['force_publish']))
			{
				
				$data[$this->field_name_id_array['published']] = $config['force_publish'];
			}

			if (!empty($config['publish_down']) && $config['publish_down'] != $db->getNullDate())
			{
				$defaultData['publish_down'] = JFactory::getDate($config['publish_down'], $offset)->toSql();
			}

			if (!empty($config['created_by']) && $config['created_by'] > 0)
			{
				$defaultData['created_by'] = $config['created_by'];
			}

			if (!empty($config['default_icon']))
			{
				$defaultData['icon'] = $config['default_icon'];
			}

			
			if (empty($defaultData['metadescription']) && !empty($config['meta_description']))
			{
				$defaultData['metadescription'] = $config['meta_description'];
			}

			
			if (empty($defaultData['metakeyword']) && !empty($config['meta_keyword']))
			{
				$defaultData['metakeyword'] = $config['meta_keyword'];
			}

			if (empty($data['secondary_cats']) && !empty($config['secondary_cats_assign']))
			{
				$data['secondary_cats'] = $config['secondary_cats_assign'];
			}

			if (empty($defaultData['language']))
			{
				$defaultData['language'] = '*';
			}

			if (empty($defaultData['access']))
			{
				$defaultData['access'] = 1;
			}

			if (empty($defaultData['style_id']))
			{
				$defaultData['style_id'] = -1;
			}

			if (empty($defaultData['layout']))
			{
				$defaultData['layout'] = -1;
			}

			if (empty($defaultData['params']))
			{
				$params = array(
					'display_params' => array(
						'show_comment' => -2,
						'fields'       => array(
							'title'   => array(
								'detail_view' => -2,
							),
							'created' => array(
								'detail_view' => -2,
							),
							'author'  => array(
								'detail_view' => -2,
							),
							'cat_id'  => array(
								'detail_view' => -2,
							),
							'rating'  => array(
								'detail_view' => -2,
							),
						),
					),
				);

				$defaultData['params'] = json_encode($params);
			}
		}

		
		if (!empty($data['secondary_cats']))
		{
			if (is_string($data['secondary_cats']))
			{
				$data['secondary_cats'] = explode(',', $data['secondary_cats']);
			}

			
			$index = array_search($data['main_cat'], $data['secondary_cats']);
			if ($index)
			{
				unset($data['secondary_cats'][$index]);
			}

			$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
			$params  = JUDownloadHelper::getParams();

			
			$index = array_search($rootCat->id, $data['secondary_cats']);
			if ($index && !$params->get('allow_add_doc_to_root', 0))
			{
				unset($data['secondary_cats'][$index]);
			}

			
			$data[$this->field_name_id_array['cat_id']]['secondary'] = implode(',', $data['secondary_cats']);

		}

		
		
		
		$aliasFieldId = $this->field_name_id_array['alias'];
		$titleFieldId = $this->field_name_id_array['title'];

		if (!$isInsert)
		{
			$oldMainCatId = JUDownloadFrontHelperCategory::getMainCategoryId($data['id']);

			
			if ($data['main_cat'] != $oldMainCatId)
			{
				if (empty($data[$titleFieldId]))
				{
					$document            = JUDownloadHelper::getDocumentById($data['id']);
					$data[$titleFieldId] = $document->title;
				}

				if (empty($data[$aliasFieldId]))
				{
					$document            = JUDownloadHelper::getDocumentById($data['id']);
					$data[$aliasFieldId] = $document->alias;
				}
			}
		}

		if (empty($data[$aliasFieldId]))
		{
			$data[$aliasFieldId] = strtolower(JApplication::stringURLSafe($data[$titleFieldId]));
		}

		$this->titleIncrement($data['main_cat'], $data['id'], $data[$aliasFieldId], $data[$titleFieldId]);

		
		$data['gallery']      = !empty($data['gallery']) ? explode('|', $data['gallery']) : array();
		$data['files']        = !empty($data['files']) ? explode('|', $data['files']) : array();
		$data['related_docs'] = !empty($data['related_docs']) ? explode(',', $data['related_docs']) : array();

		
		
		if (!empty($data['style_id']) && !$isInsert)
		{
			
			$oldTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfDocument($data['id']);
			$styleId                = $data['style_id'];
			if ($styleId == -2)
			{
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getDefaultTemplateStyle();
			}
			elseif ($styleId == -1)
			{
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleOfCategory($data['main_cat']);
			}
			else
			{
				$newTemplateStyleObject = JUDownloadFrontHelperTemplate::getTemplateStyleObject($styleId);
			}

			if ($oldTemplateStyleObject->template_id != $newTemplateStyleObject->template_id && !isset($data['template_params']))
			{
				$data['template_params'] = '';
			}
		}

		
		if (!empty($data['template_params']) && is_array($data['template_params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($data['template_params']);
			$data['template_params'] = $registry->toString();
		}

		
		$data['plugin_params'] = !empty($data['plugin_params']) ? $data['plugin_params'] : array();
		if (!is_array($data['plugin_params']))
		{
			$registry = new JRegistry;
			$registry->loadString($data['plugin_params']);
			$data['plugin_params'] = $registry->toArray();
		}

		$db_plugin_params = array();
		if (!$isInsert)
		{
			$db->setQuery("SELECT plugin_params FROM #__judownload_documents WHERE id = " . $data['id']);
			$rule_str      = $db->loadResult();
			$rule_registry = new JRegistry;
			$rule_registry->loadString($rule_str);
			$db_plugin_params = $rule_registry->toArray();
		}

		if (!empty($db_plugin_params))
		{
			$db->setQuery("SELECT element FROM #__extensions WHERE type='plugin' AND folder = 'judownload'");
			$rule_plugins = $db->loadColumn();

			foreach ($db_plugin_params AS $name => $value)
			{
				
				if (!in_array($name, $rule_plugins))
				{
					unset($db_plugin_params[$name]);
				}

				
				if (array_key_exists($name, $data['plugin_params']))
				{
					unset($db_plugin_params[$name]);
				}
			}
		}
		$plugin_params = array_merge($db_plugin_params, $data['plugin_params']);
		$registry      = new JRegistry;
		$registry->loadArray($plugin_params);
		$data['plugin_params'] = $registry->toString();

		
		$fieldsData = array();

		foreach ($data AS $key => $value)
		{
			
			if (is_numeric($key))
			{
				$fieldsData[$key] = $value;
			}
			
			elseif (is_string($key)
				&&
				$key != 'files'
				&&
				$key != 'gallery'
				&&
				$key != 'main_cat'
				&&
				$key != 'secondary_cats'
				&&
				$key != 'related_docs'
			)
			{
				$defaultData[$key] = $value;
			}
		}

		
		$postData = array(
			'main_cat'     => $data['main_cat'],
			'data'         => $defaultData,
			'fieldsData'   => $fieldsData,
			'files'        => $data['files'],
			'gallery'      => $data['gallery'],
			'related_docs' => $data['related_docs']
		);

		return $postData;
	}

	
	public function insertUpdateDocument($data, $isInsert = true)
	{
		$db          = JFactory::getDbo();
		$iconDir     = JPATH_ROOT . '/' . JUDownloadFrontHelper::getDirectory('document_icon_directory', 'media/com_judownload/images/document/');
		$originalDir = $iconDir . 'original/';

		$newMainCatId = $data['main_cat'];
		$gallery      = $data['gallery'];
		$files        = $data['files'];
		$fieldsData   = $data['fieldsData'];
		$relatedDocs  = $data['related_docs'];
		$data         = $data['data'];

		$messages = array();

		
		
		if (!$isInsert)
		{
			$docObj = JUDownloadHelper::getDocumentById($data['id']);
			if ($docObj->cat_id != $newMainCatId)
			{
				$oldFieldGroup = JUDownloadHelper::getCategoryById($docObj->cat_id);
				$newFieldGroup = JUDownloadHelper::getCategoryById($newMainCatId);

				
				if ($oldFieldGroup->fieldgroup_id != $newFieldGroup->fieldgroup_id)
				{
					$query = $db->getQuery(true);

					$query->select("field.*");
					$query->from("#__judownload_fields AS field");
					$query->select("plg.folder");
					$query->join("", "#__judownload_plugins AS plg ON field.plugin_id = plg.id");
					$query->join("", "#__judownload_categories AS c ON (c.fieldgroup_id = field.group_id AND field.group_id != 1)");
					$query->join("", "#__judownload_documents_xref AS dxref ON (dxref.cat_id = c.id AND dxref.main = 1)");
					$query->join("", "#__judownload_documents AS d ON dxref.doc_id = d.id");
					$query->where("d.id = " . $data['id']);
					$query->group('field.id');
					$query->order('field.ordering');
					$db->setQuery($query);
					$fields = $db->loadObjectList();
					foreach ($fields AS $field)
					{
						$fieldObj = JUDownloadFrontHelperField::getField($field, $data['id']);
						$fieldObj->onDelete();
					}
				}
			}
		}

		

		
		$iconPath    = '';
		$iconFieldId = $this->field_name_id_array['icon'];

		if (!empty($data['icon']))
		{
			$iconPath = $data['icon'];
			unset($data['icon']);
		}

		if (!empty($fieldsData[$iconFieldId]))
		{
			$iconPath = $fieldsData[$iconFieldId];
			unset($fieldsData[$iconFieldId]);
		}


		$table = JTable::getInstance("Document", "JUDownloadTable");

		if (!$table->bind($data) || !$table->check() || !$table->store())
		{
			return array('doc_id' => 0, 'messages' => $table->getErrors());
		}

		$docId = $table->id;

		
		$categoriesField             = new JUDownloadFieldCore_categories(null, $docId);
		$categoriesField->is_new_doc = $isInsert;
		$result                      = $categoriesField->storeValue($fieldsData[$this->field_name_id_array['cat_id']]);

		if (!$result)
		{
			
			$table->delete($docId);

			return array('doc_id' => 0, 'messages' => $db->getErrorMsg());
		}

		
		$query = $db->getQuery(true);
		$query->select("field.*");
		$query->from("#__judownload_fields AS field");
		$query->select("plg.folder");
		$query->join("", "#__judownload_plugins AS plg ON field.plugin_id = plg.id");
		$query->join("", "#__judownload_categories AS c ON (c.fieldgroup_id = field.group_id OR field.group_id = 1)");
		$query->join("", "#__judownload_documents_xref AS dxref ON (dxref.cat_id = c.id AND dxref.main = 1)");
		$query->join("", "#__judownload_documents AS d ON dxref.doc_id = d.id");
		$query->where("d.id = $docId");
		
		$query->where("field.field_name != 'id'");
		
		$query->where("field.field_name != 'cat_id'");

		$query->group('field.id');
		$query->order('ordering ASC');
		$db->setQuery($query);
		$fields = $db->loadObjectList();
		
		$docObj = JUDownloadHelper::getDocumentById($docId);

		foreach ($fields AS $field)
		{
			if (isset($fieldsData[$field->id]))
			{
				$fieldObj              = JUDownloadFrontHelperField::getField($field, $docObj);
				$fieldObj->fields_data = $fieldsData;
				$fieldValue            = $fieldsData[$field->id];
				
				$fieldObj->is_new_doc = $isInsert;
				$fieldValue           = $fieldObj->onImportDocument($fieldValue);
				$fieldObj->storeValue($fieldValue);
			}
		}

		
		if ($iconPath)
		{
			$iconPath = JUDownloadHelper::getPhysicalPath($iconPath);

			if (!$iconPath)
			{
				$messages[] = JText::sprintf('COM_JUDOWNLOAD_CSV_PROCESS_FILE_S_NOT_FOUND', $iconPath);
			}
			else
			{
				
				if ($data['id'] > 0)
				{
					if ($table->icon)
					{
						if (JFile::exists($iconDir . $table->icon))
						{
							JFile::delete($iconDir . $table->icon);
							JFile::delete($originalDir . $table->icon);
						}
					}
				}

				$iconName = basename($iconPath);
				$iconName = $docId . "_" . JUDownloadHelper::fileNameFilter($iconName);
				if (JFile::copy($iconPath, $originalDir . $iconName) && JUDownloadHelper::renderImages($originalDir . $iconName, $iconDir . $iconName, 'document_icon', true, null, $data['id']))
				{
					$table->icon = $iconName;
					$table->store();
				}
			}

		}

		
		$imageTable = JTable::getInstance("Image", "JUDownloadTable");

		
		if (!empty($gallery))
		{
			$dir_document_ori   = JPATH_SITE . "/" . JUDownloadFrontHelper::getDirectory("document_original_image_directory", "media/com_judownload/images/gallery/original/") . $docId . "/";
			$dir_document_small = JPATH_SITE . "/" . JUDownloadFrontHelper::getDirectory("document_small_image_directory", "media/com_judownload/images/gallery/small/") . $docId . "/";
			$dir_document_big   = JPATH_SITE . "/" . JUDownloadFrontHelper::getDirectory("document_big_image_directory", "media/com_judownload/images/gallery/big/") . $docId . "/";
			if (!JFolder::exists($dir_document_ori))
			{
				$file_index = $dir_document_ori . 'index.html';
				$buffer     = "<!DOCTYPE html><title></title>";
				JFile::write($file_index, $buffer);
			}

			if (!JFolder::exists($dir_document_small))
			{
				$file_index = $dir_document_small . 'index.html';
				$buffer     = "<!DOCTYPE html><title></title>";
				JFile::write($file_index, $buffer);
			}

			if (!JFolder::exists($dir_document_big))
			{
				$file_index = $dir_document_big . 'index.html';
				$buffer     = "<!DOCTYPE html><title></title>";
				JFile::write($file_index, $buffer);
			}

			$image_ordering = 1;

			$date = JFactory::getDate();
			foreach ($gallery AS $imagePath)
			{
				$imagePath = JUDownloadHelper::getPhysicalPath($imagePath);

				if (!$imagePath)
				{
					$messages[] = JText::sprintf('COM_JUDOWNLOAD_CSV_PROCESS_FILE_S_NOT_FOUND', $imagePath);

					continue;
				}

				
				$imageName = basename($imagePath);
				$imageName = JUDownloadHelper::generateImageNameByDocument($docId, $imageName);
				if (JFile::copy($imagePath, $dir_document_ori . $imageName)
					&& JUDownloadHelper::renderImages($dir_document_ori . $imageName, $dir_document_small . $imageName, 'document_small', true, null, $data['id'])
					&& JUDownloadHelper::renderImages($dir_document_ori . $imageName, $dir_document_big . $imageName, 'document_big', true, null, $data['id'])
				)
				{
					$imageObj            = new stdClass();
					$imageObj->id        = 0;
					$imageObj->file_name = $imageName;
					$imageObj->doc_id    = $docId;
					$imageObj->ordering  = $image_ordering;
					$imageObj->created   = $date->toSql();
					$imageObj->published = 1;

					if (!$imageTable->bind($imageObj) || !$imageTable->check() || !$imageTable->store())
					{
						$messages[] = implode(' | ', $imageTable->getErrors());

						
						JFile::delete($dir_document_ori . $imageName);
						JFile::delete($dir_document_small . $imageName);
						JFile::delete($dir_document_big . $imageName);
					}
					else
					{
						$image_ordering++;
					}
				}
				else
				{
					$messages[] = JText::sprintf('COM_JUDOWNLOAD_CSV_PROCESS_CAN_NOT_COPY_FILE_FROM_S_TO_S', $imagePath, $dir_document_ori . $imageName);
				}
			}
		}


		
		$fileTable = JTable::getInstance("File", "JUDownloadTable");

		
		if (!empty($files))
		{
			$file_directory = JPATH_SITE . "/" . JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/");
			if (!JFolder::exists($file_directory . $docId . "/"))
			{
				$file_index = $file_directory . $docId . "/index.html";
				$buffer     = "<!DOCTYPE html><title></title>";
				JFile::write($file_index, $buffer);
			}

			$file_ordering = 1;

			foreach ($files AS $filePath)
			{
				$filePath = JUDownloadHelper::getPhysicalPath($filePath);

				if (!$filePath)
				{
					$messages[] = JText::sprintf('COM_JUDOWNLOAD_CSV_PROCESS_FILE_S_NOT_FOUND', $filePath);

					continue;
				}
				
				$query = $db->getQuery(true);
				$query->select('COUNT(*)')
					->from('#__judownload_files')
					->where('doc_id = ' . $docId)
					->where('`rename` = ' . $db->quote(basename($filePath)));

				$db->setQuery($query);
				$count = $db->loadResult();

				if ($count)
				{
					$messages[] = JText::sprintf('COM_JUDOWNLOAD_CSV_PROCESS_EXISTED_FILE_S', $filePath);

					continue;
				}

				$fileObject              = array();
				$fileObject['id']        = 0;
				$fileObject['doc_id']    = $docId;
				$fileObject['ordering']  = $file_ordering;
				$fileObject['rename']    = basename($filePath);
				$fileObject['file_name'] = md5($fileObject['rename'] . JUDownloadHelper::generateRandomString(10)) . "." . JFile::getExt($fileObject['rename']);
				$fileObject['published'] = 1;

				$this->addFileInfo($fileObject, $filePath, $file_directory);

				$dest = $file_directory . $docId . "/" . $fileObject['file_name'];

				if ($fileTable->bind($fileObject) && $fileTable->check() && $fileTable->store())
				{
					if (!JFile::copy($filePath, $dest))
					{
						$fileTable->delete($fileTable->id);

						$messages[] = JText::sprintf('COM_JUDOWNLOAD_CSV_PROCESS_CAN_NOT_COPY_FILE_FROM_S_TO_S', $filePath, $dest);
					}

					$file_ordering++;
				}
				else
				{
					$messages[] = implode(' | ', $fileTable->getErrors());
				}
			}
		}


		
		if (!empty($relatedDocs))
		{
			$ordering = 1;
			foreach ($relatedDocs AS $relatedDocId)
			{
				$document = JUDownloadHelper::getDocumentById($relatedDocId);
				if ($document)
				{
					$relatedDocObj = new stdClass();

					$relatedDocObj->id             = 0;
					$relatedDocObj->doc_id         = $docId;
					$relatedDocObj->doc_id_related = $relatedDocId;
					$relatedDocObj->ordering       = $ordering;

					$query = $db->getQuery(true);
					$query->select('id')
						->from('#__judownload_documents_relations')
						->where('doc_id = ' . $docId)
						->where('doc_id_related = ' . $relatedDocId);

					$db->setQuery($query);
					$existedId = $db->loadResult();

					if ($existedId)
					{
						$relatedDocObj->id = $existedId;
						$db->updateObject('#__judownload_documents_relations', $relatedDocObj, 'id');
					}
					else
					{
						$db->insertObject('#__judownload_documents_relations', $relatedDocObj);
					}

					$relationId = $db->insertid();

					if ($relationId > 0)
					{
						$ordering++;
					}
					else
					{
						$messages[] = $db->getErrorMsg();
					}
				}
				else
				{
					$messages[] = JText::sprintf('COM_JUDOWNLOAD_CSV_PROCESS_INVALID_RELATED_DOCUMENT_N', $relatedDocId);
				}
			}
		}

		return array('doc_id' => $docId, 'messages' => $messages);
	}

	
	public function writeToLogFile()
	{
		$app = JFactory::getApplication();

		$logPath = $app->get('log_path', JPATH_SITE . '/logs') . '/' . $app->getUserState('csv_log_file_name', "com_judownload.log." . date('Y-m-d H-i-s') . '.csv');

		if (!empty($this->errorInfo))
		{
			if (!JFolder::exists(dirname($logPath)))
			{
				JFolder::create(dirname($logPath));

				$file_index = dirname($logPath) . '/index.html';
				$buffer     = "<!DOCTYPE html><title></title>";
				JFile::write($file_index, $buffer);
			}

			if (!JFile::exists($logPath))
			{
				array_unshift($this->errorInfo, array('Time', 'Row', 'Error number', 'Message', 'Detail'));
			}

			
			$this->array2csv($this->errorInfo, $logPath, 'a');
		}

	}

	
	public function titleIncrement($catId, $docId, &$alias, &$title)
	{
		$db = $this->getDbo();

		do
		{
			$query = $db->getQuery(true);
			$query->select('COUNT(*)')
				->from('#__judownload_documents AS d')
				->join('', '#__judownload_documents_xref AS dxref on d.id = dxref.doc_id')
				->where('dxref.cat_id = ' . $catId)
				->where('dxref.main = 1')
				->where('d.title = ' . $db->quote($title))
				->where('d.alias = ' . $db->quote($alias));
			if ($docId > 0)
			{
				$query->where('d.id != ' . $docId);
			}

			$db->setQuery($query);
			$result = $db->loadResult();

			if ($result > 0)
			{
				$title = JString::increment($title);
				$alias = JString::increment($alias, 'dash');
			}

		} while ($result);

		return true;
	}


	
	public function addLog($errorN, $rowN, $message = '', $type = 'error')
	{
		$offset            = JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));
		$date              = JFactory::getDate('now', $offset);
		$error             = array(
			'datetime' => $date,
			'row'      => $rowN,
			'error'    => $errorN,
			'message'  => JText::_($this->errors[$errorN]),
			'detail'   => is_array($message) ? implode(' | ', $message) : $message,
		);
		$this->errorInfo[] = $error;

		
		if (!empty($message))
		{
			if (is_array($message))
			{
				$message = implode('<br/>', $message);
			}

			switch ($type)
			{
				case 'error':
					$this->messages['error'][] = JText::sprintf('COM_JUDOWNLOAD_CSV_PROCESS_AT_N_S_ERROR', $rowN, $message);

					break;

				case 'message':
					$this->messages['message'][] = JText::sprintf('COM_JUDOWNLOAD_CSV_PROCESS_AT_N_S_MESSAGE', $rowN, $message);

					break;

				case 'warning':
					$this->messages['warning'][] = JText::sprintf('COM_JUDOWNLOAD_CSV_PROCESS_AT_N_S_WARNING', $rowN, $message);
					break;
			}
		}
	}





	
	public function export()
	{
		if (JUDownloadHelper::hasCSVPlugin())
		{
			$judownloadCsv = new JUDownloadCSV($this);
			$judownloadCsv->export();
		}

		return true;
	}

	
	public function getExportData($exportColumns, $filter)
	{
		$db         = JFactory::getDbo();
		$exportData = array();

		
		$selectFields = $this->getDocumentTableFieldsName();

		
		foreach ($selectFields AS $index => $field)
		{
			
			if ($field == 'id')
			{
				continue;
			}

			
			if (isset($this->field_name_id_array[$field]))
			{
				$fieldId = $this->field_name_id_array[$field];

				if (!in_array($fieldId, $exportColumns))
				{
					unset($selectFields[$index]);
				}
				else
				{
					$indexField = array_search($fieldId, $exportColumns);
					unset($exportColumns[$indexField]);
				}
			}
			
			else
			{
				if (!in_array($field, $exportColumns))
				{
					unset($selectFields[$index]);
				}
				else
				{
					$indexField = array_search($field, $exportColumns);
					unset($exportColumns[$indexField]);
				}
			}
		}

		if (in_array($this->field_name_id_array['description'], $exportColumns))
		{
			
			$selectFields[] = 'introtext';
			$selectFields[] = 'fulltext';

			$indexField = array_search($this->field_name_id_array['description'], $exportColumns);
			unset($exportColumns[$indexField]);
		}

		
		$query = $this->_prepareQuery($selectFields, $filter);

		$limit = 0;
		if (!empty($filter['csv_limit_export']) && is_numeric($filter['csv_limit_export']))
		{
			$limit = $filter['csv_limit_export'];
		}

		$db->setQuery($query, 0, $limit);

		$documents = $db->loadObjectList();

		if (!empty($documents))
		{
			foreach ($documents AS $document)
			{
				$docId = $document->id;
				
				$mainCatId = JUDownloadFrontHelperCategory::getMainCategoryId($docId);
				$data      = get_object_vars($document);

				
				if (!in_array($this->field_name_id_array['id'], $exportColumns))
				{
					unset($data['id']);
				}

				
				if (isset($data['introtext']))
				{
					$data['description'] = !empty($data['fulltext']) ? $data['introtext'] . "<hr id=\"system-readmore\" />" . $data['fulltext'] : $data['introtext'];

					unset($data['introtext']);
					unset($data['fulltext']);
				}

				
				if (in_array($this->field_name_id_array['rating'], $exportColumns))
				{
					$query = $db->getQuery(true);
					$query->select('score')
						->from('#__judownload_rating')
						->where('doc_id = ' . $docId);
					$db->setQuery($query);
					$data['rating'] = $db->loadResult();
				}

				
				
				foreach ($exportColumns AS $fieldId)
				{
					if (is_numeric($fieldId)
						&&
						$fieldId != $this->field_name_id_array['id']
						&&
						$fieldId != $this->field_name_id_array['tags']
					)
					{
						$query = $db->getQuery(true);
						$query->select('value')
							->from('#__judownload_fields_values')
							->where('doc_id = ' . $docId)
							->where('field_id = ' . $fieldId);

						$db->setQuery($query);
						$data['field_' . $fieldId] = $db->loadColumn();
					}

				}

				
				if (in_array($this->field_name_id_array['tags'], $exportColumns))
				{
					$query = $db->getQuery(true);
					$query->select('t.title')
						->from('#__judownload_tags AS t')
						->join('', '#__judownload_tags_xref AS txref ON txref.tag_id = t.id')
						->where('txref.doc_id = ' . $docId);

					$db->setQuery($query);
					$tags = $db->loadColumn();

					$data['tags'] = $tags;
				}


				
				if (in_array('main_cat', $exportColumns))
				{
					$data['main_cat'] = $mainCatId;
				}

				
				if (in_array('secondary_cats', $exportColumns))
				{
					$query = $db->getQuery(true);
					$query->select("c.id");
					$query->from("#__judownload_categories AS c");
					$query->join("", "#__judownload_documents_xref AS dxref ON (c.id = dxref.cat_id)");
					$query->where("dxref.doc_id = " . $docId);
					$query->where("dxref.main = 0");
					$query->order('dxref.ordering ASC');

					$db->setQuery($query);
					$cats = $db->loadColumn();

					if (!empty($cats))
					{
						$data['secondary_cats'] = $cats;
					}
					else
					{
						$data['secondary_cats'] = '';
					}
				}

				
				if (in_array('gallery', $exportColumns))
				{
					$query = $db->getQuery(true);
					$query->select('file_name')
						->from('#__judownload_images')
						->where('doc_id = ' . $docId);

					$db->setQuery($query);
					$imagesName = $db->loadColumn();

					foreach ($imagesName AS $index => $imageName)
					{
						$imagesName[$index] = JUDownloadFrontHelper::getDirectory('document_original_image_directory', 'media/com_judownload/images/gallery/original/', true) . $docId . '/' . $imageName;
					}

					$data['gallery'] = $imagesName;
				}


				
				if (in_array('files', $exportColumns))
				{
					$query = $db->getQuery(true);
					$query->select('file_name')
						->from('#__judownload_files')
						->where('doc_id = ' . $docId);

					$db->setQuery($query);
					$filesName = $db->loadColumn();

					foreach ($filesName AS $index => $fileName)
					{
						$filesName[$index] = JUDownloadFrontHelper::getDirectory("file_directory", "media/com_judownload/files/", true) . $docId . '/' . $fileName;
					}

					$data['files'] = $filesName;
				}

				
				if (in_array('related_docs', $exportColumns))
				{
					$query = $db->getQuery(true);
					$query->select('doc_id_related')
						->from('#__judownload_documents_relations')
						->where('doc_id = ' . $docId);

					$db->setQuery($query);
					$relatedDocs = $db->loadColumn();

					$data['related_docs'] = $relatedDocs;
				}

				$exportData[] = $this->filterExportFieldValue($data);
			}
		}

		$columns = array_keys($exportData[0]);
		array_unshift($exportData, $columns);

		return $exportData;
	}

	public function removeUtf8Bom($text)
	{
		$bom  = pack('H*', 'EFBBBF');
		$text = preg_replace("/^$bom/", '', $text);

		return $text;
	}

	
	protected function _prepareQuery($fieldNames, $filter)
	{
		
		if (empty($fieldNames))
		{
			return false;
		}

		$db = $this->getDbo();

		$fieldNames = array_map(function ($element)
		{
			$element = 'd.' . $element;

			return $element;

		}, $fieldNames);

		

		
		$catIds = isset($filter['csv_cat_filter']) ? $filter['csv_cat_filter'] : array();

		
		$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
		if (count($catIds) == 1 && $catIds[0] == $rootCat->id)
		{
			$catIds = array();
		}

		
		if (isset($filter['csv_sub_cat']) && !empty($catIds))
		{
			$tmp = array();

			foreach ($catIds AS $catId)
			{
				$categoryTree = JUDownloadHelper::getCategoryTree($catId);
				foreach ($categoryTree AS $category)
				{
					$tmp[] = $category->id;
				}
			}

			$catIds = array_unique(array_merge($catIds, $tmp));
		}

		
		$tags = $filter['csv_tag_filter'];
		$tags = array_filter($tags);

		
		$licenses = array_filter($filter['licenses']);

		

		$query = $db->getQuery(true);
		$query->select(implode(',', $fieldNames))
			->from("#__judownload_documents AS d")
			->join("", "#__judownload_documents_xref AS dx ON dx.doc_id = d.id")
			->join("", "#__judownload_categories AS c ON dx.cat_id = c.id");


		if (!empty($catIds))
		{
			$query->where("dx.cat_id IN (" . implode(',', $catIds) . ")");
		}

		if (!empty($tags))
		{
			$query->join("LEFT", "#__judownload_tags_xref AS tx ON tx.doc_id = d.id");
			$query->where("tx.tag_id IN (" . implode(",", $tags) . ")");
		}

		
		if (!empty($filter['csv_publishing_filter']))
		{
			$filterConditions = array();
			$nullDate         = $db->getNullDate();
			$now              = JFactory::getDate()->toSql();

			foreach ($filter['csv_publishing_filter'] AS $value)
			{
				$condition = array();
				switch (trim($value))
				{
					case 'published':
						$condition[] = 'd.published = 1';
						$condition[] = '(d.publish_up = ' . $db->quote($nullDate) . ' OR d.publish_up <= ' . $db->quote($now) . ')';
						$condition[] = '(d.publish_down = ' . $db->quote($nullDate) . ' OR d.publish_down >= ' . $db->quote($now) . ')';
						$condition[] = 'd.approved = 1';
						break;

					case 'unpublished':
						$condition[] = 'd.published = 0';
						$condition[] = 'd.approved = 1';
						break;

					case 'pending':
						$condition[] = 'd.publish_up < ' . $db->quote($now);
						$condition[] = 'd.published = 1';
						$condition[] = 'd.approved = 1';
						break;

					case 'expired':
						$condition[] = 'd.publish_down < ' . $db->quote($now);
						$condition[] = 'd.published = 1';
						$condition[] = 'd.approved = 1';
						break;

					case 'pending_approval':
						$condition[] = 'd.approved = 0 ';
						break;

					case 'approved':
						$condition[] = 'd.approved = 1';
						break;
				}

				if (!empty($condition))
				{
					$filterConditions[] = '( ' . implode(' AND ', $condition) . ' )';
				}
			}

			if (!empty($filterConditions))
			{
				$query->where('(' . implode(' OR ', $filterConditions) . ')');
			}
		}

		
		$offset = JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));
		if (!empty($filter['csv_created_from_filter']))
		{
			$time = JFactory::getDate($filter['csv_created_from_filter'], $offset)->toSql();
			$query->where('d.created >= ' . $db->quote($time));
		}

		if (!empty($filter['csv_created_to_filter']))
		{
			$time = JFactory::getDate($filter['csv_created_to_filter'], $offset)->toSql();
			$query->where('d.created <= ' . $db->quote($time));
		}

		
		if (!empty($licenses))
		{
			$query->where('d.license_id IN (' . implode(',', $licenses) . ')');
		}

		$query->group('d.id');

		return $query;
	}

	
	public function filterExportFieldValue($data)
	{
		if (!empty($data))
		{
			foreach ($data AS $key => $value)
			{
				if (is_object($value))
				{
					$value = get_object_vars($value);
				}

				if (is_array($value))
				{
					if (empty($value))
					{
						$value = '';
					}
					else
					{
						switch ($key)
						{
							case 'files':
								$value = !empty($value) ? implode('|', $value) : '';

								break;

							case 'gallery':
								$value = !empty($value) ? implode('|', $value) : '';

								break;

							default:
								$value = !empty($value) ? implode(',', $value) : '';

								break;
						}
					}
				}

				
				$data[$key] = $value;
			}

			return $data;
		}

		return array();

	}

	
	public function array2csv($data, $path = 'php://output', $mode = 'w')
	{
		if (count($data) == 0)
		{
			return null;
		}

		ob_start();

		$file = fopen($path, $mode);

		

		foreach ($data AS $row)
		{
			fputcsv($file, $row);
		}

		fclose($file);

		return ob_get_clean();
	}

	public function downloadSendHeaders($filename)
	{
		
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");
	}

	
	public function getLicenses()
	{
		$db    = $this->getDbo();
		$query = "SELECT id, title FROM #__judownload_licenses";
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function addFileInfo(&$file, $filePath, $destDir)
	{
		$params = JUDownloadHelper::getParams();

		if (function_exists('finfo_open'))
		{
			
			$finfo             = finfo_open(FILEINFO_MIME);
			$file['mime_type'] = finfo_file($finfo, $filePath);
			finfo_close($finfo);
		}
		elseif (function_exists('mime_content_type'))
		{
			
			$file['mime_type'] = mime_content_type($filePath);
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
			return $file['crc32_checksum'] = hash_file('crc32b', $filePath);
		}
		else
		{
			$file['crc32_checksum'] = '';
		}

		
		do
		{
			$file['file_name'] = md5($file['file_name'] . JUDownloadHelper::generateRandomString(10)) . "." . JFile::getExt($file['file_name']);
			$dest              = $destDir . $file['doc_id'] . "/" . $file['file_name'];
		} while (JFile::exists($dest));
	}

}