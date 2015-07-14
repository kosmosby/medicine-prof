<?php
/**
  * @version     5.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 15-Nov-2010
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

// no direct access
defined('_JEXEC') or die(';)');
jimport('joomla.application.component.controller');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');

class osemscController extends JControllerLegacy
{
	protected
	$controller = null,
	$_c = null
	;

	function __construct()
	{
		parent::__construct();
	}

	function initControl()
	{
		
		// Require specific controller if requested
		$this->controller = JRequest::getWord('controller',null);
		//$this->hasactivated();
		// get the Real Controller
		$this->_c = $this->getController();
	}

	function display($cachable = false, $urlparams = false)
	{
		if(!JRequest::getWord('view',null))
		{
			JRequest::setVar('view','memberships');
		}

	    parent::display();
	}
	/*
	 *  if ajax action, exit;
	 */
	function executeTask($task)
	{
		$this->_c->execute($task);

		$ajax = JRequest::getBool('ajax',false);

		if($ajax)
		{//echo 'controller-test';
			exit;
		}
	}

	function getController()
	{
		$controller = $this->controller;

		if($controller)
		{
			require_once(OSEMSC_B_CONTROLLER.DS.$controller.'.php');

			$class = 'oseMscController'.$controller;

			return new $class();
		}
		else
		{
			return $this;
		}
	}

	function redirectE()
	{
		$this->_c->redirect();
	}

	function action()
	{
		$actionName = JRequest::getString('action');

		$msc = oseRegistry::call('msc');

		$result = $msc->runAddonAction($actionName);

		/*
		if($updated)
		{
			$result['success'] = true;
			$result['title'] = 'Done';
			$result['content'] = 'Update Successfully!';
		}
		else
		{
			$result['success'] = true;
			$result['title'] = 'Error';
			$result['content'] = 'Fail to Update!';
		}
		*/
		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getHeader()
	{
		require_once(OSEMSC_B_PATH.DS.'html'.DS.'header.php');
		oseExit('');
	}

	function getCountry()
	{
		$result = oseRegistry::call('msc')->getInstance('Methods')->getCountry();

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getDefState()
	{
		$result = oseRegistry::call('msc')->getInstance('Methods')->getDefState();

		$result = oseJson::encode($result);

		oseExit($result);
		
	}
	
	function getAddons()
	{
		$result = array();

		$type = JRequest::getCmd('addon_type',null);

		if(empty($type))
		{
			oseExit() ;
		}

		$items = oseMscAddon::getAddonList($type,true,null);

		if(count($items) < 1)
		{
			$result['total'] = 0;
			$result['results'] = array();
		}
		else
		{
			$result['total'] = count($items);
			$result['results'] = $items;
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function getAddon()
	{
		$result = array();

		$addon_name = JRequest::getCmd('addon_name',null);
		$type = JRequest::getCmd('addon_type',null);

		echo '<script type="text/javascript">'."\r\n";
		require_once(JPATH_SITE.DS.oseMscMethods::getAddonPath($addon_name.'.js',$type));
		echo "\r\n".'</script>';
		oseExit();
	}
	function hasactivated()
	{
		$db= JFactory::getDBO();
		$query = "SELECT * FROM #__ose_activation WHERE ext = 'osemsc'";
		$db->setQuery($query);
		$result = $db->loadObject();
		if (empty($result))
		{
			$view= JRequest::getVar('view');
			if ($view!='activation')
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
	function getMod()
    {
    	$result = array();

		$addon_name = JRequest::getCmd('addon_name',null);
		$type = JRequest::getCmd('addon_type',null);

		echo '<script type="text/javascript">'."\r\n";
		require_once(JPATH_SITE.DS.oseMscMethods::getJsModPath($addon_name,$type));
		echo "\r\n".'</script>';
		oseExit();
    }

    function getState()
	{
		$result = oseRegistry::call('msc')->getInstance('Methods')->getState();

		$result = oseJson::encode($result);

		oseExit($result);
	}
}  // class
