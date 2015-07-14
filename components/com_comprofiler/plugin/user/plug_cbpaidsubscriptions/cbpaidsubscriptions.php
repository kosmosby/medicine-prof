<?php
/**
* @version $Id: cbpaidsubscriptions.php 1589 2012-12-26 17:37:52Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\ListTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

$memMax				=	trim( @ini_get( 'memory_limit' ) );
if ( $memMax ) {
	$last			=	strtolower( $memMax{strlen( $memMax ) - 1} );
	switch( $last ) {
		case 'g':
			$memMax	*=	1024 * 1024 * 1024;
			break;
		case 'm':
			$memMax	*=	1024 * 1024;
			break;
		case 'k':
			$memMax	*=	1024;
			break;
	}
	if ( $memMax < 48000000 ) {
		@ini_set( 'memory_limit', '48M' );
	}
}

global $_CB_framework;
if ( $_CB_framework->getCfg( 'debug' ) ) {
	ini_set('display_errors',true);
	error_reporting(E_ALL);
}

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onBeforeUserRegistration',		'onBeforeUserRegistration',			'getcbpaidsubscriptionsTab' );
$_PLUGINS->registerFunction( 'onAfterUserRegistration',			'onAfterUserRegistration',			'getcbpaidsubscriptionsTab' );
$_PLUGINS->registerFunction( 'onAfterUserRegistrationMailsSent','onAfterUserRegistrationMailsSent',	'getcbpaidsubscriptionsTab' );
$_PLUGINS->registerFunction( 'onBeforeUserActive', 				'onBeforeUserActive',				'getcbpaidsubscriptionsTab' );
$_PLUGINS->registerFunction( 'onAfterNewUser', 					'onAfterNewUser',					'getcbpaidsubscriptionsTab' );
$_PLUGINS->registerFunction( 'onDuringLogin', 					'onDuringLogin',					'getcbpaidsubscriptionsTab' );
$_PLUGINS->registerFunction( 'onBeforeDeleteUser',				'onBeforeDeleteUser',				'getcbpaidsubscriptionsTab' );
$_PLUGINS->registerFunction( 'onAfterDeleteUser',				'onAfterDeleteUser',				'getcbpaidsubscriptionsTab' );
$_PLUGINS->registerFunction( 'onStartUsersList',				'onStartUsersList',					'getcbpaidsubscriptionsTab' );
$_PLUGINS->registerFunction( 'onAfterFieldsFetch',				'onAfterFieldsFetch',				'getcbpaidsubscriptionsTab' );		// for required fields hiding at registration
$_PLUGINS->registerFunction( 'onCBSubsCheckExpireMe',			'onCBSubsCheckExpireMe',			'getcbpaidsubscriptionsTab' );


/** @noinspection PhpIncludeInspection */
include_once( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/cbpaidsubscriptions.class.php');
if ( $_CB_framework->getUi() == 2 ) {
	// backend
	/** @noinspection PhpIncludeInspection */
	include_once( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/admin.cbpaidsubscriptions.php');
	cbimport( 'cb.params' );
}

cbpaidErrorHandler::install();
cbpaidErrorHandler::on();

/**
* Paid Subscriptions Tab Class for handling the CB * tab * api
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @author Beat
*/
class getcbpaidsubscriptionsTab extends cbpaidApp {	//	extends cbTabHandler
	/** @var array of cbpaidProduct  intermediate storage for between onBeforeUserRegistration and onBeforeUserActive */
	protected $chosenPlans		=	null;
	/** @var cbpaidPaymentBasket     intermediate storage for between onBeforeUserRegistration and onBeforeUserActive */
	protected $paymentBasket	=	null;

	/**
	 * Constructor
	 */
	public function __construct( )
	{
		if ( is_callable( array( 'cbpaidApp', 'getBaseClass' ) ) ) {
			// this 'if' above is added for compatibility during update over CBSubs 1.0.x
			cbpaidApp::getBaseClass( $this );
			parent::__construct();
			cbpaidApp::loadLang();
		} else {
			// old method for CBSubs 1.0.3 classes during upgrade:
			parent::__construct();
			/** @noinspection PhpUndefinedCallbackInspection */
			call_user_func_array( array( $this, '_loadLang' ), array() );	// phplint-safe: $this->_loadLang();
		}
	}

/*
	public function editPluginView( &$row, $option, $task, $uid, $action, &$element, $mode, &$pluginParams ) {
		// CB 1.2.3+ method: (needs to remove from the XML file the class at top)
		global $_CB_database;

		$adminObj	=	new cbpaidAdminView( $_CB_database );
		return $adminObj->_editPluginView( $row, $option, $task, $uid, $action, $element, $mode, $pluginParams );
	}
*/
	/**
	 * Posts a POST form by https if available, otherwise by http and gets result.
	 *
	 *
	 * @deprecated since CBSubs 2.1 (Kept for backwards compatibility during CBSubs 2.x only)
	 *
	 * @param  string  $urlNoHttpsPrefix  URL without https:// in front (but works also with http:// or https:// in front, but it's ignored.
	 * @param  array|string  $formvars          Variables in form to post
	 * @param  int     $timeout           Timeout of the access
	 * @param  string  $result            RETURNING: the fetched access
	 * @param  int     $status            RETURNING: the status of the access (e.g. 200 is normal)
	 * @param  string  $getPostType       'post' (default) or 'get'
	 * @param  string  $contentType       'normal' (default) or 'xml' ($formvars['xml']=xml content) or 'json' (application/json)
	 * @param  string  $acceptType        '* / *' (default) or 'application/xml' or 'application/json'
	 * @param  boolean $https             SSL protocol (default: true)
	 * @param  int     $port              port number (default: 443)
	 * @param  string  $username          HTTP username authentication
	 * @param  string  $password          HTTP password authentication
	 * @param  boolean $allowHttpFallback Allow fallback to http if https not available locally (default: false)
	 * @param  string  $referer           referrer
	 * @return int     $error             error-code of access (0 for ok)
	 */
	public function _httpsRequest( $urlNoHttpsPrefix, $formvars, $timeout, &$result, &$status, $getPostType = 'post', $contentType='normal', $acceptType='*/*', $https = true, $port = 443, $username = '', $password = '', $allowHttpFallback = false, $referer = null ) {
		return cbpaidWebservices::httpsRequest( $urlNoHttpsPrefix, $formvars, $timeout, $result, $status, $getPostType, $contentType, $acceptType, $https, $port, $username, $password, $allowHttpFallback, $referer );
	}

	/**
	 * Returns a protected user-specific edit invoicing address URL
	 * e.g.
	 * http://site/component/option,com_comprofiler/task,tabclass/user,37612/tab,getcbpaidsubscriptionstab/cbpaidsubscriptionsact,editinvoiceaddress/cbpaidsubscriptionsbasket,425/cbpaidsubscriptionsbck,cbm_07e2fc60_448eb84d_7ef5ad8dee839e51b4c6dfd802ad470f/
	 *
	 * @param  cbpaidPaymentBasket        $paymentBasket
	 * @return string
	 */
	public function getInvoicingAddressEditUrl( $paymentBasket ) {
		$checkHash				=	$paymentBasket->checkHashUser();
		$basegetarray			=	array( 'user' => $paymentBasket->user_id, 'Itemid' => getCBprofileItemid( 0 ), 'act' => 'editinvoiceaddress', 'basket' => $paymentBasket->id, 'bck' => $checkHash );
		return $this->getHttpsAbsURLwithParam( $basegetarray, 'pluginclass', true );
	}
	/**
	 * Returns a protected user-specific invoice display address URL
	 *
	 * @param  cbpaidPaymentBasket        $invoice
	 * @return string
	 */
	public function getInvoiceUrl( $invoice  ) {
		return $this->getSecuredBasketShowUrl( $invoice, 'invoice', 'component' );
	}
	/**
	 * Returns a protected user-specific invoice display address URL
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return string
	 */
	public function getRecordPaymentUrl( $paymentBasket  ) {
		if( $paymentBasket->authoriseAction( 'cbsubs.recordpayments' ) ) {
			return $this->getSecuredBasketShowUrl( $paymentBasket, 'recordpayment', 'html' );
		} else {
			return null;
		}
	}
	/**
	 * Returns a protected user-specific invoice display address URL
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  string               $task           'invoice' or 'recordpayment'
	 * @param  string               $format         'html', 'component'
	 * @return string
	 */
	protected function getSecuredBasketShowUrl( $paymentBasket, $task, $format ) {
		global $_CB_framework;

		$basegetarray			=	array( 'user' => $paymentBasket->user_id, 'Itemid' => 0, 'act' => 'show' . $task, $task => $paymentBasket->id );
		if ( ! $_CB_framework->MyId() ) {
			$basegetarray['invoicecheck']	=	$paymentBasket->checkHashInvoice();
		}
		return $this->getHttpsAbsURLwithParam( $basegetarray, 'pluginclass', true, null, $format );
	}
	/**
	 * Displays user subscription tab in HTML, doing normal checks and expiries on the way
	 * 
	 * @param  UserTable  $user
	 * @param  string     $htmlTabDescription
	 * @return string
	 */
	public function displayUserTab( $user, $htmlTabDescription = null ) {

		$paidsubsManager		=&	cbpaidSubscriptionsMgr::getInstance();
		$paidsubsManager->checkExpireMe( __FUNCTION__ );

		$tabController			=	new cbpaidControllerCBTab();
		return $tabController->displaySubscriptionsAndInvoicesLink( $user, $htmlTabDescription );
	}

	/**
	 * Standard CB methods and HTML formatting helpers:
	 */
	/**
	 * Generates the HTML to display the user profile tab
	 *
	 * @param  TabTable        $tab       the tab database entry
	 * @param  UserTable       $user      the user being displayed
	 * @param  int             $ui        1 for front-end, 2 for back-end
	 * @return string|boolean             Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getDisplayTab( $tab, $user, $ui ) {
		cbpaidErrorHandler::on();

		$return						=	null;
		if ( $this->params->get( 'display_subscriptions_on_user_profile', 1 ) ) {
			$htmlTabDescription		=	( $tab ? $this->_writeTabDescription( $tab, $user ) : null );
			$return					=	$this->displayUserTab( $user, $htmlTabDescription );
		}
		cbpaidErrorHandler::off();
		return $return;
	}

	/**
	 * Generates the HTML to display the user edit tab
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @return mixed                 either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getEditTab( $tab, $user, $ui ) {
		global $ueConfig, $_CB_framework;

		$return		=	'';
		if ( ( $ui != 2 ) /* || ( $_CB_framework->myId() == $user->id ) */ ) {
			return $return;
		}

		cbpaidErrorHandler::on();

		if ( cbpaidApp::authoriseAction( 'cbsubs.usersubscriptionview' ) ) {


			$params					=	$this->params;
			
			$paidsubsManager		=&	cbpaidSubscriptionsMgr::getInstance();
			$paidsubsManager->checkExpireMe( __FUNCTION__ );
	
			if ( $user->id ) {
				$basketsMgr			=&	cbpaidOrdersMgr::getInstance();
				$basketsMgr->timeoutUnusedBaskets( $user->id );
			}
	
			$title					=	CBPTXT::Th( $params->get( 'profileTitle', "Your subscriptions" ));
			if ( $title ) {
				$name				=	getNameFormat( $user->name, $user->username, $ueConfig['name_format'] );
				$return				.=	'<div class="contentheading" id="cbregProfileTitle">' . sprintf( $title, $name ) . "</div>\n";
			}
	
			$return					.=	$this->_writeTabDescription( $tab, $user );
	
			$itsmyself				=	( $_CB_framework->myId() == $user->id );

			$subscriptionsGUI		=	new cbpaidControllerUI();
			$return					.=	$subscriptionsGUI->getShowSubscriptionUpgrades( $user, $itsmyself );

		}

		cbpaidErrorHandler::off();
		return $return;

/*
		global $_CB_framework;
		
		$params = $this->params;
		$exampleText		= $params->get('exampletext', 'Text Parameter not set!');

		$xmlfile = $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/cbpaidsubscriptions.xml';
		$this->userParams = new Registry( $user->cb_subs_params, $xmlfile, $maintagname='cbinstall', $attrname='type', $attrvalue='plugin', $paramsnode='params' );

		$ret = $this->userParams->render( $pluginId=null, $tabId=null, $tag_name='userparams',$attr='class',$attrvalue='getcbpaidsubscriptionsTab', $control_name='subscriptionparams', $paramstextarea=false );
		
		return $ret;
*/
	}

	/**
	 * Saves the user edit tab postdata into the tab's permanent storage
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @param  array      $postdata  _POST data for saving edited tab content as generated with getEditTab
	 * @return string|boolean        Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function saveEditTab( $tab, &$user, $ui, $postdata ) {
		global $_CB_framework, $_PLUGINS;
		// var_export( $_POST ); exit;
		if ( $ui != 2 ) {
			return null;
		}
		if(intval( $_CB_framework->myId() ) < 1) {
			cbNotAuth();
			return null;
		}
		cbpaidErrorHandler::on();

		if ( cbpaidApp::authoriseAction( 'cbsubs.usersubscriptionview' ) && cbpaidApp::authoriseAction( 'cbsubs.usersubscriptionmanage' ) ) {


			$now				=	$_CB_framework->now();
	/*
			$params = cbGetParam( $_REQUEST, 'subscriptionparams', '' );
			$cb_subs_params = Registry::getRawParamsUnescaped( $params, false );
			$user->cb_subs_params = $cb_subs_params;
	*/
			$subscriptionsGUI	=	new cbpaidControllerUI();
			if ( ( $ui == 2 ) && ( $user->id == 0 ) ) {
				$chosenPlans	=	$subscriptionsGUI->getAndCheckChosenRegistrationPlans( $postdata, $user );
			} else {
				$chosenPlans	=	$subscriptionsGUI->getAndCheckChosenUpgradePlans( $postdata, $user, $now );
			}
			if ( ! is_array( $chosenPlans ) ) {
				$_PLUGINS->_setErrorMSG( $chosenPlans );
				$_PLUGINS->raiseError( 1 );
				return false;
			}
			// Renew / Pay / Reactivate buttons -> checkboxes: subscriptionpay or subscriptionrenew or subscriptionunsubscribe: value is subscription_id.
			$unsubscribe		=	$subscriptionsGUI->getEditPostedBoxes( 'unsubscribe' );
			$renew				=	$subscriptionsGUI->getEditPostedBoxes( 'renew' );
			$reactivate			=	$subscriptionsGUI->getEditPostedBoxes( 'reactivate' );
			$resubscribe		=	$subscriptionsGUI->getEditPostedBoxes( 'resubscribe' );
			$pay				=	$subscriptionsGUI->getEditPostedBoxes( 'pay' );
			$delete				=	$subscriptionsGUI->getEditPostedBoxes( 'delete' );
			// Plan upgrade possibilities: values: subscription_id or null, act='upgrade', $plan= plan number or 0
	
			$act				=	$this->_getReqParam( 'act' );
			$subscriptionIds	=	$subscriptionsGUI->getEditPostedBoxes( 'id' );
	
			$paidSomethinMgr	=&	cbpaidSomethingMgr::getInstance();
	
			foreach ( $unsubscribe as $ps ) {
				// only unsubscribe (and maybe upgrade, means resubscribe):
				$subscription	=	$paidSomethinMgr->loadSomething( $ps[0], $ps[1] );
				if ( $subscription ) {
					// $subscription->deactivate( $user, 'C' );		// cancelled
					$resultErrMessage	=	cbpaidControllerOrder::doUnsubscribeConfirm( $user, null, $subscription->plan_id, $subscription->id );
					if ( $resultErrMessage === false ) {
						$_PLUGINS->_setErrorMSG( $this->getErrorMSG() );
						$_PLUGINS->raiseError(1);
						return false;
					}
				}
			}
	
			foreach ( $renew as $ps ) {
				// only renew:
				$subscription	=	$paidSomethinMgr->loadSomething( $ps[0], $ps[1] );
				if ( $subscription ) {
					$subscription->activate( $user, $now, true, 'R' );
				}
			}
			foreach ( $reactivate as $ps ) {
				// only renew:
				$subscription	=	$paidSomethinMgr->loadSomething( $ps[0], $ps[1] );
				if ( $subscription ) {
					$subscription->activate( $user, $now, true, 'R' );
				}
			}
			foreach ( $resubscribe as $ps ) {
				// only renew:
				$subscription	=	$paidSomethinMgr->loadSomething( $ps[0], $ps[1] );
				if ( $subscription ) {
					$subscription->activate( $user, $now, true, 'R' );
				}
			}
	
			foreach ( $pay as $ps ) {
				// only renew:
				$subscription	=	$paidSomethinMgr->loadSomething( $ps[0], $ps[1] );
				if ( $subscription ) {
					$subscription->activate( $user, $now, true, 'N' );
				}
			}
	
			foreach ( $delete as $ps ) {
				// only renew:
				$subscription	=	$paidSomethinMgr->loadSomething( $ps[0], $ps[1] );
				if ( $subscription ) {
					$newPlanId		= null;
					$unifiedStatus	= 'Denied';
					if ( $subscription->canDelete() ) {
						$subscription->revert( $user, $newPlanId, $unifiedStatus );
						$subscription->historySetMessage( 'Subscription deleted from administration backend' );
						if ( ! $subscription->delete( ) ) {
							$_PLUGINS->_setErrorMSG( $subscription->getError() );
							$_PLUGINS->raiseError( 1 );
							return false;
						}
					} else {
						$_PLUGINS->_setErrorMSG( $subscription->getError() );
						$_PLUGINS->raiseError( 1 );
						return false;
					}
				}
			}
	
			if ( $chosenPlans && ( count( $chosenPlans ) > 0 ) ) {
				if ( $act == 'upgrade' ) {
					if ( $user->id ) {
						// upgrade existing ones:
						cbpaidControllerOrder::createSubscriptionsAndPayment( $user, $chosenPlans, $postdata, $subscriptionIds, null, 'I', null, 'U', 'free' );
					} else {
						// when creating a user in backend, there is no user->id yet, so we need to defer the call (just below):
						global $_CBSUBS_temp_backend_create;
						$_CBSUBS_temp_backend_create	=	array( array( 'cbpaidControllerOrder', 'createSubscriptionsAndPayment' ), array( &$user, $chosenPlans, $postdata, $subscriptionIds, null, 'I', null, 'U', 'free' ));
					}
				}
			}

		}

		cbpaidErrorHandler::off();
		return null;
	}
	/**
	 * Called after successful user creation in backend
	 *
	 * @param  UserTable  $user
	 */
	public function onAfterNewUser( /** @noinspection PhpUnusedParameterInspection */ $user ) {
		global $_CBSUBS_temp_backend_create;

		if ( $_CBSUBS_temp_backend_create ) {
			call_user_func_array( $_CBSUBS_temp_backend_create[0], $_CBSUBS_temp_backend_create[1] );
		}
	}

	/**
	 * Generates the HTML to display the registration tab/area
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @param  array      $postdata  _POST data for saving edited tab content as generated with getEditTab
	 * @return string|boolean        Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getDisplayRegistration( $tab, $user, $ui, $postdata ) {
		cbpaidErrorHandler::on();
		$return							=	'';


		$params							=	$this->params;
		$registrationPlansEnabled		=	$params->get('registrationPlansEnabled', 0);

		if ($registrationPlansEnabled) {
			$this->outputRegTemplate();

			$subscriptionsGUI			=	new cbpaidControllerUI();
			$plansTitle					=	CBPTXT::T( $this->params->get( 'regTitle', "Subscriptions" ) );
			
			$htmlPlans					=	$subscriptionsGUI->getShowRegistrationPlans( $user, $plansTitle, 'N' );
			/** @var $viewer cbpaiduserregistrationplansView */
			$viewer						=	cbpaidTemplateHandler::getViewer( null, 'userregistrationplans' );
			$viewer->setModel( $tab );
			$return						=	$viewer->drawRegistrationPlans( $plansTitle, $htmlPlans );

		}
		cbpaidErrorHandler::off();
		return $return;
	}

	/**
	 * Saves the registration tab/area postdata into the tab's permanent storage
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @param  array      $postdata  _POST data for saving edited tab content as generated with getEditTab
	 * @return string|boolean        Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function saveRegistrationTab( $tab, &$user, $ui, $postdata ) {
	}
	/**
	 * Checks and binds the plans choosen at registration
	 * 
	 * @param  UserTable     $user        reflecting the user being registered
	 * @return cbpaidProduct[]|string
	 */
	protected function & _getRegistrationChoosenPlans( &$user ) {
		global $_POST;

		static $firstCall							=	true;
		static $chosenPlans							=	null;
		if ( $firstCall ) {
			$firstCall								=	false;
			$params									=	$this->params;
			$registrationPlansEnabled				=	$params->get( 'registrationPlansEnabled', 0 );
			if ( $registrationPlansEnabled ) {
				$subscriptionsGUI					=	new cbpaidControllerUI();
				$chosenPlans						=	$subscriptionsGUI->getAndCheckChosenRegistrationPlans( $_POST, $user );
			}
		}
		return $chosenPlans;
	}
	/**
	* Saves the registration tab/area postdata into the tab's permanent storage
	*
	* @param  UserTable  $row        reflecting the user being registered
	* @param  UserTable  $rowExtras  old duplicate
	* @return boolean                true if ok, false if ErrorMSG generated
	*/
	public function onBeforeUserRegistration( &$row, &$rowExtras ) {
		global $_CB_database, $_POST, $_PLUGINS, $ueConfig;
		cbpaidErrorHandler::on();

		$params									=	$this->params;
		$registrationPlansEnabled				=	$params->get( 'registrationPlansEnabled', 0 );
		$enableFreeRegisteredUser				=	$params->get( 'enableFreeRegisteredUser', 1 );
		
		$result = true;
		if ( $registrationPlansEnabled ) {
			$chosenPlans						=	$this->_getRegistrationChoosenPlans( $row );		// keep chosen plans including _options for onAfterUserRegistration
			if ( is_array( $chosenPlans ) ) {									// no more exclusive-only plans: && ( count( $chosenPlans ) > 0 ) ) {
				if ( ( count( $chosenPlans ) > 0 ) || ( $enableFreeRegisteredUser ) ) {
					$approvedOverride			=	null;
					$confirmedOverride			=	null;
					$free						=	false;
					$this->_checkRegistrationOverridesFree( $chosenPlans, $free, $approvedOverride, $confirmedOverride );
					if ( $approvedOverride !== null ) {
						$rowExtras->approved	=	$approvedOverride;
						$ueConfig['reg_admin_approval']	=	( $approvedOverride ? '0' : '1' );
					}
					if ( $confirmedOverride !== null ) {
						$rowExtras->confirmed	=	$confirmedOverride;
						$ueConfig['reg_confirmation']		=	( $confirmedOverride ? '0' : '1' );
					}
					if ( ! $free ) {
						// non-free plan: force workflow to auto-confirm + auto-approve, but block until payment completed:
						$row->block				=	( ( $enableFreeRegisteredUser && $rowExtras->approved && $rowExtras->confirmed ) ? '0' : '1' );
					}
				} else {
					$errorMsg					=	CBPTXT::T("Registration is enabled, free registrations are not allowed, but no subscription plan is available for registration");
					$_PLUGINS->raiseError(0);
					$_PLUGINS->_setErrorMSG( sprintf( CBPTXT::T("Sorry, %s. Please contact site administrator."), $errorMsg ) );
					$log						=	new cbpaidHistory( $_CB_database );
					$null						=	null;
					$log->logError( 3, CBPTXT::T("User error") .' : ' . CBPTXT::T("Configuration does not make sense") . ': ' . $errorMsg, $null );
				}
			} else {
				$_PLUGINS->raiseError(0);
				if ( is_array( $chosenPlans ) ) {
					$subTxt						=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
					$_PLUGINS->_setErrorMSG( sprintf( CBPTXT::T("Please choose your %s plan."), $subTxt ) );
				} else {
					$_PLUGINS->_setErrorMSG( $chosenPlans );
				}
				$result  = false;
			}
		}
		cbpaidErrorHandler::off();
		return $result;
	}
	/**
	* Saves the registration tab/area postdata into the tab's permanent storage
	*
	* @param  UserTable  $user       the user being registered
	* @param  UserTable  $rowExtras  (depreciated extra-fields)
	* @param  boolean    $bool       true
	* @return string                 text to display or NULL
	*/
	public function onAfterUserRegistration( &$user, /** @noinspection PhpUnusedParameterInspection */ &$rowExtras, /** @noinspection PhpUnusedParameterInspection */ $bool ) {
		cbpaidErrorHandler::on();
		$params						=	$this->params;
		$registrationPlansEnabled	=	$params->get( 'registrationPlansEnabled', 0 );
		$enableFreeRegisteredUser	=	$params->get( 'enableFreeRegisteredUser', 1 );

		if ( $registrationPlansEnabled ) {
			// as saved in onBeforeUserRegistration, now that we have userid:
			$chosenPlans			=	$this->_getRegistrationChoosenPlans( $user );
			if ( is_array( $chosenPlans ) && ( count( $chosenPlans ) > 0 ) ) {
				$this->paymentBasket	=	cbpaidControllerOrder::createSubscriptionsAndPayment( $user, $chosenPlans, $_POST, null, null, 'R', null, 'N', 'now' );
			} elseif ( ! $enableFreeRegisteredUser ) {
				trigger_error( 'cbpaid:onAfterUserRegistration: No free plan but no plan chosen !', E_USER_ERROR );
			}
		}
		cbpaidErrorHandler::off();
		return null;
	}
	/**
	 * Checks for the overrides with the chosen plans
	 *
	 * @param  cbpaidProduct[]  $chosenPlans        array of cbpaidProduct chosen
	 * @param  boolean          $free               RETURNED: true if all plans are free or no plans exist
	 * @param  int|null         $approvedOverride   RETURNED: NULL if no overrides, or value for approved at registration
	 * @param  int|null         $confirmedOverride  RETURNED: NULL if no overrides, or value for confirmed at registration
	 */
	protected function _checkRegistrationOverridesFree( &$chosenPlans, &$free, &$approvedOverride, &$confirmedOverride ) {
		$approvedOverride				=	null;
		$confirmedOverride				=	null;
		$free							=	true;
		foreach ( $chosenPlans as $plan ) {
			if ( in_array( $plan->get( 'approved' ),  array( '0', '1', '3' ) ) && ( ( $approvedOverride  & 1 ) !== 1 ) ) {
				$approvedOverride		= ( ( (int) $plan->get( 'approved'  ) ) & 1 ) ? 0 : 1;		// change only if not default = 2 and still approved or unchanged
			}
			if ( in_array( $plan->get( 'confirmed' ), array( '0', '1', '3' ) ) && ( ( $confirmedOverride & 1 ) !== 1 ) ) {
				$confirmedOverride		= ( ( (int) $plan->get( 'confirmed' ) ) & 1 ) ? 0 : 1;
			}
			if ( $plan->get( $plan->getPlanVarName( 'N', 1, 'rate' ) ) != 0 ) {
				$free					=	false;
			}
		}
	}
	/**
	* CB user activation interception
	*
	* @param  UserTable  $user          + moscomprofile the user being displayed
	* @param  int        $ui            1 for front-end, 2 for back-end
	* @param  string     $cause         (one of: 'UserRegistration', 'UserConfirmation', 'UserApproval', 'NewUser', 'UpdateUser')
	* @param  boolean    $mailToAdmins  true if the standard new-user email should be sent to admins if moderator emails are enabled
	* @param  boolean    $mailToUser    true if the welcome new user email (from CB config) should be sent to the new user
	* @return array                     with keys (or null if not intercepting/changing behavior):
	* 		boolean ['activate']          false if should not activate user 
	* 		boolean ['mailToAdmins']      false if should not send the CB system email to admin, true otherwise
	* 		boolean ['mailToUser']        false if should not send the CB welcome email to new user, true otherwise
	* 		boolean ['showSysMessage']    false if should not show the CB standard result message to new user, true otherwise
	* 		string  ['emailAdminSubject'] email subject and
	* 		string  ['emailAdminMessage'] email body complements to send to admins: you should also check ( $ueConfig['moderatorEmail'] == 1 )
	* 		string  ['emailUserSubject']  email subject and
	* 		string  ['emailUserMessage']  email body complements to send to admins: you should also check ( $ueConfig['moderatorEmail'] == 1 )
	* 		string  ['messagesToUser']    html text to display to user (within a div-tag)
	*/
	public function onBeforeUserActive( &$user, $ui, $cause, /** @noinspection PhpUnusedParameterInspection */ $mailToAdmins, /** @noinspection PhpUnusedParameterInspection */ $mailToUser )
	{
		global $_CB_database, $_POST;

		cbpaidErrorHandler::on();

		$params									=	$this->params;
		$registrationPlansEnabled				=	$params->get( 'registrationPlansEnabled', 0 );
		$enableFreeRegisteredUser				=	$params->get( 'enableFreeRegisteredUser', 1 );

		$result = null;			// no interception or change by default

		if ( $registrationPlansEnabled ) {
			// any paid subscription plan on the way ?
			$paidUserExtension					=&	cbpaidUserExtension::getInstance( $user->id );
			$subscriptions						=&	$paidUserExtension->getUserSubscriptions( 'R' );
			if ( count( $subscriptions ) > 0 ) {
				$confirmedOverride				=	0;
				$approvedOverride				=	0;
				$planId							=	0;
				$subId							=	0;
				foreach ( $subscriptions as $sub ) {
			//		if ( $sub->getPlanAttribute( 'rate' ) != 0 ) {
						$confirmedOverride		=	max( $confirmedOverride, $sub->getPlanAttribute( 'confirmed' ) );			//TBD: // FIXME CHECK THIS!
						$approvedOverride		=	max( $approvedOverride,  $sub->getPlanAttribute( 'approved' ) );			//TBD: // FIXME CHECK THIS!
						$planId					=	$sub->getPlanAttribute( 'id' );
						$subId					=	$sub->id;
			//		}
				}
				// non-free plan:
				if ( ( $ui == 1 )
					&& ( ( in_array( $cause, array( 'UserRegistration', 'SameUserRegistrationAgain' ) ) )			// && ( ! $enableFreeRegisteredUser )
						&& ( ( $confirmedOverride != 3 ) && ( $approvedOverride != 3 ) ) )
					|| ( ( $cause == 'UserConfirmation' )
						&& ( ( $confirmedOverride == 3 ) && ( $approvedOverride != 3 ) ) ) )
				{
					// no pre-confirmation or pre-approval:
					// first registration: don't send email or activate yet (this is case with confirm=0 and approve=0) if no free registration:
					if ( ! $this->paymentBasket ) {
						$this->paymentBasket	=	new cbpaidPaymentBasket( $_CB_database );
						$this->paymentBasket->loadLatestBasketOfUserPlanSubscription( $user->id, $planId, $subId );
					}
					$introText					=	$params->get('intro_text', null);
					if ( is_object( $this->paymentBasket ) ) {
						$paymentFormHtml		=	cbpaidControllerOrder::showBasketForPayment( $user, $this->paymentBasket, $introText );
					} else {
						$paymentFormHtml		=	$this->paymentBasket;		// show messages as nothing to pay.
					}
					$result = array(	'activate'		 =>	$enableFreeRegisteredUser,
										'mailToAdmins'	 =>	false,
										'mailToUser'	 =>	false,
										'showSysMessage' =>	false,
										'messagesToUser' =>	$paymentFormHtml
									);
					$this->outputRegTemplate();
				}
				elseif ( ( ( ( $ui == 1 ) || ( $ui == 2 ) ) && ( $cause == 'UserApproval' ) && ( $approvedOverride == 3 ) )
							|| ( $ui == 2 ) && ( ( $cause == 'NewUser' ) || ( $cause == 'UpdateUser' ) ) && ( $approvedOverride == 3 ) )
				{
					// user appoval from front- or back-end and payment only after approval
					// or new user created or edited from backend and payment only after approval:
					$result = array(	'activate'		 =>	$enableFreeRegisteredUser,
										'mailToAdmins'	 =>	false,
										'mailToUser'	 =>	true,				//TBD: need to add link in email to pay if $enableFreeRegisteredUser is not true
										'showSysMessage' =>	false,
										'messagesToUser' =>	null
									);
				}
			} elseif ( $this->paymentBasket ) {		// donation or other sales: display basket:
				$introText						=	$params->get('intro_text', null);
				if ( is_object( $this->paymentBasket ) ) {
					$paymentFormHtml			=	cbpaidControllerOrder::showBasketForPayment( $user, $this->paymentBasket, $introText );
				} else {
					$paymentFormHtml			=	$this->paymentBasket;		// show messages as nothing to pay.
				}
				$result = array(	'activate'		 =>	true,
									'mailToAdmins'	 =>	true,	// all these true mean no override.
									'mailToUser'	 =>	true,
									'showSysMessage' =>	true,
									'messagesToUser' =>	$paymentFormHtml
								);
				$this->outputRegTemplate();
			}
			// $result['emailUserMessage'] = ......;
		}
		cbpaidErrorHandler::off();
		return $result;
	}
	/**
	* CB user activation interception
	* 
	* @param  UserTable  $user       + moscomprofile the user being displayed
	* @param  int        $ui         1 for front-end, 2 for back-end
	* @param  string     $returnURL  URL to redirect to (using cbRedirect) (can be changed here !)
	* @return array                  with keys (or null if not intercepting/changing behavior):
	* 		string  ['messagesToUser']	html text to display to user (within a div-tag)
	* 		string	['alertMessage']	false if should not display any JS popup, true otherwise
	* 		boolean ['showSysMessage']	false if should not show the CB standard result error messages to user, true otherwise
	* 		boolean ['stopLogin']		true if should not login, false otherwise
	*/
	public function onDuringLogin( &$user, $ui, &$returnURL ) {
		global $_CB_framework, $_POST;

		cbpaidErrorHandler::on();

		$params							=	$this->params;
		$registrationPlansEnabled		=	$params->get( 'registrationPlansEnabled', 0 );
		$enableFreeRegisteredUser		=	$params->get( 'enableFreeRegisteredUser', 1 );

		$result							=	null;			// no interception or change by default

		$paidsubsManager				=&	cbpaidSubscriptionsMgr::getInstance();
		$paidsubsManager->checkExpireMe( __FUNCTION__, $user->id );

		if ( $registrationPlansEnabled ) {
			// any unpaid basket pending ?
			$basketsMgr					=&	cbpaidOrdersMgr::getInstance();
			$paymentBasket				=	$basketsMgr->loadCurrentUnpaidBasket( $user->id );
//			$paymentBasket				=	new cbpaidPaymentBasket( $_CB_database );
//			if ( $paymentBasket->loadLatestBasketFromUser( $user->id, true ) ) {
			if ( $paymentBasket ) {
				// if there is an unpaid basket, show it to the user, and keep user logged-in or not, depending of $user->block status:
				$introText				=	$params->get('intro_text', null);
				$paymentFormHtml		=	cbpaidControllerOrder::showBasketForPayment( $user, $paymentBasket, $introText );
				$result = array(	'messagesToUser' =>	$paymentFormHtml,
									'alertMessage'	 =>	null,
									'showSysMessage' =>	false,
									'stopLogin'		 =>	false			// $user->block will be tested by CB later		// ( ! $enableFreeRegisteredUser )
								);
				$_POST['loginfrom']		=	'noLoginFormDisplay';
				$this->outputRegTemplate();
				$returnURL				=	null;
			} else {
				// this is now user-subciptions-plans specific stuff:
				$paidUserExtension		=&	cbpaidUserExtension::getInstance( $user->id );
				$subscriptions			=	$paidUserExtension->getUserSubscriptions();
				// user blocked in frontend while not enabling free registered users ?
				if ( ( $ui == 1 ) && $user->block /* && ! $enableFreeRegisteredUser */ ) {
					$subsToPay			=	null;
					$couldPayNow		=	false;
					if ( $user->confirmed && ( $user->approved == 1 ) ) {
						// all fine to login on joomla and CB side
						$couldPayNow	=	true;
					} else {
						foreach ( $subscriptions as $sub ) {
							if ( in_array( $sub->status, array( 'R', 'X') ) && ( ! $sub->checkifValid() ) ) {
								if ( $sub->status == 'R' ) {
									$reason = 'N';
								} else {
									$reason = 'R';
								}
								$now	=	$_CB_framework->now();
								$price	= $sub->getPriceOfNextPayment( null, $now, 1, $reason );
								if ( $price > 0 ) {
					 				if (	( ( ! $user->confirmed ) && ( $sub->getPlanAttribute( 'confirmed' ) != 3 ) )	// unconfirmed but payment not after confirmation
										||	( $user->confirmed && ( $user->approved != 1 )  && ( $sub->getPlanAttribute( 'approved' ) != 3 ) )
										)															// or confirmed but unapproved & payment not after approval
									{
										$couldPayNow	=	true;
										$subsToPay[]	=	array( $sub->plan_id, $sub->id );
									}
								}
							}
						}
					}
					if ( $couldPayNow ) {
						// any unpaid payment basket ?
						$introText		=	$params->get('intro_text', null);
						$paymentStatus	=	null;
						$paymentFormHtml =	cbpaidControllerOrder::showPaymentForm( $user, null, $introText, $subsToPay, $paymentStatus );
						if ($paymentFormHtml ) {
							if ( ( ( $paymentStatus == null ) || ( $paymentStatus == 'NotInitiated' ) )
								// if unpaid: display basket and payment buttons or redirect for payment depending if multiple payment choices or intro text present:
							|| ( $paymentStatus != 'Completed' ) )
								// if payment not completed: display payment basket current status:
							{
								$result = array(	'messagesToUser' =>	$paymentFormHtml,
													'alertMessage'	 =>	null,
													'showSysMessage' =>	false,
													'stopLogin'		 =>	( ! $enableFreeRegisteredUser )
												);
								$_POST['loginfrom']		=	'noLoginFormDisplay';
								$this->outputRegTemplate();
								$returnURL	=	null;
							}
						}
					}
				} else {
					if ( ( strpos( $returnURL, 'getTabComponent' ) || strpos( $returnURL, 'tabclass' ) || strpos( $returnURL, 'pluginclass' ) ) && strpos( $returnURL, 'cbpaidsubscriptions' ) ) {
						// avoid displaying the thank you page again (specially that the registration basket will not be found:
						$returnURL		=	null;
					}
					if ( $user->lastvisitDate == '0000-00-00 00:00:00' ) {
						// user has a paid subscription plan, and it's his first login:
						$firstLoginUrl		=	null;
						$paidUserExtension	=&	cbpaidUserExtension::getInstance( $user->id );
						$subscriptions		=	$paidUserExtension->getUserSubscriptions( 'A' );
						foreach ( $subscriptions as $sub ) {
							if ( $sub->checkifValid() && $sub->getPlanAttribute( 'firstloginurl' ) ) {
								$firstLoginUrl	=	$sub->getPlanAttribute( 'firstloginurl' );
							}
						}
						if ( $firstLoginUrl ) {
							$returnURL		=	cbSef( $firstLoginUrl );
						}
					} else {
						// it's a paid subscription user and see if we need to redirect him to his login-home page URL:
						$eachLoginUrl		=	null;
						$paidUserExtension	=&	cbpaidUserExtension::getInstance( $user->id );
						$subscriptions		=	$paidUserExtension->getUserSubscriptions( 'A' );
						foreach ( $subscriptions as $sub ) {
							if ( $sub->checkifValid() && $sub->getPlanAttribute( 'eachloginurl' ) ) {
								$eachLoginUrl =	$sub->getPlanAttribute( 'eachloginurl' );
							}
						}
						if ( $eachLoginUrl ) {
							$returnURL		=	cbSef( $eachLoginUrl );
						}
					}
				}
			}
				
/*			
			// any paid subscription plan on the way ?
			$chosenPlans				=	$this->_getChosenUserPlans( $user );
			if ( count( $chosenPlans ) > 0 ) {
				// user blocked in frontend while not enabling free registered users ?
				if ( ( $ui == 1 ) && $user->block /* && ! $enableFreeRegisteredUser * / ) {
					$couldPayNow		=	false;
					if ( $user->confirmed && ( $user->approved == 1 ) ) {
						// all fine to login on joomla and CB side
						$couldPayNow	=	true;
					} else {
						foreach ( $chosenPlans as $plan ) {
							if ( $plan->get( 'rate' ) != 0 ) {
				 				if (	( ( ! $user->confirmed ) && ( $plan->get( 'confirmed' ) != 3 ) )	// unconfirmed but payment not after confirmation
									||	( $user->confirmed && ( $user->approved != 1 )  && ( $plan->get( 'approved' ) != 3 ) )
									)															// or confirmed but unapproved & payment not after approval
								{
									$couldPayNow	=	true;
								}
							}
						}
					}
					if ( $couldPayNow ) {
						// any unpaid payment basket ?
						$introText		=	$params->get('intro_text', null);
						$paymentStatus	=	null;
						$paymentFormHtml =	cbpaidControllerOrder::showPaymentForm( $user, $chosenPlans, $introText, null, $paymentStatus );
						if ( ( ( $paymentStatus == null ) || ( $paymentStatus == 'NotInitiated' ) )
							// if unpaid: display basket and payment buttons or redirect for payment depending if multiple payment choices or intro text present:
						|| ( $paymentStatus != 'Completed' ) )
							// if payment not completed: display payment basket current status:
						{
							$result = array(	'messagesToUser' =>	$paymentFormHtml,
												'alertMessage'	 =>	null,
												'showSysMessage' =>	false,
												'stopLogin'		 =>	( ! $enableFreeRegisteredUser )
											);
							$this->_outputRegTemplate();
							$returnURL	=	null;
						}
					}
				} elseif ( $user->lastvisitDate == '0000-00-00 00:00:00' ) {
					// user has a paid subscription plan, and it's his first login:
					if ( strpos( $returnURL, 'getTabComponent' ) && strpos( $returnURL, 'cbpaidsubscriptions' ) ) {
						// avoid displaying the thank you page again (specially that the registration basket will not be found:
						$returnURL		=	null;
					}
					$firstLoginUrl		=	null;
					foreach ( $chosenPlans as $plan ) {
						if ( $plan->get( 'firstloginurl' ) ) {
							$firstLoginUrl	=	$plan->get( 'firstloginurl' );
						}
					}
					if ( $firstLoginUrl ) {
						$returnURL		=	cbSef( $firstLoginUrl );
					}
				} else {
					// it's a paid subscription user and see if we need to redirect him to his login-home page URL:
					$eachLoginUrl		=	null;
					foreach ( $chosenPlans as $plan ) {
						if ( $plan->get( 'firstloginurl' ) ) {
							$eachLoginUrl =	$plan->get( 'eachloginurl' );
						}
					}
					if ( $eachLoginUrl ) {
						$returnURL		=	cbSef( $eachLoginUrl );
					}
				}
			}
*/
		}
		cbpaidErrorHandler::off();
		return $result;
	}

	/**
	* Stops the display of messages
	*
	* @param  UserTable  $row             reflecting the user being registered
	* @param  UserTable  $rowExtras       reflecting the user extra fields being registered
	* @param  array      $messagesToUser  of string: html text to display to user (within a div-tag)
	* @param  boolean    $confirm         if user is confirmed    : true
	* @param  boolean    $approve         if user is approved     : true
	* @param  boolean    $bool            true
	*/
	public function onAfterUserRegistrationMailsSent(&$row, &$rowExtras, &$messagesToUser, $confirm, $approve, $bool) {
/*		global $_PLUGINS;
		$params = $this->params;

		if ( $rowExtras->cb_paidsubscription_plan
			&& ( ( $plan = & $this->_loadPlan( $rowExtras->cb_paidsubscription_plan ) ) !== null )
			&& $plan->get( 'rate' ) != 0 ) {
			// non-free plan:
			$_PLUGINS->raiseError(0);		// avoid displaying standard registration completed messages at this time
		}
*/	}

	/**
	* UserBot Called when a user is deleted from backend (prepare future unregistration)
	*
	* @param  UserTable  $user  reflecting the user being deleted
	* @return boolean           true if all is ok, or false if ErrorMSG generated
	*/
	public function onBeforeDeleteUser( $user ) {
		cbpaidErrorHandler::on();

		$paidUserExtension			=&	cbpaidUserExtension::getInstance( $user->id );
		$subscriptions				=&	$paidUserExtension->getUserSubscriptions();

		foreach ( array_keys( $subscriptions ) as $k ) {
			if ( $subscriptions[$k]->status == 'A' ) {
				$subscriptions[$k]->deactivate( $user, 'C' );
			}
		}
		cbpaidErrorHandler::off();
	}
	/**
	* UserBot Called when a user is deleted from backend (unregistration done)
	*
	* @param  UserTable  $user     reflecting the user being deleted
	* @param  boolean    $success  TRUE for successful deleting
	* @return boolean              true if all is ok, or false if ErrorMSG generated
	*/
	public function onAfterDeleteUser( $user, /** @noinspection PhpUnusedParameterInspection */ $success ) {
		global $_CB_database;

		cbpaidErrorHandler::on();

		$return = true;

		$paidUserExtension			=&	cbpaidUserExtension::getInstance( $user->id );
		$subscriptions				=&	$paidUserExtension->getUserSubscriptions();

		foreach ( array_keys( $subscriptions ) as $k ) {
			$subscriptions[$k]->historySetMessage( 'User subscription deleted because user is deleted' );
			if ( ! $subscriptions[$k]->delete() ) {
				$this->_setErrorMSG('SQL error cbpay userdelete error: '.htmlspecialchars($_CB_database->getErrorMsg())."\n");
				$return = false;
			}
		}
		cbpaidErrorHandler::off();
		return $return;
	}
	/**
	 * Use the CB users list event as trigger to check for subscriptions expiries.
	 *
	 * @param  int        $listid
	 * @param  ListTable  $row
	 * @param  string     $search
	 * @param  int        $limitstart
	 * @param  int        $limit
	 * @return array                   array of additional fields to search
	 */
	public function onStartUsersList( /** @noinspection PhpUnusedParameterInspection */ &$listid, &$row, &$search, &$limitstart, &$limit ) {
		$this->onCBSubsCheckExpireMe( __FUNCTION__ );
		return array();		
	}
	/**
	 * Checks for the user's subscriptions,
	 * and if no subscription found (and free memberships not allowed):
	 * redirects or returns FALSE depending on $redirect
	 * Otherwise returns TRUE
	 *
	 * @param  string   $functionName  Name of calling function (CB API functions: getDisplayTab, getEditTab, onDuringLogin, getTabComponent, module: mod_subscriptions )
	 * @param  int      $userId        Check a specific user
	 * @param  boolean  $redirect      If should redirect in case of expiration
	 * @return boolean                 if $redirect == false: TRUE: membership valid, FALSE: membership not valid, otherwise: TRUE or REDIRECT !
	 */
	public function onCBSubsCheckExpireMe( $functionName, $userId = null, $redirect = true ) {
		cbpaidErrorHandler::on();

		$paidsubsManager					=&	cbpaidSubscriptionsMgr::getInstance();
		$paidsubsManager->checkExpireMe( $functionName, $userId, $redirect );

		cbpaidErrorHandler::off();
	}
	/**
	 * Modify the fields records for the purpose of user registration to remove the "required" attribute of fields hidden by the selected plans
	 *
	 * @param  array      $fields
	 * @param  UserTable  $user
	 * @param  string     $reason
	 * @param  int        $tabid
	 * @param  mixed      $fieldIdOrName
	 * @param  boolean    $fullAccess
	 */
	public function onAfterFieldsFetch( &$fields, &$user, $reason, $tabid, $fieldIdOrName, /** @noinspection PhpUnusedParameterInspection */ $fullAccess = false ) {
		if ( ( $reason != 'register' ) || $tabid || $fieldIdOrName ) {
			return;
		}
		$chosenPlans			=	$this->_getRegistrationChoosenPlans( $user );
		if ( ! ( is_array( $chosenPlans ) && ( count( $chosenPlans ) > 0 ) ) ) {
			return;
		}

		$allFieldsToHide		=	array();
		/** @var cbpaidProduct[] $chosenPlans */
		foreach  ( array_keys( $chosenPlans ) AS $id ) {
			$hideregistrationfields	=	$chosenPlans[$id]->get( 'hideregistrationfields' );
			if ( $hideregistrationfields ) {
				$fieldsToHide			=	explode( '|*|', $hideregistrationfields );
				$allFieldsToHide		=	array_merge( $allFieldsToHide, $fieldsToHide );
			}
		}
		foreach ( array_keys( $fields ) as $idx ) {
			if ( in_array( $fields[$idx]->fieldid, $allFieldsToHide ) ) {
				$fields[$idx]->required	=	0;
			}
		}
	}

	/**
	 * WARNING: UNCHECKED ACCESS! On purpose unchecked access for M2M operations
	 * Generates the HTML to display for a specific component-like page for the tab. WARNING: unchecked access !
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @param  array      $postdata  _POST data for saving edited tab content as generated with getEditTab
	 * @return string|boolean        Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getTabComponent( $tab, $user, $ui, $postdata ) {
		global $_POST;

		cbpaidErrorHandler::on();

		$tabComponent		=	new cbpaidControllerCBTab();
		$return				=	$tabComponent->getTabComponent( $tab, $user, $ui, $postdata );

		cbpaidErrorHandler::off();
		return $return;
	}
	/**
	 * Method overload to shorten prefix from cbpaidsubscriptions to cbp to shorten URLs
	 * as auth.net is limiting silent URLs and 2co notification URLs to 256 chars.
	 *
	 * Returns prefix for all GET and POST 
	 * @param  string $postfix
	 * @return string
	 */
	public function _getPrefix( $postfix='' ) {
		return 'cbp' . $postfix;
	}
	/**
	* Method overload to still accept old cbpaidsubscriptions prefixes
	*
	* gets an ESCAPED and urldecoded request parameter for the plugin
	* you need to call stripslashes to remove escapes, and htmlspecialchars before displaying.
	*
	* @param  string  $name     name of parameter in REQUEST URL
	* @param  string  $def      default value of parameter in REQUEST URL if none found
	* @param  string  $postfix  postfix for identifying multiple pagings/search/sorts (optional)
	* @return string|array      value of the parameter (urldecode processed for international and special chars) and ESCAPED! and ALLOW HTML!
	*/
	public function _getReqParam( $name, $def=null, $postfix="" ) {
		global $_GET, $_POST;

		$prefixedName	=	$this->_getPrefix($postfix) . $name;
		// can not do this as it $name can be in array form with '[]' : if ( isset( $_POST[$prefixedName] ) || isset( $_GET[$prefixedName] ) ) {
		if ( ( cbGetParam( $_POST, $prefixedName, false ) !== false ) || ( cbGetParam( $_GET, $prefixedName, false ) !== false ) ) {
			return parent::_getReqParam( $name, $def, $postfix );
		} else {
			// legacy urls:
			$prefixedName	=	'cbpaidsubscriptions' . $postfix . $name;
			$value		=	cbGetParam( $_POST, $prefixedName, false );
			if ( $value !== false ) {
				$value	=	cbGetParam( $_POST, $prefixedName, $def );
			} else {
				$value	=	cbGetParam( $_GET, $prefixedName, $def );
				if ( $value && is_string( $value ) ) {
					$value	=	urldecode( $value );
				}
			}
			return $value;
		}
	}
	/**
	* Gives the URL of a link with plugin parameters, as HTTPS if global CBSubs setting is to use HTTPS for Forms (PCI-DSS compliance).
	*
	* @param  array    $paramArray        array of string with key name of parameters
	* @param  string   $task              cb task to link to (default: userProfile)
	* @param  boolean  $sefed             TRUE to call cbSef (default), FALSE to leave URL unsefed
	* @param  array    $excludeParamList  of string with keys of parameters to not include
	* @param  string   $format            'html', 'component', 'raw', 'rawrel'		(added in CB 1.2.3)
	* @return string value of the parameter
	*/
	public function getHttpsAbsURLwithParam( $paramArray, $task = 'userProfile', $sefed = true, $excludeParamList = null, $format = 'html' ) {
		$url	=	$this->_getAbsURLwithParam( $paramArray, $task, $sefed, $excludeParamList, $format );
		if ( cbpaidApp::settingsParams()->get( 'https_posts', 0 ) ) {
			return preg_replace( '/^https?:/', 'https:', $url );
		} else {
			return $url;
		}
	}
	/**
	 * BACKWARDS COMPATIBILITY FUNCTION FOR options plugin
	 * @deprecated 2.0.2 : use getHttpsAbsURLwithParam
	 *
	 * @param  array    $paramArray        array of string with key name of parameters
	 * @param  string   $task              cb task to link to (default: userProfile)
	 * @param  boolean  $sefed             TRUE to call cbSef (default), FALSE to leave URL unsefed
	 * @param  array    $excludeParamList  of string with keys of parameters to not include
	 * @param  string   $format            'html', 'component', 'raw', 'rawrel'		(added in CB 1.2.3)
	 * @return string value of the parameter
	 */
	public function _getHttpsAbsURLwithParam( $paramArray, $task = 'userProfile', $sefed = true, $excludeParamList = null, $format = 'html' ) {
		return $this->getHttpsAbsURLwithParam( $paramArray, $task, $sefed, $excludeParamList, $format );
	}
}	// end class getcbpaidsubscriptionsTab


// Turn error handler used during file load off:
if ( class_exists( 'cbpaidErrorHandler' ) ) {		// protection for upgrades
	cbpaidErrorHandler::off();
}
