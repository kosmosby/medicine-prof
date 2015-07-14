<?php
/**
 * @version 3.0
 * @package JomWALL CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
error_reporting(0);

if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}

$app = JFactory::getApplication('site');
 
$config =  & $app->getParams('com_awdwall');

$bgColor = $config->get('bg_color', 1);
error_reporting(0);

require_once(JPATH_COMPONENT . DS . 'defines.php');
require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'user.php');
require_once (JPATH_COMPONENT.DS.'controller.php');
if($controller = JRequest::getWord('controller')) {
	require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
}
$classname	= 'awdwallController'.$controller;
$controller = new $classname( );
$controller->execute( JRequest::getCmd('task'));
$controller->redirect();
?>