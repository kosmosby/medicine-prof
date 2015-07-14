<?php
/**
* @version $Id: cbpaidsubscriptions.paypal.php 1608 2012-12-29 04:12:52Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
				
/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

// This gateway implements a payment handler using a hosted page at the PSP:
// Import class cbpaidHostedPagePayHandler that extends cbpaidPayHandler
// and implements all gateway-generic CBSubs methods.

/**
 * This is our very first payment gateway, and has not yet been completely been refactored to use our new CBSubs API.
 * Please do not use this implementation as a good example!
 */

/**
* Paypal payment processor class of Paid Subscriptions
*/
class cbpaidpaypal extends cbpaidHostedPagePayHandler
{
	/**
	 * Gateway API version used
	 * @var int
	 */
	public $gatewayApiVersion	=	"1.3.0";
	/**
	 * Array of gateway API urls: normally:
	 * array(	'single+normal' 	=>	'normal.gateway.com',
	 *			'single+test'		=>	'tests.gateway.com',
	 *			'recurring+normal'	=>	'recurring.gateway.com',
	 *			'recurring+test'	=>	'recurring-tests.gateway.com' );
	 * @var array of string
	 */
	protected $_gatewayUrls		=	array(	'paypal+normal' 	=>	'www.paypal.com',
											'paypal+test'		=>	'www.sandbox.paypal.com' );
	/**
	 * Overrides base class with 2:
	 * Hash type: 1 = only if there is a basket id (default), 2 = always, 0 = never
	 * @var int
	 */
	protected $_urlHashType		=	2;

	protected $_paypalApi		=	null;

	/**
	 * PUBLIC METHODS CALLED BY CBSUBS (AND THAT CAN BE EXTENDED IF NEEDED BY GATEWAY:
	 */

	/**
	 * Returns text 'using your xxxx account no....'
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return string  Text
	 */
	public function getTxtUsingAccount( $paymentBasket )
	{
		if ( $paymentBasket->receipt_id ) {
			// No PayPal account:
			return ' ' . CBPTXT::P("using PayPal with your email [PAYER_EMAIL]", array( '[PAYER_EMAIL]' => $paymentBasket->payer_email ) )
					.	'. '
					.	CBPTXT::P("Your PayPal receipt id is [RECEIPT_ID]", array( '[RECEIPT_ID]' => $paymentBasket->receipt_id ) );
		} else {
			// Has PayPal account:
			return ' ' . CBPTXT::P("using your PayPal account [PAYER_EMAIL]", array( '[PAYER_EMAIL]' => $paymentBasket->payer_email ) );
		}
	}

	/**
	 * Checks a simple hash for the payment against pdtback parameter
	 * As this gateway has other security methods but limited url, we do not want the hash in the urls
	 *
	 * @param  string  $hashToCheck
	 * @param  int     $basketId     Payment basket id
	 * @return string                Unique hash
	 */
	public function hashPdtBackCheck( $hashToCheck, $basketId = null )
	{
		return true;		// Not needed in Paypal
	}

	/**
	 * Returns html text (<p> paragraphs) on current status and next steps of payment, depending on payment basket status
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  Payment basket being paid
	 * @return string
	 */
	public function getTxtNextStep( $paymentBasket )
	{
		switch ( $paymentBasket->payment_status ) {
		 	case 'Completed':
				$newMsg = CBPTXT::Th("Your transaction has been completed, and a receipt for your purchase has been emailed to you by PayPal.")
						. ' '
						. CBPTXT::Th("You may log into your account at www.paypal.com to view status details of this transaction.");
		 		break;
		 	case 'Pending':
				$newMsg = CBPTXT::Th("Your payment is currently being processed.")
						. ' '
						. CBPTXT::Th("A receipt for your purchase will be emailed to you by PayPal once processing is complete.")
						. ' '
						. CBPTXT::Th("You may log into your account at www.paypal.com to view status details of this transaction.");
		 		break;
			case 'RegistrationCancelled':
				$newMsg = parent::getTxtNextStep( $paymentBasket );
				break;
			case 'FreeTrial':
				// if ( $paymentBasket->period1 && ( $paymentBasket->amount3 != 0 ) ) {
				//	  $newMsg = CBPTXT::Th("Your next payment will be done automatically.");		// main interface will output: 'Thank you for subscribing to .' before this
				// }
				$newMsg	=	'';
				break;
			case 'Processed':
		 	case 'Denied':
		 	case 'Reversed':
		 	case 'Refunded':
			case 'Partially-Refunded':
		 	default:
				$newMsg = CBPTXT::Th("Your transaction is not cleared and has currently following status:") . ' <strong>' . CBPTXT::Th( htmlspecialchars( $paymentBasket->payment_status ) ) . '.</strong>'
						. ' '
						. CBPTXT::Th("You may log into your account at www.paypal.com to view status details of this transaction.");
	 			break;
		}
		return $newMsg;
	}

	/**
	 * Maps payment handler payment status to standard cpay status
	 *
	 * @param  string    $paymentStatus     payment handler payment status
	 * @return string                       standard cpay status: Completed, Processed, Denied, Pending, Unknown
	 */
	protected function mapPaymentStatus( $paymentStatus )
	{
		switch ( $paymentStatus ) {
			case 'Canceled_Reversal':
				$newStatus	=	'Processed';		// these should not update validity, just restore it
				break;
			case 'Expired':
			case 'Failed':
			case 'Voided':
				$newStatus	=	'Denied';
				break;
			case 'In-Progress':
				$newStatus	=	'Pending';
				break;
			default:
				// following cases are handled in parent:
				// 'Completed','Processed','Denied','Reversed','Refunded','Partially-Refunded',
				// 'Pending','RegistrationCancelled','NotInitiated','FreeTrial'.
				$newStatus	=	parent::mapPaymentStatus( $paymentStatus );
				break;
		}
		return $newStatus;
	}

	/**
	 * CBSUBS HOSTED PAGE PAYMENT API METHODS:
	 */

	/**
	 * Popoulates basic request parameters for gateway depending on basket (without specifying payment type)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket   paymentBasket object
	 * @return array                                 Returns array $requestParams
	 */
	protected function getSinglePaymentRequstParams( $paymentBasket )
	{
		global $_CB_framework;

		$varsArray	=	array(	
							'business'		=>	trim($this->getAccountParam( 'paypal_business' )),
							'quantity'		=>	$paymentBasket->quantity,
							'item_name'		=>	$paymentBasket->item_name,
							'item_number'	=>	$paymentBasket->item_number,
							'amount'		=>	sprintf( '%.2f', $paymentBasket->mc_gross ),
							'currency_code'	=>	$paymentBasket->mc_currency,
							'no_shipping'	=>	intval($this->getAccountParam( 'paypal_no_shipping', '0' )),
							'custom'		=>	$paymentBasket->id,
							'no_note'		=>	intval($this->getAccountParam( 'paypal_no_note', '1' )),
			/* normal: */
							'return'		=>	$this->getSuccessUrl( $paymentBasket ),
						//	'return'		=>	$return_success_url .'&start_debug=1&debug_host=127.0.0.1&no_remote=1&debug_port=10137&debug_stop=1&debug_session_id=' . ( 1000 + rand( 100, 9999 ) ),	// <- pdt studio 6, studio 5.5 -> &start_debug=1&debug_host=127.0.0.1',
							'cancel_return'	=>	$this->getCancelUrl( $paymentBasket ),
							'notify_url'	=>	$this->getNotifyUrl( $paymentBasket ),
						//	'notify_url'	=>	$notify_url .'&start_debug=1&debug_host=127.0.0.1&no_remote=1&debug_port=10137&debug_stop=1&debug_session_id=' . ( 1000 + rand( 100, 9999 ) ),	// <- pdt studio 6, studio 5.5 -> &start_debug=1&debug_host=127.0.0.1',
							'charset'		=>	$_CB_framework->outputCharset(),
			/* debug IPN: *
							'return'		=>	'http://af.joomlalight.com',
							'cancel_return'	=>	$return_cancel_url,
							'notify_url'	=>	$notify_url .'&start_debug=1&debug_host=193.5.2.218',
			/* debug PDT: *
							'return'		=>	$return_success_url .'&start_debug=1&debug_host=83.77.4.181',
							'cancel_return'	=>	$return_cancel_url,
							'notify_url'	=>	'http://af.joomlalight.com',
			/* debug Cancel: *
							'return'		=>	$return_success_url,
							'cancel_return'	=>	$return_cancel_url .'&start_debug=1&debug_host=193.5.2.218',
							'notify_url'	=>	$notify_url,
			/* */
							'rm'			=>	'2',							// ( $redirectNow ? '1' : '2' ),
							'cmd'			=>	'_xclick'						//TBD: check for compatibility
						);
		if ( $paymentBasket->tax && ( $paymentBasket->tax > 0 ) ) {
			// Treat roundings particularly well, so it matches gross amount always:
			$mc_gross_2f					=	sprintf( '%.2f', $paymentBasket->mc_gross );
			$varsArray['tax']				=	sprintf( '%.2f', $paymentBasket->tax );
			$varsArray['amount']			=	sprintf( '%.2f', $mc_gross_2f - $varsArray['tax'] );
		}
		if ( $this->getAccountParam( 'paypal_page_style' ) ) {
			$varsArray['page_style']		=	$this->getAccountParam( 'paypal_page_style' );
		}
		$image_url							=	$this->getImageUrl();
		if ( $image_url ) {
			$varsArray['image_url']			=	$image_url;
		}
		if ( $this->getAccountParam( 'paypal_country' ) ) {
			$varsArray['country']			=	$this->getAccountParam( 'paypal_country' );
		}
		if ( $paymentBasket->invoice ) {
			$varsArray['invoice']			=	$paymentBasket->invoice;
		}
		$this->_populateAddress( $varsArray, $paymentBasket );
		$this->_paypalEncryptIfOk( $varsArray );
		return $varsArray;
	}

	/**
	 * Optional function: only needed for recurring payments:
	 * Popoulates subscription request parameters for gateway depending on basket (without specifying payment type)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket   paymentBasket object
	 * @return array                                 Returns array $requestParams
	 */
	protected function getSubscriptionRequstParams( $paymentBasket )
	{
		global $_CB_framework;

		// $paymentBasket->setAmountsPeriods( $now );							//TBD decide if we don't want to update the periods (e.g. if basket is old), but then needs to be done elsewhere too
		list( $p3, $t3 )					=	$this->_paypalPeriodsLimits( explode( ' ', $paymentBasket->period3 ) );
		$varsArray	=	array(	
							'business'		=>	trim($this->getAccountParam( 'paypal_business' )),
						//	'quantity'		=>	$paymentBasket->quantity,
							'item_name'		=>	$paymentBasket->item_name,
							'item_number'	=>	$paymentBasket->item_number,
							'p3'			=>	$p3,
							't3'			=>	$t3,
							'a3'			=>	sprintf( '%.2f', $paymentBasket->mc_amount3 ),
							'src'			=>	'1',
							'sra'			=>	'1',
							'modify'		=>	'0',
							'currency_code'	=>	$paymentBasket->mc_currency,
							'no_shipping'	=>	'0',											//?????
							'custom'		=>	$paymentBasket->id,
						//	'usr_manage'	=>	'0',
							'no_note'		=>	intval($this->getAccountParam( 'paypal_no_note', '1' )),
			/* normal: */
							'return'		=>	$this->getSuccessUrl( $paymentBasket ),
							'cancel_return'	=>	$this->getCancelUrl( $paymentBasket ),
							'notify_url'	=>	$this->getNotifyUrl( $paymentBasket ),
							'charset'		=>	$_CB_framework->outputCharset(),
		/* debug IPN: *
							'return'		=>	'http://af.joomlalight.com',
							'cancel_return'	=>	$return_cancel_url,
							'notify_url'	=>	$notify_url .'&start_debug=1&debug_host=193.5.2.218',
			/* debug PDT: *
							'return'		=>	$return_success_url .'&start_debug=1&debug_host=83.77.4.181',
							'cancel_return'	=>	$return_cancel_url,
							'notify_url'	=>	'http://af.joomlalight.com',
			/* debug Cancel: *
							'return'		=>	$return_success_url,
							'cancel_return'	=>	$return_cancel_url .'&start_debug=1&debug_host=193.5.2.218',
							'notify_url'	=>	$notify_url,
			/* */
							'rm'			=>	'2',												//( $redirectNow ? '1' : '2' ),
							'cmd'			=>	'_xclick-subscriptions'															//TBD: check for compatibility
						);
		if ( $this->getAccountParam( 'paypal_page_style' ) ) {
			$varsArray['page_style']		=	$this->getAccountParam( 'paypal_page_style' );
		}
		$image_url							=	$this->getImageUrl();
		if ( $image_url ) {
			$varsArray['image_url']			=	$image_url;
		}
		if ( $this->getAccountParam( 'paypal_country' ) ) {
			$varsArray['lc']				=	$this->getAccountParam( 'paypal_country' );
		}
		if ( $paymentBasket->invoice ) {
			$varsArray['invoice']			=	$paymentBasket->invoice;
		}
		if ( $paymentBasket->period1 ) {
			list( $p1, $t1 )				=	$this->_paypalPeriodsLimits( explode( ' ', $paymentBasket->period1 ) );
			$varsArray['a1']				=	sprintf( '%.2f', $paymentBasket->mc_amount1 );
			$varsArray['p1']				=	$p1;
			$varsArray['t1']				=	$t1;
		}
		if ( $paymentBasket->period2 ) {
			list( $p2, $t2 )				=	$this->_paypalPeriodsLimits( explode( ' ', $paymentBasket->period2 ) );
			$varsArray['a2']				=	sprintf( '%.2f', $paymentBasket->mc_amount2 );
			$varsArray['p2']				=	$p2;
			$varsArray['t2']				=	$t2;
		}
		if ( $paymentBasket->recur_times ) {
			$varsArray['srt']				=	$paymentBasket->recur_times;
		}
		$this->_populateAddress( $varsArray, $paymentBasket );
		$this->_paypalEncryptIfOk( $varsArray );
		return $varsArray;
	}

