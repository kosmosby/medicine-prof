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
// include js and css
require_once(JPATH_COMPONENT . DS . 'js' . DS . 'include.php');
//$Itemid = AwdwallHelperUser::getComItemId();
$Itemid = AwdwallHelperUser::getComItemId();
// get user object
$user = &JFactory::getUser();
//$friendJsUrl = 'index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid;
$friendJsUrl = JRoute::_('index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid);
$groupsUrl = JRoute::_('index.php?option=com_awdwall&task=groups&Itemid=' . $Itemid, false);
$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';
if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
{
	$showalbumlink=true;
	$infolink=JRoute::_("index.php?option=com_awdjomalbum&view=userinfo&wuid=".$user->id."&Itemid=".$Itemid, false);
	$albumlink=JRoute::_("index.php?option=com_awdjomalbum&view=awdalbumlist&wuid=".$user->id."&Itemid=".$Itemid, false);
}
else
{
	$showalbumlink=false;
	$infolink='';
	$albumlink="";
}

//$config = &JComponentHelper::getParams('com_awdwall');
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
$width 			= $config->get('width', 725);
$display_name 	= $config->get('display_name', 1);
$display_gallery = $config->get('display_gallery', 1);
$display_mywalluserimages 		= $config->get('display_mywalluserimages', 0);
$display_mywallusermp3 			= $config->get('display_mywallusermp3', 0);
$display_mywalluserfiles 		= $config->get('display_mywalluserfiles', 0);
$display_mywalluservideos 		= $config->get('display_mywalluservideos', 0);
$display_mywalluserlinks 		= $config->get('display_mywalluserlinks', 0);
$display_mywallusertrails 		= $config->get('display_mywallusertrails', 0);
$display_mywalluserjings 		= $config->get('display_mywalluserjings', 0);
$display_mywalluserevents 		= $config->get('display_mywalluserevents', 0);
$display_mywalluserarticles 	= $config->get('display_mywalluserarticles', 0);


?>
<style type="text/css">
	#awd-mainarea .wallheadingRight ul li a, #awd-mainarea .wallheadingRight ul li.separator{
		color:#<?php echo $this->color[1]; ?>;
	}
	#awd-mainarea .profileLink a, #awd-mainarea .profileMenu .ppm a, #awd-mainarea #msg_content .rbroundboxleft .mid_content a, #awd-mainarea .commentinfo a, #awd-mainarea .user_place ul.profileMenu li a{
		color:#<?php echo $this->color[2]; ?>!important;
	}
	#awd-mainarea #awd_message, #awd-mainarea .right_mid_content .walltowall li{
		color:#<?php echo $this->color[3]; ?>!important;
	}
	#awd-mainarea .wall_date{
		color:#<?php echo $this->color[4]; ?>;
	}
	#awd-mainarea .rbroundboxright, #awd-mainarea .fullboxtop, #awd-mainarea .rbroundboxleft_user, #awd-mainarea .lightblue_box, #awd-mainarea .rbroundboxrighttop{
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
	#awd-mainarea .whitebox, #awd-mainarea .blog_content, #awd-mainarea .about_me{
		background-color:#<?php echo $this->color[14]; ?>;
	}
	<?php }?>
	#awd-mainarea .rbroundboxleft .mid_content a, #awd-mainarea .blog_groups, #awd-mainarea .blog_groups a, #awd-mainarea .blog_friend, #awd-mainarea .blog_friend a, #awd-mainarea a.authorlink{
		color:#<?php echo $this->color[6]; ?>;
	}
	#awd-mainarea #msg_content .rbroundboxleft,#awd-mainarea  #msg_content .awdfullbox{
		background-color:#<?php echo $this->color[7]; ?>;
	}
	#awd-mainarea .walltowall li a, #awd-mainarea  #msg_content .maincomment_noimg_right h3 a{
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
	<?php if($template!='default'){?>
	#awd-mainarea .wallheading, #awd-mainarea .wallheadingRight{
		background-color:#<?php echo $this->color[13]; ?>;
	}
	#awd-mainarea .round, #awd-mainarea .search_user{
		background-color:#<?php echo $this->color[14]; ?>;
	}
	#awd-mainarea .whitebox, #awd-mainarea .blog_content, #awd-mainarea .about_me{
		background-color:#<?php echo $this->color[12]; ?>;
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
#awd-mainarea  #dropAlerts .notiItem a{
color:#<?php echo $this->color[2]; ?>!important;
}

<?php if($template!='default'){?>
#awd-mainarea .rbroundboxleft .mid_content .totalno{
	background:#<?php echo $this->color[12]; ?>;
    border: 1px solid #<?php echo $this->color[2]; ?>;
    color: #<?php echo $this->color[8]; ?>;
    float: right;
    margin-right: 5px;
    margin-top: 0;
    padding: 0 3px;
    text-align: center;
}
<?php }else{?>
#awd-mainarea  .totalno{
   background: red;
    -o-border-radius: 7px;
    -ms-border-radius: 7px;
    -moz-border-radius: 7px;
    -webkit-border-radius: 7px;
    border-radius: 7px;
    -o-box-shadow: 0 0 3px rgba(0,0,0,0.3);
    -ms-box-shadow: 0 0 3px rgba(0,0,0,0.3);
    -moz-box-shadow: 0 0 3px rgba(0,0,0,0.3);
    -webkit-box-shadow: 0 0 3px rgba(0,0,0,0.3);
    box-shadow: 0 0 3px rgba(0,0,0,0.3);
    color: #FFF;
    float: right;
    margin-right: 5px;
    margin-top: 0;
    padding: 0 3px;
    line-height: 11px;
    height: 11px;
	margin-top:-14px;
	font-size:11px;
	}
<?php } ?>
#awd-mainarea ul.profileMenu li {
    margin-bottom: 5px;
}
#awd-mainarea .mid_content p
{
min-height:16px!important;
}
#awd-mainarea .rbroundboxleft .mid_content .myavtar
{max-width:133px!important; }

.jplist .awdpagination span{
	color: #<?php echo $this->color[4]; ?>!important;
	border:1px solid #<?php echo $this->color[4]; ?>!important;
}

.jplist .awdpagination  span.current{
	font-weight: bold!important;
	color: #<?php echo $this->color[8]; ?>!important;
}



