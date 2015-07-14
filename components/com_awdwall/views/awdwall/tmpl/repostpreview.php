<?php
/**
 * @version 2.5
 * @package JomWALL CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
//require_once(JPATH_COMPONENT . DS . 'js' . DS . 'include.php');
$Itemid = AwdwallHelperUser::getComItemId();

$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
$displayPm = $config->get('display_pm', 1);
$displayShare = $config->get('display_share', 1);
$displayLike = $config->get('display_like', 1);
$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
$moderator_users = $config->get('moderator_users', '');
$getrealtimecomment = $config->get('getrealtimecomment', 0);

$moderator_users=explode(',',$moderator_users);
$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';
$jomalbumexist='';
if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
{
	$jomalbumexist=1;
}

$db =& JFactory::getDBO();
// get user object
$user = &JFactory::getUser();
if(isset($this->msgs[0])){
	$n = count($this->msgs);
	for($i = 0; $i < $n; $i++){
		$pmText = '';
		
		if((int)$this->msgs[$i]->is_pm){
			$pmText = '<div class="pm_text">Private message from </div>';
		}
		$video = null;
		$pageTitle = AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);
		if($this->msgs[$i]->type == 'video'){
			$video = $this->wallModel->getVideoInfoByWid($this->msgs[$i]->id);
			$pageTitle = $video->title;
		}
		$image = null;
		if($this->msgs[$i]->type == 'image'){
			$image = $this->wallModel->getImageInfoByWid($this->msgs[$i]->id);
			$pageTitle = $image->name;
		}
		$mp3 = null;
		if($this->msgs[$i]->type == 'mp3'){
			$mp3 = $this->wallModel->getMp3InfoByWid($this->msgs[$i]->id);
			$pageTitle = $mp3->title;
		}
		$link = null;
		if($this->msgs[$i]->type == 'link'){
			$link = $this->wallModel->getLinkInfoByWid($this->msgs[$i]->id);
			$pageTitle = $link->title;
		}
		$file = null;
		if($this->msgs[$i]->type == 'file'){
			$file  = $this->wallModel->getFileInfoByWid($this->msgs[$i]->id);
			$pageTitle = $file ->title;
		}
		$jing=null;
		if($this->msgs[$i]->type== 'jing'){
			$jing  = $this->wallModel->getJingInfoByWid($this->msgs[$i]->id);
			$pageTitle = $jing ->jing_title;
		}
		$event=null;
		if($this->msgs[$i]->type == 'event'){
			$event  = $this->wallModel->getEventInfoByWid($this->msgs[$i]->id);
			$pageTitle = $event ->title;
		}
		$trail=null;
		if($this->msgs[$i]->type == 'trail'){
			$trail  = $this->wallModel->getTrailInfoByWid($this->msgs[$i]->id);
			$pageTitle = $trail ->trail_title;
		}
		$article=null;
		if($this->msgs[$i]->type == 'article'){
			$article  = $this->wallModel->getArticleInfoByWid($this->msgs[$i]->id);
			$pageTitle = $article ->title;
		}
		
		$pageTitle = addslashes(htmlspecialchars($pageTitle));
		$pageTitle = str_replace(chr(13), " ", $pageTitle); //remove carriage returns
		$pageTitle = str_replace(chr(10), " ", $pageTitle); //remove line feeds 
		// check privacy
		/*
		$isFriend = 1;
		if((int)$this->privacy == 1){
			if((int)$this->msgs[$i]->commenter_id != (int)$user->id)
				$isFriend = JsLib::isFriend($this->msgs[$i]->commenter_id, $user->id);			
		}
		*/
		// check rightcolumn css
		$rightColumnCss = 'rbroundboxright';
		$showLeft = true;
