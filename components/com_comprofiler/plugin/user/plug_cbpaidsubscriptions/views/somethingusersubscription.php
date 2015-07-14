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
include_once cbpaidApp::getAbsoluteFilePath( 'views/something.php' );

/**
 * VIEW: Subscriptions view class
 *
 */
class cbpaidsomethingusersubscriptionView extends cbpaidSomethingViewView {
	/**
	 * Draws the subscription for registrations and profile views
	 *
	 * @param  string                       $insertBeforeValidity    HTML text to insert after this item description but before validity
	 * @param  string                       $insertAfterDescription  HTML text to insert after this item as sub-items
	 * @param  boolean                      $showStateCheckMark      If check/cross-marks for ->active state should be drawn
	 * @return string
	 */
	public function drawSomething( $insertBeforeValidity, $insertAfterDescription, $showStateCheckMark = true ) {
		parent::drawSomething( $insertBeforeValidity, $insertAfterDescription, $showStateCheckMark );
		return $this->display();
	}
}
