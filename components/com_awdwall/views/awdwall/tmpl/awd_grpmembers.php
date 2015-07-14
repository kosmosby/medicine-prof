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
$cbItemid = AwdwallHelperUser::getJsItemId();
// get user object
$user = &JFactory::getUser();

$mainframe	=& JFactory::getApplication();
if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) 
{}
else
{
$mainframe->Redirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$Itemid , false ), JText::_('Group is disabled'));
}
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

//$friendJsUrl = 'index.php?com_comprofiler=&task=manageConnections&Itemid=' . $cbItemid . '&option=com_comprofiler';
$friendJsUrl = 'index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid;
$groupsUrl = JRoute::_('index.php?option=com_awdwall&task=groups&Itemid=' . $Itemid, false);

//$config = &JComponentHelper::getParams('com_awdwall');
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
$width 			= $config->get('width', 725);
$display_name 	= $config->get('display_name', 1);
$display_gallery = $config->get('display_gallery', 1);
?>
<style type="text/css">
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
	}	#awd-mainarea .rbroundboxright, #awd-mainarea .fullboxtop, #awd-mainarea .rbroundboxleft_user, #awd-mainarea .rbroundboxleft, #awd-mainarea .lightblue_box, #awd-mainarea .rbroundboxrighttop{
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
	 #awd-mainarea .blog_content, #awd-mainarea .about_me{
		background-color:#<?php echo $this->color[14]; ?>;
	}
	<?php }?>
	#awd-mainarea .rbroundboxleft .mid_content a, #awd-mainarea .blog_groups, #awd-mainarea .blog_groups a, #awd-mainarea .blog_friend, #awd-mainarea .blog_friend a, #awd-mainarea a.authorlink{
		color:#<?php echo $this->color[6]; ?>;
	}
	#msg_content .rbroundboxleft, #msg_content .awdfullbox{
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
#awd-mainarea #dropAlerts .notiItem a{
color:#<?php echo $this->color[2]; ?>!important;
}
#awd-mainarea .rbroundboxleft .mid_content .myavtar
{max-width:133px!important; }
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
        <li <?php if($layout == 'main') {echo 'class="activenews mainactivenews"';}else{echo 'class="newsfeed mainactivenews"';} ?>><a <?php if($layout == 'main') {echo 'class="activenews"';}else{echo 'class="newsfeed"';} ?> href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('News Feed');?>" ></a> </li>
		<?php if((int)$user->id){?>
        <?php if($template=='default'){?>
        <li style="float:left; display:block; position:relative;">
            <a href="#" title="Notifications" id="notifications-button">
                <img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/8.png" alt=""/>
                <span class="message-count" style="display:none"></span>
            </a>
        </li>
        <?php }?>
        <li class="separator"></li>
        <li><a href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('Friends');?>"><?php echo JText::_('Friends');?><font color="red" size="1"><?php if((int)$this->pendingFriends > 1) echo '(' . $this->pendingFriends . JText::_('Requests') . ')';elseif((int)$this->pendingFriends == 1) echo '(' . $this->pendingFriends . JText::_('') . ')';?></font></a></li>
		<?php if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) {?>
        
		<li class="separator"></li>
		 <li> <a href="<?php echo $groupsUrl;?>" title="<?php echo JText::_('Groups');?>"  class="active"><?php echo JText::_('Groups');?></a> </li>
		<?php }?>
        <li class="separator"></li>
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
        <div style=" float:left; width:auto; margin-left:6px;padding-top:3px; height:32px;"><?php echo AwdwallHelperUser::getDisplayName($user->id);?></div>
        </div>
        </a>
        </li>
        <?php }?>
      </ul>
    </div>
  </div>
  
 <div class="awdfullbox fullboxtop  clearfix"> <span class="bl"></span>
    <div class="rbroundboxleft">
      <div class="mid_content">
	  <a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->grpInfo->id . '&Itemid=' . $Itemid, false);?>">
		<img style="padding-top:12px;" src="<?php echo AwdwallHelperUser::getBigGrpImg133($this->grpInfo->image, $this->grpInfo->id);?>" alt="<?php echo $this->grpInfo->title;?>" title="<?php echo $this->grpInfo->title;?>" class="myavtar"/>
	  </a>
	   <?php if($this->owner){?>
	   <p style="text-align:left;padding-left:15px;padding-top:5px;"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=groupsetting&groupid=' . $this->grpInfo->id. '&Itemid=' . $Itemid);?>"><?php echo JText::_('Group Settings');?></a></p>
	   <?php if($this->isPrivate){?>
	   <p style="text-align:left;padding-left:15px;"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=invitemembers&groupid=' . $this->grpInfo->id. '&Itemid=' . $Itemid);?>"><?php echo JText::_('Invite Members');?></a></p>
	   <?php }?>
	  <?php }?>
	   <br />
        <br />
		<div class="about_me clearfix">
		<div class="about_tr"> <div class="about_tl"></div></div>          
          <div class="about_content">
           
			<p class="border"><strong><?php echo JText::_('Basic Information');?></strong></p>
			<?php if(isset($this->grpInfo)){?>
           <dl class="profile-right-info">						
			<dt><?php echo JText::_('Description');?></dt>
			<dd><?php echo $this->grpInfo->description;?></dd>
			
			<dt><?php echo JText::_('Created by');?></dt>
			<?php foreach($this->memberscreator as $membercreator){ ?>
				<dd><?php echo AwdwallHelperUser::getDisplayName($membercreator->creator);?></dd>			
			<?php } ?>
			<dt><?php echo JText::_('Created Date');?></dt>
			<dd><?php echo date('d-m-Y', $this->grpInfo->created_date);?></dd>
	    	</dl>              
			<?php }?>	
			
           </div>
              <div class="about_br"> <div class="about_bl"></div></div>          
        </div>
       
        <br />
        <br />
        <div class="about_me clearfix">
			<div class="about_tr"> <div class="about_tl"></div></div>          
                <div class="about_content">
                    <p class="border"><strong><?php echo JText::_('Group Member');?></strong></p>
                <dl class="profile-right-info">	    
                    <dd>
                        <span style="float:left;"><?php echo ($this->nofMembers + 1);?> <?php echo JText::_('Members');?></span> 
                        <a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=grpmembers&groupid=' . $this->grpInfo->id.'&Itemid=' . $Itemid);?>" title="<?php echo JText::_('Members');?>" style="float:right;"><?php echo JText::_('See all');?></a>
                    </dd>
                </dl>
                  <br />   <br />
                		  <?php if((int)$this->nofMembers) {?>		  
		  <?php $i = 1; foreach($this->members as $member){
				$class = 'column1';
				if($i%2 == 0)
					$class = 'column2';
				$i++;
		  ?>
		   <div style="min-height:20px;">
		  <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $member->user_id .'&Itemid=' . $Itemid);?>">
		  		 
		<img src="<?php echo AwdwallHelperUser::getBigAvatar19($member->user_id);?>"  title="<?php echo AwdwallHelperUser::getDisplayName($member->user_id);?>"  style="float:left;margin-top:0px;" border="0"  height="19" width="19" class="awdpostavatar" /></a>
		
		  <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $member->user_id .'&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px; margin-left:5px;">
		  <?php echo AwdwallHelperUser::getDisplayName($member->user_id);?>
		  
		  </a> 
		  </div>
			<?php } }?>
			<?php 
				//get member is creator group
				if($class == 'column1'){
					$class = 'column2';
				}else{
					$class = 'column1';
				}
			?>
			<?php foreach($this->memberscreator as $membercreator){ ?>
		  <div style="min-height:20px;">
				<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $membercreator->creator .'&Itemid=' . $Itemid);?>">

				<img src="<?php echo AwdwallHelperUser::getBigAvatar19($membercreator->creator);?>"  title="<?php echo AwdwallHelperUser::getDisplayName($membercreator->creator);?>"  style="float:left;margin-top:0px;" border="0"  height="19" width="19" class="awdpostavatar" /></a>
				
				<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $membercreator->creator .'&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px; margin-left:5px;">
		  <?php echo AwdwallHelperUser::getDisplayName($membercreator->creator);?>
			</a>
			
		  </div>
			<?php } ?>
            </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>
        <br />
        <br />
 	
	  </div>
    </div>
    <div class="rbroundboxrighttop"> <span class="bl2"></span><span class="br2"></span>
      <div class="user_place"> 
    <span class="profileName">
	<a href="javascript:void(0);"><?php echo JText::_('Members of this group');?></a>
    </span>&nbsp;
   
      </div>
 <div class="fullboxnew" >
 <!-- start msg content --> 
 <span id="msg_loader"></span>
 <div id="msg_content_not_ajax">
 
 <?php foreach($this->memberscreator as $membercreator){ ?>
 <!-- Begin get creator -->
	<div class="awdfullbox clearfix" id="msg_block_<?php echo $membercreator->membercreator;?>"><span class="tl"></span><span class="bl"></span>
    <div class="rbroundboxleft">	
      <div class="mid_content"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $membercreator->creator . '&Itemid=' . $Itemid, false);?>">
		<img src="<?php echo AwdwallHelperUser::getBigAvatar51($membercreator->creator);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($membercreator->creator);?>" title="<?php echo AwdwallHelperUser::getDisplayName($membercreator->creator);?>"  height="50" width="50" class="awdpostavatar"/>
	  </a> 	
	  </div>
	  
    </div>
    <div class="rbroundboxright"> <span class="tl2"></span><span class="bl2"></span><span class="tr2"></span><span class="br2"></span>
    <div class="right_mid_content">	
	 <ul class="walltowall">
	<li><a style="font-size:12px;" href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $membercreator->creator . '&Itemid=' . $Itemid, false);?>"><?php echo AwdwallHelperUser::getDisplayName($membercreator->creator);?></a>&nbsp;&nbsp;	
	</li>
	</ul>
  <div class="commentinfo"> 
  <span class="wall_date"><?php echo AwdwallHelperUser::getDisplayTime($membercreator->created_date);?> </span>&nbsp;&nbsp;
