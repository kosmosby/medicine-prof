Ext.ns('oseMscAddon','oseMscAddon.mscListParams');
Ext.ns('oseMsc','oseMsc.reg');

	oseMscAddon.msc_cart = function(fp){
		this.fp = fp
		this.getList = function()	{
			var combo = new Ext.form.ComboBox({
		  		itemId:'msc_id'
		        ,fieldLabel: Joomla.JText._('Membership_List')
		        ,hiddenName: 'msc_id'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:true
			    ,listWidth: 350
			    ,editable: false
			    ,hidden:true
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
		        ,fieldLabel: Joomla.JText._('Membership_Option')
		        ,hiddenName: 'msc_option'
		  		,listWidth: 350
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:true
			    ,editable: false
			    ,hidden:true
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
		        ,fieldLabel: 'Your preferred Currency'
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

	oseMscAddon.msc_cart.prototype = {
		init: function()	{
			var fp = this.fp;
			//var list = this.getList();
			//var option = this.getOption();
			var currency = this.getCurrency('local');
			oseMsc.combo.getLocalJsonData(currency, oseMsc.currency);

			var tpl = new Ext.XTemplate(
				'<br/><div style="font-size:9px"> Note: If you would like to use other currencies, please change it to your preferred currency in the above drop-down list.</div> <br/>',
				'<table cellspacing="2px" width="400px">',
			    	'<thead class="osemsc-table-header">',
			    		'<tr>',
			    			'<th width = "60%"><b>Subscription</b></th>',
			    			'<th width = "20%"><b>Price</b></th>',
			    			'<th width = "20%"><b>Action</b></th>',
			    		'</tr>',
			    	'</thead>',
			    	'<tbody>',
			    	'<tpl for=".">',
			        	'<tr class="membershipSummary-price">',
			    			'<td>{title}</td>',
			    			'<td>{standard_price}</td>',
			    			'<td><div class="view-remove"></div></td>',
			    		'</tr>',
			    	'</tpl>',
			        '</tbody>',
			    '</table>'
			);


			var store = new Ext.data.JsonStore({
			    url: 'index.php?option=com_osemsc&controller=register'
			    ,baseParams:{task: "action", action:'register.msc.getSelectedMsc'}
			    ,root: 'results'
			    ,idProperty: 'total'
			    ,fields: [
			        {name: 'title', type: 'string', mapping: 'title'}
			        ,{name: 'standard_price', type: 'string', mapping: 'standard_price'}
			        ,{name: 'entry_id', type: 'string', mapping: 'entry_id'}
			        ,{name: 'entry_type', type: 'string', mapping: 'entry_type'}
			    ]
			    ,autoLoad:{}
			});


			var dataView = new Ext.DataView({
		        store: store
		        ,tpl: tpl
		        ,id:'msc_cart'
		        ,autoHeight: true
		        ,multiSelect: true
		        //,overClass:'x-view-over'
		        ,itemSelector:'div.view-remove'
		        ,emptyText: 'No membership in the cart'
		        ,listeners:{
		        	click: function(dv,i,node,e)	{
		        		var r = dv.getStore().getAt(i);

		        		dataView.getEl().mask('Pleas wait...','Refreshing')
						Ext.Ajax.request({
							url: 'index.php?option=com_osemsc&controller=register'
							,params: {task: 'removeCartItem',entry_id:r.get('entry_id'),entry_type:r.get('entry_type')}
							,success: function(response,opt)	{
								//oseMsc.reg.panel.fireEvent('load');
								dataView.getEl().unmask();
								dataView.getStore().removeAt(i);
								dataView.refresh()

								fs.getComponent('result-panel').fireEvent('load')
							}
						})
		        	}
		        }
		    });

		    //oseMsc.combo.getLocalJsonData(dataView,oseMsc.selectedMsc);

		    var tplSubtotal = new Ext.XTemplate(
				'<table cellspacing="2px" width="400px">',
			    	'<tbody>',
			        	'<tr>',
			    			'<td align="right">Subtotal: {subtotal}</td>',
			    		'</tr>',
			        '</tbody>',
			    '</table>',
			    {
			        compiled: true,
			        disableFormats: true
    			}
			);

			var fs = new Ext.form.FieldSet({
				title: Joomla.JText._('Membership_Type')
				,id:'membership-type-info'
				,labelWidth: 150
				,items:[currency,dataView
				,{
					border: false
					,itemId: 'result-panel'
					,listeners: {
						load: function()	{
							this.load({
								url: 'index.php?option=com_osemsc'
								,params:{controller: 'register', task: 'action', action: 'register.payment.getSubTotal'}
								,callback: function(el,success,response,opt)	{
									var msg = Ext.decode(response.responseText);

									tplSubtotal.overwrite(this.body,msg.content)

								}
								,scope:this
							})
						}
						,render: function(p)	{
							p.fireEvent('load');
						}
					}
				},{
					html:'<a href="index.php?option=com_osemsc&view=memberships">Continue Shopping</a>'
					,border: false
					//,bodyStyle: 'text-align: right'
				},{
					html:'<span class="notes">Taxes (if any) will be updated on the next page</span>'
					,border: false
					,bodyStyle: 'text-align: right'
				}]
			})

			currency.addListener('select',function(c,r)	{
				Ext.Ajax.request({
					url: 'index.php?option=com_osemsc&controller=register'
					,params:{task: 'changeCurrency','ose_currency':r.get('value')}
					,callback: function()	{
						filterOption = this.filterOption;
						fs.getEl().mask('Reloading...');
						dataView.getStore().reload({
							scope: this
							,callback: function(el,success,response,opt)	{
								fs.getEl().unmask();
								dataView.getStore().reload();
								dataView.refresh();
								fs.getComponent('result-panel').fireEvent('load')
							}
						});
					}
					,scope: this
				})
			}, this)

		  	return fs;
		}
	}
