<?php
/**
* @version $Id: cbpaidsubscriptions.swisspostfinance.php 600 2010-07-30 17:02:20Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;
use CBLib\Registry\ParamsInterface;
use CBLib\Xml\SimpleXMLElement;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

// This gateway implements a payment handler using a hosted page at the PSP:
// Import class cbpaidHostedPagePayHandler that extends cbpaidPayHandler
// and implements all gateway-generic CBSubs methods.

/**
 * Payment handler class for this gateway: Handles all payment events and notifications, called by the parent class:
 * Please note that except the constructor and the API version this class does not implement any public methods.
 */
class cbpaidogoneoem extends cbpaidPayHandler		//TODO: refactor with extends cbpaidHostedPagePayHandler {
{
	/**
	 * Gateway API version used
	 * @var int
	 */
	public $gatewayApiVersion	=	"1.3.0";

	/**
	 * Constructor
	 * 
	 * @param  cbpaidGatewayAccount        $account
	 */
	public function __construct( $account )
	{
		global $_CB_framework;

		parent::__construct( $account );

		$this->_gatewayUrls					=	array(	'psp+normal' 	=>	$this->getAccountParam( 'psp_normal_url' ),		// 'secure.ogone.com/ncol/prod/orderstandard.asp.', 'orderstandard_utf8.asp' if utf8
														'psp+test'		=>	$this->getAccountParam( 'psp_test_url' ) );
		if ( $_CB_framework->outputCharset() == 'UTF-8' ) {
			foreach ( $this->_gatewayUrls as $k => $v ) {
				$this->_gatewayUrls[$k]		=	str_replace( 'orderstandard.asp', 'orderstandard_utf8.asp', $v );
			}
		}
	}

	/**
	 * Convert contry string (e.g. United States of America) into 2 or 3 letter (e.g. US or USA)
	 * Or country 2 or 3 letters code to country string in English
	 * Or betweeen country codes: Values for $nbLetters:
	 * 3  : country   -> 3 letters
	 * 2  : country   -> 2 letters
	 * -2 : 2 letters -> country
	 * -3 : 3 letters -> country
	 * 23 : 2 letters -> 3 letters
	 * 32 : 3 letters -> 2 letters
	 *
	 * @param  string $country    Full text country name (if $nbLetters = 2 or 3) or country code (if $nbLetters = -2 or -3 or 23 or 32)
	 * @param  int    $nbLetters  Number of letters code (2 or 3 for full name to code and -2 or -3 for code to name)
	 * @return string             2/3/full-letters country name
	 */
	protected function countryToLetters( $country, $nbLetters )
	{
		$countries		=	new cbpaidCountries();
		switch ( $nbLetters ) {
			case 3:
				$ret		=	$countries->countryToThreeLetters( $country );
				break;
			case 2:
				$ret		=	$countries->countryToTwoLetters( $country );
				break;
			case -2:
				$ret		=	$countries->twoLettersToCountry( $country );
				break;
			case -3:
				$ret		=	$countries->threeLettersToCountry( $country );
				break;
			case 23:
				$ret		=	$countries->twoToThreeLettersCountry( $country );
				break;
			case 32:
				$ret		=	$countries->threeToTwoLettersCountry( $country );
				break;
			default:
				trigger_error( 'Unknown nbLetters in countryToLetters', E_USER_WARNING );
				$ret		=	null;
				break;
		}
		if ( $ret === null ) {
			$n				=	( $nbLetters < 0 ? 255 : $nbLetters % 10 );
			$ret			=	substr( $country, 0, $n );
		}

		return $ret;
	}

	/**
	 * gives gateway button URL server name from gateway URL list
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @param  boolean              $autoRecurring   TRUE: autorecurring payment, FALSE: single payment
	 * @return string  server-name (with 'https://' )
	 */
	protected function pspUrl( /** @noinspection PhpUnusedParameterInspection */ $paymentBasket, $autoRecurring )
	{
		return $this->gatewayUrl( 'psp' );
	}

	/**
	 * PUBLIC METHODS CALLED BY CBSUBS (AND THAT CAN BE EXTENDED IF NEEDED BY GATEWAY:
	 */

