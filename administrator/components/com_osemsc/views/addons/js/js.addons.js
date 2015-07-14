Ext.ns('oseMsc','oseMsc.addon');
Ext.onReady(function(){

	oseMsc.addon.enable_func = function(bool_side)	{
		var node = oseMsc.addon.grid.getSelectionModel().getSelected();
		oseMsc.addon.grid.body.mask('wait...')
		Ext.Ajax.request({
			url:'index.php?option=com_osemsc&controller=addons',
			params:{task:'enableAddon',isBackend:bool_side,addon_id:node.id},	
			success: function(response,opts){
				var msg = Ext.decode(response.responseText);
				oseMsc.msg.setAlert(msg.title,msg.content);
				
				oseMsc.addon.grid.getStore().reload();
				oseMsc.addon.grid.getView().refresh();
				oseMsc.addon.grid.body.unmask()
			}
		})
	};
	
	
	
	
	oseMsc.addon.store = new Ext.data.Store({
		proxy: new Ext.data.HttpProxy({
    		url: 'index.php?option=com_osemsc&controller=addons'
    		,method: 'POST'
		})
		,baseParams:{task: "getList",limit: 20}
		,reader: new Ext.data.JsonReader({   
              // we tell the datastore where to get his data from
			root: 'results',
			totalProperty: 'total'
		},[ 
			{name: 'id', type: 'int', mapping: 'id'},
    		{name: 'title', type: 'string', mapping: 'title'},
    		{name: 'name', type: 'string', mapping: 'name'},
    		{name: 'type', type: 'string', mapping: 'type'},
   			{name: 'ordering', type: 'string', mapping: 'ordering'},
   			{name: 'backend', type: 'string', mapping: 'backend'},
   			{name: 'frontend', type: 'string', mapping: 'frontend'},
   			{name: 'backend_enabled', type: 'string', mapping: 'backend_enabled'},
   			{name: 'frontend_enabled', type: 'string', mapping: 'frontend_enabled'},
  		])
  		
		,sortInfo:{field: 'id', direction: "ASC"}
		,listeners: {
			beforeload: function(s){
				s.setBaseParam('addontype',oseMsc.addon.addonCombo.getValue())
			}
		}
	});
	
	oseMsc.addon.cm = new Ext.grid.ColumnModel({
		defaults: {
            sortable: true
            ,width: 100
        },
        columns: [
	        new Ext.grid.RowNumberer({header:'#'})
		    ,{id: 'id', header: 'ID', dataIndex: 'id', hidden: true,hideable:true}
		    ,{id: 'title', header: 'Title', dataIndex: 'title'}
		    //,{id: 'name', header: 'Name', dataIndex: 'name',width: 150}
		    ,{id: 'type', header: 'Type', dataIndex: 'type',width: 200}
		    //,{id: 'ordering', header: 'Ordering', dataIndex: 'ordering',align: 'center',width: 70}
		    ,{
		    	id: 'frontend', header: 'Frontend', dataIndex: 'frontend',align: 'center'
		    	,renderer: function(val)	{
		    		if(val == 1)	{
		    			return 'available';
		    		}	else	{
		    			return '--';
		    		}
		    	}
		    }
		    ,{
		    	xtype: 'actioncolumn'
                ,width: 50
                ,align: 'center'
                ,items: [{
                    getClass: function(v, meta, rec)	{
                		if (rec.get('frontend') == 1) {
                            //this.items[1].tooltip = 'Do not buy!';
                            if (rec.get('frontend_enabled') == 1) {
                            	return 'enable-col';
                            }	else	{
                            	return 'disable-col';
                            }
                        } else {
                            //this.items[1].tooltip = 'Buy stock';
                            return 'none-col';
                        }
                		
                	}
                    ,tooltip: 'Click to change'
                    ,handler: function(grid, rowIndex, colIndex) {
                    	grid.getSelectionModel().selectRow(rowIndex)
                    	var r = grid.getSelectionModel().getSelected();
                    	if(r.data.frontend == 1)	{
                    		oseMsc.addon.enable_func(0)
                    	}
                    }
                }]
		    }
		    ,{
		    	id: 'backend', header: 'Backend', dataIndex: 'backend',align: 'center'
		    	,renderer: function(val)	{
		    		if(val == 1)	{
		    			return 'available';
		    		}	else	{
		    			return '--';
		    		}
		    	}
		    }
		    ,{
		    	xtype: 'actioncolumn'
                ,width: 50
                ,align: 'center'
                ,items: [{
                	getClass: function(v, meta, rec)	{
                		if (rec.get('backend') == 1) {
                            //this.items[1].tooltip = 'Do not buy!';
                            if (rec.get('backend_enabled') == 1) {
                            	return 'enable-col';
                            }	else	{
                            	return 'disable-col';
                            }
                        } else {
                            //this.items[1].tooltip = 'Buy stock';
                            return 'none-col';
                        }
                		
                	}
                    ,tooltip: 'Click to change'
                    ,handler: function(grid, rowIndex, colIndex) {
                        grid.getSelectionModel().selectRow(rowIndex)
                    	var r = grid.getSelectionModel().getSelected();
                    	if(r.data.backend == 1)	{
                    		oseMsc.addon.enable_func(1)
                    	}
                    }
                }]
		    }
	    ]
	});
	
	oseMsc.addon.addonCombo = new Ext.form.ComboBox({
		ref: 'combo'
    	,hiddenName: 'addontype'
	    ,typeAhead: true
	    ,triggerAction: 'all'
	    ,lazyRender:false
	    ,lastQuery: ''
	    ,mode: 'remote'
    	,store: new Ext.data.Store({
			proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=addons',
        		method: 'POST'
	      	})
			,baseParams:{task: "getAddonTypes"}
			,reader: new Ext.data.JsonReader({   
				root: 'results',
				totalProperty: 'total'
			},[ 
				{name: 'text', type: 'string', mapping: 'text'},
				{name: 'value', type: 'string', mapping: 'value'}
			])
			,sortInfo:{field: 'text', direction: "ASC"}
			,listeners: {
				load: function(s,r)	{
					var t = oseMsc.addon.addonCombo;
					//alert(t.items.items[4].toSource())
					t.setValue(r[0].data.value);
					t.fireEvent('select',t,r[0],0)
				}
			}
		})

	    ,valueField: 'value'
	    ,displayField: 'text'
	    
	    ,listeners: {
	        select: function(c,r,i)	{
    			oseMsc.addon.grid.getStore().reload({
    				params:{addontype:r.data.value}
    			});
    		}
        }
	})

	oseMsc.addon.grid = new Ext.grid.GridPanel({
		height: 650
		,autoScroll: true
		
		,store: oseMsc.addon.store
		,cm:oseMsc.addon.cm
		,sm:new Ext.grid.RowSelectionModel({singleSelect:true})
		,autoExpandColumn: 'title'
		
		//,viewConfig:{forceFit: true}
		,tbar: new Ext.Toolbar({
			items:[{
				text: 'Enable Frontend',
				handler: oseMsc.addon.enable_func.createDelegate(this, [0])
				
			},{
				text: 'Enable Backend',
				handler: oseMsc.addon.enable_func.createDelegate(this, [1])
			},'->',{
		    	text: 'Addon Type'
		    },oseMsc.addon.addonCombo]
		})
            
     	,bbar:new Ext.PagingToolbar({
    		pageSize: 20
    		,store: oseMsc.addon.store
    		,plugins: new Ext.ux.grid.limit({})
    		,displayInfo: true
		    ,displayMsg: 'Displaying topics {0} - {1} of {2}'
		    ,emptyMsg: "No topics to display"
	    })
	    ,listeners: {
	    	render: function(g)	{
	    		oseMsc.addon.addonCombo.getStore().load();
	    	}
	    }
	});
	
	oseMsc.addon.panel = new Ext.Panel({
		border: false
		,width: Ext.get('ose-addon-list').getWidth() - 15
		,items: [oseMsc.addon.grid]
		,renderTo: 'ose-addon-list'
	})
	
	//oseMsc.addon.grid.render('ose-addon-list');
});