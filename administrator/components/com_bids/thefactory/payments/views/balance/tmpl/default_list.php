<?php defined('_JEXEC') or die('Restricted access'); ?>
<form name="adminForm" action="index.php" method="get">
    <input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>"/>
    <input type="hidden" name="task" value="balances.listing"/>
    <div id="cpanel">
        <?php
            $link = 'index.php?option=' . APP_EXTENSION . '&amp;task=orders.listing';
            echo JTheFactoryPaymentsHtmlHelper::quickIconButton($link, 'paymentitems.png', JText::_("FACTORY_ORDERS_BUTTON") );

            $link = 'index.php?option=' . APP_EXTENSION . '&amp;task=payments.listing';
            echo JTheFactoryPaymentsHtmlHelper::quickIconButton($link, 'payments.png', JText::_("FACTORY_PAYMENTS_BUTTON") );

            $link = 'index.php?option=' . APP_EXTENSION . '&amp;task=balances.listing';
            echo JTheFactoryPaymentsHtmlHelper::quickIconButton($link, 'payments.png', JText::_("FACTORY_BALANCES_BUTTON") );
        ?>
    </div>
    <div>
        <?php echo JText::_( 'FACTORY_USERNAME_FILTER' ); ?>
        <input type="text" name="filter_userid" id="filter_userid" size="30" value="<?php echo $this->filter_userid;?>" class="inputbox" title="<?php echo JText::_( 'FACTORY_PART_OF_USERNAME' );?>" />
        &nbsp;&nbsp;&nbsp;
        <?php echo $this->filter_balances ;?>
        <input type="submit" name="filterbutton" value="<?php echo JText::_("FACTORY_FILTER");?>"/>
    </div>
    <div style="clear:both;"></div>
    <table class="adminlist" >
        <thead>
            <tr>
                <th width="30"><?php echo JText::_("FACTORY_USERID");?></th>
                <th width="*%"><?php echo JText::_("FACTORY_USER_NAME");?></th>
                <th width="100"><?php echo JText::_("FACTORY_AMOUNT");?></th>
                <th width="80"><?php echo JText::_("FACTORY_CURRENCY");?></th>
            </tr>
        </thead>
    <tbody>
    <?php
    $odd=0;
    foreach ($this->userbalances as $userbalance) {?>
    <tr class="row<?php echo ($odd=1-$odd);?>">
        <td align="center"><?php echo $userbalance->userid;?></td>
        <td><?php echo $userbalance->username;?></td>
        <td><?php echo $userbalance->balance;?></td>
        <td><?php echo $userbalance->currency;?></td>
    </tr>
    <?php } ?>
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
