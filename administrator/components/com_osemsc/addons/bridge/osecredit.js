Ext.ns('oseMscAddon');

	var addonosecreditStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.osecredit.getPlans'}, 
		  reader: new Ext.data.JsonReader({   
		    root: 'results',
		    totalProperty: 'total'
		  },[ 
		    {name: 'osecredit_id', type: 'int', mapping: 'id'},
		    {name: 'name', type: 'string', mapping: 'title'}
		  ]),
		  autoLoad:{}
	});
	
	

	var addonosecreditFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('OSE_Credit_Plan_Selection'),
		anchor: '95%',
		items:[{
				fieldLabel: Joomla.JText._('Enable')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'osecredit_enable'
				,defaults: {xtype: 'radio', name:'osecredit_enable'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0, checked: true}
				]
		},{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('Plan')
	            ,hiddenName: 'osecredit.osecredit_id'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonosecreditStore
			    ,valueField: 'osecredit_id'
			    ,displayField: 'name'
	    }]
		
	});

	//
	// Addon Msc Panel
	//
	oseMscAddon.osecredit = new Ext.Panel({

		defaults: [{anchour:'95%'}],
		tbar: [{
			text: Joomla.JText._('save'),
			handler: function(){
				oseMscAddon.osecredit.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.osecredit.save',msc_id: oseMsc.msc_id
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
		           addonosecreditFieldset
		    ],
		    reader:new Ext.data.JsonReader({   
			    root: 'result',
			    totalProperty: 'total',
			    fields:[ 
				 	{name: 'osecredit.osecredit_id', type: 'int', mapping: 'osecredit_id'}
				 	,{name: 'osecredit_enable', type: 'int', mapping: 'enable'}
			  	]
		  	})
		}],
		
		listeners:{
			render: function(panel){
				panel.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'osecredit'}
				});
			}
		}
	});