Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();
	
	var addonContentComponent_sm = new Ext.grid.CheckboxSelectionModel({
		//singleSelect:true,
		listeners:{
			selectionchange: function(sm,node){
				oseMscAddon.component.grid.getTopToolbar().findById('show-to-members').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.component.grid.getTopToolbar().findById('show-to-all').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.component.grid.getTopToolbar().findById('hide-to-members').setDisabled(sm.getCount() < 1); // >
			}
		}
	});
	var addonContentComponent_store = new Ext.data.Store({
		proxy: new Ext.data.HttpProxy({
	      	url: 'index.php?option=com_osemsc&controller=content',
            method: 'POST'
      	}),
	  	baseParams:{task: "action",action:'content.component.getList',msc_id:''}, 
	  	reader: new Ext.data.JsonReader({   
		            
		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'id'
	  	},[ 
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'name', type: 'string', mapping: 'name'},
		    {name: 'controlled', type: 'string', mapping: 'controlled'},
  		]),
	  	//sortInfo:{field: 'user_id', direction: "ASC"},
	  	
	  	listeners: {
	  		beforeload: function(store,records,options)	{
		  		store.setBaseParam('msc_id',oseMsc.msc_id);
		  	}
	  	}
	});
	
	var addonContentModule_cm = new Ext.grid.ColumnModel({
		defaults:{},
		columns:[ 
			addonContentComponent_sm,
			new Ext.grid.RowNumberer({header:'#'}),
		    {id:'id',header: Joomla.JText._("ID"), width: 200, dataIndex: 'id',hidden:true},
	        {header: Joomla.JText._('Component_Item'),  dataIndex: 'name'},
	        {header: Joomla.JText._("Controlled"),  dataIndex: 'controlled'}
	  	]
  	});
	
	oseMscAddon.component = new Ext.Panel({
		//title: 'Component',
		listeners:{
			render: function(p)	{
				addonContentComponent_store.load();
			}
		},
		items:[{
			ref:'grid',
			xtype:'grid',
			autoScroll:true,
			height: 400,
			viewConfig:{forceFit: true},
			//autoHeight: true,
			//plugins: [addonContentModule_filter],
			store: addonContentComponent_store,
			sm: addonContentComponent_sm,
			cm: addonContentModule_cm,
			
			bbar: new Ext.PagingToolbar({
	    		pageSize: 20,
	    		store: addonContentComponent_store,
	    		displayInfo: true,
	    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
				emptyMsg: Joomla.JText._("No_topics_to_display")
		    }),
		    
		    tbar:[{
		    	text: Joomla.JText._('Show_to_Members'),
		    	id:'show-to-members',
		    	disabled:true,
		    	handler: function()	{
		    		var ids = oseMscAddon.component.grid.getSelectionModel().getSelections();
		    		
		    		var sids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			sids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.component.changeStatus','com_ids[]':sids,
		    				msc_id:oseMsc.msc_id, status: '1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.component.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.component.grid.getStore().reload();
		    					oseMscAddon.component.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },{
		    	text: Joomla.JText._('Show_to_All'),
		    	id:'show-to-all',
		    	disabled:true,
		    	handler: function()	{
		    		var ids = oseMscAddon.component.grid.getSelectionModel().getSelections();
		    		
		    		var sids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			sids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.component.changeStatus','com_ids[]':sids,
		    				msc_id:oseMsc.msc_id, status: '0'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.component.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.component.grid.getStore().reload();
		    					oseMscAddon.component.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },{
		    	text: Joomla.JText._('Hide_to_Members'),
		    	id:'hide-to-members',
		    	disabled:true,
		    	hidden: true,
		    	handler: function()	{
		    		var ids = oseMscAddon.component.grid.getSelectionModel().getSelections();
		    		
		    		var sids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			sids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.component.changeStatus','com_ids[]':sids,
		    				msc_id:oseMsc.msc_id,status: '-1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.component.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.component.grid.getStore().reload();
		    					oseMscAddon.component.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },'->',{
		    	text: Joomla.JText._('Template')
		    },{
		    	xtype: 'combo',
		    	ref: 'assigned',
		    	hiddenName: 'assigned',
	            width: 125,
			    typeAhead: true,
			    triggerAction: 'all',
			    lazyRender:false,
			    mode: 'remote',
		    	store:new Ext.data.Store({
					proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=content',
	            		method: 'POST'
			      	}),
					baseParams:{task: "action",action:'content.component.getFilterTypes',filter_type:'assigned'}, 
					reader: new Ext.data.JsonReader({   
						root: 'results',
						totalProperty: 'total'
					},[ 
						{name: 'value', type: 'string', mapping: 'value'},
						{name: 'text', type: 'string', mapping: 'text'}
					])
					//sortInfo:{field: 'name', direction: "ASC"},
					
				}),

			    valueField: 'value',
			    displayField: 'text',
			    
			    listeners: {
			        // delete the previous query in the beforequery event or set
			        // combo.lastQuery = null (this will reload the store the next time it expands)
			        beforequery: function(qe){
			        	delete qe.combo.lastQuery;
			        },
	    			afterrender: function(e)	{
	    				e.setValue('');
	    			},
		    		select: function(c,r,i)	{
		    			var t = oseMscAddon.component.grid.getTopToolbar();
		    			oseMscAddon.component.grid.getStore().reload({
		    				params:{
		    					msc_id:oseMscs.msc_id
		    				}
		    			});
		    		}
		        }
		    },
				new Ext.ux.form.SearchField({
	                store: addonContentComponent_store,
	                paramName: 'search',
	                width:150
	            })
			]
		}]
	});