	/**
	 * The user got redirected back from the payment service provider with a success message: Let's see how successfull it was
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	 * @return string                               HTML to display if frontend, text to return to gateway if notification, FALSE if XML error (and not yet ErrorMSG generated), or NULL if nothing to display
	 */
	protected function handleReturn( $paymentBasket, $postdata )
	{
		global $_GET;

		$ret								=	null;
		if ( isset( $postdata['txn_type'] ) || isset( $_GET['txn_type'] ) ) {
			$ret	=	$this->hanldePaypalIPN( $paymentBasket, $this->useGetIfPostdataEmpty( $postdata ) );
		} else {
			$ret	=	$this->handlePaypalPDT( $paymentBasket, $postdata );
		}
		return $ret;
	}

	/**
	 * The user cancelled his payment
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	 * @return string                               HTML to display if frontend, text to return to gateway if notification, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	 */
	protected function handleCancel( $paymentBasket, $postdata )
	{
		// The user cancelled his payment (and registration):
		$ret										=	null;
		if ( $this->hashPdtBackCheck( $this->_getReqParam( 'pdtback' ) ) ) {
			$paymentBasketId						=	(int) $this->_getReqParam( 'basket');
			$exists									=	$paymentBasket->load( (int) $paymentBasketId );
			if ( $exists ) {
				if ( $paymentBasket->payment_status == 'NotInitiated' ) {
					$paymentBasket->payment_status	=	'RegistrationCancelled';
					$ret							=	'<div class="message">' . $this->getTxtNextStep( $paymentBasket ) . '</div>';
					$paymentBasket->payment_status	=	'RedisplayOriginalBasket';
				} // no else here, as the basket status will trigger the appropriate actions after returning from here.
			} else {
				$this->_setLogErrorMSG( 7, null, 'Paypal: Info: User returned from Paypal with Cancel but basket does not exist anymore so he got message to retry.', CBPTXT::T("Sorry, your payment basket has timed out. Please select your items again.") );
			}
		}
		return $ret;
	}

	/**
	 * Checks for $_GET args if no $postdata (that way IPNs can be replayed
	 *
	 * @param $postdata
	 * @return array
	 */
	private function useGetIfPostdataEmpty( $postdata )
	{
		global $_GET;

		if ( $postdata && ( count( $postdata ) > 0 ) ) {
			$ipnArgs					=	$postdata;
		} else {
			// This allows to replay IPNs from the data in PayPal History - IPN History by simply appending them to the URL:
			$ipnArgs					=	$_GET;
			// option=com_comprofiler&task=pluginclass&plugin=cbpaidsubscriptions&cbpgacctno=28&cbppdtback=6edaa2b108d4ea8fc3663f91c9b78d2e&cbpbasket=322&result=notify&cbpid=cbp4ff714207f556364285060&format=raw
			unset( $ipnArgs['option'], $ipnArgs['task'], $ipnArgs['view'], $ipnArgs['format'], $ipnArgs['plugin'], $ipnArgs['cbpgacctno'], $ipnArgs['cbpbasket'], $ipnArgs['result'], $ipnArgs['cbpid'], $ipnArgs['Itemid'] );
		}
		return $ipnArgs;
	}

