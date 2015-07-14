 <?php
/**
 * @version 2.5
 * @package JomWALL CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
defined('_JEXEC') or die();
$user = &JFactory::getUser();
?>
<div style="width:100%;">
<h3><?php echo $this->image->name;?></h3>
<div style="float:left;width:440px;">
   <img width="440" src="<?php echo JURI::base();?>images/<?php echo $this->userId;?>/original/<?php echo $this->image->path; ?>" title="<?php echo $this->image->name; ?>" alt="<?php echo $this->image->name; ?>"/> 
	<br />
	<br />
<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall', false);?>"><?php echo JText::_('Back To My wall');?></a>
</div>

<div style="float:left;width:30%;padding-left:20px;"><?php echo $this->image->description; ?></div>
</div>
