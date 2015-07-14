<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterCoupon
{
	function add()
	{
		$db = oseDB::instance();

		$coupon_code = JRequest::getCmd('coupon_code',null);

		$result = array();

		if(empty($coupon_code))
		{
			$result['success'] = false;
			$result['title'] = JText::_('Notice');
			$result['content'] = JText::_('THE_COUPON_IS_INVALID');
		}
		else
		{
			$config = oseMscConfig::getConfig('coupon','obj');
			if(oseObject::getValue($config,'wildcard_enabled',false))
			{
				$white_pass_minlength = oseObject::getValue($config,'wildcard_minlength',10);
				if(strlen($coupon_code) < $white_pass_minlength)
				{
					$result['success'] = false;
					$result['title'] = JText::_('Notice');
					$result['content'] = JText::_('THE COUPON CODE MINIMUM LENGTH IS').' '.$white_pass_minlength;
					$result = oseJson::encode($result);

					oseExit($result);
				}
				$code = $db->Quote($coupon_code);
				$query = " SELECT * FROM `#__osemsc_coupon`"
						." WHERE {$code} LIKE CONCAT('%',`code`)"
						;
			}
			else
			{
				$code = $db->Quote($coupon_code);
				$query = " SELECT * FROM `#__osemsc_coupon`"
						." WHERE code = {$code}"
						;
			}

			$db->setQuery($query);
			$item = oseDB::loadItem('obj');
			$couponParams = oseJson::decode(oseObject::getValue($item,'params','{}'));
			if(empty($item) )
			{
				$result['success'] = false;
				$result['title'] = JText::_('Notice');
				$result['content'] = JText::_('THE_COUPON_IS_INVALID');
			}
			else
			{
				$user = oseMscPublic::getUser();
				$cart = oseMscPublic::getCart();

				// check range 1
				if(!$user->guest)
				{
					$query = " SELECT COUNT(*) FROM `#__osemsc_member`"
							." WHERE `member_id` = '{$user->id}'"
							;
					$db->setQuery($query);
					$hasMsc = $db->loadResult();
					$itemParams = oseJson::decode($item->params);
					if($hasMsc > 0 && $itemParams->range == 'new_member_only')
					{
						$cart->updateParams('coupon',null);
						$cart->update();
						$result['success'] = false;
						$result['title'] = JText::_('Notice');
						$result['content'] = JText::_('COUPON_RANGE_NOT_MATCH');
						return $result;
					}
				}

				// check range 2
				$items= $cart->get('items');
				foreach ($items as $tmpitem)
				{
					$cartitem= $tmpitem;
					break;
				}
				if(!empty($cartitem))
				{
					$params = oseJson::decode($item->params);
					$msc_id = oseMscPublic :: getEntryMscID($cartitem);
					$mscs = $params->msc_ids;
					if($mscs != 'all')
					{
						if(!is_array($mscs))
						{
							$mscs = explode(',',$mscs);
						}

						if(!in_array($msc_id,$mscs))
						{
							$cart->updateParams('coupon',null);
							$cart->update();
							$result['success'] = false;
							$result['title'] = JText::_('Notice');
							$result['content'] = JText::_('THE_COUPON_IS_INVALID');
							return $result;
						}
					}

					$coupon_currencies = explode(",", oseObject::getValue($couponParams,'currencies',''));
					$session =& JFactory::getSession();
					$currency = $session->get('osePaymentCurrency',oseRegistry::call('msc')->getConfig('currency','obj')->primary_currency);

					if(empty($coupon_currencies))
					{

					}
					else
					{
						if(!in_array('all',$coupon_currencies) && !in_array($currency, $coupon_currencies))
						{
								$allowCurrencies = implode("OR", $coupon_currencies);
								$cart->updateParams('coupon',null);
								$cart->update();
								$result['success'] = false;
								$result['title'] = JText::_('Notice');
								$result['content'] = JText::_('THE_COUPON_CANNOT_BE_APPLIED_IN_THIS_CURRENCY').$allowCurrencies;
								return $result;
						}
					}


				}

				// check amount
				$query = " SELECT COUNT(*) FROM `#__osemsc_coupon_user`"
						." WHERE `coupon_id` = '{$item->id}' AND `paid` = '1'"
						;
				$db->setQuery($query);

				$used = $db->loadResult();

				if($item->amount_infinity != 1 && $used >= $item->amount)
				{
					$result['success'] = false;
					$result['title'] = JText::_('Notice');
					$result['content'] = JText::_('COUPON_USED_OUT');
					return $result;
				}

				$result['success'] = true;
				$result['title'] = JText::_('SUCCEED');
				$result['content'] = JText::_('COUPON_DISCOUNT_APPLIED');
				$result['is_free_manual'] = false;
				//check the total amount
				$query = "SELECT frontend_enabled FROM `#__osemsc_addon` WHERE `name` = 'payment_mode'";
				$db->setQuery($query);
				$enabled = $db->loadResult();
				if($enabled)
				{
					$payment_mode = $cart->getParams('payment_mode');
				}else 
				{
					$payment_mode = oseMscPublic::getPaymentMode('payment_payment_mode');
					$payment_mode = oseMscPublic::savePaymentMode();
					$payment_mode = $cart->getParams('payment_mode');
				}
				
				if($payment_mode == 'm')
				{
					if($item->discount_type == 'rate' && $item->discount == 100)
					{
						$result['is_free_manual'] = true;
					}else{
						$items= $cart->get('items');
						$cartitem= $items[0];
						$price = oseObject :: getValue($cartitem, 'standard_raw_price');
						if($price <= $item->discount)
						{
							$result['is_free_manual'] = true;
						}
					}
				}
				$cart->updateParams('coupon_range',oseObject::getValue($couponParams,'range','all'));
				$cart->updateParams('coupon_range2',oseObject::getValue($couponParams,'range2','first'));
				$cart->updateParams('coupon_discount',$item->discount);
				$cart->updateParams('coupon_discount_type',$item->discount_type);
				$cart->updateParams('coupon_currencies',oseObject::getValue($couponParams,'currencies','all'));

				$couponParams = oseJson::decode($item->params);

				$couponNumber = $cart->getParams('coupon_number');

				if($coupon_code == $cart->getParams('coupon'))
				{
					//
					$cart->updateParams('coupon_msc_ids',$couponParams->msc_ids);

				}
				else
				{
					$cart->updateParams('coupon',$coupon_code);
					$cart->updateParams('coupon_id',$item->id);
					if($user->guest)
					{
						$cart->updateParams('coupon_number',uniqid('guest_'));
					}
					else
					{
						$cart->updateParams('coupon_number',uniqid($user->id.'_'));
					}


					$cart->updateParams('coupon_msc_ids',$couponParams->msc_ids);



				}

				$updated = $this->updateCoupon($item,$cart->getParams('coupon_number'));
				if($updated)
				{
					$cart->updateParams('coupon_user_id',$updated);
				}
				//$cart->cart['params'] = array();
				$cart->update();
			}

		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getCurrentCode()
	{
		$cart = oseMscPublic::getCart();

		$code = $cart->getParams('coupon');
		if(empty($code))
		{
			return JText::_('PLEASE_ENTER_COUPON_CODE').'.';
		}
		else
		{
			return JText::_('THE_COUPON_CODE').': "'.$cart->getParams('coupon').'" '.JText::_('CODE_APPLIED_ON_CHECKOUT');
		}
	}

	private function updateCoupon($item,$coupon_number = null)
	{
		$db = oseDB::instance();

		$user = oseMscPublic::getUser();

		$coupon_id = oseObject::getValue($item,'id',0);

		$where = array();

		if($user->guest)
		{
			$where[] = "coupon_number = '{$coupon_number}'";
		}
		else
		{
			$where[] = "user_id = '{$user->id}'";
		}

		$where[] = "`coupon_id` = '{$coupon_id}'";
		$where[] = "`paid` = 0";

		$where = oseDB::implodeWhere($where);

		$query = " SELECT * FROM `#__osemsc_coupon_user`"
				. $where
				." ORDER BY `id` DESC"
				;
		$db->setQuery($query);

		$obj = oseDB::loadItem('obj');

		$array = array();
		if(empty($obj))
		{
			$array['coupon_id'] = $coupon_id;
			$array['user_id'] = $user->id;
			$array['msc_id'] = 0;
			$array['coupon_number'] = $coupon_number;
			$updated = oseDB::insert('#__osemsc_coupon_user',$array);
			$coupon_user_id = $updated;
			if(!$updated)
			{
				return false;
			}

			if(oseObject::getValue($item,'amount_infinity') == 0)
			{
				$couponParams = oseJson::decode($item->params);

				$couponParams->amount_left--;

				$couponParams = oseJson::encode($couponParams);

				$array = array();
				$array['id'] = $coupon_id;
				$array['params'] = $couponParams;
				//oseExit($couponParams);
				$updated = oseDB::update('#__osemsc_coupon','id',$array);

				if(!$updated)
				{
					return false;
				}
			}

		}
		else
		{
			$array['id'] = $obj->id;
			$array['coupon_id'] = $coupon_id;
			$array['user_id'] = $user->id;
			$array['msc_id'] = 0;

			$coupon_user_id = $obj->id;

			$updated = oseDB::update('#__osemsc_coupon_user','id',$array);

		}

		if($updated)
		{
			$updated = $coupon_user_id;
		}

		return $updated;
	}

	public static function save($params)
	{
		$db = oseDB::instance();
		$member_id = $params['member_id'];
    	//$msc_id = $params['msc_id'];
    	//$msc_option = $params['msc_option'];
    	JRequest::setVar('member_id',$member_id);

    	if(empty($member_id))
    	{

			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Error');

			return $result;
    	}

    	$cart = oseMscPublic::getCart();
    	$coupon_id = $cart->getParams('coupon_id');

    	if(empty($coupon_id))
    	{
    		$result['success'] = true;
			$result['title'] = JText :: _('Done');
			$result['content'] = JText :: _('Done');

			return $result;
    	}

    	$coupon_number = $cart->getParams('coupon_number');

    	$where = array();

		$where[] = "coupon_number = '{$coupon_number}'";
		$where[] = "`coupon_id` = '{$coupon_id}'";

		$where = oseDB::implodeWhere($where);

		$query = " UPDATE `#__osemsc_coupon_user`"
				." SET `user_id` = '{$member_id}'"
				. $where
				;
		$db->setQuery($query);

		$updated = oseDB::query();

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText :: _('Done');
			$result['content'] = JText :: _('Done');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText :: _('Error');
			$result['content'] = JText :: _('Coupon: Can not save the member ID');
		}
		return $result;
	}
}
?>