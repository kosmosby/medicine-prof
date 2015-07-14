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
// get user object
$db =& JFactory::getDBO();

$user = &JFactory::getUser();
//JHTML::_('behavior.calendar');

// include js and css
require_once(JPATH_COMPONENT . DS . 'js' . DS . 'include.php');
//$Itemid = AwdwallHelperUser::getComItemId();
$Itemid = AwdwallHelperUser::getComItemId();
// get jomosical Itemid
$jsItemid = AwdwallHelperUser::getJsItemId();
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

//$friendJsUrl = JRoute::_('index.php?com_comprofiler&task=manageConnections&option=com_comprofiler&Itemid=' . $Itemid, false);
$friendJsUrl = JRoute::_('index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid);
$groupsUrl = JRoute::_('index.php?option=com_awdwall&task=groups&Itemid=' . $Itemid, false);
//$config = &JComponentHelper::getParams('com_awdwall');
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
$width 			= $config->get('width', 725);
$getrealtimecomment = $config->get('getrealtimecomment', 0);
$display_gallery = $config->get('display_gallery', 1);
$fbshareaapid= $config->get('fbshareaapid', '');
?>
<style type="text/css">
#awd-mainarea p{
clear:both;}

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
	 #awd-mainarea .rbroundboxright, #awd-mainarea .fullboxtop,  #awd-mainarea .lightblue_box, #awd-mainarea .rbroundboxrighttop{
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
	<?php }?>
	#awd-mainarea .rbroundboxleft .mid_content a, #awd-mainarea .blog_groups, #awd-mainarea .blog_groups a, #awd-mainarea .blog_friend, #awd-mainarea .blog_friend a, #awd-mainarea a.authorlink{
		color:#<?php echo $this->color[6]; ?>!important;
	}
	#awd-mainarea #msg_content .rbroundboxleft, #awd-mainarea #msg_content .awdfullbox{
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
	#awd-mainarea .whitebox, #awd-mainarea .blog_content, #awd-mainarea .about_me{
		background-color:#<?php echo $this->color[12]; ?>;
	}
	<?php if($template!='default'){?>
	#awd-mainarea .wallheading, #awd-mainarea .wallheadingRight{
		background-color:#<?php echo $this->color[13]; ?>;
	}
	#awd-mainarea .round, #awd-mainarea .search_user{
		background-color:#<?php echo $this->color[14]; ?>;
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
#awd-mainarea ul.profileMenu .totalno{
	background:#<?php echo $this->color[12]; ?>;
    border: 1px solid #<?php echo $this->color[2]; ?>;
    color: #<?php echo $this->color[8]; ?>;
    float: right;
    margin-right: 5px;
    margin-top: 0;
    padding: 0 3px;
    text-align: center;
}

#awd-mainarea ul.profileMenu li {
    margin-bottom: 5px;
}
#awd-mainarea ul.profileMenu .mainwalllavel
{
color:#<?php echo $this->color[8]; ?>;
font-weight:bold;
float:left;
}
#awd-mainarea .user_place .wall .profileLink a{
margin:0px;
padding:0px;
font-weight:normal !important;
}
#awd-mainarea .user_place .wall a{
font-weight:bold !important;
}
/*.awd_dropDownMain,.awd_dropDownMain{
margin:9px -50px;
}*/
#awd-mainarea .walltowall li .postlink a
{
font-weight:normal !important;
}
#awd-mainarea  .profileName  a{	
padding-left:0px !important;
}
#main ul
{ 
list-style-type:none !important;
padding-left:0px;
}
#awd-mainarea .post_type_icon
{ margin-left:-20px;}
#awd-mainarea .ui-widget{
font-size:12px!important;
}
/*#awd-mainarea .ui-state-default, .ui-widget-content .ui-state-default{
height:20px!important;
}*/
#btl-register-in-process,#btl-login-in-process,#btl-forgot-in-process{
	display: none;
	background: url("<?php echo JURI::base();?>components/com_awdwall/images/loading.gif") no-repeat #000 50%;
	opacity: 0.4;
	width: 100%;
	height: 100%;
	position: absolute;
	z-index: 9999;
	top:-1px;
	left:-1px;
	padding-top:1px;
	padding-left:1px;
}


</style>
<script src="components/com_awdwall/js/selectcustomer.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function(){
		//dangcv upload status		
		jQuery("#awd_message").bind('paste', function(e){
			var el = jQuery(this);
			setTimeout(function() {
				var value = jQuery(el).val();
				ajaxStatusUpload(value);
			}, 100);
		});
		jQuery( "#startdate" ).datepicker({
			showOn: "button",
			buttonImage: "<?php echo JURI::base();?>components/com_awdwall/images/calendar.gif",
			buttonImageOnly: true,
			dateFormat: 'yy-mm-dd'
		});
		jQuery( "#enddate" ).datepicker({
			showOn: "button",
			buttonImage: "<?php echo JURI::base();?>components/com_awdwall/images/calendar.gif",
			buttonImageOnly: true,
			dateFormat: 'yy-mm-dd'
		});
		jQuery.datepicker.formatDate('yy-mm-dd');
		
		
	});
	function changeimg(str, wid_tmp){
		var url = document.getElementById("url_root").value;
		url = url+"joomla.php";
		var sid = Math.random();
		str = str.replace("http://", "");
		
		jQuery.get(url, { q: str, q_wid_tmp: wid_tmp, sid: sid },
		function(data){
			//alert("Data Loaded: " + data);
		});
	}

	function no_img(){
		var n = document.getElementById('count_img').getAttributeNode('value').value;
		var k = 0;
		for(i=1; i<=n; i++){
			var temp = document.getElementById(i).getAttributeNode('class').value;
			if(temp == 'no_hidden'){
				k = i;
				document.getElementById('hidden_img').value = k;
			}
		}
		var check = document.getElementById('hidden_img').checked;
		if(check){
			for(i=1; i<=n; i++){
				document.getElementById(i).className = 'hidden';
			}
			document.getElementById('cur_image').value = 0;
			document.getElementById('div_awd_attached_img').style.display = 'none';
			document.getElementById('count_text').style.display = 'none';
			document.getElementById('prev').style.display = 'none';
			document.getElementById('next').style.display = 'none';
			//update database
			var src = "";
			var wid_tmp = document.getElementById('wid_tmp').value;
			changeimg(src, wid_tmp);
		}
		else{
			var k = document.getElementById('hidden_img').getAttributeNode('value').value;
			var src = document.getElementById(k).getAttributeNode('src').value;
			var wid_tmp = document.getElementById('wid_tmp').value;
			changeimg(src,wid_tmp);
			for(i=1; i<=n; i++){				
				if(i == k){
					document.getElementById(i).className = 'no_hidden';
					document.getElementById('cur_image').value = k;
				}else{
					document.getElementById(i).className = 'hidden';
				}
			}
			document.getElementById('div_awd_attached_img').style.display = 'block';
			document.getElementById('count_text').style.display = 'block';
			document.getElementById('prev').style.display = 'inline';
			document.getElementById('next').style.display = 'inline';
		}
	}
	function next_img(){
		var temp;
		var id;
		var n = document.getElementById('count_img').getAttributeNode('value').value;
		var k = 0;
		for(i=1; i<=n; i++){
			temp = document.getElementById(i).getAttributeNode('class').value;
			if(temp == 'no_hidden'){
				k = i;
				id = document.getElementById(i).getAttributeNode('class').value;
			}
		}
		if( k == 0 || k == n ){
			k = n; 
		}else{
			k = k + 1;
		}
		document.getElementById("count_img_active").innerHTML = k;
		var src = document.getElementById(k).getAttributeNode('src').value;
		var wid_tmp = document.getElementById('wid_tmp').value;
		changeimg(src,wid_tmp);					
		for(i=1; i<=n; i++){
			if(i == k){
				document.getElementById(i).className = 'no_hidden';
				document.getElementById('cur_image').value = i;
			}
			else{
				document.getElementById(i).className = 'hidden';
			}
		}
	}
	function prev_img(){
		var temp;
		var id;
		var n = document.getElementById('count_img').getAttributeNode('value').value;
		var k = 0;
		for(i=1; i<=n; i++){
			temp = document.getElementById(i).getAttributeNode('class').value;
			if(temp == 'no_hidden'){
				k = i;
				id = document.getElementById(i).getAttributeNode('class').value;
			}
		}
		if(k == 1){
			k =1;
		}else{
			k = k-1;
		}
		document.getElementById("count_img_active").innerHTML = k;
		var src = document.getElementById(k).getAttributeNode('src').value;
		var wid_tmp = document.getElementById('wid_tmp').value;
		changeimg(src, wid_tmp);
		
		for(i=1; i<=n; i++){
			if(i == k){
				document.getElementById(i).className = 'no_hidden';
				document.getElementById('cur_image').value = i;
			}
			else{
				document.getElementById(i).className = 'hidden';
			}
		}
	}
