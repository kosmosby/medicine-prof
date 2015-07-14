 <?php
/**
 * @version 2.4
 * @package Jomwall-CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
// include js and css
require_once(JPATH_COMPONENT . DS . 'js' . DS . 'include.php');
// get user object
$user = &JFactory::getUser();
$Itemid = AwdwallHelperUser::getComItemId();
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


//$Itemid = AwdwallHelperUser::getComItemId();

$cbItemid = AwdwallHelperUser::getJsItemId();
$nofMyGrps = count($this->myGrps) + count($this->extraGroups);
$nofAllGrps = count($this->allGrps);
$nofPendings = count($this->pendings);
//$friendJsUrl = 'index.php?com_comprofiler=&task=manageConnections&Itemid=' . $cbItemid . '&option=com_comprofiler';
$friendJsUrl = JRoute::_('index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid);

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
	#awd-mainarea .profileLink a, #awd-mainarea .profileMenu .ppm a, #awd-mainarea #msg_content .rbroundboxleft .mid_content a{
		color:#<?php echo $this->color[2]; ?>;
	}
	#awd-mainarea #awd_message, #awd-mainarea .right_mid_content .walltowall li{
		color:#<?php echo $this->color[3]; ?>!important;
	}
	#awd-mainarea .wall_date{
		color:#<?php echo $this->color[4]; ?>;
	}
	 #awd-mainarea .rbroundboxright, #awd-mainarea .awdfullbox_top, #awd-mainarea .rbroundboxleft_user, #awd-mainarea .lightblue_box, #awd-mainarea .rbroundboxrighttop{
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
		background-color:#<?php echo $this->color[14]; ?>!important;
	}
	<?php }?>
	#awd-mainarea .commentinfo a, .commentinfo a:visited, #awd-mainarea .rbroundboxleft .mid_content a, #awd-mainarea .blog_groups, #awd-mainarea .blog_groups a, #awd-mainarea .blog_friend, #awd-mainarea .blog_friend a{
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
</style>
<div style="width:100%" id="awd-mainarea">
<div class="wallheading">
<script type="text/javascript">
	window.onload=function(){
		var tabs1 = document.getElementById('tabs-1');	
		var tabs2 = document.getElementById('tabs-2');	
		var tabs3 = document.getElementById('tabs-3');	
		/*if(navigator.appName == 'Opera'){
			//tabs.style.margin = '22px 0px 0px -284px';
			tabs1.className='fix_op fix_op_tab1 ui-tabs-panel ui-widget-content ui-corner-bottom';
			tabs2.className='fix_op fix_op_tab<?php if($nofAllGrps > 1) echo '2'; else echo '1';?> ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide';
			tabs3.className='fix_op fix_op_tab3 ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide';
		}		*/
	}
</script>
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
<div id="awd_groups">
<div id="awd_grps_cont">
<div id="awd_grps_header">
<?php echo JText::_('Groups');?>
<span class="awd_grp_new"><img src="<?php echo  JURI::base() . "components/com_awdwall/images/".$template."/icon-create.png";?>" >&nbsp;<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=group&task=newgroup&Itemid=' . $Itemid);?>"><?php echo JText::_('Create New Group');?></a></span>
</div>
<div id="awd_grps_body">
<script type="text/javascript">
	jQuery(function() {
		jQuery("#tabs").tabs();
	});
</script>

