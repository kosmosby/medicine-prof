<?php
/**
 * @version $Id: cbpaidGatewaysAccountsMgr.php 1546 2012-12-02 23:16:25Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Application\Application;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class to manage gateway accounts objects
 *
 */
class cbpaidGatewaysAccountsMgr extends cbpaidInstanciator {
	protected $_classnameField			=	'gateway_type';
	protected $_classnamePrefix			=	'cbpaidGatewayAccount';
	// already in name in database: protected $_classLibraryPrefix	=	'processors.';
	protected $_classLibrarySubfolders	=	true;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_gateway_accounts', 'id',  $db );
	}
	/**
	 * Gets a single instance of the cbpaidPlansMgr class
	 *
	 * @param  CBdatabase  $db
	 * @return cbpaidGatewaysAccountsMgr
	 */
	public static function & getInstance( &$db = null ) {
		static $singleInstance	=	null;
		if ( $singleInstance === null ) {
			$singleInstance		=	new self( $db );
		}
		return $singleInstance;
	}
	/**
	 * Loads all (published) plans from database in a way which is ordered as a tree
	 *
	 * @param  int                     $owner        reflecting the user needing to see plan (NULL: means all plans)
	 * @param  boolean                 $enabled     TRUE if to load only published plans
	 * @param  array                   $currency    Currency of payment that must be accepted
	 * @return cbpaidGatewayAccount[]
	 */
	public function & loadEnabledAccounts( $owner = 0, $enabled = true, $currency = null ) {
		static $_objects	=	array();
		if ( ! isset( $_objects[$enabled][$owner] ) ) {
			$sql			=	"SELECT a.* FROM `" . $this->_tbl . "` AS a";
			$where			=	array();
			if ( $enabled ) {
				$where[]	=	"a.enabled > 0";
			}
			if ( $owner !== null ) {
				$where[]	=	"a.owner = " . (int) $owner;
			}
			$where[]		=	"a.viewaccesslevel IN " . $this->_db->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() );

			if ( count( $where ) > 0 ) {
				$sql		.=	"\n WHERE " . implode( " AND ", $where );
			}
			$sql			.=	"\n ORDER BY a.`ordering` ASC";
			$this->_db->setQuery( $sql );
			$_objects[$enabled][$owner] =& $this->_loadTrueObjects( $this->_tbl_key );
		}
		if ( $currency ) {
			// A currency has been specified: we need to filter available gateways by their list of accepted currencies:
			$acts			=	array();
			foreach ( $_objects[$enabled][$owner] as $k => $v ) {
				/** @noinspection PhpUndefinedMethodInspection */
				if ( $_objects[$enabled][$owner][$k]->acceptsCurrency( $currency ) ) {
					$acts[]	=	$_objects[$enabled][$owner][$k];
				}
			}
			return $acts;
		} else {
			return $_objects[$enabled][$owner];
		}
	}
	/**
	 * Used by XML for Backend:
	 *
	 * Maps array of arrays to an array of new objects of the corresponding class for each row
	 *
	 * @param  array|int  $resultsArray   array of a row of database to convert | int id of row to load
	 * @return cbpaidGatewayAccount
	 */
	public static function & gatewayAccountObjects( &$resultsArray ) {
		global $_CB_database;

		$objMgr						=&	cbpaidGatewaysAccountsMgr::getInstance( $_CB_database );
		$objectsArray				=&	$objMgr->getObjects( $resultsArray );
		return $objectsArray;
	}
}	// class cbpaidGatewaysAccountsMgr
