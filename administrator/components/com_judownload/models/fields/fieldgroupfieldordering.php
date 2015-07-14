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

class JFormFieldFieldGroupFieldOrdering extends JFormField
{
	protected $type = 'fieldgroupfieldordering';

	protected function getInput()
	{
		if ($this->form->getValue('id') == 1)
		{
			return '<input type="text" disabled="disabled" readonly="readonly" value="' . JText::_('COM_JUDOWNLOAD_NOT_SET') . '">';
		}
		$document = JFactory::getDocument();
		$db       = JFactory::getDbo();
		JHtml::_('behavior.calendar');
		$script = "jQuery(document).ready(function(){
			jQuery('#" . $this->id . "').change(function(){
				jQuery('.field-lists').hide();
				
				if(jQuery('#" . $this->id . "').val() == 1){
					
					jQuery('#field-lists-custom').show();
					
					jQuery('#field-lists-default').hide();
					
				}else{
					
					jQuery('#field-lists-custom').hide();
					
					jQuery('#field-lists-default').show();
				}
			});

			jQuery('#" . $this->id . "').trigger('change');
			jQuery('#field-lists-custom').dragsort({ dragSelector: 'li', dragEnd: saveOrder, placeHolderTemplate: \"<li class='placeHolder'><div></div></li>\", dragSelectorExclude: ''});
            function saveOrder() {
	            return;
            };
		});";
		$document->addScriptDeclaration($script);

		$html       = '<div class="fieldgroupfieldordering">';
		$opstions   = array();
		$opstions[] = array("text" => JText::_('COM_JUDOWNLOAD_DEFAULT'), "value" => 0);
		$opstions[] = array("text" => JText::_('COM_JUDOWNLOAD_CUSTOM'), "value" => 1);
		$html .= JHtml::_('select.genericlist', $opstions, $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->id);

		
		$query = $db->getQuery(true);
		$query->select("field.id, field.caption, field.group_id, field_group.name AS fg_name");
		$query->from("#__judownload_fields AS field");
		$query->join("", "#__judownload_fields_groups AS field_group ON (field_group.id = field.group_id)");
		$query->where("field.group_id = 1 OR field.group_id = " . $this->form->getValue("id", null, 0));
		$query->order("field.group_id, field.ordering");
		$db->setQuery($query);
		$fields = $db->loadObjectList();
		$html .= "<ul class=\"field-lists nav\" id=\"field-lists-default\" class=\"adminform\">";
		if ($fields)
		{
			foreach ($fields AS $field)
			{
				$html .= "<li>";
				$html .= "<div>";
				$html .= "<span class=\"caption\">" . $field->caption . "</span>";
				$html .= "<span class=\"group\">" . $field->fg_name . "</span>";
				$html .= "</div>";
				$html .= "</li>";
			}
		}
		$html .= "</ul>";

		
		if ($this->form->getValue("id"))
		{
			$query = $db->getQuery(true);
			$query->select("field.id, field.caption, field.group_id, field_group.name AS fg_name, fordering.ordering");
			$query->from("#__judownload_fields AS field");
			$query->join("LEFT", "#__judownload_fields_ordering AS fordering ON (fordering.item_id = " . (int) $this->form->getValue("id", null, 0) . " AND fordering.field_id = field.id AND fordering.type = 'fieldgroup')");
			$query->join("", "#__judownload_fields_groups AS field_group ON (field_group.id = field.group_id)");
			$query->where("field.group_id = 1 OR field.group_id = " . (int) $this->form->getValue("id", null, 0));
			$query->group('field.id');
			$query->order("fordering.ordering, field.group_id, field.ordering");
			$db->setQuery($query);
			$_fields = $db->loadObjectList();
			$fields  = array();
			foreach ($_fields AS $key => $field)
			{
				if (!is_null($field->ordering))
				{
					$fields[] = $field;
					unset($_fields[$key]);
				}
			}

			if (!empty($_fields))
			{
				$fields = array_merge($fields, $_fields);
			}
		}

		$html .= "<ul class=\"field-lists nav\" id=\"field-lists-custom\" class=\"adminform\">";
		if ($fields)
		{
			foreach ($fields AS $field)
			{
				$html .= "<li>";
				$html .= "<div>";
				$html .= "<span class=\"caption\">" . $field->caption . "</span>";
				$html .= "<span class=\"group\">" . $field->fg_name . "</span>";
				$html .= "<input type=\"hidden\" name=\"fields_ordering[]\" value=\"" . $field->id . "\" />";
				$html .= "</div>";
				$html .= "</li>";
			}
		}
		$html .= "</ul>";
		$html .= "</div>";

		return $html;
	}
}

?>