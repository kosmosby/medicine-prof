<?php
/**
 * @version 3.0
 * @package Jomgallery
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
if(!defined('REAL_NAME')){
define('REAL_NAME', 0);
}
if(!defined('USERNAME')){
define('USERNAME', 1);
}
if( function_exists('mb_strcut') ) {
mb_internal_encoding("UTF-8");
}
error_reporting(0);
$user = &JFactory::getUser();

$mainframe=&JFactory::getApplication();
require_once(JPATH_SITE . DS . 'components'.DS.'com_awdwall'. DS . 'helpers' . DS . 'user.php');
require_once(JPATH_SITE . DS . 'components'.DS.'com_awdwall'. DS . 'libraries' . DS . 'jslib.php');
require_once( JPATH_COMPONENT.DS.'functions.php' );
$Itemid=AwdwallHelperUser::getComItemId();

if(!empty($user->id))

{
	// Require the base controller
	$filename = 'jquery-1.4.2.min.js';
	$path = JURI::base().'components/com_awdwall/js/';
	//JHTML::script($filename, $path, true);
	//echo '<script type="text/javascript">jQuery.noConflict();<script>';
	$doc =& JFactory::getDocument();
	$style = '#awd-mainarea .wallheadingRight ul li ul {'
	. 'background: none!important;'
	. '}'; 
	$doc->addStyleDeclaration( $style );

	require_once JPATH_COMPONENT.DS.'controller.php';



// Initialize the controller
$controller = new AwdjomalbumController();
$controller->execute(JRequest::getCmd('task'));



// Redirect if set by the controller
$controller->redirect();

}

else

{

$mainframe->redirect(JRoute::_('index.php?option=com_awdwall&Itemid='.$Itemid)); 

}


?>