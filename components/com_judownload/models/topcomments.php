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


class JUDownloadModelTopComments extends JUDLModelList
{
	public function populateState($ordering = null, $direction = null)
	{
		
		$app = JFactory::getApplication();

		$params = JUDownloadHelper::getParams();
		$this->setState('params', $params);

		
		if ($this->context)
		{
			$commentPagination = $params->get('comment_pagination', 10);

			$limitArray = JUDownloadFrontHelper::customLimitBox();

			if (is_array($limitArray) && count($limitArray))
			{
				$limit = $app->input->getUint('limit', null);
				if (is_null($limit) || in_array($limit, $limitArray))
				{
					$limit = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $commentPagination, 'uint');
				}
				else
				{
					$limit = $commentPagination;
				}
			}
			else
			{
				$limit = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $commentPagination, 'uint');
			}

			$this->setState('list.limit', $limit);

			$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));

			$commentOrdering = $params->get('comment_ordering', 'cm.created');
			$orderCol        = $app->getUserStateFromRequest($this->context . '.list.ordering', 'filter_order', $commentOrdering);
			$this->setState('list.ordering', $orderCol);

			$commentDirection = $params->get('comment_direction', 'DESC');
			$listOrder        = $app->getUserStateFromRequest($this->context . '.list.direction', 'filter_order_Dir', $commentDirection);
			$this->setState('list.direction', $listOrder);
		}
		else
		{
			$this->setState('list.start', 0);
			$this->state->set('list.limit', 0);
		}
	}

	public function getTotal()
	{
		
		$store = $this->getStoreId('getTotal');

		
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('cm.*');
		$query->from('#__judownload_comments AS cm');
		$query->where('cm.published  = 1');
		$query->where('cm.approved  = 1');
		$rootComment = JUDownloadFrontHelperComment::getRootComment();
		$query->where('cm.id != ' . $rootComment->id);
		$params = $this->getState('params');
		if ($params->get('top_comment_level', 'toplevel') == 'toplevel')
		{
			$query->where('cm.level = 1');
		}

		try
		{
			$total = (int) $this->_getListCount($query);
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$params             = $this->getState('params');
		$top_comments_limit = $params->get('top_comments_limit', 100);
		if ($top_comments_limit && $total > $top_comments_limit)
		{
			$total = $top_comments_limit;
		}

		
		$this->cache[$store] = $total;

		return $this->cache[$store];
	}

	protected function getListQuery()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('cm.*, r.score');
		$query->from('#__judownload_comments AS cm');
		$query->join('LEFT', '#__judownload_rating AS r ON cm.rating_id = r.id');
		$query->where('cm.published  = 1');
		$query->where('cm.approved  = 1');
		$rootComment = JUDownloadFrontHelperComment::getRootComment();
		$query->where('cm.id != ' . $rootComment->id);
		$params = $this->getState('params');
		if ($params->get('top_comment_level', 'toplevel') == 'toplevel')
		{
			$query->where('cm.level = 1');
		}

		
		$ordering  = $this->getState('list.ordering', 'cm.created');
		$direction = $this->getState('list.direction', 'desc');
		$query->order($ordering . ' ' . $direction);

		return $query;
	}

	public function getTopDocumentsLimit()
	{
		$params = $this->getState('params');

		$limit = $params->get('top_comments_limit', 100);

		$storeId = $this->getStoreId();
		$storeId = md5(__METHOD__ . "::$storeId::$limit");

		if (isset($this->cache[$storeId]))
		{
			return $this->cache[$storeId];
		}

		$query = $this->getListQuery();
		$db    = JFactory::getDbo();
		$db->setQuery($query, 0, $limit);
		$this->cache[$storeId] = $db->loadObjectList();

		return $this->cache[$storeId];
	}

	public function getItems()
	{
		$params = $this->getState('params');

		if ($params->get('top_comments_limit', 100))
		{
			$commentObjectList = array_slice($this->getTopDocumentsLimit(), $this->getStart(), $this->getState('list.limit'));
		}
		else
		{
			$commentObjectList = parent::getItems();
		}

		return $commentObjectList;
	}
}