	/**
	* Returns either a hidden form with a visible button or redirects directly to payment processing page
	* @param  UserTable            $user           object reflecting the user being registered
	* @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	* @param  boolean              $redirectNow    redirect immediately instead of returning HTML for output
	* @return string                               HTML  (or DOES REDIRECT if $redirectNow == TRUE)
	*/
	public function getPaymentBasketProcess( $user, $paymentBasket, $redirectNow )
	{
		$enable_processor			=	$this->getAccountParam( 'enabled', 0 );

		// No payment possible if processor is disabled:
		if ( ! $enable_processor ) {
			return null;
		}
		// If nothing is possible here, just return null:
		$ret						=	null;

		// Depending on basket and on gateway enabling, compute these 3 booleans: maximum only one will be true:
		list( /* $singlePaymentForced */, $subscriptionForced, /* $userChoicePossible */ )	=	$this->listPaymentSingleOrSubscribePossibilities( $enable_processor, $paymentBasket );

		$subMethod					=	( $subscriptionForced ? 'subscribe' : 'single' );

		$payment_methods_selection	=	$this->getAccountParam( 'payment_methods_selection' );
		switch ( $payment_methods_selection ) {

			case 'onsite':
				$pmlist				=	trim( $this->getAccountParam( 'cardtypes' ) );
				if ( $pmlist ) {
					$paymentMethods	=	explode( '|*|', $pmlist );
				} else {
					$paymentMethods	=	array();
				}
				break;

			case 'list':
				$pmlist				=	trim( $this->getAccountParam( 'pmlist' ) );
				if ( $pmlist ) {
					$paymentMethods	=	preg_split( '/\s*,\s*/', $pmlist );
				} else {
					$paymentMethods	=	null;
				}
				break;
			case 'gateway':
			default:
				$paymentMethods		=	null;
				break;
		}

		switch ( $redirectNow ) {
			case 'redirect':

				$requestParams		=	$this->_completePaymentRequestParams( $paymentBasket, $paymentMethods );
				$url				=	$this->pspUrl( $paymentBasket, false ) . '?' . http_build_query( $requestParams, null, '&' );
				$ret				=	array( $url );
				break;
			
			case 'radios':

				$ret				=	array();

				if ( $payment_methods_selection == 'onsite' ) {
					foreach ( $paymentMethods as $method ) {
						$PMbrand	=	explode( ':', $method );
						$ret[]		=	$this->getPayRadioRecepie( $paymentBasket, $subMethod, $PMbrand[0], $PMbrand[2] );
					}
				} else {
					$ret[]			=	$this->getPayRadioRecepie( $paymentBasket, $subMethod, 'psp', 'Credit Card' );	// CBPTXT::T("Credit Card")
				}
				break;

			case 'buttons':

				if ( $payment_methods_selection == 'onsite' ) {
					foreach ( $paymentMethods as $method ) {
						$PMbrand	=	explode( ':', $method );
						$currencies	=	$this->getAccountParam( $PMbrand[0] . '_currencies' );
						if ( ( $currencies == '' ) || in_array( $paymentBasket->mc_currency, explode( '|*|', $currencies ) ) ) {
							$ret[]	=	$this->getPayButtonRecepie( $paymentBasket, $subMethod, array( 'PM' => $PMbrand[1], 'BRAND' => $PMbrand[2] ), $PMbrand[2], $PMbrand[0] );
						} elseif ( $this->getAccountParam( 'currency_acceptance_mode' ) == 'A' ) {
							$ret[]	=	$this->getChangeOfCurrencyButton( $paymentBasket, $PMbrand[2], $PMbrand[0] );
						}
					}
				} else {
					if ( $this->allowedBasketCurrency( $paymentBasket->mc_currency, null ) === $paymentBasket->mc_currency ) {
						$ret[]		=	$this->getPayButtonRecepie( $paymentBasket, $subMethod, $paymentMethods );
					} elseif ( $this->getAccountParam( 'currency_acceptance_mode' ) == 'A' ) {
						$ret[]	=	$this->getChangeOfCurrencyButton( $paymentBasket );
					}
				}
				break;

			default:
				break;
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
		$result					=	cbGetParam( $_GET, 'result' );
		switch ( $result ) {
			case 'notify':
				// We got an INS automatic notification from the payment service provider:
				$this->handleNotification( $paymentBasket, $postdata );
				$allowHumanHtmlOutput	=	false;
				break;
			case 'success':		// there is no 'cancel' or 'failed' $result for 2co, as 1) the result is in a POST, and 2) in case of fail, there is no commeback
				// The user got redirected back from the Payment Service Provider:
				$this->handleReturn( $paymentBasket, $postdata );
				break;
			case 'cancel':		// there is no 'cancel' or 'failed' $result for 2co, as 1) the result is in a POST, and 2) in case of fail, there is no commeback
				// The user got redirected back from the Payment Service Provider:
				$this->handleCancel( $paymentBasket, $postdata );
				break;
			default:
				break;
		}
		return  null;			// no special displays for Ogone.
	}

	/**
	 * Attempts to authorize and capture a credit card for a single payment of a payment basket using PSP DirectLink
	 *
	 * @ param  array                $card                : $card['type'], $card['number'], $card['firstname'], $card['lastname'], $card['expmonth'], $card['expyear'], and optionally: $card['address'], $card['zip'], $card['country']
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  string               $returnText                  RETURN param
	 * @param  boolean              $transientErrorDoReschedule  RETURN param
	 * @return boolean|null                                      TRUE: succes, FALSE: failed or unknown result, NULL: not implemented
	 */
	public function processAutoRecurringPayment( $paymentBasket, &$returnText, &$transientErrorDoReschedule )
	{
		// form XML request:
		$formvars		=	$this->_fillinAutoRecuringDirectLinkRequstParams( $paymentBasket );
		$error			=	null;
		$status			=	null;
		$response		=	$this->_directLinkOperation( $paymentBasket, 'orderdirect', $formvars, 'Autorecurring', $error, $status );
		if ( $response === false ) {

			$user					=	CBuser::getUserDataInstance( $paymentBasket->user_id );
			$username				=	$user ? $user->username : '?';
			$returnText	=	sprintf( CBPTXT::T("FAILED Auto-recurring payment of %s for basket %s Order_id %s of %s (username %s - user id %s) using %s due to error %s."), $paymentBasket->renderPrice( null, null, null, false ), $paymentBasket->id, $paymentBasket->sale_id, $paymentBasket->first_name . ' ' . $paymentBasket->last_name, $username, $paymentBasket->user_id, $this->getPayName(), 'HTTP error ' . ': ' . $error . ' ' . 'Status' . ': ' . $status );
			$transientErrorDoReschedule	=	true;
			$return		=	false;

		} else {

			// clean logs for PCI compliance:
			$formvarsCleaned	=	$formvars;
			if ( isset( $formvars['CC'] ) ) {
				$formvarsCleaned['CC']	=	preg_replace( '/^.+(.{4})$/', 'XXXX XXXX XXXX \1', $formvars['CC'] );
			}
			unset( $formvarsCleaned['CVC'] );
			unset( $formvarsCleaned['Ecom_Payment_Card_Verification'] );
			if ( isset( $formvars['PSWD'] ) ) {
				$formvars['PSWD']	=	'********';
			}
			
			// Parse the response XML results:
			$paymentResult			=	$this->handleDirectLinkPaymentResult( $paymentBasket, $response, 'A', array( 'formvars' => $formvarsCleaned, 'xmlreply' => $response ) );

			$user					=	CBuser::getUserDataInstance( $paymentBasket->user_id );
			$username				=	$user ? $user->username : '?';
			if ( $paymentResult !== false ) {
				if ( ( $paymentResult === true ) && in_array( $paymentBasket->payment_status, array( 'Completed', 'Pending' ) ) ) {
					if ( $paymentBasket->payment_status == 'Completed') {
						$returnText	=	sprintf( CBPTXT::T("Completed Auto-recurring payment of %s for basket %s Order_id %s of %s (username %s - user id %s) using %s with txn_id %s and auth_id %s."), $paymentBasket->renderPrice( null, null, null, false ), $paymentBasket->id, $paymentBasket->sale_id, $paymentBasket->first_name . ' ' . $paymentBasket->last_name, $username, $paymentBasket->user_id, $this->getPayName(), $paymentBasket->txn_id, $paymentBasket->auth_id );
					} else {
						$returnText	=	sprintf( CBPTXT::T("Pending Auto-recurring payment of %s for basket %s Order_id %s of %s (username %s - user id %s) using %s with txn_id %s and auth_id %s for reason: %s."), $paymentBasket->renderPrice( null, null, null, false ), $paymentBasket->id, $paymentBasket->sale_id, $paymentBasket->first_name . ' ' . $paymentBasket->last_name, $username, $paymentBasket->user_id, $this->getPayName(), $paymentBasket->txn_id, $paymentBasket->auth_id, $paymentBasket->reason_code );
					}
					$transientErrorDoReschedule	=	false;
					$return		=	true;
				} else {
					$returnText	=	sprintf( CBPTXT::T("FAILED (%s) Auto-recurring payment of %s for basket %s Order_id %s of %s (username %s - user id %s) using %s due to error %s."), $paymentBasket->payment_status, $paymentBasket->renderPrice( null, null, null, false ), $paymentBasket->id, $paymentBasket->sale_id, $paymentBasket->first_name . ' ' . $paymentBasket->last_name, $username, $paymentBasket->user_id, $this->getPayName(), $paymentBasket->reason_code );
					$transientErrorDoReschedule	=	true;
					$return		=	false;
				}
			} else {
				$returnText	=	sprintf( CBPTXT::T("FAILED (Error) Auto-recurring payment of %s for basket %s Order_id %s of %s (username %s - user id %s) using %s due to error %s."), $paymentBasket->renderPrice( null, null, null, false ), $paymentBasket->id, $paymentBasket->sale_id, $paymentBasket->first_name . ' ' . $paymentBasket->last_name, $username, $paymentBasket->user_id, $this->getPayName(), $paymentBasket->reason_code );
				$transientErrorDoReschedule	=	true;
				$return		=	false;
			}

		}
		return $return;
	}

	/**
	* Cancels a subscription
	*
	* @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	* @param  cbpaidPaymentItem[]  $paymentItems   redirect immediately instead of returning HTML for output
	* @return boolean                              true if unsubscription done successfully, false if error
	*/
	public function stopPaymentSubscription( $paymentBasket, $paymentItems )
	{
		if ( $paymentBasket->mc_amount3 ) {
			// Recurring amount existing and if first amount existed it got payed OK: 
			$paymentBasket->unscheduleAutoRecurringPayments();
		}
		return true;
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
		$return					=	false;
		// form XML request:
		$operation				=	$lastRefund ?  'RFS' : 'RFD';
		$formvars				=	$this->_fillinMaintenanceDirectLinkRequstParams( $paymentBasket, $payment, $amount, $operation );
		$error					=	null;
		$status					=	null;
		$response				=	$this->_directLinkOperation( $paymentBasket, 'maintenancedirect', $formvars, 'Refund', $error, $status );
		if ( $response !== false ) {
			// Parse the response XML results:
			$type				=	$lastRefund ? '3' : '4';
			if ( isset( $formvars['PSWD'] ) ) {
				// Remove sensitive information from log:
				$formvars['PSWD']	=	'********';
			}
			$paymentResult		=	$this->handleDirectLinkPaymentResult( $paymentBasket, $response, $type, array( 'reasonforrefund' => $reasonText, 'formvars' => $formvars, 'xmlreply' => $response ), $payment );
			if ( $paymentResult === true ) {
				$return		=	true;
			}
		}
		return $return;
	}

	/**
	 * Checks if $proposedCurrency is allowed by the payment method, and returns another accepted one if not.
	 * 
	 * @param  string  $proposedCurrency
	 * @param  string  $payment_type
	 * @return string                     3-letter currency acceptable by this gateway
	 */
	public function allowedBasketCurrency( $proposedCurrency, $payment_type )
	{
		if ( $payment_type ) {
			$currencies		=	$this->getAccountParam( $payment_type . '_currencies' );
		} else {
			$currencies		=	$this->getAccountParam( 'currencies_accepted' );
		}
		if ( ! $currencies ) {
			// All currencies accepted: no changes:
			return $proposedCurrency;
		}
		$currencies		=	explode( '|*|', $currencies );
		if ( in_array( $proposedCurrency, $currencies ) ) {
			// Basket's currency is accepted: no changes:
			return $proposedCurrency;
		}
		$params				=	cbpaidApp::settingsParams();
		$mainCurrency		=	$params->get( 'currency_code' );
		if ( in_array( $mainCurrency, $currencies ) ) {
			return $mainCurrency;
		}
		$secondaryCurrency	=	$params->get( 'secondary_currency_code' );
		if ( in_array( $secondaryCurrency, $currencies ) ) {
			return $secondaryCurrency;
		}
		return array_shift( $currencies );
	}

	/**
	 * GATEWAY-INTERNAL SPECIFIC METHODS:
	 */

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
	private function getPayRadioRecepie( $paymentBasket, $subMethod, $paymentType, $defaultLabel )
	{
		if ( $paymentType == 'psp' ) {
			$cardtypesString		=	$this->getAccountParam( $paymentType . '_radio_cardtypes' );
			$cardtypes				=	$cardtypesString ? explode( '|*|', $cardtypesString ) : array();
		} else {
			$cardtypes				=	array( $paymentType );
		}
		$brandLabelHtml				=	$this->getAccountParam( $paymentType . '_radio_name', $defaultLabel );			// CBPTXT::T("Credit Card")
		$brandDescriptionHtml		=	CBPTXT::Th( $this->getAccountParam( $paymentType . '_radio_description' ) );

		if ( $brandLabelHtml === null ) {
			$brandLabelHtml			=	CBPTXT::Th( $this->getAccountParam( 'psp_human_name' ) );
		} else {
			$brandLabelHtml			=	CBPTXT::Th( $brandLabelHtml );
		}

		if ( $this->getAccountParam( 'payment_methods_selection' ) == 'onsite' ) {
			$currencies				=	$this->getAccountParam( $paymentType . '_currencies' );
		} else {
			$currencies				=	$this->getAccountParam( 'currencies_accepted' );
		}
		$payNameForCssClass			=	$this->getPayName();
		if ( ( $currencies == '' ) || in_array( $paymentBasket->mc_currency, explode( '|*|', $currencies ) ) ) {
			$paySafelyWith			=	CBPTXT::T( $this->getAccountParam( 'button_title_text' ) );
			$altText				=	strip_tags( sprintf( $paySafelyWith, $brandLabelHtml ) );
		} else {
			if ( $this->getAccountParam( 'payment_methods_selection' ) == 'onsite' ) {
				$altText			=	CBPTXT::T( $this->getAccountParam( $paymentType . '_currencies_description' ) );
			} else {
				$altText			=	CBPTXT::T( $this->getAccountParam( 'currency_acceptance_text' ) );
			}
			$payNameForCssClass		.=	' ' . 'cbregconfirmtitleonclick';
		}

		return cbpaidGatewaySelectorRadio::getPaymentRadio( $this->getAccountParam( 'id' ), $subMethod, $paymentType, $cardtypes, $brandLabelHtml, $brandDescriptionHtml, $altText, $payNameForCssClass );
		
	}

	/**
	 * Returns an HTML form with a visible button and hidden fields for the gateway
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket   paymentBasket object
	 * @param  string               $subMethod
	 * @param  array                $paymentMethods
	 * @param  string|null          $brandText
	 * @param  string|null          $brandName
	 * @return string                                HTML OR array with all params for the rendering function of CBSubs' template
	 */
	private function getPayButtonRecepie( $paymentBasket, $subMethod, $paymentMethods, $brandText = null, $brandName = null )
	{
		$requestParams				=	$this->_completePaymentRequestParams( $paymentBasket, $paymentMethods );
		$translatedBrandText		=	$this->translatedBrandText( $brandText );
		$paymentType				=	( $brandText === null ) ? '' : $brandName;
		$altText					=	htmlspecialchars( sprintf( CBPTXT::T("Pay with %s"), $translatedBrandText ) );
		// This is for automatic tool to catch the string: CBPTXT::T("Pay safely with %s");
		$titleText					=	htmlspecialchars( sprintf( CBPTXT::T( $this->getAccountParam( 'button_title_text', "Pay safely with %s") ), $translatedBrandText ) );

		$customImage				=	$this->customImage( $brandName );
		$payNameForCssClass			=	$this->getPayName();
		$butId						=	'cbpaidButt' . strtolower( $this->getPayName() );		// instead of previously: 'cbpaidButton_' . htmlspecialchars( $this->getPayName() )

		$pspUrl						=	$this->pspUrl( $paymentBasket, false );
		return cbpaidGatewaySelectorButton::getPaymentButton( $this->getAccountParam( 'id' ), $subMethod, $paymentType, $pspUrl, $requestParams, $customImage, $altText, $titleText, $payNameForCssClass, $butId );
	}

	/**
	 * Returns an HTML form with a visible button and hidden fields for the gateway
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @param  string|null          $brandText
	 * @param  string|null          $brandName
	 * @return string                               HTML OR array with all params for the rendering function of CBSubs' template
	 */
	private function getChangeOfCurrencyButton( $paymentBasket, $brandText = null, $brandName = null )
	{

		$newCurrency				=	$this->mainCurrencyOfBrand( $brandName );

		$translatedBrandText		=	$this->translatedBrandText( $brandText );
		$altText					=	htmlspecialchars( sprintf( CBPTXT::T("Pay with %s"), $translatedBrandText ) );

		$prmTitleText				=	( $brandName ? $brandName . '_currencies_description' : 'currency_acceptance_text' );
		$titleText					=	CBPTXT::T( $this->getAccountParam( $prmTitleText ) );

		$customImage				=	$this->customImage( $brandName );
		$payNameForCssClass			=	$this->getPayName();
		$butId						=	'cbpaidButt' . strtolower( $this->getPayName() );		// instead of previously: 'cbpaidButton_' . htmlspecialchars( $this->getPayName() )

		return cbpaidGatewaySelectorButton::getChangeOfCurrencyButton( $paymentBasket, $newCurrency, $customImage, $altText, $titleText, $payNameForCssClass . ' ' . 'cbregconfirmtitleonclick', $butId );
	}

	/**
	 * Utility function to determine the brand text of gateway or of card
	 * 
	 * @param  string  $brandText
	 * @return string
	 */
	private function translatedBrandText( $brandText )
	{
		if ( $brandText === null ) {
			$translatedBrandText	=	CBPTXT::T( $this->getAccountParam( 'psp_human_name' ) );
		} else {
			$translatedBrandText	=	CBPTXT::T( $brandText );
		}
		return $translatedBrandText;
	}

	/**
	 * Utility to determine custom image depending on brand name and settings
	 * 
	 * @param  string  $brandName
	 * @return string
	 */
	private function customImage( $brandName )
	{
		$prmCustImg					=	'custom_image';
		$prmImg						=	'image';

		if ( $brandName ) {
			$customImage			=	cbpaidApp::getLiveSiteFilePath( 'icons/cards/cc_3d_' . preg_replace( '/[^a-z0-9]/', '', strtolower( $brandName ) ) . '.png' );
		} else {
			$customImage			=	trim( $this->getAccountParam( $prmCustImg ) );
			if ( $customImage == "" ) {
				$customImage		=	trim( $this->getAccountParam( $prmImg ) );
			}
		}
		return $customImage;
	}

	/**
	 * Utility to find the currency to convert the basket to depending on the brand
	 * 
	 * @param  string  $brandName
	 * @return string
	 */
	private function mainCurrencyOfBrand( $brandName )
	{
		$params				=	cbpaidApp::settingsParams();
		$mainCurrency		=	$params->get( 'currency_code' );

		if ( $brandName ) {
			$currencies		=	$this->getAccountParam( $brandName . '_currencies' );
		} else {
			$currencies		=	null;
		}
		if ( ( ! $brandName ) || ( ! $currencies ) ) {
			$currencies		=	$this->getAccountParam( 'currencies_accepted' );
		}
		if ( $currencies == '' ) {
			// Desperately return main currency:
			return $mainCurrency;
		}
		$currencies			=	explode( '|*|', $currencies );
		if ( in_array( $mainCurrency, $currencies ) ) {
			return $mainCurrency;
		}
		$secondaryCurrency	=	$params->get( 'secondary_currency_code' );
		if ( in_array( $secondaryCurrency, $currencies ) ) {
			return $secondaryCurrency;
		}
		return array_shift( $currencies );
	}

	/**
	 * Performs an Ogone directLink operation
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  Payment basket
	 * @param  string               $operation      'orderdirect', 'maintenancedirect'
	 * @param  array                $formvars       Variables of the form
	 * @param  string               $paymentType    'Autorecurring'
	 * @param  string               $error          OUTPUT: Error text
	 * @param  int|null             $status         OUTPUT: status of HTTP Reply
	 * @return bool|null
	 */
	protected function _directLinkOperation( $paymentBasket, $operation, $formvars, $paymentType, &$error, &$status )
	{
		// form XML request:
		// $formvars		=	$this->_fillinAutoRecuringDirectLinkRequstParams( $paymentBasket );
		if ( $this->getAccountParam( 'sha_signature_directlink' ) ) {
			$this->_pspSign( $formvars, 'sha_signature_directlink' );
		}

		$directlink_url	=	preg_replace( '/orderstandard(_utf8)?\.asp$/', $operation . '.asp', $this->pspUrl( $paymentBasket, false ) );

		//	echo $formvars;

		//Send the XML via curl:
		$response		=	null;
		$status			=	null;
		$error			=	$this->_httpsRequest( $directlink_url, $formvars, 105, $response, $status, 'post', 'normal' );
		if( $error || ( $status != 200 ) || ! $response ) {
			global $_CB_framework;
			$now		=	$_CB_framework->now();
			$ipn		=&	$this->_prepareIpn( 'B', $paymentBasket->payment_status, $paymentType, 'PSP DirectLink unavailable', $now, 'utf-8' );
			$ipn->bindBasket( $paymentBasket );
			$errorText	=	'HTTPS POST Connection to payment gateway server failed (check system information in CBSubs Settings): ERROR: ' . $error . ' (' . ($status == -100 ? 'Timeout' : $status ) . ')';
			$ipn->setRawResult( $errorText );
			$ipn->setRawData(	'$response="' . addslashes( $response ) . "\"\n"
						.	'$status=' . addslashes( $status ) . ";\n"
						.	'$xml=' . $directlink_url . ";\n"
						.	'$_POST=' . var_export( $formvars, true ) . ";\n"
						);
			$this->_storeIpnResult( $ipn, 'DIRECTLINKDOWN' );
			$this->_setLogErrorMSG( 4, $ipn, $this->getPayName() . ': ' . $errorText, CBPTXT::T("Sorry, the payment server did not reply.") . ' ' . CBPTXT::T("Please contact site administrator to check payment status and error log.") );
			$response	=	false;
		}
		return $response;
	}

	/**
	 * Sign with SHA as configured the $requestParams
	 *
	 * @param  array   $requestParams      IN and OUT: add ['SHASign']
	 * @param  string  $psp_sha_signature  Signature string to add to each parameter
	 * @param  string  $psp_sha_algorithm  Hashing algorithm
	 * @return string                      Hash string
	 */
	private function _pspSignSHA( $requestParams, $psp_sha_signature, $psp_sha_algorithm )
	{
		$signParams								=	array();
		foreach ($requestParams as $k => $v) {
			if ( ( $v !== '' ) && ( $v !== null ) ) {
				$signParams[strtoupper( $k )]	=	$v;
			}
		}
		ksort( $signParams );

		$string									=	'';
		foreach ($signParams as $k => $v) {
			$string								.=	$k . '=' . $v . $psp_sha_signature;
		}
		if ( $psp_sha_algorithm == 'sha1' ) {
			return strtoupper( sha1( $string /* to trigger failed reply uncomment: . 'A' */ ) );
		} else {
			return strtoupper( hash( $psp_sha_algorithm, $string /* to trigger failed reply uncomment: . 'A' */ ) );
		}
	}

	/**
	 * Sign with SHA as configured the $requestParams
	 *
	 * @param  array  $requestParams   IN and OUT: add ['SHASign']
	 * @param  string $sha_signature   Signature to use (default: 'sha_signature', but can be 'sha_signature_directlink')
	 * @return void
	 */
	private function _pspSign( &$requestParams, $sha_signature = 'sha_signature' )
	{
		$psp_sha_signature						=	$this->getAccountParam( $sha_signature );
		$psp_sha_algorithm						=	$this->getAccountParam( 'sha_algorithm', 'sha1' );
		$requestParams['SHASign']				=	$this->_pspSignSHA( $requestParams, $psp_sha_signature, $psp_sha_algorithm );
	}

	/**
	 * Verifies signature
	 *
	 * @param  array  $requestParams  Request parameters
	 * @return boolean                TRUE: signature matchs, FALSE: does not match
	 */
	private function _pspVerifySignature( $requestParams )
	{
		$signParams								=	$requestParams;
		unset( $signParams['SHASIGN'] );
		$signParams								=	array();
		foreach ( $requestParams as $k => $v ) {
			if ( ( $k != 'SHASIGN' ) && ( $v !== '' ) && ( $v !== null ) ) {
				$signParams[$k]					=	stripslashes( cbGetParam( $requestParams, $k, '', _CB_NOTRIM ) );
			}
		}
		$psp_sha_signature						=	$this->getAccountParam( 'sha_signature_out' );
		$psp_sha_algorithm						=	$this->getAccountParam( 'sha_algorithm', 'sha1' );
		$shasign								=	$this->_pspSignSHA( $signParams, $psp_sha_signature, $psp_sha_algorithm );
		return ( $shasign === cbGetParam( $requestParams, 'SHASIGN' ) );
	}

	/**
	 * Compute the CBSubs payment_status based on gateway's params:
	 * 
	 * STATUS:	Status of the payment:
		Statuses in 1 digit are 'normal' statuses:
		0 means the payment is invalid (e.g. data validation error) or the processing is not complete either because it is still underway, or because the transaction was interrupted. If the cause is a validation error, an additional error code (*) (NCERROR) identifies the error.
		1 means the customer cancelled the transaction.
		2 means the acquirer did not authorise the payment.
		5 means the acquirer autorised the payment.
		9 means the payment was captured.
		Statuses in 2 digits correspond either to 'intermediary' situations or to abnormal events. When the second digit is:
		1, this means the payment processing is on hold.
		2, this means an unrecoverable error occurred during the communication with the acquirer. The result is therefore not determined. You must therefore call the acquirer's helpdesk to find out the actual result of this transaction.
		3, this means the payment processing (capture or cancellation) was refused by the acquirer whilst the payment had been authorised beforehand. It can be due to a technical error or to the expiration of the authorisation. You must therefore call the acquirer's helpdesk to find out the actual result of this transaction.
		4, this means our system has been notified the transaction was rejected well after the transaction was sent to your acquirer.
		5, this means our system hasn't sent the requested transaction to the acquirer since the merchant will send the transaction to the acquirer himself, like he specified in his configuration.
	 *
	 * ACCEPTANCE:
	 * Acquirer's acceptance (authorization) code.
	 * The acquirer sends back this code to confirm the amount of the transaction has been blocked on the card of the customer. The acceptance code is not unique.
	 *
	 * @param  array                $postdata        raw POST data received from the payment gateway
	 * @param  string               $reason          OUTPUT: reason_code
	 * @param  string               $previousStatus  previous CBSubs status
	 * @param  cbpaidPaymentBasket  $paymentBasket   (only for error logging purposes)
	 * @return string
	 */
	private function _paymentStatus( $postdata, &$reason, $previousStatus, /** @noinspection PhpUnusedParameterInspection */ &$paymentBasket )
	{
		$accept_payment_condition	=	$this->getAccountParam( 'accept_payment_condition' );
		$status						=	(int) cbGetParam( $postdata, 'STATUS', 0 );
		// Not needed: $acceptance	=	cbGetParam( $postdata, 'ACCEPTANCE', '' );
		$ncerror					=	cbGetParam( $postdata, 'NCERROR', '' );
		$ncerrorplus				=	cbGetParam( $postdata, 'NCERRORPLUS', '' );

		switch ( $status ) {
			case 0:
				if ( $ncerrorplus == 50001005 ) {
					// Expiry date error: Happens typically with autorecurring payments:
					$reason	=	'Card Expiry date expired';
					$status	=	'Denied';
				} else {
					$reason	=	'Incomplete or invalid';
					$status	=	'Error';
				}
				break;
			case 1:
				$reason		=	'Cancelled by client';
				$status		=	'RegistrationCancelled';
				break;
			case 2:
				$reason		=	'Authorization refused';
				$status		=	'Denied';
				break;
			case 4:
				$reason		=	'Order stored';
				$status		=	'Pending';
				break;
			case 41:
				$reason		=	'Waiting client payment';
				$status		=	'Pending';
				break;
			case 5:
				if ( ( $accept_payment_condition == 'pending' ) || ( $accept_payment_condition == 'authorized' ) ) {
					$reason	=	null;
					$status	=	'Completed';
				} else {
					$reason	=	'Authorized';
					$status	=	'Pending';
				}
				break;
			case 51:
				if ( $accept_payment_condition == 'pending' ) {
					$reason	=	null;
					$status	=	'Completed';
				}else {
					$reason	=	'Authorization waiting';
					$status	=	'Pending';
				}
				break;
			case 52:
				$reason		=	'Authorization not known';
				$status		=	'Pending';
				break;
			case 59:
				if ( ( $accept_payment_condition == 'pending' ) || ( $accept_payment_condition == 'authorized' ) ) {
					$reason	=	null;
					$status	=	'Completed';
				} else {
					$reason	=	'Author. to get manually';
					$status	=	'Pending';
				}
				break;
			case 6:
				$reason		=	'Authorized and canceled';
				$status		=	'Denied';
				break;
			case 61:
				$reason		=	'Author. deletion waiting';
				$status		=	'Pending';
				break;
			case 62:
				$reason		=	'Author. deletion uncertain';
				$status		=	$previousStatus;
				break;
			case 63:
				$reason		=	'Author. deletion refused';
				$status		=	$previousStatus;
				break;
			case 7:
				$reason		=	'Payment deleted';
				$status		=	'Refunded';
				break;
			case 71:
				$reason		=	'Payment deletion pending';
				$status		=	$previousStatus;
				break;
			case 72:
				$reason		=	'Payment deletion uncertain';
				$status		=	$previousStatus;
				break;
			case 73:
				$reason		=	'Payment deletion refused';
				$status		=	$previousStatus;
				break;
			case 74:
				$reason		=	'Payment deleted (not accepted)';
				$status		=	'Denied';
				break;
			case 75:
				$reason		=	'Deletion processed by merchant';
				$status		=	'Refunded';
				break;
			case 8:
				$reason		=	'Refund';
				$status		=	'Refunded';
				break;
			case 81:
				$reason		=	'Refund pending';
				$status		=	$previousStatus;
				break;
			case 82:
				$reason		=	'Refund uncertain';
				$status		=	$previousStatus;
				break;
			case 83:
				$reason		=	'Refund refused';
				$status		=	$previousStatus;
				break;
			case 84:
				$reason		=	'Payment declined by the acquirer (will be debited)';
				$status		=	'Denied';
				break;
			case 85:
				$reason		=	'Refund processed by merchant';
				$status		=	'Refunded';
				break;
			case 9:
				$reason		=	null;		//'Payment requested';
				$status		=	'Completed';
				break;
			case 91:
				if ( ( $accept_payment_condition == 'pending' ) || ( $accept_payment_condition == 'authorized' ) ) {
					$reason	=	null;
					$status	=	'Completed';
				} else {
					$reason	=	'Payment processing';
					$status	=	'Pending';
				}
				break;
			case 92:
				$reason		=	'Payment uncertain';
				$status		=	'Pending';
				break;
			case 93:
				$reason		=	'Payment refused';
				$status		=	'Denied';
				break;
			case 94:
				$reason		=	'Refund declined by the acquirer';
				$status		=	$previousStatus;
				break;
			case 95:
				$reason		=	null;	// 'Payment processed by merchant';
				$status		=	'Completed';
				break;
			case 97:
			case 98:
			case 99:
				$reason		=	'Being processed (intermediate technical status)';
				$status		=	'Pending';
				break;
		}
		if ( $ncerror ) {
			if ( $ncerrorplus == 'unknown order/0/s/' ) {
				$ncerrorplus .=	' : ' . CBPTXT::T("SHA1-IN pass phrase missing in the CBSubs settings.");
			} elseif ( $ncerrorplus == 'unknown order/1/s/' ) {
				$ncerrorplus .=	' : ' . CBPTXT::T("Mismatch of the SHA1-IN pass phrases in the PSP and CBSubs settings.");
			} elseif ( substr( $ncerrorplus, 0, 18 ) == 'unknown order/1/i/' ) {
				$ncerrorplus .=	' : ' . CBPTXT::T("IP Address of this server %s is not listed in authorized IP addresses at PSP settings in the IP address field of the Data and origin verification tab of Technical Parameters.", substr( $ncerrorplus, 18 ) );
			}
			$reason			.=	' (' . $ncerror . ( $ncerrorplus ? ': ' . $ncerrorplus : '' ) . ')';

			// $this->_setLogErrorMSG( 3, $paymentBasket, $this->getPayName() . ': ' . $reasonCode, null );
		}
		return $status;
	}

	/**
	 * Compute the refund status based on gateway's reply.
	 *
	 * @param  array    $postdata  raw POST data received from the payment gateway
	 * @param  string   $reason    OUT: reason_code
	 * @return boolean
	 */
	private function _refundInitiationStatus( $postdata, &$reason )
	{
		$status						=	(int) cbGetParam( $postdata, 'STATUS', 0 );
		switch ($status) {
			case 7:
				$reason		=	'Payment deleted';
				return true;
			case 71:
				$reason		=	'Payment deletion pending';
				return true;
			case 72:
				$reason		=	'Payment deletion uncertain';
				return FALSE;
			case 73:
				$reason		=	'Payment deletion refused';
				return FALSE;
			case 74:
				$reason		=	'Payment deleted (not accepted)';
				return FALSE;
			case 75:
				$reason		=	'Deletion processed by merchant';
				return true;
			case 8:
				$reason		=	'Refund';
				return true;
			case 81:
				$reason		=	'Refund pending';
				return true;
			case 82:
				$reason		=	'Refund uncertain';
				return FALSE;
			case 83:
				$reason		=	'Refund refused';
				return FALSE;
			case 84:
				$reason		=	'Payment declined by the acquirer (will be debited)';
				return FALSE;
			case 85:
				$reason		=	'Refund processed by merchant';
				return true;
			case 94:
				$reason		=	'Refund declined by the acquirer';
				return FALSE;
			default:
				$reason		=	'Unexpected DirectLink STATUS: ' . $status;
				$ncerror	=	cbGetParam( $postdata, 'NCERROR', '' );
				$ncerrorplus =	cbGetParam( $postdata, 'NCERRORPLUS', '' );
				if ( $ncerror ) {
					$reason	.=	' ' . 'Error: ' . $ncerror . ': ' . $ncerrorplus;
				}
				break;
		}
		return FALSE;
	}

	/**
	 * Gets Payment type text
	 *
	 * @param  array  $postdata  POST params
	 * @return string            e.g. "Visa Credit Card"
	 */
	private function _getPaymentType( $postdata )
	{
		$pm			=	preg_replace( '/[^-+ a-zA-Z0-9_]/', '', stripslashes( cbGetParam( $postdata, 'PM', '' ) ) );
		if ( $pm == 'CreditCard' ) {
			$brand	=	preg_replace( '/[^-+ a-zA-Z0-9_]/', '', stripslashes( cbGetParam( $postdata, 'BRAND', '' ) ) );
			$pm		=	( $brand ? $brand . ' ' : '' ) . 'Credit Card';
		}
		return $pm;
	}
	
	/**
	* Popoulates basic request parameters for gateway depending on basket (without specifying payment type)
	*
	* @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	* @return array                                $requestParams
	*/
	private function _getBasicRequstParams( $paymentBasket )
	{
		// Mandatory parameters:
		$requestParams							=	array();
		$requestParams['PSPID']					=	$this->getAccountParam( 'pspid' );
		$requestParams['orderID']				=	$paymentBasket->id;
		$requestParams['amount']				=	100 * sprintf( '%.2f', $paymentBasket->mc_gross );
		$requestParams['currency']				=	$paymentBasket->mc_currency;
		$requestParams['language']				=	$this->getAccountParam( 'language', 'en_US' );
		$requestParams['operation']				=	$this->getAccountParam( 'operation', 'SAL' );


		// Courtesy field (pre-filled but editable on credit card mask):
		$requestParams['CN']					=	$paymentBasket->first_name . ( $paymentBasket->first_name && $paymentBasket->last_name ? ' ' : '' ) . $paymentBasket->last_name;

		// Recommended anti-fraud fields:
		if ( $this->getAccountParam( 'givehiddenemail' ) && ( strlen( $paymentBasket->payer_email ) <= 50 ) ) {
			$requestParams['EMAIL']				=	$paymentBasket->payer_email;
		}

		if ( $this->getAccountParam( 'givehiddenaddress' ) ) {
			cbimport( 'cb.tabs' );				// needed for cbIsoUtf_substr()
			$addressFields						=	array(	'owneraddress'	=> array( $paymentBasket->address_street, 35 ),
															'ownerZIP'		=> array( $paymentBasket->address_zip, 10 ),
															'ownertown'		=> array( $paymentBasket->address_city, 25 ),
															'ownercty'		=> array( $this->countryToLetters( $paymentBasket->address_country, 2 ), 2 ) );
			foreach ( $addressFields as $k => $value_maxlength ) {
				$adrField						=	cbIsoUtf_substr( $value_maxlength[0], 0, $value_maxlength[1] );
				if ( $adrField ) {
					$requestParams[$k]			=	$adrField;
				}
			}
		}

		if ( $this->getAccountParam( 'givehiddentelno' ) && ( strlen( $paymentBasket->contact_phone ) <= 50 ) ) {
			$requestParams['ownertelno']		=	$paymentBasket->contact_phone;
		}

		// Recommended anti-fraud fields:
		if ( $this->getAccountParam( 'givedescription' ) ) {
			cbimport( 'cb.tabs' );
			$item_name							=	cbIsoUtf_substr( $paymentBasket->item_name, 0, 100 );
			if ( $item_name ) {
				$requestParams['COM']			=	$item_name;
			}
		}
		// Alias Manager option:
		$alias_manager							=	$this->getAccountParam( 'alias_manager' );
		if ( $alias_manager ) {
			$existing_alias						=	$this->getFinancialCoordinatesAlias( $paymentBasket->user_id );
			if ( $existing_alias && isset( $existing_alias['alias'] ) ) {
				$requestParams['ALIAS']			 =	$existing_alias['alias'];
				$requestParams['ALIASUSAGE']	 =	$this->getAccountParam( 'aliasusage_already_existing' );
			} else {
				if ( $alias_manager == 1 ) {
					// Enabled only for recurring payments: let's see if it is allowed to be autorecurring:
					$enable_processor				=	3;		// we allow autorecurring but let user choose, as choice is at payment gateway and enforcement is only settable at processor in this processor...
					$pay1subscribe2					=	$this->_getPaySubscribePossibilities( $enable_processor, $paymentBasket );
					if ( $pay1subscribe2 & 0x2 ) {
						// a payment subscription is possible:
						$alias_manager				=	2;
					}
				}
				if ( $alias_manager == 2 ) {
					$requestParams['ALIASUSAGE']		 =	$this->getAccountParam( 'aliasusage' );
					if ( $this->getAccountParam( 'aliasdeterminedby' ) == 'psp' ) {
						$requestParams['ALIASOPERATION'] =	'BYOGONE';
					} else {
						$requestParams['ALIAS']			 =	'cbsubs_customer_' . $paymentBasket->user_id . '_card_' . 1;
					}
				}
			}
		}
		return $requestParams;
	}

	/**
	* Popoulates basic request parameters for gateway depending on basket (without specifying payment type)
	*
	* @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	* @return array                                $requestParams
	*/
	private function _fillinAutoRecuringDirectLinkRequstParams( $paymentBasket )
	{
		// Fill-in basic params:
		$requestParams							=	$this->_getBasicRequstParams( $paymentBasket );
		$requestParams['USERID']				=	$this->getAccountParam( 'directlink_userid' );
		$requestParams['PSWD']					=	$this->getAccountParam( 'directlink_pswd' );

		$requestParams['RTIMEOUT']				=	90;			// this request is from a cron task, thus we use maximum timeout
		$requestParams['ECI']					=	9;			// Recurring (from e-commerce)

		$requestParams['orderID']				=	$paymentBasket->id . 'R' . ( intval( $paymentBasket->recur_times_used ) + 2 );
		
		return $requestParams;
	}

	/**
	* Popoulates basic request parameters for gateway depending on basket (without specifying payment type)
	*
	* @param  cbpaidPaymentBasket  $paymentBasket  PaymentBasket object
	* @param  cbpaidPayment        $payment        Payment object
	* @param  float                $amount         Amount in currency of the payment
	* @param  string               $operation      Maintenance operation: last Refund: 'RFS', not last: 'RFD', 'REN', 'DEL', 'SAL', 'SAS' (see DirectLink doc of Ogone, v 2.4.1)
	* @return array                                $requestParams
	*/
	private function _fillinMaintenanceDirectLinkRequstParams( /** @noinspection PhpUnusedParameterInspection */ $paymentBasket, $payment, $amount, $operation )
	{
		$requestParams							=	array();
		// Mandatory params:
		$requestParams['PSPID']					=	$this->getAccountParam( 'pspid' );
		$requestParams['USERID']				=	$this->getAccountParam( 'directlink_userid' );
		$requestParams['PSWD']					=	$this->getAccountParam( 'directlink_pswd' );
		$requestParams['PAYID']					=	$payment->txn_id;
		$requestParams['amount']				=	(int) ( 100 * sprintf( '%.2f', $amount ) );
		$requestParams['operation']				=	$operation;
		// Optional params, shouldn't hurt:
		$requestParams['language']				=	$this->getAccountParam( 'language', 'en_US' );
		$requestParams['RTIMEOUT']				=	90;			// this request is from a cron task, thus we use maximum timeout
		
		return $requestParams;
	}

	/**
	* Popoulates basic request parameters for gateway depending on basket (without specifying payment type)
	*
	* @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	* @return array                                $requestParams
	*/
	private function _fillinBasicRequstParams( $paymentBasket )
	{
		static $cache							=	array();

		if ( ! isset( $cache[(int) $paymentBasket->id][(int) $this->getAccountParam( 'id' )] ) ) { 
			// Fill-in basic params:
			$requestParams							=	$this->_getBasicRequstParams( $paymentBasket );
			// Add redirection URLs:
			/*
			accepturl
			URL of the web page to show the customer when the payment is authorized (status 5), accepted (status 9) or waiting to be accepted (pending, status 51 or 91).
			declineurl
			URL of the web page to show the customer when the acquirer declines the authorisation (status 2) more than the maximum authorised number of attempts.
			exceptionurl
			URL of the web page to show the customer when the payment result is uncertain (status 52 or 92).
			If this field is empty the customer will be shown the accepturl instead.
			cancelurl
			URL of the web page to show the customer when he cancels the payment (status 1).
			If this field is empty the declineurl will be shown to the customer instead.
			*/
			$comeBackUrl							=	$this->getSuccessUrl( $paymentBasket );
			$requestParams['accepturl']				=	$comeBackUrl;
			$requestParams['declineurl']			=	$comeBackUrl;
			$requestParams['exceptionurl']			=	$comeBackUrl;
			$requestParams['cancelurl']				=	$this->getCancelUrl( $paymentBasket );
			if ( $this->getAccountParam( 'payment_methods_selection' ) != 'gateway' && $this->getAccountParam( 'show_back_button' ) && ! preg_match( '/\s*,\s*/', $this->getAccountParam( 'pmlist' ) ) ) {
				$requestParams['backurl']			=	$paymentBasket->getShowBasketUrl( false );
			}	
			if ( $this->getAccountParam( 'iphonespecificlayout' ) && isset( $_SERVER['HTTP_USER_AGENT'] ) && ( strpos($_SERVER['HTTP_USER_AGENT'],"iPhone") || strpos($_SERVER['HTTP_USER_AGENT'],"iPod") ) ) {
				$requestParams['TP']				=	'PaymentPage_1_iPhone.htm';
			} elseif ( ( $this->getAccountParam( 'payment_template_type' ) == 'dynamic' ) && $this->getAccountParam( 'tp' ) ) {
				$requestParams['TP']				=	$this->getAccountParam( 'tp' );
			} else {
				$staticTemplateStyles				=	array( 'title', 'bgcolor', 'txtcolor', 'tblbgcolor', 'tbltxtcolor', 'buttonbgcolor', 'buttontxtcolor', 'fonttype', 'logo' );
				foreach ( $staticTemplateStyles as $v ) {
					$value							=	$this->getAccountParam( $v );
					if ( $value ) {
						$requestParams[strtoupper($v)]	=	$value;
					}
				}
			}
			$win3ds									=	$this->getAccountParam( 'win3ds' );
			if ( $win3ds ) {
				$requestParams['WIN3DS']			=	$win3ds;
			}

			// Finally cache the information in case we have multiple buttons on the site to choose the payment method (later):
			$cache[(int) $paymentBasket->id][(int) $this->getAccountParam( 'id' )]	=	$requestParams;
		}
		return $cache[(int) $paymentBasket->id][(int) $this->getAccountParam( 'id' )];
	}

	/**
	 * Gives CSS id for gateway using name of PSP and id of account
	 *
	 * @return string
	 */
	private function getGgatewayNameIdUserParamsPrefix( )
	{
		return 'gateway_' . $this->getPayName() . '_' . $this->getAccountParam( 'id' ) . '_';
	}

	/**
	 * Gets Financial alias information of the user (crypted)
	 *
	 * @param  int  $userId
	 * @return mixed|null
	 */
	private function getFinancialCoordinatesAlias( $userId )
	{
		$userPaymentParams						=	cbpaidUserParams::getUserParamsInstance( $userId, 'params' );
		$prefix									=	$this->getGgatewayNameIdUserParamsPrefix();
		$aliasInfo								=	$userPaymentParams->getUserParam( $prefix . 'alias' );
		if ( $aliasInfo ) {
			return $userPaymentParams->decryptAliasInfo( $aliasInfo, 'sf7Qsj-k,p.7/2sp0+2&a%YK85LrC3*fkI1?LfJ(25%).!$NWfE', 'CbS3unlkaDhnDonmDu3847fn*9)Lk)nus(2%$%km' );
		}
		return null;
	}

	/**
	 * Sets Financial alias information of the user (crypted)
	 *
	 * @param  int    $userId
	 * @param  mixed  $aliasInfo
	 * @return void
	 */
	private function setFinancialCoordinatesAlias( $userId, $aliasInfo )
	{
		$userPaymentParams						=	cbpaidUserParams::getUserParamsInstance( $userId, 'params' );
		$prefix									=	$this->getGgatewayNameIdUserParamsPrefix();
		$userPaymentParams->setUserParam( $prefix . 'alias', $userPaymentParams->encryptAliasInfo( $aliasInfo, 'sf7Qsj-k,p.7/2sp0+2&a%YK85LrC3*fkI1?LfJ(25%).!$NWfE', 'CbS3unlkaDhnDonmDu3847fn*9)Lk)nus(2%$%km' ) );
		$userPaymentParams->storeParams();
		$userPaymentParams->store();
	}
	/**
	 * Returns either a hidden form with a visible button or redirects directly to payment processing page
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket   payment Basket
	 * @param  array                $paymentMethods  payment methods: NULL: choices at PSP, SINGLE: array( 'PM' => 'CreditCard', 'BRAND' => 'VISA' ) or RESTRICTED SELECT: array( 'CreditCard', 'giropay', ... )
	 * @return array
	 */
	private function _completePaymentRequestParams( &$paymentBasket, $paymentMethods )
	{
		$requestParams							=	$this->_fillinBasicRequstParams( $paymentBasket );

		if ( is_array( $paymentMethods ) ) {
			if ( isset( $paymentMethods['PM'] ) ) {
				$requestParams['PM']				=	$paymentMethods['PM'];
				if ( isset( $paymentMethods['BRAND'] ) ) {
					$requestParams['BRAND']			=	$paymentMethods['BRAND'];
				}
			} else {
				$numberOfMethods					=	count( $paymentMethods );
				if ( $numberOfMethods  > 0 ) {
					if ( $numberOfMethods == 1 ) {
						$requestParams['PM']		=	$paymentMethods[0];
					} else {
						$requestParams['PMLIST']	=	implode( ';', $paymentMethods );
					}
				}
			}
		}

		$this->_pspSign( $requestParams );
		return $requestParams;
	}

	/**
	* The payment service provider server did a server-to-server notification: verify and handle it here:
	*
	* @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	* @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	* @return boolean|null                         TRUE if payment is Completed or Pending, FALSE if registration cancelled or ErrorMSG generated, or NULL if payment Denied or Refunded successfully
	*/
	private function handleNotification( $paymentBasket, $postdata )
	{
		if ( count( $postdata ) > 0 && isset( $postdata['orderID'] ) ) {
			// we prefer POST for sensitive data:
			$requestdata					=	$postdata;
		} else {
			// but if customer needs GET, we will work with it too:
			$requestdata					=	$this->_getGetParams();
		}
		return $this->_returnParamsHandler( $paymentBasket, $requestdata, 'I' );
	}

	/**
	* The user got redirected back from the payment service provider with a success message: let's see how successfull it was
	*
	* @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	* @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	* @return boolean|null                         TRUE if payment is Completed or Pending, FALSE if registration cancelled or ErrorMSG generated, or NULL if payment Denied or Refunded successfully
	*/
	private function handleReturn( $paymentBasket, /** @noinspection PhpUnusedParameterInspection */ $postdata )
	{
		$requestdata						=	$this->_getGetParams();
		return $this->_returnParamsHandler( $paymentBasket, $requestdata, 'R' );
	}

	/**
	* We got result of a DirectLink payment request: handle that.
	*
	* @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	* @param  string               $xmlreply           _POST data for saving edited tab content as generated with getEditTab
	* @param  string               $type               Type of return ('P' for DirectLink (first payment) 'A' for Autorecurring payment (DirectLink) )
	* @param  array                $additionalLogData  Additional data arrays to log with IPN
	* @param  cbpaidPayment        $payment            (optional): Needed only for refunds ($type '3' or '4')
	* @return boolean|null                             TRUE if payment is Completed or Pending, FALSE if registration cancelled or ErrorMSG generated, or NULL if payment Denied or Refunded successfully
	*/
	private function handleDirectLinkPaymentResult( $paymentBasket, $xmlreply, $type, $additionalLogData, $payment = null )
	{
		$xml								=	@new SimpleXMLElement( $xmlreply, LIBXML_NONET | ( defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0 ) );
		if ( $xml && ( $xml->getName() == 'ncresponse' ) ) {
			$requestdata					=	$xml->attributes();
			return $this->_returnParamsHandler( $paymentBasket, $requestdata, $type, $additionalLogData, $payment );
		} else {
			$this->_setLogErrorMSG( 3, null, $this->getPayName() . ': DirectLink reply is not the expected XML record: ' . $xmlreply, CBPTXT::T("Sorry, an unexpected reply has been received from the payment processor.") . ' ' . CBPTXT::T("Please contact site administrator to check error log.") );
			return false;
		}
	}

	/**
	* The user got redirected back from the payment service provider with a success message: let's see how successfull it was
	*
	* @param  cbpaidPaymentBasket  $paymentBasket       New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	* @param  array                $requestdata         Data returned by gateway
	* @param  string               $type                Type of return ('I' for INS, 'R' for PDT, 'P' for DirectLink (first payment) 'A' for Autorecurring payment (DirectLink), '3' for Refund, '4' for Partial Refund )
	* @param  array                $additionalLogData   Additional data arrays to log with IPN
	* @param  cbpaidPayment        $payment             (optional): Needed only for refunds ($type '3' or '4')
	* @return boolean|null                              TRUE if payment is Completed or Pending, FALSE if registration cancelled or ErrorMSG generated, or NULL if payment Denied or Refunded successfully
	*/
	private function _returnParamsHandler( $paymentBasket, $requestdata, $type, $additionalLogData = null, $payment = null )
	{
		global $_CB_framework, $_GET, $_POST;
		
		$ret								=	null;

		$paymentBasketId					=	(int) cbGetParam( $requestdata, 'orderID', 0 );
		if ( $paymentBasketId ) {
			$isDirectLink					=	in_array( $type, array( 'P', 'A', '3', '4' ) );

			if ( $isDirectLink && $paymentBasket->id && ( $paymentBasketId == $paymentBasket->id ) ) {
				$exists						=	true;
			} else {
				$exists						=	$paymentBasket->load( (int) $paymentBasketId );
			}
			if ( $exists
				&& ( ( $isDirectLink )
					|| (
						( cbGetParam( $requestdata, $this->_getPagingParamName( 'id' ), 0 ) == $paymentBasket->shared_secret )
						&& ( ! ( ( $type == 'R' ) && ( $paymentBasket->payment_status == 'Completed' ) ) ) )
				) )
			{
				/*
				 * Parameter		Value
					orderID			Your order reference
					amount			Order amount (not multiplied by 100)
					currency		Currency of the order
					PM				Payment method
					ACCEPTANCE		Acceptance code returned by acquirer
					STATUS			Transaction status
					CARDNO			Masked card number
					PAYID			Payment reference in our system
					NCERROR			Error code
					NCERRORPLUS		With DirectLink: Error text
					BRAND			Card brand (our system derives it from the card number) or similar information for other payment methods.
					ED			*	Expiry date			//TBD * not yet used
					TRXDATE		*	Transaction date	//TBD * not yet used
					CN				Cardholder/customer name
					SHASIGN			SHA signature composed by our system, if SHA-1-OUT configured by you.
				 */
				// MM/DD/YY -> YYYY-MM-DD:
				//	$trxdate		=	vsprintf( '20%3$02d-%1$02d-%2$02d', sscanf( cbGetParam( $requestdata, 'TRXDATE' ), '%02d/%02d/%02d' ) );
				
				// Log the return record:
				$log_type					=	$type;
				$reason						=	null;
				$paymentStatus				=	$this->_paymentStatus( $requestdata, $reason, $paymentBasket->payment_status, $paymentBasket );
				$paymentType				=	$this->_getPaymentType( $requestdata );
				$paymentTime				=	$_CB_framework->now();
				if ( $paymentStatus == 'Error' ) {
					$errorTypes				=	array( 'I' => 'D', 'R' => 'E', 'P' => 'V', 'A' => 'W', '3' => 'V', '4' => 'V' );
					if ( isset( $errorTypes[$type] ) ) {
						$log_type			=	$errorTypes[$type];
					}
				}
				$ipn						=&	$this->_prepareIpn( $log_type, $paymentStatus, $paymentType, $reason, $paymentTime, 'utf-8' );
				if ( $paymentStatus == 'Refunded' ) {
					// in case of refund we need to log the payment as it has same TnxId as first payment: so we need payment_date for discrimination:
					$ipn->payment_date		=	date( 'H:i:s M d, Y T', $paymentTime );			// paypal-style
				}
				$ipn->test_ipn				=	( $this->getAccountParam( 'normal_gateway' ) == '0' ? 1 : 0 );
				$message_type_to_log		=	array( 'R' => 'RETURN_TO_SITE', 'I' => 'NOTIFICATION', 'P' => 'DIRECTLINK PAYMENT', 'A' => 'AUTORECURRING DIRECTLINK PAYMENT', '3' => 'DIRECTLINK REFUND', '4' => 'DIRECTLINK PARTIAL REFUND' );
				$ipn->raw_data				=	'$message_type="' . ( isset( $message_type_to_log[$type] ) ? $message_type_to_log[$type] : 'UNKNOWN' ) . '";' . "\n";
				if ( $additionalLogData ) {
					foreach ( $additionalLogData as $k => $v ) {
						$ipn->raw_data		.=	'$' . $k . '="' . var_export( $v, true ) . '";' . "\n";
					}
				}
				$ipn->raw_data				.=	/* cbGetParam() not needed: we want raw info */ '$requestdata=' . var_export( $requestdata, true ) . ";\n"
											.	/* cbGetParam() not needed: we want raw info */ '$_GET=' . var_export( $_GET, true ) . ";\n"
											.	/* cbGetParam() not needed: we want raw info */ '$_POST=' . var_export( $_POST, true ) . ";\n"
											;

				if ( $paymentStatus == 'Error' ) {
					$paymentBasket->reason_code	=	$reason;
					$this->_storeIpnResult( $ipn, 'DIRECTLINKERROR:' . $reason );
					$this->_setLogErrorMSG( 4, $ipn, $this->getPayName() . ': ' . $reason, CBPTXT::T("Sorry, the payment server replied with an error.") . ' ' . CBPTXT::T("Please contact site administrator to check payment status and error log.") );
					$ret					=	false;
				} else {
					// Transfer fields from basket to IPN:
					$ipn->bindBasket( $paymentBasket );
					$ipn->address_name		=	$paymentBasket->first_name . ( $paymentBasket->first_name && $paymentBasket->last_name ? ' ' : '' ) . $paymentBasket->last_name;
					$basketToIpn			=	array( 'address_street', 'address_city', 'address_state', 'address_zip', 'address_country', 'address_country_code', 'payer_business_name', 'payer_email', 'contact_phone', 'vat_number' );
					foreach ( $basketToIpn as $k ) {
						$ipn->$k			=	$paymentBasket->$k;
					}

					// Transfer from the INS of gateway to our IPN:
					$insToIpn				=	array(
												'mc_currency'		=>	'currency',
												'sale_id'			=>	'orderID',
												'txn_id'			=>	'PAYID',
												'auth_id'			=>	'ACCEPTANCE',
												'residence_country'	=>	'IPCTY'			//	'CCCTY'
											 );
					foreach ( $insToIpn as $k => $v ) {
						$ipn->$k			=	cbGetParam( $requestdata, $v, null, _CB_NOTRIM );
					}
					$ipn->mc_gross			=	sprintf( '%.2f', cbGetParam( $requestdata, 'amount' ) );
					if ( ( $paymentStatus == 'Refunded' ) && ( $ipn->mc_gross > 0 ) ) {
						$ipn->mc_gross		=	'-' . $ipn->mc_gross;
						if ( in_array( $type, array( '3', '4' ) ) ) {
							$ipn->computeRefundedTax( $payment );
							$ipn->parent_txn_id	=	$payment->txn_id;
						}
					}
					// try to guess first and last name:
					if ( isset( $requestdata['CN'] ) ) {
						$cn					=	iconv( 'UTF-8', 'UTF-8//IGNORE', cbGetParam( $requestdata, 'CN', null, _CB_NOTRIM ) );
						
						$card_names			=	explode( ' ', $cn, 2 );
						if ( count( $card_names ) < 2 ) {
							$card_names		=	array( '', $cn );
						}
						$ipn->first_name	=	$card_names[0];
						$ipn->last_name		=	$card_names[1];
					}	
					$ipn->user_id			=	(int) $paymentBasket->user_id;
	
					$recurring				=	$type == 'A' ? true : false;
	
					// Alias Manager option:
					$alias_manager				=	$this->getAccountParam( 'alias_manager' );
					if ( $alias_manager ) {
						$aliasInfo['alias']		=	cbGetParam( $requestdata, 'ALIAS' );
						if ( $aliasInfo['alias'] ) {
							if ( in_array( $ipn->payment_status, array( 'Completed', 'Processed', 'Pending' ) ) ) {
								// Let's see if basket has been allowed to be autorecurring:
								$enable_processor	=	3;		// we allow autorecurring but let user choose, as choice is at payment gateway and enforcement is only settable at processor in this processor...
								$pay1subscribe2		=	$this->_getPaySubscribePossibilities( $enable_processor, $paymentBasket );
								if ( $pay1subscribe2 & 0x2 ) {
									// autorecurring allowed or mandatory:
									$recurring		=	true;
								}
		
								if ( isset( $requestdata['CARDNO'] ) ) {
									// Memorize alias only if payment has not been denied and that is first alias payment (means with is a masked CARDNO returned):
									$aliasInfo['paymenttype']			=	$paymentType;
									$aliasInfo['paymentmethod']			=	stripslashes( cbGetParam( $requestdata, 'PM', '' ) );
									$aliasInfo['paymentbrand']			=	stripslashes( cbGetParam( $requestdata, 'BRAND', '' ) );
									$aliasInfo['cardnumber']			=	stripslashes( cbGetParam( $requestdata, 'CARDNO' ) );
									// Format CC expiration date if there is one: 1215 --> 2015-12
									$ed									=	stripslashes( cbGetParam( $requestdata, 'ED' ) );
									if ( preg_match( '/\d{4}/', $ed ) ) {
										$ed								=	preg_replace( '/(\d{2})(\d{2})/', '20\2-\1', $ed );
									}
									$aliasInfo['cardexpirationdate']	=	$ed;
									$aliasInfo['cardname']				=	stripslashes( cbGetParam( $requestdata, 'CN', null, _CB_NOTRIM ) );
									$aliasInfo['lastpaymentbasket']		=	(int) $paymentBasket->id;
									$this->setFinancialCoordinatesAlias( $paymentBasket->user_id, $aliasInfo );
								}
							}
						}
					}
					if ( $recurring ) {
						if ( $type != 'A' ) {
							$ipn->txn_type			=	'subscr_signup';

							$ipn->subscr_id			=	(int) $paymentBasket->id;
							$ipn->subscr_date		=	$ipn->payment_date;
						} else {
							if ( $paymentStatus == 'Denied' ) {
								if ( $paymentBasket->reattempts_tried + 1 <= cbpaidScheduler::getInstance( $this )->retries ) {
									$ipn->txn_type	=	'subscr_failed';
								} else {
									$ipn->txn_type	=	'subscr_cancel';
								}
							} elseif ( in_array( $paymentStatus, array( 'Completed', 'Processed', 'Pending' ) ) ) {
								$ipn->txn_type		=	'subscr_payment';
							}
						}
					} else {
						$ipn->txn_type				=	'web_accept';
					}
	
					// DirectLink Payments and Auto-recurring payments do not have a SHA-OUT signature, other ones do have one that must be checked:
					if ( $isDirectLink || $this->_pspVerifySignature( $requestdata ) ) {
						if (	( $paymentBasketId == cbGetParam( $requestdata, 'orderID' ) )
							&&	( ( sprintf( '%.2f', $paymentBasket->mc_gross ) == $ipn->mc_gross ) || ( $ipn->payment_status == 'Refunded' ) )			// Partial refunds have smaller sums!
							&&	( $paymentBasket->mc_currency == $ipn->mc_currency ) )
						{
							if ( in_array( $ipn->payment_status, array( 'Completed', 'Processed', 'Pending', 'Refunded', 'Denied' ) ) ) {

								// We need to detect partial refunds by comparing the refunded amount to the initially sent amount:
								if ( ( $type == '4' ) || ( ( $ipn->payment_status == 'Refunded' ) && ( sprintf( '%.2f', $paymentBasket->mc_gross ) != sprintf( '%.2f', - $ipn->mc_gross ) ) ) ) {
									$ipn->payment_status		=	'Partially-Refunded';
								}

								$this->_storeIpnResult( $ipn, 'SUCCESS' );
								$this->_bindIpnToBasket( $ipn, $paymentBasket );
								$autorecurring_type				=	( $recurring ? 2 : 0 );		// 0: not auto-recurring, 1: auto-recurring without payment processor notifications, 2: auto-renewing with processor notifications updating $expiry_date
								$autorenew_type					=	( $recurring ? 2 : 0 );		// 0: not auto-renewing (manual renewals), 1: asked for by user, 2: mandatory by configuration
								if ( $recurring ) {
									$paymentBasket->reattempt	=	1;					// we want to reattempt auto-recurring payment in case of failure
								}
								$txnIdMultiplePaymentDates		=	in_array( $paymentStatus, array( 'Refunded', 'Partially-Refunded' ) );	// in case of refund we need to log the payment as it has same TnxId as first payment.

								$this->updatePaymentStatus( $paymentBasket, $ipn->txn_type, $ipn->payment_status, $ipn, 1, $autorecurring_type, $autorenew_type, $txnIdMultiplePaymentDates );

								if ( in_array( $ipn->payment_status, array( 'Completed', 'Processed', 'Pending' ) ) ) {
									$ret						=	true;

									if ( $recurring && ( $type != 'A' ) ) {
										// Now we need to schedule the automatic payments to be done by CBSubs using DirectLink, and which will trigger the auto-renewals (if it's not already the autorecurring payment (A)):
										$paymentBasket->scheduleAutoRecurringPayments();
									}
								}
							} else {
								$this->_storeIpnResult( $ipn, 'FAILED' );
								$paymentBasket->payment_status	=	$ipn->payment_status;
								$this->_setErrorMSG( '<div class="message">' . $this->getTxtNextStep( $paymentBasket ) . '</div>' );
								$paymentBasket->payment_status	=	'RedisplayOriginalBasket';
								$ret							=	false;
							}
						} elseif ( $isDirectLink && in_array( $type, array( '3', '4' ) ) ) {
							// DirectLink refunds with pending or refused statuses:
							$resultText							=	null;
							$ret								=	$this->_refundInitiationStatus( $requestdata, $resultText );
							$this->_setErrorMSG( $resultText );
							$ipn->raw_data						.=	'$REFUNDRESULT="' . addslashes( $resultText ) . "\";\n";
							// We are going through a 'Refund-pending' state here, so we can't update the payment yet, and CBSubs does not yet handle this state, as "Pending" is only for payment.
							// Log this as PDT, but wait for IPN for real refund for updating the basket:
							$ipn->payment_status				=	$ret ? ( $type == '3' ? 'Refunded' : 'Partially-Refunded' ) : 'Denied';
							$this->_storeIpnResult( $ipn, 'SUCCESS' );
						} else {
							$this->_storeIpnResult( $ipn, 'MISMATCH' );
							$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ': orderID, amount or currency missmatch.', CBPTXT::T("Sorry, the payment does not match the basket.") . ' ' . CBPTXT::T("Please contact site administrator to check error log.") );
							$ret								=	false;
						}
					} else {
						$this->_storeIpnResult( $ipn, 'SIGNERROR' );
						$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ': SHA-OUT hash does not match with gateway. Please check SHA-OUT setting.', CBPTXT::T("The SHA-OUT signature is incorrect.") . ' ' . CBPTXT::T("Please contact site administrator to check error log.") );
						$ret									=	false;
					}
				}
			}
		} else {
			$errorInfo					=	' Parameters:' . var_export( $requestdata, true ) . "\n";
			if ( $additionalLogData ) {
				foreach ( $additionalLogData as $k => $v ) {
					$errorInfo			.=	'$' . $k . '="' . var_export( $v, true ) . '";' . "\n";
				}
			}
			if ( isset( $requestdata['NCERRORPLUS'] ) ) {
				switch ( substr( $requestdata['NCERRORPLUS'], 0 , 17 ) ) {
					case 'unknown order/1/i':
						$errorClearText		=	sprintf( 'The IP address of your server received by the payment server %s is not set in the list of authorized IP addresses entered in the IP address field of the "Technical Informations" menu "Data and origin verification" tab in the payment portal.', substr( $requestdata['NCERRORPLUS'], 0 , 18 ) );
						break;
					case 'unknown order/1/s':
						$errorClearText		=	'The SHASign differs from the SHASign calculated using the value entered in the SHA-1-IN Signature field (password/pass phrase) in the "Data and origin verification" tab in the payment portal.';
						break;
					case 'unknown order/0/s':
						$errorClearText		=	'The SHASign field in CBSubs gateway setting is empty but an additional string (password/pass phrase) has been entered in the SHA-1-IN Signature field in the "Data and origin verification" tab in the payment portal.';
						break;
					default:
						$errorClearText		=	$requestdata['NCERRORPLUS'];
					break;
				}
			} else {
				$errorClearText				=	'Probably due to setting not active in: Technical Information: Transaction Feedback tab: "I want to receive transaction feedback parameters on the redirection URLs"';
			}
			$this->_setLogErrorMSG( 3, null, $this->getPayName() . ': The OrderId is missing in the return URL: ' . $errorClearText . $errorInfo, CBPTXT::T("Please contact site administrator to check error log.") );
		}
		return  $ret;
	}	// end function _returnParamsHandler

	/**
	* The user cancelled his payment
	*
	* @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	* @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	* @return null
	*/
	private function handleCancel( $paymentBasket, /** @noinspection PhpUnusedParameterInspection */ $postdata )
	{
		global $_GET;

		// The user cancelled his payment (and registration):
		
		if ( $this->hashPdtBackCheck( $this->_getReqParam( 'pdtback' ) ) ) {
			$paymentBasketId				=	(int) $this->_getReqParam( 'basket' );
			$exists							=	$paymentBasket->load( (int) $paymentBasketId );
			if ( $exists && ( $this->_getReqParam( 'id' ) == $paymentBasket->shared_secret ) && ( $paymentBasket->payment_status != 'Completed' ) ) {
				$paymentBasket->payment_status		=	'RegistrationCancelled';

				$messageToUser				=	$this->_getCancelledErrorText( $_GET );
				$this->_setErrorMSG( $messageToUser );
			}
		}
		return  null;
	}

	/**
	 * Try being more helpful than Ogone itself by giving instructions to customer to contact their credit-card issuing bank if it is their issuer who blocks the payment.
	 *
	 * @param  array   $requestdata  POST data
	 * @return string                Helpful error message
	 */
	private function _getCancelledErrorText( $requestdata )
	{

		$ret					=	CBPTXT::T("Payment cancelled.");

		// does not work, but not security-relevant here:	if ( $this->_pspVerifySignature( $requestdata ) ) {

		$ncerrorplus			=	cbGetParam( $postdata, 'NCERRORPLUS', '' );
		if ( $ncerrorplus ) {
			$ret				.=	' ' . htmlspecialchars( $ncerrorplus );
		} else {
			$status				=	(int) cbGetParam( $requestdata, 'STATUS', 0 );
			$ncerror			=	cbGetParam( $requestdata, 'NCERROR', '' );
			if ( ( $status == 2 ) || ( in_array( $ncerror, array(
																	30001001, // Payment refused by the acquirer
																	30001012, // Card black listed - Contact acquirer
																	30001090, // CVC check required by front end and returned invalid by acquirer
																	30001091, // ZIP check required by front end and returned invalid by acquirer
																	30001092, // Address check required by front end and returned invalid by acquirer
																	30001152, // Card/Supplier Amount limit reached (CSL)
																	30001154, // You have reached the usage limit allowed
																	30001155, // You have reached the usage limit allowed
																	30001156, // You have reached the usage limit allowed
																	30002001, // Payment refused by the financial institution
																	30021001, // Call acquirer support call number
																	30041001, // Retain card
																	30051001, // Authorization declined
																	30071001, // Retain card - special conditions
																	30131002, // You have reached the total amount allowed
																	30341001, // Suspicion of fraud
																	30411001, // Lost card
																	30431001, // Stolen card, pick up
																	30511001, // Insufficient funds
																	30521001, // No Authorization. Contact the issuer of your card
																	30541001, // Card expired
																	30571001, // Transaction not permitted on card
																	30581001, // Transaction not allowed on this terminal
																	30591001, // Suspicion of fraud
																	30601001, // The merchant must contact the acquirer
																	30611001, // Amount exceeds card ceiling
																	30621001, // Restricted card
																	30761001, // Card holder already contesting
																	31041001, // Inactive card
																	31081001, // Card refused
																	31101001, // no	Plafond transaction (major� du bonus) d�pass�
																	31111001, // no	Plafond mensuel (major� du bonus) d�pass�
																	31121001, // no	Plafond centre de facturation d�pass�
																	31131001, // no	Plafond entreprise d�pass�
																	31141001, // no	Code MCC du fournisseur non autoris� pour la carte
																	31151001, // no	Num�ro SIRET du fournisseur non autoris� pour la carte
																	31161001, // no	This is not a valid online banking account
																	40001134, // Authentication failed, please retry or cancel
																	60000007, // account number blocked
																	60000008, // specific direct debit block
																	60000009, // account number WKA
																	60000010, // administrative reason
																	60000011, // account number expired
																	60000012, // no direct debit authorisation given
																	60000013, // debit not approved
																	60001010, // direct debit not possible
																	60001011, // creditor payment not possible
																	60001012, // payer's account number unknown WKA-number
																	60001013, // payee's account number unknown WKA-number
																	60001014  // impermissible WKA transaction
			) ) ) ) {
				$paymentType	=	htmlspecialchars( $this->_getPaymentType( $requestdata ) );
				$text			=	CBPTXT::T("The payment has been refused by your %s issuer/bank")
								.	': '
								.	CBPTXT::T("Please contact your %s issuer/bank hotline (open 24 hours per day, 7 days a week, phone number is on the back of your %s or on existing issuer/bank invoices) to understand exact refusal reason and once resolved try paying again.")
								.	' '
								.	CBPTXT::T("Or use another payment method.");
				$ret			=	sprintf( $text, $paymentType, $paymentType, $paymentType );
			}
		}
		return $ret;
	}

	/**
	 * USED by XML interface ONLY !!! Renders URL to set in the 2Checkout interface for notifications
	 *
	 * @param  string  $gatewayId  Gateway account id
	 * @return string              HTML to display
	 */
	public function renderNotifyUrl( /** @noinspection PhpUnusedParameterInspection */ $gatewayId )
	{
		return $this->getNotifyUrl( null );
	}
}

