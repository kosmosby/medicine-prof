Ext.ns('oseMscAddon','oseMscAddon.paymentAdvParams');

function genCombo(name, fieldlabel)
{
	var combo = new Ext.form.ComboBox({
		hiddenName: name,
		fieldLabel: fieldlabel,
	    typeAhead: true,
	    triggerAction: 'all',
	    labelStyle: 'min-width: 80px;',
	    lazyRender:true,
	    mode: 'local',
	    store: new Ext.data.ArrayStore({
	        id: 0,
	        fields: [
	            'myId',
	            'displayText'
	        ],
	        data: [[1, Joomla.JText._('ose_Yes')], [0, Joomla.JText._('ose_No')]]
	    }),
	    valueField: 'myId',
	    displayField: 'displayText',
	    listeners:{
			render: function(combo){
				if (combo.getValue()=='')
				{
					combo.setValue(0);
				}
			 }
	}
		    
	});
	
	return combo; 
}

oseMscAddon.paymentAdvParams.createForm = function(){
		this.mfReader = function()	{
			return new Ext.data.JsonReader({
			    root: 'result'
			    ,totalProperty: 'total'
			    ,fields:[
			    	{name: 'id', type: 'string', mapping: 'id'}
				    ,{name: 'idurl', type: 'string', mapping: 'idurl'}
				    ,{name: 'optionname', type: 'string', mapping: 'optionname'}
					,{name: 'paymentAdv_renew_discount', type: 'string', mapping: 'renew_discount'}
					,{name: 'paymentAdv_renew_discount_type', type: 'string', mapping: 'renew_discount_type'}
					,{name: 'paymentAdv_payment_mode', type: 'string', mapping: 'payment_mode'}
					,{name: 'paymentAdv_twoco_productid', type: 'string', mapping: 'twoco_productid'}
					,{name: 'paymentAdv_clickbank_productid', type: 'string', mapping: 'clickbank_productid'}
					,{name: 'paymentAdv_option_visibility', type: 'string', mapping: 'option_visibility'}
					,{name: 'paymentAdv_nosamemembership', type: 'string', mapping: 'nosamemembership'}
				]
		  	})
		}
	}



