<?php
/**
* @version $Id: $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Template for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Registry\ParamsInterface;
use CBLib\Registry\Registry;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/** @noinspection PhpIncludeInspection */
include_once cbpaidApp::getAbsoluteFilePath( 'views/product.php' );

/**
 * VIEW: Donation product view class
 *
 */
class cbpaidProductdonationView extends cbpaidProductView {
	public $periodPrice;
	public $currency;
	public $fixedchoices;
	public $defaultchoice;
	public $donateamounts;
	public $_donselName;
	public $_donvalName;

	/**
	 * Returns the version of the implemented View
	 *
	 * @return int
	 */
	public function version( ) {
		return 1;
	}

	/**
	 * Draws the subscription for registrations and profile views
	 *
	 * @param  string   $plansTitle              Title field of the plans (for validation texts)
	 * @param  string   $selectionId             html input tag attribute id=''    field for the input
	 * @param  string   $selectionName           html input tag attribute name=''  field for the input
	 * @param  string   $selectionValue          html input tag attribute value='' field for the input
	 * @param  string   $insertBeforePrice       HTML text to insert after description of this item but before price
	 * @param  string   $insertAfterDescription  HTML text to insert after this item as sub-items
	 * @param  boolean  $selected                TRUE if the item is selected
	 * @param  string   $reason                  Payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  boolean  $displayDescription      TRUE: display description also
	 * @param  boolean  $displayPrice            TRUE: display price/price selection also
	 * @param  int      $user_id                 User-id for whom the plan is drawn
	 * @return string                            HTML
	 */
	public function drawProduct( $plansTitle, $selectionId, $selectionName, $selectionValue, $insertBeforePrice, $insertAfterDescription, $selected, $reason, $displayDescription, $displayPrice, $user_id ) {
		parent::drawProduct( $plansTitle, $selectionId, $selectionName, $selectionValue, $insertBeforePrice, $insertAfterDescription, $selected, $reason, $displayDescription, $displayPrice, $user_id );

		$this->periodPrice				=	null;
		if ( $displayPrice ) {
			$this->_setOptionNames( $selectionId, $selectionName, true );
	
			$this->currency				=	$this->_model->get( 'currency' );
			if ( ! $this->currency ) {
				$this->currency			=	cbpaidApp::settingsParams()->get( 'currency_code' );
			}
			$cbpaidMoney				=	cbpaidMoney::getInstance();
			$currencySymbol				=	$cbpaidMoney->renderCurrencySymbol( $this->currency, true );
			if ( $cbpaidMoney->currencyAfterOrNotBefore() ) {
				$currencySymbolAfter	=	' ' . $currencySymbol;
				$currencySymbolBefore	=	'';
			} else {
				$currencySymbolAfter	=	'';
				$currencySymbolBefore	=	$currencySymbol . ' ';
			}
	
			$this->fixedchoices			=	$this->_model->getParam( 'fixedchoices', 1 );
			$this->defaultchoice		=	$this->_model->getParam( 'defaultchoice', '' );
			$this->donateamounts		=	explode( ',', $this->_model->getParam( 'donateamount', '' ) );
			$this->_trimArray( $this->donateamounts );
			if ( ( $this->fixedchoices == 2 ) && ( count( $this->donateamounts ) == 1 ) ) {
				$this->periodPrice		=	'<input type="hidden" name="' . $this->_donselName . '" value="' . $this->donateamounts[0] . '" />'
										.	'<span class="cbregDonationRate">'
										.	$this->_model->displayPeriodPrice( $reason, 0, null, null, true )
										.	'</span>';
			} elseif ( $this->fixedchoices > 1 ) {
				$options				=	array();
				$options[]				=	moscomprofilerHTML::makeOption( '', htmlspecialchars( CBPTXT::T("--- Select amount ---") ) );
				$this->_valuesToOptions( $options, $this->donateamounts, $this->currency );
				if ( $this->fixedchoices == 3 ) {
					$options[]			=	moscomprofilerHTML::makeOption( '0', htmlspecialchars( CBPTXT::T("Other...") ) );
				}
				$this->periodPrice		=	moscomprofilerHTML::selectList( $options,
																 $this->_donselName,
																 'class="inputbox cbregDonationSelector"'		// id="' . $selectionId . 'donsel' . '"'
/*
																.	' onclick="'
																. 'if (this.options[this.selectedIndex].value==\'\' || this.options[this.selectedIndex].value==\'\') { '
																.		'document.getElementById(\'' . $this->_selectionId . '\').checked=false; '
																. '} else { '
																.		'document.getElementById(\'' . $this->_selectionId . '\').checked=true; '
																. '} '
											. ( ( $this->fixedchoices == 3 ) ?
																 'if (this.options[this.selectedIndex].value==\'0\') { '
																.		'document.getElementById(\'' . $selectionId . 'donspan' . '\').style.display=\'\' ; '
																.		'document.getElementById(\'' . $selectionId . 'donval' . '\').focus(); '
																. '} else { '
																.		'document.getElementById(\'' . $selectionId . 'donspan' . '\').style.display=\'none\'; '
																. '} '
											: '' )
																. 'return true;"'
*/
																,
																 'value',
																 'text',
																 $this->defaultchoice, 2, false );
			}
			if ( $this->fixedchoices == 3 ) {
				$this->periodPrice		=	'<span class="cbregDonationSelect">' . $this->periodPrice . '</span>';
			}
			$hiddenStyle				=	'';
			if ( $this->fixedchoices != 2 ) {
				if ( ( $this->fixedchoices == 3 ) && ( in_array( $this->defaultchoice, $this->donateamounts ) || ( $this->defaultchoice == '' ) ) ) {
					$hiddenStyle		=	' style="display:none;"';
					$defaultDonateValue	=	'';
				} else {
					$defaultDonateValue	=	$this->defaultchoice;
				}
			/*	if ( ( $this->fixedchoices == 1 ) {
					$defaultDonateValue	=	$this->defaultchoice;
				}
			*/
				$this->periodPrice		.=	'<span class="cbregDonationValue" id="' . $selectionId . 'donspan' . '"'. $hiddenStyle .'>'
										.	$currencySymbolBefore
										.	'<input type="text" size="12" name="' . $this->_donvalName . '" id="' . $selectionId . 'donval'
										.		'" class="inputbox cbregDonationFreeValue" value="' . htmlspecialchars( $defaultDonateValue ) . '"'
/*
										.		' onblur="if (this.value!=\'\' && this.value!=\'0\' && this.value!=\'0.00\') { document.getElementById(\'' . $this->_selectionId
										.		'\').checked=true; } else { document.getElementById(\'' . $this->_selectionId
										.		'\').checked=false; } return true;"'
*/
										.		' />'
										.	$currencySymbolAfter
										.	'</span>';
			}
		}

		return $this->display();
	}

