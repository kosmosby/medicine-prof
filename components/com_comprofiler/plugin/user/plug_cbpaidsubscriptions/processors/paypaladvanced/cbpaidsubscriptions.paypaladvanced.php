<?php
/**
* @version $Id: cbpaidsubscriptions.paypaladvanced.php 2781 2013-02-22 17:05:07Z kyle $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/** Ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_CB_framework;

// Avoids errors in CB plugin edit:
/** @noinspection PhpIncludeInspection */
include_once( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/cbpaidsubscriptions.class.php' );
/** @noinspection PhpIncludeInspection */
include_once( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/cbpaidsubscriptions.pay.php' );

// This gateway implements a payment handler using a hosted page at the PSP:
// Import class cbpaidHostedPagePayHandler that extends cbpaidPayHandler
// and implements all gateway-generic CBSubs methods:
cbpaidApp::import( 'hostedpage' );

/**
 * Payment handler class for this gateway: Handles all payment events and notifications, called by the parent class:
 *
 * OEM base
 * Please note that except the constructor and the API version this class does not implement any public methods.
 */
class cbpaidpaypaladvancedoem extends cbpaidHostedPagePayHandler
{
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
										'link+normal' => $this->getAccountParam( 'psp_link_url' ),
										'link+test' => $this->getAccountParam( 'psp_link_test_url' ),
									);
	}

	/**
	 * CBSUBS HOSTED PAGE PAYMENT API METHODS:
	 */

	/**
	 * Returns single payment request parameters for gateway depending on basket (without specifying payment type)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket   paymentBasket object
	 * @return array                                 Returns array $requestParams
	 */
	protected function getSinglePaymentRequstParams( $paymentBasket )
	{
		$return				=	false;

		if ( in_array( $this->getAccountParam( 'template_layout', 'MINLAYOUT' ), array( 'MINLAYOUT', 'MOBILE' ) ) ) {
			$return			=	array(	'amount' => sprintf( '%0.2f', $paymentBasket->mc_gross ),
										'currency_code' => $paymentBasket->mc_currency,
										'custom' => $paymentBasket->id
									);
		} else {
			if ( $this->hasPaypalPayflow() ) {
				$return		=	$this->_payflowPayment( $paymentBasket );
			} else {
				$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' Needed Paypal Payflow vendor, user and password not set.', CBPTXT::T( 'Needed Paypal Payflow vendor, user and password not set.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
			}
		}

		return $return;
	}

	/**
	 * Optional function: only needed for recurring payments:
	 * Returns subscription request parameters for gateway depending on basket (without specifying payment type)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket   paymentBasket object
	 * @return array                                 Returns array $requestParams
	 */
	protected function getSubscriptionRequstParams( $paymentBasket )
	{
		$return				=	false;

		if ( in_array( $this->getAccountParam( 'template_layout', 'MINLAYOUT' ), array( 'MINLAYOUT', 'MOBILE' ) ) ) {
			$return			=	array(	'amount' => sprintf( '%0.2f', $paymentBasket->mc_gross ),
										'currency_code' => $paymentBasket->mc_currency,
										'custom' => $paymentBasket->id,
										'subscription' => true
									);
		} else {
			if ( $this->hasPaypalPayflow() ) {
				$return		=	$this->_payflowPayment( $paymentBasket, true );
			} else {
				$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' Needed Paypal Payflow vendor, user and password not set.', CBPTXT::T( 'Needed Paypal Payflow vendor, user and password not set.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
			}
		}

		return $return;
	}

	/**
	* Handles the gateway-specific result of payments (redirects back to this site and gateway notifications). WARNING: unchecked access !
	*
	* @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	* @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	* @param  string               $result         result= get parameter, other than 'notify', 'success' or 'cancel'.
	* @return string                               HTML to display if frontend, text to return to gateway if notification, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	*/
	protected function handleOtherResult( $paymentBasket, $postdata, $result )
	{
		$return							=	null;

		if ( $result == 'payment' ) {
			$paymentBasketId			=	(int) cbGetParam( $postdata, 'custom', 0 );
			$exists						=	$paymentBasket->load( (int) $paymentBasketId );

			if ( $exists && ( sprintf( '%.2f', $paymentBasket->mc_gross ) == cbGetParam( $postdata, 'amount' ) ) && ( $paymentBasket->mc_currency == cbGetParam( $postdata, 'currency_code' ) ) ) {
				if ( $this->hasPaypalPayflow() ) {
					$requestParams		=	$this->_payflowPayment( $paymentBasket, cbGetParam( $postdata, 'subscription', false ) );

					$return				=	$paymentBasket->displayBasket()
										.	'<iframe src="' . $this->gatewayUrl( 'link' ) . '?' . http_build_query( $requestParams ) . '" name="paypalAdvFrame" scrolling="no" width="100%" height="600px" frameborder="0"></iframe>';
				} else {
					$this->_setLogErrorMSG( 3, $paymentBasket, $this->getPayName() . ' payment Error: Payflow not configured', CBPTXT::T( 'Payment basket can not complete.' ));
				}
			} else {
				$this->_setErrorMSG( CBPTXT::T( 'Payment basket does not login.' ) );

				$return					=	false;
			}
		}

		return $return;
	}


	/**
	 * The user got redirected back from the payment service provider with a success message: Let's see how successfull it was
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	 * @return string                               HTML to display if frontend, FALSE if XML error (and not yet ErrorMSG generated), or NULL if nothing to display
	 */
	protected function handleReturn( $paymentBasket, $postdata )
	{
		if ( ( count( $postdata ) > 0 ) && isset( $postdata['SECURETOKENID'] ) ) {
			// we prefer POST for sensitive data:
			$requestdata		=	$postdata;
		} else {
			// but if customer needs GET, we will work with it too (removing CMS/CB/CBSubs specific routing params):
			$requestdata		=	$this->_getGetParams();
		}

		return $this->_returnParamsHandler( $paymentBasket, $requestdata, 'R' );
	}

	/**
	 * The user cancelled his payment
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	 * @return string                               HTML to display, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	 */
	protected function handleCancel( $paymentBasket, $postdata )
	{
		// The user cancelled his payment (and registration):
		if ( $this->hashPdtBackCheck( $this->_getReqParam( 'pdtback' ) ) ) {
			$paymentBasketId					=	(int) $this->_getReqParam( 'basket' );

			// check if cancel was from gateway:
			if ( ! $paymentBasketId ) {
				$paymentBasketId				=	(int) cbGetParam( $_REQUEST, 'USER1', null );
			}

			$exists								=	$paymentBasket->load( (int) $paymentBasketId );

			if ( $exists && ( $paymentBasket->payment_status != 'Completed' ) ) {
				$paymentBasket->payment_status	=	'RedisplayOriginalBasket';

				$this->_setErrorMSG( CBPTXT::T( 'Payment cancelled.' ) );
			}
		}

		return false;
	}

	/**
	 * The payment service provider server did a server-to-server notification: Verify and handle it here:
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	 * @return string                              Text to return to gateway if notification, or NULL if nothing to display
	 */
	protected function handleNotification( $paymentBasket, $postdata )
	{
		if ( ( count( $postdata ) > 0 ) && isset( $postdata['SECURETOKENID'] ) ) {
			// we prefer POST for sensitive data:
			$requestdata		=	$postdata;
		} else {
			// but if gateway needs GET, we will work with it too:
			$requestdata		=	$this->_getGetParams();
		}

		$this->_returnParamsHandler( $paymentBasket, $requestdata, 'I' );
	}

	/**
	* Cancels an existing recurring subscription
	*
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @param  cbpaidPaymentItem[]  $paymentItems   redirect immediately instead of returning HTML for output
	 * @return boolean|string                       TRUE if unsubscription done successfully, STRING if error
	*/
	protected function handleStopPaymentSubscription( $paymentBasket, $paymentItems )
	{
		global $_CB_framework;

		$return										=	false;

		if ( $paymentBasket->mc_amount3 ) {
			$subscriptionId							=	$paymentBasket->subscr_id;

			if ( $subscriptionId ) {
				if ( $this->hasPaypalPayflow() ) {
					$request						=	array(	'PARTNER' => 'PayPal',
																'VENDOR' => $this->getAccountParam( 'paypal_payflow_vendor' ),
																'USER' => $this->getAccountParam( 'paypal_payflow_user' ),
																'PWD' => $this->getAccountParam( 'paypal_payflow_password' ),
																'TENDER' => $this->_getPaymentMethod( $paymentBasket->payment_method ),
																'TRXTYPE' => 'R',
																'ACTION' => 'C',
																'ORIGPROFILEID' => $subscriptionId
															);

					$formUrl						=	array();

					foreach ( $request as $k => $v ) {
						$formUrl[$k]				=	$k . '=' . $v;
					}

					$formUrl						=	implode( '&', $formUrl );

					$results						=	array();
					$response						=	null;
					$status							=	null;
					$error							=	$this->_httpsRequest( $this->gatewayUrl( 'psp' ), $formUrl, 105, $response, $status, 'post', 'normal' );

					if ( $response ) {
						parse_str( $response, $results );
					}

					if ( $error || ( $status != 200 ) || ( ! $response ) ) {
						$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' HTTPS POST request to payment gateway server failed.', CBPTXT::T( "Submitted subscription cancellation didn't return an error but didn't complete." ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
					} else {
						if ( cbGetParam( $results, 'RESULT' ) == 0 ) {
							$ipn					=&	$this->_prepareIpn( 'R', $paymentBasket->payment_status, $paymentBasket->payment_type, 'Unsubscribe', $_CB_framework->now(), 'utf-8' );
							$ipn->test_ipn			=	$paymentBasket->test_ipn;
							$ipn->raw_result		=	'SUCCESS';
							$ipn->raw_data			=	'$message_type="STOP_PAYMENT_SUBSCRIPTION"' . ";\n"
													.	/* cbGetParam() not needed: we want raw info */ '$xml_response=' . $response . ";\n"
													.	/* cbGetParam() not needed: we want raw info */ '$_GET=' . var_export( $_GET, true ) . ";\n"
													.	/* cbGetParam() not needed: we want raw info */ '$_POST=' . var_export( $_POST, true ) . ";\n";

							$ipn->bindBasket( $paymentBasket );

							$bskToIpn				=	array(	'sale_id' => 'sale_id',
																'txn_id' => 'txn_id',
																'subscr_id' => 'subscr_id',
																'user_id' => 'user_id'
															);

							foreach ( $bskToIpn as $k => $v ) {
								$ipn->$k			=	$paymentBasket->$v;
							}

							$ipn->txn_type			=	'subscr_cancel';

							if ( ! $ipn->store() ) {
								$this->_setLogErrorMSG( 3, null, $this->getPayName() . ': unsubscribe IPN failed to store', CBPTXT::T( 'Submitted unsubscription failed on-site.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
							} else {
								$return				=	true;
							}
						} else {
							$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' Paypal Payflow error returned. ERROR: ' . cbGetParam( $results, 'RESPMSG' ), CBPTXT::T( 'Please contact site administrator to check error log.' ) );
						}
					}
				} else {
					$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' Needed Paypal Payflow vendor, user and password not set.', CBPTXT::T( 'Needed Paypal Payflow vendor, user and password not set.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
				}
			} else {
				$this->_setLogErrorMSG( 3, null, $this->getPayName() . ': unsubscribe failed from missing subscr_id in payment basket', CBPTXT::T( 'Submitted unsubscription failed on-site.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
			}
		}

		return $return;
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

		$return									=	false;

		if ( $this->hasPaypalPayflow() ) {
			if ( $amount != $payment->mc_gross ) {
				$logType						=	'4';
				$paymentStatus					=	'Partially-Refunded';
				$refundType						=	'Partial';
			} else {
				$logType						=	'3';
				$paymentStatus					=	'Refunded';
				$refundType						=	'Full';
			}

			$request							=	array(	'PARTNER' => 'PayPal',
															'VENDOR' => $this->getAccountParam( 'paypal_payflow_vendor' ),
															'USER' => $this->getAccountParam( 'paypal_payflow_user' ),
															'PWD' => $this->getAccountParam( 'paypal_payflow_password' ),
															'TENDER' => $this->_getPaymentMethod( $payment->payment_method ),
															'TRXTYPE' => 'C',
															'ORIGID' => $payment->txn_id
														);

			if ( $refundType != 'Full' ) {
				$request['AMT']					=	sprintf( '%.02f', $amount );
			}

			if ( $reasonText ) {
				$request['COMMENT1']			=	$reasonText;
			}

			$formUrl							=	array();

			foreach ( $request as $k => $v ) {
				$formUrl[$k]					=	$k . '=' . $v;
			}

			$formUrl							=	implode( '&', $formUrl );

			$results							=	array();
			$response							=	null;
			$status								=	null;
			$error								=	$this->_httpsRequest( $this->gatewayUrl( 'psp' ), $formUrl, 105, $response, $status, 'post', 'normal' );

			if ( $response ) {
				parse_str( $response, $results );
			}

			if ( $error || ( $status != 200 ) || ( ! $response ) ) {
				$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' HTTPS POST request to payment gateway server failed.', CBPTXT::T( "Submitted refund request didn't return an error but didn't complete." ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
			} else {
				if ( cbGetParam( $results, 'RESULT' ) == 0 ) {
					$paymentType				=	'Instant';
					$reasonCode					=	null;

					$ipn						=&	$this->_prepareIpn( $logType, $paymentStatus, $paymentType, $reasonCode, $_CB_framework->now(), 'utf-8' );

					$ipn->bindBasket( $paymentBasket );

					$ipn->user_id				=	(int) $paymentBasket->user_id;

					$ipn->setRawResult( 'SUCCESS' );

					$request['PWD']				=	'********';

					$rawData					=	'$response="' . preg_replace( '/([^\s]{100})/', '$1 ', $response ) . "\"\n"
												.	'$results=' . var_export( $results, true ) . ";\n"
												.	'$requestParams=' . var_export( $request, true ) . ";\n";

					$ipn->setRawData( $rawData );

					$ipn->mc_currency			=	$payment->mc_currency;
					$ipn->mc_gross				=	( - $amount );

					$ipn->computeRefundedTax( $payment );

					$ipn->auth_id				=	cbGetParam( $results, 'CORRELATIONID' );
					$ipn->txn_id				=	cbGetParam( $results, 'PNREF' );
					$ipn->parent_txn_id			=	$payment->txn_id;

					$this->updatePaymentStatus( $paymentBasket, $ipn->txn_type, $ipn->payment_status, $ipn, 1, 0, 0, false );

					$ipn->store();

					$return						=	true;
				} else {
					$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' Paypal Payflow error returned. ERROR: ' . cbGetParam( $results, 'RESPMSG' ), CBPTXT::T( 'Please contact site administrator to check error log.' ) );
				}
			}
		} else {
			$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' Needed Paypal Payflow vendor, user and password not set.', CBPTXT::T( 'Needed Paypal Payflow vendor, user and password not set.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
		}

		return $return;
	}

	/**
	 * gives gateway button URL server name from gateway URL list
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @param  boolean              $autoRecurring   TRUE: autorecurring payment, FALSE: single payment
	 * @return string  server-name (with 'https://' )
	 */
	protected function pspUrl( $paymentBasket, $autoRecurring )
	{
		return $this->pspRedirectUrl( $paymentBasket, array(), $autoRecurring );
	}

	/**
	 * Returns https redirect URL for redirections to gateway for payment with $requestParams
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @param  array                $requestParams
	 * @param  boolean              $autoRecurring   TRUE: autorecurring payment, FALSE: single payment
	 * @return string                                Full URL for redirect including https:// in front
	 */
	protected function pspRedirectUrl( $paymentBasket, $requestParams, $autoRecurring )
	{
		if ( in_array( $this->getAccountParam( 'template_layout', 'MINLAYOUT' ), array( 'MINLAYOUT', 'MOBILE' ) ) ) {
			$return		=	$this->cbsubsGatewayUrl( 'payment', null, $paymentBasket, $requestParams, false );
		} else {
			$return		=	$this->gatewayUrl( 'link' );
		}

		return $return;
	}

	/**
	 * The user got redirected back from the payment service provider with a success message: let's see how successfull it was
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket       New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $requestdata         Data returned by gateway
	 * @param  string               $type                Type of return ('R' for PDT, 'I' for INS, 'A' for Autorecurring payment (Vault) )
     * @param  array                $additionalLogData   Additional strings to log with IPN
	 * @return string                                    HTML to display if frontend, text to return to gateway if notification, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	 */
	private function _returnParamsHandler( $paymentBasket, $requestdata, $type, $additionalLogData = null )
	{
		global $_CB_framework, $_GET, $_POST;

		$ret													=	null;
		$paymentBasketId										=	(int) cbGetParam( $requestdata, 'USER1', null );

		if ( $paymentBasketId ) {
			$exists												=	$paymentBasket->load( (int) $paymentBasketId );

			if ( $exists && ( ( cbGetParam( $requestdata, $this->_getPagingParamName( 'id' ), 0 ) == $paymentBasket->shared_secret ) && ( ! ( ( ( $type == 'R' ) || ( $type == 'I' ) ) && ( $paymentBasket->payment_status == 'Completed' ) ) ) ) ) {
				// Log the return record:
				$log_type										=	$type;
				$reason											=	null;
				$paymentStatus									=	$this->_mapPaymentStatus( $requestdata, $reason );
				$paymentType									=	$this->_getPaymentType( $requestdata );
				$paymentTime									=	$_CB_framework->now();

				if ( $paymentStatus == 'Error' ) {
					$errorTypes									=	array( 'I' => 'D', 'R' => 'E', '3' => 'V', '4' => 'V' );

					if ( isset( $errorTypes[$type] ) ) {
						$log_type								=	$errorTypes[$type];
					}
				}

				$ipn											=&	$this->_prepareIpn( $log_type, $paymentStatus, $paymentType, $reason, $paymentTime, 'utf-8' );

				if ( $paymentStatus == 'Refunded' ) {
					// in case of refund we need to log the payment as it has same TnxId as first payment: so we need payment_date for discrimination:
					$ipn->payment_date							=	date( 'H:i:s M d, Y T', $paymentTime ); // paypal-style
				}

				$ipn->test_ipn									=	( $this->getAccountParam( 'normal_gateway' ) == '0' ? 1 : 0 );
				$ipn->raw_data									=	'$message_type="' . ( $type == 'R' ? 'RETURN_TO_SITE' : ( $type == 'I' ? 'NOTIFICATION' : ( $type == '3' ? 'REFUND' : ( $type == '4' ? 'PARTIAL_REFUND' : 'UNKNOWN' ) ) ) ) . '";' . "\n";

				if ( $additionalLogData ) {
					foreach ( $additionalLogData as $k => $v ) {
						$ipn->raw_data							.=	'$' . $k . '="' . var_export( $v, true ) . '";' . "\n";
					}
				}

				$ipn->raw_data									.=	/* cbGetParam() not needed: we want raw info */ '$requestdata=' . var_export( $requestdata, true ) . ";\n"
																.	/* cbGetParam() not needed: we want raw info */ '$_GET=' . var_export( $_GET, true ) . ";\n"
																.	/* cbGetParam() not needed: we want raw info */ '$_POST=' . var_export( $_POST, true ) . ";\n";

				if ( $paymentStatus == 'Error' ) {
					$paymentBasket->reason_code					=	$reason;

					$this->_storeIpnResult( $ipn, 'ERROR:' . $reason );
					$this->_setLogErrorMSG( 4, $ipn, $this->getPayName() . ': ' . $reason, CBPTXT::T( 'Sorry, the payment server replied with an error.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check payment status and error log.' ) );

					$ret										=	false;
				} else {
					$ipn->bindBasket( $paymentBasket );

					$ipn->sale_id								=	$paymentBasketId;

					$insToIpn									=	array(	'sale_id' => 'PPREF',
																			'txn_id' => 'PNREF',
																			'first_name' => 'FIRSTNAME',
																			'last_name' => 'LASTNAME',
																			'address_street' => 'ADDRESS',
																			'address_zip' => 'ZIP',
																			'address_city' => 'CITY',
																			'address_country' => 'COUNTRY',
																			'address_state' => 'STATE',
																			'payer_email' => 'EMAIL',
																			'contact_phone' => 'PHONE',
																			'payer_id' => 'PAYERID',
																			'auth_id' => 'CORRELATIONID',
																			'tax' => 'TAXAMT',
																			'mc_fee' => 'FEEAMT',
																			'mc_gross' => 'AMT'
																		);

					foreach ( $insToIpn as $k => $v ) {
						$ipn->$k								=	cbGetParam( $requestdata, $v );
					}

					$ipn->mc_currency							=	$paymentBasket->mc_currency;
					$ipn->user_id								=	(int) $paymentBasket->user_id;

					$recurring									=	( ( cbGetParam( $requestdata, 'USER4' ) == 'R' ) && ( cbGetParam( $requestdata, 'TENDER' ) != 'P' ) ? true : false );

					if ( $recurring ) {
						if ( ( $paymentStatus == 'Completed' ) && ( ! $paymentBasket->subscr_id ) ) {
							if ( $this->hasPaypalPayflow() ) {
								list( /*$p3 */, $t3, $start )	=	$this->_paypalPeriodsLimits( explode( ' ', $paymentBasket->period3 ), $paymentTime );

								if ( $paymentBasket->period1 ) {
									list( /*$p2*/, /*$t2*/, $start )	=	$this->_paypalPeriodsLimits( explode( ' ', $paymentBasket->period1 ), $paymentTime );
								}

								$request						=	array(	'PARTNER' => 'PayPal',
																			'VENDOR' => $this->getAccountParam( 'paypal_payflow_vendor' ),
																			'USER' => $this->getAccountParam( 'paypal_payflow_user' ),
																			'PWD' => $this->getAccountParam( 'paypal_payflow_password' ),
																			'TENDER' => cbGetParam( $requestdata, 'TENDER' ),
																			'TRXTYPE' => 'R',
																			'ACTION' => 'A',
																			'PROFILENAME' => $paymentBasket->item_name,
																			'AMT' => sprintf( '%.2f', $paymentBasket->mc_amount3 ),
																			'START' => date( 'mdY', $start ),
																			'TERM' => ( $paymentBasket->recur_times ? $paymentBasket->recur_times : 0 ),
																			'PAYPERIOD' => $t3,
																			'ORIGID' => $ipn->txn_id
																		);

								$formUrl						=	array();

								foreach ( $request as $k => $v ) {
									$formUrl[$k]				=	$k . '=' . $v;
								}

								$formUrl						=	implode( '&', $formUrl );

								$results						=	array();
								$response						=	null;
								$status							=	null;
								$error							=	$this->_httpsRequest( $this->gatewayUrl( 'psp' ), $formUrl, 105, $response, $status, 'post', 'normal' );

								if ( $response ) {
									parse_str( $response, $results );
								}

								if ( $error || ( $status != 200 ) || ( ! $response ) ) {
									$ipn->txn_type				=	'web_accept';

									$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' HTTPS POST request to payment gateway server failed.', CBPTXT::T( "Submitted refund request didn't return an error but didn't complete." ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
								} else {
									if ( cbGetParam( $results, 'RESULT' ) == 0 ) {
										$ipn->txn_type			=	'subscr_signup';
										$ipn->subscr_id			=	cbGetParam( $results, 'PROFILEID' );
										$ipn->subscr_date		=	$ipn->payment_date;
									} else {
										$ipn->txn_type			=	'web_accept';

										$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' Paypal Payflow error returned. ERROR: ' . cbGetParam( $results, 'RESPMSG' ), CBPTXT::T( 'Please contact site administrator to check error log.' ) );
									}
								}
							} else {
								$ipn->txn_type					=	'web_accept';

								$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' Needed Paypal Payflow vendor, user and password not set.', CBPTXT::T( 'Needed Paypal Payflow vendor, user and password not set.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
							}
						} elseif ( $paymentStatus == 'Denied' ) {
							if ( ( $paymentBasket->reattempts_tried + 1 ) <= cbpaidScheduler::getInstance( $this )->retries ) {
								$ipn->txn_type					=	'subscr_failed';
							} else {
								$ipn->txn_type					=	'subscr_cancel';
							}
						} elseif ( in_array( $paymentStatus, array( 'Completed', 'Processed', 'Pending' ) ) ) {
							$ipn->txn_type						=	'subscr_payment';
						}
					} else {
						$ipn->txn_type							=	'web_accept';
					}

					// validate payment from PDT or IPN
					$valid										=	$this->_validateIPN( $ipn, $paymentBasket );

					if ( $valid === true ) {
						if ( in_array( $ipn->payment_status, array( 'Completed', 'Processed', 'Pending', 'Refunded', 'Denied' ) ) ) {
							$this->_storeIpnResult( $ipn, 'SUCCESS' );

							$autorecurring_type					=	( in_array( $ipn->txn_type, array( 'subscr_payment', 'subscr_signup', 'subscr_modify', 'subscr_eot', 'subscr_cancel', 'subscr_failed' ) ) ? 2 : 0 );
							$autorenew_type						=	( $autorecurring_type ? ( ( ( $this->getAccountParam( 'enabled', 0 ) == 3 ) && ( $paymentBasket->isAnyAutoRecurring() == 2 ) ) ? 1 : 2 ) : 0 );

							if ( $autorecurring_type && ( $ipn->txn_type == 'subscr_signup' ) && ( ( $paymentBasket->period1 ) && ( $paymentBasket->mc_amount1 == 0 ) ) && ( $ipn->payment_status == '' ) ) {
								$ipn->payment_status			=	'Completed';
							}

							if ( ( $ipn->payment_status == 'Refunded' ) && ( $paymentBasket->mc_gross != ( - $ipn->mc_gross ) ) ) {
								$ipn->payment_status			=	'Partially-Refunded';
							}

							$this->_bindIpnToBasket( $ipn, $paymentBasket );

							// add the gateway to the basket:
							$paymentBasket->payment_method		=	$this->getPayName();
							$paymentBasket->gateway_account		=	$this->getAccountParam( 'id' );

							$this->updatePaymentStatus( $paymentBasket, $ipn->txn_type, $ipn->payment_status, $ipn, 1, $autorecurring_type, $autorenew_type, false );

							if ( in_array( $ipn->payment_status, array( 'Completed', 'Processed', 'Pending' ) ) ) {
								$ret							=	true;
							}
						} else {
							$this->_storeIpnResult( $ipn, 'FAILED' );

							$paymentBasket->payment_status		=	$ipn->payment_status;

							$this->_setErrorMSG( '<div class="message">' . $this->getTxtNextStep( $paymentBasket ) . '</div>' );

							$paymentBasket->payment_status		=	'RedisplayOriginalBasket';
							$ret								=	false;
						}
					} else {
						$this->_storeIpnResult( $ipn, 'MISMATCH' );

						$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' Paypal IPN fraud attempt. ERROR: ' . $valid, CBPTXT::T( 'Invalid transaction.' ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );

						$ret									=	false;
					}
				}
			}
		} else {
			$this->_setLogErrorMSG( 3, null, $this->getPayName() . ': USER1 is missing in the return URL: ' . var_export( $_GET, true ), CBPTXT::T( 'Please contact site administrator to check error log.' ) );
		}

		if ( ( $type == 'R' ) && in_array( $this->getAccountParam( 'template_layout', 'MINLAYOUT' ), array( 'MINLAYOUT', 'MOBILE' ) ) ) {
			$js													=	"if ( top != self ) {"
																.		"document.body.style.display = 'none';"
																.		"parent.location = '" . addslashes( ( $ret ? $this->getSuccessUrl( $paymentBasket ) : $this->getCancelUrl( $paymentBasket ) ) ) . "';"
																.	"}";

			echo '<script type="text/javascript">' . $js . '</script>';
		}

		return  $ret;
	}

	/**
	 * PRIVATE METHODS OF THIS CLASS
	 */

	private function getBasketIdFromToken( )
	{
		// Check if exists in URL:
		$basketId					=	(int) cbGetParam( $_GET, 'cbpbasket', null );

		if ( ! $basketId ) {
			// Check if token exists in URL:
			$basketId				=	cbGetParam( $_GET, 'SECURETOKENID', null );

			if ( $basketId ) {
				$basketIdParts		=	explode( '_', $basketId );
				$basketId			=	( isset( $basketIdParts[0] ) ? (int) $basketIdParts[0] : null );
			}

			if ( ! $basketId ) {
				// Check if token exists in POST:
				$basketId			=	cbGetParam( $_POST, 'SECURETOKENID', null );

				if ( $basketId ) {
					$basketIdParts	=	explode( '_', $basketId );
					$basketId		=	( isset( $basketIdParts[0] ) ? (int) $basketIdParts[0] : null );
				}
			}
		}

		return $basketId;
	}

	/**
	 * Checks if a Paypal Payflow is set
	 *
	 * @return bool
	 */
	private function hasPaypalPayflow( )
	{
		return ( $this->getAccountParam( 'paypal_payflow_vendor' ) && $this->getAccountParam( 'paypal_payflow_user' ) && $this->getAccountParam( 'paypal_payflow_password' ) );
	}

	/**
	 * perform anti fraud checks on ipn values
	 *
	 * @param cbpaidPaymentNotification $ipn
	 * @param cbpaidPaymentBasket $paymentBasket
	 * @return bool|string
	 */
	private function _validateIPN( $ipn, $paymentBasket )
	{
		global $_CB_database;

		$matching						=	true;

		if ( in_array( $ipn->payment_status, array( 'Completed', 'Processed', 'Canceled_Reversal' ) ) ) {
			if ( $ipn->txn_type == 'subscr_payment' ) {
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

		if ( in_array( $ipn->txn_type, array( 'subscr_signup', 'subscr_modify', 'subscr_eot', 'subscr_cancel', 'subscr_failed', 'subscr_payment' ) ) ) {
			if ( ! $paymentBasket->isAnyAutoRecurring() ) {
				$matching				=	CBPTXT::P( 'paypal subscription IPN type [txn_type] for a basket without auto-recurring items', array( '[txn_type]' => $ipn->txn_type ) );
			}
		}

		if ( ! in_array( $ipn->txn_type, array( 'subscr_signup', 'subscr_modify', 'subscr_eot', 'subscr_cancel', 'subscr_failed' ) ) ) {
			if ( ( $ipn->txn_id === '' ) || ( $ipn->txn_id === 0 ) || ( $ipn->txn_id === null ) ) {
				$matching				=	CBPTXT::T( 'illegal transaction id' );
			} else {
				$countBaskets			=	$paymentBasket->countRows( "txn_id = '" . $_CB_database->getEscaped( $ipn->txn_id ) . "' AND payment_status = 'Completed'" );

				if ( ( $countBaskets == 1 ) && ( $paymentBasket->txn_id != $ipn->txn_id ) || ( $countBaskets > 1 ) ) {
					$matching			=	CBPTXT::P( 'transaction already used for [count] other already completed payment(s)', array( '[count]' => $countBaskets ) );
				}
			}
		}

		return $matching;
	}

	private function _mapPaymentStatus( $requestdata, &$reason )
	{
		if ( cbGetParam( $requestdata, 'RESULT' ) == 0 ) {
			$type				=	cbGetParam( $requestdata, 'PAYMENTTYPE' );

			if ( ( ! $type ) || ( $type == 'instant' ) ) {
				$reason			=	null;
				$status			=	'Completed';
			} else {
				if ( $this->getAccountParam( 'accept_payment_condition' ) == 'pending' ) {
					$reason		=	null;
					$status		=	'Completed';
				} else {
					$reason		=	'Transaction being processed';
					$status		=	'Pending';
				}
			}
		} else {
			$reason				=	stripslashes( cbGetParam( $requestdata, 'RESPMSG' ) );
			$status				=	'Error';
		}

		return $status;
	}

	private function _getPaymentType( $requestdata )
	{
		switch ( cbGetParam( $requestdata, 'TENDER' ) ) {
			case 'A':
				$type			=	CBPTXT::T( 'ACH' );
				break;
			case 'CC':
				$type			=	null;

				switch ( cbGetParam( $requestdata, 'CARDTYPE' ) ) {
					case '0':
						$type	.=	CBPTXT::T( 'Visa' ) . ' ';
						break;
					case '1':
						$type	.=	CBPTXT::T( 'MasterCard' ) . ' ';
						break;
					case '2':
						$type	.=	CBPTXT::T( 'Discover' ) . ' ';
						break;
					case '3':
						$type	.=	CBPTXT::T( 'American Express' ) . ' ';
						break;
					case '4':
						$type	.=	CBPTXT::T( 'Diners Club' ) . ' ';
						break;
					case '5':
						$type	.=	CBPTXT::T( 'JCB' ) . ' ';
						break;
				}

				$type			.=	CBPTXT::T( 'Credit Card' );
				break;
			case 'D':
				$type			=	CBPTXT::T( 'Pinless Debit' );
				break;
			case 'K':
				$type			=	CBPTXT::T( 'Telecheck' );
				break;
			case 'P':
			default:
				$type			=	CBPTXT::T( 'PayPal' );
				break;
		}

		return $type;
	}

	private function _getPaymentMethod( $paymentType )
	{
		if ( $paymentType == CBPTXT::T( 'ACH' ) ) {
			$method	=	'A';
		} elseif ( strpos( $paymentType, CBPTXT::T( 'Credit Card' ) ) !== false ) {
			$method	=	'C';
		} elseif ( $paymentType == CBPTXT::T( 'Pinless Debit' ) ) {
			$method	=	'D';
		} elseif ( $paymentType == CBPTXT::T( 'Telecheck' ) ) {
			$method	=	'K';
		} else {
			$method	=	'P';
		}

		return $method;
	}

	/**
	 * Prepares and signs payflow payment $requestParams
	 *
	 * @param cbpaidPaymentBasket $paymentBasket
	 * @param bool $subscription
	 * @return array $requestParams
	 */
	private function _payflowPayment( $paymentBasket, $subscription = false )
	{
		$requestParams									=	array();

		if ( $this->hasPaypalPayflow() ) {
			$countries									=	new cbpaidCountries();

			if ( $paymentBasket->period3 ) {
				if ( $paymentBasket->period1 ) {
					$amount								=	sprintf( '%.2f', $paymentBasket->mc_amount1 );
				} else {
					$amount								=	sprintf( '%.2f', $paymentBasket->mc_amount3 );
				}
			} else {
				$amount									=	sprintf( '%.2f', $paymentBasket->mc_gross );
			}

			if ( $this->getAccountParam( 'normal_gateway' ) == '0' ) {
				$requestParams['MODE']					=	'TEST';
			}

			$request									=	array(	'PARTNER' => 'PayPal',
																	'VENDOR' => $this->getAccountParam( 'paypal_payflow_vendor' ),
																	'USER' => $this->getAccountParam( 'paypal_payflow_user' ),
																	'PWD' => $this->getAccountParam( 'paypal_payflow_password' ),
																	'TRXTYPE' => 'S',
																	'AMT' => $amount,
																	'CREATESECURETOKEN' => 'Y',
																	'SECURETOKENID' => uniqid(),
																	'TEMPLATE' => $this->getAccountParam( 'template_layout', 'MINLAYOUT' ),
																	'ORDERDESC' => $paymentBasket->item_name,
																	'INVNUM' => $paymentBasket->invoice,
																	'CURRENCY' => $paymentBasket->mc_currency,
																	'USER1' => $paymentBasket->id,
																	'USER2' => $paymentBasket->user_id,
																	'USER3' => $paymentBasket->item_number,
																	'USER4' => ( $subscription ? 'R' : 'S' )
																);

			if ( $subscription ) {
				$request['RECURRING']					=	'Y';
			}

			if ( $this->getAccountParam( 'givehiddenbillemail' ) && ( strlen( $paymentBasket->payer_email ) <= 127 ) ) {
				$request['EMAIL']						=	$paymentBasket->payer_email;
			}

			if ( $this->getAccountParam( 'givehiddenbilladdress' ) ) {
				cbimport( 'cb.tabs' );

				$addressFields							=	array(	'BILLTOFIRSTNAME' => array( $paymentBasket->first_name, 30 ),
																	'BILLTOLASTNAME' => array( $paymentBasket->last_name, 30 ),
																	'BILLTOSTREET' => array( $paymentBasket->address_street, 150 ),
																	'BILLTOZIP' => array( $paymentBasket->address_zip, 9 ),
																	'BILLTOCITY' => array( $paymentBasket->address_city, 45 ),
																	'BILLTOCOUNTRY' => array( $countries->countryToTwoLetters( $paymentBasket->address_country ), 2 )
																);

				if ( $paymentBasket->address_state != 'other' ) {
					$addressFields['BILLTOSTATE']		=	array( substr( $paymentBasket->address_state, -2 ), 2 );
				}

				foreach ( $addressFields as $k => $valueMaxlength ) {
					$adrField							=	cbIsoUtf_substr( $valueMaxlength[0], 0, $valueMaxlength[1] );

					if ( $adrField ) {
						$request[$k]					=	$adrField;
					}
				}
			}

			if ( $this->getAccountParam( 'givehiddenbilltelno' ) && ( strlen( $paymentBasket->contact_phone ) <= 50 ) ) {
				$request['BILLTOPHONENUM']				=	$paymentBasket->contact_phone;
			}

			if ( $this->getAccountParam( 'givehiddenshipemail' ) && ( strlen( $paymentBasket->payer_email ) <= 127 ) ) {
				$request['SHIPTOEMAIL']					=	$paymentBasket->payer_email;
			}

			if ( $this->getAccountParam( 'givehiddenshipaddress' ) ) {
				cbimport( 'cb.tabs' );

				$addressFields							=	array(	'SHIPTOFIRSTNAME' => array( $paymentBasket->first_name, 30 ),
																	'SHIPTOLASTNAME' => array( $paymentBasket->last_name, 30 ),
																	'SHIPTOSTREET' => array( $paymentBasket->address_street, 150 ),
																	'SHIPTOZIP' => array( $paymentBasket->address_zip, 9 ),
																	'SHIPTOCITY' => array( $paymentBasket->address_city, 45 ),
																	'SHIPTOCOUNTRY' => array( $countries->countryToThreeLetters( $paymentBasket->address_country ), 3 )
																);

				if ( $paymentBasket->address_state != 'other' ) {
					$addressFields['SHIPTOSTATE']		=	array( substr( $paymentBasket->address_state, -2 ), 2 );
				}

				foreach ( $addressFields as $k => $valueMaxlength ) {
					$adrField							=	cbIsoUtf_substr( $valueMaxlength[0], 0, $valueMaxlength[1] );

					if ( $adrField ) {
						$request[$k]					=	$adrField;
					}
				}
			}

			if ( $this->getAccountParam( 'givehiddenshiptelno' ) && ( strlen( $paymentBasket->contact_phone ) <= 50 ) ) {
				$request['SHIPTOPHONENUM']				=	$paymentBasket->contact_phone;
			}

			$formUrl									=	array();

			foreach ( $request as $k => $v ) {
				$formUrl[$k]							=	$k . '=' . $v;
			}

			$formUrl									=	implode( '&', $formUrl );

			$results									=	array();
			$response									=	null;
			$status										=	null;
			$error										=	$this->_httpsRequest( $this->gatewayUrl( 'psp' ), $formUrl, 105, $response, $status, 'post', 'normal' );

			if ( $response ) {
				parse_str( $response, $results );
			}

			if ( $error || ( $status != 200 ) || ( ! $response ) ) {
				$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' HTTPS POST request to payment gateway server failed.', CBPTXT::T( "Submitted subscription payment didn't return an error but didn't complete." ) . ' ' . CBPTXT::T( 'Please contact site administrator to check error log.' ) );
			} else {
				if ( cbGetParam( $results, 'RESULT' ) == '0' ) {
					$requestParams['SECURETOKEN']		=	cbGetParam( $results, 'SECURETOKEN' );
					$requestParams['SECURETOKENID']		=	cbGetParam( $results, 'SECURETOKENID' );
				} else{
					$this->_setLogErrorMSG( 3, null, $this->getPayName() . ' Paypal Payflow error returned. ERROR: ' . cbGetParam( $results, 'RESPMSG' ), CBPTXT::T( 'Please contact site administrator to check error log.' ) );
				}
			}
		}

		return $requestParams;
	}

	/**
	 * Limits and converts periods to Paypal limits
	 *
	 * @param array $periodTypeArray  ( int $value, string $periodCOde ) : $periodCode: 'DAY','WEEK','MONT','YEAR'
	 * @param int $now                unix timestamp of now
	 * @return array                  same encoding, but limited
	 */
	private function _paypalPeriodsLimits( $periodTypeArray, $now )
	{
		$p			=	$periodTypeArray[0];
		$t			=	$periodTypeArray[1];
		$s			=	$now;

		if ( $t == 'D' ) {
			$t		=	'DAY';
			$s		=	strtotime( '+' . intval( $p ) . ' DAY', $now );

			if ( $p == 7 ) {
				$t	=	'WEEK';
				$p	=	1;
			} elseif ( $p == 14 ) {
				$t	=	'BIWK';
				$p	=	1;
			} elseif ( $p == 15 ) {
				$t	=	'SMMO';
				$p	=	1;
			} elseif ( $p == 28 ) {
				$t	=	'FRWK';
				$p	=	1;
			} elseif ( $p == 30 ) {
				$t	=	'MONT';
				$p	=	1;
			} elseif ( $p == 90 ) {
				$t	=	'QTER';
				$p	=	1;
			} elseif ( $p == 180 ) {
				$t	=	'SMYR';
				$p	=	1;
			} elseif ( $p == 365 ) {
				$t	=	'YEAR';
				$p	=	1;
			}
		}

		if ( $t == 'W' ) {
			$t		=	'WEEK';
			$s		=	strtotime( '+' . intval( $p ) . ' WEEK', $now );

			if ( $p == 2 ) {
				$t	=	'BIWK';
				$p	=	1;
			} elseif ( $p == 4 ) {
				$t	=	'FRWK';
				$p	=	1;
			}
		}

		if ( $t == 'M' ) {
			$t		=	'MONT';
			$s		=	strtotime( '+' . intval( $p ) . ' MONTH', $now );

			if ( $p == 3 ) {
				$t	=	'QTER';
				$p	=	1;
			} elseif ( $p == 6 ) {
				$t	=	'SMYR';
				$p	=	1;
			} elseif ( $p == 12 ) {
				$t	=	'YEAR';
				$p	=	1;
			}
		}

		if ( $t == 'Y' ) {
			$t		=	'YEAR';
			$s		=	strtotime( '+' . intval( $p ) . ' YEAR', $now );
		}

		return array( $p, $t, $s );
	}
}

/**
 * Payment account class for this gateway: Stores the settings for that gateway instance, and is used when editing and storing gateway parameters in the backend.
 *
 * OEM base
 * No methods need to be implemented or overriden in this class, except to implement the private-type params used specifically for this gateway:
 */
class cbpaidGatewayAccountpaypaladvancedoem extends cbpaidGatewayAccounthostedpage
{
}

/**
 * Payment handler class for this gateway: Handles all payment events and notifications, called by the parent class:
 *
 * Gateway-specific
 * Please note that except the constructor and the API version this class does not implement any public methods.
 */
class cbpaidpaypaladvanced extends cbpaidpaypaladvancedoem
{
}

/**
 * Payment account class for this gateway: Stores the settings for that gateway instance, and is used when editing and storing gateway parameters in the backend.
 *
 * Gateway-specific
 * No methods need to be implemented or overriden in this class, except to implement the private-type params used specifically for this gateway:
 */
class cbpaidGatewayAccountpaypaladvanced extends cbpaidGatewayAccountpaypaladvancedoem
{
}
