<?php
/**
* @version $Id: cbsubs.tax.php 1608 2012-12-29 04:12:52Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/* TO DUMP COUNTRIES and GEO ZONES:
cbimport( 'cb.dbchecker' );
global $_CB_database;
$chker = new CBdbChecker( $_CB_database );
echo $chker->_dumpAll( array( '#__cbsubs_geo_zones', '#__cbsubs_geo_zones_entries' ), true );
//echo $chker->_dumpAll( array( '#__comprofiler_countries', '#__comprofiler_provinces' ), true );
exit;
*/

use CB\Database\Table\PluginTable;
use CBLib\Xml\SimpleXMLElement;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_CB_framework, $_PLUGINS;

/** @noinspection PhpIncludeInspection */
include_once( $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/user/plug_cbpaidsubscriptions/cbpaidsubscriptions.class.php');

$_PLUGINS->registerFunction( 'onCPayPaymentItemEvent', 'onCPayPaymentItemEvent', 'getcbsubtaxTab' );
$_PLUGINS->registerFunction( 'onxmlBeforeCbSubsDisplayOrSaveInvoice', 'onxmlBeforeCbSubsDisplayOrSaveInvoice', 'getcbsubtaxTab' );


/**
 * Model for tax rules ( #__cbsubs_tax_rules table)
 */
class cbpaidTaxRule extends cbpaidTable {
	public $id;						// sql:int(20)
	public $name;					// sql:varchar(64)
	public $short_code;				// sql:varchar(16)
	public $default;				// sql:int(2)
	public $ordering;				// sql:int(11)
	public $owner		=	0;		// sql:int(11) default="0"
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_tax_rules', 'id', $db );
		$this->_historySetLogger();
	}
	/**
	 * Singletons loader
	 *
	 * @param  int  $id
	 * @return cbpaidTaxRule
	 */
	public static function getInstance( $id ) {
		static $cache		=	array();
		$id					=	(int) $id;
		if ( ! isset( $cache[$id] ) ) {
			$classname		=	__CLASS__;
			/** @var $cache cbpaidTaxRule[]  */
			$cache[$id]		=	new $classname();
			$cache[$id]->load( (int) $id );
		}
		return $cache[$id];
	}
	/**
	 * Gets the short code for this tax rule
	 *
	 * @return string  Short code
	 */
	public function getShortCode( ) {
		return $this->short_code;
	}
	/**
	 * Gets the default tax rule id
	 *
	 * @return int|null  Rule id or NULL if none is default
	 */
	public static function getDefaultTaxRuleId( ) {
		$taxRule			=	new cbpaidTaxRule();
		if ( $taxRule->loadThisMatching( array( 'default' => 1 ), array( 'ordering' => 'ASC' ) ) ) {
			return (int) $taxRule->id;
		} else {
			return null;
		}
	}
	/**
	*	Check for whether dependancies exist for this object in the db schema
	*
	*	@param  int      $oid   Optional key index
	*	@return boolean         TRUE: OK to delete, FALSE: not OK to delete, error in $this->_error
	*/
	public function canDelete( $oid = null ) {
		$relatedTables	=	array(	CBPTXT::T("Tax Rates")		=> '#__cbsubs_tax_rates',
									CBPTXT::T("Plans")				=> '#__cbsubs_plans' );

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = $oid;
		}

		if ( $oid == 1 ) {
			$this->setError( CBPTXT::T("The system Not-taxable Tax Rule entry can not be deleted.") );
			return false;
		}

		foreach ( $relatedTables as $text => $table ) {
			$query = "SELECT COUNT(*)"
			. "\n FROM `" . $table . "`"
			. "\n WHERE `tax_rule_id` = ". (int) $this->$k
			;
			$this->_db->setQuery( $query );
	
			$count = $this->_db->loadResult();
			if ( $count > 0 ) {
				$this->setError( sprintf( CBPTXT::T("%d %s exist using this Tax Rule."), $count, $text ) );
				return false;
			}
		}
		return true;
	}
}

/**
 * Class for tax rate (compoundable cross-totalizers)
 */
class cbpaidsalestaxTotalizertype extends cbpaidTotalizertypeCompoundable {
	public $id;							// sql:int(20) unsigned="true" auto_increment="1"
	public $description;				// sql:varchar(64)
	public $tax_kind;					// sql:varchar(24)
	public $tax_rate;					// sql:decimal(16,5) null="true"
	public $tax_currency;				// sql:varchar(3)
	public $tax_amount;					// sql:decimal(16,5) null="true"
	public $tax_stages;					// tax_stages(255)
	public $priority;					// sql:int(20)						// inherited
	public $tax_rule_id;				// sql:int(20) unsigned="true"
	public $buyer_geo_zone_id;			// sql:int(20) unsigned="true"
	public $seller_geo_zone_id;			// sql:int(20) unsigned="true"
	public $tax_inside_of_country;		// sql:int(2)  unsigned="true" default="1"
	public $tax_outside_of_country;		// sql:int(2)  unsigned="true" default="1"
	public $tax_inside_of_state;		// sql:int(2)  unsigned="true" default="1"
	public $tax_outside_of_state;		// sql:int(2)  unsigned="true" default="1"
	// <!-- Maybe in here we should add seller zone condition so that we can prefill all rules -->
	public $published	=	1;			// sql:int(2)" default="1"
	public $ordering;					// sql:int(11)
	public $start_date;					// sql:date" null="false"
	public $stop_date;					// sql:date" null="false"
	public $start_stop_date_function;	// sql:int(3) default="1"
	public $business_check;
	public $seller_taxnumber;			// sql:varchar(20)
	public $owner		=	0;			// sql:int(11) default="0"
	public $params;						// sql:varchar(255)

