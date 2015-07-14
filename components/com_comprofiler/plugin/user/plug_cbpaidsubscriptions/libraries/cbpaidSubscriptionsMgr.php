<?php
/**
 * @version $Id: cbpaidSubscriptionsMgr.php 1608 2012-12-29 04:12:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;
use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class to manage subscriptions
 *
 */
class cbpaidSubscriptionsMgr {
	protected $_upgradesCache						=	array();

	/**
	 * Gets a single instance of the cbpaidSubscriptionsMgr class
	 *
	 * @return cbpaidSubscriptionsMgr
	 */
	public static function & getInstance( ) {
		static $singleInstance						=	null;

		if ( $singleInstance === null ) {
			$singleInstance							=	new self();
		}
		return $singleInstance;
	}
	/**
	 * Returns an array with $accessPlans and their children as available for upgrades for $user at this time
	 *
	 * @param  int[]      $accessPlans
	 * @param  UserTable  $user
	 * @return int[]
	 */
	public function getUpgradablePlansWithChildrensForPlans( $accessPlans, $user ) {
		global $_CB_framework;

		// load active subscriptions into $activeSubscriptions and plans to which user can upgrade into $upgradePlans
		$activeSubscriptions						=	array();
		$upgradePlans								=	cbpaidSubscriptionsMgr::getInstance()->getUpgradeAndRenewalPossibilities( 1, ( $user ? $user->id : null ), $_CB_framework->now(), $activeSubscriptions, $accessPlans, 1, true );

		$accessPlansWithChildren					=	array();
		foreach ( $upgradePlans as $plan ) {
			if ( in_array( $plan->id, $accessPlans ) || in_array( $plan->get( 'parent' ), $accessPlans ) ) {
				$accessPlansWithChildren[]			=	$plan->id;
			}
		}
		return $accessPlansWithChildren;
	}
	/**
	 * Checks for upgrade or renewal possibilities
	 *
	 * @param  int                   $ui                     1=frontend, 2=backend
	 * @param  int                   $user_id
	 * @param  int                   $now                    system unix time
	 * @param  cbpaidUsersubscriptionRecord[]  $subscriptionsReturned  RETURNED: current subscriptions
	 *                                                               with ->status = 'A' for active ones and 'X' for expired ones. 'R' unpaid, 'C' cancelled.
	 * @param  array|null            $plansToShowOnly        array of specific plan numbers to show (so we add these plans if allowed and not spontaneous in frontend
	 * @param  int                   $subsAccess             0 has only read access, 1 has user access, 2 reserved for future Super-admin access
	 * @param  boolean               $plansToShowOnlyDoIncludeChildren  Include children with plansToShowOnly
	 * @return cbPaidProduct[]                               upgrade possibilities including _renewalDiscount in plan's currency
	 */
	public function & getUpgradeAndRenewalPossibilities( $ui, $user_id, $now, &$subscriptionsReturned, $plansToShowOnly = null, $subsAccess = 1, $plansToShowOnlyDoIncludeChildren = false ) {
		global $_CB_database, $_CB_framework;

		if ( ! isset( $this->_upgradesCache[$user_id] ) ) {
			$quantity								=	1;			//TBD later !

			$paidUserExtension						=&	cbpaidUserExtension::getInstance( $user_id );
			$subscriptions							=	$paidUserExtension->getUserSubscriptions( null, true );

			$user									=	CBuser::getUserDataInstance( (int) $user_id );
			$plansMgr								=&	cbpaidPlansMgr::getInstance();
			$plans									=&	$plansMgr->loadPublishedPlans( $user, true, 'any', null );		//TBD LATER: upgrades limiting owners

			$params									=&	cbpaidApp::settingsParams();
			$enableFreeRegisteredUser				=	$params->get( 'enableFreeRegisteredUser', 1 );
			$createAlsoFreeSubscriptions			=	$params->get( 'createAlsoFreeSubscriptions', 0 );

			$noValidSubscriptionFound				=	true;
			$subscriptionsUpgradePlansIdsDiscount	=	array();	// array: [$k][$upgrade_plan->id]=discountedPrice  where $l is index in $subscriptions
			$activeExclusiveSubChild				=	array();	// array: [$parentPlanId] = true
			$notProposedParents						=	array();

			foreach ( array_keys( $subscriptions ) as $k ) {
				// for each user subscription:
				// 1. check if it's plan can be shown as an extra subscription possibility and/or upgrade,
				$subscriptions[$k]->checkRenewalUpgrade( $ui, $user, $quantity, $now, $subsAccess );

				// 2. don't propose subscription which can not be shown to the user
				if ( $subscriptions[$k]->_hideItsPlan && isset( $plans[$subscriptions[$k]->plan_id] ) ) {
					$plans[$subscriptions[$k]->plan_id]->_drawOnlyAsContainer		=	true;
					// $notProposedParents[$subscriptions[$k]->plan_id]				=	true;
				}
				if (  ( $subscriptions[$k]->_hideThisSubscription || ! $subscriptions[$k]->checkIfValid( $now ) )
					&& ( isset( $plans[$subscriptions[$k]->plan_id] ) && ( $plans[$subscriptions[$k]->plan_id]->get( 'multiple') == 0 ) ) )
				{
					foreach ( array_keys( $plans ) as $pk ) {
						// hidden or inactive subscription: do not display any of its children plans as upgrade possibility:
						if ( $plans[$pk]->get( 'parent' ) == $subscriptions[$k]->plan_id ) {
							$plans[$pk]->_drawOnlyAsContainer		=	true;
							$notProposedParents[$pk]				=	true;
						}
					}
				}
				if ( $subscriptions[$k]->_hideThisSubscription ) {
					unset( $subscriptions[$k] );
				} elseif ( $subscriptions[$k]->checkIfValid( $now ) ) {
					// 3. all upgrade possibilities of this subscription
					$noValidSubscriptionFound						=	false;
					$subscriptionsUpgradePlansIdsDiscount[$k]		=	$subscriptions[$k]->_upgradePlansIdsDiscount;
					if ( $subscriptions[$k]->getPlanAttribute( 'exclusive' ) == 1 ) {
						$activeExclusiveSubChild[$subscriptions[$k]->getPlanAttribute( 'parent' )]	=	true;
					}
				} else {
				}
			}

			// add to each plan the subscriptions which can be upgraded: plan, subscription and price:
			foreach ( array_keys( $plans ) as $pk ) {
				foreach ( $subscriptionsUpgradePlansIdsDiscount as $k => $upgradePlansDiscount ) {
					foreach ( $upgradePlansDiscount as $planId => $discountedPrice ) {
						if ( $plans[$pk]->get( 'id' ) == $planId ) {
							$plans[$pk]->_subscriptionToUpdate		=	array( $subscriptions[$k]->plan_id, $subscriptions[$k]->id );
							$plans[$pk]->_renewalDiscount			=	$discountedPrice;
						}
					}
				}
			}

			// finally remove all plans not allowed for upgrade and
			// also all exclusive plans which can't be upgraded to by no subscription
			// (already subscribed plans have already been removed by plan's _hideItsPlan instructions):
			// also memorize them as removed parent, so that children are not proposed either:
			foreach ( array_keys( $plans ) as $pk ) {
				$exclPlan											=	$plans[$pk]->get( 'exclusive' );
				$resultTexts										=	array();

				// remove plans not listed by default and not specifically selected:
				if (   ( ! $plans[$pk]->isPlanAllowingUpgradesToThis( $user_id, $resultTexts ) )
					|| ( ( ( $plans[$pk]->get( 'propose_upgrade' ) != 1 ) && ( $ui != 2 ) ) && ! ( $plansToShowOnly && ( in_array( $pk, $plansToShowOnly ) || ( $plansToShowOnlyDoIncludeChildren && in_array( $plans[$pk]->get( 'parent' ), $plansToShowOnly ) ) ) ) )
					|| ( ( $exclPlan == 1 )
						&& ( $plans[$pk]->get( 'multiple' ) == 0 )
						&& isset( $activeExclusiveSubChild[$plans[$pk]->get( 'parent' )] )
						&& ( $plans[$pk]->_subscriptionToUpdate === null ) ) )
				{
					// if ( $ui == 1 ) {	// when we are in frontend:
					if ( ! ( isset( $plans[$pk]->_drawOnlyAsContainer ) && ( $plans[$pk]->_drawOnlyAsContainer ) ) ) {
						$plans[$pk]->_drawOnlyAsContainer			=	true;
						$notProposedParents[$pk]					=	true;
					}
				}
			}
			// very finally remove also children of non-authorized parent plans:
			// second case is that parent plan isn't published:
			foreach ( array_keys( $plans ) as $pk ) {
				$parentPlanId										=	$plans[$pk]->get( 'parent' );
				if ( $parentPlanId && ( isset( $notProposedParents[$parentPlanId] ) || ! isset( $plans[$parentPlanId] ) ) ) {
					$plans[$pk]->_drawOnlyAsContainer				=	true;
				}
			}


			// If no sbscriptions at all or no active/registered ones, and the corresponding setting allows it:
			// Find the first free lifetime one with Registered level:
			if ( ( ( count( $subscriptions ) == 0 ) || $noValidSubscriptionFound ) && $enableFreeRegisteredUser && ! $createAlsoFreeSubscriptions ) {
				$firstFreePlanId									=	null;
				$registeredUserGroup								=	$_CB_framework->getCfg( 'new_usertype' );
				foreach ( $plans as $v ) {
					if ( $v->isLifetimeValidity() && $v->isFree() && in_array( $v->get( 'usergroup' ), array( $registeredUserGroup, 0 ) ) ) {
						if ( $firstFreePlanId === null ) {
							$firstFreePlanId						=	$v->get( 'id' );
						}
						break;
					}
				}
				if ( $firstFreePlanId ) {
					$freeSub										=	new cbpaidUsersubscriptionRecord( $_CB_database );
					$freeSub->createSubscription( $user_id, $plans[$firstFreePlanId],  null, null, 'A', false );
					array_unshift( $subscriptions, $freeSub );
					$plans[$firstFreePlanId]->_drawOnlyAsContainer	=	true;
				}
			}
			$this->_upgradesCache[$user_id]							=	array( 'subscriptions' => &$subscriptions, 'plans' => &$plans );
		}
		$subscriptionsReturned										=	$this->_upgradesCache[$user_id]['subscriptions'];
		return $this->_upgradesCache[$user_id]['plans'];
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
	public function checkExpireMe( /** @noinspection PhpUnusedParameterInspection */ $functionName, $userId = null, $redirect = true ) {
		global $_CB_framework;

		static $notCalled			=	true;
		static $notMassExpired		=	true;
		if ( $notCalled && ( $_CB_framework->GetUi() == 1 ) ) {

			if ( $userId == null ) {
				$params				=	cbpaidApp::settingsParams();
				if ( $notMassExpired && $params->get( 'massexpirymethod', 0 ) == 1 ) {
					// mass-expire 10 subscriptions at a time on the way if not exipring a particular user:
					$plansMgr		=&	cbpaidPlansMgr::getInstance();
					$plansMgr->checkAllSubscriptions( 10 );
					$notMassExpired	=	false;
				}
				$userId				=	$_CB_framework->myId();
			}
			if ( $userId ) {
				// make sure to not check more than once:
				$notCalled			=	false;

				$null				=	null;

				$paidUserExtension	=&	cbpaidUserExtension::getInstance( $userId );
				$subscriptionOk		=	$paidUserExtension->checkUserSubscriptions( false, $null, 'X', true );
				if ( ! $subscriptionOk ) {
					if ( $redirect ) {
						$this->_redirectExpiredMembership( $userId );
						return false;		// if we are already displaying the correct screen...
					} else {
						return false;
					}
				}
			}
		}
		return true;
	}
	/**
	 * Redirects expired user to the re-subscription screen.
	 * @access private
	 * @param  int  $userId
	 */
	protected function _redirectExpiredMembership( $userId ) {
		global $_CB_framework;

		$params						=&	cbpaidApp::settingsParams();

		$paidUserExtension			=&	cbpaidUserExtension::getInstance( $userId );
		$expiredSubscriptions		=	$paidUserExtension->getUserSubscriptions( 'X' );	// check if there is any expired extensions for the text
		if ( count( $expiredSubscriptions ) > 0 ) {
			$textMessage			=	$params->get( 'subscriptionExpiredText', "Your membership has expired." );
			$expiredRedirectLink	=	$params->get( 'subscriptionExpiredRedirectLink' );
		} else {
			$textMessage			=	$params->get( 'subscriptionNeededText', "A membership is needed for access." );
			$expiredRedirectLink	=	$params->get( 'subscriptionNeededRedirectLink' );
		}

		if ( ! $expiredRedirectLink ) {
			$baseClass				=	cbpaidApp::getBaseClass();
			if ( $baseClass ) {
				$expiredRedirectLink	=	$baseClass->_getAbsURLwithParam( array( 'Itemid' => 0, 'account' => 'expired', 'user' => (int) $userId ), 'pluginclass', false );
			} else {
				// without baseClass, as baseClass is not loaded in case of cbpaidsubsbot:
				$cbpPrefix = 'cbp';
				$expiredRedirectLink	=	'index.php?option=com_comprofiler&task=pluginclass&plugin=cbpaidsubscriptions&' . $cbpPrefix . 'account=expired&user=' . (int) $userId;
				// this would work only for logged-in users: 'index.php?option=com_comprofiler&task=pluginclass&plugin=cbpaidsubscriptions&do=display_subscriptions';		// &Itemid= ???
			}
			if ( $userId ) {
				$_SESSION['cbsubs']['expireduser']	=	$userId;
			}
		}

		if ( ( $_CB_framework->getRequestVar( 'option' ) != 'com_comprofiler' ) || ( $_CB_framework->getRequestVar( 'task' ) != 'pluginclass' ) || ( $_CB_framework->getRequestVar( 'plugin' ) != 'cbpaidsubscriptions' ) ) {
			cbRedirect( cbSef( $expiredRedirectLink, false ), CBPTXT::T( $textMessage ), 'warning' );
		}
	}
	/**
	 * USED by XML interface ONLY !!! Renders url for the product
	 *
	 * @param  string           $value    Variable value ( 'massexpire' )
	 * @param  ParamsInterface  $params
	 * @return string                     HTML to display
	 */
	public function renderUrlOfAutoExpiry( $value, &$params ) {
		$url	=	'index.php?option=com_comprofiler&amp;task=pluginclass&amp;plugin=cbpaidsubscriptions&amp;do=' . htmlspecialchars( $value ) . '&amp;key='
			.	md5( $params->get( 'license_number' ) );
		$url	=	cbSef( $url, true, 'raw' );
		return '<a href="' . $url . '" target="_blank">' . $url . '</a>';
	}
} // end class cbpaidSubscriptionsMgr
