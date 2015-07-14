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

// get users's wall object

$userWall = &JFactory::getUser($this->wuid);

// set active tab

$wallActive = '';

$videoActive = '';

$imageActive = '';

$mp3Active = '';

$linkActive = '';

$fileActive = '';

$trailActive = '';

$pmActive = '';

if($this->task == 'videos')

	$videoActive = 'class="active"';

elseif($this->task == 'images')

	$imageActive = 'class="active"';

elseif($this->task == 'music')

	$mp3Active = 'class="active"';

elseif($this->task == 'links')

	$linkActive = 'class="active"';

elseif($this->task == 'files')

	$fileActive = 'class="active"';

elseif($this->task == 'trails')

	$trailActive = 'class="active"';

elseif($this->task == 'pm')

	$pmActive = 'class="active"';

else

	$wallActive = 'class="active"';

// get jomosical Itemid

$comItemid = AwdwallHelperUser::getComItemId();

//$friendJsUrl = JRoute::_('index.php?option=com_awdwall&task=friends&Itemid=' . $comItemid, false);

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
	#awd-mainarea .profileLink a, #awd-mainarea .profileMenu .ppm a, #awd-mainarea #msg_content .rbroundboxleft .mid_content a, #awd-mainarea .commentinfo a, #awd-mainarea .user_place ul.profileMenu li a{
		color:#<?php echo $this->color[2]; ?>;
	}
	#awd-mainarea #awd_message, #awd-mainarea .right_mid_content .walltowall li{
		color:#<?php echo $this->color[3]; ?>!important;
	}
	#awd-mainarea .wall_date{
		color:#<?php echo $this->color[4]; ?>;
	}
	#awd-mainarea .rbroundboxright, #awd-mainarea .fullboxtop, #awd-mainarea .rbroundboxleft_user, #awd-mainarea .rbroundboxleft, #awd-mainarea .lightblue_box, #awd-mainarea .rbroundboxrighttop{
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

			<img src="<?php echo AwdwallHelperUser::getBigGrpImg133($this->grpInfo->image, $this->grpInfo->id);?>"  title="<?php echo $this->grpInfo->title;?>" class="myavtar"/>

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
		  		 
		<img src="<?php echo AwdwallHelperUser::getBigAvatar19($member->user_id);?>"  title="<?php echo AwdwallHelperUser::getDisplayName($member->user_id);?>"  style="float:left;margin-top:0px;" border="0"  height="19" width="19" /></a>
		
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

				<img src="<?php echo AwdwallHelperUser::getBigAvatar19($membercreator->creator);?>"  title="<?php echo AwdwallHelperUser::getDisplayName($membercreator->creator);?>"  style="float:left;margin-top:0px;" border="0"  height="19" width="19"/></a>
				
				<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $membercreator->creator .'&Itemid=' . $Itemid);?>" style="text-decoration:none;margin-top:3px; margin-left:5px;">
		  <?php echo AwdwallHelperUser::getDisplayName($membercreator->creator);?>
			</a>
			
		  </div>
			<?php } ?>
            </div>
            <div class="about_br"> <div class="about_bl"></div></div>   
        </div>

		

	  </div>

    </div>

    <div class="rbroundboxrighttop"> <span class="bl2"></span><span class="br2"></span>

      <div class="user_place"> 

    <span class="profileName">

	<a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->grpInfo->id . '&Itemid=' . $Itemid, false);?>">

<?php echo $this->grpInfo->title;?>

	</a>

    </span>&nbsp;

      <span class="profileStatus" id="awd_profile_status">

	  <?php

		if(isset($this->latestPost->message))

			echo $this->latestPost->message;		

	  ?>

	  </span>

      </div>

	  

 <div class="fullboxnew" >

 <!-- start msg content --> 

 <span id="msg_loader"></span>

 <div id="msg_content">

<script type="text/javascript">

	jQuery(function() {

		jQuery("#tabs").tabs();

	});

</script>

<div id="tabs">

	<ul>

		<li><a href="#tabs-1"><b><?php echo JText::_('General settings');?></b></a></li>

		<li><a href="#tabs-2"><b><?php echo JText::_('Delete group');?></b></a></li>		

	</ul>

	<div id="tabs-1">

	<?php JHTML::_('behavior.formvalidation'); ?>

<script language="javascript">

function myValidate(f) {

   if(document.formvalidator.isValid(f)){      

      return true; 

   }

   else{

      var msg = '';

      //if($('group_title').hasClass('invalid')){msg += '\n\n\t* Invalid E-Mail Address';}

      //alert(msg);

   }

   return false;

}

