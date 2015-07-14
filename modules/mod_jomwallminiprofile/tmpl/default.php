<?php
/**
 *
 * $Id: default.php 1.0.0 2013-01-15 13:13:58 AWDsolution.com $
 * @package	    JomWALL Mini Profile
 * @subpackage	jomwallminiprofile
 * @version     1.0.0
 * @description This module display a small snap of jomwall profile.
 * @copyright	  Copyright © 2013 - All rights reserved.
 * @license		  GNU General Public License v2.0
 * @author		  AWDsolution.com
 * @author mail	support@awdsolution.com
 * @website		  AWDsolution.com
 *
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
error_reporting(0);
define('REAL_NAME', 0);
define('USERNAME', 1);

$config 		= &JComponentHelper::getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
$display_gallery = $config->get('display_gallery', 1);
$displayPm = $config->get('display_pm', 1);
$display_group = $config->get('display_group', 1);
$display_group_for_moderators = $config->get('display_group_for_moderators', 1);
$moderator_users = $config->get('moderator_users', '');
$moderator_users=explode(',',$moderator_users);

$Itemid = AwdwallHelperUser::getComItemId();
$document	= JFactory::getDocument();
//$document->addStyleSheet(JURI::base().'components/com_awdwall/css/style_'.$template.'.css');
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
$friendJsUrl = 'index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid;
$groupsUrl = JRoute::_('index.php?option=com_awdwall&task=groups&Itemid=' . $Itemid, false);


$mywalllink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' .$user->id .'&Itemid=' . $Itemid, false);
$mainlink=base64_encode(JRoute::_("index.php?option=com_awdwall&task=login&Itemid=".$Itemid,false));

if($template=='default')
{
	$fcolor='111111';
	$bcolor='AAAAAA';
}
else
{
	$fcolor=$color[2];
	$bcolor=$color[2];
}
$jqueryversion = $params->get('jqueryversion', '1.8.2');
$injquery = $params->get('injquery', 1);

$document	= JFactory::getDocument();
if($injquery==1)
{
if($_REQUEST['option']!='com_awdwall')
{
	$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/'.$jqueryversion.'/jquery.min.js' );
}
}
?>

<style>
@import url('<?php echo JURI::base();?>components/com_awdwall/css/colorbox.css');


.modmessage-count {
    background: none repeat scroll 0 0 red;
    border-radius: 7px 7px 7px 7px;
    box-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
    color: #FFFFFF;
    display: block;
    font-size: 10px;
    font-weight: bold;
    height: 14px;
    line-height: 14px;
    padding: 0 4px;
    position: absolute;
    right: 5px;
    text-shadow: none;
    top: -6px;
    z-index: 999;
}
.modmessage-counter {
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
    padding: 2px 3px!important;
    line-height: 9px!important;
    height: 9px;
	margin-top:-15px;
	font-size:9px!important;
}
#modnotifications-button{
display:block;
height:30px;
width:30px;
position:relative;
float:right;
top:-33px;
cursor:pointer;
}

 p.moduleawdprofile{
	text-align:left !important;
	padding:1px;
	border-top: 1px solid #<?php echo $bcolor;?>!important;
	display:block;
	font-size:11px!important;
	margin:0px!important;
	text-decoration:none!important;
}


 p.moduleawdprofile a.mvideo {
	background: url(<?php echo JURI::base();?>components/com_awdwall/images/default/icon-videos.png) no-repeat left !important;
	padding-left:24px;
	display:block;
	margin-left:10px;
	font-size:11px!important;
	margin-top:3px;
	color:#<?php echo $fcolor;?>!important;
	text-decoration:none!important;
}
 p.moduleawdprofile  a.mphoto {
	background: url(<?php echo JURI::base();?>components/com_awdwall/images/default/icon-images.png) no-repeat left !important;
	padding-left:24px;
	display:block;
	margin-left:10px;
	font-size:11px!important;
	margin-top:3px;
	color:#<?php echo $fcolor;?>!important;
	text-decoration:none!important;
}
 p.moduleawdprofile  a.minfo {
	background: url(<?php echo JURI::base();?>components/com_awdwall/images/default/icon-info.png) no-repeat left !important;
	padding-left:24px;
	display:block;
	margin-left:10px;
	font-size:11px!important;
	margin-top:3px;
	color:#<?php echo $fcolor;?>!important;
	text-decoration:none!important;
}
 p.moduleawdprofile  a.mmessage {
	background: url(<?php echo JURI::base();?>components/com_awdwall/images/default/icon-message.png) no-repeat left !important;
	padding-left:24px;
	display:block;
	margin-left:10px;
	font-size:11px!important;
	margin-top:3px;
	color:#<?php echo $fcolor;?>!important;
	text-decoration:none!important;
}
 p.moduleawdprofile  a.friends {
	background: url(<?php echo JURI::base();?>components/com_awdwall/images/default/icon-friends.png) no-repeat left !important;
	padding-left:24px;
	display:block;
	margin-left:10px;
	font-size:11px!important;
	margin-top:3px;
	color:#<?php echo $fcolor;?>!important;
	text-decoration:none!important;
}
 p.moduleawdprofile  a.groups {
	background: url(<?php echo JURI::base();?>components/com_awdwall/images/default/icon-groups.png) no-repeat left !important;
	padding-left:24px;
	display:block;
	margin-left:10px;
	font-size:11px!important;
	margin-top:3px;
	color:#<?php echo $fcolor;?>!important;
	text-decoration:none!important;
}
 p.moduleawdprofile  a.awdlink {
	background: url(<?php echo JURI::base();?>components/com_awdwall/images/default/icon-links.png) no-repeat left !important;
	padding-left:24px;
	display:block;
	margin-left:10px;
	font-size:11px!important;
	margin-top:3px;
	color:#<?php echo $fcolor;?>!important;
	text-decoration:none!important;
}

#awdloginmodulewrapper p.login
{
padding:0px!important;
line-height:normal!important;
 box-shadow: none!important;
margin-top:10px!important;
}

#awdloginmodulewrapper p.login input
{
border-radius: 3px 3px 3px 3px;
    box-shadow: none!important;
    color: #FFFFFF!important;;
    cursor: pointer!important;;
    display: inline-block !important;
    float: none !important;
    font-size: 14px !important;
    height: auto !important;
    margin-bottom: 10px;
    padding: 4px 3px;
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.5);
    text-transform: none !important;
    transition: all 0.2s linear 0s;
    width: 99%;
}
#awdloginmodulewrapper p.login input {
background:  #<?php echo $color[8];?>!important;
border: 1px solid #<?php echo $color[2];?>!important;
	display:inline-block!important;
	float:none!important;
}
#awdloginmodulewrapper p.login input:hover {
 background: #<?php echo $color[2];?>!important;
}

#awdmodmenu {
	position: relative;
	margin-left: 0px;
	margin-top:0px;
}
#awdmodmenu a {
display:block;
height:30px;
width:30px;
position:relative;
float:right;
top:-33px;
cursor:pointer;
}
#awdmodmenu ul {
	list-style-type: none;
	margin:0px!important;
}
#awdmodmenu li {
	float: none;
	position: relative;
	text-align: center;
	margin:0px!important;
	padding:0px!important;
}
#awdmodmenu ul.awdmodsub-menu {
	position: absolute;
	left: -10px;
	z-index: 90;
	display:none;
	margin:0px!important;
	padding:0px!important;
}
#awdmodmenu ul.awdmodsub-menu li {
	text-align: left;
	margin:0px!important;
	padding:0px!important;
}
#awdmodmenu li:hover ul.awdmodsub-menu {
	/*display: block;*/
	margin:0px!important;
	padding:0px!important;
}
#awdmodmenu a {
	text-decoration:none;
}
.awdmodegg {
	position:relative;
	box-shadow: 0 3px 8px rgba(0, 0, 0, 0.25);
	background-color:#fff;
	border-radius: 4px 4px 4px 4px;
	border: 1px solid rgba(100, 100, 100, 0.4);
}
.awdmodegg_Body {
	border-top:1px solid #D1D8E7;
	color:#808080;
}
.awdmodegg_Message {
	font-size:13px !important;
	font-weight:normal;
	overflow:hidden
}
#awdmodmenu h3 {
	font-size:13px!important;
	color:#333;
	margin:0;
	padding:0
}
.awdmodcomment_ui {
	border-bottom:1px solid #e5eaf1;
	clear:left;
	float:none;
	overflow:hidden;
	padding:6px 4px 3px 6px;
	width:190px;
	cursor:pointer;
	background:none repeat scroll 0 0 #FFFFC0
}
.awdmodcomment_ui:hover {
	background-color: #F7F7F7;
}
.awdmodcomment_text {
	padding:0px 0 4px;
	color:#333333;
}
.awdmodcomment_actual_text {
	display:block;
	padding-left:.1em;
	padding-right:.1em;
	font-size:11px!important;
	font-weight:normal!important;
	height:35px;
	white-space: nowrap;
	color:#333333;
}
#mes {
	padding: 0px 3px;
	border-radius: 2px 2px 2px 2px;
	background-color: rgb(240, 61, 37);
	font-size: 9px;
	font-weight: bold;
	color: #fff;
	position: absolute;
	top: -5px;
	left: 23px;
	display:none;
}
.awdmodtoppointer {
	background-image:url(<?php echo JURI::base();?>modules/mod_jomwallminiprofile/images/top.png);
	background-repeat: no-repeat;
	height: 11px;
	position: absolute;
	top: -11px;
	width: 20px;
	right: 55px;
}
#awdmodtwo_comments{
max-height:200px;
min-height:30px;
overflow:scroll;
overflow-x: hidden;
}

