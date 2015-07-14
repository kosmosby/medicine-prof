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

JLoader::register('JUDownloadFrontHelperCategory', JPATH_SITE . '/components/com_judownload/helpers/category.php');

class JUDownloadModuleHelper
{
	public $params = null;
	public $view = null;
	public $input;

	public function __construct($params = '')
	{
		$this->params = $params;
		$this->input  = JFactory::getApplication()->input;
	}

	
	public function isModuleShown()
	{
		
		if ($this->input->get('option', '') != 'com_judownload')
		{
			return true;
		}

		$assign_cats       = $this->params->get('assign_cats', 'all');
		$pages_assignment  = $this->params->get('pages_assignment', array());
		$allAssignedCatIds = $this->getAllAssignedCatIds();

		if (is_null($pages_assignment))
		{
			$pages_assignment = array();
		}

		
		if ($this->getCategoryIdInPage() !== false)
		{
			$cat_id = $this->getCategoryIdInPage();
			if (in_array('categories', $pages_assignment) && ($assign_cats == 'all' || in_array($cat_id, $allAssignedCatIds)))
			{
				return true;
			}

			return false;
		}
		
		elseif ($this->getDocIdInPage() !== false)
		{
			$doc_id = $this->getDocIdInPage();
			$cat_id = JUDownloadFrontHelperCategory::getMainCategoryId($doc_id);
			if (in_array('documents', $pages_assignment) && ($assign_cats == 'all' || in_array($cat_id, $allAssignedCatIds)))
			{
				return true;
			}

			return false;
		}
		
		else
		{
			if ($this->params->get('other_pages_assignment', 1))
			{
				return true;
			}

			return false;
		}
	}

	
	public function getView()
	{
		if (!is_null($this->view))
		{
			return $this->view;
		}
		else
		{
			$this->view = $this->input->getCmd('view', '');

			return $this->view;
		}
	}

	
	public function getCategoryIdInPage()
	{
		$view = $this->getView();

		if (in_array($view, array('category', 'categories')))
		{
			return $this->input->getInt('id', 0);
		}
		else
		{
			return false;
		}
	}

	
	public function getDocIdInPage()
	{
		$view = $this->getView();

		if (in_array($view, array('document')))
		{
			return $this->input->getInt('id', 0);
		}
		
		elseif (in_array($view, array('report', 'contact')))
		{
			return $this->input->getInt('doc_id', 0);
		}
		else
		{
			return false;
		}
	}

	
	protected function getAllAssignedCatIds()
	{
		$cats_assignment = $this->params->get('categories_assignment', array());
		$rootCatId       = JUDownloadFrontHelperCategory::getRootCategory()->id;
		$allAssignedCats = array();
		if (count($cats_assignment))
		{
			foreach ($cats_assignment AS $cat_id)
			{
				$recursiveCatIds = JUDownloadFrontHelperCategory::getCategoryIdsRecursive($cat_id);
				array_unshift($recursiveCatIds, $cat_id);
				$allAssignedCats = array_merge($allAssignedCats, $recursiveCatIds);
			}
			
			array_unshift($allAssignedCats, $rootCatId);
		}

		return $allAssignedCats;
	}

	
	public static function getCurrentCatId()
	{
		$app    = JFactory::getApplication();
		$option = $app->input->getString('option', '');
		$view   = $app->input->getString('view', '');
		$catId  = JUDownloadFrontHelperCategory::getRootCategory()->id;
		if ($option == 'com_judownload' && $view)
		{
			if ($view == 'document')
			{
				$docId = $app->input->getInt('id', 0);
				if ($docId > 0)
				{
					$catId = JUDownloadFrontHelperCategory::getMainCategoryId($docId);
				}
			}
			elseif ($view == 'category' || $view == 'categories')
			{
				$catId = $app->input->getInt('id', 0);
			}
		}

		return $catId;
	}

	
	public static function getItemId($needles = null)
	{
		require_once 'route.php';
		$itemId = JUDownloadHelperRoute::findItemId($needles);

		return $itemId = '&Itemid=' . $itemId;
	}

	
	public static function isEmptyCat($catId)
	{
		$categoryIdArrayCanAccess = JUDownloadFrontHelperCategory::getAccessibleCategoryIds();
		if (empty($categoryIdArrayCanAccess))
		{
			return true;
		}

		$user        = JFactory::getUser();
		$levelsArray = $user->getAuthorisedViewLevels();
		$levelString = implode(',', $levelsArray);

		$db       = JFactory::getDBO();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();

		
		$query = $db->getQuery(true);

		$query->select('COUNT(*)');
		$query->from('#__judownload_categories AS c');
		$query->where('c.parent_id = ' . $catId);

		
		$query->where('c.published = 1');
		$query->where('(c.publish_up = ' . $db->quote($nullDate) . ' OR c.publish_up <= ' . $db->quote($nowDate) . ')');
		$query->where('(c.publish_down = ' . $db->quote($nullDate) . ' OR c.publish_down >= ' . $db->quote($nowDate) . ')');

		
		$query->where('c.access IN (' . $levelString . ')');

		
		$query->where('c.id IN (' . implode(",", $categoryIdArrayCanAccess) . ')');


		
		$app         = JFactory::getApplication();
		$tagLanguage = JFactory::getLanguage()->getTag();
		if ($app->getLanguageFilter())
		{
			$query->where('c.language IN (' . $db->quote($tagLanguage) . ',' . $db->quote('*') . ')');
		}
		$db->setQuery($query);

		$totalSubCats = $db->loadResult();

		
		$query = $db->getQuery(true);

		$query->select('COUNT(*)');
		$query->from('#__judownload_documents AS d');

		
		$query->where('dx.cat_id = ' . $catId);

		
		$query->where('d.approved = 1');

		
		$query->where('d.published = 1');
		$query->where('(d.publish_up = ' . $db->quote($nullDate) . ' OR d.publish_up <= ' . $db->quote($nowDate) . ')');
		$query->where('(d.publish_down = ' . $db->quote($nullDate) . ' OR d.publish_down >= ' . $db->quote($nowDate) . ')');

		
		if ($user->get('guest'))
		{
			$query->where('d.access IN (' . $levelString . ')');
		}
		else
		{
			$query->where('(d.access IN (' . $levelString . ') OR (d.created_by = ' . $user->id . '))');
		}

		
		$query->join('INNER', '#__judownload_documents_xref AS dx ON d.id = dx.doc_id');
		$query->where('dx.cat_id IN (' . implode(",", $categoryIdArrayCanAccess) . ')');

		
		$app         = JFactory::getApplication();
		$tagLanguage = JFactory::getLanguage()->getTag();
		if ($app->getLanguageFilter())
		{
			$query->where('d.language IN (' . $db->quote($tagLanguage) . ',' . $db->quote('*') . ')');
		}
		$query->group('d.id');
		$db->setQuery($query);

		$totalDocuments = $db->loadResult();

		if (!$totalSubCats && !$totalDocuments)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

?>