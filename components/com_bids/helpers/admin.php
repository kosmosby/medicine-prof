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

class BidsHelperAdmin {

    static function subMenuHelper() {

        JSubMenuHelper::addEntry(JText::_('COM_BIDS_ADMINMENU_AUCTIONS'), 'index.php?option=com_bids&task=offers');
        JSubMenuHelper::addEntry(JText::_('COM_BIDS_ADMINMENU_PAYMENTS'), 'index.php?option=com_bids&task=payments.listing');
        JSubMenuHelper::addEntry(JText::_('COM_BIDS_ADMINMENU_MONITORING'), 'index.php?option=com_bids&task=misclists');
        JSubMenuHelper::addEntry(JText::_('COM_BIDS_ADMINMENU_TOOLS'), 'index.php?option=com_bids&task=auctionmanager');
        JSubMenuHelper::addEntry(JText::_('COM_BIDS_ADMINMENU_USERS'), 'index.php?option=com_bids&task=users');
        JSubMenuHelper::addEntry(JText::_('COM_BIDS_ADMINMENU_SETTINGS'), 'index.php?option=com_bids&task=settingsmanager');
        JSubMenuHelper::addEntry(JText::_('COM_BIDS_ADMINMENU_ABOUT'), 'index.php?option=com_bids&task=about.main');
    }
}