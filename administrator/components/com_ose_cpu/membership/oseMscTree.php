<?php
defined('_JEXEC') or die(";)");
class oseMscTree
{
	// assume the msc_id == parent_id exists
	public static function add($array = array('parent_id'=>0))
	{
		$db = oseDB::instance();
		// empty at all
		if(self::isEmpty(0))
		{
			return self::addAtFirstTime($array);
		}
		else
		{
			$parentId = empty($array['parent_id'])?0:$db->Quote($array['parent_id']);
			$ordering = empty($array['ordering'])?999:$array['ordering'];
			$where = array();
			if(self::isEmpty($parentId))
			{
				return self::addChildNode($parentId,$ordering);
			}
			else
			{
				return self::addNode($parentId,$ordering);
			}
		}
	}
	public static function isEmpty($parent_id)
	{
		$db = oseDB::instance();
		$query = " SELECT count(*) FROM `#__osemsc_acl`"
		." WHERE parent_id={$parent_id} "
		;
		$db->setQuery($query);
		if($db->loadResult() > 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	public static function isLeaf($msc_id)
	{
		$db = oseDB::instance();
		$query = " SELECT count(*) FROM `#__osemsc_acl` "
		." WHERE id= {$msc_id} AND rgt = lft + 1 "
		;
		$db->setQuery($query);

		if( $db->loadResult() > 0 )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function addAtFirstTime($array)
	{
		$db= oseDB::instance();
		$title = empty($array['title'])?$db->Quote(''):$db->Quote($array['title']);
		$name = empty($array['name'])?$db->Quote('msc_1'):$db->Quote($array['name']);
		$parentId = empty($array['parent_id'])?0:$db->Quote($array['parent_id']);
		oseDB::lock('#__osemsc_acl WRITE');
		$query = " INSERT INTO `#__osemsc_acl` (`id`,`title`,`alias`) "
		." VALUES ('1',{$title},{$name}) "
		;
		$db->setQuery($query);
		$result = $db->query();
		oseDB::unlock();

		return $result;
	}

	public static function addChildNode($parent_id,$ordering)
	{
		$db = oseDB::instance();

		$depth = self::getNodeDepth($parent_id);
		$level = $depth + 1;

		$where = array();
		$where[] = "acl2.id = {$parent_id}";

		if(empty($ordering))
		{
			$where[] = "acl2.ordering = ".$db->Quote($ordering);
		}

		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );

		$query = " LOCK TABLE `#__osemsc_acl` WRITE, `#__osemsc_acl` AS acl2 WRITE";
		$db->setQuery($query);
		$db->query();

		$query = " SELECT acl2.* FROM `#__osemsc_acl` AS acl2"
		. $where
		." ORDER BY acl2.ordering DESC LIMIT 1 "
		;

		$db->setQuery($query);
		$item = $db->loadObject();

		// make two vacancy for the new item
		$query = " UPDATE `#__osemsc_acl` AS acl2 SET acl2.rgt = acl2.rgt + 2 "
		." WHERE acl2.rgt > {$item->lft} "
		;
		$db->setQuery($query);
		if(!$db->query())
		{
			oseDB::unlock();
			return false;
		}


		$query = " UPDATE `#__osemsc_acl` AS acl2 SET acl2.lft = acl2.lft + 2 "
		." WHERE acl2.lft > {$item->lft} "
		;

		$db->setQuery($query);
		if(!$db->query())
		{
			oseDB::unlock();
			return false;
		}

		$query = " INSERT INTO `#__osemsc_acl` (`title`,`alias`,`lft`,`rgt`,`parent_id`,`ordering`,`level`) "
		." SELECT 'New Membership',CONCAT('msc_',(MAX(id)+1)),{$item->lft}+1,{$item->lft}+2,{$parent_id},{$ordering},{$level} FROM `#__osemsc_acl` AS acl2"
		;

		$db->setQuery($query);
		//oseExit($db->_sql);
		if(!$db->query())
		{
			oseDB::unlock();
			return false;
		}

		$query = " UPDATE `#__osemsc_acl` AS acl2 "
		." SET acl2.leaf = 0 "
		." WHERE acl2.id = {$parent_id} "
		;

		$db->setQuery($query);
		if(!$db->query())
		{
			oseDB::unlock();
			return false;
		}

		oseDB::unlock();
		return true;
	}

	public static function addNode($parent_id,$ordering)
	{
		$where = array();

		$db = oseDB::instance();

		$where[] = "acl2.parent_id = {$parent_id}";

		if(empty($ordering))
		{
			$where[] = "acl2.ordering = ".$db->Quote($ordering);
		}

		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );


		$depth = self::getNodeDepth($parent_id);
		$level = $depth + 1;

		$query = " LOCK TABLE `#__osemsc_acl` WRITE, `#__osemsc_acl` AS acl2 WRITE";
		$db->setQuery($query);
		$db->query();

		$query = " SELECT acl2.* FROM `#__osemsc_acl` AS acl2"
		. $where
		." ORDER BY acl2.ordering DESC LIMIT 1"
		;

		$db->setQuery($query);
		$item = $db->loadObject();

		// make two vacancy for the new item
		$query = " UPDATE `#__osemsc_acl` AS acl2 SET acl2.rgt = acl2.rgt + 2 "
		." WHERE acl2.rgt > {$item->rgt} "
		;
		$db->setQuery($query);
		if(!$db->query())
		{
			oseDB::unlock();
			return false;
		}


		$query = " UPDATE `#__osemsc_acl` AS acl2 SET acl2.lft = acl2.lft + 2 "
		." WHERE acl2.lft > {$item->rgt} "
		;

		$db->setQuery($query);
		if(!$db->query())
		{
			oseDB::unlock();
			return false;
		}

		$query = " INSERT INTO `#__osemsc_acl` (`title`,`alias`,`lft`,`rgt`,`parent_id`,`ordering`,`level`) "
		." SELECT 'New Membership',CONCAT('msc_',(MAX(id)+1)),{$item->rgt}+1,{$item->rgt}+2,{$parent_id},{$ordering},{$level} FROM `#__osemsc_acl` AS acl2"
		;

		$db->setQuery($query);
		if(!$db->query())
		{
			oseDB::unlock();
			return false;
		}

		oseDB::unlock();
		return true;
	}

	public static function delNode($msc_id)
	{
		$db = oseDB::instance();

		$query = " LOCK TABLE `#__osemsc_acl` WRITE, `#__osemsc_acl` AS acl2 WRITE";
		$db->setQuery($query);
		$db->query();

		$query = "SELECT * FROM `#__osemsc_acl`"
		."WHERE id = {$msc_id}"
		;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');

		if(empty($item))
		{
			oseDB::unlock();
			return true;
		}

		$query = " DELETE FROM `#__osemsc_acl` "
		." WHERE lft BETWEEN {$item->lft} AND {$item->rgt};"
		;
		$db->setQuery($query);
		if(!oseDB::query(true))
		{
			return false;
		}

		$width = $item->rgt - $item->lft + 1;

		$query = " UPDATE `#__osemsc_acl` SET rgt = rgt - {$width}  "
		." WHERE rgt > {$item->rgt}"
		;
		$db->setQuery($query);
		$db->query();

		$query = " UPDATE `#__osemsc_acl` SET lft = lft - {$width} "
		." WHERE lft > {$item->rgt} "
		;
		$db->setQuery($query);
		$db->query();

		oseDB::unlock();

		return true;
	}

	/*
	 *  Not test
	*
	*/
	public static  function retrieveTree($msc_id = 0,$type = 'array')
	{
		$db = oseDB::instance();
		$searchName = $db->Quote($msc_id);
		if (!class_exists("JoomFishManager"))
		{
			$query = "LOCK TABLE `#__osemsc_acl` AS node READ, `#__osemsc_acl` AS parent READ";
			$db->setQuery($query);
			$db->query();
		}
		$where = array();
		if(!empty($msc_id))
		{
			$where[] = "parent.id = {$msc_id}";
		}
		$where[] = 'node.lft BETWEEN parent.lft AND parent.rgt';
		$where = oseDB::implodeWhere($where);
		$query = " SELECT node.* "
		." FROM `#__osemsc_acl` AS node,`#__osemsc_acl` AS parent"
		. $where
		." ORDER BY node.lft;"
		;

		$db->setQuery($query);
		$objs = oseDB::loadList($type);
		oseDB::unlock();
		return $objs;
	}

	public static function getAllLeaf($type = 'array')
	{
		$db = oseDB::instance();

		$query = " SELECT * FROM `#__osemsc_acl` "
		." WHERE rgt= lft +1 "
		;
		$db->setQuery($query);
		return oseDB::loadList($type);
	}

	public static function getNodePath($msc_id = 0)
	{
		$db = oseDB::instance();

		$query = " LOCK TABLE `#__osemsc_acl` AS node READ, `#__osemsc_acl` AS parent READ";
		$db->setQuery($query);
		$db->query();

		if(!empty($msc_id))
		{
			$where[] = "node.id =  {$msc_id}";
		}

		$where[] = 'node.lft BETWEEN parent.lft AND parent.rgt';

		$query = " SELECT parent.alias"
		." FROM `#__osemsc_acl` AS node, `#__osemsc_acl` AS parent"
		. $where
		." ORDER BY parent.lft "
		;
		$db->setQuery($query);

		$objs = oseDB::loadList('obj');

		oseDB::unlock();

		return $objs;
	}

	public static function getNodeDepth($msc_id = 0)
	{
		$db = oseDB::instance();

		$query = " LOCK TABLE `#__osemsc_acl` AS node READ, `#__osemsc_acl` AS parent READ";
		$db->setQuery($query);
		$db->query();

		if(!empty($msc_id))
		{
			$where[] = "node.id =  {$msc_id}";
		}
		else
		{
			return 0;
		}

		$where[] = 'node.lft BETWEEN parent.lft AND parent.rgt';

		$where = oseDB::implodeWhere($where);

		$query = " SELECT (COUNT(parent.id) - 1) AS depth "
		." FROM `#__osemsc_acl` AS node, `#__osemsc_acl` AS parent"
		. $where
		." GROUP BY node.id"
		." ORDER BY node.lft;"
		;
		$db->setQuery($query);

		$depth = $db->loadResult();

		oseDB::unlock();

		return $depth;

	}

	public static function getNodeLevel($msc_id = 0)
	{
		$db = oseDB::instance();

		if(!empty($msc_id))
		{
			$where[] = "id =  {$msc_id}";
		}

		$where = oseDB::implodeWhere($where);

		$query = " SELECT * FROM `#__osemsc_acl`"
		. $where
		;
		$db->setQuery($query);

		return $db->loadObject();

	}

	public static function getSubTreeDepth($parent_id,$depth = 999,$type = 'array')
	{
		$db = oseDB::instance();

		$query = " LOCK TABLE `#__osemsc_acl` AS node READ, "
		." `#__osemsc_acl` AS parent READ,"
		." `#__osemsc_acl` AS sub_parent READ"
		;
		$db->setQuery($query);
		//$db->query();

		$where[] = "node.lft BETWEEN parent.lft AND parent.rgt";
		$where[] = "node.lft BETWEEN sub_parent.lft AND sub_parent.rgt";
		$where[] = "sub_parent.id = sub_tree.id";

		$where = oseDB::implodeWhere($where);

		$query = " SELECT node.*, (COUNT(parent.id) - (sub_tree.depth + 1)) AS depth"
		." FROM `#__osemsc_acl` AS node,"
		." `#__osemsc_acl` AS parent,"
		." `#__osemsc_acl` AS sub_parent,"
		."("
		."		SELECT node.id, (COUNT(parent.id) - 1) AS depth"
		." 		FROM `#__osemsc_acl` AS node,"
		."		`#__osemsc_acl` AS parent"
		."		WHERE node.lft BETWEEN parent.lft AND parent.rgt"
		."		AND node.parent_id = {$parent_id}"
		."		GROUP BY node.id"
		."		ORDER BY node.lft"
		.") AS sub_tree"
		.$where
		." GROUP BY node.id"
		." HAVING depth <= {$depth}"
		." ORDER BY node.lft;"
		;

		$db->setQuery($query);
		$objs = oseDB::loadList($type);
		return $objs;
	}

	public static function reorder($parent_id = 0)
	{
		$db = oseDB::instance();

		$where = array();


		$where[] = "parent_id = {$parent_id}";


		$where = oseDB::implodeWhere($where);

		// Get the primary keys and ordering values for the selection.
		$query = " SELECT * FROM `#__osemsc_acl`"
		. $where
		." ORDER BY ordering ASC"
		;

		$db->setQuery($query);
		$rows = oseDB::loadList('obj');

		// Check for a database error.
		if ($db->getErrorNum())
		{
			$result = JText::sprintf('JLIB_DATABASE_ERROR_REORDER_FAILED', get_class($this), $db->getErrorMsg());
			oseExit($result);

			return false;
		}

		// Compact the ordering values.
		foreach ($rows as $i => $row)
		{
			// Make sure the ordering is a positive integer.
			if ($row->ordering >= 0)
			{
				// Only update rows that are necessary.
				if ($row->ordering != $i+1)
				{
					// Update the row ordering field.

					$query = " UPDATE `#__osemsc_acl` "
					." SET ordering={$i}+1"
					." WHERE id = {$row->id}"
					;
					$db->setQuery($query);

					// Check for a database error.
					if (!$db->query())
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	public static function orderChange($node,$ordering)
	{
		$db = oseDB::instance();

		$current = $node;

		oseDB::lock('#__osemsc_acl WRITE');

		$query = " SELECT * FROM `#__osemsc_acl`"
		." WHERE parent_id={$current->parent_id} AND ordering= {$ordering} "
		;
		$db->setQuery($query);
		// get the Current Msc paranet ID
		$obj = $db->loadObject();

		if($current->ordering > $ordering) // After
		{
			$query = " UPDATE `#__osemsc_acl` "
			." SET ordering= ordering+1 "
			." WHERE ordering >= {$ordering} AND ordering < {$current->ordering} "
			;
			$db->setQuery($query);
			if(!$db->query())
			{
				oseDB::unlock();
				return false;
			}

			$query = " UPDATE `#__osemsc_acl` "
			." SET ordering= {$ordering} "
			." WHERE id = {$current->id} "
			;
			$db->setQuery($query);
			if(!$db->query())
			{
				oseDB::unlock();
				return false;
			}


		}
		else
		{
			$query = " UPDATE `#__osemsc_acl` "
			." SET ordering= ordering-1 "
			." WHERE ordering <= {$ordering} AND ordering > {$current->ordering} "
			;
			$db->setQuery($query);
			if(!$db->query())
			{
				oseDB::unlock();
				return false;
			}

			$query = " UPDATE `#__osemsc_acl` "
			." SET ordering= {$ordering} "
			." WHERE id = {$current->id} "
			;
			$db->setQuery($query);
			if(!$db->query())
			{
				oseDB::unlock();
				return false;
			}

		}
		oseDB::unlock();
		return true;
	}

	/*
	 * $current  the item needed to move
	* $ordering the position
	*/
	public static function treeOrderChange($node,$ordering)
	{
		$db = oseDB::instance();

		$current = $node;

		$obj = self::getNodeByOrder($current->parent_id,$ordering,'obj');

		if(empty($obj))
		{
			oseExit('No Need To Change');
		}

		$currentTree = self::retrieveTree($current->id,'obj');

		oseDB::lock('#__osemsc_acl WRITE');


		$curWidth = $current->rgt - $current->lft + 1;


		$treeId = array();

		foreach($currentTree as $tree)
		{
			$treeId[] = $tree->id;
		}

		$treeId = implode(',',$treeId);

		//if($current->lft > $obj->lft) // After
		if($current->ordering > $ordering)
		{
			$objWidth = $current->lft - $obj->lft;

			$query = " UPDATE `#__osemsc_acl` "
			." SET lft= lft + {$curWidth},rgt= rgt + {$curWidth} "
			." WHERE rgt < {$current->lft} AND rgt > {$obj->lft} "
			;
			$db->setQuery($query);

			if(!$db->query())
			{
				oseDB::unlock();
				return false;
			}

			$query = " UPDATE `#__osemsc_acl` "
			." SET lft= lft - {$objWidth},rgt= rgt - {$objWidth} "
			." WHERE id IN ({$treeId}) "
			;
			$db->setQuery($query);
			if(!$db->query())
			{
				oseDB::unlock();
				return false;
			}

		}
		else
		{
			$objWidth = $obj->rgt - $current->rgt;

			$query = " UPDATE `#__osemsc_acl` "
			." SET lft= lft - {$curWidth},rgt= rgt - {$curWidth} "
			." WHERE lft > {$current->rgt} AND lft < {$obj->rgt} "
			;
			$db->setQuery($query);
			if(!$db->query())
			{
				oseDB::unlock();
				return false;
			}

			$query = " UPDATE `#__osemsc_acl` "
			." SET lft= lft + {$objWidth},rgt= rgt + {$objWidth} "
			." WHERE id IN ({$treeId}) "
			;
			$db->setQuery($query);

			//oseExit($db->_sql);

			if(!$db->query())
			{
				oseDB::unlock();
				return false;
			}

		}
		oseDB::unlock();
		return true;
	}

	// iterate
	public static function rebuildTree()
	{
		$db = oseDB::instance();
		// Clear to 0
		$query = " UPDATE `#__osemsc_acl`"
		." SET lft = 0, rgt = 0"
		." WHERE lft != 0"
		;
		$db->setQuery($query);
		$db->query();

		$query = " UPDATE `#__osemsc_acl`"
		." SET lft = 1, rgt = 2"
		." WHERE parent_id = 0 AND ordering = 1"
		;
		$db->setQuery($query);
		$db->query();

		$children = array();
		$objs = self::getTreeByParentId('obj');

		foreach ($objs as $v )
		{
			$pt = $v->parent_id;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
		//print_r($children);exit;
		self::treerecurse( 0, '', array(), $children);
		//return $children;
	}

	public static function getSiblingNode($node,$ordering,$type = 'array')
	{
		$db = oseDB::instance();

		oseDB::lock('#__osemsc_acl READ');

		$query = " SELECT * FROM `#__osemsc_acl`"
		." WHERE parent_id = {$node->parent_id}"
		." AND ordering = {$node->ordering} + ({$ordering})"
		;
		$db->setQuery($query);

		$item = oseDB::loadItem($type);

		oseDB::unlock();

		return $item;
	}

	public static function getParentNode($node,$type = 'array')
	{
		$db = oseDB::instance();


		oseDB::lock('#__osemsc_acl READ');

		$query = " SELECT * FROM `#__osemsc_acl`"
		." WHERE id = {$node->parent_id}"
		;
		$db->setQuery($query);

		$item = oseDB::loadItem($type);

		oseDB::unlock();

		return $item;
	}

	public static function getChildNodes($node,$type = 'array')
	{
		$db = oseDB::instance();

		oseDB::lock('#__osemsc_acl READ');

		$query = " SELECT * FROM `#__osemsc_acl`"
		." WHERE parent_id = {$node->id}"
		;
		$db->setQuery($query);

		$objs = oseDB::loadList($type);

		oseDB::unlock();

		return $objs;
	}

	public static function getNode($msc_id,$type = 'array')
	{
		$db = oseDB::instance();
		oseDB::lock(' #__osemsc_acl READ;');
		$query = " SELECT * FROM `#__osemsc_acl`"
				." WHERE id = {$msc_id} ";
		$db->setQuery($query);
		$obj = oseDB::loadItem($type);
		if(is_array($obj))
		{
			$obj['image'] = empty($obj['image'])?null:trim(JURI::root(),'/').$obj['image'];
		}else{
			$obj->image = empty($obj->image)?null:trim(JURI::root(),'/').$obj->image;
		}
		oseDB::unlock();
		return $obj;
	}
	public static function getNodeByOrder($parent_id,$ordering,$type = 'array')
	{
		$db = oseDB::instance();
		oseDB::lock('#__osemsc_acl READ');
		$query = " SELECT * FROM `#__osemsc_acl`"
			." WHERE parent_id = {$parent_id} AND ordering = {$ordering} ";
		$db->setQuery($query);
		$obj = oseDB::loadItem($type);
		oseDB::unlock();
		return $obj;
	}

	public static  function getTreeByParentId($type = 'array')
	{
		$db = oseDB::instance();

		$query = " SELECT * FROM `#__osemsc_acl` "
		." ORDER BY parent_id, ordering ASC"
		;
		$db->setQuery($query);

		$objs = oseDB::loadList($type);

		return $objs;

	}

	public static function treerecurse( $id, $indent, $list, &$children)
	{
		$db = oseDB::instance();

		if ( isset($children[$id]) )
		{
			$total = count($children[$id]);
			$i = 0;
			foreach ($children[$id] as $node)
			{
				$id = $node->id;

				if(isset($children[$node->id]))
				{
					if($node->ordering == 1 )
					{
						if( $node->parent_id != 0)
						{
							$parent = self::getParentNode($node,'obj');

							$query = " UPDATE `#__osemsc_acl` "
							." SET lft = {$parent->lft} +1"
							." WHERE id = {$node->id} "
							;
							$db->setQuery($query);
							$db->query();
						}
					}
					else
					{
						$preSibling = self::getSiblingNode($node,-1,'obj');

						$query = " UPDATE `#__osemsc_acl` "
						." SET lft = {$preSibling->rgt} +1"
						." WHERE id = {$node->id} "
						;
						$db->setQuery($query);
						$db->query();
					}

					self::TreeRecurse( $id, $indent, $list, $children);
				}
				else
				{
					if($node->ordering == 1)
					{
						if( $node->parent_id != 0)
						{
							$parent = self::getParentNode($node,'obj');

							$query = " UPDATE `#__osemsc_acl` "
							." SET lft = {$parent->lft} +1, rgt = {$parent->lft} +2"
							." WHERE id = {$node->id} "
							;
							$db->setQuery($query);
							$db->query();
						}
					}
					else
					{
						$preSibling = self::getSiblingNode($node,-1,'obj');
						//print_r($node);echo '<br>';
						$query = " UPDATE `#__osemsc_acl` "
						." SET lft = {$preSibling->rgt} +1,rgt = {$preSibling->rgt} +2"
						." WHERE id = {$node->id} "
						;
						$db->setQuery($query);
						$db->query();
					}
				}



				if( $i == ($total - 1) )
				{
					$node = self::getNode($node->id,'obj');
					$query = " UPDATE `#__osemsc_acl` "
					." SET rgt = {$node->rgt} + 1 "
					." WHERE id = {$node->parent_id} "
					;
					$db->setQuery($query);
					$db->query();
				}

				$i++;
			}
		}

		return true;

	}
}
?>
