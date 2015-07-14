<?php
/**
 * @version $Id: cbpaidPaymentCurrencyInfo.php 1541 2012-11-23 22:21:52Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Registry\ParamsInterface;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * This class is only used for backend:
 */
class cbpaidPaymentCurrencyInfo {
	/**
	 * BACKEND-ONLY XML RENDERING METHODS:
	 */

	/**
	 * USED by XML interface ONLY !!! Renders main currency conversion rates
	 *
	 * @param  string           $value
	 * @param  ParamsInterface  $params
	 * @return string                    HTML to display
	 */
	public function renderMainRate( /** @noinspection PhpUnusedParameterInspection */ $value, &$params ) {
		$textCurrency				=	$params->get( 'currency_code', 'USD' );
		$textSecondaryCurrency		=	$params->get( 'secondary_currency_code' );

		$price						=	1.0;
		// $priceText					=	$this->renderPrice( $price, $textCurrency, true );

		if ( $textSecondaryCurrency && ( $textSecondaryCurrency != $textCurrency ) ) {
			$_CBPAY_CURRENCIES		=&	cbpaidApp::getCurrenciesConverter();
			$secondaryPrice			=	$_CBPAY_CURRENCIES->convertCurrency( $textCurrency, $textSecondaryCurrency, $price );
			if ( $secondaryPrice !== null ) {
				// we do not want roundings here:
				// $secondaryPriceText	=	$this->renderPrice( $secondaryPrice, $textSecondaryCurrency, true );
				// return $secondaryPriceText . ' / ' . $priceText;
				return sprintf( '%s %0.2f / %s %0.2f', $textSecondaryCurrency, $secondaryPrice, $textCurrency, $price );
			} else {
				$error				=	$_CBPAY_CURRENCIES->getError();
				return '<font style="color:red">' . $error . '</font>';
			}
		}
		return null;
	}
}