	public $_rule_short_code;
	/**
	 * Items using this tax rate
	 * @var int[]
	 */
	public $_itemsUsingThisRate;
	/**
	 * Payment basket associated with this tax rate
	 * @var cbpaidPaymentBasket
	 */
	protected $_paymentBasket;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_tax_rates', 'id', $db );
		$this->_historySetLogger();
	}
	/**
	 * Internal function to convert CB-formatted date from field into SQL date.
	 * @access private
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function _displayDateToSql( $value ) {
		global $ueConfig;

		if ( $value !== null ) {
			$sqlFormat					=	'Y-m-d';
			$fieldForm					=	str_replace( 'y', 'Y', $ueConfig['date_format'] );
			$value						=	dateConverter( stripslashes( $value ), $fieldForm, $sqlFormat );
			if ( ! preg_match( '/[0-9]{4}-[01][0-9]-[0-3][0-9]/', $value ) ) {
				$value					=	'';
			}
		}
		return $value;
	}
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
		if ( ! preg_match( '/^\d{4}-\d\d-\d\d/', $this->start_date ) ) {
			// in backend we didn't yet fix the date picker to have different storage ordering than display format.
			$this->start_date	=	$this->_displayDateToSql( $this->start_date );
			$this->stop_date	=	$this->_displayDateToSql( $this->stop_date );
		}
		return parent::store( $updateNulls );
	}
	/**
	 * loads and returns an array of potentially applicable cbpaidsalestaxTotalizertype for a given $taxRuleId
	 * filtered by all geographic zone settings and by published state
	 * BUT not by time: 'start_date' and 'stop_date' of each tax must still be evaluated properly
	 *
	 * @param  int      $taxRuleId
	 * @param  string   $buyerCountry
	 * @param  string   $buyerProvince
	 * @param  string   $buyerZip
	 * @param  string   $sellerCountry
	 * @param  string   $sellerProvince
	 * @param  string   $sellerZip
	 * @param  int      $sellerOwnerId
	 * @param  boolean  $isPublished
	 * @return cbpaidsalestaxTotalizertype[]   indexed by id, sorted by 'priority' and 'ordering'
	 */
	public function getRatesForRuleAndGeo( $taxRuleId, $buyerCountry, $buyerProvince, $buyerZip, $sellerCountry, $sellerProvince, $sellerZip, /** @noinspection PhpUnusedParameterInspection */ $sellerOwnerId, $isPublished = true ) {
		$taxRuleId			=	(int) $taxRuleId;
		if ( $taxRuleId == 0 ) {
			$taxRuleId		=	cbpaidTaxRule::getDefaultTaxRuleId();
		}
		if ( $taxRuleId == 0 ) {
			return array();
		}
		/* Query example:
			SELECT r.* FROM `#__cbsubs_tax_rates` r
			LEFT JOIN #__cbsubs_geo_zones b ON b.id = r.`buyer_geo_zone_id`
			LEFT JOIN #__cbsubs_geo_zones s ON s.id = r.`seller_geo_zone_id`
			LEFT JOIN #__cbsubs_geo_zones_entries be ON
			     be.geo_zone_id = b.id
			 AND be.`country_iso_code2` = 'CH'
			 AND ( be.`province_iso_code` = '' OR be.`province_iso_code` = 'CH-VD' )
			 AND ( be.`zip_code_condition` = 0 OR ( be.`zip_code_condition` = 1 AND '1010' REGEXP be.`zip_code_regexp` ) OR ( be.`zip_code_condition` = 2 AND be.`zip_code_min` <= 1010 AND be.`zip_code_max` >= 1010 ) )
			LEFT JOIN #__cbsubs_geo_zones_entries se ON
			     se.geo_zone_id = s.id
			 AND se.`country_iso_code2` = 'CH'
			 AND ( se.`province_iso_code` = '' OR se.`province_iso_code` = 'CH-VD' )
			 AND ( se.`zip_code_condition` = 0 OR ( se.`zip_code_condition` = 1 AND '1010' REGEXP se.`zip_code_regexp` ) OR ( se.`zip_code_condition` = 2 AND se.`zip_code_min` <= 1010 AND se.`zip_code_max` >= 1010 ) )
			WHERE r.`tax_rule_id` = 1
			AND ((('CH' = 'CH'))) AND ( r.`tax_inside_of_country` = 1 OR ( r.`tax_inside_of_country` = 2 AND 'isCONSUMER' = 'yes' ) OR ( r.`tax_inside_of_country` = 3 AND 'isBUSINESS' = 'yes' ) )
			# AND ((('CH' <> 'DE'))) AND ( r.`tax_outside_of_country` = 1 OR ( r.`tax_outside_of_country` = 2 AND 'isCONSUMER' = 'yes' ) OR ( r.`tax_outside_of_country` = 3 AND 'isBUSINESS' = 'yes' ) )
			AND r.`tax_inside_of_state` = 1
			AND r.`tax_outside_of_state` = 1
			GROUP by r.`id`
			ORDER by r.`priority`, r.`ordering`
		 */
/*
		$sql			=	'SELECT r.*, ru.short_code AS _rule_short_code FROM ' . $this->_db->NameQuote( '#__cbsubs_tax_rates' ) . ' AS r'
						.	"\n JOIN "		. $this->_db->NameQuote( '#__cbsubs_tax_rules' ) . ' AS ru ON ru.id = r.' . $this->_db->NameQuote( 'tax_rule_id' )
						.	"\n LEFT JOIN " . $this->_db->NameQuote( '#__cbsubs_geo_zones' ) . ' AS b ON b.id = r.' . $this->_db->NameQuote( 'buyer_geo_zone_id' )
						.	"\n LEFT JOIN " . $this->_db->NameQuote( '#__cbsubs_geo_zones' ) . ' AS s ON s.id = r.' . $this->_db->NameQuote( 'seller_geo_zone_id' )
						.	"\n LEFT JOIN " . $this->_db->NameQuote( '#__cbsubs_geo_zones_entries' ) . ' AS be ON'
						.	"\n      be.geo_zone_id = b.id"
						.	"\n 	 AND be." . $this->_db->NameQuote( 'country_iso_code2' ) . ' = ' . $this->_db->Quote( $buyerCountry )
						.	"\n 	 AND ( be." . $this->_db->NameQuote( 'province_iso_code' ) . ' = ' . $this->_db->Quote( '' )
						.	"\n 	    OR be." . $this->_db->NameQuote( 'province_iso_code' ) . ' = ' . $this->_db->Quote( $buyerProvince ) . ' )'
						.	"\n 	 AND ( be." . $this->_db->NameQuote( 'zip_code_condition' ) . ' = 0'
						.	"\n 	  OR ( be." . $this->_db->NameQuote( 'zip_code_condition' ) . ' = 1 AND ' . $this->_db->Quote( $buyerZip ) . ' REGEXP be.' . $this->_db->NameQuote( 'zip_code_regexp' ) . ' )'
						.	"\n 	  OR ( be." . $this->_db->NameQuote( 'zip_code_condition' ) . ' = 2'
						.	"\n 	 						 AND be." . $this->_db->NameQuote( 'zip_code_min' ) . ' <= ' . (int) $buyerZip
						.	"\n 	 						 AND be." . $this->_db->NameQuote( 'zip_code_max' ) . ' >= ' . (int) $buyerZip . ' ) )'
						.	"\n LEFT JOIN " . $this->_db->NameQuote( '#__cbsubs_geo_zones_entries' ) . ' AS se ON'
						.	"\n      se.geo_zone_id = s.id"
						.	"\n 	 AND se." . $this->_db->NameQuote( 'country_iso_code2' ) . ' = ' . $this->_db->Quote( $sellerCountry )
						.	"\n 	 AND ( se." . $this->_db->NameQuote( 'province_iso_code' ) . ' = ' . $this->_db->Quote( '' )
						.	"\n 	    OR se." . $this->_db->NameQuote( 'province_iso_code' ) . ' = ' . $this->_db->Quote( $sellerProvince ) . ' )'
						.	"\n 	 AND ( se." . $this->_db->NameQuote( 'zip_code_condition' ) . ' = 0'
						.	"\n 	  OR ( se." . $this->_db->NameQuote( 'zip_code_condition' ) . ' = 1 AND ' . $this->_db->Quote( $sellerZip ) . ' REGEXP se.' . $this->_db->NameQuote( 'zip_code_regexp' ) . ' )'
						.	"\n 	  OR ( se." . $this->_db->NameQuote( 'zip_code_condition' ) . ' = 2'
						.	"\n 	 						 AND se." . $this->_db->NameQuote( 'zip_code_min' ) . ' <= ' . (int) $sellerZip
						.	"\n 	 						 AND se." . $this->_db->NameQuote( 'zip_code_max' ) . ' >= ' . (int) $sellerZip . ' ) )'
						.	"\n WHERE .....
 */
		$sql			=	'SELECT r.*, ru.short_code AS _rule_short_code FROM ' . $this->_db->NameQuote( '#__cbsubs_tax_rates' ) . ' AS r'
						.	"\n JOIN "		. $this->_db->NameQuote( '#__cbsubs_tax_rules' ) . ' AS ru ON ru.id = r.' . $this->_db->NameQuote( 'tax_rule_id' )
						.	"\n LEFT JOIN " . $this->_db->NameQuote( '#__cbsubs_geo_zones' ) . ' AS b ON b.id = r.' . $this->_db->NameQuote( 'buyer_geo_zone_id' )
						.	"\n LEFT JOIN " . $this->_db->NameQuote( '#__cbsubs_geo_zones' ) . ' AS s ON s.id = r.' . $this->_db->NameQuote( 'seller_geo_zone_id' )
						.	"\n WHERE r." . $this->_db->NameQuote( 'tax_rule_id' ) . ' = ' . (int) $taxRuleId
						.	"\n AND ( ( r." . $this->_db->NameQuote( 'buyer_geo_zone_id' ) . ' = 0 )'
						.	"\n		OR (  SELECT COUNT(*) FROM " . $this->_db->NameQuote( '#__cbsubs_geo_zones_entries' ) . ' AS be'
						.	"\n			  WHERE be.geo_zone_id = b.id"
						.	"\n 		 AND be." . $this->_db->NameQuote( 'country_iso_code2' ) . ' = ' . $this->_db->Quote( $buyerCountry )
						.	"\n 		 AND ( be." . $this->_db->NameQuote( 'province_iso_code' ) . ' = ' . $this->_db->Quote( '' )
						.	"\n 		    OR be." . $this->_db->NameQuote( 'province_iso_code' ) . ' = ' . $this->_db->Quote( $buyerProvince ) . ' )'
						.	"\n 		 AND ( be." . $this->_db->NameQuote( 'zip_code_condition' ) . ' = 0'
						.	"\n 		  OR ( be." . $this->_db->NameQuote( 'zip_code_condition' ) . ' = 1 AND ' . $this->_db->Quote( $buyerZip ) . ' REGEXP be.' . $this->_db->NameQuote( 'zip_code_regexp' ) . ' )'
						.	"\n 		  OR ( be." . $this->_db->NameQuote( 'zip_code_condition' ) . ' = 2'
						.	"\n 		 						 AND be." . $this->_db->NameQuote( 'zip_code_min' ) . ' <= ' . (int) $buyerZip
						.	"\n 		 						 AND be." . $this->_db->NameQuote( 'zip_code_max' ) . ' >= ' . (int) $buyerZip . ' ) )'
						.	"\n			) > 0 )"
						.	"\n AND ( ( r." . $this->_db->NameQuote( 'seller_geo_zone_id' ) . ' = 0 )'
						.	"\n		OR (  SELECT COUNT(*) FROM " . $this->_db->NameQuote( '#__cbsubs_geo_zones_entries' ) . ' AS se '
						.	"\n 	     WHERE se.geo_zone_id = s.id"
						.	"\n 		 AND se." . $this->_db->NameQuote( 'country_iso_code2' ) . ' = ' . $this->_db->Quote( $sellerCountry )
						.	"\n 		 AND ( se." . $this->_db->NameQuote( 'province_iso_code' ) . ' = ' . $this->_db->Quote( '' )
						.	"\n 		    OR se." . $this->_db->NameQuote( 'province_iso_code' ) . ' = ' . $this->_db->Quote( $sellerProvince ) . ' )'
						.	"\n 		 AND ( se." . $this->_db->NameQuote( 'zip_code_condition' ) . ' = 0'
						.	"\n 		  OR ( se." . $this->_db->NameQuote( 'zip_code_condition' ) . ' = 1 AND ' . $this->_db->Quote( $sellerZip ) . ' REGEXP se.' . $this->_db->NameQuote( 'zip_code_regexp' ) . ' )'
						.	"\n 		  OR ( se." . $this->_db->NameQuote( 'zip_code_condition' ) . ' = 2'
						.	"\n 		 						 AND se." . $this->_db->NameQuote( 'zip_code_min' ) . ' <= ' . (int) $sellerZip
						.	"\n 		 						 AND se." . $this->_db->NameQuote( 'zip_code_max' ) . ' >= ' . (int) $sellerZip . ' ) )'
						.	"\n			) > 0 )";
		if ( $sellerCountry == $buyerCountry ) {
			// $sql		.=	"\n AND ( r." . $this->_db->NameQuote( 'tax_inside_of_country' ) . ' = 1 OR r.' . $this->_db->NameQuote( 'tax_inside_of_country' ) . ' = ' . ( $buyerIsBusiness ? 3 : 2 ) . ' )';
			$sql		.=	"\n AND ( r." . $this->_db->NameQuote( 'tax_inside_of_country' ) . ' >= 1 )';
			if ( $sellerProvince ) {
				if ( ( $sellerProvince == $buyerProvince ) || ( $buyerProvince == '' ) ) {
					// $sql	.=	"\n AND ( r." . $this->_db->NameQuote( 'tax_inside_of_state' )   . ' = 1 OR r.' . $this->_db->NameQuote( 'tax_inside_of_state' )   . ' = ' . ( $buyerIsBusiness ? 3 : 2 ) . ' )';
					$sql	.=	"\n AND ( r." . $this->_db->NameQuote( 'tax_inside_of_state' )   . ' >= 1 )';
				} else {
					// $sql	.=	"\n AND ( r." . $this->_db->NameQuote( 'tax_outside_of_state' )   . ' = 1 OR r.' . $this->_db->NameQuote( 'tax_outside_of_state' )   . ' = ' . ( $buyerIsBusiness ? 3 : 2 ) . ' )';
					$sql	.=	"\n AND ( r." . $this->_db->NameQuote( 'tax_outside_of_state' )   . ' >= 1 )';
				}
			}
		} else {
			// $sql		.=	"\n AND ( r." . $this->_db->NameQuote( 'tax_outside_of_country' ) . ' = 1 OR r.' . $this->_db->NameQuote( 'tax_outside_of_country' ) . ' = ' . ( $buyerIsBusiness ? 3 : 2 ) . ' )';
			$sql		.=	"\n AND ( r." . $this->_db->NameQuote( 'tax_outside_of_country' ) . ' >= 1 )';
		}
		if ( $isPublished ) {
			$sql		.=	"\n AND r." . $this->_db->NameQuote( 'published' ) . ' = 1';
		}
		// Later : $sql			.=	"\n AND r." . $this->_db->NameQuote( 'owner' ) . ' = ' . (int) $sellerOwnerId;
		// Later : $sql			.=	"\n AND ru." . $this->_db->NameQuote( 'owner' ) . ' = ' . (int) $sellerOwnerId;
		$sql			.=	"\n GROUP by r." . $this->_db->NameQuote( 'id' )
						.	"\n ORDER by r." . $this->_db->NameQuote( 'priority' ) . ', r.' . $this->_db->NameQuote( 'ordering' );
		$this->_db->setQuery( $sql );
		return $this->loadTrueObjects( null, $this->_tbl_key, array( '_rule_short_code' ) );
	}

	/**
	 * Validates tax rate's business status
	 *
	 * @param  cbpaidPaymentBasket          $paymentBasket
	 * @param  cbpaidsalestaxTotalizertype  $tax
	 * @param $buyerCountry
	 * @param $buyerProvince
	 * @param $sellerCountry
	 * @param $sellerProvince
	 * @return bool
	 */
	public function validateTaxRateBusinessStatus( &$paymentBasket, $tax, $buyerCountry, $buyerProvince, $sellerCountry, $sellerProvince ) {
		global $_CB_framework, $_PLUGINS;

		$taxrateApplies					=	true;
		if ( $tax->business_check ) {
			$absoluteValidationsPath	=	$_CB_framework->getCfg('absolute_path') . '/'. $_PLUGINS->getPluginRelPath( cbpaidApp::getBaseClass()->getPluginObject() ) . '/plugin/cbsubstax/validations/' . $tax->business_check;
			$valphp		=	$absoluteValidationsPath . '/validation.php';
			if ( is_readable( $valphp ) ) {
				/** @noinspection PhpIncludeInspection */
				include_once $valphp;
				$className	=	'cbpaidValidate_' . $tax->business_check;
				if ( is_callable( array( $className, 'validateInvoiceAddress' ) ) ) {
					/** @var $validator cbpaidValidate */
					$validator	=	new $className();
					$validator->validateInvoiceAddress( $paymentBasket, $tax );

					$businessStatusGeoValues			=	array( 1, ( $paymentBasket->is_business == 1 ? 3 : 2 ) );
					if ( $sellerCountry == $buyerCountry ) {
						$taxrateApplies			=	in_array( $tax->tax_inside_of_country, $businessStatusGeoValues );
						// $sql		.=	"\n AND ( r." . $tax->_db->NameQuote( 'tax_inside_of_country' ) . ' = 1 OR r.' . $tax->_db->NameQuote( 'tax_inside_of_country' ) . ' = ' . ( $buyerIsBusiness ? 3 : 2 ) . ' )';
						if ( $taxrateApplies && $sellerProvince ) {
							if ( ( $sellerProvince == $buyerProvince ) || ( $buyerProvince == '' ) ) {
								$taxrateApplies			=	in_array( $tax->tax_inside_of_state, $businessStatusGeoValues );
								// $sql	.=	"\n AND ( r." . $tax->_db->NameQuote( 'tax_inside_of_state' )   . ' = 1 OR r.' . $tax->_db->NameQuote( 'tax_inside_of_state' )   . ' = ' . ( $buyerIsBusiness ? 3 : 2 ) . ' )';
							} else {
								$taxrateApplies			=	in_array( $tax->tax_outside_of_state, $businessStatusGeoValues );
								// $sql	.=	"\n AND ( r." . $tax->_db->NameQuote( 'tax_outside_of_state' )   . ' = 1 OR r.' . $tax->_db->NameQuote( 'tax_outside_of_state' )   . ' = ' . ( $buyerIsBusiness ? 3 : 2 ) . ' )';
							}
						}
					} else {
						$taxrateApplies			=	in_array( $tax->tax_outside_of_country, $businessStatusGeoValues );
						// $sql		.=	"\n AND ( r." . $tax->_db->NameQuote( 'tax_outside_of_country' ) . ' = 1 OR r.' . $tax->_db->NameQuote( 'tax_outside_of_country' ) . ' = ' . ( $buyerIsBusiness ? 3 : 2 ) . ' )';
					}
				}
			}
		}
		return $taxrateApplies;
	}
	/**
	 * Reset totalizer compounders
	 *
	 * @return void
	 */
	public function resetTotalizer( ) {
		// Nothing to do here
	}
	/**
	 * Sets the basket for the calculation functions below: getAmountBeforePercents, getPercents and getAmountAfterPercents
	 * 
	 * @param cbpaidPaymentBasket $paymentBasket
	 */
	public function setBasket( $paymentBasket ) {
		$this->_paymentBasket		=	$paymentBasket;
	}
	/**
	 * Sets the basket item for the calculation functions below: getAmountBeforePercents, getPercents and getAmountAfterPercents
	 * 
	 * @param cbpaidPaymentItem $item
	 */
	public function setPaymentItem( $item ) {
		// nothing to do here		$this->_paymentItem		=	$item;
	}
	/**
	 * Computes fixed amount before percentage, or if only fixed amount, fixed amount
	 * 
	 * @param  float    $amount
	 * @param  float    $amountTaxExcl
	 * @param  float    $periodProrater
	 * @param  boolean  $isFirstPeriod
	 * @param  string   $currency_code
	 * @return float|null
	 */
	public function getAmountBeforePercents( $amount, $amountTaxExcl, $periodProrater, $isFirstPeriod, $currency_code ) {
		/*
		<option value="percentage">A percentage of the item price</option>
		<option value="fixed">A fixed amount</option>
		<option value="fixed_percent">A fixed amount added/substracted from the item price, plus a percentage</option>
		<option value="percent_fixed">A percentage of the item price, added/substracted by a fixed amount</option>
		<option value="value_dependent">A fixed amount depending on the corresponding items price</option>
		*/
		$return						=	null;
		if ( in_array( $this->tax_kind, array( 'fixed', 'percent_fixed' ) ) ) {
			$_CBPAY_CURRENCIES		=&	cbpaidApp::getCurrenciesConverter();
			$cbpaidMoney			=&	cbpaidMoney::getInstance();
			$return					=	$_CBPAY_CURRENCIES->convertCurrency( $cbpaidMoney->currency( $this->tax_currency ), $currency_code, $this->tax_amount );		// null if cannot convert
		} elseif ( $this->tax_kind == 'value_dependent') {
			$step					=	$this->_findTaxStep( $amountTaxExcl, $this->tax_stages );
			if ( substr( $step, -1 ) !== '%' ) {
				$return				=	(float) $step;
			}
		}
		if ( $return !== null ) {
			$return					*=	$periodProrater;
		}
		return $return;
	}
	/**
	 * Computes the percentage on amount
	 * 
	 * @param  float    $amount
	 * @param  float    $amountTaxExcl
	 * @param  float    $periodProrater
	 * @param  boolean  $isFirstPeriod
	 * @return float|int|null
	 */
	public function getPercents( $amount, $amountTaxExcl, $periodProrater, $isFirstPeriod ) {
		$return						=	null;
		if ( in_array( $this->tax_kind, array( 'percentage', 'fixed_percent', 'percent_fixed' ) ) ) {
			$return					=	(float) $this->tax_rate / 100;
		} elseif ( $this->tax_kind == 'value_dependent') {
			$step					=	$this->_findTaxStep( $amountTaxExcl, $this->tax_stages );
			if ( substr( $step, -1 ) === '%' ) {
				$return				=	(float) substr( $step, 0, -1 ) / 100;
			}
		}
		if ( $return !== null ) {
			$return					*=	$periodProrater;
		}
		return $return;
	}
	/**
	 * Computes the amount after percentage, only if it's combined
	 * 
	 * @param  float    $amount
	 * @param  float    $amountTaxExcl
	 * @param  float    $periodProrater
	 * @param  boolean  $isFirstPeriod
	 * @param  string   $currency_code
	 * @return float|null
	 */
	public function getAmountAfterPercents( $amount, $amountTaxExcl, $periodProrater, $isFirstPeriod, $currency_code ) {
		$return						=	null;
		if ( in_array( $this->tax_kind, array( 'fixed_percent' ) ) ) {
			$_CBPAY_CURRENCIES		=&	cbpaidApp::getCurrenciesConverter();
			$cbpaidMoney			=&	cbpaidMoney::getInstance();
			$return					=	$_CBPAY_CURRENCIES->convertCurrency( $cbpaidMoney->currency( $this->tax_currency ), $currency_code, $this->tax_amount );		// null if cannot convert
		}
		if ( $return !== null ) {
			$return					*=	$periodProrater;
		}
		return $return;
	}
}
/**
 * Model for geographical Zone Entry
 */