oseMscAddon.paymentAdvParams.createForm.prototype = {
		init: function(grid)	{
			var reader =  this.mfReader();
			var do_not_show_same_membership = genCombo('paymentAdv_nosamemembership', 'Do not show to members of the same membership plan'); 
			var visibilitycombo = new Ext.form.ComboBox({
				hiddenName: 'paymentAdv_option_visibility',
				fieldLabel: Joomla.JText._('Option_Visitility'),
			    typeAhead: true,
			    triggerAction: 'all',
			    labelStyle: 'min-width: 80px;',
			    lazyRender:true,
			    mode: 'local',
			    store: new Ext.data.ArrayStore({
			        id: 0,
			        fields: [
			            'myId',
			            'displayText'
			        ],
			        data: [[1, Joomla.JText._('Show_to_Members')], [-1, Joomla.JText._('Hide_to_Members')], [0, Joomla.JText._('Show_to_All')]]
			    }),
			    valueField: 'myId',
			    displayField: 'displayText',
			    listeners:{
					render: function(combo){
						if (combo.getValue()=='')
						{
							combo.setValue(0);
						}
					 }
			}
				    
			});
			var addonPaymentFormPanel = new Ext.FormPanel({
				labelWidth: 150
				,height: 380
				,buttons: [{
					text: Joomla.JText._('save')
					,handler: function(){
						addonPaymentFormPanel.getForm().submit({
						    clientValidation: true
						    ,url: 'index.php?option=com_osemsc&controller=membership'
						    ,params: {
						        task: 'action', action : 'panel.paymentAdv.save',msc_id: oseMsc.msc_id
						    }
						    ,success: function(form, action) {
						    	oseMsc.formSuccess(form, action);
	
						    	oseMsc.refreshGrid(grid);
						    }
						    ,failure:  oseMsc.formFailureMB
		    			})
					}
				}]
				,autoScroll: true
				,reader: reader
				,bodyStyle: 'padding: 10px;padding-left: 20px;padding-right: 20px'
				,items:[{
					xtype: 'fieldset'
					,title: Joomla.JText._('Renewal_Setting')
					,items:[{
						fieldLabel: Joomla.JText._('Renewal_Discount')
						,xtype: 'numberfield'
						,name: 'paymentAdv_renew_discount'
					},{
				    	fieldLabel: Joomla.JText._('Discount_type')
			        	,xtype: 'radiogroup'
			    		,name: 'paymentAdv_renew_discount_type'
			    		,defaults: {name: 'paymentAdv_renew_discount_type'}
			    		,columns:1
			    		,items: [{
			    			boxLabel : Joomla.JText._('Percentage')
			    			,inputValue: 'rate'
			    		},{
			    			boxLabel : Joomla.JText._('Absolute_amount')
			    			,inputValue: 'amount'
			    			,checked: true
			    		}]
				    },{
						xtype: 'radiogroup'
						,name: 'paymentAdv_payment_mode'
						,fieldLabel: Joomla.JText._('Payment_Mode')
						,width: 700
						,defaults:{xtype:'radio',name:'paymentAdv_payment_mode'}
						,items:[
							{boxLabel: Joomla.JText._('Manual_Renewing_Only'), inputValue: 'm'}
							,{boxLabel: Joomla.JText._('Automatic_Renewing_Only'), inputValue: 'a'}
							,{boxLabel: Joomla.JText._('Both'), inputValue: 'b', checked: true}
						]
					}]
				},{
					xtype: 'fieldset'
					,title: Joomla.JText._('2Checkout_Payment_Gateway_Setting')
					,items:[{
							fieldLabel: Joomla.JText._('Product_ID')
							,xtype: 'textfield'
							,name: 'paymentAdv_twoco_productid'
						}]
				},{
					xtype: 'fieldset'
					,title: Joomla.JText._('ClickBank_Payment_Gateway_Setting')
					,items:[{
							fieldLabel: Joomla.JText._('Clickbank_Item_Number')
							,xtype: 'textfield'
							,name: 'paymentAdv_clickbank_productid'
						}]
				},
				{
					xtype: 'fieldset'
					,title: Joomla.JText._('Visibility_Setting_This_is_a_very_special_setting_do_not_use_this_function_unless_you_know_how_it_works')
					,items:[
					        visibilitycombo, do_not_show_same_membership
					]
				}
				,{
					xtype: 'hidden'
					,name: 'id'
				}]
			});

			return addonPaymentFormPanel;
		}
		
		,openWin: function(form)	{
			this.win = new Ext.Window({
				title: Joomla.JText._('Payment_Parameter_Setting')
				,items: form
				,width: 800
				,modal: true
			})

			this.win.show().alignTo(Ext.getBody(),'t-t');
		}
	}

	oseMscAddon.paymentAdvParams.openCWin = function(grid,i)	{
		var addonPaymentFormCreate = new oseMscAddon.paymentAdvParams.createForm();
		var addonPaymentForm = addonPaymentFormCreate.init(grid);
		var r = grid.getStore().getAt(i);
		
		addonPaymentForm.getForm().setValues(r.data)
		addonPaymentFormCreate.openWin(addonPaymentForm);
	}

  	oseMscAddon.paymentAdvParams.gridSm = new Ext.grid.RowSelectionModel({
		singleSelect:false
		,listeners: {
			selectionchange: function(sm)	{
				//oseMscAddon.paymentAdv.getTopToolbar().editBtn.setDisabled(sm.getCount()<1);
			}
			,rowselect: function(sm,i,r)	{
				oseMscAddon.paymentAdvParams.gridSelectedItem = r.data;
			}
		}
	});

  	oseMscAddon.paymentAdvParams.gridStore = new Ext.data.Store({
	    proxy: new Ext.data.HttpProxy({
            url: 'index.php?option=com_osemsc&controller=membership',
            method: 'POST'
      	})
	  	,baseParams:{task: "action",action: 'panel.paymentAdv.getOptions',msc_id: oseMscs.msc_id}
	  	,reader: new Ext.data.JsonReader({
		    root: 'results',
		    totalProperty: 'total'
	  	},[
		    {name: 'id', type: 'string', mapping: 'id'}
		    ,{name: 'idurl', type: 'string', mapping: 'idurl'}
		    ,{name: 'optionname', type: 'string', mapping: 'optionname'}
			,{name: 'paymentAdv_renew_discount', type: 'string', mapping: 'renew_discount'}
			,{name: 'paymentAdv_renew_discount_type', type: 'string', mapping: 'renew_discount_type'}
			,{name: 'paymentAdv_payment_mode', type: 'string', mapping: 'payment_mode'}
			,{name: 'paymentAdv_twoco_productid', type: 'string', mapping: 'twoco_productid'}
			,{name: 'paymentAdv_clickbank_productid', type: 'string', mapping: 'clickbank_productid'}
			,{name: 'paymentAdv_option_visibility', type: 'string', mapping: 'option_visibility'}
			,{name: 'paymentAdv_nosamemembership', type: 'string', mapping: 'nosamemembership'}
	  	])
	  	,sort: 'ordering'
	  	,autoLoad:{}
	})

  	oseMscAddon.paymentAdv = new Ext.grid.GridPanel({
  		store: oseMscAddon.paymentAdvParams.gridStore
  		,cm: new Ext.grid.ColumnModel({
	        defaults: {
	            sortable: false
	        },
	        columns: [
	        	new Ext.grid.RowNumberer({header:'#'})
	            ,{id: 'idurl', header: Joomla.JText._('ID'),  hidden:false, dataIndex: 'idurl', width: 100}
	            ,{
			    	id: 'option', header: Joomla.JText._('Option'), xtype: 'templatecolumn', dataIndex: 'p3,t3',
			    	tpl: new Ext.Template(
			    		'<p>{optionname}</p>'
			    	)
			    },{
	            	xtype: 'actioncolumn'
	                ,width: 150
	                ,align: 'center'
	                ,header: Joomla.JText._('Action')
	                ,items: [{
	                    getClass: function(v, meta, rec,ri,ci,s)	{
                        	return 'edit-col';
	                	}
	                    ,tooltip: Joomla.JText._('Edit')
	                    ,handler: function(grid, rowIndex, colIndex) {
	                    	
	                    	oseMscAddon.paymentAdvParams.openCWin(grid,rowIndex);
	                    }
	                }]
                }
	        ]
	    })
  		,sm: oseMscAddon.paymentAdvParams.gridSm
  		,bbar:new Ext.PagingToolbar({
    		pageSize: 20,
    		store: oseMscAddon.paymentAdvParams.gridStore,
    		displayInfo: true,
    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
			emptyMsg: Joomla.JText._("No_topics_to_display")

	    })
  		//,viewConfig: {forceFit: true}
  		,autoExpandColumn: 'option'
		,height: 500
  	})