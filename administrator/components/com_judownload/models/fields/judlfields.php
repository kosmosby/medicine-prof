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

class JFormFieldJUDLFields extends JFormField
{
	
	protected $type = 'JUDLFields';

	

	protected function getInput()
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::root() . 'administrator/components/com_judownload/assets/css/juselect.css');
		$document->addScript(JUri::root() . 'administrator/components/com_judownload/assets/js/juselect.js');
		$document->addScript(JUri::root() . "components/com_judownload/assets/js/jquery.dragsort.min.js");

		$scriptv3 = '
		setTimeout(function(){
				$(".juselect-container").find(".juselect-select").show();
				$(".juselect-container").next(".chzn-container").hide();
		}, 10);';

		$script = 'jQuery(document).ready(function($){
			$("#' . $this->id . '").juSelect({
				\'selectItems\' : "' . ($this->value ? implode(",", $this->value) : '') . '"
			});

			$(".juselect-list").dragsort({
	            dragSelector: "li",
	            
	            placeHolderTemplate: \'<li class="placeHolder"></li>\',
	            dragSelectorExclude: \'a\'
            });

			' . (JUDownloadHelper::isJoomla3x() ? $scriptv3 : '') . '
		});';

		$document->addScriptDeclaration($script);

		$attr = '';
		
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1' || (string) $this->disabled == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		
		$options = (array) $this->getOptions();

		return JHtml::_(
			'select.groupedlist', $options, $this->name,
			array('id' => $this->id, 'group.id' => 'id', 'list.attr' => $attr, 'list.select' => '')
		);
	}

	
	protected function getOptions()
	{
		
		$options       = array();
		$fieldGroupArr = $this->getFieldsGroups();
		foreach ($fieldGroupArr AS $fieldGroup)
		{
			$fields = $this->getFieldsByGroupId($fieldGroup->id);
			if ($fields)
			{
				$options[$fieldGroup->id]          = array();
				$options[$fieldGroup->id]['text']  = $fieldGroup->name;
				$options[$fieldGroup->id]['items'] = array();
				foreach ($fields as $field)
				{
					$options[$fieldGroup->id]['items'][] = array('value' => $field->id, 'text' => $field->caption);
				}
			}
		}

		return $options;
	}

	protected function getFieldsGroups()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, name');
		$query->from('#__judownload_fields_groups');
		$query->where('published = 1');
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	protected function getFieldsByGroupId($groupId)
	{
		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();

		$query = $db->getQuery(true);
		$query->select('id, field_name, caption');
		$query->from('#__judownload_fields');
		$query->where('group_id = ' . $groupId);
		$query->where('published = 1');
		$query->where("publish_up <= " . $db->quote($nowDate));
		$query->where("(publish_down = " . $db->quote($nullDate) . " OR publish_down > " . $db->quote($nowDate) . ")");
		if ($this->element['moduleType'] == "filter_search")
		{
			$query->where('filter_search = 1');
		}
		$query->order('ordering');
		$db->setQuery($query);
		$fields = $db->loadObjectList();

		return $fields;
	}
}

?>