<?php if((int)$user->id){
if($this->displayPm){
?>
<a href="javascript:void(0);" onclick="showPMBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getpmbox&tmpl=component';?>', <?php echo $membercreator->creator;?>, <?php echo $membercreator->creator;?>, <?php echo $membercreator->creator;?>);"><?php echo JText::_('PM');?></a> - <?php } ?>
<?php if($membercreator->owner || $user->id == $membercreator->creator) {?>

<a href="javascript:void(0);" onclick="openFriendDeleteBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=deletegrpmember&groupid=' . (int)$membercreator->users[$i]->group_id . '&tmpl=component&userid=' . $membercreator->creator;?>', <?php echo $membercreator->creator; ?>);"><?php echo JText::_('Delete');?></a>
<?php }?>
<?php if(AwdwallHelperUser::checkOnline($membercreator->creator)){?>
	<img src="<?php echo AwdwallHelperUser::getOnlineIcon();?>" title="<?php echo JText::_('Online');?>" alt="<?php echo JText::_('Online');?>" style="vertical-align:middle;"/>
<?php }?>
<?php }?><p></p>
<!--start pm box -->
	<div id="pm_<?php echo $membercreator->creator;?>" class="comment_text">
	<span id="pm_loader_<?php echo $membercreator->creator;?>" style="display:none;margin:10px;margin-top:10px;"><img src="components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
 </div>
	  </div>
	</div>  
    </div>
  </div>
 <!-- End get creator -->
 <?php } ?>
 
