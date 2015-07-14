<?php
/**
* @version $Id: cbpaidsubscriptions.paypalpro.php 1581 2012-12-24 02:36:44Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Registry\ParamsInterface;

/** Ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_CB_framework;

// Avoids errors in CB plugin edit:
/** @noinspection PhpIncludeInspection */
include_once( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/cbpaidsubscriptions.class.php' );

// This gateway implements a payment handler using a on-site credit-card page:
// Import class cbpaidCreditCardsPayHandler that extends cbpaidPayHandler
// and implements all gateway-generic CBSubs methods.

/**
 * Payment handler class for this gateway: Handles all payment events and notifications, called by the parent class:
 *
 * OEM base
 * Please note that except the constructor and the API version this class does not implement any public methods.
 */
class cbpaidpaypalprooem extends cbpaidCreditCardsPayHandler
{
	/**
	 * Overrides base class with 1:
	 * Hash type: 1 = only if there is a basket id (default), 2 = always, 0 = never
	 * @var int
	 */
	protected $_urlHashType		=	1;

	/**
	 * Gateway API version used
	 * @var int
	 */
	public $gatewayApiVersion	=	"1.3.0";

	/**
	 * Constructor
	 *
	 * @param cbpaidGatewayAccount $account
	 */
	public function __construct( $account )
	{
		parent::__construct( $account );

		// Set gateway URLS for $this->pspUrl() results: first 2 are the main hosted payment page posting URL, next ones are gateway-specific:
		$this->_gatewayUrls	=	array(	'psp+normal' => $this->getAccountParam( 'psp_normal_url' ),
										'psp+test' => $this->getAccountParam( 'psp_test_url' ),
									);
	}

	/**
	 * CBSUBS PAY HANLDER API METHODS:
	 */

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

		$return								=	false;

		if ( $this->hasPaypalApi() ) {
			if ( $amount != $payment->mc_gross ) {
				$logType						=	'4';
				$paymentStatus					=	'Partially-Refunded';
				$refundType						=	'Partial';
			} else {
				$logType						=	'3';
				$paymentStatus					=	'Refunded';
				$refundType						=	'Full';
			}

			$requestParams						=	array(	'METHOD' => 'RefundTransaction',
															'TRANSACTIONID' => $payment->txn_id,
															'REFUNDTYPE' => $refundType
														);

			if ( $refundType != 'Full' ) {
				$requestParams['CURRENCYCODE']	=	$payment->mc_currency;
				$requestParams['AMT']			=	sprintf( '%.02f', $amount );
			}

			if ( $reasonText ) {
				$requestParams['NOTE']			=	$reasonText;
			}

			$this->_signRequestParams( $requestParams );

			$results							=	array();
			$response							=	null;
			$status								=	null;
			$error								=	$this->_httpsRequest( str_replace( 'www', 'api-3t', $this->gatewayUrl( 'psp' ) . '/nvp' ), $requestParams, 105, $response, $status, 'post', 'normal' );

			if ( $response ) {
				parse_str( $response, $results );
			}

			if ( $error || ( $status != 200 ) || ( ! $response ) ) {
				$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' HTTPS POST request to payment gateway server failed.', CBPTXT::T( "Submitted refund request didn't return an error but didn't complete." ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
			} else {
				if ( cbGetParam( $results, 'ACK' ) == 'Success' ) {
					if ( isset( $results['REFUNDINFO'] ) ) {
						$paymentType			=	cbGetParam( $results['REFUNDINFO'], 'REFUNDSTATUS' );
						$reasonCode				=	cbGetParam( $results['REFUNDINFO'], 'PENDINGREASON' );

						if ( $reasonCode == 'None' ) {
							$reasonCode			=	null;
						}
					} else {
						$paymentType			=	'instant';
						$reasonCode				=	null;
					}

					$ipn						=&	$this->_prepareIpn( $logType, $paymentStatus, $paymentType, $reasonCode, $_CB_framework->now(), 'utf-8' );

					$ipn->bindBasket( $paymentBasket );

					$ipn->user_id				=	(int) $paymentBasket->user_id;

					$ipn->setRawResult( 'SUCCESS' );

					$rawData					=	'$response="' . preg_replace( '/([^\s]{100})/', '$1 ', $response ) . "\"\n"
												.	'$results=' . var_export( $results, true ) . ";\n"
												.	'$requestParams=' . var_export( $requestParams, true ) . ";\n";

					$ipn->setRawData( $rawData );

					$ipn->mc_currency			=	cbGetParam( $results, 'CURRENCYCODE' );
					$ipn->mc_gross				=	( - cbGetParam( $results, 'GROSSREFUNDAMT' ) );

					$ipn->computeRefundedTax( $payment );

					$ipn->mc_fee				=	( - cbGetParam( $results, 'FEEREFUNDAMT' ) );
					$ipn->auth_id				=	cbGetParam( $results, 'CORRELATIONID' );
					$ipn->txn_id				=	cbGetParam( $results, 'REFUNDTRANSACTIONID' );
					$ipn->parent_txn_id			=	$payment->txn_id;

					$this->updatePaymentStatus( $paymentBasket, $ipn->txn_type, $ipn->payment_status, $ipn, 1, 0, 0, false );

					$ipn->store();

					$return						=	true;
				} else {
					$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' Paypal API error returned. ERROR: ' . cbGetParam( $results, 'L_LONGMESSAGE0' ) . ' CODE: ' . cbGetParam( $results, 'L_ERRORCODE0' ), cbGetParam( $results, 'L_SHORTMESSAGE0' ) . '. ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
				}
			}
		} else {
			$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' Needed Paypal API username, password and signature not set.', CBPTXT::T( 'Needed Paypal API username, password and signature not set.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
		}

		return $return;
	}

	/**
	 * CBSUBS ON-SITE CREDIT-CARDS PAGES PAYMENT API METHODS:
	 */

	/**
	 * Attempts to authorize and capture a credit card for a single payment of a payment basket
	 *
	 * @param array $card                           contains type, number, firstname, lastname, expmonth, expyear, and optionally: address, zip, country
	 * @param cbpaidPaymentBasket $paymentBasket
	 * @param int $now                              unix timestamp of now
	 * @param cbpaidsubscriptionsNotification $ipn  returns the stored notification
	 * @param boolean $authnetSubscription          true if it is a subscription and amount is in mc_amount1 and not in mc_gross
	 * @return array|bool                           subscriptionId if subscription request succeeded, otherwise ARRAY( 'level' => 'spurious' or 'fatal', 'errorText', 'errorCode' => string ) of error to display
	 */
	protected function processSinglePayment( $card, $paymentBasket, $now, &$ipn, $authnetSubscription )
	{
		if ( $this->hasPaypalApi() ) {
			$countries			=	new cbpaidCountries();

			if ( $authnetSubscription > 1 ) {
				if ( $paymentBasket->period1 ) {
					$amount		=	sprintf( '%.2f', $paymentBasket->mc_amount1 );
				} else {
					$amount		=	sprintf( '%.2f', $paymentBasket->mc_amount3 );
				}
			} else {
				$amount			=	sprintf( '%.2f', $paymentBasket->mc_gross );
			}

			$ipAddresses		=	cbpaidRequest::getIParray();

			$requestParams		=	array(	'METHOD' => 'DoDirectPayment',
											'PAYMENTACTION' => 'Sale',
											'IPADDRESS' => substr( array_pop( $ipAddresses ), 0, 15 ),
											'CREDITCARDTYPE' => cbIsoUtf_substr( $card['type'], 0, 10 ),
											'ACCT' => substr( preg_replace ( '/[^0-9]+/', '', strval( $card['number'] ) ), 0, 22 ),
											'EXPDATE' => substr( sprintf( '%02d', intval( $card['expmonth'] ) ), 0, 2 ) . substr( strval( intval( $card['expyear'] ) ), 0, 4 ),
											'CVV2' => substr( preg_replace ( '/[^0-9]+/', '', strval( $card['cvv'] ) ), 0, 4 ),
											'EMAIL' => cbIsoUtf_substr( $paymentBasket->payer_email, 0, 127 ),
											'FIRSTNAME' => cbIsoUtf_substr( $card['firstname'], 0, 25 ),
											'LASTNAME' => cbIsoUtf_substr( $card['lastname'], 0, 25 ),
											'STREET' => cbIsoUtf_substr( $paymentBasket->address_street, 0, 100 ),
											'CITY' => cbIsoUtf_substr( $paymentBasket->address_city, 0, 40 ),
											'STATE' => cbIsoUtf_substr( substr( $paymentBasket->address_state, -2 ), 0, 2 ),
											'COUNTRYCODE' => $countries->countryToTwoLetters( $paymentBasket->address_country ),
											'ZIP' => cbIsoUtf_substr( $paymentBasket->address_zip, 0, 20 ),
											'AMT' => $amount,
											'CURRENCYCODE' => $paymentBasket->mc_currency,
											'DESC' => $paymentBasket->item_name,
											'CUSTOM' => $paymentBasket->id,
											'INVNUM' => $paymentBasket->invoice,
											'NOTIFYURL' => $this->getNotifyUrl( $paymentBasket )
										);

			$this->_signRequestParams( $requestParams );

			$results			=	array();
			$response			=	null;
			$status				=	null;
			$error				=	$this->_httpsRequest( str_replace( 'www', 'api-3t', $this->gatewayUrl( 'psp' ) . '/nvp' ), $requestParams, 105, $response, $status, 'post', 'normal' );

			if ( $response ) {
				parse_str( $response, $results );
			}

			if ( $error || ( $status != 200 ) || ( ! $response ) ) {
				$return			=	array(	'level' => 'spurious',
											'errorText' => CBPTXT::T( "Submitted subscription payment didn't return an error but didn't complete." ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ),
											'errorCode' => '8888'
										);

				$logType		=	'B';
			} else {
				if ( cbGetParam( $results, 'ACK' ) == 'Success' ) {
					$return		=	cbGetParam( $results, 'TRANSACTIONID' );

					$logType	=	'P';
				} else {
					$return		=	array(	'level' => 'fatal',
											'errorText' => cbGetParam( $results, 'L_SHORTMESSAGE0' ) . '. ' . CBPTXT::T( 'Please contact site administrator to check error log.' ),
											'errorCode' => cbGetParam( $results, 'L_ERRORCODE0' )
										);

					$logType	=	'V';
				}
			}

			$ipn				=	$this->_logNotification( $logType, $now, $paymentBasket, $card, $requestParams, $response, $results, $return );
		} else {
			$return				=	array(	'level' => 'fatal',
											'errorText' => CBPTXT::T( 'Needed Paypal API username, password and signature not set.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ),
											'errorCode' => '8888'
										);
		}

		return $return;
	}

	/**
	 * Attempts to subscribe a credit card for recurring subscription of a payment basket.
	 *
	 * @param array $card                           contains type, number, firstname, lastname, expmonth, expyear, and optionally: address, zip, country
	 * @param cbpaidPaymentBasket $paymentBasket
	 * @param int $now                              unix timestamp of now
	 * @param cbpaidsubscriptionsNotification $ipn  returns the stored notification
	 * @param int $occurrences                      returns the number of occurences pay-subscribed firmly
	 * @param int $autorecurring_type               returns:  0: not auto-recurring, 1: auto-recurring without payment processor notifications, 2: auto-renewing with processor notifications updating $expiry_date
	 * @param int $autorenew_type                   returns:  0: not auto-renewing (manual renewals), 1: asked for by user, 2: mandatory by configuration
	 * @return mixed                                subscriptionId if subscription request succeeded, otherwise ARRAY( 'level' => 'inform', 'spurious' or 'fatal', 'errorText', 'errorCode' => string ) of error to display
	 */
	protected function processSubscriptionPayment( $card, $paymentBasket, $now, &$ipn, &$occurrences, &$autorecurring_type, &$autorenew_type )
	{
		$return											=	false;

		if ( $this->hasPaypalApi() ) {
			$countries									=	new cbpaidCountries();

			list( $p3, $t3, $start )					=	$this->_paypalPeriodsLimits( explode( ' ', $paymentBasket->period3 ), $now );

			if ( $paymentBasket->period1 ) {
				list( /* $p1 */, /* $t1 */, $start )	=	$this->_paypalPeriodsLimits( explode( ' ', $paymentBasket->period1 ), $now );

				$initialAmount							=	$paymentBasket->mc_amount1;
			} else {
				$initialAmount							=	$paymentBasket->mc_amount3;
			}

			$requestParams								=	array(	'METHOD' => 'CreateRecurringPaymentsProfile',
																	'SUBSCRIBERNAME' => cbIsoUtf_substr( $card['firstname'] . ' ' . $card['lastname'], 0, 32 ),
																	'PROFILESTARTDATE' => substr( date( 'c', $start ), 0, 19 ),
																	'PROFILEREFERENCE' => $paymentBasket->invoice,
																	'DESC' => cbIsoUtf_substr( $paymentBasket->item_name, 0, 127 ),
																	'BILLINGPERIOD' => $t3,
																	'BILLINGFREQUENCY' => $p3,
																	'INITAMT' => sprintf( '%.2f', $initialAmount ),
																	'AMT' => sprintf( '%.2f', $paymentBasket->mc_amount3 ),
																	'CURRENCYCODE' => $paymentBasket->mc_currency,
																	'CREDITCARDTYPE' => cbIsoUtf_substr( $card['type'], 0, 10 ),
																	'ACCT' => substr( preg_replace ( '/[^0-9]+/', '', strval( $card['number'] ) ), 0, 22 ),
																	'EXPDATE' => substr( sprintf( '%02d', intval( $card['expmonth'] ) ), 0, 2 ) . substr( strval( intval( $card['expyear'] ) ), 0, 4 ),
																	'CVV2' => substr( preg_replace ( '/[^0-9]+/', '', strval( $card['cvv'] ) ), 0, 4 ),
																	'EMAIL' => cbIsoUtf_substr( $paymentBasket->payer_email, 0, 127 ),
																	'PAYERID' => $paymentBasket->user_id,
																	'FIRSTNAME' => cbIsoUtf_substr( $card['firstname'], 0, 25 ),
																	'LASTNAME' => cbIsoUtf_substr( $card['lastname'], 0, 25 ),
																	'STREET' => cbIsoUtf_substr( $paymentBasket->address_street, 0, 100 ),
																	'CITY' => cbIsoUtf_substr( $paymentBasket->address_city, 0, 40 ),
																	'STATE' => cbIsoUtf_substr( substr( $paymentBasket->address_state, -2 ), 0, 2 ),
																	'COUNTRYCODE' => $countries->countryToTwoLetters( $paymentBasket->address_country ),
																	'ZIP' => cbIsoUtf_substr( $paymentBasket->address_zip, 0, 20 )
																);

			if ( $paymentBasket->recur_times ) {
				$requestParams['TOTALBILLINGCYCLES']	=	$paymentBasket->recur_times;
			}

			$this->_signRequestParams( $requestParams );

			$results									=	array();
			$response									=	null;
			$status										=	null;
			$error										=	$this->_httpsRequest( str_replace( 'www', 'api-3t', $this->gatewayUrl( 'psp' ) . '/nvp' ), $requestParams, 105, $response, $status, 'post', 'normal' );

			if ( $response ) {
				parse_str( $response, $results );
			}

			if ( $error || ( $status != 200 ) || ( ! $response ) ) {
				$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' HTTPS POST request to payment gateway server failed.', CBPTXT::T( "Submitted subscription payment didn't return an error but didn't complete." ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );

				$logType								=	'C';
			} else {
				if ( cbGetParam( $results, 'ACK' ) == 'Success' ) {
					$autorecurring_type					=	2;
					$autorenew_type						=	( $autorecurring_type ? ( ( $this->getAccountParam( 'enabled', 0 ) == 3 ) && ( $paymentBasket->isAnyAutoRecurring() == 2 ) ? 1 : 2 ) : 0 );

					$return								=	cbGetParam( $results, 'PROFILEID' );

					$logType							=	'A';
				} else {
					$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' Paypal API error returned. ERROR: ' . cbGetParam( $results, 'L_LONGMESSAGE0' ) . ' CODE: ' . cbGetParam( $results, 'L_ERRORCODE0' ), cbGetParam( $results, 'L_SHORTMESSAGE0' ) . '. ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );

					$logType							=	'W';
				}
			}

			$ipn										=	$this->_logNotification( $logType, $now, $paymentBasket, $card, $requestParams, $response, $results, $return );
		} else {
			$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' Needed Paypal API username, password and signature not set.', CBPTXT::T( "Submitted subscription payment didn't return an error but didn't complete." ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
		}

		return $return;
	}

	/**
	 * Attempts to unsubscribe a subscription of a payment basket.
	 *
	 * @param  cbpaidPaymentBasket              $paymentBasket
	 * @param  cbpaidPaymentItem[]              $paymentItems
	 * @param  cbpaidsubscriptionsNotification  $ipn                        returns the stored notification
	 * @param  string                           $authorize_subscription_id
	 * @return mixed                                                        subscriptionId if subscription request succeeded, otherwise ARRAY( 'level' => 'inform', 'spurious' or 'fatal', 'errorText', 'errorCode' => string ) of error to display
	 */
	protected function processSubscriptionCancellation( $paymentBasket, $paymentItems, &$ipn, $authorize_subscription_id )
	{
		global $_CB_framework;

		$return						=	false;

		if ( $this->hasPaypalApi() ) {
			$requestParams			=	array(	'METHOD' => 'ManageRecurringPaymentsProfileStatus',
												'PROFILEID' => $authorize_subscription_id,
												'ACTION' => 'Cancel'
											);

			$this->_signRequestParams( $requestParams );

			$results				=	array();
			$response				=	null;
			$status					=	null;
			$error					=	$this->_httpsRequest( str_replace( 'www', 'api-3t', $this->gatewayUrl( 'psp' ) . '/nvp' ), $requestParams, 105, $response, $status, 'post', 'normal' );

			if ( $response ) {
				parse_str( $response, $results );
			}

			if ( $error || ( $status != 200 ) || ( ! $response ) ) {
				$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' HTTPS POST request to payment gateway server failed.', CBPTXT::T( "Submitted subscription cancellation didn't return an error but didn't complete." ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );

				$logType			=	'C';
			} else {
				if ( cbGetParam( $results, 'ACK' ) == 'Success' ) {
					$return			=	cbGetParam( $results, 'PROFILEID' );

					$logType		=	'5';
				} else {
					if ( cbGetParam( $results, 'L_ERRORCODE0' ) == '11556' ) {
						$return		=	$authorize_subscription_id;

						$logType	=	'5';
					} else {
						$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' Paypal API error returned. ERROR: ' . cbGetParam( $results, 'L_LONGMESSAGE0' ) . ' CODE: ' . cbGetParam( $results, 'L_ERRORCODE0' ), cbGetParam( $results, 'L_SHORTMESSAGE0' ) . '. ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );

						$logType	=	'W';
					}
				}
			}

			$ipn					=	$this->_logNotification( $logType, $_CB_framework->now(), $paymentBasket, null, $requestParams, $response, $results, $return );
		} else {
			$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' Needed Paypal API username, password and signature not set.', CBPTXT::T( 'Needed Paypal API username, password and signature not set.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
		}

		return $return;
	}

	/**
	 * Handles a gateway notification
	 *
	 * @param cbpaidPaymentBasket $paymentBasket
	 * @param array $postdata
	 * @return bool
	 */
	protected function handleNotify( $paymentBasket, $postdata )
	{
		global $_CB_framework;

		$transactionId												=	cbGetParam( $postdata, 'txn_id', null );
		$subscriptionId												=	cbGetParam( $postdata, 'recurring_payment_id', null );
		$return														=	false;

		if ( $transactionId || $subscriptionId ) {
			if ( $this->hasPaypalApi() ) {
				$paymentStatus										=	cbGetParam( $postdata, 'payment_status', null );
				$paymentType										=	cbGetParam( $postdata, 'payment_type', null );

				$ipn												=&	$this->_prepareIpn( 'I', $paymentStatus, $paymentType, null, $_CB_framework->now(), 'utf-8' );

				if ( $subscriptionId ) {
					$exists											=	$paymentBasket->loadThisMatching( array( 'subscr_id' => $subscriptionId ) );
				} else {
					$custom											=	(int) cbGetParam( $postdata, 'custom', null );

					if ( $custom ) {
						$exists										=	$paymentBasket->load( $custom );
					} else {
						$exists										=	$paymentBasket->loadThisMatching( array( 'txn_id' => $transactionId ) );
					}
				}

				if ( $exists ) {
					$ipn->bindBasket( $paymentBasket );

					$ipn->user_id									=	(int) $paymentBasket->user_id;
				}

				$ipn->bind( $postdata );

				if ( $exists ) {
					$ipn->item_number								=	$paymentBasket->item_number;
				}

				if ( $subscriptionId ) {
					if ( ! $ipn->payment_status ) {
						$profileStatus								=	cbGetParam( $postdata, 'profile_status', null );

						if ( $profileStatus == 'Cancelled' ) {
							$ipn->payment_status					=	'Unsubscribed';
							$ipn->payment_date						=	cbGetParam( $postdata, 'time_created', null );
						} elseif ( $profileStatus == 'Active' ) {
							$ipn->payment_status					=	'Completed';
							$ipn->payment_date						=	cbGetParam( $postdata, 'time_created', null );
						} elseif ( $profileStatus == 'Expired' ) {
							$ipn->payment_status					=	'Unsubscribed';
							$ipn->payment_date						=	cbGetParam( $postdata, 'time_created', null );
						} elseif ( $profileStatus == 'Suspended' ) {
							$ipn->payment_status					=	'Denied';
							$ipn->payment_date						=	cbGetParam( $postdata, 'time_created', null );
						} elseif ( $profileStatus == 'Pending' ) {
							$ipn->payment_status					=	'Pending';
							$ipn->payment_date						=	cbGetParam( $postdata, 'time_created', null );
						}
					}

					$requestParams									=	array(	'METHOD' => 'GetRecurringPaymentsProfileDetails',
																				'PROFILEID' => $subscriptionId
																			);
				} else {
					$requestParams									=	array(	'METHOD' => 'GetTransactionDetails',
																				'TRANSACTIONID' => $transactionId
																			);
				}

				$this->_signRequestParams( $requestParams );

				$results											=	array();
				$response											=	null;
				$status												=	null;
				$error												=	$this->_httpsRequest( str_replace( 'www', 'api-3t', $this->gatewayUrl( 'psp' ) . '/nvp' ), $requestParams, 105, $response, $status, 'post', 'normal' );

				if ( $response ) {
					parse_str( $response, $results );
				}

				$rawData											=	'$response="' . preg_replace( '/([^\s]{100})/', '$1 ', $response ) . "\"\n"
																	.	'$results=' . var_export( $results, true ) . ";\n"
																	.	'$_GET=' . var_export( $_GET, true ) . ";\n"
																	.	'$_POST=' . var_export( $_POST, true ) . ";\n";

				$ipn->setRawData( $rawData );

				if ( $error || ( $status != 200 ) || ( ! $response ) ) {
					$ipn->log_type									=	'D';

					$ipn->setRawResult( 'COMMUNICATION ERROR' );

					$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' HTTPS POST request to payment gateway server failed.', CBPTXT::T( "Submitted transaction details request didn't return an error but didn't complete." ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
				} else {
					$insToIpn										=	array(	'address_street' => 'STREET',
																				'address_city' => 'CITY',
																				'address_state' => 'STATE',
																				'address_zip' => 'ZIP',
																				'address_country' => 'COUNTRY',
																				'address_country_code' => 'COUNTRYCODE',
																				'address_status' => 'ADDRESSSTATUS',
																				'first_name' => 'FIRSTNAME',
																				'last_name' => 'LASTNAME',
																				'payer_email' => 'EMAIL',
																				'payer_id' => 'PAYERID',
																				'payer_status' => 'PAYERSTATUS',
																				'auth_id' => 'CORRELATIONID',
																				'tax' => 'TAXAMT',
																				'mc_currency' => 'CURRENCYCODE',
																				'mc_fee' => 'FEEAMT',
																				'mc_gross' => 'AMT'
																			);

					if ( $ipn->payment_status == 'Refunded' ) {
						unset( $insToIpn['mc_fee'] );
						unset( $insToIpn['mc_gross'] );
					}

					foreach ( $insToIpn as $k => $v ) {
						$apiValue									=	cbGetParam( $results, $v );

						if ( $apiValue && ( ! in_array( $apiValue, array( '0.00', 'None' ) ) ) ) {
							$ipn->$k								=	$apiValue;
						}
					}

					switch ( $ipn->txn_type ) {
						case 'recurring_payment':
							$ipn->txn_type							=	'subscr_payment';
							break;
						case 'recurring_payment_profile_created':
							$ipn->txn_type							=	'subscr_signup';
							break;
						case 'recurring_payment_profile_cancel':
							$ipn->txn_type							=	'subscr_cancel';
							break;
						case 'recurring_payment_expired':
							$ipn->txn_type							=	'subscr_eot';
							break;
						case 'recurring_payment_skipped':
							$ipn->txn_type							=	'subscr_failed';
							break;
					}

					$valid											=	$this->_validateIPN( $ipn, $paymentBasket, cbGetParam( $_REQUEST, 'cbpid' ) );

					if ( $valid === true ) {
						if ( cbGetParam( $results, 'ACK' ) == 'Success' ) {
							if ( $exists ) {
								$ipn->setRawResult( 'SUCCESS' );

								if ( $ipn->txn_type != 'subscr_signup' ) {
									$autorecurring_type				=	( in_array( $ipn->txn_type, array( 'subscr_payment', 'subscr_signup', 'subscr_modify', 'subscr_eot', 'subscr_cancel', 'subscr_failed' ) ) ? 2 : 0 );
									$autorenew_type					=	( $autorecurring_type ? ( ( ( $this->getAccountParam( 'enabled', 0 ) == 3 ) && ( $paymentBasket->isAnyAutoRecurring() == 2 ) ) ? 1 : 2 ) : 0 );

									if ( $autorecurring_type && ( $ipn->txn_type == 'subscr_signup' ) && ( ( $paymentBasket->period1 ) && ( $paymentBasket->mc_amount1 == 0 ) ) && ( $ipn->payment_status == '' ) ) {
										$ipn->payment_status		=	'Completed';
									}

									if ( ( $ipn->payment_status == 'Refunded' ) && ( $paymentBasket->mc_gross != ( - $ipn->mc_gross ) ) ) {
										$ipn->payment_status		=	'Partially-Refunded';
									}

									if ( in_array( $ipn->txn_type, array( 'subscr_eot', 'subscr_cancel', 'subscr_failed' ) ) ) {
										$autorecurring_type			=	0;
									}

									$this->updatePaymentStatus( $paymentBasket, $ipn->txn_type, $ipn->payment_status, $ipn, 1, $autorecurring_type, $autorenew_type, false );

									$return							=	true;

									$ipn->store();
								} else {
									$return							=	null;
								}
							} else {
								$ipn->log_type						=	'J';

								$ipn->setRawResult( 'FAILED' );
							}
						} else {
							$ipn->log_type							=	'M';

							$ipn->setRawResult( 'FAILED' );

							$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' Paypal API error returned. ERROR: ' . cbGetParam( $results, 'L_LONGMESSAGE0' ) . ' CODE: ' . cbGetParam( $results, 'L_ERRORCODE0' ), cbGetParam( $results, 'L_SHORTMESSAGE0' ) . '. ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
						}
					} else {
						$ipn->log_type								=	'O';

						$ipn->setRawResult( 'MISMATCH' );

						$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' Paypal IPN fraud attempt. ERROR: ' . $valid, CBPTXT::T( 'Invalid transaction.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
					}
				}
			} else {
				$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' Needed Paypal API username, password and signature not set.' . "\n" . '$_GET=' . var_export( $_GET, true ) . "\n" . '$_POST=' . var_export( $_POST, true ) . "\n", CBPTXT::T( 'Needed Paypal API username, password and signature not set.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
			}
		} else {
			$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' Needed Paypal Transaction ID (txn_id) missing.' . "\n" . '$_GET=' . var_export( $_GET, true ) . "\n" . '$_POST=' . var_export( $_POST, true ) . "\n", CBPTXT::T( 'Transaction not found.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
		}

		return $return;
	}

	/**
	 * PRIVATE METHODS OF THIS CLASS
	 */

	/**
	 * Checks if a Paypal API is set
	 *
	 * @return bool
	 */
	private function hasPaypalApi( )
	{
		return ( $this->getAccountParam( 'paypal_api_username' ) && $this->getAccountParam( 'paypal_api_password' ) && $this->getAccountParam( 'paypal_api_signature' ) );
	}

	/**
	 * perform anti fraud checks on ipn values
	 *
	 * @param cbpaidPaymentNotification $ipn
	 * @param cbpaidPaymentBasket $paymentBasket
	 * @param string $cbpid
	 * @return bool|string
	 */
	private function _validateIPN( $ipn, $paymentBasket, $cbpid )
	{
		global $_CB_database;

		$matching						=	true;

		if ( in_array( $ipn->payment_status, array( 'Completed', 'Processed', 'Canceled_Reversal' ) ) ) {
			if ( in_array( $ipn->txn_type, array( 'subscr_payment', 'subscr_signup' ) ) ) {
				$payments				=	$paymentBasket->getPaymentsTotals( $ipn->txn_id );

				if ( ( $paymentBasket->mc_amount1 != 0 ) && ( $payments->count == 0 ) ) {
					$amount				=	$paymentBasket->mc_amount1;
				} else {
					$amount				=	$paymentBasket->mc_amount3;
				}

				if ( sprintf( '%.2f', $ipn->mc_gross ) != sprintf( '%.2f', $amount ) ) {
					if ( ( sprintf( '%.2f', $ipn->mc_gross ) < sprintf( '%.2f', $amount ) ) || ( sprintf( '%.2f', ( $ipn->mc_gross - $ipn->tax ) ) != sprintf( '%.2f', $amount ) ) ) {
						if ( ( ! ( ( $paymentBasket->mc_amount1 != 0 ) && ( $payments->count == 0 ) ) ) && ( ( (float) sprintf( '%.2f', ( $ipn->mc_gross - abs( $ipn->tax ) ) ) ) < ( (float) sprintf( '%.2f', $amount ) ) ) ) {
							$matching	=	CBPTXT::P( 'amount mismatch on recurring_payment: $amount: [amount] != IPN mc_gross: [gross] or IPN mc_gross - IPN tax: [net] where IPN tax = [tax]', array( '[amount]' => $amount, '[net]' => ( $ipn->mc_gross - $ipn->tax ), '[gross]' => $ipn->mc_gross, '[tax]' => $ipn->tax ) );
						}
					}
				}
			} else {
				if ( sprintf( '%.2f', $ipn->mc_gross ) != sprintf( '%.2f', $paymentBasket->mc_gross ) ) {
					if ( ( sprintf( '%.2f', $ipn->mc_gross ) < sprintf( '%.2f', $paymentBasket->mc_gross ) ) || ( sprintf( '%.2f', $ipn->mc_gross - $ipn->tax ) != sprintf( '%.2f', $paymentBasket->mc_gross ) ) ) {
						$matching		=	CBPTXT::P( 'amount mismatch on webaccept: BASKET mc_gross: [basket_gross] != IPN mc_gross: [gross] or IPN mc_gross - IPN tax: [net] where IPN tax = [tax]', array( '[basket_gross]' => $paymentBasket->mc_gross, '[net]' => ( $ipn->mc_gross - $ipn->tax ), '[gross]' => $ipn->mc_gross, '[tax]' => $ipn->tax ) );
					}
				}
			}
		}

		if ( in_array( $ipn->txn_type, array( 'subscr_payment', 'subscr_signup', 'subscr_cancel', 'subscr_eot', 'subscr_failed' ) ) ) {
			if ( ! $paymentBasket->isAnyAutoRecurring() ) {
				$matching				=	CBPTXT::P( 'paypal subscription IPN type [txn_type] for a basket without auto-recurring items', array( '[txn_type]' => $ipn->txn_type ) );
			}
		}

		if ( ! in_array( $ipn->txn_type, array( 'subscr_signup', 'subscr_cancel', 'subscr_eot', 'subscr_failed' ) ) ) {
			if ( ( $ipn->txn_id === '' ) || ( $ipn->txn_id === 0 ) || ( $ipn->txn_id === null ) ) {
				$matching				=	CBPTXT::T( 'illegal transaction id' );
			} else {
				$countBaskets			=	$paymentBasket->countRows( "txn_id = '" . $_CB_database->getEscaped( $ipn->txn_id ) . "' AND payment_status = 'Completed'" );

				if ( ( $countBaskets == 1 ) && ( $paymentBasket->txn_id != $ipn->txn_id ) || ( $countBaskets > 1 ) ) {
					$matching			=	CBPTXT::P( 'transaction already used for [count] other already completed payment(s)', array( '[count]' => $countBaskets ) );
				}
			}
		}

		if ( $cbpid && ( $cbpid != $paymentBasket->shared_secret ) ) {
			$matching					=	CBPTXT::P( 'shared secret [cbpid] returned by Paypal does not match the value we expected', array( '[cbpid]' => htmlspecialchars( $cbpid ) ) );
		}

		return $matching;
	}

	/**
	 * Logs payment notification
	 *
	 * @param string $logType
	 * @param int $now
	 * @param cbpaidPaymentBasket $paymentBasket
	 * @param array|null $card
	 * @param array $request
	 * @param null|string $response
	 * @param array $results
	 * @param null|string|array $return
	 * @return cbpaidPaymentNotification
	 */
	private function _logNotification( $logType, $now, $paymentBasket, $card, $request, $response, $results, $return )
	{
		$paymentType											=	( $card ? $card['type'] . ' Credit Card' : null );

		if ( is_string( $return ) ) {
			$transactionId										=	$return;
			$paymentStatus										=	'Completed';
			$reason												=	null;
			$rawResult											=	'SUCCESS';

			if ( $logType == '5' ) {
				$paymentStatus									=	'Unsubscribed';
			}
		} else {
			$transactionId										=	null;
			$paymentStatus										=	'Denied';
			$reason												=	( isset( $return['errorCode'] ) ? $return['errorCode'] : null );
			$rawResult											=	'FAILED';
		}

		$ipn													=&	$this->_prepareIpn( $logType, $paymentStatus, $paymentType, $reason, $now, 'utf-8' );

		$ipn->bindBasket( $paymentBasket );

		$ipn->user_id											=	(int) $paymentBasket->user_id;
		$ipn->auth_id											=	cbGetParam( $results, 'CORRELATIONID' );

		if ( isset( $request['ACCT'] ) ) {
			$request['ACCT']									=	'XXXX XXXX XXXX ' . substr( $request['ACCT'], -4, 4 );
		}

		if ( isset( $request['CVV2'] ) ) {
			$request['CVV2']									=	'XXX';
		}

		if ( isset( $request['USER'] ) ) {
			unset( $request['USER'] );
		}

		if ( isset( $request['PWD'] ) ) {
			unset( $request['PWD'] );
		}

		if ( isset( $request['SIGNATURE'] ) ) {
			unset( $request['SIGNATURE'] );
		}

		$legalCCStore											=	/* cbGetParam not needed, we want raw log here! */ $_POST;

		if ( isset( $legalCCStore[$this->_getPagingParamName('number')] ) ) {
			$legalCCStore[$this->_getPagingParamName('number')]	=	'XXXX XXXX XXXX ' . substr( trim( $legalCCStore[$this->_getPagingParamName('number')] ), -4, 4 );
		}

		if ( isset( $legalCCStore[$this->_getPagingParamName('cvv')] ) ) {
			$legalCCStore[$this->_getPagingParamName('cvv')]	= 'XXX';
		}

		if ( $card ) {
			$ipn->setPayerNameId( $card['firstname'], $card['lastname'], $legalCCStore[$this->_getPagingParamName('number')] );
		} else {
			$ipn->setPayerNameId( $paymentBasket->first_name, $paymentBasket->last_name );
		}

		$ipn->setRawResult( $rawResult );

		$rawData												=	'$response="' . preg_replace( '/([^\s]{100})/', '$1 ', $response ) . "\"\n"
																.	'$results=' . var_export( $results, true ) . ";\n"
																.	'$return=' . var_export( $return, true ) . ";\n"
																.	'$request=' . var_export( $request, true ) . ";\n"
																.	'$_POST=' . var_export( $legalCCStore, true ) . ";\n";

		$ipn->setRawData( $rawData );

		if ( in_array( $logType, array( 'P', 'B', 'Q', 'V', 'X' ) ) ) {
			$ipn->setTxnSingle( $transactionId );
		} elseif ( in_array( $logType, array( 'A', 'C', 'Z', 'U', 'Y', 'W', '5' ) ) ) {
			if ( ! $ipn->txn_id ) {
				$ipn->txn_id									=	cbGetParam( $results, 'TRANSACTIONID' );
			}

			if ( ! $paymentBasket->subscr_id ) {
				$firstPayment									=	true;
			} else {
				$firstPayment									=	false;
			}

			$ipn->setTxnSubscription( $paymentBasket, $transactionId, $now );

			if ( $logType == '5' ) {
				$ipn->txn_type									=	'subscr_cancel';
			} else {
				if ( $firstPayment ) {
					$ipn->txn_type								=	'subscr_signup';
				}
			}

			if ( $transactionId ) {
				$this->_bindNotificationToBasket( $ipn, $paymentBasket );
			}
		}

		$ipn->store();

		return $ipn;
	}

	/**
	 * sign payment request $requestParams with api access added to $requestParams array
	 *
	 * @param array $requestParams
	 */
	private function _signRequestParams( &$requestParams )
	{
		if ( $this->hasPaypalApi() ) {
			$requestParams['VERSION']	=	'97.0'; // October 2012 - https://cms.paypal.com/cms_content/US/en_US/files/developer/PP_NVPAPI_DeveloperGuide.pdf
			$requestParams['USER']		=	$this->getAccountParam( 'paypal_api_username' );
			$requestParams['PWD']		=	$this->getAccountParam( 'paypal_api_password' );
			$requestParams['SIGNATURE']	=	$this->getAccountParam( 'paypal_api_signature' );
		}
	}

	/**
	 * Limits and converts periods to Paypal limits
	 *
	 * @param array $periodTypeArray  ( int $value, string $periodCOde ) : $periodCode: 'Day','Week','Month','Year'
	 * @param int $now                unix timestamp of now
	 * @return array                  same encoding, but limited
	 */
	private function _paypalPeriodsLimits( $periodTypeArray, $now )
	{
		$p			=	$periodTypeArray[0];
		$t			=	$periodTypeArray[1];
		$s			=	$now;

		if ( $t == 'D' ) {
			$t		=	'Day';
			$s		=	strtotime( '+' . intval( $p ) . ' DAY', $now );

			if ( $p > 90 ) {
				$t	=	'W';
				$p	=	floor( $p / 7 );
			}
		}

		if ( $t == 'W' ) {
			$t		=	'Week';
			$s		=	strtotime( '+' . intval( $p ) . ' WEEK', $now );

			if ( $p > 52 ) {
				$t	=	'M';
				$p	=	floor( $p * 12 / 52 );
			}
		}

		if ( $t == 'M' ) {
			$t		=	'Month';
			$s		=	strtotime( '+' . intval( $p ) . ' MONTH', $now );

			if ( $p > 24 ) {
				$t	=	'Y';
				$p	=	floor( $p / 12 );
			}
		}

		if ( $t == 'Y' ) {
			$t		=	'Year';

			if ( $p > 5 ) {
				$p	=	5;
			}

			$s		=	strtotime( '+' . intval( $p ) . ' YEAR', $now );
		}

		return array( $p, $t, $s );
	}

	/**
	 * FUNCTIONS FOR BACKEND INTERFACE:
	 */

	/**
	 * Renders URL to set in the gateway interface for notifications
	 *
	 * @param string $urlType
	 * @return string
	 */
	public function adminUrlRender( $urlType )
	{
		switch ( $urlType ) {
			case 'successurl':
				return $this->getSuccessUrl( null );
				break;
			case 'cancelurl':
				return $this->getCancelUrl( null );
				break;
			case 'notifyurl':
				return $this->getNotifyUrl( null );
				break;
			default:
		}

		return 'Error: Unknown url type: ' . htmlspecialchars( $urlType );
	}
}

/**
 * Payment account class for this gateway: Stores the settings for that gateway instance, and is used when editing and storing gateway parameters in the backend.
 *
 * OEM base
 * No methods need to be implemented or overriden in this class, except to implement the private-type params used specifically for this gateway:
 */
class cbpaidGatewayAccountpaypalprooem extends cbpaidGatewayAccountCreditCards
{
	/**
	 * USED by XML interface ONLY !!! Renders URL for successful returns
	 *
	 * @param  string              $value   Variable value ( 'successurl', 'cancelurl', 'notifyurl' )
	 * @param  ParamsInterface     $params
	 * @param  string              $name    The name of the form element
	 * @param  CBSimpleXMLElement  $node    The xml element for the parameter
	 * @return string                       HTML to display
	 */
	public function renderUrl( /** @noinspection PhpUnusedParameterInspection */ $value, $params, $name, $node )
	{
		/** @noinspection PhpUndefinedMethodInspection */
		return str_replace( 'http://', 'https://', $this->getPayMean()->adminUrlRender( $node->attributes( 'value' ) ) );
	}
}

/**
 * Payment handler class for this gateway: Handles all payment events and notifications, called by the parent class:
 *
 * Gateway-specific
 * Please note that except the constructor and the API version this class does not implement any public methods.
 */
class cbpaidpaypalpro extends cbpaidpaypalprooem
{
}

/**
 * Payment account class for this gateway: Stores the settings for that gateway instance, and is used when editing and storing gateway parameters in the backend.
 *
 *
 * Gateway-specific
 * No methods need to be implemented or overriden in this class, except to implement the private-type params used specifically for this gateway:
 */
class cbpaidGatewayAccountpaypalpro extends cbpaidGatewayAccountpaypalprooem
{
}
