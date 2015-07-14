Ext.ns('oseMscAddon');

	var addonmailchimpStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.mailchimp.getList'}, 
		  reader: new Ext.data.JsonReader({   
		    root: 'results',
		    totalProperty: 'total'
		  },[ 
		    {name: 'list_id', type: 'string', mapping: 'list_id'},
		    {name: 'name', type: 'string', mapping: 'name'}
		  ]),
		  autoLoad:{}
	});
	
	

	var addonmailchimpFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('MailChimp_List_Selection'),
		anchor: '95%',
		items:[{
				fieldLabel: Joomla.JText._('Enable')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'mailchimp_enable'
				,defaults: {xtype: 'radio', name:'mailchimp_enable'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0, checked: true}
				]
		},{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('List')
	            ,hiddenName: 'mailchimp.list_id'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonmailchimpStore
			    ,valueField: 'list_id'
			    ,displayField: 'name'
	    }]
		
	});
	
	var addonmailchimpFieldset2 = new Ext.form.FieldSet({
		title:Joomla.JText._('Membership_Expiration_MailChimp_Setting'),
		anchor: '95%',
		items:[{
				fieldLabel: Joomla.JText._('Unsubscribe_the_member_from_List')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'mailchimp_unsubscribe_enable'
				,defaults: {xtype: 'radio', name:'mailchimp_unsubscribe_enable'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0, checked: true}
				]
		},{
				fieldLabel: Joomla.JText._('Delete_the_member_from_your_list_instead_of_unsubscribing')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'mailchimp_delete'
				,defaults: {xtype: 'radio', name:'mailchimp_delete'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0, checked: true}
				]
		},{
				fieldLabel: Joomla.JText._('Send_the_goodbye_email_to_expired_member')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'mailchimp_sendGoodbye'
				,defaults: {xtype: 'radio', name:'mailchimp_sendGoodbye'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Send_unsubscribe_notification_email_to_expired_member')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'mailchimp_sendNotify'
				,defaults: {xtype: 'radio', name:'mailchimp_sendNotify'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		}]
		
	});

	//
	// Addon Msc Panel
	//
	oseMscAddon.mailchimp = new Ext.Panel({

		defaults: [{anchour:'95%'}],
		tbar: [{
			text: Joomla.JText._('save'),
			handler: function(){
				oseMscAddon.mailchimp.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.mailchimp.save',msc_id: oseMsc.msc_id
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
			labelWidth: 300,
		    bodyStyle:'padding:5px',
			autoScroll: true,
			autoWidth: true,
		    border: false,
		    defaults: [{anchour:'90%'}],
		    
		    items:[{
				xtype: 'fieldset'
				,title: Joomla.JText._('Tips')
				,anchor: '95%'	
				,border: false
				,items:[{
					html: Joomla.JText._('Please_set_the_MailChimp_API_Key_in_the_MSC_Configration_3rd_Party_you_can_get_the_API_key_From_MailChimp_admin_panel_Account_API_Keys_Authorized_Apps')
					,border:false
				}]
			}
		    ,addonmailchimpFieldset
		    ,addonmailchimpFieldset2
		    ],
		    reader:new Ext.data.JsonReader({   
			    root: 'result',
			    totalProperty: 'total',
			    fields:[ 
				 	{name: 'mailchimp.list_id', type: 'string', mapping: 'list_id'}
				 	,{name: 'mailchimp_enable', type: 'int', mapping: 'enable'}
				 	,{name: 'mailchimp_unsubscribe_enable', type: 'int', mapping: 'unsubscribe_enable'}
				 	,{name: 'mailchimp_sendGoodbye', type: 'int', mapping: 'sendGoodbye'}
				 	,{name: 'mailchimp_sendNotify', type: 'int', mapping: 'sendNotify'}
				 	,{name: 'mailchimp_delete', type: 'int', mapping: 'delete'}
			  	]
		  	})
		}],
		
		listeners:{
			render: function(panel){
				panel.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'mailchimp'}
				});
			}
		}
	});