</style>
<?php
if($_REQUEST['option']!='com_awdwall')
{
 $document->addScript(JURI::base().'components/com_awdwall/js/jquery.colorbox.js' );

}
?>
<script type="text/javascript">
AWDminiprofile=jQuery.noConflict();
function awdmsignout()
{
	document.awdmlogoutfrm.submit();
}

AWDminiprofile(document).ready(function(){
 checkmoduletotalnotification();
AWDminiprofile(".awdiframe").colorbox({
    iframe:true,
    width:"990px",
    height:"550px",
	scrolling: false,
    onLoad:function() {
        AWDminiprofile('html, body').css('overflow', 'hidden'); // page scrollbars off
    },
    onClosed:function() {
        AWDminiprofile('html, body').css('overflow', ''); // page scrollbars on
    }
});

AWDminiprofile("#awdmodlinknotification").click(function () {
	AWDminiprofile(".awdmodsub-menu").fadeIn("slow");
});

});
AWDminiprofile(document).mouseup(function (e)
{
    var container = AWDminiprofile(".awdmodsub-menu");

    if (container.has(e.target).length === 0)
    {
        container.fadeOut("slow");
    }
});
function checkmoduletotalnotification()
{
	var Itemid=document.getElementById("Itemid").value;
	var d = new Date();
	var time = d.getTime();
	var  animStyle = 'fade';
	var url='<?php echo JURI::base();?>modules/mod_jomwallminiprofile/ajax.php?task=gettotalnotification&timestamp='+time;

			AWDminiprofile.get(url, function(data){
				if(data.length>5 && data!=0){
					if (AWDminiprofile("#awdmodtwo_comments")[0]){
							AWDminiprofile("#awdmodtwo_comments").load(url);
					}
				}
			});
		 window.setTimeout(function() {checkmoduletotalnotification();}, 9000);
}
function navigateurl(id,url,type)
{
	var urll='index.php?option=com_awdwall&task=delnotification&nid='+id;

	AWDminiprofile.post(urll, function(data){
	   if(type!='tag')
	   {
		window.location.href=url;
	   }
	   else
	   {
			AWDminiprofile.fn.colorbox({
				href:url,
				iframe:true,
				width:"990px",
				height:"550px",
				scrolling: false,
				onLoad:function() {
					AWDminiprofile('html, body').css('overflow', 'hidden'); // page scrollbars off
				},
				onClosed:function() {
					AWDminiprofile('html, body').css('overflow', ''); // page scrollbars on
				}
			});
	   }
	});

}

