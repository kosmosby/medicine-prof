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

class JFormFieldCriteriaGroupForCat extends JFormField
{
	protected $type = 'criteriagroupforcat';

	protected function getInput()
	{
		$db    = JFactory::getDbo();
		$query = 'SELECT id AS value, name AS text, published FROM #__judownload_criterias_groups ';
		$db->setQuery($query);
		$options = $db->loadObjectList();

		if (!empty($options))
		{
			foreach ($options as $option)
			{
				if ($option->published != 1)
				{
					$option->text = "[" . $option->text . "]";
				}
			}
		}
		$users = JFactory::getUser();

		$required_class = $this->element['required'] == 'true' ? 'required' : '';
		$class          = $this->element['class'];

		$attributes = "class=\"inputbox $class $required_class\"";

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

		$html = JHtml::_('select.genericlist', $options, $this->name, $attributes, 'value', 'text', $this->value, $this->id);

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
				$return = " ( [" . $criteriaGroupName . "] ) ";
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