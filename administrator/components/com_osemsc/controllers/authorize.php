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

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'class.connection.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'AuthnetARB.class.php');

class oseMscControllerAuthorize extends oseMscController
{
	public function __construct()
	{
		parent::__construct();
	} //function
	

	function updateAuthorizeOrderInfo()
	{
		$result = array();
		$id = JRequest::getInt('id');
		$order_number = JRequest::getCmd('order_number');
		$serial_number = JRequest::getCmd('payment_serial_number');
		$payment_price = JRequest::getFloat('payment_price');
		
		$email = JRequest::getString('email');
		
		$db = oseDB::instance();
		
		$query = " SELECT * FROM `#__osemsc_order_fix`"
				." WHERE `member_id` = '{$id}'"
				;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		
		if(empty($item))
		{
			$query = " SELECT * FROM `#__osemsc_member`"
					." WHERE `id` = '{$id}'"
					;
			$db->setQuery($query);
			$memInfo = oseDB::loadItem('obj');
			// generate order and order item
			// we can copy other order to modify
			$query = " SELECT b.*,a.`params` as `oiParams` FROM `#__osemsc_order_item` AS a"
					." INNER JOIN `#__osemsc_order` AS b ON a.`order_id` = b.`order_id`"
					." WHERE a.`entry_id` = '{$memInfo->msc_id}' AND b.`order_status`='confirmed'"
					." AND b.`payment_mode` = 'a'"
					." AND b.`payment_method` = 'authorize'"
					." ORDER BY b.`order_id` DESC"
					." LIMIT 1"
					;
			$db->setQuery($query);
			$oItem = oseDB::loadItem();
			
			unset($oItem['order_id']);
			$oItem['order_number'] = (empty($order_number))?$this->generateOrderNumber($item->user_id):$order_number;
			$oItem['create_date'] = oseHtml::getDateTime();
			if(!empty($serial_number))
			{
				$oItem['payment_serial_number'] = $serial_number;
			}
			//$oItem['payment_serial_number'] = $serial_number;
			$oItem['payment_method'] = 'authorize';
			$oItem['user_id'] = $memInfo->member_id;
			
			// check the order number
			if(!empty($order_number))
			{
				$query = " SELECT * FROM `#__osemsc_order`"
				." WHERE `order_number` = ".$db->Quote($order_number)
				." AND `user_id` = '{$memInfo->member_id}'"
				;
				$db->setQUery($query);
				$fitem = oseDB::loadItem('obj');
					
				if(!empty($fitem))
				{
					$noItem = array();
					$noItem['order_id'] = $fitem->order_id;
					if(strlen($fitem->order_number) >= 32)
					{
						$noItem['order_number'] = $this->generateOrderNumber($item->user_id);
					}
					else
					{
						$noItem['order_number'] = $fitem->order_number.'_no';
					}
					$updated = oseDB::update('#__osemsc_order','order_id',$noItem);
				}
					
				$updated = oseDB::insert('#__osemsc_order',$oItem);
			}
			else
			{
				$updated = oseDB::insert('#__osemsc_order',$oItem);
			}
			
			if(!$updated)
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('ERROR1');
					
				oseExit(oseJSON::encode($result));
			}
			
			$oVals = array();
			$oVals['order_id'] = $updated;
			$oVals['entry_id'] = $memInfo->msc_id;
			$oVals['payment_price'] = $oItem['payment_price'];
			$oVals['payment_currency'] = $oItem['payment_currency'];
			$oVals['params'] = $oItem['oiParams'];
			$updated = oseDB::insert('#__osemsc_order_item',$oVals);
			
			if(!$updated)
			{
				oseDB::delete('#__osemsc_order', array('order_id'=>$updated));
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('ERROR2');
				oseExit(oseJSON::encode($result));
			}
			
			// update member table params
			$mVals = array();
			$mVals['params'] = array();
			$mVals['id'] = $id;
			$mVals['params']['order_id'] = $oVals['order_id'];
			$mVals['params']['order_item_id'] = $updated;
			$mVals['params']['payment_mode'] = 'a';
			$mVals['params'] = oseJson::encode($mVals['params']);
			oseDB::update("#__osemsc_member",'id',$mVals);
			
			// update the order paypal status
			$ofVals = array();
			$ofVals['order_id'] = $oVals['order_id'];
			$ofVals['order_item_id'] = $updated;
			
			$ofVals['member_id'] = $id;
			$ofVals['msc_id'] = $memInfo->msc_id;
			$ofVals['user_id'] = $memInfo->member_id;
			
			$ofVals['hasParams'] = 1;
			$ofVals['payment_method'] = 'authorize';
			$ofVals['payment_mode'] = 'a';
			$ofVals['status'] = 'updated';
			$ofVals['create_date'] = oseHtml::getDateTime();
			$ofVals['params'] = $oItem['params'];
			
			$ofVals['email'] = $email;
			oseDB::insert("#__osemsc_order_fix",$ofVals);
		}
		elseif( empty($item->order_id) )
		{
			$query = " SELECT * FROM `#__osemsc_member`"
			." WHERE `id` = '{$id}'"
			;
			$db->setQuery($query);
			$memInfo = oseDB::loadItem('obj');
			// generate order and order item
			// we can copy other order to modify
			$query = " SELECT b.*,a.`params` as `oiParams` FROM `#__osemsc_order_item` AS a"
			." INNER JOIN `#__osemsc_order` AS b ON a.`order_id` = b.`order_id`"
			." WHERE a.`entry_id` = '{$memInfo->msc_id}' AND b.`order_status`='confirmed'"
			." AND b.`payment_mode` = 'a'"
			." AND b.`payment_method` = 'authorize'"
			." ORDER BY b.`order_id` DESC"
			." LIMIT 1"
			;
			$db->setQuery($query);
			$oItem = oseDB::loadItem();

			unset($oItem['order_id']);
			$oItem['order_number'] = (empty($order_number))?$this->generateOrderNumber($item->user_id):$order_number;
			$oItem['create_date'] = oseHtml::getDateTime();
			if(!empty($serial_number))
			{
				$oItem['payment_serial_number'] = $serial_number;
			}
			
			if(!empty($payment_price))
			{
				$oItem['payment_price'] = $payment_price;
				
				$oParams = oseJson::decode($oItem['params']);
				$oParams->subtotal = $oParams->next_total = $oParams->total = $payment_price;
				$oItem['params'] = oseJson::encode($oParams);
			}
			//$oItem['payment_serial_number'] = $serial_number;
			$oItem['user_id'] = $memInfo->member_id;
			$oItem['payment_method'] = 'authorize';

			// check the order number
			if(!empty($order_number))
			{
				$query = " SELECT * FROM `#__osemsc_order`"
				." WHERE `order_number` = ".$db->Quote($order_number)
				." AND `user_id` = '{$item->user_id}'"
				;
				$db->setQUery($query);
				$fitem = oseDB::loadItem('obj');
					
				if(!empty($fitem))
				{
					$noItem = array();
					$noItem['order_id'] = $fitem->order_id;
					if(strlen($fitem->order_number) >= 32)
					{
						$noItem['order_number'] = $this->generateOrderNumber($item->user_id);
					}
					else
					{
						$noItem['order_number'] = $fitem->order_number.'_no';
					}
					$updated = oseDB::update('#__osemsc_order','order_id',$noItem);
				}

				$updated = oseDB::insert('#__osemsc_order',$oItem);
			}
			else
			{
				$updated = oseDB::insert('#__osemsc_order',$oItem);
			}

			if(!$updated)
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('ERROR1');

				oseExit(oseJSON::encode($result));
			}

