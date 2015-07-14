<?php
defined('_JEXEC') or die('Restricted access');

    JSubMenuHelper::addEntry(
        JText::_('COM_BIDS_SETTINGS'),
        'index.php?option=com_bids&task=settingsmanager',
        false
    );
    JToolBarHelper::title(  JText::_( 'COM_BIDS_AUCTIONS_FACTORY' ) . ': <small><small>[ '.JText::_( 'COM_BIDS_COUNTRY_MANAGER' ).' ]</small></small>' , 'bids' );
    JToolBarHelper::custom( 'countries.toggle', 'apply', 'apply', JText::_('COM_BIDS_ENABLEDISABLE'));

?>
