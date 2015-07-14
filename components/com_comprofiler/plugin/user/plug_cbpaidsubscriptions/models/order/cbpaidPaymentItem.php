<?php
/**
 * @version $Id: cbpaidPaymentItem.php 1608 2012-12-29 04:12:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Payment item database table class
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 */
class cbpaidPaymentItem extends cbpaidItem {
	/** @var int Primary key */
	//inherited:	public $id					= null;
	public $payment_basket_id;
	//inherited:	public $item_type;
	public $quantity;
	public $unit;
	public $artnum;
	public $description;
	public $alias;
	public $discount_text;
	public $first_discount_amount;
	public $discount_amount;
	public $prorate_discount;
	//inherited:	public $currency;
	//inherited:	public $rate;
	public $subscription_id;
	public $plan_id;
	/**
	 * N = New, R = Renew, U = Upgrade
	 * @var string
	 */
	public $reason;
	//inherited:	public $validity;			// $period;
	//inherited:	public $bonustime;
	/**
	 * datetime of subscription start of FIRST term
	 * @var string */
	public $start_date;
	public $stop_date;
	public $second_stop_date;
	//inherited:	public $autorecurring;		// $periodsnumber
	//inherited:	public $recurring_max_times;
	//inherited:	public $first_rate;
	//inherited:	public $first_validity;
	public $tax_rule_id;
	public $tax_amount;
	public $first_tax_amount;
	public $owner;
	public $parent			=	0;			//TBD: parent item
	/** Global ordering within the invoice (no hierarchy depending on parent, just straight.
	 * @var int */
	public $ordering		=	0;
	/**
	 * Subscription
	 * @var cbpaidSomething
	 */
	private $_subscription	=	null;
	public $_parentSub		=	null;
	public $_renewalDiscount;

	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db
	 */
	public function __construct( &$db ) {
		parent::__construct( '#__cbsubs_payment_items', 'id', $db );
	}
	/**
	 * Get the most recent payment basket, even if timed-out !
	 *
	 * @param  int      $userId
	 * @param  int      $planId
	 * @param  int      $subscriptionId
	 * @param  string   $paymentStatus    'NotInitiated': search only not initiated baskets which is not to old, NULL: search any kind, string: search for particular status.
	 * @param  int      $notBasketId      NULL or id of basket to NOT load
	 * @return boolean                    true = success, false = not found
	 */
	public function loadLatestPaymentItemOfUserPlanSubscription( $userId, $planId = null, $subscriptionId = null, $paymentStatus = 'NotInitiated', $notBasketId = null ) {
		if ( ( $planId != null ) && ( $subscriptionId != null ) ) {
			$query = "SELECT i.*"
				. "\n FROM #__cbsubs_payment_items i"
				. "\n JOIN #__cbsubs_payment_baskets b ON b.id = i.payment_basket_id"
				. "\n WHERE i.subscription_id = " . (int) $subscriptionId
				. "\n AND i.plan_id = "			  . (int) $planId
				. "\n AND b.user_id = "			  . (int) $userId
				. ( $paymentStatus ?
					"\n AND b.payment_status = " . $this->_db->Quote( $paymentStatus )
					: '')
				. ( $notBasketId ?
					"\n AND b.id <> " . (int) $notBasketId
					: '')
				. "\n ORDER BY b.time_initiated DESC"
			;
			$this->_db->setQuery( $query, 0, 1 );
			return $this->_db->loadObject( $this );
		}
		return false;
	}
	/**
	 * Stops the auto-recurring payments for $this payment item (for now it stops the whole basket)
	 *
	 * @return string|boolean  true if unsubscription done successfully, string if error
	 */
	public function stopAutorecurringPayments( ) {
		$basket		=	$this->loadBasket();
		if ( $basket ) {
			$items	=	array( $this );
			return $basket->stopAutoRecurringPayments( $items );
		}
		return false;
	}
	/**
	 * Create a paymentItem object and corresponding object in memory (not yet stored to database)
	 *
	 * @param  string               $item_type          'subscription' for now
	 * @param  float                $quantity           quantity of the unit (typically 1.0)
	 * @param  string               $artNumber          article number text for the invoice
	 * @param  string               $itemDescription    description of the item line
	 * @param  string               $itemAlias          very short description of the item line
	 * @param  string               $itemCurrency       currency of the item line
	 * @param  float                $itemPrice          price of the item line (in that currency)
	 * @param  int                  $owner              owner (receiving account should-be) of this payment item
	 * @param  string               $reason             payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=upgrade
	 * @param  string               $unit               unit of the quantity
	 */
	public function createItem( $item_type, $quantity, $artNumber, $itemDescription, $itemAlias, $itemCurrency, $itemPrice,
								$owner, $reason, $unit = null ) {
		$this->reset();

		$this->payment_basket_id		= null;
		$this->item_type				= $item_type;
		$this->quantity					= $quantity;
		$this->unit						= $unit;
		$this->artnum					= $artNumber;
		$this->description				= $itemDescription;
		$this->alias					= $itemAlias;
		$this->currency					= $itemCurrency;
		$this->rate						= $itemPrice;
		$this->subscription_id			= null;
		$this->plan_id					= null;
		$this->reason					= $reason;
		$this->validity					= null;
		$this->start_date				= null;
		$this->stop_date				= null;
		$this->second_stop_date			= null;
		$this->autorecurring			= null;
		$this->recurring_max_times		= null;
		$this->first_rate				= null;
		$this->first_validity			= null;
		$this->bonustime				= null;
		$this->owner					= $owner;
	}
	/**
	 * Add subscriptions-specific items to the line
	 *
	 * @param  int     $subscriptionId
	 * @param  int     $planId
	 * @param  string  $validity
	 * @param  string  $start_date
	 * @param  string  $stop_date
	 * @param  string  $second_stop_date
	 * @param  int     $autorecurring          autorecurring item: 0: no, 1: yes, 2: leave choice to user
	 * @param  int     $recurring_max_times    maximum number of times the item is autorecurring
	 * @param  float   $first_rate
	 * @param  string  $first_validity
	 * @param  string  $bonustime
	 * @param  float   $prorate_discount
	 */
	public function setSubscriptionVars( $subscriptionId, $planId, $validity, $start_date, $stop_date, $second_stop_date,
										 $autorecurring, $recurring_max_times, $first_rate,
										 $first_validity, $bonustime, $prorate_discount ) {
		$this->subscription_id			= $subscriptionId;
		$this->plan_id					= $planId;
		$this->validity					= $validity;
		$this->start_date				= $start_date;
		$this->stop_date				= $stop_date;
		$this->second_stop_date			= $second_stop_date;
		$this->autorecurring			= $autorecurring;
		$this->recurring_max_times		= $recurring_max_times;
		$this->first_rate				= $first_rate;
		$this->first_validity			= $first_validity;
		$this->bonustime				= $bonustime;
		$this->prorate_discount			= $prorate_discount;
	}
	/**
	 * Trigger integrations
	 *
	 * @param  string                    $event
	 * @param  cbpaidSomething           $something
	 * @param  cbpaidPaymentBasket|null  $paymentBasket  Payment basket
	 */
	public function callIntegrations( $event = 'addSomethingToBasket', $something, $paymentBasket ) {
		global $_PLUGINS;

		$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
		$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');
		$_PLUGINS->trigger( 'onCPayPaymentItemEvent', array( $event, &$something, &$paymentBasket, &$this ) );
	}
	/**
	 * Updates the payment basket id of the payment item
	 *
	 * @param  int  $paymentBasketId
	 */
	public function setPaymentBasket( $paymentBasketId ) {
		$this->payment_basket_id		=	(int) $paymentBasketId;
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
	 * Updates the parent field of the payment item (no separate ordering, ordering is global)
	 *
	 * @param  int  $parentItemId
	 */
	public function setParentItem( $parentItemId ) {
		$this->parent					=	(int) $parentItemId;
	}
	/**
	 * Fix variable name 'first_validity' to 'validity' if there is no different first period
	 * Overrides cbpaidTimed to take in account rule of cbpaidPaymentItem
	 *
	 * @param  string  $varName   'first_validity' or 'validity'   !!! CHANGES (FIXES) THAT VAR NAME
	 */
	public function fixVarName( &$varName ) {
		if ( ( $varName === 'first_validity' ) && ( ( $this->first_validity === null ) || ( $this->first_validity === '0000-00-00 00:00:00' ) ) ) {
			$varName	=	'validity';
		}
	}
	/**
	 * says if validity is unlimitted
	 * OVERRIDES the base method from cbpaidTimed class
	 *
	 * @return boolean	             true if lifetime, false if limitted time
	 */
	public function isLifetimeValidity() {
		return ( ( in_array( $this->get( 'first_validity' ), array( '', '0000-00-00 00:00:00' ) ) ) && ( $this->get( 'validity' ) == '0000-00-00 00:00:00' ) );
	}
	/**
	 * returns the price to pay for this item (in case of recurring payments, the first payment)
	 *
	 * @param  boolean  $net  default: TRUE: return net discounted amount, FALSE: return non-discounted gross rate value
	 * @return float
	 */
	public function getPrice( $net = true ) {
		if ( ( $this->first_rate === null ) || ( ( $this->first_validity === null ) || ( $this->first_validity === '' ) || ( $this->first_validity === '0000-00-00 00:00:00' ) ) ) {
			return $this->rate + ( $net ? $this->discount_amount : 0 );
		} else {
			return $this->first_rate + ( $net ? $this->first_discount_amount : 0 );
		}
	}
	/**
	 * loads the cbSubscription of this payment item: cbSubscription + ->reason from payment_items (N=New, R=Renewal)
	 * caches in object
	 *
	 * @return cbpaidSomething   if subscription is loaded or already loaded, or NULL otherwise
	 */
	public function & loadSubscription() {
		if ( ( ! $this->_subscription ) && $this->plan_id && $this->subscription_id ) {
			$somethingMgr						=&	cbpaidSomethingMgr::getInstance();
			$sub								=&	$somethingMgr->loadSomething( $this->plan_id, $this->subscription_id );
			if ( $sub ) {
				$this->_subscription			=&	$sub;
				$this->_subscription->_reason	=	$this->reason;	// needed for upgradePayment
				//	$this->_subscription->loadPlan();				// not yet needed, so don't do it yet, will be done as needed
			}
		}
		return $this->_subscription;
	}
	/*
	 * Sets a subscription associated with this item
	 *
	 * @param  cbpaidSomething  $subscription
	 * @return void
	 * FIXME: to use ?
	public function setSubscription( &$subscription ) {
		$this->_subscription					=&	$subscription;
	}
	*/
	/**
	 * loads the basket of this payment item: cbSubscription + ->reason from payment_items (N=New, R=Renewal)
	 * caches in object
	 *
	 * @return cbpaidPaymentBasket   if subscription is loaded or already loaded, or NULL otherwise
	 */
	public function & loadBasket() {
		if ( $this->payment_basket_id ) {
			$basket								=	new cbpaidPaymentBasket( $this->_db );
			if ( $basket->load( (int) $this->payment_basket_id ) ) {
				return $basket;
			}
		}
		$false									=	false;
		return $false;
	}
	/**
	 * Get an attribute of this stored object
	 *
	 * @param  string    $paramName
	 * @param  mixed     $default
	 * @param  string    $paramColumn  null means it is a database column of the plan and not a param entry
	 * @return mixed
	 */
	public function getPlanParam( $paramName, $default, $paramColumn = 'params' ) {
		$subscription							=&	$this->loadSubscription();
		if ( $subscription ) {
			$plan								=	$subscription->getPlan();
			if ( $plan ) {
				if ( $paramColumn ) {
					return $plan->getParam( $paramName, $default, $paramColumn );
				} else {
					$value						=	$plan->get( $paramName );
					if ( $value !== null ) {
						return $value;
					}
				}
			}
		}
		return $default;
	}
	/**
	 * Gives Calendar Year start
	 * OVERRIDES the base method from cbpaidTimed class
	 *
	 * @param  string  $varName      'first_validity' or 'validity'
	 * @return array                 array( (int) month, (int) day )
	 */
	public function calendarYearStart( $varName ) {
		$subscription		=	$this->loadSubscription();
		if ( $subscription ) {
			$plan			=	$subscription->getPlan();
			if ( $plan ) {
				return $plan->calendarYearStart( $varName );
			}
		}
		//TODO: log error
		return array( 1, 1 );
	}
	/**
	 * RENDERING METHODS:
	 */
	/**
	 * Renders the rate of $this payment item
	 *
	 * @param  boolean      $html     TRUE: HTML rendering, FALSE: TEXT rendering
	 * @return string
	 */
	public function renderItemRate( $html ) {										//TBD: //FIXME Ideally, an Item should be independant of subscriptions and plans for rendering its price.
		if ( $this->start_date ) {
			$startTime		=	$this->strToTime( $this->start_date );
		} else {
			$startTime		=	null;
		}
		$expiryTime			=	null;

		if ( ! $this->autorecurring ) {
			$cbpaidMoney	=&	cbpaidMoney::getInstance();
			return $cbpaidMoney->renderPrice( $this->rate, $this->currency, $html, false );

		} else {
			$occurrence		=	0;			//FIXME: don't have the real value in paymentitem: put it there !
			//	$plan			=	$this->_setPlanCorrespondingToItem();

			// Save rates and temporarily change them just for display before restoring them:
			$saveFirstRate	=	$this->first_rate;
			$saveRate		=	$this->rate;
			if ( $this->prorate_discount != 0 ) {
				if ( ( $this->first_rate !== null ) && ( $this->first_rate !== '' ) && ( ! ( $this->first_rate < 0 ) ) ) {
					$this->_renewalDiscount			=	$this->first_rate;
					$this->first_rate				=	$this->first_rate + $this->prorate_discount;
				} else {
					$this->_renewalDiscount			=	$this->rate;
					$this->rate						=	$this->rate + $this->prorate_discount;
				}
			} else {
				$this->_renewalDiscount				=	null;
			}
			$html				=	$this->displayPeriodPrice( $this->reason, $occurrence, $expiryTime, $startTime, $html, false, false );
			// Restore saved rates:
			$this->first_rate	=	$saveFirstRate;
			$this->rate			=	$saveRate;
		}
		return $html;
	}
	/**
	 * Gets the prefix before 'rate' and 'validity' for this plan depending on reason and occurrences
	 *
	 * @param  string        $reason      Payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  int           $occurrence   <= 1 : first occurrence, > 1: next occurrences
	 * @param  string        $variable    Name of main recurring variable ( 'rate' or 'validity' )
	 * @return string                     'rate' or 'first_rate' or 'validity' or 'first_validity'
	 */
	public function getPlanVarName( $reason, $occurrence, $variable ) {
		$varRate		=	'';
		if ( ( $occurrence <= 1 ) && ( ( $this->first_rate || $this->first_validity ) ) ) {
			$varRate	=	'first_';
		}
		return $varRate . $variable;
	}

	/**
	 * Renders the rate of $this payment item
	 *
	 * @param  string       $variable
	 * @param  boolean      $html     TRUE: HTML rendering, FALSE: TEXT rendering
	 * @param  boolean      $rounded
	 * @return string|null
	 */
	public function renderJustItemRates( $variable, $html, $rounded = false ) {
		$first_var			=	'first_' . $variable;
		$cbpaidMoney		=&	cbpaidMoney::getInstance();
		if ( ! $this->autorecurring ) {
			$itemHasFirstRate	=	$this->first_validity || $this->first_rate;
			$value			=	( $itemHasFirstRate ? $this->$first_var : $this->$variable );
			return $cbpaidMoney->renderPrice( $value, $this->currency, $html, $rounded, false );

		} else {
			if ( $this->$first_var || $this->$variable ) {
				$first		=	$cbpaidMoney->renderPrice( $this->$first_var, $this->currency, $html, $rounded, false );
				$then		=	$cbpaidMoney->renderPrice( $this->$variable, $this->currency, $html, $rounded, false );
				if ( $this->$first_var && ( $this->$variable === null ) ) {
					$ret	=	$first;
				} elseif ( ( $this->$first_var === null ) && $this->$variable ) {
					$ret	=	$then;
				} else {
					$ret	=	sprintf( $html ? CBPTXT::Th("%s, then %s") : CBPTXT::T("%s, then %s"), $first, $then );
				}
			} else {
				$ret		=	null;
			}
		}
		return $ret;
	}
	/**
	 * Renders a $variable for an $output
	 *
	 * @param  string   $variable
	 * @param  string   $output
	 * @param  boolean  $rounded
	 * @return string|null
	 */
	public function renderColumn( $variable, $output = 'html', $rounded = false  ) {
		$html					=	( $output == 'html' );
		switch ( $variable ) {
			case 'rate':
				$ret			=	$this->renderItemRate( $html );
				break;
			case 'discount_amount':
			case 'tax_amount':
				$ret			=	$this->renderJustItemRates( $variable, $html, $rounded );
				break;
			case 'first_rate':
			case 'first_discount_amount':
			case 'first_tax_amount':
				$ret			=	cbpaidMoney::getInstance()->renderPrice( $this->$variable, $this->currency, $html, $rounded );
				break;
			case 'quantity':
				// removes insignifiant zeros after ., as well as the . itself if no decimals:
				$matches		=	null;
				$matched		=	preg_match( "/^(.+?)[.]?[0]*$/", $this->get( $variable ), $matches );
				$ret			=	( $matched ? $matches[1] : null );
				break;
			case 'validity_period':
				if ( $this->start_date && $this->stop_date && ( $this->start_date  != '0000-00-00 00:00:00' ) && ( $this->stop_date != '0000-00-00 00:00:00' )) {
					$showTime				=	false;
					$startDate				=	cbFormatDate( $this->start_date, 1, $showTime );
					$stopDate				=	cbFormatDate( $this->stop_date, 1, $showTime );

					$ret					=	htmlspecialchars( $startDate );
					if ( $stopDate && ( $startDate != $stopDate ) ) {
						$ret				.=	( $html ? '&nbsp;-&nbsp;' : ' - ' ) . htmlspecialchars( $stopDate );
					}
					if ( $this->second_stop_date && ( $this->second_stop_date != '0000-00-00 00:00:00' ) ) {
						$secondStartDate	=	cbFormatDate( date( 'Y-m-d H:i:s', cbpaidTimes::getInstance()->strToTime( $this->stop_date ) + 1 ), 1, $showTime );
						$secondStopDate		=	cbFormatDate( $this->second_stop_date, 1, $showTime );
						$retsecond			=	htmlspecialchars( $secondStartDate ) . ( $html ? '&nbsp;-&nbsp;' : ' - ' ) . htmlspecialchars( $secondStopDate );
						$ret				=	sprintf( $html ? CBPTXT::Th("%s, then %s") : CBPTXT::T("%s, then %s"), $ret, $retsecond );
					}

				} else {
					$ret					=	null;
				}
				break;
			case 'tax_rule_id':
				if ( $this->tax_rule_id && is_callable( array( 'cbpaidTaxRule', 'getInstance' ) ) ) {
					$ret		=	cbpaidTaxRule::getInstance( (int) $this->tax_rule_id )->getShortCode();
				} else {
					$ret		=	null;
				}
				break;
			case 'original_rate':
			case 'first_original_rate':
				$ret			=	null;
				break;
			case 'ordering':
			case 'artnum':
			case 'description':
			case 'discount_text':
			default:
				$value			=	$this->get( $variable );
				if ( $value !== null ) {
					$ret		=	 htmlspecialchars( $this->get( $variable ) );
				} else {
					$ret		=	null;
				}
				break;
		}
		return $ret;
	}
	/**
	 * BACKEND RENDERING METHODS:
	 */
	/**
	 * USED by XML interface ONLY !!! Renders amount
	 *
	 * @param  string           $price
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderAmount( $price, /** @noinspection PhpUnusedParameterInspection */ &$params ) {
		if ( $price ) {
			$cbpaidMoney			=&	cbpaidMoney::getInstance();
			$priceRoundings			=	100;		// $params->get('price_roundings', 100 );
			$priceRounded			=	$cbpaidMoney->renderNumber( round( $price * $priceRoundings ) / $priceRoundings, 'money', false );
		} else {
			$priceRounded			= '-';
		}
		return $priceRounded;
	}
	/**
	 * USED by XML interface ONLY !!! Renders main currency + amount
	 *
	 * @param  string           $price
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderCurrencyAmount( $price, &$params ) {
		return $params->get( 'currency_code' ) . '&nbsp;' . $this->renderAmount( $price, $params );
	}
}	// class cbpaidPaymentItem
