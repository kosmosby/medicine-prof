 <?php 
defined('_JEXEC') or die('Restricted access');
$Itemid=AwdwallHelperUser::getComItemId();
$userids=$this->userids;
$privacy=$this->privacy;
$useridstr=$this->useridstr;
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$listingperpage 		= $config->get('listingperpage', 5);
$imagelimit 		= $config->get('imagelimit',4);

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
$template 		= $config->get('temp', 'blue');
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
if($privacy==0)
{
$awdpstr=JText::_('All');
}
if($privacy==1)
{
$awdpstr=JText::_('Friends Only');
}
if($privacy==2)
{
$awdpstr=JText::_('Friends Of Friends');
}

 ?>
<link rel="stylesheet" href="<?php JURI::base();?>components/com_awdjomalbum/css/style.css" type="text/css" />
<link rel="stylesheet" href="<?php JURI::base();?>components/com_awdjomalbum/css/style2.css" type="text/css" />
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
		<style type='text/css'>
			div.awdselectBox
			{
				position:relative;
				display:inline-block;
				cursor:default;
				text-align:left;
				line-height:30px;
				clear:both;
				color:#fff;
			}
			span.awdselected
			{
				width:167px;
				text-indent:20px;
				<?php if($template!='default'){?>
				border:1px solid #<?php echo $color[8];?>;
				<?php } else {?>
				border:1px solid #555555;
				<?php }?>
				border-right:none;
				border-top-left-radius:5px;
				border-bottom-left-radius:5px;
				<?php if($template!='default'){?>
				background:#<?php echo $color[8];?>;
				<?php } else {?>
				background:#555555;
				<?php }?>
				overflow:hidden;
				cursor:pointer;
			}
			span.awdselectArrow
			{
				width:30px;
				<?php if($template!='default'){?>
				border:1px solid #<?php echo $color[8];?>;
				<?php } else {?>
				border:1px solid #555555;
				<?php }?>
				border-top-right-radius:5px;
				border-bottom-right-radius:5px;
				text-align:center;
				font-size:20px;
				-webkit-user-select: none;
				-khtml-user-select: none;
				-moz-user-select: none;
				-o-user-select: none;
				user-select: none;
				<?php if($template!='default'){?>
				background:#<?php echo $color[8];?>;
				<?php } else {?>
				background:#555555;
				<?php }?>
				cursor:pointer;
			}
			
			span.awdselectArrow,span.awdselected
			{
				position:relative;
				float:left;
				height:30px;
				z-index:1;
			}
			
			div.awdselectOptions
			{
				position:absolute;
				top:28px;
				left:0;
				width:198px;
				<?php if($template!='default'){?>
				border:1px solid #<?php echo $color[8];?>;
				<?php } else {?>
				border:1px solid #555555;
				<?php }?>
				border-bottom-right-radius:5px;
				border-bottom-left-radius:5px;
				overflow:hidden;
				<?php if($template!='default'){?>
				background:#<?php echo $color[8];?>;
				<?php } else {?>
				background:#555555;
				<?php }?>
				padding-top:2px;
				display:none;
				cursor:pointer;
			}
				
			span.awdselectOption
			{
				display:block;
				width:80%;
				line-height:20px;
				padding:5px 10%;
			}
			
			span.awdselectOption:hover
			{
				color:#fff;
				background: #<?php echo $color[2];?>!important;
			}	
			div.awdgallerymore_box{
				display:block;
				
				height:30px!important;
				
				<?php if($template!='default'){?>
				border:1px solid #<?php echo $color[8];?>;
				<?php } else {?>
				border:1px solid #555555;
				<?php }?>
				border-radius:5px;
				overflow:hidden;
				<?php if($template!='default'){?>
				background:#<?php echo $color[8];?>;
				<?php } else {?>
				background:#555555;
				<?php }?>
				
				cursor:pointer;
				color:#FFFFFF;
				text-align:center;
			}
			div.awdgallerymore_box a{
			color:#FFFFFF!important;
			display:inline-block!important;
			padding:8px 5px 5px 5px!important;
			}		
		</style>
	<script type='text/javascript'><!--
			jQuery(document).ready(function() {
				 enableSelectBoxes();
//				jQuery('span.awdselected,span.awdselectArrow').click(function(){
//				 	enableSelectBoxes();
//				});
			});
			jQuery(document).mouseup(function (e)
			{
				var container = jQuery("div.awdselectOptions");
			
				if (container.has(e.target).length === 0)
				{
					container.css('display','none');
				}
			});
			function enableSelectBoxes(){
				jQuery('div.awdselectBox').each(function(){
					jQuery(this).children('span.awdselected').html(jQuery(this).children('div.awdselectOptions').children('span.awdselectOption:first').html());
					jQuery(this).attr('value',jQuery(this).children('div.awdselectOptions').children('span.awdselectOption:first').attr('value'));
					
					jQuery(this).children('span.awdselected,span.awdselectArrow').click(function(){
						if(jQuery(this).parent().children('div.awdselectOptions').css('display') == 'none'){
							jQuery(this).parent().children('div.awdselectOptions').css('display','block');
						}
						else
						{
							jQuery(this).parent().children('div.awdselectOptions').css('display','none');
						}
					});
					
					jQuery(this).find('span.awdselectOption').click(function(){
						jQuery(this).parent().css('display','none');
						jQuery(this).closest('div.awdselectBox').attr('value',jQuery(this).attr('value'));
						jQuery(this).parent().siblings('span.awdselected').html(jQuery(this).html());
						jQuery("#privacy").attr('value',jQuery(this).attr('value'));
						jQuery("#galleryForm").submit();
					});
				});	
				<?php if($_REQUEST['privacy']){?>	
				jQuery('span.awdselected').html('<?php echo $awdpstr;?>');
				<?php } ?>		
			}//-->
			
