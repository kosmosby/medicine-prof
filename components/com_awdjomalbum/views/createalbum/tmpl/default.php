 <?php 
defined('_JEXEC') or die('Restricted access');
$Itemid=AwdwallHelperUser::getComItemId();
$wallversion=checkversionwall();
$db		=& JFactory :: getDBO();
$user =& JFactory::getUser();
$pendingFriends = JsLib::getPendingFriends($user->id);
$groupsUrl=JRoute::_('index.php?option=com_awdwall&task=groups&Itemid=' . $Itemid, false);
if($wallversion=='cb')
{
	$friendJsUrl = JRoute::_('index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid, false);
	$accountUrl=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $user->id . '&Itemid=' . AwdwallHelperUser::getJsItemId(), false);
}
elseif($wallversion=='js')
{
	$friendJsUrl = JRoute::_('index.php?option=com_community&view=friends&userid=' . $user->id . '&Itemid=' . AwdwallHelperUser::getJsItemId(),false);
	$accountUrl=JRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id . '&Itemid=' . AwdwallHelperUser::getJsItemId(), false);
}
else
{
	$friendJsUrl = JRoute::_('index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid, false);
	$accountUrl=JRoute::_('index.php?option=com_awdwall&task=uploadavatar&Itemid=' . $Itemid, false);
}
//$config 		= &JComponentHelper::getParams('com_awdwall');
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
$width 		= $config->get('width', 725);
$displayName 	= $config->get('display_name', 1);
//$query = "SELECT params FROM #__components WHERE `name`='Colors' AND `admin_menu_link`='option=com_awdwall&controller=colors'";
$link='index.php?option=com_awdwall&controller=colors';
$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
$params = json_decode( $db->loadResult(), true );
for($i=1; $i<=14; $i++)
{
	$str_color = 'color'.$i;			
	$color[$i]= $params[$str_color];
}
 $link3=JRoute::_('index.php?option=com_awdjomalbum&view=awdalbumlist&wuid='.$user->id.'&Itemid='.AwdwallHelperUser::getComItemId(),false);
$albumlink=JRoute::_("index.php?option=com_awdjomalbum&view=awdalbumlist&wuid=".$user->id."&Itemid=".AwdwallHelperUser::getComItemId(),false);
 ?>
<link rel="stylesheet" href="<?php JURI::base();?>components/com_awdjomalbum/css/style.css" type="text/css" />
<link rel="stylesheet" href="<?php JURI::base();?>components/com_awdjomalbum/css/style2.css" type="text/css" />
<script type="text/javascript">
	function validateForm()
	{
		var form = document.CreateAlbumForm;
		
		if(form.name.value=='')
		{
			alert('Album name can not be left blank');
			form.name.focus();
			return false;
		} 
		
		if(form.location.value=='')
		{
			alert('Location can not be left blank');
			form.location.focus();
			return false;
		}
	
	}
	</script>
<div  id="awd-mainarea" style="width:100%;">
<style type="text/css">
table
{
	border:solid 0px #ddd !important
}
tr, td
{
	border:none !important;
}
#awd-mainarea a,#awd-mainarea a:visited,#awd-mainarea a:hover{
color:#<?php echo $color[2]; ?>;
text-decoration:none;
}
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
	#awd-mainarea .mid_content_top, #awd-mainarea .rbroundboxright, #awd-mainarea .fullboxtop, #awd-mainarea .rbroundboxleft_user, #awd-mainarea .lightblue_box, #awd-mainarea .rbroundboxrighttop{
		background-color:#<?php echo $color[5]; ?>;
	}
	#awd-mainarea .rbroundboxleft .mid_content a, #awd-mainarea .blog_groups, #awd-mainarea .blog_groups a, #awd-mainarea .blog_friend, #awd-mainarea .blog_friend a, #awd-mainarea a.authorlink{
		color:#<?php echo $color[6]; ?>;
	}
	#msg_content .rbroundboxleft, #msg_content .awdfullbox{
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
		background-color:#<?php echo $this->color[13]; ?>;
	}
	#awd-mainarea .round, #awd-mainarea .search_user{
		background-color:#<?php echo $this->color[14]; ?>;
	}
	<?php } ?>
	.commentBox2{
	background-color:#<?php echo $color[12]; ?>;
	}
