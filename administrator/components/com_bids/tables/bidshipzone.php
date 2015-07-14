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

class JTableBidShipZone extends JTable {

    var $id	 = null;
    var $name	 = null;
    var $state	 = null;

    function __construct(&$db) {
        parent::__construct('#__bid_shipment_zones','id',$db);
    }

    function getList(){

        $this->_db->setQuery('SELECT * FROM '.$this->_tbl.' ORDER BY `name`');
        return $this->_db->loadObjectList();
    }

    function getPriceList($auctionID, $all = false) {

        $db = JFactory::getDbo();

        $all_filter = $all ? 'OR a.auction IS NULL' : '';
        $db->setQuery(
            'SELECT DISTINCT z.*, a.price
                FROM `#__bid_shipment_zones` AS z
                LEFT JOIN #__bid_shipment_prices AS a
                    ON z.id = a.zone
                WHERE auction = '.$db->Quote($auctionID).
                $all_filter );

        return $db->loadObjectList();
    }
}
