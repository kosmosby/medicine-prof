<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionContentMenu extends oseMscAddon
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
		
		//oseRegistry::call('content')->getInstance('Msc')->getMemberRestrictedContent('joomla','menu','63');
		
		//oseExit($db->_sql);
		
		
		$search	= JRequest::getString('search',null);
		$search	= JString::strtolower( $search );
		
		
		$msc_id = JRequest::getInt('msc_id',0);
		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',20);
		$menutype = JRequest::getString('menutype','mainmenu');
		
		$where = array();
		
		//$where = array_merge($where,oseJSON::generateQueryWhere());
		
		// Added in V 4.4, menu access levels
		
		$where[] = 'm.menutype = '.$db->Quote($menutype);
		$where[] = 'm.published != -2';
		
		if($search)
		{
			$searchQuery = ' LOWER(m.name) LIKE '.$db->Quote('%'.$search.'%')
						  .' OR LOWER(m.alias) LIKE '.$db->Quote('%'.$search.'%')
						  ;
			$where[] =  $searchQuery;
		}
		
		//$access = oseMscJaccess::get_msc_aid(25);
		
		//Added in V 4.4, menu access levels

		// Generate where query

		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );
		
		$query = 'SELECT COUNT(*)' 
				.' FROM `#__menu` AS m' 
				. $where
				;

		$db->setQuery( $query);
		$total = $db->loadResult();
		
		$query = 'SELECT m.*,  com.name AS com_name' 
				.' FROM `#__menu` AS m' 
				.' LEFT JOIN `#__extensions` AS com ON com.extension_id = m.component_id' 
				. $where
				. ' ORDER BY m.lft'
				;

		$db->setQuery( $query, $start, $limit );
