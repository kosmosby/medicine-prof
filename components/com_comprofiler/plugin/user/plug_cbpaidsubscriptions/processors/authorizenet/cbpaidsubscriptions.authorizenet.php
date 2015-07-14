<?php
/**
* @version $Id: cbpaidsubscriptions.authorizenet.php 1581 2012-12-24 02:36:44Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

// This gateway implements a payment handler using a on-site credit-card page:
// Import class cbpaidCreditCardsPayHandler that extends cbpaidPayHandler
// and implements all gateway-generic CBSubs methods.

/**
* Paid Subscriptions Tab Class for handling the CB tab api
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @author Beat
*/
class cbpaidauthorizenet extends cbpaidCreditCardsPayHandler
{
	/**
	 * Gateway API version used
	 * @var int
	 */
	public $gatewayApiVersion	=	"1.3.0";

	/**
	 * Array of gateway API urls
	 * @var array of string
	 */
	protected $_gatewayUrls		=	array(	'single+normal' 	=>	'secure.authorize.net/gateway/transact.dll',
											'single+test'		=>	'test.authorize.net/gateway/transact.dll',
											'recurring+normal'	=>	'api.authorize.net/xml/v1/request.api',
											'recurring+test'	=>	'apitest.authorize.net/xml/v1/request.api'
										 );
	/**
	 * subscription variables of currently attempted subscription
	 * @var array
	 */
	private $_subscriptionTiming;

	/**
	 * CBSUBS ON-SITE CREDIT-CARDS PAGES PAYMENT API METHODS:
	 */

	/**
	 * Attempts to authorize and capture a credit card for a single payment of a payment basket using authorize.net AIM
	 *
	 * @param  array                      $card                : $card['type'], $card['number'], $card['firstname'], $card['lastname'], $card['expmonth'], $card['expyear'], and optionally: $card['address'], $card['zip'], $card['country']
	 * @param  cbpaidPaymentBasket        $paymentBasket
	 * @param  int                        $now                  unix timestamp of now
	 * @param  cbpaidPaymentNotification  $ipn                  returns the stored notification
	 * @param  boolean                    $authnetSubscription  true if it is a subscription and amount is in mc_amount1 and not in mc_gross
	 * @return string|array                                     STRING subscriptionId  if subscription request succeeded, otherwise ARRAY( 'level' => 'spurious' or 'fatal', 'errorText', 'errorCode' => string ) of error to display
	 */
	protected function processSinglePayment( $card, $paymentBasket, $now, &$ipn, $authnetSubscription )
	{
		// form XML request:
		$formvars = $this->_encodeAIMPostRequest( 'AUTH_CAPTURE', $card, $paymentBasket, $authnetSubscription );
		
	//	echo $formvars;

		//Send the XML via curl:
		$response		=	null;
		$status			=	null;
		$error			=	$this->_httpsRequest( $this->_payUrlAIM(), $formvars, 45, $response, $status, 'post', 'normal' );
		if( $error || ( $status != 200 ) || ! $response ) {
			$return		=	array( 'level'	 => 'spurious',
							'errorText' => 'HTTPS POST Connection to payment gateway server failed (check system information in CBSubs Settings): ERROR: ' . $error . ' (' . ($status == -100 ? 'Timeout' : $status ) . ')',
							'errorCode'	=>	'8888' );
			$results	=	array();
			$log_type	=	'B';
		} else {
			// Parse the response XML results:
			$results = $this->_authorizeParseAIMReturn( $response );

	//		echo var_export( $results, true );

			if ( count( $results ) >= 70 ) {
				if ( $results[1] == '1' ) {					// Result Code = Approved
					if ( $results[69] == $paymentBasket->id ) {		// merchant-supplied
						$return = (string) $results[7];						// trans_id
						$log_type	=	'P';
					} else {
						$return		=	array(	'level'		=> 'fatal',
												'errorText'	=> 'Returned refId missmatches',
												'errorCode'	=> '9999' );
						$log_type	=	'Q';
					}
				} else {
					$return = array();
					if ( $results[1] == '2' ) {				// Result Code = Declined
						$return ['errorText'] = 'Payment refused by payment gateway: ';
						$log_type	=	'P';
					} elseif ( $results[1] == '3' ) {
						$return ['errorText'] = 'Payment error received from payment gateway: ';
						$log_type	=	'V';
					} else {
						$return ['errorText'] = 'Unknown result received from payment gateway: ' . htmlspecialchars( $results[1] );
						$log_type	=	'X';
					}
					$return ['errorText'] .= htmlspecialchars( $results[4] . ' (' . $results[3] . ')' );
					$return ['errorCode'] = $results[3];
					if ( in_array( $results[3], array(19, 20 , 21, 22, 23, 24, 25, 26, 57, 58, 59, 60, 61, 62, 63 ) ) ) {		// Please try again.
						$return['level']	='spurious';
					} else {
						$return['level']	='fatal';
					}
				}
			} else {
				$return = array( 'level'	 => 'fatal',
								 'errorText' => 'Returned text: ' . htmlspecialchars( implode( ' ', $results ) ) ,
								 'errorCode' => '1111' );
				$log_type	=	'X';
			}
		}
		$ipn = $this->_logNotification( $log_type, $now, $paymentBasket, $card, var_export( $formvars, true ), $response, $results, $return );
		return $return;
	}

	/**
	 * Attempts to subscribe a credit card for AIM + ARB subscription of a payment basket.
	 * Errors are only server reachability errors or format error, as Credit-Cards are not checked or charged in authorize.net ARB API !!! :
	 * ARB are subscriptions to a cron script running at authorize.net each day at 2:30 AM PST, while authorize.net ARB server time is MST with US DST.
	 *
	 * @param  array                      $card                : $card['type'], $card['number'], $card['firstname'], $card['lastname'], $card['expmonth'], $card['expyear'], and optionally: $card['address'], $card['zip'], $card['country']
	 * @param  cbpaidPaymentBasket        $paymentBasket
	 * @param  int                        $now                 unix timestamp of now
	 * @param  cbpaidPaymentNotification  $ipn                 returns the stored notification
	 * @param  int                        $occurrences         returns the number of occurences pay-subscribed firmly
	 * @param  int                        $autorecurring_type  returns:  0: not auto-recurring, 1: auto-recurring without payment processor notifications, 2: auto-renewing with processor notifications updating $expiry_date
	 * @param  int                        $autorenew_type      returns:  0: not auto-renewing (manual renewals), 1: asked for by user, 2: mandatory by configuration
	 * @return string|array                                    STRING subscriptionId   if subscription request succeeded, otherwise ARRAY( 'level' => 'inform', 'spurious' or 'fatal', 'errorText', 'errorCode' => string ) of error to display
	 */
	protected function processSubscriptionPayment( $card, $paymentBasket, $now, &$ipn, &$occurrences, &$autorecurring_type, &$autorenew_type )
	{
		// recuring payment:
		$ipn						=	null;

		$occurrences				=	0;
		$autorecurring_type			=	0;
		$autorenew_type				=	0;
		$authorize_trans_id			=	array();	// not int means error by default
		$authorize_subscription_id	=	array();	// not int means error by default

		if ( ( $paymentBasket->period1 && ( $paymentBasket->mc_amount1 != 0 ) )
			|| ( ( ! $paymentBasket->period1 ) && ( $paymentBasket->mc_amount3 != 0 ) ) ) {

			// Upfront amount non-null: do an AIM first:
			$authorize_trans_id		=	$this->_attemptSinglePayment( $card, $paymentBasket, $now, $ipn, 2 );
			if ( $authorize_trans_id !== false ) {
				$occurrences			=	1;
			}
/*
				$authorize_trans_id			=	$this->processSinglePayment( $card, $paymentBasket, $now, $ipn, $authnetSubscription );
				if ( is_string( $authorize_trans_id ) ) {
					$paymentBasket->bindObjectToThisObject( $ipn, $privateVarsList );
					$occurrences			=	1;
				} elseif ( is_array( $authorize_trans_id ) && isset( $authorize_trans_id['errorCode'] ) && isset( $authorize_trans_id['errorText'] ) ) {
					$this->_setLogErrorMSG( 5, $ipn, $this->getPayName() . ' AIM error returned ' . $authorize_trans_id['errorCode'], $authorize_trans_id['errorText'] );
				} else {
					$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' AIM unknown error', CBPTXT::T("Submitted payment didn't return an error but didn't complete.") );
				}
*/
		} else {
			//TBD v2: authorize and release first amount to check that credit-card number is valid !
		}
		if ( ( $paymentBasket->mc_amount3 != 0 ) && ( ! $this->getErrorMSG() ) ) {
			// Recurring amount existing and if first amount existed it got payed OK:
			//TBD v2: check if there is only a free trial then a single payment, and authorize amount now for free trial then capture at end of free trial (if not cancelled, in which case the captured amount should be released)
			// Subscribe to an ARB:
			$ipnSubscr						=	null;
			/** @var $ipnSubscr cbpaidPaymentNotification */
			$authorize_subscription_id		=	$this->_processARBsubscriptionPayment( $card, $paymentBasket, $now, $ipnSubscr );
			if ( is_string( $authorize_subscription_id ) ) {
				if ( $this->getAccountParam( 'authorize_silent_posts_set', 0 ) ) {
					$occurrences			=	1;
					$autorecurring_type		=	2;		// with gateway notifications for each payment
				} else {
					// silent posts not set: go by old method:
					$occurrences			=	$occurrences + $this->_subscriptionTiming['totalOccurrences'];
					$autorecurring_type		=	1;		// without gateway notifications for each payment
				}
				$authnetSubscription		=	( ( $this->getAccountParam( 'enabled', 0 ) >= 2 ) && $paymentBasket->isAnyAutoRecurring() );
				$autorenew_type				=	( $authnetSubscription ? 2 : 0 );			//TBD: mandatory by system imposed by implementation here !!!
				$this->_bindNotificationToBasket( $ipnSubscr, $paymentBasket );
				if ( $ipn === null ) {
					$ipn					=	$ipnSubscr;
				}
			} elseif ( is_array( $authorize_subscription_id ) && isset( $authorize_subscription_id['errorCode'] ) && isset( $authorize_subscription_id['errorText'] ) ) {
				$this->_setLogErrorMSG( 5, $ipnSubscr, $this->getPayName() . ' ARB error returned ' . $authorize_subscription_id['errorCode'], CBPTXT::T("Subscription payment registration error: ") . $authorize_subscription_id['errorText'] );
			} elseif ( is_string( $authorize_subscription_id ) ) {
				// return $authorize_subscription_id;
			} else {
				$this->_setLogErrorMSG( 3, $ipnSubscr, $this->getPayName() . ' ARB unknown error returned', CBPTXT::T("Submitted subscription payment didn't return an error but didn't complete.") );
			}
		}
		if ( is_string( $authorize_trans_id ) ) {
			return $authorize_trans_id;
		} elseif ( is_string( $authorize_subscription_id ) ) {
			return $authorize_subscription_id;
		} elseif ( is_string( $authorize_trans_id ) ) {
			return $authorize_trans_id;
		} elseif ( is_string( $authorize_subscription_id ) ) {
			return $authorize_subscription_id;
		}
		return null;
	}