class cbpaidGeoZoneEntry extends cbpaidTable {
	public $id;							// sql:int(20)" unsigned="true" auto_increment="1"
	public $geo_zone_id;				// sql:int(20) unsigned="true"
	public $country_iso_code2;			// sql:char(2)
	public $province_iso_code;			// sql:varchar(12)
	public $zip_code_min;				// sql:varchar(1024)
	public $zip_code_max;				// sql:varchar(1024)
	public $ordering;					// sql:int(11)
	public $owner		=	0;			// sql:int(11) default="0"
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_geo_zones_entries', 'id', $db );
		$this->_historySetLogger();
	}
}

/**
 * Model for geographical Zone
 */
class cbpaidGeoZone extends cbpaidTable {
	public $id;							// sql:int(20)" unsigned="true" auto_increment="1"
	public $name;						// sql:varchar(64)
	public $description;				// sql:varchar(255)
	public $ordering;					// sql:int(11)
	public $owner		=	0;			// sql:int(11) default="0"
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_geo_zones', 'id', $db );
		$this->_historySetLogger();
	}
}

/**
 * Model for geographical Country
 */
class moscomprofilerCountry extends cbpaidTable {
	public $country_iso_code2;			// sql:char(2)
	public $country_iso_code3;			// sql:char(3)
	public $country_iso_num;			// sql:varchar(3)
	public $country_name;				// sql:varchar(50)
	public $country_region;				// sql:varchar(8)
	public $country_sub_region;			// sql:varchar(25)
	public $country_fips_code;			// sql:char(2)
	public $country_fips_name;			// sql:varchar(50)
	public $country_alternate_names;	// sql:varchar(255)
	public $country_longitude;			// sql:decimal(17,14)
	public $country_latitude;			// sql:decimal(17,14)
	public $country_provinces_title;	// sql:varchar(64)
	public $country_taxnumber_prefix;	// sql:varchar(6)
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__comprofiler_countries', 'country_iso_code2', $db );
		$this->_historySetLogger();
	}
}

