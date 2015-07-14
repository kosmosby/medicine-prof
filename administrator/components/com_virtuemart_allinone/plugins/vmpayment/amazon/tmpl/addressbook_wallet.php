<?php
defined('_JEXEC') or die();

/**
 * @author Valérie Isaksen
 * @version $Id: addressbook_wallet.php 8364 2014-10-02 10:37:39Z alatak $
 * @package VirtueMart
 * @subpackage vmpayment
 * @copyright Copyright (C) 2004-Copyright (C) 2004-2014 Virtuemart Team. All rights reserved.   - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
?>

<?php
static $jsAWLoaded = false;
$doc = JFactory::getDocument();
vmJsApi::jPrice();
jimport('joomla.environment.browser');
$browser = JBrowser::getInstance();
$isMobile = $browser->isMobile();
if ($isMobile) {
	$doc->setMetaData('viewport', "width=device-width, initial-scale=1, maximum-scale=1");
}
if (!$jsAWLoaded) {
	$doc->addScript(JURI::root(true) . '/plugins/vmpayment/amazon/assets/js/amazon.js');
	if ($viewData['include_amazon_css']) {
		$doc->addStyleSheet(JURI::root(true) . '/plugins/vmpayment/amazon/assets/css/amazon.css');
	}


//vmJsApi::js('plugins/vmpayment/amazon/assets/js/site', '');
	$doc->addScriptDeclaration("
		//<![CDATA[
jQuery(document).ready( function($) {
	amazonPayment.initPayment('" . $viewData['sellerId'] . "','" . $viewData['amazonOrderReferenceId'] . "', '" . $viewData['addressbook_designWidth'] . "', '" . $viewData['addressbook_designHeight'] . "', '" . $isMobile . "', '" . $viewData['virtuemart_paymentmethod_id'] . "', '" . $viewData['readOnlyWidgets'] . "');
});
//]]>
"); // addScriptDeclaration
	if ($viewData['renderAddressBook']) {
		$doc->addScriptDeclaration("
		//<![CDATA[
jQuery(document).ready( function($) {
	amazonPayment.showAmazonAddress();
});
//]]>
"); // addScriptDeclaration
	}
	if ($viewData['renderWalletBook']) {
		$doc->addScriptDeclaration("
		//<![CDATA[
jQuery(document).ready( function($) {
	amazonPayment.showAmazonWallet();
});
//]]>
"); // addScriptDeclaration
	}

	$doc->addScriptDeclaration("
	//<![CDATA[
jQuery(document).ready( function($) {
$('#leaveAmazonCheckout').click(function(){
	amazonPayment.leaveAmazonCheckout();
	});
});
//]]>
");

	if ($viewData['captureNow']) {
		$doc->addScriptDeclaration("
		//<![CDATA[
jQuery(document).ready( function($) {
	amazonPayment.displayCaptureNowWarning('" . JText::_('VMPAYMENT_AMAZON_CHARGE_NOW') . "');
});
//]]>
"); // addScriptDeclaration
	}


}

?>




