<?php
/**
 * @version 2.5
 * @package AWDwall
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
defined('_JEXEC') or die('Restricted access');
 error_reporting(0);

if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}

if($_REQUEST['option']=='com_comprofiler'){
$cbsession =& JFactory::getSession();
$cbtable = & JTable::getInstance('session');
$cbtable->load( $cbsession->getId() );
if($cbtable->client_id != 1) {
	if (file_exists(JPATH_BASE.'/components'. DS .'com_awdwall' . DS . 'awdwall.php')) //if com_awdwall install
	{
		$otherlanguage =& JFactory::getLanguage();
		$otherlanguage->load( 'com_awdwall', JPATH_SITE );
		require_once(JPATH_ROOT.DS.'components'.DS.'com_awdwall' . DS . 'defines.php');
		require_once(JPATH_ROOT.DS.'components'.DS.'com_awdwall' . DS . 'helpers' . DS . 'user.php');
		require_once(JPATH_ROOT.DS.'components'.DS.'com_awdwall' . DS . 'libraries' . DS . 'jslib.php');
		require_once (JPATH_ROOT.DS.'components'.DS.'com_awdwall'. DS . 'models' . DS . 'group.php');
		require_once (JPATH_ROOT.DS.'components'.DS.'com_awdwall'. DS . 'models' . DS . 'wall.php');
		require_once (JPATH_ROOT.DS.'components'.DS.'com_awdwall'. DS . 'js' . DS . 'includecb.php');
	}
}
error_reporting(0);
$mainframe	=& JFactory::getApplication();
$db =& JFactory::getDBO();
$usercurrent=&JFactory::getUser();
if(isset($_REQUEST['user']))
{
$userid=$_REQUEST['user'];
}
else
{
$userid=$usercurrent->id;
}
$str=JPATH_COMPONENT;
$pos = strrpos($str, "administrator");
if($pos)
{
}
else
{
if (file_exists(JPATH_BASE.'/components'. DS .'com_awdwall' . DS . 'libraries' . DS . 'jslib.php')) //if com_awdwall install 
{
	require_once (JPATH_BASE.'/components'. DS .'com_awdwall' . DS . 'libraries' . DS . 'jslib.php');
}
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
$display_profile_link 		= $config->get('display_profile_link', 1);
//echo 'display_profile_link='.$display_profile_link;

$privacy = $config->get('privacy', 0);
$showPosts = 1;
	if((int)$privacy == 1)
	{
		if((int)$userid != (int)$usercurrent->id)
		{
			$showPosts = JsLib::isFriend($usercurrent->id, $userid);
		}
	}
}
if(empty($userid)){$userid=$usercurrent->id;}
if($showPosts || $pos!=0){
class getWallTab extends cbTabHandler 
{
	function getWallTab() 
	{
	  $this->cbTabHandler();
	}
	/**
	* Main function, displays the Total Points Tab in Community Builder
	*/	
	function getDisplayTab($tab, $user, $ui) 
	{
		$Itemid = AwdwallHelperUser::getComItemId();
		$cbItemid = AwdwallHelperUser::getJsItemId();
		$page 			= JRequest::getCmd('awd_page', 0);
		$db		=& JFactory::getDBO();
		JHTML::_('behavior.calendar');
		$wallActive = '';
		$videoActive = '';
		$imageActive = '';
		$mp3Active = '';
		$linkActive = '';
		$fileActive = '';
		$trailActive = '';
		$jinglActive = '';
		$pmActive = '';
		$eventActive = '';
		$articleActive = '';
		if($task == 'videos')
			$videoActive = 'class="active"';
		elseif($task == 'images')
			$imageActive = 'class="active"';
		elseif($task == 'music')
			$mp3Active = 'class="active"';
		elseif($task == 'links')
			$linkActive = 'class="active"';
		elseif($task == 'files')
			$fileActive = 'class="active"';
		elseif($task == 'trails')
			$trailActive = 'class="active"';
		elseif($task == 'jing')
			$jingActive = 'class="active"';
		elseif($task == 'events')
			$eventActive = 'class="active"';
			
		elseif($task == 'article')
		
			$articleActive = 'class="active"';
		elseif($task == 'pm')
			$pmActive = 'class="active"';
		else
			$wallActive = 'class="active"';
		
		
		$groupModel = new AwdwallModelGroup();
		$wallModel = new AwdwallModelWall();	
		$document =& JFactory::getDocument();
		$usercurrent=&JFactory::getUser();
		$userId = JRequest::getVar('user', 0, 'default', 'int');
		$wuid = JRequest::getVar('user', 0, 'default', 'int');
		if(!$userId)
			$userId = $usercurrent->id;
		if(!$wuid)
			$wuid = $usercurrent->id;
		$db 	=& JFactory::getDBO();
		
		$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';
		//echo $wallalbumfile;
		$jomalbumexist='';
		if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
		{
			$jomalbumexist=1;
		}
		$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';
		if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
		{
			$showalbumlink=true;
			$infolink="index.php?option=com_awdjomalbum&view=userinfo&wuid=".$wuid."&Itemid=".$Itemid;
			$albumlink="index.php?option=com_awdjomalbum&view=awdalbumlist&wuid=".$wuid."&Itemid=".$Itemid;
		}
		else
		{
			$showalbumlink=false;
			$infolink='';
			$albumlink="";
		}
		$hwdvideosharefile = JPATH_SITE . '/components/com_hwdvideoshare/hwdvideoshare.php';
		$hwdvideoshare='';
		if (file_exists($hwdvideosharefile)) // if com_awdjomalbum install then only
		{
			$hwdvideoshare=1;
		}
		$link='index.php?option=com_awdwall&controller=colors';
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$params = json_decode( $db->loadResult(), true );
		for($i=1; $i<=14; $i++)
		{
			$str_color = 'color'.$i;			
			$color[$i]= $params[$str_color];
		}
		
		//usertype of login user...	
		$usercurrent=&JFactory::getUser();
		$query="SELECT group_id FROM #__user_usergroup_map where user_id=".$usercurrent->id;
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
		if($vowner)
		$can_delete=1;
		
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$template 		= $config->get('temp', 'default');
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
		$getrealtimecomment = $config->get('getrealtimecomment', 0);
		$fields			= $config->get('fieldids', '');
		$displayCommentLike= $config->get('displayCommentLike', 1);
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
		$usercurrent=&JFactory::getUser();
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
		
		if($wuid){
			if($task == 'friends')
				$where[] = '(aw.user_id = ' . (int)$wuid .' OR aw.commenter_id = ' . (int)$wuid . ' )';
			else
				$where[] = '(user_id = ' . (int)$wuid .' OR commenter_id = ' . (int)$wuid . ' )';
			if($wuid != $usercurrent->id){
				if($task == 'friends')
					$where[] = '(IF(aw.is_pm=1, aw.commenter_id = ' . $usercurrent->id . ', 1))';
				else
					$where[] = '(IF(is_pm=1, commenter_id = ' . $usercurrent->id . ', 1))';
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
//			if($usercurrent->id!=$wuid && $grpid)
//			{
//				$where[] = '(IF(group_id !=" null", group_id NOT IN ('.$grpid.'), id))';
//			}
			$where[] = 'group_id IS NULL';
		$where[] = 'wall_date IS NOT NULL';
		/*if((int)$privacy){
				$where[] = '((commenter_id IN (select referenceid FROM #__comprofiler_members where memberid 	 = ' . $wuid . ' AND accepted = 1 AND pending = 0)) OR (user_id = ' . $wuid . ' OR commenter_id = ' . $wuid . '))';
		}*/
		//get total event attend list of user
		
		$sql= 'select wall_id FROM #__awd_wall_event_attend where user_id = ' . $usercurrent->id . ' AND status = 1';
		$db->setQuery($sql);
		$walleventlist=$db->loadObjectList();
		for($i=0;$i<count($walleventlist);$i++)
		{
			$eventlist[$i]=$walleventlist[$i]->wall_id;
		}
		if($eventlist)
			$wallid=implode(",",$eventlist);
		
		//get friend list
		
		$sql= 'select connect_from FROM #__awd_connection where connect_to = ' . $usercurrent->id . ' AND status = 1 AND pending=0';
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
			$friendsuser = $friends.','.$usercurrent->id; 
		}
		else
		$friendsuser = $usercurrent->id;
		
		if($friendoffriends != '')
			$friendoffriendsuser = $friendoffriends.','.$usercurrent->id;
		else
		$friendoffriendsuser = $usercurrent->id;
		
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
	if($wuid!=$usercurrent->id)
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
				$where[] = '((commenter_id IN (' .$friendsuser. ')) OR (user_id = ' . $usercurrent->id . ' OR commenter_id = ' . $usercurrent->id . ') OR (group_id IS NOT NULL))';
			}else{
				$where[] = '((commenter_id IN (' .$friendsuser. ')) OR (user_id = ' . $usercurrent->id . ' OR commenter_id = ' . $usercurrent->id . '))';
			}
		}
		
		if((int)$privacy==2){ 
			
			if($friendoffriends=='')
				$friendoffriends=$usercurrent->id;
				
			if($friendoffriends!='' && $friends!='')
			{
				$friendoffriends=$friendoffriends.','.$friends;
			}
			if($friendoffriends=='' && $friends!='')
			{
				$friendoffriends=$friends;
			}
			if($layout == 'main'){
				
				$where[] = '((commenter_id IN ('.$friendoffriends.')) OR (user_id = ' . $usercurrent->id . ' OR commenter_id = ' . $usercurrent->id . ') OR (group_id IS NOT NULL))';
			}else{
				$where[] = '((commenter_id IN (' .$friendoffriends. ')) OR (user_id = ' . $usercurrent->id . ' OR commenter_id = ' . $usercurrent->id . '))';
			}
		}
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$whr = count($whr) ? ' OR ' . implode( ' OR ', $whr ) : '';
		//$where =$where . $whr;
		
		$offset = $page*$postLimit;
		
		$msgs 	= $wallModel->getAllMsg($postLimit, $where, $offset, $privacy);
		$nofMsgs = $wallModel->countMsg($where, $privacy);
		
//		echo "no of msg=".$nofMsgs;
//		echo "<pre>";
//		print_r($msgs);
//		echo "</pre>";
//		exit;		
			$isFriend = '';
			$showPosts = 1;
			$friendStatus = 1;
			if((int)$privacy == 1){
				if((int)$wuid != (int)$usercurrent->id){
					$showPosts = JsLib::isFriend($usercurrent->id, $wuid);				
				}
				
				if(!(int)$showPosts){
				
					$wallUserName = AwdwallHelperUser::getDisplayName($wuid);
				}
			}
			if((int)$privacy == 2){
				if((int)$wuid != (int)$usercurrent->id){
					$showPosts = JsLib::isFriendOfFriend($usercurrent->id, $wuid);				
				}
			}
		
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
		
			$amPmSelect		= array();
			$amPmSelect[]		= JHTML::_('select.option',  'AM', "am" );
			$amPmSelect[]		= JHTML::_('select.option',  'PM', "pm" );
			$startAmPmSelect	= JHTML::_('select.genericlist',  $amPmSelect , 'starttime-ampm', array('class'=>'required inputbox'), 'value', 'text', '' , false );
			$endAmPmSelect		= JHTML::_('select.genericlist',  $amPmSelect , 'endtime-ampm', array('class'=>'required inputbox'), 'value', 'text', '' , false );
		
		//Select Hour...
			for($i = 1; $i <= 12; $i++)
			{
				$hours[] = JHTML::_('select.option',  $i, sprintf( "%02d" ,$i) );
			}
		$startHourSelect		= JHTML::_('select.genericlist',  $hours, 'starttime-hour', array('class'=>'required inputbox'), 'value', 'text', '' , false );
		$endHourSelect			= JHTML::_('select.genericlist',  $hours, 'endtime-hour', array('class'=>'required inputbox'), 'value', 'text', '' , false );
		
		//Select Minute...
		$minutes	= array();
		$minutes[]	= JHTML::_('select.option',  "00", "00" );
		$minutes[]	= JHTML::_('select.option',  15, "15" );
		$minutes[]	= JHTML::_('select.option',  30, "30" );
		$minutes[] 	= JHTML::_('select.option',  45, "45" );
		$startMinSelect		= JHTML::_('select.genericlist',  $minutes , 'starttime-minute', array('class'=>'required inputbox'), 'value', 'text', '' , false );
		$endMinSelect		= JHTML::_('select.genericlist',  $minutes , 'endtime-minute', array('class'=>'required inputbox'), 'value', 'text', '' , false );
		ob_start();	
	?>
