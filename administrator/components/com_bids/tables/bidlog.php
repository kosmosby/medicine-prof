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

class JTableBidLog extends JTable {

    var $id             = null;
    var $auction_id     = null;
    var $userid         = null;
    var $id_proxy       = null;
    var $initial_bid    = null;
    var $bid_price      = null;
    var $payment        = null;
    var $cancel         = null;
    var $accept         = null;
    var $modified       = null;
    var $quantity       = null;

    function __construct( &$db ) {

        parent::__construct( '#__bid_log', 'id', $db );
    }

    function loadNthBest($auctionId,$userid,$n=1) {

        $db = $this->getDbo();
        $q = $db->getQuery(true);
        $q->select('*')
            ->from($this->getTableName())
            ->where('auction_id='.$db->quote($auctionId))
            ->where('userid='.$db->quote($userid))
            ->order('bid_price DESC');

        $db->setQuery( $q, $n>=1 ? ($n-1) : 0, 1);

        $this->bind($db->loadObject());
    }
}
