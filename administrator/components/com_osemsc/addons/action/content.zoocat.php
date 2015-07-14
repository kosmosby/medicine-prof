<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionContentZoocat extends oseMscAddon
{
	public static function save($params)
	{


	}

	public static function delete($params)
	{

	}

	function & getArtList() 
	{
		$db= oseDB :: instance();
		$cat_id = JRequest :: getInt('cat_id', 0);
		$search = JRequest :: getString('search', null);
		$search = JString :: strtolower($search);
		
		$msc_id= JRequest :: getInt('msc_id', 0);
		
		$start= JRequest :: getInt('start', 0);
		$limit= JRequest :: getInt('limit', 20);
		
		$where= array();
		
		if($cat_id) {
			$where[]= 'c.category_id = '.$cat_id;
		}
		$where = oseDB::implodeWhere($where);
		
		$query = " SELECT COUNT(*)"
				." FROM `#__zoo_category_item` AS c "
				. $where
				//." ORDER BY c.title"
				;
		$db->setQuery($query);
		$total= $db->loadResult();
		
		$query = " SELECT i.id,i.name"
				." FROM `#__zoo_item` AS i "
				." RIGHT JOIN `#__zoo_category_item` AS c"
				." ON i.`id` = c.`item_id`"
				. $where
				." ORDER BY i.id"
				;
		$db->setQuery($query,$start,$limit);
		$rows= oseDB :: loadList('obj');
		
		foreach($rows as $item) 
		{
			$item->treename = $item->name;
			$obj= oseRegistry :: call('content')->getInstance('msc')->getItem('zoo', 'article', $item->id, 'msc', $msc_id, null, 'obj');
			$controlled= empty($obj) ? 0 : $obj->status;
			if($controlled == '1') {
				$item->controlled= JText :: _('SHOW_TO_MEMBERS');
			}
			elseif($controlled == '-1') {
				$item->controlled= JText :: _('HIDE_TO_MEMBERS');
			} else {
				$item->controlled= JText :: _('SHOW_TO_ALL');
			}
			$item->leaf= true;
			$item->iconCls= 'task';
			$item->checked= false;
			//$item->controlled = $controlled;
		}
		
		$result= array();
		$result['total']= $total;
		$result['results']= $rows;
		
		return $result;
	}
	
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
		$appid = JRequest::getInt('appid',0);
		$where = array();
		$where[] = ' zc.`published` = 1';
		if ( $appid ) 
		{
			$where['appid'] = 'zc.`application_id` ='.$db->Quote($appid);
		}
		if($search)
		{
			$searchQuery = ' LOWER(zc.`name`) LIKE '.$db->Quote('%'.$search.'%') ;
			$Swhere[] =  $searchQuery;
			$Swhere = ( count( $Swhere ) ? ' WHERE (' . implode( ') AND (', $Swhere ) . ')' : '' );
			$query = ' SELECT zc.id '
					.' FROM `#__zoo_category` AS zc'
					. $Swhere
					.' ORDER BY zc.`id`';
			$db->setQuery($query);
			//oseExit($db->_sql);
			$search_rows = $db->loadResultArray();
				
				
		}

		$access = oseMscJaccess::get_msc_aid(25);


		if (!empty($access))
		{
			//$where[] = "m.access <= {$access} ";
		}
		//Added in V 4.4, menu access levels

		// Generate where query
		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );
		$query = ' SELECT zc.*'
		.' FROM `#__zoo_category` AS zc'
		. $where
		.' ORDER BY zc.`id`';
		
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
			$obj = oseRegistry::call('content')->getInstance('msc')->getItem('zoo','category',$item->id,'msc',$msc_id,null,'obj');
			$item->type = empty($obj->content_type)?'category':$obj->content_type;
			$controlled = empty($obj)?0:$obj->status;
				
			if($controlled == '1')
			{
				$item->controlled = JText :: _('SHOW_TO_MEMBERS');
			}
			elseif($controlled == '-1')
			{
				$item->controlled = JText :: _('HIDE_TO_MEMBERS');
			}
			else
			{
				$item->controlled = JText :: _('SHOW_TO_ALL');
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

	function changeStatus() 
	{
		$db= oseDB :: instance();
		
		$msc_id= JRequest :: getInt('msc_id', 0);
		
		$content_ids= JRequest :: getVar('jc_ids', array());
		$timeLength= JRequest :: getVar('time_length', array());
		$timeUnit= JRequest :: getVar('time_unit', array());
		$content_type = JRequest :: getCmd('content_type', null);
		//oseExit($content_ids);
		$newStatus= JRequest :: getInt('status', 0);
		$result= array();
		$result['success']= true;
		$result['title']= 'Done';
		$result['content']= 'Successfully';
		if(empty($content_ids)) {
			return $result;
		}
		foreach($content_ids as $key => $content_id) {
			//$node= explode('-', $content_id);
			//$content_type= $node[0];
			//$content_id= $node[1];
			switch($content_type) {
				case('sec') :
					//$updated= $this->changeSecStatus($content_id, $msc_id, $newStatus);
					break;
				case('cat') :
					$ItemParams = array();
					//$ItemParams['time_length'] = $timeLength[$key];
					//$ItemParams['time_unit'] = 'week';//$timeUnit[$key];
					$updated= $this->changeCatStatus($content_id, $msc_id, $newStatus,$ItemParams);
					break;
				case('art') :
					$updated= $this->changeArtStatus($content_id, $msc_id, $newStatus);
					break;
				default :
					$updated= array('success' => true);
					break;
			}
		}
		if(!$updated['success']) {
			$result['success']= false;
			$result['title']= JText::_('ERROR');
			$result['content']= JText::_('ERROR');
		}
		return $result;
	}
	
	function changeCatStatus($content_id, $msc_id, $newStatus,$params = array()) 
	{
		$db = oseDB::instance();

		$content= oseRegistry :: call('content')->getInstance('msc');

		$query = " SELECT zc.*"
				." FROM `#__zoo_category` AS zc";
		$db->setQuery($query);		
		$rows = $db->loadObjectList();
		$children = array();
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
		$contentIds = self::getSubCats($content_id, array(), $children);
		array_push($contentIds,$content_id);//print_r($contentIds);exit;
		foreach($contentIds as  $content_id)
		{
			$item= $content->getItem('zoo', 'category', $content_id, 'msc', $msc_id, '', 'obj');
			if(empty($item)) 
			{
				$updated= $content->insert('zoo', 'category', $content_id, 'msc', $msc_id, $newStatus);
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
			
			$query= ' SELECT * FROM `#__zoo_category_item` '.' WHERE `category_id` = '.$content_id;
			$db->setQuery($query);
			$objs= oseDB :: loadList('obj');
			foreach($objs as $obj) {
				$updated= $this->changeArtStatus($obj->item_id, $msc_id, $newStatus,$params);
				if(!$updated) {
					$result= array();
					$result['success']= false;
					$result['title']= JText::_('ERROR');
					$result['content']= JText::_('ERROR');
					return $result;
				}
			}
		}	
				
		$result = array();
		$result['success'] = true;
		$result['title']= JText::_('DONE');
		$result['content']= JText::_('SUCCESSFULLY');
		return $result;
	}
	
	function changeArtStatus($content_id, $msc_id, $newStatus)
	{
		$result= array();
		$result['success']= true;
		$result['title']= 'Done';
		$result['content']= 'Successfully';
		$db= oseDB :: instance();
		$content= oseRegistry :: call('content')->getInstance('msc');
		$item= $content->getItem('zoo', 'article', $content_id, 'msc', $msc_id, '', 'obj');
		if(empty($item)) {
			$updated= $content->insert('zoo', 'article', $content_id, 'msc', $msc_id, $newStatus);
			if(!$updated) {
				$result= array();
				$result['success']= false;
				$result['title']= 'Error';
				$result['content']= 'Error';
				return $result;
			}
		} else {
			$status= $item->status;
			//if($status != $newStatus) {
				$updated= $content->update($item, $newStatus);
				if(!$updated) {
					$result= array();
					$result['success']= false;
					$result['title']= JText::_('ERROR');
					$result['content']= JText::_('ERROR');
					return $result;
				}
			//}
		}
		return $result;
	}
	
	function getApps()
	{
		$db = oseDB::instance();
		$query = " SELECT id,name FROM `#__zoo_application`"
				." ORDER BY id ASC"
				;
		$db->setQuery($query);
		$cats = oseDB::loadList();
		$result = array();
		$result['total'] = count($cats);
		$result['results'] = $cats;
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