Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();
	
	var addonMemberDirectoryInfo_reader = new Ext.data.JsonReader({   
	    root: 'result',
	    totalProperty: 'total',
	    idProperty: 'directory_id',
	    fields:[ 
	    	{name: 'directory.directory_id', type: 'string', mapping: 'directory_id'},
		    {name: 'directory.directory_name', type: 'string', mapping: 'directory_name'},
		    {name: 'directory.directory_website', type: 'string', mapping: 'directory_website'},
		    {name: 'directory.directory_description', type: 'string', mapping: 'directory_description'},
		    {name: 'directory.directory_logo', type: 'string', mapping: 'directory_logo'}
	  	]
  	});
  	
	var addonMemberDirectoryInfo = new Ext.form.FormPanel({
		border: false
		,height: 390
		,width: 550
		,fileUpload: true
		,bodyStyle: 'margin: 10px'
		,labelWidth: 150
		
		,items:[{
			name: 'directory.directory_id',
			xtype: 'hidden'
		},{
			fieldLabel: 'Company Name',
			name: 'directory.directory_name',
			xtype: 'textfield'
		},{
			fieldLabel: 'Website Address',
			name: 'directory.directory_website',
			xtype: 'textfield'
		},{
			fieldLabel: 'Business Description',
			name: 'directory.directory_description',
			xtype: 'textarea',
            width: 250
		},{
			itemId:'logo',
			fieldLabel: 'Company Logo',
			border: false,
			layout: 'hbox',
			items:[{
				//id: 'directoryLogoBtn',
				ref:'image',
				xtype:'fileuploadfield',
                name: 'directory.directory_logo',
                buttonOnly: true,
                id: 'image',
                //width: 200,
                //anchor:'85%',
	            //emptyText: 'Select an image',
	            buttonText: 'Upload File',
	            buttonCfg: {
	            //   iconCls: 'upload-icon'
	            	width: 100
	            },
	            listeners: {
	            	fileselected: function(fb, v){
	            		
	            		addonMemberDirectoryInfo.getForm().submit({
	            			url:'index.php?option=com_osemsc&controller=member',
	            			params:{task:'action',action:'member.directory.preview'},
	            			success:function(form,action)	{
	            				var msg = action.result;
	            				if(msg.uploaded){
	            					Ext.getCmp('preview').getEl().set({'src':msg.img_path});
	            				}	else	{
	            					Ext.Msg.alert(msg.title,msg.content);
	            				}
	            			},
	            			failure:function(form,action){
	            				var msg = action.result;
	            				Ext.Msg.alert('Error','Failed!');
	            			}
	            		});
	            	}
	            }
			},{
				xtype: 'button',
				text: 'reset',
				width: 100,
				handler: function(b,e){
					b.previousSibling().reset();
					//Ext.get('preview').update('');
					
				}
			}]
		},{
			border: false
			//,height: 150
			,id:'preview'
			,fieldLabel: ''
			,xtype:'box'
		    ,frame:false
			,autoEl:{
				tag:'img'
				,src:''
			}
		}]
		
		,reader: addonMemberDirectoryInfo_reader
		
		,buttons: [{
			text: 'save',
			handler: function(){
				addonMemberDirectoryInfo.getForm().submit({
					url:'index.php?option=com_osemsc&controller=member',
					params:{task:'action',action:'member.directory.updateDirectoryInfo'},
					success: function(fp, o)	{
						var msg = o.result;
						oseMscAddon.msg.setAlert(msg.title,msg.content);
					},
					failure: function(fp, o){
						var msg = o.result;
						oseMscAddon.msg.setAlert(msg.title,msg.content);
					}
				});
			}
		}]
	});
	
	var addonMemberDirectoryPreview = new Ext.Panel({
		border: false
		,height: 150
		,bodyStyle: 'margin: 10px'
		,width: 150
		,items:[{
			border: false
			//,height: 150
			,id:'preview'
			,xtype:'box'
		    ,frame:false
			,autoEl:{
				tag:'img'
				,src:''
			}
		}]
	});
	
	var addonMemberDirectoryLocation_Store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=member',
	            method: 'POST'
	      }),
		  baseParams:{task: "action",action:"member.directory.getLocations",limit: 20}, 
		  reader: new Ext.data.JsonReader({   
		              // we tell the datastore where to get his data from
		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'location_id'
		  },[ 
		    {name: 'id', type: 'int', mapping: 'location_id'},
		    {name: 'address', type: 'string', mapping: 'address'},
		    {name: 'city', type: 'string', mapping: 'city'},
		    {name: 'state', type: 'string', mapping: 'state'},
		    {name: 'country', type: 'string', mapping: 'country'},
		    {name: 'postcode', type: 'string', mapping: 'postcode'},
		    {name: 'contact_title', type: 'string', mapping: 'contact_title'},
		    {name: 'contact_name', type: 'string', mapping: 'contact_name'},
		    {name: 'contact_email', type: 'string', mapping: 'contact_email'}
		    
		  ]),
		  sortInfo:{field: 'id', direction: "ASC"}
		  //autoLoad:{},
	});
	
	
	
	var addonMemberDirectoryLocationForm = {
		xtype: 'form'
		,ref: 'form'
		,labelWidth: 200
		,defaults: {xtype: 'textfield', width: 300, msgTarget: 'side'}
		,items:[{
			xtype:'hidden',
            name: 'location_id'
        },{
            fieldLabel: 'Address1',
            name: 'directory.addr1'
        },{
            fieldLabel: 'Address2',
            name: 'directory.addr2'
        },{
            fieldLabel: 'City',
            name: 'directory.city'
        },{
            fieldLabel: 'State/Province',
            name: 'directory.state'
        },{
            fieldLabel: 'Country'
            ,name: 'directory.country'
            ,id: 'directory_country'
            ,xtype: 'combo'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    //,listClass: 'combo-left'
		    ,lastQuery: ''
		    ,mode: 'remote'
		    ,allowBlank: false
		    ,lastQuery: ''
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=register',
		            method: 'POST'
	      		})
			  	,baseParams:{task: "getCountry"}
			  	,reader: new Ext.data.JsonReader({   
			    	root: 'results',
			    	totalProperty: 'total'
			    	,idProperty: 'country_id'
			  	},[ 
			    {name: 'code', type: 'string', mapping: 'country_name'},
			    {name: 'cname', type: 'string', mapping: 'country_name'}
			  	])
			  	,listeners:{
			  		load: function(s,r,i)	{
			  			
			  		}
			  	}
			  	,autoLoad: {}
			})
			
		    ,valueField: 'code'
		    ,displayField: 'cname'
        },{
            fieldLabel: 'Zip/Postal Code',
            name: 'directory.postcode'
        },{
            fieldLabel: 'Phone',
            name: 'directory.telephone'
        },{
            fieldLabel: 'Location Contact Name',
            name: 'directory.contact_name'
        },{
            fieldLabel: 'Location Contact Title',
            name: 'directory.contact_title'
        },{
            fieldLabel: 'Location Contact Email'
            ,name: 'directory.contact_email'
            ,vtype: 'email'
        }],
	    reader:new Ext.data.JsonReader({   
		    root: 'result',
		    totalProperty: 'total',
		    idProperty: 'location_id',
		    fields:[ 
			    {name: 'location_id', type: 'string', mapping: 'location_id'},
			    {name: 'directory.addr1', type: 'string', mapping: 'location_addr1'},
			    {name: 'directory.addr2', type: 'string', mapping: 'location_addr2'},
			    {name: 'directory.city', type: 'string', mapping: 'location_city'},
			    {name: 'directory.state', type: 'string', mapping: 'location_state'},
			    {name: 'directory.country', type: 'string', mapping: 'location_country'},
			    {name: 'directory.postcode', type: 'string', mapping: 'location_postcode'},
			    {name: 'directory.telephone', type: 'string', mapping: 'location_telephone'},
			    {name: 'directory.contact_name', type: 'string', mapping: 'contact_name'},
			    {name: 'directory.contact_title', type: 'string', mapping: 'contact_title'},
			    {name: 'directory.contact_email', type: 'string', mapping: 'contact_email'}
		  	]
	  	})
	  	
	};
	
	var addonMemberDirectoryLocation = new Ext.grid.GridPanel({
		//title:'dfsdf',
		border: false,
		viewConfig: {forceFit: true},
		//autoHeight: true,
		store: addonMemberDirectoryLocation_Store,
		cm:new Ext.grid.ColumnModel({
	        columns: [
		        new Ext.grid.RowNumberer({header:'#'}),
			    {id: 'id', header: 'ID', dataIndex: 'id', hidden: true,hideable:true},
			    {id: 'address', header: 'Address', dataIndex: 'address'},
			    {id: 'city', header: 'City', dataIndex: 'city'},
			    {id: 'state', header: 'State', dataIndex: 'state'}
		    ]
		}),
		sm:new Ext.grid.RowSelectionModel({
			singleSelect:true
			,listeners: {
				selectionchange : function(sm)	{
					addonMemberDirectoryLocation.getTopToolbar().editBtn.setDisabled(sm.getCount() < 1);
					addonMemberDirectoryLocation.getTopToolbar().preBtn.setDisabled(sm.getCount() < 1);
				}
			}	
		}),
		
		bbar: new Ext.PagingToolbar({
    		pageSize: 20,
    		store: addonMemberDirectoryLocation_Store,
    		displayInfo: true,
		    displayMsg: 'Displaying topics {0} - {1} of {2}',
		    emptyMsg: "No topics to display"
	    }),
		tbar:[{
			text:'Add',
			ref: 'addBtn',
			handler: function(){
				var addonMemberDirectoryLocationForm_add = addonMemberDirectoryLocationForm;
				addonMemberDirectoryLocationForm_add.buttons = [{
					text: 'save',
					handler: function()	{
						addonMemberDirectoryLocationWin.form.getForm().submit({
							url: 'index.php?option=com_osemsc&controller=member',
							params:{task:'action',action:'member.directory.updateLocationInfo'},
							success: function(form, action)	{
								var msg = action.result;
								oseMscAddon.msg.setAlert(msg.title,msg.content);
								
								addonMemberDirectoryLocation.getStore().reload();
								addonMemberDirectoryLocation.getView().refresh();
							}
						});
					}
				}]
				
				if(!addonMemberDirectoryLocationWin)	{
					var addonMemberDirectoryLocationWin = new Ext.Window({
						title: 'Location Information - Add',
						modal: true,
						width: 700,
						items:[
							addonMemberDirectoryLocationForm_add
						]
						,listeners: {
							render: function()	{
								var combo = addonMemberDirectoryLocationWin.form.getForm().findField('directory.country');
								combo.getStore().on('load',function(s,r){
									combo.setValue('US')
								})
							}
						}
					});
				}
				
				addonMemberDirectoryLocationWin.show(this).alignTo(Ext.getBody(),'t-t');
			}
		},{
			text:'Edit',
			ref: 'editBtn',
			disabled: true,
			handler: function(){
				var addonMemberDirectoryLocationForm_edit = addonMemberDirectoryLocationForm;
				addonMemberDirectoryLocationForm_edit.buttons = [{
					text: 'save',
					handler: function()	{
						var node = addonMemberDirectoryLocation.getSelectionModel().getSelected();
						addonMemberDirectoryLocationWin.form.getForm().submit({
							url: 'index.php?option=com_osemsc&controller=member',
							params:{task:'action',action:'member.directory.updateLocationInfo'},
							success: function(form, action)	{
								var msg = action.result;
								oseMscAddon.msg.setAlert(msg.title,msg.content);
								
								addonMemberDirectoryLocation.getStore().reload();
								addonMemberDirectoryLocation.getView().refresh();
							}
						});
					}
				}];
				
				if(!addonMemberDirectoryLocationWin)	{
					var addonMemberDirectoryLocationWin = new Ext.Window({
						title: 'Location Information - Edit',
						modal: true,
						width: 700,
						items:[
							addonMemberDirectoryLocationForm_edit
						],
						
						listeners:{
							show: function(w){
								var node = addonMemberDirectoryLocation.getSelectionModel().getSelected();
								
								w.form.getForm().load({
									url: 'index.php?option=com_osemsc&controller=member'
									,params:{task:'action',action:'member.directory.getLocationItem',location_id:node.id}
									,callback : function(el, success, response, opt) 	{
										var msg = Ext.decode(response.responseText);
										
										if(!msg.result.country)	{
											Ext.get('directory_country').setValue('US');
										}
										
									}
								});
							}
							
							,render: function()	{
								var combo = addonMemberDirectoryLocationWin.form.getForm().findField('directory.country');
								combo.getStore().on('load',function(s,r){
									combo.setValue('US')
								})
							}
						}
					});
				}
				
				addonMemberDirectoryLocationWin.show(this).alignTo(Ext.getBody(),'t-t');
			}
		},{
			text:'Delete'
			,handler: function()	{
				var node = addonMemberDirectoryLocation.getSelectionModel().getSelected();
				Ext.Msg.confirm('Warning','Are you sure to continue?',function(btn,text){
					if( btn == 'yes' )	{
						Ext.Ajax.request({
							url: 'index.php?option=com_osemsc&controller=member'
							,params: {task: 'action', action:'member.directory.removeLocation',location_id:node.id}
							,success: function(form, action)	{
								oseMsc.ajaxSuccess(form, action)
								
								addonMemberDirectoryLocation.getStore().reload();
								addonMemberDirectoryLocation.getView().refresh();
							}
						});
					}
				})
			}
		},{
			text:'Preview',
			ref: 'preBtn',
			//disabled: true,
			handler: function()	{
				var node = addonMemberDirectoryLocation.getSelectionModel().getSelected();
			//alert(node.data.address+","+node.data.city+","+node.data.state+","+node.data.postcode+"," +node.data.country);
				if(!mapwin){
		            var mapwin = new Ext.Window({
		                layout: 'fit'
		                ,title: 'GMap Window'
		                ,width: 500
		                ,height: 500
		                ,x: 100
		                ,y: 60
		                ,items:[{
		                    xtype: 'gmappanel'
		                    
		                    ,ref:'gmap'
		                    ,zoomLevel: 14
		                    ,gmapType: 'map'
		                    ,mapConfOpts: ['enableScrollWheelZoom','enableDoubleClickZoom','enableDragging']
		                    ,mapControls: ['GSmallMapControl','GMapTypeControl','NonExistantControl']
		              
		                    /*,addAddrMarker : function(addr, marker){
        						
								this.geocoder = new GClientGeocoder();
				
						        this.geocoder.getLocations(addr, function(response){
						        	place = response.Placemark[0];
						        	point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
						        	
									this.addMarker(point,marker,marker.clear,marker.center,marker.listeners);

								}.createDelegate(this));
								
						    }*/
						    
						    ,addAddrMarker : function(addr, marker){
								var s = this.setCenter;
								this.geocoder = new GClientGeocoder();
				
						        this.geocoder.getLocations(addr, function(response){
						        	place = response.Placemark[0];
						        	point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
						        	
						        	Ext.applyIf(marker,G_DEFAULT_ICON);
				
							        if (marker.clear === true){
							            this.getMap().clearOverlays();
							        }
							        if (marker.center === true) {
							            this.getMap().setCenter(point, this.zoomLevel);
							        }
							
							        var mark = new GMarker(point,marker);
							        
							        GEvent.addListener(mark,"mouseover",function(){
							        	mark.openInfoWindowHtml("Title:"+marker.title+"<br>Name:"+marker.name+"<br>Email:"+marker.email);
							        });
							        
							        
							        /*
							        if (typeof marker.listeners === 'object'){
							            for (evt in marker.listeners) {
							                GEvent.bind(mark, evt, this, marker.listeners[evt]);
							            }
							        }
							        */
							        
							        this.getMap().addOverlay(mark);
						        	
									//this.addMarker(point,marker,marker.clear,marker.center,marker.listeners);
				
								}.createDelegate(this));
								
						    }
		                }]
		               
		            });
		            
		            //alert(addonMemberDirectoryLocation_Store.reader.jsonData.toSource());
		            
		           	Ext.each(addonMemberDirectoryLocation_Store.reader.jsonData.results,function(r,i,all){
		          		
		           		var data = r;
		           		var address = '"'+data.address+','+data.city+','+data.state+','+data.postcode+','+data.country+'"';
		           		//alert(address);
		           		mapwin.gmap.addAddrMarker(address,{
			            	title: data.contact_title,
			            	name: data.contact_name,
			            	email: data.contact_email,
			            	clear: false,
			            	center: (i == 0)?true:false
			            });
		           	});
		           	
		        	
				}	
				
				mapwin.show();
			}
		}]
	});
	
	var addonMemberDirectoryCatForm = {
		ref:'form',
		xtype: 'form',
		items:[{
			ref:'tree',
			xtype: 'treepanel',
			useArrows: true,
	        autoScroll: true,
	        animate: true,
	        enableDD: true,
	        containerScroll: true,
	        border: false,
	        // auto create TreeLoader
	        dataUrl: 'index.php?option=com_osemsc&controller=member&task=action&action=member.directory.getMtcats',
			baseParams: {task:'action',action:'member.directory.getMtCats'},
	        root: {
	            nodeType: 'async',
	            text: 'Root',
	            draggable: false,
	            id: 'root'
	        }
		}]
	}
	
	var addonMemberDirectoryCat_Store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=member',
	            method: 'POST'
	        }),
		  baseParams:{task: "action",action:"member.directory.getSelectedLinkCats",limit: 20}, 
		  reader: new Ext.data.JsonReader({   
		              // we tell the datastore where to get his data from
		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'cl_id'
		  },[ 
		    {name: 'cat_id', type: 'int', mapping: 'cat_id'},
		    {name: 'cat_name', type: 'string', mapping: 'cat_name'},
		    {name: 'main', type: 'string', mapping: 'main'}
		  ])
	});
	
	var addonMemberDirectoryCat = new Ext.grid.GridPanel({
		//title:'dfsdf',
		border: false,
		//autoHeight: true,
		viewConfig: {forceFit: true},
		store:addonMemberDirectoryCat_Store,
		cm:new Ext.grid.ColumnModel({
	        columns: [
		        new Ext.grid.RowNumberer({header:'#'}),
			    {id: 'cat_id', header: 'ID', dataIndex: 'cat_id', hidden: true,hideable:true},
			    {id: 'cat_name', header: 'Category', dataIndex: 'cat_name'},
			    {
			    	id: 'main', header: 'Category', dataIndex: 'main',
			    	renderer: function(val){
			    		if( val == '1' )	{
			    			return 'main';
			    		}else{
			    			return '';
			    		}
			    		
			    	}
			    }
		    ]
		}),
		sm:new Ext.grid.RowSelectionModel({
			singleSelect:true
			,listeners: {
				selectionchange : function(sm)	{
					addonMemberDirectoryCat.getTopToolbar().editBtn.setDisabled(sm.getCount() < 1);
				}
			}	
		}),
		
		tbar:[{
			text:'Add',
			handler: function()	{
				var addonMemberDirectoryCatForm_add = addonMemberDirectoryCatForm;
				
				addonMemberDirectoryCatForm_add.buttons = [{
					text: 'Save',
					handler: function()	{
						var node = addonMemberDirectoryCatWin.form.tree.getSelectionModel().getSelectedNode();
						
						addonMemberDirectoryCatWin.form.getForm().submit({
							url:'index.php?option=com_osemsc&controller=member',
							params:{
								task:'action',action:'member.directory.updateMtCat',
								cat_id: node.attributes.cat_id
								
							},
							success: function(form,action)	{
								var msg = action.result;
								oseMscAddon.msg.setAlert(msg.title,msg.content);
								addonMemberDirectoryCat.getStore().reload();
								addonMemberDirectoryCat.getView().refresh();
							},
							failure: function(form,action)	{
								var msg = action.result;
								oseMscAddon.msg.setAlert(msg.title,msg.content);
							}
						})
					}
				}]
				if(!addonMemberDirectoryCatWin)	{
					var addonMemberDirectoryCatWin = new Ext.Window({
						title: 'Location Category - Add',
						width: 500,
						autoHeight: true,
						modal: true,
						plain: false,
						items:[
							addonMemberDirectoryCatForm
						]
					})
				}
				
				addonMemberDirectoryCatWin.form.tree.getRootNode().expand();
				addonMemberDirectoryCatWin.show(this).alignTo(Ext.getBody(),'t-t');
			}
		},{
			text:'Edit',
			ref: 'editBtn',
			disabled: true,
			handler: function()	{
				var addonMemberDirectoryCatForm_edit = addonMemberDirectoryCatForm;
				
				addonMemberDirectoryCatForm_edit.buttons = [{
					text: 'Save',
					handler: function()	{
						var tNode = addonMemberDirectoryCatWin.form.tree.getSelectionModel().getSelectedNode();
						var gNode = addonMemberDirectoryCat.getSelectionModel().getSelected();
						alert(gNode.id);
						addonMemberDirectoryCatWin.form.getForm().submit({
							url:'index.php?option=com_osemsc&controller=member',
							params:{
								task:'action',action:'member.directory.updateMtCat',cl_id:gNode.id,
								cat_id: tNode.attributes.cat_id
							},
							success: function(form,action)	{
								var msg = action.result;
								oseMscAddon.msg.setAlert(msg.title,msg.content);
								
								addonMemberDirectoryCat.getStore().reload();
								addonMemberDirectoryCat.getView().refresh();
							},
							failure: function(form,action)	{
								var msg = action.result;
								oseMscAddon.msg.setAlert(msg.title,msg.content);
							}
						})
					}
				}]
				
				
				if(!addonMemberDirectoryCatWin)	{
					var addonMemberDirectoryCatWin = new Ext.Window({
						title: 'Location Category - Edit',
						width: 500,
						autoHeight: true,
						modal: true,
						plain: false,
						items:[
							addonMemberDirectoryCatForm
						]
					})
				}
				
				addonMemberDirectoryCatWin.form.tree.getRootNode().expand();
				addonMemberDirectoryCatWin.show(this).alignTo(Ext.getBody(),'t-t');
			}
		},{
			text:'Delete'
            ,handler: function()	{
				var gNode = addonMemberDirectoryCat.getSelectionModel().getSelected();
				Ext.Ajax.request({
					url: 'index.php?option=com_osemsc&controller=member'
					,params:{task:'action',action:'member.directory.removeMtCat',cl_id: gNode.id}
					,success: function(response,opt)	{
						var msg = Ext.decode(response.responseText);
						oseMscAddon.msg.setAlert(msg.title,msg.content);
						
						addonMemberDirectoryCat.getStore().reload();
						addonMemberDirectoryCat.getView().refresh();
					}
				});
			}
		}],
		
		bbar:new Ext.PagingToolbar({
    		pageSize: 20,
    		store: addonMemberDirectoryCat_Store,
    		displayInfo: true,
		    displayMsg: 'Displaying topics {0} - {1} of {2}',
		    emptyMsg: "No topics to display"
	    })
		
	});
	
	oseMscAddon.directory = new Ext.TabPanel({
		autoHeight: true
		,activeTab:0
		,defaults:{autoHeight: true}
		,items:[{
			title: 'Directory Information'
			,defaults: {height: 350}
			,layout: 'hbox'
			,items: [addonMemberDirectoryInfo,addonMemberDirectoryPreview]
		},{
			title: 'Directory Location'
			,defaults: {height: 350}
			,items: [addonMemberDirectoryLocation]
		},{
			title: 'Directory Location Category'
			,defaults: {height: 350}
			,items: [addonMemberDirectoryCat]
		}]
		,listeners: {
			render: function(w){
				addonMemberDirectoryLocation_Store.load();
				addonMemberDirectoryCat_Store.load();
				addonMemberDirectoryInfo.getForm().load({
					url: 'index.php?option=com_osemsc&controller=member',
					params:{task:'action',action:'member.directory.getDirectoryItem'},
					success: function(form,action){
						var msg = action.result.data;
						Ext.getCmp('preview').getEl().set({'src':msg['directory.directory_logo']});
						
					}
				});
				
			}
		}
	});