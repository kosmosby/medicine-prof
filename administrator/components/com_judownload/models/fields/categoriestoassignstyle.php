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


jimport('joomla.form.formfield');

class JFormFieldCategoriesToAssignStyle extends JFormField
{
	protected $cache = array();

	protected $type = 'CategoriesToAssignStyle';

	protected function getInput()
	{
		$db       = JFactory::getDbo();
		$app      = JFactory::getApplication();
		$style_id = $app->input->getInt('id', 0);
		$db->setQuery("SELECT id FROM #__judownload_categories WHERE style_id = " . $style_id);
		$catIds = $db->loadColumn();

		$rootCategory     = JUDownloadFrontHelperCategory::getRootCategory();
		$categoryTree     = JUDownloadHelper::getCategoryTree($rootCategory->id);
		$html             = "<div id=\"categoriestoassignstyle\" class=\"categoriestoassignstyle\">";
		$select_cat_class = 'select-cat';
		$html .= '<div class="btn-group pull-left">';
		$html .= '<button type="button" class="btn btn-mini" onclick="jQuery(\'.' . $select_cat_class . '\').each(function(el) { jQuery(this).prop(\'checked\', !jQuery(this).is(\':checked\')); });">' . JText::_('JGLOBAL_SELECTION_INVERT') . '</button>';
		$html .= '<button type="button" class="btn btn-mini" onclick="jQuery(\'.' . $select_cat_class . '\').each(function(el) { jQuery(this).prop(\'checked\', false); });">' . JText::_('JGLOBAL_SELECTION_NONE') . '</button>';
		$html .= '<button type="button" class="btn btn-mini" onclick="jQuery(\'.' . $select_cat_class . '\').each(function(el) {  jQuery(this).prop(\'checked\', true); });">' . JText::_('JGLOBAL_SELECTION_ALL') . '</button>';
		$html .= '</div>';

		$html .= "<ul class=\"nav\" id=\"" . $this->id . "\">";

		foreach ($categoryTree AS $category)
		{
			$html .= "<li>";
			$cat_style_id = "";
			if ($category->id == 1)
			{
				$cat_style_id = $category->style_id;
				$inherit      = "";
			}
			elseif ($category->style_id == $style_id)
			{
				$html .= "<input class =\"" . $select_cat_class . "\" id=\"catid-" . $category->id . "\" type='checkbox' name='" . $this->name . "[]' value = '" . $category->id . "' checked>";
				$cat_style_id = $style_id;
				$inherit      = "";
			}
			elseif ($category->style_id == -1)
			{
				$html .= "<input class =\"" . $select_cat_class . "\" id=\"catid-" . $category->id . "\" type='checkbox' name='" . $this->name . "[]' value = '" . $category->id . "'";
				$db->setQuery("SELECT id FROM #__judownload_categories WHERE lft <" . $category->lft . " AND rgt > " . $category->rgt);
				$parentCatIds = $db->loadColumn();
				$parentCatIds = array_reverse($parentCatIds);
				if (is_array($catIds) && !empty($catIds))
				{
					foreach ($parentCatIds AS $parentCatId)
					{
						if (in_array($parentCatId, $catIds))
						{
							$html .= " checked";
						}
					}
				}

				$html .= ">";
				foreach ($parentCatIds AS $parentCatId)
				{
					$styleId = $this->getStyleIdByCategoryId($parentCatId);
					if ($styleId > -1 || $styleId == -2)
					{
						$cat_style_id = $styleId;
						break;
					}
				}

				$inherit = "<span class=\"inherited-value\" title=\"" . JText::_("COM_JUDOWNLOAD_INHERIT") . "\">" . JText::_("COM_JUDOWNLOAD_INHERIT") . "</span>";
			}
			elseif ($category->style_id == -2)
			{
				$html .= "<input type='checkbox' name='" . $this->name . "[]' value = '" . $category->id . "'>";
				$inherit = "";
				$db->setQuery("SELECT id FROM #__judownload_template_styles WHERE home = 1");
				$cat_style_id = $db->loadResult();
			}
			else
			{
				$html .= "<input class =\"" . $select_cat_class . "\" id=\"catid-" . $category->id . "\" type='checkbox' name='" . $this->name . "[]' value = '" . $category->id . "' />";
				$inherit      = "";
				$cat_style_id = $category->style_id;
			}

			if ($cat_style_id == -2)
			{
				$style_title = $this->getStyleTitle(0, true);
			}
			else
			{
				$style_title = $this->getStyleTitle($cat_style_id);
			}

			$style_title = $style_title ? " [" . $style_title . "] " : "";

			$html .= "<label for=\"catid-" . $category->id . "\">" . str_repeat('<span class="gi">|—</span>', $category->level);
			if ($category->id == 1)
			{
				$html .= $category->title;
			}
			else
			{
				$html .= "<a href=\"" . JRoute::_('index.php?option=com_judownload&task=category.edit&id=' . $category->id) . "\">" . $category->title . "</a>";
			}
			$html .= " <span class=\"style-title\">" . $style_title . "</span>" . $inherit . "</label>";
			$html .= "</li>";
		}
		$html .= "</ul>";
		$html .= "</div>";

		return $html;
	}

	protected function getStyleIdByCategoryId($cat_id)
	{
		$storeId = md5(__METHOD__ . "::" . $cat_id);
		if (!isset($this->cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = "SELECT style_id FROM #__judownload_categories WHERE id = " . $cat_id;
			$db->setQuery($query);

			$this->cache[$storeId] = $db->loadResult();
		}

		return $this->cache[$storeId];
	}

	protected function getStyleTitle($styleId = null, $home = false)
	{
		$storeId = md5(__METHOD__ . "::" . (int) $styleId . "::" . (int) $home);
		if (!isset($this->cache[$storeId]))
		{
			$db = JFactory::getDbo();
			if ($home)
			{
				$query = "SELECT title FROM #__judownload_template_styles WHERE home = 1";
			}
			else
			{
				$query = "SELECT title FROM #__judownload_template_styles WHERE id = " . (int) $styleId;
			}
			$db->setQuery($query);

			$this->cache[$storeId] = $db->loadResult();
		}

		return $this->cache[$storeId];
	}
}