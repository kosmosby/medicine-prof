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

class JTableBidUser extends FactoryFieldsTbl {

    var $userid		= null;
    var $name		= null;
    var $surname	= null;
    var $address	= null;
    var $city		= null;
    var $country	= null;
    var $phone		= null;
    var $modified   = null;
    var $verified	= null;
    var $isBidder	= null;
    var $isSeller	= null;
    var $powerseller	= null;
    var $paypalemail    = null;

    function __construct( &$db ) {

        parent::__construct( '#__bid_users', 'userid', $db );
    }
    function createRecord($userid)
    {
        $database= $this->getDBO();
        $database->setQuery("insert into ".
            $this->getTableName().
            " (userid) values ('{$userid}')"
        );
        return $database->query();
        
    }
    function getRatingsList($uid=null) {

        if (!$uid) {
            $uid=$this->userid;
        }
        $database= $this->getDBO();
        $database->setQuery("SELECT rating,count(*) AS nr  FROM `#__bid_rate` WHERE `user_rated_id`='$uid' GROUP BY rating ORDER BY rating");

        return $database->loadAssocList("rating");
    }
}
?>
