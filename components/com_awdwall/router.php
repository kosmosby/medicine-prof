<?php

/**

 * @version 2.0

 * @package JomWALL CB

 * @author   AWDsolution.com

 * @link http://www.AWDsolution.com

 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html

 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.

*/



// Check to ensure this file is included in Joomla!

defined('_JEXEC') or die('Restricted access');
error_reporting(0);


function AwdwallBuildRoute(&$query)

{

	$segments = array();

	//$config = &JComponentHelper::getParams('com_awdwall');
	$app = JFactory::getApplication('site');
	$config =  & $app->getParams('com_awdwall');



	if((int)$config->get('seo_format')){

		if(array_key_exists('wuid', $query)){

			$userid = AwdGetUserId($query['wuid']);

			if($userid){

				$user		= &JFactory::getUser($query['wuid']);

				$username	= $user->username;

				$segments[]	= $username;			

				unset($query['wuid']);

			}{

				//				

			}

		}else{

			if(isset($query['layout']) && $query['layout'] != 'main'){

				$user = &JFactory::getUser();

				if((int)$user->id){

					$user		= &JFactory::getUser();

					$username	= $user->username;

					$segments[]	= $username;	

				}

			}elseif(!isset($query['layout']) && !isset($query['groupid'])){

				$user = &JFactory::getUser();

				if((int)$user->id){

					$user		= &JFactory::getUser();

					$username	= $user->username;

					$segments[]	= $username;	

				}

			}

		}

	}



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

	

	if(isset($query['imageid'])){					

		$segments[] = $query['imageid'];		

		unset($query['imageid']);

	}

	

	if(isset($query['videoid'])){					

		$segments[] = $query['videoid'];		

		unset($query['videoid']);

	}

	

	if(isset($query['groupid'])){					

		$segments[] = $query['groupid'];		

		unset($query['groupid']);

	}

	return $segments;

}



function AwdwallParseRoute($segments)

{

//	echo "<pre>";
//	print_r($segments);
//	echo "</pre>";
	//exit;
	$vars = array();

	$count = count($segments);

	if(!empty($count)){

		$user	= $segments[0];

		

		// Check if this user exist

		$userid = AwdGetUserId($user);

			

		if($userid != 0){

			array_shift($segments);			

		}

	}

	$count = count($segments);

	if((int)$count == 4){

		// If there are no menus we try to use the segments

		if(!empty($segments[0])){

			$vars['view'] = $segments[0];

		}

		if(!empty($segments[1])){

			$vars['layout'] = $segments[1];

		}

		if(!empty($segments[2])){

			$vars['task'] = $segments[2];

		}

		if(!empty($segments[3])){

			$vars['wuid'] = $segments[3];

		}

	}

	

	if((int)$count == 3){

		if(in_array('mywall', $segments)){			

			if(!empty($segments[0])){

				$vars['view'] = $segments[0];

			}

			if(!empty($segments[1])){

				$vars['layout'] = $segments[1];

			}

			if(!empty($segments[2])){

				$vars['wuid'] = $segments[2];

			}			

		}elseif(in_array('viewimage', $segments)){			

			if(!empty($segments[0])){

				$vars['task'] = $segments[0];

			}

			if(!empty($segments[1])){

				$vars['wuid'] = $segments[1];

			}

			if(!empty($segments[2])){

				$vars['imageid'] = $segments[2];

			}

		}elseif(in_array('viewvideo', $segments)){			

			if(!empty($segments[0])){

				$vars['task'] = $segments[0];

			}

			if(!empty($segments[1])){

				$vars['wuid'] = $segments[1];

			}

			if(!empty($segments[2])){

				$vars['videoid'] = $segments[2];

			}

		}else{

			if(!empty($segments[0])){

				$vars['view'] = $segments[0];

			}

			if(!empty($segments[1])){

				$vars['layout'] = $segments[1];

			}

			if(!empty($segments[2])){

				$vars['task'] = $segments[2];

			}		

		}

	}



	if((int)$count == 2){
		if(in_array('mywall', $segments) || in_array('main', $segments)){
			if(!empty($segments[0])){
				$vars['view'] = $segments[0];
			}
			if(!empty($segments[1])){
				$vars['layout'] = $segments[1];
			}
		}elseif(in_array('group', $segments)){
			if(!empty($segments[0])){
				$vars['view'] = $segments[0];
			}
			if(!empty($segments[1])){
				$vars['task'] = $segments[1];
			}
			
		}
		elseif(in_array('friends', $segments)){
			if(!empty($segments[0])){
				$vars['view'] = $segments[0];
			}
			if(!empty($segments[1])){
				$vars['task'] = $segments[1];
			}
		}
		else{

			if(!empty($segments[0])){
				$vars['task'] = $segments[0];
			}
			if(!empty($segments[1])){
				$vars['groupid'] = $segments[1];
			}
		}
	}

	

	if((int)$count == 1){

		$vars['task'] = $segments[0];

	}

	//print_r($vars);

	return $vars;

}



function AwdGetUserId($name)

{

	$db			=& JFactory::getDBO();

	$sql = "SELECT `id` FROM #__users WHERE `username`=" . $db->Quote($name);

	$db->setQuery($sql);

	$id = $db->loadResult();



	return $id;

}