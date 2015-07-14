<?php
/**
 * @version $Id: cbpaidGatewaySelectorButton.php 1546 2012-12-02 23:16:25Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Post-type Form Button-type payment method selection
 */
class cbpaidGatewaySelectorButton extends cbpaidGatewaySelector {
	/**
	 * Url to post to (not htmlspecialchared)
	 * @var string
	 */
	public $pspUrl;
	/**
	 * hidden-fields request parameters (not htmlspecialchared)
	 * @var array of string
	 */
	public $requestParams;
	/**
	 * Custom image for the button (src url) (not htmlspecialchared)
	 * @var string
	 */
	public $customImage;
	/**
	 * Alternate text for customIamge (not htmlspecialchared)
	 *
	 * @var string
	 */
	public $altText;
	/**
	 * Title text for customIamge (not htmlspecialchared)
	 *
	 * @var string
	 */
	public $titleText;
	/**
	 * Unique button id (not htmlspecialchared)
	 *
	 * @var string
	 */
	public $butId;
	/**
	 * Creates a cbpaidGatewaySelectorButton object
	 * (all params should be not htmlspecialchared)
	 *
	 * @param  int|null  $gatewayId
	 * @param  string    $subMethod
	 * @param  string    $paymentType
	 * @param  string    $pspUrl
	 * @param  array     $requestParams
	 * @param  string    $customImage
	 * @param  string    $altText
	 * @param  string    $titleText
	 * @param  string    $payNameForCssClass
	 * @param  string    $butId
	 * @return cbpaidGatewaySelectorButton
	 */
	public static function getPaymentButton( $gatewayId, $subMethod, $paymentType, $pspUrl, $requestParams, $customImage, $altText, $titleText, $payNameForCssClass, $butId ) {
		$ps							=	new self();
		$ps->gatewayId				=	$gatewayId;
		$ps->subMethod				=	$subMethod;
		$ps->paymentType			=	$paymentType;
		$ps->pspUrl					=	$pspUrl;
		$ps->requestParams			=	$requestParams;
		$ps->customImage			=	$customImage;
		$ps->altText				=	$altText;
		$ps->titleText				=	$titleText;
		$ps->payNameForCssClass		=	$payNameForCssClass;
		$ps->butId					=	$butId;
		return $ps;
	}
	/**
	 * Creates a cbpaidGatewaySelectorButton object for just changing the currency after asking for confirmation to change currency
	 *
	 * @param  cbpaidPaymentBasket  $paymentBasket
	 * @param  string               $newCurrency
	 * @param  string               $customImage
	 * @param  string               $altText
	 * @param  string               $titleText
	 * @param  string               $payNameForCssClass
	 * @param  string               $butId
	 * @return cbpaidGatewaySelectorButton
	 */
	public static function getChangeOfCurrencyButton( $paymentBasket, $newCurrency, $customImage, $altText, $titleText, $payNameForCssClass, $butId ) {
		list( $pspUrl, $requestParams, $currencyInputName )		=	cbpaidControllerPaychoices::getCurrencyChangeFormParams( $paymentBasket );
		$requestParams[$currencyInputName]						=	$newCurrency;
		$requestParams[cbSpoofField()]							=	cbSpoofString( null, 'plugin' );
		return self::getPaymentButton( null, null, null, cbSef( $pspUrl, false ), $requestParams, $customImage, $altText, $titleText, $payNameForCssClass, $butId );
	}
}
