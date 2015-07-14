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
?>
<form name="frm_pm_<?php echo $this->cid;?>" id="frm_pm_<?php echo $this->cid;?>" method="post" action="<?php echo 'index.php?option=com_awdwall&view=awdwall&task=addpm';?>" onsubmit="return false;">
<textarea rows="1" cols="1" class="text_comment" style="overflow:auto !important;" id="awd_pm_<?php echo $this->cid;?>" name="awd_pm_<?php echo $this->cid;?>"></textarea>
<div class="post_msg_btn">
<input class="postButton_small" value="<?php echo JText::_('PM');?>"  type="submit" onclick="aPostPM('<?php echo  JURI::base().'index.php?option=com_awdwall&view=awdwall&task=addpm&cid=' . (int)$this->cid . '&tmpl=component';?>', <?php echo $this->wallId;?>);" />
<input class="postButton_small" value="<?php echo JText::_('Cancel');?>"  type="submit" onclick="closePMBox(<?php echo $this->cid;?>);" />
</div>
<input type="hidden" name="pm_receiver_id_<?php echo $this->cid;?>" id="pm_receiver_id_<?php echo $this->cid;?>" value="<?php echo $this->receiverId;?>"/>
<input type="hidden" name="pm_wall_id_<?php echo $this->cid;?>" id="pm_wall_id_<?php echo $this->cid;?>" value="<?php echo $this->wallId;?>"/>
<div><br /><br /><br /></div>
</form>