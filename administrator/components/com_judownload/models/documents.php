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


class JUDownloadModelDocuments extends JModelList
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
		$app = JFactory::getApplication();

		
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

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

		parent::populateState($ordering, $direction);
	}

	
	protected function getListQuery()
	{
		$listOrder = $this->state->get('list.ordering');
		$listDirn  = $this->state->get('list.direction');
		$search    = $this->state->get('filter.search');

		$db    = $this->getDBO();
		$query = $db->getQuery(true);
		$query->SELECT('d.*');
		$query->FROM('#__judownload_documents AS d');

		$query->JOIN('', '#__judownload_documents_xref AS dxref ON dxref.doc_id = d.id AND dxref.main =1');

		$query->SELECT('c.title AS category_title');
		$query->JOIN('', '#__judownload_categories AS c ON dxref.cat_id = c.id');

		$query->SELECT('l.title AS license_title');
		$query->JOIN('LEFT', '#__judownload_licenses AS l ON l.id = d.license_id');

		$query->SELECT('ua.name AS created_by');
		$query->JOIN('LEFT', '#__users AS ua ON ua.id = d.created_by');

		
		$query->SELECT('vl.title AS access_level');
		$query->JOIN('LEFT', '#__viewlevels AS vl ON vl.id = d.access');

		
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

		
		$query->where('d.approved = 1');

		
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('d.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = substr($search, 7);
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
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
				$query->ORDER($listOrder . " " . $listDirn . ', d.title');
			}
			else
			{
				$query->ORDER($listOrder . ' ' . $listDirn);
			}
		}

		return $query;
	}

}
