<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRenewOrder
{
	public static function renew($params)
	{
		$result = array();
		$result['success'] = true;
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Renew.Order');
			
			return $result;
		}
		//unset($params['allow_work']);
		
		return oseMscAddon::runAction('join.order.save',$params,true,false);
	}
	
	public static function activate($params)
	{
		$result = array();
		$result['success'] = true;
		
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Renew.Order');
			
			return $result;
		}
		//unset($params['allow_work']);
		
		return oseMscAddon::runAction('join.order.save',$params,true,false);
	}
}
?>