</style>
<div style="width:100%" id="awd-mainarea">
  <div class="wallheading">
    <div class="wallheadingRight">

      <ul>
      <?php if($template=='default'){?>
		<li style="padding:0px 4px!important;">
          <div class="searchWall">
            <form action="#" name="frm_auto_search" id="frm_auto_search" method="post">
              <input id="search_user" name="search_user" class="search_user ac_input" type="text" />
            </form>
          </div>
        </li>
		<?php }?>
		<?php $layout = $_REQUEST['layout']; ?>
		<li class="logo"><img src="components/com_awdwall/images/awdwall.png" alt="AWDwall" title="AWDwall"></li>

        <li <?php if($layout == 'main') {echo 'class="activenews"';}else{echo 'class="newsfeed"';} ?>><a <?php if($layout == 'main') {echo 'class="activenews"';}else{echo 'class="newsfeed"';} ?> href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('News Feed');?>" ></a> </li>

		<?php if((int)$user->id){?>
        <?php if($template=='default'){?>
        <li style="float:left; display:block; position:relative;">
            <a href="#" title="Notifications" id="notifications-button">
                <img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/8.png" alt=""/>
                <span class="message-count" style="display:none"></span>
            </a>
        </li>
        <?php }?>
        <li class="separator"> </li>
        <li><a href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('Friends');?>"><?php echo JText::_('Friends');?> <?php if($template!='default'){?><font color="red" size="1"><?php if((int)$this->pendingFriends > 1) echo '(' . $this->pendingFriends . JText::_('Requests') . ')';elseif((int)$this->pendingFriends == 1) echo '(' . $this->pendingFriends . JText::_('') . ')';?></font><?php }?></a></li>
		<?php if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) {?>
        
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
         <li> <a href="<?php echo JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $user->id . '&Itemid=' . AwdwallHelperUser::getJsItemId(), false);?>" title="<?php echo JText::_('Account');?>"><?php echo JText::_('Account');?></a> </li>
		 
		
		 <?php if($template!='default'){?>
        <li class="separator"> </li>
		<li style="padding-top:0px;">
          <div class="searchWall">
            <form action="#" name="frm_auto_search" id="frm_auto_search" method="post">
              <input id="search_user" name="search_user" class="search_user ac_input" type="text" />
            </form>
          </div>
        </li>
       <li class="separator"> </li>
		<li class="no"> <a href="JavaScript:void(0);" id="awdnoticealert">
<img src="<?php echo JURI::base();?>components/com_awdwall/images/alertnoticeoff.png" id="alertimg" alt="Notification" /></a>
			<ul>
				<li> 
					<div style="display: none; " id="dropAlerts">
					  <div class="notiItemsWrap">
						<div class="txtWrap"><center><?php echo JText::_("No new Notice");?></center></div>
					  </div>
					</div>
				</li>
			</ul>  
		</li>
		<?php }?>

		
        <li class="no signout" style="float:right;"> <a href="javascript:void(0)" title="<?php echo JText::_('Sign out');?>"  onclick="awdsignout();"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/logoutbutton.png" alt="<?php echo JText::_('Sign out');?>" title="<?php echo JText::_('Sign out');?>" class="imglogout" /></a> </li>
		<li style="float:right;" class="toolbaravtar">
        <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('My Wall');?>"  <?php if($layout == 'mywall') {echo 'class="active"';} ?>>
        <div style="height:32px; margin-right:15px; ">
        <div style=" float:left; width:32px; height:32px;box-shadow: 0px 0px 3px #fff;"><img src="<?php echo AwdwallHelperUser::getBigAvatar32($user->id);?>" class="avtartool "  height="32" width="32"/></div>
        <div style=" float:left; width:auto; margin-left:6px;padding-top:3px; height:32px;"><?php if((int)$this->displayName == USERNAME) {?>
				<?php echo substr(JText::sprintf('Users Wall', $user->username),0,15);?>
                <?php }else{?>
                <?php echo substr(JText::sprintf('Users Wall', $user->name),0,15);?>
            <?php }?></div>
        </div>
        </a>
        </li>
        <?php }?>
      </ul>

    </div>
  </div>
  
 <div class="awdfullbox fullboxtop  clearfix"> <span class="bl"></span>
 <?php if($template=='default'){?>
 <div class="rbroundboxleft_user">
      <div class="user_place"> 
	  
	  			<img src="<?php echo AwdwallHelperUser::getBigAvatar133($user->id);?>"  title="<?php echo AwdwallHelperUser::getDisplayName($user->id);?>" width="133" />
	   
	   <br />
        <br />
        <?php if($template!='default'){?>
		<?php if($showalbumlink){?>
		   <p ><a href="<?php echo JRoute::_($infolink);?>" class="minfo"><?php echo JText::_('Info');?></a></p>
<?php  if($display_gallery==1){?>
		   <p ><a href="<?php echo JRoute::_($albumlink);?>" class="mphoto"><?php echo JText::_('Photos');?></a></p>
	   <?php } ?>
	   <?php } ?>


		<p ><a href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('Friends');?>" class="friends"><?php echo JText::_('Friends');?></a>
		<?php if( $this->pendingFriends) {?>
			<span class="totalno"><?php echo $this->pendingFriends;?></span>
		<?php }?>
		</p>
		<?php if((int)$this->displayPm){?>
		<p ><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&task=pm&Itemid=' . $Itemid);?>" title="<?php echo JText::_('Messages');?>" class="mmessage"><?php echo JText::_('Messages');?></a>
		<?php if($this->totalpm){?>
			<span class="totalno"><?php echo $this->totalpm;?></span>
		<?php }?>
		</p>
	<?php }?>
	<?php if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) {?>
		<p ><a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=groups&Itemid='.$Itemid);?>" title="<?php echo JText::_('Groups');?>" class="groups"><?php echo JText::_('Groups');?></a>
		<?php if($this->pendingGroups){?>
			<span class="totalno"><?php echo $this->pendingGroups;?></span>
		<?php }?>
		</p>
		<p><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=group&task=newgroup&Itemid=' . $Itemid);?>"><?php echo JText::_('Create Group');?></a></p>
	<?php }?>
		<?php 
		if($this->hwdvideoshare){
			$hwdvideolink=JRoute::_("index.php?option=com_hwdvideoshare&task=viewChannel&sort=uploads&user_id=".$user->id."&Itemid=".$Itemid);
		?>
	   <p ><a href="<?php echo $hwdvideolink;?>" class="mvideo"><?php echo JText::_('Videos');?></a></p>
	   <?php } ?>
 <?php } else {?>
		<?php if($showalbumlink){?>
		   <p style="border-top: 1px solid #BBBBBB!important;"><a href="<?php echo JRoute::_($infolink);?>" class="minfo"><?php echo JText::_('Info');?></a></p>
		<?php  if($display_gallery==1){?>
		   <p style="border-top: 1px solid #BBBBBB!important;"><a href="<?php echo JRoute::_($albumlink);?>" class="mphoto"><?php echo JText::_('Photos');?></a></p>
	   <?php } ?>
	   <?php } ?>


		<p style="border-top: 1px solid #BBBBBB!important;"><a href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('Friends');?>" class="friends"><?php echo JText::_('Friends');?></a>
		<?php if( $this->pendingFriends) {?>
			<span class="totalno"><?php echo $this->pendingFriends;?></span>
		<?php }?>
		</p>
		<?php if((int)$this->displayPm){?>
		<p style="border-top: 1px solid #BBBBBB!important;"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&task=pm&Itemid=' . $Itemid);?>" title="<?php echo JText::_('Messages');?>" class="mmessage"><?php echo JText::_('Messages');?></a>
		<?php if($this->totalpm){?>
			<span class="totalno"><?php echo $this->totalpm;?></span>
		<?php }?>
		</p>
	<?php }?>
	<?php if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) {?>
		<p style="border-top: 1px solid #BBBBBB!important;"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=groups&Itemid='.$Itemid);?>" title="<?php echo JText::_('Groups');?>" class="groups"><?php echo JText::_('Groups');?></a>
		<?php if($this->pendingGroups){?>
			<span class="totalno"><?php echo $this->pendingGroups;?></span>
		<?php }?>
		</p>
		
	<?php }?>
		<?php 
		if($this->hwdvideoshare){
			$hwdvideolink=JRoute::_("index.php?option=com_hwdvideoshare&task=viewChannel&sort=uploads&user_id=".$user->id."&Itemid=".$Itemid);
		?>
	   <p style="border-top: 1px solid #BBBBBB!important;"><a href="<?php echo $hwdvideolink;?>" class="mvideo"><?php echo JText::_('Videos');?></a></p>
	   <?php } ?>
 <?php } ?>
        <br />
	   
	   
	   
	   
	   <br />
        <br />
		<div class="about_me clearfix">
		<div class="about_tr"> <div class="about_tl"></div></div>          
          <div class="about_content">
            <!--p class="title border"> <!--img src="<?php echo JURI::base();?>components/com_awdwall/images/icon_spen.gif" alt="Steve Stuart" align="right" border="0"><?php //echo JText::_('About Me');?></p--><p class="border"><strong><?php echo JText::_('Basic Information');?></strong></p>
			
            <dl class="profile-right-info">

			<?php if((int)$this->displayName == USERNAME) {?>
	
			<dt><?php echo JText::_('USERNAME');?></dt>
	
			<dd><?php echo AwdwallHelperUser::getDisplayName($user->id);?></dd>
	
			<?php }else{?>
	
	
			<dt><?php echo JText::_('USERNAME');?></dt>
	
			<dd><?php echo AwdwallHelperUser::getDisplayName($user->id);?></dd>
	
			<?php }?>
		<?php
		$cbfields=explode(",",$this->albumuserinfo->cbfields);
		if(is_array($this->basicInfo)){
		foreach($this->basicInfo as $arr){
		$cbff='display_'.str_replace(' ','',$arr[1]);
		if(in_array($cbff,$cbfields))
		{
		?>
		<dt><?php echo $arr[1];?></dt><dd>
		<?php echo $arr[0];?></dd>
		<?php
		}
		}
		}
		$userinfo=$this->userinfo;
		$userinfo=$this->userinfo;
		if(!empty($userinfo))
		{  ?>
        
			<?php if($userinfo->birthday != '0000-00-00' &&  $userinfo->display_birthday==1){?>
			<dt><?php echo JText::_('Birthday');?></dt>
			<dd><?php if($userinfo->hide_birthyear==1){echo date('jS F ', strtotime($userinfo->birthday));}else{echo date('jS F Y', strtotime($userinfo->birthday));}?></dd>
			<?php }?>
            
			<?php if($userinfo->workingat != '' &&  $userinfo->display_workingat==1){?>
			<dt><?php echo JText::_('Working At');?></dt>
			<dd><?php echo $userinfo->workingat;?></dd>
			<?php }?>
			
			
			<?php if($userinfo->studied != '' &&  $userinfo->display_studied==1){?>
			<dt><?php echo JText::_('Studied');?></dt>
			<dd><?php echo $userinfo->studied;?></dd>
			<?php }?>
			
			<?php if($userinfo->livein != '' &&  $userinfo->display_livein==1){?>
			<dt><?php echo JText::_('Live in');?></dt>
			<dd><?php echo $userinfo->livein;?></dd>
			<?php }?>
			
			
			<?php if($userinfo->phone != '' &&  $userinfo->display_phone==1){?>
			<dt><?php echo JText::_('Phone');?></dt>
			<dd><?php echo $userinfo->phone;?></dd>
			<?php }?>
			<?php if($userinfo->cell != '' &&  $userinfo->display_cell==1){?>
			<dt><?php echo JText::_('Cell');?></dt>
			<dd><?php echo $userinfo->cell;?></dd>
			<?php }?>
			
			<?php 
			$colvalue=AwdwallHelperUser::getUserinfocolname('col1');
			if($userinfo->col1 != '' &&  $userinfo->display_col1==1 && $colvalue){?>
	
				<dt><?php echo $colvalue;?></dt>
	
				<dd><?php echo $userinfo->col1;?></dd>
			<?php }?>
			
			<?php 
			$colvalue=AwdwallHelperUser::getUserinfocolname('col2');
			if($userinfo->col2 != '' &&  $userinfo->display_col2==1 && $colvalue){?>
				<dt><?php echo $colvalue;?></dt>
				<dd><?php echo $userinfo->col2;?></dd>
			<?php }?>
			
			<?php 
			$colvalue=AwdwallHelperUser::getUserinfocolname('col3');
			if($userinfo->col3 != '' &&  $userinfo->display_col3==1 && $colvalue){?>
	
				<dt><?php echo $colvalue;?></dt>
	
				<dd><?php echo $userinfo->col3;?></dd>
			<?php }?>
			
			<?php 
			$colvalue=AwdwallHelperUser::getUserinfocolname('col4');
			if($userinfo->col4 != '' &&  $userinfo->display_col4==1 && $colvalue){?>
	
				<dt><?php echo $colvalue;?></dt>
	
				<dd><?php echo $userinfo->col4;?></dd>
	
			<?php }?>
			
			<?php 
			$colvalue=AwdwallHelperUser::getUserinfocolname('col5');
			if($userinfo->col5 != '' &&  $userinfo->display_col5==1 && $colvalue){?>
	
				<dt><?php echo $colvalue;?></dt>
	
				<dd><?php echo $userinfo->col5;?></dd>
			<?php }?>
				<?php
				if(($userinfo->display_facebook_user==1 && $userinfo->facebook_user !='')  || ($userinfo->display_twitter_user==1 && $userinfo->twitter_user) || ($userinfo->display_youtube_user==1 && $userinfo->youtube_user))
					{?>
					<dt><?php echo JText::_('Social');?></dt>
					<?php }?>
					<dd>	
					<?php
					/*if($userinfo->skype_user && $userinfo->display_skype_user==1) {
						$isOnline=AwdwallHelperUser::IsSkypeOnline($userinfo->skype_user);
						echo $isOnline;
					}*/
					if($userinfo->facebook_user && $userinfo->display_facebook_user==1) {?>
						&nbsp;
						<a href="http://www.facebook.com/<?php echo $userinfo->facebook_user;?>" target="_blank">
							<img src="<?php JURI::base();?>components/com_awdwall/images/facebook_icon.jpg" /></a>
					<?php
					}
					if($userinfo->twitter_user && $userinfo->display_twitter_user==1) {?>
						&nbsp;<a href="http://www.twitter.com/<?php echo $userinfo->twitter_user;?>" target="_blank">
							<img src="<?php JURI::base();?>components/com_awdwall/images/twitter_icon.png" /></a>
					<?php
					}
					if($userinfo->youtube_user && $userinfo->display_youtube_user==1) {?>
						&nbsp;<a href="http://www.youtube.com/user/<?php echo $userinfo->youtube_user;?>" target="_blank">
							<img src="<?php JURI::base();?>components/com_awdwall/images/youtube_icon.jpg" /></a>
					<?php }?>
						</dd>
				
					<?php
			}
			?>
	    	</dl> 
			
           </div>
              <div class="about_br"> <div class="about_bl"></div></div>          
        </div>
       
        <br />
        <br />
