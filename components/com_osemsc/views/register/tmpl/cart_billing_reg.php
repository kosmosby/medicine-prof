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
	/// Params Setting
	oseMsc.reg.params = {checked:false};
	Ext.apply(Ext.form.VTypes,{
		uniqueUserName: function(val,field)	{
			var unique = oseMsc.reg.params;
			if(!unique.checked)	{

				Ext.Ajax.request({
	        		url: 'index.php?option=com_osemsc&controller=register'
	        		,params: {
	        			task : 'formValidate'
	        			,juser_username : val
	        		}
	        		,success: function(response, opt)	{
	        			var msg = Ext.decode(response.responseText);

	        			unique =  msg;
	        			unique.checked = true;

	        			oseMsc.reg.params = unique;
	        			return field.validate();
	        		}
	        	});
			}	else	{

				oseMsc.reg.params.checked = false;

				if(!Ext.isBoolean(unique.result))	{
    				return false;
    			}	else	{
    				return true;
    			}

			}
			return true;
		}
		,uniqueUserNameText: 'This username has been registered by other user.'
	})
	/// Params Setting End

	oseMsc.reg.juserFieldset = new Ext.form.FieldSet({
 		title: 'User Account'
 		,defaultType: 'textfield'
 		,autoHeight: true
 		//,labelWidth: 120
 		,defaults:{width: 150,msgTarget:'side'}
 		,items:[{
            fieldLabel: 'Username'
        	,xtype:'textfield'
        	,name: "juser.username"
            ,allowBlank:false
            ,validationEvent: 'blur'
            ,vtype: 'uniqueUserName'

        },{
            fieldLabel: 'First Name'
            ,name: "juser.firstname"
            ,allowBlank:false
        },{
            fieldLabel: 'Last Name'
            ,name: "juser.lastname"
            ,allowBlank:false
        },{
            fieldLabel: 'Email'
            ,name: 'juser.email'
            ,vtype:'email'
        }, {
        	itemId: 'pwd'
            ,fieldLabel: 'Password'
            ,name: 'juser.password1'
            ,inputType: 'password'
            ,allowBlank:false
        },{
            fieldLabel: 'Password Confirm'
            ,name: 'juser.password2'
            ,inputType: 'password'
            ,validateOnBlur: true
            ,validator :  function(val){
        		if(val != oseMsc.reg.juserFieldset.getComponent('pwd').getValue()){
        			return oseMsc.reg.juserFieldset.getComponent('pwd').fieldLabel + 'does not match';
        		}	else	{
        			return true;
        		}
            }
        }]
 	});

	oseMsc.reg.signinForm = new Ext.FormPanel({
		renderTo: 'ose-reg-billing-reg-panel'
		,border:false
		,layout: 'hbox'
		,autoHeight: true
		,autoWidth: true
		,defaults:{width:(Ext.get('ose-reg-billing-reg-panel').getWidth()-10)/2,border:false}
		,items:[{
			bodyStyle: 'padding:3px'
			,ref:'hbox1'
			,defaults:{labelWidth: 130}
			,items:[
				oseMsc.reg.juserFieldset
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
	 					oseMsc.reg.signinForm.doLayout();
	 				}
	 			}
			}]
		}]
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
						 					,clientValidation: true
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
<div id="ose-reg-billing-reg-panel"></div>
<div id="ose-payment-callback-form"></div>
