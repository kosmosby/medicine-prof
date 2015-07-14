<?php
/**
 * @version $Id: cbpaidPaymentNotification.php 1551 2012-12-03 10:52:03Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Notifications storage class
 *
 */
class cbpaidPaymentNotification extends cbpaidPaymentBaseEntries {
	// public $id				= null;		+ a lot of others are inherited
	public $user_id;				//++
	public $notify_version;			// => 2.1
	public $verify_sign;			// => AiPC9BjkCyDFQXbSkoZcgqH3hpacAHhKdspoUv3WGA8DlP-6DHuuESUd

	public $payment_basket_id	= null;
	public $payment_method		= null;
	public $gateway_account		= null;
	public $log_type;
	public $time_received;
	public $raw_data;
	public $raw_result;
	public $ip_addresses;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_notifications', 'id', $db );
	}
	/**
	 * Fills object with all standard items of a Notification record
	 *
	 * @param  cbpaidPayHandler     $payHandler
	 * @param  int                  $test_ipn
	 * @param  string               $log_type
	 * @param  string               $paymentStatus
	 * @param  string               $paymentType
	 * @param  string               $reasonCode
	 * @param  int                  $paymentTime
	 * @param  string               $charset
	 */
	public function initNotification( $payHandler, $test_ipn, $log_type, $paymentStatus, $paymentType, $reasonCode, $paymentTime, $charset = 'utf-8' ) {
		global $_CB_framework;

		$this->payment_method		=	$payHandler->getPayName();
		$this->gateway_account		=	$payHandler->getAccountParam( 'id' );
		$this->log_type				=	$log_type;
		$this->time_received		=	date( 'Y-m-d H:i:s', $_CB_framework->now() );

		$this->ip_addresses			=	cbpaidRequest::getIPlist();
		$this->notify_version		=	'2.1';
		$this->user_id				=	(int) cbGetParam( $_GET, 'user', 0 );
		$this->charset				=	$charset;
		$this->test_ipn				=	$test_ipn;

		$this->payer_status			=	'unverified';

		$this->payment_status		=	$paymentStatus;
		if ( in_array( $paymentStatus, array( 'Completed', 'Pending', 'Processed', 'Failed', 'Reversed', 'Refunded', 'Partially-Refunded', 'Canceled_Reversal' ) ) ) {
			if ( in_array( $paymentStatus, array( 'Completed', 'Reversed', 'Refunded', 'Partially-Refunded', 'Canceled_Reversal' ) ) ) {
				$this->payment_date	=	date( 'H:i:s M d, Y T', $paymentTime );			// paypal-style
			}
			$this->payment_type		=	$paymentType;
		}
		if ( $reasonCode ) {
			$this->reason_code		=	$reasonCode;
		}
	}
	/**
	 * Adjusts refund IPN's tax to the $payment's original tax proportionally to refunded amount $ipn->mc_gross proportion to original amount $payment->mc_gross
	 *
	 * @param  cbpaidPayment  $payment
	 * @return void
	 */
	public function computeRefundedTax( $payment ) {
		if ( $payment->tax && ! $this->tax ) {
			$originalAmount			=	round( $payment->mc_gross, 2 );
			if ( $originalAmount ) {
				$this->tax			=	- round( $payment->tax * round( abs( $this->mc_gross ), 2 ) / $originalAmount, 2 );
			}
		}
	}
	/**
	 * Bind a basket's main information into $this IPN
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 */
	public function bindBasket( $paymentBasket ) {
		$this->payment_basket_id	=	$paymentBasket->id;
		$this->item_name			=	$paymentBasket->item_name;
		$this->item_number			=	$paymentBasket->item_number;
		$this->quantity				=	$paymentBasket->quantity;
		$this->custom				=	$paymentBasket->id;
		$this->payment_gross		=	$paymentBasket->mc_gross;
		$copyValues					=	array( 'invoice', 'tax', 'mc_currency', 'mc_gross', 'mc_handling', 'mc_shipping' );
		foreach ( $copyValues as $v ) {
			$this->$v				=	$paymentBasket->$v;
		}
	}
	/**
	 * Sets raw_data of notification
	 * @param  string  $raw_data
	 * @return void
	 */
	public function setRawData( $raw_data ) {
		$this->raw_data				=	$raw_data;
	}
	/**
	 * Sets raw_result of notification
	 * @param  string  $raw_result
	 * @return void
	 */
	public function setRawResult( $raw_result ) {
		$this->raw_result			=	$raw_result;
	}
	/**
	 * Sets first_name, last_name and payer_id of notification
	 * @param  string  $first_name
	 * @param  string  $last_name
	 * @param  int     $payer_id
	 * @return void
	 */
	public function setPayerNameId( $first_name, $last_name, $payer_id = null ) {
		$this->first_name			=	$first_name;
		$this->last_name			=	$last_name;
		if ( $payer_id !== null ) {
			$this->payer_id			=	$payer_id;
		}
	}
	/**
	 * Sets notification for single payment: txn_id and txn_type to 'web_accept', and recurring to 0 in $this notification
	 * @param  string  $txn_id
	 * @return void
	 */
	public function setTxnSingle( $txn_id ) {
		$this->txn_id				=	$txn_id;
		$this->txn_type				=	'web_accept';
		$this->recurring			=	0;
	}
	/**
	 * Sets notification for autorecurring payment: txn_type to 'subscr_payment', subscr_id, subscr_date of $this notification
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  string               $subscr_id
	 * @param  int                  $subscriptionStartTime
	 * @param  int                  $reattempt
	 */
	public function setTxnSubscription( $paymentBasket, $subscr_id, $subscriptionStartTime, $reattempt = 0 ) {
		$this->txn_type				=	'subscr_payment';
		$this->subscr_id			=	$subscr_id;
		$this->subscr_date			=	date( 'H:i:s M d, Y T', $subscriptionStartTime );			// paypal-style
		$copySubValues				=	array( 'period1', 'period2', 'period3', 'mc_amount1', 'mc_amount2', 'mc_amount3', 'recurring', 'recur_times' );
		foreach ( $copySubValues as $v ) {
			$this->$v				=	$paymentBasket->$v;
		}
		$this->recurring			=	1;
		$this->reattempt			=	$reattempt;
	}
	/**
	 * Sets receiver_id of $this notification
	 * @param $receiver_id
	 */
	public function setReceiver( $receiver_id ) {
		$this->receiver_id			=	$receiver_id;
	}
	/**
	 * Sets verify_sign, auth_id and auth_status of $this notification
	 * @param $verify_sign
	 * @param $auth_id
	 * @param $auth_status
	 */
	public function setVerifySignAuthIdStatus( $verify_sign, $auth_id, $auth_status ) {
		$this->verify_sign			=	$verify_sign;
		$this->auth_id				=	$auth_id;
		$this->auth_status			=	$auth_status;
	}
}	// class cbpaidPaymentNotification
/**
 * Backwards-compatibility class
 * @obsolete since CBSubs 2.0.2
 */
class cbpaidsubscriptionsNotification extends cbpaidPaymentNotification { }
