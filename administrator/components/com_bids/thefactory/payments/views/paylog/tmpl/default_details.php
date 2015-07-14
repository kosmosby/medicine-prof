<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="get" name="adminForm">
    <div class='width-100 fltlft'>
        <fieldset class="adminform">
        <legend><?php echo JText::_( 'FACTORY_PAYMENT_NUMBER' ),": #",$this->paylog->id; ?></legend>
            <table class="paramlist admintable" border="0" width="880">
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_PAYMENT_DATE"),":"; ?>
                    </td>
                    <td class="paramlist_value">
                        <?php echo $this->paylog->date;?>
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
                        <?php echo $this->paylog->amount," ",$this->paylog->currency; ?>
                    </td>
                </tr>
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_STATUS"),":"; ?>
                    </td>
                    <td class="paramlist_value"><strong>
                        <?php echo $this->paylog->status; ?></strong>
                    </td>
                </tr>
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_ORDER_ID"),":"; ?>
                    </td>
                    <td class="paramlist_value">
                        <a href="index.php?option=<?php echo APP_EXTENSION;?>&task=orders.viewdetails&id=<?php echo $this->paylog->orderid;?>">
                            <?php echo $this->paylog->orderid;?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_REF_NR"),":"; ?>
                    </td>
                    <td class="paramlist_value">
                            <?php echo $this->paylog->refnumber;?>
                    </td>
                </tr>
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_IPN_IP"),":"; ?>
                    </td>
                    <td class="paramlist_value">
                            <?php echo $this->paylog->ipn_ip;?>
                    </td>
                </tr>
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_PAYMENT_METHOD"),":"; ?>
                    </td>
                    <td class="paramlist_value">
                            <?php echo $this->paylog->payment_method;?>
                    </td>
                </tr>
    
            </table>
        </fieldset>
    </div>
    <div class='width-100 fltlft'>   
        <fieldset class="adminform">
        <legend><?php echo JText::_( 'FACTORY_RAW_IPN_CONTENTS' )?></legend>
            <pre style="padding-left: 20px;"><?php echo $this->paylog->ipn_response;?></pre>
        </fieldset>
    </div>    
    <input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
    <input type="hidden" name="id" value="<?php echo $this->paylog->id;?>" />
    <input type="hidden" name="task" value="payments.listing" />
    <input type="hidden" name="boxchecked" value="0" />    
</form>
