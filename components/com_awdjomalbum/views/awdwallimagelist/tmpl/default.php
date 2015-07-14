<?php 

 

$username=$this->username;

$userid=$this->userid; 

$pid=$this->pid; 



$db		=& JFactory :: getDBO();

$user =& JFactory::getUser();









$Itemid=AwdwallHelperUser::getComItemId();

$wallversion=checkversionwall();

$friendstr=getfriendlist();



$pendingFriends = JsLib::getPendingFriends($user->id);

$groupsUrl=JRoute::_('index.php?option=com_awdwall&task=groups&Itemid=' . $Itemid, false);

if($wallversion=='cb')

{

$friendJsUrl = JRoute::_('index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid, false);


$accountUrl=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $user->id . '&Itemid=' . AwdwallHelperUser::getJsItemId(), false);

}

elseif($wallversion=='js')

{

$friendJsUrl = JRoute::_('index.php?option=com_community&view=friends&userid=' . $user->id . '&Itemid=' . $Itemid,false);

$accountUrl=JRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id . '&Itemid=' . $Itemid, false);

}

else

{

$friendJsUrl = JRoute::_('index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid, false);

$accountUrl=JRoute::_('index.php?option=com_awdwall&task=uploadavatar&Itemid=' . $Itemid, false);

}



$photorow=$this->photorow;

$next=$this->nextR;

$prev=$this->prevR;

$totalRecord=$this->totRecord;

$curPosition=$this->curPosition;

$tagrows=$this->tagrows;

$friendstr=getfriendlist();



$pageTitle=$photorow->description.' photo no. '.$curPosition;



$commentrows=$this->commentrows;

$totalcommentrows=$this->totalcommentrows;

$totComments=count($totalcommentrows); 



if($next){

$nextLink=JRoute::_('index.php?option=com_awdjomalbum&view=awdwallimagelist&tmpl=component&wuid='.$userid.'&pid='.$next.'&Itemid='.AwdwallHelperUser::getComItemId(),false);
$nextLink1=JRoute::_('index.php?option=com_awdjomalbum&view=awdwallimagelist&tmpl=component&wuid='.$userid.'&pid='.$next.'&Itemid='.AwdwallHelperUser::getComItemId(),false);

}

else

{

$nextLink1=JRoute::_('index.php?option=com_awdjomalbum&view=awdwallimagelist&tmpl=component&wuid='.$userid.'&pid='.$photorow->id.'&Itemid='.$Itemid,false);

}

if($prev)

{

$prevLink=JRoute::_('index.php?option=com_awdjomalbum&view=awdwallimagelist&tmpl=component&wuid='.$userid.'&pid='.$prev.'&Itemid='.AwdwallHelperUser::getComItemId(),false);

}

else

{

$prevLink='#';

}



$albumlink 	=JRoute::_('index.php?option=com_awdjomalbum&view=awdalbumlist&wuid='.$userid.'&Itemid='.AwdwallHelperUser::getComItemId());





$values=getCurrentUserDetails($userid); 

$profilelink=$values[1];

	 



$albumlikerows=$this->albumlikerows;

$imgpath=JURI::base()."images/".$userid."/original/".$photorow->path;


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



	list($imgwidth, $imgheight, $type, $attr) = getimagesize($imgpath);

$scalimgwidth=$width-15;
if($imgwidth>$scalimgwidth)
{
	$imgpath=JURI::base()."images/".$userid."/large/".$photorow->path;
}
else
{
	$imgpath=$imgpath;
}
$sql= 'SELECT username from #__users where id='.$userid;
$db->setQuery($sql);
$username=$db->loadResult();
			$sql= 'SELECT wall_date from #__awd_wall inner join #__awd_wall_images on #__awd_wall.id=#__awd_wall_images.wall_id and type="image" and #__awd_wall_images.id='.$photorow->id;
		 	$db->setQuery($sql);
			$wall_date=$db->loadResult();
$upload_date = AwdwallHelperUser::getDisplayTime($wall_date);
	$app = JFactory::getApplication('site');
	$config =  & $app->getParams('com_awdwall');
	$jqueryversion 		= $config->get('jqueryversion', '1.7.2');
$albumlink="index.php?option=com_awdjomalbum&view=awdalbumlist&wuid=".$user->id."&Itemid=".AwdwallHelperUser::getComItemId();

$newwallid=$photorow->wall_id;
$imagequery="select * from #__awd_wall where id=".$newwallid;
$db->setQuery($imagequery);
$newwallrows=$db->loadObjectList();
$newwallrow=$newwallrows[0];
$upload_date=$newwallrow->wall_date;
$upload_date = AwdwallHelperUser::getDisplayTime($upload_date);
$commenter_id=$newwallrow->commenter_id;

$sql= 'SELECT username from #__users where id='.$commenter_id;
$db->setQuery($sql);
$username=$db->loadResult();
$owneruser = &JFactory::getUser($commenter_id);
if((int)$displayName == 1) {
$username=$owneruser->username;
}else{
$username=$owneruser->name;
 }
?>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/<?php echo $jqueryversion; ?>/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery.expander.js"></script>
<script type="text/javascript">

	jQuery(document).ready(function(){
	jQuery('img.imgtag').each(function(){
		jQuery(this).load(function(){
			var maxWidth = jQuery(this).width(); // Max width for the image
			var maxHeight = jQuery(this).height();   // Max height for the image
			jQuery(this).css("width", "auto").css("height", "auto"); // Remove existing CSS
			jQuery(this).removeAttr("width").removeAttr("height"); // Remove HTML attributes
			var width = jQuery(this).width();    // Current image width
			var height = jQuery(this).height();  // Current image height
	
			if(width > height) {
				// Check if the current width is larger than the max
				if(width > maxWidth){
					var ratio = maxWidth / width;   // get ratio for scaling image
					jQuery(this).css("width", maxWidth); // Set new width
					jQuery(this).css("height", height * ratio);  // Scale height based on ratio
					height = height * ratio;    // Reset height to match scaled image
				}
			} else {
				// Check if current height is larger than max
				if(height > maxHeight){
					var ratio = maxHeight / height; // get ratio for scaling image
					jQuery(this).css("height", maxHeight);   // Set new height
					jQuery(this).css("width", width * ratio);    // Scale width based on ratio
					width = width * ratio;  // Reset width to match scaled image
				}
			}
		});
	});
	
  jQuery('span.wallimagedesc').expander({
	slicePoint: 100,
	widow: 2,
	expandSpeed: 0,
	userCollapseText: '<?php echo JText::_('read less');?>',
	expandText:'<?php echo JText::_('read more');?>',
  });
	});

</script>
<link rel="stylesheet" href="<?php echo JURI::base();?>components/com_awdwall/css/style_<?php echo $template; ?>.css"  type="text/css" />
<link rel="stylesheet" href="<?php echo JURI::base();?>components/com_awdjomalbum/css/style.css" type="text/css" />
<link rel="stylesheet" href="<?php echo JURI::base();?>components/com_awdjomalbum/css/style2.css" type="text/css" />
<div  id="awd-mainarea" style="margin-left:auto; margin-right:auto; padding:0px;">
<style type="text/css">
#awd-mainarea{
	background-color:none;
}
#awd-mainarea table
{
	border:solid 0px #ddd !important
}
#awd-mainarea tr, #awd-mainarea td
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
#msg_content .rbroundboxleft, #awd-mainarea #msg_content .awdfullbox{
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
	background:none;
}
#awd-mainarea .new_comment_row{
	background-color:#<?php echo $color[12]; ?>;
}
#awd-mainarea div.sharealbum{
	background-color:#<?php echo $color[14]; ?>;
}
#awd-mainarea .fullboxnew{
	background-color:#FFFFFF;
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
body.contentpane{
margin:0px!important;
}
#main{
padding:0px!important;
}
.wallimagedesc{
padding:5px 0px;
display:inline-block;
width:100%;
}
</style>
  <div class="detailPage" style="margin-left:auto; margin-right:auto;background-color:#000000; width:960px;">
    <div id="image" style=" background-color:#000000;width:600px;text-align: center;">
   
      <div  id="tag-wrapper" > <img src="<?php echo $imgpath;?>" name="myImage" class="imgtag" id="mainimage" style="max-width:600px; max-height:500px;"/>
        <div style="left: 197px; top: 117px; display: none;" id="tag-target"></div>
      </div>
       <div style="  bottom:10px; left:20px; position: absolute; width:130px;">
       <?php if(!empty($user->id)) { ?>
       <div class="tag" id="tagbutton"></div>
       <?php } ?>
       </div>
       <div style="  bottom:10px; right:380px; position: absolute; width:100px;">
	<?php if($totalRecord>1){?>
    <span style="cursor:pointer; color:#0000FF;"><a href="<?php echo $prevLink; ?>" title="<?php echo JText::_('Previous');?>"><img src="<?php echo JURI::base();?>components/com_awdjomalbum/images/prev.png"  /></a></span>
    <?php } if($next){?>
    <span   style="cursor:pointer; color:#0000FF;"><a href="<?php echo $nextLink; ?>" title="<?php echo JText::_('Next');?>"><img src="<?php echo JURI::base();?>components/com_awdjomalbum/images/next.png"  /></a></span>
    <?php } ?>
    </div>
    </div>
    <div style="width:330px;  background-color:#FFFFFF;  float:right; padding:5px 10px;" id="awdwhitebox">
    <div style="width:230px; margin:5px 0;"><div style="float:left;width:55px;"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$commenter_id.'&Itemid='.AwdwallHelperUser::getComItemId());?>" target="_top"><img src="<?php echo AwdwallHelperUser::getBigAvatar51($commenter_id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($commenter_id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($commenter_id);?>" border="0" width="50" /></a></div><div style="float:left;width:150px; padding-left:3px; padding-top:0px;"><div style="float:left; width:100%;"><span class="profileName"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$commenter_id.'&Itemid='.AwdwallHelperUser::getComItemId());?>" target="_top"><?php echo AwdwallHelperUser::getDisplayName($commenter_id);?></a>&nbsp;&nbsp;<?php echo $photorow->name; ?></span></div><div style="float:left; width:100%;padding-top:5px;"><span class="wall_date" style="font-size:11px;"><?php echo $upload_date;?></span></div>
</div></div>
<span class="wallimagedesc"><?php echo $photorow->description;?></span>
	<?php if(!empty($user->id)) { ?>
    <div style="margin:7px 0; width:100%; float:left;" >
    <span id="like" ><a href="JavaScript:void(0);" onClick="awdphotowallLike('<?php echo $photorow->id; ?>','<?php echo $user->id;?>');"><?php echo JText::_('Like');?></a></span>
     </div>
     <div style="clear:both;"></div>
    <?php } ?>
  
      <?php
		$sql="select * from #__awd_jomalbum_photo_wall_like where photoid=".$photorow->id." order by id desc Limit 5";
		$db->setQuery($sql);
		$albumlikerows=$db->loadObjectList();
		$sql="select count(*) from #__awd_jomalbum_photo_wall_like where photoid=".$photorow->id;
		$db->setQuery($sql);
		$totLike=$db->loadResult();
		 ?>
      <div <?php if($totLike>0) {?> style="background-color:#<?php echo $color[12]; ?>; padding:5px; margin-bottom:5px" <?php }else{ ?> style="height:65px;" <?php } ?> id="new_album_like">
        <?php if($totLike>0) {?>
        <div style="width:100%; text-align:left; padding-bottom:3px; " ><span  class="likespan"><?php echo $totLike.'&nbsp;'.JText::_('People like this photo');?></span></div>
        <?php }
		 foreach($albumlikerows as $albumlikerow) {  
			$values=getCurrentUserDetails($albumlikerow->userid);  
			$avatarTable=$values[2];
			$userprofileLinkCUser=$values[1];
			$values1=getUserDetails($albumlikerow->userid,$avatarTable,$user->id); 
			$imgPath1=$values1[0];
		?>
        <a href="<?php echo $userprofileLinkCUser; ?>" style="padding-right:5px;" target="_top"> <img src="<?php echo $imgPath1; ?>" border="0" height="32" width="32" /> </a>
        <?php } ?>
      </div>
   	  <div style="height: 220px; overflow:hidden; overflow-y: scroll; width: 100%;">
		
      <div id="new_comment_here"></div>
      <div class="commentBox"></div>
      <?php 
            
		$userprofileLinkAWDCUser=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.AwdwallHelperUser::getComItemId());
		$avatarTable='';
		$values=getCurrentUserDetails($user->id);
		$imgPathCUser=$values[0];
		$userprofileLinkCUser=$values[1];
		$avatarTable=$values[2];
		$cDatecUser=AwdwallHelperUser::getDisplayTime(time());
		if($commentrows){
		$counter=0; 
		foreach($commentrows as $commentrow) {
		$cDate=AwdwallHelperUser::getDisplayTime($commentrow->wall_date);
		$uid=$commentrow->uid;
		$userprofileLinkAWD=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$uid.'&Itemid='.AwdwallHelperUser::getComItemId());
		$values1=getUserDetails($commentrow->commenter_id,$avatarTable,$uid);
		$imgPath1=$values1[0];
		$userprofileLink=$values1[1];
				$commentername=AwdwallHelperUser::getDisplayName($commentrow->commenter_id);

            ?>
      <div class="commentBox2" id="new_comment_table<?php echo $counter; ?>" >
        <div id="avtar"><a href="<?php echo $userprofileLinkAWD; ?>" target="_top"> <img src="<?php echo $imgPath1; ?>" border="0" align="absmiddle" height="32" width="32"/> </a></div>
        <div class="commentDetail " >
          <div style="margin-bottom:10px;width:85%; text-align:justify;"><span class="cUser"><a href="<?php echo $userprofileLinkAWD; ?>" class="authorlink" target="_top"><?php echo $commentername;?></a></span> <span class="comments"><?php echo nl2br(AwdwallHelperUser::showSmileyicons($commentrow->message)); ?></span></div>
          <div class="subcommentmenu"><span class="commentDate wall_date"><?php echo $cDate; ?></span> -<span><a href="JavaScript:void(0);" onClick="reportComment('<?php echo $commentrow->id; ?>','<?php echo $user->id;?>');"><?php echo JText::_('Report');?></a></span>
            <?php if($user->id==$userid) {?>
            - <span class="comment_del_but" id="<?php echo $counter; ?>" ><a href="JavaScript:void(0);"><?php echo JText::_('Delete');?></a></span>
            <?php } else if($user->id==$uid) { ?>
            - <span class="comment_del_but" id="<?php echo $counter; ?>" ><a href="JavaScript:void(0);"><?php echo JText::_('Delete');?></a></span>
            <?php }?>
          </div>
          <input type="hidden" value="<?php echo $commentrow->id; ?>" id="hid<?php echo $counter; ?>" />
        </div>
      </div>
      <div class="cDivider" id="new_comment_table1<?php echo $counter; ?>"></div>
      <?php  $counter++;  } } else {?>
      <?php } ?>
      <div style="clear:both;"></div>
      <div id="oldercommentDiv"></div>
      <div id="oldercommentDivloader" style="display:none"><img src="<?php echo JURI::base() . "components/com_awdwall/images/".$template."/"."ajax-loader.gif";?>" /></div>
      <?php if( count($totalcommentrows) > count($commentrows)){?>
      <a href="JavaScript:void(0);" onClick="showoldercomments(<?php echo $pid;?>);"><?php echo JText::_('Older Comments');?></a>
      <?php } ?>
      </div>
        <br/>
      <hr style="border:0px solid #DCDCDC;"/>
       <br/>
    <div style=" width:330px;">
    <?php echo AwdwallHelperUser::getSmileyicons('comm_input');?>
    <textarea class="text_comment" id="comm_input" rows="6" cols="50" style="overflow:auto !important;"></textarea>
    <img src="<?php echo JURI::base();?>components/com_awdwall/images/smicon.png" alt="Insert emotions" title="Insert emotions" onClick="smilyshow('comm_input')" style="cursor: pointer; clear:both; margin-top: -22px;position: absolute;right: 40px; z-index: 1; display:block;" />

    <br/>
    <button class="postButton_small" id="post_but"><?php echo JText::_('Comment');?></button>
    </div>
    </div>
  </div>

	<input type="hidden" name="option" value="com_awdjomalbum" />
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="view" value="awdwallimagelist" /> 
	<input type="hidden" name="commentcounter" id="commentcounter" value="<?php echo $counter;?>" /> 

