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

class JFormFieldCategoriesToAssignEmail extends JFormField
{
	protected $type = "CategoriesToAssignEmail";

	public function getInput()
	{
		$categories   = JUDownloadHelper::getCatsByLevel(1);
		$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();

		$document = JFactory::getDocument();
		$script   = 'jQuery(document).ready(function($){
			$("#cat-' . $rootCategory->id . '").change(function(){
				if($(this).is(":checked")){
					$(this).parent().parent().find(".category-checkbox").attr("disabled", true).prop("checked", true);
				}else{
					$(this).parent().parent().find(".category-checkbox").attr("disabled", false).prop("checked", false);
				}
			});
		});';
		$document->addScriptDeclaration($script);

		$relCatIds = array();
		if ($this->form->getValue('id'))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('c.id');
			$query->from('#__judownload_categories AS c');
			$query->join('', '#__judownload_emails_xref AS exref ON (exref.cat_id = c.id)');
			$query->join('', '#__judownload_emails AS e ON (exref.email_id = e.id)');
			$query->where('exref.email_id = ' . $this->form->getValue('id'));
			$db->setQuery($query);
			$relCatIds = $db->loadColumn();
		}

		$html = '<div id="categoriestoassignemail" class="categoriestoassignemail">';
		$html .= '<ul id="' . $this->id . '" class="nav">';
		$html .= '<li>';
		$html .= '<input id="cat-' . $rootCategory->id . '" name="' . $this->name . '[]"  value = "' . $rootCategory->id . '" ' . (in_array($rootCategory->id, $relCatIds) ? 'checked="checked"' : '') . ' class="input" type="checkbox" />';
		$html .= '<label for="cat-' . $rootCategory->id . '">' . str_repeat('<span class="gi">|—</span>', $rootCategory->level) . $rootCategory->title . '</label>';
		$html .= '</li>';
		if ($categories)
		{
			foreach ($categories AS $category)
			{
				$html .= '<li>';
				$attr = '';
				if (in_array($rootCategory->id, $relCatIds))
				{
					$attr = 'checked="checked" disabled="disabled"';
				}
				elseif (in_array($category->id, $relCatIds))
				{
					$attr = 'checked="checked"';
				}

				$html .= '<input id="cat-' . $category->id . '" class="input category-checkbox" type="checkbox" name="' . $this->name . '[]" value = "' . $category->id . '" ' . $attr . '/>';
				$html .= '<label for="cat-' . $category->id . '">' . str_repeat('<span class="gi">|—</span>', $category->level) . $category->title . '</label>';
				$html .= '</li>';
			}
		}

		$html .= '</ul></div>';

		return $html;
	}
}