<style type="text/css">
.tab-page {
	float:left;
}
</style>
<script src="<?php echo JURI::base();?>components/com_awdwall/js/selectcustomer.js"></script>
<script type="text/javascript">	
	jQuery(document).ready(function(){
		//dangcv upload status
		jQuery("#awd_message").bind('paste', function(e){
			var el = jQuery(this);
			setTimeout(function() {
				var value = jQuery(el).val();
				ajaxStatusUpload(value);
			}, 100);
		});
		jQuery( "#startdate" ).datepicker({
			showOn: "button",
			buttonImage: "<?php echo JURI::base();?>components/com_awdwall/images/calendar.gif",
			buttonImageOnly: true,
			dateFormat: 'yy-mm-dd'
		});
		jQuery( "#enddate" ).datepicker({
			showOn: "button",
			buttonImage: "<?php echo JURI::base();?>components/com_awdwall/images/calendar.gif",
			buttonImageOnly: true,
			dateFormat: 'yy-mm-dd'
		});
		jQuery.datepicker.formatDate('yy-mm-dd');
	});
	function changeimg(str, wid_tmp){
		var url = document.getElementById("url_root").value;
		url = url+"joomla.php";
		var sid = Math.random();
		str = str.replace("http://", "");
		
		jQuery.get(url, { q: str, q_wid_tmp: wid_tmp, sid: sid },
		function(data){
			//alert("Data Loaded: " + data);
		});
	}
	function ajaxfilter(url,type)
	{ 
		var template = document.getElementById("select_temp").value;
		document.getElementById("msg_content").innerHTML='<img src="' +siteUrl+'components/com_awdwall/images/' +template+ '/ajax-loader.gif" />';
		page =0;
		//alert(url + '&awd_page=' + page + '&type=' + type);
		jQuery.get(url + '&awd_page=' + page + '&type=' + type, {}, 
	
		function(data){
			if(document.getElementById("awd_page"))
			document.getElementById("awd_page").value = 1;		
			document.getElementById("msg_content").innerHTML =data;
			
		}, "html");
		document.getElementById("task").value = type;
	}
	function no_img(){
		var n = document.getElementById('count_img').getAttributeNode('value').value;
		var k = 0;
		for(i=1; i<=n; i++){
			var temp = document.getElementById(i).getAttributeNode('class').value;
			if(temp == 'no_hidden'){
				k = i;
				document.getElementById('hidden_img').value = k;
			}
		}
		var check = document.getElementById('hidden_img').checked;
		if(check){
			for(i=1; i<=n; i++){
				document.getElementById(i).className = 'hidden';
			}
			document.getElementById('cur_image').value = 0;
			document.getElementById('div_awd_attached_img').style.display = 'none';
			document.getElementById('count_text').style.display = 'none';
			document.getElementById('prev').style.display = 'none';
			document.getElementById('next').style.display = 'none';
			//update database
			var src = "";
			var wid_tmp = document.getElementById('wid_tmp').value;
			changeimg(src, wid_tmp);
		}
		else{
			var k = document.getElementById('hidden_img').getAttributeNode('value').value;
			var src = document.getElementById(k).getAttributeNode('src').value;
			var wid_tmp = document.getElementById('wid_tmp').value;
			changeimg(src,wid_tmp);
			for(i=1; i<=n; i++){				
				if(i == k){
					document.getElementById(i).className = 'no_hidden';
					document.getElementById('cur_image').value = k;
				}else{
					document.getElementById(i).className = 'hidden';
				}
			}
			document.getElementById('div_awd_attached_img').style.display = 'block';
			document.getElementById('count_text').style.display = 'block';
			document.getElementById('prev').style.display = 'inline';
			document.getElementById('next').style.display = 'inline';
		}
	}
	function next_img(){
		var temp;
		var id;
		var n = document.getElementById('count_img').getAttributeNode('value').value;
		if(n == 0){
			document.getElementById("count_img_active").innerHTML = 0;
			return true;
		}
		var k = 0;
		for(i=1; i<=n; i++){
			temp = document.getElementById(i).getAttributeNode('class').value;
			if(temp == 'no_hidden'){
				k = i;
				id = document.getElementById(i).getAttributeNode('class').value;
			}
		}
		if( k == 0 || k == n ){
			k = n; 
		}else{
			k = k + 1;
		}
		document.getElementById("count_img_active").innerHTML = k;
		var src = document.getElementById(k).getAttributeNode('src').value;
		var wid_tmp = document.getElementById('wid_tmp').value;
		changeimg(src,wid_tmp);					
		for(i=1; i<=n; i++){
			if(i == k){
				document.getElementById(i).className = 'no_hidden';
				document.getElementById('cur_image').value = i;
			}
			else{
				document.getElementById(i).className = 'hidden';
			}
		}
	}
	function prev_img(){
		var temp;
		var id;
		var n = document.getElementById('count_img').getAttributeNode('value').value;
		if(n == 0){
			document.getElementById("count_img_active").innerHTML = 0;
			return true;
		}
		var k = 0;
		for(i=1; i<=n; i++){
			temp = document.getElementById(i).getAttributeNode('class').value;
			if(temp == 'no_hidden'){
				k = i;
				id = document.getElementById(i).getAttributeNode('class').value;
			}
		}
		if(k == 1){
			k =1;
		}else{
			k = k-1;
		}
		document.getElementById("count_img_active").innerHTML = k;
		var src = document.getElementById(k).getAttributeNode('src').value;
		var wid_tmp = document.getElementById('wid_tmp').value;
		changeimg(src, wid_tmp);
		
		for(i=1; i<=n; i++){
			if(i == k){
				document.getElementById(i).className = 'no_hidden';
				document.getElementById('cur_image').value = i;
			}
			else{
				document.getElementById(i).className = 'hidden';
			}
		}
	}
</script>
<script type="text/javascript">
		//show share
		function show_share(){
			getElementByClass_share('a', 'ashare');
		}
		function hidden_share(){
			getElementByClass_share('a', 'ashare');
			var att=document.getElementById(d).getAttributeNode('rel').value;
			document.getElementById(att).style.display = "none";
			getElementByClass_share('a', 'ashare');
		}
		function getElementByClass_share(node, class_name){
			var tag = document.getElementsByTagName(node);
			var getAgn = tag;
			class_name_click = class_name+'ashare';
			class_name_unclick = class_name+'ashare';
			for (i=0; i<tag.length; i++) {
				if(tag[i].className == class_name_click || tag[i].className == class_name || tag[i].className == class_name_unclick){
					tag[i].onclick=function() {																		
						for (var x=0; x<getAgn.length; x++) {
							getAgn[x].className=getAgn[x].className.replace("unclick", "");
							getAgn[x].className=getAgn[x].className.replace("click", "unclick");																					
						}
						if ((this.className.indexOf('unclick'))!=-1) {
							this.className=this.className.replace("unclick", "");																			
						}
						else { this.className+="click";}
						if(this.className == 'ashareclick')
							inser_share('a', 'ashare', 'ashareclick', 'ashare');
						if(this.className == 'ashare')
							inser_share('a', 'ashare', 'ashareclick', 'ashareclick');	
					}
				}
			}
		}
		
		function inser_share(node, text, class_name1, class_name2){
			var elms=document.getElementsByTagName(node);
			for(i=0;i<elms.length;i++){
				if(elms[i].className== class_name1){
					elms[i].id = text + i;
					d =  text + i;
				}
				if(elms[i].className==class_name2){ elms[i].id = ""; }
			}
			var att			= document.getElementById(d);
			var id		 	= att.getAttributeNode('rel').value;
			var id_tyle		= document.getElementById(id);
			display(id_tyle, 'inline');
		}
		
		function display(type, text){
			type.style.display = text;
		}
	
		//show video
		function show_video(){
			getElementByClass('a');
		}
		function getElementByClass(node){
			var tag = document.getElementsByTagName(node);
			var getAgn = tag;
			for (i=0; i<tag.length; i++) {
				if(tag[i].className == 'show_videclick' || tag[i].className == 'show_vide' || tag[i].className == 'show_videunclick'){
					tag[i].onclick=function() {																		
						for (var x=0; x<getAgn.length; x++) {
							getAgn[x].className=getAgn[x].className.replace("unclick", "");
							getAgn[x].className=getAgn[x].className.replace("click", "unclick");																					
						}
						if ((this.className.indexOf('unclick'))!=-1) {
							this.className=this.className.replace("unclick", "");																			
						}
						else { this.className+="click";	}
						inser_id();
					}
				}
			}
		}
		
		function inser_id(){
			var elms=document.getElementsByTagName('a');
			for(i=0;i<elms.length;i++){
				if(elms[i].className=='show_videclick'){
					elms[i].id="ab"+i;
					d = "ab"+i;
				}
				if(elms[i].className=='show_videunclick'){ elms[i].id=""; }
			}
			
			var att=document.getElementById(d);
			var url	 		= att.getAttributeNode('rel').value;
			var type		= att.getAttributeNode('alt').value;
			var id_inser 	= att.getAttributeNode('rev').value;
			
			var div 		= document.getElementById(id_inser);
			if(type = 'youtube'){
				div.innerHTML = '<object width="426" height="240" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="opaque" name="wmode"><param value="true" name="allowfullscreen"><param value="always" name="allowscriptaccess"><param value="'+url+'&amp;autoplay=1" name="movie"><embed width="426" height="240" wmode="opaque" allowscriptaccess="always" allowfullscreen="true" type="application/x-shockwave-flash" src="'+url+'&amp;autoplay=1"></object>';
			}
			if(type = 'metacafe'){
				div.innerHTML = '<object width="426" height="240" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="opaque" name="wmode"><param value="true" name="allowfullscreen"><param value="always" name="allowscriptaccess"><param value="'+url+'" name="movie"><embed width="426" height="240" wmode="opaque" allowscriptaccess="always" allowfullscreen="true" type="application/x-shockwave-flash" src="'+url+'"></object>';
			}
			if(type = 'vimeo'){
				div.innerHTML = '<object width="426" height="240" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="opaque" name="wmode"><param value="true" name="allowfullscreen"><param value="always" name="allowscriptaccess"><param value="'+url+'" name="movie"><embed width="426" height="240" wmode="opaque" allowscriptaccess="always" allowfullscreen="true" type="application/x-shockwave-flash" src="'+url+'"></object>';
			}
			if(type = 'myspace'){
				div.innerHTML = '<object width="426" height="240" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="opaque" name="wmode"><param value="true" name="allowfullscreen"><param value="always" name="allowscriptaccess"><param value="'+url+'" name="movie"><embed width="426" height="240" wmode="opaque" allowscriptaccess="always" allowfullscreen="true" type="application/x-shockwave-flash" src="'+url+'"></object>';
			}
			if(type = 'howcast'){
				div.innerHTML = '<object width="426" height="240" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="opaque" name="wmode"><param value="true" name="allowfullscreen"><param value="always" name="allowscriptaccess"><param value="'+url+'" name="movie"><embed style="background:#000000" width="426" height="240" wmode="opaque" allowscriptaccess="always" allowfullscreen="true" type="application/x-shockwave-flash" src="'+url+'"></object>';
			}
		}			
			
</script>
<div  id="awd-mainarea" style="width:95.5%;">
  <style type="text/css">
#awd-mainarea p{
clear:both;}

	#awd-mainarea .wallheadingRight ul li a, #awd-mainarea .wallheadingRight ul li.separator{
		color:#<?php echo $color[1]; ?>;
	}
	#awd-mainarea .profileLink a, #awd-mainarea .profileMenu .ppm a, #awd-mainarea #msg_content .rbroundboxleft .mid_content a, #awd-mainarea .commentinfo a, #awd-mainarea .user_place ul.profileMenu li a{
		color:#<?php echo $color[2]; ?>;
	}
	#awd-mainarea #awd_message, #awd-mainarea .right_mid_content .walltowall li{
		color:#<?php echo $color[3]; ?>!important;
	}
	#awd-mainarea .wall_date{
		color:#<?php echo $color[4]; ?>;
	}
	 #awd-mainarea .rbroundboxright, #awd-mainarea .fullboxtop,  #awd-mainarea .lightblue_box, #awd-mainarea .rbroundboxrighttop{
		background-color:#<?php echo $color[5]; ?>;
	}
	<?php if($template!='default'){?>
	#awd-mainarea .mid_content_top{
	background-color:#<?php echo $color[5]; ?>;
	}
	<?php }else{ ?>
	#awd-mainarea .mid_content_top{
	background-color:#<?php echo $color[1]; ?>;
	}
	#awd-mainarea .rbroundboxright{
	 background-color:#<?php echo $color[1]; ?>;
	}
	<?php }?>
	#awd-mainarea .rbroundboxleft .mid_content a, #awd-mainarea .blog_groups, #awd-mainarea .blog_groups a, #awd-mainarea .blog_friend, #awd-mainarea .blog_friend a, #awd-mainarea a.authorlink{
		color:#<?php echo $color[6]; ?>!important;
	}
	#awd-mainarea #msg_content .rbroundboxleft, #awd-mainarea #msg_content .awdfullbox{
		background-color:#<?php echo $color[7]; ?>;
	}
	#awd-mainarea .walltowall li a, #msg_content .maincomment_noimg_right h3 a{
		color:#<?php echo $color[8]; ?>;
	}
	#awd-mainarea ul.tabProfile li a{
		background-color:#<?php echo $color[9]; ?>;
	}
	#awd-mainarea ul.tabProfile li a:hover, #awd-mainarea ul.tabProfile li.active a{
		background-color:#<?php echo $color[10]; ?>;
	}
	#awd-mainarea ul.tabProfile li a{
		color:#<?php echo $color[11]; ?>;
	}
	#awd-mainarea .whitebox, #awd-mainarea .blog_content, #awd-mainarea .about_me{
		background-color:#<?php echo $color[12]; ?>;
	}
	<?php if($template!='default'){?>
	#awd-mainarea .wallheading, #awd-mainarea .wallheadingRight{
		background-color:#<?php echo $color[13]; ?>;
	}
	#awd-mainarea .round, #awd-mainarea .search_user{
		background-color:#<?php echo $color[14]; ?>;
	}
	<?php } ?>
#awd-mainarea .notiItemsWrap{
background-color:#<?php echo $color[12]; ?>;
color:#<?php echo $color[3]; ?>;
}
#awd-mainarea .notiItemsWrapLast{
background-color:#<?php echo $color[12]; ?>;
color:#<?php echo $color[3]; ?>;
}

#awd-mainarea #dropAlerts .notiItem a{
color:#<?php echo $color[2]; ?>!important;
}
#awd-mainarea ul.profileMenu .totalno{
	background:#<?php echo $color[12]; ?>;
    border: 1px solid #<?php echo $color[2]; ?>;
    color: #<?php echo $color[8]; ?>;
    float: right;
    margin-right: 5px;
    margin-top: 0;
    padding: 0 3px;
    text-align: center;
}

