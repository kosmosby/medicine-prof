<?php
/**
* CBSubs (TM): Community Builder Paid Subscriptions Plugin: cbsubsgoogleanalytics
* @version $Id: cbsubs.googleanalytics.php 1528 2012-11-23 14:33:41Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage cbsubs.googleanalytics.php
* @author Beat
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onCPayAfterPaymentStatusUpdateEvent',	'onCPayAfterPaymentStatusUpdateEvent',	'getcbsubsgoogleanalyticsTab' );
/**
 * CBSubs Google-Analytics integration plugin handler class
 */
class getcbsubsgoogleanalyticsTab extends cbTabHandler {

	/**
	 * Handles changes of payment basket $paymentBasket payment statuses events
	 * This function may be called more than one time for events different than the Completed or Processed state if there are multiple notifications
	 *
	 * $unifiedStatus status mappings with e.g. Paypal status:
	 * CB Unified status				Paypal status
	 * Completed				<--		Completed
	 * Processed				<--		Processed, Canceled_Reversal
	 * Denied					<--		Denied, Expired, Failed, Voided
	 * Refunded					<--		Reversed, Refunded, Partially-Refunded
	 * Pending					<--		Pending, In-Progress
	 * RegistrationCancelled	<--		A new cb registration got cancelled by user (e.g. paypal cancel payment button)
	 *
	 * @param  UserTable                      $user                    User paying
	 * @param  cbpaidPaymentBasket            $paymentBasket           CBPaid Payment basket being paid (corresponding to PayPal variable names)
	 * @param  cbpaidUsersubscriptionRecord[] $subscriptions           CBPay Subscriptions being paid
	 * @param  string                         $unifiedStatus           new unified status: see above
	 * @param  string                         $previousUnifiedStatus   previous unified status: see above
	 * @param  string                         $eventType               type of event (paypal type): 'web_accept', 'subscr_payment', 'subscr_signup', 'subscr_modify', 'subscr_eot', 'subscr_cancel', 'subscr_failed'
	 * @param  cbpaidPaymentNotification      $notification            notification object of the payment
	 * @return void
	 */
 	public function onCPayAfterPaymentStatusUpdateEvent( $user, $paymentBasket, /** @noinspection PhpUnusedParameterInspection */ $subscriptions, $unifiedStatus, /** @noinspection PhpUnusedParameterInspection */ $previousUnifiedStatus, /** @noinspection PhpUnusedParameterInspection */ $eventType, /** @noinspection PhpUnusedParameterInspection */ $notification ) {
		global $_CB_framework, $_SERVER;

		if ( ! is_object( $user ) ) {
			return;
		}

		if ( ! in_array( $unifiedStatus, array( 'Completed', 'Processed' ) ) ) {
			return;
		}

		$params				=	cbpaidApp::settingsParams();
		$trackingCode		=	trim( $params->get( 'googleanalytics_trackingcode', null ) );

		if ( ! $trackingCode ) {
			return;
		}

		$isHttps			=	( isset( $_SERVER['HTTPS'] ) && ( ! empty( $_SERVER['HTTPS'] ) ) && ( $_SERVER['HTTPS'] != 'off' ) );

		$_CB_framework->document->addHeadScriptUrl( ( $isHttps ? 'https://ssl.' : 'http://www.' ) . 'google-analytics.com/ga.js' );

		$domainName			=	trim( $params->get( 'googleanalytics_domainname', null ) );

		$js					= 	"var _gaq = _gaq || [];"
							.	"_gaq.push(['_setAccount', '" . addslashes( $trackingCode ) . "']);";

		if ( $domainName ) {
			$js				.=	"_gaq.push(['_setDomainName', '" . addslashes( $domainName ) . "']);"
							.	( $domainName != 'none' ? "_gaq.push(['_setAllowHash', false]);" : null );
		}

		$js					.=	"_gaq.push(['_trackPageview']);"
							.	"_gaq.push(['_addTrans',"
							.		"'" . addslashes( $paymentBasket->item_number ) . "',"			// Order ID
							.		"'Community Builder',"											// Affiliation
							.		"'" . addslashes( $paymentBasket->mc_gross ) . "',"				// Total
							.		"'" . addslashes( $paymentBasket->tax ) . "',"					// Tax
							.		"'" . addslashes( $paymentBasket->mc_shipping ) . "',"			// Shipping
							.		"'" . addslashes( $paymentBasket->address_city ) . "',"			// City
							.		"'" . addslashes( $paymentBasket->address_state ) . "',"		// State
							.		"'" . addslashes( $paymentBasket->address_country ) . "'"		// Country
							.	"]);";

		$paymentItems		=	$paymentBasket->loadPaymentItems();

		if ( $paymentItems ) foreach ( $paymentItems as $item ) {
			$subscription	=	$item->loadSubscription();
			$plan			=	$subscription->getPlan();

			$js				.=	"_gaq.push(['_addItem',"
							.		"'" . addslashes( $paymentBasket->item_number ) . "',"		// Order ID
							.		"'" . addslashes( $item->id ) . "',"						// SKU
							.		"'" . addslashes( $plan->name ) . "',"						// Product Name
							.		"'" . addslashes( $plan->item_type ) . "',"					// Category
							.		"'" . addslashes( $item->getPrice() ) . "',"				// Price
							.		"'" . addslashes( $item->quantity ) . "'"					// Quantity
							.	"]);";
		}

		$js					.=	"_gaq.push(['_trackTrans']);";

		$_CB_framework->outputCbJQuery( $js );
	}
}
