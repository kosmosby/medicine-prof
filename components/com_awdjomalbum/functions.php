<?php 
/**
 * @version 2.4
 * @package Jomgallery
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
if(!defined('REAL_NAME')){
define('REAL_NAME', 0);
}
if(!defined('USERNAME')){
define('USERNAME', 1);
}

	function getUserInfo($userId)
	{
		$db =& JFactory::getDBO();
		$versionwall=checkversionwall();
		$query='';
		if($wallversion=='cb')
		{
		// get user_id from wall_users
			$data['gender'] = '';
			$data['birthday'] = '';
		}
		elseif($wallversion=='js')
		{
			$data['gender'] = '';
			$data['birthday'] = '';
		}
		else
		{
			$query = 'SELECT * FROM #__awd_wall_users WHERE user_id = ' . (int)$userId;
			$db->setQuery($query);
			$row = $db->loadObject();
			
			$data = array();
			if((int)$row->gender == 1)
				$data['gender'] = JText::_('Male');
			elseif((int)$row->gender == 2)
				$data['gender'] = JText::_('Female');
				
			$data['birthday'] = $row->birthday;

		}
		return $data;
	}
	
	  function getCurrentUserDetails($userid)
	{

	 	$db		=& JFactory :: getDBO();
		$app = JFactory::getApplication('site');
	    $config =  & $app->getParams('com_awdwall');
		$template 		= $config->get('temp', 'default');

		

		$query 	= 'SELECT email FROM #__users WHERE id = ' . (int)$userid;
		$db 	= & JFactory::getDBO();
		$db->setQuery($query);
		$email = $db->loadResult();		


		$sql="SELECT  count(*) FROM #__users WHERE id = " . (int)$userid;
		$db->setQuery($sql);
		$result=$db->loadResult();

		$jsfile = JPATH_SITE . '/components/com_community/community.php';
		$cbfile = JPATH_SITE . '/components/com_comprofiler/comprofiler.php';

		if (file_exists($jsfile))
		{
			if($result!=0)	{
				$userprofileLinkCUser=JRoute::_('index.php?option=com_community&view=profile&userid='.$userid.'&Itemid='.AwdwallHelperUser::getComItemId()); 
			}
			$avatarTable='community_users'; 
		}
		else if(file_exists($cbfile))
		{
			if($result!=0)
			{
				$userprofileLinkCUser=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user='.$userid.'&Itemid='.AwdwallHelperUser::getJsItemId()); 
			}
			$avatarTable='comprofiler';

		}
		else
		{
			if($result!=0)
			{
 				$userprofileLinkCUser=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$userid.'&Itemid='.AwdwallHelperUser::getComItemId()); 
			}
			$avatarTable='awd_wall_users';
		}
		
//		$query 	= "SELECT facebook_id FROM #__jconnector_ids WHERE user_id = "  . (int)$userid;
//		$db->setQuery($query);
//		$facebook_id = $db->loadResult();
//		if($facebook_id)
//		{
//			$imgPathCUser='https://graph.facebook.com/'.$facebook_id.'/picture?type=square';
//		}
//		else
//		{
//			
//			$query 	= 'SELECT avatar FROM #__awd_wall_users WHERE user_id = ' . (int)$userid;
//			$db 	= & JFactory::getDBO();
//			$db->setQuery($query);
//			$img = $db->loadResult();		
//			
//			if($img == NULL){
//				$imgPathCUser = JURI::base() . "components/com_awdwall/images/".$template."/".$template."32.png";
//			}else{
//				$imgPathCUser = JURI::base() . "images/wallavatar/" . $userid . "/thumb/tn32" . $img;
//			}
//			
//		}
		$imgPathCUser=AwdwallHelperUser::getBigAvatar32($userid);
		// echo $userprofileLinkCUser;

		$result=array();

		$result[]=$imgPathCUser;

		$result[]=$userprofileLinkCUser;

		$result[]=$avatarTable;

		return $result;

	} 
	
	 function getUserDetails($userid,$avatarTable,$uid)
	{
		//echo $userid.' '.$avatarTable.' '.$currentUser;
			$db		=& JFactory :: getDBO();
			//$config 		= &JComponentHelper::getParams('com_awdwall');
			$app = JFactory::getApplication('site');
			$config =  & $app->getParams('com_awdwall');
			$template 		= $config->get('temp', 'default');

			
		$jsfile = JPATH_SITE . '/components/com_community/community.php';
		$cbfile = JPATH_SITE . '/components/com_comprofiler/comprofiler.php';

		if($avatarTable=='community_users')
		{
			if($result!=0)	{
				$userprofileLink=JRoute::_('index.php?option=com_community&view=profile&userid='.$userid.'&Itemid='.AwdwallHelperUser::getComItemId()); 
				
			} 
		}
		else if($avatarTable=='comprofiler')
		{
			if($result!=0)
			{
				$userprofileLink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user='.$userid.'&Itemid='.AwdwallHelperUser::getJsItemId()); 
			}

		}
		else
		{
			if($result!=0)
			{
 					$userprofileLink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$userid.'&Itemid='.AwdwallHelperUser::getComItemId()); 
			}
			
		}
		
//		$query 	= "SELECT facebook_id FROM #__jconnector_ids WHERE user_id = "  . (int)$userid;
//		$db->setQuery($query);
//		$facebook_id = $db->loadResult();
//		if($facebook_id)
//		{
//			$imgPath1='https://graph.facebook.com/'.$facebook_id.'/picture?type=square';
//		}
//		else
//		{
//			
//			$query 	= 'SELECT avatar FROM #__awd_wall_users WHERE user_id = ' . (int)$userid;
//			$db 	= & JFactory::getDBO();
//			$db->setQuery($query);
//			$img = $db->loadResult();		
//			
//			if($img == NULL){
//				$imgPath1 = JURI::base() . "components/com_awdwall/images/".$template."/".$template."32.png";
//			}else{
//				$imgPath1 = JURI::base() . "images/wallavatar/" . $userid . "/thumb/tn32" . $img;
//			}
//			
//		}
			
			$imgPath1=AwdwallHelperUser::getBigAvatar32($userid);			
			
			$result=array();
			$result[]=$imgPath1; 
			$result[]=$userprofileLink;
			return $result;
			
			//$this->assignRef('imgPath1', $imgPath1); 
			//$this->assignRef('userprofileLink', $userprofileLink);
	}
	
	function getfriendlist()
	{
		$db		=& JFactory :: getDBO();
		$user=&JFactory::getUser();
		$versionwall=checkversionwall();
		if($versionwall=='js')
		{
			$db = &JFactory::getDBO();
			$query = 'SELECT connect_to FROM #__community_connection inner join #__users on #__community_connection.connect_to=#__users.id '
					.'WHERE connect_from = ' . (int)$user->id . ' '
					.'AND status = 1 ' ;
			$db->setQuery($query);
			$friends=$db->loadObjectList();
			$flist='<div id="pts_userlist">';
			foreach($friends as $friend)
			{
				$sql="SELECT  count(*) FROM #__users WHERE id = " . (int)$friend->connect_to;
				$db->setQuery($sql);
				$result=$db->loadResult();
				if($result!=0)
				{
				$fuser=&JFactory::getUser($friend->connect_to);
				$tagrowusername=AwdwallHelperUser::getDisplayName($fuser->id);
			$flist=$flist.'<label><input type="radio" name="tagfriend" id="tagfriend'.$fuser->id.'" value="'.$fuser->id.'" onclick="tagSelectorClick(this, '.$fuser->id.');" /><input type="hidden" id="tageusername'.$fuser->id.'" value="'.$tagrowusername.'" />'.$tagrowusername.'</label>';
				}
			}			
			$flist=$flist.'</div>';
		}
		if($versionwall=='cb')
		{
			$query = 'SELECT connect_to FROM #__awd_connection inner join #__users on #__awd_connection.connect_to=#__users.id '
					.'WHERE connect_from = ' . (int)$user->id . ' '
					.'AND status = 1 AND pending = 0  ' ;
			$db->setQuery($query);
			$friends=$db->loadObjectList();
			$flist='<div id="pts_userlist">';
			foreach($friends as $friend)
			{
				$sql="SELECT  count(*) FROM #__users WHERE id = " . (int)$friend->connect_to;
				$db->setQuery($sql);
				$result=$db->loadResult();
				if($result!=0)
				{
			$fuser=&JFactory::getUser($friend->connect_to);
				$tagrowusername=AwdwallHelperUser::getDisplayName($fuser->id);
			$flist=$flist.'<label><input type="radio" name="tagfriend" id="tagfriend'.$fuser->id.'" value="'.$fuser->id.'" onclick="tagSelectorClick(this, '.$fuser->id.');" /><input type="hidden" id="tageusername'.$fuser->id.'" value="'.$tagrowusername.'" />'.$tagrowusername.'</label>';
			}
			}
			$flist=$flist.'</div>';
		}
		if($versionwall=='standalone')
		{
			$db = &JFactory::getDBO();
			$query = 'SELECT connect_to FROM #__awd_connection inner join #__users on #__awd_connection.connect_to=#__users.id '
					.'WHERE connect_from = ' . (int)$user->id . ' '
					.'AND status = 1 AND pending = 0 ' ;
			$db->setQuery($query);
			$friends=$db->loadObjectList();
			$flist='<div id="pts_userlist">';
			foreach($friends as $friend)
			{
				$sql="SELECT  count(*) FROM #__users WHERE id = " . (int)$friend->connect_to;
				$db->setQuery($sql);
				$result=$db->loadResult();
				if($result!=0)
				{

			$fuser=&JFactory::getUser($friend->connect_to);
				$tagrowusername=AwdwallHelperUser::getDisplayName($fuser->id);
			$flist=$flist.'<label><input type="radio" name="tagfriend" id="tagfriend'.$fuser->id.'" value="'.$fuser->id.'" onclick="tagSelectorClick(this, '.$fuser->id.');" /><input type="hidden" id="tageusername'.$fuser->id.'" value="'.$tagrowusername.'" />'.$tagrowusername.'</label>';
			}
			}	
			$flist=$flist.'</div>';

		}
		return $flist;
		
	}
	

	function getfriendlistAlbum($uid)

	{

		$db		=& JFactory :: getDBO();

		$user=&JFactory::getUser();

		$versionwall=checkversionwall();

		$access=0;

		if($versionwall=='js')

		{

			$db = &JFactory::getDBO();

			$query = 'SELECT connection_id FROM #__community_connection '

				.'WHERE connect_from = ' . (int)$uid . ' AND connect_to = ' . (int)$user->id . ' '

				.'AND status = 1'

				;

		$db->setQuery($query);

		$result1 = $db->loadResult();

		if((int)$result1 > 0){

			// check second way

				$query = 'SELECT connection_id FROM #__community_connection '

					.'WHERE connect_from = ' . (int)$user->id . ' AND connect_to = ' . (int)$uid . ' '

					.'AND status = 1'

					;

					$db->setQuery($query);

					$result = $db->loadResult();

				$result2 = $db->loadResult();

				if((int)$result2 > 0){

					$access=1;

				} 

			}			

 		}

		if($versionwall=='cb')

		{

			$query = 'SELECT connect_to FROM #__awd_connection '

			.'WHERE connect_from = ' . (int)$uid . ' AND connect_to = ' . (int)$user->id . ' '

			.' AND status = 1 AND pending = 0 '

			;

			$db->setQuery($query);

			$result = $db->loadResult();

			if((int)$result > 0){

				$access=1;

			}

		}

		

		if($versionwall=='standalone')

		{

			$db = &JFactory::getDBO();

			 



		$query = 'SELECT connection_id FROM #__awd_connection '

				.'WHERE connect_from = ' . (int)$uid . ' AND connect_to = ' . (int)$user->id . ' '

				.'AND status = 1 AND pending = 0'

				;

		$db->setQuery($query);

		$result = $db->loadResult();

		if((int)$result > 0){

			$access=1;

		} 

		

		}

		return $access;

		

	}


	function getfriendOfFriendlistAlbum($uid)

	{

		$db		=& JFactory :: getDBO();

		$user=&JFactory::getUser();

		$versionwall=checkversionwall();

		$access=0;

		if($versionwall=='js')

		{
			$db = &JFactory::getDBO();
				
			$sql= 'select connect_to FROM #__community_connection where connect_from = ' . $uid . ' AND status = "1" and pending="0"';
			$db->setQuery($sql);
			$friendlist=$db->loadObjectList();
			
			for($i=0;$i<count($friendlist);$i++)
			{
				$friend=$friendlist[$i];
				$list1[$i]=$friend->connect_to;
			}
			if($list1)
			$friends=implode(",",$list1);
					
			$query='select connection_id FROM #__community_connection where connect_to in (' . $friends . ') AND connect_from='.(int)$user->id.' AND status = "1" AND pending="0"';		
	
			$db->setQuery($query);
	
			$result = $db->loadObjectList();
	
			if(!empty($result)){
	
						$access=1;
				}			

 		}

		if($versionwall=='cb')

		{

			$query = 'SELECT connect_to FROM #__awd_connection '

			.'WHERE connect_from = ' . (int)$uid . ' AND connect_to = ' . (int)$user->id . ' '

			.' AND status = 1 AND pending = 0 '

			;

			$db->setQuery($query);

			$result = $db->loadResult();

			if((int)$result > 0){

				$access=1;

			}

		}

		

		if($versionwall=='standalone')

		{
			$db = &JFactory::getDBO();
				
			$sql= 'select connect_to FROM #__awd_connection where connect_from = ' . $uid . ' AND status = "1" and pending="0"';
			$db->setQuery($sql);
			$friendlist=$db->loadObjectList();
			
			for($i=0;$i<count($friendlist);$i++)
			{
				$friend=$friendlist[$i];
				$list1[$i]=$friend->connect_to;
			}
			if($list1)
			$friends=implode(",",$list1);
			
			if(empty($friends))
			{
			$query='select connection_id FROM #__awd_connection where connect_from='.(int)$user->id.' AND status = "1" AND pending="0"';		
			}
			else
			{
			$query='select connection_id FROM #__awd_connection where connect_to in (' . $friends . ') AND connect_from='.(int)$user->id.' AND status = "1" AND pending="0"';		
			}		
	
			$db->setQuery($query);
	
			$result = $db->loadObjectList();
	
			if(!empty($result)){
	
						$access=1;
				}			

		}

		return $access;

	}

	
	
	function checkversionwall()
	{
		$db		=& JFactory :: getDBO();
		$versionwall=JText::_('standalone');
		$jsfile = JPATH_SITE . '/components/com_community/community.php';
		$cbfile = JPATH_SITE . '/components/com_comprofiler/comprofiler.php';
		if (file_exists($jsfile))
		{
			$versionwall='js';
		}
		else if(file_exists($cbfile))
		{
			$versionwall='cb';
		}
		else
		{
			$versionwall='standalone';
		}
		return $versionwall;
		
	}
	
	function getAlbumDetail($id)
	{
		$db		=& JFactory :: getDBO();
		$user=&JFactory::getUser();
		$query = 'SELECT * FROM #__awd_jomalbum where id='.$id;
		$db->setQuery($query);
		$albums=$db->loadObjectList();
		return $albums[0];
		
	}
	function isalbumviewable($id)
	{

		$db		=& JFactory :: getDBO();

		$user=&JFactory::getUser();

		$query = 'SELECT * FROM #__awd_jomalbum where id='.$id;

		$db->setQuery($query);

		$albums=$db->loadObjectList();

		

		$wallversion=checkversionwall();

		$friendstr=getfriendlistAlbum($albums[0]->userid);

	 	$friendofFriendstr=getfriendOfFriendlistAlbum($albums[0]->userid);


		if($albums[0]->privacy==0) // all

		{

			return true;

		}

		else if($albums[0]->privacy==1) //friendonly

		{

			if($albums[0]->userid==$user->id) // if owner of album

			{

				return true;

			}

			else

			{

				if($friendstr==1)

				{  

						return true; 

				}

				else

				{

					return false;

				}

			}

		}

		else if($albums[0]->privacy==3) // me only

		{

			if($albums[0]->userid==$user->id)

			{

				return true;

			}

			else

			{

				return false;

			}

		}

		else if($albums[0]->privacy==2) // friend of friends

		{

			if($albums[0]->userid==$user->id)// if owner of album

			{

				return true;

			}
			
			else if($friendstr==1)// if friend
			{
				return true;
			}

			else

			{
				if($friendofFriendstr==1)// if friend of friends
				{
					return true;
				}
				else
				{
					return false;
				}

			}

		}

		else

		{

			return false;

		}



	}
	
	function getComItemId()
	{
		$db 	= &JFactory::getDBO();
		$query  = "SELECT id FROM #__menu WHERE link='index.php?option=com_awdwall&view=awdwall&layout=main' and published='1'";
		$db->setQuery($query);
		return $db->loadResult();
	}
?>