//		if((int)$this->msgs[$i]->commenter_id != (int)$this->msgs[$i]->user_id && (int)$this->msgs[$i]->commenter_id == $this->wuid && !in_array($this->task, $this->arrTask)){
//			$showLeft = false;
//			$rightColumnCss = 'rbroundboxrightfull';
//		}
//		$showLeft = true;
		$textWall = '';
	switch($this->msgs[$i]->type){
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
	}
	$group = $this->groupModel->getGroupInfo($this->msgs[$i]->group_id);
	// check if this is private group		
	$isPrivate = $this->groupModel->isPriaveGrp($this->msgs[$i]->group_id);
	$vowner = $this->groupModel->checkGrpOwner($user->id, $this->msgs[$i]->group_id);
	if((int)$isPrivate){
		// check if viewer is member of this group		
		if(!(int)$vowner){
			$isMemberGrp = $this->groupModel->isMemberGrp($this->msgs[$i]->group_id, $user->id);
			if(!(int)$isMemberGrp){
				continue;
			}
		}
		
	}
	$owner = $this->groupModel->checkGrpOwner($this->msgs[$i]->commenter_id, $this->msgs[$i]->group_id);
	if($this->layout != 'main'){
		$vowner = $this->groupModel->checkGrpOwner($this->wuid, $this->msgs[$i]->group_id);
		if((int)$vowner && (int)$owner){
			$showLeft = false;
			$rightColumnCss = 'rbroundboxrightfull';
		}
		if($this->msgs[$i]->group_id != NULL && ($this->wuid == $this->msgs[$i]->commenter_id)){
			$showLeft = false;
			$rightColumnCss = 'rbroundboxrightfull';
		}	
	}
	$likeTxt = '';
	if($this->msgs[$i]->type == 'like'){
		$likeTxt = JText::sprintf('A LIKES B POST', '<a href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>', JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->user_id . '&Itemid=' . $Itemid, false), AwdwallHelperUser::getDisplayName($this->msgs[$i]->user_id));
	}
	
	if($this->msgs[$i]->type == 'friend'){ 
		$likeTxt = JText::sprintf('A AND B ARE FRIEND', '<a href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>',  '<a href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->user_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->user_id) . '</a>');
	}
	
	if($this->msgs[$i]->type == 'group'){
		$likeTxt = JText::sprintf('JOIN GROUP NEWS FEED', '<a href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' .$this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>',  '<a href="' . JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->msgs[$i]->group_id . '&Itemid=' . $Itemid, false) . '">' . $group->title . '</a>');
	}
	$wroteOngroupTxt = '';
	if($this->msgs[$i]->group_id){
		$wroteOngroupTxt = JText::sprintf('A WRITE ON B GROUP', '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '" >' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>', '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->msgs[$i]->group_id . '&Itemid=' . $Itemid, false) . '" >' . $group->title . '</a>');
	}
	//$config = &JComponentHelper::getParams('com_awdwall');
	$app = JFactory::getApplication('site');
	$config =  & $app->getParams('com_awdwall');
	$template 		= $config->get('temp', 'default');
	
	$display_profile_link 		= $config->get('display_profile_link', 1);
	$showLeft=1;
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$postLimit 		= $config->get('nof_post', 15);
		$page 			= JRequest::getCmd('awd_page', 0);
		$where = array();
		$where[] = 'connect_from  = ' . (int)$this->msgs[$i]->commenter_id;
		$where = count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$offset = $page*$postLimit;
		$friends = $this->wallModel->getAllFriends($postLimit, $where, $offset);
	
	
		$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id .'&Itemid=' . $Itemid, false);
?>
<style>
@import url('<?php echo JURI::base();?>components/com_awdwall/css/bootstrap.min.css');
#awd-mainarea p{
clear:both;}

	#awd-mainarea .wallheadingRight ul li a, #awd-mainarea .wallheadingRight ul li.separator{
		color:#<?php echo $this->color[1]; ?>;
	}
	#awd-mainarea .profileLink a, #awd-mainarea .profileMenu .ppm a, #awd-mainarea #msg_content .rbroundboxleft .mid_content a, #awd-mainarea .commentinfo a, #awd-mainarea .user_place ul.profileMenu li a{
		color:#<?php echo $this->color[2]; ?>;
	}
	#awd-mainarea #awd_message, #awd-mainarea .right_mid_content .walltowall li{
		color:#<?php echo $this->color[3]; ?>!important;
	}
	#awd-mainarea .wall_date{
		color:#<?php echo $this->color[4]; ?>;
	}
	 #awd-mainarea .rbroundboxright, #awd-mainarea .fullboxtop,  #awd-mainarea .lightblue_box, #awd-mainarea .rbroundboxrighttop{
		background-color:#<?php echo $this->color[5]; ?>;
	}
	<?php if($template!='default'){?>
	#awd-mainarea .mid_content_top{
	background-color:#<?php echo $this->color[5]; ?>;
	}
	<?php }else{ ?>
	#awd-mainarea .mid_content_top{
	background-color:#<?php echo $this->color[1]; ?>;
	}
	#awd-mainarea .rbroundboxright{
	 background-color:#<?php echo $this->color[1]; ?>;
	}
	<?php }?>
	#awd-mainarea .rbroundboxleft .mid_content a, #awd-mainarea .blog_groups, #awd-mainarea .blog_groups a, #awd-mainarea .blog_friend, #awd-mainarea .blog_friend a, #awd-mainarea a.authorlink{
		color:#<?php echo $this->color[6]; ?>!important;
	}
	#awd-mainarea #msg_content .rbroundboxleft, #awd-mainarea #msg_content .awdfullbox{
		background-color:#<?php echo $this->color[7]; ?>;
	}
	#awd-mainarea .walltowall li a, #msg_content .maincomment_noimg_right h3 a{
		color:#<?php echo $this->color[8]; ?>;
	}
	#awd-mainarea ul.tabProfile li a{
		background-color:#<?php echo $this->color[9]; ?>;
	}
	#awd-mainarea ul.tabProfile li a:hover, #awd-mainarea ul.tabProfile li.active a{
		background-color:#<?php echo $this->color[10]; ?>;
	}
	#awd-mainarea ul.tabProfile li a{
		color:#<?php echo $this->color[11]; ?>;
	}
	#awd-mainarea .whitebox, #awd-mainarea .blog_content, #awd-mainarea .about_me{
		background-color:#<?php echo $this->color[12]; ?>;
	}
	<?php if($template!='default'){?>
	#awd-mainarea .wallheading, #awd-mainarea .wallheadingRight{
		background-color:#<?php echo $this->color[13]; ?>;
	}
	#awd-mainarea .round, #awd-mainarea .search_user{
		background-color:#<?php echo $this->color[14]; ?>;
	}
	<?php } ?>