</script>
<div style="width:100%" id="awd-mainarea">
<script type="text/javascript">
		//show share
		function show_share(){
			getElementByClass_share('a', 'ashare');
		}
		function hidden_share(){
			getElementByClass_share('a', 'ashare');
			var att=document.getElementById(d).getAttributeNode('rel').value;
			document.getElementById(att).style.display = "none";
			getElementByClass_share('a', 'ashare');
		}
		function getElementByClass_share(node, class_name){
			var tag = document.getElementsByTagName(node);
			var getAgn = tag;
			class_name_click = class_name+'ashare';
			class_name_unclick = class_name+'ashare';
			for (i=0; i<tag.length; i++) {
				if(tag[i].className == class_name_click || tag[i].className == class_name || tag[i].className == class_name_unclick){
					tag[i].onclick=function() {																		
						for (var x=0; x<getAgn.length; x++) {
							getAgn[x].className=getAgn[x].className.replace("unclick", "");
							getAgn[x].className=getAgn[x].className.replace("click", "unclick");																					
						}
						if ((this.className.indexOf('unclick'))!=-1) {
							this.className=this.className.replace("unclick", "");																			
						}
						else { this.className+="click";}
						if(this.className == 'ashareclick')
							inser_share('a', 'ashare', 'ashareclick', 'ashare');
						if(this.className == 'share')
							inser_share('a', 'ashare', 'ashareclick', 'ashareclick');	
					}
				}
			}
		}
		
		function inser_share(node, text, class_name1, class_name2){
			var elms=document.getElementsByTagName(node);
			for(i=0;i<elms.length;i++){
				if(elms[i].className== class_name1){
					elms[i].id = text + i;
					d =  text + i;
				}
				if(elms[i].className==class_name2){ elms[i].id = ""; }
			}
			var att			= document.getElementById(d);
			var id		 	= att.getAttributeNode('rel').value;
			var id_tyle		= document.getElementById(id);
			display(id_tyle, 'inline');
		}
		
		function display(type, text){
			type.style.display = text;
		}
	
		//show video
		function show_video(){
			getElementByClass('a');
		}
		function getElementByClass(node){
			var tag = document.getElementsByTagName(node);
			var getAgn = tag;
			for (i=0; i<tag.length; i++) {
				if(tag[i].className == 'show_videclick' || tag[i].className == 'show_vide' || tag[i].className == 'show_videunclick'){
					tag[i].onclick=function() {																		
						for (var x=0; x<getAgn.length; x++) {
							getAgn[x].className=getAgn[x].className.replace("unclick", "");
							getAgn[x].className=getAgn[x].className.replace("click", "unclick");																					
						}
						if ((this.className.indexOf('unclick'))!=-1) {
							this.className=this.className.replace("unclick", "");																			
						}
						else { this.className+="click";	}
						inser_id();
					}
				}
			}
		}
		
		function inser_id(){
			var elms=document.getElementsByTagName('a');
			for(i=0;i<elms.length;i++){
				if(elms[i].className=='show_videclick'){
					elms[i].id="ab"+i;
					d = "ab"+i;
				}
				if(elms[i].className=='show_videunclick'){ elms[i].id=""; }
			}
			
			var att=document.getElementById(d);
			var url	 		= att.getAttributeNode('rel').value;
			var type		= att.getAttributeNode('alt').value;
			var id_inser 	= att.getAttributeNode('rev').value;
			
			var div 		= document.getElementById(id_inser);
			if(type = 'youtube'){
				div.innerHTML = '<object width="426" height="240" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="opaque" name="wmode"><param value="true" name="allowfullscreen"><param value="always" name="allowscriptaccess"><param value="'+url+'&amp;autoplay=1" name="movie"><embed width="426" height="240" wmode="opaque" allowscriptaccess="always" allowfullscreen="true" type="application/x-shockwave-flash" src="'+url+'&amp;autoplay=1"></object>';
			}
			if(type = 'metacafe'){
				div.innerHTML = '<object width="426" height="240" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="opaque" name="wmode"><param value="true" name="allowfullscreen"><param value="always" name="allowscriptaccess"><param value="'+url+'" name="movie"><embed width="426" height="240" wmode="opaque" allowscriptaccess="always" allowfullscreen="true" type="application/x-shockwave-flash" src="'+url+'"></object>';
			}
			if(type = 'vimeo'){
				div.innerHTML = '<object width="426" height="240" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="opaque" name="wmode"><param value="true" name="allowfullscreen"><param value="always" name="allowscriptaccess"><param value="'+url+'" name="movie"><embed width="426" height="240" wmode="opaque" allowscriptaccess="always" allowfullscreen="true" type="application/x-shockwave-flash" src="'+url+'"></object>';
			}
			if(type = 'myspace'){
				div.innerHTML = '<object width="426" height="240" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="opaque" name="wmode"><param value="true" name="allowfullscreen"><param value="always" name="allowscriptaccess"><param value="'+url+'" name="movie"><embed width="426" height="240" wmode="opaque" allowscriptaccess="always" allowfullscreen="true" type="application/x-shockwave-flash" src="'+url+'"></object>';
			}
			if(type = 'howcast'){
				div.innerHTML = '<object width="426" height="240" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param value="opaque" name="wmode"><param value="true" name="allowfullscreen"><param value="always" name="allowscriptaccess"><param value="'+url+'" name="movie"><embed style="background:#000000" width="426" height="240" wmode="opaque" allowscriptaccess="always" allowfullscreen="true" type="application/x-shockwave-flash" src="'+url+'"></object>';
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

        <li <?php if($layout == 'main') {echo 'class="activenews"';}else{echo 'class="newsfeed"';} ?>><a <?php if($layout == 'main') {echo 'class="activenews"';}else{echo 'class="newsfeed"';} ?> href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('News Feed');?>" ></a> </li>

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
        <li><a href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('Friends');?>"><?php echo JText::_('Friends');?> <?php if($template!='default'){?><font color="red" size="1"><?php if((int)$this->pendingFriends > 1) echo '(' . $this->pendingFriends . JText::_('Requests') . ')';elseif((int)$this->pendingFriends == 1) echo '(' . $this->pendingFriends . JText::_('') . ')';?></font><?php }?></a></li>
		<?php if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) {?>
        
		<li class="separator"> </li>

		 <li> <a href="<?php echo $groupsUrl;?>" title="<?php echo JText::_('Groups');?>"><?php echo JText::_('Groups');?></a> </li>
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
  
  <div class="awdfullbox_top fullboxtop  clearfix"> <span class="bl"></span>
  
   <?php if($template=='default'){?>
    <div class="rbroundboxleft ">

      <div class="user_place" style="padding-top:0px;"> 
      
<div class="blue demo-container">
<div id="btl-login-in-process"></div>
<ul id="drilldown-4">
 <?php if((int)$user->id){?>
<li ><a  href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid=' . $Itemid, false);?>"  title="<?php echo JText::_('My Wall');?>" ><img src="<?php echo AwdwallHelperUser::getBigAvatar32($this->wuid);?>" class="avtartool "  height="32" width="32" style="margin-top:7px; margin-left:10px;"/><span>
  <?php echo AwdwallHelperUser::getDisplayName($user->id);?>
  </span></a> </li>
   <?php } ?>
<li ><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('News Feed');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/09.png" alt=""/><span><?php echo JText::_('News Feed');?></span></a></li>
<li><a  href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('Friends');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/friends.png" alt=""/><span><?php echo JText::_('Friends');?></span></a>
  <?php if((int)$this->pendingFriends) { ?>
  <span class="message-count_leftmenu" ><?php echo (int)$this->pendingFriends;?></span>
  <?php } ?>
</li>
<?php if($this->displayPm){?>
<li ><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&task=pm&Itemid=' . $Itemid);?>" title="<?php echo JText::_('Messages');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/33.png" alt=""/><span><?php echo JText::_('Messages');?></span></a>
  <?php if($this->totalpm) {?>
  <span class="message-count_leftmenu" ><?php echo $this->totalpm;?></span>
  <?php } ?>
</li>
<?php }?>
<?php if((int)$this->displayEvent){?>
<li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=events&attendevent=1&wuid='.$user->id.'&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Event');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/29.png" alt=""/><span><?php echo JText::_('Event');?></span></a></li>
<?php }?>
<?php if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) {?>
<li ><a href="javascript:void(0)" title="<?php echo JText::_('Groups');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/112.png" alt=""/><span><?php echo JText::_('Groups');?></span></a>
<?php if($this->pendingGroups) {?>
  <span class="message-count_leftmenu"><?php echo $this->pendingGroups;?></span>
  <?php } ?>
  <ul>
    <li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=group&task=newgroup&Itemid=' . $Itemid);?>" title="<?php echo JText::_('Create Group');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/create-a-group.png" alt=""/><span><?php echo JText::_('Create Group');?></span></a></li>
    <li><a href="<?php echo $groupsUrl;?>" title="<?php echo JText::_('Groups');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/112.png" alt=""/><span><?php echo JText::_('Groups');?></span></a></li>
  </ul>
</li>
<?php }?>
<li><a href="javascript:void(0)"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/140.png" alt=""/><span><?php echo JText::_('FILTER');?></span></a>
  <ul>
    <?php if((int)$this->displayVideo){?>
    <li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=videos&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Videos');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/video_filter.png" alt=""/><span><?php echo JText::_('Videos');?></span></a></li>
    <?php }?>
    <?php if((int)$this->displayImage){?>
    <li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=images&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Images');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/images_filter.png" alt=""/><span><?php echo JText::_('Images');?></span></a></li>
    <?php }?>
    <?php if((int)$this->displayMusic){?>
    <li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=music&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Music');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/music_filter.png" alt=""/><span><?php echo JText::_('Music');?></span></a></li>
    <?php }?>
    <?php if((int)$this->displayLink){?>
    <li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=links&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Links');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/link_filter.png" alt=""/><span><?php echo JText::_('Links');?></span></a></li>
    <?php }?>
    <?php if((int)$this->displayFile){?>
    <li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=files&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Files');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/file_filter.png" alt=""/><span><?php echo JText::_('Files');?></span></a></li>
    <?php }?>
    <?php if((int)$this->displayTrail){?>
    <li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=trails&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Trails');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/trail_filter.png" alt=""/><span><?php echo JText::_('Trails');?></span></a></li>
    <?php }?>
    <?php if((int)$this->displayJing){?>
    <li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=jing&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Jings');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/jing_filter.png" alt=""/><span><?php echo JText::_('Jings');?></span></a></li>
    <?php } ?>
    <?php if((int)$this->displayEvent){?>
    <li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=events&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Event');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/event_filter.png" alt=""/><span><?php echo JText::_('Event');?></span></a></li>
    <?php } ?>
    <?php if((int)$this->displayArticle){?>
    <li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=article&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Article');?>"><img src="<?php echo JURI::base().'components/com_awdwall/';?>images/<?php echo $template;?>/article_filter.png" alt=""/><span><?php echo JText::_('Article');?></span></a></li>
    <?php } ?>
  </ul>
</li>

</ul>
</div>



      </div>

    </div>
 <?php }else{ ?>
 <div class="rbroundboxleft_user">
      <div class="user_place"> 
	  <div class="wall"  style="margin-top:15px;text-align:left; margin-left:10px;">
	  <?php if((int)$user->id){?>
	  
	  <span  class="mainwalluser"> 
	  <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id . '&Itemid=' . $Itemid);?>">
	  
		<img  src="<?php echo AwdwallHelperUser::getBigAvatar51($this->wuid);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($this->wuid);?>" title="<?php echo AwdwallHelperUser::getDisplayName($this->wuid);?>"  height="50" width="50" /></a>
		 </span>  
		 <div style="height:5px;"></div>
		<span class="profileName"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid=' . $Itemid, false);?>">
		<?php echo AwdwallHelperUser::getDisplayName($user->id);?></a>
		</span><br />
        <span class="profileLink"><br /><?php /*?><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('View my wall');?>"><?php echo JText::_('View my wall');?></a><?php */?></span>
	  <?php }?>
	  </div>
        <ul class="profileMenu">

          <li class="ppm"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('News Feed');?>"><?php echo JText::_('News Feed');?></a></li>

		  <?php if((int)$user->id){?>
		  <li class="friend"><a href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('Friends');?>"><?php echo JText::_('Friends');?></a>
		   <?php if($this->pendingFriends) {?>
  				<span class="totalno"><?php echo $this->pendingFriends;?></span>
			<?php }?>
  		 </li>
		  <?php if($this->displayPm){?>
			  <li class="pmessage"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&task=pm&Itemid=' . $Itemid);?>" title="<?php echo JText::_('Messages');?>"><?php echo JText::_('Messages');?></a>
			 <?php if($this->totalpm) {?>
				<span class="totalno"><?php echo $this->totalpm;?></span>
			<?php }?>
			  </li>
		  <?php }?>
		 <?php if((int)$this->displayEvent){?>

		  <li class="pevent">
		  <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=events&attendevent=1&wuid='.$user->id.'&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Event');?>"><?php echo JText::_('Event');?></a><?php 
		  if($this->totaleventOfuser){?>
		  	<span class="totalno"><?php echo $this->totaleventOfuser;?></span>
		<?php }?>
		  </li>

		 <?php }?>
		  <?php if($this->display_group || ($this->display_group_for_moderators && in_array($user->id,$this->moderator_users)) ) {?>
				  <li class="line" style="margin-bottom: 6px;">&nbsp;</li>
				  <label class="mainwalllavel"><?php echo JText::_('GROUPS');?></label>
				   <div style="height:5px; clear:both"></div>
				   <li class="groups"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=group&task=newgroup&Itemid=' . $Itemid);?>"><?php echo JText::_('Create Group');?></a></li>
				
				  <li class="groups"><a href="<?php echo $groupsUrl;?>" title="<?php echo JText::_('Groups');?>"><?php echo JText::_('Groups');?></a></li>
		<?php }
		}?>
          <li class="line" style="margin-bottom: 6px;">&nbsp;</li>
		  <label class="mainwalllavel"><?php echo JText::_('FILTER');?></label>
		   <div style="height:5px; clear:both"></div>

		  <?php if((int)$this->displayVideo){?>

          <li class="pvideo"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=videos&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Videos');?>"><?php echo JText::_('Videos');?></a></li>

		  <?php }?>

		   <?php if((int)$this->displayImage){?>

          <li class="pimage"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=images&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Images');?>"><?php echo JText::_('Images');?></a></li>

			 <?php }?>

			<?php if((int)$this->displayMusic){?>

		   <li class="music"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=music&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Music');?>"><?php echo JText::_('Music');?></a></li>

		    <?php }?>

		<?php if((int)$this->displayLink){?>

          <li class="links"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=links&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Links');?>"><?php echo JText::_('Links');?></a></li>

		  <?php }?>

		 <?php if((int)$this->displayFile){?>

          <li class="files"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=files&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Files');?>"><?php echo JText::_('Files');?></a></li>

		 <?php }?>

		 <?php if((int)$this->displayTrail){?>

		  <li class="ptrail"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=trails&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Trails');?>"><?php echo JText::_('Trails');?></a></li>

		 <?php }?>
		 <?php if((int)$this->displayJing){?>

		  <li class="pjing"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=jing&Itemid=' . $Itemid);?>" title="<?php echo JText::_('Jings');?>"><?php echo JText::_('Jing');?></a></li>

		 <?php }?>
		 <?php if((int)$this->displayEvent){?>

		  <li class="pevent">
		  <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=events&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Event');?>"><?php echo JText::_('Event');?></a></li>

		 <?php }?>
		 <?php if((int)$this->displayArticle){?>
		 
		  <li class="particle">
		  <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&task=article&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Article');?>"><?php echo JText::_('Article');?></a></li>

			 <?php } ?>
          <li class="line" style="margin-bottom: 6px;">&nbsp;</li>


        </ul>
      </div>
    </div>
	<?php } ?>
	
    <div class="rbroundboxrighttop" style="margin-top:0xp;padding-top:0px;"><span class="bl2"></span><span class="br2"></span>
      <div class="mid_content_top">&nbsp;&nbsp;
        <h2 class="newsFeed"><?php echo JText::_('News Feed');?></h2>
		<script type="text/javascript">
	function show_list_attach(){
		var show_attach = document.getElementById('list_attach');
		show_attach.style.display = "inline";
		show_attach.style.float = "left";
				
				
		var show_attach_vide = document.getElementById('awd_video_form');
		show_attach_vide.style.float = "left";
		
		var show_attach_img = document.getElementById('awd_image_form');
		show_attach_img.style.float = "left";
		
		var show_attach_link = document.getElementById('awd_link_form');
		show_attach_link.style.float = "left";
		
		var show_jing_link = document.getElementById('awd_jing_form');
		show_jing_link.style.float = "left";
		
		var show_event_link = document.getElementById('awd_event_form');
		show_event_link.style.float = "left";
		
		var show_trail_link = document.getElementById('awd_trail_form');
		show_trail_link.style.float = "left";
		
		var show_article_link = document.getElementById('awd_article_form');
		show_article_link.style.float = "left";
	}
	function dis_list_attach(){
		var show_attach = document.getElementById('list_attach');
		show_attach.style.display = "none";
		
		var show_attach_vide = document.getElementById('awd_video_form');
		show_attach_vide.style.display = "none";
		
		var show_attach_img = document.getElementById('awd_image_form');
		show_attach_img.style.display = "none";
		
		var show_attach_link = document.getElementById('awd_link_form');
		show_attach_link.style.display = "none";
		
		var show_jing_link = document.getElementById('awd_jing_form');
		show_jing_link.style.display = "none";
		
		var show_event_link = document.getElementById('awd_event_form');
		show_event_link.style.display = "none";
		
		var show_trail_link = document.getElementById('awd_trail_form');
		show_trail_link.style.display = "none";
		
		var show_article_link = document.getElementById('awd_article_form');
		show_article_link.style.display = "none";
	}
