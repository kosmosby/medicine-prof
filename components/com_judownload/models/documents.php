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

class JUDownloadModelDocuments extends JUDLModelList
{

	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'd.id',
				'd.title',
				'c.title',
				'l.title',
				'd.created_by',
				'd.created',
				'catid',
				'licenseid',
				'access',
				'published',
				'featured'
			);
		}

		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{
		$app    = JFactory::getApplication();
		$params = JUDownloadHelper::getParams();

		
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		
		if ($this->context)
		{
			$documentPagination = $params->get('document_pagination', 10);

			$limitArray = JUDownloadFrontHelper::customLimitBox();

			if (is_array($limitArray) && count($limitArray))
			{
				$limit = $app->input->getInt('limit', 0);
				if (in_array($limit, $limitArray))
				{
					$limit = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $documentPagination, 'uint');
				}
				else
				{
					$limit = $documentPagination;
				}
			}
			else
			{
				$limit = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $documentPagination, 'uint');
			}

			$this->setState('list.limit', $limit);

			$this->setState('list.start', $app->input->getUint('limitstart', 0));

			$orderCol = $app->getUserStateFromRequest($this->context . '.list.ordering', 'filter_order', '');
			$this->setState('list.ordering', $orderCol);

			$listOrder = $app->getUserStateFromRequest($this->context . '.list.direction', 'filter_order_Dir', 'ASC');
			$this->setState('list.direction', $listOrder);

			$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
			$this->setState('filter.search', $search);

			$category = $this->getUserStateFromRequest($this->context . '.filter.category', 'filter_catid');
			$this->setState('filter.catid', $category);

			$license = $this->getUserStateFromRequest($this->context . '.filter.license', 'filter_licenseid');
			$this->setState('filter.licenseid', $license);

			$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
			$this->setState('filter.access', $access);

			$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published');
			$this->setState('filter.published', $published);

			$featured = $this->getUserStateFromRequest($this->context . '.filter.featured', 'filter_featured');
			$this->setState('filter.featured', $featured);
		}
		else
		{
			$this->setState('list.start', 0);
			$this->state->set('list.limit', 0);
		}
	}


	
	protected function getListQuery()
	{
		$listOrder = $this->state->get('list.ordering');
		$listDirn  = $this->state->get('list.direction');
		$search    = $this->state->get('filter.search');

		$user      = JFactory::getUser();
		$levels    = $user->getAuthorisedViewLevels();
		$levelsStr = implode(',', $levels);

		$db       = $this->getDBO();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();

		$query = $db->getQuery(true);
		$query->select('d.*');
		$query->from('#__judownload_documents AS d');

		$query->join('', '#__judownload_documents_xref AS dxref ON dxref.doc_id = d.id AND dxref.main = 1');

		$query->select('c.title AS category_title');
		$query->join('', '#__judownload_categories AS c ON dxref.cat_id = c.id');

		
		$categoryIdArrayCanAccess = JUDownloadFrontHelperCategory::getAccessibleCategoryIds();
		if (is_array($categoryIdArrayCanAccess) && count($categoryIdArrayCanAccess) > 0)
		{
			$query->where('c.id IN(' . implode(",", $categoryIdArrayCanAccess) . ')');
		}
		else
		{
			$query->where('c.id IN("")');
		}

		$query->select('l.title AS license_title');
		$query->join('LEFT', '#__judownload_licenses AS l ON l.id = d.license_id');

		$query->select('ua.name AS created_by');
		$query->join('LEFT', '#__users AS ua ON ua.id = d.created_by');

		
		$query->select('vl.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS vl ON vl.id = d.access');

		
		$query->where('d.approved = 1');

		
		$query->where('d.published = 1');
		$query->where('(d.publish_up = ' . $db->quote($nullDate) . ' OR d.publish_up <= ' . $db->quote($nowDate) . ')');
		$query->where('(d.publish_down = ' . $db->quote($nullDate) . ' OR d.publish_down >= ' . $db->quote($nowDate) . ')');

		
		if ($user->get('guest'))
		{
			$query->where('d.access IN (' . $levelsStr . ')');
		}
		else
		{
			$query->where('(d.access IN (' . $levelsStr . ') OR (d.created_by = ' . $user->id . '))');
		}


		
		$catid = $this->getState('filter.catid');
		if ($catid)
		{
			$query->where('c.id = ' . $db->quote($catid));
		}

		
		$licenseid = $this->getState('filter.licenseid');
		if ($licenseid)
		{
			$query->where('l.id = ' . $db->quote($licenseid));
		}

		
		$access = $this->getState('filter.access');
		if ($access)
		{
			$query->where('d.access = ' . (int) $access);
		}

		
		$published = $this->getState('filter.published', '');
		if ($published !== '')
		{
			$query->where('d.published = ' . (int) $published);
		}

		
		$featured = $this->getState('filter.featured', '');
		if ($featured !== '')
		{
			$query->where('d.featured = ' . (int) $featured);
		}

		
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('d.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = substr($search, 7);
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('c.title LIKE ' . $search);
			}
			elseif (stripos($search, 'created_by:') === 0)
			{
				$search = substr($search, 11);
				if (is_numeric($search))
				{
					$query->where('d.created_by = 0 OR d.created_by IS NULL');
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
					$query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
				}
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('d.title LIKE ' . $search);
			}
		}

		$orderingAllow = array('d.id', 'd.title', 'd.author', 'c.title', 'l.title',
			'd.featured', 'd.published', 'd.created');
		if (in_array($listOrder, $orderingAllow))
		{
			if ($listOrder == 'c.title' || $listOrder == 'l.title')
			{
				$query->order($listOrder . " " . $listDirn . ', d.title');
			}
			else
			{
				$query->order($listOrder . ' ' . $listDirn);
			}
		}

		return $query;
	}

	
	public function getStart()
	{
		return $this->getState('list.start');
	}
}