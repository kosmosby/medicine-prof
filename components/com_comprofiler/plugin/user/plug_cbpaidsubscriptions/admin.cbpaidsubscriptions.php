<?php
/**
* @version $Id: admin.cbpaidsubscriptions.php 1608 2012-12-29 04:12:52Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onBeforeBackendUsersListBuildQuery',	'onBeforeBackendUsersListBuildQuery',	'cbpaidAdminEvents' );
$_PLUGINS->registerFunction( 'onAfterBackendUsersList',				'onAfterBackendUsersList',				'cbpaidAdminEvents' );
$_PLUGINS->registerFunction( 'onBeforeBackendUsersEmailForm',		'onBeforeBackendUsersEmailForm',		'cbpaidAdminEvents' );
// $_PLUGINS->registerFunction( 'onBeforeBackendUsersEmailStart',		'onBeforeBackendUsersEmailStart',		'cbpaidAdminEvents' );
$_PLUGINS->registerFunction( 'onBeforeBackendUserEmail',			'onBeforeBackendUserEmail',				'cbpaidAdminEvents' );

global $_CB_framework;
if ( $_CB_framework->getCfg( 'debug' ) ) {
	ini_set('display_errors',true);
	error_reporting(E_ALL);
}

/** @noinspection PhpIncludeInspection */
include_once( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/cbpaidsubscriptions.class.php');

cbpaidErrorHandler::install();
cbpaidErrorHandler::on();

/**
 * Controller class for the admin interface
 */
class cbpaidAdminEvents extends cbpaidApp {		// getcbpaidsubscriptionsTab {
	protected $filter_cbpaidplan;
	protected $filter_cbpaidsubstate;
	protected $filter_cbpaidsubexpdate;

	/**
	 * Intercepts CB User Manager list controller to add filters evaluation
	 *
	 * @param  array   $tablesSQL
	 * @param  array   $joinsSQL
	 * @param  array   $tablesWhereSQL
	 * @param  string  $option
	 * @return void
	 */
	public function onBeforeBackendUsersListBuildQuery( /** @noinspection PhpUnusedParameterInspection */ &$tablesSQL, &$joinsSQL, &$tablesWhereSQL, $option ) {
		global $_CB_framework, $_CB_database;

		if ( cbpaidApp::authoriseAction( 'cbsubs.usersubscriptionview' ) ) {

			$this->filter_cbpaidplan		=	(int) $_CB_framework->getUserStateFromRequest( "filter_cbpaidplan{$option}", 'filter_cbpaidplan', 0 );
			$this->filter_cbpaidsubstate	=	$_CB_framework->getUserStateFromRequest( "filter_cbpaidsubstate{$option}", 'filter_cbpaidsubstate', 'A' );
			if ( ! in_array( $this->filter_cbpaidsubstate, array( 'A', 'X', 'C', 'U' ) ) ) {
				$this->filter_cbpaidsubstate	=	'A';
			}
			$this->filter_cbpaidsubexpdate	=	$_CB_framework->getUserStateFromRequest( "filter_cbpaidsubexpdate{$option}", 'filter_cbpaidsubexpdate', '' );
			if ( ! preg_match( '/^(|-?\d+ (DAY|WEEK|MONTH))$/', $this->filter_cbpaidsubexpdate ) ) {
				$this->filter_cbpaidsubexpdate	=	'';
			}
			
			if ( ( $this->filter_cbpaidplan )
				&& ( ( $this->filter_cbpaidplan == -2 )
					|| ( ( in_array( $this->filter_cbpaidsubstate, array( 'A', 'X', 'C', 'U' ) ) )
						&& preg_match( '/^(|-?\d+ (DAY|WEEK|MONTH))$/', $this->filter_cbpaidsubexpdate )
			) ) ) {
				if ( $this->filter_cbpaidsubexpdate ) {
					if ( $this->filter_cbpaidsubexpdate[0] == '-' ) {
						$andExpDate		=	' AND subs.expiry_date > date_sub(NOW(), INTERVAL ' . substr( $this->filter_cbpaidsubexpdate, 1 ) . ')';
					} else {
						$andExpDate		=	' AND subs.expiry_date < date_add(NOW(), INTERVAL ' . $this->filter_cbpaidsubexpdate . ')';
					}
				} else {
					$andExpDate			=	'';
				}
					
				if ( $this->filter_cbpaidplan == -1 ) {
					// any plan:
					$joinsSQL[]			=	'INNER JOIN #__cbsubs_subscriptions AS subs ON (subs.user_id = u.id AND subs.status =' . $_CB_database->Quote( $this->filter_cbpaidsubstate )
										.	$andExpDate
										.	')';
				} elseif ( $this->filter_cbpaidplan == -2 ) {
					// no plan:
					$joinsSQL[]			=	"LEFT JOIN #__cbsubs_subscriptions AS subs ON (subs.user_id = u.id AND subs.status ='A')";
					$tablesWhereSQL[]	=	"subs.plan_id IS NULL";
				} else {
					// specific plan:
					$joinsSQL[]			=	'INNER JOIN #__cbsubs_subscriptions AS subs ON (subs.user_id = u.id AND subs.status =' . $_CB_database->Quote( $this->filter_cbpaidsubstate )
										.	' AND subs.plan_id = ' . (int) $this->filter_cbpaidplan
										.	$andExpDate
										.	')';
				}
			}

		}
	}

	/**
	 * Intercepts CB User Manager list Viewer to add filters
	 *
	 * @param  int        $listId
	 * @param  UserTable  $rows
	 * @param  cbPageNav  $pageNav
	 * @param  string     $search
	 * @param  string[]   $lists
	 * @param  string     $option
	 * @param  string     $select_tag_attribs
	 * @return array
	 */
	public function onAfterBackendUsersList( /** @noinspection PhpUnusedParameterInspection */ $listId, &$rows, /** @noinspection PhpUnusedParameterInspection */ &$pageNav, /** @noinspection PhpUnusedParameterInspection */ &$search, &$lists, /** @noinspection PhpUnusedParameterInspection */ $option, $select_tag_attribs ) {
		if ( ! cbpaidApp::authoriseAction( 'cbsubs.usersubscriptionview' ) ) {
			return array();
		}

		$this->outputRegTemplate();

		// 1. Filters:
		// 1.a. prepare dropdown selector filter with the list of published plans:
		$plansMgr					=&	cbpaidPlansMgr::getInstance();
		$plans						=	$plansMgr->loadPublishedPlans( CBuser::getMyUserDataInstance(), true, 'any', null );

		$plansList					=	array();
		$plansList[]				=	moscomprofilerHTML::makeOption( 0, CBPTXT::T('- Select Subscription Plan - ') );

		foreach ( $plans as $k => $plan ) {
			$plansList[]			=	moscomprofilerHTML::makeOption( $k, $plan->get( 'alias' ) );
		}
		if ( count( $plans ) > 0 ) {
			$plansList[]			=	moscomprofilerHTML::makeOption( -1, CBPTXT::T('ANY PLAN ACTIVE') );
			$plansList[]			=	moscomprofilerHTML::makeOption( -2, CBPTXT::T('NO PLAN ACTIVE') );
		}
		$lists['cbpaidplan']		=	moscomprofilerHTML::selectList( $plansList, 'filter_cbpaidplan', $select_tag_attribs, 'value', 'text', $this->filter_cbpaidplan, 2 );

		// 1.b. prepare additional selector filter for status of subscriptions:
		if ( $this->filter_cbpaidplan && ( $this->filter_cbpaidplan != -2 ) ) {
			// any plan or specific plan:		// no plan: nothing for now to do
			$statesList				=	array();
			$statesList[]			=	moscomprofilerHTML::makeOption( 'A', CBPTXT::T('Active') );
			$statesList[]			=	moscomprofilerHTML::makeOption( 'X', CBPTXT::T('Expired') );
			$statesList[]			=	moscomprofilerHTML::makeOption( 'C', CBPTXT::T('Cancelled') );
			$statesList[]			=	moscomprofilerHTML::makeOption( 'U', CBPTXT::T('Upgraded') );
			$lists['cbpaidsubstate'] =	moscomprofilerHTML::selectList( $statesList, 'filter_cbpaidsubstate', $select_tag_attribs, 'value', 'text', $this->filter_cbpaidsubstate, 1 );

			$datesList				=	array();
			$datesList[]			=	moscomprofilerHTML::makeOption( '', CBPTXT::T('- Select expiry date -') );
			if ( $this->filter_cbpaidsubstate == 'A' ) {
				$datesList[]		=	moscomprofilerHTML::makeOption( '1 DAY', sprintf( CBPTXT::T('Expiring within %s hours'), 24 ) );
				foreach ( array( 2, 3, 4, 5, 6, 7 ) as $v ) {
					$datesList[]	=	moscomprofilerHTML::makeOption( $v . ' DAY', sprintf( CBPTXT::T('Expiring within %s days'), $v ) );
				}
				foreach ( array( 2, 3, 4 ) as $v ) {
					$datesList[]	=	moscomprofilerHTML::makeOption( $v . ' WEEK', sprintf( CBPTXT::T('Expiring within %s weeks'), $v ) );
				}
				$datesList[]		=	moscomprofilerHTML::makeOption( '1 MONTH', CBPTXT::T('Expiring within in 1 month') );
				foreach ( array( 2, 3, 4, 6, 9, 12 ) as $v ) {
					$datesList[]	=	moscomprofilerHTML::makeOption( $v . ' MONTH', sprintf( CBPTXT::T('Expiring within %s months'), $v ) );
				}
			} else {
				$datesList[]		=	moscomprofilerHTML::makeOption( '-1 DAY', sprintf( CBPTXT::T('Expired last %s hours'), 24 ) );
				foreach ( array( 2, 3, 4, 5, 6, 7 ) as $v ) {
					$datesList[]	=	moscomprofilerHTML::makeOption( '-' . $v . ' DAY', sprintf( CBPTXT::T('Expired last %s days'), $v ) );
				}
				foreach ( array( 2, 3, 4 ) as $v ) {
					$datesList[]	=	moscomprofilerHTML::makeOption( '-' . $v . ' WEEK', sprintf( CBPTXT::T('Expired last %s weeks'), $v ) );
				}
				$datesList[]		=	moscomprofilerHTML::makeOption( '-1 MONTH', CBPTXT::T('Expired last month') );
				foreach ( array( 2, 3, 4, 6, 9, 12 ) as $v ) {
					$datesList[]	=	moscomprofilerHTML::makeOption( '-' . $v . ' MONTH', sprintf( CBPTXT::T('Expired last %s months'), $v ) );
				}
			}
			$lists['cbpaidsubexpdate'] =	moscomprofilerHTML::selectList( $datesList, 'filter_cbpaidsubexpdate', $select_tag_attribs, 'value', 'text', $this->filter_cbpaidsubexpdate, 1 );
		}

		// 2. add subscriptions colum to backend users-lists:
		$pluginColumns				=	array();
		foreach ( $rows as $row ) {
			$paidUserExtension		=&	cbpaidUserExtension::getInstance( (int) $row->id );
			$subscriptions			=	$paidUserExtension->getUserSubscriptions( 'A', true );
			$displayPlans			=	array();
			foreach ( $subscriptions as $sub ) {
				$plan				=	$sub->getPlan();
				if ( $plan ) {
					$cssclass		=	$plan->get( 'cssclass' );
					$aliasHtml		=	htmlspecialchars( $plan->get( 'alias' ) );
				} else {
					$cssclass		=	null;
					$aliasHtml		=	CBPTXT::Ph("PLAN OF SUBSCRIPTION ID [SUB_ID] IS DELETED", array( '[SUB_ID]' => $sub->id ) );
				}
				$displayPlans[]		=	'<span' . ( $cssclass ? ' class="' . htmlspecialchars( $cssclass ) . '"' : '' ) . '>'
									.	$aliasHtml
									.	'</span>'
									;
			}
			$pluginColumns[$row->id]	=	implode( ', ', $displayPlans );
		}
		return array( CBPTXT::T('Subscriptions') =>	$pluginColumns );
	}
	/**
	 * Called just before showing the form to send email to users, allowing to add lines to the form
	 *
	 * param  array      $rows
	 * param  cbPageNav  $pageNav
	 * param  string     $search
	 * param  array      $lists
	 * param  array      $cid
	 * param  string     $emailSubject
	 * param  string     $emailBody
	 * param  string     $inputTextExtras
	 * param  string     $select_tag_attribs
	 * param  boolean    $simulationMode
	 * param  string     $option
	 * @return array      of 'title' => 'row' html code for lines to display in form
	 */
	public function onBeforeBackendUsersEmailForm( /* &$rows, &$pageNav, &$search, &$lists, &$cid, &$emailSubject, &$emailBody, &$inputTextExtras, &$select_tag_attribs, $simulationMode, $option */ ) {
		$lines			=	array();
		if ( cbpaidApp::authoriseAction( 'cbsubs.usersubscriptionview' ) ) {
			if ( $this->filter_cbpaidplan > 0 ) {
				$plansMgr	=&	cbpaidPlansMgr::getInstance();
				$plan		=&	$plansMgr->loadPlan( (int) $this->filter_cbpaidplan );
				$lines[CBPTXT::Th('Selected plan')]	=	CBPTXT::Th($plan->name);
				$lines[CBPTXT::Th('More substitutions with the selected plan')]	=	'[subscription_start_date], '
				.	( $plan->isProductWithExpiration() ? '[subscription_end_date], ' : '' )
				.	'[subscription_lastrenew_date]';
			}
		}
		return $lines;
	}
	/**
	 * Called just before starting to send email to users, allowing to get the posts of the previous email form and to add lines to the form
	 *
	 * @param  array      $rows
	 * @param  int        $total
	 * @param  string     $search
	 * @param  array      $lists
	 * @param  array      $cid
	 * @param  string     $emailSubject
	 * @param  string     $emailBody
	 * @param  string     $inputTextExtras
	 * @param  boolean    $simulationMode
	 * @param  string     $option
	 * @return array      of 'title' => 'row' html code for lines to display in sending screen
	public function onBeforeBackendUsersEmailStart( &$rows, $total, $search, $lists, $cid, &$emailSubject, &$emailBody, &$inputTextExtras, $simulationMode, $option ) {
	}
	 */
	/**
	 * Called just before emailing each user from CB Users management backend
	 *
	 * @param  UserTable  $user
	 * @param  string     $emailSubject
	 * @param  string     $emailBody
	 * @param  int        $mode
	 * @param  array      $extraStrings    Entries can be filled in this function and will be used to email
	 * @param  boolean    $simulationMode
	 */
	public function onBeforeBackendUserEmail( &$user, /** @noinspection PhpUnusedParameterInspection */ &$emailSubject, /** @noinspection PhpUnusedParameterInspection */ &$emailBody, /** @noinspection PhpUnusedParameterInspection */ $mode, &$extraStrings, /** @noinspection PhpUnusedParameterInspection */ $simulationMode ) {
		if ( cbpaidApp::authoriseAction( 'cbsubs.usersubscriptionview' ) ) {
			if ( $this->filter_cbpaidplan > 0 ) {
				$params		=&	cbpaidApp::settingsParams();
				$showtime	=	( $params->get( 'showtime', '1' ) == '1' );
	
				$plansMgr	=&	cbpaidPlansMgr::getInstance();
				$plan		=&	$plansMgr->loadPlan( (int) $this->filter_cbpaidplan );
				$sub		=	$plan->loadLatestSomethingOfUser( $user->id, $this->filter_cbpaidsubstate );
	
				$extraStrings['subscription_start_date']		=	( $sub ? cbFormatDate( $sub->getSubscriptionDate(), 1, $showtime ) : CBPTXT::T('No subscription') );
	
				if ( $plan->isProductWithExpiration() ) {
					if ( $sub ) {
						$extraStrings['subscription_end_date']	=	$sub->getFormattedExpirationDateText();
					} else {
						$extraStrings['subscription_end_date']	=	CBPTXT::T('No subscription');
					}
				}
	
				$extraStrings['subscription_lastrenew_date']	=	( $sub ? cbFormatDate( $sub->getLastRenewDate(), 1, $showtime ) : CBPTXT::T('No subscription') );
			}
		}
	}
}

/**
 * Model Class for implementing Refunds
 */
class cbpaidPaymentManualRefund extends cbpaidPayment {
	public $refund_gross;
	public $refund_is_last;
	public $refund_reason;

	/**
	 * If table key (id) is NULL : inserts a new row
	 * otherwise updates existing row in the database table
	 *
	 * Can be overridden or overloaded by the child class
	 *
	 * @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	 * @return boolean                TRUE if successful otherwise FALSE
	 */
	public function store( $updateNulls = false ) {
		if ( ! cbpaidApp::authoriseAction( 'cbsubs.refunds' ) ) {
			$this->setError( CBPTXT::T("Not authorized") );
			return false;
		}

		// 1) check:
		if ( ! in_array( $this->payment_status, array( 'Completed', 'Pending', 'Partially-Refunded' ) ) ) {
			$this->setError( CBPTXT::T("This payment is not completed, pending or partially refunded.") );
			return false;
		}
		if ( $this->txn_id == '' ) {
			$this->txn_id			=	'None';		// needed for updatePayment to generate payment record.
		}

		$payment					=	new cbpaidPayment();
		if ( ! $payment->load( (int) $this->id ) ) {
			$this->setError( CBPTXT::T("This payment does not exist.") );
			return false;
		}
		$paymentBasket				=	new cbpaidPaymentBasket();
		if ( ! $paymentBasket->load( $this->payment_basket_id ) ) {
			$this->setError( CBPTXT::T("This payment has no associated payment basket and cannot be refunded from here. Maybe from your PSP online terminal ?") );
			return false;
			
		}

		if ( ! $this->gateway_account ) {
			$this->setError( CBPTXT::T("This payment has no gateway associated so can not be refunded.") );
			return false;
		}

		$payAccount					=	cbpaidControllerPaychoices::getInstance()->getPayAccount( $this->gateway_account );
		if ( ! $payAccount ) {
			$this->setError( CBPTXT::T("This payment's payment basket's associated gateway account is not active, so can not be refunded from here.") );
			return false;
		}
		$payClass					=	$payAccount->getPayMean();
		$returnText					=	null;
		$amount						=	sprintf( '%.2f', (float) $this->refund_gross );
		if ( is_callable( array( $payClass, 'refundPayment' ) ) ) {
			$success				=	$payClass->refundPayment( $paymentBasket, $payment, null, $this->refund_is_last, $amount, $this->refund_reason, $returnText );
		} else {
			$success				=	false;
		}

		$user						=	CBuser::getUserDataInstance( $paymentBasket->user_id );
		$username					=	$user ? $user->username : '?';
		$replacements				=	array( '[REFUNDAMOUNT]'		=>	$payment->mc_currency . ' ' . $amount,
												'[PAYMENTID]'		=>	$payment->id,
												'[PAYMENTAMOUNT]'	=>	$payment->mc_currency . ' ' . $payment->mc_gross,
												'[BASKETID]'		=>	$paymentBasket->id,
												'[ORDERID]'			=>	$paymentBasket->sale_id,
												'[FULLNAME]'		=>	$paymentBasket->first_name . ' ' . $paymentBasket->last_name,
												'[USERNAME]'		=>	$username,
												'[USERID]'			=>	$paymentBasket->user_id,
												'[PAYMENTMETHOD]'	=>	$payClass->getPayName(),
												'[TXNID]'			=>	$payment->txn_id,
												'[AUTHID]'			=>	$payment->auth_id,
												'[ERRORREASON]'		=>	$paymentBasket->reason_code );
		if ( $success ) {
			// Success Message ?
			// $returnText	=	CBPTXT::P("Refunded [REFUNDAMOUNT] for payment id [PAYMENTID] of [PAYMENTAMOUNT] for basket id [BASKETID], Order id [ORDERID] of [FULLNAME] (username [USERNAME] - user id [USERID]) using [PAYMENTMETHOD] with txn_id [TXNID] and auth_id [AUTHID].", $replacements );
		} else {
			$this->setError( CBPTXT::T( $payClass->getErrorMSG() ) . '. '
						   . CBPTXT::P("Refund request of [REFUNDAMOUNT] for payment id [PAYMENTID] of [PAYMENTAMOUNT] for basket id [BASKETID], Order id [ORDERID] of [FULLNAME] (username [USERNAME] - user id [USERID]) using [PAYMENTMETHOD] with txn_id [TXNID] and auth_id [AUTHID] failed for reason: [ERRORREASON].", $replacements )
							);
			return false;
		}
		return true;
	}
}

/**
 * Administration menus handler class, probably obsolete
 */
class cbpaidsubscriptionsAdmin {
	/**
	 * This function is typically called with a plugin with menus: these must be declared with:
	 * 	<adminmenus>
	 * 		<menu action="import">Import subscriptions</menu>
	 * 	</adminmenus>
	 *
	 * @param  PluginTable               $plugin   plugin db object
	 * @param  string                    $menu     &menu=.... the value part of the URL
	 * @param  cbParamsEditorController  $params   plugin parameters
	 * @return string                              HTML to display
	 */
	public function menu( /** @noinspection PhpUnusedParameterInspection */ $plugin, $menu, $params ) {
		switch ( $menu ) {
			case 'ajversion':
				return $this->ajversion();
			case 'curconvcheck':
				return $this->currencyconvertercheck();
			default:
				break;
		}
		return null;
	}
	/**
	 * Called upon
	 * administrator/index3.php?option=com_comprofiler&task=pluginmenu&cid=566&menu=ajversion&no_html=1&format=raw
	 *
	 * @return string HTML
	 */
	protected function ajversion() {
		global $_GET;

		if ( $_GET['mode'] == 'updatesonly' ) {
			$silent		=	true;
		} else {
			$silent		=	false;
		}

		include_once( dirname( __FILE__ ) . '/cbpaidsubscriptions.php' );

		return cbpaidVersionMgr::latestVersion( $silent );
	}
	/**
	 * Called upon
	 * administrator/index3.php?option=com_comprofiler&task=pluginmenu&cid=566&menu=curconvcheck&no_html=1&format=raw
	 *
	 * @return string HTML
	 */
	protected function currencyconvertercheck() {
		$ret					=	null;
		$_CBPAY_CURRENCIES		=&	cbpaidApp::getCurrenciesConverter();
		$secondaryPrice			=	$_CBPAY_CURRENCIES->convertCurrency( 'EUR', 'USD', 1.0 );
		if ( $secondaryPrice === null ) {
			$ret				=	'<div class="cbDisabled">' . $_CBPAY_CURRENCIES->getError() . '</div>';
		}
		return $ret;
	}
}	// class cbsubscriptionsAdmin
