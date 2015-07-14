<?php
/**
 * @version $Id: cbpaidPlansMgr.php 1608 2012-12-29 04:12:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;
use CBLib\Application\Application;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class to manage paid plans objects
 *
 */
class cbpaidPlansMgr extends cbpaidInstanciator {
	protected $_classnameField			=	'item_type';
	protected $_classnamePrefix			=	'cbpaidProduct';
	protected $_classLibraryPrefix		=	'products.';
	protected $_classLibrarySubfolders	=	true;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_plans', 'id', $db );
	}

	/**
	 * Gets a single instance of the cbpaidPlansMgr class
	 *
	 * @param  CBdatabase|null  $db
	 * @return cbpaidPlansMgr
	 */
	public static function & getInstance( &$db = null ) {
		static $singleInstance	=	null;
		if ( $singleInstance === null ) {
			$singleInstance		=	new self( $db );
		}
		return $singleInstance;
	}
	/**
	 * Loads a plan and returns an object of the corresponding class
	 *
	 * @param  int            $id  Id of plan
	 * @return cbpaidProduct
	 */
	public function & loadPlan( $id ) {
		$plan	=&	$this->getObject( $id );
		return $plan;
	}
	/**
	 * Loads a (published) plans from database checking access right to it
	 *
	 * @param  int                 $planId        Plan id to return (if permitted)
	 * @param  UserTable|null      $user          ( or NULL: means all plans)
	 * @param  string              $forCause      specific cause (in addition of $published = TRUE): 'any', 'registration' or 'upgrade'
	 * @param  boolean             $published     TRUE if to load only published plans
	 * @param  int|null            $owner         plan owner (seller), 0 = System, NULL = any
	 * @return cbpaidProduct|null                 Product if available, otherwise NULL
	 */
	public function getPublishedPlan( $planId, $user, $forCause = 'auto', $published = true, $owner = null ) {
		$plans				=&	$this->loadPublishedPlans( $user, $published, $forCause, $owner );

		if ( $plans && isset( $plans[$planId] ) ) {
			return $plans[$planId];
		}
		return null;
	}
	/**
	 * Loads all (published) plans from database in a way which is ordered as a tree
	 *
	 * Avoid using this function, prefer the new getPublishedPlan()
	 *
	 * @param  UserTable|null   $user          ( or NULL: means all plans)
	 * @param  boolean          $published     TRUE if to load only published plans
	 * @param  string           $forCause      specific cause (in addition of $published = TRUE): 'any', 'registration' or 'upgrade', 'auto' (automatically 'registration' or 'upgrade' depending on $user existing and logged-in or not, if $user = null 'any')
	 * @param  int|null         $owner         plan owner (seller), 0 = System, NULL = any
	 * @return cbpaidProduct[]                 Products
	 */
	public function & loadPublishedPlans( $user, $published, $forCause, $owner ) {
		global $_CB_framework;

		if ( $forCause == 'auto' ) {
			$forCause		=	( $user ? ( $user->id ? 'upgrade' : 'registration' ) : 'any' );
		}

		static $_plans		=	array();
		if ( is_object( $user ) ) {
			$gids			=	(array) $user->gids;
		} elseif ( ( $user === null ) || ( $user === 0 ) ) {			// === 0 is by backwards compatibility for when doing an update and cbsubs.content_access.php is from provious version
			$gids			=	array();
		} else {
			trigger_error( 'loadPublishedPlans: user is not object or null.', E_USER_NOTICE );
			$emptyArray		=	array();
			return $emptyArray;
		}
		$pIdx				=	'P' . implode( '-', $gids );

		if ( ! isset( $_plans[$pIdx][$published][$forCause] ) ) {
			$sql			 =	"SELECT a.* FROM `" . $this->_tbl . "` AS a"
				.	"\n LEFT JOIN `" . $this->_tbl . "` AS b ON b.`id` = a.`parent`";
			$where			=	array();
			if ( $published ) {
				$where[]	=	"a.published = 1";
			}
			if ( $forCause == 'registration' ) {
				$where[]	=	"a.allow_newsubscriptions = 1";
				$where[]	=	"a.allow_registration = 1";
				if ( $_CB_framework->getUi() == 1 ) {
					$where[]	=	'a.allow_frontend = 1';
				}
			} elseif ( $forCause == 'upgrade' ) {
				$where[]	=	"a.allow_newsubscriptions = 1";
				$where[]	=	"a.allow_upgrade_to_this = 1";
				if ( $_CB_framework->getUi() == 1 ) {
					$where[]	=	'a.allow_frontend = 1';
				}
			}
			if ( $owner !== null ) {
				$where[]	=	"a.owner = " . (int) $owner;
			}
			if ( $user ) {
				// Old groups-based access:
				$where[]	=	"a.access IN (" . implode( ',', $_CB_framework->acl->get_groups_below_me( $user->id, true ) ) . ")";
			}
			// New view access levels:
			$where[]		=	"a.viewaccesslevel IN " . $this->_db->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() );

			if ( count( $where ) > 0 ) {
				$sql		.=	"\n WHERE " . implode( " AND ", $where );
			}
			$sql			.=	"\n GROUP BY a.id";
			$sql			.=	"\n ORDER BY IF( ISNULL( b.`ordering` ) , a.`ordering`, b.`ordering` )  ASC, IF( ISNULL( b.`ordering` ) , a.`ordering`, 11000 + a.`ordering` )  ASC";
			$this->_db->setQuery( $sql );
			$_plans[$pIdx][$published][$forCause] =& $this->_loadTrueObjects( $this->_tbl_key );
			if ( $forCause == 'registration' ) {
				foreach ( $_plans[$pIdx][$published][$forCause] as $k => $v ) {
					/** @var $v cbpaidProduct */
					if ( ! $v->isPlanAllowingRegistration() ) {
						unset( $_plans[$pIdx][$published][$forCause][$k] );
					}
				}
			} elseif ( $forCause == 'upgrade' ) {
				foreach ( $_plans[$pIdx][$published][$forCause] as $k => $v ) {
					/** @var $v cbpaidProduct */
					$resultTexts	=	array();
					if ( ! $v->isPlanAllowingUpgradesToThis( ( $user && isset( $user->id ) ) ? $user->id : null, $resultTexts ) ) {
						unset( $_plans[$pIdx][$published][$forCause][$k] );
					}
				}
			}
		}
		return $_plans[$pIdx][$published][$forCause];
	}
	/**
	 * Used by XML for Backend:
	 *
	 * Maps array of arrays to an array of new objects of the corresponding class for each row
	 *
	 * @param  array|int  $resultsArray   array of a row of database to convert | int id of row to load
	 * @return cbpaidProduct
	 */
	public static function & productObjects( &$resultsArray ) {
		$plansMgr					=&	cbpaidPlansMgr::getInstance();
		$objectsArray				=&	$plansMgr->getObjects( $resultsArray );
		return $objectsArray;
	}
	/**
	 * Checks all subscriptions for each plan
	 *
	 * @param  int  $limit  Limit the number of checks for each plan for this time
	 * @return int          Total subscriptions checked
	 */
	public function checkAllSubscriptions( $limit = 0 ) {
		$plans						=&	$this->loadPublishedPlans( null, true, 'any', null );
		$total						=	0;
		foreach ( $plans as $p ) {
			$total					+=	$p->checkAllSubscriptions( $limit );
		}
		return $total;
	}
}	// class cbpaidPlansMgr
