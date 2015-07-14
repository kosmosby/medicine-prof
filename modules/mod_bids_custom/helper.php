<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
 * 	0 => "latest_auctions",
 * 	1 => "popular_auctions",
 * 	2 => "most_valuable",
 * 	3 => "random_auctions",
 * 	4 => "featured_auctions"
 *
 */
class modBidsHelper{


	static function getRecords( $user_id, $limit ){

		$dbo = JFactory::getDBO();

		$dbo->setQuery( modBidsHelper::getQuery($user_id) );
		$list = $dbo->loadObjectList();

		return $list;
	}

	static function getQuery( $user_id  ){

            $sort_by		=	"start_date";
            $selectCols = array(
                    "a.id",
                    "a.title",
                    "a.userid",
                    "a.auction_type",
                    "a.initial_price",
                    "a.BIN_price",
                    "a.currency",
                    "u.username as by_user",
                    "a.start_date",
                    "a.end_date",
                    'a.params',
                    'a.close_offer',
                    'p.picture',
                    'MAX(b.bid_price) AS maxBid'
            );

            $where = array(
                    "a.published = 1",
                    "a.close_offer = 0",
                    "a.close_by_admin = 0",
                    "a.start_date <= UTC_TIMESTAMP()",
                    "a.end_date >= UTC_TIMESTAMP()"
            );
            $where[] = "a.userid='{$user_id}'";

            $orderings = array();


            $JoinList = array("LEFT JOIN `#__users` as u on a.userid=u.id ",
                                "LEFT JOIN #__bid_pictures AS p on a.id=p.auction_id");


            $orderings[] = "a.$sort_by DESC, p.ordering";

            $query="SELECT ".implode(",",$selectCols).
            " FROM `#__bid_auctions` as a
            LEFT JOIN `#__bids` as b ON a.id=b.auction_id ".

            implode(" \r\n ",$JoinList)."\r\n".
            "WHERE ".implode(" AND ",$where)." ".PHP_EOL.
            "GROUP BY a.id ".PHP_EOL.
            "ORDER BY ".implode(",",$orderings);
            return $query;
	}

}

?>
