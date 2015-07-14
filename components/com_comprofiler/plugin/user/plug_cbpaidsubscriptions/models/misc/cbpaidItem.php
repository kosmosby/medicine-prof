<?php
/**
 * @version $Id: cbpaidItem.php 1608 2012-12-29 04:12:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Product class: only here to be used as generic product class and extended for individual product types
 *
 */
abstract class cbpaidItem extends cbpaidTimed {
	public $id							=	null;
	public $item_type;
	public $currency;
	public $rate;
	public $validity;
	public $bonustime;
	public $first_rate;
	public $first_validity;
	/** Autorecurring subscription: 0: no, 1: yes always, 2: user-selectable.
	 * @var int */
	public $autorecurring;
	/** The total times of regular priced recurrings, so not including for the first period:
	 * @var int */
	public $recurring_max_times;

	protected $_displayPeriodPriceRecursionsLimiter	=	1;
	/**
	 * In-memory variable, Set in function getUpgradeAndRenewalPossibilities()
	 * @var int|float|null
	 */
	public $_renewalDiscount;

	/**
	 * Constructor
	 *
	 *	@param string      $table  name of the table in the db schema relating to child class
	 *	@param string      $key    name of the primary key field in the table
	 *	@param CBdatabase  $db     CB Database object
	 */
	public function __construct( $table, $key, &$db = null ) {
		parent::__construct( $table, $key, $db );
	}
	/**
	 * PRICING METHODS:
	 */
	/**
	 * Returns currency of this timed item
	 *
	 * Previously: param  ParamsInterface  $params  OBSOLTETE
	 * @return string  currency code
	 */
	public function currency() {
		$cbpaidMoney		=&	cbpaidMoney::getInstance();
		return $cbpaidMoney->currency( $this->currency );
	}
	/**
	 * Checks if the item is tangible or intangible (always intangible for now)
	 *
	 * @return boolean
	 */
	public function isTangible() {
		return false;			//TBD: implement this further.
	}
	/**
	 * RENDERING METHODS:
	 */
	/**
	 * Gets the prefix before 'rate' and 'validity' for this plan depending on reason and occurrences
	 *
	 * @param  string        $reason      Payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  int           $occurrence  = 0 : first occurrence, >= 1: next occurrences
	 * @param  string        $variable    Name of main recurring variable ( 'rate' or 'validity' )
	 * @return string                     'rate' or 'first_rate' or 'validity' or 'first_validity'
	 */
	public function getPlanVarName( /** @noinspection PhpUnusedParameterInspection */ $reason, $occurrence, $variable ) {
		return $variable;
	}
	/**
	 * Returns pricedisplay setting if exists, otherwise NULL
	 *
	 * @return string|null
	 */
	protected function getPriceDisplay( ) {
		return null;
	}
	/**
	 * Returns HTML or TEXT rendering the validity period and pricing for that given plan.
	 *
	 * @param  string       $reason      payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  int          $occurrence  = 0 : first occurrence, >= 1: next occurrences
	 * @param  int          $expiryTime  expiry time of plan
	 * @param  int          $startTime   starting time of plan
	 * @param  boolean      $html        TRUE for HTML, FALSE for TEXT
	 * @param  boolean      $roundings   TRUE: do round, FALSE: do not round display
	 * @param  boolean      $displaySecondaryCurrency   TRUE: display secondary currencies, FALSE: only display in $this->currency()
	 * @return string
	 */
	public function displayPeriodPrice( $reason = 'N', $occurrence = 0, $expiryTime = null, $startTime = null, $html = true, $roundings = true, $displaySecondaryCurrency = true ) {
		global $_PLUGINS;

		$ret								=	'';
		if ( ( $this->_displayPeriodPriceRecursionsLimiter-- == 1 ) && $this->id ) {
			$_PLUGINS->loadPluginGroup( 'user', 'cbsubs.' );
			$_PLUGINS->loadPluginGroup('user/plug_cbpaidsubscriptions/plugin');
			$ret	=	implode( '', $_PLUGINS->trigger( 'onCPayBeforeDisplayProductPeriodPrice', array( &$this, &$reason, &$occurrence, &$expiryTime, &$startTime, &$html, &$roundings, &$displaySecondaryCurrency ) ) );
		}
		$price								=	$this->get( 'rate' );

		$firstPeriodFullPrice				=	null;
		$firstPeriodPrice					=	null;

		$recurring_max_times				=	$this->get( 'recurring_max_times' );
		$autorecurring						=	$this->get( 'autorecurring' );

		if ( $reason == 'R' ) {		// renew:
			$prorateDiscount				=	false;
		} else {					// register or upgrade:
			$prorateDiscount				=	isset( $this->_renewalDiscount ) && ( $this->_renewalDiscount !== null );

			$varName						=	$this->getPlanVarName( $reason, $occurrence, 'validity' );
			$varRate						=	$this->getPlanVarName( $reason, $occurrence, 'rate' );

			if ( ( $varName != 'validity' ) && ( $varRate !== 'rate' ) ) {
				$first_price				=	$this->get( $varRate );
				if ( ( ! cbpaidMoney::equalFloats( $price, $first_price) ) || ( $this->get( $varName ) != $this->get( 'validity' ) ) ) {
					$firstPeriodFullPrice	=	$first_price;
					if ( $prorateDiscount ) {
						$firstPeriodPrice	=	$this->_renewalDiscount;
					} else {
						$firstPeriodPrice	=	$first_price;
					}
				} else {
					// Present without first_ things, but as there is one increment $recurring_max_times :
					$firstPeriodFullPrice	=	null;
					if ( $prorateDiscount ) {
						$firstPeriodPrice	=	$this->_renewalDiscount;
					} else {
						$firstPeriodPrice	=	$price;
					}
					if ( $recurring_max_times ) {
						$recurring_max_times++;
					}
				}
			} else if ( $prorateDiscount ) {
				$firstPeriodFullPrice		=	null;
				$firstPeriodPrice			=	$this->_renewalDiscount;
			}
		}

		$displayPeriod						=	true;

		$ret .= $this->renderPeriodPrice( $price, $firstPeriodFullPrice, $firstPeriodPrice, $prorateDiscount, $expiryTime, $startTime,
			$autorecurring, $recurring_max_times, $reason, $occurrence, $html, $roundings, $displayPeriod, $displaySecondaryCurrency );

		$args					=	array( $price, $firstPeriodFullPrice, $firstPeriodPrice, $prorateDiscount, $expiryTime, $startTime,
			$autorecurring, $recurring_max_times, $reason, $occurrence, $html, $roundings, $displayPeriod, $displaySecondaryCurrency );
		$method					=	array( $this, 'renderPeriodPrice' );

		if ( ( $this->_displayPeriodPriceRecursionsLimiter == 0 ) && $this->id ) {
			$_PLUGINS->trigger( 'onCPayAfterDisplayProductPeriodPrice', array( $this, &$ret, $method, $args ) );
		}
		++$this->_displayPeriodPriceRecursionsLimiter;

		return $ret;
	}
	/**
	 * Returns HTML or TEXT rendering the validity period and pricing for that given plan.
	 * (public because can be used as call-back in onCPayAfterDisplayProductPeriodPrice event handlers)
	 *
	 * @param  float        $price                 price of plan
	 * @param  float|null   $firstPeriodFullPrice  regular price of plan in first period
	 * @param  float|null   $firstPeriodPrice      real price of plan in first period (with discount)
	 * @param  boolean      $prorateDiscount       is a discount applied to first price ?
	 * @param  int          $expiryTime            expiry time of plan
	 * @param  int          $startTime             starting time of plan
	 * @param  boolean      $autorecurring         is plan autorecurring ?
	 * @param  int          $recurring_max_times   if autorecurring, maximum total number of occurrences
	 * @param  string       $reason                payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  int          $occurrence            Occurence of the payment for that item
	 * @param  boolean      $html                  TRUE for HTML, FALSE for TEXT
	 * @param  boolean      $roundings             TRUE: do round, FALSE: do not round display
	 * @param  boolean      $displayPeriod         TRUE: display price and period, FALSE: display price only (ONLY for single prices)
	 * @param  boolean       $displaySecondaryCurrency   TRUE: display secondary currencies, FALSE: only display in $this->currency()
	 * @return string                              HTML or TEXT
	 */
	public function renderPeriodPrice( $price, $firstPeriodFullPrice, $firstPeriodPrice, $prorateDiscount, $expiryTime, $startTime,
									   $autorecurring, $recurring_max_times, $reason, $occurrence, $html, $roundings = true, $displayPeriod = true, $displaySecondaryCurrency = true ) {
		global $_CB_framework;

		$params							=&	cbpaidApp::settingsParams();
		$return							=	'';

		$priceDisplay					=	$this->getPriceDisplay();	// This is used in this class if available (it's actually only available in derived class cbpaidProduct and in all its derived classes)
		if ( $priceDisplay == null ) {
			$priceDisplay				=	'[automatic]';
		} else {
			$priceDisplay				=	( $html ? CBPTXT::Th( $priceDisplay ) : CBPTXT::T( $priceDisplay ) );
		}
		$period_for_price_text			=	$params->get('period_for_price_text', '%1s%2s%3s' );
		$textFor						=	' ' . ( $html ? CBPTXT::Th($params->get('regtextFor')) : CBPTXT::T($params->get('regtextFor')) ) . ' ';

		$priceText						=	$this->renderPricesWithConversion( $price, $html, $roundings, $displaySecondaryCurrency );

		if ( $firstPeriodFullPrice !== null ) {
			$firstPeriodFullPriceText	=	$this->renderPricesWithConversion( $firstPeriodFullPrice, $html, $roundings, $displaySecondaryCurrency );
		} else {
			$firstPeriodFullPriceText	=	null;
		}
		if ( $firstPeriodPrice !== null ) {
			$discountedPriceText		=	$this->renderPricesWithConversion( $firstPeriodPrice, $html, $roundings, $displaySecondaryCurrency );
		} else {
			$discountedPriceText		=	null;
		}

		if ( $firstPeriodFullPrice !== null ) {
			$effectiveFirstPeriodFullPrice		=	$firstPeriodFullPrice;
			$effectiveFirstPeriodFullPriceText	=	$firstPeriodFullPriceText;
		} else {
			$effectiveFirstPeriodFullPrice		=	$price;
			$effectiveFirstPeriodFullPriceText	=	$priceText;
		}
		$firstPeriodRendered = null;
		if ( ( $this->bonustime!='0000-00-00 00:00:00' && $reason!='R') || ( ( $firstPeriodPrice || $prorateDiscount ) && ( $effectiveFirstPeriodFullPrice != $firstPeriodPrice ) ) ) {
			if ( ( $firstPeriodPrice || $prorateDiscount ) && ( $effectiveFirstPeriodFullPrice != $firstPeriodPrice ) ) {
				if ( $autorecurring ) {
					if ( $reason == 'U' ) {
						if ( $prorateDiscount ) {
							$subTxt				=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
							$t	= sprintf( CBPTXT::T("The first payment of the upgrade for %s, taking in account your current %s, is %s instead of %s."), '%s', $subTxt, '%s', '%s' );
						} else {
							$t	= CBPTXT::T("The first payment of the upgrade for %s is %s instead of %s.");
						}
					} elseif ( $reason == 'R' ) {
						if ( $prorateDiscount ) {
							$t	= CBPTXT::T("The first payment of the renewal for %s, pro-rata temporis, is %s instead of %s.");
						} else {
							$t	= CBPTXT::T("The first payment of the renewal for %s is %s instead of %s.");
						}
					} else {
						if ( $prorateDiscount ) {
							$t	= CBPTXT::T("The first payment for %s, pro-rata temporis, is %s instead of %s.");
						} else {
							$t	= CBPTXT::T("The first payment for %s is %s instead of %s.");
						}
					}
				} else {
					if ( $reason == 'U' ) {
						if ( $prorateDiscount ) {
							$subTxt				=	CBPTXT::T( $params->get( 'subscription_name', 'subscription' ) );
							$t	= sprintf( CBPTXT::T("The price of the upgrade for %s, taking in account your current %s, is %s instead of %s."), '%s', $subTxt, '%s', '%s' );
						} else {
							$t	= CBPTXT::T("The price of the upgrade for %s is %s instead of %s.");
						}
					} elseif ( $reason == 'R' ) {
						if ( $prorateDiscount ) {
							$t	= CBPTXT::T("The price of the renewal for %s, pro-rata temporis, is %s instead of %s.");
						} else {
							$t	= CBPTXT::T("The price of the renewal for %s is %s instead of %s.");
						}
					} else {
						if ( $prorateDiscount ) {
							$t	= CBPTXT::T("The price for %s, pro-rata temporis, is %s instead of %s.");
						} else {
							$t	= CBPTXT::T("The price for %s is %s instead of %s.");
						}
					}
				}
				$varName				=	$this->getPlanVarName( $reason, $occurrence, 'validity' );
				$firstPeriodRendered	=	sprintf( $t, $this->getFormattedValidity( $expiryTime, $startTime, $varName, $reason, 1, true, $html ),
					$discountedPriceText, $effectiveFirstPeriodFullPriceText );
			} else {
				if ( $autorecurring ) {
					if ( $reason == 'U' ) {
						$t	= CBPTXT::T("The first period for the upgrade will be %s.");
					} elseif ( $reason == 'R' ) {
						$t	= CBPTXT::T("The first period for the renewal will be %s.");
					} else {
						$t	= CBPTXT::T("The first period will be %s.");
					}
					$varName				=	$this->getPlanVarName( $reason, $occurrence, 'validity' );
					$firstPeriodRendered	=	sprintf( $t, $this->getFormattedValidity( $expiryTime, $startTime, $varName, $reason, 1, true, $html ) );
				}
			}
		}

		cbimport( 'cb.tabs' );		//needed for CBuser and replacements

		if ( ( $firstPeriodFullPrice === null ) && ! $autorecurring ) {
			$validityText	=	$this->getFormattedValidity( $expiryTime, $startTime, 'validity', $reason, 1, true, $html );
			if ( ( $validityText != '' ) && $displayPeriod ) {
				$period		=	$this->_span( $validityText,	$html, 'cbregTimeframe', false );
				$for		=	$this->_span( $textFor					,	$html, 'cbregFor', false );
			} else {
				$period		=	'';					// in case 'lifetime' text is ''
				$for		=	'';
			}
			if ( $priceText != '' ) {
				$money		=	$this->_span( $priceText				,	$html, 'cbregRate', false );
			} else {
				$for		=	'';					// in case 'Free' text is ''
				$money		=	'';
			}
			$automatic		=	sprintf( $period_for_price_text, $period, $for, $money );
			if ( strtolower( $priceDisplay ) == '[automatic]' ) {
				$return		.=	$automatic;
			} else {
				$per		=	$this->_span(  ' ' . ( $html ? CBPTXT::Th("per") : CBPTXT::T("per") ) . ' '		,	$html, 'cbregFor', false );
				$cbUser		=	CBuser::getInstance( $_CB_framework->myId() );
				$return		=	$cbUser->replaceUserVars( $priceDisplay, true, false );
				$return		=	str_ireplace( array( '[price]', '[for]', '[period]', '[per]', '[automatic]' ), array( $money, $for, $period, $per, $automatic ), $return );
			}
		} else {
			if ( $firstPeriodFullPrice !== null ) {
				$moneyFirst	=	$this->_span( $firstPeriodFullPriceText	,	$html, 'cbregRate', false );
				$forFirst	=	$this->_span( $textFor						,	$html, 'cbregFor', false );
				$varName	=	$this->getPlanVarName( $reason, $occurrence, 'validity' );
				$periodFirst =	$this->_span( $this->_renderPeriodOfValiditiy( $varName, 1 )	,	$html, 'cbregTimeframe' );
				$then		=	$this->_span( ( $html ? CBPTXT::Th(", then ")	: CBPTXT::T(", then ")	)			,	$html, 'cbregFor', false );
				// $automatic .=	$this->_timedObject->getFormattedValidity( $expiryTime, $startTime, false );
				$automatic	=	sprintf( $period_for_price_text, $periodFirst, $forFirst, $moneyFirst )
					.	$then;
			} else {
				$moneyFirst	=	null;
				$forFirst	=	null;
				$periodFirst =	null;
				$then		=	null;
				$automatic	=	'';
			}
			$money			=	$this->_span( $priceText						,	$html, 'cbregRate', false );
			if ( $price ) {
				$per		=	$this->_span(  ' ' . ( $html ? CBPTXT::Th("per") : CBPTXT::T("per") ) . ' '		,	$html, 'cbregFor', false );
				$period		=	$this->_span( $this->_renderPeriodOfValiditiy( 'validity', 1, false )	,	$html, 'cbregTimeframe' );
				$automatic	.=	sprintf( '%3$s%2$s%1$s', $period, $per, $money );
			} else {
				$per		=	null;
				$period		=	null;
				$automatic	.=	$money;
			}
			if ( $recurring_max_times ) {
				$during		=	$this->_span( ', ' . ( $html ? CBPTXT::Th("during") : CBPTXT::T("during") ) . ' ',	$html, 'cbregFor', false );
				$periodTot	=	$this->_span( $this->_renderPeriodOfValiditiy( 'validity', $recurring_max_times, true, false )	,	$html, 'cbregTimeframe' );
				$automatic	.=	$during . $periodTot;
			} else {
				$during		=	null;
				$periodTot	=	null;
			}

			if ( strtolower( $priceDisplay ) == '[automatic]' ) {
				$return		.=	$automatic;
			} else {
				$cbUser		=	CBuser::getInstance( $_CB_framework->myId() );
				$return		=	$cbUser->replaceUserVars( $priceDisplay, true, false );
				$return		=	str_ireplace( array( '[price]', '[per]', '[period]', '[for]', '[firstperiod]', '[firstfor]', '[firstprice]', '[then]', '[during]', '[totalperiod]', '[automatic]' ),
					array( $money, $per, $period, $textFor, $periodFirst, $forFirst, $moneyFirst, $then, $during, $periodTot, $automatic ), $return );
			}
		}
		if ( $firstPeriodRendered ) {
			$return			.=	' ' . $this->_span( $firstPeriodRendered											,	$html, 'cbregDiscountRate', false );
		}
		return $return;
	}

