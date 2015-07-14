<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="get" name="adminForm">

    <table class="adminlist" cellspacing="1">
        <thead>
        <tr>

            <th width="80" align="center">
                <a href="javascript:submitbutton('gateways.reorder')" class="saveorder"
                   title="<?php echo JText::_('FACTORY_SAVE_ORDERING');?>"></a>
            </th>
            <th class="title"><?php echo JText::_("FACTORY_PAYMENT_SYSTEM");?></th>
            <th class="title" width="30%" nowrap="nowrap"><?php echo JText::_("FACTORY_CLASS_NAME");?></th>
            <th class="title" width="10%"><?php echo JText::_("FACTORY_IS_DEFAULT_GATEWAY"); ?></th>
            <th class="title" width="10%"><?php echo JText::_("FACTORY_ENABLED");?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $k = 0;
        for ($i = 0, $n = count($this->gateways); $i < $n; $i++) {
            $row = $this->gateways[$i];
            $link = 'index.php?option=' . APP_EXTENSION . '&task=gateways.edit&id=' . $row->id;
            $link_enable = 'index.php?option=' . APP_EXTENSION . '&task=gateways.toggle&id=' . $row->id;
            $link_default = 'index.php?option=' . APP_EXTENSION . '&task=gateways.setdefault&id=' . $row->id;
            $img_enabled = $row->enabled ? "tick.png" : "publish_r.png";
            $img_default = $row->isdefault ? "featured.png" : "disabled.png";
            ?>
        <tr class="<?php echo "row$k"; ?>">

            <td align="center">
                <input type="text" size="5" name="order_<?php echo $row->id;?>" value="<?php echo $row->ordering;?>"
                       class="text_area" style="text-align: center"/>
            </td>
            <td align="left"><a href="<?php echo $link;?>"><?php echo $row->paysystem; ?></a></td>
            <td align="center"><?php echo $row->classname; ?></td>
            <td align="center">
                <a href="<?php echo $link_default;?>">
                    <?php echo JHtml::_('image.administrator', 'admin/' . $img_default);?>
                </a>
            </td>
            <td align="center">
                <a href="<?php echo $link_enable;?>">
                    <?php echo JHtml::_('image.administrator', 'admin/' . $img_enabled);?>
                </a>
            </td>

        </tr>
            <?php
            $k = 1 - $k;
        }
        ?>
        </tbody>
    </table>
    <input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>"/>
    <input type="hidden" name="task" value="gateways.listing"/>
    <input type="hidden" name="boxchecked" value="0"/>

</form>
