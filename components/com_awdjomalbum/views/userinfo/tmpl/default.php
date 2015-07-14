<?php defined('_JEXEC') or die('Restricted access'); 
$user =& JFactory::getUser();
$username=$this->username;
$userid=$this->userid;
$rows=$this->rows;
$row=$this->rows[0];
$userhighlightfields=explode(",",$row->userhighlightfields);
$count=count($this->colrows);
$colrow=$this->colrows[0];
$colrow1=$this->colrows[1];
$colrow2=$this->colrows[2];
$colrow3=$this->colrows[3];
$colrow4=$this->colrows[4];
$Itemid=AwdwallHelperUser::getComItemId();
$wallversion=checkversionwall();
$db		=& JFactory :: getDBO();
$user =& JFactory::getUser();
$pendingFriends = JsLib::getPendingFriends($user->id);
$groupsUrl=JRoute::_('index.php?option=com_awdwall&task=groups&Itemid=' . $Itemid, false);
if($wallversion=='cb')
{
$friendJsUrl = JRoute::_('index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid, false);
$accountUrl=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $user->id . '&Itemid=' .  AwdwallHelperUser::getJsItemId(), false);
}
elseif($wallversion=='js')
{
$friendJsUrl = JRoute::_('index.php?option=com_community&view=friends&userid=' . $user->id . '&Itemid=' .  AwdwallHelperUser::getJsItemId(),false);
$accountUrl=JRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id . '&Itemid=' .  AwdwallHelperUser::getJsItemId(), false);
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
$fields			= $config->get('fieldids', '');
$display_gallery = $config->get('display_gallery', 1);
$basicInfo 		= JsLib::getUserBasicInfo($user->id, $fields);
$cbfields=explode(",",$row->cbfields);

//echo '<pre>';
//print_r($basicInfo);
//echo '</pre>';
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
#awd-mainarea table
{
	border:solid 0px #ddd !important
}
#awd-mainarea tr,#awd-mainarea  td
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
	#msg_content .rbroundboxleft,#awd-mainarea  #msg_content .awdfullbox{
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
	#awd-mainarea .commentBox2{
	background-color:#<?php echo $color[12]; ?>;
	}
#awd-mainarea textarea
{
width:70% !important;
}
#awd-mainarea .hightlightboxMain{
float:left;
margin:5px 0px; 
/*background-color:#<?php echo $color[12]; ?>;
border:5px solid #ffffff;
*/padding:5px;
width:97%;
}
#awd-mainarea .hightlightboxleft{
float:left;
width:65%;
color:#<?php echo $color[3]; ?>;
font-size:11px;
}
#awd-mainarea .hightlightboxright{
float:left;
width:34%;
text-align:right;
}
#awd-mainarea ul.hightlightul{
list-style:none;
margin:0;
padding:0;
}
#awd-mainarea ul li.workat{
background-image:url(<?php echo JURI::base();?>components/com_awdwall/images/workat.png)!important;
background-repeat:no-repeat;
background-position:left center;
padding-left:15px!important;
}
#awd-mainarea span.hightlightlevel{
color:#<?php echo $color[11]; ?>;
font-size:11px;
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
  <script type="text/javascript">
	function validateForm()
	{
		var form = document.UserInfoForm;
		
//		if(form.currentcity.value=='' && form.hometown.value=='' && form.languages.value=='' && form.aboutme.value=='')
//
//		{
//
//			form.currentcity.focus();
//
//			return false;
//
//		} 
		var found = 0;  
		for(var i = 0; i < document.getElementById('highlightfields').options.length; i++) 
		{  
			
			if(document.getElementById('highlightfields').options[i].selected) 
			{  
				found++;  
			}  
		}  
		if(found > 4) {  
			alert("<?php echo JText::_('You can select maximum four to display in highlightbox.');?>");
			return false;
		}  
		
		
	}
