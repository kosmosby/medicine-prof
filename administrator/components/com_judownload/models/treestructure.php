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


jimport('joomla.application.component.modellist');


class JUDownloadModelTreeStructure extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'tbl_cat.id',
				'tbl_cat.title',
				'tbl_cat.lft',
				'published',
				'tbl_cat.published',
				'level',
				'tbl_cat.level',
				'tbl_cat.selected_fieldgroup',
				'tbl_cat.fieldgroup_id',
				'tbl_cat.selected_criteriagroup',
				'tbl_cat.criteriagroup_id',
				'tbl_cat.style_id',
				'access',
				'tbl_cat.access',
				'language',
				'tbl_cat.language',
			);
		}

		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level', '');
		$this->setState('filter.level', $level);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
		$this->setState('filter.access', $access);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		parent::populateState('tbl_cat.lft', 'asc');
	}

	
	protected function getStoreId($id = '')
	{
		
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.level');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	
	protected function getListQuery()
	{
		
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('tbl_cat.*');
		$query->from('#__judownload_categories AS tbl_cat');

		$query->select('ua.name AS checked_out_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = tbl_cat.checked_out');

		
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = tbl_cat.access');

		
		$query->select('tbl_field_group.name AS field_group_title')
			->join('LEFT', '#__judownload_fields_groups AS tbl_field_group ON tbl_field_group.id = tbl_cat.fieldgroup_id');

		
		$query->select('tbl_criteria_group.name AS criteria_group_title')
			->join('LEFT', '#__judownload_criterias_groups AS tbl_criteria_group ON tbl_criteria_group.id = tbl_cat.criteriagroup_id');

		
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('tbl_cat.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(tbl_cat.published = 0 OR tbl_cat.published = 1)');
		}

		
		$level = $this->getState('filter.level');
		if (is_numeric($level))
		{
			$query->where('tbl_cat.level = ' . (int) $level);
		}

		
		$access = $this->getState('filter.access');
		if (is_numeric($access))
		{
			$query->where('tbl_cat.access = ' . (int) $access);
		}

		
		$language = $this->getState('filter.language');
		if ($language)
		{
			$query->where('tbl_cat.language IN ("","*","' . $language . '")');
		}

		
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$search         = '%' . $db->escape($search, true) . '%';
			$field_searches = "tbl_cat.title LIKE '{$search}'";
			$query->where($field_searches);
		}

		
		$orderCol  = $this->getState('list.ordering', 'tbl_cat.lft');
		$orderDirn = $this->getState('list.direction', 'asc');

		if ($orderCol != '')
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	public function getItems()
	{
		$items = parent::getItems();

		if ($items)
		{
			foreach ($items AS $item)
			{
				$item->selected_fieldgroup_title = $this->calculateSelectedFieldGroup($item->selected_fieldgroup, $item->fieldgroup_id, $item->field_group_title);
				if (!$item->field_group_title)
				{
					
					$item->field_group_title = JText::_('None');
				}

				$item->selected_criteriagroup_title = $this->calculateSelectedCriteriaGroup($item->selected_criteriagroup, $item->criteriagroup_id, $item->criteria_group_title);
				if (!$item->criteria_group_title)
				{
					
					$item->criteria_group_title = JText::_('None');
				}

				$item->selected_style_title = $this->calculateStyle($item->style_id);

				$realStyle = $this->calculatorInheritStyle($item->id);

				$item->real_style_id = $realStyle->id;

				$item->style_title = $realStyle->title;

				$item->template_title = $realStyle->template_title;

				$item->template_id = $realStyle->template_id;
			}
		}

		return $items;
	}


	public function calculateSelectedFieldGroup($value, $fieldGroupId, $fieldGroupTitle)
	{
		if ($value == -1)
		{
			
			$settingTitle = JText::_('Inherited');
		}
		elseif ($value == 0)
		{
			
			$settingTitle = JText::_('None');
		}
		elseif ($value == $fieldGroupId)
		{
			$settingTitle = $fieldGroupTitle;
		}
		else
		{
			
			$settingTitle = JText::_('Undefined');
		}

		return $settingTitle;
	}


	public function calculateSelectedCriteriaGroup($value, $criteriaGroupId, $criteriaGroupTitle)
	{
		if ($value == -1)
		{
			
			$settingTitle = JText::_('Inherited');
		}
		elseif ($value == 0)
		{
			
			$settingTitle = JText::_('None');
		}
		elseif ($value == $criteriaGroupId)
		{
			$settingTitle = $criteriaGroupTitle;
		}
		else
		{
			
			$settingTitle = JText::_('Undefined');
		}

		return $settingTitle;
	}

	public function calculateStyle($value)
	{
		if ($value == -2)
		{
			
			$settingTitle = JText::_('Global config');
		}
		elseif ($value == -1)
		{
			
			$settingTitle = JText::_('Inherited');
		}
		elseif ($value == 0)
		{
			
			$settingTitle = JText::_('No');
		}
		elseif ($value == 1)
		{
			$settingTitle = JText::_('Yes');
		}
		else
		{
			
			$settingTitle = JText::_('Undefined');
		}

		return $settingTitle;
	}

	protected function calculatorInheritStyle($cat_id)
	{
		do
		{
			$category = JUDownloadHelper::getCategoryById($cat_id);
			$style_id = $category->style_id;
			$cat_id   = $category->parent_id;
		} while ($style_id == -1 && $cat_id != 0);

		if ($style_id == -2)
		{
			return $this->getStyle();
		}
		else
		{
			return $this->getStyle($style_id);
		}
	}

	protected function getStyle($id = null)
	{
		if ($id == null)
		{
			$where = "style.home = 1";
		}
		else
		{
			$where = "style.id = $id";
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('style.id, style.title, plg.title AS template_title, tpl.id AS template_id');
		$query->from('#__judownload_template_styles AS style');
		$query->join('', '#__judownload_templates AS tpl ON tpl.id = style.template_id');
		$query->join('', '#__judownload_plugins AS plg ON plg.id = tpl.plugin_id');
		$query->where($where);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

}
