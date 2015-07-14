<?php
/**
 * @version $Id: cbpaidPayHandler.php 1608 2012-12-29 04:12:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Real Payment Handler class
 */
abstract class cbpaidPayHandler extends cbpaidBaseClass {
	/** gateway account class
	 * @var cbpaidGatewayAccount */
	protected $account;
	/**
	 * Gateway API version used
	 * @var int
	 */
	public $gatewayApiVersion	=	"1.2.0";
	/**
	 * Array of gateway API urls: normally:
	 * array(	'single+normal' 	=>	'normal.gateway.com',
	 *			'single+test'		=>	'tests.gateway.com',
	 *			'recurring+normal'	=>	'recurring.gateway.com',
	 *			'recurring+test'	=>	'recurring-tests.gateway.com' );
	 * @var array of string
	 */
	protected $_gatewayUrls		=	array();	// override !
	/**
	 * Hash type: 1 = only if there is a basket id (default), 2 = always, 0 = never
	 * @var int
	 */
	protected $_urlHashType	=	1;
	/**
	 * @var $_button string
	 */
	public $_button;
	/**
	 * Constructor
	 *
	 * @param  cbpaidGatewayAccount        $account
	 */
	public function __construct( $account ) {
		$this->account				=	$account;
		parent::__construct();
	}
	/**
	 * Gets an account parameter
	 *
	 * @param  string  $key       Name of the parameter
	 * @param  mixed   $default   Default value: if array() will return an array
	 * @return string|array
	 */
	public function getAccountParam( $key, $default = null ) {
		if ( isset( $this->account->$key ) ) {
			return $this->account->$key;
		} else {
			return $this->account->getParam( $key, $default );
		}
	}
	/**
	 * Utility for gateways to get the payment gateway URL without https:// out of $this->serverUrls array
	 * - depends on $case
	 * - depends on 'normal_gateway' account-param: 0 = test, 1 = normal, 2 = special url in 'gateway_$case_url' account-param
	 *
	 * @param  string  $case   Must be safe ! 'single', 'recurring' or any other case, from constant, not request
	 * @return string          URL with HTTPS://
	 */
	protected function gatewayUrl( $case = 'single' ) {
		$serverType		=	$this->getAccountParam( 'normal_gateway', 1 );
		if ( $serverType == 0 ) {
			$url		=	'https://' . $this->_gatewayUrls[$case . '+test'];
		} elseif ( $serverType == 2 ) {
			$url		=	$this->getAccountParam( 'gateway_' . $case . '_url', '' );
			if ( ! cbStartOfStringMatch( $url, 'https://' ) ) {
				$url	=	'https://' . $url;
			}
		} else {
			$url		=	'https://' . $this->_gatewayUrls[$case . '+normal'];
		}
		return $url;
	}

	/**
	 * Gateway specific methods called by CBSubs, should be overridden:
	 * ================================================================
	 */

