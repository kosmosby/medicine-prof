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


class JFormFieldPagesAssignment extends JFormFieldCheckboxes
{
	
	protected $type = 'PagesAssignment';

	
	protected function getInput()
	{
		
		$html = array();

		
		$class = $this->element['class'] ? ' class="checkboxes ' . (string) $this->element['class'] . '"' : ' class="checkboxes"';

		
		$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';

		
		$options = $this->getOptions();

		
		$html[] = '<ul>';
		foreach ($options AS $i => $option)
		{
			
			$checked = ((in_array((string) $option->value, (array) $this->value) || empty($this->value)) ? ' checked="checked"' : '');

			$html[] = '<li>';
			$html[] = '<input type="checkbox" id="' . $this->id . $i . '" name="' . $this->name . '"' .
				' value="' . htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"'
				. $checked . '/>';

			$html[] = '<label for="' . $this->id . $i . '"' . $class . '> ' . JText::_($option->text) . '</label>';
			$html[] = '</li>';
		}
		$html[] = '</ul>';

		
		$html[] = '</fieldset>';

		return implode($html);
	}

	
	protected function getOptions()
	{
		$options = array(
			'categories' => JText::_('Categories'),
			'documents'  => JText::_('Documents')
		);

		$select_options = array();
		foreach ($options AS $key => $value)
		{
			$tmp              = JHtml::_('select.option', $key, $value, 'value', 'text');
			$select_options[] = $tmp;
		}

		return $select_options;
	}
}
