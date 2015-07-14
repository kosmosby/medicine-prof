<?php
/**
 * @version $Id: cbpaidControllerCBComponent.php 1563 2012-12-22 19:14:43Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * CBSubs class in case auto-loader checks for class in future:
 */
class cbpaidControllerCBComponent { }


/**
 * Paid Subscriptions Plugin Class for handling the CB * Plugin * api
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @author Beat
 */
class CBplug_cbpaidsubscriptions extends getcbpaidsubscriptionsTab {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Backend startup:
	 *
	 */
	public function loadAdmin() {
		// already done above always:
		// $path	=	$this->getPluginPath();
		// require_once( $path . '/cbpaidsubscriptions.sql.php' );
		// require_once( $path . '/cbpaidsubscriptions.gui.php' );
	}
	/*
	 * Example how to load XML file in Backend
	 *
	 * @param  string $type        Task-type
	 * @param  string $typeValue   Sub-Task type
	 * @return string              Full content of corresponding XML file.
	 *
	public function getXml( $type = null, $typeValue = null ) {
		global $_CB_framework, $_SERVER;

		if ( ($_CB_framework->getUi() == 2 ) && ( $type != 'editTab' ) && ( $type != 'front' ) ) {
			$debugFile	=	'edit.plugin.xml';
			if ( @file_exists( $debugFile )) {
				return file_get_contents( $debugFile );
			}
			require_once( $this->getPluginPath() . '/admin.cbpaidsubscriptions.ctrl.php' );
			return XMLDEFINEDINPHP;
		} else {
			return null;
		}
	}
	*/
	/**
	 * WARNING: UNCHECKED ACCESS! On purpose unchecked access for M2M operations
	 * Generates the HTML to display for a specific component-like page for the tab. WARNING: unchecked access !
	 * @param  null       $tab
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @param  array      $postdata  _POST data for saving edited tab content as generated with getEditTab
	 * @return mixed                 either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getCBpluginComponent( $tab, &$user, $ui, &$postdata ) {
		global $_CB_framework, $ueConfig, $_GET;

		cbpaidErrorHandler::on();

		$result								=	null;
		$do									=	cbGetParam( $_GET, 'do' );
		switch ( $do ) {
			case null:
				$return						=	$this->getTabComponent( $tab, $user, $ui, $postdata );
				cbpaidErrorHandler::keepTurnedOn();
				break;

			case 'display_subscriptions':
				if ( $user && $user->id && $_CB_framework->myId() ) {
					$regTitle				=	strip_tags( CBPTXT::T( $this->params->get( 'regTitle', "Subscriptions" ) ) );
					outputCbTemplate();
					$_CB_framework->setPageTitle( $regTitle );
					$_CB_framework->appendPathWay( $regTitle );
					$pre					=	'<div class="cbPageOuter"><div class="cbPageInner">';
					$post					=	'</div></div><div class="cbClr"> </div>';
					$return					=	$pre . $this->displayUserTab( $user ) . $post;
				} else {
					if ( ( ( $_CB_framework->getCfg( 'allowUserRegistration' ) == '0' )
						&& ( ( ! isset($ueConfig['reg_admin_allowcbregistration']) ) || $ueConfig['reg_admin_allowcbregistration'] != '1' ) ) )
					{
						$return				=	_UE_NOT_AUTHORIZED . '<br />' . _UE_DO_LOGIN;
					} else {
						/*
						$registrationUrl	=	cbSef( 'index.php?option=com_comprofiler&task=registers' );
						$registrationLink	=	'<a href="' . $registrationUrl . '">' . _UE_REGISTER . '</a>';
						$loginRegisterText	=	sprintf( CBPTXT::Th("Please login or %s"), $registrationLink );
						$return				=	_UE_NOT_AUTHORIZED . '<br /><br />' . $loginRegisterText;
						*/
						$accessPlans		=	null;
						$return				=	cbpaidControllerOffer::displaySpecificPlans( $accessPlans, null, $user, '' );

					}
				}
				break;

			case 'accessdenied':
				$params						=&	cbpaidApp::settingsParams();
				$accessRedirectLink			=	$params->get( 'subscriptionNeededRedirectLink' );
				if ( $accessRedirectLink ) {
					$textMessage			=	$params->get( 'subscriptionNeededText', "A membership is needed for access." );
					$return					=	null;
					cbRedirect( cbSef( $accessRedirectLink, false ), CBPTXT::T( $textMessage ), 'warning' );
				} else {
					/** @noinspection PhpIncludeInspection */
					include_once cbpaidApp::getAbsoluteFilePath( 'plugin/cbsubscontent/cbsubs.content_deniedview.php' );
					$accessDeniedView		=	new cbpaidContentAccessDeniedView();
					$return					=	$accessDeniedView->display( $user, $this );
				}
				break;

			case 'displayplans':
				$plansParam					=	cbGetParam( $_GET, 'plans' );
				$plans						=	null;
				$preselect					=	null;
				if ( $plansParam ) {
					$plansParam				=	explode( '-', $plansParam );
					foreach ( $plansParam as $p ) {
						$pN					=	(int) $p;
						if ( $pN ) {
							$plans[]		=	$pN;
							if ( substr( $p, -1 ) == 's' ) {
								$preselect[] =	$pN;
							}
						}

					}
				}
				if ( ( $user === null ) || ( $user->id == $_CB_framework->myId() ) ) {
					$introText				=	CBPTXT::Th( $this->params->get( 'plansDisplayIntroText', "We suggest subscribing to following subscriptions:" ) );
					$return					=	cbpaidControllerOffer::displaySpecificPlans( $plans, $preselect, $user, $introText );
				} else {
					$return					=	_UE_NOT_AUTHORIZED;
				}
				break;

			case 'massexpire':				// cron
				$params						=&	cbpaidApp::settingsParams();
				$key						=	cbGetParam( $_GET, 'key' );
				if ( $key && ( $key == md5( $params->get( 'license_number' ) ) ) && ( $params->get( 'massexpirymethod', 0 ) >= 2 ) ) {
					$limit					=	$params->get( 'massexpirynumber', 100 );
					// mass-expire 100 subscriptions at a time on the way if not exipring a particular user:
					$plansMgr				=&	cbpaidPlansMgr::getInstance();
					$plansMgr->checkAllSubscriptions( (int) $limit );
					$return					=	null;
				} else {
					$return					=	CBPTXT::Th("Invalid mass-expiry link: link is in global CBSubs settings.");
				}
				break;

			case 'autopayments':			// cron
				$params						=&	cbpaidApp::settingsParams();
				$key						=	cbGetParam( $_GET, 'key' );
				if ( $key && ( $key == md5( $params->get( 'license_number' ) ) ) ) {
					$limit					=	$params->get( 'massautorenewalsnumber', 100 );
					// mass-autorenew 100 subscriptions at a time:
					$results				=	cbpaidOrdersMgr::getInstance()->triggerScheduledAutoRecurringPayments( (int) $limit );
					$return					=	implode( "\r\n\r\n", $results );
					$massrenewalemail		=	trim( $params->get( 'massrenewalemail', '' ) );
					if ( $massrenewalemail ) {
						cbimport( 'cb.notification' );
						$recipients			=	explode( ',', $massrenewalemail );
						if ( $return ) {
							$body			=	CBPTXT::T("CBSubs has just processed auto-recurring payments as follows:") . "\r\n\r\n"
								.	$return
								.	"\r\n\r\n" . CBPTXT::T("This is an automated email, do not reply.") . "\r\n\r\n";
							comprofilerMail( '', '', $recipients, CBPTXT::T("CBSubs has processed auto-recurring payments"), $body );
						}
					}
					if ( $params->get( 'massrenewaloutputincron', 1 ) != 1 ) {
						// silence output to Cron:
						$return				=	null;
					}
				} else {
					$return					=	CBPTXT::Th("Invalid auto-renewals link: link is in global CBSubs settings.");
				}
				break;

			default:
				$return						=	sprintf( CBPTXT::Th("No valid %s action chosen"), '"do"' ) . '.';
				break;
		}
		cbpaidErrorHandler::off();
		return $return;
	}

	/**
	 * Displays specific plans
	 * @deprecated 2.1 : use cbpaidControllerOffer::displaySpecificPlans
	 * (here for Content integration plugin < 2.1 during upgrades)
	 *
	 * @param  int[]|null   $plans
	 * @param  int[]|null   $plansToPreselect
	 * @param  UserTable    $user
	 * @param  string       $introText
	 * @return null|string
	 */
	public function displaySpecificPlans( $plans, $plansToPreselect, $user, $introText ) {
		return cbpaidControllerOffer::displaySpecificPlans( $plans, $plansToPreselect, $user, $introText );
	}
	/**
	 * Displays specific plans
	 * @deprecated 2.0.2 : use cbpaidControllerOffer::displaySpecificPlans
	 * (here for Content integration plugin < 2.0.2 during upgrades)
	 *
	 * @param  int[]|null   $plans
	 * @param  int[]|null   $plansToPreselect
	 * @param  UserTable    $user
	 * @param  string       $introText
	 * @return null|string
	 */
	public function _displaySpecificPlans( $plans, $plansToPreselect, $user, $introText ) {
		return cbpaidControllerOffer::displaySpecificPlans( $plans, $plansToPreselect, $user, $introText );
	}
}
