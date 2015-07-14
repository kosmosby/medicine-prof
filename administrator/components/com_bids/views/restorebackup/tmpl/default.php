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
        var bkp = document.getElementById('backup');
        var loc=document.getElementById('local_file');
        if(!bkp.value && !loc.value){
            alert('<?php echo JText::_('COM_BIDS_ERR_CSV_NO_FILE'); ?>');
            return false;
        }
    }
</script>
<form action="index.php" method="post" name="adminForm" onsubmit="return validateForm();" enctype="multipart/form-data">
    <input type="hidden" name="option" value="com_bids">
    <input type="hidden" name="task" value="admin.restorebackup">

    <table width="100%" class="adminlist" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td width="300">
                <?php echo JText::_('COM_BIDS_BKP_FILE'); ?>
            </td>
            <td>
                <input type="file" name="backup" id="backup" size="50" />
            </td>
        </tr>
        <tr>
            <td>
                <?php echo JText::_('COM_BIDS_LOCAL_FILE'); ?>
            </td>
            <td>
                <input type="texst" name="local_file" id="local_file" size="100" value="<?php echo JPATH_ROOT; ?>">
            </td>
        </tr>

        <tr>
            <td>
                <?php echo JText::_('COM_BIDS_BKP_KEEP'); ?>
            </td>
            <td>
                <?php echo JHTML::_('select.booleanlist', 'overwrite_imgs', '', 0); ?>
            </td>
        </tr>
    </table>
</form>
