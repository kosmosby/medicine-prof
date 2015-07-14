<?php
/**
 * @version 2.5
 * @package JomWALL CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$Itemid = AwdwallHelperUser::getComItemId();
$wallalbumfile = JPATH_SITE . '/components/com_awdjomalbum/awdjomalbum.php';

$jomalbumexist='';
if (file_exists($wallalbumfile)) // if com_awdjomalbum install then only
{
	$jomalbumexist=1;
}

//$Itemid = AwdwallHelperUser::getComItemId();
$db =& JFactory::getDBO();
// get user object
$user = &JFactory::getUser();
$pageTitle = addslashes(htmlspecialchars(AwdwallHelperUser::showSmileyicons($this->msg)));
$pageTitle = str_replace(chr(13), " ", $pageTitle); //remove carriage returns
$pageTitle = str_replace(chr(10), " ", $pageTitle); //remove line feeds 
$video = null;
$pageTitle = AwdwallHelperUser::showSmileyicons($this->msg);
if($this->type == 'video'){
	$video = $this->wallModel->getVideoInfoByWid($this->wid);
	$pageTitle = $video->title;
}
$image = null;
if($this->type == 'image'){
	$image = $this->wallModel->getImageInfoByWid($this->wid);
	$pageTitle = $image->name;
}
$mp3 = null;
if($this->type == 'mp3'){
	$mp3 = $this->wallModel->getMp3InfoByWid($this->wid);
	$pageTitle = $mp3->title;
}
$link = null;
if($this->type == 'link'){
	$link = $this->wallModel->getLinkInfoByWid($this->wid);
	$pageTitle = $link->title;
}
$file = null;
if($this->type == 'file'){
	$file  = $this->wallModel->getFileInfoByWid($this->wid);
	$pageTitle = $file ->title;
}
$jing=null;
if($this->type== 'jing'){
	$jing  = $this->wallModel->getJingInfoByWid($this->wid);
	$pageTitle = $jing ->jing_title;
}
$event=null;
if($this->type == 'event'){
	$event  = $this->wallModel->getEventInfoByWid($this->wid);
	$pageTitle = $event ->title;
}
$trail=null;
if($this->type == 'trail'){
	$trail  = $this->wallModel->getTrailInfoByWid($this->wid);
	$pageTitle = $trail ->trail_title;
}
$article=null;
if($this->type == 'article'){
	$article  = $this->wallModel->getArticleInfoByWid($this->wid);
	$pageTitle = $article ->title;
}
//$config = &JComponentHelper::getParams('com_awdwall');
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');

$displayPm = $config->get('display_pm', 1);
$displayShare = $config->get('display_share', 1);
$displayLike = $config->get('display_like', 1);
$moderator_users = $config->get('moderator_users', '');
$moderator_users=explode(',',$moderator_users);
$getrealtimecomment = $config->get('getrealtimecomment', 0);
$fbshareaapid= $config->get('fbshareaapid', '');

$template 		= $config->get('temp', 'default');
if($this->display_profile_link==1)
{
	$profilelink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $user->id . '&Itemid=' . AwdwallHelperUser::getJsItemId(), false);
	$rprofilelink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' .$this->receiver->id . '&Itemid=' . AwdwallHelperUser::getJsItemId(), false);
}
else
{
	$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&Itemid=' . $Itemid, false);
	$rprofilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' .$this->receiver->id.'&Itemid=' . $Itemid, false);
}

?>
<div class="awdfullbox clearfix" id="msg_block_<?php echo $this->wid;?>"> 
<span class="tl"></span><span class="bl"></span>
    <div class="rbroundboxleft">
      <div class="mid_content"> 	
	  <?php if(isset($this->owner) && $this->owner){?>
	    <a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->grpInfo->id . '&Itemid=' . $Itemid, false);?>">
		<img src="<?php echo AwdwallHelperUser::getBigGrpImg51($this->grpInfo->image, $this->grpInfo->id);?>" alt="<?php echo $this->grpInfo->title;?>" title="<?php echo $this->grpInfo->title;?>"/></a>
	  <?php }else{?>
	    <a href="<?php echo $profilelink;?>" > 
		<img src="<?php echo AwdwallHelperUser::getBigAvatar51($user->id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($user->id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($user->id);?>"  height="50" width="50"  class="awdpostavatar" />
        <?php 
	if(AwdwallHelperUser::isSocialfeed($this->wid)) 
	{?>
		<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo AwdwallHelperUser::isSocialfeed($this->wid); ?>.png" class="post_type_icon"  />
	<?php }	?>
            </a>
	  <?php }?>
	  <br />
	  <?php if(!$this->owner): ?>
	  <?php if($this->display_profile_link==1):?>
	<a style="font-size:11px;" href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id . '&Itemid=' . $Itemid, false);?>"><?php echo JText::_('Main wall profile');?></a>
		<?php endif; ?>
		<?php endif; ?>
	 </div>
    </div>
    <div class="rbroundboxright"> <span class="tl2"></span><span class="bl2"></span><span class="tr2"></span><span class="br2"></span>
      <div class="right_mid_content">
        <ul class="walltowall">
<?php if($user->id != $this->receiverId && $this->receiverId != 0 && $this->layout != 'mywall'){?>
	<li><?php echo JText::sprintf('A WRITE ON B WALL', '<a style="font-size:12px;" href="' . $profilelink . '" >' . AwdwallHelperUser::getDisplayName($user->id) . '</a>', '<a style="font-size:12px;" href="' . $rprofilelink. '">' . AwdwallHelperUser::getDisplayName($this->receiver->id). "'s" . '</a>');?>&nbsp;&nbsp;<?php echo nl2br(AwdwallHelperUser::showSmileyicons($this->msg));?></li>
<?php }elseif($user->id != $this->receiverId && $this->receiverId != 0 && $this->layout == 'mywall'){?>
		<li><a style="font-size:12px;" href="<?php echo $profilelink;?>"><?php echo AwdwallHelperUser::getDisplayName($user->id);?></a>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo nl2br(AwdwallHelperUser::showSmileyicons($this->msg));?></span></li>
	<!--li><a style="font-size:12px;" href="<?php //echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->receiver->id . '&Itemid=' . $Itemid, false);?>"><?php //echo AwdwallHelperUser::getDisplayName($this->receiver->id);?></a></li-->
<?php }else{ ?>
		<?php if(isset($this->owner) && $this->owner){?>
			<li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $this->grpInfo->id . '&Itemid=' . $Itemid, false);?>"><?php echo $this->grpInfo->title;?></a>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo nl2br(AwdwallHelperUser::showSmileyicons($this->msg));?></span></li>
		 <?php }else{?>
			<li><a href="<?php echo $profilelink;?>"><?php echo AwdwallHelperUser::getDisplayName($user->id);?></a>&nbsp;&nbsp;<span class="awdmessagetxt"><?php echo nl2br(AwdwallHelperUser::showSmileyicons($this->msg));?></span></li>
		 <?php }?>
<?php }?>
        </ul>
		
        <div class="commentinfo"> 
	<!-- start image block -->
<?php if($this->type == 'image'){?>
<div class="whitebox video">
	<div id="video_block_<?php echo $this->wid;?>">
	<div class="clearfix">
            <div class="imagebox">
			<?php
			$pid=$image->id;
			$image_uid=$this->receiverId;
			//$imglink=JRoute::_('index.php?option=com_awdjomalbum&view=awdwallimagelist&wuid='.$image_uid.'&pid='.$pid.'&Itemid='.$Itemid,false);	//echo $imglink;
			$imglink=JRoute::_("index.php?option=com_awdjomalbum&view=awdwallimagelist&tmpl=component&wuid=".$image_uid."&pid=".$pid."&Itemid=".AwdwallHelperUser::getComItemId());

			if($jomalbumexist)
			{?>
			<a href="<?php echo $imglink;?>" class="awdiframe">
				<img src="<?php echo JURI::base();?>images/<?php echo $this->receiverId;?>/thumb/<?php echo $image->path;?>" title="<?php echo $image->name;?>" alt="<?php echo $image->name;?>" /> <br />
			</a>
			<?php }
			else {?>
            <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>images/<?php echo $this->receiverId;?>/original/<?php echo $image->path;?>', '<?php echo $image->name;?>', '<?php echo $image->name;?>');" title="<?php echo $image->name;?>">
				<img src="<?php echo JURI::base();?>images/<?php echo $this->receiverId;?>/thumb/<?php echo $image->path;?>" title="<?php echo $image->name;?>" alt="<?php echo $image->name;?>" /> <br />
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

<?php if($this->type == 'text'){?>
		<?php
			$query = "SELECT * FROM #__awd_wall_videos WHERE wall_id='".$this->wid."'";
			$db->setQuery($query);
			$linkvideo = $db->loadObjectList();
			$video = $linkvideo[0];
		?>
		<?php if($video->id){?>
			<div class="whitebox video">
			<div style="overflow:hidden;" id="video_block_<?php echo $this->wid;?>">
			<div class="clearfix">
					<div class="imagebox">
		<?php

		if($this->hwdvideoshare)
		{
			$query = "SELECT hwdviodeo_id FROM #__awd_wall_videos_hwd WHERE wall_id='".$this->wid."'";
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
					<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-videos.png" title="<?php echo JText::_('View video');?>" alt="<?php echo JText::_('View video');?>o" /> 
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
    <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $this->wid;?>&iframe=true&tmpl=component', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>" ><?php echo JText::_('View video');?></a>
    
    <?php
    }
        }else{	
            if($video->type == 'youtube'){
    
    ?>
        <a class="show_vide" rev="video_<?php echo $this->wid;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.youtube.com/v/<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
        
    <?php
    
            }elseif($video->type == 'vimeo'){	
    ?>
        <a  class="show_vide" rev="video_<?php echo $this->wid;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://vimeo.com/moogaloop.swf?clip_id=<?php echo $video->video_id?>&amp;autoplay=1" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php		
    
            }elseif($video->type == 'myspace'){
    ?>	
    
        <a  class="show_vide" rev="video_<?php echo $this->wid;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://mediaservices.myspace.com/services/media/embed.aspx/m=<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }elseif($video->type == 'metacafe'){
    
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->wid;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.metacafe.com/fplayer/<?php echo $video->video_id;?>.swf" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }elseif($video->type == 'howcast'){
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->wid;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.howcast.com/flash/howcast_player.swf?file=<?php echo $video->video_id;?>&amp;theme=black" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
    
            }elseif($video->type == 'blip'){
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->wid;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://a.blip.tv/scripts/flash/stratos.swf#file=http://blip.tv/rss/flash/<?php echo $video->video_id;?>&autostart=true&showinfo=false" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }else
            {
        ?>
    <a href="javascript:void(0);" onclick="showlinevideo('<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $this->wid;?>&tmpl=componen','<?php echo $this->wid;?>')" ><?php echo JText::_('View video');?></a>
        
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
			  <div style="width:455px; float: left; margin-top: 16px;" id="video_<?php echo $this->wid;?>"></div>
			</div>
		</div>
		<?php } ?>


		<!-- start link block -->
		<?php 
			$query = "SELECT * FROM #__awd_wall_links WHERE wall_id='".$this->wid."'";
			$db->setQuery($query);
			$checklink = $db->loadObjectList();
			$link = $checklink[0];
				$str=$link->link;
				$str=str_replace('www.','',$str);
				$str=str_replace('http://','',$str);
				$str='http://www.'.$str;
				//$link->link=$str;
		?>
		<?php if($link->id){ ?>
		<div class="whitebox video">
			<div id="video_block_<?php echo $this->wid;?>">
			<div class="clearfix">
		<?php if($link->path != ''){?>
				<div class="imagebox"><img src="<?php echo JURI::base();?>images/<?php echo $this->receiverId;?>/thumb/<?php echo $link->path;?>" title="<?php echo $link->title;?>" alt="<?php echo $link->description;?>"  /> <br />
					<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-links.png" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /> 
		<?php 
			if($this->imageLightbox){
		?>
		<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>images/<?php echo $this->receiverId;?>/original/<?php echo $link->path;?>', '<?php echo $link->name;?>', '<?php echo $link->description;?>');" title="<?php echo $link->name;?>"><?php echo JText::_('View link');?></a>
		<?php
			}else
				echo '<a href="' . JRoute::_('index.php?option=com_awdwall&task=viewimage&wuid=' . $this->receiverId . '&imageid=' . $link->id . '&Itemid=' . $Itemid, false) . '" >' . JText::_('View link') . '</a>';
		?>		 
			</div>
			<div class="maincomment">
				<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->title;?></a></h3>
				<p><?php echo $link->description;?></p>             
			</div>
		<?php }else{ ?>
			<div class="maincomment_noimg">
					<?php if($link->link_img){?>
					<div class="maincomment_noimg_left">
						<a href="<?php echo $link->link;?>" alt=""><img src="<?php echo $link->link_img;?>" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /></a>

					</div>
					<?php } ?>
					
					<div class="maincomment_noimg_right">
						<h3><a href="<?php echo $link->link;?>" target="blank"><?php echo $link->title;?></a></h3>
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

<!-- end image block -->
<!-- start video block -->
<?php if($this->type == 'video'){?>
	<div class="whitebox video">
	<div style="overflow:hidden;" id="video_block_<?php echo $this->wid;?>">
	<div class="clearfix">
            <div class="imagebox">
<?php

if($this->hwdvideoshare)
{
	$query = "SELECT hwdviodeo_id FROM #__awd_wall_videos_hwd WHERE wall_id='".$this->wid."'";
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
?>	<br />
            <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-videos.png" title="<?php echo JText::_('View video');?>" alt="<?php echo JText::_('View video');?>o" /> 
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
    <a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $this->wid;?>&iframe=true&tmpl=component', '<?php $video->title;?>', '<?php $video->description;?>')" title="<?php $video->title;?>" ><?php echo JText::_('View video');?></a>
    
    <?php
    }
        }else{	
            if($video->type == 'youtube'){
    
    ?>
        <a class="show_vide" rev="video_<?php echo $this->wid;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.youtube.com/v/<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
        
    <?php
    
            }elseif($video->type == 'vimeo'){	
    ?>
        <a  class="show_vide" rev="video_<?php echo $this->wid;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://vimeo.com/moogaloop.swf?clip_id=<?php echo $video->video_id?>&amp;autoplay=1" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php		
    
            }elseif($video->type == 'myspace'){
    ?>	
    
        <a  class="show_vide" rev="video_<?php echo $this->wid;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://mediaservices.myspace.com/services/media/embed.aspx/m=<?php echo $video->video_id;?>" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }elseif($video->type == 'metacafe'){
    
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->wid;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.metacafe.com/fplayer/<?php echo $video->video_id;?>.swf" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }elseif($video->type == 'howcast'){
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->wid;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://www.howcast.com/flash/howcast_player.swf?file=<?php echo $video->video_id;?>&amp;theme=black" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
    
            }elseif($video->type == 'blip'){
    ?>	
        <a  class="show_vide" rev="video_<?php echo $this->wid;?>" alt="<?php echo $video->type; ?>" onmouseover="show_video();" href="javascript:void(0);" rel="http://a.blip.tv/scripts/flash/stratos.swf#file=http://blip.tv/rss/flash/<?php echo $video->video_id;?>&autostart=true&showinfo=false" title="<?php $video->title;?>"><?php echo JText::_('View video');?></a>
    
    <?php
    
            }else
            {
        ?>
    <a href="javascript:void(0);" onclick="showlinevideo('<?php echo JURI::base();?>index.php?option=com_awdwall&task=showvideo&wallid=<?php echo $this->wid;?>&tmpl=componen','<?php echo $this->wid;?>')" ><?php echo JText::_('View video');?></a>
        
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
	  <div style="width:455px; float: left; margin-top: 16px;" id="video_<?php echo $this->wid;?>"></div>
	</div>
</div>
<?php }?>
<!-- end video block -->
<!-- start link block -->
<?php if($this->type == 'link'){
				$str=$link->link;
				$str=str_replace('www.','',$str);
				$str=str_replace('http://','',$str);
				$str=str_replace('https://','',$str);
				$str='http://www.'.$str;
				// $link->link=$str;
?>
<div class="whitebox video">
	<div id="video_block_<?php echo $this->wid;?>">
	<div class="clearfix">
<?php if($link->path != ''){?>
        <div class="imagebox"><img src="<?php echo JURI::base();?>images/<?php echo $this->receiverId;?>/thumb/<?php echo $link->path;?>" title="<?php echo $link->title;?>" alt="<?php echo $link->description;?>"  /> <br />
            <img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-images.png" title="<?php echo JText::_('View link');?>" alt="<?php echo JText::_('View link');?>" /> 
<?php 
	if($this->imageLightbox){
?>
<a href="javascript:void(0);" onclick="jQuery.prettyPhoto.open('<?php echo JURI::base();?>images/<?php echo $this->receiverId;?>/original/<?php echo $link->path;?>', '<?php echo $link->name;?>', '<?php echo $link->description;?>');" title="<?php echo $link->name;?>"><?php echo JText::_('View link');?></a>
<?php
	}else
		echo '<a href="' . JRoute::_('index.php?option=com_awdwall&task=viewimage&wuid=' . $this->receiverId . '&imageid=' . $link->id . '&Itemid=' . $Itemid, false) . '" >' . JText::_('View link') . '</a>';
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
<?php if($this->type == 'mp3'){

		$parsedVideoLink	= parse_url($mp3->path);
		preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $parsedVideoLink['host'], $matches);
		$domain		= $matches['domain'];
		
		if($domain!='soundcloud.com')
		{

?>
<div class="whitebox video">
	<div id="video_block_<?php echo $this->wid;?>">
	<div class="clearfix">
    <div class="imagebox">
	<img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-music.png" title="<?php echo JText::_('Music');?>" alt="<?php echo JText::_('Music');?>" style="float:left;" />
	<h3 style="font-size:13px;font-weight:bold;margin:0;padding:3px 0px 6px 5px;"> &nbsp;<?php echo $mp3->title;?></h3>	
	<object width="200" height="24" id="audioplayer1" data="<?php echo JURI::base();?>components/com_awdwall/js/player.swf" type="application/x-shockwave-flash">
<param value="<?php echo JURI::base();?>components/com_awdwall/js/player.swf" name="movie">
<param value="playerID=1&amp;soundFile=<?php echo JURI::base();?>images/mp3/<?php echo $this->receiverId;?>/<?php echo $mp3->path;?>" name="FlashVars">
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
if($this->type =='jing')
{
?>

<div class="whitebox video">
	<div style="overflow:hidden;" id="jing_block_<?php echo $this->wid;?>">
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





<?php /*?><script language="javascript">
getJingData('<?php echo $jing->id;?>');
</script>
<div id="jing<?php echo $jing->id;?>"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" title="<?php echo JText::_('Loading');?>" alt="<?php echo JText::_('Loading');?>" border="0" /></div>
<br />
<a href="<?php echo $jing->jing_link;?>" title="<?php echo $jing->jing_title;?>" target="_blank"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/icon-jing.png" title="<?php echo JText::_('Jing');?>" alt="<?php echo JText::_('Jing');?>" border="0" height="14" />&nbsp;&nbsp;&nbsp;<?php echo JText::_('View Jing');?></a>
<?php */?>
<?php 
}
?>
<!-- end of jing block -->
<!--start event block-->
<?php if($this->type == 'event'){
$startd=explode("\n",$event->start_time);
$endd=explode("\n",$event->end_time);
$starttime=$startd[1].' '.$startd[2].' '.date('l, j-M-y',strtotime($startd[0]));
$endtime=$endd[1].' '.$endd[2].' '.date('l, j-M-y',strtotime($endd[0]));
?>
<div class="whitebox event">

	<div id="event_block_<?php echo $this->wid;?>">

	<div class="clearfix">

    <div class="imagebox" style="margin-top:10px; margin-bottom:10px;">
	
<?php
	if($event->image)
	{?>
	<img src="<?php echo JURI::base();?>images/awd_events/<?php echo $this->receiverId;?>/thumb/<?php echo $event->image;?>" title="<?php echo $event->title;?>" alt="<?php echo $event->title;?>" /></a>
<?php }
	else {?>
	<img src="<?php echo JURI::base();?>components/com_awdwall/images/event.png" title="<?php echo $event->title;?>" alt="<?php echo $event->title;?>" />
	<?php }?>

	</div>
	
		<div class="maincomment" style="margin-top:10px;">

			<h3><?php echo $event->title;?></h3>
			<p><font><?php echo JText::_('Location');?>: </font><?php echo $event->location;?></p>   
			<font><?php echo JText::_('Time');?>: </font>  
			<p><font><?php echo JText::_('Start');?>: </font><?php echo $starttime;?><br />
			<font><?php echo JText::_('End');?>: &nbsp;</font><?php echo $endtime;?></p>   
			<p><?php echo nl2br($event->description);?></p>  
			<p><?php echo JText::_('Are you coming ?');?>
			<select name="attend_event" id="attend_event" onchange="attendEvent('<?php echo 
 'index.php?option=com_awdwall&view=awdwall&task=addEventAttend&wid=' . (int)$this->wid . '&cid=' .(int)$this->msgs[$i]->commenter_id.'&eventId=' . (int)$event->id .'&tmpl=component';?>', '<?php echo  'index.php?option=com_awdwall&view=awdwall&task=getEventAttend&wid=' . (int)$this->wid . '&tmpl=component&Itemid='.$Itemid;?>',<?php echo (int)$this->wid;?>);">
				<option value=""><?php echo JText::_('Select');?></option>
				<option value="1"><?php echo JText::_('JYES');?></option>
				<option value="0"><?php echo JText::_('JNO');?></option>
			</select>
			</p>  
		 </div>

	</div>

	</div>

</div>
<?php }?>
	<!--end event block-->

