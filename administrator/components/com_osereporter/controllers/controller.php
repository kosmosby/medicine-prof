<?php
/**
  * @version       1.0 +
  * @package       Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Reporter - com_osereporter
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 24-May-2011
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
defined('_JEXEC') or die(';)');
jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');
class osereporterController extends JController
{
	protected $controller= null, $_c= null;
	function __construct()
	{
		parent :: __construct();
	}
	function initControl()
	{
		// Require specific controller if requested
		$this->controller= JRequest :: getWord('controller', null);
		// get the Real Controller
		$this->_c= $this->getController();
	}
	function display()
	{
		if(!JRequest :: getWord('view', null))
		{
			JRequest :: setVar('view', 'daily');
		}
		parent :: display();
	} // function
	/*
	 *  Initialize the config.
	 */
	function init()
	{}
	function initScript()
	{
		// Remove auto generated mootool from header
		$document= JFactory :: getDocument();
		$headerstuff= $document->getHeadData();
		//reset($headerstuff['scripts']);
		$keys= array_keys($headerstuff['scripts']);
		foreach($keys as $key)
		{
			if(preg_match('/mootools/', $key))
			{
				//unset($headerstuff['scripts'][$key]);
			}
		}
		$document->setHeadData($headerstuff);
		JHTML :: script('jquery-1.4.2.min.js', 'administrator/components/com_osereporter/jqueryui/js/');
		JHTML :: script('jquery-ui-1.8rc3.custom.min.js', 'administrator/components/com_osereporter/jqueryui/js/');
		JHTML :: script('jquery.form.js', 'administrator/components/com_osereporter/jqueryui/js/');
		JHTML :: script('jquery.ui.core.js', 'administrator/components/com_osereporter/jqueryui/js/ui/');
		JHTML :: script('fisheye-iutil.min.js', 'administrator/components/com_osereporter/jqueryui/js/jqDock/');
		JHTML :: script('jquery.qtip-1.0.0-rc3.min.js', 'administrator/components/com_osereporter/jqueryui/js/tips/');
		$document->addScriptDeclaration('jQuery.noConflict();var jq = jQuery;');
	}
	/*
	 *  if ajax action, exit;
	 */
	function executeTask($task)
	{
		$this->_c->execute($task);
		$ajax= JRequest :: getCmd('ajax', false);
		if($ajax)
		{
			//echo 'controller-test';
			exit;
		}
	}
	function getController()
	{
		$controller= $this->controller;
		if($controller)
		{
			require_once(OSEREPORTER_CONTROLLER.DS.$controller.'.php');
			$class= 'osereporterController'.$controller;
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
} // class
?>