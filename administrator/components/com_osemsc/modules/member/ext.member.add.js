Ext.ns('oseMemMsc','oseMemMsc.addParams');
	
	oseMemMsc.addParams.iterateSuccessFn = function(addMscOptionWin,iterateNumber)	{
		addMscOptionWin.form.getForm().submit({
			url: 'index.php?option=com_osemsc&controller=members'
			,waitMsg: 'Loading...'
			,timeout: 6000000
			,success: function(form,action){
				var msg = action.result;
				
				if(msg.end)	{
					oseMsc.formSuccess(form,action);
					if(msg.success)	{
						oseMemMsc.add.getStore().reload();
						oseMemMsc.add.getView().refresh();
						
						oseMemsMsc.grid.getBottomToolbar().doRefresh();
						oseMsc.refreshGrid(oseMemsMsc.mTree);
					}
				}	else	{
					iterateNumber = iterateNumber+1;
					oseMemMsc.addParams.iterateSuccessFn(addMscOptionWin,msg.iterate_number)
				}
				
			}
		    ,params: { 'task':'importUser2Member', msc_id: oseMemsMsc.tree_msc_id, iterate_number:iterateNumber}
		});
	}
	
	oseMemMsc.addParams.mscOpitonBox = function(fn)	{
		var addMscOptionWin = new Ext.Window({
			title: Joomla.JText._('Select_Msc_Option')
			,width: '500'
			,height: '100'
			,modal: true
			,bodyStyle: 'padding: 10px'
			,items:[{
				xtype:'form'
				,ref: 'form'
				,labelWidth: 150
				,border: false
				,items:[{
					itemId:'msc_option'
					,width: 230
			  		,ref: 'msc_option'
			  		,xtype: 'combo'
			        ,fieldLabel: Joomla.JText._('Membership_Option')
			        ,hiddenName: 'msc_option'
				    ,typeAhead: true
				    ,triggerAction: 'all'
				    ,lazyRender:true
				    ,lastQuery:''
				    ,mode: 'local'
				    ,store: new Ext.data.Store({
				  		proxy: new Ext.data.HttpProxy({
			            	url: 'index.php?option=com_osemsc&controller=members',
				            method: 'POST'
			     	 	})
					  	,baseParams:{task: 'getOptions',msc_id: oseMemsMsc.tree_msc_id}
					  	,reader: new Ext.data.JsonReader({
					    	root: 'results',
						    totalProperty: 'total'
					  	},[
						    {name: 'id', type: 'string', mapping: 'id'},
						    {name: 'title', type: 'string', mapping: 'title'}
					  	])
				  		,sortInfo:{field: 'id', direction: "ASC"}
					  	,listeners: {
					    	load: function(s,r,i)	{
					    		addMscOptionWin.form.getForm().findField('msc_option').setValue(r[0].data.id)
					    	}
					    }
					})
				    ,valueField: 'id'
				    ,displayField: 'title'
				    ,listeners: {
				    	render: function(c)	{
				    		c.getStore().load();
				    	}
				    }
				}]
				,buttons:[{
					text: Joomla.JText._('OK')
					,handler: fn
					,scope: this
				}]
			}]
		})
		
		return addMscOptionWin;
	}
/* -- Store -- */
	oseMemMsc.addParams.store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=members',
	            method: 'POST'
	        }),
		  baseParams:{task: "getUsers",limit: 20, msc_id: oseMemsMsc.tree_msc_id}, 
		  reader: new Ext.data.JsonReader({   
		              // we tell the datastore where to get his data from
		    root: 'results',
		    totalProperty: 'total'
		  },[ 
		    {id:"id",name: 'ID', type: 'int', mapping: 'id'},
		    {name: 'Uname', type: 'string', mapping: 'username'},
		    {name: 'Name', type: 'string', mapping: 'name'},
		    {name: 'Email', type: 'string', mapping: 'email'}
		  ]),
		  sortInfo:{field: 'ID', direction: "ASC"},
		  autoLoad: {}
	});


	oseMemMsc.addParams.sm = new Ext.grid.CheckboxSelectionModel({
        listeners: {
            selectionchange: function(sm) {
            	//oseMemMsc.add.tbar.addBtn.setDisabled(sm.getCount() < 1);
            }
        }
    });

