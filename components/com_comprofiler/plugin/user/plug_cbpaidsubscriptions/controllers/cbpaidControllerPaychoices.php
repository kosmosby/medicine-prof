<?php
/**
 * @version $Id: cbpaidControllerPaychoices.php 1546 2012-12-02 23:16:25Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;
use CBLib\Registry\Registry;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * This class handles different payment methods
 *
 */
class cbpaidControllerPaychoices {
	/**
	 * Constructor
	 */
	public function __construct() {
	}
	/**
	 * returns the static cbpaidControllerPaychoices (and creates it if needed)
	 *
	 * @return cbpaidControllerPaychoices object
	 */
	public static function getInstance() {
		/** @var cbpaidControllerPaychoices */
		static $payMeansClass	=	null;

		if ( $payMeansClass === null ) {
			$payMeansClass	=	new self();
		}
		return $payMeansClass;
	}
	/**
	 * Gets all payment classes installed AND enabled
	 *
	 * @param  int          $owner
	 * @param  boolean      $enabled
	 * @param  string       $currency    Currency of payment that must be accepted
	 * @return cbpaidGatewayAccount[]  objects for the installed payment classes
	 */
	public function & getPayAccounts( $owner = 0, $enabled = true, $currency = null ) {
		global $_CB_database;

		$gatewayAccountsMgr			=&	cbpaidGatewaysAccountsMgr::getInstance( $_CB_database );
		$accounts					=&	$gatewayAccountsMgr->loadEnabledAccounts( $owner, $enabled, $currency );
		return $accounts;
	}
	/**
	 * Gets a gateway account (regardless of enabled or not !)
	 *
	 * @param  int|null              $gateAccount
	 * @return cbpaidGatewayAccount                object for the corresponding account or FALSE in case of error
	 */
	public function & getPayAccount( $gateAccount ) {
		global $_CB_database;

		if ( ! $gateAccount ) {
			$false				=	false;
			return $false;
		}
		$gatewayAccountsMgr			=&	cbpaidGatewaysAccountsMgr::getInstance( $_CB_database );
		$account					=&	$gatewayAccountsMgr->getObject( (int) $gateAccount );
		return $account;
	}
	/**
	 * Render the payment possibilities as radios, buttons, or URL for redirect of browser
	 *
	 * @param  cbpaidPaymentRadio[][]      $payChoicesHtmlArray
	 * @param  cbpaidPaymentBasket         $paymentBasket
	 * @param  string                      $redirectNow
	 * @param  string                      $chosenPaymentMethod
	 * @param  array                       $payChoicesHtmlRadiosArray  OUT
	 * @param  cbpaidGatewaySelector|null  $chosenPaymentSelector      OUT
	 * @return string
	 */
	private function _renderPayChoicesArray( $payChoicesHtmlArray, $paymentBasket, $redirectNow, $chosenPaymentMethod, &$payChoicesHtmlRadiosArray, &$chosenPaymentSelector ) {
		$ret										=	array();
		$chosenPaymentSelector						=	null;

		if ( ( $redirectNow == 'redirect' ) || ( $redirectNow === true ) ) {
			foreach ( $payChoicesHtmlArray as $drawParams ) {
				if ( is_array( $drawParams ) ) {
					if ( is_string( $drawParams[0] ) ) {
						// Redirect is possible: Just return URL:
						$ret						=	$drawParams[0];
					} else {
						// Redirect is wished but instead we got a button, as redirect was not possible (e.g. URL > 2k IE limit with Paypal encrypted payments):
						/** @var $renderer cbpaidBasketView */
						$renderer					=	cbpaidTemplateHandler::getViewer( null, 'basket' );
						$ret[]						=	$renderer->drawPaymentButton( $drawParams[0] );
					}
				}
			}
		} elseif ( $redirectNow == 'radios' ) {
			$payment_method_radios_template			=	cbpaidApp::settingsParams()->get( 'payment_method_radios_template', '' );
			/** @var $renderer cbpaidBasketView */
			$renderer								=	cbpaidTemplateHandler::getViewer( $payment_method_radios_template, 'basket' );
			$renderer->setModel( $paymentBasket );
			foreach ( $payChoicesHtmlArray as $gatewaySubMethods ) {
				if ( is_array( $gatewaySubMethods ) ) {
					foreach ( $gatewaySubMethods as $radioPaymentSelector ) {
						/** @var $radioPaymentSelector cbpaidGatewaySelectorRadio */
						$radioValue					=	$radioPaymentSelector->radioValue();
						$selected					=	( $chosenPaymentMethod === $radioValue );
						if ( $selected ) {
							$chosenPaymentSelector	=	$radioPaymentSelector;
						}
						$payChoicesHtmlRadiosArray[] =	array( $selected, $renderer->drawPaymentRadio( $radioPaymentSelector, $selected ) );
					}
				} elseif ( is_string( $gatewaySubMethods ) ) {
					$ret[]							=	$gatewaySubMethods;
				}
			}
		} else {
			/** @var $renderer cbpaidBasketView */
			$renderer								=	cbpaidTemplateHandler::getViewer( null, 'basket' );
			$renderer->setModel( $paymentBasket );
			foreach ( $payChoicesHtmlArray as $gatewaySubMethods ) {
				if ( is_array( $gatewaySubMethods ) ) {
					foreach ( $gatewaySubMethods as $paymentButton ) {
						$ret[]						=	$renderer->drawPaymentButton( $paymentButton );
					}
				} elseif ( is_string( $gatewaySubMethods ) ) {
					$ret[]							=	$gatewaySubMethods;
				}
			}
		}
		return $ret;
	}
	/**
	 * Gets payment methods and parameters available to pay this basket.
	 *
	 * @param  UserTable            $user
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  string               $introText
	 * @param  string               $redirectNow   OUT : 'buttons', 'radios', 'redirect'
	 * @return array
	 */
	public function getPaymentMethodsParams( $user, $paymentBasket, $introText, &$redirectNow ) {
		$params								=	cbpaidApp::settingsParams();
		$invoicingAddressQuery				=	$params->get( 'invoicing_address_query' );
		$basket_requiredterms				=	$params->get( 'basket_requiredterms' );

		$payChoicesHtmlArray				=	array();

		if ( $paymentBasket->mc_amount1 != 0 || $paymentBasket->mc_amount3 != 0 || $paymentBasket->mc_gross != 0 ) {
			$payment_method_selection_type	=	$params->get( 'payment_method_selection_type', 'buttons' );
			$payAccounts					=&	$this->getPayAccounts( $paymentBasket->owner, true, $paymentBasket->mc_currency );
			$nbAccounts						=	count( $payAccounts );

			$redirectNowPossible			=	! ( ( $nbAccounts != 1 ) || $introText || ( $invoicingAddressQuery > 0 ) || ( $basket_requiredterms > 0 ) );
			if ( $redirectNowPossible ) {
				$redirectNow				=	'redirect';
			} else {
				if ( $payment_method_selection_type == 'radios' ) {
					$redirectNow			=	'radios';
				} else {
					$redirectNow			=	'buttons';
				}
			}

			if ( $nbAccounts > 0 ) {
				foreach ( array_keys( $payAccounts ) as $k ) {
					$payClass				=	$payAccounts[$k]->getPayMean();
					$payChoicesHtmlArray[]	=	$payClass->getPaymentBasketProcess( $user, $paymentBasket, $payClass->gatewayApiVersion == '1.2.0' ? ( $redirectNow == 'redirect' ) : $redirectNow );
				}
			} else {
				$result						=	'<div class="error">' . sprintf( CBPTXT::Th( "No payment gateway defined for the selling owner %s of these products." ), $paymentBasket->owner ) . "</div>\n";
				cbpaidApp::getBaseClass()->_setErrorMSG( $result );
			}

			$isAnyAutoRecurring				=	$paymentBasket->isAnyAutoRecurring();
			if ( ( $isAnyAutoRecurring != 1 ) && ( $paymentBasket->period1 ) && ( $paymentBasket->mc_amount1 == 0 ) )  {
				// Free trial button:
				$payChoicesHtmlArray[]		=	$this->getFreeTrialButton( $user, $paymentBasket, $redirectNow );
			}

		} else {
			// Free basket (after disounts): Free purchase button:
			$redirectNowPossible			=	! ( $introText || ( $invoicingAddressQuery > 0 ) || ( $basket_requiredterms > 0 ) );
			$redirectNow					=	$redirectNowPossible ? 'redirect' : 'buttons';

			// Free order button:
			$payChoicesHtmlArray[]			=	$this->getFreeTrialButton( $user, $paymentBasket, $redirectNow, 'order' );
		}
		return $payChoicesHtmlArray;
	}
	/**
	 * Give all rendering parameters for Free trial/Order now button
	 *
	 * @param  UserTable            $user
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  string               $redirectNow    'buttons', 'radios', 'redirect'
	 * @param  string               $buttonType     'freetrial' or 'order'
	 * @return array
	 */
	private function getFreeTrialButton( $user, $paymentBasket, $redirectNow, $buttonType = 'freetrial' ) {
		cbpaidApp::import( 'processors.freetrial.freetrial' );
		$freetrial							=	new cbpaidGatewayAccountfreetrial();
		$payClass							=	$freetrial->getPayMean();
		$payClass->_button					=	$buttonType;
		return $payClass->getPaymentBasketProcess( $user, $paymentBasket, $redirectNow );
	}
	/**
	 * Give all rendering parameters for a payment button
	 * or error html
	 *
	 * @param  UserTable                   $user
	 * @param  cbpaidPaymentBasket         $paymentBasket
	 * @param  int                         $gatewayId
	 * @param  string                      $paymentType            'single', 'subscribe' or gateway-specific payment type
	 * @param  cbpaidGatewaySelector|null  $chosenPaymentSelector
	 * @return array|string
	 */
	private function getPayMethodButton( $user, $paymentBasket, $gatewayId, /** @noinspection PhpUnusedParameterInspection */ $paymentType, $chosenPaymentSelector ) {
		if ( is_numeric( $gatewayId ) ) {
			// A payment gateway has been choosen:
			$payAccounts					=&	$this->getPayAccounts( $paymentBasket->owner, true );
			if ( isset( $payAccounts[$gatewayId] ) ) {
				$payClass					=	$payAccounts[$gatewayId]->getPayMean();
				$payChoices					=	$payClass->getPaymentBasketProcess( $user, $paymentBasket, $payClass->gatewayApiVersion == '1.2.0' ? false : 'buttons' );
				if ( is_array( $payChoices ) ) {
					foreach ( $payChoices as $paymentButton ) {
						if ( ( $paymentButton->paymentType == $chosenPaymentSelector->paymentType ) && ( $paymentButton->subMethod == $chosenPaymentSelector->subMethod ) ) {
							return array( $paymentButton );
						}
					}
				}
				return $payChoices;
			} else {
				return '<div class="error">' . CBPTXT::Th("The chosen payment method is not available. Please choose another one.") . '</div>';
			}
		} elseif ( $gatewayId == 'freetrial' ) {
			// Free trial choice:
			return $this->getFreeTrialButton( $user, $paymentBasket, 'buttons' );
		} else {
			trigger_error( 'Unknown payment method', E_USER_WARNING );
			return null;
		}

	}
	/**
	 * Modify aspect of single payment button if there is only one to match the global parameter for this
	 *
	 * @param  cbpaidGatewaySelector[]  $payChoicePayButton
	 * @param  string                   $paymentType
	 * @return void
	 */
	private function modifyAspectPayMethodButton( &$payChoicePayButton, $paymentType = 'single' ) {
		if ( is_array( $payChoicePayButton ) && ( count( $payChoicePayButton ) == 1 ) ) {
			$params							=	cbpaidApp::settingsParams();
			$payment_button					=	$params->get( 'payment_button' );
			if ( $payment_button == 'paybutton' ) {
				$method						=	( $paymentType == 'subscribe' ? 'subscribe' : 'single' );
				$prmImg						=	'paybutton_' . $method . '_image';
				$prmCustImg					=	'paybutton_' . $method . '_custom_image';
				$customImage				=	trim( $params->get( $prmCustImg ) );
				if ( $customImage == '' ) {
					$customImage			=	$params->get( $prmImg );
				}
				if ( $customImage ) {
					$payChoicePayButton[0]->customImage		=	$customImage;
				}
			}
		}
	}
	/**
	 * Gets all allowed currencies as an array (including primary and secondary currency)
	 *
	 * @return array
	 */
	public function getAllCurrencies( ) {
		$params								=	cbpaidApp::settingsParams();
		$currency_code						=	$params->get( 'currency_code' );
		$secondary_currency_code			=	$params->get( 'secondary_currency_code' );
		$allowed_currencies					=	$params->get( 'allowed_currencies' );

		$allCurrencies						=	$allowed_currencies ? explode( '|*|', $allowed_currencies ) : array();

		if ( $secondary_currency_code && ! in_array( $secondary_currency_code, $allCurrencies ) ) {
			array_unshift( $allCurrencies, $secondary_currency_code );
		}
		if ( $currency_code && ! in_array( $currency_code, $allCurrencies ) ) {
			array_unshift( $allCurrencies, $currency_code );
		}
		return $allCurrencies;
	}
	/**
	 * Renders a <select> drop-down with currency
	 *
	 * @param  string  $selectedCurrency
	 * @param  string  $currencyInputName
	 * @return string
	 */
	private function drawCurrencySelect( $selectedCurrency, $currencyInputName ) {
		$allCurrencies						=	$this->getAllCurrencies();
		if ( ! in_array( $selectedCurrency, $allCurrencies ) ) {
			array_unshift( $allCurrencies, $selectedCurrency );
		}
		$values								=	array();
		foreach ( $allCurrencies as $currency ) {
			$values[]						=	moscomprofilerHTML::makeOption( $currency, CBPTXT::T( $currency ) );
		}
		return moscomprofilerHTML::selectList( $values, $currencyInputName, 'class="cpayCurrency cpayOrderCurrency"', 'value', 'text', $selectedCurrency, 2 );
	}
	/**
	 * Renders a drop-down with currency
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return string
	 */
	private function displayCurrencySelector( $paymentBasket ) {
		list( $getParams, $formHiddens, $currencyInputName )	=	self::getCurrencyChangeFormParams( $paymentBasket );

		$htmlCurrency						=	$this->drawCurrencySelect( $paymentBasket->mc_currency, $currencyInputName );

		$methodsHTML						=	'<div class="cbregPaymentCurrencyChoice">'
			.	'<label for="cpayOrderCurrency" class="cpayCurrencyLabel">' . CBPTXT::Th("Currency") . ':' . '</label>' . ' '
			.	$htmlCurrency
			.	' '
			.	'<span class="cb_button_wrapper">'
			.	'<button type="submit" id="cbregSelectCurrency">' . CBPTXT::Th("Change Currency") . '</button>'
			.	'</span>'
			.	"</div>\n"
		;
		$subscriptionsGUI					=	new cbpaidControllerUI();
		$result								=	'<div class="cbregCurrencySelect">' . $subscriptionsGUI->drawForm( $methodsHTML, null, $formHiddens, $getParams ) . "</div>\n";
		$subscriptionsGUI->addcbpaidjsplugin();
		return $result;
	}
	/**
	 * Returns the URL and hidden params, and name of currency param for a form to change currency
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return array                ( url unsefed/unhtmlspecialchared, array( hidden form params ), name of currency input )
	 */
	public static function getCurrencyChangeFormParams( $paymentBasket ) {
		$getParams							=	$paymentBasket->getSetBasketPaymentMethodUrl( null, 'html', 'setbsktcurrency' );
		$ajaxGetParams						=	cbUnHtmlspecialchars( $paymentBasket->getSetBasketPaymentMethodUrl( null, 'raw', 'setbsktcurrency' ) );
		$formHiddens						=	array(	cbpaidApp::getBaseClass()->_getPagingParamName('act') => 'setbsktcurrency',
			'ajaxurl' => bin2hex( $ajaxGetParams ) );
		return array( $getParams, $formHiddens, 'currency' );
	}
	/**
	 * display basket and payment buttons or redirect for payment depending if multiple payment choices or intro text present:
	 *
	 * @param  UserTable            $user
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  string               $introText
	 * @param  boolean              $ajax           TRUE if AJAX refresh inside #cbregPayMethodsChoice, FALSE: wraps in <div id="cbregPayMethodsChoice">
	 * @return string                               HTML  (or DOES REDIRECT if $redirectNow = ! ( ( $nbClasses != 1 ) || $introText ) == TRUE)
	 */
	public function getPaymentBasketPaymentForm( &$user, &$paymentBasket, $introText, $ajax = false ) {
		global $_PLUGINS;

		$result								=	null;

		$params								=	cbpaidApp::settingsParams();
		$invoicingAddressQuery				=	$params->get( 'invoicing_address_query' );
		$basket_requiredterms				=	$params->get( 'basket_requiredterms' );
		$basket_requiredtermserror			=	$params->get( 'basket_requiredtermserror' );
		$payment_method_selection_type		=	$params->get( 'payment_method_selection_type', 'buttons' );
		$allow_select_currency				=	$params->get( 'allow_select_currency', '0' );

		$redirectNow						=	null;
		$payChoicesArray					=	$this->getPaymentMethodsParams( $user, $paymentBasket, $introText, $redirectNow );

		$chosenPaymentMethod				=	$paymentBasket->gateway_account ? $paymentBasket->gateway_account . '-' . $paymentBasket->payment_type : '';		// cbGetParam( $_POST, 'payment_method' );

		$payChoicesHtmlRadiosArray			=	array();
		$chosenPaymentSelector				=	null;
		$payChoicesHtmlBottomArray			=	$this->_renderPayChoicesArray( $payChoicesArray, $paymentBasket, $redirectNow, $chosenPaymentMethod, $payChoicesHtmlRadiosArray, $chosenPaymentSelector );
		if ( $redirectNow == 'redirect' && is_string( $payChoicesHtmlBottomArray ) ) {
			cbRedirect( $payChoicesHtmlBottomArray );
		}

		$subscriptionsGUI					=	new cbpaidControllerUI();
		$subscriptionsGUI->addcbpaidjsplugin();

		if ( ( $payment_method_selection_type == 'radios') && ( $chosenPaymentMethod != '' ) && $chosenPaymentSelector ) {
			// Select button to draw:
			$payChoicePayButton				=	$this->getPayMethodButton( $user, $paymentBasket, $paymentBasket->gateway_account, $paymentBasket->payment_type, $chosenPaymentSelector );
			/** @var $chosenPaymentSelector cbpaidGatewaySelector */
			$this->modifyAspectPayMethodButton( $payChoicePayButton, $chosenPaymentSelector->paymentType );
			$dummy							=	null;
			$payChoicePayButtonHtmlArray	=	$this->_renderPayChoicesArray( array( $payChoicePayButton ), $paymentBasket, 'buttons', $chosenPaymentMethod, $payChoicesHtmlRadiosArray, $dummy );
			$payChoicesHtmlBottomArray		=	array_merge( $payChoicesHtmlBottomArray, $payChoicePayButtonHtmlArray );
		}

		if ( true )  {
			// always add cancel link
			cbpaidApp::import( 'processors.cancelpay.cancelpay' );
			$cancelmethod					=	new cbpaidGatewayAccountcancelpay();
			$payClass						=	$cancelmethod->getPayMean();
			$payChoicesHtmlBottomArray[]	=	$payClass->getPaymentBasketProcess( $user, $paymentBasket, 'buttons' );	// never redirectNow a cancel link :D !
		}

		$basketHtml							=	$paymentBasket->displayBasket();

		if ( $allow_select_currency == 2 ) {
			$currencySelector				=	$this->displayCurrencySelector( $paymentBasket );
		} else {
			$currencySelector				=	null;
		}
		$txtConclusion						=	$params->get('conclusion_text');
		$txtFinal							=	$params->get('final_text');

		$txtTerms						=	null;
		if ( $basket_requiredterms == 1 ) {
			global $_CB_database, $_CB_framework;

			$query							=	'SELECT ' . $_CB_database->NameQuote( 'params' )
											.	"\n FROM " .  $_CB_database->NameQuote( '#__comprofiler_fields' )
											.	"\n WHERE " . $_CB_database->NameQuote( 'name' ) . " = " . $_CB_database->Quote( 'acceptedterms' );
			$_CB_database->setQuery( $query );
			$tcParams						=	new Registry( $_CB_database->loadResult() );

			$termsOutput					=	$tcParams->get( 'terms_output', 'url' );
			$termsDisplay					=	$tcParams->get( 'terms_display', 'modal' );
			$termsURL						=	$tcParams->get( 'terms_url', null );
			$termsText						=	$tcParams->get( 'terms_text', null );
			$termsWidth						=	(int) $tcParams->get( 'terms_width', 400 );
			$termsHeight					=	(int) $tcParams->get( 'terms_height', 200 );

			if ( ! $termsHeight ) {
				$termsHeight				=	200;
			}

			if ( ( ( $termsOutput == 'url' ) && $termsURL ) || ( ( $termsOutput == 'text' ) && $termsText ) ) {
				if ( $termsDisplay == 'iframe' ) {
					if ( $termsOutput == 'url' ) {
						$txtTerms			.=				'<iframe class="cbTermsFrameURL" height="' . $termsHeight . '" width="' . ( $termsWidth ? $termsWidth : '100%' ) . '" src="' . htmlspecialchars( $termsURL ) . '"></iframe>';
					} else {
						$txtTerms			.=				'<div class="cbTermsFrameText" style="height:' . $termsHeight . 'px;width:' . ( $termsWidth ? $termsWidth . 'px' : '100%' ) . ';overflow:auto;">' . CBPTXT::T( $termsText ) . '</div>';
					}
				}

				if ( $termsDisplay != 'iframe' ) {
					$attributes				=	' class="cbTermsLink"';

					if ( ( $termsOutput == 'text' ) && ( $termsDisplay == 'window' ) ) {
						$termsDisplay		=	'modal';
					}

					if ( $termsDisplay == 'modal' ) {
						if ( ! $termsWidth ) {
							$termsWidth		=	400;
						}

						if ( $termsOutput == 'url' ) {
							$tooltip		=	'<iframe class="cbTermsModalURL" height="' . $termsHeight . '" width="' . $termsWidth . '" src="' . htmlspecialchars( $termsURL ) . '"></iframe>';
						} else {
							$tooltip		=	'<div class="cbTermsModalText" style="height:' . $termsHeight . 'px;width:' . $termsWidth . 'px;overflow:auto;">' . CBPTXT::T( $termsText ) . '</div>';
						}

						$url				=	'javascript:void(0);';
						$attributes			.=	' ' . cbTooltip( $_CB_framework->getUi(), $tooltip, CBPTXT::T( 'Terms and Conditions' ), 'auto', null, null, null, 'data-cbtooltip="true" data-modal="true"' );
					} else {
						$url				=	htmlspecialchars( $termsURL );
						$attributes			.=	' target="_blank"';
					}

					$txtTerms				.=				CBPTXT::P( 'I have read and approve the <a href="[url]"[attributes]>Terms and Conditions</a>', array( '[url]' => $url, '[attributes]' => $attributes ) );
				} else {
					$txtTerms				.=				CBPTXT::T( 'I have read and approve the above Terms and Conditions.' );
				}
			}
		} elseif ( $basket_requiredterms == 2 ) {
			$txtTerms					=	$params->get( 'basket_termsandconditions' );
		}

		if ($introText) {
			$result						.=	'<div class="cbregIntro">' . CBPTXT::Th( $introText ) . "</div>\n";
		}
		$result							.=	$basketHtml;

		if ( $allow_select_currency == 2 ) {
			$result						.=	$currencySelector;
		}

		if ( $invoicingAddressQuery > 0 ) {
			$errorMsg					=	$paymentBasket->checkAddressComplete();
			if ( $errorMsg && ( $invoicingAddressQuery == 2 ) ) {
				$result					=	'';
				$introAddrNeeded		=	$params->get('invoicing_address_required_into_text');
				if ($introAddrNeeded) {
					$result				.=	'<div class="cbregIntro">' . CBPTXT::Th( $introAddrNeeded ) . "</div>\n";
				}
				$result					.=	$paymentBasket->renderInvoicingAddressForm( $user );	// $xmlController->handleAction( 'action', 'editinvoiceaddress' );
				return $result;
			} else {
				if ( $errorMsg ) {
					cbpaidApp::getBaseClass()->_setErrorMSG( $errorMsg );
				}
				$result					.=	'<div class="cbregInvoicingAddress">'
					.	$paymentBasket->renderInvoicingAddressFieldset()
					.	'</div>';
			}
			// display current invoicing address with a link to change/edit it with a back link to the payment basket id
			// if the address is not mandatory.
			// If it is mandatory, check that it is complete (and later also screened),
			// if not display instead of this the invoicing address edit page !
		}
		$integrationsResults			=	$_PLUGINS->trigger( 'onCbSubsAfterPaymentBasket', array( $paymentBasket, &$result, &$txtTerms ) );
		foreach ( $integrationsResults as $intRes ) {
			if ( is_string( $intRes ) ) {
				$result					.=	$intRes;
			}
		}
		if ( $txtConclusion ) {
			$result						.=	'<div class="cbregConcl">' . CBPTXT::Th( $txtConclusion ) . "</div>\n";
		}

		if ( count( $payChoicesHtmlRadiosArray ) > 0 ) {

			$radios_intro_html			=	CBPTXT::Th( $params->get( 'radios_intro_html' ) );
			$radios_conclusion_html		=	CBPTXT::Th( $params->get( ( $chosenPaymentMethod != null ) ? 'radios_selected_conclusion_html' : 'radios_unselected_conclusion_html' ) );

			$htmlList					=	'<ul class="cbregPaymentMethodChoiceList">' . "\n";
			foreach ( $payChoicesHtmlRadiosArray as $selHtmlArr ) {
				if ( $selHtmlArr[0] ) {
					$htmlList			.=	'<li class="cbregCCradioLi cbregCCradioSelected">';
				} else {
					$htmlList			.=	'<li class="cbregCCradioLi">';				//LATER:  class="cbpaidCCradio cbpaidRadio_<?php echo htmlspecialchars( $this->payNameForCssClass ); " id="<?php echo htmlspecialchars( $this->butId );
				}
				$htmlList				.=	'<div class="cbregCCradioLiBg"></div>'		// This allows to use the CSS trick for highlighting as explained here: http://www.commentcamarche.net/forum/affich-3898635-transparance-du-fond-uniquement
					.	$selHtmlArr[1]
					.	"</li>\n";
			}
			$htmlList					.=	"</ul>\n";

			$methodsHTML				=	'<div class="cbregPaymentMethodChoice ' . ( ( $chosenPaymentMethod != null ) ? 'cbregPMselected' : 'cbregPMunselected' ) . '">'
				.	( $radios_intro_html ? '<h2 class="contentheading cbregPaymenMethodChoiceIntro">' . $radios_intro_html . '</h2>' : '' )
				.	$htmlList
				.	'<span class="cb_button_wrapper">'
				.	'<button type="submit" id="cbregSelectPayment">' . CBPTXT::Th("Change Payment Method") . '</button>'
				.	'</span>'
				.	( $radios_conclusion_html ? '<div class="cbregPaymenMethodChoiceConclusion">' . $radios_conclusion_html . '</div>' : '' )
				.	"</div>\n"
			;
			$getParams					=	$paymentBasket->getSetBasketPaymentMethodUrl( $user );
			$ajaxGetParams				=	cbUnHtmlspecialchars( $paymentBasket->getSetBasketPaymentMethodUrl( $user, 'raw' ) );
			$formHiddens				=	array(	cbpaidApp::getBaseClass()->_getPagingParamName('act') => 'setbsktpmtmeth',
				'ajaxurl' => bin2hex( $ajaxGetParams ) );
			$result						.=	'<div class="cbregPaymentMethodsSelect">' . $subscriptionsGUI->drawForm( $methodsHTML, null, $formHiddens, $getParams ) . "</div>\n";
			$termsCanBeDisplayed		=	( $payment_method_selection_type != 'radios' ) || ( $chosenPaymentMethod != null );
		} else {
			$termsCanBeDisplayed		=	true;
		}

		if ( $txtTerms ) {
			if ( $termsCanBeDisplayed ) {
				$accepted				=	( cbGetParam( $_POST, 'terms_accepted', 0 ) == 1 );
				$settings				=	'<div class="cbregTermsAccept"><input type="checkbox" class="required" name="terms_accepted" id="terms_accepted" value="1"'
					.	( $accepted ? ' checked="checked" disabled="disabled" ' : '' )
					.	'/> '
					.	'<label for="terms_accepted">'
					.	$txtTerms
					.	'</label></div>'
				;
				if ( ! $accepted ) {
					$settings			.=	'<span class="cb_button_wrapper">'
						.	'<button type="submit" id="cbTermsAccept" title="' . htmlspecialchars( CBPTXT::T( $basket_requiredtermserror ) ) . '">' . CBPTXT::Th("Accept Terms") . '</button>'
						.	'</span>'
					;
				}
				$getParams				=	$accepted ? '#' : $paymentBasket->getShowBasketUrl( false );
				$formHiddens			=	$accepted ? array( 'terms_accepted' => 1 ) : array();
				$result					.=	'<div class="cbregTerms">' . $subscriptionsGUI->drawForm( $settings, null, $formHiddens, $getParams ) . "</div>\n";
			} else {
				$accepted				=	false;
			}
		} else {
			$accepted					=	true;
		}

		$result							.=	'<div class="cbpayChoices cbclearboth"'
			.	( $termsCanBeDisplayed && $txtTerms && ! $accepted ? ' style="display:none;"' : '' )
			.	">\n "
			.	implode ( "\n  ", $payChoicesHtmlBottomArray )
			.	"\n</div>\n";
		if ( $txtFinal ) {
			$result						.=	'<div class="cbregFinalText">' . CBPTXT::Th( $txtFinal ) . "</div>\n";
		}

		$result							=	'<div class="cbpayBasketView">' . $result . '</div>';
		if ( ! $ajax ) {
			$result						=	'<div id="cbpayOrderContainer">'	// Needed for Javascript delegated binding
				.	$result
				.	'</div>';
		}
		return $result;
	}
}	// class cbpaidControllerPaychoices
