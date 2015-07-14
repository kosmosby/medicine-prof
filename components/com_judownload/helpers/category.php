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

class JUDownloadFrontHelperCategory
{
	
	protected static $cache = array();

	public static function getJoomlaTemplate($view = 'category')
	{
		$clientId = 0;
		$client   = JApplicationHelper::getClientInfo($clientId);

		$extn = 'com_judownload';

		$lang = JFactory::getLanguage();

		$items = array();

		
		if ($extn && $view && $client)
		{
			$lang->load($extn . '.sys', JPATH_ADMINISTRATOR, null, false, false)
			|| $lang->load($extn . '.sys', JPATH_ADMINISTRATOR . '/components/' . $extn, null, false, false)
			|| $lang->load($extn . '.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
			|| $lang->load($extn . '.sys', JPATH_ADMINISTRATOR . '/components/' . $extn, $lang->getDefault(), false, false);

			
			$component_path = JPath::clean($client->path . '/components/' . $extn . '/views/' . $view . '/tmpl');

			
			$groups = array();

			

			$groups['inherit']          = array();
			$groups['inherit']['id']    = 'layout_inherit';
			$groups['inherit']['text']  = '---' . JText::_('COM_JUDOWNLOAD_INHERIT') . '---';
			$groups['inherit']['items'] = array();

			
			if (is_dir($component_path) && ($component_layouts = JFolder::files($component_path, '^[^_]*\.xml$', false, true)))
			{
				
				$groups['_']          = array();
				$groups['_']['id']    = 'layout__';
				$groups['_']['text']  = JText::sprintf('JOPTION_FROM_COMPONENT');
				$groups['_']['items'] = array();

				foreach ($component_layouts AS $i => $file)
				{
					
					if (!$xml = simplexml_load_file($file))
					{
						unset($component_layouts[$i]);

						continue;
					}

					
					if (!$menu = $xml->xpath('layout[1]'))
					{
						unset($component_layouts[$i]);

						continue;
					}

					$menu = $menu[0];

					
					$value                  = JFile::stripext(JFile::getName($file));
					$component_layouts[$i]  = $value;
					$text                   = isset($menu['option']) ? JText::_($menu['option']) : (isset($menu['title']) ? JText::_($menu['title']) : $value);
					$groups['_']['items'][] = JHtml::_('select.option', '_:' . $value, $text);
					$items['_'][$value]     = $text;
				}
			}
		}

		return $items;
	}

	public static function getLayoutCategory($cat_id, $items, $layout = null)
	{
		if ($layout == null)
		{
			$params = JUDownloadHelper::getParams($cat_id);
			$layout = $params->get("layout_category", '_:default');
		}

		if ($layout)
		{
			$layout = explode(":", $layout);

			if ($layout[0] == "_")
			{
				return "(Component &gt; " . $items['_'][$layout[1]] . ")";
			}
			else
			{
				return "($layout[0] &gt; " . $items[$layout[0]][$layout[1]] . ")";
			}
		}
	}

	public static function calculatorInheritCategoryLayout($items, $cat_id)
	{
		do
		{
			$category = JUDownloadHelper::getCategoryById($cat_id);
			$layout   = $category->layout;
			$cat_id   = $category->parent_id;
		} while ($layout == -1);

		if ($layout == -2)
		{
			return JUDownloadFrontHelperCategory::getLayoutCategory($cat_id, $items);
		}
		else
		{
			return JUDownloadFrontHelperCategory::getLayoutCategory($cat_id, $items, $layout);
		}
	}

	public static function getLayoutDocument($cat_id, $items, $layout = null)
	{
		if ($layout == null)
		{
			$params = JUDownloadHelper::getParams($cat_id);
			$layout = $params->get("layout_document", '_:default');
		}
		if ($layout)
		{
			$layout = explode(":", $layout);
			if ($layout[0] == "_")
			{
				return "(Component &gt; " . $items['_'][$layout[1]] . " )";
			}
			else
			{
				return "(" . $layout[0] . " &gt; " . $items[$layout[0]][$layout[1]] . ")";
			}
		}
	}

	public static function calculatorInheritDocumentLayout($items, $cat_id)
	{
		do
		{
			$category = JUDownloadHelper::getCategoryById($cat_id);
			$layout   = $category->layout_document;
			$cat_id   = $category->parent_id;
		} while ($layout == -1);

		if ($layout == -2)
		{
			return JUDownloadFrontHelperCategory::getLayoutDocument($cat_id, $items);
		}
		else
		{
			return JUDownloadFrontHelperCategory::getLayoutDocument($cat_id, $items, $layout);
		}
	}

	
	public static function getRootCategory()
	{
		return JUDownloadHelper::getCategoryById(1);
	}

	
	public static function getCategory($catId, $select = '*', $checkLanguage = false, $checkAccess = true)
	{
		if (empty($catId))
		{
			return null;
		}

		
		if (strpos(",", $select) !== false)
		{
			$selectColumnArr = array_map('trim', explode(',', $select));
			sort($selectColumnArr);
			$select = implode(",", $selectColumnArr);
		}

		$storeId = md5(__METHOD__ . "::$catId::$select" . (int) $checkLanguage . "::" . (int) $checkAccess);
		if (!isset(self::$cache[$storeId]))
		{
			$db       = JFactory::getDbo();
			$nullDate = $db->getNullDate();
			$nowDate  = JFactory::getDate()->toSql();

			$query = $db->getQuery(true);
			$query->select($select);
			$query->from('#__judownload_categories');
			$query->where('published = 1');
			$query->where('(publish_up = ' . $db->quote($nullDate) . ' OR publish_up <= ' . $db->quote($nowDate) . ')');
			$query->where('(publish_down = ' . $db->quote($nullDate) . ' OR publish_down > ' . $db->quote($nowDate) . ')');

			
			if ($checkAccess)
			{
				$user      = JFactory::getUser();
				$levels    = $user->getAuthorisedViewLevels();
				$levelsStr = implode(',', $levels);
				$query->where('access IN (' . $levelsStr . ')');
			}

			if ($checkLanguage)
			{
				
				$app         = JFactory::getApplication();
				$tagLanguage = JFactory::getLanguage()->getTag();
				if ($app->getLanguageFilter())
				{
					$query->where('language IN (' . $db->quote($tagLanguage) . ',' . $db->quote('*') . ',' . $db->quote('') . ')');
				}
			}

			$query->where('id = ' . $catId);
			$db->setQuery($query);
			$catObj = $db->loadObject();
			if (!is_object($catObj))
			{
				self::$cache[$storeId] = false;

				return false;
			}

			if (isset($catObj->images))
			{
				$registry = new JRegistry;
				$registry->loadString($catObj->images);
				$catObj->images = $registry->toObject();
			}

			if (isset($catObj->id))
			{
				$catObj->params = JUDownloadFrontHelperCategory::getCategoryDisplayParams($catObj->id);
			}

			if (isset($catObj->template_params))
			{
				$catObj->template_params = new JRegistry($catObj->template_params);
			}

			self::$cache[$storeId] = $catObj;
		}

		return self::$cache[$storeId];
	}

	
	public static function getMainCategory($documentId)
	{
		if (!$documentId)
		{
			return null;
		}

		$storeId = md5(__METHOD__ . "::$documentId");
		if (!isset(self::$cache[$storeId]))
		{
			$docObj = JUDownloadHelper::getDocumentById($documentId);
			if (!$docObj)
			{
				return null;
			}
			$catObj                = JUDownloadHelper::getCategoryById($docObj->cat_id);
			self::$cache[$storeId] = $catObj;
		}

		return self::$cache[$storeId];
	}

	
	public static function getMainCategoryId($documentId, $resetCache = false)
	{
		$docObj = JUDownloadHelper::getDocumentById($documentId, $resetCache);

		if (is_object($docObj))
		{
			return $docObj->cat_id;
		}

		return null;
	}

	
	public static function getCategoriesRecursive($categoryId, $checkLanguage = false, $checkAccess = true, $fetchSelfCategory = false, $countCat = false, $countDoc = false, $getIdOnly = false)
	{
		
		if (!$categoryId)
		{
			$categoryId = 1;
		}

		$storeId = md5(__METHOD__ . "::$categoryId::" . (int) $checkLanguage . "::" . (int) $checkAccess . "::" . (int) $fetchSelfCategory . "::" . (int) $countCat . "::" . (int) $countDoc . "::" . (int) $getIdOnly);
		if (!isset(self::$cache[$storeId]))
		{
			
			$user   = JFactory::getUser();
			$levels = $user->getAuthorisedViewLevels();

			
			$nowDate = JFactory::getDate()->toSql();

			
			JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_judownload/tables");
			$categoryTable = JTable::getInstance('Category', 'JUDownloadTable');

			
			$nestedCategories = $categoryTable->customGetTree($categoryId);

			
			if (!is_array($nestedCategories) || empty($nestedCategories))
			{
				return array();
			}

			
			$validCategories  = array();
			$validCategoryIds = array();

			
			$filterLanguage = false;
			if ($checkLanguage)
			{
				$app = JFactory::getApplication();
				if ($app->getLanguageFilter())
				{
					$filterLanguage   = true;
					$languageTag      = JFactory::getLanguage()->getTag();
					$languageTagArray = array('', '*', $languageTag);
				}
			}

			foreach ($nestedCategories AS $key => $category)
			{
				
				if ($key == 0)
				{
					
					if ($checkAccess)
					{
						if (!in_array($category->access, $levels))
						{
							return array();
						}
					}

					
					if ($category->published != 1)
					{
						return array();
					}

					
					if ($category->publish_up > $nowDate)
					{
						return array();
					}

					
					if (intval($category->publish_down) != 0 && $category->publish_down < $nowDate)
					{
						return array();
					}

					
					if ($filterLanguage)
					{
						if (!in_array($category->language, $languageTagArray))
						{
							return array();
						}
					}

					
					if ($fetchSelfCategory)
					{
						$validCategories[]  = $category;
						$validCategoryIds[] = $category->id;
					}
					else
					{
						$validCategoryIds[] = $category->id;
					}
				}
				else
				{
					
					if (!in_array($category->parent_id, $validCategoryIds))
					{
						unset($nestedCategories[$key]);
						continue;
					}

					
					if ($checkAccess)
					{
						if (!in_array($category->access, $levels))
						{
							unset($nestedCategories[$key]);
							continue;
						}
					}

					
					if ($category->published != 1)
					{
						unset($nestedCategories[$key]);
						continue;
					}

					
					if ($category->publish_up > $nowDate)
					{
						unset($nestedCategories[$key]);
						continue;
					}

					
					if (intval($category->publish_down) != 0 && $category->publish_down < $nowDate)
					{
						unset($nestedCategories[$key]);
						continue;
					}

					
					if ($filterLanguage)
					{
						if (!in_array($category->language, $languageTagArray))
						{
							unset($nestedCategories[$key]);
							continue;
						}
					}

					
					$validCategories[]  = $category;
					$validCategoryIds[] = $category->id;
				}
			}

			$path = array();
			foreach ($validCategories AS $validCategory)
			{
				if (isset($path[$validCategory->parent_id]))
				{
					$path[$validCategory->id] = $path[$validCategory->parent_id];
					array_unshift($path[$validCategory->id], $validCategory->parent_id);
				}
				else
				{
					$path[$validCategory->id] = array($validCategory->parent_id);
				}
			}

			foreach ($validCategories AS $validCategory)
			{
				if (isset($path[$validCategory->id]))
				{
					$validCategory->path = $path[$validCategory->id];
				}
			}

			$countChild       = array();
			$countChildNested = array();
			$childCategoryId  = array();
			foreach ($validCategories AS $validCategory)
			{
				if (!isset($countChild[$validCategory->parent_id]))
				{
					$countChild[$validCategory->parent_id] = 1;
				}
				else
				{
					$countChild[$validCategory->parent_id] += 1;
				}

				foreach ($validCategory->path AS $pathId)
				{
					if (isset($countChildNested[$pathId]))
					{
						$countChildNested[$pathId] += 1;
					}
					else
					{
						$countChildNested[$pathId] = 1;
					}

					if (isset($childCategoryId[$pathId]))
					{
						array_push($childCategoryId[$pathId], $validCategory->id);
					}
					else
					{
						$childCategoryId[$pathId] = array($validCategory->id);
					}
				}
			}

			foreach ($validCategories AS $validCategory)
			{
				if (isset($countChild[$validCategory->id]))
				{
					$validCategory->total_childs = $countChild[$validCategory->id];
				}
				else
				{
					$validCategory->total_childs = 0;
				}

				if (isset($childCategoryId[$validCategory->id]))
				{
					$validCategory->nested_cat_array = $childCategoryId[$validCategory->id];
				}

				if (isset($validCategory->nested_cat_array))
				{
					if (is_array($validCategory->nested_cat_array) && count($validCategory->nested_cat_array) > 0)
					{
						$includedCategoryArr = array_merge(array($validCategory->id), $validCategory->nested_cat_array);
					}
					else
					{
						$includedCategoryArr = array($validCategory->id);
					}
				}
				else
				{
					$includedCategoryArr = array($validCategory->id);
				}

				if ($countCat)
				{
					$validCategory->total_nested_categories = count($includedCategoryArr) - 1;

				}

				if ($countDoc)
				{
					$validCategory->total_documents = JUDownloadFrontHelperCategory::getTotalDocumentsInCategory($validCategory->id, $includedCategoryArr);
				}

			}

			
			if ($getIdOnly)
			{
				if (!empty($validCategories))
				{
					foreach ($validCategories AS $keyValidCategory => $valueValidCategory)
					{
						$validCategories[$keyValidCategory] = $valueValidCategory->id;
					}
				}
			}

			self::$cache[$storeId] = $validCategories;
		}

		return self::$cache[$storeId];
	}

	
	public static function getCategoryIdsRecursive($categoryId)
	{
		$storeId = md5(__METHOD__ . "::$categoryId");
		if (!isset(self::$cache[$storeId]))
		{
			$catIdArr = array();

			$nestedCategories = JUDownloadFrontHelperCategory::getCategoriesRecursive($categoryId, false, true);
			if (!empty($nestedCategories))
			{
				foreach ($nestedCategories AS $categoryObj)
				{
					$catIdArr[] = $categoryObj->id;
				}
			}

			self::$cache[$storeId] = $catIdArr;
		}

		return self::$cache[$storeId];
	}

	
	public static function getAccessibleCategoryIds()
	{
		$storeId = md5(__CLASS__ . '::AccessibleCategoryIds');
		if (!isset(self::$cache[$storeId]))
		{
			
			$catIdArray = JUDownloadFrontHelperCategory::getCategoryIdsRecursive(1);
			
			array_unshift($catIdArray, 1);
			self::$cache[$storeId] = $catIdArray;
		}

		return self::$cache[$storeId];
	}

	
	public static function getTotalSubCategoriesInCategory($categoryId, $nestedCategories = null)
	{
		
		if ($nestedCategories == null)
		{
			$nestedCategories = JUDownloadFrontHelperCategory::getCategoriesRecursive($categoryId, true, true, true);
		}

		
		
		$total = count($nestedCategories) - 1;
		if ($total < 0)
		{
			$total = 0;
		}

		return $total;
	}

	
	public static function getCategoryLayout($catId)
	{
		$path = JUDownloadHelper::getCategoryPath($catId);

		$pathCatToRoot = array_reverse($path);

		foreach ($pathCatToRoot AS $category)
		{
			if ($category->layout == -2)
			{
				$params = JUDownloadHelper::getParams($catId);
				$layout = $params->get('layout_category', '_:default');

				return $layout;
			}
			elseif ($category->layout == -1)
			{
				
				if ($category->parent_id == 0)
				{
					$params = JUDownloadHelper::getParams($catId);
					$layout = $params->get('layout_category', '_:default');

					return $layout;
				}
				else
				{
					continue;
				}
			}
			else
			{
				$layout = trim($category->layout);

				return $layout;
			}
		}

		
		return '_:default';
	}

	
	public static function getCategoryViewLayout($layoutUrl, $categoryId)
	{
		
		if ($layoutUrl)
		{
			$layout = $layoutUrl;
		}
		else
		{
			$app = JFactory::getApplication();
			
			$activeMenuItem = $app->getMenu()->getActive();
			if (($activeMenuItem) && ($activeMenuItem->component == 'com_judownload') && ((strpos($activeMenuItem->link, 'view=category') > 0) && (strpos($activeMenuItem->link, '&id=' . (string) $categoryId) > 0)))
			{
				$activeMenuItemId = $activeMenuItem->id;
			}
			else
			{
				$activeMenuItemId = false;
			}

			
			if ($activeMenuItemId)
			{
				$menus = $app->getMenu();
				$menu  = $menus->getItem($activeMenuItemId);
				if (isset($menu->query['layout']))
				{
					$layout = $menu->query['layout'];
				}
				else
				{
					$layout = 'default';
				}
			}
			
			else
			{
				$layout = 'default';
			}
		}

		
		if (empty($layout))
		{
			$layout = 'default';
		}

		return $layout;
	}

	
	public static function getTotalDocumentsInCategory($categoryId, $nestedCategoryIds = null)
	{
		
		if (is_null($nestedCategoryIds))
		{
			$nestedCategories  = JUDownloadFrontHelperCategory::getCategoriesRecursive($categoryId, true, true, true);
			$nestedCategoryIds = array();
			foreach ($nestedCategories AS $nestedCategory)
			{
				$nestedCategoryIds[] = $nestedCategory->id;
			}
		}

		if (!is_array($nestedCategoryIds) || !count($nestedCategoryIds))
		{
			return 0;
		}

		$storeId = md5(__METHOD__ . "::" . serialize($nestedCategoryIds));
		if (!isset(self::$cache[$storeId]))
		{
			$user      = JFactory::getUser();
			$levels    = $user->getAuthorisedViewLevels();
			$levelsStr = implode(',', $levels);

			$db       = JFactory::getDbo();
			$nullDate = $db->getNullDate();
			$nowDate  = JFactory::getDate()->toSql();

			
			$query = $db->getQuery(true);
			$query->select('COUNT(*)');
			$query->from('#__judownload_documents AS d');
			$query->join('', '#__judownload_documents_xref AS dx ON d.id = dx.doc_id');
			$query->join('', '#__judownload_categories AS c ON c.id = dx.cat_id');

			if (is_array($nestedCategoryIds) && count($nestedCategoryIds) > 0)
			{
				$query->where('(c.id IN (' . implode(",", $nestedCategoryIds) . '))');
			}
			else
			{
				$query->where('(c.id IN (""))');
			}

			$query->join('', '#__judownload_documents_xref AS dxmain ON d.id = dxmain.doc_id AND dxmain.main = 1');
			$query->join('', '#__judownload_categories AS cmain ON cmain.id = dxmain.cat_id');

			
			$categoryIdArrayCanAccess = JUDownloadFrontHelperCategory::getAccessibleCategoryIds();
			if (is_array($categoryIdArrayCanAccess) && count($categoryIdArrayCanAccess) > 0)
			{
				$query->where('cmain.id IN(' . implode(",", $categoryIdArrayCanAccess) . ')');
			}
			else
			{
				$query->where('cmain.id IN("")');
			}

			
			$query->where('d.approved = 1');

			
			$query->where('d.published = 1');
			$query->where('(d.publish_up = ' . $db->quote($nullDate) . ' OR d.publish_up <= ' . $db->quote($nowDate) . ')');
			$query->where('(d.publish_down = ' . $db->quote($nullDate) . ' OR d.publish_down >= ' . $db->quote($nowDate) . ')');

			
			if ($user->get('guest'))
			{
				$query->where('d.access IN (' . $levelsStr . ')');
			}
			else
			{
				$query->where('(d.access IN (' . $levelsStr . ') OR d.created_by = ' . $user->id . ')');
			}

			
			$app         = JFactory::getApplication();
			$tagLanguage = JFactory::getLanguage()->getTag();
			if ($app->getLanguageFilter())
			{
				$query->where('d.language IN (' . $db->quote($tagLanguage) . ',' . $db->quote('*') . ',' . $db->quote('') . ')');
			}

			$db->setQuery($query);

			self::$cache[$storeId] = $db->loadResult();
		}

		return self::$cache[$storeId];
	}

	
	public static function getCategoryDisplayParams($cat_id)
	{
		if (!$cat_id)
		{
			return false;
		}

		$params                = JUDownloadHelper::getParams($cat_id);
		$global_display_params = new JRegistry(isset($params->get('display_params')->cat) ? $params->get('display_params')->cat : array());
		$catObj                = JUDownloadHelper::getCategoryById($cat_id);
		$category_params       = $catObj->params;
		if ($category_params)
		{
			$category_params         = json_decode($category_params);
			$category_display_params = $category_params->display_params;
			if ($category_display_params)
			{
				foreach ($category_display_params AS $option => $value)
				{
					if ($value == -2)
					{
						unset($category_display_params->$option);
					}
				}

				$global_display_params->loadObject($category_display_params);
			}
		}

		return $global_display_params;
	}

}