	/**
	 * returns unique payment method name
	 *
	 * @return string  name of payment method
	 */
	public function getPayName() {
		return substr( get_class( $this ), 6 );
	}
	/**
	 * Returns text 'using your xxxx account no....'
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return string  Text
	 */
	public function getTxtUsingAccount( $paymentBasket ) {
		return sprintf( ' ' . CBPTXT::T("using %s payment method") . ' ', CBPTXT::T( $paymentBasket->payment_method ) );
	}
	/**
	 * Returns html text on current status and next steps of payment, depending on payment basket status
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  Payment basket being paid
	 * @return string    HTML
	 */
	public function getTxtNextStep( $paymentBasket ) {
		switch ( $paymentBasket->payment_status ) {
			case 'Completed':
				if ( ( $paymentBasket->txn_id ) && ( $paymentBasket->subscr_id ) ) {
					$newMsg		=	sprintf( CBPTXT::Th("Your payment has been completed with transaction id %s and with subscription id %s."), htmlspecialchars( $paymentBasket->txn_id ), htmlspecialchars( $paymentBasket->subscr_id ) );
				} elseif ( $paymentBasket->txn_id ) {
					$newMsg		=	sprintf( CBPTXT::Th("Your payment has been completed with transaction id %s."), htmlspecialchars( $paymentBasket->txn_id ) );
				} elseif ( $paymentBasket->subscr_id ) {
					$newMsg		=	sprintf( CBPTXT::Th("Your payment has been completed with subscription id %s."), htmlspecialchars( $paymentBasket->subscr_id ) );
				} else {
					$newMsg = CBPTXT::Th("Your payment has been completed.");
				}
				break;
			case 'Pending':
				$newMsg = CBPTXT::Th("Your payment is currently being processed.");
				break;
			case 'FreeTrial':
				$newMsg	=	'';
				break;
			case 'RegistrationCancelled':
				$newMsg = CBPTXT::Th("Payment operation cancelled.")
					.	' '
					.	CBPTXT::Th("Please choose another payment method.");
				break;
			case 'Denied':
				$newMsg	=	CBPTXT::Th("The payment has been denied and therefore not executed.")
					.	' '
					.	CBPTXT::Th("Please choose another payment method.");
				break;
			case 'Processed':
			case 'Reversed':
			case 'Refunded':
			case 'Partially-Refunded':
			default:
				$newMsg = CBPTXT::Th("Your transaction is not cleared and has currently following status:") . ' <strong>' . CBPTXT::Th( htmlspecialchars( $paymentBasket->payment_status ) ) . '</strong>.';
				break;
		}
		return $newMsg;
	}
	/**
	 * This is the main method to initiate a payment, depending on $redirectNow, it will either:
	 * 'redirect' : Redirect directly to the payment page of the payment gateway
	 * 'radios'   : Return all elements needed to display a list of selection radios, each with list of cards accepted, label of the radio, and a description that will be displayed when the radio is selected.
	 * 'buttons'  : Returns all elements needed to display a list of buttons with hidden form elements
	 * Note: this method can be called 2 times in case the radio is selected, to also get what's needed to display the payment buttons.
	 *
	 * $redirectNow Expected return array:
	 * ------------ ----------------------
	 * 'redirect' : return array( 'url_to_which_to_redirect' )
	 * 'radios'   : return array( array( account_id, submethod, paymentMethod:'single'|'subscribe', array(cardtypes), 'label for radio', 'description for radio' ), ... )
	 * 'buttons'  : return array( array( post_url, requestParams, customImage, altText, titleText, payNameForCssClass, butId ), ... )
	 *
	 * @param  UserTable            $user           object reflecting the user being registered (it can have id 0 or be NULL in future)
	 * @param  cbpaidPaymentBasket  $paymentBasket  Order Basket to be paid
	 * @param  string               $redirectNow    'redirect', 'radios', 'buttons', other: return null (see above)
	 * @return string|array                         array: See above, OR string: HTML to display in buttons area
	 */
	abstract public function getPaymentBasketProcess( $user, $paymentBasket, $redirectNow );
	/**
	 * Attempts to validate a successful recurring payment
	 * (optional)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  string               $returnText                  RETURN param
	 * @param  boolean              $transientErrorDoReschedule  RETURN param
	 * @return boolean              TRUE: succes, FALSE: failed or unknown result
	 */
	public function processAutoRecurringPayment( /** @noinspection PhpUnusedParameterInspection */ $paymentBasket, &$returnText, &$transientErrorDoReschedule ) {
		return null;
	}
	/**
	 * Stops a recurring payment for a basket or for specific payment items
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @param  cbpaidPaymentItem[]  $paymentItems   redirect immediately instead of returning HTML for output
	 * @return boolean                              TRUE if unsubscription done successfully, FALSE if error
	 */
	public function stopPaymentSubscription( /** @noinspection PhpUnusedParameterInspection */ $paymentBasket, $paymentItems ) {
		return false;		// override !
	}
	/**
	 * Handles the gateway-specific result of payments (redirects back to this site and gateway notifications). WARNING: unchecked access !
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket         New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata              _POST data for saving edited tab content as generated with getEditTab
	 * @param  boolean              $allowHumanHtmlOutput  Input+Output: set to FALSE if it's an IPN, and if it is already false, keep quiet
	 * @return string                                      HTML to display if frontend, text to return to gateway if notification, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	 */
	abstract public function resultNotification( $paymentBasket, $postdata, &$allowHumanHtmlOutput );
	/**
	 * Refunds a payment
	 *
	 * @param  cbpaidPaymentBasket       $paymentBasket  paymentBasket object
	 * @param  cbpaidPayment             $payment        payment object
	 * @param  cbpaidPaymentItem[]|null  $paymentItems   Array of payment items to refund completely (if no $amount)
	 * @param  boolean                   $lastRefund     Last refund for $payment ?
	 * @param  float                     $amount         Amount in currency of the payment
	 * @param  string                    $reasonText     Refund reason comment text for gateway
	 * @param  string                    $returnText     RETURN param : Text of success message or of error message
	 * @return boolean                                   true if refund done successfully, false if error
	 */
	public function refundPayment( /** @noinspection PhpUnusedParameterInspection */ $paymentBasket, $payment, $paymentItems, $lastRefund, $amount, $reasonText, &$returnText ) {
		return false;		// Override if function is available
	}
	/**
	 * Checks if $proposedCurrency is allowed by the payment method, and returns another accepted one if not.
	 *
	 * @param  string  $proposedCurrency
	 * @param  string  $payment_type
	 * @return string                     3-letter currency acceptable by this gateway
	 */
	public function allowedBasketCurrency( $proposedCurrency, /** @noinspection PhpUnusedParameterInspection */ $payment_type ) {
		$currencies		=	$this->getAccountParam( 'currencies_accepted' );
		if ( ! $currencies ) {
			// All currencies accepted: no changes:
			return $proposedCurrency;
		}
		$currencies		=	explode( '|*|', $currencies );
		if ( in_array( $proposedCurrency, $currencies ) ) {
			// Basket's currency is accepted: no changes:
			return $proposedCurrency;
		}
		$params				=	cbpaidApp::settingsParams();
		$mainCurrency		=	$params->get( 'currency_code' );
		if ( in_array( $mainCurrency, $currencies ) ) {
			return $mainCurrency;
		}
		$secondaryCurrency	=	$params->get( 'secondary_currency_code' );
		if ( in_array( $secondaryCurrency, $currencies ) ) {
			return $secondaryCurrency;
		}
		return array_shift( $currencies );
	}

	/**
	 * Internal utilities for gateways extending this class:
	 * =====================================================
	 */