//-- Buttons
				
	oseMemMsc.addParams.tbar = new Ext.Toolbar({
	    items: [{
	    	ref:'createBtn',
		    iconCls: 'icon-user-add',
		    hidden: true,
		    text: Joomla.JText._('Create_A_New_User'),
		    handler: function(btn,event){
		        if(!oseMemMscAdd_createForm)	{
		        	var oseMemMscAdd_createForm = new Ext.Window({
		        		title: Joomla.JText._('Create_A_New_User')
		        		,width: 500
		        		,autoHeight: true
		        		,defaults:{border:false}
		        		,modal: true
		        		,x:300
		        		,y:50
		        		,autoLoad:{
							url: 'index.php?option=com_osemsc'
							,params:{ task:'getMod',addon_name: 'create',addon_type: 'member' }
							,scripts: true
							,callback: function(el ,success, response, options)	{
								oseMemMscAdd_createForm.add(eval('oseMemMsc.create'));
								oseMemMscAdd_createForm.doLayout();
								oseMemMsc.create.getForm().findField('msc_option').setDisabled(true)
								oseMemMsc.create.getForm().findField('msc_option').setVisible(false)
								oseMemMsc.create.buttons[0].on('click',function()	{
									oseMemMscAdd_createForm.getEl().mask(Joomla.JText._('Loading'))
									oseMemMsc.create.getForm().submit({
					    				url: 'index.php?option=com_osemsc&controller=members'
						    			,params: {task: 'action', action: 'member.juser.createUser'}
						    			,success: function(form,action)	{
						    				oseMsc.formSuccess(form,action);
						    				oseMemMsc.add.getStore().reload();
						    				oseMemMsc.add.getView().refresh();
						    				
						    				oseMemMscAdd_createForm.close()
						    				oseMemMscAdd_createForm.getEl().unmask()
						    			}
						    			,failure: function(form,action)	{
						    				oseMsc.formFailureMB(form,action);
						    				oseMemMscAdd_createForm.getEl().unmask()
						    			}
					    			})
								})
							}
						}
		        	});
		        }
		        
		        oseMemMscAdd_createForm.show();
		    }
	    },{
	    	ref:'addBtn',
		    iconCls: 'icon-user-add',
		    text: Joomla.JText._('Add_to_membership'),
		    handler: function(){
				var s = oseMemMsc.add.getSelectionModel().getSelections();
				var member_ids = new Array();
				for( var i =0; i < s.length; i++ ) {//>
					var r = s[i];
					member_ids[i] = r.id;
				};
				
				if(!addMscOptionWin)	{
					var addMscOptionWin = oseMemMsc.addParams.mscOpitonBox(function()	{
						Ext.Msg.wait('Loading');
						addMscOptionWin.form.getForm().submit({
							url: 'index.php?option=com_osemsc&controller=members',
							//waitMsg: 'Loading...',
							timeout: 6000000,
							success: function(form,action){
								Ext.Msg.hide();
								var msg = action.result;
								oseMsc.formSuccess(form,action);
								
								if(msg.success)	{
									oseMemMsc.add.getStore().reload();
									oseMemMsc.add.getView().refresh();
									oseMsc.refreshGrid(oseMemsMsc.mTree);
									oseMemsMsc.grid.getBottomToolbar().doRefresh();
								}
							},
						    params: { 'task':'joinMsc','member_ids[]': member_ids, msc_id:oseMemsMsc.tree_msc_id}
						});
					});
				}
				
				addMscOptionWin.show().alignTo(Ext.getBody(),'t-t')
		    }
	    },{
	    	ref:'importBtn',
		    iconCls: 'icon-user-add',
		    text: Joomla.JText._('Load_all_user_to_this_membership'),
		    handler: function()	{
				if(!addMscOptionWin)	{
					var addMscOptionWin = oseMemMsc.addParams.mscOpitonBox(function()	{
						Ext.Msg.confirm(Joomla.JText._('Notice'),Joomla.JText._('Are_you_sure_to_import_all_users')+oseMemMsc.addParams.store.getTotalCount() +Joomla.JText._('to_join_this_membership'),function(btn, text){
						    if (btn == 'yes'){
						       oseMemMsc.addParams.iterateSuccessFn(addMscOptionWin,0);
						   }
						})
					});
				}
				addMscOptionWin.show().alignTo(Ext.getBody(),'t-t')
		    }
	    }
	        ,'->'
	        ,Joomla.JText._('You_can_search_by_name_user_name_and_email')
	        ,new Ext.ux.form.SearchField({
                store: oseMemMsc.addParams.store,
                paramName: 'search',
                width:150
            })
	    ]
	});


// --------------------------------- Members Grid ---------------------------------------- //
	oseMemMsc.add = new Ext.grid.GridPanel({
        store:oseMemMsc.addParams.store
       
     	,height: 500
     	,viewConfig:{forceFit:true}
     	,colModel: new Ext.grid.ColumnModel({
		    defaults: {
		        width: 200,
		        sortable: true
		    },
		    columns: [
		        oseMemMsc.addParams.sm,
		        new Ext.grid.RowNumberer({header:'#'}),
			    {id: 'uname', header: Joomla.JText._('User_Name'), dataIndex: 'Uname'},
			    {id: 'name', header: Joomla.JText._('Name'), dataIndex: 'Name'},
			    {id: 'email', header: Joomla.JText._('Email'), dataIndex: 'Email'},
			    {id: 'id', header: Joomla.JText._('ID'), dataIndex: 'ID', width: 50}
		    ]
		})
     	
     	,sm: oseMemMsc.addParams.sm
     	
     	,tbar: oseMemMsc.addParams.tbar
            
     	,bbar:new Ext.PagingToolbar({
    		pageSize: 20,
    		store: oseMemMsc.addParams.store,
    		displayInfo: true,
    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
			emptyMsg: Joomla.JText._("No_topics_to_display")
	    })
    });
