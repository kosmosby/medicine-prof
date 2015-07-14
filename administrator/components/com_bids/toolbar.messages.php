<?php

defined('_JEXEC') or die('Restricted access');

JToolBarHelper::title(  JText::_( 'COM_BIDS_AUCTIONS_FACTORY' ) . ': <small><small>[ '.JText::_( 'COM_BIDS_MESSAGES_MANAGER' ).' ]</small></small>' , 'bids' );
JToolBarHelper::custom( 'messages.toggle', 'apply', 'apply', JText::_('COM_BIDS_ENABLEDISABLE'),true);
JToolBarHelper::custom( 'messages.delete', 'delete', 'delete', JText::_('COM_BIDS_DELETE'),true);
 
?>
