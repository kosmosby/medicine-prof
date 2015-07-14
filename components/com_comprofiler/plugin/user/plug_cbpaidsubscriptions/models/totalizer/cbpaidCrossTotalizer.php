<?php
/**
* @version $Id: cbpaidCrossTotalizer.php 1546 2012-12-02 23:16:25Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Plugin for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class definition for totalizers definitions that are compoundable (cross-totalizers), like tax rates or promotions definitions
 */
abstract class cbpaidTotalizertypeCompoundable extends cbpaidTotalizerType {
	public $priority;						// sql:int(20)

	/**
	 * Reset totalizer compounders
	 *
	 * @return void
	 */
	public function resetTotalizer( ) {
		// OVERRIDE: TODO: LATER this becomes abstract !!
	}
	
	/**
	 * Sets the basket for the calculation functions below: getAmountBeforePercents, getPercents and getAmountAfterPercents
	 * 
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @return void
	 */
	abstract public function setBasket( $paymentBasket );
	/**
	 * Sets the basket item for the calculation functions below: getAmountBeforePercents, getPercents and getAmountAfterPercents
	 * 
	 * @param  cbpaidPaymentItem  $item
	 * @return void
	 */
	public function setPaymentItem( $item ) {
		// OVERRIDE: TODO: LATER this becomes abstract !!
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
	abstract public function getAmountBeforePercents( $amount, $amountTaxExcl, $periodProrater, $isFirstPeriod, $currency_code );
	/**
	 * Computes the percentage on amount
	 * 
	 * @param  float    $amount
	 * @param  float    $amountTaxExcl
	 * @param  float    $periodProrater
	 * @param  boolean  $isFirstPeriod
	 */
	abstract public function getPercents( $amount, $amountTaxExcl, $periodProrater, $isFirstPeriod );
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
	abstract public function getAmountAfterPercents( $amount, $amountTaxExcl, $periodProrater, $isFirstPeriod, $currency_code );
	/**
	 * Implements stepping function finder depending on $amountTaxExcl
	 * 
	 * @param  float   $amountTaxExcl
	 * @param  string  $tax_stages
	 * @return string|null
	 */
	protected function _findTaxStep( $amountTaxExcl, $tax_stages ) {
		$matches	=	null;
		if ( preg_match_all( '/(?:(?:(\d+(?:\.\d*)?)(?:(?:-(\d+(?:\.\d*)?))|(\+)):(\d+(?:\.\d*)?%?));?)/', $tax_stages, $matches ) ) {
			// 0-20:0%;20-50:1.00;50-100:4.2%;100-500.50:5%;500.50+:25.00
			// 0-20:0%;500.50+:25.00 =>
			// array( 0 => array( 0 => ..., 1 => array( '0', '500.50' ), 2 => array( '20'0, '' ), 3 => array( '', '+' ), 4 => array( '0%', '25.00' ) )
			for ( $i = count( $matches[0] ) - 1 ; $i >= 0 ; $i-- ) {
				if ( ( $amountTaxExcl >= $matches[1][$i] ) && ( ( $matches[3][$i] == '+' ) || ( $amountTaxExcl < $matches[2][$i] ) ) ) {
					return $matches[4][$i];
				}
			}
		}
		return null;
	}
}

/**
 * Class definition for totalizers storage in baskets (like payment items) that are compoundable (cross-totalizers)
 */
abstract class cbpaidPaymentTotalizerCompoundable extends cbpaidPaymentTotalizer {
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
	protected $_itemIndexes;
	/**
	 * @var cbpaidTotalizertypeCompoundable
	 */
	protected $_totalizertypeSettings;
	/**
	 * Time-prorates a tax/discount for an item
	 *
	 * @param  cbpaidPaymentItem  $item
	 * @param  boolean            $isTotalizerFirstPeriod
	 * @return float              a value between 0.0 and 1.0
	 */
	abstract public function proRatePeriod( $item, $isTotalizerFirstPeriod );
	/**
	 * Applies $this totalizer to the $paymentBasket
	 * 
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  boolean              $anyAutoRecurringInBasket
	 * @return boolean              TRUE: Totalizer applied, FALSE: remove $this totalizer from $paymentBasket 
	 */
	abstract public function applyThisTotalizerToBasket( $paymentBasket, $anyAutoRecurringInBasket );

