Ext.ns('oseMscAddon');
	oseMscAddon.coupon = function()	{
		this.getCouponText = function()	{
			return new Ext.Panel({
				autoLoad:{
					url: 'index.php?option=com_osemsc'
					,params:{controller:'register',task:'action',action:'register.coupon.getCurrentCode'}
					,callback: function(el,success,response,opt)	{
						//el.update('');
						var msg = Ext.decode(response.responseText);
						el.update(msg);
					}
				}
				,bodyStyle:'margin-bottom:3px; border: 0px;'
				,ref: 'coupon'
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
							}
						})
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
			
			return new Ext.form.FieldSet({
				id: 'ose-coupon-form'
				,title: Joomla.JText._('Coupon')
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
								}
								,failure: oseMsc.ajaxFailureMB
								
							})
						}
					}]
				}]
			})
		}
	}