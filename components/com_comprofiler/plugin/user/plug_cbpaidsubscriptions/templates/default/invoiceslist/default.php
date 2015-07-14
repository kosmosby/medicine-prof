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

	<div class="contentheading" id="cbregSubscribed"><?php echo $this->plansTitle; ?></div>

	<div class='cbregInvoicesList cbclearboth'>
		<table id="cbregInvListTable">
			<thead>
				<tr class="sectiontableheader">
					<th scope="col" class="cbregInvoiceDate">
						<?php echo CBPTXT::Th("Invoice date"); ?>
					</th>
<?php
		if ( $this->show_invoice_numbers ) {
?>
					<th scope="col" class="cbregInvoiceNumber">
						<?php echo CBPTXT::Th("Invoice number"); ?>
					</th>
<?php
		}
?>
					<th scope="col" class="cbregInvoiceState">
						<?php echo CBPTXT::Th("Invoice state"); ?>
					</th>
					<th scope="col" class="cbregInvoicePaymentType">
						<?php echo CBPTXT::Th("Payment type"); ?>
					</th>
				</tr>
			</thead>
			<tbody>
<?php
		$k	=	1;
		foreach ( $this->_model as $i ) {
			/** @var $i cbpaidPaymentBasket */
			$k	=	( $k == 1 ? 2 : 1 );
			$invoiceUrl		=	$this->invoicesUrls[$i->id];
?>
				<tr class="sectiontableentry<?php echo $k; ?>">
					<td class="cbregInvoiceDate">
						<a  onclick="window.open('<?php echo $invoiceUrl; 
							 ?>', 'cbinvoice', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'); return false;" target="_blank" href="<?php
							 echo $invoiceUrl;
							 ?>"><?php echo cbFormatDate( $i->time_initiated, 1, false ); ?></a>
					</td>
<?php
		if ( $this->show_invoice_numbers ) {
?>
					<td class="cbregInvoiceNumber">
						<?php echo htmlspecialchars( $i->invoice ); ?>
					</td>
<?php
		}
?>
					<td class="cbregInvoiceState">
						<?php
		echo CBPTXT::Th( htmlspecialchars( $i->payment_status ) );
		if ( $i->payment_status == 'Pending' ) {
			echo ' (' . sprintf( CBPTXT::Th("waiting for %s"), ( $i->pending_reason ? $i->pending_reason : CBPTXT::T("Payment") ) ) . ')';
		}
		$maintenanceButtonsHtml		=	$i->renderMaintenanceButtonsHtml();
		if ( $maintenanceButtonsHtml ) {
			echo '<div class="cbregMaintenanceButtons">' . implode( ' | ', $maintenanceButtonsHtml ) . '</div>';
		}
						?>
					</td>
					<td class="cbregInvoicePaymentType">
						<?php echo CBPTXT::Th( htmlspecialchars( $i->payment_type ) ); ?>
					</td>
				</tr>
<?php
		}
?>
			</tbody>
		</table>
	</div>
<?php
		global $_CB_framework;
		if ( $_CB_framework->getUi() == 1 ) {
?>
	<div class="cbControlButtonsLine">
		<a href="<?php echo $_CB_framework->userProfileUrl( $this->user->id, true, 'getcbpaidsubscriptionsTab' ); ?>">
			<?php echo CBPTXT::Th("Click here to go back to your user profile"); ?>
		</a>
	</div>
<?php
		}
?>
