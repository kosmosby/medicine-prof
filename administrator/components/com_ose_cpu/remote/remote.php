<?php
defined('_JEXEC') or die(";)");
abstract class abstractOseRemote
{

}

class oseRemote extends abstractOseRemote
{
	protected $member_id = null;

	function __construct()
	{

	}

	function __toString()
	{
		return get_class($this);
	}

	function getClientBridge($type)
	{
		switch($type)
		{
			case('soap'):
				require_once(dirname(__FILE__).DS.$type.DS."nu{$type}.php");
			break;
			
			default:
				require_once(dirname(__FILE__).DS.'fsockandcurl'.DS."connection.php");
			break;
		}
	}
}