 <?php
/**
 * @version 2.4
 * @package JomWALL-CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
// include js and css
require_once(JPATH_COMPONENT . DS . 'js' . DS . 'include.php');
// get user object
$user = &JFactory::getUser();
//$Itemid = AwdwallHelperUser::getComItemId();
$Itemid = AwdwallHelperUser::getComItemId();
$friendJsUrl = 'index.php?option=com_awdwall&task=friends&Itemid=' . $Itemid;
//$config = &JComponentHelper::getParams('com_awdwall');
$app = JFactory::getApplication('site');
$config =  & $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
?>
<style type="text/css">
	#awd-mainarea .wallheadingRight ul li a, #awd-mainarea .wallheadingRight ul li.separator{
		color:#<?php echo $this->color[1]; ?>;
	}
	#awd-mainarea .profileLink a, #awd-mainarea .profileMenu .ppm a, #awd-mainarea #msg_content .rbroundboxleft .mid_content a, #awd-mainarea .commentinfo a, #awd-mainarea .user_place ul.profileMenu li a{
		color:#<?php echo $this->color[2]; ?>;
	}
	#awd-mainarea #awd_message, #awd-mainarea .right_mid_content .walltowall li{
		color:#<?php echo $this->color[3]; ?>!important;
	}
	#awd-mainarea .wall_date{
		color:#<?php echo $this->color[4]; ?>;
	}
	#awd-mainarea .mid_content_top, #awd-mainarea .rbroundboxright, #awd-mainarea .fullboxtop, #awd-mainarea .rbroundboxleft_user, #awd-mainarea .lightblue_box, #awd-mainarea .rbroundboxrighttop{
		background-color:#<?php echo $this->color[5]; ?>;
	}
	#awd-mainarea .rbroundboxleft .mid_content a, #awd-mainarea .blog_groups, #awd-mainarea .blog_groups a, #awd-mainarea .blog_friend, #awd-mainarea .blog_friend a, #awd-mainarea a.authorlink{
		color:#<?php echo $this->color[6]; ?>;
	}
	#msg_content .rbroundboxleft, #msg_content .awdfullbox{
		background-color:#<?php echo $this->color[7]; ?>;
	}
	#awd-mainarea .walltowall li a, #msg_content .maincomment_noimg_right h3 a{
		color:#<?php echo $this->color[8]; ?>;
	}
	#awd-mainarea ul.tabProfile li a{
		background-color:#<?php echo $this->color[9]; ?>;
	}
	#awd-mainarea ul.tabProfile li a:hover, #awd-mainarea ul.tabProfile li.active a{
		background-color:#<?php echo $this->color[10]; ?>;
	}
	#awd-mainarea ul.tabProfile li a{
		color:#<?php echo $this->color[11]; ?>;
	}
	#awd-mainarea .whitebox, #awd-mainarea .blog_content, #awd-mainarea .about_me{
		background-color:#<?php echo $this->color[12]; ?>;
	}
	#awd-mainarea .wallheading, #awd-mainarea .wallheadingRight{
		background-color:#<?php echo $this->color[13]; ?>;
	}
	#awd-mainarea .round, #awd-mainarea .search_user{
		background-color:#<?php echo $this->color[14]; ?>;
	}
</style>
<div id="awd-mainarea">
<div class="wallheading">
    <div class="wallheadingRight">
		<?php $layout = $_REQUEST['layout']; ?>
      <ul>
		<li class="logo"><img src="components/com_awdwall/images/awdwall.png" alt="AWDwall" title="AWDwall"></li>
        <li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('News Feed');?>"><?php echo JText::_('News Feed');?></a> </li>
		<?php if((int)$user->id){?>
        <li class="separator">&nbsp;&nbsp;&nbsp;&nbsp;</li>
        <li><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('My Wall');?>">
		<?php if((int)$this->displayName == USERNAME) {?>
		<?php echo JText::sprintf('Users Wall', $user->username);?>
		<?php }else{?>
		<?php echo JText::sprintf('Users Wall', $user->name);?>
		<?php }?>
		</a> </li>
		<li class="separator"> </li>
        <li> <a href="<?php echo $friendJsUrl;?>" title="<?php echo JText::_('Friends');?>"><?php echo JText::_('Friends');?></a></li>
        <li class="separator"> </li>
		       <li> <a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=groups&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Groups');?>"><?php echo JText::_('Groups');?></a> </li>
        <li class="separator"> </li>
        <li> <a href="<?php echo JRoute::_('index.php?option=com_awdwall&task=uploadavatar&Itemid=' . $Itemid, false);?>" title="<?php echo JText::_('Account');?>"><?php echo JText::_('Account');?></a> </li>
        <li class="separator"> </li>
		<li class="no"> <a href="<?php echo JRoute::_('index.php?option=com_users&view=login&Itemid='.$Itemid, false);?>" title="<?php echo JText::_('Sign out');?>" ><img src="components/com_awdwall/images/logoutbutton.png" alt="<?php echo JText::_('Sign out');?>" title="<?php echo JText::_('Sign out');?>" class="imglogout" /></a> </li>
		<?php }?>
      </ul>
      <div class="searchWall">
         <form action="#" name="frm_auto_search" id="frm_auto_search" method="post">
          <input id="search_user" name="search_user" class="search_user ac_input" type="text" />
        </form>
      </div>
    </div>
  </div>
 <div class="awdfullbox fullboxtop  clearfix"> <span class="bl"></span>
<div style="width:100%;padding:20px;">
<form name="frmUploadAvatar" id="frmUploadAvatar" enctype="multipart/form-data" method="post" action="index.php?option=com_awdwall&view=awdwall">
<h3><?php echo JText::_('Upload Your Avatar');?></h3>
<p><label><?php echo JText::_('Current Avatar');?>:</label> <img src="<?php echo AwdwallHelperUser::getBigAvatar($this->user->user_id);?>" alt="Avatar" width="" height="" /></p>
<p><label><?php echo JText::_('New Avatar');?>:</label> <input type="file" name="avatar" id="avatar" /></p>
<p><strong><?php echo JText::_('Basic Information');?>:</strong></p>
<p><label><?php echo JText::_('Gender');?>:</label> 
<?php echo $this->listGender;?></p>
<p><label><?php echo JText::_('Birthday');?>:</label> <input type="text" name="birthday" id="birthday" maxlength="100" size="20" value="<?php echo $this->user->birthday;?>" />(<?php echo JText::_('DMY');?>)</p>
<p><label><?php echo JText::_('About Me');?>:</label> <textarea name="aboutme" id="aboutme" rows="5" cols="30"><?php echo $this->user->aboutme;?></textarea></p>
<br style="clear:both;"/>
<p class="submit"><input type="submit" name="submit" id="submit" value="Submit" /></p>
<input type="hidden" name="cbuser" id="cbuser" value="<?php echo $this->user->user_id;?>" />
<input type="hidden" name="task" id="task" value="saveAvatar" />
<input type="hidden" name="itemid" id="itemid" value="<?php echo $Itemid;?>" />
</form>
</div>
</div>
</div>