	/**
	 * The payment service provider server did a server-to-server notification: Verify and handle it here:
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	 * @return string                               HTML to display if frontend, text to return to gateway if notification, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	 */
	protected function handleNotification( $paymentBasket, $postdata )
	{
		return $this->hanldePaypalIPN( $paymentBasket, $this->useGetIfPostdataEmpty( $postdata ) );
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
		global $_CB_framework, $_CB_database;

		if ( $paymentBasket->mc_amount3 ) {
			// Recurring amount existing and if first amount existed it got payed OK: subscribe to an ARB:
			if ( $paymentBasket->subscr_id ) {
				
				if ( $this->hasPaypalApi() /* && ( substr( $paymentBasket->subscr_id, 0, 2 ) != 'S-' ) */ ) {			// Only subscription ids starting with I- can be handled through API for unsubscription: S- subscriptions can not.
					// There is an API access, let's try to use it:

					$return						=	$this->getPaypalApi()->ManageRecurringPaymentsProfileStatus( $paymentBasket->subscr_id, 'cancel', null );
					if ( $return ) {
						// Success! : $return is TRUE:
						$ipn					=	new cbpaidPaymentNotification( $_CB_database );
						$ipn->payment_method	=	$this->getPayName();
						$ipn->gateway_account	=	$this->getAccountParam( 'id' );
						$ipn->log_type			=	'5';
						$ipn->time_received		=	date( 'Y-m-d H:i:s', $_CB_framework->now() );
						$ipn->raw_data			=	/* cbGetParam() not needed: we want raw info */ '$_GET=' . var_export( $_GET, true ) . ";\n";
						$ipn->raw_data			.=	/* cbGetParam() not needed: we want raw info */ '$_POST=' . var_export( $_POST, true ) . ";\n";
						$ipn->raw_data			.=	$this->getPaypalApi()->getRawLogData() . ";\n";
						$ipn->ip_addresses		=	cbpaidRequest::getIPlist();
						$ipn->payment_basket_id	=	$paymentBasket->id;
						$ipn->user_id			=	$paymentBasket->user_id;
						$_CB_database->insertObject( $ipn->getTableName(), $ipn, $ipn->getKeyName() );

					} else {
						$this->setLogPaypalApiErrors( $paymentBasket );
						$return					=	'';
					}
				} else {
					$return						=	'';
				}
				if ( ! $return ) {
					// No API, we can only render a button: (which is not displayed in case of upgrades):

					if ( ! is_string( $return ) ) {
						$return					=	'';
					}

					$return						.=	CBPTXT::Th("You are currently using PayPal  Recurring Payments to pay for your subscription. To unsubscribe and stop future payments, you must to do this from Paypal. Click on the button below to login into PayPal and follow the instructions there to unsubscribe. This will automatically stop your subscription on this site.");
	
					$paymentButton				=	$this->getPayButtonRecepie( $paymentBasket, 'subscribe', 'cancel' );
	
					// Needed to post the button:
					$subscriptionsGUI					=	new cbpaidControllerUI();
					$subscriptionsGUI->addcbpaidjsplugin();

					/** @var $renderer cbpaidBasketView */
					$renderer					=	cbpaidTemplateHandler::getViewer( null, 'basket' );
					$renderer->setModel( $paymentBasket );
					$return						.=	$renderer->drawPaymentButton( $paymentButton );
				}
			} else {
				$this->_setLogErrorMSG( 3, null, 'Paypal autorecurring payment subscriptions: stopPaymentSubscription error: missing subscr_id in payment basket', CBPTXT::T("Submitted unsubscription didn't return an error but didn't complete.") );
				$return							=	'';
			}
		} else {
			$return								=	CBPTXT::T("Unsubscription from payment processor is not possible as this payment basket has no autorecurring amount.");
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
		global $_CB_database;

		$return						=	false;

		// Form request:
		if ( $this->hasPaypalApi() ) {
			// There is an API access, let's try to use it:

			if ( $lastRefund || ( $amount == $payment->mc_gross ) ) {
				$refundType			=	'Full';
				$currencyCode		=	null;
				$amount				=	null;
			} else {
				$refundType			=	'Partial';
				$currencyCode		=	$payment->mc_currency;
			}
			$results				=	array();
			$return					=	$this->getPaypalApi()->RefundTransaction( $payment->txn_id, $refundType, $currencyCode, $amount, $reasonText, $results );
			if ( $return ) {
				// Success in refunding:
				if ( $refundType == 'Partial' ) {
					$paymentStatus	=	'Partially-Refunded';
				} else {
					$paymentStatus	=	'Refunded';
				}
				$paymentType		=	$results['REFUNDSTATUS'];
				$reasonCode			=	$results['PENDINGREASON'];
				$paymentTime		=	strtotime( $results['TIMESTAMP'] );
				$charset			=	'UTF-8';
				$ipn				=	$this->_prepareIpn( '3', $paymentStatus, $paymentType, $reasonCode, $paymentTime, $charset );
				$ipn->raw_data		.=	$this->getPaypalApi()->getRawLogData() . ";\n";
				$ipn->bindBasket( $paymentBasket );
				$ipn->mc_currency	=	$results['CURRENCYCODE'];
				$ipn->mc_gross		=	- $results['GROSSREFUNDAMT'];
				$ipn->computeRefundedTax( $payment );
				$ipn->mc_fee		=	- $results['FEEREFUNDAMT'];
				$ipn->auth_id		=	$results['CORRELATIONID'];
				$ipn->txn_id		=	$results['REFUNDTRANSACTIONID'];
				$ipn->parent_txn_id	=	$payment->txn_id;

				$_CB_database->insertObject( $ipn->getTableName(), $ipn, $ipn->getKeyName() );

				$this->updatePaymentStatus( $paymentBasket, $ipn->txn_type, $ipn->payment_status, $ipn, 1, 0, 0, false );

			} else {
				$this->setLogPaypalApiErrors( $paymentBasket );
			}
		} else {
			$this->_setLogErrorMSG( 3, $payment, null, CBPTXT::T("Needed Paypal API username, password and signature not set") );
		}

		$returnText					=	null;

		return $return;
	}

	/**
	 * INTERNAL METHODS THAT CAN BE RE-IMPLEMENTED IN PAYMENT HANDLER IF NEEDED:
	 */

	/**
	 * gives gateway button URL server name from gateway URL list
	 *
	 * @param  cbpaidPaymentBasket|null  $paymentBasket  paymentBasket object
	 * @param  boolean                   $autoRecurring  TRUE: autorecurring payment, FALSE: single payment
	 * @return string                                    server-name (with 'https://' )
	 */
	protected function pspUrl( $paymentBasket, $autoRecurring )
	{
		return $this->_paypalUrl() . "/cgi-bin/webscr";
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
		foreach ( $requestParams as $k => $v ) {
			$requestParams[$k]	=	$k . '=' . $this->_paypalUrlEncode( $v );
		}

		$url		=	$this->_paypalUrl();
		if ( ! $autoRecurring ) {
			$url	.=	'/xclick/';
		} else {
			$url	.=	'/xclick-subscriptions/';
		}
		$url		.=	implode( '&', $requestParams );
		return $url;
	}

	/**
	 * Returns a cbpaidGatewaySelectorButton object parameters for rendering an HTML form with a visible button and hidden fields for the gateway
	 * Or a string with HTML content instead (not recommended)
	 * 
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @param  string               $subMethod      'single', 'subscribe' or gateway-specific string (e.g. credit-card brand)
	 * @param  string               $paymentType    'single' or 'subscribe' or for subscriptions 'cancel'
	 * @return cbpaidGatewaySelectorButton                  or string with HTML
	 */
	protected function getPayButtonRecepie( $paymentBasket, $subMethod, $paymentType )
	{
		list( $customImage, $altText, $payNameForCssClass, $butId )	=	$this->getPayButtonParams( $paymentType );

		switch ( $paymentType ) {
			case 'single':
				$requestParams	=	$this->getSinglePaymentRequstParams( $paymentBasket );
				$titleText		=	htmlspecialchars( sprintf( CBPTXT::T("Pay safely with %s"), CBPTXT::T("PayPal") ) );
				break;
			case 'subscribe':
				$requestParams	=	$this->getSubscriptionRequstParams( $paymentBasket );
				$titleText		=	htmlspecialchars( sprintf( CBPTXT::T("Subscribe to automatic payments safely with %s"), CBPTXT::T("PayPal") ) );
				break;
			case 'cancel':
				$requestParams	=	$this->_getPaymentBasketSubscriptionCancel( $paymentBasket );
				$titleText		=	$altText;
				break;
			default:
				$requestParams	=	array();
				$titleText		=	null;
		}

		$pspUrl					=	$this->pspUrl( $paymentBasket, ( $paymentType != 'single' ) );
		return cbpaidGatewaySelectorButton::getPaymentButton( $this->getAccountParam( 'id' ), $subMethod, $paymentType, $pspUrl, $requestParams, $customImage, $altText, $titleText, $payNameForCssClass, $butId );
	}

	/**
	 * Returns a cbpaidGatewaySelectorButton object parameters for rendering an HTML form with a visible button and hidden fields for the gateway
	 * For just switching currency of gateway.
	 * 
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @param  string               $subMethod      'single', 'subscribe' or gateway-specific string (e.g. credit-card brand)
	 * @param  string               $paymentType    'single' or 'subscribe' or for subscriptions 'cancel'
	 * @return cbpaidGatewaySelectorButton                  or string with HTML
	 */
	protected function getChangeOfCurrencyButton( $paymentBasket, $subMethod, $paymentType )
	{
		list( $customImage, $altText, $payNameForCssClass, $butId )	=	$this->getPayButtonParams( $paymentType );

		$titleText				=	CBPTXT::T( $this->getAccountParam( 'currency_acceptance_text' ) );

		$newCurrency			=	$this->mainCurrencyOfGateway();
		return cbpaidGatewaySelectorButton::getChangeOfCurrencyButton( $paymentBasket, $newCurrency, $customImage, $altText, $titleText, $payNameForCssClass . ' ' . 'cbregconfirmtitleonclick', $butId );
	}

	/**
	 * PRIVATE METHODS OF THIS CLASS
	 */

	/**
	 * Returns params for getPayButtonRecepie() and getChangeOfCurrencyButton()
	 * 
	 * @param  string  $paymentType  'single' or 'subscribe' or for subscriptions 'cancel'
	 * @return array                 array( $customImage, $altText, $payNameForCssClass, $butId )
	 */
	private function getPayButtonParams( $paymentType )
	{
		$brandText				=	CBPTXT::T("PayPal");

		switch ( $paymentType ) {
			case 'single':
				$prmImg			=	'paypal_image';
				$prmCustImg		=	'paypal_custom_image';
				$altText		=	sprintf( CBPTXT::T( 'Pay with %s' ), $brandText );
				$butId			=	'cbpaidButt' . strtolower( $this->getPayName() );
				break;
			case 'subscribe':
				$prmImg			=	'paypal_subscribe_image';
				$prmCustImg		=	'paypal_subscribe_custom_image';
				$altText		=	htmlspecialchars( CBPTXT::P("Subscribe to automatic payments with [PAYMENT_METHOD]", array( '[PAYMENT_METHOD]' => $brandText ) ) );
				$butId			=	'cbpaidButt' . strtolower( $this->getPayName() ) . 'subscr';
				break;
			case 'cancel':
				$prmImg			=	null;
				$prmCustImg		=	null;
				$altText		=	htmlspecialchars( CBPTXT::T("Cancel automatic payments") );
				$butId			=	'cbpaidButtpaypalsubscrcancel';
				break;
			default:
				trigger_error( __CLASS__ . '::' . __FUNCTION__ . ': unknown paymentType.', E_USER_WARNING );
				$prmImg			=	null;
				$prmCustImg		=	null;
				$altText		=	'';
				$butId			=	null;
				break;
		}
		if ( $paymentType == 'cancel' ) {
			$customImage		=	CBPTXT::T("Unsubscribe at Paypal");
		} else {
			$customImage		=	trim( $this->getAccountParam( $prmCustImg ) );
		}
		if ( $customImage == '' ) {
			$customImage		=	trim( $this->getAccountParam( $prmImg ) );
		}

		$payNameForCssClass		=	$this->getPayName();

		return array( $customImage, $altText, $payNameForCssClass, $butId );
	}

	/**
	 * Encodes an URL the paypal way
	 *
	 * @param  string  $text   plaintext url with htmlspecialchars
	 * @return string          encoded url
	 */
	private function _paypalUrlEncode( $text )
	{
		return str_replace( '%2F', '/', urlencode( cbUnHtmlspecialchars( $text ) ) );
	}

	/**
	 * Fixes Paypal's IPN bugs: accept that case and fix $ipn , so that paymentBasket and payment are correct per CBSubs
	 * - payment_status = 'Canceled_Reversal' : the mc_gross amount is not including mc_fee as it was when it was completed and when it was reversed:
	 * 
	 * @param  cbpaidPaymentNotification  $ipn
	 * @param  cbpaidPaymentBasket        $paymentBasket
	 */
	private function _fixPayPalIpnBugs( $ipn, $paymentBasket )
	{
		if ( in_array( $ipn->payment_status, array( 'Reversed', 'Canceled_Reversal' ), true ) ) {
			// treat 'mc_gross':
			$payments					=	$paymentBasket->getPaymentsTotals();
			if ( ( $paymentBasket->mc_amount1 != 0 ) && ( $payments->count == 0 ) ) {
				$tobepaid				=	$paymentBasket->mc_amount1;
			} elseif ( $paymentBasket->mc_amount3 != 0 ) {
				$tobepaid				=	$paymentBasket->mc_amount3;
			} else {
				$tobepaid				=	$paymentBasket->mc_gross;
			}
			if ( $ipn->payment_status === 'Reversed' ) {
				$tobepaid				=	- $tobepaid;
			}
			if ( sprintf( '%.2f', $ipn->mc_gross ) != sprintf( '%.2f', $tobepaid ) ) {
				if ( sprintf( '%.2f', $ipn->mc_gross + $ipn->mc_fee ) == sprintf( '%.2f', $tobepaid ) ) {
					// Bug in paypal: mc_gross is without fees in that case !!!: so workaround the bug and the possible fix later at paypal: (2010/11/24):
					$ipn->mc_gross		=	sprintf( '%.2f', $ipn->mc_gross + $ipn->mc_fee );
				} elseif ( sprintf( '%.2f', $ipn->mc_gross - $ipn->tax + $ipn->mc_fee ) == sprintf( '%.2f', $tobepaid ) ) {
					// try to check if it's same bug, but with a paypal tax added in paypal:
					$ipn->mc_gross		=	sprintf( '%.2f', $ipn->mc_gross + $ipn->mc_fee );
				}
			}
		}
		
	}

	/**
	 * Checks against frauds for PDT and for IPN
	 *
  	 * In order to prevent fraud, PayPal recommends that your programs verify the following:
	 * When you receive a VERIFIED response, perform the following checks: 
	 *  1. Check that the payment_status is Completed. (NOT done here, but in UpdatePayment status)
	 *  2. If the payment_status is Completed, check the txn_id against the previous completed PayPal
	 *     transaction you have processed to ensure it is not a duplicate.
	 *  3. After you have checked the payment_status and txn_id, make sure the 
	 *     receiver_email is an email address registered in your PayPal account.
	 *  4. Check that the price, mc_gross, and currency, mc_currency, are correct for the item, 
	 *     item_name or item_number.
	 *  5. Check the the shared secret returned to you is correct.
	 * 
	 * @param  cbpaidPaymentNotification  $ipn            notification verified with paypal
	 * @param  cbpaidPaymentBasket        $paymentBasket  matched basket
	 * @param  string                     $cbpid          shared secret which should be returned by paypal
	 * @return boolean|string                             TRUE for no fraud detected, otherwise error TEXT
	 */
	private function _checkNotPayPalFraud( $ipn, $paymentBasket, $cbpid )
	{
		global $_CB_database;
		
		$matching = true;
		// 3) receiver_email is an email address registered in your PayPal account, to prevent the payment
		//    from being sent to a fraudulent account:
		$receiver_email			=	strtolower( trim( $this->getAccountParam( 'paypal_receiver_email' ) ) );
		$business				=	strtolower( trim( $this->getAccountParam( 'paypal_business' ) ) );
		if ( $receiver_email ) {
			if ( strtolower( $ipn->receiver_email ) != $receiver_email ) {
				// let's give a second, third and fourth chance to misconfigurations:
				if (   ( strtolower( $ipn->business ) != $business )
					&& ( strtolower( $ipn->receiver_email ) != $business )
					&& ( strtolower( $ipn->business ) != $receiver_email ) )
				{
					$matching	=	sprintf( "receiver_email mismatch: parametered business (%s) or receiver (%s) expected does not match IPN business (%s) or receiver (%s).", $business, $receiver_email, $ipn->business, $ipn->receiver_email );
				}
			}
		} else {
			if ( strtolower( $ipn->business ) != $business ) {
				// let's give a second chance to misconfigurations:
				if ( strtolower( $ipn->receiver_email ) != $business ) {
					$matching	=	sprintf( "business email mismatch: parametered business (%s) or receiver (%s) expected does not match IPN receiver (%s).", $business, $receiver_email, $ipn->receiver_email );
				}
			}
		}
		// 4) Check transaction details, such as the item number and price, to confirm that the price has not
		//    been changed: mc_gross, and currency, mc_currency, item_name or item_number:
		if ( in_array( $ipn->payment_status, array( 'Completed', 'Processed', 'Canceled_Reversal' ) ) ) {
			if ( $ipn->txn_type == 'subscr_payment' ) {
				$checkFields	=	array( 'mc_currency' => 100, 'item_name' => 127, 'item_number' => 127 );
				$txt_error		=	"currency, item name or number mismatch";
				// treat 'mc_gross':
				$payments		=	$paymentBasket->getPaymentsTotals( $ipn->txn_id );
				if ( ( $paymentBasket->mc_amount1 != 0 ) && ( $payments->count == 0 ) ) {
					$tobepaid	=	$paymentBasket->mc_amount1;
				} else {
					$tobepaid	=	$paymentBasket->mc_amount3;
				}
				if ( sprintf( '%.2f', $ipn->mc_gross ) != sprintf( '%.2f', $tobepaid ) ) {
					// try to check if it's a paypal tax added in paypal:
					if ( ( sprintf( '%.2f', $ipn->mc_gross ) < sprintf( '%.2f', $tobepaid ) )
					||   ( sprintf( '%.2f', $ipn->mc_gross - $ipn->tax ) != sprintf( '%.2f', $tobepaid ) ) )
					{
						// Final attempt to say "ok": if there is an increase in this recurring payment (not first one) done at paypal's side (20% per period max):
						if ( ( ! ( ( $paymentBasket->mc_amount1 != 0 ) && ( $payments->count == 0 ) ) ) && ( ( (float ) sprintf( '%.2f', $ipn->mc_gross - abs( $ipn->tax ) ) ) < (float) sprintf( '%.2f', $tobepaid ) ) ) {
							$matching	=	sprintf("amount mismatch on subscr_payment: tobepaid: %s != IPN mc_gross: %s or IPN mc_gross - IPN tax: %s where IPN tax = %s", $tobepaid, $ipn->mc_gross - $ipn->tax, $ipn->mc_gross, $ipn->tax );
						}
					}
				}
			} else {				// elseif ( $ipn->txn_type == 'web_accept' ) {
				if ( sprintf( '%.2f', $ipn->mc_gross ) != sprintf( '%.2f', $paymentBasket->mc_gross ) ) {
					// try to check if it's a paypal tax added in paypal:
					if ( ( sprintf( '%.2f', $ipn->mc_gross ) < sprintf( '%.2f', $paymentBasket->mc_gross ) )
					||   ( sprintf( '%.2f', $ipn->mc_gross - $ipn->tax ) != sprintf( '%.2f', $paymentBasket->mc_gross ) ) )
					{
						$matching	=	sprintf("amount mismatch on webaccept: BASKET mc_gross: %s != IPN mc_gross: %s or IPN mc_gross - IPN tax: %s where IPN tax = %s", $paymentBasket->mc_gross, $ipn->mc_gross - $ipn->tax, $ipn->mc_gross, $ipn->tax );
					}
				}
				$checkFields	=	array( 'mc_currency' => 100, 'item_name' => 127, 'item_number' => 127, 'quantity' => 127 );
				if ( $ipn->payment_status == 'Canceled_Reversal' ) {
					// for some reasons, Cancel_Reversal (we won!) don't provide quantity, so do not check: (bug #1099)
					unset( $checkFields['quantity'] );
				}
				$txt_error		=	"currency, item name or number or quantity mismatch";
			}
			foreach ( $checkFields as $cf => $csize ) {
				if ( !isset( $ipn->$cf) || !isset( $paymentBasket->$cf) || ( trim( substr( $ipn->$cf, 0, $csize ) ) != trim( substr( $paymentBasket->$cf, 0, $csize ) ) ) ) {
					// print_r($ipn); print_r($paymentBasket);
					$matching	=	$txt_error . ': ' . sprintf( "IPN %s (%s) does not match basket %s (%s) nor their trimmed sizes for IPN (%s) and basket (%s)", $cf, $ipn->$cf, $cf, $paymentBasket->$cf, trim( substr( $ipn->$cf, 0, $csize ) ), trim( substr( $paymentBasket->$cf, 0, $csize ) ) );
					break;
				}
			}
		} else {
			//TBD: see what to check for other events...
		}
		if ( in_array( $ipn->txn_type, array( 'subscr_signup', 'subscr_modify', 'subscr_eot', 'subscr_cancel', 'subscr_failed', 'subscr_payment' ) ) ) {
			if ( ! $paymentBasket->isAnyAutoRecurring() ) {
				$matching		=	sprintf( "paypal subscription IPN type %s for a basket without auto-recurring items", $ipn->txn_type );
			}
		}
	 	// 2) txn_id is not a duplicate to prevent someone from reusing an old, completed transaction:
		if ( ! in_array( $ipn->txn_type, array( 'subscr_signup', 'subscr_modify', 'subscr_eot', 'subscr_cancel', 'subscr_failed' ) ) ) {
			if ( ( $ipn->txn_id === '' ) || ( $ipn->txn_id === 0 ) || ( $ipn->txn_id === null ) ) {
				$matching		=	"illegal transaction id";
			} else {
				$countBaskets	=	$paymentBasket->countRows( "txn_id = '" . $_CB_database->getEscaped( $ipn->txn_id ) . "' AND payment_status = 'Completed'" );
				if ( ( $countBaskets == 1 ) && ( $paymentBasket->txn_id != $ipn->txn_id ) || ( $countBaskets > 1 ) ) {
					$matching	=	sprintf( "transaction already used for %d other already completed payment(s)", $countBaskets );
				}
			}
		}
		// 5) Check the the shared secret returned to you is correct.
		if ( $cbpid != $paymentBasket->shared_secret ) {
			$matching			=	sprintf( "shared secret '%s' returned by Paypal does not match the value we expected", htmlspecialchars( $cbpid ) );
		}
		return $matching;
	}

	/**
	 * Gives the configured paypal server name
	 *
	 * @return string                  Server-name (with 'https://' )
	 */
	private function _paypalUrl( )
	{
		return $this->gatewayUrl( 'paypal' );
	}

	/**
	 * Finds a writable temporary directory path
	 *
	 * @return string
	 */
	private function findATmpDir( )
	{
		global $_CB_framework;

		// First try the new PHP 5.2.1+ function:
		if ( function_exists( 'sys_get_temp_dir' ) ) {
			$tmpDir		=	@sys_get_temp_dir();
			if ( @is_dir( $tmpDir ) && @is_writable( $tmpDir ) ) {
				return $tmpDir;
			}
		}
		// Based on http://www.phpit.net/article/creating-zip-tar-archives-dynamically-php/2/
		$varsToTry	=	array( 'TMP', 'TMPDIR', 'TEMP' );
		foreach ( $varsToTry as $v ) {
			if ( ! empty( $_ENV[$v] ) ) {
				$tmpDir		=	@realpath( $v );
				if ( isset( $tmpDir ) && $tmpDir && @is_dir( $tmpDir ) && @is_writable( $tmpDir ) ) {
					return $tmpDir;
				}
			}
		}
		// Try the CMS tmp directory:
		$tmpDir		=	$_CB_framework->getCfg( 'absolute_path' ) . '/tmp';
		if ( @is_dir( $tmpDir ) && @is_writable( $tmpDir ) ) {
			return $tmpDir;
		}
		// Try the CMS cache directory:
		$tmpDir		=	$_CB_framework->getCfg( 'cachepath' );
		if ( @is_dir( $tmpDir ) && @is_writable( $tmpDir ) ) {
			return $tmpDir;
		}
		// Try /tmp:
		$tmpDir		=	@realpath( '/tmp' );
		if ( $tmpDir && @is_dir( $tmpDir ) && @is_writable( $tmpDir ) ) {
			return $tmpDir;
		}
		return null;
	}

	/**
	 * Encrypts and signs the request to paypal
	 *
	 * To generate a keypair:
	 * openssl genrsa -des3 -out privkey.pem 2048
	 * openssl req -new -x509 -key privkey.pem -out cacert.pem -days 3650
	 * 
	 * To encrypt and sign (that's what we do here):
	 * openssl smime -sign -signer cacert.pem -inkey privkey.pem -outform der -nodetach -binary -passin pass:1234 | openssl smime -encrypt -des3 -binary -outform pem paypal_cert_pem.txt
	 *
	 * @param  string        $cleartext  Cleartext to encrypt and sign
	 * @return string                    Encrypted text or FALSE
	 */
	private function _paypalEncrypt( $cleartext )
	{
		$return							=	false;

		$paypal_openssl_path			=	$this->params->get( 'openssl_exec_path', '/usr/bin/openssl' );
		$paypal_public_certificate_path	=	$this->getAccountParam( 'paypal_public_certificate_path' );
		$paypal_private_key_path		=	$this->getAccountParam( 'paypal_private_key_path' );
		$paypal_public_key_path			=	$this->getAccountParam( 'paypal_public_key_path' );
		$paypal_private_key_password	=	$this->getAccountParam( 'paypal_private_key_password' );

		$tmpDir							=	$this->findATmpDir();
		if ( ( $tmpDir === null ) || ( ! is_dir( $tmpDir ) ) || ! is_writable( $tmpDir ) ) {
			$this->_setLogErrorMSG( 3, $this->account, 'paypal openssl', 'did not find a writable temporary directory (' . $tmpDir . '). Please make sure that your cachepath global CMS setting is a writable directory.' );
			$tmpDir						=	null;
		}

		$h = @getenv('HOME') . "\n";
		if ( ! is_writable( $h ) ) {
			@putenv("HOME=/tmp");		// try avoiding unable to write 'random state'		( http://www.paypaldeveloper.com/pdn/board/message?board.id=ewp&thread.id=110&view=by_date_ascending&page=2 )
		} else {
			$h			=	null;
		}

		if ( extension_loaded( 'openssl' ) && defined( 'OPENSSL_VERSION_TEXT' ) && ( $tmpDir !== null ) ) {

			$clearFile					=	tempnam($tmpDir, 'clr_');
			$signedFile					=	tempnam($tmpDir, 'sign_');
			$encryptedFile				=	tempnam($tmpDir, 'encr_');

			if ( is_readable( $paypal_public_key_path ) && is_readable( $paypal_private_key_path ) && is_readable( $paypal_public_certificate_path ) ) {
				$certificate			=	openssl_x509_read( file_get_contents( $paypal_public_key_path ) );
				$privateKey				=	openssl_pkey_get_private( file_get_contents( $paypal_private_key_path ), $paypal_private_key_password );
				$paypalcert				=	openssl_x509_read( file_get_contents( $paypal_public_certificate_path ) );
				if ( ( $certificate !== false ) && ( $privateKey !== false ) && ( $paypalcert !== false ) ) {
					$privOk				=	openssl_x509_check_private_key( $certificate, $privateKey );
					if ( $privOk ) {
						$out			=	fopen( $clearFile, 'wb' );
						if ( $out !== false ) {
							fwrite( $out, $cleartext );
							fclose( $out );
	
							if ( openssl_pkcs7_sign( $clearFile, $signedFile, $certificate, $privateKey, array(), PKCS7_BINARY ) ) {
								@unlink( $clearFile );
			
								$signedData		=	explode( "\n\n", file_get_contents( $signedFile ) );
				
								$out			=	fopen($signedFile, 'wb');
								if ( $out !== false ) {
									fwrite( $out, base64_decode( $signedData[1] ) );
									fclose( $out );
				
									if ( openssl_pkcs7_encrypt( $signedFile, $encryptedFile, $paypalcert, array(), PKCS7_BINARY ) ) {
										@unlink( $signedFile );
										$encryptedData	=	explode("\n\n", file_get_contents( $encryptedFile ), 2 );
										@unlink( $encryptedFile );

										$return	=	"-----BEGIN PKCS7-----\n"
												.	trim( $encryptedData[1] )
												.	"\n-----END PKCS7-----";
									} else {
										$this->_setLogErrorMSG( 3, $this->account, 'paypal openssl_pkcs7_encrypt(signedFile,paypal_public_cer) ', 'returns an error on signature.' );
									}
								} else {
									$this->_setLogErrorMSG( 3, $this->account, 'paypal openssl open ', $signedFile . ' returns an error creating it.' );
								}
							} else {
								$this->_setLogErrorMSG( 3, $this->account, 'paypal openssl_pkcs7_sign(message,your_private_key)', 'returns an error.' );
							}
						} else {
							$this->_setLogErrorMSG( 3, $this->account, 'paypal openssl open ', $clearFile . ' returns an error creating it.' );
						}	
					} else {
						$this->_setLogErrorMSG( 3, $this->account, 'paypal openssl_pkcs7_sign(message,your_private_key)', 'returns an error.' );
					}
				} else {
					if ( $certificate === false ) {
						$this->_setLogErrorMSG( 3, $this->account, 'paypal openssl_x509_read(your_public_key)', 'returns an error.' );
					}
					if ( $privateKey === false ) {
						$this->_setLogErrorMSG( 3, $this->account, 'paypal openssl_pkey_get_private(your_private_key)', 'returns an error. Maybe wrong password for private key ?' );
					}
					if ( $paypalcert === false ) {
						$this->_setLogErrorMSG( 3, $this->account, 'paypal openssl_x509_read(paypal_public_certificate)', 'returns an error.' );
					}
				}
			} else {
				$this->_setLogErrorMSG( 3, $this->account, 'paypal openssl tempnam()', 'returns unwritable filepaths (' . $clearFile . ')' );
			}

		}
		if ( $return === false ) {
			if ( function_exists( 'is_executable' ) ) {
				$configPath	=	$this->params->get( 'openssl_exec_path', '/usr/bin/openssl' );
				$paths = array( '/usr/bin/openssl', '/usr/local/bin/openssl', 'openssl' );
				if ( $configPath ) {
					array_unshift( $paths, $configPath );
				}
				foreach ($paths as $path) {
					if ( @is_executable( $path ) ) {
						// openssl found:
						$paypal_openssl_path	=	$path;
						break;
					}
				}
			}

			if ( @is_executable( $paypal_openssl_path ) ) {

				$openssl_cmd	=	$paypal_openssl_path . ' smime -sign -signer ' .$paypal_public_key_path
								.	' -inkey ' . $paypal_private_key_path
								.	' -outform der -nodetach -binary -passin pass:' . $paypal_private_key_password
								.	' | '
								.	$paypal_openssl_path . ' smime -encrypt -des3 -binary -outform pem ' . $paypal_public_certificate_path;

				$descriptors	=	array(	0 => array('pipe', 'r'),
											1 => array('pipe', 'w'),
											2 => array('pipe', 'w') );

				$pipes			=	null;
				$process		=	@proc_open( $openssl_cmd, $descriptors, $pipes );				// PHP 4.3.0 required for paypal encryption !

				if (is_resource($process)) {
					@fwrite( $pipes[0], $cleartext );
					@fflush( $pipes[0] );
					@fclose( $pipes[0] );
		
					$output		=	'';
					while ( ! feof( $pipes[1] ) ) {
						$output	.=	@fgets( $pipes[1] );
					}
					$error		=	'';
					while ( ! feof( $pipes[2] ) ) {
						$error	.=	@fgets( $pipes[2] );
					}
					$error		=	trim( $error );
	
					@fclose( $pipes[1] );
					@fclose( $pipes[2] );
					@proc_close( $process );
					
					if ( $error ) {
						$this->_setLogErrorMSG( 3, $this->account, 'paypal openssl executable error', $error );
					}
					$return		=	trim( $output );
				} else {
					$this->_setLogErrorMSG( 5, $this->account, 'paypal openssl executable', 'could not start with proc_open' );
				}
			}
		}

		if ( $h ) {
			@putenv( "HOME=" . $h );
		}
		return $return;
	}

	/**
	 * Encrypts Paypal if set so in params.
	 *
	 * @param array $varsArray
	 */
	private function _paypalEncryptIfOk( &$varsArray )
	{
		if ( $this->getAccountParam( 'paypal_encrypted', 0 ) != 1 ) {
			return;
		}
		$cleartext		=	'cert_id=' . $this->getAccountParam( 'paypal_certificate_id' ) . "\n";
		foreach ( $varsArray as $k => $v ) {
			$cleartext	.=	$k . '=' . $v . "\n";
		}

		$encrypted		=	$this->_paypalEncrypt( $cleartext );
		if ( $encrypted ) {
			$varsArray	=	array( 'cmd' => '_s-xclick', 'encrypted' => $encrypted );
		} else {
			trigger_error( 'openssl encryption failed.', E_USER_WARNING );
		}
	}

	/**
	 * Limits and converts periods to Paypal limits
	 *
	 * @param  array  $periodTypeArray  ( int $value, string $periodCOde ) : $periodCode: 'D','W','M','Y'
	 * @return array  same encoding, but limited
	 */
	private function _paypalPeriodsLimits( $periodTypeArray )
	{
		$p		=	$periodTypeArray[0];
		$t		=	$periodTypeArray[1];

		if ( ( $t == 'D' ) && ( $p > 90 ) ) {
			$t	=	'W';
			$p	=	floor( $p / 7 );
		}
		if  ( ( $t == 'W' ) && ( $p > 52 ) ) {
			$t	=	'M';
			$p	=	floor( $p * 12 / 52 );
		}
		if ( ( $t == 'M' ) &&  ( $p > 24 ) ) {
			$t	=	'Y';
			$p	=	floor( $p / 12 );
		}
		if ( ( $t == 'Y' ) &&  ( $p > 5 ) ) {
			$t	=	'Y';
			$p	=	5;
		}
		return array( $p, $t );
	}

	/**
	 * Returns URL of logo image to pass to paypal for checkout page
	 *
	 * @return string
	 */
	private function getImageUrl( )
	{
		global $_CB_framework;

		$image_url			=	trim( $this->getAccountParam( 'paypal_regLogoImage' ) );
		if ( $image_url && ! cbStartOfStringMatch( $image_url, 'http' ) ) {
			$image_url 		=	$_CB_framework->getCfg( 'live_site' ) . '/' . $image_url;
		}
		return $image_url;
	}

	/**
	 * Populates the address fields of $varsArray corresponding to $paymentBasket taking in account the known string length limits of Paypal
	 * 
	 * @param  array                $varsArray
	 * @param  cbpaidPaymentBasket  $paymentBasket        Payment basket to pay
	 */
	private function _populateAddress( &$varsArray, $paymentBasket )
	{
		$varsArray['mrb']				=	'8UK64PPCMZE58';			// our mrb	
		$varsArray['bn']				=	'Joomlapolis_Cart_WPS';		// our bn code per email of Greg Campagnolo of 21 avril 2012 02:39:24 and 1 mai 2012 21:33:22
		// Recommended anti-fraud fields:
		if ( $this->getAccountParam( 'givehiddenemail', 1 ) && ( strlen( $paymentBasket->payer_email ) <= 50 ) ) {
			$varsArray['email']			=	$paymentBasket->payer_email;
		}
		if ( $this->getAccountParam( 'givehiddenphonenumber', 1 ) ) {
			$varsArray['night_phone_b']	=	$paymentBasket->contact_phone;
		}
		if ( $this->getAccountParam( 'givehiddenaddress', 1 ) ) {
			cbimport( 'cb.tabs' );				// needed for cbIsoUtf_substr()
			$addressFields				=	array(	'address1'		=> array( $paymentBasket->address_street, 100 ),
													'city'			=> array( $paymentBasket->address_city, 40 ),
													'country'		=> array( $this->countryToLetters( $paymentBasket->address_country, 2 ), 2 ),
													'first_name'	=> array( $paymentBasket->first_name, 32 ),
													'last_name'		=> array( $paymentBasket->last_name, 32 ),
													'zip'			=> array( $paymentBasket->address_zip, 32 ),
											);
			if ( $paymentBasket->address_state != 'other' ) {
				$addressFields['state']	=	array( substr( $paymentBasket->address_state, -2 ), 2 );
			}
			foreach ( $addressFields as $k => $value_maxlength ) {
				$adrField				=	cbIsoUtf_substr( $value_maxlength[0], 0, $value_maxlength[1] );
				if ( $adrField ) {
					$varsArray[$k]		=	$adrField;
				}
			}
		}
	}

	/**
	 * Computes request variables for paypal subscription cancellation/post
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket        Payment basket to pay
	 * @return array  of string
	 */
	private function _getPaymentBasketSubscriptionCancel( $paymentBasket )
	{
		$varsArray	=	array(	
	//						'business'		=>	trim($this->getAccountParam( 'paypal_business' )),
							'alias'			=>	trim($this->getAccountParam( 'paypal_business' )),
							'rm'			=>	'2',												//( $redirectNow ? '1' : '2' ),
							'subscr_id'		=>	$paymentBasket->subscr_id,							//TBD: not really needed
							'cmd'			=>	'_subscr-find'
						);
		if ( $this->getAccountParam( 'paypal_page_style' ) ) {
			$varsArray['page_style']		=	$this->getAccountParam( 'paypal_page_style' );
		}
		$image_url							=	$this->getImageUrl();
		if ( $image_url ) {
			$varsArray['image_url']			=	$image_url;
		}
		if ( $this->getAccountParam( 'paypal_country' ) ) {
			$varsArray['lc']				=	$this->getAccountParam( 'paypal_country' );
		}
		if ( $paymentBasket->invoice ) {
			$varsArray['invoice']			=	$paymentBasket->invoice;
		}
		//TBD: There is a Paypal bug avoiding this possibility:			$this->_paypalEncryptIfOk( $varsArray );
		return $varsArray;
	}

	/**
	 * Converts $string $from charset to $to charset and returns result
	 * 
	 * @param  string  $string  String in $from charset
	 * @param  string  $from    'UTF-8', 'ISO-8859-1', ...
	 * @param  string  $to      'UTF-8', 'ISO-8859-1', ...
	 * @return string           String in $to charset
	 */
	private function _charsetConv( $string, $from, $to )
	{
		if ( ( $from == 'UTF-8' ) && ( strncmp( $to, 'ISO-8859-1', 9 ) == 0 ) ) {
			return utf8_decode( $string );
		} elseif ( ( $to == 'UTF-8' ) && ( strncmp( $from, 'ISO-8859-1', 9 ) == 0 ) ) {
			return utf8_encode( $string );
		} else {
			return html_entity_decode( htmlentities( $string, ENT_NOQUOTES, $from ), ENT_NOQUOTES, $to );
		}
	}

	/**
	 * Copies relevant $ipn parameters into $paymentBasket
	 *
	 * @param  cbpaidPaymentNotification  $ipn
	 * @param  cbpaidPaymentBasket        $paymentBasket
	 * @return void
	 */
	protected function _bindIpnToBasket( $ipn, &$paymentBasket )
	{
		/* Copy:
			'test_ipn', 'payer_id', 'payer_status', 'residence_country', 'business', 'receiver_email',
			'receiver_id', 'custom', 'memo', 'auth_id', 'auth_exp', 'auth_amount', 'auth_status', 'parent_txn_id',
			'payment_method', 'gateway_account', 'payment_date' (IF COMPLETED), 'payment_type', 'pending_reason', 'reason_code', 'sale_id', 'txn_id', 'txn_type',
			'subscr_date', 'subscr_effective', 'recurring', 'reattempt', 'retry_at', 'recur_times', 'username', 'password',
			'subscr_id' )
		*/
		// Not copied on purpose:
		// - charset
		// Should be OK to copy:
		// - payment_method
		// - gateway_account
		parent::_bindIpnToBasket( $ipn, $paymentBasket );
		// copying missing as particular to this gateway: payer_email, receipt_id
		if ( $ipn->payer_email ) {
			$paymentBasket->payer_email		=	$ipn->payer_email;
		}
		if ( $ipn->receipt_id ) {
			$paymentBasket->receipt_id		=	$ipn->receipt_id;
		}

		if ( $ipn->payment_status === 'Completed' ) {
			// Correct the payment basket with tax set at and provided by the gateway:
			$mc_gross						=	(float) $ipn->mc_gross;
			$tax							=	(float) $ipn->tax;
			if ( ( $tax > 0 ) && ( $paymentBasket->mc_gross < $mc_gross ) && ( ( $paymentBasket->mc_gross - ( $mc_gross - $tax ) ) < 0.00001 ) ) {
				$paymentBasket->mc_gross	+=	$tax;
			}
		}
		// Correct the payment basket with payment fees provided by the gateway:
		$mc_fee								=	(float) $ipn->mc_fee;
		if ( $mc_fee != 0.0 ) {
			$paymentBasket->mc_fee			+=	$mc_fee;
		}
	}

	/**
	 * Unescapes from PHP escaping algorythm if magic_quotes are set
	 *
	 * @param  string  string
	 * @return string
	 */
	private function cbGetUnEscaped( $string )
	{
		if (get_magic_quotes_gpc()==1) {
			// if (ini_get('magic_quotes_sybase')) return str_replace("''","'",$string);
			return ( stripslashes( $string ));			// this does not handle it correctly if magic_quotes_sybase is ON.
		} else {
			return ( $string );
		}
	}

	/**
	 * Handle Paypal IPN
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	 * @return string                               HTML to display if frontend, text to return to gateway if notification, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	 */
	private function hanldePaypalIPN( $paymentBasket, $postdata )
	{
		global $_CB_framework, $_CB_database, $_GET, $_POST;

		$ret							=	null;
		if ( ( cbGetParam( $_GET, 'result' ) == 'notify' ) || ( ( cbGetParam( $_GET, 'result' ) == 'success') && isset( $postdata['txn_type'] ) ) ) {
			// cbGetParam( $_GET, 'result' ) == 'notify'	:	IPN : We got an Instant Payment Notification (IPN) from Paypal :
			// isset($postdata['txn_type'])	:	We got returned to website with a Post ! This means PDT is not enabled, or website is not public ! so we check anyway to make sure:

			/// I P N :		Process Instant Payment Notification (IPN):

			$ipn						=	new cbpaidPaymentNotification($_CB_database);
			$ipn->bind( $postdata );

			$getExport					=	var_export( $_GET, true );		/* cbGetParam() not needed: we want raw info */
			$postExport					=	var_export( $_POST, true );		/* cbGetParam() not needed: we want raw info */
			if ( $ipn->charset && ( $ipn->charset != $_CB_framework->outputCharset() ) ) {
				$getExport				=	$this->_charsetConv( $getExport, $ipn->charset, $_CB_framework->outputCharset() );
				$postExport				=	$this->_charsetConv( $postExport, $ipn->charset, $_CB_framework->outputCharset() );
			}

			$ipn->payment_method		=	$this->getPayName();
			$ipn->gateway_account		=	$this->getAccountParam( 'id' );
			$ipn->log_type				=	'I';
			$ipn->time_received			=	date('Y-m-d H:i:s', $_CB_framework->now() );
			$ipn->raw_data				=	/* cbGetParam() not needed: we want raw info */ '$_GET=' . $getExport . ";\n";
			$ipn->raw_data				.=	/* cbGetParam() not needed: we want raw info */ '$_POST=' . $postExport . ";\n";
			$ipn->ip_addresses			=	cbpaidRequest::getIPlist();
			$ipn->user_id				=	(int) cbGetParam( $_GET, 'user', 0 );
			$ipn->payment_basket_id		=	(int) $ipn->custom;
			
			$_CB_database->insertObject( $ipn->getTableName(), $ipn, $ipn->getKeyName());

			// read the post from PayPal system and add 'cmd' and post back to PayPal system to validate
			$formvars = array( 'cmd' => '_notify-validate' );
			foreach ($postdata as $key => $value) {
				$formvars[$key]			=	$this->cbGetUnEscaped( $value );
			}

			$results = null;
			$status = null;
			$error = $this->_httpsRequest( $this->_paypalUrl() . '/cgi-bin/webscr', $formvars, 30, $results, $status, 'post', 'normal', '*/*', true, 443, '', '', true, null );
			$transaction_info = urldecode($results);

			if ( $error || $status != 200 ) {
				$ipn->raw_result = 'COMMUNICATION ERROR';
//				$ipn->raw_data = 'Error: '. $error . ' Status: ' . $status . ' Transaction info: ' . $transaction_info;
				$ipn->raw_data			.=	'$error=\''. $error . "';\n";
				$ipn->raw_data			.=	'$status=\'' . $status . "';\n";
				$ipn->raw_data			.=	'$transaction_info=\'' . $transaction_info . "';\n";
				$ipn->log_type			=	'D';
				$_CB_database->updateObject( $ipn->getTableName(), $ipn, $ipn->getKeyName(), false );
				$this->_setLogErrorMSG( 3, $ipn, 'Paypal: Error at notification received: could not reach Paypal gateway for notification check at ' . $this->_paypalUrl() . '. ' . $ipn->raw_result, null );
				// Returns error 500 to paypal, so paypal can retry to reach us later
				header('HTTP/1.0 500 Internal Server Error');
				exit();
			} else {
				$ipn->raw_result		=	$transaction_info;
			}
			$_CB_database->updateObject( $ipn->getTableName(), $ipn, $ipn->getKeyName(), false);

			if (strcmp ($transaction_info, 'VERIFIED') == 0) {

				if ( isset( $ipn->charset ) ) {
					if ( strtolower( $ipn->charset ) != strtolower( $_CB_framework->outputCharset() ) ) {
						foreach ( array_keys( $postdata ) as $k ) {
							if ( isset( $ipn->$k ) && is_string( $ipn->$k ) ) {
								$ipn->$k					=	$this->_charsetConv( $ipn->$k, $ipn->charset, $_CB_framework->outputCharset() );
							}
						}
						$ipn->charset						=	$_CB_framework->outputCharset();
					}			
				}
				
				$paymentBasketId							=	(int) $ipn->custom;
				$autorecurring_type							=	( in_array( $ipn->txn_type, array( 'subscr_payment', 'subscr_signup', 'subscr_modify', 'subscr_eot', 'subscr_cancel', 'subscr_failed' ) ) ? 2 : 0 );
				$exists = $paymentBasket->load( (int) $paymentBasketId );
				if ( $exists ) {		//  && ( ( $paymentBasket->payment_status != $ipn->payment_status ) || ( $autorecurring_type && ( $paymentBasket->txn_id != $ipn->txn_id ) ) ) ) {
					$this->_fixPayPalIpnBugs( $ipn, $paymentBasket );
					$noFraudCheckResult						=	$this->_checkNotPayPalFraud( $ipn, $paymentBasket, cbGetParam( $_REQUEST, 'cbpid', '' ) );
					if ( $noFraudCheckResult === true ) {
						if ( ( $ipn->txn_type == 'web_accept' ) || ( $ipn->txn_type == '' ) || $autorecurring_type ) {		// refunds don't have txn_type (but if we are here, the IPN is already VERIFIED !
							$paypalUserChoicePossible		=	( ( $this->getAccountParam( 'enabled', 0 ) == 3 ) && ( $paymentBasket->isAnyAutoRecurring() == 2 ) );
							$autorenew_type					=	( $autorecurring_type ? ( $paypalUserChoicePossible ? 1 : 2 ) : 0 );
							if ( $autorecurring_type && ( $ipn->txn_type == 'subscr_signup' ) && ( ( $paymentBasket->period1 ) && ( $paymentBasket->mc_amount1 == 0 ) ) && ( $ipn->payment_status == '' ) ) {
								$ipn->payment_status		=	'Completed';	// 'FreeTrial'
							}
							if ( ( $ipn->payment_status == 'Refunded' ) && ( $paymentBasket->mc_gross != - $ipn->mc_gross ) ) {
								// Fix a paypal server bug: we are receiving payment_status 'Refunded' instead of 'Partially-Refunded':
								// needed to be fixed as follows to avoid loosing the subscriptions:
								$ipn->payment_status		=	'Partially-Refunded';
								//TBD : if we have several partial refunds, we should still say 'Refunded' for the one refunding the last chunk...
							}
							$this->_bindIpnToBasket( $ipn, $paymentBasket );
							$paymentBasket->payment_method	=	$this->getPayName();
							$paymentBasket->gateway_account	=	$this->getAccountParam( 'id' );
// $this->_setLogErrorMSG( 6, $ipn, 'Paypal DEBUG1', ' autorecurring_type:' . var_export( $autorecurring_type, true ) . ' autorenew_type:' . var_export( $autorenew_type, true ) . ' txn_type:' . var_export( $ipn->txn_type, true ) );
							$this->updatePaymentStatus( $paymentBasket, $ipn->txn_type, $ipn->payment_status, $ipn, 1, $autorecurring_type, $autorenew_type, false );
							
							if ( cbGetParam( $_GET, 'result' ) == 'success' ) {		// in case of return to website with a POST, issue warning to enabel PDT (warn also for IPN as probably the setting is also wrong!)
								$this->_setLogErrorMSG( 4, $ipn, 'Paypal: Got POST successful return from Paypal', CBPTXT::T("Please tell sysadmin to enable IPN and PDT in his Paypal account.") );
							}
						} elseif ( $ipn->txn_type == 'new_case' ) {
							$ipn->log_type					=	'H';	// IPN New case
						} else {
							$ipn->log_type					=	'T';	// IPN Wrong TYPE
							if ( cbGetParam( $_GET , 'result' ) == 'success' ) {
								$errorText					=	CBPTXT::T("Error: this is not a payment yet, type received is: ") . htmlspecialchars( $ipn->txn_type ) . '. ' . CBPTXT::T("Please tell sysadmin to enable IPN and PDT in his Paypal account.");
								$this->_setLogErrorMSG( 3, $ipn, 'Paypal: Unexpected type received from Paypal', $errorText );
							}
						}
					} else {
						$ipn->log_type						=	'F';		// IPN FRAUD detected
						$ipn->raw_result					=	$noFraudCheckResult;
						if ( cbGetParam( $_GET , 'result' ) == 'success' ) {
							$this->_setLogErrorMSG( 3, $ipn, 'Paypal: Fraud attempt or Paypal value mismatch detected by the plugin: ' . $noFraudCheckResult, CBPTXT::T("Error") . ': ' . htmlspecialchars( $ipn->raw_result ) . '. ' . CBPTXT::T("Please tell sysadmin to enable IPN and PDT in his Paypal account.") );
						}
					}
				} else {
					$ipn->log_type							=	'J';
					if ( cbGetParam( $_GET , 'result' ) == 'success' ) {
						if ( ! $exists ) {
							$this->_setLogErrorMSG( 3, $ipn, 'Paypal', CBPTXT::T("Error") . ': ' . CBPTXT::T("Innexistant payment basket") . '. ' . CBPTXT::T("Please tell sysadmin to enable IPN and PDT in his Paypal account.") );
						} else {
							$this->_setLogErrorMSG( 3, $ipn, 'Paypal', CBPTXT::T("Payment basket payment status is already ") . htmlspecialchars( $paymentBasket->payment_status ) . '. ' . CBPTXT::T("Please tell sysadmin to enable PDT in his Paypal account.") );
						}
					}
				}
			} else if (strcmp ($transaction_info, 'INVALID') == 0) {
				// log for manual investigation:
				$ipn->log_type								=	'K';		// KO
				if ( cbGetParam( $_GET , 'result' ) == 'success' ) {
					$this->_setLogErrorMSG( 3, $ipn, 'Paypal: Fraud attempt or Paypal value mismatch detected by Paypal: ', CBPTXT::T("Error") . ': ' . CBPTXT::T("return information didn't validate with paypal. Please tell sysadmin to enable IPN and PDT in his Paypal account.") );
				}
				$_CB_database->updateObject( $ipn->getTableName(), $ipn, $ipn->getKeyName(), false );
				header('HTTP/1.0 500 Internal Server Error');
				exit('INVALID');
			} else {
				$ipn->log_type								=	'M';
			}
			$_CB_database->updateObject( $ipn->getTableName(), $ipn, $ipn->getKeyName(), false );
			
		}
		return $ret;
	}

	/**
	 * Handle Paypal PDT
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	 * @return string                               HTML to display if frontend, text to return to gateway if notification, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	 */
	private function handlePaypalPDT( $paymentBasket, /** @noinspection PhpUnusedParameterInspection */ $postdata )
	{
		global $_CB_framework, $_CB_database, $_GET, $_POST;

		$ret								=	null;
		// The user got redirected back from paypal with a success message:

		if ( isset( $_GET['tx'] ) && isset( $_GET['st'] ) && isset( $_GET['amt'] ) && isset( $_GET['cc'] ) ) {

			/// P D T :		Process Payment Data Transaction (PDT):

			// check if PDT not already processed:
			$pbTmp							=	new cbpaidPaymentBasket( $_CB_database );
			$paymentBasketId				=	(int) $this->_getReqParam('basket');
			if ( $paymentBasketId
				&& $pbTmp->load( (int) $paymentBasketId )
				&& ( $pbTmp->payment_status == cbGetParam( $_GET, 'st' ) )
				&& ( $pbTmp->txn_id == cbGetParam( $_GET, 'tx' ) )
				&& ( $pbTmp->shared_secret ==cbGetParam( $_GET, 'cbpid' ) ) )
			{
				// this PDT has already been treated...probably a Nth reload or bookmarked page:
				$paymentBasket->load( (int) $pbTmp->id );
			} else {
				$ipn						=	new cbpaidPaymentNotification($_CB_database);
				$ipn->payment_method		=	$this->getPayName();
				$ipn->gateway_account		=	$this->getAccountParam( 'id' );
				// done below: $ipn->log_type			= 'R';
				$ipn->time_received			=	date( 'Y-m-d H:i:s', $_CB_framework->now() );
				$ipn->raw_data				=	/* cbGetParam() not needed: we want raw info */ '$_GET=' . var_export( $_GET, true ) . ";\n";
				$ipn->raw_data				.=	/* cbGetParam() not needed: we want raw info */ '$_POST=' . var_export( $_POST, true ) . ";\n";
				$ipn->ip_addresses			=	cbpaidRequest::getIPlist();
				$ipn->user_id				=	$pbTmp->user_id;

				// post back to PayPal system to validate:
				$formvars					=	array(	'cmd'	=> '_notify-synch',
														'tx'	=> cbGetParam( $_REQUEST, 'tx', '' ),
														'at'	=> trim($this->getAccountParam('paypal_identity_token'))
													 );
				$results					=	null;
				$status						=	null;
				$error						=	$this->_httpsRequest( $this->_paypalUrl() . '/cgi-bin/webscr', $formvars, 30, $results, $status, 'post', 'normal', '*/*', true, 443, '', '', true, null );
				$transaction_info			=	urldecode($results);			//FIXME: urldecode is done below already!

				if ( $error || ( $status != 200 ) ) {
					$ipn->raw_result		=	'COMMUNICATION ERROR';
//					$ipn->raw_data = 'Error: '. $error . ' Status: ' . $status . ' Transaction info: ' . $transaction_info;
					$ipn->raw_data			.=	'$error=\''. $error . "';\n";
					$ipn->raw_data			.=	'$status=\'' . $status . "';\n";
					$ipn->raw_data			.=	'$formvars=' . var_export( $formvars, true ) . ";\n";
					$ipn->raw_data			.=	'$transaction_info=\'' . $transaction_info . "';\n";
					$ipn->log_type			=	'E';
					$ipn->time_received		=	date( 'Y-m-d H:i:s', $_CB_framework->now() );
					$_CB_database->insertObject( $ipn->getTableName(), $ipn, $ipn->getKeyName() );

					$this->_setLogErrorMSG( 3, $ipn, 'Paypal: Error at notification received: could not reach Paypal gateway for notification check at ' . $this->_paypalUrl() . '. ' . $ipn->raw_result, null );
					$this->_setErrorMSG( sprintf( CBPTXT::T("Sorry no response for your payment from payment server (error %s). Please check your email and status later."), $error ) );
					$ret					=	false;
				} else {
					// echo $transaction_info;
					$input = explode("\n", $transaction_info);
					foreach ($input as $k => $in) {
						$input[$k]			=	trim( $in, "\n\r" );
					}
					$resultMessage = array_shift( $input );
					$output					=	array();
					foreach ($input as $in) {
						$posEqualSign		=	strpos($in, '=');
						if ($posEqualSign === false) {
							$output[]		=	$in;
						} else {
							$output[substr($in,0,$posEqualSign)]	=	substr($in, $posEqualSign+1);
						}
					}
					if ( isset( $output['charset'] ) && ( $resultMessage == 'SUCCESS' ) ) {
						if ( strtolower( $output['charset'] ) != strtolower( $_CB_framework->outputCharset() ) ) {
							foreach ($output as $k => $v ) {
								$output[$k]		=	$this->_charsetConv( $v, $output['charset'], $_CB_framework->outputCharset() );
							}
							$output['charset']	=	$_CB_framework->outputCharset();
						}			
					}
					$ipn->bind( $output );
					$ipn->raw_result 		=	$resultMessage;
					$ipn->raw_data			.=	'$transaction_info=\'' . $transaction_info . "';\n";
					$ipn->raw_data			.=	'$PDT_RESULT=' . var_export( $output, true ) . ";\n";
					$ipn->payment_basket_id	=	(int) $ipn->custom;
					/*
					if(!$_CB_database->updateObject( $ipn->_tbl, $ipn, $ipn->_tbl_key, false)) {
						echo 'update error:'.htmlspecialchars($_CB_database->stderr(true))."\n";
						exit();
					}
					*/
	
					if ( $resultMessage == 'SUCCESS' ) {
						$ipn->log_type							= 'R';
						$_CB_database->insertObject( $ipn->getTableName(), $ipn, $ipn->getKeyName() );

						$paymentBasketId						=	(int) $ipn->custom;
						$exists									=	$paymentBasket->load( (int) $paymentBasketId );
						if ( $exists ) {
							$this->_fixPayPalIpnBugs( $ipn, $paymentBasket );
							$noFraudCheckResult					=	$this->_checkNotPayPalFraud( $ipn, $paymentBasket, cbGetParam( $_REQUEST, 'cbpid', '' ) );
							if ( $noFraudCheckResult === true ) {
								$autorecurring_type				=	( in_array( $ipn->txn_type, array( 'subscr_payment', 'subscr_signup', 'subscr_modify', 'subscr_eot', 'subscr_cancel', 'subscr_failed' ) ) ? 2 : 0 );
								$paypalUserChoicePossible		=	( ( $this->getAccountParam( 'enabled', 0 ) == 3 ) && ( $paymentBasket->isAnyAutoRecurring() == 2 ) );
								$autorenew_type					=	( $autorecurring_type ? ( $paypalUserChoicePossible ? 1 : 2 ) : 0 );
								$this->_bindIpnToBasket( $ipn, $paymentBasket );
								$paymentBasket->payment_method	=	$this->getPayName();
								$paymentBasket->gateway_account	=	$this->getAccountParam( 'id' );
								$this->updatePaymentStatus( $paymentBasket, $ipn->txn_type, $ipn->payment_status, $ipn, 1, $autorecurring_type, $autorenew_type, false );

							} else {
								$this->_setLogErrorMSG( 3, $ipn, 'Received back from paypal: ' . var_export( $ipn, true ), CBPTXT::T("Payment notification mismatch: ") . $noFraudCheckResult . '.' );
								$ret = false;
								//TBD: update notification record !
								$ipn->log_type					=	'G';		// PDT FRAUD detected
								$ipn->raw_result				=	$noFraudCheckResult;
								$_CB_database->updateObject( $ipn->getTableName(), $ipn, $ipn->getKeyName(), false);
							}
						}
					} elseif ( $resultMessage == 'FAIL' ) {
						$ipn->log_type							= 'L';
						$_CB_database->insertObject( $ipn->getTableName(), $ipn, $ipn->getKeyName() );

						$this->_setLogErrorMSG( 3, $ipn, 'Paypal: Error: Received FAIL result message from Paypal', CBPTXT::T("Sorry your payment has not been processed. Transaction result:")
							. $transaction_info
							. '. ' . CBPTXT::T("Please try again and notify system administrator.") );
						$ret = false;
					} else {
						$ipn->log_type							= 'N';
						$_CB_database->insertObject( $ipn->getTableName(), $ipn, $ipn->getKeyName() );

						$this->_setLogErrorMSG( 3, $ipn, 'Paypal: Error: Received following unknown result message from Paypal: ' . $resultMessage, CBPTXT::T("Sorry no response for your payment. Please check your email and status later.") );
						$ret = false;
					}
				}
			}
		} else {
			// result=success but not a PDT from paypal:

			// it could be a subscription with a free trial period: in that case, as there is no initial transaction, we get returned without txn_id.
			// we must either guess that user subscribed (if he is allowed for free trials, or must wait for IPN:

			$paymentBasketId											=	(int) $this->_getReqParam( 'basket' );
			if ( $paymentBasketId ) {
				if ( $paymentBasket->load( (int) $paymentBasketId ) ) {
					$cbpid												=	cbGetParam( $_REQUEST, 'cbpid', '' );
					if ( $cbpid == $paymentBasket->shared_secret ) {
						$enable_paypal									=	$this->getAccountParam( 'enabled', 0 );
						$isAnyAutoRecurring								=	$paymentBasket->isAnyAutoRecurring();
						$pay1subscribe2									=	$this->_getPaySubscribePossibilities( $enable_paypal, $paymentBasket );
						if ( $isAnyAutoRecurring && ( ( $pay1subscribe2 & 0x2 ) != 0 ) && $paymentBasket->period1 ) {
							// Free first period: Wait for IPN for 20 times 1 second:
							for ( $i = 0; $i < 20; $i++ ) {
								if ( $paymentBasket->load( (int) $paymentBasketId ) ) {
									if ( $paymentBasket->payment_status == 'Completed' ) {
										break;
									}
								} else {
									break;
								}
								sleep( 1 );
							}
							if ( $paymentBasket->payment_status != 'Completed' ) {
								if ( ( $isAnyAutoRecurring == 1 ) || ( ( $isAnyAutoRecurring == 2 ) && ( $paymentBasket->mc_amount1 != 0 ) ) )  {
									// 1: forced subscription: error if no IPN came to update payment basket:
									// 2: not forced subscription but no free initial value: we really need IPN to know status:
										$this->_setErrorMSG(CBPTXT::T("Sorry, payment has not been confirmed by Paypal (no IPN received). IPN must be enabled for auto-recurring payment subscriptions."));
								} else {
									// user-choice: no need to wait for payment basket completed to activate subscriptions:

									$ipn								=	new cbpaidPaymentNotification($_CB_database);
									$ipn->payment_method				=	$this->getPayName();
									$ipn->gateway_account				=	$this->getAccountParam( 'id' );
									$ipn->log_type						=	'S';
									$ipn->time_received					=	date( 'Y-m-d H:i:s', $_CB_framework->now() );
									$ipn->raw_data						=	/* cbGetParam() not needed: we want raw info */ '$_GET=' . var_export( $_GET, true ) . ";\n";
									$ipn->raw_data						.=	/* cbGetParam() not needed: we want raw info */ '$_POST=' . var_export( $_POST, true ) . ";\n";
									$ipn->ip_addresses					=	cbpaidRequest::getIPlist();
									$ipn->payment_basket_id				=	$paymentBasket->id;
									$ipn->user_id						=	$paymentBasket->user_id;
									$_CB_database->insertObject( $ipn->getTableName(), $ipn, $ipn->getKeyName() );

									if ( $isAnyAutoRecurring == 2 )  {
										$autorecurring_type				=	2;
										$paypalUserChoicePossible		=	( ( $this->getAccountParam( 'enabled', 0 ) == 3 ) && ( $paymentBasket->isAnyAutoRecurring() == 2 ) );
										$autorenew_type					=	( $autorecurring_type ? ( $paypalUserChoicePossible ? 1 : 2 ) : 0 );
									} else {
										$autorecurring_type				=	0;
										$autorenew_type					=	0;
									}
									$paymentBasket->payment_method		=	$this->getPayName();
									$paymentBasket->gateway_account		=	$this->getAccountParam( 'id' );

									$this->updatePaymentStatus( $paymentBasket, 'web_accept', 'Completed', $ipn, 1, $autorecurring_type, $autorenew_type, false );
								}
							}
						}
					}
				}
			}


		}
		return $ret;
	}

	/**
	 * Checks if a Paypal API is set
	 *
	 * @return boolean
	 */
	private function hasPaypalApi( )
	{
		return ( $this->getAccountParam( 'paypal_api_username' ) != '' );
	}

	/**
	 * Instanciator for cbpaidPaypalApi class for this gateway
	 *
	 * @return cbpaidPaypalApi
	 */
	private function getPaypalApi( )
	{
		if ( ! $this->_paypalApi ) {
			$paypal_api_username	=	$this->getAccountParam( 'paypal_api_username' );
			$paypal_api_password	=	$this->getAccountParam( 'paypal_api_password' );
			$paypal_api_signature	=	$this->getAccountParam( 'paypal_api_signature' );

			$apiUrl					=	str_replace( 'www', 'api-3t', $this->_paypalUrl() . '/nvp' );
			$this->_paypalApi		=	new	cbpaidPaypalApi( $paypal_api_username, $paypal_api_password, $paypal_api_signature, $apiUrl );
		}
		return $this->_paypalApi;
	}

	/**
	 * Logs all PayPal API errors collected up to now to $this->_setLogErrorMSG
	 *
	 * @param  cbpaidTable|null  $object
	 * @return void
	 */
	protected function setLogPaypalApiErrors( $object = null )
	{
		foreach ( $this->getPaypalApi()->getErrors() as $e ) {
			$this->_setLogErrorMSG( $e[0], $e[1] ? $e[1] : $object, $e[2], $e[3] );
		}
	}
}	// end class cbpaidpaypal.

/**
 * Paypal API handler class
 */
class cbpaidPaypalApi
{
	private $version = '85.0';		// February 2012	https://cms.paypal.com/cms_content/US/en_US/files/developer/PP_NVPAPI_DeveloperGuide.pdf
	protected $apiCredentials;
	protected $apiUrl;
	/**
	 * @var array
	 */
	protected $errors			=	array();
	/**
	 * @var array
	 */
	protected $rawlogs			=	array();

