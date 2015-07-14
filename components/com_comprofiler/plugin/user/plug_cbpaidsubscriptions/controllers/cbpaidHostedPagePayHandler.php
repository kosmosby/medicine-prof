<?php
/**
 * @version $Id: cbpaidHostedPagePayHandler.php 1608 2012-12-29 04:12:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;
use CBLib\Xml\SimpleXMLElement;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Paid Subscriptions Payments Handler for handling classic Hosted Payment pages
 */
abstract class cbpaidHostedPagePayHandler extends cbpaidPayHandler {

	/**
	 * PUBLIC METHODS CALLED BY CBSUBS (AND THAT CAN BE EXTENDED IF NEEDED BY GATEWAY:
	 */

	/**
	 * This is the main method to initiate a payment, depending on $redirectNow, it will either:
	 * 'redirect' : Redirect directly to the payment page of the payment gateway
	 * 'radios'   : Return all elements needed to display a list of selection radios, each with list of cards accepted, label of the radio, and a description that will be displayed when the radio is selected.
	 * 'buttons'  : Returns all elements needed to display a list of buttons with hidden form elements
	 * Note: this method can be called 2 times in case the radio is selected, to also get what's needed to display the payment buttons.
	 *
	 * $redirectNow Expected return array:
	 * ------------ ----------------------
	 * 'redirect' : return array( 'url_to_which_to_redirect' )
	 * 'radios'   : return array( array( account_id, submethod, paymentMethod:'single'|'subscribe', array(cardtypes), 'label for radio', 'description for radio' ), ... )
	 * 'buttons'  : return array( array( post_url, requestParams, customImage, altText, titleText, payNameForCssClass, butId ), ... )
	 *
	 * @param  UserTable            $user           object reflecting the user being registered (it can have id 0 or be NULL in future)
	 * @param  cbpaidPaymentBasket  $paymentBasket  Order Basket to be paid
	 * @param  string               $redirectNow    'redirect', 'radios', 'buttons', other: return null (see above)
	 * @return string|array                         array: See above, OR string: HTML to display in buttons area
	 */
	public function getPaymentBasketProcess( $user, $paymentBasket, $redirectNow ) {
		$enable_processor				=	$this->getAccountParam( 'enabled', 0 );

		// No payment possible if processor is disabled:
		if ( ! $enable_processor ) {
			return null;
		}

		// Depending on basket and on gateway enabling, compute these 3 booleans: maximum only one will be true:
		list( $singlePaymentForced, $subscriptionForced, $userChoicePossible )	=	$this->listPaymentSingleOrSubscribePossibilities( $enable_processor, $paymentBasket );

		// If nothing is possible here, just return null:
		$ret							=	null;

		switch ( $redirectNow ) {
			case 'redirect':

				if ( $subscriptionForced ) {
					$requestParams		=	$this->fillinSubscriptionRequstParams( $paymentBasket );
				} else {
					$requestParams		=	$this->fillinBasicRequstParams( $paymentBasket );
				}

				$url					=	$this->pspRedirectUrl( $paymentBasket, $requestParams, $subscriptionForced );

				// Here we can just return an arrray with url to which CBSubs will cbRedirect the user's browser for payment:
				// Or we can also return a string HTML to display e.g. specific instructions (like done for offline payments)
				if ( strlen( $url ) < 2038 ) {
					$ret				=	array( $url );
				} else {
					// Later: we need to build a form to auto-post on page load because IE6-8 does not allow longer URLs.
					// For now just make it a button that can be clicked:
					$ret				=	$this->getPaymentBasketProcess( $user, $paymentBasket, 'buttons' );
				}
				break;

			case 'radios':

				$ret					=	array();

				if ( $userChoicePossible || $singlePaymentForced || ( ! $subscriptionForced ) ) { // last condition is a safeguard to display at least a payment radio:
					$ret[]				=	$this->getPayRadioRecepie( $paymentBasket, 'single', 'single', 'Credit Card' );			// CBPTXT::T("Credit Card")
				}
				if ( $userChoicePossible || $subscriptionForced ) {
					$ret[]				=	$this->getPayRadioRecepie( $paymentBasket, 'subscribe', 'subscribe', 'Credit Card (with automatic following payments)' );		// CBPTXT::T("Credit Card (with automatic following payments)")
				}
				break;

			case 'buttons':

				if ( $userChoicePossible || $singlePaymentForced || ( ! $subscriptionForced ) ) { // last condition is a safeguard to display at least a payment button
					if ( $this->canPayBasketWithThisCurrency( $paymentBasket ) ) {
						$ret[]			=	$this->getPayButtonRecepie( $paymentBasket, 'single', 'single' );
					} elseif ( $this->getAccountParam( 'currency_acceptance_mode' ) == 'A' ) {
						$ret[]			=	$this->getChangeOfCurrencyButton( $paymentBasket, 'single', 'single' );
					}
				}

				if ( $userChoicePossible || $subscriptionForced ) {
					if ( $this->canPayBasketWithThisCurrency( $paymentBasket ) ) {
						$ret[]			=	$this->getPayButtonRecepie( $paymentBasket, 'subscribe', 'subscribe' );
					} elseif ( $this->getAccountParam( 'currency_acceptance_mode' ) == 'A' ) {
						$ret[]			=	$this->getChangeOfCurrencyButton( $paymentBasket, 'subscribe', 'subscribe' );
					}
				}
				break;

			default:
				break;
		}
		return $ret;
	}
	/**
	 * This is the main method to handle the return or the notification of a payment, depending on $redirectNow, it will either:
	 * Handles the gateway-specific result of payments (redirects back to this site and gateway notifications). WARNING: unchecked access !
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket         New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata              _POST data for saving edited tab content as generated with getEditTab
	 * @param  boolean              $allowHumanHtmlOutput  Input+Output: set to FALSE if it's an IPN, and if it is already false, keep quiet
	 * @return string                                      HTML to display if frontend, text to return to gateway if notification, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	 */
	public function resultNotification( $paymentBasket, $postdata, &$allowHumanHtmlOutput ) {
		$result			=	$this->_getResultParamFromUrl();

		switch ( $result ) {
			case 'notify':
				// We got an INS automatic notification from the payment service provider:
				$ret	=	$this->handleNotification( $paymentBasket, $postdata );
				$allowHumanHtmlOutput	=	false;
				break;
			case 'success': // there is no 'cancel' or 'failed' $result for 2co, as 1) the result is in a POST, and 2) in case of fail, there is no commeback
				// The user got redirected back from the Payment Service Provider:
				$ret	=	$this->handleReturn( $paymentBasket, $postdata );
				break;
			case 'cancel': // there is no 'cancel' or 'failed' $result for 2co, as 1) the result is in a POST, and 2) in case of fail, there is no commeback
				// The user got redirected back from the Payment Service Provider:
				$ret	=	$this->handleCancel( $paymentBasket, $postdata );
				break;
			default:
				$ret	=	$this->handleOtherResult( $paymentBasket, $postdata, $result );
				break;
		}

		// no need to print out the success of the notification:
		if ( ( $ret === 1 ) || ( $ret === true ) ) {
			$ret		=	null;
		}

		return  $ret;
	}

