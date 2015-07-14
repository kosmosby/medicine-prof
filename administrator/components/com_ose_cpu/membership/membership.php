<?php
defined('_JEXEC') or die(";)");

abstract class abstractOseMembership
{
	abstract function create($parent_id = 0, $ordering = 999);
}

class oseMembership extends abstractOseMembership
{
	public function getInstanceByVersion($params = array())
	{
		static $instance;
		//$this->haKLDF775dlD();
		if(!empty($instance))
		{
			return $instance;
		}
		
		jimport( 'joomla.version' );
		$version = new JVersion();
		$version = substr($version->getShortVersion(),0,3);

		if($version == '1.5')
		{
			require_once(dirname(__FILE__).DS.'membership_j15.php');
			$className = get_class($this).'_J15';
			$instance = new $className($params);
		}
		elseif($version == '1.6')
		{
			require_once(dirname(__FILE__).DS.'membership_j16.php');
			$className = get_class($this).'_J16';
			$instance = new $className($params);
		}
		else
		{
			require_once(dirname(__FILE__).DS.'membership_j17.php');
			$className = get_class($this).'_J17';
			$instance = new $className($params);
		}

		return $instance;
	}

	public static function getInstance($type)
	{
		static $instance;
		$className = "oseMsc{$type}";

		if(!class_exists($className))
		{
			oseExit('Can Not Get the Instance of OSEFILE');
		}

		if(!$instance instanceof $className)
		{
			$instance = new $className();

			return $instance;
		}
		else
		{
			return $instance;
		}
	}

	function create($parent_id = 0, $ordering = 999)
	{

	}
	
	function haKLDF775dlD()
	{
		$db= JFactory::getDBO();
		$query = "SELECT * FROM #__ose_activation WHERE ext = 'osemsc'";
		$db->setQuery($query);
		$result = $db->loadObject();
		if (empty($result))
		{
			$option= JRequest::getVar('option');
			$view= JRequest::getVar('view');
			if ($option=='com_osemsc' && $view!='activation')
			{
				$mainframe = JFactory::getApplication();
				$mainframe ->redirect('index.php?option=com_osemsc&view=activation');
			}
		}
		elseif ($result->id == base64_decode($result->code))
		{
			return true;
		}
		else
		{
			$view= JRequest::getVar('view');
			if ($view!='activation')
			{
				$mainframe = JFactory::getApplication();
				$mainframe ->redirect('index.php?option=com_osemsc&view=activation');
			}
		}
	}
}