/**
 * Model for geographical Province of country
 */
class moscomprofilerProvince extends cbpaidTable {
	public $country_iso_code2;			// sql:char(2)
	public $province_iso_code;			// sql:varchar(12)
	public $province_name;				// sql:varchar(64)
	public $province_latin_name;		// sql:varchar(64)
	public $province_alternate_names;	// sql:varchar(255)
	public $province_title;				// sql:varchar(64)
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__comprofiler_countries', 'province_iso_code', $db );
		$this->_historySetLogger();
	}
}

/**
 * Class for tax totalizers storage in baskets (like payment items)
 */
class cbpaidPaymentTotalizer_salestax extends cbpaidPaymentTotalizerCompoundable {
/* Inherited:
	public $id					= null;
	public $payment_basket_id;
	public $ordering		=	0;
	public $totalizer_type;
	public $totalizer_id;
	public $quantity;
	public $unit;
	public $artnum;
	public $description;
	public $currency;
	public $rate;
	public $first_rate;
	public $tax_rule_id;

	public $_itemIndexes;
*/
	/**
	 * @var cbpaidsalestaxTotalizertype
	 */
	protected $_totalizertypeSettings;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( $db );
	}
	/** DEPRECATED?
	 * 
	 * @param  cbpaidPaymentBasket  $paymentBasket
	private function getBasketGeoZone( $paymentBasket ) {
		$geoZone				=	new cbpaidGeoZone();
		$geoZone->loadGeoZonesList( $paymentBasket->address_country_code, $paymentBasket->address_state, $paymentBasket->address_zip );
	}
	*/
	/**
	 * Creates entries for totalizer
	 *
	 * @param  cbpaidPaymentBasket            $paymentBasket
	 * @param  cbpaidPaymentItem[]            $paymentItems
	 * @param  cbpaidPaymentTotalizer[]       $taxableTotalizers
	 * @return cbpaidsalestaxTotalizertype[]
	 */
	public static function getApplicableRatesWithoutBusinessCheck( $paymentBasket, $paymentItems, $taxableTotalizers ) {
		$params											=&	cbpaidApp::settingsParams();
		if ( $params->get( 'integration_cbsubstax_enabled' ) != 1 ) {
			return array();
		}


		$sellerCountry									=	$params->get( 'integration_cbsubstax_system_seller_country_iso_code2' );
		$sellerProvince									=	$params->get( 'integration_cbsubstax_system_seller_province_iso_code' );
		$sellerZip										=	$params->get( 'integration_cbsubstax_system_seller_province_iso_code_zip_code' );

		// callect all tax_rules with each tax_rate applicable to this payment basket items into $taxRulesRates:
		$taxRulesRates									=	array();
		foreach ( $paymentItems as $paymentItemIndex => &$item ) {
			// $item			=	NEW cbpaidPaymentItem();
			$tax_rule									=	(string) $item->getPlanParam( 'tax_rule_id', 0, null );
			if ( $tax_rule == 0 ) {
				$tax_rule								=	(string) cbpaidTaxRule::getDefaultTaxRuleId();
			}
			if ( $tax_rule ) {
				if ( ! isset( $taxRulesRates[$tax_rule] ) ) {
					$taxComputer						=	new cbpaidsalestaxTotalizertype();
					$taxRulesRates[$tax_rule]			=	$taxComputer->getRatesForRuleAndGeo( $tax_rule, $paymentBasket->address_country_code, $paymentBasket->address_state, $paymentBasket->address_zip, $sellerCountry, $sellerProvince, $sellerZip, $paymentBasket->owner );
				}

				// Remember which items are using this rate:
				foreach ( $taxRulesRates[$tax_rule] as $k => $v ) {
					$taxRulesRates[$tax_rule][$k]->_itemsUsingThisRate[]		=	$paymentItemIndex;
				}
/*
				if ( $item->getPlanParam( 'tax_prices_inclusive_tax', 0 ) == 1 ) {
					// Convert Taxes-Included Payment Items to non-tax-included ones,
					// so that when we compute taxes, we can handle them same way as normal taxes-excluded items:
					$item->rate							=	self::_computeWithoutTax( $item->rate, $taxRulesRates[$tax_rule], $paymentBasket->mc_currency, false );
					if ( $item->first_validity ) {
						$item->first_rate				=	self::_computeWithoutTax( $item->first_rate, $taxRulesRates[$tax_rule], $paymentBasket->mc_currency, true );
					}
					if ( $item->discount_amount ) {
						$item->discount_amount			=	self::_computeWithoutTax( $item->discount_amount, $taxRulesRates[$tax_rule], $paymentBasket->mc_currency, false );
					}
					if ( $item->first_discount_amount ) {
						$item->first_discount_amount	=	self::_computeWithoutTax( $item->first_discount_amount, $taxRulesRates[$tax_rule], $paymentBasket->mc_currency, true );
					}
				}
*/
			}
		}
		unset( $item );		// was used as &$item

		foreach ( $taxableTotalizers as $totalizerToTax ) {
			$tax_rule									=	(string) $totalizerToTax->tax_rule_id;
			if ( $tax_rule ) {
				if ( ! isset( $taxRulesRates[$tax_rule] ) ) {
					$taxComputer						=	new cbpaidsalestaxTotalizertype();
					$taxRulesRates[$tax_rule]			=	$taxComputer->getRatesForRuleAndGeo( $tax_rule, $paymentBasket->address_country_code, $paymentBasket->address_state, $paymentBasket->address_zip, $sellerCountry, $sellerProvince, $sellerZip, $paymentBasket->owner );
				}
			}
		}
		return $taxRulesRates;
	}
	/**
	 * Creates entries for totalizer
	 *
	 * @param  cbpaidPaymentBasket        $paymentBasket
	 * @param  cbpaidPaymentItem[]        $paymentItems
	 * @param  cbpaidPaymentTotalizer[]   $taxableTotalizers
	 * @param  string                     $paymentTotalizerType
	 * @param  callable                   $addTotalizerToBasketFunc
	 * @return void
	 */
	public static function createTotalizerEntries( $paymentBasket, $paymentItems, $taxableTotalizers, $paymentTotalizerType, $addTotalizerToBasketFunc ) {
		global $_CB_framework;

		$myClassName									=	'cbpaidPaymentTotalizer_' . $paymentTotalizerType;

		$params											=&	cbpaidApp::settingsParams();
		$sellerCountry									=	$params->get( 'integration_cbsubstax_system_seller_country_iso_code2' );
		$sellerProvince									=	$params->get( 'integration_cbsubstax_system_seller_province_iso_code' );
		
		$taxRulesRates									=	self::getApplicableRatesWithoutBusinessCheck( $paymentBasket, $paymentItems, $taxableTotalizers );
		// collect all tax_rates of the collected tax_rules, filtered by is_business type and ordered by priority,ordering,id into $taxRatesUsed:
		$taxRatesUsed									=	array();
		foreach ( $taxRulesRates as $AllTaxRates ) {
			foreach ( $AllTaxRates as $taxRate ) {
				//$taxRate	= NEW cbpaidsalestaxTotalizertype();
				$taxComputer							=	new cbpaidsalestaxTotalizertype();
				if ( $taxComputer->validateTaxRateBusinessStatus( $paymentBasket, $taxRate, $paymentBasket->address_country_code, $paymentBasket->address_state, $sellerCountry, $sellerProvince ) ) {
					$taxRatesUsed[(int) $taxRate->priority][(int) $taxRate->ordering][(int) $taxRate->id]		=	$taxRate;
				}
			}
		}
		// now create one totalizer for each tax rate - taxing period
		$anyAutoRecurringInBasket							=	$paymentBasket->isAnyAutoRecurringPossibleWithThisBasket();

		$offset												=	(int) $_CB_framework->getCfg( 'offset' ) * 3600;
		$cbpaidTimes										=&	cbpaidTimes::getInstance();

		foreach ( $taxRatesUsed as $taxRatesUsedPrio ) {
			foreach ( $taxRatesUsedPrio as $taxRatesUsedOrdering ) {
				foreach ($taxRatesUsedOrdering as $taxRate ) {
					/** @var $taxRate cbpaidsalestaxTotalizertype */

					// Small convenience for comparisons below:
					$taxRate_stop_date						=	( $taxRate->stop_date == '0000-00-00' ? '9999-99-99' : $taxRate->stop_date );

					// check periods:
					$ratePeriods							=	array();
					if ( ( $taxRate->start_date == '0000-00-00' ) && ( $taxRate->stop_date == '0000-00-00' ) ) {
						// no time limits for this taxRate:

						$ratePeriods[0]						=	array( '0000-00-00', '0000-00-00', 'all', 0, 0, 0, 0, $taxRate->_itemsUsingThisRate );

					} elseif ( $taxRate->start_stop_date_function == 2 ) {
						// Taxes are applied throughout the period:

						// Apply taxes throughout the validity period of the item:
						foreach ( $taxRate->_itemsUsingThisRate as $paymentItemIndex ) {
							
							$itemStart						=	$paymentItems[$paymentItemIndex]->start_date	   == '0000-00-00 00:00:00' ? '0000-00-00' : date( 'Y-m-d', $cbpaidTimes->strToTime( $paymentItems[$paymentItemIndex]->start_date ) + $offset );
							$itemStop						=	$paymentItems[$paymentItemIndex]->stop_date		   == '0000-00-00 00:00:00' ? '0000-00-00' : date( 'Y-m-d', $cbpaidTimes->strToTime( $paymentItems[$paymentItemIndex]->stop_date  ) + $offset );
							$itemSecondStart				=	$paymentItems[$paymentItemIndex]->stop_date		   == '0000-00-00 00:00:00' ? '0000-00-00' : date( 'Y-m-d', $cbpaidTimes->strToTime( $paymentItems[$paymentItemIndex]->stop_date  ) + $offset + 1 );
							$itemSecondStop					=	$paymentItems[$paymentItemIndex]->second_stop_date == '0000-00-00 00:00:00' ? '0000-00-00' : date( 'Y-m-d', $cbpaidTimes->strToTime( $paymentItems[$paymentItemIndex]->second_stop_date  ) + $offset );
							$itemAutoRecurring				=	$paymentItems[$paymentItemIndex]->autorecurring;
							$hasFirstStop					=	( $itemStop && ( $itemStop !== '0000-00-00' ) );
							$hasSecondPeriod				=	( $hasFirstStop && $anyAutoRecurringInBasket && $itemAutoRecurring );
							$hasSecondStop					=	( $hasSecondPeriod && $itemSecondStop );
							$taxApplicableToFirstPeriod		=						( $taxRate_stop_date >= $itemStart )        && ( ( ( ! $hasFirstStop )  && ( $taxRate->start_date <= $itemStart ) )        || ( $hasFirstStop  && ( $taxRate->start_date <= $itemStop ) ) );
							$taxApplicableToSecondPeriod	=	$hasSecondPeriod && ( $taxRate_stop_date >= $itemSecondStart )  && ( ( ( ! $hasSecondStop ) && ( $taxRate->start_date <= $itemSecondStart  ) ) || ( $hasSecondStop && ( $taxRate->start_date <= $itemSecondStop ) ) );

							if ( $taxApplicableToFirstPeriod || $taxApplicableToSecondPeriod ) {
								// 1) The plan is not only taxed at start date, and:
								// 2) There is at least an intersection in validity dates of taxRate and item:
								if ( $hasFirstStop ) {
	 								if ( $paymentItems[$paymentItemIndex]->getPlanParam( 'tax_taxing_date', 1 ) == 0 ) {
 										// Setting of the item is to apply tax at start of period only, despite the tax being pro-rated for subscription items:
	 									self::addRatePeriodJustStartDates( $ratePeriods, $paymentBasket, $paymentItemIndex, $taxRate, $taxRate_stop_date, $hasSecondPeriod, $itemSecondStart );
	 								} else {
	 									// apply tax during period:

	 									// Item has a stop date: Does this taxRate start after the start (or stops before the stop) of the payment item ? :
	 									$taxStartDate				=	( $taxApplicableToFirstPeriod  && ( $taxRate->start_date > $itemStart )		   ? $taxRate->start_date : '0000-00-00' );
										$taxSecondStartDate			=	( $taxApplicableToSecondPeriod && ( $taxRate->start_date >= $itemSecondStart ) ? $taxRate->start_date : '0000-00-00' );
										$taxStopDate				=	( $taxApplicableToFirstPeriod  && ( $taxRate_stop_date   <= $itemStop ) 	   ? $taxRate->stop_date  : '0000-00-00' );
										$taxSecondStopDate			=	( $taxApplicableToSecondPeriod && ( $taxRate_stop_date   < $itemSecondStop )   ? $taxRate->stop_date  : '0000-00-00' );
		
										if ( ( $taxStartDate == '0000-00-00' ) && ( $taxStopDate == '0000-00-00' ) && ( $taxSecondStartDate == '0000-00-00' ) && ( $taxSecondStopDate == '0000-00-00' ) ) {
											self::addRatePeriodJustStartDates( $ratePeriods, $paymentBasket, $paymentItemIndex, $taxRate, $taxRate_stop_date, $hasSecondPeriod, $itemSecondStart );
										} else {
											// we are in the special case of a tax that cannot be applied throughout the item validity: create a special totalizer entry for this item's taxRate:
											$item_days					=	self::_my_days( $itemStart, $itemStop );
											if ( $taxApplicableToFirstPeriod ) {
												$taxStartDate			=	( ( $taxRate->start_date > $itemStart )		 ? $taxRate->start_date : $itemStart );
												$taxStopDate			=	( ( $taxRate_stop_date   < $itemStop ) 		 ? $taxRate->stop_date  : $itemStop );
												$totalizer_days			=	self::_my_days( $taxStartDate, $taxStopDate );
											} else {
												$totalizer_days			=	0;
											}
											if ( $taxApplicableToSecondPeriod ) {		// means $hasSecondPeriod = true
												$taxSecondStartDate		=	( $taxRate->start_date > $itemSecondStart ) ? $taxRate->start_date : $itemSecondStart;
												if ( $hasSecondStop ) {
													$taxSecondStopDate	=	( $taxRate_stop_date   < $itemSecondStop )  ? $taxRate->stop_date  : $itemSecondStop;
													$second_item_days		=	self::_my_days( $itemSecondStart, $itemSecondStop );
													$second_totalizer_days	=	self::_my_days( $taxSecondStartDate, $taxSecondStopDate );
												} else {
													$taxSecondStopDate		=	'0000-00-00';
													$second_item_days		=	1;
													$second_totalizer_days	=	1;
												}
												
											} else {
												$second_item_days			=	null;
												$second_totalizer_days		=	null;
											}
											if ( $taxApplicableToFirstPeriod && ! $taxApplicableToSecondPeriod ) {
												
												$ratePeriods[]		=	array( $taxStartDate, $taxStopDate, 'first', $item_days, $totalizer_days, 0, 0, array( $paymentItemIndex ) );
											}
											if ( $taxApplicableToSecondPeriod && ! $taxApplicableToFirstPeriod ) {
												$ratePeriods[]		=	array( $taxSecondStartDate, $taxSecondStopDate, 'second', 0, 0, $second_item_days, $second_totalizer_days, array( $paymentItemIndex ) );
											}
											if ( $taxApplicableToFirstPeriod && $taxApplicableToSecondPeriod ) {
												$ratePeriods[]		=	array( $taxStartDate, $taxSecondStopDate, 'all', $item_days, $totalizer_days, $second_item_days, $second_totalizer_days, array( $paymentItemIndex ) );
											}
										}
	 								}
								} else {
									// item has no stop date, so it is not a period, check if tax is applicable at begin:
									self::addRatePeriodJustStartDates( $ratePeriods, $paymentBasket, $paymentItemIndex, $taxRate, $taxRate_stop_date, $hasSecondPeriod, $itemSecondStart );
								}
							}
						}

					} elseif ( $taxRate->start_stop_date_function == 1 ) {
						// Apply all tax at purchase timepoint:

						foreach ( $taxRate->_itemsUsingThisRate as $paymentItemIndex ) {
							// Does this taxRate start after the start (or stops before the stop) of the initiating time of the basket ? :
							$itemSecondStart				=	$paymentItems[$paymentItemIndex]->stop_date		   == '0000-00-00 00:00:00' ? '0000-00-00' : date( 'Y-m-d', $cbpaidTimes->strToTime( $paymentItems[$paymentItemIndex]->stop_date  ) + $offset + 1 );
							$hasSecondPeriod				=	( $itemSecondStart != '0000-00-00' ) && $anyAutoRecurringInBasket && $paymentItems[$paymentItemIndex]->autorecurring;
							self::addRatePeriodJustStartDates( $ratePeriods, $paymentBasket, $paymentItemIndex, $taxRate, $taxRate_stop_date, $hasSecondPeriod, $itemSecondStart );
						}

					}

					// Now that we have listed all different taxRate periods, we create corresponding Tax Totalizers:
					$totalizerType							=	substr( $myClassName, strpos( $myClassName, '_' ) + 1 );		// 'salestax'
					foreach ( $ratePeriods as $rp ) {
						/** @var $salesTaxTotalizer cbpaidPaymentTotalizer_salestax */
						$salesTaxTotalizer					=	new $myClassName();
						$salesTaxTotalizer->totalizer_id	=	(int) $taxRate->id;
						$salesTaxTotalizer->totalizer_type	=	$totalizerType;
						$salesTaxTotalizer->artnum			=	$taxRate->_rule_short_code;
						$salesTaxTotalizer->description		=	$taxRate->description;
						$salesTaxTotalizer->currency		=	$paymentBasket->mc_currency;
						$salesTaxTotalizer->start_date		=	$rp[0];
						$salesTaxTotalizer->stop_date		=	$rp[1];
						if ( $anyAutoRecurringInBasket && ( ( $rp[2] == 'all' ) ||  ( $rp[2] == 'first' ) ) ) {
							$salesTaxTotalizer->first_rate				=	'0.0';
							$salesTaxTotalizer->first_item_days			=	$rp[3];
							$salesTaxTotalizer->first_totalizer_days	=	$rp[4];
							$salesTaxTotalizer->first_original_rate		=	0.0;
						}
						if ( ( ! $anyAutoRecurringInBasket ) || ( $rp[2] == 'all' ) ||  ( $rp[2] == 'second' ) ) {
							$salesTaxTotalizer->rate					=	'0.0';
							$salesTaxTotalizer->item_days				=	( $anyAutoRecurringInBasket ? $rp[5] : $rp[3] );
							$salesTaxTotalizer->totalizer_days			=	( $anyAutoRecurringInBasket ? $rp[6] : $rp[4] );
							$salesTaxTotalizer->original_rate			=	0.0;
						}
						$salesTaxTotalizer->_itemIndexes	=	$rp[7];
						if ( count( $rp[7] ) == 1 ) {
							$salesTaxTotalizer->setPaymentItemObject( $paymentItems[$rp[7][0]] );
						}
						$salesTaxTotalizer->_totalizertypeSettings		=	$taxRate;
						call_user_func_array( $addTotalizerToBasketFunc, array( $salesTaxTotalizer ) );
					}
				}
			}
		}
	}
	/**
	 * Adds rate periods start and stop periods
	 *
	 * @param  array                        $ratePeriods
	 * @param  cbpaidPaymentBasket          $paymentBasket
	 * @param  int                          $paymentItemIndex
	 * @param  cbpaidsalestaxTotalizertype  $taxRate
	 * @param  string                       $taxRate_stop_date
	 * @param  boolean                      $hasSecondPeriod
	 * @param  string                       $itemSecondStart
	 */
	private static function addRatePeriodJustStartDates( &$ratePeriods, $paymentBasket, $paymentItemIndex, $taxRate, $taxRate_stop_date, $hasSecondPeriod, $itemSecondStart ) {
		global $_CB_framework;
		$offset						=	(int) $_CB_framework->getCfg( 'offset' ) * 3600;
		$paymentBasketDay			=	date( 'Y-m-d', cbpaidTimes::getInstance()->strToTime( $paymentBasket->time_initiated ) + $offset );

		$taxApplicableToFirstPeriod		=	( $taxRate->start_date <= $paymentBasketDay ) && ( $taxRate_stop_date >= $paymentBasketDay );
		$taxApplicableToSecondPeriod	=	$hasSecondPeriod  && ( $taxRate->start_date <= $itemSecondStart ) && ( $taxRate_stop_date >= $itemSecondStart );

		if ( $taxApplicableToFirstPeriod && $taxApplicableToSecondPeriod ) {
			$ratePeriods[0][0]		=	'0000-00-00';
			$ratePeriods[0][1]		=	'0000-00-00';
			$ratePeriods[0][2]		=	'all';
			$ratePeriods[0][3]		=	0;
			$ratePeriods[0][4]		=	0;
			$ratePeriods[0][5]		=	0;
			$ratePeriods[0][6]		=	0;
			$ratePeriods[0][7][]	=	$paymentItemIndex;
		} elseif ( $taxApplicableToFirstPeriod ) {
			$ratePeriods[]			=	array( $paymentBasketDay, $paymentBasketDay, 'first', 0, 0, 0, 0, array( $paymentItemIndex ) );
		} elseif ( $taxApplicableToSecondPeriod ) {
			$ratePeriods[]			=	array( $itemSecondStart, $itemSecondStart, 'second', 0, 0, 0, 0, array( $paymentItemIndex ) );
		}
	}
	/**
	 * Time-prorates a tax for an item
	 *
	 * @param  cbpaidPaymentItem  $item
	 * @param  boolean            $isTotalizerFirstPeriod
	 * @return float              a value between 0.0 and 1.0
	 */
	public function proRatePeriod( $item, $isTotalizerFirstPeriod ) {
		if ( ( $this->start_date === '0000-00-00' ) && ( $this->stop_date === '0000-00-00' ) ) {
			return 1;
		}
		// in this case the item has mandatorily a start_date and a stop_date:
		if ( $isTotalizerFirstPeriod ) {
			// first period:
			$taxingDays			=	$this->first_totalizer_days;
			$itemDays			=	$this->first_item_days;
		} else {
			// second period:
			$taxingDays			=	$this->totalizer_days;
			$itemDays			=	$this->item_days;
		}
		if ( $taxingDays > $itemDays ) {
			trigger_error( sprintf('CBSubs Tax issue: %d taxing days > %d item days', $taxingDays, $itemDays ), E_USER_NOTICE );
		}
		if ( ( $taxingDays > 0 ) && ( $itemDays > 0 ) ) {
			return ( ( (float) $taxingDays ) / (float) $itemDays );
		} elseif ( ( $taxingDays == 0 ) && ( $itemDays == 0 ) ) {
			return 1;
		} else {
			return 0;
		}
	}
	/**
	 * Renders a $variable for an $output
	 *
	 * @param  mixed   $variable
	 * @param  string  $output
	 * @param  boolean $rounded
	 * @return mixed
	 */
	public function renderColumn( $variable, $output = 'html', $rounded = false  ) {
		if ( ( in_array( $variable, array( 'first_tax_amount', 'tax_amount' ) ) ) && ( $this->$variable == 0 ) ) {
			return null;
		}
		return parent::renderColumn( $variable, $output, $rounded );
	}
	/**
	 * Calculates rounded number of days between two dates
	 *
	 * @param  string  $start
	 * @param  string  $stop
	 * @return int
	 */
	private static function _my_days( $start, $stop ) {
		return round( ( strtotime( $stop ) - strtotime( $start ) ) / 86400 ) + 1;
	}
