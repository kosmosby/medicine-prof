Ext.ns('oseMscAddon');

	var addonPHPBBStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.phpbb.get_phpbb_group'}, 
		  reader: new Ext.data.JsonReader({   
		    root: 'results',
		    totalProperty: 'total'
		  },[ 
		    {name: 'group_id', type: 'int', mapping: 'group_id'},
		    {name: 'group_name', type: 'string', mapping: 'group_name'}
		  ]),
		  autoLoad:{}
	});
	
	

	var addonPHPBBFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('Basic_Setting'),
		anchor: '95%',
		items:[{
		    	xtype:'button',
		    	fieldLabel: Joomla.JText._('Create_The_Member_Group_in_PHPBB'),
		    	text: Joomla.JText._('Create'),
		    	handler: function(){
				oseMscAddon.phpbb.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.phpbb.create',msc_id: oseMsc.msc_id
				    },
				    success: function(form, action) {
				    	var msg = action.result;
				    	addonPHPBBStore.reload();
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
	            fieldLabel: Joomla.JText._('PHPBB_Member_Group'),
	            hiddenName: 'phpbb.group_id',
	            anchor:'95%',
			    typeAhead: true,
			    triggerAction: 'all',
			    lazyRender:false,
			    mode: 'remote',
			    store: addonPHPBBStore,
			    valueField: 'group_id',
			    displayField: 'group_name'
		    }]
		
	});

	//
	// Addon Msc Panel
	//
	oseMscAddon.phpbb = new Ext.Panel({

		defaults: [{anchour:'95%'}],
		tbar: [{
			text: Joomla.JText._('save'),
			handler: function(){
				oseMscAddon.phpbb.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.phpbb.save',msc_id: oseMsc.msc_id
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
		           addonPHPBBFieldset
		    ],
		    reader:new Ext.data.JsonReader({   
			    root: 'result',
			    totalProperty: 'total',
			    fields:[ 
				 	{name: 'phpbb.group_id', type: 'int', mapping: 'group_id'}
			  	]
		  	})
		}],
		
		listeners:{
			render: function(panel){
				panel.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'phpbb'}
				});
			}
		}
	});