</script>

<form name="frmNewGroup" id="frmNewGroup" enctype="multipart/form-data" method="post" action="index.php?option=com_awdwall" class="form-validate" onSubmit="return myValidate(this);">

<div id="aw_ng_body">

<p><label><?php echo JText::_('Group Title');?>:</label> <input type="text" name="group_title" id="group_title" maxlength="150" size="20" value="<?php echo $this->grpInfo->title;?>" class="required" /></p>

<p><label><?php echo JText::_('Description');?>:</label> <textarea name="group_description" id="group_description" rows="5" cols="30" class="required"><?php echo $this->grpInfo->description;?></textarea></p>

<p class="awd_ng_type"><label><?php echo JText::_('Type');?>:</label> 

<span class="awd_ng_rad_group">

<?php

$chk1 = '';

$chk2 = '';

if((int)$this->grpInfo->privacy == 1)

	$chk1 = 'checked="checked"';

else

	$chk2 = 'checked="checked"';

?>

<input type="radio" name="group_type" id="group_type0" value="1" <?php echo $chk1;?>/><?php echo JText::_('Public group');?>

<br /><input type="radio" name="group_type" id="group_type1" value="2" <?php echo $chk2;?>/><?php echo JText::_('Private group');?></span>

</p>

<br style="clear:both;"/>

<p style="margin-top:10px;"><label><?php echo JText::_('Image');?>:</label> <input type="file" name="awd_group_image" id="awd_group_image" maxlength="150" size="20"  /></p>

<!--br style="clear:both;"/><br />

<p><label><?php //echo JText::_('Current Image');?>:</label> <img src="<?php //echo AwdwallHelperUser::getGrpImg($user->id, '');?>" alt="<?php //echo JText::_('Image');?>" width="" height="" /></p>

<p><label><?php echo JText::_('New Image');?>:</label> <input type="file" name="grp_image" id="grp_image" /></p-->

<br style="clear:both;"/>

<p class="submit"><input type="submit" name="submit" id="submit" value="<?php echo JText::_('Create Group');?>" class="postbtn_group_black" /></p>

</div>
<input type="hidden" name="Itemid" id="Itemid" value="<?php echo $Itemid;?>" />
<input type="hidden" name="task" id="task" value="saveGroup" />

<input type="hidden" name="option" id="option" value="com_awdwall" />

<input type="hidden" name="awd_group_id" id="awd_group_id" value="<?php echo $this->grpInfo->id;?>" />

<input type="hidden" name="awd_group_old_image" id="awd_group_old_image" value="<?php echo $this->grpInfo->image;?>" />

<?php echo JHTML::_( 'form.token' ); ?>

</form>

	</div>

	<div id="tabs-2">

	<form name="frmNewGroup" id="frmNewGroup" enctype="multipart/form-data" method="post" action="index.php?option=com_awdwall" class="form-validate" onSubmit="return myValidate(this);">

<div id="aw_ng_body" style="padding:0px;">



<!--p class="awd_ng_type"><label style="width:90px;text-align:left;padding:0px;"><?php echo JText::_('Posts action');?>:</label> 

<span class="awd_ng_rad_group">

<input type="radio" name="group_type" id="group_type0" value="1" /><?php echo JText::_('Delete Keep Post');?>

<br /><input type="radio" name="group_type" id="group_type1" value="2" /><?php echo JText::_('Delete Not Keep Post');?></span>

</p-->

<br style="clear:both;"/>

<p class="submit"><input type="submit" name="submit" id="submit" value="<?php echo JText::_('Delete Group');?>" class="postbtn_group_black" /></p>

</div>

<input type="hidden" name="task" id="task" value="deleteGroup" />

<input type="hidden" name="option" id="option" value="com_awdwall" />

<input type="hidden" name="awd_group_id" id="awd_group_id" value="<?php echo $this->grpInfo->id;?>" />

<?php echo JHTML::_( 'form.token' ); ?>

</form>

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
ll=jQuery(".awdfullbox_top").width();
tt=(27/100)*ll+40;
var bb=ll-tt;
mm=Math.floor((bb*100)/ll)-.5;
var new_number = mm+'%';
jQuery(".rbroundboxrighttop").css("width",new_number);
};
 
 
 var resizeTimer = null;
jQuery(window).bind('resize', function() {
    if (resizeTimer) clearTimeout(resizeTimer);
    resizeTimer = setTimeout(adjustwidth, 10);
});

</script>
<?php }  ?>
