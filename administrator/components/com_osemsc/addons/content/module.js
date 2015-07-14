Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();
	
	var addonContentModule_sm = new Ext.grid.CheckboxSelectionModel({
		//singleSelect:true,
		listeners:{
			selectionchange: function(sm,node){
				//oseMscAddon.module.grid.getTopToolbar().items[0].findById('show-to-members').setDisabled(sm.getCount() < 1); // >
				//oseMscAddon.module.grid.getTopToolbar().items[0].findById('show-to-all').setDisabled(sm.getCount() < 1); // >
				//oseMscAddon.module.grid.getTopToolbar().items[0].findById('hide-to-members').setDisabled(sm.getCount() < 1); // >
			}
		}
	});
	var addonContentModule_store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=content',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'content.module.getList',msc_id:''}, 
		  reader: new Ext.data.JsonReader({   
		            
		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'id'
		  },[ 
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'title', type: 'string', mapping: 'title'},
		    {name: 'controlled', type: 'string', mapping: 'controlled'},
		    {name: 'module', type: 'string', mapping: 'module'},
		    {name: 'position', type: 'string', mapping: 'position'},
		    {name: 'time_length', type: 'string', mapping: 'time_length'},
		    {name: 'time_unit', type: 'string', mapping: 'time_unit'}
		  ]),
		  //sortInfo:{field: 'user_id', direction: "ASC"},
		  listeners: {
		  	beforeload: function(store,records,options)	{
		  		store.setBaseParam('msc_id',oseMscs.msc_id);
		  	}
		  }
	});
	
	
	
	
	var addonContentModule_cm = new Ext.grid.ColumnModel({
		defaults:{},
		columns:[ 
			addonContentModule_sm,
			new Ext.grid.RowNumberer({header:'#'}),
		    {id:'id',header: Joomla.JText._("ID"), width: 200, dataIndex: 'id',hidden:true},
	        {header: Joomla.JText._('Module_Item'),  dataIndex: 'title'},
	        {header: Joomla.JText._("Controlled"),  dataIndex: 'controlled'},
	        {
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
	        {header: Joomla.JText._('Module_Type'), dataIndex: 'module'},
	        {header: Joomla.JText._('Position'), dataIndex: 'position'}
	  	]
  	});
	
	oseMscAddon.module = new Ext.Panel({
		//title: 'Module',
		listeners:{
			render: function(p)	{
				addonContentModule_store.load();
			}
		}
		,items:[{
			ref:'grid',
			xtype:'editorgrid',
			autoScroll:true,
			height: 500,
			viewConfig:{forceFit: true},
			//autoHeight: true,
			
			store: addonContentModule_store,
			sm: addonContentModule_sm,
			cm: addonContentModule_cm,
			
			bbar: new Ext.PagingToolbar({
	    		pageSize: 20,
	    		store: addonContentModule_store,
	    		displayInfo: true,
	    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
				emptyMsg: Joomla.JText._("No_topics_to_display")
		    }),
		    
		    tbar:[{
		    	text: Joomla.JText._('Action')
		    	,menu:[{
		    		text:Joomla.JText._('Show_to_Members')
		    		,id:'show-to-members'
		    		//,disabled:true
		    		,xtype: 'button'
		    		,handler: function()	{
			    		var ids = oseMscAddon.module.grid.getSelectionModel().getSelections();
			    		
			    		var module_ids = new Array();
			    		var time_length = new Array();
			    		var time_unit = new Array();
			    		for(i=0;i < ids.length; i++)	{
			    			var r = ids[i];
			    			module_ids[i] = r.id;
			    			time_length[i] = r.get('time_length');
			    			time_unit[i] = r.get('time_unit');
			    		}
			    		
			    		Ext.Ajax.request({
			    			url:'index.php?option=com_osemsc&controller=content',
			    			params:{
			    				task:'action',action:'content.module.changeStatus','module_ids[]':module_ids,
			    				msc_id:oseMsc.msc_id,status: '1','time_length[]':time_length
			    				,'time_unit[]':time_unit
			    			},
			    			success: function(response,opt)	{
			    				var msg = Ext.decode(response.responseText);
			    				oseMscAddon.msg.setAlert(msg.title,msg.content);
			    				
			    				if(msg.success)	{
			    					oseMscAddon.module.grid.getSelectionModel().clearSelections();
			    					oseMscAddon.module.grid.getStore().reload();
			    					oseMscAddon.module.grid.getView().refresh();
			    				}
			    			}
			    		});
		    		}
		    	},{
		    		text:Joomla.JText._('Show_to_All')
		    		,id:'show-to-all'
		    		//,disabled:true
		    		,xtype: 'button'
		    		,handler: function()	{
			    		var ids = oseMscAddon.module.grid.getSelectionModel().getSelections();
			    		
			    		var module_ids = new Array();
			    		var time_length = new Array();
			    		var time_unit = new Array();
			    		for(i=0;i < ids.length; i++)	{
			    			var r = ids[i];
			    			module_ids[i] = r.id;
			    			time_length[i] = r.get('time_length');
			    			time_unit[i] = r.get('time_unit');
			    		}
			    		
			    		Ext.Ajax.request({
			    			url:'index.php?option=com_osemsc&controller=content',
			    			params:{
			    				task:'action',action:'content.module.changeStatus','module_ids[]':module_ids,
			    				msc_id:oseMsc.msc_id,status: '0','time_length[]':time_length
			    				,'time_unit[]':time_unit
			    			},
			    			success: function(response,opt)	{
			    				var msg = Ext.decode(response.responseText);
			    				oseMscAddon.msg.setAlert(msg.title,msg.content);
			    				
			    				if(msg.success)	{
			    					oseMscAddon.module.grid.getSelectionModel().clearSelections();
			    					oseMscAddon.module.grid.getStore().reload();
			    					oseMscAddon.module.grid.getView().refresh();
			    				}
			    			}
			    		});
		    		}
		    	},{
		    		text:Joomla.JText._('Hide_to_Members')
		    		,id:'hide-to-members'
		    		//,disabled:true
		    		,xtype: 'button'
		    		,handler: function()	{
			    		var ids = oseMscAddon.module.grid.getSelectionModel().getSelections();
			    		
			    		var module_ids = new Array();
			    		for(i=0;i < ids.length; i++)	{
			    			var r = ids[i];
			    			module_ids[i] = r.id;
			    		}
			    		
			    		Ext.Ajax.request({
			    			url:'index.php?option=com_osemsc&controller=content',
			    			params:{
			    				task:'action',action:'content.module.changeStatus','module_ids[]':module_ids,
			    				msc_id:oseMsc.msc_id,status: '-1'
			    			},
			    			success: function(response,opt)	{
			    				var msg = Ext.decode(response.responseText);
			    				oseMscAddon.msg.setAlert(msg.title,msg.content);
			    				
			    				if(msg.success)	{
			    					oseMscAddon.module.grid.getSelectionModel().clearSelections();
			    					oseMscAddon.module.grid.getStore().reload();
			    					oseMscAddon.module.grid.getView().refresh();
			    				}
			    			}
			    		})
			    	}
		    	}]
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
					baseParams:{task: "action",action:'content.module.getFilterTypes',filter_type:'assigned'}, 
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
		    			var t = oseMscAddon.module.grid.getTopToolbar();
		    			oseMscAddon.module.grid.getStore().reload({
		    				params:{
		    					position:t.position.getValue(),
		    					moduletype:t.moduletype.getValue(),
		    					assigned:t.assigned.getValue(),
		    					msc_id:oseMscs.msc_id
		    				}
		    			});
		    		}
		        }
		    },'-',{
		    	text: Joomla.JText._('Module_Type')
		    },{
		    	xtype: 'combo',
		    	hiddenName: 'moduletype',
		    	ref: 'moduletype',
	            width: 150,
			    typeAhead: true,
			    triggerAction: 'all',
			    lazyRender:false,
			    mode: 'remote',
		    	store:new Ext.data.Store({
					proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=content',
	            		method: 'POST'
			      	}),
					baseParams:{task: "action",action:'content.module.getFilterTypes',filter_type:'type'}, 
					reader: new Ext.data.JsonReader({   
						root: 'results',
						totalProperty: 'total'
					},[ 
						{name: 'value', type: 'string', mapping: 'value'},
						{name: 'text', type: 'string', mapping: 'text'},
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
		    			var t = oseMscAddon.module.grid.getTopToolbar();
		    			oseMscAddon.module.grid.getStore().reload({
		    				params:{
		    					position:t.position.getValue(),
		    					moduletype:t.moduletype.getValue(),
		    					assigned:t.assigned.getValue(),
		    					msc_id:oseMscs.msc_id
		    				}
		    			});
		    		}
		        }
		    },'-',{
		    	text: Joomla.JText._('Position')
		    },{
		    	xtype: 'combo',
		    	hiddenName: 'position',
		    	ref: 'position',
	            width: 120,
			    typeAhead: true,
			    triggerAction: 'all',
			    lazyRender:false,
			    mode: 'remote',
		    	store:new Ext.data.Store({
					proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=content',
	            		method: 'POST'
			      	}),
					baseParams:{task: "action",action:'content.module.getFilterTypes',filter_type:'position'}, 
					reader: new Ext.data.JsonReader({   
						root: 'results',
						totalProperty: 'total'
					},[ 
						{name: 'value', type: 'string', mapping: 'value'},
						{name: 'text', type: 'string', mapping: 'text'},
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
		    			var t = oseMscAddon.module.grid.getTopToolbar();
		    			oseMscAddon.module.grid.getStore().reload({
		    				params:{
		    					position:t.position.getValue(),
		    					moduletype:t.moduletype.getValue(),
		    					assigned:t.assigned.getValue(),
		    					msc_id:oseMscs.msc_id
		    				}
		    			});
		    		}
		        }
		    },'-',
				new Ext.ux.form.SearchField({
	                store: addonContentModule_store,
	                paramName: 'search',
	                width:150
	            })
			]
		}]
	});