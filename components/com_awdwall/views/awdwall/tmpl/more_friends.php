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
//require_once(JPATH_COMPONENT . DS . 'js' . DS . 'include.php');
//$Itemid = AwdwallHelperUser::getComItemId();
$Itemid = AwdwallHelperUser::getComItemId();
// get user object
$user = &JFactory::getUser();
$friendJsUrl = 'index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid;
$groupsUrl = JRoute::_('index.php?option=com_awdwall&task=groups&Itemid=' . $Itemid, false);
//$config = &JComponentHelper::getParams('com_awdwall');
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
$width 			= $config->get('width', 725);
$display_name 	= $config->get('display_name', 1);
$display_profile_link 	= $config->get('display_profile_link', 1);
if(isset($this->friends[0])){
	$n = count($this->friends);
	for($i = 0; $i < $n; $i++){
	
	if($display_profile_link==1)
	{
		$profilelink=JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $this->friends[$i]->connect_to . '&Itemid=' .AwdwallHelperUser::getJsItemId(), false);
	}
	else
	{
		$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->friends[$i]->connect_to.'&Itemid=' . $Itemid, false);
	}
		
?>  <a name="here<?php echo $this->friends[$i]->connection_id;?>"></a>
  <div class="awdfullbox clearfix" id="msg_block_<?php echo $this->friends[$i]->connection_id;?>"><span class="tl"></span><span class="bl"></span>
    <div class="rbroundboxleft">
      <div class="mid_content"><a href="<?php echo $profilelink;?>"><img src="<?php echo AwdwallHelperUser::getBigAvatar51($this->friends[$i]->connect_to);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($this->friends[$i]->connect_to);?>" title="<?php echo AwdwallHelperUser::getDisplayName($this->friends[$i]->connect_to);?>"  height="50" width="50" class="awdpostavatar"/></a> 
	 
	  </div>
    </div>
    <div class="rbroundboxright"> <span class="tl2"></span><span class="bl2"></span><span class="tr2"></span><span class="br2"></span>
    <div class="right_mid_content">
	 <ul class="walltowall">
	<li><a style="font-size:12px;" href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $this->friends[$i]->connect_to . '&Itemid=' . $Itemid, false);?>"><?php echo AwdwallHelperUser::getDisplayName($this->friends[$i]->connect_to);?></a>&nbsp;&nbsp;
	<?php if($this->friends[$i]->status == '0' && $this->friends[$i]->pending == '0'){?>
	<span id="friend_<?php echo $this->friends[$i]->connection_id;?>"><?php echo JText::_('Wants to be your friend');?>&nbsp;&nbsp;<a href="javascript:void(0);" onclick="denyFriend('<?php echo JRoute::_('index.php?option=com_awdwall&task=denyfriend&user_to=' . $this->friends[$i]->connect_to);?>', '<?php echo $this->friends[$i]->connection_id;?>');" class="deny_friend"><?php echo JText::_('Deny');?></a> <?php echo JText::_('or');?> <a href="javascript:void(0);" onclick="acceptFriend('<?php echo JRoute::_('index.php?option=com_awdwall&task=acceptfriend&user_to=' . $this->friends[$i]->connect_to);?>', '<?php echo $this->friends[$i]->connection_id;?>');" class="accept_friend"><?php echo JText::_('Accept');?></a></span>
	<?php }elseif($this->friends[$i]->status == '1' && $this->friends[$i]->pending == '1'){?>
	
	<?php echo JText::_('Waiting for authorization');?>
	
	<?php }?>
	</li>
	</ul>
  <div class="commentinfo"> <span class="wall_date"><?php echo AwdwallHelperUser::getDisplayTime($this->friends[$i]->created);?> </span>&nbsp;&nbsp;
<?php if((int)$user->id){?>
<a href="javascript:void(0);" onclick="showPMBox('<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&task=getpmbox&tmpl=component', false);?>', <?php echo $this->friends[$i]->connection_id;?>, <?php echo $this->friends[$i]->connect_to;?>, <?php echo $this->friends[$i]->connection_id;?>);"><?php echo JText::_('PM');?></a>
<?php if(AwdwallHelperUser::checkOnline($this->friends[$i]->connect_to)){?>
	<img src="<?php echo AwdwallHelperUser::getOnlineIcon();?>" title="<?php echo JText::_('Online');?>" alt="<?php echo JText::_('Online');?>" style="vertical-align:middle;"/>
<?php }?>
<?php }?><p></p>
<!--start pm box -->
	<div id="pm_<?php echo $this->friends[$i]->connection_id;?>" class="comment_text">
	<span id="pm_loader_<?php echo $this->friends[$i]->connection_id;?>" style="display:none;margin:10px;margin-top:10px;"><img src="<?php echo JURI::base();?>components/com_awdwall/images/<?php echo $template;?>/ajax-loader.gif" /></span>
	</div>
	<!--end pm box -->
		</div>
	  </div>
    </div>
  </div>
 <!-- end block msg -->
<?php 
	}// end for 
}// end if parent
?>
