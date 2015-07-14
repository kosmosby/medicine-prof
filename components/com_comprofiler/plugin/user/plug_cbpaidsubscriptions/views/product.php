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

use CBLib\Registry\ParamsInterface;
use CBLib\Registry\Registry;

/**
 * GENERIC VIEW: Product view class
 *
 */
class cbpaidProductView extends cbpaidTemplateHandler {
	/** product
	 * @access protected
	 * @var cbpaidProduct */
	public $_model;
	public $planstitle;
	public $_selectionId;
	public $_selectionName;
	public $_selectionValue;
	public $_insertBeforePrice;
	public $_insertAfterDescription;
	public $cssclass;
	public $cssid;
	public $description;
	public $exclusive;
	public $_control;
	public $extrainputs;
	public $_checked;
	public $_tick;
	public $_labelledName;

	/**
	 * Constructor
	 *
	 * @param  cbpaidProduct  $model
	 */
	public function __construct( &$model ) {
		parent::__construct();
		$this->_model	=&	$model;
	}
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
	 * @param  boolean|int  $selected            TRUE if the item is selected, FALSE if not selected but radio/checkbox is visible, (int) 2 if selected but hidden input
	 * @param  string   $reason                  Payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @param  boolean  $displayDescription      TRUE: display description also
	 * @param  boolean  $displayPrice            TRUE: display price/price selection also
	 * @param  int      $user_id                 The user id for whom this plan is proposed
	 * @return string
	 */
	public function drawProduct( $plansTitle, $selectionId, $selectionName, $selectionValue, $insertBeforePrice, $insertAfterDescription, $selected, /** @noinspection PhpUnusedParameterInspection */ $reason, $displayDescription, /** @noinspection PhpUnusedParameterInspection */ $displayPrice, $user_id ) {
		global $_CB_framework;

		$this->planstitle				=	$plansTitle;
		$this->_selectionId				=	( $selectionId			?	$this->_getPagingParamName( $selectionId )						:	null );
		$this->_selectionName			=	( $selectionName		?	$this->_getPagingParamName( $selectionName . '[selected][]' )	:	null );
		$this->_selectionValue			=	$selectionValue;
		$this->_insertBeforePrice		=	$insertBeforePrice;
		$this->_insertAfterDescription	=	$insertAfterDescription;
		$this->cssclass					=	trim( $this->_model->get( 'cssclass' ) );
		$this->cssid					=	'cbregProduct_' . $this->_model->get( 'id' );
		$this->description				=	( $displayDescription	?	$this->_model->getPersonalized( 'description', $user_id, true )	:	null );
		$this->exclusive				=	$this->_model->get( 'exclusive' );

		$hiddenSelected					=	( $selected === 2 );
		if ( $hiddenSelected ) {
			$selected					=	false;
		}
		$required						=	( $this->exclusive && ( $_CB_framework->getUi() == 1 ) && ( $this->_model->get( 'parent' ) == 0 ) && ! $hiddenSelected );

		if ( ( $this->_model->get( 'hidechildren' ) == 1 ) && ! $hiddenSelected ) {
			$this->cssclass				.=	( $this->cssclass ? ' ' : '' ) . 'cbregDoHideChildren';
		}

		if ( $hiddenSelected ) {
			$this->_control				=	'hidden';
		} elseif ( $this->exclusive ) {
			$this->_control				=	'radio';
		} else {
			$this->_control				=	'checkbox';
		}
		if ( $required && ! $hiddenSelected ) {
			$this->extrainputs			=	'mosReq="1" mosLabel="' . htmlspecialchars( CBPTXT::T( $this->planstitle ) ) . '" class="required" ';
		} else {
			$this->extrainputs			=	'';
		}
		if ( $this->cssclass ) {
			$this->cssclass				=	' ' . trim( $this->cssclass );
		}
		if ( $selected ) {
			$this->_checked				=	'checked="checked" ';
		} else {
			$this->_checked				=	'';
		}
		if ( $selectionId ) {
			$this->_tick				=	'<input type="' . $this->_control . '" name="' . $this->_selectionName . '" id="' . $this->_selectionId . '" value="' . $this->_selectionValue . '" ' . $this->_checked . $this->extrainputs . '/>';
		} else {
			$this->_tick				=	'&nbsp;';
		}
		$htmlNameTranslated				=	$this->_model->getPersonalized( 'name', $user_id, true );
		if ( $selectionId && ! $hiddenSelected ) {
			$this->_labelledName		=	'<label for="' . $this->_selectionId . '">' . $htmlNameTranslated . '</label>';
		} else {
			$this->_labelledName		=	'<label>' . $htmlNameTranslated . '</label>';
		}
		return 'override cbpaidProductView::draw for product item_type "' . htmlspecialchars( $this->_model->get( 'item_type' ) ) . '"';
	}
	/**
	 * Evaluates $postdata which is the $_POST array of the form submission of the cbpaidProductView::draw() form,
	 * and returns the filtered unescaped options.
	 *
	 * @param  string   $selectionId             html input tag attribute id=''    field for the input
	 * @param  string   $selectionName           html input tag attribute name=''  field for the input
	 * @param  string   $selectionValue          html input tag attribute value='' field for the input
	 * @param  string   $reason                  Payment reason: 'N'=new subscription (default), 'R'=renewal, 'U'=update
	 * @return ParamsInterface                   Product's selected options
	 */
	public function getOptions( /** @noinspection PhpUnusedParameterInspection */ $selectionId, $selectionName, $selectionValue, $reason ) {
		return new Registry( '' );
	}
}	// class cbpaidProductView
