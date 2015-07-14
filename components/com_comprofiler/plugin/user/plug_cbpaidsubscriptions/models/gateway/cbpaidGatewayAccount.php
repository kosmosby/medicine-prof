<?php
/**
 * @version $Id: cbpaidGatewayAccount.php 1546 2012-12-02 23:16:25Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Payment gateway account database table class
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 */
abstract class cbpaidGatewayAccount extends cbpaidTable {
	/**
	 * Primary key
	 * @var int */
	public $id							=	null;
	public $gateway_type;
	public $owner						=	0;
	public $name;
	public $enabled;
	public $normal_gateway;
	public $ordering;
	public $currencies_accepted;
	public $currency_acceptance_mode	=	'H';
	public $currency_acceptance_text;
	public $viewaccesslevel				=	1;
	public $params;
	public $cssclass;
	/**
	 * Account-specific parameters
	 * @var ParamsInterface */
	protected $_accountParams			=	null;
	/**
	 * Payment Processor Payment handler
	 * @var cbpaidPayHandler */
	protected $_payHandler				=	null;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_gateway_accounts', 'id', $db );
		$this->_historySetLogger();
	}
	/**
	 *	Binds an array/hash from database to this object
	 *
	 *	@param  int $oid  optional argument, if not specifed then the value of current key is used
	 *	@return mixed     any result from the database operation
	 */
	public function load( $oid = null ) {
		return parent::load( $oid);
	}
	/*
		public function store( ) {
			trigger_error( 'Don't use cbpaidGatewayAccount::store', E_USER_ERROR );
			return false;
		}
	*/
	/**
	 *	Check for whether dependancies exist for this object in the db schema
	 *
	 *	@param  int      $oid   Optional key index
	 *	@return boolean         TRUE: OK to delete, FALSE: not OK to delete, error in $this->_error
	 */
	public function canDelete( $oid = null ) {
		$relatedTables	=	array(	CBPTXT::T("Payment Baskets")		=> '#__cbsubs_payment_baskets',
			CBPTXT::T("Payments")				=> '#__cbsubs_payments',
			CBPTXT::T("Payment Notifications")	=> '#__cbsubs_notifications' );

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = $oid;
		}
		foreach ( $relatedTables as $text => $table ) {
			$query = "SELECT COUNT(*)"
				. "\n FROM `" . $table . "`"
				. "\n WHERE `gateway_account` = ". (int) $this->$k
			;
			$this->_db->setQuery( $query );

			$count = $this->_db->loadResult();

			if ( $count > 0 ) {
				$this->setError( sprintf( CBPTXT::T("%d %s exist for this gateway account."), $count, $text ) );
				return false;
			}
		}
		return true;
	}
	/**
	 * Returns the payment mean name
	 *
	 * @return string
	 */
	public function getPayMeanName() {
		return substr( get_class( $this ), strlen( __CLASS__ ) );
	}
	/**
	 * Gets payment mean handler
	 *
	 * @param  string                     $methodCheck
	 * @return cbpaidPayHandler|boolean
	 */
	public function & getPayMean( $methodCheck = null ) {
		if ( $this->_payHandler === null ) {
			$name				=	$this->getPayMeanName();
			if ( ! $name ) {
				$false				=	false;
				return $false;
			}
			$className			=	'cbpaid' . $name;
			if ( ! class_exists( $className ) ) {
				cbpaidApp::import( 'processors.' . $name . '.' . $name );
			}
			$this->_payHandler	=	new $className( $this );
		}
		if ( ( $methodCheck === null ) || ( $methodCheck == $this->_payHandler->getPayName() ) ) {
			return $this->_payHandler;
		} else {
			$false				=	false;
			return $false;
		}
	}
	/*
	 * Get an attribute of this stored object
	 *
	 * @param  string    $paramName     The name of the parameter
	 * @param  mixed     $default       The default value of the parameter
	 * @param  string    $paramColumn   The storage column in the $this object
	 * @return mixed
	 *
	public function getParam( $paramName, $default, $paramColumn = 'params' ) {
		if ( $this->_accountParams === null ) {
			$this->_accountParams	=	new Registry( $this->$paramColumn );
		}
		return $this->_accountParams->get( $paramName, $default );
	}
	*/
	/**
	 * Checks if this payment gateway account accepts $currency
	 *
	 * @param  string  $currency  3-char ISO currency
	 * @return boolean            TRUE: accepts it
	 */
	public function acceptsCurrency( $currency ) {
		if ( $this->currency_acceptance_mode == 'H' ) {
			return ( ( $this->currencies_accepted == '' ) || ( in_array( $currency, explode( '|*|', $this->currencies_accepted ) ) ) );
		}
		return true;
	}
}
