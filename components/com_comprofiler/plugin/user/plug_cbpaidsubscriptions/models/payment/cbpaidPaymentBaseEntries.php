<?php
/**
 * @version $Id: cbpaidPaymentBaseEntries.php 1546 2012-12-02 23:16:25Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Base class for payment tables
 *
 */
class cbpaidPaymentBaseEntries extends cbpaidTable {
	/** @var int Primary key */
	public $id					= null;

	// A.0 general variables
	public $charset;				// => windows-1252
	public $test_ipn;				// => 1

	// A.1 Buyer information:
	public $address_name;
	public $address_street;
	public $address_city;
	public $address_state;
	public $address_zip;
	public $address_country;
	public $address_country_code;
	public $address_status;

	public $first_name;				// => mam
	public $last_name;				// => joe
	public $payer_business_name;	// => MJ
	public $payer_email;			// => mj@mj.com
	public $payer_id;				// => LB77XKDMZA64G
	public $payer_status;			// => verified
	public $residence_country;		// => US
	public $contact_phone;			// => only if get contact phone option enabled in PayPal
	public $vat_number;

	// A.2 IPN and PDT Vars: Basic information:
	public $business;				// => seller@designerfotos.com
	public $item_name;				// => Platinium Contributors
	public $item_number;			// => 2
	public $quantity;				// => 1
	public $receiver_email;			// => seller@designerfotos.com
	public $receiver_id;			// => CS84V4A9S47NQ

	// A.3 IPN and PDT Vars: Advanced and Custom information:
	public $custom;					// => 78
	public $invoice;
	public $memo;
	public $option_name1;
	public $option_selection1;
	public $option_name2;
	public $option_selection2;
	public $tax;					// => 0.00

	// A.4 IPN and PDT Vars: Website payment standard, Pro and refund information:
	public $auth_id;
	public $auth_exp;
	public $auth_amount;
	public $auth_status;
	// some others for carts omitted here
	public $num_cart_items;
	public $parent_txn_id;
	public $payment_date;			// => 06:43:12 Jun 11, 2006 PDT
	public $payment_status;			// => Completed
	public $payment_type;			// => instant
	public $pending_reason;
	public $reason_code;
	public $remaining_settle;
	public $shipping;				// => 0.00
	public $transaction_entitiy;
	public $txn_id;					// => 8SN13651XD220014K
	public $txn_type;				// => web_accept
	public $receipt_id;				// => "2850-4548-4447-8475"

	// A.5 IPN and PDT Vars: Currency and Currency Exchange information:
	public $exchange_rate;
	public $mc_currency;			// => USD
	public $mc_fee;					// => 0.36
	public $mc_gross;				// => 2.00
	public $mc_handling;
	public $mc_shipping;
	public $payment_fee;			// => 0.36
	public $payment_gross;			// => 2.00
	public $settle_amount;
	public $settle_currency;

	// A.6 IPN and PDT Variables: Auctions:
	public $auction_buyer_id;
	public $auction_closing_date;
	public $auction_multi_item;
	public $for_auction;

	// A.7 IPN and PDT Vars: Mass Payment:
	// not implemented...

	// A.8 + A.9 Subscription variables:
	public $subscr_date;
	public $subscr_effective;
	public $period1;
	public $period2;
	public $period3;
	public $amount1;
	public $amount2;
	public $amount3;
	public $mc_amount1;
	public $mc_amount2;
	public $mc_amount3;
	public $recurring;
	public $reattempt;
	public $retry_at;
	public $recur_times;
	public $username;
	public $password;
	public $subscr_id;

	// A.10 Dispute Notification Variables
	public $case_id;
	public $case_type;
	public $case_creation_date;

	// CBSubs specific:
	public $integrations;
	public $sale_id;
	/**
	 * Stores the object, in this case also converting the parameter objects (integrations) into storable columns
	 *
	 * @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	 * @return boolean                TRUE if successful otherwise FALSE
	 */
	public function store( $updateNulls = false ) {
		$this->storeParams();
		return parent::store( $updateNulls );
	}
	/**
	 * Binds an object to that object
	 *
	 * @param  object  $srcObj   source object containing data
	 * @param  string  $ignore   space-separated object variable names to ignore in the binding
	 * @param  string  $prefix   prefix of source object variable names corresponding to destination object var names
	 * @return boolean TRUE
	 */
	public function bindObjectToThisObject( $srcObj, $ignore='', $prefix = null ) {
		$ignore = ' ' . $ignore . ' ';
		foreach ( array_keys( get_object_vars( $this ) ) as $k ) {
			if ( ( substr( $k, 0, 1 ) != '_' ) && ( strpos( $ignore, ' ' . $k . ' ' ) === false ) ) {
				if ( $prefix ) {
					$ak			=	$prefix . $k;
				} else {
					$ak			=	$k;
				}
				if ( isset( $srcObj->$ak ) ) {
					$this->$k	=	$srcObj->$ak;
				}
			}
		}
		return true;
	}
}	// class cbpaidPaymentBaseEntries
