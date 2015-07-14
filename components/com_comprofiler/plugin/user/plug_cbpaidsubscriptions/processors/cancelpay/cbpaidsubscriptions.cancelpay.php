<?php
/**
* @version $Id: cbpaidsubscriptions.cancelpay.php 1581 2012-12-24 02:36:44Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions: offline payments
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


/**
* Paid Subscriptions Tab Class for handling the CB tab api
*/
class cbpaidcancelpay extends cbpaidPayHandler
{
	/**
	 * Gateway API version used
	 * @var int
	 */
	public $gatewayApiVersion	=	"1.3.0";

	/**
	 * Overrides base class with 2:
	 * Hash type: 1 = only if there is a basket id (default), 2 = always, 0 = never
	 * @var int
	 */
	protected $_urlHashType	=	2;

	/**
	 * Returns either a hidden form with a visible button or redirects directly to payment processing page
	 * THIS CancelPay gateway's getPaymentBasketProcess is a bit DIFFERENT, as it just returns a string with the link to cancel.
	 *
	 * @param  UserTable            $user           object reflecting the user being registered (it can have id 0 or be NULL in future)
	 * @param  cbpaidPaymentBasket  $paymentBasket  Order Basket to be paid
	 * @param  string               $redirectNow    'redirect', 'radios', 'buttons', other: return null (see above)
	 * @return string|array                         array: See above, OR string: HTML to display in buttons area
	 */
	public function getPaymentBasketProcess( $user, $paymentBasket, $redirectNow )
	{
		$ret = null;

		if ( $redirectNow == 'redirect' ) {
			return array( $this->getCancelUrl( $paymentBasket ) );
		} else {
			$params				=&	cbpaidApp::settingsParams();
			$return_cancel_url	=	$this->getCancelUrl( $paymentBasket );

			$ret				.=	'<div><a href="' . $return_cancel_url . '">'
								.	CBPTXT::Th( $params->get( 'cancel_subscription_link_text', 'Cancel payment and subscription' ) )
								.	'</a></div>';

			$ret				=	'<div class="cbpaidCCbutton" id="cbpaidButtcancel">'
				 				.	$ret
				 				.	'</div>';
		}
		return $ret;
	}

	/**
	* Handles the gateway-specific result of payments (redirects back to this site and gateway notifications). WARNING: unchecked access !
	*
	* @param  cbpaidPaymentBasket  $paymentBasket         New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	* @param  array                $postdata              _POST data for saving edited tab content as generated with getEditTab
	* @param  boolean              $allowHumanHtmlOutput  Input+Output: set to FALSE if it's an IPN, and if it is already false, keep quiet
	* @return string                                      HTML to display if frontend, text to return to gateway if notification, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	*/
	public function resultNotification( $paymentBasket, $postdata, &$allowHumanHtmlOutput )
	{
		$ret = null;
		// $privateVarsList = 'id payment_method gateway_account user_id time_initiated time_completed ip_addresses mc_gross mc_currency quantity item_number item_name shared_secret payment_status';

		if ( cbGetParam( $_GET, 'result' ) == 'cancel') {
			
			// The user cancelled his payment (and registration):

			/* this check is done in cbpaidsubscription AFTER we return, as well as the updatePayment() call:
			$paymentBasketId				=	(int) $this->_getReqParam( 'basket' );
			if ( $paymentBasket->load( (int) $paymentBasketId ) ) {
				if ( $paymentBasket->payment_status == 'NotInitiated') {
			*/

			if ( $this->hashPdtBackCheck( $this->_getReqParam( 'pdtback', '' ) ) ) {
				$paymentBasketId				=	(int) $this->_getReqParam( 'basket' );
				$paymentBasket->id				=	$paymentBasketId;
				$paymentBasket->payment_status	=	'RegistrationCancelled';
				$this->_setErrorMSG(CBPTXT::T("Payment cancelled."));
				$ret = false;
			}
		}
		return  $ret;
	}

	/**
	 * Returns text 'using your xxxx account no....'
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return string
	 */
	public function getTxtUsingAccount( $paymentBasket )
	{
		return ' ' . CBPTXT::T("using cancellation link") . ' ';
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
/*
			case 'Completed':
				$newMsg = "<p>Your offline payment has been received and your subscription activated.</p>\n";
		 		break;
		 	case "Pending":
				$newMsg = "<p>Your offline payment is currently on its way to being processed.";
		 		break;
*/
		 	case 'RegistrationCancelled':
				$newMsg = CBPTXT::Th("Your request to cancel the subscription and invoice has been processed successfully.");
				break;
		 	default:
				$newMsg = '<p>' . CBPTXT::Th("Your transaction is not cleared and has currently following status:") . ' <strong>' . CBPTXT::Th( htmlspecialchars( $paymentBasket->payment_status ) ) . '.</strong></p>';
		 		break;
		}
		return $newMsg;
	}

}	// end class cbpaidcancelpay.

/**
 * Cancel payment "gateway account"
 */
class cbpaidGatewayAccountcancelpay extends cbpaidGatewayAccount
{
}
