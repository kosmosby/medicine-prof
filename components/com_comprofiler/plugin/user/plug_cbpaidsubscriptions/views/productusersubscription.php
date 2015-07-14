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

/** @noinspection PhpIncludeInspection */
include_once cbpaidApp::getAbsoluteFilePath( 'views/product.php' );


/**
 * VIEW: User subscription view class
 *
 */
class cbpaidProductusersubscriptionView extends cbpaidProductView {
	public $periodPrice;

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
	 * @param  int      $user_id                 User id
	 * @return string
	 */
	public function drawProduct( $plansTitle, $selectionId, $selectionName, $selectionValue, $insertBeforePrice, $insertAfterDescription, $selected, $reason, $displayDescription, $displayPrice, $user_id ) {
		parent::drawProduct( $plansTitle, $selectionId, $selectionName, $selectionValue, $insertBeforePrice, $insertAfterDescription, $selected, $reason, $displayDescription, $displayPrice, $user_id );

		if ( $displayPrice ) {
			$this->periodPrice			=	$this->_model->displayPeriodPrice( $reason, 0, null, null, true );
			if ( strtolower( $this->_model->get( 'pricedisplay' ) ) == '[automatic]' ) {
				$this->periodPrice		.=	'.';
			}
		} else {
			$this->periodPrice			=	null;
		}

		return $this->display();
	}
}	// class cbpaidProductusersubscriptionView
