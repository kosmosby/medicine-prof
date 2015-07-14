Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();
	var addonContentMtree_sm = new Ext.grid.CheckboxSelectionModel({
		//singleSelect:true,
		listeners:{
			selectionchange: function(sm,node){
				oseMscAddon.mtree.grid.getTopToolbar().findById('show-to-members').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.mtree.grid.getTopToolbar().findById('show-to-all').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.mtree.grid.getTopToolbar().findById('hide-to-members').setDisabled(sm.getCount() < 1); // >
			}
		}
	});
	
	var addonContentMtree_store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=content',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'content.mtree.getList',msc_id:''},
		  reader: new Ext.data.JsonReader({

		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'cat_id'
		  },[
		    {name: 'id', type: 'int', mapping: 'cat_id'},
		    {name: 'treename', type: 'string', mapping: 'treename'},
		    {name: 'controlled', type: 'string', mapping: 'controlled'},
		    {name: 'type', type: 'string', mapping: 'type'}
		  ]),
		  //sortInfo:{field: 'user_id', direction: "ASC"},
		  listeners: {
		  	beforeload: function(store,records,options)	{
			  	var levellimit = oseMscAddon.mtree.grid.getTopToolbar().findById('combo').getValue();
		  		store.setBaseParam('msc_id',oseMscs.msc_id);
		  		store.setBaseParam('levellimit',levellimit);
		  	}
		  }
	});


	var addonContentMtree_cm = new Ext.grid.ColumnModel({
		defaults:{},
		columns:[
			addonContentMtree_sm,
			new Ext.grid.RowNumberer({header:'#'}),
		    {id:'id',header: Joomla.JText._("ID"), width: 200, dataIndex: 'id',hidden:true},
	        {header: Joomla.JText._('Mtree_Item'),  dataIndex: 'treename'},
	        {header: Joomla.JText._("Controlled"),  dataIndex: 'controlled'},
	        {header: Joomla.JText._("Type"),  dataIndex: 'type'}
	  	]
  	});

	oseMscAddon.mtree = new Ext.Panel({
		//title: 'Menu',
		listeners:{
			render: function(p)	{
				addonContentMtree_store.load();
			}
		},

		items:[{
			ref:'grid',
			xtype:'grid',
			autoScroll:true,
			height: 400,
			viewConfig:{forceFit: true},

			store: addonContentMtree_store,
			sm: addonContentMtree_sm,
			cm: addonContentMtree_cm,

			bbar: new Ext.PagingToolbar({
	    		pageSize: 20,
	    		store: addonContentMtree_store,
	    		displayInfo: true,
	    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
				emptyMsg: Joomla.JText._("No_topics_to_display")
		    }),

		    tbar:[{
		    	text: Joomla.JText._('Show_to_Members'),
		    	id:'show-to-members',
		    	disabled:true,
		    	handler: function()	{
		    		var ids = oseMscAddon.mtree.grid.getSelectionModel().getSelections();

		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;

		    		}

		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
	    					task:'action',action:'content.mtree.changeStatus','catids[]':catids,
		    				msc_id:oseMsc.msc_id, status: '1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);

		    				if(msg.success)	{
		    					oseMscAddon.mtree.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.mtree.grid.getStore().reload();
		    					oseMscAddon.mtree.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },{
		    	text: Joomla.JText._('Show_to_All'),
		    	id:'show-to-all',
		    	disabled:true,
		    	handler: function()	{
		    		var ids = oseMscAddon.mtree.grid.getSelectionModel().getSelections();

		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}

		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.mtree.changeStatus','catids[]':catids,
		    				msc_id:oseMsc.msc_id, status: '0'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);

		    				if(msg.success)	{
		    					oseMscAddon.mtree.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.mtree.grid.getStore().reload();
		    					oseMscAddon.mtree.grid.getView().refresh();
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
		    		var ids = oseMscAddon.mtree.grid.getSelectionModel().getSelections();
		    		
		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.mtree.changeStatus','catids[]':catids,
		    				msc_id:oseMsc.msc_id, status: '-1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);

		    				if(msg.success)	{
		    					oseMscAddon.mtree.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.mtree.grid.getStore().reload();
		    					oseMscAddon.mtree.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },'->',{
		    	text: Joomla.JText._('Max_Levels')
		    },{
	        	xtype: 'combo',
	        	width: 100,
	        	id:'combo',
	        	hiddenName: 'levellimit',
	        	typeAhead: true,
			    triggerAction: 'all',
			    lazyRender:true,
			    mode: 'local',
			    store: new Ext.data.ArrayStore({
			        id: 0,
			        fields: [
			            'levellimit'
			        ],
			        data: [
			        	['1'],
			        	['2'],
	                    ['3'],
			        	['4'],
			        	['5'],
			        	['6'],
			        	['7'],
			        	['8'],
			        	['9'],
			        	['10'],
			        	['11'],
			        	['12'],
			        	['13'],
			        	['14'],
			        	['15'],
			        	['16'],
			        	['17'],
			        	['18'],
			        	['19'],
			        	['20']

			        ]
			    }),
			    valueField: 'levellimit',
			    displayField: 'levellimit',

			    listeners: {
			        // delete the previous query in the beforequery event or set
			        // combo.lastQuery = null (this will reload the store the next time it expands)
			        beforequery: function(qe){
			            delete qe.combo.lastQuery;
			        },
	    			afterrender: function(e)	{
	    				e.setValue('10');
	    			},
		    		select: function(c,r,i)	{

		    			oseMscAddon.mtree.grid.getStore().reload({
		    				params:{levellimit:r.data.levellimit,msc_id:oseMscs.msc_id}
		    			});
		    		}
		        }
		    },'-',
				new Ext.ux.form.SearchField({
	                store: addonContentMtree_store,
	                paramName: 'search',
	                width:150
	            })
			]
		}]
	});