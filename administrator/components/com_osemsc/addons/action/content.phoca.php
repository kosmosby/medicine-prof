<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionContentPhoca extends oseMscAddon
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
		$section = JRequest::getInt('section',0);
		
		
		$where = array();
		
		$where[] = "m.published = 1";

		if ( $section ) 
		{
			$where[] = 'section ='.$db->Quote($section);
		}


		if ( $search ) 
		{
			$where[] = 'LOWER( m.title ) LIKE '.$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
		}

		//Added in V 4.4, menu access levels

		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );


		
		$query = ' SELECT COUNT(*)'
				.' FROM `#__phocadownload_categories` AS m'
				. $where 
				//. $orderby
				;

		$db->setQuery( $query);
		
		$total = $db->loadResult();

		$query = ' SELECT *'
				.' FROM `#__phocadownload_categories` AS m'
				. $where 
				;

		$db->setQuery( $query, $start, $limit );

		$rows = oseDB::loadList('obj');
		
		
		
		
		foreach($rows as $item)
		{
			$obj = oseRegistry::call('content')->getInstance('msc')->getItem('phoca','category',$item->id,'msc',$msc_id,null,'obj');
			
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
		
		$msc_id = JRequest::getInt('msc_id',0);
		
		$cat_ids = JRequest::getVar('cat_ids',array());
		
		$newStatus = JRequest::getInt('status',0);
		
		foreach($cat_ids as $cat_id)
		{
			$content = oseRegistry::call('content')->getInstance('msc');
			$item = $content->getItem('phoca','category',$cat_id,'msc',$msc_id, '','obj');
			
			if(empty($item))
			{
				$updated = $content->insert('phoca','category',$cat_id,'msc',$msc_id, $newStatus);
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
}
?>