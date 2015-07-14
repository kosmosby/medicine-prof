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

class JUDownloadModelTree extends JUDLModelList
{
	
	protected function populateState($ordering = null, $direction = null)
	{
		
		$app = JFactory::getApplication();

		
		$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();
		$catId        = $app->input->getInt('id', $rootCategory->id);
		$this->setState('category.id', $catId);

		
		$params = JUDownloadHelper::getParams($catId);
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

			$orderCol = $app->getUserStateFromRequest($this->context . '.list.ordering', 'filter_order', '', 'string');
			$this->setState('list.ordering', $orderCol);

			$listOrder = $app->getUserStateFromRequest($this->context . '.list.direction', 'filter_order_Dir', '', 'cmd');
			$this->setState('list.direction', $listOrder);
		}
		else
		{
			$this->setState('list.start', 0);
			$this->state->set('list.limit', 0);
		}
	}

	
	public function getStart()
	{
		return $this->getState('list.start');
	}

	
	public function getRelatedCategories($categoryId, $ordering = 'crel.ordering', $direction = 'ASC')
	{
		$storeId = md5(__METHOD__ . "::" . $categoryId . "::" . $ordering . "::" . $direction);

		if (!isset($this->cache[$storeId]))
		{
			$params            = $this->getState('params');
			$showEmptyCategory = $params->get('show_empty_related_category', 1);

			$user      = JFactory::getUser();
			$levels    = $user->getAuthorisedViewLevels();
			$levelsStr = implode(',', $levels);

			$db       = JFactory::getDbo();
			$nullDate = $db->getNullDate();
			$nowDate  = JFactory::getDate()->toSql();

			
			$query = $db->getQuery(true);
			$query->select('c.*');
			$query->from('#__judownload_categories AS c');

			
			$query->join('INNER', '#__judownload_categories_relations AS crel ON c.id=crel.cat_id_related');
			$query->where('crel.cat_id =' . $categoryId);

			
			$query->where('c.published = 1');
			$query->where('(c.publish_up = ' . $db->quote($nullDate) . ' OR c.publish_up <= ' . $db->quote($nowDate) . ')');
			$query->where('(c.publish_down = ' . $db->quote($nullDate) . ' OR c.publish_down >= ' . $db->quote($nowDate) . ')');

			
			$query->where('c.access IN (' . $levelsStr . ')');

			
			$categoryIdArrayCanAccess = JUDownloadFrontHelperCategory::getAccessibleCategoryIds();
			if (is_array($categoryIdArrayCanAccess) && count($categoryIdArrayCanAccess) > 0)
			{
				$query->where('c.id IN(' . implode(",", $categoryIdArrayCanAccess) . ')');
			}
			else
			{
				$query->where('c.id IN("")');
			}

			
			$app = JFactory::getApplication();
			$tag = JFactory::getLanguage()->getTag();
			if ($app->getLanguageFilter())
			{
				$query->where('c.language IN (' . $db->quote($tag) . ',' . $db->quote('*') . ',' . $db->quote('') . ')');
			}

			
			$query->order($ordering . ' ' . $direction);

			
			$query->group('c.id');

			$db->setQuery($query);
			$categoriesBefore = $db->loadObjectList();

			$categoriesAfter = array();
			foreach ($categoriesBefore AS $category)
			{
				
				$showTotalSubCats   = $params->get('show_total_subcats_of_relcat', 0);
				$showTotalChildDocs = $params->get('show_total_docs_of_relcat', 0);

				$nestedCategories = null;

				if ($showTotalChildDocs || $showTotalSubCats)
				{
					$nestedCategories = JUDownloadFrontHelperCategory::getCategoriesRecursive($category->id, true, true, true, false, false, true);

					if ($showTotalChildDocs)
					{
						
						$category->total_documents = JUDownloadFrontHelperCategory::getTotalDocumentsInCategory($category->id, $nestedCategories);
					}

					if ($showTotalSubCats)
					{
						
						$category->total_nested_categories = JUDownloadFrontHelperCategory::getTotalSubCategoriesInCategory($category->id, $nestedCategories);
					}
				}

				
				$registry = new JRegistry;
				$registry->loadString($category->images);
				$category->images = $registry->toObject();

				
				$category->link = JRoute::_(JUDownloadHelperRoute::getCategoryRoute($category->id));

				
				if (!$showEmptyCategory)
				{
					
					if (is_null($nestedCategories))
					{
						$nestedCategories = JUDownloadFrontHelperCategory::getCategoriesRecursive($category->id, true, true, true, false, false, true);
					}

					if (!isset($category->total_nested_categories))
					{
						$category->total_nested_categories = JUDownloadFrontHelperCategory::getTotalSubCategoriesInCategory($category->id, $nestedCategories);
					}
					if (!isset($category->total_documents))
					{
						$category->total_documents = JUDownloadFrontHelperCategory::getTotalDocumentsInCategory($category->id, $nestedCategories);
					}
					if (($category->total_nested_categories > 0) || ($category->total_documents > 0))
					{
						$categoriesAfter[] = $category;
					}
				}
				else
				{
					$categoriesAfter[] = $category;
				}
			}

			
			$this->cache[$storeId] = $categoriesAfter;
		}

		return $this->cache[$storeId];
	}

	
	public function getSubCategories($parentId, $ordering = 'title', $direction = 'ASC')
	{
		$params            = $this->getState('params');
		$showEmptyCategory = $params->get('show_empty_subcategory', 1);

		$user      = JFactory::getUser();
		$levels    = $user->getAuthorisedViewLevels();
		$levelsStr = implode(',', $levels);

		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();

		
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__judownload_categories');
		$query->where('parent_id=' . $parentId);

		
		$query->where('published = 1');
		$query->where('(publish_up = ' . $db->quote($nullDate) . ' OR publish_up <= ' . $db->quote($nowDate) . ')');
		$query->where('(publish_down = ' . $db->quote($nullDate) . ' OR publish_down >= ' . $db->quote($nowDate) . ')');

		
		$query->where('access IN (' . $levelsStr . ')');

		
		$app = JFactory::getApplication();
		$tag = JFactory::getLanguage()->getTag();
		if ($app->getLanguageFilter())
		{
			$query->where('language IN (' . $db->quote($tag) . ',' . $db->quote('*') . ',' . $db->quote('') . ')');
		}

		
		$query->order($ordering . ' ' . $direction);

		$db->setQuery($query);
		$subCategoriesBefore = $db->loadObjectList();

		$subCategoriesAfter = array();
		foreach ($subCategoriesBefore AS $category)
		{
			
			$showTotalSubCats   = $params->get('show_total_subcats_of_subcat', 0);
			$showTotalChildDocs = $params->get('show_total_docs_of_subcat', 0);

			$nestedCategories = null;

			if ($showTotalChildDocs || $showTotalSubCats)
			{
				$nestedCategories = JUDownloadFrontHelperCategory::getCategoriesRecursive($category->id, true, true, true, false, false, true);

				if ($showTotalChildDocs)
				{
					
					$category->total_documents = JUDownloadFrontHelperCategory::getTotalDocumentsInCategory($category->id, $nestedCategories);
				}

				if ($showTotalSubCats)
				{
					
					$category->total_nested_categories = JUDownloadFrontHelperCategory::getTotalSubCategoriesInCategory($category->id, $nestedCategories);
				}
			}

			
			$registry = new JRegistry;
			$registry->loadString($category->images);
			$category->images = $registry->toObject();

			
			$category->link = JRoute::_(JUDownloadHelperRoute::getCategoryRoute($category->id));

			if (!$showEmptyCategory)
			{
				
				if (is_null($nestedCategories))
				{
					$nestedCategories = JUDownloadFrontHelperCategory::getCategoriesRecursive($category->id, true, true, true, false, false, true);
				}

				if (!isset($category->total_nested_categories))
				{
					$category->total_nested_categories = JUDownloadFrontHelperCategory::getTotalSubCategoriesInCategory($category->id, $nestedCategories);
				}
				if (!isset($category->total_documents))
				{
					$category->total_documents = JUDownloadFrontHelperCategory::getTotalDocumentsInCategory($category->id, $nestedCategories);
				}

				if (($category->total_nested_categories > 0) || ($category->total_documents > 0))
				{
					$subCategoriesAfter[] = $category;
				}
			}
			else
			{
				$subCategoriesAfter[] = $category;
			}
		}

		return $subCategoriesAfter;
	}

	
	protected function getListQuery()
	{
		$catId     = $this->getState('category.id');
		$ordering  = $this->getState('list.ordering');
		$direction = $this->getState('list.direction');

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

		
		$query->join("", "#__judownload_documents_xref AS dxref ON d.id = dxref.doc_id");
		$query->join("", "#__judownload_categories AS c ON c.id = dxref.cat_id");

		
		$query->where('c.id = ' . $catId);

		
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

		
		$app         = JFactory::getApplication();
		$languageTag = JFactory::getLanguage()->getTag();
		if ($app->getLanguageFilter())
		{
			$query->where('d.language IN (' . $db->quote($languageTag) . ',' . $db->quote('*') . ',' . $db->quote('') . ')');
		}

		
		JUDownloadFrontHelperField::appendFieldOrderingPriority($query, $catId, $ordering, $direction);

		return $query;
	}

	
	public function getItems()
	{
		$params             = $this->getState('params');
		$documentObjectList = parent::getItems();

		JUDownloadFrontHelper::appendDataToDocumentObjList($documentObjectList, $params);

		return $documentObjectList;
	}

	
	public static function getDataFeed($categoryId, $ordering = 'd.title', $direction = 'ASC', $start = 0, $limit = 0)
	{
		$user         = JFactory::getUser();
		$levels       = $user->getAuthorisedViewLevels();
		$levelsString = implode(',', $levels);

		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();

		$query = $db->getQuery(true);
		$query->select('d.*');
		$query->from('#__judownload_documents AS d');
		$query->select('dx.cat_id');
		$query->join('', '#__judownload_documents_xref AS dx ON d.id = dx.doc_id');
		$query->select('v.title AS access_title');
		$query->join('LEFT', '#__viewlevels AS v ON d.access = v.id');
		$query->select('u.name AS creator');
		$query->join('LEFT', '#__users AS u ON d.created_by = u.id');
		$query->select('m.name AS modifier');
		$query->join('LEFT', '#__users AS m ON d.modified_by = m.id');
		$query->where('dx.cat_id = ' . $categoryId);
		$query->where('d.published = 1');
		$query->where('d.approved = 1');
		$query->where('(d.publish_up = ' . $db->quote($nullDate) . ' OR d.publish_up <= ' . $db->quote($nowDate) . ')');
		$query->where('(d.publish_down = ' . $db->quote($nullDate) . ' OR d.publish_down >= ' . $db->quote($nowDate) . ')');
		$query->where('d.access IN (' . $levelsString . ')');
		$query->order($ordering . ' ' . $direction);
		if ($limit > 0)
		{
			$db->setQuery($query, $start, $limit);
		}
		else
		{
			$db->setQuery($query);
		}

		$documentObjectList = $db->loadObjectList();

		return $documentObjectList;
	}
}