/**
 * Payment account class for this gateway: Stores the settings for that gateway instance, and is used when editing and storing gateway parameters in the backend.
 *
 * OEM base
 * No methods need to be implemented or overriden in this class, except to implement the private-type params used specifically for this gateway:
 */
class cbpaidGatewayAccountogoneoem extends cbpaidGatewayAccounthostedpage
{
	/**
	 * USED by XML interface ONLY !!! Renders URL for notifications
	 *
	 * @param  string           $gatewayId  Gateway account id
	 * @param  ParamsInterface  $params
	 * @return string                       HTML to display
	 */
	public function renderNotifyUrl( $gatewayId, /** @noinspection PhpUnusedParameterInspection */ &$params ) {
		$payClass				=	$this->getPayMean();
		/** @var $payClass cbpaidogoneoem */
		return $payClass->renderNotifyUrl( $gatewayId );
		// $cbpaidMoney			=&	cbpaidMoney::getInstance();
		// $priceRoundings		=	$params->get('price_roundings', 100 );
	}
}

/**
 * Payment handler class for this gateway: Handles all payment events and notifications, called by the parent class:
 *
 * Gateway-specific
 * Please note that except the constructor and the API version this class does not implement any public methods.
 */
class cbpaidogone extends cbpaidogoneoem
{
}

/**
 * Payment account class for this gateway: Stores the settings for that gateway instance, and is used when editing and storing gateway parameters in the backend.
 *
 * Gateway-specific
 * No methods need to be implemented or overriden in this class, except to implement the private-type params used specifically for this gateway:
 */
class cbpaidGatewayAccountogone extends cbpaidGatewayAccountogoneoem
{
}
