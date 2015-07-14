<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionContentAriquizcat extends oseMscAddon
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

		$where = array();

		if($search)
		{
			$searchQuery = ' LOWER(aqc.`CategoryName`) LIKE '.$db->Quote('%'.$search.'%') ;
			$where[] =  $searchQuery;
		}
		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );
		// Generate where query

		$query = ' SELECT aqc.CategoryId AS id, aqc.CategoryName AS name'
				.' FROM `#__ariquizcategory` AS aqc'
				. $where
				.' ORDER BY aqc.`CategoryId`'
		;

		$db->setQuery( $query );
		//oseExit($db->_sql);
		$rows = oseDB::loadList('obj');

		$total = count($rows);

		// slice out elements based on limits
		$list = array_slice( $rows, $start, $limit );


		foreach($list as $item)
		{
			$obj = oseRegistry::call('content')->getInstance('msc')->getItem('ariquiz','category',$item->id,'msc',$msc_id,null,'obj');
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

		foreach($catids as $catid)
		{

			$item= $content->getItem('ariquiz', 'category', $catid, 'msc', $msc_id, '', 'obj');
			if(empty($item)) 
			{
				$updated= $content->insert('ariquiz', 'category', $catid, 'msc', $msc_id, $newStatus);
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
		$result = array();
		$result['success'] = true;
		$result['title'] = JText::_('DONE');
		$result['content'] = JText::_('SUCCESSFULLY');
		return $result;
	}
	
	
}
?>