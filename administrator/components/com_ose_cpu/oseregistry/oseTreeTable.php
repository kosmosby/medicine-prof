<?php
defined('_JEXEC') or die(";)");
if (JOOMLA30 == true) {
	jimport('joomla.table.nested');
} else {
	jimport('joomla.database.tablenested');
}
class oseTreeTable extends JTableNested {
	function getChildren($pk, $recursive = true, $diagnostic = false) {
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;
		if ($recursive) {
			// Get the node and children as a tree.
			$query = $this->_db->getQuery(true);
			$select = ($diagnostic) ? 'n.' . $k . ', n.parent_id, n.level, n.lft, n.rgt' : 'n.*';
			$query->select($select);
			$query->from($this->_tbl . ' AS n, ' . $this->_tbl . ' AS p');
			$query->where('n.lft > p.lft AND n.lft < p.rgt');
			$query->where('p.' . $k . ' = ' . (int) $pk);
			$query->order('n.lft');
			$this->_db->setQuery($query);
			$tree = $this->_db->loadObjectList();
			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_GET_TREE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				return false;
			}
		} else {
			// Get the node and children as a tree.
			$query = $this->_db->getQuery(true);
			$select = ($diagnostic) ? 'n.' . $k . ', n.parent_id, n.level, n.lft, n.rgt' : 'n.*';
			$query->select($select);
			$query->from($this->_tbl . ' AS n, ' . $this->_tbl . ' AS p');
			$query->where('n.lft > p.lft AND n.lft < p.rgt');
			$query->where('p.' . $k . ' = ' . (int) $pk);
			$query->where('n.level=p.level+1');
			$query->order('n.lft');
			$this->_db->setQuery($query);
			$tree = $this->_db->loadObjectList();
			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_GET_TREE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				return false;
			}
		}
		return $tree;
	}
	function removeChildren($pk) {
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;
		// Lock the table for writing.
		if (!$this->_lock()) {
			// Error message set in lock method.
			return false;
		}
		// If tracking assets, remove the asset first.
		if ($this->_trackAssets) {
			$name = $this->_getAssetName();
			$asset = JTable::getInstance('Asset');
			// Lock the table for writing.
			if (!$asset->_lock()) {
				// Error message set in lock method.
				return false;
			}
			if ($asset->loadByName($name)) {
				// Delete the node in assets table.
				if (!$asset->delete(null, $children)) {
					$this->setError($asset->getError());
					$asset->_unlock();
					return false;
				}
				$asset->_unlock();
			} else {
				$this->setError($asset->getError());
				$asset->_unlock();
				return false;
			}
		}
		// Get the node by id.
		if (!$node = $this->_getNode($pk)) {
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}
		// Should we delete all children along with the node?
		// Delete the node and all of its children.
		$query = $this->_db->getQuery(true);
		$query->delete();
		$query->from($this->_tbl);
		$query->where('lft > ' . (int) $node->lft . ' AND lft <' . (int) $node->rgt);
		$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');
		// Compress the left values.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('lft = lft - ' . (int) $node->width + 2);
		$query->where('lft > ' . (int) $node->rgt);
		$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');
		// Compress the right values.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('rgt = rgt - ' . (int) $node->width + 2);
		$query->where('rgt > ' . (int) $node->rgt);
		$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');
		// Unlock the table for writing.
		$this->_unlock();
		return true;
	}
}
