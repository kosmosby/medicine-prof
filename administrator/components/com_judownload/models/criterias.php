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


class JUDownloadModelCriterias extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'title',
				'group_name',
				'weights',
				'required',
				'ordering',
				'published'
			);
		}

		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$category = $this->getUserStateFromRequest($this->context . '.filter.group_id', 'group_id');
		$this->setState('filter.group_id', $category);

		parent::populateState('ordering', 'asc');
	}

	
	protected function getStoreId($id = '')
	{
		
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.group_id');

		return parent::getStoreId($id);
	}

	
	protected function getListQuery()
	{
		
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->SELECT('g.name AS group_name, c.*');
		$query->FROM('#__judownload_criterias_groups AS g RIGHT JOIN #__judownload_criterias AS c ON g.id = c.group_id');
		$query->SELECT('ua.name AS checked_out_name');
		$query->JOIN('LEFT', '#__users AS ua ON ua.id = c.checked_out');

		$group_id = $this->getState('filter.group_id');
		if ($group_id > 0)
		{
			$query->WHERE('g.id = ' . intval($group_id));
		}

		
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$search = '%' . $db->escape($search, true) . '%';
			$query->WHERE("c.title LIKE '{$search}'");
		}

		
		$orderCol  = $this->getState('list.ordering');
		$orderDirn = $this->getState('list.direction');

		if ($orderCol == 'ordering' || $orderCol == 'group_name')
		{
			$query->order($db->escape('g.name ' . $orderDirn . ', c.ordering ' . $orderDirn));
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
				$item->actionlink = 'index.php?option=com_judownload&amp;task=criteria.edit&amp;id=' . $item->id;
			}
		}

		return $items;
	}
}