.albumMid h3{color:#<?php echo $color[2]; ?>;}
textarea
{
height:33px !important;
}
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
#awd-mainarea tr, #awd-mainarea td
{
border:0 none !important;
padding:1px 0px !important;

}
.albumMid .select, .editalbum .select
{
background-color: #E5E5E5;
border: 0 none;
font-weight: bold;
height: 19px!important;
line-height: 19px!important;
margin: 0 0 0 20px!important;
padding: 0 !important;
width: 94px;
}
</style>
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
        <li><a <?php if($layout == 'main') {echo 'class="activenews mainactivenews"';}else{echo 'class="newsfeed mainactivenews"';} ?> href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('News Feed');?>" ></a> </li>
		<?php if((int)$user->id){?>
        <?php if($template=='default'){?>
        <li style="float:left; display:block; position:relative;">
            <a href="javascript:void(0)" title="Notifications" id="notifications-button">
                <img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/8.png" alt=""/>
                <span class="message-count" style="display:none"></span>
            </a>
        </li>
        <?php }?>
        <li class="separator"> </li>
        <li><a href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('Friends');?>"><?php echo JText::_('Friends');?><font color="red" size="1"><?php if((int)$pendingFriends > 1) echo '(' . $pendingFriends . JText::_('Requests') . ')';elseif((int)$pendingFriends == 1) echo '(' . $pendingFriends . JText::_('') . ')';?></font></a></li>
        
		<?php if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) {?>
		<li class="separator"> </li>
        <li> <a href="<?php echo $groupsUrl;?>" title="<?php echo JText::_('Groups');?>"><?php echo JText::_('Groups');?><font color="red" size="1"><?php if((int)pendingGroups > 1) echo '(' . $pendingGroups . JText::_('') . ')';elseif((int)$pendingGroups == 1) echo '(' . $pendingGroups . JText::_('') . ')';?></font></a> </li>
	<?php }?>
        <li class="separator"> </li>
		 <li> <a href="<?php echo JRoute::_($albumlink);?>" title="<?php echo JText::_('Gallery');?>"><?php echo JText::_('Gallery');?></a> </li>
        <li class="separator"> </li>
         <li> <a href="<?php echo $accountUrl;?>" title="<?php echo JText::_('Account');?>"><?php echo JText::_('Account');?></a> </li>
		 
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
		<li style="float:right;"  class="toolbaravtar">
        <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('My Wall');?>" class="toolbaravtar">
        <div style="height:32px; margin-right:15px; ">
        <div style=" float:left; width:32px;height:32px;box-shadow: 0px 0px 3px #fff;"><img src="<?php echo AwdwallHelperUser::getBigAvatar32($user->id);?>" class="avtartool "  height="32" width="32"/></div>
        <div style=" float:left; width:auto; margin-left:6px;padding-top:3px; height:32px;">
			<?php echo AwdwallHelperUser::getDisplayName($user->id);?></div>
        </div>
        </a>
        </li>
        	<?php }?>
      </ul>
    </div>
  </div>
 <div class="awdfullbox" style="width:100%;"> <span class="bl"></span>
    
    <div class="rbroundboxrighttop" style="width:99.6%;min-height:150px; padding:0px;"> <span class="bl2"></span><span class="br2"></span>
 <div class="fullboxnew" >
 <!-- start msg content --> 
 <div id="msg_content">
 <!-- start block msg -->
 <table width="100%" border="0" cellspacing="2" cellpadding="2" style="margin-top:15px;">
  <tr>
    <td align="left"><span class="username"><?php if((int)$displayName == 1) {echo  $user->username; }else{ echo $user->name; }?> &gt;&nbsp;<?php echo JText::_('Pictures');?></span></td>
    <td align="right"><span  class="add_as_friend"><a href="<?php echo $link3; ?>"  title="<?php echo JText::_('+ Back to albums');?>"><?php echo JText::_('+ Back to albums');?></a></span>		 
	</td>
  </tr>
</table>
<div style="clear:both; height:10px;"></div> 
<center>
<table cellpadding="0" cellspacing="0" align="center" ><tr><td align="center" >
	<form action="" name="CreateAlbumForm" method="post" onsubmit="return validateForm();">
		<div class="albumParent">
			<div class="albumLeft"></div>		
			<div class="albumMid">
				<!--<font style="font-size:16px; font-weight:bold; color:#<?php echo $color[1]; ?>"><?php echo JText::_('Create Album');?></font>-->
				<table width="100%" cellpadding="1" cellspacing="1" style="border-collapse:separate!important;">
					<tr>
						<td><?php echo JText::_('Album Name:');?> &nbsp;</td>
						<td><input type="text" name="name" id="name" value=""    maxlength="100"  class="input_border"/></td>
					</tr>
					<tr>
						<td><?php echo JText::_('Location :');?> &nbsp;</td>
						<td><input type="text" name="location"  id="location" value=""   maxlength="100" class="input_border"/></td>
					</tr>
					
					<tr>
						<td><?php echo JText::_('Description :');?>&nbsp;</td>
						<td><textarea name="descr"  id="descr" value="" class="input_border" rows="1" cols="30"></textarea>
						<!--<input type="text" name="descr"  id="descr" value=""  maxlength="100" class="text_comment" ro/>--></td>
					</tr>
					<tr>
						<td><?php echo JText::_('Share album with:');?> &nbsp;</td>
						<td class="privacy"><select name="privacy" id="privacy" class="select">
							<option value="0"><?php echo JText::_('All');?></option>
							<option value="1"><?php echo JText::_('Friends Only');?></option>
							<option value="2"><?php echo JText::_('Friends Of Friends');?></option>
							<option value="3"><?php echo JText::_('Myself');?></option>
							</select>						</td>
					</tr>
					<tr><?php 	$link=JRoute::_('index.php?option=com_awdjomalbum&Itemid='.AwdwallHelperUser::getComItemId());?>
						<td></td>
					  <td align="right" valign="middle" style="padding-top:0px"><a href="<?php echo $link3; ?>" >
					   <input type="button" name="cancel" value="<?php echo JText::_('Cancel');?>" class="postButton_small" /> </a>
				      <input type="submit" name="submit" value="<?php echo JText::_('Create');?>" class="postButton_small" /></td>
					</tr>
				</table>
			</div>
			<div class="albumRight"></div>
		</div>
 	<input type="hidden" name="option" value="com_awdjomalbum" />
	<input type="hidden" name="task" id="task" value="savealbum" />
	<input type="hidden" name="view" value="createalbum" />
	<input type="hidden" name="id" value="<?php echo $_REQUEST['id'];?>" />
	<input type="hidden" name="userid" value="<?php echo $user->id; ?>" />
	<input type="hidden" name="firsttime" value="1" />
	
</form>
</td></tr></table>
</center>
<div style="clear:both; height:10px;"></div> 
  </div>
 </div>
 </div>
     </div>
 </div>
