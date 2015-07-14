<?php
/**
* @version $Id: cbpaidsubscriptions.freetrial.php 1581 2012-12-24 02:36:44Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions: offline payments
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
				
/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

// This gateway implements a payment handler using a hosted page at the PSP:
// Import class cbpaidHostedPagePayHandler that extends cbpaidPayHandler
// and implements all gateway-generic CBSubs methods.

/**
* Paid Subscriptions Tab Class for handling the CB tab api
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @author Beat
*/
class cbpaidfreetrial extends cbpaidHostedPagePayHandler
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
	 * Specific to freetrial gateway:
	 * parameter name of the button settings, as this gateway is used for "Free Trial" and for "Order" 
	 * @var string
	 */
	public $_button	=	'freetrial';		// can be 'order' too

	/**
	 * PUBLIC METHODS CALLED BY CBSUBS (AND THAT CAN BE EXTENDED IF NEEDED BY GATEWAY:
	 */

	/**
	 * Returns text 'using your xxxx account no....'
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return string
	 */
	public function getTxtUsingAccount( $paymentBasket )
	{
		return ' ' . CBPTXT::T("using free trial possibility") . ' ';
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
			case 'FreeTrial':
			case 'Completed':
				$newMsg	=	'<p>' . CBPTXT::Th("Your free subscriptions are now activated.") . '</p>';
				break;
		 	default:
				$newMsg = '<p>' . CBPTXT::Th("Your transaction is not cleared and has currently following status:") . ' <strong>' . CBPTXT::Th( htmlspecialchars( $paymentBasket->payment_status ) ) . '.</strong></p>';
		 		break;
		}
		return $newMsg;
	}

	/**
	 * Gets an account parameter: This gateway has no parameters and is always enabled.
	 *
	 * @param  string  $key       Name of the parameter
	 * @param  mixed   $default   Default value
	 * @return string
	 */
	public function getAccountParam( $key, $default = null )
	{
		// echo $key . ' ';
		if ( $key == 'enabled' ) {
			$ret	=	'1';
		} else {
			$ret	=	$default;
		}
		return $ret;
	}
	
	/**
	 * CBSUBS HOSTED PAGE PAYMENT API METHODS:
	 */

	/**
	 * Returns an HTML form with a visible button and hidden fields for the gateway
	 * 
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @return array                                array with all hidden POST params
	 */
	protected function getSinglePaymentRequstParams( $paymentBasket )
	{
		return array();		// no hidden post params
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
		return null;
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
		return null;
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
		return null;
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
		return false;
	}

	/**
	 * Cancels an existing recurring subscription
	 * (optional)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @param  cbpaidPaymentItem[]  $paymentItems   redirect immediately instead of returning HTML for output
	 *
	 * @return boolean|string                       TRUE if unsubscription done successfully, STRING if error
	 */
	protected function handleStopPaymentSubscription( $paymentBasket, $paymentItems )
	{
		return true;		// unsubscribe an offline paid membership should always work, as this will be handled offline anyway!
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
		global $_CB_framework;

		$privateVarsList = 'id user_id time_initiated time_completed ip_addresses mc_gross mc_currency '
						.	'quantity item_number item_name shared_secret payment_status '
						.	'invoice period1 period2 period3 mc_amount1 mc_amount2 mc_amount3';

		$ret = null;
		// $privateVarsList = 'id payment_method gateway_account user_id time_initiated time_completed ip_addresses mc_gross mc_currency quantity item_number item_name shared_secret payment_status';

		if ( $result == 'freetrial' ) {
			$paymentBasketId = (int) $this->_getReqParam( 'basket' );
			if ( $paymentBasketId ) {
				$exists = $paymentBasket->load( (int) $paymentBasketId );
				if ( $exists && ( $this->_getReqParam( 'cbpid' ) == $paymentBasket->shared_secret ) && ( $paymentBasket->payment_status == 'NotInitiated' ) ) {
					$isAnyAutoRecurring					=	$paymentBasket->isAnyAutoRecurring();
					if ( ( $isAnyAutoRecurring == 2 && ( ( $paymentBasket->period1 ) && ( $paymentBasket->mc_amount1 == 0 ) ) )
					|| ( ( $paymentBasket->mc_amount1 == 0 ) && ( $paymentBasket->mc_amount3 == 0 ) && ( $paymentBasket->mc_gross == 0 ) ) 
					) {
						// user-choice: no need to wait for payment basket completed to activate subscriptions:
						$paymentBasket->payment_method	=	$this->getPayName();
						// $paymentBasket->gateway_account = $this->getAccountParam( 'id' );
						// $ipn	=	null;
						$ipn							=	$this->_logNotification( 'P', $_CB_framework->now(), $paymentBasket );
						$paymentBasket->bindObjectToThisObject( $ipn, $privateVarsList );
						$this->updatePaymentStatus( $paymentBasket, 'web_accept', 'FreeTrial', $ipn, 1, 0, 0, true );
					}
				}
			}
		}
		return  $ret;
	}

	/**
	 * INTERNAL METHODS THAT CAN BE RE-IMPLEMENTED IN PAYMENT HANDLER IF NEEDED:
	 */

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
		return $this->cbsubsGatewayUrl( 'freetrial', null, $paymentBasket, array( 'cbpid' => $paymentBasket->shared_secret ), false, true );
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
		// Settings for Free Trial and Order Now buttons are stored in global CBSubs settings:
		$params					=	cbpaidApp::settingsParams();

		$cardtypes					=	array();

		if ( $this->_button == 'freetrial' ) {
			$brandLabelHtml				=	CBPTXT::Th( $params->get( 'freetrial_radio_name', "Free Trial" ) );		// CBPTXT::T("Free Trial")
			$altText					=	CBPTXT::T( $params->get( 'freetrial_radio_alt', "Subscribe to free trial period only") );	// CBPTXT::T("Subscribe to free trial period only")
			$brandDescriptionHtml		=	CBPTXT::Th( $params->get( 'freetrial_radio_description' ) );
		} else {
			$brandLabelHtml				=	CBPTXT::Th("Confirm Order");
			$altText					=	CBPTXT::T("Confirm Order");
			$brandDescriptionHtml		=	null;
		}
		return cbpaidGatewaySelectorRadio::getPaymentRadio( $this->_button, $subMethod, $paymentType, $cardtypes, $brandLabelHtml, $brandDescriptionHtml, $altText );
		
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
		// Settings for Free Trial and Order Now buttons are stored in global CBSubs settings:
		$params					=	cbpaidApp::settingsParams();

		// Generate URLs for payment:
		$pspUrl					=	$this->pspUrl( $paymentBasket, ( $paymentType == 'subscribe' ) );
		$requestParams			=	$this->getSinglePaymentRequstParams( $paymentBasket );
		$customImage			=	$params->get( $this->_button . '_custom_image' );
		$titleText				=	( $this->_button == 'freetrial' ? CBPTXT::T("Subscribe to free trial period only") : CBPTXT::T("Confirm Order") );
		$altText				=	( $this->_button == 'freetrial' ? CBPTXT::T("Free Trial") :  CBPTXT::T("Confirm Order") );
		$butId					=	'cbpaidButt' . strtolower( $this->_button );

		if ( $customImage == '' ) {
			$customImage		=	$params->get( $this->_button . '_image', 'components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/icons/cards/'
								.	( $this->_button == 'freetrial' ? 'cc_big_orange_free_trial.gif' : 'cc_big_orange_confirm_order.gif' ) );
		}
		$payNameForCssClass		=	$this->_button;
		
		return cbpaidGatewaySelectorButton::getPaymentButton( $this->getAccountParam( 'id' ), $subMethod, $paymentType, $pspUrl, $requestParams, $customImage, $altText, $titleText, $payNameForCssClass, $butId );
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
	 * @return cbpaidPaymentNotification
	 */
	private function _logNotification( $log_type, $now, $paymentBasket )
	{
		global $_CB_database;

		$ipn = new cbpaidPaymentNotification($_CB_database);
		$ipn->payment_method	=	$this->getPayName();
		$ipn->gateway_account	=	$this->getAccountParam( 'id' );
		$ipn->log_type			=	$log_type;
		$ipn->time_received		=	date( 'Y-m-d H:i:s', $now );
		$ipn->payment_basket_id	=	$paymentBasket->id;

		$ipn->raw_data			=	'$_POST=' . var_export( $_POST, true ) . ';\n';

		$ipn->raw_result 		=	'FREE_TRIAL';
		$ipn->ip_addresses		=	cbpaidRequest::getIPlist();
		$ipn->notify_version	=	'2.1';
		$ipn->user_id			=	(int) cbGetParam( $_GET, 'user', 0 );
		$ipn->charset			=	'utf-8';
		$ipn->test_ipn			=	0;
		$ipn->first_name		=	$paymentBasket->first_name;
		$ipn->last_name			=	$paymentBasket->last_name;
		$ipn->payer_status		=	'unverified';
		$ipn->item_name			=	$paymentBasket->item_name;
		$ipn->item_number		=	$paymentBasket->item_number;
		$ipn->quantity			=	$paymentBasket->quantity;
		$ipn->custom			=	$paymentBasket->id;
		$ipn->invoice			=	$paymentBasket->invoice;
		$ipn->mc_currency		=	$paymentBasket->mc_currency;
		$ipn->tax				=	'0.00';
		$ipn->mc_gross			=	'0.00';
		$ipn->payment_status	=	'Completed';
		$ipn->payment_date		=	date( 'H:i:s M d, Y T', $now );			// paypal-style
		$ipn->payment_type		=	'Free trial';
		$ipn->txn_id			=	null;
		$ipn->txn_type			=	'web_accept';
		$ipn->recurring			=	0;

		$_CB_database->insertObject( $ipn->getTableName(), $ipn, $ipn->getKeyName() );

		return $ipn;
	}

}

/**
 * Payment account class for this gateway: Stores the settings for that gateway instance, and is used when editing and storing gateway parameters in the backend.
 *
 * No methods need to be implemented or overriden in this class, except to implement the private-type params used specifically for this gateway:
 */
class cbpaidGatewayAccountfreetrial extends cbpaidGatewayAccount
{
}