<!-- start article block -->

<?php if($this->type == 'article')
{?>

<div class="whitebox article">

	<div id="article_block_<?php echo $this->wid;?>">

	<div class="clearfix">
	
<?php
	if($article->image)
	{?>
    <div class="imagebox" style="margin-top:10px; margin-bottom:10px;">
	
	<a href="<?php echo JURI::base();?>index.php?option=com_content&view=article&id=<?php echo $article->article_id;?>&Itemid=<?php echo $Itemid;?>" >
		<img src="<?php echo JURI::base();?>images/awd_articles/<?php echo $this->receiverId;?>/thumb/<?php echo $article->image;?>" title="<?php echo $article->title;?>" alt="<?php echo $article->title;?>" style="max-height:84px; max-width:112px;" />
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
if($this->type == 'trail')
{
?>
<a href="<?php echo $trail->trail_link;?>" target="_blank" style="font-size:12px;"><?php echo $trail->trail_title;?></a><br><br><iframe src="http://www.everytrail.com/iframe2.php?trip_id=<?php echo str_replace('http://www.everytrail.com/view_trip.php?trip_id=','',$trail->trail_link);?>&width=400&height=300" marginheight="0" marginwidth="0" frameborder="0" scrolling="no" width="400" height="300"></iframe>
<?php 
}
?>
<!-- end of trail block -->
	
<!-- start file block -->
<?php if($this->type == 'file'){?>
<div class="whitebox video">
	<div id="video_block_<?php echo $this->wid;?>">
	<div class="clearfix">
    <div class="imagebox"><a href="<?php echo JURI::base();?>images/awdfiles/<?php echo $this->receiverId;?>/<?php echo $file->path;?>" target="_blank">
	<img src="<?php echo JURI::base();?>components/com_awdwall/images/file.png" title="<?php echo JText::_('Download');?>" alt="<?php echo JText::_('Download');?>" /></a>
	<br /><span style="padding-left:12px;font-weight:bold;color:#308CB6;"><a href="<?php echo JURI::base();?>images/awdfiles/<?php echo $this->receiverId;?>/<?php echo $file->path;?>" target="_blank"><?php echo JText::_('Download');?></a></span>
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
<span class="wall_date"><?php if(AwdwallHelperUser::isSocialfeed($this->wid)) 
	{echo '<img src="components/com_awdwall/images/'.AwdwallHelperUser::isSocialfeed($this->wid).'_date.png" />&nbsp;&nbsp;'.JText::_('via').'&nbsp;'.ucfirst(AwdwallHelperUser::isSocialfeed($this->wid)).'&nbsp;&nbsp;';}
?><?php echo AwdwallHelperUser::getDisplayTime($this->postedTime);?></span>&nbsp;&nbsp;&nbsp; 
		<a href="javascript:void(0);" onclick="showCommentBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getcbox&tmpl=component';?>', <?php echo (int)$this->wid;?>, <?php echo $user->id;?>, <?php echo (int)$this->wid;?>);"><?php echo JText::_('Comment');?></a>
<?php
	$canlike = $this->wallModel->getLikeOfMsgOfUser($this->wid,$user->id);
if($displayLike) {	
	if(!$canlike){
?>
- <a href="javascript:void(0);" onclick="openLikeMsgBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=addlikemsg&wid=' . (int)$this->wid . '&tmpl=component';?>', '<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' .  (int)$this->wid . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo $this->wid;?>');"><?php echo JText::_('Like');?></a>
<?php } 
}?>
		<span id="wholike_box_<?php echo $this->wid;?>">
<?php
	// get who likes of message
	$whoLikes = $this->wallModel->getLikeOfMsg($this->wid);
	if(isset($whoLikes[0])){
?>
<?php /*?>	- <a href="javascript:void(0);" onclick="getWhoLikeMsg('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$this->wid . '&tmpl=component';?>', <?php echo $this->wid;?>);"><?php echo JText::_('Who likes it');?></a> 
<?php */?><?php }?>		
</span>
<?php
$sharepageTitle = $this->msg;
$sharepageTitle = addslashes(htmlspecialchars($sharepageTitle));
$sharepageTitle = str_replace(chr(13), " ", $sharepageTitle); //remove carriage returns
$sharepageTitle = str_replace(chr(10), " ", $sharepageTitle); //remove line feeds 
$facebooksharepageTitle=$sharepageTitle;
$facebookdesc=$sharepageTitle;
$imageurl='';
if($this->type == 'file'){
$imageurl=JURI::base().'components/com_awdwall/images/file.png';
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$pageTitle;
}
if($this->type == 'video'){
$imageurl=JURI::base().'images/'.$video->thumb;
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$video->description;
}
if($this->type == 'image'){
$imageurl=JURI::base().'images/'.$user->id.'/thumb/'.$image->path;
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$image->description;
}
if($this->type == 'link'){
 if($link->link_img != ''){
$imageurl=$link->link_img;
$facebooksharepageTitle=$pageTitle;
$facebookdesc=$link->description;
}
}
if($this->type == 'event'){
if($event->image){
	$imageurl=JURI::base().'images/awd_events/'.$user->id.'/thumb/'.$event->image;
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
$facebookshareurl='https://www.facebook.com/dialog/feed?app_id='.$fbshareaapid.'&link='.urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->wid . '&Itemid=' . $Itemid)).'&
  &name='.urlencode($facebooksharepageTitle).'&picture='.$imageurl.'&
  description='.urlencode(strip_tags($facebookdesc)).'&redirect_uri='.urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->wid . '&Itemid=' . $Itemid));
}
else
{
$facebookshareurl='https://www.facebook.com/dialog/feed?app_id='.$fbshareaapid.'&link='.urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->wid . '&Itemid=' . $Itemid)).'&
  &name='.urlencode($facebooksharepageTitle).'&
  description='.urlencode(strip_tags($facebookdesc)).'&redirect_uri='.urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->wid . '&Itemid=' . $Itemid));
}}
?>