<div id="tabs">
	<ul>
		<li class="tab1"><a href="#tabs-1"><b><?php echo JText::_('My Groups');?> (<?php echo $nofMyGrps;?>)</b></a></li>
		<li class="tab2"><a href="#tabs-2"><b><?php echo JText::_('All Groups');?> (<?php echo $nofAllGrps;?>)</b></a></li>		
		<li class="tab3"><a href="#tabs-3"><b><?php echo JText::_('Pending my approval');?>  <?php if($nofPendings){?><font style="color:#FF0000">(<?php echo $nofPendings;?>)</font><?php }else{?> (<?php echo $nofPendings;?>) <?php } ?></b></a></li>
	</ul>
	<div id="tabs-1">
	<?php if($nofMyGrps){
	foreach($this->myGrps as $group){
	$link = JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $group->id.'&Itemid=' . $Itemid, false);
	$strInfo = '<font color="#AAAAAA" size="-1">';
	$nofMembers = $this->groupModel->countMemGrp($group->id) + 1;
	if($nofMembers == 0)
		$strInfo .= $nofMembers . ' ' . JText::_('Member') . ', ';
	else
		$strInfo .= $nofMembers . ' ' . JText::_('Members') . ', ';
		
	$nofPosts = $this->groupModel->countPostGrp($group->id);
	if($nofPosts == 0)
		$strInfo .= $nofPosts . ' ' . JText::_('Post');
	else
		$strInfo .= $nofPosts . ' ' . JText::_('Posts');
	$strInfo .= '</font>';
	?>
		<div class="awd_grp_ele"><a href="<?php echo $link;?>">
			<img src="<?php echo AwdwallHelperUser::getBigGrpImg64($group->image, $group->id);?>"  title="<?php echo AwdwallHelperUser::getDisplayName($groups->creator)?>" border="0" />
		</a>
		<div class="awd_subgrp_ele">
		<?php if((int)$group->privacy == 2){?>
		<img src="<?php echo JURI::base();?>components/com_awdwall/images/private_group.jpg" title="" border="0" /><?php }?>
		<a href="<?php echo $link;?>"><?php echo $group->title;?></a><br /><br /><?php echo $strInfo;?></div>
		</div>
	<?php }
	if(isset($this->extraGroups[0])){
		foreach($this->extraGroups as $group){
		$link = JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $group->id.'&Itemid=' . $Itemid);
		$strInfo = '<font color="#AAAAAA" size="-1">';
	$nofMembers = $this->groupModel->countMemGrp($group->id) + 1;
	if($nofMembers == 0)
		$strInfo .= $nofMembers . ' ' . JText::_('Member') . ', ';
	else
		$strInfo .= $nofMembers . ' ' . JText::_('Members') . ', ';
		
	$nofPosts = $this->groupModel->countPostGrp($group->id);
	if($nofPosts == 0)
		$strInfo .= $nofPosts . ' ' . JText::_('Post');
	else
		$strInfo .= $nofPosts . ' ' . JText::_('Posts');
	$strInfo .= '</font>';
	?>
	<div class="awd_grp_ele"><a href="<?php echo $link;?>">
			<img src="<?php echo AwdwallHelperUser::getBigGrpImg64($group->image, $group->id);?>"  title="<?php echo AwdwallHelperUser::getDisplayName($groups->creator)?>" border="0" />
	</a>
		<div class="awd_subgrp_ele">
		<?php if((int)$group->privacy == 2){?>
		<img src="<?php echo JURI::base();?>components/com_awdwall/images/private_group.jpg" title="" border="0" />
		<?php }?>
		<a href="<?php echo $link;?>" ><?php echo $group->title;?></a><br /><br /><?php echo $strInfo;?></div>
		</div>
<?php	
		}
	}
	}else{?>
		<div><?php echo JText::_('There is no group');?></div>
	<?php }?>
	<br style="clear:left;"/>
	</div>
	<div id="tabs-2">
	<?php if($nofAllGrps){
	foreach($this->allGrps as $group){
	$link = JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $group->id.'&Itemid=' . $Itemid);
	$isMemberGrp = $this->groupModel->isMemberGrp($group->id, $user->id);
	$strInfo = '<font color="#AAAAAA" size="-1">';
	$nofMembers = $this->groupModel->countMemGrp($group->id) + 1;
	if($nofMembers == 0)
		$strInfo .= $nofMembers . ' ' . JText::_('Member') . ', ';
	else
		$strInfo .= $nofMembers . ' ' . JText::_('Members') . ', ';
		
	$nofPosts = $this->groupModel->countPostGrp($group->id);
	if($nofPosts == 0)
		$strInfo .= $nofPosts . ' ' . JText::_('Post');
	else
		$strInfo .= $nofPosts . ' ' . JText::_('Posts');
	$strInfo .= '</font>';
	?>
		<div class="awd_grp_ele">
		<?php if((int)$group->privacy == 2 && !$isMemberGrp){?>
		
			<img src="<?php echo AwdwallHelperUser::getBigGrpImg64($group->image, $group->id);?>"  title="<?php echo AwdwallHelperUser::getDisplayName($group->creator)?>" border="0" />
		
		<?php }else{?>
		<a href="<?php echo $link;?>">
			<img src="<?php echo AwdwallHelperUser::getBigGrpImg64($group->image, $group->id);?>"  title="<?php echo AwdwallHelperUser::getDisplayName($group->creator);?>" border="0" />
		</a>
		<?php }?>
		<div class="awd_subgrp_ele">
		<?php if((int)$group->privacy == 2){?>
		<img src="<?php echo JURI::base();?>components/com_awdwall/images/private_group.jpg" title="" border="0" />
		<?php }?>
		<?php if((int)$group->privacy == 2 && !$isMemberGrp && $group->creator!=$user->id){?>
		<?php echo $group->title;?>
		<?php }else{?>
		<a href="<?php echo $link;?>"><?php echo $group->title;?></a>
		<?php }?>
		<br /><br /><?php echo $strInfo;?>
		</div>
		</div>
	<?php } 
	
	}else{?>
		<div><?php echo JText::_('There is no group');?></div>
	<?php }?>
	<br style="clear:left;"/>
	</div>
	<div id="tabs-3">
	<?php if($nofPendings){
	foreach($this->pendings as $pending){
	?>
	<div class="awd_grp_ele" style="width:100%;" id="invite_block_<?php echo $pending->id;?>">
			<img src="<?php echo AwdwallHelperUser::getBigGrpImg64($pending->image, $pending->id);?>"  title="<?php echo $pending->title;?>" border="0" />
		<div class="awd_subgrp_ele">		
		<a href="javascript:void(0);"><?php echo $pending->title;?></a>
		<br /><br />
		<span id="invite_<?php echo $pending->id;?>"><?php echo JText::_('Invite you');?>&nbsp;&nbsp;<a href="javascript:void(0);" onclick="denyInvite('<?php echo 'index.php?option=com_awdwall&task=denyinvite&groupid=' . $pending->id . '&userid=' . $pending->user_id;?>', '<?php echo $pending->id;?>');" class="deny_friend"><?php echo JText::_('Deny');?></a> <?php echo JText::_('or');?> <a href="javascript:void(0);" onclick="acceptInvite('<?php echo 'index.php?option=com_awdwall&task=acceptinvite&groupid=' . $pending->id . '&userid=' . $pending->user_id;?>', '<?php echo $pending->id;?>');" class="accept_friend"><?php echo JText::_('Accept');?></a></span>
	</div>
	</div>
	<?php
	}
	}
	?>
	<br style="clear:left;"/>
	</div>
</div>

</div>
</div>
</div>

</div>
</div>
<input type="hidden" name="posted_wid" id="posted_wid" value="" />
<input type="hidden" name="wall_last_time" id="wall_last_time" value="<?php echo time();?>" />
<input type="hidden" name="layout" id="layout" value="mywall" />

