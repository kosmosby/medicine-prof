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

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('checkboxes');


class JFormFieldCategoriesAssignment extends JFormFieldCheckboxes
{
	
	protected $type = 'CategoriesAssignment';

	
	protected function getInput()
	{
		
		$class = $this->element['class'] ? ' class="checkboxes ' . (string) $this->element['class'] . '"' : ' class="checkboxes"';

		
		$html = '<fieldset id="' . $this->id . '"' . $class . '>';

		$html .= '<div class="btn-group pull-left">';
		$html .= '<button type="button"  class="btn btn-mini" onclick="jQuery(\'.assigned_cat\').each(function(el) { jQuery(this).prop(\'checked\', !jQuery(this).is(\':checked\')); });">';
		$html .= JText::_('JGLOBAL_SELECTION_INVERT') . '</button>';
		$html .= '<button type="button" class="btn btn-mini" onclick="jQuery(\'.assigned_cat\').each(function(el) { jQuery(this).prop(\'checked\', false); });">';
		$html .= JText::_('JGLOBAL_SELECTION_NONE') . '</button>';
		$html .= '<button type="button"  class="btn btn-mini" onclick="jQuery(\'.assigned_cat\').each(function(el) { jQuery(this).prop(\'checked\', true); });">';
		$html .= JText::_('JGLOBAL_SELECTION_ALL') . '</button>';
		$html .= '</div>';
		$html .= '<div class="clearfix"></div>';

		
		$options = $this->getOptions();

		
		$html .= '<ul class="clearfix" style="margin-top: 10px;">';

		foreach ($options AS $i => $option)
		{
			
			$checked = ((in_array((string) $option->value, (array) $this->value)) ? ' checked="checked"' : '');

			$html .= '<li>';
			$html .= '<input type="checkbox" id="' . $this->id . $i . '" class="assigned_cat" name="' . $this->name . '"' .
				' value="' . htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"'
				. $checked . '/>';

			$html .= '<label for="' . $this->id . $i . '">' . JText::_($option->text) . '</label>';
			$html .= '</li>';
		}
		$html .= '</ul>';

		
		$html .= '</fieldset>';

		return $html;
	}

	
	protected function getOptions()
	{
		$db = JFactory::getDbo();
		$db->setQuery('SELECT * FROM #__judownload_categories WHERE published = 1 AND level = 1 ORDER BY lft ASC');
		$categories = $db->loadObjectList();

		foreach ($categories AS $category)
		{
			$tmp       = JHtml::_('select.option', $category->id, $category->title, 'value', 'text');
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
