<?php
/**
 * @version 2.5
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
$Itemid = AwdwallHelperUser::getComItemId();
$db =& JFactory::getDBO();

// get user object
$user = &JFactory::getUser();
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
$jingActive = '';
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
elseif($this->task == 'jing')
	$jingActive = 'class="active"';
elseif($this->task == 'pm')
	$pmActive = 'class="active"';
else
	$wallActive = 'class="active"';
// get jomosical Itemid
$jsItemid = AwdwallHelperUser::getJsItemId();
$friendJsUrl = 'index.php?com_comprofiler=&task=manageConnections&option=com_comprofiler&Itemid='. $Itemid;
//$config = &JComponentHelper::getParams('com_awdwall');
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
$width 		= $config->get('width', 750);
?>
<div  id="awd-mainarea" style="width:100%;">
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
	#awd-mainarea .mid_content_top, #awd-mainarea .rbroundboxright, #awd-mainarea .fullboxtop, #awd-mainarea .rbroundboxleft_user, #awd-mainarea .lightblue_box, #awd-mainarea .rbroundboxrighttop{
		background-color:#<?php echo $this->color[5]; ?>;
	}
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
	#awd-mainarea .whitebox, #awd-mainarea .blog_content, #awd-mainarea .about_me{
		background-color:#<?php echo $this->color[12]; ?>;
	}
	#awd-mainarea .wallheading, #awd-mainarea .wallheadingRight{
		background-color:#<?php echo $this->color[13]; ?>;
	}
	#awd-mainarea .round, #awd-mainarea .search_user{
		background-color:#<?php echo $this->color[14]; ?>;
	}
</style>
<script src="components/com_awdwall/js/selectcustomer.js"></script>
<script type="text/javascript">	
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
			showCustomer(src, wid_tmp);
		}
		else{
			var k = document.getElementById('hidden_img').getAttributeNode('value').value;
			var src = document.getElementById(k).getAttributeNode('src').value;
			var wid_tmp = document.getElementById('wid_tmp').value;
			showCustomer(src,wid_tmp);
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
		if(n == 0){
			document.getElementById("count_img_active").innerHTML = 0;
			return true;
		}
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
		showCustomer(src,wid_tmp);					
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
		if(n == 0){
			document.getElementById("count_img_active").innerHTML = 0;
			return true;
		}
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
		showCustomer(src, wid_tmp);
		
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
<script type="text/javascript">
		//show share
		function show_share(){
			getElementByClass_share('a', 'share');
		}
		function hidden_share(){
			getElementByClass_share('a', 'share');
			var att=document.getElementById(d).getAttributeNode('rel').value;
			document.getElementById(att).style.display = "none";
			getElementByClass_share('a', 'share');
		}
		function getElementByClass_share(node, class_name){
			var tag = document.getElementsByTagName(node);
			var getAgn = tag;
			class_name_click = class_name+'share';
			class_name_unclick = class_name+'share';
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
						if(this.className == 'shareclick')
							inser_share('a', 'share', 'shareclick', 'share');
						if(this.className == 'share')
							inser_share('a', 'share', 'shareclick', 'shareclick');	
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
  
  
 <div class="awdfullbox" style="width:100%;"> <span class="bl"></span>
    
    <div class="rbroundboxrighttop" style="width:100%;min-height:150px;"> <span class="bl2"></span><span class="br2"></span>
      <div class="user_place"> 
    <span class="profileName">
	<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->wuid . '&Itemid=' . $Itemid, false);?>">
     <?php if((int)$this->displayName == USERNAME) {?>
	<?php echo $userWall->username;?>
    <?php }else{?>
    <?php echo $userWall->name;?>
    <?php }?>
	</a>
    </span>&nbsp;
      <span class="profileStatus" id="awd_profile_status">
	  <?php
		if(isset($this->latestPost->message))
			echo $this->latestPost->message;		
	  ?>
	  </span>
     
        <ul class="tabProfile">
          <li <?php echo $wallActive;?>>
          <a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->wuid . '&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Wall');?></a></li>
		  <?php if((int)$this->displayVideo){?>
		  <li <?php echo $videoActive;?>><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->wuid . '&task=videos&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Videos');?></a></li>
		  <?php }?>
		   <?php if((int)$this->displayImage){?>
          <li <?php echo $imageActive;?>><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->wuid . '&task=images&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Images');?></a></li>
		  <?php }?>
		   <?php if((int)$this->displayMusic){?>
          <li <?php echo $mp3Active;?>><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->wuid . '&task=music&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Mp3');?></a></li>
		  <?php }?>
		  <?php if((int)$this->displayLink){?>
		  <li <?php echo $linkActive;?>><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->wuid . '&task=links&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Links');?></a></li>
		  <?php }?>
		  <?php if((int)$this->displayFile){?>
		  <li <?php echo $filesActive;?>><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->wuid . '&task=files&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Files');?></a></li>
		 <?php }?>
		<?php if((int)$this->displayTrail){?>
          <li <?php echo $trailActive;?>><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->wuid . '&task=trails&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Trails');?></a></li>
		  <?php }?>
          <li <?php echo $pmActive;?>><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->wuid . '&task=pm&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Private Message');?></a></li>
        </ul>
        <form name="frm_message" id="frm_message" method="post" action="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&task=addmsg', false);?>" onsubmit="return false;" enctype="multipart/form-data">
		<input type="hidden" name="wall_last_time" id="wall_last_time" value="<?php echo time();?>" />
		<input type="hidden" name="layout" id="layout" value="mywall" />
		<input type="hidden" name="posted_wid" id="posted_wid" value="" />
		<input type="hidden" name="awd_task" id="awd_task" value="<?php echo $this->task;?>" />
           <textarea  rows="" cols="" class="round" id="awd_message" name="awd_message"></textarea>
        <div class="post_msg_btn">
		 <?php if((int)$user->id){?>
			<input type="hidden" name="receiver_id" id="receiver_id" value="<?php echo $this->wuid;?>"/>
			<input type="hidden" name="layout" id="layout" value="mywall"/>
			<input type="hidden" name="wuid" id="wuid" value="<?php echo $this->wuid;?>"/>				
		
            <input class="postButton_small" value="Post"  type="submit" onclick="aPostMsg('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=addmsg&tmpl=component&layout=mywall&Itemid='.$Itemid;?>', '<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlatestpost&tmpl=component&layout=mywall&wuid=' . $this->wuid.'&Itemid='.$Itemid;?>');" />
			
			<ul class="attach">
          <li><?php echo JText::_('Attach');?>:</li>
		  <?php if((int)$this->displayVideo){?>
          <li><a href="javascript:void(0);" onclick="openVideoBox();" style="padding-top:2px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-videos.png" title="<?php echo JText::_('Videos');?>" alt="<?php echo JText::_('Videos');?>" border="0" /></a></li>
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
		  <li><a href="http://www.everytrail.com/" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-trails.png" title="<?php echo JText::_('Trails');?>" alt="<?php echo JText::_('Trails');?>" border="0" /></a></li>
		   <?php }?>
		<?php if((int)$this->displayJing){?>
		  <li><a href="javascript:void(0);" onclick="openJingBox();"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-jing.png" title="<?php echo JText::_('Jing');?>" alt="<?php echo JText::_('Jing');?>" border="0" /></a></li>
			<?php }?>
        </ul>
		 
		 <?php }else{?>
			<span class="login_post_text">&ldquo;<?php echo JText::_('You need to login to post comments');?>&rdquo;</span>
		 <?php }?>
		 </div>
               
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
		<div class="attach_link clearfix" style="display:none;" id="awd_video_form">
		<span class="close"><a href="javascript:void(0);" onclick="closeVideoBox();">x</a></span>
		<p><span style="float:left;"><?php echo JText::_('Video URL');?>:</span> <span style="float:right;padding-right:20px;">
		<ul style="list-style-type:none;margin:0px;padding:0px;padding-left:20px;" c>
            <li style="margin:0px;padding:0px;"><a href="http://www.youtube.com/" title="<?php echo JText::_('YouTube');?>" alt="<?php echo JText::_('YouTube');?>" target="_blank"><?php echo JText::_('YouTube');?></a></li><li>-</li>
			<li><a href="http://vids.myspace.com/" title="<?php echo JText::_('Myspace');?>" alt="<?php echo JText::_('Myspace');?>" target="_blank"><?php echo JText::_('Myspace');?></a></li><li>-</li>
			<li><a href="http://www.vimeo.com/" title="<?php echo JText::_('Vimeo');?>" alt="<?php echo JText::_('Vimeo');?>" target="_blank"><?php echo JText::_('Vimeo');?></a></li><li>-</li>
			<li><a href="http://www.metacafe.com/" title="<?php echo JText::_('Metacafe');?>" alt="<?php echo JText::_('Metacafe');?>" target="_blank"><?php echo JText::_('Metacafe');?></a></li><li>-</li>
			<li><a href="http://www.howcast.com/" title="<?php echo JText::_('Howcast');?>" alt="<?php echo JText::_('Howcast');?>" target="_blank"><?php echo JText::_('Howcast');?></a></li>
        </ul>
		</span>
	</p>
		 <p style="clear:both;margin-top:10px;text-align:center;"><input id="vLink" name="vLink" class="input_border" size="85%" /></p>
		 <br />
		<input type="hidden" name="awd_video_title" id="awd_video_title" value="" />
		 <input type="hidden" name="awd_video_desc" id="awd_video_desc" value="" />
		
		<span class="wr_buton_at uploadvideo">
		<input type="button" value=" <?php echo JText::_('Attach Video');?>" class="button" onclick="ajaxVideoUpload('<?php echo 'index.php?option=com_awdwall&task=addvideo&wuid=' . $this->wuid;?>')" />
		</span><a href="javascript:void(0);" onclick="closeVideoBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>
		<span style="display:none;padding:5px 0px !important;" id="video_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
        </div>
<!-- end video form -->
<!-- image form -->
		<div class="attach_link clearfix" style="display:none;" id="awd_image_form">
		 <span class="close"> <a href="javascript:void(0);" onclick="closeImageBox();">x</a></span>
		 <?php echo JText::_('Choose image to upload');?>:			 
		 <p><label style="padding-right:35px;float:left;padding-top:10px;"><?php echo JText::_('Image Name');?></label> <input type="text" name="awd_image_title" id="awd_image_title" class="input_border" size="40" maxlength="150"/></p>
		 <p><label style="padding-right:70px;float:left;padding-top:10px;"><?php echo JText::_('Image');?> </label><input type="file" id="awd_link_image" name="awd_link_image" class="input_border" size="40" />(<?php echo JText::sprintf('Image Ext Only', AwdwallHelperUser::getImageExt());?>)</p>
		 <p><label style="padding-right:7px;float:left;padding-top:10px;"><?php echo JText::_('Image Description');?></label>
		 <textarea id="awd_image_description" name="awd_image_description" cols="40" rows="4" class="input_border"></textarea>
		 </p>
		 <input type="hidden" name="awd_mp3_desc" id="awd_mp3_desc" value="" />
		 <br />
		<span class="wr_buton_at uploadcamera">
		<input type="button" value=" <?php echo JText::_('Upload Image');?>" class="button" onclick="ajaxImageUpload('<?php echo 'index.php?option=com_awdwall&task=addimage&wuid=' . $this->wuid;?>')" />
		</span><a href="javascript:void(0);" onclick="closeImageBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>
		<span style="display:none;padding:5px 0px !important;" id="image_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
        </div>
<!-- end image form -->
<!-- mp3 form -->
		<div class="attach_link clearfix" style="display:none;" id="awd_mp3_form">
		 <span class="close"> <a href="javascript:void(0);" onclick="closeMp3Box();">x</a></span>
		 <?php echo JText::_('Choose mp3 to upload');?>:
		 <p><label style="padding-right:14px;float:left;padding-top:10px;"><?php echo JText::_('Title');?> </label> <input type="text" name="awd_mp3_title" id="awd_mp3_title" class="input_border" size="48" maxlength="150" /></p>
		 <p><label style="padding-right:17px;float:left;padding-top:10px;"><?php echo JText::_('Url');?> </label><input type="file" id="awd_link_mp3" name="awd_link_mp3" size="50" class="input_border" /></p>
		 <input type="hidden" name="awd_mp3_desc" id="awd_mp3_desc" value="" />
		 <br />
		<span class="wr_buton_at uploadmp3">
		<input type="button" value=" <?php echo JText::_('Upload MP3');?>" class="button" onclick="ajaxMp3Upload('<?php echo 'index.php?option=com_awdwall&task=addmp3&wuid=' . $this->wuid;?>')" />
		</span><a href="javascript:void(0);" onclick="closeMp3Box();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>
		<span style="display:none;padding:5px 0px !important;" id="mp3_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
        </div>
<!-- end mp3 form -->
<!-- link form -->
		<div class="attach_link clearfix" style="display:none;" id="awd_link_form">
		 <span class="close"> <a href="javascript:void(0);" onclick="closeLinkBox();">x</a></span>
	<?php echo JText::_('Url');?>:		 		
		 <p style="text-align:center;"><input type="text" name="awd_link" value="http://" id="awd_link" size="85%" maxlength="150" class="input_border" /></p>
		 <input type="hidden" name="awd_link_title" id="awd_link_title" value="" />
		 <input type="hidden" name="awd_link_desc" id="awd_link_desc" value="" /> <br />
		<span class="wr_buton_at">
		<input type="button" value=" <?php echo JText::_('Attach Link');?>" class="button" onclick="ajaxLinkUpload('<?php echo 'index.php?option=com_awdwall&task=addlink&wuid=' . $this->wuid;?>')" />
		</span> <a href="javascript:void(0);" onclick="closeLinkBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a> 
<span style="display:none;padding:5px 0px !important;" id="link_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>        
        </div>
<!-- end link form -->
<!-- Jing form -->

		<div class="attach_link clearfix" style="display:none;" id="awd_jing_form">

		 <span class="close"> <a href="javascript:void(0);" onclick="closeJingBox();">x</a></span>

		 <?php echo JText::_('Jing screen and videos');?>:

		 <p><label style="padding-right:17px;float:left;padding-top:10px;padding-right:57px;"><?php echo JText::_('Title');?> </label> <input type="text" name="awd_jing_title" id="awd_jing_title" class="input_border" size="48" maxlength="150" /></p>

		 <p><label style="padding-right:17px;float:left;padding-top:10px;padding-right:55px;"><?php echo JText::_('Url');?> </label><input type="text" id="awd_jing_link" name="awd_jing_link" size="50" class="input_border" /></p>
		 <p><label style="padding-right:17px;float:left;padding-top:10px;"><?php echo JText::_('Description');?> </label><textarea id="awd_jing_description" name="awd_jing_description" cols="40" rows="4" class="input_border"></textarea></p>
		 

<br />

		<span class="wr_buton_at">

		<input type="button" value=" <?php echo JText::_('Attach Jing');?>" class="button" onclick="ajaxJingUpload('<?php echo  JURI::base().'index.php?option=com_awdwall&task=addjing&wuid=' . $this->wuid;?>')" />
</span> <a href="javascript:void(0);" onclick="closeJingBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a> 

<span style="display:none;padding:5px 0px !important;" id="jing_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>        

        </div>

<!-- end Jings form -->

<!-- file form -->
		<div class="attach_link clearfix" style="display:none;" id="awd_file_form">
		 <span class="close"> <a href="javascript:void(0);" onclick="closeFileBox();">x</a></span>
		<?php echo JText::_('Choose file to upload');?>:
		<p><label style="padding-right:10px;float:left;padding-top:10px;"><?php echo JText::_('Title');?> </label><input type="text" id="awd_file_title" name="awd_file_title" class="input_border" size="40" maxlength="150" /> </p>
		 <p><label style="padding-right:13px;float:left;padding-top:10px;"><?php echo JText::_('Url');?> </label><input type="file" id="awd_file_link" name="awd_file_link" class="input_border" size="40" /> (<?php echo JText::sprintf('File Ext Only', AwdwallHelperUser::getFileExt());?>)</p>
		 <input type="hidden" name="awd_mp3_desc" id="awd_mp3_desc" value="" />
		<br />
		<span class="wr_buton_at uploadfile">
		<input type="button" value=" <?php echo JText::_('Attach a File');?>" class="button" onclick="ajaxAwdFileUpload('<?php echo 'index.php?option=com_awdwall&task=addfile&wuid=' . $this->wuid;?>')" />
		</span> <a href="javascript:void(0);" onclick="closeFileBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>  
<span style="display:none;padding:5px 0px !important;" id="file_loading">&nbsp;&nbsp;<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>         
        </div>
<!-- end file form -->
<!-- pm form -->
		<div class="attach_link clearfix" style="display:none;" id="awd_pm_form">
		 <span class="close"> <a href="javascript:void(0);" onclick="closePmBox();">x</a></span>
		 <?php echo JText::_('Private Message');?>		
		 <span style="display:none;" id="pm_loading"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>		
		 <p><label style="padding-right:7px;float:left;padding-top:10px;"><?php echo JText::_('Text');?></label>
		 <textarea id="awd_pm_description" name="awd_pm_description" cols="40" rows="4" class="input_border"></textarea>
		 </p>
		<input type="hidden" name="awd_pm_receiver_id" id="awd_pm_receiver_id" value="<?php echo $this->wuid;?>"/>
		<br />
		<span class="">
		<a href="javascript:void(0);" onclick="ajaxPmUpload('<?php echo 'index.php?option=com_awdwall&task=addpmuser&wuid=' . $this->wuid;?>');" class="button_cancel"> <span>&nbsp;&nbsp;&nbsp;<?php echo JText::_('PM');?>&nbsp;&nbsp;&nbsp;</span></a>
		</span> <a href="javascript:void(0);" onclick="closePmBox();" class="button_cancel"> <span><?php echo JText::_('Cancel');?></span></a>        
        </div>
<!-- end pm form -->
		<input type="hidden" id="wid_tmp" name="wid_tmp" value="" />
		<input type="hidden" id="type" name="type" value="text" />
		</form>
      </div>
 <div class="fullboxnew" >
 <!-- start msg content --> 
 <span id="msg_loader"></span>
 <div id="msg_content">
 <!-- start block msg -->
<?php 
if(isset($this->msgs[0]) && $this->showPosts){
	$n = count($this->msgs);
	for($i = 0; $i < $n; $i++){
		
		$pmText = '';
		if((int)$this->msgs[$i]->is_pm && ((int)$user->id == (int)$this->msgs[$i]->user_id || (int)$user->id == (int)$this->msgs[$i]->commenter_id)){
			$pmText = '<div class="pm_text">' . JText::_('PM From') . ' </div>';
		}else{
			$pmText = '';
		}
		
		$video = null;
		$pageTitle = $this->msgs[$i]->message;
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
		if($this->msgs[$i]->type == 'jing'){
			$jing  = $this->wallModel->getJingInfoByWid($this->msgs[$i]->id);
			$pageTitle = $jing ->jing_title;
		}
		$pageTitle = addslashes(htmlspecialchars($pageTitle));
		$pageTitle = str_replace(chr(13), " ", $pageTitle); //remove carriage returns
		$pageTitle = str_replace(chr(10), " ", $pageTitle); //remove line feeds 
		
		// check rightcolumn css
		$rightColumnCss = 'rbroundboxright';
		$aPostB = false;
		if((int)$this->msgs[$i]->commenter_id != (int)$this->msgs[$i]->user_id && (int)$this->msgs[$i]->commenter_id == $this->wuid && !in_array($this->task, $this->arrTask) && !(int)$this->msgs[$i]->is_pm){
			$aPostB = true;
			$rightColumnCss = 'rbroundboxrightfull';
		}
		// check if viewer is member of this group
		
		$vowner = $this->groupModel->checkGrpOwner($this->wuid, $this->msgs[$i]->group_id);
		if((int)$vowner){
			$aPostB = true;
			$rightColumnCss = 'rbroundboxrightfull';
		}
		if($this->msgs[$i]->group_id != NULL && ($this->wuid == $this->msgs[$i]->commenter_id)){
			$aPostB = true;
			$rightColumnCss = 'rbroundboxrightfull';
		}
		/*
		if($this->msgs[$i]->group_id != NULL){
			$aPostB = true;
			$rightColumnCss = 'rbroundboxrightfull';
		}
		*/
		$group = $this->groupModel->getGroupInfo($this->msgs[$i]->group_id);
		$likeTxt = '';
		if($this->msgs[$i]->type == 'like'){
			$likeTxt = JText::sprintf('A LIKES B POST', '<a href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>', JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->user_id . '&Itemid=' . $Itemid, false), AwdwallHelperUser::getDisplayName($this->msgs[$i]->user_id));
		}
		if($this->msgs[$i]->type == 'friend'){ 
		$likeTxt = JText::sprintf('A AND B ARE FRIEND', '<a href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>',  '<a href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->user_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->user_id) . '</a>');		
	}
	if($this->msgs[$i]->type == 'group'){ 
		$likeTxt = JText::sprintf('JOIN GROUP NEWS FEED', '<a href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>',  '<a href="' . JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->msgs[$i]->group_id . '&Itemid=' . $Itemid, false) . '">' . $group->title . '</a>');
	}
	
	//filter video
	if($_GET['task'] == 'videos'){
		if($rightColumnCss == 'rbroundboxrightfull'){
			continue;
		}
	}