	/**
	 * Maps payment handler payment status to standard cpay status
	 * This method can be overriden if non-standard payment statuses are used.
	 *
	 * @param  string    $paymentStatus     payment handler payment status
	 * @return string                       standard cpay status: Completed, Processed, Denied, Pending, Unknown
	 */
	protected function mapPaymentStatus( $paymentStatus ) {
		switch ( $paymentStatus ) {
			case 'Completed':
			case 'Processed':
			case 'Denied':
			case 'Reversed':
			case 'Refunded':
			case 'Partially-Refunded':
			case 'Pending':
			case 'RegistrationCancelled':			// This needs always to be present, as cbpay-internally-generated
			case 'NotInitiated':					// This needs always to be present, as cbpay-internally-generated
			case 'FreeTrial':						// This needs always to be present, as cbpay-internally-generated
				return $paymentStatus;
			default:
				return 'Unknown';
		}
	}
	/**
	 * Checks if this gateway can pay basket with its currency using this gateway
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return boolean
	 */
	protected function canPayBasketWithThisCurrency( $paymentBasket ) {
		$currencies	=	$this->getAccountParam( 'currencies_accepted' );
		return ( ( $currencies == '' ) || in_array( $paymentBasket->mc_currency, explode( '|*|', $currencies ) ) );

	}
	/**
	 * Utility to find the currency to convert the basket to depending on the brand
	 *
	 * @return string
	 */
	protected function mainCurrencyOfGateway() {
		$params				=	cbpaidApp::settingsParams();
		$mainCurrency		=	$params->get( 'currency_code' );

		$currencies		=	$this->getAccountParam( 'currencies_accepted' );

		if ( $currencies == '' ) {
			// Desperately return main currency:
			return $mainCurrency;
		}
		$currencies			=	explode( '|*|', $currencies );
		if ( in_array( $mainCurrency, $currencies ) ) {
			return $mainCurrency;
		}
		$secondaryCurrency	=	$params->get( 'secondary_currency_code' );
		if ( in_array( $secondaryCurrency, $currencies ) ) {
			return $secondaryCurrency;
		}
		return array_shift( $currencies );
	}
	/**
	 * Returns the base URL for the payment gateway
	 *
	 * @param  UserTable|null  $user               CB User if useful
	 * @param  int|null        $basketId
	 * @param  array|null      $additionalUrlVars
	 * @param  boolean         $noAccount          If the gateway account id should also be given (only useful for the FreeTrial gateway)
	 * @return array
	 */
	protected function _baseUrlArray( $user, $basketId = null, $additionalUrlVars = null, $noAccount = false ) {
		if ( $additionalUrlVars === null ) {
			$additionalUrlVars			=	array();
		}
		$basegetarray					=	array();
		$gacctno						=	$this->getAccountParam( 'id' );
		if ( $noAccount || ( $gacctno == 0 ) ) {
			$basegetarray['method']		=	$this->getPayName();
		} else {
			$basegetarray['gacctno']	=	$gacctno;
		}
		$pdtback						=	$this->hashPdtBack();
		if ( $pdtback ) {
			$basegetarray['pdtback']	=	$this->hashPdtBack();
		}
		$basegetarray['Itemid']			=	0;
		if ( $user && $user->id ) {
			$basegetarray['user']		=	(int) $user->id;
		}
		if ( $basketId ) {
			$basegetarray['basket']		=	(int) $basketId;
		}
		if ( count( $additionalUrlVars ) > 0 ) {
			$basegetarray				=	array_merge( $basegetarray, $additionalUrlVars );
		}
		return $basegetarray;
	}
	/**
	 * Returns an URL for the gateway depending on parameters
	 *
	 * @param  string                    $result
	 * @param  UserTable|null            $user
	 * @param  cbpaidPaymentBasket|null  $paymentBasket
	 * @param  string[string]            $additionalUrlVars
	 * @param  boolean                   $htmlspecialchars
	 * @param  boolean                   $noAccount
	 * @param  boolean                   $sefed
	 * @param  string[]                  $additionalNotPrefixedVars
	 * @return string
	 */
	protected function cbsubsGatewayUrl( $result, $user, $paymentBasket, $additionalUrlVars = null, $htmlspecialchars = true, $noAccount = false, $sefed = true, $additionalNotPrefixedVars = null ) {
		if ( $additionalUrlVars === null ) {
			$additionalUrlVars	=	array();
		}
		$basegetarray			=	$this->_baseUrlArray( $user, ( $paymentBasket ? $paymentBasket->id : null ), $additionalUrlVars, $noAccount );
		$uri					=	$this->_getAbsURLwithParam( $basegetarray, 'pluginclass', false ) . '&amp;result=' . urlencode( $result ) . ( $paymentBasket ? '&amp;cbpid=' . urlencode( $paymentBasket->shared_secret ) : '' );
		if ( $additionalNotPrefixedVars ) {
			foreach ( $additionalNotPrefixedVars as $k => $v ) {
				$uri			.=	'&amp;' . $k . '=' . urlencode( $v );
			}
		}
		if ( $sefed ) {
			return cbSef( $uri, $htmlspecialchars );
		} else {
			return ( $htmlspecialchars ? $uri : cbUnHtmlspecialchars( $uri ) );
		}
	}
	/**
	 * Returns the SUCCESS Url for $this gateway for $paymentBasket basket
	 *
	 * @param  cbpaidPaymentBasket|null  $paymentBasket
	 * @return string
	 */
	protected function getSuccessUrl( $paymentBasket ) {
		return $this->cbsubsGatewayUrl( 'success', null, $paymentBasket, array(), false, false, true );
	}
	/**
	 * Returns the CANCEL Url for $this gateway for $paymentBasket basket
	 *
	 * @param  cbpaidPaymentBasket|null  $paymentBasket
	 * @return string
	 */
	protected function getCancelUrl( $paymentBasket ) {
		return $this->cbsubsGatewayUrl( 'cancel', null, $paymentBasket, array(), false, false, true );
	}
	/**
	 * Returns the notification URL for the callbacks/IPNs (not htmlspecialchared) for $this gateway for $paymentBasket basket
	 * Uses getAccountParam( 'notifications_host' ) if it is defined
	 *
	 * @param  cbpaidPaymentBasket|null  $paymentBasket
	 * @param  boolean                   $noAccount      TRUE: do not include account number, but account method, FALSE (default): include account number
	 * @return string                    URL
	 */
	protected function getNotifyUrl( $paymentBasket, $noAccount = false ) {
		global $_CB_framework;

		$notifications_host		=	trim( $this->getAccountParam( 'notifications_host', '' ) );
		return	( $notifications_host ? $notifications_host : $_CB_framework->getCfg( 'live_site' ) )
			.	'/'
			.	cbSef( $this->cbsubsGatewayUrl( 'notify', null, $paymentBasket, array(), false, $noAccount, false ), false, 'rawrel' )
			;
	}
	/**
	 * Returns the result parameter from the URL
	 *
	 * @return string
	 */
	protected function _getResultParamFromUrl( ) {
		global $_GET;
		return cbGetParam( $_GET, 'result' );
	}
	/**
	 * Returns HTML code for hidden input fields for payment form for the gateway
	 *
	 * @param  array         $varsArray            Keyed array of GET variables for the Paypal payment link
	 * @return string                              HTML code for hidden input fields for payment form for paypal
	 */
	protected function _toHiddenInputsTxt( $varsArray ) {
		$ret				=	'';
		foreach ( $varsArray as $k => $v ) {
			$ret			.=	'<input type="hidden" name="' . htmlspecialchars( $k ) . '" value="' . htmlspecialchars( $v ) . "\" />\n";
		}
		return $ret;
	}
	/**
	 * Returns GET parameters sent by gateway without the CBSubs and Joomla/Mambo request parameters
	 *
	 * @return array
	 */
	protected function _getGetParams() {
		global $_GET;

		$requestdata	=	$_GET; // this gateway does not use posts.

		unset( $requestdata['option'], $requestdata['task'], $requestdata['user'], $requestdata['plugin'], $requestdata['no_html'], $requestdata['format'], $requestdata['tmpl'], $requestdata['Itemid'], $requestdata['lang'], $requestdata['language'], $requestdata[$this->_getPagingParamName('gacctno')], $requestdata[$this->_getPagingParamName('basket')], $requestdata['result'], $requestdata[$this->_getPagingParamName('id')] );

		return $requestdata;
	}
	/**
	 * Checks single payment and payment subscription possibilities depending on payment processor and on payment basket
	 *
	 * @param  int                  $enable_processor   payment processor state:  0:  disabled, 1: only for single payments, 2: only for payment subscriptions, 3: for single and subscription payments
	 * @param  cbpaidPaymentBasket  $paymentBasket      for checking if this basket has recurring payments
	 * @return int                  bits: 0x1 : single payment to be offered, 0x2: payment subscription to be offered, (0x3: both to be offered) : these are bits, do logical AND ( & )
	 */
	protected function _getPaySubscribePossibilities( $enable_processor, $paymentBasket ) {
		$pay1subscribe2				=	0;

		$isAnyAutoRecurring			=	$paymentBasket->isAnyAutoRecurring();
		if ( ( $isAnyAutoRecurring == 2 ) && ( ( $paymentBasket->mc_amount1 == 0 ) && ( $paymentBasket->period1 != '' ) ) ) {
			$isAnyAutoRecurring		=	1;											// first period is free: nothing to pay upfront: enforce autorecurring
		}

		$paySubscriptionForced		=	( ( $enable_processor == 2 ) || ( ( $enable_processor == 3 ) && ( $isAnyAutoRecurring == 1 ) ) );
		$paySinglePaymentForced		=	( ( $enable_processor == 1 ) || ( ( $enable_processor == 3 ) && ( $isAnyAutoRecurring == 0 ) ) );
		$payUserChoicePossible		=	( ( $enable_processor == 3 ) && ( $isAnyAutoRecurring == 2 ) );
		if ( $payUserChoicePossible || $paySinglePaymentForced || ( ! $paySubscriptionForced ) ) {		// last condition is a safeguard to display at least a payment button
			$pay1subscribe2			+=	1;
		}
		if ( $payUserChoicePossible || ( $isAnyAutoRecurring && $paySubscriptionForced ) ) {
			$pay1subscribe2			+=	2;
		}
		return $pay1subscribe2;
	}
	/**
	 * Checks single payment and payment subscription possibilities depending on payment processor and on payment basket.
	 * Use:
	 * list( $singlePaymentForced, $subscriptionForced, $userChoicePossible )	=	$this->listPaymentSingleOrSubscribePossibilities( $enable_processor, &$paymentBasket );
	 *
	 * @param  int                  $enable_processor   payment processor state:  0:  disabled, 1: only for single payments, 2: only for payment subscriptions, 3: for single and subscription payments
	 * @param  cbpaidPaymentBasket  $paymentBasket      for checking if this basket has recurring payments
	 * @return array of boolean                         only one is true: array( $singlePaymentForced, $subscriptionForced, $userChoicePossible )
	 */
	protected function listPaymentSingleOrSubscribePossibilities( $enable_processor, $paymentBasket ) {
		$subscriptionPossibilities	=	$this->_getPaySubscribePossibilities( $enable_processor, $paymentBasket );
		$singlePaymentForced		=	( $subscriptionPossibilities == 0x1 );
		$subscriptionForced			=	( $subscriptionPossibilities == 0x2 );
		$userChoicePossible			=	( $subscriptionPossibilities == 0x3 );
		return array( $singlePaymentForced, $subscriptionForced, $userChoicePossible );
	}
	/**
	 * Posts a POST form by https if available, otherwise by http and gets result.
	 *
	 * @param  string  $urlNoHttpsPrefix  URL without https:// in front (but works also with http:// or https:// in front, but it's ignored.
	 * @param  array|string  $formvars          Variables in form to post
	 * @param  int     $timeout           Timeout of the access
	 * @param  string  $result            RETURNING: the fetched access
	 * @param  int     $status            RETURNING: the status of the access (e.g. 200 is normal)
	 * @param  string  $getPostType       'post' (default) or 'get'
	 * @param  string  $contentType       'normal' (default) or 'xml' ($formvars['xml']=xml content) or 'json' (application/json)
	 * @param  string  $acceptType        '* / *' (default) or 'application/xml' or 'application/json'
	 * @param  boolean $https             SSL protocol (default: true)
	 * @param  int     $port              port number (default: 443)
	 * @param  string  $username          HTTP username authentication
	 * @param  string  $password          HTTP password authentication
	 * @param  boolean $allowHttpFallback Allow fallback to http if https not available locally (default: false)
	 * @param  string  $referer           referrer
	 * @return int     $error             error-code of access (0 for ok)
	 */
	protected function _httpsRequest( $urlNoHttpsPrefix, $formvars, $timeout, &$result, &$status, $getPostType = 'post', $contentType='normal', $acceptType='*/*', $https = true, $port = 443, $username = '', $password = '', $allowHttpFallback = false, $referer = null ) {
		return cbpaidWebservices::httpsRequest( $urlNoHttpsPrefix, $formvars, $timeout, $result, $status, $getPostType, $contentType, $acceptType, $https, $port, $username, $password, $allowHttpFallback, $referer );
	}
	/**
	 * Sets the text of the last error and logs it to the history logger
	 * @access private
	 *
	 * @param  int               $log_priority      Priority of message (UNIX-type): 0: Emergency, 1: Alert, 2: Critical, 3: Error, 4: Warning, 5: Notice, 6: Info, 7: Debug
	 * @param  cbpaidTable|null  $object            Object stored in database, so that table name of table and id of key can be stored with the error
	 * @param  string            $logMessagePrefix  Error message prefix for the logged message (simple non-html text only): will be prepended with ': '
	 * @param  string            $userMessage       Error message for user (simple non-html text only)
	 */
	protected function _setLogErrorMSG( $log_priority, $object, $logMessagePrefix, $userMessage ) {
		global $_CB_database;

		$logObject			=	new cbpaidHistory( $_CB_database );
		$logText			=	( $logMessagePrefix ? $logMessagePrefix . ( $userMessage ? ': ' . $userMessage : '' ) : $userMessage );
		$logObject->logError( $log_priority, $logText, $object );

		if ( $userMessage ) {
			$this->_setErrorMSG( $userMessage );
		}
	}
	/**
	 * Prepares an cbpaidPaymentNotification record with known values
	 *
	 * @param  string               $log_type
	 * @param  string               $paymentStatus
	 * @param  string|null          $paymentType
	 * @param  string|null          $reasonCode
	 * @param  int|null             $paymentTime
	 * @param  string               $charset
	 * @return cbpaidPaymentNotification
	 */
	protected function & _prepareIpn( $log_type, $paymentStatus, $paymentType, $reasonCode, $paymentTime, $charset ) {
		$ipn						=	new cbpaidPaymentNotification();
		$serverType					=	$this->getAccountParam( 'normal_gateway', 1 );
		$test_ipn					=	( ( $serverType == 0 ) ? 1 : 0 );

		$ipn->initNotification( $this, $test_ipn, $log_type, $paymentStatus, $paymentType, $reasonCode, $paymentTime, $charset );
		return $ipn;
	}
	/**
	 * Put $result into $ipn->raw_result and store.
	 * Trigger_error warning if store fails.
	 *
	 * @param  cbpaidPaymentNotification  $ipn
	 * @param  string              $result  'SUCCESS', 'FAILED', 'MISMATCH', 'SIGNERROR', ...
	 */
	protected function _storeIpnResult( &$ipn, $result ) {
		$ipn->raw_result 			=	$result;
		if( ! $ipn->store() ) {
			global $_CB_database;
			trigger_error( $this->getPayName() . ' log store error:' . htmlspecialchars( $_CB_database->getErrorMsg() ), E_USER_NOTICE );
		}
	}
	/**
	 * Computes a simple hash for the payment pdtback parameter
	 *
	 * @param  int  $basketId   Payment basket id
	 * @return string           Unique hash
	 */
	protected function hashPdtBack( $basketId = null ) {
		if ( ( $this->_urlHashType === 2 ) || ( ( $this->_urlHashType === 1 ) && $basketId ) ) {
			global $_CB_framework;
			$secret	=	$_CB_framework->getCfg( 'secret' );
			$site	=	$_CB_framework->getCfg( 'live_site' );
			$site	=	rtrim( ltrim( $site, 'htpsHTPS:/' ), '0123456789:/' );
			$clear	=	$secret . $this->getPayName() . 'return' . $site . $basketId;
			return md5( $clear );
		}
		return '';
	}
	/**
	 * Checks a simple hash for the payment against pdtback parameter
	 *
	 * @param  string  $hashToCheck  Hash string to check
	 * @param  int     $basketId     Payment basket id
	 * @return boolean
	 */
	public function hashPdtBackCheck( $hashToCheck, $basketId = null ) {
		if ( ( $this->_urlHashType === 2 ) || ( ( $this->_urlHashType === 1 ) && $basketId ) ) {
			return ( $hashToCheck == $this->hashPdtBack( $basketId ) );
		}
		return true;
	}
	/**
	 * Copies relevant $ipn parameters into $paymentBasket
	 *
	 * @param  cbpaidPaymentNotification  $ipn
	 * @param  cbpaidPaymentBasket        $paymentBasket
	 * @return void
	 */
	protected function _bindIpnToBasket( $ipn, &$paymentBasket ) {
		$copy			=	array( 'test_ipn', 'payer_id', 'payer_status', 'residence_country', 'business', 'receiver_email',
			'receiver_id', 'custom', 'memo', 'auth_id', 'auth_exp', 'auth_amount', 'auth_status', 'parent_txn_id',
			'payment_method', 'gateway_account', 'payment_type', 'pending_reason', 'reason_code', 'sale_id', 'txn_id', 'txn_type',
			'subscr_date', 'subscr_effective', 'recurring', 'reattempt', 'retry_at', 'recur_times', 'username', 'password',
			'subscr_id' );
		foreach ($copy as $v ) {
			if ( $ipn->$v !== null ) {
				$paymentBasket->$v	=	$ipn->$v;
			}
		}
		if ( ( $ipn->payment_status === 'Completed' ) && $ipn->payment_date ) {
			$paymentBasket->payment_date	=	$ipn->payment_date;
		}
	}
	/**
	 * Private utility function for function updatePaymentStatus(): Stores only once the completed payment for a given transaction id of a $notification
	 *
	 * @param  cbpaidPaymentBasket        $paymentBasket
	 * @param  cbpaidPaymentNotification  $notification               notification object of the payment
	 * @param  int                        $now
	 * @param  boolean|string             $txnIdMultiplePaymentDates  FALSE: unique txn_id for each payment, TRUE: same txn_id can have multiple payment dates, additionally: 'singlepayment' will not look at txn_id at all
	 * @param  string                     $updateMessage              Message for the history log for the payment when storing to database
	 * @return boolean                                          TRUE: This is really a new payment, FALSE: no, we already received and recorded it (e.g. IPN before PDT)
	 */
	private function _storePaymentOnce( &$paymentBasket, &$notification, $now, $txnIdMultiplePaymentDates, $updateMessage ) {
		global $_CB_database;

		$payment							=	new cbpaidPayment( $_CB_database );
		$whereArray							=	array( 'payment_basket_id' => (int) $paymentBasket->id, 'mc_gross' => (string) $notification->mc_gross );
		if ( ( $notification->txn_id !== null ) && ( $txnIdMultiplePaymentDates !== 'singlepayment' ) ) {
			$whereArray['txn_id']			=	(string) $notification->txn_id;
		}
		if ( $txnIdMultiplePaymentDates === true ) {
			$whereArray['payment_date']		=	(string) $notification->payment_date;
		}
		$entry_exists						=	$payment->loadThisMatching( $whereArray, array( 'id' => 'ASC' ) );

		$iAmReferencePayment				=	( ( ! $entry_exists ) || ( ! in_array( $payment->payment_status, array( 'Completed', 'Processed' ) ) ) );
		if ( $iAmReferencePayment ) {
			// now here we could be at this same place with 2 processes ! if IPN and PDT happen exactly same time (it happens!)
			if ( $notification->payment_date ) {
				$paymentDate				=	date('Y-m-d H:i:s', strtotime( $notification->payment_date ) );
			} else {
				$paymentDate				=	$paymentBasket->time_completed;
			}
			$payment->bindPayment( $paymentBasket, $notification, $paymentDate, $now );
			$payment->historySetMessage( $updateMessage );
			$payment->store();
		}

		if ( ( ! $entry_exists ) && $iAmReferencePayment ) {
			// we had to insert a new payment entry: check that it's not duplicate due to simultaneous asynchronous IPN with PDT
			$allPayments					=	$payment->loadThisMatchingList( $whereArray );
			if ( count( $allPayments ) !== 1 ) {
				if ( $payment->id != min( array_keys( $allPayments ) ) ) {
					// oops! it really happened: return that we are not the one which should change anything, and delete our duplicate record:
					$iAmReferencePayment	=	false;
					$payment->delete();
				}
				// it happened, but we are the wining payment...
			}
		}
		return $iAmReferencePayment;
	}

