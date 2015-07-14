<?php
/**
 * @ version 2.4
 * @package JomWALL -Joomla
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$Itemid = AwdwallHelperUser::getComItemId();
//$Itemid = $_REQUEST['Itemid'];
// get user object
$user = &JFactory::getUser();

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
		$profilelink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $user->id. '&Itemid=' .$Itemid, false);
//	}

?>
<div class="whitebox" id="c_block_<?php echo $this->wid;?>">
  <div class="clearfix">
    <div class="subcommentImagebox"><a href="<?php echo $profilelink;?>">
		<img src="<?php echo AwdwallHelperUser::getBigAvatar32($user->id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($user->id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($user->id);?>"  height="32" width="32" class="awdpostavatar"/>	
	</a></div>
    <div class="subcomment">
      <div class="rbroundbox">
        <div class="rbtop">
          <div></div>
        </div>
        <div class="rbcontent"><div class="pm_text"><?php echo JText::_('Private Message Sent');?>! </div><br /> <a href="<?php echo JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $user->id. '&Itemid=' .$Itemid, false);?>" class="authorlink"><?php echo AwdwallHelperUser::getDisplayName($user->id);?></a>&nbsp;&nbsp;<?php echo $this->msg;?>
          <div class="subcommentmenu"> <span class="wall_date"><?php echo AwdwallHelperUser::getDisplayTime($this->postedTime);?></span>&nbsp;&nbsp;&nbsp;
		  <a href="javascript:void(0);" onclick="openPMDeleteBox('<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&task=deletecomment&wid=' . (int)$this->wid . '&tmpl=component', false);?>', <?php echo $this->wid;?>);"><?php echo JText::_('Delete');?></a> </div>
        </div>
        <div class="rbbot">
          <div></div>
        </div>
      </div>
    </div>
  </div>
</div>