?>  <a name="here<?php echo $this->msgs[$i]->id;?>"></a>
  <div class="awdfullbox clearfix" id="msg_block_<?php echo $i;?>"><span class="tl"></span><span class="bl"></span>
  <?php if(!$aPostB){ ?>
    <div class="rbroundboxleft">
      <div class="mid_content"><a href="<?php echo JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $this->msgs[$i]->commenter_id . '&Itemid=' . AwdwallHelperUser::getJsItemId(), false);?>"><img src="<?php echo JURI::base();?>components/com_awdwall/libraries/phpthumb.php?src=<?php echo AwdwallHelperUser::getBigAvatar($this->msgs[$i]->commenter_id);?>&w=51&h=51" alt="<?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?>" />
	<?php 
	if(AwdwallHelperUser::isTweet($this->msgs[$i]->id)) 
	{?>
		<img src="<?php echo JURI::base();?>components/com_awdwall/images/Twitter-icon.png" class="post_type_icon"  />
	<?php }	?>
	  </a> 	 
	  <br />
	<a style="font-size:11px;" href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Main wall profile');?></a>
	  </div>
    </div>
<?php }?>
    <div class="<?php echo $rightColumnCss;?>"><span class="tl2"></span><span class="bl2"></span><span class="tr2"></span><span class="br2"></span>
    <div class="right_mid_content">
		<?php echo $pmText;?>
	 <ul class="walltowall">
