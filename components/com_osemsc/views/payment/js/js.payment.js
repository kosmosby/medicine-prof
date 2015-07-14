Ext.ns('oseMsc','oseMsc.payment');

	oseMsc.payment.form = function(fp)	{
		this.fp = fp
	}
	
	oseMsc.payment.form.prototype = {
		buildWindow: function()	{
			var confirmWin = new Ext.Window({
				width: 500
				,title: Joomla.JText._('Confirmation')
				,modal: true
				,items:[{
					border: false
					,height: 450
					,autoLoad: {
						url: 'index.php?option=com_osemsc&controller=payment'
						,params:{
							task : 'generateConfirmDialog'
							,bill_country: this.fp.getForm().findField('bill_country').getValue()
							,bill_state: this.fp.getForm().findField('bill_state').getValue()
							,payment_method: this.fp.getForm().findField('payment.payment_method').getValue()
						}
					}
					,buttons: [{
						text: Joomla.JText._('OK')
						,handler: function()	{
							//confirmWin.body.mask('Membership registration in progress, please wait...');
							this.fp.getForm().submit({
			 					//waitMsg: 'Waiting...'
			 					clientValidation: true
			 					,url: 'index.php?option=com_osemsc&controller=payment'
			 					,params:{task:'toPayment'}
			 					,timeout: 120000
			 					,success: this.onPaymentSuccess
			 					,failure: this.onPaymentFailure
			 				})
						}
						,scope: this
					}]
				}]
			}).show().alignTo(Ext.getBody(),'t-t');
			
		}
		
		,onPaymentSuccess: function(form,action)	{
			var msg = action.result;
			var payment_method = msg.payment_method;
	
			switch(payment_method)	{
				case('paypal'):
					Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
						Ext.get('paypal_image').dom.click();
					}).setVisibilityMode(2).setVisible(false);
	
				break;
	
				case('none'):
					//confirmWin.body.unmask();
					oseMsc.formSuccessMB(form,action,function(btn,text){
						if(btn == 'ok')	{
							window.location = 'index.php?option=com_osemsc&view=member'
						}
					})
				break;
				
				case('epay'):
					//window.location = msg.html.url;
	
					Ext.get('ose-payment-callback-form').update(msg.html,false,function(){
						//alert(msg.html);
						Ext.get('epay_button').dom.click();
					}).setVisibilityMode(2).setVisible(false);
	
				break;
				
				case('pnw'):
					//window.location = msg.html.url;
	
					Ext.get('ose-payment-callback-form').update(msg.html,false,function(){
						//alert(msg.html);
						Ext.get('submit_button').dom.click();
					}).setVisibilityMode(2).setVisible(false);
	
				break;
				
				default:
					//confirmWin.body.unmask();
					oseMsc.formSuccessMB(form,action,function(btn,text){
						if(btn == 'ok')	{
							window.location = 'index.php?option=com_osemsc&view=member'
						}
					})
				break;
			}
		}
		
		,onPaymentFailure: function(form,action)	{
			//this.confirmWin.body.unmask();
			oseMsc.formFailureMB(form,action)
		}
		
		,onLoadAddons: function(addons,position,getForm)	{

			var posItem = eval("this.fp."+position);
			
			Ext.each(addons, function(item,i,all)	{
				if(item.addon_name)	{
					//alert(item.addon_name);
					
					if(getForm)	{
						var obj = eval("new "+item.addon_name+"(this.fp)")
					}else{
						var obj = eval("new "+item.addon_name+"()")
					}
					posItem.add(obj.init())
					//eval('this.fp.'+position).add(item.addon_name);
				}
			},this);
			
		}
		,render: function(div){
			this.fp.render(div);
		}
		,loadRegInfo: function()	{
			this.fp.getForm().load({
				url: 'index.php?option=com_osemsc&controller=payment'
				,waitMsg: 'Initializing...'
				,params: {task: 'getBillingInfo'}
				,success: function()	{
					//alert('d');
					//alert(oseMsc.payment.formPanel.getForm().findField('msc_option'))
				}
				,failure: function()	{
					//alert('f')
				}
			});
		}
		,setClickBtnAction: function(btnId)	{
			//alert(btnId)
			switch(btnId)	{
				case('submitBtnOk'):
					this.fp.getFooterToolbar().findById(btnId).on('click',this.buildWindow,this);
				break;
			}
		}
		,setBtnActive: function(btnId,active)	{
			//alert(btnId)
			switch(btnId)	{
				case('submitBtnOk'):
					this.fp.getFooterToolbar().findById(btnId).setDisabled(!active);
				break;
			}
		}
	}

	oseMsc.payment.buildForm = function()	{
		var fp = new Ext.FormPanel({
			border: false
			,id: 'payment-form-panel'
			,items: [{
				ref: 'payment'
	 			,xtype: 'panel'
	 			,border: false
			}]
			,reader:new Ext.data.JsonReader({
			    root: 'results'
			    ,totalProperty: 'total'
			    ,idProperty: 'id'
			    ,fields:[
				    {name: 'bill.addr1', type: 'string', mapping: 'addr1'}
				    ,{name: 'bill.city', type: 'string', mapping: 'city'}
				    ,{name: 'bill.state', type: 'string', mapping: 'state'}
				    ,{name: 'bill.postcode', type: 'string', mapping: 'postcode'}
			  	]
		  	})
			,buttons: [{
				text: 'OK'
				,id: 'submitBtnOk'
			}]
	
		})
		
		return fp;
	}