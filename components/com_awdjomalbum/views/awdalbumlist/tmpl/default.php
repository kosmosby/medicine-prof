<?php defined('_JEXEC') or die('Restricted access'); 
$username=$this->username;
$userid=$this->userid;
$user =& JFactory::getUser();
$photorows=$this->photorows;
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
			<?php echo AwdwallHelperUser::getDisplayName($user->id);?></div>
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
 <!-- start block msg -->
 <table width="100%" border="0" cellspacing="2" cellpadding="2" style="margin-top:15px;">
  <tr>
    <td align="left"><span class="username"><?php echo $username; ?> &gt;&nbsp;<?php echo JText::_('Pictures');?></span></td>
    <td align="right"><?php if(!empty($user->id)) {
	$link3=JRoute::_('index.php?option=com_awdjomalbum&view=createalbum&Itemid='.AwdwallHelperUser::getComItemId(),false);
	?>	
	<span id="uploadphotos" class="add_as_friend"><a href="<?php echo $link3; ?>"  title="<?php echo JText::_('+ Upload Photos');?>"><?php echo JText::_('+ Upload Photos');?></a></span>		 
<?php }?>
</td>
  </tr>
</table>
 
 
 
 
 
	<div style="clear:both; height:10px;"></div> 
<form action="" method="post" name="adminForm">
<?php if(count($this->rows)>0) {?>
<div id="imgList">	
	<?php
	
	$db	=& JFactory::getDBO(); 
		
	for ($i=0, $n=count( $this->rows ); $i < $n; $i++)
	{
		
		$db		=& JFactory :: getDBO();
		
		$row 	=$this->rows[$i];
		
		$albumview=isalbumviewable($row->id);	
		
		if($albumview)
		{
		$sql='Select * from #__awd_jomalbum_photos where albumid='.$row->id .' and userid='.$row->userid;
		//echo $sql;
		$db->setQuery($sql);
		$photos=$db->loadObjectList();
		$photo=$photos[0];
		//print_r($photo);
		$totalphotos=count($photos);			
		//$link 	=JRoute::_('index.php?option=com_awdjomalbum&view=awdimagelist&pid='.$photo->id.'&albumid='. $row->id.'&Itemid='.AwdwallHelperUser::getComItemId());
		$link 	=JRoute::_('index.php?option=com_awdjomalbum&view=awdalbumimages&albumid='. $row->id.'&Itemid='.AwdwallHelperUser::getComItemId());
		$link2 	=JRoute::_('index.php?option=com_awdjomalbum&view=awd_addphoto&id='. $row->id.'&Itemid='.AwdwallHelperUser::getComItemId());
			if($row->userid==$user->id) {
	  	 	?>
			<div class="albumBox">
				<div class="albumImg">
				<?php if($totalphotos>0) {?>
				<a href="<?php echo $link; ?>">
				<img src="<?php echo JURI::base();?>images/awd_photo/awd_thumb_photo/<?php echo $photo->image_name; ?>" /> </a>
				<?php } else { ?>
				<img src="<?php JURI::base();?>components/com_awdjomalbum/images/no_image.png"/> 
				
				<?php }?>
		</div>
				<div class="albumName"><?php echo $row->name; ?></div>
				
				<div class="photoCount"><?php echo $totalphotos; if($totalphotos>1) { echo JText::_('photos'); } else { echo JText::_(' photo'); }?></div> 
				<?php if($row->userid==$user->id) {?>
				<div class="editalbumlink"><a href="<?php echo $link2; ?>"><?php echo JText::_('Edit Album');?></a></div> <?php }?>
			</div>
		 
		<?php
	  	}
		elseif($totalphotos!=0)
		{
		?>
			<div class="albumBox">
				<div class="albumImg"><a href="<?php echo $link; ?>">
					<img src="<?php echo JURI::base();?>images/awd_photo/awd_thumb_photo/<?php echo $photo->image_name; ?>" />
				</a>
		</div>
				<div class="albumName"><?php echo $row->name; ?></div>
				
				<div class="photoCount"><?php echo $totalphotos; ?> <?php if($totalphotos>1) { echo JText::_('photos');  } else { echo JText::_('photo'); }?></div> 
				<?php if($row->userid==$user->id) {?>
				<div class="editalbumlink"><a href="<?php echo $link2; ?>"><?php echo JText::_('Edit Album');?></a></div> <?php }?>
			</div>
		 
		<?php
		}
	}
		
	}
	?> 
</div>
<?php } else { ?> 
<?php echo JText::_('There are no albumn created by').'&nbsp;'. $username; ?>
<div style="clear:both; height:10px;"></div> 
<?php } 
if(count($photorows)>0)
{
?>
<div style="clear:both;">
<div  class="wallPhotos"><?php echo JText::_('Wall Photos'); ?></div>
	
<?php
	foreach($photorows as $photorow)
	{
		$imgpath=JURI::base()."images/".$userid."/thumb/".$photorow->path;
		$link 	=JRoute::_("index.php?option=com_awdjomalbum&view=awdwallimagelist&tmpl=component&wuid=".$userid."&pid=".$photorow->id."&Itemid=".AwdwallHelperUser::getComItemId());
 	?>
		<div class="wallImages"><a href="<?php echo $link;?>" class='awdiframe'>
		<img src="<?php echo $imgpath; ?>" width="112" height="84" border="0" align="absmiddle" />
		</a></div>
<?php }
?>
</div>
<?php
}
?>
<div style="clear:all;"></div>
	<input type="hidden" name="option" value="com_awdjomalbum" /> 
	<input type="hidden" name="view" value="awdalbumlist" />	
