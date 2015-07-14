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
$comment_textarea="awd_comment_".$this->wallId;
?>
<form name="frm_comment_<?php echo $this->wallId;?>" id="frm_comment_<?php echo $this->wallId;?>" method="post" action="<?php echo 'index.php?option=com_awdwall&view=awdwall&task=addmsg';?>" onsubmit="return false;">
<?php echo AwdwallHelperUser::getSmileyicons($comment_textarea);?>
<textarea rows="1" cols="1" class="text_comment" style="overflow:auto !important; display:block;" id="awd_comment_<?php echo $this->wallId;?>" name="awd_comment_<?php echo $this->wallId;?>"></textarea>
<?php echo AwdwallHelperUser::awdshowcommentsmilyicon($comment_textarea);?>
<div class="post_msg_btn">
<input class="postButton_small" value="<?php echo JText::_('Comment');?>"  type="submit" onclick="aPostComment('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=addcomment&cid=' . (int)$this->wallId . '&tmpl=component';?>', <?php echo $this->wallId;?>);" />
<input class="postButton_small" value="<?php echo JText::_('Cancel');?>"  type="submit" onclick="closeCommentBox(<?php echo $this->wallId;?>);" />
</div>
<input type="hidden" name="c_receiver_id_<?php echo $this->wallId;?>" id="c_receiver_id_<?php echo $this->wallId;?>" value="<?php echo $this->receiverId;?>"/>
<input type="hidden" name="c_wall_id_<?php echo $this->wallId;?>" id="c_wall_id_<?php echo $this->wallId;?>" value="<?php echo $this->wallId;?>"/>
<input type="hidden" name="c_isreply_<?php echo $this->wallId;?>" id="c_isreply_<?php echo $this->wallId;?>" value="<?php echo $this->isReply;?>"/>
<div><br /><br /><br /></div>
</form>