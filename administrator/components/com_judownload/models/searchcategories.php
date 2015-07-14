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


class JUDownloadModelSearchCategories extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'c.id',
				'c.title',
				'c.parent_id',
				'c.access',
				'c.created_by',
				'total_documents',
				'c.featured',
				'c.published',
				'c.language',
				'c.created'
			);
		}
		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{
		$accessId = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
		$this->setState('filter.access', $accessId);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '');
		$this->setState('filter.state', $state);

		$parent = $this->getUserStateFromRequest($this->context . '.filter.parent', 'filter_parent', '');
		$this->setState('filter.parent', $parent);

		$searchword = $this->getUserStateFromRequest($this->context . '.filter.searchword', 'searchword', '');
		$this->setState('filter.searchword', $searchword);

		parent::populateState('c.title', 'asc');
	}

	
	protected function getStoreId($id = '')
	{
		
		$id .= ':' . $this->getState('list.access');
		$id .= ':' . $this->getState('list.language');
		$id .= ':' . $this->getState('list.state');
		$id .= ':' . $this->getState('list.parent');
		$id .= ':' . $this->getState('list.searchword');

		return parent::getStoreId($id);
	}

	
	protected function getListQuery()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->SELECT('c.*');
		$query->SELECT('(SELECT COUNT(*) FROM #__judownload_documents AS d
						    JOIN #__judownload_documents_xref AS dxref 
							ON (dxref.doc_id = d.id AND dxref.main = 1)
							WHERE dxref.cat_id = c.id
						  ) AS total_documents');
		$query->FROM('#__judownload_categories AS c');
		$query->SELECT('ua.username');
		$query->JOIN('LEFT', '#__users AS ua ON ua.id = c.created_by');
		$query->SELECT('vl.title AS access');
		$query->JOIN('LEFT', '#__viewlevels AS vl ON c.access = vl.id');
		$query->SELECT('field_group.name AS fieldgroup_name, field_group.id AS fieldgroup_id');
		$query->JOIN('LEFT', '#__judownload_fields_groups AS field_group ON field_group.id = c.fieldgroup_id');

		$app           = JFactory::getApplication();
		$simple_search = $app->input->get('submit_simple_search');
		if (isset($simple_search))
		{
			$this->resetState();
		}

		
		$parent = $this->state->get('filter.parent');
		if ($parent)
		{
			$query->where('c.parent_id = ' . (int) $parent);
		}
		
		$state = $this->state->get('filter.state');
		if ($state != '')
		{
			$query->where('c.published = ' . (int) $state);
		}
		
		$access = $this->state->get('filter.access');
		if ($access)
		{
			$query->where('c.access = ' . (int) $access);
		}
		
		$language = $this->state->get('filter.language');
		if ($language)
		{
			$query->where('c.language = ' . $db->quote($language));
		}

		$searchword = trim($app->input->get('searchword', '', 'string'));
		if (!empty($searchword))
		{
			if (stripos($searchword, 'id:') === 0)
			{
				$query->where('c.id = ' . (int) substr($searchword, 3));
			}
			elseif (stripos($searchword, 'author:') === 0)
			{
				$searchword = $db->Quote('%' . $db->escape(substr($searchword, 7), true) . '%');
				$query->where('(ua.name LIKE ' . $searchword . ' OR ua.username LIKE ' . $searchword . ')');
			}
			else
			{
				$searchword = $db->Quote('%' . $db->escape($searchword, true) . '%');
				$query->where('(c.title LIKE ' . $searchword . ' OR c.alias LIKE ' . $searchword . ')');
			}
		}
		
		$orderCol  = $this->state->get('list.ordering', 'a.title');
		$orderDirn = $this->state->get('list.direction', 'asc');

		$query->ORDER($orderCol . ' ' . $orderDirn);

		return $query;
	}

	
	public function resetState()
	{
		$app = JFactory::getApplication();
		$app->input->set('filter_access', null);
		$app->input->set('filter_language', null);
		$app->input->set('filter_state', null);
		$app->input->set('filter_category', null);
		$app->input->set('limit', $app->getCfg('list_limit'));
		$app->input->set('limitstart', null);
		$app->input->set('filter_order', '');
		$app->input->set('filter_order_Dir', 'asc');
	}
}
