Ext.ns('oseMscAddon','oseMscAddon.oselicParams');
Ext.ns('oseMscAddon','oseMscAddon.oselic');

oseMscAddon.oselicParams.createForm = function(){
	this.mfReader = function()	{
		return new Ext.data.JsonReader({
		    root: 'result'
		    ,totalProperty: 'total'
		    ,fields:[
		    	{name: 'id', type: 'string', mapping: 'id'}
			    ,{name: 'idurl', type: 'string', mapping: 'idurl'}
			    ,{name: 'optionname', type: 'string', mapping: 'optionname'}

			    ,{name: 'oselic.license_id', type: 'string', mapping: 'license_id'}
			    ,{name: 'oselic.enable_license', type: 'string', mapping: 'enable_license'}
			]
	  	})
	}
}

oseMscAddon.oselicParams.createForm.prototype = {
	init: function(grid)	{
		var reader =  this.mfReader();

		oseMscAddon.oselicParams.License = new Ext.form.FieldSet({
			title:'License'
			,labelWidth: 150
			,items:[{
				xtype: 'checkbox'
				,inputValue: 1
				,name: 'oselic.enable_license'
				,fieldLabel: 'Enable'
			},{
	        	xtype:'combo'
	        	,ref: 'licenselist'
	            ,fieldLabel: 'License ID'
	            ,hiddenName: 'oselic.license_id'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'local'
			    ,store: new Ext.data.Store({
			  		proxy: new Ext.data.HttpProxy({
		            	url: 'index.php?option=com_osemsc&controller=memberships',
			            method: 'POST'
			      	})
				  	,baseParams:{task: "action",action:'panel.oselic.getLicLicense'}
				  	,reader: new Ext.data.JsonReader({   
					    root: 'results',
					    totalProperty: 'total'
				  	},[ 
					    {name: 'id', type: 'int', mapping: 'id'},
					    {name: 'lic_name', type: 'string', mapping: 'title'}
				  	])
				  	,autoLoad:{}
				  	,listeners: {
			    		load: function(s,r)	{
			    			addonPaymentFormPanel.getForm().setValues(addonPaymentFormPanel.formData);
			    			
			    			//oseMscAddon.oselicParams.License.licenselist.setValue(r[0].get('id'))
			    			/*addonPaymentFormPanel.getForm().load({
								waitMsg : 'Loading...',
								url: 'index.php?option=com_osemsc&controller=membership',
								params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'oselic'}
								,success: function(form,action)	{
									//oseMscAddon.oselicParams.License.licenselist.fireEvent('load');
									
									
									if(Ext.value(action.result.data['oselic.license_id'],false))	{
										oseMscAddon.oselicParams.License.licenselist.setValue(action.result.data['oselic.license_id']);
									}	
									else if(r.length > 0)	{
										oseMscAddon.oselicParams.License.licenselist.setValue(r[0].get('id'));
									} else	{
										//oseMscAddon.oselicParams.License.licenselist.setValue(r[0].get('id'));
									}
									
									//oseMscAddon.oselicParams.License.licenselist.setValue(r[0].get('id'))
								}
							});*/
			    		}
			    	}
				})
			    ,valueField: 'id'
			    ,displayField: 'lic_name'
		    },{
		    	xtype:'combo'
	            ,fieldLabel: 'Member Expiry Mode'
	        	,hiddenName: 'oselic.member_expiry_mode'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,mode: 'local'
			    ,store: new Ext.data.ArrayStore({
			    	fields: [ 
					    {name: 'id', type: 'int', mapping: 'id'}
					    ,{name: 'lic_name', type: 'string', mapping: 'title'}
				  	]
				  	,data: [
				  	    {id: 1, title: 'Expired when member is expired'}
				  	    ,{id: 2, title: 'Expired with a fixed date'}
		  	        ]
			    	,id: 0
				})
		    ,value:1
			    ,valueField: 'id'
			    ,displayField: 'lic_name'
		    },{
		    	xtype: 'datefield'
		    	,name: 'oselic.expiry_date'
		    	,format: 'Y-m-d'
		    	,fieldLabel: 'Fixed Expiry Date'
		    },{
				xtype: 'hidden'
				,name: 'id'
			}]
		});
		
		var addonPaymentFormPanel = new Ext.FormPanel({
			bodyStyle:'padding:10px'
			,autoScroll: true
			,autoWidth: true
		    ,border: false
			,height: 300
		    ,items:[
		    	oseMscAddon.oselicParams.License
		    ]
			
			,buttons: [{
				text: 'save',
				handler: function(btn){
					btn.findParentByType('form').getForm().submit({
					    clientValidation: true,
					    url: 'index.php?option=com_osemsc&controller=membership',
					    params: {
					        task: 'action', action : 'panel.oselic.save',msc_id: oseMsc.msc_id
					    },
					    success: function(form, action) {
					    	oseMsc.formSuccess(form,action)
					    },
					    failure: function(form, action) {
					        oseMsc.formFailureMB(form,action)
					    }
	    			})
				}
			}]
			,reader:new Ext.data.JsonReader({   
			    root: 'result',
			    totalProperty: 'total',
			    fields:[ 
				    {name: 'oselic.license_id', type: 'string', mapping: 'license_id'}
				    ,{name: 'oselic.enable_license', type: 'string', mapping: 'enable_license'}
				    ,{name: 'oselic.member_expiry_mode', type: 'int', mapping: 'member_expiry_mode'}
				    ,{name: 'oselic.expiry_date', type: 'date', mapping: 'expiry_date'}
				    //,{name: 'lic.license_discount', type: 'int', mapping: 'license_discount'}
				    //,{name: 'lic.license_discount_type', type: 'string', mapping: 'license_discount_type'}
			  	]
		  	})
			
		});
		return addonPaymentFormPanel;
	}
	
	,openWin: function(form,grid)	{
		this.win = new Ext.Window({
			title: 'Payment Parameter Setting'
			,items: form
			,width: 800
			,modal: true
			,listeners: {
				close: function(w)	{
					grid.getStore().reload();
				}
			}
		});

		this.win.show().alignTo(Ext.getBody(),'t-t');
	}
}