	/**
	 * Checks if it has payment items attached
	 *
	 * @return bool
	 */
	public function hasItems( ) {
		return ( $this->_itemIndexes && ( count( $this->_itemIndexes ) > 0 ) );
	}

	/**
	 * Computes totalizer into $paymentBasket and into $paymentItems
	 *
	 * @param  cbpaidPaymentBasket                   $paymentBasket
	 * @param  cbpaidPaymentItem[]                   $paymentItems
	 * @param  cbpaidPaymentTotalizerCompoundable[]  $taxableTotalizers
	 * @return void
	 */
	public function computeTotalizer( &$paymentBasket, &$paymentItems, &$taxableTotalizers ) {
		// All tax totalizers are a give taxRate, and are following each other, ordered by priority, ordering and itemss' validity intersections with the tax rate.
		// But as they need to be compounded within same priority and computed by item, we do compute tax totalizers all at once when first one is called.
		static $notComputed				=	true;
		if ( $notComputed ) {
			$notComputed				=	false;

			$thisTotalizerType			=	$this->getTotalizerType();
			$compounder					=	cbpaidCrossTotalizer::getSelfOfType( $thisTotalizerType );

			// prepares computing separate totals for each payment item 
			foreach ( $paymentItems as $k => $item ) {
				// $item	=	NEW cbpaidPaymentItem();
				foreach ( $taxableTotalizers as $totalizer ) {
					// $totalizer = NEW cbpaidPaymentTotalizer_salestax();
					if ( ( $totalizer->totalizer_type == $thisTotalizerType ) && $totalizer->_itemIndexes && in_array( $k, $totalizer->_itemIndexes ) ) {
						$compounder->addRate( $totalizer->_totalizertypeSettings, $totalizer, $k, $item );
					}
				}
			}

			// prepares computing separate totals for each payment totalizer which is taxed:
			/* This part is buggy, as it taxes completely each totalizer in each instance of each tax rule:
			foreach ( $taxableTotalizers as $k2 => $totalizerToTax ) {
				if ( $totalizerToTax->tax_rule_id ) {
					foreach ( $taxableTotalizers as $totalizer ) {
						// $totalizer = NEW cbpaidPaymentTotalizer_salestax();
						if ( ( $totalizer->totalizer_type == $thisTotalizerType ) && ( $totalizer->_totalizertypeSettings->tax_rule_id ==  $totalizerToTax->tax_rule_id ) ) {
							$taxCompounder->addRate( $totalizer->_totalizertypeSettings, $totalizer, $k+1+$k2, $totalizerToTax );
						}
					}
					
				}
			}
			*/

			// Now do the real tax computing:
			$compounder->computeAllTaxes( $paymentBasket, $taxableTotalizers, $this->getTotalizerType() );
		}
	}
}

/**
 * Class definition for the calculating totalizer memory-only object
 */
abstract class cbpaidCrossTotalizer {
	protected $ratesToCompound	=	array();
	/**
	 * Instanciator for $type corss-totalizer
	 * 
	 * @param  string  $type
	 * @return cbpaidCrossTotalizer
	 */
	public static function getSelfOfType( $type ) {
		$class		=	'cbpaidCrossTotalizer_' . $type;
		return new $class();
	}
	/**
	 * Gives $item->first_rate or $item->rate of $item depending of $first
	 *
	 * @param  cbpaidPaymentItem  $item
	 * @param  boolean            $inclusive
	 * @param  boolean            $first
	 * @param  boolean            $itemHasReallyFirstRate
	 * @return float
	 */
	abstract protected function _getItemAmount_first_incl( $item, $inclusive, $first, $itemHasReallyFirstRate = false );
	/**
	 * Returns name of totalizer total column in payment item
	 *
	 * @param  boolean  $first  If it's first amount
	 * @return string
	 */
	abstract protected function _getItemTotalizerColumnName( $first );

