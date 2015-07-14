Ext.ns('oseMemsMsc');

	oseMemsMsc.buildGrid = function()	{
		this.store = new Ext.data.Store({
			  proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=members',
		            method: 'POST'
		        }),
			  baseParams:{task: "getList"},
			  reader: new Ext.data.JsonReader({
			    root: 'results',
			    totalProperty: 'total',
			    idProperty: 'member_id'
			  },[
			    {name: 'id', type: 'int', mapping: 'id'},
			    {name: 'member_id', type: 'int', mapping: 'member_id'},
			    {name: 'msc_id', type: 'int', mapping: 'msc_id'},
			    {name: 'Uname', type: 'string', mapping: 'username'},
			    {name: 'Name', type: 'string', mapping: 'name'},
			    {name: 'Membership', type: 'string', mapping: 'msc_name'},
			    {name: 'Email', type: 'string', mapping: 'email'},
			    {name: 'Start_date', type: 'string', mapping: 'start_date'},
			    {name: 'Expired_date', type: 'string', mapping: 'expired_date'},
			    {name: 'Status', type: 'int', mapping: 'status'}
			  ])
			  ,sortInfo:{field: 'id', direction: "ASC"}
		});

		this.cm = new Ext.grid.ColumnModel({
	        defaults: {
	            width: 150,
	            sortable: true
	        },
	        columns: [
		        new Ext.grid.RowNumberer({header:'#'}),
			    {id: 'id', header: Joomla.JText._('ID'), dataIndex: 'id', hidden: true,hideable:true},
			    {id: 'name', header: Joomla.JText._('Name'), dataIndex: 'Name'},
			    {id: 'uname', header: Joomla.JText._('Username'), dataIndex: 'Uname'},
			    {id: 'start_date', header: Joomla.JText._('Start_Date'), dataIndex: 'Start_date', width: 135},
			    {id: 'expired_date', header: Joomla.JText._('Expired_date'), dataIndex: 'Expired_date', width: 135},
			    {id: 'status', header: Joomla.JText._('Status'), dataIndex: 'Status',hidden:true,
			    	renderer:function(val)	{
			    		var result = (val==1)?Joomla.JText._('Active'):Joomla.JText._('Expired');
			    		return result;
			    	}
			    }
			    ,{
			    	id: 'action', header: Joomla.JText._('Action'), xtype: 'actioncolumn',width: 50
			    	,items: [{
	                    getClass: function(v, meta, rec)	{
	                    	return 'delete-col';
	                	}
	                    ,tooltip: Joomla.JText._('Click_to_cancel_member_s_membership')
	                    ,handler: function(grid, rowIndex, colIndex) {
							grid.getSelectionModel().selectRow(rowIndex)
							grid.getTopToolbar().cancelBtn.fireEvent('click');
	                    }
	                }]
			    }
		    ]
		});

		this.sm = new Ext.grid.RowSelectionModel({singleSelect:true});

		this.deleteBtn = new Ext.Button({
			ref:'deleteBtn',
		    iconCls: 'icon-user-delete',
		    text: Joomla.JText._('Delete'),
		    disabled: true
		});

		this.renewBtn = new Ext.Button({
			ref:'renewBtn',
		    iconCls: 'icon-user-renew',
		    text: Joomla.JText._('Renew_Members'),
		    disabled: true,
		    handler: function(btn,event){
		        if(!oseMemsMscAddWin)	{
		        	var oseMemsMscAddWin = new Ext.Window({
		        		title: Joomla.JText._('Renew_Members_Membership')
		        		,width: 800
		        		,autoHeight: true
		        		,x: 250
		        		,y: 30
		        		,modal: true
		        		,autoLoad:{
							url: 'index.php?option=com_osemsc'
							,params:{ task:'getMod', addon_name: 'renew', addon_type: 'member', msc_id: oseMemsMsc.msc_id}
							,scripts: true
							,callback: function(el ,success, response, options)	{
								oseMemsMscAddWin.add(eval('oseMemMsc.renew'));
								oseMemsMscAddWin.doLayout();
							}
						}
		        	})
		        }

		        oseMemsMscAddWin.show();
		    }
		});

		this.createBtn = new Ext.Button({
			ref:'createBtn',
		    iconCls: 'icon-user-create',
		    text: Joomla.JText._('Create_New_Users'),
		    disabled: true
		});

		this.addBtn = new Ext.Button({
			ref:'addBtn',
		    iconCls: 'icon-user-add',
		    text: Joomla.JText._('Add_Members'),
		    disabled: true,
		    handler: function(btn,event){
		        if(!oseMemsMscAddWin)	{
		        	var oseMemsMscAddWin = new Ext.Window({
		        		title: Joomla.JText._('Add_a_new_user_to_a_membership')
		        		,width: 850
		        		,autoHeight: true
		        		,x: 250
		        		,y: 30
		        		,modal: true
		        		,autoLoad:{
							url: 'index.php?option=com_osemsc'
							,params:{ task:'getMod', addon_name: 'add', addon_type: 'member', msc_id: oseMemsMsc.msc_id}
							,scripts: true
							,callback: function(el ,success, response, options)	{
								oseMemsMscAddWin.add(eval('oseMemMsc.add'));
								oseMemsMscAddWin.doLayout();
							}
						}
		        	})
		        }

		        oseMemsMscAddWin.show();
		    }
		});

		this.cancelBtn = new Ext.Button({
			ref: 'cancelBtn',
			hidden: true,
	        iconCls: 'icon-user-delete',
	        text: Joomla.JText._('Cancel_Membership'),
	        disabled: true
		});

		this.tb = function()	{
			var deleteB = this.deleteBtn;
			var renewB = this.renewBtn;
			var addB = this.addBtn;
			var createB = this.createBtn;
			var cancelB = this.cancelBtn;
			var store = this.store;

			return new Ext.Toolbar({
			    items: [
			    	addB
			    	,renewB
			    	,createB
			    	,deleteB
			    	,cancelB
			    	,'->'
			    	, {
			        	ref:'status',
			        	xtype:'combo',
			        	editable: false,
			            hiddenName: 'status',
			            width:100,
					    typeAhead: true,
					    emptyText: Joomla.JText._('Status'),
					    triggerAction: 'all',
					    lazyRender:false,
					    mode: 'local',
					    store: new Ext.data.ArrayStore({
					        id: 0,
					        fields: [
					            'value',
					            'text'
					        ],
					        data: [
					        	['1',Joomla.JText._('Active')]
					        	,['0', Joomla.JText._('Expired')]
					        ]
					    }),
					    valueField: 'value',
					    displayField: 'text',
					    listeners: {
				    		render: function(c)	{
				    			c.setValue(1);
				    		}
				        }
			        }
			    	,'-',new Ext.ux.form.SearchField({
			            store: store,
			            paramName: 'search',
			            width:150,
			            emptyText: Joomla.JText._('Search_a_member')
			            ,listeners: {
			            	render:  function(f)	{
			            		Ext.QuickTips.register({
								    target: f.getEl(),
								    title: Joomla.JText._('Search_Range'),
								    text: Joomla.JText._('Username_Name_Email'),
								    width: 150,
								    dismissDelay: 10000 // Hide after 10 seconds hover
								});
			            		//alert(f.getEl())
			            	}
			            }
			        })
			    ]
			});
		}

		this.bb = new Ext.PagingToolbar({
			pageSize: 20
			,store: this.store
			,displayInfo: true
			,displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}'
			,emptyMsg: Joomla.JText._("No_topics_to_display")
			,plugins: new Ext.ux.grid.limit({})
	    })
	}

	oseMemsMsc.buildGrid.prototype = {
		init: function()	{
			var store = this.store;
			var sm = this.sm;
			var cm = this.cm;
			var tb = this.tb();
			var bb = this.bb;

			var grid = new Ext.grid.GridPanel({
		    	id:'ose-member-list'
		    	,title: Joomla.JText._('OSE_Member_List')
		        ,store: store
		        //,viewConfig:{forceFit:true}
		        ,autoExpandColumn: 'name'
		        ,margins: {top:5, right:3, bottom:5, left:3}
		     	,colModel:cm
		     	,height: 500
		     	,region: 'center'
		     	,sm: sm
		     	,tbar: tb
		     	,bbar: bb
		    });

		    return grid;
		}
	}
