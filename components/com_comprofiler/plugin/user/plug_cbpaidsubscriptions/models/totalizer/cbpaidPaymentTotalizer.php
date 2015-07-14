<?php
/**
 * @version $Id: cbpaidPaymentTotalizer.php 1599 2012-12-28 03:38:32Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * This file includes all basic CBSubs Totalizer classes for sub-totals and grand-totals
 */

/**
 * This class is the Totalizer definition class (settings)
 */
abstract class cbpaidTotalizerType extends cbpaidTable { }
/**
 * Payment totalizer database table class: This class is the Totalizer type that is in cbsubs_totalizers and is related to baskets
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 */
class cbpaidPaymentTotalizer extends cbpaidInstanciator {
	protected $_classnameField			=	'totalizer_type';
	protected $_classnamePrefix			=	'cbpaidPaymentTotalizer_';
	protected $_classLibraryPrefix		=	'plugin.';
	protected $_classLibrarySubfolders	=	true;

	/**
	 * Primary key
	 * @var int */
	public $id							= null;
	public $payment_basket_id;
	public $payment_item_id;
	public $ordering					=	0;
	public $totalizer_type;
	public $totalizer_id;
	public $quantity;
	public $unit;
	public $artnum;
	public $description;
	public $totalizer_days;
	public $item_days;
	public $first_totalizer_days;
	public $first_item_days;
	public $currency;
	public $original_rate;
	public $first_original_rate;
	public $rate;
	public $first_rate;
	public $start_date;
	public $stop_date;
	public $tax_rule_id;
	public $tax_amount;
	public $first_tax_amount;
	/**
	 * @var cbpaidPaymentBasket
	 */
	protected $_paymentBasket;
	/**
	 * @var cbpaidPaymentItem
	 */
	protected $_paymentItem;
	/**
	 * @var cbpaidTotalizerType
	 */
	protected $_totalizerType;
	/**
	 * Constructor
	 * @access private
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db = null ) {
		parent::__construct( '#__cbsubs_payment_totalizers', 'id', $db );
	}
	/**
	 * Gets a single instance of the class
	 *
	 * @param  CBdatabase  $db
	 * @return stdClass
	 */
	public static function & getInstance( &$db = null ) {
		trigger_error('Totalizer::getInstance called, should not be!', E_USER_ERROR );
	}

