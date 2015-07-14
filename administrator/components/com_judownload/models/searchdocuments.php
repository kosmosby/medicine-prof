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


class JUDownloadModelSearchDocuments extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'title',
				'cat_id',
				'published',
				'featured',
				'access',
				'created_by',
				'downloads',
				'comments',
				'reports',
				'subscriptions',
				'language'
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

		$category = $this->getUserStateFromRequest($this->context . '.filter.category', 'filter_category', '');
		$this->setState('filter.category', $category);

		$searchword = $this->getUserStateFromRequest($this->context . '.filter.searchword', 'searchword', '');
		$this->setState('filter.searchword', $searchword);

		parent::populateState($ordering, $direction);
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

	
	protected function getStoreId($id = '')
	{
		
		$id .= ':' . $this->getState('list.access');
		$id .= ':' . $this->getState('list.language');
		$id .= ':' . $this->getState('list.category');
		$id .= ':' . $this->getState('list.searchword');

		return parent::getStoreId($id);
	}

	
	protected function getListQuery()
	{
		$app        = JFactory::getApplication();
		$searchword = trim($app->input->get('searchword', '', 'string'));

		return JUDownloadSearchHelper::getDocumentsSearch($searchword, $this->getState());
	}
}