<?php if($displayShare) {?>
		- <a class="ashare" rev="hid_<?php echo $this->wid; ?>" rel="ashare_<?php echo $this->wid; ?>" onmouseover="show_share();" href="javascript:void(0);"><?php echo JText::_('Share');?></a>
<?php }?>
		
	<div class="ashare" style="display:none;" id="ashare_<?php echo $this->wid; ?>">
		<div class="share-top"><div></div></div>
		<a href="javascript:void(0);" onclick="hidden_share();" style="float: right; font-weight: bold; color: rgb(170, 170, 170); margin-right: 7px;">X</a>
		<div class="share-center">
        <?php if($fbshareaapid){?>
			<a rel="nofollow" target="_blank"  href="<?php echo $facebookshareurl;?>" title="<?php echo JText::_('facebook');?>"><?php echo JText::_('Facebook');?></a>
			<br/>
            <?php } ?>
			<a rel="nofollow" target="_blank"  href="http://twitter.com/share?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->wid . '&Itemid=' . $Itemid));?>&text=<?php echo urlencode($pageTitle);?>" title="<?php echo JText::_('twitter');?>"><?php echo JText::_('Twitter');?></a>
			<br/>
			<a rel="nofollow" target="_blank"  href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->wid . '&Itemid=' . $Itemid));?>&title=<?php echo urlencode($pageTitle);?>" title="<?php echo JText::_('LinkedIn');?>"><?php echo JText::_('LinkedIn');?></a>
			<br/>
			<a target="_blank" href="https://plus.google.com/share?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->wid . '&Itemid=' . $Itemid));?>"><?php echo JText::_('Google Plus');?></a>
			<br/>
			<a   target="_blank" href="http://digg.com/submit?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->wid . '&Itemid=' . $Itemid));?>"><?php echo JText::_('Digg');?></a>
			<br/>
			<a  target="_blank" href="http://www.stumbleupon.com/submit?url=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->wid . '&Itemid=' . $Itemid));?>&amp;title=<?php echo urlencode($pageTitle);?>"><?php echo JText::_('Stumbleupon');?></a>
		</div>
		<div class="share-bottom"><div></div></div>
	</div>