	/**
	 * Adds a rate to this Cross-Totalizer
	 *
	 * @param  cbpaidTotalizertypeCompoundable           $rate
	 * @param  cbpaidPaymentTotalizerCompoundable        $totalizer
	 * @param  int                                       $itemIndex   Index of the item to treat
	 * @param  cbpaidPaymentItem|cbpaidPaymentTotalizer  $item        cbpaidPaymentItem or taxable cbpaidPaymentTotalizer
	 * @return void
	 */
	public function addRate( $rate, $totalizer, $itemIndex, $item ) {
		$this->ratesToCompound[(int) $rate->priority][(int) $itemIndex]['item']			=	$item;
		$this->ratesToCompound[(int) $rate->priority][(int) $itemIndex]['ratestots'][]	=	array( $rate, $totalizer );
	}
	/**
	 * Compounds rates of this compounder added with addRate() method
	 *
	 * @param  cbpaidPaymentBasket       $paymentBasket
	 * @param  cbpaidPaymentTotalizerCompoundable[]  $taxableTotalizers
	 * @param  string                    $totalizerType
	 * @return void
	 */
	public function computeAllTaxes( $paymentBasket, &$taxableTotalizers, $totalizerType ) {
		$currency								=	$paymentBasket->mc_currency;
		$anyAutoRecurringInBasket				=	$paymentBasket->isAnyAutoRecurringPossibleWithThisBasket();
		
		foreach ( $this->ratesToCompound as /* $priority => */ $ratesOfPriorityItem ) {
			// handle first taxes-inclusive items:
			//TODO		$this->_getItemsTaxesInclusiveToExclusive( $ratesOfPriorityItem, $currency );

			$itemsTaxesToAdd					=	array();

			foreach ( $ratesOfPriorityItem as /* itemIndex => */ $ratesOfItem ) {
				$item							=	$ratesOfItem['item'];

				// item can have first_rate and first_period SOMETIMES, but only of $anyAutoRecurringInBasket
				// totalizer is same rules as basket: if basket has first_period then totalizer follows basket.
				// So:
				// Now $first.... below is refering to the $item :

				$extraAmountBefore				=	0;
				$extraPercents					=	0;
				$extraAmountAfter				=	0;
				$firstExtraAmountBefore			=	0;
				$firstExtraPercents				=	0;
				$firstExtraAmountAfter			=	0;
				$itemHasReallyFirstRate			=	( isset( $item->first_validity ) && $item->first_validity ) || $item->first_rate;	// when $item is cbpaidPaymentTotalizer then it doesn't have first_validity
				$itemHasFirstRate				=	$itemHasReallyFirstRate || $item->first_discount_amount || $item->first_tax_amount;
				$amountTaxExcl					=	$this->_getItemAmount_first_incl( $item, false, false );
				$amount							=	$this->_getItemAmount_first_incl( $item, true,  false );
				if ( $itemHasFirstRate ) {
					$firstAmountTaxExcl			=	$this->_getItemAmount_first_incl( $item, false, true, $itemHasReallyFirstRate );
					$firstAmount				=	$this->_getItemAmount_first_incl( $item, true,  true, $itemHasReallyFirstRate );
				} else {
					$firstAmountTaxExcl			=	null;
					$firstAmount				=	null;
				}

				// first handles the item:
				$totalizerRatesOfItem					=	array();

				// first period first, as some totalizers differ after use (e.g. wallet use), then the normal period:
				foreach ( $ratesOfItem['ratestots'] as $k => $rateTotalizer ) {
					/** @var $rate cbpaidTotalizertypeCompoundable */
					list( $rate, $totalizer )	=	$rateTotalizer;
					$rate->setBasket( $paymentBasket );
					$rate->setPaymentItem( $item );

/* NOT NOW, MAYBE LATER DIFFERENTLY:
					if ( $item->getPlanParam( 'tax_taxing_date', 1 ) == 2 ) {
						$tax_taxing_date_ratio	=	$item->getPlanParam( 'tax_taxing_date_ratio', 0.0 );
						// we have a non-linear taxation on this item:
		$item = NEW cbpaidPaymentItem();
						// Does this taxRate start after the start (or stops before the stop) of the initiating time of the basket ? :
						global $_CB_framework;
						$offset					=	$_CB_framework->getCfg( 'offset' ) * 3600;
						$itemStartDay			=	date( 'Y-m-d', cbpaidTimes::getInstance()->strToTime( $item->start_date ) + $offset );
						$taxRate_stop_date		=	( $rate->stop_date == '0000-00-00' ? '9999-99-99' : $rate->stop_date );
						if ( ( $rate->start_date <= $itemStartDay ) && ( $taxRate_stop_date >= $itemStartDay ) ) {
							// The item starts during the validity period of this tax rate: we need to fix the ratios:
							
						} else {
							$proraterFactor		=	( 100.0 - (float) $tax_taxing_date_ratio ) / 100;
						}
	
					} else {
						$proraterFactor			=	1;
					}
*/
		//			if ( $totalizer->first_rate !== null ) {
					/** @var $totalizer cbpaidPaymentTotalizerCompoundable */
					$firstPeriodProrater		=	$totalizer->proRatePeriod( $item, true );
					$totalizerRatesOfItem[$k]['fr']	=	$firstPeriodProrater;
					if ( $itemHasFirstRate ) {
						$firstExtraAmountBefore	+=	( $totalizerRatesOfItem[$k]['fb']		=	$rate->getAmountBeforePercents( $firstAmount, $firstAmountTaxExcl, $firstPeriodProrater, true, $currency ) );
						$firstExtraPercents		+=	( $totalizerRatesOfItem[$k]['fp']		=	$rate->getPercents( $firstAmount, $firstAmountTaxExcl, $firstPeriodProrater,true ) );
						$firstExtraAmountAfter	+=	( $totalizerRatesOfItem[$k]['fa']		=	$rate->getAmountAfterPercents( $firstAmount, $firstAmountTaxExcl, $firstPeriodProrater,true, $currency ) );
					} else {
						$firstExtraAmountBefore	+=	( $totalizerRatesOfItem[$k]['fb']		=	$rate->getAmountBeforePercents( $amount, $amountTaxExcl, $firstPeriodProrater, true, $currency ) );
						$firstExtraPercents		+=	( $totalizerRatesOfItem[$k]['fp']		=	$rate->getPercents( $amount, $amountTaxExcl, $firstPeriodProrater, true ) );
						$firstExtraAmountAfter	+=	( $totalizerRatesOfItem[$k]['fa']		=	$rate->getAmountAfterPercents( $amount, $amountTaxExcl, $firstPeriodProrater, true, $currency ) );
					}
		//			}
				}
				// Now the normal recurring period:
				if ( $anyAutoRecurringInBasket && ( $item->autorecurring > 0 ) ) {
					foreach ( $ratesOfItem['ratestots'] as $k => $rateTotalizer ) {
						list( $rate, $totalizer )	=	$rateTotalizer;
						if ( $totalizer->rate !== null ) {
							$periodProrater				=	$totalizer->proRatePeriod( $item, false );
							$totalizerRatesOfItem[$k]['r']	=	$periodProrater;
							$extraAmountBefore			+=	( $totalizerRatesOfItem[$k]['b']		=	$rate->getAmountBeforePercents( $amount, $amountTaxExcl, $periodProrater, false, $currency ) );
							$extraPercents				+=	( $totalizerRatesOfItem[$k]['p']		=	$rate->getPercents( $amount, $amountTaxExcl, $periodProrater, false ) );
							$extraAmountAfter			+=	( $totalizerRatesOfItem[$k]['a']		=	$rate->getAmountAfterPercents( $amount, $amountTaxExcl, $periodProrater, false, $currency ) );
						}
					}
				}
				// Now adds to the item:
				if ( $itemHasFirstRate ) {
					$first_tax_amount				=	( ( ( $firstAmount + $firstExtraAmountBefore ) * ( 1 + $firstExtraPercents ) ) + $firstExtraAmountAfter ) - $firstAmount;
				} else {
					$first_tax_amount				=	( ( ( $amount + $firstExtraAmountBefore ) * ( 1 + $firstExtraPercents ) ) + $firstExtraAmountAfter ) - $amount;
				}
				$second_tax_amount					=	( ( ( $amount + $extraAmountBefore ) * ( 1 + $extraPercents ) ) + $extraAmountAfter ) - $amount;

				if ( $anyAutoRecurringInBasket && ( $item->autorecurring > 0 ) ) {
					// We cannot do this now, otherwise same-priority taxes will be compounded:
					// $item->first_tax_amount	+=	$first_tax_amount;
					// So store that for after all rates totalizers of this priority have been handled:
					if ( ( $first_tax_amount != 0 ) && ( $itemHasFirstRate || ( $first_tax_amount != $second_tax_amount ) ) ) {
						if ( ! $itemHasFirstRate ) {
							// As the following step will create the first rate for the item, we need to copy current discount from regular discount amount:
							$item->first_discount_amount	=	$item->discount_amount;
							$item->first_tax_amount			=	$item->tax_amount;
						} 
						// Now prepare following statement but deferred:
						// $item->first_discount_amount (or ->first_tax_amount)		+=	$first_tax_amount;
						$itemsTaxesToAdd[]					=	array( $item, $this->_getItemTotalizerColumnName( true ), $first_tax_amount );
					} else {
						$second_tax_amount					=	( ( ( $amount + $firstExtraAmountBefore ) * ( 1 + $firstExtraPercents ) ) + $firstExtraAmountAfter ) - $amount;
					}
					if ( $second_tax_amount != 0 ) {
						// Now prepare following statement but deferred:
						// $item->discount_amount (or ->tax_amount)		+=	$tax_amount;
						$itemsTaxesToAdd[]					=	array( $item, $this->_getItemTotalizerColumnName( false ), $second_tax_amount );
					}
				} else {
					$itemsTaxesToAdd[]						=	array( $item, $this->_getItemTotalizerColumnName( false ), $first_tax_amount );
				}
					
				// then, now that totals for this item in this tax priority are known, handles the totalizers (of that priority, for that item):
				foreach ( $ratesOfItem['ratestots'] as $k => $rateTotalizer ) {
					list( $rate, $totalizer )		=	$rateTotalizer;
					if ( $totalizer->first_rate !== null ) {
						if ( $itemHasFirstRate ) {
							$totalizer->first_original_rate		+=	$firstAmount * $totalizerRatesOfItem[$k]['fr'];
							$totalizer->first_rate	+=	$totalizerRatesOfItem[$k]['fb'] + ( ( ( $firstAmount + $firstExtraAmountBefore ) * ( 0 + $totalizerRatesOfItem[$k]['fp'] ) ) + $totalizerRatesOfItem[$k]['fa'] );
						} else {
							// Basket and totalizer have first rate but the item doesn't have one: still add item's non-first tax to totalizer's first tax:
							$totalizer->first_original_rate		+=	$amount * $totalizerRatesOfItem[$k]['fr'];
							$totalizer->first_rate	+=	$totalizerRatesOfItem[$k]['fb'] + ( ( ( $amount + $firstExtraAmountBefore ) * ( 0 + $totalizerRatesOfItem[$k]['fp'] ) ) + $totalizerRatesOfItem[$k]['fa'] );
						}
						$totalizer->first_original_rate			=	round( $totalizer->first_original_rate, 5 );
						$totalizer->first_rate					=	round( $totalizer->first_rate, 5 );
					}
					if ( ( $totalizer->rate !== null )  && ( ( ! $anyAutoRecurringInBasket) || ( $anyAutoRecurringInBasket && ( $item->autorecurring > 0 ) ) ) ) {
						if ( $anyAutoRecurringInBasket ) {
							$totalizer->original_rate			+=	$amount * $totalizerRatesOfItem[$k]['r'];
							$totalizer->rate					+=	$totalizerRatesOfItem[$k]['b'] + ( ( ( $amount + $extraAmountBefore ) * ( 0 + $totalizerRatesOfItem[$k]['p'] ) ) + $totalizerRatesOfItem[$k]['a'] );
						} else {
							$totalizer->original_rate			+=	$amount * $totalizerRatesOfItem[$k]['fr'];
							$totalizer->rate					+=	$totalizerRatesOfItem[$k]['fb'] + ( ( ( $amount + $extraAmountBefore ) * ( 0 + $totalizerRatesOfItem[$k]['fp'] ) ) + $totalizerRatesOfItem[$k]['fa'] );
						}
						$totalizer->original_rate				=	round( $totalizer->original_rate, 5 );
						$totalizer->rate						=	round( $totalizer->rate, 5 );
						if ( ( $totalizer->first_rate == null ) && $anyAutoRecurringInBasket ) {
							$totalizer->first_rate				=	'0.0';
							$totalizer->first_item_days			=	$totalizer->item_days;
							$totalizer->first_totalizer_days	=	$totalizer->totalizer_days;
							$totalizer->first_original_rate		=	'0.0';
						}
					}
				}
			}
			unset( $totalizerRatesOfItem );

			// Now can add to items the taxes, before we look at next tax rates priorities:
			foreach ( $itemsTaxesToAdd as $taxToAdd ) {
				$taxToAdd[0]->{$taxToAdd[1]}		+=	$taxToAdd[2];
			}

		}

		// Now that all items and totalizers have been computed, adjust basket:
		foreach ( $taxableTotalizers as $k => $totalizer ) {
			if ( ( $totalizer->totalizer_type == $totalizerType ) && $totalizer->hasItems() ) {
				if ( ! $totalizer->applyThisTotalizerToBasket( $paymentBasket, $anyAutoRecurringInBasket ) ) {
					unset( $taxableTotalizers[$k] );
				}
			}
		}
	}
/* TODO LATER:
	private function _getItemsTaxesInclusiveToExclusive( $ratesOfPriorityItem, $currency ) {
		foreach ( array_reverse( $this->ratesToCompound ) as $ratesOfPriorityItem ) {			//DIFFMINUS
			$itemsTaxesToAdd					=	array();

			foreach ( array_reverse( $ratesOfPriorityItem ) as $ratesOfItem ) {					//DIFFMINUS
				$item							=	$ratesOfItem['item'];
				if ( $item->getPlanParam( 'tax_prices_inclusive_tax', 0 ) == 1 ) {				//DIFFMINUS
					// Convert Taxes-Included Payment Items to non-tax-included ones,
					// so that when we compute taxes, we can handle them same way as normal taxes-excluded items:

					$extraAmountBefore				=	0;
					$extraPercents					=	0;
					$extraAmountAfter				=	0;
					$itemHasFirstRate				=	( isset( $item->first_validity ) && $item->first_validity ) || $item->first_rate;	// when $item is cbpaidPaymentTotalizer then it doesn't have first_validity
					$amount							=	$item->rate;							//DIFFMINUS
					$amountTaxExcl					=	$amount - $item->tax_amount;			//DIFFMINUS
					if ( $itemHasFirstRate ) {
						$firstExtraAmountBefore		=	0;
						$firstExtraPercents			=	0;
						$firstExtraAmountAfter		=	0;
						$firstAmount				=	$item->first_rate;						//DIFFMINUS
						$firstAmountTaxExcl			=	$firstAmount - $item->first_tax_amount;	//DIFFMINUS
					}
	
					// first handles the item:
					$totalizerRatesOfItem					=	array();
					foreach ( $ratesOfItem['ratestots'] as $k => $rateTotalizer ) {
						list( $rate, $totalizer )	=	$rateTotalizer;
						if ( $itemHasFirstRate && ( $totalizer->first_rate !== null ) ) {
							$periodProrater			=	$totalizer->proRatePeriod( $item, true );
							$firstExtraAmountBefore	+=	( $totalizerRatesOfItem[$k]['fb']		=	$periodProrater * $rate->getAmountBeforePercents( $firstAmount, $firstAmountTaxExcl, true, $currency ) );
							$firstExtraPercents		+=	( $totalizerRatesOfItem[$k]['fp']		=	$periodProrater * $rate->getPercents( $firstAmount, $firstAmountTaxExcl, true ) );
							$firstExtraAmountAfter	+=	( $totalizerRatesOfItem[$k]['fa']		=	$periodProrater * $rate->getAmountAfterPercents( $firstAmount, $firstAmountTaxExcl, true, $currency ) );
						}
						if ( $totalizer->rate !== null ) {
							$periodProrater			=	$totalizer->proRatePeriod( $item, false );
							$extraAmountBefore		+=	( $totalizerRatesOfItem[$k]['b']		=	$periodProrater * $rate->getAmountBeforePercents( $amount, $amountTaxExcl, false, $currency ) );
							$extraPercents			+=	( $totalizerRatesOfItem[$k]['p']		=	$periodProrater * $rate->getPercents( $amount, $amountTaxExcl, false ) );
							$extraAmountAfter		+=	( $totalizerRatesOfItem[$k]['a']		=	$periodProrater * $rate->getAmountAfterPercents( $amount, $amountTaxExcl, false, $currency ) );
						}
					}
					// Now adds to the item:
					if ( $itemHasFirstRate ) {
						$first_tax_amount			=	( ( ( $firstAmount - $firstExtraAmountAfter ) / ( 1 + $firstExtraPercents ) ) - $firstExtraAmountBefore ) - $firstAmount;	//DIFFMINUS		
							
						// We cannot do this now, otherwise same-priority taxes will be compounded:
						// $item->first_tax_amount	+=	$first_tax_amount;
						// So store that for after all rates totalizers of this priority have been handled:
						if ( $first_tax_amount != 0 ) {
							$itemsTaxesToAdd[]		=	array( $item, 'first_tax_amount', $first_tax_amount );
						}
					}
					// $tax_amount					=	( ( ( $amount + $extraAmountBefore ) * ( 1 + $extraPercents ) ) + $extraAmountAfter ) - $amount;
					$tax_amount						=	( ( ( $amount - $extraAmountAfter ) / ( 1 + $extraPercents ) ) - $extraAmountBefore ) - $amount;	//DIFFMINUS		
					if ( $tax_amount != 0 ) {
						$itemsTaxesToAdd[]			=	array( $item, 'tax_amount', $tax_amount );
					}

					// then, now that totals for this item in this tax priority are known, handles the totalizers (of that priority, for that item):
					foreach ( $ratesOfItem['ratestots'] as $k => $rateTotalizer ) {
						list( $rate, $totalizer )	=	$rateTotalizer;
						if ( $itemHasFirstRate && ( $totalizer->first_rate !== null ) ) {
							$totalizer->first_rate	+=	$totalizerRatesOfItem[$k]['fb'] + ( ( ( $firstAmount + $firstExtraAmountBefore ) * ( 0 + $totalizerRatesOfItem[$k]['fp'] ) ) + $totalizerRatesOfItem[$k]['fa'] );
						}
						if ( $totalizer->rate !== null ) {
							$totalizer->rate		+=	$totalizerRatesOfItem[$k]['b'] + ( ( ( $amount + $extraAmountBefore ) * ( 0 + $totalizerRatesOfItem[$k]['p'] ) ) + $totalizerRatesOfItem[$k]['a'] );
			//FIXME				$totalizer->rate		+=	- $totalizerRatesOfItem[$k]['a'] + ( ( ( $amount - $extraAmountAfter ) / ( 1 + $totalizerRatesOfItem[$k]['p'] ) ) + $totalizerRatesOfItem[$k]['a'] );
						}
					}

				}
			}
			unset( $totalizerRatesOfItem );

			// Now can add to items the taxes, before we look at next tax rates priorities:
			foreach ( $itemsTaxesToAdd as $taxToAdd ) {
				$taxToAdd[0]->{$taxToAdd[1]}	+=	$taxToAdd[2];
			}

		}
	}
*/
}

