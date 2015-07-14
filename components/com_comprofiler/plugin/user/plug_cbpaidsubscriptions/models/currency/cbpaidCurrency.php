<?php
/**
* @version $Id: cbpaidCurrency.php 1556 2012-12-20 14:47:51Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Xml\SimpleXMLElement;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
* Currency converter class
*/
class cbpaidCurrency extends cbpaidTable {
	/** @var int Primary key */
	public $id					= null;
	public $base_currency;
	public $currency;
	public $rate;
	public $source;
	/** @var string  timedate last currency rate updating date (remote server time) */
	public $last_updated_date;
	/** @var string  timedate last renewal date (local server time) */
	public $last_renewed_date;
	/** @var int */
	public $ordering;
	/** @var array   rates cache */
	protected $_cache = array();
	/** @var string  default basis currency */
	protected $_default_base_currency = 'EUR';
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( '#__cbsubs_currencies', 'id' );
		$this->_historySetLogger();
	}
	/**
	 * Fetch an http file from the URL
	 *
	 * @param  string  $url    URL from where to fetch
	 * @return string          content, or NULL in case of error
	 */
	private function _fetch_http_file( $url ) {
		cbimport( 'cb.snoopy' );

		$s					=	new CBSnoopy();
		$s->read_timeout	=	20;
		@$s->fetch( $url );
		if ( $s->error || $s->status != 200 ) {
  		  	// echo '<font color="red">Connection to update server failed: ERROR: ' . $s->error . ($s->status == -100 ? 'Timeout' : $s->status).'</font>';
  		  	$content		=	null;
	    } else {
			$content		=	$s->results;
	    }
		return $content;
	}
	/**
	 * Fetches XML file from currency server and parses currency results
	 * @access private
	 *
	 * @param  string $sourceURL         URL
	 * @param  string $source            Text to store as source of currency conversion rate
	 * @param  string $source_currency   Main currency of the source for rates
	 * @param  string $tag               Main path to XML
	 * @param  string $subtag            Sub-tag to XML
	 * @param  string $dateAttr          tag-name for date
	 * @param  string $subsubtag         sub-sub tag for XML of currency
	 * @param  string $currAttr          attribute for currency name
	 * @param  string $rateAttr          attribute for currency rate
	 */
	private function _readCurrencies( $sourceURL = 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml', $source='European Central Bank', $source_currency = 'EUR',
							   $tag = 'Cube/Cube', $subtag = 'Cube', $dateAttr = 'time', /** @noinspection PhpUnusedParameterInspection */ $subsubtag = 'Cube', $currAttr = 'currency', $rateAttr = 'rate' ) {
		global $_CB_framework, $_CB_database;
		
		$now			=	$_CB_framework->now();
		
		$xmlText		=	$this->_fetch_http_file( $sourceURL );

		if ( $xmlText && ( substr( $xmlText, 0 , 5 ) == '<?xml' ) ) {
			// handles limitations of SimpleXML:
			$xmlText	=	str_replace('gesmes:', '', $xmlText );
			
			try{
				$xdoc		=	new SimpleXMLElement( $xmlText, LIBXML_NONET | ( defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0 ) );
			} catch (Exception $e){
				cbpaidApp::getBaseClass()->setLogErrorMSG( 3, null, "ECB Currency conversion XML result invalid" . ": " . $e->getMessage(). ': Received reply: ' . $xmlText, null );
				$xdoc		=	null;
			}
			if ( $xdoc && count( $xdoc->children() ) > 0 ) {
				// get the currencies element
				/** @var $currencies_element SimpleXMLElement */
				$currencies_element =& $xdoc->getElementByPath( $tag );
				if ( $currencies_element ) {
					$last_updated_date = $currencies_element->attributes( $dateAttr );
					if ( count( $currencies_element->children() ) > 0 ) {
						/** @var $xCu SimpleXMLElement */
						foreach ( $currencies_element->children() as $xCu) {
							if ( $xCu->getName() == $subtag ) {
								$currency = $xCu->attributes( $currAttr );
								$rate	  = $xCu->attributes( $rateAttr );
								if ( $currency && $rate ) {
									$currObj = new cbpaidCurrency( $_CB_database );
									$currObj->setCurrencyRate( $source_currency, $currency, $rate, $source, $last_updated_date, $now );
								}
							}
						}
						$currObj = new cbpaidCurrency( $_CB_database );
						$currObj->setCurrencyRate( $source_currency, $source_currency, 1.0,  'CB', $last_updated_date, $now );
					}
				}
			}
		}
	}
	/**
	 * Checks if currency conversion of $this is up to date
	 *
	 * @param  int     $extraHoursOfValidity  number of hours of extra-validity
	 * @param  string  $renewalInterval       strtotime relative time of validity (default: '+12 hours')
	 * @return boolean                        TRUE: entry is up-to-date, FALSE: not up-to-date
	 */
	public function isUpToDate( $extraHoursOfValidity = 0, $renewalInterval = '+12 hours'	/* , $dailyGMTrenewalTime = '13' */ ) {
		global $_CB_framework;

		if ( $this->last_renewed_date && ( $this->last_renewed_date != '0000-00-00 00:00:00' ) ) {
			$now = $_CB_framework->now();
			list($y, $c, $d, $h, $m, $s) = sscanf( $this->last_renewed_date, '%d-%d-%d %d:%d:%d' );
			$lastRenewedTime = mktime($h, $m, $s, $c, $d, $y);
			$nextRenewalDue = strtotime( $renewalInterval, $lastRenewedTime );
			$result = ( $now < ( $nextRenewalDue + ( $extraHoursOfValidity * 3600 ) ) );
						/* LATER:
						if ( $now > $nextRenewalDue ) {
							$currentGMTHour		= gmdate( 'H', $now );
							$currentLocalHour	= date(   'H', $now );
							$tOffset			= $currentGMTHour - $currentLocalHour;
							if ( $tOffset > 12 ) {
								$tOffset -= 24;
							} elseif ( $tOffset < -11 ) {
								$tOffset += 24;
							}
							$TimeOffsetHoursText = ( ( $tOffset >= 0 ) ? ( '+' . $tOffset ) : $tOffset ) . ' hours';
							$gmTime = strtotime( $TimeOffsetHoursText, $lastRenewedTime );
						*/
		} else {
			$result = false;
		}
		return $result;
	}
	/**
	 * Sets and stores in database this currency rate
	 *
	 * @param  string $base_currency      Base currency
	 * @param  string $currency           Foreign currency
	 * @param  float  $rate               Conversion rate ( foreign / base )
	 * @param  string $source             Source of information text
	 * @param  string $last_updated_date  Date of last update
	 * @param  int    $now                Unix Time of the update
	 */
	public function setCurrencyRate( $base_currency, $currency, $rate, $source, $last_updated_date, $now ) {
		global $_CB_database;

		$this->base_currency		= $base_currency;
		$this->currency				= $currency;
		$this->rate					= $rate;
		$this->source				= $source;
		$this->last_updated_date	= $last_updated_date;	// ( ( strlen( $last_updated_date ) > 10 ) ? $last_updated_date : $last_updated_date . ' 00:00:00' );
		$this->last_renewed_date	= date( 'Y-m-d H:i:s', $now );
		$this->historySetMessage( 'New currency exchange rate received from source: ' . $source );
		if (!$this->store() ) {
			trigger_error( 'payment_currencies store error:'.htmlspecialchars($_CB_database->getErrorMsg()), E_USER_ERROR );
		}
	}
	/**
	 * Returns a converted $amount $fromCurrency $toCurrency
	 *
	 * @param  string  $fromCurrency   ISO 3-chars currency code
	 * @param  string  $toCurrency     ISO 3-chars currency code
	 * @param  float   $amount
	 * @return float                   if amount could be converted, null if rates are missing
	 */
	public function convertCurrency( $fromCurrency, $toCurrency, $amount ) {
		if ( ( ! $fromCurrency ) || ( ! $toCurrency ) ) {
			trigger_error( sprintf( 'cbpaidCurrency::convertCurrency: empty currency parameter: from: %s, t: %s.', $fromCurrency, $toCurrency ), E_USER_WARNING );
		}
		$return						=	null;
		if ( $fromCurrency == $toCurrency ) {
			$return					=	$amount;
		} elseif ( $fromCurrency == $this->_default_base_currency ) {
			if ( $this->loadCurrencyRate( $this->_default_base_currency, $toCurrency ) ) {
				$return				=	$amount * $this->rate;
			}
		} elseif ( $toCurrency == $this->_default_base_currency ) {
			if ( $this->loadCurrencyRate( $this->_default_base_currency, $fromCurrency ) ) {
				$return				=	$amount / $this->rate;
			}
		} else {
			if ( $this->loadCurrencyRate( $this->_default_base_currency, $fromCurrency ) ) {
				$fromRate			=	$this->rate;
				if ( $this->loadCurrencyRate( $this->_default_base_currency, $toCurrency ) ) {
					$return			=	$amount * $this->rate / $fromRate;
				}
			}
		}
		if ( $return === null ) {
			$this->setError( CBPTXT::Th( "Currency Auto-Update Error: could not reach [SERVER_NAME_WITH_LINK] server",
										 array( '[SERVER_NAME_WITH_LINK]' => '<a href="http://www.ecb.int/" target="_blank">http://www.ecb.int</a>' ) ) );
		}
		return $return;
	}
	/**
	 * Loads $this from the database
	 * @access private
	 *
	 * @param  string $base_currency  Base currency code
	 * @param  string $currency       Foreign currency code
	 * @return boolean                TRUE: success, FALSE : couldn't load from DB (db has error code)
	 */
	protected function _loadCurrencyRateFromDataBase( $base_currency, $currency ) {
		$query = "SELECT *"
		. "\n FROM `" . $this->_tbl . "`"
		. "\n WHERE base_currency = '" . $base_currency . "'"
		. "\n AND currency = '" . $currency . "'"
		;
		$this->_db->setQuery( $query );
		
		return $this->_db->loadObject( $this );
	}
	/**
	 * Loads an up-to-date conversion rate from database, and if needed updates the server
	 *
	 * @param  string   $base_currency
	 * @param  string   $currency
	 * @param  int      $extraHoursOfValidity
	 * @return boolean
	 */
	public function loadCurrencyRate( $base_currency, $currency, $extraHoursOfValidity = 168 ) {
		if ( isset( $this->_cache[$currency] ) ) {
			$this->rate		= $this->_cache[$currency];
			$this->currency	= $currency;
			$result = true;
		} else {
			$result = $this->_loadCurrencyRateFromDataBase( $base_currency, $currency );
			if ( ( ! $result ) || ( ! $this->isUpToDate() ) ) {
				$this->_readCurrencies();
				$result = $this->_loadCurrencyRateFromDataBase( $base_currency, $currency );
				if ( ! $this->isUpToDate( $extraHoursOfValidity ) ) {
					$result = false;
				}
			}
			if ( $result ) {
				$this->_cache[$currency] = $this->rate;
			}
		}
		if ( $this->rate == 0 ) {
			$result = false;
		}
		return $result;
	}
	/**
	 * Stores $this into database:
	 * - try to update by id if known, otherwise by currency codes pair
	 * - inserts if not existant
	 *
	 * @param  boolean  $updateNulls   Update also NULLs of $this in database
	 * @return boolean                 Result: TRUE: OK, FALSE: error in database
	 */
	public function store( $updateNulls = false )
	{
		$k						=	$this->_tbl_key;

		if ( $this->$k ) {
			$sql 				=	"SELECT " . $this->_tbl_key . " FROM " . $this->_tbl
				 				.	"\n WHERE " . $this->_tbl_key . " = ".  (int) $this->$k;
		} else {
			$sql				=	"SELECT " . $this->_tbl_key . " FROM " . $this->_tbl
								.	"\n WHERE base_currency = '" . $this->_db->getEscaped( $this->base_currency )	. "'"
								.	"\n AND currency = '"		. $this->_db->getEscaped( $this->currency )		. "'";
		}
		$this->_db->SetQuery( $sql );
		$idArrays				=	$this->_db->loadResultArray();

		if ( count( $idArrays ) > 0 ) {
			// existing record:
			if ( ! $this->$k ) {
				$this->$k		=	$idArrays[0];
			}
			$ret				= parent::store( $updateNulls );
			
		} else {
			// new record
			$sql				=	"SELECT MAX(ordering) FROM " . $this->_tbl;
			$this->_db->SetQuery( $sql );
			$max				=	$this->_db->LoadResult();
			$this->ordering 	=	$max+1;
			$this->$k			=	null;
			$ret				=	parent::store( $updateNulls );
		}

		return $ret;
	}
}	// cbpaidCurrency

/* TEST:
$_CBPAY_CURRENCIES = new cbpaidCurrency( $_CB_database );
$from = 'CHF'; $to = 'EUR';
echo "<br />$from --> $to : " . $_CBPAY_CURRENCIES->convertCurrency( $from, $to, 1.0 );
$from = 'EUR'; $to = 'CHF';
echo "<br />$from --> $to : " . $_CBPAY_CURRENCIES->convertCurrency( $from, $to, 1.0 );
$from = 'USD'; $to = 'EUR';
echo "<br />$from --> $to : " . $_CBPAY_CURRENCIES->convertCurrency( $from, $to, 1.0 );
$from = 'EUR'; $to = 'USD';
echo "<br />$from --> $to : " . $_CBPAY_CURRENCIES->convertCurrency( $from, $to, 1.0 );
$from = 'USD'; $to = 'CHF';
echo "<br />$from --> $to : " . $_CBPAY_CURRENCIES->convertCurrency( $from, $to, 1.0 );
*/
