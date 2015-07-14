Ext.ns('oseMscAddon','oseMscAddon.contentZooParams');
	oseMscAddon.msg = new Ext.App();
	
	oseMscAddon.contentZooParams.viewArticle = function(catId)	{
		if(!articleWin)	{
			var articleWinStore = new Ext.data.Store({
				proxy: new Ext.data.HttpProxy({
			    	url: 'index.php?option=com_osemsc&controller=content',
		            method: 'POST'
		      	}),
			  	baseParams:{task: "action",action:'content.zoocat.getArtList'}, 
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
			  	listeners: {
				  	beforeload: function(store,records,options)	{
				  		store.setBaseParam('msc_id',oseMscs.msc_id);
				  		store.setBaseParam('cat_id',catId);
				  	}
			  	}
			})
			
			var articleWinGridSm = new Ext.grid.CheckboxSelectionModel({
				listeners:{
					selectionchange: function(sm,node){
						//addonMemberLicuser.getTopToolbar().editBtn.setDisabled(sm.getCount() != 1); // >
						//addonMemberLicuser.getTopToolbar().removeBtn.setDisabled(sm.getCount() != 1); // >
					}
				}
			})
			var articleWin = new Ext.Window({
				width: 900
				,modal: true
				,items:[{
					xtype: 'grid'
					,ref: 'grid'
					,height: 500
					,store: articleWinStore
					,autoExpandColumn: 'content-item'
					,listeners: {
						render: function(g)	{
							articleWinStore.reload();
						}
					}
					,cm: new Ext.grid.ColumnModel({
						defaults:{},
						columns:[ 
							articleWinGridSm
							,new Ext.grid.RowNumberer({header:'#'})
						    ,{id:'id',header: Joomla.JText._("ID"), width: 20, dataIndex: 'id',hidden:true}
					        ,{id:'content-item',header: Joomla.JText._('Content_Item'),  dataIndex: 'treename'}
					        ,{header: Joomla.JText._('Controlled'),  dataIndex: 'controlled', width: 200}
					  	]
				  	})
				  	,sm: articleWinGridSm
					,bbar: new Ext.PagingToolbar({
			    		pageSize: 20
			    		,store: articleWinStore
			    		,plugins: new Ext.ux.grid.limit({})
			    		,displayInfo: true
			    		,displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}'
						,emptyMsg: Joomla.JText._("No_topics_to_display")
				    })
				    
				    ,tbar:[{
				    	text:  Joomla.JText._('Show_to_Members')
				    	,handler: function()	{
				    		var ids = articleWin.grid.getSelectionModel().getSelections();
				    		
				    		var menu_ids = new Array();
				    		for(i=0;i < ids.length; i++)	{
				    			var r = ids[i];
				    			menu_ids[i] = r.id;
				    		}
				    		
				    		Ext.Ajax.request({
				    			url:'index.php?option=com_osemsc&controller=content',
				    			params:{
				    				task:'action',action:'content.zoocat.changeStatus','jc_ids[]':menu_ids,
				    				msc_id:oseMsc.msc_id, status: '1', content_type: 'art'
				    			},
				    			success: function(response,opt)	{
				    				var msg = Ext.decode(response.responseText);
				    				oseMscAddon.msg.setAlert(msg.title,msg.content);
				    				
				    				if(msg.success)	{
				    					articleWin.grid.getSelectionModel().clearSelections();
				    					articleWin.grid.getStore().reload();
				    					articleWin.grid.getView().refresh();
				    				}
				    			}
				    		});
				    	}
				    },{
				    	text: Joomla.JText._('Show_to_All'),
				    	handler: function()	{
				    		var ids = articleWin.grid.getSelectionModel().getSelections();
				    		
				    		var menu_ids = new Array();
				    		for(i=0;i < ids.length; i++)	{
				    			var r = ids[i];
				    			menu_ids[i] = r.id;
				    		}
				    		
				    		Ext.Ajax.request({
				    			url:'index.php?option=com_osemsc&controller=content',
				    			params:{
				    				task:'action',action:'content.zoocat.changeStatus','jc_ids[]':menu_ids,
				    				msc_id:oseMsc.msc_id, status: '0', content_type: 'art'
				    			},
				    			success: function(response,opt)	{
				    				var msg = Ext.decode(response.responseText);
				    				oseMscAddon.msg.setAlert(msg.title,msg.content);
				    				
				    				if(msg.success)	{
				    					articleWin.grid.getSelectionModel().clearSelections();
				    					articleWin.grid.getStore().reload();
				    					articleWin.grid.getView().refresh();
				    				}
				    			}
				    		});
				    	}
				    },{
				    	text: Joomla.JText._('Hide_to_Members'),
				    	hidden: true,
				    	handler: function()	{
				    		var ids = articleWin.grid.getSelectionModel().getSelections();
				    		
				    		var menu_ids = new Array();
				    		for(i=0;i < ids.length; i++)	{
				    			var r = ids[i];
				    			menu_ids[i] = r.id;
				    		}
				    		
				    		Ext.Ajax.request({
				    			url:'index.php?option=com_osemsc&controller=content',
				    			params:{
				    				task:'action',action:'content.zoocat.changeStatus','jc_ids[]':menu_ids,
				    				msc_id:oseMsc.msc_id,status: '-1', content_type: 'art'
				    			},
				    			success: function(response,opt)	{
				    				var msg = Ext.decode(response.responseText);
				    				oseMscAddon.msg.setAlert(msg.title,msg.content);
				    				
				    				if(msg.success)	{
				    					articleWin.grid.getSelectionModel().clearSelections();
				    					articleWin.grid.getStore().reload();
				    					articleWin.grid.getView().refresh();
				    				}
				    			}
				    		});
				    	}
				    },'->',
						new Ext.ux.form.SearchField({
			                store: oseMscAddon.contentZooParams.store,
			                paramName: 'search',
			                width:150
			            })
					]
				}]
			})
		}
		
		articleWin.show().alignTo(Ext.getBody(),'t-t')
	}
	
	var addonContentZoocat_sm = new Ext.grid.CheckboxSelectionModel({
		//singleSelect:true,
		listeners:{
			selectionchange: function(sm,node){
				oseMscAddon.zoocat.grid.getTopToolbar().findById('show-to-members').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.zoocat.grid.getTopToolbar().findById('show-to-all').setDisabled(sm.getCount() < 1); // >
				oseMscAddon.zoocat.grid.getTopToolbar().findById('hide-to-members').setDisabled(sm.getCount() < 1); // >
			}
		}
	});
	
	var addonContentZoocat_store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=content',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:'content.zoocat.getList',msc_id:''}, 
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
			  	//var appid = oseMscAddon.zoocat.grid.getTopToolbar().findById('appCombo').getValue();
			  	//var limit = oseMscAddon.zoocat.grid.getTopToolbar().findById('combo').getValue();
			 	store.setBaseParam('msc_id',oseMscs.msc_id);
			 	//store.setBaseParam('appid',appid);
			 	//store.setBaseParam('levellimit',limit);
			 				  	
		  	}
		  }
	});
	
	
	var addonContentZoocat_cm = new Ext.grid.ColumnModel({
		defaults:{},
		columns:[ 
			addonContentZoocat_sm,
			new Ext.grid.RowNumberer({header:'#'}),
		    {id:'id',header: Joomla.JText._("ID"), width: 200, dataIndex: 'id',hidden:true},
	        {header: Joomla.JText._('Zoo_Category'),  dataIndex: 'treename'},
	        {header: Joomla.JText._("Controlled"),  dataIndex: 'controlled'},
	        {header: Joomla.JText._("Type"),  dataIndex: 'type'},
	        {
	        	id:'action',header: Joomla.JText._('Control_Article_s'), width: 100
	        	,xtype: 'actioncolumn'
                ,align: 'center'
                ,items: [{
                    getClass: function(v, meta, rec)	{
                        return 'article-col';
                	}
                    ,tooltip: Joomla.JText._('View_Articles')
                    ,handler: function(grid, rowIndex, colIndex) {
                    	grid.getSelectionModel().selectRow(rowIndex)
                    	var r = grid.getSelectionModel().getSelected();
                    	//alert(r.data.id);
                    	oseMscAddon.contentZooParams.viewArticle(r.data.id);
                    }
                }]
	        }
	  	]
  	});
	
	oseMscAddon.zoocat = new Ext.Panel({
		//title: 'Menu',
		items:[{
			ref:'grid',
			xtype:'grid',
			autoScroll:true,
			height: 400,
			viewConfig:{forceFit: true},
			
			store: addonContentZoocat_store,
			sm: addonContentZoocat_sm,
			cm: addonContentZoocat_cm,
			
			bbar: new Ext.PagingToolbar({
	    		pageSize: 20,
	    		store: addonContentZoocat_store,
	    		displayInfo: true,
	    		plugins: new Ext.ux.grid.limit({}),
	    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
				emptyMsg: Joomla.JText._("No_topics_to_display")
		    }),
		    
		    tbar:[{
		    	text: Joomla.JText._('Show_to_Members'),
		    	id:'show-to-members',
		    	disabled:true,
		    	handler: function()	{
		    		var ids = oseMscAddon.zoocat.grid.getSelectionModel().getSelections();
		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
	    					task:'action',action:'content.zoocat.changeStatus','jc_ids[]':catids,
		    				msc_id:oseMsc.msc_id, status: '1',content_type: 'cat'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.zoocat.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.zoocat.grid.getStore().reload();
		    					oseMscAddon.zoocat.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },{
		    	text: Joomla.JText._('Show_to_All'),
		    	id:'show-to-all',
		    	disabled:true,
		    	handler: function()	{
		    		var ids = oseMscAddon.zoocat.grid.getSelectionModel().getSelections();
		    		
		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.zoocat.changeStatus','jc_ids[]':catids,
		    				msc_id:oseMsc.msc_id, status: '0',content_type: 'cat'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.zoocat.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.zoocat.grid.getStore().reload();
		    					oseMscAddon.zoocat.grid.getView().refresh();
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
		    		var ids = oseMscAddon.zoocat.grid.getSelectionModel().getSelections();
		    		
		    		var catids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			catids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.zoocat.changeStatus','catids[]':catids,
		    				msc_id:oseMsc.msc_id,status: '-1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.zoocat.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.zoocat.grid.getStore().reload();
		    					oseMscAddon.zoocat.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },'->',{
		    	text: Joomla.JText._('Applications')
		    },{
		    	xtype: 'combo'
		    	,hiddenName: 'appid'
		    	,id: 'appCombo'
			    ,typeAhead: true
			    ,width: 150
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,lastQuery: ''
		    	,store:new Ext.data.Store({
					proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=content'
	            		,method: 'POST'
			      	})
					,baseParams:{task: "action",action:'content.zoocat.getApps'}
					,reader: new Ext.data.JsonReader({   
						root: 'results',
						totalProperty: 'total'
					},[ 
						{name: 'id', type: 'string', mapping: 'id'}
						,{name: 'name', type: 'string', mapping: 'name'}
					])
					,sortInfo:{field: 'id', direction: "ASC"}
					,autoLoad: {}
					,listeners: {
			    		load: function(s,r,i)	{
			    			var comboMscId= oseMscAddon.zoocat.grid.getTopToolbar().findById('appCombo');
				  			
				    		comboMscId.setValue(r[0].data.id);
	
				    		comboMscId.fireEvent('select',comboMscId,r[0],0)
				    	}
			    	}
				})

			    ,valueField: 'id'
			    ,displayField: 'name'
			    
			    ,listeners: {
		    		select: function(c,r,i)	{
		    			//var limit = oseMscAddon.zoocat.grid.getTopToolbar().findById('combo').getValue();
				    	/*oseMscAddon.zoocat.grid.getStore().reload({
		    				params:{appid:r.data.id,msc_id:oseMscs.msc_id,levellimit:limit}
		    			});*/
		    			oseMscAddon.zoocat.grid.getStore().setBaseParam('appid',r.get('id'));
		    			oseMscAddon.zoocat.grid.getBottomToolbar().doRefresh();
		    		}
		        }
		    },'-',{
		    	text: Joomla.JText._('Max_Levels')
		    },{
	        	xtype: 'combo',
	        	width: 60,
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
	    			render: function(e)	{
	    				e.setValue('10');
	    			},
	    			select: function(c,r,i)	{
	    				/*var appid = oseMscAddon.zoocat.grid.getTopToolbar().findById('appCombo').getValue();
		    			oseMscAddon.zoocat.grid.getStore().reload({
		    				params:{levellimit:r.data.levellimit,msc_id:oseMscs.msc_id,appid:appid}
		    			});*/
		    			oseMscAddon.zoocat.grid.getStore().setBaseParam('levellimit',r.get('levellimit'));
		    			oseMscAddon.zoocat.grid.getBottomToolbar().doRefresh();
		    		}
		        }
		    },'-',
				new Ext.ux.form.SearchField({
	                store: addonContentZoocat_store,
	                paramName: 'search',
	                width:150
	            })
			]
		}]
	});