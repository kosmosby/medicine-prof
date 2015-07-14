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

class JFormFieldJuListFilter extends JFormField
{
	
	protected $type = 'julistfilter';

	protected function getInput()
	{
		
		$html = array();
		$attr = '';

		
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

		
		if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		
		$options = (array) $this->getOptions();

		
		if ((string) $this->element['readonly'] == 'true')
		{
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		}
		
		else
		{
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}

		return implode($html);
	}

	
	protected function getOptions()
	{
		
		$options = array();
		foreach ($this->element->children() AS $option)
		{
			$disable = false;
			
			if ($option->getName() != 'option')
			{
				continue;
			}

			if ($option['filter'] == true)
			{
				if ($option['value'] == "ccomment")
				{
					$file = JPATH_ROOT . '/administrator/components/com_comment/comment.php';
				}
				else
				{
					$com  = 'com_' . $option['value'];
					$file = JPATH_ROOT . '/administrator/components/' . $com . '/' . $option['value'] . '.php';
				}
				$file = JPath::clean($file);

				if (!JFile::exists($file))
				{
					$disable = true;
				}
			}

			
			$tmp = JHtml::_(
				'select.option', (string) $option['value'],
				JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
				'value', 'text', $disable
			);

			
			$tmp->class = (string) $option['class'];

			
			$tmp->onclick = (string) $option['onclick'];

			
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}