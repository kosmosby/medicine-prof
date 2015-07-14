Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();

	var addonContentPhoca_sm = new Ext.grid.CheckboxSelectionModel({
		//singleSelect:true,
		listeners:{
			selectionchange: function(sm,node){
				oseMscAddon.phoca.grid.getTopToolbar().findById('show-to-members').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.phoca.grid.getTopToolbar().findById('show-to-all').setDisabled(sm.getCount() < 1); // >
				//oseMscAddon.k2cat.grid.getTopToolbar().findById('hide-to-members').setDisabled(sm.getCount() < 1); // >
			}
		}
	});
	var addonContentPhoca_action = function(newStatus)	{
		var ids = oseMscAddon.phoca.grid.getSelectionModel().getSelections();

		var cat_ids = new Array();
		for(i=0;i < ids.length; i++)	{
			var r = ids[i];
			cat_ids[i] = r.id;
		}

		Ext.Ajax.request({
			url:'index.php?option=com_osemsc&controller=content',
			params:{
				task:'action',action:'content.phoca.changeStatus','cat_ids[]':cat_ids,
				msc_id:oseMsc.msc_id, status: newStatus
			},
			success: function(response,opt)	{
				var msg = Ext.decode(response.responseText);
				oseMscAddon.msg.setAlert(msg.title,msg.content);

				if(msg.success)	{
					oseMscAddon.phoca.grid.getSelectionModel().clearSelections();
					oseMscAddon.phoca.grid.getStore().reload();
					oseMscAddon.phoca.grid.getView().refresh();
				}
			}
		});
	}


	var addonContentPhoca_store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=content',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'content.phoca.getList',msc_id:''},
		  reader: new Ext.data.JsonReader({

		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'id'
		  },[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'title', type: 'string', mapping: 'title'},
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



	var addonContentPhoca_cm = new Ext.grid.ColumnModel({
		defaults:{},
		columns:[
			addonContentPhoca_sm,
			new Ext.grid.RowNumberer({header:'#'}),
		    {id:'id',header: Joomla.JText._("ID"), width: 200, dataIndex: 'id',hidden:true},
	        {header: Joomla.JText._("Title"),  dataIndex: 'title'},
	        {
	        	header: Joomla.JText._("Controlled"),  dataIndex: 'controlled'
	        },
	        {header: Joomla.JText._("Type"), dataIndex: 'type'}
	  	]
  	});

	oseMscAddon.phoca = new Ext.Panel({
		//title: 'Menu',
		listeners:{
			render: function(p)	{
				addonContentPhoca_store.load();
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



			store: addonContentPhoca_store,
			sm: addonContentPhoca_sm,
			cm: addonContentPhoca_cm,

			bbar: new Ext.PagingToolbar({
	    		pageSize: 20,
	    		store: addonContentPhoca_store,
	    		displayInfo: true,
	    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
				emptyMsg: Joomla.JText._("No_topics_to_display")
		    }),

		    tbar:[
		    {
		    	id:'show-to-members',
		    	text: Joomla.JText._('Show_to_Members'),
		    	handler: function()	{
		    		addonContentPhoca_action(1)
		    	}
		    },{
		    	id:'show-to-all',
		    	text: Joomla.JText._('Show_to_All'),
		    	handler: function()	{
		    		addonContentPhoca_action(0)
		    	}
		    },
		    '->',
		    {
		    	xtype: 'combo'
		    	,hidden:true
		    	,hiddenName: 'section'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,lastQuery: ''
		    	,store:new Ext.data.Store({
					proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=content',
	            		method: 'POST'
			      	}),
					baseParams:{task: "action",action:'content.phoca.getSections'},
					reader: new Ext.data.JsonReader({
						root: 'results',
						totalProperty: 'total'
					},[
						{name: 'id', type: 'string', mapping: 'id'},
						{name: 'title', type: 'string', mapping: 'title'}
					]),
					sortInfo:{field: 'id', direction: "ASC"}
					,autoLoad: {}
				}),

			    valueField: 'id',
			    displayField: 'title',

			    listeners: {
			        // delete the previous query in the beforequery event or set
			        // combo.lastQuery = null (this will reload the store the next time it expands)

		    		select: function(c,r,i)	{
		    			oseMscAddon.phoca.grid.getStore().reload({
		    				params:{section:r.data.id}
		    			});
		    		}
		        }
		    },'-',
				new Ext.ux.form.SearchField({
	                store: addonContentPhoca_store,
	                paramName: 'search',
	                width:150
	            })
			]
		}]
	});