<?php

defined('_JEXEC') or die('Restricted access');

JToolBarHelper::title(  JText::_( 'COM_BIDS_AUCTIONS_FACTORY' ) . ': <small><small>[ '.JText::_( 'COM_BIDS_REVIEWS_MANAGER' ).' ]</small></small>' , 'bids' );
JToolBarHelper::custom( 'ratings.delete', 'delete', 'delete', JText::_('COM_BIDS_DELETE'),true);
