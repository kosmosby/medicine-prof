Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();
	
	var addonContentRokdownload_sm = new Ext.grid.CheckboxSelectionModel({
		//singleSelect:true,
		listeners:{
			selectionchange: function(sm,node){
				oseMscAddon.rokdownload.grid.getTopToolbar().findById('show-to-members').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.rokdownload.grid.getTopToolbar().findById('show-to-all').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.rokdownload.grid.getTopToolbar().findById('hide-to-members').setDisabled(sm.getCount() < 1); // >
			}
		}
	});
	
	var addonContentRokdownload_store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=content',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'content.rokdownload.getList',msc_id:''},
		  reader: new Ext.data.JsonReader({

		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'id'
		  },[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'name', type: 'string', mapping: 'name'},
		    {name: 'path', type: 'string', mapping: 'path'},
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


	var addonContentRokdownload_cm = new Ext.grid.ColumnModel({
		defaults:{},
		columns:[
			addonContentRokdownload_sm,
			new Ext.grid.RowNumberer({header:'#'}),
		    {id:'id',header: Joomla.JText._("ID"), width: 200, dataIndex: 'id',hidden:true},
	        {header: Joomla.JText._('RokDownload_Category'),  dataIndex: 'name'},
	        {header: Joomla.JText._('Path'),  dataIndex: 'path'},
	        {header: Joomla.JText._("Controlled"),  dataIndex: 'controlled'},
	        {header: Joomla.JText._("Type"),  dataIndex: 'type'}
	  	]
  	});

	oseMscAddon.rokdownload = new Ext.Panel({
		//title: 'Menu',
		listeners:{
			render: function(p)	{
				addonContentRokdownload_store.load();
			}
		},

		items:[{
			ref:'grid',
			xtype:'grid',
			autoScroll:true,
			height: 400,
			viewConfig:{forceFit: true},

			store: addonContentRokdownload_store,
			sm: addonContentRokdownload_sm,
			cm: addonContentRokdownload_cm,

			bbar: new Ext.PagingToolbar({
	    		pageSize: 20,
	    		store: addonContentRokdownload_store,
	    		displayInfo: true,
	    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
				emptyMsg: Joomla.JText._("No_topics_to_display")
		    }),

		    tbar:[{
		    	text: Joomla.JText._('Show_to_Members'),
		    	id:'show-to-members',
		    	disabled:true,
		    	handler: function()	{
		    		var ids = oseMscAddon.rokdownload.grid.getSelectionModel().getSelections();

		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}

		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
	    					task:'action',action:'content.rokdownload.changeStatus','catids[]':catids,
		    				msc_id:oseMsc.msc_id, status: '1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);

		    				if(msg.success)	{
		    					oseMscAddon.rokdownload.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.rokdownload.grid.getStore().reload();
		    					oseMscAddon.rokdownload.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },{
		    	text: Joomla.JText._('Show_to_All'),
		    	id:'show-to-all',
		    	disabled:true,
		    	handler: function()	{
		    		var ids = oseMscAddon.rokdownload.grid.getSelectionModel().getSelections();

		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}

		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.rokdownload.changeStatus','catids[]':catids,
		    				msc_id:oseMsc.msc_id, status: '0'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);

		    				if(msg.success)	{
		    					oseMscAddon.rokdownload.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.rokdownload.grid.getStore().reload();
		    					oseMscAddon.rokdownload.grid.getView().refresh();
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
			    	var ids = oseMscAddon.rokdownload.grid.getSelectionModel().getSelections();
	
		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}
	
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.rokdownload.changeStatus','catids[]':catids,
		    				msc_id:oseMsc.msc_id, status: '-1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
	
		    				if(msg.success)	{
		    					oseMscAddon.rokdownload.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.rokdownload.grid.getStore().reload();
		    					oseMscAddon.rokdownload.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },'->',
				new Ext.ux.form.SearchField({
	                store: addonContentRokdownload_store,
	                paramName: 'search',
	                width:150
	            })
			]
		}]
	});