</form>
  </div>
 </div>
 </div>
     </div>
 </div>
       <!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>-->
        <script>
                
                // these are (ruh-roh) globals. You could wrap in an
                // immediately-Invoked Function Expression (IIFE) if you wanted to...
                var currentTallest = 0,
                    currentRowStart = 0,
                    rowDivs = new Array();
                
                function setConformingHeight(el, newHeight) {
                        // set the height to something new, but remember the original height in case things change
                        el.data("originalHeight", (el.data("originalHeight") == undefined) ? (el.height()) : (el.data("originalHeight")));
                        el.height(newHeight);
                }
                
                function getOriginalHeight(el) {
                        // if the height has changed, send the originalHeight
                        return (el.data("originalHeight") == undefined) ? (el.height()) : (el.data("originalHeight"));
                }
                
                function columnConform() {
                
                        // find the tallest DIV in the row, and set the heights of all of the DIVs to match it.
                        jQuery('.albumBox').each(function() {
                        
                                // "caching"
                                var $el = jQuery(this);
                                
                                var topPosition = $el.position().top;
                
                                if (currentRowStart != topPosition) {
                
                                        // we just came to a new row.  Set all the heights on the completed row
                                        for(currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) setConformingHeight(rowDivs[currentDiv], currentTallest);
                
                                        // set the variables for the new row
                                        rowDivs.length = 0; // empty the array
                                        currentRowStart = topPosition;
                                        currentTallest = getOriginalHeight($el);
                                        rowDivs.push($el);
                
                                } else {
                
                                        // another div on the current row.  Add it to the list and check if it's taller
                                        rowDivs.push($el);
                                        currentTallest = (currentTallest < getOriginalHeight($el)) ? (getOriginalHeight($el)) : (currentTallest);
                
                                }
                                // do the last row
                                for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) setConformingHeight(rowDivs[currentDiv], currentTallest);
                
                        });
                
                }
                
                
                jQuery(window).resize(function() {
                        columnConform();
                });
                
                // Dom Ready
                // You might also want to wait until window.onload if images are the things that
                // are unequalizing the blocks
                jQuery(function() {
                        columnConform();
                });
                
        </script>
