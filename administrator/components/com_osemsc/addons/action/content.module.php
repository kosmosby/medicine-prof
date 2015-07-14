<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionContentModule extends oseMscAddon
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
		$moduletype = JRequest::getString('moduletype','');
		$assigned = JRequest::getString('assigned','');
		$position = JRequest::getString('position','');
		
		$where = array(); $filter = array();

		$filter[] = ' LEFT JOIN #__users AS u ON u.id = m.checked_out ';
		$filter[] = ' LEFT JOIN #__modules_menu AS mm ON mm.moduleid = m.id ';

		if ( $assigned ) 
		{
			$filter[] = 'LEFT JOIN #__templates_menu AS t ON t.menuid = mm.menuid';

			$where[] = 't.template = '.$db->Quote($assigned);
		}

		if ( $position ) 
		{
			$where[] = 'm.position = '.$db->Quote($position);
		}


		if ( $moduletype ) 
		{
			$where[] = 'm.module = '.$db->Quote($moduletype);
		}


		if ( $search ) 
		{
			$where[] = 'LOWER( m.title ) LIKE '.$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
		}

		// Added in V 4.4, menu access levels

		if (!class_exists("OSEJACCESS"))
		{
		    //require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_osemsc".DS."warehouse".DS."ExtAPIs".DS."jaccess.php");
		}

		//$access = OSEJACCESS::get_msc_aid($this->id);


		if (!empty($access))
		{
		     //$where[] = "m.access <= {$access} ";
		}			

		//Added in V 4.4, menu access levels

		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );

		$filter = ' ' . implode( ' ', $filter );
		
		$query = ' SELECT COUNT(*)'
				.' FROM `#__modules` AS m'
				. $filter
				. $where 
				//. $orderby
				;

		$db->setQuery( $query);
		
		$total = $db->loadResult();

		$query = ' SELECT m.*, u.name AS editor,  MIN(mm.menuid) AS pages'
				.' FROM `#__modules` AS m'
				. $filter
				. $where 
				.' GROUP BY m.id'
				//. $orderby
				;

		$db->setQuery( $query, $start, $limit );

		$rows = oseDB::loadList('obj');
		
		
		
		
		foreach($rows as $item)
		{
			$obj = oseRegistry::call('content')->getInstance('msc')->getItem('joomla','module',$item->id,'msc',$msc_id,null,'obj');
			
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
			
			$params = oseJson::decode(oseObject::getValue($obj,'params','{}'));
			
			if(!empty($params->time_length))
			{
				$item->time_length = $params->time_length;
				$item->time_unit = $params->time_unit;
			}
		}
		
		
		$result = array();
		$result['total'] = $total;
		$result['results'] = $rows;
		return $result;

	}
	
	function getMenuTypes()
	{
		$db = oseDB::instance();
		$menu_types = null;

		$query = "SELECT menutype FROM `#__menu_types`";


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
				$array[] = array('value'=>0,'text'=>JText::_( 'SELECT_POSITION' ));
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
				$array[] = array('value'=>0,'text'=>JText::_( 'SELECT_MODULL_TYPE' ));
				$filter = array_merge($array,oseDB::loadList());
			break;
			
			case('assigned'):
				$query = ' SELECT DISTINCT(template) AS text, template AS value'
						.' FROM #__templates_menu' 
						;
				$db->setQuery( $query );
		
				$array = array();
				$array[] = array('value'=>'','text'=>JText::_( 'SELECT_TEMPLATE' ));
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
		
		$module_ids = JRequest::getVar('module_ids',array());
		
		$newStatus = JRequest::getInt('status',0);
		
		$timeLength= JRequest :: getVar('time_length', array());
		$timeUnit= JRequest :: getVar('time_unit', array());
		
		foreach($module_ids as $key => $module_id)
		{
			$content = oseRegistry::call('content')->getInstance('msc');
			$item = $content->getItem('joomla','module',$module_id,'msc',$msc_id, '','obj');
			
			$ItemParams = array();
			$ItemParams['time_length'] = $timeLength[$key];
			$ItemParams['time_unit'] = $timeUnit[$key];
			
			if(empty($item))
			{
				$updated = $content->insert('joomla','module',$module_id,'msc',$msc_id, $newStatus,$ItemParams);
				$db = oseDB::instance();
				
				if(!$updated)
				{
					$result = array();
					$result['success'] = false;
					$result['title'] = JText::_('ERROR');;
					$result['content'] = JText::_('ERROR');;
					return $result;
				}
			}
			else
			{
				$status = $item->status;
				
				if($status == $newStatus)
				{
					$updated = $content->update($item, $newStatus,$ItemParams);
				
					if(!$updated)
					{
						$result = array();
						$result['success'] = false;
						$result['title'] = JText::_('ERROR');;
						$result['content'] = JText::_('ERROR');;
						return $result;
					}
					
				}
				else
				{
					$updated = $content->update($item, $newStatus,$ItemParams);
				
					if(!$updated)
					{
						$result = array();
						$result['success'] = false;
						$result['title'] = JText::_('ERROR');;
						$result['content'] = JText::_('ERROR');;
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