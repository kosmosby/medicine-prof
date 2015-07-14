Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();
	
	var addonContentAriquizcat_sm = new Ext.grid.CheckboxSelectionModel({
		//singleSelect:true,
		listeners:{
			selectionchange: function(sm,node){
				oseMscAddon.ariquizcat.grid.getTopToolbar().findById('show-to-members').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.ariquizcat.grid.getTopToolbar().findById('show-to-all').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.ariquizcat.grid.getTopToolbar().findById('hide-to-members').setDisabled(sm.getCount() < 1); // >
			}
		}
	});
	
	var addonContentAriquizcat_store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=content',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'content.ariquizcat.getList',msc_id:''}, 
		  reader: new Ext.data.JsonReader({   
		            
		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'id'
		  },[ 
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'name', type: 'string', mapping: 'name'},
		    {name: 'controlled', type: 'string', mapping: 'controlled'},
		    {name: 'type', type: 'string', mapping: 'type'}
		  ]),
		  //sortInfo:{field: 'user_id', direction: "ASC"},
		  listeners: {
			 
		  	beforeload: function(store,records,options)	{
		  		store.setBaseParam('msc_id',oseMscs.msc_id);  	
		  	}
		  }
	});
	
	
	var addonContentAriquizcat_cm = new Ext.grid.ColumnModel({
		defaults:{},
		columns:[ 
			addonContentAriquizcat_sm,
			new Ext.grid.RowNumberer({header:'#'}),
		    {id:'id',header: Joomla.JText._("ID"), width: 200, dataIndex: 'id',hidden:true},
	        {header: Joomla.JText._('ARIQuiz_Category'),  dataIndex: 'name'},
	        {header: Joomla.JText._("Controlled"),  dataIndex: 'controlled'},
	        {header: Joomla.JText._("Type"),  dataIndex: 'type'}
	  	]
  	});
	
	oseMscAddon.ariquizcat = new Ext.Panel({
		//title: 'Menu',
		listeners:{
			render: function(p)	{
				addonContentAriquizcat_store.load();
			}
		},
		
		items:[{
			ref:'grid',
			xtype:'grid',
			autoScroll:true,
			height: 400,
			viewConfig:{forceFit: true},
			
			store: addonContentAriquizcat_store,
			sm: addonContentAriquizcat_sm,
			cm: addonContentAriquizcat_cm,
			
			bbar: new Ext.PagingToolbar({
	    		pageSize: 20,
	    		store: addonContentAriquizcat_store,
	    		displayInfo: true,
	    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
				emptyMsg: Joomla.JText._("No_topics_to_display")
		    }),
		    
		    tbar:[{
		    	text: Joomla.JText._('Show_to_Members'),
		    	id:'show-to-members',
		    	disabled:true,
		    	handler: function()	{
		    		var ids = oseMscAddon.ariquizcat.grid.getSelectionModel().getSelections();
		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
	    					task:'action',action:'content.ariquizcat.changeStatus','catids[]':catids,
		    				msc_id:oseMsc.msc_id, status: '1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.ariquizcat.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.ariquizcat.grid.getStore().reload();
		    					oseMscAddon.ariquizcat.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },{
		    	text: Joomla.JText._('Show_to_All'),
		    	id:'show-to-all',
		    	disabled:true,
		    	handler: function()	{
		    		var ids = oseMscAddon.ariquizcat.grid.getSelectionModel().getSelections();
		    		
		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.ariquizcat.changeStatus','catids[]':catids,
		    				msc_id:oseMsc.msc_id, status: '0'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.ariquizcat.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.ariquizcat.grid.getStore().reload();
		    					oseMscAddon.ariquizcat.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },{
		    	text: Joomla.JText._('Hide_to_Members'),
		    	id:'hide-to-members',
		    	hidden: true,
		    	disabled:true,
		    	handler: function()	{
		    		var ids = oseMscAddon.ariquizcat.grid.getSelectionModel().getSelections();
		    		
		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.ariquizcat.changeStatus','catids[]':catids,
		    				msc_id:oseMsc.msc_id,status: '-1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.ariquizcat.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.ariquizcat.grid.getStore().reload();
		    					oseMscAddon.ariquizcat.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },'->',
				new Ext.ux.form.SearchField({
	                store: addonContentAriquizcat_store,
	                paramName: 'search',
	                width:150
	            })
			]
		}]
	});