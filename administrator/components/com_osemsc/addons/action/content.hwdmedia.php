<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionContentHwdmedia extends oseMscAddon
{

	function &getList()
	{
		$db = oseDB::instance();

		$search	= JRequest::getString('search',null);
		$search	= JString::strtolower( $search );


		$msc_id = JRequest::getInt('msc_id',0);
		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',20);
		$levellimit = JRequest::getInt('levellimit',10);
		$levellimit = empty($levellimit)?10:$levellimit;
		$where = array();
		$where[] = ' hwd.`published` = 1';
		$where[] = ' hwd.`extension` = \'com_hwdmediashare\'';
		if($search)
		{
			$searchQuery = ' LOWER(hwd.`title`) LIKE '.$db->Quote('%'.$search.'%') ;
			$where[] =  $searchQuery;
			$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );
			$query = ' SELECT hwd.id '
			.' FROM `#__categories` AS hwd'
			. $where
			.' ORDER BY hwd.`id`';
			$db->setQuery($query);
			$search_rows = $db->loadResultArray();


		}

		// Generate where query
		$query = ' SELECT hwd.*'
		.' FROM `#__categories` AS hwd'
		.' WHERE hwd.`published` = 1'
		.' AND hwd.`extension` = \'com_hwdmediashare\''
		. ' ORDER BY hwd.`id`'
		;

		$db->setQuery( $query );//echo $query;exit;
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
				$pt = $v->parent_id;

				$list = @$children[$pt] ? $children[$pt] : array();

				array_push( $list, $v );

				$children[$pt] = $list;
			}
		}
//print_r($children);exit;
		// second pass - get an indent list of the items

		$list = self::treerecurse(1, '', array(), $children, max(0, $levellimit - 1));
		// eventually only pick out the searched items
	 if ($search)
	 {
	 	$list1 = array();
	 	$search_rows = (count($search_rows) > 0)?$search_rows:array();
	 	foreach ($search_rows as $sid)
	 	{
	 		foreach ($list as $item)
	 		{
	 			if ($item->id == $sid)
	 			{
	 				$list1[] = $item;
	 			}
	 		}
	 	}
	 	// replace full list with found items
	 	$list = $list1;
	 }
		// slice out elements based on limits
		$list = array_slice( $list, $start, $limit );


		foreach($list as $item)
		{
			$obj = oseRegistry::call('content')->getInstance('msc')->getItem('hwdmedia','category',$item->id,'msc',$msc_id,null,'obj');
			$item->type = empty($obj->content_type)?'category':$obj->content_type;
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
				if ($v->parent_id == 0)
				{
					$txt = $v->title;
				} else
				{
					$txt = $pre . $v->title;
				}
				$pt = $v->parent_id;
				$list[$id] = $v;
				$list[$id]->treename = "$indent$txt";
				$list[$id]->children = count(@$children[$id]);
				$list = $this->TreeRecurse($id, $indent . $spacer, $list, $children, $maxlevel, $level + 1, $type);
			}
		}
		return $list;
	}

	function changeStatus()
	{
		$db = oseDB::instance();
		$children = array();
		$msc_id = JRequest::getInt('msc_id',0);

		$catids = JRequest::getVar('catids',array());

		$newStatus = JRequest::getInt('status',0);

		$content= oseRegistry :: call('content')->getInstance('msc');

		$query = ' SELECT hwd.*'
				.' FROM `#__categories` AS hwd'
				.' WHERE hwd.`published` = 1'
				.' AND hwd.`extension` = \'com_hwdmediashare\''
				. ' ORDER BY hwd.`id`'
		;

		$db->setQuery( $query );
		//oseExit($db->_sql);
		$rows = oseDB::loadList('obj');
		if (!empty($rows))
		{
			foreach ($rows as $v )
			{
				$pt = $v->parent_id ;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push( $list, $v );
				$children[$pt] = $list;
			}
		}
		foreach($catids as $catid)
		{
			$contentIds = self::getSubCats($catid, array(), $children);
			array_push($contentIds,$catid);
			$contentIds = array_unique($contentIds);
			foreach($contentIds as  $content_id)
			{
				$item= $content->getItem('hwdmedia', 'category', $content_id, 'msc', $msc_id, '', 'obj');
				if(empty($item))
				{
					$updated= $content->insert('hwdmedia', 'category', $content_id, 'msc', $msc_id, $newStatus);
					if(!$updated)
					{
						$result= array();
						$result['success']= false;
						$result['title']= JText::_('ERROR');
						$result['content']= JText::_('ERROR');
						return $result;
					}
				} else {
					$status= $item->status;
					if($status != $newStatus)
					{
						$updated= $content->update($item, $newStatus);
						if(!$updated)
						{
							$result= array();
							$result['success']= false;
							$result['title']= JText::_('ERROR');
							$result['content']= JText::_('ERROR');
							return $result;
						}
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

	function getSubCats($id, $list, &$children)
	{
		if (@$children[$id])
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->id;
				$list[] = $id;
				$list = $this->getSubCats($id, $list, $children);
			}
		}
		return $list;
	}
}
?>