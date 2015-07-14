<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

?>
<br/>
<table border="0" width="100%">

    <th>
        <?php //echo $this->category;?> <?php //echo $this->atx;?>
    </th>


    <?php for($i=0;$i<count($this->items);$i++) {?>
        <tr>
            <td>
                <a href="index.php?option=com_flexpaper&Itemid=<?php echo $this->itemid;?>&task=catalogsalename&view=catalogsalename&id=<?php echo $this->items[$i]->id;?>"><?php echo $this->items[$i]->name;?></a>
            </td>
        </tr>
    <?php }?>
</table>

<br />

<a href="javascript:window.history.back();">Назад</a>


<br />
<br />