/*
	protected static function _computeWithoutTax( $amountTaxIncl, $taxRates, $currency, $isFirstPeriod ) {
		return self::_computeTax( $amountTaxIncl, array_reverse( $taxRates, true ), $currency, $isFirstPeriod, 'substract' );
	}
	
	/**
	 * 
	 * @param  float                   $amountTaxExcl
	 * @param  array of cbpaidsalestaxTotalizertype  $taxRates
	 * @param  boolean                 $isFirstPeriod
	 * @param  string                  $way ('add', 'substract')
	 * @return float                   Amount with (add) or without (substract mode) tax
	 *
	protected static function _computeTax( $amountTaxExcl, $taxRates, $currency, $isFirstPeriod, $way = 'add' ) {
		$amount						=	$amountTaxExcl;
		if ( $amountTaxExcl != null ) {
			$taxCompounder			=	new cbpaidTaxCrossTotalizer2();
			foreach ( $taxRates as $rate ) {
				// $rate			=	NEW cbpaidsalestaxTotalizertype();
				$taxCompounder->addRate( $rate );
			}
			$amount					=	$taxCompounder->compound( $amount, $amountTaxExcl, $currency, $isFirstPeriod, $way );
		}
		return $amount;
	}
*/
	/**
	 * Applies $this totalizer to the $paymentBasket
	 * 
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  boolean              $anyAutoRecurringInBasket
	 * @return boolean              TRUE: Totalizer applied, FALSE: remove $this totalizer from $paymentBasket 
	 */
	public function applyThisTotalizerToBasket( $paymentBasket, $anyAutoRecurringInBasket ) {
		if ( ( $this->first_rate || $this->rate ) && ( ( $this->first_rate != 0 ) || ( $this->rate != 0 ) ) ) {
			if ( $anyAutoRecurringInBasket ) {

				if ( ( ! $paymentBasket->period1 ) && ( $this->first_rate != $this->rate ) ) {
					// create different first period in basket total, as discount won't apply same way:
					$paymentBasket->period1			=	$paymentBasket->period3;
					$paymentBasket->mc_amount1		=	$paymentBasket->mc_amount3;
				}

				if ( $paymentBasket->period1 ) {
					$paymentBasket->mc_amount1	+=	$this->first_rate;
					//TODO there is no mc_tax1 and mc_tax3 in cbpaidPaymentBasket for now... so don't note it specifically.
					$paymentBasket->tax			+=	$this->first_rate;
					$paymentBasket->mc_gross	+=	$this->first_rate;
				} else {
					$paymentBasket->tax			+=	$this->rate;
					$paymentBasket->mc_gross	+=	$this->rate;
				}
				$paymentBasket->mc_amount3		+=	$this->rate;

			} else {
				$paymentBasket->tax					+=	$this->rate;
				$paymentBasket->mc_gross			+=	$this->rate;
			}
		} else {
			// if ( $this->_totalizertypeSettings->show_also_zero_values ) {
			if ( $anyAutoRecurringInBasket && ( ! $paymentBasket->period1 ) && ( $this->first_rate == 0 ) ) {
				// avoid displaying "0.- then 0.-" if all items above this one have only one value:
				$this->first_rate				=	null;
				$this->first_item_days			=	null;
				$this->first_totalizer_days		=	null;
				$this->first_original_rate		=	null;
			}
			// } else {
			//	return false;		// to unset( $taxableTotalizers[$k] );
			// }
		}
		return true;
	}
}

