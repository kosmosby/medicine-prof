Ext.ns('oseMscAddon');

	var addonAcyMailingListStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.acymailing.getMailingList'},
		  reader: new Ext.data.JsonReader({
		    root: 'results',
		    totalProperty: 'total'
		  },[
		    {name: 'listid', type: 'int', mapping: 'listid'},
		    {name: 'name', type: 'string', mapping: 'name'}
		  ]),
		  autoLoad:{}
	});



	var addonAcyMailingListFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('AcyMailing_List_Setting'),
		anchor: '95%',
		items:[{
	    	xtype:'combo',
            fieldLabel: Joomla.JText._('Mailing_List'),
            hiddenName: 'acymailing.listid',
            anchor:'95%',
		    typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:false,
		    mode: 'remote',
		    store: addonAcyMailingListStore,
		    valueField: 'listid',
		    displayField: 'name'
	    },{
	    	xtype:'button',
	    	fieldLabel: Joomla.JText._('Synchronize'),
	    	text: Joomla.JText._('Synchronize'),
	    	handler: function(){
			oseMscAddon.acymailing.form.getForm().submit({
			    clientValidation: true,
			    url: 'index.php?option=com_osemsc&controller=membership',
			    params: {
			        task: 'action', action : 'panel.acymailing.synchronize',msc_id: oseMsc.msc_id
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
	    },{
			xtype: 'displayfield',
			html:Joomla.JText._('Warning_Clicking_this_button_adds_all_members_of_the_selected_ACYmailing_list_to_the_current_OSE_membership_and_adds_any_members_of_the_OSE_membership_who_are_not_members_of_the_ACYmailing_list_to_that_mailing_list_Where_a_user_is_already_a_member_of_both_the_ACYmailing_list_and_the_OSE_membership_no_action_is_taken')
	    }]

	});

	//
	// Addon Msc Panel
	//
	oseMscAddon.acymailing = new Ext.Panel({

		defaults: [{anchour:'95%'}],
		tbar: [{
			text: 'save',
			handler: function(){
				oseMscAddon.acymailing.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.acymailing.save',msc_id: oseMsc.msc_id
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
		    bodyStyle:'padding:5px',
			autoScroll: true,
			autoWidth: true,
		    border: false,
		    defaults: [{anchour:'90%'}],

		    items:[
		           addonAcyMailingListFieldset
		    ],
		    reader:new Ext.data.JsonReader({
			    root: 'result',
			    totalProperty: 'total',
			    fields:[
				 	{name: 'acymailing.listid', type: 'int', mapping: 'listid'}
			  	]
		  	})
		}],

		listeners:{
			render: function(panel){
				panel.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'acymailing'}
				});
			}
		}
	});