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

class JUDownloadFrontHelperDocument
{
	
	protected static $cache = array();

	
	public static function getDocument($documentId, $checkLanguage = false, $checkAccess = true, $documentObject = null)
	{
		if (!$documentId)
		{
			return null;
		}

		

		$storeId = md5(__METHOD__ . "::$documentId::" . (int) $checkLanguage . "::" . (int) $checkAccess);
		if (!isset(self::$cache[$storeId]))
		{
			if (empty($documentObject))
			{
				$documentObject = JUDownloadHelper::getDocumentById($documentId);
			}

			$nowDate = JFactory::getDate()->toSql();

			if (!is_object($documentObject))
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			if ($documentObject->approved != 1)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			if ($documentObject->published != 1)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			if ($documentObject->publish_up > $nowDate)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			if ($documentObject->publish_down != '0000-00-00 00:00:00' && $documentObject->publish_down < $nowDate)
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			if ($checkAccess)
			{
				$user   = JFactory::getUser();
				$levels = $user->getAuthorisedViewLevels();

				if ($user->get('guest'))
				{
					if (!in_array($documentObject->access, $levels))
					{
						self::$cache[$storeId] = false;

						return self::$cache[$storeId];
					}
				}
				else
				{
					if (!in_array($documentObject->access, $levels) && $documentObject->created_by != $user->id)
					{
						self::$cache[$storeId] = false;

						return self::$cache[$storeId];
					}
				}
			}

			if ($checkLanguage)
			{
				
				$app         = JFactory::getApplication();
				$tagLanguage = JFactory::getLanguage()->getTag();
				if ($app->getLanguageFilter())
				{
					$languageArray = array($tagLanguage, '*', '');
					if (!in_array($documentObject->language, $languageArray))
					{
						self::$cache[$storeId] = false;

						return self::$cache[$storeId];
					}
				}
			}

			self::$cache[$storeId] = $documentObject;
		}

		return self::$cache[$storeId];
	}

	
	public static function documentHasPassword($doc)
	{
		if (!is_object($doc))
		{
			$doc = JUDownloadHelper::getDocumentById($doc);
		}

		if (is_object($doc))
		{
			
			$passwordField = JUDownloadFrontHelperField::getField('download_password', $doc);
			if (!is_object($passwordField) || !$passwordField->isPublished())
			{
				return false;
			}

			if ($doc->download_password != '')
			{
				return true;
			}
		}

		
		return false;
	}

	
	public static function isDocumentPublished($documentId)
	{
		$documentObject = JUDownloadHelper::getDocumentById($documentId);
		if (!is_object($documentObject))
		{
			return false;
		}
		$catPublished = JUDownloadFrontHelperPermission::canDoCategory($documentObject->cat_id);
		if (!$catPublished)
		{
			return false;
		}

		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();

		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__judownload_documents');
		
		$query->where('approved = 1');
		
		$query->where('published = 1');
		$query->where('(publish_up = ' . $db->quote($nullDate) . ' OR publish_up <= ' . $db->quote($nowDate) . ')');
		$query->where('(publish_down = ' . $db->quote($nullDate) . ' OR publish_down >= ' . $db->quote($nowDate) . ')');
		$query->where('id =' . $documentId);
		$db->setQuery($query);
		$result = $db->loadResult();

		if ($result)
		{
			return true;
		}

		return false;
	}

	
	public static function getDocumentLayoutFromCategory($categoryId)
	{
		$path = JUDownloadHelper::getCategoryPath($categoryId);

		$pathCatToRoot = array_reverse($path);

		foreach ($pathCatToRoot AS $category)
		{
			if ($category->layout_document == -2)
			{
				$params = JUDownloadHelper::getParams($categoryId);
				$layout = $params->get('layout_document', '_:default');

				return $layout;
			}
			elseif ($category->layout_document == -1)
			{
				
				if ($category->parent_id == 0)
				{
					$params = JUDownloadHelper::getParams($categoryId);
					$layout = $params->get('layout_document', '_:default');

					return $layout;
				}
				else
				{
					continue;
				}
			}
			else
			{
				$layout = trim($category->layout_document);

				return $layout;
			}
		}

		
		return '_:default';
	}

	
	public static function getDocumentLayout($docId)
	{
		$storeId = md5(__METHOD__ . "::" . $docId);
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('layout');
			$query->from('#__judownload_documents');
			$query->where('id =' . $docId);
			$db->setQuery($query);
			$layout = $db->loadResult();

			
			if ($layout == -2)
			{
				$params = JUDownloadHelper::getParams(null, $docId);
				$layout = $params->get('layout_document', '_:default');
			}
			
			elseif ($layout == -1)
			{
				$parentId = (int) JUDownloadFrontHelperCategory::getMainCategoryId($docId);
				
				if ($parentId == 0)
				{
					$params = JUDownloadHelper::getParams(null, $docId);
					$layout = $params->get('layout_document', '_:default');
				}
				
				else
				{
					$layout = JUDownloadFrontHelperDocument::getDocumentLayoutFromCategory($parentId);
				}
			}
			else
			{
				$layout = trim($layout);
			}

			self::$cache[$storeId] = $layout;
		}

