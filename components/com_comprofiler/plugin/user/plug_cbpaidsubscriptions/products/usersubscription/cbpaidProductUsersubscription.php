<?php
/**
 * @version $Id: cbpaidProductUsersubscription.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Paid subscription Plan class
 *
 */
class cbpaidProductUsersubscription extends cbpaidProduct {
	/** Temp internal variable for holding discount in case of upgrade:
	 *  @var float */
	public $_renewalDiscount;
	// /** Timed class for this plan
	//  * @var cbpaidTimed */
	// protected $_timedObject;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( $db );
	}
	/**
	 *	Check for whether dependancies exist for this object in the db schema
	 *
	 *	@param  int      $oid   Optional key index
	 *	@return boolean         TRUE: OK to delete, FALSE: not OK to delete, error in $this->_error
	 */
	public function canDelete( $oid = null ) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = $oid;
		}
		$query = "SELECT COUNT(*)"
			. "\n FROM `#__cbsubs_subscriptions`"
			. "\n WHERE `plan_id` = ". (int) $this->$k
		;
		$this->_db->setQuery( $query );

		$obj = null;
		$count = $this->_db->loadResult($obj);
		if ( $count > 0 ) {
			$this->setError( CBPTXT::T("Subscriptions exist for this plan") );
			return false;
		}
		return parent::canDelete( $oid );
	}
	/**
	 * Creates a subscription object of the appropriate class to the product
	 *
	 * @return cbpaidUsersubscriptionRecord
	 */
	public function & newSubscription() {
		global $_CB_database;

		$sub	=	new cbpaidUsersubscriptionRecord( $_CB_database );
		$sub->set( 'plan_id', $this->get( 'id' ) );
		return $sub;
	}
	/**
	 * Returns remaining value of a subscription with this plan in the plan's currency
	 * at $time when it's expiring at $expiryDate
	 *
	 * @param  int                 $time          Unix-time
	 * @param  string              $expiryDate    SQL datetime
	 * @param  int                 $occurrence    = 0 : first occurrence, >= 1: next occurrences
	 * @param  boolean             $upgraded_sub  TRUE if the underlying subscription is an upgrade from another subscription
	 * @param  cbpaidUsersubscriptionRecord  $subscription  the subscription
	 * @return float                              value
	 */
	public function remainingPriceValue( $time, $expiryDate, $occurrence, $upgraded_sub, &$subscription ) {
		if ( $occurrence == 0 ) {
			if ( $upgraded_sub ) {
				$reason			=	'U';
			} else {
				$reason			=	'N';
			}
		} else {
			$reason				=	'R';
		}

		$quantity				=	1;			//TBD LATER

		$varName				=	$this->getPlanVarName( $reason, $occurrence, 'validity' );
		// $varRate				=	$this->getPlanVarName( $reason, $occurrence, 'rate' );
		$fullPrice				=	$this->getPrice( $this->currency(), $subscription, $reason, $occurrence, $time, 0, $quantity );
		if ( ( $this->get( 'prorate' ) == 0 ) || ( $fullPrice == 0 ) || ( $fullPrice === false ) || ( ! $this->checkValid( $expiryDate, $time ) ) ) {
			// No-prorate plans, as well as free or expired subscriptions don't have a remaining value:
			$roundedRemaingValue				=	0;
		} elseif ( $expiryDate == null ) {
			$roundedRemaingValue				=	$fullPrice;
		} else {
			list($y,  $c,  $d,  $h,  $m,  $s )	=	sscanf( $expiryDate, '%d-%d-%d %d:%d:%d' );
			$remainingPeriod					=	mktime($h, $m, $s, $c, $d, $y) - $time;
			$fullPeriod							=	$this->getFullPeriodValidityTime( $time, $varName );
			if ( ( $fullPeriod === null ) || ( $fullPeriod == 0 ) ) {
				// lifetime or just expired:
				$roundedRemaingValue			=	0;
			} else {
				$remaingValue					=	( $remainingPeriod / $fullPeriod ) * $fullPrice;
				$params							=&	cbpaidApp::settingsParams();
				$priceRoundings					=	$params->get( 'price_roundings', 100 );
				$roundedRemaingValue			=	floor( $remaingValue * $priceRoundings ) / $priceRoundings;
				if ( $roundedRemaingValue > $fullPrice ) {	// got a bonus ?
					$roundedRemaingValue		=	$fullPrice;
				}
			}
		}
		return $roundedRemaingValue;
	}
	/**
	 * Tells if plan is time limited and can expire
	 *
	 * @return boolean	True if product can expire, False if it can not
	 */
	public function isProductWithExpiration() {
		return true;		// override !
	}
	/**
	 * Checks if plan is renewable in advance at all by its params
	 *
	 * @return boolean	True if renewable at all
	 */
	public function isPlanRenewable() {
		return (	( $this->get( 'renewableinadvanceby' ) != '9999-99-99 99:99:99' )		// '9999-99-99 99:99:99' means non-renewable
			&&	( ! ( ( $this->get( 'rate' ) == 0 ) && ( $this->get( 'autorecurring' ) > 0 ) ) )
		);
	}
	/**
	 * Checks if the subscription is renewable given its current $expiryDate and a given $time
	 *
	 * @param  string   $expiryDate     SQL-formatted expiry date or NULL for non-expiring item
	 * @param  int|null $time           UNIX-formatted time (default null: now)
	 * @return boolean	                TRUE if valid (not expired), FALSE otherwise
	 */
	public function checkIfRenewable( $expiryDate, $time = null ) {
		if ( ( $expiryDate == null ) || ( ! $this->isPlanRenewable() ) ) {
			// lifetime or not renewable:
			return false;
		} else {
			list($y,  $c,  $d,  $h,  $m,  $s )	=	sscanf( $expiryDate,							'%d-%d-%d %d:%d:%d' );
			list($yy, $cc, $dd, $hh, $mm, $ss)	=	sscanf( $this->get( 'renewableinadvanceby' ),	'%d-%d-%d %d:%d:%d' );
			$y -= $yy;	$c -= $cc;	$d -= $dd;
			$h -= $hh;	$m -= $mm;	$s -= $ss;
			$renewableFromTimeOnwards			=	mktime($h, $m, $s, $c, $d, $y);
			if ( $time === null ) {
				global $_CB_framework;
				$time							=	$_CB_framework->now();
			}
			return ( $time > $renewableFromTimeOnwards );
		}
	}
	/**
	 * Returns text for button for upgrade, renewals, etc.
	 *
	 * @param  string  $type  'upgrade', 'pay', 'renew', 'reactivate', 'resubscribe', 'unsubscribe', 'delete', default is Apply
	 * @return string         translated button text (without htmlspecialchars, it will be applied on the returned text.
	 */
	public function buttonText( $type ) {
		return parent::buttonText( $type );
	}
	/**
	 * Checks all subscriptions of this plan for mass-expiries
	 *
	 * @param  int           $limit   limit of number of users to expire ( 0 = no limit )
	 * @return int                    Count of subscriptions expired for this plan.
	 */
	public function checkAllSubscriptions( $limit ) {
		global $_CB_framework;

		$now						=	$_CB_framework->now();
		$nowGraceTimeAgo			=	$this->substractValidityFromTime( $this->get( 'graceperiod' ), $now );
		$cutOffDate					=	date( 'Y-m-d H:i:s', $nowGraceTimeAgo );

		$fields						=	array(	'user_id'	);
		$conditions					=	array(	'plan_id'		=>	array( '=', (int) $this->get( 'id' ) ),
			'status'		=>	array( '=', 'A' ),
			'expiry_date'	=>	array( '<', $cutOffDate ),
			'expiry_date '	=>	array( '>', '0000-00-00 00:00:00' )
		);
		$ordering					=	array();
		$subscriptionLoader			=	$this->newSubscription();
		$subscriptionLoader->setMatchingQuery( $fields, $conditions, $ordering, 0, $limit );
		$expiredUsers				=	$this->_db->loadResultArray();

		if ( count( $expiredUsers ) > 0 ) {
			$deactivatedSub			=	null;
			foreach ( $expiredUsers as $user_id ) {
				$paidUserExtension	=&	cbpaidUserExtension::getInstance( $user_id );
				$paidUserExtension->checkUserSubscriptions( false, $deactivatedSub, 'X', true );
			}
		}
		return count( $expiredUsers );
	}

	/**
	 * BACKEND-ONLY XML RENDERING METHODS:
	 */

	/*
	 * USED by XML interface ONLY !!! Renders plan rate (either first or recurring one)
	 *
	 * @param  string           $price       Variable value
	 * @param  ParamsInterface  $params
	 * @return string                        HTML to display
	 * INHERITED:
	public function renderRate( $value, &$params ) {
		if ( ( $value === null ) || ( $value === '' ) || ( $value < 0 ) ) {
			$html		=	'-';
		} else {
			$temp		=	$this->get( 'rate' );
			$this->set( 'rate', $value );
			$currency	=	null;
			$html		=	$this->renderPrice( $value, $currency );
			$this->set( 'rate', $temp );
		}
		return $html;
	}
	*/
	/**
	 * USED by XML interface ONLY !!! Renders plan validity			//NOT YET USED !
	 *
	 * @param  string           $value   Variable value
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderValidity( $value, &$params ) {
		if ( $value == '' ) {
			$html		=	'-';
		} else {
			$temp		=	$this->get( 'validity' );
			$this->set( 'validity', $value );
			$html		=	$this->getFormattedValidity( $value, null, 'validity' );
			$this->set( 'validity', $temp );
		}
		return $html;
	}
}	// class cbpaidProductUsersubscription