<?php if($displayPm){?>		
		- <a href="javascript:void(0);" onclick="showPMBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getpmbox&tmpl=component';?>', <?php echo $this->wid;?>, <?php echo $user->id;?>, <?php echo $this->wid;?>);"><?php echo JText::_('PM');?></a> 
<?php }?>
		- <a href="javascript:void(0);" onclick="openMsgDeleteBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=deletemsg&wid=' . (int)$this->wid . '&tmpl=component';?>', <?php echo $this->wid;?>);"><?php echo JText::_('Delete');?></a>
		<?php if(AwdwallHelperUser::checkOnline($user->id)){?>
	<img src="<?php echo AwdwallHelperUser::getOnlineIcon();?>" title="<?php echo JText::_('Online');?>" alt="<?php echo JText::_('Online');?>" style="vertical-align:middle;"/>
	<?php }?>	
		<p></p>
  <!-- start like box -->
<div id="like_<?php echo (int)$this->wid;?>">
</div>
<?php
	$whoLikes = $this->wallModel->getLikeOfMsg($this->wid);
	if(isset($whoLikes[0])){
?>
<script type="text/javascript">
getWhoLikeMsg('<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$this->wid . '&tmpl=component&Itemid='.$Itemid;?>', <?php echo $this->wid;?>);
</script>
<?php } ?>

	<!-- end like box -->
	<!-- start event-attend box -->
