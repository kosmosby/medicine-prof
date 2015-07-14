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

class JTableBidIncrement extends JTable {

    var $id			 = null;
    var $min_bid	 = null;
    var $max_bid	 = null;
    var $value	 	 = null;

    function __construct(&$db) {
        parent::__construct('#__bid_increment','id',$db);
    }

    function getIncrement($value) {

        $this->_db->setQuery(" SELECT * FROM `#__bid_increment` WHERE {$value} BETWEEN min_bid AND max_bid  ORDER BY min_bid DESC ");
        //echo $this->_db->_sql;
        $tmp = $this->_db->loadObject();
        $this->bind($tmp);

        return $this;
    }

    function getList($min = null) {

        $filter = $min ? " WHERE  $min < max_bid" : '';
        $this->_db->setQuery(" SELECT * FROM `#__bid_increment` $filter order by `min_bid`");

        return $this->_db->loadObjectList();
    }	
}