</script>
<div class="moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
<a href="<?php echo $mywalllink;?>" >
<div style="width:100%; height:150px; overflow:hidden; background-image:url('<?php echo AwdwallHelperUser::getBigAvatar133($user->id);?>'); background-position:center; background-repeat:no-repeat; border:0px solid #FF0000;"></div>
</a>
<div style="height:40px; background: rgba(0, 0, 0, 0.5); margin-top:-30px; color:#ffffff; font-weight:bold; font-size:16px; padding:10px; width:inherit;"><span style=" width:80%; height:18px;line-height:18px; overflow:hidden; display:block; margin:0px; padding:0px;"><?php echo AwdwallHelperUser::getDisplayName($user->id);?></span><span style=" width:80%; font-size:9px;  display:block; margin:0px; padding:0px; line-height:11px;"><?php echo $userstatus;?></span>

<div id="awdmodmenu">
  <ul>
    <li> <a href="javascript:void(0)" id="awdmodlinknotification"> <img src="<?php echo JURI::base().'components/com_awdwall/';?>images/default/8.png"  /> <span id="mes"></span> </a>
      <ul class="awdmodsub-menu">
        <li class="awdmodegg">
          <div class="awdmodtoppointer"><!--<img src="<?php echo JURI::base();?>modules/mod_jomwallminiprofile/images/top.png" />--></div>
          <div id="awdmodtwo_comments">
          <div class="awdmodcomment_ui">
            <div class="awdmodcomment_text">
              <div  class="awdmodcomment_actual_text"><center><?php echo JText::_("No new Notice");?></center></div>
            </div>
          </div>
          </div>
        </li>
      </ul>
    </li>
  </ul>
