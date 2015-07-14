<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionContentOsedownload extends oseMscAddon
{
	function &getList()
	{
		$db = oseDB::instance();
		
		$search	= JRequest::getString('search',null);
		$search	= JString::strtolower( $search );
		
		
		$msc_id = JRequest::getInt('msc_id',0);
		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',20);
		
		$where = array();
		
		$where[] = "m.published = 1";

		if($search)
		{
			$searchQuery = ' LOWER(m.`title`) LIKE '.$db->Quote('%'.$search.'%') ;
			$where[] =  $searchQuery;
			$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );
			$query = ' SELECT m.id '
			.' FROM `#__ose_download_categories` AS m'
			. $where
			.' ORDER BY m.`id`';
			$db->setQuery($query);
			$search_rows = $db->loadResultArray();
		}

		$query = ' SELECT *'
				.' FROM `#__ose_download_categories` AS m'
				.' WHERE m.`published` = 1'
				;
		$db->setQuery( $query);
		$rows = oseDB::loadList('obj');
		
		$total = count($rows);
		
		$children = array();
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

		$list = self::treerecurse(0, '', array(), $children, max(0, $limit - 1));

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
		 
		 	$list = $list1;
		}
		
		$list = array_slice( $list, $start, $limit );
		
		foreach($list as $item)
		{
			$obj = oseRegistry::call('content')->getInstance('msc')->getItem('osedownload','category',$item->id,'msc',$msc_id,null,'obj');
			
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
	
	function getSections()
	{
		$db = oseDB::instance();
		$menu_types = null;

		$query = "SELECT id,title FROM `#__phocadownload_sections`";


		$db->setQuery($query);

		$menu_types = oseDB::loadList();
		
		$result = array();
		$result['total'] = count($menu_types);
		$result['results'] = $menu_types;
		return $result;

	}
	
	function getFilterTypes()
	{
		$db = oseDB::instance();
		
		$lists = array();
		
		$type = JRequest::getString('filter_type');
		
		switch($type)
		{
			case('position'):
				$query = 'SELECT m.position AS value, m.position AS text'
						.' FROM #__modules as m'
						.' GROUP BY m.position'
						.' ORDER BY m.position'
						;

				$db->setQuery( $query );
				$array = array();
				$array[] = array('value'=>0,'text'=>JText::_( '-- Select Position --' ));
				$filter = array_merge($array,oseDB::loadList());
			break;
			
			case('type'):
				$query = 'SELECT module AS value, module AS text'
						.' FROM #__modules'
						.' GROUP BY module'
						.' ORDER BY module'
						;
		
				$db->setQuery( $query );
				$array = array();
				$array[] = array('value'=>0,'text'=>JText::_( '-- Select Modull Type --' ));
				$filter = array_merge($array,oseDB::loadList());
			break;
			
			case('assigned'):
				$query = ' SELECT DISTINCT(template) AS text, template AS value'
						.' FROM #__templates_menu' 
						;
				$db->setQuery( $query );
		
				$array = array();
				$array[] = array('value'=>'','text'=>JText::_( '-- Select Template --' ));
				$filter = array_merge($array,oseDB::loadList());
			break;
		}

		
		$result = array();
		$result['total'] = count($filter);
		$result['results'] = $filter;
		return $result;
		
	}
	
	function changeStatus()
	{
		$db = oseDB::instance();
		$children = array();
		$msc_id = JRequest::getInt('msc_id',0);

		$catids = JRequest::getVar('cat_ids',array());

		$newStatus = JRequest::getInt('status',0);

		$content= oseRegistry :: call('content')->getInstance('msc');

		$query = ' SELECT m.*'
				.' FROM `#__ose_download_categories` AS m'
				.' WHERE m.`published` = 1'
				. ' ORDER BY m.`id`'
				;

		$db->setQuery( $query );
		//oseExit($db->_sql);
		$rows = oseDB::loadList('obj');
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
		
		foreach($catids as $catid)
		{
			$contentIds = self::getSubCats($catid, array(), $children);
			array_push($contentIds,$catid);
			$contentIds = array_unique($contentIds);
			foreach($contentIds as  $content_id)
			{
				$item= $content->getItem('osedownload', 'category', $content_id, 'msc', $msc_id, '', 'obj');
				if(empty($item))
				{
					$updated= $content->insert('osedownload', 'category', $content_id, 'msc', $msc_id, $newStatus);
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
		$result['content'] = JText::_(JText::_('SUCCESSFULLY'));
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