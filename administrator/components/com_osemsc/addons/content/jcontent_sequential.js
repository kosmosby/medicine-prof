Ext.ns('oseMscAddon','oseMscAddon.jcontentSeqParams');
	
	
	oseMscAddon.jcontentSeqParams.buildInfra = function() 	{
		return {
			buildStore: function(params)	{
				var gStore = new Ext.data.Store({
					proxy: new Ext.data.HttpProxy({
			    		url: 'index.php?option=com_osemsc&controller=content'
			    		,method: 'POST'
					})
					,baseParams:params
					,reader: new Ext.data.JsonReader({
						root: 'results'
						,totalProperty: 'total'
						,idProperty: 'id'
					},[
						{name: 'id', type: 'string', mapping: 'id'}
						,{name: 'title', type: 'string', mapping: 'title'}
			    		,{name: 'time_length', type: 'string', mapping: 'time_length'}
			    		,{name: 'controlled', type: 'string', mapping: 'controlled'}
					    ,{name: 'type', type: 'string', mapping: 'type'}
					    ,{name: 'content_id', type: 'string', mapping: 'content_id'}
					    ,{name: 'status', type: 'string', mapping: 'status'}
					    ,{name: 'treename', type: 'string', mapping: 'treename'}
			  		])

					,sortInfo:{field: 'id', direction: "ASC"}
					,autoLoad:{}
				});

				return gStore
			}

			,buildSm: function()	{
				var sm = new Ext.grid.CheckboxSelectionModel({});

				return sm;
			}

			,buildCm: function(sm,viewType)	{
				var cm = new Ext.grid.ColumnModel({
					defaults: {
			            sortable: false
			            ,width: 150
			        },
			        columns:[ 
						sm
						,new Ext.grid.RowNumberer({header:'#'})
					    ,{id:'id',header: Joomla.JText._("ID"), width: 20, dataIndex: 'id',hidden:true}
				        ,{id:'treename',header: Joomla.JText._('Content_Item'),  dataIndex: 'treename'}
				        ,{	
				        	header: Joomla.JText._("Controlled"),  dataIndex: 'controlled', width: 200
				        },{
				        	header: Joomla.JText._('Time_Length'),  dataIndex: 'time_length', width: 150
			        		,editor: new Ext.form.TextField({})
				        	
				        },{
				        	header: Joomla.JText._('Time_Unit'),  dataIndex: 'time_unit', width: 150
			        		,renderer: function(val)	{
			        			return Joomla.JText._('Week_s')
			        		}
				        }
				        ,{
				        	id:'action',header: Joomla.JText._("Control")+' '+viewType+"(s)", width: 150
				        	,xtype: 'actioncolumn'
			                ,align: 'center'
			                ,items: [{
			                    getClass: function(v, meta, rec)	{
			                        return 'article-col';
			                	}
			                    ,tooltip: Joomla.JText._('View')+' '+viewType
			                }]
				        }
				  	]
				});

				return cm;
			}
			
			,changeStatus: function(grid,status,content_type)	{
				var ids = grid.getSelectionModel().getSelections();
		    		
	    		var menu_ids = new Array();
	    		var time_length = new Array();
	    		var time_unit = new Array();
	    		
	    		for(i=0;i < ids.length; i++)	{
	    			var r = ids[i];
	    			menu_ids[i] = r.id;
	    			time_length[i] = r.get('time_length');
	    			time_unit[i] = r.get('time_unit');
	    		}
	    		
	    		Ext.Ajax.request({
	    			url:'index.php?option=com_osemsc&controller=content',
	    			params:{
	    				task:'action',action:'content.jcontent.changeStatus','jc_ids[]':menu_ids,
	    				msc_id: oseMsc.msc_id, status: status, content_type: content_type,'time_length[]':time_length
	    				,'time_unit[]':time_unit
	    			}
	    			,callback: function(el,success,response,opt)	{
	    				var msg = Ext.decode(response.responseText);
	    				oseMsc.ajaxSuccess(response,opt);
	    				
	    				if(msg.success)	{
	    					grid.getSelectionModel().clearSelections();
	    					grid.getStore().reload();
	    					grid.getView().refresh();
	    				}
	    			}
	    		});
			}
		}
	}()
	
	oseMscAddon.jcontentSeqParams.catInit = function()	{
		var store = oseMscAddon.jcontentSeqParams.buildInfra.buildStore({
			task: 'action', action: 'content.jcontent.getSequentialCatList'
			,msc_id: oseMsc.msc_id
		});
		var sm = oseMscAddon.jcontentSeqParams.buildInfra.buildSm();
		var cm = oseMscAddon.jcontentSeqParams.buildInfra.buildCm(sm,'Article');
		cm.columns.pop();
		var buildGrid = new ose.quickGrid.build(store,cm,sm);
		
		buildGrid.initEditorGrid = function(id)	{
			var grid = new Ext.grid.EditorGridPanel({
				store: this.store
				,cm: this.cm
				,sm: this.sm
				,'id': id
				,tbar: [{
					text: Joomla.JText._('Show_to_Members')
					,itemId: 'btnStM'
					,scope:this
				},{
					text: Joomla.JText._('Show_to_All')
					,itemId: 'btnStA'
					,scope:this
				},'->',Joomla.JText._('Max_Levels'),{
					xtype: 'combo'
					,width: 50
				    ,typeAhead: true
				    ,triggerAction: 'all'
				    ,lazyRender:true
				    ,mode: 'local'
				    ,store: new Ext.data.ArrayStore({
				        id: 0
				        ,fields: [
				            'myId'
				            ,'displayText'
				        ]
				        ,data: [
				        	[0, 'All'],[1, '1'],[2, '2'],[3, '3']
				        	,[4, '4'],[5, '5'],[6, '6'],[7, '7']
				        	,[8, '8'],[9, '9'],[10, '10']
				        ]
				    })
				    ,valueField: 'myId'
				    ,displayField: 'displayText'
				    ,listeners: {
				    	select: function(c,newV,oldV)	{
				    		store.setBaseParam('maxLevel',c.getValue())
				    		store.load();
				    		grid.getView().refresh();
				    	}
				    }
				},'-',Joomla.JText._('Search')
				,new Ext.ux.form.SearchField({
	                store: this.store,
	                paramName: 'search',
	                width:150
	            })]
				,bbar: this.buildBottomToolbar()
				,autoExpandColumn: 'treename'
				,height: 500
				,listeners:{
					afteredit: function(e)	{
						Ext.Ajax.request({
							url: 'index.php?option=com_osemsc'
							,params:{
								controller:'content',task: "action",action:'content.jcontent.updateParams'
								,field: e.field, value: e.value, jc_id: e.record.get('id'),msc_id:oseMsc.msc_id
								,status: e.record.get('status')
							}
						})
					}
				}
			})
			
			this.grid = grid;
		}
		
		buildGrid.initEditorGrid('cat-grid');
		buildGrid.setTopBtnAction('btnStM',function()	{
			oseMscAddon.jcontentSeqParams.buildInfra.changeStatus(this.grid,1,'cat')
		});
		buildGrid.setTopBtnAction('btnStA',function()	{
			oseMscAddon.jcontentSeqParams.buildInfra.changeStatus(this.grid,0,'cat')
		});
		
		var gridCat = buildGrid.output();
		
		return gridCat
	}
	
	oseMscAddon.jcontent_sequential = oseMscAddon.jcontentSeqParams.catInit();