#awd-mainarea ul.profileMenu li {
    margin-bottom: 5px;
}
#awd-mainarea ul.profileMenu .mainwalllavel
{
color:#<?php echo $color[8]; ?>;
font-weight:bold;
float:left;
}
#awd-mainarea .user_place .wall .profileLink a{
margin:0px;
padding:0px;
font-weight:normal !important;
}
#awd-mainarea .user_place .wall a{
font-weight:bold !important;
}
/*.awd_dropDownMain,.awd_dropDownMain{
margin:9px -50px;
}*/
#awd-mainarea .walltowall li .postlink a
{
font-weight:normal !important;
}
#awd-mainarea  .profileName  a{	
padding-left:0px !important;
}
#main ul
{ 
list-style-type:none !important;
padding-left:0px;
}
#awd-mainarea .post_type_icon
{ margin-left:-20px;}
#awd-mainarea .ui-widget{
font-size:12px!important;
}
</style>
<?php 
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
$display_gallery = $config->get('display_gallery', 1);
$friendJsUrl = JRoute::_('index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid);
$groupsUrl = JRoute::_('index.php?option=com_awdwall&task=groups&Itemid=' . $Itemid, false);

?>
<div class="wallheading">
    <div class="wallheadingRight">
      <ul>
		<?php $layout = $_REQUEST['layout']; ?>
		<li class="logo"><img src="components/com_awdwall/images/awdwall.png" alt="AWDwall" title="AWDwall"></li>
        <li <?php if($layout == 'main') {echo 'class="activenews"';}else{echo 'class="newsfeed"';} ?>><a <?php if($layout == 'main') {echo 'class="activenews"';}else{echo 'class="newsfeed"';} ?> href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('News Feed');?>" ></a> </li>
		<?php if((int)$usercurrent->id){?>
        <li class="separator"> </li>
        <li><a href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('Friends');?>"><?php echo JText::_('Friends');?><font color="red" size="1"><?php if((int)$pendingFriends > 1) echo '(' . $pendingFriends . JText::_('Requests') . ')';elseif((int)$pendingFriends == 1) echo '(' . $pendingFriends . JText::_('') . ')';?></font></a></li>
		<?php if($display_group || ($display_group_for_moderators && in_array($usercurrent->id,$moderator_users)) ) {?>
        
		<li class="separator"> </li>
		 <li> <a href="<?php echo $groupsUrl;?>" title="<?php echo JText::_('Groups');?>"><?php echo JText::_('Groups');?></a> </li>
		<?php }?>
        <li class="separator"> </li>
		<?php  if($showalbumlink){?>
        <?php  if($display_gallery==1){?>
                 <li> <a href="<?php echo JRoute::_($albumlink);?>" title="<?php echo JText::_('Gallery');?>"><?php echo JText::_('Gallery');?></a> </li>
                <li class="separator"> </li>
        <?php }?>
        <?php }?>
         <li> <a href="<?php echo JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $usercurrent->id . '&Itemid=' . AwdwallHelperUser::getJsItemId(), false);?>" title="<?php echo JText::_('Account');?>"><?php echo JText::_('Account');?></a> </li>
        <li class="separator"> </li>
        
        <li class="no signout" style="float:right;"> <a href="javascript:void(0)" title="<?php echo JText::_('Sign out');?>"  onclick="awdsignout();"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/logoutbutton.png" alt="<?php echo JText::_('Sign out');?>" title="<?php echo JText::_('Sign out');?>" class="imglogout" /></a> </li>
		<li style="float:right;" class="toolbaravtar">
        <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('My Wall');?>"  <?php if($layout == 'mywall') {echo 'class="active"';} ?>>
        <div style="height:40px;">
        <div style=" float:left; width:32px;"><img src="<?php echo AwdwallHelperUser::getBigAvatar32($usercurrent->id);?>" class="avtartool"  height="32" width="32"/></div>
        <div style=" float:left; width:auto; margin-left:6px;"><?php if((int)$displayName == USERNAME) {?>
				<?php echo substr(JText::sprintf('Users Wall', $usercurrent->username),0,15);?>
                <?php }else{?>
                <?php echo substr(JText::sprintf('Users Wall', $usercurrent->name),0,15);?>
            <?php }?></div>
        </div>
        </a>
        </li>
        <?php }?>
      </ul>
    </div>
  </div>
  <div class="awdfullbox" style="width:100%;"> <span class="bl"></span>
    <div class="rbroundboxrighttop" style="width:98%;min-height:150px; padding:5px;"> <span class="bl2"></span><span class="br2"></span>
      <ul class="tabProfile">
        <li <?php echo $wallActive;?>>
          <?php if((int)$display_filterwall){?>
          <a href="<?php echo JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $wuid . '&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Wall');?></a></li>
        <?php }?>
        <?php if((int)$display_filtervideo){?>
        <li <?php echo $videoActive;?>><a href="javascript:void(0);" onclick="return ajaxfilter('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getoldermsg&tmpl=component&wuid=' . $wuid . '&Itemid=' . $Itemid;?>','videos');"><?php echo JText::_('Videos');?></a></li>
        <?php }?>
        <?php if((int)$display_filterimage){?>
        <li <?php echo $imageActive;?>><a href="javascript:void(0);" onclick="return ajaxfilter('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getoldermsg&tmpl=component&wuid=' . $wuid . '&Itemid=' . $Itemid;?>','images');"><?php echo JText::_('Images');?></a></li>
        <?php }?>
        <?php if((int)$display_filtermusic){?>
        <li <?php echo $mp3Active;?>><a href="javascript:void(0);" onclick="return ajaxfilter('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getoldermsg&tmpl=component&wuid=' . $wuid . '&Itemid=' . $Itemid;?>','music');"><?php echo JText::_('Mp3');?></a></li>
        <?php }?>
        <?php if((int)$display_filterlink){?>
        <li <?php echo $linkActive;?>><a href="javascript:void(0);" onclick="return ajaxfilter('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getoldermsg&tmpl=component&wuid=' . $wuid . '&Itemid=' . $Itemid;?>','links');"><?php echo JText::_('Links');?></a></li>
        <?php }?>
        <?php if((int)$display_filterfile){?>
        <li <?php echo $filesActive;?>><a href="javascript:void(0);" onclick="return ajaxfilter('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getoldermsg&tmpl=component&wuid=' . $wuid . '&Itemid=' . $Itemid;?>','files');"><?php echo JText::_('Files');?></a></li>
        <?php }?>
        <?php if((int)$display_filtertrail){?>
        <li <?php echo $trailActive;?>><a href="javascript:void(0);" onclick="return ajaxfilter('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getoldermsg&tmpl=component&wuid=' . $wuid . '&Itemid=' . $Itemid;?>','trails');"><?php echo JText::_('Trails');?></a></li>
        <?php }?>
        <?php if((int)$display_filterjing){?>
        <li <?php echo $jingActive;?>><a href="javascript:void(0);" onclick="return ajaxfilter('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getoldermsg&tmpl=component&wuid=' . $wuid . '&Itemid=' . $Itemid;?>','jing');"><?php echo JText::_('Jing');?></a></li>
        <?php }?>
        <?php if((int)$display_filterevent){?>
        <li <?php echo $eventActive;?>><a href="javascript:void(0);" onclick="return ajaxfilter('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getoldermsg&tmpl=component&wuid=' . $wuid . '&Itemid=' . $Itemid;?>','events');"><?php echo JText::_('Events');?></a></li>
        <?php }?>
        <?php if((int)$display_filterarticle){?>
        <li <?php echo $articleActive;?>><a href="javascript:void(0);" onclick="return ajaxfilter('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getoldermsg&tmpl=component&wuid=' . $wuid . '&Itemid=' . $Itemid;?>','article');"><?php echo JText::_('Article');?></a></li>
        <?php }?>
        <?php if((int)$display_filterpm){?>
        <li <?php echo $pmActive;?>><a href="javascript:void(0);" onclick="return ajaxfilter('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getoldermsg&tmpl=component&wuid=' . $wuid . '&Itemid=' . $Itemid;?>','pm');"><?php echo JText::_('Private Message');?></a></li>
        <?php }?>
      </ul>
      <form name="frm_message" id="frm_message" method="post" action="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&task=addmsg&Itemid=' . $Itemid, false);?>" onsubmit="return false;" enctype="multipart/form-data">
      

<?php echo AwdwallHelperUser::getSmileyicons("awd_message");?>
           <textarea  rows="" cols="60" class="round" id="awd_message" name="awd_message" style="width:96% !important;"></textarea>  
<?php echo AwdwallHelperUser::awdshowsmilyicon("awd_message");?>
      
        <input type="hidden" name="wall_last_time" id="wall_last_time" value="<?php echo time();?>" />
        <input type="hidden" name="layout" id="layout" value="mywall" />
        <input type="hidden" name="posted_wid" id="posted_wid" value="" />
        <input type="hidden" name="awd_task" id="awd_task" value="<?php echo $task;?>" />
        <input type="hidden"  name="post_privacy" id="post_privacy" value="0" />
        <input type="hidden" name="receiver_id" id="receiver_id" value="<?php echo $wuid;?>"/>
        <input type="hidden" name="layout" id="layout" value="mywall"/>
        <input type="hidden" name="wuid" id="wuid" value="<?php echo $wuid;?>"/>
        <span style="float:right; width:160px">
        <select class="awd_Selectprivacy js_PriDefault" name="creator-privacy" style="display: none;">
          <option selected="selected" value="0" class="js_PriOption js_Pri-0"><?php echo JText::_('Everyone');?></option>
          <option value="1" class="js_PriOption js_Pri-30"><?php echo JText::_('Friends');?></option>
          <option value="2" class="js_PriOption js_Pri-20"><?php echo JText::_('Friend Of Friends');?></option>
        </select>
        <input class="postButton_small"  title="<?php echo JText::_('Click to post attachment');?>" value="<?php echo JText::_('POST BUTTON TEXT');?>"  type="submit" onclick="aPostMsg('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=addmsg&tmpl=component&layout=mywall&Itemid='.$Itemid;?>', '<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlatestpost&tmpl=component&layout=mywall&wuid=' . $wuid.'&Itemid='.$Itemid;?>');" style="margin-right:30px; margin-top:12px;"/>
        </span>
        
        <ul class="attach" style="padding-left:10px;">
          <li><?php echo JText::_('Attach');?>:</li>
          <?php if((int)$displayVideo){?>
          <li><a href="javascript:void(0);" onclick="openVideoBox();" style="padding-top:2px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-videos.png" title="<?php echo JText::_('Videos');?>" alt="<?php echo JText::_('Videos');?>" border="0" /></a></li>
          <?php }?>
          <?php if((int)$displayImage){?>
          <li><a href="javascript:void(0);" onclick="openImageBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-images.png" title="<?php echo JText::_('Images');?>" alt="<?php echo JText::_('Images');?>" border="0" /></a></li>
          <?php }?>
          <?php if((int)$displayMusic){?>
          <li><a href="javascript:void(0);" onclick="openMp3Box();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-music.png" title="<?php echo JText::_('Music');?>" alt="<?php echo JText::_('Music');?>" border="0" /></a></li>
          <?php }?>
          <?php if((int)$displayLink){?>
          <li><a href="javascript:void(0);" onclick="openLinkBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('Links');?>" alt="<?php echo JText::_('Links');?>" border="0" /></a></li>
          <?php }?>
          <?php if((int)$displayFile){?>
          <li><a href="javascript:void(0);" onclick="openFileBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-file.png" title="<?php echo JText::_('Files');?>" alt="<?php echo JText::_('Files');?>" border="0" /></a></li>
          <?php }?>
          <?php if((int)$displayTrail){?>
          <li><a href="javascript:void(0);" onclick="openTrailBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-trails.png" title="<?php echo JText::_('Trails');?>" alt="<?php echo JText::_('Trails');?>" border="0" /></a></li>
          <?php }?>
          <?php if((int)$displayJing){?>
          <li><a href="javascript:void(0);" onclick="openJingBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-jing.png" title="<?php echo JText::_('Jing');?>" alt="<?php echo JText::_('Jing');?>" border="0" /></a></li>
          <?php }?>
          <?php if((int)$displayEvent){?>
          <li><a href="javascript:void(0);" onclick="openEventBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-event.png" title="<?php echo JText::_('Events');?>" alt="<?php echo JText::_('Events');?>" border="0" /></a></li>
          <?php }?>
          <?php if((int)$displayArticle){ 
					if($user_groupid){?>
          <li><a href="javascript:void(0);" onclick="openArticleBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-article.png" title="<?php echo JText::_('Article');?>" alt="<?php echo JText::_('Article');?>" border="0" /></a></li>
          <?php } 
			   }?>
          <li id="status_loading" style="display: none;">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/default/ajax-loader.gif"></span>
        </ul>
        <input type="hidden" name="cur_image" id="cur_image" />
        <div style="display:none;" id="div_awd_attached_file">
          <h3><?php echo JText::_('Attached');?></h3>
          <div id="div_awd_attached_img"> </div>
          <div id="div_awd_attached_info"> <span class="editable" id="awd_attached_title"></span><br>
            <span id="awd_attached_file"></span><br>
            <span class="editable" id="awd_attached_des"></span> <br>
            <br>
            <div class="awd_attached_attribute">
              <label style="float:left"><img src="<?php echo JURI::base();?>components/com_awdwall/images/prev.png" id="prev" onclick="prev_img();" alt="" /><img src="<?php echo JURI::base();?>components/com_awdwall/images/next.png" id="next" onclick="next_img();" alt="" /></label>
              <div id="count_text"> <span id="count_img_active">1</span> <?php echo Jtext::_('of'); ?> <span id="sum_img"></span> </div>
              <br/>
              <br/>
              <input onclick="no_img()" type="checkbox" value="check" id="hidden_img" name="hidden_img" />
              <?php echo Jtext::_('Hidden image thumb'); ?> </div>
          </div>
          <input type="hidden" id="count_img" />
          <input type="hidden" name="url_root" value="<?php echo JURI::base();?>" id="url_root" />
          <div style="display:none;" id="txtHint"></div>
        </div>
		<!-- video form -->
		<div class="attach_link clearfix message info" style="display:none;" id="awd_video_form" >

		<span class="close"><a href="javascript:void(0);" onClick="closeVideoBox();">x</a></span>
<BR />
		<div style="float:left; width:100%;">
		
		<div style="float:left;padding-right:20px;  ">
			<div class="vicons"><a href="http://www.youtube.com/" title="<?php echo JText::_('YouTube');?>" alt="<?php echo JText::_('YouTube');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/youtube.png" /></a></div>
			<div class="vicons"><a href="http://vids.myspace.com/" title="<?php echo JText::_('Myspace');?>" alt="<?php echo JText::_('Myspace');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/myspace.png" /></a></div>
			<div class="vicons"><a href="http://www.vimeo.com/" title="<?php echo JText::_('Vimeo');?>" alt="<?php echo JText::_('Vimeo');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/vimeo.png" /></a></div>
			<div class="vicons"><a href="http://www.metacafe.com/" title="<?php echo JText::_('Metacafe');?>" alt="<?php echo JText::_('Metacafe');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/metacafe.png" /></a></div>
			<div class="vicons"><a href="http://www.howcast.com/" title="<?php echo JText::_('Howcast');?>" alt="<?php echo JText::_('Howcast');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/howcast.png" /></a></div>
			<div class="vicons"><a href="http://blip.tv/" title="<?php echo JText::_('bliptv');?>" alt="<?php echo JText::_('bliptv');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/bliptv.png" /></a></div>
			<div class="vicons"><a href="http://www.break.com/" title="<?php echo JText::_('break');?>" alt="<?php echo JText::_('break');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/break.png" /></a></div>
			<div class="vicons"><a href="http://www.dailymotion.com/" title="<?php echo JText::_('dailymotion');?>" alt="<?php echo JText::_('dailymotion');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/dailymotion.png" /></a></div>
			<div class="vicons"><a href="http://www.flickr.com/" title="<?php echo JText::_('flickr');?>" alt="<?php echo JText::_('flickr');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/flickr.png" /></a></div>
			<div class="vicons"><a href="http://www.justin.tv/" title="<?php echo JText::_('justintv');?>" alt="<?php echo JText::_('justintv');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/justintv.png" /></a></div>
			<div class="vicons"><a href="http://www.liveleak.com/" title="<?php echo JText::_('liveleak');?>" alt="<?php echo JText::_('liveleak');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/liveleak.png" /></a></div>
		<div class="vicons"><a href="http://screen.yahoo.com/" title="<?php echo JText::_('yahoo');?>" alt="<?php echo JText::_('yahoo');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/yahoo.png" /></a></div>
			<div class="vicons"><a href="http://www.ustream.tv/" title="<?php echo JText::_('ustream');?>" alt="<?php echo JText::_('ustream');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/ustream.png" /></a></div>
			<div class="vicons"><a href="http://www.livestream.com/" title="<?php echo JText::_('livestream');?>" alt="<?php echo JText::_('livestream');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/livestream.png" /></a></div>
			<div class="vicons"><a href="http://www.mips.tv/" title="<?php echo JText::_('mips');?>" alt="<?php echo JText::_('mips');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/mips.png" /></a></div>
			<div class="vicons"><a href="http://www.mtv.com/" title="<?php echo JText::_('mtv');?>" alt="<?php echo JText::_('mtv');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/mtv.png" /></a></div>
			<div class="vicons"><a href="http://www.photobucket.com/" title="<?php echo JText::_('photobucket');?>" alt="<?php echo JText::_('photobucket');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/photobucket.png" /></a></div>
			<div class="vicons"><a href="http://www.twitch.tv/" title="<?php echo JText::_('twitch');?>" alt="<?php echo JText::_('twitch');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/twitch.png" /></a></div>
		</div>
	</div>
    <div class="clearfix">
    <div class="form-input form-input1"><input type="text" id="vLink" name="vLink" placeholder="<?php echo JText::_('Video URL');?>" /></div>
    </div>
		 <br />

		<input type="hidden" name="awd_video_title" id="awd_video_title" value="" />

		 <input type="hidden" name="awd_video_desc" id="awd_video_desc" value="" />

		

		<span class="wr_buton_at uploadvideo">

		<input type="button" value=" <?php echo JText::_('Attach Video');?>" class="button" onClick="ajaxVideoUpload('<?php echo JURI::base().'index.php?option=com_awdwall&task=addvideo&wuid=' . $wuid;?>')" />

		</span><a href="javascript:void(0);" onClick="closeVideoBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>

		<span style="display:none;padding:5px 0px !important;" id="video_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>

        </div>

<!-- end video form -->

<!-- image form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_image_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeImageBox();">x</a></span>
<br /><br />


		 <?php echo JText::_('Choose image to upload');?>:			 
    <div class="clearfix">
    <div class="form-input form-input1"><input type="text" id="awd_image_title" name="awd_image_title" placeholder="<?php echo JText::_('Image Name');?>" /></div>
    </div>
    <div class="clearfix">
    <div class="form-input form-input1"><input type="file" id="awd_link_image" name="awd_link_image" placeholder="<?php echo JText::_('Image');?>" />(<?php echo JText::sprintf('Image Ext Only', AwdwallHelperUser::getImageExt());?>)</div>
    </div>
    <div class="clearfix">
    <div class="form-input form-input1"><textarea id="awd_image_description" name="awd_image_description" cols="40" rows="4" placeholder="<?php echo JText::_('Image Description');?>" ></textarea></div>
    </div>



		 <input type="hidden" name="awd_mp3_desc" id="awd_mp3_desc" value="" />

		 <br />

		<span class="wr_buton_at uploadcamera">

		<input type="button" value=" <?php echo JText::_('Upload Image');?>" class="button" onClick="ajaxImageUpload('<?php echo 'index.php?option=com_awdwall&task=addimage&wuid=' . $wuid;?>')" />

		</span><a href="javascript:void(0);" onClick="closeImageBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>

		<span style="display:none;padding:5px 0px !important;" id="image_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>

        </div>

<!-- end image form -->

<!-- mp3 form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_mp3_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeMp3Box();">x</a></span>
<br /><br />
		 <?php echo JText::_('Choose mp3 to upload');?>:
    <div class="clearfix">
    <div class="form-input form-input1"><input type="text" name="awd_mp3_title" id="awd_mp3_title"  maxlength="150" placeholder="<?php echo JText::_('Title');?>"/></div>
    </div>
    <div class="clearfix">
    <div class="form-input form-input1"><input type="file"  name="awd_link_mp3" id="awd_link_mp3"  maxlength="150" placeholder="<?php echo JText::_('Url');?>"/></div>
    </div>
		 <input type="hidden" name="awd_mp3_desc" id="awd_mp3_desc" value="" />

		 <br />

		<span class="wr_buton_at uploadmp3">
		<input type="button" value=" <?php echo JText::_('Upload MP3');?>" class="button" onClick="ajaxMp3Upload('<?php echo JURI::base().'index.php?option=com_awdwall&task=addmp3&wuid=' . $wuid;?>')" />

		</span><a href="javascript:void(0);" onClick="closeMp3Box();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>
         <br /><br /><br />
		<hr />
		 <br /><?php echo JText::_(' OR ');?><a href="http://soundcloud.com/" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/soundcloud.gif" /></a><br /><br />
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_soundcloudurl" id="awd_soundcloudurl"   placeholder="<?php echo JText::_('Attach Sound Cloud');?>"/></div>
        </div>

         
<br /><br />

		<span class="wr_buton_at uploadmp3">
		<input type="button" value=" <?php echo JText::_('Attach');?>" class="button" onClick="ajaxsoundcloudUpload('<?php echo JURI::base().'index.php?option=com_awdwall&task=addsoundcloud&wuid=' . $wuid;?>')" />

		</span><a href="javascript:void(0);" onClick="closeMp3Box();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>
		<span style="display:none;padding:5px 0px !important;" id="mp3_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>

        </div>

<!-- end mp3 form -->

<!-- link form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_link_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeLinkBox();">x</a></span>
<br /><br />
	<?php // echo JText::_('Url');?>		 		
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_link" id="awd_link"   placeholder="<?php echo JText::_('Url');?>"/></div>
        </div>
		<!-- <p style="text-align:center;"><input type="text" name="awd_link" id="awd_link" value="http://" size="65" maxlength="150" class="input_border" /></p>-->

		 <input type="hidden" name="awd_link_title" id="awd_link_title" value="" />

		 <input type="hidden" name="awd_link_desc" id="awd_link_desc" value="" /> <br />

		<span class="wr_buton_at">

		<input type="button" value=" <?php echo JText::_('Attach Link');?>" class="button" onClick="ajaxLinkUpload('<?php echo 'index.php?option=com_awdwall&task=addlink&wuid=' . $wuid;?>')" />

		</span> <a href="javascript:void(0);" onClick="closeLinkBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a> 

<span style="display:none;padding:5px 0px !important;" id="link_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>        

        </div>

<!-- end link form -->
<!-- Jing form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_jing_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeJingBox();">x</a></span>

		 <?php echo JText::_('Jing screen and videos');?>:
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_jing_title" id="awd_jing_title"   placeholder="<?php echo JText::_('Title');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_jing_link" id="awd_jing_link"   placeholder="<?php echo JText::_('Url');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><textarea id="awd_jing_description" name="awd_jing_description"  placeholder="<?php echo JText::_('Description');?>"></textarea></div>
        </div>
<!--		 <p><label style="padding-right:17px;float:left;padding-top:10px;padding-right:57px;"><?php echo JText::_('Title');?> </label> <input type="text" name="awd_jing_title" id="awd_jing_title" class="input_border" size="48" maxlength="150" /></p>

		 <p><label style="padding-right:17px;float:left;padding-top:10px;padding-right:55px;"><?php echo JText::_('Url');?> </label><input type="text" id="awd_jing_link" name="awd_jing_link" size="50" class="input_border" /></p>
		 <p><label style="padding-right:17px;float:left;padding-top:10px;"><?php echo JText::_('Description');?> </label><textarea id="awd_jing_description" name="awd_jing_description" cols="40" rows="4" class="input_border"></textarea></p>
-->		 

<br />

		<span class="wr_buton_at">

		<input type="button" value=" <?php echo JText::_('Attach Jing');?>" class="button" onClick="ajaxJingUpload('<?php echo  JURI::base().'index.php?option=com_awdwall&task=addjing&wuid=' . $wuid;?>')" />
</span> <a href="javascript:void(0);" onClick="closeJingBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a> 

<span style="display:none;padding:5px 0px !important;" id="jing_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>        

        </div>

<!-- end Jings form -->

<!-- start event form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_event_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeEventBox();">x</a></span>
		<br /><br />
		 <?php echo JText::_('CHOOSE EVENT TO UPLOAD');?>:			 
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_event_title" id="awd_event_title"   placeholder="<?php echo JText::_('Title');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_event_location" id="awd_event_location"  placeholder="<?php echo JText::_('Location');?>"/><?php // echo JText::sprintf('E.g. San Jose, California ');?></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><?php echo JText::_('Start Time');?><?php $dt=date('Y'.'-'.'m'.'-'.'d');?>
				<input type="text" name="startdate" id="startdate" value="<?php echo $dt; ?>"/><?php echo $startHourSelect.'&nbsp;:&nbsp;'.$startMinSelect .' &nbsp; '.$startAmPmSelect;?></div>
        </div>
        <br />
        <div class="clearfix">
        <div class="form-input form-input1"><?php echo JText::_('End Time');?><input type="text" name="enddate" id="enddate" value="<?php echo $dt; ?>"/>&nbsp;<?php echo $endHourSelect.'&nbsp;:&nbsp;'.$endMinSelect .' &nbsp; ' .$endAmPmSelect;?></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><textarea id="awd_event_description" name="awd_event_description"  placeholder="<?php echo JText::_('Event Description');?>"></textarea></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="file" id="awd_event_image" name="awd_event_image"  placeholder="<?php echo JText::_('Image');?>" /></div>
        </div>
        <?php echo JText::_('E-mail');?>
        <div class="clearfix">
        <div class="form-input form-input1">
			<input type="radio" name="event_mail" id="event_mail" value="0" class="input_border" checked="checked" /><?php echo JText::_('No e-mail');?>
		 	<input type="radio" name="event_mail" id="event_mail" value="1" class="input_border" /><?php echo JText::_('Friends');?>
		 	<input type="radio" name="event_mail" id="event_mail" value="2" class="input_border" /><?php echo JText::_('Everyone');?>
        </div>
        </div>

		<!-- <p><label style="padding-right:78px;float:left;padding-top:10px;"><?php echo JText::_('Title');?></label> <input type="text" name="awd_event_title" id="awd_event_title" class="input_border" size="40" maxlength="150"/></p>
		 
		 <p>
		 	<label style="padding-right:55px;float:left;padding-top:10px;"><?php echo JText::_('Location');?></label> <input type="text" name="awd_event_location" id="awd_event_location" class="input_border" size="40" maxlength="150"/>
			<?php echo JText::sprintf('E.g. San Jose, California ');?>
		 </p>
		
		<p>
		 	<label style="padding-right:47px;float:left;padding-top:10px;"><?php echo JText::_('Start Time');?></label> 
			<span style="margin-top:5px; display:block">
				<?php $dt=date('Y'.'-'.'m'.'-'.'d');?>
				<input type="text" name="startdate" id="startdate" value="<?php echo $dt; ?>"/>&nbsp;<?php echo $startHourSelect.'&nbsp;:&nbsp;'.$startMinSelect .' &nbsp; '.$startAmPmSelect;?>
			</span>
		</p>

		<p>
		 	<label style="padding-right:50px;float:left;padding-top:10px;"><?php echo JText::_('End Time');?></label>
            <span style="margin-top:5px; display:block">
				<input type="text" name="enddate" id="enddate" value="<?php echo $dt; ?>"/>&nbsp;<?php echo $endHourSelect.'&nbsp;:&nbsp;'.$endMinSelect .' &nbsp; ' .$endAmPmSelect;?>
			</span>
		</p>

		 <p>
			 <label style="padding-right:7px;float:left;padding-top:10px;"><?php echo JText::_('Event Description');?></label>
	
			 <textarea id="awd_event_description" name="awd_event_description" cols="40" rows="4" class="input_border"></textarea>
	
		 </p>
		 
		 <p><label style="padding-right:70px;float:left;padding-top:10px;"><?php echo JText::_('Image');?> </label><input type="file" id="awd_event_image" name="awd_event_image" class="input_border" size="40" /><br />
		 
		 <p>
			<label style="padding-right:70px;float:left;padding-top:10px;"><?php echo JText::_('E-mail');?> </label>		 	
			<input type="radio" name="event_mail" id="event_mail" value="0" class="input_border" checked="checked" /><?php echo JText::_('No e-mail');?>
		 	<input type="radio" name="event_mail" id="event_mail" value="1" class="input_border" /><?php echo JText::_('Friends');?>
		 	<input type="radio" name="event_mail" id="event_mail" value="2" class="input_border" /><?php echo JText::_('Everyone');?>
		 </p>-->


		 <br />

		<span class="wr_buton_at uploadcamera">

		<input type="button" value=" <?php echo JText::_('Upload Event');?>" class="button" onClick="ajaxEventUpload('<?php echo 'index.php?option=com_awdwall&task=addevent&wuid=' . $wuid;?>')" />

		</span><a href="javascript:void(0);" onClick="closeEventBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>

		<span style="display:none;padding:5px 0px !important;" id="event_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>

        </div>
		

<!-- end event form -->

<!-- start article form -->

		<div class="attach_link clearfix message info " style="display:none;" id="awd_article_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeArticleBox();">x</a></span>
 <br /> <br />
		 <?php echo JText::_('CHOOSE ARTICLE TO UPLOAD');?>:			 
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_article_title" id="awd_article_title"   placeholder="<?php echo JText::_('ARTICLE TITLE');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1">
        <textarea id="awd_article_description" name="awd_article_description" placeholder="<?php echo JText::_('ARTICLE BODY');?>" ></textarea>
        </div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="file" id="awd_article_image" name="awd_article_image"  placeholder="<?php echo JText::_('Image');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><?php echo JText::_('Load Wall Comments');?>
        <select name="loadjomwall" id="loadjomwall">
				<option value="0"><?php echo JText::_('JNO');?></option>
				<option value="1"><?php echo JText::_('JYES');?></option>
		</select>
		</div>


        </div>
        <div class="clearfix">
        <div class="form-input form-input1">
        	<?php echo $lists['catid'];?>
		</div>
        </div>
		<!-- <p><label style="padding-right:98px;float:left;padding-top:10px;"><?php echo JText::_('Title');?></label> <input type="text" name="awd_article_title" id="awd_article_title" class="input_border" size="53" maxlength="150"/></p>
		
		 <p>
			 <label style="padding-right:25px;float:left;padding-top:10px;"><?php echo JText::_('Article Description');?></label>
	
			 <textarea id="awd_article_description" name="awd_article_description" cols="50" rows="8" class="input_border"></textarea>
	
		 </p>
		 
		 <p><label style="padding-right:90px;float:left;padding-top:10px;"><?php echo JText::_('Image');?> </label><input type="file" id="awd_article_image" name="awd_article_image" class="input_border" size="40" /></p><br />
		 
		 <p><label style="padding-right:10px;float:left;padding-top:2px;"><?php echo JText::_('Load Wall Comments');?> </label>
		 	<select name="loadjomwall" id="loadjomwall">
				<option value="0">No</option>
				<option value="1">Yes</option>
			</select>
		 </p>
<br />
		 <p style="float:left;"><label style="padding-right:39px;float:left;padding-top:2px;"><?php echo JText::_('Select Category');?> </label>
			<?php echo $lists['catid'];?>
		 </p>-->

		 <br /><br />

		<span class="wr_buton_at uploadcamera">

		<input type="button" value=" <?php echo JText::_('Upload Article');?>" class="button" onClick="ajaxArticleUpload('<?php echo 'index.php?option=com_awdwall&task=addarticle&wuid=' . $wuid;?>')" />

		</span><a href="javascript:void(0);" onClick="closeArticleBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>

		<span style="display:none;padding:5px 0px !important;" id="article_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>

        </div>
		

<!-- end article form -->

<!-- file form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_file_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeFileBox();">x</a></span>
 <br /> <br />

		<?php // echo JText::_('Choose file to upload');?>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" id="awd_file_title" name="awd_file_title"  placeholder="<?php echo JText::_('File Title');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="file" id="awd_file_link" name="awd_file_link"  placeholder="<?php echo JText::_('Url');?>"/>(<?php echo JText::sprintf('File Ext Only', AwdwallHelperUser::getFileExt());?></div>
        </div>
        
		<!--<p><label style="padding-right:10px;float:left;padding-top:10px;"><?php echo JText::_('Title');?> </label><input type="text" id="awd_file_title" name="awd_file_title" class="input_border" size="40" maxlength="150" /> </p>

		 <p><label style="padding-right:13px;float:left;padding-top:10px;"><?php echo JText::_('Url');?> </label><input type="file" id="awd_file_link" name="awd_file_link" class="input_border" size="40" /> (<?php echo JText::sprintf('File Ext Only', AwdwallHelperUser::getFileExt());?>)</p>-->

		 <input type="hidden" name="awd_mp3_desc" id="awd_mp3_desc" value="" />

		<br />

		<span class="wr_buton_at uploadfile">

		<input type="button" value=" <?php echo JText::_('Attach a File');?>" class="button" onClick="ajaxAwdFileUpload('<?php echo JURI::base().'index.php?option=com_awdwall&task=addfile&wuid=' . $wuid;?>')" />

		</span> <a href="javascript:void(0);" onClick="closeFileBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>  

<span style="display:none;padding:5px 0px !important;" id="file_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>         

        </div>

<!-- end file form -->
<!-- start Trail form -->
<div class="attach_link clearfix  message info" style="display:none;" id="awd_trail_form">

 <span class="close"> <a href="javascript:void(0);" onClick="closeTrailBox();">x</a></span>
 <br /> <br />
		 <?php echo '<strong>'.JText::_('Add Trip from EveryTail.com').'</strong>';?>&nbsp;&nbsp;<a href="http://www.everytrail.com/" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/everytrail_text_icon.jpg" /></a>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" id="awd_trail_title" name="awd_trail_title"  placeholder="<?php echo JText::_('Title');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" id="awd_trail_link" name="awd_trail_link"  placeholder="<?php echo JText::_('Url');?>"/></div>
        </div>

		

		 <br />

		<span class="wr_buton_at uploadtrail">

		<input type="button" value=" <?php echo JText::_('Add Trip');?>" class="button" onClick="ajaxTripUpload('<?php echo 'index.php?option=com_awdwall&task=addtrail&wuid=' . $wuid;?>')" />

		</span> <a href="javascript:void(0);" onClick="closeTrailBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a> 

<span style="display:none;padding:5px 0px !important;" id="trail_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>        

        </div>

<!-- end Trail form -->
<!-- pm form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_pm_form">

		 <span class="close"> <a href="javascript:void(0);" onclick="closePmBox();">x</a></span>

		 <?php echo JText::_('Private Message');?>		

		 <span style="display:none;" id="pm_loading"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>		

		 <p><label style="padding-right:7px;float:left;padding-top:10px;"><?php echo JText::_('Text');?></label>

		 <textarea id="awd_pm_description" name="awd_pm_description" cols="40" rows="4" class="input_border" ></textarea>

		 </p>

		<input type="hidden" name="awd_pm_receiver_id" id="awd_pm_receiver_id" value="<?php echo $wuid;?>"/>

		<br />

		<span class="">

		<a href="javascript:void(0);" onclick="ajaxPmUpload('<?php echo 'index.php?option=com_awdwall&task=addpmuser&wuid=' . $wuid;?>');" class="button_cancel"> <span>&nbsp;&nbsp;&nbsp;<?php echo JText::_('PM');?>&nbsp;&nbsp;&nbsp;</span></a>

		</span> <a href="javascript:void(0);" onclick="closePmBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>        
	</div>

<!-- end pm form -->
        <input type="hidden" id="wid_tmp" name="wid_tmp" value="" />
        <input type="hidden" id="type" name="type" value="text" />
      </form>
      <br />
      <div class="fullboxnew" >
        <!-- start msg content -->
        <span id="msg_loader"></span>
        <div id="msg_content">
          <!-- start block msg -->
          <?php 
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
$display_profile_link 		= $config->get('display_profile_link', 1);
$fbshareaapid= $config->get('fbshareaapid', '');
	
if(isset($msgs[0]) && $showPosts){
	$n = count($msgs);
	for($i = 0; $i < $n; $i++){
		
		$pmText = '';
		if((int)$msgs[$i]->is_pm && ((int)$usercurrent->id == (int)$msgs[$i]->user_id || (int)$usercurrent->id == (int)$msgs[$i]->commenter_id)){
			$pmText = '<div class="pm_text">' . JText::_('PM From') . ' </div>';
		}else{
			$pmText = '';
		}
		
		$video = null;
		$pageTitle = $msgs[$i]->message;
		if($msgs[$i]->type == 'video'){
			$video = $wallModel->getVideoInfoByWid($msgs[$i]->id);
			$pageTitle = $video->title;
		}
		$image = null;
		if($msgs[$i]->type == 'image'){
			$image = $wallModel->getImageInfoByWid($msgs[$i]->id);
			$pageTitle = $image->name;
		}
		$mp3 = null;
		if($msgs[$i]->type == 'mp3'){
			$mp3 = $wallModel->getMp3InfoByWid($msgs[$i]->id);
			$pageTitle = $mp3->title;
		}
		$link = null;
		if($msgs[$i]->type == 'link'){
			$link = $wallModel->getLinkInfoByWid($msgs[$i]->id);
			$pageTitle = $link->title;
		}
		$file = null;
		if($msgs[$i]->type == 'file'){
			$file  = $wallModel->getFileInfoByWid($msgs[$i]->id);
			$pageTitle = $file ->title;
		}
		$trail=null;
		if($msgs[$i]->type == 'trail'){
			$trail  = $wallModel->getTrailInfoByWid($msgs[$i]->id);
			$pageTitle = $trail ->trail_title;
		}
		
		if($msgs[$i]->type == 'jing'){
			$jing  = $wallModel->getJingInfoByWid($msgs[$i]->id);
			$pageTitle = $jing ->jing_title;
		}
		if($msgs[$i]->type == 'event'){
			$event  = $wallModel->getEventInfoByWid($msgs[$i]->id);
			$pageTitle = $event ->title;
		}
		
		$article  = null;
		if($msgs[$i]->type == 'article'){
			$article  = $wallModel->getArticleInfoByWid($msgs[$i]->id);
			$pageTitle = $article ->title;
		}
		$pageTitle = addslashes(htmlspecialchars($pageTitle));
		$pageTitle = str_replace(chr(13), " ", $pageTitle); //remove carriage returns
		$pageTitle = str_replace(chr(10), " ", $pageTitle); //remove line feeds 
		
		
	if($display_profile_link==1)
	{
	
		$profilelink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' .$msgs[$i]->commenter_id. '&Itemid=' .$cbItemid, false);
		$rprofilelink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' .$msgs[$i]->user_id. '&Itemid=' .$cbItemid, false);
	}
	else
	{
		$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $msgs[$i]->commenter_id.'&Itemid=' . $Itemid, false);
		$rprofilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $msgs[$i]->user_id.'&Itemid=' . $Itemid, false);
	}
		
		// check rightcolumn css
		$rightColumnCss = 'rbroundboxright';
		$aPostB = false;
		$showLeft = true;
		if((int)$msgs[$i]->commenter_id != (int)$msgs[$i]->user_id && (int)$msgs[$i]->commenter_id == $wuid && !in_array($task, $arrTask) && !(int)$msgs[$i]->is_pm){
			$aPostB = true;
			$rightColumnCss = 'rbroundboxrightfull';
		}
		// check if viewer is member of this group
		$vowner = $groupModel->checkGrpOwner($wuid, $msgs[$i]->group_id);
		if((int)$vowner){
			$aPostB = true;
			$rightColumnCss = 'rbroundboxrightfull';
		}
		if($msgs[$i]->group_id != NULL && ($wuid == $msgs[$i]->commenter_id)){
			$aPostB = true;
			$rightColumnCss = 'rbroundboxrightfull';
		}
		$group = $groupModel->getGroupInfo($msgs[$i]->group_id);
		$likeTxt = '';
		
		if($msgs[$i]->type == 'like'){
			$likeTxt = JText::sprintf('A LIKES B POST', '<a href="' . $profilelink . '">' . AwdwallHelperUser::getDisplayName($msgs[$i]->commenter_id) . '</a>', $rprofilelink, AwdwallHelperUser::getDisplayName($msgs[$i]->user_id));
		}
		if($msgs[$i]->type == 'friend'){ 
		$likeTxt = JText::sprintf('A AND B ARE FRIEND', '<a href="' . $profilelink . '">' . AwdwallHelperUser::getDisplayName($msgs[$i]->commenter_id) . '</a>',  '<a href="' . $rprofilelink . '">' . AwdwallHelperUser::getDisplayName($msgs[$i]->user_id) . '</a>');		
	}
	if($msgs[$i]->type == 'group'){ 
		$likeTxt = JText::sprintf('JOIN GROUP NEWS FEED', '<a href="' . $profilelink . '">' . AwdwallHelperUser::getDisplayName($msgs[$i]->commenter_id) . '</a>',  '<a href="' . JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $msgs[$i]->group_id . '&Itemid=' . $Itemid, false) . '">' . $group->title . '</a>');
	}
	
	//filter video
	if($_GET['task'] == 'videos'){
		if($rightColumnCss == 'rbroundboxrightfull'){
			continue;
		}
	}
	
	
