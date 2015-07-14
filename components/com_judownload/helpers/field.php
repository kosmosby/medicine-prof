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

class JUDownloadFrontHelperField
{
	
	public static $cache = array();

	
	public static function getFieldGroupById($fieldGroupId)
	{
		$storeId = md5(__METHOD__ . "::" . (int) $fieldGroupId);
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__judownload_fields_groups');
			$query->where('id = ' . $fieldGroupId);
			$db->setQuery($query);
			self::$cache[$storeId] = $db->loadObject();
		}

		return self::$cache[$storeId];
	}

	
	public static function appendFieldOrderingPriority(&$query = null, $catIds = null, $ordering = null, $direction = null)
	{
		if (!$catIds)
		{
			$catIds = JUDownloadFrontHelperCategory::getRootCategory()->id;
		}

		$storeId = md5(__METHOD__ . "::" . serialize($catIds) . "::$ordering");
		if (!isset(self::$cache[$storeId]))
		{
			$db             = JFactory::getDbo();
			$nullDate       = $db->getNullDate();
			$nowDate        = JFactory::getDate()->toSql();
			$priority_query = $db->getQuery(true);
			$priority_query->select("field.*");
			$priority_query->from("#__judownload_fields AS field");
			$priority_query->select("plg.folder");
			$priority_query->join("", "#__judownload_plugins AS plg ON field.plugin_id = plg.id ");
			$priority_query->join("", "#__judownload_fields_groups AS field_group ON field.group_id = field_group.id");
			$priority_query->join("", "#__judownload_categories AS c ON (
																			(
																				c.fieldgroup_id = field_group.id AND
																				c.published = 1 AND
																				c.publish_up <= " . $db->quote($nowDate) . " AND
																				(c.publish_down = " . $db->quote($nullDate) . " OR c.publish_down > " . $db->quote($nowDate) . ")
																			) OR field.group_id = 1
																		 )");
			$where   = array();
			$where[] = 'field.published = 1';
			$where[] = 'field.publish_up <= ' . $db->quote($nowDate);
			$where[] = '(field.publish_down = ' . $db->quote($nullDate) . ' OR field.publish_down > ' . $db->quote($nowDate) . ')';

			if (is_array($catIds))
			{
				$where[] = "(c.id IN (" . implode(",", $catIds) . ") OR field.group_id = 1)";
			}
			else
			{
				$where[] = "(c.id = $catIds OR field.group_id = 1)";
			}

			$where[] = 'field.allow_priority = 1';
			$where[] = 'field_group.published = 1';

			
			$priority_query->where("(" . implode(" AND ", $where) . ")", "OR");

			if ($ordering)
			{
				$where   = array();
				$where[] = "(field.id = '$ordering' OR field.field_name = '$ordering')";
				$app     = JFactory::getApplication();
				if ($app->isSite())
				{
					$where[] = "field.frontend_ordering = 1";
				}
				else
				{
					$where[] = "field.backend_list_view >= 1";
				}
				$where[] = "field.published = 1";
				$where[] = "field.publish_up <= " . $db->quote($nowDate);
				$where[] = "(field.publish_down = " . $db->quote($nullDate) . " OR field.publish_down >= " . $db->quote($nowDate) . ")";
				$priority_query->where("(" . implode(" AND ", $where) . ")", "OR");
			}
			$priority_query->group('field.id');

			$priority_query->order('field.priority ASC');

			$db->setQuery($priority_query);

			self::$cache[$storeId] = $db->loadObjectList();
		}

		$priorityFields = self::$cache[$storeId];

		$priority_order = array();
		
		$_ordering = "";
		foreach ($priorityFields AS $priorityField)
		{
			$field = JUDownloadFrontHelperField::getField($priorityField);
			if ($field)
			{
				$priority = $field->orderingPriority($query);
				if ($priority)
				{
					$priority_order[] = $priority['ordering'] . ' ' . $priority['direction'];
				}

				if ($ordering && $field->id == $ordering && $priority)
				{
					$_ordering = $priority['ordering'];
				}
			}
		}

		$priority_str = "";
		if ($priority_order)
		{
			$priority_str = implode(", ", $priority_order);
		}

		if ($_ordering)
		{
			$ordering_str = $_ordering . " " . $direction;
			if ($priority_str)
			{
				$priority_str = $ordering_str . ", " . $priority_str;
			}
		}

		if ($priority_str)
		{
			$query->order($priority_str);
		}

		return $priority_str;
	}

	
	public static function getFieldById($fieldId, $fieldObj = null)
	{
		if (!$fieldId)
		{
			return null;
		}

		
		$storeId = md5(__METHOD__ . "::$fieldId");

		if (!isset(self::$cache[$storeId]))
		{
			if (!is_object($fieldObj))
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('field.*, p.folder')
					->from('#__judownload_fields AS field')
					->join('LEFT', '#__judownload_plugins AS p ON p.id = field.plugin_id');

				if (is_numeric($fieldId))
				{
					$query->where('field.id = ' . $fieldId);
				}
				
				else
				{
					$query->where('field.field_name = ' . $db->quote($fieldId));
				}

				$db->setQuery($query);

				$fieldObj = $db->loadObject();
			}

			
			unset($fieldObj->allow_priority);
			unset($fieldObj->backend_list_view);
			unset($fieldObj->backend_list_view_ordering);
			unset($fieldObj->checked_out);
			unset($fieldObj->checked_out_time);
			unset($fieldObj->asset_id);
			unset($fieldObj->ordering);
			unset($fieldObj->frontend_ordering);
			unset($fieldObj->metatitle);
			unset($fieldObj->metakeyword);
			unset($fieldObj->metadescription);
			unset($fieldObj->metadata);
			unset($fieldObj->ignored_options);
			unset($fieldObj->created);
			unset($fieldObj->created_by);
			unset($fieldObj->modified);
			unset($fieldObj->modified_by);

			self::$cache[$storeId] = $fieldObj;
		}

		return self::$cache[$storeId];
	}

	public static function getFieldByFolder($folder, $doc = null, $only = true)
	{
		$db       = JFactory::getDbo();
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(JFactory::getDate()->toSql());
		$query    = $db->getQuery(true);
		$query->select('field.*, plg.folder')
			->from('#__judownload_fields as field')
			->join('', '#__judownload_plugins as plg ON field.plugin_id = plg.id')
			->where('field.published = 1')
			->where('field.publish_up <= ' . $nowDate)
			->where('(field.publish_down = ' . $nullDate . ' OR field.publish_down > ' . $nowDate . ')')
			->where('plg.folder = ' . $db->quote($folder));

		$db->setQuery($query);

		$fieldsObjs = $db->loadObjectList();
		$fields     = array();
		if ($fieldsObjs)
		{
			foreach ($fieldsObjs as $fieldsObj)
			{
				$fields[] = self::getField($fieldsObj, $doc);
				if ($only)
				{
					break;
				}
			}
		}

		if ($only)
		{
			return array_shift($fields);
		}
		else
		{
			return $fields;
		}
	}

	
	public static function getField($field, $doc = null, $resetDocumentCache = false)
	{
		if (!$field)
		{
			return null;
		}

		if (is_object($field))
		{
			if ($field->field_name != "")
			{
				$fieldId = $field->field_name;
			}
			else
			{
				$fieldId = $field->id;
			}
		}
		else
		{
			$fieldId = $field;
		}

		
		$storeId = md5("JUDLField::" . $fieldId);
		if (!isset(self::$cache['fields'][$storeId]))
		{
			
			if (!is_object($field))
			{
				$field = self::getFieldById($field);
			}
			if (!$field)
			{
				return false;
			}

			
			if (!$field->folder)
			{
				$fieldClassName = 'JUDownloadFieldBase';
			}
			else
			{
				$fieldClassName = 'JUDownloadField' . $field->folder;
			}

			$_fieldObj = clone $field;

			$fieldClass = null;
			if (class_exists($fieldClassName))
			{
				$fieldClass = new $fieldClassName($_fieldObj);
			}

			self::$cache['fields'][$storeId] = $fieldClass;
		}

		
		$fieldClass = self::$cache['fields'][$storeId];
		if ($fieldClass)
		{
			$fieldClassWithDoc = clone $fieldClass;
			$fieldClassWithDoc->loadDocument($doc, $resetDocumentCache);

			return $fieldClassWithDoc;
		}
		else
		{
			return $fieldClass;
		}
	}

	
	public static function getFields($doc, $view = null, $includedOnlyFields = array(), $ignoredFields = array(), $additionFields = array())
	{
		$user        = JFactory::getUser();
		$accessLevel = implode(',', $user->getAuthorisedViewLevels());
		$date        = JFactory::getDate();
		$db          = JFactory::getDbo();

		
		if (is_object($doc) && !isset($doc->cat_id))
		{
			$doc = JUDownloadHelper::getDocumentById($doc->id);
		}

		
		if (is_numeric($doc))
		{
			$doc = JUDownloadHelper::getDocumentById($doc);
		}

		$catId = $doc->cat_id;

		

		
		$catObjStoreId = md5(__METHOD__ . "::catObj::$catId::$accessLevel");
		if (!isset(self::$cache[$catObjStoreId]))
		{
			$query = $db->getQuery(true);
			$query->select('c.id, c.field_ordering_type');
			$query->select('fg.id AS field_group_id, fg.field_ordering_type AS fg_field_ordering_type');
			$query->from('#__judownload_categories AS c');
			$query->join('LEFT', '#__judownload_fields_groups AS fg ON (fg.id = c.fieldgroup_id AND fg.published = 1 AND fg.access IN (' . $accessLevel . '))');
			$query->where('c.id = ' . $catId);
			$db->setQuery($query);
			self::$cache[$catObjStoreId] = $db->loadObject();
		}
		$catObj = self::$cache[$catObjStoreId];

		
		if (empty($catObj))
		{
			return false;
		}

		
		
		if ($catObj->field_ordering_type == 1)
		{
			$item_id        = $catObj->id;
			$type           = 'category';
			$field_group_id = $catObj->field_group_id;
		}
		
		else
		{
			
			if ($catObj->fg_field_ordering_type == 1)
			{
				$item_id        = $catObj->field_group_id;
				$type           = 'fieldgroup';
				$field_group_id = $catObj->field_group_id;
			}
			
			else
			{
				$item_id        = 0;
				$type           = '';
				$field_group_id = $catObj->field_group_id;
			}
		}

		$fieldsStoreId = md5(__METHOD__ . "::fieldsObj::$item_id::$type::$field_group_id::$view::" . "::" . serialize($includedOnlyFields) . "::" . serialize($ignoredFields) . "::" . serialize($additionFields));
		if (!isset(self::$cache[$fieldsStoreId]))
		{
			$query = $db->getQuery(true);
			$query->select("field.*, plg.folder");
			$query->from("#__judownload_fields AS field");
			$query->join("", "#__judownload_plugins AS plg ON (field.plugin_id = plg.id)");
			if ($item_id)
			{
				$query->select("fordering.ordering");
				$query->join("LEFT", "#__judownload_fields_ordering AS fordering ON (fordering.field_id = field.id AND fordering.item_id = " . (int) $item_id . " AND fordering.type = '$type')");
				$query->order("fordering.ordering");
			}
			$query->join("", "#__judownload_fields_groups AS fg ON (fg.id = field.group_id)");
			$query->where('fg.access IN (' . $accessLevel . ')');

			
			$nullDate = $db->quote($db->getNullDate());
			$nowDate  = $db->quote($date->toSql());
			$query->where('field.published = 1');
			$query->where('field.publish_up <= ' . $nowDate);
			$query->where('(field.publish_down = ' . $nullDate . ' OR field.publish_down > ' . $nowDate . ')');

			
			$query->where('(field.access IN (' . $accessLevel . ') OR field.who_can_download_can_access = 1)');

			
			if ($field_group_id > 1)
			{
				$query->where("(field.group_id = 1 OR field.group_id = " . (int) $field_group_id . ")");
			}
			
			else
			{
				$query->where("field.group_id = 1");
			}

			
			$additionFieldsStr = "";
			if ($additionFields)
			{
				$additionFieldIds = $additionFieldNames = array();
				foreach ($additionFields AS $additionField)
				{
					if ($additionField && is_numeric($additionField))
					{
						$additionFieldIds[$additionField] = $additionField;
					}
					elseif ($additionField)
					{
						$additionFieldNames[$additionField] = $additionField;
					}
				}

				if ($additionFieldIds)
				{
					$additionFieldsStr .= " OR field.id IN (" . implode(",", $additionFieldIds) . ")";
				}

				if ($additionFieldNames)
				{
					$additionFieldsStr .= " OR field.field_name IN ('" . implode("','", $additionFieldNames) . "')";
				}
			}

			
			$app         = JFactory::getApplication();
			$languageTag = JFactory::getLanguage()->getTag();
			if ($app->getLanguageFilter())
			{
				$query->where("(field.language IN (" . $db->quote($languageTag) . "," . $db->quote('*') . "," . $db->quote('') . ")" . $additionFieldsStr . ")");
			}

			
			if ($view == 1)
			{
				$query->where("(field.list_view = 1" . $additionFieldsStr . ")");
			}
			elseif ($view == 2)
			{
				$query->where("(field.details_view = 1" . $additionFieldsStr . ")");
			}

			
			if ($ignoredFields)
			{
				$ignoreFieldIds = $ignoreFieldNames = array();
				foreach ($ignoredFields AS $ignoredField)
				{
					if ($ignoredField && is_numeric($ignoredField))
					{
						$ignoreFieldIds[$ignoredField] = $ignoredField;
					}
					elseif ($ignoredField)
					{
						$ignoreFieldNames[$ignoredField] = $ignoredField;
					}
				}

				if ($ignoreFieldIds)
				{
					$query->where("field.id NOT IN (" . implode(",", $ignoreFieldIds) . ")");
				}

				if ($ignoreFieldNames)
				{
					$query->where("field.field_name NOT IN ('" . implode("','", $ignoreFieldNames) . "')");
				}
			}

			
			if ($includedOnlyFields)
			{
				$includedFieldIds = $includedFieldNames = array();
				foreach ($includedOnlyFields AS $includedField)
				{
					if ($includedField && is_numeric($includedField))
					{
						$includedFieldIds[$includedField] = $includedField;
					}
					elseif ($includedField)
					{
						$includedFieldNames[$includedField] = $includedField;
					}
				}

				if ($includedFieldIds)
				{
					$query->where("field.id IN (" . implode(",", $includedFieldIds) . ")");
				}

				if ($includedFieldNames)
				{
					$query->where("field.field_name IN ('" . implode("','", $includedFieldNames) . "')");
				}
			}

			$query->group('field.id');
			
			$query->order("fg.ordering, field.ordering");
			$db->setQuery($query);

			$fields = $db->loadObjectList();

			
			$newFields = array();
			foreach ($fields AS $key => $field)
			{
				
				if (isset($field->ordering) && is_null($field->ordering))
				{
					$newFields[] = $field;
					unset($fields[$key]);
				}
			}
			
			if (!empty($newFields))
			{
				$fields = array_merge($fields, $newFields);
			}

			self::$cache[$fieldsStoreId] = $fields;
		}
		$fields = self::$cache[$fieldsStoreId];

		
		if (!$fields)
		{
			return false;
		}

		$fieldObjectList = array();
		if (count($fields))
		{
			foreach ($fields AS $_field)
			{
				$field = clone $_field;

				
				if ($field->field_name != "")
				{
					$newKey = $field->field_name;
				}
				else
				{
					$newKey = $field->id;
				}

				$fieldObjectList[$newKey] = self::getField($field, $doc);

				unset($field);
			}
		}

		return $fieldObjectList;
	}

	
	public static function mergeFieldOptions($global_display_params, $document_display_params)
	{
		$fields = new stdClass();

		if (isset($global_display_params->fields))
		{
			$fields = $global_display_params->fields;

			
			foreach ($fields AS $fieldKey => $fieldOptions)
			{
				foreach ($fieldOptions AS $fieldOptionKey => $fieldOptionValue)
				{
					
					if (isset($document_display_params->fields->$fieldKey->$fieldOptionKey) && $document_display_params->fields->$fieldKey->$fieldOptionKey != '-2')
					{
						$fields->$fieldKey->$fieldOptionKey = $document_display_params->fields->$fieldKey->$fieldOptionKey;
					}
				}
			}
		}

		$app              = JFactory::getApplication();
		$activeMenuParams = new JRegistry;

		if ($menu = $app->getMenu()->getActive())
		{
			$activeMenuParams->loadString($menu->params);
		}

		$activeMenuObj = $activeMenuParams->toObject();

		if (isset($activeMenuObj->doc->fields))
		{
			
			foreach ($activeMenuObj->doc->fields AS $fieldKey => $fieldOptions)
			{
				foreach ($fieldOptions AS $fieldOptionKey => $fieldOptionValue)
				{
					
					if ($fieldOptionValue !== null && $fieldOptionValue !== '')
					{
						$fields->$fieldKey->$fieldOptionKey = $fieldOptionValue;
					}
				}
			}
		}

		
		$global_display_params->fields = $fields;

		$registry = new JRegistry($global_display_params);

		return $registry;
	}

	
	public static function getFrontEndOrdering($catId = 1)
	{
		$db       = JFactory::getDbo();
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(JFactory::getDate()->toSql());
		$query    = $db->getQuery(true);
		
		
		$query->select('field.caption, field.id, field.field_name,field.group_id');
		$query->from('#__judownload_fields AS field');
		$query->join('', '#__judownload_plugins AS plg ON plg.id = field.plugin_id');
		$query->join('', '#__judownload_fields_groups AS field_group ON field_group.id = field.group_id');
		$query->join('', '#__judownload_categories AS c ON (field_group.id = c.fieldgroup_id OR field.group_id = 1)');
		$query->where('field.frontend_ordering = 1');
		$query->where('field.published = 1');
		$query->where('field.publish_up <= ' . $nowDate);
		$query->where('(field.publish_down = ' . $nullDate . ' OR field.publish_down >= ' . $nowDate . ')');
		$query->where('field_group.published = 1');
		
		$query->where('(c.id = ' . $catId . ' OR field.group_id = 1)');
		$query->group('field.id');
		$query->order('field.group_id ASC, field.ordering ASC');
		$db->setQuery($query);
		$fields            = $db->loadObjectList();
		$fieldOrdering     = array();
		$fieldOrdering[""] = JText::_('COM_JUDOWNLOAD_DEFAULT');
		if (count($fields) > 0)
		{
			foreach ($fields AS $field)
			{
				
				$fieldOrdering[$field->id] = JText::_($field->caption);
			}
		}

		return $fieldOrdering;
	}

	
	public static function getFrontEndDirection()
	{
		$orderDirArray         = array();
		$orderDirArray['ASC']  = JText::_('COM_JUDOWNLOAD_ASC');
		$orderDirArray['DESC'] = JText::_('COM_JUDOWNLOAD_DESC');

		return $orderDirArray;
	}
}