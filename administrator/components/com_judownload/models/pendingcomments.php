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


class JUDownloadModelPendingComments extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'cm.id',
				'cm.title',
				'd.title',
				'ua.username',
				'cm.guest_name',
				'cm.parent_id',
				'cm.created',
				'total_reports',
				'total_subscriptions'
			);
		}

		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$author = $this->getUserStateFromRequest($this->context . '.filter.author', 'filter_author');
		$this->setState('filter.author', $author);

		parent::populateState('cm.created', 'asc');
	}

	
	protected function getStoreId($id = '')
	{
		
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.author');

		return parent::getStoreId($id);
	}

	

	protected function getListQuery()
	{
		
		$db    = $this->getDBO();
		$query = $db->getQuery(true);
		$query->SELECT('cm.*');
		$query->FROM('#__judownload_comments AS cm');

		$query->SELECT('d.id AS document_id, d.title AS document_title');
		$query->JOIN('LEFT', '#__judownload_documents AS d ON cm.doc_id = d.id');

		$query->SELECT('ua.name AS author_name');
		$query->JOIN('LEFT', '#__users AS ua ON cm.user_id = ua.id');

		$query->SELECT('cm1.title AS parent');
		$query->JOIN('LEFT', '#__judownload_comments AS cm1 ON cm.parent_id = cm1.id');

		$query->SELECT('ua1.name AS checked_out_name');
		$query->JOIN('LEFT', '#__users AS ua1 ON ua1.id =  cm.checked_out');

		$query->SELECT('(SELECT COUNT(*) FROM #__judownload_reports AS r WHERE (cm.id = r.item_id AND r.type="comment")) AS total_reports');
		$query->SELECT('(SELECT COUNT(*) FROM #__judownload_subscriptions AS sub WHERE (cm.id = sub.item_id AND sub.type="comment" AND sub.published = 1)) AS total_subscriptions');

		$search = $this->getState('filter.search');

		$query->WHERE("(cm.parent_id != 0 AND cm.level != 0)");
		$query->WHERE("cm.approved != 1 ");

		
		if (!empty($search))
		{
			$search = '%' . $db->escape($search, true) . '%';

			$where = "(cm.title LIKE '{$search}')";
			$query->WHERE($where);
		}

		$author = $this->getState('filter.author');
		if ($author == "guest_name")
		{
			$query->WHERE("cm.user_id <= 0");
		}
		elseif ($author == "user_id")
		{
			$query->WHERE("cm.user_id > 0");
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
				$item->actionlink = 'index.php?option=com_judownload&amp;task=comment.edit&amp;id=' . $item->id;
			}
		}

		return $items;
	}
}