	/**
	 * Returns formatted time period ( xxx weeks , or xxx years xxx months xxx days xxx hours xxx minutes xxx seconds
	 *
	 * @param  string        $varName                    'validity' or 'fist_validity'
	 * @param  int           $occurrences                [default: 1] multiply period by the occurrences before displaying
	 * @param  boolean       $displayOne                 [default: true] displays also if only 1 unit of something
	 * @param  boolean       $displayCalendarYearStart   [default: true] displays start of calendar year if not January 1st
	 * @return string
	 */
	private function _renderPeriodOfValiditiy( $varName, $occurrences = 1, $displayOne = true, $displayCalendarYearStart = true ) {
		// $ycdhmsArray	=	$this->_timedObject->getValidity( $varName );
		// $prefix			=	( $this->_timedObject->isCalendarValidity( $varName ) ? CBPTXT::T("calendar ") : '' );
		// return $this->_timedObject->renderPeriod( $ycdhmsArray, $occurrences, $displayOne, $prefix );
		$ycdhmsArray	=	$this->getValidity( $varName );
		$prefix			=	( $this->isCalendarValidity( $varName ) ? CBPTXT::T("calendar ") : '' );
		$text			=	$this->renderPeriod( $ycdhmsArray, $occurrences, $displayOne, $prefix );
		$calStart		=	$this->calendarYearStart( $varName );
		if ( $prefix && ( $calStart != '01-01' ) && $displayCalendarYearStart ) {
			list( $m, $d )	=	explode( '-', $calStart );
			$text		.=	' ' . CBPTXT::T("starting") . ' ' . date( 'F j', mktime( 0, 0, 0, $m, $d, 2004 ) );
		}
		return $text;
	}
	/**
	 * Adds a span around text if it's HTML.
	 *
	 * @param  string   $text
	 * @param  boolean  $html
	 * @param  string   $class
	 * @param  boolean  $htmlspecialCh  do a htmlspecialchars
	 * @param  string   $tag
	 * @return string
	 */
	private function _span( $text, $html, $class, $htmlspecialCh = true, $tag='span') {
		if ($html) {
			$r	=	'<' . $tag . ' class="' . $class . '">' . ( $htmlspecialCh ? str_replace( ' ', '&nbsp;', htmlspecialchars( $text ) ) : $text ) . '</' . $tag . '>';
		} else {
			$r	=	$text;
		}
		return $r;
	}

