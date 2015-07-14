<?php defined('_JEXEC') or die('Restricted access'); ?>
<h1><?php echo JText::_('FACTORY_CHECKOUT_YOUR_ORDER'),$this->order->id;?> </h1>
<h3><?php echo JText::_('FACTORY_ORDER_DETAILS');?></h3>

<table width="100%" class="order_details">
    <thead>
        <th>#</th>
        <th><?php echo JText::_('FACTORY_ORDER_ITEM');?></th>
        <th><?php echo JText::_('FACTORY_QUANTITY');?></th>
        <th><?php echo JText::_('FACTORY_UNIT_PRICE');?></th>
        <th><?php echo JText::_('FACTORY_TOTAL_PRICE');?></th>
    </thead>
    <?php for($i=0;$i<count($this->order_items);$i++):?>
    <tr>
        <td><?php echo $i+1;?>.</td>
        <td><?php echo $this->order_items[$i]->itemdetails;?></td>
        <td><?php echo $this->order_items[$i]->quantity;?></td>
        <td><?php echo number_format($this->order_items[$i]->price/$this->order_items[$i]->quantity,2)," ",$this->order_items[$i]->currency;?></td>
        <td><?php echo number_format($this->order_items[$i]->price,2)," ",$this->order_items[$i]->currency;?></td>

    </tr>
    <?php endfor;?>
    <td colspan="5">&nbsp;</td>
    <tfoot>
    <td colspan="4" align="right"></td><td><strong><?php echo $this->order->order_total," ",$this->order->order_currency;?></strong></td>
    </tfoot>
</table>
<br/>
<br/>
<h3><?php echo JText::_('FACTORY_CHOOSE_A_PAYMENT_GATEWAY'),':';?></h3>
<br/>
<br/>
<table width="100%" class="payment_gateways" cellpadding="0" cellspacing="0" border="0">
<?php for($i=0;$i<count($this->payment_gateways);$i++):?>
    <tr>
        <td><strong><?php echo $this->payment_gateways[$i]->fullname?></strong></td>
        <td><?php echo $this->payment_forms[$i]?></td>
    </tr>
<?php endfor;?>
</table>