	/**
	 * Returns text 'using your xxxx account no....' displayed after successful payment.
	 * This gives opportunity to personalize with gateway-specific text.
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return string  Text
	 */
	public function getTxtUsingAccount( $paymentBasket ) {
		//FIXME: in future this should be $payment and NOT basket!

		return ' ' . CBPTXT::P( 'using [PAYMENTMETHOD]', array( '[PAYMENTMETHOD]' => CBPTXT::T( $paymentBasket->payment_type ) ) );
	}

	/**
	 * Attempts to validate a successful recurring payment
	 * (optional)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  string               $returnText                  RETURN param
	 * @param  boolean              $transientErrorDoReschedule  RETURN param
	 * @return boolean|null                                      TRUE: succes, FALSE: failed or unknown result, NULL: not implemented
	 */
	public function processAutoRecurringPayment( $paymentBasket, &$returnText, &$transientErrorDoReschedule ) {
		return $this->processScheduledAutoRecurringPayment( $paymentBasket, $returnText, $transientErrorDoReschedule );
	}

	/**
	 * Cancels an existing recurring subscription
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket paymentBasket object
	 * @param  cbpaidPaymentItem[]  $paymentItems redirect immediately instead of returning HTML for output
	 *
	 * @return boolean|string              TRUE if unsubscription done successfully, STRING if error
	 */
	public function stopPaymentSubscription( $paymentBasket, $paymentItems ) {
		return $this->handleStopPaymentSubscription( $paymentBasket, $paymentItems );
	}

