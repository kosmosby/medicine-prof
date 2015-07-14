<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

?>
<br />
<table border="0" width="100%">

    <?php for($i=0;$i<count($this->items);$i++) {?>
        <tr>
            <td>
                <a href="index.php?option=com_flexpaper&Itemid=<?php echo $this->itemid;?>&task=catalogatx&view=catalogatx&cat_id=<?php echo $this->items[$i]->id;?>"><?php echo $this->items[$i]->name;?></a>
            </td>
        </tr>
    <?php }?>
</table>
<br />