<?php if($aPostB && $this->msgs[$i]->group_id == NULL){ ?>
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
		case 'image';
			$textWall = 'A ADDED PHOTO ON B WALL';
			break;
		case 'video';
			$textWall = 'A ADDED VIDEO ON B WALL';
			break;
		case 'trail';
			$textWall = 'A ADDED TRAIL ON B WALL';
			break;
		case 'link';
			$textWall = 'A ADDED LINK ON B WALL';
			break;
		case 'file';
			$textWall = 'A ADDED FILE ON B WALL';
			break;
		case 'mp3';
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
	}
	echo JText::sprintf($textWall, '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '" >' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>', '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->user_id . '&Itemid=' . $Itemid, false) . '">' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->user_id) . "'s" . '</a>');
	?>
</li>
	<!--li><a style="font-size:12px;" href="<?php //echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false);?>" class="john"><?php //echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?></a></li>
	<li><a style="font-size:12px;" href="<?php //echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->user_id . '&Itemid=' . $Itemid, false);?>"><?php //echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->user_id);?></a> &nbsp;&nbsp;<?php //echo $this->msgs[$i]->message;?></li-->
<?php }else{ 
if(!(int)$this->msgs[$i]->group_id){
	if($this->msgs[$i]->type == 'like' || $this->msgs[$i]->type == 'friend' || $this->msgs[$i]->type == 'group'){
		echo '<li>' . $likeTxt . '</li>';
	}else{
	if(!(int)$this->msgs[$i]->is_pm){
?>
	<li><a style="font-size:12px;" href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false);?>"><?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?></a>&nbsp;&nbsp;<?php echo $this->msgs[$i]->message;?></li>
	<?php }else{
	if($this->msgs[$i]->commenter_id != $this->msgs[$i]->user_id && $this->wuid == $this->msgs[$i]->commenter_id){
	?>
	<li><a style="font-size:12px;" href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false);?>" class="john"><?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?></a></li>
	<li><a style="font-size:12px;" href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->user_id . '&Itemid=' . $Itemid, false);?>"><?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->user_id);?></a> &nbsp;&nbsp;<?php echo $this->msgs[$i]->message;?></li>
	<?php }else{?>
	<li><a style="font-size:12px;" href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false);?>"><?php echo AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id);?></a>&nbsp;&nbsp;<?php echo $this->msgs[$i]->message;?></li>
	<?php }?>
	<?php }?>
	
