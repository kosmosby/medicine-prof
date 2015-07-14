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


class JUDownloadModelTags extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'tag.id',
				'tag.title',
				'total_documents',
				'tag.ordering',
				'tag.published'
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

		parent::populateState('tag.ordering', 'asc');
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
		$query->SELECT('tag.*, tag_xref.total_documents');
		$query->FROM('#__judownload_tags AS tag');
		$query->join('LEFT', '(SELECT tag_id, COUNT(*) AS total_documents FROM #__judownload_tags_xref GROUP BY tag_id) AS tag_xref ON tag_xref.tag_id = tag.id');
		$query->SELECT('ua.name AS checked_out_name');
		$query->JOIN('LEFT', '#__users AS ua ON ua.id = tag.checked_out');

		
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('tag.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(tag.published = 0 OR tag.published = 1)');
		}

		
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$search         = '%' . $db->escape($search, true) . '%';
			$field_searches = "tag.title LIKE '{$search}'";
			$query->WHERE($field_searches);
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
				$item->actionlink = 'index.php?option=com_judownload&amp;task=tag.edit&amp;id=' . $item->id;
			}
		}

		return $items;
	}
}