</script>		

        <form name="frm_message" id="frm_message" method="post" action="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&task=addmsg', false);?>" onsubmit="return false;">
		<input type="hidden" name="wall_last_time" id="wall_last_time" value="<?php echo time();?>" />
		<input type="hidden" name="layout" id="layout" value="main" />
		<input type="hidden" name="posted_wid" id="posted_wid" value="" />
		<?php echo AwdwallHelperUser::getSmileyicons("awd_message");?>
        <textarea  rows="" cols="" onclick="show_list_attach();" class="round" id="awd_message" name="awd_message" style="height:50px;"></textarea>
        <?php echo AwdwallHelperUser::awdshowsmilyicon("awd_message");?>        
  <div class="post_msg_btn">
		 <?php if((int)$user->id){?> 

			<input type="hidden"  name="post_privacy" id="post_privacy" value="0" />

			<input type="hidden" name="receiver_id" id="receiver_id" value="<?php echo $this->wuid;?>"/>
			 <span style="float:right; width:140px;">

				 <select class="awd_Selectprivacy js_PriDefault" name="creator-privacy" style="display: none;">
					<option  value="0" class="js_PriOption js_Pri-0"><?php echo JText::_('Everyone');?></option>
					<option value="1" class="js_PriOption js_Pri-30"><?php echo JText::_('Friends');?></option>
					<option value="2" class="js_PriOption js_Pri-20"><?php echo JText::_('Friend Of Friends');?></option>
				</select>
			
            <input class="postButton_small" title="<?php echo JText::_('Click to post attachment');?>" value="<?php echo JText::_('Post');?>"  type="submit" onclick="aPostMsg('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=addmsg&tmpl=component&Itemid='.$Itemid;?>', '');" />
			
			</span>
			
		<!--Begin insert list icon and function -->							
    
        <ul class="attach" id="list_attach" style="display:none;">
          <li><?php echo JText::_('Attach');?>:</li>
		  <?php if((int)$this->displayVideo){?>
		  
          <li><a href="javascript:void(0);" onclick="openVideoBox();" ><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-videos.png" title="<?php echo JText::_('Videos');?>" alt="<?php echo JText::_('Videos');?>" border="0" /></a></li>
		   <?php }?>
		   
		 <?php if((int)$this->displayImage){?>
          <li><a href="javascript:void(0);" onclick="openImageBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-images.png" title="<?php echo JText::_('Images');?>" alt="<?php echo JText::_('Images');?>" border="0" /></a></li>
		   <?php }?>
		   
		   
		   <?php if((int)$this->displayMusic){?>
		  <li><a href="javascript:void(0);" onclick="openMp3Box();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-music.png" title="<?php echo JText::_('Music');?>" alt="<?php echo JText::_('Music');?>" border="0" /></a></li>
		   <?php }?>
	
		   
		   <?php if((int)$this->displayLink){?>
		  <li><a href="javascript:void(0);" onclick="openLinkBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('Links');?>" alt="<?php echo JText::_('Links');?>" border="0" /></a></li>
		   <?php }?>
		   

		   <?php if((int)$this->displayFile){?>
		   <li><a href="javascript:void(0);" onclick="openFileBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-file.png" title="<?php echo JText::_('Files');?>" alt="<?php echo JText::_('Files');?>" border="0" /></a></li>
		    <?php }?>
			
			
		    <?php if((int)$this->displayTrail){?>
		  <li><a href="javascript:void(0);" onclick="openTrailBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-trails.png" title="<?php echo JText::_('Trails');?>" alt="<?php echo JText::_('Trails');?>" border="0" /></a></li>
		   <?php }?>
		   <?php if((int)$this->displayJing){?>
		  <li><a href="javascript:void(0);" onclick="openJingBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-jing.png" title="<?php echo JText::_('Jing');?>" alt="<?php echo JText::_('Jing');?>" border="0" /></a></li>
		  <?php }?>
		    <?php if((int)$this->displayEvent){?>
		  <li><a href="javascript:void(0);" onclick="openEventBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-event.png" title="<?php echo JText::_('Events');?>" alt="<?php echo JText::_('Events');?>" border="0" /></a></li>
		   <?php }?>
		    <?php if((int)$this->displayArticle){
					if($this->user_groupid){?>

		  <li><a href="javascript:void(0);" onclick="openArticleBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-article.png" title="<?php echo JText::_('Article');?>" alt="<?php echo JText::_('Article');?>" border="0" /></a></li>
			   <?php } 
		   		}?>
		  <!-- <span class="close"><a style="font-weight: bold; font-size: 14px; color:#7C7C7C;" alt="delete" href="javascript:void(1);" onclick="dis_list_attach();">X</a></span>-->
		  
		  <center> 
		   <li id="status_loading" style="display: none;">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/default/ajax-loader.gif"></li>
		   </center>
        </ul>
			
		<!--End insert list icon and function -->	
			
		 <?php }else{?>
			<span class="login_post_text">&ldquo;<?php echo JText::_('You need to login to post comments');?>&rdquo;</span>
		 <?php }?>
          </div>
		  <!-- Begin ineser icon -->	
		<input type="hidden" name="cur_image" id="cur_image" />
		<div style="display:none;" id="div_awd_attached_file">
			<h3><?php echo JText::_('Attached');?></h3>
			<div id="div_awd_attached_img">		
			</div>
			<div id="div_awd_attached_info">
				<span class="editable" id="awd_attached_title"></span><br>
				<span id="awd_attached_file"></span><br>
				<span class="editable" id="awd_attached_des"></span>
				<br>
				<br>
				<div class="awd_attached_attribute">
					<label style="float:left"><img src="<?php echo JURI::base();?>components/com_awdwall/images/prev.png" id="prev" onclick="prev_img();" alt="" /><img src="<?php echo JURI::base();?>components/com_awdwall/images/next.png" id="next" onclick="next_img();" alt="" /></label>				
					<div id="count_text">
						<span id="count_img_active">1</span> <?php echo Jtext::_('of'); ?> <span id="sum_img"></span>
					</div>
					<br/>
					<br/>
					<input onclick="no_img()" type="checkbox" value="check" id="hidden_img" name="hidden_img" /> <?php echo Jtext::_('Hidden image thumb'); ?>
				</div>
			</div>
			<input type="hidden" id="count_img" />
			<input type="hidden" name="url_root" value="<?php echo JURI::base();?>" id="url_root" />
			<div style="display:none;" id="txtHint"></div>
		</div>
		<!-- video form -->
		<div class="attach_link clearfix message info" style="display:none;" id="awd_video_form" >

		<span class="close"><a href="javascript:void(0);" onClick="closeVideoBox();">x</a></span>
<BR />
		<div style="float:left; width:100%;">
		
		<div style="float:left;padding-right:20px;  ">
			<div class="vicons"><a href="http://www.youtube.com/" title="<?php echo JText::_('YouTube');?>" alt="<?php echo JText::_('YouTube');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/youtube.png" /></a></div>
			<div class="vicons"><a href="http://vids.myspace.com/" title="<?php echo JText::_('Myspace');?>" alt="<?php echo JText::_('Myspace');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/myspace.png" /></a></div>
			<div class="vicons"><a href="http://www.vimeo.com/" title="<?php echo JText::_('Vimeo');?>" alt="<?php echo JText::_('Vimeo');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/vimeo.png" /></a></div>
			<div class="vicons"><a href="http://www.metacafe.com/" title="<?php echo JText::_('Metacafe');?>" alt="<?php echo JText::_('Metacafe');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/metacafe.png" /></a></div>
			<div class="vicons"><a href="http://www.howcast.com/" title="<?php echo JText::_('Howcast');?>" alt="<?php echo JText::_('Howcast');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/howcast.png" /></a></div>
			<div class="vicons"><a href="http://blip.tv/" title="<?php echo JText::_('bliptv');?>" alt="<?php echo JText::_('bliptv');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/bliptv.png" /></a></div>
			<div class="vicons"><a href="http://www.break.com/" title="<?php echo JText::_('break');?>" alt="<?php echo JText::_('break');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/break.png" /></a></div>
			<div class="vicons"><a href="http://www.dailymotion.com/" title="<?php echo JText::_('dailymotion');?>" alt="<?php echo JText::_('dailymotion');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/dailymotion.png" /></a></div>
			<div class="vicons"><a href="http://www.flickr.com/" title="<?php echo JText::_('flickr');?>" alt="<?php echo JText::_('flickr');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/flickr.png" /></a></div>
			<div class="vicons"><a href="http://www.justin.tv/" title="<?php echo JText::_('justintv');?>" alt="<?php echo JText::_('justintv');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/justintv.png" /></a></div>
			<div class="vicons"><a href="http://www.liveleak.com/" title="<?php echo JText::_('liveleak');?>" alt="<?php echo JText::_('liveleak');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/liveleak.png" /></a></div>
		<div class="vicons"><a href="http://screen.yahoo.com/" title="<?php echo JText::_('yahoo');?>" alt="<?php echo JText::_('yahoo');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/yahoo.png" /></a></div>
			<div class="vicons"><a href="http://www.ustream.tv/" title="<?php echo JText::_('ustream');?>" alt="<?php echo JText::_('ustream');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/ustream.png" /></a></div>
			<div class="vicons"><a href="http://www.livestream.com/" title="<?php echo JText::_('livestream');?>" alt="<?php echo JText::_('livestream');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/livestream.png" /></a></div>
			<div class="vicons"><a href="http://www.mips.tv/" title="<?php echo JText::_('mips');?>" alt="<?php echo JText::_('mips');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/mips.png" /></a></div>
			<div class="vicons"><a href="http://www.mtv.com/" title="<?php echo JText::_('mtv');?>" alt="<?php echo JText::_('mtv');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/mtv.png" /></a></div>
			<div class="vicons"><a href="http://www.photobucket.com/" title="<?php echo JText::_('photobucket');?>" alt="<?php echo JText::_('photobucket');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/photobucket.png" /></a></div>
			<div class="vicons"><a href="http://www.twitch.tv/" title="<?php echo JText::_('twitch');?>" alt="<?php echo JText::_('twitch');?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/vicons/twitch.png" /></a></div>
		</div>
	</div>
    <div class="clearfix">
    <div class="form-input form-input1"><input type="text" id="vLink" name="vLink" placeholder="<?php echo JText::_('Video URL');?>" /></div>
    </div>
		 <br />

		<input type="hidden" name="awd_video_title" id="awd_video_title" value="" />

		 <input type="hidden" name="awd_video_desc" id="awd_video_desc" value="" />

		

		<span class="wr_buton_at uploadvideo">

		<input type="button" value=" <?php echo JText::_('Attach Video');?>" class="button" onClick="ajaxVideoUpload('<?php echo JURI::base().'index.php?option=com_awdwall&task=addvideo&wuid=' . $this->wuid;?>')" />

		</span><a href="javascript:void(0);" onClick="closeVideoBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>

		<span style="display:none;padding:5px 0px !important;" id="video_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>

        </div>

<!-- end video form -->

<!-- image form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_image_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeImageBox();">x</a></span>
<br /><br />


		 <?php echo JText::_('Choose image to upload');?>:			 
    <div class="clearfix">
    <div class="form-input form-input1"><input type="text" id="awd_image_title" name="awd_image_title" placeholder="<?php echo JText::_('Image Name');?>" /></div>
    </div>
    <div class="clearfix">
    <div class="form-input form-input1"><input type="file" id="awd_link_image" name="awd_link_image" placeholder="<?php echo JText::_('Image');?>" />(<?php echo JText::sprintf('Image Ext Only', AwdwallHelperUser::getImageExt());?>)</div>
    </div>
    <div class="clearfix">
    <div class="form-input form-input1"><textarea id="awd_image_description" name="awd_image_description" cols="40" rows="4" placeholder="<?php echo JText::_('Image Description');?>" ></textarea></div>
    </div>



		 <input type="hidden" name="awd_mp3_desc" id="awd_mp3_desc" value="" />

		 <br />

		<span class="wr_buton_at uploadcamera">

		<input type="button" value=" <?php echo JText::_('Upload Image');?>" class="button" onClick="ajaxImageUpload('<?php echo 'index.php?option=com_awdwall&task=addimage&wuid=' . $this->wuid;?>')" />

		</span><a href="javascript:void(0);" onClick="closeImageBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>

		<span style="display:none;padding:5px 0px !important;" id="image_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>

        </div>

<!-- end image form -->

<!-- mp3 form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_mp3_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeMp3Box();">x</a></span>
<br /><br />
		 <?php echo JText::_('Choose mp3 to upload');?>:
    <div class="clearfix">
    <div class="form-input form-input1"><input type="text" name="awd_mp3_title" id="awd_mp3_title"  maxlength="150" placeholder="<?php echo JText::_('Title');?>"/></div>
    </div>
    <div class="clearfix">
    <div class="form-input form-input1"><input type="file"  name="awd_link_mp3" id="awd_link_mp3"  maxlength="150" placeholder="<?php echo JText::_('Url');?>"/></div>
    </div>
		 <input type="hidden" name="awd_mp3_desc" id="awd_mp3_desc" value="" />

		 <br />

		<span class="wr_buton_at uploadmp3">
		<input type="button" value=" <?php echo JText::_('Upload MP3');?>" class="button" onClick="ajaxMp3Upload('<?php echo JURI::base().'index.php?option=com_awdwall&task=addmp3&wuid=' . $this->wuid;?>')" />

		</span><a href="javascript:void(0);" onClick="closeMp3Box();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>
         <br /><br /><br />
		<hr />
		 <br /><?php echo JText::_('AWDOR');?><a href="http://soundcloud.com/" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/soundcloud.gif" /></a><br /><br />
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_soundcloudurl" id="awd_soundcloudurl"   placeholder="<?php echo JText::_('Attach Sound Cloud');?>"/></div>
        </div>

         
<br /><br />

		<span class="wr_buton_at uploadmp3">
		<input type="button" value=" <?php echo JText::_('Attach');?>" class="button" onClick="ajaxsoundcloudUpload('<?php echo JURI::base().'index.php?option=com_awdwall&task=addsoundcloud&wuid=' . $this->wuid;?>')" />

		</span><a href="javascript:void(0);" onClick="closeMp3Box();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>
		<span style="display:none;padding:5px 0px !important;" id="mp3_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>

        </div>

<!-- end mp3 form -->

