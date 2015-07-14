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




// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
jimport('joomla.application.component.helper');

/**
 * @package		Auctions
 */
class bidsModelRatings extends JModel
{
	var $_name='ratings';

    protected $userratings;
    
    function canRate($auctionId,$rater,$rated) {

        $db=$this->getDBO();
		$db->setQuery('SELECT COUNT(1) FROM #__bid_rate WHERE voter_id='.$db->Quote($rater).' AND user_rated_id='.$db->Quote($rated).' AND auction_id='.$db->Quote($auctionId) );
    
		return !($db->loadResult()>0);
    }

    function getUserRatings($userid) {
		
        $db= $this->getDBO();
		$db->setQuery("SELECT sum(rating) as rating, ".
            "count(rating) as count FROM `#__bid_rate` WHERE user_rated_id ='{$userid}' and rate_type='bidder' ");
        $r1 = $db->loadObject();
        
        $result["rating_bidder"]=round(($r1->count>0)?($r1->rating/$r1->count):0,1);
        $result["count_bidder"]=$r1->count;

		$db->setQuery("SELECT sum(rating) as rating, ".
            "count(rating) as count FROM `#__bid_rate` WHERE user_rated_id ='{$userid}' and rate_type='auctioneer' ");
        $r2 = $db->loadObject();
        
        $result["rating_bidder"]=round(($r2->count>0)?($r2->rating/$r2->count):0,1);
        $result["count_bidder"]=$r2->count;
        
        $result["rating_overall"]=round(($r1->count+$r2->count>0)?(($r1->rating+$r2->rating)/($r1->count+$r2->count)):0,1);
        $result["count_overall"]=($r1->count+$r2->count);

        return $result;   
    }
    function getRatingsList($userid)
    {
        $db= $this->getDBO();
		$query = 'select r.*,u.username,a.title from #__bid_rate r
			  left join #__users u on r.voter_id = u.id
			  left join #__bid_auctions a on r.auction_id = a.id
	    	  where r.user_rated_id = '.$db->quote($userid);
		$db->setQuery($query);
		return $db->loadObjectList();
        
    }

    function loadUserRatings($userid) {

        $db = JFactory::getDBO();
        $q = "SELECT r.*,
                us.username,
                a.title, a.id
                FROM #__bid_rate r
                LEFT JOIN #__users us ON r.voter_id=us.id
                LEFT JOIN #__bid_auctions a ON r.auction_id=a.id
                WHERE r.user_rated_id=".$db->quote($userid);
        $db->setQuery($q);

        $this->userratings = $db->loadObjectList();
    }
}
