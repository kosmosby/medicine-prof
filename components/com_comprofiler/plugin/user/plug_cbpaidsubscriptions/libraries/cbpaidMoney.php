<?php
/**
 * @version $Id: cbpaidMoney.php 1591 2012-12-27 14:49:19Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class handling money and currencies
 */
class cbpaidMoney {
	/** Main params
	 *  @var ParamsInterface */
	protected $params;
	/**
	 * Constructor
	 * @private
	 */
	protected function __construct( ) {
		$this->params					=&	cbpaidApp::settingsParams();
	}
	/**
	 * Gets a single instance of the cbpaidMoney class
	 *
	 * @return cbpaidMoney
	 */
	public static function & getInstance( ) {
		static $singleInstance	=	null;
		if ( $singleInstance === null ) {
			$singleInstance		=	new cbpaidMoney();
		}
		return $singleInstance;
	}
	/**
	 * Checks for 2 floats to be identical, despite minor internal representation artifacts
	 *
	 * @param  float   $v1  First value
	 * @param  float   $v2  Second value
	 * @return boolean      TRUE: equal, FALSE: not equal
	 */
	public static function equalFloats( $v1, $v2 ) {
		return ( abs( $v1 - $v2 ) < 0.0000001 );
	}
	/**
	 * returns price (rate) in the given currency.
	 *
	 * @param  float       $amount            Amount to convert
	 * @param  string      $fromCurrencyCode  ISO currency
	 * @param  string      $toCurrencyCode    ISO currency
	 * @param  boolean     $withRounding      If roundings corresponding to params should be done
	 * @param  boolean     $withMarkup        If markup corresponding to params should be done
	 * @return float|null                      returns $price in $currency_code or null if it can not convert.
	 */
	public function convertPrice( $amount, $fromCurrencyCode, $toCurrencyCode, $withRounding, $withMarkup ) {
		if ( $toCurrencyCode && ( $toCurrencyCode != $fromCurrencyCode ) ) {
			$_CBPAY_CURRENCIES		=&	cbpaidApp::getCurrenciesConverter();
			$amount					=	$_CBPAY_CURRENCIES->convertCurrency( $fromCurrencyCode, $toCurrencyCode, $amount );		// null if cannot convert

			if ( $amount !== null ) {
				if ( $withMarkup ) {
					$markup			=	$this->params->get( 'currency_conversion_markup_percent', 0 );
					if ( $markup ) {
						$amount		=	$amount * ( 1.0 + ( $markup / 100.0 ) );
					}
				}

				if ( $withRounding ) {
					$roundings		=	$this->params->get( 'rounding_converted_currency_price', 0 );
					$priceRoundings	=	( $roundings ? $this->params->get('price_roundings', 100 ) : 100 );
					$amount			=	round( $amount * $priceRoundings ) / $priceRoundings;
				}
			}
		}
		return $amount;
	}

