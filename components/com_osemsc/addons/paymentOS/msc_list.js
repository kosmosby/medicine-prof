Ext.ns('oseMscAddon','oseMscAddon.mscListParams');
Ext.ns('oseMsc','oseMsc.payment');

	oseMscAddon.msc_list = function(){
		this.getList = function()	{
			var combo = new Ext.form.ComboBox({
		  		itemId:'msc_id'
		        ,fieldLabel: Joomla.JText._('Membership_List')
		        ,editable: false
		        ,hiddenName: 'msc_id'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:true
			    ,lastQuery:''
			    ,mode: 'remote'
			    ,forceSelection: true
			    ,store: new Ext.data.Store({
			  		proxy: new Ext.data.HttpProxy({
		            	url: 'index.php?option=com_osemsc&controller=memberships',
			            method: 'POST'
		     	 	})
				  	,baseParams:{task: "action", action:'register.msc.getList',msc_id: ''}
				  	,reader: new Ext.data.JsonReader({
				    	root: 'results',
					    totalProperty: 'total'
				  	},[
					    {name: 'id', type: 'int', mapping: 'id'},
					    {name: 'title', type: 'string', mapping: 'title'}
				  	])
			  		,sortInfo:{field: 'id', direction: "ASC"}
				  	,listeners: {
				    	load: function(s,r,i)	{
				    		combo.setValue(r[0].data.id)
				    		combo.fireEvent('select',combo,r[0],0);
				    	}
				    }
				    //,autoLoad:{}
				})

			    ,valueField: 'id'
			    ,displayField: 'title'
			    ,listeners: {
			    	select: function(c,r,i)	{
			    		//option.getStore().load({params:{msc_id:r.data.id}});
			    		//oseMsc.payment.msc_id = r.data.id
			    	}
			    }
		  	});

		  	return combo;
		}

		this.getOption = function ()	{
			option = new Ext.form.ComboBox({
				itemId:'msc_option'
		        ,fieldLabel: Joomla.JText._('Membership_Option')
		        ,editable: false
		        ,hiddenName: 'msc_option'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:true
			    ,lastQuery:''
			    ,mode: 'remote'
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
					    {name: 'id', type: 'string', mapping: 'id'},
					    {name: 'text', type: 'string', mapping: 'text'}
				  	])
			  		,sortInfo:{field: 'id', direction: "ASC"}
				  	,listeners: {
				    	load: function(s,r,i)	{
				    		option.setValue(r[0].data.id)
				    		//oseMsc.payment.msc_option = r[0].data.id
				    		option.fireEvent('select',option,r[0]);
				    	}
				    }
				})
			    ,valueField: 'id'
			    ,displayField: 'text'
			    ,listeners: {
			    	select: function(c,r,i)	{
			    		oseMsc.payment.msc_option = r.data.id
			    		Ext.Ajax.request({
			    			url: 'index.php?option=com_osemsc'
							,params:{
								controller: 'register', task: "action", action:'register.msc.saveOption'
								,msc_id: oseMsc.payment.msc_id,msc_option: oseMsc.payment.msc_option
							}
			    		})
			    	}
			    }
		  	});

		  	return option;
		}

		this.getCurrency =  function(option)	{
	  		return new Ext.Panel({
				autoLoad:{
					url: 'index.php?option=com_osemsc&controller=register'
					,params: {task: 'action', action: 'register.msc.getCurrencyListWithExit'}
					,callback: function()	{
						Ext.fly('ose_currency').on('change',function(event,el,o)	{

							Ext.Ajax.request({
								url: 'index.php?option=com_osemsc&controller=register'
								,params:{task: 'changeCurrency','ose_currency':Ext.fly('ose_currency').getValue()}
								,callback: function()	{
									option.getStore().reload();
								}
							})
						})
					}
				}
				,border: false
			})
	  	}
	}

	oseMscAddon.msc_list.prototype = {
		init: function()	{
			var list = this.getList();
			var option = this.getOption();
			var currency = this.getCurrency(option);

			list.on('select',function(c,r,i)	{
	    		option.getStore().load({params:{msc_id:r.data.id}});
	    		oseMsc.payment.msc_id = r.data.id
	    	})

	    	list.getStore().load();

	    	return new Ext.form.FieldSet({
				title: Joomla.JText._('Membership_Type')
				,labelWidth: 150
				,items:[{
					layout: 'hbox'
					,fieldLabel: Joomla.JText._('Membership_List')
					,border: false
					,items:[
						list
						,{
							html: '<a href="javascript:void(0)">show all memberships</a>'
							,border: false
							,bodyStyle: 'padding-top:3px;padding-left:3px'
							,listeners:{
								render: function(p)	{
									p.getEl().on('click',function(){
										Ext.Ajax.request({
											url: 'index.php?option=com_osemsc&controller=register'
											,params: {task: 'removeCartItem', entry_id: 0}
											,callback: function()	{
												list.getStore().reload();
											}
										})
									})
								}
							}
						}
					]
				},{
					layout: 'hbox'
					,fieldLabel: Joomla.JText._('Membership_Option')
					,border: false
					,items:[
						option
						,{
							html: Joomla.JText._('Currency')
							,bodyStyle: 'padding-top:3px;padding-left:3px;padding-right:3px'
							,border:false
						}
						,currency
					]
				}]
			})
		}
	}