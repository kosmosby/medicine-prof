<?php
/**
 * @version 3.0
 * @package JomWALL CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
class AwdwallHelperUser
{
	function getGrpImg($img, $grpId)
	{	
		$grpImg = '';		
		if($img == NULL){
			$grpImg = JURI::base() . "components/com_awdwall/images/group_thumb.jpg";
		}else{
			$grpImg = JURI::base() . "images/awdgrp_images/" . $grpId . "/thumb/tn" . $img;
		}
		
		return $grpImg;
	}
	
	function getBigGrpImg($img, $grpId)
	{					
		$grpImg = '';
		if($img == NULL){
			$grpImg = JURI::base() . "components/com_awdwall/images/group.jpg";
		}else{
			$grpImg = JURI::base() . "images/awdgrp_images/" . $grpId . "/original/" . $img;
		}
		
		return $grpImg;
	}
	function getBigGrpImg133($img, $grpId)
	{					
		$grpImg = '';
		if($img == NULL){
			$grpImg = JURI::base() . "components/com_awdwall/images/group.jpg";
		}else{
			$grpImg = JURI::base() . "images/comprofiler/plug_cbgroupjive/2/" . $grpId .  "/" . $img;
		}
		
		return $grpImg;
	}
	function getBigGrpImg64($img, $grpId)
	{					
		$grpImg = '';
		if($img == NULL){
			$grpImg = JURI::base() ."components/com_awdwall/images/group_thumb64.jpg";
		}else{
			$grpImg = JURI::base() . "images/comprofiler/plug_cbgroupjive/2/tn" . $grpId .  "/" . $img;
		}
		
		return $grpImg;
	}
	function getBigGrpImg51($img, $grpId)
	{					
		$grpImg = '';
		if($img == NULL){
			$grpImg = JURI::base() ."components/com_awdwall/images/group_thumb51.jpg";
		}else{
			$grpImg = JURI::base() . "images/awdgrp_images/" . $grpId . "/thumb/tn51" . $img;
		}
		
		return $grpImg;
	}
	function getBigGrpImg40($img, $grpId)
	{					
		$grpImg = '';
		if($img == NULL){
			$grpImg = JURI::base() . "components/com_awdwall/images/group_thumb40.jpg";
		}else{
			$grpImg = JURI::base() . "images/awdgrp_images/" . $grpId . "/thumb/tn40" . $img;
		}
		
		return $grpImg;
	}
	function getBigGrpImg32($img, $grpId)
	{					
		$grpImg = '';
		if($img == NULL){
			$grpImg = JURI::base() . "components/com_awdwall/images/group_thumb32.jpg";
		}else{
			$grpImg = JURI::base() . "images/awdgrp_images/" . $grpId . "/thumb/tn32" . $img;
		}
		
		return $grpImg;
	}
	function getBigGrpImg19($img, $grpId)
	{					
		$grpImg = '';
		if($img == NULL){
			$grpImg = JURI::base() . "components/com_awdwall/images/group_thumb19.jpg";
		}else{
			$grpImg = JURI::base() . "images/awdgrp_images/" . $grpId . "/thumb/tn19" . $img;
		}
		
		return $grpImg;
	}
	function checkcbfacebookplguin()
	{
	 	$db		=& JFactory :: getDBO();
		$query 	= "SELECT published FROM #__comprofiler_plugin WHERE folder='plug_cbfacebookconnect'";
		$db->setQuery($query);
		$published = $db->loadResult();
		return $published;
	}
	
	function getAvatar($userId)
	{	
	 	$db		=& JFactory :: getDBO();
		$app = JFactory::getApplication('site');
		$config = &JComponentHelper::getParams('com_awdwall');
		$template 		= $config->get('temp', 'default');
		$avatarintergration 		= $config->get('avatarintergration', '0');
		
		$cbfacebookavatar=	AwdwallHelperUser::checkcbfacebookplguin();
		if($cbfacebookavatar==1)
		{
			$query = "SELECT fb_userid FROM #__comprofiler WHERE user_id = " . (int)$userId;
			$db->setQuery($query);
			$facebook_id = $db->loadResult();
			if($facebook_id)
			{
				$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=large';
				return $avatar;
			}		
		}
		
		$query 	= "SELECT facebook_id FROM #__jconnector_ids WHERE user_id = "  . (int)$userId;
		$db->setQuery($query);
		$facebook_id = $db->loadResult();
		if($facebook_id)
		{
			$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=large';
		}
		else
		{
			
			$query 	= 'SELECT avatar FROM #__awd_wall_users WHERE user_id = ' . (int)$userId;
			$db 	= & JFactory::getDBO();
			$db->setQuery($query);
			$img = $db->loadResult();		
			
			if($img == NULL){
				$avatar = JURI::base() . "components/com_awdwall/images/".$template."/".$template.".png";
			}else{
				$avatar = JURI::base() . "images/wallavatar/" . $userId . "/thumb/tn51" . $img;
			}
			
		}
			
		if($avatarintergration==1) // k2
		{
				if(file_exists(JPATH_SITE . '/components/com_k2/k2.php'))
				{
					require_once (JPATH_SITE . '/components/com_k2/helpers/utilities.php');
				
				$avatar=K2HelperUtilities::getAvatar($userId);
				}
		}
		else if($avatarintergration==2) // easyblog
		{
				if(file_exists(JPATH_SITE . '/components/com_easyblog/easyblog.php'))
				{
					require_once (JPATH_SITE . '/components/com_easyblog/helpers/helper.php');
				
				$blogger	= EasyBlogHelper::getTable( 'Profile', 'Table');
				$blogger->load( $userId );
				$avatar=$blogger->getAvatar();
				}
		}
		else if($avatarintergration==3) // alphauserpoint
		{
				if(file_exists(JPATH_SITE . '/components/com_alphauserpoints/alphauserpoints.php'))
				{
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helper.php');
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helpers/helpers.php');
				
					$_user_info = AlphaUserPointsHelper::getUserInfo ( $referrerid='', $userId  );
					$com_params = &JComponentHelper::getParams( 'com_alphauserpoints' );
					$useAvatarFrom = $com_params->get('useAvatarFrom');
					
					$width=100;
					$avatar = getAvatar( $useAvatarFrom, $_user_info, $height,$width);	
					$doc = new DOMDocument();
					$doc->loadHTML($avatar);
					$imageTags = $doc->getElementsByTagName('img');
					
					foreach($imageTags as $tag) {
						$avatar=$tag->getAttribute('src');
					}
				}
		}
		return $avatar;
	}
	
	function getBigAvatar($userId)
	{	
	 	$db		=& JFactory :: getDBO();
		$app = JFactory::getApplication('site');
		$config = &JComponentHelper::getParams('com_awdwall');
		$template 		= $config->get('temp', 'default');
		$avatarintergration 		= $config->get('avatarintergration', '0');
		
		$cbfacebookavatar=	AwdwallHelperUser::checkcbfacebookplguin();
		if(	$cbfacebookavatar==1)
		{
			$query = "SELECT fb_userid FROM #__comprofiler WHERE user_id = " . (int)$userId;
			$db->setQuery($query);
			$facebook_id = $db->loadResult();
			if($facebook_id)
			{
				$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=large';
				return $avatar;
			}		
		}
		
		$query 	= "SELECT facebook_id FROM #__jconnector_ids WHERE user_id = "  . (int)$userId;
		$db->setQuery($query);
		$facebook_id = $db->loadResult();
		if($facebook_id)
		{
			$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=large';
		}
		else
		{
			
			$query 	= 'SELECT avatar FROM #__awd_wall_users WHERE user_id = ' . (int)$userId;
			$db 	= & JFactory::getDBO();
			$db->setQuery($query);
			$img = $db->loadResult();		
			
			if($img == NULL){
				$avatar = JURI::base() . "components/com_awdwall/images/".$template."/".$template.".png";
			}else{
				$avatar = JURI::base() . "images/wallavatar/" . $userId . "/original/" . $img;
			}
			
		}
		
		if($avatarintergration==1) // k2
		{
				if(file_exists(JPATH_SITE . '/components/com_k2/k2.php'))
				{
					require_once (JPATH_SITE . '/components/com_k2/helpers/utilities.php');
				
				$avatar=K2HelperUtilities::getAvatar($userId);
				}
		}
		else if($avatarintergration==2) // easyblog
		{
				if(file_exists(JPATH_SITE . '/components/com_easyblog/easyblog.php'))
				{
					require_once (JPATH_SITE . '/components/com_easyblog/helpers/helper.php');
				
				$blogger	= EasyBlogHelper::getTable( 'Profile', 'Table');
				$blogger->load( $userId );
				$avatar=$blogger->getAvatar();
				}
		}
		else if($avatarintergration==3) // alphauserpoint
		{
				if(file_exists(JPATH_SITE . '/components/com_alphauserpoints/alphauserpoints.php'))
				{
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helper.php');
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helpers/helpers.php');
				
					$_user_info = AlphaUserPointsHelper::getUserInfo ( $referrerid='', $userId  );
					$com_params = &JComponentHelper::getParams( 'com_alphauserpoints' );
					$useAvatarFrom = $com_params->get('useAvatarFrom');
					
					$width=100;
					$avatar = getAvatar( $useAvatarFrom, $_user_info, $height,$width);	
					$doc = new DOMDocument();
					$doc->loadHTML($avatar);
					$imageTags = $doc->getElementsByTagName('img');
					
					foreach($imageTags as $tag) {
						$avatar=$tag->getAttribute('src');
					}
				}
		}
		return $avatar;
	}
	function getBigAvatar133($userId)
	{	
		$db		=& JFactory :: getDBO();
		$app = JFactory::getApplication('site');
		$config = &JComponentHelper::getParams('com_awdwall');
		$template 		= $config->get('temp', 'default');
		$avatarintergration 		= $config->get('avatarintergration', '0');
		
		$cbfacebookavatar=	AwdwallHelperUser::checkcbfacebookplguin();
		if(	$cbfacebookavatar==1)
		{
			$query = "SELECT fb_userid FROM #__comprofiler WHERE user_id = " . (int)$userId;
			$db->setQuery($query);
			$facebook_id = $db->loadResult();
			if($facebook_id)
			{
				$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=large';
				return $avatar;
			}		
		}
		
			$query 	= "SELECT facebook_id FROM #__jconnector_ids WHERE user_id = "  . (int)$userId;
			$db->setQuery($query);
			$facebook_id = $db->loadResult();
			if($facebook_id)
			{
				$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=large';
			}
			else
			{
				
				$query 	= 'SELECT avatar FROM #__awd_wall_users WHERE user_id = ' . (int)$userId;
				$db 	= & JFactory::getDBO();
				$db->setQuery($query);
				$img = $db->loadResult();		
				
				if($img == NULL){
					$avatar = JURI::base() . "components/com_awdwall/images/".$template."/".$template.".png";
				}else{
					$avatar = JURI::base() . "images/wallavatar/" . $userId . "/thumb/tn133" . $img;
				}
				
			}
		
		if($avatarintergration==1) // k2
		{
				if(file_exists(JPATH_SITE . '/components/com_k2/k2.php'))
				{
					require_once (JPATH_SITE . '/components/com_k2/helpers/utilities.php');
				
				$avatar=K2HelperUtilities::getAvatar($userId);
				}
		}
		else if($avatarintergration==2) // easyblog
		{
				if(file_exists(JPATH_SITE . '/components/com_easyblog/easyblog.php'))
				{
					require_once (JPATH_SITE . '/components/com_easyblog/helpers/helper.php');
				
				$blogger	= EasyBlogHelper::getTable( 'Profile', 'Table');
				$blogger->load( $userId );
				$avatar=$blogger->getAvatar();
				}
		}
		else if($avatarintergration==3) // alphauserpoint
		{
				if(file_exists(JPATH_SITE . '/components/com_alphauserpoints/alphauserpoints.php'))
				{
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helper.php');
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helpers/helpers.php');
				
					$_user_info = AlphaUserPointsHelper::getUserInfo ( $referrerid='', $userId  );
					$com_params = &JComponentHelper::getParams( 'com_alphauserpoints' );
					$useAvatarFrom = $com_params->get('useAvatarFrom');
				
					$width=133;
					$avatar = getAvatar( $useAvatarFrom, $_user_info, $height,$width);	
					$doc = new DOMDocument();
					$doc->loadHTML($avatar);
					$imageTags = $doc->getElementsByTagName('img');
					
					foreach($imageTags as $tag) {
						$avatar=$tag->getAttribute('src');
					}
				}
		}
		return $avatar;
	}
	function getBigAvatar51($userId)
	{	
	 	
		$db		=& JFactory :: getDBO();
		$app = JFactory::getApplication('site');
		$config 		= &JComponentHelper::getParams('com_awdwall');
		$template 		= $config->get('temp', 'default');
		$avatarintergration 		= $config->get('avatarintergration', '0');
		
		
		$cbfacebookavatar=	AwdwallHelperUser::checkcbfacebookplguin();
		if(	$cbfacebookavatar==1)
		{
			$query = "SELECT fb_userid FROM #__comprofiler WHERE user_id = " . (int)$userId;
			$db->setQuery($query);
			$facebook_id = $db->loadResult();
			if($facebook_id)
			{
				$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=square';
				return $avatar;
			}		
		}
		
			
			$query 	= "SELECT facebook_id FROM #__jconnector_ids WHERE user_id = "  . (int)$userId;
			$db->setQuery($query);
			$facebook_id = $db->loadResult();
			if($facebook_id)
			{
				$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=square';
			}
			else
			{
				
				$query 	= 'SELECT avatar FROM #__awd_wall_users WHERE user_id = ' . (int)$userId;
				$db 	= & JFactory::getDBO();
				$db->setQuery($query);
				$img = $db->loadResult();		
				
				if($img == NULL){
					$avatar = JURI::root() . "components/com_awdwall/images/".$template."/".$template."51.png";
				}else{
					$avatar = JURI::root() . "images/wallavatar/" . $userId . "/thumb/tn51" . $img;
				}
			}
		
		if($avatarintergration==1) // k2
		{
				if(file_exists(JPATH_SITE . '/components/com_k2/k2.php'))
				{
					require_once (JPATH_SITE . '/components/com_k2/helpers/utilities.php');
				
				$avatar=K2HelperUtilities::getAvatar($userId);
				}
			
		}
		else if($avatarintergration==2) // easyblog
		{
				if(file_exists(JPATH_SITE . '/components/com_easyblog/easyblog.php'))
				{
					require_once (JPATH_SITE . '/components/com_easyblog/helpers/helper.php');
				
				$blogger	= EasyBlogHelper::getTable( 'Profile', 'Table');
				$blogger->load( $userId );
				$avatar=$blogger->getAvatar();
				}
		}
		else if($avatarintergration==3) // alphauserpoint
		{
				if(file_exists(JPATH_SITE . '/components/com_alphauserpoints/alphauserpoints.php'))
				{
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helper.php');
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helpers/helpers.php');
				
					$_user_info = AlphaUserPointsHelper::getUserInfo ( $referrerid='', $userId  );
					$com_params = &JComponentHelper::getParams( 'com_alphauserpoints' );
					$useAvatarFrom = $com_params->get('useAvatarFrom');
					$height = 50;
					$width=50;
					$avatar = getAvatar( $useAvatarFrom, $_user_info, $height,$width);	
					$doc = new DOMDocument();
					$doc->loadHTML($avatar);
					$imageTags = $doc->getElementsByTagName('img');
					
					foreach($imageTags as $tag) {
						$avatar=$tag->getAttribute('src');
					}
				}
		}
		return $avatar;
	}
	function getBigAvatar40($userId)
	{	
		$db		=& JFactory :: getDBO();
		$app = JFactory::getApplication('site');
		$config = &JComponentHelper::getParams('com_awdwall');
		$template 		= $config->get('temp', 'default');
		$avatarintergration 		= $config->get('avatarintergration', '0');
		
		$cbfacebookavatar=	AwdwallHelperUser::checkcbfacebookplguin();
		if(	$cbfacebookavatar==1)
		{
			$query = "SELECT fb_userid FROM #__comprofiler WHERE user_id = " . (int)$userId;
			$db->setQuery($query);
			$facebook_id = $db->loadResult();
			if($facebook_id)
			{
				$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=square';
				return $avatar;
			}		
		}
		
			$query 	= "SELECT facebook_id FROM #__jconnector_ids WHERE user_id = "  . (int)$userId;
			$db->setQuery($query);
			$facebook_id = $db->loadResult();
			if($facebook_id)
			{
				$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=square';
			}
			else
			{
				
				$query 	= 'SELECT avatar FROM #__awd_wall_users WHERE user_id = ' . (int)$userId;
				$db 	= & JFactory::getDBO();
				$db->setQuery($query);
				$img = $db->loadResult();		
				
				if($img == NULL){
					$avatar = JURI::base() . "components/com_awdwall/images/".$template."/".$template."40.png";
				}else{
					$avatar = JURI::base() . "images/wallavatar/" . $userId . "/thumb/tn40" . $img;
				}
				
			}
	
		if($avatarintergration==1) // k2
		{
				if(file_exists(JPATH_SITE . '/components/com_k2/k2.php'))
				{
					require_once (JPATH_SITE . '/components/com_k2/helpers/utilities.php');
				
				$avatar=K2HelperUtilities::getAvatar($userId);
				}
		}
		else if($avatarintergration==2) // easyblog
		{
				if(file_exists(JPATH_SITE . '/components/com_easyblog/easyblog.php'))
				{
					require_once (JPATH_SITE . '/components/com_easyblog/helpers/helper.php');
				
				$blogger	= EasyBlogHelper::getTable( 'Profile', 'Table');
				$blogger->load( $userId );
				$avatar=$blogger->getAvatar();
				}
		}
		else if($avatarintergration==3) // alphauserpoint
		{
				if(file_exists(JPATH_SITE . '/components/com_alphauserpoints/alphauserpoints.php'))
				{
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helper.php');
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helpers/helpers.php');
				
					$_user_info = AlphaUserPointsHelper::getUserInfo ( $referrerid='', $userId  );
					$com_params = &JComponentHelper::getParams( 'com_alphauserpoints' );
					$useAvatarFrom = $com_params->get('useAvatarFrom');
					$height = 40;
					$width=40;
					$avatar = getAvatar( $useAvatarFrom, $_user_info, $height,$width);	
					$doc = new DOMDocument();
					$doc->loadHTML($avatar);
					$imageTags = $doc->getElementsByTagName('img');
					
					foreach($imageTags as $tag) {
						$avatar=$tag->getAttribute('src');
					}
				}
		}
		return $avatar;
	}
	function getBigAvatar32($userId)
	{	
		$db		=& JFactory :: getDBO();
		$app = JFactory::getApplication('site');
		$config = &JComponentHelper::getParams('com_awdwall');
		$template 		= $config->get('temp', 'default');
		$avatarintergration 		= $config->get('avatarintergration', '0');
		
		$cbfacebookavatar=	AwdwallHelperUser::checkcbfacebookplguin();
		if(	$cbfacebookavatar==1)
		{
			$query = "SELECT fb_userid FROM #__comprofiler WHERE user_id = " . (int)$userId;
			$db->setQuery($query);
			$facebook_id = $db->loadResult();
			if($facebook_id)
			{
				$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=square';
				return $avatar;
			}		
		}
		
			$query 	= "SELECT facebook_id FROM #__jconnector_ids WHERE user_id = "  . (int)$userId;
			$db->setQuery($query);
			$facebook_id = $db->loadResult();
			if($facebook_id)
			{
				$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=square';
			}
			else
			{
				
				$query 	= 'SELECT avatar FROM #__awd_wall_users WHERE user_id = ' . (int)$userId;
				$db 	= & JFactory::getDBO();
				$db->setQuery($query);
				$img = $db->loadResult();		
				
				if($img == NULL){
					$avatar = JURI::base() . "components/com_awdwall/images/".$template."/".$template."32.png";
				}else{
					$avatar = JURI::base() . "images/wallavatar/" . $userId . "/thumb/tn32" . $img;
				}
				
			}
		
	    if($avatarintergration==1) // k2
		{
				if(file_exists(JPATH_SITE . '/components/com_k2/k2.php'))
				{
					require_once (JPATH_SITE . '/components/com_k2/helpers/utilities.php');
				
				$avatar=K2HelperUtilities::getAvatar($userId);
				}
		}
		else if($avatarintergration==2) // easyblog
		{
				if(file_exists(JPATH_SITE . '/components/com_easyblog/easyblog.php'))
				{
					require_once (JPATH_SITE . '/components/com_easyblog/helpers/helper.php');
				
				$blogger	= EasyBlogHelper::getTable( 'Profile', 'Table');
				$blogger->load( $userId );
				$avatar=$blogger->getAvatar();
				}
		}
		else if($avatarintergration==3) // alphauserpoint
		{
				if(file_exists(JPATH_SITE . '/components/com_alphauserpoints/alphauserpoints.php'))
				{
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helper.php');
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helpers/helpers.php');
				
					$_user_info = AlphaUserPointsHelper::getUserInfo ( $referrerid='', $userId  );
					$com_params = &JComponentHelper::getParams( 'com_alphauserpoints' );
					$useAvatarFrom = $com_params->get('useAvatarFrom');
					$height = 32;
					$width=32;
					$avatar = getAvatar( $useAvatarFrom, $_user_info, $height,$width);	
					$doc = new DOMDocument();
					$doc->loadHTML($avatar);
					$imageTags = $doc->getElementsByTagName('img');
					
					foreach($imageTags as $tag) {
						$avatar=$tag->getAttribute('src');
					}
				}
		}
		return $avatar;
	}
	function getBigAvatar19($userId)
	{
		$db		=& JFactory :: getDBO();
		$app = JFactory::getApplication('site');
		$config = &JComponentHelper::getParams('com_awdwall');
		$template 		= $config->get('temp', 'default');
		$avatarintergration 		= $config->get('avatarintergration', '0');
		
		$cbfacebookavatar=	AwdwallHelperUser::checkcbfacebookplguin();
		if(	$cbfacebookavatar==1)
		{
			$query = "SELECT fb_userid FROM #__comprofiler WHERE user_id = " . (int)$userId;
			$db->setQuery($query);
			$facebook_id = $db->loadResult();
			if($facebook_id)
			{
				$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=square';
				return $avatar;
			}		
		}
		
			$query 	= "SELECT facebook_id FROM #__jconnector_ids WHERE user_id = "  . (int)$userId;
			$db->setQuery($query);
			$facebook_id = $db->loadResult();
			if($facebook_id)
			{
				$avatar='https://graph.facebook.com/'.$facebook_id.'/picture?type=square';
			}
			else
			{
				
				$query 	= 'SELECT avatar FROM #__awd_wall_users WHERE user_id = ' . (int)$userId;
				$db 	= & JFactory::getDBO();
				$db->setQuery($query);
				$img = $db->loadResult();		
				
				if($img == NULL){
					$avatar = JURI::base() . "components/com_awdwall/images/".$template."/".$template."19.png";
				}else{
					$avatar = JURI::base() . "images/wallavatar/" . $userId . "/thumb/tn19" . $img;
				}
				
			}
		if($avatarintergration==1) // k2
		{
				if(file_exists(JPATH_SITE . '/components/com_k2/k2.php'))
				{
					require_once (JPATH_SITE . '/components/com_k2/helpers/utilities.php');
					$avatar=K2HelperUtilities::getAvatar($userId);
				}
				
		}
		else if($avatarintergration==2) // easyblog
		{
				if(file_exists(JPATH_SITE . '/components/com_easyblog/easyblog.php'))
				{
					require_once (JPATH_SITE . '/components/com_easyblog/helpers/helper.php');
				
				$blogger	= EasyBlogHelper::getTable( 'Profile', 'Table');
				$blogger->load( $userId );
				$avatar=$blogger->getAvatar();
				}
		}
		else if($avatarintergration==3) // alphauserpoint
		{
				if(file_exists(JPATH_SITE . '/components/com_alphauserpoints/alphauserpoints.php'))
				{
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helper.php');
					require_once (JPATH_SITE . '/components/com_alphauserpoints/helpers/helpers.php');
				
					$_user_info = AlphaUserPointsHelper::getUserInfo ( $referrerid='', $userId  );
					$com_params = &JComponentHelper::getParams( 'com_alphauserpoints' );
					$useAvatarFrom = $com_params->get('useAvatarFrom');
					$height = 19;
					$width=19;
					$avatar = getAvatar( $useAvatarFrom, $_user_info, $height,$width);	
					$doc = new DOMDocument();
					$doc->loadHTML($avatar);
					$imageTags = $doc->getElementsByTagName('img');
					
					foreach($imageTags as $tag) {
						$avatar=$tag->getAttribute('src');
					}
				}
		}
		return $avatar;
	}
	
function getDisplayName($userId)
{
	$app = JFactory::getApplication('site');
	$config = &JComponentHelper::getParams('com_awdwall');
	$displayName = (int)$config->get('display_name', 1);
	$user = &JFactory::getUser($userId);
	if( !function_exists('mb_strcut') ) {
		if($displayName == USERNAME){
			return substr($user->username, 0, 30);
		}else{
			return substr($user->name, 0, 30);
		}
	}
	else
	{	mb_internal_encoding("UTF-8");
		if($displayName == USERNAME){
			return mb_strcut($user->username, 0, 30);
		}else{
			return mb_strcut($user->name, 0, 30);
		}
	}
}	
	
	function getDisplayTime($utime)
	{
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$dtFormat 	= $config->get('dt_format', 'g:i A  l, j-M-y');
		$timestamp_format 	= $config->get('timestamp_format', '1');
		if($timestamp_format==0) {
			return date($dtFormat, $utime);
		}
		else
		{
			$time_difference = time() - $utime ; 
			$seconds = $time_difference ; 
			$minutes = round($time_difference / 60 );
			$hours = round($time_difference / 3600 ); 
			$days = round($time_difference / 86400 ); 
			$weeks = round($time_difference / 604800 ); 
			$months = round($time_difference / 2419200 ); 
			$years = round($time_difference / 29030400 ); 
			
			if($seconds <= 60)
			{
			return JText::sprintf('POST TIME SECOND AGO', $seconds);
			}
			else if($minutes <=60)
			{
			   if($minutes==1)
			   {
				return JText::_('ONE MINUTE AGO');
				}
			   else
			   {
			   return JText::sprintf('POST TIME MINUTE AGO', $minutes);
			   }
			}
			else if($hours <=24)
			{
			   if($hours==1)
			   {
			   return JText::_('ONE HOUR AGO');
			   }
			  else
			  {
			  return JText::sprintf('POST TIME HOUR AGO', $hours);
			  }
			}
			else if($days <=7)
			{
			  if($days==1)
			   {
			   return JText::_('YESTERDAY');
			   }
			  else
			  {
			  return JText::sprintf('POST TIME DAY AGO', $days);
			  }
			
			
			  
			}
			else if($weeks <=4)
			{
			  if($weeks==1)
			   {
			   return JText::_('ONE WEEK AGO');
			   }
			  else
			  {
			  return JText::sprintf('POST TIME WEEK AGO', $weeks);
			  }
			 }
			else if($months <=12)
			{
			   if($months==1)
			   {
			   return JText::_('ONE MONTH AGO');
			   }
			  else
			  {
			  return JText::sprintf('POST TIME MONTH AGO', $months);
			  }
			 
			   
			}
			
			else
			{
			if($years==1)
			   {
			   return JText::_('ONE YEAR AGO');
			   }
			  else
			  {
			  return JText::sprintf('POST TIME YEAR AGO', $years);
			  }
			
			
			}
			
		}		
		
	}
	
	function getComItemId()
	{
		$db 	= &JFactory::getDBO();
		$query  = "SELECT id FROM #__menu WHERE link='index.php?option=com_awdwall&view=awdwall&layout=main' and published='1' limit 1";
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	function formatDuration($duration = 0, $format = 'HH:MM:SS')
	{
		if ($format == 'seconds' || $format == 'sec') {
			$arg = explode(":", $duration);
			$hour	= isset($arg[0]) ? intval($arg[0]) : 0;
			$minute	= isset($arg[1]) ? intval($arg[1]) : 0;
			$second	= isset($arg[2]) ? intval($arg[2]) : 0;
			$sec = ($hour*3600) + ($minute*60) + ($second);
			return (int) $sec;
		}
		if ($format == 'HH:MM:SS' || $format == 'hms') {
			$timeUnits = array
			(
				'HH' => $duration / 3600 % 24,
				'MM' => $duration / 60 % 60,
				'SS' => $duration % 60
			);
			$arg = array();
			foreach ($timeUnits as $timeUnit => $value) {
				$arg[$timeUnit] = ($value > 0) ? $value : 0;
			}
			$hms = '%02s:%02s:%02s';
			$hms = sprintf($hms, $arg['HH'], $arg['MM'], $arg['SS']);
			return $hms;
		}
	}
	
	function formatUrlInMsg($msg)
	{
		$stringToArray = explode(" ", $msg);
		$msg = '';
		foreach($stringToArray as $key => $val){
			if(preg_match('/^(http(s?):\/\/|ftp:\/\/{1})((\w+\.){1,})\w{2,}$/i', $val)){
				$val = '<a href="' . $val . '" target="_blank">' . $val . '</a>';
			}else if(preg_match('/^((\w+\.){1,})\w{2,}$/i', $val)){
				$val = '<a href="http://' . $val . '" target="_blank">' . $val . '</a>';
			}else if(preg_match('/^(http(s?):\/\/|ftp:\/\/{1})/i', $val)){
				$val = '<a href="' . $val . '" target="_blank">' . $val . '</a>';
			}
			$msg .= $val . ' ';
		}
		return $msg;
	}
	
	function checkTrailPost($msg)
	{
		ini_set(magic_quotes_gpc, 0);
		$msg = stripslashes($msg);
		$pos = stripos($msg, 'http://www.everytrail.com/view_trip.php');
		if($pos !== false){
			$msg = str_ireplace('script', '', $msg);			
			$msg = str_ireplace('onmouse', '', $msg);			
			$msg = str_ireplace('onclick', '', $msg);
			$msg = str_ireplace('javascript', '', $msg);
			return $msg;
		}else{
			return JRequest::getString('awd_message', '');
		}
	}
	
	function getJsItemId()
	{
		$db 	= &JFactory::getDBO();
		$query  = "SELECT id FROM #__menu WHERE link = 'index.php?option=com_comprofiler' and client_id=0 and published=1 order by id limit 1";
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	function getImageExt()
	{			
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		return $config->get('image_ext', 'gif,png,jpg,jpge');
		
	}
	
	function getFileExt()
	{	
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		return $config->get('file_ext', 'doc,docx,pdf,xls,txt');
		
	}
	
	function checkOnline($userId)
	{
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		if(!(int)$config->get('display_online'))
			return false;
		$db 	= &JFactory::getDBO();
		$query  = "SELECT userid FROM #__session WHERE userid = " . (int)$userId;
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	function getOnlineIcon()
	{
		return JURI::base() . 'components/com_awdwall/images/online.jpg';
	}
	
	function formatDate($date)
	{	
		if($date != '')
			return date('d/m/Y', strtotime($date));
	}
	
	function getUserinfocolname($colname)
	{	
		$db 	= &JFactory::getDBO();
		$app =& JFactory::getApplication();
		$dbprefix=$app->getCfg('dbprefix'); 
		$tablename=$dbprefix.'awd_jomalbum_info_ques';
		
		$query = 'show tables like "'.$tablename.'"';
		
		$db->setQuery($query);
		$results = $db->loadObjectList();
		if(count($results)>0)
		{
			$query  = "SELECT value FROM #__awd_jomalbum_info_ques WHERE colname = '" . $colname."'";
			$db->setQuery($query);
			return $db->loadResult();
		}
	}
	
	function getUserInfo($userid)
	{	
		$db 	= &JFactory::getDBO();
		$app =& JFactory::getApplication();
		$dbprefix=$app->getCfg('dbprefix'); 
		$tablename=$dbprefix.'awd_jomalbum_userinfo';
		
		$query = 'show tables like "'.$tablename.'"';
		$db->setQuery($query);
		$results = $db->loadObjectList();
		if(count($results)>0)
		{
			$query="Select * from #__awd_jomalbum_userinfo where userid=".$userid; 
			$db->setQuery($query);
			$userinfo=$db->loadObjectList();
			$userinfo=$userinfo[0];
		}
		return $userinfo;
	}
	
	function IsPendingFriend($userid,$wuid)
	{	
		$db 	= &JFactory::getDBO();
		$query  = "SELECT connection_id FROM #__awd_connection WHERE connect_from = '" . $wuid."' and connect_to='".$userid."' and status='1' and pending='1'";
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	function IsSkypeOnline($skype_account)
	{
		
		$config 	= &JComponentHelper::getParams('com_awdwall');
		$skype_choice 	= $config->get('skype_choice', 'chat');
		
		$cUrl = curl_init();
		$skype_account = utf8_encode($skype_account);
		curl_setopt($cUrl, CURLOPT_TRANSFERTEXT, 1);
		curl_setopt($cUrl, CURLOPT_FORBID_REUSE, 1); 
		curl_setopt($cUrl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($cUrl, CURLOPT_DNS_CACHE_TIMEOUT, 0);
		curl_setopt($cUrl, CURLOPT_URL,'http://mystatus.skype.com/'.$skype_account.'.num'); 
        curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($cUrl, CURLOPT_TIMEOUT, 5); 
        $status_code = trim(curl_exec($cUrl)); 
        curl_close($cUrl); 
        $n = intval($status_code); 
			if ( !is_numeric($n) )
				$n = 0;
			$s = array(1 => 'offline', 2 => 'online', 3 => 'away', 5 => 'do_not_disturb');
			if ( array_key_exists($n, $s) )
				$status = $s[$n];
			else
				$status = 'offline';

		//echo   $status_code;		
		$skype_img='<div id="skypeStatus" ><a href="skype:'.$skype_account.'?'.$skype_choice.'"><img  class="myavtar" id="skypeimg" /></a><input type="hidden" id="skpstatus" value="0" /><script language="javascript" type="text/javascript">getSkypeStatus("'.$skype_account.'");</script></div>';		

		return $skype_img;
	}
	
	function isTweet($wall_id)
	{
		$db 	= &JFactory::getDBO();
		$query  = "SELECT tweet_id FROM #__awd_wall_tweets WHERE wall_id = '" .$wall_id . "'";
		$db->setQuery($query);
		return $db->loadResult();
	}
		
	function isSocialfeed($wall_id)
	{
		$db 	= &JFactory::getDBO();
		$query  = "SELECT type FROM #__awd_wall_social_feeds WHERE wallid = '" .$wall_id . "'";
		$db->setQuery($query);
		return $db->loadResult();
	}

	function getHightlightbox($userid)
	{
		$db 	= &JFactory::getDBO();
		$itemId = AwdwallHelperUser::getComItemId();
		$user =& JFactory::getUser();
		$config 	= &JComponentHelper::getParams('com_awdwall');
		$himages 	= $config->get('himages', 3);
		$fields			= $config->get('fieldids', '');
		$basicInfo 		= JsLib::getUserBasicInfo($user->id, $fields);
		
		$app =& JFactory::getApplication();
		$dbprefix=$app->getCfg('dbprefix'); 
		$tablename=$dbprefix.'awd_jomalbum_userinfo';
		
		$query = 'show tables like "'.$tablename.'"';
		$db->setQuery($query);
		$results = $db->loadObjectList();
		if(count($results)>0)
		{
			$query="Select * from #__awd_jomalbum_userinfo where userid=".$userid; 
			$db->setQuery($query);
			$userinfo=$db->loadObjectList();
			$userinfo=$userinfo[0];
		}
		
		$userhighlightfields=explode(",",$userinfo->userhighlightfields);
		
		$counter=0;
		
		$query="SELECT p.id,p.path,a.wall_date FROM #__awd_wall_images AS p INNER JOIN #__awd_wall AS a inner join #__awd_wall_privacy as pv on pv.wall_id=a.id WHERE a.commenter_id 	 =".$userid." and pv.privacy=0 and p.wall_id=a.id and a.wall_date IS NOT NULL ORDER BY a.id DESC LIMIT ".$himages;
		$db->setQuery($query);
		$wallimages=$db->loadObjectList();
		foreach($wallimages as $wallimage)
		{
			$imagearray[$i]['path']=JURI::base().'images/'.$userid.'/original/'.$wallimage->path;
			$link 	=JRoute::_("index.php?option=com_awdjomalbum&view=awdwallimagelist&wuid=".$userid."&pid=".$wallimage->id."&Itemid=".$itemId);
		}

		//print_r($userhighlightfields);
	?>
	<div  class="hightlightboxMain">
        <div  class="hightlightboxInner">
        	<div class="hightlightboxleft">
            <ul  class="hightlightul">
           
			<?php 
				
                if(is_array($basicInfo)){ 
                foreach($basicInfo as $arr){
                $cbff='display_'.str_replace(' ','',$arr[1]);
				//print_r($arr);
				if((in_array($arr[1],$userhighlightfields))&&(!empty($arr[0])) && ($counter<=4)){
            ?>  
            	<li class="workat"><span class="hightlightlevel"><?php echo $arr[1];?></span>&nbsp;<?php  echo $arr[0];?></li>
            <?php
					$counter=$counter+1;
					}
                } 
              } 
            ?>
            	<?php if((in_array('currentcity',$userhighlightfields))&&(!empty($userinfo->currentcity)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Current City');?></span>&nbsp;<?php if(strlen($userinfo->currentcity)>30){echo substr($userinfo->currentcity,0,30).'...';}else{ echo $userinfo->currentcity;}?></li>
                <?php $counter=$counter+1;} ?>
            	<?php if((in_array('hometown',$userhighlightfields))&&(!empty($userinfo->hometown)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Hometown');?></span>&nbsp;<?php if(strlen($userinfo->hometown)>30){echo substr($userinfo->hometown,0,30).'...';}else{ echo $userinfo->hometown;}?></li>
                <?php $counter=$counter+1;} ?>
            	<?php if((in_array('currentcity',$userhighlightfields))&&(!empty($userinfo->languages)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Languages');?></span>&nbsp;<?php if(strlen($userinfo->languages)>30){echo substr($userinfo->languages,0,30).'...';}else{ echo $userinfo->languages;}?></li>
                <?php $counter=$counter+1;} ?>
            	<?php if((in_array('birthday',$userhighlightfields))&&(!empty($userinfo->birthday)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Birthday');?></span>&nbsp;
				<?php if($userinfo->hide_birthyear==1){echo date('jS F ', strtotime($userinfo->birthday));}else{echo date('jS F Y', strtotime($userinfo->birthday));}?></li>
                <?php $counter=$counter+1;} ?>
            	<?php if((in_array('skype_user',$userhighlightfields))&&(!empty($userinfo->skype_user)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Skype Userid');?></span>&nbsp;<?php if(strlen($userinfo->skype_user)>30){echo substr($userinfo->skype_user,0,30).'...';}else{ echo $userinfo->skype_user;}?></li>
                <?php $counter=$counter+1;} ?>
            	<?php if((in_array('facebook_user',$userhighlightfields))&&(!empty($userinfo->facebook_user)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Facebook Userid');?></span>&nbsp;<?php if(strlen($userinfo->facebook_user)>30){echo substr($userinfo->facebook_user,0,30).'...';}else{ echo $userinfo->facebook_user;}?></li>
                <?php $counter=$counter+1;} ?>
            	<?php if((in_array('twitter_user',$userhighlightfields))&&(!empty($userinfo->twitter_user)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Twitter Userid');?></span>&nbsp;<?php if(strlen($userinfo->twitter_user)>30){echo substr($userinfo->twitter_user,0,30).'...';}else{ echo $userinfo->twitter_user;}?></li>
                <?php $counter=$counter+1;} ?>
            	<?php if((in_array('youtube_user',$userhighlightfields))&&(!empty($userinfo->youtube_user)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Youtube Userid');?></span>&nbsp;<?php if(strlen($userinfo->youtube_user)>30){echo substr($userinfo->youtube_user,0,30).'...';}else{ echo $userinfo->youtube_user;}?></li>
                <?php } ?>
                
            	<?php if((in_array('workingat',$userhighlightfields))&&(!empty($userinfo->workingat)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Working At');?></span>&nbsp;<?php if(strlen($userinfo->workingat)>30){echo substr($userinfo->workingat,0,30).'...';}else{ echo $userinfo->workingat;}?></li>
                <?php $counter=$counter+1;} ?>
            	<?php if((in_array('studied',$userhighlightfields))&&(!empty($userinfo->studied)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Studied');?></span>&nbsp;<?php if(strlen($userinfo->studied)>30){echo substr($userinfo->studied,0,30).'...';}else{ echo $userinfo->studied;}?></li>
                <?php $counter=$counter+1;} ?>
            	<?php if((in_array('livein',$userhighlightfields))&&(!empty($userinfo->livein)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Live in');?></span>&nbsp;<?php if(strlen($userinfo->livein)>30){echo substr($userinfo->livein,0,30).'...';}else{ echo $userinfo->livein;}?></li>
                <?php $counter=$counter+1;} ?>
            	<?php if((in_array('phone',$userhighlightfields))&&(!empty($userinfo->phone)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Phone');?></span>&nbsp;<?php if(strlen($userinfo->phone)>30){echo substr($userinfo->phone,0,30).'...';}else{ echo $userinfo->phone;}?></li>
                <?php $counter=$counter+1;} ?>
            	<?php if((in_array('cell',$userhighlightfields))&&(!empty($userinfo->cell)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Cell');?></span>&nbsp;<?php if(strlen($userinfo->cell)>30){echo substr($userinfo->cell,0,30).'...';}else{ echo $userinfo->cell;}?></li>
                <?php $counter=$counter+1;} ?>
            	<?php if((in_array('maritalstatus',$userhighlightfields))&&(!empty($userinfo->maritalstatus)) && ($counter<=4)){?>
            	<li class="workat"><span class="hightlightlevel"><?php echo JText::_('Marital status');?></span>&nbsp;
				<?php if($userinfo->maritalstatus=='married'){echo  JText::_('Married');}?>
				<?php if($userinfo->maritalstatus=='single'){echo  JText::_('Single');}?>
				<?php if($userinfo->maritalstatus=='divorced'){echo  JText::_('Divorced');}?>
                </li>
                <?php $counter=$counter+1;} ?>
                
            </ul>
            </div>
            <?php 
			if(count($wallimages)){
			?>
            <div class="hightlightboxright">
            <?php 
			
		foreach($wallimages as $wallimage)
		{
			$imagepath=JURI::base().'images/'.$userid.'/thumb/tn'.$wallimage->path;
			//$imagearray[$i]['pdate']=$wallimage->wall_date;
			$link 	=JRoute::_("index.php?option=com_awdjomalbum&view=awdwallimagelist&wuid=".$userid."&pid=".$wallimage->id."&Itemid=".$itemId);
				//echo $imagearray[$k]['path'];
			?>
<a href="<?php echo $link;?>" class="awdiframe">

<img src="<?php echo $imagepath;?>" alt="" title="" style="float:left;margin-left:2px;"/>  </a>
			 <?php 
				}
				?>
             </div>
                <?php
			} 
			?>
            
        </div>
    </div>
    <?php if($counter==0  and count($wallimages)==0 ){?>
    <script type="text/javascript">
	jQuery(".hightlightboxMain").hide();
	</script>
    <?php } ?>
    <?php if($counter==0  and count($wallimages)>0 ){?>
    <script type="text/javascript">
	jQuery(".hightlightboxleft").hide();
	</script>
    <?php } ?>
	<?php
		
	}
	
function getlatestuserfiles($userid)
{
$db = &JFactory::getDBO();

$sql="Select awi.*,aw.* from #__awd_wall_files as awi inner join #__awd_wall as aw on aw.id=awi.wall_id  where aw.user_id=".$userid." and aw.group_id IS NULL  and aw.wall_date IS NOT NULL order by aw.id desc limit 5";
$db->setQuery($sql);
$rows = $db->loadObjectList();
return $rows;

}
function getlatestuserimages($userid)
{
$db = &JFactory::getDBO();

$sql="Select awi.*,aw.* from #__awd_wall_images as awi inner join #__awd_wall as aw on aw.id=awi.wall_id  where aw.user_id=".$userid." and aw.group_id IS NULL and aw.wall_date IS NOT NULL order by aw.id desc limit 5";
$db->setQuery($sql);
$rows = $db->loadObjectList();
return $rows;

}
function getlatestusermusic($userid)
{
$db = &JFactory::getDBO();

$sql="Select awi.*,aw.* from #__awd_wall_mp3s as awi inner join #__awd_wall as aw on aw.id=awi.wall_id  where aw.user_id=".$userid." and aw.group_id IS NULL  and aw.wall_date IS NOT NULL order by aw.id desc limit 5";
$db->setQuery($sql);
$rows = $db->loadObjectList();
return $rows;

}
function getlatestuservideo($userid)
{
$db = &JFactory::getDBO();

$sql="Select awi.*,aw.* from #__awd_wall_videos as awi inner join #__awd_wall as aw on aw.id=awi.wall_id  where aw.user_id=".$userid." and aw.group_id IS NULL  and aw.wall_date IS NOT NULL order by aw.id desc limit 5";
$db->setQuery($sql);
$rows = $db->loadObjectList();
return $rows;
}
function getlatestuserlinks($userid)
{
$db = &JFactory::getDBO();

$sql="Select awi.*,aw.* from #__awd_wall_links as awi inner join #__awd_wall as aw on aw.id=awi.wall_id  where aw.user_id=".$userid." and aw.group_id IS NULL  and aw.wall_date IS NOT NULL order by aw.id desc limit 5";
$db->setQuery($sql);
$rows = $db->loadObjectList();
return $rows;
}
function getlatestuserjinks($userid)
{
$db = &JFactory::getDBO();

$sql="Select awi.*,aw.* from #__awd_wall_jing as awi inner join #__awd_wall as aw on aw.id=awi.wall_id  where aw.user_id=".$userid." and aw.group_id IS NULL  and aw.wall_date IS NOT NULL order by aw.id desc limit 5";
$db->setQuery($sql);
$rows = $db->loadObjectList();
return $rows;
}
function getlatestusertrail($userid)
{
$db = &JFactory::getDBO();

$sql="Select awi.*,aw.* from #__awd_wall_trail as awi inner join #__awd_wall as aw on aw.id=awi.wall_id  where aw.user_id=".$userid." and aw.group_id IS NULL  and aw.wall_date IS NOT NULL order by aw.id desc limit 5";
$db->setQuery($sql);
$rows = $db->loadObjectList();
return $rows;
}
function getlatestuserevents($userid)
{
$db = &JFactory::getDBO();

$sql="Select awi.*,aw.* from #__awd_wall_events as awi inner join #__awd_wall as aw on aw.id=awi.wall_id  where aw.user_id=".$userid." and aw.group_id IS NULL  and aw.wall_date IS NOT NULL order by aw.id desc limit 5";
$db->setQuery($sql);
$rows = $db->loadObjectList();
return $rows;
}
function getlatestuserarticles($userid)
{
$db = &JFactory::getDBO();

$sql="Select awi.*,aw.* from #__awd_wall_article as awi inner join #__awd_wall as aw on aw.id=awi.wall_id  where aw.user_id=".$userid." and aw.group_id IS NULL  and aw.wall_date IS NOT NULL order by aw.id desc limit 5";
$db->setQuery($sql);
$rows = $db->loadObjectList();
return $rows;
}
function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

function mbFormat($bytes,$decimals=1)
{
    return number_format($bytes/(1024*1024),$decimals);
} 	
function showSmileyicons($text){
$simlecode= array(":)", ":(", ":D", "B)", ":o", ";(", "(sweat)", ":*", ":P", "(blush)", ":^)", "|-)", "(wait)","|(","(inlove)","(devil)","(yawn)","(puke)","(y)","(n)","(h)","(beer)","(dance)","(pizza)","(flex)","(cash)","(coffee)","(U)","(wait)","(smoke)","(whew)");
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/smile.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/sadsmile.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/bigsmile.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/cool.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/wink.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/crying.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/sweating.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/kiss.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/tongueout.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/blush.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/wondering.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/sleepy.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/wait.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/dull.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/inlove.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/devil.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/yawn.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/puke.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/yes.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/no.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/heart.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/beer.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/dance.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/pizza.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/muscle.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/cash.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/coffee.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/brokenheart.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/wait.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/smoke.gif\"/>";
$simlecodeimage[]="<img  src=\"".JURI::base()."components/com_awdwall/images/smilies/whew.gif\"/>";

for ($i=0;$i<count($simlecode);$i++)
{
	$text=str_replace($simlecode[$i],$simlecodeimage[$i],$text);
}
return $text;
}
function awdshowsmilyicon($textarea)
{
?>
<img src="<?php echo JURI::base();?>components/com_awdwall/images/smicon.png" alt="Insert emotions" title="Insert emotions" onclick="smilyshow('<?php echo $textarea;?>')" style="cursor: pointer;margin-right: 20px; margin-top: -32px;position: absolute;right: 20px; z-index: 1;"/>
<?php
}
function awdshowcommentsmilyicon($textarea)
{
?>

<img src="<?php echo JURI::base();?>components/com_awdwall/images/smicon.png" alt="Insert emotions" title="Insert emotions" onclick="smilyshow('<?php echo $textarea;?>')" style="cursor: pointer; clear:both; margin-top: -32px;position: absolute;left: 55%; z-index: 1; display:block;" />

<?php
}

function getSmileyicons($textarea){
?>
<!--<div style="padding:3px; float:right; height:25px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/smicon.png" alt="Insert emotions" title="Insert emotions" onclick="smilyshow('<?php echo $textarea;?>')" style="cursor:pointer"/></div>-->
<div style=" clear:both;"></div>
<div style="float:left; display:none; padding:3px; margin:3px 0px; width:96%;" id="smilycontainer_<?php echo $textarea;?>">
		
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/smile.gif" alt="smile" title="smile" onclick="insertSmiley(':)','<?php echo $textarea;?>')" style="cursor:pointer; padding:2px;" />
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/sadsmile.gif" alt="sad" title="sad"  onclick="insertSmiley(':(','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;" />
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/bigsmile.gif" alt="bigsmile" title="bigsmile"  onclick="insertSmiley(':D','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;" />
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/cool.gif" alt="cool" title="cool"  onclick="insertSmiley('B)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/wink.gif" alt="wink" title="wink"  onclick="insertSmiley(':o','<?php echo $textarea;?>')" style="cursor:pointer; padding:2px;"/>  
         
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/crying.gif" alt="crying" title="crying"  onclick="insertSmiley(';(','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/sweating.gif" alt="sweating" title="sweating"  onclick="insertSmiley('(sweat)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/kiss.gif" alt="kiss" title="kiss"  onclick="insertSmiley(':*','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
       
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/tongueout.gif" alt="tongueout" title="tongueout"  onclick="insertSmiley(':P','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/blush.gif" alt="blush" title="blush"  onclick="insertSmiley('(blush)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/wondering.gif" alt="wondering" title="wondering"  onclick="insertSmiley(':^)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
      
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/sleepy.gif" alt="sleepy" title="sleepy"  onclick="insertSmiley('|-)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/wait.gif"  alt="wait" title="wait" onclick="insertSmiley('(wait)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/dull.gif" alt="dull" title="dull"  onclick="insertSmiley('|(','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/inlove.gif"  alt="inlove" title="inlove" onclick="insertSmiley('(inlove)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/devil.gif" alt="devil" title="devil"  onclick="insertSmiley('(devil)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/yawn.gif"  alt="yawn" title="yawn" onclick="insertSmiley('(yawn)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/puke.gif" alt="puke" title="puke"  onclick="insertSmiley('(puke)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/yes.gif" alt="yes" title="yes"  onclick="insertSmiley('(y)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/no.gif" alt="no" title="no"  onclick="insertSmiley('(n)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/heart.gif" alt="heart" title="heart"  onclick="insertSmiley('(h)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/beer.gif" alt="beer" title="beer"  onclick="insertSmiley('(beer)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/dance.gif"  alt="dance" title="dance" onclick="insertSmiley('(dance)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/pizza.gif"  alt="pizza" title="pizza" onclick="insertSmiley('(pizza)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/muscle.gif"  alt="muscle" title="muscle" onclick="insertSmiley('(flex)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/cash.gif"  alt="cash" title="cash" onclick="insertSmiley('(cash)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/coffee.gif"  alt="coffee" title="coffee" onclick="insertSmiley('(coffee)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/brokenheart.gif"  alt="brokenheart" title="brokenheart" onclick="insertSmiley('(U)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/wait.gif"  alt="wait" title="wait" onclick="insertSmiley('(wait)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/smoke.gif"  alt="smoke" title="smoke" onclick="insertSmiley('(smoke)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
        <img src="<?php echo JURI::base();?>components/com_awdwall/images/smilies/whew.gif"  alt="whew" title="whew" onclick="insertSmiley('(whew)','<?php echo $textarea;?>')"  style="cursor:pointer; padding:2px;"/>
</div>
<div style=" clear:both;"></div>
<?php
}
	/*
	#This function adds statuses to jomwall
	$message=Plain text Message
	$attachment=Link to video,imge,mp3
	$userid=id of user whose status should change
	$imgpath=Path of image to append after message
	$customparams=Extra fields
	*/
	function addtostream($message,$attachment,$type='text',$userid,$imgpath,$customparams=array())
	{ 
		require_once (JPATH_SITE.'/components/com_awdwall/models/wall.php');
		require_once (JPATH_SITE.'/components/com_awdwall/controller.php');
		$db 		= &JFactory::getDBO();
		
		$mainframe	=& JFactory::getApplication();
		$itemId = AwdwallHelperUser::getComItemId();
		$user 		= &JFactory::getUser($userid);	
		$receiverId = $user->id;	
		$groupId = JRequest::getInt('groupid', NULL);
		$title=$description=$fileName=$images_array1=$tags=$images_array=$link_root='';
		if((int)$user->id)
		{
			
			if($type=='link')
			{
			$link=$attachment;
					$file = @fopen($attachment, "r"); 
					if (!empty($file))
					{

					
					$data = '';
					while (!feof($file))
					{
						$data .= fgets($file, 1024);
					}
					if (!empty($data))
					{
					// get title
					$pattern =  "'<title>(.*?)<\/title>'s";		
					preg_match_all($pattern, $data, $matches);
					$title = $matches[1][0];
					$tags = get_meta_tags($link);
					$description = $tags['description'];
	
					$image_regex = '/<img[^>]*'.'src=[\"|\'](.*)[\"|\']/Ui';
					preg_match_all($image_regex, $data, $img, PREG_PATTERN_ORDER);
					$images_array = $img[1];
					
					preg_match("/^(http:\/\/)?([^\/]+)/i", $attachment, $link_root);
					$first_link = 'http://'.$link_root[2];
					
				$n = count($images_array);
				if ($n>0)
				{	
					for($k=0; $k<$n;$k++)
					{	
						$check = strrpos($images_array[$k], 'http');
						if((string)$check == "")
						{			
							$images_array[$k] = $first_link . $images_array[$k];
						}
						
						if(getimagesize($images_array[$k]))
						{
							list($width, $height) = getimagesize($images_array[$k]);			
							if($width >= 100) 
							{	
								$images_array1=$images_array[$k];
								break;
							}
						}				
							
					}
				}	
				}
					} 
					
			if(!empty($images_array1))
			{
					$wall = &JTable::getInstance('Wall', 'Table');
					$wall->user_id 		= $user->id;					
					$wall->type			= 'link';
					$wall->commenter_id	= $user->id;
					$wall->group_id		= NULL;
					$wall->user_name	= '';
					$wall->avatar		= '';
					$wall->message		= $message;
					$wall->reply		= 0;
					$wall->is_read		= 0;
					$wall->is_pm		= 0;
					$wall->is_reply		= 0;
					$wall->posted_id	= NULL;
					$wall->wall_date	= time();
					
					$title 			= ltrim($title);
					$title 			= rtrim($title);
					$description 	= ltrim($description);
					$description 	= rtrim($description);
					$fileName=$images_array1;
					// store wall to database
					if (!$wall->store()){				

					}
					$wallId = $wall->id;				
					$sql = 'INSERT INTO #__awd_wall_links(wall_id, title, link, path, description) VALUES("'.$wallId .'","' . $title . '", "' . $attachment . '", "' . $fileName . '","' . $description . '")';
					$db->setQuery($sql);
					$db->query();
					$feedsource = basename($imgpath, ".png");
					$query = "INSERT INTO #__awd_wall_social_feeds(wallid,type) VALUES(" . $wall->id . ",'".$feedsource."')";
					$db->setQuery($query);
					$db->query();
			}
			else
			{
					$wall = &JTable::getInstance('Wall', 'Table');
					$wall->user_id 		= $user->id;					
					$wall->type			= 'text';
					$wall->commenter_id	= $user->id;
					$wall->group_id		= NULL;
					$wall->user_name	= '';
					$wall->avatar		= '';
					$wall->message		= $message;
					$wall->reply		= 0;
					$wall->is_read		= 0;
					$wall->is_pm		= 0;
					$wall->is_reply		= 0;
					$wall->posted_id	= NULL;
					$wall->wall_date	= time();
					if (!$wall->store()){				

					}
					$feedsource = basename($imgpath, ".png");
					
					$query = "INSERT INTO #__awd_wall_social_feeds(wallid,type) VALUES(" . $wall->id . ",'".$feedsource."')";
					$db->setQuery($query);
					$db->query();
			}
				
			}
			else
			{
					$wall = &JTable::getInstance('Wall', 'Table');
					$wall->user_id 		= $user->id;					
					$wall->type			= 'text';
					$wall->commenter_id	= $user->id;
					$wall->group_id		= NULL;
					$wall->user_name	= '';
					$wall->avatar		= '';
					$wall->message		= $message;
					$wall->reply		= 0;
					$wall->is_read		= 0;
					$wall->is_pm		= 0;
					$wall->is_reply		= 0;
					$wall->posted_id	= NULL;
					$wall->wall_date	= time();
					if (!$wall->store()){				

					}
					$feedsource = basename($imgpath, ".png");
					
					$query = "INSERT INTO #__awd_wall_social_feeds(wallid,type) VALUES(" . $wall->id . ",'".$feedsource."')";
					$db->setQuery($query);
					$db->query();
					
			}
		
		}
	
	}

}
?>