Ext.ns('oseMsc','oseMsc.config','oseMsc.config.locale');
	oseMsc.config.locale.buildCountryGrid = function() 	{
		return {
			buildStore: function()	{
				var gStore = new Ext.data.Store({
					proxy: new Ext.data.HttpProxy({
			    		url: 'index.php?option=com_osemsc&controller=config'
			    		,method: 'POST'
					})
					,baseParams:{task: "getCountryList",limit: 20}
					,reader: new Ext.data.JsonReader({
						root: 'results'
						,totalProperty: 'total'
						,idProperty: 'country_id'
					},[
						{name: 'country_id', type: 'int', mapping: 'country_id'}
						,{name: 'country_name', type: 'string', mapping: 'country_name'}
			    		,{name: 'country_3_code', type: 'string', mapping: 'country_3_code'}
			    		,{name: 'country_2_code', type: 'string', mapping: 'country_2_code'}
			  		])

					,sortInfo:{field: 'country_id', direction: "ASC"}
				});

				return gStore
			}

			,buildSm: function()	{
				var sm = new Ext.grid.CheckboxSelectionModel({
					listeners: {
						selectionchange: function(sm)	{

						}
					}
				});

				return sm;
			}

			,buildCm: function(sm)	{
				var cm = new Ext.grid.ColumnModel({
					defaults: {
			            sortable: false
			            ,width: 100
			        },
			        columns: [
				        new Ext.grid.RowNumberer({header:'#'})
				        ,sm
					    ,{id: 'country_id', header: Joomla.JText._('ID'), dataIndex: 'country_id', hidden: true,hideable:true}
					    ,{id: 'country_name', header: Joomla.JText._('Country_Name'), dataIndex: 'country_name'}
					    ,{id: 'country_3_code', header: Joomla.JText._('Country_3_Code'), dataIndex: 'country_3_code'}
					    ,{id: 'country_2_code', header: Joomla.JText._('Country_2_Code'), dataIndex: 'country_2_code',width: 150}
					    ,{
					    	xtype: 'actioncolumn'
					    	,id:'action'
			                ,width: 200
			                ,header: Joomla.JText._('Edit_Delete_Edit_State')
			                ,align: 'center'
			                ,items: [{
			                    getClass: function(v, meta, rec)	{
			                		return 'edit-col';
			                	}
			                    ,tooltip: Joomla.JText._('Click_to_edit')
			                    ,handler: function(grid, rowIndex, colIndex) {}
			                },{
			                    getClass: function(v, meta, rec)	{
			                		return '';//'disable-col';
			                	}
			                    ,tooltip: Joomla.JText._('Click_to_delete')
			                    ,handler: function(grid, rowIndex, colIndex) { }
			                    ,scope:this
			                },{
			                    getClass: function(v, meta, rec)	{
			                		return 'viewState-col';
			                	}
			                    ,tooltip: Joomla.JText._('Click_to_Edit_State_List')
			                    ,handler: function(grid, rowIndex, colIndex) {
			                    	oseMsc.config.locale.country_id = grid.getStore().getAt(rowIndex).get('country_id')
			                    	var panel = oseMsc.config.locale.stateInit();
			                    	var win = new Ext.Window({
										title: Joomla.JText._('State_List')
										,items: panel
										,width: 800
										,modal: true
										,closeAction: 'close'
										,autoHeight: true
									}).show().alignTo(Ext.getBody(),'t-t');
			                    }
			                    ,scope:this
			                }]
					    }
				    ]
				});

				return cm;
			}
		}
	}()

	oseMsc.config.locale.buildCountryForm = function()	{
		var fReader = new Ext.data.JsonReader({
		    root: 'result',
		    totalProperty: 'total',
		    idProperty: 'country_id',
		    fields:[
			    {name: 'country_id', type: 'int', mapping: 'country_id'}
				,{name: 'country_name', type: 'string', mapping: 'country_name'}
	    		,{name: 'country_3_code', type: 'string', mapping: 'country_3_code'}
	    		,{name: 'country_2_code', type: 'string', mapping: 'country_2_code'}
		  	]
	  	});

	  	//var getCountryList = oseMsc.combo.getCountryCombo('Country','country_3_code',3);
		//var getStateList = oseMsc.combo.getStateCombo('State','state_2_code',2);

		//oseMsc.combo.relateCountryState(getCountryList,getStateList,'USA');
		//getCountryList.getStore().load();

		var tForm = new Ext.FormPanel({
			items:[{
				xtype: 'textfield'
				,name: 'country_name'
				,fieldLabel: Joomla.JText._('Country_Name')
			},{
				xtype: 'textfield'
				,name: 'country_3_code'
				,fieldLabel: Joomla.JText._('Country_3_Code')
			},{
				xtype: 'textfield'
				,name: 'country_2_code'
				,fieldLabel: Joomla.JText._('Country_2_Code')
			},{
				xtype: 'hidden'
				,name: 'country_id'
			}]
			,labelWidth: 130
			,bodyStyle: 'padding: 10px'
			,defaults: {width: 300}
			,height: 250
			,reader: fReader
			,buttons:[{
				text: Joomla.JText._('Save')
				,itemId: 'btnSave'
				,handler: function()	{
					Ext.Msg.wait(Joomla.JText._('Processing'),Joomla.JText._('please_wait'));
					tForm.getForm().submit({
						url:'index.php?option=com_osemsc&controller=config'
						,params:{task:'saveCountry'}
						//,waitMsg: 'Processing...'
						,success: function(bform,action)	{
							Ext.Msg.hide();
							oseMsc.formSuccess(bform,action)
							this.grid.getStore().reload();
							this.grid.getView().refresh();
						}
						,failure: oseMsc.formFailureMB
						,scope:this
					})
				}
				,scope:this
			}]
		})

		return tForm;
	}

	oseMsc.config.locale.buildCountryWin = function(form,closeMode)	{
		if(typeof(closeMode) == 'undefined')	{
			closeMode = 'close';
		}

		var win = new Ext.Window({
			title: Joomla.JText._('Country_Information')
			,items: form
			,width: 500
			,modal: true
			,closeAction: closeMode
			,autoHeight: true
		})

		win.show().alignTo(Ext.getBody(),'t-t');
	}

	oseMsc.config.locale.init = function()	{
		var store = oseMsc.config.locale.buildCountryGrid.buildStore();
		var sm = oseMsc.config.locale.buildCountryGrid.buildSm();
		var cm = oseMsc.config.locale.buildCountryGrid.buildCm(sm);

		var buildCountry = new ose.quickGrid.build(store,cm,sm);

		// generate a grid
		buildCountry.init('country-grid');

		buildCountry.buildForm = oseMsc.config.locale.buildCountryForm;
		buildCountry.buildWin = oseMsc.config.locale.buildCountryWin;

		buildCountry.setDeleteAction('country_id',{
			url: 'index.php?option=com_osemsc&controller=config'
			,params: {task: 'removeCountry'}
		})

		buildCountry.relate('action');

		buildCountry.addTopBtn({
			xtype: 'button'
			,text: Joomla.JText._('Set_Default_Selected_Country')
			,handler: function()	{
				var countryCombo = oseMsc.combo.getCountryCombo(Joomla.JText._('Default_Country'),'default_country',3);
				var form = new Ext.FormPanel({
					labelWidth: 100
					,items:[{
						xtype: 'combo'
						,fieldLabel: Joomla.JText._('Default_Country')
			            ,codeNumber: 3
			            ,hiddenName: 'default_country'
			            ,itemId: 'default_country'
					    ,typeAhead: true
					    ,triggerAction: 'all'
					    ,lazyRender:false
					    ,listClass: 'combo-left'
					    ,submitValue: false
					    ,mode: 'local'
					    ,lastQuery: ''
					    ,forceSelection: true
					    ,store: new Ext.data.Store({
					  		proxy: new Ext.data.HttpProxy({
					            url: 'index.php?option=com_osemsc'
					            ,method: 'POST'
				      		})
						  	,baseParams:{task: "getCountry"}
						  	,reader: new Ext.data.JsonReader({
						    	root: 'results'
						    	,totalProperty: 'total'
						    	,idProperty: 'country_id'
						  	},[
						    {name: 'code3', type: 'string', mapping: 'country_3_code'}
						    ,{name: 'subject', type: 'string', mapping: 'country_name'}
						    ,{name: 'code2', type: 'string', mapping: 'country_2_code'}
						    ,{name: 'country_id', type: 'string', mapping: 'country_id'}
						  	])
						  	,listeners: {
						  		load: function()	{
						  			form.load({
					  					url: 'index.php?option=com_osemsc'
					  					,params: {controller:'config',task:'getConfig',config_type:'locale'}
					  				})
						  		}
						  	}
						  	,autoLoad:{}
						})
						
					    ,valueField: 'country_id'
					    ,displayField: 'subject'
						
					}]
					,buttons:[{
						text: Joomla.JText._('Save')
						,handler: function()	{
							var c = form.getComponent('default_country');
							var r = c.getStore().getById(c.getValue());
							
							var e = Ext.encode({
								'country_id':r.get('country_id')
								,'code3': r.get('code3')
								,'code2': r.get('code2')
							});
							form.getForm().submit({
								url: 'index.php?option=com_osemsc'
								,params:{controller:'config',task:'save',config_type:'locale',default_country_json:e}
								,success: oseMsc.formSuccess
								,failure: oseMsc.formFailureMB
							})
						}
						,scope: this
					}]
					,reader: new Ext.data.JsonReader({
						root: 'result',
						totalProperty: 'total'
					},[
						{name: 'id', type: 'int', mapping: 'id'}
			    		,{name: 'default_country', type: 'string', mapping: 'default_country'}
			  		])
			  		,listeners:{
			  			render: function()	{
			  				//form.load({
			  				//	url: 'index.php?option=com_osemsc'
			  				//	,params: {controller:'config',task:'getConfig',config_type:'locale'}
			  				//})
			  			}
			  		}
				})
				new Ext.Window({
					title: ''
					,width: 390
					,items: form
					,modal: true
				}).show().alignTo(Ext.getBody(),'t-t');
			} 
		})
		
		buildCountry.addTopBtn({
			xtype: 'button'
			,text: Joomla.JText._('Set_Default_Selected_State')
			,handler: function()	{
				var form = new Ext.FormPanel({
					labelWidth: 100
					,items:[{
						xtype: 'combo'
						,fieldLabel: Joomla.JText._('Default_State')
			            ,codeNumber: 3
			            ,hiddenName: 'default_state'
			            ,itemId: 'default_state'
					    ,typeAhead: true
					    ,triggerAction: 'all'
					    ,lazyRender:false
					    ,listClass: 'combo-left'
					    ,submitValue: false
					    ,mode: 'local'
					    ,lastQuery: ''
					    ,forceSelection: true
					    ,store: new Ext.data.Store({
					  		proxy: new Ext.data.HttpProxy({
					            url: 'index.php?option=com_osemsc'
					            ,method: 'POST'
				      		})
						  	,baseParams:{task: "getDefState"}
						  	,reader: new Ext.data.JsonReader({
						    	root: 'results'
						    	,totalProperty: 'total'
						    	,idProperty: 'state_id'
						  	},[
						    {name: 'code3', type: 'string', mapping: 'state_3_code'}
						    ,{name: 'subject', type: 'string', mapping: 'state_name'}
						    ,{name: 'code2', type: 'string', mapping: 'state_2_code'}
						    ,{name: 'country_id', type: 'string', mapping: 'country_id'}
						    ,{name: 'state_id', type: 'string', mapping: 'state_id'}
						  	])
						  	,listeners: {
						  		load: function()	{
						  			form.load({
					  					url: 'index.php?option=com_osemsc'
					  					,params: {controller:'config',task:'getConfig',config_type:'locale'}
					  				})
						  		}
						  	}
						  	,autoLoad:{}
						})
						
					    ,valueField: 'state_id'
					    ,displayField: 'subject'
						
					}]
					,buttons:[{
						text: Joomla.JText._('Save')
						,handler: function()	{
							var c = form.getComponent('default_state');
							var r = c.getStore().getById(c.getValue());
							
							var e = Ext.encode({
								'state_id':r.get('state_id')
								,'country_id':r.get('country_id')
								,'code3': r.get('code3')
								,'code2': r.get('code2')
							});
							form.getForm().submit({
								url: 'index.php?option=com_osemsc'
								,params:{controller:'config',task:'save',config_type:'locale',default_state_json:e}
								,success: oseMsc.formSuccess
								,failure: oseMsc.formFailureMB
							})
						}
						,scope: this
					}]
					,reader: new Ext.data.JsonReader({
						root: 'result',
						totalProperty: 'total'
					},[
						{name: 'id', type: 'int', mapping: 'id'}
			    		,{name: 'default_state', type: 'string', mapping: 'default_state'}
			  		])
			  		,listeners:{
			  			render: function()	{
			  				//form.load({
			  				//	url: 'index.php?option=com_osemsc'
			  				//	,params: {controller:'config',task:'getConfig',config_type:'locale'}
			  				//})
			  			}
			  		}
				})
				new Ext.Window({
					title: ''
					,width: 390
					,items: form
					,modal: true
				}).show().alignTo(Ext.getBody(),'t-t');
			} 
		})
		
		buildCountry.addTopBtn('->');
		buildCountry.addTopBtn({text: Joomla.JText._('Search')});
		

		var grid = buildCountry.output();
		grid = Ext.apply(grid,{
			autoExpandColumn: 'country_name'
		})
		
		buildCountry.addTopBtn(
			new Ext.ux.form.SearchField({
                store: grid.getStore(),
                paramName: 'search',
                width:150
            })
        );

		grid.getStore().load();
		grid.setHeight(600)

		var panel = new Ext.Panel({
			title: Joomla.JText._('Locale')
			,bodyStyle:'padding:10px'
			//,defaults:{bodyStyle:'padding:10px'}
			,items: [grid]
		});

		return panel;
	}
	
