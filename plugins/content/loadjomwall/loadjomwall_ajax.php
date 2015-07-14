<?php
define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__) . '/../../..' );
define('JPATH_CORE', JPATH_BASE . '/../../..');
define( 'DS', DIRECTORY_SEPARATOR );
error_reporting(0);
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

$rootpath=str_replace('plugins/content/loadjomwall/','',JURI::root());

$mainframe =JFactory::getApplication('site');
$db  =& JFactory::getDBO();

require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

$rate = (int) JRequest::getVar( 'rating', false );
$cid  = (int) JRequest::getVar( 'cid', false );

$db =& JFactory::getDBO();
require_once(JPATH_SITE . DS . 'components'.DS.'com_awdwall'. DS . 'helpers' . DS . 'user.php');
$lang =& JFactory::getLanguage();
$extension = 'com_awdwall';
$base_dir = JPATH_SITE;
$language_tag = 'en-GB';
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);

//echo $mainframe->getCfg('livesite');
jimport( 'joomla.plugin.helper' );
jimport( 'joomla.language.helper' );

$plg = JPluginHelper::getPlugin('content', 'loadjomwall');
//$pluginParams    = new JParameter( $plg->params );
$temparray=json_decode($plg->params);
if(empty($temparray->wallversion)){$wallversion=1;}else{$wallversion=$temparray->wallversion;}
if(empty($temparray->showavatar)){$showavatar=1;}else{$showavatar=$temparray->showavatar;}
$moderator_users=$temparray->moderator_users;
$moderator_users=explode(',',$moderator_users);
$user =& JFactory::getUser();
$ItemId=getComItemId();
$message_walloriginal=$_REQUEST['message_wall'];	
$message_walloriginal=formatUrlInMsg($message_walloriginal);
$message_wall=$_REQUEST['message_wall'];	
$message_wall=formatUrlInMsg($message_wall);
$contentid=$_REQUEST['contentid'];	
$date=date("Y-m-d H:i:s");
$task=$_REQUEST['task'];	
if($task=='add')
{
$query = "INSERT INTO #__awd_wall_content_comments (content_id, user_id, comment, submitted) VALUES ('".intval($contentid)."', '".intval($user->id)."', '".nl2br($message_wall)."', '".$date."') ";
$db->setQuery($query);			
if (!@$db->query()) {
	$error = 1;
	$message = $db->stderr();
}
$commentid=$db->insertid();
$email_check = '';
$return_json = '';

$path=JURI::base(); 
$temp='plugins/content/loadjomwall/';
$message_walloriginal=str_replace($temp,'',AwdwallHelperUser::showSmileyicons($message_walloriginal));
// adding to com_awdwall
if(file_exists(JPATH_SITE . '/components/com_awdwall/awdwall.php'))
{
$websitename=$mainframe->getCfg('fromname');
$user 			= &JFactory::getUser($userId);
$db->setQuery('SELECT cat.id FROM #__categories cat RIGHT JOIN #__content cont ON cat.id = cont.catid WHERE cont.id='.$contentid);
$category_id = $db->loadResult();
$db->setQuery('SELECT alias FROM #__content WHERE id='.$contentid);
$alias = $db->loadResult();
//$articlelink='index.php?option=com_content&view=article&id='.$contentid.'&Itemid='.$ItemId;
// $url = ' '.JURI::root().ContentHelperRoute::getArticleRoute($article->id, $article->catid); 
//$articlelink=ContentHelperRoute::getArticleRoute($contentid, $category_id, 0);
$mode=$mainframe->getCfg('sef');
$newUrl = ContentHelperRoute::getArticleRoute($contentid.':'.$alias, $category_id);
$router = new JRouterSite(array('mode'=>$mode));
$newUrl = $router->build($newUrl)->toString(array('path', 'query', 'fragment'));
$newUrl = str_replace('/administrator/', '', $newUrl);
$newUrl = str_replace('component/content/article/', '', $newUrl);
$newUrl = str_replace('plugins/content/loadjomwall/', '', $newUrl);
$articlelink=$newUrl;

	$query 	= "SELECT title FROM #__content where id = " . (int)$contentid;
	$db->setQuery($query);
	$articletitle = $db->loadResult();
$message_wall=' '.JText::_('has comment on').' <a href="'.$articlelink.'" >'.$articletitle.'</a>&nbsp;'.JText::_('article').'<br>'.nl2br($message_wall);
	$query = "INSERT INTO #__awd_wall (user_id, type, commenter_id, message,wall_date) VALUES ('".intval($user->id)."','text', '".intval($user->id)."', '".nl2br($message_wall)."', '".time()."') ";
	$db->setQuery($query);			
	if (!@$db->query()) {
		$error = 1;
		$message = $db->stderr();
	}
}
$liketotal=countlike($commentid);
$message='<div class=\'loadjomwallouter\' id=\''.$commentid.'\'> ';
	if($showavatar){
		$avatar=getAvatar($user->id,$wallversion);
		$commentuser =& JFactory::getUser($user->id);
		$commentuserusername=AwdwallHelperUser::getDisplayName($user->id);

		$profilelink='index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$ItemId;
	$startd=explode(" ",date("Y-m-d H:i:s"));
	$submittedtime=date('l,M j',strtotime($startd[0]));
	$commentdate=time();
	$comdate= AwdwallHelperUser::getDisplayTime($commentdate);
$message=$message.'<div class=\'loadjomwallinner\' id=\'avatar\'><a href=\''.$profilelink.'\' > <img src=\''.$avatar.'\'   title=\''.$commentuserusername.'\' border=\'0\' alt=\''.$commentuserusername.'\'> </a></div>';
	 } 
	$message=$message.'<div class=\'loadjomwallinner\' id=\'comment-holder\'><div id=\'text\'><a href=\''.$profilelink.'\' >'.$commentuserusername.'</a> '.nl2br( stripcslashes( $message_walloriginal)).' </div><div class=\'ago\'><a href=\'javascript::void(0);\' onclick=\'return showallcommentlike('.$commentid.');\' ><span id=\'commentlike'.$commentid.'\' class=\'show-more\'>'.$liketotal.'</span></a>&nbsp;&nbsp;<a href=\'javascript::void(0)\' onclick=\'return likecomment('.$commentid.')\'>Like</a>&nbsp;&nbsp;'.$comdate.'</div></div><a href=\'javascript::void(0)\' onclick=\'return deletecomment('.$commentid.')\' class=\'loadjomwalldelete\'></a><br style=\'clear: both;\'></div>^'.$commentid;

echo $message;

//$return_json = '{"message":"' . $message . '",';
//$return_json = $return_json . '"commentid":"' . $commentid . '"}';
//echo $return_json;
}

