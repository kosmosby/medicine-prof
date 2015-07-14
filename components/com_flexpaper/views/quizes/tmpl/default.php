<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

echo $this->loadTemplate('topmenu');
?>

<table border="0" width="100%">
    <tr>
        <th align="left" width="50%" height="50"><?php echo JText::_('COM_FLEXPAPER_QUIZ_NAME');?></th>
        <th align="left" ><?php echo JText::_('COM_FLEXPAPER_QUIZ_PASSED');?></th>
    </tr>

    <?php for($i=0;$i<count($this->items);$i++) {?>
        <tr>
            <td>
                <a href="index.php?option=com_flexpaper&Itemid=<?php echo $this->itemid;?>&task=runtest&testid=<?php echo $this->items[$i]->id;?>&course_id=<?php echo $this->items[$i]->course_id;?>"><?php echo $this->items[$i]->title;?></a>
            </td>
            <td><?php echo $this->items[$i]->passed?JText::_('COM_FLEXPAPER_YES'):JText::_('COM_FLEXPAPER_NO');?></td>
        </tr>
    <?php }?>
</table>