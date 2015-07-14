Ext.ns('oseMscAddon');

	//
	// Addon Msc Panel
	//
	oseMscAddon.jgroup = new Ext.Panel({

		defaults: [{anchour:'95%'}],
		tbar: [{
			text: Joomla.JText._('save'),
			handler: function(){
				oseMscAddon.jgroup.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.jgroup.save',msc_id: oseMsc.msc_id
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
		    
		    items:[{
					xtype:'fieldset',
					title:Joomla.JText._('Joomla_User_Group_Selection'),
					items:[{
						border:false,
						autoLoad:{
							method:'POST',
							url:'index.php?option=com_osemsc&controller=membership',
							params:{task:'action',action:'panel.jgroup.getGroups',msc_id:oseMscs.msc_id}
								
						}
					 }]
				}
		    ],
		    reader:new Ext.data.JsonReader({   
			    root: 'result',
			    totalProperty: 'total',
			    fields:[ 
				 	{name: 'jgroup.jgroup_id', type: 'int', mapping: 'jgroup_id'}
	
			  	]
		  	})
		}],
		
		listeners:{
			
		}
	});