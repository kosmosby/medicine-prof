Ext.ns('oseMscAddon','oseMscAddon.mscListParams');
Ext.ns('oseMsc','oseMsc.reg');

	oseMscAddon.msc_list = function(fp){
		this.fp = fp
		this.getList = function()	{
			var combo = new Ext.form.ComboBox({
		  		itemId:'msc_id'
		  		,id:'msc-id'
		        ,fieldLabel: Joomla.JText._('Membership_List')
		        ,hiddenName: 'msc_id'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:true
			    ,width: 280
			    ,listWidth: 350
			    ,editable: false
			    ,lastQuery:''
			    ,mode: 'remote'
			    //,autoSelect: true
			    ,forceSelection: true
			    ,store: new Ext.data.Store({
			  		proxy: new Ext.data.HttpProxy({
		            	url: 'index.php?option=com_osemsc&controller=memberships'
			            ,method: 'POST'
		     	 	})
				  	,baseParams:{task: "action", action:'register.msc.getList',msc_id: ''}
				  	,reader: new Ext.data.JsonReader({
				    	root: 'results'
					    ,totalProperty: 'total'
				  	},[
					    {name: 'id', type: 'int', mapping: 'id'}
					    ,{name: 'title', type: 'string', mapping: 'title'}
					    ,{name: 'description', type: 'string', mapping: 'description'}
				  	])
			  		,sortInfo:{field: 'id', direction: "ASC"}
				})

			    ,valueField: 'id'
			    ,displayField: 'title'

		  	});

		  	return combo;
		}

		this.getOption = function ()	{
			option = new Ext.form.ComboBox({
				itemId:'msc_option'
				,id:'msc-option'
		        ,fieldLabel: Joomla.JText._('Membership_Option')
		        ,hiddenName: 'msc_option'
			    ,width: 280
			    ,listWidth: 350
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:true
			    ,editable: false
			    ,lastQuery:''
			    ,mode: 'remote'
			    ,forceSelection: true
			    ,store: new Ext.data.Store({
			  		proxy: new Ext.data.HttpProxy({
		            	url: 'index.php?option=com_osemsc&controller=memberships',
			            method: 'POST'
		     	 	})
				  	,baseParams:{task: "action", action:'register.msc.getOptions'}
				  	,reader: new Ext.data.JsonReader({
				    	root: 'results',
					    totalProperty: 'total'
				  	},[
					    {name: 'id', type: 'string', mapping: 'id'}
					    ,{name: 'msc_id', type: 'string', mapping: 'msc_id'}
					    ,{name: 'title', type: 'string', mapping: 'title'}
					    ,{name: 'trial_price', type: 'string', mapping: 'trial_price'}
					    ,{name: 'standard_price', type: 'string', mapping: 'standard_price'}
					    ,{name: 'trial_recurrence', type: 'string', mapping: 'trial_recurrence'}
					    ,{name: 'standard_recurrence', type: 'string', mapping: 'standard_recurrence'}
					    ,{name: 'has_trial', type: 'string', mapping: 'has_trial'}
					    ,{name: 'isFree', type: 'string', mapping: 'isFree'}
				  	])
			  		,sortInfo:{field: 'id', direction: "ASC"}
				})
			    ,valueField: 'id'
			    ,displayField: 'title'
		  	});

		  	return option;
		}

		this.getCurrency =  function(mode)	{
			var filterOption = this.filterOption;
			var currency = new Ext.form.ComboBox({
				itemId:'currency'
		        ,fieldLabel: Joomla.JText._('Currency')
		        ,hiddenName: 'ose_currency'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:true
			    ,editable: false
			    ,lastQuery:''
			    ,mode: mode
			    ,forceSelection: true
			    ,store: new Ext.data.Store({
			  		proxy: new Ext.data.HttpProxy({
		            	url: 'index.php?option=com_osemsc&controller=memberships',
			            method: 'POST'
		     	 	})
				  	,baseParams:{task: "action", action:'register.msc.getCurrencyListCombo'}
				  	,reader: new Ext.data.JsonReader({
				    	root: 'results'
					    ,totalProperty: 'total'
					    ,idProperty: 'value'
				  	},[
					    {name: 'value', type: 'string', mapping: 'value'}
				  	])
			  		,sortInfo:{field: 'value', direction: "ASC"}
				})
			    ,valueField: 'value'
			    ,displayField: 'value'
			    ,listeners: {

			    }
		  	});

		  	return currency
	  	}

	  	this.filterOption = function(list,option)	{
	  		option.getStore().filter([{
				fn   : function(record) {
					return record.get('msc_id') == list.getValue()
				},
				scope: this
			}]);
	  	}
	}

	oseMscAddon.msc_list.prototype = {
		init: function()	{
			var fp = this.fp;
			var list = this.getList();
			var option = this.getOption();
			var currency = this.getCurrency('local');

			oseMsc.combo.getLocalJsonData(list, oseMsc.mscList);
			oseMsc.combo.getLocalJsonData(option, oseMsc.mscOptions);
			oseMsc.combo.getLocalJsonData(currency, oseMsc.currency);

			var t = new Ext.Template(
			    '<table cellspacing="2px" width="430px">',
			    	'<thead class="osemsc-table-header">',
			    		'<tr>',
			    			'<th width = "80%"><b>'+Joomla.JText._('Subscription')+'</b></th>',
			    			'<th width = "20%"><b>'+Joomla.JText._('Price')+'</b></th>',
			    		'</tr>',
			    	'</thead>',
			        '<tbody>',
			        	'<tr class="membershipSummary-price">',
			    			'<td>{title}</td>',
			    			'<td>{standard_price}</td>',
			    		'</tr>',
			        '</tbody>',
			    '</table>',
			    // a configuration object:
			    {
			        compiled: true,      // compile immediately
			        disableFormats: true // See Notes below.
			    }
			);

			var t2 = new Ext.Template(
			    '<table cellspacing="2px" width="430px">',
			    		'<tr>',
			    			'<td width = "20%"><b>'+Joomla.JText._('Subscription')+'</b></th>',
			    			'<td width = "20%">{title}</td>',
			    		'</tr>',
			        	'<tr class="membershipSummary-price">',
			    			'<td width = "20%">'+Joomla.JText._('TRIAL_PERIOD')+'</th>',
			    			'<td width = "20%">{trial_price} / {trial_recurrence}</td>',
			    		'</tr>',
			        	'<tr class="membershipSummary-price">',
			    			'<td width = "20%">'+Joomla.JText._('Standard_Price_After_Trial')+'</th>',
			    			'<td width = "20%">{standard_price} / {standard_recurrence}</td>',
			    		'</tr>',
			    '</table>',
			    // a configuration object:
			    {
			        compiled: true,      // compile immediately
			        disableFormats: true // See Notes below.
			    }
			);

			var twin = new Ext.XTemplate(
			    '<table cellspacing="2px" width="100%">',
			    	'<thead class="osemsc-table-header">',
			    		'<tr>',
			    			'<th>'+Joomla.JText._('Subscription')+'</th>',
			    			'<th>'+Joomla.JText._('Subscription_Length')+'</th>',
			    			'<th>'+Joomla.JText._('Billing_Amount')+'</th>',
			    		'</tr>',
			    	'</thead>',
			        '<tbody>',
			        	'<tr>',
			    			'<td>{title}</td>',
			    			'<td>{standard_recurrence}</td>',
			    			'<td>{standard_price}</td>',
			    		'</tr>',
			        '</tbody>',
			    '</table>',
			    '<div class="notes">'+Joomla.JText._('SUBSCRITPION_EXPLANATIONS')+'</div>',
			    '<div class="about-intro"><div class="about-head">'+Joomla.JText._('ABOUT_THE_MEMBERSHIP_PLAN')+'</div><div class="about-body">{description}</div></div>',
			    {
			        compiled: true      // compile immediately
			        ,disableFormats: true // See Notes below.

			    }
			);

		var twin2 = new Ext.XTemplate(
			    '<table cellspacing="2px" width="100%">',
			    	'<thead class="osemsc-table-header">',
			    		'<tr>',
			    			'<th>{title}</th>',
			    			'<th>'+Joomla.JText._('Subscription_Length')+'</th>',
			    			'<th>'+Joomla.JText._('Billing_Amount')+'</th>',
			    		'</tr>',
			    	'</thead>',
			        '<tbody>',
			    		'<tr>',
			    			'<td>'+Joomla.JText._('TRIAL_PERIOD')+'</td>',
			    			'<td>{trial_recurrence}</td>',
			    			'<td>{trial_price}</td>',
			    		'</tr>',
			    		'<tr>',
			    			'<td>'+Joomla.JText._('Standard_Price_After_Trial')+'</td>',
			    			'<td>{standard_recurrence}</td>',
			    			'<td>{standard_price}</td>',
			    		'</tr>',
			        '</tbody>',
			    '</table>',
			    '<div class="notes">'+Joomla.JText._('SUBSCRITPION_EXPLANATIONS')+'</div>',
			    '<div class="about-intro"><div class="about-head">'+Joomla.JText._('ABOUT_THE_MEMBERSHIP_PLAN')+'</div><div class="about-body">{description}</div></div>',
			    {
			        compiled: true      // compile immediately
			        ,disableFormats: true // See Notes below.

			    }
			);
			var fs = new Ext.form.FieldSet({
				title: Joomla.JText._('Membership_Type')
				,id:'membership-type-info'
				,labelWidth: 130
				,items:[list,option,currency,
				{
					ref: 'info'
					,border: false
					,bodyStyle: 'padding-top: 10px'
				},{
					html:'<span class="notes">'+Joomla.JText._('Taxes_if_any_will_be_updated_on_the_next_page')+'</span>'
					,border: false
					,bodyStyle: 'text-align: right'
				},{
					xtype: 'button'
					,text: Joomla.JText._('ABOUT_THE_MEMBERSHIP_PLAN')
					,handler: function()	{
						var i = option.getStore().find('id',option.getValue())
						var s = option.getStore().getAt(i);
						//alert(s.data.toSource())
					    var msc_id = s.data.msc_id;
					    s.data.description = list.getStore().getById(msc_id).data.description;
					       
						if (s.data.trial_price!='')
						{
							new Ext.Window({
								title: Joomla.JText._('Information')
								,width: 600
								,bodyStyle: 'padding: 10px'
								,html: twin2.apply(s.data)
							}).show().alignTo(Ext.getBody(),'c-c',[0,10]);
						}
						else
						{
							new Ext.Window({
									title: Joomla.JText._('Information')
									,width: 600
									,bodyStyle: 'padding: 10px'
									,html: twin.apply(s.data)
							}).show().alignTo(Ext.getBody(),'c-c',[0,10]);
						}
					}
				}]
			})

			currency.addListener('select',function(c,r)	{
				Ext.Ajax.request({
					url: 'index.php?option=com_osemsc&controller=register'
					,params:{task: 'changeCurrency','ose_currency':r.get('value')}
					,callback: function()	{
						filterOption = this.filterOption;
						fs.getEl().mask(Joomla.JText._('Reloading'));
						option.getStore().reload({
							scope: this
							,callback: function(el,success,response,opt)	{
								var optR = option.getStore().getById(oseMsc.reg.msc_option);
								fs.getEl().unmask();
								filterOption(list,option)
								option.fireEvent('select',option,optR);
							}
						});
					}
					,scope: this
				})
			}, this)

			list.on('select',function(c,r,i)	{
	    		oseMsc.reg.msc_id = r.data.id

				var sr = r;

				this.filterOption(list,option);

				if(option.getStore().getCount() > 0)	{
					option.setValue(option.getStore().getAt(0).get('id'))
					option.fireEvent('select',option,option.getStore().getAt(0),0)
				}	else	{
					option.setValue('');
				}

				if(typeof(Ext.getCmp('terms-fs')) != 'undefined')	{
					oseMscAddon.checkboxAmount = 0;
	    			Ext.each(Ext.getCmp('terms-fs').findByType('panel'),function(item,i,all){
	    				if(typeof(item.term_msc_id) != 'undefined')
	    				{
		    				if(item.term_msc_id > 0 )
		    				{
		    					if(item.term_msc_id == oseMsc.reg.msc_id)
								{
		    						oseMscAddon.checkboxAmount+=1;
									item.setVisible(true);
								}else{
									item.setVisible(false);
								}
		    				}else{
		    					oseMscAddon.checkboxAmount+=1;
		    				}
	    				}
											
					});
	    		}
				
				if(typeof(Ext.getCmp('ose-reg-payment')) != 'undefined')	{
					Ext.getCmp('ose-reg-payment').findById('payment_payment_method').getStore().reload({
	    				params:{'msc_id':oseMsc.reg.msc_id}
	    			});
				}
				
	    	},this)

	    	option.on('select',function(c,r,i)	{
	    		//var free = (r.data.title.indexOf('Free')>0)
				var free = (r.get('isFree')==true)?true:false;
	    		oseMsc.reg.msc_option = r.data.id
	    		Ext.Ajax.request({
	    			url: 'index.php?option=com_osemsc'
					,params:{
						controller: 'register', task: "action", action:'register.msc.saveOption'
						,msc_id: oseMsc.reg.msc_id,msc_option: oseMsc.reg.msc_option
					}
	    		})
	    		if (r.data.trial_price!='')
	    		{
	    			fs.info.update(t2.apply(r.data));
	    		}
	    		else
	    		{
	    			fs.info.update(t.apply(r.data));
				}
	    		if(typeof(Ext.getCmp('ose-reg-payment')) != 'undefined')	{
	    			(free==true)?Ext.get('ose-reg-payment').mask():Ext.get('ose-reg-payment').unmask();
	    			
					//var paymentMethodCombo = this.fp.getForm().findField('payment.payment_method');
					//var paymentMethodCombo_i = paymentMethodCombo.getStore().findExact('code',paymentMethodCombo.getValue());
					//paymentMethodCombo.fireEvent('select',paymentMethodCombo,paymentMethodCombo.getStore().getAt(paymentMethodCombo_i))
	    			/*
	    			Ext.each(Ext.getCmp('ose-reg-payment').findByType('textfield'),function(item,i,all){
						item.setDisabled(free)
					});
					*/
	    			var paymentMethodCombo = this.fp.getForm().findField('payment.payment_method');
	    			var payment_method = paymentMethodCombo.getValue();
	    			switch (payment_method)	{
		    			case('paypal_cc'):
		    			case('authorize'):
		    			case('eway'):
		    			case('beanstream'):
		    			//case('pnw'):

			    			Ext.each(Ext.getCmp('ose-reg-payment').findByType('field'), function(item,i,all){
			    				if(item.getXType() != 'compositefield')	{
				    				item.setDisabled(free);
					    			item.allowBlank=free;
				    			}	else	{
				    				Ext.each(item.items.items, function(subitem,i,all){
				    					subitem.setDisabled(free);
						    			subitem.allowBlank=free;
				    				})
				    			}
							});
	    				break;
					}
	    			if((free==true && oseMsc.hide_payment==true) == true)
	    			{
	    				Ext.each(Ext.getCmp('ose-reg-payment').findByType('field'), function(item,i,all){
		    				if(item.getXType() != 'compositefield')	{
			    				item.setVisible(false);	
			    			}	else	{
			    				Ext.each(item.items.items, function(subitem,i,all){
			    					subitem.setVisible(false);	
			    				})
			    			}
						});
	    				Ext.getCmp('ose-reg-payment').setVisible(false);
	    			}else{
	    				Ext.each(Ext.getCmp('ose-reg-payment').findByType('field'), function(item,i,all){
		    				if(item.getXType() != 'compositefield')	{
			    				item.setVisible(true);	
			    			}	else	{
			    				Ext.each(item.items.items, function(subitem,i,all){
			    					subitem.setVisible(true);	
			    				})
			    			}
						});
	    				Ext.getCmp('ose-reg-payment').setVisible(true);
	    			}	
	    		}

				if(typeof(Ext.getCmp('ose-reg-billinginfo')) != 'undefined')	{
	    			Ext.each(Ext.getCmp('ose-reg-billinginfo').findByType('textfield'),function(item,i,all){
						item.setDisabled(free);
						if(item.getName() == 'bill.addr1' && free==true)	{
							item.allowBlank = true;
						}
						else if(item.getName() == 'bill.addr1' && free==false)
						{
							item.allowBlank = false;
						}
					});
					(free==true)?Ext.get('ose-reg-billinginfo').mask():Ext.get('ose-reg-billinginfo').unmask();
					if((free==true && oseMsc.hide_billinginfo==true) == true)
					{
						Ext.each(Ext.getCmp('ose-reg-billinginfo').findByType('field'),function(item,i,all){
							item.setVisible(false);							
						});
						Ext.getCmp('ose-reg-billinginfo').setVisible(false);
					}else{
						Ext.each(Ext.getCmp('ose-reg-billinginfo').findByType('field'),function(item,i,all){
							item.setVisible(true);							
						});
						Ext.getCmp('ose-reg-billinginfo').setVisible(true);
					}				
	    		}

	    		if(typeof(Ext.getCmp('ose-reg-renewal-pref')) != 'undefined')	{
	    			(free==true)?Ext.get('ose-reg-renewal-pref').mask():Ext.get('ose-reg-renewal-pref').unmask();
	    			Ext.each(Ext.getCmp('ose-reg-renewal-pref').findByType('radiogroup'),function(item,i,all){
						item.setDisabled(free);
					});
	    		}

	    		this.fp.doLayout();
	    	},this);

	    	return fs;

		}
	}