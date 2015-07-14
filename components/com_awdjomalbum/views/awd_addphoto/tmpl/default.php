<?php defined('_JEXEC') or die('Restricted access'); 
require_once(JPATH_SITE . DS . 'components'.DS.'com_awdjomalbum'. DS.'js' . DS . 'include.php');
//JHTML::_('behavior.mootools'); 
// jimport('joomla.html.pane');
//$myTabs = &JPane::getInstance('tabs', array('startOffset'=>0));
// $mysliders = &JPane::getInstance('sliders', array('allowAllClose' => true));
$photoRows=$this->photoRows;
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
<link rel="stylesheet" href="<?php JURI::base();?>components/com_awdjomalbum/css/tab2.css" type="text/css" />
<link rel="stylesheet" href="<?php JURI::base();?>components/com_awdjomalbum/css/style2.css" type="text/css" />
<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery("#uploadphoto").bind("click", function () {
					jQuery("#msg_content").mask("Processing...");
				});
				
			});
 
function deletealbum()
{
	if (confirm("Do You Want To Delete?"))
		{         
			
			document.getElementById('task').value="deletealbum";
			document.forms["adminForm"].submit();
			
		}
      
}
    
</script>
<div  id="awd-mainarea" style="width:100%;">
  <style type="text/css">
#awd-mainarea a:link,#awd-mainarea a:visited,#awd-mainarea a:hover{
color:#<?php echo $color[2]; ?>;
text-decoration:none;
}
#awd-mainarea .pane-sliders .title {
color: #<?php echo $color[2]; ?>;
}
#awd-mainarea .pane-sliders .panel { border: 1px solid #<?php echo $color[14]; ?>;}
#awd-mainarea .pane-sliders .panel h3 { background: #f6f6f6; color: #666}
#awd-mainarea .pane-sliders .content { background: #f6f6f6; }
#awd-mainarea .jpane-toggler-down { border-bottom: 1px solid #<?php echo $color[12]; ?>; }
/* tabs */
#awd-mainarea dl.tabs dt {
border-left: 1px solid #<?php echo $color[14]; ?>;
border-right: 1px solid #<?php echo $color[14]; ?>;
border-top: 1px solid #<?php echo $color[14]; ?>;
background: #f0f0f0;
color: #666; 
}
#awd-mainarea dl.tabs dt.open {
background: #<?php echo $color[12]; ?>;
border-bottom: 1px solid #<?php echo $color[14]; ?>;
color: #000;
font-weight:bold;
}
#awd-mainarea div.current {
border: 1px solid #<?php echo $color[14]; ?>;
}
#awd-mainarea div.current dd {
background: #<?php echo $color[12]; ?>;
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
		#awd-mainarea .commentBox2{
	background-color:#<?php echo $color[12]; ?>;
	}
