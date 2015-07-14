<?php defined('_JEXEC') or die('Restricted access'); ?>
<div style="float: left;">
    <form action="index.php" method="get" name="adminForm">
        <table class="adminlist" cellspacing="1">
        <thead>
            <tr>
                <th width="60" align="center"><?php echo JText::_("COM_BIDS_ORDER_NR"),'#'; ?></th>
                <th width="200" align="center"><?php echo JText::_("COM_BIDS_ORDER_DATE"); ?></th>
                <th class="title" width="250"><?php echo JText::_("COM_BIDS_USERNAME");?></th>
                <th class="title" width="150" nowrap="nowrap"><?php echo JText::_("COM_BIDS_AMOUNT");?></th>
            </tr>
        </thead>
        <tbody>
        <?php
        $odd=0;
        foreach($this->payments as $payment): ?>
        <tr>
            <td><?php echo $payment->orderid;?></td>
            <td><?php echo $payment->orderdate;?></td>
            <td><?php echo $payment->username;?></td>
            <td style="text-align: right;"><?php echo number_format($payment->price,2)," ",$payment->currency;?></td>
        </tr>
        <?php endforeach; ?>
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
        <input type="hidden" name="task" value="pricing.payments" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="item" value="comission" />
        <input type="hidden" name="commissionType" value="<?php echo JRequest::getVar('commissionType'); ?>" />
    </form>
</div>