	/**
	 * Attempts to unsubscribe an ARB subscription of a payment basket.
	 * ARB are subscriptions to a cron script running at authorize.net each day at 2:30 AM PST, while authorize.net ARB server time is MST with US DST.
	 *
	 * @param  cbpaidPaymentBasket        $paymentBasket
	 * @param  cbpaidPaymentItem[]        $paymentItems
	 * @param  cbpaidPaymentNotification  $ipn                        returns the stored notification
	 * @param  string                     $authorize_subscription_id
	 * @return string|array                                           STRING subscriptionId   if subscription request succeeded, otherwise ARRAY( 'level' => 'inform', 'spurious' or 'fatal', 'errorText', 'errorCode' => string ) of error to display
	 */
	protected function processSubscriptionCancellation( $paymentBasket, $paymentItems, &$ipn, $authorize_subscription_id )
	{
		global $_CB_framework;

		$card		=	null;
		$now		=	$_CB_framework->now();
		return $this->_processARBsubscriptionPayment( $card, $paymentBasket, $now, $ipn, 'CancelSubscription', $authorize_subscription_id );
	}

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
	public function refundPayment( $paymentBasket, $payment, $paymentItems, $lastRefund, $amount, $reasonText, &$returnText )
	{
		global $_CB_framework;

		// First try to VOID the payment if not yet captured:
		$trials				=	array( 'VOID', 'CREDIT' );
		$return				=	null;

		foreach ( $trials as $aimRequestType ) {
			$formvars		= $this->_encodeAIMRefundRequest( $aimRequestType, $payment, $amount );
			//Send the XML via curl:
			$response		=	null;
			$status			=	null;
			$error			=	$this->_httpsRequest( $this->_payUrlAIM(), $formvars, 45, $response, $status, 'post', 'normal' );
			if( $error || ( $status != 200 ) || ! $response ) {
				$return		=	array( 'level'	 => 'spurious',
								'errorText' => 'HTTPS POST Connection to payment gateway server failed (check system information in CBSubs Settings): ERROR: ' . $error . ' (' . ($status == -100 ? 'Timeout' : $status ) . ')',
								'errorCode'	=>	'8888' );
				$results	=	array();
				$log_type	=	'B';
			} else {
				// Parse the response XML results:
				$results = $this->_authorizeParseAIMReturn( $response );
	
		//		echo var_export( $results, true );
	
				if ( count( $results ) >= 68 ) {
					if ( $results[1] == '1' ) {					// Result Code = Approved
						$return = (string) $results[7];						// trans_id
						if ( $lastRefund ) {
							$log_type	=	'3';		// Refund
						} else {
							$log_type	=	'4';		// Partial Refund
						}
					} else {
						$return = array();
						if ( $results[1] == '2' ) {				// Result Code = Declined
							$return['errorText'] = 'Payment refused by payment gateway: ';
							$log_type	=	'3';
						} elseif ( $results[1] == '3' ) {
							$return['errorText'] = 'Payment error received from payment gateway: ';
							$log_type	=	'V';
						} else {
							$return['errorText'] = 'Unknown result received from payment gateway: ' . htmlspecialchars( $results[1] );
							$log_type	=	'X';
						}
						$return['errorText'] .= htmlspecialchars( $results[4] . ' (' . $results[3] . ')' );
						$return['errorCode'] = $results[3];
						if ( in_array( $results[3], array(19, 20 , 21, 22, 23, 24, 25, 26, 57, 58, 59, 60, 61, 62, 63 ) ) ) {		// Please try again.
							$return['level']	='spurious';
						} else {
							$return['level']	='fatal';
						}
					}
				} else {
					$return = array( 'level'	 => 'fatal',
									 'errorText' => 'Returned text: ' . htmlspecialchars( implode( ' ', $results ) ) ,
									 'errorCode' => '1111' );
					$log_type	=	'X';
				}
			}

			$_POST[$this->_getPagingParamName('number')]	=	$payment->payer_id;
			$card											=	array( 'firstname' => $payment->first_name, 'lastname' => $payment->last_name, 'type' => str_replace( ' Credit Card', '', $payment->payment_type ) );
			$ipn = $this->_logNotification( $log_type, $_CB_framework->now(), $paymentBasket, $card, var_export( $formvars, true ), $response, $results, $return, $payment );

			if ( in_array( $log_type, array( '3', '4' ) ) && ( $results[1] == 1 ) ) {
				$this->_bindNotificationToBasket( $ipn, $paymentBasket );
				$this->updatePaymentStatus( $paymentBasket, $ipn->txn_type, $ipn->payment_status, $ipn, 1, 0, 0, true );
				break;
			}
		}
		$success		=	! is_array( $return );
		if ( ! $success ) {
			$this->_setErrorMSG( $return['errorText'] );
		}
		return $success;
	}

	/**
	 * Handles a gateway notification
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  array                $postdata         'x_response_code', 'x_response_subcode', 'x_response_reason_code', 'x_response_reason_text', 'x_auth_code', 'x_avs_code', 'x_trans_id', 'x_invoice_num', 'x_description', 'x_amount', 'x_method', 'x_type', 'x_cust_id', 'x_first_name', 'x_last_name', 'x_company', 'x_address', 'x_city', 'x_state', 'x_zip', 'x_country', 'x_phone', 'x_fax', 'x_email', 'x_ship_to_first_name', 'x_ship_to_last_name', 'x_ship_to_company', 'x_ship_to_address', 'x_ship_to_city', 'x_ship_to_state', 'x_ship_to_zip', 'x_ship_to_country', 'x_tax', 'x_duty', 'x_freight', 'x_tax_exempt', 'x_po_num', 'x_MD5_Hash', 'x_cavv_response', 'x_test_request', 'x_subscription_id', 'x_subscription_paynum'
	 * @return boolean
	 */
	protected function handleNotify( $paymentBasket, $postdata )
	{
		$ret	=	$this->_handleNotifyInternal( $postdata );
		if ( $ret === false ) {
			// Return server error to PSP, so maybe he retries later:
			header('HTTP/1.0 500 Internal Server Error');
			echo 'ARB-Silent: ARB silent post Hash check failed';
			exit();
		}
		return $ret;
	}

