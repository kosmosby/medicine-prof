<?php
/**
* @version $Id: $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Template for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


/**
 * VIEW: Basket view class
 *
 */
class cbpaidBasketView extends cbpaidTemplateHandler  {
	/** basket
	 * @access private
	 * @var cbpaidPaymentBasket */
	public $summaryText;
	public $captionText;
	public $displayColumns;
	public $totalizerColumns;
	public $itemsLinesCols;
	public $totalizerLinesCols;

	public $couponLabelText;
	public $couponAddButtonText;
	public $couponDescription;
	public $couponDescriptionHints;
	public $couponRemoveButtonText;
	public $couponsUsed;
	
	public $allColumns				=	array();
	public $columnsFormats			=	array();
	public $footerFormats			=	array();

	// For radios:
	public $gatewayId;
	public $radioValue;
	public $cardtypes;
	public $brandLabelHtml;
	public $brandDescriptionHtml;
	public $payNameForCssClass;
	public $selected;
	
	public $formTargetUrl;
	public $txtHiddenInputs;
	public $buttonImageOrText;
	public $altText;
	public $titleText;
	public $butId;
	/**
	 * Returns the version of the implemented View
	 *
	 * @return int
	 */
	public function version( ) {
		return 1;
	}
	/**
	 * Draws the basket
	 *
	 * @param  string               $summaryText        Text for the table summary
	 * @param  string               $captionText        Text for the table caption
	 * @param  array                $displayColumns     Keyed array columnName => columnTitle
	 * @param  array                $totalizerColumns   Keyed array columnName => columnTitle
	 * @param  array                $itemsLinesCols     Array of keyed arrays: lineIdx => columnName => HTMLcellContent
	 * @param  array                $totalizerLinesCols Array of keyed arrays: lineIdx => columnName => HTMLcellContent
	 * @return string
	 */
	public function drawBasket( $summaryText, $captionText, $displayColumns, $totalizerColumns, $itemsLinesCols, $totalizerLinesCols ) {
		$this->summaryText			=	$summaryText;
		$this->captionText			=	$captionText;
		$this->displayColumns		=	$displayColumns;
		$this->totalizerColumns		=	$totalizerColumns;
		$this->itemsLinesCols		=	$itemsLinesCols;
		$this->totalizerLinesCols	=	$totalizerLinesCols;

		$this->_computeSingleTablePlacement();

		return $this->display();
	}
	/**
	 * Draws the coupon form
	 *
	 * @param  string               $couponLabelText           Text for the coupon input field label
	 * @param  string               $couponAddButtonText       Text for the Add coupon button
	 * @param  string               $couponDescription         Text for the Add coupon button description
	 * @param  array                $html_couponsDescriptions  HTML texts for coupons descriptions by possible coupons (hints)
	 * @param  string               $couponRemoveButtonText    Text for the Remove coupon button
	 * @param  array                $couponsUsed               Coupons used in basket
	 * @return string
	 */
	public function drawCouponForm( $couponLabelText, $couponAddButtonText, $couponDescription, $html_couponsDescriptions, $couponRemoveButtonText, $couponsUsed ) {
		$this->couponLabelText			=	$couponLabelText;
		$this->couponAddButtonText		=	$couponAddButtonText;
		$this->couponRemoveButtonText	=	$couponRemoveButtonText;
		$this->couponDescription		=	$couponDescription;
		$this->couponDescriptionHints	=	$html_couponsDescriptions;
		$this->couponsUsed				=	$couponsUsed;

		return $this->display( 'coupon' );
	}
	/**
	 * Draws a line with a radio selection for a payment method
	 * 
	 * @param  cbpaidGatewaySelectorRadio  $radioPaymentSelector
	 * @param  boolean $selected
	 * @return string
	 */
	public function drawPaymentRadio( $radioPaymentSelector, $selected ) {
		$this->gatewayId				=	$radioPaymentSelector->gatewayId;
		$this->radioValue				=	$radioPaymentSelector->radioValue();
		$this->cardtypes				=	$radioPaymentSelector->cardTypes;
		$this->brandLabelHtml			=	$radioPaymentSelector->brandLabelHtml;
		$this->brandDescriptionHtml		=	$radioPaymentSelector->brandDescriptionHtml;
		$this->altText					=	$radioPaymentSelector->altText;
		$this->payNameForCssClass		=	$radioPaymentSelector->payNameForCssClass;
		$this->selected					=	$selected;
		
		return $this->display( 'payradio' );
	}
	/**
	 * Draws a payment button
	 *
	 * @param  cbpaidGatewaySelectorButton  $paymentButton
	 * @return string
	 */
	public function drawPaymentButton( $paymentButton ) {
		$this->formTargetUrl			=	$paymentButton->pspUrl;
		$this->txtHiddenInputs			=	$this->_toHiddenInputsTxt( $paymentButton->requestParams );
		$this->buttonImageOrText		=	$paymentButton->customImage;
		$this->altText					=	$paymentButton->altText;
		$this->titleText				=	$paymentButton->titleText;
		$this->payNameForCssClass		=	$paymentButton->payNameForCssClass;
		$this->butId					=	$paymentButton->butId;

		return $this->display( 'paybutton' );
	}
	/**
	 * Computes columns in a single table for header, items and totalizers:
	 *
	 * returns:
	 * $this->allColumns      array( key => caption )
	 * $this->columnsFormats  array( array( key, caption, colspan ) 
	 * $this->footerFormats   array( array( key, caption, colspan ) 
	 */
	protected function _computeSingleTablePlacement( ) {
		$this->allColumns							=	$this->_orderedUnionOfOrderedArrays( $this->displayColumns, $this->totalizerColumns );
		$ci											=	null;
		$fi											=	null;
		$clf										=	0;
		$flf										=	0;
		foreach ( $this->allColumns as $k => $caption ) {
			if ( isset( $this->displayColumns[$k] ) ) {
				$ci									=	$k;
				$this->columnsFormats[$ci]			=	array( $k, $caption, $clf );	// last entry is colspan and will be automatically incremented below
				$clf								=	0;
			}
			if ( isset( $this->totalizerColumns[$k] ) ) {
				$fi									=	$k;
				$this->footerFormats[$fi]			=	array( $k, $caption, $flf );
				$flf								=	0;
			}
			$ci ? ++$this->columnsFormats[$ci][2] : ++$clf;
			$fi ? ++$this->footerFormats[$fi][2] : ++$flf;
		}
	}
	/**
	 * Returns an ordered union of two arrays $a1 and $a2, respecting ordering of each array
	 *
	 * @param  array  $a1
	 * @param  array  $a2
	 * @return array
	 */
	protected function _orderedUnionOfOrderedArrays( $a1, $a2 ) {
		$k1						=	array_keys( $a1 );
		$k2						=	array_keys( $a2 );
		$i1						=	0;
		$i2						=	0;
		$a12					=	array();
		while ( isset( $k1[$i1] ) && isset( $k2[$i2]) ) {
			if ( $k1[$i1] === $k2[$i2] ) {
				$a12[$k1[$i1]]	=	$a1[$k1[$i1]];
				++$i1;
				++$i2;
			} elseif ( in_array( $k1[$i1], $k2 ) ) {
				// it's comming up in $k2, so take from k2 until there:
				$a12[$k2[$i2]]	=	$a2[$k2[$i2++]];
			} elseif ( in_array( $k2[$i2], $k1 ) ) {
				// it's comming up in $k1, so take from k1 until there:
				$a12[$k1[$i1]]	=	$a1[$k1[$i1++]];
			} else {
				// ok, none of the array keys are present in the other array, give priority to k1:
				$a12[$k1[$i1]]	=	$a1[$k1[$i1++]];
			}
		}
		// now there is nothing more in common, copy each, with priority to k1:
		while ( isset( $k1[$i1] ) ) {
			$a12[$k1[$i1]]		=	$a1[$k1[$i1++]];
		}
		while ( isset( $k2[$i2] ) ) {
			$a12[$k2[$i2]]		=	$a2[$k2[$i2++]];
		}
		return $a12;
	}
	/**
	 * Returns HTML code for hidden input fields for payment form for the gateway
	 *
	 * @param  array         $varsArray            Keyed array of GET variables for the Paypal payment link
	 * @return string                              HTML code for hidden input fields for payment form for paypal
	 */
	protected function _toHiddenInputsTxt( $varsArray ) {
		$ret				=	'';
		foreach ( $varsArray as $k => $v ) {
			$ret			.=	'<input type="hidden" name="' . htmlspecialchars( $k ) . '" value="' . htmlspecialchars( $v ) . "\" />\n";
		}
		return $ret;
	}
}	// class cbpaidBasketView
