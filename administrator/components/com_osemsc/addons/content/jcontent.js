Ext.ns('oseMscAddon','oseMscAddon.jcontentParams');

	oseMscAddon.msg = new Ext.App();
	
	oseMscAddon.jcontentParams.viewArticle = function(catId)	{
		if(!articleWin)	{
			var articleWinStore = new Ext.data.Store({
				proxy: new Ext.data.HttpProxy({
			    	url: 'index.php?option=com_osemsc&controller=content',
		            method: 'POST'
		      	}),
			  	baseParams:{task: "action",action:'content.jcontent.getArtList'}, 
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
				    	text: Joomla.JText._('Show_to_Members')
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
				    				task:'action',action:'content.jcontent.changeStatus','jc_ids[]':menu_ids,
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
				    				task:'action',action:'content.jcontent.changeStatus','jc_ids[]':menu_ids,
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
				    				task:'action',action:'content.jcontent.changeStatus','jc_ids[]':menu_ids,
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
			                store: oseMscAddon.jcontentParams.store,
			                paramName: 'search',
			                width:150
			            })
					]
				}]
			})
		}
		
		articleWin.show().alignTo(Ext.getBody(),'t-t')
	}
	
	oseMscAddon.jcontentParams.store = new Ext.data.Store({
  		proxy: new Ext.data.HttpProxy({
            url: 'index.php?option=com_osemsc&controller=content',
            method: 'POST'
      	}),
	  	baseParams:{task: "action",action:'content.jcontent.getCatList',msc_id:''}, 
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
		  	}
	  	}
	});
	
	oseMscAddon.jcontentParams.sm = new Ext.grid.CheckboxSelectionModel({
		listeners:{
			selectionchange: function(sm,node){
				//addonMemberLicuser.getTopToolbar().editBtn.setDisabled(sm.getCount() != 1); // >
				//addonMemberLicuser.getTopToolbar().removeBtn.setDisabled(sm.getCount() != 1); // >
			}
		}
	})

	oseMscAddon.jcontentParams.cm = new Ext.grid.ColumnModel({
		defaults:{},
		columns:[ 
			oseMscAddon.jcontentParams.sm
			,new Ext.grid.RowNumberer({header:'#'})
		    ,{id:'id',header: Joomla.JText._("ID"), width: 20, dataIndex: 'id',hidden:true}
	        ,{id:'content-item',header: Joomla.JText._("Content Item"),  dataIndex: 'treename'}
	        ,{	
	        	header: Joomla.JText._("Controlled"),  dataIndex: 'controlled', width: 200
	        }
	        ,{
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
                    	oseMscAddon.jcontentParams.viewArticle(r.data.id);
                    }
                }]
	        }
	  	]
  	});
	
	
	oseMscAddon.jcontent = new Ext.Panel({
		items:[{
			ref:'grid'
			,xtype:'grid'
			,autoScroll:true
			,height: 500
			//,viewConfig:{forceFit: true}
			,autoExpandColumn: 'content-item'
			,store: oseMscAddon.jcontentParams.store
			,sm: oseMscAddon.jcontentParams.sm
			,cm: oseMscAddon.jcontentParams.cm
			,listeners:{
				render: function(p)	{
					oseMscAddon.jcontentParams.store.load();
				}
			}
			,bbar: new Ext.PagingToolbar({
	    		pageSize: 20
	    		,store: oseMscAddon.jcontentParams.store
	    		,plugins: new Ext.ux.grid.limit({})
	    		,displayInfo: true
	    		,displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}'
				,emptyMsg: Joomla.JText._("No_topics_to_display")
		    })
		    
		    ,tbar:[{
		    	text: Joomla.JText._('Show_to_Members')
		    	,handler: function()	{
		    		var ids = oseMscAddon.jcontent.grid.getSelectionModel().getSelections();
		    		
		    		var menu_ids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			menu_ids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.jcontent.changeStatus','jc_ids[]':menu_ids,
		    				msc_id:oseMsc.msc_id, status: '1', content_type: 'cat'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.jcontent.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.jcontent.grid.getStore().reload();
		    					oseMscAddon.jcontent.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },{
		    	text: Joomla.JText._('Show_to_All'),
		    	handler: function()	{
		    		var ids = oseMscAddon.jcontent.grid.getSelectionModel().getSelections();
		    		
		    		var menu_ids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			menu_ids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.jcontent.changeStatus','jc_ids[]':menu_ids,
		    				msc_id:oseMsc.msc_id, status: '0', content_type: 'cat'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.jcontent.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.jcontent.grid.getStore().reload();
		    					oseMscAddon.jcontent.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },{
		    	text: Joomla.JText._('Hide_to_Members'),
		    	hidden: true,
		    	handler: function()	{
		    		var ids = oseMscAddon.jcontent.grid.getSelectionModel().getSelections();
		    		
		    		var menu_ids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			menu_ids[i] = r.id;
		    		}
		    		
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.jcontent.changeStatus','jc_ids[]':menu_ids,
		    				msc_id:oseMsc.msc_id,status: '-1', content_type: 'cat'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.jcontent.grid.getSelectionModel().clearSelections();
		    					oseMscAddon.jcontent.grid.getStore().reload();
		    					oseMscAddon.jcontent.grid.getView().refresh();
		    				}
		    			}
		    		});
		    	}
		    },'->',{
		    	text: Joomla.JText._('Menu_Type')
		    	,hidden: true
		    },{
		    	xtype: 'combo'
		    	,hiddenName: 'menutype'
		    	,id: 'menuTypeCombo'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,lastQuery: ''
			    ,hidden: true
			    ,mode: 'remote'
		    	,store:new Ext.data.Store({
					proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=content',
	            		method: 'POST'
			      	})
					,baseParams:{task: "action",action:'content.menu.getMenuTypes'}
					,reader: new Ext.data.JsonReader({   
						root: 'results',
						totalProperty: 'total'
					},[ 
						{name: 'id', type: 'string', mapping: 'menutype'},
						{name: 'name', type: 'string', mapping: 'menutype'}
					])
					,sortInfo:{field: 'name', direction: "ASC"}
					//,autoLoad: {}
					,listeners: {
						load: function(s,r)	{
							var combo = oseMscAddon.jcontent.grid.getTopToolbar().findById('menuTypeCombo')
							
							if(!oseMscAddon.jcontentParams.menutype)	{
								combo.setValue(r[0].data.id)
								combo.fireEvent('select',combo,r[0],0)
								
							}	else	{
								combo.setValue(oseMscAddon.jcontentParams.menutype)
							}
							
						}
					}
				})
			    ,valueField: 'id'
			    ,displayField: 'name'
			    ,listeners: {
		    		select: function(c,r,i)	{
		    			oseMscAddon.jcontentParams.menutype = r.data.name;
		    			oseMscAddon.jcontent.grid.getStore().reload({
		    				params:{msc_id:oseMscs.msc_id}
		    			});
		    		}
		        }
		    },'-',{
		    	text: Joomla.JText._('Search')
		    },
				new Ext.ux.form.SearchField({
	                store: oseMscAddon.jcontentParams.store,
	                paramName: 'search',
	                width:150
	            })
			]
		}]
	});