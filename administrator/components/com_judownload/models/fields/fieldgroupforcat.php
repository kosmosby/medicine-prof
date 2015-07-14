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

class JFormFieldFieldGroupForCat extends JFormField
{
	protected $type = 'fieldgroupforcat';

	protected function getInput()
	{
		$html = '';
		$db   = JFactory::getDbo();

		$options = JUDownloadHelper::getFieldGroupOptions();

		if ($this->element['usenone'] == 'true')
		{
			array_unshift($options, array('value' => '0', 'text' => JText::_('COM_JUDOWNLOAD_NONE')));
		}

		if ($this->element['useinherit'] == 'true')
		{
			$appendInherit = "";
			if ($this->form->getValue("id"))
			{
				$appendInherit = " ( " . JText::_('COM_JUDOWNLOAD_NONE') . " )";
				if ($this->form->getValue("id") > 0)
				{
					$catObj = JUDownloadHelper::getCategoryById($this->form->getValue("parent_id"));
					if ($catObj->fieldgroup_id > 1)
					{
						$query = "SELECT name, published FROM #__judownload_fields_groups WHERE id = " . (int) $catObj->fieldgroup_id . " AND id != 1";
						$db->setQuery($query);
						$fieldgroup    = $db->loadObject();
						$groupName     = $fieldgroup->published != 1 ? "[" . $fieldgroup->name . "]" : $fieldgroup->name;
						$appendInherit = "( " . $groupName . " )";
					}
				}
			}
			array_unshift($options, array('value' => '-1', 'text' => JText::_('COM_JUDOWNLOAD_INHERIT') . $appendInherit));
		}
		else
		{
			array_unshift($options, array('value' => '', 'text' => JText::_('COM_JUDOWNLOAD_SELECT_FIELD_GROUP')));
		}

		$required_class = $this->element['required'] == 'true' ? 'required' : '';

		$attributes = "class=\"inputbox $required_class\"";
		$html .= JHtml::_('select.genericlist', $options, $this->name, $attributes, 'value', 'text', $this->value, $this->id);

		return $html;
	}
}

?>