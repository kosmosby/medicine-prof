<?php
/**
* @version $Id: cbsubs.url.php 1489 2012-07-16 14:57:45Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;
use CBLib\Registry\ParamsInterface;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onCPayUserStateChange', 'onCPayUserStateChange', 'getcbsubsurlTab' );

/**
 * CBSubs URL integration plugin class
 */
class getcbsubsurlTab extends cbTabHandler {
	/**
	 * Called at each change of user subscription state due to a plan activation or deactivation
	 *
	 * @param  UserTable        $user
	 * @param  string           $status
	 * @param  int              $planId
	 * @param  int              $replacedPlanId
	 * @param  ParamsInterface  $integrationParams
	 * @param  string           $cause              'PaidSubscription' (first activation only), 'SubscriptionActivated' (renewals, cancellation reversals), 'SubscriptionDeactivated', 'Denied'
	 * @param  string           $reason             'N' new subscription, 'R' renewal, 'U'=update
	 * @param  int              $now                Unix time
	 * @param cbpaidSomething   $subscription
	 */
	public function onCPayUserStateChange( &$user, $status, $planId, $replacedPlanId, &$integrationParams, $cause, $reason, /** @noinspection PhpUnusedParameterInspection */ $now, &$subscription ) {
		global $_CB_framework;
		
		if ( ! $user ) {
			return;
		}
		
		$event		=	null;
		
		if ( ( $status == 'A' ) && ( $cause == 'PaidSubscription' ) && ( $reason != 'R' ) ) {
			$event	=	'activation';
		} elseif ( ( $status == 'A' ) && ( $cause == 'PaidSubscription' ) && ( $reason == 'R' ) ) {
			$event	=	'renewal';
		} elseif ( ( $status == 'X' ) && ( $cause != 'Pending' ) ) {
			$event	=	'expiration';
		} elseif ( ( $status == 'C' ) && ( $cause != 'Pending' ) ) {
			$event	=	'deactivation';
		}
		
		if ( $event ) {
			$path									=	$integrationParams->get( 'url_path_' . $event, null );
			$method									=	$integrationParams->get( 'url_method_' . $event, 'GET' );
			$results								=	$integrationParams->get( 'url_results_' . $event, 0 );
			
			if ( $path ) {

				// add substitutions for: [plan_id], [replaced_plan_id], [subscription_id], [parent_plan_id], [parent_subscription_id]
				$extraStringsLocal					=	array(
													'plan_id'					=>	(int) $planId,
													'replaced_plan_id'			=>	(int) $replacedPlanId,
													'subscription_id'			=>	(int) $subscription->id,
													'parent_plan_id'			=>	(int) $subscription->parent_plan,
													'parent_subscription_id'	=>	(int) $subscription->parent_subscription
													);
				$extraStrings						=	array_merge( $subscription->substitutionStrings( false ), $extraStringsLocal );

				cbimport( 'cb.snoopy' );
				
				$cbUser								=&	CBuser::getInstance( $user->id );
				
				if ( ! $cbUser ) {
					return;
				}
				
				$path								=	trim( $cbUser->replaceUserVars( $path, array( $this, '_urlencode' ), false, $extraStrings, false ) );
				$snoopy								=	new CBSnoopy();
				$snoopy->read_timeout				=	30;
				
				switch ($method ) {
					case 'POST':
						$post						=	$integrationParams->get( 'url_post_' . $event, null );
						$formvar					=	array();

						if ( $post ) {
							$formvars				=	explode( "\n", $post );
							foreach ( $formvars as $vars ) {
								$var				=	explode( '=', trim( $vars ), 2 );
								if ( count( $var ) == 2 ) {
									$key			=	trim( $var[0] );
									$value			=	trim( $cbUser->replaceUserVars( $var[1], false, false, $extraStrings, false ) );
									$formvar[$key]	=	$value;
								}
							}
						}
						
						$snoopy->submit( $path, $formvar );
						break;

					case 'XML':
						$xmlText					=	$integrationParams->get( 'url_xml_' . $event, null );
						$xmlText					=	trim( $cbUser->replaceUserVars( $xmlText, array( $this, '_htmlspecialchars' ), false, $extraStrings, false ) );
						$formvar					=	array( 'xml' => $xmlText );
						$snoopy->set_submit_xml();
						$snoopy->submit( $path, $formvar );
						break;

					case 'GET':
					default:
						$snoopy->fetch( $path );
						break;
				}

				if ( $results && ( ! $snoopy->error ) && ( $snoopy->status == 200 ) && $snoopy->results && ( $_CB_framework->getUi() == 1 ) ) {
					// display only in frontend:
					echo '<div class="CBSubsURL_Results_' . (int) $planId . '">' . $snoopy->results . '</div>';
				}
			}
		}
	}
	/**
	 * Utility function for CBuser::replaceUserVars to urlencode() all CB substitutions
	 *
	 * @param  string  $str
	 * @return string
	 */
	public function _urlencode( $str ) {
		return urlencode( $str );
	}

	/**
	 * Utility function for CBuser::replaceUserVars to htmlspecialchars() all CB substitutions
	 *
	 * @param  string  $str
	 * @return string
	 */
	public function _htmlspecialchars( $str ) {
		return htmlspecialchars( $str, ENT_COMPAT, 'UTF-8' );
	}
}
