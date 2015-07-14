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

class oseMscControllerPaypal extends oseMscController
{
	public function __construct()
	{
		parent::__construct();
	} //function 
	

	function updatePaypalOrderInfo()
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
					." AND b.`payment_method` = 'paypal'"
			." ORDER BY b.`order_id` DESC"
					." LIMIT 1"
					;
			$db->setQuery($query);
			$oItem = oseDB::loadItem();
			
			unset($oItem['order_id']);
			$oItem['order_number'] = (empty($order_number))?$this->generateOrderNumber($item->user_id):$order_number;
			$oItem['create_date'] = oseHtml::getDateTime();
			//$oItem['payment_serial_number'] = $serial_number;
			$oItem['payment_method'] = 'paypal';
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
			$ofVals['payment_method'] = 'paypal';
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
			." AND b.`payment_method` = 'paypal'"
			." ORDER BY b.`order_id` DESC"
			." LIMIT 1"
			;
			$db->setQuery($query);
			$oItem = oseDB::loadItem();

			unset($oItem['order_id']);
			$oItem['order_number'] = (empty($order_number))?$this->generateOrderNumber($item->user_id):$order_number;
			$oItem['create_date'] = oseHtml::getDateTime();
			//$oItem['payment_serial_number'] = $serial_number;
			$oItem['user_id'] = $memInfo->member_id;
			$oItem['payment_method'] = 'paypal';

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
			$ofVals['payment_method'] = 'paypal';
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
			
			/*if(!in_array( $item->payment_method,array('paypal','system','') ) )
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
			
			$oItem['payment_mode'] = 'a';
			$oItem['payment_method'] = 'paypal';
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
			
			if(!empty($email))
			{
				$oItem['email'] = $email;
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
	
	function updateMemberExpiryDate()	
	{
		$result = array();
		$id = JRequest::getInt('id');
		$expired_date = JRequest::getString('expired_date');

		$db = oseDB::instance();

		$query = " SELECT * FROM `#__osemsc_order_fix`"
				." WHERE `id` = '{$id}'"
				;
		$db->SetQuery($query);
		$item = oseDB::loadItem('obj');

		$query = " SELECT * FROM `#__osemsc_member`"
				." WHERE `id` = '{$item->member_id}'"
				;
		$db->SetQuery($query);
		$member = oseDB::loadItem('obj');
		
		$eDate = explode(' ',$member->expired_date);
		$expired_date = $expired_date.' '.$eDate[1];
		
		// update member table params
		$mVals = array();
		$mVals['id'] = $item->member_id;
		$mVals['expired_date'] = $expired_date;
		oseDB::update("#__osemsc_member",'id',$mVals);


		$result['success'] = true;
		$result['title'] = JText::_('SUCCESS');
		$result['content'] = JText::_('SUCCESS');

		$result = oseJSON::encode($result);
		oseExit($result);
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
					
					$user = JFactory::getUser($mItem->member_id);
					$oItem->email = $user->email;
					
					$ofVals['email'] = $user->email;
					
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
			$query = " SELECT a.`email`,b.`order_number`,b.`payment_serial_number`,b.`payment_price`,b.`order_id`"
			." FROM `#__osemsc_order_fix`AS a"
			." INNER JOIN `#__osemsc_order` AS b on a.`order_id`=b.`order_id`"
			." WHERE a.`order_id` = '{$item->order_id}'"
			;
			$db->setQuery($query);
			$oItem = oseDB::loadItem('obj');
			
			if(empty($oItem->email))
			{
				$user = JFactory::getUser($item->user_id);
				$oItem->email = $user->email;
			}
			$oItem->id = $id;
			$result['success'] = true;
			$result['data'] = $oItem;
		}
		
		oseExit(oseJson::encode($result));
	}	
	
	function validate()
	{
		$db = oseDB::instance();

		$result = array();
		$id = JRequest::getInt('id');
		$this->addOrderInfoFix($id);
		$query = " SELECT b.*,a.member_id,a.msc_id,a.id AS `fix_id`,a.email"
				." FROM `#__osemsc_order_fix` AS a"
				." INNER JOIN `#__osemsc_order` AS b ON b.order_id = a.order_id"
				." WHERE b.`order_id` = '{$id}'"
				." LIMIT 1"
				;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		
	
		/*$query = " SELECT b.*"
				." FROM `#__osemsc_order` AS b"
				." WHERE b.`order_id` = '{$id}'"
				." LIMIT 1"
				;
								
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		
		$query = " SELECT a.member_id,a.msc_id,a.id AS `fix_id`,a.email"
				." FROM `#__osemsc_order_fix` AS a"
				." WHERE a.`order_id` = '{$id}'"
				." LIMIT 1"
				;
										
		$db->setQuery($query);
		$fitem = oseDB::loadItem('obj');
		if(empty($fitem))
		{
			$vals = array();
			$vals['order_id'] = $id;
			$fix_id = oseDB::insert('#__osemsc_order_fix',$vals);
			
			$item->fix_id = $fix_id;
		}
		else
		{
			$item->member_id = $fitem->member_id;
			$item->msc_id = $fitem->msc_id;
			$item->fix_id = $fitem->fix_id;
			$item->email = $fitem->email;
		}*/
		
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
					/*
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
					*/
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
			$arb->url =  "apitest.authorize.net";

