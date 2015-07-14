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


class JUDownloadModelPlugins extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'plg.id',
				'plg.title',
				'plg.type',
				'plg.author',
				'plg.email',
				'plg.website',
				'plg.date',
				'plg.version',
				'plg.folder',
				'plg.core',
				'plg.default'
			);
		}

		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$filter_type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type');
		$this->setState('filter.type', $filter_type);

		parent::populateState('plg.title', 'asc');
	}

	
	protected function getStoreId($id = '')
	{
		
		$id .= ':' . $this->getState('list.search');
		$id .= ':' . $this->getState('list.type');

		return parent::getStoreId($id);
	}

	
	protected function getListQuery()
	{
		
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->SELECT('plg.*');
		$query->FROM('#__judownload_plugins AS plg');
		
		$query->select('ua.name AS checked_out_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = plg.checked_out');

		$search = $this->getState('filter.search');
		
		if (!empty($search))
		{
			$search = '%' . $db->escape($search, true) . '%';

			$field_searches = "plg.title LIKE '{$search}'";

			$query->WHERE($field_searches);
		}

		$type = $this->getState('filter.type');
		if ($type)
		{
			$query->WHERE("plg.type = '$type'");
		}

		
		$orderCol  = $this->getState('list.ordering');
		$orderDirn = $this->getState('list.direction');

		if ($orderCol != '')
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
				$item->actionlink = 'index.php?option=com_judownload&amp;task=plugin.edit&amp;id=' . $item->id;
			}
		}

		return $items;
	}

}