			$oVals = array();
			$oVals['order_id'] = $updated;
			$oVals['entry_id'] = $memInfo->msc_id;
			$oVals['payment_price'] = $oItem['payment_price'];
			$oVals['payment_currency'] = $oItem['payment_currency'];
			$oVals['params'] = $oItem['oiParams'];
			$updated = oseDB::insert('#__osemsc_order_item',$oVals);

			if(!$updated)
			{ 
				oseDB::delete('#__osemsc_order', array('order_id'=>$updated));
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('ERROR2');
				oseExit(oseJSON::encode($result));
			}

			// update member table params
			$mVals = array();
			$mVals['params'] = array();
			$mVals['id'] = $id;
			$mVals['params']['order_id'] = $oVals['order_id'];
			$mVals['params']['order_item_id'] = $updated;
			$mVals['params']['payment_mode'] = 'a';
			$mVals['params'] = oseJson::encode($mVals['params']);
			oseDB::update("#__osemsc_member",'id',$mVals);

			// update the order paypal status
			$ofVals = array();
			$ofVals['id'] = $item->id;
			$ofVals['order_id'] = $oVals['order_id'];
			$ofVals['order_item_id'] = $updated;

			$ofVals['hasParams'] = 1;
			$ofVals['payment_method'] = 'authorize';
			$ofVals['payment_mode'] = 'a';
			$ofVals['status'] = 'updated';
			$ofVals['params'] = $oItem['params'];
			