<!-- start block msg -->
<?php 
if(isset($this->users[0])){
	$n = count($this->users);
	for($i = 0; $i < $n; $i++){
	//	if($this->users[$i]->group_id != NULL && $this->grpInfo->id != $this->users[$i]->group_id)
	//		continue;
	if($this->display_profile_link==1)
	{
		$profilelink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $this->users[$i]->id . '&Itemid=' . AwdwallHelperUser::getJsItemId(), false);
	}
	else
	{
		$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->users[$i]->id .'&Itemid=' . $Itemid, false);
	}
?> 
  <div class="awdfullbox clearfix" id="msg_block_<?php echo $this->users[$i]->id;?>"><span class="tl"></span><span class="bl"></span>
    <div class="rbroundboxleft">	
      <div class="mid_content"><a href="<?php echo $profilelink;?>">
		<img src="<?php echo AwdwallHelperUser::getBigAvatar51($this->users[$i]->id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($this->users[$i]->id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($this->users[$i]->id);?>"  height="50" width="50" class="awdpostavatar" />
	  </a> 	
	  </div>
	  
    </div>
    <div class="rbroundboxright"> <span class="tl2"></span><span class="bl2"></span><span class="tr2"></span><span class="br2"></span>
    <div class="right_mid_content">	
	 <ul class="walltowall">
	<li><a style="font-size:12px;" href="<?php echo $profilelink;?>"><?php echo AwdwallHelperUser::getDisplayName($this->users[$i]->id);?></a>&nbsp;&nbsp;	
	</li>
	</ul>
  <div class="commentinfo"> 
  <span class="wall_date"><?php echo AwdwallHelperUser::getDisplayTime($this->users[$i]->created_date);?> </span>&nbsp;&nbsp;
