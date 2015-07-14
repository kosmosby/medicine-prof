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
defined('_JEXEC') or die("Direct Access Not Allowed");
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}
// INSTALLATION;
$installedPhpVersion= floatval(phpversion());
$supportedPhpVersion= 5.1;
$install= JRequest :: getVar('install', '', 'REQUEST');
$view= JRequest :: getVar('view', '', 'GET');
$task= JRequest :: getVar('task', '', 'REQUEST');
$component= 'com_osemsc';
//install
if(((file_exists(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.$component.DS.'installer.dummy.ini') || $install)) ||($installedPhpVersion < $supportedPhpVersion))
{
	$app = JFactory::getApplication();
	$app->JComponentTitle ="OSE Application Installer";
	require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.$component.DS.'define.php');
	require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.$component.DS.'installer.helper.php');
	require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.$component.DS.'helpers'.DS.'osemsc.php');
	$oseInstaller= new oseInstallerHelper();
	$oseInstaller->install();
	$document = JFactory::getDocument();
	$document->addScript(JURI::root().'media/system/js/mootools-core.js');
}
else
{
	require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'osesofthelper.php');
	require_once(JPATH_COMPONENT_SITE.DS.'init.php');
	require_once(JPATH_COMPONENT.DS.'libraries'.DS.'oseMscPublic.php');
	// Require the base controller
	if (JOOMLA30==true)
	{	
		require_once(OSEMSC_B_CONTROLLER.DS.'controller.php');
		require_once(OSEMSC_B_MODEL.DS.'model.php');
		require_once(OSEMSC_B_VIEW.DS.'view.php');
	}
	else
	{
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'legacy'.DS.'controller.php');
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'legacy'.DS.'model.php');
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'legacy'.DS.'view.php');
		
	}	
	JLoader :: register('OsemscHelper', dirname(__FILE__).DS.'helpers'.DS.'osemsc.php');
	OsemscHelper :: addSubmenu();
	// Create the Controller
	$_c= new osemscController();
	$_c->initControl();
	// Perform the Request task
	$_c->executeTask(JRequest :: getCmd('task', null));
	// Redirect if set by the controller
	$_c->redirectE();
}