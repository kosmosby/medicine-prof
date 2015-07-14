Ext.ns('oseMscAddon');

	var addonhidepaymentStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.hidepayment.getMethods'}, 
		  reader: new Ext.data.JsonReader({   
		    root: 'results',
		    totalProperty: 'total'
		  },[ 
		    {name: 'value', type: 'string', mapping: 'value'},
		    {name: 'text', type: 'string', mapping: 'text'}
		  ]),
		  autoLoad:{}
	});

	var addonhidepaymentFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('JomSocial_Profile_Type_Selection'),
		anchor: '95%',
		items:[{
				fieldLabel: Joomla.JText._('Enable')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'hidepayment_enable'
				,defaults: {xtype: 'radio', name:'hidepayment_enable'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0, checked: true}
				]
		},{
		    	xtype:'combo'
	            ,fieldLabel: 'Profile Type '
	            ,hiddenName: 'hidepayment.value'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonhidepaymentStore
			    ,valueField: 'value'
			    ,displayField: 'text'
	    }]
		
	});

	//
	// Addon Msc Panel
	//
	oseMscAddon.hidepayment = new Ext.Panel({

		defaults: [{anchour:'95%'}],
		tbar: [{
			text: Joomla.JText._('save'),
			handler: function(){
				oseMscAddon.hidepayment.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.hidepayment.save',msc_id: oseMsc.msc_id
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
			labelWidth: 200,
		    bodyStyle:'padding:5px',
			autoScroll: true,
			autoWidth: true,
		    border: false,
		    defaults: [{anchour:'90%'}],
		    
		    items:[{
					fieldLabel: Joomla.JText._('Enable')
					,xtype: 'radiogroup'
					,autoWidth: true	
					,name:'hidepayment_enable'
					,defaults: {xtype: 'radio', name:'hidepayment_enable'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1}
						,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0, checked: true}
					]
		    	},{
	        		xtype: 'multiselect'
		        	,fieldLabel: Joomla.JText._('payment_methods_hidden_for_this_membership')
		            ,name: 'hidepayment.value'
		            ,width: 500
		            ,height: 150
		            //,allowBlank:false
		            ,store: addonhidepaymentStore
		            ,valueField: 'value'
				  	,displayField: 'text'
		            ,ddReorder: true
		        }
		    ],
		    reader:new Ext.data.JsonReader({   
			    root: 'result',
			    totalProperty: 'total',
			    fields:[ 
				 	{name: 'hidepayment.value', type: 'string', mapping: 'value'}
				 	,{name: 'hidepayment_enable', type: 'int', mapping: 'enable'}
			  	]
		  	})
		}],
		
		listeners:{
			render: function(panel){
				panel.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'hidepayment'}
				});
			}
		}
	});