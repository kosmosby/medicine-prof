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

class JFormFieldCategoriesToAssignCriteria extends JFormField
{
	protected $cache = array();

	
	protected $type = 'CategoriesToAssignCriteria';

	
	protected function getInput()
	{
		$criteriagroup_id = $this->form->getValue('id');
		$rootCategory     = JUDownloadFrontHelperCategory::getRootCategory();
		$categoryTree     = JUDownloadHelper::getCategoryTree($rootCategory->id);
		$html             = "<div id=\"categoriestoassigncriteriagroup\" class=\"categoriestoassigncriteriagroup pull-left\">";
		$select_cat_class = 'select-cat';
		$html .= '<div class="btn-group pull-left">';
		$html .= '<button type="button" class="btn btn-mini" onclick="jQuery(\'.' . $select_cat_class . '\').each(function(el) { jQuery(this).prop(\'checked\', !jQuery(this).is(\':checked\')); });">' . JText::_('JGLOBAL_SELECTION_INVERT') . '</button>';
		$html .= '<button type="button" class="btn btn-mini" onclick="jQuery(\'.' . $select_cat_class . '\').each(function(el) { jQuery(this).prop(\'checked\', false); });">' . JText::_('JGLOBAL_SELECTION_NONE') . '</button>';
		$html .= '<button type="button" class="btn btn-mini" onclick="jQuery(\'.' . $select_cat_class . '\').each(function(el) {  jQuery(this).prop(\'checked\', true); });">' . JText::_('JGLOBAL_SELECTION_ALL') . '</button>';
		$html .= '</div>';

		$html .= "<ul id=\"" . $this->id . "\">";
		$html .= "<li>";
		$html .= "<input id=\"catid-" . $rootCategory->id . "\" type=\"checkbox\" value=\"" . $rootCategory->id . "\" disabled=\"true\" />";
		$html .= "<label for=\"catid-" . $rootCategory->id . "\">" . str_repeat('<span class="gi">|—</span>', $rootCategory->level) . $rootCategory->title . "</label>";
		$html .= "</li>";
		foreach ($categoryTree AS $item)
		{
			if ($item->id == $rootCategory->id)
			{
				continue;
			}

			$edit_criteriagroup_link = '';
			$html .= "<li>";
			
			if ($item->selected_criteriagroup == -1 && $item->criteriagroup_id > 0)
			{
				$html .= "<span class=\"inherited-value\" title=\"" . JText::_("COM_JUDOWNLOAD_INHERIT") . "\">" . JText::_("COM_JUDOWNLOAD_INHERIT") . "</span>";
			}
			
			elseif ($item->criteriagroup_id == $criteriagroup_id)
			{
				$html .= "<input class =\"" . $select_cat_class . "\" id=\"catid-" . $item->id . "\" type=\"checkbox\" value=\"" . $item->id . "\" checked=\"checked\" name=\"" . $this->name . "[]\"/>";
			}
			
			elseif ($item->criteriagroup_id > 0)
			{
				$edit_criteriagroup_link = "<span><a href=\"index.php?option=com_judownload&task=criteriagroup.edit&id=" . $item->criteriagroup_id . "\" title=\"" . JText::_('COM_JUDOWNLOAD_EDIT_THIS_CRITERIA_GROUP') . "\" >[" . $this->getCriteriaGroupName($item->criteriagroup_id) . "]</a></span>";
				$html .= "<input id=\"catid-" . $item->id . "\" type=\"checkbox\" checked=\"checked\" value=\"" . $item->id . "\" disabled=\"true\" />";
			}
			
			else
			{
				$html .= "<input class =\"" . $select_cat_class . "\" id=\"catid-" . $item->id . "\" type=\"checkbox\" value=\"" . $item->id . "\" name=\"" . $this->name . "[]\"/>";
			}

			$html .= "<label for=\"catid-" . $item->id . "\">" . str_repeat('<span class="gi">|—</span>', $item->level) . $item->title . "</label>";
			$html .= $edit_criteriagroup_link;
			$html .= "</li>";
		}

		$html .= "</ul>";
		$html .= "</div>";

		return $html;
	}

	protected function getCriteriaGroupName($id)
	{
		$storeId = md5(__METHOD__ . "::" . $id);
		if (!isset($this->cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = "SELECT name FROM #__judownload_criterias_groups WHERE id=$id";
			$db->setQuery($query);

			$this->cache[$storeId] = $db->loadResult();
		}

		return $this->cache[$storeId];
	}
}