if($task=='del')
{
	$db =& JFactory::getDBO();
	$id=$_REQUEST['id'];
	$query = "DELETE FROM #__awd_wall_content_comments where id=".intval($id);
	$db->setQuery($query);			
	if (!@$db->query()) {
		$error = 1;
		$message = $db->stderr();
	}
	$query = "DELETE FROM #__awd_wall_content_comment_like where commentid=".intval($id);
	$db->setQuery($query);			
	if (!@$db->query()) {
		$error = 1;
		$message = $db->stderr();
	}
$return_json = '{"message":"' . $message . '"}';
echo 'ok';

}

if($task=='getlike')
{
	$db =& JFactory::getDBO();
	$id=$_REQUEST['id'];

	$query 	= "SELECT * FROM #__awd_wall_content_comment_like where commentid = " . (int)$id;
	$db->setQuery($query);
	$likerows = $db->loadObjectList();
	if(count($likerows))
	{
	foreach($likerows as $likerow)
	{
	$avatar=getAvatar($likerow->userid ,$wallversion);
	$commentuser =& JFactory::getUser($likerow->userid);
	$likeusername=AwdwallHelperUser::getDisplayName($likerow->userid);

	$profilelink='index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$likerow->userid.'&Itemid='.$ItemId;
	$message=$message.'<div class=\'awdthumb\'><a href=\''.$profilelink.'\' title=\''.$likeusername.'\' alt=\''.$likeusername.'\'><img src=\''.$avatar.'\' height=\'35\' width=\'35\' border=\'0\' title=\''.$likeusername.'\' alt=\''.$likeusername.'\' /><br><center id=\'awdusername\' >'.$likeusername.'</center></a></div>';
	}
	$return_json = '{"message":"' . $message . '"}';
	echo $return_json;

	}
	


}