<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('Friends');?>&nbsp;<?php if((int)$this->totalFriends) {?> <?php echo $this->totalFriends;?> <?php } ?></strong></p>
                <dl class="profile-right-info">	    
                    <dd>
                        <span style="float:left;"><?php /*?><?php echo $this->totalFriends;?> <?php echo JText::_('Friends');?><?php */?></span> 
                        <?php if((int)$this->totalFriends) {?>
                         <a href="<?php echo $friendJsUrl;?>"  title="<?php echo JText::_('Friends');?>"  style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a> 
                         <?php } ?>
                   
                    </dd>
                </dl>
                  
                 <?php if((int)$this->totalFriends) {?>		  
		  <?php $i = 1; foreach($this->leftFriends as $friend){
				$class = 'column1';
				if($i%2 == 0)
					$class = 'column2';
				$i++;
		  ?>
		  <div style="min-height:20px;">
		  <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $friend->connect_to .'&Itemid=' . $Itemid, false);?>">

		<img src="<?php echo AwdwallHelperUser::getBigAvatar19($friend->connect_to);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($friend->connect_to);?>" title="<?php echo AwdwallHelperUser::getDisplayName($friend->connect_to);?>" style="float:left;margin-top:0px;"  height="19" width="19" /></a>
		
		  <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $friend->connect_to .'&Itemid=' . $Itemid, false);?>"  style="text-decoration:none;margin-top:3px; margin-left:5px;">
		  <?php echo AwdwallHelperUser::getDisplayName($friend->connect_to);?>
		  </a>
		  </div> 
		  <div style=" clear:both; height:3px;"></div>
			<?php } }?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
                
        
        <br />
        <br />
