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
// get user object
$user = &JFactory::getUser();
//$Itemid = AwdwallHelperUser::getComItemId();
$Itemid = AwdwallHelperUser::getComItemId();

$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$moderator_users = $config->get('moderator_users', '');
$moderator_users=explode(',',$moderator_users);
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$wallModel = new AwdwallModelWall();
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayCommentLike 	= $config->get('displayCommentLike', 1);
		$display_profile_link 	= $config->get('display_profile_link', 1);

if(isset($this->comments[0])){
	foreach($this->comments as $comment){	
	$commenter_id=$this->wallModel->getwallpostowner($comment->reply);
		$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $comment->commenter_id.'&Itemid=' . $Itemid, false);
?>
<div class="whitebox" id="c_block_<?php echo $comment->id;?>">
  <div class="clearfix">
    <div class="subcommentImagebox"><a href="<?php echo $profilelink;?>">
		<img src="<?php echo AwdwallHelperUser::getBigAvatar32($comment->commenter_id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($comment->commenter_id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($comment->commenter_id);?>"  height="32" width="32"  class="awdpostavatar" />	
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
$canlike = $wallModel->getLikeOfMsgOfUser($comment->id,$user->id);
if($displayCommentLike){
	if(!$canlike){
?>
	&nbsp;&nbsp;
	<a href="javascript:void(0);" onclick="openLikeCommentBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=addlikemsg&wid=' . (int)$comment->id . '&cid=' .(int)$user->id.'&tmpl=component';?>', '<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$comment->id . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$comment->id;?>');"><?php echo JText::_('Like');?></a> &nbsp;&nbsp;
<?php
	}
	else
	{
?>
	&nbsp;&nbsp;
	<a href="javascript:void(0);" onclick="deleteLikeCommentBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=deletelikemsg&wid=' . (int)$comment->id . '&cid=' .(int)$user->id.'&tmpl=component';?>', '<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$comment->id . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$comment->id;?>');"><?php echo JText::_('Unlike');?></a> &nbsp;&nbsp;
<?php
	}
}
}
?>
 </span> 
<?php
if((int)$user->id ) {
	$whoLikes = $wallModel->getLikeOfMsg($comment->id);
	if(isset($whoLikes[0])){
?>
            <script type="text/javascript">
			getWhoLikeComment('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$comment->id . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$comment->id;?>','<?php echo (int)$comment->id;?>');
			</script>
<?php 
	} }
?>          
           
          
	<?php if((int)$user->id ) {  if((int)$user->id == (int)$comment->commenter_id || $this->can_delete || in_array($user->id,$moderator_users) || (int)$user->id == (int)$commenter_id){?>	  
		  &nbsp;&nbsp;
		  <a href="javascript:void(0);" onclick="openCommentDeleteBox('<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&task=deletecomment&wid=' . (int)$comment->id . '&tmpl=component', false);?>', <?php echo $comment->id; ?>);"><?php echo JText::_('Delete');?></a> 
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
