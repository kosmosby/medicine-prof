/*
 * File: app/view/BillingFieldset.js
 *
 * This file was generated by Sencha Architect version 2.1.0.
 * http://www.sencha.com/products/architect/
 *
 * This file requires use of the Sencha Touch 2.0.x library, under independent license.
 * License of Sencha Architect does not include license for Sencha Touch 2.0.x. For more
 * details see http://www.sencha.com/license or contact license@sencha.com.
 *
 * This file will be auto-generated each and everytime you save your project.
 *
 * Do NOT hand edit this file.
 */

Ext.define('MyApp.view.reg.CouponFieldset', {
    extend: 'Ext.form.FieldSet',
    alias: 'widget.coupon',
    requires:['MyApp.field.HolderSelect'],
    config: {
        title: Joomla.JText._('Coupon'),
        items: [
                {
                	xtype: 'container',
                	layout: 'hbox',
                	items: [
	                	{
			                xtype: 'textfield',
			                itemId: 'coupon_code',
			                label: Joomla.JText._('Coupon_Code'),
			                labelWidth: '46%',
			                name: 'coupon_code',
			                placeHolder: Joomla.JText._('PLEASE_ENTER_COUPON_CODE'),
			                flex: 2
			            }
			            ,{
							xtype: 'button'
							,text: Joomla.JText._('Use')
							, flex: 1
							,handler: function(btn)	{
								var coupon_code = btn.up('container').down('#coupon_code').getValue();
								//alert(coupon_code)
								var option = btn.up('formpanel').down('#msc_option').getValue();;
								if(option == null || option == '')
								{
									Ext.Msg.alert(Joomla.JText._('Error'), Joomla.JText._('PLEASE_SELECT_A_MEMBERSHIP_FIRST'), function(){});
								}else{
									Ext.Ajax.request({
										url: getCurrentUrl()+'index.php?option=com_osemsc&controller=register'
										,params:{
											task:'action',action:'register.coupon.add'
											,coupon_code: coupon_code
										}
										,success: function(response,opt)		{
											var msg = Ext.decode(response.responseText);
											//alert(response.responseText)
											 Ext.Msg.alert(msg.title, msg.content, function(){});
										}
										,failure: function(response,opt)		{
											var msg = Ext.decode(response.responseText);
											 Ext.Msg.alert(msg.title, msg.content, function(){});
										}
		
									})
								}
								
							}
						}
                	]
					
                }
        ]
    }

});