oseMscAddon.oselicParams.openCWin = function(grid,i)	{
	var addonPaymentFormCreate = new oseMscAddon.oselicParams.createForm();
	var addonPaymentForm = addonPaymentFormCreate.init(grid);
	var r = grid.getStore().getAt(i);
	//alert(r.data.toSource());
	addonPaymentForm.getForm().setValues(r.data);
	addonPaymentForm.formData = r.data;
	addonPaymentFormCreate.openWin(addonPaymentForm,grid);
}

oseMscAddon.oselicParams.gridSm = new Ext.grid.RowSelectionModel({
	singleSelect:false
	,listeners: {
		selectionchange: function(sm)	{
			oseMscAddon.oselicParams.getTopToolbar().editBtn.setDisabled(sm.getCount()<1);
		}
		,rowselect: function(sm,i,r)	{
			oseMscAddon.oselicParams.gridSelectedItem = r.data;
		}
	}
});

oseMscAddon.oselicParams.gridStore = new Ext.data.Store({
    proxy: new Ext.data.HttpProxy({
        url: 'index.php?option=com_osemsc&controller=membership',
        method: 'POST'
  	})
  	,baseParams:{task: "action",action: 'panel.oselic.getOptions',msc_id: oseMscs.msc_id}
  	,reader: new Ext.data.JsonReader({
	    root: 'results',
	    totalProperty: 'total'
  	},[
	    {name: 'id', type: 'string', mapping: 'id'}
	    ,{name: 'idurl', type: 'string', mapping: 'idurl'}
	    ,{name: 'optionname', type: 'string', mapping: 'optionname'}
	    ,{name: 'oselic.member_expiry_mode', type: 'int', mapping: 'member_expiry_mode'}
	    ,{name: 'oselic.expiry_date', type: 'date', mapping: 'expiry_date'}
	    ,{name: 'oselic.license_id', type: 'string', mapping: 'license_id'}
	    ,{name: 'oselic.enable_license', type: 'string', mapping: 'enable_license'}
  	])
  	,sort: 'ordering'
  	,autoLoad:{}
})

	oseMscAddon.oselic = new Ext.grid.GridPanel({
		store: oseMscAddon.oselicParams.gridStore
		,cm: new Ext.grid.ColumnModel({
        defaults: {
            sortable: false
        },
        columns: [
        	new Ext.grid.RowNumberer({header:'#'})
            ,{id: 'idurl', header: 'ID',  hidden:false, dataIndex: 'idurl', width: 100}
            ,{
		    	id: 'option', header: 'Option', xtype: 'templatecolumn', dataIndex: 'p3,t3',
		    	tpl: new Ext.Template(
		    		'<p>{optionname}</p>'
		    	)
		    },{
            	xtype: 'actioncolumn'
                ,width: 150
                ,align: 'center'
                ,header: 'Action'
                ,items: [{
                    getClass: function(v, meta, rec,ri,ci,s)	{
                    	return 'edit-col';
                	}
                    ,tooltip: 'Edit'
                    ,handler: function(grid, rowIndex, colIndex) {
                    	
                    	oseMscAddon.oselicParams.openCWin(grid,rowIndex);
                    }
                }]
            }
        ]
    })
		,sm: oseMscAddon.oselicParams.gridSm
		,bbar:new Ext.PagingToolbar({
		pageSize: 20,
		store: oseMscAddon.oselicParams.gridStore,
		displayInfo: true,
	    displayMsg: 'Displaying topics {0} - {1} of {2}',
	    emptyMsg: "No topics to display"

    })
		//,viewConfig: {forceFit: true}
		,autoExpandColumn: 'option'
	,height: 500
	});
	
	//////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////
