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

class JUDownloadFrontHelperCriteria
{
	
	protected static $cache = array();

	
	public static function getCriteriaGroupIdByCategoryId($mainCatId)
	{
		$catObj = JUDownloadHelper::getCategoryById($mainCatId);
		if ($catObj)
		{
			return $catObj->criteriagroup_id;
		}

		return null;
	}

	
	public static function getCriteriaGroupById($criteriaGroupId)
	{
		$storeId = md5(__METHOD__ . "::" . (int) $criteriaGroupId);
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__judownload_criterias_groups');
			$query->where('id = ' . $criteriaGroupId);
			$db->setQuery($query);
			self::$cache[$storeId] = $db->loadObject();
		}

		return self::$cache[$storeId];
	}

	
	public static function getCriteriasByCatId($mainCatId)
	{
		if (!$mainCatId)
		{
			return array();
		}

		$storeId = md5(__METHOD__ . "::$mainCatId");
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('cr.*');
			$query->from('#__judownload_criterias AS cr');
			$query->join('INNER', '#__judownload_criterias_groups AS crg ON cr.group_id = crg.id');
			$query->join('LEFT', '#__judownload_categories AS c ON crg.id = criteriagroup_id');
			$query->where('crg.published = 1');
			$query->where('cr.published = 1');
			$query->where('c.id = ' . $mainCatId);
			$query->order('cr.ordering ASC');
			$db->setQuery($query);
			$criterias = $db->loadObjectList();

			self::$cache[$storeId] = $criterias;
		}

		return self::$cache[$storeId];
	}
}