	/**
	 * Renders a price with currency, formatting roundings corresponding to $params
	 *
	 * @param  float         $price      Price: positive float or NULL
	 * @param  string|null   $currency   Currency to render into (default: $this's currency)
	 * @param  boolean       $html       HTML mode or text mode
	 * @param  boolean       $roundings  TRUE: do round, FALSE: do not round display
	 * @return string                    HTML or Text
	 */
	public function renderPrice( $price, $currency = null, $html = true, $roundings = true ) {
		if ( $currency === null ) {
			$currency				=	$this->currency();
		}
		return cbpaidMoney::getInstance()->renderPrice( $price, $currency, $html, $roundings );
	}
	/**
	 * Renders a price in default currency, formatting roundings corresponding to $params
	 *
	 * @param  float         $price      Price: positive float or NULL
	 * @param  boolean       $html       HTML mode or text mode
	 * @param  boolean       $roundings  TRUE: do round, FALSE: do not round display
	 * @param  boolean       $displaySecondaryCurrency   TRUE: display secondary currencies, FALSE: only display in $this->currency()
	 * @return string                    HTML or Text
	 */
	public function renderPricesWithConversion( $price, $html, $roundings = true, $displaySecondaryCurrency = true ) {
		$textCurrency				=	$this->currency();

		$params						=&	cbpaidApp::settingsParams();
		$textSecondaryCurrency		=	$params->get( 'secondary_currency_code' );

		if ( $displaySecondaryCurrency && ( $price != 0 ) && $textSecondaryCurrency && ( $textSecondaryCurrency != $textCurrency ) ) {
			// Initialize convertion+rendering function:
			$this->_renderPriceInCurrency( $price, $html, $roundings );

			// Get and fix format:
			$format					=	$params->get( 'secondary_price_display_format', '[MAIN_CURRENCY_PRICE] (~ [SECONDARY_CURRENCY_PRICE])' );
			if ( $html ) {
				if ( strpos( $format, '<' ) === false ) {
					$format			=	str_replace( array( ' ', '~' ), array( '&nbsp;', '&asymp;' ), $format );
				}
			} else {
				$format				=	str_replace( array( '&nbsp;', '&asymp;' ), array( ' ', '~' ), strip_tags( $format ) );
			}

			// Set main and secondary currencies:
			$format					=	str_replace( array( '[MAIN_CURRENCY_PRICE]', '[SECONDARY_CURRENCY_PRICE]' ), array( '[' . $textCurrency . ']', '[' . $textSecondaryCurrency . ']' ), $format );
			// Render prices:
			$priceText				=	preg_replace_callback( '/\[(...)\]/', array( $this, '_renderPriceInCurrency' ), $format );
		} else {
			// Single currency simple case:
			$priceText				=	$this->renderPrice( $price, $textCurrency, $html, $roundings );
		}

		return $priceText;
	}
	/**
	 * preg_replace_callback replacer function
	 * @access protected
	 * (do not make private in this case, as it is invoked from an inherited class, it would hit PHP bug https://bugs.php.net/bug.php?id=62547 and protected triggers bug in PHP 5.2)
	 *
	 * @param  float|array  $input         Price to set  (or preg callback input array)
	 * @param  boolean      $htmlSet       $html to set
	 * @param  boolean      $roundingsSet  $roundings to set
	 * @return string
	 */
	public function _renderPriceInCurrency( $input, $htmlSet = null, $roundingsSet = null ) {
		static $price					=	null;
		static $html					=	null;
		static $roundings				=	null;
		if ( ! is_array( $input ) ) {
			$price						=	$input;
			$html						=	$htmlSet;
			$roundings					=	$roundingsSet;
			$secondaryPriceText			=	null;
		} else {
			$secondaryPriceText			=	null;
			$textCurrency				=	$this->currency();
			$textSecondaryCurrency		=	$input[1];

			if ( $textSecondaryCurrency ) {
				if ( $textSecondaryCurrency == $textCurrency ) {
					$priceInCurrency	=	$price;
				} else {
					$priceInCurrency	=	cbpaidMoney::getInstance()->convertPrice( $price, $textCurrency, $textSecondaryCurrency, $roundings, true );
				}
				$secondaryPriceText		=	$this->renderPrice( $priceInCurrency, $textSecondaryCurrency, $html, $roundings );
			}
		}
		return $secondaryPriceText;
	}
}	// class cbpaidItem
