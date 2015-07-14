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
$session =& JFactory::getSession();
$cart = $session->get('osecart');
?>
<script type="text/javascript">
Ext.onReady(function()	{

 	oseMsc.reg.userForm = new Ext.form.FieldSet({
 		title: 'User Information'
 		//,border: false
	  	,items:[{
	  		xtype: 'hidden'
	  		,name: 'user_id'
	  	},{
	  		xtype: 'displayfield'
	  		,name: 'username'
	  		,fieldLabel: 'Username'
	  	},{
	  		xtype: 'displayfield'
	  		,name: 'firstname'
	  		,fieldLabel: 'First Name'
	  	},{
	  		xtype: 'displayfield'
	  		,name: 'lastname'
	  		,fieldLabel: 'Last Name'
	  	},{
	  		xtype: 'displayfield'
	  		,name: 'email'
	  		,fieldLabel: 'Email'
	  	}]

 	})

	oseMsc.reg.signinForm = new Ext.FormPanel({
		renderTo: 'ose-reg-billing-panel'
		,border:false
		,layout: 'hbox'
		,autoHeight: true
		,autoWidth: true
		,defaults:{width:(Ext.get('ose-reg-billing-panel').getWidth()-10)/2,border:false}
		,items:[{
			bodyStyle: 'padding:3px'
			,ref:'hbox1'
			,defaults:{labelWidth: 130, msgTarget: 'side'}
			,items:[
				oseMsc.reg.userForm
			,{
				defaults:{labelWidth: 130}
				,id: 'billing'
				,border:false
				,autoHeight: true
				,autoLoad:{
	 				url: 'index.php?option=com_osemsc&controller=register'
	 				,params:{task: 'getAddons',pos: 'billing'}
	 				//,scripts: true
	 				,callback: function(el,success,response,opt)	{
	 					var result = Ext.decode(response.responseText);
	 					el.update('');
	 					var billing = oseMsc.reg.signinForm.findById('billing');
	 					Ext.each(result, function(item,i,all)	{
	 						if(item.addon_name)	{
		 						billing.add(eval(item.addon_name));
		 						eval(item.addon_name).setTitle(item.title)
		 						billing.doLayout();
	 						}
	 					});
	 					oseMsc.reg.signinForm.doLayout();
	 					oseMsc.reg.panel.doLayout();

	 					oseMsc.reg.signinForm.getForm().load({
							url: 'index.php?option=com_osemsc&controller=member'
							,params: {task: 'action', action: 'member.billinginfo.getItem'}
						})
	 				}
	 			}
			}]
		},{
			width: 10
		},{
			bodyStyle: 'padding:3px'
			,ref:'hbox2'
			,defaults:{labelWidth: 130}
			,items:[{
				defaults:{labelWidth: 130}
				,id: 'payment'
				,border:false
				,autoHeight: true
				,autoLoad:{
	 				url: 'index.php?option=com_osemsc&controller=register'
	 				,params:{task: 'getAddons',pos: 'payment'}
	 				,callback: function(el,success,response,opt)	{
	 					var result = Ext.decode(response.responseText);
	 					el.update('');

	 					var payment = oseMsc.reg.signinForm.findById('payment');
	 					Ext.each(result, function(item,i,all)	{
	 						if(item.addon_name)	{
		 						payment.add(eval(item.addon_name));
		 						eval(item.addon_name).setTitle(item.title)
		 						payment.doLayout();
	 						}
	 					});
	 					//oseMsc.reg.signinForm.hbox2.doLayout();
	 					oseMsc.reg.signinForm.doLayout();
	 					//oseMsc.reg.panel.doLayout();
	 				}
	 			}
			}]
		}]
		,listeners: {
			render: function(p)	{
				/*p.getForm().load({
 					url: 'index.php?option=com_osemsc&controller=member'
					,params:{task: 'action', action:'member.juser.getItem'}
 				})
				p.getForm().load({
					url: 'index.php?option=com_osemsc&controller=member'
					,params: {task: 'action', action: 'member.billinginfo.getItem'}
				})*/
			}
		}
		,reader:new Ext.data.JsonReader({
		    root: 'result',
		    totalProperty: 'total',
		    idProperty: 'user_id',
		    fields:[
			    {name: 'bill.addr1', type: 'string', mapping: 'addr1'}
			    ,{name: 'bill.city', type: 'string', mapping: 'city'}
			    ,{name: 'bill.state', type: 'string', mapping: 'state'}
			    ,{name: 'bill.postcode', type: 'string', mapping: 'postcode'}
			    ,{name: 'bill.country', type: 'string', mapping: 'country'}
			    ,{name: 'bill.company', type: 'string', mapping: 'company'}
			    ,{name: 'bill.telephone', type: 'string', mapping: 'telephone'}

			    ,{name: 'user_id', type: 'int', mapping: 'user_id'}
			    ,{name: 'username', type: 'string', mapping: 'username'}
			    ,{name: 'firstname', type: 'string', mapping: 'firstname'}
			    ,{name: 'lastname', type: 'string', mapping: 'lastname'}
			    ,{name: 'email', type: 'string', mapping: 'user_email'}
		  	]
	  	})
	  	,buttonAlign: 'left'
	  	,fbar: new Ext.Toolbar({
			items:[{
				text:'Back to Cart'
				,listeners: {
					click:function()	{
						Ext.Ajax.request({
							url: 'index.php?option=com_osemsc'
							,params:{ task:'backStep', step:'cart','controller':'register'}
							,callback: function()	{
								oseMsc.reg.panel.fireEvent('load');
							}
						})
					}
				}
			},'->',{
		    	ref: 'ok'
		        ,text: 'Continue'
		        ,handler: function(){
	 				if(!confirmWin)	{
						var confirmWin = new Ext.Window({
							title: 'Confirmation'
							,width: 800
							,modal: true
							,items:[{
								border: false
								,height: 500
								,autoLoad: {
									url: 'index.php?option=com_osemsc&controller=register'
									,params:{
										task : 'generateConfirmDialog'
										,payment_method: oseMsc.reg.signinForm.getForm().findField('payment.payment_method').getValue()
									}
								}
								,buttons: [{
									text: 'OK'
									,handler: function()	{
										//confirmWin.getEl().mask('Membership registration in progress, please wait...');
										oseMsc.reg.signinForm.getForm().submit({
						 					url: 'index.php?option=com_osemsc&controller=register'
						 					,params:{task:'save'}
						 					,success: function(form,action){
												var payment_method = form.findField('payment.payment_method').getValue();

												switch(payment_method)	{
						 							case('paypal'):
						 								var msg = action.result;
														//window.location = msg.html.url;

														Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
															Ext.get('paypal_image').dom.click();
														}).setVisibilityMode(2).setVisible(false);

						 							break;

						 							default:
						 								oseMsc.formSuccessMB(form, action,function(btn,text){
								 							if(btn == 'ok')	{
								 								Ext.Msg.wait('Redirecting. Please Wait...');
								 								window.location = 'index.php?option=com_osemsc&view=member'
							 								}
							 							})

														confirmWin.getEl().unmask();
						 							break;
						 						}
												oseMsc.reg.panel.fireEvent('load');
						 						confirmWin.getEl().unmask();
						 					}
						 					,failure: function(form,action){
						 						confirmWin.getEl().unmask();
						 						if (action.failureType === Ext.form.Action.CLIENT_INVALID){
													Ext.Msg.alert('Notice','Pleas Check The Notice In The Form');
										        }

												if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
										            Ext.Msg.alert('Error',
										            'Status:'+action.response.status+': '+
										            action.response.statusText);
										        }

										        if (action.failureType === Ext.form.Action.SERVER_INVALID){
										            var msg = action.result;
													if(!action.result.script)	{
							 							oseMsc.formFailureMB(form,action,function(btn,text){
								 							if(btn == 'ok')	{
								 								Ext.Msg.wait('Redirecting. Please Wait...');
								 								window.location.reload();
								 							}
								 						});
													}	else	{

								 						confirmWin.close();
														eval('oseMsc.reg.regForm.getForm().findField'+action.result.script.replace('username','juser.username'));
													}
										        }
						 					}
						 				})
									}
								}]
							}]

						});


					}

					confirmWin.show().alignTo(Ext.getBody(),'t-t');

	 			}
			}]
	    })
	})

})
</script>

<div id='thermometer'>
<div id='image'>
<img src ='/components/com_osemsc/assets/images/therm_cart_billing.gif' border='0'>
</div>
<div class='therm_sec'>
<div class="text">
	<?php echo JText::_('Shopping Basket');?>
</div>
<div class="text">

	<?php echo JText::_('Sign In');?>
	</span>
</div>
<div class="text">
	<span class='selected'>
	<?php echo JText::_('Billing');?>
	</span>
</div>
<div class="text"><?php echo JText::_('Order Confirm');?></div>
</div>
</div>

<div id="ose-reg-user-panel"></div>
<div id="ose-reg-billing-panel"></div>
<div id="ose-payment-callback-form"></div>