<!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>-->



 <script type="text/javascript">
    function insertSmiley(smiley,txtid)
    {
		var divid='#smilycontainer_'+txtid;
		var TextArea = document.getElementById(txtid);
		var val = TextArea.value;
		var before = val.substring(0, TextArea.selectionStart);
		var after = val.substring(TextArea.selectionEnd, val.length);
		var smileyWithPadding = " " + smiley + " ";
		TextArea.value = before + smileyWithPadding + after;
		//jQuery(divid).slideToggle("slow");
    }
	function smilyshow(txtid)
	{
		var divid='#smilycontainer_'+txtid;
		jQuery(divid).css("margin-top","-90px");
		jQuery(divid).css("background-color","#ffffff");
		jQuery(divid).css("position","relative");
		jQuery(divid).css("z-index","1");
		jQuery(divid).slideToggle("slow");
		
	}
jQuery('#awdwhitebox').css("height",jQuery('#detailPage').height());
var targetX, targetY;

var tagCounter = <?php echo count($tagrows)?>;



jQuery(document).ready(function(){

	//Dynamically wrap image

	//jQuery(".imgtag").wrap('<div id="tag-wrapper"></div>');

	

	//Dynamically size wrapper div based on image dimensions

	//jQuery("#tag-wrapper").css({width: jQuery(".imgtag").outerWidth(), height: jQuery(".imgtag").outerHeight()});

	

	//Append #tag-target content and #tag-input content

	jQuery("#tag-wrapper").append('<div id="photo_tag_selector" ><div id="tag-target"></div><div id="tag-input"><label for="tag-name"><?php echo JText::_('PERSONNAME');?></label><input type="text" id="tag-name"><?php echo $friendstr;?><input type="hidden" id="taguserid"><button type="button"><?php echo JText::_('SUBMIT');?></button><button type="reset"><?php echo JText::_('CANCEL');?></button></div></div>');

	

	<?php foreach($tagrows as $tagrow){

	$profileurl= JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$tagrow->taguserid.'&Itemid='.AwdwallHelperUser::getComItemId(),false);

	$taguserid=&JFactory::getUser($tagrow->taguserid);

	if($tagrow->taguserid)

	{
$tagusername=AwdwallHelperUser::getDisplayName($tagrow->taguserid);
	?>

	//Adds a new list item below image. Also adds events inline since they are dynamically created after page load

	jQuery("#tag-wrapper").after('<span id="hotspot-item-<?php echo $tagrow->id;?>"><a class="remove" onmouseover="showTag(<?php echo $tagrow->id;?>)" onmouseout="hideTag(<?php echo $tagrow->id;?>)" href="<?php echo $profileurl;?>"><?php echo $tagusername;?></a><?php if($user->id==$tagrow->userid){?><span class="remove" onclick="removeTag(<?php echo $tagrow->id;?>)" onmouseover="showTag(<?php echo $tagrow->id;?>)" onmouseout="hideTag(<?php echo $tagrow->id;?>)"><?php echo JText::_('(').JText::_('Remove').JText::_(')');?></span><?php }?></span>');

	<?php 

	}

	else

	{

	?>

	jQuery("#tag-wrapper").after('<span id="hotspot-item-<?php echo $tagrow->id;?>"><span class="remove" onmouseover="showTag(<?php echo $tagrow->id;?>)" onmouseout="hideTag(<?php echo $tagrow->id;?>)" ><?php echo $tagrow->tagValue;?></span><?php if($user->id==$tagrow->userid){?><span class="remove" onclick="removeTag(<?php echo $tagrow->id;?>)" onmouseover="showTag(<?php echo $tagrow->id;?>)" onmouseout="hideTag(<?php echo $tagrow->id;?>)"><?php echo JText::_('(').JText::_('Remove').JText::_(')');?></span><?php }?></span>');

	<?php 

	}

	?>

		jQuery("#tag-wrapper").append('<span id="hotspot-<?php echo $tagrow->id;?>" class="hotspot" style="left:<?php echo $tagrow->targetX;?>px; top:<?php echo $tagrow->targetY;?>px;"><span><?php echo $tagrow->tagValue;?></span></span>');



	<?php

	

	}

	?>



	

	<?php if($user->id){?>

	//jQuery("#tag-wrapper").click(function(e){

	jQuery(".imgtag").click(function(e){	

	var cursorcss=jQuery('.imgtag').css('cursor');

	if(cursorcss=='crosshair')

	{

		//Determine area within element that mouse was clicked

		mouseX = e.pageX - jQuery("#tag-wrapper").offset().left;

		mouseY = e.pageY - jQuery("#tag-wrapper").offset().top;

		

		//Get height and width of #tag-target

		targetWidth = jQuery("#tag-target").outerWidth();

		targetHeight = jQuery("#tag-target").outerHeight();

		

		//Determine position for #tag-target

		targetX = mouseX-targetWidth/2;

		targetY = mouseY-targetHeight/2;

		

		//Determine position for #tag-input

		inputX = mouseX+targetWidth/2;

		inputY = mouseY-targetHeight/2;

		

		//Animate if second click, else position and fade in for first click

		if(jQuery("#tag-target").css("display")=="block")

		{

			jQuery("#tag-target").animate({left: targetX, top: targetY}, 500);

			jQuery("#tag-input").animate({left: inputX, top: inputY}, 500);

		} else {

			jQuery("#tag-target").css({left: targetX, top: targetY}).fadeIn();

			jQuery("#tag-input").css({left: inputX, top: inputY}).fadeIn();

		}

		

		//Give input focus

		jQuery("#tag-name").focus();

	}

	else

	{

		window.location.href='<?php echo $nextLink1; ?>';

	}

	});

	<?php } ?>

	jQuery("#tagbutton").click(function(e){	

	

	var cursorcss=jQuery('.imgtag').css('cursor');

	

	if(cursorcss=='crosshair')

	{

		jQuery("#tag-target").fadeOut();

		jQuery("#tag-input").fadeOut();

		jQuery("#tag-name").val("");

		jQuery(".imgtag").css({cursor:'pointer'});

		jQuery('#tagbutton').removeClass('donetag');

		jQuery("#tagbutton").addClass("tag");

		



	}

	else

	{

		jQuery(".imgtag").css({cursor:'crosshair'});

		

		//jQuery("#tag-target").animate({left: 100, top: 100}, 500);

		//jQuery("#tag-input").animate({left: 205, top: 100}, 500);

		jQuery('#tagbutton').removeClass('tag');

		jQuery("#tagbutton").addClass("donetag");

	}

		//Determine area within element that mouse was clicked

		

//		targetWidth = jQuery("#tag-wrapper").outerWidth();

//		targetHeight = jQuery("#tag-wrapper").outerHeight();

////		

////		

//		mouseX =jQuery("#tag-wrapper").offset().left;

//		mouseY = jQuery("#tag-wrapper").offset().top;

////		

////		//Get height and width of #tag-target

////		targetWidth = jQuery("#tag-target").outerWidth();

////		targetHeight = jQuery("#tag-target").outerHeight();

////		

////		//Determine position for #tag-target

//		targetX = mouseX-targetWidth/2;

//		targetY = mouseY-targetHeight/2;

//		

//		//Determine position for #tag-input

//		inputX = mouseX+targetWidth/2;

//		inputY = mouseY-targetHeight/2;

		

		//Animate if second click, else position and fade in for first click

		if(jQuery("#tag-target").css("display")=="block")

		{

			jQuery("#tag-target").animate({left: 100, top: 100}, 500);

			jQuery("#tag-input").animate({left: 205, top: 100}, 500);

		} else {

			//jQuery("#tag-target").css({left: 100, top: 100}).fadeIn();

			//jQuery("#tag-input").css({left: 205, top: 100}).fadeIn();

		}

		

		//Give input focus

		jQuery("#tag-name").focus();	

	});

	

	

	//If cancel button is clicked

	jQuery('button[type="reset"]').click(function(){

		closeTagInput();

	});

	

	//If enter button is clicked within #tag-input

	jQuery("#tag-name").keyup(function(e) {

		if(e.keyCode == 13) submitTag();

	});	

	

	//If submit button is clicked

	jQuery('button[type="button"]').click(function(){

		submitTag();

	});



}); //jQuery(document).ready



