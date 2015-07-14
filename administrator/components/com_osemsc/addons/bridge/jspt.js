Ext.ns('oseMscAddon');

	var addonjsptStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.jspt.getJSPT'}, 
		  reader: new Ext.data.JsonReader({   
		    root: 'results',
		    totalProperty: 'total'
		  },[ 
		    {name: 'jspt_id', type: 'int', mapping: 'id'},
		    {name: 'name', type: 'string', mapping: 'name'}
		  ]),
		  autoLoad:{}
	});
	
	var addonfieldsStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'panel.jspt.getFields'}, 
		  reader: new Ext.data.JsonReader({   
		    root: 'results',
		    totalProperty: 'total'
		  },[ 
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'name', type: 'string', mapping: 'name'}
		  ]),
		  autoLoad:{}
	});

	var addonjsptFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('JomSocial_Profile_Type_Selection'),
		anchor: '95%',
		items:[{
				fieldLabel: Joomla.JText._('Enable')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'jspt_enable'
				,defaults: {xtype: 'radio', name:'jspt_enable'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0, checked: true}
				]
		},{
		    	xtype:'combo'
	            ,fieldLabel: 'Profile Type '
	            ,hiddenName: 'jspt.jspt_id'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonjsptStore
			    ,valueField: 'jspt_id'
			    ,displayField: 'name'
	    }]
		
	});

	var addonjomsocialFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('JomSocial_Setting'),
		anchor: '95%',
		items:[{
				fieldLabel: Joomla.JText._('Jomsocial_Sync')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'jspt_jomsync'
				,defaults: {xtype: 'radio', name:'jspt_jomsync'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0, checked: true}
				]
		},{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('First_Name')
	            ,hiddenName: 'jspt_firstname'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonfieldsStore
			    ,valueField: 'id'
			    ,displayField: 'name'
	    },{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('Last_Name')
	            ,hiddenName: 'jspt_lastname'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonfieldsStore
			    ,valueField: 'id'
			    ,displayField: 'name'
	    },{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('Company')
	            ,hiddenName: 'jspt_company'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonfieldsStore
			    ,valueField: 'id'
			    ,displayField: 'name'
	    },{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('Address_1')
	            ,hiddenName: 'jspt_addr1'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonfieldsStore
			    ,valueField: 'id'
			    ,displayField: 'name'
	    },{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('Address_2')
	            ,hiddenName: 'jspt_addr2'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonfieldsStore
			    ,valueField: 'id'
			    ,displayField: 'name'
	    },{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('City')
	            ,hiddenName: 'jspt_city'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonfieldsStore
			    ,valueField: 'id'
			    ,displayField: 'name'
	    },{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('State')
	            ,hiddenName: 'jspt_state'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonfieldsStore
			    ,valueField: 'id'
			    ,displayField: 'name'
	    },{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('Country')
	            ,hiddenName: 'jspt_country'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonfieldsStore
			    ,valueField: 'id'
			    ,displayField: 'name'
	    },{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('ZIP_POSTAL_CODE')
	            ,hiddenName: 'jspt_postcode'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonfieldsStore
			    ,valueField: 'id'
			    ,displayField: 'name'
	    },{
		    	xtype:'combo'
	            ,fieldLabel: Joomla.JText._('Telephone')
	            ,hiddenName: 'jspt_telephone'
	            ,anchor:'95%'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: addonfieldsStore
			    ,valueField: 'id'
			    ,displayField: 'name'
	    }]
		
	});

	//
	// Addon Msc Panel
	//
	oseMscAddon.jspt = new Ext.Panel({

		defaults: [{anchour:'95%'}],
		tbar: [{
			text: Joomla.JText._('save'),
			handler: function(){
				oseMscAddon.jspt.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.jspt.save',msc_id: oseMsc.msc_id
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
		           addonjsptFieldset
		           ,addonjomsocialFieldset
		    ],
		    reader:new Ext.data.JsonReader({   
			    root: 'result',
			    totalProperty: 'total',
			    fields:[ 
				 	{name: 'jspt.jspt_id', type: 'int', mapping: 'jspt_id'}
				 	,{name: 'jspt_enable', type: 'int', mapping: 'enable'}
				 	,{name: 'jspt_jomsync', type: 'int', mapping: 'jomsync'}
				 	,{name: 'jspt_firstname', type: 'int', mapping: 'firstname'}
				 	,{name: 'jspt_lastname', type: 'int', mapping: 'lastname'}
				 	,{name: 'jspt_company', type: 'int', mapping: 'company'}
				 	,{name: 'jspt_addr1', type: 'int', mapping: 'addr1'}
				 	,{name: 'jspt_addr2', type: 'int', mapping: 'addr2'}
				 	,{name: 'jspt_city', type: 'int', mapping: 'city'}
				 	,{name: 'jspt_state', type: 'int', mapping: 'state'}
				 	,{name: 'jspt_country', type: 'int', mapping: 'country'}
				 	,{name: 'jspt_postcode', type: 'int', mapping: 'postcode'}
				 	,{name: 'jspt_telephone', type: 'int', mapping: 'telephone'}
			  	]
		  	})
		}],
		
		listeners:{
			render: function(panel){
				panel.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'jspt'}
				});
			}
		}
	});