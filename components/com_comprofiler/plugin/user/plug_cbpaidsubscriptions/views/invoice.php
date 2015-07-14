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
 * VIEW: single Invoices view class
 *
 */
class cbpaidInvoiceView extends cbpaidTemplateHandler  {
	public $user;
	public $extraStrings;
	public $header;
	public $invoiceHeaderAlign;
	public $footer;
	public $invoiceFooterAlign;
	public $invoicerAddress;
	public $invoiceDate;
	public $invoiceNumberTitle;
	public $invoiceNumber;
	public $invoiceNumberHtml;
	public $invoiceConditions;
	public $paymentType;
	public $address;
	public $basketHtml;
	public $displayButtons;
	/**
	 * Returns the version of the implemented View
	 *
	 * @return int
	 */
	public function version( ) {
		return 1;
	}
	/**
	 * Draws the invoice
	 *
	 * @param  UserTable  $user
	 * @param  array      $extraStrings
	 * @param  boolean    $displayButtons   Displays the PRINT and CLOSE buttons
	 * @return string                       HTML
	 */
	public function drawInvoice( $user, $extraStrings, $displayButtons = true ) {
		/** @var $invoice cbpaidPaymentBasket */
		$invoice					=	$this->_model;
		$this->user					=	$user;
		$this->extraStrings			=	$extraStrings;
		$this->displayButtons		=	$displayButtons;

		$params						=	cbpaidApp::settingsParams();

		$invoiceHeader				=	cbReplaceVars( CBPTXT::T( trim( $params->get( 'invoice_header', '' ) ) ), $user, true, false, $extraStrings, false );
		$invoiceHeaderAltText		=	cbReplaceVars( CBPTXT::T( trim( $params->get( 'invoice_header_alt_text' ) ) ), $user, false, false, $extraStrings, false );
		$this->invoiceHeaderAlign	=	trim( $params->get( 'invoice_header_align', '' ) );
		$invoiceFooter				=	cbReplaceVars( CBPTXT::T( trim( $params->get( 'invoice_footer', '' ) ) ), $user, true, false, $extraStrings, false );
		$invoiceFooterAltText		=	cbReplaceVars( CBPTXT::T( trim( $params->get( 'invoice_footer_alt_text' ) ) ), $user, false, false, $extraStrings, false );
		$this->invoiceFooterAlign	=	trim( $params->get( 'invoice_footer_align', '' ) );
		$this->invoicerAddress		=	cbReplaceVars( CBPTXT::T( trim( $params->get( 'invoicer_address', '' ) ) ), $user, true, false, $extraStrings, false );
		$this->invoiceConditions	=	cbReplaceVars( CBPTXT::T( trim( $params->get( 'invoice_conditions', '' ) ) ), $user, true, false, $extraStrings, false );

		if ( $invoiceHeader ) {
			if ( preg_match( "/(\\.jpg|\\.png|\\.gif)$/i", $invoiceHeader ) ) {
				$this->header		=	'<img src="' . htmlspecialchars( $invoiceHeader ) . '" alt="' . htmlspecialchars( CBPTXT::T( $invoiceHeaderAltText ) ) . "\" />\n";
			} else {
				$this->header		=	$invoiceHeader;
			}
		} else {
			$this->header			=	null;
		}
		if ( $invoiceFooter ) {
			if ( preg_match( "/(\\.jpg|\\.png|\\.gif)$/i", $invoiceFooter ) ) {
				$this->footer		=	'<img src="' . htmlspecialchars( $invoiceFooter ) . '" alt="' . htmlspecialchars( CBPTXT::T( $invoiceFooterAltText ) ) . "\" />\n";
			} else {
				$this->footer		=	$invoiceFooter;
			}
		} else {
			$this->footer			=	null;
		}

		$this->invoiceDate			=	cbFormatDate( $invoice->time_initiated, 1, false );
		$this->invoiceNumberTitle	=	CBPTXT::Th( $invoice->getInvoiceTitleFormat() );
		$this->invoiceNumber		=	$invoice->invoice;
		$this->invoiceNumberHtml	=	'<span class="cbregInvTitle">'
									.	str_replace( ' ', '&nbsp;', str_replace( '[INVOICENUMBER]', '<span class="cbregInvField">' . $invoice->invoice . '</span>', $this->invoiceNumberTitle ) )
									.	'</span>';
		
		$this->paymentType			=	CBPTXT::T( $invoice->payment_type );
/*
		$this->address				=	$invoice->payer_business_name
									.	"\n"
									.	$invoice->first_name . ' ' . $invoice->last_name
									.	"\n"
									.	$invoice->address_street
									.	"\n"
									.	$invoice->address_city . ( $invoice->address_state ? ', ' . $invoice->address_state : '' )
									.	"\n"
									.	$invoice->address_zip
									.	"\n"
									.	$invoice->address_country
									;
*/
		$this->address				=	cbReplaceVars( CBPTXT::T( trim( $params->get( 'invoice_address_format' ) ) ), $user, false, false, $extraStrings, false );
		if ( $this->address == strip_tags( $this->address ) ) {
			$this->address			=	nl2br( $this->address );
		}
		$this->basketHtml			=	$invoice->displayBasket( "Invoice details", '', 'invoice' );		// it's translated, this is for translations grabber: CBPTxt::Th("Invoice details");

		return $this->display();
	}
}	// class cbpaidInvoiceView
