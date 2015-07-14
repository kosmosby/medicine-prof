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

defined('_JEXEC') or die('Restricted access');
?>
<script language="javascript" type="text/javascript">
    function validateForm(){
        var csv = document.getElementById('csv');

        if(!csv.value){
            alert('<?php echo JText::_('COM_BIDS_ERR_CSV_NO_FILE'); ?>');
            return false;
        }
    }
</script>
<form action="index.php" method="post" name="adminForm" onsubmit="return validateForm();" enctype="multipart/form-data">
    <input type="hidden" name="option" value="com_bids">
    <input type="hidden" name="task" value="importcsv">

    <table width="100%" class="adminlist" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td>
                <?php echo JText::_('COM_BIDS_CSV_FILE'), JHTML::tooltip(JText::_('COM_BIDS_HELP_UPLOADCSV')); ?>
            </td>
            <td>
                <input type="file" name="csv" id="csv" size="50">
            </td>
        </tr>
        <tr>
            <td>
                <?php echo JText::_('COM_BIDS_CSV_IMG_ARCH'), JHTML::tooltip(JText::_('COM_BIDS_HELP_UPLOADIMG')); ?>
            </td>
            <td>
                <input type="file" name="arch" id="arch" size="50">
            </td>
        </tr>

    </table>
</form>