<?php } }else{
if($this->msgs[$i]->type == 'group'){
	echo '<li>' . $likeTxt . '</li>';
	}else{
?>
	<li><?php echo JText::sprintf('A WRITE ON B GROUP', '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->msgs[$i]->commenter_id . '&Itemid=' . $Itemid, false) . '" >' . AwdwallHelperUser::getDisplayName($this->msgs[$i]->commenter_id) . '</a>', '<a style="font-size:12px;" href="' . JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->msgs[$i]->group_id . '&Itemid=' . $Itemid, false) . '" >' . $group->title . '</a>');?></li>
<?php }}?>
<?php }?>
	</ul>
  <div class="commentinfo"> <span class="wall_date"><?php echo AwdwallHelperUser::getDisplayTime($this->msgs[$i]->wall_date);?> </span>&nbsp;&nbsp;
<?php if((int)$user->id && !$aPostB){
if(!(int)$this->msgs[$i]->is_pm){
?>	
	<a href="javascript:void(0);" onclick="showCommentBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getcbox&tmpl=component';?>', <?php echo $this->msgs[$i]->id;?>, <?php echo $this->msgs[$i]->commenter_id;?>, <?php echo $this->msgs[$i]->id;?>);" title="Comment"><?php echo JText::_('Comment');?></a> 
<?php
	$canlike = $this->wallModel->getLikeOfMsgOfUser($this->msgs[$i]->id,$user->id);
	if(!$canlike){
?>
	- 
<a href="javascript:void(0);" onclick="openLikeMsgBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=addlikemsg&wid=' . (int)$this->msgs[$i]->id . '&cid=' .(int)$this->msgs[$i]->commenter_id.'&tmpl=component';?>', '<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo $this->msgs[$i]->id;?>');"><?php echo JText::_('Like');?></a>
<?php } ?>
<span id="wholike_box_<?php echo $this->msgs[$i]->id;?>">	
<?php
	// get who likes of message
	$whoLikes = $this->wallModel->getLikeOfMsg($this->msgs[$i]->id);
	if(isset($whoLikes[0])){
?>
<?php /*?>	- <a href="javascript:void(0);" onclick="getWhoLikeMsg('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component';?>', <?php echo $this->msgs[$i]->id;?>);"><?php echo JText::_('Who likes it');?></a> 
<?php */?><?php }?>
</span>
	- <a class="share" rev="hid_<?php echo $this->msgs[$i]->id; ?>" rel="share_<?php echo $this->msgs[$i]->id; ?>" onmouseover="show_share();" href="javascript:void(0);"><?php echo JText::_('Share');?></a>
		
	<div class="share" style="display:none;" id="share_<?php echo $this->msgs[$i]->id; ?>">
		<div class="share-top"><div></div></div>
		<a href="javascript:void(0);" onclick="hidden_share();" style="float: right; font-weight: bold; color: rgb(170, 170, 170); margin-right: 7px;">X</a>
		<div class="share-center">
			<a class="facebook" rel="nofollow" target="_blank"  href="http://www.facebook.com/share.php?u=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid));?>&t=<?php echo urlencode($pageTitle);?>" title="<?php echo JText::_('facebook');?>"><?php echo JText::_('Facebook');?></a>
			<br/>
			<a class="twitter" rel="nofollow" target="_blank"  href="http://twitter.com/share?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid));?>&text=<?php echo urlencode($pageTitle);?>" title="<?php echo JText::_('twitter');?>"><?php echo JText::_('Twitter');?></a>
			<br/>
			<a class="myspace" rel="nofollow" target="_blank"  href="http://www.myspace.com/Modules/PostTo/Pages/?t=<?php echo urlencode($pageTitle);?>&u=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid));?>&l=1" title="<?php echo JText::_('Myspace');?>"><?php echo JText::_('Myspace');?></a>
			<br/>
			<a class="email" target="_blank" href="http://www.addtoany.com/email?linkurl=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid));?>&linkname="><?php echo JText::_('Email');?></a>
			<br/>
			<a  class="email" target="_blank" href="http://www.blogger.com/blog_this.pyra?t&u=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid));?>&n=<?php echo urlencode($pageTitle);?>&pli=1"><?php echo JText::_('Blog');?></a>
			<br/>
			<a  class="email" target="_blank" href="http://www.google.com/buzz/post?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->msgs[$i]->id . '&Itemid=' . $Itemid));?>"><?php echo JText::_('Buzz');?></a>
		</div>
		<div class="share-bottom"><div></div></div>
	</div>
	- <a href="javascript:void(0);" onclick="showPMBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getpmbox&tmpl=component';?>', <?php echo $this->msgs[$i]->id;?>, <?php echo $this->msgs[$i]->commenter_id;?>, <?php echo $this->msgs[$i]->id;?>);"><?php echo JText::_('PM');?></a>
	<?php if((int)$user->id == (int)$this->msgs[$i]->commenter_id){?>
	- <a href="javascript:void(0);" onclick="openMsgDeleteBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=deletemsg&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component';?>', <?php echo $i;?>);"><?php echo JText::_('Delete');?></a> 
	<?php }elseif((int)$user->id == (int)$_GET['wuid']){?>
	- <a href="javascript:void(0);" onclick="openMsgDeleteBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=deletemsg&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component';?>', <?php echo $i;?>);"><?php echo JText::_('Delete');?></a> 
	<?php } // end delete ?>
	
<?php }else{// end not pm ?>
<a href="javascript:void(0);" onclick="showCommentBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getcbox&tmpl=component&is_reply=1';?>', <?php echo $this->msgs[$i]->id;?>, <?php echo $this->msgs[$i]->commenter_id;?>, <?php echo $this->msgs[$i]->id;?>);" title="Comment"><?php echo JText::_('Reply');?></a>
<?php if((int)$user->id == (int)$this->msgs[$i]->commenter_id){?>
	- <a href="javascript:void(0);" onclick="openMsgDeleteBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=deletemsg&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component';?>', <?php echo $i;?>);"><?php echo JText::_('Delete');?></a> 
	<?php } // end delete ?>
<?php }?>
<?php if(AwdwallHelperUser::checkOnline($this->msgs[$i]->commenter_id)){?>
	<img src="<?php echo AwdwallHelperUser::getOnlineIcon();?>" title="<?php echo JText::_('Online');?>" alt="<?php echo JText::_('Online');?>" style="vertical-align:middle;"/>
<?php }?>
<?php }else{ ?>
<?php if((int)$user->id == (int)$this->msgs[$i]->commenter_id){?>

	- <a href="javascript:void(0);" onclick="openMsgDeleteBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=deletemsg&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component';?>', <?php echo $i;?>);"><?php echo JText::_('Delete');?></a> 

	<?php } // end delete ?>
<?php } ?>
<p></p>
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
	<!--start pm box -->
	<div id="pm_<?php echo $this->msgs[$i]->id;?>" class="comment_text">
	<span id="pm_loader_<?php echo $this->msgs[$i]->id;?>" style="display:none;margin:10px;margin-top:10px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
	</div>
	<!--end pm box -->
	<!--start comment box -->
	<div id="c_<?php echo $this->msgs[$i]->id;?>" class="comment_text">
	<span id="c_loader_<?php echo $this->msgs[$i]->id;?>" style="display:none;margin-bottom:10px;margin-top:10px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
	</div>
	<!--end comment box -->
	<!-- start video block -->
