<?php
defined('_JEXEC') or die(";)");
class oseMscAddonActionPanelJshopping extends oseMscAddon 
{
	public static function save($params= array()) 
	{
		$result= array();
		$post= JRequest :: get('post');
		$msc_id= JRequest :: getInt('msc_id');

		if(oseMscAddon :: quickSavePanel('jshopping_', $post)) {
			$result['success']= true;
			$result['title']= JText :: _('DONE');
			$result['content']= JText :: _('SAVE_SUCCESSFULLY');
		} else {
			$result['success']= false;
			$result['title']= JText :: _('ERROR');
			$result['content']= JText :: _('ERROR');
		}
		return $result;
	}
	public static function getProduct($params= array()) 
	{
		$db= oseDB :: instance();
		$query= "SELECT product_id,`name_en-GB` AS product_name FROM `#__jshopping_products`";
		$db->setQuery($query);
		$items= oseDB :: loadList();
		$result= array();
		if(count($items) < 1) {
			$result['total']= 0;
			$result['results']= '';
		} else {
			$result['total']= count($items);
			$result['results']= $items;
		}
		return $result;
	}
	
	public static function getCat($params= array()) 
	{
		$db= oseDB :: instance();
		$query= "SELECT category_id, `name_en-GB` AS category_name FROM `#__jshopping_categories`";
		$db->setQuery($query);
		$items= oseDB :: loadList();
		$result= array();
		if(count($items) < 1) {
			$result['total']= 0;
			$result['results']= '';
		} else {
			$result['total']= count($items);
			$result['results']= $items;
		}
		return $result;
	}
	
	public static function create()
	{
		$result= array();
		$msc_id = JRequest :: getInt('msc_id');
		$db= oseDB :: instance();
		$query = "SELECT title FROM `#__osemsc_acl` WHERE `id` = ".$msc_id;
		$db->setQuery($query);
		$title = $db->loadResult();
		
		$query = "SELECT count(*) FROM `#__jshopping_usergroups` WHERE `usergroup_name` = '{$title}'";
		$db->setQuery($query);
		$exist = $db->loadResult();
		
		if(!empty($exist))
		{
			$result['success']= true;
			$result['title']= JText :: _('DONE');
			$result['content']= JText :: _('USER_GROUP_EXISTS');
			return $result;
		}else{
			$query = "INSERT INTO `#__jshopping_usergroups` (`usergroup_id`,`usergroup_name`,`usergroup_discount`,`usergroup_description`,`usergroup_is_default`) VALUES (NULL,'{$title}','0.00','',0)";
			$db->setQuery($query);
			if(!$db->query()) {
				$result['success']= false;
				$result['title']= JText :: _('ERROR');
				$result['content']= $db->getErrorMsg();
				return $result;
			}
		}
		
		$result['success']= true;
		$result['title']= JText :: _('DONE');
		$result['content']= JText :: _('SAVE_SUCCESSFULLY');
		return $result;
	} 
	
	public static function getUg()
	{
		$db= oseDB :: instance();
		$query= " SELECT * FROM `#__jshopping_usergroups` ".		" ORDER BY `usergroup_id` ASC ";
		$db->setQuery($query);
		$items= oseDB :: loadList();
		$result= array();
		if(count($items) < 1) {
			$result['total']= 0;
			$result['results']= '';
		} else {
			$result['total']= count($items);
			$result['results']= $items;
		}
		return $result;
	}
}
?>