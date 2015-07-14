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

defined( '_JEXEC' ) or die( 'Restricted access' );

class JTableBid extends JTable {

    var $id             = null;
    var $auction_id     = null;
    var $userid         = null;
    var $initial_bid    = null;
    var $bid_price      = null;
    var $payment        = null;
    var $cancel         = null;
    var $accept         = null;
    var $modified       = null;
    var $id_proxy       = null;
    var $quantity       = null;

    function __construct( &$db ) {

        parent::__construct( '#__bids', 'id', $db );
    }

    function getAuction() {

        $a =  JTable::getInstance('auction') ;
        $a->load($this->auction_id);

        return $a;
    }
}
