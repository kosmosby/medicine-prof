Ext.ns('oseMscAddon','oseMscAddon.acymailing2Params');
Ext.ns('oseMscAddon','oseMscAddon.acymailing2');

oseMscAddon.acymailing2Params.createForm = function(){
	this.mfReader = function()	{
		return new Ext.data.JsonReader({
		    root: 'result'
		    ,totalProperty: 'total'
		    ,fields:[
		    	{name: 'id', type: 'string', mapping: 'id'}
			    ,{name: 'idurl', type: 'string', mapping: 'idurl'}
			    ,{name: 'optionname', type: 'string', mapping: 'optionname'}
			    ,{name: 'acymailing2.listid', type: 'string', mapping: 'listid'}
			]
	  	})
	}
}

oseMscAddon.acymailing2Params.createForm.prototype = {
	init: function(grid)	{
		var reader =  this.mfReader();

		oseMscAddon.acymailing2Params.License = new Ext.form.FieldSet({
			title:'AcyMailing List'
			,labelWidth: 150
			,items:[{
	        	xtype:'combo'
	        	,ref: 'licenselist'
	            ,fieldLabel: 'AcyMailing List'
	            ,hiddenName: 'acymailing2.listid'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'local'
			    ,store: new Ext.data.Store({
			  		proxy: new Ext.data.HttpProxy({
		            	url: 'index.php?option=com_osemsc&controller=memberships',
			            method: 'POST'
			      	})
				  	,baseParams:{task: "action",action:'panel.acymailing2.getList'}
				  	,reader: new Ext.data.JsonReader({   
					    root: 'results',
					    totalProperty: 'total'
				  	},[ 
					    {name: 'id', type: 'int', mapping: 'listid'},
					    {name: 'name', type: 'string', mapping: 'name'}
				  	])
				  	,autoLoad:{}
				  	,listeners: {
			    		load: function(s,r)	{
			    			addonPaymentFormPanel.getForm().setValues(addonPaymentFormPanel.formData);

			    		}
			    	}
				})
			    ,valueField: 'id'
			    ,displayField: 'name'
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
			,height: 200
		    ,items:[
		    	oseMscAddon.acymailing2Params.License
		    ]
			
			,buttons: [{
				text: 'save',
				handler: function(btn){
					btn.findParentByType('form').getForm().submit({
					    clientValidation: true,
					    url: 'index.php?option=com_osemsc&controller=membership',
					    params: {
					        task: 'action', action : 'panel.acymailing2.save',msc_id: oseMsc.msc_id
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
				    {name: 'acymailing2.listid', type: 'string', mapping: 'listid'}
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

oseMscAddon.acymailing2Params.openCWin = function(grid,i)	{
	var addonPaymentFormCreate = new oseMscAddon.acymailing2Params.createForm();
	var addonPaymentForm = addonPaymentFormCreate.init(grid);
	var r = grid.getStore().getAt(i);
	//alert(r.data.toSource());
	addonPaymentForm.getForm().setValues(r.data);
	addonPaymentForm.formData = r.data;
	addonPaymentFormCreate.openWin(addonPaymentForm,grid);
}

oseMscAddon.acymailing2Params.gridSm = new Ext.grid.RowSelectionModel({
	singleSelect:false
	,listeners: {
		selectionchange: function(sm)	{
			oseMscAddon.acymailing2Params.getTopToolbar().editBtn.setDisabled(sm.getCount()<1);
		}
		,rowselect: function(sm,i,r)	{
			oseMscAddon.acymailing2Params.gridSelectedItem = r.data;
		}
	}
});

oseMscAddon.acymailing2Params.gridStore = new Ext.data.Store({
    proxy: new Ext.data.HttpProxy({
        url: 'index.php?option=com_osemsc&controller=membership',
        method: 'POST'
  	})
  	,baseParams:{task: "action",action: 'panel.acymailing2.getOptions',msc_id: oseMscs.msc_id}
  	,reader: new Ext.data.JsonReader({
	    root: 'results',
	    totalProperty: 'total'
  	},[
	    {name: 'id', type: 'string', mapping: 'id'}
	    ,{name: 'idurl', type: 'string', mapping: 'idurl'}
	    ,{name: 'optionname', type: 'string', mapping: 'optionname'}
	    ,{name: 'acymailing2.listid', type: 'string', mapping: 'listid'}
 	])
  	,sort: 'ordering'
  	,autoLoad:{}
})

	oseMscAddon.acymailing2 = new Ext.grid.GridPanel({
		store: oseMscAddon.acymailing2Params.gridStore
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
                    	
                    	oseMscAddon.acymailing2Params.openCWin(grid,rowIndex);
                    }
                }]
            }
        ]
    })
		,sm: oseMscAddon.acymailing2Params.gridSm
		,bbar:new Ext.PagingToolbar({
		pageSize: 20,
		store: oseMscAddon.acymailing2Params.gridStore,
		displayInfo: true,
	    displayMsg: 'Displaying topics {0} - {1} of {2}',
	    emptyMsg: "No topics to display"

    })
		//,viewConfig: {forceFit: true}
		,autoExpandColumn: 'option'
	,height: 500
	});
	