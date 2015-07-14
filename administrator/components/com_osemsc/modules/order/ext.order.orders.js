Ext.ns('oseMsc','oseMscOrders');
oseMscOrders.gridInit = function()	{ }
oseMscOrders.gridInit.prototype = {
		init: function()	{
			oseMsc.msg = new Ext.App();
			oseMscOrders.store = new Ext.data.Store({
				  proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=orders',
			            method: 'POST'
			      })
			      ,baseParams:{task: "getOrders",limit: 25}
				  ,reader: new Ext.data.JsonReader({
				    root: 'results'
				    ,totalProperty: 'total'
				    ,idProperty: 'order_id'
				  },[
				    {name: 'order_id', type: 'int', mapping: 'order_id'}
				    ,{name: 'user_id', type: 'int', mapping: 'user_id'}
				    ,{name: 'create_date', type: 'string', mapping: 'create_date'}
				    ,{name: 'title', type: 'string', mapping: 'title'}
				    ,{name: 'mscTitle', type: 'string', mapping: 'mscTitle'}
				    ,{name: 'name', type: 'string', mapping: 'name'}
				    ,{name: 'payment_price', type: 'string', mapping: 'payment_price'}
				    ,{name: 'payment_currency', type: 'string', mapping: 'payment_currency'}
				    ,{name: 'order_status', type: 'string', mapping: 'order_status'}
				    ,{name: 'payment_serial_number', type: 'string', mapping: 'payment_serial_number'}
				    ,{name: 'payment_method', type: 'string', mapping: 'payment_method'}
				    ,{name: 'payment_mode', type: 'string', mapping: 'payment_mode'}
				    ,{name: 'params', type: 'string', mapping: 'params'}
				  ])
				  ,autoLoad:{}
				  ,listeners: {
					  beforeload: function(store,records,options)	{
					  	var status = oseMscOrders.tbar.filter_status.getValue();
					  	store.setBaseParam('filter_status',status);
				  	}
				  }
			});
			oseMscOrders.tbar = new Ext.Toolbar({
			    items: [{
		    		ref:'confirmBtn'
		            ,iconCls: 'icon-user-add'
		            ,text: Joomla.JText._('Confirm')
		            ,handler: function(){
		            	var node = oseMscOrders.grid.getSelectionModel().getSelected();
		            	if(!updateOrderWin)	{
		        			var updateOrderWin = new Ext.Window({
		        				width: 500
		        				,title: Joomla.JText._('Review')
		        				,modal: true
		        				,items:[{
		        					border: false
		        					,height: 450
		        					,autoLoad: {
		        						url: 'index.php?option=com_osemsc&controller=orders'
		        						,params: {task:'getOrderMemInfo',order_id:node.id}
		        					}
		        					,buttons: [{
		        						text: Joomla.JText._('Confirm')
		        						,handler: function()	{
		        							updateOrderWin.close();
											Ext.Ajax.request({
							            		url:'index.php?option=com_osemsc&controller=orders'
							            		,params:{task:'confirmOrder',order_id:node.id}
							            		,success: function(response,opt)	{
							            			var msg = Ext.decode(response.responseText);
							            			oseMsc.msg.setAlert(msg.title,msg.content);

							            			if(msg.success && Ext.isBoolean(msg.success))	{
							            				oseMscOrders.grid.getStore().reload();
							            				oseMscOrders.grid.getView().refresh();
							            			}
							            		}
							            	})
		        						}
		        						,scope: this
		        					},{
		        						text: Joomla.JText._('Cancel')
		                				,handler: function()	{
		                					updateOrderWin.close();
		                				}
		                			}]
		        				}]
				    
		        			});
		        		}
		            	updateOrderWin.show().alignTo(Ext.getBody(),'c-c')
		            }
		        },{
		    		ref:'pendingBtn'
		            ,iconCls: 'icon-user-add'
		            ,text: Joomla.JText._('Pending')
		            ,handler: function(){
		            	var node = oseMscOrders.grid.getSelectionModel().getSelected();

		            	Ext.Ajax.request({
		            		url:'index.php?option=com_osemsc&controller=orders'
		            		,params:{task:'pendingOrder',order_id:node.id}
		            		,success: function(response,opt)	{
		            			var msg = Ext.decode(response.responseText);
		            			oseMsc.msg.setAlert(msg.title,msg.content);

		            			if(msg.success && Ext.isBoolean(msg.success))	{
		            				oseMscOrders.grid.getStore().reload();
		            				oseMscOrders.grid.getView().refresh();
		            			}
		            		}
		            	})
		            }
		        },{
		    		ref:'createBtn'
		            ,iconCls: 'icon-user-add'
		            ,text: Joomla.JText._('Create_Order')
		            ,handler: function(){
		               	var node = oseMscOrders.grid.getSelectionModel().getSelected();
		                if(!createOrderWin)	{
		            		var createOrderWin = new Ext.Window({
		            			width: 550
		            			,title: Joomla.JText._('Create_Order')
		            			,modal: true
		            			,labelWidth:150
		            			,items:[{
		        					xtype: 'form'
		        					,ref: 'form'
		        					,height: 300
		        					,labelWidth: 200
		        					,border: false
		        					,defaults: {border: false,msgTarget : 'side'}
		        					,buttons: [{
		        						text: Joomla.JText._('Ok')
		        						,handler: function()	{
		        							if(createOrderWin.form.user_id.getValue() != '')
		        							{
		        								Ext.Msg.wait(Joomla.JText._('Creating'),Joomla.JText._('Please_wait'))
		        								createOrderWin.form.getForm().submit({
		            								url: 'index.php?option=com_osemsc&controller=orders',
		            								//waitMsg: 'Creating...',
		            								timeout: 6000000,
		            								success: function(form,action){
		            									Ext.Msg.hide()
		            									var msg = action.result;
		            									oseMsc.msg.setAlert(msg.title,msg.content);
		            									createOrderWin.close();
		            									oseMscOrders.grid.getStore().reload();
		            		            				oseMscOrders.grid.getView().refresh();
		            								},
		            							    params: { 'task':'createOrder'}
		            							});
		        							}else{
		        								oseMsc.msg.setAlert(Joomla.JText._('Error'),Joomla.JText._('Please_select_a_member_first'));
		        							}	
		        							
		        						}
		       						},{
		       							text: Joomla.JText._('Cancel')
		       							,handler: function()	{
		       								createOrderWin.close();
		       							}
		       						}]
		       						,items:[{
		                   				xtype:'combo'
		                   				,id:'msc-id'
		                       			,fieldLabel: Joomla.JText._('Membership_List')	
		                       		    ,hiddenName: 'msc_id'
		                       			,typeAhead: true
		                       			,triggerAction: 'all'
		                       			,lazyRender:true
		                       			,width: 280
		                       			,allowBlank:false
		                       			,listWidth: 350
		                        		,lastQuery:''
		                   			    ,mode: 'remote'
		                   			    ,forceSelection: true
		                   			    ,store: new Ext.data.Store({
		                   			  		proxy: new Ext.data.HttpProxy({
		                   		            	url: 'index.php?option=com_osemsc&controller=coupons'
		                   			            ,method: 'POST'
		                   			  		})
		                   				 	,baseParams:{task: "getMscList"}
		                   				 	,reader: new Ext.data.JsonReader({
		                   				 		root: 'results'
		                   				 		,totalProperty: 'total'
		                   				 	},[
		                   				 	   {name: 'id', type: 'int', mapping: 'id'}
		                   				 	   ,{name: 'title', type: 'string', mapping: 'title'}
		                				  ])
		                        		  ,autoLoad:{}
		                   				  ,listeners:{
		         					  		load: function(s,r){
		         					  			var msc_id = s.getAt(0).get('id');
		         					  			createOrderWin.form.findById('msc-id').setValue(msc_id);
		         					  		}
		                   				 }
		                        		  ,sortInfo:{field: 'id', direction: "ASC"}
		                        		})
		                        	    ,valueField: 'id'
		                        		,displayField: 'title'
		                        		,listeners:{	
		                        			select: function(c,r,i){
		                        				var option = createOrderWin.form.msc_option
		                        				option.getStore().filter([{
		                        					fn   : function(record) {
		                        						return record.get('msc_id') == c.getValue()
		                        					},
		                        					scope: this
		                        				}]);
		                        				if(option.getStore().getCount() > 0)	{
		                        					option.setValue(option.getStore().getAt(0).get('id'))
		                        				}else{
		                        					option.setValue('');
		                        				}
		                        			}
		                        		}
		                        	 },{
		                        		 xtype:'combo'
		                        		 ,width: 280
		                        		 ,allowBlank:false
		                        		 ,ref: 'msc_option'
		                        		 ,fieldLabel: Joomla.JText._('Membership_Option')
		                        		 ,hiddenName: 'msc_option'
		                        		 ,typeAhead: true
		                        		 ,triggerAction: 'all'
		                        		 ,lazyRender:true
		                        		 ,mode: 'remote'
		                        	     ,store: new Ext.data.Store({
		                       		  		proxy: new Ext.data.HttpProxy({
		                       		           	url: 'index.php?option=com_osemsc&controller=orders',
		                       		            method: 'POST'
		                       		  	 	})
		                       				,baseParams:{task: 'getAllOptions'}
		                       				,reader: new Ext.data.JsonReader({
		                       				   	root: 'results',
		                       				    totalProperty: 'total'
		                       				},[
		                       				    {name: 'oid', type: 'string', mapping: 'id'},
		                       				    {name: 'msc_id', type: 'string', mapping: 'msc_id'},
		                       				    {name: 'title', type: 'string', mapping: 'title'}
		                       				])
		                       			  	,sortInfo:{field: 'oid', direction: "ASC"}
		                       				//,autoLoad:{}
		                       				,listeners: {
		                       					load: function(s)	{
		                       						var msc_id = createOrderWin.form.findById('msc-id').getValue();
		                       						s.filter([{
		                            					fn   : function(record) {
		                            						return record.get('msc_id') == msc_id
		                            					},
		                            					scope: this
		                            				}]);
		                       						
		                       				  	}
		                        		    }
		                        				    
		                        		})
		                        		,valueField: 'oid'
		                        		,displayField: 'title'	 
		                        	 },{
		                        		 xtype:'button'
		                        		,fieldLabel: Joomla.JText._('Choose_a_member')	 
		                        		,text: Joomla.JText._('Select')
		                        		,handler: function(){
		                        			
		                        			var memStore = new Ext.data.Store({
			                      		  		  proxy: new Ext.data.HttpProxy({
			                  			            url: 'index.php?option=com_osemsc&controller=orders',
			                  			            method: 'POST'
			                  			        }),
			                  				  baseParams:{task: "getUsers",limit: 20}, 
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
		                        			
		                        			var memGrid = new Ext.grid.GridPanel({
		                        		        store:memStore
		                        		        ,height: 500
		                        		     	,viewConfig:{forceFit:true}
		                        		     	,colModel: new Ext.grid.ColumnModel({
		                        				    defaults: {
		                        				        width: 200,
		                        				        sortable: true
		                        				    },
		                        				    columns: [
		                        				        //oseMemMsc.addParams.sm,
		                        				        new Ext.grid.RowNumberer({header:'#'}),
		                        					    {id: 'uname', header: 'User Name', dataIndex: 'Uname'},
		                        					    {id: 'name', header: 'Name', dataIndex: 'Name'},
		                        					    {id: 'email', header: 'Email', dataIndex: 'Email'},
		                        					    {id: 'id', header: 'ID', dataIndex: 'ID', width: 50}
		                        				    ]
		                        				})
		                        		     	
		                        		     	,sm: new Ext.grid.RowSelectionModel({
		                        					singleSelect: true
		                        				})
		                        		     	
		                        		     	,tbar: new Ext.Toolbar({
		                        		     		 items: [{
		                        		     			text: Joomla.JText._('Select')
		                        		     			,handler: function(btn,event){
		                        		     				var s = memGrid.getSelectionModel().getSelections();
		                        		     				if(s.length <= 0)
		                        		     				{
		                        		     					oseMsc.msg.setAlert(Joomla.JText._('Error'),Joomla.JText._('Please_select_a_member_first'));
		                        		     				}else{
		                        		     					var record = s[0];
		                        		     					createOrderWin.form.uname.setVisible(true);
		                        		     					createOrderWin.form.uname.setValue(record.get('Uname'));
		                        		     					createOrderWin.form.email.setVisible(true);
		                        		     					createOrderWin.form.email.setValue(record.get('Email'));
		                           		     					createOrderWin.form.user_id.setValue(record.id);
		                           		     					addMemWin.close();
		                        		     				}	
		                        		     			}
		                        		     		 },'->'
		                        		 	        ,Joomla.JText._('You_can_search_by_name_user_name_and_email')
		                        			        ,new Ext.ux.form.SearchField({
		                        		                store: memStore,
		                        		                paramName: 'search',
		                        		                width:150
		                        		            })]
		                        		     	})   
		                        		     	,bbar:new Ext.PagingToolbar({
		                        		    		pageSize: 20,
		                        		    		store: memStore,
		                        		    		displayInfo: true,
		                        		    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
		                        					emptyMsg: Joomla.JText._("No_topics_to_display")
		                        			    })
		                        		    });
		                        			
		                        			 if(!addMemWin)	{
		                                 		var addMemWin = new Ext.Window({
		                                 			title: Joomla.JText._('Choose_a_member')
		                        		        	,width: 800
		                        		        	,autoHeight: true
		                        		        	
		                                 		});
		                                	 }
		                        			 addMemWin.add(eval(memGrid));
		                        			 addMemWin.doLayout();
		                        			 addMemWin.show().alignTo(Ext.getBody(),'t-t')
		                        		}
		                        	 },{
		                        		 xtype:'displayfield'
		                        		,ref:'uname'	 
		                        	    ,fieldLabel: Joomla.JText._('User_name')
		                        	    ,hidden:true	
		                        	 },{
		                        		 xtype:'displayfield'
		                             	,ref:'email'	 
		                             	,fieldLabel: Joomla.JText._('Email')
		                             	,hidden:true		
		                             },{
		                        		 xtype:'hidden'
		                          		,ref:'user_id'	 
		                        		,name:'user_id'	 
		                        	 }]
		        				}]
		   		    
		           			});
		           		}
		                	createOrderWin.show().alignTo(Ext.getBody(),'c-c')
		               }
		           },{
		    		ref:'truncateBtn'
		            ,iconCls: 'icon-user-add'
		            ,text: Joomla.JText._('Clear_Order_Data')
		            ,handler: function(){
		               	Ext.Msg.confirm(Joomla.JText._('Notice'),Joomla.JText._('Please_confirm_that_you_would_like_to_clear_ALL_order_data_Please_note_that_all_order_information_will_be_removed_and_this_action_can_not_be_reverted_and_all_of_your_Auto_Renewing_membership_will_be_affected_Please_do_it_with_care_OSE_will_not_be_responsible_for_any_issues_causing_by_this_action'),function(btn,txt)	{
		               		if(btn == 'yes')	{
			               		Ext.Ajax.request({
				               		url:'index.php?option=com_osemsc&controller=orders'
				               		,params:{task:'Truncate'}
				               		,success: function(response,opt)	{
				                			var msg = Ext.decode(response.responseText);
				                			oseMsc.msg.setAlert(msg.title,msg.content);
				                			if(msg.success && Ext.isBoolean(msg.success))	{
				              				oseMscOrders.grid.getStore().reload();
				               				oseMscOrders.grid.getView().refresh();
				               			}
				               		}
				               	})
		               		}
		               	});
		             }
		        },'->'
		        , Joomla.JText._('Status'),{
		        	ref:'filter_status',
		        	xtype:'combo',
		            hiddenName: 'filter_status',
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
				        	['', Joomla.JText._('All')]
				        	,['pending', Joomla.JText._('Pending')]
				        	,['expired', Joomla.JText._('Expired')]
				        	,['confirmed',Joomla.JText._('Confirmed')]
				        ]
				    }),
				    valueField: 'value',
				    displayField: 'text',
				    listeners: {
				        select: function(c,r,i)	{
			    			oseMscOrders.store.reload({
			    				params:{filter_status:r.data.value}
			    			});
			    		}
			    		,render: function(c)	{
			    			c.setValue('');
			    		}
			        }
		        }
		        , '-',Joomla.JText._('Search_Order')
		        ,new Ext.ux.form.SearchField({
		            store: oseMscOrders.store,
		            paramName: 'search_order',
		            width:150
		            ,listeners:{
		            	render: function(b)	{
		    				Ext.QuickTips.register({
							    target: b.getEl(),
							    anchor: 'right',
							    text: Joomla.JText._('Search_by_order_id_or_referred_ID'),
							    width: 250
							});
		    			}
		            }
		        }),'-',Joomla.JText._('Search_User')
		        ,new Ext.ux.form.SearchField({
		            store: oseMscOrders.store,
		            paramName: 'search_user',
		            width:150
		            ,listeners:{
		            	render: function(b)	{
		    				Ext.QuickTips.register({
							    target: b.getEl(),
							    anchor: 'right',
							    text: Joomla.JText._('Search_by_username_or_name'),
							    width: 250
							});
		    			}
		            }
		        })]
			});


			oseMscOrders.grid = new Ext.grid.GridPanel({
				store: oseMscOrders.store
				,title: Joomla.JText._('Order_List')
				,colModel: new Ext.grid.ColumnModel({
			        defaults: {
			            width: 130,
			            sortable: false
			        },
			        columns: [
			            {id: 'id', header: Joomla.JText._('ID'),  hidden: true, dataIndex: 'order_id'}
			            ,new Ext.grid.RowNumberer({header:'#'})
			            ,{header: Joomla.JText._('Date'), dataIndex: 'create_date', sortable: true,id:'date'}
			            ,{header: Joomla.JText._('Title'), dataIndex: 'title', sortable: true,id:'title'}
			            ,{header: Joomla.JText._('Reference_ID'), dataIndex: 'payment_serial_number',width: 200}
			            ,{header: Joomla.JText._('User_s_name'), dataIndex: 'name',width: 150}
			            //,{header: 'Membership', dataIndex: 'mscTitle', sortable: true,width: 150}
			            ,{header: Joomla.JText._('Price'), dataIndex: 'payment_price',width: 80, align: 'center'}
			            ,{header: Joomla.JText._('Currency'), dataIndex: 'payment_currency',width: 80, align: 'center'}
			            ,{header: Joomla.JText._('Order_Status'), dataIndex: 'order_status',width: 90}
			            ,{header: Joomla.JText._('Payment_Method'), dataIndex: 'payment_method'}
			            ,{
			            	header: Joomla.JText._('Payment_Mode'), dataIndex: 'payment_mode'
			            	,renderer: function(val)	{
					    		if(val == 'm')	{
					    			return Joomla.JText._('Manual_Renewing');
					    		}	else	{
					    			return Joomla.JText._('Automatic_Renewing');
					    		}
					    	}
			            }
			        ]
			    })
			    ,autoExpandColumn: 'title'
			    ,sm: new Ext.grid.RowSelectionModel({singleSelect:true})
			    ,tbar: oseMscOrders.tbar
			    ,bbar: new Ext.PagingToolbar({
		    		pageSize: 20
		    		,store: oseMscOrders.store
		    		,displayInfo: true
		    		,displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}'
					,emptyMsg: Joomla.JText._("No_topics_to_display")
				    ,plugins: new Ext.ux.grid.limit({})
			    })
			    ,height: 820
			});
		}
}	