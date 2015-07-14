Ext.ns('oseMscAddon');

	var addonMscEmailStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=emails',
	            method: 'POST'
	      }),
		  baseParams:{task: "getEmails"},
		  reader: new Ext.data.JsonReader({
		    root: 'results',
		    totalProperty: 'total'
		  },[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'Subject', type: 'string', mapping: 'subject'}
		  ])
	});


	var addonMscEmailFieldset = new Ext.form.FieldSet({
		title:'Email Setting',
		labelWidth: 200,
		items:[
	    {
        	xtype:'combo',
            fieldLabel: 'Membership Welcome Email ',
            hiddenName: 'msc.wel_email',
		    typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:false,
		    mode: 'remote',
		    store: addonMscEmailStore,
		    valueField: 'id',
		    displayField: 'Subject'
	    },{
        	xtype:'combo',
            fieldLabel: 'Membership Cancellation Email ',
            hiddenName: 'msc.cancel_email',
		    typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:false,
		    mode: 'remote',
		    store: addonMscEmailStore,
		    valueField: 'id',
		    displayField: 'Subject'

	    },{
        	xtype:'combo',
            fieldLabel: 'Membership Expiration Reminder',
            hiddenName: 'msc.notification',
		    typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:false,
		    mode: 'remote',
		    store: addonMscEmailStore,
		    valueField: 'id',
		    displayField: 'Subject'

	    },{
        	xtype:'combo',
            fieldLabel: 'Membership Expiration Email',
            hiddenName: 'msc.exp_email',
		    typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:false,
		    mode: 'remote',
		    store: addonMscEmailStore,
		    valueField: 'id',
		    displayField: 'Subject'
	    }]
	});



	//
	// Addon Msc Panel
	//
	oseMscAddon.msc = new Ext.Panel({
		//title: 'Membership Parameters',
		bodyStyle: 'padding: 10px',
		defaults: [{bodyStyle: 'padding: 10px'}],
		tbar: [{
			text: 'save',
			handler: function(){
				oseMscAddon.msc.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.msc.save',msc_id: oseMsc.msc_id
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
			labelAlign: 'left',
			labelWidth: 150,
		    //title: 'Membership Setting',

			autoScroll: true,
			autoWidth: true,
		    border: false,
		    defaults: [{bodyStyle:'padding:10px', width: '95%'}],

		    items:[{
		    	itemId:'limit-number',
	        	xtype:'numberfield',
	            fieldLabel: 'Number Limitation ',
	            name: 'msc.limit_number',
	            emptyText: '-1'
		    },{
		    	xtype:'fieldset',
		    	labelAlign: 'top',
		    	title: 'Default Messages to Non-Members',
		    	items:[{
		    		hideLabel: true,
		    		xtype:'htmleditor',
		    		name:'msc.restrict',
			        itemId:'restrict',
			        height:150
		    	}]
		    },
		    	addonMscEmailFieldset
		    ],
		    reader:new Ext.data.JsonReader({
			    root: 'result',
			    totalProperty: 'total',
			    fields:[
				    {name: 'msc.limit_number', type: 'int', mapping: 'limit_number'},
				    {name: 'msc.restrict', type: 'string', mapping: 'restrict'},
				    {name: 'msc.reg_email', type: 'int', mapping: 'reg_email'},
				    {name: 'msc.wel_email', type: 'int', mapping: 'wel_email'},
				    {name: 'msc.cancel_email', type: 'int', mapping: 'cancel_email'},
				    {name: 'msc.exp_email', type: 'int', mapping: 'exp_email'},
				    {name: 'msc.notification', type: 'int', mapping: 'notification'}
			  	]
		  	})
		}],

		listeners:{
			render: function(panel){
				addonMscEmailStore.load();
				panel.form.getForm().load({
					//waitMsg : 'Loading...',
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'msc'}
				});
			}
		}
	});

	//alert(Ext.getCmp('mscSetting-panelWin').title);