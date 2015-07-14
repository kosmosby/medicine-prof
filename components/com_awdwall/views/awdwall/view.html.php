<?php

/**
 * @version 3.0
 * @package JomWALL CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access

defined('_JEXEC') or die('Restricted access');



jimport( 'joomla.application.component.view');

class AwdwallViewAwdwall extends JViewLegacy

{

	function display($tpl = null)

	{			

		$mainframe	=& JFactory::getApplication();

		$itemId 	= AwdwallHelperUser::getComItemId();

		$task 			= JRequest::getCmd('task', '');

		$page 			= JRequest::getCmd('awd_page', 0);

		$wid 			= JRequest::getInt('wid', 0);	

		$layout = JRequest::getCmd('layout', '');

		$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';
		//echo $wallalbumfile;
		$jomalbumexist='';
		if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
		{
			$jomalbumexist=1;
		}
		$this->assignRef('jomalbumexist', $jomalbumexist);

		$hwdvideosharefile = JPATH_SITE . '/components/com_hwdvideoshare/hwdvideoshare.php';
		$hwdvideoshare='';
		if (file_exists($hwdvideosharefile)) // if com_awdjomalbum install then only
		{
			$hwdvideoshare=1;
		}
		$this->assignRef('hwdvideoshare', $hwdvideoshare);

		//color

		$db		=& JFactory::getDBO();

//		$query = "SELECT params FROM #__components WHERE `name`='Colors' AND `admin_menu_link`='option=com_awdwall&controller=colors'";
		$link='index.php?option=com_awdwall&controller=colors';
		
		
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
		   $params['timestamp_format'] = '1';
		   $params['access_level'] = '1';
		   $params['display_online'] = '0';
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
		   // store the combined result
		   $paramsString = json_encode( $params );
		   $db->setQuery('UPDATE #__extensions SET params = ' .$db->quote( $paramsString ) .' WHERE element = "com_awdwall" and type="component" ' );
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}
		}
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$awdparams = json_decode( $db->loadResult(), true );
		if(empty($awdparams))
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
		   $paramsString = json_encode( $awdparams );
		   $db->setQuery('UPDATE #__menu SET params = ' .$db->quote( $paramsString ) .' WHERE link = "'.$link.'"' );
		 //  echo 'UPDATE #__menu SET params = ' .$db->quote( $paramsString ) .' WHERE link = "'.$link.'"';
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}
		 }
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$params = json_decode( $db->loadResult(), true );
		for($i=1; $i<=14; $i++)
		{
			$str_color = 'color'.$i;			
			$color[$i]= $params[$str_color];
		}

		
		
		//usertype of login user...	
		$user = &JFactory::getUser();
		$query="SELECT group_id FROM #__user_usergroup_map where user_id=".$user->id;
		$db->setQuery($query);
		$user_groupidList= $db->loadObjectList();
		
		//usertype access
		$groupList=array(5,6,7,8);
		$user_groupid='';
		foreach ($user_groupidList as $ugroupid)
		{
			if(in_array($ugroupid->group_id,$groupList)){
			$user_groupid='Can add article';
			}
		}
			
		$admin_groupid=array(7,8);

		$can_delete='';
		foreach ($user_groupidList as $ugroupid)
		{
			if(in_array($ugroupid->group_id,$admin_groupid)){
			$can_delete=1;
			}
		}
		if($owner)
		$can_delete=1;
		
		$this->assignRef('can_delete', $can_delete);
		// get configuration from database

		//$config = &JComponentHelper::getParams('com_awdwall');
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');

		$postLimit 		= $config->get('nof_post', 15);
		$commentLimit 	= $config->get('nof_comment', 3);
		$displayName 	= $config->get('display_name', 1);
		$videoLightbox  = $config->get('video_lightbox', 0);
		$width  		= $config->get('width', 725);
		$imageLightbox  = $config->get('image_lightbox', 1);
		$privacy 		= $config->get('privacy', 0);
		$displayVideo 	= $config->get('display_video', 1);
		$displayImage 	= $config->get('display_image', 1);
		$displayMusic 	= $config->get('display_music', 1);
		$displayLink 	= $config->get('display_link', 1);
		$displayFile 	= $config->get('display_file', 1);
		$displayTrail 	= $config->get('display_trail', 0);
		$displayJing 	= $config->get('display_jing', 1);
		$displayEvent 	= $config->get('display_event', 1);
		$displayArticle = $config->get('display_article', 0);
		$displayCommentLike=$config->get('displayCommentLike', 1);
		$display_filterwall = $config->get('display_filterwall', 1);
		$display_filtervideo = $config->get('display_filtervideo', 1);
		$display_filterimage = $config->get('display_filterimage', 1);
		$display_filtermusic = $config->get('display_filtermusic', 1);
		$display_filterlink = $config->get('display_filterlink', 1);
		$display_filterfile = $config->get('display_filterfile', 1);
		$display_filterjing = $config->get('display_filterjing', 1);
		$display_filtertrail = $config->get('display_filtertrail', 0);
		$display_filterevent = $config->get('display_filterevent', 1);
		$display_filterarticle = $config->get('display_filterarticle', 0);
		$display_hightlightbox = $config->get('display_hightlightbox', 0);
		$display_filterpm = $config->get('display_filterpm', 0);
		$displayPm = $config->get('display_pm', 1);
		$displayShare = $config->get('display_share', 1);
		$displayLike = $config->get('display_like', 1);
		$moderator_users = $config->get('moderator_users', '');
		$display_group = $config->get('display_group', 1);
		$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
		$moderator_users=explode(',',$moderator_users);

		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);

		$fields			= $config->get('fieldids', '');


		$arrTask = array();

		$arrTask[] = 'videos';

		$arrTask[] = 'images';

		$arrTask[] = 'trails';

		$arrTask[] = 'music';

		$arrTask[] = 'links';

		$arrTask[] = 'files';
		
		$arrTask[] = 'jing';
		
		$arrTask[] = 'events';

		$arrTask[] = 'article';

		$user = &JFactory::getUser();

		// get userid from url

		$wuid = JRequest::getInt('wuid', 0);		

		// get all msg from databases

		$wallModel = & $this->getModel('wall');

		// build where clause	

		$where = array();

		if($task == 'videos')

			$where[] = 'type = "video"';

		elseif($task == 'images')

			$where[] = 'type = "image"';

		elseif($task == 'trails')

			$where[] = 'type = "trail"';

		elseif($task == 'pm')

			$where[] = 'is_pm = 1';

		elseif($task == 'music')

			$where[] = 'type = "mp3"';

		elseif($task == 'links')

			$where[] = 'type = "link"';	

		elseif($task == 'files')

			$where[] = 'type = "file"';
			
		elseif($task == 'jing')

			$where[] = 'type = "jing"';
			
		elseif($task == 'events')
		
			$where[] = 'type = "event"';
			
		elseif($task == 'article')
		
			$where[] = 'type = "article"';
			
		if($task == 'friends')

			$where[] = 'aw.reply = 0';

		else

			$where[] = 'reply = 0';

		

		if($wuid){

			if($task == 'friends')

				$where[] = '(aw.user_id = ' . (int)$wuid .' OR aw.commenter_id = ' . (int)$wuid . ' )';

			else

				$where[] = '(user_id = ' . (int)$wuid .' OR commenter_id = ' . (int)$wuid . ' )';

			if($wuid != $user->id){

				if($task == 'friends')

					$where[] = '(IF(aw.is_pm=1, aw.commenter_id = ' . $user->id . ', 1))';

				else

					$where[] = '(IF(is_pm=1, commenter_id = ' . $user->id . ', 1))';

			}

		}else{

			if($task == 'friends')

				$where[] = 'aw.is_pm = 0';

			else

				$where[] = 'is_pm = 0';

		}

		if($wid){

			if($task == 'friends')

				$where[] = 'aw.id = '. $wid;

			else

				$where[] = 'id = '. $wid;

		}

		
		//Private group id
		$sql='select id from #__awd_groups where privacy="2"';
		$db->setQuery($sql);
		$private_group=$db->loadObjectList();
		for($i=0;$i<count($private_group);$i++)
		{
			$grouplist[$i]=$private_group[$i]->id;
		}
		if($grouplist)
			$grpid=implode(",",$grouplist);

//		if($layout == 'mywall')
//		{
//			if($user->id!=$wuid && $grpid)
//			{
//				$where[] = '(IF(group_id !=" null", group_id NOT IN ('.$grpid.'), id))';
//			}
//		}
		if($layout == 'mywall')
		{
			//$where[] = 'group_id IS NULL or group_id=0';
			$where[] = 'group_id IS NULL ';
		}
		$where[] = 'wall_date IS NOT NULL';


		//get total event attend list of user
		
		$sql= 'select wall_id FROM #__awd_wall_event_attend where user_id = ' . $user->id . ' AND status = 1';
		$db->setQuery($sql);
		$walleventlist=$db->loadObjectList();
		for($i=0;$i<count($walleventlist);$i++)
		{
			$eventlist[$i]=$walleventlist[$i]->wall_id;
		}
		if($eventlist)
			$wallid=implode(",",$eventlist);
		

		//get friend list
		
		$sql= 'select connect_from FROM #__awd_connection where connect_to = ' . $user->id . ' AND status = 1 AND pending=0';
		$db->setQuery($sql);
		$friendlist=$db->loadObjectList();
		for($i=0;$i<count($friendlist);$i++)
		{
			$friend=$friendlist[$i];
			$list1[$i]=$friend->connect_to;
			$list1[$i]=$friend->connect_from;
		}
		$friends=implode(",",$list1);
		
		//friend of friend list of user
				
		if($friends)
		$sql= 'select connect_from FROM #__awd_connection where connect_to in (' . $friends . ') AND status = 1 AND pending=0';
		//echo $sql;
		$db->setQuery($sql);
		$friendoffriendlist=$db->loadObjectList();
		for($i=0;$i<count($friendoffriendlist);$i++)
		{
			$friendoffriend=$friendoffriendlist[$i];
			//$list2[$i]=$friendoffriend->connect_to;
			$list2[$i]=$friendoffriend->connect_from;
		}
		
		$friendoffriends=implode(",",$list2);
		
		
		if($friends != '')
		{
			$friendsuser = $friends.','.$user->id; 
		}
		else
		$friendsuser = $user->id;
		
		if($friendoffriends != '')
			$friendoffriendsuser = $friendoffriends.','.$user->id;
		else
		$friendoffriendsuser = $user->id;
		
		//Wall posts by friends
		
		$sql='select id from #__awd_wall where commenter_id IN (' . $friendsuser . ')';
		
		$db->setQuery($sql);
		$friendsPost=$db->loadObjectList();
		for($i=0;$i<count($friendsPost);$i++)
		{
			$friendPost=$friendsPost[$i];
			$fPostslist[$i]=$friendPost->id;
		}
		
		$fPosts=implode(",",$fPostslist);
		
		//Wall posts by friends of friends
		
		$sql='select a.id as wallid from #__awd_wall as a left join #__awd_wall_privacy as b on a.id=b.wall_id where a.commenter_id IN (' . $friendoffriendsuser . ') AND b.privacy="2"' ;
		//echo $sql;
		$db->setQuery($sql);
		$fofriendsPost=$db->loadObjectList();
		for($i=0;$i<count($fofriendsPost);$i++)
		{
			$foffriendPost=$fofriendsPost[$i];
			$fofPostslist[$i]=$foffriendPost->wallid;
		}
		
		$fofPosts=implode(",",$fofPostslist);
		

		$totalPosts='';
		if($fofPosts !='' && $totalPosts!='')
			$totalPosts=$totalPosts.','.$fofPosts;
		if($fofPosts !='' && $totalPosts=='')
			$totalPosts=$fofPosts;
		if($fPosts !='' && $totalPosts=='')
			$totalPosts=$fPosts;
		if($fPosts !='' && $totalPosts!='')
			$totalPosts=$totalPosts.','.$fPosts;		
		
		//total Wall posts under privacy 
		if($totalPosts)
			$whrprivacy=' and wall_id not in ('.$totalPosts.')';
			
		$sql='select wall_id from #__awd_wall_privacy where privacy!="0" '.$whrprivacy.' order by wall_id';
		
		$db->setQuery($sql);
		$privacyPOsts=$db->loadObjectList();
		for($i=0;$i<count($privacyPOsts);$i++)
		{
			$privacyPOst=$privacyPOsts[$i];
			$privacyPOstlist[$i]=$privacyPOst->wall_id;
		}
		$privacyPOsts=implode(",",$privacyPOstlist);
	if($wuid!=$user->id)
		{
			if(in_array($wuid,$friendlist))
			{
				
			}
			else if(in_array($wuid,$friendoffriendlist))
			{
				$where[]= ' id NOT IN (select a.id as wallid from #__awd_wall as a left join #__awd_wall_privacy as b on a.id=b.wall_id where (a.commenter_id ='.$wuid.' or a.user_id='.$wuid.') AND b.privacy="1")';
			}
			else
			{
				$where[]= ' id NOT IN (select a.id as wallid from #__awd_wall as a left join #__awd_wall_privacy as b on a.id=b.wall_id where (a.commenter_id ='.$wuid.' or a.user_id='.$wuid.') AND (b.privacy="1" or b.privacy="2"))';
			}
		}
		$whr=array();
						
		if($fPosts !='')

		{
			$whr[]= '((id IN (select wall_id FROM #__awd_wall_privacy where privacy = "1"  and wall_id in (' .$fPosts. '))))';
		}
		if($totalPosts!='')	
			$whr[]= '((id IN (select wall_id FROM #__awd_wall_privacy where privacy = "2" and wall_id in (' .$totalPosts. '))))';
			
		$whr[]= '((id IN (select wall_id FROM #__awd_wall_privacy where privacy = "0")))';
		
		if($wid!='' || $layout==mywall)
		{
		}
		else
		{
			if($privacyPOsts)
			$where[]= '((id NOT IN ('.$privacyPOsts.')))';
		}
		if((int)$privacy==1){ 

			if($layout == 'main'){

				$where[] = '((commenter_id IN (' .$friendsuser. ')) OR (user_id = ' . $user->id . ' OR commenter_id = ' . $user->id . ') OR (group_id IS NOT NULL))';

			}else{

				$where[] = '((commenter_id IN (' .$friendsuser. ')) OR (user_id = ' . $user->id . ' OR commenter_id = ' . $user->id . '))';

			}
		}
		
		if((int)$privacy==2){ 

			
			if($friendoffriends=='')
				$friendoffriends=$user->id;
				
			if($friendoffriends!='' && $friends!='')
			{
				$friendoffriends=$friendoffriends.','.$friends;
			}
			if($friendoffriends=='' && $friends!='')
			{
				$friendoffriends=$friends;
			}

			if($layout == 'main'){
				
				$where[] = '((commenter_id IN ('.$friendoffriends.')) OR (user_id = ' . $user->id . ' OR commenter_id = ' . $user->id . ') OR (group_id IS NOT NULL))';

			}else{

				$where[] = '((commenter_id IN (' .$friendoffriends. ')) OR (user_id = ' . $user->id . ' OR commenter_id = ' . $user->id . '))';

			}
		}

		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';



		if($_REQUEST['attendevent'] && $wallid)
		{
			$where =$where. ' OR id IN ('.$wallid.')';
		}

		$whr = count($whr) ? ' OR ' . implode( ' OR ', $whr ) : '';
		if($task!='' || $wid!='' || $layout==mywall)
		$where =$where;
		else
		$where =$where . $whr;
		

		$offset = $page*$postLimit;

		if($task == 'friends'){

			$msgs 	= $wallModel->getAllMsgFriends($postLimit, $where, $offset);

			$nofMsgs = $wallModel->countMsgFriends($where);

		}else{

			$msgs 	= $wallModel->getAllMsg($postLimit, $where, $offset, $privacy);

			$nofMsgs = $wallModel->countMsg($where, $privacy);

		}

		// if wid != 0 set page title for share link

		if($wid && isset($msgs[0])){

			$document	=& JFactory::getDocument();

			$pageTitle = preg_replace("/\<a([^>]*)\>([^<]*)\<\/a\>/i", "$2", $msgs[0]->message);

			if($msgs[0]->type == 'video'){

				$video = $wallModel->getVideoInfoByWid($msgs[0]->id);

				$pageTitle = $video->title;

			}			

			if($msgs[0]->type == 'image'){

				$image = $wallModel->getImageInfoByWid($msgs[0]->id);

				$pageTitle = $image->name;

			}

			if($msgs[0]->type == 'mp3'){

				$mp3 = $wallModel->getMp3InfoByWid($msgs[0]->id);

				$pageTitle = $mp3->title;

			}

			if($msgs[0]->type == 'link'){

				$link = $wallModel->getLinkInfoByWid($msgs[0]->id);

				$pageTitle = $link->title;

			}

			if($msgs[0]->type == 'file'){

				$file = $wallModel->getFileInfoByWid($msgs[0]->id);

				$pageTitle = $file->title;

			}
			if($msgs[0]->type == 'jing'){

				$jing = $wallModel->getJingInfoByWid($msgs[0]->id);

				$pageTitle = $jing->jing_title;

			}
			if($msgs[0]->type == 'event'){
			
				$event = $wallModel->getEventInfoByWid($msgs[0]->id);
				
				$pageTitle = $event->title;
			}
			if($msgs[0]->type == 'article'){
				$article = $wallModel->getArticleInfoByWid($msgs[0]->id);
				$pageTitle = $article->title;
			}

			$cName = AwdwallHelperUser::getDisplayName($msgs[0]->commenter_id);

			$pageTitle = addslashes(htmlspecialchars($pageTitle));

			$document->setTitle($pageTitle);

			$document->setDescription('Wrote by ' . $cName);

		}

	

		// Load the form validation behavior

		JHTML::_('behavior.formvalidation');

		

		// set receiver_id = user->id if wuid = 0

		if($wuid == 0)

			$wuid = $user->id;

		

		if($layout == 'mywall' || $layout == 'cbmywall'){

		require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'jslib.php');		

			// check friend to view

			// check privacy

			$isFriend = '';

			$showPosts = 1;

			$friendStatus = 1;

			if((int)$privacy == 1){

				if((int)$wuid != (int)$user->id){

					$showPosts = JsLib::isFriend($user->id, $wuid);				

				}

				if(!(int)$showPosts){

					$wallUserName = AwdwallHelperUser::getDisplayName($wuid);

					//$mainframe->redirect(JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&Itemid=' . $itemId, false), JText::sprintf("FRIEND TO VIEW USER WALL", $wallUserName));

				}

			}

			if((int)$privacy == 2){

				if((int)$wuid != (int)$user->id){

					$showPosts = JsLib::isFriendOfFriend($user->id, $wuid);				
				}

			}

			if((int)$wuid != (int)$user->id){

				$isFriend = JsLib::isFriend($user->id, $wuid);

				$friendStatus = JsLib::getFriendStatus($user->id, $wuid);

			}

			$latestPost = $wallModel->getLatestPostByUserId($wuid);

			$this->assignRef('latestPost', $latestPost);

			

			$basicInfo 		= JsLib::getUserBasicInfo($wuid, $fields);

			$totalFriends 	= JsLib::countFriends($wuid);

			$friendLimit 	= (int)$config->get('nof_friends', 4);

			if($friendLimit > 6)

				$friendLimit = 6;

			$friends 		= JsLib::getAllFriends($wuid, $friendLimit);

			// get 4 first groups to display

			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');

			$where = array();

			$where[] = 'ag.creator = ' . $wuid . ' OR agm.user_id =' . $wuid;

			

			$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';

			$groups = AwdwallModelGroup::getAllGrps($where, 4, 0);

			$this->assignRef('groups', $groups);

			// get friends need to approval

			$pendingFriends = JsLib::getPendingFriends($user->id);

			// get groups need to approval

			//$pendingGroups = JsLib::getPendingGroups($user->id);

			$this->assignRef('pendingFriends', $pendingFriends);

			$this->assignRef('pendingGroups', $pendingGroups);

			$this->assignRef('basicInfo', $basicInfo);

			$this->assignRef('totalFriends', $totalFriends);

			$this->assignRef('friends', $friends);

			$this->assignRef('isFriend', $isFriend);

			$this->assignRef('showPosts', $showPosts);

			$this->assignRef('friendStatus', $friendStatus);

			

		}

		if((int)$privacy == 1 || (int)$privacy == 2){

			require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'jslib.php');

		}

		
		//Userinfo of login user
		$userinfo = AwdwallHelperUser::getUserInfo($wuid);		
		$this->assignRef('userinfo', $userinfo);  
		
		//Total private message of user
		$totalpm=$wallModel->countpm($user->id);
		//echo $totalpm;

		// check group		

	

		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');

		$groupModel = new AwdwallModelGroup();		

		//Total groups created by user
		$myGrps = $groupModel->getMyGrps($wuid);
		$totalmygroup=count($myGrps);
		
		
		//Count Event attend by user
		$query="Select count(*) from #__awd_wall_event_attend as a left join #__awd_wall as b on a.wall_id=b.id where b.wall_date IS NOT NULL and a.user_id =".$user->id." and status='1' and b.user_id!=".$user->id; 
		$db->setQuery($query);
		$eventOfuser=$db->loadResult();

		//Count Event posted by user
		$query="Select count(*) from #__awd_wall where user_id =".$user->id." and type='event' and  wall_date IS NOT NULL "; 
		$db->setQuery($query);
		$eventpostOfuser=$db->loadResult();

		$totaleventOfuser=$eventOfuser+$eventpostOfuser;
		
		$this->assignRef('totaleventOfuser', $totaleventOfuser);
		
		$this->assignRef('totalmygroup', $totalmygroup);

		$this->assignRef('groupModel', $groupModel);			

		$this->assignRef('totalpm', $totalpm);

		$this->assignRef('color', $color);

		$this->assignRef('displayName', $displayName);

		$this->assignRef('wuid', $wuid);

		$this->assignRef('msgs', $msgs);

		$this->assignRef('wallModel', $wallModel);

		$this->assignRef('videoLightbox', $videoLightbox);

		$this->assignRef('imageLightbox', $imageLightbox);

		$this->assignRef('task', $task);

		$this->assignRef('nofMsgs', $nofMsgs);

		$this->assignRef('page', $page);

		$this->assignRef('postLimit', $postLimit);

		$this->assignRef('commentLimit', $commentLimit);

		$this->assignRef('privacy', $privacy);

		$this->assignRef('displayVideo', $displayVideo);

		$this->assignRef('displayImage', $displayImage);

		$this->assignRef('displayMusic', $displayMusic);

		$this->assignRef('displayLink', $displayLink);

		$this->assignRef('displayFile', $displayFile);

		$this->assignRef('displayTrail', $displayTrail);
		
		$this->assignRef('displayJing', $displayJing);
		
		$this->assignRef('displayEvent', $displayEvent);
		$this->assignRef('displayCommentLike', $displayCommentLike);
		$this->assignRef('display_hightlightbox', $display_hightlightbox);
		$this->assignRef('displayArticle', $displayArticle);
		$this->assignRef('display_filterwall', $display_filterwall);
		$this->assignRef('display_filtervideo', $display_filtervideo);
		$this->assignRef('display_filterimage', $display_filterimage);
		$this->assignRef('display_filtermusic', $display_filtermusic);
		$this->assignRef('display_filterlink', $display_filterlink);
		$this->assignRef('display_filterfile', $display_filterfile);
		$this->assignRef('display_filterjing', $display_filterjing);
		$this->assignRef('display_filtertrail', $display_filtertrail);
		$this->assignRef('display_filterevent', $display_filterevent);
		$this->assignRef('display_filterarticle', $display_filterarticle);
		$this->assignRef('display_filterpm', $display_filterpm);
		$this->assignRef('displayPm', $displayPm);
		$this->assignRef('displayShare', $displayShare);
		$this->assignRef('displayLike', $displayLike);
		$this->assignRef('moderator_users', $moderator_users);
		$this->assignRef('display_group', $display_group);
		$this->assignRef('display_group_for_moderators', $display_group_for_moderators);
		
		$this->assignRef('arrTask', $arrTask);
		$this->assignRef('groupList', $groupList);
		$this->assignRef('user_groupid', $user_groupid);


		
		//joomla categorylist
		$query = "SELECT id AS value, title AS text FROM #__categories WHERE extension='com_content'";
		//echo $query ;
		$db->setQuery($query);
		$catrows = $db->loadObjectList();
		if(count($catrows))
		{
			$types1[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select Category' ) .' -' );
			foreach( $db->loadObjectList() as $obj )
			{
			$types1[] = JHTML::_('select.option',  $obj->value, JText::_( $obj->text ) );
			}
			
			
			$lists['catid'] 	= JHTML::_('select.genericlist',   $types1, 'catid', 'class="inputbox" style="width:150px; height:20px; font-size:14px; " size="1" ', 'value', 'text', "$catid" );
		}

		$this->assignRef('lists', $lists);
		

		// add modal box joomla

		// JHTML::_('behavior.modal');
		
		//Select AM PM...
			$amPmSelect		= array();
			$amPmSelect[]		= JHTML::_('select.option',  'AM', "am" );
			$amPmSelect[]		= JHTML::_('select.option',  'PM', "pm" );
			$startAmPmSelect	= JHTML::_('select.genericlist',  $amPmSelect , 'starttime-ampm', array('class'=>'required inputbox','style'=>'width:50px !important;'), 'value', 'text', '' , false );
			$endAmPmSelect		= JHTML::_('select.genericlist',  $amPmSelect , 'endtime-ampm', array('class'=>'required inputbox','style'=>'width:50px !important;'), 'value', 'text', '' , false );
		$this->assignRef('startAmPmSelect', $startAmPmSelect);
		$this->assignRef('endAmPmSelect', $endAmPmSelect);
		
		//Select Hour...
			for($i = 1; $i <= 12; $i++)
			{
				$hours[] = JHTML::_('select.option',  $i, sprintf( "%02d" ,$i) );
			}
		$startHourSelect		= JHTML::_('select.genericlist',  $hours, 'starttime-hour', array('class'=>'required inputbox','style'=>'width:50px !important;'), 'value', 'text', '' , false );
		$endHourSelect			= JHTML::_('select.genericlist',  $hours, 'endtime-hour', array('class'=>'required inputbox','style'=>'width:50px !important;'), 'value', 'text', '' , false );
		$this->assignRef('startHourSelect', $startHourSelect);
		$this->assignRef('endHourSelect', $endHourSelect);
		
		//Select Minute...
		$minutes	= array();
		$minutes[]	= JHTML::_('select.option',  "00", "00" );
		$minutes[]	= JHTML::_('select.option',  15, "15" );
		$minutes[]	= JHTML::_('select.option',  30, "30" );
		$minutes[] 	= JHTML::_('select.option',  45, "45" );

		$startMinSelect		= JHTML::_('select.genericlist',  $minutes , 'starttime-minute', array('class'=>'required inputbox','style'=>'width:50px !important;'), 'value', 'text', '' , false );
		$endMinSelect		= JHTML::_('select.genericlist',  $minutes , 'endtime-minute', array('class'=>'required inputbox','style'=>'width:50px !important;'), 'value', 'text', '' , false );
		$this->assignRef('startMinSelect', $startMinSelect);
		$this->assignRef('endMinSelect', $endMinSelect);
		
		$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';
		if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
		{
			$query="Select * from #__awd_jomalbum_userinfo where userid=".$wuid; 
			$db->setQuery($query);
			$rows=$db->loadObjectList();
			$this->assignRef('albumuserinfo', $rows[0]);
		}
		
		parent::display($tpl);

	}
	public function loginpage($tpl = null)
	{
			$db		=& JFactory::getDBO();
			$user 	= &JFactory::getUser();
			$Itemid = AwdwallHelperUser::getComItemId();
			$mainframe = JFactory::getApplication('site');
			$mainlink=JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$Itemid  , false );
			if($user->id)
			{
				$mainframe->redirect($mainlink);
			}
			$link='index.php?option=com_awdwall&controller=colors';
			$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
			$params = json_decode( $db->loadResult(), true );
			for($i=1; $i<=14; $i++)
			{
				$str_color = 'color'.$i;			
				$color[$i]= $params[$str_color];
			}
		
		$this->assignRef('color', $color);
		parent::display($tpl);
	}
	public function getrealtimecomment($tpl = null)
	{
		$task 			= JRequest::getCmd('type', '');
		$db		=& JFactory::getDBO();
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$commentLimit 	= $config->get('nof_comment', 3);		
		$displayName 	= $config->get('display_name', 1);		
		$user = &JFactory::getUser();
		$wid = JRequest::getInt('wid', 0);
		$wallModel = & $this->getModel('wall');
			$comments 	= $wallModel->getrealtimecomment( $wid);
			$this->assignRef('comments', $comments);
			$user = &JFactory::getUser();
			$query="SELECT group_id FROM #__user_usergroup_map where user_id=".$user->id;
			$db->setQuery($query);
			$user_groupidList= $db->loadObjectList();
			
			$admin_groupid=array(7,8);
	
			$can_delete='';
			foreach ($user_groupidList as $ugroupid)
			{
				if(in_array($ugroupid->group_id,$admin_groupid)){
				$can_delete=1;
				}
			}
			if($owner)
			$can_delete=1;
			$this->assignRef('can_delete', $can_delete);
			parent::display($tpl);
	}
	
	function viewVideo($tpl = null)

	{

		$mainframe	=& JFactory::getApplication();

		$user = &JFactory::getUser();

		$db =& JFactory::getDBO();

		$videoId = JRequest::getVar('videoid', '', 'cmd');

		if(empty($videoId)){

 			$url	= JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&wuid=' . $user->id, false);

 			$mainframe->redirect($url, JText::_('No proper video id'), 'warning');

		}

	

		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'video.php');

		$video =& JTable::getInstance('Video', 'Table');

		if (!$video->load($videoId)) {

			$url	= JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&wuid=' . $user->id, false);

			$mainframe->redirect($url, JText::_('Video not available'), 'warning');

		}

		

		// Load embed code if video is linked		

		$providerName = $video->type;

		require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'videos' . DS . $providerName . '.php');

		$videoProvider	= JString::ucfirst($video->type);

		$className		= 'TableVideo' . $videoProvider;	

		$videoObj		= new $className();

		$video->player	= $videoObj->getViewHTML($video->video_id);

		$this->assignRef('video', $video);
		
		$app = JFactory::getApplication('site');
		 
		$config =  & $app->getParams('com_awdwall');
		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		parent::display($tpl);
		
		parent::display($tpl);

	}

	

	function viewImage($tpl = null)

	{

		global $mainframe;

		

		$user = &JFactory::getUser();

		$db =& JFactory::getDBO();

		$imageId = JRequest::getVar('imageid', '', 'cmd');

		if(empty($imageId)){

 			$url	= JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&wuid=' . $user->id, false);

 			$mainframe->redirect($url, JText::_('No proper image id'), 'warning');

		}

	

		$query = 'SELECT * FROM #__awd_wall_images WHERE id = ' . (int)$imageId;

		$db->setQuery($query);

		$image = $db->loadObject();

		if (!is_object($image)) {

			$url	= JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&wuid=' . $user->id, false);

			$mainframe->redirect($url, JText::_('Image not available'), 'warning');

		}

		

		// get user_id from wall

		$query = 'SELECT user_id FROM #__awd_wall WHERE id = ' . (int)$image->wall_id;

		$db->setQuery($query);

		$userId = $db->loadResult();

		$this->assignRef('userId', $userId);

		$this->assignRef('image', $image);

		$app = JFactory::getApplication('site');
		 
		$config =  & $app->getParams('com_awdwall');
		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		parent::display($tpl);

	}

	

	public function getMsgBlock($tpl = null)

	{

		$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';
		//echo $wallalbumfile;
		$jomalbumexist='';
		if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
		{
			$jomalbumexist=1;
		}
		$this->assignRef('jomalbumexist', $jomalbumexist);

		$hwdvideosharefile = JPATH_SITE . '/components/com_hwdvideoshare/hwdvideoshare.php';
		$hwdvideoshare='';
		if (file_exists($hwdvideosharefile)) // if com_awdjomalbum install then only
		{
			$hwdvideoshare=1;
		}
		$this->assignRef('hwdvideoshare', $hwdvideoshare);
		
		$wallModel = & $this->getModel('wall');

		$receiverId = JRequest::getInt('receiver_id', 0);

		$wid 		= JRequest::getInt('wid', 0);

		$mywall 	= JRequest::getString('layout', '');

		$type		= JRequest::getString('type', 'text');

		$msg = $_REQUEST['awd_message'];

		$msg = AwdwallHelperUser::checkTrailPost($msg);		

		$msg 		= AwdwallHelperUser::formatUrlInMsg($msg);

		$receiver 	=  &JFactory::getUser($receiverId);

		// get configuration from database
	
		//$config = &JComponentHelper::getParams('com_awdwall');
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');

		$displayName 	= $config->get('display_name', 1);
		$videoLightbox  = $config->get('video_lightbox', 0);
		$width  		= $config->get('width', 725);
		$imageLightbox  = $config->get('image_lightbox', 1);

		$user = &JFactory::getUser();

		// check group

		$groupId = JRequest::getInt('groupid', 0);

		if($groupId){

			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');

			$groupModel = new AwdwallModelGroup();

			$owner = $groupModel->checkGrpOwner($user->id, $groupId);

			$this->assignRef('owner', $owner);

			// get group info

			$grpInfo = $groupModel->getGroupInfo($groupId);

			$this->assignRef('grpInfo', $grpInfo);

		}

		$this->assignRef('videoLightbox', $videoLightbox);
		$this->assignRef('width', $width);
		$this->assignRef('imageLightbox', $imageLightbox);

		$this->assignRef('receiverId', $receiverId);

		$this->assignRef('receiver', $receiver);

		$this->assignRef('msg', $msg);

		$this->assignRef('wid', $wid);

		$this->assignRef('layout', $mywall);

		$this->assignRef('type', $type);

		$this->assignRef('postedTime', time());

		$this->assignRef('wallModel', $wallModel);

		$app = JFactory::getApplication('site');
		 
		$config =  & $app->getParams('com_awdwall');
		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		parent::display($tpl);

	}

	

	public function getCommentBox($tpl = null)

	{

		$receiverId = JRequest::getInt('commenter_id', 0);

		$wallId 	= JRequest::getInt('wall_id', 0);

		$cid 		= JRequest::getInt('cid', 0);

		$isReply 	= JRequest::getInt('is_reply', 0);

		$this->assignRef('receiverId', $receiverId);

		$this->assignRef('wallId', $wallId);

		$this->assignRef('cid', $cid);

		$this->assignRef('isReply', $isReply);

		$app = JFactory::getApplication('site');
		 
		$config =  & $app->getParams('com_awdwall');
		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		parent::display($tpl);

	}

	

	public function getCommentBlock($tpl = null)
	{
		$cid 		= JRequest::getInt('cid', 0);
		$wid 		= JRequest::getInt('wid', 0);
		//$msg 		= JRequest::getString('awd_comment_' . $cid);
		//$msg 		= JRequest::getString('awd_comment_' . $cid);
		$msg= JRequest::getString('awd_comment_' . $cid);
		$msg = AwdwallHelperUser::formatUrlInMsg($msg);	
		$this->assignRef('wid', $wid);
		$this->assignRef('msg', nl2br($msg));
		$this->assignRef('postedTime', time());
		$app = JFactory::getApplication('site');
		 
		$config =  & $app->getParams('com_awdwall');
		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		parent::display($tpl);
	}

	

	public function getLikeMsg($tpl = null)

	{

		// get people that have the same like with this post

		$db 	= &JFactory::getDBO();

		$wid 	= JRequest::getInt('wid', 0);		
		$Itemid 	= AwdwallHelperUser::getComItemId();		

		$query 	= 'SELECT count(*) as totallike FROM #__awd_wall_comment_like WHERE wall_id = ' . (int)$wid . ' ';
		$db->setQuery($query);
		$totallike = $db->loadResult();
		$query 	= 'SELECT * FROM #__awd_wall_comment_like WHERE wall_id = '.(int)$wid.' GROUP BY user_id order by rand() limit 5';
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$this->assignRef('rows', $rows);
		$this->assignRef('Itemid', $Itemid);
		$this->assignRef('totallike', $totallike);

		parent::display($tpl);

	}

	

	public function getPMBox($tpl = null)

	{

		$receiverId = JRequest::getInt('commenter_id', 0);

		$wallId 	= JRequest::getInt('wall_id', 0);

		$cid = JRequest::getInt('cid', 0);

		$this->assignRef('receiverId', $receiverId);

		$this->assignRef('wallId', $wallId);

		$this->assignRef('cid', $cid);

		parent::display($tpl);

	}

	

	public function getPMBlock($tpl = null)

	{

		$cid 		= JRequest::getInt('cid', 0);

		$wid 		= JRequest::getInt('wid', 0);

		$msg 		= JRequest::getString('awd_pm_' . $cid);

		$this->assignRef('wid', $wid);

		$this->assignRef('msg', $msg);

		$this->assignRef('postedTime', time());

		parent::display($tpl);

	}

	

	public function getOlderPosts($tpl = null)

	{
		$db 	= &JFactory::getDBO();
		$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';
		//echo $wallalbumfile;
		$jomalbumexist='';
		if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
		{
			$jomalbumexist=1;
		}
		$this->assignRef('jomalbumexist', $jomalbumexist);

		$hwdvideosharefile = JPATH_SITE . '/components/com_hwdvideoshare/hwdvideoshare.php';
		$hwdvideoshare='';
		if (file_exists($hwdvideosharefile)) // if com_awdjomalbum install then only
		{
			$hwdvideoshare=1;
		}
		$this->assignRef('hwdvideoshare', $hwdvideoshare);
		
		$task 			= JRequest::getCmd('type', '');

		$page 			= JRequest::getCmd('awd_page', 0);

		$layout 		= JRequest::getCmd('layout', 'main');

		// get configuration from database
	
		//$config = &JComponentHelper::getParams('com_awdwall');
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');

		$postLimit 		= $config->get('nof_post', 15);
		$commentLimit 	= $config->get('nof_comment', 3);		
		$displayName 	= $config->get('display_name', 1);
		$videoLightbox  = $config->get('video_lightbox', 1);
		$width		  = $config->get('width', 725);
		$imageLightbox  = $config->get('image_lightbox', 1);
		$privacy 		= $config->get('privacy', 0);
		$displayCommentLike=$config->get('displayCommentLike', 1);

		$arrTask = array();

		$arrTask[] = 'videos';

		$arrTask[] = 'images';

		$arrTask[] = 'trails';

		$arrTask[] = 'music';

		$arrTask[] = 'links';

		$arrTask[] = 'files';

		$arrTask[] = 'events';

		$arrTask[] = 'jing';

		$arrTask[] = 'article';

		$user = &JFactory::getUser();

		// get userid from url

		$wuid = JRequest::getInt('wuid', 0);

		$groupId = JRequest::getInt('groupid', 0);

		// get all msg from databases

		$wallModel = & $this->getModel('wall');

		// build where clause	

		$where = array();

		if($task == 'videos')

			$where[] = 'type = "video"';

		elseif($task == 'images')

			$where[] = 'type = "image"';

		elseif($task == 'trails')

			$where[] = 'type = "trail"';

		elseif($task == 'pm')

			$where[] = 'is_pm = 1';


		elseif($task == 'music')

			$where[] = 'type = "mp3"';

		elseif($task == 'links')

			$where[] = 'type = "link"';	

		elseif($task == 'files')

			$where[] = 'type = "file"';

		if($task == 'friends')

			$where[] = 'aw.reply = 0';
			
		elseif($task == 'jing')

			$where[] = 'type = "jing"';
		elseif($task == 'events')
		
			$where[] = 'type = "event"';

		elseif($task == 'article')
			$where[] = 'type = "article"';	
			
		else

			$where[] = 'reply = 0';

		if(!$groupId){

			if($wuid){			

				$where[] = '(user_id = ' . (int)$wuid .' OR commenter_id = ' . (int)$wuid . ' )';	
				
				if($wuid!=$user->id)
					$where[] = '(IF(is_pm=1, commenter_id = ' . $user->id . ' , 1))';
				
			}else{

				$where[] = 'is_pm = 0';			

			}

		}else

			$where[] = 'group_id = ' . $groupId;

		
		$where[] = 'wall_date IS NOT NULL';

		

		if((int)$privacy){			

			if($layout == 'mywall')

				$where[] = '((commenter_id IN (select referenceid FROM #__comprofiler_members where memberid 	 = ' . $wuid . ' AND accepted = 1 AND pending = 0)) OR (user_id = ' . $wuid . ' OR commenter_id = ' . $wuid . '))';

			else

				$where[] = '((commenter_id IN (select referenceid FROM #__comprofiler_members where memberid 	 = ' . $user->id . ' AND accepted = 1 AND pending = 0)) OR (user_id = ' . $user->id . ' OR commenter_id = ' . $user->id . ') OR (group_id IS NOT NULL))';

		}
		if($layout == 'mywall')
		{
			$where[] = 'group_id IS NULL';
		}
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';

		

		$offset = $page*$postLimit;

		$msgs = $wallModel->getAllMsg($postLimit, $where, $offset, $privacy);

		$nofMsgs = $wallModel->countMsg();

		// Load the form validation behavior

		JHTML::_('behavior.formvalidation');

		

		// set receiver_id = user->id if wuid = 0

		if($wuid == 0)

			$wuid = $user->id;

		// check group		

	

			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');

			$groupModel = new AwdwallModelGroup();		

			$this->assignRef('groupModel', $groupModel);			

	
	
		//usertype of login user...	
		$user = &JFactory::getUser();
		$query="SELECT group_id FROM #__user_usergroup_map where user_id=".$user->id;
		$db->setQuery($query);
		$user_groupidList= $db->loadObjectList();
				
		$admin_groupid=array(7,8);

		$can_delete='';
		foreach ($user_groupidList as $ugroupid)
		{
			if(in_array($ugroupid->group_id,$admin_groupid)){
			$can_delete=1;
			}
		}
		if($owner)
		$can_delete=1;
		
		$this->assignRef('can_delete', $can_delete);
		$this->assignRef('displayName', $displayName);

		$this->assignRef('wuid', $wuid);

		$this->assignRef('groupId', $groupId);

		$this->assignRef('msgs', $msgs);

		$this->assignRef('wallModel', $wallModel);

		$this->assignRef('videoLightbox', $videoLightbox);

		$this->assignRef('imageLightbox', $imageLightbox);

		$this->assignRef('task', $task);

		$this->assignRef('nofMsgs', $nofMsgs);

		$this->assignRef('page', $page);

		$this->assignRef('postLimit', $postLimit);

		$this->assignRef('commentLimit', $commentLimit);

		$this->assignRef('layout', $layout);

		$this->assignRef('privacy', $privacy);

		$this->assignRef('arrTask', $arrTask);
		$this->assignRef('displayCommentLike', $displayCommentLike);
		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		parent::display($tpl);

	}

	

	public function getOlderComments($tpl = null)

	{

		$task 			= JRequest::getCmd('type', '');
		$db 	= &JFactory::getDBO();
		$cpage 			= JRequest::getCmd('awd_c_page', 0);

		// get configuration from database

		//$config = &JComponentHelper::getParams('com_awdwall');
		$app = JFactory::getApplication('site');
		 
		$config =  & $app->getParams('com_awdwall');
		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
	
		$commentLimit 	= $config->get('nof_comment', 3);		

		$displayName 	= $config->get('display_name', 1);		

		$user = &JFactory::getUser();

		// get userid from url

		$wuid = JRequest::getInt('wuid', 0);

		$wid = JRequest::getInt('wid', 0);

		$cid = JRequest::getInt('cid', 0);

		// get all msg from databases

		$wallModel = & $this->getModel('wall');
		$this->assignRef('wallModel', $wallModel);
		// build where clause	

		$where = array();

		

		$where[] = 'reply = ' .$wid;

		$where[] = 'is_pm = 0';

		

		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';

		

		if($cpage == 0 || $cpage == 1){

			// get comments of message	

			$coffset 	= $cpage* $commentLimit;

			$nofComments = $wallModel->countComment($wid);

			if($cpage == 1){

				$commentLimit = (int)$nofComments - (int)$commentLimit;

			}

			$comments 	= $wallModel->getAllCommentOfMsg($commentLimit, $wid, $coffset);

			$this->assignRef('comments', $comments);
		
			//usertype of login user...	
			$user = &JFactory::getUser();
			$query="SELECT group_id FROM #__user_usergroup_map where user_id=".$user->id;
			$db->setQuery($query);
			$user_groupidList= $db->loadObjectList();
			
			$admin_groupid=array(7,8);
	
			$can_delete='';
			foreach ($user_groupidList as $ugroupid)
			{
				if(in_array($ugroupid->group_id,$admin_groupid)){
				$can_delete=1;
				}
			}
			if($owner)
			$can_delete=1;
			
			$this->assignRef('can_delete', $can_delete);
			parent::display($tpl);

		}

	}

	

	public function getLatestMsgBlock()

	{		

		
		$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';
		//echo $wallalbumfile;
		$jomalbumexist='';
		if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
		{
			$jomalbumexist=1;
		}
		$this->assignRef('jomalbumexist', $jomalbumexist);

		$hwdvideosharefile = JPATH_SITE . '/components/com_hwdvideoshare/hwdvideoshare.php';
		$hwdvideoshare='';
		if (file_exists($hwdvideosharefile)) // if com_awdjomalbum install then only
		{
			$hwdvideoshare=1;
		}
		$this->assignRef('hwdvideoshare', $hwdvideoshare);
		
		$layout = JRequest::getCmd('layout', '');

		$start 	= JRequest::getInt('start', 0);

		$end 	= JRequest::getInt('end', 0);

		$postedWid 	= JRequest::getString('posted_wid', '');

		$task 	= JRequest::getString('type', '');

		// get configuration from database

		//$config = &JComponentHelper::getParams('com_awdwall');
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');

		$postLimit 		= $config->get('nof_post', 15);
		$commentLimit 	= $config->get('nof_comment', 3);
		$displayName 	= $config->get('display_name', 1);
		$videoLightbox  = $config->get('video_lightbox', 1);
		$width  		= $config->get('width', 725);
		$imageLightbox  = $config->get('image_lightbox', 1);
		$privacy 		= $config->get('privacy', 0);

		$user = &JFactory::getUser();

		// get userid from url

		$wuid = JRequest::getInt('wuid', 0);

		$groupId = JRequest::getInt('groupid', 0);

		// set receiver_id = user->id if wuid = 0

		if($layout == 'mywall'){

			if($wuid == 0)

				$wuid = $user->id;

		}
		if($layout == 'mywall')
		{
			$where[] = 'group_id IS NULL';
		}
		// get all msg from databases

		$wallModel = & $this->getModel('wall');

		// build where clause	

		$where = array();

		if($task == 'videos')

			$where[] = 'type = "video"';

		elseif($task == 'images')

			$where[] = 'type = "image"';

		elseif($task == 'trails')

			$where[] = 'type = "trail"';

		elseif($task == 'pm')

			$where[] = 'is_pm = 1';

		elseif($task == 'music')

			$where[] = 'type = "mp3"';

		elseif($task == 'links')

			$where[] = 'type = "link"';	

		elseif($task == 'files')

			$where[] = 'type = "file"';
			
		elseif($task == 'events')
		
			$where[] = 'type = "event"';	
			
		elseif($task == 'jing')
		
			$where[] = 'type = "jing"';	
			
		elseif($task == 'article')
		
			$where[] = 'type = "article"';	

		if($task == 'friends')

			$where[] = 'aw.reply = 0';

		$where[] = 'reply = 0';

		if($start){

			$where[] = 'wall_date >= ' . $start;

		}

		if($end){

			$where[] = 'wall_date <= ' . $end;

		}

		if($postedWid != ''){

			$where[] = 'id NOT IN(' . $postedWid . ')';

		}

		if($wuid && !$groupId){			

			$where[] = '(user_id = ' . (int)$wuid .' OR commenter_id = ' . (int)$wuid . ' )';	

		}else{

			$where[] = 'is_pm = 0';			

		}

		if($groupId)			

			$where[] = 'group_id = ' . $groupId;
		$where[] = 'commenter_id != ' .$user->id;	

		$db =& JFactory::getDBO();
	
		//usertype of login user...	
		$user = &JFactory::getUser();
		$query="SELECT group_id FROM #__user_usergroup_map where user_id=".$user->id;
		$db->setQuery($query);
		$user_groupidList= $db->loadObjectList();
				
		$admin_groupid=array(7,8);

		$can_delete='';
		foreach ($user_groupidList as $ugroupid)
		{
			if(in_array($ugroupid->group_id,$admin_groupid)){
			$can_delete=1;
			}
		}
		if($owner)
		$can_delete=1;
		
		$this->assignRef('can_delete', $can_delete);
		//get friend list
		
		$sql= 'select connect_from FROM #__awd_connection where connect_to = ' . $user->id .  ' AND status = 1 AND pending=0';
		$db->setQuery($sql);
		$friendlist=$db->loadObjectList();
		for($i=0;$i<count($friendlist);$i++)
		{
			$friend=$friendlist[$i];
			//$list1[$i]=$friend->connect_to;
			$list1[$i]=$friend->connect_from;
		}
		if($list1)
		$friends=implode(",",$list1);
		
		//friend of friend list of user
				
		if($friends)
		$sql= 'select connect_from FROM #__awd_connection where connect_to in (' . $friends . ') AND status = 1 AND pending=0';
		$db->setQuery($sql);
		$friendoffriendlist=$db->loadObjectList();
		for($i=0;$i<count($friendoffriendlist);$i++)
		{
			$friendoffriend=$friendoffriendlist[$i];
			$list2[$i]=$friendoffriend->connect_to;
			$list2[$i]=$friendoffriend->connect_from;
		}
		if($list2)
		$friendoffriends=implode(",",$list2);
		
		
		if($friends != '')
		{
			$friendsuser = $friends.','.$user->id; 
		}
		else
		$friendsuser = $user->id;
		
		//Wall posts by friends
		
		$sql='select id from #__awd_wall where user_id IN  (' . $friendsuser . ') OR commenter_id IN (' . $friendsuser . ')';
		$db->setQuery($sql);
		$friendsPost=$db->loadObjectList();
		for($i=0;$i<count($friendsPost);$i++)
		{
			$friendPost=$friendsPost[$i];
			$fPostslist[$i]=$friendPost->id;
		}
		if($fPostslist)
		$fPosts=implode(",",$fPostslist);
		//Wall posts by friends of friends
	//	$sql='select id from #__awd_wall where user_id IN  (' . $friendoffriendsuser . ') OR commenter_id IN (' . $friendoffriendsuser . ')';
		$commenterlist=$friendsuser;
		if($friendoffriends != '')
		{
		$commenterlist=$commenterlist.','.$friendoffriends;
		$sql='select a.id as wallid from #__awd_wall as a left join #__awd_wall_privacy as b on a.id=b.wall_id where a.commenter_id IN (' . $commenterlist . ') AND b.privacy="2"' ;
		}
		$db->setQuery($sql);
		$fofriendsPost=$db->loadObjectList();
		for($i=0;$i<count($fofriendsPost);$i++)
		{
			$foffriendPost=$fofriendsPost[$i];
			$fofPostslist[$i]=$foffriendPost->wallid;
		}
		if($fofPostslist)
		$fofPosts=implode(",",$fofPostslist);
			
		$sql='select wall_id from #__awd_wall_privacy where privacy="0"';
		
		$db->setQuery($sql);
		$privacyPOsts=$db->loadObjectList();
		for($i=0;$i<count($privacyPOsts);$i++)
		{
			$privacyPOst=$privacyPOsts[$i];
			$privacyPOstlist[$i]=$privacyPOst->wall_id;
		}
		if($privacyPOstlist)
		$privacyPOst=implode(",",$privacyPOstlist);
		
		$totalpost='';
		if($privacyPOst!='' && $totalpost!='')	
		$totalpost=	$totalpost.','.$privacyPOst;
		if($privacyPOst!='' && $totalpost=='')	
		$totalpost=	$privacyPOst;
		if($fPosts!='' && $totalpost!='')	
		$totalpost=	$totalpost.','.$fPosts;
		if($fPosts!='' && $totalpost=='')
		$totalpost=	$fPosts;
		if($fofPosts!=''  && $totalpost!='')	
		$totalpost=	$totalpost.','.$fofPosts;
		if($fofPosts!=''  && $totalpost=='')	
		$totalpost=	$fofPosts;
		
		$temparray=explode(',',$totalpost);
		
		for($i=0;$i<count($temparray);$i++)
		{	if(!empty($temparray[$i]))
			$temparray1[]=$temparray[$i];
		}
		if($temparray1)
		{
			$totalpost=implode(',',$temparray1);
			$where[] = ' id IN('.$totalpost.')';
		}
		

		/*if((int)$privacy){			

			if($layout == 'mywall')

				$where[] = '((commenter_id IN (select referenceid FROM #__comprofiler_members where memberid 	 = ' . $wuid . ' AND accepted = 1 AND pending = 0)) OR (user_id = ' . $wuid . ' OR commenter_id = ' . $wuid . '))';

			else

				$where[] = '((commenter_id IN (select referenceid FROM #__comprofiler_members where memberid 	 = ' . $user->id . ' AND accepted = 1 AND pending = 0)) OR (user_id = ' . $user->id . ' OR commenter_id = ' . $user->id . ') OR (group_id IS NOT NULL))';

		}*/
		$where[] = '(IF(is_pm=1, user_id = ' . $user->id . ', 1))';
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
	//	$where=$where.' or '.' (IF(is_pm=1, user_id = ' . $user->id . ', 1)) ';
				

		$msgs = $wallModel->getAllMsg($postLimit, $where, 0, $privacy);	

		// check group			

		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');

		$groupModel = new AwdwallModelGroup();		

		$this->assignRef('groupModel', $groupModel);

		$this->assignRef('displayName', $displayName);

		$this->assignRef('wuid', $wuid);

		$this->assignRef('msgs', $msgs);

		$this->assignRef('wallModel', $wallModel);

		$this->assignRef('videoLightbox', $videoLightbox);

		$this->assignRef('imageLightbox', $imageLightbox);

		$this->assignRef('task', $task);

		$this->assignRef('nofMsgs', $nofMsgs);

		$this->assignRef('page', $page);

		$this->assignRef('postLimit', $postLimit);

		$this->assignRef('commentLimit', $commentLimit);

		$this->assignRef('layout', $layout);

		$app = JFactory::getApplication('site');
		 
		$config =  & $app->getParams('com_awdwall');
		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		parent::display($tpl);

	}

	

	public function getPMUserBlock($tpl = null)

	{		
		 
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayPm = $config->get('display_pm', 1);
		$displayShare = $config->get('display_share', 1);
		$displayLike = $config->get('display_like', 1);
		
		$wid 		= JRequest::getInt('wid', 0);

		$receiverId = JRequest::getInt('awd_pm_receiver_id', 0);

		$msg 		= JRequest::getString('awd_pm_description');

		$wallModel = & $this->getModel('wall');

		$receiver 	=  &JFactory::getUser($receiverId);

		

		$this->assignRef('wid', $wid);

		$this->assignRef('msg', $msg);

		$this->assignRef('receiverId', $receiverId);

		$this->assignRef('receiver', $receiver);

		$this->assignRef('postedTime', time());

		$this->assignRef('wallModel', $wallModel);
		$this->assignRef('displayPm', $displayPm);
		$this->assignRef('displayShare', displayShare);
		$this->assignRef('displayLike', $displayLike);
		parent::display($tpl);

	}

	

	public function avatar($tpl = null)

	{

		$user = &JFactory::getUser();

		$db =& JFactory::getDBO();

		// get user_id from wall_users

		$query = 'SELECT * FROM #__awd_wall_users WHERE user_id = ' . (int)$user->id;

		$db->setQuery($query);

		$row = $db->loadObject();

		$this->assignRef('user', $row);

		

		//color

		$db		=& JFactory::getDBO();

//		$query = "SELECT params FROM #__components WHERE `name`='Colors' AND `admin_menu_link`='option=com_awdwall&controller=colors'";
		$link='index.php?option=com_awdwall&controller=colors';
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$params = json_decode( $db->loadResult(), true );
		for($i=1; $i<=14; $i++)
		{
			$str_color = 'color'.$i;			
			$color[$i]= $params[$str_color];
		}

		

		$this->assignRef('color', $color);

		

		// build gender select box

		$arr[] = JHTML::_('select.option', '0', '- '. JText::_('Select Gender') .' -');

		$arr[] = JHTML::_('select.option', '1', JText::_('Male'));

		$arr[] = JHTML::_('select.option', '2', JText::_('Female'));

		$default = 0;

		if(isset($row->gender))

			$default = $row->gender;

		$listGender = JHTML::_('select.genericlist', $arr, 'gender', '', 'value', 'text', $default, 'gender');

		//$config = &JComponentHelper::getParams('com_awdwall');
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');

		$displayName 	= $config->get('display_name', 1);

		$this->assignRef('displayName', $displayName);

		$this->assignRef('listGender', $listGender);

		parent::display($tpl);

	}

	

	public function viewFriends($tpl = null)

	{

		$mainframe	=& JFactory::getApplication();

		$itemId = AwdwallHelperUser::getComItemId();

		$task 			= JRequest::getCmd('task', '');

		$page 			= JRequest::getCmd('awd_page', 0);



		// get configuration from database

		//$config = &JComponentHelper::getParams('com_awdwall');
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayName 	= $config->get('display_name', 1);
		$postLimit 		= $config->get('nof_post', 15);
		$fields			= $config->get('fieldids', '');
		$privacy 		= $config->get('privacy', 0); 

		$displayPm = $config->get('display_pm', 1);
		
		$display_group = $config->get('display_group', 1);
		
		$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
		
		$moderator_users = $config->get('moderator_users', '');
		
		$moderator_users=explode(',',$moderator_users);

		$user = &JFactory::getUser();		

		// get all msg from databases

		$wallModel = & $this->getModel('wall');
		$this->assignRef('wallModel', $wallModel);
		// build where clause	

		$where = array();

		$where[] = 'connect_from  = ' . (int)$user->id;

		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';

		$offset = $page*$postLimit;

		

		//color

		$db		=& JFactory::getDBO();

//		$query = "SELECT params FROM #__components WHERE `name`='Colors' AND `admin_menu_link`='option=com_awdwall&controller=colors'";
		$link='index.php?option=com_awdwall&controller=colors';
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$params = json_decode( $db->loadResult(), true );
		for($i=1; $i<=14; $i++)
		{
			$str_color = 'color'.$i;			
			$color[$i]= $params[$str_color];
		}

		

		$this->assignRef('color', $color);

		

		//$friends = $wallModel->getAllFriends($postLimit, $where, $offset);
		$query 	= 'SELECT * FROM #__awd_connection  inner join #__users ON #__awd_connection.connect_to=#__users.id '				
				.$where . ' '
				.'ORDER BY connection_id DESC ';
				// echo $query ;
		$db->setQuery($query);
		$friends =$db->loadObjectList();
		$nofFriends = $wallModel->countFriends($where);

		require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'jslib.php');

		$basicInfo 		= JsLib::getUserBasicInfo($user->id, $fields);

		$totalFriends 	= JsLib::countFriends($user->id);

		$friendLimit 	= (int)$config->get('nof_friends', 4);

		if($friendLimit > 6)

			$friendLimit = 6;

		$leftFriends 	= JsLib::getAllFriends($user->id, $friendLimit);

		
		// get 4 first groups to display

		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');

		$whr = array();

		$whr[] = 'ag.creator = ' . $user->id . ' OR agm.user_id =' . $user->id;
		$whr[] =' status="1"';
		$whr = count($whr) ? ' WHERE ' . implode( ' AND ', $whr ) : '';

		$groups = AwdwallModelGroup::getAllGrps($whr, 4, 0);


		//Total pending friend requests
		$pendingFriends = JsLib::getPendingFriends($user->id);
		
		//Userinfo of login user
		$userinfo = AwdwallHelperUser::getUserInfo($user->id);		
		$this->assignRef('userinfo', $userinfo);  
		
		//Total private message of user
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$modelWall = new AwdwallModelWall();	
		//print_r($modelWall);
		$totalpm = $modelWall->countpm($user->id);
		
		$pendingGroups = JsLib::getPendingGroups($user->id);

		$this->assignRef('pendingGroups', $pendingGroups);
		$this->assignRef('totalpm', $totalpm);
		$this->assignRef('totalmygroup', $totalmygroup);
		$this->assignRef('groups', $groups);

		$this->assignRef('totalFriends', $totalFriends);

		$this->assignRef('leftFriends', $leftFriends);

		$this->assignRef('basicInfo', $basicInfo);

		$this->assignRef('nofFriends', $nofFriends);

		$this->assignRef('friends', $friends);

		$this->assignRef('page', $page);

		$this->assignRef('postLimit', $postLimit);		

		$this->assignRef('privacy', $privacy);
		$this->assignRef('displayName', $displayName);
		$this->assignRef('displayPm', $displayPm);
		$this->assignRef('display_group', $display_group);
		$this->assignRef('display_group_for_moderators', $display_group_for_moderators);
		$this->assignRef('moderator_users', $moderator_users);
		$this->assignRef('pendingFriends', $pendingFriends);

		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		
		
		$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';
		if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
		{
			$query="Select * from #__awd_jomalbum_userinfo where userid=".$user->id; 
			$db->setQuery($query);
			$rows=$db->loadObjectList();
			$this->assignRef('albumuserinfo', $rows[0]);
		}
		
		parent::display($tpl);

	}

	

	function getMoreFriends($tpl = null)

	{		

		$page 			= JRequest::getCmd('awd_page', 0);		

		// get configuration from database

		//$config = &JComponentHelper::getParams('com_awdwall');
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayName 	= $config->get('display_name', 1);
		$this->assignRef('displayName', $displayName);
		$postLimit 		= $config->get('nof_post', 15);		

		$privacy 		= $config->get('privacy', 0);

		$user = &JFactory::getUser();		

		// get all msg from databases

		$wallModel = & $this->getModel('wall');

		// build where clause	

		$where = array();

		$where[] = 'connect_from  = ' . (int)$user->id;

		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';

		$offset = $page*$postLimit;

		

		$friends = $wallModel->getAllFriends($postLimit, $where, $offset);

		$nofFriends = $wallModel->countFriends($where);

						

		$this->assignRef('nofFriends', $nofFriends);

		$this->assignRef('friends', $friends);

		$this->assignRef('page', $page);

		$this->assignRef('postLimit', $postLimit);		

		$this->assignRef('privacy', $privacy);

		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		parent::display($tpl);

	}

	

	function newGroup($tpl = null)

	{		

		//color
		$mainframe = JFactory::getApplication('site');
		$Itemid = AwdwallHelperUser::getComItemId();
		$user 	= &JFactory::getUser();
		$db		=& JFactory::getDBO();

//		$query = "SELECT params FROM #__components WHERE `name`='Colors' AND `admin_menu_link`='option=com_awdwall&controller=colors'";
		$link='index.php?option=com_awdwall&controller=colors';
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$params = json_decode( $db->loadResult(), true );
		for($i=1; $i<=14; $i++)
		{
			$str_color = 'color'.$i;			
			$color[$i]= $params[$str_color];
		}

		

		$this->assignRef('color', $color);
		
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$display_group = $config->get('display_group', 1);
		$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
		$moderator_users = $config->get('moderator_users', '');
		
		
		
		$query = 'SELECT count(*) as groupcount FROM #__awd_groups ';
		$db->setQuery($query);
		$groupcount = $db->loadResult();
		
		$group_creation = $config->get('group_creation', 1);
		$maxgroupno = $config->get('maxgroupno', -1);
		$groupcreator = $config->get('groupcreator', '');
		
		if($group_creation==0)
		{
			$flag=1;
			$msg=JText::_('COM_COMAWDWALL_GROUP_CREATION_DENY_TEXT1');
			$mainframe->redirect(JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid=' . $Itemid, false), $msg);
		}
		if($maxgroupno!=-1)
		{
			if($groupcount>=$maxgroupno)
			{
				$flag=1;
				$msg=JText::_('COM_COMAWDWALL_GROUP_CREATION_DENY_TEXT2');
				$mainframe->redirect(JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid=' . $Itemid, false), $msg);
			}
		
		}
		
		if(!empty($groupcreator))
		{
			$agroups = JAccess::getGroupsByUser($user->id, false);
			$commonElements = array_intersect($agroups,$groupcreator);
			$comcount=count($commonElements);
			if($comcount==0)
			{
				$flag=1;
				$msg=JText::_('COM_COMAWDWALL_GROUP_CREATION_DENY_TEXT3');
				$mainframe->redirect(JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid=' . $Itemid, false), $msg);
			}
			
		}
		
		$moderator_users=explode(',',$moderator_users);
		$displayName 	= $config->get('display_name', 1);
		$this->assignRef('displayName', $displayName);
		$this->assignRef('display_group', $display_group);
		$this->assignRef('display_group_for_moderators', $display_group_for_moderators);
		$this->assignRef('moderator_users', $moderator_users);
		
		parent::display($tpl);

	}

	

	function groups($tpl = null)

	{		

		//color

		$db		=& JFactory::getDBO();

//		$query = "SELECT params FROM #__components WHERE `name`='Colors' AND `admin_menu_link`='option=com_awdwall&controller=colors'";
		$link='index.php?option=com_awdwall&controller=colors';
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$params = json_decode( $db->loadResult(), true );
		for($i=1; $i<=14; $i++)
		{
			$str_color = 'color'.$i;			
			$color[$i]= $params[$str_color];
		}

		

		$user = &JFactory::getUser();

		$groupModel = & $this->getModel('group');

		$myGrps = $groupModel->getMyGrps($user->id);

		$this->assignRef('myGrps', $myGrps);

		$allGrps = $groupModel->getAllGrps('', 100, 0);

		$extraGroups = $groupModel->getGroupsFromMem($user->id);

		$this->assignRef('extraGroups', $extraGroups);

		$this->assignRef('allGrps', $allGrps);

		$this->assignRef('color', $color);

		$this->assignRef('groupModel', $groupModel);

		// get pending approval

		$pendings = $groupModel->getPendingApproval($user->id);

		$this->assignRef('pendings', $pendings);

		//$config = &JComponentHelper::getParams('com_awdwall');
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');

		$displayName 	= $config->get('display_name', 1);

		$this->assignRef('displayName', $displayName);
		
		$display_group = $config->get('display_group', 1);
		$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
		$moderator_users = $config->get('moderator_users', '');
		$moderator_users=explode(',',$moderator_users);
		
		$this->assignRef('display_group', $display_group);
		$this->assignRef('display_group_for_moderators', $display_group_for_moderators);
		$this->assignRef('moderator_users', $moderator_users);
		
		parent::display($tpl);

	}

	

	function viewGroup($tpl = null)

	{

		$mainframe	=& JFactory::getApplication();
		$db =& JFactory::getDBO();
		$user = &JFactory::getUser();

		$groupModel = & $this->getModel('group');

		$itemId = AwdwallHelperUser::getComItemId();						

		$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';
		//echo $wallalbumfile;
		$jomalbumexist='';
		if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
		{
			$jomalbumexist=1;
		}
		$this->assignRef('jomalbumexist', $jomalbumexist);

		$hwdvideosharefile = JPATH_SITE . '/components/com_hwdvideoshare/hwdvideoshare.php';
		$hwdvideoshare='';
		if (file_exists($hwdvideosharefile)) // if com_awdjomalbum install then only
		{
			$hwdvideoshare=1;
		}
		$this->assignRef('hwdvideoshare', $hwdvideoshare);

		// get configuration from database
		
		$app = JFactory::getApplication('site');
 
		$config =  & $app->getParams('com_awdwall');

		// check if logged users are owner of this group

		$groupId = JRequest::getInt('groupid', 0);

		$owner = $groupModel->checkGrpOwner($user->id, $groupId);	

		$this->assignRef('owner', $owner);

		// get group info

		$grpInfo = $groupModel->getGroupInfo($groupId);

		$this->assignRef('grpInfo', $grpInfo);		

		// check if private group 

		$isPrivate = $groupModel->isPriaveGrp($groupId);

		if($isPrivate){

			// check if this user that viewing is member of this group

			$this->assignRef('isPrivate', $isPrivate);

		}else{

			

		}

		

		// check if this user is member of this group

		$isMemberGrp = $groupModel->isMemberGrp($groupId, $user->id);

		$this->assignRef('isMemberGrp', $isMemberGrp);

		// check if owner of this private group or not

		if($isPrivate){

			if(!$owner && !$isMemberGrp){

				$mainframe->redirect(JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid=' . $itemId, false), JText::_('YOU NEED TO MEMBER TO VIEW PRG'));

			}

		}

		// get total members of this groups

		$groupsLimit = (int)$config->get('nof_groups', 4);

		$members = $groupModel->getAllMemberByGrp($groupId, $groupsLimit);

		$nofMembers = count($members);

		$this->assignRef('members', $members);

		$this->assignRef('nofMembers', $nofMembers);

		$this->assignRef('groupModel', $groupModel);
		$query = 'SELECT COUNT(*) FROM #__users AS u '
				.'INNER JOIN #__awd_groups_members AS gm ON gm.user_id = u.id '
				.'WHERE u.block=0 AND u.id NOT IN (SELECT creator FROM #__awd_groups WHERE id = ' . (int)$groupId . ') AND gm.group_id = ' . (int)$groupId
				;
		$db->setQuery($query);
		$totalUsers = $db->loadResult();	
		$this->assignRef('nofMembers', $totalUsers);	
		// get posts

		$mainframe	=& JFactory::getApplication();

		$itemId = AwdwallHelperUser::getComItemId();

		$task 			= JRequest::getCmd('type', '');

		$page 			= JRequest::getCmd('awd_page', 0);

		$wid 			= JRequest::getInt('wid', 0);

		

		//color

		$db		=& JFactory::getDBO();

//		$query = "SELECT params FROM #__components WHERE `name`='Colors' AND `admin_menu_link`='option=com_awdwall&controller=colors'";
		$link='index.php?option=com_awdwall&controller=colors';
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$params = json_decode( $db->loadResult(), true );
		for($i=1; $i<=14; $i++)
		{
			$str_color = 'color'.$i;			
			$color[$i]= $params[$str_color];
		}
		


		
		//usertype of login user...	
		$user = &JFactory::getUser();
		$query="SELECT group_id FROM #__user_usergroup_map where user_id=".$user->id;
		$db->setQuery($query);
		$user_groupidList= $db->loadObjectList();
		
		//usertype access
		$groupList=array(5,6,7,8);
		$user_groupid='';
		foreach ($user_groupidList as $ugroupid)
		{
			if(in_array($ugroupid->group_id,$groupList)){
			$user_groupid='Can add article';
			}
		}
		
		$admin_groupid=array(7,8);

		$can_delete='';
		foreach ($user_groupidList as $ugroupid)
		{
			if(in_array($ugroupid->group_id,$admin_groupid)){
			$can_delete=1;
			}
		}
		if($owner)
		$can_delete=1;
		
		$this->assignRef('can_delete', $can_delete);

		//status group (dangcv)

		$wallModel = & $this->getModel('wall');

		$groupid = $_REQUEST['groupid'];

		$latestPost = $wallModel->showstatus($groupid);

		$this->assignRef('latestPost', $latestPost);

		

		//get member creator group (dangcv)

		$memberscreator = $groupModel->getMembercreatorByGrp($groupId);

		$this->assignRef('memberscreator', $memberscreator);

		

		

		//get member creator group

		$memberscreator = $groupModel->getMembercreatorByGrp($groupId);

		$this->assignRef('memberscreator', $memberscreator);

		

		$postLimit 		= $config->get('nof_post', 15);

		$commentLimit 	= $config->get('nof_comment', 3);

		$displayName 	= $config->get('display_name', 1);

		$videoLightbox  = $config->get('video_lightbox', 1);

		$imageLightbox  = $config->get('image_lightbox', 1);

		$privacy 		= $config->get('privacy', 0);		

		$displayVideo 	= $config->get('display_video', 1);

		$displayImage 	= $config->get('display_image', 1);

		$displayMusic 	= $config->get('display_music', 1);

		$displayLink 	= $config->get('display_link', 1);

		$displayFile 	= $config->get('display_file', 1);

		$displayTrail 	= $config->get('display_trail', 1);

		$displayJing 	= $config->get('display_jing', 1);
		
		$displayEvent 	= $config->get('display_event', 1);
		
		$displayArticle = $config->get('display_article', 1);

		$display_filterwall = $config->get('display_filterwall', 1);
		$display_filtervideo = $config->get('display_filtervideo', 1);
		$display_filterimage = $config->get('display_filterimage', 1);
		$display_filtermusic = $config->get('display_filtermusic', 1);
		$display_filterlink = $config->get('display_filterlink', 1);
		$display_filterfile = $config->get('display_filterfile', 1);
		$display_filterjing = $config->get('display_filterjing', 1);
		$display_filtertrail = $config->get('display_filtertrail', 1);
		$display_filterevent = $config->get('display_filterevent', 1);
		$display_filterarticle = $config->get('display_filterarticle', 1);
		$display_filterpm = $config->get('display_filterpm', 1);
		$displayPm = $config->get('display_pm', 1);
		$displayShare = $config->get('display_share', 1);
		$displayLike = $config->get('display_like', 1);
		$moderator_users = $config->get('moderator_users', '');
		$moderator_users=explode(',',$moderator_users);
		$display_group = $config->get('display_group', 1);
		$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		// get all msg from databases

		$wallModel = & $this->getModel('wall');

		// build where clause	

		$where = array();

		if($task == 'videos')

			$where[] = 'type = "video"';

		elseif($task == 'images')

			$where[] = 'type = "image"';

		elseif($task == 'trails')

			$where[] = 'type = "trail"';

		elseif($task == 'pm')

			$where[] = 'is_pm = 1';

		elseif($task == 'music')

			$where[] = 'type = "mp3"';

		elseif($task == 'links')

			$where[] = 'type = "link"';	

		elseif($task == 'files')

			$where[] = 'type = "file"';

		elseif($task == 'jing')

			$where[] = 'type = "jing"';
			
		elseif($task == 'events')
		
			$where[] = 'type = "event"';
			
		elseif($task == 'article')
		
			$where[] = 'type = "article"';
			
		if($task == 'friends')

			$where[] = 'aw.reply = 0';

		else

			$where[] = 'reply = 0';

		$where[] = 'group_id = ' . $groupId;

		$where[] = 'wall_date IS NOT NULL';

		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';

		

		$offset = $page*$postLimit;

		$msgs 	 = $wallModel->getAllGrpMsg($postLimit, $where, $offset);

		$nofMsgs = $wallModel->countGrpMsg($where);

		

		$this->assignRef('color', $color);

		$this->assignRef('msgs', $msgs);

		$this->assignRef('wallModel', $wallModel);

		$this->assignRef('videoLightbox', $videoLightbox);

		$this->assignRef('imageLightbox', $imageLightbox);

		$this->assignRef('task', $task);

		$this->assignRef('nofMsgs', $nofMsgs);

		$this->assignRef('page', $page);		

		$this->assignRef('postLimit', $postLimit);

		$this->assignRef('commentLimit', $commentLimit);		

		$this->assignRef('displayVideo', $displayVideo);

		$this->assignRef('displayImage', $displayImage);

		$this->assignRef('displayMusic', $displayMusic);

		$this->assignRef('displayLink', $displayLink);

		$this->assignRef('displayFile', $displayFile);

		$this->assignRef('displayTrail', $displayTrail);

		$this->assignRef('displayJing', $displayJing);
		
		$this->assignRef('displayEvent', $displayEvent);

		$this->assignRef('displayName', $displayName);

		$this->assignRef('displayArticle', $displayArticle);
		$this->assignRef('groupList', $groupList);
		$this->assignRef('user_groupid', $user_groupid);

		$this->assignRef('display_filterwall', $display_filterwall);
		$this->assignRef('display_filtervideo', $display_filtervideo);
		$this->assignRef('display_filterimage', $display_filterimage);
		$this->assignRef('display_filtermusic', $display_filtermusic);
		$this->assignRef('display_filterlink', $display_filterlink);
		$this->assignRef('display_filterfile', $display_filterfile);
		$this->assignRef('display_filterjing', $display_filterjing);
		$this->assignRef('display_filtertrail', $display_filtertrail);
		$this->assignRef('display_filterevent', $display_filterevent);
		$this->assignRef('display_filterpm', $display_filterpm);
		$this->assignRef('display_filterarticle', $display_filterarticle);
		$this->assignRef('displayPm', $displayPm);
		$this->assignRef('displayShare', $displayShare);
		$this->assignRef('displayLike', $displayLike);
		$this->assignRef('moderator_users', $moderator_users);
		$this->assignRef('display_group', $display_group);
		$this->assignRef('display_group_for_moderators', $display_group_for_moderators);

		
		//joomla categorylist
		$query = "SELECT id AS value, title AS text FROM #__categories WHERE extension='com_content'";
		//echo $query ;
		$db->setQuery($query);
		$catrows = $db->loadObjectList();
		if(count($catrows))
		{
			$types1[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select Category' ) .' -' );
			foreach( $db->loadObjectList() as $obj )
			{
			$types1[] = JHTML::_('select.option',  $obj->value, JText::_( $obj->text ) );
			}
			
			
			$lists['catid'] 	= JHTML::_('select.genericlist',   $types1, 'catid', 'class="inputbox" style="width:150px; height:20px; font-size:14px; " size="1" ', 'value', 'text', "$catid" );
		}

		$this->assignRef('lists', $lists);
		

		//Select AM PM...
			$amPmSelect		= array();
			$amPmSelect[]		= JHTML::_('select.option',  'AM', "am" );
			$amPmSelect[]		= JHTML::_('select.option',  'PM', "pm" );
			$startAmPmSelect	= JHTML::_('select.genericlist',  $amPmSelect , 'starttime-ampm', array('class'=>'required inputbox','style'=>'width:50px !important;'), 'value', 'text', '' , false );
			$endAmPmSelect		= JHTML::_('select.genericlist',  $amPmSelect , 'endtime-ampm', array('class'=>'required inputbox','style'=>'width:50px !important;'), 'value', 'text', '' , false );
		$this->assignRef('startAmPmSelect', $startAmPmSelect);
		$this->assignRef('endAmPmSelect', $endAmPmSelect);
		
		//Select Hour...
			for($i = 1; $i <= 12; $i++)
			{
				$hours[] = JHTML::_('select.option',  $i, sprintf( "%02d" ,$i) );
			}
		$startHourSelect		= JHTML::_('select.genericlist',  $hours, 'starttime-hour', array('class'=>'required inputbox','style'=>'width:50px !important;'), 'value', 'text', '' , false );
		$endHourSelect			= JHTML::_('select.genericlist',  $hours, 'endtime-hour', array('class'=>'required inputbox','style'=>'width:50px !important;'), 'value', 'text', '' , false );
		$this->assignRef('startHourSelect', $startHourSelect);
		$this->assignRef('endHourSelect', $endHourSelect);
		
		//Select Minute...
		$minutes	= array();
		$minutes[]	= JHTML::_('select.option',  "00", "00" );
		$minutes[]	= JHTML::_('select.option',  15, "15" );
		$minutes[]	= JHTML::_('select.option',  30, "30" );
		$minutes[] 	= JHTML::_('select.option',  45, "45" );

		$startMinSelect		= JHTML::_('select.genericlist',  $minutes , 'starttime-minute', array('class'=>'required inputbox','style'=>'width:50px !important;'), 'value', 'text', '' , false );
		$endMinSelect		= JHTML::_('select.genericlist',  $minutes , 'endtime-minute', array('class'=>'required inputbox','style'=>'width:50px !important;'), 'value', 'text', '' , false );
		$this->assignRef('startMinSelect', $startMinSelect);
		$this->assignRef('endMinSelect', $endMinSelect);

		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		parent::display($tpl);

	}

	

	function groupSetting($tpl = null)

	{				
$db =& JFactory::getDBO();
		$user = &JFactory::getUser();

		$groupModel = & $this->getModel('group');

		// get configuration from database

		//$config = &JComponentHelper::getParams('com_awdwall');
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayName 	= $config->get('display_name', 1);
		$this->assignRef('displayName', $displayName);
		// check if logged users are owner of this group

		$groupId = JRequest::getInt('groupid', 0);

		// check if logged users are owner of this group		

		$owner = $groupModel->checkGrpOwner($user->id, $groupId);		

		$this->assignRef('owner', $owner);

		// check if private group 

		$isPrivate = $groupModel->isPriaveGrp($groupId);

		if($isPrivate){

			// check if this user that viewing is member of this group

			$this->assignRef('isPrivate', $isPrivate);

		}else{

			

		}
		
		$display_group = $config->get('display_group', 1);
		$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
		$moderator_users = $config->get('moderator_users', '');
		$moderator_users=explode(',',$moderator_users);
		
		$this->assignRef('display_group', $display_group);
		$this->assignRef('display_group_for_moderators', $display_group_for_moderators);
		$this->assignRef('moderator_users', $moderator_users);
		
		// get total members of this groups

		$groupsLimit = (int)$config->get('nof_groups', 4);

		$members = $groupModel->getAllMemberByGrp($groupId, $groupsLimit);

		$nofMembers = count($members);

		$this->assignRef('members', $members);

		$this->assignRef('nofMembers', $nofMembers);

		$query = 'SELECT COUNT(*) FROM #__users AS u '
				.'INNER JOIN #__awd_groups_members AS gm ON gm.user_id = u.id '
				.'WHERE u.block=0 AND u.id NOT IN (SELECT creator FROM #__awd_groups WHERE id = ' . (int)$groupId . ') AND gm.group_id = ' . (int)$groupId
				;
		$db->setQuery($query);
		$totalUsers = $db->loadResult();	
		$this->assignRef('nofMembers', $totalUsers);	

		//color

		$db		=& JFactory::getDBO();

//		$query = "SELECT params FROM #__components WHERE `name`='Colors' AND `admin_menu_link`='option=com_awdwall&controller=colors'";
		$link='index.php?option=com_awdwall&controller=colors';
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$params = json_decode( $db->loadResult(), true );
		for($i=1; $i<=14; $i++)
		{
			$str_color = 'color'.$i;			
			$color[$i]= $params[$str_color];
		}

		

		$this->assignRef('color', $color);

		

		//get member creator group

		$memberscreator = $groupModel->getMembercreatorByGrp($groupId);

		$this->assignRef('memberscreator', $memberscreator);

		

		// get group info

		$grpInfo = $groupModel->getGroupInfo($groupId);

		$this->assignRef('grpInfo', $grpInfo);

		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		parent::display($tpl);

	}

	

	function grpMembers($tpl = null)

	{

		$user = &JFactory::getUser();
$db =& JFactory::getDBO();
		$groupModel = & $this->getModel('group');

		// check if logged users are owner of this group

		$groupId = JRequest::getInt('groupid', 0);

		$owner = $groupModel->checkGrpOwner($user->id, $groupId);		

		$this->assignRef('owner', $owner);

		// get group info

		$grpInfo = $groupModel->getGroupInfo($groupId);

		$this->assignRef('grpInfo', $grpInfo);

		// check if private group 

		$isPrivate = $groupModel->isPriaveGrp($groupId);

		if($isPrivate){

			// check if this user that viewing is member of this group

			$this->assignRef('isPrivate', $isPrivate);

		}else{

			

		}

		// check if this user is member of this group

		$isMemberGrp = $groupModel->isMemberGrp($groupId, $user->id);

		$this->assignRef('isMemberGrp', $isMemberGrp);

		// get total members of this groups

		// get configuration from database

		//$config = &JComponentHelper::getParams('com_awdwall');
		
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayPm = $config->get('display_pm', 1);
		$display_group = $config->get('display_group', 1);
		$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
		$moderator_users = $config->get('moderator_users', '');
		$moderator_users=explode(',',$moderator_users);
		
		$this->assignRef('displayPm', $displayPm);
		$this->assignRef('display_group', $display_group);
		$this->assignRef('display_group_for_moderators', $display_group_for_moderators);
		$this->assignRef('moderator_users', $moderator_users);
		

		$displayName 	= $config->get('display_name', 1);

		$groupsLimit = (int)$config->get('nof_groups', 4);

		$members = $groupModel->getAllMemberByGrp($groupId, $groupsLimit);

		$nofMembers = count($members);

		$this->assignRef('members', $members);

		$this->assignRef('nofMembers', $nofMembers);
		$query = 'SELECT COUNT(*) FROM #__users AS u '
				.'INNER JOIN #__awd_groups_members AS gm ON gm.user_id = u.id '
				.'WHERE u.block=0 AND u.id NOT IN (SELECT creator FROM #__awd_groups WHERE id = ' . (int)$groupId . ') AND gm.group_id = ' . (int)$groupId
				;
		$db->setQuery($query);
		$totalUsers = $db->loadResult();	
		$this->assignRef('nofMembers', $totalUsers);	
		// get all users

		$usersLimit = (int)$config->get('nof_invite_members', 10);

		

		$limit		= JRequest::getVar('limit', $usersLimit, '', 'int');

		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		$db 	= &JFactory::getDBO();	

		$query = 'SELECT COUNT(*) FROM #__users AS u '

				.'INNER JOIN #__awd_groups_members AS gm ON gm.user_id = u.id '

				.'WHERE u.block=0 AND u.id NOT IN (SELECT creator FROM #__awd_groups WHERE id = ' . (int)$groupId . ') AND gm.group_id = ' . (int)$groupId

				;

		$db->setQuery($query);

		$totalUsers = $db->loadResult();

		

		//color

		$db		=& JFactory::getDBO();

//		$query = "SELECT params FROM #__components WHERE `name`='Colors' AND `admin_menu_link`='option=com_awdwall&controller=colors'";
		$link='index.php?option=com_awdwall&controller=colors';
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$params = json_decode( $db->loadResult(), true );
		for($i=1; $i<=14; $i++)
		{
			$str_color = 'color'.$i;			
			$color[$i]= $params[$str_color];
		}

		

		$this->assignRef('color', $color);

		

		//get member creator group

		$memberscreator = $groupModel->getMembercreatorByGrp($groupId);

		$this->assignRef('memberscreator', $memberscreator);

		

		jimport('joomla.html.pagination');

		$pageNav = new JPagination($totalUsers, $limitstart, $limit);

		$query = 'SELECT u.*, gm.status, gm.group_id, gm.created_date FROM #__users AS u '

				.'INNER JOIN #__awd_groups_members AS gm ON gm.user_id = u.id '

				.'WHERE u.block=0 AND u.id NOT IN (SELECT creator FROM #__awd_groups WHERE id = ' . (int)$groupId . ') AND gm.group_id = ' . (int)$groupId . ' AND gm.status = 1 '

				.'LIMIT ' . $pageNav->limitstart . ', ' . $pageNav->limit

				;

		$db->setQuery($query);

		$users = $db->loadObjectList();

		$this->assignRef('limitstart', $limitstart);

		$this->assignRef('pageNav', $pageNav);

		$this->assignRef('users', $users);

		$this->assignRef('displayName', $displayName);

		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		parent::display($tpl);

	}

	

	function inviteMembers($tpl = null)

	{
		$db		=& JFactory::getDBO();
		$user = &JFactory::getUser();

		$groupModel = & $this->getModel('group');

		// get configuration from database

		//$config = &JComponentHelper::getParams('com_awdwall');
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayName 	= $config->get('display_name', 1);

		// check if logged users are owner of this group

		$groupId = JRequest::getInt('groupid', 0);

		$owner = $groupModel->checkGrpOwner($user->id, $groupId);		

		$this->assignRef('owner', $owner);

		// get group info

		$grpInfo = $groupModel->getGroupInfo($groupId);

		$this->assignRef('grpInfo', $grpInfo);

		// check if private group 

		$isPrivate = $groupModel->isPriaveGrp($groupId);

		if($isPrivate){

			// check if this user that viewing is member of this group

			$this->assignRef('isPrivate', $isPrivate);

		}else{

			

		}

		

		//color

		$db		=& JFactory::getDBO();

//		$query = "SELECT params FROM #__components WHERE `name`='Colors' AND `admin_menu_link`='option=com_awdwall&controller=colors'";
		$link='index.php?option=com_awdwall&controller=colors';
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$params = json_decode( $db->loadResult(), true );
		for($i=1; $i<=14; $i++)
		{
			$str_color = 'color'.$i;			
			$color[$i]= $params[$str_color];
		}

		

		$this->assignRef('color', $color);

		

		//get member creator group (dangcv)

		$memberscreator = $groupModel->getMembercreatorByGrp($groupId);

		$this->assignRef('memberscreator', $memberscreator);

		

		// check if this user is member of this group

		$isMemberGrp = $groupModel->isMemberGrp($groupId, $user->id);

		$this->assignRef('isMemberGrp', $isMemberGrp);

		// get total members of this groups

		$groupsLimit = (int)$config->get('nof_groups', 4);

		$members = $groupModel->getAllMemberByGrp($groupId, $groupsLimit);

		$nofMembers = count($members);

		$this->assignRef('members', $members);

		$this->assignRef('nofMembers', $nofMembers);
		$query = 'SELECT COUNT(*) FROM #__users AS u '
				.'INNER JOIN #__awd_groups_members AS gm ON gm.user_id = u.id '
				.'WHERE u.block=0 AND u.id NOT IN (SELECT creator FROM #__awd_groups WHERE id = ' . (int)$groupId . ') AND gm.group_id = ' . (int)$groupId
				;
		$db->setQuery($query);
		$totalUsers = $db->loadResult();	
		$this->assignRef('nofMembers', $totalUsers);	
		// get all users

		$usersLimit = (int)$config->get('nof_invite_members', 10);

		

		$limit		= JRequest::getVar('limit', $usersLimit, '', 'int');

		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		$db 	= &JFactory::getDBO();	

		$query = 'SELECT COUNT(*) FROM #__users WHERE block=0 AND id NOT IN (' . $user->id . ')';

		$db->setQuery($query);

		$totalUsers = $db->loadResult();

		

		jimport('joomla.html.pagination');

		$pageNav = new JPagination($totalUsers, $limitstart, $limit);

		$query = 'SELECT * FROM #__users '				
				.'WHERE block=0 AND id NOT IN (' . $user->id . ') '
			//	.'LIMIT ' . $pageNav->limitstart . ', ' . $pageNav->limit
				;
		$db->setQuery($query);
		$users = $db->loadObjectList();

		$this->assignRef('limitstart', $limitstart);

		$this->assignRef('pageNav', $pageNav);

		$this->assignRef('users', $users);

		$this->assignRef('groupModel', $groupModel);
		
		$display_group = $config->get('display_group', 1);
		$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
		$moderator_users = $config->get('moderator_users', '');
		$moderator_users=explode(',',$moderator_users);
		
		$this->assignRef('display_group', $display_group);
		$this->assignRef('display_group_for_moderators', $display_group_for_moderators);
		$this->assignRef('moderator_users', $moderator_users);
		
		
		$this->assignRef('limitstart', $limitstart);
		$this->assignRef('pageNav', $pageNav);
		$this->assignRef('users', $users);
		$this->assignRef('groupModel', $groupModel);
		$this->assignRef('displayName', $displayName);
		
		$display_profile_link = $config->get('display_profile_link', 1);
		$this->assignRef('display_profile_link', $display_profile_link);
		parent::display($tpl);

	}

}

?>

