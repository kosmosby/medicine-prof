<?php
/**
 * @version $Id: cbpaidUserExtension.php 1608 2012-12-29 04:12:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\FieldTable;
use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class extending user only in memory as abstraction to get user's subscriptions:
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 */
class cbpaidUserExtension extends cbpaidTable {
	public $id;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase|null  $db
	 * @param  int              $user_id
	 */
	public function __construct( &$db = null , $user_id ) {
		$this->id			=	(int) $user_id;
		parent::__construct( '#__users', 'id', $db );
	}
	/**
	 * Gets The reference instance of CBuser for user id, or a new instance if $userId == 0
	 * @static
	 * 	 *
	 * @param  int|null             $userId
	 * @return cbpaidUserExtension
	 */
	public static function & getInstance( $userId ) {
		global $_CB_database;

		static $instances			=	array();

		$userId						=	(int) $userId;
		if ( ! isset( $instances[$userId] ) ) {
			$instances[$userId]		=	new cbpaidUserExtension( $_CB_database, $userId );
		}
		return $instances[$userId];
	}
	/**
	 * Gets value of field id $fieldId from CB user fields record
	 *
	 * @param  int      $fieldId
	 * @param  boolean  $fullAccess    IF true do not take in account current user's viewing rights
	 * @return string|null
	 */
	public function getFieldValue( $fieldId, $fullAccess = false ) {
		$cbUser				=	CBuser::getInstance( (int) $this->id );
		if ( $cbUser ) {
			$fieldContent		=	$cbUser->getField( (int) $fieldId, null, 'php', 'none', 'profile', 0, $fullAccess );
			if ( is_array( $fieldContent ) && ( count( $fieldContent ) > 0 ) ) {
				return array_shift( $fieldContent );
			} elseif ( $fieldContent === null ) {
				//TODO:	REMOVE WHEN CB 1.5 is required:
				// Now this is a quick hack to get a value from a field that is not visible on profile but still in database:
				$user		=	$cbUser->getUserData();
				if ( $user ) {
					$field			=	new FieldTable( $this->_db );
					if ( $field->load( (int) $fieldId ) ) {
						$fieldCol	=	$field->tablecolumns;
						if ( isset( $user->$fieldCol ) ) {
							return $user->$fieldCol;
						}
					}
				}
			}
		}
		return null;
	}
	/**
	 * Sets a CB field id $fieldId to $value
	 *
	 * @param  int      $fieldId
	 * @param  mixed    $value
	 * @param  boolean  $fullAccess    IF true do not take in account current user's viewing rights
	 * @return boolean                 TRUE: Field value could be set, FALSE: User or Field not found
	 */
	public function setFieldValue( $fieldId, $value, $fullAccess = false ) {
		$cbUser								=	CBuser::getInstance( (int) $this->id );
		if ( $cbUser ) {
			$fieldContent					=	$cbUser->getField( (int) $fieldId, null, 'php', 'none', 'profile', 0, $fullAccess );
			$user						=	$cbUser->getUserData();
			if ( $user ) {
				if ( ! ( is_array( $fieldContent ) && ( count( $fieldContent ) > 0 ) ) ) {
					//TODO:	REMOVE WHEN CB 1.5 is required:
					// Now this is a quick hack to get a value from a field that is not visible on profile but still in database:
					$field					=	new FieldTable( $this->_db );
					if ( $field->load( (int) $fieldId ) ) {
						$fieldCol			=	$field->tablecolumns;
						if ( isset( $user->$fieldCol ) ) {
							$fieldContent	=	array( $fieldCol => $user->$fieldCol );
						}
					}
				}
				if ( is_array( $fieldContent ) && ( count( $fieldContent ) > 0 ) ) {
					foreach ( $fieldContent as $fName => $fValue ) {
						$user->$fName			=	$value;
						return true;
					}
				}
			}
		}
		return false;
	}
	/**
	 * Returns the active subscriptions of the user
	 *
	 * @return cbpaidUsersubscriptionRecord[]  indexed by subscription id
	 */
	public function & getActiveSubscriptions( ) {
		$deactivatedSub				=	null;
		$this->checkUserSubscriptions( false, $deactivatedSub, 'X' );
		$subs						=&	$this->getUserSubscriptions( 'A' );
		return $subs;
	}
	/**
	 * gets the cbpaidUsersubscriptionRecord only (not other somethings for now !) of this user
	 *
	 * @param  string   $status      '': any status, 'A': only active ones, 'R': only registered ones
	 * @param  boolean  $ordered     TRUE: sort subscriptions by plan sorting, FALSE (default): doesn't sort subscriptions
	 * @return cbpaidUsersubscriptionRecord[]  indexed by subscription id
	 */
	public function & getUserSubscriptions( $status = '', $ordered = false ) {
		static $subsCache	=	array();

		if ( $status == 'clearcache' ) {
			unset( $subsCache[$this->id] );
			$null			=	null;
			return $null;
		}
		$statusIndex		=	( $status ? $status : 'ZZ' );
		if ( ! isset( $subsCache[$this->id][$statusIndex][$ordered] ) ) {

			if ( $this->id ) {
				$this->_db->setQuery( "SELECT s.* FROM #__cbsubs_subscriptions s"
					. ( $ordered ?	"\n LEFT JOIN #__cbsubs_plans AS a ON a.`id` = s.`plan_id`"
						. "\n LEFT JOIN #__cbsubs_plans AS b ON b.`id` = a.`parent`" : "" )
					. "\n WHERE s.user_id = " . (int) $this->id
					. ( $status ?	"\n AND s.status = " . $this->_db->Quote( $status ) : "" )
					. ( $ordered ?	"\n GROUP BY s.`id`"
						. "\n ORDER BY IF( ISNULL( b.`ordering` ) , a.`ordering`, b.`ordering` )  ASC, IF( ISNULL( b.`ordering` ) , a.`ordering`, 11000 + a.`ordering` )  ASC"
						:	( $status ? "\n ORDER BY s.plan_id" : "" ) )	// this allows to use fully the existing index (no performance hit for that sorting)
				);
				$subscriptions	=&	$this->loadTrueObjects( 'cbpaidUsersubscriptionRecord', 'id' );
			} else {
				$subscriptions	=	array();
			}

			$subsCache[$this->id][$statusIndex][$ordered]	=&	$subscriptions;
		}
		return $subsCache[$this->id][$statusIndex][$ordered];
	}
	/**
	 * Checks for upgrade or renewal possibilities
	 *
	 * @param  boolean                       $newSubsActivation   called to activate new subscriptions
	 * @param  cbpaidUsersubscriptionRecord|null       $deactivatedSub      just deactivated subscription before (and reason for) calling this method (or NULL)
	 * @param  string                        $reason              checking reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  boolean                       $forceCheck
	 * @return boolean|null                                       TRUE: any valid subscription found, FALSE: no valid subscription, NULL: no $forceCheck and no changes
	 */
	public function checkUserSubscriptions( $newSubsActivation, &$deactivatedSub, $reason, $forceCheck = false ) {
		global $_CB_framework;

		$params														=&	cbpaidApp::settingsParams();

		// as this method beeing called e.g. whenever a subscription is deactivated, it's calling itself through expireIfExpired:
		static $recurringStopper									=	0;
		static $justExpiredSubs									=	array();

		$user_id													=	$this->id;
		if ( ! $user_id ) {
			return null;
		}
		$user														=	CBuser::getUserDataInstance( $user_id );
		if ( ! $user ) {
			return null;
		}

		if ( $deactivatedSub !== null ) {
			$justExpiredSubs[$deactivatedSub->id]					=	$deactivatedSub;
		}
		while ( $recurringStopper++ == 0 ) {
			// get all subscriptions with status 'A' Active:
			$subscriptions											=	$this->getUserSubscriptions( 'A' );

			// check for just expired subscriptions within status 'A' Active and expires them now:
			foreach ( array_keys( $subscriptions ) as $k ) {
				if ( ! isset( $justExpiredSubs[$k] ) ) {
					$exp											=	$subscriptions[$k]->expireIfExpired();
					if ( $exp ) {
						$justExpiredSubs[$k]						=	$subscriptions[$k];
					}
				}
			}
			// check if no recurring occured:
			if ( $recurringStopper == 1 ) {
				// no recurrings, can stop at next while:
				$recurringStopper									=	-1;
			} else {
				// recurring occured, need to recheck everything:
				$recurringStopper									=	0;
			}
		}
		if ( $recurringStopper == 0 ) {
			if ( $newSubsActivation || ( count( $justExpiredSubs ) > 0 ) ) {
				// some subscription(s) just expired, we now need to adapt user's ACL:

				$remainingSubscriptions								=	$this->getUserSubscriptions( 'A' );
				if ( count( $justExpiredSubs ) > 0 ) {
					// Just expired Gids: collect Gids to remove:
					list( /*$oldBlock*/, $removeGids, $oldChldGids )	=	$this->_computeMaxBlockGid( $user, $justExpiredSubs );
				} else {
					$removeGids										=	array();
					$oldChldGids									=	array();
				}
				// Collect current Gids (and their children Gids):
				list( $block, $gids, $curChldGids )					=	$this->_computeMaxBlockGid( $user, $remainingSubscriptions );
				// Add old children gids and current children Gids to make sure we're allowed to change gid in j 1.5- through multiple plans and usergroups:
				$oldChldGids										=	array_merge( $oldChldGids, $curChldGids );
				// Now adjust the blocking and gids:
				$this->_adjustUserAclBlock( $user, 'PaidSubscription', $block, $gids, $removeGids, $oldChldGids, $reason );
				return ( $block == 0 );
			} else {
				if ( $forceCheck ) {
					$enableFreeRegisteredUser						=	$params->get( 'enableFreeRegisteredUser', 1 );
					$remainingSubscriptions							=	$this->getUserSubscriptions( 'A' );
					$block											=	( ( count( $remainingSubscriptions ) == 0 ) && ( ! $enableFreeRegisteredUser ) ) ? 1 : 0;
					if ( $block == 1 ) {
						list( $block, $gids, /*$childGids*/ )		=	$this->_computeMaxBlockGid( $user, $remainingSubscriptions );
						// Get all gids of all plans that are available to $user_id: This will allow to not change any GID of a user which is not included in those GIDS (e.g. super-admin) in j 1.5-:
						$oldPotentialPlansGids						=	$this->_allPlansGid( $user );
						$removeGids									=	array();
						$this->_adjustUserAclBlock( $user, 'PaidSubscription', $block, $gids, $removeGids, $oldPotentialPlansGids, $reason );

						if ( ( ! self::_allValuesOfArrayInArray( (array) $user->gids, $oldPotentialPlansGids ) )
							|| in_array( $_CB_framework->acl->mapGroupNamesToValues( 'Superadministrator' ), (array) $user->gids ) )
						{
							// Do not block a user that has a gid in his gids that is not controlled by a plan that was accessible to him after downgrade:
							// This avoids blocking e.g. super admins if there is no super-admin plan.
							// But also if there is a Super-admin plan by configuration error, it still should not get blocked:
							$block									=	0;
						}
					}
					return ( $block == 0 );
				}
			}
		}
		return null;
	}
	/**
	 * Computes user's ACL and block depending on list of subscriptions.
	 *
	 * @param  UserTable                       $user
	 * @param  cbpaidUsersubscriptionRecord[]  $subscriptions
	 * @return array                                           ( $block, $gids, $allGids )
	 */
	public function _computeMaxBlockGid( $user, &$subscriptions ) {
		global $_CB_framework;

		$params								=&	cbpaidApp::settingsParams();
		$enableFreeRegisteredUser			=	$params->get( 'enableFreeRegisteredUser', 1 );

		// Determine highest new $gid (0 means nothing left):
		$registeredGid						=	(int) $_CB_framework->acl->mapGroupNamesToValues( 'Registered' );
		$gids								=	array( $registeredGid );	// default minimum acl gid
		$allGids							=	array( $registeredGid );
		$block								=	0;							// don't block by default
		if ( count( $subscriptions ) ) {
			$activeGids						=	array();
			foreach ( $subscriptions as $sub ) {
				$subGid						=	(int) $sub->getPlanAttribute( 'usergroup' );
				if ( $subGid ) {
					$activeGids[$subGid]	=	$subGid;
				}
			}

			if ( count( $activeGids ) > 0 ) {
				$activeGids[$registeredGid]	=	$registeredGid;
				$gids					=	array_values( $activeGids );
				$allGids				=	$gids;
			}

		} elseif ( ( ! $enableFreeRegisteredUser ) && ( ! in_array( $_CB_framework->acl->mapGroupNamesToValues( 'Superadministrator' ), (array) $user->gids ) ) ) {
			$block							=	1;
		}
		return array( $block, $gids, $allGids );
	}
	/**
	 * Gets all plans GIDs
	 *
	 * @param  UserTable  $user           User to check for
	 * @return array                      ( $block, $gid )
	 */
	protected function _allPlansGid( $user ) {
		global $_CB_framework;

		// Determine highest new $gid (0 means nothing left):
		$registeredGid						=	(int) $_CB_framework->acl->mapGroupNamesToValues( 'Registered' );
		$allGids							=	array( $registeredGid => $registeredGid );

		$plansMgr							=&	cbpaidPlansMgr::getInstance();
		$plans								=&	$plansMgr->loadPublishedPlans( $user, true, 'any', null );
		foreach ( $plans as $p ) {
			$planGid						=	(int) $p->get( 'usergroup' );
			if ( $planGid ) {
				$allGids[$planGid]			=	$planGid;
			}
		}
		return $allGids;
	}
	/**
	 * Adjusts user's ACL and block user if no subscriptions left.
	 *
	 * @param  UserTable  $user
	 * @param  string     $cause          cause of the change
	 * @param  int        $block          1: block user, 0: don't block
	 * @param  int[]      $gids           new ACL gids (in Joomla < 1.6: only 1 entry)
	 * @param  int[]      $removeGids     old ACL gids from plans that just expired
	 * @param  array      $oldChldGids    old ACL gids and children
	 * @param  string     $reason         reason of change: 'N'=new subscription (default), 'R'=renewal, 'U'=update + 'X'=expiries
	 */
	protected function _adjustUserAclBlock( $user, /** @noinspection PhpUnusedParameterInspection */ $cause, $block, $gids, $removeGids, $oldChldGids, $reason ) {
		global $_CB_framework;

		if ( $user && $user->id ) {
			if ( self::_anyValueOfArrayInArray( (array) $user->gids, $oldChldGids ) ) {
				// test above is to not degrade higher-level users !
				$wasBlocked							=	$user->block;
				$gidsUniqueSorted					=	array_unique( $gids );
				sort( $gidsUniqueSorted, SORT_NUMERIC );
				$userGids							=	array_unique( (array) $user->gids );
				sort( $userGids, SORT_NUMERIC );
				if ( ( $block != $wasBlocked ) || ( $userGids != $gidsUniqueSorted ) ) {
					$superadministratorgid			=	$_CB_framework->acl->mapGroupNamesToValues( 'Superadministrator' );
					$k_SA							=	array_search( $superadministratorgid, $removeGids );
					if ( $k_SA !== false ) {
						unset( $removeGids[$k_SA] );
					}

					$user->gids						=	array_unique( array_merge( array_diff( $userGids, $removeGids ), $gidsUniqueSorted ) );
					sort( $user->gids, SORT_NUMERIC );

					if ( $block && ! in_array( $superadministratorgid, (array) $user->gids ) ) {
						$user->block				=	1;
					}
					$oldPwd							=	$user->password;
					$user->password					=	null;		// don't update cleartext password in case of registration
					$user->store();					// takes care of Mambo/Joomla ACL tables and many other stuff
					$user->password					=	$oldPwd;
					if ( checkJversion() == 2 ) {
						// This is needed for instant adding of groups to logged-in user (fixing bug #3581): Can be removed once CB 1.9 is released:
						$session					=	JFactory::getSession();
						$jUser						=	$session->get( 'user' );

						if ( $jUser->id == $user->id ) {
							JAccess::clearStatics();
							$session->set( 'user', new JUser( (int) $user->id ) );
						}
					}
					if ( ( $block == 0 ) && ( $wasBlocked == 1 ) ) {
						$messagesToUser			=	( ( $reason == 'N' ) && ( ( ! $user->lastvisitDate ) || ( $user->lastvisitDate == '0000-00-00 00:00:00' ) ) );
						activateUser( $user, 0, 'PaidSubscription', $messagesToUser, $messagesToUser );	//TBD:	//FIXME		Don't ACTIVATE user 2nd time if this function is called from subscription->activate as a result of onUserActivate?
					}
				}
			}
		} else {
			user_error( sprintf( 'AdjustUserAclBlock: user id %d not found in users table.', $user->id ), E_USER_NOTICE );
		}
	}
	/**
	 * Is any of the values of array $a1 in array $a2 ?
	 *
	 * @param  array    $a1
	 * @param  array    $a2
	 * @return boolean
	 */
	protected static function _anyValueOfArrayInArray( $a1, $a2 ) {
		foreach ( $a1  as $v ) {
			if ( in_array( $v, $a2 ) ) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Are all of the values of array $a1 in array $a2 ?
	 *
	 * @param  array    $a1
	 * @param  array    $a2
	 * @return boolean
	 */
	protected static function _allValuesOfArrayInArray( $a1, $a2 ) {
		foreach ( $a1  as $v ) {
			if ( ! in_array( $v, $a2 ) ) {
				return false;
			}
		}
		return true;
	}
}	// class cbpaidUserExtension
