<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="get" name="adminForm">

    <div id="cpanel">
        <?php
        $link = 'index.php?option=' . APP_EXTENSION . '&amp;task=orders.listing';
        echo JTheFactoryPaymentsHtmlHelper::quickIconButton($link, 'paymentitems.png', JText::_("FACTORY_ORDERS_BUTTON"));

        $link = 'index.php?option=' . APP_EXTENSION . '&amp;task=payments.listing';
        echo JTheFactoryPaymentsHtmlHelper::quickIconButton($link, 'payments.png', JText::_("FACTORY_PAYMENTS_BUTTON"));

        $link = 'index.php?option=' . APP_EXTENSION . '&amp;task=balances.listing';
        echo JTheFactoryPaymentsHtmlHelper::quickIconButton($link, 'payments.png', JText::_("FACTORY_BALANCES_BUTTON"));
        ?>
    </div>
    <div>
        <?php echo JText::_('FACTORY_USERNAME_FILTER'); ?>
        <input type="text" name="filter_username" id="filter_username" size="30"
               value="<?php echo $this->filter_username;?>" class="text_area"/>

        <button onclick="this.form.submit();"><?php echo JText::_('FACTORY_FILTER'); ?></button>
        <button onclick="this.form.filter_username.value='';this.form.submit();"><?php echo JText::_('FACTORY_RESET'); ?></button>

    </div>
    <div style="clear:both;"></div>

    <table class="adminlist" cellspacing="1">
    <thead>
        <tr>
            <th width="60" align="center"><?php echo JText::_("FACTORY_ORDER_NR"); ?></th>
            <th width="200" align="center"><?php echo JText::_("FACTORY_ORDER_DATE"); ?></th>
            <th class="title" width="250"><?php echo JText::_("FACTORY_USERNAME");?></th>
            <th class="title" width="150" nowrap="nowrap"><?php echo JText::_("FACTORY_ORDER_TOTAL");?></th>
            <th class="title" width="10%"><?php echo JText::_("FACTORY_PAYMENT_LOG");?></th>
            <th class="title" width="10%"><?php echo JText::_("FACTORY_ORDER_STATUS"); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $k = 0;
    for ($i=0, $n=count( $this->orders ); $i < $n; $i++) {
        $row = $this->orders[$i];

        $link_orderitems 	= 'index.php?option='.APP_EXTENSION.'&task=orders.viewdetails&id='. $row->id;

        $link_paylog 	= 'index.php?option='.APP_EXTENSION.'&task=payments.viewdetails&id='. $row->paylogid;
        $img_paylog=$row->paylogid?"icon-16-allow.png":"publish_r.png";
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td align="left"><?php echo JHtml::_('grid.id', $i, $row->id,false,'id')," ",$row->id;?></td>
            <td align="left"><?php echo $row->orderdate;?></td>
            <td align="left"><?php echo $row->username;?></td>
            <td align="center">
                <a href="<?php echo $link_orderitems;?>" title="<?php echo JText::_("FACTORY_VIEW_ORDER_ITEMS");?>">
                    <?php echo $row->order_total," ",$row->order_currency; ?>
                </a>
            </td>
            <td align="center">
                <?php if ($row->paylogid) :?>
                <a href="<?php echo $link_paylog;?>" title="<?php echo JText::_("FACTORY_VIEW_PAYMENT_LOG_DETAILS"); ?>">
                    <?php echo JHtml::_('image.administrator','admin/'.$img_paylog);?>
                </a>
                <?php else: ?>
                    <?php echo JHtml::_('image.administrator','admin/'.$img_paylog);?>
                <?php endif;?>
            </td>
            <td align="center"><strong style="font-size: 110%"><?php echo $row->status;?></strong></td>

        </tr>
        <?php
        $k = 1 - $k;
    }
    ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="15">
                <?php echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
    </tfoot>
    </table>
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="orders.listing" />
<input type="hidden" name="boxchecked" value="0" />

</form>
