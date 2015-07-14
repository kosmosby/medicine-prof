<?php
defined('_JEXEC') or die(";)");
class oseMscPlanManual extends oseObject
{
	protected $_table = '#__osemsc_plan_m';
	
	protected $id = 0;
	protected $plan_id = 0;
	protected $amount = 0.00;
	protected $a1 = 0.00;
	protected $p1 = 0;
	protected $t1 = 'day';
	protected $a1_special_enabled = 0;
	protected $a1_special = 0.00;
	protected $a1_special_from = '0000-00-00 00:00:00';
	protected $a1_special_to = '0000-00-00 00:00:00';
	protected $a3_discout_enabled = 0;
	protected $a3_discout_amount = 0;
	protected $a3_discout_unit = 'percentage';
	protected $a3 = 0.00;
	protected $p3 = 0;
	protected $t3 = 'day';
	
	protected $params = '';
	
	function __construct($p)
	{
		parent::__construct($p);
		
		$db = oseDB::instance();
		$query = " SELECT * FROM `{$this->_table}`"
				." WHERE `plan_id` = '".$this->get('plan_id')."'"
				;
		$db->setQuery($query);
		$item = oseDB::loadItem();
		
		if(empty($item))
		{
			$this->create();
		}
		else
		{
			$this->setProperties($item);
		}
	}
	
	function create()
	{
		$vals = $this->getProperties();
		$updated = oseDB::insert($this->_table,$vals);
		return $updated;
	}
	
	function update()
	{
		$vals = $this->getProperties();
		$updated = oseDB::update($this->_table,'plan_id',$vals);
		return $updated;
	}
	
	function delete()
	{
		$updated = oseDB::delete($this->_table,array('plan_id'=>$this->get('plan_id')));
		return $updated;
	}
	
	function output()
	{
		$p =array();

		if($this->get('a1_special_enabled'))
		{
			if( strtotime($this->get('a1_special_to')) > strtotime(oseHtml2::getDateTime() ) )
			{
				$a1 = $this->get('a1_special');
				$a3 = $this->get('a1');
			}
			else
			{
				$a3 = $a1 = $this->get('a1');
			}
		}
		else
		{
			$a3 = $a1 = $this->get('a1');
		}
		$p['first_raw_price'] = $a1;
		$p['second_raw_price'] = $a3;
		$p['p1'] = $this->get('p1');
		$p['t1'] = $this->get('t1');

		$p['p3'] = $this->get('p3');
		$p['t3'] = $this->get('t3');
		$p['discount'] = array();
		if($this->get('a3_discout_enabled'))
		{
			
			$p['discount']['amount'] = $this->get('a3_discout_amount');
			$p['discount']['unit'] = $this->get('a3_discout_unit');
			$p['discount']['type'] = 'renew';
			$p['discount']['time'] = 2;
			$p['discount']['title'] = null;
		}
		
		return $p;
	}
}
?>