/////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////
	oseMsc.config.locale.buildStateGrid = function() 	{
		return {
			buildStore: function()	{
				var gStore = new Ext.data.Store({
					proxy: new Ext.data.HttpProxy({
			    		url: 'index.php?option=com_osemsc&controller=config'
			    		,method: 'POST'
					})
					,baseParams:{task: "getStateList",limit: 20, country_id: oseMsc.config.locale.country_id}
					,reader: new Ext.data.JsonReader({
						root: 'results'
						,totalProperty: 'total'
						,idProperty: 'state_id'
					},[
						{name: 'state_id', type: 'int', mapping: 'state_id'}
						,{name: 'country_id', type: 'int', mapping: 'country_id'}
						,{name: 'state_name', type: 'string', mapping: 'state_name'}
			    		,{name: 'state_3_code', type: 'string', mapping: 'state_3_code'}
			    		,{name: 'state_2_code', type: 'string', mapping: 'state_2_code'}
			  		])

					,sortInfo:{field: 'state_id', direction: "ASC"}
				});

				return gStore
			}

			,buildSm: function()	{
				var sm = new Ext.grid.CheckboxSelectionModel({
					listeners: {
						selectionchange: function(sm)	{

						}
					}
				});

				return sm;
			}

			,buildCm: function(sm)	{
				var cm = new Ext.grid.ColumnModel({
					defaults: {
			            sortable: false
			            ,width: 100
			        },
			        columns: [
				        new Ext.grid.RowNumberer({header:'#'})
				        ,sm
					    ,{id: 'state_id', header: 'ID', dataIndex: 'state_id', hidden: true,hideable:true}
					    ,{id: 'country_id', header: 'ID', dataIndex: 'country_id', hidden: true,hideable:true}
					    ,{id: 'state_name', header: 'state Name', dataIndex: 'state_name'}
					    ,{id: 'state_3_code', header: 'state 3 Code', dataIndex: 'state_3_code'}
					    ,{id: 'state_2_code', header: 'state 2 Code', dataIndex: 'state_2_code',width: 150}
					    ,{
					    	xtype: 'actioncolumn'
					    	,id:'action'
			                ,width: 200
			                ,header: Joomla.JText._('Edit_Delete')
			                ,align: 'center'
			                ,items: [{
			                    getClass: function(v, meta, rec)	{
			                		return 'edit-col';
			                	}
			                    ,tooltip: Joomla.JText._('Click_to_edit')
			                    ,handler: function(grid, rowIndex, colIndex) {

			                    }
			                },{
			                    getClass: function(v, meta, rec)	{
			                		return 'disable-col';
			                	}
			                    ,tooltip: Joomla.JText._('Click_to_delete')
			                    ,handler: function(grid, rowIndex, colIndex) {
			                    	//var rs = grid.getStore().getAt(rowIndex)
			                    	//var form = this.buildForm();
			                    	//form.getForm().setValues(rs.data);
									//this.buildWin(form);
			                    }
			                    ,scope:this
			                }]
					    }
				    ]
				});

				return cm;
			}
		}
	}()

	oseMsc.config.locale.buildStateForm = function()	{
		var fReader = new Ext.data.JsonReader({
		    root: 'result',
		    totalProperty: 'total',
		    idProperty: 'state_id',
		    fields:[
			    {name: 'state_id', type: 'int', mapping: 'state_id'}
			    ,{name: 'country_id', type: 'int', mapping: 'country_id'}
				,{name: 'state_name', type: 'string', mapping: 'state_name'}
	    		,{name: 'state_3_code', type: 'string', mapping: 'state_3_code'}
	    		,{name: 'state_2_code', type: 'string', mapping: 'state_2_code'}
		  	]
	  	});

		var tForm = new Ext.FormPanel({
			items:[{
				xtype: 'textfield'
				,name: 'state_name'
				,fieldLabel: Joomla.JText._('state_Name')
			},{
				xtype: 'textfield'
				,name: 'state_3_code'
				,fieldLabel: Joomla.JText._('state_3_Code')
			},{
				xtype: 'textfield'
				,name: 'state_2_code'
				,fieldLabel: Joomla.JText._('state_2_Code')
			},{
				xtype: 'hidden'
				,name: 'state_id'
			},{
				xtype: 'hidden'
				,name: 'country_id'
				,value: oseMsc.config.locale.country_id
			}]
			,labelWidth: 130
			,bodyStyle: 'padding: 10px'
			,defaults: {width: 300}
			,height: 250
			,reader: fReader
			,buttons:[{
				text: Joomla.JText._('Save')
				,itemId: 'btnSave'
				,handler: function()	{
					Ext.Msg.wait(Joomla.JText._('Processing'),Joomla.JText._('please_wait'));
					tForm.getForm().submit({
						url:'index.php?option=com_osemsc&controller=config'
						,params:{task:'saveState'}
						//,waitMsg: 'Processing...'
						,success: function(bform,action)	{
							Ext.Msg.hide();
							oseMsc.formSuccess(bform,action)
							this.grid.getStore().reload();
							this.grid.getView().refresh();
						}
						,failure: oseMsc.formFailureMB
						,scope:this
					})
				}
				,scope:this
			}]
		})

		return tForm;
	}

	oseMsc.config.locale.buildStateWin = function(form,closeMode)	{
		if(typeof(closeMode) == 'undefined')	{
			closeMode = 'close';
		}

		var win = new Ext.Window({
			title: Joomla.JText._('state_Information')
			,items: form
			,width: 500
			,modal: true
			,closeAction: closeMode
			,autoHeight: true
		})

		win.show().alignTo(Ext.getBody(),'t-t');
	}

	oseMsc.config.locale.stateInit = function()	{
		var store = oseMsc.config.locale.buildStateGrid.buildStore();
		var sm = oseMsc.config.locale.buildStateGrid.buildSm();
		var cm = oseMsc.config.locale.buildStateGrid.buildCm(sm);

		var buildState = new ose.quickGrid.build(store,cm,sm);

		// generate a grid
		buildState.init('state-grid');

		buildState.buildForm = oseMsc.config.locale.buildStateForm;
		buildState.buildWin = oseMsc.config.locale.buildStateWin;

		buildState.setDeleteAction('state_id',{
			url: 'index.php?option=com_osemsc&controller=config'
			,params: {task: 'removestate'}
		})

		buildState.relate('action');

		var grid = buildState.output();
		grid = Ext.apply(grid,{
			autoExpandColumn: 'state_name'
		})

		grid.getStore().load();
		grid.setHeight(600)

		var panel = new Ext.Panel({
			title: Joomla.JText._('State')
			,bodyStyle:'padding:10px'
			//,defaults:{bodyStyle:'padding:10px'}
			,items: [grid]
		});

		return grid;
	}