#awd-mainarea .notiItemsWrap{
background-color:#<?php echo $this->color[12]; ?>;
color:#<?php echo $this->color[3]; ?>;
}
#awd-mainarea .notiItemsWrapLast{
background-color:#<?php echo $this->color[12]; ?>;
color:#<?php echo $this->color[3]; ?>;
}

#awd-mainarea #dropAlerts .notiItem a{
color:#<?php echo $this->color[2]; ?>!important;
}
#awd-mainarea ul.profileMenu .totalno{
	background:#<?php echo $this->color[12]; ?>;
    border: 1px solid #<?php echo $this->color[2]; ?>;
    color: #<?php echo $this->color[8]; ?>;
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
color:#<?php echo $this->color[8]; ?>;
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

</style>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/bootstrap.min.min.js"></script>
<link rel="stylesheet" href="<?php echo JURI::base();?>components/com_awdwall/css/style_<?php echo $template; ?>.css"  type="text/css" />
<div  id="awd-mainarea" style="width:100%;">
<div class="awdfullbox clearfix" id="msg_block_<?php echo $this->msgs[$i]->id;?>">
<span class="tl"></span><span class="bl"></span>
	 <?php if($showLeft){?>
    <div class="rbroundboxleft" style="width:60px;">
      <div class="mid_content">
	  <?php if(!(int)$this->msgs[$i]->group_id){?>
	  <a href="<?php echo $profilelink;?>">
	<img src="<?php echo AwdwallHelperUser::getBigAvatar51($this->msgs[$i]->commenter_id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?>" height="50" width="50" class="awdpostavatar"/>
	<?php 
	if(AwdwallHelperUser::isSocialfeed($this->msgs[$i]->id)) 
	{?>
		<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo AwdwallHelperUser::isSocialfeed($this->msgs[$i]->id); ?>.png" class="post_type_icon"  />
	<?php }	?>
	  </a>	
	<?php }else{
	if($owner || $vowner){
	?>
	<a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->msgs[$i]->group_id. '&Itemid=' . $Itemid, false);?>">
	<img src="<?php echo AwdwallHelperUser::getBigGrpImg51($group->image, $group->id);?>" alt="<?php echo $group->title;?>" title="<?php echo $group->title;?>" />
	</a>
	<?php }else{?>  
	<a href="<?php echo $profilelink;?>">
	<img src="<?php echo AwdwallHelperUser::getBigAvatar51($this->msgs[$i]->commenter_id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?>"  height="50" width="50"/>
	<?php 
	if(AwdwallHelperUser::isSocialfeed($this->msgs[$i]->id)) 
	{?>
		<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo AwdwallHelperUser::isSocialfeed($this->msgs[$i]->id); ?>.png" class="post_type_icon"  />
	<?php }	?>
	  </a>	
	<?php }?>  
	<?php }?> 
	  </div>
    </div>
	<?php }?>
    <div class="<?php echo $rightColumnCss;?>" style="width:400px;"><span class="tl2"></span><span class="bl2"></span><span class="tr2"></span><span class="br2"></span>
      <div class="right_mid_content">
		<?php echo $pmText;?>
        <ul class="walltowall">
<?php if((int)$this->msgs[$i]->commenter_id != (int)$this->msgs[$i]->user_id && (int)$this->msgs[$i]->user_id && $this->layout != 'mywall' && !$this->groupId){?>
	<li>
	<?php
	if($this->msgs[$i]->type != 'like' && $this->msgs[$i]->type != 'friend' && $this->msgs[$i]->type != 'group'){
	echo JText::sprintf($textWall, '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '" >' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>', '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->user_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->user_id) . "'s" . '</a>');
	}else{
		echo $likeTxt;
	}
?>
	&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);?></span></li>
<?php }elseif((int)$this->msgs[$i]->commenter_id != (int)$this->msgs[$i]->user_id && ((int)$this->msgs[$i]->user_id != $this->wuid) && $this->layout == 'mywall' && !$this->groupId){
if($showLeft){
?>
<li><a style="font-size:12px;" href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false);?>" class="john"><?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?></a></li>
	<li><a style="font-size:12px;" href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->user_id . '&Itemid=' . $Itemid, false);?>"><?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->user_id);?></a> &nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);?></span></li>
