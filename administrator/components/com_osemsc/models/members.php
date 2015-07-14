<?php
/**
  * @version     5.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 15-Nov-2010
  * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
  *
  *
  *  This program is free software: you can redistribute it and/or modify
  *  it under the terms of the GNU General Public License as published by
  *  the Free Software Foundation, either version 3 of the License, or
  *  (at your option) any later version.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU General Public License for more details.
  *
  *  You should have received a copy of the GNU General Public License
  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
  *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
*/
defined('_JEXEC') or die("Direct Access Not Allowed");


class oseMscModelMembers extends oseMscModel
{
	protected $total = 0;

    public function __construct()
    {
        parent::__construct();
    } //function

	function save()
	{

	}

	function getList($msc_id)
	{
		$post = array();
        $post['search'] = JRequest::getString('search',null);
		$post['start'] = JRequest::getInt('start',0);
		$post['limit'] = JRequest::getInt('limit',20);
		$post['status'] = JRequest::getInt('status',1);

		$member = oseRegistry::call('msc');

		$result = $member->getMembers($msc_id,$post['status'],$post['search'],$post['start'],$post['limit']);
		
		return $result;
	}

	function getUsers()
	{
		$db = oseDB::instance();

		$search = JRequest::getString('search',null);

		$post['search'] = JRequest::getString('search',null);
		$post['start'] = JRequest::getInt('start',0);
		$post['limit'] = JRequest::getInt('limit',20);
		$post['msc_id'] = JRequest::getInt('msc_id','');
		$member = oseRegistry::call('member');

		$result = $member->getUsers($post);

		return $result;
	}

	function joinMsc($member_id,$paymentInfo)
	{

		$result = oseMscPublic::generateOrder($member_id,'system_admin',$paymentInfo);


		if(!$result['success'])
		{
			return $result;
		}

		$orderInfo = oseRegistry::call('payment')->getInstance('Order')->getOrder(array("`order_id`='{$result['order_id']}'"),'obj');

		return oseMscPublic::processPayment($orderInfo);
		/*
		//$db = oseDB::instance();
		$member = oseRegistry::call('member');

		$result = array();
		$result['success'] = true;
		$result['title'] = 'Done';
		$result['content'] = 'Added successfully!';

		foreach($member_ids as $member_id)
		{
			$member->instance($member_id);

			$isMember = $member->isSpecificMember($msc_id);

			$user = JFactory::getUser();
			$params = array();
			$params['member_id'] = $user->id;
			$params['payment_method'] = 'system_admin';

			oseRegistry::call('msc')->runAddonAction('member.billinginfo.save');
			oseRegistry::call('msc')->runAddonAction('register.payment.save',$params);

			if(!$member->joinMsc($msc_id))
			{
				$result['success'] = false;
				$result['title'] = 'Error';
				$result['content'] = 'Fail adding user!';

				return $result;
			}
			else
			{


			    // is Member, renew... else join.
			   // $db = oseDB::instance();
			    $msc_option = JRequest::getCmd('msc_option',null);

			    $payment = oseRegistry::call('payment');

			    $orderParams = array();
			    //$price_params = $order->generateOrderParams($msc_id,);
				$orderParams['payment_price'] = 0;
		        $orderParams['order_number'] = $payment->generateOrderNumber($member_id);
		        $orderParams['create_date'] = oseHTML::getDateTime();;//date("Y-m-d H:i:s");


		        $extPayments = oseMscAddon::getExtInfo($msc_id,'payment');

		        $extPayment = $extPayments[$msc_option];

		        if (strtolower(oseObject::getValue($extPayment,'payment_mode'))== 'b')
				{
					$orderParams['payment_mode'] = 'm';
					$price_params = $payment->generateOrderParams($msc_id,0,'m',$msc_option);
				}
				else
				{
					$orderParams['payment_mode'] = oseObject::getValue($extPayment,'payment_mode');
					$price_params = $payment->generateOrderParams($msc_id,0,oseObject::getValue($extPayment,'payment_mode'),$msc_option);
				}


				//$orderParams['payment_mode'] = 'm';


		        $orderParams['payment_from'] = 'system_admin';
		        $orderParams['payment_method'] = 'osemsc';
		        $orderParams['order_status'] = 'confirmed';
				//$params['payment_from_order_id'] = $order_number;
				$orderParams['params'] = oseJSON::encode($price_params);
			    $order_id = $payment->generateOrder($msc_id,$member_id,$orderParams);


			    if(!$order_id)
			    {
			    	$result['success'] = false;
					$result['title'] = 'Error';
					$result['content'] = 'Fail adding user!';

					return $result;
			    }
			    else
			    {
			    	//self::addVmOrder($msc_id,$member_id,$orderParams,$orderParams['order_number']);
			    	//$db = oseDB::instance();oseExit($db->_sql);
			    }

			    $params = array();
				$params['msc_id'] = $msc_id;
				$params['member_id'] = $member_id;
				$params['join_from'] = 'payment';
				$params['order_id'] = $order_id;
				$params['allow_work'] = true;
				$params['master'] = true;

			    //oseExit($isMember);
			    if(!$isMember)
			    {//oseExit('join');
					$list = oseMscAddon::getAddonList('join',true,1,'obj');

					foreach($list as $addon)
					{
						$action_name = 'join.'.$addon->name.'.save';

						$result = oseMscAddon::runAction('join.'.$addon->name.'.save',$params);

						if(!$result['success'])
						{
							$this->cancelMsc(array($member_id),$msc_id);

							return $result;
						}
					}
			    }
			    else
			    {

		    		$list = oseMscAddon::getAddonList('renew',true,1,'obj');

					foreach($list as $addon)
					{
						$action_name = 'renew.'.$addon->name.'.activate';

						$result = oseMscAddon::runAction($action_name,$params);

						if(!$result['success'])
						{
							$this->cancelMsc(array($member_id),$msc_id);

							return $result;
						}
					}

			    }

			}
		}

		return $result;
		*/
	}