/**
 * Class for the calculating totalizer memory-only object
 */
class cbpaidCrossTotalizer_salestax extends cbpaidCrossTotalizer {
	/**
	 * Gives $item->first_rate or $item->rate of $item depending of $first
	 * @param  cbpaidPaymentItem  $item
	 * @param  boolean            $inclusive
	 * @param  boolean            $first
	 * @param  boolean            $itemHasReallyFirstRate
	 * @return float
	 */
	protected function _getItemAmount_first_incl( $item, $inclusive, $first, $itemHasReallyFirstRate = false ) {
		if ( $first ) {
			if ( $itemHasReallyFirstRate ) {
				$amt					=	$item->first_rate + $item->first_discount_amount;
			} else {
				$amt					=	$item->rate		  + $item->first_discount_amount;
			}
		} else {
			$amt						=	$item->rate + $item->discount_amount;
		}
		if ( $inclusive ) {
			$amt						+=	$first ? $item->first_tax_amount : $item->tax_amount;
		}
		return $amt;
	}
	/**
	 * Returns name of totalizer total column in payment item
	 *
	 * @param  boolean  $first  If it's first amount
	 * @return string
	 */
	protected function _getItemTotalizerColumnName( $first ) {
		return $first ? 'first_tax_amount' : 'tax_amount';
	}
}
/**
 * Class definition for the calculating totalizer memory-only object
 */