<!-- link form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_link_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeLinkBox();">x</a></span>
<br /><br />
	<?php // echo JText::_('Url');?>		 		
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_link" id="awd_link"   placeholder="<?php echo JText::_('Url');?>"/></div>
        </div>
		<!-- <p style="text-align:center;"><input type="text" name="awd_link" id="awd_link" value="http://" size="65" maxlength="150" class="input_border" /></p>-->

		 <input type="hidden" name="awd_link_title" id="awd_link_title" value="" />

		 <input type="hidden" name="awd_link_desc" id="awd_link_desc" value="" /> <br />

		<span class="wr_buton_at">

		<input type="button" value=" <?php echo JText::_('Attach Link');?>" class="button" onClick="ajaxLinkUpload('<?php echo 'index.php?option=com_awdwall&task=addlink&wuid=' . $this->wuid;?>')" />

		</span> <a href="javascript:void(0);" onClick="closeLinkBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a> 

<span style="display:none;padding:5px 0px !important;" id="link_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>        

        </div>

<!-- end link form -->
<!-- Jing form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_jing_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeJingBox();">x</a></span>

		 <?php echo JText::_('Jing screen and videos');?>:
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_jing_title" id="awd_jing_title"   placeholder="<?php echo JText::_('Title');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_jing_link" id="awd_jing_link"   placeholder="<?php echo JText::_('Url');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><textarea id="awd_jing_description" name="awd_jing_description"  placeholder="<?php echo JText::_('Description');?>"></textarea></div>
        </div>
<!--		 <p><label style="padding-right:17px;float:left;padding-top:10px;padding-right:57px;"><?php echo JText::_('Title');?> </label> <input type="text" name="awd_jing_title" id="awd_jing_title" class="input_border" size="48" maxlength="150" /></p>

		 <p><label style="padding-right:17px;float:left;padding-top:10px;padding-right:55px;"><?php echo JText::_('Url');?> </label><input type="text" id="awd_jing_link" name="awd_jing_link" size="50" class="input_border" /></p>
		 <p><label style="padding-right:17px;float:left;padding-top:10px;"><?php echo JText::_('Description');?> </label><textarea id="awd_jing_description" name="awd_jing_description" cols="40" rows="4" class="input_border"></textarea></p>
-->		 

<br />

		<span class="wr_buton_at">

		<input type="button" value=" <?php echo JText::_('Attach Jing');?>" class="button" onClick="ajaxJingUpload('<?php echo  JURI::base().'index.php?option=com_awdwall&task=addjing&wuid=' . $this->wuid;?>')" />
</span> <a href="javascript:void(0);" onClick="closeJingBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a> 

<span style="display:none;padding:5px 0px !important;" id="jing_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>        

        </div>

<!-- end Jings form -->

<!-- start event form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_event_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeEventBox();">x</a></span>
		<br /><br />
		 <?php echo JText::_('CHOOSE EVENT TO UPLOAD');?>:			 
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_event_title" id="awd_event_title"   placeholder="<?php echo JText::_('Title');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_event_location" id="awd_event_location"  placeholder="<?php echo JText::_('Location');?>"/><?php //echo JText::sprintf('E.g. San Jose, California ');?></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><?php echo JText::_('Start Time');?><?php $dt=date('Y'.'-'.'m'.'-'.'d');?>
				<input type="text" name="startdate" id="startdate" value="<?php echo $dt; ?>"/><?php echo $this->startHourSelect.'&nbsp;:&nbsp;'.$this->startMinSelect .' &nbsp; '.$this->startAmPmSelect;?></div>
        </div>
        <br />
        <div class="clearfix">
        <div class="form-input form-input1"><?php echo JText::_('End Time');?><input type="text" name="enddate" id="enddate" value="<?php echo $dt; ?>"/>&nbsp;<?php echo $this->endHourSelect.'&nbsp;:&nbsp;'.$this->endMinSelect .' &nbsp; ' .$this->endAmPmSelect;?></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><textarea id="awd_event_description" name="awd_event_description"  placeholder="<?php echo JText::_('Event Description');?>"></textarea></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="file" id="awd_event_image" name="awd_event_image"  placeholder="<?php echo JText::_('Image');?>" /></div>
        </div>
        <?php echo JText::_('E-mail');?>
        <div class="clearfix">
        <div class="form-input form-input1">
			<input type="radio" name="event_mail" id="event_mail" value="0" class="input_border" checked="checked" /><?php echo JText::_('No e-mail');?>
		 	<input type="radio" name="event_mail" id="event_mail" value="1" class="input_border" /><?php echo JText::_('Friends');?>
		 	<input type="radio" name="event_mail" id="event_mail" value="2" class="input_border" /><?php echo JText::_('Everyone');?>
        </div>
        </div>

		<!-- <p><label style="padding-right:78px;float:left;padding-top:10px;"><?php echo JText::_('Title');?></label> <input type="text" name="awd_event_title" id="awd_event_title" class="input_border" size="40" maxlength="150"/></p>
		 
		 <p>
		 	<label style="padding-right:55px;float:left;padding-top:10px;"><?php echo JText::_('Location');?></label> <input type="text" name="awd_event_location" id="awd_event_location" class="input_border" size="40" maxlength="150"/>
			<?php echo JText::sprintf('E.g. San Jose, California ');?>
		 </p>
		
		<p>
		 	<label style="padding-right:47px;float:left;padding-top:10px;"><?php echo JText::_('Start Time');?></label> 
			<span style="margin-top:5px; display:block">
				<?php $dt=date('Y'.'-'.'m'.'-'.'d');?>
				<input type="text" name="startdate" id="startdate" value="<?php echo $dt; ?>"/>&nbsp;<?php echo $this->startHourSelect.'&nbsp;:&nbsp;'.$this->startMinSelect .' &nbsp; '.$this->startAmPmSelect;?>
			</span>
		</p>

		<p>
		 	<label style="padding-right:50px;float:left;padding-top:10px;"><?php echo JText::_('End Time');?></label>
            <span style="margin-top:5px; display:block">
				<input type="text" name="enddate" id="enddate" value="<?php echo $dt; ?>"/>&nbsp;<?php echo $this->endHourSelect.'&nbsp;:&nbsp;'.$this->endMinSelect .' &nbsp; ' .$this->endAmPmSelect;?>
			</span>
		</p>

		 <p>
			 <label style="padding-right:7px;float:left;padding-top:10px;"><?php echo JText::_('Event Description');?></label>
	
			 <textarea id="awd_event_description" name="awd_event_description" cols="40" rows="4" class="input_border"></textarea>
	
		 </p>
		 
		 <p><label style="padding-right:70px;float:left;padding-top:10px;"><?php echo JText::_('Image');?> </label><input type="file" id="awd_event_image" name="awd_event_image" class="input_border" size="40" /><br />
		 
		 <p>
			<label style="padding-right:70px;float:left;padding-top:10px;"><?php echo JText::_('E-mail');?> </label>		 	
			<input type="radio" name="event_mail" id="event_mail" value="0" class="input_border" checked="checked" /><?php echo JText::_('No e-mail');?>
		 	<input type="radio" name="event_mail" id="event_mail" value="1" class="input_border" /><?php echo JText::_('Friends');?>
		 	<input type="radio" name="event_mail" id="event_mail" value="2" class="input_border" /><?php echo JText::_('Everyone');?>
		 </p>-->


		 <br />

		<span class="wr_buton_at uploadcamera">

		<input type="button" value=" <?php echo JText::_('Upload Event');?>" class="button" onClick="ajaxEventUpload('<?php echo 'index.php?option=com_awdwall&task=addevent&wuid=' . $this->wuid;?>')" />

		</span><a href="javascript:void(0);" onClick="closeEventBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>

		<span style="display:none;padding:5px 0px !important;" id="event_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>

        </div>
		

<!-- end event form -->

<!-- start article form -->

		<div class="attach_link clearfix message info " style="display:none;" id="awd_article_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeArticleBox();">x</a></span>
 <br /> <br />
		 <?php echo JText::_('CHOOSE ARTICLE TO UPLOAD');?>:			 
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" name="awd_article_title" id="awd_article_title"   placeholder="<?php echo JText::_('ARTICLE TITLE');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1">
        <textarea id="awd_article_description" name="awd_article_description" placeholder="<?php echo JText::_('ARTICLE BODY');?>" ></textarea>
        </div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="file" id="awd_article_image" name="awd_article_image"  placeholder="<?php echo JText::_('Image');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><?php echo JText::_('Load Wall Comments');?>
        <select name="loadjomwall" id="loadjomwall">
            <option value="0"><?php echo JText::_('JNO');?></option>
            <option value="1"><?php echo JText::_('JYES');?></option>
		</select>
		</div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1">
        	<?php echo $this->lists['catid'];?>
		</div>
        </div>
		<!-- <p><label style="padding-right:98px;float:left;padding-top:10px;"><?php echo JText::_('Title');?></label> <input type="text" name="awd_article_title" id="awd_article_title" class="input_border" size="53" maxlength="150"/></p>
		
		 <p>
			 <label style="padding-right:25px;float:left;padding-top:10px;"><?php echo JText::_('Article Description');?></label>
	
			 <textarea id="awd_article_description" name="awd_article_description" cols="50" rows="8" class="input_border"></textarea>
	
		 </p>
		 
		 <p><label style="padding-right:90px;float:left;padding-top:10px;"><?php echo JText::_('Image');?> </label><input type="file" id="awd_article_image" name="awd_article_image" class="input_border" size="40" /></p><br />
		 
		 <p><label style="padding-right:10px;float:left;padding-top:2px;"><?php echo JText::_('Load Wall Comments');?> </label>
		 	<select name="loadjomwall" id="loadjomwall">
				<option value="0">No</option>
				<option value="1">Yes</option>
			</select>
		 </p>
<br />
		 <p style="float:left;"><label style="padding-right:39px;float:left;padding-top:2px;"><?php echo JText::_('Select Category');?> </label>
			<?php echo $this->lists['catid'];?>
		 </p>-->

		 <br /><br />

		<span class="wr_buton_at uploadcamera">

		<input type="button" value=" <?php echo JText::_('Upload Article');?>" class="button" onClick="ajaxArticleUpload('<?php echo 'index.php?option=com_awdwall&task=addarticle&wuid=' . $this->wuid;?>')" />

		</span><a href="javascript:void(0);" onClick="closeArticleBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>

		<span style="display:none;padding:5px 0px !important;" id="article_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>

        </div>
		

<!-- end article form -->

