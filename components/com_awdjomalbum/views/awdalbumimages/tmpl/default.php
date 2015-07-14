<?php defined('_JEXEC') or die('Restricted access'); 
$Itemid=AwdwallHelperUser::getComItemId();
$username=$this->username;
$albumname=$this->albumname;
$userid=$this->userid;
$useralbumlisturl=JRoute::_("index.php?option=com_awdjomalbum&view=awdalbumlist&wuid=".$userid."&Itemid=".$Itemid, false);
$userprofileurl=JRoute::_("index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=".$userid."&Itemid=".$Itemid, false);
$user =& JFactory::getUser();
$photorows=$this->rows;
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
$albumlink=JRoute::_("index.php?option=com_awdjomalbum&view=awdalbumlist&wuid=".$user->id."&Itemid=".AwdwallHelperUser::getComItemId(),false);
?>
<link rel="stylesheet" href="<?php JURI::base();?>components/com_awdjomalbum/css/style.css" type="text/css" />
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
#awd-mainarea a:link,#awd-mainarea a:visited,#awd-mainarea a:hover{
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
			<?php if((int)$displayName == 1) {?>
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
 <div class="awdfullbox" style="width:100%;"> <span class="bl"></span>
    
    <div class="rbroundboxrighttop" style="width:99.8%;min-height:150px; padding:0px;"> <span class="bl2"></span><span class="br2"></span>
 <div class="fullboxnew" >
 <!-- start msg content --> 
 <div id="msg_content">
 <table width="100%" border="0" cellspacing="2" cellpadding="2" style="margin-top:15px;">
  <tr>
    <td align="left"><span class="username"><?php echo $albumname; ?></span><br /><br /><?php echo JText::_('By').'&nbsp;<a href="'.$userprofileurl.'">'.$username.'</a>&nbsp;(<a href="'.$useralbumlisturl.'">'.JText::_('Albums').'</a>)'; ?><br /><br /></td>
  </tr>
</table>
<?php 
for ($i=0, $n=count( $this->rows ); $i < $n; $i++)
{
$link 	=JRoute::_('index.php?option=com_awdjomalbum&view=awdimagelist&tmpl=component&pid='.$this->rows[$i]->id.'&albumid='.$this->rows[$i]->albumid.'&Itemid='.AwdwallHelperUser::getComItemId());
?>
<div class="awdimgborderdiv">
 <!--<a href="<?php echo $link;?>" rel="iframe-full-full"  class="pirobox_gall1">-->
  <a href="<?php echo $link;?>" class='awdiframe'>
<img src="<?php echo JURI::base();?>images/awd_photo/awd_thumb_photo/<?php echo $this->rows[$i]->image_name; ?>" />
</a>
</div>
<?php
}
?>
  </div>
 </div>
 </div>
     </div>
 </div>
  
