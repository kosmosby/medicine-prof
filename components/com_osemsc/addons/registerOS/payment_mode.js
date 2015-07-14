Ext.ns('oseMscAddon');

	oseMscAddon.payment_mode = function()	{
		this.getRadioGroup = function()	{
			var addonPaymentModeRadioGroup = new Ext.Panel({
				ref: 'rgPanel'
				,border: false
				,autoLoad:{
					url: 'index.php?option=com_osemsc&controller=register'
					,params:{controller: 'register', task: 'action',action:"register.payment_mode.getPaymentMode"}
					,callback: function(el,success,response,opt)	{
						var result = Ext.decode(response.responseText);

					 	addonPaymentModeRadioGroup.update('');
					 	var c = new Array();
					 	Ext.each(result.results, function(item,i,all){
					 		c[i] = {
					 			xtype:'radio'
					 			,boxLabel: (item.payment_mode == 'm')?Joomla.JText._('Manual_Renewal'):Joomla.JText._('Automatic_Renewal')
					 			,name: 'payment.payment_mode'
					 			,inputValue: item.payment_mode
					 			,checked: item.checked
					 		}
					 	})

					 	addonPaymentModeRadioGroup.add({
				 			xtype:'radiogroup'
				 			,id: 'payment_mode'
				 			,name: 'payment.payment_mode'
				 			,columns: 1
				 			,items: c
				 			,listeners: {
				 				change: function(rg,checked)	{
				 					var val = checked.getGroupValue()
				 					Ext.Ajax.request({
				 						url: 'index.php?option=com_osemsc'
										,params:{
											controller: 'register', task: "action", action:'register.payment_mode.savePaymentMode'
											,payment_payment_mode: val
										}
				 					})
				 				}
				 				,render: function(rg)	{
				 					rg.fireEvent('change',rg,rg.getValue())
				 				}
				 			}
				 		});

					 	addonPaymentModeRadioGroup.doLayout();
				 	}
				}
			});

			return addonPaymentModeRadioGroup;
		}
	}

	oseMscAddon.payment_mode.prototype = {
		init: function()	{
			var addonPaymentModeRadioGroup = this.getRadioGroup();
			return new Ext.form.FieldSet({
				title: Joomla.JText._('Membership_Renewal_Preference')
				,id: 'ose-reg-renewal-pref'
				,items: addonPaymentModeRadioGroup//addonMscDataView
			})
		}
	}