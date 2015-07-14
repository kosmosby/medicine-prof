<?php
/**
  * @version       1.0 +
  * @package       Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Reporter - com_osereporter
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 24-May-2011
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
defined('_JEXEC') or die('Restricted access');
class osereporterModelMemlist extends osereporterModel
{
	//var $prefix = 'mig_';
	function __construct()
	{
		parent :: __construct();
	}
	function getList()
	{
		$db= oseDB :: instance();
		$start= JRequest :: getInt('start', 0);
		$limit= JRequest :: getInt('limit', 0);
		$msc_id= JRequest :: getInt('msc_id', 0);
		$start_date= JRequest :: getVar('start_date', 0);
		$end_date= JRequest :: getVar('end_date', 0);
		$where= array();
		if(!empty($msc_id))
		{
			$where[]= 'mem.`msc_id` = '.$msc_id;
		}
		if(!empty($start_date))
		{
			$where[]= "DATE(mem.`start_date`) >= DATE('{$start_date}')";
		}
		if(!empty($end_date))
		{
			$where[]= "DATE(mem.`expired_date`) <= DATE('{$end_date}')";
		}
		$where=(count($where) ? ' WHERE ('.implode(') AND (', $where).')' : '');
		$db= oseDB :: instance();
		$query= " SELECT * FROM `#__osemsc_member_view` as mem".		" INNER JOIN `#__osemsc_billinginfo` as bil".		" ON mem.`member_id` = bil.`user_id`".		$where;
		$db->setQuery($query);
		$list= $db->loadObjectList();
		$total= count($list);
		$query= " SELECT * FROM `#__osemsc_member_view` as mem".		" INNER JOIN `#__osemsc_billinginfo` as bil".		" ON mem.`member_id` = bil.`user_id`".		$where;
		$db->setQuery($query, $start, $limit);
		$objs= $db->loadObjectList();
		$items= array();
		$item= array();
		$i= 0;
		foreach($objs as $obj)
		{
			$item['id']= $i;
			$item['member_id']= $obj->member_id;
			$item['firstname']= $obj->firstname;
			$item['lastname']= $obj->lastname;
			$item['company']= $obj->company;
			$item['email']= $obj->email;
			$item['address1']= $obj->addr1;
			$item['address2']= $obj->addr2;
			$item['city']= $obj->city;
			$item['state']= self::getStateName($obj->state);
			$item['country']= self::getCountryName($obj->country);
			$item['postcode']= $obj->postcode;
			$item['telephone'] = $obj->telephone;
			$item['username']= $obj->username;
			$item['msc']= $obj->msc_name;
			$item['start_date']= date("Y-m-d", strtotime($obj->start_date));
			if($obj->expired_date == '0000-00-00 00:00:00')
			{
				$item['end_date']= '0000-00-00';
			}
			else
			{
				$item['end_date']= date("Y-m-d", strtotime($obj->expired_date));
			}
			$memParams= oseJSON :: decode($obj->memParams);
			$order_id= $memParams->order_id;
			$query= "SELECT * FROM `#__osemsc_order` WHERE `order_id` = '{$order_id}'";
			$db->setQuery($query);
			$orderInfo= $db->loadObject();
			$orderParams= empty($orderInfo->params) ? null : oseJSON :: decode($orderInfo->params);
			$item['create_date']= empty($orderInfo->create_date) ? null : date("Y-m-d", strtotime($orderInfo->create_date));
			$item['subtotal']= empty($orderParams->subtotal) ? 0 : $orderParams->subtotal.' '.$orderInfo->payment_currency;
			$item['tax']= empty($orderParams->gross_tax) ? 0 : $orderParams->gross_tax.' '.$orderInfo->payment_currency;
			$item['total']= empty($orderParams->total) ? 0 : $orderParams->total.' '.$orderInfo->payment_currency;
			$payment_method= empty($orderInfo->payment_method) ? null : $orderInfo->payment_method;
			$item['payment_method']= self :: transPaymentMethod($payment_method);
			$items[]= $item;
			$i++;
		}
		$result= array();
		$result['total']= $total;
		$result['results']= $items;
		return $result;
	}
	function getMscList()
	{
		$db= oseDB :: instance();
		$query= "SELECT * FROM `#__osemsc_acl`";
		$db->setQuery($query);
		$list= $db->loadObjectList();
		$result= array();
		$result['total']= count($list);
		$result['results']= $list;
		return $result;
	}
	function getStartDateList()
	{
		$db= oseDB :: instance();
		$query= " SELECT DATE(mem.start_date) as sdate FROM `#__osemsc_member_view` as mem"		//." INNER JOIN `#__osemsc_acl` as acl"
	//." ON mem.`msc_id` = acl.`id`"
	." GROUP BY DATE(mem.`start_date`)";
		$db->setQuery($query);
		$objs= $db->loadObjectList();
		$result= array();
		$result['total']= count($objs);
		$result['results']= $objs;
		return $result;
	}
	function getEndDateList()
	{
		$db= oseDB :: instance();
		$query= " SELECT DATE(mem.expired_date) as edate FROM `#__osemsc_member_view` as mem"		//." INNER JOIN `#__osemsc_acl` as acl"
	//." ON mem.`msc_id` = acl.`id`"
	." GROUP BY DATE(mem.`expired_date`)";
		$db->setQuery($query);
		$objs= $db->loadObjectList();
		$result= array();
		$result['total']= count($objs);
		$result['results']= $objs;
		return $result;
	}
	function exportCsv()
	{
		$db= oseDB :: instance();
		$out= null;
		$array= array();
		$array['member_id']= JText :: _('Member ID');
		$array['firstname']= JText :: _('First Name');
		$array['lastname']= JText :: _('Last Name');
		$array['company']= JText :: _('Company');
		$array['address1']= JText :: _('Address1');
		$array['address2']= JText :: _('Address2');
		$array['city']= JText :: _('City');
		$array['state']= JText :: _('State');
		$array['country']= JText :: _('County');
		$array['postcode']= JText :: _('Zip');
		$array['telephone']= JText :: _('Phone');
		$array['username']= JText :: _('User Name');
		$array['email']= JText :: _('e-mail');
		$array['msc']= JText :: _('Membership Plan');
		$array['start_date']= JText :: _('Start Date');
		$array['end_date']= JText :: _('End Date');
		$array['create_date']= JText :: _('Purchase Date');
		$array['subtotal']= JText :: _('Subtotal');
		$array['tax']= JText :: _('Tax');
		$array['total']= JText :: _('Total');
		$array['payment_method']= JText :: _('Payment Method');
		$out .= implode(',', $array);
		$out .= "\n";
		$msc_id= JRequest :: getInt('msc_id', 0);
		$where= array();
		if(!empty($msc_id))
		{
			$where[]= 'mem.`msc_id` = '.$msc_id;
		}
		$where=(count($where) ? ' WHERE ('.implode(') AND (', $where).')' : '');
		$query= " SELECT * FROM `#__osemsc_member_view` as mem".		" INNER JOIN `#__osemsc_billinginfo` as bil".		" ON mem.`member_id` = bil.`user_id`".		$where;
		$db->setQuery($query);
		$objs= $db->loadObjectList();
		//$time = oseHTML::getDateTime();
		$time= date("Y-m-d");
		$filename= "member-list-".$time.".csv";
		$csv_terminated= "\n";
		$csv_separator= ",";
		$csv_enclosed= '"';
		$csv_escaped= "\\";
		foreach($objs as $key => $obj)
		{
			$array= array();
			$array['member_id']= $obj->member_id;
			$array['firstname']= $obj->firstname;
			$array['lastname']= $obj->lastname;
			$array['company']= $obj->company;
			$array['address1']= $obj->addr1;
			$array['address2']= $obj->addr2;
			$array['city']= $obj->city;
			$array['state']= self::getStateName($obj->state);
			$array['country']= self::getCountryName($obj->country);
			$array['postcode']= $obj->postcode;
			$array['telephone']= $obj->telephone;
			$array['username']= $obj->username;
			$array['email']= $obj->email;
			$array['msc']= $obj->msc_name;
			$array['start_date']= date("Y-m-d", strtotime($obj->start_date));
			if($obj->expired_date == '0000-00-00 00:00:00')
			{
				$array['end_date']= '0000-00-00';
			}
			else
			{
				$array['end_date']= date("Y-m-d", strtotime($obj->expired_date));
			}
			$memParams= oseJSON :: decode($obj->memParams);
			$order_id= $memParams->order_id;
			$query= "SELECT * FROM `#__osemsc_order` WHERE `order_id` = '{$order_id}'";
			$db->setQuery($query);
			$orderInfo= $db->loadObject();
			$orderParams= empty($orderInfo->params) ? null : oseJSON :: decode($orderInfo->params);
			$array['create_date']= empty($orderInfo->create_date) ? null : date("Y-m-d", strtotime($orderInfo->create_date));
			$array['subtotal']= empty($orderParams->subtotal) ? 0 : $orderParams->subtotal.' '.$orderInfo->payment_currency;
			$array['tax']= empty($orderParams->gross_tax) ? 0 : $orderParams->gross_tax.' '.$orderInfo->payment_currency;
			$array['total']= empty($orderParams->total) ? 0 : $orderParams->total.' '.$orderInfo->payment_currency;
			$payment_method= empty($orderInfo->payment_method) ? null : $orderInfo->payment_method;
			$array['payment_method']= self :: transPaymentMethod($payment_method);
			foreach($array as $akey => $arr)
			{
				$array[$akey] = str_replace(',',';',$array[$akey]);
				$array[$akey] = str_replace('\n',' ',$array[$akey]);
			}
		
			$out .= implode(',', $array);
			$out .= "\n";
		}
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Length: ".strlen($out));
		// Output to browser with appropriate mime type, you choose ;)
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=$filename");
		oseExit($out);
	}
	function transPaymentMethod($payment_method)
	{
		switch($payment_method)
		{
			default :
				return $payment_method;
				break;
			case 'authorize' :
				return 'Authorize.net';
				break;
			case 'paypal_cc' :
				return 'Paypal Credit Card';
				break;
			case 'paypal' :
				return 'PayPal';
				break;
			case 'eway' :
				return 'eWay';
				break;
			case 'epay' :
				return 'ePay';
				break;
			case 'pnw' :
				return 'Payment Network';
				break;
			case 'beanstream' :
				return 'Beanstream';
				break;
			case 'vpcash_cc' :
				return 'VirtualPayCash Credit Card';
				break;
			case 'vpcash' :
				return 'VirtualPayCash';
				break;
			case 'bbva' :
				return 'BBVA';
				break;
			case 'poffline' :
				return 'Pay-Offline';
				break;
		}
	}
	
	function getCountryName($code)
	{
		$db= oseDB :: instance();
		$query = "SELECT country_name FROM `#__osemsc_country` WHERE `country_3_code` = '{$code}'";
		$db->setQuery($query);
		$country = $db->loadResult();
		$country = empty($country)?$code:$country;
		return $country;
	}
	
	function getStateName($code)
	{
		$db= oseDB :: instance();
		$query = "SELECT state_name FROM `#__osemsc_state` WHERE `state_2_code` = '{$code}'";
		$db->setQuery($query);
		$state = $db->loadResult();
		$state = empty($state)?$code:$state;
		return $state;
	}
	function exportCsvAll()
	{
		$db= oseDB :: instance();
		$out= null;
		$array= array();
		$array['member_id']= JText :: _('Member ID');
		$array['firstname']= JText :: _('First Name');
		$array['lastname']= JText :: _('Last Name');
		$array['company']= JText :: _('Company');
		$array['address1']= JText :: _('Address1');
		$array['address2']= JText :: _('Address2');
		$array['city']= JText :: _('City');
		$array['state']= JText :: _('State');
		$array['country']= JText :: _('County');
		$array['postcode']= JText :: _('Zip');
		$array['telephone']= JText :: _('Phone');
		$array['username']= JText :: _('User Name');
		$array['email']= JText :: _('e-mail');
		$array['msc']= JText :: _('Membership Plan');
		$array['start_date']= JText :: _('Start Date');
		$array['end_date']= JText :: _('End Date');
		$array['create_date']= JText :: _('Purchase Date');
		$array['subtotal']= JText :: _('Subtotal');
		$array['tax']= JText :: _('Tax');
		$array['total']= JText :: _('Total');
		$array['payment_method']= JText :: _('Payment Method');
		
		//Additional Info
		$query = " SELECT * FROM `#__osemsc_fields` WHERE `published` = '1' ORDER BY `ordering`";
		$db->setQuery($query);
		$fields = $db->loadObjectList();
		foreach($fields as $field)
		{
			$array['field'.$field->id] = JText::_(ucwords($field->name));
		}
		
		$out .= implode(',', $array);
		$out .= "\n";
		$msc_id= JRequest :: getInt('msc_id', 0);
		$where= array();
		if(!empty($msc_id))
		{
			$where[]= 'mem.`msc_id` = '.$msc_id;
		}
		$where=(count($where) ? ' WHERE ('.implode(') AND (', $where).')' : '');
		$query= " SELECT * FROM `#__osemsc_member_view` as mem".		" INNER JOIN `#__osemsc_billinginfo` as bil".		" ON mem.`member_id` = bil.`user_id`".		$where;
		$db->setQuery($query);
		$objs= $db->loadObjectList();
		//$time = oseHTML::getDateTime();
		$time= date("Y-m-d");
		$filename= "member-all-".$time.".csv";
		$csv_terminated= "\n";
		$csv_separator= ",";
		$csv_enclosed= '"';
		$csv_escaped= "\\";
		foreach($objs as $key => $obj)
		{
			$array= array();
			$array['member_id']= $obj->member_id;
			$array['firstname']= $obj->firstname;
			$array['lastname']= $obj->lastname;
			$array['company']= $obj->company;
			$array['address1']= $obj->addr1;
			$array['address2']= $obj->addr2;
			$array['city']= $obj->city;
			$array['state']= self::getStateName($obj->state);
			$array['country']= self::getCountryName($obj->country);
			$array['postcode']= $obj->postcode;
			$array['telephone']= $obj->telephone;
			$array['username']= $obj->username;
			$array['email']= $obj->email;
			$array['msc']= $obj->msc_name;
			$array['start_date']= date("Y-m-d", strtotime($obj->start_date));
			if($obj->expired_date == '0000-00-00 00:00:00')
			{
				$array['end_date']= '0000-00-00';
			}
			else
			{
				$array['end_date']= date("Y-m-d", strtotime($obj->expired_date));
			}
			$memParams= oseJSON :: decode($obj->memParams);
			$order_id= $memParams->order_id;
			$query= "SELECT * FROM `#__osemsc_order` WHERE `order_id` = '{$order_id}'";
			$db->setQuery($query);
			$orderInfo= $db->loadObject();
			$orderParams= empty($orderInfo->params) ? null : oseJSON :: decode($orderInfo->params);
			$array['create_date']= empty($orderInfo->create_date) ? null : date("Y-m-d", strtotime($orderInfo->create_date));
			$array['subtotal']= empty($orderParams->subtotal) ? 0 : $orderParams->subtotal.' '.$orderInfo->payment_currency;
			$array['tax']= empty($orderParams->gross_tax) ? 0 : $orderParams->gross_tax.' '.$orderInfo->payment_currency;
			$array['total']= empty($orderParams->total) ? 0 : $orderParams->total.' '.$orderInfo->payment_currency;
			$payment_method= empty($orderInfo->payment_method) ? null : $orderInfo->payment_method;
			$array['payment_method']= self :: transPaymentMethod($payment_method);
			
			//Additional Info
			foreach($fields as $field)
			{
				$query = "SELECT value FROM `#__osemsc_fields_values` WHERE `field_id` = ".$field->id." AND `member_id` = ".$obj->member_id;
				$db->setQuery($query);
				$value = $db->loadResult();
				$array['field'.$field->id] = empty($value)?null:$value;
			}
			foreach($array as $akey => $arr)
			{
				$array[$akey] = str_replace(',',';',$array[$akey]);
				$array[$akey] = str_replace('\n',' ',$array[$akey]);
			}

			$out .= implode(',', $array);
			$out .= "\n";
		}
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Length: ".strlen($out));
		// Output to browser with appropriate mime type, you choose ;)
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=$filename");
		oseExit($out);
	}
}
?>