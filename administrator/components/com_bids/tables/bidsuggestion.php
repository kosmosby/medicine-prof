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

class JTableBidSuggestion extends JTable {
	
    var $id 		= null;
    var $auction_id	= null;
    var $userid 	= null;
    var $bid_price	= null;
    var $modified 	= null;
    var $quantity 	= null;
    var $status	 	= null;
    var $parent_id 	= null;

    function __construct ( &$db ) {
        parent::__construct( '#__bid_suggestions', 'id', $db );
    }
}
