<?php
defined('_JEXEC') or die("Direct Access Not Allowed");

class oseMscLang extends oseLanguage
{
	protected $arr = array();
	function __construct()
	{
		$this->def();
	}
	
	function def($key, $value)
	{
		$this->arr[$key] = $value;
	}
}
?>
