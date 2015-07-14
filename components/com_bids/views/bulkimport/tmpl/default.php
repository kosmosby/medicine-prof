<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
-------------------------------------------------------------------------*/

$doc= JFactory::getDocument();
$doc->addScriptDeclaration(
    "
            function validateForm(){
                var csv = document.getElementById('csv');
                if(!csv.value){
                    alert('".JText::_('COM_BIDS_ERR_CSV_NO_FILE')."');
                    return false;
                }
            }
    "
    );
?>
        <form action="<?php echo JURI::root(); ?>index.php" method="post" name="auctionForm" onsubmit="return validateForm();" enctype="multipart/form-data">
            <input type="hidden" name="option" value="com_bids">
            <input type="hidden" name="task" value="importcsv">
            <input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>">
            <table width="100%" class="contentpaneopen">
                <tr><th colspan="2"><?php echo JText::_('COM_BIDS_AUCTIONS_BULK_IMPORT'); ?></th></tr>
                <tr>
                    <td><?php echo JText::_('COM_BIDS_CSV_FILE'), JHTML::tooltip( JText::_('COM_BIDS_HELP_UPLOADCSV') ); ?></td>
                    <td><input type="file" name="csv" id="csv"></td>
                </tr>
                <tr>
                    <td><?php echo JText::_('COM_BIDS_CSV_IMG_ARCH'), JHTML::tooltip( JText::_('COM_BIDS_HELP_UPLOADIMG') ); ?></td>
                    <td><input type="file" name="arch" id="arch"></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" name="send" value="<?php echo JText::_('COM_BIDS_BUT_SAVE'); ?>" class="auction_button" /></td>
                </tr>
            </table>
        </form>
        <div align="left">
        <?php
        if (count($this->errors) > 0) {
            for ($i = 1; $i <= count($this->errors); $i++) {
                if ($this->errors[$i]) {
                    echo JText::_(bid_line) . " " . $this->errors[$i] . '<br>';
                }
            }
        }
        ?>
        </div>
