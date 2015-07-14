Ext.ns('oseMsc','oseMsc.config');
Ext.ns('oseMsc.config.currencyParams');

oseMsc.config.currencyInit = function()	{

}

oseMsc.config.currencyInit.prototype = {
		init: function()	{
			oseMsc.config.currencyParams.openCWin = function(isNew)	{
		  		if(!newCurrencyWin)	{
		  			var newCurrencyWin = new Ext.Window({
		  				title: Joomla.JText._('Multiple_Currency')
		  				,width: 500
		  				,id:'multiple-currency'
						,height: 200
						,border: false
						,modal: true
						,bodyStyle: 'padding: 10px'
		  				,items:[{
		  					xtype: 'form'
		  					,ref: 'form'
							,labelWidth: 250
							,border: false
		  					,items:[{
		  						fieldLabel: Joomla.JText._('Currency_Name')
		  						,xtype: 'textfield'
		  						,name: 'currency'
		  						,value: (isNew == true)?'':oseMsc.config.currencyParams.currency
		  					},{
		  						fieldLabel: Joomla.JText._('Exchange_Rate_Primary_Currency_1')
		  						,xtype: 'numberfield'
		  						,name: 'rate'
		  						,value: (isNew == true)?'':oseMsc.config.currencyParams.rate
		  					}]
		  				},
		  				{
		  				buttons:[{
		  					text: Joomla.JText._('Save')
		  					,handler: function()	{
		  						Ext.Msg.wait(Joomla.JText._('Loading'),Joomla.JText._('please_wait'));
		  						newCurrencyWin.form.getForm().submit({
		  							url: 'index.php?option=com_osemsc&controller=config'
		  							//,waitMsg: 'Loading...'
		  							,params: {task: 'saveMCurrency'}
		  							,success: function(form, action)	{
		  								Ext.Msg.hide();
		  								oseMsc.formSuccess(form, action);
		  								oseMsc.config.currencyParams.grid.getStore().reload()
		  								oseMsc.config.currencyParams.grid.getView().refresh()
		  							}
		  						})
		  					}
		  				}]
		  				}
		  				]
		  			})
		  		}

		  		newCurrencyWin.show().alignTo(Ext.getBody(),'c-c')
		  	}


			oseMsc.config.currencyParams.reader = new Ext.data.JsonReader({
			    root: 'result',
			    totalProperty: 'total',
			    fields:[
				    {name: 'id', type: 'int', mapping: 'id'}
				    ,{name: 'primary_currency', type: 'string', mapping: 'primary_currency'}
			  	]
		  	})

			oseMsc.config.currencyParams.mainC = new Ext.FormPanel({
				border: false
				,labelWidth: 150
				,reader: oseMsc.config.currencyParams.reader
				,items:[{
					xtype: 'fieldset'
					,title: Joomla.JText._('Primary_Currency')
					,bodyStyle:'padding:10px 10px 0'
					,items:[{
						fieldLabel: Joomla.JText._('Primary_Currency')
						,layout: 'hbox'
						,border: false
						,items:	[{
							xtype: 'displayfield'
							,name: 'primary_currency'
							,itemId: 'primary_currency'
							,width: 100
							,style: 'padding-top: 9px'
						},{
							xtype: 'button'
							,text: Joomla.JText._('Edit')
							,handler: function()	{
								var fVal = oseMsc.config.currencyParams.mainC.getForm().findField('primary_currency').getValue();
								if(!currencyEditWin)	{
									var currencyEditWin = new Ext.Window({
										title: Joomla.JText._('Edit_Primary_Currency')
										,width: 500
										,height: 150
										,border: false
										,modal: true
										,bodyStyle: 'padding: 10px'
										,items:[{
											xtype: 'form'
											,ref: 'form'
											,labelWidth: 200

											,border: false
											,items:[{
												fieldLabel: Joomla.JText._('Primary_Currency')
												,xtype: 'textfield'
												,name: 'primary_currency'
												//,emptyText: 'USD'
												,value: fVal//.length > 0?fVal:''
											}]
										}]
										,buttons: [{
											text: Joomla.JText._('Save')
											,handler: function()	{
												Ext.Msg.wait(Joomla.JText._('Loading'),Joomla.JText._('please_wait'));
												currencyEditWin.form.getForm().submit({
													url: 'index.php?option=com_osemsc&controller=config'
													,params:{task:'save',config_type:'currency'}
													//,waitMsg: 'Loading...'
													,success: function(form,action){
														Ext.Msg.hide();
														oseMsc.formSuccess(form,action)
														var wVal = currencyEditWin.form.getForm().findField('primary_currency').getValue();
														//alert(wVal);
														oseMsc.config.currencyParams.mainC.getForm().findField('primary_currency').setValue(wVal);
													}
												})
											}
										}]
									})
								}

								currencyEditWin.show().alignTo(Ext.getBody(),'c-c')
							}
						}]

					}]
				}]

		  	})

		  	oseMsc.config.currencyParams.gridSm = new Ext.grid.CheckboxSelectionModel({
				singleSelect:false
				,listeners: {
					selectionchange: function(sm)	{
						oseMsc.config.currencyParams.grid.getTopToolbar().editBtn.setDisabled(sm.getCount()<1)
						oseMsc.config.currencyParams.grid.getTopToolbar().removeBtn.setDisabled(sm.getCount()<1)
					}
					,rowselect: function(sm,i,r)	{
						oseMsc.config.currencyParams.currency = r.data.currency;
						oseMsc.config.currencyParams.rate = r.data.rate;
					}
				}
			});

		  	oseMsc.config.currencyParams.gridStore = new Ext.data.Store({
			    proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=config'
		            ,method: 'POST'
		      	})
			  	,baseParams:{task:'getCurrencyTypes',name:'primary_currency',config_type:'currency'}
			  	,reader: new Ext.data.JsonReader({
				    root: 'results'
				    ,totalProperty: 'total'
				    ,idProperty: 'currency'
			  	},[
				    {name: 'currency', type: 'string', mapping: 'currency'}
				    ,{name: 'rate', type: 'string', mapping: 'rate'}
			  	])
			  	,autoLoad:{}
			})

		  	oseMsc.config.currencyParams.grid = new Ext.grid.GridPanel({
		  		store: oseMsc.config.currencyParams.gridStore
		  		,cm: new Ext.grid.ColumnModel({
			        defaults: {
			            sortable: false
			        },
			        columns: [
			        	oseMsc.config.currencyParams.gridSm
			        	,new Ext.grid.RowNumberer({header:'#'})
			            ,{header: Joomla.JText._('Currency'), dataIndex: 'currency', width: 150}
			            ,{id: 'rate', header: Joomla.JText._('Rate'),  hidden:false, dataIndex: 'rate', width: 20}
			        ]
			    })
		  		,sm: oseMsc.config.currencyParams.gridSm

		  		,bbar:new Ext.PagingToolbar({
		    		pageSize: 20,
		    		store: oseMsc.config.currencyParams.gridStore,
		    		displayInfo: true,
		    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
					emptyMsg: Joomla.JText._("No_topics_to_display")

			    })
		  		,tbar: new Ext.Toolbar({
				    items: [{
			    		ref:'addBtn'
			            ,iconCls: 'icon-user-add'
			            ,text: Joomla.JText._('Add')
			            ,handler: function()	{
			            	oseMsc.config.currencyParams.openCWin(true)
			            }
			        },{
			        	ref: 'editBtn'
			            ,iconCls: 'icon-user-edit'
			            ,text: Joomla.JText._('Edit')
			            ,disabled: true
			            ,handler: function()	{
			            	oseMsc.config.currencyParams.openCWin(false)
			            }
			        },{
			        	ref: 'removeBtn'
			            ,iconCls: 'icon-user-delete'
			            ,text: Joomla.JText._('Remove')
			            ,disabled: true
			            ,handler: function()	{
			            	Ext.Ajax.request({
			            		url: 'index.php?option=com_osemsc&controller=config'
			            		,params:{task: 'removeCurrency',currency: oseMsc.config.currencyParams.currency}
			            		,success: function(response, opt)	{
			            			oseMsc.ajaxSuccess(response, opt)
			            		}
			            	})

			            }
			        },{
			        	ref: 'removeAllBtn'
			            ,iconCls: 'icon-user-delete'
			            ,text: Joomla.JText._('Remove_All')
			            //,disabled: true
			            ,handler: function()	{
			            	Ext.Ajax.request({
			            		url: 'index.php?option=com_osemsc&controller=config'
			            		,params:{task: 'removeAllCurrency',currency: oseMsc.config.currencyParams.currency}
			            		,success: function(response, opt)	{
			            			oseMsc.ajaxSuccess(response, opt)
			            		}
			            	})

			            }
			        }]
				})
		  		,viewConfig: {forceFit: true}
		  		,height: 300
		  	})

			oseMsc.config.currency = new Ext.Panel({
				title:Joomla.JText._('Currency')
				,border: false
				,labelWidth: 150
				,autoHeight: true
				,bodyStyle:'padding:10px 10px 0'

				,reader:	oseMsc.config.currencyParams.reader

				,items:[
					oseMsc.config.currencyParams.mainC
				,{
					xtype: 'fieldset'
					,bodyStyle:'padding:10px 10px 0'
					,title: Joomla.JText._('Exchange_Rate')
					,items: [oseMsc.config.currencyParams.grid]
				}]



				,listeners: {
					render: function(p){
						oseMsc.config.currencyParams.mainC.getForm().load({
							url: 'index.php?option=com_osemsc&controller=config'
							,params:{task:'getConfig',config_type:'currency'}
							,success: function(form,action)	{
								var data = action.result;
							}
						});

					}
				}

			})
		}
}
	