<!-- file form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_file_form">

		 <span class="close"> <a href="javascript:void(0);" onClick="closeFileBox();">x</a></span>
 <br /> <br />

		<?php //echo JText::_('Choose file to upload');?> 
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" id="awd_file_title" name="awd_file_title"  placeholder="<?php echo JText::_('File Title');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="file" id="awd_file_link" name="awd_file_link"  placeholder="<?php echo JText::_('Url');?>"/>(<?php echo JText::sprintf('File Ext Only', AwdwallHelperUser::getFileExt());?></div>
        </div>
        
		<!--<p><label style="padding-right:10px;float:left;padding-top:10px;"><?php echo JText::_('Title');?> </label><input type="text" id="awd_file_title" name="awd_file_title" class="input_border" size="40" maxlength="150" /> </p>

		 <p><label style="padding-right:13px;float:left;padding-top:10px;"><?php echo JText::_('Url');?> </label><input type="file" id="awd_file_link" name="awd_file_link" class="input_border" size="40" /> (<?php echo JText::sprintf('File Ext Only', AwdwallHelperUser::getFileExt());?>)</p>-->

		 <input type="hidden" name="awd_mp3_desc" id="awd_mp3_desc" value="" />

		<br />

		<span class="wr_buton_at uploadfile">

		<input type="button" value=" <?php echo JText::_('Attach a File');?>" class="button" onClick="ajaxAwdFileUpload('<?php echo JURI::base().'index.php?option=com_awdwall&task=addfile&wuid=' . $this->wuid;?>')" />

		</span> <a href="javascript:void(0);" onClick="closeFileBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>  

<span style="display:none;padding:5px 0px !important;" id="file_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>         

        </div>

<!-- end file form -->
<!-- start Trail form -->
<div class="attach_link clearfix  message info" style="display:none;" id="awd_trail_form">

 <span class="close"> <a href="javascript:void(0);" onClick="closeTrailBox();">x</a></span>
 <br /> <br />
		 <?php echo '<strong>'.JText::_('Add Trip from EveryTail.com').'</strong>';?>&nbsp;&nbsp;<a href="http://www.everytrail.com/" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/everytrail_text_icon.jpg" /></a>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" id="awd_trail_title" name="awd_trail_title"  placeholder="<?php echo JText::_('Title');?>"/></div>
        </div>
        <div class="clearfix">
        <div class="form-input form-input1"><input type="text" id="awd_trail_link" name="awd_trail_link"  placeholder="<?php echo JText::_('Url');?>"/></div>
        </div>

		

		 <br />

		<span class="wr_buton_at uploadtrail">

		<input type="button" value=" <?php echo JText::_('Add Trip');?>" class="button" onClick="ajaxTripUpload('<?php echo 'index.php?option=com_awdwall&task=addtrail&wuid=' . $this->wuid;?>')" />

		</span> <a href="javascript:void(0);" onClick="closeTrailBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a> 

<span style="display:none;padding:5px 0px !important;" id="trail_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>        

        </div>

<!-- end Trail form -->
<!-- pm form -->

		<div class="attach_link clearfix  message info" style="display:none;" id="awd_pm_form">

		 <span class="close"> <a href="javascript:void(0);" onclick="closePmBox();">x</a></span>

		 <?php echo JText::_('Private Message');?>		

		 <span style="display:none;" id="pm_loading"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>		

		 <p><label style="padding-right:7px;float:left;padding-top:10px;"><?php echo JText::_('Text');?></label>

		 <textarea id="awd_pm_description" name="awd_pm_description" cols="40" rows="4" class="input_border" ></textarea>

		 </p>

		<input type="hidden" name="awd_pm_receiver_id" id="awd_pm_receiver_id" value="<?php echo $this->wuid;?>"/>

		<br />

		<span class="">

		<a href="javascript:void(0);" onclick="ajaxPmUpload('<?php echo 'index.php?option=com_awdwall&task=addpmuser&wuid=' . $this->wuid;?>');" class="button_cancel"> <span>&nbsp;&nbsp;&nbsp;<?php echo JText::_('PM');?>&nbsp;&nbsp;&nbsp;</span></a>

		</span> <a href="javascript:void(0);" onclick="closePmBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>        
	</div>

<!-- end pm form -->

<!-- End insert icon -->
	<input type="hidden" id="wid_tmp" name="wid_tmp" value="" />
		<input type="hidden" id="type" name="type" value="text" />
        </form>
    </div>
	  
  <br style="clear:both;" /> 
<div class="fullboxnew">
 <!-- start msg content --> 
 <span id="msg_loader"></span>
 <div id="msg_content">
 <!-- start block msg -->
<?php 
if(isset($this->msgs[0])){
	$n = count($this->msgs);
	for($i = 0; $i < $n; $i++){
		$video = null;
		$pageTitle = AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);
		$sharepageTitle = $this->msgs[$i]->message;
		if($this->msgs[$i]->type == 'video'){
			$video = $this->wallModel->getVideoInfoByWid($this->msgs[$i]->id);
			$pageTitle = $video->title;
		}
		$image = null;
		if($this->msgs[$i]->type == 'image'){
			$image = $this->wallModel->getImageInfoByWid($this->msgs[$i]->id);
			$pageTitle = $image->name;
		}
		$mp3 = null;
		if($this->msgs[$i]->type == 'mp3'){
			$mp3 = $this->wallModel->getMp3InfoByWid($this->msgs[$i]->id);
			$pageTitle = $mp3->title;
		}
		$link = null;
		if($this->msgs[$i]->type == 'link'){
			$link = $this->wallModel->getLinkInfoByWid($this->msgs[$i]->id);
			$pageTitle = $link->title;
		}
		$file = null;
		if($this->msgs[$i]->type == 'file'){
			$file  = $this->wallModel->getFileInfoByWid($this->msgs[$i]->id);
			$pageTitle = $file ->title;
		}
		$trail=null;
		if($this->msgs[$i]->type == 'trail'){
			$trail  = $this->wallModel->getTrailInfoByWid($this->msgs[$i]->id);
			$pageTitle = $trail ->trail_title;
		}
		
		if($this->msgs[$i]->type == 'jing'){
			$jing  = $this->wallModel->getJingInfoByWid($this->msgs[$i]->id);
			$pageTitle = $jing ->jing_title;
		}

		if($this->msgs[$i]->type == 'event'){

			$event  = $this->wallModel->getEventInfoByWid($this->msgs[$i]->id);

			$pageTitle = $event ->title;

		}

		if($this->msgs[$i]->type == 'article'){

			$article  = $this->wallModel->getArticleInfoByWid($this->msgs[$i]->id);

			$pageTitle = $article ->title;

		}

		$pageTitle = addslashes(htmlspecialchars($pageTitle));
		$pageTitle = str_replace(chr(13), " ", $pageTitle); //remove carriage returns
		$pageTitle = str_replace(chr(10), " ", $pageTitle); //remove line feeds 
		$sharepageTitle = $this->msgs[$i]->message;
		$sharepageTitle = addslashes(htmlspecialchars($sharepageTitle));
		$sharepageTitle = str_replace(chr(13), " ", $sharepageTitle); //remove carriage returns
		$sharepageTitle = str_replace(chr(10), " ", $sharepageTitle); //remove line feeds 
		
		 
		
		// check privacy
		$isFriend = 1;
		if((int)$this->privacy == 1){
			if((int)$this->msgs[$i]->commenter_id != (int)$user->id)
				$isFriend = JsLib::isFriend($this->msgs[$i]->commenter_id, $user->id);			
		}
		
		// check rightcolumn css
		$rightColumnCss = 'rbroundboxright';

		$showLeft = true;
		if($this->msgs[$i]->type == 'alert'){
			$showLeft = false;
			$rightColumnCss = 'rbroundboxrightfull';
		}
		$group = $this->groupModel->getGroupInfo($this->msgs[$i]->group_id);
		$owner = $this->groupModel->checkGrpOwner($this->msgs[$i]->commenter_id, $this->msgs[$i]->group_id);
		// check if this is private group		
		$isPrivate = $this->groupModel->isPriaveGrp($this->msgs[$i]->group_id);
		if((int)$isPrivate){
			// check if viewer is member of this group
			$vowner = $this->groupModel->checkGrpOwner($user->id, $this->msgs[$i]->group_id);
			if(!(int)$vowner){
				$isMemberGrp = $this->groupModel->isMemberGrp($this->msgs[$i]->group_id, $user->id);
				if(!(int)$isMemberGrp)
					continue;
			}
		}
		
	$likeTxt = '';
	if($this->msgs[$i]->type == 'like'){
		$likeTxt = JText::sprintf('A LIKES B POST', '<a href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>', JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->user_id . '&Itemid=' . $Itemid, false), AwdwallHelperUser::getDisplayName($this->msgs[$i]->user_id));
	}
	if($this->msgs[$i]->type == 'friend'){ 
		$likeTxt = JText::sprintf('A AND B ARE FRIEND', '<a href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>',  '<a href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->user_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->user_id) . '</a>');
	}
	if($this->msgs[$i]->type == 'group'){ 
		$likeTxt = JText::sprintf('JOIN GROUP NEWS FEED', '<a href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>',  '<a href="' . JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->msgs[$i]->group_id . '&Itemid=' . $Itemid, false) . '">' . $group->title . '</a>');
	}

	if($this->display_profile_link==1)
	{
		$profilelink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $this->msgs[$i]->commenter_id . '&Itemid=' . AwdwallHelperUser::getJsItemId(), false);
	}
	else
	{
		$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id .'&Itemid=' . $Itemid, false);
	}
	
?>
<?php //if((int)$isFriend){?>
  <a name="here<?php echo $this->msgs[$i]->id;?>"></a>
  <div class="awdfullbox clearfix" id="msg_block_<?php echo $i;?>"><span class="tl"></span><span class="bl"></span>
 <?php if($showLeft){?>
    <div class="rbroundboxleft">
      <div class="mid_content">
	<?php if(!(int)$this->msgs[$i]->group_id){?>
	
	 <a href="<?php echo $profilelink;?>">
	  
<img src="<?php echo AwdwallHelperUser::getBigAvatar51($this->msgs[$i]->commenter_id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?>"  height="50" width="50"/>	<?php 
	if(AwdwallHelperUser::isSocialfeed($this->msgs[$i]->id)) 
	{?>
		<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo AwdwallHelperUser::isSocialfeed($this->msgs[$i]->id); ?>.png" class="post_type_icon"  />
	<?php }elseif(AwdwallHelperUser::isTweet($this->msgs[$i]->id)) {	?>
    		<img src="<?php echo JURI::base();?>components/com_awdwall/images/twitter.png" class="post_type_icon"  />
<?php } ?>

	  </a>
	<?php }else{
	if($owner){
	?>
	<a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->msgs[$i]->group_id. '&Itemid=' . $Itemid, false);?>">
		<img src="<?php echo AwdwallHelperUser::getBigGrpImg51($group->image, $group->id);?>" alt="<?php echo $group->title;?>" title="<?php echo $group->title;?>"/>
	</a>
	<?php }else{?>  
	<a href="<?php echo $profilelink;?>">
		<img src="<?php echo AwdwallHelperUser::getBigAvatar51($this->msgs[$i]->commenter_id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?>"  height="50" width="50" />	<?php 
	if(AwdwallHelperUser::isSocialfeed($this->msgs[$i]->id)) 
	{?>
		<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo AwdwallHelperUser::isSocialfeed($this->msgs[$i]->id); ?>.png" class="post_type_icon"  />
	<?php }elseif(AwdwallHelperUser::isTweet($this->msgs[$i]->id)) {	?>
    		<img src="<?php echo JURI::base();?>components/com_awdwall/images/twitter.png" class="post_type_icon"  />
<?php } ?>

	</a>
	<?php }?>  
	<?php }?>
	<br />
	<?php if(!$owner){ 
		if($this->display_profile_link==1){?>
	<a style="font-size:11px;" href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id .'&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Main wall profile');?></a>
	 <?php } 
	 }?>
	  </div>
	  
    </div>
<?php }?>
    <div class="<?php echo $rightColumnCss;?>"> <span class="tl2"></span><span class="bl2"></span><span class="tr2"></span><span class="br2"></span>
      <div class="right_mid_content">
	 <ul class="walltowall">
<?php if((int)$this->msgs[$i]->commenter_id != (int)$this->msgs[$i]->user_id && (int)$this->msgs[$i]->user_id){?>
	<li>
<?php
	$textWall = '';
	
	switch($this->msgs[$i]->type){
		case 'text':
			$textWall = 'A WRITE ON B WALL';
			break;
		case 'jing':
				$textWall = 'A ADDED JING ON B WALL';
			break;
		case 'event':
				$textWall = 'A ADDED EVENT ON B WALL';
			break;
		case 'image':
			$textWall = 'A ADDED PHOTO ON B WALL';
			break;
		case 'video':
			$textWall = 'A ADDED VIDEO ON B WALL';
			break;
		case 'trail':
			$textWall = 'A ADDED TRAIL ON B WALL';
			break;
		case 'link':
			$textWall = 'A ADDED LINK ON B WALL';
			break;
		case 'file':
			$textWall = 'A ADDED FILE ON B WALL';
			break;
		case 'mp3':
			$textWall = 'A ADDED SONG ON B WALL';
			break;
		case 'like':
			echo $likeTxt;
			break;
		case 'friend':
			echo $likeTxt;
			break;
		case 'group':
			echo $likeTxt;
			break;
			
		case 'tag':

			$textWall = '';

			break;
	}
	
	echo JText::sprintf($textWall, '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '" >' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>', '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->user_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->user_id) . "'s" . '</a>');
?>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);?></span></li>
<?php }else{ 
if($showLeft){
if(!(int)$this->msgs[$i]->group_id){
	if($this->msgs[$i]->type == 'like' || $this->msgs[$i]->type == 'friend' || $this->msgs[$i]->type == 'group'){
		echo '<li>' . $likeTxt . '</li>';
	}else{
?>
	<li><a style="font-size:12px;" href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false);?>"><?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?></a>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);?></span></li>
   
<?php } }else{
if($owner){
?>
<li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->msgs[$i]->group_id. '&Itemid=' . $Itemid, false);?>"><?php echo $group->title;?></a>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);?></span></li>
<?php }else{
?>
<li><?php echo JText::sprintf('A WRITE ON B GROUP', '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '" >' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>', '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->msgs[$i]->group_id . '&Itemid=' . $Itemid, false) . '" >' . $group->title . '</a>');?>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);?></span></li>
<?php }?>
<?php }?>
<?php }else {?>
	<li><span class="awdmessagetxt"><?php echo AwdwallHelperUser::showSmileyicons($this->msgs[$i]->message);?></span></li>
<?php }}?>
	</ul>

  <div class="commentinfo"> 
	
