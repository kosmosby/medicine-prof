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
//$Itemid = AwdwallHelperUser::getComItemId();
$Itemid = AwdwallHelperUser::getComItemId();
// get user object
$user = &JFactory::getUser();
$pageTitle = addslashes(htmlspecialchars($this->msg));
$pageTitle = str_replace(chr(13), " ", $pageTitle); //remove carriage returns
$pageTitle = str_replace(chr(10), " ", $pageTitle); //remove line feeds 
$video = null;
$pageTitle = $this->msgs[$i]->message;
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');

$displayPm = $config->get('display_pm', 1);
$displayShare = $config->get('display_share', 1);
$displayLike = $config->get('display_like', 1);
$template 		= $config->get('temp', 'default');

	$app = JFactory::getApplication('site');
	$config =  & $app->getParams('com_awdwall');
	$displayCommentLike 	= $config->get('displayCommentLike', 1);
	$display_profile_link 	= $config->get('display_profile_link', 1);

		$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id. '&Itemid=' .$Itemid, false);
		$rprofilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->receiver->id. '&Itemid=' .$Itemid, false);


?>
<div class="awdfullbox clearfix" id="msg_block_<?php echo $this->wid;?>"> 
<span class="tl"></span><span class="bl"></span>
    <div class="rbroundboxleft">
      <div class="mid_content"> 
	  <a href="<?php echo $profilelink;?>">
		<img src="<?php echo AwdwallHelperUser::getBigAvatar51($user->id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($user->id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($user->id);?>"  height="50" width="50" class="awdpostavatar"/></a>
	
	  </div>
    </div>
    <div class="rbroundboxright"> <span class="tl2"></span><span class="bl2"></span><span class="tr2"></span><span class="br2"></span>
      <div class="right_mid_content">
	  <div class="pm_text"><?php echo JText::_('PM From');?> </div>
        <ul class="walltowall">
<?php if($user->id != $this->receiverId && $this->receiverId != 0){?>
		<li><a style="font-size:12px;" href="<?php echo JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $user->id. '&Itemid=' .$Itemid, false);?>" class="john"><?php echo AwdwallHelperUser::getDisplayName($user->id);?></a></li>
	<li><a style="font-size:12px;" href="<?php echo $rprofilelink;?>"><?php echo AwdwallHelperUser::getDisplayName($this->receiver->id);?></a> &nbsp;&nbsp;<?php echo $this->msg;?></li>
<?php }else{ ?>
			<li> <a href="<?php echo $profilelink;?>"><?php echo AwdwallHelperUser::getDisplayName($user->id);?></a>&nbsp;&nbsp;<?php echo $this->msg;?></li>
<?php }?>
        </ul>
		
        <div class="commentinfo"> <span class="wall_date"><?php echo AwdwallHelperUser::getDisplayTime($this->postedTime);?></span>&nbsp;&nbsp;&nbsp; 
		<a href="javascript:void(0);" onclick="showCommentBox('<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&task=getcbox&tmpl=component', false);?>', <?php echo (int)$this->wid;?>, <?php echo $user->id;?>, <?php echo (int)$this->wid;?>);"><?php echo JText::_('Comment');?></a> 
<?php
	$canlike = $this->wallModel->getLikeOfMsgOfUser($this->wid,$user->id);
if($displayLike){
	if(!$canlike){
?>
- <a href="javascript:void(0);" onclick="openLikeMsgBox('<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&task=addlikemsg&wid=' . (int)$this->wid . '&tmpl=component', false);?>', '<?php echo 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$this->msgs[$i]->id . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo $this->wid;?>');"><?php echo JText::_('Like');?></a>
<?php } 
}?>		
		<span id="wholike_box_<?php echo $this->wid;?>">
<?php
	// get who likes of message
	$whoLikes = $this->wallModel->getLikeOfMsg($this->wid);
	if(isset($whoLikes[0])){
?>
<?php /*?>	- <a href="javascript:void(0);" onclick="getWhoLikeMsg('<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$this->wid . '&tmpl=component', false);?>', <?php echo $this->msgs[$i]->id;?>);"><?php echo JText::_('Who likes it');?></a> 
<?php */?><?php }?>		
</span>
<?php if($displayShare) {?>
		- <a rel="nofollow" target="_blank"  href="http://www.facebook.com/share.php?u=<?php echo urlencode(JRoute::_(JURI::root(). 'index.php?option=com_awdwall&view=awdwall&layout=main&wid=' . $this->wid . '&Itemid=' . $Itemid));?>&t=<?php echo urlencode($pageTitle);?>" title="<?php echo JText::_('Share');?>"><?php echo JText::_('Share');?></a>	
<?php }
if($displayPm) {?>
		- <a href="javascript:void(0);" onclick="showPMBox('<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&task=getpmbox&tmpl=component', false);?>', <?php echo $this->wid;?>, <?php echo $user->id;?>, <?php echo $this->wid;?>);"><?php echo JText::_('PM');?></a> -
 <?php }?>		
		<a href="javascript:void(0);" onclick="openMsgDeleteBox('<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&task=deletemsg&wid=' . (int)$this->wid . '&tmpl=component', false);?>', <?php echo $this->wid;?>);"><?php echo JText::_('Delete');?></a>
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
	<div id="c_content_<?php echo $this->wid;?>">
	</div>
      </div>
    </div>
  </div>
</div><script>var posted_wid = document.getElementById("posted_wid").value;if(posted_wid != ''){document.getElementById("posted_wid").value = posted_wid + ',' + <?php echo $this->wid;?>;}else{document.getElementById("posted_wid").value = <?php echo $this->wid;?>;}</script>