	/**
	 * Gets the type name of the totalizer, which is also the integration name
	 *
	 * @return string  Type of totalizer
	 */
	public function getTotalizerType( ) {
		$names		=	explode( '_', get_class( $this ), 2 );
		return $names[1];
	}
	/**
	 * Updates the payment basket id of the payment totalizer
	 *
	 * @param  int  $paymentBasketId
	 */
	public function setPaymentBasket( $paymentBasketId ) {
		$this->payment_basket_id		=	(int) $paymentBasketId;
	}
	/**
	 * Updates the payment item id of the payment totalizer. If $paymentItemId is Null, sets the id of the payment item previously set by setPaymentItemObject() method
	 *
	 * @param  int|null  $paymentItemId
	 */
	public function setPaymentItem( $paymentItemId = null ) {
		if ( ( $paymentItemId === null ) && isset( $this->_paymentItem->id ) ) {
			$paymentItemId				=	$this->_paymentItem->id;
		}
		$this->payment_item_id			=	(int) $paymentItemId;
	}
	/**
	 * Updates the payment item corresponding to the payment totalizer (if any)
	 *
	 * @param  cbpaidPaymentItem  $paymentItem
	 */
	public function setPaymentItemObject( $paymentItem ) {
		$this->_paymentItem				=	$paymentItem;
	}
	/**
	 * Updates the payment basket id of the payment totalizer
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 */
	public function setPaymentBasketObject( $paymentBasket ) {
		$this->_paymentBasket			=	$paymentBasket;
	}
	/**
	 * Updates the ordering field of the payment item (no check for unique ordering, default is 0)
	 *
	 * @param  int  $ordering
	 */
	public function setOrdering( $ordering ) {
		$this->ordering					=	(int) $ordering;
	}
	/**
	 * Creates entries for totalizer
	 *
	 * @param  cbpaidPaymentBasket       $paymentBasket
	 * @param  cbpaidPaymentItem[]       $paymentItems
	 * @param  cbpaidPaymentTotalizer[]  $taxableTotalizers
	 * @param  string                    $paymentTotalizerType
	 * @param  callable                  $addTotalizerToBasketFunc
	 */
	public static function createTotalizerEntries( $paymentBasket, $paymentItems, $taxableTotalizers, $paymentTotalizerType, $addTotalizerToBasketFunc ) {
		// Override!
	}
	/**
	 * Computes totalizer into $paymentBasket and into $paymentItems
	 *
	 * @param  cbpaidPaymentBasket       $paymentBasket
	 * @param  cbpaidPaymentItem[]       $paymentItems
	 * @param  cbpaidPaymentTotalizer[]  $taxableTotalizers
	 * @return void
	 */
	public function computeTotalizer( &$paymentBasket, &$paymentItems, &$taxableTotalizers ) {
		// Override!
	}
	/**
	 * Notifies any IPN/PDT/status change
	 *
	 * @param  boolean                    $thisIsReferencePayment TRUE if this event stores the payment
	 * @param  string                     $unifiedStatus          Payment/Subscription status ('PaidSubscription', 'Denied', 'RegistrationCancelled', NOT allowed here: 'Completed', 'Processed', 'Pending', 'In-Progress'
	 * @param  string                     $previousUnifiedStatus  Payment/Subscription status ('PaidSubscription', 'Denied', 'RegistrationCancelled', NOT allowed here: 'Completed', 'Processed', 'Pending', 'In-Progress'
	 * @param  cbpaidPaymentBasket        $paymentBasket          Basket
	 * @param  cbpaidPaymentNotification  $notification           notification object of the payment
	 * @param  int                        $now                    Current time
	 * @param  UserTable                  $user                   Corresponding user
	 * @param  string                     $eventType              type of event (paypal type): 'web_accept', 'subscr_payment', 'subscr_signup', 'subscr_modify', 'subscr_eot', 'subscr_cancel', 'subscr_failed'
	 * @param  string                     $paymentStatus          new status (Completed, RegistrationCancelled)
	 * @param  int                        $occurrences            renewal occurrences
	 * @param  int                        $autorecurring_type     0: not auto-recurring, 1: auto-recurring without payment processor notifications, 2: auto-renewing with processor notifications updating $expiry_date
	 * @param  int                        $autorenew_type         0: not auto-renewing (manual renewals), 1: asked for by user, 2: mandatory by configuration
	 * @param  boolean                    $txnIdMultiplePaymentDates   FALSE: unique txn_id for each payment, TRUE: same txn_id can have multiple payment dates
	 * @return void
	 */
	public function notifyPaymentStatus( $thisIsReferencePayment, $unifiedStatus, $previousUnifiedStatus, &$paymentBasket, &$notification, $now, &$user, $eventType, $paymentStatus, $occurrences, $autorecurring_type, $autorenew_type, $txnIdMultiplePaymentDates ) {
		// override if really needed !
	}
	/**
	 * RENDERING METHODS:
	 */
	/**
	 * Renders the rate of $this payment item
	 *
	 * @param  string       $variable
	 * @param  boolean      $html       TRUE: HTML rendering, FALSE: TEXT rendering
	 * @param  boolean      $rounded
	 * @return string|null
	 */
	public function renderItemRate( $variable, $html, $rounded = false ) {
		$first_var			=	'first_' . $variable;
		$cbpaidMoney		=&	cbpaidMoney::getInstance();
		if ( ! $this->_paymentBasket->isAnyAutoRecurring() ) {
			return $cbpaidMoney->renderPrice( $this->$variable, $this->currency, $html, $rounded, false );

		} else {
			if ( $this->$first_var || $this->$variable ) {
				$first		=	$cbpaidMoney->renderPrice( $this->$first_var, $this->currency, $html, $rounded, false );
				$then		=	$cbpaidMoney->renderPrice( $this->$variable, $this->currency, $html, $rounded, false );
				if ( $this->$first_var && ( $this->$variable === null ) ) {
					// $ret	=	$first;		// replacing this line by next one fixes bug #3624 at display time
					$ret	=	sprintf( $html ? CBPTXT::Th("%s, then %s") : CBPTXT::T("%s, then %s"), $first, $then );
				} elseif ( $this->$variable && ( $this->$first_var === null ) ) {
					$ret	=	$then;
				} elseif ( ( $this->$first_var === $this->$variable ) && ( $this->_paymentBasket->period1 == null ) ) {
					$ret	=	$first;
				} else {
					$ret	=	sprintf( $html ? CBPTXT::Th("%s, then %s") : CBPTXT::T("%s, then %s"), $first, $then );
				}
			} else {
				if ( ( $variable == 'rate' ) && ( $this->rate !== null ) ) {
					$ret	=	$cbpaidMoney->renderPrice( $this->$variable, $this->currency, $html, $rounded, false );
				} else {
					$ret	=	null;
				}
			}
		}
		return $ret;
	}
	/**
	 * Renders a $variable for an $output
	 *
	 * @param  string       $variable  Variable to render
	 * @param  string       $output    'html': HTML rendering, 'text': TEXT rendering
	 * @param  boolean      $rounded   Round column values ?
	 * @return string|null
	 */
	public function renderColumn( $variable, $output = 'html', $rounded = false  ) {
		$html					=	( $output == 'html' );
		switch ( $variable ) {
			case 'rate':
			case 'original_rate':
			case 'tax_amount':
				$ret			=	$this->renderItemRate( $variable, $html, $rounded );
				break;
			case 'first_rate':
			case 'first_original_rate':
			case 'first_tax_amount':
				if ( property_exists( $this, $variable ) ) {
					$ret		=	cbpaidMoney::getInstance()->renderPrice( $this->$variable, $this->currency, $html, $rounded );
				} else {
					$ret		=	null;
				}
				break;
			case 'validity_period':
				if ( $this->start_date && $this->stop_date && ( $this->start_date  != '0000-00-00 00:00:00' ) && ( $this->stop_date != '0000-00-00 00:00:00' )) {
					$startDate	=	cbFormatDate( $this->start_date, 0, false );
					$stopDate	=	cbFormatDate( $this->stop_date, 0, false );
					$ret		=	htmlspecialchars( $startDate );
					if ( $startDate != $stopDate ) {
						$ret	.=	( $html ? '&nbsp;-&nbsp;' : ' - ' ) . htmlspecialchars( $stopDate );
					}
				} else {
					$ret		=	null;
				}
				break;
			case 'tax_rule_id':
				if ( $this->tax_rule_id && is_callable( array( 'cbpaidTaxRule', 'getInstance' ) ) ) {
					$ret		=	cbpaidTaxRule::getInstance( (int) $this->tax_rule_id )->getShortCode();
				} else {
					$ret		=	null;
				}
				break;
			case 'ordering':
				if ( $this->payment_item_id ) {
					$paymItem	=	$this->_paymentBasket->getPaymentItem( $this->payment_item_id );
					if ( $paymItem ) {
						$ret	=	htmlspecialchars( $paymItem->ordering );
					} else {
						$ret	=	null;
					}
				} else {
					$ret		=	null;
				}
				break;
			case 'discount_amount':
			case 'first_discount_amount':
				$ret			=	null;
				break;
			case 'quantity':
			case 'artnum':
			case 'description':
			case 'discount_text':
			default:
				$ret			=	htmlspecialchars( $this->get( $variable ) );
				break;
		}
		return $ret;
	}
	/**
	 * loads the cbSubscription of this payment item: cbSubscription + ->reason from payment_items (N=New, R=Renewal)
	 * caches in object
	 *
	 * @return cbpaidUsersubscriptionRecord   if subscription is loaded or already loaded, or NULL otherwise
	 */
	public function & loadTotalizerSettings() {
		if ( ( ! $this->_totalizerType ) && $this->totalizer_type && $this->totalizer_id ) {
			$class								=	'cbpaid' . $this->totalizer_type . 'Totalizertype';
			$this->_totalizerType				=	new $class( $this->_db );
			$this->_totalizerType->load( (int) $this->totalizer_id );
		}
		return $this->_totalizerType;
	}
	/**
	 * Get an attribute of this stored object
	 *
	 * @param  string    $paramName
	 * @param  mixed     $default
	 * @param  string    $paramColumn  null means it is a database column of the plan and not a param entry
	 * @return mixed
	 */
	public function getTotalizerParam( $paramName, $default, $paramColumn = 'params' ) {
		$totalizerSettings					=&	$this->loadTotalizerSettings();
		if ( $totalizerSettings ) {
			if ( $paramColumn ) {
				return $totalizerSettings->getParam( $paramName, $default, $paramColumn );
			} else {
				$value						=	$totalizerSettings->get( $paramName );
				if ( $value !== null ) {
					return $value;
				}
			}
		}
		return $default;
	}
} // end class cbpaidPaymentTotalizer
/**
 * Subtotals totalizer
 */
