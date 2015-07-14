<?php defined('_JEXEC') or die('Restricted access'); ?>
<form name="adminForm" action="index.php" method="post">
<input type="hidden" name="option" value="com_bids" />
<input type="hidden" name="task" value="increments.save" />
<input type="hidden" name="id" value="<?php echo $this->increment->id;?>" />
<table class="adminform">
    <tr>
        <td width="100"><?php echo JText::_('COM_BIDS_INTERVAL'); ?>:</td>
        <td>
            <input type="text" name="min_bid" style="width:50px;" value="<?php echo $this->increment->min_bid; ?>" /> -
            <input type="text" name="max_bid" style="width:50px;" value="<?php echo $this->increment->max_bid; ?>" />
        </td>
    </tr>
    <tr>
        <td width="100"><?php echo JText::_('COM_BIDS_INCREMENT'); ?>:</td>
        <td><input type="text" name="value" style="width:35px;" value="<?php echo $this->increment->value; ?>" /></td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="submit" value="<?php echo JText::_('COM_BIDS_SAVE'); ?>" />&nbsp;&nbsp;
            <input type="button" value="<?php echo JText::_('COM_BIDS_CANCEL'); ?>" onclick="window.location='index.php?option=com_bids&task=increments.listing&tmpl=component';" />
        </td>
    </tr>
</table>
</form>
