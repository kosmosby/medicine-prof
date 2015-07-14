<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="get" name="adminForm">

    <input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>"/>
    <input type="hidden" name="task" value="payments.listing"/>
    <input type="hidden" name="boxchecked" value="0"/>

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
        <?php echo JText::_('FACTORY_USERNAME_FILTER'); ?>:
        <input type="text" name="filter_username" id="filter_username" size="30"
               value="<?php echo $this->filter_username;?>" class="text_area"/>

        <button onclick="this.form.submit();"><?php echo JText::_('FACTORY_FILTER'); ?></button>
        <button onclick="this.form.filter_username.value='';this.form.submit();"><?php echo JText::_('FACTORY_RESET'); ?></button>
    </div>
    <div style="clear:both;"></div>

    <table class="adminlist" cellspacing="1">
    <thead>
        <tr>
            <th width="60" align="center"><?php echo JText::_("FACTORY_PAYMENTID"); ?></th>
            <th width="200" align="center"><?php echo JText::_("FACTORY_PAYMENT_DATE"); ?></th>
            <th width="200" align="center"><?php echo JText::_("FACTORY_PAYMENT_STATUS"); ?></th>
            <th class="title" width="250"><?php echo JText::_("FACTORY_USERNAME");?></th>
            <th class="title" width="150" nowrap="nowrap"><?php echo JText::_("FACTORY_PAYMENT_AMOUNT");?></th>
            <th class="title" width="10%"><?php echo JText::_("FACTORY_ORDER_NUMBER");?></th>
            <th class="title" width="10%"><?php echo JText::_("FACTORY_REF_NUMBER"); ?></th>
            <th class="title" width="10%"><?php echo JText::_("FACTORY_PAYMENT_METHOD"); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $k = 0;
    for ($i=0, $n=count( $this->payments ); $i < $n; $i++) {
        $row = $this->payments[$i];

        $link_orderdetails 	= 'index.php?option='.APP_EXTENSION.'&task=orders.viewdetails&id='. $row->orderid;

        $link_paymentdetails 	= 'index.php?option='.APP_EXTENSION.'&task=payments.viewdetails&id='. $row->id;
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td align="left"><?php echo JHtml::_('grid.id', $i, $row->id,false,'id')," ",$row->id;?></td>
            <td align="left"><?php echo $row->date;?></td>
            <td align="center"><strong style="font-size: 110%"><?php echo $row->status;?></strong></td>
            <td align="left"><?php echo $row->username;?></td>
            <td align="center">
                <a href="<?php echo $link_paymentdetails;?>" title="<?php echo JText::_("FACTORY_VIEW_PAYMENT_DETAILS");?>">
                    <?php echo $row->amount," ",$row->currency; ?>
                </a>
            </td>
            <td align="center">
                <a href="<?php echo $link_orderdetails;?>" title="<?php echo JText::_("FACTORY_VIEW_ORDER_DETAILS"); ?>">
                    <?php echo $row->orderid;?>
                </a>
            </td>
            <td align="center"><?php echo $row->refnumber;?></td>
            <td align="center"><?php echo $row->payment_method;?></td>

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
</form>
