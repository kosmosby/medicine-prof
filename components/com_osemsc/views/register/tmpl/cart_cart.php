<?php
/**
  * @version     5.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 15-Nov-2010
  * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
  *
  *
  *  This program is free software: you can redistribute it and/or modify
  *  it under the terms of the GNU General Public License as published by
  *  the Free Software Foundation, either version 3 of the License, or
  *  (at your option) any later version.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU General Public License for more details.
  *
  *  You should have received a copy of the GNU General Public License
  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
  *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
*/
defined('_JEXEC') or die("Direct Access Not Allowed");
$cart = oseMscPublic::getCart();
$cartItems = $cart->get('items');
$subtotal = $cart->getSubtotal();
$total = $cart->get('total');
$discount = $cart->get('discount');
//oseExit($subtotal);
?>
<?php
	if(empty($cartItems))
	{
?>
<script type="text/javascript">
//Ext.onReady(function(){
	oseMsc.reg.license = new Ext.FormPanel({
		id: 'ose-license-form'
		,renderTo: 'ose-license'
		,border: false
		,labelAlign: 'top'
		,items:[{
			layout:'hbox'
			,fieldLabel: 'License Code'
			,border:false
			,items:[{
				xtype:'textfield'
				,name: 'license_code'
				,allowBlank: false
			},{
				xtype: 'button'
				,text: 'Add'
				,handler: function()	{
					oseMsc.reg.license.getForm().submit({
						url: 'index.php?option=com_osemsc&controller=register'
						,params:{task:'action',action:'register.license.add'}
						,success: function(form,action)		{
							oseMsc.formSuccess(form,action);
							oseMsc.reg.panel.fireEvent('load');
						}
						,failure: function(form,action)		{
							oseMsc.formFailureMB(form,action);
						}
					})
				}
			}]
		}]
	})
//}
</script>
 		No Item In The Cart, Back to Shopping!
 		<a href="<?php echo JRoute::_('index.php?option=com_osemsc&view=memberships');?>">Back to Membership List</a>
		<div id="ose-license"></div>
<?php
	}
	else
	{
		$currencyCombo = oseRegistry::call('msc')->runAddonAction('register.msc.getCurrencyList');
		$paymentModeCombo = oseRegistry::call('msc')->runAddonAction('register.payment.getPaymentMode');

?>
<script type="text/javascript">
Ext.onReady(function(){
	oseMsc.reg.continueBtn = Ext.get('ose-button-continue');
	oseMsc.reg.continueBtn.on('click', function(){
			Ext.Ajax.request({
				url: 'index.php?option=com_osemsc&controller=register'
				,params: {
					task: 'save'
					}
				,callback: function(el,success,response,opt)
				{
					oseMsc.reg.panel.fireEvent('load');
				}
			})
	});

	oseMsc.reg.license = new Ext.FormPanel({
		id: 'ose-license-form'
		,renderTo: 'ose-license'
		,border: false
		,labelAlign: 'top'
		,items:[{
			layout:'hbox'
			,fieldLabel: 'License Code'
			,border:false
			,items:[{
				xtype:'textfield'
				,name: 'license_code'
				,allowBlank: true
			},{
				xtype: 'button'
				,text: 'Add'
				,handler: function()	{
					oseMsc.reg.license.getForm().submit({
						url: 'index.php?option=com_osemsc&controller=register'
						,params:{task:'action',action:'register.license.add'}
						,success: function(form,action)		{
							oseMsc.formSuccess(form,action);
							oseMsc.reg.panel.fireEvent('load');
						}
						,failure: function(form,action)		{
							oseMsc.formFailureMB(form,action);
						}
					})
				}
			}]
		}]
	})

	oseMsc.reg.coupon = new Ext.FormPanel({
		id: 'ose-coupon-form'
		,renderTo: 'ose-coupon'
		,border: false
		,labelAlign: 'top'
		,items:[{
			layout:'hbox'
			,fieldLabel: 'Coupon Code'
			,border:false
			,items:[{
				xtype:'textfield'
				,name: 'coupon_code'
				,allowBlank: true
			},{
				xtype: 'button'
				,text: 'Use'
				,handler: function()	{
					oseMsc.reg.coupon.getForm().submit({
						url: 'index.php?option=com_osemsc&controller=register'
						,params:{task:'action',action:'register.coupon.add'}
						,success: function(form,action)		{
							oseMsc.formSuccess(form,action);
							oseMsc.reg.panel.fireEvent('load');
						}
						,failure: function(form,action)		{
							oseMsc.formFailureMB(form,action);
						}
					})
				}
			}]
		}]
	})
})

oseMsc.reg.reload = function()	{
	Ext.Msg.wait('Pleas wait...','Refreshing')
	Ext.Ajax.request({
		url: 'index.php?option=com_osemsc&controller=register'
		,params: {task: 'changeCurrency',ose_currency:Ext.fly('ose_currency').getValue()}
		,callback: function(el,success,response,opt)	{
			window.location.reload();
		}
	})
}

oseMsc.reg.changePaymentMode = function()	{
	Ext.Msg.wait('Pleas wait...','Refreshing')
	Ext.Ajax.request({
		url: 'index.php?option=com_osemsc&controller=register'
		,params: {task: 'changePaymentMode',ose_payment_mode:Ext.fly('ose_payment_mode').getValue()}
		,callback: function(el,success,response,opt)	{
			window.location.reload();
		}
	})
}

oseMsc.reg.removeItem = function(entry_id,entry_type)	{
	Ext.Msg.wait('Pleas wait...','Refreshing')
	Ext.Ajax.request({
		url: 'index.php?option=com_osemsc&controller=register'
		,params: {task: 'removeCartItem','entry_id':entry_id,'entry_type':entry_type}
		,callback: function(el,success,response,opt)	{
			oseMsc.reg.panel.fireEvent('load');
			Ext.Msg.hide();
		}
	})
}

</script>

<div id='thermometer'>
<div id='image'>
<img src ='/components/com_osemsc/assets/images/therm_cart.gif' border='0'>
</div>
<div class='therm_sec'>
<div class="text">
	<span class='selected'>
	<?php echo JText::_('Shopping Basket');?>
	</span>
</div>
<div class="text"><?php echo JText::_('Sign In');?></div>
<div class="text"><?php echo JText::_('Billing');?></div>
<div class="text"><?php echo JText::_('Order Confirm');?></div>
</div>
</div>

	<div id='osecart-cart'>
		<div class='osecart-heading'><?php echo JText::_('Items in your Shopping Cart:');?></div>
		<div class='payment-selector'>
			<div class='items' id="ose-currency-transmit">Currency:<span id="ose-currency-combo"><?php echo $currencyCombo;?></span></div>
			<div class='items' id="ose-payment-mode">Payment Mode:<span id="ose-payment-mode-combo"><?php echo $paymentModeCombo;?></span></div>
		</div>

	   <div id='osecart-items'>
		<table width='100%'>
			<thead class='osecart-header'>
				<th class='items' colspan='2'><?php echo JText::_('Item');?></th>
				<th class='items'><?php echo JText::_('Update');?></th>
				<th class='items'><?php echo JText::_('Price');?></th>
			</thead>

			<tbody>
<?php
		$price = array();
		$currency = oseMscPublic::getSelectedCurrency();
		foreach ($cartItems as $key => $item)
		{
			if(oseObject::getValue($item,'payment_mode') == 'm')
			{
				$paymentPrice = oseObject::getValue($item,'standard_price');
			}
			else
			{
				$has_trial = oseObject::getValue($item,'has_trial');
				$paymentPrice = empty($has_trial)?oseObject::getValue($item,'standard_price'):oseObject::getValue($item,'trial_price');
			}
			//$explode = str_replace($currency,'',$paymentPrice);
			//$price[] = trim($explode);
?>
			<tr class='purchased-items'>
				<td width='82px'><img src ='/<?php echo oseObject::getValue($item,'image');?>'></td>
				<td class='purchased-item-title'><?php echo oseObject::getValue($item,'title');?></td>
				<td class='purchased-item'><a href="javascript:oseMsc.reg.removeItem('<?php echo oseObject::getValue($item,'entry_id');?>','<?php echo oseObject::getValue($item,'entry_type');?>')">Remove</a></td>
				<td class='purchased-item-costs'><?php echo oseObject::getValue($item,'standard_price');?></td>
			</tr>
<?php
		}
?>
			</tbody>

			<tfoot>
			<tr>
				<td colspan="2">
				</td>
				<td>
				<?php echo JText::_('Sub Total');?>:
				</td>
				<td class="purchased-item-costs">
				<?php echo $currency.' '.$subtotal;?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				</td>
				<td >
				<?php echo JText::_('Discounts');?>:
				</td>
				<td class="purchased-item-costs">
				<?php echo $currency.' '.$discount;?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				</td>
				<td>
				<?php echo JText::_('Total');?>:
				</td>
				<td class="purchased-item-costs">
				<?php echo $currency.' '.$total;?>
				</td>
			</tr>
			</tfoot>
		</table>
	  <div>
	  <div id='additional'>
	    <div id="ose-license"></div>
		<div id="ose-coupon"></div>
	  </div>
	  <div id='osecontinue' >
			<div class='items'>
			<button id='ose-button-continue' name='ose-button-continue' class='button'>Checkout </button>
			</div>
			<div class='items'>
			<button onclick="window.location='<?php echo JRoute::_('index.php?option=com_osemsc&view=memberships');?>'" class='button'>Continue Shopping </button>
			</div>
	  </div>
	</div>
<?php
	}
?>