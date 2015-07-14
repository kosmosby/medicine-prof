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
<table width="100%">
    <tr>
        <td width="60%" valign="top">
            <div id="cpanel">
            <?php
                $link = 'index.php?option=com_bids&amp;task=bid_stats';
                echo JHTML::_('bidsettings.quickIconButton', $link, 'settings.png', JText::_('COM_BIDS_STATISTICS'));

                $link = 'index.php?option=com_bids&amp;task=messages.listing';
                echo JHTML::_('bidsettings.quickIconButton', $link, 'add_db.png', "Messages");

                $link = 'index.php?option=com_bids&amp;task=ratings.listing';
                echo JHTML::_('bidsettings.quickIconButton', $link, 'gsettings.png', "Ratings &amp; Reviews");

                $link = 'index.php?option=com_bids&amp;task=reported.listing';
                echo JHTML::_('bidsettings.quickIconButton', $link, 'settings.png', "Reported Auctions");
            ?>
            </div>
        </td>
    </tr>
</table>