?>
          <a name="here<?php echo $msgs[$i]->id;?>"></a>
          <div class="awdfullbox clearfix" id="msg_block_<?php echo $i;?>"><span class="tl"></span><span class="bl"></span>
            <?php if(!$aPostB){ ?>
            <div class="rbroundboxleft">
              <div class="mid_content"><a href="<?php echo $profilelink;?>"> <img src="<?php echo AwdwallHelperUser::getBigAvatar51($msgs[$i]->commenter_id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($msgs[$i]->commenter_id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($msgs[$i]->commenter_id);?>" height="50" width="50" class="awdpostavatar" />
                <?php 
	if(AwdwallHelperUser::isSocialfeed($msgs[$i]->id)) 
	{?>
		<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo AwdwallHelperUser::isSocialfeed($msgs[$i]->id); ?>.png" class="post_type_icon"  />
	<?php }elseif(AwdwallHelperUser::isTweet($msgs[$i]->id)) {	?>
    		<img src="<?php echo JURI::base();?>components/com_awdwall/images/twitter.png" class="post_type_icon"  />
<?php } ?>
                </a> <br />
                <?php if($display_profile_link==1):?>
                <a style="font-size:11px;" href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Main wall profile');?></a>
                <?php endif; ?>
              </div>
            </div>
            <?php }?>
            <div class="<?php echo $rightColumnCss;?>"><span class="tl2"></span><span class="bl2"></span><span class="tr2"></span><span class="br2"></span>
              <div class="right_mid_content"> <?php echo $pmText;?>
                <ul class="walltowall">
                  <?php if($aPostB && $msgs[$i]->group_id == NULL){ ?>
                  <li>
                    <?php 
	$textWall = '';
	switch($msgs[$i]->type){
		case 'text':
			$textWall = 'A WRITE ON B WALL';
			break;
		case 'jing':
				$textWall = 'A ADDED JING ON B WALL';
			break;
		case 'event':
				$textWall = 'A ADDED EVENT ON B WALL';
			break;
		case 'image';
			$textWall = 'A ADDED PHOTO ON B WALL';
			break;
		case 'video';
			$textWall = 'A ADDED VIDEO ON B WALL';
			break;
		case 'trail';
			$textWall = 'A ADDED TRAIL ON B WALL';
			break;
		case 'link';
			$textWall = 'A ADDED LINK ON B WALL';
			break;
		case 'file';
			$textWall = 'A ADDED FILE ON B WALL';
			break;
		case 'mp3';
			$textWall = 'A ADDED SONG ON B WALL';
			break;
		case 'like':
			echo $likeTxt;
			break;
		case 'friend':
			echo $likeTxt;
			break;
		case 'group':
			echo $likeTxt;
			break;
		case 'tag':
					echo '<a style="font-size:12px;" href="' . $profilelink . '" >' . AwdwallHelperUser::getDisplayName($msgs[$i]->commenter_id) . '</a>&nbsp;&nbsp;';
echo $msgs[$i]->message;
			break;
	}
	echo JText::sprintf($textWall, '<a style="font-size:12px;" href="' . $profilelink . '" >' . AwdwallHelperUser::getDisplayName($msgs[$i]->commenter_id) . '</a>', '<a style="font-size:12px;" href="' . $rprofilelink . '">' . AwdwallHelperUser::getDisplayName($msgs[$i]->user_id) . "'s" . '</a>');
	?>
                  </li>
                  <!--li><a style="font-size:12px;" href="<?php //echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false);?>" class="john"><?php //echo AwdwallHelperUser::getDisplayName($msgs[$i]->commenter_id);?></a></li>
	<li><a style="font-size:12px;" href="<?php //echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $msgs[$i]->user_id . '&Itemid=' . $Itemid, false);?>"><?php //echo AwdwallHelperUser::getDisplayName($msgs[$i]->user_id);?></a> &nbsp;&nbsp;<?php //echo AwdwallHelperUser::showSmileyicons($msgs[$i]->message);?></li-->
                  <?php }else{ 
if(!(int)$msgs[$i]->group_id){
	if($msgs[$i]->type == 'like' || $msgs[$i]->type == 'friend' || $msgs[$i]->type == 'group'){
		echo '<li>' . $likeTxt . '</li>';
	}else{
	if(!(int)$msgs[$i]->is_pm){
?>
                  <li><a style="font-size:12px;" href="<?php echo $profilelink;?>"><?php echo AwdwallHelperUser::getDisplayName($msgs[$i]->commenter_id);?></a>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($msgs[$i]->message);?></span></li>
                  <?php }else{
	if($msgs[$i]->commenter_id != $msgs[$i]->user_id && $wuid == $msgs[$i]->commenter_id){
	?>
                  <li><a style="font-size:12px;" href="<?php echo $profilelink;?>" class="john"><?php echo AwdwallHelperUser::getDisplayName($msgs[$i]->commenter_id);?></a></li>
                  <li><a style="font-size:12px;" href="<?php echo $rprofilelink;?>"><?php echo AwdwallHelperUser::getDisplayName($msgs[$i]->user_id);?></a> &nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($msgs[$i]->message);?></span></li>
                  <?php }else{?>
                  <li><a style="font-size:12px;" href="<?php echo $rprofilelink;?>"><?php echo AwdwallHelperUser::getDisplayName($msgs[$i]->commenter_id);?></a>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($msgs[$i]->message);?></span></li>
                  <?php }?>
                  <?php }?>
                  <?php } }else{
if($msgs[$i]->type == 'group'){
	echo '<li>' . $likeTxt . '</li>';
	}else{
?>
                  <li><?php echo JText::sprintf('A WRITE ON B GROUP', '<a style="font-size:12px;" href="' . $profilelink . '" >' . AwdwallHelperUser::getDisplayName($msgs[$i]->commenter_id) . '</a>', '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $msgs[$i]->group_id . '&Itemid=' . $Itemid, false) . '" >' . $group->title . '</a>');?></li>
                  <?php }}?>
                  <?php }?>
                </ul>
                <div class="commentinfo"> 
                  <!-- start link and video of text -->
                  <?php if($msgs[$i]->type == 'text' && !$aPostB){?>
                  <?php
				$query = "SELECT * FROM #__awd_wall_videos WHERE wall_id='".$msgs[$i]->id."'";
				$db->setQuery($query);
				$linkvideo = $db->loadObjectList();
				$video = $linkvideo[0];
			?>
                  <?php if($video->id){?>
                  <?php //if($msgs[$i]->type == 'video'){?>
                  <div class="whitebox video">
                    <div style="overflow:hidden;" id="video_block_<?php echo $msgs[$i]->id;?>">
                      <div class="clearfix">
                        <div class="imagebox">
                          <?php
				if($hwdvideoshare)
				{
					$query = "SELECT hwdviodeo_id FROM #__awd_wall_videos_hwd WHERE wall_id='".$msgs[$i]->id."'";
					$db->setQuery($query);
					$hwdviodeo_id = $db->loadResult();
					if($hwdviodeo_id)
					{
					$hwdlink=JRoute::_('index.php?option=com_hwdvideoshare&task=viewvideo&video_id='.$hwdviodeo_id.'&Itemid='.$Itemid);	
					?>
                          <a href="<?php echo $hwdlink;?>"  title="" target="_self"><img src="<?php echo JURI::base();?>images/<?php echo $video->thumb; ?>" title="<?php echo $video->title; ?>" alt="<?php echo $video->title; ?>"  /></a>
                          <?php
					}
					else
					{
					?>
                          <img src="<?php echo JURI::base();?>images/<?php echo $video->thumb; ?>" title="<?php echo $video->title; ?>" alt="<?php echo $video->title; ?>"  />
                          <?php
					}
				}
				else
				{
				?>
                          <img src="<?php echo JURI::base();?>images/<?php echo $video->thumb; ?>" title="<?php echo $video->title; ?>" alt="<?php echo $video->title; ?>"  />
                          <?php 
				} 
				?>
                          <br />
                          <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-videos.png" title="<?php echo JText::_('View video');?>" alt="<?php echo JText::_('View video');?>" />
                          <?php 
					if($videoLightbox){
		if($video->type == 'youtube'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://www.youtube.com/watch?v=<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'vimeo'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://vimeo.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php		
		}elseif($video->type == 'myspace'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://myspace.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'metacafe'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://metacafe.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'howcast'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://howcast.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
	}else if($video->type == 'blip'){
?>
<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://a.blip.tv/scripts/flash/stratos.swf#file=http://blip.tv/rss/flash/<?php echo $video->video_id;?>&autostart=true&showinfo=false', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
}
else
{
?>
<a href="<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $msgs[$i]->id;?>&iframe=true&tmpl=component" rel="prettyPhoto[iframes]" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
}
	}else{
		if($video->type == 'youtube'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://www.youtube.com/watch?v=<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'vimeo'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://vimeo.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php		
		}elseif($video->type == 'myspace'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://myspace.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'metacafe'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://metacafe.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'howcast'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://howcast.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
	}else if($video->type == 'blip'){
?>
<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://a.blip.tv/scripts/flash/stratos.swf#file=http://blip.tv/rss/flash/<?php echo $video->video_id;?>&autostart=true&showinfo=false', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
}
else
{
?>
<a href="<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $msgs[$i]->id;?>&iframe=true&tmpl=component" rel="prettyPhoto[iframes]" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
}
	}
				?>
                        </div>
                        <div class="maincomment">
                          <h3><?php echo $video->title;?></h3>
                          <p><?php echo substr($video->description,0,200);?></p>
                          <font color="#9997ac"><?php echo JText::_('Length');?>: </font><?php echo AwdwallHelperUser::formatDuration((int)($video->duration), 'HH:MM:SS');?> </div>
                      </div>
                      <div style="width:455px; float: left; margin-top: 16px;" id="video_<?php echo $msgs[$i]->id;?>"></div>
                    </div>
                  </div>
                  <?php } ?>
                  <!-- start link block -->
                  <?php 
				$query = "SELECT * FROM #__awd_wall_links WHERE wall_id='".$msgs[$i]->id."'";
				$db->setQuery($query);
				$checklink = $db->loadObjectList();
				$link = $checklink[0];
			?>
                  <?php if($link->id){?>
                  <div class="whitebox video">
                    <div id="video_block_<?php echo $msgs[$i]->id;?>">
                      <div class="clearfix">
                        <?php if($link->path != ''){?>
                        <div class="imagebox"> <img src="<?php echo JURI::base();?>images/<?php echo $msgs[$i]->user_id;?>/thumb/<?php echo $link->path;?>" title="<?php echo $link->title;?>" alt="<?php echo $link->description;?>"  /> <br />
                          <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" />
                          <?php if($imageLightbox){ ?>
                          <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>images/<?php echo $msgs[$i]->user_id;?>/original/<?php echo $link->path;?>', '<?php echo $link->name;?>', '<?php echo $link->description;?>');" title="<?php echo $link->name;?>"><?php echo JText::_('View link');?></a>
                          <?php } else echo '<a href="' . JRoute::_('index.php?option=com_awdwall&task=viewimage&wuid=' . $msgs[$i]->user_id . '&imageid=' . $link->id . '&Itemid=' . $Itemid) . '" >' . JText::_('View link') . '</a>'; ?>
                        </div>
                        <div class="maincomment">
                          <h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->title;?></a></h3>
                          <h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->link;?></a></h3>
                          <p><?php echo $link->description;?></p>
                        </div>
                        <?php }else{ ?>
                        <div class="maincomment_noimg">
                          <?php if($link->link_img){?>
                          <div class="maincomment_noimg_left"> <a target="blank" href="<?php echo $link->link;?>" alt=""><img src="<?php echo $link->link_img;?>" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /></a> </div>
                          <?php } ?>
                          <div class="maincomment_noimg_right">
                            <h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->title;?></a></h3>
                            <h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->link;?></a></h3>
                            <p><?php echo $link->description;?></p>
                          </div>
                          <?php if($link->title){?>
                          <div style=" margin-top:3px; width:100%; clear:both;">
                            <div style="float:left; width:5%;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('View link');?>" align="absmiddle" alt="<?php echo JText::_('View link');?>" style="height:20px; width:20px; clear:right;" /></div>
                            <div style=" float:left; margin-left:5px; width:90%;"><a href="<?php echo $link->link;?>" target="blank" style="margin:0px; padding:0; font-size:9px; font-weight:normal;"><?php echo $link->title;?></a></div>
                          </div>
                          <?php } ?>
                        </div>
                        <?php }?>
                      </div>
                    </div>
                  </div>
                  <?php }?>
                  <!-- end link block -->
                  <?php } ?>
                  <!-- end link and video of text -->
                  <!-- start video block -->
                  <?php if($msgs[$i]->type == 'video' && !$aPostB){?>
                  <div class="whitebox video">
                    <div style="overflow:hidden;" id="video_block_<?php echo $msgs[$i]->id;?>">
                      <div class="clearfix">
                        <div class="imagebox">
                          <?php
if($hwdvideoshare)
{
	$query = "SELECT hwdviodeo_id FROM #__awd_wall_videos_hwd WHERE wall_id='".$msgs[$i]->id."'";
	$db->setQuery($query);
	$hwdviodeo_id = $db->loadResult();
	if($hwdviodeo_id)
	{
	$hwdlink=JRoute::_('index.php?option=com_hwdvideoshare&task=viewvideo&video_id='.$hwdviodeo_id.'&Itemid='.$Itemid);	
	?>
                          <a href="<?php echo $hwdlink;?>"  title="" target="_self"><img src="<?php echo JURI::base();?>images/<?php echo $video->thumb; ?>" title="<?php echo $video->title; ?>" alt="<?php echo $video->title; ?>"  /></a>
                          <?php
	}
	else
	{
	?>
                          <img src="<?php echo JURI::base();?>images/<?php echo $video->thumb; ?>" title="<?php echo $video->title; ?>" alt="<?php echo $video->title; ?>"  />
                          <?php
	}
}
else
{
?>
                          <img src="<?php echo JURI::base();?>images/<?php echo $video->thumb; ?>" title="<?php echo $video->title; ?>" alt="<?php echo $video->title; ?>"  />
                          <?php 
} 
?>
                          <br />
                          <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-videos.png" title="<?php echo JText::_('View video');?>" alt="<?php echo JText::_('View video');?>" />
                          <?php 
	if($videoLightbox){
		if($video->type == 'youtube'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://www.youtube.com/watch?v=<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'vimeo'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://vimeo.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php		
		}elseif($video->type == 'myspace'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://myspace.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'metacafe'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://metacafe.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'howcast'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://howcast.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
	}else if($video->type == 'blip'){
?>
<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://a.blip.tv/scripts/flash/stratos.swf#file=http://blip.tv/rss/flash/<?php echo $video->video_id;?>&autostart=true&showinfo=false', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
}
else
{
?>
<a href="<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $msgs[$i]->id;?>&iframe=true&tmpl=component" rel="prettyPhoto[iframes]" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
}
	}else{	
		if($video->type == 'youtube'){
?>
	<a class="show_vide" rev="video_<?php echo $msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.youtube.com/v/<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
	
<?php
		}elseif($video->type == 'vimeo'){	
?>
	<a  class="show_vide" rev="video_<?php echo $msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://vimeo.com/moogaloop.swf?clip_id=<?php echo $video->video_id?>&amp;autoplay=1" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php		
		}elseif($video->type == 'myspace'){
?>	
	<a  class="show_vide" rev="video_<?php echo $msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://mediaservices.myspace.com/services/media/embed.aspx/m=<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'metacafe'){
?>	
	<a  class="show_vide" rev="video_<?php echo $msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.metacafe.com/fplayer/<?php echo $video->video_id;?>.swf" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'howcast'){
?>	
	<a  class="show_vide" rev="video_<?php echo $msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.howcast.com/flash/howcast_player.swf?file=<?php echo $video->video_id;?>&amp;theme=black" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'blip'){
?>	
	<a  class="show_vide" rev="video_<?php echo $msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://a.blip.tv/scripts/flash/stratos.swf#file=http://blip.tv/rss/flash/<?php echo $video->video_id;?>&autostart=true&showinfo=false" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}else
		{
	?>
<a href="javascript:void(0);" onclick="showlinevideo('<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $msgs[$i]->id;?>&tmpl=component','<?php echo $msgs[$i]->id;?>')" ><?php echo JText::_('View video');?></a>
    
    <?php	
		
		}	
			
	
	}
?>
                        </div>
                        <div class="maincomment">
                          <h3><?php echo $video->title;?></h3>
                          <p><?php echo substr($video->description,0,200);?></p>
                          <font color="#9997ac"><?php echo JText::_('Length');?>: </font><?php echo AwdwallHelperUser::formatDuration((int)($video->duration), 'HH:MM:SS');?></div>
                      </div>
                      <div style="width:426px; float: left; margin-top: 16px;" id="video_<?php echo $msgs[$i]->id;?>"></div>
                    </div>
                  </div>
                  <?php }?>
                  <!-- end video block -->
                  <!-- start image block -->
                  <?php if($msgs[$i]->type == 'image' && !$aPostB){?>
                  <div class="whitebox video">
                    <div id="video_block_<?php echo $msgs[$i]->id;?>">
                      <div class="clearfix">
                        <div class="imagebox">
                          <?php 
	$pid=$image->id;
	$image_uid=$msgs[$i]->user_id;
	//$imglink=JRoute::_('index.php?option=com_awdjomalbum&view=awdwallimagelist&wuid='.$image_uid.'&pid='.$pid.'&Itemid='.$Itemid,false);	//echo $imglink;
	$imglink=JRoute::_("index.php?option=com_awdjomalbum&view=awdwallimagelist&tmpl=component&wuid=".$image_uid."&pid=".$pid."&Itemid=".AwdwallHelperUser::getComItemId());



	if($jomalbumexist)
	{
	?>
                          <a href="<?php echo $imglink;?>" class="awdiframe"><img src="<?php echo JURI::base();?>images/<?php echo $msgs[$i]->user_id;?>/thumb/<?php echo $image->path;?>" title="<?php echo $image->name;?>" alt="<?php echo $image->name;?>" /></a>
                          <?php }else{?>
            <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>images/<?php echo $msgs[$i]->user_id;?>/original/<?php echo $image->path;?>', '<?php echo $image->name;?>', '<?php echo $image->description;?>');" title="<?php echo $image->name;?>">
				<img src="<?php echo JURI::base();?>images/<?php echo $msgs[$i]->user_id;?>/thumb/<?php echo $image->path;?>" title="<?php echo $image->name;?>" alt="<?php echo $image->name;?>" /> <br /></a>
                          <?php }?>
                        </div>
                        <div class="maincomment">
                          <h3><?php echo $image->name;?></h3>
                          <p><?php echo $image->description;?></p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php }?>
                  <!-- end image block -->
                  <!-- start link block -->
                  <?php if($msgs[$i]->type == 'link' && !$aPostB){?>
                  <div class="whitebox video">
                    <div id="video_block_<?php echo $msgs[$i]->id;?>">
                      <div class="clearfix">
                        <?php if($link->path != ''){?>
                        <div class="imagebox"><img src="<?php echo JURI::base();?>images/<?php echo $msgs[$i]->user_id;?>/thumb/<?php echo $link->path;?>" title="<?php echo $link->title;?>" alt="<?php echo $link->description;?>"  /> <br />
                          <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-images.png" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" />
                          <?php 
	if($imageLightbox){
?>
                          <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>images/<?php echo $msgs[$i]->user_id;?>/original/<?php echo $link->path;?>', '<?php echo $link->name;?>', '<?php echo $link->description;?>');" title="<?php echo $link->name;?>"><?php echo JText::_('View link');?></a>
                          <?php
	}else
		echo '<a href="' . JRoute::_('index.php?option=com_awdwall&task=viewimage&wuid=' . $msgs[$i]->user_id . '&imageid=' . $link->id . '&Itemid=' . $Itemid, false) . '" >' . JText::_('View link') . '</a>';
?>
                        </div>
                        <div class="maincomment">
                          <h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->title;?></a></h3>
                          <h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->link;?></a></h3>
                          <p><?php echo $link->description;?></p>
                        </div>
                        <?php }else{ ?>
                        <div class="maincomment_noimg">
                          <?php if($link->link_img){?>
                          <div class="maincomment_noimg_left"> <a target="blank" href="<?php echo $link->link;?>" alt=""><img src="<?php echo $link->link_img;?>" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /></a> </div>
                          <?php } ?>
                          <div class="maincomment_noimg_right">
                            <h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->title;?></a></h3>
                            <h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->link;?></a></h3>
                            <p><?php echo $link->description;?></p>
                          </div>
                          <?php if($link->title){?>
                          <div style=" margin-top:3px; width:100%; clear:both;">
                            <div style="float:left; width:5%;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('View link');?>" align="absmiddle" alt="<?php echo JText::_('View link');?>" style="height:20px; width:20px; clear:right;" /></div>
                            <div style=" float:left; margin-left:5px; width:90%;"><a href="<?php echo $link->link;?>" target="blank" style="margin:0px; padding:0; font-size:9px; font-weight:normal;"><?php echo $link->title;?></a></div>
                          </div>
                          <?php } ?>
                        </div>
                        <?php }?>
                      </div>
                    </div>
                  </div>
                  <?php }?>
                  <!-- end link block -->
                  <!-- start mp3 block -->
                  <?php if($msgs[$i]->type == 'mp3' && !$aPostB){
					$parsedVideoLink	= parse_url($mp3->path);
					preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $parsedVideoLink['host'], $matches);
					$domain		= $matches['domain'];
					
					if($domain!='soundcloud.com')
					{
				  ?>
                  <div class="whitebox video">
                    <div id="video_block_<?php echo $msgs[$i]->id;?>">
                      <div class="clearfix">
                        <div class="title-mus"> <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-music.png" title="<?php echo JText::_('Music');?>" alt="<?php echo JText::_('Music');?>" style="float:left;" />
                          <h3 style="font-size:13px;font-weight:bold;margin:0;padding:3px 0px 6px 5px;"> &nbsp;<?php echo $mp3->title;?></h3>
                        </div>
                        <div class="imagebox">
                          <object width="200" height="24" id="audioplayer1" data="<?php echo JURI::base();?>components/com_awdwall/js/player.swf" type="application/x-shockwave-flash">
                            <param value="<?php echo JURI::base();?>components/com_awdwall/js/player.swf" name="movie">
                            <param value="playerID=1&amp;soundFile=<?php echo JURI::base();?>images/mp3/<?php echo $msgs[$i]->user_id;?>/<?php echo $mp3->path;?>" name="FlashVars">
                            <param value="high" name="quality">
                            <param value="true" name="menu">
                            <param value="transparent" name="wmode">
                          </object>
                          <br />
                        </div>
                        <div class="maincomment"> </div>
                      </div>
                    </div>
                  </div>
<?php
		}
		else
		{
			$videoWidth='400' ;
			$player_height='81';
			$auto_play='false';
			$show_comments='false';
			$color='#ff7700';
			$theme_color='#CCCCCC';
			$url=urlencode($mp3->path);
			$embed = '<object height="'.$player_height.'" width="'.$videoWidth.'">
			<param name="movie" value="http://player.soundcloud.com/player.swf?url='.$url.'&amp;g=bb&amp;auto_play='.$auto_play.'&amp;show_comments='.$show_comments.'&amp;color='.$color.'&amp;theme_color='.$theme_color.'">
			</param>
			<param name="allowscriptaccess" value="always">
			</param>
			<embed allowscriptaccess="always" height="'.$player_height.'" src="http://player.soundcloud.com/player.swf?url='.$url.'&amp;g=bb&amp;auto_play='.$auto_play.'&amp;show_comments='.$show_comments.'&amp;color='.$color.'&amp;theme_color='.$theme_color.'" type="application/x-shockwave-flash" width="'.$videoWidth.'">
			</embed>
			</object>';
		echo $embed;
		}
?>
                  <?php }?>
                  <!-- end mp3 block -->
                  <!-- start of jing block -->
                  <?php 
