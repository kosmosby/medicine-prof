<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="get" name="adminForm">

    <div class='width-100 fltlft'>
        <fieldset class="adminform">
        <legend><?php echo JText::_( 'FACTORY_ORDER_NUMBER' ),": #",$this->order->id; ?></legend>
            <table class="paramlist admintable" border="0" width="880">
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_ORDER_DATE"),":"; ?>
                    </td>
                    <td class="paramlist_value">
                        <?php echo $this->order->orderdate;?>
                    </td>
                </tr>
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_USERNAME"),":"; ?>
                    </td>
                    <td class="paramlist_value">
                        <?php echo $this->user->username;?>
                    </td>
                </tr>
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_ORDER_TOTAL"),":"; ?>
                    </td>
                    <td class="paramlist_value">
                        <?php echo $this->order->order_total," ",$this->order->order_currency; ?>
                    </td>
                </tr>
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_STATUS"),":"; ?>
                    </td>
                    <td class="paramlist_value"><strong>
                        <?php
                            switch($this->order->status){
                                case "P":
                                    echo JText::_("FACTORY_PENDING");
                                break;
                                case "C":
                                    echo JText::_("FACTORY_CONFIRMED");
                                break;
                                case "X":
                                    echo JText::_("FACTORY_CANCELLED");
                                break;
    
                            }
                        ?></strong>
                    </td>
                </tr>
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_PAYMENT_LOG_ID"),":"; ?>
                    </td>
                    <td class="paramlist_value">
                        <?php if($this->order->paylogid): ?>
                            <a href="index.php?option=<?php echo APP_EXTENSION;?>&task=payments.viewdetails&id=<?php echo $this->order->paylogid;?>">
                                <?php echo $this->order->paylogid;?>
                            </a>
                        <?php else: ?>
                            -
                        <?php endif;?>
                    </td>
                </tr>
    
            </table>
        </fieldset>
        <fieldset class="adminform">
            <legend><?php echo JText::_( 'FACTORY_ORDER_NUMBER' ),": #",$this->order->id; ?></legend>
            <table class="paramlist admintable" border="0" width="100%">
                <thead>
                    <th>#</th>
                    <th><?php echo JText::_("FACTORY_ITEM_NAME"); ?></th>
                    <th>&nbsp;</th>
                    <th><?php echo JText::_("FACTORY_QUANTITY"); ?></th>
                    <th><?php echo JText::_("FACTORY_ITEM_PRICE"); ?></th>
                </thead>
                <tbody>
                    <?php
                    $i=1;
                    foreach($this->orderitems as $row): ?>
                    <tr>
                        <td><?php echo $i++;?></td>
                        <td><?php echo $row->itemname;?></td>
                        <td><?php echo $row->itemdetails;?></td>
                        <td><?php echo $row->quantity;?></td>
                        <td><?php echo $row->price," ",$row->currency;?></td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </fieldset>
    </div>
    <input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
    <input type="hidden" name="id" value="<?php echo $this->order->id;?>" />
    <input type="hidden" name="task" value="orders.listing" />
    <input type="hidden" name="boxchecked" value="0" />
</form>