	/**
	 * Internal method to handles a gateway notification:
	 *
	 * @param  array                $postdata         'x_response_code', 'x_response_subcode', 'x_response_reason_code', 'x_response_reason_text', 'x_auth_code', 'x_avs_code', 'x_trans_id', 'x_invoice_num', 'x_description', 'x_amount', 'x_method', 'x_type', 'x_cust_id', 'x_first_name', 'x_last_name', 'x_company', 'x_address', 'x_city', 'x_state', 'x_zip', 'x_country', 'x_phone', 'x_fax', 'x_email', 'x_ship_to_first_name', 'x_ship_to_last_name', 'x_ship_to_company', 'x_ship_to_address', 'x_ship_to_city', 'x_ship_to_state', 'x_ship_to_zip', 'x_ship_to_country', 'x_tax', 'x_duty', 'x_freight', 'x_tax_exempt', 'x_po_num', 'x_MD5_Hash', 'x_cavv_response', 'x_test_request', 'x_subscription_id', 'x_subscription_paynum'
	 * @return boolean
	 */
	private function _handleNotifyInternal( $postdata )
	{
		global $_CB_framework, $_CB_database;

		// Only ARB notifications have: x_subscription_id (Subscription ID) and the x_subscription_paynum (Subscription Payment Number)

		// check that required params are here:
		static $mandatory	=	array( 'x_cust_id', 'x_response_code', 'x_trans_id', 'x_amount', 'x_po_num', 'x_MD5_Hash' );
		foreach ( $mandatory as $v ) {
			if ( ! isset( $postdata[$v] ) ) {
				$ipn						=&	$this->_prepareIpn( 'D', '', '', 'ARB-Silent: mandatory param missing', null, 'utf-8' );
				$ipn->raw_data				=	'$message_type="' . addslashes( cbGetParam( $postdata, 'x_type' ) ) . "\";\n"
											.	/* cbGetParam() not needed: we want raw info */ '$_GET=' . var_export( $_GET, true ) . ";\n"
											.	/* cbGetParam() not needed: we want raw info */ '$_POST=' . var_export( $_POST, true ) . ";\n"
											;
				$ipn->store();

				return false;
			}
		}

		// check MD5 hash:
		if ( ! $this->_checkHashARBsilent( $postdata ) ) {
			$ipn							=&	$this->_prepareIpn( 'O', '', '', 'ARB-Silent: ARB silent post Hash check failed', null, 'utf-8' );
			$ipn->raw_data					=	'$message_type="' . addslashes( cbGetParam( $postdata, 'x_type' ) ) . "\";\n"
											.	/* cbGetParam() not needed: we want raw info */ '$_GET=' . var_export( $_GET, true ) . ";\n"
											.	/* cbGetParam() not needed: we want raw info */ '$_POST=' . var_export( $_POST, true ) . ";\n"
											;
			$ipn->store();

			return false;
		}

/*
x_response_code			=	1
x_response_subcode		=	1
x_response_reason_code	=	1
x_response_reason_text	=	This+transaction+has+been+approved%2E
x_auth_code				=	QbJHm4
x_avs_code				=	Y
x_trans_id				=	2147490176
x_invoice_num			=	INV12345					<--------
x_description			=	My+test+description
x_amount				=	0%2E44
x_method				=	CC
x_type					=	auth%5Fcapture
x_cust_id				=	CustId                      <--------
x_first_name			=	Firstname
x_last_name				=	LastNamenardkkwhczdp
x_company				=	
x_address				=	
x_city					=	
x_state					=	
x_zip					=	
x_country				=	
x_phone					=	
x_fax					=	
x_email					=	
x_ship_to_first_name	=	
x_ship_to_last_name		=	
x_ship_to_company		=	
x_ship_to_address		=	
x_ship_to_city			=	
x_ship_to_state			=	
x_ship_to_zip			=	
x_ship_to_country		=	
x_tax					=	0%2E0000
x_duty					=	0%2E0000
x_freight				=	0%2E0000
x_tax_exempt			=	FALSE
x_po_num				=	                           <-------- not in ARB
x_MD5_Hash				=	B9B3D19AEFD7BECC86C5FB3DB717D565
x_cavv_response			=	2
x_test_request			=	false
x_subscription_id		=	101635
x_subscription_paynum	=	1
*/
		// For ARB we have:
		$mandatory							=	array( 'x_test_request', 'x_subscription_id', 'x_subscription_paynum' );
		// For AIM we have: 'cb_custom' (paymentbasket id)

		// For now ignore non-ARB silent posts:
		foreach ( $mandatory as $v ) {
			if ( ! isset( $postdata[$v] ) ) {
				// This is not an ARB silent post: silently ignore it:
				return true;
			}
		}

		// get the mandatory params, such as x_cust_id:
		$x_cust_id							=	cbGetParam( $postdata, 'x_cust_id' );
		$x_subscription_id					=	cbGetParam( $postdata, 'x_subscription_id', 0 );
		$x_type								=	strtoupper( cbGetParam( $postdata, 'x_type' ) );
		$x_response_code					=	cbGetParam( $postdata, 'x_response_code' );
		// $x_response_subcode					=	cbGetParam( $postdata, 'x_response_subcode' );
		$x_response_reason_code				=	cbGetParam( $postdata, 'x_response_reason_code' );
		$x_response_reason_text				=	cbGetParam( $postdata, 'x_response_reason_text' );
		$x_invoice_num						=	cbGetParam( $postdata, 'x_invoice_num' );
		$x_trans_id							=	cbGetParam( $postdata, 'x_trans_id' );
		$x_amount							=	cbGetParam( $postdata, 'x_amount' );
		$x_method							=	cbGetParam( $postdata, 'x_method' );
		if ( ( ! $x_trans_id ) || ( ! $x_invoice_num ) || ( ! $x_cust_id ) || ( ! $x_subscription_id ) || ( ! $x_type ) || ( ! $x_response_code ) || ( ! $x_response_reason_code ) ) {
			return false;
		}

		// Log the INS:
		$log_type							=	'I';
		$paymentStatus						=	$this->_paymentStatus( $x_type, $x_response_code );
		$reasonCode							=	null;
		$paymentType						=	( $x_method == 'CC' ? 'Credit Card' : ( $x_method == 'ECHECK' ? 'E-Check' : $x_method ) );
		$reasonText							=	( $x_response_code != 1 ? $x_response_code . ': ' . $x_response_reason_text : null );		//TODO do it in normal AIM and ARB too
		$ipn								=&	$this->_prepareIpn( $log_type, $paymentStatus, $paymentType, $reasonText, $_CB_framework->now(), 'utf-8' );
		$ipn->raw_data						=	'$message_type="' . $x_type . "\";\n"
											.	/* cbGetParam() not needed: we want raw info */ '$_GET=' . var_export( $_GET, true ) . ";\n"
											.	/* cbGetParam() not needed: we want raw info */ '$_POST=' . var_export( $_POST, true ) . ";\n"
											;
		$ipn->user_id						=	(int) $x_cust_id;
		$ipn->payment_status				=	$paymentStatus;
		if ( ( $paymentStatus == 'Pending' ) && ( $x_response_code == 4 ) ) {
			$ipn->pending_reason			=	'paymentreview';		// held for payment review
		}
				
		// handle the message:
		switch ( $x_type ) {				// The type of credit card transaction: AUTH_CAPTURE, AUTH_ONLY, CAPTURE_ONLY, CREDIT, PRIOR_AUTH_CAPTURE, VOID
			case 'AUTH_CAPTURE':			// The amount is sent for authorization, and if approved, is automatically submitted for settlement.
			case 'CAPTURE_ONLY':			// This transaction type is used to complete a previously authorized transaction that was not originally submitted through the payment gateway or that requires voice authorization.
			case 'PRIOR_AUTH_CAPTURE':		// An Authorization Only and a Prior Authorization and Capture together are considered one complete transaction.
			case 'VOID':					// This transaction type is used to cancel an original transaction that is not yet settled and prevents it from being sent for settlement.
			case 'CREDIT':					// This transaction type is used to refund a customer for a transaction that was originally processed and successfully settled through the payment gateway.
			case 'AUTH_ONLY':				// This transaction type is sent for authorization only. The transaction will not be sent for settlement until the credit card transaction type Prior Authorization and Capture (see definition below) is submitted, or the transaction is submitted for capture manually in the Merchant Interface.
				$paymentBasket				=	new cbpaidPaymentBasket( $_CB_database );
				$basketLoaded				=	$paymentBasket->loadThisMatching( array( 'user_id' => (int) $x_cust_id, 'subscr_id' => (string) $x_subscription_id ) );
				if ( $basketLoaded ) {
					$ipn->bindBasket( $paymentBasket );
					$ipn->test_ipn			=	$paymentBasket->test_ipn;
				} else {
					$ipn->mc_gross			=	$x_amount;
					$ipn->mc_currency		=	'USD';
				}
				if ( in_array( $x_type, array( 'CREDIT', 'VOID' ) ) ) {
					$ipn->mc_gross			=	- $ipn->mc_gross;
				}
				$ipn->txn_id				=	$x_trans_id;
				$ipn->payment_date			=	date( 'H:i:s M d, Y T', $_CB_framework->now() );		// Paypal-style
				if ( $x_subscription_id ) {
					$ipn->txn_type			=	'subscr_payment';
					$ipn->subscr_id			=	$x_subscription_id;
				} else {
					$ipn->txn_type			=	'web_accept';
				}

				$ipn->raw_result			=	'VERIFIED';
				if ( $basketLoaded ) {
					// Account for the payment: if it is a subscription, then it is of type "auto-renewing with processor notifications" anyway:
					$autorecurring_type		=	( $x_subscription_id ? 2 : 0 );		// 0: not auto-recurring, 1: auto-recurring without payment processor notifications, 2: auto-renewing with processor notifications updating $expiry_date
					$autorenew_type			=	( $x_subscription_id ? 2 : 0 );		// 0: not auto-renewing (manual renewals), 1: asked for by user, 2: mandatory by configuration
					$this->updatePaymentStatus( $paymentBasket, $ipn->txn_type, $paymentStatus, $ipn, 1, $autorecurring_type, $autorenew_type, false );
					$ret					=	true;
				} else {
					$ret					=	false;
				}
				break;
			default:
				$ipn->log_type				=	'T';
				$ipn->reason_code			=	'Unexpected x_type';
				$ret						=	false;
		}
		$ipn->store();

		return $ret;
	}