<?php if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) {?>
<?php if(isset($this->groups[0])){?>
        <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('Groups');?></strong></p>
                    <br />
                <?php if(isset($this->groups[0])){?>
		<?php foreach($this->groups as $group){	?>
		<div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' .$group->id. '&Itemid=' . $Itemid);?>">
			<img src="<?php echo AwdwallHelperUser::getBigGrpImg19($group->image, $group->id);?>"  title="<?php echo $group->title;?>" style="float:left;margin-top:0px;" border="0"/></a>&nbsp;
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' .$group->id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><?php echo $group->title;?></a>
		</div>
		<div style=" clear:both; height:3px;"></div>
		<?php }?>
	<?php }?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
 <?php }
 		}?>

<?php 
if($display_mywalluservideos==1)
{
$userfiles=AwdwallHelperUser::getlatestuservideo($user->id);
?>   
<?php if(count($userfiles)){?>    
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My videos');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=videos&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	
				$imgpath=JURI::base()."images/".$userfile->thumb;
				?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;" title="<?php echo $userfile->title;?>"><span style="float:left; padding-right:8px; margin-top:-3px; height:19px; width:19px; background-image:url(<?php echo $imgpath;?>); background-position:center; overflow:hidden; background-repeat:no-repeat; margin-right:10px;"></span><?php echo substr($userfile->title,0,25);?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
        <?php } ?>
<?php } ?>

