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


jimport('joomla.application.component.modellist');


class JUDownloadModelCriteriaGroups extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'name',
				'total_criterias',
				'published'
			);
		}

		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		parent::populateState('name', 'asc');
	}

	
	protected function getStoreId($id = '')
	{
		
		$id .= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

	
	protected function getListQuery()
	{
		
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->SELECT('cg.*');
		$query->SELECT('(SELECT COUNT(*) FROM #__judownload_criterias AS criteria WHERE criteria.group_id = cg.id) AS total_criterias');
		$query->FROM('#__judownload_criterias_groups AS cg');
		$query->SELECT('ua.name AS checked_out_name');
		$query->JOIN('LEFT', '#__users AS ua ON ua.id = cg.checked_out');

		$search = $this->getState('filter.search');

		$db = $this->getDbo();

		
		if (!empty($search))
		{
			$search = '%' . $db->escape($search, true) . '%';
			$where  = "(name LIKE '{$search}')";
			$query->WHERE($where);
		}

		
		$orderCol  = $this->getState('list.ordering');
		$orderDirn = $this->getState('list.direction');

		if ($orderCol == 'ordering')
		{
			$query->order($db->escape('cat_id, ' . $orderCol . ' ' . $orderDirn));
		}
		elseif ($orderCol != '')
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	public function getItems()
	{
		$items = parent::getItems();
		if ($items)
		{
			foreach ($items AS $item)
			{
				$item->actionlink         = 'index.php?option=com_judownload&amp;task=criteriagroup.edit&amp;id=' . $item->id;
				$item->assignedCategories = $this->getAssignedCategories($item->id);
			}
		}

		return $items;
	}

	
	protected function getAssignedCategories($criteriagroup_id)
	{
		$user  = JFactory::getUser();
		$db    = JFactory::getDbo();
		$query = "SELECT id, title FROM #__judownload_categories WHERE criteriagroup_id=$criteriagroup_id ORDER BY level, lft";
		$db->setQuery($query);
		$categories          = $db->loadObjectList();
		$category_name_arr   = array();
		$GroupCanDoCatManage = JUDownloadHelper::checkGroupPermission("category.edit");
		foreach ($categories AS $category)
		{
			if (
				($user->authorise('judl.category.edit', 'com_judownload.category.' . $category->id) ||
					($user->authorise('judl.category.edit.own', 'com_judownload.category.' . $category->id) && $category->created_by == $user->id))
				&& $GroupCanDoCatManage
			)
			{
				$link                = "index.php?option=com_judownload&task=category.edit&id=" . $category->id;
				$category_name_arr[] = "<a href=\"$link\" title=\"" . JText::_('COM_JUDOWNLOAD_EDIT_THIS_CATEGORY') . "\">" . $category->title . "</a>";
			}
			else
			{
				$category_name_arr[] = $category->title;
			}
		}

		return implode(', ', $category_name_arr);
	}

}
