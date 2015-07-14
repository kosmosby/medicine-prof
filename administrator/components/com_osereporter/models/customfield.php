<?php
/**
 * @subpackage OSE Sitemap Generator
 *
 * @author Open Source Excellence {@link  http://www.opensource-excellence.com}
 * @author Created on 01-Apr-2010
 * @copyright Copyright (C) 2010 Open Source Excellence. All rights reserved.
 * @license GNU/GPL v2  http://www.gnu.org/copyleft/gpl.html
 */
// no direct access
defined('_JEXEC') or die('Restricted access');


class osereporterModelCustomfield extends osereporterModel {

	//var $prefix = 'mig_';
	function __construct() {
		parent::__construct();
	}

	function getColumns()
	{
		$db= oseDB::instance();
		$query = "SELECT * FROM `#__osemsc_fields` WHERE `published` = '1' ORDER BY `ordering`";
		$db->setQuery($query);
		$objs = $db->loadObjectList();
		//$array = array();
		$arrays = array();
		$arrays[] = array('id'=>'id', 'header'=>'ID', 'dataIndex'=>'id','width'=>5);
		$arrays[] = array('header'=>'User ID', 'dataIndex'=>'member_id');
		$arrays[] = array('header'=>'Username', 'dataIndex'=>'username');
		$arrays[] = array('header'=>'Name', 'dataIndex'=>'name');
		foreach($objs as $obj)
		{
			$arrays[] = array('header'=>ucwords($obj->name), 'dataIndex'=>'field'.$obj->id);
		}
		//$arrays = array('0'=>array('header'=>ucwords($objs[0]->name), 'dataIndex'=>'member_id'),'1'=>array('id'=>'ID', 'dataIndex'=>'id'));
		$list = oseJSON::encode($arrays);
		return $list;
	}
	function getStore()
	{
		$db= oseDB::instance();
		$query = "SELECT * FROM `#__osemsc_fields` WHERE `published` = '1' ORDER BY `ordering`";
		$db->setQuery($query);
		$objs = $db->loadObjectList();
		$arrays = array();
		$arrays[] = array('name'=>'id','type'=>'int','mapping'=>'id');
		$arrays[] = array('name'=>'member_id','type'=>'int','mapping'=>'member_id');
		$arrays[] = array('name'=>'username','type'=>'string','mapping'=>'username');
		$arrays[] = array('name'=>'name','type'=>'string','string'=>'name');
		foreach($objs as $obj)
		{
			$arrays[] = array('name'=>'field'.$obj->id, 'type'=>'string','mapping'=>'field'.$obj->id);
		}
		$list = oseJSON::encode($arrays);
		return $list;
	}
	function getList()
	{
		$db= oseDB::instance();
		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',0);
		$msc_id = JRequest::getInt('msc_id',0);
		$start_date = JRequest::getVar('start_date',0);
		$end_date = JRequest::getVar('end_date',0);

		$where = array();
		if(!empty($msc_id))
		{
			$where[] = 'mem.`msc_id` = '.$msc_id;
		}

		if(!empty($start_date))
		{
			$where[] = "DATE(mem.`start_date`) >= DATE('{$start_date}')";
		}

		if(!empty($end_date))
		{
			$where[] = "DATE(mem.`expired_date`) <= DATE('{$end_date}')";
		}
		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );

		$db= oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_member_view` as mem"
				//." INNER JOIN `#__osemsc_billinginfo` as bil"
				//." ON mem.`member_id` = bil.`user_id`"
				.$where
				." GROUP BY mem.`member_id`";

		$db->setQuery($query);
		$list = $db->loadObjectList();
		$total = count($list);

		$query = " SELECT * FROM `#__osemsc_member_view` as mem"
				//." INNER JOIN `#__osemsc_billinginfo` as bil"
				//." ON mem.`member_id` = bil.`user_id`"
				
				.$where
				." GROUP BY mem.`member_id`";
		$db->setQuery($query,$start,$limit);
		$objs = $db->loadObjectList();
		$items = array();
		$item = array();
		$i=1;

		foreach($objs as $obj)
		{
			$item['id'] = $i;
			$item['member_id'] = $obj->member_id;
			$item['username'] = $obj->username;
			$item['name'] = $obj->name;
			$query = "SELECT * FROM `#__osemsc_fields_values` WHERE `member_id` = ".$obj->member_id;
			$db->setQuery($query);
			$values = $db->loadObjectList();
			if(!empty($values))
			{
				foreach($values as $value)
				{
					 $item['field'.$value->field_id] = $value->value;
				}
			}
			$i++;
			$items[] = $item;
			unset($item);
		}
		$result = array();
		$result['total'] = $total;
		$result['results'] = $items;

