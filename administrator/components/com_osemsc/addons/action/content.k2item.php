<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionContentK2item extends oseMscAddon
{
	public static function save($params)
	{
		
		
	}
	
	public static function delete($params)
	{
		
	}
	
	function &getItems()
	{
		$db = oseDB::instance();
		
		$search	= JRequest::getString('search',null);
		$search	= JString::strtolower( $search );
		
		
		$msc_id = JRequest::getInt('msc_id',0);
		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',20);
		$catid = JRequest::getInt('catid',0);
		
		
		$where = array();
		
		$where[] = "m.published = 1";

		if ( $catid ) 
		{
			$where['catid'] = 'catid ='.$db->Quote($catid);
		}


		if ( $search ) 
		{
			$where[] = 'LOWER( m.title ) LIKE '.$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
			unset($where['catid']);
		}

		//Added in V 4.4, menu access levels

		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );


		
		$query = ' SELECT COUNT(*)'
				.' FROM `#__k2_items` AS m'
				. $where 
				//. $orderby
				;

		$db->setQuery( $query);
		
		$total = $db->loadResult();

		$query = ' SELECT *'
				.' FROM `#__k2_items` AS m'
				. $where 
				;

		$db->setQuery( $query, $start, $limit );

		$rows = oseDB::loadList('obj');
		
		
		foreach($rows as $item)
		{
			$obj = oseRegistry::call('content')->getInstance('msc')->getItem('k2','article',$item->id,'msc',$msc_id,null,'obj');
			//$item->type = $obj->content_type;
			$controlled = empty($obj)?0:$obj->status;
			
			if($controlled == '1')
			{
				$item->controlled = JText::_('SHOW_TO_MEMBERS');
			}
			elseif($controlled == '-1')
			{
				$item->controlled = JText::_('HIDE_TO_MEMBERS');
			}
			else
			{
				$item->controlled = JText::_('SHOW_TO_ALL');
			}
		}

		$result = array();
		$result['total'] = $total;
		$result['results'] = $rows;
		return $result;

	}
	
	function getCats1()
	{
		$db = oseDB::instance();
	
		$query = " SELECT id,name FROM `#__k2_categories`"
				." WHERE `published` = '1'"
				." ORDER BY ordering ASC"
				;


		$db->setQuery($query);

		$cats = oseDB::loadList();
		
		$result = array();
		$result['total'] = count($cats);
		$result['results'] = $cats;
		return $result;

	}
	
	
	function changeStatus()
	{
		$db = oseDB::instance();
		
		$msc_id = JRequest::getInt('msc_id',0);
		
		$item_ids = JRequest::getVar('item_ids',array());
		
		$newStatus = JRequest::getInt('status',0);
		
		if(empty($item_ids[0]))
		{
			$result = array();
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] =  JText::_('PLEASE_CHOOSE_A_ITEM_FIRST');
			return $result;
		}

		foreach($item_ids as $item_id)
		{
			$content = oseRegistry::call('content')->getInstance('msc');
			$item = $content->getItem('k2','article',$item_id,'msc',$msc_id, '','obj');
			
			if(empty($item))
			{
				$updated = $content->insert('k2','article',$item_id,'msc',$msc_id, $newStatus);
				$db = oseDB::instance();
				
				if(!$updated)
				{
					$result = array();
					$result['success'] = false;
					$result['title'] = JText::_('ERROR');
					$result['content'] = JText::_('ERROR');
					return $result;
				}
			}
			else
			{
				$status = $item->status;
				
				if($status == $newStatus)
				{
					continue;
					
				}
				else
				{
					$updated = $content->update($item, $newStatus);
				
					if(!$updated)
					{
						$result = array();
						$result['success'] = false;
						$result['title'] = JText::_('ERROR');
						$result['content'] = JText::_('ERROR');
						return $result;
					}
				}
			}
		}
		
		$result = array();
		$result['success'] = true;
		$result['title'] = JText::_('DONE');
		$result['content'] = JText::_('SUCCESSFULLY');
		return $result;
	}
	
	function getCats()
	{
		$db = oseDB::instance();


		
		$levellimit = JRequest::getInt('levellimit',10);
		$levellimit = empty($levellimit)?10:$levellimit;
		$where = array();
		$where[] = ' k2c.`published` = 1';
		
		// Generate where query

		$query = ' SELECT k2c.id,k2c.name,k2c.parent'
				.' FROM `#__k2_categories` AS k2c'
				.' WHERE k2c.`published` = 1'
				. ' ORDER BY k2c.`id`'
		;

		$db->setQuery( $query );
		//oseExit($db->_sql);
		$rows = oseDB::loadList('obj');

		$total = count($rows);

		// establish the hierarchy of the cats

		$children = array();

		// first pass - collect children


		if (!empty($rows))
		{
			foreach ($rows as $v )
			{
				$pt = $v->parent;

				$list = @$children[$pt] ? $children[$pt] : array();

				array_push( $list, $v );

				$children[$pt] = $list;
			}
		}

		// second pass - get an indent list of the items

		$list = self::treerecurse(0, '', array(), $children, max(0, $levellimit - 1));
		
		$items = array_values($list);

		$result = array();
		$result['total'] = $total;
		$result['results'] = $items;
		return $result;

	}

	function treerecurse($id, $indent, $list, &$children, $maxlevel = 9999, $level = 0, $type = 1)
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->id;
				if ($type)
				{
					$pre = '<sup>|_</sup>&nbsp;';
					$spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				} else
				{
					$pre = '- ';
					$spacer = '&nbsp;&nbsp;';
				}
				if ($v->parent == 0)
				{
					$txt = $v->name;
				} else
				{
					$txt = $pre . $v->name;
				}
				$pt = $v->parent;
				$list[$id] = $v;
				$list[$id]->treename = "$indent$txt";
				$list[$id]->children = count(@$children[$id]);
				$list = $this->TreeRecurse($id, $indent . $spacer, $list, $children, $maxlevel, $level + 1, $type);
			}
		}
		return $list;
	}
}
?>