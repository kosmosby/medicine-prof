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

$lists = $this->lists;
?>

<form action="index.php" method="post" name="adminForm">

    <input type="hidden" name="option" value="com_bids" />
    <input type="hidden" name="task" value="quicksavecats" />
    <input type="hidden" name="tmpl" value="component" />

    <table width="100%">
        <tr>
            <td style="width:200px;">
                <?php echo JText::_('COM_BIDS_CATEGORY_PARENT'); ?>
            </td>
            <td>
                <?php echo $lists['parent']; ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php echo JText::_('COM_BIDS_QUICKADD_TEXT') ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <textarea rows="25" cols="50" name="catstext"></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" value="Save" />
            </td>
        </tr>
    </table>
</form>