		return self::$cache[$storeId];
	}

	
	public static function getDocumentViewLayout($layoutUrl, $documentId)
	{
		
		if ($layoutUrl)
		{
			$layout = $layoutUrl;
		}
		else
		{
			$app = JFactory::getApplication();
			
			$activeMenuItem = $app->getMenu()->getActive();
			if (($activeMenuItem) && ($activeMenuItem->component == 'com_judownload') && ((strpos($activeMenuItem->link, 'view=document') > 0) && (strpos($activeMenuItem->link, '&id=' . (string) $documentId) > 0)))
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

	
	public static function checkHotDocument($publish_up, $download_per_day_to_be_hot, $total_downloads)
	{
		$timeNow    = strtotime(JFactory::getDate()->toSql());
		$publish_up = strtotime($publish_up);
		$total_days = ceil(($timeNow - $publish_up) / 86400);

		$download_per_day = $total_downloads / $total_days;

		if ($download_per_day >= $download_per_day_to_be_hot)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	
	public static function getTotalDocumentsOfUserApprovedByMod($userId)
	{
		if (!$userId)
		{
			return 0;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("COUNT(*)");
		$query->from('#__judownload_documents');
		$query->where("created_by =" . $userId);
		$query->where("approved = 1");
		$query->where("approved_by != 0");
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	
	public static function getDocumentDisplayParams($doc_id)
	{
		if (!$doc_id)
		{
			return null;
		}

		$storeId = md5(__METHOD__ . "::$doc_id");
		if (!isset(self::$cache[$storeId]))
		{
			$params                         = JUDownloadHelper::getParams(null, $doc_id);
			$global_display_params          = $params->get('display_params');
			$global_document_display_object = isset($global_display_params->doc) ? $global_display_params->doc : array();
			$global_document_display_params = new JRegistry($global_document_display_object);

			$docObj          = JUDownloadHelper::getDocumentById($doc_id);
			$document_params = $docObj->params;
			if ($document_params)
			{
				$document_params         = json_decode($document_params);
				$document_display_params = $document_params->display_params;

				if ($document_display_params)
				{
					$global_document_display_params = JUDownloadFrontHelperField::mergeFieldOptions($global_document_display_params->toObject(), $document_display_params);
					unset($document_display_params->fields);

					foreach ($document_display_params AS $option => $value)
					{
						if ($value == '-2')
						{
							unset($document_display_params->$option);
						}
					}

					$global_document_display_params->loadObject($document_display_params);
				}
			}

			self::$cache[$storeId] = $global_document_display_params;
		}

		return self::$cache[$storeId];
	}

	
	public static function getTotalPublishedFilesOfDocument($documentId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__judownload_files');
		$query->where('published = 1');
		$query->where('doc_id =' . $documentId);
		$db->setQuery($query);

		return $db->loadResult();
	}

	
	public static function getFilesByDocumentId($docId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__judownload_files');
		$query->where('doc_id = ' . $docId);
		$query->where('published = 1');
		$db->setQuery($query);
		$files = $db->loadObjectList();

		foreach ($files AS $key => $file)
		{
			$file->size  = JUDownloadHelper::formatBytes($file->size);
			$files[$key] = $file;
		}

		return $files;
	}

	public static function getAddDocumentLink($categoryId = null, $xhtml = true)
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu('site');

		
		$params = JUDownloadHelper::getParams($categoryId);
		
		$itemId       = 0;
		$assignItemId = $params->get('assign_itemid_to_submit_link', 'currentItemid');
		switch (strtolower($assignItemId))
		{
			default:
			case "currentitemid":
				$itemId = $app->input->getInt('Itemid', 0);
				break;

			case "docsubmitmenuitemid":
				$component = JComponentHelper::getComponent('com_judownload');
				$menuItems = $menus->getItems('component_id', $component->id);
				
				foreach ($menuItems AS $menuItem)
				{
					if (isset($menuItem->query) && $menuItem->query['view'] == 'form')
					{
						$itemId = $menuItem->id;
						break;
					}
				}
				break;

			case "predefineditemid":
				$predefinedItemId = (int) $params->get('predefined_itemid_for_submit_link', 0);
				if (is_object($menus->getItem($predefinedItemId)))
				{
					$itemId = $predefinedItemId;
				}
				else
				{
					$itemId = $app->input->getInt('Itemid', 0);
				}
				break;
		}

		$submitDocLink = 'index.php?option=com_judownload&task=form.add';

		if ($categoryId)
		{
			$submitDocLink .= '&cat_id=' . $categoryId;
		}

		if ($itemId)
		{
			$submitDocLink .= '&Itemid=' . $itemId;
		}

		return JRoute::_($submitDocLink, $xhtml);
	}

	
	public static function getDownloadRuleErrorMessages($docId)
	{
		$error_messages = array();
		$user           = JFactory::getUser();

		$asset = 'com_judownload.document.' . $docId;
		if ($user->authorise('judl.document.download', $asset))
		{
			return true;
		}
		else
		{
			$error_messages[] = JText::_('COM_JUDOWNLOAD_YOU_ARE_NOT_AUTHORIZED_TO_DOWNLOAD');
		}

		$docObj = JUDownloadHelper::getDocumentById($docId);

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('judownload');
		$pluginTriggerResults = $dispatcher->trigger('canDownload', array($docObj));

		foreach ($pluginTriggerResults AS $result)
		{
			
			if ($result === true)
			{
				return true;
			}
			else
			{
				
				if (is_string($result))
				{
					$error_messages[] = $result;
				}
			}
		}

		return $error_messages;
	}

} 