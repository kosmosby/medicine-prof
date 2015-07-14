<?php
/**
* @version 2.4
* @package Jomgallery
* @author AWDsolution.com
* @link http://www.AWDsolution.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
* @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
error_reporting(0);
function AwdjomalbumBuildRoute(&$query)
{
/*echo '</pre>';
print_r($query);
echo '</pre>';
exit;*/


$segments = array();
$config = &JComponentHelper::getParams('com_awdjomalbum');


$escapeRouteChar	= array('.', '-', '\\', '/', '@', '#', '?', '!', '^', '&', '<', '>', '\'' , '"' );

if(isset($query['view'])){
$segments[] = $query['view'];
unset($query['view']);
}

if(isset($query['layout'])){
$segments[] = $query['layout'];
unset($query['layout']);
}

if(isset($query['task'])){
$segments[] = $query['task'];
unset($query['task']);
}

if(isset($query['wuid'])){
$segments[] = $query['wuid'];
unset($query['wuid']);
}

if(isset($query['pid'])){
$segments[] = $query['pid'];
unset($query['pid']);
}

if(isset($query['albumid'])){
$segments[] = $query['albumid'];
unset($query['albumid']);
}

if(isset($query['id'])){
$segments[] = $query['id'];
unset($query['id']);
}

if(isset($query['Itemid'])){
// if($query['view']!='gallery'){
$segments[] = $query['Itemid'];
unset($query['Itemid']);
// }

}

if(isset($query['firsttime'])){
$segments[] = $query['firsttime'];
unset($query['firsttime']);
}

return $segments;
}


function AwdalbumGetUserId($name)
{
$db	=& JFactory::getDBO();
$sql = "SELECT `id` FROM #__users WHERE `username`=" . $db->Quote($name);
$db->setQuery($sql);
$id = $db->loadResult();

return $id;
}

function AwdjomalbumParseRoute($segments)
{
//print_r($segments);
$vars = array();
$count = count($segments);
if(!empty($count)){
$user	= $segments[0];

// Check if this user exist
$userid = AwdalbumGetUserId($user);

if($userid != 0){
array_shift($segments);
}
}
$count = count($segments);

if((int)$count == 4){
if(in_array('awdimagelist', $segments)){
if(!empty($segments[0])){
$vars['view'] = $segments[0];
}
if(!empty($segments[1])){
$vars['pid'] = $segments[1];
}
if(!empty($segments[2])){
$vars['albumid'] = $segments[2];
}
if(!empty($segments[3])){
$vars['Itemid'] = $segments[3];
}
}elseif(in_array('awdwallimagelist', $segments)){
if(!empty($segments[0])){
$vars['view'] = $segments[0];
}
if(!empty($segments[1])){
$vars['wuid'] = $segments[1];
}
if(!empty($segments[2])){
$vars['pid'] = $segments[2];
}
if(!empty($segments[3])){
$vars['Itemid'] = $segments[3];
}
}
else if(in_array('awd_addphoto', $segments))
{
if(!empty($segments[0])){
$vars['view'] = $segments[0];
}
if(!empty($segments[1])){
$vars['id'] = $segments[1];
}
if(!empty($segments[2])){
$vars['Itemid'] = $segments[2];
}
if(!empty($segments[3])){
$vars['firsttime'] = $segments[3];
}
}
else if(in_array('showlightboxview', $segments))
{

	if(in_array('albumid', $segments))
	{
		if(!empty($segments[0])){
		$vars['task'] = $segments[0];
		}
		if(!empty($segments[1])){
		$vars['albumid'] = $segments[1];
		}
		if(!empty($segments[2])){
		$vars['pid'] = $segments[2];
		}
		if(!empty($segments[3])){
		$vars['Itemid'] = $segments[3];
		}
	}
	else
	{
		if(!empty($segments[0])){
		$vars['task'] = $segments[0];
		}
		if(!empty($segments[1])){
		$vars['wuid'] = $segments[1];
		}
		if(!empty($segments[2])){
		$vars['pid'] = $segments[2];
		}
		if(!empty($segments[3])){
		$vars['Itemid'] = $segments[3];
		}
	}

}
}

if((int)$count == 3){

if(in_array('awdjomalbum', $segments))
{
if(!empty($segments[0])){
$vars['view'] = $segments[0];
}
if(!empty($segments[1])){
$vars['layout'] = $segments[1];
}
if(!empty($segments[2])){
$vars['Itemid'] = $segments[2];
}
}
else if(in_array('awd_addphoto', $segments))
{
if(!empty($segments[0])){
$vars['view'] = $segments[0];
}
if(!empty($segments[1])){
$vars['id'] = $segments[1];
}
if(!empty($segments[2])){
$vars['Itemid'] = $segments[2];
}
}
else if(in_array('awdalbumimages', $segments))
{
if(!empty($segments[0])){
$vars['view'] = $segments[0];
}
if(!empty($segments[1])){
$vars['albumid'] = $segments[1];
}
if(!empty($segments[2])){
$vars['Itemid'] = $segments[2];
}
}
else
{
if(!empty($segments[0])){
$vars['view'] = $segments[0];
}
if(!empty($segments[1])){
$vars['wuid'] = $segments[1];
}
if(!empty($segments[2])){
$vars['Itemid'] = $segments[2];
}
}
}

		if((int)$count == 2)
		{	
		
			if(in_array('createalbum', $segments))
			{	
				if(!empty($segments[0])){
					$vars['view'] = $segments[0];
				}
				if(!empty($segments[2])){
					$vars['Itemid'] = $segments[2];
				}			
			} 
		}
	if((int)$count == 1){
	$vars['view'] = 'gallery';
	$vars['Itemid'] = $segments[0];
	}
//print_r($vars);exit;
return $vars;
} 