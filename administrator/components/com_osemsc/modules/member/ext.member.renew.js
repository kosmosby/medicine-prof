Ext.ns('oseMemMsc','oseMemMsc.renewParams');
	
	oseMemMsc.renewParams.iterateSuccessFn = function(addMscOptionWin,iterateNumber)	{
		addMscOptionWin.form.getForm().submit({
			url: 'index.php?option=com_osemsc&controller=members'
			,waitMsg: 'Loading...'
			,timeout: 6000000
			,success: function(form,action){
				var msg = action.result;
				
				if(msg.end)	{
					oseMsc.formSuccess(form,action);
					if(msg.success)	{
						oseMemMsc.renew.getStore().reload();
						oseMemMsc.renew.getView().refresh();
						
						oseMemsMsc.grid.getBottomToolbar().doRefresh();
						oseMsc.refreshGrid(oseMemsMsc.mTree);
					}
				}	else	{
					iterateNumber = iterateNumber+1;
					oseMemMsc.renewParams.iterateSuccessFn(addMscOptionWin,msg.iterate_number)
				}
				
			}
		    ,params: { 'task':'importUser2Member', msc_id: oseMemsMsc.tree_msc_id, iterate_number:iterateNumber}
		});
	}
	
	oseMemMsc.renewParams.mscOpitonBox = function(fn)	{
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
	oseMemMsc.renewParams.store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=members',
	            method: 'POST'
	        }),
		  baseParams:{task: "getList",limit: 20, msc_id: oseMemsMsc.tree_msc_id}, 
		  reader: new Ext.data.JsonReader({   
		              // we tell the datastore where to get his data from
		    root: 'results',
		    totalProperty: 'total'
		  },[ 
		    {id:"id",name: 'ID', type: 'int', mapping: 'member_id'},
		    {name: 'Uname', type: 'string', mapping: 'username'},
		    {name: 'Name', type: 'string', mapping: 'name'},
		    {name: 'Email', type: 'string', mapping: 'email'},
		    {name: 'expired_date', type: 'string', mapping: 'expired_date'},
		    {name: 'start_date', type: 'string', mapping: 'start_date'}
		  ]),
		  sortInfo:{field: 'ID', direction: "ASC"},
		  autoLoad: {}
	});


	oseMemMsc.renewParams.sm = new Ext.grid.CheckboxSelectionModel({
        listeners: {
            selectionchange: function(sm) {
            	//oseMemMsc.add.tbar.addBtn.setDisabled(sm.getCount() < 1);
            }
        }
    });

//-- Buttons
				
	oseMemMsc.renewParams.tbar = new Ext.Toolbar({
	    items: [{
	    	ref:'addBtn',
		    iconCls: 'icon-user-add',
		    text: Joomla.JText._('Renew_membership'),
		    handler: function(){
				var s = oseMemMsc.renew.getSelectionModel().getSelections();
				var member_ids = new Array();
				for( var i =0; i < s.length; i++ ) {//>
					var r = s[i];
					member_ids[i] = r.data.ID;
				};
				
				if(!addMscOptionWin)	{
					var addMscOptionWin = oseMemMsc.renewParams.mscOpitonBox(function()	{
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
									oseMemMsc.renew.getStore().reload();
									oseMemMsc.renew.getView().refresh();
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
	    }
	        ,'->'
	        ,Joomla.JText._('You_can_search_by_name_user_name_and_email')
	        ,new Ext.ux.form.SearchField({
                store: oseMemMsc.renewParams.store,
                paramName: 'search',
                width:150
            })
	    ]
	});


// --------------------------------- Members Grid ---------------------------------------- //
	oseMemMsc.renew = new Ext.grid.GridPanel({
        store:oseMemMsc.renewParams.store
       
     	,height: 500
     	,viewConfig:{forceFit:true}
     	,colModel: new Ext.grid.ColumnModel({
		    defaults: {
		        width: 200,
		        sortable: true
		    },
		    columns: [
		        oseMemMsc.renewParams.sm,
		        new Ext.grid.RowNumberer({header:'#'}),
			    {id: 'uname', header: Joomla.JText._('User_Name'), dataIndex: 'Uname'},
			    {id: 'name', header: Joomla.JText._('Name'), dataIndex: 'Name'},
			    {id: 'email', header: Joomla.JText._('Email'), dataIndex: 'Email'},
			    {id: 'start_date', header: Joomla.JText._('Start_Date'), dataIndex: 'start_date'},
			    {id: 'expired_date', header: Joomla.JText._('Expired_Date'), dataIndex: 'expired_date'},
			    {id: 'id', header: 'ID', dataIndex: Joomla.JText._('ID'), width: 50}
		    ]
		})
     	
     	,sm: oseMemMsc.renewParams.sm
     	
     	,tbar: oseMemMsc.renewParams.tbar
            
     	,bbar:new Ext.PagingToolbar({
    		pageSize: 20,
    		store: oseMemMsc.renewParams.store,
    		displayInfo: true,
    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
			emptyMsg: Joomla.JText._("No_topics_to_display")
	    })
    });