function submitTag()

{

	var tagValue = jQuery("#tag-name").val();

	//alert(document.getElementById("taguserid").value);

	var taguserid = document.getElementById("taguserid").value;

	if(taguserid=='')

	{

		if(tagValue=='')

		{

			return false;

		}

	}

	

	var tagaddurl="index.php?option=com_awdjomalbum&task=addwalltag&photoid=" + <?php echo $_REQUEST['pid'];?>+'&tagValue='+tagValue+'&targetX='+targetX+'&targetY='+targetY+'&taguserid='+taguserid+'&Itemid='+ <?php echo $Itemid;?>;

	//alert(tagaddurl);

	//return false;

	jQuery.post(tagaddurl, function(data) {

	 if(data)

	 {

		//jQuery('#new_album_like').clear();

		//alert(data);

		tagCounter=data;

		//document.getElementById('new_comment_like'+commentID).innerHTML=htm;

		//jQuery('#new_album_like').append(htm);

	   }

	});

	

	//alert(tagCounter);

	

	//Adds a new list item below image. Also adds events inline since they are dynamically created after page load

	if(taguserid)

	{

	var profileurl="index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid="+taguserid;

	//Adds a new list item below image. Also adds events inline since they are dynamically created after page load

	jQuery("#tag-wrapper").after('<span id="hotspot-item-' + tagCounter + '"><a onmouseover="showTag(' + tagCounter + ')" onmouseout="hideTag(' + tagCounter + ')" href="'+profileurl+'">'+ tagValue +'</a><span class="remove" onclick="removeTag(' + tagCounter + ')" onmouseover="showTag(' + tagCounter + ')" onmouseout="hideTag(' + tagCounter + ')">(Remove)</span></span>');

	}

	else

	{

	jQuery("#tag-wrapper").after('<span id="hotspot-item-' + tagCounter + '"><span onmouseover="showTag(' + tagCounter + ')" onmouseout="hideTag(' + tagCounter + ')">'+ tagValue +'</span><span class="remove" onclick="removeTag(' + tagCounter + ')" onmouseover="showTag(' + tagCounter + ')" onmouseout="hideTag(' + tagCounter + ')">(Remove)</span></span>');

	}

	

	//Adds a new hotspot to image

	jQuery("#tag-wrapper").append('<div id="hotspot-' + tagCounter + '" class="hotspot" style="left:' + targetX + 'px; top:' + targetY + 'px;"><span>' + tagValue + '</span></div>');

	

	tagCounter++;

	closeTagInput();

	

}