	/**
	 * PRIVATE METHODS OF THIS CLASS
	 */

	/**
	 * Popoulates basic request parameters for gateway depending on basket (without specifying payment type)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket   paymentBasket object
	 * @return array                                 Returns array $requestParams
	 */
	private function fillinBasicRequstParams( $paymentBasket ) {
		static $cache																=	array();

		if ( ! isset( $cache[(int) $paymentBasket->id][(int) $this->getAccountParam( 'id' )] ) ) {
			// fill-in basic params:
			$requestParams															=	$this->getSinglePaymentRequstParams( $paymentBasket );

			// cache the information in case we have multiple buttons on the site to choose the payment method (later):
			$cache[(int) $paymentBasket->id][(int) $this->getAccountParam( 'id' )]	=	$requestParams;
		}

		return $cache[(int) $paymentBasket->id][(int) $this->getAccountParam( 'id' )];
	}

	/**
	 * Optional function: Only for gateways with recurring payments:
	 * Popoulates subscription request parameters for gateway depending on basket (without specifying payment type)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket   paymentBasket object
	 * @return array                                 Returns array $requestParams
	 */
	private function fillinSubscriptionRequstParams( $paymentBasket ) {
		static $cache																=	array();

		if ( ! isset( $cache[(int) $paymentBasket->id][(int) $this->getAccountParam( 'id' )] ) ) {
			// fill-in subscription params:
			$requestParams															=	$this->getSubscriptionRequstParams( $paymentBasket );

			// cache the information in case we have multiple buttons on the site to choose the payment method (later):
			$cache[(int) $paymentBasket->id][(int) $this->getAccountParam( 'id' )]	=	$requestParams;
		}

		return $cache[(int) $paymentBasket->id][(int) $this->getAccountParam( 'id' )];
	}

	/**
	 * METHODS TO IMPLEMENT IN PAYMENT HANDLER:
	 */

