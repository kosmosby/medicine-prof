<?php
/**
 * @version $Id: cbpaidPaymentBasket.php 1608 2012-12-29 04:12:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\ComprofilerTable;
use CB\Database\Table\UserTable;
use CBLib\Database\Table\TableInterface;
use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Payment Basket database table class
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 */
class cbpaidPaymentBasket extends cbpaidPaymentBaseEntries {
	// public $id					=	null;		+ a lot of others are inherited
	public $user_id;						//++
	public $owner					=	0;
	// public $log_type;
	public $payment_method;					//++
	public $gateway_account;				//++
	// public $time_received;
	public $time_initiated;					//++
	public $time_completed;					//++
	public $time_completed_date;			//++
	public $time_completed_day_of_week; 	//++
	public $time_completed_yearweek;		//++
	public $time_completed_yearmonth;		//++
	public $time_completed_hour;			//++
	public $shared_secret;					//++
	// public $raw_data;
	// public $raw_result;
	public $ip_addresses;
	public $is_business;
	public $vat_verification;
	/**
	 * @since 1.2
	 */
	public $recur_times_used;
	public $reattempts_tried;
	public $scheduler_state;
	public $scheduler_next_maturity;
	/**
	 * @since 1.3
	 */
	public $proformainvoice;

	// Private variables:
	/** @var cbpaidPaymentItem[] */
	private $_paymentItems			=	null;
	/** @var cbpaidPaymentTotalizer[] */
	private $_paymentTotalizers		=	null;
	/** @var cbpaidUsersubscriptionRecord[] */
	private $_subscriptions				=	null;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_payment_baskets', 'id', $db );
		$this->_historySetLogger();
	}
	/**
	 * If table key (id) is NULL : inserts a new row
	 * otherwise updates existing row in the database table
	 *
	 * Can be overridden or overloaded by the child class
	 *
	 * @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	 * @return boolean                TRUE if successful otherwise FALSE
	 */
	public function store( $updateNulls=false ) {
		global $_CB_framework;
		if ( $this->time_completed && ( $this->time_completed != $this->_db->getNullDate() ) ) {
			$offset								=	(int) $_CB_framework->getCfg( 'offset' ) * 3600;

			list($y, $c, $d, $h, $m, $s)		=	sscanf( $this->time_completed, '%d-%d-%d %d:%d:%d');
			$time_paid							=	mktime($h, $m, $s, $c, $d, $y);			// we do NOT use PHP strtotime, which is broken
			$time_paid							+=	$offset;

			$dateDayHour						=	explode( ' ', date( 'Y-m-d w H o W', $time_paid ) );
			$dateDayHour[1]						+=	1;			// --> 1 = Sunday...7 = Saturday, ISO-8601 numeric representation of the day of the week, like MySQL

			$this->time_completed_date			=	$dateDayHour[0];
			$this->time_completed_day_of_week	=	$dateDayHour[1];
			$this->time_completed_yearweek		=	$dateDayHour[3] . '-W' . $dateDayHour[4];
			$this->time_completed_yearmonth		=	substr( $dateDayHour[0], 0, 7 );
			$this->time_completed_hour			=	$dateDayHour[2];
		}
		return parent::store( $updateNulls );
	}
	/**
	 * Authorize action on basket
	 *
	 * @param  string  $action  'pay', 'recordpayment', 'refund'
	 * @return boolean
	 */
	public function authoriseAction( $action ) {
		if ( $action == 'pay' ) {
			global $_CB_framework;
			if ( $_CB_framework->myId() == $this->user_id ) {
				// You can always pay for yourself (for now at least):
				return true;
			} else {
				return cbpaidApp::authoriseAction( 'cbsubs.recordpayments' );
			}
		}
		return cbpaidApp::authoriseAction( $action );
	}
	/**
	 * create a paymentBasket in database
	 *
	 * @param  UserTable  $user
	 * @param  float      $price
	 * @param  string     $currency
	 * @param  int        $quantity
	 * @param  string     $item_number
	 * @param  string     $item_name
	 * @param  boolean    $store         default: TRUE: store object in database, FALSE: keep in memory only
	 * @param  int        $now           unix time
	 * @param  int        $owner         basket owner (seller)
	 * @param  string     $reason        payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 */
	public function createPaymentBasket( &$user, $price, $currency, $quantity, $item_number, $item_name, $store, $now, $owner, /** @noinspection PhpUnusedParameterInspection */ $reason ) {
		global $_CB_database;

		$this->reset();

		$this->user_id			=	(int) $user->id;
		$this->owner			=	(int) $owner;
		$this->payment_status	=	'NotInitiated';
		$this->time_initiated	=	date( 'Y-m-d H:i:s', $now );
		$this->ip_addresses		=	cbpaidRequest::getIPlist();
		$this->mc_gross			=	$price;		// for now, later sum...
		$this->mc_currency		=	$currency;
		$this->quantity			=	$quantity;
		$this->item_number		=	$item_number;
		$this->item_name		=	$item_name;
		$this->setRandom_shared_secret();
		$this->_setInvoicingAddress( $user );
		if ( $store ) {
			$this->historySetMessage( 'Creating new payment basket' );
			if ( ! $this->store() ) {
				trigger_error( 'payment_basket store error:' . htmlspecialchars( $_CB_database->getErrorMsg() ), E_USER_ERROR );
			}
		}
	}
	/**
	 * Computes and keeps in memory object a random unique shared secret
	 *
	 */
	public function setRandom_shared_secret( ) {
		$this->shared_secret	=	str_replace( '.', '', uniqid( 'cbp', true ) );
	}
	/**
	 * Computes and sets the invoice number to $this->invoice
	 *
	 * @param  UserTable  $user
	 * @param  string     $reason       Payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @return void
	 */
	protected function setProformaInvoiceNumber( $user, $reason ) {
		if ( $this->invoice ) {
			// Never set again a new proforma invoice a second time:
			return;
		}
		$params					=	cbpaidApp::settingsParams();
		if ( $params->get( 'invoices_numbering_type', 0 ) == 0 ) {
			$invoiceType		=	'invoice';
		} else {
			$invoiceType		=	'proformainvoice';
		}
		$this->_setInvoiceNumber( $invoiceType, $user, $reason, $this->item_number );
	}
	/**
	 * Computes and sets the invoice number to $this->invoice
	 *
	 * @param  string              $reason       Payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @return void
	 */
	public function setPaidInvoiceNumber( $reason ) {
		$params							=	cbpaidApp::settingsParams();
		if ( $params->get( 'invoices_numbering_type', 0 ) >= 1 ) {
			// Never set again a new final invoice a second time:
			if ( $this->proformainvoice === null ) {
				$this->proformainvoice	=	(string) $this->invoice;
				$user					=	CBuser::getUserDataInstance( $this->user_id );
				$this->_setInvoiceNumber( 'invoice', $user, $reason, $this->item_number );
			}
		}
	}
	/**
	 * Gets the title format for the invoice
	 *
	 * @return string  'Invoice [INVOICENUMBER]' or 'Proforma Invoice [INVOICENUMBER]' (or user-defined)
	 */
	public function getInvoiceTitleFormat( ) {
		if ( ! $this->invoice ) {
			// No invoice number at all:
			return null;
		}
		$params						=	cbpaidApp::settingsParams();

		if ( ( $params->get( 'invoices_numbering_type', 0 ) >= 1 ) && ( $this->proformainvoice == null ) ) {
			$invoiceType			=	'proformainvoice';
			$default				=	'Proforma Invoice [INVOICENUMBER]';		// CBPTXT::T("Proforma Invoice [INVOICENUMBER]")
		} else {
			$invoiceType			=	'invoice';
			$default				=	'Invoice [INVOICENUMBER]';				// CBPTXT::T("Invoice [INVOICENUMBER]")
		}
		return trim( $params->get( $invoiceType . '_title_format', $default ) );		// This is 'invoice_title_format', 'proformainvoice_title_format'
	}
	/**
	 * Computes and sets the invoice number to $this->invoice
	 *
	 * @param  string     $invoiceType  Type of number ('proformainvoice', 'invoice', later: 'quote')
	 * @param  UserTable  $user
	 * @param  string     $reason       Payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  string     $item_number
	 */
	protected function _setInvoiceNumber( $invoiceType, $user, $reason, $item_number ) {
		$params						=	cbpaidApp::settingsParams();
		$invoiceFormat				=	trim( $params->get( $invoiceType . '_number_format' ) );	// This is 'invoice_number_format', 'proformainvoice_number_format'
		if ( $invoiceFormat ) {
			$increment				=	trim( $params->get( $invoiceType . '_number_increment' ) );	// This is 'invoice_number_increment', 'proformainvoice_number_increment'
			$extraStrings			=	array(	'EMAILADDRESS'			=>	$user->email,
				'PREFIX_TEXT'			=>	$reason,
				'ARTNUMS'				=>	$item_number
			);
			// Autoloads cbpaidPaymentNumber:
			$this->invoice			=	cbpaidPaymentNumber::generateUniqueNumber( $invoiceType, $invoiceFormat, $user->id, $extraStrings, $increment );
		}
	}
	/**
	 * Generates extra strings for substitutions for invoices
	 *
	 * @return array
	 */
	protected function getInvoiceExtraStrings( ) {
		global $_CB_framework;

		$user						=	CBuser::getUserDataInstance( $this->user_id );
		$extraStrings				=	array(	'SITENAME'				=>	$_CB_framework->getCfg( 'sitename' ),
			'SITEURL'				=>	$_CB_framework->getCfg( 'live_site' ),
			'EMAILADDRESS'			=>	$user->email,
			'INVOICE_ID'			=>	$this->id,
			'INVOICE_NO'			=>	$this->invoice,
			'PROFORMA_INVOICE_NO'	=>	$this->proformainvoice,
			'ITEMS_NAME'			=>	$this->item_name,
			'ITEMS_NUMBERS'			=>	$this->item_number,
			'PAYMENT_STATUS'		=>	$this->payment_status,
			'PAYMENT_METHOD'		=>	$this->payment_method,
			'DATE_ORDERED'			=>	cbFormatDate( $this->time_initiated, 1, false ),
			'DATE_TIME_ORDERED'		=>	cbFormatDate( $this->time_initiated, 1, true ),
			'DATE_PAID'				=>	cbFormatDate( $this->time_completed, 1, false ),
			'TRANSACTION_ID'		=>	$this->txn_id,
			'TRANSACTION_TYPE'		=>	$this->txn_type,
			'CURRENCY'				=>	$this->mc_currency,
			'TOTAL_PRICE'			=>	sprintf( '%0.2f', $this->mc_gross ),
			'TAX_AMOUNT'			=>	sprintf( '%0.2f', $this->tax ),
			'ADDRESS_FIRST_NAME'	=>	$this->first_name,
			'ADDRESS_LAST_NAME'		=>	$this->last_name,
			'ADDRESS_COMPANY'		=>	$this->payer_business_name,
			'ADDRESS_STREET'		=>	$this->address_street,
			'ADDRESS_CITY'			=>	$this->address_city,
			'ADDRESS_STATE'			=>	$this->address_state,
			'ADDRESS_ZIPCODE'		=>	$this->address_zip,
			'ADDRESS_COUNTRY'		=>	$this->address_country,
			'ADDRESS_PHONE'			=>	$this->contact_phone,
			'PAYER_EMAIL'			=>	$this->payer_email,
			'ADDRESS_VAT_NUMBER'	=>	$this->vat_number
		);
		return $extraStrings;
	}
	/**
	 * Returns substitution strings
	 *
	 * @see cbpaidSomething::substitutionStrings()
	 *
	 * @param  boolean  $html   HTML or TEXT return
	 * @return array
	 */
	public function substitutionStrings( $html ) {
		$user										=	CBuser::getUserDataInstance( (int) $this->user_id );

		$extraStrings								=	$this->getInvoiceExtraStrings();

		if ( $html ) {
			$itsmyself								=	true;				// Show user's view of invoice always
			$extraStrings['INVOICE_CONTENT_HTML']	=	$this->displayInvoice( $user, $itsmyself, false );
			$extraStrings['INVOICE_ITEMS_HTML']		=	$this->displayBasket( null, null, 'invoice' );
			$extraStrings['BASKET_CONTENT_HTML']	=	$this->displayBasket( null, null );
		}

		return $extraStrings;
	}
	/**
	 * Sets the invoicing address off from the CB user profile.
	 * Specifically following fields:
	 *
	 * payer_business_name
	 * address_name
	 * first_name
	 * last_name
	 * address_street
	 * address_city
	 * address_state
	 * address_zip
	 * address_country
	 * address_country_code
	 * payer_email
	 * contact_phone
	 *
	 * @param  UserTable  $user
	 * @return void
	 */
	protected function _setInvoicingAddressFromUserProfile( $user ) {
		global $_CB_database;

		$params					=&	cbpaidApp::settingsParams();

		$fieldsSettings			=	array(
			'invoice_cb_field_name'			=>	'address_name',
			'invoice_cb_field_company'		=>	'payer_business_name',
			'invoice_cb_field_address'		=>	'address_street',
			'invoice_cb_field_city'			=>	'address_city',
			'invoice_cb_field_state'		=>	'address_state',
			'invoice_cb_field_zipcode'		=>	'address_zip',
			'invoice_cb_field_country'		=>	'address_country',
			'invoice_cb_field_phone'		=>	'contact_phone',
			'invoice_cb_field_vat_number'	=>	'vat_number'
		);
		// fieldid => payment basket variable
		$cbFieldIds						=	array();
		foreach ( $fieldsSettings as $k => $v ) {
			$fieldId					=	(int) $params->get( $k );
			if ( $fieldId ) {
				$cbFieldIds[$fieldId]	=	$v;
			}
		}
		if ( count( $cbFieldIds ) > 0 ) {
			$_CB_database->setQuery("SELECT `name`, `fieldid` FROM #__comprofiler_fields WHERE published = 1 AND fieldid IN (" . implode( ',', array_keys( $cbFieldIds ) ) . ");" );
			$allFields						=	$_CB_database->loadAssocList( 'fieldid' );
			if ( is_array( $allFields ) ) {
				foreach ( $allFields as $k => $v ) {
					$basketVarName			=	$cbFieldIds[$k];
					$userVarName			=	$v['name'];
					if ( isset( $user->$userVarName ) ) {
						$this->$basketVarName	=	$user->$userVarName;
					}
				}
			}
		}
		$firstLastName					=	$this->_splitName( $user );
		$this->first_name				=	$firstLastName[0];
		$this->last_name				=	$firstLastName[1];
		$this->payer_email				=	$user->email;
		$this->_computeTwoLettersCountryFromCountry();
	}

	/**
	 * Computes 2-letters country code from the $this->address_country and stores it into $this->address_country_code
	 */
	protected function _computeTwoLettersCountryFromCountry( ) {
		$countries						=	new cbpaidCountries();
		$twoLettersCountry				=	$countries->countryToTwoLetters( $this->address_country );
		if ( $twoLettersCountry ) {
			$this->address_country_code	=	$twoLettersCountry;
		}
	}

	/**
	 * Computes country from the $this->address_country_code 2-letters country code and stores it into $this->address_country
	 */
	protected function _computeCountryFromTwoLettersCountry( ) {
		$countries						=	new cbpaidCountries();
		$country						=	$countries->twoLettersToCountry( $this->address_country_code );
		if ( $country ) {
			$this->address_country		=	$country;
		}
	}
	/**
	 * Checks if invoicing address in basket is complete to meet mandatory requirement
	 *
	 * @return boolean
	 */
	public function checkAddressComplete( ) {
		$params					=	cbpaidApp::settingsParams();
		$invoicingAddressQuery	=	$params->get( 'invoicing_address_query' );
		$invoicing_fields		=	array(	'first_name'			=>	"First name",
			'last_name'				=>	"Last name",
			// not mandatory	'payer_business_name'	=>	"Company name",
			'address_street'		=>	"Address",
			'address_city'			=>	"City",
			'address_state'			=>	"State",
			'address_zip'			=>	"Zipcode",
			'address_country'		=>	"Country",
			// not mandatory	'contact_phone'			=>	"Phone"
			// not mandatory	'vat_number'			=>	"VAT Number"
		);
		foreach ( $invoicing_fields as $k => $v ) {
			if ( ( trim( $this->$k ) === '' ) && ! ( ( $k == 'address_state' ) && ! preg_match( '/^(United States|Canada|Australia)$/', $this->address_country ) ) )  {
				// State only mandatory/missing where asked
				if ( $invoicingAddressQuery == 2 ) {
					return sprintf( CBPTXT::T("The %s is required"), CBPTXT::T( $v ) );
				} else {
					return sprintf( CBPTXT::T("The %s is missing"), CBPTXT::T( $v ) );
				}
			}
		}
		return null;
	}
	/**
	 * Checks if invoicing address in basket is complete to meet mandatory requirement
	 *
	 * @return boolean
	 */
	public function storeInvoicingDefaultAddress( ) {
		$invoicing_fields			=	array(
			'first_name',
			'last_name',
			'payer_business_name',
			'address_street',
			'address_city',
			'address_state',
			'address_zip',
			'address_country',
			'contact_phone',
			'vat_number'
		);
		$comprofiler				=	new ComprofilerTable( $this->_db );
		$comprofiler->id			=	$this->user_id;
		$profile_prefix				=	'cb_subs_inv_';
		$anythingToStore			=	false;
		foreach ( $invoicing_fields as $k ) {
			if ( $this->$k != '' ) {
				$ak					=	$profile_prefix . $k;
				$comprofiler->$ak	=	$this->$k;
				$anythingToStore	=	true;
			}
		}
		if ( $anythingToStore ) {
			return $comprofiler->store();
		} else {
			return true;
		}

	}
	/**
	 * Sets the invoicing address off from the CB user profile.
	 * Specifically following fields:
	 *
	 * payer_business_name
	 * address_name
	 * first_name
	 * last_name
	 * address_street
	 * address_city
	 * address_state
	 * address_zip
	 * address_country
	 * address_country_code
	 * payer_email
	 * contact_phone
	 * vat_number
	 *
	 * @param  UserTable  $user
	 * @return void
	 */
	protected function _setInvoicingAddress( &$user ) {
		/** @var $user cbpaidUserWithSubsFields */
		if ( isset( $user->cb_subs_inv_last_name ) && isset( $user->cb_subs_inv_address_country ) && ( $user->cb_subs_inv_last_name || $user->cb_subs_inv_address_country ) ) {
			$invoicing_fields	=	array(
				'first_name',
				'last_name',
				'payer_business_name',
				'address_street',
				'address_city',
				'address_state',
				'address_zip',
				'address_country',
				'contact_phone',
				'vat_number'
			);
			$profile_prefix		=	'cb_subs_inv_';
			foreach ( $invoicing_fields as $k ) {
				$ak				=	$profile_prefix . $k;
				if ( $user->$ak ) {
					$this->$k	=	$user->$ak;
				}
			}
			$this->address_name	=	$user->cb_subs_inv_first_name
				.	( ( $user->cb_subs_inv_first_name && $user->cb_subs_inv_last_name ) ? ' ' : '' )
				.	$user->cb_subs_inv_last_name;
			$this->payer_email	=	$user->email;
			$this->_computeTwoLettersCountryFromCountry();
		} else {
			$this->_setInvoicingAddressFromUserProfile( $user );
		}
	}
	/**
	 * computes first and last names depending on CB configuration
	 *
	 * @param  UserTable  $user
	 * @return string[]          ( first_name, last_name )
	 */
	protected function _splitName( $user ) {
		global $ueConfig;

		switch ( $ueConfig['name_style'] ) {
			case 2:
				// firstname + lastname:
			case 3:
				// firstname + middlename + lastname:
				return array( $user->firstname, $user->lastname );
			default:
				// name only:
				$nameParts	=	explode( ' ', $user->name );
				$n			=	count( $nameParts );
				if ( $nameParts < 2 ) {
					return array( '', $user->name );
				} else {
					$nFirst	=	floor( $n / 2 );
					$first	=	array_slice( $nameParts, 0, $nFirst );
					$last	=	array_slice( $nameParts, $nFirst );
					return array( implode( ' ', $first ), implode( ' ', $last ) );
				}
		}
	}
	/**
	 * Get the most recent payment basket, checking for time-out, and deleting if timed out !
	 *
	 * if returned cbpaidPaymentBasket has ->id != null then it's existing !
	 * @static
	 *
	 * @param  int|null $userid             User Id
	 * @param  boolean  $generateNewBasket  Delete expired basket and Generate new basket systematically
	 * @param  boolean  $generateWarning    true: sets error message with warning "A payment invoice exists already: Please check below if it is correct. If not correct, click on the cancel link below, and select your choice again."
	 * @return cbpaidPaymentBasket          Payment basket
	 */
	public static function & getInstanceBasketOfUser( $userid, $generateNewBasket, $generateWarning = true ) {
		global $_CB_framework, $_CB_database;

		$paymentBasket						=	new cbpaidPaymentBasket( $_CB_database );
		if ( $paymentBasket->loadLatestBasketOfUserPlanSubscription( $userid ) ) {

			// auto-expire basket of more than 30 minutes:
			$cbpaidTimes					=&	cbpaidTimes::getInstance();
			$initiatedAt					=	$cbpaidTimes->strToTime( $paymentBasket->time_initiated );

			if ( $generateNewBasket || ( $initiatedAt < ( $_CB_framework->now() - 1800 ) ) ) {
				// auto-expire basket of more than 30 minutes:
				$paymentBasket->delete();
				$paymentBasket				=	new cbpaidPaymentBasket( $_CB_database );
			} else {
				// otherwise return existing basket
				if ( $generateWarning ) {
					cbpaidApp::getBaseClass()->_setErrorMSG( CBPTXT::T("A payment invoice exists already: Please check below if it is correct. If not correct, click on the cancel link below, and select your choice again.") );
				}
			}
		}
		return $paymentBasket;
	}
	/**
	 * Get the most recent payment basket, even if timed-out !
	 *
	 * @param  int      $userid
	 * @param  int      $planId           [optional]
	 * @param  int      $subscriptionId   [optional]
	 * @param  string   $paymentStatus    'NotInitiated': search only not initiated baskets which is not to old, NULL: search any kind, string: search for particular status.
	 * @param  int      $notBasketId      NULL or id of basket to NOT load
	 * @return boolean                    true = success, false = not found
	 */
	public function loadLatestBasketOfUserPlanSubscription( $userid, $planId = null, $subscriptionId = null, $paymentStatus = 'NotInitiated', $notBasketId = null ) {
		if ( ( $planId === null ) && ( $subscriptionId === null ) ) {
			$query	=	"SELECT b.*"
				. "\n FROM #__cbsubs_payment_baskets b"
				. "\n WHERE b.user_id = " . (int) $userid
				. ( $paymentStatus ?
					"\n AND b.payment_status = " . $this->_db->Quote( $paymentStatus )
					: '')
				. "\n ORDER BY b.time_initiated DESC"
			;
		} else {
			$query = "SELECT b.*"
				. "\n FROM #__cbsubs_payment_baskets b, #__cbsubs_payment_items i, #__cbsubs_subscriptions s"
				. "\n WHERE b.id = i.payment_basket_id "
				. "\n AND i.subscription_id = s.id "
				. "\n AND s.user_id = " . (int) $userid
				. ( $planId ?
					"\n AND s.plan_id = " . (int) $planId
					: '')
				. ( $subscriptionId ?
					"\n AND s.id = " . (int) $subscriptionId
					: '')
				. "\n AND b.user_id = s.user_id"
				. ( $paymentStatus ?
					"\n AND b.payment_status = " . $this->_db->Quote( $paymentStatus )
					: '')
				. ( $notBasketId ?
					"\n AND b.id <> " . (int) $notBasketId
					: '')
				. "\n ORDER BY b.time_initiated DESC"
			;
		}
		$this->_db->setQuery( $query, 0, 1 );
		return $this->_db->loadObject( $this );
	}
	/**
	 * creates a basket with items in database and completes this object with subscriptions
	 *
	 * @param  UserTable                       $user
	 * @param  cbpaidUsersubscriptionRecord[]  $subscriptions
	 * @param  string                          $prefixText
	 * @param  string                          $reason         payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  int                             $now            time of now (single now time for consistency in db)
	 * @return boolean                                         TRUE if something can be paid, FALSE if payment basket would be free (but then it's not created).
	 */
	public function createAndFillCreteSubscriptionsItems( &$user, &$subscriptions, $prefixText, $reason, $now ) {
		$result			=	$this->_tryCreateAndFillCreteSubscriptionsItems( $user, $subscriptions, $prefixText, $reason, $now, true );
		if ( ! $result ) {
			$result		=	$this->_tryCreateAndFillCreteSubscriptionsItems( $user, $subscriptions, $prefixText, $reason, $now, false );
		}
		return $result;
	}
	/**
	 * creates a basket with items in database and completes this object with subscriptions
	 *
	 * @param  UserTable                       $user
	 * @param  cbpaidUsersubscriptionRecord[]  $subscriptions
	 * @param  string                          $prefixText        Text to prefix before the item descriptions								//TBD this should be on a per-item basis
	 * @param  string                          $reason            payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  int                             $now               unix time of now (single now time for consistency in db)
	 * @param  boolean                         $tryAutorecurring  try to build the payment basket as autorecurring
	 * @return boolean                                            TRUE: could build basket, FALSE: could not.
	 */
	protected function _tryCreateAndFillCreteSubscriptionsItems( &$user, &$subscriptions, $prefixText, $reason, $now, $tryAutorecurring ) {
		global $_CB_framework;

		$params								=&	cbpaidApp::settingsParams();

		$plansTitle							=	$params->get( 'regTitle' );
		$prefixText							=	/* $sitename . ': ' . */ ($prefixText ? $prefixText.' ' : '') . $plansTitle . ': ';

		// preliminary round: Check currencies and owner
		$main_currency_code					=	trim( $params->get( 'currency_code' ) );			//TBD later: let users choose payment currency...depending of payment gateway and CC contracts
		$currency_code						=	'';
		$owner								=	null;
		foreach ( $subscriptions as $k =>	$sub ) {
			$item_currency					=	$subscriptions[$k]->getPlanAttribute( 'currency' );
			$item_owner						=	$subscriptions[$k]->getPlanAttribute( 'owner' );
			if ( $item_currency ) {
				if ( $currency_code ) {
					if ( $item_currency 	!= $currency_code ) {
						// another item to pay already has a different currency ==> use main site currency
						$currency_code		=	$main_currency_code;
					}
				} else {
					// item has a currency and no other item has set a currency
					$currency_code			=	$item_currency;
				}
			}
			if ( $item_owner ) {
				if ( ( $owner === null ) || ( $owner == $item_owner ) ) {
					$owner					=	$item_owner;
				} else {
					trigger_error( sprintf( 'Mixed items owners in basket: found id %d and %d: setting owner to be System.', $owner, $item_owner ), E_USER_WARNING );
					$owner					=	0;
				}
			}
		}
		if ( $currency_code == '' ) {
			$currency_code					=	$main_currency_code;
		}

		$totalPrice							=	0.0;
		$itnames							=	array();
		$itnums								=	array();

		$this->_paymentItems				=	array();
		$this->_paymentTotalizers			=	array();
		$paymentItemsOrdering				=	1;
		// prepare PaymentItems:
		foreach ( $subscriptions as $k => $sub ) {
			$artNumber						=	$sub->getArtNoSubId() . '-' . $user->id . '-' . $sub->plan_id;
			$quantity						=	1;
			$item							=	$subscriptions[$k]->createPaymentItem( $quantity, $currency_code, $artNumber, $prefixText, $reason, $now, $tryAutorecurring );
			if ( $item !== false ) {
				$item->setOrdering( $paymentItemsOrdering++ );
				if ( $sub->parent_subscription ) {
					$item->_parentSub		=	array( $sub->parent_plan, $sub->parent_subscription );
				}
				$item->callIntegrations( 'addSomethingToBasket', $sub, $this );
				$this->_paymentItems[$k]	=	$item;
				// compact summary texts for basket:
				$totalPrice					+=	$item->getPrice( false );
				$itnames[$k]				=	$sub->getPersonalized( 'name', false );
				$itnums[$k]					=	$item->artnum;
			}
		}

		if ( ( ! $tryAutorecurring ) && ( $totalPrice == 0 ) ) {
			// nothing to pay this time, so don't bother creating a 0.- invoice:
			return false;
		}

		// build item description line, default:	[ITEM_ALIAS]	and		[PREFIX_TEXT] [PLANS_TITLE]: [ITEM_NAME][VALIDITY_IF_NOT_AUTORECURRING] for [USERNAME]
		$prefixText					=	'';
		if ( $reason == 'R' ) {
			$prefixText				=	CBPTXT::T("Renew");
		} elseif ( $reason == 'U' ) {
			$prefixText				=	CBPTXT::T("Upgrade");
		}

		$extraStrings				=	array(	'ITEMS_ALIASES'			=>	implode( ', ', $itnames ),
			'SITENAME'				=>	$_CB_framework->getCfg( 'sitename' ),
			'SITEURL'				=>	$_CB_framework->getCfg( 'live_site' ),
			'PLANS_TITLE'			=>	strip_tags( $params->get( 'regTitle' ) ),
			'EMAILADDRESS'			=>	$user->email,
			'PREFIX_TEXT'			=>	$prefixText
		);
		$item_name					=	trim( cbReplaceVars( CBPTXT::T( $params->get( 'basket_item_name', '[ITEMS_ALIASES] for [USERNAME]' ) ), $user, false, false, $extraStrings, false ) );

		// create paymentBasket:
		$item_number						=	implode( ',', $itnums );
		$this->createPaymentBasket( $user, $totalPrice, $currency_code, 1, $item_number, $item_name, false, $now, $owner, $reason );

		return $this->_storeBasketItemsTotalizers( $tryAutorecurring, $now, $user, $reason );
	}
	/**
	 * Changes the currency of a payment basket
	 * and stores the updated basket, items and totalizers.
	 *
	 * @param  string   $newCurrency   Warning: Must be sanitized before
	 * @param  boolean  $updateBasket  Update basket and totalizer calling $this->updateBasketRecomputeTotalizers()
	 * @return void
	 */
	public function changeCurrency( $newCurrency, $updateBasket = true ) {
		// Are we really changing ?
		if ( $this->mc_currency != $newCurrency ) {

			// Is basket still just a not-committed order ?
			if ( $this->payment_status == 'NotInitiated' ) {

				// Check if currency conversion rate is defined:
				$_CBPAY_CURRENCIES	=&	cbpaidApp::getCurrenciesConverter();
				$rate				=	$_CBPAY_CURRENCIES->convertCurrency( $this->mc_currency, $newCurrency, $this->mc_gross );		// null if cannot convert
				if ( $rate !== null ) {

					// We could convert, so it's safe to re-compute the basket:
					foreach ( $this->loadPaymentItems() as $item ) {
						// Update each item in basket from its subscription so that any plan-depending settings are taken in account:
						// $item = NEW cbpaidPaymentItem();
						$subscription		=	$item->loadSubscription();
						if ( $subscription ) {
							// $subscription = NEW cbpaidSomething();
							$subscription->updatePaymentItem( $item, $this, null, $newCurrency );
						}
					}

					$this->checkPaymentMethodValidForCurrency( $newCurrency );

					$this->mc_currency		=	$newCurrency;
					if ( $updateBasket ) {
						$this->updateBasketRecomputeTotalizers();
					}
				}
			}
		}
	}
	/**
	 * Re-computes basket sums from items.
	 * Tries storing payment basket, items (same if existing) and totalizers (re-created).
	 * @access private
	 * (but function is public as temporarily used by options plugin)
	 *
	 * @param  boolean    $tryAutorecurring
	 * @param  int        $now
	 * @param  UserTable  $user
	 * @param  string     $reason            Payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @return boolean                       if $tryAutorecurring == true : TRUE: is autorecurring, FALSE: is not autorecurring. Please retry with $tryAutorecurring = false.
	 */
	public function _storeBasketItemsTotalizers( $tryAutorecurring, $now, $user = null, $reason = null ) {
		global $_PLUGINS;

		$totalPrice							=	0.0;
		foreach ( $this->_paymentItems as $item ) {
			$totalPrice						+=	$item->getPrice( false );
		}
		$this->mc_gross						=	$totalPrice;
		$this->tax							=	null;

		if ( $tryAutorecurring ) {
			$couldDoAutoRecurring			=	$this->setAmountsPeriods( $now );
			if ( ! $couldDoAutoRecurring ) {
				return false;
			}
		}

		if ( $user && $reason ) {
			// Set invoice number (only first time):
			$this->setProformaInvoiceNumber($user, $reason );
		}

		$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');
		$_PLUGINS->trigger( 'onCPayBeforeComputeTotalizersPaymentBasketUpdated', array( $this, $this->_paymentItems ) );

		$this->_createAllTotalizers();
		$this->_computeTotalizers();

		$_PLUGINS->trigger( 'onCPayBeforeStorePaymentBasketUpdated', array( $this, $this->_paymentItems, $this->_paymentTotalizers ) );

		$this->historySetMessage( 'Store payment basket' );
		$this->store( true );

		// add new paymentBasket id to items and store them into database:
		foreach ( $this->_paymentItems as $k => $v ) {
			$this->_paymentItems[$k]->setPaymentBasket( $this->id );
			$this->_paymentItems[$k]->store( true );
		}

		// finally compute items hierarchy and updates database:
		foreach ( $this->_paymentItems as $k => $v ) {
			if ( isset( $v->_parentSub ) ) {
				$parentItem					=	null;
				foreach ( $this->_paymentItems as $vv ) {
					if ( ( $vv->plan_id == $v->_parentSub[0] ) && ( $vv->subscription_id == $v->_parentSub[1] ) ) {
						$parentItem			=	$vv->id;
						break;
					}
				}

				// here we could search for parent item in database from another invoice, but we don't want that feature for now...
				if ( $parentItem ) {
					/** @var $parentItem int */
					$this->_paymentItems[$k]->setParentItem( $parentItem );
					$this->_paymentItems[$k]->store( true );
				}
			}
		}

		$this->_fixArrayIndices( $this->_paymentItems );
		// add new paymentBasket id to totalizers and store them into database:
		foreach ( $this->_paymentTotalizers as $k => $v ) {
			$this->_paymentTotalizers[$k]->setPaymentBasket( $this->id );
			$this->_paymentTotalizers[$k]->setPaymentItem();
			$this->_paymentTotalizers[$k]->store( true );
		}

		$this->_fixArrayIndices( $this->_paymentTotalizers );

		$_PLUGINS->trigger( 'onCPayAfterPaymentBasketUpdated', array( $this, $this->_paymentItems, $this->_paymentTotalizers ) );

		return true;
	}
	/**
	 * remaps the $array's indices to match $array's objects' table keys.
	 *
	 * @param  $array  Array INPUT + OUTPUT
	 * @return void
	 */
	private function _fixArrayIndices( &$array ) {
		$remapping								=	array();
		foreach ( $array as $o ) {
			if ( $o instanceof TableInterface ) {
				$oKey	=	$o->getKeyName();
			} else {
				$oKey	=	$o->_tbl_key;
			}
			$remapping[$o->{$oKey}]	=	$o;
		}
		$array								=	$remapping;
	}
	/**
	 * Create all totalizers
	 */
	protected function _createAllTotalizers( ) {
		// Reset tax in each item:
		foreach ( $this->_paymentItems as $k => $item ) {
			$this->_paymentItems[$k]->tax_amount			=	null;
			$this->_paymentItems[$k]->first_tax_amount		=	null;
			$this->_paymentItems[$k]->discount_amount		=	null;
			$this->_paymentItems[$k]->first_discount_amount	=	null;
		}

		// First try adding totalizers corresponding to the entries ordering rules in configuration:
		$params						=&	cbpaidApp::settingsParams();
		$baskettotalizerordering	=	$params->get( 'baskettotalizerordering', array() );
		unset( $baskettotalizerordering[0] );		// in case an entry is not used with order 0
		if ( count( $baskettotalizerordering ) > 0 ) {
			// reorder in ordering way:
			asort( $baskettotalizerordering, SORT_NUMERIC );
			foreach ( $baskettotalizerordering as $paymentTotalizerName => $ordering ) {
				if ( $ordering != 0 ) {
					$totalizerClassName				=	'cbpaidPaymentTotalizer_' . $paymentTotalizerName;
					$createTotalizerEntriesFunc		=	array( $totalizerClassName, 'createTotalizerEntries' );
					if ( is_callable( $createTotalizerEntriesFunc ) ) {
						// calls static method cbpaidPaymentTotalizer_TOTALIZERNAME::createTotalizerEntries( $paymentBasket, $paymentItems, $paymentTotalizerName, $addTotalizerToBasketFunc ) :
						call_user_func_array( $createTotalizerEntriesFunc, array( $this, $this->_paymentItems, $this->_paymentTotalizers, $paymentTotalizerName, array( $this, 'addTotalizerToBasket' ) ) );
					}
				}
			}
			// Now treat the special case where we only have sub-totals and grand total:
			$otherThanTotals						=	false;
			$hasGrandtotal						=	false;
			foreach ($this->_paymentTotalizers as $totalizerEntry ) {
				if ( ! ( ( $totalizerEntry->totalizer_type == 'grandtotal' ) || ( substr( $totalizerEntry->totalizer_type, 0, 8 ) == 'subtotal' ) ) ) {
					$otherThanTotals				=	true;
				}
				if ( $totalizerEntry->totalizer_type == 'grandtotal' ) {
					$hasGrandtotal					=	true;
				}
			}
			if ( ( ! $otherThanTotals ) && $hasGrandtotal && ( count( $this->_paymentTotalizers ) > 1 ) ) {
				foreach ( $this->_paymentTotalizers as $k => $totalizerEntry ) {
					if ( substr( $totalizerEntry->totalizer_type, 0, 8 ) == 'subtotal' ) {
						unset( $this->_paymentTotalizers[$k] );
					}
				}
			}
		}
	}
	/**
	 * Computes all totalizers created:
	 */
	protected function _computeTotalizers( ) {
		// Then compute basket correspondingly:
		foreach ( $this->_paymentTotalizers as $k => $totalizerEntry ) {
			// totalizers can be unset during computeTotalizers, so we need to check existence:
			if ( isset( $this->_paymentTotalizers[$k] ) ) {
				$this->_paymentTotalizers[$k]->computeTotalizer( $this, $this->_paymentItems, $this->_paymentTotalizers );
			}
		}
	}
	/**
	 * registers a totalizer to the basket
	 * @param  cbpaidPaymentTotalizer   $totalizerEntry
	 */
	public function addTotalizerToBasket( $totalizerEntry ) {
		$totalizersOrdering					=	1 + count( $this->_paymentTotalizers );
		$totalizerEntry->setOrdering( $totalizersOrdering );
		$totalizerEntry->setPaymentBasketObject( $this );
		//FIXME: set totalizer_type too
		$this->_paymentTotalizers[]			=	$totalizerEntry;
	}
	/**
	 * Used internally and in promotion plugin
	 *
	 * @return void
	 */
	public function updateBasketRecomputeTotalizers( ) {
		$this->loadPaymentItems();
		$tryAutorecurring			=	$this->isAnyAutoRecurring();

		if ( $this->payment_status == 'NotInitiated' ) {
			global $_CB_framework;
			$timeOfBasket			=	$_CB_framework->now();
			$this->time_initiated	=	date( 'Y-m-d H:i:s', $timeOfBasket );
		} else {
			$timeOfBasket			=	cbpaidTimes::getInstance()->strToTime( $this->time_initiated );
		}

		$this->deleteTotalizers();
		$this->_storeBasketItemsTotalizers( $tryAutorecurring, $timeOfBasket );
	}
	/**
	 * Schedules events for autorecurring payments
	 *
	 * @param  boolean  $storeObject  calls $this->store()
	 * @return boolean                TRUE: scheduling was needed, FALSE: no scheduling needed
	 */
	public function scheduleAutoRecurringPayments( $storeObject = true ) {
		return cbpaidScheduler::getInstance( $this )->schedule( $storeObject );
	}
	/**
	 * Schedules events for autorecurring payments
	 *
	 * @param  boolean  $storeObject  calls $this->store()
	 */
	public function unscheduleAutoRecurringPayments( $storeObject = true ) {
		cbpaidScheduler::getInstance( $this )->unschedule( $storeObject );
	}
	/**
	 * Triggers autorecurring payments scheduled in cbpaidScheduler
	 *
	 * @return null|string  NULL: no tasks done. String: result or error to forward to admins.
	 */
	public function triggerScheduledAutoRecurringPayment( ) {
		$schedule					=	cbpaidScheduler::getInstance( $this );
		$newAttempt					=	false;
		if ( $schedule->attemptScheduledTask() ) {
			if ( $this->gateway_account ) {
				$payAccount			=	cbpaidControllerPaychoices::getInstance()->getPayAccount( $this->gateway_account ) ;
				if ( $payAccount ) {
					$payClass		=	$payAccount->getPayMean();
					if ( $payClass ) {
						$return		=	null;
						$transientErrorDoReschedule	=	false;
						$success	=	$payClass->processAutoRecurringPayment( $this, $return, $transientErrorDoReschedule );
						if ( $success === true ) {
							$another =	$schedule->attemptScheduledTaskSuccessful();
						} elseif ( $success === false) {
							$another =	$schedule->attemptScheduledTaskFailed( $transientErrorDoReschedule );
						} elseif ( $success === null ) {
							$another =	false;
						} else {
							$another = false;
							trigger_error( sprintf( "Unexpected return value from processAutoRecurringPayment on %s", $payClass->getPayName() ), E_USER_WARNING );
						}
						if ( $another ) {
							$return	.=	' ' . sprintf( CBPTXT::T("The next payment reattempt is scheduled on %s"), $this->scheduler_next_maturity );
						}
					} else {
						$newAttempt	=	$schedule->attemptScheduledTaskFailed( true );
						$return		=	sprintf( CBPTXT::T("Auto-Recurring payment of Basket %s could not be done, because the payment gateway account %s (id %s) class does not have method processAutoRecurringPayment."), $this->id, $this->payment_method, $this->gateway_account );
					}
				} else {
					$newAttempt		=	$schedule->attemptScheduledTaskFailed( true );
					$return			=	sprintf( CBPTXT::T("Auto-Recurring payment of Basket %s could not be done, because the payment gateway account %s (id %s) is not existent and active."), $this->id, $this->payment_method, $this->gateway_account );
				}
			} else {
				$newAttempt			=	$schedule->attemptScheduledTaskFailed( true );
				$return				=	sprintf( CBPTXT::T("Auto-Recurring payment of Basket %s could not be done, because the basket has no payment gateway account set (method: %s)."), $this->id, $this->payment_method );
			}
		} else {
			$return					=	CBPTXT::P("Error in last scheduled taks for this Basket [BASKET_ID] or error in payment scheduling table: Unexpected state (e.g. already executing) or cannot store. This state will be cleared automatically after 3 cron jobs so that a retry can automatically occur.", array( '[BASKET_ID]' => $this->id ) );
		}
		if ( $newAttempt ) {
			$return					.=	' ' . CBPTXT::T("A new auto-recurring payment attempt has been scheduled.");
		}
		return $return;
	}
	/**
	 * Stops auto-recurring payments for a given subscription
	 *
	 * @param  cbpaidPaymentItem[]  $paymentItems
	 * @return string|boolean                      true if unsubscription done successfully, string if error
	 */
	public function stopAutoRecurringPayments( &$paymentItems ) {
		if ( $this->recurring ) {
			$result				=	false;
			$baseClass			=	cbpaidApp::getBaseClass();
			$payAccount			=	cbpaidControllerPaychoices::getInstance()->getPayAccount( $this->gateway_account );
			if ( $payAccount ) {
				$payClass		=	$payAccount->getPayMean( $this->payment_method );
			} else {
				$payClass		=	null;
			}
			if ( $payClass ) {
				$result			=	$payClass->stopPaymentSubscription( $this, $paymentItems );
			} else {
				$baseClass->_setErrorMSG('StopAutoRecurringPayments: Payment method ' . $this->payment_method . ' and/or gateway account id ' . $this->gateway_account . ' not found');
			}
		} else {
			$result		=	null;
		}
		return $result;
	}
	/**
	 * Computes amount and period of payments within the basket and updates basket with these
	 *
	 * @param  int           $now
	 * @return boolean       TRUE: all duration periods match, could set, FALSE: no match found, couldn't set.
	 */
	public function setAmountsPeriods( $now ) {
		$this->loadPaymentItems();
		$ret	=	false;
		if ( $this->_paymentItems && ( count( $this->_paymentItems ) > 0 ) ) {
			$ret	=	true;

			/*
			// first check each corresponding subscription:			//TBD: not even sure if that part is really needed !!! :D
			$subscriptionValidity				=	null;
			$subscriptionExpiry					=	null;
			$subscriptionExpiryIfActivatedNow	=	null;
			foreach ( $this->_paymentItems as $paymentItem ) {
				$sub	=	$paymentItem->loadSubscription();
				if ( $sub ) {
					$subExpiryIfActivatedNow	=	$sub->computeExpiryTimeIfActivatedNow( $now, $paymentItem->reason );
					$subExpiry	 				=	$sub->getExpiryDate( $now );
					$plan		 				=	$sub->getPlan();
					$subValidity				=	$plan->get( 'validity' );
					if ( $subscriptionValidity === null ) {
						$subscriptionValidity	=	$subValidity;
					} elseif ( $subValidity && ( $subscriptionValidity != $subValidity ) ) {
						trigger_error('trying to subscribe ARB to subscriptions of different durations: ' . $subscriptionValidity . ' <> ' . $subValidity, E_USER_NOTICE );
						return false;
					}
					if ( $subscriptionExpiry === null ) {
						$subscriptionExpiry		=	$subExpiry;
					} elseif ( $subExpiry && ( $subscriptionExpiry != $subExpiry ) ) {
						trigger_error('trying to subscribe ARB to subscriptions expiring at different dates: ' . $subscriptionExpiry . ' <> ' . $subExpiry, E_USER_NOTICE );
						return false;
					}
					if ( $subscriptionExpiryIfActivatedNow === null ) {
						$subscriptionExpiryIfActivatedNow	=	$subExpiryIfActivatedNow;
					} elseif ( $subExpiryIfActivatedNow && ( $subExpiryIfActivatedNow != $subscriptionExpiryIfActivatedNow ) ) {
						trigger_error('trying to subscribe ARB to subscriptions which would now be expiring at different dates: ' . $subExpiryIfActivatedNow . ' <> ' . $subExpiryIfActivatedNow, E_USER_NOTICE );
						return false;
					}
				}
			}
			*/

			// then check the payment items:
			$checkArray	=	array(	'validity'				=>	null,
				// 'bonustime'			=>	null,
				// 'expiry_date'		=>	null,
				'autorecurring'			=>	null,
				'recurring_max_times'	=>	null,
				'first_validity'		=>	null );
			$subscriptionExpiry					=	null;
			$subscriptionExpiryIfActivatedNow	=	null;
			$validityPeriodTime					=	null;
			$total_recurring_rate				=	0.0;
			$total_rate_now						=	0.0;
			$autorecurringSubPaymentItemK		=	null;
			foreach ( $this->_paymentItems as $paymentK => $paymentItem ) {
				if ( $paymentItem->autorecurring ) {
					foreach ( array_keys( $checkArray ) as $k ) {
						if ( $checkArray[$k] === null ) {
							$checkArray[$k]		=	$paymentItem->$k;
						} elseif ( $paymentItem->$k && ( $checkArray[$k] != $paymentItem->$k )  && ( ( $k != 'autorecurring' ) || ( $checkArray[$k] > 0 ) != ( $paymentItem->$k > 0 ) ) ) {
							trigger_error('trying to subscribe ARB payment items different on key ' . $k . ' with different values ' . $checkArray[$k] . ' <> ' .$paymentItem->$k, E_USER_NOTICE );
							return false;
						}
					}
					/** @var $sub cbpaidUsersubscriptionRecord */ /* (as it is auto-recurring here) */
					$sub	=	$paymentItem->loadSubscription();
					if ( $sub ) {
						$subExpiryIfActivatedNow	=	$sub->computeExpiryTimeIfActivatedNow( $now, $paymentItem->reason );
						$subExpiry	 				=	$sub->getExpiryDate( $now );
						$subValidityPeriodTime		=	$paymentItem->getFullPeriodValidityTime( $subExpiryIfActivatedNow, 'validity' );

						if ( $subscriptionExpiry === null ) {
							$subscriptionExpiry		=	$subExpiry;
						} elseif ( $subExpiry && ( $subscriptionExpiry != $subExpiry ) ) {
							trigger_error('trying to subscribe ARB to subscriptions expiring at different dates: ' . $subscriptionExpiry . ' <> ' . $subExpiry, E_USER_NOTICE );
							return false;
						}
						if ( $subscriptionExpiryIfActivatedNow === null ) {
							$subscriptionExpiryIfActivatedNow	=	$subExpiryIfActivatedNow;
						} elseif ( $subExpiryIfActivatedNow && ( $subExpiryIfActivatedNow != $subscriptionExpiryIfActivatedNow ) ) {
							trigger_error('trying to subscribe ARB to subscriptions which would now be expiring in first term at different dates: ' . $subExpiryIfActivatedNow . ' <> ' . $subExpiryIfActivatedNow, E_USER_NOTICE );
							return false;
						}
						if ( $validityPeriodTime === null ) {
							$validityPeriodTime			=	$subValidityPeriodTime;
						} elseif ( $subValidityPeriodTime && ( $subValidityPeriodTime != $validityPeriodTime ) ) {
							trigger_error('trying to subscribe ARB to subscriptions which would now be expiring in subsequent term at different dates: ' . $subExpiryIfActivatedNow . ' <> ' . $subExpiryIfActivatedNow, E_USER_NOTICE );
							return false;
						}
					}
					$autorecurringSubPaymentItemK	=	$paymentK;

					$total_recurring_rate			+=	$paymentItem->rate;
				}
				$total_rate_now						+=	( $paymentItem->first_rate === null ? $paymentItem->rate : $paymentItem->first_rate );
			}
			if ( $autorecurringSubPaymentItemK === null ) {
				return false;		// no autorecurring items
			}
			if ( $this->mc_gross != $total_rate_now ) {
				trigger_error('trying to subscribe ARB to subscriptions which mc_gross and total_rate_now totals mismatch: ' . $this->mc_gross . ' <> ' . $total_rate_now . ' Probably due to the initial duration not set for a plan with a different initial price/duration.', E_USER_WARNING );
				return false;
			}

			// ok all payment items which are subscriptions are in full agreement: populate a1,t1,p1...3 fields:
			$this->recur_times		=	$checkArray['recurring_max_times'];
			if ( $checkArray['first_validity'] ) {
				$this->mc_amount1	=	$this->mc_gross;
				$this->mc_amount3	=	$total_recurring_rate;
				$this->period1		=	$this->timePeriodToPeriod( $now, $subscriptionExpiryIfActivatedNow - $now, $checkArray['first_validity'] );
				$this->period3		=	$this->timePeriodToPeriod( $subscriptionExpiryIfActivatedNow, $validityPeriodTime, $checkArray['validity'] );
			} else {
				$firstPeriod		=	$this->timePeriodToPeriod( $now, $subscriptionExpiryIfActivatedNow - $now, $checkArray['validity'] );
				$secondPeriod		=	$this->validityToYmwdPeriod( $this->_paymentItems[$autorecurringSubPaymentItemK], 'validity' );
				if ( ( $firstPeriod == $secondPeriod ) && ( $total_rate_now == $total_recurring_rate ) ) {
					$this->mc_amount3	=	$this->mc_gross;
					$this->period3		=	$firstPeriod;
					$this->mc_amount1	=	null;
					$this->period1		=	'';
				} else {
					$this->mc_amount1	=	$this->mc_gross;
					$this->mc_amount3	=	$total_recurring_rate;
					$this->period1		=	$firstPeriod;
					$this->period3		=	$secondPeriod;
					if ( $this->recur_times ) {
						--$this->recur_times;
					}
				}
			}
		}
		return $ret;
	}
	/**
	 * Converts a unix period of time between $now and $validityPeriodTime (in row as hint as $validity ) into
	 * the most appropriate unit period:
	 * Days, Weeks, Months or Years in 1-9000 D, 1-52000 W, 1-24000 M, 1-5000 Y
	 *
	 * @param  int      $now                   unix time right now
	 * @param  int      $validityPeriodTime    period of unix time for the validity
	 * @param  string   $validity              raw validity definition string
	 * @return string                          XX D, XX W, XX M, X Y as appropriate
	 */
	public function timePeriodToPeriod( $now, $validityPeriodTime, $validity ) {
		global $_CB_framework;

//		$ret	=	null;	leave commented to flag any errors !

		if ( $validityPeriodTime == 0 ) {
			return '0 D';
		}
		if ( ! ( cbStartOfStringMatch( $validity, '0000-00' ) || cbStartOfStringMatch( $validity, 'U:0000-00' ) ) ) {
			// Base Period expressed in months and years:
			$offset		=	(int) $_CB_framework->getCfg( 'offset' ) * 3600;
			$startYmd	=	explode( ' ', date( 'Y m d', $now + $offset ) );
			$matches	=	null;
			if ( preg_match( "/^U:(\\d+)-(\\d+)-(\\d+) 00:00:00\$/", $validity, $matches ) && self::_isLastDayOfMonth( $startYmd )
				|| preg_match( "/^(\\d+)-(\\d+)-(\\d+) 00:00:00\$/", $validity, $matches ) ) {
				if ( $matches[3] == 0 ) {
					if ( $matches[2] == 0 ) {
						// years only:
						$ret	=	( (int) $matches[1] ) . ' Y';
					} else {
						// maybe years but months for sure:
						$ret	=	( (int) ( ( 12 * $matches[1] ) + $matches[2] ) ) . ' M';
					}
					return $ret;
				} elseif ( ( $matches[2] == 0 ) && ( $matches[1] == 0 ) ) {
					// days only:
					if ( ( $matches[3] % 7 ) == 0 ) {
						// weeks:
						$ret	=	( ( (int) $matches[3] ) / 7 ) . ' W';
					} else {
						$ret		=	( (int) $matches[3] ) . ' D';
					}
					return $ret;
				}	// else it's a wierd combination, best handled by the day and week as below.
			}
			/*
						$offset		=	$_CB_framework->getCfg( 'offset' ) * 3600;
						$now		+=	$offset;
						$expiryYmd	=	explode( ' ', date( 'Y m d', $now + $validityPeriodTime ) );
						$y			=	$expiryYmd[0] - $startYmd[0];
						$m			=	$expiryYmd[1] - $startYmd[1];
						$d			=	$expiryYmd[2] - $startYmd[2];
						// ECHO "y: $y m: $m d: $d  start: ".$startYmd[0].'-'.$startYmd[1].'-'.$startYmd[2].' expire: '.$expiryYmd[0].'-'.$expiryYmd[1].'-'.$expiryYmd[2].'<br/>';
						if ( $d == 0 ) {
							if ( ( $m < 0 ) && ( $y > 0 ) ) {
								$m	+=	12;
								$y--;
							}
							if ( $y && $m ) {
								trigger_error("trying to subscribe ARB to subscriptions of mixed months ($m) and years ($y) duration.", E_USER_ERROR );
							}
							if ( $y > 0 ) {
								$ret	=	$y . ' Y';
							} else {
								$ret	=	$m . ' M';
							}
							return $ret;
						}
			*/
		}
		// no years or months in validity period (or a period incompatible with months and years only):
		$ht	=	$validityPeriodTime / 3600;
		if ( $ht < 24 ) {
			$ret	=	'1 D';
		} else {
			$dt	=	$ht / 24;
			$wt	=	$dt / 7;
			if ( $dt && ( $wt == 0 ) ) {
				$ret	=	( (int) $dt ) . ' D';
			} else {
				$h	=	$ht % 24;
				$d	=	$dt % 7;
				if ( $wt && ( $d == 0 ) && ( $h == 0 ) ) {
					$ret	=	( (int) $wt ) . ' W';
				} else {
					$ret	=	( (int) $dt ) . ' D';
				}
			}
		}
		return $ret;
	}
	/**
	 * Checks if day is last day of month
	 * @param  array  $ymdArray ( y, m, d )
	 * @return boolean
	 */
	private static function _isLastDayOfMonth( $ymdArray ) {
		return ( $ymdArray[2] == date( 'd', mktime( 23,59,59, $ymdArray[1]+1, 0, $ymdArray[0] ) ) );
	}
	/**
	 * Converts the validity of a timed item into the most appropriate unit period:
	 * Days, Weeks, Months or Years in 1-90 D, 1-52 W, 1-24 M, 1-5 Y
	 *
	 * @param  cbpaidTimed  $cbpaidTimed   object for validity
	 * @param  string       $varName       name of variable ( 'validity' or 'first_validity')
	 * @return string                      XX D, XX W, XX M, X Y as appropriate
	 */
	private function validityToYmwdPeriod( &$cbpaidTimed, $varName ) {
		list($y, $c, $d, $h, $m, $s) = $cbpaidTimed->getValidity( $varName );
		if ( ( $s == 0 ) && ( $m == 0 ) && ( ( $h % 24 ) == 0 ) ) {
			$d	= $d + ( (int) ( $h / 24 ) );
			if ( ( $d && $c ) || ( $c && $y ) || ( $y && $d ) ) {
				trigger_error('trying to subscribe ARB to subscriptions of mixed days and months duration.', E_USER_WARNING );
			}
		} else {
			trigger_error('trying to subscribe ARB to subscriptions of non-day-multiple duration.', E_USER_WARNING );
		}
		if ( $d && ( ( $d % 7 ) == 0 ) ) {
			$w	=	$d / 7;
			$d	=	0;
		} else {
			$w	=	0;
		}
		if ( $d ) {
			$ymwd = $d . ' D';
		} elseif ( $w ) {
			$ymwd = $w . ' W';
		} elseif ( $c ) {
			$ymwd = $c . ' M';
		} elseif ( $y ) {
			$ymwd = $y . ' Y';
		} else {
			$ymwd = '0 D';
		}
		return $ymwd;
	}
	/**
	 * Converts a Days, Weeks, Months or Years period into Unix-time
	 *
	 * @param  string  $ymwd   Days, Weeks, Months or Years in 1-90 D, 1-52 W, 1-24 M, 1-5 Y
	 * @return int             Unix time
	 */
	private function ymwdPeriodToTimePeriod( $ymwd ) {
//		$period		=	null;	leave commented to flag any errors !
		$pta		=	array();

		if ( $ymwd ) {
			$pta		=	explode( ' ', $ymwd );
		}
		if ( isset( $pta[1] ) ) {
			switch ( $pta[1] ) {
				case 'Y':
					$period	=	sprintf('%04d-00-00 00:00:00',   $pta[0] );
					break;
				case 'M':
					$period	=	sprintf('0000-%02d-00 00:00:00', $pta[0] );
					break;
				case 'W':
					$pta[0]	=	7 * $pta[0];
					$period	=	sprintf('0000-00-%02d 00:00:00', $pta[0] );
					break;
				case 'D':
					$period	=	sprintf('0000-00-%02d 00:00:00', $pta[0] );
					break;
				default:
					trigger_error( sprintf( '%s::%s: Unsupported period: %s.', __CLASS__, __FUNCTION__, $ymwd ) );
					$period	=	null;
					break;
			}
		} else {
			trigger_error( sprintf( '%s::%s: Unsupported period: %s.', __CLASS__, __FUNCTION__, $ymwd ) );
			$period	=	null;
		}
		return $period;
	}
	/**
	 * Checks if any autorecuring is possible with this basket
	 * @return boolean
	 */
	public function isAnyAutoRecurringPossibleWithThisBasket( ) {
		return ( $this->period3 && $this->mc_amount3 );
	}
	/**
	 * Checks if any payment items of this payment basket is an autorecurring items and returns highest setting: 0: no, 1: imposed by system, 2: chosen by user
	 *
	 * @return int  autorecurring: 0: no, 1: imposed by system or basket, 2: chosen by user
	 */
	public function isAnyAutoRecurring( ) {
		$highestAutoRecurring					=	0;
		if ( $this->isAnyAutoRecurringPossibleWithThisBasket() ) {
			$this->loadPaymentItems();
			if ( $this->_paymentItems ) {
				foreach ( $this->_paymentItems as $paymentItem ) {
					if ( $paymentItem->autorecurring == 1 ) {													// must autorecure: enforce
						$highestAutoRecurring	=	1;
					} elseif ( ( $paymentItem->autorecurring == 2 ) && ( $highestAutoRecurring != 1 ) ) {		// user's choice: if no enforcement before, allow choice
						$highestAutoRecurring	=	2;
					}
				}
			}
		}
		return $highestAutoRecurring;
	}
	/**
	 * loads and returns the cbpaidPaymentItem's of this cbPaymentBasket
	 * they are cached into $this->_paymentItems as array of cbpaidPaymentItem
	 *
	 * @return cbpaidPaymentItem[]
	 */
	public function & loadPaymentItems() {
		global $_CB_database, $_PLUGINS;

		if ( $this->_paymentItems === null ) {
			$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
			$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');

			$sampleItem				=	new cbpaidPaymentItem( $_CB_database );
			$this->_paymentItems	=	$sampleItem->loadThisMatchingList( array( 'payment_basket_id' => (int) $this->id ), array( 'ordering' => 'ASC' ) );
			// foreach ( $this->_paymentItems as $item ) {
			//	$sub->loadPlan();								// not yet needed, so don't do it yet.
			// }
		}
		return $this->_paymentItems;
	}
	/**
	 * Returns a payment item from the basket
	 *
	 * @param  int                     $item_id  Payment Item id
	 * @return cbpaidPaymentItem|null
	 */
	public function getPaymentItem( $item_id ) {
		if ( isset( $this->_paymentItems[$item_id] ) ) {
			return $this->_paymentItems[$item_id];
		}
		return null;
	}
	/**
	 * Adds a payment item to the basket in memory
	 * @param  cbpaidPaymentItem  $paymentItem
	 * @return void
	 */
	public function addPaymentItem( $paymentItem ) {
		$this->_paymentItems[]	=	$paymentItem;
	}
	/**
	 * Removes a payment item from the basket in memory
	 *
	 * @param  int                     $item_id  Payment Item id
	 * @return cbpaidPaymentItem|null
	 */
	public function removePaymentItem( $item_id ) {
		unset( $this->_paymentItems[$item_id] );
	}
	/**
	 * loads and returns the cbpaidPaymentItem's of this cbPaymentBasket
	 * they are cached into $this->_paymentItems as array of cbpaidPaymentItem
	 *
	 * @return cbpaidPaymentTotalizer[]
	 */
	public function & loadPaymentTotalizers() {
		global $_CB_database;

		if ( $this->_paymentTotalizers === null ) {
			$sampleItem					=	new cbpaidPaymentTotalizer( $_CB_database );
			$this->_paymentTotalizers	=	$sampleItem->loadThisMatchingList( array( 'payment_basket_id' => (int) $this->id ), array( 'ordering' => 'ASC' ) );

			// Now also link back to basket, as it is needed to be able to display totalizers correctly:
			foreach ( array_keys( $this->_paymentTotalizers ) as $k ) {
				$this->_paymentTotalizers[$k]->setPaymentBasketObject( $this );
			}
		}
		return $this->_paymentTotalizers;
	}
	/**
	 * Gets the validity of the latest payment item of basket		//TBD: check if all are same ?
	 *
	 * @return string   private (U:)datetime of validity of the payment item
	 */
	public function get_period( ) {
		$this->loadPaymentItems();
		$period			=	null;
		foreach ( $this->_paymentItems as $item ) {
			$period		=	$item->validity;
			// $occurrences = $item->recurring_max_times;
		}
		return $period;
	}
	/**
	 * loads the cbSubscriptions of this cbPaymentBasket into $this->_subscriptions as array of cbSubscription + ->reason from payment_items (N=New, R=Renewal)
	 *
	 * @return cbpaidSomething[]
	 */
	private function & _loadSubscriptions() {
		$subscriptions					=	array();
		$this->loadPaymentItems();

		if ( ! is_array( $this->_paymentItems ) ) {
			return $subscriptions;
		}

		foreach ( array_keys( $this->_paymentItems ) as $k ) {
			/** @noinspection PhpUndefinedMethodInspection  DUE to bug in IDE */
			$sub					=&	$this->_paymentItems[$k]->loadSubscription();
			if ( $sub != null ) {
				/** @noinspection PhpUndefinedMethodInspection  DUE to bug in IDE */
				$subscriptions[]	=&	$this->_paymentItems[$k]->loadSubscription();
			}
			unset( $sub );
		}

		return $subscriptions;
	}

	/**
	 * gets the cbSubscriptions of this cbPaymentBasket
	 *
	 * @return cbpaidSomething[]    of cbSubscription + ->reason from payment_items (N=New, R=Renewal)
	 */
	public function & getSubscriptions() {
		if ( $this->_subscriptions === null ) {
			$this->_subscriptions	=&	$this->_loadSubscriptions();
		}
		return $this->_subscriptions;
	}
	/**
	 * computes the payments made really made on this basket:
	 *
	 * @param  string          $txnIdToNotCount  (optional) txn_id of payment(s) to ignore in sum
	 * @return cbpaidPayments                    has 2 public vars: total and count: of payments made
	 */
	public function getPaymentsTotals( $txnIdToNotCount = null ) {
		$paymentTotals	=	new cbpaidPayments( $this->_db );
		$paymentTotals->getBasketPaidTotal( $this->id, $txnIdToNotCount );
		return $paymentTotals;
	}
	/**
	 * Deletes the payment_basket and all related payment_items in the database
	 * as well as corresponding newly created but inactive products
	 *
	 * @param  int      $oid   Key id of row to delete (otherwise it's the one of $this)
	 * @return boolean         TRUE if OK, FALSE if error
	 */
	public function delete( $oid = null ) {
		global $_CB_database;

		if ( $oid ) {
			$k				=	$this->_tbl_key;
			$this->$k		=	(int) $oid;
		}

		$subscriptions		=&	$this->getSubscriptions();
		foreach ( $subscriptions as $k => $v ) {
			if ( is_object( $v ) && in_array( $v->status, array( 'R', 'I' ) ) ) {
				if ( ! $subscriptions[$k]->hasPendingPayment( $this->id ) ) {
					$subscriptions[$k]->delete();
				}
			}
		}
		$query				=	"DELETE FROM #__cbsubs_payment_items"
			.	"\n WHERE payment_basket_id = ". (int) $this->id;
		$_CB_database->setQuery( $query );
		if ( !$_CB_database->query() ) {
			trigger_error( "delete paymentItems error:".htmlspecialchars($_CB_database->getErrorMsg()), E_USER_ERROR );
			return false;
		}
		if ( ! $this->deleteTotalizers() ) {
			return false;
		}
		if ( ! parent::delete( $oid ) ) {
			trigger_error( "delete paymentBasket error:".htmlspecialchars( $this->getError() ), E_USER_ERROR );
			return false;
		}
		return true;
	}
	/**
	 * Deletes the totalizers of $this basket
	 *
	 * @return boolean  TRUE: success, FALSE: database error
	 */
	protected function deleteTotalizers( ) {
		global $_CB_database;

		$query				=	"DELETE FROM #__cbsubs_payment_totalizers"
			.	"\n WHERE payment_basket_id = ". (int) $this->id;
		$_CB_database->setQuery( $query );
		if ( !$_CB_database->query() ) {
			trigger_error( "delete paymentTotalizers error:".htmlspecialchars($_CB_database->getErrorMsg(true)), E_USER_ERROR );
			return false;
		}
		$this->_paymentTotalizers	=	array();
		return true;
	}
	/**
	 * Hash with salt the basket secret
	 *
	 * @param  string|null $string  NULL: hash, OR a string which should be the hash of the installation (database and site secret) to check existing hash
	 * @param  string|null $secret  STRING: hash, OR null
	 * @return string               Hash
	 */
	public function hash( $string = null, $secret = null ) {
		global $_CB_framework;

		$date			=	date( 'mY' );
		if ( $string === null ) {
			$salt		=	array();
			$salt[0]	=	mt_rand( 1, 2147483647 );
			$salt[1]	=	mt_rand( 1, 2147483647 );		// 2 * 31 bits random
		} else {
			$salt		=	sscanf( $string, '%08x%08x%s' );
			if ( $string != sprintf( '%08x%08x%s', $salt[0], $salt[1], sha1( $salt[0] . $date . $_CB_framework->getCfg( 'db' ) . $_CB_framework->getCfg('secret') . $secret . $salt[1] ) ) ) {
				$date	=	date( 'mY', time() - 2419200 );	// 28 extra-days of grace.
			}
		}
		return sprintf( '%08x%08x%s', $salt[0], $salt[1], sha1( $salt[0] . $date . $_CB_framework->getCfg( 'db' ) . $_CB_framework->getCfg('secret') . $secret . $salt[1] ) );
	}
	/**
	 * Returns a hashed security string for the user only (not for payment processors, that one is different for security reason !
	 *
	 * @param  string               $hashToCheck    In case of checking, this is the string to check, hashed
	 * @return string
	 */
	public function checkHashUser( $hashToCheck = null ) {
		return $this->hash( $hashToCheck, $this->user_id . '_' . $this->id . '_' . $this->shared_secret );
	}
	/**
	 * Returns a hashed security string for the user only for invoice view (not for payment processors, that one is different for security reason !
	 *
	 * @param  string               $hashToCheck    In case of checking, this is the string to check, hashed
	 * @return string
	 */
	public function checkHashInvoice( $hashToCheck = null ) {
		return $this->hash( $hashToCheck, $this->user_id . '_' . $this->id . '_' . $this->shared_secret . '_' . $this->payer_status );
	}

	/**
	 * RENDERING METHODS:
	 */

	/**
	 * Renders the edit form for the invoicing address for that basket.
	 *
	 * @param  UserTable  $user
	 * @return string
	 */
	public function renderInvoicingAddressForm( $user ) {
		return $this->renderForm( 'invoice', 'editinvoiceaddress', $user->id );
	}
	/**
	 * Renders the fieldset to display the invoicing address for that basket.
	 *
	 * @return string
	 */
	public function renderInvoicingAddressFieldset() {
		$result				=	$this->renderForm( 'invoice', 'show_invoicingaddress' );

		$baseClass			=&	cbpaidApp::getBaseClass();
		$editAddressUrl		=	$baseClass->getInvoicingAddressEditUrl( $this );
		$result				=	str_replace( '</fieldset>',
			'<div class="cbregInvoiceLinks"><a href="' . $editAddressUrl . '">'
				.	CBPTXT::Th("Click here to modify invoicing address")
				.	'</a></div>' . "\n"
				.	'</fieldset>',
			$result
		);
		return $result;
	}
	/**
	 * Saves invoicing address, and if error, sets error to baseClass and
	 * Renders the edit form for the invoicing address for that basket again.
	 *
	 * @param  UserTable    $user
	 * @return string|null         NULL if no error, otherwise HTML for edit.
	 */
	public function saveInvoicingAddressForm( &$user ) {
		$invoicingAddressQuery	=	cbpaidApp::settingsParams()->get( 'invoicing_address_query' );

		if ( $invoicingAddressQuery ) {
			$return				=	$this->bindFromFormPost( 'invoice', 'editinvoiceaddress' );
			if ( $return === null ) {
				$this->_computeCountryFromTwoLettersCountry();
				$this->store();					//TBD saves a second time, not optimal, but works for now, so do not want to remove while it can be removed, but would require testing.

				$this->updateBasketRecomputeTotalizers();

				if ( ( $invoicingAddressQuery == 2 ) && ( null != ( $errorMsg = $this->checkAddressComplete() ) ) ) {
					cbpaidApp::getBaseClass()->_setErrorMSG( $errorMsg );
					$return		=	$this->renderInvoicingAddressForm( $user );
				}
			}
		} else {
			$return				=	null;
		}
		return $return;
	}
	/**
	 * Renders a XML form for this basket
	 *
	 * @param  string   $actionType
	 * @param  string   $action
	 * @param  int      $user_id
	 * @return string
	 */
	protected function renderForm( $actionType, $action, $user_id = null ) {
		$baseClass			=	cbpaidApp::getBaseClass();
		if ( $user_id ) {
			$options		=	array(	$baseClass->_getPagingParamName( 'basket' )	=> $this->id,
				$baseClass->_getPagingParamName( 'bck' )	=> $this->checkHashUser()
			);
		} else {
			$options		=	null;
		}
		return cbpaidXmlHandler::render( $actionType, $action, $this, $options, $user_id );
	}
	/**
	 * Binds post from a XML form to this basket
	 *
	 * @param  string       $actionType
	 * @param  string       $action
	 * @return string|null               NULL: success, STRING: validation error
	 */
	protected function bindFromFormPost( $actionType, $action ) {
		return cbpaidXmlHandler::bindToModel( $actionType, $action, $this );
	}
	/**
	 * Displays invoice corresponding to this basket (no checks), used in frontend and in backend.
	 *
	 * @param  UserTable   $user
	 * @param  boolean     $itsmyself
	 * @param  boolean     $displayButtons   Displays the PRINT and CLOSE buttons
	 * @return string
	 */
	public function displayInvoice( &$user, $itsmyself, $displayButtons = true ) {
		$extraStrings		=	$this->getInvoiceExtraStrings();
		$subscriptionsGUI	=	new cbpaidControllerUI();
		return $subscriptionsGUI->showInvoice( $this, $user, $itsmyself, $extraStrings, $displayButtons );
	}
	/**
	 * Displays the integrations form for the basket
	 *
	 * @param  string  $html         HTML text to put inside the <form> element
	 * @param  string  $integration  Integration name for the form params and css id
	 * @return string                HTML with form
	 */
	public function displayIntegrationForm( $html, $integration ) {
		static $nameId	=	1;

		$checkHash				=	$this->checkHashUser();
		$baseClass				=	cbpaidApp::getBaseClass();
		$urlHtmlSpecialChared	=	$baseClass->getHttpsAbsURLwithParam( array( 'Itemid' => 0, 'act' => 'saveeditbasketintegration', 'integration' => $integration, 'bskt' => (int) $this->id, 'bck' => $checkHash ), 'pluginclass' );
		$classes				=	'cbregintformdiv cbregint_' . $integration;
		return '<div class="' . $classes . '" id="cbregintformdiv' . $nameId . '">'
			. '<form enctype="multipart/form-data" action="' . $urlHtmlSpecialChared . '" method="post" name="adminForm' . $nameId . '" id="cbregintform' . $nameId++ . '" class="cb_form cbregfrontendform cbregBasketIntegration">'
			. $html
			. cbGetSpoofInputTag( 'plugin' )
			. '</form></div>';
	}
	/**
	 * Returns the protected basket URL
	 *
	 * @param  boolean              $htmlspecialchars
	 * @return string               URL
	 */
	public function getShowBasketUrl( $htmlspecialchars = true ) {
		$uri	=	cbpaidApp::getBaseClass()->getHttpsAbsURLwithParam( array( 'act' => 'showbskt', 'bskt' => $this->id, 'bck' => $this->checkHashUser(), 'Itemid' => 0 ), 'pluginclass' );
		return ( $htmlspecialchars ? $uri : cbUnHtmlspecialchars( $uri ) );
	}
	/**
	 * Returns the protected basket URL
	 *
	 * @param  UserTable|null  $user
	 * @param  string          $format  'html', 'component', 'raw', 'rawrel'		(added in CB 1.2.3)
	 * @param  string          $act     'setbsktpmtmeth', 'setbsktcurrency'
	 * @return string                   URL not sefed not htmlspecialchared
	 */
	public function getSetBasketPaymentMethodUrl( /** @noinspection PhpUnusedParameterInspection */ $user, $format = 'html', $act = 'setbsktpmtmeth' ) {
		$sefed			=	( $format != 'html' );		// We need to call CBSef for format 'raw'
		return cbUnHtmlspecialchars( cbpaidApp::getBaseClass()->getHttpsAbsURLwithParam( array( 'act' => $act, 'bskt' => $this->id, 'bck' => $this->checkHashUser(), 'Itemid' => 0 ), 'pluginclass', $sefed, null, $format ) );
	}
	/**
	 * Checks if $newCurrency is valid for $this->gateway_account, and if not, remove $this payment method
	 *
	 * @param  string  $newCurrency
	 * @return void
	 */
	private function checkPaymentMethodValidForCurrency( $newCurrency ) {
		if ( $this->gateway_account ) {
			$payAccount							=	cbpaidControllerPaychoices::getInstance()->getPayAccount( $this->gateway_account );

			$allowedNewCurrency					=	$payAccount->getPayMean()->allowedBasketCurrency( $newCurrency, $this->payment_type );
			if ( $allowedNewCurrency != $newCurrency ) {
				$this->removePaymentMethod();
			}
		}
	}
	/**
	 * Removes a payment method from $this
	 */
	private function removePaymentMethod( ) {
		$this->payment_method			=	'';
		$this->gateway_account			=	0;
		$this->payment_type				=	'';
	}
	/**
	 * Saves invoicing address, and if error, sets error to baseClass and
	 * Renders the edit form for the invoicing address for that basket again.
	 *
	 * @param  UserTable    $user
	 * @param  string       $introText
	 * @param  string       $chosenPaymentMethod
	 * @return string|null                         NULL if no error, otherwise HTML for error display.
	 */
	public function saveBasketPaymentMethodForm( $user, $introText, $chosenPaymentMethod ) {
		if ( $this->payment_status == 'NotInitiated' ) {
			$payMeans							=	cbpaidControllerPaychoices::getInstance();

			$redirectNow						=	null;
			$payChoicesHtmlArray				=	$payMeans->getPaymentMethodsParams( $user, $this, $introText, $redirectNow );
			$selected							=	false;
			if ( $redirectNow == 'radios' ) {
				foreach ( $payChoicesHtmlArray as $gatewaySubMethods ) {
					if ( is_array( $gatewaySubMethods ) ) {
						foreach ( $gatewaySubMethods as $paymentRadio ) {
							/** @var $paymentRadio cbpaidGatewaySelectorRadio */
							$selected			=	( $chosenPaymentMethod === $paymentRadio->radioValue() );
							if ( $selected ) {
								break;
							}
						}
					}
					if ( $selected ) {
						break;
					}
				}
			}
			if ( $selected ) {
				// $paymentRadio is defined here:
				/** @var $paymentRadio cbpaidGatewaySelectorRadio */
				$payAccount						=	$payMeans->getPayAccount( $paymentRadio->gatewayId );
				$this->payment_method			=	$payAccount->getPayMeanName();
				$this->gateway_account			=	$payAccount->id;
				$this->payment_type				=	$paymentRadio->paymentType;

				// change $this->mc_currency if needde by the payment method:
				$newCurrency					=	$payAccount->getPayMean()->allowedBasketCurrency( $this->mc_currency, $this->payment_type );
				if ( $this->mc_currency != $newCurrency ) {
					$this->changeCurrency( $newCurrency, false );
				}
				$this->updateBasketRecomputeTotalizers();
				$return							=	null;
			} else {
				$return							=	CBPTXT::T("Chosen Payment Method is not applicable.");
			}
		} else {
			$return								=	CBPTXT::T("Payment of this order has already been initiated.");
		}
		return $return;
	}
	/**
	 * Renders tax amount
	 *
	 * @param  float         $amount
	 * @param  boolean       $html
	 * @return string        HTML or text of basket's price
	 */
	private function renderTax( $amount, $html = true ) {
		$cbpaidMoney			=&	cbpaidMoney::getInstance();
		return $cbpaidMoney->renderPrice( $amount, $this->mc_currency, $html, false );
	}

	/**
	 * Renders price of basket
	 *
	 * @param  float         $amount
	 * @param  string        $period   private validity string (U:)datetime
	 * @param  int           $occurrences
	 * @param  boolean       $html
	 * @return string        HTML or text of basket's price
	 */
	public function renderPrice( $amount = null, $period = null, $occurrences = null, $html = true ) {
		if ( $amount === null ) {
			$amount			=	$this->mc_gross;
		}
		$cbpaidMoney			=&	cbpaidMoney::getInstance();
		$cbpaidTimes			=&	cbpaidTimes::getInstance();

		$text 				=	$cbpaidMoney->renderPrice( $amount, $this->mc_currency, $html, false );
		if ( $period ) {
			if ( cbStartOfStringMatch( $period, 'U:' ) ) {
				$period			=	substr( $period, 2 );
				$prefix			=	CBPTXT::T("calendar");
			} else {
				$prefix			=	'';
			}
			$text .= ' ' . CBPTXT::T("per") . ' '
				. $cbpaidTimes->renderPeriod( $period, 1 , false, $prefix );
			if ( $occurrences ) {
				$text .= ' '
					. sprintf( CBPTXT::T("in %d other installments for a total of %s."),
						$occurrences,
						$cbpaidTimes->renderPeriod( $period, $occurrences )				//TBD: check for ->period3 ?????		//FIXME
					);
			}
		}
		return $text;
	}
	/**
	 * Renders payments cycles with rate and validity for $this basket
	 *
	 * @param  boolean       $html
	 * @param  boolean       $roundings  TRUE: use settings roundings, FALSE: round to cents
	 * @return string                 HTML text
	 */
	public function renderRatesValidtiy( $html, $roundings = true ) {
		if ( $this->period1 ) {
			if ( $this->recur_times ) {
				$tmpltext	=	CBPTXT::T("%s for the first %s, then %s for each %s, in %s installments");
			} else {
				$tmpltext	=	CBPTXT::T("%s for the first %s, then %s each %s");		//TBD: alternate text: "&s, then, after %s, %s each %s"
			}
		} else {
			if ( $this->recur_times ) {
				$tmpltext	=	CBPTXT::T("%3\$s for each %4\$s, in %5\$s installments");
			} else {
				$tmpltext	=	CBPTXT::T("%3\$s for each %4\$s");
			}
		}
		$cbpaidMoney			=&	cbpaidMoney::getInstance();
		$cbpaidTimes			=&	cbpaidTimes::getInstance();

		if ( $this->period1 ) {
			$validity			 =	$this->ymwdPeriodToTimePeriod( $this->period1 );
			$first_validity_text =	$cbpaidTimes->renderPeriod( $validity, 1 , false );
			$first_rate_text	 =	$cbpaidMoney->renderPrice( $this->mc_amount1, $this->mc_currency, $html, $roundings );
		} else {
			$first_validity_text =	null;
			$first_rate_text	 =	null;
		}

		$validity				=	$this->ymwdPeriodToTimePeriod( $this->period3 );
		$validity_text			=	$cbpaidTimes->renderPeriod( $validity, 1 , false );
		$rate_text				=	$cbpaidMoney->renderPrice( $this->mc_amount3, $this->mc_currency, $html, $roundings );

		$text					=	sprintf( $tmpltext, $first_rate_text, $first_validity_text, $rate_text, $validity_text, $this->recur_times );
		return $text;
	}
	/**
	 * Returns list of columns indexed by database key and with untranslated column titles
	 * Includes hardcoded for now:
	 * - Strings for column titles
	 * - default values for global settings
	 *
	 * @param  string  $paramName  paramater name of global settings ( 'basket_item_line1_columns', 'basket_item_line2_columns', 'basket_totalizer_line1_columns', 'basket_item_line2_columns' )
	 * @return array
	 */
	private function _basketColumns( $paramName ) {
		static $names = array(	'ordering'					=>	'Pos.',				// CBPTXT::Th("Pos."),
			'quantity'					=>	'Qty.',				// CBPTxt::Th("Qty.");
			'artnum'					=>	'Art. Num.',		// CBPTxt::Th("Art. Num.");
			'description'				=>	'Item',				// CBPTxt::Th("Item");
			'validity_period'			=>	'Validity',			// CBPTxt::Th("Validity");
			'discount_text'				=>	'Discount',			// CBPTxt::Th("Discount");
			'first_discount_amount'		=>	'Discount',			// CBPTxt::Th("Discount");
			'second_discount_amount'	=>	'Discount',			// CBPTxt::Th("Discount");
			'tax_rule_id'				=>	'Tax code',			// CBPTxt::Th("Tax code");
			'first_original_rate'		=>	'Base',				// CBPTxt::Th("Base"); was CBPTxt::Th("Tax applied to");
			'second_original_rate'		=>	'Base',				// CBPTxt::Th("Base"); was CBPTxt::Th("Tax applied to");
			'first_tax_amount'			=>	'Sales Tax',		// CBPTxt::Th("Sales Tax");
			'second_tax_amount'			=>	'Sales Tax',		// CBPTxt::Th("Sales Tax");
			'first_rate'				=>	'Price',			// CBPTxt::Th("Price");
			'second_rate'				=>	'Price',			// CBPTxt::Th("Price");
		);

		$params										=&	cbpaidApp::settingsParams();
		if ( substr( $paramName, 0, 7 ) == 'basket_' ) {
			$basket_items_view_type					=	$params->get( 'basket_items_view_type' );
		} else {
			$basket_items_view_type					=	$params->get( 'invoice_items_view_type' );
		}

		if ( $basket_items_view_type == 1 ) {
			// We have custom settings, no need to default them again here:
			$colsSelected							=	$params->get( $paramName, '' );
		} else {
			// $displayColumns				=	array( 'ordering' => "Pos.", 'quantity' => "Quantity", 'artnum' => "Art. Num.", 'description' => "Item", 'rate' => "Price" );
			static $paramDefaultAr = array(	'basket_item_line1_columns'			=>	'description|*|first_rate|*|second_rate',
				//	'basket_item_line2_columns'			=>	'validity_period',
				'basket_totalizer_line1_columns'	=>	'artnum|*|description|*|validity_period|*|first_rate|*|second_rate',
				//	'basket_totalizer_line2_columns'	=>	'',
				'invoice_item_line1_columns'		=>	'ordering|*|quantity|*|artnum|*|description|*|first_rate|*|second_rate',
				//	'invoice_item_line2_columns'		=>	'validity_period',
				'invoice_totalizer_line1_columns'	=>	'artnum|*|description|*|validity_period|*|first_rate|*|second_rate',
				//	'invoice_totalizer_line2_columns'	=>	'',
			);
			$colsSelected							=	$paramDefaultAr[$paramName];
		}

		// Ok, now get the columns enabled in settings, and construct the corresponding headers table:
		$columns									=	explode( '|*|', $colsSelected );
		$displayColumns								=	array();
		foreach ( $columns as $itemColumn ) {
			if ( isset( $names[$itemColumn] ) ) {
				if ( strpos( $itemColumn, 'second_' ) === 0 ) {
					// if second_.... is selected, display both items and not only first_:
					$originalName					=	substr( $itemColumn, 7 );
					unset( $displayColumns['first_' . $originalName] );
					$displayColumns[$originalName]	=	$names[$itemColumn];
				} else {
					$displayColumns[$itemColumn]	=	$names[$itemColumn];
				}
			}
		}
		return $displayColumns;
	}
	/**
	 * Renders a basket with content
	 *
	 * @param  string  $summaryText     Title         default: CBPTxt("Payment")
	 * @param  string  $captionText     Subtitle      default: CBPTxt("Your membership for payment:")
	 * @param  string  $displayColumnsParamsBaseName  'basket' or 'invoice'
	 * @param  string  $output                        'html' or ?
	 * @return string                                 HTML content with a table for the basket
	 */
	public function displayBasket( $summaryText = "Payment", $captionText = "Your membership for payment:", $displayColumnsParamsBaseName = 'basket', $output = 'html' ) {
		$return							=	null;

		$params							=&	cbpaidApp::settingsParams();

		$this->loadPaymentItems();
		$this->loadPaymentTotalizers();

		$displayColumns					=	 $this->_basketColumns( $displayColumnsParamsBaseName . '_item_line1_columns' );
		$totalizerColumns				=	 $this->_basketColumns( $displayColumnsParamsBaseName . '_totalizer_line1_columns' );

		// Now computes all basket items lines columns content, and removes completely emty columns:

		// this will hold 2-dimensional array of values in basket items view:
		$itemsLinesCols					=	array();
		foreach ( array_keys( $this->_paymentItems ) as $lineIdx ) {
			$itemsLinesCols[$lineIdx]['plan_cssclass']		=		$this->_paymentItems[$lineIdx]->getPlanParam( 'cssclass', '', null );
			foreach ( array_keys( $displayColumns ) as $colName ) {
				$itemsLinesCols[$lineIdx][$colName]		=	$this->renderBasketItem( $lineIdx, $colName, $output );
			}
		}
		if ( $params->get( $displayColumnsParamsBaseName . '_item_display_empty_columns', 0 ) == 0 ) {
			$this->_cleanUpEmptyColumns( $displayColumns, $itemsLinesCols );
		}

		$totalizerLinesCols				=	array();
		foreach ( array_keys( $this->_paymentTotalizers ) as $lineIdx ) {
			$totalizerLinesCols[$lineIdx]['totalizer_type']		=	$this->_paymentTotalizers[$lineIdx]->totalizer_type;
			foreach ( array_keys( $totalizerColumns ) as $colName ) {
				$totalizerLinesCols[$lineIdx][$colName]			=	$this->renderBasketTotalizer( $lineIdx, $colName, $output );
			}
		}
		if ( $params->get( $displayColumnsParamsBaseName . '_totalizer_display_empty_columns', 0 ) == 0 ) {
			$this->_cleanUpEmptyColumns( $totalizerColumns, $totalizerLinesCols );
		}


		if ( count( $this->_paymentTotalizers ) == 0 ) {
			// We have an old basket, that did not have totalizers: we still need to display it properly:
			if ( count ( $totalizerColumns ) == 0 ) {
				$totalizerColumns		=	array( 'description' =>	'Item', 'rate'	=>	'Price' );
			}
			$totalizerLinesCols			=	array();
			if ( ( (float) $this->tax ) != 0.0 ) {
				$renderedTotalNoTax		=	$this->renderTax( $this->mc_gross - $this->tax, true );
				$renderedTaxAmount		=	$this->renderTax( $this->tax, true );

				$totalizerLinesCols[]	=	array( 'totalizer_type' => 'subtotal1', 'description' =>	CBPTXT::Th("Total before tax"), 'rate'	=>	$renderedTotalNoTax );
				$totalizerLinesCols[]	=	array( 'totalizer_type' => 'salestax', 'description' => CBPTXT::Th("Sales tax"), 'rate'	=>	$renderedTaxAmount );
			}
			$renderedBasketPrice		=	$this->renderPrice( null, null, null, true );
			$totalizerLinesCols[]		=	array( 'totalizer_type' => 'grandtotal', 'description' => CBPTXT::Th("Total"), 'rate'	=>	$renderedBasketPrice );
		}

		$subscriptionsGUI				=	new cbpaidControllerUI();
		$return							=	$subscriptionsGUI->showBasket( $this, $summaryText, $captionText, $displayColumns, $totalizerColumns, $itemsLinesCols, $totalizerLinesCols );

		return $return;
	}
	/**
	 * Cleanup empty columns (for the mode "Do not display completely empty columns (default)")
	 * @param  array  $displayColumns
	 * @param  array  $itemsLinesCols
	 */
	private function _cleanUpEmptyColumns( &$displayColumns, &$itemsLinesCols ) {
		// after the next foreach loop, this will hold the completely empty columns:
		$emptyDisplayColumns		=	$displayColumns;
		foreach ( $itemsLinesCols as /* $lineIdx => */ $line ) {
			foreach ( $line as $colName => $content ) {
				if ( $content !== null ) {
					unset( $emptyDisplayColumns[$colName] );
				}
			}
		}
		foreach ( array_keys( $emptyDisplayColumns ) as $colName ) {
			unset( $displayColumns[$colName] );
			foreach ( array_keys( $itemsLinesCols ) as $lineIdx ) {
				unset( $itemsLinesCols[$lineIdx][$colName] );
			}
		}
	}
	/*
		public function listBasketItemsKeys( ) {
			return array_keys( $this->loadPaymentItems() );
		}
		public function listBasketTotalizersKeys( ) {
			return array_keys( $this->loadPaymentTotalizers() );
		}
	*/
	/**
	 * Call-back function from cbpaidControllerUI::showBasket()
	 *
	 * @param  int    $key
	 * @param  string $variable
	 * @param  string $output
	 * @return string
	 */
	public function renderBasketItem( $key, $variable, $output = 'html' ) {
		return $this->_paymentItems[$key]->renderColumn( $variable, $output );
	}
	/**
	 * Call-back function from cbpaidControllerUI::showBasket()
	 *
	 * @param  int    $key
	 * @param  string $variable
	 * @param  string $output
	 * @return string
	 */
	public function renderBasketTotalizer( $key, $variable, $output = 'html'  ) {
		return $this->_paymentTotalizers[$key]->renderColumn( $variable, $output );
	}
	/**
	 * Gives links for maintenance of an invoice/basket in the list of invoices in frontend
	 *
	 * @return array   of HTML links
	 */
	public function renderMaintenanceButtonsHtml( ) {
		$html				=	array();
		if ( $this->authoriseAction( 'cbsubs.recordpayments' ) ) {
			if ( $this->payment_status == 'Pending') {
				$html[]		=	'<a href="' . cbpaidApp::getBaseClass()->getRecordPaymentUrl( $this ) . '" class="cbregLinkRecordPayment">'
					.	CBPTXT::Th("Record offline payment")
					.	'</a>';
			}
		}
		return $html;
	}
	/**
	 * BACKEND RENDERING METHODS:
	 */
	/**
	 * USED by XML interface ONLY !!! Renders payment basket
	 *
	 * @param  string           $value
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderBasket( $value, &$params ) {
		if ( cbpaidApp::getBaseClass() === null ) {
			//TODO: check if this is even needed:
			$pseudoPlugin				=	new getcbpaidsubscriptionsTab();
			$pseudoPlugin->params		=&	$params;
			cbpaidApp::getBaseClass( $pseudoPlugin );
		}
		$baseClass						=&	cbpaidApp::getBaseClass();
		$baseClass->outputRegTemplate();
		$this->load( (int) $value );
		return $this->displayBasket();
	}

	/**
	 * USED by XML interface ONLY !!! Renders invoice
	 *
	 * @param  string           $value
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderInvoice( $value, &$params ) {
		global $_CB_framework;

		if ( ( $_CB_framework->getUi() == 2 ) && ( $_CB_framework->myId() != 0 ) ) {

			if ( cbpaidApp::getBaseClass() === null ) {
				//TODO: check if this is even needed:
				$pseudoPlugin				=	new getcbpaidsubscriptionsTab();
				$pseudoPlugin->params		=&	$params;
				cbpaidApp::getBaseClass( $pseudoPlugin );
			}
			$baseClass						=&	cbpaidApp::getBaseClass();

			$itsmyself					=	true;			// simulate user's view of invoice.

			$baseClass->outputRegTemplate();

			if ( strpos( cbGetParam( $_GET, 'invoice' ), ',') === false ) {
				if ( $this->load( (int) $value ) ) {
					$user					=	CBuser::getUserDataInstance( (int) $this->user_id );
				}
				return $this->displayInvoice( $user, $itsmyself, true );
			} else {
				$html					=	'<div class="cbregmultipage">';
				foreach ( explode( ',', cbGetParam( $_GET, 'invoice' ) ) as $basketId ) {
					$paymentBasket		=	new self();
					if ( $paymentBasket->load( (int) $basketId ) ) {
						$user			=	CBuser::getUserDataInstance( (int) $paymentBasket->user_id );
					}
					$html				.=	$paymentBasket->displayInvoice( $user, $itsmyself, false )
						.	'<hr class="cbregpagebreak" />';
					if ( is_callable( array( 'CBuser', 'unsetUsersNotNeeded' ) ) ) {
						// CB 1.8+:
						CBuser::unsetUsersNotNeeded( array( (int) $paymentBasket->user_id ) );
					}
					unset( $paymentBasket, $user );
				}
				$html					.=	'</div>';
			}
			return $html;
		}
		return null;
	}
	/**
	 * USED by XML interface ONLY !!! Renders amount
	 *
	 * @param  string           $price
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderAmount( $price, /** @noinspection PhpUnusedParameterInspection */ &$params ) {
		if ( $price ) {
			$cbpaidMoney			=&	cbpaidMoney::getInstance();
			$priceRoundings			=	100;		// $params->get('price_roundings', 100 );
			$priceRounded			=	$cbpaidMoney->renderNumber( round( $price * $priceRoundings ) / $priceRoundings, 'money', false );
		} else {
			$priceRounded			= '-';
		}
		return $priceRounded;
	}
	/**
	 * USED by XML interface ONLY !!! Renders main currency + amount
	 *
	 * @param  string              $price
	 * @param  ParamsInterface     $params
	 * @param  string              $name    The name of the form element
	 * @param  CBSimpleXMLElement  $node    The xml element for the parameter
	 * @return string                       HTML to display
	 */
	public function renderCurrencyAmount( $price, &$params, /** @noinspection PhpUnusedParameterInspection */ $name, $node ) {
		$currency			=	$node->attributes( 'value' );
		if ( $currency ) {
			$currencyCode	=	$currency;
		} else {
			$currencyCode	=	$params->get( 'currency_code' );
		}
		$renderedAmount		=	$this->renderAmount( $price, $params );
		if ( $renderedAmount != '-' ) {
			return $currencyCode . '&nbsp;' . $renderedAmount;
		} else {
			return '-';
		}
	}
}	// class cbpaidPaymentBasket

/**
 * In-memory only class for computations only:
 *
 */
class cbpaidPayments {
	// in memory only:
	public $total	=	0.0;
	public $count	=	0;
	/** database
	 *  @var CBdatabase */
	public $_db;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db ) {
		$this->_db	=&	$db;
	}
	/**
	 * gets statistics
	 *
	 * @param  int       $basketId          Basket id for which payments have been done
	 * @param  string    $txnIdToNotCount   (optional) txn_id of payment(s) to ignore in sum
	 * @return boolean   true if could load
	 */
	public function getBasketPaidTotal( $basketId, $txnIdToNotCount = null ) {
		$sql	=	"SELECT COUNT(*) AS count, SUM(mc_gross) AS total "
			.	"\n  FROM #__cbsubs_payments "
			.	"\n  WHERE payment_basket_id = " . (int) $basketId
			.	"\n  AND payment_status = " . $this->_db->Quote( 'Completed' )
		;
		if ( $txnIdToNotCount ) {
			$sql .=	"\n  AND txn_id <> " . $this->_db->Quote( $txnIdToNotCount );
		}
		$this->_db->setQuery( $sql );
		return $this->_db->loadObject( $this );
	}
}	// class cbpaidPayments