<?php if($this->msgs[$i]->type == 'video' && !$aPostB){?>
	<div class="whitebox video">
	<div style="overflow:hidden;" id="video_block_<?php echo $this->msgs[$i]->id;?>">
	<div class="clearfix">
            <div class="imagebox"><img src="<?php echo JURI::base();?>images/<?php echo $video->thumb; ?>" title="<?php echo $video->title; ?>" alt="<?php echo $video->title; ?>"  /> <br />
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

		}	
			
	
	}
?>		 
	</div>
            <div class="maincomment">
              <h3><?php echo $video->title;?></h3>
              <p><?php echo substr($video->description,0,200);?></p>
              <font color="#9997ac"><?php echo JText::_('Length');?>: </font><?php echo AwdwallHelperUser::formatDuration((int)($video->duration), 'HH:MM:SS');?></div>			  
      </div>
	  <div style="width:426px; float: left; margin-top: 16px;" id="video_<?php echo $this->msgs[$i]->id;?>"></div>
	</div>
</div>
<?php }?>
<!-- end video block -->
<!-- start image block -->
<?php if($this->msgs[$i]->type == 'image' && !$aPostB){?>
<div class="whitebox video">
	<div id="video_block_<?php echo $this->msgs[$i]->id;?>">
	<div class="clearfix">
            <div class="imagebox">
	<?php 
	$pid=$image->id;
	$image_uid=$this->msgs[$i]->user_id;
	$imglink=JRoute::_('index.php?option=com_awdjomalbum&view=awdwallimagelist&wuid='.$image_uid.'&pid='.$pid.'&Itemid='.$Itemid,false);	//echo $imglink;
	if($this->jomalbumexist)
	{
	?><a href="<?php echo $imglink;?>"><img src="<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $image->path;?>" title="<?php echo $image->name;?>" alt="<?php echo $image->name;?>" /></a>
	<?php }else{?>
	<img src="<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $image->path;?>" title="<?php echo $image->name;?>" alt="<?php echo $image->name;?>" />
	<?php }?>
 <br />
            <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-images.png" title="<?php echo JText::_('View image');?>" alt="<?php echo JText::_('View image');?>" /> 
<?php 
	if($this->imageLightbox){
?>
<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/original/<?php echo $image->path;?>', '<?php echo $image->name;?>', '<?php echo $image->description;?>');" title="<?php echo $image->name;?>"><?php echo JText::_('View image');?></a>
<?php
	}else
		echo '<a href="' . JRoute::_('index.php?option=com_awdwall&task=viewimage&wuid=' . $this->msgs[$i]->user_id . '&imageid=' . $image->id . '&Itemid=' . $Itemid, false) . '" >' . JText::_('View image') . '</a>';
?>		 
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
<?php if($this->msgs[$i]->type == 'link' && !$aPostB){?>
<div class="whitebox video">
	<div id="video_block_<?php echo $this->msgs[$i]->id;?>">
	<div class="clearfix">
<?php if($link->path != ''){?>
        <div class="imagebox"><img src="<?php echo JURI::base();?>images/<?php echo $this->msgs[$i]->user_id;?>/thumb/<?php echo $link->path;?>" title="<?php echo $link->title;?>" alt="<?php echo $link->description;?>"  /> <br />
            <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-images.png" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /> 
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
<div style=" margin-top:3px; width:100%; clear:both;"><div style="float:left; width:5%;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('View link');?>" align="absmiddle" alt="<?php echo JText::_('View link');?>" style="height:20px; width:20px; clear:right;" /></div><div style=" float:left; margin-left:5px; width:90%;"><a href="<?php echo $link->link;?>" target="blank" style="margin:0px; padding:0; font-size:9px; font-weight:normal;"><?php echo $link->title;?></a></div></div>
<?php } ?>
		</div>	
<?php }?>
	</div>
	</div>
</div>
<?php }?>
<!-- end link block -->
<!-- start mp3 block -->
<?php if($this->msgs[$i]->type == 'mp3' && !$aPostB){?>
<div class="whitebox video">
	<div id="video_block_<?php echo $this->msgs[$i]->id;?>">
	<div class="clearfix">
	<div class="title-mus">
		<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-music.png" title="<?php echo JText::_('Music');?>" alt="<?php echo JText::_('Music');?>" style="float:left;" />
		<h3 style="color:#3D5A96;font-size:13px;font-weight:bold;margin:0;padding:3px 0px 6px 5px;"> &nbsp;<?php echo $mp3->title;?></h3>
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
<?php }?>
<!-- end mp3 block -->
<!-- start of jing block -->
<?php 
if($this->msgs[$i]->type == 'jing'  && !$aPostB)
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

