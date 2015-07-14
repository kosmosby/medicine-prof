<?php defined('_JEXEC') or die('Restricted access'); ?>
<table cellspacing="8">
<?php foreach ($this->shortcuts as $shortcut=>$description): ?>
<tr>
    <td><a href="#" onclick="return ClickShortcut('<?php echo $shortcut;?>')"><span class="mailman_shortcut"><?php echo $shortcut;?></span></a></td>
    <td>&nbsp; - &nbsp;</td>
    <td><b><?php echo JText::_($description);?></b></td>
</tr>
<?php endforeach;?> 
</table>