<?php 
if($display_mywalluserimages==1)
{
$userimages=AwdwallHelperUser::getlatestuserimages($user->id);
?>   
<?php if(count($userimages)){?>     
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Pictures');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=images&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   
				<?php if(count($userimages)){?>
                <?php foreach($userimages as $userimage){	
				$imgpath=JURI::base()."images/".$userimage->commenter_id."/thumb/".$userimage->path;
				?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userimage->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;" title="<?php echo $userimage->name;?>"><span style="float:left; padding-right:8px; margin-top:-3px; height:19px; width:19px; background-image:url(<?php echo $imgpath;?>); background-position:center; overflow:hidden; background-repeat:no-repeat; margin-right:10px;"></span><?php echo substr($userimage->name,0,25);?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
         <?php } ?>
<?php } ?>

<?php 
if($display_mywallusermp3==1)
{
$usermusics=AwdwallHelperUser::getlatestusermusic($user->id);
?> 
<?php if(count($usermusics)){?>         
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Music');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=music&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                    
				<?php if(count($usermusics)){?>
                <?php foreach($usermusics as $usermusic){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$usermusic->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-music.png" alt=""/></span><?php echo $usermusic->title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
         <?php } ?>
<?php } ?>
<?php 
if($display_mywalluserlinks==1)
{
$userfiles=AwdwallHelperUser::getlatestuserlinks($user->id);
?>   
<?php if(count($userfiles)){?>      
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Links');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=links&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-links.png" alt=""/></span><?php echo $userfile->title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
         <?php } ?>
<?php } ?>

        
<?php 
if($display_mywalluserfiles==1)
{
$userfiles=AwdwallHelperUser::getlatestuserfiles($user->id);
?>  
<?php if(count($userfiles)){?>      
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Files');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=files&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-file.png" alt=""/></span><?php echo $userfile->title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
        <?php } ?>
<?php } ?>
        
<?php 
if($display_mywallusertrails==1)
{
$userfiles=AwdwallHelperUser::getlatestusertrail($user->id);
?>  
<?php if(count($userfiles)){?>       
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Trails');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=trails&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-trails.png" alt=""/></span><?php echo $userfile->title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
        <?php } ?>
<?php } ?>

<?php 
if($display_mywalluserjings==1)
{
$userfiles=AwdwallHelperUser::getlatestuserjinks($user->id);
?>  
<?php if(count($userfiles)){?>       
   
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Jings');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=jing&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-jing.png" alt=""/></span><?php echo $userfile->jing_title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
<?php } ?>  
<?php } ?>

<?php 
if($display_mywalluserevents==1)
{
$userfiles=AwdwallHelperUser::getlatestuserevents($user->id);
?>    
<?php if(count($userfiles)){?>      
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Events');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=events&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-event.png" alt=""/></span><?php echo $userfile->title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
        <?php } ?>  

<?php } ?>

<?php 
if($display_mywalluserarticles==1)
{
$userfiles=AwdwallHelperUser::getlatestuserarticles($user->id);
?>  
<?php if(count($userfiles)){?>        
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Articles');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=article&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-article.png" alt=""/></span><?php echo $userfile->title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
         <?php } ?>  
<?php } ?>		
	  </div>
    </div>
  <?php }else{ ?>
 
    <div class="rbroundboxleft">
      <div class="mid_content"> 
	  			<img src="<?php echo AwdwallHelperUser::getBigAvatar133($user->id);?>"  title="<?php echo AwdwallHelperUser::getDisplayName($user->id);?>" width="133" />
	   
	   <br />
        <br />
		<?php if($showalbumlink){?>
		   <p ><a href="<?php echo JRoute::_($infolink);?>" class="minfo"><?php echo JText::_('Info');?></a></p>
           <?php  if($display_gallery==1){?>
		   <p ><a href="<?php echo JRoute::_($albumlink);?>" class="mphoto"><?php echo JText::_('Photos');?></a></p>
	   <?php } ?>
	   <?php } ?>


		<p ><a href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('Friends');?>" class="friends"><?php echo JText::_('Friends');?></a>
		<?php if( $this->pendingFriends) {?>
			<span class="totalno"><?php echo $this->pendingFriends;?></span>
		<?php }?>
		</p>
		<?php if((int)$this->displayPm){?>
		<p ><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&task=pm&Itemid=' . $Itemid);?>" title="<?php echo JText::_('Messages');?>" class="mmessage"><?php echo JText::_('Messages');?></a>
		<?php if($this->totalpm){?>
			<span class="totalno"><?php echo $this->totalpm;?></span>
		<?php }?>
		</p>
	<?php }?>
	<?php if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) {?>
		<p ><a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=groups&Itemid='.$Itemid);?>" title="<?php echo JText::_('Groups');?>" class="groups"><?php echo JText::_('Groups');?></a>
		
	
		</p>

		<p><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=group&task=newgroup&Itemid=' . $Itemid);?>"><?php echo JText::_('Create Group');?></a></p>
	<?php }?>
		<?php 
		if($this->hwdvideoshare){
			$hwdvideolink=JRoute::_("index.php?option=com_hwdvideoshare&task=viewChannel&sort=uploads&user_id=".$user->id."&Itemid=".$Itemid);
		?>
	   <p><a href="<?php echo $hwdvideolink;?>" class="mvideo"><?php echo JText::_('Videos');?></a></p>
	   <?php } ?>

	   <br />
        <br />
		<div class="about_me clearfix">
  <div class="about_tr">
    <div class="about_tl"></div>
  </div>
  <div class="about_content">
    <!--p class="title border"> <!--img src="<?php echo JURI::base();?>components/com_awdwall/images/icon_spen.gif" alt="Steve Stuart" align="right" border="0"><?php //echo JText::_('About Me');?></p-->
    <p class="border"><strong><?php echo JText::_('Basic Information');?></strong></p>
    <dl class="profile-right-info">
	

		<?php if((int)$this->displayName == USERNAME) {?>
	
		<dt><?php echo JText::_('USERNAME');?></dt>
	
		<dd><?php echo AwdwallHelperUser::getDisplayName($user->id);?></dd>
	
		<?php }else{?>
	
	
		<dt><?php echo JText::_('USERNAME');?></dt>
	
		<dd><?php echo AwdwallHelperUser::getDisplayName($user->id);?></dd>
	
		<?php }?>
		<?php
		$cbfields=explode(",",$this->albumuserinfo->cbfields);
		if(is_array($this->basicInfo)){
		foreach($this->basicInfo as $arr){
		$cbff='display_'.str_replace(' ','',$arr[1]);
		if(in_array($cbff,$cbfields))
		{
		?>
		<dt><?php echo $arr[1];?></dt><dd>
		<?php echo $arr[0];?></dd>
		<?php
		}
		}
		}
		$userinfo=$this->userinfo;
		$userinfo=$this->userinfo;
		if(!empty($userinfo))
		{  ?>
        
			<?php if($userinfo->birthday != '0000-00-00' &&  $userinfo->display_birthday==1){?>
			<dt><?php echo JText::_('Birthday');?></dt>
			<dd><?php if($userinfo->hide_birthyear==1){echo date('jS F ', strtotime($userinfo->birthday));}else{echo date('jS F Y', strtotime($userinfo->birthday));}?></dd>
			<?php }?>
            
			<?php if($userinfo->workingat != '' &&  $userinfo->display_workingat==1){?>
			<dt><?php echo JText::_('Working At');?></dt>
			<dd><?php echo $userinfo->workingat;?></dd>
			<?php }?>
			
			
			<?php if($userinfo->studied != '' &&  $userinfo->display_studied==1){?>
			<dt><?php echo JText::_('Studied');?></dt>
			<dd><?php echo $userinfo->studied;?></dd>
			<?php }?>
			
			<?php if($userinfo->livein != '' &&  $userinfo->display_livein==1){?>
			<dt><?php echo JText::_('Live in');?></dt>
			<dd><?php echo $userinfo->livein;?></dd>
			<?php }?>
			
			
			<?php if($userinfo->phone != '' &&  $userinfo->display_phone==1){?>
			<dt><?php echo JText::_('Phone');?></dt>
			<dd><?php echo $userinfo->phone;?></dd>
			<?php }?>
			<?php if($userinfo->cell != '' &&  $userinfo->display_cell==1){?>
			<dt><?php echo JText::_('Cell');?></dt>
			<dd><?php echo $userinfo->cell;?></dd>
			<?php }?>
			
			<?php 
			$colvalue=AwdwallHelperUser::getUserinfocolname('col1');
			if($userinfo->col1 != '' &&  $userinfo->display_col1==1 && $colvalue){?>
	
				<dt><?php echo $colvalue;?></dt>
	
				<dd><?php echo $userinfo->col1;?></dd>
			<?php }?>
			
			<?php 
			$colvalue=AwdwallHelperUser::getUserinfocolname('col2');
			if($userinfo->col2 != '' &&  $userinfo->display_col2==1 && $colvalue){?>
				<dt><?php echo $colvalue;?></dt>
				<dd><?php echo $userinfo->col2;?></dd>
			<?php }?>
			
			<?php 
			$colvalue=AwdwallHelperUser::getUserinfocolname('col3');
			if($userinfo->col3 != '' &&  $userinfo->display_col3==1 && $colvalue){?>
	
				<dt><?php echo $colvalue;?></dt>
	
				<dd><?php echo $userinfo->col3;?></dd>
			<?php }?>
			
			<?php 
			$colvalue=AwdwallHelperUser::getUserinfocolname('col4');
			if($userinfo->col4 != '' &&  $userinfo->display_col4==1 && $colvalue){?>
	
				<dt><?php echo $colvalue;?></dt>
	
				<dd><?php echo $userinfo->col4;?></dd>
	
			<?php }?>
			
			<?php 
			$colvalue=AwdwallHelperUser::getUserinfocolname('col5');
			if($userinfo->col5 != '' &&  $userinfo->display_col5==1 && $colvalue){?>
	
				<dt><?php echo $colvalue;?></dt>
	
				<dd><?php echo $userinfo->col5;?></dd>
			<?php }?>
			
			
			
			
			<?php
			if(($userinfo->display_facebook_user==1 && $userinfo->facebook_user !='')  || ($userinfo->display_twitter_user==1 && $userinfo->twitter_user) || ($userinfo->display_youtube_user==1 && $userinfo->youtube_user))
				{?>
				<dt><?php echo JText::_('Social');?></dt>
				<?php }?>
				<dd>	
				<?php
				/*if($userinfo->skype_user && $userinfo->display_skype_user==1) {
					$isOnline=AwdwallHelperUser::IsSkypeOnline($userinfo->skype_user);
					echo $isOnline;
				}*/
				if($userinfo->facebook_user && $userinfo->display_facebook_user==1) {?>
					&nbsp;
					<a href="http://www.facebook.com/<?php echo $userinfo->facebook_user;?>" target="_blank">
						<img src="<?php JURI::base();?>components/com_awdwall/images/facebook_icon.jpg" /></a>
				<?php
				}
				if($userinfo->twitter_user && $userinfo->display_twitter_user==1) {?>
					&nbsp;<a href="http://www.twitter.com/<?php echo $userinfo->twitter_user;?>" target="_blank">
						<img src="<?php JURI::base();?>components/com_awdwall/images/twitter_icon.png" /></a>
				<?php
				}
				if($userinfo->youtube_user && $userinfo->display_youtube_user==1) {?>
					&nbsp;<a href="http://www.youtube.com/user/<?php echo $userinfo->youtube_user;?>" target="_blank">
						<img src="<?php JURI::base();?>components/com_awdwall/images/youtube_icon.jpg" /></a>
				<?php }?>
					</dd>
			
				<?php
		}
		?>
    </dl>
  </div>
  <div class="about_br">
    <div class="about_bl"></div>
  </div>
</div>
       
        <br />
        <br />
        <div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('Friends');?></strong></p>
                <dl class="profile-right-info">	    
                    <dd>
                        <span style="float:left;"><?php echo $this->totalFriends;?> <?php echo JText::_('Friends');?></span> 
                        <?php if((int)$this->totalFriends) {?>
                         <a href="<?php echo $friendJsUrl;?>"  title="<?php echo JText::_('Friends');?>"  style="float:right;"><?php echo JText::_('See all');?></a> 
                         <?php
                         }?>
                   
                    </dd>
                </dl>
                  <br />   <br />
                 <?php if((int)$this->totalFriends) {?>		  
		  <?php $i = 1; foreach($this->leftFriends as $friend){
				$class = 'column1';
				if($i%2 == 0)
					$class = 'column2';
				$i++;
		  ?>
		  <div style="min-height:20px;">
		  <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $friend->connect_to .'&Itemid=' . $Itemid, false);?>">

		<img src="<?php echo AwdwallHelperUser::getBigAvatar19($friend->connect_to);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($friend->connect_to);?>" title="<?php echo AwdwallHelperUser::getDisplayName($friend->connect_to);?>" style="float:left;margin-top:0px;"  height="19" width="19" class="awdpostavatar"/></a>
		
		  <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $friend->connect_to .'&Itemid=' . $Itemid, false);?>"  style="text-decoration:none;margin-top:3px; margin-left:5px;">
		  <?php echo AwdwallHelperUser::getDisplayName($friend->connect_to);?>
		  </a>
		  </div> 
		  <div style=" clear:both; height:3px;"></div>
			<?php } }?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
        <br />
        <br />

<?php if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) {?>
<?php if(isset($this->groups[0])){?>
        <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('Groups');?></strong></p>
                    <br />
                <?php if(isset($this->groups[0])){?>
		<?php foreach($this->groups as $group){	?>
		<div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' .$group->id. '&Itemid=' . $Itemid);?>">
			<img src="<?php echo AwdwallHelperUser::getBigGrpImg19($group->image, $group->id);?>"  title="<?php echo $group->title;?>" style="float:left;margin-top:0px;" border="0"/></a>&nbsp;
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' .$group->id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><?php echo $group->title;?></a>
		</div>
		<div style=" clear:both; height:3px;"></div>
		<?php }?>
	<?php }?>
     <p ><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=group&task=newgroup&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Create a Group');?></a></p>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
 <?php }
 		}?>