	/**
	 * PRIVATE METHODS OF THIS CLASS
	 */

	/**
	 * Attempts to subscribe a credit card for ARB subscription of a payment basket.
	 * Errors are only server reachability errors or format error, as Credit-Cards are not checked or charged in authorize.net ARB API !!! :
	 * ARB are subscriptions to a cron script running at authorize.net each day at 2:30 AM PST, while authorize.net ARB server time is MST with US DST.
	 *
	 * @param  array|null                      $card                       : $card['type'], $card['number'], $card['firstname'], $card['lastname'], $card['expmonth'], $card['expyear'], and optionally: $card['address'], $card['zip'], $card['country'], except for 'CancelSubscription': NULL
	 * @param  cbpaidPaymentBasket             $paymentBasket              Basket
	 * @param  int                             $now                        unix timestamp of now
	 * @param  cbpaidPaymentNotification|null  $ipn                        returns the stored notification
	 * @param  string                          $arbRequestType             : 'CreateSubscription', 'UpdateSubscription', 'CancelSubscription'
	 * @param  string                          $authorize_subscription_id  (if not CreateSubscription)
	 * @return string|array                                                STRING subscriptionId   if subscription request succeeded, otherwise ARRAY( 'level' => 'inform', 'spurious' or 'fatal', 'errorText', 'errorCode' => string ) of error to display
	 */
	private function _processARBsubscriptionPayment( $card, &$paymentBasket, $now, &$ipn, $arbRequestType = 'CreateSubscription', $authorize_subscription_id = null )
	{
		if ( $paymentBasket->mc_currency != 'USD' ) {
			$return = array( 'level'	 => 'fatal',
							 'errorText' => 'Authorize.net ARB API handles only USD, not ' . $paymentBasket->mc_currency );
			$response		=	null;
			$status			=	null;
			$results		=	null;
			$xmlContent		=	null;
			$log_type		=	'Z';
		} else {
			// form XML request:
			$xmlContent = $this->_encodeArbXmlRequest( $arbRequestType, $card, $paymentBasket, $authorize_subscription_id );
			if ( is_array( $xmlContent ) ) {
				return $xmlContent[0];
			}
	/*		echo $xmlContent;
	*/		
			//Send the XML via curl:
			$response		=	null;
			$status			=	null;
			$error			=	$this->_httpsRequest( $this->_payUrlARB(), $xmlContent, 45, $response, $status, 'post', 'xml' );
			if( $error || ( $status != 200 ) || ! $response ) {
				$return		= array( 'level'	 => 'spurious',
							'errorText' => 'Connection to payment gateway server failed: ERROR: ' . $error . ' (' . ($status == -100 ? 'Timeout' : $status ) . ')' );
				$results	=	array();
				$log_type	=	'B';
			} else {
				// Parse the response XML results:
				$results = $this->_authorizeParseARBReturn( $response );
	/*			
				echo '<br> refId: '			. $results['refId']				. '<br>';
				echo ' resultCode: '		. $results['resultCode']		. '<br>';
				echo ' code: '				. $results['code']				. '<br>';
				echo ' text: '				. $results['text']				. '<br>';
				echo ' subscriptionId: '	. $results['subscriptionId']	. '<br><br>';
	*/	
				if ( $results['resultCode'] == 'Ok' ) {
					if ( $results['code'] == 'I00001' ) {			// && ( $results['text'] == 'Successful' ) Server instead returns: 'Successful.' with a dot at the end!
						if ( $results['refId'] == $paymentBasket->id ) {
							if ( $arbRequestType == '' ) {
								$paymentBasket->subscr_id = (string) $results['subscriptionId'];
							}
							$return = (string) $results['subscriptionId'];
							$log_type	=	'A';
						} else {
							$return = array( 'level'	 => 'fatal',
											 'errorText' => 'Returned refId missmatches',
											 'errorCode' => 'E10000' );
							$log_type	=	'U';
						}
					} else {
						$return = array( 'level'	 => 'inform',
										 'errorText' => 'Information received from payment gateway: ' . $results['code'] . ': ' . $results['text'],
										 'errorCode' => $results['code'] );
						$log_type	=	'A';
					}
				}
				elseif ( $results['resultCode'] == 'Error' )
				{
					if ( $results['code'] == 'E00001') {		// 'An unexpected system error occurred while processing this request. Please try again.
						$return = array( 'level'	 => 'spurious',
										 'errorText' => 'Error received from payment gateway: ' . $results['code'] . ': ' . $results['text'],
										 'errorCode' => $results['code'] );
					} else {
						$return = array( 'level'	 => 'fatal',
										 'errorText' => 'Error received from payment gateway: ' . $results['code'] . ': ' . $results['text'],
										 'errorCode' => $results['code'] );
					}
					$log_type	=	'W';
				} else {
					$return = array( 'level'	 => 'fatal',
									 'errorText' => 'Unknown resultCode received from payment gateway: ' . $results['resultCode'] . '.',
									 'errorCode' => $results['code'] );
					$log_type	=	'Y';
				}
			}
		}
		$ipn = $this->_logNotification( $log_type, $now, $paymentBasket, $card, $xmlContent, $response, $results, $return );
		return $return;
	}

	/**
	 * Logs notification
	 *
	 * @param  string                           $log_type
	 * @param  int                              $now
	 * @param  cbpaidPaymentBasket              $paymentBasket
	 * @param  array                            $card
	 * @param  string                           $xmlContent
	 * @param  string                           $response
	 * @param  array                            $results
	 * @param  array                            $return
	 * @param  cbpaidPayment                    $payment  (optional): needed only for refunds $log_type = '3' or '4'
	 * @return cbpaidPaymentNotification
	 */
	private function _logNotification( $log_type, $now, $paymentBasket, $card, &$xmlContent, &$response, &$results, &$return, $payment = null )
	{
		$paymentType				=	ucwords( $card['type'] ) . ' Credit Card';
		if  ( is_string( $return ) ) {
			if ( $log_type == '3' ) {
				$paymentStatus		=	'Refunded';
			} elseif ( $log_type == '4' ) {
				$paymentStatus		=	'Partially-Refunded';
			} else {
				$paymentStatus		=	'Completed';
			}
			$reasonCode				=	null;
		} else {
			$paymentStatus			=	'Failed';
			$reasonCode				=	( isset( $return['errorCode'] ) ? $return['errorCode'] : null );
		}

		$legalXmlContent			=	preg_replace( '/([0-9]+)([0-9]{4}<\/cardNumber>)/im', 'XXXX XXXX XXXX $2', $xmlContent );
		$legalXmlContent			=	preg_replace( '/(x_card_num[^0-9]+)([0-9]+)([0-9]{4}.*)$/im', '$1XXXXXXXXXXXX$3', $legalXmlContent );
		$legalXmlContent			=	preg_replace( '/(x_card_code[^0-9]+)([0-9]+)/im', '$1XXX', $legalXmlContent );
		$legalCCStore				=	/* cbGetParam not needed, we want raw log here! */ $_POST;
		if ( isset( $legalCCStore[$this->_getPagingParamName('number')] ) ) {
			$legalCCStore[$this->_getPagingParamName('number')]	=	'XXXX XXXX XXXX ' . substr( $legalCCStore[$this->_getPagingParamName('number')], -4, 4 );
		} else {
			$legalCCStore[$this->_getPagingParamName('number')]	=	null;
		}
		if ( isset( $legalCCStore[$this->_getPagingParamName('cvv')] ) ) {
			$legalCCStore[$this->_getPagingParamName('cvv')]		= 'XXX';
		}

		$ipn						=&	$this->_prepareIpn( $log_type, $paymentStatus, $paymentType, $reasonCode, $now, 'utf-8' );
		$ipn->bindBasket( $paymentBasket );
		if ( in_array( $log_type, array( '3', '4' ) ) ) {
			$ipn->mc_gross			=	- $results[10];
			$ipn->computeRefundedTax( $payment );
			$ipn->parent_txn_id		=	$payment->txn_id;
		}
		$ipn->setPayerNameId( $card['firstname'], $card['lastname'], $legalCCStore[$this->_getPagingParamName('number')] );
		
		$ipn->setRawData(	'$response="' . $response . "\"\n"
						.	'$results=' . var_export( $results, true ) . ";\n"
						.	'$return=' . var_export( $return, true ) . ";\n"
						.	'$xml=' . var_export( $legalXmlContent, true ) . ";\n"
						.	'$_POST=' . var_export( $legalCCStore, true ) . ";\n"
						);

		$ipn->setReceiver( $this->ISOtoUtf8( $this->getAccountParam( 'authorize_login_id' ) ) );

		if ( in_array( $log_type, array( 'P', 'B', 'Q', 'V', 'X', '3', '4' ) ) ) {
			if ( count( $results ) >= 7 ) {
				switch ( $results[1] ) {
					case 1:
						$raw_result	=	'APPROVED';
						break;
					case 2:
						$raw_result	=	'DECLINED';
						break;
					case 3:
						$raw_result	=	'ERROR';
						break;
					default:
						$raw_result	=	'UNEXPECTED';
						break;
				}
				$txn_id				=	$results[7];
			} else {
				$raw_result			=	'INSUFFICIENT REPLY';
				$txn_id				=	null;
			}
			$ipn->setRawResult( $raw_result );
			$ipn->setTxnSingle( $txn_id );
			if ( is_string( $return ) && ( count( $results ) >= 38 ) ) {
				$verify_sign		=	$results[38];
				$auth_id			=	$results[5];
				$auth_status		=	$results[1];
				$ipn->setVerifySignAuthIdStatus( $verify_sign, $auth_id, $auth_status );
			}
		} elseif ( in_array( $log_type, array( 'A', 'B', 'Z', 'U', 'Y', 'W' ) ) ) {
			if ( isset( $results['text'] ) ) {
				$ipn->setRawResult( trim( $results['text'] ) );
			}
			$ipn->setTxnSubscription( $paymentBasket, ( is_string( $return ) ? $return : null ), $this->_subscriptionTiming['startTime'] );
		}
		$ipn->store();
		return $ipn;
	}