<?php }else{
if($this->msgs[$i]->type == 'like' || $this->msgs[$i]->type == 'friend' || $this->msgs[$i]->type == 'group'){
		echo '<li>' . $likeTxt . '</li>';
	}else{
?>
<li><?php 
echo JText::sprintf($textWall, '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '" >' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>', '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->user_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->user_id) . "'s" . '</a>');?>
</li>
<?php }}?>
<?php }else{ ?>
 <?php if(!(int)$this->msgs[$i]->group_id){
 if($this->msgs[$i]->type == 'like' || $this->msgs[$i]->type == 'friend' || $this->msgs[$i]->type == 'group'){
		echo '<li>' . $likeTxt . '</li>';
	}else{
 ?>
			<li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false);?>"><?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?></a>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);?></span></li>
<?php } }else{
if($this->msgs[$i]->type == 'group'){
	echo '<li>' . $likeTxt . '</li>';
	}else{
if($owner){ 
	if($this->layout == 'mywall'){
		echo '<li>' . $wroteOngroupTxt . '</li>';
	}else{
?>
	<li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->msgs[$i]->group_id. '&Itemid=' . $Itemid, false);?>"><?php echo $group->title;?></a>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);?></span></li>
<?php }}else{
if(!$vowner){
	if($this->msgs[$i]->commenter_id == $this->wuid){
?>
<li><?php echo $wroteOngroupTxt;?></li>
<?php }else{
if($this->layout == 'group' || $owner){
?>
<li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->msgs[$i]->group_id. '&Itemid=' . $Itemid, false);?>"><?php echo $group->title;?></a>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);?></span></li>
<?php }else{?>
<li><?php echo $wroteOngroupTxt;?>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);?></span></li>	
	<?php }}?>
<?php }else{
if($this->msgs[$i]->group_id){
?>
<li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->msgs[$i]->group_id. '&Itemid=' . $Itemid, false);?>"><?php echo $group->title;?></a>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);?></span></li>
<?php }else{?>
<li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false);?>"><?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?></a>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);?></span></li>
<?php }}?>

<?php }?>
<?php }}?>
<?php }?>
        </ul>
        <div class="commentinfo"> 