if($task=='like')
{
	$db =& JFactory::getDBO();
	$user 			= &JFactory::getUser();

	$id=$_REQUEST['id'];
	$query 	= "SELECT count(*) as countuser FROM #__awd_wall_content_comment_like where userid = " .$user->id." and commentid=".$id;
	$db->setQuery($query);
	$countuser = $db->loadResult();
	if($countuser==0)
	{
		$query = "INSERT INTO #__awd_wall_content_comment_like (userid, commentid) VALUES ('".$user->id."', '".$id."') ";
		$db->setQuery($query);			
		if (!@$db->query()) {
			$error = 1;
			$message = $db->stderr();
		}
	}
	$query 	= "SELECT count(*) as totalcount FROM #__awd_wall_content_comment_like where commentid = " . (int)$id;
	$db->setQuery($query);
	$totalcount = $db->loadResult();
	$return_json = '{"message":"' . $totalcount . '"}';
	echo $return_json;

}
if($task=='getnewmessage')
{
	$db =& JFactory::getDBO();
	$user 			= &JFactory::getUser();
	$ItemId=getComItemId();
	$contentid=$_REQUEST['id'];	
	$date=date("Y-m-d H:i:s");
	
$duration=15;
$dateinsec=strtotime($date);
$newdate=$dateinsec-$duration;
$newdate=date('Y-m-d H:i:s',$newdate);

	
	$query = 'SELECT *' .
	' FROM #__awd_wall_content_comments' .
	' WHERE content_id = '.(int)$contentid.' and user_id!='.$user->id.' and submitted >= "'.$newdate.'" order by id ';
	//echo $query;
	$db->setQuery($query);
	$commentrows = $db->loadObjectList();
	
		$admin_groupid=array(7,8);
		$can_delete='';
		if($user->id)
		{
			$query="SELECT group_id FROM #__user_usergroup_map where user_id=".$user->id;
			$db->setQuery($query);
			$user_groupidList= $db->loadObjectList();
			$can_delete='';
			foreach ($user_groupidList as $ugroupid)
			{
				if(in_array($ugroupid->group_id,$admin_groupid)){
				$can_delete=1;
				}
			}
		}
	
	if(count($commentrows))
	{
	foreach ($commentrows as $commentrow)
	{
		
?>
 <div class="loadjomwallouter" id="<?php echo $commentrow->id;?>"> 
	<?php if($showavatar){
		$avatar=getAvatar($commentrow->user_id,$wallversion);
		$commentuser =& JFactory::getUser($commentrow->user_id);
		$commentuserusername=AwdwallHelperUser::getDisplayName($commentrow->user_id);

		$profilelink='index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$commentrow->user_id.'&Itemid='.$ItemId;
		
	$startd=explode(" ",$commentrow->submitted);
	$submittedtime=date('l,M j',strtotime($startd[0]));
	$commentdate=strtotime($commentrow->submitted);
$path=JURI::base(); 
$temp='plugins/content/loadjomwall/';
$message_walloriginal=str_replace($temp,'',AwdwallHelperUser::showSmileyicons($commentrow->comment));
	?>
	  <div class="loadjomwallinner" id="avatar">
	 <a href="<?php echo $profilelink;?>" alt="<?php echo $commentuserusername;?>" title="<?php echo $commentuserusername;?>"> <img src="<?php echo $avatar;?>"  border="0" alt="<?php echo $commentuserusername;?>" title="<?php echo $commentuserusername;?>" width="32" height="32"> </a>
	  </div>
	  <?php } ?>
					  
					  <div class="loadjomwallinner" id="comment-holder">
						<div id="text"><a href="<?php echo $profilelink;?>" ><?php echo $commentuserusername;?></a> <?php echo $message_walloriginal;?> </div>
						<div class="ago"><a href="javascript::void(0);" onclick="return showallcommentlike(<?php echo $commentrow->id;?>);" > <span id="commentlike<?php echo $commentrow->id;?>" class="show-more"><?php echo countlike($commentrow->id);?></span></a><?php if($user->id){?><a href="javascript::void(0);" onclick="return likecomment(<?php echo $commentrow->id;?>)"><?php echo JText::_('Like');?></a><?php } ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo  AwdwallHelperUser::getDisplayTime($commentdate);?></div>
					  </div>
					  <?php 
					  if($user->id)
					  {
					 
					  if($user->id==$commentrow->user_id || $can_delete==1 || ( in_array($user->id,$moderator_users)) ){?>
					  <a href="javascript::void(0)" onclick="return deletecomment(<?php echo $commentrow->id;?>)" class="loadjomwalldelete"></a>
					  <?php } } ?>
					  <br style="clear: both;">
</div>   
<?php
}
}
}

function countlike($commentid)
{
	$db =& JFactory::getDBO();
	$query 	= "SELECT count(*) as totalcount FROM #__awd_wall_content_comment_like where commentid = " . (int)$commentid;
	$db->setQuery($query);
	$totalcount = $db->loadResult();
	
	return $totalcount;
}


function getAvatar($userId,$wallversion)
{	
$path=JURI::base(); 
$temp='plugins/content/loadjomwall/';
$path=str_replace($temp,'',$path);
$db =& JFactory::getDBO();
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'blue');
$avatarintergration 		= $config->get('avatarintergration', '0');
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
				$avatar = $path . "components/com_awdwall/images/".$template."/".$template."32.png";
			}else{
				$avatar = $path. "images/wallavatar/" . $userId . "/thumb/tn32" . $img;
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
function getComItemId()
{
	$db 	= &JFactory::getDBO();
	$query  = "SELECT id FROM #__menu WHERE link='index.php?option=com_awdwall&view=awdwall&layout=main' and published='1'";
	$db->setQuery($query);
	return $db->loadResult();
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

?>