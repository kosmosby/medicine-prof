<?php
/**
 * @version $Id: cbpaidDonationRecord.php 1605 2012-12-29 02:04:26Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Donations database table class
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 */
class cbpaidDonationRecord extends cbpaidNonRecurringSomething {
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db  A database connector object
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_donations', 'id', $db );
	}
	/**
	 * Returns the human name of the record (not translated)
	 *
	 * @return string
	 */
	public function recordName( ) {
		return 'Donation';
	}
	/**
	 * Returns subscription part of article number
	 *
	 * @return string   'Sxxxx' where xxxx is the subscription id.
	 */
	public function getArtNoSubId( ) {
		return 'D' . $this->id;
	}
	/**
	 * Sets amount and currency of the record depending on plan (and on input options if applicable)
	 *
	 * @param  cbPaidProduct  $plan
	 */
	public function getCurrencyAmount( &$plan ) {
		if ( $plan->_options ) {
			$this->amount				=	$plan->_options->get( 'amount' );
		}
		$this->currency					=	$plan->currency();
	}
	/**
	 * SUBSCRIPTION PRESENTATION METHODS:
	 */
	/**
	 * Returns substitution strings
	 *
	 * @see cbpaidSomething::substitutionStrings()
	 *
	 * @param  boolean  $html                              HTML or TEXT return
	 * @param  boolean  $runContentPluginsIfAllowedByPlan  DEFAULT: TRUE
	 * @return array
	 */
	public function substitutionStrings( $html, $runContentPluginsIfAllowedByPlan = true ) {
		$strings						=	parent::substitutionStrings( $html, $runContentPluginsIfAllowedByPlan );

		$plan							=	$this->getPlan();

		// For donations, [PLAN_PRICE] is the amount just donated, as it's user-selectable:
		$strings['PLAN_PRICE']			=	cbpaidMoney::getInstance()->renderPrice( $this->amount, $this->currency, $html, false );
		$strings['PLAN_RATE']			=	sprintf( '%.2f', cbpaidApp::getCurrenciesConverter()->convertCurrency( $this->currency, $plan->currency(), $this->amount ) );
		$strings['PLAN_FIRST_RATE']		=	$strings['PLAN_RATE'];

		return $strings;
	}
}	// class cbpaidDonationRecord
