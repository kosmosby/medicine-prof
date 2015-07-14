<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionJoinIdev
{
	public static function save($params)
	{
		$result = array();
		$result['success'] = true;
		//oseExit($params);
		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error Join.Idev');

			return $result;
		}
		unset($params['allow_work']);

		if( $params['join_from'] != 'payment')
		{
			$result['success'] = true;
			$result['title'] = JText::_('Done');
			$result['content'] = JText::_('Done Join.Order');

			return $result;
		}

		$oseMscConfig = oseRegistry::call('msc')->getConfig('thirdparty','obj');

		if(empty($oseMscConfig->idev_enable))
		{
			return $result;
		}

		if(empty($oseMscConfig->idev_url))
		{
			return $result;
		}

		$db = oseDB::instance();
		//$post = JRequest::get('post');
		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];
		$order_id = $params['order_id'];
		$order_item_id = $params['order_item_id'];

		$where = array();
		$where[] = "order_id = {$order_id}";

		$payment = oseRegistry::call('payment');
		$curOrder = $payment->getOrder($where,'obj');
		$curOrderParams = oseJson::decode($curOrder->params);

		$member = oseRegistry::call('member');
		$member->instance($member_id);
		$userInfo = $member->getUserInfo('obj');

		$memParams = $member->getMemberInfo($msc_id,'obj')->memParams;
		$memParams = oseJSON::decode($memParams);
		$memParams->first_ip = $curOrderParams->first_ip;

		$coupon_user_id = $curOrderParams->coupon_user_id;
		$query = "SELECT coupon_id FROM `#__osemsc_coupon_user`";
		$db->setQuery($query);
		$coupon_id = $db->loadResult();
		$query = "SELECT code FROM `#__osemsc_coupon`";
		$db->setQuery($query);
		$coupon = $db->loadResult();
		$code = null;
		if(!empty($coupon))
		{
			$code = "&coupon_code=".$coupon;
		}
		
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$oseMscConfig->idev_url."/sale.php?profile=72198&idev_saleamt={$curOrder->payment_price}&idev_ordernum={$order_id}&ip_address={$memParams->first_ip}".$code);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_exec($ch);
			curl_close($ch);
			return $result;
	    } catch (Exception $e) {
	    	return $result;
	    }
	}

	public static function cancel($params)
	{
		$result = array();
		$result['success'] = true;

		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = JText::_('Error');
			$result['content'] = JText::_('Error Join.iDev');

			return $result;
		}
		unset($params['allow_work']);

		$msc_id = $params['msc_id'];
		$member_id = $params['member_id'];

		$result = array();
		$result['success'] = true;
		$result['title'] = JText::_('Done');
		$result['content'] = JText::_("Done");

		return $result;
	}


	public function remove($params)
	{
		$result = array();
		$result['success'] = true;

		if(empty($params['allow_work']))
		{
			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_("Error");
			return $result;
		}
		unset($params['allow_work']);
		return $result;

	}

}
?>