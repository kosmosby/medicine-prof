<?php
/**
 * @version $Id: cbpaidProductDonation.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Paid cbpaidProductDonation Plan class
 */
class cbpaidProductDonation extends cbpaidProduct {
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( $db );
		// $this->cbpaidTable( '#__cbsubs_plans', 'id', $db );
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
			. "\n FROM `#__cbsubs_donations`"
			. "\n WHERE `plan_id` = ". (int) $this->$k
		;
		$this->_db->setQuery( $query );

		$obj = null;
		$count = $this->_db->loadResult($obj);
		if ( $count > 0 ) {
			$this->setError( CBPTXT::T("Donations exist for this donation plan") );
			return false;
		}
		return parent::canDelete( $oid );
	}
	/**
	 * Creates a subscription object of the appropriate class to the product
	 *
	 * @return cbpaidDonationRecord
	 */
	public function & newSubscription( ) {
		global $_CB_database;

		$sub	=	new cbpaidDonationRecord( $_CB_database );
		$sub->set( 'plan_id', $this->get( 'id' ) );
		return $sub;
	}
	/**
	 * Returns text for button for upgrade, renewals, etc.
	 *
	 * @param  string  $type  'upgrade', 'pay', 'renew', 'reactivate', 'resubscribe', 'unsubscribe', 'delete', default is Apply
	 * @return string         translated button text (without htmlspecialchars, it will be applied on the returned text.
	 */
	public function buttonText( $type ) {
		switch ( $type ) {
			case 'upgrade':
				return CBPTXT::T("Donate");
			case 'pay':
				return CBPTXT::T("Donate Now");
			default:
				return parent::buttonText( $type );
		}
	}

	/**
	 * Gives plan's price depending on a number of parameters
	 *
	 * @param  string           $currency_code   Currency of price needed
	 * @param  cbpaidSomething  $subscription    subscriptions being proposed for upgrade
	 * @param  string           $reason          Payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  int              $occurrence      = 0 : first occurrence, >= 1: next occurrences
	 * @param  int              $startTime       Unix time starting time of plan
	 * @param  float|int        $remainingValue  Remaining value of previous plan to deduct depending on settings
	 * @param  float|int        $quantity        Quantity purchased
	 * @return float|boolean                     Price if possible to determine, FALSE if can't be purchased/subscribed
	 */
	public function getPrice( $currency_code, $subscription, $reason = 'N', $occurrence = 0, $startTime = null, $remainingValue = 0, $quantity = 1 ) {
		global $_PLUGINS;

		if ( isset( $this->_options ) && is_object( $this->_options ) ) {
			$donation	=	$this->_options->get( 'amount' );	// donations are priceless, unless user gives amount.
		} else {
			$donation	=	null;
		}

		$_PLUGINS->trigger( 'onCPayBeforeGetProductPrice', array( $this, $subscription, $reason, $currency_code, &$donation, $occurrence, $startTime, $remainingValue, $quantity ) );

		if ( $donation === null ) {
			$price		=	false;
		} else {
			$price		=	$this->_priceConvert( $currency_code, $donation );
			if ( $price === null ) {
				$price	=	false;
			}
		}

		$_PLUGINS->trigger( 'onCPayAfterGetProductPrice', array( $this, $subscription, $reason, $currency_code, &$price, $occurrence, $startTime, $remainingValue, $quantity ) );
		return $price;
	}
	/**
	 * Returns HTML or TEXT rendering the validity period and pricing for that given plan.
	 *
	 * @param  string       $reason      payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  int          $occurrence  = 0 : first occurrence, >= 1: next occurrences
	 * @param  int          $expiryTime  expiry time of plan
	 * @param  int          $startTime   starting time of plan
	 * @param  boolean      $html        TRUE for HTML, FALSE for TEXT
	 * @param  boolean      $roundings   TRUE: do round, FALSE: do not round display
	 * @param  boolean      $displaySecondaryCurrency   TRUE: display secondary currencies, FALSE: only display in $this->currency()
	 * @return string
	 */
	public function displayPeriodPrice( $reason = 'N', $occurrence = 0, $expiryTime = null, $startTime = null, $html = true, $roundings = true, $displaySecondaryCurrency = true ) {
		global $_PLUGINS;

		$ret								=	'';
		if ( ( $this->_displayPeriodPriceRecursionsLimiter-- == 1 ) && $this->id ) {
			$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
			$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');
			$ret	=	implode( '', $_PLUGINS->trigger( 'onCPayBeforeDisplayProductPeriodPrice', array( &$this, &$reason, &$occurrence, &$expiryTime, &$startTime, &$html, &$roundings, &$displaySecondaryCurrency ) ) );
		}

		$prices								=	explode( ',', $this->getParam( 'donateamount', '' ) );
		if ( count( $prices ) == 1 ) {
			$price							=	(float) $prices[0];
		} else {
			return '-';
		}
		$firstPeriodFullPrice				=	null;
		$firstPeriodPrice					=	null;

		$recurring_max_times				=	0;
		$autorecurring						=	0;

		if ( $reason == 'R' ) {		// renew:
			$prorateDiscount				=	false;
		} else {					// register or upgrade:
			$prorateDiscount				=	( isset( $this->_renewalDiscount ) && ( $this->_renewalDiscount !== null ) );

			if ( $prorateDiscount ) {
				$firstPeriodFullPrice		=	null;
				$firstPeriodPrice			=	$this->_renewalDiscount;
			}
		}

		$displayPeriod						=	false;

		$ret .= $this->renderPeriodPrice( $price, $firstPeriodFullPrice, $firstPeriodPrice, $prorateDiscount, $expiryTime, $startTime,
			$autorecurring, $recurring_max_times, $reason, $occurrence, $html, $roundings, $displayPeriod );

		$args					=	array( $price, $firstPeriodFullPrice, $firstPeriodPrice, $prorateDiscount, $expiryTime, $startTime,
			$autorecurring, $recurring_max_times, $reason, $occurrence, $html, $roundings, $displayPeriod, $displaySecondaryCurrency );
		$method					=	array( $this, 'renderPeriodPrice' );

		if ( ( $this->_displayPeriodPriceRecursionsLimiter == 0 ) && $this->id ) {
			$_PLUGINS->trigger( 'onCPayAfterDisplayProductPeriodPrice', array( $this, &$ret, $method, $args ) );
		}
		++$this->_displayPeriodPriceRecursionsLimiter;

		return $ret;
	}
	/**
	 * Gives number of active sales and turnover in plan's currency
	 *
	 * @return stdClass        ->gross_total : Total turnover in the currency of the plan, ->quantity : Total sales, ->currency : Currency of plan
	 */
	public function getTurnover() {
		global $_CB_database;

		$currency	=	$this->currency();

		$sql		=	'SELECT COUNT(*) AS `quantity`, SUM( ( a.`amount` * ( b.`rate` / c.`rate` ) ) ) AS `gross_total` FROM `#__cbsubs_donations` AS a'
			.	"\n LEFT JOIN `#__cbsubs_currencies` AS b ON b.`currency` = " . $_CB_database->Quote( $currency )
			.	"\n LEFT JOIN `#__cbsubs_currencies` AS c ON c.`currency` = a.`currency`"
			.	"\n WHERE a.`status` = 'A'"
			.	"\n AND a.`plan_id` = " . (int) $this->get( 'id' )
		;
		$this->_db->setQuery( $sql );
		$result		=	null;
		if ( $this->_db->loadObject( $result ) ) {
			/** @var $result cbpaidProductDonationTurnover */
			$result->currency	=	$currency;
		}
		return $result;
	}
}	// class cbpaidProductDonation
/**
 * Data storage class for cbpaidProductDonation::getTurnover()
 */
class cbpaidProductDonationTurnover {
	public $quantity;
	public $gross_total;
	public $currency;
}
