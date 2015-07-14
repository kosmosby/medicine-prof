Ext.ns('oseMscAddon');

	var addonJshoppingProductStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.jshopping.getProduct'},
		  reader: new Ext.data.JsonReader({
		    root: 'results',
		    totalProperty: 'total'
		  },[
		    {name: 'id', type: 'int', mapping: 'product_id'},
		    {name: 'product_name', type: 'string', mapping: 'product_name'}
		  ]),
		  autoLoad:{}
	});

	var addonJshoppingUgStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.jshopping.getUg'},
		  reader: new Ext.data.JsonReader({
		    root: 'results',
		    totalProperty: 'total'
		  },[
		    {name: 'id', type: 'int', mapping: 'usergroup_id'},
		    {name: 'name', type: 'string', mapping: 'usergroup_name'}
		  ]),
		  autoLoad:{}
	});
	
	var addonJshoppingCatStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.jshopping.getCat'},
		  reader: new Ext.data.JsonReader({
		    root: 'results',
		    totalProperty: 'total'
		  },[
		    {name: 'id', type: 'int', mapping: 'category_id'},
		    {name: 'name', type: 'string', mapping: 'category_name'}
		  ]),
		  autoLoad:{}
	});


	var addonJshoppingBasicFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('User_Group_Bridging_Setting'),
		anchor: '95%',
		items:[{
	    	xtype:'button',
	    	fieldLabel: Joomla.JText._('Create_The_User_Group_With_The_Same_Name'),
	    	text: 'Create',
	    	handler: function(){
			oseMscAddon.jshopping.form.getForm().submit({
			    clientValidation: true,
			    url: 'index.php?option=com_osemsc&controller=membership',
			    params: {
			        task: 'action', action : 'panel.jshopping.create',msc_id: oseMsc.msc_id
			    },
			    success: function(form, action) {
			    	var msg = action.result;
			    	addonJshoppingUgStore.reload();
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
            fieldLabel: Joomla.JText._('Please_select_the_user_group'),
            hiddenName: 'jshopping.ug_id',
            anchor:'95%',
		    typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:false,
		    mode: 'remote',
		    store: addonJshoppingUgStore,
		    valueField: 'id',
		    displayField: 'name'

	    }]
	});

	var addonJshoppingAdvFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('Shopping_cart_bridging_setting'),
		anchor: '95%',
		items:[
		{
			xtype: 'displayfield',
			html:Joomla.JText._('Please_choose_either_the_bridging_product_or_bridging_category_in_JoomShopping_for_this_membership_plan_Note_selecting_category_options_will_override_your_setting_for_the_product_where_your_users_will_be_added_to_the_membership_if_he_she_buys_one_of_the_products_in_the_selected_category_Please_leave_it_blank_delete_the_value_if_you_do_not_need_this_function')
	    },
		{
        	xtype:'combo',
            fieldLabel: Joomla.JText._('JoomShopping_Product'),
            hiddenName: 'jshopping.product_id',
            anchor:'95%',
		    typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:false,
		    mode: 'remote',
		    store: addonJshoppingProductStore,
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
            fieldLabel: Joomla.JText._('JoomShopping_Product_Category'),
            hiddenName: 'jshopping.category_id',
            anchor:'95%',
		    typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:false,
		    mode: 'remote',
		    store: addonJshoppingCatStore,
		    valueField: 'id',
		    displayField: 'name'
	    }]

	});

	//
	// Addon Msc Panel
	//
	oseMscAddon.jshopping = new Ext.Panel({
		//title: 'Virturemart Bridge',
		defaults: [{anchour:'95%'}],
		tbar: [{
			text: Joomla.JText._('save'),
			handler: function(){
				oseMscAddon.jshopping.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.jshopping.save',msc_id: oseMsc.msc_id
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
		           addonJshoppingBasicFieldset,
			       addonJshoppingAdvFieldset
		    ],
		    reader:new Ext.data.JsonReader({
			    root: 'result',
			    totalProperty: 'total',
			    fields:[
			        {name: 'jshopping.ug_id', type: 'int', mapping: 'ug_id'},
				    {name: 'jshopping.product_id', type: 'string', mapping: 'product_id'},
					{name: 'jshopping.category_id', type: 'int', mapping: 'category_id'}
			  	]
		  	})
		}],

		listeners:{
			render: function(panel){
				panel.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'jshopping'}
				});
			}
		}
	});