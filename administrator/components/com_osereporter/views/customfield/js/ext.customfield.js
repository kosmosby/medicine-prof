Ext.onReady(function(){
Ext.ns('oseReporter','oseReporter.Customfield');
	oseReporter.msg = new Ext.App();

	oseReporter.Customfield.createCsv = function()	{
		window.open(
			'index.php?option=com_osereporter&controller=customfield&task=exportCsv'
			,'win1'
			,'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'
		);

		return false;
	}

	oseReporter.Customfield.mscCombo = new Ext.form.ComboBox({
		hiddenName: 'msc_id'
		,hidden:true	
	    ,width: 200
	   	,id: 'mscCombo'
	    ,typeAhead: true
	    ,triggerAction: 'all'
	    ,lazyRender:false
	    ,mode: 'remote'
	    ,lastQuery: ''
	   	,store:new Ext.data.Store({
			proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osereporter&controller=customfield'
           		,method: 'POST'
	      	})
			,baseParams:{task: "getMscList"}
			,reader: new Ext.data.JsonReader({
				root: 'results',
				totalProperty: 'total'
			},[
				{name: 'id', type: 'string', mapping: 'id'}
				,{name: 'title', type: 'string', mapping: 'title'}
			])
			,sortInfo:{field: 'id', direction: "ASC"}
			,autoLoad: {}
			,listeners: {
	    		load: function(s,r,i)	{
					var defaultData = {
		                    id: '0',
		                    title: 'Select a Membership Plan'
	                };
		                var recId = s.getTotalCount(); // provide unique id
		                var p = new s.recordType(defaultData, recId); // create new record
		                s.insert(0,p);
		                var mscCombo = oseReporter.Customfieldgrid.getTopToolbar().findById('mscCombo');
			  			mscCombo.setValue(0);

		    	}
	    	}
		})
		,valueField: 'id'
		,displayField: 'title'

		,listeners: {
	    	select: function(c,r,i)	{
    			var sdate = oseReporter.Customfieldgrid.getTopToolbar().findById('sdate').getValue();
    			var edate = oseReporter.Customfieldgrid.getTopToolbar().findById('edate').getValue();
    			oseReporter.Customfieldgrid.getStore().reload({
					params:{'msc_id':r.data.id,'start_date':sdate,'end_date':edate}
				});
	    	}
	    }

	});

	var oseReporterCustomfield_sm = new Ext.grid.CheckboxSelectionModel({
		//singleSelect:true,
		listeners:{
			selectionchange: function(sm,node){
				//oseMigrator.Combinegrid.getTopToolbar().findById('combine').setDisabled(sm.getCount() < 2); // >
			}
		}
	});

	var oseReporterCustomfield_store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osereporter&controller=customfield',
	            method: 'POST'
	      }),
		  baseParams:{task: "getList",limit: 25},
		  reader: new Ext.data.JsonReader({

		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'id'
		  },store),
		  //sortInfo:{field: 'user_id', direction: "ASC"},
		  listeners: {
			  beforeload: function(store,records,options)	{
			  	var msc = oseReporter.Customfieldgrid.getTopToolbar().findById('mscCombo').getValue();
  	  			var sdate = oseReporter.Customfieldgrid.getTopToolbar().findById('sdate').getValue();
  	  			var edate = oseReporter.Customfieldgrid.getTopToolbar().findById('edate').getValue();
		   		store.setBaseParam('msc_id',msc);
		  		store.setBaseParam('start_date',sdate);
		  		store.setBaseParam('end_date',edate);
		  	}
		  }
	});

	
	var oseReporterCustomfield_cm = new Ext.grid.ColumnModel({
		defaults:{ sortable: true},
		columns:column
  	});

	oseReporter.Customfieldgrid = new Ext.grid.GridPanel({
		//title: 'Menu',
		listeners:{
			render: function(p)	{
				oseReporterCustomfield_store.load();
			}
		},
		autoScroll:true,
		height: 400,
		viewConfig:{forceFit: true},

		store: oseReporterCustomfield_store,
		sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
		cm: oseReporterCustomfield_cm,

		bbar: new Ext.PagingToolbar({
    		pageSize: 25,
    		store: oseReporterCustomfield_store,
    		plugins: new Ext.ux.grid.limit({}),
    		displayInfo: true,
		    displayMsg: 'Displaying topics {0} - {1} of {2}',
		    emptyMsg: "No topics to display"
	    }),

	    tbar:['->'
	          ,oseReporter.Customfield.mscCombo
	          ,{
				xtype:'datefield'
				,hidden:true
				,id:'sdate'
				,width:150
				,emptyText:'Membership Start Date'
				,name:'start_date'
				,format: 'Y-m-d'
				,listeners: {
			    	select: function(c,r,i)	{
	        	  		var msc = oseReporter.Customfieldgrid.getTopToolbar().findById('mscCombo').getValue();
	        	  		var sdate = oseReporter.Customfieldgrid.getTopToolbar().findById('sdate').getValue();
	        	  		var edate = oseReporter.Customfieldgrid.getTopToolbar().findById('edate').getValue();
	        	  		oseReporter.Customfieldgrid.getStore().reload({
							params:{'end_date':edate,'msc_id':msc,'start_date':sdate}
						});
			    	}
			    }
			  },{
				xtype:'datefield'
				,hidden:true
				,id:'edate'
				,width:150
				,emptyText:'Membership Expiry Date'
				,format: 'Y-m-d'
				,name:'end_date'
				,listeners: {
			    	select: function(c,r,i)	{
	        	  		var msc = oseReporter.Customfieldgrid.getTopToolbar().findById('mscCombo').getValue();
	        	  		var sdate = oseReporter.Customfieldgrid.getTopToolbar().findById('sdate').getValue();
	        	  		var edate = oseReporter.Customfieldgrid.getTopToolbar().findById('edate').getValue();
	        	  		oseReporter.Customfieldgrid.getStore().reload({
							params:{'end_date':edate,'msc_id':msc,'start_date':sdate}
						});
			    	}
			    }
			  },{
			    	text: 'Reset',
			    	hidden:true,
			    	handler: function()	{
				  		var msc = oseReporter.Customfieldgrid.getTopToolbar().findById('mscCombo').setValue(0);
				  		var sdate = oseReporter.Customfieldgrid.getTopToolbar().findById('sdate').setValue();
				  		var edate = oseReporter.Customfieldgrid.getTopToolbar().findById('edate').setValue();
				  		oseReporter.Customfieldgrid.getStore().reload({
							params:{'end_date':'','msc_id':'','start_date':''}
						});
			  		}
			  },{
		    	text: 'Export to CSV',
		    	id:'export',
		    	handler: function()	{
		    		oseReporter.Customfield.createCsv();
		    	}
		    		/*
			    	if(!customfieldExportWin)	{
						var customfieldExportWin = new Ext.Window({
							title: 'Export Report'
							,width: 600
							,autoHeight: true
							,items:[{
								xtype: 'form'
								,border: false
								,ref: 'form'
								,bodyStyle: 'padding: 10px'
								,labelWidth: 200
								,defaults: {width: 200}
								,height: 200

						    	,items: [{
						    		xtype: 'combo'
						    		,hidden:true
							    	,hiddenName: 'msc_id'
							    	,width: 200
							    	,fieldLabel:'Membership Plan'
							    	,id: 'msc'
							    	,typeAhead: true
							    	,triggerAction: 'all'
							    	,lazyRender:false
							    	,mode: 'remote'
							    	,lastQuery: ''
							    	,store:new Ext.data.Store({
							    		proxy: new Ext.data.HttpProxy({
							    		url: 'index.php?option=com_osereporter&controller=customfield'
							    		,method: 'POST'
							    		})
								    	,baseParams:{task: "getMscList"}
								    	,reader: new Ext.data.JsonReader({
								    		root: 'results',
								    		totalProperty: 'total'
								    	},[
								    		{name: 'id', type: 'string', mapping: 'id'}
								    		,{name: 'title', type: 'string', mapping: 'title'}
								    	])
								    	,sortInfo:{field: 'id', direction: "ASC"}
								    	,autoLoad: {}
								    	,listeners: {
								    		load: function(s,r,i)	{
								    			var defaultData = {
								    				id: '0',
								    			    title: 'All'
								    	        };
								    	        var recId = s.getTotalCount(); // provide unique id
								    			var p = new s.recordType(defaultData, recId); // create new record
								    			s.insert(0,p);
								    			var mscCombo = customfieldExportWin.form.findById('msc');
								    		    mscCombo.setValue(0);
								    	   	}
								    	}
							    	})
							    	,valueField: 'id'
							    	,displayField: 'title'
							    }]

							,buttons: [{
							   	text: 'Export'
							   	,handler: function()	{
							   		var msc = customfieldExportWin.form.findById('msc').getValue();
							   		//oseReporter.Dailygrid.setDisabled(true);
							   		oseReporter.Customfield.createCsv(msc);
							   		customfieldExportWin.close();
							   	}
							   }]
							}]
						})

						customfieldExportWin.show().alignTo(Ext.getBody(),'t-t')
					}
		    	}
			  */
		    }]
	});
	
	oseReporter.panel = new Ext.Panel({
		//activeItem: 0
		//renderTo: 'ose-reporter'
		width: Ext.get('ose-reporter').getWidth() - 15
		,items:	oseReporter.Customfieldgrid
	});
	oseReporter.panel.render('ose-reporter');
});