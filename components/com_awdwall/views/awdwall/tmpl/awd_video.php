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
?>
<div style="width:100%;">
<h3><?php echo $this->video->title;?></h3>
<div style="float:left;width:440px;">
        <?php echo $this->video->player; ?>
	<br />
	<br />
<a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall', false);?>"><?php echo JText::_('Back To My wall');?></a>
</div>

<div style="float:left;width:35%;padding-left:20px;"><?php echo $this->video->description; ?></div>
</div>
