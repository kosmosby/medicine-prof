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


class JUDownloadModelLicenses extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'l.id',
				'l.title',
				'l.ordering',
				'l.published',
				'total_documents'
			);
		}

		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		parent::populateState('l.ordering', 'asc');
	}

	
	protected function getStoreId($id = '')
	{
		
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');

		return parent::getStoreId($id);
	}

	
	protected function getListQuery()
	{
		
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->SELECT('l.*');
		$query->FROM('#__judownload_licenses AS l');
		$query->SELECT('ua.name AS checked_out_name');
		$query->JOIN('LEFT', '#__users AS ua ON ua.id = l.checked_out');

		$search = $this->getState('filter.search');

		$db = $this->getDbo();

		
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('l.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(l.published = 0 OR l.published = 1)');
		}

		
		if (!empty($search))
		{
			$search = '%' . $db->escape($search, true) . '%';

			$where = "(l.title LIKE '{$search}')";
			$query->WHERE($where);
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
			$db = JFactory::getDbo();
			foreach ($items AS $item)
			{
				$item->actionlink = 'index.php?option=com_judownload&amp;task=license.edit&amp;id=' . $item->id;
				$query            = $db->getQuery(true);
				$query->select('COUNT(*)')
					->from('#__judownload_documents AS d')
					->join('', '#__judownload_licenses AS l ON d.license_id = l.id')
					->where('l.id = ' . $item->id);
				$db->setQuery($query);
				$item->total_documents = $db->loadResult();
			}
		}

		return $items;
	}
}