if($msgs[$i]->type == 'jing' && !$aPostB)
{
?>
                  <?php /*?><script language="javascript">
getJingData('<?php echo $jing->id;?>');
</script>
<div id="jing<?php echo $jing->id;?>"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" title="<?php echo JText::_('Loading');?>" alt="<?php echo JText::_('Loading');?>" border="0" /></div>
<br />
<a href="<?php echo $jing->jing_link;?>" title="<?php echo $jing->jing_title;?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-jing.png" title="<?php echo JText::_('Jing');?>" alt="<?php echo JText::_('Jing');?>" border="0" height="14" />&nbsp;&nbsp;&nbsp;<?php echo JText::_('View Jing');?></a>
<?php */?>
                  <div class="whitebox video">
                    <div style="overflow:hidden;" id="jing_block_<?php echo $msgs[$i]->id;?>">
                      <div class="clearfix">
                        <div class="imagebox" >
                          <script language="javascript">
			getJingThumbData('<?php echo $jing->id;?>');
			</script>
                          <div id="sceenthumb_<?php echo $jing->id;?>">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></div>
                          <br />
                          <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-jing.png" title="<?php echo JText::_('View Jing');?>" alt="<?php echo JText::_('View Jing');?>" />
                          <?php 
	if($jingLightbox){
?>
                          <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo $jing->jing_link;?>', '<?php echo $jing->jing_title;?>', '<?php echo $jing->jing_title;?>')" title="<?php echo $jing->jing_title;?>"><?php echo JText::_('View Jing');?></a>
                          <?php
	}else{	
?>
                          <a class="show_vide"  alt="<?php echo $jing->jing_title;?>" onclick="showjing(<?php echo $jing->id;?>);" href="javascript:void(0);" title="<?php echo $jing->jing_title;?>"><?php echo JText::_('View Jing');?></a>
                          <?php
	}
?>
                        </div>
                        <div class="maincomment">
                          <h3><?php echo $jing->jing_title;?></h3>
                          <h3><a href="<?php echo $jing->jing_link;?>" target="blank"><?php echo $jing->jing_link;?></a></h3>
                          <p><?php echo nl2br(substr($jing->jing_description,0,200));?></p>
                        </div>
                      </div>
                      <div style="width:455px; float: left; margin-top: 16px;display:none;" id="jingp_<?php echo $jing->id;?>" > &nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /> </div>
                    </div>
                  </div>
                  <?php 
}
?>
                  <!-- end of jing block -->
                  <!-- start event block -->
                  <?php if($msgs[$i]->type == 'event' && !$aPostB)
{
$startd=explode("\n",$event->start_time);
$endd=explode("\n",$event->end_time);
$starttime=$startd[1].' '.$startd[2].' '.date('l, j-M-y',strtotime($startd[0]));
$endtime=$endd[1].' '.$endd[2].' '.date('l, j-M-y',strtotime($endd[0]));
?>
                  <div class="whitebox event">
                    <div id="event_block_<?php echo $msgs[$i]->id;?>">
                      <div class="clearfix">
                        <div class="imagebox" style="margin-top:10px; margin-bottom:10px;">
                          <?php
	if($event->image)
	{?>
                          <img src="<?php echo JURI::base();?>images/awd_events/<?php echo $msgs[$i]->user_id;?>/thumb/<?php echo $event->image;?>" title="<?php echo $event->title;?>" alt="<?php echo $event->title;?>" /></a>
                          <?php }
	else {?>
                          <img src="<?php echo JURI::base();?>components/com_awdwall/images/event.png" title="<?php echo $event->title;?>" alt="<?php echo $event->title;?>" />
                          <?php }?>
                        </div>
                        <?php
	
	?>
                        <div class="maincomment" style="margin-top:10px;">
                          <h3><?php echo $event->title;?></h3>
                          <p><font><?php echo JText::_('Location: ');?></font><?php echo $event->location;?></p>
                          <p><font><?php echo JText::_('Time: ');?></font></p>
                          <p><font><?php echo JText::_('Start: ');?></font><?php echo $starttime;?><br />
                            <font><?php echo JText::_('End: ');?>&nbsp;</font><?php echo $endtime;?></p>
                          <p><?php echo nl2br($event->description);?></p>
                          <?php
	$canAttend = $wallModel->getEventOfMsgOfUser($msgs[$i]->id,$usercurrent->id);
	
	if(!$canAttend){
?>
                          <p><?php echo JText::_('Are you coming ?');?>
                            <select name="attend_event" id="attend_event" onchange="attendEvent('<?php echo 
 'index.php?option=com_awdwall&view=awdwall&task=addEventAttend&wid=' . (int)$msgs[$i]->id . '&cid=' .(int)$msgs[$i]->commenter_id.'&eventId=' . (int)$event->id .'&tmpl=component';?>', '<?php echo  'index.php?option=com_awdwall&view=awdwall&task=getEventAttend&wid=' . (int)$msgs[$i]->id . '&tmpl=component&Itemid='.$Itemid;?>',<?php echo $msgs[$i]->id;?>);">
                              <option value=""><?php echo JText::_('Select');?></option>
                              <option value="1"><?php echo JText::_('Yes');?></option>
                              <option value="0"><?php echo JText::_('No');?></option>
                            </select>
                          </p>
                          <?php }?>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php }?>
                  <!-- end event block -->
                  <!-- start article block -->
                  <?php if($msgs[$i]->type == 'article')
{?>
                  <div class="whitebox article">
                    <div id="article_block_<?php echo $msgs[$i]->id;?>">
                      <div class="clearfix">
                        <?php
	if($article->image)
	{?>
                        <div class="imagebox" style="margin-top:10px; margin-bottom:10px;"> <a href="<?php echo JURI::base();?>index.php?option=com_content&view=article&id=<?php echo $article->article_id;?>&Itemid=<?php echo $Itemid;?>" > <img src="<?php echo JURI::base();?>images/awd_articles/<?php echo $msgs[$i]->user_id;?>/thumb/<?php echo $article->image;?>" title="<?php echo $article->title;?>" alt="<?php echo $article->title;?>" style="max-height:84px; max-width:112px;" /> </a> </div>
                        <div class="maincomment" style="margin-top:10px;"> <a href="<?php echo JURI::base();?>index.php?option=com_content&view=article&id=<?php echo $article->article_id;?>&Itemid=<?php echo $Itemid;?>" >
                          <h3><?php echo $article->title;?></h3>
                          </a>
                          <p>
                            <?php 
			$loadjomwall='{loadjomwall}';
			//echo $loadjomwall;
			if(strpos($article->description,$loadjomwall)!==false) {
				echo str_replace($loadjomwall," ",$article->description);
			}
			else {
				echo $article->description;
			}?>
                          </p>
                        </div>
                        <?php } else {?>
                        <div class="maincomment" style="margin-top:10px; float:left; width:98%"> <a href="<?php echo JURI::base();?>index.php?option=com_content&view=article&id=<?php echo $article->article_id;?>&Itemid=<?php echo $Itemid;?>" >
                          <h3><?php echo $article->title;?></h3>
                          </a>
                          <p><br />
                            <?php 
			$loadjomwall='{loadjomwall}';
			//echo $loadjomwall;
			if(strpos($article->description,$loadjomwall)!==false) {
				echo str_replace($loadjomwall," ",$article->description);
			}
			else {
				echo $article->description;
			}?>
                          </p>
                        </div>
                        <?php }?>
                      </div>
                    </div>
                  </div>
                  <?php }?>
                  <!-- end article block -->
                  <!-- start of trail block -->
                  <?php 