</script>
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
        <li><a href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('Friends');?>"><?php echo JText::_('Friends');?><font color="red" size="1">
          <?php if((int)$pendingFriends > 1) echo '(' . $pendingFriends . JText::_('Requests') . ')';elseif((int)$pendingFriends == 1) echo '(' . $pendingFriends . JText::_('') . ')';?>
          </font></a></li>
        <?php if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) {?>
        <li class="separator"> </li>
        <li> <a href="<?php echo $groupsUrl;?>" title="<?php echo JText::_('Groups');?>"><?php echo JText::_('Groups');?><font color="red" size="1">
          <?php if((int)pendingGroups > 1) echo '(' . $pendingGroups . JText::_('') . ')';elseif((int)$pendingGroups == 1) echo '(' . $pendingGroups . JText::_('') . ')';?>
          </font></a> </li>
        <?php }?>
        <?php  if($display_gallery==1){?>
        <li class="separator"> </li>
		 <li> <a href="<?php echo JRoute::_($albumlink);?>" title="<?php echo JText::_('Gallery');?>"><?php echo JText::_('Gallery');?></a> </li>
		<?php }?>
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
        <li style="float:right;"  class="toolbaravtar"> <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('My Wall');?>" class="toolbaravtar">
        <div style="height:32px; margin-right:15px; ">
        <div style=" float:left; width:32px;height:32px;box-shadow: 0px 0px 3px #fff;"><img src="<?php echo AwdwallHelperUser::getBigAvatar32($user->id);?>" class="avtartool "  height="32" width="32"/></div>
        <div style=" float:left; width:auto; margin-left:6px;padding-top:3px; height:32px;">
			<?php echo AwdwallHelperUser::getDisplayName($user->id);?></div>
        </div>
          </a> </li>
        <?php }?>
      </ul>
    </div>
  </div>
  <div class="awdfullbox" style="width:100%;"> <span class="bl"></span>
    <div class="rbroundboxrighttop" style="width:99.8%;min-height:150px; padding:0px;"> <span class="bl2"></span><span class="br2"></span>
      <div class="fullboxnew" >
        <!-- start msg content -->
        <div id="msg_content">
          <!-- start block msg -->
          <table width="100%" border="0" cellspacing="2" cellpadding="2" style="margin-top:15px;">
            <tr>
              <td align="left"><span class="username"><?php echo $username; ?> &gt; <?php echo JText::_('Info');?></span></td>
            </tr>
          </table>
          <div style="clear:both; height:10px;"></div>
          <?php				
