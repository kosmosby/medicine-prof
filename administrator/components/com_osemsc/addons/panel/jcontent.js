Ext.ns('oseMscAddon');

	var addonjcontentFieldset = new Ext.form.FieldSet({
		title:'Joomla Content Access Control',
		anchor: '95%',
		labelWidth:250,
		items:[{
	    		id:'view'	
		    	,xtype:'checkbox'
		        ,fieldLabel: 'Allow user to view content items'
		        ,name: 'jcontent_view_control'
		        ,inputValue: 1
		    },{
	    		id:'create'	
			   	,xtype:'checkbox'
			    ,fieldLabel: 'Allow user to create new content items'
			    ,name: 'jcontent_create_control'
			    ,inputValue: 1
			},{
	    		id:'edit'	
				,xtype:'checkbox'
				,fieldLabel: 'Allow user to update content items'
				,name: 'jcontent_edit_control'
				,inputValue: 1
			}]
		
	});

	//
	// Addon Msc Panel
	//
	oseMscAddon.jcontent = new Ext.Panel({

		defaults: [{anchour:'95%'}],
		tbar: [{
			text: 'save',
			handler: function(){
				var view = addonjcontentFieldset.findById('view').getValue();
				var create = addonjcontentFieldset.findById('create').getValue();
				var edit = addonjcontentFieldset.findById('edit').getValue();
				if(!view && !create && !edit)
				{
					 Ext.Msg.alert('Error', 'You Must Select The Access Level.');
				}else{
					oseMscAddon.jcontent.form.getForm().submit({
					    clientValidation: true,
					    url: 'index.php?option=com_osemsc&controller=membership',
					    params: {
					        task: 'action', action : 'panel.jcontent.save',msc_id: oseMsc.msc_id
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
		           addonjcontentFieldset
		    ],
		    reader:new Ext.data.JsonReader({   
			    root: 'result',
			    totalProperty: 'total',
			    fields:[ 
				 	{name: 'jcontent_view_control', type: 'int', mapping: 'view_control'}
				 	,{name: 'jcontent_edit_control', type: 'int', mapping: 'edit_control'}
				 	,{name: 'jcontent_create_control', type: 'int', mapping: 'create_control'}
			  	]
		  	})
		}],
		
		listeners:{
			render: function(panel){
				panel.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'jcontent'}
				});
			}
		}
	});