Ext.ns('oseMscAddon');

	//
	// Addon Msc Panel
	//
	oseMscAddon.docman = new Ext.Panel({

		defaults: [{anchour:'95%'}],
		tbar: [{
			text: Joomla.JText._('save'),
			handler: function(){
				oseMscAddon.docman.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.docman.save',msc_id: oseMsc.msc_id
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
		    reader:new Ext.data.JsonReader({   
			    root: 'result',
			    totalProperty: 'total',
			    fields:[ 
				 	{name: 'docman_groups_id', type: 'string', mapping: 'groups_id'},
				 	{name: 'id', type: 'string', mapping: 'id'}
			  	]
		  	})
		}],
		
		listeners:{
			render: function(panel){
				panel.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'docman'},
					success: function(form,action)	{
						var result = action.result;
						var gids = result.data.docman_groups_id;

						var addondocmanFieldset = new Ext.form.FieldSet({
							title:Joomla.JText._('Docman_Group_Selection'),
							anchor: '95%',
							items:[{
						        	xtype: 'multiselect'
						        	,id:'docman_groups_id'
						        	,fieldLabel: Joomla.JText._('Docman_Group')
						            ,name: 'docman_groups_id'
						            ,width: 250
						            ,height: 150
						            //,allowBlank:false
						            ,store: new Ext.data.Store({
										proxy: new Ext.data.HttpProxy({
											url: 'index.php?option=com_osemsc&controller=memberships'
								            ,method: 'POST'
							      		})
							      		,baseParams:{task: "action",action:'panel.docman.getGroups'}
								  		,reader: new Ext.data.JsonReader({
									    	root: 'results'
									    	,totalProperty: 'total'
									  	},[
										    {name: 'id', type: 'string', mapping: 'id'}
										    ,{name: 'title', type: 'string', mapping: 'title'}
									  	])
								  		,autoLoad:{}
								  		,listeners:{
									  		load: function(s,r){
									  			addondocmanFieldset.findById('docman_groups_id').setValue(gids);
									  		}
									  	}
									})
						            ,valueField: 'id'
								  	,displayField: 'title'
						            ,ddReorder: true
						        }]
							
						});
						panel.form.add(addondocmanFieldset);

						panel.doLayout();
					},
        			failure:function(form,action){
        				var addondocmanFieldset = new Ext.form.FieldSet({
							title:Joomla.JText._('Docman_Group_Selection'),
							anchor: '95%',
							items:[{
						        	xtype: 'multiselect'
						        	,fieldLabel: Joomla.JText._('Docman_Group')
						            ,name: 'docman_groups_id'
						            ,width: 250
						            ,height: 150
						            //,allowBlank:false
						            ,store: new Ext.data.Store({
										proxy: new Ext.data.HttpProxy({
											url: 'index.php?option=com_osemsc&controller=memberships'
								            ,method: 'POST'
							      		})
							      		,baseParams:{task: "action",action:'panel.docman.getGroups'}
								  		,reader: new Ext.data.JsonReader({
									    	root: 'results'
									    	,totalProperty: 'total'
									  	},[
										    {name: 'id', type: 'string', mapping: 'id'}
										    ,{name: 'title', type: 'string', mapping: 'title'}
									  	])
								  		,autoLoad:{}
									})
						            ,valueField: 'id'
								  	,displayField: 'title'
						            ,ddReorder: true
						        }]
							
						});
						panel.form.add(addondocmanFieldset);

						panel.doLayout();
        			}
				});
			}
		}
	});