class cbpaidTaxCrossTotalizer2 {
	protected $ratesToCompound	=	array();
	/**
	 * Adds a rate to this Cross-Totalizer
	 *
	 * @param  cbpaidsalestaxTotalizertype $rate
	 */
	public function addRate( $rate ) {
		$this->ratesToCompound[(int) $rate->priority][]	=	$rate;
	}
	/**
	 * Compounds rates of this compounder added with addRate() method
	 *
	 * @param  float    $amount
	 * @param  float    $amountTaxExcl
	 * @param  string   $currency
	 * @param  boolean  $isFirstPeriod
	 * @param  string   $way            ('add', 'substract')
	 * @return float|int
	 */
	public function compound( $amount, $amountTaxExcl, $currency, $isFirstPeriod, $way ) {
		foreach ($this->ratesToCompound as $ratesOfPriority ) {
			$extraAmountBefore			=	0;
			$extraPercents				=	0;
			$extraAmountAfter			=	0;
			foreach ( $ratesOfPriority as $rate ) {
				/** @var $rate cbpaidsalestaxTotalizertype */
				$extraAmountBefore		+=	$rate->getAmountBeforePercents( $amount, $amountTaxExcl, 1, $isFirstPeriod, $currency );
				$extraPercents			+=	$rate->getPercents( $amount, $amountTaxExcl, 1, $isFirstPeriod );
				$extraAmountAfter		+=	$rate->getAmountAfterPercents( $amount, $amountTaxExcl, 1, $isFirstPeriod, $currency );
			}
			if ( $way == 'add' ) {
				$amount					=	( ( $amount + $extraAmountBefore ) * ( 1 + $extraPercents ) ) + $extraAmountAfter;
			} elseif ( $way == 'substract' ) {
				$amount					=	( ( $amount - $extraAmountAfter ) / ( 1 + $extraPercents ) ) - $extraAmountBefore;
			}
		}
		return $amount;
	}
}
/**
* Paid Subscriptions Tab Class for handling the CB tab api
*/
class getcbsubtaxTab extends cbTabHandler {
	/**
	 * Integration when a new payment item is added to a basket
	 *
	 * @param  string                    $event
	 * @param  cbpaidSomething           $something
	 * @param  cbpaidPaymentBasket|null  $paymentBasket
	 * @param  cbpaidPaymentItem         $paymentItem
	 */
	public function onCPayPaymentItemEvent( $event, /** @noinspection PhpUnusedParameterInspection */ $something, /** @noinspection PhpUnusedParameterInspection */ $paymentBasket, $paymentItem ) {
		if ( $event == 'addSomethingToBasket' ) {
			$tax_rule					=	(int) $paymentItem->getPlanParam( 'tax_rule_id', 0, null );
			if ( $tax_rule == 0 ) {
				$tax_rule				=	(int) cbpaidTaxRule::getDefaultTaxRuleId();
			}
			$paymentItem->tax_rule_id	=	$tax_rule;
		}
	}
	/**
	 * Extends the XML invoice address in params
	 *
	 * @param  SimpleXMLElement     $param
	 * @param  PluginTable          $pluginObject
	 * @param  cbpaidPaymentBasket  $paymentBasket  (the data being displayed)
	 * @param  boolean              $isSaving
	 * @return SimpleXMLElement
	 */
	public function onxmlBeforeCbSubsDisplayOrSaveInvoice( /** @noinspection PhpUnusedParameterInspection */ $param, $pluginObject, $paymentBasket, $isSaving ) {
		global $_CB_framework, $_PLUGINS;

		$paymentItems			=	$paymentBasket->loadPaymentItems();
		$taxableTotalizers		=	$paymentBasket->loadPaymentTotalizers();

		$_PLUGINS->loadPluginGroup( 'user/plug_cbpaidsubscriptions/plugin/cbsubstax/validations', null, ( $_CB_framework->getUi() == 2 ? 0 : 1 ) );

		$taxRulesRates				=	cbpaidPaymentTotalizer_salestax::getApplicableRatesWithoutBusinessCheck( $paymentBasket, $paymentItems, $taxableTotalizers );
		$fromXml					=	array();
		foreach ( $taxRulesRates as $AllTaxRates ) {
			foreach ( $AllTaxRates as $taxRate ) {
				//$taxRate	= NEW cbpaidsalestaxTotalizertype();

				$business_check		=	$taxRate->business_check;
				if ( $business_check ) {
					$absoluteValidationsPath	=	$_CB_framework->getCfg('absolute_path') . '/'. $_PLUGINS->getPluginRelPath( $pluginObject ) . '/plugin/cbsubstax/validations/' . $business_check;
					$valphp		=	$absoluteValidationsPath . '/validation.php';
					if ( is_readable( $valphp ) ) {
						/** @noinspection PhpIncludeInspection */
						include_once $valphp;
						// $className	=	'cbpaidValidate_' . $tax->business_check;
					}
					$fromFile		=	$absoluteValidationsPath . '/xml/edit.invoice.xml';
					if ( is_readable( $fromFile ) ) {
						$fromRoot	=	new SimpleXMLElement( $fromFile, LIBXML_NONET | ( defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0 ), true );
						$fromXml	=	array_merge( $fromXml, $fromRoot->xpath( '/*/editinvoicevalidationintegration/*' ) );
					}
				}
			}
		}
		return $fromXml;
	}
	/**
	 * Extends the XML invoice address in params
	 *
	 * @param  SimpleXMLElement     $param
	 * @param  PluginTable          $pluginObject
	 * @param  cbpaidPaymentBasket  $paymentBasket  (the data being displayed)
	 * @param  boolean              $isSaving
	 * @return SimpleXMLElement
	public function OLD_onxmlBeforeCbSubsDisplayOrSaveInvoice( $param, $pluginObject, $paymentBasket, $isSaving ) {
		global $_CB_framework, $_PLUGINS;

		$paymentBasket->loadPaymentTotalizers();

		$_PLUGINS->loadPluginGroup( 'user/plug_cbpaidsubscriptions/plugin/cbsubstax/validations', null, ( $_CB_framework->getUi() == 2 ? 0 : 1 ) );

		foreach ( $paymentBasket->_paymentTotalizers as $totalizer ) {
			$business_check		=	$totalizer->getTotalizerParam( 'business_check', null, null );
			if ( $business_check ) {
				$absoluteValidationsPath	=	$_CB_framework->getCfg('absolute_path') . '/'. $_PLUGINS->getPluginRelPath( $pluginObject ) . '/plugin/cbsubstax/validations/' . $business_check;
				$fromFile		=	$absoluteValidationsPath . '/xml/edit.invoice.xml';
				if ( is_readable( $fromFile ) ) {
					$fromRoot	=	new SimpleXMLElement( $fromFile, LIBXML_NONET | ( defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0 ), true );
					$fromXml	=	$fromRoot->xpath( '/*   /      editinvoicevalidationintegration/*' );
					return $fromXml;
				}
			}
		}
		return array();
	}
	 */
}
/**
 * Definition Class for validating the business status for a basket and a tax (extended by EU tax)
 */
abstract class cbpaidValidate extends cbObject {
	/**
	 * Validates and computes business status on payment invoice save 
	 *
	 * @param  cbpaidPaymentBasket          $paymentBasket
	 * @param  cbpaidsalestaxTotalizertype  $salestaxTotalizerType
	 */
	abstract public function validateInvoiceAddress( $paymentBasket, $salestaxTotalizerType );
}
