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


class JUDownloadModelComments extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'cm.id',
				'cm.title',
				'd.title',
				'ua.name',
				'ua.email',
				'cm.total_votes',
				'cm.helpful_votes',
				'cm.created',
				'cm.ip_address',
				'cm.published',
				'total_reports',
				'total_subscriptions',
				'cm.lft'
			);
		}

		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		parent::populateState('cm.created', 'desc');
	}

	
	protected function getStoreId($id = '')
	{
		
		$id .= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

	
	protected function getListQuery()
	{
		
		$app   = JFactory::getApplication();
		$db    = $this->getDBO();
		$query = $db->getQuery(true);
		$query->SELECT('cm.*');
		$query->FROM('#__judownload_comments AS cm');

		$query->SELECT('ua1.name AS checked_out_name');
		$query->JOIN('LEFT', '#__users AS ua1 ON ua1.id = cm.checked_out');

		$query->SELECT('ua.name AS author_name, ua.email AS author_email');
		$query->JOIN('LEFT', '#__users AS ua ON ua.id = cm.user_id');

		$query->SELECT('d.title AS document_title');
		$query->JOIN('LEFT', '#__judownload_documents AS d ON d.id = cm.doc_id');

		$query->SELECT('(SELECT COUNT(*) FROM #__judownload_reports AS r WHERE (cm.id = r.item_id AND r.type = "comment")) AS total_reports');
		$query->SELECT('(SELECT COUNT(*) FROM #__judownload_subscriptions AS sub WHERE (cm.id = sub.item_id AND sub.type = "comment" AND sub.published = 1)) AS total_subscriptions');

		$search = $this->getState('filter.search');
		
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$search = substr($search, 3);
				if (is_numeric($search))
				{
					$query->where('cm.id = ' . (int) $search);
				}
			}
			elseif (stripos($search, 'document:') === 0)
			{
				$search = substr($search, 9);
				if (is_numeric($search))
				{
					$query->where('d.id = ' . (int) $search);
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
					$query->where('d.title LIKE ' . $search);
				}
			}
			elseif (stripos($search, 'author:') === 0)
			{
				if (strtolower(substr($search, 7)) == "guest")
				{
					$query->where('cm.user_id = 0 OR cm.user_id IS NULL');
				}
				else
				{
					$search = $db->Quote('%' . $db->escape(substr($search, 7), true) . '%');
					$query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ' OR cm.guest_name LIKE ' . $search . ')');
				}
			}
			else
			{
				$search = '%' . $db->escape($search, true) . '%';
				$where  = "cm.title LIKE '{$search}'";
				$query->WHERE($where);
			}

		}

		$query->WHERE("(cm.parent_id != 0 AND cm.level != 0)");
		$query->WHERE("cm.approved = 1");
		$ignore = $app->input->getInt("id", 0);
		if ($ignore)
		{
			$query->WHERE("cm.id != " . $ignore);
		}

		$doc_id = $app->input->getInt("doc_id", 0);
		if ($doc_id)
		{
			$query->WHERE("cm.doc_id = " . $doc_id);
		}
		elseif ($doc_id === '0')
		{
			$query->WHERE("cm.level = -1");
		}

		
		$orderCol  = $this->getState('list.ordering');
		$orderDirn = $this->getState('list.direction');

		if ($orderCol == "ua.name")
		{
			$orderCol = $orderCol . " " . $orderDirn . ", cm.guest_name";
		}

		if ($orderCol == "ua.email")
		{
			$orderCol = $orderCol . " " . $orderDirn . ", cm.guest_email";
		}

		if ($orderCol != '')
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}
}
