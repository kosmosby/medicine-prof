Ext.ns('oseMscAddon');

	var addonLicSeatFieldset = new Ext.form.FieldSet({
		title:'License Seat Setting'
		,labelWidth: 200
		,items: [{
			xtype: 'checkbox'
			,fieldLabel: 'Enabled'
			,name: 'licseat_enabled'
			,inputValue: 1
		},{
			xtype: 'numberfield'
			,name: 'licseat_seat_number'
			,maxlength: 10
			,maxValue: 9999999999
			,fieldLabel: 'Maximum number of concurrent logins'
		},{
			xtype: 'checkbox'
			,fieldLabel: 'Sent to Licenser Contact'
			,name: 'licseat_contact_send'
			,inputValue: 1
		},{
			xtype: 'checkbox'
			,fieldLabel: 'Sent to Internal Contact'
			,name: 'licseat_internal_contact_send'
			,inputValue: 1
		}]
	});



	//
	// Addon Msc Panel
	//
	oseMscAddon.licseat = new Ext.FormPanel({
		//title: 'Membership Parameters',
		bodyStyle: 'padding: 10px'
		,labelWidth: 200
		,items: addonLicSeatFieldset
		,reader:new Ext.data.JsonReader({
		    root: 'result',
		    totalProperty: 'total',
		    fields:[
			    {name: 'licseat_seat_number', type: 'int', mapping: 'seat_number'}
			    ,{name: 'licseat_enabled', type: 'int', mapping: 'enabled'}
			    //,{name: 'licseat_contact', type: 'string', mapping: 'contact'}
			    //,{name: 'licseat_internal_contact', type: 'string', mapping: 'internal_contact'}
		  		,{name: 'licseat_contact_send', type: 'string', mapping: 'contact_send'}
			    ,{name: 'licseat_internal_contact_send', type: 'string', mapping: 'internal_contact_send'}
		  	]
	  	})
	  	,buttons: [{
	  		text: 'Save'
	  		,handler: function()	{
	  			oseMscAddon.licseat.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'bridge.licseat.save',msc_id: oseMsc.msc_id
				    },
				    waitMsg : 'Loading...',
				    success: oseMsc.formSuccess
				    ,failure: oseMsc.formFailureMB
    			})
	  		}
	  	}]
		,listeners:{
			render: function(panel){
				panel.getForm().load({
					waitMsg : 'Loading...',
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'licseat'}
				});
			}
		}
	});