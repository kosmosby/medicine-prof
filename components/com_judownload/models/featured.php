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

class JUDownloadModelFeatured extends JUDLModelList
{
	
	protected function populateState($ordering = null, $direction = null)
	{
		
		$app = JFactory::getApplication();

		$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();
		$categoryId   = $app->input->getInt('id', $rootCategory->id);

		$this->setState('category.id', $categoryId);

		$params = JUDownloadHelper::getParams($categoryId);
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

			$listOrder = $app->getUserStateFromRequest($this->context . '.list.direction', 'filter_order_Dir', 'ASC');
			$this->setState('list.direction', $listOrder);
		}
		else
		{
			$this->setState('list.start', 0);
			$this->state->set('list.limit', 0);
		}
	}

	
	protected function getListQuery()
	{
		$app                    = JFactory::getApplication();
		$rootCategory           = JUDownloadFrontHelperCategory::getRootCategory();
		$categoryId             = $this->getState('category.id', $rootCategory->id);
		$getAllNestedCategories = $app->input->getInt('all', 0);

		$catFilter = true;
		
		if ($categoryId == 1 && $getAllNestedCategories == 1)
		{
			$catFilter = false;
		}

		if ($catFilter)
		{
			$categoryIdArray = array();
			if ($getAllNestedCategories == 1)
			{
				$nestedCategories = JUDownloadFrontHelperCategory::getCategoriesRecursive($categoryId, true, true);
				if (count($nestedCategories) > 0)
				{
					foreach ($nestedCategories AS $categoryObj)
					{
						$categoryIdArray[] = $categoryObj->id;
					}
				}
			}
			array_unshift($categoryIdArray, $categoryId);
			$categoryString = implode(",", $categoryIdArray);
		}

		$ordering  = $this->getState('list.ordering', '');
		$direction = $this->getState('list.direction', 'ASC');

		$user      = JFactory::getUser();
		$levels    = $user->getAuthorisedViewLevels();
		$levelsStr = implode(',', $levels);

		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();

		
		$query = $db->getQuery(true);
		$query->select('d.*');
		$query->from('#__judownload_documents AS d');

		JUDownloadFrontHelper::optimizeListDocumentQuery($query);

		if ($catFilter)
		{
			
			$query->join('', '#__judownload_documents_xref AS dxref ON d.id = dxref.doc_id');
			$query->join('', '#__judownload_categories AS c ON c.id = dxref.cat_id');

			
			$query->where('c.id IN(' . $categoryString . ')');

			
			$query->group('d.id');
		}

		
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

		
		$query->where('d.featured = 1');


		
		$app = JFactory::getApplication();
		$tag = JFactory::getLanguage()->getTag();

		if ($app->getLanguageFilter())
		{
			$query->where('d.language IN (' . $db->quote($tag) . ',' . $db->quote('*') . ',' . $db->quote('') . ')');
		}

		
		$categoryRoot = JUDownloadFrontHelperCategory::getRootCategory();

		JUDownloadFrontHelperField::appendFieldOrderingPriority($query, $categoryRoot->id, $ordering, $direction);

		return $query;
	}

	
	public function getItems()
	{
		$params             = $this->getState('params');
		$documentObjectList = parent::getItems();

		JUDownloadFrontHelper::appendDataToDocumentObjList($documentObjectList, $params);

		return $documentObjectList;
	}

	
	public function getStart()
	{
		return $this->getState('list.start');
	}
}