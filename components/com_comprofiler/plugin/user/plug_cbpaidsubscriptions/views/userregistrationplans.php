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

use CB\Database\Table\TabTable;

/**
 * VIEW: User's subscriptions (somethings) view class
 *
 */
class cbpaiduserregistrationplansView extends cbpaidTemplateHandler {
	public $plansTitle;
	public $htmlPlans;
	public $cssclass;
	/**
	 * @var TabTable
	 */
	public $_model;
	/**
	 * Returns the version of the implemented View
	 *
	 * @return int
	 */
	public function version( ) {
		return 1;
	}
	/**
	 * Draws the user profile tab "Subscriptions" (if enabled, user is the profile owner, and something to display.
	 * 
	 * @param  string      $plansTitle
	 * @param  string      $htmlPlans
	 * @return array|null
	 */
	public function drawRegistrationPlans( $plansTitle, $htmlPlans ) {
		$this->plansTitle				=	$plansTitle;
		$this->htmlPlans				=	$htmlPlans;

		$htmlValue						=	$this->display();
		$description					=	null;
		$uniqueId						=	'cbregplans';
		$displayOnTwoLines				=	( cbpaidApp::settingsParams()->get( 'regDisplayLines', 2 ) == 2 );

		$tab							=	$this->_model;
		if ( $htmlValue ) {
			return array( cbTabs::_createPseudoField( $tab, $this->plansTitle, $htmlValue, $description, $uniqueId, $displayOnTwoLines ) );
		} else {
			return null;
		}
	}

}