			$arb->login = AUTHORIZENET_API_LOGIN_ID;
			$arb->transkey = AUTHORIZENET_TRANSACTION_KEY;
		}
		// End

		// init Beanstream

		// End

		$oParams = oseJson::decode($oItem->params);
		
		if($oItem->payment_method == 'paypal')
		{
			$user = JFactory::getUser($oItem->user_id);

			$postVar = array();

			//$start_time_o = '2012-05-01 00:00:00';
			$start_time = strtotime("-1 year");
			$iso_start = date('Y-m-d\T00:00:00\Z',  $start_time);

			$end_time = strtotime('2011-04-01 00:00:00');
			$iso_end = date('Y-m-d\T24:00:00\Z',  $end_time);

			$postVar['METHOD'] = 'TransactionSearch';
			$postVar['STARTDATE'] = $iso_start;
			//$postVar['INVNUM'] = $oItem->order_number;
			$postVar['EMAIL'] = empty($oItem->email)?$user->email:$oItem->email;
			//oseExit($postVar);
			$postString=null;
			foreach($postVar AS $key => $val) {
				$postString .= "&".urlencode($key)."=".urlencode($val);
			}
			
			
			$resArray= self :: PaypalAPIConnect($postString);
			//$resArray1= self :: PaypalAPIConnect1($postString);
			
			//if( in_array('Failure',array($resArray['ACK'],$resArray1['ACK']) ))
			if( in_array('Failure',array($resArray['ACK']) ))
			{
				$result['finish'] = false;
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = urldecode($resArray['L_LONGMESSAGE0']);

				return $result; 
			}
			
			/*if(!empty($resArray['L_STATUS0']))
			{
				if( !empty($resArray1['L_STATUS0']) )
				{
					$ed = date_create( urldecode($resArray['L_TIMESTAMP0'] ));
					$ed = date_format($ed, 'Y-m-d H:i:s');
					
					$ed1 = date_create( urldecode($resArray1['L_TIMESTAMP0'] ));
					$ed1 = date_format($ed1, 'Y-m-d H:i:s');
					
					if($ed < $ed1)
					{
						$resArray = $resArray1;
					}
				}
			}
			elseif( !empty($resArray1['L_STATUS0']) )
			{
				$resArray = $resArray1;
			}*/
			
			//oseExit($resArray);
			if(!empty($resArray['L_STATUS0']))
			{
				$trnArray = array();
				foreach($resArray as $k => $v)
				{
					if( strpos( $k,'L_STATUS') !== false )
					{
						$trnArray[$k] = $v;
					}
					else
					{
						continue;
					}
				}
		
				$success = false;$next = false;
				foreach($trnArray as $k => $v)
				{
					if($next)
					{
						$next = false;
						continue;
					}
					
					$nk = str_replace('L_STATUS','',$k);
					
					if($v == 'Completed' || $v == 'Unclaimed')
					{
						$success = true;
						$expDate = urldecode($resArray['L_TIMESTAMP'.$nk]);
						$expDate = new DateTime($expDate);
						$expired_date = $expDate->format('Y-m-d H:i:s');
							
						$recurrence_num = oseObject::getValue($oParams, 'p3');
						$recurrence_unit = oseObject::getValue($oParams, 't3');
						
						$expired_date = " DATE_ADD('{$expired_date}' ,INTERVAL {$recurrence_num} {$recurrence_unit}) ";
						
						$query = " UPDATE `#__osemsc_member` "
						." SET `eternal`=0, `expired_date` = {$expired_date}"
						." WHERE `id` = '{$oItem->member_id}'"
						;
						$db->setQuery($query);
						$updated = oseDB::query();
						
						$query = " UPDATE `#__osemsc_member` "
						." SET  `status` = 1 "
						." WHERE `id` = '{$oItem->member_id}' AND `expired_date` > NOW()"
						;
						$db->setQuery($query);
						$updated = oseDB::query();
						
						$ofVals = array();
						$ofVals['id'] = $oItem->fix_id;
						$ofVals['status'] = 'fixed';
						oseDB::update("#__osemsc_order_fix",'id',$ofVals);
						
						
						$ofVals = array();
						$ofVals['order_id'] = $oItem->order_id;
						
						$query = " SELECT COUNT(*) FROM `#__osemsc_member`"
						." WHERE `id` = '{$oItem->member_id}' AND `expired_date` > NOW()"
						;
						$db->setQuery($query);
						$needChanged = $db->loadResult();
						
						if($needChanged > 0 && $v == 'Completed')
						{
							$ofVals['order_status'] = 'confirmed';
						}
						elseif($v == 'Unclaimed')
						{
							$ofVals['order_status'] = 'confirmed';
						}
						else
						{
							$ofVals['order_status'] = 'skipped';
						}
						
						oseDB::update("#__osemsc_order",'order_id',$ofVals);
						
						$result['finish'] = true;
						$result['success'] = true;
						$result['title'] = JText::_('SUCCESS');
						$result['content'] = JText::_('SUCCESS');
						break;
					}
					elseif($v == 'Unclaimed1')
					{
						if($k == 0)
						{
							$ofVals = array();
							$ofVals['order_id'] = $oItem->order_id;
							$ofVals['order_status'] = 'skipped';
							oseDB::update("#__osemsc_order",'order_id',$ofVals);
						}
						
						continue;
					}
					else
					{
						if($k == 0)
						{
							$ofVals = array();
							$ofVals['order_id'] = $oItem->order_id;
							$ofVals['order_status'] = 'skipped';
							//oseDB::update("#__osemsc_order",'order_id',$ofVals);
						}
						continue;
						$next = true;
					}
				}
				
				if( !$success )
				{
					$vals['id'] = $obj->id;
					$vals['virtual_status'] = 0;
					//$vals['status'] = 0;
					$vals['eternal'] = 0;
					$vals['expired_date'] = oseHtml::getDateTime();
					//oseDB::update('#__osemsc_member','id',$vals);
						
					$result['finish'] = true;
					$result['success'] = false;
					$result['title'] = JText::_('ERROR');
					
					if(count($trnArray) > 3)
					{
						$result['content'] = JText::_('Maybe the subscription is cancelled');
					}
					elseif(count($trnArray) == 3)
					{
						$result['content'] = JText::_('Maybe the first payment is cancelled');
					}
					else
					{
						$vals = array();
						$vals['id'] = $oItem->member_id;
						$vals['virtual_status'] = 0;
						//$vals['status'] = 0;
						$vals['eternal'] = 0;
						//$vals['expired_date'] = oseHtml::getDateTime();
						oseDB::update('#__osemsc_member','id',$vals);
						
						$result['content'] = JText::_('No Success Payment has been searched from ').$start_time;
					}
					
				}
				/*
				if( $resArray['L_STATUS0'] == 'Completed' )
				{
					$expDate = urldecode($resArray['L_TIMESTAMP0']);
					$expDate = new DateTime($expDate);
					$expired_date = $expDate->format('Y-m-d H:i:s');
						
					$recurrence_num = oseObject::getValue($oParams, 'p3');
					$recurrence_unit = oseObject::getValue($oParams, 't3');

					$expired_date = " DATE_ADD('{$expired_date}' ,INTERVAL {$recurrence_num} {$recurrence_unit}) ";
					//oseExit($expired_date);
					$query = " UPDATE `#__osemsc_member` "
					." SET  `status` = 1 ,`eternal`=0, `expired_date` = {$expired_date}"
					." WHERE `id` = '{$oItem->member_id}'"
					;
					$db->setQuery($query);
					$updated = oseDB::query();

					$ofVals = array();
					$ofVals['id'] = $oItem->fix_id;
					$ofVals['status'] = 'fixed';
					oseDB::update("#__osemsc_order_fix",'id',$ofVals);

					$result['finish'] = false;
					$result['success'] = true;
					$result['title'] = JText::_('SUCCESS');
					$result['content'] = JText::_('SUCCESS');
				}
				// mark it expired
				else
				{
					$vals['id'] = $obj->id;
					$vals['virtual_status'] = 0;
					//$vals['status'] = 0;
					$vals['eternal'] = 0;
					$vals['expired_date'] = oseHtml::getDateTime();
					//oseDB::update('#__osemsc_member','id',$vals);

					$result['finish'] = true;
					$result['success'] = true;
					$result['title'] = JText::_('SUCCESS');
					$result['content'] = JText::_('SUCCESS');
				}*/
			}
			else
			{
				// mark it expired
					
				$vals['id'] = $obj->id;
				$vals['virtual_status'] = 0;
				//$vals['status'] = 0;
				$vals['eternal'] = 0;
				$vals['expired_date'] = oseHtml::getDateTime();
				//oseDB::update('#__osemsc_member','id',$vals);
					
				$result['finish'] = true;
				$result['success'] = true;
				$result['title'] = JText::_('SUCCESS');
				$result['content'] = JText::_('SUCCESS');
			}
			
				
			return $result;
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('Only Support Paypal Recurring Payment');
				
			return $result;
		}
	}
	
	private function PaypalAPIConnect($postString) {
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'libraries'.DS.'class.connection.php');
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		$test_mode= $pConfig->paypal_testmode;
		$API_UserName= $pConfig->paypal_api_username;
		$API_Password= $pConfig->paypal_api_passwd;
		$API_Signature= $pConfig->paypal_api_signature;
		
		
		
		$subject= '';
		if(empty($API_UserName) || empty($API_Password) || empty($API_Signature)) {
			return false;
		}
		define('VERSION', '64.0');
		define('ACK_SUCCESS', 'SUCCESS');
		define('ACK_SUCCESS_WITH_WARNING', 'SUCCESSWITHWARNING');
		if($test_mode == true) {
			$API_Endpoint= 'api-3t.sandbox.paypal.com';
			$Paypal_URL= 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
		} else {
			$API_Endpoint= 'api-3t.paypal.com';
			$Paypal_URL= 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';
		}
		$postVar['PWD']= $API_Password;
		$postVar['USER']= $API_UserName;
		$postVar['SIGNATURE']= $API_Signature;
		$postVar['VERSION']= VERSION;
		$postHead= '';
		foreach($postVar AS $key => $val) {
			$postHead .= "&".urlencode($key)."=".$val;
		}
		$postString= $postString.$postHead;
		$response= OSECONNECTOR :: send_request_via_fsockopen($API_Endpoint, '/nvp', $postString,'urlencoded');
		$resArray= self :: parseResults($response);
		$resArray["Paypal_URL"]= $Paypal_URL;
		return $resArray;
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