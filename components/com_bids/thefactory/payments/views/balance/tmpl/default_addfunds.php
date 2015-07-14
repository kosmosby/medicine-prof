<?php defined('_JEXEC') or die('Restricted access'); ?>
<h1><?php echo JText::_("FACTORY_ADD_FUNDS_TO_YOUR_ACCOUNT");?></h1>

<h2><?php echo JText::_("FACTORY_YOUR_CURRENT_BALANCE_IS"),": ",number_format($this->balance->balance,2),' ',$this->balance->currency;?></h2>

<div>
    <form action="index.php" method="post" name="paymentform">
    <table width="75%">
    <tr>
        <td><?php echo JText::_("FACTORY_ADD");?>: </td>
        <td><input name="amount" value="" size="6" type="text" class="inputbox"> <?php echo $this->currency?></td>

    </tr>
    <tr>
        <td align="center"><input type="submit" name="submit" value="<?php echo JText::_("FACTORY_SUBMIT");?>"></td>
        <td>&nbsp;</td>
    </tr>
    </table>
    <input name="option" type="hidden" value="<?php echo APP_EXTENSION;?>">
    <input name="task" type="hidden" value="balance.checkout">
    <input name="Itemid" type="hidden" value="<?php echo $this->Itemid; ?>">
    </form>
</div>
