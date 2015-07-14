<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php

?>
<form action="index.php" method="get" name="adminForm">
    <input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
    <input type="hidden" name="task" value="pricing.config" />
    <input type="hidden" name="item" value="comission" />
    <input type="hidden" name="userid" value="0" />
    <input type="hidden" name="commissionType" value="" />

    <div style="font-size: 14px; font-weight: bold;;">
        <?php echo JText::_('COM_BIDS_COMMISSION_TYPE') . ' ' . $this->lists['selectType']; ?>
    </div>
</form>
