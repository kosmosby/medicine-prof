Ext.ns('oseMscAddon');

	var addonk2groupStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.k2group.getGroups'}, 
		  reader: new Ext.data.JsonReader({   
		    root: 'results',
		    totalProperty: 'total'
		  },[ 
		    {name: 'group_id', type: 'int', mapping: 'id'},
		    {name: 'name', type: 'string', mapping: 'name'}
		  ]),
		  autoLoad:{}
	});
	
	

	var addonk2groupFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('K2_User_Group_Selection'),
		anchor: '95%',
		items:[{
				fieldLabel: Joomla.JText._('Enable')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'k2group_enable'
				,defaults: {xtype: 'radio', name:'k2group_enable'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0, checked: true}
				]
		},{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('K2_Group')
	            ,hiddenName: 'k2group.group_id'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonk2groupStore
			    ,valueField: 'group_id'
			    ,displayField: 'name'
	    },{
				fieldLabel: Joomla.JText._('Expiration_group')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'k2group_enable_exp'
				,defaults: {xtype: 'radio', name:'k2group_enable_exp'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0, checked: true}
				]
		},{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('K2_Group_expiration')
	            ,hiddenName: 'k2group.exp_group_id'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonk2groupStore
			    ,valueField: 'group_id'
			    ,displayField: 'name'
	    }]
		
	});

	//
	// Addon Msc Panel
	//
	oseMscAddon.k2group = new Ext.Panel({

		defaults: [{anchour:'95%'}],
		tbar: [{
			text: Joomla.JText._('save'),
			handler: function(){
				oseMscAddon.k2group.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.k2group.save',msc_id: oseMsc.msc_id
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
		           addonk2groupFieldset
		    ],
		    reader:new Ext.data.JsonReader({   
			    root: 'result',
			    totalProperty: 'total',
			    fields:[ 
				 	{name: 'k2group.group_id', type: 'int', mapping: 'group_id'}
				 	,{name: 'k2group_enable', type: 'int', mapping: 'enable'}
				 	,{name: 'k2group_enable_exp', type: 'int', mapping: 'enable_exp'}
				 	,{name: 'k2group.exp_group_id', type: 'int', mapping: 'exp_group_id'}
			  	]
		  	})
		}],
		
		listeners:{
			render: function(panel){
				panel.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'k2group'}
				});
			}
		}
	});