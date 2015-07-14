<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');


?>
<br/>



<table border="0" width="100%" >
    <tr>
        <td valign="top" align="center" style="font-size: 11px;">
            Наименование группы АТХ
        </td>
        <td valign="top" align="center" style="font-size: 11px;">
            Международное непатентованное название
        </td>
        <td valign="top" align="center" style="font-size: 11px;">
            Торговое наименование ЛС отечественного производства
        </td>
        <td valign="top" align="center" style="font-size: 11px;">
            Производитель
        </td>
        <td valign="top" align="center" style="font-size: 11px;">
            Торговые наименования аналогов зарубежного производства
        </td>

    </tr>

    <tr>
        <td height="20">
         <td>
        </td>
        <td>
         </td>
        <td>
         </td>
        <td>
         </td>

    </tr>

    <tr>
        <td valign="top" align="center">
           <a href="index.php?option=com_flexpaper&task=catalogsalenames&view=catalogsalenames&atx_id=<?php echo $this->item->atx_id;?>">
                <?php echo $this->atx;?>
            </a>
        </td>
        <td valign="top" align="center">
            <?php echo $this->international_name;?>
        </td>
        <td valign="top" align="center">
            <?php echo $this->item->name;?>
        </td>
        <td valign="top" align="center">
            <?php echo $this->item->man_name;?>
        </td>
        <td valign="top" align="center">
            <?php for($i=0;$i<count($this->analogs);$i++) {?>

                <div>
                    <?php echo $this->analogs[$i]->name;?>
                </div>

            <?php }?>
        </td>

    </tr>

    <tr>
        <td colspan="5" style="padding-top: 20px;">
            <img width="200" src="<?php echo $this->item->image;?>">
        </td>
    </tr>
</table>

<br />

<a href="javascript:window.history.back();">Назад</a>


<br />
<br />