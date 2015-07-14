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
$user = &JFactory::getUser();
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$wallModel = new AwdwallModelWall();
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayCommentLike 	= $config->get('displayCommentLike', 1);
		$display_profile_link 	= $config->get('display_profile_link', 1);

//	if($display_profile_link==1)
//	{
//		$profilelink=JRoute::_('index.php?option=com_comprofiler&user=' . $user->id. '&Itemid=' .$Itemid, false);
//	}
//	else
//	{
		$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id.'&Itemid=' . $Itemid, false);
//	}

//$Itemid = AwdwallHelperUser::getComItemId();
// get user object

?>
<div class="whitebox" id="c_block_<?php echo $this->wid;?>">
  <div class="clearfix">
    <div class="subcommentImagebox"><a href="<?php echo $profilelink;?>">
		<img src="<?php echo AwdwallHelperUser::getBigAvatar32($user->id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($user->id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($user->id);?>"  height="32" width="32"  class="awdpostavatar" />
	</a></div>
    <div class="subcomment">
      <div class="rbroundbox">
        <div class="rbtop">
          <div></div>
        </div>
        <div class="rbcontent"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $user->id . '&Itemid=' . $Itemid, false);?>" class="authorlink"><?php echo AwdwallHelperUser::getDisplayName($user->id);?></a>&nbsp;&nbsp;<?php echo nl2br(AwdwallHelperUser::showSmileyicons($this->msg));?>
          <div class="subcommentmenu"> <span class="wall_date"><?php echo AwdwallHelperUser::getDisplayTime($this->postedTime);?></span>
          
<span id="commentlike_<?php echo $this->wid;?>">          
<?php
$canlike = $wallModel->getLikeOfMsgOfUser($this->wid,$user->id);
if($displayCommentLike){
	if(!$canlike){
?>
	&nbsp;&nbsp;
	<a href="javascript:void(0);" onclick="openLikeCommentBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=addlikemsg&wid=' . (int)$this->wid . '&cid=' .(int)$user->id.'&tmpl=component';?>', '<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$this->wid . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$this->wid;?>');"><?php echo JText::_('Like');?></a> &nbsp;&nbsp;
<?php
	}
	else
	{
?>
	&nbsp;&nbsp;
	<a href="javascript:void(0);" onclick="deleteLikeCommentBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=deletelikemsg&wid=' . (int)$this->wid . '&cid=' .(int)$user->id.'&tmpl=component';?>', '<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$this->wid . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$this->wid;?>');"><?php echo JText::_('Unlike');?></a> &nbsp;&nbsp;
<?php
	}
}
?>
 </span> 
<?php
	$whoLikes = $wallModel->getLikeOfMsg($this->wid);
	if(isset($whoLikes[0])){
?>
            <script type="text/javascript">
			getWhoLikeComment('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$this->wid . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$this->wid;?>','<?php echo (int)$this->wid;?>');
			</script>
<?php 
	}
?>  
        
          &nbsp;&nbsp;&nbsp;
		  <a href="javascript:void(0);" onclick="openCommentDeleteBox('<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&task=deletecomment&wid=' . (int)$this->wid . '&tmpl=component', false);?>', <?php echo $this->wid;?>);"><?php echo JText::_('Delete');?></a> </div>
        </div>
        <div class="rbbot">
          <div></div>
        </div>
      </div>
    </div>
  </div>
</div>