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
$Itemid = AwdwallHelperUser::getComItemId();
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$display_profile_link 	= $config->get('display_profile_link', 1);


if(isset($this->rows[0])){	
?>
<div class="whitebox"><font style="font-size:11px;" class="likespan"><?php echo $this->totallike.' '.JText::_('People like this');?></font>
  <div class="clearfix">
<?php foreach($this->rows as $row){
//	if($display_profile_link==1)
//	{
//		$profilelink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' .$row->user_id. '&Itemid=' .$Itemid, false);
//	}
//	else
//	{
		$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $row->user_id.'&Itemid=' . $Itemid, false);
//	}

?>
    <div class="subcommentImagebox"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $row->user_id.'&Itemid=' . $Itemid, false);?>">
	<img src="<?php echo AwdwallHelperUser::getBigAvatar32($row->user_id);?>"  title="<?php echo AwdwallHelperUser::getDisplayName($row->user_id);?>" border="0"  height="32" width="32" class="awdpostavatar"/>
	</a>
	</div>
<?php }?>
  </div>
</div>
<br />
<?php }?>