	/**
	 * Popoulates basic request parameters for gateway depending on basket (without specifying payment type)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket   paymentBasket object
	 * @return array                                 Returns array $requestParams
	 */
	abstract protected function getSinglePaymentRequstParams( $paymentBasket );
	/**
	 * Optional function: only needed for recurring payments:
	 * Popoulates subscription request parameters for gateway depending on basket (without specifying payment type)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket   paymentBasket object
	 * @return array                                 Returns array $requestParams
	 */
	abstract protected function getSubscriptionRequstParams( $paymentBasket );
	/**
	 * The user got redirected back from the payment service provider with a success message: Let's see how successfull it was
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	 * @return string                               HTML to display if frontend, text to return to gateway if notification, FALSE if XML error (and not yet ErrorMSG generated), or NULL if nothing to display
	 */
	abstract protected function handleReturn( $paymentBasket, $postdata );
	/**
	 * The user cancelled his payment
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	 * @return string                               HTML to display if frontend, text to return to gateway if notification, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	 */
	abstract protected function handleCancel( $paymentBasket, $postdata );
	/**
	 * The payment service provider server did a server-to-server notification: Verify and handle it here:
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	 * @return string                               HTML to display if frontend, text to return to gateway if notification, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	 */
	abstract protected function handleNotification( $paymentBasket, $postdata );
	/**
	 * There is a gateway-custom result: Verify and handle it here:
	 * (optional)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  New empty object. returning: includes the id of the payment basket of this callback (strictly verified, otherwise untouched)
	 * @param  array                $postdata       _POST data for saving edited tab content as generated with getEditTab
	 * @param  string               $result         result= get parameter, other than 'notify', 'success' or 'cancel'.
	 * @return string                               HTML to display if frontend, text to return to gateway if notification, FALSE if registration cancelled and ErrorMSG generated, or NULL if nothing to display
	 */
	protected function handleOtherResult( /** @noinspection PhpUnusedParameterInspection */ $paymentBasket, $postdata, $result ) {
		return null;
	}
	/**
	 * Attempts to validate a successful recurring payment
	 * (optional)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  string               $returnText                  RETURN param
	 * @param  boolean              $transientErrorDoReschedule  RETURN param
	 * @return boolean|null                                      TRUE: succes, FALSE: failed or unknown result, NULL: not implemented
	 */
	protected function processScheduledAutoRecurringPayment( /** @noinspection PhpUnusedParameterInspection */ $paymentBasket, &$returnText, &$transientErrorDoReschedule ) {
		// Override if needed
		$returnText						=	$this->getPayName() . ' ' . 'processAutoRecurringPayment not supported';
		$transientErrorDoReschedule		=	false;
		return null;
	}
	/**
	 * Cancels an existing recurring subscription
	 * (optional)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket paymentBasket object
	 * @param  cbpaidPaymentItem[]  $paymentItems redirect immediately instead of returning HTML for output
	 * @return boolean|string                     TRUE if unsubscription done successfully, STRING if error
	 */
	protected function handleStopPaymentSubscription( /** @noinspection PhpUnusedParameterInspection */ $paymentBasket, $paymentItems ) {
		// Override if needed
		return $this->getPayName() . ' ' . 'stopPaymentSubscription not supported';
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
	protected function pspUrl( /** @noinspection PhpUnusedParameterInspection */ $paymentBasket, $autoRecurring ) {
		return $this->gatewayUrl( 'psp' );
	}
	/**
	 * Returns https redirect URL for redirections to gateway for payment with $requestParams
	 *
	 * @param  cbpaidPaymentBasket|null  $paymentBasket  paymentBasket object
	 * @param  array                     $requestParams
	 * @param  boolean                   $autoRecurring  TRUE: autorecurring payment, FALSE: single payment
	 * @return string                                    Full URL for redirect including https:// in front
	 */
	protected function pspRedirectUrl( $paymentBasket, $requestParams, $autoRecurring ) {
		return $this->pspUrl( $paymentBasket, $autoRecurring ) . '?' . http_build_query( $requestParams, null, '&' );
	}
	/**
	 * Returns an array for the 'radios' array of $redirectNow type:
	 * return array( account_id, subMethod, paymentMethod:'single'|'subscribe', array(cardtypes), 'label for radio', 'description for radio' )
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @param  string               $subMethod
	 * @param  string               $paymentType
	 * @param  string               $defaultLabel
	 * @return array
	 */
	protected function getPayRadioRecepie( $paymentBasket, $subMethod, $paymentType, $defaultLabel ) {
		$cardtypesString			=	$this->getAccountParam( $paymentType . '_radio_cardtypes' );
		$cardtypes					=	$cardtypesString ? explode( '|*|', $cardtypesString ) : array();

		$brandLabelHtml				=	$this->getAccountParam( $paymentType . '_radio_name', $defaultLabel );
		$brandDescriptionHtml		=	CBPTXT::Th( $this->getAccountParam( $paymentType . '_radio_description' ) );

		if ( $brandLabelHtml === null ) {
			$brandLabelHtml			=	CBPTXT::T( $this->getAccountParam( 'psp_human_name' ) );
		} else {
			$brandLabelHtml			=	CBPTXT::T( $brandLabelHtml );									// CBPTXT::T("Credit Card")
		}

		$payNameForCssClass			=	$this->getPayName();
		if ( $this->canPayBasketWithThisCurrency( $paymentBasket ) ) {
			$paySafelyWith			=	CBPTXT::T( $this->getAccountParam( 'button_title_text' ) );
			$altText				=	sprintf( $paySafelyWith, $brandLabelHtml );
		} else {
			$altText				=	CBPTXT::T( $this->getAccountParam( 'currency_acceptance_text' ) );
			$payNameForCssClass		.=	' ' . 'cbregconfirmtitleonclick';
		}

		return cbpaidGatewaySelectorRadio::getPaymentRadio( $this->getAccountParam( 'id' ), $subMethod, $paymentType, $cardtypes, $brandLabelHtml, $brandDescriptionHtml, $altText, $payNameForCssClass );
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
	protected function getPayButtonRecepie( $paymentBasket, $subMethod, $paymentType ) {
		$brandText				=	CBPTXT::T( $this->getAccountParam( 'psp_human_name' ) );

		if ( $paymentType == 'single' ) {
			$requestParams	=	$this->fillinBasicRequstParams( $paymentBasket );
			$prmImg			=	'image';
			$prmCustImg		=	'custom_image';
			$altText		=	sprintf( CBPTXT::T( 'Pay with %s' ), $brandText );
			$titleText		=	sprintf( CBPTXT::T( $this->getAccountParam( 'button_title_text', 'Pay safely with %s' ) ), $brandText );
			$butId			=	'cbpaidButt' . strtolower( $this->getPayName() );
		} elseif ( $paymentType == 'subscribe' ) {
			$requestParams	=	$this->fillinSubscriptionRequstParams( $paymentBasket );
			$prmImg			=	'subscribe_image';
			$prmCustImg		=	'subscribe_custom_image';
			$altText		=	sprintf( CBPTXT::T( 'Subscribe with %s' ), $brandText );
			$titleText		=	sprintf( CBPTXT::T( $this->getAccountParam( 'subscribe_button_title_text', 'Subscribe safely with %s' ) ), $brandText );
			$butId			=	'cbpaidButt' . strtolower( $this->getPayName() ) . 'subscr';
		} else {
			return CBPTXT::T("Unknown payment type");
		}

		$customImage			=	trim( $this->getAccountParam( $prmCustImg ) );
		if ( $customImage == '' ) {
			$customImage		=	trim( $this->getAccountParam( $prmImg ) );
		}
		$payNameForCssClass		=	$this->getPayName();

		return cbpaidGatewaySelectorButton::getPaymentButton( $this->getAccountParam( 'id' ), $subMethod, $paymentType, $this->pspUrl( $paymentBasket, ( $paymentType == 'subscribe') ), $requestParams, $customImage, $altText, $titleText, $payNameForCssClass, $butId );
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
	protected function getChangeOfCurrencyButton( $paymentBasket, /** @noinspection PhpUnusedParameterInspection */ $subMethod, $paymentType ) {
		$brandText				=	CBPTXT::T( $this->getAccountParam( 'psp_human_name' ) );

		if ( $paymentType == 'single' ) {
			$prmImg			=	'image';
			$prmCustImg		=	'custom_image';
			$altText		=	sprintf( CBPTXT::T( 'Pay with %s' ), $brandText );
			$butId			=	'cbpaidButt' . strtolower( $this->getPayName() );
		} elseif ( $paymentType == 'subscribe' ) {
			$prmImg			=	'subscribe_image';
			$prmCustImg		=	'subscribe_custom_image';
			$altText		=	sprintf( CBPTXT::T( 'Subscribe with %s' ), $brandText );
			$butId			=	'cbpaidButt' . strtolower( $this->getPayName() ) . 'subscr';
		} else {
			return CBPTXT::T("Unknown payment type");
		}

		$customImage			=	trim( $this->getAccountParam( $prmCustImg ) );
		if ( $customImage == '' ) {
			$customImage		=	trim( $this->getAccountParam( $prmImg ) );
		}

		$titleText				=	CBPTXT::T( $this->getAccountParam( 'currency_acceptance_text' ) );

		$payNameForCssClass		=	$this->getPayName();
		$newCurrency			=	$this->mainCurrencyOfGateway();
		return cbpaidGatewaySelectorButton::getChangeOfCurrencyButton( $paymentBasket, $newCurrency, $customImage, $altText, $titleText, $payNameForCssClass . ' ' . 'cbregconfirmtitleonclick', $butId );
	}
	/**
	 * HELPER FUNCTIONS USEFUL FOR GATEWAYS:
	 */

	/**
	 * Concatenates with $glue the values listed in $listOfParams from keyed array $requestParams after applying $array_mapFunction function to each.
	 * Includes undefined values of $requestParams if $includeUndefined is TRUE.
	 * Includes empty values of $requestParams if $includeEmpty is TRUE.
	 *
	 * There are special cases for parameter name in $listOfParams:
	 * 1) if of the form '$nameofgatewayvar', it will use as value result of $this->getAccountParam( 'nameofgatewayvar' ).
	 * 2) if of the form '#thisvalue', it will use 'thisvalue' as value
	 * 3) if of the form '?keyToCheck?valueIfTrue:valueIfFalse':
	 * e.g. '?RECUR_RESULT?subscription:purchase': if $requestParams['RECUR_RESULT'] is set and equivalent to boolean TRUE, 'subscription' will be used as value, otherwise 'purchase'. will be used.
	 *
	 * @param  array     $requestParams        The keyed parameters to concatenate
	 * @param  array     $listOfParams         List of parameter names to concatenate (case-sensitive, unless $caseInsensitiveKeys is TRUE). If empty list, uses all keys from $requestParams.
	 * @param  callable  $array_mapFunction    Function to apply to the values of $requestParams (e.g. string 'strtoupper')
	 * @param  string    $glue                 Glue applied between concatenated params (but not at begin or end)
	 * @param  string    $glueKV               If non-empty string: outputs Key GlueKV Param instead of Param
	 * @param  boolean   $includeEmpty         Includes also values which are empty string '' (but not NULL)
	 * @param  boolean   $includeUndefined     Includes also values which are unset or NULL as ''
	 * @param  boolean   $caseInsensitiveKeys  Keys in $requestParams must not be same casing as listed in $listOfParams
	 * @param  boolean   $sortedKeys           Should $listOfParams be sorted first ?
	 * @param  boolean   $rawParams            $requestParams are raw $_POST or $_GET input and should be sanitized and unescaped if needed
	 * @return string                          Concatenated string
	 */
	protected function _concatVars( $requestParams, $listOfParams, $array_mapFunction = null, $glue = '', $glueKV = '', $includeEmpty = false, $includeUndefined = false , $caseInsensitiveKeys = false, $sortedKeys = false, $rawParams = false ) {
		if ( ! $listOfParams ) {
			$listOfParams	=	array_keys( $requestParams );
		}
		if ( $sortedKeys ) {
			sort( $listOfParams );
		}
		if ( $caseInsensitiveKeys ) {
			// Case-insensitive keys: Upper-case all keys:
			$signParams		=	array();
			foreach ( $requestParams as $k => $v ) {
				$signParams[strtoupper( $k )]	=	$v;
			}
			$nonUpperCase	=	$listOfParams;
			$listOfParams	=	array_map('strtoupper', $listOfParams );
		} else {
			$signParams		=	$requestParams;
		}
		if ( $array_mapFunction ) {
			// Array-values remapping function:
			$signParams		=	array_map( $array_mapFunction, $signParams );
		}

		// Now concatenate based on $includeUndefined :
		$string				=	null;
		foreach ( $listOfParams as $i => $k ) {
			if ( $k[0] == '$' ) {
				// Special case of the form: $nameofgatewayvar:
				$v			=	$this->getAccountParam( substr( ( isset( $nonUpperCase ) ? $nonUpperCase[$i] : $k ), 1 ) );
			} elseif ( $k[0] == '#' ) {
				$v			=	substr( $k, 1 );
			} elseif ( $k[0] == '?' ) {
				// Special case of the form: ?IFthisKEYisSETandTRUE?useThisValue1:OtherwiseUseThisValue2 :
				$kk			=	substr( $k, 1, strpos( $k, '?', 1 ) -1 );
				$matches	=	null;
				// find Values 1 and 2 in string which is not uppercased, if it was uppercased:
				if ( preg_match( '/^\?.+\?(.+):(.+)$/', ( isset( $nonUpperCase ) ? $nonUpperCase[$i] : $k ), $matches ) ) {
					// But check the param on the uppercased key:
					if ( isset( $signParams[$kk] ) && ( ( (bool) $signParams[$kk] ) === true ) ) {
						$v	=	$matches[1];
					} else {
						$v	=	$matches[2];
					}
				} else {
					trigger_error( sprintf( "%s::%s: Incorrect key %s in listOfParams array", __CLASS__, __FUNCTION__, $k ), E_USER_WARNING );
					$v      =   '';
				}
			} else {
				// Normal case of key:
				if ( isset( $signParams[$k] ) ) {
					// value exists, normal case:
					if ( $rawParams ) {
						$v	=	stripslashes( cbGetParam( $signParams, $k, null, _CB_NOTRIM ) );
					} else {
						$v	=	$signParams[$k];
					}
				} else {
					// value not set or NULL:
					if ( ! $includeUndefined ) {
						continue;
					}
					// Set to empty if we include undefineds:
					$v		=	'';
				}
			}
			// Ok, we have a value $v: check for not empty and not null string, unless we also include empty:
			if ( ( ( $v !== '' ) && ( $v !== null ) ) || $includeEmpty ) {
				// Add $glue at begin except first time when $string is NULL:
				$string		.=	( ( $string === null ) ? '' : $glue )
					// Add $k key and $glueKV if needed before value $v:
					.	( $glueKV ? $k . $glueKV : '' )
					.	$v;
			}
		}
		unset( $signParams );
		return $string;
	}
	/**
	 * Apply hash $algorithm to $string, and upper-case result if $upperCases = true.
	 *
	 * @param  string   $string      string to hash
	 * @param  string   $algorithm   md5, sha1, sha256, sha512
	 * @param  boolean  $upperCases  Returns hash uppercased
	 * @return string
	 */
	protected function _hashString( $string, $algorithm = 'sha1', $upperCases = false ) {
		switch ( $algorithm ) {
			case 'md5':
				$h			=	md5( $string );
				break;
			case 'sha1':
				$h			=	sha1( $string );
				break;

			case 'sha256':
			case 'sha512':
			default:
				$h			=	hash( $algorithm, $string );
				break;
		}
		if ( $upperCases ) {
			$h				=	strtoupper( $h );
		}
		return $h;
	}
	/*
		protected function _fillRequestPost( &$requestVars, $listOfParams, $paymentBasket, $requestParams = null ) {
			$vars				=	array(	'accountparams'		=>	array( $this, 'getAccountParam' ),
											'basket'			=>	$paymentBasket,
											'request'			=>	$requestParams );
			return $this->_fillRequestArray( $requestVars, $listOfParams, $vars );
		}
		protected function _fillRequestArray( &$requestVars, $listOfParams, $vars ) {
			$listOfParams		=	array(	array( 'protocol',		'const',			'4' ),
											array( 'msgtype',		'const',			'authorize' ),
											array( 'merchant',		'accountparams',	'pspid' ),
											array( 'language',		'accountparams',	'language' ),
											array( 'ordernumber',	'basket',			'' ),
											array( 'amount',		'',		'' ),
											array( 'currency',		'basket',		'mc_currency' ),
											array( '',		'',		'' ),
											array( '',		'',		'' ),
											array( '',		'',		'' ),
											array( '',		'',		'' ),
										 );
			foreach ( $listOfParams as $k ) {
				$tk				=	explode( ':', $k, 2 );
				if ( count( $tk ) == 2 ) {
					if ( $tk[0] == 'const' ) {
						$v	=	$tk[1];
					} elseif ( isset( $vars[$tk[0]] ) ) {
						if ( is_object( $tk[0] ) && property_exists( $vars[$tk[0]], $tk[1] ) ) {
							$v		=	$vars[$tk[0]]->{$tk[1]};
						} elseif ( is_array( $tk[0] ) &&  array_key_exists( $tk[1], $vars[$tk[0]] ) ) {
							$v		=	$vars[$tk[0]][$tk[1]];
						} elseif ( is_callable( $tk[0] ) ) {
							$v		=	call_user_func_array( $tk[0], array( $tk[1] ) );
						} else {
							// Developer-friendly error treatment:
							if ( is_object( $tk[0] ) && ! property_exists( $vars[$tk[0]], $tk[1] ) ) {
								$this->_setLogErrorMSG( 3, $this->account, sprintf( '%s : parameter object proprety %s of parameter %s is undefined in vars (%s).', __FUNCTION__, $tk[1], $k, implode(', ', array_keys( $vars ) ) ) );
							} elseif ( is_array( $tk[0] ) && ! array_key_exists( $tk[1], $vars[$tk[0]] ) ) {
								$this->_setLogErrorMSG( 3, $this->account, sprintf( '%s : parameter array key %s of parameter %s is undefined in vars (%s).', __FUNCTION__, $tk[1], $k, implode(', ', array_keys( $vars ) ) ) );
							} else {
								$this->_setLogErrorMSG( 3, $this->account, sprintf( '%s : parameter type %s of parameter %s in vars (%s) is neither an object nor a funciton.', __FUNCTION__, $tk[1], $k, implode(', ', array_keys( $vars ) ) ) );
							}
							continue;
						}
					} else {
						// Developer-friendly error treatment:
						$this->_setLogErrorMSG( 3, $this->account, sprintf( '%s : parameter type %s of parameter %s is undefined in vars (%s).', __FUNCTION__, $tk[0], $k, implode(', ', array_keys( $vars ) ) ) );
						continue;
					}
					$requestVars[ ]		=	$v;
				}
			}
		}
	*/
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
	protected function countryToLetters( $country, $nbLetters ) {

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
				$ret        =   null;
				break;
		}
		if ( $ret === null ) {
			$n				=	( $nbLetters < 0 ? 255 : $nbLetters % 10 );
			$ret			=	substr( $country, 0, $n );
		}

		return $ret;
	}
	/**
	 * Converts xml response into associative array of tag => value of data OR tag => array( tag_name => value, ...)
	 * E.g.:
	 * <a>
	 *     <b>c</b>
	 * </a>
	 * becomes:
	 * array( 'a' => array( 'b' => 'c' ) );
	 *
	 * @param  SimpleXMLElement  $xml
	 * @return array
	 */
	protected function xmlTagValuesToArray( $xml ) {
		$requestdata				=	array();

		if ( $xml ) {
			foreach ( $xml as $k => $v ) {
				$requestdata[$k]	=	$this->xmlTagValuesToArray( $v );
			}
		}

		if ( count( $requestdata ) > 0 ) {
			return $requestdata;
		} else {
			return (string) $xml;
		}
	}

	/**
	 * FUNCTIONS FOR BACKEND INTERFACE:
	 */

	/**
	 * Used by backend only by cbpaidGatewayAccountalertpayoem::renderNotifyUrl() :
	 * Renders URL to set in the gateway interface for notifications
	 *
	 * @param  string  $urlType  Type of URL ( 'successurl', 'cancelurl', 'notifyurl' )
	 * @return string
	 */
	public function adminUrlRender( $urlType ) {
		switch ( $urlType ) {
			case 'successurl':
				return $this->getSuccessUrl( null );
				break;
			case 'cancelurl':
				return $this->getCancelUrl( null );
				break;
			case 'notifyurl':
				return $this->getNotifyUrl( null );
				break;
			default:
		}
		return 'Error: Unknown url type: ' . htmlspecialchars( $urlType );
	}

}	// end class cbpaidHostedPagePayHandler.