class cbpaidPaymentTotalizer_subtotal extends cbpaidPaymentTotalizer {
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
	*/
	/**
	 * Creates entries for totalizer
	 *
	 * @param  cbpaidPaymentBasket       $paymentBasket
	 * @param  cbpaidPaymentItem[]       $paymentItems
	 * @param  cbpaidPaymentTotalizer[]  $taxableTotalizers
	 * @param  string                    $paymentTotalizerType
	 * @param  callback                  $addTotalizerToBasketFunc
	 * @return void
	 */
	public static function createTotalizerEntries( $paymentBasket, $paymentItems, $taxableTotalizers, $paymentTotalizerType, $addTotalizerToBasketFunc ) {
		$params								=&	cbpaidApp::settingsParams();

		$myClassName						=	'cbpaidPaymentTotalizer_' . $paymentTotalizerType;
		$salesTaxTotalizer					=	new $myClassName();
		// $salesTaxTotalizer				=	NEW cbpaidPaymentTotalizer_subtotal1();
		$salesTaxTotalizer->totalizer_id	=	(int) substr( $paymentTotalizerType, -1 );
		$salesTaxTotalizer->totalizer_type	=	( $salesTaxTotalizer->totalizer_id ? substr( $paymentTotalizerType, 0, -1 ) : $paymentTotalizerType );
		$salesTaxTotalizer->artnum			=	$params->get( 'totalizer_artnum_' . $paymentTotalizerType );
		$salesTaxTotalizer->description		=	$params->get( 'totalizer_description_' . $paymentTotalizerType );
		$salesTaxTotalizer->currency		=	$paymentBasket->mc_currency;
		$salesTaxTotalizer->start_date		=	null;
		$salesTaxTotalizer->stop_date		=	null;
		$salesTaxTotalizer->first_rate		=	null;
		$salesTaxTotalizer->rate			=	null;
		call_user_func_array( $addTotalizerToBasketFunc, array( $salesTaxTotalizer ) );
	}
	/**
	 * Computes totalizer into $paymentBasket and into $paymentItems
	 *
	 * @param  cbpaidPaymentBasket       $paymentBasket
	 * @param  cbpaidPaymentItem[]       $paymentItems
	 * @param  cbpaidPaymentTotalizer[]  $taxableTotalizers
	 * @return void
	 */
	public function computeTotalizer( &$paymentBasket, &$paymentItems, &$taxableTotalizers ) {
		if ( $paymentBasket->period1 ) {
			$this->first_rate				=	$paymentBasket->mc_amount1;
			$this->rate						=	$paymentBasket->mc_amount3;
		} else {
			$this->rate						=	$paymentBasket->mc_gross;
		}
		// Now that all items and totalizers have been computed, adjust basket:
	}
	/**
	 * Renders a $variable for an $output
	 *
	 * @param  mixed   $variable
	 * @param  string  $output
	 * @param  boolean $rounded
	 * @return string|null
	 */
	public function renderColumn( $variable, $output = 'html', $rounded = false  ) {
		if ( ( ! in_array( $variable, array( 'rate', 'first_rate', 'validity_period', 'description' ) ) ) && ( is_callable( 'property_exists' ) ? property_exists( $this, $variable ) : property_exists( $this, $variable ) ) && ( $this->$variable == 0 ) ) {
			return null;
		}
		return parent::renderColumn( $variable, $output, $rounded );
	}
}
/** Sub-total 1 totalizer **/
class cbpaidPaymentTotalizer_subtotal1  extends cbpaidPaymentTotalizer_subtotal { }
/** Sub-total 2 totalizer **/
class cbpaidPaymentTotalizer_subtotal2  extends cbpaidPaymentTotalizer_subtotal { }
/** Sub-total 3 totalizer **/
class cbpaidPaymentTotalizer_subtotal3  extends cbpaidPaymentTotalizer_subtotal { }
/** Sub-total 4 totalizer **/
class cbpaidPaymentTotalizer_subtotal4  extends cbpaidPaymentTotalizer_subtotal { }