/*
	oseMemsMsc.store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=members',
	            method: 'POST'
	        }),
		  baseParams:{task: "getList"},
		  reader: new Ext.data.JsonReader({
		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'member_id'
		  },[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'Uname', type: 'string', mapping: 'username'},
		    {name: 'Name', type: 'string', mapping: 'name'},
		    {name: 'Membership', type: 'string', mapping: 'msc_name'},
		    {name: 'Email', type: 'string', mapping: 'email'},
		    {name: 'Start_date', type: 'string', mapping: 'start_date'},
		    {name: 'Expired_date', type: 'string', mapping: 'expired_date'},
		    {name: 'Status', type: 'int', mapping: 'status'}
		  ])
		  ,sortInfo:{field: 'id', direction: "ASC"}
	});


	oseMemsMsc.cm = new Ext.grid.ColumnModel({
        defaults: {
            width: 200,
            sortable: true
        },
        columns: [
	        new Ext.grid.RowNumberer({header:'#'}),
		    {id: 'id', header: 'ID', dataIndex: 'id', hidden: true,hideable:true},
		    {id: 'uname', header: 'Username', dataIndex: 'Uname'},
		    {id: 'name', header: 'Name', dataIndex: 'Name'},
		    {id: 'start_date', header: 'Start Date', dataIndex: 'Start_date'},
		    {id: 'expired_date', header: 'Expired date', dataIndex: 'Expired_date'},
		    {id: 'status', header: 'Status', dataIndex: 'Status',
		    	renderer:function(val)	{
		    		var result = (val==1)?'Active':'Expired';
		    		return result;
		    	}
		    }
	    ]
	});


	oseMemsMsc.createBtn = new Ext.Button({
		ref:'createBtn',
	    iconCls: 'icon-user-create',
	    text: 'Create A New User',
	    disabled: true,
	    handler: function(btn,event){
	        if(!oseMemsMscCreateWin)	{
	        	var oseMemsMscCreateWin = new Ext.Window({
	        		title: 'Create a member'
	        		,width: 800
	        		,autoHeight: true
	        		,modal: true
	        		,autoLoad:{
						url: 'index.php?option=com_osemsc'
						,params:{ task:'getMod', addon_name: 'create', addon_type: 'member' }
						,scripts: true
						,callback: function(el ,success, response, options)	{
							oseMemsMscCreateWin.add(eval('oseMemMsc.create'));
							oseMemsMscCreateWin.doLayout();

							oseMemMsc.create.buttons[0].on('click',function()	{
								//oseMemMsc.create.getEl().mask('Loading...')
								oseMemMsc.create.getForm().submit({
				    				url: 'index.php?option=com_osemsc&controller=members'
					    			,params: {task: 'action', action: 'member.juser.createMember',msc_id: oseMemsMsc.msc_id}
					    			,success: function(form,action)	{
					    				oseMsc.formSuccess(form,action);

					    				oseMemsMsc.grid.getStore().reload();
					    				oseMemsMsc.grid.getView().refresh();

					    				oseMemsMscCreateWin.close()
					    				oseMemMsc.create.getEl().unmask()
					    			}
					    			,failure: function(form,action)	{
					    				oseMsc.formFailureMB(form,action);
					    				oseMemMsc.create.getEl().unmask()
					    			}
				    			})
							})
						}
					}

	        	})
	        }

	        oseMemsMscCreateWin.show().alignTo(Ext.getBody(),'t-t');
	    }
	});

	oseMemsMsc.addBtn = new Ext.Button({
		ref:'addBtn',
	    iconCls: 'icon-user-add',
	    text: 'Add New Members',
	    disabled: true,
	    handler: function(btn,event){
	        if(!oseMemsMscAddWin)	{
	        	var oseMemsMscAddWin = new Ext.Window({
	        		title: 'Add a new user to a membership'
	        		,width: 800
	        		,autoHeight: true
	        		,x: 250
	        		,y: 30
	        		,modal: true
	        		,autoLoad:{
						url: 'index.php?option=com_osemsc'
						,params:{ task:'getMod', addon_name: 'add', addon_type: 'member', msc_id: oseMemsMsc.msc_id}
						,scripts: true
						,callback: function(el ,success, response, options)	{
							oseMemsMscAddWin.add(eval('oseMemMsc.add'));
							oseMemsMscAddWin.doLayout();
						}
					}
	        	})
	        }

	        oseMemsMscAddWin.show();
	    }
	});

	oseMemsMsc.cancelBtn = new Ext.Button({
		ref: 'cancelBtn',
        iconCls: 'icon-user-delete',
        text: 'Cancel Membership',
        disabled: true
	});


	oseMemsMsc.tbar = new Ext.Toolbar({
	    items: [
	    	oseMemsMsc.createBtn
	    	,oseMemsMsc.addBtn
	    	,oseMemsMsc.cancelBtn
	    	,'->'
	    	, 'Status',{
	        	ref:'status',
	        	xtype:'combo',
	        	editable: false,
	            hiddenName: 'status',
	            width:100,
			    typeAhead: true,
			    triggerAction: 'all',
			    lazyRender:false,
			    mode: 'local',
			    store: new Ext.data.ArrayStore({
			        id: 0,
			        fields: [
			            'value',
			            'text'
			        ],
			        data: [
			        	['1','Active']
			        	,['0', 'Expired']
			        ]
			    }),
			    valueField: 'value',
			    displayField: 'text',
			    listeners: {
			        select: function(c,r,i)	{
			        	oseMemsMsc.store.setBaseParam('status',r.data.value);
			        	oseMsc.refreshGrid(oseMemsMsc.grid);
		    		}
		    		,render: function(c)	{
		    			c.setValue(1);
		    		}
		        }
	        }
	    	,new Ext.ux.form.SearchField({
	            store: oseMemsMsc.store,
	            paramName: 'search',
	            width:150
	        })
	    ]
	});



// --------------------------------- Members Grid ---------------------------------------- //
	oseMemsMsc.grid = new Ext.grid.GridPanel({
    	id:'ose-member-list'
    	,title: 'OSE Member List'
        ,store: oseMemsMsc.store
        ,viewConfig:{forceFit:true}
        ,plugins: []
        ,margins: {top:5, right:3, bottom:5, left:3}
     	,colModel:oseMemsMsc.cm
     	,height: 500
     	,region: 'center'
     	,sm: new Ext.grid.RowSelectionModel({singleSelect:true})

     	,tbar: oseMemsMsc.tbar
     	,bbar: 	new Ext.PagingToolbar({
			pageSize: 20
			,store: oseMemsMsc.store
			,displayInfo: true
		    ,displayMsg: 'Displaying items {0} - {1} of {2}'
		    ,emptyMsg: "No items to display"
			,plugins: new Ext.ux.grid.limit({})
	    })
    });
*/