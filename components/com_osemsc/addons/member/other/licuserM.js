Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();
	
	var addonMemberLicuser_Store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	      	url: 'index.php?option=com_osemsc&controller=member'
	      	,method: 'POST'
	      })
		  ,baseParams:{task: "action",action:'member.licuser.getStaffs',limit: 20}
		  ,reader: new Ext.data.JsonReader({   
		    root: 'results'
		    ,totalProperty: 'total'
		    ,idProperty: 'user_id'
		  },[ 
		    {name: 'user_id', type: 'int', mapping: 'user_id'}
		    ,{name: 'username', type: 'string', mapping: 'username'}
		    ,{name: 'firstname', type: 'string', mapping: 'firstname'}
		    ,{name: 'lastname', type: 'string', mapping: 'lastname'}
		    ,{name: 'user_type', type: 'string', mapping: 'group_name'}
		    ,{name: 'user_status', type: 'string', mapping: 'user_status'}
		  ])
		 ,sortInfo:{field: 'user_id', direction: "ASC"}
		 
	})
	
	var addonMemberLicuser_Reader = new Ext.data.JsonReader({   
	    root: 'result',
	    totalProperty: 'total',
	    idProperty: 'user_id',
	    fields:[ 
		    {name: 'user_id', type: 'int', mapping: 'user_id'},
		    {name: 'username', type: 'string', mapping: 'username'},
		    {name: 'firstname', type: 'string', mapping: 'firstname'},
		    {name: 'lastname', type: 'string', mapping: 'lastname'},
		    {name: 'user_type', type: 'string', mapping: 'group_id'},
		    {name: 'user_status', type: 'string', mapping: 'user_status'},
		    {name: 'email', type: 'string', mapping: 'email'},
		    {name: 'primary_contact', type: 'string', mapping: 'primary_contact'}
	  	]
  	});
	
	oseMscAddon.uniqueUserName= {checked:false};
	Ext.apply(Ext.form.VTypes,{
		uniqueUserName: function(val,field)	{
			if(!oseMscAddon.uniqueUserName.checked)	{
				
				Ext.Ajax.request({
	        		url: 'index.php?option=com_osemsc&controller=member'
	        		,params: {
	        			task : 'action',action:'member.licuser.formValidate' 
	        			,username : val
	        			,user_id: Ext.getCmp('user_id').getValue() 
	        		}
	        		,success: function(response, opt)	{
	        			var msg = Ext.decode(response.responseText);
	        			
	        			if(!Ext.isBoolean(msg.result))	{
	        				//alert('haha, i test')//addonMemberLicuserForm.form.getForm().findField('username').setActiveError(msg.result);
	        				field.markInvalid(msg.result);
	        			}
	        			oseMscAddon.uniqueUserName =  msg;
	        			oseMscAddon.uniqueUserName.checked = true;
	        			
	        			return field.validate();
	        		}
	        	});
			}	else	{
				oseMscAddon.uniqueUserName.checked = false;
				if(!Ext.isBoolean(oseMscAddon.uniqueUserName.result))	{
    				return false;
    			}	else	{
    				return true;
    			}	
    			
			}
			return true;
		}
		,uniqueUserNameText: 'This username has been registered by other user.'
	})
	
	var addonMemberLicuserForm = {
		xtype: 'form',
		ref: 'form',
		reader: addonMemberLicuser_Reader,
		bodyStyle: 'padding : 10px',
		defaults: {xtype: 'textfield', width: 200,msgTarget: 'side'}
		,labelWidth: 150
		
		,items:[{
			fieldLabel: 'User Name'
            ,name: "username"
            ,allowBlank:false
            ,validationEvent : 'blur' 
            ,vtype: 'uniqueUserName'
            /*,validator: function(val)	{
            	Ext.Ajax.request({
            		url: 'index.php?option=com_osemsc&controller=register'
            		,params: {
            			task : 'uniqueUserName' 
            			,username : val
            			,user_id: Ext.getCmp('user_id').getValue() 
            		}
            		,success: function(response, opt)	{
            			var msg = Ext.decode(response.responseText);
            			oseMscAddon.uniqueUserName =  msg.result;
            			
            			if(!Ext.isBoolean(msg.result))	{
            				//alert('haha, i test')//addonMemberLicuserForm.form.getForm().findField('username').setActiveError(msg.result);
            				//addonMemberLicuserWin.form.getForm().markInvalid({'username': msg.result});
            			}
            			//oseMscAddon.uniqueUserName =  msg.result;
            			
            			//alert('1');
            		}
            	});
            	//return oseMscAddon.uniqueUserName;
            	//alert('2');
            }*/
		},{
			xtype: 'hidden'
			,name: 'user_id'
			,id: 'user_id'
			,value: ''
		},{
			fieldLabel: 'First Name',
			name: 'firstname'
		},{
			fieldLabel: 'Last Name',
			name: 'lastname'
		},{
			fieldLabel: 'Email Address',
			name: 'email',
			vtype: 'email'
		},{
			fieldLabel: 'User Type',
			xtype: 'combo',
			hiddenName: 'user_type',
			triggerAction: 'all',
    		lazyRender:true,
			mode: 'local',
	    	store: new Ext.data.ArrayStore({
		        id: 0,
		        fields: [
		            'type',
		            'displayText'
		        ],
		        data: [['3', 'Standard'], ['2', 'Admin']]
		    }),
		    
		    valueField: 'type',
    		displayField: 'displayText',
    		
    		listeners:{
    			afterrender: function(e)	{
    				e.setValue('3');
    			}
    		}
		},{
			fieldLabel: 'User Status',
			xtype: 'combo',
			hiddenName: 'user_status',
			triggerAction: 'all',
    		lazyRender:true,
			mode: 'local',
	    	store: new Ext.data.ArrayStore({
		        id: 0,
		        fields: [
		            'type',
		            'displayText'
		        ],
		        //data: [[0, 'Active'], [1, 'Inactive']]
		    }),
		    
		    valueField: 'type',
    		displayField: 'displayText',
    		
    		listeners:{
    			load: function(s,r,i)	{
    				e.setValue(0);
    				//alert('dd')
    			}
    		}
		},{
			fieldLabel: 'Primary Contact',
			xtype: 'hidden',
			name: 'primary_contact',
			checked: true,
			inputValue: 0
		}]
	}
	
	var addonMemberLicuser = new Ext.grid.GridPanel({
		border: false
		,viewConfig: {forceFit: true}
		,height: 500
		,store: addonMemberLicuser_Store
		,cm: new Ext.grid.ColumnModel({
	        columns: [
		        new Ext.grid.RowNumberer({header:'#'}),
			    {id: 'id', header: 'ID', dataIndex: 'user_id', hidden: true,hideable:true},
			    {id: 'username', header: 'Username', dataIndex: 'username'},
			    {id: 'firstname', header: 'First Name', dataIndex: 'firstname'},
			    {id: 'lastname', header: 'Last Name', dataIndex: 'lastname'},
			    {id: 'user_type', header: 'User Type', dataIndex: 'user_type'},
			    {
			    	id: 'status', header: 'Status', dataIndex: 'user_status',
			    	renderer: function(val)	{
			    		if(val == 0)	{
			    			return 'Active';
			    		}	else	{
			    			return 'Inactive';
			    		}
			    	}
			    }
		    ]
		})
		
		,sm:new Ext.grid.RowSelectionModel({
			singleSelect:true,
			listeners:{
				selectionchange: function(sm,node){
					addonMemberLicuser.getTopToolbar().editBtn.setDisabled(sm.getCount() != 1); // >
					addonMemberLicuser.getTopToolbar().removeBtn.setDisabled(sm.getCount() != 1); // >
				}
			}
		})
		
		,bbar: new Ext.PagingToolbar({
    		pageSize: 20,
    		store: addonMemberLicuser_Store,
    		displayInfo: true,
		    displayMsg: 'Displaying topics {0} - {1} of {2}',
		    emptyMsg: "No topics to display"
	    })
		
		,tbar:new Ext.Toolbar({
			items:['->',{
				text:'Add',
				ref: 'addBtn',
				handler: function(){
					if(!addonMemberLicuserWin)	{
						var addonMemberLicuserWin = new Ext.Window({
							title: 'User Information',
							width: 500,
							items:[
								addonMemberLicuserForm
							],
							modal: true,
							buttons: [{
								text: 'save',
								handler: function()	{
									
									addonMemberLicuserWin.getEl().mask('Loading...');
									var node = addonMemberLicuser.getSelectionModel().getSelected();
									addonMemberLicuserWin.form.getForm().submit({
										clientValidation: true,
										url: 'index.php?option=com_osemsc&controller=member',
										params:{task:'action',action:'member.licuser.save'},
										success: function(form, action)	{
											var msg = action.result;
											
											addonMemberLicuserWin.form.getForm().findField('user_id').setValue(msg.user_id);
											
											oseMscAddon.msg.setAlert(msg.title,msg.content);
											
											addonMemberLicuser.getStore().reload();
											addonMemberLicuser.getView().refresh();
											
											addonMemberLicuserWin.getEl().unmask();
										}
										,failure: function(form, action)	{
											if(action.result.script)	{
												eval('addonMemberLicuserWin.form.getForm().findField'+action.result.script);
											}	else	{
												oseMsc.formFailureMB(form, action)
											}
											addonMemberLicuserWin.getEl().unmask();
										}
									});
									
								}
							}]
							,listeners: {
								show:function(w)	{
									w.form.getForm().findField('user_status').getStore().loadData([[0, 'Active']]);
									w.form.getForm().findField('user_status').setValue(0);
								}
							}
						})
					}
					
					
					//addonMemberLicuserWin.form.getForm().findField('user_status').getStore().loadData([[0, 'Active']]);
					addonMemberLicuserWin.show(this).alignTo(Ext.getBody(),'t-t');
				}
			},{
				text:'Edit',
				ref: 'editBtn',
				disabled : true,
				handler: function(){
					if(!addonMemberLicuserWin)	{
						var addonMemberLicuserWin = new Ext.Window({
							title: 'User Information',
							width: 500,
							items:[
								addonMemberLicuserForm
							],
							modal: true,
							buttons: [{
								text: 'save',
								handler: function()	{
									//addonMemberLicuserWin.getEl().mask('Loading...');
									var node = addonMemberLicuser.getSelectionModel().getSelected();
									addonMemberLicuserWin.form.getForm().submit({
										url: 'index.php?option=com_osemsc&controller=member'
										,params:{task:'action',action:'member.licuser.save'}
										,success: function(form, action)	{
											var msg = action.result;
											oseMscAddon.msg.setAlert(msg.title,msg.content);
											
											addonMemberLicuser.getStore().reload();
											addonMemberLicuser.getView().refresh();
											addonMemberLicuserWin.getEl().unmask();
										}
										,failure: function(form, action)	{
											if(action.result.script)	{
												eval('addonMemberLicuserWin.form.getForm().findField'+action.result.script);
											}	else	{
												oseMsc.formFailureMB(form, action)
											}
											addonMemberLicuserWin.getEl().unmask();
										}
									});
								}
							}],
							
							listeners:{
								show: function(w){
									var node = addonMemberLicuser.getSelectionModel().getSelected();
									w.form.getForm().findField('user_status').getStore().loadData([[0, 'Active'],[1, 'Inactive']]);
									w.form.getForm().findField('user_status').setValue(0);
									w.form.getForm().load({
										waitMsg: 'Loading...',
										url: 'index.php?option=com_osemsc&controller=member',
										params:{task:'action',action:'member.licuser.getItem',user_id:node.id}
									})
								}
							}
						})
					}
					
					
					addonMemberLicuserWin.show(this).alignTo(Ext.getBody(),'t-t');
				}
			},{
				text: 'Remove',
				ref: 'removeBtn',
				disabled : true,
				handler: function()	{
					var node = addonMemberLicuser.getSelectionModel().getSelected();
									
					Ext.Ajax.request({
						url: 'index.php?option=com_osemsc&controller=member',
						params:{task:'action',action:'member.licuser.remove',member_id:node.id},
						success: function(response,opt)	{
							var msg = Ext.decode(response.responseText);
							oseMscAddon.msg.setAlert(msg.title,msg.content);
							
							if(msg.success)	{
								addonMemberLicuser.getStore().remove(node);
							}
							
						}
					})
				}
			}]
		})
	});
	
	oseMscAddon.licuser = new Ext.Panel({
		autoHeight: true,
		items:[
			addonMemberLicuser
		],
		listeners: {
			render: function(p)	{
				addonMemberLicuser_Store.load();
			}
		}
	});
	
	