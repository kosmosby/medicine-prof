<?php
/**
 * @version 3.0
 * @package JomWALL -CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
class Awdwalladminhelper 
{

	function awdadmintoolbar($cntr)
	{
	?>

<div style="color:white;font-weight:bold; margin-bottom:20px;">
  <div style="background:url(components/com_awdwall/images/box_bg.png) no-repeat top left;height:8px;">
    <div style="background: url(components/com_awdwall/images/box_bg.png) no-repeat top right;height:8px;"></div>
    <div style="background-color:#2c2c2c;border-top:2px solid #1f1f1f;height:8px;margin:-8px 8px;"></div>
  </div>
  <div style="background-color:#2c2c2c;padding:0 20px; border-left:2px solid #1f1f1f;border-right:2px solid #1f1f1f; ">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td align="left" width="180"><img src="components/com_awdwall/images/jomwallblacktoolbar.png" /></td>
        <td align="left" valign="middle"><ul id="awdsubmenu">
            <li> <a href="index.php?option=com_awdwall&amp;controller=awdwall" class="<?php if($cntr=='awdwall') echo 'active'; ?>" >Control Panel</a> </li>
            <li> <a href="index.php?option=com_config&amp;view=component&amp;component=com_awdwall" class="<?php if($cntr=='config') echo 'active'; ?>">Configuration</a> </li>
            <li> <a href="index.php?option=com_awdwall&controller=config" class="<?php if($cntr=='config') echo 'active'; ?>">Upload Templates</a> </li>
            <li> <a href="index.php?option=com_awdwall&amp;controller=wall" class="<?php if($cntr=='wall') echo 'active'; ?>">All Messages</a> </li>
            <li> <a href="index.php?option=com_awdwall&amp;controller=groups" class="<?php if($cntr=='groups') echo 'active'; ?>">Groups</a> </li>
            <li> <a href="index.php?option=com_awdwall&amp;controller=colors" class="<?php if($cntr=='colors') echo 'active'; ?>">Colors</a> </li>
            <li> <a href="index.php?option=com_awdwall&amp;controller=about" class="<?php if($cntr=='about') echo 'active'; ?>">About us</a> </li>
            <li> <a href="index.php?option=com_awdwall&amp;controller=update" class="<?php if($cntr=='update') echo 'active'; ?>">Updates</a> </li>
          </ul></td>
        <td align="right" class="tdright">JomWALL version 3.0<br />
          Copyright &copy; <?php echo date("Y");?> JomWALL.com</td>
      </tr>
    </table>
  </div>
  <div style="background:url(components/com_awdwall/images/box_bg.png) no-repeat bottom left;height:8px;">
    <div style="background:url(components/com_awdwall/images/box_bg.png) no-repeat bottom right;height:8px;"></div>
    <div style="background-color:#2c2c2c;border-bottom:2px solid #1f1f1f;height:8px;margin:-10px 8px;"></div>
  </div>
</div>
<?php 
}

function awdfooter()
{
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="left" valign="bottom" width="33%">JomWALL - Real time Content Sharing &amp; Collaboration System<br />
      Copyright &copy; 2009 - <?php echo date("Y");?> <a href="http://jomwall.com/" target="_blank">JomWALL</a>.com All Rights Reserved.</td>
    <td align="center" valign="middle"width="34%">
   </td>
    <td align="right" valign="middle" width="33%"><img src="components/com_awdwall/images/awdboomlogo.png" /><br />Jomwall is a product of <a href="http://awdsolution.com/" target="_blank">Awdsolutions</a></td>
  </tr>
</table>
<?php
}

}
?>