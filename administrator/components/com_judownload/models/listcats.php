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


class JUDownloadModelListCats extends JModelList
{
	
	public $fields = array();
	public $fields_use = array();
	public $dropdown_fields = array();
	public $dropdown_fields_selected = array();
	
	public $cat_fields = array();
	public $cat_fields_use = array();
	public $cat_dropdown_fields = array();
	public $cat_dropdown_fields_selected = array();

	public $params;

	
	public function __construct($config = array())
	{
		$app          = JFactory::getApplication();
		$rootCat      = JUDownloadFrontHelperCategory::getRootCategory();
		$cat_id       = $app->input->getInt('cat_id', $rootCat->id);
		$this->params = JUDownloadHelper::getParams($cat_id);
		if (!$cat_id)
		{
			$app->input->set('cat_id', $rootCat->id);
		}

		
		$fields       = array_filter($app->input->get("fields", array(), 'array'));
		$apply_layout = $app->input->getInt("apply_layout", 0);
		$reset_layout = $app->input->getInt("reset_layout", 0);
		if ($fields && $apply_layout)
		{
			$app->setUserState("com_judownload.listcats." . $cat_id . ".fields", $fields);
		}
		$this->fields = $this->getFields($cat_id);

		if ($reset_layout || !$app->getUserState("com_judownload.listcats." . $cat_id . ".fields", array()))
		{
			$fieldIds = array();
			foreach ($this->fields AS $field)
			{
				if ($field->backend_list_view == 2)
				{
					$fieldIds[] = $field->id;
				}
			}

			$app->setUserState("com_judownload.listcats." . $cat_id . ".fields", $fieldIds);
		}

		$fieldIds = $app->getUserState("com_judownload.listcats." . $cat_id . ".fields", array());
		if (empty($fieldIds))
		{
			$fieldIds = array();
			foreach ($this->fields AS $field)
			{
				if (in_array($field->field_name, array("title", "id")))
				{
					$fieldIds[] = $field->id;
				}
			}
		}

		$sortedFields = array();
		foreach ($this->fields AS $field)
		{
			if (in_array($field->id, $fieldIds))
			{
				$sortedFields[]                   = $field->id;
				$this->dropdown_fields_selected[] = array("value" => $field->id, "text" => $field->caption);

				$this->fields_use[] = $field;
			}

			$this->dropdown_fields[] = array("value" => $field->id, "text" => $field->caption);
		}

		$config['filter_fields'] = $sortedFields;

		
		$this->cat_fields   = JUDownloadHelper::getCatFields();
		$cat_field_selected = array_filter($app->input->get("category_fields", array(), 'array'));
		$apply_cat_layout   = $app->input->getInt("apply_cat_layout", 0);
		$reset_cat_layout   = $app->input->getInt("reset_cat_layout", 0);
		if ($cat_field_selected && $apply_cat_layout)
		{
			$app->setUserState("com_judownload.listcats." . $cat_id . ".categoryfields", $cat_field_selected);
		}
		$cat_dropdown_field_selected = (array) $this->params->get('category_fields_listview_ordering');
		$cat_field                   = array();
		foreach ($cat_dropdown_field_selected AS $field => $value)
		{
			if ($value == 2)
			{
				$cat_field[] = $field;
			}
			$this->cat_dropdown_fields[] = array("value" => $field, "text" => $this->cat_fields[$field]);
		}

		if ($reset_cat_layout || !$app->getUserState("com_judownload.listcats." . $cat_id . ".categoryfields", array()))
		{
			$app->setUserState("com_judownload.listcats." . $cat_id . ".categoryfields", $cat_field);
		}

		$this->cat_fields_use = $app->getUserState("com_judownload.listcats." . $cat_id . ".categoryfields", array());

		if (empty($this->cat_fields_use))
		{
			$this->cat_fields_use = array("title", "id");
		}

		foreach ($this->cat_fields_use AS $field)
		{
			$this->cat_dropdown_fields_selected[] = array("value" => $field, "text" => $this->cat_fields[$field]);
		}

		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{

		$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
		$cat_id  = $this->getUserStateFromRequest($this->context . '.cat_id', 'cat_id', $rootCat->id);
		$this->setState('list.cat_id', $cat_id);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search_cat', 'filter_search_cat');
		$this->setState('filter.search_cat', $search);

		$search = $this->getUserStateFromRequest($this->context . '.filter.simple_search', 'simple_search');
		$this->setState('filter.simple_search', $search);

		$search_in = $this->getUserStateFromRequest($this->context . '.filter.search_in', 'search_in', 'documents', 'none', false);
		$this->setState('filter.search_in', $search_in);

		$ordering = $this->getUserStateFromRequest($this->context . '.filter.ordering_cat', 'filter_order_cat');
		if (empty($ordering))
		{
			$this->setState('filter.ordering_cat', 'lft');
		}
		else
		{
			$this->setState('filter.ordering_cat', $ordering);
		}

		$direction = $this->getUserStateFromRequest($this->context . '.filter.direction_cat', 'filter_order_Dir_cat');
		if (empty($direction))
		{
			$this->setState('filter.direction_cat', 'asc');
		}
		else
		{
			$this->setState('filter.direction_cat', $direction);
		}

		parent::populateState($ordering, $direction);

	}

	
	protected function getStoreId($id = '')
	{
		
		$id .= ':' . $this->getState('list.cat_id');
		$id .= ':' . $this->getState('list.search');

		return parent::getStoreId($id);
	}

	
	protected function _getListCount($query)
	{
		
		if ($query instanceof JDatabaseQuery
			&& $query->type == 'select'
			&& ($query->group !== null || $query->having !== null)
		)
		{
			$query = clone $query;
			$query->clear('select')->clear('order')->select('1');
		}

		return parent::_getListCount($query);
	}

	
	protected function getListQuery()
	{
		$cat_id    = $this->state->get('list.cat_id');
		$listOrder = $this->state->get('list.ordering');
		$listDirn  = $this->state->get('list.direction');
		$db        = JFactory::getDbo();
		$query     = $db->getQuery(true);
		if ($this->fields)
		{
			$query->SELECT('d.*');
			$query->FROM('#__judownload_documents AS d');
			$query->SELECT('dxref.main');
			$query->JOIN("", "#__judownload_documents_xref AS dxref ON d.id = dxref.doc_id");
			$query->JOIN("", "#__judownload_categories AS c ON c.id = dxref.cat_id");
			$query->SELECT('ua3.name AS checked_out_name');
			$query->JOIN("LEFT", "#__users AS ua3 ON d.checked_out = ua3.id");

			$search = $this->state->get('filter.search');
			if (!empty($search))
			{
				$search = '%' . $db->escape($search, true) . '%';
				$where  = "(dxref.cat_id = $cat_id AND d.title LIKE '{$search}')";
			}
			else
			{
				$where = "dxref.cat_id = $cat_id";
			}

			$query->WHERE($where);

			$query->WHERE('d.approved > 0');

			JUDownloadFrontHelperField::appendFieldOrderingPriority($query, $cat_id, $listOrder, $listDirn);

			$query->group('d.id');

			return $query;
		}

		return null;
	}

	public function getItems()
	{
		$items = parent::getItems();
		if ($items)
		{
			foreach ($items AS $item)
			{
				$item->actionlink = 'index.php?option=com_judownload&amp;task=document.edit&amp;id=' . $item->id;
			}
		}

		return $items;
	}

	
	public function getListCategory($parentId, $ordering = 'lft', $direction = 'asc')
	{
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$fields = $this->cat_fields_use;
		
		if (!in_array('id', $fields))
		{
			$fields[] = 'id';
		}

		$fields[] = 'checked_out';
		$fields[] = 'created_by';
		if (in_array('published', $fields))
		{
			$fields[] = 'publish_up';
			$fields[] = 'publish_down';
		}

		$fields = array_unique($fields);
		foreach ($fields AS $field)
		{
			switch ($field)
			{
				case "description":
					$query->SELECT('CONCAT(c.introtext, c.fulltext) AS description');
					break;

				case "intro_image":
				case "detail_image":
					$query->SELECT('c.images');
					break;
				case "access":
					$query->select('vl.title AS access');
					$query->join('LEFT', '#__viewlevels AS vl ON vl.id = c.access');
					break;

				case "checked_out":
					$query->select('c.checked_out');
					$query->select('c.checked_out_time');
					$query->select('ua.name AS checked_out_name');
					$query->join("LEFT", "#__users AS ua ON c.checked_out = ua.id");
					break;

				case "total_categories":
					$query->select('(SELECT COUNT(*) FROM #__judownload_categories AS c1 WHERE c1.lft BETWEEN c.lft+1 AND c.rgt) AS total_categories');
					break;

				case "total_documents":
					$query->select('(SELECT COUNT(*) FROM #__judownload_documents AS d
					                    JOIN #__judownload_documents_xref AS dxref ON d.id = dxref.doc_id
					                    JOIN #__judownload_categories AS c2 ON c2.id = dxref.cat_id
					                    WHERE (c2.lft BETWEEN c.lft AND c.rgt) AND d.approved = 1 ) AS total_documents');
					break;

				case "rel_cats":
					$query->select('(SELECT COUNT(*) FROM #__judownload_categories_relations AS rc WHERE rc.cat_id = c.id) AS total_relations');
					break;

				
				case "title":
					$query->select('c.title');
				case "alias":
					$query->select('c.alias');
					break;

				
				case "lft":
					$query->select('c.lft');
				case "parent_id":
					$query->select('c.parent_id');
					break;

				default:
					$query->select('c.' . $field);
					break;
			}
		}

		$query->from('#__judownload_categories AS c');
		$search_cat = $this->state->get('filter.search_cat');
		if (!empty($search_cat))
		{
			$search = '%' . $db->escape($search_cat, true) . '%';
			$where  = "(parent_id=$parentId AND c.title LIKE '{$search}')";
		}
		else
		{
			$where = "parent_id=$parentId";
		}

		$query->where($where);

		if ($ordering)
		{
			switch ($ordering)
			{
				case 'intro_image':
				case 'detail_image':
					$query->order('c.images ' . $direction);
					break;

				default:
					$query->order($ordering . ' ' . $direction);
					break;
			}
		}
		$db->setQuery($query);

		$rows = $db->loadObjectList();

		return $rows;
	}

	
	public function getRelatedCategories($cat_id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("c.title");
		$query->from("#__judownload_categories AS c");
		$query->join("", "#__judownload_categories_relations AS cr ON cr.cat_id_related = c.id");
		$query->where("cr.cat_id = $cat_id");
		$db->setQuery($query);

		return $db->loadColumn();
	}

	
	public function hasDocumentPending($doc_id)
	{
		$db    = $this->getDbo();
		$query = "SELECT COUNT(*) FROM #__judownload_documents WHERE id = -" . $doc_id;
		$db->setQuery($query);

		return $db->loadResult();
	}

	
	public function getFields($cat_id)
	{
		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();
		$query    = $db->getQuery(true);
		$query->select("field.*, plg.folder");
		$query->from("#__judownload_fields AS field");
		$query->join("", "#__judownload_fields_groups AS field_group ON field.group_id = field_group.id");
		$query->join("", "#__judownload_categories AS c ON (c.fieldgroup_id = field_group.id OR field.group_id = 1)");
		$query->join("", "#__judownload_plugins AS plg ON plg.id = field.plugin_id");
		$query->where("(c.id = " . $cat_id . " OR field.group_id = 1)");
		$query->where("field_group.published = 1");
		$query->where("field.backend_list_view >= 1");
		$query->where("field.published > 0");
		$query->where('field.publish_up <= ' . $db->quote($nowDate));
		$query->where('(field.publish_down = ' . $db->quote($nullDate) . ' OR field.publish_down > ' . $db->quote($nowDate) . ')');
		$query->order("field.backend_list_view_ordering");
		$query->group('field.id');
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
