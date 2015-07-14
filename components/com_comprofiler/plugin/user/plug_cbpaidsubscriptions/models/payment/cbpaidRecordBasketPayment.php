<?php
/**
* @version $Id: cbpaidRecordBasketPayment.php 1608 2012-12-29 04:12:52Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class to record a manual payment for a basket
 */
class cbpaidRecordBasketPayment extends cbpaidPaymentBasket {
	/**
	 * Internal function to convert CB-formatted date from field into SQL date.
	 * @access private
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function _displayDateToSql( $value ) {
		global $ueConfig;

		if ( $value !== null ) {
			$sqlFormat					=	'Y-m-d';
			$fieldForm					=	str_replace( 'y', 'Y', $ueConfig['date_format'] );
			$value						=	dateConverter( stripslashes( $value ), $fieldForm, $sqlFormat );
			if ( ! preg_match( '/[0-9]{4}-[01][0-9]-[0-3][0-9]/', $value ) ) {
				$value					=	'';
			}
		}
		return $value;
	}
	/**
	 * This is the frontend or backend method used directly
	 * @see cbpaidPaymentBasket::store()
	 *
	 * If table key (id) is NULL : inserts a new row
	 * otherwise updates existing row in the database table
	 *
	 * Can be overridden or overloaded by the child class
	 *
	 * @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	 * @return boolean                TRUE if successful otherwise FALSE
	 */
	public function store( $updateNulls = false ) {
		global $_CB_framework, $_CB_database;

		// 1) check:
		if ( ! in_array( $this->payment_status, array( 'Pending', 'Refunded', 'NotInitiated' ) ) ) {
			$this->setError( CBPTXT::T("This payment basket is not pending.") );
			return false;
		}
		if ( $this->txn_id == '' ) {
			$this->txn_id			=	'None';		// needed for updatePayment to generate payment record.
		}

		$paymentBasket				=	new cbpaidPaymentBasket( $_CB_database );
		$paymentBasket->load( $this->id );

		if ( ! $paymentBasket->gateway_account ) {
			$this->setError( CBPTXT::T("This payment basket has no gateway associated so can not be paid manually.") );
			return false;
		}
		$ipn								=	new cbpaidPaymentNotification( $_CB_database );
		$ipn->bindObjectToThisObject( $paymentBasket, 'id' );

		$ipn->mc_currency					=	$this->mc_currency;
		$ipn->mc_gross						=	$this->mc_gross;
		$this->time_completed				=	$this->_displayDateToSql( $this->time_completed );		//TBD: should be in bind() (NOT IMPORTANT)
		if ( $this->time_completed == '' ) {
			$this->time_completed			=	date( 'Y-m-d H:i:s', $_CB_framework->now() );
		}
		$paymentBasket->time_completed		=	$this->time_completed;
		$ipn->payment_type					=	$this->payment_type;
		$paymentBasket->payment_type		=	$this->payment_type;
		$ipn->txn_id						=	$this->txn_id;
		$paymentBasket->txn_id				=	$this->txn_id;

		$ipn->payment_status		=	'Completed';
		$ipn->txn_type				=	'web_accept';

		$ipn->payment_method		=	$this->payment_method;
		$ipn->gateway_account		=	$this->gateway_account;

		$ipn->log_type				=	'P';
		$ipn->time_received			=	date( 'Y-m-d H:i:s', $_CB_framework->now() );
		$ipn->payment_date			=	date( 'H:i:s M d, Y T', $this->time_completed ? strtotime( $this->time_completed ) : $_CB_framework->now() );			// paypal-style				//TBD FIXME: WE SHOULD CHANGE THIS OLD DATE STYLE ONCE WITH UTC timezone inputed

		$ipn->payment_basket_id	=	$this->id;
		$ipn->raw_result 			=	'manual';
		$ipn->raw_data				=	'';
		$ipn->ip_addresses			=	cbpaidRequest::getIPlist();
		$ipn->user_id				=	$_CB_framework->myId();

		$ipn->txn_id				=	$this->txn_id;
		$ipn->payment_type			=	$this->payment_type;
		$ipn->charset				=	$_CB_framework->outputCharset();

		//TBD
/*
		$paymentBasket->first_name	= $ipn->first_name	= cbGetParam( $_POST, 'txtBTitle' );
		$paymentBasket->first_name		= $ipn->first_name		= cbGetParam( $_POST, 'txtBFirstName' );
		$paymentBasket->last_name		= $ipn->last_name		= cbGetParam( $_POST, 'txtBLastName' );
		$paymentBasket->address_street	= $ipn->address_street	= cbGetParam( $_POST, 'txtBAddr1' );
		$paymentBasket->address_zip		= $ipn->address_zip		= cbGetParam( $_POST, 'txtBZipCode' );
		$paymentBasket->address_city	= $ipn->address_city	= cbGetParam( $_POST, 'txtBCity' );
		$paymentBasket->address_country	= $ipn->address_country	= cbGetParam( $_POST, 'txtBCountry' );
		//TBD? $paymentBasket->phone	= $ipn->phone			= cbGetParam( $_POST, 'txtBTel' );
		//TBD? $paymentBasket->fax		= $ipn->fax				= cbGetParam( $_POST, 'txtBFax' );
		$paymentBasket->payer_email		= $ipn->payer_email		= cbGetParam( $_POST, 'txtBEmail' );
*/
		if( ! $_CB_database->insertObject( $ipn->getTableName(), $ipn, $ipn->getKeyName() ) ) {
			trigger_error( 'store error:'.htmlspecialchars($_CB_database->getErrorMsg()), E_USER_ERROR );
			//TBD also in paypal: error code 500 !!!
		}

		$payAccount					=	cbpaidControllerPaychoices::getInstance()->getPayAccount( $paymentBasket->gateway_account );
		if ( ! $payAccount ) {
			$this->setError( CBPTXT::T("This payment basket's associated gateway account is not active, so can not be paid manually.") );
			return false;
		}
		$payClass					=	$payAccount->getPayMean();

		$payClass->updatePaymentStatus( $paymentBasket, 'web_accept', 'Completed', $ipn, 1, 0, 0, 'singlepayment' );
		return true;
	}
	/**
	 * Renders the edit form for the invoicing address for that basket.
	 *
	 * @param  int     $user_id  User id of person registering the payment
	 * @return string
	 */
	protected function renderRecordPaymentForm( $user_id ) {
		if ( $this->authoriseAction( 'cbsubs.recordpayments' ) ) {
			return $this->renderForm( 'recordpayment', 'recordpayment', $user_id, true );
		} else {
			return CBPTXT::Th("Not authorized");
		}
	}
	/**
	 * Saves invoicing address, and if error, sets error to baseClass and
	 * Renders the edit form for the invoicing address for that basket again.
	 *
	 * @return string|null                             NULL if no error, otherwise HTML for edit.
	 */
	protected function saveRecordPaymentForm( ) {
		if ( $this->authoriseAction( 'cbsubs.recordpayments' ) ) {
			$return				=	$this->bindFromFormPost( 'recordpayment', 'recordpayment' );
			if ( $return === null ) {
				$this->store();
			}
		} else {
			$return				=	CBPTXT::Th("Not authorized");
		}
		return $return;
	}
	/**
	 * Renders record payment view
	 * 
	 * @param  int      $paymentBasketId
	 * @return string
	 */
	public static function displayRecordPaymentForm( $paymentBasketId ) {
		global $_CB_framework;
		// also called in the case of reload of invoicing address:

		cbpaidApp::loadLang( 'admin' );

		$paymentRecorder				=	new self();
		
		$exists							=	$paymentBasketId && $paymentRecorder->load( (int) $paymentBasketId );
		if ( $exists ) {

			if ( $paymentRecorder->authoriseAction( 'cbsubs.recordpayments' ) ) {

				$return				=	$paymentRecorder->renderRecordPaymentForm( $_CB_framework->myId() );

			} else {
				$return					=	CBPTXT::T("You are not authorized to record payments.");
			}
		} else {
			$return						=	CBPTXT::T("Payment basket not found.");
		}
		return $return;
	}
	/**
	 * Saves record payment view
	 *
	 * @param  int          $paymentBasketId
	 * @return null|string
	 */
	public static function saveRecordPayment( $paymentBasketId ) {
		cbpaidApp::loadLang( 'admin' );

		$paymentRecorder				=	new self();

		$exists							=	$paymentBasketId && $paymentRecorder->load( (int) $paymentBasketId );
		if ( $exists ) {

			if ( $paymentRecorder->authoriseAction( 'cbsubs.recordpayments' ) ) {
				$return					=	$paymentRecorder->saveRecordPaymentForm();
			} else {
				$return					=	CBPTXT::T("You are not authorized to record payments.");
			}
		} else {
			$return						=	CBPTXT::T("No unpaid payment basket found.");
		}
		return $return;
	}
}
