<?php
defined('_JEXEC') or die(";)");
class oseMscAddonActionPanelVm2 extends oseMscAddon {
	public static function save($params= array()) {
		$result= array();
		$post= JRequest :: get('post');
		if(oseMscAddon :: quickSavePanel('vm2_', $post)) {
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
	public static function getProduct($params= array()) {
		$db= oseDB :: instance();
		$lang = &JFactory::getLanguage();
		$tag = strtolower($lang->get('tag'));
		$tag = empty($tag)?null:'_'.str_replace('-','_',$tag);
		$query= "SELECT virtuemart_product_id,product_name FROM `#__virtuemart_products{$tag}`";
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
	public static function getSg($params= array()) {
		$db= oseDB :: instance();
		$query= " SELECT virtuemart_shoppergroup_id,shopper_group_name FROM `#__virtuemart_shoppergroups` ".		" ORDER BY `virtuemart_shoppergroup_id` ASC ";
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
	public static function getCat($params= array()) {
		$db= oseDB :: instance();
		$lang = &JFactory::getLanguage();
		$tag = strtolower($lang->get('tag'));
		$tag = empty($tag)?null:'_'.str_replace('-','_',$tag);
		$query= "SELECT virtuemart_category_id, category_name FROM `#__virtuemart_categories{$tag}`";
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
		$msc_id = JRequest::getInt('msc_id',null);
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title']= JText :: _('ERROR');
			$result['content']= JText :: _('ERROR');
			return $result;
		}

		$mscItem= oseRegistry :: call("msc")->getInfo($msc_id, $type= 'array');
		if(empty($mscItem)) {
			$result['success']= false;
			$result['title']= JText :: _('ERROR');
			$result['content']= JText :: _('ERROR');
			return $result;
		}

		$db= oseDB :: instance();
		$query= " SELECT `virtuemart_shoppergroup_id` FROM `#__virtuemart_shoppergroups` ".			" WHERE `shopper_group_name` = ".$db->Quote($mscItem['title']);
		;
		$db->setQuery($query);
		$items= oseDB :: loadList();
		if(empty($items)) {
			$query= " INSERT INTO `#__virtuemart_shoppergroups` (`virtuemart_shoppergroup_id`, `virtuemart_vendor_id`, `shopper_group_name`, `shopper_group_desc`, `published`) VALUES (NULL, 1, ".$db->Quote($mscItem['title']) .", ".$db->Quote($mscItem['title']) .", 1);";
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
}
?>