	function cancelMsc($member_ids,$msc_id)
	{
		$result = array();
		$result['success'] = false;
		$result['title'] = 'Error';
		$result['content'] = 'Fail removing';

		$member = oseRegistry::call('member');

		foreach($member_ids as $member_id)
		{
			$member->instance($member_id);
			if(!$member->cancelMsc($msc_id))
			{
				return $result;
			}
			else
			{
				$params = array();
				$params['msc_id'] = $msc_id;
				$params['member_id'] = $member_id;
				$params['join_from'] = 'payment';
				$params['allow_work'] = true;
				$params['master'] = true;
				$list = oseMscAddon::getAddonList('join',true,1,'obj');

				foreach($list as $addon)
				{
					$action_name = oseMscAddon :: getActionName($addon,'cancel','join');

					$result = oseMscAddon::runAction($action_name,$params);

					if(!$result['success'])
					{
						$result['content'] = 'Member ID:'.$member_id.' '. $action_name.' '.$result['content'];
						return $result;
					}
				}

				//Cancel Emial
				$userInfo = $member->getUserInfo('obj');
				$msc = oseRegistry::call('msc');
				$ext = $msc->getExtInfo($msc_id,'msc','obj');

				if($ext->cancel_email)
				{
					$email = $member->getInstance('email');

					$emailTempDetail = $email->getDoc($ext->cancel_email,'obj');

					$variables = $email->getEmailVariablesCancel($member_id,$msc_id);

					$emailParams = $email->buildEmailParams($emailTempDetail->type);

					$emailDetail = $email->transEmail($emailTempDetail,$variables,$emailParams);

					$email->sendEmail($emailDetail,$userInfo->email);

					$emailConfig = oseMscConfig::getConfig('email','obj');
					if($emailConfig->sendCancel2Admin)
					{
						$email->sendToAdminGroup($emailDetail,$emailConfig->admin_group);
					}
				}
			}
		}
		
		$result['success'] = true;
		$result['title'] = 'Done';
		$result['content'] = 'Removed successfully!';
		
		return $result;
	}