</div>

</div>
		<?php if($showalbumlink){?>
        	<?php if($show_profile_link){?>
		   <p class="moduleawdprofile"><a href="<?php echo JRoute::_($infolink);?>" class="minfo"><?php echo JText::_('MOD_JOMWALLMINIPROFILE_INFO');?></a></p>
		   <?php } ?>
        	<?php if($show_photo_link){?>
		<?php  if($display_gallery==1){?>
		   <p class="moduleawdprofile"><a href="<?php echo JRoute::_($albumlink);?>" class="mphoto"><?php echo JText::_('MOD_JOMWALLMINIPROFILE_PHOTOS');?> [<?php echo $totalphotos;?>]</a></p>
		   <?php } ?>
	   <?php } ?>
	   <?php } ?>
        	<?php if($show_wallpost_link){?>
		<p class="moduleawdprofile"><a href="<?php echo $mywalllink;?>" title="<?php echo JText::_('MOD_JOMWALLMINIPROFILE_TOTAL_WALLPOST');?>" class="friends"><?php echo JText::_('MOD_JOMWALLMINIPROFILE_TOTAL_WALLPOST');?> [<?php echo $countwallpost;?>]</a>
		</p>
	   <?php } ?>
        	<?php if($show_friend_link){?>
		<p class="moduleawdprofile"><a href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('MOD_JOMWALLMINIPROFILE_FRIENDS');?>" class="friends"><?php echo JText::_('MOD_JOMWALLMINIPROFILE_FRIENDS');?> [<?php echo $countFriends;?>]</a>
		<?php if( $getPendingFriends) {?>
			<span class="modmessage-counter"><?php echo $getPendingFriends;?></span>
		<?php }?>
		</p>
	   <?php } ?>
        	<?php if($show_message_link){?>
		<?php if((int)$displayPm){?>
		<p class="moduleawdprofile"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&task=pm&Itemid=' . $Itemid);?>" title="<?php echo JText::_('MOD_JOMWALLMINIPROFILE_MESSAGES');?>" class="mmessage"><?php echo JText::_('MOD_JOMWALLMINIPROFILE_MESSAGES');?></a>
		<?php if($totalpm){?>
			<span class="modmessage-counter"><?php echo $totalpm;?></span>
		<?php }?>
		</p>
	<?php }?>
	   <?php } ?>
        	<?php if($show_group_link){?>
	<?php if($display_group || ($display_group_for_moderators && in_array($user->id,$moderator_users)) ) {?>
		<p class="moduleawdprofile"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=groups&Itemid='.$Itemid);?>" title="<?php echo JText::_('MOD_JOMWALLMINIPROFILE_GROUPS');?>" class="groups"><?php echo JText::_('MOD_JOMWALLMINIPROFILE_GROUPS');?> [<?php echo $getMyGrps;?>]</a>
		<?php if($pendinggroup){?>
			<span class="modmessage-counter"><?php echo $pendinggroup;?></span>
		<?php  }?>
		</p>
		<?php }?>
		<?php }?>
		<?php
        if(!empty($profilelinks))
        {
            foreach ($profilelinks as $profilelink)
            {
                $item = JFactory::getApplication()->getMenu()->getItem( $profilelink );
				if($item->type!='url')
				{
					$url = JRoute::_($item->link . '&Itemid=' . $item->id);
				}
				else
				{
					$url =$item->link;
				}
            ?>
            <p class="moduleawdprofile"><a href="<?php echo $url;?>" title="<?php echo $item->title;?>" class="awdlink"><?php echo $item->title;?></a></p>
            <?php
            }
        }

        ?>
<div style="clear:both;" id="awdloginmodulewrapper">
<p class="login ">
<input type="button"  onclick="awdmsignout();" class="button" value="<?php echo JText::_("MOD_JOMWALLMINIPROFILE_SIGNOUT");?>">
</p>
</div>
<input type="hidden" id="Itemid" value="<?php echo  $Itemid; ?>"/>
<form method="post" action="index.php?option=com_users&task=user.logout" name="awdmlogoutfrm" >
<input type="hidden" value="<?php echo $mainlink;?>" name="return" />
<input type="hidden" value="1" name="<?php echo JSession::getFormToken();?>" />
</form>

<div style="clear:both;"></div>
</div>
