<?php
/**
 * @version 3.0
 * @package JomWALL -CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 error_reporting(0);
// feeding joomla users into jomwall table
$db	=& JFactory::getDBO();
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}
$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';
if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
{
	$query 	= 'SELECT * FROM #__users WHERE block =0 AND id NOT IN (SELECT userid FROM #__awd_jomalbum_userinfo )';
	$db->setQuery($query);
	$userlist=$db->loadObjectList();
	if(count($userlist))
	{
		foreach($userlist as $importuser)
		{
			$sqlquery="INSERT INTO #__awd_jomalbum_userinfo ( `userid`) VALUES ('".$importuser->id."')";
			$db->setQuery($sqlquery);
			if (!$db->query()) 
			{
				return JError::raiseWarning( 500, $db->getError() );
			}
		}
	}
}
$query 	= 'SELECT * FROM #__users WHERE block =0 AND id NOT IN (SELECT user_id  FROM #__awd_wall_users )';
$db->setQuery($query);
$userlist=$db->loadObjectList();
if(count($userlist))
{
	foreach($userlist as $importuser)
	{
		$sqlquery="INSERT INTO #__awd_wall_users ( `user_id`) VALUES ('".$importuser->id."')";
		$db->setQuery($sqlquery);
		if (!$db->query()) 
		{
			return JError::raiseWarning( 500, $db->getError() );
		}
	}
}

$controller = JRequest::getWord('controller', 'awdwall'); 
$document	= JFactory::getDocument();
$document->addStyleSheet( rtrim( JURI::root() , '/' ) . '/administrator/components/com_awdwall/images/awd.css' );
//set the controller page  
require_once(JPATH_COMPONENT . DS . 'controllers' . DS . $controller . '.php');
require_once(JPATH_COMPONENT . DS . 'helpers' . DS .  'helper.php');
// Create the controller helloworldController 
$classname  = $controller . 'controller';

//create a new class of classname and set the default task:display
$controller = new $classname(array('default_task' => 'display'));

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
// Redirect if set by the controller
$controller->redirect(); 
//add parmas

$db	=& JFactory::getDBO();

$db->setQuery('SELECT params FROM #__extensions WHERE element = "com_awdwall" and type="component"');
$params = json_decode( $db->loadResult(), true );
if(empty($params))
{
   $params['temp'] = 'default';
   $params['width'] = '725';
   $params['email_auto'] = '0';
   $params['video_lightbox'] = '0';
   $params['image_lightbox'] = '1';
   $params['display_name'] = '1';
   $params['nof_post'] = '15';
   $params['nof_comment'] = '3';
   $params['bg_color'] = '#FFFFFF';
   $params['image_ext'] = 'gif,png,jpg,jpge';
   $params['file_ext'] = 'doc,docx,pdf,xls,txt';
   $params['privacy'] = '0';
   $params['nof_friends'] = '4';
   $params['display_online'] = '1';
   $params['seo_format'] = '0';
   $params['display_video'] = '1';
   $params['display_image'] = '1';
   $params['display_music'] = '1';
   $params['display_link'] = '1';
   $params['display_file'] = '1';
   $params['display_trail'] = '0';
   $params['dt_format'] = 'g:i A l, j-M-y';
   $params['nof_groups'] = '4';
   $params['nof_invite_members'] = '10';
   $params['display_hightlightbox'] = '0';
   $params['timestamp_format'] = '1';
   // store the combined result
   $paramsString = json_encode( $params );
   $db->setQuery('UPDATE #__extensions SET params = ' .$db->quote( $paramsString ) .' WHERE element = "com_awdwall" and type="component" ' );
	if (!$db->query()) {
	return JError::raiseWarning( 500, $db->getError() );
	}
}

		$link='index.php?option=com_awdwall&controller=colors';
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$colorparams = json_decode( $db->loadResult(), true );
		if(empty($colorparams))
		{
		   $awdparams['color1'] = 'FFFFFF';
		   $awdparams['color2'] = '111111';
		   $awdparams['color3'] = '333333';
		   $awdparams['color4'] = '8C8C8C';
		   $awdparams['color5'] = 'EAE7E0';
		   $awdparams['color6'] = '111111';
		   $awdparams['color7'] = 'FFFFFF';
		   $awdparams['color8'] = '111111';
		   $awdparams['color9'] = 'EAE7E0';
		   $awdparams['color10'] = 'FFFFFF';
		   $awdparams['color11'] = '475875';
		   $awdparams['color12'] = 'FFFFFF';
		   $awdparams['color13'] = 'B0C3C5';
		   $awdparams['color14'] = 'E1DFD9';
		   // store the combined result
		   $awdparamsString = json_encode( $awdparams );
		   $db->setQuery('UPDATE #__menu SET params = ' .$db->quote( $awdparamsString ) .' WHERE link = "'.$link.'"' );
		 //  echo 'UPDATE #__menu SET params = ' .$db->quote( $paramsString ) .' WHERE link = "'.$link.'"';
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}
			
		}
?>
