<?php
/**
 * @version $Id: cbpaidSomethingMgr.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class to load the subscriptions somethings of any type
 *
 */
class cbpaidSomethingMgr {
	public $_db;

	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	private function __construct( &$db = null ) {
		if ( is_null( $db ) ) {
			global $_CB_database;
			$db			=&	$_CB_database;
		}
		$this->_db		=&	$db;
	}
	/**
	 * Gets a single instance of the cbpaidSomethingMgr class
	 * @static
	 *
	 * @return cbpaidSomethingMgr
	 */
	public static function & getInstance( ) {
		static $singleInstance	=	null;
		if ( $singleInstance === null ) {
			$singleInstance		=	new self();
		}
		return $singleInstance;
	}
	/**
	 * Loads a subscription of a plan and returns an object of the corresponding class
	 *
	 * @param  int  $planId          Id of plan
	 * @param  int  $subscriptionId  Id of subscription within the plan $planId
	 * @return cbpaidSomething       The Something of plan $planId and subscription-id $subscriptionId
	 */
	public function & loadSomething( $planId, $subscriptionId ) {
		static $_planSubscriptions		=	array();
		$planId							=	(int) $planId;
		$subscriptionId					=	(int) $subscriptionId;

		if ( ! isset( $_planSubscriptions[$planId][$subscriptionId] ) ) {
			$plansMgr														=&	cbpaidPlansMgr::getInstance();	// can't access $this here as in static call.
			if ( $planId ) {
				$plan														=&	$plansMgr->loadPlan( $planId );
				if ( $plan ) {
					if ( $subscriptionId ) {

						$_planSubscriptions[$planId][$subscriptionId]		=	$plan->newSubscription();
						/** @noinspection PhpUndefinedMethodInspection (Due to limitation of IDE) */
						if ( $_planSubscriptions[$planId][$subscriptionId]->load( $subscriptionId ) ) {
							if ( $_planSubscriptions[$planId][$subscriptionId]->plan_id == $planId ) {

								return $_planSubscriptions[$planId][$subscriptionId];

							}
							cbpaidApp::setLogErrorMSG( 5, $plan, sprintf( 'loadSomething::planid %d of subid %d does not match planid %d.', $_planSubscriptions[$planId][$subscriptionId]->plan_id, $subscriptionId, $planId ), null );
						} else {
							cbpaidApp::setLogErrorMSG( 5, $plan, sprintf( 'loadSomething::subid %d with planid %d could not load.', $subscriptionId, $planId ), null );
						}
						unset( $_planSubscriptions[$planId][$subscriptionId] );

					} else {
						cbpaidApp::setLogErrorMSG( 5, $plan, sprintf( 'loadSomething::plan id: %d but no subscription id.', $planId ), null );
					}
				} else {
					cbpaidApp::setLogErrorMSG( 5, $plan, sprintf( 'loadSomething::plan id: %d is missing in database for subscription id: %d', $planId, $subscriptionId ), null );
				}

				$null													=	null;
				return $null;

			} else {
				trigger_error( 'loadSomething::no plan id.', E_USER_ERROR );
				exit;
			}
		}
		return $_planSubscriptions[$planId][$subscriptionId];
	}
	/**
	 * Loads all of Something of user $user.
	 *
	 * @param  UserTable          $user      The user
	 * @param  string             $status    Optionally the status of the something ('A'=active, 'X'=expired, and so on)
	 * @return cbpaidSomething[][]
	 */
	public static function getAllSomethingOfUser( $user, $status = 'A' ) {
		static $somethings										=	array();

		$statusIndex		=	( $status ? $status : 'ZZ' );
		if ( ! isset( $somethings[$user->id][$statusIndex] ) ) {
			$somethings[$user->id][$statusIndex]				=	array();
			$plansMgr											=&	cbpaidPlansMgr::getInstance();
			$plans												=&	$plansMgr->loadPublishedPlans( $user, true, 'any', null );
			foreach ( $plans as $plan ) {
				// $plan = NEW cbpaidProduct();
				if ( ! isset( $somethings[$user->id][$statusIndex][$plan->item_type] ) ) {
					$somethings[$user->id][$statusIndex][$plan->item_type]	=	$plan->newSubscription()->loadTheseSomethingsOfUser( $user->id, $status );
				}
			}
		}
		return $somethings[$user->id][$statusIndex];
	}
}	// class cbpaidSomethingMgr
