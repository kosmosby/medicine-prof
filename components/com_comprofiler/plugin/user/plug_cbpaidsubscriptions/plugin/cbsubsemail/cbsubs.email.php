<?php
/**
* CBSubs (TM): Community Builder Paid Subscriptions Plugin: cbsubsemail
* @version $Id: cbsubs.email.php 1487 2012-07-15 00:00:12Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage cbsubs.email.php
* @author Beat
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;
use CBLib\Registry\ParamsInterface;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onCPayUserStateChange', 'onCPayUserStateChange', 'getcbsubsemailTab' );
/**
 * CBSubs Email integration plugin handler class
 */
class getcbsubsemailTab extends cbTabHandler {

	/**
	 * Builds and sends e-mail
	 *
	 * @param UserTable  $user
	 * @param string     $mailFrom_email
	 * @param string     $mailFrom_name
	 * @param string     $mailTo
	 * @param string     $mailSubject
	 * @param string     $mailBody
	 * @param string     $mailHtml
	 * @param string     $mailCC
	 * @param string     $mailBCC
	 * @param string     $mailAttachments
	 * @param string[]   $extraStrings
	 * @return void
	 */
	protected function sendMail( $user, $mailFrom_email, $mailFrom_name, $mailTo, $mailSubject, $mailBody, $mailHtml, $mailCC, $mailBCC, $mailAttachments, $extraStrings ) {
		global $_CB_framework;

		cbimport( 'cb.tabs' );

		$cbUser					=	CBuser::getInstance( $user->id );

		if ( ! $cbUser ) {
			return;
		}

		$mailFrom_email			=	trim( $cbUser->replaceUserVars( $mailFrom_email, false, false, array(), false ) );
		$mailFrom_name			=	trim( $cbUser->replaceUserVars( $mailFrom_name, false, false, array(), false ) );
		$mailTo					=	trim( $cbUser->replaceUserVars( $mailTo, false, false, array(), false ) );
		$mailCC					=	trim( $cbUser->replaceUserVars( $mailCC, false, false, array(), false ) );
		$mailBCC				=	trim( $cbUser->replaceUserVars( $mailBCC, false, false, array(), false ) );
		$mailSubject			=	trim( $cbUser->replaceUserVars( CBPTXT::T( $mailSubject ), false, false, $extraStrings, false ) );
		$mailBody				=	trim( $cbUser->replaceUserVars( CBPTXT::T( $mailBody ), false, false, $extraStrings, false ) );
		$mailAttachments		=	trim( $mailAttachments );

		if ( $mailTo != '' ) {
			$mailTo				=	preg_split( '/ *, */', $mailTo );
		} else {
			return;
		}

		if ( $mailCC != '' ) {
			$mailCC				=	preg_split( '/ *, */', $mailCC );
		} else {
			$mailCC				=	null;
		}

		if ( $mailBCC != '' ) {
			$mailBCC			=	preg_split( '/ *, */', $mailBCC );
		} else {
			$mailBCC			=	null;
		}

		if ( $mailAttachments != '' ) {
			$mailAttachments	=	preg_split( '/ *, */', $mailAttachments );
		} else {
			$mailAttachments	=	null;
		}

		if ( $mailTo && ( $mailSubject || $mailBody ) ) {
			comprofilerMail( $mailFrom_email, $mailFrom_name, $mailTo, $_CB_framework->getCfg( 'sitename' ).' - '.$mailSubject, $mailBody, $mailHtml, $mailCC, $mailBCC, $mailAttachments );
		}
	}

	/**
	 * Called at each change of user subscription state due to a plan activation or deactivation
	 *
	 * @param  UserTable        $user               The user owning the $subscription with that $planId
	 * @param  string           $status             New status: 'A'=Active, 'X'=Expired, 'C'=Cancelled
	 * @param  int              $planId             Plan Id which is changing status
	 * @param  int              $replacedPlanId     Replaced Plan Id in case of an upgrade
	 * @param  ParamsInterface  $integrationParams  Integration parameters for that plan $planId
	 * @param  string           $cause              'PaidSubscription' (first activation only), 'SubscriptionActivated' (renewals, cancellation reversals), 'SubscriptionDeactivated', 'Denied'
	 * @param  string           $reason             'N' new subscription, 'R' renewal, 'U'=update )
	 * @param  int              $now                Unix time
	 * @param  cbpaidSomething  $subscription       Subscription/Donation/Merchandise record
	 * @param  int              $autorenewed        0: not auto-renewing (manually renewed), 1: automatically renewed (if $reason == 'R')
	 * @return void
	 */
	public function onCPayUserStateChange( $user, $status, /** @noinspection PhpUnusedParameterInspection */ $planId, /** @noinspection PhpUnusedParameterInspection */ $replacedPlanId, $integrationParams, $cause, $reason, /** @noinspection PhpUnusedParameterInspection */ $now, $subscription, $autorenewed ) {
		if ( ! is_object( $user ) ) {
			return;
		}

		$event			=	null;

		if ( ( $status == 'A' ) && ( $cause == 'PaidSubscription' ) && ( $reason != 'R' ) ) {
			$event		=	'activation';
		} elseif ( ( $status == 'A' ) && ( $cause == 'PaidSubscription' ) && ( $reason == 'R' ) && ( $autorenewed == 0 ) ) {
			$event		=	'renewal';
		} elseif ( ( $status == 'A' ) && ( $cause == 'PaidSubscription' ) && ( $reason == 'R' ) && ( $autorenewed == 1 ) ) {
			$event		=	'autorenewal';
		} elseif ( ( $status == 'X' ) && ( $cause != 'Pending' ) ) {
			$event		=	'expired';
		} elseif ( ( $status == 'C' ) && ( $cause != 'Pending' ) ) {
			$event		=	'deactivation';
		} elseif ( ( $cause == 'Pending' ) && ( $reason != 'R' ) && ( $autorenewed == 0 ) ) {
			$event		=	'pendingfirst';
		} elseif ( ( $cause == 'Pending' ) && ( $reason == 'R' ) && ( $autorenewed == 0 ) ) {
			$event		=	'pendingrenewal';
		}

		if ( $event ) {
			$fromName		=	$integrationParams->get( 'cbemail_name_' . $event, null );
			$fromEmail		=	$integrationParams->get( 'cbemail_address_' . $event, null );
			$aTo			=	$integrationParams->get( 'cbemail_to_' . $event, null );
			$aCC			=	$integrationParams->get( 'cbemail_cc_' . $event, null );
			$aBCC			=	$integrationParams->get( 'cbemail_bcc_' . $event, null );
			$aSub			=	$integrationParams->get( 'cbemail_sub_' . $event, null );
			$aMsg			=	$integrationParams->get( 'cbemail_msg_' . $event, null );
			$aAtch			=	$integrationParams->get( 'cbemail_atch_' . $event, null );
			$aType			=	(int) $integrationParams->get( 'cbemail_type_' . $event, 0 );
			$extraStrings	=	$subscription->substitutionStrings( $aType == 1 );
			$this->sendMail( $user, $fromEmail, $fromName, $aTo, $aSub, $aMsg, $aType, $aCC, $aBCC, $aAtch, $extraStrings );
		}
	}
}
