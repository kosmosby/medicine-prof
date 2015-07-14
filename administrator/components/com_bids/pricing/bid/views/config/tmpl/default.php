<?php defined('_JEXEC') or die('Restricted access'); ?>
<form name="adminForm" action="index.php" method="post">
<fieldset>
    <legend><?php echo JText::_("COM_BIDS_GENERAL_SETTINGS"); ?></legend>
    <table width="100%" class="paramlist admintable">
        <tbody>
            <tr>
                <td width="220" class="paramlist key"><?php echo JText::_("COM_BIDS_CURRENCY_USED"); ?>: </td>
                <td><?php echo JHtml::_('currency.selectlist','currency','',$this->currency);?></td>
            </tr>
            <tr>
                <td width="220" class="paramlist key"><?php echo JText::_("COM_BIDS_DEFAULT_LISTING_PRICE"); ?>: </td>
                <td><input value="<?php echo $this->default_price;?>" class="inputbox" name="default_price" size="5"></td>
            </tr>
            <tr>
                <td width="220" class="paramlist key">
                    <?php echo JText::_("COM_BIDS_PAYPERBID_ALLOW_INSUFFICIENT_FUNDS"); ?>: <?php echo JHTML::tooltip(JText::_('COM_BIDS_PAYPERBID_ALLOW_INSUFFICIENT_FUNDS_HELP')); ?>
                </td>
                <td>
                    <input type="checkbox" value="1" class="inputbox" name="allow_no_funds" size="5" <?php echo $this->allow_no_funds ? 'checked=""' : '' ; ?> />
                </td>
            </tr>
        </tbody>
    </table>
</fieldset>
<fieldset>
    <legend><?php echo JText::_("COM_BIDS_SPECIAL_PRICES"); ?></legend>
    <table width="100%" class="paramlist admintable">
        <tbody>
            <tr>
                <td width="220" class="paramlist key"><?php echo JText::_("COM_BIDS_PRICE_FOR_POWERSELLERS"); ?>: </td>
                <td><input value="<?php echo $this->price_powerseller;?>" class="inputbox" name="price_powerseller" size="5"></td>
            </tr>
            <tr>
                <td width="220" class="paramlist key"><?php echo JText::_("COM_BIDS_PRICE_FOR_VERIFIED_USERS"); ?>: </td>
                <td><input value="<?php echo $this->price_verified;?>" class="inputbox" name="price_verified" size="5"></td>
            </tr>
        </tbody>
    </table>
</fieldset>
<input type="hidden" name="task" value="">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>"/>
<input type="hidden" name="item" value="<?php echo $this->itemname;?>"/>

</form>
