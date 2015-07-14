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

class JUDownloadModelSearch extends JUDLModelList
{

	protected function populateState($ordering = null, $direction = null)
	{
		
		$app = JFactory::getApplication();

		$params = JUDownloadHelper::getParams();
		$this->setState('params', $params);

		
		if ($this->context)
		{
			$documentPagination = $params->get('document_pagination', 10);

			$limitArray = JUDownloadFrontHelper::customLimitBox();

			if (is_array($limitArray) && count($limitArray))
			{
				$limit = $app->input->getUint('limit', null);
				if (is_null($limit) || in_array($limit, $limitArray))
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

			$listOrder = $app->getUserStateFromRequest($this->context . '.list.direction', 'filter_order_Dir', '');
			$this->setState('list.direction', $listOrder);
		}
		else
		{
			$this->setState('list.start', 0);
			$this->state->set('list.limit', 0);
		}
	}

	
	public function resetState()
	{
		$app = JFactory::getApplication();
		$app->input->set('limit', $app->getCfg('list_limit'));
		$app->input->set('limitstart', null);
		$app->input->set('filter_order', '');
		$app->input->set('filter_order_Dir', 'asc');
	}

	
	protected function getListQuery()
	{
		$app           = JFactory::getApplication();
		$searchword    = $app->input->getString('searchword', '');
		$cat_id        = $app->input->getInt('cat_id', 0);
		$sub_cat       = $app->input->getInt('sub_cat', 0);
		$search_cat_id = null;

		if ($sub_cat)
		{
			$search_cat_id = $this->getCatIdTree($cat_id);
		}
		else
		{
			$search_cat_id = $cat_id;
		}

		return JUDownloadSearchHelper::getDocumentsSearch($searchword, $this->getState(), $search_cat_id);
	}

	
	public function getItems()
	{
		$app              = JFactory::getApplication();
		$searchword       = $app->input->getString('searchword', '');
		$params           = $this->getState('params');
		$minSearchWord    = $params->get('searchword_min_length', 3);
		$maxSearchWord    = $params->get('searchword_max_length', 30);
		$searchwordLength = strlen($searchword);
		if (!$searchwordLength || ($minSearchWord > 0 && $searchwordLength < $minSearchWord) || ($maxSearchWord > 0 && $searchwordLength > $maxSearchWord))
		{
			if ($searchwordLength > 0)
			{
				$app->enqueueMessage(JText::sprintf("COM_JUDOWNLOAD_SEARCH_TERM_MUST_BE_A_MINIMUM_OF_X_CHARACTERS_AND_MAXIMUM_OF_X_CHARACTER", $minSearchWord, $maxSearchWord));
			}

			return array();
		}
		else
		{
			$documentObjectList = parent::getItems();
			JUDownloadFrontHelper::appendDataToDocumentObjList($documentObjectList, $params);

			return $documentObjectList;
		}
	}

	
	public function getCatIdTree($catId, $includeItSelf = true)
	{
		$catId = (int) $catId;
		if (!$catId)
		{
			return null;
		}

		if (!JUDownloadFrontHelperPermission::canDoCategory($catId))
		{
			return null;
		}

		$cat_id_arr = array();

		if ($includeItSelf)
		{
			$cat_id_arr[] = $catId;
		}

		$db    = JFactory::getDbo();
		$query = "SELECT id FROM #__judownload_categories WHERE parent_id = " . $catId;
		$db->setQuery($query);
		$child_cat_ids = $db->loadColumn();

		foreach ($child_cat_ids AS $child_cat_id)
		{
			$cat_id_arr[]     = $child_cat_id;
			$child_cat_id_arr = $this->getCatIdTree($child_cat_id, false);

			if ($child_cat_id_arr)
			{
				$cat_id_arr = array_merge($cat_id_arr, $child_cat_id_arr);
			}
		}

		return array_unique($cat_id_arr);
	}
}
