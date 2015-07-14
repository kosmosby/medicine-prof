<?php

defined('_JEXEC') or die('Restricted access');

JToolBarHelper::title(  JText::_( 'COM_BIDS_AUCTIONS_FACTORY' ) . ': <small><small>[ '.JText::_( 'COM_BIDS_SHIPPING_ZONE_MANAGER' ).' ]</small></small>' , 'bids' );
JToolBarHelper::custom( 'reported.toggle', 'apply', 'apply', JText::_('COM_BIDS_TOGGLE'),true);