/*
	oseMscAddon.oselicParams.License = new Ext.form.FieldSet({
		title:'License'
		,labelWidth: 150
		,items:[{
			xtype: 'checkbox'
			,inputValue: 1
			,name: 'oselic.enable_license'
			,fieldLabel: 'Enable'
		},{
        	xtype:'combo'
        	,ref: 'licenselist'
            ,fieldLabel: 'License ID'
            ,hiddenName: 'oselic.license_id'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    ,lazyRender:false
		    ,mode: 'local'
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
	            	url: 'index.php?option=com_osemsc&controller=memberships',
		            method: 'POST'
		      	})
			  	,baseParams:{task: "action",action:'panel.oselic.getLicLicense'}
			  	,reader: new Ext.data.JsonReader({   
				    root: 'results',
				    totalProperty: 'total'
			  	},[ 
				    {name: 'id', type: 'int', mapping: 'id'},
				    {name: 'lic_name', type: 'string', mapping: 'title'}
			  	])
			  	,autoLoad:{}
			  	,listeners: {
		    		load: function(s,r)	{
		    			//oseMscAddon.oselicParams.License.licenselist.setValue(r[0].get('id'))
		    			oseMscAddon.oselic.getForm().load({
							waitMsg : 'Loading...',
							url: 'index.php?option=com_osemsc&controller=membership',
							params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'oselic'}
							,success: function(form,action)	{
								//oseMscAddon.oselicParams.License.licenselist.fireEvent('load');
								
								
								if(Ext.value(action.result.data['oselic.license_id'],false))	{
									oseMscAddon.oselicParams.License.licenselist.setValue(action.result.data['oselic.license_id']);
								}	else	{
									oseMscAddon.oselicParams.License.licenselist.setValue(r[0].get('id'))
								}
								
								//oseMscAddon.oselicParams.License.licenselist.setValue(r[0].get('id'))
							}
						});
		    		}
		    	}
			})
		    ,valueField: 'id'
		    ,displayField: 'lic_name'
	    },{
	    	xtype:'combo'
            ,fieldLabel: 'Member Expiry Mode'
        	,hiddenName: 'oselic.member_expiry_mode'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    ,mode: 'local'
		    ,store: new Ext.data.ArrayStore({
		    	fields: [ 
				    {name: 'id', type: 'int', mapping: 'id'}
				    ,{name: 'lic_name', type: 'string', mapping: 'title'}
			  	]
			  	,data: [
			  	    {id: 0, title: 'Default'}
			  	    ,{id: 1, title: 'Expired when member is expired'}
			  	    ,{id: 2, title: 'Expired with a fixed date'}
	  	        ]
		    	,id: 0
			})
		    ,valueField: 'id'
		    ,displayField: 'lic_name'
	    },{
	    	xtype: 'datefield'
	    	,name: 'expiry_date'
	    	,format: 'Y-m-d'
	    	,fieldLabel: 'Fixed Expiry Date'
	    }]
	});

	//
	// Addon Msc Panel
	//
	oseMscAddon.oselicForm = new Ext.FormPanel({
		bodyStyle:'padding:10px'
		,autoScroll: true
		,autoWidth: true
	    ,border: false
		,height: 300
	    ,items:[
	    	oseMscAddon.oselicParams.License
	    ]
		  
		,buttons: [{
			text: 'save',
			handler: function(){
				oseMscAddon.oselic.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.oselic.save',msc_id: oseMsc.msc_id
				    },
				    success: function(form, action) {
				    	oseMsc.formSuccess(form,action)
				    },
				    failure: function(form, action) {
				        oseMsc.formFailureMB(form,action)
				    }
    			})
			}
		}]
		,reader:new Ext.data.JsonReader({   
		    root: 'result',
		    totalProperty: 'total',
		    fields:[ 
			    {name: 'oselic.lic_id', type: 'string', mapping: 'lic_id'}
			    ,{name: 'oselic.enable_cs', type: 'string', mapping: 'enable_cs'}
			    ,{name: 'oselic.license_id', type: 'string', mapping: 'license_id'}
			    ,{name: 'oselic.enable_license', type: 'string', mapping: 'enable_license'}
			    //,{name: 'lic.license_discount', type: 'int', mapping: 'license_discount'}
			    //,{name: 'lic.license_discount_type', type: 'string', mapping: 'license_discount_type'}
		  	]
	  	})
	  	
		,listeners:{
			render: function(panel){
			
			}
		}
		
	});
*/