if($msgs[$i]->type == 'trail')
{
?>
                  <a href="<?php echo $trail->trail_link;?>" target="_blank" style="font-size:12px;"><?php echo $trail->trail_title;?></a><br>
                  <br>
                  <iframe src="http://www.everytrail.com/iframe2.php?trip_id=<?php echo str_replace('http://www.everytrail.com/view_trip.php?trip_id=','',$trail->trail_link);?>&width=350&height=300" marginheight="0" marginwidth="0" frameborder="0" scrolling="no" width="350" height="300"></iframe>
                  <?php 
}
?>
                  <!-- end of trail block -->
                  <!-- start file block -->
                  <?php if($msgs[$i]->type == 'file' && !$aPostB){?>
                  <div class="whitebox video">
                    <div id="video_block_<?php echo $msgs[$i]->id;?>">
                      <div class="clearfix">
                        <div class="imagebox"><a href="<?php echo JURI::base();?>images/awdfiles/<?php echo $msgs[$i]->user_id;?>/<?php echo $file->path;?>" target="_blank"> <img src="<?php echo JURI::base();?>components/com_awdwall/images/file.png" title="<?php echo JText::_('Download');?>" alt="<?php echo JText::_('Download');?>" /></a> <br />
                          <span style="padding-left:12px;font-weight:bold;color:#308CB6;"><a href="<?php echo JURI::base();?>images/awdfiles/<?php echo $msgs[$i]->user_id;?>/<?php echo $file->path;?>" target="_blank"><?php echo JText::_('Download');?></a></span> </div>
                        <div class="maincomment">
                          <h3><?php echo $file->title;?></h3>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php }?>
                  <!-- end file block -->
                  
 <div style="padding:8px 0px;">
 <span class="wall_date">
                  <?php if(AwdwallHelperUser::isSocialfeed($msgs[$i]->id)) 
	{echo '<img src="'.JURI::base().'components/com_awdwall/images/'.AwdwallHelperUser::isSocialfeed($msgs[$i]->id).'_date.png" />&nbsp;&nbsp;'.JText::_('via').'&nbsp;'.ucfirst(AwdwallHelperUser::isSocialfeed($msgs[$i]->id)).'&nbsp;&nbsp;';}
