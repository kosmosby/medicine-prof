<?php defined('_JEXEC') or die('Restricted access'); ?>
<h2><?php echo JText::_("FACTORY_ADD_NEW_MANUAL_PAYMENT");?></h2>
<form action="index.php" method="get" name="adminForm">
    <div class='width-100 fltlft'>
        <fieldset class="adminform">
        <legend><?php echo JText::_( 'FACTORY_PAYMENT_DETAILS' ); ?></legend>
            <table class="paramlist admintable" border="0" width="880">
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_AMOUNT"),":"; ?>
                    </td>
                    <td class="paramlist_value">
                        <input name="amount" size="20" value="">&nbsp;<?php echo JHtml::_("payments.currencylist"); ?>
                    </td>
                </tr>
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_REFERENCE_NUMBER"),":"; ?>
                    </td>
                    <td class="paramlist_value">
                        <input name="refnumber" size="20" value="">
                    </td>
                </tr>
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_STATUS"),":"; ?>
                    </td>
                    <td class="paramlist_value">
                        <?php echo JHtml::_("payments.statuslist"); ?>
                    </td>
                </tr>
                <tr>
                    <td class="paramlist_key" align="left" width="250">
                        <?php echo JText::_("FACTORY_USER"),":"; ?>
                    </td>
                    <td class="paramlist_value">
                        <?php echo JHtml::_("payments.userlist"); ?>
                    </td>
                </tr>
            </table>
        </fieldset>
    </div>
    <input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
    <input type="hidden" name="task" value="payments.savepayment" />
</form>
