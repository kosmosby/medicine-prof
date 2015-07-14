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


	static function getRecords( &$params ){

        jimport('joomla.html.parameter');

		$limit = $params->get("nr_auctions_displayed",5);
		$dbo = JFactory::getDBO();
		$dbo->setQuery( modBidsHelper::getQuery($params), 0 , $limit );
		$list = $dbo->loadObjectList();

        foreach($list as $a) {
            $auctionParams = @new JParameter($a->params);
        }
		return $list;
	}

	static function getQuery( &$params ){

            $module_type		=	$params->get("type_display",0);
            $sort_by		=	$params->get("sort_by","start_date");
            $userid=$params->get("filter_user","");
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
            if ($userid) {
                $where[] = "a.userid='{$userid}'";
            }

            switch( $params->get('bin_filter', 0) )
            {
                case 1:
                    $where[] = 'a.auction_type = '.AUCTION_TYPE_PUBLIC;
                    $where[] = 'a.BIN_price>0';
                    break;
                case 2:
                    $where[] = 'a.auction_type = ' . AUCTION_TYPE_BIN_ONLY;
                    break;

            }

            $orderings = array();

            modBidsHelper::_paramQuery($params, $selectCols, $where, $orderings );

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

	static function _paramQuery( $params, &$selectCols , &$where, &$orderings ){

		$module_type		=	$params->get("type_display",0);

		$filter_featured	=	$params->get("featured","");
		if ( $filter_featured )
			$filter_featured	=	"a.featured='$filter_featured'";


		switch ($module_type){
			case '0':
				//
			break;
			case '1':
				if ($filter_featured) {
					$where[] = $filter_featured;
				}
				$orderings[] = "a.hits";
			break;
			case '2':
				if ($filter_featured) {
					$where[] = $filter_featured;
				}
				$orderings[] = "b.bid_price DESC ";
			break;
			case '3':
				if ($filter_featured) {
					$where[] = $filter_featured;
				}
				$orderings[] = "RAND()";
			break;
			case '4':
                $where[] = $filter_featured ? $filter_featured : "a.featured<>'none'";
			break;
            case '5':

                $orderings[] = 'end_date ASC';

                break;
			default:
				//
			break;
		}

	}
}

?>