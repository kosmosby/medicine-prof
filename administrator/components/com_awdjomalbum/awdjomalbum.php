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
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}
define( 'COM_AWDJOMALBUM_DIR', 'images'.DS.'awdjomalbum'.DS );
define( 'COM_AWDJOMALBUM_BASE', JPATH_ROOT.DS.COM_AWDJOMALBUM_DIR );
define( 'COM_AWDJOMALBUM_BASEURL', JURI::root().str_replace( DS, '/', COM_AWDJOMALBUM_DIR ));
error_reporting(1);
$document	= JFactory::getDocument();
$document->addStyleSheet( rtrim( JURI::root() , '/' ) . '/administrator/components/com_awdjomalbum/images/awd.css' );
require_once JPATH_COMPONENT.DS.'controller.php';
require_once JPATH_COMPONENT.DS.'helpers'.DS.'helper.php';

$controller = new AwdjomalbumController( );

$controller->execute( JRequest::getCmd('task'));
$controller->redirect();


?>