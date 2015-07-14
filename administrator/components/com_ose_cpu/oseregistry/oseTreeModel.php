<?php
/**
 * @version     4.0 +
 * @package     Open Source Excellence Central Processing Units
 * @author      Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author      Created on 17-May-2011
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @Copyright Copyright (C) 2010- Open Source Excellence (R)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die(";)");
class oseTreeModel extends oseObject {
	protected $id = 0;
	protected $parent_id = 0;
	protected $title = null;
	protected $ordering = 1;
	protected $lft = 1;
	protected $rgt = 2;
	protected $leaf = 1;
	protected $level = 1;
	protected $required = 1;
	protected $published = null;
	protected $params = '';
	protected $_isNew = false;
	protected $_table = null;
	function __construct($p = array()) {
		parent::__construct($p);
	}
	function create() {
		$vals = $this->getProperties();
		unset($vals['id']);
		$vals['params'] = oseJson::encode($vals['params']);
		$updated = oseDB::insert($this->_table, $vals);
		if ($updated) {
			$this->set('id', $updated);
		}
		return $updated;
	}
	function update() {
		$vals = $this->getProperties();
		$vals['params'] = oseJson::encode($vals['params']);
		return oseDB::update($this->_table, 'id', $vals);
	}
	function getChildren() {
		$db = oseDB::instance();
		$query = " SELECT * FROM `{$this->_table}`" . " WHERE `parent_id` = " . $db->Quote($this->id) . " ORDER BY `ordering` ASC";
		$db->setQuery($query);
		$objs = oseDB::loadList('obj');
		return $objs;
	}
	function createChild($vals) {
		$db = oseDB::instance();
		$level = $this->getNodeLevel();
		$vals['level'] = $level + 1;
		$children = $this->getChildren();
		$childrenTotal = count($children);
		if ($childrenTotal > 0) {
			$child = $children[$childrenTotal - 1];
			$query = " UPDATE `{$this->_table}` AS acl2 SET acl2.rgt = acl2.rgt + 2 " . " WHERE acl2.rgt > {$child->rgt} ";
			$db->setQuery($query);
			if (!oseDB::query()) {
				return false;
			}
			$query = " UPDATE `{$this->_table}` AS acl2 SET acl2.lft = acl2.lft + 2 " . " WHERE acl2.lft > {$child->rgt} ";
			$db->setQuery($query);
			if (!oseDB::query()) {
				return false;
			}
			$vals['lft'] = $child->rgt + 1;
			$vals['rgt'] = $child->rgt + 2;
			$vals['ordering'] = $child->ordering + 1;
			$updated = oseDB::insert($this->_table, $vals);
			if ($updated) {
				$this->load();
			}
			return $updated;
		} else {
			// make two vacancy for the new item
			$query = " UPDATE `{$this->_table}` SET rgt = rgt + 2 " . " WHERE rgt > {$this->lft} ";
			$db->setQuery($query);
			if (!oseDB::query()) {
				return false;
			}
			$query = " UPDATE `{$this->_table}` AS acl2 SET acl2.lft = acl2.lft + 2 " . " WHERE acl2.lft > {$this->lft} ";
			$db->setQuery($query);
			if (!oseDB::query()) {
				return false;
			}
			$vals['lft'] = $this->lft + 1;
			$vals['rgt'] = $this->rgt + 1;
			$updated = oseDB::insert($this->_table, $vals);
			if ($updated) {
				$this->load();
			}
			return $updated;
		}
	}
	function load() {
	}
	function removeChildren() {
		$db = oseDB::instance();
		if ($this->rgt == $this->lft + 1) {
			return true;
		}
		$lft = $this->lft + 1;
		$rgt = $this->rgt - 1;
		$query = " DELETE FROM `{$this->_table}` " . " WHERE lft BETWEEN {$lft} AND {$rgt}";
		$db->setQuery($query);
		$updated = oseDB::query();
		if (!$updated) {
			return false;
		}
		$width = $rgt - $lft + 1;
		$query = " UPDATE `{$this->_table}` SET rgt = rgt - {$width}  " . " WHERE rgt > {$rgt}";
		$db->setQuery($query);
		oseDB::query();
		$query = " UPDATE `{$this->_table}` SET lft = lft - {$width} " . " WHERE lft > {$rgt} ";
		$db->setQuery($query);
		oseDB::query();
		$this->load();
		return true;
	}
	function delete() {
		$this->removeChildren();
		$db = oseDB::instance();
		$where = array();
		$query = " DELETE FROM `{$this->_table}` " . " WHERE lft BETWEEN {$this->lft} AND {$this->rgt}";
		$db->setQuery($query);
		$updated = oseDB::query();
		if (!$updated) {
			return false;
		}
		$width = $this->rgt - $this->lft + 1;
		$query = " UPDATE `{$this->_table}` SET rgt = rgt - {$width}  " . " WHERE rgt > {$this->rgt}";
		$db->setQuery($query);
		oseDB::query();
		$query = " UPDATE `{$this->_table}` SET lft = lft - {$width} " . " WHERE lft > {$this->rgt} ";
		$db->setQuery($query);
		return oseDB::query();
	}
	function publish($isTrue) {
		$isTrue = (int) $isTrue;
		$db = oseDB::instance();
		$where = array();
		$query = " UPDATE `{$this->_table}` SET `published` = '{$isTrue}'" . " WHERE lft BETWEEN {$this->lft} AND {$this->rgt}" . " AND `form_id` = '{$this->form_id}'";
		$db->setQuery($query);
		$updated = oseDB::query();
		return $updated;
	}
	function getNodeLevel() {
		$db = oseDB::instance();
		$where[] = "node.id =  {$this->id}";
		$where[] = 'node.lft BETWEEN parent.lft AND parent.rgt';
		$where = oseDB::implodeWhere($where);
		$query = " SELECT (COUNT(parent.id)) AS depth " . " FROM `{$this->_table}` AS node, `{$this->_table}` AS parent" . $where . " GROUP BY node.id" . " ORDER BY node.lft;";
		$db->setQuery($query);
		$depth = $db->loadResult();
		return $depth;
	}
	function reorder() {
		$db = oseDB::instance();
		$where = array();
		$where[] = "`parent_id` = " . $this->get('parent_id');
		$where = oseDB::implodeWhere($where);
		// Get the primary keys and ordering values for the selection.
		$query = " SELECT * FROM `{$this->_table}`" . $where . " ORDER BY ordering ASC";
		$db->setQuery($query);
		$rows = oseDB::loadList('obj');
		// Check for a database error.
		if ($db->getErrorNum()) {
			$result = JText::sprintf('JLIB_DATABASE_ERROR_REORDER_FAILED', get_class($this), $db->getErrorMsg());
			oseExit($result);
			return false;
		}
		// Compact the ordering values.
		foreach ($rows as $i => $row) {
			// Make sure the ordering is a positive integer.
			if ($row->ordering >= 0) {
				// Only update rows that are necessary.
				if ($row->ordering != $i + 1) {
					// Update the row ordering field.
					$query = " UPDATE `{$this->_table}` " . " SET `ordering`={$i}+1" . " WHERE `id` = {$row->id}";
					$db->setQuery($query);
					// Check for a database error.
					if (!oseDB::query()) {
						return false;
					}
				}
			}
		}
		return true;
	}
	function orderChange($ordering) {
		$db = oseDB::instance();
		//oseDB::lock('#__ose_commerce_element');
		$where = array();
		$where[] = "`parent_id` = " . $this->get('parent_id');
		$where[] = "`ordering`= {$ordering}";
		$where = oseDB::implodeWhere($where);
		$query = " SELECT * FROM `{$this->_table}`" . $where;
		$db->setQuery($query);
		// get the Current Msc paranet ID
		$obj = $db->loadObject();
		$where = array();
		if ($this->ordering > $ordering) // After
		{
			$where[] = "`ordering` >= {$ordering} ";
			$where[] = "`ordering` < {$this->ordering}";
			$where[] = "`form_id` = " . $this->get('form_id');
			$where[] = "`parent_id` = " . $this->get('parent_id');
			$where = oseDB::implodeWhere($where);
			$query = " UPDATE `{$this->_table}` " . " SET ordering= ordering+1 " . $where;
			$db->setQuery($query);
			if (!oseDB::query()) {
				return false;
			}
			$query = " UPDATE `{$this->_table}` " . " SET ordering= {$ordering} " . " WHERE id = {$this->id} ";
			$db->setQuery($query);
			if (!oseDB::query()) {
				return false;
			}
		} else {
			$where[] = "`ordering` <= '{$ordering}' ";
			$where[] = "`ordering` > '{$this->ordering}'";
			$where[] = "`form_id` = " . $this->get('form_id');
			$where[] = "`parent_id` = " . $this->get('parent_id');
			$query = " UPDATE `{$this->_table}` " . " SET ordering= ordering-1 " . $where;
			$db->setQuery($query);
			if (!oseDB::query()) {
				return false;
			}
			$query = " UPDATE `{$this->_table}` " . " SET ordering= {$ordering} " . " WHERE id = {$this->id} ";
			$db->setQuery($query);
			if (!oseDB::query()) {
				return false;
			}
		}
		return true;
	}
	/*
	 * $current  the item needed to move
	 * $ordering the position
	 */
	function treeOrderChange($ordering) {
		$db = oseDB::instance();
		$query = " SELECT * FROM `{$this->_table}`" . " WHERE `ordering` = '{$ordering}' AND `parent_id`=" . $this->get('parent_id');
		$db->setQuery($query);
		$obj = oseDB::loadItem('obj');
		$curWidth = $this->rgt - $this->lft + 1;
		$tObjs = $this->retrieveTree();
		$treeId = array();
		foreach ($tObjs as $tree) {
			$treeId[] = $tree->id;
		}
		$treeId = implode(',', $treeId);
		//if($current->lft > $obj->lft) // After
		if ($this->ordering > $ordering) {
			$objWidth = $this->lft - $obj->lft;
			$query = " UPDATE `{$this->_table}` " . " SET lft= lft + {$curWidth},rgt= rgt + {$curWidth} " . " WHERE rgt < {$this->lft} AND rgt > {$obj->lft} ";
			$db->setQuery($query);
			if (!oseDB::query()) {
				return false;
			}
			$query = " UPDATE `{$this->_table}` " . " SET lft= lft - {$objWidth},rgt= rgt - {$objWidth} " . " WHERE id IN ({$treeId}) ";
			$db->setQuery($query);
			if (!oseDB::query()) {
				return false;
			}
		} else {
			$objWidth = $obj->rgt - $this->rgt;
			$query = " UPDATE `{$this->_table}` " . " SET lft= lft - {$curWidth},rgt= rgt - {$curWidth} " . " WHERE lft > {$this->rgt} AND lft < {$obj->rgt} ";
			$db->setQuery($query);
			if (!oseDB::query()) {
				return false;
			}
			$query = " UPDATE `{$this->_table}` " . " SET lft= lft + {$objWidth},rgt= rgt + {$objWidth} " . " WHERE id IN ({$treeId}) ";
			$db->setQuery($query);
			if (!oseDB::query()) {
				return false;
			}
		}
		return true;
	}
	function retrieveTree() {
		$db = oseDB::instance();
		$where[] = 'node.lft BETWEEN parent.lft AND parent.rgt';
		$where[] = 'parent.id=' . $this->get('id');
		$where = oseDB::implodeWhere($where);
		$query = " SELECT node.* " . " FROM `{$this->_table}` AS node,`{$this->_table}` AS parent" . $where . " ORDER BY node.lft;";
		$db->setQuery($query);
		$objs = oseDB::loadList('obj');
		return $objs;
	}
	function getTreeByParentId($type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT * FROM `{$this->_table}` " . " ORDER BY parent_id, ordering ASC";
		$db->setQuery($query);
		$objs = oseDB::loadList($type);
		return $objs;
	}
	function rebuildTree() {
		$db = oseDB::instance();
		// Clear to 0
		$query = " UPDATE `{$this->_table}`" . " SET lft = 0, rgt = 0" . " WHERE lft != 0";
		$db->setQuery($query);
		oseDB::query();
		$query = " UPDATE `{$this->_table}`" . " SET lft = 1, rgt = 2" . " WHERE parent_id = 0 AND ordering = 1";
		$db->setQuery($query);
		oseDB::query();
		$children = array();
		$objs = $this->getTreeByParentId('obj');
		foreach ($objs as $v) {
			$pt = $v->parent_id;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push($list, $v);
			$children[$pt] = $list;
		}
		$this->treerecurse(0, '', array(), $children);
	}
	function getNode($msc_id, $type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT * FROM `{$this->_table}`" . " WHERE id = {$msc_id} ";
		$db->setQuery($query);
		$obj = oseDB::loadItem($type);
		return $obj;
	}
	function getParentNode() {
		$db = oseDB::instance();
		$query = " SELECT * FROM `{$this->_table}`" . " WHERE `id` = " . $db->Quote($this->parent_id);
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		return $item;
	}
	function getSiblingNode($node, $ordering, $type = 'array') {
		$db = oseDB::instance();
		$query = " SELECT * FROM `{$this->_table}`" . " WHERE parent_id = {$node->parent_id}" . " AND ordering = {$node->ordering} + ({$ordering})";
		$db->setQuery($query);
		$item = oseDB::loadItem($type);
		return $item;
	}
	function treerecurse($id, $indent, $list, &$children) {
		$db = oseDB::instance();
		if (isset($children[$id])) {
			$total = count($children[$id]);
			$i = 0;
			foreach ($children[$id] as $node) {
				$id = $node->id;
				if (isset($children[$node->id])) {
					if ($node->ordering == 1) {
						if ($node->parent_id != 0) {
							$parent = $this->getParentNode($node, 'obj');
							$query = " UPDATE `{$this->_table}` " . " SET lft = " . oseGetValue($parent, 'lft') . " +1" . " WHERE id = {$node->id} ";
							$db->setQuery($query);
							oseDB::query();
						}
					} else {
						$preSibling = $this->getSiblingNode($node, -1, 'obj');
						$query = " UPDATE `{$this->_table}` " . " SET lft = " . oseGetValue($preSibling, 'rgt') . " +1" . " WHERE id = {$node->id} ";
						$db->setQuery($query);
						oseDB::query();
					}
					$this->TreeRecurse($id, $indent, $list, $children);
				} else {
					if ($node->ordering == 1) {
						if ($node->parent_id != 0) {
							$parent = $this->getParentNode($node, 'obj');
							$lft = oseGetValue($parent, 'lft');
							$query = " UPDATE `{$this->_table}` " . " SET lft = {$lft} +1, rgt = {$lft} +2" . " WHERE id = {$node->id} ";
							$db->setQuery($query);
							oseDB::query();
						}
					} else {
						$preSibling = $this->getSiblingNode($node, -1, 'obj');
						$rgt = oseGetValue($preSibling, 'rgt');
						$query = " UPDATE `{$this->_table}` " . " SET lft = {$rgt} +1,rgt = {$rgt} +2" . " WHERE id = {$node->id} ";
						$db->setQuery($query);
						oseDB::query();
					}
				}
				if ($i == ($total - 1)) {
					$node = $this->getNode($node->id, 'obj');
					$query = " UPDATE `{$this->_table}` " . " SET rgt = {$node->rgt} + 1 " . " WHERE id = {$node->parent_id} ";
					$db->setQuery($query);
					oseDB::query();
				}
				$i++;
			}
		}
		return true;
	}
}
?>