function closeTagInput()

{

	jQuery("#tag-target").fadeOut();

	jQuery("#tag-input").fadeOut();

	jQuery("#tag-name").val("");

	

		jQuery(".imgtag").css({cursor:'pointer'});

		jQuery('#tagbutton').removeClass('donetag');

		jQuery("#tagbutton").addClass("tag");

	return false;

}



function removeTag(i)

{

	var tagdelurl="index.php?option=com_awdjomalbum&task=deletewalltag&id=" + i;

	//alert(tagdelurl)

	jQuery.post(tagdelurl);

	

	

	jQuery("#hotspot-item-"+i).fadeOut();

	jQuery("#hotspot-"+i).fadeOut();

}



function showTag(i)

{

	jQuery("#hotspot-"+i).addClass("hotspothover");

}



function hideTag(i)

{

	jQuery("#hotspot-"+i).removeClass("hotspothover");

}

function tagSelectorClick(e,id)

{

var tagusername='tageusername'+id;

document.getElementById("tag-name").value=document.getElementById(tagusername).value;

document.getElementById("taguserid").value=id;

//alert(id);

submitTag();

}



</script>







<script type="text/javascript">

function showoldercomments(photoid)

{

	limitstart=document.getElementById("commentcounter").value;

	

	var url='index.php?option=com_awdjomalbum&task=getolderwcomments&tmpl=component&id='+photoid+'&limitstart='+limitstart;

	

	jQuery("#oldercommentDivloader").show();

	jQuery.get(url, {}, 

			function(data){

				if(data != '')

					jQuery("#oldercommentDiv").append(data);	

					jQuery("#oldercommentDivloader").hide();		

			}, "html");



document.getElementById("commentcounter").value=parseInt(limitstart)+5;

}



		function show_share(){

			jQuery("#share_photo").show();

			

		}



		function hidden_share(){

			jQuery("#share_photo").hide();

		}

		

		

	function commentLike(commentID,uID)

	{

			 url="index.php?option=com_awdjomalbum&view=awdcommentlike&tmpl=component&commentID="+commentID+"&uID="+uID+"&Itemid=<?php echo AwdwallHelperUser::getComItemId()?>"; 

		//  	alert(url);

		htm='';

		document.getElementById('new_comment_like'+commentID).innerHTML='<img src="components/com_awdjomalbum/images/loader.gif"/>';

			jQuery.post(url, function(data) {

			 if(data.length)

			 {

			 	//jQuery('#new_album_like').clear();

			 	htm=data;

				document.getElementById('new_comment_like'+commentID).innerHTML=htm;

			   	//jQuery('#new_album_like').append(htm);

			   }

			});

	}



	function awdphotowallLike(photoID,uID)

	{

	 

			 url="index.php?option=com_awdjomalbum&task=addphotowalllike&tmpl=component&photoID="+photoID+"&uID="+uID+"&Itemid=<?php echo AwdwallHelperUser::getComItemId()?>"; 

			//alert(url);

		htm='';

		document.getElementById('new_album_like').innerHTML='<img src="<?php echo JURI::base() . "components/com_awdwall/images/".$template."/"."ajax-loader.gif";?>" />';

			jQuery.post(url, function(data) {

			 if(data.length)

			 {

			 	//jQuery('#new_album_like').clear();

			 	htm=data;

				document.getElementById('new_album_like').innerHTML=htm;

			   	//jQuery('#new_album_like').append(htm);

			   }

			});

	}

	

	function reportComment(commentID,uID)

	{

			 url="index.php?option=com_awdjomalbum&view=awdwallimagelist&task=reportwallcomment&tmpl=component&commentID="+commentID+"&uID="+uID+"&Itemid=<?php echo AwdwallHelperUser::getComItemId()?>"; 

		  //	alert(url);

		htm='';

		//document.getElementById('new_comment_like'+commentID).innerHTML='<img src="components/com_awdjomalbum/images/loader.gif"/>';

			jQuery.post(url, function(data) {

			 if(data.length)

			 {

			 	alert(data);

				//jQuery('#new_album_like').clear();

			 //	htm=data;

			//	document.getElementById('new_comment_like'+commentID).innerHTML=htm;

			   	//jQuery('#new_album_like').append(htm);

			   }

			});

	}

	

