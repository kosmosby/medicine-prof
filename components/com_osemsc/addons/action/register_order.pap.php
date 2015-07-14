<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegister_OrderPap
{
	public static function add($params)
	{
		$result = array();
		$result['success'] = true;
		
		if(empty($params))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Register_Order.PAP');
			
			return $result;
		}
		//unset($params['allow_work']);
		
		if(!empty($_COOKIE['PAPVisitorId']))
		{
			$orderParams = oseJson::decode($params['params']);
			
			$orderParams->pap_visitorid = $_COOKIE['PAPVisitorId'];
			$orderParams->first_ip = oseMscPublic::getIP();
			$params['params'] = oseJson::encode($orderParams);
			return $params;
		}
		else
		{
			return $params;
		}
	}
}
?>