	/**
	 * Constructor
	 *
	 * @param  string  $api_username
	 * @param  string  $api_password
	 * @param  string  $api_signature
	 * @param  string  $apiUrl
	 * @return cbpaidPaypalApi
	 */
	public function __construct( $api_username, $api_password, $api_signature, $apiUrl )
	{
		$this->apiCredentials	=	array(	'VERSION'	=>	$this->version,
											'USER'		=>	$api_username,
											'PWD'		=>	$api_password,
											'SIGNATURE'	=>	$api_signature
										 );
		$this->apiUrl			=	$apiUrl;
	}

	/**
	 * Gets the logged errors and resets error log.
	 *
	 * @return array
	 */
	public function getErrors( )
	{
		$errors				=	$this->errors;
		$this->errors		=	array();
		return $errors;
	}

	/**
	 * Gets the raw log data (string or array) for notifications logging
	 *
	 * @param  boolean            $asString  Return the logs as string instead of as array
	 * @return array|string|null             Logs
	 */
	public function getRawLogData( $asString = true )
	{
		if ( $asString ) {
			$logs			=	null;
			foreach ( $this->rawlogs as $log ) {
				foreach ( $log as $k => $v ) {
					$logs	.=	'$' . $k . '=' . var_export( $v, true ) . ";\n";
				}
			}
		} else {
			$logs			=	$this->rawlogs;
		}
		$this->rawlogs		=	array();
		return $logs;
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
	protected function setError( $log_priority, $object, $logMessagePrefix, $userMessage )
	{
		$this->errors[]		=	array( $log_priority, $object, $logMessagePrefix, $userMessage );
	}

	/**
	 * Logs the API_REQUEST $request and API_REPLY $results
	 *
	 * @param $request
	 * @param $results
	 */
	protected function setRawLogData( $request, $results )
	{
		$this->rawlogs[]	=	array( 'API_REQUEST' => $request, 'API_REPLY' => $results );
	}

	/**
	 * Calls PayPal API on an NVP basis
	 *
	 * @param  array          $specificVars  Request vars (beyond API credentials)
	 * @return array|boolean                 Results if array, FALSE: error (logged)
	 */
	protected function callAPI( $specificVars )
	{
		$formvars					=	array_merge( $this->apiCredentials, $specificVars );

		//Send the XML via curl:
		$response					=	null;
		$status						=	null;
		$error						=	cbpaidWebservices::httpsRequest( $this->apiUrl, $formvars, 105, $response, $status, 'post', 'normal' );
		if( $error || ( $status != 200 ) || ! $response ) {
			// Error, log it:
			$this->setError( 3, null, 'Paypal API: Error ' . $error . ' Status ' . $status . ': could not reach Paypal API gateway at ' . $this->apiUrl . '.', CBPTXT::T("Paypal API gateway could not be reached.") . ' ' . CBPTXT::T("Please ask administrator to check his error log for details.") );
			return false;
		} else {
			// API call success:
			$results				=	array();
			parse_str( $response, $results );
			//?	foreach ( $results as &$v ) {
			//?		$v		=	urldecode( $v );
			//?	}

			// Remove sensitive data from raw logs:
			$formvars['USER']		=	'USER';
			$formvars['PWD']		=	'PWD';
			$formvars['SIGNATURE']	=	'SIGNATURE';
			// Log raw data:
			$this->setRawLogData( $formvars, $results );
			return $results;
		}
		
	}

	/**
	 * Decodes the ERRORCODE and SHORTERRORMESSAGE and LONGERRORMESSAGE received back from PayPal API
	 *
	 * @param array         $results          Results of API call
	 * @param string|null   $detailsForAdmin  OUTPUT: details for admin logging, including CORRELATIONID and TIMESTAMP
	 * @return string|null                    User-displayable error message, NULL if no errors found
	 */
	protected function decodeErrorsToStrings( $results, &$detailsForAdmin = null )
	{
		$errors					=	array();
		$details				=	array();
		for ( $i = 0; true; $i++ ) {
			if ( isset( $results['L_ERRORCODE' . $i] ) ) {
				$arr			=	array(	'[SHORTERRORMESSAGE]'	=>	$results['L_SHORTMESSAGE' . $i],
											'[LONGERRORMESSAGE]'	=>	$results['L_LONGMESSAGE' . $i],
											'[ERRORCODE]'			=>	$results['L_ERRORCODE' . $i],
										 );
				$errors[]		=	CBPTXT::P("[SHORTERRORMESSAGE]: [LONGERRORMESSAGE]", $arr );
				$details[]		=	CBPTXT::P("[SHORTERRORMESSAGE]: [LONGERRORMESSAGE] (error code [ERRORCODE])", $arr );
			} else {
				break;
			}
		}
		if ( count( $errors ) ) {
			if ( isset( $results['CORRELATIONID'] ) ) {
				$correlation	=	 ' ' . CBPTXT::P("with Paypal API correlation id [CORRELATIONID]", array( '[CORRELATIONID]' => $results['CORRELATIONID'] ) );
				if ( isset( $results['TIMESTAMP'] ) ) {
					$correlation =	 ' ' . CBPTXT::P("at time [TIMESTAMP]", array( '[TIMESTAMP]' => $results['TIMESTAMP'] ) );
				}
			} else {
				$correlation	=	null;
			}
			$detailsForAdmin	=	implode( ', ', $details ) . $correlation;

			return implode( ', ', $errors );
		}
		return null;
	}

	/**
	 * Format array $vars as key:value,... text
	 *
	 * @param  array   $vars  Keyed values
	 * @return string         Result
	 */
	private function _formatVars( $vars )
	{
		$text			=	'';
		foreach ( $vars as $k => $v ) {
			if ( $v ) {
				$text	.=	( $text ? ', ' : '' ) . $k . ': ' . $v;
			}
		}
		return $text;
	}

	/**
	 * Evaluates $results depending on the ACK value
	 *
	 * @param  array  $specificVars
	 * @param  array  $results
	 * @param  string $text
	 * @return bool
	 */
	protected function evaluateResults( $specificVars, $results, $text )
	{
		$ret								=	false;
		if ( $results ) {
			if ( isset( $results['ACK'] ) ) {
				switch ( $results['ACK'] ) {

					case 'SuccessWithWarning':
						$detailsForAdmin	=	null;
						$this->decodeErrorsToStrings( $results, $detailsForAdmin );
						$this->setError( 6, null, sprintf( 'Paypal API %s: Successful but with warning: %s.', $this->_formatVars( $specificVars ), $detailsForAdmin ), CBPTXT::P("[APIMETHODD] successful, but notice returned by gateway.", array( '[APIMETHODD]' => $text ) ) . ' ' . CBPTXT::T("Please ask administrator to check his error log for details.") );
						$ret				=	true;
						break;

					case 'Success':
						$ret				=	true;
						break;

					case 'FailureWithWarning':
					case 'Failure':
					default:
						$detailsForAdmin	=	null;
						$error				=	$this->decodeErrorsToStrings( $results, $detailsForAdmin );
						$this->setError( 3, null, sprintf( 'Paypal API %s: Failed with error: %s. REQUEST: %s, REPLY: %s', $this->_formatVars( $specificVars ), $detailsForAdmin, var_export( $specificVars, true ), var_export( $results, true ) ), CBPTXT::P("[APIMETHODD] failed: [ERRORMESSAGE]", array( '[APIMETHODD]' => $text, '[ERRORMESSAGE]' => $error ) ) );
						$ret				=	false;
						break;
				}
			}
		}
		return $ret;
	}

	/**
	 * The RefundTransaction API operation issues a refund to the PayPal account holder associated with a transaction.
	 * $refundType :
	 *  Full			– Full refund (default).
	 *  Partial			– Partial refund
	 *  ExternalDispute – External dispute
	 *  Other			– Other type of refund. (Value available since version 82.0)
	 * 
	 * $results :
	 *  REFUNDTRANSACTIONID	Unique transaction ID of the refund
	 *  FEEREFUNDAMT	    Transaction fee refunded to original recipient of payment
	 *  GROSSREFUNDAMT		Amount refunded to the original payer
	 *  NETREFUNDAMT		Amount subtracted from PayPal balance of the original recipient of payment, to make this refund
	 *  TOTALREFUNDEDAMT	Total of all refunds associated with this transaction
	 *  CURRENCYCODE		Currency code
	 *  REFUNDINFO			Contains refund payment status information
	 *  REFUNDSTATUS		"instant", "delayed"
	 *  PENDINGREASON		"none", "echeck"
	 * 
	 * @param  string  $transactionId   Unique identifier of the transaction to be refunded.
	 * @param  string  $refundType      Type of refund you are making: ( "Full", "Partial" )
	 * @param  string  $currencyCode    Currency code. This field is required for partial refunds. Not used for full refunds.
	 * @param  float   $amount          Refund amount. The amount is required if RefundType is Partial. Not used for full refunds.
	 * @param  string  $note            (Optional) Custom memo about the refund. 255 characters single-byte characters limit.
	 * @param  array   $results         RETURNED: Refund request results:
	 * @return boolean                  TRUE: Succeeded, FALSE: Error, available in function getErrors()
	 */
	public function RefundTransaction( $transactionId, $refundType, $currencyCode, $amount, $note, &$results )
	{
		$specificVars						=	array(	'METHOD'		=>	__FUNCTION__,
														'TRANSACTIONID'		=>	$transactionId,
														'REFUNDTYPE'		=>	$refundType );
		if ( $refundType != 'Full' ) {
			$specificVars['CURRENCYCODE']	=	$currencyCode;
			$specificVars['AMT']			=	sprintf( '%.02f', $amount );
		}
		if ( $note ) {
			$specificVars['NOTE']			=	$note;
		}
		$results							=	$this->callAPI( $specificVars );
		$ret								=	$this->evaluateResults( $specificVars, $results, ( $refundType == 'Full' ) ? CBPTXT::T("Refund of payment") :  CBPTXT::T("Partial of payment") );
		if ( $ret ) {
			
		}
		return $ret;
	}

	/**
	 * PayPal API Call ManageRecurringPaymentsProfileStatus
	 *
	 * @param  string  $profileId  PayPal payment subscription profile-id
	 * @param  string  $action     PayPal API ACTION
	 * @param  string  $note       PayPal API NOTE   (optional)
	 * @return boolean
	 */
	public function ManageRecurringPaymentsProfileStatus( $profileId, $action, $note )
	{
		$specificVars				=	array(	'METHOD'		=>	__FUNCTION__,
												'PROFILEID'		=>	$profileId,
												'ACTION'		=>	$action );
		if ( $note ) {
			$specificVars['NOTE']	=	$note;
		}
		$results					=	$this->callAPI( $specificVars );
		$ret						=	$this->evaluateResults( $specificVars, $results, CBPTXT::T("Unsubscription from auto-recurring payments") );
		return $ret;
	}
}
/**
 * Payment account class for this gateway: Stores the settings for that gateway instance, and is used when editing and storing gateway parameters in the backend.
 *
 * No methods need to be implemented or overriden in this class, except to implement the private-type params used specifically for this gateway:
 */
class cbpaidGatewayAccountpaypal extends cbpaidGatewayAccounthostedpage
{
}
