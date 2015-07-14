<?php
/**
 * @version $Id: cbpaidControllerCBTab.php 1608 2012-12-29 04:12:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

use CBLib\Registry\GetterInterface;
use CBLib\Registry\ParamsInterface;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

/**
 * CBSubs HTTPS remote web-services requests class
 */
class cbpaidControllerCBTab {
	/**
	 * @var ParamsInterface
	 */
	protected $params;
	/**
	 * @var getcbpaidsubscriptionsTab
	 */
	protected $base;

	/**
	 * Constructor
	 */
	public function __construct( ) {
		$this->params	=	cbpaidApp::settingsParams();
		$this->base		=	cbpaidApp::getBaseClass();

	}
	/**
	 * Returns a protected user-specific invoice display address URL
	 *
	 * @param  UserTable  $user
	 * @return string
	 */
	protected function getInvoicesListUrl( &$user ) {
		$basegetarray			=	array( 'user' => $user->id, 'Itemid' => getCBprofileItemid( 0 ), 'act' => 'showinvoiceslist' );
		return $this->base->getHttpsAbsURLwithParam( $basegetarray, 'pluginclass', true );
	}
	/**
	 * Gets invoices of $user for $invoicesShowPeriod, or just the count if $countOnly
	 *
	 * @param  UserTable                  $user
	 * @param  string                     $invoicesShowPeriod  SQL DATETIME formatted period of time
	 * @param  boolean                    $countOnly           Count only, do not get them
	 * @return cbpaidPaymentBasket[]|int
	 */
	protected function _getInvoices( &$user, $invoicesShowPeriod, $countOnly ) {
		global $_CB_framework;

		$now					=	$_CB_framework->now();
		$basketsMgr				=	cbpaidOrdersMgr::getInstance();
		return $basketsMgr->getBaskets( $user, $invoicesShowPeriod, $now, $countOnly );
	}
	/**
	 * Displays a given invoice number $invoiceNo of $user
	 *
	 * @param  int        $invoiceNo
	 * @param  UserTable  $user
	 * @return string
	 */
	protected function showInvoice( $invoiceNo, $user ) {
		global $_CB_database, $_CB_framework;

		$return									=	'';

		$params									=	$this->params;
		$showInvoices							=	$params->get( 'show_invoices', 1 );
		if ( $showInvoices ) {
			$invoiceNo							=	(int) $invoiceNo;
			if ( $invoiceNo ) {
				$basket							=	new cbpaidPaymentBasket( $_CB_database );
				if ( ( $basket->load( (int) $invoiceNo ) ) && ( $user->id == $basket->user_id ) ) {
					$ok						=	false;
					$myId					=	$_CB_framework->myId();
					if ( $myId == 0 ) {
						$ck					=	$this->base->_getReqParam( 'invoicecheck' );
						if ( $ck && ( $ck == $basket->checkHashInvoice( $ck ) ) ) {
							$ok				=	true;
						}
					} elseif ( $user->id == $myId ) {
						$ok					=	true;
					} elseif ( cbpaidApp::authoriseAction( 'cbsubs.sales' ) || cbpaidApp::authoriseAction( 'cbsubs.financial' ) ) {
						$ok					=	true;
					}
					if ( $ok ) {
						$itsmyself			=	( $_CB_framework->myId() == $user->id );
						$return				.=	$basket->displayInvoice( $user, $itsmyself );
					} else {
						$return .=	'<div class="error">'
							.	CBPTXT::Th("You need to be logged in to view your private information.")
							.	'</div>'
						;
					}
				} else {
					$this->base->_setErrorMSG( CBPTXT::T("No unpaid payment basket found.") );
				}
			} else {
				$this->base->_setErrorMSG( CBPTXT::T("Not authorized action") );
			}
		} else {
			$this->base->_setErrorMSG( CBPTXT::T("Not authorized action") );
		}
		return $return;
	}
	/**
	 * Displays user subscription and link to invoice HTML (if allowed)
	 *
	 * @param  UserTable  $user
	 * @param  string     $htmlTabDescription
	 * @return string
	 */
	public function displaySubscriptionsAndInvoicesLink( $user, $htmlTabDescription = null ) {
		global $_CB_framework;

		$return					=	'';
		$params					=	$this->params;

		$itsmyself				=	( $_CB_framework->myId() == $user->id );
		$displayToMe			=	$itsmyself;
		if ( ! $itsmyself ) {
			$displayToMe		=	cbpaidApp::authoriseAction( 'cbsubs.usersubscriptionview' );
			if ( $displayToMe ) {
				$itsmyself		=	cbpaidApp::authoriseAction( 'cbsubs.usersubscriptionmanage' );
			}
		}
		if ( $user->id && $displayToMe ) {

			$basketsMgr				=&	cbpaidOrdersMgr::getInstance();
			$basketsMgr->timeoutUnusedBaskets( $user->id );

			$subscriptionsGUI		=	new cbpaidControllerUI();
			$htmlSubscriptionsAndUpgrades =	$subscriptionsGUI->getShowSubscriptionUpgrades( $user, $itsmyself );

			$htmlInvoicesLink		=	null;
			$showInvoices			=	$params->get( 'show_invoices', 1 );
			$invoicesShowPeriod		=	$params->get( 'invoices_show_period', '0000-06-00 00:00:00' );
			if ( $showInvoices ) {
				$invoicesNumber		=	$this->_getInvoices( $user, $invoicesShowPeriod, true );
				if ( $invoicesNumber > 0 ) {
					$invoicesListUrl =	$this->getInvoicesListUrl( $user );
					if ( $invoicesShowPeriod && ( $invoicesShowPeriod != '0000-00-00 00:00:00' ) ) {
						$cbpaidTimes	=&	cbpaidTimes::getInstance();
						$periodText		=	$cbpaidTimes->renderPeriod( $invoicesShowPeriod, 1, false );
					} else {
						$periodText		=	'';
					}
					$htmlInvoicesLink	=	$subscriptionsGUI->showInvoicesListLink( $invoicesNumber, $invoicesListUrl, $user, $itsmyself, $periodText );
				}
			}

			$tabTitleText			=	$params->get( 'profileTitle', "Your subscriptions" );

			/** @var $viewer cbpaiduserprofilesubstabView */
			$viewer					=	cbpaidTemplateHandler::getViewer( null, 'userprofilesubstab' );
			$viewer->setModel( $user );
			$return					.=	$viewer->drawTab( $htmlSubscriptionsAndUpgrades, $htmlInvoicesLink, $tabTitleText, $htmlTabDescription );
		}
		return $return;
	}
	/**
	 * WARNING: UNCHECKED ACCESS! On purpose unchecked access for M2M operations
	 * Generates the HTML to display for a specific component-like page for the tab. WARNING: unchecked access !
	 * @param  TabTable|null  $tab       the tab database entry
	 * @param  UserTable      $user      the user being displayed
	 * @param  int            $ui        1 for front-end, 2 for back-end
	 * @param  array          $postdata  _POST data for saving edited tab content as generated with getEditTab
	 * @return mixed                     either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getTabComponent( /** @noinspection PhpUnusedParameterInspection */ $tab, $user, $ui, $postdata ) {
		global $_CB_database, $_CB_framework, $_POST;

		$return								=	'';
		$paid								=	false;

		$oldignoreuserabort = ignore_user_abort(true);

		$allowHumanHtmlOutput				=	true;			// this will be reverted in case of M2M server-to-server notifications

		$act								=	$this->base->_getReqParam( 'act' );
		$actPosted							=	isset($_POST[$this->base->_getPagingParamName('act')]);

		if ( $act === null ) {
			$act							=	$this->base->input( 'act', null, GetterInterface::COMMAND );
			$actPosted						=	$this->base->input( 'post/act', null, GetterInterface::COMMAND ) !== null;
		}

		$post_user_id						=	(int) cbGetParam( $_GET, 'user', 0 );

		if ( $actPosted && ( $post_user_id > 0 ) ) {
			$access							=	false;
			$myId							=	$_CB_framework->myId();
			if ( is_object( $user ) ) {
				if ( $myId == 0 ) {
					if ( in_array( $act, array( 'saveeditinvoiceaddress', 'saveeditbasketintegration', 'showbskt' ) ) ) {
						$access				=	true;
					} else {
						$paidsubsManager	=&	cbpaidSubscriptionsMgr::getInstance();
						if ( ! $paidsubsManager->checkExpireMe( __FUNCTION__, $user->id, false ) ) {
							// expired subscriptions: we will allow limited access to:
							if ( in_array( $act, array( 'upgrade', 'pay', 'reactivate', 'resubscribe', 'display_subscriptions' ) ) ) {
								$access		=	true;
							}
						}
					}
				} else {
					if ( ( $ui == 1 && ( $user->id == $myId ) )
						||	 ( cbpaidApp::authoriseAction( 'cbsubs.usersubscriptionmanage' ) ) ) {
						$access				=	true;
					}
				}
			} else {
				$return						=	CBPTXT::T("User does not exist") . '.';
			}
			if ( ! $access ) {
				$return						.=	'<br />' . CBPTXT::T("Not authorized action") . '.';
				return $return;
			}

			cbSpoofCheck( 'plugin' );		// anti-spoofing check


			// renew or upgrade subscription payment form:
			$params							=	$this->params;
			$now							=	$_CB_framework->now();
			$subscriptionsGUI				=	new cbpaidControllerUI();
			$subscriptionIds				=	$subscriptionsGUI->getEditPostedBoxes( 'id' );

			if ( $subscriptionIds == array( 0 ) ) {
				$subscriptionIds			=	array();
			}
			if ( $post_user_id && ( $user->id == $post_user_id ) ) {
				outputCbTemplate();
				$this->base->outputRegTemplate();
				outputCbJs();
				switch ( $act ) {
					case 'upgrade':		// upgrade an existing subscription
						// display basket and payment buttons or redirect for payment depending if multiple payment choices or intro text present:
						$chosenPlans		=	$subscriptionsGUI->getAndCheckChosenUpgradePlans( $postdata, $user, $now );
						if ( ( ! is_array( $chosenPlans ) ) || ( count( $chosenPlans ) == 0 ) ) {
							$subTxt			=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
							$return			.=	( is_string( $chosenPlans ) ? $chosenPlans . '<br />' : '' )
								.	sprintf( CBPTXT::Th("Please press back button and select the %s plan to which you would like to upgrade."), $subTxt );
							break;
						}
						$introText			=	CBPTXT::Th( $params->get( 'intro_text_upgrade', null ) );
						//TBD: check if already exists (reload protection):
						$paymentBasket		=	cbpaidControllerOrder::createSubscriptionsAndPayment( $user, $chosenPlans, $postdata, $subscriptionIds, null, 'R', CBPTXT::T("Upgrade"), 'U' );
						if ( is_object( $paymentBasket ) ) {
							$return			=	cbpaidControllerOrder::showBasketForPayment( $user, $paymentBasket, $introText );
						} else {
							$return			=	$paymentBasket;		// show messages as nothing to pay.
						}
						break;
					case 'pay':			// pay for an unpaid subscription
						// display basket and payment buttons or redirect for payment depending if multiple payment choices or intro text present:
						$plan				=	$this->base->_getReqParam( 'plan' );
						if ( ( ! $plan ) || ( ! isset( $subscriptionIds[$plan] ) ) || ( ! $subscriptionIds[$plan] ) ) {
							$subTxt			=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
							$return			.=	sprintf( CBPTXT::Th("Please press back button and select a %s plan."), $subTxt );
							break;
						}
						$plansMgr			=&	cbpaidPlansMgr::getInstance();
						$chosenPlans		=	array();
						$chosenPlans[(int) $plan]		=	$plansMgr->loadPlan( (int) $plan );
						$introText			=	CBPTXT::Th( $params->get( 'intro_text', null ) );
						$paymentStatus		=	null;
						$return				=	cbpaidControllerOrder::showPaymentForm( $user, $chosenPlans, $introText, $subscriptionIds, $paymentStatus );
						break;
					case 'renew':		// renew a still valid subscription
					case 'reactivate':	// reactivate an expired subscription
					case 'resubscribe':	// resubscribe a cancelled subscription
						// display basket and payment buttons or redirect for payment depending if multiple payment choices or intro text present:
						$plan				=	$this->base->_getReqParam( 'plan' );
						if ( ( ! $plan ) || ( ! isset( $subscriptionIds[$plan] ) ) || ( ! $subscriptionIds[$plan] ) ) {
							$subTxt			=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
							$return			.=	sprintf( CBPTXT::Th("Please press back button and select a %s plan."), $subTxt );
							break;
						}
						$plansMgr			=&	cbpaidPlansMgr::getInstance();
						$chosenPlans		=	array();
						$chosenPlans[(int) $plan]		=	$plansMgr->loadPlan( (int) $plan );

						$paidSomethingMgr	=&	cbpaidSomethingMgr::getInstance();
						$subscription		=	$paidSomethingMgr->loadSomething( $subscriptionIds[$plan][0], $subscriptionIds[$plan][1] );
						global $_PLUGINS;
						$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
						$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');
						$_PLUGINS->trigger( 'onCPayAfterPlanRenewalSelected', array( &$chosenPlans[(int) $plan], &$subscription, $act ) );
						if ( $_PLUGINS->is_errors() ) {
							$return			.=	$_PLUGINS->getErrorMSG();
							break;
						}

						$introText			=	CBPTXT::Th( $params->get( 'intro_text_renew', null ) );
						//TBD: check if already exists (reload protection):
						$paymentBasket		=	cbpaidControllerOrder::createSubscriptionsAndPayment( $user, $chosenPlans, $postdata, $subscriptionIds, null, null, CBPTXT::T("Renew"), 'R' );
						if ( is_object( $paymentBasket ) ) {
							$return			=	cbpaidControllerOrder::showBasketForPayment( $user, $paymentBasket, $introText );
						} else {
							$return			=	$paymentBasket;		// show messages as nothing to pay.
						}
						break;
					case 'unsubscribe':	// request to unsubscribe an active subscription
						// display unsubscribe confirmation form:
						$plan				=	$this->base->_getReqParam( 'plan' );
						if ( ( ! $plan ) || ( ! isset( $subscriptionIds[$plan] ) ) || ( ! $subscriptionIds[$plan] ) ) {
							$subTxt			=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
							$return			.=	sprintf( CBPTXT::Th("Please press back button and select a %s plan."), $subTxt );
							break;
						}
						$introText			=	CBPTXT::Th( $params->get( 'unsubscribe_intro_text' , null ) );
						$return				=	$subscriptionsGUI->showUnsubscribeForm( $user, $introText, (int) $plan, (int) $subscriptionIds[$plan][1] );

						break;
					case 'confirm_unsubscribe':	// confirm previous request to unsubscribe an active subscription
						// unsubscribe confirmed:
						$plan				=	$this->base->_getReqParam( 'plan' );
						if ( ( ! $plan ) || ( ! isset( $subscriptionIds[$plan] ) ) || ( ! $subscriptionIds[$plan] ) ) {
							$subTxt			=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
							$return			.=	sprintf( CBPTXT::Th("Please press back button and select a %s plan."), $subTxt );
							break;
						}
						if ( ( $plan ) && ( count( $subscriptionIds ) == 1 ) ) {
							$unsubscribeConfText =	CBPTXT::Th( $params->get( 'unsubscribe_confirmation_text', null ) );
							$return			=	cbpaidControllerOrder::doUnsubscribeConfirm( $user, $unsubscribeConfText, (int) $plan, (int) $subscriptionIds[$plan][1] );
						}
						break;
					case 'display_subscriptions':
						// unsubscribe cancelled: display subscriptions:
						$return				=	$this->base->displayUserTab( $user );
						break;
					case 'showinvoice':
						// shows a particular user invoice:
						if ( $params->get( 'show_invoices', 1 ) ) {
							$invoiceNo		=	$this->base->_getReqParam( 'invoice' );
							$return			=	$this->showInvoice( $invoiceNo, $user );
						}
						break;
					case 'saveeditinvoiceaddress':
					case 'editinvoiceaddress':		// this is the case of reload of invoicing address
						$invoicingAddressQuery		=	$params->get( 'invoicing_address_query' );
						if ( $invoicingAddressQuery > 0 ) {
							$basketId				=	$this->base->_getReqParam( 'basket', 0 );
							$hashToCheck			=	$this->base->_getReqParam( 'bck' );
							$paymentBasket			=	new cbpaidPaymentBasket( $_CB_database );
							if ( $basketId && $paymentBasket->load( (int) $basketId ) && ( $paymentBasket->payment_status == 'NotInitiated' ) && ( $hashToCheck == $paymentBasket->checkHashUser( $hashToCheck ) ) ) {
								if ( ( $act == 'saveeditinvoiceaddress' ) && $this->base->input( 'actbutton', null, GetterInterface::COMMAND ) ) {				// IE7-8 will return text instead of value and IE6 will return button all the time http://www.dev-archive.net/articles/forms/multiple-submit-buttons.html
									$return			=	$paymentBasket->saveInvoicingAddressForm( $user );
									if ( $return === null ) {
										$paymentBasket->storeInvoicingDefaultAddress();
										$introText	=	CBPTXT::Th( $params->get( 'intro_text', null ) );
										$return		.=	cbpaidControllerOrder::showBasketForPayment( $user, $paymentBasket, $introText );
									}
								} else {
									// invoice has reloaded itself (e.g. for country change):
									$return			=	$paymentBasket->renderInvoicingAddressForm( $user );
								}
							} else {
								$this->base->_setErrorMSG( CBPTXT::T("No unpaid payment basket found.") );
							}
						} else {
							$this->base->_setErrorMSG( CBPTXT::T("Not authorized action") );
						}

						break;
					case 'saverecordpayment':
					case 'editrecordpayment':		// this is the case of reload of the form
						$basketId				=	$this->base->_getReqParam( 'basket', 0 );
						$hashToCheck			=	$this->base->_getReqParam( 'bck' );
						$paymentBasket			=	new cbpaidPaymentBasket( $_CB_database );
						if ( $basketId && $paymentBasket->load( (int) $basketId ) && ( $paymentBasket->payment_status != 'Completed' ) && ( $hashToCheck == $paymentBasket->checkHashUser( $hashToCheck ) ) ) {
							if ( $paymentBasket->authoriseAction( 'cbsubs.recordpayments' ) ) {
								if ( ( $act == 'saverecordpayment' ) && $this->base->input( 'actbutton', null, GetterInterface::COMMAND ) ) {				// IE7-8 will return text instead of value and IE6 will return button all the time http://www.dev-archive.net/articles/forms/multiple-submit-buttons.html
									$return			=	cbpaidRecordBasketPayment::saveRecordPayment( $paymentBasket->id );
									if ( $return === null ) {
										$return		.=	CBPTXT::T("Payment recorded.")
											.	' <a href="' . $_CB_framework->userProfileUrl( $paymentBasket->user_id, true ) . '">'
											.	CBPTXT::Th("View user profile")
											.	'</a>';
									}
								} else {
									// invoice has reloaded itself (e.g. for country change):
									$return			=	cbpaidRecordBasketPayment::displayRecordPaymentForm( $paymentBasket->id );
								}
							} else {
								$this->base->_setErrorMSG( CBPTXT::T("Not authorized action") );
							}
						} else {
							$this->base->_setErrorMSG( CBPTXT::T("No unpaid payment basket found.") );
						}

						break;

					default:
						cbNotAuth();
						return '';
						break;
				}
			}

		} elseif ( $this->base->_getReqParam( 'account' ) && ( ( (int) cbGetParam( $_GET, 'user', 0 ) ) > 0 ) ) {

			$account					=	$this->base->_getReqParam( 'account' );
			$post_user_id				=	(int) cbGetParam( $_GET, 'user', 0 );
			$user						=	CBuser::getUserDataInstance( (int) $post_user_id );
			if ( $user->id ) {
				if ( isset( $_SESSION['cbsubs']['expireduser'] ) && ( $_SESSION['cbsubs']['expireduser'] == $user->id ) ) {
					// expired subscriptions of membership: show possibilities:
					$subscriptionsGUI		=	new cbpaidControllerUI();

					outputCbTemplate();
					$this->base->outputRegTemplate();
					outputCbJs();

					switch ( $account ) {
						case 'expired':
							$paidsubsManager		=&	cbpaidSubscriptionsMgr::getInstance();
							if ( ! $paidsubsManager->checkExpireMe( __FUNCTION__, $user->id, false ) ) {
								// no valid membership:
								$return				=	$subscriptionsGUI->getShowSubscriptionUpgrades( $user, true );
							}

							break;
						default:
							break;
					}
				} else {
					$return					=	CBPTXT::Th("Browser cookies must be enabled.");
				}
			}

		} elseif ( in_array( $act, array( 'setbsktpmtmeth', 'setbsktcurrency' ) ) ) {

			cbSpoofCheck( 'plugin' );		// anti-spoofing check
			$params							=	$this->params;
			outputCbTemplate();
			$this->base->outputRegTemplate();
			outputCbJs();

			$basketId				=	$this->base->_getReqParam( 'bskt', 0 );
			$hashToCheck			=	$this->base->_getReqParam( 'bck' );

			$paymentBasket			=	new cbpaidPaymentBasket( $_CB_database );
			if ( $basketId && $paymentBasket->load( (int) $basketId ) && ( $paymentBasket->payment_status == 'NotInitiated' ) && ( $hashToCheck == $paymentBasket->checkHashUser( $hashToCheck ) ) ) {

				switch ( $act ) {
					case 'setbsktpmtmeth':
						if ( $params->get( 'payment_method_selection_type' ) == 'radios' ) {
							$chosenPaymentMethod	=	cbGetParam( $_POST, 'payment_method' );
							$introText				=	CBPTXT::Th( $params->get( 'intro_text', null ) );
							$return					=	$paymentBasket->saveBasketPaymentMethodForm( $user, $introText, $chosenPaymentMethod );
							if ( $return === null ) {
								$return				.=	cbpaidControllerOrder::showBasketForPayment( $user, $paymentBasket, $introText );
							}
						} else {
							$this->base->_setErrorMSG( CBPTXT::T("Not authorized action") );
						}
						break;

					case 'setbsktcurrency':
						if ( $params->get( 'allow_select_currency', '0' ) ) {
							$newCurrency			=	cbGetParam( $_POST, 'currency' );
							if ( $newCurrency ) {
								if ( in_array( $newCurrency, cbpaidControllerPaychoices::getInstance()->getAllCurrencies() ) ) {
									$paymentBasket->changeCurrency( $newCurrency );
								} else {
									$this->base->_setErrorMSG( CBPTXT::T("This currency is not allowed") );
								}
								$introText			=	CBPTXT::Th( $params->get( 'intro_text', null ) );
								$return				.=	cbpaidControllerOrder::showBasketForPayment( $user, $paymentBasket, $introText );
							} else {
								$this->base->_setErrorMSG( CBPTXT::T("Not authorized action") );
							}
						} else {
							$this->base->_setErrorMSG( CBPTXT::T("Changes of currency of orders are not authorized") );
						}
						break;

					default:
						cbNotAuth();
						return '';
						break;
				}

			} else {
				$this->base->_setErrorMSG( CBPTXT::T("No unpaid payment basket found.") );
			}

		} elseif ( $act == 'cbsubsclass' ) {

			$pluginName						=	$this->base->_getReqParam( 'class' );
			if ( preg_match( '/^[a-z]+$/', $pluginName ) ) {
				$element					=	'cbsubs.' . $pluginName;
				global $_PLUGINS;
				$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin', $element );
				$loadedPlugins				=&	$_PLUGINS->getLoadedPluginGroup( 'user/plug_cbpaidsubscriptions/plugin' );
				$params						=	$this->params;
				foreach ($loadedPlugins as $p ) {
					if ( $p->element == $element ) {
						$pluginId			=	$p->id;
						$args				=	array( &$user, &$params, &$postdata );
						/** @noinspection PhpUndefinedCallbackInspection */
						$return				=	$_PLUGINS->call( $pluginId, 'executeTask', 'getcbsubs' . $pluginName . 'Tab', $args, null );
						break;
					}
				}
			}

		} elseif ( $act && ( ! in_array( $act, array( 'showbskt', 'setbsktpmtmeth' ) ) ) && ( ( (int) cbGetParam( $_GET, 'user', 0 ) ) > 0 ) ) {

			if ( ! is_object( $user ) ) {
				return CBPTXT::T("User does not exist.");
			}

			$params								=	$this->params;

			$post_user_id						=	(int) cbGetParam( $_GET, 'user', 0 );
			if ( $post_user_id && ( ( $user->id == $post_user_id ) || ( cbpaidApp::authoriseAction( 'cbsubs.usersubscriptionmanage' ) ) ) ) {

				outputCbTemplate();
				$this->base->outputRegTemplate();
				outputCbJs();

				switch ( $act ) {
					case 'showinvoice':
						if ( $params->get( 'show_invoices', 1 ) ) {
							$invoiceNo			=	$this->base->_getReqParam( 'invoice', 0 );
							// This also checks for cbpaidApp::authoriseAction on cbsubs.sales or cbsubs.financial access permissions:
							$return				=	$this->showInvoice( $invoiceNo, $user );
						} else {
							$this->base->_setErrorMSG( CBPTXT::T("Not authorized action") );
						}
						break;
					case 'showinvoiceslist':
						$showInvoices			=	$params->get( 'show_invoices', 1 );
						$invoicesShowPeriod		=	$params->get( 'invoices_show_period', '0000-06-00 00:00:00' );
						$itsmyself				=	( $_CB_framework->myId() == $user->id );
						if ( $showInvoices && ( $itsmyself || ( cbpaidApp::authoriseAction( 'cbsubs.sales' ) || cbpaidApp::authoriseAction( 'cbsubs.financial' ) ) ) ) {
							$subscriptionsGUI	=	new cbpaidControllerUI();
							$invoices			=	$this->_getInvoices( $user, $invoicesShowPeriod, false );

							if ( $invoicesShowPeriod && ( $invoicesShowPeriod != '0000-00-00 00:00:00' ) ) {
								$cbpaidTimes	=&	cbpaidTimes::getInstance();
								$periodText		=	$cbpaidTimes->renderPeriod( $invoicesShowPeriod, 1, false );
							} else {
								$periodText		=	'';
							}
							$return				.=	$subscriptionsGUI->showInvoicesList( $invoices, $user, $itsmyself, $periodText );
						} else {
							$this->base->_setErrorMSG( CBPTXT::T("Not authorized action") );
						}
						break;
					case 'editinvoiceaddress':			// this is the case of the initial edit address link
						if ( $params->get( 'invoicing_address_query' ) > 0 ) {
							$basketId			=	$this->base->_getReqParam( 'basket', 0 );
							$hashToCheck		=	$this->base->_getReqParam( 'bck' );
							$paymentBasket		=	new cbpaidPaymentBasket( $_CB_database );
							if ( $basketId && $paymentBasket->load( (int) $basketId ) && ( $paymentBasket->payment_status == 'NotInitiated' ) && ( $hashToCheck == $paymentBasket->checkHashUser( $hashToCheck ) ) ) {
								$return			=	$paymentBasket->renderInvoicingAddressForm( $user );
							} else {
								$this->base->_setErrorMSG( CBPTXT::T("No unpaid payment basket found.") );
							}
						} else {
							$this->base->_setErrorMSG( CBPTXT::T("Not authorized action") );
						}
						break;
					case 'showrecordpayment':
						$paymentBasketId		=	$this->base->_getReqParam( 'recordpayment', 0 );
						if ( $paymentBasketId ) {
							$paymentBasket		=	new cbpaidPaymentBasket();
							if ( $paymentBasket->load( (int) $paymentBasketId ) && $paymentBasket->authoriseAction( 'cbsubs.recordpayments' ) ) {
								// Auto-loads class: and authorization is checked inside:
								$return				=	cbpaidRecordBasketPayment::displayRecordPaymentForm( $paymentBasketId );
							} else {
								$this->base->_setErrorMSG( CBPTXT::T("Not authorized action") );
							}
						} else {
							$this->base->_setErrorMSG( CBPTXT::T("Not authorized action") );
						}
						break;
					default:
						$this->base->_setErrorMSG( CBPTXT::T("Not authorized action") );
						break;
				}
			}

		} elseif ( $act == 'showbskt' && ( ( ( (int) cbGetParam( $_GET, 'user', 0 ) ) > 0 ) ) || ( $this->base->_getReqParam( 'bskt', 0 ) && $this->base->_getReqParam( 'bck' ) ) ) {

			$basketId			=	$this->base->_getReqParam( 'bskt', 0 );
			$hashToCheck		=	$this->base->_getReqParam( 'bck' );

			// Basket integrations saving/editing url:
			if ( in_array($act, array( 'saveeditbasketintegration', 'editbasketintegration' ) ) ) {		// edit is the case of edit or reload of integration form
				$integration			=	$this->base->_getReqParam( 'integration' );
				$paymentBasket			=	new cbpaidPaymentBasket( $_CB_database );
				if ( preg_match( '/^[a-z]+$/', $integration ) && $basketId && $paymentBasket->load( (int) $basketId ) && ( $paymentBasket->payment_status == 'NotInitiated' ) && ( $hashToCheck == $paymentBasket->checkHashUser( $hashToCheck ) ) ) {
					global $_PLUGINS;
					$element			=	'cbsubs.' . $integration;
					$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin', $element );
					$results		=	$_PLUGINS->trigger( 'onCPayEditBasketIntegration', array( $integration, $act, &$paymentBasket ) );
					$return			=	null;
					foreach ( $results as $r ) {
						if ( $r ) {
							$return	.=	$r;
						}
					}
					if ( $act == 'editbasketintegration' ) {
						if ( $return !== null ) {
							return $return;
						}
					}
				} else {
					$this->base->_setErrorMSG( CBPTXT::T("No unpaid payment basket found.") );
				}
			}


			$post_user_id							=	(int) cbGetParam( $_GET, 'user', 0 );
			if ( $post_user_id && ! ( ( is_object( $user ) && ( $user->id == $post_user_id ) ) ) ) {
				return CBPTXT::T("User does not exist.");
			}

			outputCbTemplate();
			$this->base->outputRegTemplate();
			outputCbJs();
			$params				=	$this->params;

			$paymentBasket		=	new cbpaidPaymentBasket( $_CB_database );
			if ( $basketId && $paymentBasket->load( (int) $basketId ) && ( $paymentBasket->payment_status == 'NotInitiated' ) ) {
				if ( ! $post_user_id ) {
					$cbUser		=&	CBuser::getInstance( (int) $paymentBasket->user_id );
					$user		=&	$cbUser->getUserData();
					if ( ( ! is_object( $user ) ) || ! $user->id ) {
						return CBPTXT::T("User does not exist.");
					}
				}
				if ( ( $hashToCheck && $hashToCheck == $paymentBasket->checkHashUser( $hashToCheck ) )
					|| ( ( ! $hashToCheck ) && $paymentBasket->user_id && ( $paymentBasket->user_id == $_CB_framework->myId() ) ) )
				{
					$introText	=	CBPTXT::Th( $params->get( 'intro_text', null ) );
					$return		.=	cbpaidControllerOrder::showBasketForPayment( $user, $paymentBasket, $introText );
				} else {
					$this->base->_setErrorMSG( CBPTXT::T("Not authorized action") );
				}
			} else {
				$this->base->_setErrorMSG( CBPTXT::T("No unpaid payment basket found.") );
			}

			//	} elseif ( isset($_REQUEST['result']) && isset( $_REQUEST['user'] ) && ( $_REQUEST['user'] > 0 ) ) {
		} elseif ( isset($_REQUEST['result']) && ( $this->base->_getReqParam('method') || $this->base->_getReqParam('gacctno') ) ) {

			// don't check license here so initiated payments can complete !

			$params				=	$this->params;

			$method				=	$this->base->_getReqParam('method');

			if ( ( $method == 'freetrial' ) || ( $method == 'cancelpay' ) ) {
				cbpaidApp::import( 'processors.freetrial.freetrial' );
				cbpaidApp::import( 'processors.cancelpay.cancelpay' );
				$className		=	'cbpaidGatewayAccount' . $method;
				$payAccount		=	new $className( $_CB_database );
			} else {
				$gateAccount	=	$this->base->_getReqParam('gacctno');

				$payAccount		=	cbpaidControllerPaychoices::getInstance()->getPayAccount( $gateAccount );
				if ( ! $payAccount ) {
					return '';
				}
			}
			$payClass			=	$payAccount->getPayMean();
			$paymentBasket		=	new cbpaidPaymentBasket($_CB_database);

			if ( $payClass && ( ( $this->base->_getReqParam('method') == $payClass->getPayName() ) || ( $this->base->_getReqParam('method') == null ) ) && $payClass->hashPdtBackCheck( $this->base->_getReqParam('pdtback') ) ) {
				// output for resultNotification: $return and $allowHumanHtmlOutput
				$return			=	$payClass->resultNotification( $paymentBasket, $postdata, $allowHumanHtmlOutput );
			}

			if ( ! $paymentBasket->id ) {
				$this->base->_setErrorMSG(CBPTXT::T("No suitable basket found."));
			} else {
				$user			=&	CBuser::getUserDataInstance( (int) $paymentBasket->user_id );

				if ( $paymentBasket->payment_status == 'RegistrationCancelled' ) {
					// registration cancelled: delete payment basket and delete user after checking that he is not yet active:
					if ( $paymentBasket->load( (int) $paymentBasket->id ) ) {
						if ( $payClass->hashPdtBackCheck( $this->base->_getReqParam('pdtback') ) && ( ( $paymentBasket->payment_status == 'NotInitiated' ) || ( ( $paymentBasket->payment_status === 'Pending' ) && ( $paymentBasket->payment_method === 'offline' ) ) ) ) {

							$notification						=	new cbpaidPaymentNotification();
							$notification->initNotification( $payClass, 0, 'P', $paymentBasket->payment_status, $paymentBasket->payment_type, null, $_CB_framework->now(), $paymentBasket->charset );

							$payClass->updatePaymentStatus( $paymentBasket, 'web_accept', 'RegistrationCancelled', $notification, 0, 0, 0, true );

							// This is a notification or a return to site after payment, we want to log any error happening in third-party stuff in case:
							cbpaidErrorHandler::keepTurnedOn();
						}
					}
				}
				if ( $allowHumanHtmlOutput ) {
					// If frontend, we display result, otherwise, If Server-to-server notification: do not display any additional text here !
					switch ( $paymentBasket->payment_status ) {
						case 'Completed':
							// PayPal recommends including the following information with the confirmation:
							// - Item name
							// - Amount paid
							// - Payer email
							// - Shipping address
							$newMsg = sprintf( CBPTXT::Th("Thank you for your payment of %s for the %s %s."), $paymentBasket->renderPrice(),
								$paymentBasket->item_name,
								htmlspecialchars( $payClass->getTxtUsingAccount( $paymentBasket ) ) )		// ' using your paypal account ' . $paymentBasket->payer_email
								. ' ' . $payClass->getTxtNextStep( $paymentBasket );
							// . "Your transaction has been completed, and a receipt for your purchase has been emailed to you by PayPal. "
							// . "You may log into your account at www.paypal.com to view details of this transaction.</p>\n";
							if ( $params->get( 'show_invoices' ) ) {
								$itsmyself			=	( $_CB_framework->myId() == $user->id );
								$subscriptionsGUI	=	new cbpaidControllerUI();
								$newMsg				.=	'<p id="cbregviewinvoicelink">'
									.	$subscriptionsGUI->getInvoiceShowAhtml( $paymentBasket, $user, $itsmyself, CBPTXT::Th("View printable invoice") )
									.	'</p>'
								;
							}
							$paid = true;
							break;
						case 'Pending':
							$newMsg = sprintf( CBPTXT::Th("Thank you for initiating the payment of %s for the %s %s."), $paymentBasket->renderPrice(),
								$paymentBasket->item_name,
								htmlspecialchars( $payClass->getTxtUsingAccount( $paymentBasket ) ) )		// ' using your paypal account ' . $paymentBasket->payer_email
								. ' ' . $payClass->getTxtNextStep( $paymentBasket );
							// . "Your payment is currently being processed. "
							// . "A receipt for your purchase will be emailed to you by PayPal once processing is complete. "
							// . "You may log into your account at www.paypal.com to view status details of this transaction.</p>\n";
							break;
						case 'RegistrationCancelled':
							$newMsg		=	$payClass->getTxtNextStep( $paymentBasket );
							break;
						case 'FreeTrial':
							$newMsg = CBPTXT::Th("Thank you for subscribing to") . ' ' . $paymentBasket->item_name . '.'
								. ' ' . $payClass->getTxtNextStep( $paymentBasket );
							break;
						case null:
							$newMsg	= CBPTXT::T("Payment basket does not exist.");
							break;
						case 'NotInitiated':
							$newMsg	=	'';
							break;
						case 'RedisplayOriginalBasket':
							if ( $paymentBasket->load( (int) $paymentBasket->id ) && ( $paymentBasket->payment_status == 'NotInitiated' ) ) {
								$introText		=	CBPTXT::Th( $params->get( 'intro_text', null ) );
								$return			.=	cbpaidControllerOrder::showBasketForPayment( $user, $paymentBasket, $introText );
							}
							$newMsg				=	'';
							break;
						case 'Processed':
						case 'Denied':
						case 'Reversed':
						case 'Refunded':
						case 'Partially-Refunded':
						default:
							$newMsg = $payClass->getTxtNextStep( $paymentBasket );
							// "<p>Your transaction is not cleared and has currently following status: <strong>" . $paymentBasket->payment_status . ".</strong></p>"
							// . "<p>You may log into your account at www.paypal.com to view status details of this transaction.</p>";
							break;
					}

					if ( in_array( $paymentBasket->payment_status, array( 'Completed', 'Pending' ) ) ) {
						$subscriptions = $paymentBasket->getSubscriptions();
						$texts		=	array();			// avoid repeating several times identical texts:
						if ( is_array( $subscriptions ) ) {
							foreach ( $subscriptions as $sub ) {
								/** @var $sub cbpaidSomething */
								$thankYouParam		=	( $paymentBasket->payment_status == 'Completed') ? 'thankyoutextcompleted' : 'thankyoutextpending';
								$thankYouText		=	$sub->getPersonalized( $thankYouParam, true );
								if ( $thankYouText && ! in_array( $thankYouText, $texts ) ) {
									$texts[]		=	$thankYouText;
									if ( strpos( $thankYouText, '<' ) === false ) {
										$msgTag		=	'p';
									} else {
										$msgTag		=	'div';
									}
									$newMsg			.=	'<' . $msgTag . ' class="cbregThanks" id="cbregThanks' . $sub->plan_id . '">' . $thankYouText . '</' . $msgTag . ">\n";
								}
							}
						}
					}
					if ( $newMsg ) {
						$return .= '<div>' . $newMsg . '</div>';
					}

					if ( $paid && ( $_CB_framework->myId() < 1 ) && ( cbGetParam( $_REQUEST, 'user', 0 ) == $paymentBasket->user_id ) ) {
						$_CB_database->setQuery( "SELECT * FROM #__comprofiler c, #__users u WHERE c.id=u.id AND c.id=".(int) $paymentBasket->user_id );
						if ( $_CB_database->loadObject( $user ) && ( $user->lastvisitDate == '0000-00-00 00:00:00' ) ) {
							$return = '<p>' . implode( '', getActivationMessage( $user, 'UserRegistration' ) ) . '</p>' . $return;
						}
					}
				}
			}

		} else {
			cbNotAuth();
			return ' ' . CBPTXT::T("No result.");
		}

		if ( $allowHumanHtmlOutput ) {
			$allErrorMsgs	=	$this->base->getErrorMSG( '</div><div class="error">' );
			if ( $allErrorMsgs ) {
				$errorMsg	=	'<div class="error">' . $allErrorMsgs . '</div>';
			} else {
				$errorMsg	=	null;
			}

			/** @var string $return */
			if ( ( $return == '' ) && ( $errorMsg ) ) {
				$this->base->outputRegTemplate();
				$return		=	$errorMsg . '<br /><br />' . $return;
				$return		.=	cbpaidControllerOrder::showBasketForPayment( $user, $paymentBasket, '' );
			} else {
				$return		=	$errorMsg . $return;
			}
		}

		if ( ! is_null( $oldignoreuserabort ) ) {
			ignore_user_abort($oldignoreuserabort);
		}

		return $return;
	}
}
