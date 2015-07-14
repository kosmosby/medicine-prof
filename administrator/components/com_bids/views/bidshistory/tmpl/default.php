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

JHTML::_('behavior.modal');
?>

<div>
     <table width="100%" class="adminlist">
     <tr>
        <th><?php echo JText::_("COM_BIDS_USERNAME");?></th>
        <th><?php echo JText::_("COM_BIDS_BID");?></th>
        <th><?php echo JText::_("COM_BIDS_DATE");?></th>
     </tr>
     <?php  foreach($this->bids as $bid) : ?>
        <tr>
            <td><?php echo $bid->username;?></td>
            <td><?php echo number_format($bid->bid_price,2).'&nbsp;'.$this->auction->currency;?></td>
            <td><?php echo $bid->modified; ?></td>
        </tr>
     <?php endforeach;?>
     </table>
</div>
