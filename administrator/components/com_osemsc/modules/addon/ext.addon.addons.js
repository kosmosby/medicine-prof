Ext.ns('oseMsc','oseMsc.addon');
	oseMsc.addon.msg = new Ext.App();
	
	oseMsc.addon.enable_func = function(bool_side)	{
		var node = oseMsc.addon.grid.getSelectionModel().getSelected();
		Ext.Ajax.request({
			url:'index.php?option=com_osemsc&controller=addons',
			params:{task:'enableAddon',isBackend:bool_side,addon_id:node.id},	
			success: function(response,opts){
				var msg = Ext.decode(response.responseText);
				oseMsc.addon.msg.setAlert(msg.title,msg.content);
				
				oseMsc.addon.grid.getStore().reload();
				oseMsc.addon.grid.getView().refresh();
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
		    ,{id: 'name', header: 'Name', dataIndex: 'name',width: 150}
		    ,{id: 'type', header: 'Type', dataIndex: 'type',width: 150}
		    ,{id: 'ordering', header: 'Ordering', dataIndex: 'ordering',align: 'center',width: 70}
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
	
	oseMsc.addon.formReader = new Ext.data.JsonReader({   
					            
	    root: 'result',
	    totalProperty: 'total',
	   
	    fields:[ 
		    {name: 'addon_id', type: 'int', mapping: 'id'},
		    {name: 'addon.name', type: 'string', mapping: 'name'},
		    {name: 'addon.title', type: 'string', mapping: 'title'},
		    {name: 'addon.type', type: 'string', mapping: 'type'},
		    {name: 'addon.action', type: 'string', mapping: 'action'},
		    {name: 'addon.addon_name', type: 'string', mapping: 'addon_name'},
		    {name: 'addon.frontend', type: 'string', mapping: 'frontend'},
		    {name: 'addon.frontend_enabled', type: 'string', mapping: 'frontend_enabled'},
		    {name: 'addon.backend', type: 'string', mapping: 'backend'},
		    {name: 'addon.backend_enabled', type: 'string', mapping: 'backend_enabled'},
		    {name: 'addon.ordering', type: 'string', mapping: 'ordering'},
	  	]
  	});
  	
	oseMsc.addon.form = {
		xtype:'form',
		ref:'form',
		items:[{
			xtype: 'hidden',
			name:'addon_id'
		},{
			xtype: 'textfield',
			name: 'addon.name',
			fieldLabel: 'Name'
		},{
			xtype: 'textfield',
			name: 'addon.title',
			fieldLabel: 'Title'
		},{
			xtype: 'textfield',
			name: 'addon.type',
			fieldLabel: 'Type'
		},{
			xtype: 'textfield',
			name: 'addon.action',
			fieldLabel: 'Action'
		},{
			xtype: 'textfield',
			name: 'addon.addon_name',
			fieldLabel: 'Addon Name'
		},{
			xtype: 'textfield',
			name: 'addon.frontend',
			fieldLabel: 'Frontend',
			value: 1
		},{
			xtype: 'textfield',
			name: 'addon.frontend_enabled',
			fieldLabel: 'Frontend Enabled',
			value: 1
		},{
			xtype: 'textfield',
			name: 'addon.backend',
			fieldLabel: 'Backend',
			value: 1
		},{
			xtype: 'textfield',
			name: 'addon.backend_enabled',
			fieldLabel: 'Backend Enabled',
			value: 1
		},{
        	ref:'ordering',
        	xtype:'combo',
            fieldLabel: 'Ordering',
            hiddenName: 'ordering',
            anchor:'95%',
		    typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:false,
		    mode: 'remote',

		    store: new Ext.data.Store({
				  proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=addons',
			            method: 'POST'
			      }),
				  baseParams:{task: "getOrder", addon_id: '',type:''}, 
				  reader: new Ext.data.JsonReader({   
				    root: 'results',
				    totalProperty: 'total'
				  },[ 
				    {name: 'Order', type: 'int', mapping: 'ordering'},
				    {name: 'Title', type: 'string', mapping: 'displayText'}
				  ])
				  //sortInfo:{field: 'Order', direction: "ASC"},
			}),

		    valueField: 'Order',
		    displayField: 'Title'
        }],
        
        reader: oseMsc.addon.formReader
	};
	
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
		
		//,viewConfig:{forceFit: true}
		,autoExpandColumn: 'title'
		
		,tbar: new Ext.Toolbar({
			items:[{
				text:'Add',
				handler: function(){
					if(!oseMscAddonForm)	{
						var oseMscAddonForm = new Ext.Window({
							width: 500,
							
							tbar: [{
								text: 'Save',
								handler: function()	{
									//var node = oseMsc.addon.grid.getSelectionModel().getSelected();
									oseMscAddonForm.form.getForm().submit({
										url:'index.php?option=com_osemsc&controller=addons',
										params:{task:'save'},
										success: function(form,action)	{
											var msg = action.result;
											oseMsc.addon.msg.setAlert(msg.title,msg.content);
											
											oseMsc.addon.grid.getStore().reload();
											oseMsc.addon.grid.getView().refresh();
										}
									});
								}
							}],
							items:[
								oseMsc.addon.form
							],
							
							listeners: {
								show: function(w){
									
								}
							}
						})
					}
					
					oseMscAddonForm.show(this);
				}
			},{
				text:'Edit',
				handler: function(){
					if(!oseMscAddonForm)	{
						var oseMscAddonForm = new Ext.Window({
							width: 500,
							
							tbar: [{
								text: 'Save',
								handler: function()	{
									var node = oseMsc.addon.grid.getSelectionModel().getSelected();
									oseMscAddonForm.form.getForm().submit({
										url:'index.php?option=com_osemsc&controller=addons',
										params:{task:'save'},
										success: function(form,action)	{
											var msg = action.result;
											oseMsc.addon.msg.setAlert(msg.title,msg.content);
											
											oseMsc.addon.grid.getStore().reload();
											oseMsc.addon.grid.getView().refresh();
										}
									})
								}
							}],
							items:[
								oseMsc.addon.form
							],
							
							listeners: {
								show: function(w){
									var node = oseMsc.addon.grid.getSelectionModel().getSelected();
									//w.form.ordering.getStore().setBaseParam('addon_id',node.id);
									w.form.ordering.getStore().setBaseParam('type',node.data.type);
									w.form.ordering.getStore().reload();
									w.form.getForm().load({
										url:'index.php?option=com_osemsc&controller=addons',
										params:{task:'getAddon',addon_id:node.id}
									})
								}
							}
						})
					}
					
					oseMscAddonForm.show(this);
				}
			},{
				text:'Remove',
				handler: function(){
					Ext.Msg.confirm('Notice','Are You Sure to Remove',function(btn,text){
						if(btn == 'yes')	{
							var node = oseMsc.addon.grid.getSelectionModel().getSelected();
							Ext.Ajax.request({
								url:'index.php?option=com_osemsc&controller=addons',
								params:{task:'remove',addon_id:node.id},
								success: function(response,opt)	{
									var msg = Ext.decode(response.responseText);
									oseMsc.addon.msg.setAlert(msg.title,msg.content);
									if(msg.success)	{
										oseMsc.addon.grid.getStore().reload();
										oseMsc.addon.grid.getView().refresh();
									}
								}
							})
						}
					})
				}
			},{
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
    		pageSize: 20,
    		store: oseMsc.addon.store,
    		displayInfo: true,
		    displayMsg: 'Displaying topics {0} - {1} of {2}',
		    emptyMsg: "No topics to display"
	    })
	    
	    ,listeners: {
	    	render: function(g)	{
	    		oseMsc.addon.addonCombo.getStore().load();
	    	}
	    }
	});