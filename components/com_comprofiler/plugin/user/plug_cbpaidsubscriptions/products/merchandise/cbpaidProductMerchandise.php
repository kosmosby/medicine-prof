<?php
/**
 * @version $Id: cbpaidProductMerchandise.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Paid cbpaidProductMerchandise Plan class
 */
class cbpaidProductMerchandise extends cbpaidProduct {
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
			. "\n FROM `#__cbsubs_merchandises`"
			. "\n WHERE `plan_id` = ". (int) $this->$k
		;
		$this->_db->setQuery( $query );

		$obj = null;
		$count = $this->_db->loadResult($obj);
		if ( $count > 0 ) {
			$this->setError( CBPTXT::T("Merchandises exist for this merchandise plan") );
			return false;
		}
		return parent::canDelete( $oid );
	}
	/**
	 * Creates a subscription object of the appropriate class to the product
	 *
	 * @return cbpaidMerchandiseRecord
	 */
	public function & newSubscription( ) {
		global $_CB_database;

		$sub	=	new cbpaidMerchandiseRecord( $_CB_database );
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
				return CBPTXT::T("Buy");
			case 'pay':
				return CBPTXT::T("Buy Now");
			default:
				return parent::buttonText( $type );
		}
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

		$price								=	$this->get( 'rate' );
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
			$autorecurring, $recurring_max_times, $reason, $occurrence, $html, $roundings, $displayPeriod, $displaySecondaryCurrency );

		$args					=	array( $price, $firstPeriodFullPrice, $firstPeriodPrice, $prorateDiscount, $expiryTime, $startTime,
			$autorecurring, $recurring_max_times, $reason, $occurrence, $html, $roundings, $displayPeriod, $displaySecondaryCurrency );
		$method					=	array( $this, 'renderPeriodPrice' );

		if ( ( $this->_displayPeriodPriceRecursionsLimiter == 0 ) && $this->id ) {
			$_PLUGINS->trigger( 'onCPayAfterDisplayProductPeriodPrice', array( $this, &$ret, $method, $args ) );
		}
		++$this->_displayPeriodPriceRecursionsLimiter;

		return $ret;
	}
}	// class cbpaidProductMerchandise
