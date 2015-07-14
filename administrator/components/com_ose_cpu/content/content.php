<?php
defined('_JEXEC') or die(";)");
abstract class abstractOseContent
{

}

class oseContent extends abstractOseContent
{
	protected $member_id = null;

	function __construct()
	{

	}

	function __toString()
	{
		return get_class($this);
	}

	public static function getInstance($type)
	{
		$className = "oseContent{$type}";

		if(class_exists($className))
		{
			static $instance;

			if(!$instance instanceof $className)
			{
				$instance = new $className();
			}

			return $instance;
		}
		else
		{
			oseExit('Can Not Get the Instance of OSEFILE');
		}
	}

	public  function getInstanceByVersion()
	{
		static $instance;

		if(!empty($instance))
		{
			return $instance;
		}

		jimport( 'joomla.version' );
		$version = new JVersion();
		$version = substr($version->getShortVersion(),0,3);

		if($version == '1.5')
		{
			require_once(dirname(__FILE__).DS.'content_j15.php');
			$className = get_class().'_J15';
			$instance = new $className();
		}
		elseif($version == '1.6')
		{
			require_once(dirname(__FILE__).DS.'content_j16.php');
			$className = get_class($this).'_J16';
			$instance = new $className();
		}
		else
		{
			require_once(dirname(__FILE__).DS.'content_j17.php');
			$className = get_class($this).'_J17';
			$instance = new $className();
		}

		return $instance;
	}
}