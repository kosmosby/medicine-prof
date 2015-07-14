<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegister_OrderOSEAffiliate
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
		if(!empty($_COOKIE['oseAffiliate']))
		{
			$orderParams = oseJson::decode($params['params']);
			$oseAffiliate = oseJSON::decode($_COOKIE['oseAffiliate']);
		
			
			$orderParams->oseaffiliateID = $oseAffiliate->oafid;
			$orderParams->osebannerID = $oseAffiliate->obid;
			$orderParams->first_ip = oseMscPublic::getIP();
			//$orderParams->first_ip = oseMscPublic::getIP();
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