	/**
	 * Methods for processors and for CBSubs:
	 * ======================================
	 */

	/**
	 * Updates payment status of basket and of corresponding subscriptions if there is a change in status
	 *
	 * @param  cbpaidPaymentBasket        $paymentBasket         Basket
	 * @param  string                     $eventType             type of event (paypal type): 'web_accept', 'subscr_payment', 'subscr_signup', 'subscr_modify', 'subscr_eot', 'subscr_cancel', 'subscr_failed'
	 * @param  string                     $paymentStatus         new status (Completed, RegistrationCancelled)
	 * @param  cbpaidPaymentNotification  $notification          notification object of the payment
	 * @param  int                        $occurrences           renewal occurrences
	 * @param  int                        $autorecurring_type    0: not auto-recurring, 1: auto-recurring without payment processor notifications, 2: auto-renewing with processor notifications updating $expiry_date
	 * @param  int                        $autorenew_type        0: not auto-renewing (manual renewals), 1: asked for by user, 2: mandatory by configuration
	 * @param  boolean|string             $txnIdMultiplePaymentDates  FALSE: unique txn_id for each payment, TRUE: same txn_id can have multiple payment dates, additionally: 'SINGLEPAYMENT' will not look at txn_id at all
	 * @param  boolean                    $storePaymentRecord   TRUE: normal case, create payment record if needed. FALSE: offline case where pending payment should not create a payment record.
	 * @return void
	 */
	public function updatePaymentStatus( &$paymentBasket, $eventType, $paymentStatus, &$notification, $occurrences, $autorecurring_type, $autorenew_type, $txnIdMultiplePaymentDates, /** @noinspection PhpUnusedParameterInspection */ $storePaymentRecord = true ) {
		global $_CB_framework, $_PLUGINS;

		$pluginsLoaded								=	false;
		$basketUpdateNulls							=	false;

		$previousUnifiedStatus						=	$this->mapPaymentStatus( $paymentBasket->payment_status );
		$unifiedStatus								=	$this->mapPaymentStatus( $paymentStatus );
		// get all related subscriptions being paid by this basket:
		$subscriptions								=&	$paymentBasket->getSubscriptions();
		$thisIsReferencePayment						=	false;

		$user										=&	CBuser::getUserDataInstance( (int) $paymentBasket->user_id );

		if ( ( $paymentBasket->payment_status != $paymentStatus ) || ( $unifiedStatus == 'Partially-Refunded' ) || $autorecurring_type ) {
			if ( $paymentStatus && ( in_array( $eventType, array( 'web_accept', 'subscr_payment', 'subscr_signup' ) ) || in_array( $unifiedStatus, array( 'Reversed', 'Refunded', 'Partially-Refunded' ) ) ) ) {
				$paymentBasket->payment_status		=	$paymentStatus;
			}

			if ( in_array( $eventType, array( 'subscr_payment', 'subscr_signup' ) ) ) {
				$paymentBasket->recurring			=	1;
			}

			if ( ( $autorecurring_type == 0 ) && in_array( $unifiedStatus, array( 'Completed', 'Processed', 'FreeTrial' ) ) ) {
				$paymentBasket->mc_amount1			=	null;
				$paymentBasket->mc_amount3			=	null;
				$paymentBasket->period1				=	null;
				$paymentBasket->period3				=	null;
				$basketUpdateNulls					=	true;
			}
			// if (count($subscriptions) >= 1) {
			$now									=	$_CB_framework->now();
			$completed								=	false;
			$thisIsReferencePayment					=	false;
			$reason									=	null;

			switch ( $unifiedStatus ) {
				case 'FreeTrial':
				case 'Completed':
				case 'Processed':					// this includes Canceled_Reversal !!! :

					if ( $unifiedStatus == 'FreeTrial' ) {
						$paymentBasket->payment_status	=	'Completed';
					}

					if ( ( $unifiedStatus == 'FreeTrial' ) || ( $unifiedStatus == 'Completed' ) ) {
						if ( $notification->payment_date  ) {
							$time_completed				=	strtotime( $notification->payment_date );
						} else {
							$time_completed				=	$now;
						}
						$paymentBasket->time_completed	=	date('Y-m-d H:i:s', $time_completed);
						$completed = true;
					}

					if ( $paymentStatus == 'Canceled_Reversal' ) {
						$paymentBasket->payment_status	=	'Completed';
					}
					if ( is_object( $notification ) && isset( $notification->txn_id ) ) {
						// real payment with transaction id: store as reference payment if not already stored:
						$thisIsReferencePayment	=	$this->_storePaymentOnce( $paymentBasket, $notification, $now, $txnIdMultiplePaymentDates, 'Updating payment record because of new status of payment basket: ' . $unifiedStatus . ( $paymentStatus != $unifiedStatus ? ' (new gateway-status: ' . $paymentStatus . ')' : '' )
							.	' because of event received: ' . $eventType
							.	'. Previous status was: ' . $previousUnifiedStatus );
					} else {
						// Free trials don't have a notification:
						$thisIsReferencePayment		=	true;
					}
					if ( $thisIsReferencePayment ) {
						// payment not yet processed:
						$autorenewed					=	 ( $paymentBasket->recurring == 1 ) && ( $unifiedStatus == 'Completed' ) && ( $previousUnifiedStatus == 'Completed' );
						for ( $i = 0, $n = count( $subscriptions ) ; $i < $n ; $i++ ) {
							$reason						=	$autorenewed ? 'R' : $subscriptions[$i]->_reason;
							$subscriptions[$i]->activate( $user, $now, $completed, $reason, $occurrences, $autorecurring_type, $autorenew_type, $autorenewed ? 1 : 0 );
						}
					}
					break;

				case 'RegistrationCancelled':
				case 'Reversed':
				case 'Refunded':
				case 'Unsubscribed':
					if ( $unifiedStatus == 'RegistrationCancelled' ) {
						if ( ! ( ( $previousUnifiedStatus == 'NotInitiated' ) || ( ( $previousUnifiedStatus === 'Pending' ) && ( $paymentBasket->payment_method === 'offline' ) ) ) ) {
							return;
						}
					}
					for ( $i = 0, $n = count( $subscriptions ) ; $i < $n ; $i++ ) {
						$reason						=	$subscriptions[$i]->_reason;
						if ( ( $reason != 'R') || in_array( $unifiedStatus, array( 'Reversed', 'Refunded' ) ) ) {
							// Expired and Cancelled as well as Partially-Refunded are not reverted !		//TBD: really revert on refund everything ? a plan param would be nice here
							if ( ( ! in_array( $previousUnifiedStatus, array( 'Pending', 'In-Progress', 'Denied', 'Reversed', 'Refunded' ) ) ) && in_array( $subscriptions[$i]->status, array( 'A', 'R', 'I' ) ) && ! $subscriptions[$i]->hasPendingPayment( $paymentBasket->id ) ) {
								// not a cancelled or denied renewal:
								$subscriptions[$i]->revert( $user, $unifiedStatus );
							}
						}
					}
					if ( $unifiedStatus == 'RegistrationCancelled' ) {
						$paymentBasket->historySetMessage( 'Payment basket deleted because the subscriptions and payment got cancelled' );
						$paymentBasket->delete();		// deletes also payment_Items
					}
					$paidUserExtension				=&	cbpaidUserExtension::getInstance( $paymentBasket->user_id );
					$subscriptionsAnyAtAll			=&	$paidUserExtension->getUserSubscriptions( '' );
					$params							=&	cbpaidApp::settingsParams();
					$createAlsoFreeSubscriptions	=	$params->get('createAlsoFreeSubscriptions', 0);
					if ( count( $subscriptionsAnyAtAll ) == 0 && ! $createAlsoFreeSubscriptions ) {

						$user	=	new UserTable();

						$id		=	(int) cbGetParam( $_GET, 'user' );

						$user->load( (int) $id );

						if ( $user->id && $user->block == 1 ) {
							$user->delete( null );
						}
					}
					break;

				case 'Denied':
				case 'Pending':
					if ( $unifiedStatus == 'Denied' ) {
						// In fact when denied, it's the case as if the user attempted payment but failed it: He should be able to re-try: So just store the payment as denied for the records.
						if ( ( $eventType == 'subscr_failed' ) || ( ( $eventType == 'subscr_cancel' ) && ( $autorecurring_type != 2 ) ) ) {
							// special case of a failed attempt:
							// or this is the final failed attempt of a basket with notifications:
							break;
						}
					}
					if ( $previousUnifiedStatus == 'Completed' ) {
						return;		// do not change a Completed payment as it cannot become Pending again. If we get "Pending" after "Completed", it is a messages chronological order mistake.
					}
					break;

				case 'In-Progress':
				case 'Partially-Refunded':
				default:
					break;
			}
			if ( $eventType == 'subscr_cancel' ) {
				if ( ! in_array( $unifiedStatus, array( 'Denied', 'Reversed', 'Refunded', 'Unsubscribed' ) ) ) {
					for ( $i = 0, $n = count( $subscriptions ) ; $i < $n ; $i++ ) {
						$subscriptions[$i]->autorecurring_cancelled( $user, $unifiedStatus, $eventType );
					}
				}
			}
			for ( $i = 0, $n = count( $subscriptions ) ; $i < $n ; $i++ ) {
				$subscriptions[$i]->notifyPaymentStatus( $unifiedStatus, $previousUnifiedStatus, $paymentBasket, $notification, $now, $user, $eventType, $paymentStatus, $occurrences, $autorecurring_type, $autorenew_type );
			}
			if ( in_array( $unifiedStatus, array( 'Denied', 'Reversed', 'Refunded', 'Partially-Refunded', 'Pending', 'In-Progress' ) ) ) {
				$thisIsReferencePayment	=	$this->_storePaymentOnce( $paymentBasket, $notification, $now, $txnIdMultiplePaymentDates, 'Updating payment record because of new status of payment basket: ' . $unifiedStatus . ( $paymentStatus != $unifiedStatus ? ' (new gateway-status: ' . $paymentStatus . ')' : '' )
					. ' because of event received: ' . $eventType
					. '. Previous status was: ' . $previousUnifiedStatus );
			}
			// }
			foreach ( $paymentBasket->loadPaymentTotalizers() as $totalizer ) {
				$totalizer->notifyPaymentStatus( $thisIsReferencePayment, $unifiedStatus, $previousUnifiedStatus, $paymentBasket, $notification, $now, $user, $eventType, $paymentStatus, $occurrences, $autorecurring_type, $autorenew_type, $txnIdMultiplePaymentDates );
			}
			if ( ! in_array( $unifiedStatus, array( /* 'FreeTrial', */ 'RegistrationCancelled' ) ) ) {
				if ( $thisIsReferencePayment && in_array( $unifiedStatus, array( 'Completed', 'Processed' ) ) ) {
					$paymentBasket->setPaidInvoiceNumber( $reason );
				}
				$paymentBasket->historySetMessage( 'Updating payment basket ' . ( $paymentStatus !== null ? 'status: ' . $unifiedStatus . ( $paymentStatus != $unifiedStatus ? ' (new gateway-status: ' . $paymentStatus . ')' : '' ) : '' )
					. ' because of event received: ' . $eventType
					. ( $paymentStatus !== null ? '. Previous status was: ' . $previousUnifiedStatus : '' ) );
				$paymentBasket->store( $basketUpdateNulls );
			} else {
				//TDB ? : $paymentBasket->delete(); in case of RegistrationCancelled done above, but should be done in case of FreeTrial ? (could be a param in future)
			}
			if ( ( ! in_array( $unifiedStatus, array( 'Completed', 'Processed' ) ) ) || $thisIsReferencePayment ) {
				$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
				$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');
				$pluginsLoaded							=	true;
				$_PLUGINS->trigger( 'onCPayAfterPaymentStatusChange', array( &$user, &$paymentBasket, &$subscriptions, $unifiedStatus, $previousUnifiedStatus, $occurrences, $autorecurring_type, $autorenew_type ) );
			}
		}
		if ( ( ! in_array( $unifiedStatus, array( 'Completed', 'Processed' ) ) ) || $thisIsReferencePayment ) {
			if ( ! $pluginsLoaded ) {
				$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
				$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');
			}
			$_PLUGINS->trigger( 'onCPayAfterPaymentStatusUpdateEvent', array( &$user, &$paymentBasket, &$subscriptions, $unifiedStatus, $previousUnifiedStatus, $eventType, &$notification ) );
		}
	}
}	// class cbpaidPayHandler