<!-- start link and video of text -->
	<?php if($this->msgs[$i]->type == 'text'){?>
		<?php
				$query = "SELECT * FROM #__awd_wall_videos WHERE wall_id='".$this->msgs[$i]->id."'";
				$db->setQuery($query);
				$linkvideo = $db->loadObjectList();
				$video = $linkvideo[0];
			?>
			<?php if($video->id){?>

				
					<div class="whitebox video">
					<div style="overflow:hidden;" id="video_block_<?php echo $this->msgs[$i]->id;?>">
					<div class="clearfix">
					<div class="imagebox">
				<?php
				if($this->hwdvideoshare)
				{
					$query = "SELECT hwdviodeo_id FROM #__awd_wall_videos_hwd WHERE wall_id='".$this->msgs[$i]->id."'";
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
    
        if($this->videoLightbox){
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
    <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $this->msgs[$i]->id;?>&iframe=true&tmpl=component', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>" ><?php echo JText::_('View video');?></a>
    <?php
    }
        }else{	
            if($video->type == 'youtube'){
    
    ?>
        <a class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.youtube.com/v/<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
        
    <?php
    
            }elseif($video->type == 'vimeo'){	
    ?>
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://vimeo.com/moogaloop.swf?clip_id=<?php echo $video->video_id?>&amp;autoplay=1" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php		
    
            }elseif($video->type == 'myspace'){
    ?>	
    
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://mediaservices.myspace.com/services/media/embed.aspx/m=<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }elseif($video->type == 'metacafe'){
    
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.metacafe.com/fplayer/<?php echo $video->video_id;?>.swf" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }elseif($video->type == 'howcast'){
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.howcast.com/flash/howcast_player.swf?file=<?php echo $video->video_id;?>&amp;theme=black" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
    
            }elseif($video->type == 'blip'){
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://a.blip.tv/scripts/flash/stratos.swf#file=http://blip.tv/rss/flash/<?php echo $video->video_id;?>&autostart=true&showinfo=false" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }else
            {
        ?>
    <a href="javascript:void(0);" onclick="showlinevideo('<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $this->msgs[$i]->id;?>&tmpl=componen','<?php echo $this->msgs[$i]->id;?>')" ><?php echo JText::_('View video');?></a>
        
        <?php	
            
            }	
                
        
        }
    ?>		 
					</div>
							<div class="maincomment">
							  <h3><?php echo $video->title;?></h3>
							  <p><?php echo substr($video->description,0,200);?></p>
							  <font color="#9997ac"><?php echo JText::_('Length');?>: </font><?php echo AwdwallHelperUser::formatDuration((int)($video->duration), 'HH:MM:SS');?>

							  </div>			  
					  </div>

					<div style="width:455px; float: left; margin-top: 16px;" id="video_<?php echo $this->msgs[$i]->id;?>"></div>

					</div>
				</div>
			<?php } ?>


			<!-- start link block -->
			<?php 
				$query = "SELECT * FROM #__awd_wall_links WHERE wall_id='".$this->msgs[$i]->id."'";
				$db->setQuery($query);
				$checklink = $db->loadObjectList();
				$link = $checklink[0];
				$str=$link->link;
				$str=str_replace('www.','',$str);
				$str=str_replace('http://','',$str);
				$str='http://www.'.$str;
				$link->link=$str;
			?>
			<?php if($link->id){?>
				<div class="whitebox video">
					<div id="video_block_<?php echo $this->msgs[$i]->id;?>">
					<div class="clearfix">
					<?php if($link->path != ''){?>
						<div class="imagebox">
							<img src="<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $link->path;?>" title="<?php echo $link->title;?>" alt="<?php echo $link->description;?>"  /> <br />
							<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /> 
							<?php if($this->imageLightbox){ ?>
								<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/original/<?php echo $link->path;?>', '<?php echo $link->name;?>', '<?php echo $link->description;?>');" title="<?php echo $link->name;?>"><?php echo JText::_('View link');?></a>
							<?php } else echo '<a href="' . JRoute::_('index.php?option=com_awdwall&task=viewimage&wuid=' . $this->msgs[$i]->user_id . '&imageid=' . $link->id . '&Itemid=' . $Itemid) . '" >' . JText::_('View link') . '</a>'; ?>		 
						</div>
						<div class="maincomment">
							<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->title;?></a></h3>
							<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->link;?></a></h3>		<p><?php echo $link->description;?></p>             
						</div>
					<?php }else{ ?>
						<div class="maincomment_noimg">
							<?php if($link->link_img){?>
							<div class="maincomment_noimg_left">
								<a target="blank" href="<?php echo $link->link;?>" alt=""><img src="<?php echo $link->link_img;?>" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /></a>
							</div>
							<?php } ?>
							<div class="maincomment_noimg_right">
								<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->title;?></a></h3>
								<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->link;?></a></h3>
								<p><?php echo $link->description;?></p>             
							</div>
							<?php if($link->title){?>
							<div style=" margin-top:3px; width:100%; clear:both;"><div style="float:left; width:5%;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('View link');?>" align="absmiddle" alt="<?php echo JText::_('View link');?>" style="height:20px; width:20px; clear:right;" /></div><div style=" float:left; margin-left:5px; width:90%;"><a href="<?php echo $link->link;?>" target="blank" style="margin:0px; padding:0; font-size:9px; font-weight:normal;"><?php echo $link->title;?></a></div></div>
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
<?php if($this->msgs[$i]->type == 'video' ){?>
	<div class="whitebox video">
	<div style="overflow:hidden;" id="video_block_<?php echo $this->msgs[$i]->id;?>">
	<div class="clearfix">
            <div class="imagebox">
<?php
if($this->hwdvideoshare)
{
	$query = "SELECT hwdviodeo_id FROM #__awd_wall_videos_hwd WHERE wall_id='".$this->msgs[$i]->id."'";
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
<br /> <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-videos.png" title="<?php echo JText::_('View video');?>" alt="<?php echo JText::_('View video');?>" /> 
	<?php 
    
        if($this->videoLightbox){
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
    <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $this->msgs[$i]->id;?>&iframe=true&tmpl=component', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>" ><?php echo JText::_('View video');?></a>
    <?php
    }
        }else{	
            if($video->type == 'youtube'){
    
    ?>
        <a class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.youtube.com/v/<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
        
    <?php
    
            }elseif($video->type == 'vimeo'){	
    ?>
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://vimeo.com/moogaloop.swf?clip_id=<?php echo $video->video_id?>&amp;autoplay=1" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php		
    
            }elseif($video->type == 'myspace'){
    ?>	
    
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://mediaservices.myspace.com/services/media/embed.aspx/m=<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }elseif($video->type == 'metacafe'){
    
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.metacafe.com/fplayer/<?php echo $video->video_id;?>.swf" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }elseif($video->type == 'howcast'){
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.howcast.com/flash/howcast_player.swf?file=<?php echo $video->video_id;?>&amp;theme=black" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
    
            }elseif($video->type == 'blip'){
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://a.blip.tv/scripts/flash/stratos.swf#file=http://blip.tv/rss/flash/<?php echo $video->video_id;?>&autostart=true&showinfo=false" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }else
            {
        ?>
    <a href="javascript:void(0);" onclick="showlinevideo('<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $this->msgs[$i]->id;?>&tmpl=componen','<?php echo $this->msgs[$i]->id;?>')" ><?php echo JText::_('View video');?></a>
        
        <?php	
            
            }	
                
        
        }
    ?>		 
	</div>
            <div class="maincomment">
              <h3><?php echo $video->title;?></h3>
              <p><?php echo substr($video->description,0,200);?></p>
              <font color="#9997ac"><?php echo JText::_('Length');?>: </font><?php echo AwdwallHelperUser::formatDuration((int)($video->duration), 'HH:MM:SS');?>
			  </div>			  
      </div>
	  <div style="width:455px; float: left; margin-top: 16px;" id="video_<?php echo $this->msgs[$i]->id;?>"></div>
	</div>
</div>
<?php }?>
<!-- end video block -->
<!-- start image block -->
<?php if($this->msgs[$i]->type == 'image' ){?>
<div class="whitebox video">
	<div id="video_block_<?php echo $this->msgs[$i]->id;?>">
	<div class="clearfix">
            <div class="imagebox">
			<?php
			$pid=$image->id;
			$image_uid=$this->msgs[$i]->user_id;
			//$imglink=JRoute::_('index.php?option=com_awdjomalbum&view=awdwallimagelist&wuid='.$image_uid.'&pid='.$pid.'&Itemid='.$Itemid,false);	//echo $imglink;
			$imglink=JRoute::_("index.php?option=com_awdjomalbum&view=awdwallimagelist&tmpl=component&wuid=".$image_uid."&pid=".$pid."&Itemid=".AwdwallHelperUser::getComItemId());

			if($this->jomalbumexist)
			{?>
			<a href="<?php echo $imglink;?>" class="awdiframe">
				<img src="<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $image->path;?>" title="<?php echo $image->name;?>" alt="<?php echo $image->name;?>" /> <br />
			</a>
			<?php }
			else {?>
            <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/original/<?php echo $image->path;?>', '<?php echo $image->name;?>', '<?php echo $image->name;?>');" title="<?php echo $image->name;?>">
				<img src="<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $image->path;?>" title="<?php echo $image->name;?>" alt="<?php echo $image->name;?>" /> <br />
                </a>
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
<?php if($this->msgs[$i]->type == 'link' ){
				$str=$link->link;
				$str=str_replace('www.','',$str);
				$str=str_replace('http://','',$str);
				$str='http://www.'.$str;
				$link->link=$str;
?>
<div class="whitebox video">
	<div id="video_block_<?php echo $this->msgs[$i]->id;?>">
	<div class="clearfix">
<?php if($link->path != ''){?>
        <div class="imagebox"><img src="<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $link->path;?>" title="<?php echo $link->title;?>" alt="<?php echo $link->description;?>"  /> <br />
            <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /> 
<?php 
	if($this->imageLightbox){
?>
<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/original/<?php echo $link->path;?>', '<?php echo $link->name;?>', '<?php echo $link->description;?>');" title="<?php echo $link->name;?>"><?php echo JText::_('View link');?></a>
<?php
	}else
		echo '<a href="' . JRoute::_('index.php?option=com_awdwall&task=viewimage&wuid=' . $this->msgs[$i]->user_id . '&imageid=' . $link->id . '&Itemid=' . $Itemid, false) . '" >' . JText::_('View link') . '</a>';
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
			<div class="maincomment_noimg_left">
				<a target="blank" href="<?php echo $link->link;?>" alt=""><img src="<?php echo $link->link_img;?>" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /></a>
			</div>
			<?php } ?>
			
			<div class="maincomment_noimg_right">
				<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->title;?></a></h3>
				<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->link;?></a></h3>
				<p><?php echo $link->description;?></p>             
			</div>
<?php if($link->title){?>
<div style=" margin-top:3px; width:100%; clear:both;"><div style="float:left; width:5%;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('View link');?>" align="absmiddle" alt="<?php echo JText::_('View link');?>" style="height:20px; width:20px; clear:right;" /></div><div style=" float:left; margin-left:5px; width:90%;"><a href="<?php echo $link->link;?>" target="blank" style="margin:0px; padding:0; font-size:9px; font-weight:normal;"><?php echo $link->title;?></a></div></div>
<?php } ?>
		</div>
<?php }?>
	</div>
	</div>
</div>
<?php }?>
<!-- end link block -->
<!-- start mp3 block -->
<?php if($this->msgs[$i]->type == 'mp3' ){
		$parsedVideoLink	= parse_url($mp3->path);
		preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $parsedVideoLink['host'], $matches);
		$domain		= $matches['domain'];
		
		if($domain!='soundcloud.com')
		{

?>
<div class="whitebox video">
	<div id="video_block_<?php echo $this->msgs[$i]->id;?>">
	<div class="clearfix">
	<div class="title-mus">	
		<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-music.png" title="<?php echo JText::_('Music');?>" alt="<?php echo JText::_('Music');?>" style="float:left;" />
		<h3 style="font-size:13px;font-weight:bold;margin:0;padding:3px 0px 6px 5px;"> &nbsp;<?php echo $mp3->title;?></h3>
	</div>
    <div class="imagebox">	
	<script type="text/javascript">
AC_FL_RunContent( 'width','200','height','24','id','audioplayer1','data','<?php echo JURI::base();?>components/com_awdwall/js/player.swf','type','application/x-shockwave-flash','movie','<?php echo JURI::base();?>components/com_awdwall/js/player','flashvars','playerID=1&soundFile=<?php echo JURI::base();?>images/mp3/<?php echo $this->msgs[$i]->user_id;?>/<?php echo $mp3->path;?>','quality','high','menu','true','wmode','transparent' ); //end AC code
</script><noscript><object width="200" height="24" id="audioplayer1" data="<?php echo JURI::base();?>components/com_awdwall/js/player.swf" type="application/x-shockwave-flash">
<param value="<?php echo JURI::base();?>components/com_awdwall/js/player.swf" name="movie">
<param value="playerID=1&amp;soundFile=<?php echo JURI::base();?>images/mp3/<?php echo $this->msgs[$i]->user_id;?>/<?php echo $mp3->path;?>" name="FlashVars">
<param value="high" name="quality">
<param value="true" name="menu">
<param value="transparent" name="wmode">
</object></noscript><br />
	</div>
		<div class="maincomment">		  		
		 </div>
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
if($this->msgs[$i]->type =='jing' )
{
?>
<div class="whitebox video">
	<div style="overflow:hidden;" id="jing_block_<?php echo $this->msgs[$i]->id;?>">
	<div class="clearfix">
            <div class="imagebox" >
			<script language="javascript">
			getJingThumbData('<?php echo $jing->id;?>');
			</script>
			<div id="sceenthumb_<?php echo $jing->id;?>">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></div>
			 <br />
            <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-jing.png" title="<?php echo JText::_('View Jing');?>" alt="<?php echo JText::_('View Jing');?>" /> 
<?php 
	if($this->jingLightbox){
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
	<div style="width:455px; float: left; margin-top: 16px;display:none;" id="jingp_<?php echo $jing->id;?>" >
	&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" />
	</div>

	</div>
</div>


<?php /*?><script language="javascript">
getJingData('<?php echo $jing->id;?>');
</script>
<div id="jing<?php echo $jing->id;?>"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" title="<?php echo JText::_('Loading');?>" alt="<?php echo JText::_('Loading');?>" border="0" /></div>
<br />
<a href="<?php echo $jing->jing_link;?>" title="<?php echo $jing->jing_title;?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-jing.png" title="<?php echo JText::_('Jing');?>" alt="<?php echo JText::_('Jing');?>" border="0" height="14" />&nbsp;&nbsp;&nbsp;<?php echo JText::_('View Jing');?></a>
<?php */?><?php 
}
?>
<!-- end of jing block -->

<!-- start event block -->

<?php if($this->msgs[$i]->type == 'event' ){
$startd=explode("\n",$event->start_time);
$endd=explode("\n",$event->end_time);
$starttime=$startd[1].' '.$startd[2].' '.date('l, j-M-y',strtotime($startd[0]));
$endtime=$endd[1].' '.$endd[2].' '.date('l, j-M-y',strtotime($endd[0]));
?>

<div class="whitebox event">

	<div id="event_block_<?php echo $this->msgs[$i]->id;?>">

	<div class="clearfix">

    <div class="imagebox" style="margin-top:10px; margin-bottom:10px;">
	
<?php
	if($event->image)
	{?>
	<img src="<?php echo JURI::base();?>images/awd_events/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $event->image;?>" title="<?php echo $event->title;?>" alt="<?php echo $event->title;?>" /></a>
<?php }
	else {?>
	<img src="<?php echo JURI::base();?>components/com_awdwall/images/event.png" title="<?php echo $event->title;?>" alt="<?php echo $event->title;?>" />
	<?php }?>

	</div>
	<?php
	
	?>
		<div class="maincomment" style="margin-top:10px;">

			<h3><?php echo $event->title;?></h3>
			<p><font><?php echo JText::_('Location');?>: </font><?php echo $event->location;?></p>   
			<font><?php echo JText::_('Time');?>: </font>  
			<p><font><?php echo JText::_('Start');?>: </font><?php echo $starttime;?><br />
			<font><?php echo JText::_('End');?>: &nbsp;</font><?php echo $endtime;?></p>   
			
			<p><?php echo nl2br($event->description);?></p>  
<?php
	$canAttend = $this->wallModel->getEventOfMsgOfUser($this->msgs[$i]->id,$user->id);
	if(!$canAttend){
?>
			<p><?php echo JText::_('Are you coming ?');?>
			<select name="attend_event" id="attend_event" onchange="attendEvent('<?php echo 
 'index.php?option=com_awdwall&view=awdwall&task=addEventAttend&wid=' . (int)$this->msgs[$i]->id . '&cid=' .(int)$this->msgs[$i]->commenter_id.'&eventId=' . (int)$event->id .'&tmpl=component';?>', '<?php echo  'index.php?option=com_awdwall&view=awdwall&task=getEventAttend&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component&Itemid='.$Itemid;?>',<?php echo $this->msgs[$i]->id;?>);">
				<option value=""><?php echo JText::_('Select');?></option>
				<option value="1"><?php echo JText::_('JYES');?></option>
				<option value="0"><?php echo JText::_('JNO');?></option>
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

<?php if($this->msgs[$i]->type == 'article')
{?>

<div class="whitebox article">

	<div id="article_block_<?php echo $this->msgs[$i]->id;?>">

	<div class="clearfix">
	
<?php
	if($article->image)
	{?>
    <div class="imagebox" style="margin-top:10px; margin-bottom:10px;">
	
	<a href="<?php echo JURI::base();?>index.php?option=com_content&view=article&id=<?php echo $article->article_id;?>&Itemid=<?php echo $Itemid;?>" >
		<img src="<?php echo JURI::base();?>images/awd_articles/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $article->image;?>" title="<?php echo $article->title;?>" alt="<?php echo $article->title;?>" style="max-height:84px; max-width:112px;" />
	</a>

	</div>

		<div class="maincomment" style="margin-top:10px;">
			<a href="<?php echo JURI::base();?>index.php?option=com_content&view=article&id=<?php echo $article->article_id;?>&Itemid=<?php echo $Itemid;?>" ><h3><?php echo $article->title;?></h3></a>
			
			<p>
			<?php 
			$loadjomwall='{loadjomwall}';
			//echo $loadjomwall;
			if(strpos($article->description,$loadjomwall)!==false) {
				echo str_replace($loadjomwall," ",$article->description);
			}
			else {
				echo $article->description;
			}?></p>  
		 </div>

<?php } else {?>
	
		<div class="maincomment" style="margin-top:10px; float:left; width:98%">
			<a href="<?php echo JURI::base();?>index.php?option=com_content&view=article&id=<?php echo $article->article_id;?>&Itemid=<?php echo $Itemid;?>" ><h3><?php echo $article->title;?></h3></a>
			
			<p><br />
			<?php 
			$loadjomwall='{loadjomwall}';
			//echo $loadjomwall;
			if(strpos($article->description,$loadjomwall)!==false) {
				echo str_replace($loadjomwall," ",$article->description);
			}
			else {
				echo $article->description;
			}?></p>  
	</div>
	<?php }?>
	</div>
	</div>

</div>

<?php }?>

<!-- end article block -->

<!-- start of trail block -->
<?php 
if($this->msgs[$i]->type == 'trail')
{
?>
<a href="<?php echo $trail->trail_link;?>" target="_blank" style="font-size:12px;"><?php echo $trail->trail_title;?></a><br><br><iframe src="http://www.everytrail.com/iframe2.php?trip_id=<?php echo str_replace('http://www.everytrail.com/view_trip.php?trip_id=','',$trail->trail_link);?>&width=400&height=300" marginheight="0" marginwidth="0" frameborder="0" scrolling="no" width="400" height="300"></iframe>
<?php 
}
?>
<!-- end of trail block -->

<!-- start file block -->
<?php if($this->msgs[$i]->type == 'file' ){?>
<div class="whitebox video">
	<div id="video_block_<?php echo $this->msgs[$i]->id;?>">
	<div class="clearfix">
    <div class="imagebox"><a href="<?php echo JURI::base();?>images/awdfiles/<?php echo $this->msgs[$i]->user_id;?>/<?php echo $file->path;?>" target="_blank">
	<img src="<?php echo JURI::base();?>components/com_awdwall/images/file.png" title="<?php echo JText::_('Download');?>" alt="<?php echo JText::_('Download');?>" /></a>
	<br /><span style="padding-left:12px;font-weight:bold;color:#308CB6;"><a href="<?php echo JURI::base();?>images/awdfiles/<?php echo $this->msgs[$i]->user_id;?>/<?php echo $file->path;?>" target="_blank"><?php echo JText::_('Download');?></a></span>
	</div>
		<div class="maincomment">
			<h3><?php echo $file->title;?></h3>
		 </div>
	</div>
	</div>
</div>
<?php }?>
<!-- end file block -->

<!--start comment block -->

   <!-- start older comments-->
	</div>
	<!--end comment block-->	
    </div>
	</div>
</div>
</div><?php if(!$this->msgs[$i]->group_id){?>
<?php if(count($friends)){?>
<div style="clear:both; height:5px;"></div>
<span style="font-size:11px; font-weight:bold;">
<?php 
echo JText::sprintf('COM_COMAWDWALL_FRIEND_LIST', AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id));
?>
</span>
<div style="clear:both; height:5px;"></div>
<?php if(count($friends)){?>
<?php foreach($friends as $friend){?>
		  <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $friend->connect_to .'&Itemid=' . $Itemid, false);?>" style="text-decoration:none;">
		<img src="<?php echo AwdwallHelperUser::getBigAvatar32($friend->connect_to);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($friend->connect_to);?>" title="<?php echo AwdwallHelperUser::getDisplayName($friend->connect_to);?>" style="float:left;margin-top:0px;margin-right:3px;margin-left:3px;box-shadow: 0px 0px 3px #ccc;"  height="32" width="32"/></a>
<?php } ?>
<?php } ?>
<?php } ?>
<?php } ?>
<?php } }?>