/** Generic totalizer type class **/
abstract class cbpaidsubtotalTotalizertype extends cbpaidTotalizerType {
	/**
	 * Avoid loading of a totalizer that is not in database
	 *
	 * @param  int|null  $oid
	 * @return boolean
	 */
	public function load( $oid = null ) {
		return false;
	}
}
//TODO not even sure if the following is needed! :
/** Sub-total 1 totalizer type **/
class cbpaidsubtotal1Totalizertype extends cbpaidsubtotalTotalizertype { }
/** Sub-total 2 totalizer type **/
class cbpaidsubtotal2Totalizertype extends cbpaidsubtotalTotalizertype { }
/** Sub-total 3 totalizer type **/
class cbpaidsubtotal3Totalizertype extends cbpaidsubtotalTotalizertype { }
/** Sub-total 4 totalizer type **/
class cbpaidsubtotal4Totalizertype extends cbpaidsubtotalTotalizertype { }
/** Sub-total grand totalizer type **/
class cbpaidgrandtotalTotalizertype extends cbpaidTotalizerType { }
/**
 * Grand-Total totalizer class
 */
class cbpaidPaymentTotalizer_grandtotal extends cbpaidPaymentTotalizer_subtotal1
{
	/**
	 * Return items propreties
	 *
	 * @param  cbpaidPaymentItem[]                         $paymentItems
	 * @return array[boolean, float, string, int|boolean]                 Recurring, Total Discount, period of validity, occurrences or false if none
	 */
	private function _itemsProps( &$paymentItems ) {
		$recurring					=	false;
		$discount					=	0.0;
		$period						=	null;
		$occurrences				=	null;
		foreach ( $paymentItems as $item ) {

			if ( $item->validity && ( $item->validity != '0000-00-00 00:00:00' ) && $item->autorecurring ) {
				$period				=	$item->validity;

				$occurrences			=	$item->recurring_max_times;
				if ( $item->autorecurring ) {
					$recurring			=	true;
				}
			}
			if ( $item->discount_amount || $item->prorate_discount ) {
				$discount			+=	$item->discount_amount + $item->prorate_discount;
			}
		}
		return array( $recurring, $discount, $period, $occurrences );
	}
	/**
	 * Computes totalizer into $paymentBasket and into $paymentItems
	 *
	 * @param  cbpaidPaymentBasket       $paymentBasket
	 * @param  cbpaidPaymentItem[]       $paymentItems
	 * @param  cbpaidPaymentTotalizer[]  $taxableTotalizers
	 * @return void
	 */
	public function computeTotalizer( &$paymentBasket, &$paymentItems, &$taxableTotalizers ) {
		parent::computeTotalizer( $paymentBasket, $paymentItems, $taxableTotalizers );

		$params								=&	cbpaidApp::settingsParams();
		if ( $params->get( 'totalizer_description_' . 'grandtotal' ) == '[AUTOMATIC]' ) {
			list( $recurring, $discount, /* $period */, /*$occurrences */ )			=	$this->_itemsProps( $paymentItems );

			if ( $recurring ) {
				if ( $discount || ( $paymentBasket->period1 && ( $paymentBasket->mc_amount1 != $paymentBasket->mc_amount3 ) ) ) {
					$basketPriceText	=	CBPTXT::Th("Total for first payment");
				} else {
					$basketPriceText	=	CBPTXT::Th("Total per payment");
				}
			} else {
				$basketPriceText		=	CBPTXT::Th("Total");
			}

			$this->description			=	$basketPriceText;
		}
	}
	/**
	 * Renders the rate of $this payment item
	 *
	 * @param  string       $variable
	 * @param  boolean      $output   'html', ...
	 * @param  boolean      $rounded
	 * @return string|null
	 */
	private function renderTotalRate( $variable, $output, $rounded = false ) {
		$renderedBasketPrice			=	null;
		$params							=&	cbpaidApp::settingsParams();
		if ( $params->get( 'totalizer_description_' . 'grandtotal' ) == '[AUTOMATIC]' ) {
			list( $recurring, /* $discount */, $period, $occurrences )			=	$this->_itemsProps( $this->_paymentBasket->loadPaymentItems() );

			$renderedBasketPrice		=	$this->_paymentBasket->renderPrice( null, null, null, true );

			if ( $recurring && ( $this->_paymentBasket->period1 && ( $this->_paymentBasket->mc_amount1 != $this->_paymentBasket->mc_amount3 ) ) ) {
				$then					=	$this->_paymentBasket->renderPrice( $this->_paymentBasket->mc_amount3  /* $this->_paymentBasket->mc_gross + $discount */ , $period, $occurrences, true );
				$renderedBasketPrice	=	sprintf( CBPTXT::Th("%s, then %s"), $renderedBasketPrice, $then );
			}
		} else {
			parent::renderColumn( $variable, $output, $rounded );
		}
		return $renderedBasketPrice;
	}
	/**
	 * Renders a $variable for an $output
	 *
	 * @param  string       $variable
	 * @param  string       $output   'html', ...
	 * @param  boolean      $rounded
	 * @return string|null
	 */
	public function renderColumn( $variable, $output = 'html', $rounded = false  ) {
		if ( $variable == 'rate' ) {
			// Special, more detailed, treatment for grand-total rate:
			$ret					=	$this->renderTotalRate( $variable, $output, $rounded );
			if ( $ret ) {
				return $ret;
			}
		}
		return parent::renderColumn( $variable, $output, $rounded );
	}
} // end class cbpaidPaymentTotalizer_grandtotal