<?php 
if($display_mywalluservideos==1)
{
$userfiles=AwdwallHelperUser::getlatestuservideo($user->id);
?>   
<?php if(count($userfiles)){?>    
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My videos');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=videos&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	
				$imgpath=JURI::base()."images/".$userfile->thumb;
				?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;" title="<?php echo $userfile->title;?>"><span style="float:left; padding-right:8px; margin-top:-3px; height:19px; width:19px; background-image:url(<?php echo $imgpath;?>); background-position:center; overflow:hidden; background-repeat:no-repeat; margin-right:10px;"></span><?php echo substr($userfile->title,0,25);?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
        <?php } ?>
<?php } ?>

<?php 
if($display_mywalluserimages==1)
{
$userimages=AwdwallHelperUser::getlatestuserimages($user->id);
?>   
<?php if(count($userimages)){?>     
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Pictures');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=images&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   
				<?php if(count($userimages)){?>
                <?php foreach($userimages as $userimage){	
				$imgpath=JURI::base()."images/".$userimage->commenter_id."/thumb/".$userimage->path;
				?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userimage->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;" title="<?php echo $userimage->name;?>"><span style="float:left; padding-right:8px; margin-top:-3px; height:19px; width:19px; background-image:url(<?php echo $imgpath;?>); background-position:center; overflow:hidden; background-repeat:no-repeat; margin-right:10px;"></span><?php echo substr($userimage->name,0,25);?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
         <?php } ?>
<?php } ?>

<?php 
if($display_mywallusermp3==1)
{
$usermusics=AwdwallHelperUser::getlatestusermusic($user->id);
?> 
<?php if(count($usermusics)){?>         
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Music');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=music&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                    
				<?php if(count($usermusics)){?>
                <?php foreach($usermusics as $usermusic){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$usermusic->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-music.png" alt=""/></span><?php echo $usermusic->title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
         <?php } ?>
<?php } ?>
<?php 
if($display_mywalluserlinks==1)
{
$userfiles=AwdwallHelperUser::getlatestuserlinks($user->id);
?>   
<?php if(count($userfiles)){?>      
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Links');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=links&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-links.png" alt=""/></span><?php echo $userfile->title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
         <?php } ?>
<?php } ?>

        
<?php 
if($display_mywalluserfiles==1)
{
$userfiles=AwdwallHelperUser::getlatestuserfiles($user->id);
?>  
<?php if(count($userfiles)){?>      
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Files');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=files&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-file.png" alt=""/></span><?php echo $userfile->title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
        <?php } ?>
<?php } ?>
        
<?php 
if($display_mywallusertrails==1)
{
$userfiles=AwdwallHelperUser::getlatestusertrail($user->id);
?>  
<?php if(count($userfiles)){?>       
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Trails');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=trails&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-trails.png" alt=""/></span><?php echo $userfile->title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
        <?php } ?>
<?php } ?>

<?php 
if($display_mywalluserjings==1)
{
$userfiles=AwdwallHelperUser::getlatestuserjinks($user->id);
?>  
<?php if(count($userfiles)){?>       
   
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Jings');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=jing&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-jing.png" alt=""/></span><?php echo $userfile->jing_title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
<?php } ?>  
<?php } ?>

<?php 
if($display_mywalluserevents==1)
{
$userfiles=AwdwallHelperUser::getlatestuserevents($user->id);
?>    
<?php if(count($userfiles)){?>      
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Events');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=events&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-event.png" alt=""/></span><?php echo $userfile->title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
        <?php } ?>  

<?php } ?>

<?php 
if($display_mywalluserarticles==1)
{
$userfiles=AwdwallHelperUser::getlatestuserarticles($user->id);
?>  
<?php if(count($userfiles)){?>        
         <br />
        <br />
		<div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('My Articles');?></strong></p>
                    <dl class="profile-right-info">	    
                    <dd>
                    <span style="float:left;"></span> 
                    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&task=article&&Itemid=' . $Itemid);?>" title="<?php echo JText::_('See all');?>" style="float:right; margin-top:-25px;" class="awdseeall"><?php echo JText::_('See all');?></a>
                    </dd>
                    </dl>
                   

				<?php if(count($userfiles)){?>
                <?php foreach($userfiles as $userfile){	?>
                <div style="min-height:20px;">
		<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&wid=' .$userfile->wall_id. '&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px;"><span style="float:left; padding-right:8px; margin-top:-3px;"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/icon-article.png" alt=""/></span><?php echo $userfile->title;?></a>
                </div>
                <div style=" clear:both; height:3px;"></div>
                <?php 
				}
                }
				?>
                </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
         <?php } ?>  
<?php } ?>		
	  </div>
    </div>
  <?php }?>  
    
    
    <div class="rbroundboxrighttop"> <span class="bl2"></span><span class="br2"></span>
      <div class="user_place"> 
    <span class="profileName">
	<a href="javascript:void(0);"><?php echo JText::_('Friends');?></a>
    </span>&nbsp;
   
      </div>
 <div class="fullboxnew" >
 <!-- start msg content --> 
 <span id="msg_loader"></span>
 <div id="msg_content" class="awdlist">
<div id="demo" class="">
<div class="panel  panel-top">						
    <div class="drop-down page-by">
        <ul >
            <li  ><span data-number="10" style="padding-left:5px;"><?php echo JText::_('COM_COMAWDWALL_FILTER_TEXT1');?></span></li>
            <li  ><span data-number="20" style="padding-left:5px;"><?php echo JText::_('COM_COMAWDWALL_FILTER_TEXT2');?></span></li>
            <li  ><span data-number="30" style="padding-left:5px;"><?php echo JText::_('COM_COMAWDWALL_FILTER_TEXT3');?></span></li>
            <li  ><span data-number="all" style="padding-left:5px;"><?php echo JText::_('COM_COMAWDWALL_FILTER_TEXT4');?></span></li>
        </ul>
    </div>
    <div class="drop-down sort-drop-down">
        <ul>
            <li><span data-sort="title" data-order="asc" data-type="text" style="padding-left:5px;"><?php echo JText::_('COM_COMAWDWALL_FILTER_TEXT5');?></span></li>
            <li><span data-sort="title" data-order="desc" data-type="text" style="padding-left:5px;"><?php echo JText::_('COM_COMAWDWALL_FILTER_TEXT6');?></span></li>
        </ul>
    </div>
    
    <!-- filter -->
    <div class="filter">							
        <input data-name="title" type="text" value="" placeholder="<?php echo JText::_('COM_COMAWDWALL_FILTER_TEXT7');?>"/>
        <input data-name="description" type="hidden" value="" placeholder="Filter by description"/>							
    </div>
    <div style="clear:both; height:5px;"></div>
    <div class="awdinfopage" data-type="range"></div>
    <div class="awdpagination"></div>						
</div>
<div class="awdlist">

 <!-- start block msg -->
<?php 
if(isset($this->friends[0])){
	$n = count($this->friends);
	for($i = 0; $i < $n; $i++){
	
	if($this->display_profile_link==1)
	{
		$profilelink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $this->friends[$i]->connect_to . '&Itemid=' . AwdwallHelperUser::getJsItemId(), false);
	}
	else
	{
		$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->friends[$i]->connect_to.'&Itemid=' . $Itemid, false);
	}
		
?> <a name="here<?php echo $this->friends[$i]->connection_id;?>" id="here<?php echo $this->friends[$i]->connection_id;?>"></a>
  <div class="awdfullbox clearfix awdlist-item" id="msg_block_<?php echo $this->friends[$i]->connection_id;?>"><span class="tl"></span><span class="bl"></span>
    <div class="rbroundboxleft">
      <div class="mid_content"><a href="<?php echo $profilelink;?>">
		<img src="<?php echo AwdwallHelperUser::getBigAvatar51($this->friends[$i]->connect_to);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($this->friends[$i]->connect_to);?>" title="<?php echo AwdwallHelperUser::getDisplayName($this->friends[$i]->connect_to);?>"  height="50" width="50" class="awdpostavatar"/>
	  </a>	  </div>
    </div>
    <div class="rbroundboxright"> <span class="tl2"></span><span class="bl2"></span><span class="tr2"></span><span class="br2"></span>
    <div class="right_mid_content">
	 <ul class="walltowall">
	<li><a style="font-size:12px;" href="<?php echo $profilelink;?>" class="awdtitle"><?php echo AwdwallHelperUser::getDisplayName($this->friends[$i]->connect_to);?></a>&nbsp;&nbsp;
	<?php if($this->friends[$i]->status == '0' && $this->friends[$i]->pending == '0'){?>
	<span id="friend_<?php echo $this->friends[$i]->connection_id;?>"><?php echo JText::_('Wants to be your friend');?>&nbsp;&nbsp;<a href="javascript:void(0);" onclick="denyFriend('<?php echo JURI::base().'index.php?option=com_awdwall&task=denyfriend&user_to=' . $this->friends[$i]->connect_to;?>', '<?php echo $this->friends[$i]->connection_id;?>');" class="deny_friend"><?php echo JText::_('Deny');?></a> <?php echo JText::_('or');?> <a href="javascript:void(0);" onclick="acceptFriend('<?php echo JURI::base().'index.php?option=com_awdwall&task=acceptfriend&user_to=' . $this->friends[$i]->connect_to;?>', '<?php echo $this->friends[$i]->connection_id;?>');" class="accept_friend"><?php echo JText::_('Accept');?></a></span>
	<?php }elseif($this->friends[$i]->status == '1' && $this->friends[$i]->pending == '1'){?>
	
	<?php echo JText::_('Waiting for authorization');?>
	
	<?php }?>
	</li>
	</ul>
     <div class="commentinfo"> <span class="wall_date"><?php echo AwdwallHelperUser::getDisplayTime($this->friends[$i]->created);?> </span>&nbsp;&nbsp;
         <?php if((int)$user->id){
	if($this->displayPm){?>
         <a href="javascript:void(0);" onclick="showPMBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getpmbox&tmpl=component';?>', <?php echo $this->friends[$i]->connection_id;?>, <?php echo $this->friends[$i]->connect_to;?>, <?php echo $this->friends[$i]->connection_id;?>);"><?php echo JText::_('PM');?></a> - 
       <?php }?>
         <a href="javascript:void(0);" onclick="openFriendDeleteBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=deletefriend&user_to=' . (int)$this->friends[$i]->connect_to . '&tmpl=component';?>', <?php echo $this->friends[$i]->connection_id; ?>);"><?php echo JText::_('Delete');?></a>
         <?php if(AwdwallHelperUser::checkOnline($this->friends[$i]->connect_to)){?>
          <img src="<?php echo AwdwallHelperUser::getOnlineIcon();?>" title="<?php echo JText::_('Online');?>" alt="<?php echo JText::_('Online');?>" style="vertical-align:middle;"/>
         <?php }?>
         <?php }?>
       <!--start pm box -->
       <div id="pm_<?php echo $this->friends[$i]->connection_id;?>" class="comment_text">
	<span id="pm_loader_<?php echo $this->friends[$i]->connection_id;?>" style="display:none;margin:10px;margin-top:10px;"><img src="components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>	</div>
	<!--end pm box -->
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
 </div>
     </div>
 <?php
	if(((($this->page + 1) * $this->postLimit) < $this->nofFriends) && (int)$user->id){
?>
 <div class="lightblue_box"><a href="javascript:void(0);" onclick="getOlderPosts('<?php echo JRoute::_(JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getolderfriend&tmpl=component', false);?>');"><?php echo JText::_('More');?></a>&nbsp;&nbsp;<span id="older_posts_loader" style="display:none;"><img src="components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
	<input id="awd_page" name="awd_page" type="hidden" value="<?php echo ($this->page + 1);?>" autocomplete="off" />
	<input id="task" name="task" type="hidden" value="<?php echo $this->task;?>" autocomplete="off" />
 </div>
<?php } ?>
 <!-- end msg content --> 
 </div>
 
 <div id="dialog_friend_delete_box" title="<?php echo JText::_('Delete Friend');?>" style="display:none;">
	<?php echo JText::_('Are you sure you want to delete this friend');?>
	<br />
	<br />
	<span id="friend_delete_loader"></span>
	<input type="hidden" name="friend_delete_url" id="friend_delete_url" />
	<input type="hidden" name="friend_delete_block_id" id="friend_delete_block_id" />
</div>
<input type="hidden" name="wall_last_time" id="wall_last_time" value="<?php echo time();?>" />
<input type="hidden" name="posted_wid" id="posted_wid" value="" />

<?php  if($template=='default') { ?>
<script type="text/javascript">
if(jQuery(".rbroundboxrighttop").height() < jQuery(".rbroundboxleft_user").height())
{
 jQuery(".rbroundboxrighttop").height(jQuery(".rbroundboxleft_user").height());
 }
 



adjustwidth();

function adjustwidth() {

var tt;
var mm;
var ll;

ll=jQuery(".awdfullbox").width();
tt=(27/100)*ll+40;
var bb=ll-tt;
mm=Math.floor((bb*100)/ll)-.5;
var new_number = mm+'%';

jQuery(".rbroundboxrighttop").css("width",new_number);
};
 
 
 var resizeTimer = null;
jQuery(window).bind('resize', function() {
    if (resizeTimer) clearTimeout(resizeTimer);
    resizeTimer = setTimeout(adjustwidth, 5);
});

</script>
<?php }  ?>
<script type="text/javascript">
			jQuery("document").ready(function(){
				var jplist = jQuery("#demo").jplist({
				
					//main options
					items_box: ".awdlist", //items container
					item_path: ".awdlist-item", //path to the item
					css_prefix: "jplist",
					cookies: false,
					redraw_callback: "",
					
					//sort
					sort: {title: "a.awdtitle",
						   description: "p.desc",
						   like: "p.like",
						   date: "p.date"},
					sort_name: "title",
					sort_order: "asc", //"desc",
					sort_type: "text",
					
					//filter
					filter_path: ".filter",
					filter: {title: "a.awdtitle",
							 description: "p.desc"},
					
					//paging
					pagingbox: ".awdpagination",
					pageinfo: ".awdinfopage",
					items_on_page: 10,
					paging_length: 7,  //pager length
					show_one_page: false,
					
					//drop down
					sort_dd_path: ".sort-drop-down",
					paging_dd_path: ".page-by"					
				});
			});


</script>