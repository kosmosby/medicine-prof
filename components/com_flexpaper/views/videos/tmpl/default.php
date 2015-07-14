<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

echo $this->loadTemplate('topmenu');
?>

<table border="0" width="100%">
    <tr>
        <th align="left" width="50%" height="50"><?php echo JText::_('COM_FLEXPAPER_COURSE_NAME');?></th>
    </tr>

    <?php for($i=0;$i<count($this->items);$i++) {?>
        <tr>
            <td>
                <a href="index.php?option=com_flexpaper&Itemid=<?php echo $this->itemid;?>&task=video&view=video&course_id=<?php echo $this->items[$i]->course_id;?>"><?php echo $this->items[$i]->title;?></a>
            </td>
        </tr>
    <?php }?>
</table>