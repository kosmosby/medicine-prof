<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionContentRokdownload extends oseMscAddon
{
	public static function save($params)
	{
		
		
	}
	
	public static function delete($params)
	{
		
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
		
		$where = array();
		$where[] = 'rok.`folder` = 1';
		$where[] = 'rok.`published` = 1';
		if($search)
		{
			$searchQuery = ' LOWER(rok.`name`) LIKE '.$db->Quote('%'.$search.'%') ;
			$where[] =  $searchQuery;
		}
		
		$access = oseMscJaccess::get_msc_aid(25);


		if (!empty($access))
		{
     		//$where[] = "m.access <= {$access} ";
		}			
		//Added in V 4.4, menu access levels

		// Generate where query

		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );
		
		$query = ' SELECT rok.*' 
				.' FROM `#__rokdownloads` AS rok' 
				. $where
				. ' ORDER BY rok.id'
				;

		$db->setQuery( $query );
//oseExit($db->_sql);
		$rows = oseDB::loadList('obj');
		
		$total = count($rows);

		// slice out elements based on limits
		$list = array_slice( $rows, $start, $limit );
		

		foreach($list as $item)
		{
			$obj = oseRegistry::call('content')->getInstance('msc')->getItem('rokdownload','category',$item->id,'msc',$msc_id,null,'obj');
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

	
	function changeStatus()
	{
		$db = oseDB::instance();
		
		$msc_id = JRequest::getInt('msc_id',0);
		
		$catids = JRequest::getVar('catids',array());
		
		$newStatus = JRequest::getInt('status',0);
		
		$content= oseRegistry :: call('content')->getInstance('msc');
		$contentIds = array();
		foreach($catids as $catid)
		{
			
			$contentIds[] = $catid;
			$query = "SELECT * FROM `#__rokdownloads` WHERE `id` = '{$catid}'";
			$db->setQuery($query);
			$node = $db->loadObject();
			$query = "SELECT * FROM `#__rokdownloads` WHERE lft >= '{$node->lft}' and rgt <= '{$node->rgt}' AND `folder` = '1'";
			$db->setQuery($query);
			$objs = $db->loadObjectList();
			
			foreach($objs as  $obj)
			{
				$contentIds[] = $obj->id;
			} 
		}
		$contentIds = array_unique($contentIds);
		foreach($contentIds as  $content_id)
		{
			$item= $content->getItem('rokdownload', 'category', $content_id, 'msc', $msc_id, '', 'obj');
			if(empty($item)) {
				$updated= $content->insert('rokdownload', 'category', $content_id, 'msc', $msc_id, $newStatus);
				if(!$updated) {
					$result= array();
					$result['success']= false;
					$result['title']= JText::_('ERROR');
					$result['content']= JText::_('ERROR');
					return $result;
				}
			} else {
				$status= $item->status;
				if($status != $newStatus) {
					$updated= $content->update($item, $newStatus);
					if(!$updated) {
						$result= array();
						$result['success']= false;
						$result['title']= JText::_('ERROR');
						$result['content']= JText::_('ERROR');
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
}
?>