<!-- start file block -->
<?php if($this->msgs[$i]->type == 'file' && !$aPostB){?>
<div class="whitebox video">
	<div id="video_block_<?php echo $this->msgs[$i]->id;?>">
	<div class="clearfix">
                        <div class="imagebox"><a href="<?php echo JURI::base();?>images/awdfiles/<?php echo $this->msgs[$i]->user_id;?>/<?php echo $file->path;?>" target="_blank"> 
                          <img src="<?php echo JURI::base();?>components/com_awdwall/images/file.png" title="<?php echo JText::_('Download');?>" alt="<?php echo JText::_('Download');?>" /></a> 
                          <br />
                          <span style="padding-left:12px;font-weight:bold;color:#308CB6;"><a href="<?php echo JURI::base();?>images/awdfiles/<?php echo $this->msgs[$i]->user_id;?>/<?php echo $file->path;?>" target="_blank"><?php echo JText::_('Download');?></a></span>
	</div>
		<div class="maincomment">
			<h3><?php echo $file->title;?></h3>
		 </div>
	</div>
	</div>
</div>
<?php }?>
<!-- end file block -->
<!--start comment block -->
<div id="c_content_<?php echo $this->msgs[$i]->id;?>">
<?php
	// get comments of message
	$cpage 		= 0;
	$coffset 	= $cpage*$this->commentLimit;
	$comments 	= $this->wallModel->getAllCommentOfMsg($this->commentLimit, $this->msgs[$i]->id, $coffset);
	$nofComments = $this->wallModel->countComment($this->msgs[$i]->id);
	if(isset($comments[0])){
		foreach($comments as $comment){		
?>
<div class="whitebox" id="c_block_<?php echo $comment->id;?>">
  <div class="clearfix">
    <div class="subcommentImagebox"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $comment->commenter_id . '&Itemid=' . $Itemid, false);?>"><img src="<?php echo AwdwallHelperUser::getAvatar($comment->commenter_id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($comment->commenter_id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($comment->commenter_id);?>" /></a></div>
    <div class="subcomment">
      <div class="rbroundbox">
        <div class="rbtop">
          <div></div>
        </div>
        <div class="rbcontent">
			<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $comment->commenter_id . '&Itemid=' . $Itemid, false);?>" class="authorlink"><?php echo AwdwallHelperUser::getDisplayName($comment->commenter_id);?></a>&nbsp;&nbsp;<?php echo $comment->message;?>
          <div class="subcommentmenu"> 
		  <span class="wall_date"><?php echo AwdwallHelperUser::getDisplayTime($comment->wall_date);?></span>
	<?php if((int)$user->id == (int)$comment->commenter_id){?>	  
		  &nbsp;&nbsp;
		  <a href="javascript:void(0);" onclick="openCommentDeleteBox('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=deletecomment&wid=' . (int)$comment->id . '&tmpl=component';?>', <?php echo $comment->id; ?>);"><?php echo JText::_('Delete');?></a> 
	<?php }?>	  
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

	<!--end comment block-->
		</div>
	  </div>
    </div>
  </div>
 <!-- end block msg -->
<?php 
	}// end for 
}// end if parent
?>
  </div>
 </div>
 </div>
     </div>
 <?php
	if(((($this->page + 1) * $this->postLimit) < $this->nofMsgs) && (int)$user->id){
?>
 <div class="lightblue_box"><a href="javascript:void(0);" onclick="getOlderPosts('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getoldermsg&tmpl=component&layout=mywall&wuid=' . $this->wuid;?>');"><?php echo JText::_('Older Posts');?></a>&nbsp;&nbsp;<span id="older_posts_loader" style="display:none;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
	<input id="awd_page" name="awd_page" type="hidden" value="<?php echo ($this->page + 1);?>" autocomplete="off" />
	<input id="task" name="task" type="hidden" value="<?php echo $this->task;?>" autocomplete="off" />
 </div>
<?php } ?>
 <!-- end msg content --> 
 </div>
 
