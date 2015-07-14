<?php
/**
* @version $Id: cbpaidsubscriptions.offline.php 1581 2012-12-24 02:36:44Z beat $
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
class cbpaidoffline extends cbpaidHostedPagePayHandler
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
	 * Returns text 'using your xxxx account no....'
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return string  Text
	 */
	public function getTxtUsingAccount( $paymentBasket )
	{
		return ' ' . CBPTXT::T("using an offline payment method") . ' ';
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
				$newMsg = CBPTXT::Th( $this->getAccountParam( 'thanks_message_completed', "Your offline payment request has been noted and your subscription is now active. Please make sure actual payment has been processed from your end in order to avoid subscription interruption." ) )
						. '<hr class="cpayOfflineComplSlip" />'
						. $this->_outputSlip( $paymentBasket );
		 		break;
		 	case 'Pending':
				$newMsg = CBPTXT::Th( $this->getAccountParam( 'thanks_message_pending', "Your offline payment request has been recorded and your subscription will be activated when backoffice receives and confirms payment." ) );
		 		break;
			case 'RegistrationCancelled':
				$newMsg = CBPTXT::Th("You requested to cancel the subscription and payment.");
				break;
			case 'Processed':
		 	case 'Denied':
		 	case 'Reversed':
		 	case 'Refunded':
			case 'Partially-Refunded':
		 	default:
				$newMsg = CBPTXT::Th("Your transaction is not cleared and has currently following status:") . ' <strong>' . CBPTXT::Th( htmlspecialchars( $paymentBasket->payment_status ) ) . '.</strong>';
		 		break;
		}
		return $newMsg;
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
		return array(	'cmd'			=>	'showinstructions',
						'amount'		=>	$paymentBasket->mc_gross,
						'currency_code'	=>	$paymentBasket->mc_currency,
						'custom'		=>	$paymentBasket->id
					);
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
	* Handles the gateway-specific result of payments (redirects back to this site and gateway notifications). WARNING: unchecked access !
	*
	* @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	* @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	* @param  string               $result         result= get parameter, other than 'notify', 'success' or 'cancel'.
	* @return string                               HTML to display if frontend, text to return to gateway if notification, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	*/
	protected function handleOtherResult( $paymentBasket, $postdata, $result )
	{
		global $_CB_framework, $_POST;
	
		$ret = null;
		// $privateVarsList = 'id payment_method gateway_account user_id time_initiated time_completed ip_addresses mc_gross mc_currency quantity item_number item_name shared_secret payment_status';

		switch ( $result ) {
			case 'showinstructions':
				//We need to display payment instructions:
				$paymentBasketId		=	(int) cbGetParam( $_POST, 'custom', 0 );
				if ( $paymentBasketId == 0 ) {
					$paymentBasketId	=	(int) cbGetParam( $_GET, 'custom', 0 );
				}
				$exists = $paymentBasket->load( (int) $paymentBasketId );
				if ( $exists
					&& ( cbGetParam( $_REQUEST, 'amount', 0.0 )			== $paymentBasket->mc_gross )
					&& ( cbGetParam( $_REQUEST, 'currency_code', 0 )	== $paymentBasket->mc_currency ) )
				{
					$ret	=	$this->_outputInstructions( $paymentBasket );
					if ( $ret !== false ) {
						outputCbTemplate();
						$this->_outputRegTemplate();
						$ret	=	$paymentBasket->displayBasket()
								.	$ret;
					}
				} else {
					$this->_setErrorMSG(CBPTXT::T("Payment basket does not login."));
					$ret = false;
				}
				break;

			case 'showslip':

				// The user asked to see the payment slip.
				
				if ( isset($_GET['user']) && isset($_GET['cbpid']) )
				{
					$paymentBasketId = (int) cbGetParam( $_GET, 'cbpid', 0 );
					$exists = $paymentBasket->load( (int) $paymentBasketId );
					if ( $exists
						&& ( ( (int) cbGetParam( $_GET, 'user', 0 ) ) == $paymentBasket->user_id )
						&& ( ( (int) cbGetParam( $_GET, 'cbpbhs' ) ) == md5( $paymentBasket->shared_secret ) ) )
					{
						// log:
						$ipn					=&	$this->_logNotification( 'R', $_CB_framework->now(), $paymentBasket );
						if (   ( $paymentBasket->payment_status == 'NotInitiated' )
							&& ( $paymentBasket->payment_method == $this->getPayName() )
							&& ( $paymentBasket->gateway_account == $this->getAccountParam( 'id' ) ) ) {
							$privateVarsList	=	'id user_id time_initiated time_completed ip_addresses mc_gross mc_currency '
												.	'quantity item_number item_name shared_secret payment_status '
												.	'invoice period1 period2 period3 mc_amount1 mc_amount2 mc_amount3';
							$paymentBasket->bindObjectToThisObject( $ipn, $privateVarsList );
							$newPaymentState	=	$this->getAccountParam( 'pending_payment_state', 'Pending' );
							$this->updatePaymentStatus( $paymentBasket, 'web_accept', $newPaymentState, $ipn, 1, 0, 0, true, false );
						}
	
						if ( in_array( $paymentBasket->payment_status, array( 'NotInitiated', 'Pending' ) ) ) {
							$ret	=	$this->_outputSlip( $paymentBasket );
						}
					}
	
				}
				break;

			default:
				$ret				=	CBPTXT::Th("Unexpected result");
				break;
		}		
		return  $ret;
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
		if ( $this->hashPdtBackCheck( $this->_getReqParam( 'pdtback', '' ) ) ) {
			$paymentBasketId				=	(int) $this->_getReqParam( 'basket' );
			$paymentBasket->id				=	$paymentBasketId;
			$paymentBasket->payment_status	=	'RegistrationCancelled';
			$this->_setErrorMSG(CBPTXT::T("Payment cancelled."));
		}
		return false;
	}
	
	/**
	 * Cancels an existing recurring subscription
	 * (optional)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket paymentBasket object
	 * @param  cbpaidPaymentItem[]  $paymentItems redirect immediately instead of returning HTML for output
	 * @return boolean|string              TRUE if unsubscription done successfully, STRING if error
	 */
	protected function handleStopPaymentSubscription( $paymentBasket, $paymentItems )
	{
		return true;		// unsubscribe an offline paid membership should always work, as this will be handled offline anyway!
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
		$ipn->txn_id		=	null;
		$ipn->parent_txn_id	=	$payment->txn_id;

		$_CB_database->insertObject( $ipn->getTableName(), $ipn, $ipn->getKeyName() );

		$this->updatePaymentStatus( $paymentBasket, $ipn->txn_type, $ipn->payment_status, $ipn, 1, 0, 0, true );

		return true;
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
		return $this->cbsubsGatewayUrl( 'showinstructions', null, $paymentBasket, null, false, false, true, $requestParams );
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
		// Generate URLs for payment instructions:
		$instructions_url		=	$this->pspUrl( $paymentBasket, ( $paymentType == 'subscribe' ) );
		// $return_cancel_url	=	$this->getCancelUrl( $paymentBasket );
		$requestParams			=	$this->getSinglePaymentRequstParams( $paymentBasket );
		$prmImg					=	'offline_image';
		$prmCustImg				=	'custom_offline_image';
		$titleText				=	CBPTXT::T( $this->getAccountParam( 'button_title_text' ) );
		$altText				=	$titleText;
		$butId					=	'cbpaidButt' . strtolower( $this->getPayName() ) . '_' . $this->getAccountParam( 'id' );

		$customImage			=	trim( $this->getAccountParam( $prmCustImg ) );
		if ( $customImage == '' ) {
			$customImage		=	trim( $this->getAccountParam( $prmImg ) );
		}
		$payNameForCssClass		=	$this->getPayName();

		return cbpaidGatewaySelectorButton::getPaymentButton( $this->getAccountParam( 'id' ), $subMethod, $paymentType, $instructions_url, $requestParams, $customImage, $altText, $titleText, $payNameForCssClass, $butId );
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
		$prmImg					=	'offline_image';
		$prmCustImg				=	'custom_offline_image';
		$titleText				=	CBPTXT::T( $this->getAccountParam( 'button_title_text' ) );
		$altText				=	$titleText;
		$butId					=	'cbpaidButt' . strtolower( $this->getPayName() ) . '_' . $this->getAccountParam( 'id' );

		$customImage			=	trim( $this->getAccountParam( $prmCustImg ) );
		if ( $customImage == '' ) {
			$customImage		=	trim( $this->getAccountParam( $prmImg ) );
		}
		$payNameForCssClass		=	$this->getPayName();
		$newCurrency			=	$this->mainCurrencyOfGateway();
		return cbpaidGatewaySelectorButton::getChangeOfCurrencyButton( $paymentBasket, $newCurrency, $customImage, $altText, $titleText, $payNameForCssClass . ' ' . 'cbregconfirmtitleonclick', $butId );
	}

	/**
	 * PRIVATE METHODS OF THIS CLASS
	 */

    /**
     * Private method to output the payment instructions
     * @param  cbpaidPaymentBasket  $paymentBasket
     * @return bool|string
     */
	private function _outputInstructions( $paymentBasket )
	{
		if ( ( $paymentBasket->payment_method == 'offline' ) || ( $paymentBasket->payment_method == null ) ) {
			if ( $paymentBasket->payment_method == null ) {
				$paymentBasket->payment_method	=	$this->getPayName();
				$paymentBasket->gateway_account	=	$this->getAccountParam( 'id' );
				$paymentBasket->store();
			}
			// Generate URLs for payment slip:
			$slip_popup_window					=	$this->getAccountParam( 'slip_popup_window', 1 );
			$slip_urlHtmlSpecialchared			=	$this->_getSlipUrlHtmlSpecialchared( $paymentBasket, $slip_popup_window );

			$slip_link_text						=	CBPTXT::Th( $this->getAccountParam( 'slip_link_text', "Click here to confirm that you are paying off-line and to display the Payment slip to print" ) );
			$areYouSureText						=	CBPTXT::T( $this->getAccountParam( 'slip_link_areyousure_text', "This will lock this transaction for offline payment. Please continue now only if you are preparing the payment now. Are you paying now ?") );
			$ret	= "<div class=\"cbpaidInstructions\">" . CBPTXT::Th( $this->getAccountParam('regOfflineInstructions') ) . "</div>\n";
			$ret	.= '<div class="cbpaySlip"><a ';
			if ( $areYouSureText || $slip_popup_window ) {
				$ret	.=	'onclick="';
				if ( $areYouSureText ) {
					$ret	.=	'if ( confirm( \'' . CBPTXT::jsAddSlashes( htmlspecialchars( $areYouSureText ) ) . '\' ) ) ';
				}
				if ( $slip_popup_window ) {
					$ret	.=	'window.open(\'' . $slip_urlHtmlSpecialchared
							.	'\', \'payslip\', \'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no\'); return false;" '
							.	'target="_blank" ';
				} else {
					$ret	.=	' return true else return false;" ';
				}
			}
			$ret		.=	'href="'
						.	$slip_urlHtmlSpecialchared . '">' . $slip_link_text . "</a></div>\n";
		} else {
			$this->_setErrorMSG(CBPTXT::T("Another payment method has already been choosen to pay."));
			$ret = false;
		}
		return $ret;
	}

	/**
	 * Gets html for slip page
	 * 
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return string
	 */
	private function _outputSlip( $paymentBasket )
	{
		global $_CB_framework;

		$slip_html_title		=	CBPTXT::Th( $this->getAccountParam( 'slip_html_title', "Payment Slip No. " ) );
		$slip_html_for_site		=	CBPTXT::Th( $this->getAccountParam( 'slip_html_for_site', "For website:" ) );
		$slip_html_for_url		=	$this->getAccountParam( 'slip_site_url', 1 );
		$slip_html_for_item		=	CBPTXT::Th( $this->getAccountParam( 'slip_html_for_item', "For item:" ) );
		$slip_html_for_member	=	CBPTXT::Th( $this->getAccountParam( 'slip_html_for_member', "For member:" ) );
		$slip_html_reference	=	CBPTXT::Th( $this->getAccountParam( 'slip_html_reference', "Important: include our reference with your payment:" ) );
		$slip_reference_site	=	$this->getAccountParam( 'slip_reference_site', 1 );
		$slip_html_conclusion	=	CBPTXT::Th( $this->getAccountParam( 'slip_html_conclusion', "If you pay by check, please print and enclose this page with your check." ) );

		$slip_html_pure			=	$this->getAccountParam( 'slip_html_pure', 0 );
		$slip_popup_window		=	$this->getAccountParam( 'slip_popup_window', 1 );
		$slip_print_button		=	$this->getAccountParam( 'slip_print_button', 1 );
		
		outputCbTemplate();
		$this->_outputRegTemplate();
		$ret  = '<div class="cbpaidPaymentSlip">';
		
		if ( $slip_html_pure ) {

			$vars				=	array(	'[order_id]'			=>	$paymentBasket->id,
											'[item_number]'			=>	$paymentBasket->item_number,
											'[item_description]'	=>	$paymentBasket->item_name,
											'[user_id]'				=>	$paymentBasket->user_id,
											'[username]'			=>	$paymentBasket->username,
											'[address_name]'		=>	$paymentBasket->address_name,
											'[address_street]'		=>	$paymentBasket->address_street,
											'[address_city]'		=>	$paymentBasket->address_city,
											'[address_state]'		=>	$paymentBasket->address_state,
											'[address_zip]'			=>	$paymentBasket->address_zip,
											'[address_country]'		=>	$paymentBasket->address_country,
											'[address_country_code]' =>	$paymentBasket->address_country_code,
											'[first_name]'			=>	$paymentBasket->first_name,
											'[last_name]'			=>	$paymentBasket->last_name,
											'[order_table]'			=>	$paymentBasket->displayBasket(),
											'[sitename]'			=>	$_CB_framework->getCfg( 'sitename' ),
											'[live_site]'			=>	preg_replace( "/^(https?:\\/\\/)/i", '', $_CB_framework->getCfg( 'live_site' ) )
										 );
			$default_html		=	'<h2>Payment Slip No. [order_id]</h2>'
								.	'<h3 id="cbpaidWebsite">For website: [sitename]</h3>'
								.	'<p id="cbpaidAddress"><address>[live_site]</address></p>'
								.	'<h3 id="cbpaidItem">For item: [item_number]</h3>'
								.	'<h3 id="cbpaidUser">For member: [first_name] [last_name]</h3>'
								.	'<div>[order_table]</div>'
								.	'<p id="cbpaidReference"><strong>Important: include our reference with your payment: &nbsp;<u style=\"font-size:125%\">Number [order_id] / [live_site]</u></strong></p>'
								.	'<p id=\"cbpaidCheck\">If you pay by check, please print and enclose this page with your check.</p>'
								;
			$slip_html_custom	=	CBPTXT::Th( $this->getAccountParam( 'slip_html_custom', $default_html ) );

			$ret	.=	strtr( $slip_html_custom, $vars );
		} else {

			if ( $slip_html_title ) {
				$ret .= '<h2>' .$slip_html_title . ' ' . $paymentBasket->id . "</h2>\n";
			}
			if ( $slip_html_for_site ) {
				$ret .= "<h3 id=\"cbpaidWebsite\">" . $slip_html_for_site . ' ' . $_CB_framework->getCfg( 'sitename' ) . "</h3>\n";
			}
			if ( $slip_html_for_url ) {
				$ret .= "<p id=\"cbpaidAddress\"><address>" . $_CB_framework->getCfg( 'live_site' ) . "</address></p>\n";
			}
			if ( $slip_html_for_item ) {
				$ret .= "<h3 id=\"cbpaidItem\">" . $slip_html_for_item . ' ' . $paymentBasket->item_number . "</h3>\n";
			}
			if ( $slip_html_for_member ) {
				$ret .= "<h3 id=\"cbpaidUser\">" . $slip_html_for_member . ' ' . $paymentBasket->first_name . ' ' . $paymentBasket->last_name . "</h3>\n";
			}
			$ret .= $paymentBasket->displayBasket();
			if ( $slip_html_reference ) {
				$ret		.=	"<p id=\"cbpaidReference\"><strong>" . $slip_html_reference
							.	" &nbsp;<u style=\"font-size:125%\">" . CBPTXT::T("Number") . ' ' . $paymentBasket->id;
				if ( $slip_reference_site ) {
					$ret	.=	" / " . preg_replace( "/^(https?:\\/\\/)/i", '', $_CB_framework->getCfg( 'live_site' ) );
				}
				$ret		.=	"</u></strong></p>\n";
			}
			if ( $slip_html_conclusion ) {
				$ret .= "<p id=\"cbpaidCheck\">" . $slip_html_conclusion . "</p>\n";
			}

		}
		if ( $slip_popup_window ) {
			$ret .= "<div id=\"cbpaidPrint\" style=\"width:100%;text-align:center;\"><p><a href=\"javascript:void(window.print())\">" . CBPTXT::Th("PRINT") . "</a></p></div>\n";
			$ret .= "<div id=\"cbpaidClose\" style=\"width:100%;text-align:center;\"><p><a href=\"javascript:void(window.close())\">" . CBPTXT::Th("CLOSE") . "</a></p></div>\n";
		} else {
			if ( $slip_print_button ) {
				$slip_urlHtmlSpecialchared			=	$this->_getSlipUrlHtmlSpecialchared( $paymentBasket, true );
	
				$ret .= "<div id=\"cbpaidPrint\" style=\"width:100%;text-align:center;\"><p><a href=\"" . $slip_urlHtmlSpecialchared . "\" "
					.	'onclick="window.open(\'' . $slip_urlHtmlSpecialchared
					.	'\', \'payslip\', \'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no\'); return false;" '
					.	'target="_blank" '
					.	'>' . CBPTXT::Th("PRINT") . "</a></p></div>\n";
			}
		}

		$recordPaymentUrl	=	cbpaidApp::getBaseClass()->getRecordPaymentUrl( $paymentBasket );
		if( $recordPaymentUrl ) {
			$ret	.=	'<div id="cbpaidRecordPayment"><a href="' . $recordPaymentUrl . '" title="' . htmlspecialchars( CBPTXT::T("Record the offline payment now") ) . '">' . CBPTXT::Th("RECORD OFFLINE PAYMENT") . '</a></div>';
		}

		$ret .= "</div>";
		return $ret;
	}

	/**
	 * Gets htmlspeicalchared URL for slip page
	 * 
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  boolean              $popupWindow
	 * @param  string               $result         adds &result=$result
	 * @return string
	 */
	private function _getSlipUrlHtmlSpecialchared( $paymentBasket, $popupWindow, $result = 'showslip' )
	{
		$null			=	null;
		$basegetarray	=	$this->_baseUrlArray( null );
		$basegetarray['user']	=	$paymentBasket->user_id;
		$slip_url		=	cbSef( $this->getHttpsAbsURLwithParam( $basegetarray, 'pluginclass', false) . '&amp;result=' . htmlspecialchars( $result )
									. '&amp;cbpid=' . $paymentBasket->id . '&amp;cbpbhs=' . md5( $paymentBasket->shared_secret )
								, true, ( $popupWindow ? 'component' : 'html' ) );
		return $slip_url;
	}

	/**
	 * Logs notification
	 *
	 * @param  string                           $log_type
	 * @param  int                              $now
	 * @param  cbpaidPaymentBasket              $paymentBasket
	 * @return cbpaidPaymentNotification
	 */
	private function & _logNotification( $log_type, $now, $paymentBasket )
	{
		global $_CB_framework, $_CB_database;

		$ipn					=	new cbpaidPaymentNotification($_CB_database);
		$ipn->payment_method	=	$this->getPayName();
		$ipn->gateway_account	=	$this->getAccountParam( 'id' );
		$ipn->log_type			=	$log_type;
		$ipn->time_received		=	date('Y-m-d H:i:s', $now );
		$ipn->payment_basket_id	=	$paymentBasket->id;
		$ipn->raw_result 		=	cbGetParam( $_GET, 'result' );
		$ipn->raw_data			=	/* cbGetParam() not needed, we want raw info */ '$_POST=' . var_export( $_POST, true ) . ";\n";
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
		$ipn->tax				=	$paymentBasket->tax;
		$ipn->mc_gross			=	$paymentBasket->mc_gross;
		$ipn->payment_status	=	'Pending';
		$ipn->payment_date		=	date( 'H:i:s M d, Y T', $_CB_framework->now() );			// paypal-style
		$ipn->payment_type		=	'Offline';
		$ipn->txn_id			=	null;
		$ipn->txn_type			=	'web_accept';
		$ipn->recurring			=	0;

		$_CB_database->insertObject( $ipn->getTableName(), $ipn, $ipn->getKeyName() );

		return $ipn;
	}
}	// end class cbpaidoffline.

/**
 * Payment account class for this gateway: Stores the settings for that gateway instance, and is used when editing and storing gateway parameters in the backend.
 * No methods need to be implemented or overriden in this class, except to implement the private-type params used specifically for this gateway:
 */
class cbpaidGatewayAccountoffline extends cbpaidGatewayAccounthostedpage
{
}