function getOlderPosts(url)
{
	var page = document.getElementById("awd_page").value;
	var privacy = document.getElementById("privacy").value;
	document.getElementById("older_posts_loader").style.display = 'inline-block';
	jQuery.get(url + '&awd_page=' + page + '&privacy='+ privacy + '&view=olderimageblock', {}, 
	function(data){
		document.getElementById("older_posts_loader").style.display = 'none';
		page = parseInt(page)+1;
		document.getElementById("awd_page").value = page;		
		jQuery("#mainalbumpage").append(data);
	}, "html");
}
			
		</script>
<script type="text/javascript">
  jQuery(document).ready(function(){
jQuery(".awdiframenew").colorbox({
    iframe:true, 
    width:"990px", 
    height:"550px", 
	scrolling: false,
    onLoad:function() {
        jQuery('html, body').css('overflow', 'hidden'); // page scrollbars off
    }, 
    onClosed:function() {
        jQuery('html, body').css('overflow', ''); // page scrollbars on
    }
});
  });
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
 <div id="msg_content" >
 <!-- start block msg -->
 <table width="94%" border="0" cellspacing="2" cellpadding="2" style="margin-top:15px; ">
  <tr>
    <td align="left" style="padding-left:20px!important;"><span class="username"><?php echo JText::_('MEMBER GALLERY');?></span></td>
    <td align="right" style="padding-right:15px!important;">
		<div class='awdselectBox'>
			<span class='awdselected'></span>
			<span class='awdselectArrow'>&#9660</span>
			<div class="awdselectOptions" >
				<span class="awdselectOption" value="0"><?php echo JText::_('All');?></span>
				<span class="awdselectOption" value="1"><?php echo JText::_('Friends Only');?></span>
				<span class="awdselectOption" value="2"><?php echo JText::_('Friends Of Friends');?></span>
			</div>
		</div>
 <form  id="galleryForm" name="galleryForm" method="post" >
  <input type="hidden" name="privacy" id="privacy" value="<?php echo $privacy;?>"  />  
 	<input type="hidden" name="option" value="com_awdjomalbum" />
	<input type="hidden" name="view" value="gallery" />
</form>
	</td>
  </tr>
</table>
<div style="clear:both; height:10px;"></div> 
<div id="mainalbumpage" style="padding:0px 15px;">
<?php


