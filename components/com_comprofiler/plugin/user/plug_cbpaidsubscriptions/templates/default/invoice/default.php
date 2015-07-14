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

$tmplVersion	=	1;	// This is the template version that needs to match
?>


<div class="cbreginvoice">
  <div class="cbreginvoiceBody">
<?php
		// Header:
		if ( $this->header ) {
?>
	<div class="cbreginvoiceHeading"<?php echo ( $this->invoiceHeaderAlign ? ' style="text-align:'. $this->invoiceHeaderAlign . '"' : '' ); ?>><?php
			echo $this->header;
	?></div>
<?php
		}

		// Address of invoicer:
		if ( $this->invoicerAddress ) {
?>
	<div class="cbreginvoicerAddress"><?php
			echo $this->invoicerAddress;
	?></div>
<?php
		}

		// Date:
?>
	<div class="cbregInvoiceDate">
		<span class="cbregInvTitle"><?php
			echo CBPTXT::Th("Date:");
		?>&nbsp;</span>
		<span class="cbregInvField"><?php
			echo $this->invoiceDate;
		?></span>
	</div>
<?php
		// Invoiced user address:
?>
	<div class="cbreginvoicedaddress"><?php
			echo $this->address;
	?></div>
<?php
		// Invoice number:
?>
	<div class="cbregInvoiceNumber">
		<?php
			echo $this->invoiceNumberHtml;
		?>
	</div>
<?php
		// Customer number: not needed

		// Invoiced items:

		echo $this->basketHtml;

		// Payment type:
?>
	<div class="cbregInvoicePaymentType">
		<span class="cbregInvTitle"><?php
			echo CBPTXT::Th("Payment method:");
		?>&nbsp;</span>
		<span class="cbregInvField"><?php
			echo $this->paymentType;
		?></span>
	</div>
<?php
		// Conditions:
		if ( $this->invoiceConditions ) {
?>
	<div class="cbregInvoiceConditions">
		<span class="cbregInvTitle"><?php
			echo CBPTXT::Th("Terms and conditions:");
		?>&nbsp;</span>
		<span class="cbregInvField"><?php
			echo $this->invoiceConditions;
		?></span>
	</div>
<?php
		}
		// Buttons:
		if ( $this->displayButtons ) {
?>
	<div id="cbpaidPrint"><p><a href="javascript:void(window.print())"><?php echo CBPTXT::T("PRINT"); ?></a></p></div>
	<div id="cbpaidClose"><p><a href="javascript:void(window.close())"><?php echo CBPTXT::T("CLOSE"); ?></a></p></div>
<?php
		}
?>
  </div>
<?php
		// Footer and PRINT / CLOSE buttons:
		if ( $this->footer ) {
?>
  <div class="cbreginvoiceFooter"<?php echo ( $this->invoiceFooterAlign ? ' style="text-align:'. $this->invoiceFooterAlign . ';"' : '' ); ?>><div><?php
			echo $this->footer;
	?></div></div>
<?php
		}
?>
</div>
