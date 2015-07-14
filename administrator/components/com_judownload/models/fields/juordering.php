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

class JFormFieldJUOrdering extends JFormField
{

	protected $type = 'juordering';

	protected function getInput()
	{
		
		$html = array();
		$attr = '';

		
		$id = (int) $this->form->getValue('id');
		if ($this->element['table'])
		{
			switch (strtolower($this->element['table']))
			{
				case "category":
					$whereLabel = 'parent_id';
					$whereValue = (int) $this->form->getValue('parent_id');
					$table      = '#__judownload_categories';
					$query      = 'SELECT ordering AS value, title AS text FROM ' . $table;
					break;

				case "field":
					$whereLabel = 'group_id';
					$whereValue = (int) $this->form->getValue('group_id');
					$table      = '#__judownload_fields';
					$query      = 'SELECT ordering AS value, caption AS text FROM ' . $table;
					break;

				case "criterias":
					$whereLabel = 'group_id';
					$whereValue = (int) $this->form->getValue('group_id');
					$table      = '#__judownload_criterias';
					$query      = 'SELECT ordering AS value, title AS text FROM ' . $table;
					break;

				case "license":
					$whereLabel = '';
					$whereValue = '';
					$table      = '#__judownload_licenses';
					$query      = 'SELECT ordering AS value, title AS text FROM ' . $table;
					break;

				case "corefieldconfig":
					$whereLabel = 'group_id';
					$whereValue = (int) $this->form->getValue('group_id');
					$table      = '#__judownload_core_fields_config';
					$query      = 'SELECT ordering AS value, caption AS text FROM ' . $table;
					break;

				case "priority":
					$whereLabel = '';
					$whereValue = '';
					$table      = '#__judownload_fields';
					$query      = 'SELECT priority AS value, caption AS text FROM ' . $table;
					break;

				case "backend_list_view_ordering":
					$whereLabel = '';
					$whereValue = '';
					$table      = '#__judownload_fields';
					$query      = 'SELECT backend_list_view_ordering AS value, caption AS text FROM ' . $table;
					break;

				case "tags":
					$whereLabel = '';
					$whereValue = '';
					$table      = '#__judownload_tags';
					$query      = 'SELECT ordering AS value, title AS text FROM ' . $table;
					break;

				case "fields_groups":
					$whereLabel = '';
					$whereValue = '';
					$table      = '#__judownload_fields_groups';
					$query      = 'SELECT ordering AS value, name AS text FROM ' . $table;
					break;

				case "emails":
					$whereLabel = '';
					$whereValue = '';
					$table      = '#__judownload_emails';
					$query      = 'SELECT ordering AS value, subject AS text FROM ' . $table;
					break;

				default:
					break;
			}
		}

		
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		if ($whereLabel != '')
		{
			$query .= ' WHERE ' . $whereLabel . ' = ' . (int) $whereValue;
		}

		switch (strtolower($this->element['table']))
		{
			case "priority" :
			case "priority_field" :
				$query .= ' ORDER BY priority';
				break;
			case "backend_list_view_ordering" :
				$query .= ' ORDER BY backend_list_view_ordering';
				break;
			default:
				$query .= ' ORDER BY ordering';
				break;
		}
		
		if ((string) $this->element['readonly'] == 'true')
		{
			$html[] = JHtml::_('list.ordering', '', $query, trim($attr), $this->value, $id ? 0 : 1);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '" />';
		}
		
		else
		{
			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, $id ? 0 : 1);
		}

		return implode($html);
	}
}