$count=count($userids);
foreach($userids as $userid)
{
$albumlink=JRoute::_("index.php?option=com_awdjomalbum&view=awdalbumlist&wuid=".$userid."&Itemid=".AwdwallHelperUser::getComItemId(), false);

if($privacy==2)
{
$query= "SELECT upload_date FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=2 and #__awd_jomalbum.userid=".$userid." order by #__awd_jomalbum_photos.id desc";
}

if($privacy==1)
{
$query= "SELECT upload_date FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=1 and #__awd_jomalbum.userid=".$userid." order by #__awd_jomalbum_photos.id desc";
}

if($privacy==0)
{
$query= "SELECT upload_date FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=".$privacy." and  #__awd_jomalbum.userid=".$userid." order by #__awd_jomalbum_photos.id desc limit 1";
}
$db->setQuery($query);
$upload_date=$db->loadResult();
$upload_date= AwdwallHelperUser::getDisplayTime(strtotime($upload_date));
?>

<div style="width:230px; margin:5px 5px; padding-left:5px;"><div style="float:left;width:50px; height:50px; overflow:hidden; box-shadow: 0px 0px 3px #111;"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$userid.'&Itemid='.AwdwallHelperUser::getComItemId());?>" target="_top"><img src="<?php echo AwdwallHelperUser::getBigAvatar51($userid);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($userid);?>" title="<?php echo AwdwallHelperUser::getDisplayName($userid);?>" border="0" width="50" /></a></div><div style="float:left;width:150px; padding-left:8px; padding-top:0px;"><div style="float:left; width:100%;"><span class="profileName"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$userid.'&Itemid='.AwdwallHelperUser::getComItemId());?>" target="_top"><?php echo AwdwallHelperUser::getDisplayName($userid);?></a></span></div><div style="float:left; width:100%;padding-top:5px;"><span class="wall_date" style="font-size:11px;"><?php echo $upload_date;?></span></div>
</div></div>
<div style="clear:both; height:10px;"></div> 
<?php
if($privacy==1)
{
$query= "SELECT *,#__awd_jomalbum_photos.id as pid FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=1 and  #__awd_jomalbum.userid=".$userid." order by #__awd_jomalbum_photos.id desc limit ".$imagelimit;
}
if($privacy==2)
{
$query= "SELECT *,#__awd_jomalbum_photos.id as pid FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=2 and  #__awd_jomalbum.userid=".$userid." order by #__awd_jomalbum_photos.id desc limit ".$imagelimit;
}
if($privacy==0)
{
$query= "SELECT *,#__awd_jomalbum_photos.id as pid FROM #__awd_jomalbum inner join #__awd_jomalbum_photos on  #__awd_jomalbum.id=#__awd_jomalbum_photos.albumid WHERE  privacy=".$privacy." and  #__awd_jomalbum.userid=".$userid." order by #__awd_jomalbum_photos.id desc limit ".$imagelimit;
}
$db->setQuery($query);
$puser_rows=$db->loadObjectList();
foreach($puser_rows as $puser_row)
{
$link 	=JRoute::_('index.php?option=com_awdjomalbum&view=awdimagelist&tmpl=component&pid='.$puser_row->pid.'&albumid='.$puser_row->albumid.'&Itemid='.AwdwallHelperUser::getComItemId());
$cquery= "SELECT count(*) FROM #__awd_jomalbum_comment where photoid=".$puser_row->pid;
$db->setQuery($cquery);
$commentcount=$db->loadResult();

?>
<div style="width:102px;margin:8px;   height:86px; float:left;">
<div  style="width:102px; height:76px; background-position:center; border:1px solid #CCCCCC; padding:4px; float:left;">
<a href="<?php echo $link;?>" class='awdiframe'><div style="background-image:url(<?php echo JURI::base();?>images/awd_photo/awd_thumb_photo/<?php echo $puser_row->image_name; ?>); background-repeat:no-repeat; width:102px; height:76px; background-position:center; float:left;"></div></a>
</div>
<div style="clear:both; height:5px;"></div>
<span style="width:100%; font-size:11px;"><?php if($commentcount){echo $commentcount.' '.JText::_('COMMENTS');}?></span>
</div>
<?php
}
?>
<div style="clear:both; height:5px;"></div> 
<span  class="add_as_friend" style="float:right; margin-right:10px; margin-top:15px;">
<a  href="<?php echo $albumlink;?>"><?php echo JText::_('READMORE');?></a>
</span>
<div style="clear:both; height:1px;"></div> 
<hr />
<?php
}
?>
</div>
<div style="clear:both; height:10px;"></div>
<?php
if(((($this->page + 1) * $listingperpage) < $this->nooffeeds) && (int)$user->id){
?>
 <div class="awdgallerymore_box"><a href="javascript:void(0);" onclick="getOlderPosts('<?php echo JURI::base().'index.php?option=com_awdjomalbum&view=awdwall&view=olderimageblock&tmpl=component';?>');"><?php echo JText::_('View All');?></a>&nbsp;&nbsp;<span id="older_posts_loader" style="display:none;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
<input id="awd_page" name="awd_page" type="hidden" value="<?php echo ($this->page + 1);?>" autocomplete="off"/>
<input id="view" name="view" type="hidden" value="olderimageblock" autocomplete="off" />

  </div>
  <?php } ?>
  
 </div>
 </div>
     </div>
 </div>
</div>