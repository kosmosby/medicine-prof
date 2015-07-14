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

class JUDownloadFrontHelperBreadcrumb
{
	
	protected static $cache = array();

	
	public static function createPathwayItem($title, $link = '')
	{
		$lang = JFactory::getLanguage();

		$prefix = 'COM_JUDOWNLOAD_';

		if ($lang->hasKey($prefix . $title))
		{
			$title = JText::_($prefix . $title);
		}
		else
		{
			$title = ucfirst(strtolower($title));
		}

		$pathwayItem       = new stdClass;
		$pathwayItem->name = html_entity_decode(JText::_($title), ENT_COMPAT, 'UTF-8');
		$pathwayItem->link = JRoute::_($link);

		return $pathwayItem;
	}

	
	public static function getBreadcrumbCategory($categoryId)
	{
		
		$app   = JFactory::getApplication();
		$menus = $app->getMenu('site');

		
		$categoryPath = JUDownloadHelper::getCategoryPath($categoryId);
		$pathwayArray = array();

		if (!empty($categoryPath))
		{
			if (!isset($categoryPath[1]))
			{
				
				$findMenuTreeLevel1 = false;
			}
			else
			{
				
				$topCategoryLevelId = $categoryPath[1]->id;
				$needles            = array(
					'tree' => array((int) $topCategoryLevelId)
				);
				$findMenuTreeLevel1 = JUDownloadHelperRoute::findItemId($needles, true);
			}

			if (!$findMenuTreeLevel1)
			{
				
				$pathwayArray[] = JUDownloadFrontHelperBreadcrumb::getRootPathway();
				
				array_shift($categoryPath);
			}
			else
			{
				
				$menuTreeLevel1 = $menus->getItem($findMenuTreeLevel1);
				
				$pathwayItem       = new stdClass;
				$pathwayItem->name = html_entity_decode($menuTreeLevel1->title, ENT_COMPAT, 'UTF-8');
				$pathwayItem->link = JRoute::_($menuTreeLevel1->link);
				$pathwayArray[]    = $pathwayItem;
				
				array_shift($categoryPath);
				array_shift($categoryPath);
			}

			if (!empty($categoryPath))
			{
				foreach ($categoryPath as $categoryPathItem)
				{
					$pathwayItem       = new stdClass;
					$pathwayItem->name = html_entity_decode($categoryPathItem->title, ENT_COMPAT, 'UTF-8');
					$pathwayItem->link = JUDownloadHelperRoute::getCategoryRoute($categoryPathItem->id, $topCategoryLevelId);
					$pathwayArray[]    = $pathwayItem;
				}
			}
		}

		return $pathwayArray;
	}

	
	public static function getRootPathway()
	{
		$app              = JFactory::getApplication();
		$menus            = $app->getMenu('site');
		$categoryRoot     = JUDownloadFrontHelperCategory::getRootCategory();
		$needles          = array(
			'tree' => array((int) $categoryRoot->id)
		);
		$findMenuTreeRoot = JUDownloadHelperRoute::findItemId($needles, true);

		$rootPathway = new stdClass;
		if ($findMenuTreeRoot)
		{
			$menuTreeRoot      = $menus->getItem($findMenuTreeRoot);
			$rootPathway->name = $menuTreeRoot->title;
			$rootPathway->link = JRoute::_($menuTreeRoot->link);
		}
		else
		{
			$rootPathway->name = html_entity_decode(JText::_('COM_JUDOWNLOAD_ROOT'), ENT_COMPAT, 'UTF-8');
			$rootPathway->link = JUDownloadHelperRoute::getTreeRoute($categoryRoot->id);
		}

		return $rootPathway;
	}

}