if($user->id==$_REQUEST['wuid'])
{
	$albumconfig 			= &JComponentHelper::getParams('com_awdjomalbum');
	$display_personaltab 	= $albumconfig->get('display_personaltab', '1');
	$display_socialtab 		= $albumconfig->get('display_socialtab', '1');
	$display_contacttab 	= $albumconfig->get('display_contacttab', '1');
	$display_othertab 		= $albumconfig->get('display_othertab', '1');
	$display_birthdayfield 	= $albumconfig->get('display_birthdayfield', '1');
?>
<form action="" name="UserInfoForm" method="post" onsubmit="return validateForm();">
<ul id="awdtabs">
<?php if($display_personaltab){?>
  <li><a href="#" name="tab1"><?php echo JText::_('Personal Info');?></a></li>
<?php } ?>
<?php if($display_socialtab){?>
  <li><a href="#" name="tab2"><?php echo JText::_('Social Info');?></a></li>
<?php } ?>
<?php if($display_contacttab){?>
  <li><a href="#" name="tab3"><?php echo JText::_('Contact Info');?></a></li>
<?php } ?>
<?php if($display_othertab){?>
  <li><a href="#" name="tab4"><?php echo JText::_('Other Info');?></a></li>
<?php } ?>
</ul>
<div id="awdcontent">
<?php if($display_personaltab){?>
<div id="tab1">
            <table  cellpadding="10" cellspacing="10" border="0" width="98%">
              <tr>
                <td></td>
                <td></td>
                <td align="center"><?php echo JText::_('Display');?></td>
              </tr>
		<?php
            if(is_array($basicInfo)){ 
            foreach($basicInfo as $arr){
			$cbff='display_'.str_replace(' ','',$arr[1]);
        ?>
              <tr>
                <td><?php echo $arr[1];?>: </td>
                <td><?php echo $arr[0];?></td>
                <td align="center" height="30"><input type="checkbox" name="display_<?php echo str_replace(' ','',$arr[1]);?>" value="display_<?php echo str_replace(' ','',$arr[1]);?>" <?php if(in_array($cbff,$cbfields)) echo 'checked="checked"'; ?> /></td>
              </tr>
		<?php 
            } 
         } 
        ?>
<?php if($display_birthdayfield){?>
          <tr>
                <td><?php echo JText::_('Birthday');?>: </td>
                <td><input type="text" name="birthday"  id="birthday" value="<?php echo $row->birthday; ?>"   maxlength="100" class="input_border"/>
                  <?php echo JText::_(' (YYYY-MM-DD)');?>&nbsp;&nbsp;<input type="checkbox" name="hide_birthyear" value="1" <?php if($row->hide_birthyear==1) echo 'checked="checked"'; ?> />&nbsp;<?php echo JText::_('HIDE BIRTHDAY YEAR');?></td>
                <td align="center"><input type="checkbox" name="display_birthday" id="display_birthday" value="1" <?php if($row->display_birthday==1) echo 'checked="checked"'; ?> /></td>
              </tr>
<?php } ?>
</table>
</div>
<?php } ?>
<?php if($display_socialtab){?>
<div id="tab2">
<table  cellpadding="10" cellspacing="10" border="0" width="98%">
  <tr>
    <td></td>
    <td></td>
    <td align="center"><?php echo JText::_('Display');?></td>
  </tr>
  <tr>
    <td><?php echo JText::_('Skype Userid');?> </td>
    <td ><input type="text" name="skype_user"  id="skype_user" value="<?php echo $row->skype_user; ?>"   maxlength="100" class="input_border"/>
    </td>
    <td align="center"><input type="checkbox" name="display_skype_user" value="1" <?php if($row->display_skype_user==1) echo 'checked="checked"'; ?> /></td>
  </tr>
  <tr>
    <td><?php echo JText::_('Facebook Userid');?> </td>
    <td ><input type="text" name="facebook_user"  id="facebook_user" value="<?php echo $row->facebook_user; ?>"   maxlength="100" class="input_border"/>
    </td>
    <td align="center"><input type="checkbox" name="display_facebook_user" value="1" <?php if($row->display_facebook_user==1) echo 'checked="checked"'; ?> /></td>
  </tr>
  <tr>
    <td><?php echo JText::_('Twitter Userid');?> </td>
    <td ><input type="text" name="twitter_user"  id="twitter_user" value="<?php echo $row->twitter_user; ?>"   maxlength="100" class="input_border"/>
    </td>
    <td align="center"><input type="checkbox" name="display_twitter_user" value="1" <?php if($row->display_twitter_user==1) echo 'checked="checked"'; ?> /></td>
  </tr>
  <tr>
    <td >&nbsp;</td>
    <td ></td>
    <td ></td>
  </tr>
  <tr>
    <td><?php echo JText::_('Twitter Privacy');?></td>
    <td><select name="twitter_privacy">
        <option value="0" <?php if($row->twitter_privacy==0) echo 'selected="selected"';?> ><?php echo JText::_('Everyone');?></option>
        <option value="1" <?php if($row->twitter_privacy==1) echo 'selected="selected"';?> ><?php echo JText::_('Friends Only');?></option>
        <option value="2" <?php if($row->twitter_privacy==2) echo 'selected="selected"';?> ><?php echo JText::_('Friends Of Friends');?></option>
      </select>
    </td>
    <td></td>
  </tr>
  <tr>
    <td >&nbsp;</td>
    <td ></td>
    <td ></td>
  </tr>
  <tr>
    <td><?php echo JText::_('Display Tweets In Wall Post');?></td>
    <td><input type="radio" name="display_twitter_post" value="1" <?php if($row->display_twitter_post==1) echo 'checked="checked"'; ?> />
      <?php echo '&nbsp;'.JText::_('M_YES');?>
      <input type="radio" name="display_twitter_post" value="0" <?php if($row->display_twitter_post==0) echo 'checked="checked"'; ?> />
      <?php echo '&nbsp;'.JText::_('M_NO');?> </td>
    <td></td>
  </tr>
  <tr>
    <td >&nbsp;</td>
    <td ></td>
    <td ></td>
  </tr>
  <tr>
    <td><?php echo JText::_('Youtube Userid');?> </td>
    <td ><input type="text" name="youtube_user"  id="youtube_user" value="<?php echo $row->youtube_user; ?>"   maxlength="100" class="input_border"/>
    </td>
    <td align="center"><input type="checkbox" name="display_youtube_user" value="1" <?php if($row->display_youtube_user==1) echo 'checked="checked"'; ?> /></td>
  </tr>
</table>
</div>
<?php } ?>
<?php if($display_contacttab){?>
<div id="tab3">
<table  cellpadding="10" cellspacing="10" border="0" width="98%">
  <tr>
    <td></td>
    <td></td>
    <td align="center"><?php echo JText::_('Display');?></td>
  </tr>
  <tr>
    <td><?php echo JText::_('Working At');?>:</td>
    <td ><input type="text" name="workingat"  id="workingat"  value="<?php echo $row->workingat; ?>"   maxlength="100" class="input_border"/>
    </td>
    <td align="center"><input type="checkbox" name="display_workingat" value="1" <?php if($row->display_workingat==1) echo 'checked="checked"'; ?> /></td>
  </tr>
  <tr>
    <td><?php echo JText::_('Studied');?>:</td>
    <td ><input type="text" name="studied"  id="studied"  value="<?php echo $row->studied; ?>"   maxlength="100" class="input_border"/>
    </td>
    <td align="center"><input type="checkbox" name="display_studied" value="1" <?php if($row->display_studied==1) echo 'checked="checked"'; ?> /></td>
  </tr>
  <tr>
    <td><?php echo JText::_('Live in');?>:</td>
    <td ><input type="text" name="livein"  id="livein"  value="<?php echo $row->livein; ?>"   maxlength="100" class="input_border"/>
    </td>
    <td align="center"><input type="checkbox" name="display_livein" value="1" <?php if($row->display_livein==1) echo 'checked="checked"'; ?> /></td>
  </tr>
  <tr>
    <td><?php echo JText::_('Phone');?>:</td>
    <td ><input type="text" name="phone"  id="phone"  value="<?php echo $row->phone; ?>"   maxlength="100" class="input_border"/>
    </td>
    <td align="center"><input type="checkbox" name="display_phone" value="1" <?php if($row->display_phone==1) echo 'checked="checked"'; ?> /></td>
  </tr>
  <tr>
    <td><?php echo JText::_('Cell');?>:</td>
    <td ><input type="text" name="cell"  id="cell"  value="<?php echo $row->cell; ?>"   maxlength="100" class="input_border"/>
    </td>
    <td align="center"><input type="checkbox" name="display_cell" value="1" <?php if($row->display_cell==1) echo 'checked="checked"'; ?> /></td>
  </tr>
  <tr>
    <td >&nbsp;</td>
    <td ></td>
    <td ></td>
  </tr>
  </table>
</div>
<?php } ?>
<?php if($display_othertab){?>
<div id="tab4">
<table  cellpadding="10" cellspacing="10" border="0" width="98%">
  <tr>
    <td></td>
    <td></td>
    <td align="center"><?php echo JText::_('Display');?></td>
  </tr>
<tr>
<td >&nbsp;</td>
<td ></td>
<td ></td>
</tr>
<tr>
<td valign="top"><?php echo JText::_('Highlight Fields');?>:</td>
<td  valign="top"><select name="highlightfields[]" id="highlightfields" multiple="multiple" size="10" style="height:100px;">
<?php if($display_personaltab){?>
<?php 
	if(is_array($basicInfo)){ 
	foreach($basicInfo as $arr){
?>
<option value="<?php echo $arr[1];?>" <?php if(in_array($arr[1],$userhighlightfields)){ echo 'selected="selected"';}?> ><?php echo $arr[1];?></option>
<?php } 
}
?>
<?php 
}
?>
<?php if($display_socialtab){?>
<option value="skype_user" <?php if(in_array('skype_user',$userhighlightfields)){ echo 'selected="selected"';}?> ><?php echo JText::_('Skype Userid');?></option>
<option value="facebook_user" <?php if(in_array('facebook_user',$userhighlightfields)){ echo 'selected="selected"';}?> ><?php echo JText::_('Facebook Userid');?></option>
<option value="twitter_user" <?php if(in_array('twitter_user',$userhighlightfields)){ echo 'selected="selected"';}?> ><?php echo JText::_('Twitter Userid');?></option>
<option value="youtube_user" <?php if(in_array('youtube_user',$userhighlightfields)){ echo 'selected="selected"';}?> ><?php echo JText::_('Youtube Userid');?></option>
<?php } ?>
<?php if($display_contacttab){?>
<option value="workingat" <?php if(in_array('workingat',$userhighlightfields)){ echo 'selected="selected"';}?> ><?php echo JText::_('Working At');?></option>
<option value="studied" <?php if(in_array('studied',$userhighlightfields)){ echo 'selected="selected"';}?> ><?php echo JText::_('Studied');?></option>
<option value="livein" <?php if(in_array('livein',$userhighlightfields)){ echo 'selected="selected"';}?> ><?php echo JText::_('Live in');?></option>
<option value="phone" <?php if(in_array('phone',$userhighlightfields)){ echo 'selected="selected"';}?> ><?php echo JText::_('Phone');?></option>
<option value="cell" <?php if(in_array('cell',$userhighlightfields)){ echo 'selected="selected"';}?> ><?php echo JText::_('Cell');?></option>
<?php } ?>
</select>
</td>
<td ></td>
</tr>
<?php
if($colrow->value && $colrow->published)
{ ?>
<tr>
<td>
<?php echo $colrow->value;?> :</td>
<td >
<textarea name="<?php echo $colrow->colname;?>"  id="<?php echo $colrow->colname;?>"  class="input_border" rows="2" cols="40"><?php echo $row->col1; ?></textarea>
</td>
<td align="center"><input type="checkbox" name="display_col1" value="1" <?php if($row->display_col1==1) echo 'checked="checked"'; ?> /></td>
</tr>
<?php }
if($colrow1->value && $colrow1->published)
{ ?>
<tr>
<td>
<?php echo $colrow1->value;?> :</td>
<td >
<textarea name="<?php echo $colrow1->colname;?>"  id="<?php echo $colrow1->colname;?>"  class="input_border" rows="2" cols="40"><?php echo $row->col2; ?></textarea>
</td>
<td align="center"><input type="checkbox" name="display_col2" value="1" <?php if($row->display_col2==1) echo 'checked="checked"'; ?> /></td>
</tr>
<?php }
if($colrow2->value && $colrow2->published)
{ ?>
<tr>
<td>
<?php echo $colrow2->value;?> :</td>
<td >
<textarea name="<?php echo $colrow2->colname;?>"  id="<?php echo $colrow2->colname;?>"  class="input_border" rows="2" cols="40"><?php echo $row->col3; ?></textarea>
</td>
<td align="center"><input type="checkbox" name="display_col3" value="1" <?php if($row->display_col3==1) echo 'checked="checked"'; ?> /></td>
</tr>
<?php }
if($colrow3->value && $colrow3->published)
{ ?>
<tr>
<td>
<?php echo $colrow3->value;?> :</td>
<td >
<textarea name="<?php echo $colrow3->colname;?>"  id="<?php echo $colrow3->colname;?>"  class="input_border" rows="2" cols="40"><?php echo $row->col4; ?></textarea>
</td>
<td align="center"><input type="checkbox" name="display_col4" value="1" <?php if($row->display_col4==1) echo 'checked="checked"'; ?> /></td>
</tr>
<?php }
if($colrow4->value && $colrow4->published)
{ ?>
<tr>
<td>
<?php echo $colrow4->value;?> :</td>
<td >
<textarea name="<?php echo $colrow4->colname;?>"  id="<?php echo $colrow4->colname;?>"  class="input_border" rows="2" cols="40"><?php echo $row->col5; ?></textarea>
</td>
<td align="center"><input type="checkbox" name="display_col5" value="1" <?php if($row->display_col5==1) echo 'checked="checked"'; ?> /></td>
</tr>
<?php }?>
</table>            
</div>
<?php } ?>
</div>    
<input type="hidden" name="option" value="com_awdjomalbum" />
<input type="hidden" name="task" id="task" value="saveinfo" />
<input type="hidden" name="view" value="awdjomalbum" />
<input type="hidden" name="id" value="<?php echo $row->id;?>" />
<input type="hidden" name="userid" value="<?php echo $user->id; ?>" />
<input type="hidden" name="Itemid" value="<?php echo AwdwallHelperUser::getComItemId();?>" />
<div style="float:left; width:100%; text-align:center; margin:10px 0px;">  
<center><input type="submit" name="submit" value="<?php echo JText::_('Save');?>" class="postButton_small" style="float:none!important;" /></center> 
</div>
</form>   
          
<?php 
}
else
{
if($_REQUEST['wuid'])
{
$wuid=$_REQUEST['wuid'];
}
else
{
$wuid=$user->id;
}
$usermywalllink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$wuid.'&Itemid=' . $Itemid, false);
$usergallerylink=JRoute::_('index.php?option=com_awdjomalbum&view=awdalbumlist&wuid='.$wuid.'&Itemid=' . $Itemid, false);
?>
          <div  class="hightlightboxMain">
            <div  class="hightlightboxInner">
              <div class="hightlightboxleft">
               <form class="awdalbumform">
					<?php
                        if(is_array($basicInfo)){ 
                        foreach($basicInfo as $arr){
                        $cbff='display_'.str_replace(' ','',$arr[1]);
                        //echo $cbff;
                        if(in_array($cbff,$cbfields))
                        {
                        if($arr[0])
                        {
                    ?>
                    <div class="clearfix">
                        <label for="form-name" class="form-label"><?php echo $arr[1];?></label>
                        <div class="form-input"><?php echo $arr[0];?></div>
                    </div>
				<?php 
                        }
                        }
                    } 
                 } 
                ?>
                            <?php if($row->currentcity && $row->display_currentcity==1){?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo JText::_('Current City');?></label>
                                <div class="form-input"><?php  echo $row->currentcity;?></div>
                            </div>
                            <?php } ?>
							 <?php if($row->hometown && $row->display_hometown==1){?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo JText::_('Hometown');?></label>
                                <div class="form-input"><?php  echo $row->hometown;?></div>
                            </div>
                            <?php } ?>
                            <?php if($row->languages && $row->display_languages==1){?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo JText::_('Languages');?></label>
                                <div class="form-input"><?php  echo $row->languages;?></div>
                            </div>
                            <?php } ?>
                            <?php if($row->birthday && $row->display_birthday==1){?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo JText::_('Birthday');?></label>
                                <div class="form-input"><?php if($row->hide_birthyear==1){echo date('jS F ', strtotime($row->birthday));}else{echo date('jS F Y', strtotime($row->birthday));}?></div>
                            </div>
                            <?php } ?>
                            <?php if($row->workingat && $row->display_workingat==1){?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo JText::_('Working At');?></label>
                                <div class="form-input"><?php  echo $row->workingat;?></div>
                            </div>
                            <?php } ?>
                            <?php if($row->studied && $row->display_studied==1){?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo JText::_('Studied');?></label>
                                <div class="form-input"><?php  echo $row->studied;?></div>
                            </div>
                            <?php } ?>
                            <?php if($row->livein && $row->display_livein==1){?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo JText::_('Live in');?></label>
                                <div class="form-input"><?php  echo $row->livein;?></div>
                            </div>
                            <?php } ?>
                            <?php if($row->phone && $row->display_phone==1){?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo JText::_('Phone');?></label>
                                <div class="form-input"><?php  echo $row->phone;?></div>
                            </div>
                            <?php } ?>
                             <?php if($row->cell && $row->display_cell==1){?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo JText::_('Cell');?></label>
                                <div class="form-input"><?php  echo $row->cell;?></div>
                            </div>
                            <?php } ?>
                            <?php if($this->basicInfo['gender']){?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo JText::_('Gender');?></label>
                                <div class="form-input"><?php  echo $this->basicInfo['gender'];?></div>
                            </div>
                            <?php } ?>
                            <?php if($row->maritalstatus && $row->display_maritalstatus==1){?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo JText::_('Marital status');?></label>
                                <div class="form-input">	<?php if($row->maritalstatus=='married'){echo  JText::_('Married');}?>
								<?php if($row->maritalstatus=='single'){echo  JText::_('Single');}?>
                                <?php if($row->maritalstatus=='divorced'){echo  JText::_('Divorced');}?>
                            </div>
                            </div>
                            <?php } ?>
                            <?php if($row->aboutme && $row->display_aboutme==1){?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo JText::_('About Me');?></label>
                                <div class="form-input"><?php  echo nl2br($row->aboutme);?></div>
                            </div>
                            <?php } ?>
                            
                            <?php if($colrow->value && $row->col1 && $row->display_col1==1 && $colrow->published==1){ ?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo $colrow->value;?></label>
                                <div class="form-input"><?php  echo nl2br($row->col1); ?></div>
                            </div>
                            <?php } ?>
                            <?php if($colrow1->value && $row->col2 && $row->display_col2==1 && $colrow1->published==1){ ?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo $colrow1->value;?></label>
                                <div class="form-input"><?php  echo nl2br($row->col2); ?></div>
                            </div>
                            <?php } ?>
                            <?php if($colrow2->value && $row->col3 && $row->display_col3==1 && $colrow2->published==1){ ?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo $colrow2->value;?></label>
                                <div class="form-input"><?php  echo nl2br($row->col3); ?></div>
                            </div>
                            <?php } ?>
                            <?php if($colrow3->value && $row->col4 && $row->display_col3==1 && $colrow3->published==1){ ?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo $colrow3->value;?></label>
                                <div class="form-input"><?php  echo nl2br($row4->col1); ?></div>
                            </div>
                            <?php } ?>
                            <?php if($colrow4->value && $row->col5 && $row->display_col4==1 && $colrow4->published==1){ ?>
                            <div class="clearfix">
                                <label for="form-name" class="form-label"><?php echo $colrow4->value;?></label>
                                <div class="form-input"><?php  echo nl2br($row->col5); ?></div>
                            </div>
                            <?php } ?>
                            
                            
              			</form>
              
              
              </div>
              <div class="hightlightboxright"> 
              
              
            <table  border="0" cellspacing="0" cellpadding="0" align="right">
  <tr>
    <td align="center"><img src="<?php echo AwdwallHelperUser::getBigAvatar133($_REQUEST['wuid']);?>" class="avtartool"  /></td>
  </tr>
  <tr>
    <td align="left"><br /><?php echo JText::sprintf('USERWALL',$usermywalllink, $username);?></td>
  </tr>
  <tr>
    <td align="left"><br /><?php echo JText::sprintf('USERGALLERY',$usergallerylink,$username);?></td>
  </tr>
</table>
				
			  
             
				
			 
              </div>
            </div>
          </div>
          <?php 
}
?>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
jQuery(document).ready(function() {
   jQuery("#awdcontent div").hide(); // Initially hide all content
   jQuery("#awdtabs li:first").attr("id","awdcurrent"); // Activate first tab
   jQuery("#awdcontent div:first").fadeIn(); // Show first tab content
    
   jQuery('#awdtabs a').click(function(e) {
        e.preventDefault();
        if (jQuery(this).closest("li").attr("id") == "awdcurrent"){ //detection for current tab
         return       
        }
        else{             
        jQuery("#awdcontent div").hide(); //Hide all content
        jQuery("#awdtabs li").attr("id",""); //Reset id's
        jQuery(this).parent().attr("id","awdcurrent"); // Activate this
        jQuery('#' + jQuery(this).attr('name')).fadeIn(); // Show content for current tab
        }
    });
});
</script>
