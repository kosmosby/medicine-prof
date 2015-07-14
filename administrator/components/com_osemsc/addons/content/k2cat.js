Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();
	
	var addonContentK2cat_sm = new Ext.grid.CheckboxSelectionModel({
		//singleSelect:true,
		listeners:{
			selectionchange: function(sm,node){
				oseMscAddon.k2cat.grid.getTopToolbar().findById('show-to-members').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.k2cat.grid.getTopToolbar().findById('show-to-all').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.k2cat.grid.getTopToolbar().findById('hide-to-members').setDisabled(sm.getCount() < 1); // >
			}
		}
	});
	
	var addonContentK2cat_store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=content',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'content.k2cat.getList',msc_id:''}, 
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
			  	var levellimit = oseMscAddon.k2cat.grid.getTopToolbar().findById('combo').getValue();
		  		store.setBaseParam('msc_id',oseMscs.msc_id);
		  		store.setBaseParam('levellimit',levellimit);
			  	
		  	}
		  }
	});
	
	
	var addonContentK2cat_cm = new Ext.grid.ColumnModel({
		defaults:{},
		columns:[ 
			addonContentK2cat_sm,
			new Ext.grid.RowNumberer({header:'#'}),
		    {id:'id',header: Joomla.JText._("ID"), width: 200, dataIndex: 'id',hidden:true},
	        {header: Joomla.JText._('K2_Category'),  dataIndex: 'treename'},
	        {header: Joomla.JText._("Controlled"),  dataIndex: 'controlled'},
	        {header: Joomla.JText._("Type"),  dataIndex: 'type'}
	  	]
  	});
	
	oseMscAddon.k2cat = new Ext.Panel({
		//title: 'Menu',
		listeners:{
			render: function(p)	{
				addonContentK2cat_store.load();
			}
		},
		
		items:[{
			ref:'grid',
			xtype:'grid',
			autoScroll:true,
			height: 400,
			viewConfig:{forceFit: true},
			
			store: addonContentK2cat_store,
			sm: addonContentK2cat_sm,
			cm: addonContentK2cat_cm,
			
			bbar: new Ext.PagingToolbar({
	    		pageSize: 20,
	    		store: addonContentK2cat_store,
	    		displayInfo: true,
	    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
				emptyMsg: Joomla.JText._("No_topics_to_display")
		    }),
		    
		    tbar:[{
		    	text: Joomla.JText._('Show_to_Members'),
		    	id:'show-to-members',
		    	disabled:true,
		    	handler: function()	{
		    		var ids = oseMscAddon.k2cat.grid.getSelectionModel().getSelections();
		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
	    					task:'action',action:'content.k2cat.changeStatus','catids[]':catids,
		    				msc_id:oseMsc.msc_id, status: '1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.k2cat.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.k2cat.grid.getStore().reload();
		    					oseMscAddon.k2cat.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },{
		    	text: Joomla.JText._('Show_to_All'),
		    	id:'show-to-all',
		    	disabled:true,
		    	handler: function()	{
		    		var ids = oseMscAddon.k2cat.grid.getSelectionModel().getSelections();
		    		
		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.k2cat.changeStatus','catids[]':catids,
		    				msc_id:oseMsc.msc_id, status: '0'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.k2cat.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.k2cat.grid.getStore().reload();
		    					oseMscAddon.K2cat.grid.getView().refresh();
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
		    		var ids = oseMscAddon.k2cat.grid.getSelectionModel().getSelections();
		    		
		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.k2cat.changeStatus','catids[]':catids,
		    				msc_id:oseMsc.msc_id,status: '-1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.k2cat.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.k2cat.grid.getStore().reload();
		    					oseMscAddon.k2cat.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },'->',{
		    	text: Joomla.JText._('Max_Levels')
		    },{
	        	xtype: 'combo',
	        	width: 200,
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
	    				
		    			oseMscAddon.k2cat.grid.getStore().reload({
		    				params:{levellimit:r.data.levellimit,msc_id:oseMscs.msc_id}
		    			});
		    		}
		        }
		    },'-',
				new Ext.ux.form.SearchField({
	                store: addonContentK2cat_store,
	                paramName: 'search',
	                width:150
	            })
			]
		}]
	});