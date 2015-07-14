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

class JFormFieldCategoryFieldOrdering extends JFormField
{
	protected $type = 'categoryfieldordering';

	protected function getInput()
	{
		$document = JFactory::getDocument();
		$db       = JFactory::getDbo();
		JHtml::_('behavior.calendar');
		$script = "jQuery(document).ready(function(){
			var selected_fieldgroup_db = " . $this->form->getValue("selected_fieldgroup", null, -1) . ";
			jQuery('#" . $this->id . ", #jform_selected_fieldgroup').change(function(){
				var selected_fieldgroup = jQuery('#jform_selected_fieldgroup').val();
				jQuery('.field-lists').hide().find('li input[type=\"hidden\"]').attr('disabled', true);
				
				if(jQuery('#" . $this->id . "').val() == 1){
					
					jQuery('#field-lists-custom').show().find('li').hide();
					
					jQuery('#field-lists-default').hide();
					
					var fieldgroup_id = getFieldgroupId(selected_fieldgroup, selected_fieldgroup_db);
					
					jQuery('#field-lists-custom li[class=\"field-1\"]').show().find('input[type=\"hidden\"]').removeAttr('disabled');
					
					if(fieldgroup_id > 0){
						jQuery('#field-lists-custom li[class=\"field-'+fieldgroup_id+'\"]').show().find('input[type=\"hidden\"]').removeAttr('disabled');
					}
				}
				
				else
				{
					jQuery('#field-lists-custom').hide().find('input[type=\"hidden\"]').attr('disabled', true);
					var fieldgroup_id = getFieldgroupId(selected_fieldgroup, selected_fieldgroup_db);
					jQuery('#field-lists-default').show().find('li').hide().end().find('li[class=\"field-1\"], li[class=\"field-'+fieldgroup_id+'\"]').show();
				}
			});
			
			
			function getFieldgroupId(selected_fieldgroup, selected_fieldgroup_db){
				var fieldgroup_id = '';
				if(selected_fieldgroup == -1){
					if(selected_fieldgroup == selected_fieldgroup_db ){
						fieldgroup_id = jQuery('#jform_fieldgroup_id').val();
					}else{
						jQuery.ajax({
							  url: 'index.php?option=com_judownload&task=category.getFieldGroup&tmpl=component',
							  type: 'POST',
							  data: { cat_id : " . $this->form->getValue("parent_id", null, 0) . " }
							}).done(function(fieldg_id){
								var fieldgroup_id = parseInt(fieldg_id);
							});
					}
				}else{
					 fieldgroup_id = jQuery('#jform_selected_fieldgroup').val();
				}
				return fieldgroup_id;
			}
			
			jQuery('#" . $this->id . "').trigger('change');
			jQuery('#field-lists-custom').dragsort({ dragSelector: 'li', dragEnd: saveOrder, placeHolderTemplate: \"<li class='placeHolder'><div></div></li>\", dragSelectorExclude: ''});
            function saveOrder() {
	            return;
            };
		});";
		$document->addScriptDeclaration($script);

		$html       = '';
		$opstions   = array();
		$opstions[] = array("text" => JText::_('COM_JUDOWNLOAD_DEFAULT'), "value" => 0);
		$opstions[] = array("text" => JText::_('COM_JUDOWNLOAD_CUSTOM'), "value" => 1);
		$html .= JHtml::_('select.genericlist', $opstions, $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->id);

		
		$query = $db->getQuery(true);
		$query->select("field.id, field.caption, field.group_id, field_group.name AS fg_name, fordering.ordering");
		$query->from("#__judownload_fields AS field");
		$query->join("LEFT", "#__judownload_fields_ordering AS fordering ON (fordering.item_id = " . (int) $this->form->getValue("id") . " AND fordering.field_id = field.id AND fordering.type = 'category')");
		$query->join("", "#__judownload_fields_groups AS field_group ON (field_group.id = field.group_id)");
		$query->where("field.group_id = 1 OR field.group_id = " . (int) $this->form->getValue("fieldgroup_id"));
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

		$html .= "<ul class=\"field-lists\" id=\"field-lists-custom\" class=\"adminform\">";
		if ($fields)
		{
			foreach ($fields AS $field)
			{
				$html .= "<li class=\"field-" . $field->group_id . "\">";
				$html .= "<div>";
				$html .= "<span class=\"caption\">" . $field->caption . "</span>";
				$html .= "<span class=\"group\">" . $field->fg_name . "</span>";
				$html .= "<input type=\"hidden\" name=\"fields_ordering[]\" value=\"" . $field->id . "\" />";
				$html .= "</div>";
				$html .= "</li>";
			}
		}
		$html .= "</ul>";

		
		$query = $db->getQuery(true);
		$query->select("field.id, field.caption, field.group_id, field_group.name AS fg_name, fordering.ordering");
		$query->from("#__judownload_fields AS field");
		$query->join("LEFT", "#__judownload_fields_ordering AS fordering ON (fordering.item_id = " . (int) $this->form->getValue("fieldgroup_id") . " AND fordering.field_id = field.id AND fordering.type = 'fieldgroup')");
		$query->join("LEFT", "#__judownload_fields_groups AS field_group ON (field_group.id = field.group_id)");
		$query->where("field.group_id = 1 OR field.group_id = " . (int) $this->form->getValue("fieldgroup_id"));
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

		$html .= "<ul class=\"field-lists\" id=\"field-lists-default\" class=\"adminform\">";
		if ($fields)
		{
			foreach ($fields AS $field)
			{
				$html .= "<li class=\"field-" . $field->group_id . "\">";
				$html .= "<div>";
				$html .= "<span class=\"caption\">" . $field->caption . "</span>";
				$html .= "<span class=\"group\">" . $field->fg_name . "</span>";
				$html .= "</div>";
				$html .= "</li>";
			}
		}

		$html .= "</ul>";

		return $html;
	}
}

?>