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


class JUDownloadModelFields extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'field.id',
				'field.caption',
				'field.group_id',
				'field.plugin_id',
				'field.published',
				'field.ordering',
				'field.allow_priority',
				'field.priority',
				'field.priority_direction',
				'field.hide_caption',
				'field.required',
				'field.list_view',
				'field.details_view',
				'field.simple_search',
				'field.advanced_search',
				'field.filter_search',
				'field.backend_list_view',
				'field.backend_list_view_ordering',
				'field.frontend_ordering',
				'plg.title',
				'group_id',
				'plugin_id'
			);
		}

		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$group = $this->getUserStateFromRequest($this->context . '.filter.group_id', 'filter_group');
		$this->setState('filter.group_id', $group);
		$group = $this->getUserStateFromRequest($this->context . '.filter.plugin_id', 'filter_plugin');
		$this->setState('filter.plugin_id', $group);

		parent::populateState('field.ordering', 'asc');

	}

	
	protected function getStoreId($id = '')
	{
		
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.group_id');

		return parent::getStoreId($id);
	}

	
	protected function getListQuery()
	{
		
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->SELECT('field.*, plg.title AS plg_title');
		$query->FROM('#__judownload_fields AS field');
		$query->JOIN('', '#__judownload_plugins AS plg ON (plg.id = field.plugin_id)');
		$query->SELECT('field_group.id AS field_group_id, field_group.name AS field_group_name');
		$query->JOIN('', '#__judownload_fields_groups AS field_group ON field.group_id = field_group.id');
		$query->SELECT('ua.name AS checked_out_name');
		$query->JOIN('LEFT', '#__users AS ua ON (ua.id = field.checked_out)');

		$search = $this->getState('filter.search');
		
		if (!empty($search))
		{
			$search = '%' . $db->escape($search, true) . '%';
			$where  = "(field.caption LIKE '{$search}')";
			$query->WHERE($where);
		}

		$group_id = $this->getState('filter.group_id');
		if (isset($group_id) && $group_id !== "")
		{
			$where = "(field.group_id = " . (int) $group_id . ")";
			$query->WHERE($where);
		}

		$plugin_id = $this->getState('filter.plugin_id');
		if (isset($plugin_id) && $plugin_id !== "")
		{
			$where = "(field.plugin_id = " . (int) $plugin_id . ")";
			$query->WHERE($where);
		}

		
		$orderCol  = $this->getState('list.ordering');
		$orderDirn = $this->getState('list.direction');
		if ($orderCol == 'field.ordering')
		{
			$query->order($db->escape('field.group_id, ' . $orderCol . ' ' . $orderDirn));
		}
		elseif ($orderCol == 'field.group_id')
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn . ", field.caption ASC "));
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
				$item->actionlink = 'index.php?option=com_judownload&amp;task=field.edit&amp;id=' . $item->id;
			}
		}

		return $items;
	}

}
