Ext.ns('oseMscAddon','oseMscAddon.menuParams');

	oseMscAddon.msg = new Ext.App();
	var addonContentMenu_sm = new Ext.grid.CheckboxSelectionModel({
		listeners:{
			selectionchange: function(sm,node){
				//addonMemberLicuser.getTopToolbar().editBtn.setDisabled(sm.getCount() != 1); // >
				//addonMemberLicuser.getTopToolbar().removeBtn.setDisabled(sm.getCount() != 1); // >
			}
		}
	});
	var addonContentMenu_store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=content',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'content.menu.getList',msc_id:''}, 
		  reader: new Ext.data.JsonReader({   
		            
		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'id'
		  },[ 
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'treename', type: 'string', mapping: 'treename'},
		    {name: 'controlled', type: 'string', mapping: 'controlled'},
		    {name: 'type', type: 'string', mapping: 'type'},
		    {name: 'time_length', type: 'string', mapping: 'time_length'},
		    {name: 'time_unit', type: 'string', mapping: 'time_unit'}
		  ]),
		  //sortInfo:{field: 'user_id', direction: "ASC"},
		  listeners: {
		  	beforeload: function(store,records,options)	{
		  		store.setBaseParam('msc_id',oseMscs.msc_id);
		  		store.setBaseParam('menutype',oseMscAddon.menuParams.menutype);
		  	}
		  }
	});
	
	/*
	var addonContentMenu_filter = new Ext.ux.grid.GridFilters({
        // encode and local configuration options defined previously for easier reuse
        encode: true, // json encode the filter query
        local: true,   // defaults to false (remote filtering)
        filters: [{
            type: 'list',
            dataIndex: 'restricted',
            
            options: ['Controlled']
        }]
    });    
    */
	
	
	var addonContentMenu_cm = new Ext.grid.ColumnModel({
		defaults:{},
		columns:[ 
			addonContentMenu_sm,
			new Ext.grid.RowNumberer({header:'#'}),
		    {id:'id',header: Joomla.JText._("ID"), width: 200, dataIndex: 'id',hidden:true},
	        {header: Joomla.JText._("Menu Item"),  dataIndex: 'treename'},
	        {	
	        	header: Joomla.JText._("Controlled"),  dataIndex: 'controlled'
	        },{
	        	header: Joomla.JText._('Time_Length'),  dataIndex: 'time_length', width: 150
        		,editor: new Ext.form.TextField({})
	        	
	        },{
	        	header: "Time Unit",  dataIndex: 'time_unit', width: 150
	        	,editor:new Ext.form.ComboBox({
			        width: 50
				    ,typeAhead: true
				    ,triggerAction: 'all'
				    ,lazyRender:true
				    ,mode: 'local'
				    ,store: new Ext.data.ArrayStore({
				        fields: [
				            'time_unit'
				            ,'Title'
				        ]
				        ,data: [
				        	//['hour', 'Hour(s)']
				        	['day', 'Day(s)']
				        	,['week', 'Week(s)']
				        	,['month', 'Month(s)']
				        	//['year', 'Year(s)']
				        ]
					})
					,valueField: 'time_unit'
		    		,displayField: 'Title'
		    		
				 }),renderer: function(val)	{
		        		switch(val)
		        		{
		        			case('day'):
		        				return 'Day(s)';
		        			break;
		        			case('week'):
		        				return 'Week(s)';
		        			break;
		        			case('month'):
		        				return 'Month(s)';
		        			break;
		        		}
		        	}
	        },
	        {header: Joomla.JText._("Type"), dataIndex: 'type'}
	  	]
  	});
	
	oseMscAddon.menu = new Ext.Panel({
		items:[{
			ref:'grid'
			,xtype:'editorgrid'
			,autoScroll:true
			,height: 400
			,viewConfig:{forceFit: true}
			,store: addonContentMenu_store
			,sm: addonContentMenu_sm
			,cm: addonContentMenu_cm
			,listeners:{
				render: function(p)	{
					addonContentMenu_store.load();
				}
			}
			,bbar: new Ext.PagingToolbar({
	    		pageSize: 20
	    		,store: addonContentMenu_store
	    		,plugins: new Ext.ux.grid.limit({})
	    		,displayInfo: true
	    		,displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}'
				,emptyMsg: Joomla.JText._("No_topics_to_display")
		    })
		    
		    ,tbar:[{
		    	text: Joomla.JText._('Show_to_Members')
		    	,handler: function()	{
		    		var ids = oseMscAddon.menu.grid.getSelectionModel().getSelections();
		    		
		    		var menu_ids = new Array();
		    		var time_length = new Array();
		    		var time_unit = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			menu_ids[i] = r.id;
		    			time_length[i] = r.get('time_length');
		    			time_unit[i] = r.get('time_unit');
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.menu.changeStatus','menu_ids[]':menu_ids,
		    				msc_id:oseMsc.msc_id, status: '1','time_length[]':time_length
		    				,'time_unit[]':time_unit
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.menu.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.menu.grid.getStore().reload();
		    					oseMscAddon.menu.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },{
		    	text: Joomla.JText._('Show_to_All'),
		    	handler: function()	{
		    		var ids = oseMscAddon.menu.grid.getSelectionModel().getSelections();
		    		
		    		var menu_ids = new Array();
		    		var time_length = new Array();
		    		var time_unit = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			menu_ids[i] = r.id;
		    			time_length[i] = r.get('time_length');
		    			time_unit[i] = r.get('time_unit');
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.menu.changeStatus','menu_ids[]':menu_ids,
		    				msc_id:oseMsc.msc_id, status: '0','time_length[]':time_length
		    				,'time_unit[]':time_unit
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.menu.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.menu.grid.getStore().reload();
		    					oseMscAddon.menu.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },{
		    	text: Joomla.JText._('Hide_to_Members'),
		    	handler: function()	{
		    		var ids = oseMscAddon.menu.grid.getSelectionModel().getSelections();
		    		
		    		var menu_ids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			menu_ids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.menu.changeStatus','menu_ids[]':menu_ids,
		    				msc_id:oseMsc.msc_id,status: '-1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.menu.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.menu.grid.getStore().reload();
		    					oseMscAddon.menu.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },'->',{
		    	text: Joomla.JText._('Menu_Type')
		    },{
		    	xtype: 'combo'
		    	,hiddenName: 'menutype'
		    	,id: 'menuTypeCombo'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,lastQuery: ''
			    ,mode: 'remote'
		    	,store:new Ext.data.Store({
					proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=content',
	            		method: 'POST'
			      	})
					,baseParams:{task: "action",action:'content.menu.getMenuTypes'}
					,reader: new Ext.data.JsonReader({   
						root: 'results',
						totalProperty: 'total'
					},[ 
						{name: 'id', type: 'string', mapping: 'menutype'},
						{name: 'name', type: 'string', mapping: 'menutype'}
					])
					,sortInfo:{field: 'name', direction: "ASC"}
					,autoLoad: {}
					,listeners: {
						load: function(s,r)	{
							var combo = oseMscAddon.menu.grid.getTopToolbar().findById('menuTypeCombo')
							
							if(!oseMscAddon.menuParams.menutype)	{
								combo.setValue(r[0].data.id)
								combo.fireEvent('select',combo,r[0],0)
								
							}	else	{
								combo.setValue(oseMscAddon.menuParams.menutype)
							}
							
						}
					}
				})
			    ,valueField: 'id'
			    ,displayField: 'name'
			    ,listeners: {
		    		select: function(c,r,i)	{
		    			oseMscAddon.menuParams.menutype = r.data.name;
		    			oseMscAddon.menu.grid.getStore().reload({
		    				params:{msc_id:oseMscs.msc_id}
		    			});
		    		}
		        }
		    },'-',
				new Ext.ux.form.SearchField({
	                store: addonContentMenu_store,
	                paramName: 'search',
	                width:150
	            })
			]
		}]
	});