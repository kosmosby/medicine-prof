Ext.ns('oseMscAddon');

	var addonVmProductStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.vm.getProduct'},
		  reader: new Ext.data.JsonReader({
		    root: 'results',
		    totalProperty: 'total'
		  },[
		    {name: 'id', type: 'int', mapping: 'product_id'},
		    {name: 'product_name', type: 'string', mapping: 'product_name'}
		  ]),
		  autoLoad:{}
	});

	var addonVmSgStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.vm.getSg'},
		  reader: new Ext.data.JsonReader({
		    root: 'results',
		    totalProperty: 'total'
		  },[
		    {name: 'id', type: 'int', mapping: 'shopper_group_id'},
		    {name: 'name', type: 'string', mapping: 'shopper_group_name'}
		  ]),
		  autoLoad:{}
	});

	var addonVmCatStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.vm.getCat'},
		  reader: new Ext.data.JsonReader({
		    root: 'results',
		    totalProperty: 'total'
		  },[
		    {name: 'id', type: 'int', mapping: 'category_id'},
		    {name: 'name', type: 'string', mapping: 'category_name'}
		  ]),
		  autoLoad:{}
	});


	var addonVmBasicFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('Shopper_Group_Bridging_Setting')+' -- <a href="http://wiki.opensource-excellence.com/index.php?title=Bridging_VirtueMart_Shopper_group_with_OSE_Membership" target="_blank"> '+Joomla.JText._('Instruction')+' </a>',
		anchor: '95%',
		items:[{
	    	xtype:'button',
	    	fieldLabel: Joomla.JText._('Create_The_Shopper_Group_With_The_Same_Name'),
	    	text: Joomla.JText._('Create'),
	    	handler: function(){
	    	oseMscAddon.vm.form.getForm().submit({
			    clientValidation: true,
			    url: 'index.php?option=com_osemsc&controller=membership',
			    params: {
			        task: 'action', action : 'panel.vm.create',msc_id: oseMsc.msc_id
			    },
			    success: function(form, action) {
			    	var msg = action.result;
			    	addonVmSgStore.reload();
			    	oseMsc.msg.setAlert(msg.title,msg.content);
			    	
			    },
			    failure: function(form, action) {
			        switch (action.failureType) {
			            case Ext.form.Action.CLIENT_INVALID:
			                Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
			                break;
			            case Ext.form.Action.CONNECT_FAILURE:
			                Ext.Msg.alert('Failure', 'Ajax communication failed');
			                break;
			            case Ext.form.Action.SERVER_INVALID:
			               Ext.Msg.alert('Failure', action.result.msg);
			       }
			    }
			})
		}
    },{
        	xtype:'combo',
            fieldLabel: Joomla.JText._('Please_select_the_shopper_group'),
            hiddenName: 'vm.sg_id',
            anchor:'95%',
		    typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:false,
		    mode: 'remote',
		    store: addonVmSgStore,
		    valueField: 'id',
		    displayField: 'name'

	    }]
	});

	var addonVmAdvFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('Shopping_cart_bridging_setting')+' -- '+Joomla.JText._('if_you_would_like_to_sell_manually_renewing_membership_plan_through_VirtueMart')+'  -- <a href="http://wiki.opensource-excellence.com/index.php?title=Sell_Manually_Renewing_Membership_Plans_through_VirtueMart" target="_blank"> '+Joomla.JText._('Instruction')+' </a>',
		anchor: '95%',
		items:[
		{
			xtype: 'displayfield',
			html:Joomla.JText._('Please_choose_either_the_bridging_product_or_bridging_category_in_VirtueMart_for_this_membership_plan_Note_selecting_VM_category_options_will_override_your_setting_for_the_VM_product_where_your_users_will_be_added_to_the_membership_if_he_she_buys_one_of_the_products_in_the_selected_category_Please_leave_it_blank_delete_the_value_if_you_do_not_need_this_function')
	    },
		{
        	xtype:'combo',
            fieldLabel: Joomla.JText._('Virtuemart_Product'),
            hiddenName: 'vm.product_id',
            anchor:'95%',
		    typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:false,
		    mode: 'remote',
		    store: addonVmProductStore,
		    valueField: 'id',
		    displayField: 'product_name',
		    listeners: {
		        // delete the previous query in the beforequery event or set
		        // combo.lastQuery = null (this will reload the store the next time it expands)
		        beforequery: function(qe){
		            delete qe.combo.lastQuery;
		        }
		    }

	    },
		{
	    	xtype:'combo',
            fieldLabel: Joomla.JText._('Virtuemart_Product_Category'),
            hiddenName: 'vm.category_id',
            anchor:'95%',
		    typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:false,
		    mode: 'remote',
		    store: addonVmCatStore,
		    valueField: 'id',
		    displayField: 'name'
	    }]

	});

	var addonVmBillFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('Billing_Info_And_Order_Info_Bridging_Setting'),
		items:[{
        	xtype:'radiogroup',
            fieldLabel: Joomla.JText._('Update_MSC_Billing_Info_to_VM'),
            hiddenName: 'vm.update_billing',
            defaults:{xtype:'radio', name:'vm.update_billing'},
            items:[
            	{boxLabel:Joomla.JText._('ose_Yes'), inputValue:1,checked:true},
            	{boxLabel:Joomla.JText._('ose_No'), inputValue:0}
            ]

	    },{
        	xtype:'radiogroup',
            fieldLabel: Joomla.JText._('Update_MSC_Order_Info_to_VM'),
            hiddenName: 'vm.update_order',
            defaults:{xtype:'radio', name:'vm.update_order'},
            items:[
            	{boxLabel:Joomla.JText._('ose_Yes'), inputValue:1,checked:true},
            	{boxLabel:Joomla.JText._('ose_No'), inputValue:0}
            ]

	    }]
	});
	//
	// Addon Msc Panel
	//
	oseMscAddon.vm = new Ext.Panel({
		//title: 'Virturemart Bridge',
		defaults: [{anchour:'95%'}],
		tbar: [{
			text: Joomla.JText._('save'),
			handler: function(){
				oseMscAddon.vm.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.vm.save',msc_id: oseMsc.msc_id
				    },
				    success: function(form, action) {
				    	var msg = action.result;
				    	oseMsc.msg.setAlert(msg.title,msg.content);

				    },
				    failure: function(form, action) {
				        switch (action.failureType) {
				            case Ext.form.Action.CLIENT_INVALID:
				                Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
				                break;
				            case Ext.form.Action.CONNECT_FAILURE:
				                Ext.Msg.alert('Failure', 'Ajax communication failed');
				                break;
				            case Ext.form.Action.SERVER_INVALID:
				               Ext.Msg.alert('Failure', action.result.msg);
				       }
				    }
    			})
			}
		}],
		items:[{
			ref:'form',
			xtype:'form',
			labelAlign: 'top',
		    bodyStyle:'padding:5px',
			autoScroll: true,
			autoWidth: true,
		    border: false,
		    defaults: [{anchour:'90%'}],

		    items:[
		    	addonVmBasicFieldset,
		    	addonVmAdvFieldset,
		    	addonVmBillFieldset
		    ],
		    reader:new Ext.data.JsonReader({
			    root: 'result',
			    totalProperty: 'total',
			    fields:[
				    {name: 'vm.product_id', type: 'string', mapping: 'product_id'},
				    {name: 'vm.create_sg', type: 'int', mapping: 'create_sg'},
				 	{name: 'vm.sg_id', type: 'int', mapping: 'sg_id'},
				 	{name: 'vm.category_id', type: 'int', mapping: 'category_id'},
				 	{name: 'vm.update_billing', type: 'int', mapping: 'update_billing'},
				 	{name: 'vm.update_order', type: 'int', mapping: 'update_order'}
			  	]
		  	})
		}],

		listeners:{
			render: function(panel){
				panel.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'vm'}
				});
			}
		}
	});