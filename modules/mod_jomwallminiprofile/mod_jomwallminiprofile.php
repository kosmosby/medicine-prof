<?php
/**
 *
 * @package	    JomWALL Mini Profile 
 * @subpackage	jomwallminiprofile
 * @version     1.0.0
 * @description This module display a small snap of jomwall profile.
 * @copyright	  Copyright © 2013 - All rights reserved.
 * @license		  GNU General Public License v2.0
 * @author		  AWDsolution.com
 * @author mail	support@awdsolution.com
 * @website		  AWDsolution.com
 *
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
error_reporting(0);
define( 'DS', DIRECTORY_SEPARATOR );
require_once(JPATH_SITE . DS . 'components'.DS.'com_awdwall'. DS . 'helpers' . DS . 'user.php');
require_once(JPATH_SITE . DS . 'components'.DS.'com_awdwall'. DS . 'libraries' . DS . 'jslib.php');
require_once(JPATH_SITE . DS . 'components'.DS.'com_awdwall'. DS . 'models' . DS . 'group.php');
require_once(JPATH_SITE . DS . 'components'.DS.'com_awdwall'. DS . 'models' . DS . 'wall.php');
$config 		= &JComponentHelper::getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
// include the helper file
require_once(dirname(__FILE__).DS.'helper.php');

$user 			= &JFactory::getUser();
if(empty($user->id)) return;
$db		= &JFactory::getDBO();
if($user->id)
{
	$countFriends=JsLib::countFriends($user->id);
	$getPendingFriends=JsLib::getPendingFriends($user->id);
	
	$pendinggroup=JsLib::getPendingGroups($user->id);
	$getMyGrps=count(AwdwallModelGroup::getMyGrps($user->id));
	
	$countwallpost=ModJomwallminiprofileHelper::countwallpost($user->id);
	
	$countalbumphotos=ModJomwallminiprofileHelper::countalbumphotos($user->id);
	$countwallphotos=ModJomwallminiprofileHelper::countwallphotos($user->id);
	$totalphotos=$countalbumphotos+$countwallphotos;
	
	$avatar=AwdwallHelperUser::getBigAvatar51($user->id);
	
	$modelWall = new AwdwallModelWall();	
	$totalpm = $modelWall->countpm($user->id);
	$getlatestwallpost= $modelWall->getLatestPostByUserId($user->id,'');
	if(!empty($getlatestwallpost))
	{	
		if(strlen(strip_tags($getlatestwallpost->message))>54)
		{
			$userstatus=AwdwallHelperUser::showSmileyicons(substr(strip_tags($getlatestwallpost->message),0,54)).'...';
		}
		else
		{
			$userstatus=AwdwallHelperUser::showSmileyicons(substr(strip_tags($getlatestwallpost->message),0,54));
		}
		
	}
	
	$profilelinks		= $params->get('profilelinks', '');
	$show_profile_link		= $params->get('show_profile_link', 1);
	$show_photo_link		= $params->get('show_photo_link', 1);
	$show_friend_link		= $params->get('show_friend_link', 1);
	$show_message_link		= $params->get('show_message_link', 1);
	$show_group_link		= $params->get('show_group_link', 1);
	$show_wallpost_link		= $params->get('show_wallpost_link', 1);

}
$Itemid = AwdwallHelperUser::getComItemId();
$link='index.php?option=com_awdwall&controller=colors';
$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
$cparams = json_decode( $db->loadResult(), true );
for($i=1; $i<=14; $i++)
{
	$str_color = 'color'.$i;			
	$color[$i]= $cparams[$str_color];
}
 
require(JModuleHelper::getLayoutPath('mod_jomwallminiprofile'));

?>