?>
                  <?php echo AwdwallHelperUser::getDisplayTime($msgs[$i]->wall_date);?> </span>&nbsp;&nbsp;
                  <?php if((int)$usercurrent->id && !$aPostB){
if(!(int)$msgs[$i]->is_pm){
?>
                  <a href="javascript:void(0);" onclick="showCommentBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getcbox&tmpl=component';?>', <?php echo $msgs[$i]->id;?>, <?php echo $msgs[$i]->commenter_id;?>, <?php echo $msgs[$i]->id;?>);" title="Comment"><?php echo JText::_('Comment');?></a>
                  <?php
	$canlike = $wallModel->getLikeOfMsgOfUser($msgs[$i]->id,$usercurrent->id);
 if($displayLike){	
	if(!$canlike){
?>
                  - <a href="javascript:void(0);" onclick="openLikeMsgBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=addlikemsg&wid=' . (int)$msgs[$i]->id . '&cid=' .(int)$msgs[$i]->commenter_id.'&tmpl=component';?>', '<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$msgs[$i]->id . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo $msgs[$i]->id;?>');"><?php echo JText::_('Like');?></a>
                  <?php } 
}?>
                  <span id="wholike_box_<?php echo $msgs[$i]->id;?>">
                  <?php
	// get who likes of message
	$whoLikes = $wallModel->getLikeOfMsg($msgs[$i]->id);
	if(isset($whoLikes[0])){
?>
                  <?php /*?>	- <a href="javascript:void(0);" onclick="getWhoLikeMsg('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$msgs[$i]->id . '&tmpl=component';?>', <?php echo $msgs[$i]->id;?>);"><?php echo JText::_('Who likes it');?></a> 
<?php */?>
                  <?php }?>
                  </span>
<?php
$sharepageTitle = $msgs[$i]->message;
$sharepageTitle = addslashes(htmlspecialchars($sharepageTitle));
$sharepageTitle = str_replace(chr(13), " ", $sharepageTitle); //remove carriage returns
$sharepageTitle = str_replace(chr(10), " ", $sharepageTitle); //remove line feeds 
$facebooksharepageTitle=$sharepageTitle;
$facebookdesc=$sharepageTitle;
$imageurl='';
if($msgs[$i]->type == 'file'){
$imageurl=JURI::base().'components/com_awdwall/images/file.png';
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$pageTitle;
}
if($msgs[$i]->type == 'video'){
$imageurl=JURI::base().'images/'.$video->thumb;
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$video->description;
}
if($msgs[$i]->type == 'image'){
$imageurl=JURI::base().'images/'.$msgs[$i]->user_id.'/thumb/'.$image->path;
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$image->description;
}
if($msgs[$i]->type == 'link'){
 if($link->link_img != ''){
$imageurl=$link->link_img;
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$link->description;
}
}
if($msgs[$i]->type == 'event'){
if($event->image){
	$imageurl=JURI::base().'images/awd_events/'.$msgs[$i]->user_id.'/thumb/'.$event->image;
}
else
{
	$imageurl=JURI::base().'components/com_awdwall/images/event.png';
}
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$event->description;

}
if($fbshareaapid){
if(!empty($imageurl))
{
$facebookshareurl='https://www.facebook.com/dialog/feed?app_id='.$fbshareaapid.'&link='.urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $msgs[$i]->id . '&Itemid=' . $Itemid)).'&
  &name='.urlencode($facebooksharepageTitle).'&picture='.$imageurl.'&
  description='.urlencode(strip_tags($facebookdesc)).'&redirect_uri='.urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $msgs[$i]->id . '&Itemid=' . $Itemid));
}
else
{
$facebookshareurl='https://www.facebook.com/dialog/feed?app_id='.$fbshareaapid.'&link='.urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $msgs[$i]->id . '&Itemid=' . $Itemid)).'&
  &name='.urlencode($facebooksharepageTitle).'&
  description='.urlencode(strip_tags($facebookdesc)).'&redirect_uri='.urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $msgs[$i]->id . '&Itemid=' . $Itemid));
}}
?>
                  <?php if($displayShare){?>
                  - <a class="ashare" rev="hid_<?php echo $msgs[$i]->id; ?>" rel="ashare_<?php echo $msgs[$i]->id; ?>" onmouseover="show_share();" href="javascript:void(0);"><?php echo JText::_('Share');?></a>
                  <?php }?>
                  <div class="ashare" style="display:none;" id="ashare_<?php echo $msgs[$i]->id; ?>">
		<div class="share-top"><div></div></div>
		<a href="javascript:void(0);" onclick="hidden_share();" style="float: right; font-weight: bold; color: rgb(170, 170, 170); margin-right: 7px;">X</a>
		<div class="share-center">
        <?php if($fbshareaapid){?>
			<a rel="nofollow" target="_blank"  href="<?php echo $facebookshareurl;?>" title="<?php echo JText::_('facebook');?>"><?php echo JText::_('Facebook');?></a>
			<br/>
            <?php } ?>
			<a rel="nofollow" target="_blank"  href="http://twitter.com/share?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $msgs[$i]->id . '&Itemid=' . $Itemid));?>&text=<?php echo urlencode($pageTitle);?>" title="<?php echo JText::_('twitter');?>"><?php echo JText::_('Twitter');?></a>
			<br/>
			<a rel="nofollow" target="_blank"  href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $msgs[$i]->id . '&Itemid=' . $Itemid));?>&title=<?php echo urlencode($pageTitle);?>" title="<?php echo JText::_('LinkedIn');?>"><?php echo JText::_('LinkedIn');?></a>
			<br/>
			<a target="_blank" href="https://plus.google.com/share?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $msgs[$i]->id . '&Itemid=' . $Itemid));?>"><?php echo JText::_('Google Plus');?></a>
			<br/>
			<a   target="_blank" href="http://digg.com/submit?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $msgs[$i]->id . '&Itemid=' . $Itemid));?>"><?php echo JText::_('Digg');?></a>
			<br/>
			<a  target="_blank" href="http://www.stumbleupon.com/submit?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $msgs[$i]->id . '&Itemid=' . $Itemid));?>&amp;title=<?php echo urlencode($pageTitle);?>"><?php echo JText::_('Stumbleupon');?></a>
		</div>
		<div class="share-bottom"><div></div></div>
	</div>
                  <?php if($displayPm){?>
                  - <a href="javascript:void(0);" onclick="showPMBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getpmbox&tmpl=component';?>', <?php echo $msgs[$i]->id;?>, <?php echo $msgs[$i]->commenter_id;?>, <?php echo $msgs[$i]->id;?>);"><?php echo JText::_('PM');?></a>
                  <?php }?>
                  <?php if((int)$usercurrent->id == (int)$msgs[$i]->commenter_id || $can_delete || in_array($usercurrent->id,$moderator_users)){?>
                  - <a href="javascript:void(0);" onclick="openMsgDeleteBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=deletemsg&wid=' . (int)$msgs[$i]->id . '&tmpl=component';?>', <?php echo $i;?>);"><?php echo JText::_('Delete');?></a>
                  <?php }elseif((int)$usercurrent->id == (int)$_GET['wuid'] || $can_delete || in_array($usercurrent->id,$moderator_users)){?>
                  - <a href="javascript:void(0);" onclick="openMsgDeleteBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=deletemsg&wid=' . (int)$msgs[$i]->id . '&tmpl=component';?>', <?php echo $i;?>);"><?php echo JText::_('Delete');?></a>
                  <?php } // end delete ?>
                  <?php }else{// end not pm ?>
                  <a href="javascript:void(0);" onclick="showCommentBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getcbox&tmpl=component&is_reply=1';?>', <?php echo $msgs[$i]->id;?>, <?php echo $msgs[$i]->commenter_id;?>, <?php echo $msgs[$i]->id;?>);" title="Comment"><?php echo JText::_('Reply');?></a>
                  <?php if((int)$usercurrent->id == (int)$msgs[$i]->commenter_id || $can_delete || in_array($usercurrent->id,$moderator_users)){?>
                  - <a href="javascript:void(0);" onclick="openMsgDeleteBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=deletemsg&wid=' . (int)$msgs[$i]->id . '&tmpl=component';?>', <?php echo $i;?>);"><?php echo JText::_('Delete');?></a>
                  <?php } // end delete ?>
                  <?php }?>
                  <?php if(AwdwallHelperUser::checkOnline($msgs[$i]->commenter_id)){?>
                  <img src="<?php echo AwdwallHelperUser::getOnlineIcon();?>" title="<?php echo JText::_('Online');?>" alt="<?php echo JText::_('Online');?>" style="vertical-align:middle;"/>
                  <?php }?>
                  <?php }else{ ?>
                  <?php if((int)$usercurrent->id == (int)$msgs[$i]->commenter_id || $can_delete || in_array($usercurrent->id,$moderator_users)){?>
                  - <a href="javascript:void(0);" onclick="openMsgDeleteBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=deletemsg&wid=' . (int)$msgs[$i]->id . '&tmpl=component';?>', <?php echo $i;?>);"><?php echo JText::_('Delete');?></a>
                  <?php } // end delete ?>
                  <?php } ?>
                  <p></p>
                  <!-- start like box -->
                  <div id="like_<?php echo $msgs[$i]->id;?>"> </div>
                  <?php
	$whoLikes = $wallModel->getLikeOfMsg($msgs[$i]->id);
	if(isset($whoLikes[0])){
?>
                  <script type="text/javascript">
getWhoLikeMsg('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$msgs[$i]->id . '&tmpl=component&Itemid='.$Itemid;?>', <?php echo $msgs[$i]->id;?>);
</script>
                  <?php } ?>
                  <!-- end like box -->
                  <!-- start event-attend box -->
                  <div id="event_<?php echo $msgs[$i]->id;?>"> </div>
                  <?php
	$whoAttends = $wallModel->getAttendOfMsg($msgs[$i]->id);
	
	if(isset($whoAttends[0])){
?>
                  <div id="event_<?php echo $msgs[$i]->id;?>" class="comment_text"> <span id="event_loader_<?php echo $msgs[$i]->id;?>" style="display:none;margin:10px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span> </div>
                  <script type="text/javascript">
<?php /*?>alert('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$msgs[$i]->id . '&tmpl=component&Itemid='.$Itemid;?>');
<?php */?>
getWhoAttendEvent('<?php echo 
JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getEventAttend&wid=' . (int)$msgs[$i]->id . '&tmpl=component&Itemid='.$Itemid;?>', <?php echo $msgs[$i]->id;?>);
</script>
                  <?php } ?>
                  <!-- end event-attend box -->
                  <!--start pm box -->
                  <div id="pm_<?php echo $msgs[$i]->id;?>" class="comment_text"> <span id="pm_loader_<?php echo $msgs[$i]->id;?>" style="display:none;margin:10px;margin-top:10px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span> </div>
                  <!--end pm box -->
                  <!--start comment box -->
                  <div id="c_<?php echo $msgs[$i]->id;?>" class="comment_text"> <span id="c_loader_<?php echo $msgs[$i]->id;?>" style="display:none;margin-bottom:10px;margin-top:10px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span> </div>
                  <!--end comment box -->
 </div>                 
                  <!--start comment block -->
