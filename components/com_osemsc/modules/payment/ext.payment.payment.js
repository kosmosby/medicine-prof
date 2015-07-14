<script type="text/javascript">
Ext.ns('oseMsc','oseMsc.payment');
oseMsc.msg = new Ext.App();

Ext.onReady(function(){
	Ext.QuickTips.init();

	//oseMemMsc.modStore.loadData([
    //    ["Joomla User Information", 'juser','member']
	//]);

	//oseMsc.payment.panel.render('ose-payment');

	oseMsc.payment.formPanel = new Ext.FormPanel({
		border: false
		,items: [{
			border: false
			,ref : 'mscList'
			,autoLoad: {
				url: 'index.php?option=com_osemsc&controller=payment'
				,params:{ task:'getAddon',addon_name: 'msc',addon_type: 'paymentOS' }
				,scripts: true
				,callback: function(el ,success, response, options)	{
					oseMsc.payment.formPanel.mscList.add(eval('oseMscAddon.msc'));
					oseMsc.payment.formPanel.mscList.doLayout();
				}
			}
		},{
			border: false
			,ref : 'payment_mode'
			,autoLoad: {
				url: 'index.php?option=com_osemsc&controller=payment'
				,params:{ task:'getAddon',addon_name: 'payment_mode',addon_type: 'paymentOS' }
				,scripts: true
				,callback: function(el ,success, response, options)	{
					oseMsc.payment.formPanel.payment_mode.add(eval('oseMscAddon.payment_mode'));
					oseMsc.payment.formPanel.payment_mode.doLayout();
				}
			}
		},{
			border: false
			,ref : 'payment'
			,autoLoad: {
				url: 'index.php?option=com_osemsc&controller=payment'
				,params:{ task:'getAddon',addon_name: 'payment',addon_type: 'paymentOS' }
				,scripts: true
				,callback: function(el ,success, response, options)	{
					oseMsc.payment.formPanel.payment.add(eval('oseMscAddon.payment'));
					oseMsc.payment.formPanel.payment.doLayout();

					oseMsc.payment.formPanel.getForm().load({
						url: 'index.php?option=com_osemsc&controller=payment'
						,params: {task: 'getBillingInfo'}
					});
				}
			}
		}]
		,renderTo: 'ose-payment'
		,reader:new Ext.data.JsonReader({
		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'user_id',
		    fields:[
			    {name: 'msc_id', type: 'string', mapping: 'msc_id'}
			    ,{name: 'payment.payment_mode', type: 'string', mapping: 'payment_mode'}
			    ,{name: 'bill.addr1', type: 'string', mapping: 'addr1'}
			    ,{name: 'bill.city', type: 'string', mapping: 'city'}
			    ,{name: 'bill.state', type: 'string', mapping: 'state'}
			    ,{name: 'bill.postcode', type: 'string', mapping: 'postcode'}
		  	]
	  	})
		,buttons: [{
			text: 'OK'
			,handler: function()	{
				if(!confirmWin)	{
					var confirmWin = new Ext.Window({
						width: 500
						,title: 'Confirmation'
						,modal: true
						,autoLoad: {
							url: 'index.php?option=com_osemsc&controller=payment'
							,params:{
								task : 'generateConfirmDialog'
								,msc_id: Ext.getCmp('msc_id').getValue().getGroupValue()
								,payment_mode: Ext.getCmp('payment_mode').getValue().getGroupValue()
								,payment_method: oseMsc.payment.formPanel.getForm().findField('payment.payment_method').getValue()
							}
						}
						,buttons: [{
							text: 'OK'
							,handler: function()	{
								oseMsc.payment.formPanel.getForm().submit({
				 					//waitMsg: 'Waiting...'
				 					clientValidation: true
				 					,url: 'index.php?option=com_osemsc&controller=payment'
				 					,params:{task:'toPaymentOS'}
				 					,success: function(form,action){
				 						var payment_method = oseMsc.payment.formPanel.getForm().findField('payment.payment_method').getValue();

				 						switch(payment_method)	{
				 							case('paypal'):
				 								var msg = action.result;
												//window.location = msg.html.url;

												Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
													Ext.get('paypal_image').dom.click();
												}).setVisibilityMode(2).setVisible(false);

				 							break;

				 							default:
				 								oseMsc.formSuccessMB(form,action)
				 							break;
				 						}

				 					}
				 					,failure: function(form,action){
				 						oseMsc.formFailure(form,action)
				 					}
				 				})
							}
						}]
					});


				}

				confirmWin.show();
			}
		}]

	})
});
</script>
