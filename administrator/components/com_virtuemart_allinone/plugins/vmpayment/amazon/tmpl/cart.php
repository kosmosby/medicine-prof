<?php
/**
 *
 * Layout for the AMAZON cart
 * @version $Id$
 * @package    VirtueMart
 * @subpackage Cart
 * @author Valerie Isaksen
 *
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
vmJsApi::jSite();
if (VmConfig::get('usefancy', 1)) {
	vmJsApi::js('fancybox/jquery.fancybox-1.3.4.pack');
	vmJsApi::css('jquery.fancybox-1.3.4');
	$box = "
//<![CDATA[
	jQuery(document).ready(function($) {
		$('div#full-tos').hide();
		var con = $('div#full-tos').html();
		$('a#terms-of-service').click(function(event) {
			event.preventDefault();
			$.fancybox ({ div: '#full-tos', content: con });
		});
	});

//]]>
";
} else {
	vmJsApi::js('facebox');
	vmJsApi::css('facebox');
	$box = "
//<![CDATA[
	jQuery(document).ready(function($) {
		$('div#full-tos').hide();
		$('a#terms-of-service').click(function(event) {
			event.preventDefault();
			$.facebox( { div: '#full-tos' }, 'my-groovy-style');
		});
	});

//]]>
";
}

JHtml::_('behavior.formvalidation');
$document = JFactory::getDocument();
$document->addScriptDeclaration($box);


$document->addScriptDeclaration ("

//<![CDATA[
	jQuery(document).ready(function($) {
	jQuery(this).vm2front('stopVmLoading');
	jQuery('#checkoutFormSubmit').bind('click dblclick', function(e){
	jQuery(this).vm2front('startVmLoading');
	e.preventDefault();
    jQuery(this).attr('disabled', 'true');
    jQuery(this).removeClass( 'vm-button-correct' );
    jQuery(this).addClass( 'vm-button' );
    jQuery('#checkoutForm').submit();

});

	});

//]]>

");



$document->addStyleDeclaration('#facebox .content {display: block !important; height: 480px !important; overflow: auto; width: 560px !important; }');

?>
	<div id="amazonShipmentNotFoundDiv">
		<?php if (!$this->found_shipment_method) { ?>
			<div id="system-message-container">
				<dl id="system-message">
					<dt class="info">info</dt>
					<dd class="info message">
						<ul>
							<li><?php echo JText::_('VMPAYMENT_AMAZON_UPDATECART_SHIPMENT_NOT_FOUND'); ?></li>
						</ul>
					</dd>
				</dl>
			</div>
		<?php
		}
		?>
	</div>
	<div id="amazonErrorDiv">
	</div>

	<div id="amazonLoading"></div>

	<div class="cart-view" id="cart-view">
		<div id="amazonHeader">
			<div class="width50 floatleft">
				<h1><?php echo JText::_('VMPAYMENT_AMAZON_PAY_WITH_AMAZON'); ?></h1>

				<div class="payments_signin_button"></div>
			</div>
			<div class="width50 floatleft right">
				<?php // Continue Shopping Button
				if (!empty($this->continue_link_html)) {
					echo $this->continue_link_html;
				}
				?>
				<div>
					<a href="#" id="leaveAmazonCheckout"><?php echo vmText::_('VMPAYMENT_AMAZON_LEAVE_PAY_WITH_AMAZON') ?></a>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div id="amazonAddressBookWalletWidgetDiv">
			<div id="amazonAddressBookWidgetDiv" class="width50 floatleft"></div>

			<div id="amazonWalletWidgetDiv" class="width50 floatleft"></div>
		</div>
		<div class="clear"></div>
		<div id="amazonChargeNowWarning"></div>

		<div class="clear"></div>
		<div id="amazonCartDiv">
			<div id="signInButton"></div>

			<?php

			if ($this->checkout_task) {
				$taskRoute = '&task=' . $this->checkout_task;
			} else {
				$taskRoute = '';
			}

			if ($this->cart->getDataValidated()) {
				$this->readonly_cart = true;
			} else {
				$this->readonly_cart = false;
			}

			?>
			<form method="post" id="checkoutForm" name="checkoutForm" action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart' . $taskRoute, $this->useXHTML, $this->useSSL); ?>">

				<div id="amazonShipmentsDiv"><?php
					//if (!$this->readonly_cart) {
					if (!$this->cart->automaticSelectedShipment  and !$this->cart->_dataValidated) {
						?>
						<?php echo $this->loadTemplate('shipment'); ?>
					<?php

					}
					?>
				</div>
				<?php
				//}
				// This displays the pricelist MUST be done with tables, because it is also used for the emails
				echo $this->loadTemplate('pricelist');
				// added in 2.0.8
				?>


				<?php // Leave A Comment Field ?>
				<div class="customer-comment marginbottom15">
					<span class="comment"><?php echo JText::_('COM_VIRTUEMART_COMMENT_CART'); ?></span><br/>
					<textarea class="customer-comment" name="customer_comment" cols="60" rows="1"><?php echo $this->cart->customer_comment; ?></textarea>
				</div>
				<?php // Leave A Comment Field END ?>



				<?php // Continue and Checkout Button ?>
				<div class="checkout-button-top">

					<?php // Terms Of Service Checkbox
					if (!class_exists('VirtueMartModelUserfields')) {
						require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'userfields.php');
					}
					$userFieldsModel = VmModel::getModel('userfields');
					if ($userFieldsModel->getIfRequired('agreed')) {
						if (!class_exists('VmHtml')) {
							require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'html.php');
						}
						echo VmHtml::checkbox('tosAccepted', $this->cart->tosAccepted, 1, 0, 'class="terms-of-service"');

						if (VmConfig::get('oncheckout_show_legal_info', 1)) {
							?>
							<div class="terms-of-service">
								<label for="tosAccepted">
									<a href="<?php JRoute::_('index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=1', FALSE) ?>" class="terms-of-service" id="terms-of-service" rel="facebox"
									   target="_blank"> <span class="vmicon vm2-termsofservice-icon"></span>
										<?php echo JText::_('COM_VIRTUEMART_CART_TOS_READ_AND_ACCEPTED'); ?>
									</a> </label>

								<div id="full-tos">
									<h2><?php echo JText::_('COM_VIRTUEMART_CART_TOS'); ?></h2>
									<?php echo $this->cart->vendor->vendor_terms_of_service; ?>
								</div>

							</div>
						<?php
						}
					}
					?>
					<div id="amazon_checkout">

						<?php
						echo $this->checkout_link_html;
						?>
					</div>
				</div>
				<?php // Continue and Checkout Button END ?>
				<input type='hidden' name='order_language' value='<?php echo $this->order_language; ?>'/>
				<input type='hidden' id='STsameAsBT' name='STsameAsBT' value='<?php echo $this->cart->STsameAsBT; ?>'/>
				<input type='hidden' name='task' value='<?php echo $this->checkout_task; ?>'/>
				<input type='hidden' name='virtuemart_paymentmethod_id' value='<?php echo $this->cart->virtuemart_paymentmethod_id; ?>'/>
				<input type='hidden' name='doRedirect' value='false'/>
				<input type='hidden' name='option' value='com_virtuemart'/>
				<input type='hidden' name='view' value='cart'/>
			</form>
		</div>
	</div>

<?php vmTime('Cart view Finished task ', 'Start'); ?>