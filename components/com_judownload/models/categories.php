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

class JUDownloadModelCategories extends JUDLModelList
{

	protected function populateState($ordering = null, $direction = null)
	{
		
		$app = JFactory::getApplication();

		$catId  = $app->input->getInt('id', 1);
		$params = JUDownloadHelper::getParams($catId);
		$this->setState('params', $params);
	}

	
	public function getCategoriesRecursive($categoryId, $limitedLevel = -1)
	{
		$params = $this->getState('params');

		$countTotalSubCats = $params->get('all_categories_show_total_subcategories', 1);

		$countTotalDocs = $params->get('all_categories_show_total_documents', 1);

		$categories = JUDownloadFrontHelperCategory::getCategoriesRecursive($categoryId, true, true, true, $countTotalSubCats, $countTotalDocs);

		$newCategories = array();

		if (is_array($categories) && count($categories) > 0)
		{
			
			if ($limitedLevel == -1)
			{
				$newCategories = $categories;
			}
			else
			{
				
				$firstElement = $categories[0];
				$minLevel     = $firstElement->level;
				foreach ($categories AS $category)
				{
					$category->virtual_level = $category->level - $minLevel;
					if ($category->virtual_level <= $limitedLevel)
					{
						$newCategories[] = $category;
					}
				}
			}

			if (is_array($newCategories) && count($newCategories) > 0)
			{
				$showEmptyCategory = (int) $params->get('all_categories_show_empty_category', 1);
				
				if (!$showEmptyCategory)
				{
					foreach ($newCategories AS $keyCategory => $valueCategory)
					{
						if ($countTotalSubCats && $countTotalDocs)
						{
							if ($valueCategory->total_nested_categories > 0 || $valueCategory->total_documents > 0)
							{
								$isEmptyCategory = false;
							}
							else
							{
								$isEmptyCategory = true;
							}
						}
						else
						{
							$isEmptyCategory = $this->isEmptyCategory($valueCategory->id);
						}

						if ($isEmptyCategory)
						{
							unset($newCategories[$keyCategory]);
						}
					}
				}
			}
		}

		return array_values($newCategories);
	}

	
	public function isEmptyCategory($categoryId)
	{
		
		$nestedCategories = JUDownloadFrontHelperCategory::getCategoriesRecursive($categoryId, true, true, true, true, true, $getIdOnly = true);

		if (!is_array($nestedCategories) || empty($nestedCategories))
		{
			return true;
		}

		$categoryObject = $nestedCategories[0];

		
		if ($categoryObject->total_nested_categories > 0)
		{
			return false;
		}

		
		if ($categoryObject->total_documents > 0)
		{
			return false;
		}

		return true;
	}

}