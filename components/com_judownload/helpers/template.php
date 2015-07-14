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

class JUDownloadFrontHelperTemplate
{
	
	protected static $cache = array();

	
	public static function getTemplatePathWithoutRoot($templateId)
	{
		$storeId = md5(__METHOD__ . "::" . (int) $templateId);
		if (!isset(self::$cache[$storeId]))
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
			$templateTable = JTable::getInstance('Template', 'JUDownloadTable');
			$path          = $templateTable->getPath($templateId);
			if (is_array($path) && count($path))
			{
				array_shift($path);
			}
			self::$cache[$storeId] = $path;
		}

		return self::$cache[$storeId];
	}

	
	public static function updateStyleIdForCatDocUsingDefaultStyle($styleIdDefault)
	{
		$db = JFactory::getDbo();

		
		$query = $db->getQuery(true);
		$query->update('#__judownload_categories');
		$query->set('style_id = ' . $styleIdDefault);
		$query->where('style_id = -2');
		$query->where('parent_id != 0');
		$db->setQuery($query);
		$db->execute();

		$query = $db->getQuery(true);
		$query->update('#__judownload_documents');
		$query->set('style_id = ' . $styleIdDefault);
		$query->where('style_id = -2');
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	
	public static function removeTemplateParamsOfCatDocUsingDefaultStyle()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->update('#__judownload_categories');
		$query->set('template_params = ""');
		$query->where('style_id = -2');
		$db->setQuery($query);
		$db->execute();

		$query = $db->getQuery(true);
		$query->update('#__judownload_documents');
		$query->set('template_params = ""');
		$query->where('style_id = -2');
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	
	public static function removeTemplateParamsOfInheritedStyleCatDoc($categoryId)
	{
		$categoryIdArray = self::getInheritedStyleCatArray($categoryId);

		if (is_array($categoryIdArray) && count($categoryIdArray))
		{
			$db = JFactory::getDbo();

			$categoryIdString = implode(',', $categoryIdArray);
			$query            = $db->getQuery(true);
			$query->update('#__judownload_categories');
			$query->set('template_params = ""');
			$query->where('id IN (' . $categoryIdString . ')');
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->update('#__judownload_documents AS d');
			$query->join('', '#__judownload_documents_xref AS dxref ON d.id = dxref.doc_id AND dxref.main = 1');
			$query->set('d.template_params = ""');
			$query->where('d.style_id = -1');
			$query->where('dxref.cat_id IN (' . $categoryIdString . ')');
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	
	public static function getInheritedStyleCatArray($categoryId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from('#__judownload_categories');
		$query->where('parent_id = ' . $categoryId);
		$query->where('style_id = -1');
		$db->setQuery($query);
		$categoryIdArray = $db->loadColumn();

		if (is_array($categoryIdArray) && count($categoryIdArray))
		{
			foreach ($categoryIdArray AS $categoryValue)
			{
				$categoryIdArray = array_merge($categoryIdArray, self::getInheritedStyleCatArray($categoryValue));
			}
		}

		return $categoryIdArray;
	}

	
	public static function getTemplateStyleOfCategory($categoryId)
	{
		$categoryPath = JUDownloadHelper::getCategoryPath($categoryId);

		$categoryPathReverse = array_reverse($categoryPath);

		foreach ($categoryPathReverse AS $categoryObject)
		{
			if ($categoryObject->style_id == -2)
			{
				$templateStyleObject = self::getDefaultTemplateStyle();

				return $templateStyleObject;
			}
			elseif ($categoryObject->style_id == -1)
			{
				
				if ($categoryObject->parent_id == 0)
				{
					$templateStyleObject = self::getDefaultTemplateStyle();

					return $templateStyleObject;
				}
				else
				{
					continue;
				}
			}
			else
			{
				$templateStyleObject = self::getTemplateStyleObject($categoryObject->style_id);
				if (is_object($templateStyleObject))
				{
					return $templateStyleObject;
				}
			}
		}

		$templateStyleObject = self::getDefaultTemplateStyle();

		return $templateStyleObject;
	}

	
	public static function getTemplateStyleOfDocument($documentId)
	{
		$storeId = md5(__METHOD__ . "::" . (int) $documentId);

		if (!isset(self::$cache[$storeId]))
		{
			$documentObject = JUDownloadHelper::getDocumentById($documentId);
			$styleId        = $documentObject->style_id;
			if ($styleId == -2)
			{
				$templateStyleObject = self::getDefaultTemplateStyle();
			}
			elseif ($styleId == -1)
			{
				$mainCategoryId      = JUDownloadFrontHelperCategory::getMainCategoryId($documentId);
				$templateStyleObject = self::getTemplateStyleOfCategory($mainCategoryId);
			}
			else
			{
				$templateStyleObject = self::getTemplateStyleObject($styleId);
			}

			if (is_object($templateStyleObject))
			{
				self::$cache[$storeId] = $templateStyleObject;
			}
			else
			{
				self::$cache[$storeId] = self::getDefaultTemplateStyle();
			}
		}

		return self::$cache[$storeId];
	}

	
	public static function getTemplateStyleObject($styleId)
	{
		$storeId = md5(__METHOD__ . "::" . (int) $styleId);

		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('style.*');
			$query->select('plg.title AS template_title, plg.folder');
			$query->from('#__judownload_template_styles AS style');
			$query->join('', '#__judownload_templates AS tpl ON tpl.id = style.template_id');
			$query->join('', '#__judownload_plugins AS plg ON plg.id = tpl.plugin_id');
			$query->where('style.id = ' . $styleId);
			$db->setQuery($query);

			self::$cache[$storeId] = $db->loadObject();
		}

		return self::$cache[$storeId];
	}

	
	public static function getTemplateObject($templateId)
	{
		$storeId = md5(__METHOD__ . "::" . (int) $templateId);

		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('tpl.*');
			$query->select('plg.title,plg.folder');
			$query->from('#__judownload_templates AS tpl');
			$query->join('', '#__judownload_plugins AS plg ON plg.id = tpl.plugin_id');
			$query->where('tpl.id = ' . $templateId);
			$db->setQuery($query);
			self::$cache[$storeId] = $db->loadObject();
		}

		return self::$cache[$storeId];
	}

	
	public static function getHomeTemplateStyle()
	{
		$storeId = md5(__METHOD__);

		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('style.*');
			$query->select('plg.title AS template_title, plg.folder');
			$query->from('#__judownload_template_styles AS style');
			$query->join('', '#__judownload_templates AS tpl ON tpl.id = style.template_id');
			$query->join('', '#__judownload_plugins AS plg ON plg.id = tpl.plugin_id');
			$query->where('style.home = 1');
			$query->where("plg.type = " . $db->quote('template'));
			$db->setQuery($query);
			$templateStyleObject = $db->loadObject();

			self::$cache[$storeId] = $templateStyleObject;
		}

		return self::$cache[$storeId];
	}

	
	public static function getHomeTemplateStyleByLanguage()
	{
		$user         = JFactory::getUser();
		$lang         = JFactory::getLanguage();
		$userLanguage = $user->getParam('language', $lang->getTag());

		$storeId = md5(__METHOD__ . "::" . $userLanguage);
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('style.*');
			$query->select('plg.title AS template_title, plg.folder');
			$query->from('#__judownload_template_styles AS style');
			$query->join('', '#__judownload_templates AS tpl ON tpl.id = style.template_id');
			$query->join('', '#__judownload_plugins AS plg ON plg.id = tpl.plugin_id');
			$query->where('style.home = ' . $db->quote($userLanguage));
			$query->where('plg.type = ' . $db->quote('template'));
			$db->setQuery($query);
			$templateStyleObject = $db->loadObject();

			self::$cache[$storeId] = $templateStyleObject;
		}

		return self::$cache[$storeId];
	}

	
	public static function getDefaultTemplateStyle($byUserLanguage = false)
	{
		$app = JFactory::getApplication();

		$storeId = md5(__METHOD__ . "::" . (int) $byUserLanguage);
		if (!isset(self::$cache[$storeId]))
		{
			if ($byUserLanguage)
			{
				$templateStyleObjectByLang = JUDownloadFrontHelperTemplate::getHomeTemplateStyleByLanguage();
				$templateStyleObjectByHome = JUDownloadFrontHelperTemplate::getHomeTemplateStyle();
				if (!is_object($templateStyleObjectByLang) || $templateStyleObjectByLang->template_id != $templateStyleObjectByHome->template_id)
				{
					$templateStyleObject = $templateStyleObjectByHome;
				}
				else
				{
					$templateStyleObject = $templateStyleObjectByLang;
				}
			}
			else
			{
				$templateStyleObject = JUDownloadFrontHelperTemplate::getHomeTemplateStyle();
			}

			
			if ($app->isSite())
			{
				if (!is_object($templateStyleObject))
				{
					$templateStyleObject = new stdClass();
				}

				if (!isset($templateStyleObject->folder) || !$templateStyleObject->folder)
				{
					$templateStyleObject->folder = 'default';
				}
			}

			self::$cache[$storeId] = $templateStyleObject;
		}

		return self::$cache[$storeId];
	}

	
	public static function getCurrentTemplateStyle($view = '', $id = null)
	{
		$app    = JFactory::getApplication();
		$jInput = $app->input;

		
		if (!$view)
		{
			$view = $jInput->getString('view', '');
		}

		
		if ($jInput->getString('option', '') != 'com_judownload')
		{
			$view = '';
		}

		
		if (!$id)
		{
			switch ($view)
			{
				case 'form':
					$id           = $jInput->getInt('id', 0);
					$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();
					$cat_id       = $jInput->getInt('cat_id', $rootCategory->id);
					break;
				case 'document':
					$id = $jInput->getInt('id', 0);
					break;
				case 'category':
					$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();
					$id           = $jInput->getInt('id', $rootCategory->id);
					break;
			}
		}

		switch ($view)
		{
			
			case 'form':
				if ($id)
				{
					$templateStyleObject         = self::getTemplateStyleOfDocument($id);
					$documentObject              = JUDownloadHelper::getDocumentById($id);
					$templateStyleObject->params = self::getTemplateStyleParams($templateStyleObject->id, $documentObject->template_params);
				}
				else
				{
					$templateStyleObject         = self::getTemplateStyleOfCategory($cat_id);
					$categoryObject              = JUDownloadHelper::getCategoryById($cat_id);
					$templateStyleObject->params = self::getTemplateStyleParams($templateStyleObject->id, $categoryObject->template_params);
				}
				break;
			case 'document':
				$templateStyleObject         = self::getTemplateStyleOfDocument($id);
				$documentObject              = JUDownloadHelper::getDocumentById($id);
				$templateStyleObject->params = self::getTemplateStyleParams($templateStyleObject->id, $documentObject->template_params);
				break;
			
			case 'category':
				$templateStyleObject         = self::getTemplateStyleOfCategory($id);
				$categoryObject              = JUDownloadHelper::getCategoryById($id);
				$templateStyleObject->params = self::getTemplateStyleParams($templateStyleObject->id, $categoryObject->template_params);
				break;
			
			default:
				$templateStyleObject         = self::getDefaultTemplateStyle();
				$templateStyleObject->params = self::getTemplateStyleParams($templateStyleObject->id);
				break;
		}

		return $templateStyleObject;
	}

	
	public static function getTemplateStyleParams($templateStyleId, $paramsOverride = null)
	{
		$storeId = md5(__METHOD__ . "::" . (int) $templateStyleId . "::" . serialize($paramsOverride));
		if (!isset(self::$cache[$storeId]))
		{
			$registry = new JRegistry;
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
			$styleTable        = JTable::getInstance('Style', 'JUDownloadTable');
			$templateStylePath = $styleTable->getPath($templateStyleId);
			if (is_array($templateStylePath) && count($templateStylePath))
			{
				array_shift($templateStylePath);
			}

			foreach ($templateStylePath AS $templateStyleItem)
			{
				$registry->loadString($templateStyleItem->params);
			}

			if ($paramsOverride)
			{
				$registry->loadString($paramsOverride);
			}

			self::$cache[$storeId] = $registry;
		}

		return self::$cache[$storeId];
	}
}