<!-- start link and video of text -->
	<?php if($this->msgs[$i]->type == 'text' && !$aPostB){?>
		<?php
				$query = "SELECT * FROM #__awd_wall_videos WHERE wall_id='".$this->msgs[$i]->id."'";
				$db->setQuery($query);
				$linkvideo = $db->loadObjectList();
				$video = $linkvideo[0];
			?>
			<?php if($video->id){?>

					<div class="whitebox video">
					<div style="overflow:hidden;" id="video_block_<?php echo $this->msgs[$i]->id;?>">
					<div class="clearfix">
					<div class="imagebox">
				<?php
				if($this->hwdvideoshare)
				{
					$query = "SELECT hwdviodeo_id FROM #__awd_wall_videos_hwd WHERE wall_id='".$this->msgs[$i]->id."'";
					$db->setQuery($query);
					$hwdviodeo_id = $db->loadResult();
					if($hwdviodeo_id)
					{
					$hwdlink=JRoute::_('index.php?option=com_hwdvideoshare&task=viewvideo&video_id='.$hwdviodeo_id.'&Itemid='.$Itemid);	
					?>
					<a href="<?php echo $hwdlink;?>"  title="" target="_self"><img src="<?php echo JURI::base();?>images/<?php echo $video->thumb; ?>" title="<?php echo $video->title; ?>" alt="<?php echo $video->title; ?>"  /></a>
					<?php
					}
					else
					{
					?>
					<img src="<?php echo JURI::base();?>images/<?php echo $video->thumb; ?>" title="<?php echo $video->title; ?>" alt="<?php echo $video->title; ?>"  />
					<?php
					}
				}
				else
				{
				?>
				<img src="<?php echo JURI::base();?>images/<?php echo $video->thumb; ?>" title="<?php echo $video->title; ?>" alt="<?php echo $video->title; ?>"  />
				<?php 
				} 
				?>				
					<br />
							<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-videos.png" title="<?php echo JText::_('View video');?>" alt="<?php echo JText::_('View video');?>" /> 
	<?php 
    
        if($this->videoLightbox){
            if($video->type == 'youtube'){
    ?>
        <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://www.youtube.com/watch?v=<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    <?php
            }elseif($video->type == 'vimeo'){
    ?>
        <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://vimeo.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    <?php		
            }elseif($video->type == 'myspace'){
    ?>
        <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://myspace.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    <?php
            }elseif($video->type == 'metacafe'){
    ?>
        <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://metacafe.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    <?php
            }elseif($video->type == 'howcast'){
    ?>
        <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://howcast.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    <?php
        }else if($video->type == 'blip'){
    ?>
    <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://a.blip.tv/scripts/flash/stratos.swf#file=http://blip.tv/rss/flash/<?php echo $video->video_id;?>&autostart=true&showinfo=false', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    <?php
    }
    else
    {
    ?>
    <a href="<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $this->msgs[$i]->id;?>&iframe=true&tmpl=component" rel="prettyPhoto[iframes<?php echo $this->msgs[$i]->id;?>]" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    }
        }else{	
            if($video->type == 'youtube'){
    
    ?>
        <a class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.youtube.com/v/<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
        
    <?php
    
            }elseif($video->type == 'vimeo'){	
    ?>
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://vimeo.com/moogaloop.swf?clip_id=<?php echo $video->video_id?>&amp;autoplay=1" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php		
    
            }elseif($video->type == 'myspace'){
    ?>	
    
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://mediaservices.myspace.com/services/media/embed.aspx/m=<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }elseif($video->type == 'metacafe'){
    
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.metacafe.com/fplayer/<?php echo $video->video_id;?>.swf" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }elseif($video->type == 'howcast'){
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.howcast.com/flash/howcast_player.swf?file=<?php echo $video->video_id;?>&amp;theme=black" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
    
            }elseif($video->type == 'blip'){
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://a.blip.tv/scripts/flash/stratos.swf#file=http://blip.tv/rss/flash/<?php echo $video->video_id;?>&autostart=true&showinfo=false" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }else
            {
        ?>
    <a href="javascript:void(0);" onclick="showlinevideo('<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $this->msgs[$i]->id;?>&tmpl=componen','<?php echo $this->msgs[$i]->id;?>')" ><?php echo JText::_('View video');?></a>
        
        <?php	
            
            }	
                
        
        }
    ?>		 
					</div>
							<div class="maincomment">
							  <h3><?php echo $video->title;?></h3>
							  <p><?php echo substr($video->description,0,200);?></p>
							  <font color="#9997ac"><?php echo JText::_('Length');?>: </font><?php echo AwdwallHelperUser::formatDuration((int)($video->duration), 'HH:MM:SS');?>

							  </div>			  
					  </div>

					<div style="width:455px; float: left; margin-top: 16px;" id="video_<?php echo $this->msgs[$i]->id;?>"></div>

					</div>
				</div>
			<?php } ?>


			<!-- start link block -->
			<?php 
				$query = "SELECT * FROM #__awd_wall_links WHERE wall_id='".$this->msgs[$i]->id."'";
				$db->setQuery($query);
				$checklink = $db->loadObjectList();
				$link = $checklink[0];
				$str=$link->link;
				$str=str_replace('www.','',$str);
				$str=str_replace('http://','',$str);
				$str='http://www.'.$str;
				// $link->link=$str;
			?>
			<?php if($link->id){?>
				<div class="whitebox video">
					<div id="video_block_<?php echo $this->msgs[$i]->id;?>">
					<div class="clearfix">
					<?php if($link->path != ''){?>
						<div class="imagebox">
							<img src="<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $link->path;?>" title="<?php echo $link->title;?>" alt="<?php echo $link->description;?>"  /> <br />
							<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /> 
							<?php if($this->imageLightbox){ ?>
								<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/original/<?php echo $link->path;?>', '<?php echo $link->name;?>', '<?php echo $link->description;?>');" title="<?php echo $link->name;?>"><?php echo JText::_('View link');?></a>
							<?php } else echo '<a href="' . JRoute::_('index.php?option=com_awdwall&task=viewimage&wuid=' . $this->msgs[$i]->user_id . '&imageid=' . $link->id . '&Itemid=' . $Itemid) . '" >' . JText::_('View link') . '</a>'; ?>		 
						</div>
						<div class="maincomment">
							<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->title;?></a></h3>
							<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->link;?></a></h3>		<p><?php echo $link->description;?></p>             
						</div>
					<?php }else{ ?>
						<div class="maincomment_noimg">
							<?php if($link->link_img){?>
							<div class="maincomment_noimg_left">
								<a target="blank" href="<?php echo $link->link;?>" alt=""><img src="<?php echo $link->link_img;?>" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /></a>
							</div>
							<?php } ?>
							<div class="maincomment_noimg_right">
								<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->title;?></a></h3>
								<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->link;?></a></h3>
								<p><?php echo $link->description;?></p>             
							</div>
							<?php if($link->title){?>
							<div style=" margin-top:3px; width:100%; clear:both;"><div style="float:left; width:5%;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('View link');?>" align="absmiddle" alt="<?php echo JText::_('View link');?>" style="height:20px; width:20px; clear:right;" /></div><div style=" float:left; margin-left:5px; width:90%;"><a href="<?php echo $link->link;?>" target="blank" style="margin:0px; padding:0; font-size:9px; font-weight:normal;"><?php echo $link->title;?></a></div></div>
							<?php } ?>
						</div>
				<?php }?>
					</div>
					</div>
				</div>
			<?php }?>
			<!-- end link block -->
				<?php } ?>
	<!-- end link and video of text -->	
	
	<!-- start video block -->

<?php if($this->msgs[$i]->type == 'video'){?>

	<div class="whitebox video">

	<div style="overflow:hidden;" id="video_block_<?php echo $this->msgs[$i]->id;?>">

	<div class="clearfix">

            <div class="imagebox">
<?php

if($this->hwdvideoshare)
{
	$query = "SELECT hwdviodeo_id FROM #__awd_wall_videos_hwd WHERE wall_id='".$this->msgs[$i]->id."'";
	$db->setQuery($query);
	$hwdviodeo_id = $db->loadResult();
	if($hwdviodeo_id)
	{
	$hwdlink=JRoute::_('index.php?option=com_hwdvideoshare&task=viewvideo&video_id='.$hwdviodeo_id.'&Itemid='.$Itemid);	
	?>
	<a href="<?php echo $hwdlink;?>"  title="" target="_self"><img src="<?php echo JURI::base();?>images/<?php echo $video->thumb; ?>" title="<?php echo $video->title; ?>" alt="<?php echo $video->title; ?>"  /></a>
	<?php
	}
	else
	{
	?>
	<img src="<?php echo JURI::base();?>images/<?php echo $video->thumb; ?>" title="<?php echo $video->title; ?>" alt="<?php echo $video->title; ?>"  />
	<?php
	}
}
else
{
?>
<img src="<?php echo JURI::base();?>images/<?php echo $video->thumb; ?>" title="<?php echo $video->title; ?>" alt="<?php echo $video->title; ?>"  />

<?php 
} 
?>		
<br />

            <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-videos.png" title="<?php echo JText::_('View video');?>" alt="<?php echo JText::_('View video');?>" /> 

<?php 

	if($this->videoLightbox){
		if($video->type == 'youtube'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://www.youtube.com/watch?v=<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'vimeo'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://vimeo.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php		
		}elseif($video->type == 'myspace'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://myspace.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'metacafe'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://metacafe.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
		}elseif($video->type == 'howcast'){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://howcast.com/<?php echo $video->video_id;?>', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
	}else if($video->type == 'blip'){
?>
<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('http://a.blip.tv/scripts/flash/stratos.swf#file=http://blip.tv/rss/flash/<?php echo $video->video_id;?>&autostart=true&showinfo=false', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
<?php
}
else
{
?>
<a href="<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $this->msgs[$i]->id;?>&iframe=true&tmpl=component" rel="prettyPhoto[iframes]" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>

<?php
}
	}else{	
		if($video->type == 'youtube'){

?>
	<a class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.youtube.com/v/<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
	
<?php

		}elseif($video->type == 'vimeo'){	
?>
	<a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://vimeo.com/moogaloop.swf?clip_id=<?php echo $video->video_id?>&amp;autoplay=1" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>

<?php		

		}elseif($video->type == 'myspace'){
?>	

	<a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://mediaservices.myspace.com/services/media/embed.aspx/m=<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>

<?php

		}elseif($video->type == 'metacafe'){

?>	
	<a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.metacafe.com/fplayer/<?php echo $video->video_id;?>.swf" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>

<?php

		}elseif($video->type == 'howcast'){
?>	
	<a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.howcast.com/flash/howcast_player.swf?file=<?php echo $video->video_id;?>&amp;theme=black" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>

<?php


		}elseif($video->type == 'blip'){
?>	
	<a  class="show_vide" rev="video_<?php echo $this->msgs[$i]->id;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://a.blip.tv/scripts/flash/stratos.swf#file=http://blip.tv/rss/flash/<?php echo $video->video_id;?>&autostart=true&showinfo=false" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>

<?php

		}else
		{
	?>
<a href="javascript:void(0);" onclick="showlinevideo('<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $this->msgs[$i]->id;?>&tmpl=componen','<?php echo $this->msgs[$i]->id;?>')" ><?php echo JText::_('View video');?></a>
    
    <?php	
		
		}	
			
	
	}
?>		 

	</div>

            <div class="maincomment">

              <h3><?php echo $video->title;?></h3>

              <p><?php echo substr($video->description,0,200);?></p>

              <font color="#9997ac"><?php echo JText::_('Length');?>: </font><?php echo AwdwallHelperUser::formatDuration((int)($video->duration), 'HH:MM:SS');?></div>			  

      </div>
	<div style="width:455px; float: left; margin-top: 16px;" id="video_<?php echo $this->msgs[$i]->id;?>"></div>
	</div>

</div>

<?php }?>

<!-- end video block -->
<!-- start image block -->
<?php if($this->msgs[$i]->type == 'image'){?>

<div class="whitebox video">

	<div id="video_block_<?php echo $this->msgs[$i]->id;?>">

	<div class="clearfix">
			
            <div class="imagebox">
			<?php
			$pid=$image->id;
			$image_uid=$this->msgs[$i]->user_id;
			//$imglink=JRoute::_('index.php?option=com_awdjomalbum&view=awdwallimagelist&wuid='.$image_uid.'&pid='.$pid.'&Itemid='.$Itemid,false);	//echo $imglink;
			$imglink=JRoute::_("index.php?option=com_awdjomalbum&view=awdwallimagelist&tmpl=component&wuid=".$image_uid."&pid=".$pid."&Itemid=".AwdwallHelperUser::getComItemId());

			if($this->jomalbumexist)
			{?>
			<a href="<?php echo $imglink;?>" class="awdiframe">
				<img src="<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $image->path;?>" title="<?php echo $image->name;?>" alt="<?php echo $image->name;?>" /> <br />
			</a>
			<?php }
			else {?>
            <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/original/<?php echo $image->path;?>', '<?php echo $image->name;?>', '<?php echo $image->name;?>');" title="<?php echo $image->name;?>">
				<img src="<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $image->path;?>" title="<?php echo $image->name;?>" alt="<?php echo $image->name;?>" /> <br />
                </a>
			<?php }?>
	</div>

		<div class="maincomment">

		  <h3><?php echo $image->name;?></h3>

		  <p><?php echo $image->description;?></p>             

		 </div>

	</div>

	</div>

</div>

<?php }?>
<!-- end image block -->
<!-- start link block -->
<?php if($this->msgs[$i]->type == 'link'){
				$str=$link->link;
				$str=str_replace('www.','',$str);
				$str=str_replace('http://','',$str);
				$str=str_replace('https://','',$str);
				$str='http://www.'.$str;
				// $link->link=$str;
?>

<div class="whitebox video">

	<div id="video_block_<?php echo $this->msgs[$i]->id;?>">

	<div class="clearfix">

<?php if($link->path != ''){?>

        <div class="imagebox"><img src="<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $link->path;?>" title="<?php echo $link->title;?>" alt="<?php echo $link->description;?>"  /> <br />

            <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /> 

<?php 

	if($this->imageLightbox){

?>

<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/original/<?php echo $link->path;?>', '<?php echo $link->name;?>', '<?php echo $link->description;?>');" title="<?php echo $link->name;?>"><?php echo JText::_('View link');?></a>

<?php

	}else

		echo '<a href="' . JRoute::_('index.php?option=com_awdwall&task=viewimage&wuid=' . $this->msgs[$i]->user_id . '&imageid=' . $link->id . '&Itemid=' . $Itemid, false) . '" >' . JText::_('View link') . '</a>';

?>		 

	</div>

	<div class="maincomment">

		<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->title;?></a></h3>
		<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->link;?></a></h3>
		<p><?php echo $link->description;?></p>             

	</div>

<?php }else{ ?>

	<div class="maincomment_noimg">
			<?php if($link->link_img){?>
			<div class="maincomment_noimg_left">
				<a target="blank" href="<?php echo $link->link;?>" alt=""><img src="<?php echo $link->link_img;?>" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /></a>
			</div>
			<?php } ?>
			
			<div class="maincomment_noimg_right">
				<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->title;?></a></h3>
				<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->link;?></a></h3>
				<p><?php echo $link->description;?></p>             
			</div>
<?php if($link->title){?>
<div style="line-height:20px; margin-top:3px; width:100%; clear:both;"><div style="float:left; width:5%;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('View link');?>" align="absmiddle" alt="<?php echo JText::_('View link');?>" style="height:20px; width:20px; clear:right;" /></div><div style=" float:left; margin-left:5px; width:90%;"><a href="<?php echo $link->link;?>" target="blank" style="margin:0px; padding:0; font-size:9px; font-weight:normal;"><?php echo $link->title;?></a></div></div>
<?php } ?>
			
		</div>	

<?php }?>

	</div>

	</div>

</div>

<?php }?>
<!-- end link block -->
<!-- start mp3 block -->
<?php if($this->msgs[$i]->type == 'mp3'){

		$parsedVideoLink	= parse_url($mp3->path);
		preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $parsedVideoLink['host'], $matches);
		$domain		= $matches['domain'];
		
		if($domain!='soundcloud.com')
		{
?>
<div class="whitebox video">

	<div id="video_block_<?php echo $this->msgs[$i]->id;?>">

	<div class="clearfix">
	<div class="title-mus">
		<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-music.png" title="<?php echo JText::_('Music');?>" alt="<?php echo JText::_('Music');?>" style="float:left;" />
		<h3 style="font-size:13px;font-weight:bold;margin:0;padding:3px 0px 6px 5px;"> &nbsp;<?php echo $mp3->title;?></h3>
	</div>
    <div class="imagebox">
		<object width="200" height="24" id="audioplayer1" data="<?php echo JURI::base();?>components/com_awdwall/js/player.swf" type="application/x-shockwave-flash">

<param value="<?php echo JURI::base();?>components/com_awdwall/js/player.swf" name="movie">

<param value="playerID=1&amp;soundFile=<?php echo JURI::base();?>images/mp3/<?php echo $this->msgs[$i]->user_id;?>/<?php echo $mp3->path;?>" name="FlashVars">

<param value="high" name="quality">

<param value="true" name="menu">

<param value="transparent" name="wmode">

</object><br />

	</div>

		<div class="maincomment">		  		

		 </div>

	</div>

	</div>

</div>
<?php
		}
		else
		{
			$videoWidth='400' ;
			$player_height='81';
			$auto_play='false';
			$show_comments='false';
			$color='#ff7700';
			$theme_color='#CCCCCC';
			$url=urlencode($mp3->path);
			$embed = '<object height="'.$player_height.'" width="'.$videoWidth.'">
			<param name="movie" value="http://player.soundcloud.com/player.swf?url='.$url.'&amp;g=bb&amp;auto_play='.$auto_play.'&amp;show_comments='.$show_comments.'&amp;color='.$color.'&amp;theme_color='.$theme_color.'">
			</param>
			<param name="allowscriptaccess" value="always">
			</param>
			<embed allowscriptaccess="always" height="'.$player_height.'" src="http://player.soundcloud.com/player.swf?url='.$url.'&amp;g=bb&amp;auto_play='.$auto_play.'&amp;show_comments='.$show_comments.'&amp;color='.$color.'&amp;theme_color='.$theme_color.'" type="application/x-shockwave-flash" width="'.$videoWidth.'">
			</embed>
			</object>';
		echo $embed;
		}
?>



<?php }?>
<!-- end mp3 block -->
<!-- start of jing block -->
<?php 
if($this->msgs[$i]->type == 'jing')
{
?>
<?php /*?><script language="javascript">
getJingData('<?php echo $jing->id;?>');
</script>
<div id="jing<?php echo $jing->id;?>"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" title="<?php echo JText::_('Loading');?>" alt="<?php echo JText::_('Loading');?>" border="0" /></div>
<br />
<a href="<?php echo $jing->jing_link;?>" title="<?php echo $jing->jing_title;?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-jing.png" title="<?php echo JText::_('Jing');?>" alt="<?php echo JText::_('Jing');?>" border="0" height="14" />&nbsp;&nbsp;&nbsp;<?php echo JText::_('View Jing');?></a>
<?php */?>
<div class="whitebox video">
	<div style="overflow:hidden;" id="jing_block_<?php echo $this->msgs[$i]->id;?>">
	<div class="clearfix">
            <div class="imagebox" >
			<script language="javascript">
			getJingThumbData('<?php echo $jing->id;?>');
			</script>
			<div id="sceenthumb_<?php echo $jing->id;?>">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></div>
			 <br />
            <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-jing.png" title="<?php echo JText::_('View Jing');?>" alt="<?php echo JText::_('View Jing');?>" /> 
<?php 
	if($this->jingLightbox){
?>
	<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo $jing->jing_link;?>', '<?php echo $jing->jing_title;?>', '<?php echo $jing->jing_title;?>')" title="<?php echo $jing->jing_title;?>"><?php echo JText::_('View Jing');?></a>
<?php
	}else{	
?>
	<a class="show_vide"  alt="<?php echo $jing->jing_title;?>" onclick="showjing(<?php echo $jing->id;?>);" href="javascript:void(0);" title="<?php echo $jing->jing_title;?>"><?php echo JText::_('View Jing');?></a>
	
<?php
	}
?>		 
	</div>
            <div class="maincomment">
              <h3><?php echo $jing->jing_title;?></h3>
			  <h3><a href="<?php echo $jing->jing_link;?>" target="blank"><?php echo $jing->jing_link;?></a></h3>
              <p><?php echo nl2br(substr($jing->jing_description,0,200));?></p>
              </div>			  
      </div>
	<div style="width:455px; float: left; margin-top: 16px;display:none;" id="jingp_<?php echo $jing->id;?>" >
	&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" />
	</div>

	</div>
</div>


<?php 
}
?>
<!-- end of jing block -->
<!-- start event block -->

<?php if($this->msgs[$i]->type == 'event')
{
$startd=explode("\n",$event->start_time);
$endd=explode("\n",$event->end_time);
$starttime=$startd[1].' '.$startd[2].' '.date('l, j-M-y',strtotime($startd[0]));
$endtime=$endd[1].' '.$endd[2].' '.date('l, j-M-y',strtotime($endd[0]));
?>

<div class="whitebox event">

	<div id="event_block_<?php echo $this->msgs[$i]->id;?>">

	<div class="clearfix">

    <div class="imagebox" style="margin-top:10px; margin-bottom:10px;">
	
<?php
	if($event->image)
	{?>
	<img src="<?php echo JURI::base();?>images/awd_events/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $event->image;?>" title="<?php echo $event->title;?>" alt="<?php echo $event->title;?>" /></a>
<?php }
	else {?>
	<img src="<?php echo JURI::base();?>components/com_awdwall/images/event.png" title="<?php echo $event->title;?>" alt="<?php echo $event->title;?>" />
	<?php }?>

	</div>
	<?php
	
	?>
		<div class="maincomment" style="margin-top:10px;">

			<h3><?php echo $event->title;?></h3>
			<p><font><?php echo JText::_('Location');?>: </font><?php echo $event->location;?></p>   
			<font><?php echo JText::_('Time');?>: </font>  
			<p><font><?php echo JText::_('Start');?>: </font><?php echo $starttime;?><br />
			<font><?php echo JText::_('End');?>: &nbsp;</font><?php echo $endtime;?></p>   
			
			<p><?php echo nl2br($event->description);?></p>  
			
<?php
	$canAttend = $this->wallModel->getEventOfMsgOfUser($this->msgs[$i]->id,$user->id);
	if(!$canAttend){
?>
			<p><?php echo JText::_('Are you coming ?');?>
			<select name="attend_event" id="attend_event" onchange="attendEvent('<?php echo 
 'index.php?option=com_awdwall&view=awdwall&task=addEventAttend&wid=' . (int)$this->msgs[$i]->id . '&cid=' .(int)$this->msgs[$i]->commenter_id.'&eventId=' . (int)$event->id .'&tmpl=component';?>', '<?php echo  'index.php?option=com_awdwall&view=awdwall&task=getEventAttend&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component&Itemid='.$Itemid;?>',<?php echo $this->msgs[$i]->id;?>);">
				<option value=""><?php echo JText::_('Select');?></option>
				<option value="1"><?php echo JText::_('JYES');?></option>
				<option value="0"><?php echo JText::_('JNO');?></option>
			</select>
			</p>  
<?php }?>
		 </div>

	</div>

	</div>

</div>

<?php }?>

<!-- end event block -->

<!-- start article block -->

<?php if($this->msgs[$i]->type == 'article')
{?>

<div class="whitebox article">

	<div id="article_block_<?php echo $this->msgs[$i]->id;?>">

	<div class="clearfix">
	
<?php
	if($article->image)
	{?>
    <div class="imagebox" style="margin-top:10px; margin-bottom:10px;">
	
	<a href="<?php echo JURI::base();?>index.php?option=com_content&view=article&id=<?php echo $article->article_id;?>&Itemid=<?php echo $Itemid;?>" >
		<img src="<?php echo JURI::base();?>images/awd_articles/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $article->image;?>" title="<?php echo $article->title;?>" alt="<?php echo $article->title;?>" style="max-height:84px; max-width:112px;" />
	</a>

	</div>

		<div class="maincomment" style="margin-top:10px;">
			<a href="<?php echo JURI::base();?>index.php?option=com_content&view=article&id=<?php echo $article->article_id;?>&Itemid=<?php echo $Itemid;?>" ><h3><?php echo $article->title;?></h3></a>
			
			<p>
			<?php 
			$loadjomwall='{loadjomwall}';
			//echo $loadjomwall;
			if(strpos($article->description,$loadjomwall)!==false) {
				echo str_replace($loadjomwall," ",$article->description);
			}
			else {
				echo $article->description;
			}?></p>  
		 </div>

<?php } else {?>
	
		<div class="maincomment" style="margin-top:10px; float:left; width:98%">
			<a href="<?php echo JURI::base();?>index.php?option=com_content&view=article&id=<?php echo $article->article_id;?>&Itemid=<?php echo $Itemid;?>" ><h3><?php echo $article->title;?></h3></a>
			
			<p><br />
			<?php 
			$loadjomwall='{loadjomwall}';
			//echo $loadjomwall;
			if(strpos($article->description,$loadjomwall)!==false) {
				echo str_replace($loadjomwall," ",$article->description);
			}
			else {
				echo $article->description;
			}?></p>  
	</div>
	<?php }?>
	</div>
	</div>

</div>

<?php }?>

<!-- end article block -->

<!-- start of trail block -->
<?php 
if($this->msgs[$i]->type == 'trail')
{
?>
<a href="<?php echo $trail->trail_link;?>" target="_blank" style="font-size:12px;"><?php echo $trail->trail_title;?></a><br><br><iframe src="http://www.everytrail.com/iframe2.php?trip_id=<?php echo str_replace('http://www.everytrail.com/view_trip.php?trip_id=','',$trail->trail_link);?>&width=400&height=300" marginheight="0" marginwidth="0" frameborder="0" scrolling="no" width="400" height="300"></iframe>
<?php 
}
?>
<!-- end of trail block -->

<!-- start file block -->
<?php if($this->msgs[$i]->type == 'file'){?>
<div class="whitebox video">
	<div id="video_block_<?php echo $this->msgs[$i]->id;?>">
	<div class="clearfix">
    <div class="imagebox"><a href="<?php echo JURI::base();?>images/awdfiles/<?php echo $this->msgs[$i]->user_id;?>/<?php echo $file->path;?>" target="_blank">
	<img src="<?php echo JURI::base();?>components/com_awdwall/images/file.png" title="<?php echo JText::_('Download');?>" alt="<?php echo JText::_('Download');?>" /></a>
	<br /><span style="padding-left:12px;font-weight:bold;color:#308CB6;"><a href="<?php echo JURI::base();?>images/awdfiles/<?php echo $this->msgs[$i]->user_id;?>/<?php echo $file->path;?>" target="_blank"><?php echo JText::_('Download');?></a></span>
	</div>
		<div class="maincomment">
			<h3><?php echo $file->title;?></h3>
		 </div>
	</div>
	</div>
</div>
<?php }?>
<!-- end file block -->
<div style="padding:8px 0px;">
<span class="wall_date"><?php if(AwdwallHelperUser::isSocialfeed($this->msgs[$i]->id)) 
	{echo '<img src="components/com_awdwall/images/'.AwdwallHelperUser::isSocialfeed($this->msgs[$i]->id).'_date.png" />&nbsp;&nbsp;'.JText::_('via').'&nbsp;'.ucfirst(AwdwallHelperUser::isSocialfeed($this->msgs[$i]->id)).'&nbsp;&nbsp;';}
?><?php echo AwdwallHelperUser::getDisplayTime($this->msgs[$i]->wall_date);?> </span>&nbsp;&nbsp;
<?php if((int)$user->id && $showLeft){
?>	
	<a href="javascript:void(0);" onclick="showCommentBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getcbox&tmpl=component';?>', <?php echo $this->msgs[$i]->id;?>, <?php echo $this->msgs[$i]->commenter_id;?>, <?php echo $this->msgs[$i]->id;?>);" title="Comment"><?php echo JText::_('Comment');?></a> 
<?php
	$canlike = $this->wallModel->getLikeOfMsgOfUser($this->msgs[$i]->id,$user->id);
if($this->displayLike){
	if(!$canlike){
?>
	- 
	<a href="javascript:void(0);" onclick="openLikeMsgBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=addlikemsg&wid=' . (int)$this->msgs[$i]->id . '&cid=' .(int)$this->msgs[$i]->commenter_id.'&tmpl=component';?>', '<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo $this->msgs[$i]->id;?>');"><?php echo JText::_('Like');?></a>
<?php } 
}?>
<span id="wholike_box_<?php echo $this->msgs[$i]->id;?>">	
<?php
	// get who likes of message
	$whoLikes = $this->wallModel->getLikeOfMsg($this->msgs[$i]->id);
	if(isset($whoLikes[0])){
?>
<?php /*?>	- <a href="javascript:void(0);" onclick="getWhoLikeMsg('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component';?>', <?php echo $this->msgs[$i]->id;?>);"><?php echo JText::_('Who likes it');?></a>
<?php */?><?php }?>
</span>
<?php
$sharepageTitle = $this->msgs[$i]->message;
$sharepageTitle = addslashes(htmlspecialchars($sharepageTitle));
$sharepageTitle = str_replace(chr(13), " ", $sharepageTitle); //remove carriage returns
$sharepageTitle = str_replace(chr(10), " ", $sharepageTitle); //remove line feeds 
$facebooksharepageTitle=$sharepageTitle;
$facebookdesc=$sharepageTitle;
$imageurl='';
if($this->msgs[$i]->type == 'file'){
$imageurl=JURI::base().'components/com_awdwall/images/file.png';
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$pageTitle;
}
if($this->msgs[$i]->type == 'video'){
$imageurl=JURI::base().'images/'.$video->thumb;
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$video->description;
}
if($this->msgs[$i]->type == 'image'){
$imageurl=JURI::base().'images/'.$this->msgs[$i]->user_id.'/thumb/'.$image->path;
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$image->description;
}
if($this->msgs[$i]->type == 'link'){
 if($link->link_img != ''){
$imageurl=$link->link_img;
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$link->description;
}
}
if($this->msgs[$i]->type == 'event'){
if($event->image){
	$imageurl=JURI::base().'images/awd_events/'.$this->msgs[$i]->user_id.'/thumb/'.$event->image;
}
else
{
	$imageurl=JURI::base().'components/com_awdwall/images/event.png';
}
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$event->description;

}
if($fbshareaapid){
if(!empty($imageurl))
{
$facebookshareurl='https://www.facebook.com/dialog/feed?app_id='.$fbshareaapid.'&link='.urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid)).'&
  &name='.urlencode($facebooksharepageTitle).'&picture='.$imageurl.'&
  description='.urlencode(strip_tags($facebookdesc)).'&redirect_uri='.urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid));
}
else
{
$facebookshareurl='https://www.facebook.com/dialog/feed?app_id='.$fbshareaapid.'&link='.urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid)).'&
  &name='.urlencode($facebooksharepageTitle).'&
  description='.urlencode(strip_tags($facebookdesc)).'&redirect_uri='.urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid));
}}
?>

