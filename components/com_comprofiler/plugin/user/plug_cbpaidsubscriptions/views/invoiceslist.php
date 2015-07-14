<?php
/**
* @version $Id: $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Template for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


/**
 * VIEW: Invoices list view class
 *
 */
class cbpaidInvoicesListView extends cbpaidTemplateHandler  {
	/** product
	 * @access private
	 * @var cbpaidProduct */
	public $_model;
	public $plansTitle;
	public $show_invoice_numbers;
	public $invoicesListUrl;
	public $invoicesUrls;
	public $user;

	/**
	 * Constructor
	 *
	 * @param  array  $invoices   model
	 */
	public function __construct( &$invoices ) {
		parent::__construct();
		$this->_model					=&	$invoices;
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
	 * Draws the profile view for invoices list
	 *
	 * @param  string     $invoicesListUrl  URL for the link (sefed)
	 * @param  int        $invoicesNumber   array of cbpaidPaymentBasket  of Completed and Pending baskets
	 * @param  UserTable  $user             reflecting the user being displayed (here null)
	 * @param  boolean    $itsmyself        user is logged in user
	 * @param  string     $periodText       if non-empty, text of the period showing invoices
	 * @return string
	 */
	public function drawProfileInvoicesView( $invoicesListUrl, $invoicesNumber, $user, $itsmyself, $periodText ) {
		$this->invoicesListUrl			=	$invoicesListUrl;
		$this->user						=	$user;
		$this->plansTitle				=	$this->_invoicesTitle( $invoicesNumber, $user, $itsmyself, $periodText );
		return $this->display( 'invoiceslistlink' );
	}
	/**
	 * Draws the subscription for registrations and profile views
	 *
	 * @param  int        $invoicesNumber  array of cbpaidPaymentBasket  of Completed and Pending baskets
	 * @param  UserTable  $user            reflecting the user being displayed (here null)
	 * @param  boolean    $itsmyself       user is logged in user
	 * @param  string     $periodText      if non-empty, text of the period showing invoices
	 * @return string
	 */
	public function drawInvoicesList( $invoicesNumber, $user, $itsmyself, $periodText ) {
		$this->user						=	$user;
		$this->plansTitle				=	$this->_invoicesTitle( $invoicesNumber, $user, $itsmyself, $periodText );
		$params							=&	cbpaidApp::settingsParams();
		$this->show_invoice_numbers		=	( $params->get( 'invoice_number_format' ) && $params->get( 'show_invoices' ) );

		$baseClass						=	cbpaidApp::getBaseClass();
		foreach ( $this->_model as $i ) {
			$this->invoicesUrls[$i->id]	=	$baseClass->getInvoiceUrl( $i );
		}
		return $this->display( 'default' );
	}
	/**
	 * Draws the invoice link that opens invoice in a new window
	 *
	 * @param  string  $linkContentHtml
	 * @param  string  $invoiceDetailsUrl
	 * @return string
	 */
	public function drawInvoiceLink( $linkContentHtml, $invoiceDetailsUrl ) {
		$html	=	'<a class="cbregInvoiceLink"  onclick="var cbpaidInvWin = window.open(\'' . $invoiceDetailsUrl 
		 		.	'\', \'cbinvoice\', \'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no\'); cbpaidInvWin.focus(); return false;" target="_blank" href="'
		 		.	$invoiceDetailsUrl . '">'
				.	$linkContentHtml
				.	'</a>'
				;
		//TBD no template for this yet.
		return $html;
	}
	/**
	 * Computes text for the title of the invoices list
	 *
	 * @param  int        $invoicesNumber  array of cbpaidPaymentBasket  of Completed and Pending baskets
	 * @param  UserTable  $user            reflecting the user being displayed (here null)
	 * @param  boolean    $itsmyself       user is logged in user
	 * @param  string     $periodText      if non-empty, text of the period showing invoices
	 * @return string
	 */
	protected function _invoicesTitle( $invoicesNumber, &$user, $itsmyself, $periodText ) {
		global $ueConfig;

		if ( $itsmyself ) {
			if ( $periodText ) {
				$plansTitle =	sprintf( CBPTXT::Th("Your invoices of last %s"), htmlspecialchars( $periodText ) );
			} else {
				if ( $invoicesNumber == 1 ) {
					$plansTitle =	CBPTXT::Th("Your invoice");
				} else {
					$plansTitle =	CBPTXT::Th("Your invoices");
				}
			}
		} else {
			if ( $periodText ) {
				$plansTitle =	sprintf( CBPTXT::Th("%s's invoices of last %s"), getNameFormat( $user->name, $user->username, $ueConfig['name_format'] ), htmlspecialchars( $periodText ) );
			} else {
				$plansTitle =	sprintf( CBPTXT::Th("%s's invoices"), getNameFormat( $user->name, $user->username, $ueConfig['name_format'] ) );
			}
		}
		return $plansTitle;
	}
}
