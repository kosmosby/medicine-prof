Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();

	var addonContentOsedownload_sm = new Ext.grid.CheckboxSelectionModel({
		//singleSelect:true,
		listeners:{
			selectionchange: function(sm,node){
				oseMscAddon.osedownload.grid.getTopToolbar().findById('show-to-members').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.osedownload.grid.getTopToolbar().findById('show-to-all').setDisabled(sm.getCount() < 1); // >
				//oseMscAddon.osedownload.grid.getTopToolbar().findById('hide-to-members').setDisabled(sm.getCount() < 1); // >
			}
		}
	});
	var addonContentOsedownload_action = function(newStatus)	{
		var ids = oseMscAddon.osedownload.grid.getSelectionModel().getSelections();

		var cat_ids = new Array();
		for(i=0;i < ids.length; i++)	{
			var r = ids[i];
			cat_ids[i] = r.id;
		}

		Ext.Ajax.request({
			url:'index.php?option=com_osemsc&controller=content',
			params:{
				task:'action',action:'content.osedownload.changeStatus','cat_ids[]':cat_ids,
				msc_id:oseMsc.msc_id, status: newStatus
			},
			success: function(response,opt)	{
				var msg = Ext.decode(response.responseText);
				oseMscAddon.msg.setAlert(msg.title,msg.content);

				if(msg.success)	{
					oseMscAddon.osedownload.grid.getSelectionModel().clearSelections();
					oseMscAddon.osedownload.grid.getStore().reload();
					oseMscAddon.osedownload.grid.getView().refresh();
				}
			}
		});
	}


	var addonContentOsedownload_store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=content',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'content.osedownload.getList',msc_id:''},
		  reader: new Ext.data.JsonReader({

		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'id'
		  },[
		     {name: 'id', type: 'int', mapping: 'id'},
			 {name: 'treename', type: 'string', mapping: 'treename'},
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



	var addonContentOsedownload_cm = new Ext.grid.ColumnModel({
		defaults:{},
		columns:[
			addonContentOsedownload_sm,
			new Ext.grid.RowNumberer({header:'#'}),
		    {id:'id',header: Joomla.JText._("ID"), width: 200, dataIndex: 'id',hidden:true},
	        {header: Joomla.JText._("Title"),  dataIndex: 'treename'},
	        {
	        	header: Joomla.JText._("Controlled"),  dataIndex: 'controlled'
	        },
	        {header: Joomla.JText._("Type"), dataIndex: 'type'}
	  	]
  	});

	oseMscAddon.osedownload = new Ext.Panel({
		//title: 'Menu',
		listeners:{
			render: function(p)	{
				addonContentOsedownload_store.load();
			}
		},

		items:[
		{
				html:Joomla.JText._('Instructions')+': <a href="http://wiki.opensource-excellence.com/index.php?title=Setup_Phoca_categories_control" target="_blank"> '+Joomla.JText._('LINK')+' </a> '
				,border: false
				,bodyStyle: 'text-align: left; padding: 5px; margin-left: 20x;'
		},
		{
			ref:'grid',
			xtype:'grid',
			autoScroll:true,
			height: 500,
			viewConfig:{forceFit: true},
			//autoHeight: true,
			//plugins: [addonContentPhoca_filter],



			store: addonContentOsedownload_store,
			sm: addonContentOsedownload_sm,
			cm: addonContentOsedownload_cm,

			bbar: new Ext.PagingToolbar({
	    		pageSize: 20,
	    		store: addonContentOsedownload_store,
	    		displayInfo: true,
	    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
				emptyMsg: Joomla.JText._("No_topics_to_display")
		    }),

		    tbar:[
		    {
		    	id:'show-to-members',
		    	text: Joomla.JText._('Show_to_Members'),
		    	disabled:true,
		    	handler: function()	{
		    		addonContentOsedownload_action(1)
		    	}
		    },{
		    	id:'show-to-all',
		    	text: Joomla.JText._('Show_to_All'),
		    	disabled:true,
		    	handler: function()	{
		    		addonContentOsedownload_action(0)
		    	}
		    },
		    '->',
				new Ext.ux.form.SearchField({
	                store: addonContentOsedownload_store,
	                paramName: 'search',
	                width:150
	            })
			]
		}]
	});