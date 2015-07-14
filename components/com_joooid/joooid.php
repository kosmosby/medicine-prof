<?php
/**
 * 			JOOOID
 * @version			1.0.0
 * @package			JOOOID for Joomla!
 * @copyright			Copyright (C) 2007-2011 Joomler!.net. All rights reserved.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 * @author			Yoshiki Kozaki : joomlers@gmail.com
 * @link			http://www.joomler.net/
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

// Definition of abstractions for j2.x and j3.x


if (version_compare(JVERSION, '3.0', 'ge')){
	define('JOOOID_DS',DIRECTORY_SEPARATOR );
	function joooid_toSQL($date){
		return $date->toSQL();
	}
	define('JOOOID_DB_ESCAPE', 'escape');
}
else{
	define('JOOOID_DS',DS );
	function joooid_toSQL($date){
		return $date->toMySQL();
	}
	define('JOOOID_DB_ESCAPE', 'getEscaped');
}


jimport('joomla.application.component.controller');

require_once JPATH_COMPONENT_SITE.'/helpers/route.php';


$savedErrorReporting = error_reporting(E_ERROR);


function joooidHandleError($errno, $errstr, $errfile, $errline, array $errcontext)
	{
	if($errno>2 || $GLOBALS['joooid_debug']==="0") return false;
	if(stripos($errfile,'joooid')===false ) return false;
	echo "debug:";
	var_dump($GLOBALS['joooid_debug']);
	echo "JOOOID EXCEPTION : ".$errno." - ".$errstr." - [file: ".$errfile.":".$errline."]\n";
	return true;
	}

function joooid_getController(){
	if (version_compare(JVERSION, '3.0', '<')){
		return JController::getInstance('joooid');
	}
	return JControllerLegacy::getInstance('joooid');
}

$savedErrorHandler = set_error_handler("joooidHandleError");
$controller = joooid_getController();
$view = JRequest::getCmd('view');
if(in_array($view, array('manifest'))){
	$task = 'display';
} else {
	$task = JRequest::getCmd('task', 'service');
}
if(function_exists('register_shutdown_function')){
	register_shutdown_function('joooid_shutdownFunction'); 
}
function joooid_shutDownFunction() { 
	$error = error_get_last(); 
	
	if(isset($error['file']) && stripos($error['file'],'joooid')!==false ){
	if($GLOBALS['joooid_debug']==="0") return false;
		echo "debug:";
		var_dump($GLOBALS['joooid_debug']);
		echo "JOOOID EXCEPTION:\n";
		print_r($error);
	}

} 


$controller->execute($task);
$controller->redirect();

if($savedErrorHandler!=null)
	set_error_handler($savedErrorHandler,E_ALL);

error_reporting($savedErrorReporting);