	private function addVmOrder($msc_id, $member_id, $params,$order_number)
	{

	   	if(empty($member_id))
	    {

			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText :: _('Error');

			return $result;
	   	}

		// Get the IP Address
		if (!empty($_SERVER['REMOTE_ADDR']))
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		else {
			$ip = 'unknown';
		}

		$post = JRequest::get('post');

		$payment_mode = $params['payment_mode'];
		$payment_method = $params['payment_method'];

		//Insert the vm order table(#__vm_orders)
		$order =array();
		//get membership price
		$payment = oseRegistry::call('payment');
		$paymentInfo = oseMscAddon::getExtInfo($msc_id,'payment','obj');

		if($payment_mode == 'm')
		{
			$order_subtotal = $paymentInfo->price;
		}
		else
		{
			$order_subtotal = (empty($paymentInfo->has_trial))?$paymentInfo->a3:$paymentInfo->a1;
		}

		$order['order_subtotal'] = $params['payment_price'];

		$order_total = $params['payment_price'];

		$order['order_total'] = $order_total;

		$db= oseDB::instance();

		//$order['order_tax'] = '0.00';

		$query= "SELECT user_info_id FROM `#__vm_user_info` WHERE `user_id` = '".(int) $member_id."'  AND (`address_type` = 'BT' OR `address_type` IS NULL)";
		$db->setQuery($query);
		$result= $db->loadResult();

		$hash_secret = "VirtueMartIsCool";
		$user_info_id = empty($result)?md5(uniqid( $hash_secret)):$result;
		$vendor_id = '1';

		$order['user_id'] = $member_id;
		$order['vendor_id'] = $vendor_id;
		$order['user_info_id'] = $user_info_id;
		$order['order_number'] = $order_number;
		$order['order_currency'] = (!empty($payment->currency))?$payment->currency:"USD";
	    $order['order_status'] = 'C';
	    $order['cdate'] = time();
	    $order['ip_address'] = $ip;
	    $keys = array_keys($order);
	    $keys = '`'.implode('`,`',$keys).'`';

	    foreach($order as $key => $value)
	    {
	    	$order[$key] = $db->Quote($value);
	    }

	    $values = implode(',',$order);

		$query = "INSERT INTO `#__vm_orders` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);

		if (!oseDB::query())
		{
			$result = array();

			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
		}

		//Insert the #__vm_order_history table
		$order_id = $db->insertid();
		$history = array();
		$history['order_id'] = $order_id;
		$history['order_status_code'] = 'C';
		$history['date_added'] = date("Y-m-d G:i:s", time());
		$history['customer_notified'] = '1';

		$keys = array_keys($history);
	    $keys = '`'.implode('`,`',$keys).'`';

	    foreach($history as $key => $value)
	    {
	    	$history[$key] = $db->Quote($value);
	    }

	    $values = implode(',',$history);

		$query = "INSERT INTO `#__vm_order_history` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);

		if (!oseDB::query())
		{
			$result = array();

			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
		}


		//Insert the Order payment info
		$payment = array();
		$payment['order_id'] = $order_id;
		$payment['payment_method_id'] = $payment_method;

		if($payment_method == 'authorize')
		{

		}


		//Insert the User Bill
		$bill = array();



		$query = " SELECT * FROM `#__osemsc_billinginfo`"
				." WHERE user_id = {$member_id}"
				;
		$db->setQuery($query);
		$billInfo = oseDB::loadItem();

		if(isset($billInfo))
		{
			$bill['company'] = $billInfo['company'];
			$bill['address_1'] = $billInfo['addr1'];
			$bill['address_2'] = $billInfo['addr2'];
			$bill['city'] = $billInfo['city'];
			$bill['state'] = $billInfo['state'];
			$bill['country'] = $billInfo['country'];

			//get vm country code
			$query = " SELECT country_3_code FROM `#__vm_country` WHERE `country_2_code` = '{$bill['country']}' ";
	    	$db->setQuery($query);
	    	$country_code = $db->loadResult();
	    	$bill['country'] = empty($country_code)?$bill['country']:$country_code;

	    	//get vm state code
	    	$query = " SELECT state_2_code FROM `#__vm_state` WHERE `state_name` = '{$bill['state']}' ";
	    	$db->setQuery($query);
	    	$state_code = $db->loadResult();
	    	$bill['state'] = empty($state_code)?$bill['state']:$state_code;

			$bill['zip'] = $billInfo['postcode'];
			$bill['phone_1'] = $billInfo['telephone'];
		}

		$query = " SELECT * FROM `#__osemsc_userinfo_view`"
				." WHERE user_id = {$member_id}"
				;
		$db->setQuery($query);
		$userInfo = oseDB::loadItem();

		$bill['order_id'] = $order_id;
		$bill['user_id'] = $member_id;
		$bill['address_type'] = 'BT';
		$bill['address_type_name'] = '-default-';
		$bill['last_name'] = $userInfo['lastname'];
		$bill['first_name'] = $userInfo['firstname'];
		$bill['user_email'] = $userInfo['email'];

		$keys = array_keys($bill);
	    $keys = '`'.implode('`,`',$keys).'`';

	    foreach($bill as $key => $value)
	    {
	    	$bill[$key] = $db->Quote($value);
	    }

	    $values = implode(',',$bill);

		$query = "INSERT INTO `#__vm_order_user_info` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);

		if (!oseDB::query())
		{
			$result = array();

			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
		}


		//Insert the itme table(#__vm_order_item)
		$item=array();
		$item['order_id']=$order_id;
		$item['user_info_id'] = $user_info_id;
		$item['vendor_id'] = $vendor_id;

		//get the product info
		$vm = oseMscAddon::getExtInfo($msc_id,'vm','obj');
		$query = " SELECT * FROM `#__vm_product` WHERE `product_id` = '{$vm->product_id}' ";
	    $db->setQuery($query);
	    $product = $db->loadObject();

		$item['product_id'] = $vm->product_id;
		$item['order_item_sku'] = $product->product_sku;
		$item['order_item_name'] = $product->product_name;
		$item['product_quantity'] = '1';
		$item['product_item_price'] = $order_subtotal;
		$item['product_final_price'] = $order_total;
		$item['order_item_currency'] = (!empty($payment->currency))?$payment->currency:"USD";
		$item['order_status'] = 'C';
		$item['cdate'] = time();;

		$keys = array_keys($item);
	    $keys = '`'.implode('`,`',$keys).'`';

	    foreach($item as $key => $value)
	    {
	    	$item[$key] = $db->Quote($value);
	    }

	    $values = implode(',',$item);

		$query = "INSERT INTO `#__vm_order_item` ({$keys}) VALUES ({$values});";
		$db->setQuery($query);

		if (!oseDB::query())
		{
			$result = array();

			$result['success'] = false;
			$result['title'] = 'Error';
			$result['content'] = JText::_('Error');
		}

			$result = array();

			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = JText::_('Done');

			return $result;

	}

}