<?php if($getrealtimecomment==1){?>
<script type="text/javascript">
	getrealtimecomment(<?php echo $msgs[$i]->id;?>);
</script>
<?php } ?>
                  <div id="c_content_<?php echo $msgs[$i]->id;?>">
                    <?php
	// get comments of message
	$cpage 		= 0;
	$coffset 	= $cpage*$commentLimit;
	$comments 	= $wallModel->getAllCommentOfMsg($commentLimit, $msgs[$i]->id, $coffset);
	$nofComments = $wallModel->countComment($msgs[$i]->id);
	if(isset($comments[0])){
		foreach($comments as $comment){		
		$commenter_id=$wallModel->getwallpostowner($msgs[$i]->id);
	if($display_profile_link==1)
	{
		$profilelink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' .$comment->commenter_id. '&Itemid=' .$Itemid, false);
		//$rprofilelink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' .$msgs[$i]->user_id. '&Itemid=' .$Itemid, false);
	}
	else
	{
		$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $comment->commenter_id.'&Itemid=' . $Itemid, false);
		//$rprofilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $msgs[$i]->user_id.'&Itemid=' . $Itemid, false);
	}
		
		//$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $comment->commenter_id.'&Itemid=' . $Itemid, false);
?>
                    <div class="whitebox" id="c_block_<?php echo $comment->id;?>">
                      <div class="clearfix">
                        <div class="subcommentImagebox"><a href="<?php echo $profilelink;?>"> <img src="<?php echo AwdwallHelperUser::getBigAvatar32($comment->commenter_id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($comment->commenter_id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($comment->commenter_id);?>" height="32" width="32" class="awdpostavatar"  /> </a></div>
                        <div class="subcomment">
                          <div class="rbroundbox">
                            <div class="rbtop">
                              <div></div>
                            </div>
                            <div class="rbcontent"> <a href="<?php echo $profilelink;?>" class="authorlink"><?php echo AwdwallHelperUser::getDisplayName($comment->commenter_id);?></a>&nbsp;&nbsp;<?php echo stripcslashes(AwdwallHelperUser::showSmileyicons($comment->message));?>
                              <div class="subcommentmenu"> <span class="wall_date"><?php echo AwdwallHelperUser::getDisplayTime($comment->wall_date);?></span>
<span id="commentlike_<?php echo (int)$comment->id;?>">          
        <?php
        $canlike = $wallModel->getLikeOfMsgOfUser($comment->id,$user->id);
        if($displayCommentLike){
            if(!$canlike){
        ?>
            &nbsp;&nbsp;
            <a href="javascript:void(0);" onclick="openLikeCommentBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=addlikemsg&wid=' . (int)$comment->id . '&cid=' .(int)$user->id.'&tmpl=component';?>', '<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$comment->id . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$comment->id;?>');"><?php echo JText::_('Like');?></a> &nbsp;&nbsp;
        <?php
            }
            else
            {
        ?>
            &nbsp;&nbsp;
            <a href="javascript:void(0);" onclick="deleteLikeCommentBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=deletelikemsg&wid=' . (int)$comment->id . '&cid=' .(int)$user->id.'&tmpl=component';?>', '<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$comment->id . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$comment->id;?>');"><?php echo JText::_('Unlike');?></a> &nbsp;&nbsp;
        <?php
            }
        }
        ?>
         </span> 
	<?php
        $whoLikes = $wallModel->getLikeOfMsg($comment->id);
        if(isset($whoLikes[0])){
    ?>
                <script type="text/javascript">
                getWhoLikeComment('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$comment->id . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$comment->id;?>','<?php echo (int)$comment->id;?>');
                </script>
    <?php 
        }
    ?>
                                      
                                <?php if((int)$usercurrent->id == (int)$comment->commenter_id || $can_delete || in_array($usercurrent->id,$moderator_users) || (int)$usercurrent->id == (int)$commenter_id){?>
                                &nbsp;&nbsp; <a href="javascript:void(0);" onclick="openCommentDeleteBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=deletecomment&wid=' . (int)$comment->id . '&tmpl=component';?>', <?php echo $comment->id; ?>);"><?php echo JText::_('Delete');?></a>
                                <?php }?>
                              </div>
                            </div>
                            <div class="rbbot">
                              <div></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <?php			
		}
	}
?>
                  </div>
                  <!-- start older comments-->
                  <?php
	if((($cpage + 1) * $commentLimit) < $nofComments){
?>
                  <div class="lightblue_box"><a href="javascript:void(0);" onclick="getOlderComments('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getoldercomment&tmpl=component&wid=' . $msgs[$i]->id;?>', <?php echo $msgs[$i]->id;?>);"><?php echo JText::_('Older Comments');?></a>&nbsp;&nbsp;<span id="older_comments_loader_<?php echo $msgs[$i]->id;?>" style="display:none;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
                    <input id="awd_c_page_<?php echo $msgs[$i]->id;?>" name="awd_c_page_<?php echo $msgs[$i]->id;?>" type="hidden" value="<?php echo ($cpage + 1);?>" autocomplete="off"/>
                  </div>
                  <?php } ?>
                  <!--end comment block-->
                  <!--end comment block-->
                </div>
              </div>
            </div>
          </div>
          <!-- end block msg -->
          <?php 
	}// end for 
}// end if parent
?>
        </div>
      </div>
    </div>
  </div>
  <?php
	if(((($page + 1) * $postLimit) < $nofMsgs) && (int)$user->id){
?>
  <div class="lightblue_box"><a href="javascript:void(0);" onclick="getOlderPosts('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getoldermsg&tmpl=component&layout=mywall&wuid=' . $wuid;?>');"><?php echo JText::_('Older Posts');?></a>&nbsp;&nbsp;<span id="older_posts_loader" style="display:none;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span> </div>
  <input id="awd_page" name="awd_page" type="hidden" value="<?php echo $page+1 ;?>" autocomplete="off" />
  <?php } ?>
</div>
<input id="task" name="task" type="hidden" value="<?php echo $task;?>" autocomplete="off" />
<div id="dialog_msg_delete_box" title="<?php echo JText::_('Delete Post');?>" style="display:none;"> <?php echo JText::_('Are you sure you want to delete this post');?> <br />
  <br />
  <span id="msg_delete_loader"></span>
  <input type="hidden" name="msg_delete_url" id="msg_delete_url" />
  <input type="hidden" name="msg_delete_block_id" id="msg_delete_block_id" />
</div>
<div id="dialog_c_delete_box" title="<?php echo JText::_('Delete Comment');?>" style="display:none;"> <?php echo JText::_('Are you sure you want to delete this comment');?> <br />
  <br />
  <span id="c_delete_loader"></span>
  <input type="hidden" name="c_delete_url" id="c_delete_url" />
  <input type="hidden" name="c_delete_block_id" id="c_delete_block_id" />
</div>
<div id="dialog_like_box" title="<?php echo JText::_('Like This Post');?>" style="display:none;"> <?php echo JText::_('Are you sure you want to like this post');?> <br />
  <br />
  <span id="like_loader"></span>
  <input type="hidden" name="like_url" id="like_url" />
  <input type="hidden" name="who_like_url" id="who_like_url" />
  <input type="hidden" name="who_like_wid" id="who_like_wid" />
</div>
<div id="dialog_pm_delete_box" title="<?php echo JText::_('Delete Private Message');?>" style="display:none;"> <?php echo JText::_('Are you sure you want to delete this PM');?> <br />
  <br />
  <span id="pm_delete_loader"></span>
  <input type="hidden" name="pm_delete_url" id="pm_delete_url" />
  <input type="hidden" name="pm_delete_block_id" id="pm_delete_block_id" />
</div>
<div id="dialog_add_as_friend" title="<?php echo JText::_('Add as Friend');?>" style="display:none;"> <?php echo JText::_('Are you sure you want to add this people as Friend');?> <br />
  <br />
  <span id="add_as_friend_loader"></span>
  <input type="hidden" name="add_as_friend_url" id="add_as_friend_url" />
</div>
<div id="dialog_add_as_friend_msg" title="<?php echo JText::_('Add as Friend');?>" style="display:none;"> <?php echo JText::sprintf('ADD FRIEND CONFIRM', AwdwallHelperUser::getDisplayName($userWall->id), AwdwallHelperUser::getDisplayName($userWall->id));?> <br />
  <span id="add_as_friend_loader"></span> </div>
<div style="clear:both"></div>
<?php
		$contents  = ob_get_contents();
		ob_end_clean();        
		return $contents;
	}
}
}
}
?>