<div id="event_<?php echo $this->wid;?>">

</div>

	<!-- end event-attend box -->
	<!--start pm box -->
	<div id="pm_<?php echo (int)$this->wid;?>" class="comment_text">
	<span id="pm_loader_<?php echo (int)$this->wid;?>" style="display:none;margin:10px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
	</div>
	<!--end pm box -->
	<!--start comment box -->
	<div id="c_<?php echo (int)$this->wid;?>" class="comment_text">
	<span id="c_loader_<?php echo $this->wid;?>" style="display:none;margin:10px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
	</div>
	<!--end comment box -->
	<!--start comment block -->
	<div id="c_block_<?php echo (int)$this->wid;?>">
	</div>
	<!--end comment block-->
</div>
<?php if($getrealtimecomment==1){?>
<script type="text/javascript">
	getrealtimecomment(<?php echo $this->wid;?>);
</script>
<?php } ?>
	<div id="c_content_<?php echo $this->wid;?>">
	</div>
      </div>
    </div>
  </div>
<script type="text/javascript" >
jQuery(document).ready(function(){
	jQuery("a[rel^='prettyPhotoIframe']").prettyPhoto();
	jQuery(".awdiframe").colorbox({
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

var posted_wid = document.getElementById("posted_wid").value;if(posted_wid != ''){document.getElementById("posted_wid").value = posted_wid + ',' + <?php echo $this->wid;?>;}else{document.getElementById("posted_wid").value = <?php echo $this->wid;?>;}
</script>
</div>