		return $result;
	}

	function getMscList()
	{
		$db= oseDB::instance();
		$query = "SELECT * FROM `#__osemsc_acl`";
		$db->setQuery($query);
		$list = $db->loadObjectList();
		$result = array();
		$result['total'] = count($list);
		$result['results'] = $list;
		return $result;
	}

	function getStartDateList()
	{
		$db= oseDB::instance();
		$query = " SELECT DATE(mem.start_date) as sdate FROM `#__osemsc_member_view` as mem"
				//." INNER JOIN `#__osemsc_acl` as acl"
				//." ON mem.`msc_id` = acl.`id`"
				." GROUP BY DATE(mem.`start_date`)";
		$db->setQuery($query);
		$objs = $db->loadObjectList();

		$result = array();
		$result['total'] = count($objs);
		$result['results'] = $objs;
		return $result;
	}

	function getEndDateList()
	{
		$db= oseDB::instance();
		$query = " SELECT DATE(mem.expired_date) as edate FROM `#__osemsc_member_view` as mem"
				//." INNER JOIN `#__osemsc_acl` as acl"
				//." ON mem.`msc_id` = acl.`id`"
				." GROUP BY DATE(mem.`expired_date`)";
		$db->setQuery($query);
		$objs = $db->loadObjectList();

		$result = array();
		$result['total'] = count($objs);
		$result['results'] = $objs;
		return $result;
	}
	function exportCsv()
	{
		$db = oseDB::instance();

		$out = null;
		$array = array();
		$array['member_id'] = JText::_('Member ID');
		$array['username'] = JText::_('User Name');
		$array['name'] = JText::_('Name');
		$query = " SELECT * FROM `#__osemsc_fields` WHERE `published` = '1' ORDER BY `ordering`";
		$db->setQuery($query);
		$fields = $db->loadObjectList();
		foreach($fields as $field)
		{
			$array['field'.$field->id] = JText::_(ucwords($field->name));
		}

		$out .= implode(',',$array);
		$out .= "\n";

		$msc_id = JRequest::getInt('msc_id',0);

		$where = array();
		if(!empty($msc_id))
		{
			$where[] = 'mem.`msc_id` = '.$msc_id;
		}

		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );

		$query = " SELECT * FROM `#__osemsc_member_view` as mem"
				//." INNER JOIN `#__osemsc_billinginfo` as bil"
				//." ON mem.`member_id` = bil.`user_id`"
				.$where
				." GROUP BY mem.`member_id`";
		$db->setQuery($query);
		$objs = $db->loadObjectList();

		//$time = oseHTML::getDateTime();
		$time = date("Y-m-d");

		$filename = "member-additional-info-".$time.".csv";
		$csv_terminated = "\n";
	    $csv_separator = ",";
	    $csv_enclosed = '"';
	    $csv_escaped = "\\";

		foreach($objs as $key => $obj)
		{
			$array = array();
			if(empty($obj->member_id))
			{
				continue;
			}
			$array['member_id'] = $obj->member_id;
			$array['username'] = $obj->username;
			$array['name'] = $obj->name;

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

			$out .= implode(',',$array);
			
			$out .= "\n";
		}

		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Content-Length: " . strlen($out));
	    // Output to browser with appropriate mime type, you choose ;)
	    header("Content-type: text/x-csv");
	    //header("Content-type: text/csv");
	    //header("Content-type: application/csv");
	    header("Content-Disposition: attachment; filename=$filename");

		oseExit($out);
	}

	function transPaymentMethod($payment_method)
	{
		switch($payment_method)
		{
			default:
				return null;
				break;
			case 'authorize':
				return 'Authorize.net';
				break;
			case 'paypal_cc':
				return 'Paypal Credit Card';
				break;
			case 'paypal':
				return 'PayPal';
				break;
			case 'eway':
				return 'eWay';
				break;
			case 'epay':
				return 'ePay';
				break;
			case 'pnw':
				return 'Payment Network';
				break;
			case 'beanstream':
				return 'Beanstream';
				break;
			case 'vpcash_cc':
				return 'VirtualPayCash Credit Card';
				break;
			case 'vpcash':
				return 'VirtualPayCash';
				break;
			case 'bbva':
				return 'BBVA';
				break;
			case 'poffline':
				return 'Pay-Offline';
				break;
		}
	}

}
?>