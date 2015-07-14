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

class JFormFieldCategoryTreeModerator extends JFormField
{
	protected $type = 'CategoryTreeModerator';

	protected function getInput()
	{
		$app   = JFactory::getApplication();
		$modId = $app->input->getInt('id', 0);

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('user_id');
		$query->from('#__judownload_moderators');
		$query->where('id =' . $modId);
		$db->setQuery($query);

		$query = $db->getQuery(true);
		$query->select('cat_id');
		$query->from('#__judownload_moderators_xref');
		$query->where('mod_id = ' . $modId);
		$db->setQuery($query);
		$categoryIdsAssignedMod = $db->loadColumn();

		$html = "<div id=\"moderator-assign\" class=\"moderator-assign pull-left\">";

		$select_cat_class = 'select-cat';
		$html .= '<div class="btn-group pull-left">';
		$html .= '<button type="button" class="btn btn-mini" onclick="jQuery(\'.' . $select_cat_class . '\').each(function(el) { jQuery(this).prop(\'checked\', !jQuery(this).is(\':checked\')); });">' . JText::_('JGLOBAL_SELECTION_INVERT') . '</button>';
		$html .= '<button type="button" class="btn btn-mini" onclick="jQuery(\'.' . $select_cat_class . '\').each(function(el) { jQuery(this).prop(\'checked\', false); });">' . JText::_('JGLOBAL_SELECTION_NONE') . '</button>';
		$html .= '<button type="button" class="btn btn-mini" onclick="jQuery(\'.' . $select_cat_class . '\').each(function(el) { jQuery(this).prop(\'checked\', true); });">' . JText::_('JGLOBAL_SELECTION_ALL') . '</button>';
		$html .= '</div>';

		$categoryTree = JUDownloadHelper::getCategoryTree();
		$html .= "<ul id=\"" . $this->id . "\" class=\"nav\">";
		foreach ($categoryTree AS $category)
		{
			$html .= "<li>";
			if (in_array($category->id, $categoryIdsAssignedMod))
			{
				if (!in_array($category->id, $categoryIdsAssignedMod))
				{
					$html .= "<input id=\"catid-" . $category->id . "\" disabled type=\"checkbox\" value=\"" . $category->id . "\" name=\"jform[assigntocats][]\"/>";
				}
				else
				{
					$html .= "<input id=\"catid-" . $category->id . "\" class=\"" . $select_cat_class . "\" checked type=\"checkbox\" value=\"" . $category->id . "\" name=\"jform[assigntocats][]\"/>";
				}
			}
			else
			{
				$html .= "<input id=\"catid-" . $category->id . "\" class=\"" . $select_cat_class . "\" type=\"checkbox\" value=\"" . $category->id . "\" name=\"jform[assigntocats][]\"/>";
			}
			$html .= "<label for=\"catid-" . $category->id . "\">" . str_repeat('<span class="gi">|—</span>', $category->level) . "<a href='index.php?option=com_judownload&task=category.edit&id=" . $category->id . "'>" . $category->title . "</a></label>";
			$html .= "</li>";
		}

		$html .= "</ul>";
		$html .= "</div>";

		if ($modId != 0)
		{
			return $html;
		}
		else
		{
			$html = "<div id=\"moderator-assign\" class=\"moderator-assign pull-left\">";
			$html .= JText::_('COM_JUDOWNLOAD_PLEASE_SELECT_USER_BEFORE_SELECT_CATEGORY');
			$html .= "</div>";

			return $html;
		}
	}
}

?>