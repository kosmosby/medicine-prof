Ext.ns('oseMscAddon','oseMscAddon.contentK2Params');
	oseMscAddon.msg = new Ext.App();
	
	var addonContentK2item_sm = new Ext.grid.CheckboxSelectionModel({
		listeners:{
			selectionchange: function(sm,node){
				oseMscAddon.k2item.grid.getTopToolbar().findById('show-to-members').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.k2item.grid.getTopToolbar().findById('show-to-all').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.k2item.grid.getTopToolbar().findById('hide-to-members').setDisabled(sm.getCount() < 1); // >
			}
		}
	});
	
	var addonContentK2item_action = function(newStatus)	{
		var ids = oseMscAddon.k2item.grid.getSelectionModel().getSelections();
		    		
		var item_ids = new Array();
		for(i=0;i < ids.length; i++)	{
			var r = ids[i];
			item_ids[i] = r.id;
		}
		
		Ext.Ajax.request({
			url:'index.php?option=com_osemsc&controller=content',
			params:{
				task:'action',action:'content.k2item.changeStatus','item_ids[]':item_ids,
				msc_id:oseMsc.msc_id, status: newStatus
			},
			success: function(response,opt)	{
				var msg = Ext.decode(response.responseText);
				oseMscAddon.msg.setAlert(msg.title,msg.content);
				
				if(msg.success)	{
					oseMscAddon.k2item.grid.getSelectionModel().clearSelections();
					oseMscAddon.k2item.grid.getStore().reload();
					oseMscAddon.k2item.grid.getView().refresh();
				}
			}
		});
	}
	
	
	var addonContentK2item_store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=content',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'content.k2item.getItems',msc_id:''}, 
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

		  listeners: {
		  	beforeload: function(store,records,options)	{
		  		store.setBaseParam('msc_id',oseMscs.msc_id);
		  		store.setBaseParam('catid',oseMscAddon.contentK2Params.catid);
		  	}
		  }
	});
	

	
	var addonContentK2item_cm = new Ext.grid.ColumnModel({
		defaults:{},
		columns:[ 
			addonContentK2item_sm,
			new Ext.grid.RowNumberer({header:'#'}),
		    {id:'id',header: Joomla.JText._("ID"), width: 200, dataIndex: 'id',hidden:true},
	        {header: Joomla.JText._("Title"),  dataIndex: 'title'},
	        {	
	        	header: Joomla.JText._("Controlled"),  dataIndex: 'controlled'
	        },
	        {header: Joomla.JText._("Type"), dataIndex: 'type'}
	  	]
  	});
	
	oseMscAddon.k2item = new Ext.Panel({
		//title: 'Menu',
		listeners:{
			render: function(p)	{
				//addonContentK2item_store.load();
			}
		},
		
		items:[{
			ref:'grid',
			xtype:'grid',
			autoScroll:true,
			height: 500,
			viewConfig:{forceFit: true},
			store: addonContentK2item_store,
			sm: addonContentK2item_sm,
			cm: addonContentK2item_cm,
			
			bbar: new Ext.PagingToolbar({
	    		pageSize: 20,
	    		store: addonContentK2item_store,
	    		displayInfo: true,
	    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
				emptyMsg: Joomla.JText._("No_topics_to_display")
		    }),
		    
		    tbar:[{
		    	id:'show-to-members',
		    	disabled:true,
		    	text: Joomla.JText._('Show_to_Members'),
		    	handler: function()	{
		    		addonContentK2item_action(1)
		    	}
		    },{
		    	id:'show-to-all',
		    	disabled:true,
		    	text: Joomla.JText._('Show_to_All'),
		    	handler: function()	{
		    		addonContentK2item_action(0)
		    	}
		    },{
		    	id:'hide-to-members',
		    	disabled:true,
		    	hidden: true,
		    	text: Joomla.JText._('Hide_to_Members'),
		    	handler: function()	{
		    		addonContentK2item_action('-1')
		    	}
		    },'->',{
		    	text: Joomla.JText._('Categories')
		    },{
		    	xtype: 'combo'
		    	,hiddenName: 'catid'
		    	,id: 'k2ItemCatCombo'
			    ,typeAhead: true
			    ,width: 360
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'local'
		    	,store:new Ext.data.Store({
					proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=content'
	            		,method: 'POST'
			      	})
					,baseParams:{task: "action",action:'content.k2item.getCats'}
					,reader: new Ext.data.JsonReader({   
						root: 'results',
						totalProperty: 'total'
					},[ 
						{name: 'id', type: 'string', mapping: 'id'}
						,{name: 'name', type: 'string', mapping: 'name'}
					])
					,sortInfo:{field: 'name', direction: "ASC"}
					,autoLoad: {}
					,listeners: {
			    		load: function(s,r,i)	{
			    			var comboMscId= oseMscAddon.k2item.grid.getTopToolbar().findById('k2ItemCatCombo');
				  			
				    		comboMscId.setValue(r[0].data.id);
	
				    		comboMscId.fireEvent('select',comboMscId,r[0],0)
				    	}
			    	}
			    	,autoLoad:{}
				})

			    ,valueField: 'id'
			    ,displayField: 'name'
			    
			    ,listeners: {
		    		select: function(c,r,i)	{
		    			oseMscAddon.contentK2Params.catid = r.data.id;
		    			oseMscAddon.k2item.grid.getStore().reload();
		    		}
		        }
		    },'-',
				new Ext.ux.form.SearchField({
	                store: addonContentK2item_store,
	                paramName: 'search',
	                width:150
	            })
			]
		}]
	});