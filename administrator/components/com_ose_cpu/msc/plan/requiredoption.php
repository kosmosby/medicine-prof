<?php
defined('_JEXEC') or die(";)");
class oseMscPlanRequiredOption extends oseObject
{
	protected $_table = null;
	
	protected $id = 0;
	protected $plan_id = 0;
	
	protected $params = '';
	
	function __construct($p)
	{
		parent::__construct($p);
	}
	
	function create()
	{
		return true;
	}
	
	function update()
	{
		return true;
	}
	
	function delete()
	{
		return true;
	}
	
	function output()
	{
		return array();
	}
}
?>