			$ofVals['email'] = $email;
			oseDB::update("#__osemsc_order_fix",'id',$ofVals);
		}
		else
		{
			$oItem = array();
			$oItem['order_id'] = $item->order_id;
			
			/*if(!in_array( $item->payment_method,array('authorize','system','') ) )
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = 'This member does not pay by paypal';
				
				$result = oseJSON::encode($result);
				oseExit($result);
			}*/
			
			
			
			if(!empty($order_number))
			{
				$oItem['order_number'] = $order_number;
			}
			
			if(!empty($order_number))
			{
				$query = " SELECT * FROM `#__osemsc_order`"
				." WHERE `order_number` = ".$db->Quote($order_number)
				." AND `order_id` != '{$item->order_id}'"
				." AND `user_id` = '{$item->user_id}'"
				;
				$db->setQUery($query);
				$fitem = oseDB::loadItem('obj');
					
				if(!empty($fitem) && ($fitem->order_id < $item->order_id) )
				{
					$noItem = array();
					$noItem['order_id'] = $fitem->order_id;

					if(strlen($fitem->order_number) >= 32)
					{
						$noItem['order_number'] = $this->generateOrderNumber($item->user_id);
					}
					else
					{
						$noItem['order_number'] = $fitem->order_number.'_no';
					}
					$updated = oseDB::update('#__osemsc_order','order_id',$noItem);
				}
				// update fix orderid & order item id
				elseif(!empty($fitem) && ($fitem->order_id > $item->order_id) )
				{
					$query = " SELECT `order_item_id` FROM `#__osemsc_order_item`"
					." WHERE `order_id` = '{$fitem->order_id}'"
					." LIMIT 1"
					;
					$db->setQUery($query);
					$f_order_item_id = $db->loadResult();
					$item->order_item_id = $f_order_item_id;
					$item->order_id = $fitem->order_id;
						
					$oItem['order_id'] = $item->order_id;
					$oItem['order_item_id'] = $item->order_item_id;
				}
			}
			
			if(!empty($serial_number))
			{
				$oItem['payment_serial_number'] = $serial_number;
			}
			
			if(!empty($payment_price))
			{
				$oItem['payment_price'] = $payment_price;
				
				$query = "SELECT `params` FROM `#__osemsc_order`"
				." WHERE `order_id` = '{$item->order_id}'"
				;
				$db->setQuery($query);
				$oParams = $db->loadResult();
				$oParams = oseJson::decode($oParams);
				$oParams->subtotal = $oParams->next_total = $oParams->total = $payment_price;
				$oItem['params'] = oseJson::encode($oParams);
				
				$oiVals = array();
				$oiVals['order_item_id'] = $item->order_item_id;
				$oiVals['payment_price'] = $payment_price;
				oseDB::update("#__osemsc_order_item",'order_item_id',$oiVals);
			}
			
			$oItem['payment_method'] = 'authorize';
			oseDB::update("#__osemsc_order",'order_id',$oItem);
			/*$query = " UPDATE `#__osemsc_order`"
					." SET `order_number` = ".$db->Quote($order_number)
					.",`payment_serial_number` = ".$db->Quote($serial_number)
					." WHERE `order_id` = '{$item->order_id}'"
					;
			$db->setQuery($query);
			oseDB::query();*/
			
			//
			$oItem['id'] = $item->id;
			$oItem['payment_mode'] = 'a';
			if(!empty($email))
			{
				//$oItem['email'] = $email;
			}
			
			if($item->status == 'failed')
			{
				$oItem['status'] = 'updated';
			}
			
			oseDB::update("#__osemsc_order_fix",'id',$oItem);
			
			
			// update member table params
			$mVals = array();
			$mVals['params'] = array();
			$mVals['id'] = $id;
			$mVals['params']['order_id'] = $item->order_id;
			$mVals['params']['order_item_id'] = $item->order_item_id;
			$mVals['params']['payment_mode'] = 'a';
			$mVals['params'] = oseJson::encode($mVals['params']);
			oseDB::update("#__osemsc_member",'id',$mVals);
			
		}
		
		$result['success'] = true;
		$result['title'] = JText::_('SUCCESS');
		$result['content'] = JText::_('SUCCESS');
		
		$result = oseJSON::encode($result);
		oseExit($result);
	}
	
	protected function generateOrderNumber($user_id) {
		$length = 31-strlen($user_id);
		$order_number= $user_id."_".$this->randStr($length, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890");
		$db = oseDB :: instance();
		$query = "SELECT COUNT(*) FROM `#__osemsc_order` WHERE `order_number` = ". $db->Quote($order_number);
		$db->setQuery($query);
		$result = $db->loadResult();
		if (empty($result))
		{
			return $order_number;
		}
		else
		{
			$order_number= $this->generateOrderNumber($user_id);
			return $order_number;
		}
	}
	
	protected function randStr($length= 32, $chars= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
		// Length of character list
		$chars_length=(strlen($chars) - 1);
		// Start our string
		$string= $chars {
			rand(0, $chars_length)
		};
		// Generate random string
		for($i= 1; $i < $length; $i= strlen($string)) {
			// Grab a random character from our list
			$r= $chars {
				rand(0, $chars_length)
			};
			// Make sure the same two characters don't appear next to each other
			if($r != $string {
				$i -1 })
				$string .= $r;
		}
		// Return the string
		return $string;
	}
	
protected function send_request_via_fsockopen($host, $path, $content,$contentType = 'xml') {
		$posturl= "ssl://".$host;
		$header= "Host: $host\r\n";
		$header .= "User-Agent: PHP Script\r\n";
		if($contentType == 'xml')
		{
			$header .= "Content-Type: text/xml\r\n";
		}
		elseif($contentType == 'urlencoded')
		{
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		}
		$header .= "Content-Length: ".strlen($content)."\r\n";
		$header .= "Connection: close\r\n\r\n";
		$fp= fsockopen($posturl, 443, $errno, $errstr, 30);
		if(!$fp) {
			$response= false;
		} else {
			error_reporting(E_ERROR);
			fputs($fp, "POST $path  HTTP/1.1\r\n");
			fputs($fp, $header.$content);
			//fwrite($fp, $header."\r\n".$content);
			$response= "";
			while(!feof($fp)) {
				$response= $response.fgets($fp, 128);
			}
			fclose($fp);
			error_reporting(E_ALL ^ E_NOTICE);
		}
		return $response;
	}
	
	protected function substring_between($haystack, $start, $end) 
	{
		if(strpos($haystack, $start) === false || strpos($haystack, $end) === false) {
			return false;
		} else {
			$start_position= strpos($haystack, $start) + strlen($start);
			$end_position= strpos($haystack, $end);
			return substr($haystack, $start_position, $end_position - $start_position);
		}
	}

	function loadOrderInfo()
	{
		$result = array();
		$id = JRequest::getInt('id');
		$db = oseDB::instance();
		$dData = array('id'=>$id);
		
		$query = " SELECT * FROM `#__osemsc_order_fix`"
				." WHERE `member_id` = '{$id}'"
				;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		
		if(empty($item))
		{
			$query = " SELECT * FROM `#__osemsc_member`"
					." WHERE `id` = '{$id}'"
					;
			$db->setQuery($query);
			$mItem = oseDB::loadItem('obj');
				
			if(!empty($mItem->params))
			{
				$params = oseJson::decode($mItem->params);

				if(!empty($params->order_id))
				{
					$query = " SELECT `params`,`order_id`,`payment_serial_number`,`payment_method`,`order_number`,`payment_price`"
					." FROM `#__osemsc_order`"
					." WHERE `order_id` = '{$params->order_id}'"
					;
					$db->setQuery($query);
					$oItem = oseDB::loadItem('obj');
					$oItem->id = $id;
					
					
					// update the order paypal status
					$ofVals = array();
					$ofVals['order_id'] = $params->order_id;
					$ofVals['order_item_id'] = $params->order_item_id;
						
					$ofVals['member_id'] = $id;
					$ofVals['msc_id'] = $mItem->msc_id;
					$ofVals['user_id'] = $mItem->member_id;
						
					$ofVals['hasParams'] = 1;
					$ofVals['payment_method'] = $oItem->payment_method;
					$ofVals['payment_mode'] = 'a';
					$ofVals['status'] = 'updated';
					$ofVals['create_date'] = oseHtml::getDateTime();
					$ofVals['params'] = $oItem->params;
					
					oseDB::insert("#__osemsc_order_fix",$ofVals);
					
					unset($oItem->params);
					$result['success'] = true;
					$result['data'] = $oItem;
				}
				else
				{
					
					$result['success'] = true;
					$result['data'] = $dData;
				}
			}
			else 
			{
				$result['success'] = true;
				$result['data'] = $dData;
			}
		}
		else
		{
			/*$query = " SELECT `payment_serial_number` FROM `#__osemsc_order`"
					." WHERE `order_id` = '{$item->order_id}'"
					;*/
			$query = " SELECT `order_number`,`order_id`,`payment_serial_number`,`payment_method`,`payment_price`"
			." FROM `#__osemsc_order`"
			." WHERE `order_id` = '{$item->order_id}'"
			;
			$db->setQuery($query);
			$oItem = oseDB::loadItem('obj');
			$oItem->id = $id;
			$result['success'] = true;
			$result['data'] = $oItem;
		}
		
		oseExit(oseJson::encode($result));
	}	
	
	function updateMemberExpiryDate()
	{
		$result = array();
		$id = JRequest::getInt('id');
		$expired_date = JRequest::getString('expired_date');

		$db = oseDB::instance();

		$query = " SELECT * FROM `#__osemsc_order_fix`"
		." WHERE `member_id` = '{$id}'"
		;
		$db->SetQuery($query);
		$item = oseDB::loadItem('obj');

		if(empty($item))
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('Please Update First');
			
			$result = oseJSON::encode($result);
			oseExit($result);
		}
		
		$query = " SELECT * FROM `#__osemsc_member`"
		." WHERE `id` = '{$item->member_id}'"
		;
		$db->SetQuery($query);
		$member = oseDB::loadItem('obj');


		$eDate = explode(' ',$member->expired_date);
		$expired_date = $expired_date.' '.$eDate[1];

		//$nExpired_date = date_format($member->expired_date, 'Y-m-d H:i:s');
		$nExpired_date = date_create( $expired_date);
		$nExpired_date = date_format($nExpired_date, 'Y-m-d H:i:s');
		$now = date_create(oseHtml::getDateTime());
		$now = date_format($now, 'Y-m-d H:i:s');

		$mVals = array();

		// is expired
		if($now > $nExpired_date)
		{
			// update the order fix status
			$ofVals = array();
			$ofVals['id'] = $item->id;
			$ofVals['status'] = 'deleted';
			//$ofVals['payment_method'] = $payment_method;
			oseDB::update("#__osemsc_order_fix",'id',$ofVals);

			$mVals['virtual_status'] = 0;
			$mVals['status'] = 0;
		}
		else
		{
			// update the order fix status
			$ofVals = array();
			$ofVals['id'] = $item->id;
			$ofVals['status'] = 'fixed';
			//$ofVals['payment_method'] = $payment_method;
			oseDB::update("#__osemsc_order_fix",'id',$ofVals);

			$mVals['virtual_status'] = 1;
			$mVals['status'] = 1;
		}
		
		// update member table params
		$mVals['id'] = $item->member_id;
		$mVals['expired_date'] = $expired_date;
		
		oseDB::update("#__osemsc_member",'id',$mVals);


		$result['success'] = true;
		$result['title'] = JText::_('SUCCESS');
		$result['content'] = JText::_('SUCCESS');

		$result = oseJSON::encode($result);
		oseExit($result);
	}

	function validate()
	{
		$db = oseDB::instance();

		$result = array();
		$id = JRequest::getInt('id');
		$this->addOrderInfoFix($id);
		$query = " SELECT b.*,a.member_id,a.msc_id,a.id AS `fix_id`,a.email,a.status"
				." FROM `#__osemsc_order_fix` AS a"
				." INNER JOIN `#__osemsc_order` AS b ON b.order_id = a.order_id"
				." WHERE b.`order_id` = '{$id}'"
				." LIMIT 1"
				;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		
		if(empty($item))
		{
			$result['finish'] =true;
			oseExit(oseJson::encode($result));
		}
		
		$query = " SELECT COUNT(*) FROM `#__osemsc_member`"
		." WHERE `id` = '{$item->member_id}' AND `expired_date` >  DATE_ADD(NOW(),INTERVAL 2 DAY)"
		;
		$db->setQuery($query);
		$exists = $db->loadResult();
		
		if($exists > 0)
		{
			$result['finish'] =true;
			$result['success'] =true;
			$result['title'] ='Error';
			$result['content'] ='No need to validate this member';
			//oseExit(oseJson::encode($result));
		}
		
		$updated = $this->apiCheck($item);

		$result = $updated;
		if($updated['success'])
		{
			$query = " SELECT COUNT(*) FROM `#__osemsc_member`"
			." WHERE `id` = '{$item->member_id}' AND `expired_date` > NOW()"
			;
			$db->setQuery($query);
			$exists = $db->loadResult();
				
			if(!$result['finish'])
			{
				if($exists > 0)
				{
					$result['finish'] = true;
						
					// update the order fix status
					$ofVals = array();
					$ofVals['id'] = $item->fix_id;
					$ofVals['status'] = 'fixed';
					oseDB::update("#__osemsc_order_fix",'id',$ofVals);

					// update the 6month member, -1 day!
					if($item->msc_id == 13 && $item->payment_method == 'authorize')
					{
						$expired_date = " DATE_SUB(`expired_date` ,INTERVAL 1 DAY) ";

						$query = " UPDATE `#__osemsc_member` "
						." SET  `expired_date` = {$expired_date}"
						." WHERE `id` = '{$item->member_id}'"
						;
						$db->setQuery($query);
						oseDB::query();
					}
				}
				else
				{
					$result['finish'] = false;
				}
			}

			$result['id'] = $id;
		}
		else
		{
			$result['finish'] =true;
		}

		oseExit(oseJson::encode($result));
	}


	protected function apiCheck($oItem)
	{
		$result = array();
		$db = oseDB::instance();
		static $arb;

		$oseMscConfig= oseMscConfig :: getConfig('payment', 'obj');;

	// Authorize setting
		if(!defined('AUTHORIZENET_API_LOGIN_ID'))
		{
			define("AUTHORIZENET_API_LOGIN_ID", oseObject::getValue($oseMscConfig, 'an_loginid'));
		}
		
		if(!defined('AUTHORIZENET_TRANSACTION_KEY'))
		{
			define("AUTHORIZENET_TRANSACTION_KEY", oseObject::getValue($oseMscConfig, 'an_transkey'));
		}
		
		if(!($arb instanceof AuthnetARB))
		{
			$arb = new AuthnetARB();
			$pConfig= oseMscConfig :: getConfig('payment', 'obj');
			$test_mode= $pConfig->cc_testmode;
			if($test_mode)
			{
				$arb->apitest =  "api.authorize.net";
			}else{
				$arb->url =  "api.authorize.net";
			}
			
			$arb->login = AUTHORIZENET_API_LOGIN_ID;
			$arb->transkey = AUTHORIZENET_TRANSACTION_KEY;
		}
		// End

		// init Beanstream

		// End
//oseExit($oItem->payment_method);
		$oParams = oseJson::decode($oItem->params);
		if($oItem->payment_method == 'authorize')
		{
			if(empty($oItem->payment_serial_number))
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('ERROR');
				
				oseExit(oseJson::encode($result));
			}
			
			if($oItem->payment_mode == 'm')
			{
				$vals = array();
				// update expired date
				$vals['id'] = $obj->id;
				//$vals['status'] = 0;
				$vals['virtual_status'] = 0;
				$vals['eternal'] = 0;
				//$vals['expired_date'] = oseHtml::getDateTime();
				oseDB::update('#__osemsc_member','id',$vals);
			}
			else
			{
				$arb->setParameter('subscrId', $oItem->payment_serial_number);
				$arb->getSubscriptionStatus();
				//oseExit($arb);
				$response = $arb->status;
				$vals = array();
				if( strtolower($response) == 'active' && $arb->getResponseCode() == 'I00001' && $arb->isSuccessful())
				{
					$recurrence_num = oseObject::getValue($oParams, 'p3');
					$recurrence_unit = oseObject::getValue($oParams, 't3');
					if($obj->expired_date == '0000-00-00 00:00:00')
					{
						$expired_date = " DATE_ADD(`start_date` ,INTERVAL {$recurrence_num} {$recurrence_unit}) ";
					}
					else
					{
						$expired_date = " DATE_ADD(`expired_date` ,INTERVAL {$recurrence_num} {$recurrence_unit}) ";
					}

					$query = " UPDATE `#__osemsc_member` "
							." SET  `status` = 1 ,`eternal`=0, `expired_date` = {$expired_date}"
							." WHERE `id` = '{$oItem->member_id}' AND `expired_date` < DATE_ADD(NOW(),INTERVAL 2 DAY)"
							;
					$db->setQuery($query);
					$updated = oseDB::query();
					
					$result['finish'] = false;
					$result['success'] = true;
					$result['title'] = JText::_('SUCCESS');
					$result['content'] = JText::_('SUCCESS');
				}
				elseif(substr($arb->getResponseCode(), 0,1) == 'E')
				{
					$vals['id'] = $oItem->member_id;
					$vals['virtual_status'] = 0;
					//$vals['status'] = 0;
					$vals['eternal'] = 0;
					//$vals['expired_date'] = oseHtml::getDateTime();
					//oseDB::update('#__osemsc_member','id',$vals);
						
					/*$ofVals = array();
					$ofVals['id'] = $oItem->fix_id;
					$ofVals['status'] = 'fixed';
					oseDB::update("#__osemsc_order_fix",'id',$ofVals);*/
					
					$result['finish'] = true;
					$result['success'] = false;
					$result['title'] = JText::_('ERROR');
					$result['content'] = $arb->getResponse();
				}
				else
				{
					// update expired date
					$vals['id'] = $oItem->member_id;
					$vals['virtual_status'] = 0;
					//$vals['status'] = 0;
					$vals['eternal'] = 0;
					//$vals['expired_date'] = oseHtml::getDateTime();
					oseDB::update('#__osemsc_member','id',$vals);
					
					/*$ofVals = array();
					$ofVals['id'] = $oItem->fix_id;
					$ofVals['status'] = 'fixed';
					oseDB::update("#__osemsc_order_fix",'id',$ofVals);*/
					
					
					$result['finish'] = true;
					$result['success'] = true;
					$result['title'] = JText::_('SUCCESS');
					$result['content'] = JText::_('SUCCESS');
				}
				return $result;
			}
			// if find expired date, updated it
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('Only Support Authorize');
				
			return $result;
		}
	}
	
	private function parseResults($response) {
		$c_mccomb= "\n";
		$return= array();
		$var= explode($c_mccomb, $response);
		$lastrow= $var[count($var) - 1];
		$lastrow= explode("&", $lastrow);
		foreach($lastrow as $row) {
			$row= explode("=", $row);
			$return[$row[0]]= $row[1];
		}
		return $return;
	}
	
	function markuseless()
	{
		$db = oseDB::instance();
		
		$result = array();
		$id = JRequest::getInt('id');
		
		$vals = array();
		$vals['order_id'] = $id;
		$vals['order_status'] = 'expired';
		
		oseDB::update('#__osemsc_order','order_id',$vals);
		
		$result['success'] = true;
		$result['title'] = JText::_('SUCCESS');
		$result['content'] = JText::_('SUCCESS');
		
		oseExit(oseJson::encode($result));
	}
	
	protected function addOrderInfoFix($order_id)
	{
		$result = array();

		$db = oseDB::instance();

		$query = " SELECT * FROM `#__osemsc_order_fix`"
		." WHERE `order_id` = '{$order_id}'"
		;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');

		if(empty($item))
		{
			$query = " SELECT b.*,a.entry_id,a.order_item_id"
			." FROM `#__osemsc_order_item` AS a"
			." INNER JOIN `#__osemsc_order` AS b ON b.order_id = a.order_id"
			." WHERE a.`order_id` = '{$order_id}'"
			;
			$db->setQuery($query);
			$oInfo = oseDB::loadItem('obj');
				
			$query = " SELECT * FROM `#__osemsc_member`"
			." WHERE `msc_id` = '{$oInfo->entry_id}' AND `member_id`='{$oInfo->user_id}'"
			;
			$db->setQuery($query);
			$mItem = oseDB::loadItem('obj');
				
			if(!empty($mItem))
			{

				// update the order paypal status
				$ofVals = array();
				$ofVals['order_id'] = $oInfo->order_id;
				$ofVals['order_item_id'] = $oInfo->order_item_id;

				$ofVals['member_id'] = $mItem->id;
				$ofVals['msc_id'] = $mItem->msc_id;
				$ofVals['user_id'] = $mItem->member_id;

				$ofVals['hasParams'] = 1;
				$ofVals['payment_method'] = $oInfo->payment_method;
				$ofVals['payment_mode'] = 'a';
				$ofVals['status'] = 'updated';
				$ofVals['create_date'] = oseHtml::getDateTime();
				$ofVals['params'] = $oInfo->params;

				$user = JFactory::getUser($mItem->member_id);
				$ofVals['email'] = $user->email;

				oseDB::insert("#__osemsc_order_fix",$ofVals);

			}
		}

	}
}