<?php if($this->displayShare){?>
	- <a class="ashare" rev="hid_<?php echo $this->msgs[$i]->id; ?>" rel="ashare_<?php echo $this->msgs[$i]->id; ?>" onmouseover="show_share();" href="javascript:void(0);"><?php echo JText::_('Share');?></a>
<?php }?>		
	<div class="ashare" style="display:none;" id="ashare_<?php echo $this->msgs[$i]->id; ?>">
		<div class="share-top"><div></div></div>
		<a href="javascript:void(0);" onclick="hidden_share();" style="float: right; font-weight: bold; color: rgb(170, 170, 170); margin-right: 7px;">X</a>
		<div class="share-center">
        <?php if($fbshareaapid){?>
			<a rel="nofollow" target="_blank"  href="<?php echo $facebookshareurl;?>" title="<?php echo JText::_('facebook');?>"><?php echo JText::_('Facebook');?></a>
			<br/>
            <?php } ?>
			<a rel="nofollow" target="_blank"  href="http://twitter.com/share?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid));?>&text=<?php echo urlencode($pageTitle);?>" title="<?php echo JText::_('twitter');?>"><?php echo JText::_('Twitter');?></a>
			<br/>
			<a rel="nofollow" target="_blank"  href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid));?>&title=<?php echo urlencode($pageTitle);?>" title="<?php echo JText::_('LinkedIn');?>"><?php echo JText::_('LinkedIn');?></a>
			<br/>
			<a target="_blank" href="https://plus.google.com/share?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid));?>"><?php echo JText::_('Google Plus');?></a>
			<br/>
			<a   target="_blank" href="http://digg.com/submit?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid));?>"><?php echo JText::_('Digg');?></a>
			<br/>
			<a  target="_blank" href="http://www.stumbleupon.com/submit?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid));?>&amp;title=<?php echo urlencode($pageTitle);?>"><?php echo JText::_('Stumbleupon');?></a>
		</div>
		<div class="share-bottom"><div></div></div>
	</div>
