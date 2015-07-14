<?php
/**
 * @version $Id: cbpaidOrdersMgr.php 1546 2012-12-02 23:16:25Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Baskets manager class
 */
class cbpaidOrdersMgr {
	/**
	 * Empty constructor
	 */
	private function __construct() {
	}
	/**
	 * Gets a single instance of the cbpaidOrdersMgr class
	 *
	 * @return cbpaidOrdersMgr
	 */
	public static function & getInstance( ) {
		static $singleInstance			=	null;

		if ( $singleInstance === null ) {
			$singleInstance				=	new self();
		}
		return $singleInstance;
	}
	/**
	 * Times out unused baskets according to general settings
	 *
	 * @param  int|null  $userId  Timeout for User id only (triggered by user), or if 0: triggered by admin
	 * @param  int       $limit   Maximum number of baskets to timeout this time
	 * @return int|null           Number of baskets that just timed out
	 */
	public function timeoutUnusedBaskets( $userId, $limit = 100 ) {
		global $_CB_database;

		$params		=	cbpaidApp::settingsParams();

		$query		=	"SELECT b.id FROM #__cbsubs_payment_baskets b"
			.	"\n WHERE b.payment_status = 'NotInitiated'"
			.	"\n AND b.payment_method IS NULL"
		;
		if ( $userId ) {
			$query	.=	"\n AND b.user_id = " . (int) $userId;
			$hours	=	$params->get( 'basket_timeout_user', 3 );
		} else {
			$hours	=	$params->get( 'basket_timeout_admin', 24 );
		}
		$query		.=	"\n AND b.time_initiated < DATE_SUB( NOW(), INTERVAL " . ( (int) $hours ) . " HOUR)";
		$_CB_database->setQuery( $query, 0, $limit );
		$ids		=	$_CB_database->loadResultArray();
		if ( is_array( $ids ) ) {
			foreach ( $ids as $basketId ) {
				$paymentBasket	=	new cbpaidPaymentBasket( $_CB_database );
				$paymentBasket->historySetMessage( 'Basket timeout' );
				$paymentBasket->delete( $basketId );
			}
			return count( $ids );
		} else {
			return null;
		}

	}
	/**
	 * Get the most recent unpaid payment basket for that user
	 *
	 * @param  int      $userId
	 * @return cbpaidPaymentBasket|boolean  Basket or false = db_error
	 */
	public function loadCurrentUnpaidBasket( $userId ) {
		global $_CB_framework;

		$cbpaidTimes			=&	cbpaidTimes::getInstance();
		$basket					=	$this->_tryLoadCurrentUnpaidBasket( $userId );
		if ( $basket ) {
			$timeInitiated		=	$cbpaidTimes->strToTime( $basket->time_initiated );
			$maxInitiatedTime	=	$_CB_framework->now() -10800;	// NOW - 3 hours

			if ( $timeInitiated < $maxInitiatedTime ) {
				$this->timeoutUnusedBaskets( $userId );
				$basket			=	null;
			}

		}
		return $basket;
	}
	/**
	 * Get the most recent unpaid payment basket for that user
	 *
	 * @param  int      $userId
	 * @return cbpaidPaymentBasket|boolean  Basket or false = db_error
	 */
	private function _tryLoadCurrentUnpaidBasket( $userId ) {
		global $_CB_database;

		$query		=	"SELECT b.*"
			. "\n FROM #__cbsubs_payment_baskets b"
			. "\n WHERE b.user_id = " . (int) $userId
			. "\n AND b.payment_status = 'NotInitiated'"
			. "\n AND b.payment_method IS NULL"
			// . "\n AND b.time_initiated < DATE_SUB( NOW(), INTERVAL 3 HOUR)"
			. "\n ORDER BY b.time_initiated DESC"
		;
		$_CB_database->setQuery( $query, 0, 1 );
		$object		=	new cbpaidPaymentBasket( $_CB_database );
		if ( $_CB_database->loadObject( $object ) ) {
			return $object;
		} else {
			return null;
		}
	}
	/**
	 * Computes start time from $endTime for $showPeriod date
	 * @param  string  $showPeriod  SQL DATETIME formatted period of time
	 * @param  int     $endTime     End time
	 * @return int|null             Start-time or null for not computable
	 */
	private function _periodOfValidity( $showPeriod, $endTime ) {
		if ( $showPeriod && ( $showPeriod != '0000-00-00 00:00:00' ) ) {
			$periodArray	=	sscanf( $showPeriod, '%d-%d-%d %d:%d:%d');
			$d				=	explode( ' ', date( 'Y m d H i s', $endTime ) );
			for ( $i = 0; $i < 6; ++$i ) {
				$d[$i]		-=	$periodArray[$i];
			}
			$startTime		=	mktime( $d[3], $d[4], $d[5], $d[1], $d[2], $d[0] );

		} else {
			$startTime	=	null;
		}
		return $startTime;
	}
	/**
	 * Method used to get the baskets to show the invoices list of a user
	 *
	 * @param  UserTable                  $user
	 * @param  string                     $showPeriod  SQL DATETIME formatted period of time
	 * @param  int                        $endTime
	 * @param  boolean                    $countOnly   Count only, do not get them
	 * @return cbpaidPaymentBasket[]|int
	 */
	public function getBaskets( $user, $showPeriod, $endTime, $countOnly ) {
		global $_CB_database;

		$startTime		=	$this->_periodOfValidity( $showPeriod, $endTime );
		if ( $startTime ) {
			$dateLimit	=	date( 'Y-m-d H:i:s', $startTime );
		} else {
			$dateLimit	=	null;
		}
		if ( $countOnly ) {
			$query		=	"SELECT COUNT(*)";
		} else {
			$query		=	"SELECT b.*";
		}
		$query			.=	"\n  FROM #__cbsubs_payment_baskets b"
			.	"\n  WHERE b.user_id = " . (int) $user->id
		;
		if ( $dateLimit ) {
			$query		.=	"\n AND b.time_initiated > " . $_CB_database->Quote( $dateLimit );
		}
		$query			.=	"\n AND payment_status IN ( 'Completed', 'Pending' )"
			.	"\n ORDER BY b.time_initiated DESC"
		;

		$_CB_database->setQuery( $query );
		if ( $countOnly ) {
			$baskets	=	$_CB_database->loadResult();
		} else {
			$loaderBasket	=	new cbpaidPaymentBasket( $_CB_database );
			$baskets	=	$loaderBasket->loadTrueObjects( null, 'id' );
		}
		return $baskets;
	}
	/**
	 * Loads matured baskets scheduled by CBSubs for timed action
	 *
	 * @param  int  $limit
	 * @return cbpaidPaymentBasket[]
	 */
	public function loadMaturedBaskets( $limit ) {
		global $_CB_framework;

		$loaderBasket	=	new cbpaidPaymentBasket();
		$baskets		=	$loaderBasket->loadThisMatchingList( array(	'scheduler_next_maturity' => array( '<=', date( 'Y-m-d H:i:s', $_CB_framework->now()) ),
				'scheduler_next_maturity ' => array( '>',  $loaderBasket->getDbo()->getNullDate() )			// the space in KEY is ON PURPOSE to avoid same array entry !!!
			),
			array( 'scheduler_next_maturity' => 'ASC' ),
			0,
			$limit );
		return $baskets;
	}
	/**
	 * Trigger basket events for autorecurring payments
	 *
	 * @param  int  $limit
	 * @return array           of each payment made
	 */
	public function triggerScheduledAutoRecurringPayments( $limit ) {
		$results		=	array();
		$baskets		=&	$this->loadMaturedBaskets( $limit );
		foreach ( $baskets as $b ) {
			$results[]	=	$b->triggerScheduledAutoRecurringPayment();
		}
		return $results;
	}
}
