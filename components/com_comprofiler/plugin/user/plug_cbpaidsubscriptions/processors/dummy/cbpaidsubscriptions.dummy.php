<?php
/**
* @version $Id: cbpaidsubscriptions.dummy.php 1581 2012-12-24 02:36:44Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
				
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
class cbpaiddummy extends cbpaidCreditCardsPayHandler
{
	/**
	 * Gateway API version used
	 * @var int
	 */
	public $gatewayApiVersion	=	"1.3.0";

	/**
	 * PUBLIC METHODS CALLED BY CBSUBS (AND THAT CAN BE EXTENDED IF NEEDED BY GATEWAY:
	 */

	/**
	 * Returns text "using your xxxx account no...."
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return string  Text
	 */
	public function getTxtUsingAccount( $paymentBasket )
	{
		return ' ' . CBPTXT::T("using dummy test-method");
	}

	/**
	 * CBSUBS ON-SITE CREDIT-CARDS PAGES PAYMENT API METHODS:
	 */

	/**
	 * Attempts to authorize and capture a credit card for a single payment of a payment basket using authorize.net AIM
	 *
	 * @param  array                      $card                : $card['type'], $card['number'], $card['firstname'], $card['lastname'], $card['expmonth'], $card['expyear']
	 * @param  cbpaidPaymentBasket        $paymentBasket
	 * @param  int                        $now                  unix timestamp of now
	 * @param  cbpaidPaymentNotification  $ipn                  returns the stored notification
	 * @param  boolean                    $authnetSubscription  true if it is a subscription and amount is in mc_amount1 and not in mc_gross
	 * @return string|array                                     STRING subscriptionId    if subscription request succeeded, otherwise ARRAY( 'level' => 'spurious' or 'fatal', 'errorText', 'errorCode' => string ) of error to display
	 */
	protected function processSinglePayment( $card, $paymentBasket, $now, &$ipn, $authnetSubscription )
	{
		sleep( 1 );		// simulate payment
		$return	=	'1';
		$ipn = $this->_logNotification( 'P', $now, $paymentBasket, $card, $return );
		return $return;
	}

	/**
	 * Attempts to subscribe a credit card for AIM + ARB subscription of a payment basket.
	 * Errors are only server reachability errors or format error, as Credit-Cards are not checked or charged in authorize.net ARB API !!! :
	 * ARB are subscriptions to a cron script running at authorize.net each day at 2:30 AM PST, while authorize.net ARB server time is MST with US DST.
	 *
	 * @param  array                      $card                : $card['type'], $card['number'], $card['firstname'], $card['lastname'], $card['expmonth'], $card['expyear']
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
		sleep( 1 );		// simulate payment
		$return	=	'1';
		$ipn						=	$this->_logNotification( 'S', $now, $paymentBasket, $card, $return );
		if ( ( $paymentBasket->mc_amount3 != 0 ) && ( ! $this->getErrorMSG() ) ) {
			$autorecurring_type			=	1;
			$authnetSubscription		=	( ( $this->getAccountParam( 'enabled', 0 ) >= 2 ) && $paymentBasket->isAnyAutoRecurring() );
			$autorenew_type				=	( $authnetSubscription ? 2 : 0 );			//TBD: mandatory by system imposed by implementation here !!!
		}
		$this->_bindNotificationToBasket( $ipn, $paymentBasket );
		return $return;
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
		return '1';
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
		global $_CB_framework, $_CB_database;

		if ( $amount > $payment->mc_gross ) {
			return false;
		}

		if ( $lastRefund || ( $amount == $payment->mc_gross ) ) {
			$paymentStatus	=	'Refunded';
			$log_type		=	'3';
		} else {
			$paymentStatus	=	'Partially-Refunded';
			$log_type		=	'4';
		}
		$reasonCode			=	'Refund';
		$charset			=	$_CB_framework->outputCharset();
		$ipn				=	$this->_prepareIpn( $log_type, $paymentStatus, $payment->payment_type, $reasonCode, $_CB_framework->now(), $charset );
		$ipn->bindBasket( $paymentBasket );
		$ipn->mc_gross		=	- $amount;
		$ipn->computeRefundedTax( $payment );
		$ipn->mc_fee		=	0.0;
		$ipn->auth_id		=	null;
		$ipn->txn_id		=	'124';
		$ipn->parent_txn_id	=	$payment->txn_id;

		$_CB_database->insertObject( $ipn->getTableName(), $ipn, $ipn->getKeyName() );

		$this->updatePaymentStatus( $paymentBasket, $ipn->txn_type, $ipn->payment_status, $ipn, 1, 0, 0, false );
		return true;
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
		return false;
	}

	/**
	 * Returns an array for the 'radios' array of $redirectNow type:
	 * return array( account_id, submethod, paymentMethod:'single'|'subscribe', array(cardtypes), 'label for radio', 'description for radio' )
	 * 
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @param  string               $subMethod
	 * @param  string               $paymentType
	 * @param  string               $defaultLabel
	 * @return array
	 */
	protected function getPayRadioRecepie( $paymentBasket, $subMethod, $paymentType, $defaultLabel )
	{
		$ps						=	parent::getPayRadioRecepie( $paymentBasket, $subMethod, $paymentType, $defaultLabel );
		$ps->brandLabelHtml		.=	' (' . CBPTXT::Th("Dummy") . ')';
		return $ps;
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
		$ps				=	parent::getPayButtonRecepie( $paymentBasket, $subMethod, $paymentType );
		$dummyTxt		=	' (' . CBPTXT::T("Dummy") . ')';
		$ps->altText	.=	$dummyTxt;
		$ps->titleText	.=	$dummyTxt;
		return $ps;
	}

	/**
	 * PRIVATE METHODS OF THIS CLASS
	 */

	/**
	 * Logs notification
	 *
	 * @param  string                           $log_type
	 * @param  int                              $now
	 * @param  cbpaidPaymentBasket              $paymentBasket
	 * @param  array                            $card
	 * @param  array                            $return
	 * @return cbpaidPaymentNotification
	 */
	private function _logNotification( $log_type, $now, $paymentBasket, $card, $return )
	{
		$paymentType					=	ucwords( $card['type'] ) . ' Credit Card';
		if  ( is_string( $return ) ) {
			$paymentStatus				=	'Completed';
			$reasonCode					=	null;
		} else {
			$paymentStatus				=	'Failed';
			$reasonCode					=	( isset( $return['errorCode'] ) ? $return['errorCode'] : null );
		}

		$ipn							=&	$this->_prepareIpn( $log_type, $paymentStatus, $paymentType, $reasonCode, $now, 'utf-8' );
		$ipn->bindBasket( $paymentBasket );
		$ipn->setTxnSingle( $now );

		$legalCCStore			        =	/* cbGetParam not needed, we want raw log here! */ $_POST;
		if ( isset( $legalCCStore[$this->_getPagingParamName('number')] ) ) {
			$legalCCStore[$this->_getPagingParamName('number')]	=	'XXXX XXXX XXXX ' . substr( $legalCCStore[$this->_getPagingParamName('number')], -4, 4 );
		} else {
			$legalCCStore[$this->_getPagingParamName('number')]	=	null;
		}
		if ( isset( $legalCCStore[$this->_getPagingParamName('cvv')] ) ) {
			$legalCCStore[$this->_getPagingParamName('cvv')]		= 'XXX';
		}
		$ipn->setPayerNameId( $card['firstname'], $card['lastname'], $legalCCStore[$this->_getPagingParamName('number')] );
		$ipn->setRawResult( 'SIMULATEDOK' );
		$ipn->setRawData( '$_POST=' . var_export( $legalCCStore, true ) . ';\n' );

		if ( $log_type == 'P' ) {
			$ipn->setTxnSingle( '123' );
		} elseif ( $log_type == 'S' ) {
			$ipn->setTxnSubscription( $paymentBasket, $return, time() );
		}
		$ipn->store();
		return $ipn;
	}

}	// end class cbpaiddummy.

/**
 * Payment account class for this gateway: Stores the settings for that gateway instance, and is used when editing and storing gateway parameters in the backend.
 * No methods need to be implemented or overriden in this class, except to implement the private-type params used specifically for this gateway:
 */
class cbpaidGatewayAccountdummy extends cbpaidGatewayAccountCreditCards
{
}