	/**
	 * Gets single payment URL
	 *
	 * @return string
	 */
	private function _payUrlAIM( )
	{
		return $this->gatewayUrl( 'single' );
	}

	/**
	 * Gets recurring payment URL
	 *
	 * @return string
	 */
	private function _payUrlARB( )
	{
		return $this->gatewayUrl( 'recurring' );
	}

	/**
	 * Function to parse Authorize.net ARB response
	 *
	 * @param	string				$content
	 * @return	array of string		'refId', 'resultCode', 'code', 'text', 'subscriptionID'
	 */
	private function _authorizeParseARBReturn( $content )
	{
		$response	= array();
		$response['refId']			= $this->_substring_between( $content, '<refId>' );
		$response['resultCode']		= $this->_substring_between( $content, '<resultCode>' );
		$response['code']			= $this->_substring_between( $content, '<code>' );
		$response['text']			= $this->_substring_between( $content, '<text>' );
		$response['subscriptionId'] = $this->_substring_between( $content, '<subscriptionId>' );
		return $response;
	}

	/**
	 * Function to parse Authorize.net ARB response
	 *
	 * @param	string				$content
	 * @return	array of string		indexes numbered as in AIM documentation.
	 */
	private function _authorizeParseAIMReturn( $content )
	{
		$content	= substr( $content, 1, -1 );
		$response	= explode( '|*|', $content, 200 );
		array_unshift( $response, null );
		return $response;
	}

	/**
	 * Helper function for parsing XML response
	 *
	 * @param	string	$haystack	XML raw content
	 * @param	string	$startTag		starting tag (ending tag starts with '</' instead of '<'
	 * @return	string				string between tags
	 */
	private function _substring_between( $haystack, $startTag ) 
	{
		$endTag = str_replace( '<', '</', $startTag );
		if ( ( strpos( $haystack, $startTag ) === false ) || ( strpos( $haystack, $endTag ) === false ) ) {
			return false;
		} else {
			$start_position	= strpos( $haystack, $startTag ) + strlen( $startTag );
			$end_position	= strpos( $haystack, $endTag );
			return substr( $haystack, $start_position, $end_position - $start_position );
		}
	}