//oseExit($db->getQuery());
		$rows = oseDB::loadList('obj');
		
		/*
		$total = count($rows);
		
		// establish the hierarchy of the menu

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

		// second pass - get an indent list of the items

		$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, max( 0, 99 ) );

		// eventually only pick out the searched items

		// slice out elements based on limits
		$list = array_slice( $list, $start, $limit );
		*/
		
		$ordering = array();
		$lang 		= JFactory::getLanguage();
		// Preprocess the list of items to find ordering divisions.
		foreach ($rows as $item) {
			$ordering[$item->parent_id][] = $item->id;

			// item type text
			switch ($item->type) {
				case 'url':
					$value = JText::_('COM_MENUS_TYPE_EXTERNAL_URL');
					break;

				case 'alias':
					$value = JText::_('COM_MENUS_TYPE_ALIAS');
					break;

				case 'separator':
					$value = JText::_('COM_MENUS_TYPE_SEPARATOR');
					break;

				case 'component':
				default:
					// load language
						$lang->load($item->com_name.'.sys', JPATH_ADMINISTRATOR, null, false, false)
					||	$lang->load($item->com_name.'.sys', JPATH_ADMINISTRATOR.'/components/'.$item->com_name, null, false, false)
					||	$lang->load($item->com_name.'.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
					||	$lang->load($item->com_name.'.sys', JPATH_ADMINISTRATOR.'/components/'.$item->com_name, $lang->getDefault(), false, false);

					if (!empty($item->com_name)) {
						$value	= JText::_($item->com_name);
						$vars	= null;

						parse_str($item->link, $vars);
						if (isset($vars['view'])) {
							// Attempt to load the view xml file.
							$file = JPATH_SITE.'/components/'.$item->com_name.'/views/'.$vars['view'].'/metadata.xml';
							if (JFile::exists($file) && $xml = simplexml_load_file($file)) {
								// Look for the first view node off of the root node.
								if ($view = $xml->xpath('view[1]')) {
									if (!empty($view[0]['title'])) {
										$vars['layout'] = isset($vars['layout']) ? $vars['layout'] : 'default';

										// Attempt to load the layout xml file.
										// If Alternative Menu Item, get template folder for layout file
										if (strpos($vars['layout'], ':') > 0)
										{
											// Use template folder for layout file
											$temp = explode(':', $vars['layout']);
											$file = JPATH_SITE.'/templates/'.$temp[0].'/html/'.$item->com_name.'/'.$vars['view'].'/'.$temp[1].'.xml';
											// Load template language file
											$lang->load('tpl_'.$temp[0].'.sys', JPATH_SITE, null, false, false)
											||	$lang->load('tpl_'.$temp[0].'.sys', JPATH_SITE.'/templates/'.$temp[0], null, false, false)
											||	$lang->load('tpl_'.$temp[0].'.sys', JPATH_SITE, $lang->getDefault(), false, false)
											||	$lang->load('tpl_'.$temp[0].'.sys', JPATH_SITE.'/templates/'.$temp[0], $lang->getDefault(), false, false);
											
										}
										else
										{
											// Get XML file from component folder for standard layouts
											$file = JPATH_SITE.'/components/'.$item->com_name.'/views/'.$vars['view'].'/tmpl/'.$vars['layout'].'.xml';
										}
										if (JFile::exists($file) && $xml = simplexml_load_file($file)) {
											// Look for the first view node off of the root node.
											if ($layout = $xml->xpath('layout[1]')) {
												if (!empty($layout[0]['title'])) {
													$value .= ' » ' . JText::_(trim((string) $layout[0]['title']));
												}
											}
											if (!empty($layout[0]->message[0])) {
												$item->item_type_desc = JText::_(trim((string) $layout[0]->message[0]));
											}
										}
									}
								}
								unset($xml);
							}
							else {
								// Special case for absent views
								$value .= ' » ' . JText::_($item->com_name.'_'.$vars['view'].'_VIEW_DEFAULT_TITLE');
							}
						}
					}
					else {
						if (preg_match("/^index.php\?option=([a-zA-Z\-0-9_]*)/", $item->link, $result)) {
							$value = JText::sprintf('COM_MENUS_TYPE_UNEXISTING',$result[1]);
						}
						else {
							$value = JText::_('COM_MENUS_TYPE_UNKNOWN');
						}
					}
					break;
			}
			$item->item_type = $value;
		}
		
		foreach($rows as $item)
		{
			$item->treename =  str_repeat('<span class="gtr">|&mdash;</span>', $item->level-1).$item->title;
			
			$obj = oseRegistry::call('content')->getInstance('msc')->getItem('joomla','menu',$item->id,'msc',$msc_id,null,'obj');
			
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

		$items = array_values($rows);
		
		$result = array();
		$result['total'] = $total;
		$result['results'] = $items;
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
	
	function changeStatus()
	{
		$db = oseDB::instance();
		
		//$app = JFactory::getApplication();
		//$jMenu = $app->getMenu();
		$msc_id = JRequest::getInt('msc_id',0);
		
		$menu_ids = JRequest::getVar('menu_ids',array());
		
		$newStatus = JRequest::getInt('status',0);
		
		$timeLength= JRequest :: getVar('time_length', array());
		$timeUnit= JRequest :: getVar('time_unit', array());
		
		//$time_length = array();
		//$time_unit = array();
		$new_menu_ids = array();
		foreach($menu_ids as $menu_id)
		{
			$new_menu_ids[$menu_id] = $menu_id;
			//$time_length[$menu_id] = $timeLength[$key];
			//$time_unit[$menu_id] = $timeUnit[$key];
			$query = " SELECT * FROM `#__menu`"
					." WHERE id = '{$menu_id}'"
					;
			$db->setQuery($query);
			$menu = oseDB::loadItem('obj');
			
			if($menu->type == 'alias')
			{
				$aliasoptions = oseJson::decode($menu->params)->aliasoptions;
				$new_menu_ids[$aliasoptions] = $aliasoptions;
			}
		}
		//$menu_ids = $new_menu_ids;

		foreach($menu_ids as $key => $menu_id)
		{
			
			$content = oseRegistry::call('content')->getInstance('msc');
			$item = $content->getItem('joomla','menu',$menu_id,'msc',$msc_id, '','obj');
			
			$ItemParams = array();
			$ItemParams['time_length'] = $timeLength[$key];
			$ItemParams['time_unit'] = $timeUnit[$key];
		
			if(empty($item))
			{
				/*
				if($menu->type == 'alias')
				{
					$aliasoptions = oseJson::decode($menu->params)->aliasoptions;
					//$menu_ids[] = $aliasoptions;
					$updated = $content->insert('joomla','menu',$menu_id,'msc',$msc_id, $newStatus,array('aliasoptions'=>$aliasoptions));
				}
				else
				{
					$updated = $content->insert('joomla','menu',$menu_id,'msc',$msc_id, $newStatus);
				}
				*/
				$updated = $content->insert('joomla','menu',$menu_id,'msc',$msc_id, $newStatus,$ItemParams);
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
					//continue;
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
					/*
					if($menu->type == 'alias')
					{
						$aliasoptions = oseJson::decode($menu->params)->aliasoptions;
						$itemParams = oseJson::decode($item->params,true);
						$itemParams['aliasoptions'] = $aliasoptions;
						$updated = $content->update($item, $newStatus,$itemParams);
					}
					else
					{
						$updated = $content->update($item, $newStatus);
					}
					*/
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