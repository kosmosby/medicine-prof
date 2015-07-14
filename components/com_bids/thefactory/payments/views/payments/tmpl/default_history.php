<?php defined('_JEXEC') or die('Restricted access'); ?>
<h1><?php echo JText::_("FACTORY_YOUR_PAYMENT_HISTORY");?></h1>
<div>
    <form action="index.php" method="post" name="paymentform">
	<fieldset class="filters">
		<div class="display-limit">
			<?php echo JText::_('COM_BIDS_JGLOBAL_DISPLAY_NUM'); ?>&#160;
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	</fieldset>
     
    <table width="100%">
    <thead>
        <th><?php echo JText::_("FACTORY_ORDER");?></th>
        <th><?php echo JText::_("FACTORY_DATETIME");?></th>
        <th><?php echo JText::_("FACTORY_STATUS");?></th>
        <th><?php echo JText::_("FACTORY_ORDER_TOTAL");?></th>
    </thead>
    <?php foreach($this->orders as $order):?>
    <tr>
        <td><?php echo str_pad($order->id,5,'0',STR_PAD_LEFT);?></td>
        <td><?php echo $order->orderdate;?></td>
        <td><a href="index.php?option=<?php echo APP_EXTENSION;?>&task=orderprocessor.details&orderid=<?php echo $order->id;?>&Itemid=<?php echo $this->Itemid;?>">
            <?php if ($order->status=='P') echo JText::_("FACTORY_PENDING");?>
            <?php if ($order->status=='C') echo JText::_("FACTORY_COMPLETED");?>
            <?php if ($order->status=='X') echo JText::_("FACTORY_CANCEL");?>
        </a>
        </td>
        <td><?php echo number_format($order->order_total,2)," ",$order->order_currency;?></td>
    </tr>
    <?php endforeach;?>
    </table>
    <input name="option" type="hidden" value="<?php echo APP_EXTENSION;?>">
    <input name="task" type="hidden" value="payments.history">
    <input name="Itemid" type="hidden" value="<?php echo $this->Itemid; ?>">
	<div class="pagination">
		<p class="counter">
			<?php echo $this->pagination->getPagesCounter(); ?>
		</p>
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div> 
    </form>
</div>
