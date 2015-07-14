Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();
	var addonMscMembershipGridStore =new Ext.data.Store({
		proxy: new Ext.data.HttpProxy({
	      	url: 'index.php?option=com_osemsc',
	        method: 'POST'
	    })
		,baseParams:{
			controller: "members"
		  	,task: "action"
		  	,action:'member.msc.getItems'
		  	,limit: 20
		  	,member_id:''
		}
		,reader: new Ext.data.JsonReader({   
		    root: 'results',
		    totalProperty: 'total'
		},[ 
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'Membership', type: 'string', mapping: 'msc_name'},
		    {name: 'Start_date', type: 'string', mapping: 'start_date'},
		    {name: 'Expired_date', type: 'string', mapping: 'expired_date'},
		    {name: 'Status', type: 'string', mapping: 'status'}
		])
		,listeners: {
		  	beforeload: function(s)	{
		  		s.setBaseParam('member_id',oseMemsMsc.member_id);
		  	}
		}
		,sortInfo:{field: 'id', direction: "ASC"}
		  //autoLoad: {},
	});
	
	var addonMscMembershipGrid = new Ext.grid.GridPanel({
		ref: 'grid'
		,height: 250
		,viewConfig:{forceFit:true}
		,store: addonMscMembershipGridStore
		,cm: new Ext.grid.ColumnModel({
	        defaults: {
	        },
	        columns: [
		        new Ext.grid.RowNumberer({header:'#'}),
			    {id: 'id', header: Joomla.JText._('ID'), dataIndex: 'id', hidden: true,hideable:true},
			    {id: 'name', header: Joomla.JText._('Membership'), dataIndex: 'Membership'},
			    {id: 'start_date', header: Joomla.JText._('Start_Date'), dataIndex: 'Start_date'},
			    {id: 'expired_date', header: Joomla.JText._('Expired_Date'), dataIndex: 'Expired_date'},
			    {id: 'status', header: Joomla.JText._('Status'), dataIndex: 'Status',
			    	renderer: function(val){
			    		if(val == '1'){
			    			return Joomla.JText._('Active');
			    		}	else	{
			    			return Joomla.JText._('Expired');
			    		}
			    	}
			    }
		    ]
		})
		,sm:new Ext.grid.RowSelectionModel({
			singleSelect:true,
			listeners:{
				selectchange:function(sm){
					addonMscMembershipGrid.getTopToolbar().editBtn.setDisabled(sm.getCount() < 1); // >
				}
			}
		})
		,tbar:new Ext.Toolbar({
		    items: [{
		    	text: Joomla.JText._('edit'),
		    	ref:'editBtn',
		    	handler: function(){
		    		var node = addonMscMembershipGrid.getSelectionModel().getSelected();
		    		if(!oseMscAddonMscEditForm)	{
		    			var oseMscAddonMscEditForm = new Ext.Window({
		    				title: Joomla.JText._('Edit'),
		    				width: 500,
		    				height: 200,
		    				modal: true,
		    				items:[{
		    					xtype:'form',
		    					ref:'form',
		    					bodyStyle: 'padding : 10px',
		    					items:[{
		    						itemId: 'msc_member_id',
		    						xtype: 'hidden',
		    						name:'msc_member_id',
		    						value:node.id
		    					},{
		    						fieldLabel:Joomla.JText._('Start_Date'),
		    						xtype:'compositefield',
		    						items:[{
		    							xtype:'datefield',
		    							name:'start_date'
		    						},{
		    							xtype: 'timefield',
		    							name: 'start_time',
		    							width:150
		    						}]
		    					},{
		    						fieldLabel:Joomla.JText._('Expired_Date'),
		    						xtype:'compositefield',
		    						items:[{
		    							xtype:'datefield',
		    							name:'exp_date'
		    						},{
		    							xtype: 'timefield',
		    							name: 'exp_time',
		    							width:150
		    						}]
		    					}],
		    					
		    					reader:new Ext.data.JsonReader({   
								    root: 'result',
								    idProperty: 'msc_member_id',
								    totalProperty: 'total',
								    fields:[ 
									    {name: 'start_date', type: 'string', mapping: 'start_date'},
									    {name: 'start_time', type: 'string', mapping: 'start_time'},
									    {name: 'exp_date', type: 'string', mapping: 'exp_date'},
									    {name: 'exp_time', type: 'string', mapping: 'exp_time'}
								  	]
							  	}),
							  	
							  	buttons: [{
							  		text: Joomla.JText._('Save'),
							  		handler : function()	{
							  			oseMscAddonMscEditForm.form.getForm().submit({
							  				url:'index.php?option=com_osemsc'
			    							,params:{
			    								controller:'member'
			    								,task: 'action'
			    								,action:'member.msc.updateMemRecurrence'
			    							}
			    							,success: function(form,action)	{
			    								oseMsc.formSuccess(form,action);
			    								addonMscMembershipGrid.getStore().reload();
			    								addonMscMembershipGrid.getView().refresh();
			    							}
			    							,failure: function(form,action)	{
			    								oseMsc.formFailure(form,action);
			    							}
							  			});
							  		}
							  	}]
		    				}],
		    				
		    				listeners:{
		    					show: function(p)	{
		    						var node = addonMscMembershipGrid.getSelectionModel().getSelected();
		    						p.form.getForm().load({
		    							url:'index.php?option=com_osemsc',
		    							params:{
		    								controller:'member'
		    								,task: 'action'
		    								,action:'member.msc.getMemRecurrence'
		    								,msc_member_id:node.id
		    							}
		    						});
		    					}
		    				}
		    			});
		    		}
		    		oseMscAddonMscEditForm.show(this);
		    	}
		    },{
		    	text: Joomla.JText._('remove'),
		    	hidden:true,
		    	handler: function(){
		    		var msc_id = addonMscMembershipGrid.getSelectionModel().getSelected();
		    		if(addonMscMembershipGrid.getSelectionModel().hasSelection()){
		    			Ext.Ajax.request({
			    			url: 'index.php?option=com_osemsc'
						    ,success: function(response,opts){
						    	oseMsc.ajaxSuccess(response,opts);
								addonMscMembershipGridStore.reload();
								addonMscMembershipGrid.getView().refresh();
						    }
						    ,failure: function(response,opts){
						    	oseMsc.ajaxFailure(response,opts);
						    }
						    ,params: { 
						    	controller:'member'
						    	,task: 'action'
						    	, action:'member.msc.cancelMsc' 
						    	,member_id: oseMemsMsc.member_id 
						    	,msc_id: addonMscMembershipGrid.getSelectionModel().getSelected().id
						    }
			    		});
		    		}
		    		else{
		    			Ext.Msg.alert('Error',Joomla.JText._('Please_Select_One_Membership'));
		    		}
		    	}
		    }]
		})
		,bbar: new Ext.PagingToolbar({
    		pageSize: 20,
    		store: addonMscMembershipGridStore,
    		displayInfo: true,
    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
			emptyMsg: Joomla.JText._("No_topics_to_display")
	    })
	});
	var addonMscMembershipCombo = new Ext.FormPanel({
		border: false
		,items:[{
			xtype:'compositefield'
			,fieldLabel: Joomla.JText._('Select')
			,items:[{
				xtype:'combo'
				,hiddenName: 'msc_id'
				,width: 150
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,lazyRender:false
			    ,mode: 'remote'
			    ,store: new Ext.data.Store({
					  proxy: new Ext.data.HttpProxy({
				            url: 'index.php?option=com_osemsc&controller=memberships'
				            ,method: 'POST'
				      })
					  ,baseParams:{task: "getFullTree"}
					  ,reader: new Ext.data.JsonReader({   
					    root: 'results'
					    ,totalProperty: 'total'
					  },[ 
					    {name: 'MscID', type: 'int', mapping: 'id'}
					    ,{name: 'Title', type: 'string', mapping: 'treename'}
					  ])
				})
			    ,valueField: 'MscID'
			    ,displayField: 'Title'
			},{
				xtype: 'button'
				,text: Joomla.JText._('add')
				,handler: function(){
					addonMscMembershipCombo.form.getForm().submit({
						clientValidation: true
						,url:'index.php?option=com_osemsc&controller=members'
						,params:{
							task: 'action',action:'member.msc.joinMsc'
			        		,member_id: oseMemsMsc.member_id
			        	}
			        	,success: function(form, action) {
					    	oseMsc.formSuccess(form, action)
					    	addonMscMembershipGridStore.reload();
							addonMscMembershipGrid.getView().refresh();
					    }
					    ,failure: function(form, action) {
					    	oseMsc.formFailure(form, action)
					    }
					})
				}
			}]
		}]
	});
	oseMscAddon.msc = new Ext.Panel({
		anchor: '95%'
		,height: 350
		,border: false
		,bodyStyle: 'padding: 0px'
		,items:[{
			xtype: 'fieldset'
			,title: Joomla.JText._('Memberships')
			,items:[
				addonMscMembershipGrid
			]
		}]
		,listeners: {
			render: function(p){
				addonMscMembershipGridStore.load();
			}
		}
	});