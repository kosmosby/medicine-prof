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

class JFormFieldCriteriaGroup extends JFormField
{
	protected $type = 'criteriagroup';

	protected function getInput()
	{
		$db    = JFactory::getDbo();
		$query = 'SELECT id AS value, name AS text, published FROM #__judownload_criterias_groups ';
		$db->setQuery($query);
		$options = $db->loadObjectList();
		$users   = JFactory::getUser();
		foreach ($options AS $key => $option)
		{
			if ($option->published != 1)
			{
				$option->text = '[ ' . $option->text . ' ]';
			}
			if (!$users->authorise('core.create', 'com_judownload.criteriagroup.' . $option->value))
			{
				unset($options[$key]);
			}
		}

		$required_class = $this->element['required'] == 'true' ? 'required' : '';
		$class          = $this->element['class'];
		if ($this->element['usenone'] == 'true')
		{
			array_unshift($options, array('value' => '0', 'text' => JText::_('COM_JUDOWNLOAD_NONE')));
		}

		if ($this->element['useinherit'] == 'true')
		{
			$appendInherit = "";
			if ($this->form->getValue("id"))
			{
				if ($this->form->getValue("criteriagroup_id") > 0)
				{
					$appendInherit = $this->getCriteriaGroupName($this->form->getValue("criteriagroup_id"));

				}
				else
				{
					$category      = JUDownloadHelper::getCategoryById($this->form->getValue("id"));
					$parent        = JUDownloadHelper::getCategoryById($category->parent_id);
					$appendInherit = $this->getCriteriaGroupName($parent->criteriagroup_id);
				}
			}
			array_unshift($options, array('value' => '-1', 'text' => JText::_('COM_JUDOWNLOAD_INHERIT') . $appendInherit));
		}
		else
		{
			array_unshift($options, array('value' => '', 'text' => JText::_('COM_JUDOWNLOAD_SELECT_CRITERIAL_GROUP')));
		}

		
		$canChange = true;
		if ($this->form->getValue('id'))
		{
			$query = "SELECT COUNT(*) FROM #__judownload_criterias_values WHERE criteria_id = " . $this->form->getValue('id');
			$db->setQuery($query);
			$canChange = $db->loadResult() ? false : true;
		}

		if ($canChange)
		{
			$attributes = "class=\"inputbox $class $required_class\"";
			$html       = JHtml::_('select.genericlist', $options, $this->name, $attributes, 'value', 'text', $this->value, $this->id);
		}
		else
		{
			$attributes = "class=\"inputbox $class\" disabled";
			$html       = JHtml::_('select.genericlist', $options, "_" . $this->name, $attributes, 'value', 'text', $this->value, $this->id);
			$html .= "<input class=\"$required_class\" type=\"hidden\" value=\"" . $this->value . "\" name=\"" . $this->name . "\" />";
		}

		return $html;
	}

	protected function getCriteriaGroupName($criteriagroup_id)
	{
		$return = " ( " . JText::_('COM_JUDOWNLOAD_NONE') . " ) ";
		if (!$criteriagroup_id)
		{
			return $return;
		}
		$db    = JFactory::getDbo();
		$query = "SELECT name, published FROM #__judownload_criterias_groups WHERE id = " . $criteriagroup_id;
		$db->setQuery($query);
		$criteriaGroup     = $db->loadObject();
		$criteriaGroupName = $criteriaGroup->name;
		if ($criteriaGroupName)
		{
			if ($criteriaGroup->published != 1)
			{
				$return = " ( [ " . $criteriaGroupName . " ] ) ";
			}
			else
			{
				$return = " ( " . $criteriaGroupName . " ) ";
			}
		}

		return $return;
	}
}

?>