</script>





 <script type="text/javascript">

jQuery(document).ready(function()

{

	var counter='<?php echo $totComments; ?>';

	//alert(counter);

	/*Show input box*/

	jQuery('#comment_but').click(function()

	{

		jQuery('#comm_row').slideDown();

	});

	

	/*Delete the comment*/

	jQuery('.comment_del_but').live("click",function() 

	{

	

		var ID = jQuery(this).attr("id");

		var cID='hid'+ID;

	//	alert(cID);

		comID=document.getElementById(cID).value;

//		if(confirm("Are you sure to delete this Comment?"))

//		{

			jQuery("#new_comment_table"+ID).slideUp();

			

			jQuery("#new_comment_table1"+ID).slideUp();

			var delpath="index.php?option=com_awdjomalbum&task=deletewallcomment&commentid=" + comID;

			jQuery.post(delpath);

			/*

				Write AJAX logic for deletion here

			*/

//		}

	});

	

	 

	

	

	/* Post your comment */

	jQuery('#post_but').click(function()

	{

		var comm = jQuery('#comm_input').val();

		if(comm.length != 0)

		{ 

			/*Disables the input box*/

	   		jQuery('#comm_input').attr({'disabled':'true'});

			/*Disables the post button*/

	   		jQuery('#post_but').attr({'disabled':'true'});

		var path="index.php?option=com_awdjomalbum&task=savewallcomment&photoid="+ <?php echo $_REQUEST['pid'];?> +"&Itemid=<?php echo AwdwallHelperUser::getComItemId(); ?>" + "&comment=" + comm ;



			/*Send ajax request*/

			jQuery.ajax({

		   'url':path,'data':'comment='+comm,

		   'type':'POST',

		   'success':function(data)

		   {

			   if(data.length)

			   {

			   		document.getElementById('comm_input').value="";

					var string=data.split("^") ;

					var comment=string[0];

					var id=string[1];



				   /*Create new table for new post*/

				   var htm = '<div class="commentBox2" id="new_comment_table'+counter+'">';

				   // htm += '<div class="new_comment_row" id="'+counter+'">';

					htm += '<div id="avtar"><a href="<?php echo $userprofileLinkCUser;?>"><img src="<?php echo $imgPathCUser; ?>" border="0" height="32" width="32" /></a></div>';

					htm += '<div class="commentDetail" ><div style="margin-bottom:10px;width:85%; text-align:justify;"><span class="cUser"><a href="<?php echo 		$userprofileLinkAWDCUser; ?>" class="authorlink"><?php echo AwdwallHelperUser::getDisplayName($user->id);?></a></span> <span class="comments">'+comment+'</span></div>';

					htm += '<div class="subcommentmenu"><span class="commentDate wall_date"><?php echo $cDatecUser; ?></span>';

					//htm += 'Report</span>';

					htm += '<span class="comment_del_but" id="'+counter+'" align="right""><a href="JavaScript:void(0);"> - Delete</a></span></div>';

					htm += '<input type="hidden" value="'+id+'" id="hid'+counter+'" />';

					

					//htm += '</div>';

					htm += '</div>';

					//htm +='<table width="100%"><tr><td id="new_comment_like'+id+'"></td></tr></table>';

 					htm +='<div class="cDivider" id="new_comment_table1'+counter+'"></div>';

			 

			

				   /*Append new table in predefined area*/
					
				  // jQuery('#new_comment_here').after(htm);
 				  // jQuery('.commentBox2:last').hide().show(300);
				  jQuery(htm).hide().prependTo("#new_comment_here").fadeIn("slow");
			   	   jQuery('#comm_input').removeAttr('disabled');

		   		   jQuery('#post_but').removeAttr('disabled');
					
				   counter++;
				    jQuery('#comm_row').slideUp();
					
				}

			}

			});

		} else {

		alert('please input something in comment box');

		return false;

		}



	});

});

</script>

<?php 
$mainframe= JFactory::getApplication();
$mainframe->close();
?>
