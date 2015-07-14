<?php
/**
* @version		$Id: index.php 11407 2009-01-09 17:23:42Z willebil $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) );

define( 'DS', DIRECTORY_SEPARATOR );

require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

JDEBUG ? $_PROFILER->mark( 'afterLoad' ) : null;

/**
 * CREATE THE APPLICATION
 *
 * NOTE :
 */
$mainframe =& JFactory::getApplication('site');

/**
 * INITIALISE THE APPLICATION
 *
 * NOTE :
 */
// set the language
$mainframe->initialise();

JPluginHelper::importPlugin('system');

/*************CODE JOOMLA HERE ***********/
$db = JFactory::getDBO();


//update title and description

function update_title($title, $wall_id){
	$db = JFactory::getDBO();
	$query = "UPDATE #__awd_wall_links SET title = '$title' WHERE wall_id = '$wall_id'";
	$db->setQuery($query);
	$db->query();
}
function update_des($des, $wall_id){	
	$db = JFactory::getDBO();
	$query = "UPDATE #__awd_wall_links SET description = '$des' WHERE wall_id = '$wall_id'";
	$db->setQuery($query);
	$db->query();
}
function update_link($link, $wall_id){	
$link = 'http://'.$link;
	$db = JFactory::getDBO();
	$query = "UPDATE #__awd_wall_links SET link_img = '$link' WHERE wall_id = '$wall_id'";
	$db->setQuery($query);
	$db->query();
}

$wall_id = $_GET['wid_tmp'];
$fieldname = $_GET['fieldname'];
$content = $_GET['content'];

//update link images in database
$link = $_GET['q'];
$q_wall_id = $_GET['q_wid_tmp'];
if($q_wall_id > 0){
	update_link($link, $q_wall_id);
}


//default color
if($link == 'default'){
//$config 		= &JComponentHelper::getParams('com_awdwall');
$config =  & $mainframe->getParams('com_awdwall');
$template 		= $config->get('temp', 'blue');
echo $template;
if($template == 'default'){

$colors ='{\"color1\":\"FFFFFF\",\"color2\":\"111111\",\"color3\":\"333333\",\"color4\":\"8C8C8C\",\"color5\":\"EAE7E0\",\"color6\":\"111111\",\"color7\":\"FFFFFF\",\"color8\":\"111111\",\"color9\":\"EAE7E0\",\"color10\":\"FFFFFF\",\"color11\":\"475875\",\"color12\":\"FFFFFF\",\"color13\":\"B0C3C5\",\"color14\":\"E1DFD9\"}';

}elseif($template == 'green'){

$colors = '{\"color1\":\"888888\",\"color2\":\"81AA17\",\"color3\":\"7C7C7C\",\"color4\":\"C7C7C9\",\"color5\":\"F6F6F6\",\"color6\":\"7CA70F\",\"color7\":\"F6F6F6\",\"color8\":\"7CA70F\",\"color9\":\"EDF6C9\",\"color10\":\"F6F6F6\",\"color11\":\"74AB00\",\"color12\":\"E3ECD3\",\"color13\":\"B0C3C5\",\"color14\":\"F4F8DF\"}';

}elseif($template == 'black'){

$colors = '{\"color1\":\"C8C8C6\",\"color2\":\"B9B9B9\",\"color3\":\"FFFFFF\",\"color4\":\"8C8C8C\",\"color5\":\"000000\",\"color6\":\"B9B9B9\",\"color7\":\"000000\",\"color8\":\"B9B9B9\",\"color9\":\"555555\",\"color10\":\"222222\",\"color11\":\"CECECE\",\"color12\":\"2A2A2A\",\"color13\":\"B0C3C5\",\"color14\":\"484745\"}';

}elseif($template == 'lavender'){

$colors = '{\"color1\":\"7E7E7E\",\"color2\":\"BA8FCA\",\"color3\":\"222222\",\"color4\":\"ABABAE\",\"color5\":\"F6F6F6\",\"color6\":\"AC79BF\",\"color7\":\"F6F6F6\",\"color8\":\"AD7BC0\",\"color9\":\"B485C6\",\"color10\":\"AC79BF\",\"color11\":\"EAD3F0\",\"color12\":\"F7EBFB\",\"color13\":\"B0C3C5\",\"color14\":\"F9E7FD\"}';

}elseif($template == 'orange'){

$colors = '{\"color1\":\"8C8C8C\",\"color2\":\"F6A45E\",\"color3\":\"333333\",\"color4\":\"8C8C8C\",\"color5\":\"F6F6F6\",\"color6\":\"FC9D4C\",\"color7\":\"F6F6F6\",\"color8\":\"FC9D4C\",\"color9\":\"FCE6CE\",\"color10\":\"FBEEE5\",\"color11\":\"475875\",\"color12\":\"F7EFE4\",\"color13\":\"B0C3C5\",\"color14\":\"FDDFBD\"}';

}elseif($template == 'blue'){

$colors ='{\"color1\":\"868686\",\"color2\":\"32AED2\",\"color3\":\"333333\",\"color4\":\"8C8C8C\",\"color5\":\"F6F6F6\",\"color6\":\"44C0E0\",\"color7\":\"F6F6F6\",\"color8\":\"44C0E0\",\"color9\":\"D6EEF8\",\"color10\":\"AED7E6\",\"color11\":\"475875\",\"color12\":\"E7F2F6\",\"color13\":\"B0C3C5\",\"color14\":\"E2EFF5\"}';

}
	$db = JFactory::getDBO();
	$query = "UPDATE #__menu SET `params` = '$colors' WHERE `link` = 'index.php?option=com_awdwall&controller=colors'";
	$db->setQuery($query);
	$db->query();
}

//update title in database
if($fieldname == 'awd_attached_title'){
	update_title($content, $wall_id);
}

//update description in database
if($fieldname == 'awd_attached_des'){
	update_des($content, $wall_id);
}

$fieldname = $_GET['fieldname'];
echo stripslashes(strip_tags($_GET['content'],"<br><p><img><a><br /><strong><em>"));