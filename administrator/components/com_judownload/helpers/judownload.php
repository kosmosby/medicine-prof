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


class JUDownloadHelper
{
	
	protected static $cache = array();

	################################< CATEGORY SECTION >################################

	
	public static function getCategoryById($cat_id)
	{
		if (!$cat_id)
		{
			return null;
		}

		$storeId = md5(__METHOD__ . "::$cat_id");
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->SELECT('*');
			$query->FROM('#__judownload_categories');
			$query->WHERE('id = ' . $cat_id);
			$db->setQuery($query);
			self::$cache[$storeId] = $db->loadObject();
		}

		return self::$cache[$storeId];
	}

	public static function getCategoryPath($catId, $diagnostic = false)
	{
		$storeId = md5(__METHOD__ . "::$catId::" . (int) $diagnostic);

		if (!isset(self::$cache[$storeId]))
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
			$categoryTable         = JTable::getInstance('Category', 'JUDownloadTable');
			self::$cache[$storeId] = $categoryTable->getPath($catId, $diagnostic);
		}

		return self::$cache[$storeId];
	}

	
	public static function generateCategoryPath($cat_id, $separator = " > ", $link = false, $lastItemLink = false)
	{
		if (!$cat_id)
		{
			return '';
		}

		$categories = self::getCategoryPath($cat_id);
		$totalCats  = count($categories);

		if ($separator == "li")
		{
			$html = '<ul class="breadcrumb">';
			$html .= '<li><i class="icon-location"></i></li>';
			$divider = self::isJoomla3x() ? '' : '<span class="divider">/</span>';
			foreach ($categories AS $i => $category)
			{
				$html .= ($link && ($lastItemLink || (!$lastItemLink && $i != $totalCats - 1))) ? '<li><a href="index.php?option=com_judownload&view=listcats&cat_id=' . $category->id . '" >' . $category->title . '</a>' . $divider . '</li>' : (($i != $totalCats - 1) ? '<li>' . $category->title . $divider . '</li>' : '<li class="active">' . $category->title . '</li>');
			}
			$html .= '</ul>';

			return $html;
		}
		else
		{
			$path = array();
			foreach ($categories AS $i => $category)
			{
				$path[] = ($link && ($lastItemLink || (!$lastItemLink && $i != $totalCats - 1))) ? "<a href='index.php?option=com_judownload&view=listcats&cat_id=" . $category->id . "' >" . $category->title . "</a>" : $category->title;
			}

			return implode($separator, $path);
		}
	}

	public static function getCategoryTree($categoryId = 1, $fetchSelf = true, $checkPublish = false)
	{
		$storeId = md5(__METHOD__ . "::$categoryId::" . (int) $fetchSelf . "::" . (int) $checkPublish);
		if (!isset(self::$cache[$storeId]))
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
			$categoryTable = JTable::getInstance('Category', 'JUDownloadTable');

			$nowDate = JFactory::getDate()->toSql();

			
			$validCategories  = array();
			$validCategoryIds = array();

			if ($categoryTable->load($categoryId))
			{
				$categories = $categoryTable->getTree();
				foreach ($categories AS $key => $category)
				{
					if ($key == 0)
					{
						
						if ($checkPublish && ($category->published != 1 || $nowDate < $category->publish_up || (intval($category->publish_down) != 0 && $nowDate > $category->publish_down)))
						{
							self::$cache[$storeId] = array();

							return self::$cache[$storeId];
						}

						if ($fetchSelf)
						{
							$validCategories[] = $category;
						}
					}
					else
					{
						if (!in_array($category->parent_id, $validCategoryIds))
						{
							unset($categories[$key]);
							continue;
						}

						if ($checkPublish && ($category->published != 1 || $nowDate < $category->publish_up || (intval($category->publish_down) != 0 && $nowDate > $category->publish_down)))
						{
							unset($categories[$key]);
							continue;
						}

						$validCategories[] = $category;
					}

					$validCategoryIds[] = $category->id;
				}
			}

			self::$cache[$storeId] = $validCategories;
		}

		return self::$cache[$storeId];
	}

	
	public static function getCategoryDTree($cat_id = null)
	{
		JLoader::register('JUDownloadHelperRoute', JPATH_SITE . '/components/com_judownload/helpers/route.php');

		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::root() . "components/com_judownload/assets/dtree/css/dtree.css");
		$document->addScript(JUri::root() . "components/com_judownload/assets/dtree/js/dtree.js");

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, title, parent_id, level, config_params');
		$query->from('#__judownload_categories');
		$query->order('title ASC, id ASC');
		$db->setQuery($query);
		$categories = $db->loadObjectList();

		$script     = "<script type=\"text/javascript\">\r\n";
		$iconFolder = JUri::root() . 'components/com_judownload/assets/dtree/img';
		$script .= "tree_cat = new dTree('tree_cat');\r\n";
		$script .= "tree_cat.icon.root = '$iconFolder/base.gif';\r\n
					tree_cat.icon.folder = '$iconFolder/folder.gif';\r\n
					tree_cat.icon.folderOpen = '$iconFolder/folderopen.gif';\r\n
					tree_cat.icon.node = '$iconFolder/folder.gif';\r\n
					tree_cat.icon.empty = '$iconFolder/empty.gif';\r\n
					tree_cat.icon.line = '$iconFolder/line.gif';\r\n
					tree_cat.icon.join = '$iconFolder/join.gif';\r\n
					tree_cat.icon.joinBottom = '$iconFolder/joinbottom.gif';\r\n
					tree_cat.icon.plus = '$iconFolder/plus.gif';\r\n
					tree_cat.icon.plusBottom = '$iconFolder/plusbottom.gif';\r\n
					tree_cat.icon.minus = '$iconFolder/minus.gif';\r\n
					tree_cat.icon.minusBottom = '$iconFolder/minusbottom.gif';\r\n
					tree_cat.icon.nlPlus = '$iconFolder/nolines_plus.gif';\r\n
					tree_cat.icon.nlMinus = '$iconFolder/nolines_minus.gif';\r\n";

		foreach ($categories AS $category)
		{
			$cat_title = addslashes(htmlspecialchars($category->title, ENT_QUOTES));
			if ($category->level == 1 && $category->config_params)
			{
				$cat_title .= " <i class=\"icon-cog disabled hasTooltip\" title=\"" . JText::_('COM_JUDOWNLOAD_OVERRIDE_CONFIG') . "\"></i>";
			}

			if ($category->level == 1 && JUDownloadHelperRoute::findItemId(array('tree' => array($category->id))))
			{
				$script .= "tree_cat.add($category->id, $category->parent_id, '$cat_title', '" . JUri::Base() . "index.php?option=com_judownload&view=listcats&cat_id=$category->id', '', '', tree_cat.icon.root);\r\n";
			}
			else
			{
				$script .= "tree_cat.add($category->id, $category->parent_id, '$cat_title', '" . JUri::Base() . "index.php?option=com_judownload&view=listcats&cat_id=$category->id');\r\n";
			}
		}

		$script .= "tree_cat.config.useCookies=false;\r\n";
		$script .= "tree_cat.config.closeSameLevel=true;\r\n";
		$script .= "document.write(tree_cat);\r\n";
		if ($cat_id)
		{
			$script .= "tree_cat.openTo($cat_id, true);";
		}
		$script .= "</script>";

		return $script;
	}

	
	public static function getCategoriesByDocId($doc_id, $select = 'c.*', $secondaryCat = false)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->SELECT($select);
		$query->FROM('#__judownload_categories AS c');
		$query->JOIN('', '#__judownload_documents_xref AS dxref ON dxref.cat_id = c.id');
		$query->WHERE('dxref.doc_id = ' . $doc_id);
		if ($secondaryCat)
		{
			$query->WHERE('dxref.main = 0');
		}
		$query->ORDER('dxref.main DESC, dxref.ordering ASC');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	
	public static function getCatsByLevel($level = 1, $childCatId = null)
	{
		$storeId = md5(__METHOD__ . "::$level::" . (int) $childCatId);
		if (!isset(self::$cache[$storeId]))
		{
			$db = JFactory::getDbo();
			if ($childCatId > 0)
			{
				$fromCatObj = self::getCategoryById($childCatId);
				if (!empty($fromCatObj))
				{
					$query = $db->getQuery(true);
					$query->select('*');
					$query->from('#__judownload_categories');
					$query->where('lft <= ' . $fromCatObj->lft);
					$query->where('rgt >= ' . $fromCatObj->rgt);
					$query->where('level = ' . $level);
					$db->setQuery($query);
				}
			}
			else
			{
				$query = $db->getQuery(true);
				$query->select('*');
				$query->from('#__judownload_categories');
				$query->where('level = ' . $level);
				$db->setQuery($query);
			}

			self::$cache[$storeId] = $db->loadObjectList();
		}

		return self::$cache[$storeId];
	}

	
	public static function getCategoryOptions($catId = 1, $fetchSelf = true, $checkCreatedPermission = false, $checkPublished = false, $ignoredCatId = array(), $startLevel = 0, $separation = '|—')
	{
		$categoryTree = self::getCategoryTree($catId, $fetchSelf, $checkPublished);
		if ($categoryTree)
		{
			$app     = JFactory::getApplication($catId);
			$user    = JFactory::getUser();
			$options = array();

			$ignoredCatIdArr = array();
			if ($ignoredCatId)
			{
				foreach ($ignoredCatId as $cat_id)
				{
					if (!in_array($cat_id, $ignoredCatIdArr))
					{
						$_categoryTree = self::getCategoryTree($cat_id, true);
						foreach ($_categoryTree as $category)
						{
							if (!in_array($category->id, $ignoredCatIdArr))
							{
								$ignoredCatIdArr[] = $category->id;
							}
						}
					}
				}
			}

			foreach ($categoryTree AS $key => $item)
			{
				if ($app->isSite())
				{
					$accessibleCategoryIds = JUDownloadFrontHelperCategory::getAccessibleCategoryIds();
					if (!is_array($accessibleCategoryIds))
					{
						$accessibleCategoryIds = array();
					}
					if (!in_array($item->id, $accessibleCategoryIds))
					{
						continue;
					}
				}

				if ($ignoredCatIdArr && in_array($item->id, $ignoredCatIdArr))
				{
					continue;
				}

				$disable = false;
				if ($checkCreatedPermission)
				{
					if ($checkCreatedPermission == "category")
					{
						$assetName   = 'com_judownload.category.' . (int) $item->id;
						$candoCreate = $user->authorise('judl.category.create', $assetName);
						if (!$candoCreate)
						{
							$disable = true;
						}
					}
					elseif ($checkCreatedPermission == "document")
					{
						$assetName   = 'com_judownload.category.' . (int) $item->id;
						$candoCreate = $user->authorise('judl.document.create', $assetName);
						if (!$candoCreate)
						{
							$disable = true;
						}
					}
				}

				
				if ($item->published != 1 && !$checkPublished)
				{
					$item->title = "[" . $item->title . "]";
				}

				if ($key == 0)
				{
					$firstLevel = $item->level - $startLevel;
				}

				$level = $item->level - $firstLevel;

				$options[] = JHtml::_('select.option', $item->id, str_repeat($separation, $level) . $item->title, 'value', 'text', $disable);
			}
		}

		return $options;
	}

	################################< DOCUMENT SECTION >################################

	
	public static function getDocumentById($doc_id, $resetCache = false, $documentObject = null, $getTotalFiles = false)
	{
		if (!$doc_id)
		{
			return null;
		}

		

		$storeId = md5(__METHOD__ . "::" . $doc_id . "::" . (int) $getTotalFiles);
		if (!isset(self::$cache[$storeId]) || $resetCache)
		{
			
			if (!is_object($documentObject) || ($getTotalFiles && !isset($documentObject->total_files)))
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->SELECT('d.*');
				if ($getTotalFiles)
				{
					$app = JFactory::getApplication();
					if ($app->isAdmin())
					{
						$query->select('(SELECT COUNT(*) FROM #__judownload_files AS f WHERE f.doc_id = d.id) AS total_files');
					}
					else
					{
						$query->select('(SELECT COUNT(*) FROM #__judownload_files AS f WHERE f.doc_id = d.id AND f.published = 1) AS total_files');
					}
				}
				$query->FROM('#__judownload_documents AS d');
				$query->JOIN('LEFT', '#__judownload_documents_xref AS dxref ON (d.id = dxref.doc_id AND dxref.main = 1)');
				$query->SELECT('c.id AS cat_id');
				$query->JOIN('LEFT', '#__judownload_categories AS c ON (c.id = dxref.cat_id)');
				$query->WHERE('d.id = ' . $doc_id);
				$db->setQuery($query);
				$documentObject = $db->loadObject();
			}

			if ($documentObject && $documentObject->cat_id > 0)
			{
				self::$cache[$storeId] = $documentObject;
			}
			else
			{
				return $documentObject;
			}
		}

		return self::$cache[$storeId];
	}

	
	public static function getDocumentIdsByCatId($cat_id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->SELECT('doc_id');
		$query->FROM('#__judownload_documents_xref');
		$query->WHERE('cat_id=' . $cat_id . ' AND main=1');
		$db->setQuery($query);
		$rows = $db->loadColumn();

		return $rows;
	}

	
	public static function getTotalPendingDocuments($type = '', $doc_id = null)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__judownload_documents');
		$query->where('approved < 1');

		if (strtolower($type) == 'next')
		{
			$query->where('id > ' . $doc_id);
			$query->order('id ASC');
		}
		elseif (strtolower($type) == 'prev')
		{
			$query->where('id < ' . $doc_id);
			$query->order('id DESC');
		}

		$db->setQuery($query);
		$total = $db->loadResult();

		return $total;
	}

	
	public static function getTempDocument($doc_id)
	{
		
		if ($doc_id <= 0)
		{
			return false;
		}

		$storeId = md5(__METHOD__ . "::" . $doc_id);
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__judownload_documents');
			$query->where('approved = ' . (-$doc_id));
			$db->setQuery($query);
			$document = $db->loadObject();
			if (is_null($document))
			{
				$document = false;
			}

			self::$cache[$storeId] = $document;
		}

		return self::$cache[$storeId];
	}

	
	public static function getDefaultDocumentIcon($catId = null, $docId = null)
	{
		$params       = self::getParams($catId, $docId);
		$default_icon = $params->get('document_default_icon', 'default-document.png');

		if ($default_icon != -1)
		{
			return JUri::root(true) . "/" . JUDownloadFrontHelper::getDirectory("document_image_directory", "media/com_judownload/images/document/", true) . "default/" . $default_icon;
		}
		else
		{
			return '';
		}
	}

	public static function getDocumentIcon($icon, $getDefault = true)
	{
		if ($icon)
		{
			$documentIconUrl = JUDownloadFrontHelper::getDirectory("document_image_directory", "media/com_judownload/images/document/", true);

			return JUri::root(true) . "/" . $documentIconUrl . $icon;
		}
		elseif ($getDefault)
		{
			return JUDownloadHelper::getDefaultDocumentIcon();
		}

		return '';
	}

	
	public static function getDefaultCollectionIcon()
	{
		$params       = self::getParams();
		$default_icon = $params->get('collection_default_icon', 'default-collection.png');

		if ($default_icon != -1)
		{
			return JUri::root(true) . "/" . JUDownloadFrontHelper::getDirectory("collection_icon_directory", "media/com_judownload/images/collection/", true) . "default/" . $default_icon;
		}
		else
		{
			return '';
		}
	}

	################################< COMMENT SECTION >################################

	
	public static function getTotalPendingComments($type = '', $id = null)
	{

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__judownload_comments');
		$query->where('approved < 1');
		$query->where('parent_id != 0');
		$query->where('level != 0');

		if (strtolower($type) == 'next')
		{
			$query->where('id > ' . $id);
			$query->order('id ASC');
		}
		elseif (strtolower($type) == 'prev')
		{
			$query->where('id < ' . $id);
			$query->order('id DESC');
		}

		$db->setQuery($query);
		$total = $db->loadResult();

		return $total;
	}

	################################< FIELD GROUP & FIELD SECTION >################################

	
	public static function deleteFieldValuesOfDocument($docId)
	{
		
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("field.*, plg.folder");
		$query->from("#__judownload_fields AS field");
		$query->join("", "#__judownload_plugins AS plg ON field.plugin_id = plg.id");
		$query->join("", "#__judownload_fields_groups AS fg ON fg.id = field.group_id");
		$query->join("", "#__judownload_categories AS c ON (c.fieldgroup_id = fg.id OR field.group_id = 1)");
		$query->join("", "#__judownload_documents_xref AS dxref ON dxref.cat_id = c.id");
		$query->join("", "#__judownload_documents AS d ON (dxref.doc_id = d.id AND dxref.main=1)");
		$query->where("d.id = $docId");
		$db->setQuery($query);
		$fields = $db->loadObjectList();
		foreach ($fields AS $field)
		{
			
			$fieldClass = JUDownloadFrontHelperField::getField($field, $docId);
			$fieldClass->onDelete();
		}
	}

	
	public static function autoLoadFieldClass($class)
	{
		
		if (class_exists($class))
		{
			return null;
		}

		$pattern = '/^judownloadfield(.*)$/i';
		preg_match($pattern, strtolower($class), $matches);
		if ($matches)
		{
			$fieldFolderPath = JPATH_SITE . '/components/com_judownload/fields/';
			
			if ($matches[1])
			{
				
				$path = $fieldFolderPath . $matches[1] . '/' . $matches[1] . '.php';
				if (JFile::exists($path))
				{
					require_once $path;
				}
			}
		}
	}

	
	public static function changeInheritedFieldGroupId($cat_id, $new_fieldgroup_id = null)
	{
		
		if ($new_fieldgroup_id === null)
		{
			$new_fieldgroup_id = self::getCategoryById($cat_id)->fieldgroup_id;
		}

		
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('id, fieldgroup_id');
		$query->from('#__judownload_categories');
		$query->where('parent_id = ' . $cat_id);
		$query->where('selected_fieldgroup = -1');
		$db->setQuery($query);
		$subcategories = $db->loadObjectList();

		foreach ($subcategories AS $subcategory)
		{
			if ($subcategory->fieldgroup_id != $new_fieldgroup_id)
			{
				$query = $db->getQuery(true);
				$query->update('#__judownload_categories');
				$query->set('fieldgroup_id = ' . $new_fieldgroup_id);
				$query->where('id = ' . $subcategory->id);
				$db->setQuery($query);
				$db->execute();

				
				$docIds = self::getDocumentIdsByCatId($subcategory->id);
				foreach ($docIds AS $docId)
				{
					self::deleteFieldValuesOfDocument($docId);
				}

				
				$query = $db->getQuery(true);
				$query->delete('#__judownload_fields_ordering');
				$query->where('item_id = ' . $subcategory->id);
				$query->where('type = "category"');
				$db->setQuery($query);
				$db->execute();

				
				self::changeInheritedFieldGroupId($subcategory->id, $new_fieldgroup_id);
			}
		}
	}

	
	public static function getFieldGroupIdByDocId($doc_id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('fieldgroup_id');
		$query->from('#__judownload_categories AS c');
		$query->join('', '#__judownload_documents_xref AS dxref ON c.id=dxref.cat_id');
		$query->where('dxref.doc_id=' . $doc_id);
		$query->where('dxref.main = 1');
		$db->setQuery($query);

		return $db->loadResult();
	}

	
	public static function getCatFields()
	{
		$catFields                     = array();
		$catFields['id']               = JText::_('COM_JUDOWNLOAD_FIELD_ID');
		$catFields['title']            = JText::_('COM_JUDOWNLOAD_FIELD_TITLE');
		$catFields['alias']            = JText::_('COM_JUDOWNLOAD_FIELD_ALIAS');
		$catFields['parent_id']        = JText::_('COM_JUDOWNLOAD_FIELD_PARENT_CAT');
		$catFields['rel_cats']         = JText::_('COM_JUDOWNLOAD_FIELD_REL_CATS');
		$catFields['access']           = JText::_('COM_JUDOWNLOAD_FIELD_ACCESS');
		$catFields['lft']              = JText::_('COM_JUDOWNLOAD_FIELD_ORDERING');
		$catFields['fieldgroup_id']    = JText::_('COM_JUDOWNLOAD_FIELD_FIELD_GROUP_ID');
		$catFields['criteriagroup_id'] = JText::_('COM_JUDOWNLOAD_FIELD_CRITERIA_GROUP_ID');
		$catFields['featured']         = JText::_('COM_JUDOWNLOAD_FIELD_FEATURED');
		$catFields['published']        = JText::_('COM_JUDOWNLOAD_FIELD_PUBLISHED');
		$catFields['show_item']        = JText::_('COM_JUDOWNLOAD_FIELD_SHOW_ITEM');
		$catFields['description']      = JText::_('COM_JUDOWNLOAD_FIELD_DESCRIPTION');
		$catFields['intro_image']      = JText::_('COM_JUDOWNLOAD_FIELD_INTRO_IMAGE');
		$catFields['detail_image']     = JText::_('COM_JUDOWNLOAD_FIELD_DETAIL_IMAGE');
		$catFields['publish_up']       = JText::_('COM_JUDOWNLOAD_FIELD_PUBLISH_UP');
		$catFields['publish_down']     = JText::_('COM_JUDOWNLOAD_FIELD_PUBLISH_DOWN');
		$catFields['created_by']       = JText::_('COM_JUDOWNLOAD_FIELD_CREATED_BY');
		$catFields['created']          = JText::_('COM_JUDOWNLOAD_FIELD_CREATED');
		$catFields['modified_by']      = JText::_('COM_JUDOWNLOAD_FIELD_MODIFIED_BY');
		$catFields['modified']         = JText::_('COM_JUDOWNLOAD_FIELD_MODIFIED');
		$catFields['style_id']         = JText::_('COM_JUDOWNLOAD_FIELD_TEMPLATE_STYLE');
		$catFields['layout']           = JText::_('COM_JUDOWNLOAD_FIELD_LAYOUT');
		$catFields['metatitle']        = JText::_('COM_JUDOWNLOAD_FIELD_METATITLE');
		$catFields['metakeyword']      = JText::_('COM_JUDOWNLOAD_FIELD_METAKEYWORD');
		$catFields['metadescription']  = JText::_('COM_JUDOWNLOAD_FIELD_METADESCRIPTION');
		$catFields['metadata']         = JText::_('COM_JUDOWNLOAD_FIELD_METADATA');
		$catFields['total_categories'] = JText::_('COM_JUDOWNLOAD_FIELD_TOTAL_CATEGORIES');
		$catFields['total_documents']  = JText::_('COM_JUDOWNLOAD_FIELD_TOTAL_DOCUMENTS');

		return $catFields;
	}

	
	public static function getFieldGroupOptions($createPermission = false, $ignoreCoreFields = true)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('`id` AS value, `name` AS text, published');
		$query->from('#__judownload_fields_groups');
		if ($ignoreCoreFields)
		{
			$query->where('id != 1');
		}
		$query->order('ordering ASC');
		$db->setQuery($query);
		$options     = array();
		$fieldgroups = $db->loadObjectList();
		$user        = JFactory::getUser();

		foreach ($fieldgroups AS $fieldgroup)
		{
			if ($createPermission && !$user->authorise('core.create', 'com_judownload.fieldgroup.' . $fieldgroup->value))
			{
				continue;
			}

			if ($fieldgroup->published != 1)
			{
				$fieldgroup->text = "[" . $fieldgroup->text . "]";
			}
			$options[] = JHtml::_('select.option', $fieldgroup->value, $fieldgroup->text);
		}

		return $options;
	}

	
	public static function getAdvSearchFields()
	{
		$app      = JFactory::getApplication();
		$db       = JFactory::getDbo();
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(JFactory::getDate()->toSql());
		$query    = $db->getQuery(true);
		$query->select('plg.folder, field.*, field_group.name AS filed_group_name');
		$query->from('#__judownload_fields AS field');
		$query->join('', '#__judownload_plugins AS plg ON field.plugin_id = plg.id');
		$query->join('', '#__judownload_fields_groups AS field_group ON field.group_id = field_group.id');
		if ($app->isSite())
		{
			$query->where('field.advanced_search = 1');
		}
		$query->where('field_group.published = 1');
		$query->where('field.published = 1');
		$query->where('field.field_name != "cat_id"');
		$query->where('field.publish_up <= ' . $nowDate);
		$query->where('(field.publish_down = ' . $nullDate . ' OR field.publish_down > ' . $nowDate . ')');
		$query->order('field.group_id');
		$db->setQuery($query);

		$fields = $db->loadObjectList();

		if ($fields)
		{
			$fieldGroups = array();
			foreach ($fields AS $field)
			{
				if (!isset($fieldGroups[$field->group_id]))
				{
					$fieldGroups[$field->group_id]         = new stdClass();
					$fieldGroups[$field->group_id]->name   = $field->filed_group_name;
					$fieldGroups[$field->group_id]->id     = $field->group_id;
					$fieldGroups[$field->group_id]->fields = array();
				}

				$fieldGroups[$field->group_id]->fields[] = JUDownloadFrontHelperField::getField($field);
			}

			return $fieldGroups;
		}

		return null;
	}

	
	public static function getFieldGroupsByCatIds($catIds, $search_sub_categories = false)
	{
		if (!$catIds)
		{
			return null;
		}

		$field_groups = array();
		foreach ($catIds AS $catId)
		{
			if ($search_sub_categories)
			{
				$categoryTree = JUDownloadHelper::getCategoryTree($catId, true, true);
				foreach ($categoryTree AS $sub_category)
				{
					if ($sub_category->fieldgroup_id > 0)
					{
						$field_groups[] = $sub_category->fieldgroup_id;
					}
				}
			}
			else
			{
				$catObj         = JUDownloadHelper::getCategoryById($catId);
				$field_groups[] = $catObj->fieldgroup_id ? $catObj->fieldgroup_id : 1;
			}
		}

		$field_groups = array_unique($field_groups);
		if ($field_groups)
		{
			return implode(",", $field_groups);
		}
		else
		{
			return null;
		}
	}

	################################< CRITERIA GROUP & CRITERIA SECTION >################################

	
	public static function changeInheritedCriteriaGroupId($cat_id, $new_criteriagroup_id = null)
	{
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('id, criteriagroup_id');
		$query->from('#__judownload_categories');
		$query->where('parent_id = ' . $cat_id);
		$query->where('selected_criteriagroup = -1');
		$db->setQuery($query);
		$categories = $db->loadObjectList();

		
		if (is_null($new_criteriagroup_id))
		{
			$new_criteriagroup_id = self::getCategoryById($cat_id)->criteriagroup_id;
		}

		if ($categories)
		{
			foreach ($categories AS $category)
			{
				
				if ($category->criteriagroup_id != $new_criteriagroup_id)
				{
					$query = $db->getQuery(true);
					$query->update('#__judownload_categories');
					$query->set('criteriagroup_id = ' . $new_criteriagroup_id);
					$query->where('id = ' . $category->id);
					$db->setQuery($query);
					$db->execute();

					
					
					

					
					self::changeInheritedCriteriaGroupId($category->id, $new_criteriagroup_id);
				}
			}
		}
	}

	
	public static function getCriteriaGroupIdByDocId($doc_id)
	{
		if (!$doc_id)
		{
			return null;
		}

		$storeId = md5(__METHOD__ . "::" . $doc_id);
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('c.criteriagroup_id');
			$query->from('#__judownload_categories AS c');
			$query->join('', '#__judownload_documents_xref AS dxref ON dxref.cat_id = c.id AND dxref.main = 1');
			$query->where('dxref.doc_id = ' . $doc_id);
			$db->setQuery($query);
			self::$cache[$storeId] = $db->loadResult();
		}

		return self::$cache[$storeId];
	}

	
	public static function getCriteriaGroupOptions()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('`id` AS value, `name` AS text, published');
		$query->from('#__judownload_criterias_groups');
		$query->order('id ASC');
		$db->setQuery($query);
		$criteriaGroups = $db->loadObjectList();
		$option         = array();
		foreach ($criteriaGroups AS $criteriaGroup)
		{
			if ($criteriaGroup->published != 1)
			{
				$criteriaGroup->text = '[ ' . $criteriaGroup->text . ' ]';
			}
			$option[] = JHtml::_('select.option', $criteriaGroup->value, $criteriaGroup->text);
		}

		return $option;
	}

	################################< RATING SECTION >################################

	
	public static function rebuildRating($doc_id)
	{
		$db                      = JFactory::getDbo();
		$params                  = self::getParams(null, $doc_id);
		$onlyCalculateLastRating = $params->get('only_calculate_last_rating', 0);

		if ($onlyCalculateLastRating == 1)
		{
			$query = $db->getQuery(true);
			$query->select('r.id, r.doc_id, r.score');
			$query->select('cm.approved AS comment_approved');
			$query->from('#__judownload_rating AS r');
			$query->join('LEFT', '#__judownload_comments AS cm ON cm.rating_id = r.id');
			$query->where('r.`created` = (SELECT  MAX(created) FROM `#__judownload_rating` AS r2 WHERE r2.doc_id = r.doc_id AND r2.user_id = r.user_id AND r.user_id > 0)');
			$query->where('r.doc_id = ' . $doc_id);
			$query->group('r.created, r.doc_id, r.user_id');
		}
		else
		{
			$query = $db->getQuery(true);
			$query->select('r.id, r.doc_id, r.score');
			$query->select('cm.approved AS comment_approved');
			$query->from('#__judownload_rating AS r');
			$query->join('LEFT', '#__judownload_comments AS cm ON cm.rating_id = r.id');
			$query->where('r.doc_id = ' . $doc_id);
		}

		$db->setQuery($query);
		$ratings = $db->loadObjectList();

		
		if ($ratings)
		{
			$totalScore = 0;
			$totalVotes = 0;

			if (JFile::exists(JPATH_SITE . '/components/com_judownload/fields/multirating/multirating.class.php'))
			{
				require_once JPATH_SITE . '/components/com_judownload/fields/multirating/multirating.class.php';
			}

			foreach ($ratings AS $rating)
			{
				
				if (self::hasMultiRating())
				{
					$ratingScore = JUDownloadMultiRating::rebuildRating($rating);
				}
				
				else
				{
					$ratingScore = $rating->score;
				}

				
				if ($rating->comment_approved !== 0)
				{
					$totalScore += $ratingScore;
					$totalVotes++;
				}
			}


			
			if ($onlyCalculateLastRating == 1)
			{
				$avgScore = $totalScore / $totalVotes;
				$query    = $db->getQuery(true);
				$query->update('#__judownload_documents');
				$query->set('rating = ' . $avgScore);
				$query->set('total_votes = ' . $totalVotes);
				$query->where('id = ' . $doc_id);
			}
			else
			{
				$query = $db->getQuery(true);
				$query->update('#__judownload_documents');
				$query->set('rating = (SELECT AVG(score) FROM #__judownload_rating WHERE doc_id=' . $doc_id . ')');
				$query->set('total_votes = ' . $totalVotes);
				$query->where('id = ' . $doc_id);
			}
			$db->setQuery($query);
			$db->execute();

			return false;
		}
	}

	
	public static function hasMultiRating()
	{
		JLoader::register('JUDownloadMultiRating', JPATH_SITE . '/components/com_judownload/plugins/multirating/multirating.php');
		if (!class_exists('JUDownloadMultiRating'))
		{
			return false;
		}

		return true;
	}

	
	public static function hasCSVPlugin()
	{
		JLoader::register('JUDownloadCSV', JPATH_SITE . '/components/com_judownload/plugins/csv/csv.php');
		if (!class_exists('JUDownloadCSV'))
		{
			return false;
		}

		return true;
	}
	################################< IMAGE & FILE SECTION >################################

	
	public static function renderImages($image, $output, $type = 'document_small', $output_url = true, $catId = null, $docId = null)
	{
		$params = self::getParams($catId, $docId);

		
		if (preg_match('/^https?:\/\/[^\/]+/i', $image))
		{
			$image = str_replace(JUri::root(), '', $image);
		}

		$timthumb_params        = array();
		$timthumb_params['src'] = $image;
		switch ($type)
		{
			case "document_small" :
			default :
				$timthumb_params['w']  = $params->get('document_small_image_width', 100);
				$timthumb_params['h']  = $params->get('document_small_image_height', 100);
				$timthumb_params['a']  = $params->get('document_small_image_alignment', 'c');
				$timthumb_params['zc'] = $params->get('document_small_image_zoomcrop', 1);
				break;
			case "document_big" :
				$timthumb_params['w']  = $params->get('document_big_image_width', 600);
				$timthumb_params['h']  = $params->get('document_big_image_height', 600);
				$timthumb_params['a']  = $params->get('document_big_image_alignment', 'c');
				$timthumb_params['zc'] = $params->get('document_big_image_zoomcrop', 3);
				break;
			case "category_intro" :
				$timthumb_params['w']  = $params->get('category_intro_image_width', 200);
				$timthumb_params['h']  = $params->get('category_intro_image_height', 200);
				$timthumb_params['a']  = $params->get('category_intro_image_alignment', 'c');
				$timthumb_params['zc'] = $params->get('category_intro_image_zoomcrop', 1);
				break;
			case "category_detail" :
				$timthumb_params['w']  = $params->get('category_detail_image_width', 200);
				$timthumb_params['h']  = $params->get('category_detail_image_height', 200);
				$timthumb_params['a']  = $params->get('category_detail_image_alignment', 'c');
				$timthumb_params['zc'] = $params->get('category_detail_image_zoomcrop', 1);
				break;
			case "avatar" :
				$timthumb_params['w']  = $params->get('avatar_width', 120);
				$timthumb_params['h']  = $params->get('avatar_height', 120);
				$timthumb_params['a']  = $params->get('avatar_alignment', 'c');
				$timthumb_params['zc'] = $params->get('avatar_zoomcrop', 1);
				break;
			case "document_icon" :
				$timthumb_params['w']  = $params->get('document_icon_width', 100);
				$timthumb_params['h']  = $params->get('document_icon_height', 100);
				$timthumb_params['a']  = $params->get('document_icon_alignment', 'c');
				$timthumb_params['zc'] = $params->get('document_icon_zoomcrop', 1);
				break;
			case "collection":
				$timthumb_params['w']  = $params->get('collection_icon_width', 100);
				$timthumb_params['h']  = $params->get('collection_icon_height', 100);
				$timthumb_params['a']  = $params->get('collection_icon_alignment', 'c');
				$timthumb_params['zc'] = $params->get('collection_icon_zoomcrop', 1);
				break;
		}

		$timthumb_params['q'] = $params->get('imagequality', 90);
		if ($params->get('customfilters', '') != '')
		{
			$timthumb_params['f'] = $params->get('customfilters', '');
		}
		else
		{
			$filters = $params->get('filters');
			if (!empty($filters))
			{
				$filters              = implode("|", $filters);
				$timthumb_params['f'] = $filters;
			}
		}
		$timthumb_params['s']      = $params->get('sharpen', 0);
		$timthumb_params['cc']     = trim($params->get('canvascolour', 'FFFFFF'), '#');
		$timthumb_params['ct']     = $params->get('canvastransparency', 1);
		$timthumb_params['output'] = $output;

		$tim    = new jutimthumb($timthumb_params);
		$output = $tim->start();

		

		if ($output_url)
		{
			$output = str_replace(JPATH_SITE, substr(JUri::root(), 0, -1), $output);
		}

		return $output;
	}

	
	public static function parseImageNameByTags($replace, $type = 'document', $catId = null, $docId = null)
	{
		$params = self::getParams($catId, $docId);
		if ($type == 'category')
		{
			$image_filename = "{id}_" . $params->get('category_image_filename_rule', '{category}');
		}
		else
		{
			$image_filename = $params->get('document_image_filename_rule', '{image_name}');
		}
		$search         = array('{id}', '{category}', '{document}', '{image_name}');
		$image_filename = str_replace($search, array($replace['id'], $replace['category'], $replace['document'], $replace['image_name']), $image_filename);

		return self::fileNameFilter($image_filename);
	}

	
	public static function fileNameFilter($fileName)
	{
		
		$fileNameFilterPath = JPATH_ADMINISTRATOR . "/components/com_judownload/helper/filenamefilter.php";
		if (JFile::exists($fileNameFilterPath))
		{
			require_once $fileNameFilterPath;
			if (class_exists("JUFileNameFilter"))
			{
				
				if (function_exists("fileNameFilter"))
				{
					$fileName = call_user_func("fileNameFilter", $fileName);
				}
			}
		}

		$fileInfo = pathinfo($fileName);
		$fileName = str_replace("-", "_", JFilterOutput::stringURLSafe($fileInfo['filename']));

		$fileName = JFile::makeSafe($fileName);

		
		if (!$fileName)
		{
			$fileName = JFactory::getDate()->format('Y_m_d_H_i_s');
		}

		return isset($fileInfo['extension']) ? $fileName . "." . $fileInfo['extension'] : $fileName;
	}

	
	public static function getFileNames($file_ids, $link = true)
	{
		if (strpos($file_ids, ":"))
		{
			$file_ids_version_arr = explode(':', $file_ids);
			$file_ids             = $file_ids_version_arr[0];
			$version              = $file_ids_version_arr[1];
		}
		else
		{
			$version = '';
		}

		$file_id_arr  = explode(",", $file_ids);
		$filename_arr = array();
		foreach ($file_id_arr AS $file_id)
		{
			$filename_arr[] = self::getFileName($file_id, $link);
		}
		$fileNames = implode(", ", $filename_arr);

		return ($version ? "<strong>" . JText::_('COM_JUDOWNLOAD_VERSION') . " " . $version . "</strong>: " : "") . $fileNames;
	}

	
	public static function getFileName($file_id, $link = true)
	{
		if (!$file_id)
		{
			return '';
		}

		$storeId = md5(__METHOD__ . "::" . (int) $file_id . "::" . (int) $link);
		if (!isset(self::$cache[$storeId]))
		{
			$user = JFactory::getUser();

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('doc_id, `rename`');
			$query->from('#__judownload_files');
			$query->where('id = ' . (int) $file_id);
			$db->setQuery($query);
			$fileObj = $db->loadObject();
			if (!$fileObj)
			{
				self::$cache[$storeId] = JText::sprintf('COM_JUDOWNLOAD_FILE_ID_NOT_FOUND', $file_id);

				return self::$cache[$storeId];
			}

			$documentObj      = self::getDocumentById($fileObj->doc_id);
			$groupCanDoManage = self::checkGroupPermission("document.edit");
			$canEdit          = $user->authorise('judl.document.edit', 'com_judownload.document.' . $documentObj->id);
			$canEditOwn       = $user->authorise('judl.document.edit.own', 'com_judownload.document.' . $documentObj->id) && $documentObj->created_by == $user->id;
			if ($link && ($canEdit || $canEditOwn) && $groupCanDoManage)
			{
				self::$cache[$storeId] = "<a href='index.php?option=com_judownload&task=document.edit&id=" . $fileObj->doc_id . "' title='" . JText::_('COM_JUDOWNLOAD_DOCUMENT_LABEL') . ": " . $documentObj->title . "'>" . $fileObj->rename . "</a>";
			}
			else
			{
				self::$cache[$storeId] = "<span title='" . JText::_('COM_JUDOWNLOAD_DOCUMENT_LABEL') . ": " . $documentObj->title . "'>" . $fileObj->rename . "</span>";
			}
		}

		return self::$cache[$storeId];
	}

	
	public static function getMimeType($filePath)
	{
		$mime_type = '';

		if (function_exists('finfo_open'))
		{
			$fhandle   = finfo_open(FILEINFO_MIME);
			$mime_type = finfo_file($fhandle, $filePath);
		}

		if (function_exists('mime_content_type'))
		{
			$mime_type = mime_content_type($filePath);
		}

		if (!$mime_type)
		{
			$imageExtension = array("jpeg", "pjpeg", "png", "gif", "bmp", "jpg");
			$extension      = JFile::getExt($filePath);

			if (in_array($extension, $imageExtension))
			{
				$imageInfo = getimagesize($filePath);

				$mime_type = $imageInfo['mime'];
			}
		}

		return $mime_type;
	}

	
	public static function formatBytes($n_bytes)
	{
		if ($n_bytes < 1024)
		{
			return $n_bytes . ' B';
		}
		elseif ($n_bytes < 1048576)
		{
			return round($n_bytes / 1024) . ' KB';
		}
		elseif ($n_bytes < 1073741824)
		{
			return round($n_bytes / 1048576, 2) . ' MB';
		}
		elseif ($n_bytes < 1099511627776)
		{
			return round($n_bytes / 1073741824, 2) . ' GB';
		}
		elseif ($n_bytes < 1125899906842624)
		{
			return round($n_bytes / 1099511627776, 2) . ' TB';
		}
		elseif ($n_bytes < 1152921504606846976)
		{
			return round($n_bytes / 1125899906842624, 2) . ' PB';
		}
		elseif ($n_bytes < 1180591620717411303424)
		{
			return round($n_bytes / 1152921504606846976, 2) . ' EB';
		}
		elseif ($n_bytes < 1208925819614629174706176)
		{
			return round($n_bytes / 1180591620717411303424, 2) . ' ZB';
		}
		else
		{
			return round($n_bytes / 1208925819614629174706176, 2) . ' YB';
		}
	}

	
	public static function getPostMaxSize()
	{
		$val  = ini_get('post_max_size');
		$last = strtolower($val[strlen($val) - 1]);
		switch ($last)
		{
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

	
	public static function getPhysicalPath($path)
	{
		if (empty($path))
		{
			return '';
		}

		
		if (stripos($path, JUri::root()) === 0)
		{
			$path = JPath::clean(str_replace(JUri::root(), JPATH_ROOT . "/", $path));
		}
		else
		{
			if (stripos($path, JPATH_ROOT) === false)
			{
				$path = JPath::clean(JPATH_ROOT . "/" . $path);
			}
		}

		if (JFile::exists($path))
		{
			return $path;
		}

		return '';
	}

	
	public static function downloadFile($file, $fileName, $transport = 'php', $speed = 50, $resume = true, $downloadMultiParts = true, $mimeType = false)
	{
		
		if (ini_get('zlib.output_compression'))
		{
			@ini_set('zlib.output_compression', 'Off');
		}

		
		if (function_exists('apache_setenv'))
		{
			apache_setenv('no-gzip', '1');
		}

		
		

		
		
		
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : null;
		if ($agent && preg_match('#(?:MSIE |Internet Explorer/)(?:[0-9.]+)#', $agent)
			&& (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
		)
		{
			header('Pragma: ');
			header('Cache-Control: ');
		}
		else
		{
			header('Pragma: no-store,no-cache');
			header('Cache-Control: no-cache, no-store, must-revalidate, max-age=-1');
			header('Cache-Control: post-check=0, pre-check=0', false);
		}
		header('Expires: Mon, 14 Jul 1789 12:30:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

		
		if (is_resource($file) && get_resource_type($file) == "stream")
		{
			$transport = 'php';
		}
		
		elseif (!JFile::exists($file))
		{
			return JText::sprintf("COM_JUDOWNLOAD_FILE_NOT_FOUND_X", $fileName);
		}

		$transport = 'php';

		
		if ($transport != 'php')
		{
			
			header('Content-Description: File Transfer');
			header('Date: ' . @gmdate("D, j M m Y H:i:s ") . 'GMT');
			
			if ($resume)
			{
				header('Accept-Ranges: bytes');
			}
			
			elseif (isset($_SERVER['HTTP_RANGE']))
			{
				exit;
			}

			if (!$downloadMultiParts)
			{
				
				header('Accept-Ranges: none');
			}

			header('Content-Type: application/force-download');
			
			
			
			
			header('Content-Disposition: attachment; filename="' . $fileName . '"');
		}

		switch ($transport)
		{
			case 'php':
			default:
				JLoader::register('JUDownload', JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/judownload.class.php');

				JUDownloadHelper::obCleanData();

				$download = new JUDownload($file);

				$download->rename($fileName);
				if ($mimeType)
				{
					$download->mime($mimeType);
				}
				if ($resume)
				{
					$download->resume();
				}
				$download->speed($speed);
				$download->start();

				if ($download->error)
				{
					return $download->error;
				}

				unset($download);
				break;
		}

		return true;
	}

	################################< LOG SECTION >################################

	
	public static function deleteLogs($event, $id)
	{
		if (!$id || !$event)
		{
			return false;
		}

		$db = JFactory::getDbo();
		if (is_array($id))
		{
			$query = $db->getQuery(true);
			$query->select('id');
			$query->from('#__judownload_logs');
			
			$query->where("(event LIKE " . $db->quote("%." . $event) . " OR event LIKE " . $db->quote($event . ".%") . ") AND item_id IN (" . implode(",", $id) . ")");
		}
		else
		{
			$query = $db->getQuery(true);
			$query->select('id');
			$query->from('#__judownload_logs');
			
			$query->where("(event LIKE " . $db->quote("%." . $event) . " OR event LIKE " . $db->quote($event . ".%") . ") AND item_id = " . $id);
		}

		$db->setQuery($query);
		$logIds = $db->loadColumn();

		if ($logIds)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
			$logTable = JTable::getInstance("Log", "JUDownloadTable");
			
			foreach ($logIds AS $logId)
			{
				$logTable->delete($logId);
			}
		}

		return true;
	}

	################################< LICENSE SECTION >################################

	
	public static function getLicenseOptions()
	{
		$app   = JFactory::getApplication();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('`id` AS value, `title` AS text, published');
		$query->from('#__judownload_licenses');
		if ($app->isSite())
		{
			$query->where('published = 1');
		}
		$db->setQuery($query);

		$licenses = $db->loadObjectList();
		$option   = array();
		foreach ($licenses AS $license)
		{
			
			if ($license->published != 1)
			{
				$license->text = "[" . $license->text . "]";
			}
			$option[] = JHtml::_('select.option', $license->value, $license->text);
		}

		return $option;
	}

	################################< THEME & LAYOUT SECTION >################################

	
	public static function calculateStyle($styleId, $parentCatId = 1)
	{
		if (!$parentCatId)
		{
			$parentCatId = 1;
		}

		if ($styleId == -2)
		{
			return self::getDefaultStyleId();
		}
		elseif ($styleId == -1)
		{
			do
			{
				$category    = self::getCategoryById($parentCatId);
				$styleId     = $category->style_id;
				$parentCatId = $category->parent_id;
			} while ($styleId == -1 && $parentCatId != 0);

			if ($styleId == -2)
			{
				return self::getDefaultStyleId();
			}
			else
			{
				return $styleId;
			}
		}
		else
		{
			return $styleId;
		}
	}

	
	public static function getDefaultStyleId()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('style.id');
		$query->from('#__judownload_template_styles AS style');
		$query->join('', '#__judownload_templates AS t ON t.id = style.template_id');
		$query->join('', '#__judownload_plugins AS plg ON plg.id = t.plugin_id');
		$query->where('style.home = 1');
		$db->setQuery($query);
		$result = $db->loadObject();
		if ($result)
		{
			return $result->id;
		}
		else
		{
			return 0;
		}
	}

	################################< PERMISSION SECTION >################################

	
	public static function getActions($component = 'com_judownload', $section = '', $id = 0)
	{
		if (!$component)
		{
			$component = 'com_judownload';
		}

		$user   = JFactory::getUser();
		$result = new JObject;

		$path = JPATH_ADMINISTRATOR . '/components/' . $component . '/access.xml';

		switch ($section)
		{
			case 'component':
				$actionsComponent    = JAccess::getActionsFromFile($path, "/access/section[@name='component']/");
				$actionsCategory     = JAccess::getActionsFromFile($path, "/access/section[@name='component_category']/");
				$actionsDocument     = JAccess::getActionsFromFile($path, "/access/section[@name='component_document']/");
				$actionsComment      = JAccess::getActionsFromFile($path, "/access/section[@name='component_comment']/");
				$actionsSingleRating = JAccess::getActionsFromFile($path, "/access/section[@name='component_single_rating']/");
				$actionsFieldValue   = JAccess::getActionsFromFile($path, "/access/section[@name='component_field_value']/");
				$actionsModerator    = JAccess::getActionsFromFile($path, "/access/section[@name='component_moderator']/");
				$actionsCriteria     = JAccess::getActionsFromFile($path, "/access/section[@name='component_criteria']/");
				$actions             = array_merge($actionsComponent, $actionsCategory, $actionsDocument, $actionsComment,
					$actionsSingleRating, $actionsFieldValue, $actionsModerator, $actionsCriteria);
				break;
			case 'category':
			case 'document':
				$actionsComponent    = JAccess::getActionsFromFile($path, "/access/section[@name='component']/");
				$actionsCategory     = JAccess::getActionsFromFile($path, "/access/section[@name='component_category']/");
				$actionsDocument     = JAccess::getActionsFromFile($path, "/access/section[@name='component_document']/");
				$actionsComment      = JAccess::getActionsFromFile($path, "/access/section[@name='component_comment']/");
				$actionsSingleRating = JAccess::getActionsFromFile($path, "/access/section[@name='component_single_rating']/");
				$actions             = array_merge($actionsComponent, $actionsCategory, $actionsDocument, $actionsComment, $actionsSingleRating);
				break;
			case 'fieldgroup':
			case 'field':
				$actionsComponent  = JAccess::getActionsFromFile($path, "/access/section[@name='component']/");
				$actionsFieldValue = JAccess::getActionsFromFile($path, "/access/section[@name='component_field_value']/");
				$actions           = array_merge($actionsComponent, $actionsFieldValue);
				break;
			case 'moderator':
				$actionsComponent = JAccess::getActionsFromFile($path, "/access/section[@name='component']/");
				$actionsModerator = JAccess::getActionsFromFile($path, "/access/section[@name='component_moderator']/");
				$actions          = array_merge($actionsComponent, $actionsModerator);
				break;
			case 'criteriagroup':
				$actionsComponent = JAccess::getActionsFromFile($path, "/access/section[@name='component']/");
				$actionsCriteria  = JAccess::getActionsFromFile($path, "/access/section[@name='component_criteria']/");
				$actions          = array_merge($actionsComponent, $actionsCriteria);
				break;
			default:
				$actionsComponent    = JAccess::getActionsFromFile($path, "/access/section[@name='component']/");
				$actionsCategory     = JAccess::getActionsFromFile($path, "/access/section[@name='component_category']/");
				$actionsDocument     = JAccess::getActionsFromFile($path, "/access/section[@name='component_document']/");
				$actionsComment      = JAccess::getActionsFromFile($path, "/access/section[@name='component_comment']/");
				$actionsSingleRating = JAccess::getActionsFromFile($path, "/access/section[@name='component_single_rating']/");
				$actionsFieldValue   = JAccess::getActionsFromFile($path, "/access/section[@name='component_field_value']/");
				$actionsModerator    = JAccess::getActionsFromFile($path, "/access/section[@name='component_moderator']/");
				$actionsCriteria     = JAccess::getActionsFromFile($path, "/access/section[@name='component_criteria']/");
				$actions             = array_merge($actionsComponent, $actionsCategory, $actionsDocument, $actionsComment,
					$actionsSingleRating, $actionsFieldValue, $actionsModerator, $actionsCriteria);
		}

		if ($section && $id)
		{
			$assetName = $component . '.' . $section . '.' . (int) $id;
		}
		else
		{
			$assetName = $component;
		}

		foreach ($actions AS $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	
	public static function checkGroupPermission($task_str = '', $view_str = '')
	{
		return true;
	}

	################################< COLLECTION SECTION >################################

	
	public static function deleteCollectionIcon($collectionId)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
		$collectionTable = JTable::getInstance("Collection", "JUDownloadTable");
		$collectionTable->load($collectionId);

		$errors[] = array();
		if ($collectionTable->icon)
		{
			$collection_icon_path = JPATH_SITE . "/" . JUDownloadFrontHelper::getDirectory('collection_icon_directory', "media/com_judownload/images/collection/");
			$originalIcon         = $collection_icon_path . "original/" . $collectionTable->icon;
			if (JFile::exists($originalIcon))
			{
				if (!JFile::delete($originalIcon))
				{

					return false;
				}
			}

			$resizeIcon = $collection_icon_path . $collectionTable->icon;
			if (JFile::exists($resizeIcon))
			{
				if (!JFile::delete($resizeIcon))
				{
					return false;
				}
			}
		}

		return true;
	}

	################################< MENU SECTION >################################

	
	protected static function addSubmenu($submenu)
	{
		
		if (!self::isJoomla3x())
		{
			JSubMenuHelper::addEntry(JText::_('COM_JUDOWNLOAD_SUBMENU_DASHBOARD'), 'index.php?option=com_judownload&view=dashboard', $submenu == 'dashboard');
		}
	}

	
	protected static function addPathMenu($item, $path = '')
	{
		$item->addAttribute('path', $path ? $path . '.' . $item['name'] : $item['name']);
		if (strlen(trim((string) $item)) == 0)
		{
			foreach ($item->children() AS $child)
			{
				self::addPathMenu($child, $item['path']);
			}
		}
	}

	
	protected static function showMenuItem($item)
	{
		if (strpos($item['name'], 'criteria') !== false && !self::hasMultiRating())
		{
			return 0;
		}

		if (strpos($item['name'], 'csv') !== false && !self::hasCSVPlugin())
		{
			return 0;
		}

		if ($item['proversion'] == "true" && !JUDLPROVERSION)
		{
			return 0;
		}

		$task = $view = $item['name'];
		if (strpos($item['name'], ".") !== false)
		{
			$view = "";
		}
		else
		{
			$task = "";
		}

		if (!self::checkGroupPermission($task, $view))
		{
			$showItemStatus = 0;
			$children       = $item->children();
			if (count($children))
			{
				foreach ($children AS $child)
				{
					if (self::showMenuItem($child) != 0)
					{
						$showItemStatus = 2;
						break;
					}
				}
			}
		}
		else
		{
			$showItemStatus = 1;
		}

		return $showItemStatus;
	}

	
	protected static function getMenuItems($item, $activePath)
	{
		$html     = '';
		$children = $item->children();

		if (self::showMenuItem($item) == 2)
		{
			$item['link'] = '#';
		}
		elseif (self::showMenuItem($item) == 0)
		{
			return $html;
		}

		$icon        = $item['icon'] ? $item['icon'] . ' ' : '';
		$activeClass = in_array($item['name'], $activePath) ? 'active' : '';
		if ($item->getName() == 'divider')
		{
			$html .= '<li class="divider"></li>';
		}
		elseif ($item->getName() == 'header')
		{
			$html .= '<li class="nav-header">' . $icon . ($item['label'] ? JText::_($item['label']) : $item['name']) . '</li>';
		}
		else
		{
			if (count($children) > 0)
			{
				$child_html = '';
				foreach ($children AS $child)
				{
					$child_html .= self::getMenuItems($child, $activePath);
				}

				$html .= '<li class="dropdown ' . $activeClass . '">';
				$html .= '<a href="' . $item['link'] . '" class="dropdown-toggle" data-toggle="dropdown">' . $icon . ($item['label'] ? JText::_($item['label']) : $item['name']) . ($child_html ? '<b class="caret"></b>' : '') . '</a>';
				if ($child_html)
				{
					$html .= '<ul class="dropdown-menu">';
					$html .= $child_html;
					$html .= '</ul>';
				}
				$html .= '</li>';
			}
			else
			{
				$html .= '<li class="' . $activeClass . '"><a href="' . $item['link'] . '">' . $icon . ($item['label'] ? JText::_($item['label']) : $item['name']) . '</a></li>';
			}
		}

		return $html;
	}

	
	public static function getMenu($menuName)
	{
		
		$app = JFactory::getApplication();
		if ($app->input->get('tmpl', '') == 'component')
		{
			return '';
		}

		$menu_path = JPATH_ADMINISTRATOR . "/" . 'components/com_judownload/helpers/menu.xml';
		$menu_xml  = JFactory::getXML($menu_path, true);
		$html      = '';
		if (!$menu_xml)
		{
			return $html;
		}

		foreach ($menu_xml->children() AS $child)
		{
			self::addPathMenu($child);
		}

		$activePath = array();
		$activeMenu = $menu_xml->xpath('//item[@name="' . $menuName . '"]');
		if (isset($activeMenu[0]) && $activeMenu[0])
		{
			$activePath = $activeMenu[0]['path'];
			if ($activePath)
			{
				$activePath = explode(".", $activePath);
			}
		}
		$html .= '<div class="navbar" id="jumenu">';
		$html .= '<div class="navbar-inner">';
		$html .= '<div class="container">';
		$html .= '<ul class="nav">';
		foreach ($menu_xml->children() AS $child)
		{
			$html .= self::getMenuItems($child, $activePath);
		}
		$html .= '</ul>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		self::addSubmenu('');
		$document = JFactory::getDocument();

		if (!self::isJoomla3x())
		{

			$script = "jQuery(document).ready(function($){
				var menu = $('#jumenu').clone(),
					jubootstrap = $('<div class=\"jubootstrap\" />');
					jubootstrap.html(menu);
					$('#element-box #jumenu').remove();
					$('#submenu-box').html(jubootstrap);
			});";
			$document->addScriptDeclaration($script);
		}

		$script = "jQuery(document).ready(function($){
						$('#jumenu .dropdown-toggle').dropdownHover();
					});";
		$document->addScriptDeclaration($script);

		return $html;
	}

	################################< OTHER SECTION >################################

	
	public static function getParams($catId = null, $docId = null)
	{
		// If set docId but don't set catId -> get catId by docId
		if (!$catId && $docId)
		{
			$docObj = self::getDocumentById($docId);
			if ($docObj)
			{
				$catId = $docObj->cat_id;
			}
		}

		// Only override if cat existed, override by params of top level cat
		// Find the top level category, assign to $catId if first level cat is found
		if ($catId)
		{
			$path = self::getCategoryPath($catId);

			$rootCat = $path[0];
		}
		else
		{
			$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
		}

		$catIdToGetParams = $rootCat->id;

		// Cache by catId
		$storeId = md5(__METHOD__ . "::$catIdToGetParams");
		// Set params by top level catId(or root) if it has not already set
		if (!isset(self::$cache[$storeId]))
		{
			// Get global config params(of root cat) by default
			$registry = new JRegistry;
			$registry->loadString($rootCat->config_params);

			// Override params from active menu if is a menu of component(Use merge to ignore empty string and null param value)
			$app        = JFactory::getApplication();
			$activeMenu = $app->getMenu()->getActive();
			if ($activeMenu && $activeMenu->component == 'com_judownload')
			{
				$registry->merge($activeMenu->params);
			}

			self::$cache[$storeId] = $registry;
		}

		return self::$cache[$storeId];
	}

	
	public static function obCleanData($error_reporting = false)
	{
		
		if (!$error_reporting)
		{
			error_reporting(0);
		}

		$obLevel = ob_get_level();
		if ($obLevel)
		{
			while ($obLevel > 0)
			{
				ob_end_clean();
				$obLevel--;
			}
		}
		else
		{
			ob_clean();
		}

		return true;
	}

	
	public static function isJoomla3x()
	{
		return version_compare(JVERSION, '3.0', 'ge');
	}

	
	public static function generateRandomString($length = 10)
	{
		$characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++)
		{
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}

		return $randomString;
	}

	
	public static function canEditJUDownloadPluginParams($rule, $index)
	{
		JPluginHelper::importPlugin('judownload', $rule);
		$dispatcher = JDispatcher::getInstance();
		$states     = $dispatcher->trigger('canEdit', array());
		if (in_array(true, $states))
		{
			return true;
		}

		return false;
	}

	public static function convertCsvCellToUtf8($str)
	{
		return mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str, 'UTF-8, ISO-8859-1', true));
	}

	
	public static function getCSVData($csvPath, $delimiter = ',', $enclosure = '"', $mode = 'r+', $offset = 0, $length = null, $includeFirstRow = true)
	{
		$app = JFactory::getApplication();
		
		if (!JFile::exists($csvPath))
		{
			$app->enqueueMessage("COM_JUDOWNLOAD_CSV_FILE_NOT_FOUND", 'error');

			return false;
		}

		$data = array();

		$handle = fopen($csvPath, $mode);

		try
		{
			if (!$handle)
			{
				$app->enqueueMessage(JText::sprintf('COM_JUDOWNLOAD_UNABLE_TO_OPEN_FILE', $csvPath), 'error');

				return false;
			}

			
			if (!$includeFirstRow)
			{
				$offset += 1;
			}

			$count = 0;

			while (!feof($handle))
			{
				$row = fgetcsv($handle, 0, $delimiter, $enclosure);

				
				
				if ($count == 0 && is_array($row))
				{
					$row[0] = str_replace(chr(239) . chr(187) . chr(191), '', $row[0]);
				}

				if (is_array($row) && $count >= $offset)
				{
					
					$row = array_map("JUDownloadHelper::convertCsvCellToUtf8", $row);

					$data[] = $row;
				}

				$count++;

				
				if (!is_null($length) && ($count - $offset) == $length)
				{
					break;
				}

			}

			fclose($handle);
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			fclose($handle);

			return false;
		}

		return $data;
	}


	
	public static function formValidation()
	{
		
		$storeId = md5(__METHOD__);
		if (isset(self::$cache[$storeId]) && self::$cache[$storeId])
		{
			return;
		}

		if (self::isJoomla3x())
		{
			JHtml::_('behavior.formvalidation');
		}
		
		else
		{
			JText::script('COM_JUDOWNLOAD_FIELD_INVALID');
			JHtml::_('behavior.framework');
			$document = JFactory::getDocument();
			$document->addScript(JUri::root() . "administrator/components/com_judownload/assets/js/validate.js");
		}

		self::$cache[$storeId] = true;
	}

	
	public static function appendXML(SimpleXMLElement $source, SimpleXMLElement $append, $globalConfig = false, $displayParams = false)
	{
		if ($append)
		{
			$attributes = $append->attributes();
			if ($globalConfig)
			{
				if ((isset($attributes['override']) && $attributes['override'] != 'true' && $attributes['override'] != 1) &&
					(in_array($append->getName(), array('field', 'fields', 'fieldset')))
				)
				{
					return false;
				}
			}

			if ($displayParams && $attributes['type'] == 'list')
			{
				$globalOption = $append->addChild('option', 'COM_JUDOWNLOAD_USE_GLOBAL');
				$globalOption->addAttribute('value', '-2');
			}

			if (strlen(trim((string) $append)) == 0)
			{
				$xml = $source->addChild($append->getName());
				foreach ($append->children() AS $child)
				{
					self::appendXML($xml, $child, $globalConfig, $displayParams);
				}
			}
			else
			{
				$xml = $source->addChild($append->getName(), (string) $append);
			}

			foreach ($append->attributes() AS $n => $v)
			{
				if ($displayParams && $n == 'fieldset')
				{
					$xml->addAttribute('fieldset', 'params');
				}
				elseif ($displayParams && $n == 'default')
				{
					$xml->addAttribute($n, '-2');
				}
				else
				{
					$xml->addAttribute($n, $v);
				}

			}
		}
	}

	
	public static function emailLinkRouter($url, $xhtml = true, $ssl = null)
	{
		
		$app    = JFactory::getApplication('site');
		$router = $app->getRouter();

		
		if (!$router)
		{
			return null;
		}

		if ((strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0))
		{
			return $url;
		}

		
		$uri = $router->build($url);

		$url = $uri->toString(array('path', 'query', 'fragment'));

		
		$url = preg_replace('/\s/u', '%20', $url);

		
		if ((int) $ssl)
		{
			$uri = JUri::getInstance();

			
			static $prefix;
			if (!$prefix)
			{
				$prefix = $uri->toString(array('host', 'port'));
			}

			
			$scheme = ((int) $ssl === 1) ? 'https' : 'http';

			
			if (!preg_match('#^/#', $url))
			{
				$url = '/' . $url;
			}

			
			$url = $scheme . '://' . $prefix . $url;
		}

		if ($xhtml)
		{
			$url = htmlspecialchars($url);
		}

		
		$url = str_replace('/administrator', '', $url);

		return $url;
	}

	
	public static function addCategory($doc_id, $cat_id, $main, $ordering = 1)
	{
		if (!$doc_id || !$cat_id)
		{
			return false;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->insert('#__judownload_documents_xref');
		$query->set('doc_id = ' . $doc_id . ', cat_id =' . $cat_id . ', main = ' . $main . ', ordering = ' . $ordering);
		$db->setQuery($query);

		return $db->execute();
	}

	public static function generateImageNameByDocument($doc_id, $file_name)
	{
		if (!$doc_id || !$file_name)
		{
			return "";
		}
		$dir_document_ori = JPATH_ROOT . "/" . JUDownloadFrontHelper::getDirectory("document_original_image_directory", "media/com_judownload/images/gallery/original/") . $doc_id . "/";
		$info             = pathinfo($file_name);
		$document         = JUDownloadHelper::getDocumentById($doc_id);
		$replace          = array('id' => $document->id, 'category' => '', 'document' => $document->title, 'image_name' => $info['filename']);
		$base_file_name   = JUDownloadHelper::parseImageNameByTags($replace, 'document', null, $document->id) . "." . $info['extension'];
		$img_file_name    = $base_file_name;
		$img_path_ori     = $dir_document_ori . $img_file_name;
		while (JFile::exists($img_path_ori))
		{
			$img_file_name = JUDownloadHelper::generateRandomString(3) . "-" . $base_file_name;
			$img_path_ori  = $dir_document_ori . $img_file_name;
		}

		return $img_file_name;
	}

	
	public static function getPluginOptions($type = null, $core = null, $default = null)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id AS value, title AS text');
		$query->from('#__judownload_plugins');
		if ($type)
		{
			$query->where('`type` = "' . $type . '"');
		}
		if (!is_null($core))
		{
			$query->where('`core` = "' . (int) $core . '"');
		}
		if (!is_null($default))
		{
			$query->where('`default` = "' . (int) $default . '"');
		}

		$db->setQuery($query);
		$plugins = $db->loadObjectlist();

		$options = array();
		foreach ($plugins AS $plugin)
		{

			$options[] = JHtml::_('select.option', $plugin->value, $plugin->text);
		}

		return $options;
	}


	
	public static function getDocumentSubmitType($documentId)
	{
		
		if ($documentId == 0)
		{
			return 'submit';
		}

		$documentObject = JUDownloadHelper::getDocumentById($documentId);
		
		if ($documentObject->approved == 0)
		{
			return 'submit';
		}
		
		else
		{
			return 'edit';
		}
	}

	
	public static function detectFieldsForCSVColumns($csvColumns, $importFor = 'document')
	{
		$db = JFactory::getDbo();

		$mappedColumns = array();

		switch ($importFor)
		{
			case 'file':
			case 'image':
				
				$query = "SHOW COLUMNS ";

				if ($importFor == 'file')
				{
					$query .= ' FROM ' . $db->quoteName('#__judownload_files');
				}
				else
				{
					$query .= ' FROM ' . $db->quoteName('#__judownload_images');
				}

				$db->setQuery($query);
				$columns = $db->loadColumn();

				foreach ($csvColumns AS $csvColumn)
				{
					
					$mappedColumns[$csvColumn] = 'ignore';

					foreach ($columns AS $column)
					{
						if (strcmp(strtolower($csvColumn), $column) == 0)
						{
							$mappedColumns[$csvColumn] = $column;
							break;
						}
					}
				}

				break;

			case 'document':
			default :
				foreach ($csvColumns AS $column)
				{
					$query = $db->getQuery(true);
					$query->select('id')
						->from('#__judownload_fields');

					if (preg_match('/^field_(\d+)$/', $column, $matches))
					{
						$id = $matches[1];
						$query->where('id = ' . $id);
					}
					elseif (is_string($column))
					{
						$query->where('( field_name = ' . $db->quote($column) . ' OR caption = ' . $db->quote($column) . ' )');
					}

					$db->setQuery($query);

					$fieldId = $db->loadResult();

					if ($fieldId)
					{
						$mappedColumns[$column] = $fieldId;
					}
					else
					{
						$mappedColumns[$column] = $column;
					}
				}
		}

		return $mappedColumns;
	}

	
	public static function getTemplateOptions()
	{
		
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->select('tpl.id AS value,plg.title AS text');
		$query->from('#__judownload_plugins AS plg');
		$query->join('', '#__judownload_templates AS tpl ON tpl.plugin_id = plg.id');
		$query->where('plg.type =' . $db->quote('template'));
		$query->order('tpl.lft ASC');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return $options;
	}

	
	public static function canUploadTemplateFile($file, $err = '')
	{
		$params = JUDownloadHelper::getParams();

		if (empty($file['name']))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_JUDOWNLOAD_ERROR_UPLOAD_INPUT'), 'error');

			return false;
		}

		
		$executable       = array(
			'exe', 'phtml', 'java', 'perl', 'py', 'asp', 'dll', 'go', 'jar',
			'ade', 'adp', 'bat', 'chm', 'cmd', 'com', 'cpl', 'hta', 'ins', 'isp',
			'jse', 'lib', 'mde', 'msc', 'msp', 'mst', 'pif', 'scr', 'sct', 'shb',
			'sys', 'vb', 'vbe', 'vbs', 'vxd', 'wsc', 'wsf', 'wsh'
		);
		$explodedFileName = explode('.', $file['name']);

		if (count($explodedFileName > 2))
		{
			foreach ($executable AS $extensionName)
			{
				if (in_array($extensionName, $explodedFileName))
				{
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('COM_JUDOWNLOAD_ERROR_EXECUTABLE'), 'error');

					return false;
				}
			}
		}

		jimport('joomla.filesystem.file');

		if ($file['name'] !== JFile::makeSafe($file['name']) || preg_match('/\s/', JFile::makeSafe($file['name'])))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_JUDOWNLOAD_ERROR_WARNFILENAME'), 'error');

			return false;
		}

		$format = strtolower(JFile::getExt($file['name']));

		$imageTypes   = explode(',', $params->get('template_image_formats', 'gif,bmp,jpg,jpeg,png'));
		$sourceTypes  = explode(',', $params->get('template_source_formats', 'txt,less,ini,xml,js,php,css'));
		$fontTypes    = explode(',', $params->get('template_font_formats', 'woff,ttf,otf'));
		$archiveTypes = explode(',', $params->get('template_compressed_formats', 'zip'));

		$allowable = array_merge($imageTypes, $sourceTypes, $fontTypes, $archiveTypes);

		if ($format == '' || $format == false || (!in_array($format, $allowable)))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_JUDOWNLOAD_ERROR_WARNFILETYPE'), 'error');

			return false;
		}

		if (in_array($format, $archiveTypes))
		{
			
			$zip = new ZipArchive;

			if ($zip->open($file['tmp_name']) === true)
			{
				for ($i = 0; $i < $zip->numFiles; $i++)
				{
					$entry     = $zip->getNameIndex($i);
					$endString = substr($entry, -1);

					if ($endString != DIRECTORY_SEPARATOR)
					{
						$explodeArray = explode('.', $entry);
						$ext          = end($explodeArray);

						if (!in_array($ext, $allowable))
						{
							$app = JFactory::getApplication();
							$app->enqueueMessage(JText::_('COM_JUDOWNLOAD_FILE_UNSUPPORTED_ARCHIVE'), 'error');

							return false;
						}
					}
				}
			}
			else
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('COM_JUDOWNLOAD_FILE_ARCHIVE_OPEN_FAIL'), 'error');

				return false;
			}
		}

		
		$maxSize = (int) ($params->get('template_upload_limit', 2) * 1024 * 1024);

		if ($maxSize > 0 && (int) $file['size'] > $maxSize)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_JUDOWNLOAD_ERROR_WARNFILETOOLARGE'), 'error');

			return false;
		}

		$xss_check = file_get_contents($file['tmp_name'], false, null, -1, 256);
		$html_tags = array(
			'abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink', 'blockquote',
			'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div',
			'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html',
			'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext', 'link', 'listing',
			'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object', 'ol', 'optgroup', 'option',
			'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike',
			'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml',
			'xmp', '!DOCTYPE', '!--'
		);

		foreach ($html_tags AS $tag)
		{
			
			if (stristr($xss_check, '<' . $tag . ' ') || stristr($xss_check, '<' . $tag . '>'))
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('COM_JUDOWNLOAD_ERROR_WARNIEXSS'), 'error');

				return false;
			}
		}

		return true;
	}

	public static function getComVersion($comName = true, $comVersion = true)
	{
		$app    = JFactory::getApplication();
		$option = $app->input->get('option', '');
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$query->select('manifest_cache')
			->from('#__extensions')
			->where('element = ' . $db->quote($option));
		$db->setQuery($query);
		$result   = $db->loadResult();
		$manifest = new JRegistry($result);
		$version  = array();
		if ($comName)
		{
			$name = $manifest->get('name');
			if (!JUDLPROVERSION)
			{
				$name .= ' Lite';
			}
			$version[] = $name;
		}

		if ($comVersion)
		{
			$version[] = 'Version ' . $manifest->get('version');
		}

		return implode(" - ", $version);
	}

}