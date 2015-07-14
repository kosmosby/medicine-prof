<?php
/**
 * @version $Id: cbpaidControllerOffer.php 1607 2012-12-29 02:47:22Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * CBSubs Offers Controller
 */
class cbpaidControllerOffer {
	/**
	 * Displays specific plans
	 *
	 * @param  int[]|null   $plans
	 * @param  int[]|null   $plansToPreselect
	 * @param  UserTable    $user
	 * @param  string       $introText
	 * @return null|string
	 */
	public static function displaySpecificPlans( $plans, $plansToPreselect, $user, $introText ) {
		global $_CB_framework, $_PLUGINS, $ueConfig;
		$return									=	'';

		$subscriptionsGUI						=	new cbpaidControllerUI();

		if ( is_array( $plans ) &&  count( $plans ) == 0 ) {
			$return								.=	'<div class="message">' . _UE_NOT_AUTHORIZED . ( $user === null ? ' ' . _UE_DO_LOGIN : '' ) . '</div>';
		} else {
			$subscriptionsGUI->setShowOnlyPlans( $plans );
			$subscriptionsGUI->setSelectedPlans( $plansToPreselect );
			if ( $user === null ) {
				// Show registration form:
				if ( ( ( $_CB_framework->getCfg( 'allowUserRegistration' ) == '0' )
					&& ( ( ! isset($ueConfig['reg_admin_allowcbregistration']) ) || $ueConfig['reg_admin_allowcbregistration'] != '1' ) ) )
				{
					cbNotAuth();
					return null;
				}
				if ( $_CB_framework->myId() ) {
					$return						.=	'<div class="error">' . _UE_ALREADY_LOGGED_IN . '</div>';
					return self::displayWithTemplate( $return );
				}
				$fieldsQuery					=	null;

				$_PLUGINS->loadPluginGroup( 'user' );
				$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');
				$regErrorMSG					=	null;
				$results						=	$_PLUGINS->trigger( 'onBeforeRegisterForm', array( 'com_comprofiler', isset( $ueConfig['emailpass'] ) ? $ueConfig['emailpass'] : '0', &$regErrorMSG, $fieldsQuery ) );
				if( $_PLUGINS->is_errors() ) {
					$return						.=	"<script type=\"text/javascript\">alert('".addslashes($_PLUGINS->getErrorMSG(" ; "))."'); </script>\n";
					$return						.=	$_PLUGINS->getErrorMSG("<br />");
					return self::displayWithTemplate( $return );
				}
				if ( implode( '', $results ) != "" ) {
					$allResults					=	implode( "</div><div>", $results );
					$return						.=	"<div>" . $allResults . "</div>";
					return self::displayWithTemplate( $return );
				}

				$loginOrRegister				=	CBPTXT::Th( cbpaidApp::settingsParams()->get( 'plansLoginOrRegisterText', "If you already have an account, please login first. Otherwise you need to register using this form." ) );

				$userComplete					=	new UserTable();
				$option							=	'com_comprofiler';
				$emailpass						=	isset( $ueConfig['emailpass'] ) ? $ueConfig['emailpass'] : '0';
				$regErrorMSG					=	$loginOrRegister . ' ' . $introText;
				ob_start();
				$null							=	null;
				HTML_comprofiler::registerForm( $option, $emailpass, $userComplete, $null, $regErrorMSG, true );
				$return							.=	ob_get_contents();
				ob_end_clean();

			} else {
				// User logged in: Show upgrade form:
				$itsmyself						=	true;

				$params							=&	cbpaidApp::settingsParams();
				$subTxt							=	CBPTXT::T( $params->get( 'subscriptions_name', 'subscriptions' ) );

				$title							=	ucfirst( $subTxt );
				if ( $title ) {
					$return						.=	'<div class="contentheading" id="cbregProfileTitle">' . $title . "</div>\n";
				}
				if ( $introText ) {
					$return						.=	'<div class="contentpane">' . $introText . '</div>';
				}
				$return							.=	$subscriptionsGUI->getShowSubscriptionUpgrades( $user, $itsmyself );
			}
			$subscriptionsGUI->setShowOnlyPlans ( null );
		}
		return self::displayWithTemplate( $return );
	}
	/**
	 * Displays $text inside a standard CB div, and sets page title and pathway too
	 *
	 * @param  string  $text
	 * @return string
	 */
	public static function displayWithTemplate( $text ) {
		global $_CB_framework;

		$regTitle							=	strip_tags( CBPTXT::T( cbpaidApp::settingsParams()->get( 'regTitle', "Subscriptions" ) ) );
		outputCbTemplate();
		$_CB_framework->setPageTitle( $regTitle );
		$_CB_framework->appendPathWay( $regTitle );
		$pre								=	'<div class="cbPageOuter"><div class="cbPageInner" id="cbregField">';
		$post								=	'</div></div><div class="cbClr"> </div>';
		return $pre . $text . $post;
	}
}