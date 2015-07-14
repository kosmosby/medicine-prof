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




defined('_JEXEC') or die('Restricted access');

class mod_bidsrateHelper
{
	static function getRatings($type)
	{
        $user = JFactory::getUser();
		$db	= JFactory::getDBO();

        $query	= array();
        if ($type==1 || $type==2) {

    		$query[] = "
                    SELECT
                        a.*,
                        GROUP_CONCAT(b.userid) AS toRateUserids,
                        GROUP_CONCAT(u.username) AS toRateUsernames,
                        GROUP_CONCAT(r.user_rated_id) AS ratedUsers,
                        1 AS isMyAuction,
                        p.picture
                    FROM #__bid_auctions AS a
                    LEFT JOIN #__bids AS b
                        ON a.id=b.auction_id
                    LEFT JOIN #__users AS u
                        ON b.userid=u.id
                    LEFT JOIN #__bid_rate AS r
                        ON a.id=r.auction_id AND r.user_rated_id=b.userid
                    LEFT JOIN #__bid_pictures AS p
                        ON a.id=p.auction_id
                    WHERE
                        a.userid=".$user->id."
                        AND (a.auction_type=3 OR a.close_offer=1)
                        AND b.accept=1
                        AND a.close_by_admin=0
                    GROUP BY a.id";
		}

        if ($type==1 || $type==3) {

    		$query[] = "
                    SELECT
                        a.*,
                        a.userid AS toRateUserids,
                        u.username AS toRateUsernames,
                        GROUP_CONCAT(r.user_rated_id) AS ratedUsers,
                        0 AS isMyAuction,
                        p.picture
                    FROM #__bid_auctions AS a
                    LEFT JOIN #__bids AS b
                        ON a.id=b.auction_id
                    LEFT JOIN #__users AS u
                        ON a.userid=u.id
                    LEFT JOIN #__bid_rate AS r
                        ON a.id=r.auction_id AND r.user_rated_id=a.userid
                    LEFT JOIN #__bid_pictures AS p
                        ON a.id=p.auction_id
                    WHERE
                        b.userid=".$user->id."
                        AND (a.auction_type=3 OR a.close_offer=1)
                        AND b.accept=1
                        AND a.close_by_admin=0
                    GROUP BY a.id";
		}

        $db->setQuery( implode(' UNION ',$query) );
        $result = $db->loadObjectList();

        foreach($result as &$r) {
            $r->toRateUserids = explode(',',$r->toRateUserids);
            $r->toRateUsernames = explode(',',$r->toRateUsernames);
            $r->ratedUsers = explode(',',$r->ratedUsers);
        }

		return $result;	  
	} 

}
