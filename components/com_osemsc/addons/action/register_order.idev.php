<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegister_OrderIdev
{
	public static function add($params)
	{
		$result = array();
		$result['success'] = true;

		if(empty($params))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Register_Order.Idev');

			return $result;
		}
		$orderParams = oseJson::decode($params['params']);
		$orderParams->first_ip = oseMscPublic::getIP();
		$params['params'] = oseJson::encode($orderParams);
		return $params;
	}
}
?>