<?php
defined('_JEXEC') or die(";)");
class oseMscAddonActionPanelVm extends oseMscAddon {
	public static function save($params= array()) {
		$result= array();
		$post= JRequest :: get('post');
		if(oseMscAddon :: quickSavePanel('vm_', $post)) {
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
		$query= "SELECT product_id,product_name FROM `#__vm_product`";
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
		$query= " SELECT shopper_group_id,shopper_group_name FROM `#__vm_shopper_group` ".		" ORDER BY shopper_group_id ASC ";
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
		$query= "SELECT category_id, category_name FROM `#__vm_category`";
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
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
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
		$query= " SELECT `shopper_group_id` FROM `#__vm_shopper_group` ".			" WHERE `shopper_group_name` = ".$db->Quote($mscItem['title']);
		;
		$db->setQuery($query);
		$items= oseDB :: loadList();
		if(empty($items)) {
			$query= " INSERT INTO `#__vm_shopper_group` (`shopper_group_id`, `vendor_id`, `shopper_group_name`, `shopper_group_desc`, `shopper_group_discount`, `show_price_including_tax`, `default`) VALUES (NULL, 1, ".$db->Quote($mscItem['title']) .", ".$db->Quote($mscItem['title']) .", 0.00, 1, 0);";
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