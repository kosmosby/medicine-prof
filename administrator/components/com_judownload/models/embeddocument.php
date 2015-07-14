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

class JUDownloadModelEmbedDocument extends JModelList
{

	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'd.id',
				'd.title',
				'c.title',
				'd.access',
				'd.created',
				'catid',
				'licenseid',
				'access',
				'featured'
			);
		}

		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{
		
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '');
		$this->setState('filter.search', $search);

		$accessId = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
		$this->setState('filter.access', $accessId);

		$featured = $this->getUserStateFromRequest($this->context . '.filter.featured', 'filter_featured', '');
		$this->setState('filter.featured', $featured);

		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_catid', '');
		$this->setState('filter.catid', $categoryId);

		$license = $this->getUserStateFromRequest($this->context . '.filter.license', 'filter_licenseid', '');
		$this->setState('filter.licenseid', $license);

		
		$params = JUDownloadHelper::getParams();
		$this->setState('params', $params);

		
		parent::populateState('d.title', 'asc');

		$field_display = $this->getUserStateFromRequest($this->context . '.field_display', 'field_display', array());
		$this->setState('field_display', $field_display);

	}

	
	protected function getListQuery()
	{
		
		$db   = $this->getDbo();
		$date = JFactory::getDate();

		$now      = $date->toSql();
		$nullDate = $db->getNullDate();

		$query = $db->getQuery(true);

		
		$query->select('d.id, d.title, d.alias, d.created, d.access');
		$query->from('#__judownload_documents AS d');

		
		$query->join('', '#__judownload_documents_xref AS dxref ON dxref.doc_id = d.id AND dxref.main = 1');
		
		$query->select('c.title AS category_title');
		$query->join('', '#__judownload_categories AS c ON c.id = dxref.cat_id');

		
		$query->select('vl.title AS access_title');
		$query->join('LEFT', '#__viewlevels AS vl ON vl.id = d.access');

		
		$access = $this->getState('filter.access');
		if ($access)
		{
			$query->where('d.access = ' . (int) $access);
		}

		
		$categoryId = $this->getState('filter.catid');
		if (is_numeric($categoryId))
		{
			$query->where('c.id = ' . (int) $categoryId);
		}

		$query->where('c.published = 1');
		$query->where('c.publish_up <= ' . $db->quote($now));
		$query->where('(c.publish_down = ' . $db->quote($nullDate) . ' OR c.publish_down > ' . $db->quote($now) . ')');

		
		$featured = $this->getState('filter.featured', '');
		if ($featured !== '')
		{
			$query->where('d.featured = ' . (int) $featured);
		}

		
		$licenseid = $this->getState('filter.licenseid');
		if ($licenseid)
		{
			$query->SELECT('l.title AS license_title');
			$query->JOIN('LEFT', '#__judownload_licenses AS l ON l.id = d.license_id');
			$query->where('l.id = ' . $db->quote($licenseid));
		}

		
		$query->where('d.approved = 1');
		$query->where('d.published = 1');
		$query->where('d.publish_up <= ' . $db->quote($now));
		$query->where('(d.publish_down = ' . $db->quote($nullDate) . ' OR d.publish_down > ' . $db->quote($now) . ')');

		
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('d.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->getEscaped($search, true) . '%');
				$query->where('d.title LIKE ' . $search);
			}
		}

		
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		if ($orderCol)
		{
			$query->order($orderCol . ' ' . $orderDirn);
		}

		return $query;
	}
}