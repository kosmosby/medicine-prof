<?php
/**
 * @version $Id: cbpaidPayment.php 1546 2012-12-02 23:16:25Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Payments database class
 *
 */
class cbpaidPayment extends cbpaidPaymentBaseEntries {
	// public $id				= null;		+ a lot of others are inherited
	public $notify_version;				// => 2.1
	public $verify_sign;				// => AiPC9BjkCyDFQXbSkoZcgqH3hpacAHhKdspoUv3WGA8DlP-6DHuuESUd

	public $payment_basket_id	= null;
	public $payment_method		= null;
	public $gateway_account		= null;
	public $log_id;						//MM
	public $for_user_id;				//++
	public $by_user_id;					//++
	public $to_user_id;					//++
	public $created_by_user_id;			//++
	public $modified_by_user_id;		//++
	public $time_paid;					//MM

	public $time_paid_date;				//for speedy stats queries
	public $time_paid_day_of_week;		//for speedy stats queries
	public $time_paid_yearweek;			//for speedy stats queries
	public $time_paid_yearmonth;		//for speedy stats queries
	public $time_paid_hour;				//for speedy stats queries

	public $time_created;				//MM
	public $time_modified;				//++
	public $ip_addresses;
	// Private variables:
	/** Payment basket corresponding to that payment:
	 *  @var cbpaidPaymentBasket */
	public $_paymentBasket		= null;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_payments', 'id', $db );
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
		if ( $this->time_paid && ( $this->time_paid != $this->_db->getNullDate() ) ) {
			$offset							=	(int) $_CB_framework->getCfg( 'offset' ) * 3600;

			list($y, $c, $d, $h, $m, $s)	=	sscanf( $this->time_paid, '%d-%d-%d %d:%d:%d');
			$time_paid						=	mktime($h, $m, $s, $c, $d, $y);			// we do NOT use PHP strtotime, which is broken
			$time_paid						+=	$offset;

			$dateDayHour					=	explode( ' ', date( 'Y-m-d w H o W', $time_paid ) );
			$dateDayHour[1]					+=	1;			// --> 1 = Sunday...7 = Saturday, ISO-8601 numeric representation of the day of the week, like MySQL

			$this->time_paid_date			=	$dateDayHour[0];
			$this->time_paid_day_of_week	=	$dateDayHour[1];
			$this->time_paid_yearweek		=	$dateDayHour[3] . '-W' . $dateDayHour[4];
			$this->time_paid_yearmonth		=	substr( $dateDayHour[0], 0, 7 );
			$this->time_paid_hour			=	$dateDayHour[2];
		}
		return parent::store( $updateNulls );
	}
	/**
	 * binds a payment notification object for a basket to this payment.
	 *
	 * @param  cbpaidPaymentBasket        $paymentBasket
	 * @param  cbpaidPaymentNotification  $notification
	 * @param  string                     $paymentDate     in format 'Y-m-d H:i:s'
	 * @param  int                        $now
	 * @return void
	 */
	public function bindPayment( $paymentBasket, $notification, $paymentDate, $now ) {
		$ignore	=	'id payment_basket_id payment_method gateway_account log_id for_user_id by_user_id to_user_id '
			.	'created_by_user_id modified_by_user_id time_paid time_created time_modified';
		$this->bindObjectToThisObject( $notification, $ignore );
		if ( in_array( $this->payment_status, array( 'Processed', 'Reversed', 'Refunded', 'Partially-Refunded', 'Canceled_Reversal' ) ) ) {
			$this->payment_status	=	'Completed';	// the payment is negative, but succeeded
		}
		$this->payment_basket_id	=	$paymentBasket->id;
		$this->payment_method		=	$notification->payment_method;
		$this->gateway_account		=	$notification->gateway_account;
		$this->log_id				=	$notification->id;
		$this->for_user_id			=	$paymentBasket->user_id;
		$this->by_user_id			=	$paymentBasket->user_id;		//TBD v2
		$this->to_user_id			=	0;								//TBD v2
		$this->created_by_user_id	=	0;
		$this->modified_by_user_id	=	0;
		$this->time_paid			=	$paymentDate;
		$this->time_created			=	date('Y-m-d H:i:s', $now );
		$this->time_modified		=	null;
	}
	/**
	 * BACKEND RENDERING METHODS:
	 */
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
	 * @param  string           $price
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderCurrencyAmount( $price, &$params ) {
		return $params->get( 'currency_code' ) . '&nbsp;' . $this->renderAmount( $price, $params );
	}
}	// class cbpaidPayment
