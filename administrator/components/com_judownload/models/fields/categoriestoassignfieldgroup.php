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

class JFormFieldCategoriesToAssignFieldGroup extends JFormField
{
	protected $cache = array();

	
	protected $type = 'CategoriesToAssignFieldGroup';

	
	protected function getInput()
	{
		$fieldgroup_id = $this->form->getValue('id');
		$rootCategory  = JUDownloadFrontHelperCategory::getRootCategory();
		$categoryTree  = JUDownloadHelper::getCategoryTree($rootCategory->id);
		$html          = "<div id=\"categoriestoassignfieldgroup\" class=\"categoriestoassignfieldgroup\">";
		if ($this->form->getValue('id') == 1)
		{
			$html .= '<input type="text" class="readonly" readonly="readonly" value="' . JText::_('JALL') . '"/>';
		}
		else
		{
			$select_cat_class = 'select-cat';
			$html .= '<div class="btn-group pull-left">';
			$html .= '<button type="button" class="btn btn-mini" onclick="jQuery(\'.' . $select_cat_class . '\').each(function(el) { jQuery(this).prop(\'checked\', !jQuery(this).is(\':checked\')); });">' . JText::_('JGLOBAL_SELECTION_INVERT') . '</button>';
			$html .= '<button type="button" class="btn btn-mini" onclick="jQuery(\'.' . $select_cat_class . '\').each(function(el) { jQuery(this).prop(\'checked\', false); });">' . JText::_('JGLOBAL_SELECTION_NONE') . '</button>';
			$html .= '<button type="button" class="btn btn-mini" onclick="jQuery(\'.' . $select_cat_class . '\').each(function(el) {  jQuery(this).prop(\'checked\', true); });">' . JText::_('JGLOBAL_SELECTION_ALL') . '</button>';
			$html .= '</div>';

			$html .= "<ul id=\"" . $this->id . "\" class=\"nav\">";
			$html .= "<li>";
			$html .= "<input id=\"catid-" . $rootCategory->id . "\" type=\"checkbox\" value=\"" . $rootCategory->id . "\" disabled=\"true\" />";
			$html .= "<label for=\"catid-" . $rootCategory->id . "\">" . str_repeat('<span class="gi">|—</span>', $rootCategory->level) . $rootCategory->title . "</label>";
			$html .= "</li>";
			foreach ($categoryTree AS $category)
			{
				if ($category->id == $rootCategory->id)
				{
					continue;
				}

				$link_edit_fieldgroup = '';
				$html .= "<li>";
				
				if ($category->selected_fieldgroup == -1 && $category->fieldgroup_id > 0)
				{
					$html .= "<span class=\"inherited-value\"  title=\"" . JText::_("COM_JUDOWNLOAD_INHERIT") . "\">" . JText::_('COM_JUDOWNLOAD_INHERIT') . "</span>";
				}
				
				elseif ($category->fieldgroup_id == $fieldgroup_id)
				{
					$html .= "<input class =\"" . $select_cat_class . "\" id=\"catid-" . $category->id . "\" type=\"checkbox\" value=\"" . $category->id . "\" checked=\"checked\" name=\"" . $this->name . "[]\"/>";
				}
				
				elseif ($category->fieldgroup_id > 0)
				{
					$link_edit_fieldgroup = "<span><a href=\"index.php?option=com_judownload&task=fieldgroup.edit&id=" . $category->fieldgroup_id . "\" title=\"" . $this->getFieldGroupName($category->fieldgroup_id) . "\" >[" . $this->getFieldGroupName($category->fieldgroup_id) . "]</a></span>";
					$html .= "<input id=\"catid-" . $category->id . "\" type=\"checkbox\" checked=\"checked\" value=\"" . $category->id . "\" disabled=\"true\" />";
				}
				
				else
				{
					$html .= "<input class =\"" . $select_cat_class . "\" id=\"catid-" . $category->id . "\" type=\"checkbox\" value=\"" . $category->id . "\" name=\"" . $this->name . "[]\"/>";
				}

				$html .= "<label for=\"catid-" . $category->id . "\">" . str_repeat('<span class="gi">|—</span>', $category->level) . $category->title . "</label>";
				$html .= $link_edit_fieldgroup;
				$html .= "</li>";
			}
			$html .= "</ul>";
		}
		$html .= "</div>";

		return $html;
	}

	
	protected function getFieldgroupName($id)
	{
		$storeId = md5(__METHOD__ . "::" . $id);
		if (!isset($this->cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = "SELECT name FROM #__judownload_fields_groups WHERE id=" . $id;
			$db->setQuery($query);

			$this->cache[$storeId] = $db->loadResult();
		}

		return $this->cache[$storeId];
	}
}