	/**
	 * Evaluates $postdata which is the $_POST array of the form submission of the cbpaidProductView::draw() form,
	 * and returns the filtered unescaped options.
	 *
	 * @param  string           $selectionId     html input tag attribute id=''    field for the input
	 * @param  string           $selectionName   html input tag attribute name=''  field for the input
	 * @param  string           $selectionValue  html input tag attribute value='' field for the input
	 * @param  string           $reason          Payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @return ParamsInterface                   Product's selected options
	 */
	public function getOptions( $selectionId, $selectionName, $selectionValue, $reason ) {
		$optionParams					=	new Registry( '' );

		$this->_setOptionNames( $selectionId, $selectionName, false );
		$selectedvalue					=	(float) $this->_getReqParam( $this->_donselName );
		$donval							=	abs( (float) $this->_getReqParam( $this->_donvalName ) );

		$this->fixedchoices				=	$this->_model->getParam( 'fixedchoices', 1 );
		$this->defaultchoice			=	$this->_model->getParam( 'defaultchoice', '' );
		$minimumDonation				=	(float) $this->_model->getParam( 'minimumdonation', '' );
		$maximumDonation				=	(float) $this->_model->getParam( 'maximumdonation', '' );
		$this->donateamounts			=	explode( ',', $this->_model->getParam( 'donateamount', '' ) );
		$this->_trimArray( $this->donateamounts );
		$amount							=	0;
		if ( ( $this->fixedchoices > 1 ) && in_array( $selectedvalue, $this->donateamounts ) ) {
			$amount						=	$selectedvalue;
		} elseif ( ( $selectedvalue == '' ) && ( $this->fixedchoices != 2 ) ) {
			if ( $donval < $minimumDonation ) {
				return CBPTXT::T("Amount too small") . '.';
			} elseif ( ( $maximumDonation != 0 ) && ( $donval > $maximumDonation ) ) {
				return CBPTXT::T("Amount too large") . '.';
			}
			$amount						=	$donval;
		}
		if ( $amount == 0 ) {
			return CBPTXT::T("Amount not allowed") . '.';
		}
		$optionParams->set( 'amount', $amount );
		return $optionParams;
	}

	/**
	 * Sets ->donselName and ->donvalName
	 *
	 * @access   private
	 *
	 * @param  string   $selectionId             html input tag attribute id=''    field for the input
	 * @param  string   $selectionName           html input tag attribute name='cbregSubscribed'  field for the input
	 * @param  boolean  $translate               TRUE: translate names to plugin name, FALSE: let them.
	 */
	protected function _setOptionNames( $selectionId, $selectionName, $translate ) {
		$this->_donselName				=	$selectionName . '[donate][' . $selectionId . '][donsel]';
		$this->_donvalName				=	$selectionName . '[donate][' . $selectionId . '][donval]';
		if ( $translate ) {
			$this->_donselName			=	$this->_getPagingParamName( $this->_donselName );
			$this->_donvalName			=	$this->_getPagingParamName( $this->_donvalName );
		}
	}
	/**
	 * Trims each string element of the array
	 *
	 * @param  array  $arr
	 */
	protected function _trimArray( &$arr ) {
		for ( $i = 0, $n = count( $arr ); $i < $n; $i++ ) {
			$arr[$i]	=	trim( $arr[$i] );
		}
	}

	/**
	 * converts each value to an option element of the array
	 *
	 * @param  array   $options
	 * @param  array   $arr
	 * @param  string  $currency
	 */
	protected function _valuesToOptions( &$options, &$arr, $currency ) {
		$cbpaidMoney		=&	cbpaidMoney::getInstance();
		for ( $i = 0, $n = count( $arr ); $i < $n; $i++ ) {
			$displayAmount	=	$cbpaidMoney->renderPrice( $arr[$i], $currency, true );
			$options[]		=	moscomprofilerHTML::makeOption( $arr[$i], $displayAmount );
		}
	}
}	// class cbpaidProductusersubscriptionView