<div id="dialog_msg_delete_box" title="<?php echo JText::_('Delete Post');?>" style="display:none;">
	<?php echo JText::_('Are you sure you want to delete this post');?>
	<br />
	<br />
	<span id="msg_delete_loader"></span>
	<input type="hidden" name="msg_delete_url" id="msg_delete_url" />
	<input type="hidden" name="msg_delete_block_id" id="msg_delete_block_id" />
</div>
<div id="dialog_c_delete_box" title="<?php echo JText::_('Delete Comment');?>" style="display:none;">
	<?php echo JText::_('Are you sure you want to delete this comment');?>
	<br />
	<br />
	<span id="c_delete_loader"></span>
	<input type="hidden" name="c_delete_url" id="c_delete_url" />
	<input type="hidden" name="c_delete_block_id" id="c_delete_block_id" />
</div>
<div id="dialog_like_box" title="<?php echo JText::_('Like This Post');?>" style="display:none;">
	<?php echo JText::_('Are you sure you want to like this post');?>
	<br />
	<br />
	<span id="like_loader"></span>
	<input type="hidden" name="like_url" id="like_url" />
	<input type="hidden" name="who_like_url" id="who_like_url" />
	<input type="hidden" name="who_like_wid" id="who_like_wid" />	
</div>
<div id="dialog_pm_delete_box" title="<?php echo JText::_('Delete Private Message');?>" style="display:none;">
	<?php echo JText::_('Are you sure you want to delete this PM');?>
	<br />
	<br />
	<span id="pm_delete_loader"></span>
	<input type="hidden" name="pm_delete_url" id="pm_delete_url" />
	<input type="hidden" name="pm_delete_block_id" id="pm_delete_block_id" />
</div>
<div id="dialog_add_as_friend" title="<?php echo JText::_('Add as Friend');?>" style="display:none;">
	<?php echo JText::_('Are you sure you want to add this people as Friend');?>
	<br />
	<br />
	<span id="add_as_friend_loader"></span>
	<input type="hidden" name="add_as_friend_url" id="add_as_friend_url" />	
</div>
<div id="dialog_add_as_friend_msg" title="<?php echo JText::_('Add as Friend');?>" style="display:none;">
	<?php echo JText::sprintf('ADD FRIEND CONFIRM', AwdwallHelperUser::getDisplayName($userWall->id), AwdwallHelperUser::getDisplayName($userWall->id));?>
	<br />
	<span id="add_as_friend_loader"></span>
</div>