	/**
	 * Renders a price in HTML with currency, formatting roundings corresponding to $params
	 *
	 * @param  float    $price                      Price: positive float, 0 (zero => 'regtextFree' param) or NULL/'' ('-')
	 * @param  string   $currency                   ISO currency
	 * @param  boolean  $html                       HTML mode or text mode
	 * @param  boolean  $roundings                  TRUE: use settings roundings, FALSE: round to cents
	 * @param  boolean  $displaySpecialNullAndZero
	 * @return string                               HTML or text
	 */
	public function renderPrice( $price, $currency, $html = false, $roundings = true, $displaySpecialNullAndZero = true ) {
		$params							=	$this->params;
		if ( $displaySpecialNullAndZero ) {
			if ( ( $price === null ) || ( $price === '' ) || ( $price < 0 ) ) {
				return '-';
			} elseif ( $price == 0 ) {
				return ( $html ? CBPTXT::Th( $params->get( 'regtextFree' ) ) : CBPTXT::T( $params->get( 'regtextFree' ) ) );
			}
			$sign					=	'';
		} else {
			if ( $price < 0 ) {
				$price				=	abs( $price );
				$sign				=	( $html ? '&minus;&nbsp;' : '- ' );		// J does not setlocale! so this does not work:  $locale				=	localeconv(); $sign = $locale['negative_sign'];
			} else {
				$sign				=	'';
			}
		}
		if ( $currency === null ) {
			$currency				=	$params->get( 'currency_code', 'USD' );
		}
		$priceRoundings				=	( $roundings ? $params->get('price_roundings', 100 ) : 100 );
		$priceCurrencyFormat		=	$params->get('price_currency_format', '%2$s %1$s' );
		if ( $html ) {
			$priceCurrencyFormat	=	str_replace( ' ', '&nbsp;', $priceCurrencyFormat );
		}
		$textHtmlSymbol				=	$this->renderCurrencySymbol( $currency, $html );
		$priceRounded				=	$this->renderNumber( round( $price * $priceRoundings ) / $priceRoundings, 'money', $roundings, $html );
		$priceText					=	$sign . sprintf( $priceCurrencyFormat, $priceRounded, ( $html ? '<span class="cbregPcur">' . CBPTXT::Th( $currency ) . '</span>' : CBPTXT::T( $currency ) ), $textHtmlSymbol );
		if ( $html ) {
			$priceText				=	'<span class="cbregPriceCur">' . $priceText . '</span>';
		}
		return $priceText;
	}
	/**
	 * Renders the currency symbol according to global settings
	 *
	 * @param  string|null  $currency
	 * @param  boolean      $html
	 * @return string
	 */
	public function renderCurrencySymbol( $currency, $html ) {
		$params						=	$this->params;
		if ( $currency === null ) {
			$currency				=	$params->get( 'currency_code', 'USD' );
		}
		$priceCurrencyFormat		=	$params->get('price_currency_format', '%2$s %1$s' );
		if ( $html ) {
			if ( strpos( $priceCurrencyFormat, '%3' ) !== false ) {
				$currencySymbols	=	array ( 'EUR' => '&euro;', 'USD' => '$', 'GBP' => '&pound;', 'JPY' => '&yen;', 'CHF' => 'Fr.', 'CAD' => 'C$', 'AUD' => '$', 'BRL' => 'R$', 'CNY' => '&yen;', 'DKK' => 'KR', 'ZAR' => 'R', 'SEK' => 'KR' );		//TBD: move to currency table
				$textHtmlSymbol		=	isset( $currencySymbols[$currency] ) ? $currencySymbols[$currency] : CBPTXT::Th( $currency );
			} else {
				$textHtmlSymbol		=	CBPTXT::Th( $currency );
			}
			$textHtmlSymbol			=	'<span class="cbregPcur">' . $textHtmlSymbol . '</span>';
		} else {
			$textHtmlSymbol			=	CBPTXT::T( $currency );
		}
		return $textHtmlSymbol;
	}
	/**
	 * Checks if currency symbol is before or after the amount
	 *
	 * @return boolean
	 */
	public function currencyAfterOrNotBefore( ) {
		$params						=	$this->params;
		$priceCurrencyFormat		=	$params->get('price_currency_format', '%2$s %1$s' );
		return ( strpos( $priceCurrencyFormat, '%' ) > strpos( $priceCurrencyFormat, '%1' ) );
	}
	/**
	 * Renders a number in a quite sophisticated way:
	 * - WARNING: is NOT rounded when $roundings = true, but interprets format that way
	 * - formatted corresponding to params
	 * - with complete HTML css markup if rendered with $html = true
	 *
	 * @param  float    $number
	 * @param  string   $type
	 * @param  boolean  $roundings   WARNING: is NOT rounded when $roundings = true, but interprets format that way
	 * @param  boolean  $html
	 * @return string
	 */
	public function renderNumber( $number, $type = 'money', $roundings = true, $html = false ) {
		$params							=	$this->params;
		$locale							=	localeconv();

		$separators						=	$params->get( 'numbers_separators', '|*|.' );
		$priceNumberFormat				=	$params->get( 'price_number_format', '%.2f' );
		$append							=	'';

		if ( $separators == '' ) {
			if ( $type == 'money' ) {
				$decimalSeparator		=	$locale['mon_decimal_point'];
				$thousandsSeparator		=	$locale['mon_thousands_sep'];
			} else {
				$decimalSeparator		=	$locale['decimal_point'];
				$thousandsSeparator		=	$locale['thousands_sep'];
			}
			if ( $decimalSeparator == null ) {
				// locale not set:
				$decimalSeparator		=	'.';
				$thousandsSeparator		=	'';
			}
		} else {
			list( $thousandsSeparator, $decimalSeparator )	=	explode( '|*|', $separators );
		}

		if ( $type == 'int' ) {
			$decimalPlaces				=	0;
		} else {
			switch ( $priceNumberFormat ) {
				case '':
					$decimalPlaces		=	(int) $locale['frac_digits'];
					break;
				case '%f':
					return (string) $number;

				case '%.0f.-':
				case '%.0f,-':
					// old bit strange setting, but keep for compatibility: to always round but in some cases to still display 2 digits:
					if ( $roundings || ( ( (float) ( (int) $number ) ) === ( (float) $number ) ) ) {
						$decimalPlaces		=	0;
						$append				=	$decimalSeparator . ( $html ? '&ndash;&nbsp;&nbsp;' : '- ' );
					} else {
						$decimalPlaces		=	2;		// supposition: 2 decimal places
					}
					break;

				case '%.2f.-':
				case '%.2f,-':
				case '%.2f.':
					if ( ( (float) ( (int) $number ) ) === ( (float) $number ) ) {
						$decimalPlaces		=	0;
						if ( $priceNumberFormat != '%.2f.' ) {
							$append			=	$decimalSeparator . ( $html ? '&ndash;&nbsp;&nbsp;' : '- ' );
						}
					} else {
						$decimalPlaces		=	2;
					}
					break;

				default:
					$decimalPlaces		=	(int) substr( $priceNumberFormat, 2, 1 );
					break;
			}
		}

		$formatted						=	number_format( $number, $decimalPlaces, $decimalSeparator, $thousandsSeparator ) . $append;
		if ( $html ) {
			$parts						=	explode( $decimalSeparator, $formatted );
			if ( count( $parts ) == 2 ) {
				$formatted				=	'<span class="cbregPint cbregPintwCts">' . $parts[0] . '</span>'
					.	'<span class="cbregPsep">' . $decimalSeparator . '</span>'
					.	'<span class="cbregPcts">' . $parts[1] . '</span>';
			} else {
				$formatted				=	'<span class="cbregPint cbregPintAlone">' . $formatted . '</span>'
					.	'<span class="cbregPnocts"></span>';
			}
		}
		return $formatted;
	}
	/**
	 * Rounds a number corresponding to params.
	 *
	 * @param  float   $number  The number to round
	 * @param  string  $type    The type of number ( 'money', 'int' )
	 * @return float
	 */
	public function roundNumber( $number, $type = 'money' ) {
		$params							=	$this->params;

		$priceRoundings					=	$params->get('price_roundings', 100 );
		$number							=	round( $number * $priceRoundings ) / $priceRoundings;

		$priceNumberFormat				=	$params->get( 'price_number_format', '%.2f' );
		if ( $type == 'int' ) {
			$decimalPlaces				=	0;
		} else {
			switch ( $priceNumberFormat ) {
				case '':
					$locale				=	localeconv();
					$decimalPlaces		=	(int) $locale['frac_digits'];
					break;
				case '%f':
					return (float) $number;

				case '%.0f.-':
				case '%.0f,-':
					$decimalPlaces		=	0;
					break;

				case '%.2f.-':
				case '%.2f,-':
				case '%.2f.':
					$decimalPlaces		=	2;
					break;

				default:
					$decimalPlaces		=	(int) substr( $priceNumberFormat, 2, 1 );
					break;
			}
		}
		return round( $number, $decimalPlaces );
	}
	/**
	 * Interprets the currency-code given as parameter
	 * and defaults to default currency if none.
	 *
	 * @param  string  $currency
	 * @return string
	 */
	public function currency( $currency ) {
		if ( $currency == '' ) {
			$currency					=	$this->params->get( 'currency_code', 'USD' );
		}
		if ( $currency == '' ) {
			$currency					=	'USD';
		}
		return $currency;
	}
}	// class cbpaidMoney
