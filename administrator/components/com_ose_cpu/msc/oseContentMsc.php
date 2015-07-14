<?php
defined('_JEXEC') or die(";)");

class oseContent2Msc extends oseContent2Object
{
	public $_table = '#__osemsc_content';
	function __construct($array = array())
	{
		$this->getItem($array);
		parent::__construct();
	}
	
	
}