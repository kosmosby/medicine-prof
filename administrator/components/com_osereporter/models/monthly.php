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


class osereporterModelMonthly extends osereporterModel {

	//var $prefix = 'mig_';
	function __construct() {
		parent::__construct();
	}

	function getList()
	{
		$db= oseDB::instance();
		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',0);
		$msc_id = JRequest::getInt('msc_id',0);
		$year = JRequest::getInt('year',0);

		$where = array();
		if(!empty($msc_id))
		{
			$where[] = 'mem.`msc_id` = '.$msc_id;
		}

		if(!empty($year))
		{
			$where[] = 'YEAR(mem.`start_date`) = '.$year;
		}

		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );

		$db= oseDB::instance();
		$query = " SELECT * FROM `#__osemsc_member` as mem"
				." INNER JOIN `#__osemsc_acl` as acl"
				." ON mem.`msc_id` = acl.`id`"
				.$where
				." GROUP BY mem.`msc_id`, YEAR(mem.`start_date`), MONTH(mem.`start_date`)";
		$db->setQuery($query);
		$list = $db->loadObjectList();
		$total = count($list);

		$query = " SELECT DATE(mem.start_date) as date, acl.title as msc, mem.msc_id FROM `#__osemsc_member` as mem"
				." INNER JOIN `#__osemsc_acl` as acl"
				." ON mem.`msc_id` = acl.`id`"
				.$where
				." GROUP BY mem.`msc_id`, YEAR(mem.`start_date`), MONTH(mem.`start_date`) ORDER BY DATE(mem.`start_date`) DESC";
		$db->setQuery($query,$start,$limit);
		$objs = $db->loadObjectList();
		$items = array();
		$item = array();
		$i=1;
		foreach($objs as $obj)
		{
			$item['id'] = $i;
			$item['date'] = date("Y-m",strtotime($obj->date));
			$item['msc'] = $obj->msc;
			$item['msc_id'] = $obj->msc_id;
			$query = "SELECT count(*) FROM `#__osemsc_member` WHERE `msc_id` = '{$obj->msc_id}' AND YEAR(`start_date`) = YEAR('{$obj->date}') AND MONTH(`start_date`) = MONTH('{$obj->date}') AND `status` = 1";
			$db->setQuery($query);
			$item['newmem'] = $db->loadResult();

			$query = "SELECT count(*) FROM `#__osemsc_member` WHERE `msc_id` = '{$obj->msc_id}' AND YEAR(`expired_date`) = YEAR('{$obj->date}') AND MONTH(`expired_date`) = MONTH('{$obj->date}') AND `status` = 0";
			$db->setQuery($query);
			$item['expmem'] = $db->loadResult();

			$ratio = round($item['expmem']/$item['newmem']*100,2);
			$item['ratio'] = $ratio.'%';
			
			$query = " SELECT SUM(ord.payment_price) as profits,ord.payment_currency FROM `#__osemsc_order` AS ord"
					." INNER JOIN `#__osemsc_order_item` AS ooi"
					." ON ord.`order_id` = ooi.`order_id`"
					." WHERE ooi.`entry_id` = '{$obj->msc_id}' AND YEAR(ord.`create_date`) = YEAR('{$obj->date}') AND MONTH(ord.`create_date`) = MONTH('{$obj->date}') AND ord.`order_status` = 'confirmed'" 
					." GROUP BY ord.`payment_currency`";
			$db->setQuery($query);
			$profits = $db->loadObjectList();
			$price = null;
			if(empty($profits))
			{
				$item['profits'] = '0.00';
			}else
			{
				foreach($profits as $profit)
				{
					if(!empty($profit->profits) && !empty($profit->payment_currency))
					{
						$price.= $profit->profits.' '.$profit->payment_currency.'   ,';
					}
				}
				$item['profits'] = trim($price,",");
			}

			//Order tax
			$query = " SELECT ord.* FROM `#__osemsc_order` AS ord"
					." INNER JOIN `#__osemsc_order_item` AS ooi"
					." ON ord.`order_id` = ooi.`order_id`"
					." WHERE ooi.`entry_id` = '{$obj->msc_id}' AND YEAR(ord.`create_date`) = YEAR('{$obj->date}') AND MONTH(ord.`create_date`) = MONTH('{$obj->date}') AND ord.`order_status` = 'confirmed'" 
					;
			$db->setQuery($query);
			$orders = $db->loadObjectList();
			$array = array();
			foreach($orders as $order)
			{
				$orderParams = oseJSON :: decode($order->params);
				$array[$order->payment_currency] += $orderParams->gross_tax;
			}
			$tax = null;
			foreach($array as $key => $value)
			{
				if(empty($value))
				{
					continue;
				}else{
					$tax .= $value.' '.$key.'   ,';
				}
			}
			$item['tax']= trim($tax, ",");
			
			$items[] = $item;
			$i++;
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

	function getYears()
	{
		$db= oseDB::instance();
		$query = " SELECT YEAR(mem.start_date) as date FROM `#__osemsc_member` as mem"
				." INNER JOIN `#__osemsc_acl` as acl"
				." ON mem.`msc_id` = acl.`id`"
				." GROUP BY YEAR(mem.`start_date`)";
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
		$array['date'] = JText::_('Date');
		$array['msc'] = JText::_('Membership Plan');
		$array['newmem'] = JText::_('New Members');
		$array['expmem'] = JText::_('Expired Members');
		$array['ratio'] = JText::_('Ratio');
		$array['profits'] = JText::_('Profit');
		$array['tax'] = JText::_('Tax');
		$out .= implode(',',$array);
		$out .= "\n";

		$msc_id = JRequest::getInt('msc_id',0);
		$year = JRequest::getInt('year',0);

		$where = array();
		if(!empty($msc_id))
		{
			$where[] = 'mem.`msc_id` = '.$msc_id;
		}

		if(!empty($year))
		{
			$where[] = 'YEAR(mem.`start_date`) = '.$year;
		}

		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );

		$query = " SELECT DATE(mem.start_date) as date, acl.title as msc, mem.msc_id FROM `#__osemsc_member` as mem"
				." INNER JOIN `#__osemsc_acl` as acl"
				." ON mem.`msc_id` = acl.`id`"
				.$where
				." GROUP BY mem.`msc_id`, YEAR(mem.`start_date`), MONTH(mem.`start_date`) ORDER BY DATE(mem.`start_date`) DESC";
		$db->setQuery($query);
		$objs = $db->loadObjectList();

		//$time = oseHTML::getDateTime();
		$time = date("Y-m-d");

		$filename = "monthly-statistics-".$time.".csv";
		$csv_terminated = "\n";
	    $csv_separator = ",";
	    $csv_enclosed = '"';
	    $csv_escaped = "\\";

		foreach($objs as $key => $obj)
		{
			$array = array();
			$array['date'] = oseObject::getValue($obj,'date');
			$array['msc'] = oseObject::getValue($obj,'msc');

			$query = "SELECT count(*) FROM `#__osemsc_member` WHERE `msc_id` = '{$obj->msc_id}' AND YEAR(`start_date`) = YEAR('{$obj->date}') AND MONTH(`start_date`) = MONTH('{$obj->date}') AND `status` = 1";
			$db->setQuery($query);
			$a = $db->loadResult();
			$array['newmem'] = empty($a)?0:$a;

			$query = "SELECT count(*) FROM `#__osemsc_member` WHERE `msc_id` = '{$obj->msc_id}' AND YEAR(`expired_date`) = YEAR('{$obj->date}') AND MONTH(`expired_date`) = MONTH('{$obj->date}') AND `status` = 0";
			$db->setQuery($query);
			$array['expmem'] = $db->loadResult();

			$ratio = round($array['expmem']/$array['newmem']*100,2);
			$array['ratio'] = $ratio.'%';
			
			$query = " SELECT SUM(ord.payment_price) as profits,ord.payment_currency FROM `#__osemsc_order` AS ord"
					." INNER JOIN `#__osemsc_order_item` AS ooi"
					." ON ord.`order_id` = ooi.`order_id`"
					." WHERE ooi.`entry_id` = '{$obj->msc_id}' AND YEAR(ord.`create_date`) = YEAR('{$obj->date}') AND MONTH(ord.`create_date`) = MONTH('{$obj->date}') AND ord.`order_status` = 'confirmed'" 
					." GROUP BY ord.`payment_currency`";
			$db->setQuery($query);
			$profits = $db->loadObjectList();
			$price = null;
			if(empty($profits))
			{
				$array['profits'] = '0.00';
			}else
			{
				foreach($profits as $profit)
				{
					if(!empty($profit->profits) && !empty($profit->payment_currency))
					{
						$price.= $profit->profits.' '.$profit->payment_currency.';';
					}
				}
				$array['profits'] = trim($price,";");
			}

			//Order tax
			$query = " SELECT ord.* FROM `#__osemsc_order` AS ord"
					." INNER JOIN `#__osemsc_order_item` AS ooi"
					." ON ord.`order_id` = ooi.`order_id`"
					." WHERE ooi.`entry_id` = '{$obj->msc_id}' AND YEAR(ord.`create_date`) = YEAR('{$obj->date}') AND MONTH(ord.`create_date`) = MONTH('{$obj->date}') AND ord.`order_status` = 'confirmed'" 
					;
			$db->setQuery($query);
			$orders = $db->loadObjectList();
			$arr = array();
			foreach($orders as $order)
			{
				$orderParams = oseJSON :: decode($order->params);
				$arr[$order->payment_currency] += $orderParams->gross_tax;
			}
			$tax = null;
			foreach($arr as $key => $value)
			{
				if(empty($value))
				{
					continue;
				}else{
					$tax .= $value.' '.$key.';';
				}
			}
			$array['tax']= trim($tax, ";");
			
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
	
	function getOptions()
	{
		$db = oseDB::instance();
		$msc_id = JRequest::getInt('msc_id',0);
		$date = JRequest::getVar('date',0);
		
		$query = "SELECT title FROM `#__osemsc_acl` WHERE `id` = ".(int)$msc_id;
		$db->setQuery($query);
		$title = $db->loadResult();
		$msc = oseRegistry::call('msc');
		$opts = $msc->runAddonAction('panel.payment.getOptions');
		$options = $opts['results'];
		$items = array();
		$item = array();
		foreach($options as $option)
		{
			$item['option_id'] = $option['id'];
			$item['option_name'] = $option['optionname'];
			$item['msc'] = $title;
			if(!empty($date))
			{
				$date.= '-01';
			}
			$query = " SELECT * FROM `#__osemsc_member` AS mem" ." WHERE `msc_id` = '{$msc_id}' AND YEAR(`start_date`) = YEAR('{$date}') AND MONTH(`start_date`) = MONTH('{$date}') AND `status` = 1";
			$db->setQuery($query);
			$mems = $db->loadObjectList();
			$newMem = 0;
			foreach($mems as $mem)
			{
				$params = oseJSON::decode($mem->params);
				$query = "SELECT params FROM `#__osemsc_order_item` WHERE `order_item_id` = '{$params->order_item_id}'";
				$db->setQuery($query);
				$order_params = oseJSON::decode($db->loadResult());
				if($order_params->msc_option == $item['option_id'])
				{
					$newMem++;
				}
			}
			$item['newmem'] = $newMem;
			
			$query = " SELECT * FROM `#__osemsc_member` AS mem" ." WHERE `msc_id` = '{$msc_id}' AND YEAR(`start_date`) = YEAR('{$date}') AND MONTH(`start_date`) = MONTH('{$date}') AND `status` = 0";
			$db->setQuery($query);
			$mems = $db->loadObjectList();
			$expMem = 0;
			foreach($mems as $mem)
			{
				$params = oseJSON::decode($mem->params);
				$query = "SELECT params FROM `#__osemsc_order_item` WHERE `order_item_id` = '{$params->order_item_id}'";
				$db->setQuery($query);
				$order_params = oseJSON::decode($db->loadResult());
				if($order_params->msc_option == $item['option_id'])
				{
					$expMem++;
				}
			}
			$item['expmem'] = $expMem;
			
			$ratio = round($item['expmem']/$item['newmem']*100,2);
			$item['ratio'] = $ratio.'%';
			
			$query = " SELECT ord.payment_price, ord.payment_currency, ooi.params, ord.params as order_params FROM `#__osemsc_order` AS ord"
					." INNER JOIN `#__osemsc_order_item` AS ooi"
					." ON ord.`order_id` = ooi.`order_id`"
					." WHERE ord.`order_status` = 'confirmed' AND ooi.`entry_id` = '{$msc_id}' AND YEAR(ord.`create_date`) = YEAR('{$date}') AND  MONTH(ord.`create_date`) = MONTH('{$date}')"
					;
			$db->setQuery($query);
			$objs = $db->loadObjectList();
			$arr = array();
			$arr2 = array();
			foreach($objs as $obj)
			{
				$params = oseJSON::decode($obj->params);
				if($params->msc_option == $item['option_id'])
				{
					$arr[$obj->payment_currency]+=$obj->payment_price;
					$orderParams = oseJSON::decode($obj->order_params);
					$arr2[$obj->payment_currency]+=$orderParams->gross_tax;
				}
			}		
			$profit = null;
			foreach($arr as $key => $value)
			{
				if(empty($value))
				{
					continue;
				}else{
					$profit .= $value.' '.$key.'   ,';
				}
			}
			$profit = trim($profit, ",");
			$item['profits'] = $profit;
			
			$tax = null;
			foreach($arr2 as $key => $value)
			{
				if(empty($value))
				{
					continue;
				}else{
					$tax .= $value.' '.$key.'   ,';
				}
			}
			$tax = trim($tax, ",");
			$item['tax'] = $tax;
			
			$items[] = $item;
		}
		//print_r($items);exit;
		$result = array();
		$result['total'] = count($items);
		$result['results'] = $items;
		return $result;
	}
}
?>