<?php if($this->displayPm){?>
	- <a href="javascript:void(0);" onclick="showPMBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getpmbox&tmpl=component';?>', <?php echo $this->msgs[$i]->id;?>, <?php echo $this->msgs[$i]->commenter_id;?>, <?php echo $this->msgs[$i]->id;?>);" title="<?php echo JText::_('PM');?>"><?php echo JText::_('PM');?></a>
<?php }?>
	<?php if((int)$user->id == (int)$this->msgs[$i]->commenter_id || $this->can_delete || in_array($user->id,$this->moderator_users)){?>
	- <a href="javascript:void(0);" onclick="openMsgDeleteBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=deletemsg&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component';?>', <?php echo $i;?>);"><?php echo JText::_('Delete');?></a> 
	<?php }?>
	<?php if(AwdwallHelperUser::checkOnline($this->msgs[$i]->commenter_id)){?>
	<img src="<?php echo AwdwallHelperUser::getOnlineIcon();?>" title="<?php echo JText::_('Online');?>" alt="<?php echo JText::_('Online');?>" style="vertical-align:middle;"/>
	<?php }?>	
	<?php }?><p></p>
	<!-- start like box -->
<div id="like_<?php echo $this->msgs[$i]->id;?>">
</div>
<?php
	$whoLikes = $this->wallModel->getLikeOfMsg($this->msgs[$i]->id);
	if(isset($whoLikes[0])){
?>
<script type="text/javascript">
getWhoLikeMsg('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component&Itemid='.$Itemid;?>', <?php echo $this->msgs[$i]->id;?>);
</script>
<?php } ?>

	<!-- end like box -->
	<!-- start event-attend box -->
<div id="event_<?php echo $this->msgs[$i]->id;?>">

</div>
<?php
	$whoAttends = $this->wallModel->getAttendOfMsg($this->msgs[$i]->id);
	
	if(isset($whoAttends[0])){
?>
<div id="event_<?php echo $this->msgs[$i]->id;?>" class="comment_text">

<span id="event_loader_<?php echo $this->msgs[$i]->id;?>" style="display:none;margin:10px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>

</div>
<script type="text/javascript">
<?php /*?>alert('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component&Itemid='.$Itemid;?>');
<?php */?>
getWhoAttendEvent('<?php echo 
'index.php?option=com_awdwall&view=awdwall&task=getEventAttend&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component&Itemid='.$Itemid;?>', <?php echo $this->msgs[$i]->id;?>);
</script>
<?php } ?>

	<!-- end event-attend box -->
	<!--start pm box -->
	<div id="pm_<?php echo $this->msgs[$i]->id;?>" class="comment_text">
	<span id="pm_loader_<?php echo $this->msgs[$i]->id;?>" style="display:none;margin:10px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
	</div>
	<!--end pm box -->
	<!--start comment box -->
	<div id="c_<?php echo $this->msgs[$i]->id;?>" class="comment_text">
	<span id="c_loader_<?php echo $this->msgs[$i]->id;?>" style="display:none;margin:10px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
	</div>
	<!--end comment box -->
</div>
<?php if((int)$isFriend){?>
<!--start comment block -->
<?php if($getrealtimecomment==1){?>
<script type="text/javascript">
	getrealtimecomment(<?php echo $this->msgs[$i]->id;?>);
</script>
<?php } ?>
<div id="c_content_<?php echo $this->msgs[$i]->id;?>">
<?php
	// get comments of message
	$cpage 		= 0;
	$coffset 	= $cpage*$this->commentLimit;
	$comments 	= $this->wallModel->getAllCommentOfMsg($this->commentLimit, $this->msgs[$i]->id, $coffset);
	$nofComments = $this->wallModel->countComment($this->msgs[$i]->id);
	if(isset($comments[0])){
		foreach($comments as $comment){		
		$commenter_id=$this->wallModel->getwallpostowner($this->msgs[$i]->id);
		$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $comment->commenter_id.'&Itemid=' . $Itemid, false);
?>
<div class="whitebox" id="c_block_<?php echo $comment->id;?>">
  <div class="clearfix">
    <div class="subcommentImagebox"><a href="<?php echo $profilelink;?>">
		<img src="<?php echo AwdwallHelperUser::getBigAvatar32($comment->commenter_id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($comment->commenter_id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($comment->commenter_id);?>"  height="32" width="32" class="awdpostavatar" />
	</a></div>
    <div class="subcomment">
      <div class="rbroundbox">
        <div class="rbtop">
          <div></div>
        </div>
        <div class="rbcontent">
			<a href="<?php echo $profilelink;?>" class="authorlink"><?php echo AwdwallHelperUser::getDisplayName($comment->commenter_id);?></a>&nbsp;&nbsp;<?php echo stripslashes(AwdwallHelperUser::showSmileyicons($comment->message));?>
          <div class="subcommentmenu"> 
		  <span class="wall_date"><?php echo AwdwallHelperUser::getDisplayTime($comment->wall_date);?></span>
        <span id="commentlike_<?php echo (int)$comment->id;?>">          
        <?php
		if((int)$user->id ) {
        $canlike = $this->wallModel->getLikeOfMsgOfUser($comment->id,$user->id);
        if($this->displayCommentLike){
            if(!$canlike){
        ?>
            &nbsp;&nbsp;
            <a href="javascript:void(0);" onclick="openLikeCommentBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=addlikemsg&wid=' . (int)$comment->id . '&cid=' .(int)$user->id.'&tmpl=component';?>', '<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$comment->id . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$comment->id;?>');"><?php echo JText::_('Like');?></a> &nbsp;&nbsp;
        <?php
            }
            else
            {
        ?>
            &nbsp;&nbsp;
            <a href="javascript:void(0);" onclick="deleteLikeCommentBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=deletelikemsg&wid=' . (int)$comment->id . '&cid=' .(int)$user->id.'&tmpl=component';?>', '<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$comment->id . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$comment->id;?>');"><?php echo JText::_('Unlike');?></a> &nbsp;&nbsp;
        <?php
            }
        }
		}
        ?>
         </span> 
	<?php
	if((int)$user->id ) {
        $whoLikes = $this->wallModel->getLikeOfMsg($comment->id);
        if(isset($whoLikes[0])){
    ?>
                <script type="text/javascript">
                getWhoLikeComment('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$comment->id . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$comment->id;?>','<?php echo (int)$comment->id;?>');
                </script>
    <?php 
        } }
    ?>
	<?php if((int)$user->id ) { if((int)$user->id == (int)$comment->commenter_id || $this->can_delete || in_array($user->id,$this->moderator_users)|| (int)$user->id == (int)$commenter_id){?>	  
		  &nbsp;&nbsp;
		  <a href="javascript:void(0);" onclick="openCommentDeleteBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=deletecomment&wid=' . (int)$comment->id . '&tmpl=component';?>', <?php echo $comment->id; ?>);"><?php echo JText::_('Delete');?></a> 
	<?php } } ?>	  
		  </div>
        </div>
        <div class="rbbot">
          <div></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php			
		}
	}
?>
</div>
<!-- start older comments-->
<?php
	if((($cpage + 1) * $this->commentLimit) < $nofComments){
?>
 <div class="lightblue_box"><a href="javascript:void(0);" onclick="getOlderComments('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getoldercomment&tmpl=component&wid=' . $this->msgs[$i]->id;?>', <?php echo $this->msgs[$i]->id;?>);"><?php echo JText::_('Older Comments');?></a>&nbsp;&nbsp;<span id="older_comments_loader_<?php echo $this->msgs[$i]->id;?>" style="display:none;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>

	<input id="awd_c_page_<?php echo $this->msgs[$i]->id;?>" name="awd_c_page_<?php echo $this->msgs[$i]->id;?>" type="hidden" value="<?php echo ($cpage + 1);?>" autocomplete="off"/>	
 </div>
<?php } ?>
	<!--end comment block-->
<?php }?>
		</div>
	  </div>
    </div>
  </div>
 <!-- end block msg -->
<?php 
}// end for
?>
<?php } // end if parent ?>
 </div>
</div><!-- end fullboxnew -->	
</div>
 </div>
 <!-- end msg content -->
 <?php
	if(((($this->page + 1) * $this->postLimit) < $this->nofMsgs) && (int)$user->id){
?>
 <div class="lightblue_box"><a href="javascript:void(0);" onclick="getOlderPosts('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getoldermsg&tmpl=component';?>');"><?php echo JText::_('Older Posts');?></a>&nbsp;&nbsp;<span id="older_posts_loader" style="display:none;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
	<input id="awd_page" name="awd_page" type="hidden" value="<?php echo ($this->page + 1);?>" autocomplete="off"/>
	<input id="task" name="task" type="hidden" value="<?php echo $this->task;?>" autocomplete="off" />
 </div>
<?php } ?>
 </div>
 
<div style="display: none;" id="awdlightbox-panel">
<div id="awdlightcontentbox"></div>
<p align="center;" style="clear:left; text-align:center;">
	<a id="close-panel" href="JavaScript::void(0)" onclick="closelistbox();"><?php echo JText::_('Close');?></a>
</p>
</div>
<div style="display: none;" id="awdlightbox"></div>
<?php if($template=='default'){?>
<script type="text/javascript">
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
var gg=tt-40+'px';
jQuery(".demo-container").css("width",gg);
jQuery("#drilldown-4").css("width",gg);
jQuery(".demo-container").children().css("width",gg);
jQuery("#drilldown-4").children().css("width",gg);
jQuery(".rbroundboxrighttop").css("width",new_number);

jQuery.browser.chrome = /chrome/.test(navigator.userAgent.toLowerCase()); 

if(jQuery.browser.chrome){
jQuery('#awd_message').css('width','99.8%'); 
jQuery('#awd_message').css('margin','0px'); 
jQuery('#awd_message').css('padding','0px'); 
jQuery('#awd-mainarea .post_msg_btn').css('width','98.8%'); 
}

};

var resizeTimer = null;
jQuery(window).bind('resize', function() {
    if (resizeTimer) clearTimeout(resizeTimer);
    resizeTimer = setTimeout(adjustwidth, 10);
});


</script>
<?php } ?>