Ext.ns('oseMsc','oseMsc.config','oseMsc.config.tax');
	oseMsc.config.tax.buildGrid = function() 	{
		return {
			buildStore: function()	{
				var gStore = new Ext.data.Store({
					proxy: new Ext.data.HttpProxy({
			    		url: 'index.php?option=com_osemsc&controller=config'
			    		,method: 'POST'
					})
					,baseParams:{task: "getTaxList",limit: 20}
					,reader: new Ext.data.JsonReader({
						root: 'results',
						totalProperty: 'total'
					},[
						{name: 'id', type: 'int', mapping: 'id'}
			    		,{name: 'country_3_code', type: 'string', mapping: 'country_3_code'}
			    		,{name: 'state_2_code', type: 'string', mapping: 'state_2_code'}
			    		,{name: 'rate', type: 'float', mapping: 'rate'}
			    		,{name: 'file_control', type: 'string', mapping: 'file_control'}
			    		,{name: 'has_file_control', type: 'string', mapping: 'has_file_control'}
			  		])

					,sortInfo:{field: 'id', direction: "ASC"}
					//,autoLoad:{}
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
					    ,{id: 'id', header: Joomla.JText._('ID'), dataIndex: 'id', hidden: true,hideable:true}
					    ,{id: 'country_3_code', header: Joomla.JText._('Country'), dataIndex: 'country_3_code'}
					    ,{id: 'state_2_code', header: Joomla.JText._('State'), dataIndex: 'state_2_code',width: 150}
					    ,{id: 'rate', header: Joomla.JText._('Rate'), dataIndex: 'rate',width: 150}
					    //,{id: 'file_control', header: 'File Control', dataIndex: 'file_control',width: 150}
					    /*,{
					    	id: 'has_file_control', header: 'Has File Control?', dataIndex: 'has_file_control',align: 'center',width: 100
					    	,renderer: function(val)	{
					    		if(val == 1)	{
					    			return 'Yes';
					    		}	else	{
					    			return 'No';
					    		}
					    	}
					    }*/
					    ,{
					    	xtype: 'actioncolumn'
					    	,id:'action'
			                ,width: 100
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

	oseMsc.config.tax.buildForm = function()	{
		var fReader = new Ext.data.JsonReader({
		    root: 'result',
		    totalProperty: 'total',
		    fields:[
			    {name: 'id', type: 'int', mapping: 'id'}
			    ,{name: 'country_3_code', type: 'string', mapping: 'country_3_code'}
			    ,{name: 'state_2_code', type: 'string', mapping: 'state_2_code'}
			    ,{name: 'rate', type: 'float', mapping: 'rate'}
			    ,{name: 'file_control', type: 'int', mapping: 'file_control'}
		  	]
	  	});

	  	var getCountryList = oseMsc.combo.getCountryCombo(Joomla.JText._('Country'),'country_3_code',3);
		var getStateList = oseMsc.combo.getStateCombo(Joomla.JText._('State'),'state_2_code',2);
		
		getCountryList.allowBlank = false;
		getCountryList.emptyText= Joomla.JText._('Please_Choose');
		getStateList.allowBlank = false;
		getStateList.emptyText= Joomla.JText._('Please_Choose');
		
		
		oseMsc.combo.getLocalJsonData(getCountryList,oseMsc.countryData);
		oseMsc.combo.getLocalJsonData(getStateList,oseMsc.stateData);
		getStateList.getStore().fireEvent('load',getStateList.getStore());
		
		oseMsc.combo.relateCountryState(getCountryList,getStateList);
		//getCountryList.fireEvent('select',getStateList.getStore());
		//oseMsc.combo.relateCountryState(getCountryList,getStateList,'USA');
		//getCountryList.getStore().load();

		var tForm = new Ext.FormPanel({
			items:[
				getCountryList
				,getStateList
				,{
					xtype: 'numberfield'
					,name: 'rate'
					,fieldLabel: Joomla.JText._('Rate')
				},{
					xtype: 'textfield'
					,name: 'file_control'
					,hidden:true
					,fieldLabel: Joomla.JText._('Special_File_To_Control_Tax')
				},{
					xtype: 'hidden'
					,name: 'id'
				}
			]
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
						,params:{task:'saveTax'}
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

	oseMsc.config.tax.buildWin = function(form,closeMode)	{
		if(typeof(closeMode) == 'undefined')	{
			closeMode = 'close';
		}

		var win = new Ext.Window({
			title: Joomla.JText._('Tax_Information')
			,items: form
			,width: 500
			,modal: true
			,closeAction: closeMode
			,autoHeight: true
		})

		win.show().alignTo(Ext.getBody(),'t-t');
	}

	oseMsc.config.tax.init = function()	{
		var store = oseMsc.config.tax.buildGrid.buildStore();
		var sm = oseMsc.config.tax.buildGrid.buildSm();
		var cm = oseMsc.config.tax.buildGrid.buildCm(sm);

		var build = new ose.quickGrid.build(store,cm,sm);

		// generate a grid
		build.init('tax-grid');

		build.buildForm = oseMsc.config.tax.buildForm;
		build.buildWin = oseMsc.config.tax.buildWin;

		build.setDeleteAction('id',{
			url: 'index.php?option=com_osemsc&controller=config'
			,params: {task: 'removeTax'}
		})

		build.relate('action');

		build.addTopBtn({
			xtype: 'button'
			,text: Joomla.JText._('Set_Global_Tax')
			,handler: function()	{
				var form = new Ext.FormPanel({
					labelWidth: 100
					,items:[{
						xtype: 'numberfield'
						,name: 'global_tax'
						,fieldLabel: Joomla.JText._('Default_Tax')
					}]
					,buttons:[{
						text: Joomla.JText._('Save')
						,handler: function()	{
							form.getForm().submit({
								url: 'index.php?option=com_osemsc'
								,params:{controller:'config',task:'save',config_type:'tax'}
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
			    		,{name: 'global_tax', type: 'string', mapping: 'global_tax'}
			  		])
			  		,listeners:{
			  			render: function()	{
			  				form.load({
			  					url: 'index.php?option=com_osemsc'
			  					,params: {controller:'config',task:'getConfig',config_type:'tax'}
			  				})
			  			}
			  		}
				})
				new Ext.Window({
					title: ''
					,modal: true
					,width: 390
					,items: form
				}).show().alignTo(Ext.getBody(),'t-t');
			} 
		})
		
		build.addTopBtn({
			xtype: 'button'
			,text: Joomla.JText._('Set_VAT_Number')
			,handler: function()	{
				var form = new Ext.FormPanel({
					labelWidth: 150
					,bodyStyle: 'padding: 10px'
					,items:[{
						xtype: 'textfield'
						,name: 'vat_number'
						,fieldLabel: Joomla.JText._('VAT_Number')
					},{
						fieldLabel: Joomla.JText._('Enable_Europe_VAT_Number_Validation')
						,xtype: 'radiogroup'
						,name:'enable_europe_vatnumber_validate'
						,defaults: {xtype: 'radio', name:'enable_europe_vatnumber_validate'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
						]
						,listeners: {
							change: function(rg,checked)	{
								var fs = rg.nextSibling();
								fs.setVisible(checked.getGroupValue() == 1)
								Ext.each(fs.findByType('textfield'),function(item,i,all)	{
									item.setDisabled(checked.getGroupValue() == 0);
								});
							}
							,render: function(rg)	{
								rg.fireEvent('change',rg,rg.getValue())
							}
						}
					},{
						xtype: 'fieldset'
						,title: Joomla.JText._('Europe_Validation_Required')
						
						,items:[{
							xtype: 'textfield'
							,name: 'vat_checker_requester_iso'
							,fieldLabel: Joomla.JText._('2_Digit_Code_of_Your_Country')
						}]
					}]
					,buttons:[{
						text: Joomla.JText._('Save')
						,handler: function()	{
							form.getForm().submit({
								url: 'index.php?option=com_osemsc'
								,params:{controller:'config',task:'save',config_type:'tax'}
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
			    		,{name: 'vat_number', type: 'string', mapping: 'vat_number'}
			    		,{name: 'enable_europe_vatnumber_validate', type: 'string', mapping: 'enable_europe_vatnumber_validate'}
			    		,{name: 'vat_checker_requester_iso', type: 'string', mapping: 'vat_checker_requester_iso'}
			  		])
			  		,listeners:{
			  			render: function()	{
			  				Ext.Msg.wait(Joomla.JText._('Loading'),Joomla.JText._('Please_wait'));
			  				form.load({
			  					url: 'index.php?option=com_osemsc'
			  					,params: {controller:'config',task:'getConfig',config_type:'tax'}
			  					//,waitMsg: 'Loading...'
				  				,success: function(form,action)	{
									Ext.Msg.hide()
								}
								,failure:function(form,action){
									Ext.Msg.hide()
								}
			  				})
			  			}
			  		}
				})
				new Ext.Window({
					title: Joomla.JText._('VAT_Number')
					,modal: true
					,width: 500
					,items: form
				}).show().alignTo(Ext.getBody(),'t-t');
			} 
		})

		build.addTopBtn({
			xtype: 'button'
			,text: Joomla.JText._('Load_Default_Canadian_Tax_Rate')
			,handler: function()	{
				Ext.Ajax.request({
					url: 'index.php?option=com_osemsc&controller=config'
					,params:{controller:'config',task:'loadDefCanTax'}
					,success: function(response, opt){
						oseMsc.ajaxSuccess(response, opt);
						build.output().getStore().reload();
						build.output().getView().refresh();
					}
					,failure: oseMsc.ajaxFailure
				});
			} 
		})
		
		var grid = build.output();
		grid = Ext.apply(grid,{
			autoExpandColumn: 'country_3_code'
		})

		grid.getStore().load();
		grid.setHeight(600)

		var panel = new Ext.Panel({
			title: Joomla.JText._('Tax')
			,bodyStyle:'padding:10px'
			//,defaults:{bodyStyle:'padding:10px'}
			,items: [grid]
		});

		return panel;
	}