	/**
	* UTF-8 aware alternative to substr
	* Return part of a string given character offset (and optionally length)
	* Note: supports use of negative offsets and lengths but will be slower
	* when doing so
	*
	* @param string           $str     UTF-8 String to shorten
	* @param int              $offset  number of UTF-8 characters offset (from left)
	* @param int|null         $length  (optional) length in UTF-8 characters from offset
	* @return string|boolean           FALSE if failure
	*/
	private function _cbp_utf8_substr( $str, $offset, $length = NULL )
	{
		//TBD To check: maybe cbIsoUtf_substr() should do the same ???!
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $str, $offset, $length );
		} else {	
		    if ( $offset >= 0 && $length >= 0 ) {
		
		        if ( $length === NULL ) {
		            $length = '*';
		        } else {
		            if ( !preg_match('/^[0-9]+$/', $length) ) {
		                trigger_error('utf8_substr expects parameter 3 to be long', E_USER_WARNING);
		                return FALSE;
		            }
		
		            $strlen = strlen(utf8_decode($str));
		            if ( $offset > $strlen ) {
		                return '';
		            }
		
		            if ( ( $offset + $length ) > $strlen ) {
		               $length = '*';
		            } else {
		                $length = '{'.$length.'}';
		            }
		        }
		
		        if ( !preg_match('/^[0-9]+$/', $offset) ) {
		            trigger_error('utf8_substr expects parameter 2 to be long', E_USER_WARNING);
		            return FALSE;
		        }
		
		        $pattern	=	'/^.{'.$offset.'}(.'.$length.')/us';
				$matches	=	null;
		        preg_match($pattern, $str, $matches);
		
		        if ( isset($matches[1]) ) {
		            return $matches[1];
		        }
		
		        return FALSE;
		
		    } else {
		
		        // Handle negatives using different, slower technique
		        // From: http://www.php.net/manual/en/function.substr.php#44838
		        $ar			=	null;
		        preg_match_all('/./u', $str, $ar);
		        if( $length !== NULL ) {
		            return join('',array_slice($ar[0],$offset,$length));
		        } else {
		            return join('',array_slice($ar[0],$offset));
		        }
		    }
		}
	}

	/**
	 * Computes authorize.net's local date at GMT -7 (without DST)
	 *
	 * @param  int     $startTime
	 * @return string
	 */
	private function _computeAuthorizeLocalDate( $startTime )
	{
		$localUTCoffsetSeconds	= date( 'Z', $startTime );
		$authorizeDotNetOffset	= -7*3600;		// GMT -7 (without DST)																	//TBD: check if their server goes DST... !
		$authorizeDotNetTime	= strtotime( sprintf( '%+d seconds', $authorizeDotNetOffset - $localUTCoffsetSeconds ), $startTime );	//TBD: this does not exactly match DST switchovers...
		$authorizeDotNetDate	= date( 'Y-m-d', $authorizeDotNetTime );
		return $authorizeDotNetDate;
	}

	/**
	 * Converts array( int $qty, string $unit ) of e.g. (1, 'W') into array( $qty, $unit, $seconds_from_now )

	 * @param  array  $periodTypeArray  array( int $qty, string $unit )
	 * @return array                    array( $qty, $unit, $seconds_from_now )
	 */
	private function _authnetPeriodsLimits( $periodTypeArray )
	{
		global $_CB_framework;

		$p		=	$periodTypeArray[0];
		$t		=	$periodTypeArray[1];

		if  ( $t == 'W' ) {
			$t	=	'D';
			$p	=	$p * 7;
		}
		if ( $t == 'Y' ) {
			$t	=	'M';
			$p	=	$p * 12;
		}
/* Let authorize.net give errors on the followings:
		if ( ( $t == 'D' ) && ( $p < 7 ) ) {
			trigger_error( sprintf( 'Trying to subscribe %s days to authorize.net ARB, subscription augmented to ARB minimum (7 days).', $p ), E_USER_WARNING);
			$t	=	'D';
			$p	=	7;
		}
		if ( ( $t == 'D' ) && ( $p > 365 ) ) {
			trigger_error( sprintf( 'Trying to subscribe %s days to authorize.net ARB, subscription limited to ARB maximum (365 days).', $p ), E_USER_WARNING);
			$t	=	'D';
			$p	=	365;
		}
		if ( ( $t == 'M' ) &&  ( $p > 12 ) ) {
			trigger_error( sprintf( 'Trying to subscribe %s months to authorize.net ARB, subscription limited to ARB maximum (12 months).', $p ), E_USER_WARNING);
			$t	=	'M';
			$p	=	12;
		}
*/
		if ( $t == 'D' ) {
			$t		=	'days';
			$secs	=	$p * 3600 * 24;
		} elseif ( $t == 'M' ) {
			$t		=	'months';
			$now	=	$_CB_framework->now();
			$secs	=	strtotime( '+' . intval( $p ) . ' month', $now ) - $now;
		} else {
			$secs	=	null;
		}
		return array( $p, $t, $secs );
	}

	/**
	 * Computes subscription timing depending on credit-card
	 * Also sets $this->_subscriptionTiming
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  string               $firstChargeMode
	 * @param  array                $card             array( 'expmonth' => mm, 'expyear' => yyyy )
	 * @return array
	 */
	private function _computeSubscriptionTiming( &$paymentBasket, $firstChargeMode, $card )
	{
		global $_CB_framework;

		$subscriptionTiming = array();
		$now = $_CB_framework->now();
		if ( $firstChargeMode == 'noUpfrontFirstCharge' ) {

			list( $p3, $t3, $secs3 )					=	$this->_authnetPeriodsLimits( explode( ' ', $paymentBasket->period3 ) );
			if ( $paymentBasket->period1 ) {
				list( , , $secs1 )						=	$this->_authnetPeriodsLimits( explode( ' ', $paymentBasket->period1 ) );
			} else {
				$secs1									=	$secs3;
			}
			if ( $paymentBasket->period2 ) {
				trigger_error( CBPTXT::T("authorize.net ARB does not support 2 initial periods"), E_USER_WARNING);
			}
			if ( $paymentBasket->recur_times ) {
				$rt										=	$paymentBasket->recur_times;
			} else {
				$rt										=	null;
			}

			$cardExpTimePeriod							=	$this->getCardExpTime( $card ) - $now;
			// limit removed now:	$secsInThreeYears				=	strtotime( '+3 year', $now ) - $now;
			// limit removed now:	$cardExpTimePeriod				=	min( $cardExpTimePeriod, $secsInThreeYears );
			$cardExpTimePeriod							=	$cardExpTimePeriod - $secs1;
			$subPeriodsDuringCardValidity				=	floor( $cardExpTimePeriod / $secs3 ) + 1;		// + 1 because first ding is on begin of period at $now+$secs1
			if ( $subPeriodsDuringCardValidity < 1 ) {
				$possibleToInvoiceOccurrences			=	0;
			} elseif ( ( ! $rt ) || ( $subPeriodsDuringCardValidity < $rt ) ) {
				$possibleToInvoiceOccurrences			=	$subPeriodsDuringCardValidity;
			} else {
				$possibleToInvoiceOccurrences			=	$rt;
			}

			$subscriptionTiming['startTime']			=	$now + $secs1;
			$subscriptionTiming['startDate']			=	$this->_computeAuthorizeLocalDate( $subscriptionTiming['startTime'] );
			$subscriptionTiming['intervalLength']		=	$p3;
			$subscriptionTiming['intervalUnit']			=	$t3;
			$subscriptionTiming['totalOccurrences']		=	$possibleToInvoiceOccurrences;
//FIXME: once ARBUpdateSubscriptionRequest is implemented: replace some of the above by:			$subscriptionTiming['totalOccurrences']		=	$rt ? $rt : '9999';

			$this->_subscriptionTiming					=	$subscriptionTiming;
/*
			$subscriptions	= $paymentBasket->getSubscriptions();
			if ( is_array( $subscriptions ) && ( count( $subscriptions ) > 0 ) ) {
				
				$subscriptionValidity	= null;
				$subscriptionExpiry		= null;
				foreach ( $subscriptions as $sub ) {
					$subExpiry	 = $sub->getExpiryDate( $now );
					$plan		 = $sub->getPlan();
					$subValidity = $plan->get( 'validity' );
					if ( $subscriptionValidity === null ) {
						$subscriptionValidity = $subValidity;
					} elseif ( $subValidity && ( $subscriptionValidity != $subValidity ) ) {
						trigger_error('trying to subscribe ARB to subscriptions of different durations: ' . $subscriptionValidity . ' <> ' . $subValidity, E_USER_ERROR );
					}
					if ( $subscriptionExpiry === null ) {
						$subscriptionExpiry = $subExpiry;
					} elseif ( $subExpiry && ( $subscriptionExpiry != $subExpiry ) ) {
						trigger_error('trying to subscribe ARB to subscriptions expiring at different dates: ' . $subscriptionExpiry . ' <> ' . $subExpiry, E_USER_ERROR );
					}
				}
				
				list($y, $c, $d, $h, $m, $s) = $plan->getValidity();
				if ( ( $s == 0 ) && ( $m == 0 ) && ( ( $h % 24 ) == 0 ) ) {
					$days	= $d + ( (int) ( $h / 24 ) );
					$months = $c + ( $y * 12 );
					if ( $days && $months ) {
						trigger_error('trying to subscribe ARB to subscriptions of mixed days and months duration.', E_USER_ERROR );
					}
				} else {
					trigger_error('trying to subscribe ARB to subscriptions of non-day-multiple duration.', E_USER_ERROR );
				}
				if ( $subscriptionExpiry === false ) {
					$startTime = $now;
				} else {
					$startTime = $plan->strToTime( $subscriptionExpiry );
				}

				$cardExpTimePeriod				= $this->getCardExpTime( $card )		   - $now;
				$subExpiryTimePeriod			= $plan->getFullPeriodValidityTime( $now );
				$subPeriodsDuringCardValidity	= floor( $cardExpTimePeriod / $subExpiryTimePeriod );
				$possibleToInvoiceOccurrences	= $subPeriodsDuringCardValidity + 1;	// including initial invoicing		//TBD: setting to limit renewals
				
				$paymentBasket->subscr_date		= date( 'Y-m-d H:i:s', $startTime );
				$paymentBasket->recur_times		= $possibleToInvoiceOccurrences;
				$paymentBasket->period3			= $days + $months . ' ' . ( $days ? 'D' : 'M' );
				$paymentBasket->recurring		= ( ( $possibleToInvoiceOccurrences > 1 ) ? '1' : '0' );

				$subscriptionTiming['startDate']		= $this->_computeAuthorizeLocalDate( $startTime );					//TBD: separate this from previous calculation...
				$subscriptionTiming['intervalLength']	= $days + $months;
				$subscriptionTiming['intervalUnit']		= ( $days ? 'days' : 'months' );

				$subscriptionTiming['totalOccurrences']	= $possibleToInvoiceOccurrences;
			}
*/
		}
		return $subscriptionTiming;
	}

	/**
	 * Prepares ARB request (check outside of this that amount is in USD !!!!
	 *
	 * @param  string				 $arbRequestType : 'CreateSubscription', 'UpdateSubscription', 'CancelSubscription'
	 * @param  array|null			 $card : $card['type'], $card['number'], $card['firstname'], $card['lastname'], $card['expmonth'], $card['expyear'], and optionally: $card['address'], $card['zip'], $card['country']
	 * @param  cbpaidPaymentBasket	 $paymentBasket		WARNING: Using mc_amount3 as price as it's a subscription, instead of mc_gross.
	 * @param  string				 $subscriptionId
	 * @return mixed				 string of XML request 
	 */
	private function _encodeArbXmlRequest( $arbRequestType, $card, &$paymentBasket, $subscriptionId = null )
	{
		$authorize_login_id			= $this->ISOtoUtf8( $this->getAccountParam( 'authorize_login_id' ) );
		$authorize_transaction_key	= $this->ISOtoUtf8( $this->getAccountParam( 'authorize_transaction_key' ) );
		$refId						= $this->ISOtoUtf8( $paymentBasket->id );

		if ( $arbRequestType != 'CancelSubscription' ) {

			// CreateSubscription or UpdateSubscription:

			$cardNumber					= substr( preg_replace ( '/[^0-9]+/', '', strval( $card['number'] ) ), 0, 16 );
			$cardExpirationDate			= substr( strval( intval( $card['expyear'] ) ), 0, 4 ) . '-' . substr( sprintf( '%02d', intval( $card['expmonth'] ) ), 0, 2 );
			$firstName					= $this->_cbp_utf8_substr( $this->ISOtoUtf8( $card['firstname'] ), 0, 50 );
			$lastName					= $this->_cbp_utf8_substr( $this->ISOtoUtf8( $card['lastname'] ), 0, 50 );
			$email						= $this->_cbp_utf8_substr( $this->ISOtoUtf8( $paymentBasket->payer_email ), 0, 255 );
			$country					= ( isset( $card['country'] ) ? $this->_cbp_utf8_substr( $this->ISOtoUtf8( $card['country'] ), 0, 60 ) : null );
			$zip						= ( isset( $card['zip'] ) ? $this->_cbp_utf8_substr( $this->ISOtoUtf8( $card['zip'] ), 0, 20 ) : null );
			$address					= ( isset( $card['address'] ) ? $this->_cbp_utf8_substr( $this->ISOtoUtf8( $card['address'] ), 0, 60 ) : null );

			if ( $arbRequestType == 'CreateSubscription' ) {

				$subscriptionName			= $this->_cbp_utf8_substr( $this->ISOtoUtf8( $paymentBasket->item_name ), 0, 20 );
				$subscriptionDescription	= $this->_cbp_utf8_substr( $this->ISOtoUtf8( $paymentBasket->item_name ), 0, 255 );

				$subscriptionTiming			= $this->_computeSubscriptionTiming( $paymentBasket, 'noUpfrontFirstCharge', $card );
				if ( $subscriptionTiming['totalOccurrences'] == 0 ) {
					return array( CBPTXT::T("Credit card expiry too short for subscribing to any automatically re-occurrings payments at this time.") . ' ' . CBPTXT::T("Please renew from your subscriptions in time.") );
				}
				$amount						= sprintf( '%.2f', $paymentBasket->mc_amount3 );
				$trialAmount				= null;
				if ( $paymentBasket->mc_currency != 'USD' ) {
					trigger_error('Authorize.net ARB API handles only USD, not ' . $paymentBasket->mc_currency, E_USER_ERROR );
				}

				//Build XML to post
				$content =	  "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
					. "<ARBCreateSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">\n"
					. "  <merchantAuthentication>\n"
					. "    <name>"				. htmlspecialchars( $authorize_login_id )			. "</name>\n"
					. "    <transactionKey>"	. htmlspecialchars( $authorize_transaction_key )	. "</transactionKey>\n"
					. "  </merchantAuthentication>\n"
					. "  <refId>"				. htmlspecialchars( $refId )						. "</refId>\n"
					. "  <subscription>\n"
					. "    <name>"				. htmlspecialchars( $subscriptionName )			. "</name>\n"
					. "    <paymentSchedule>\n"
					. "      <interval>\n"
					. "        <length>"		. htmlspecialchars( $subscriptionTiming['intervalLength'] )	. "</length>\n"
					. "        <unit>"			. htmlspecialchars( $subscriptionTiming['intervalUnit'] )		. "</unit>\n"
					. "      </interval>\n"
					. "      <startDate>"		. htmlspecialchars( $subscriptionTiming['startDate'] )			. "</startDate>\n"
					. "      <totalOccurrences>". htmlspecialchars( $subscriptionTiming['totalOccurrences'] )	. "</totalOccurrences>\n";
				if ( $trialAmount ) {
					$content .= "      <trialOccurrences>". '1'							. "</trialOccurrences>\n";
				}
				$content .=   "    </paymentSchedule>\n"
					. "    <amount>"			. htmlspecialchars( $amount )						. "</amount>\n";
				if ( $trialAmount ) {
					$content .= "    <trialAmount>"		. htmlspecialchars( $trialAmount )				. "</trialAmount>\n";
				}
				$content .=   "    <payment>\n"
					. "      <creditCard>\n"
					. "        <cardNumber>"	. htmlspecialchars( $cardNumber )					. "</cardNumber>\n"
					. "        <expirationDate>". htmlspecialchars( $cardExpirationDate )		. "</expirationDate>\n"
					. "      </creditCard>\n"
					. "    </payment>\n"
					. "    <order>\n"
					. "      <invoiceNumber>"	. htmlspecialchars( $paymentBasket->invoice ? $paymentBasket->invoice : $paymentBasket->id )			. "</invoiceNumber>\n"
					. "      <description>"		. htmlspecialchars( $subscriptionDescription )	. "</description>\n"
					. "    </order>\n"
					. "    <customer>\n"
					. "      <id>"				. htmlspecialchars( $paymentBasket->user_id )		. "</id>\n"
					. "      <email>"			. htmlspecialchars( $email )						. "</email>\n"
					. "    </customer>\n"
					. "    <billTo>\n"
					. "      <firstName>"		. htmlspecialchars( $firstName )					. "</firstName>\n"
					. "      <lastName>"		. htmlspecialchars( $lastName )						. "</lastName>\n"
					. ( $address !== null ?
						"      <address>"			. htmlspecialchars( $address )						. "</address>\n"
						: ''
					)
					. ( $zip !== null ?
						"      <zip>"				. htmlspecialchars( $zip )							. "</zip>\n"
						: ''
					)
					. ( $country !== null ?
						"      <country>"			. htmlspecialchars( $country )						. "</country>\n"
						: ''
					)
					. "    </billTo>\n"
					. "  </subscription>\n"
					. "</ARBCreateSubscriptionRequest>\n";

			} elseif ( $arbRequestType == 'UpdateSubscription' ) {

				//Build XML to post
				$content =	  "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
					. "<ARBUpdateSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">\n"
					. "  <merchantAuthentication>\n"
					. "    <name>"				. htmlspecialchars( $authorize_login_id )			. "</name>\n"
					. "    <transactionKey>"	. htmlspecialchars( $authorize_transaction_key )	. "</transactionKey>\n"
					. "  </merchantAuthentication>\n"
					. "  <refId>"				. htmlspecialchars( $refId )						. "</refId>\n"
					. "  <subscriptionId>"		. htmlspecialchars( $subscriptionId )				. "</subscriptionId>\n"
					. "  <subscription>\n"
					. "    <payment>\n"
					. "      <creditCard>\n"
					. "        <cardNumber>"	. htmlspecialchars( $cardNumber )					. "</cardNumber>\n"
					. "        <expirationDate>". htmlspecialchars( $cardExpirationDate )		. "</expirationDate>\n"
					. "      </creditCard>\n"
					. "    </payment>\n"
					. "    <customer>\n"
					. "      <id>"				. htmlspecialchars( $paymentBasket->user_id )		. "</id>\n"
					. "      <email>"			. htmlspecialchars( $email )						. "</email>\n"
					. "    </customer>\n"
					. "    <billTo>\n"
					. "      <firstName>"		. htmlspecialchars( $firstName )					. "</firstName>\n"
					. "      <lastName>"		. htmlspecialchars( $lastName )					. "</lastName>\n"
					. ( $address !== null ?
						"      <address>"			. htmlspecialchars( $address )						. "</address>\n"
						: ''
					)
					. ( $zip !== null ?
						"      <zip>"				. htmlspecialchars( $zip )							. "</zip>\n"
						: ''
					)
					. ( $country !== null ?
						"      <country>"			. htmlspecialchars( $country )						. "</country>\n"
						: ''
					)
					. "    </billTo>\n"
					. "  </subscription>\n"
					. "</ARBUpdateSubscriptionRequest>\n";

			} else {
				$content	=	null;
			}
		} else {	// 'CancelSubscription':

			//Build XML to post
			$content =	  "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
				. "<ARBCancelSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">\n"
				. "  <merchantAuthentication>\n"
				. "    <name>"				. htmlspecialchars( $authorize_login_id )			. "</name>\n"
				. "    <transactionKey>"	. htmlspecialchars( $authorize_transaction_key )	. "</transactionKey>\n"
				. "  </merchantAuthentication>\n"
				. "  <refId>"				. htmlspecialchars( $refId )						. "</refId>\n"
				. "  <subscriptionId>"		. htmlspecialchars( $subscriptionId )				. "</subscriptionId>\n"
				. "</ARBCancelSubscriptionRequest>\n";

		}
		return $content;
	}

	/**
	 * Prepares AIM request
	 *
	 * @param  string				$aimRequestType	 : AUTH_CAPTURE, AUTH_ONLY, CAPTURE_ONLY, CREDIT, VOID, PRIOR_AUTH_CAPTURE
	 * @param  array				$card			 : $card['type'], $card['number'], $card['firstname'], $card['lastname'], $card['expmonth'], $card['expyear'], and optionally: $card['address'], $card['zip'], $card['country']
	 * @param  cbpaidPaymentBasket	$paymentBasket	 : WARNING: Using mc_amount3 as price as it's a subscription, instead of mc_gross.
	 * @param  boolean				$authnetSubscription   true if it is a subscription and amount is in mc_amount1 and not in mc_gross
	 * @return mixed				string of XML request 
	 */
	private function _encodeAIMPostRequest( $aimRequestType, $card, &$paymentBasket, $authnetSubscription )
	{
		$authorize_login_id			= $this->ISOtoUtf8( $this->getAccountParam( 'authorize_login_id' ) );
		$authorize_transaction_key	= $this->ISOtoUtf8( $this->getAccountParam( 'authorize_transaction_key' ) );
		$invoiceNum					= $this->ISOtoUtf8( $paymentBasket->invoice ? $paymentBasket->invoice : $paymentBasket->id );
		$refId						= $this->ISOtoUtf8( $paymentBasket->id );
		// $subscriptionName			= $this->_cbp_utf8_substr( $this->ISOtoUtf8( $paymentBasket->item_name ), 0, 20 );
		$subscriptionDescription	= $this->_cbp_utf8_substr( $this->ISOtoUtf8( $paymentBasket->item_name ), 0, 255 );
		
		//TBD Check if really needed ! $subscriptionTiming			= $this->_computeSubscriptionTiming( $paymentBasket, 'noUpfrontFirstCharge', $card );
		if ( $authnetSubscription > 1 ) {
			if ( $paymentBasket->period1 ) {
				$amount				= sprintf( '%.2f', $paymentBasket->mc_amount1 );
			} else {
				$amount				= sprintf( '%.2f', $paymentBasket->mc_amount3 );
			}
		} else {
			$amount					= sprintf( '%.2f', $paymentBasket->mc_gross );
		}
		$cardNumber					= substr( preg_replace ( '/[^0-9]+/', '', strval( $card['number'] ) ), 0, 22 );
		$cardExpirationDateMM_YYYY	= substr( sprintf( '%02d', intval( $card['expmonth'] ) ), 0, 2 ) . '-' . substr( strval( intval( $card['expyear'] ) ), 0, 4 );
		$cardCVV					= substr( preg_replace ( '/[^0-9]+/', '', strval( $card['cvv'] ) ), 0, 4 );
		if ( ! $cardCVV ) {
			$cardCVV				= '';
		}
		$firstName					= $this->_cbp_utf8_substr( $this->ISOtoUtf8( $card['firstname'] ), 0, 50 );
		$lastName					= $this->_cbp_utf8_substr( $this->ISOtoUtf8( $card['lastname'] ), 0, 50 );

		$email						= $this->_cbp_utf8_substr( $this->ISOtoUtf8( $paymentBasket->payer_email ), 0, 255 );

		$country					= ( isset( $card['country'] ) ? $this->_cbp_utf8_substr( $this->ISOtoUtf8( $card['country'] ), 0, 60 ) : null );
		$zip						= ( isset( $card['zip'] ) ? $this->_cbp_utf8_substr( $this->ISOtoUtf8( $card['zip'] ), 0, 20 ) : null );
		$address					= ( isset( $card['address'] ) ? $this->_cbp_utf8_substr( $this->ISOtoUtf8( $card['address'] ), 0, 60 ) : null );

		$ipAddressesArray			= cbpaidRequest::getIParray();
		$ipAddress					= substr( array_pop( $ipAddressesArray ), 0, 15 );			// last one is the address we get from our own (trusted) webserver
		switch ( $aimRequestType ) {
			case 'AUTH_CAPTURE':
				$authnet_values	= array
					(
						'x_version'				=> '3.1',
					 	'x_relay_response'		=> 'FALSE',
						'x_delim_data'			=> 'TRUE',
						'x_delim_char'			=> '*',
						'x_encap_char'			=> '|',
						'x_duplicate_window'	=> '28800',		// no reason to pay twice same basket => 8 hours, maximum allowed by authorize.net
						'x_recurring_billing'	=> 'NO',
					
						'x_login'				=> $authorize_login_id,
						'x_tran_key'			=> $authorize_transaction_key,
					
						'x_amount'				=> $amount,
						'x_currency_code'		=> $paymentBasket->mc_currency,
					
						'x_method'				=> 'CC',
					 	'x_card_num'			=> $cardNumber,
						'x_exp_date'			=> $cardExpirationDateMM_YYYY,
						'x_card_code'			=> $cardCVV,
					
						'x_type'				=> $aimRequestType,
					
						'x_first_name'			=> $firstName,
						'x_last_name'			=> $lastName,
						'x_email'				=> $email,
						'x_cust_id'				=> $paymentBasket->user_id,
						'x_customer_ip'			=> $ipAddress,
					/*
						'x_address'				=> '342 N. Main Street #150',
						'x_city'				=> 'Ft. Worth',
						'x_state'				=> 'TX',
						'x_zip'					=> '12345',
					*/
						'x_invoice_num'			=> $invoiceNum,
						'x_description'			=> $subscriptionDescription,
					
						'cb_custom'				=> $refId,
					);
				if ( $country !== null ) {
					$authnet_values['x_country'] =	$country;
				}
				if ( $zip !== null ) {
					$authnet_values['x_zip']	 =	$zip;
				}
				if ( $address !== null ) {
					$authnet_values['x_address'] =	$address;
				}
				break;
		
			default:
				$authnet_values					 =	array();
				break;
		}
		return $authnet_values;
	}

	/**
	 * Prepares AIM Refund request
	 *
	 * @param  string				$aimRequestType	 		CREDIT, VOID
	 * @param  cbpaidPayment		$payment		 		Payment
	 * @param  float|int            $amount                 Amount to refund
	 * @return array
	 */
	private function _encodeAIMRefundRequest( $aimRequestType, $payment, $amount )
	{
		$authorize_login_id			= $this->ISOtoUtf8( $this->getAccountParam( 'authorize_login_id' ) );
		$authorize_transaction_key	= $this->ISOtoUtf8( $this->getAccountParam( 'authorize_transaction_key' ) );

		return array
			(
				'x_version'				=> '3.1',
			 	'x_relay_response'		=> 'FALSE',
				'x_delim_data'			=> 'TRUE',
				'x_delim_char'			=> '*',
				'x_encap_char'			=> '|',
				'x_duplicate_window'	=> '28800',		// no reason to pay twice same basket => 8 hours, maximum allowed by authorize.net
				'x_recurring_billing'	=> 'NO',

				'x_login'				=> $authorize_login_id,
				'x_tran_key'			=> $authorize_transaction_key,

				'x_amount'				=> sprintf( '%.2f', $amount ),
				'x_currency_code'		=> $payment->mc_currency,

				'x_method'				=> 'CC',
				'x_trans_id'			=> $payment->txn_id,
				'x_card_num'			=> substr( $payment->payer_id, -4 ),

				'x_type'				=> $aimRequestType,
			);
	}

	/**
	 * Checks ARB silent post hash
	 *
	 * @param  array  $postdata   $_POST data to check
	 * @return bool               TRUE: hash correct, FALSE: incorrect
	 */
	private function _checkHashARBsilent( $postdata )
	{
		$amount		=	sprintf( '%0.2f', (float) cbGetParam( $postdata, 'x_amount' ) );
		$transid	=	cbGetParam( $postdata, 'x_trans_id' );
		$hash		=	cbGetParam( $postdata, 'x_MD5_Hash' );
		$mdhash		=	$this->getAccountParam( 'authorize_md_hash', '' );
		return ( md5( $mdhash . $transid . $amount ) === strtolower( $hash ) );
	}

	/**
	 * Computes payment status
	 *
	 * @param  string       $x_type
	 * @param  int          $x_response_code
	 * @return null|string
	 */
	private function _paymentStatus( $x_type, $x_response_code )
	{
		switch ( $x_response_code ) {
			case 1:
				if ( in_array( $x_type, array( 'AUTH_CAPTURE', 'CAPTURE_ONLY', 'PRIOR_AUTH_CAPTURE' ) ) ) {
					return 'Completed';
				} elseif ( $x_type == 'AUTH_ONLY' ) {
					return 'Pending';
				} elseif ( ( $x_type == 'CREDIT' ) || ( $x_type == 'VOID' ) ) {
					return 'Refunded';
				} else {
					return null;	//Unknown
				}
			case 2:
				return 'Denied';
			case 4:
				return 'Pending';
			case 3:
				return null;		// Error
			default:
				return null;		// Unknown
			break;
		}
	}

	/**
	 * Function to put into authorize.net-compatible UTF-8 the local settings
	 * (which in Joomla are UTF-8)
	 *
	 * @param  string  $string
	 * @return string
	 */
	private function ISOtoUtf8( $string )
	{
		return $string;
	}

	/**
	 * USED by XML interface ONLY !!! Renders amount
	 *
	 * @param  int     $gatewayId  Id of the gateway account
	 * @return string              HTML to display
	 */
	public function renderNotifyUrl( /** @noinspection PhpUnusedParameterInspection */ $gatewayId )
	{
		return $this->getNotifyUrl( null );
	}
}	// end class cbpaidauthorizenet.

/**
 * Payment account class for this gateway: Stores the settings for that gateway instance, and is used when editing and storing gateway parameters in the backend.
 * No methods need to be implemented or overriden in this class, except to implement the private-type params used specifically for this gateway:
 */
class cbpaidGatewayAccountauthorizenet extends cbpaidGatewayAccountCreditCards
{
	/**
	 * USED by XML interface ONLY !!! Renders URL for notifications
	 *
	 * @param  string           $gatewayId   Id of this gateway account
	 * @param  ParamsInterface  $params      CBSubs global params
	 * @return string                        HTML to display
	 */
	public function renderNotifyUrl( $gatewayId, /** @noinspection PhpUnusedParameterInspection */ &$params )
	{
		$payClass				=	$this->getPayMean();
		/** @var $payClass cbpaidauthorizenet */
		return $payClass->renderNotifyUrl( $gatewayId );
	}
}
