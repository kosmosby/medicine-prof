<?php
defined('_JEXEC') or die(";)");
require_once(dirname(__FILE__).DS.'membership_j15.php');
class oseMembership_J17 extends oseMembership_J15
{
	function getloginRedirect($msc_id)
	{
		$db = oseDB::instance();
		oseDB::lock('#__menu');
		$objs = array();

		$where = array();

		//$where = array_merge($where,oseJSON::generateQueryWhere());

		// Added in V 4.4, menu access levels

		//$where[] = 'm.menutype = '.$db->Quote($menutype);
		$where[] = 'm.published != -2';
		$where[] = 'm.client_id = 0';

		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );


		$query = 'SELECT m.*,  com.name AS com_name'
				.' FROM `#__menu` AS m'
				.' LEFT JOIN `#__extensions` AS com ON com.extension_id = m.component_id'
				. $where
				. ' ORDER BY m.lft'
				;

		$db->setQuery( $query);
//oseExit($db->getQuery());

		$menus= $db->loadObjectList();
		$i = 0;
		if(!empty($menus)) {
			foreach($menus as $menu) {
			   $objs[$i]->menuid = $menu->id;
			   $objs[$i]->displayText = str_repeat('<span class="gtr">|&mdash;</span>', $menu->level).'[ID-'.$menu->id. ']-'.$menu->title;
			   $i++;
			}
		}
		oseDB::unlock();
		return $objs;
	}
}
?>
