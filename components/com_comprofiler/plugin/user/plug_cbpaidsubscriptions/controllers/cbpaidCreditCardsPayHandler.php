<?php
/**
 * @version $Id: cbpaidCreditCardsPayHandler.php 1608 2012-12-29 04:12:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Paid Subscriptions Tab Class for handling the CB tab api
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @author Beat
 */
abstract class cbpaidCreditCardsPayHandler extends cbpaidPayHandler {
	/**
	 * Overrides base class with 2:
	 * Hash type: 1 = only if there is a basket id (default), 2 = always, 0 = never
	 * @var int
	 */
	protected $_urlHashType	=	2;

	protected $ccYearsInAdvance	= 15;
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

		$prmCustImg						=	'cards_custom_image';
		$customImage					=	trim( $this->getAccountParam( $prmCustImg ) );
		$cardtypes						=	$this->getAccountParam( 'cardtypes', array() );

		switch ( $redirectNow ) {
			case 'redirect':

				$url					=	$this->_getPayFormRedirectUrl( $paymentBasket );
				$ret					=	array( $url );
				break;

			case 'radios':

				$ret					=	array();
				if ( $customImage ) {
					$ret[]				=	$this->getPayRadioRecepie( $paymentBasket, '', null, 'Credit Card' );			// CBPTXT::T("Credit Card")
				} else {
					foreach ( $cardtypes as $paymentType ) {
						$ret[]			=	$this->getPayRadioRecepie( $paymentBasket, $paymentType, $paymentType, ( $paymentType == 'amexco' ) ? 'American Express' : ucwords( $paymentType ) );
					}
				}
				break;

			case 'buttons':

				if ( $customImage ) {
					if ( $this->canPayBasketWithThisCurrency( $paymentBasket ) ) {
						$ret[]			=	$this->getPayButtonRecepie( $paymentBasket, 'single', null );
					} elseif ( $this->getAccountParam( 'currency_acceptance_mode' ) == 'A' ) {
						$ret[]			=	$this->getChangeOfCurrencyButton( $paymentBasket, 'single', null );
					}
				} else {
					foreach ( $cardtypes as $paymentType ) {
						if ( $this->canPayBasketWithThisCurrency( $paymentBasket ) ) {
							$ret[]		=	$this->getPayButtonRecepie( $paymentBasket, 'single', $paymentType );
						} elseif ( $this->getAccountParam( 'currency_acceptance_mode' ) == 'A' ) {
							$ret[]			=	$this->getChangeOfCurrencyButton( $paymentBasket, 'single', $paymentType );
						}
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
	public function resultNotification( $paymentBasket, $postdata, &$allowHumanHtmlOutput ) {
		global $_CB_framework;

		$ret							=	null;
		$now							=	$_CB_framework->now();

		$enable_authorizenet			=	$this->getAccountParam( 'enabled', 0 );
		$authorize_cardtypes			=	$this->getAccountParam( 'cardtypes', array() );
		$result							=	cbGetParam( $_REQUEST, 'result', '' );

		switch ( $result ) {

			case 'notify':

				// We got an automatic notification from gateway:

				$ret					=	$this->handleNotify( $paymentBasket, $postdata );
				$allowHumanHtmlOutput	=	false;
				break;

			case 'payform':

				// Draw the payment mask:

				$cardType				=	cbGetParam( $_REQUEST, 'cardtype' );
				$paymentBasketId		=	(int) $this->_getReqParam( 'basket' );
				$shopUser				=	$this->_getReqParam( 'shopuser' );
				if ( $paymentBasketId ) {
					$exists = $paymentBasket->load( (int) $paymentBasketId );
					if ( $exists ) {
						if ( $paymentBasket->payment_status != 'Completed' ) {
							$user			=	CBuser::getUserDataInstance( (int) $paymentBasket->user_id );
							if ( ( $shopUser ==  $this->shopuserParam( $paymentBasket ) ) && (  is_object( $user ) && ( ( $_CB_framework->myId() ) ? $paymentBasket->authoriseAction( 'pay' ) : true ) ) ) {
								$errorMsg	=	null;
								$ret		.=	$this->displayPayForm( $user, $paymentBasket, $cardType, $errorMsg, $enable_authorizenet, $authorize_cardtypes );
							} else {
								$this->_setLogErrorMSG( 5, $paymentBasket, $this->getPayName() . ' payform notice', CBPTXT::T("Payment basket does not belong to you."));
							}
						} else {
							$this->_setErrorMSG( CBPTXT::T("Payment is already completed.") );
						}
					} else {
						$this->_setLogErrorMSG( 5, $paymentBasket, $this->getPayName() . ' payform notice', CBPTXT::T("Payment basket does not login.") );
					}
				} else {
					$this->_setLogErrorMSG( 5, $paymentBasket, $this->getPayName() . ' payform notice', CBPTXT::T("Payment basket not known.") );
				}
				break;

			case 'paynow':

				// Process the payment mask posting:

				if ( $this->_checkIfHttpS( 'params' ) ) {
					$card					=	array();
					$paymentBasketId		=	(int) $this->_getReqParam( 'basket' );
					$shopUser				=	$this->_getReqParam( 'shopuser' );
					$card['type']			=	$this->_getReqParam( 'cardtype' );
					$card['number']			=	preg_replace ( '/[^0-9]+/', '', $this->_getReqParam( 'number' ) );
					$card['cvv']			=	preg_replace ( '/[^0-9]+/', '', $this->_getReqParam( 'cvv' ) );
					$card['firstname']		=	stripslashes( $this->_getReqParam( 'firstname' ) );
					$card['lastname']		=	stripslashes( $this->_getReqParam( 'lastname' ) );
					$card['expmonth']		=	(int) $this->_getReqParam( 'expmonth' );
					$card['expyear']		=	(int) $this->_getReqParam( 'expyear' );
					$card['paymentType']	=	(int) $this->_getReqParam( 'paymenttype' );
					if ( $this->getAccountParam( 'show_cc_avs', 0 ) ) {
						$card['address']	=	stripslashes( $this->_getReqParam( 'address' ) );
						$card['zip']		=	stripslashes( $this->_getReqParam( 'zip' ) );
						$card['country']	=	stripslashes( $this->_getReqParam( 'country' ) );
					}

					$exists					=	$paymentBasket->load( (int) $paymentBasketId );
					if ( $exists && ( $paymentBasket->payment_status != 'Completed' ) && ( $shopUser == $this->shopuserParam( $paymentBasket ) ) ) {
						$user				=	CBuser::getUserDataInstance( (int) $paymentBasket->user_id );
						if ( is_object( $user ) && ( ( $_CB_framework->myId() ) ? $paymentBasket->authoriseAction( 'pay' ) : true ) ) {
							if ( ! in_array( $card['type'], $authorize_cardtypes ) ) {
								$this->_setErrorMSG(CBPTXT::T("Bad credit card type: please check credit-card type again."));
							} elseif ( ! $this->checkCCNumber( $card ) ) {
								$this->_setErrorMSG(CBPTXT::T("Bad credit card number: please check credit-card number again."));
							} elseif ( ! $this->checkCCExpDate( $card, $now ) ) {
								$this->_setErrorMSG(CBPTXT::T("Bad credit card expiration date: please check credit-card expiration date again."));
							} elseif ( ! $this->checkCCName( $card ) ) {
								$this->_setErrorMSG(CBPTXT::T("Bad credit card name: please check credit-card name again."));
							} elseif ( ! in_array( $card['paymentType'], array( 1, 2 ) ) ) {
								$this->_setErrorMSG(CBPTXT::T("Please click on button to pay."));
							} else {
								// all checks are ok: ready to submit to payment processor ! :

								$pay1subscribe2				=	$this->_getPaySubscribePossibilities( $enable_authorizenet, $paymentBasket );
								$authnetSubscription		=	$card['paymentType'] & $pay1subscribe2;		// logical AND

								if ( $authnetSubscription == 1 ) {

									// single payment:
									$ipn					=	null;
									$authorize_trans_id		=	$this->_attemptSinglePayment( $card, $paymentBasket, $now, $ipn, $authnetSubscription );
									if ( $authorize_trans_id !== false ) {
										$this->updatePaymentStatus( $paymentBasket, 'web_accept', 'Completed', $ipn, 1, 0, 0, true );
									}
									/*
																		$ipn	=	null;
																		$authorize_trans_id		=	$this->processSinglePayment( $card, $paymentBasket, $now, $ipn, $authnetSubscription );
																		if ( is_string( $authorize_trans_id ) ) {
																			$this->_bindNotificationToBasket( $ipn, $paymentBasket );
																			$this->updatePaymentStatus( $paymentBasket, 'web_accept', 'Completed', $ipn, 1, 0, 0, true );
																		} elseif ( is_array( $authorize_trans_id ) && isset( $authorize_trans_id['errorCode'] ) && isset( $authorize_trans_id['errorText'] ) ) {
																			$this->_setLogErrorMSG( 5, $ipn, $this->getPayName() . ' AIM error returned ' . $authorize_trans_id['errorCode'], $authorize_trans_id['errorText'] );
																		} else {
																			$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' AIM unknown error', CBPTXT::T("Submitted payment didn't return an error but didn't complete.") );
																		}
									*/
								} elseif ( $authnetSubscription == 2 ) {
									// recuring payment:
									$ipn	=	null;

									$occurrences				=	0;
									$autorecurring_type			=	0;
									$autorenew_type				=	0;
									$authorize_subscription_id	=	$this->processSubscriptionPayment( $card, $paymentBasket, $now, $ipn, $occurrences, $autorecurring_type, $autorenew_type );
									/*
																		$authorize_trans_id			=	array();	// not int means error by default
																		$authorize_subscription_id	=	array();	// not int means error by default

																		if ( ( $paymentBasket->period1 && ( $paymentBasket->mc_amount1 != 0 ) )
																			|| ( ( ! $paymentBasket->period1 ) && ( $paymentBasket->mc_amount3 != 0 ) ) ) {

																			// Upfront amount non-null: do an AIM first:
																			$authorize_trans_id			= $this->processSinglePayment( $card, $paymentBasket, $now, $ipn, $authnetSubscription );
																			if ( is_string( $authorize_trans_id ) ) {

																				$this->_bindNotificationToBasket( $ipn, $paymentBasket );
																				$occurrences		=	1;
																			} elseif ( is_array( $authorize_trans_id ) && isset( $authorize_trans_id['errorCode'] ) && isset( $authorize_trans_id['errorText'] ) ) {
																				$this->_setLogErrorMSG( 5, $ipn, $this->getPayName() . ' AIM error returned ' . $authorize_trans_id['errorCode'], $authorize_trans_id['errorText'] );
																			} else {
																				$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' AIM unknown error', CBPTXT::T("Submitted payment didn't return an error but didn't complete.") );
																			}
																		} else {
																			//TBD v2: authorize and release first amount to check that credit-card number is valid !
																		}
																		if ( ( $paymentBasket->mc_amount3 != 0 ) && ( ! $this->getErrorMSG() ) ) {

																			// Recurring amount existing and if first amount existed it got payed OK: subscribe to an ARB:
																			$ipnSubscr	=	null;
																			$authorize_subscription_id	=	$this->processSubscriptionPayment( $card, $paymentBasket, $user, $now, $ipnSubscr );

																			if ( is_string( $authorize_subscription_id ) ) {
																				$occurrences					=	$occurrences + $this->_subscriptionTiming['totalOccurrences'];
																				$autorecurring_type				=	1;
																				$authnetSubscription			=	( ( $this->getAccountParam( 'enabled', 0 ) >= 2 ) && $paymentBasket->isAnyAutoRecurring() );
																				$autorenew_type					=	( $authnetSubscription ? 2 : 0 );			//TBD: mandatory by system imposed by implementation here !!!
																				$this->_bindNotificationToBasket( $ipnSubscr, $paymentBasket );
																				if ( $ipn === null ) {
																					$ipn						=	$ipnSubscr;
																				}
																			} elseif ( is_array( $authorize_subscription_id ) && isset( $authorize_subscription_id['errorCode'] ) && isset( $authorize_subscription_id['errorText'] ) ) {
																				$this->_setLogErrorMSG( 5, $ipnSubscr, $this->getPayName() . ' ARB error returned ' . $authorize_subscription_id['errorCode'], CBPTXT::T("Subscription payment registration error: ") . $authorize_subscription_id['errorText'] );
																			} elseif ( is_string( $authorize_subscription_id ) ) {
																				$ret							.=	$authorize_subscription_id;
																			} else {
																				$this->_setLogErrorMSG( 3, $ipnSubscr, $this->getPayName() . ' ARB unknown error returned', CBPTXT::T("Submitted subscription payment didn't return an error but didn't complete.") );
																			}
																		}
									*/
									if ( is_string( $authorize_subscription_id ) ) {
										// first payment at least did complete, or the , update payment status:					//TBD: Check if subscription time is correct in case first payment ok but second not.
										$this->updatePaymentStatus( $paymentBasket, 'subscr_payment', 'Completed', $ipn, $occurrences, $autorecurring_type, $autorenew_type, true );
									}
								} else {
									$this->_setErrorMSG( CBPTXT::T("Submitted payment without pressing pay or subscribe button.") );
								}
							}
						} else {
							$exists	=	false;
							$this->_setLogErrorMSG( 3, null, $this->getPayName(), CBPTXT::T("Unauthorized payment action.") );
						}
					} else {
						$exists		=	false;
						$this->_setErrorMSG( CBPTXT::T("Payment basket does not exist.") );
					}

					$errorMsg = $this->getErrorMSG( '<br />' );
					if ( ( $ret == '' ) && ( $errorMsg ) && $exists && ( $shopUser == $this->shopuserParam( $paymentBasket ) ) && ( $paymentBasket->payment_status != 'Completed' ) ) {
						if ( ! isset( $user ) ) {
							$user	=	CBuser::getUserDataInstance( (int) $paymentBasket->user_id );
						}
						$ret		.=	$this->displayPayForm( $user, $paymentBasket, $card['type'], $errorMsg, $enable_authorizenet, $authorize_cardtypes );
					}
				} else {
					$this->_setLogErrorMSG( 3, null, $this->getPayName(), CBPTXT::T("Unauthorized access without https.") );
				}
				break;

			case 'cancel':

				// The user cancelled his payment:

				if ( $this->hashPdtBackCheck( $this->_getReqParam( 'pdtback', '' ) ) ) {
					$paymentBasketId = cbGetParam($_REQUEST, $this->_getPagingParamName('basket'));
					$paymentBasket->id = $paymentBasketId;
					$paymentBasket->payment_status = 'RedisplayOriginalBasket';
					$this->_setErrorMSG(CBPTXT::T("Payment cancelled."));
					$ret = false;
				}
				break;

			default:
				break;
		}
		return  $ret;
	}	// end function resultNotification.
	/**
	 * Returns text "using your xxxx account no...."
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return string  Text
	 */
	public function getTxtUsingAccount( $paymentBasket ) {
		return ' ' . CBPTXT::T("using a") . ' ' . CBPTXT::T( $paymentBasket->payment_type );
	}
	/**
	 * Cancels a payment
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @param  cbpaidPaymentItem[]  $paymentItems   redirect immediately instead of returning HTML for output
	 * @return boolean                              true if unsubscription done successfully, false if error
	 */
	public function stopPaymentSubscription( $paymentBasket, $paymentItems ) {
		$return							=	false;

		if ( $paymentBasket->mc_amount3 ) {
			// Recurring amount existing and if first amount existed it got payed OK: subscribe to an ARB:
			$ipnSubscr					=	null;
			$authorize_subscription_id	=	$paymentBasket->subscr_id;

			if ( $authorize_subscription_id ) {
				$ipnSubscr				=	null;
				$result					=	$this->processSubscriptionCancellation( $paymentBasket, $paymentItems, $ipnSubscr, $authorize_subscription_id );
				if ( is_string( $result ) ) {
					// $this->_bindNotificationToBasket( $ipnSubscr, $paymentBasket );
					//TBD: need to adapt this: $this->updatePaymentStatus( $paymentBasket, 'subscr_cancel', 'Completed', $ipn, $occurrences, $autorecurring_type, $autorenew_type, true );
					//TBD:	return shouldn't return TRUE, but the list of subscriptions which have been cancelled.
					$return				=	true;
				} elseif ( isset( $result['errorText'] ) ) {
					$this->_setLogErrorMSG( 3, $ipnSubscr, $this->getPayName() . ' ARB: unsubscribe error ' . $result['errorCode'] , $result['errorText'] );
				}
			} else {
				$this->_setLogErrorMSG( 3, $ipnSubscr, $this->getPayName() . ' ARB: stopPaymentSubscription error: missing subscr_id in payment basket', CBPTXT::T("Submitted unsubscription didn't return an error but didn't complete.") );
			}
		}
		return $return;
	}


	/**
	 * METHODS TO IMPLEMENT IN PAYMENT HANDLER:
	 */

	/**
	 * Attempts to authorize and capture a credit card for a single payment of a payment basket.
	 *
	 * @param  array                            $card                : $card['type'], $card['number'], $card['firstname'], $card['lastname'], $card['expmonth'], $card['expyear']
	 * @param  cbpaidPaymentBasket              $paymentBasket
	 * @param  int                              $now                  unix timestamp of now
	 * @param  cbpaidPaymentNotification  $ipn                  returns the stored notification
	 * @param  boolean                          $authnetSubscription  true if it is a subscription and amount is in mc_amount1 and not in mc_gross
	 * @return mixed   STRING subscriptionId    if subscription request succeeded, otherwise ARRAY( 'level' => 'spurious' or 'fatal', 'errorText', 'errorCode' => string )
	 *  of error to display
	 */
	protected function processSinglePayment( /** @noinspection PhpUnusedParameterInspection */ $card, $paymentBasket, $now, &$ipn, $authnetSubscription ) {
		return array( 'level'	 => 'fatal',
			'errorText' => $this->getPayName() . ' single payment not implemented !',
			'errorCode' => '1' );
		// override !
	}
	/**
	 * Attempts to subscribe a credit card for AIM + ARB subscription of a payment basket.
	 * ARB are subscriptions to a cron script running at payment service server each day.
	 *
	 * @param  array                           $card            : $card['type'], $card['number'], $card['firstname'], $card['lastname'], $card['expmonth'], $card['expyear'], and optionally: $card['address'], $card['zip'], $card['country']
	 * @param  cbpaidPaymentBasket             $paymentBasket
	 * @param  int                             $now              unix timestamp of now
	 * @param  cbpaidPaymentNotification|null  $ipn              returns the stored notification
	 * @param  int                             $occurrences      returns the number of occurences pay-subscribed firmly
	 * @param  int                             $autorecurring_type  returns:  0: not auto-recurring, 1: auto-recurring without payment processor notifications, 2: auto-renewing with processor notifications updating $expiry_date
	 * @param  int                             $autorenew_type      returns:  0: not auto-renewing (manual renewals), 1: asked for by user, 2: mandatory by configuration
	 * @return mixed   STRING subscriptionId   if subscription request succeeded, otherwise ARRAY( 'level' => 'inform', 'spurious' or 'fatal', 'errorText', 'errorCode' => string )
	 *  of error to display
	 */
	protected function processSubscriptionPayment( /** @noinspection PhpUnusedParameterInspection */ $card, $paymentBasket, $now, &$ipn, &$occurrences, &$autorecurring_type, &$autorenew_type ) {
		$errMsg					=	"Subscription payment not implemented !";
		$this->_setLogErrorMSG( 5, $ipn, $this->getPayName() . ' ' . $errMsg, CBPTXT::T($errMsg) );
		return array( 'level'	 => 'fatal',
			'errorText' => $this->getPayName() . ' ' . $errMsg,
			'errorCode' => '1' );
		// override !
	}
	/**
	 * Attempts to unsubscribe an ARB subscription of a payment basket.
	 * ARB are subscriptions to a cron script running at authorize.net each day at 2:30 AM PST, while authorize.net ARB server time is MST with US DST.
	 *
	 * @param  cbpaidPaymentBasket              $paymentBasket
	 * @param  cbpaidPaymentItem[]              $paymentItems
	 * @param  cbpaidPaymentNotification  $ipn                        returns the stored notification
	 * @param  string                           $authorize_subscription_id
	 * @return string|array     STRING subscriptionId if subscription request succeeded, otherwise ARRAY( 'level' => 'inform', 'spurious' or 'fatal', 'errorText', 'errorCode' => string ) of error to display
	 */
	protected function processSubscriptionCancellation( /** @noinspection PhpUnusedParameterInspection */ $paymentBasket, $paymentItems, &$ipn, $authorize_subscription_id ) {
		$errMsg					=	"Subscription cancellation not implemented !";
		$this->_setLogErrorMSG( 5, $ipn, $this->getPayName() . ' ' . $errMsg, CBPTXT::T($errMsg) );
		return array( 'level'	 => 'fatal',
			'errorText' => $this->getPayName() . ' ' . $errMsg,
			'errorCode' => '1' );
		// override !
	}
	/**
	 * Handles a gateway notification
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  Payment Basket
	 * @param  array                $postdata       POST data
	 * @return boolean                              Result
	 */
	protected function handleNotify( /** @noinspection PhpUnusedParameterInspection */ $paymentBasket, $postdata ) {
		// Override !

		global $_CB_database;
		// Log the INS:
		$ipn								=&	$this->_prepareIpn( 'D', 'Unknown', null, null, null, 'utf-8' );	// IPN payment gateway communication error
		$ipn->raw_data						=	/* cbGetParam() not needed: we want raw info */ '$_GET=' . var_export( $_GET, true ) . ";\n"
			.	/* cbGetParam() not needed: we want raw info */ '$_POST=' . var_export( $_POST, true ) . ";\n"
		;
		$ipn->raw_result					=	'UNKNOWN NOTIFICATION';

		if( ! $ipn->store() ) {
			trigger_error( 'Notification log store error:' . htmlspecialchars( $_CB_database->getErrorMsg() ), E_USER_NOTICE );
		}
		return false;
	}

	/**
	 * PRIVATE METHODS OF THIS CLASS
	 */

	/**
	 * Returns the URL of the credit-card payment form
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  Payment Basket
	 * @return string
	 */
	private function _getPayFormUrl( $paymentBasket  ) {
		$additionalVars	=	array( 'shopuser' => $this->shopuserParam( $paymentBasket ) );
		return $this->cbsubsGatewayUrl( 'payform', null, $paymentBasket, $additionalVars, false );
	}
	/**
	 * Gives the Payment form URL to redirect to
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  Basket
	 * @param  array|null           $addUrlVars     Other variables to add
	 * @return string
	 */
	private function _getPayFormRedirectUrl( $paymentBasket, $addUrlVars = null ) {
		if ( $addUrlVars === null ) {
			$addUrlVars	=	array();
		}
		$basegetarray	=	$this->_baseUrlArray( null );
		$arr			=	array_merge( $basegetarray, array( 'basket' => $paymentBasket->id, 'shopuser' => $this->shopuserParam( $paymentBasket ) ), $addUrlVars );
		$arrNorm		=	array( 'result' => 'payform' );
		return $this->_getHttpsRedirectUrl( $paymentBasket, $arr, $arrNorm, $this->params->get( 'cc_http_test_mode', 'https' ) );
	}
	/**
	 * Gives the Pay Now button POST URL
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  Basket
	 * @return string
	 */
	private function _getPayNowUrl( $paymentBasket  ) {
		$url			=	$this->cbsubsGatewayUrl( 'paynow', null, $paymentBasket, null, false );
		return $this->live_siteUrlToHttps( $url );
	}
	//TBD: getCancelUrl not yet used ! need to add link see offline payment...

	/**
	 * Gets real expiration unix time for credit card expiration
	 *
	 * @param  array  $card  array( 'expmonth' => mm, 'expyear' => yyyy )
	 * @return int           unix time
	 */
	protected function getCardExpTime( $card ) {
		return strtotime( '+1 month', mktime( 0, 0, 0, $card['expmonth'], 1, $card['expyear'] ) );
	}

	/**
	 * Displays a payment form for a credit card
	 * @param  UserTable            $user
	 * @param  cbpaidPaymentBasket  $paymentBasket     Payment Basket
	 * @param  string               $cardType
	 * @param  string               $errorMsg
	 * @param  int                  $enable_processor
	 * @param  array                $cardtypes
	 * @return string                                  HTML form
	 */
	private function displayPayForm( &$user, &$paymentBasket, $cardType, $errorMsg, $enable_processor, $cardtypes ) {
		$ret						=	'';
		if ( ! in_array( $cardType, $cardtypes ) ) {
			$cardType = null;
		}

		$pay1subscribe2				=	$this->_getPaySubscribePossibilities( $enable_processor, $paymentBasket );
		if ( $pay1subscribe2 & 0x1 ) {
			$payButtonText			=	CBPTXT::Th("Pay") . ' ' . $paymentBasket->renderPrice();
		} else {
			$payButtonText			=	null;
		}
		if ( $pay1subscribe2 & 0x2 ) {
			/*		$amount					=	$paymentBasket->mc_amount3;
					$period					=	$paymentBasket->get_period();
					$occurrences			=	$paymentBasket->recur_times;
					$subscribeButtonText	=	sprintf( "Pay %s now, then %s", $paymentBasket->renderPrice(), $paymentBasket->renderPrice( $amount, $period, $occurrences ) );
			*/
			if ( $paymentBasket->mc_amount1 > 0 ) {
				$mainText			=	CBPTXT::Th("Pay %s");				// will output "Pay USD x.00 for ....."
			} else {
				$mainText			=	CBPTXT::Th("%s payment");			// will output "Free for the first ...., then USD x.00 for each day payment
			}
			$subscribeButtonText	=	sprintf( $mainText, $paymentBasket->renderRatesValidtiy( true, false ) );
		} else {
			$subscribeButtonText	=	null;
		}
		if ( $this->_checkIfHttpS( 'params' ) ) {
			$baseURL = $this->_getPayNowUrl( $paymentBasket );
			outputCbTemplate();
			$this->_outputRegTemplate();
			$this->_displayWarningsIfNeeded();
			if ( $errorMsg ) {
				$ret		.=	"<br /><div class=\"error\">" . $errorMsg . "</div><br /><br />";
				$chosenCard	=	$cardType;
				$cardType	=	null;	// redraw payment form with choice of credit-card (if they mischosen theirs):
			} else {
				$chosenCard	=	null;
			}
			$ret	.=	$paymentBasket->displayBasket();
			$ret	.=	$this->_drawCCform( $user, $paymentBasket, $cardType, $baseURL, $payButtonText, $subscribeButtonText, $chosenCard );
		} else {
			$additionalUrlVars	=	array( 'cardtype' => $cardType );
			$baseURL		=	$this->_getPayFormRedirectUrl( $paymentBasket, $additionalUrlVars );
			cbRedirect( $baseURL );
		}
		return $ret;
	}
	/**
	 * Displays (ECHOs) warnings for HTTPS missing and for TEST gateways
	 *
	 * @return void
	 */
	private function _displayWarningsIfNeeded( ) {
		global $_CB_framework;

		if ( $this->getAccountParam( 'normal_gateway', 1 ) == 0 ) {
			echo '<div style="margin: 10px 3px; padding: 5px 50px; border: 1px solid #cc0000; min-height: 22px; text-align: left;'
				. ' width: 68%; color: red; font-weight: bold; font-size: 16px; line-height: 20px;'
				. " background: #ffffcc url('" . $_CB_framework->getCfg( 'live_site' ) . "/includes/js/ThemeOffice/warning.png') no-repeat;"
				. ' background-position: 20px 7px; clear:both;">'
				. $this->getPayName()
				. ' TEST SERVER. You can use credit-card number 4242424242424242 and any valid date to test.'
				. '</div>';
		}
		if ( ! $this->_checkIfHttpS() ) {
			if ( $this->getAccountParam( 'normal_gateway', 1 ) == 0 ) {
				echo '<div style="margin: 10px 3px; padding: 5px 50px; border: 1px solid #cc0000; min-height: 22px; text-align: left;'
					. ' width: 68%; color: red; font-weight: bold; font-size: 16px; line-height: 20px;'
					. " background: #ffffcc url('" . $_CB_framework->getCfg( 'live_site' ) . "/includes/js/ThemeOffice/warning.png') no-repeat;"
					. ' background-position: 20px 7px; clear:both;">'
					. 'WARNING: INSECURE FORM. PLEASE DO NOT USE WITH REAL CREDIT-CARD NUMBERS.'
					. ' Please switch settings back to https in CB-&gt;Plugins-&gt;Settings-&gt;Display-&gt;Credit-Card Form http(s) mode'
					. '</div>';
			} else {
				$this->_setErrorMSG( CBPTXT::T("HTTP operations with Credit-cards production server are not allowed for security reasons.") );
			}
		}
	}
	/**
	 * Checks that the credit-card number checkum is valid
	 *
	 * @param  array  $card  The credit-card ($card['number'] is used here)
	 * @return boolean       TRUE: Valid
	 */
	private function checkCCNumber( $card ) {
		$cardNumber 	=	$card['number'];
		$cardLen		=	strlen( $cardNumber );
		if ( ( $cardLen < 13 ) || ( $cardLen > 16 ) ) {
			return false;
		}
		// public source: http://en.wikipedia.org/wiki/Luhn_algorithm
		$sum			=	0;
		$alt			=	false;
		for ( $i = strlen( $cardNumber ) - 1 ; $i >= 0 ; $i-- ) {
			$n			=	(int) $cardNumber[$i];
			if ( $alt ) {
				$n		=	$n * 2;
				if ( $n > 9) {
					$n	=	$n - 9;		// equivalent to adding the value of digits
				}
			}
			$sum		+=	$n;
			$alt		=	! $alt;
		}
		return ( ( $sum % 10 ) == 0 );
		/*
				$checksum = 0;                                  // running checksum total
				$j = 1;                                         // takes value of 1 or 2

				// Process each digit one by one starting at the right
				for ($i = strlen($cardNo) - 1; $i >= 0; $i--) {

					// Extract the next digit and multiply by 1 or 2 on alternative digits.
					$calc = $cardNo{$i} * $j;

					// If the result is in two digits add 1 to the checksum total
					if ($calc > 9) {
						$checksum = $checksum + 1;
						$calc = $calc - 10;
					}

					// Add the units element to the checksum total
					$checksum = $checksum + $calc;

					// Switch the value of j
					if ($j ==1) {$j = 2;} else {$j = 1;};
				}

				// All done - if checksum is divisible by 10, it is a valid modulus 10.
				// If not, report an error.
				if ($checksum % 10 != 0) {
					$errornumber = 3;
					$errortext = $ccErrors [$errornumber];
					return false;
				}
		*/
	}
	/**
	 * Checks the Credit-card expiry date to be in the future
	 *
	 * @param  array    $card  The credit-card ($card['expyear'] and $card['expmonth'] are used here)
	 * @param  int      $now   Unix time of now
	 * @return boolean         TRUE: ok
	 */
	private function checkCCExpDate( $card, $now ) {
		if ( ( ! is_int( $card['expyear'] ) ) || ( ! is_int( $card['expmonth'] ) ) ) {
			return false;
		}
		$aMonthAgo			= strtotime( '-1 month', $now );
		$yearAlmostNow		= date('Y', $aMonthAgo );
		$monthAlmostNow		= date('n', $aMonthAgo );
		$stillValid			= ( ( $card['expyear'] > $yearAlmostNow ) || ( ( $card['expyear'] == $yearAlmostNow ) && ( $card['expmonth'] >= $monthAlmostNow ) ) );
		$notToFarForward	= ( ( $card['expyear'] - $yearAlmostNow ) < $this->ccYearsInAdvance );
		return ( $stillValid && $notToFarForward );
	}
	/**
	 * Checks that the first and last names are filled-in and no longer than 50 characters each
	 *
	 * @param  array    $card  The credit-card ($card['firstname'] and $card['lastname'] are used here)
	 * @return boolean         TRUE: ok
	 */
	private function checkCCName( $card ) {
		$firstnameLen	= strlen( $card['firstname'] );
		$lastnameLen	= strlen( $card['lastname'] );
		return ( ( $firstnameLen > 0 ) && ( $firstnameLen <= 50 ) && ( $lastnameLen > 0 ) && ( $lastnameLen <=50 ) );
	}

	/**
	 * Draws the Credit-card selector (radios)
	 *
	 * @param  UserTable            $user           User
	 * @param  cbpaidPaymentBasket  $paymentBasket  Basket
	 * @param  string|null          $chosenCard     Chosen card if any
	 * @return string                               HTML
	 */
	private function _drawCCSelector( /** @noinspection PhpUnusedParameterInspection */ &$user, &$paymentBasket, $chosenCard = null ) {
		$cardtypes = $this->getAccountParam( 'cardtypes', array() );
		$ret = $this->_renderCCSelector( $cardtypes, $chosenCard );
		return $ret;
	}

	/**
	 * Draws the Credit-card selector (radios)
	 *
	 * @param  string[]     $cardTypes    Card-types available
	 * @param  string|null  $chosenCard   Chosen card if any
	 * @return string                     HTML
	 */
	private function _renderCCSelector( $cardTypes, $chosenCard = null ) {
		$card_choice_type = $this->getAccountParam( 'card_choice_type', 'image' );

		$choices = array();
		if ( $card_choice_type == 'text' ) {
			foreach ( $cardTypes as $cType ) {
				$choices[] = moscomprofilerHTML::makeOption( $cType, ucwords( $cType ) );
			}
		} else {
			foreach ( $cardTypes as $cType ) {
				$cardImg = $this->_renderCCimg( $cType );
				$choices[] = moscomprofilerHTML::makeOption( $cType, $cardImg );
			}
		}
		$ret = moscomprofilerHTML::radioList( $choices, $this->_getPagingParamName( 'cardtype' ), '', 'value', 'text', $chosenCard );
		return $ret;
	}

	/**
	 * Popoulates basic request parameters for gateway depending on basket (without specifying payment type)
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket   paymentBasket object
	 * @param  string               $cardType        CC-brand, NULL if no choice
	 * @return array                                 Returns array $requestParams
	 */
	private function fillinCCFormRequstParams( $paymentBasket, $cardType = null ) {
		$arr		=	array(	'basket'	=>	$paymentBasket->id,
			'shopuser'	=>	$this->shopuserParam( $paymentBasket ) );
		if ( $cardType ) {
			$arr['cardtype']	=	$cardType;
		}
		return $arr;
	}
	/**
	 * Returns hash to personalize URL
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket   paymentBasket object
	 * @return string                                Returns hash to personalize URL
	 */
	private function shopuserParam( $paymentBasket ) {
		return md5( $paymentBasket->user_id . $paymentBasket->shared_secret );
	}
	/**
	 * Renders the credit-card image
	 *
	 * @param  string  $cardType  CC-brand
	 * @param  string  $size      'small' or 'big'
	 * @param  boolean $srcOnly   returns URL of image instead of the <img /> HTML tag
	 * @return string
	 */
	private function _renderCCimg( $cardType, $size = 'small', $srcOnly = false ) {
		global $_CB_framework;

		$card_choice_type		=	$this->getAccountParam( 'card_choice_type', 'image' );
		$ucwCardType			=	ucwords( $cardType );

		if ( $card_choice_type == 'text' ) {
			$img				=	$ucwCardType;
		} else {
			$img				=	$_CB_framework->getCfg( 'live_site' ) . "/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/icons/cards/cc_"
				.	( ( $size == 'big') ? 'big_' : '' )
				.	$cardType . ".gif";
			if ( ! $srcOnly ) {
				$img			=	'<img src="' . $img . '" alt="' . $cardType . '" title="' . $cardType . '" />';
			}
		}
		return $img;
	}
	/**
	 * Returns an array for the 'radios' array of $redirectNow type:
	 * return array( account_id, submethod, paymentMethod:'single'|'subscribe', array(cardtypes), 'label for radio', 'description for radio' )
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket  paymentBasket object
	 * @param  string               $subMethod
	 * @param  string               $paymentType
	 * @param  string               $defaultLabel
	 * @return cbpaidGatewaySelectorRadio
	 */
	protected function getPayRadioRecepie( $paymentBasket, $subMethod, $paymentType, /** @noinspection PhpUnusedParameterInspection */ $defaultLabel ) {
		if ( $paymentType ) {
			$cardtypes				=	array( $paymentType );
			$brand					=	( ( $paymentType == 'amexco' ) ? 'American Express' : ucwords( $paymentType ) );
			$brandLabelHtml			=	CBPTXT::Th( $brand );
			$altText				=	sprintf( CBPTXT::T("Pay safely with %s"), CBPTXT::T( $brand ) );
		} else {
			$cardtypes				=	$this->getAccountParam( 'cardtypes', array() );
			$brandLabelHtml			=	CBPTXT::Th("Pay with your credit card");
			$altText				=	strip_tags( $brandLabelHtml );
		}
		$brandDescriptionHtml		=	CBPTXT::Th( $this->getAccountParam( 'psp_radio_description' ) );

		$payNameForCssClass			=	$this->getPayName();
		if ( ! $this->canPayBasketWithThisCurrency( $paymentBasket ) ) {
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
		$requestParams			=	$this->fillinCCFormRequstParams( $paymentBasket, $paymentType );
		$prmCustImg				=	'cards_custom_image';
		if ( $paymentType ) {
			$altText			=	sprintf( CBPTXT::T("Pay with %s"), CBPTXT::T( ( $paymentType == 'amexco' ) ? 'American Express' : ucwords( $paymentType ) ) );
		} else {
			$altText			=	CBPTXT::T("Pay with your credit card");
		}
		$titleText				=	$altText;
		$butId					=	'cbpaidButt' . strtolower( $paymentType ? $paymentType : $this->getPayName() );

		$customImage			=	trim( $this->getAccountParam( $prmCustImg ) );
		if ( $customImage == '' ) {
			$customImage		=	$this->_renderCCimg( $paymentType, 'big', true );
		}
		$payNameForCssClass		=	$paymentType ? $paymentType : $this->getPayName();

		$pspUrl					=	$this->_getPayFormUrl( $paymentBasket );
		return cbpaidGatewaySelectorButton::getPaymentButton( $this->getAccountParam( 'id' ), $subMethod, $paymentType, $pspUrl, $requestParams, $customImage, $altText, $titleText, $payNameForCssClass, $butId );
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
		$newCurrency			=	$this->mainCurrencyOfGateway();

		$prmCustImg				=	'cards_custom_image';
		if ( $paymentType ) {
			$altText			=	sprintf( CBPTXT::T("Pay with %s"), CBPTXT::T( ( $paymentType == 'amexco' ) ? 'American Express' : ucwords( $paymentType ) ) );
		} else {
			$altText			=	CBPTXT::T("Pay with your credit card");
		}
		$butId					=	'cbpaidButt' . strtolower( $paymentType ? $paymentType : $this->getPayName() );

		$customImage			=	trim( $this->getAccountParam( $prmCustImg ) );
		if ( $customImage == '' ) {
			$customImage		=	$this->_renderCCimg( $paymentType, 'big', true );
		}

		$titleText					=	CBPTXT::T( $this->getAccountParam( 'currency_acceptance_text' ) );

		$payNameForCssClass		=	$paymentType ? $paymentType : $this->getPayName();

		return cbpaidGatewaySelectorButton::getChangeOfCurrencyButton( $paymentBasket, $newCurrency, $customImage, $altText, $titleText, $payNameForCssClass . ' ' . 'cbregconfirmtitleonclick', $butId );
	}
	/**
	 * Draws the credit-card form
	 *
	 * @param  UserTable            $user                 User
	 * @param  cbpaidPaymentBasket  $paymentBasket        paymentBasket object
	 * @param  string               $cardType             CC-brand if no choice
	 * @param  string               $postUrl              URL for the <form>
	 * @param  string               $payButtonText        Text for payment text (if basket allows single-payments)
	 * @param  string               $subscribeButtonText  Text for subscribe button (if basket allows auto-recurring subscriptions)
	 * @param  string|null          $chosenCard
	 * @return string
	 */
	private function _drawCCform( &$user, &$paymentBasket, $cardType, $postUrl, $payButtonText, $subscribeButtonText, $chosenCard = null ) {
		global $_CB_framework, $ueConfig;

		$params					=&	cbpaidApp::settingsParams();

		$sealCode				=	$params->get( 'security_logos_and_seals' );		// keep $param, it's a global setting !
		$drawCCV				=	$params->get( 'show_cc_ccv', 1 );				// keep $param, it's a global setting !
		$drawAVS				=	$this->getAccountParam( 'show_cc_avs', 0 );		// keep $param, it's a global setting !

		if ( in_array( $ueConfig['name_style'], array( 2, 3 ) ) ) {
			$oFirstName	= htmlspecialchars( $user->firstname );
			$oLastName	= htmlspecialchars( $user->lastname );
		} else {
			$posLname	= strrpos( $user->name, ' ' );
			if ( $posLname !== false ) {
				$oFirstName	= htmlspecialchars( substr( $user->name, 0, $posLname ) );
				$oLastName	= htmlspecialchars( substr( $user->name, $posLname + 1 ) );
			} else {
				$oFirstName = '';
				$oLastName	= htmlspecialchars( $user->name );
			}
		}

		$txtHiddenInputs =
			'<input type="hidden" name="' . $this->_getPagingParamName( 'basket' )   . '" value="'	. $paymentBasket->id . "\" />\n"
				.'<input type="hidden" name="' . $this->_getPagingParamName( 'shopuser' ) . '" value="'	.  $this->shopuserParam( $paymentBasket ) . "\" />\n"
				.'<input type="hidden" name="' . $this->_getPagingParamName( 'paymenttype' ) . "\" value=\"0\" />\n";

		$txtVisibleInputs = array(
			'number'	 => '<input class="inputbox" size="20" maxlength="20" type="text" autocomplete="off" name="' . $this->_getPagingParamName( 'number' )    . '" value="" />',
			'firstname'	 => '<input class="inputbox" size="20" maxlength="50" type="text" autocomplete="off" name="' . $this->_getPagingParamName( 'firstname' ) . '" value="' . $oFirstName . '" />',
			'lastname'	 => '<input class="inputbox" size="20" maxlength="50" type="text" autocomplete="off" name="' . $this->_getPagingParamName( 'lastname' )  . '" value="' . $oLastName  . '" />'
		);
		if ( $drawCCV ) {
			$txtVisibleInputs['cvv'] = '<input class="inputbox" size="6" maxlength="4" type="text" autocomplete="off" name="' . $this->_getPagingParamName( 'cvv' ) . '" value="" />';
		}

		if ( ! $cardType ) {
			$cardSelector = $this->_drawCCSelector( $user, $paymentBasket, $chosenCard );
			$txtVisibleInputs['cardtype'] = $cardSelector;
		} else {
			$txtVisibleInputs['cardtype'] = $this->_renderCCimg( $cardType, 'big' );
			$txtHiddenInputs .= '<input type="hidden" name="' . $this->_getPagingParamName( 'cardtype' ) . '" value="'	. $cardType . "\" />\n";
		}
		$months = array();
		$months[] = moscomprofilerHTML::makeOption( '', 'MM' );
		for ( $i=1; $i <= 12; $i++ ) {
			$months[] = moscomprofilerHTML::makeOption( $i, sprintf( '%00d', $i ) );
		}
		$txtVisibleInputs['expmonth'] = moscomprofilerHTML::selectList( $months, $this->_getPagingParamName( 'expmonth' ), 'class="inputbox" size="1"', 'value', 'text', '' );

		$years = array();
		$years[] = moscomprofilerHTML::makeOption( '', 'YYYY' );
		$yearNow	= date('Y');
		$monthNow	= date('m');
		for ( $i = ( ( $monthNow == 1 ) ? -1 : 0 ) ; $i < $this->ccYearsInAdvance; $i++ ) {
			$years[] = moscomprofilerHTML::makeOption( $yearNow + $i, sprintf( '%0000d', $yearNow + $i ) );
		}
		$txtVisibleInputs['expyear'] = moscomprofilerHTML::selectList( $years, $this->_getPagingParamName( 'expyear' ), 'class="inputbox" size="1"', 'value', 'text', '' );

		if ( $drawAVS ) {
			if ( $drawAVS >= 2 ) {
				/** @var $user cbpaidUserWithSubsFields */
				$txtVisibleInputs['address'] =	'<input class="inputbox" size="40" maxlength="60" type="text" autocomplete="off" name="' . $this->_getPagingParamName( 'address' )    . '" value="' . htmlspecialchars( $user->cb_subs_inv_address_street ) . '" />';
			}
			$txtVisibleInputs['zip']		=	'<input class="inputbox" size="10" maxlength="20" type="text" autocomplete="off" name="' . $this->_getPagingParamName( 'zip' )    . '" value="' . htmlspecialchars( $user->cb_subs_inv_address_zip ) . '" />';
			$allCountriesSelect				=	array();
			$countries						=	new cbpaidCountries();
			foreach ( $countries->twoLetterIsoCountries() as $countryName) {
				$allCountriesSelect[]		=	moscomprofilerHTML::makeOption( $countryName, CBPTXT::T( $countryName ) );
			}
			$txtVisibleInputs['country']	=	moscomprofilerHTML::selectList( $allCountriesSelect, $this->_getPagingParamName( 'country' ), 'class="inputbox" size="1"', 'value', 'text', $user->cb_subs_inv_address_country );
		}
		$txtButton		= '<div class="cbregCCbutton" style="min-height:38px;vertical-align:middle;">';
		if ( $payButtonText ) {
			$txtButton .= '<button type="submit" name="cbPayNow" id="cbPayNow" value="' . htmlspecialchars( $payButtonText ) . '" title="' .  htmlspecialchars( CBPTXT::T("Pay now") ) . '">'
				.	$payButtonText
				.	'</button>';
			$js			=	'$("#cbPayNow").click( function() {'
				.		'if(cbCCformSubmitbutton(this.form)) {'
				.			'$("#cbsubsCCform input[name=\'' . $this->_getPagingParamName( 'paymenttype' ) . '\']").val("1");'
				.			'$(this).parent().fadeOut("slow", function() { $("#cbpayWheel").fadeIn("slow"); } );'
				.			'$(this.form).submit();'
				.		'}'
				.	' } );'
			;
			$_CB_framework->outputCbJQuery( $js );
		}
		if ( $payButtonText && $subscribeButtonText ) {
			$txtButton .= '<br /> ' .CBPTXT::T("or") . ' <br /> ';
		}
		if ( $subscribeButtonText ) {
			$txtButton .= '<button type="submit" name="cbSubscribeNow" id="cbSubscribeNow" value="' . htmlspecialchars( $subscribeButtonText ) . '" title="' . htmlspecialchars( CBPTXT::T("Subscribe to payments now") ) . '">'
				.	$subscribeButtonText
				.	'</button>';
			$js			=	'$("#cbSubscribeNow").click( function() {'
				.		'if(cbCCformSubmitbutton(this.form)) {'
				.			'$("#cbsubsCCform input[name=\'' . $this->_getPagingParamName( 'paymenttype' ) . '\']").val("2");'
				.			'$(this).parent().fadeOut("slow", function() { $("#cbpayWheel").fadeIn("slow"); } );'
				.			'$(this.form).submit();'
				.		'}'
				.	' } );'
			;
			$_CB_framework->outputCbJQuery( $js );
		}
		$txtButton		.= '</div>';
		$txtButton		.= '<div id="cbpayWheel" style="display:none;margin:4px 25px;"><img src="' . $this->baseClass->getPluginLIvePath() . '/icons/hot/wheel_pay.gif" alt="spinning wheel" /></div>'
			.  "\n";
		$ret	=	'';
		$this->_renderCCvalidation( '#cbsubsCCform' );

		$ret .= '<form action="' . $postUrl . '" method="post" autocomplete="off" id="cbsubsCCform" name="cbsubsCCform" class="cb_form">' . "\n";
		ob_start();
		$this->_renderCCform( $cardType, $txtVisibleInputs, $txtButton );
		$ret .= ob_get_contents();
		ob_end_clean();
		$ret .= $txtHiddenInputs;
		$ret .= "</form>\n";
		ob_start();
		$this->_renderCCsealCode( $sealCode );
		$ret .= ob_get_contents();
		ob_end_clean();
		return $ret;
	}
	/**
	 * Renders and ECHOs the credit-card form validation javascript
	 *
	 * @param  string  $formCssSelector  The selector for the form
	 * @return void
	 */
	protected function _renderCCvalidation( $formCssSelector ) {
		global $_CB_framework;

		ob_start();
		// echo "{\n";
		?>
    var cbpaidDefaultFieldBackground;
    function cbpaidIsCCNum(ccn) {
    if ( ccn.length == 0 ) {
    return false;
    }
    var sum = 0;
    var mul = 1;
    var l = ccn.length;
    for (i = 0; i < l; i++) {
    var digit = ccn.substring(l-i-1,l-i);
    var num = parseInt(digit ,10);
    if ( ! isNaN(num) ) {
    var tproduct = num * mul;
    if ( tproduct >= 10 ) {
    sum += ( tproduct % 10 ) + 1;
    } else {
    sum += tproduct;
    }
    if ( mul == 1 ) {
    mul++;
    } else {
    mul--;
    }
    }
    }
    return ( ( sum % 10 ) == 0 );
    }
    function cbpaidInnerText(el) {
    if (el.parentNode.previousSibling.innerHTML) {
    return el.parentNode.previousSibling.innerHTML;
    } else if ( el.parentNode.previousSibling.previousSibling.innerHTML ) {
    return el.parentNode.previousSibling.previousSibling.innerHTML;
    } else {
    return $('label[for="'+ $(el).attr('id') +'"]').html();
    }
    }
    function cbpaidGetBgndRed(el) {
    var bg = ((el.style.getPropertyValue) ? el.style.getPropertyValue('background') : el.style.background);
    if ( bg == null ) {
    return false;
    }
    return ( ( bg.slice(0,3)=='red' ) || ( bg=='#ff0000' ) || ( bg.slice(bg.length-3)=='red' ) );
    }
    function cbCCformSubmitbutton(mrfm) {
    var me = mrfm.elements;
    var errorMSG = '';
    var iserror=0;
    for (var i=0; i < me.length; i++) {
    if (me[i].type == 'radio' || me[i].type == 'checkbox') {
    var rOptions = me[me[i].getAttribute('name')];
    var rChecked = 0;
    if(rOptions.length > 1) {
    for (var r=0; r < rOptions.length; r++) {
    if (rOptions[r].checked) {
    rChecked=1;
    }
    }
    } else {
    if (me[i].checked) {
    rChecked=1;
    }
    }
    if(rChecked==0) {
    // add up all error messages
    if ((rOptions.length == 1) || (me[i]==rOptions[0])) {
    errorMSG += cbpaidInnerText(me[i]) + ' <?php echo addslashes( html_entity_decode(_UE_REQUIRED_ERROR, ENT_COMPAT, $_CB_framework->outputCharset() ) ); ?>\n';
    }
    if (cbpaidDefaultFieldBackground === undefined) cbpaidDefaultFieldBackground = ((me[i].style.getPropertyValue) ? me[i].style.getPropertyValue('backgroundColor') : me[i].style.backgroundColor);
    me[i].style.background = 'red';
    if (iserror==0) {
    iserror=1;
    errorMSG += cbpaidInnerText(me[i]) + ' <?php echo addslashes( html_entity_decode(_UE_REQUIRED_ERROR, ENT_COMPAT, $_CB_framework->outputCharset() ) ); ?>\n';
    }
    } else if (cbpaidGetBgndRed(me[i])) {
    me[i].style.backgroundColor = cbpaidDefaultFieldBackground;
    }
    }
    if (me[i].value == '') {
    errorMSG += cbpaidInnerText(me[i]) + ' <?php echo addslashes( html_entity_decode(_UE_REQUIRED_ERROR, ENT_COMPAT, $_CB_framework->outputCharset() ) ); ?>\n';
    if (cbpaidDefaultFieldBackground === undefined) cbpaidDefaultFieldBackground = ((me[i].style.getPropertyValue) ? me[i].style.getPropertyValue('backgroundColor') : me[i].style.backgroundColor);
    me[i].style.background = 'red';
    iserror=1;
    } else if ((me[i].getAttribute('name') == '<?php echo $this->_getPagingParamName( 'cvv' ); ?>') && ( me[i].value.length != 0 ) && ( me[i].value.length < 3 ) ) {
    errorMSG += cbpaidInnerText(me[i]) + ' <> 3 <?php echo addslashes( html_entity_decode(_UE_CHARACTERS, ENT_COMPAT, $_CB_framework->outputCharset() ) ); ?>\n';
    // notify user by changing background color, in this case to red
    if (cbpaidDefaultFieldBackground === undefined) cbpaidDefaultFieldBackground = ((me[i].style.getPropertyValue) ? me[i].style.getPropertyValue('backgroundColor') : me[i].style.backgroundColor);
    me[i].style.background = 'red';
    iserror=1;
    } else {
    if (cbpaidGetBgndRed(me[i])) {
    me[i].style.backgroundColor = cbpaidDefaultFieldBackground;
    }
    }
    }
    if ( ! cbpaidIsCCNum( me['<?php echo $this->_getPagingParamName( 'number' ); ?>'].value ) ) {
    errorMSG += cbpaidInnerText(me['<?php echo $this->_getPagingParamName( 'number' ); ?>']) + ' ' + '<?php echo addslashes( CBPTXT::T("invalid") ); ?>';
    if (cbpaidDefaultFieldBackground === undefined) cbpaidDefaultFieldBackground = ((me['<?php echo $this->_getPagingParamName( 'number' ); ?>'].style.getPropertyValue) ? me['<?php echo $this->_getPagingParamName( 'number' ); ?>'].style.getPropertyValue('background') : me['<?php echo $this->_getPagingParamName( 'number' ); ?>'].style.background);
    me['<?php echo $this->_getPagingParamName( 'number' ); ?>'].style.background = 'red';
    iserror=1;
    } else if (cbpaidGetBgndRed(me['<?php echo $this->_getPagingParamName( 'number' ); ?>'])) {
    me['<?php echo $this->_getPagingParamName( 'number' ); ?>'].style.backgroundColor = cbpaidDefaultFieldBackground;
    }
    if(iserror==1) {
    alert(errorMSG);
    return false;
    } else {
    return true;
    }
    }
    function cbCCformSubmit() {
    if ( cbCCformSubmitbutton(this) ) {
    if ( $(this).find('[name=<?php echo $this->_getPagingParamName( 'paymenttype' ); ?>]').val() == '0' ) {
    alert('<?php echo addslashes( CBPTXT::T("Please click on button to pay.") ); ?>');
    return false;
    }
    } else {
    return false;
    }
    return true;
    }
	<?php
		echo '$("' . $formCssSelector . '").submit( cbCCformSubmit );';
		// echo "\n}";
		$cbjavascript	=	ob_get_contents();
		ob_end_clean();
		$_CB_framework->outputCbJQuery( $cbjavascript );

	}
	/**
	 * Renders and ECHOs the credit-card form HTML
	 * @param  string    $cardType          CC-brand, NULL if no choice
	 * @param  string[]  $txtVisibleInputs  All input values of the form
	 * @param  string    $txtButton         Text for button
	 */
	protected function _renderCCform( /** @noinspection PhpUnusedParameterInspection */ $cardType, $txtVisibleInputs, $txtButton ) {
		global $_CB_framework;
		?>
    <div class="cbregCCnumexp">
        <div class="cbregCCtype">
            <label class="cbregLabel" for="<?php echo cbpaidApp::getBaseClass()->_getPagingParamName( 'cardtype' ) . '0'; ?>"><?php echo CBPTXT::Th("Card Type") . ':'; ?></label>
            <div class="cbregField"><?php echo $txtVisibleInputs['cardtype']; ?></div>
        </div>
        <div id="cbregCardDetails">
            <div class="cbregCCnum">
                <label for="<?php echo $this->baseClass->_getPagingParamName('number'); ?>" class="cbregLabel"><?php echo CBPTXT::Th("Card Number") . ':'; ?></label>
                <div class="cbregField"><?php echo $txtVisibleInputs['number']; ?></div>
            </div>
            <fieldset class="cbregCCexp">
                <legend class="cbregLabel"><?php echo CBPTXT::Th("Expires") . ':'; ?></legend>
                <label for="<?php echo $this->baseClass->_getPagingParamName('expmonth'); ?>" class="cbregLabel"><?php echo CBPTXT::Th("Card Expiration Month"); ?></label>
                <label for="<?php echo $this->baseClass->_getPagingParamName('expyear'); ?>" class="cbregLabel"><?php echo CBPTXT::Th("Card Expiration Year") . ':'; ?></label>
                <div class="cbregField"><?php echo $txtVisibleInputs['expmonth'] . ' &nbsp;/&nbsp; ' . $txtVisibleInputs['expyear']; ?></div>
            </fieldset>
			<?php	if ( $txtVisibleInputs['cvv'] ) {	?>
            <div class="cbregCCcvv">
                <label for="<?php echo $this->baseClass->_getPagingParamName('cvv'); ?>" class="cbregLabel"><?php echo CBPTXT::Th("Card Validation Code") . ':'; ?></label>
                <div class="cbregField"><?php echo $txtVisibleInputs['cvv']; ?>
					<?php		if ( file_exists( $_CB_framework->getCfg( 'absolute_path') . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/icons/cards/cc_cvv2.gif' ) ) {
						?> <img src="<?php
							echo $_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/icons/cards/cc_cvv2.gif';
							?>" alt="<?php echo CBPTXT::T("Visa CVV2 / Mastercard CVC2 / Discover: last 3 digits on back. American Express CID: 4 digits in front."); ?>"  title="<?php echo CBPTXT::T("Visa CVV2 / Mastercard CVC2 / Discover: last 3 digits on back. American Express CID: 4 digits in front."); ?>" />
						<?php		}	?>
                </div>
            </div>
			<?php	}		?>
            <div class="cbclearboth">
                <div class="cbregCCfirstname">
                    <label for="<?php echo $this->baseClass->_getPagingParamName('firstname'); ?>" class="cbregLabel"><?php echo CBPTXT::Th("First name on card") . ':'; ?></label>
                    <div class="cbregField"><?php echo $txtVisibleInputs['firstname']; ?></div>
                </div>
                <div class="cbregCClastname">
                    <label for="<?php echo $this->baseClass->_getPagingParamName('lastname'); ?>" class="cbregLabel"><?php echo CBPTXT::Th("Last name on card") . ':'; ?></label>
                    <div class="cbregField"><?php echo $txtVisibleInputs['lastname']; ?></div>
                </div>
            </div>
			<?php	if ( isset( $txtVisibleInputs['country'] ) ) {	?>
            <fieldset class="cbregCCinvoicingaddress">
                <legend class="cbregLabel"><?php echo CBPTXT::Th("Card invoicing address") . ':'; ?></legend>
				<?php		if ( isset( $txtVisibleInputs['address'] ) ) {	?>
                <div class="cbregCCaddress cb_form_line">
                    <label for="<?php echo $this->baseClass->_getPagingParamName('address'); ?>" class="cbregLabel"><?php echo CBPTXT::Th("Street address") . ':'; ?></label>
                    <div class="cbregField cb_field"><?php echo $txtVisibleInputs['address']; ?></div>
                </div>
				<?php		}
				if ( isset( $txtVisibleInputs['zip'] ) ) {	?>
                    <div class="cbregCCzip cb_form_line">
                        <label for="<?php echo $this->baseClass->_getPagingParamName('zip'); ?>" class="cbregLabel"><?php echo CBPTXT::Th("ZIP code") . ':'; ?></label>
                        <div class="cbregField"><?php echo $txtVisibleInputs['zip']; ?></div>
                    </div>
					<?php		}	?>
                <div class="cbregCCcountry cb_form_line">
                    <label for="<?php echo $this->baseClass->_getPagingParamName('country'); ?>" class="cbregLabel"><?php echo CBPTXT::Th("Country") . ':'; ?></label>
                    <div class="cbregField"><?php echo $txtVisibleInputs['country']; ?></div>
                </div>
            </fieldset>
			<?php	}	?>
        </div>
        <div class="cbregCCbutton">
			<?php echo $txtButton; ?>
        </div>
    </div>
	<?php
	}
	/**
	 * Renders and ECHOs the Seal HTML for the credit-card form
	 *
	 * @param  string  $sealCode  Seal HTML for the credit card form
	 * @return void
	 */
	protected function _renderCCsealCode( $sealCode ) {
		if ( $sealCode ) {	?>
        <div class="cbregCCseal">
			<?php echo CBPTXT::Th( $sealCode ); ?>
        </div>
		<?php	}
	}
	/**
	 * Binds notification record from basket
	 *
	 * @param  cbpaidPaymentNotification  $ipn
	 * @param  cbpaidPaymentBasket              $paymentBasket
	 */
	protected function _bindNotificationToBasket( $ipn, &$paymentBasket ) {
		$privateVarsList = 'id user_id time_initiated time_completed ip_addresses mc_gross mc_currency '
			.	'quantity item_number item_name shared_secret payment_date payment_status '
			.	'invoice period1 period2 period3 mc_amount1 mc_amount2 mc_amount3';
		$paymentBasket->bindObjectToThisObject( $ipn, $privateVarsList );
		if ( $ipn->payment_status === 'Completed' ) {
			$paymentBasket->payment_date	=	$ipn->payment_date;
		}
	}
	/**
	 * Attempts to authorize and capture a credit card for a single payment of a payment basket, then checks for errors and if errors are there, logs and sets them.
	 *
	 * @param  array                      $card                 : $card['type'], $card['number'], $card['firstname'], $card['lastname'], $card['expmonth'], $card['expyear']
	 * @param  cbpaidPaymentBasket        &$paymentBasket
	 * @param  int                        $now                  unix timestamp of now
	 * @param  cbpaidPaymentNotification  $ipn                  returns the stored notification
	 * @param  boolean                    $authnetSubscription  true if it is a subscription and amount is in mc_amount1 and not in mc_gross
	 * @return mixed                                            INT subscriptionId if subscription request succeeded, otherwise ARRAY( 'level' => 'spurious' or 'fatal', 'errorText', 'errorCode' => string )
	 *  of error to display
	 */
	protected function _attemptSinglePayment( $card, &$paymentBasket, $now, &$ipn, $authnetSubscription ) {
		$ipn					=	null;
		$authorize_trans_id		=	$this->processSinglePayment( $card, $paymentBasket, $now, $ipn, $authnetSubscription );
		if ( is_string( $authorize_trans_id ) ) {
			$this->_bindNotificationToBasket( $ipn, $paymentBasket );
			return $authorize_trans_id;
		} elseif ( is_array( $authorize_trans_id ) && isset( $authorize_trans_id['errorCode'] ) && isset( $authorize_trans_id['errorText'] ) ) {
			$this->_setLogErrorMSG( 5, $ipn, $this->getPayName() . ' Single payment error returned ' . $authorize_trans_id['errorCode'], $authorize_trans_id['errorText'] );
		} else {
			$this->_setLogErrorMSG( 3, $ipn, $this->getPayName() . ' Single payment unknown error', CBPTXT::T("Submitted payment didn't return an error but didn't complete.") );
		}
		return false;
	}
	/**
	 * Checks if page executes in https mode
	 *
	 * @param  string  $mode     NULL or 'params'
	 * @return boolean true if https mode, otherwise false.
	 */
	protected function _checkIfHttpS( $mode = null ) {
		if ( ( $mode != 'params' || ( $this->params->get( 'cc_http_test_mode', 'https' ) != 'http' ) ) ) {
			return ( isset( $_SERVER['HTTPS'] ) && ( !empty( $_SERVER['HTTPS'] ) ) && ( $_SERVER['HTTPS'] != 'off' ) );
		} else {
			return true;
		}
	}
	/**
	 * Returns the already sefed $url with an https://{live_site}/ in front
	 *
	 * @param  string  $url
	 * @return string
	 */
	protected function live_siteUrlToHttps( $url ) {
		if ( $this->params->get( 'cc_http_test_mode', 'https' ) != 'http' ) {

			if ( cbStartOfStringMatch( $url, 'http:' ) ) {
				$url		= str_replace( 'http://', 'https://', $url );
			}
			if ( ! cbStartOfStringMatch( $url, 'https://' ) ) {
				echo CBPTXT::T("Sorry your Joomla global configuration for live_site has an error, it should start with http:// or https:// . Secure https:// URL could not be generated. Please fix your configuration.php");
				exit;
			}
		}
		return $url;
	}

	/**
	 * Gets the HTTPS redirect URL for a given payment basket $paymentBasket of a a user $user
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  array                $addPluginUrlVars  GET-variables of the plugin (will be handled by CB plugins API)
	 * @param  array                $addUrlVars        Additional GET-variables (no CB plugins API handling)
	 * @param  string               $httpmode          HTTP/HTTPS mode: 'https' (default) or 'http'
	 * @return string                                  Sefed URL
	 */
	protected function _getHttpsRedirectUrl( /** @noinspection PhpUnusedParameterInspection */ &$paymentBasket, $addPluginUrlVars = null, $addUrlVars = null, $httpmode = 'https'  ) {
		if ( $addPluginUrlVars === null ) {
			$addPluginUrlVars	=	array();
		}
		if ( $addUrlVars === null ) {
			$addUrlVars			=	array();
		}

		$additionalArr	= array();
		$arr					=	array_merge( $additionalArr, $addPluginUrlVars );
		$url					=	$this->_getAbsURLwithParam( $arr, 'pluginclass', false );
		foreach ( $addUrlVars as $k => $v ) {
			$url				.=	'&'.urlencode( $k ) . '=' . urlencode( $v );
		}
		$url					=	cbSef( $url, false );
		if ( ! cbStartOfStringMatch( $url, 'http' ) ) {
			echo CBPTXT::T("Your Joomla global configuration for live_site does not start with http:// or https:// . Secure https:// URL could not be generated. Please fix your configuration.php");
			exit;
		}
		if ( $httpmode != 'http' ) {
			$url				=	str_replace( 'http://', 'https://', $url );
		}
		return $url;
	}
}	// end class cbpaidCreditCardsPayHandler.
