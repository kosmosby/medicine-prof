Ext.ns('oseMscAddon');
	oseMscAddon.coupon = function(fp)	{
		this.fp = fp;
		this.getCouponText = function()	{
			var fp = this.fp;
			return new Ext.Panel({
				bodyStyle:'margin-bottom:3px; border: 0px;'
				,ref: 'coupon'
				,html: Joomla.JText._('COUPON_INITIALIZING')
				,listeners:{
					load: function()	{
						this.load({
							url: 'index.php?option=com_osemsc'
							,params:{
								controller:'register'
								,task:'action'
								,action:'register.coupon.getCurrentCode'
							}
							,callback: function(el,success,response,opt)	{
								//el.update('');
								var msg = Ext.decode(response.responseText);
								el.update(msg);
								fp.doLayout()
							}
							,scope: this
						})

					}
					,render: function(p)	{
						p.fireEvent('load');
					}
				}
			})
		};

		this.getCouponField = function()	{
			return new Ext.form.TextField({
				name: 'coupon_code'
			})
		}
	}

	oseMscAddon.coupon.prototype = {
		init: function()	{
			var couponText = this.getCouponText();
			var couponField = this.getCouponField();
			
			var changeOSEFormStatus = function(is_free_manual)	{
				
				if(typeof(Ext.getCmp('ose-reg-billinginfo')) != 'undefined')	{
					(is_free_manual==true)?Ext.get('ose-reg-billinginfo').mask():Ext.get('ose-reg-billinginfo').unmask();
					Ext.each(Ext.getCmp('ose-reg-billinginfo').findByType('field'),function(item,i,all){
						item.setDisabled(is_free_manual);
						if(item.getName() == 'bill.addr1')	{
							item.allowBlank = is_free_manual;
						}
						if(oseMsc.hide_billinginfo==true)
						{
							item.setVisible(!is_free_manual);	
						}	
					});
					if(oseMsc.hide_billinginfo==true)
					{
						Ext.getCmp('ose-reg-billinginfo').setVisible(!is_free_manual);
					}
				}
				
				if(typeof(Ext.getCmp('ose-reg-payment')) != 'undefined')	{
					(is_free_manual==true)?Ext.get('ose-reg-payment').mask():Ext.get('ose-reg-payment').unmask();
					var paymentMethodCombo = Ext.getCmp('ose-reg-payment').findById('payment_payment_method');
					var payment_method = paymentMethodCombo.getValue();
					switch (payment_method)	{
		    			case('paypal_cc'):
		    			case('authorize'):
		    			case('eway'):
		    			case('beanstream'):
		    				Ext.each(Ext.getCmp('ose-reg-payment').findByType('field'), function(item,i,all){
			    				if(item.getXType() != 'compositefield')	{
				    				item.setDisabled(is_free_manual);
					    			item.allowBlank=is_free_manual;
					    			if(oseMsc.hide_payment==true)
									{
										item.setVisible(!is_free_manual);	
									}	
				    			}	else	{
				    				Ext.each(item.items.items, function(subitem,i,all){
				    					subitem.setDisabled(is_free_manual);
						    			subitem.allowBlank=is_free_manual;
						    			if(oseMsc.hide_payment==true)
										{
						    				subitem.setVisible(!is_free_manual);	
										}	
				    				})
				    			}
							});
		    			break;
					}	
										
					if(oseMsc.hide_payment==true)
					{
						Ext.getCmp('ose-reg-payment').setVisible(!is_free_manual);
					}			
				}
				
			}
			
			return new Ext.form.FieldSet({
				id: 'ose-coupon-form'
				,title: Joomla.JText._('Coupon')
				,labelWidth: 130
				,items:[
					couponText
				,{
					layout:'hbox'
					,fieldLabel: Joomla.JText._('Coupon_Code')
					,border:false
					,items:[
						couponField
					,{
						xtype: 'button'
						,text: Joomla.JText._('Use')
						,handler: function()	{
							Ext.Ajax.request({
								url: 'index.php?option=com_osemsc&controller=register'
								,params:{
									task:'action',action:'register.coupon.add'
									,coupon_code: couponField.getValue()
								}
								,success: function(response,opt)		{
									oseMsc.ajaxSuccess(response,opt);
									couponText.fireEvent('load');
									var msg = Ext.decode(response.responseText);
									changeOSEFormStatus(msg.is_free_manual);

								}
								,failure: oseMsc.ajaxFailureMB

							})
						}
					}]
				}]
			})
		}
	}