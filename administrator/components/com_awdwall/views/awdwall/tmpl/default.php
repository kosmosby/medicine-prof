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
defined('_JEXEC') or die('Restricted access'); 
//jimport('joomla.html.pane');
jimport('joomla.html.html.bootstrap');

//$pane	=& JPane::getInstance('sliders');
$url = JURI::root();
//global $mainframe;
$app = JFactory::getApplication();
Awdwalladminhelper::awdadmintoolbar('awdwall');
?>




<div class="awdm">
<table width="100%" border="0">
	<tr>
		<td width="55%" valign="top">
			<div id="cpanel">
            	<?php echo $this->addIcon('configuration.jpg','index.php?option=com_config&amp;view=component&amp;component=com_awdwall', JText::_('Configuration'));?>
				<?php echo $this->addIcon('allmsg.jpg','index.php?option=com_awdwall&amp;controller=wall', JText::_('All Messages'));?>
				<?php echo $this->addIcon('Colors.jpg','index.php?option=com_awdwall&amp;controller=colors', JText::_('Colors'));?>
				<?php echo $this->addIcon('groups.jpg','index.php?option=com_awdwall&amp;controller=groups', JText::_('Groups'));?>
				<?php echo $this->addIcon('updates.jpg','index.php?option=com_awdwall&amp;controller=update', JText::_('Updates'));?>
				<?php echo $this->addIcon('gallery.jpg','index.php?option=com_awdjomalbum', JText::_('Gallery'));?>
				<?php echo $this->addIcon('aboutus.jpg','index.php?option=com_awdwall&amp;controller=about', JText::_('About'));?>
			</div>
		</td>
		<td width="45%" valign="top">
			<?php
			
echo JHtml::_('bootstrap.startAccordion', 'slide-example', array('active' => 'slide1'));
echo JHtml::_('bootstrap.addSlide', 'slide-example', JText::_('Site Statistics'), 'slide1');
?>
<table class="adminlist"  style="border-spacing:0;">
	<tr>
		<td>						
<table class="adminlist"  >
  <tbody>
    <tr>
      <td width="250" ><?php echo JText::_('JomWALL'); ?> </span> </td>
      <td valign="top" align="left">JomWALL CB </td>
    </tr>
    <tr>
      <td width="250" ><?php echo JText::_('Site Info'); ?> </span> </td>
      <td valign="top" align="left"><?php echo $app->getCfg('sitename'); ?></td>
    </tr>
    <tr>
      <td  ><?php echo JText::_('Site URL'); ?> </span> </td>
      <td valign="top" ><a href="<?php echo JURI::root();?>"><?php echo JURI::root();?></a></td>
    </tr>
    <tr>
      <td  ><?php echo JText::_('Site Status'); ?> </span> </td>
      <td valign="top" style="color:#00CC33;"><?php echo JText::_('JomWALL CB has been successfully installed!'); ?> </td>
    </tr>
  </tbody>
</table>
		</td>
	</tr>
</table>
<?php
echo JHtml::_('bootstrap.endSlide');
echo JHtml::_('bootstrap.addSlide', 'slide-example', JText::_('JomWALL Support'), 'slide2');
?>
<table class="adminlist"  style="border-spacing:0;">
  <tbody>
    <tr>
      <td >  <a href="http://jomwall.com/support/faq" target="_blank"><?php echo JText::_('FAQ');?></a> <hr class="awdhr" />  <a href="http://jomwall.com/support/ticket-system/department/support-services"  target="_blank"><?php echo JText::_('Ticket System');?></a><hr class="awdhr" /><a href="http://jomwall.com/my-account/login"  target="_blank" ><?php echo JText::_('Login to your account');?></a><hr class="awdhr" /><a href="http://jomwall.com/blog"  target="_blank"><?php echo JText::_('Latest news');?></a></td>
      
    </tr>
  </tbody>
</table>
<?php
echo JHtml::_('bootstrap.endSlide');
echo JHtml::_('bootstrap.endAccordion');
?>
		</td>
	</tr>
</table>

<?php 
Awdwalladminhelper::awdfooter();
?>
</div>


