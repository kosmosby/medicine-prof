<?php
/**
 * @version $Id: cbpaidGatewaySelectorRadio.php 1546 2012-12-02 23:16:25Z beat $
 * @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
 * @subpackage Plugin for Paid Subscriptions
 * @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Radio-type payment method selection
 */
class cbpaidGatewaySelectorRadio extends cbpaidGatewaySelector {
	/**
	 * Card types
	 * @var array of string
	 */
	public $cardTypes;
	/**
	 * Main text html for radio label
	 * @var string
	 */
	public $brandLabelHtml;
	/**
	 * Description text html for radio label
	 * @var string
	 */
	public $brandDescriptionHtml;
	/**
	 * Alt text for images
	 * @var string
	 */
	public $altText;
	/**
	 * Creates a cbpaidGatewaySelectorRadio object
	 * (all params should be not htmlspecialchared)
	 *
	 * @param  int     $gatewayId
	 * @param  string  $subMethod
	 * @param  string  $paymentType
	 * @param  array   $cardtypes
	 * @param  string  $brandLabelHtml
	 * @param  string  $brandDescriptionHtml
	 * @param  string  $altText
	 * @param  string  $payNameForCssClass
	 * @return cbpaidGatewaySelectorRadio
	 */
	public static function getPaymentRadio( $gatewayId, $subMethod, $paymentType, $cardtypes, $brandLabelHtml, $brandDescriptionHtml, $altText, $payNameForCssClass = null ) {
		$ps							=	new self();
		$ps->gatewayId				=	$gatewayId;
		$ps->subMethod				=	$subMethod;
		$ps->paymentType			=	$paymentType;
		$ps->cardTypes				=	$cardtypes;
		$ps->brandLabelHtml			=	$brandLabelHtml;
		$ps->brandDescriptionHtml	=	$brandDescriptionHtml;
		$ps->altText				=	$altText;
		$ps->payNameForCssClass		=	$payNameForCssClass;
		return $ps;
	}
	/**
	 * Returns the value for the radio
	 * @return  string  "GatewayId-PaymentType"
	 */
	public function radioValue( ) {
		return $this->gatewayId . '-' . $this->paymentType;
	}
}
