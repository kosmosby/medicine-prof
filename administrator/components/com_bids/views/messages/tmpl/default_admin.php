<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('behavior.formvalidation'); ?>

<form method="POST" action="index.php"  name="adminForm" class="form-validate" onsubmit="return document.formvalidator.isValid(this);">
    <table width="100%">
        <tr>
            <td><?php echo JText::_('COM_BIDS_TOUSER'),": ",$this->auction->username; ?></td>
        </tr>
        <tr>
            <td><?php echo JText::_('COM_BIDS_AUCTION'),": ",$this->auction->title; ?></td>
        </tr>
        <tr>
            <td><?php echo JText::_('COM_BIDS_AUCTIONID'),": ",$this->auction->id; ?></td>
        </tr>
        <tr>
            <td><?php echo JText::_('COM_BIDS_MESSAGE');?>:<br />
                <textarea name="message" cols="80" rows="10" class="required"></textarea>
            </td>
        </tr>
        <tr>
           <td>
               <input type="submit" value="<?php echo JText::_('COM_BIDS_SEND_MESSAGE'); ?>">
           </td>
        </tr>
    </table>
    <input type="hidden" name="id_auction" value="<?php echo $this->auction->id; ?>"/>
    <input type="hidden" name="option" value="com_bids"/>
    <input type="hidden" name="task" value="send_message_auction"/>
</form>