#awd-mainarea .postButton_small{
float:none;
}
#awd-mainarea textarea
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
        <li><a href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('Friends');?>"><?php echo JText::_('Friends');?><font color="red" size="1">
          <?php if((int)$pendingFriends > 1) echo '(' . $pendingFriends . JText::_('Requests') . ')';elseif((int)$pendingFriends == 1) echo '(' . $pendingFriends . JText::_('') . ')';?>
          </font></a></li>
        <?php if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) {?>
        <li class="separator"> </li>
        <li> <a href="<?php echo $groupsUrl;?>" title="<?php echo JText::_('Groups');?>"><?php echo JText::_('Groups');?><font color="red" size="1">
          <?php if((int)pendingGroups > 1) echo '(' . $pendingGroups . JText::_('') . ')';elseif((int)$pendingGroups == 1) echo '(' . $pendingGroups . JText::_('') . ')';?>
          </font></a> </li>
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
  <div class="awdfullbox" style="width:100%"> <span class="bl"></span>
    <div class="rbroundboxrighttop" style="width:99.8%;min-height:150px; padding:0px;"> <span class="bl2"></span><span class="br2"></span>
      <div class="fullboxnew" >
        <!-- start msg content -->
        <div id="msg_content">
          <table width="100%" border="0" cellspacing="2" cellpadding="2" style="margin-top:15px;">
            <tr>
              <td align="left"><span class="username"><?php if((int)$displayName == 1) {echo  $user->username; }else{ echo $user->name; }?> &gt; <?php echo JText::_('Pictures');?></span></td>
              <td align="right"><span  class="add_as_friend"><a href="<?php echo $link3; ?>"  title="<?php echo JText::_('+ Back to albums');?>"><?php echo JText::_('+ Back to albums');?></a></span> </td>
            </tr>
          </table>
          <div style="clear:both; margin-bottom:20px;"></div>
          <!-- Deafult message -->
          <?php 	$link 	=JRoute::_('index.php?option=com_awdjomalbum&Itemid='.AwdwallHelperUser::getComItemId());?>
<ul id="awdtabs">
  <li><a href="#" name="tab1"><?php echo JText::_('Add Photo');?></span><?php echo JText::_('s');?></a></li>
  <li><a href="#" name="tab2"><?php echo JText::_('Edit Info');?></a></li>
  <li><a href="#" name="tab3"><?php echo JText::_('Delete');?></a></li>
</ul>
<div id="awdcontent">
  <div id="tab1">
  <table style="border:1px solid #<?php echo $color[14]; ?>;border-collapse:separate!important;" width="100%" >
      <tr>
        <td id="awdloadingimages" style="display:none"><table width='100%'><tr><td height='300' style='background-image:url(<?php echo JURI::base();?>components/com_awdjomalbum/images/loadergallery.gif); background-repeat:no-repeat; background-position:center;'></td></tr></table></td>
        <td id="awdloadingcontent" >
<form action="" method="post" name="adminForm" enctype="multipart/form-data"  >
                
                  <table  style="border:1px solid #<?php echo $color[14]; ?>;border-collapse:separate!important;" width="100%"  >
                    <tr>
                      <td><table align="center" cellpadding="4" cellspacing="4" width="100%" style="border-collapse:separate!important;">
                          <tr>
                            <td rowspan="6" valign="top"><?php echo JText::_('Photos: <br> You can upload <br>JPG, GIF, PNG Files.'); ?></td>
                            <td nowrap="nowrap"><input type="file" name="photo[]" />
                            </td>
                          </tr>
                          <tr>
                            <td nowrap="nowrap"><input type="file" name="photo[]" />
                            </td>
                          </tr>
                          <tr>
                            <td nowrap="nowrap"><input type="file" name="photo[]" />
                            </td>
                          </tr>
                          <tr>
                            <td nowrap="nowrap"><input type="file" name="photo[]" />
                            </td>
                          </tr>
                          <tr>
                            <td nowrap="nowrap"><input type="file" name="photo[]" />
                            </td>
                          </tr>
                          <tr>
                            <td  ><input type="submit" name="uploadphoto" id="uploadphoto" value="<?php echo JText::_( 'Upload');?>"  class="postButton_small" style="float:left !important;"  <?php /*?>onclick="return showloading();"<?php */?>  /><a href="<?php echo $link3; ?>"><input type="button"   value="<?php echo JText::_( 'Cancel');?>"  class="postButton_small" style="float:left !important;" /></a>
                            </td>
                          </tr>
                        </table>
                        <p align="center"><?php echo JText::_( 'The file size limit is 5 MB. If your upload, does not work, try upload a smaller picture.');?></p></td>
                    </tr>
                  </table>
                  <hr style="background-color:#fff;border:#000 1px dotted;border-style: none none dotted;color:#ccc; margin:5px 0px;"/>
                  <table style="clear:both;" width="100%" >
                    <?php 
		if(count($photoRows)>0){
		?>
                    <tr>
                      <td id="listofPhotos"><?php 
			$i=0;
			foreach($photoRows as $photoRow) {
			 $i++;
			$imgpath=JURI::base()."images/awd_photo/awd_thumb_photo/".$photoRow->image_name;
 			?>
                        <span id="photoRowid<?php echo $i;?>"  style="float:left;padding:3px; margin:5px; border:1px dotted #999999;"><img src="<?php echo $imgpath; ?>" width="100" border="0" align="absmiddle" /><br />
                          <a href="JavaScript:void(0);" onClick="deleteAlbumImages('<?php echo $photoRow->id;?>','photoRowid<?php echo $i;?>','<?php echo $_REQUEST['id'];?>');"><img src="<?php echo JURI::base()?>components/com_awdjomalbum/images/cancel_f2.png" align="right" /></a></span>
                        <?php } ?>
                      </td>
                    </tr>
                    <?php } ?>
                  </table>
                  <input type="hidden" name="option" value="com_awdjomalbum" />
                  <input type="hidden" name="task" id="task" value="savephoto" />
                  <input type="hidden" name="view" value="awd_addphoto" />
                  <input type="hidden" name="id" value="<?php echo $_REQUEST['id']?>" />
                  <input type="hidden" name="firsttime" value="<?php echo $_REQUEST['firsttime']?>" />
                </form>        </td>
      </tr>
   </table>
                
            
  </div>
  <div id="tab2">
                <script type="text/javascript">
		function validateForm()
		{ 
			var form = document.editAlbumForm;
		
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
<form action="" method="post" name="editAlbumForm" onSubmit="return validateForm();">
                  <table style="border:1px solid #<?php echo $color[14]; ?>" width="100%">
                    <tr>
                      <td>
                          <table align="center" cellpadding="4" cellspacing="4" width="100%" style="border-collapse:separate!important;">
                            <tr>
                              <td width="115"><?php echo JText::_( 'Album Name:');?></td>
                              <td width="159"><input type="text" name="name" id="name"   class="input_border" value="<?php echo $this->row->name; ?>" /></td>
                            </tr>
                            <tr>
                              <td><?php echo JText::_( 'location :');?></td>
                              <td><input type="text" name="location" id="location" value="<?php echo $this->row->location; ?>"   class="input_border" />
                              </td>
                            </tr>
                            <tr>
                              <td><?php echo JText::_( 'Description :');?></td>
                              <td><textarea name="descr"  id="descr" class="input_border" rows="2" cols="30"><?php echo $this->row->descr; ?></textarea>
                              </td>
                            </tr>
                            <tr>
                              <td><?php echo JText::_( 'Share Album With:');?></td>
                              <td class="privacy"><select name="privacy" id="privacy" class="select">
                                  <option value="0" <?php if(($this->row->privacy)==0){ echo 'selected="selected"';}  ?>><?php echo JText::_( 'All');?></option>
                                  <option value="1" <?php if(($this->row->privacy)==1){ echo 'selected="selected"';}  ?> ><?php echo JText::_( 'Friends Only');?></option>
                                  <option value="2" <?php if(($this->row->privacy)==2){ echo 'selected="selected"';}  ?> ><?php echo JText::_( 'Friends Of Friends');?></option>
                                  <option value="3" <?php if(($this->row->privacy)==3){ echo 'selected="selected"';}  ?>><?php echo JText::_( 'Only Me');?></option>
                                </select>
                              </td>
                            </tr>
                            <tr>
                              <td></td>
                              <td><br /><input type="submit" name="savechanges"  value="<?php echo JText::_( 'Save');?>" class="postButton_small" style="float:left !important;" />
                               <a href="<?php echo $link; ?>" >
                                <input type="button"   value="<?php echo JText::_( 'Cancel');?>"  class="postButton_small" style="float:left !important;"/>
                                </a> </td>
                            </tr>
                          </table>
                        </td>
                    </tr>
                  </table>
                  <input type="hidden" name="option" value="com_awdjomalbum" />
                  <input type="hidden" name="task" id="task" value="savealbum" />
                  <input type="hidden" name="view" value="awd_addphoto" />
                  <input type="hidden" name="id" value="<?php echo $_REQUEST['id']?>" />
                </form>
                  </div>
  <div id="tab3">
<form action="" method="post" name="adminForm" >
                  <table style="border:1px solid #<?php echo $color[14]; ?>" width="100%">
                    <tr>
                      <td align="center">
                          <table  align="center" bgcolor="#FFFFFF" width="100%">
                            <tr align="center">
                              <td ><?php echo JText::_( 'Delete Photo Album?');?>
                                <hr style="background-color:#fff;border:#000 1px dotted;border-style: none none dotted;color:#ccc; margin:5px 0px;"/></td>
                            </tr>
                            <tr >
                              <td align="center"><input type="submit" name="albumdelete" id="albumdelete" value="<?php echo JText::_( 'Delete');?>" class="postButton_small" style="float:none !important;"  />
                                <a href="<?php echo $link; ?>">
                                <input type="button" name="delcancel" value="<?php echo JText::_( 'Cancel');?>" class="postButton_small" style="float:none !important;"/>
                                </a> </td>
                            </tr>
                          </table>
                        </td>
                    </tr>
                  </table>
                  <input type="hidden" name="option" value="com_awdjomalbum" />
                  <input type="hidden" name="task" id="task" value="deletealbum" />
                  <input type="hidden" name="view" value="awd_addphoto" />
                  <input type="hidden" name="id" value="<?php echo $_REQUEST['id']?>" />
                </form>
                  </div>
  
</div>
          
          
        </div>
      </div>
    </div>
  </div>
</div>
<script language="javascript">
 
function deleteAlbumImages(imageID,photoRowid,albumID)
{  
	url="<?php echo JURI::base(); ?>index.php?option=com_awdjomalbum&view=awdalbumImageDelete&task=deleteImage&imageID="+imageID+"&albumID="+albumID;
	document.getElementById(photoRowid).innerHTML='<img src="components/com_awdjomalbum/images/loader.gif"/>';
	var x = new Request({
	url: url, 
	method: 'get', 
	onSuccess: function(responseText){
	 
	document.getElementById('listofPhotos').innerHTML = responseText;
}
}).send(); // To pass values : }).send('country_id=' + document.getElementById('country_id').value );
 
return false;
	
	 
	}
	
	
 
 </script>
<script>
function showloading()
{
// var strcontent="<table width='100%'><tr><td height='300' style='background-image:url(<?php echo JURI::base();?>components/com_awdjomalbum/images/loadergallery.gif); background-repeat:no-repeat; background-position:center;'></td></tr></table>";
  jQuery("#awdloadingcontent").hide(); 
 
 // jQuery("#awdloadingimages").html(strcontent); 
  jQuery("#awdloadingimages").show();
  return true;
}
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
