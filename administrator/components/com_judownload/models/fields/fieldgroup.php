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

class JFormFieldFieldGroup extends JFormField
{
	protected $type = 'fieldgroup';

	protected function getInput()
	{
		$html = '';
		$db   = JFactory::getDbo();

		$core_plugin = false;
		if ($this->form->getValue("id") && $this->form->getValue("plugin_id"))
		{
			$query = "SELECT core FROM #__judownload_plugins WHERE id = " . $this->form->getValue("plugin_id");
			$db->setQuery($query);
			$core_plugin = $db->loadResult();
		}

		if ($core_plugin)
		{
			$query = "SELECT name FROM #__judownload_fields_groups WHERE id = " . $this->value;
			$db->setQuery($query);
			$group_name = $db->loadResult();
			$html .= '<span class="readonly">' . $group_name . '</span>';
			$html .= '<input type="hidden" name="' . $this->name . '" value="1" />';
		}
		else
		{
			$document = JFactory::getDocument();
			$script   = "function changeFieldGroup(self, select ,value){
							if(value){
								if (self.value != select){ alert('" . JText::_('COM_JUDOWNLOAD_CHANGE_FIELD_GROUP_WARNING') . "');}
							}
							return true;
						}";
			$document->addScriptDeclaration($script);

			$app = JFactory::getApplication();
			if ($app->input->get('view') == 'field')
			{
				$options = JUDownloadHelper::getFieldGroupOptions(true, false);
			}
			else
			{
				$options = JUDownloadHelper::getFieldGroupOptions();
			}

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
			if ($app->input->get('view') == 'field')
			{
				
				$canChange = true;
				if ($this->form->getValue('id'))
				{
					$query = "SELECT COUNT(*) FROM #__judownload_fields_values WHERE field_id = " . $this->form->getValue('id');
					$db->setQuery($query);
					$canChange = $db->loadResult() ? false : true;
				}

				if ($canChange)
				{
					$attributes = "class=\"inputbox $required_class\"";
					$html .= JHtml::_('select.genericlist', $options, $this->name, $attributes, 'value', 'text', $this->value, $this->id);
				}
				else
				{
					$attributes = "class=\"inputbox\" disabled";
					$html .= JHtml::_('select.genericlist', $options, "_" . $this->name, $attributes, 'value', 'text', $this->value, $this->id);
					$html .= "<input class=\"$required_class\" type=\"hidden\" value=\"" . $this->value . "\" name=\"" . $this->name . "\" />";
				}
			}
			else
			{
				$onchange   = "onchange=\"changeFieldGroup(this, " . $this->value . ", " . $this->form->getValue("fieldgroup_id") . " );\"";
				$attributes = "class=\"inputbox $required_class\" $onchange";
				$html .= JHtml::_('select.genericlist', $options, $this->name, $attributes, 'value', 'text', $this->value, $this->id);
			}
		}

		return $html;
	}
}

?>