<?php if((int)$user->id){ 
		 if($this->displayPm){?>
<a href="javascript:void(0);" onclick="showPMBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getpmbox&tmpl=component';?>', <?php echo $this->users[$i]->id;?>, <?php echo $this->users[$i]->id;?>, <?php echo $this->users[$i]->id;?>);"><?php echo JText::_('PM');?></a> -
<?php }?>
<?php if($this->owner || $user->id == $this->users[$i]->id) {?>
 
<a href="javascript:void(0);" onclick="openFriendDeleteBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=deletegrpmember&groupid=' . (int)$this->users[$i]->group_id . '&tmpl=component&userid=' . $this->users[$i]->id;?>', <?php echo $this->users[$i]->id; ?>);"><?php echo JText::_('Delete');?></a>
<?php }?>
<?php if(AwdwallHelperUser::checkOnline($this->users[$i]->id)){?>
	<img src="<?php echo AwdwallHelperUser::getOnlineIcon();?>" title="<?php echo JText::_('Online');?>" alt="<?php echo JText::_('Online');?>" style="vertical-align:middle;"/>
<?php }?>
<?php }?><p></p>
<!--start pm box -->
	<div id="pm_<?php echo $this->users[$i]->id;?>" class="comment_text">
	<span id="pm_loader_<?php echo $this->users[$i]->id;?>" style="display:none;margin:10px;margin-top:10px;"><img src="components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
 </div>
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
 <!-- end msg content --> 
 </div>
 </div>
 <div id="dialog_friend_delete_box" title="<?php echo JText::_('Delete Friend');?>" style="display:none;">
	<?php echo JText::_('Are you sure you want to delete this friend');?>
	<br />
	<br />
	<span id="friend_delete_loader"></span>
	<input type="hidden" name="friend_delete_url" id="friend_delete_url" />
	<input type="hidden" name="friend_delete_block_id" id="friend_delete_block_id" />
</div>
<?php  if($template=='default') { ?>
<script type="text/javascript">
if(jQuery(".rbroundboxrighttop").height() < jQuery(".rbroundboxleft").height())
{
 jQuery(".rbroundboxrighttop").height(jQuery(".rbroundboxleft").height());
 }
 


adjustwidth();

function adjustwidth() {

 var tt;
var mm;
var ll;

//tt=jQuery(".awdfullbox_top").width()-(27/100);

ll=jQuery(".awdfullbox_top").width();
tt=(27/100)*ll+40;
var bb=ll-tt;
//alert(bb);
mm=Math.floor((bb*100)/ll)-.5;
var new_number = mm+'%';

//alert(mm);
//jQuery(".rbroundboxleft ").css("width",190);
jQuery(".rbroundboxrighttop").css("width",new_number);
};
 
 
 var resizeTimer = null;
jQuery(window).bind('resize', function() {
    if (resizeTimer) clearTimeout(resizeTimer);
    resizeTimer = setTimeout(adjustwidth, 10);
});

</script>
<?php }  ?>
