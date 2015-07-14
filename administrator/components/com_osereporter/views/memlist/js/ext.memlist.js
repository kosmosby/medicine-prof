Ext.ns('oseReporter','oseReporter.Memlist');
	oseReporter.msg = new Ext.App();

	oseReporter.Memlist.createCsv = function(msc,task)	{
		window.open(
			'index.php?option=com_osereporter&controller=memlist&task='+task+'&msc_id='+msc
			,'win1'
			,'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'
		);

		return false;
	}

	oseReporter.Memlist.mscCombo = new Ext.form.ComboBox({
		hiddenName: 'msc_id'
	    ,width: 200
	   	,id: 'mscCombo'
	    ,typeAhead: true
	    ,triggerAction: 'all'
	    ,lazyRender:false
	    ,mode: 'remote'
	    ,lastQuery: ''
	   	,store:new Ext.data.Store({
			proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osereporter&controller=memlist'
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
		                var mscCombo = oseReporter.Memlistgrid.getTopToolbar().findById('mscCombo');
			  			mscCombo.setValue(0);

		    	}
	    	}
		})
		,valueField: 'id'
		,displayField: 'title'

		,listeners: {
	    	select: function(c,r,i)	{
    			var sdate = oseReporter.Memlistgrid.getTopToolbar().findById('sdate').getValue();
    			var edate = oseReporter.Memlistgrid.getTopToolbar().findById('edate').getValue();
    			oseReporter.Memlistgrid.getStore().reload({
					params:{'msc_id':r.data.id,'start_date':sdate,'end_date':edate}
				});
	    	}
	    }

	});

	/*
	oseReporter.Memlist.SdateCombo = new Ext.form.ComboBox({
		hiddenName: 'start_date'
	    ,width: 150
	   	,id: 'SdateCombo'
	    ,typeAhead: true
	    ,triggerAction: 'all'
	    ,lazyRender:false
	    ,mode: 'remote'
	    ,lastQuery: ''
	   	,store:new Ext.data.Store({
			proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osereporter&controller=memlist'
           		,method: 'POST'
	      	})
			,baseParams:{task: "getStartDateList"}
			,reader: new Ext.data.JsonReader({
				root: 'results',
				totalProperty: 'total'
			},[
				{name: 'id', type: 'string', mapping: 'sdate'}
				,{name: 'sdate', type: 'string', mapping: 'sdate'}
			])
			,sortInfo:{field: 'id', direction: "ASC"}
			,autoLoad: {}
			,listeners: {
	    		load: function(s,r,i)	{
					var defaultData = {
		                    id: '0',
		                    sdate: 'Membership Start Date'
	                };
		                var recId = s.getTotalCount(); // provide unique id
		                var p = new s.recordType(defaultData, recId); // create new record
		                s.insert(0,p);
		               // var SdateCombo = oseReporter.Memlistgrid.getTopToolbar().findById('SdateCombo');
			  			//SdateCombo.setValue(0);

		    	}
	    	}
		})
		,valueField: 'id'
		,displayField: 'sdate'

		,listeners: {
	    	select: function(c,r,i)	{
    			var msc = oseReporter.Memlistgrid.getTopToolbar().findById('mscCombo').getValue();
    			var edate = oseReporter.Memlistgrid.getTopToolbar().findById('EdateCombo').getValue();
    			oseReporter.Memlistgrid.getStore().reload({
					params:{'start_date':r.data.id,'msc_id':msc,'end_date':edate}
				});
	    	}
	    }

	});

	oseReporter.Memlist.EdateCombo = new Ext.form.ComboBox({
		hiddenName: 'end_date'
	    ,width: 150
	   	,id: 'EdateCombo'
	    ,typeAhead: true
	    ,triggerAction: 'all'
	    ,lazyRender:false
	    ,mode: 'remote'
	    ,lastQuery: ''
	   	,store:new Ext.data.Store({
			proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osereporter&controller=memlist'
           		,method: 'POST'
	      	})
			,baseParams:{task: "getEndDateList"}
			,reader: new Ext.data.JsonReader({
				root: 'results',
				totalProperty: 'total'
			},[
				{name: 'id', type: 'string', mapping: 'edate'}
				,{name: 'edate', type: 'string', mapping: 'edate'}
			])
			,sortInfo:{field: 'id', direction: "ASC"}
			,autoLoad: {}
			,listeners: {
	    		load: function(s,r,i)	{
					var defaultData = {
		                    id: '0',
		                    edate: 'Membership Expiry Date'
	                };
		                var recId = s.getTotalCount(); // provide unique id
		                var p = new s.recordType(defaultData, recId); // create new record
		                s.insert(0,p);
		                //var EdateCombo = oseReporter.Memlistgrid.getTopToolbar().findById('EdateCombo');
			  			//EdateCombo.setValue(0);

		    	}
	    	}
		})
		,valueField: 'id'
		,displayField: 'edate'

		,listeners: {
	    	select: function(c,r,i)	{
    			var msc = oseReporter.Memlistgrid.getTopToolbar().findById('mscCombo').getValue();
    			var sdate = oseReporter.Memlistgrid.getTopToolbar().findById('SdateCombo').getValue();
    			oseReporter.Memlistgrid.getStore().reload({
					params:{'end_date':r.data.id,'msc_id':msc,'start_date':sdate}
				});
	    	}
	    }

	});

*/
	var oseReporterMemlist_sm = new Ext.grid.CheckboxSelectionModel({
		//singleSelect:true,
		listeners:{
			selectionchange: function(sm,node){
				//oseMigrator.Combinegrid.getTopToolbar().findById('combine').setDisabled(sm.getCount() < 2); // >
			}
		}
	});

	var oseReporterMemlist_store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osereporter&controller=memlist',
	            method: 'POST'
	      }),
		  baseParams:{task: "getList",limit: 25},
		  reader: new Ext.data.JsonReader({

		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'id'
		  },[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'member_id', type: 'int', mapping: 'member_id'},
		    {name: 'firstname', type: 'string', mapping: 'firstname'},
		    {name: 'lastname', type: 'string', mapping: 'lastname'},
		    {name: 'company', type: 'string', mapping: 'company'},
		    {name: 'address1', type: 'string', mapping: 'address1'},
		    {name: 'address2', type: 'string', mapping: 'address2'},
		    {name: 'city', type: 'string', mapping: 'city'},
		    {name: 'state', type: 'string', mapping: 'state'},
		    {name: 'country', type: 'string', mapping: 'country'},
		    {name: 'postcode', type: 'string', mapping: 'postcode'},
		    {name: 'telephone', type: 'string', mapping: 'telephone'},
		    {name: 'username', type: 'string', mapping: 'username'},
		    {name: 'email', type: 'string', mapping: 'email'},
		    {name: 'msc', type: 'string', mapping: 'msc'},
		    {name: 'start_date', type: 'string', mapping: 'start_date'},
		    {name: 'end_date', type: 'string', mapping: 'end_date'},
		    {name: 'create_date', type: 'string', mapping: 'create_date'},
		    {name: 'subtotal', type: 'string', mapping: 'subtotal'},
		    {name: 'tax', type: 'string', mapping: 'tax'},
		    {name: 'total', type: 'string', mapping: 'total'},
		    {name: 'payment_method', type: 'string', mapping: 'payment_method'},
		  ]),
		  //sortInfo:{field: 'user_id', direction: "ASC"},
		  listeners: {
			  beforeload: function(store,records,options)	{
			  	var msc = oseReporter.Memlistgrid.getTopToolbar().findById('mscCombo').getValue();
  	  			var sdate = oseReporter.Memlistgrid.getTopToolbar().findById('sdate').getValue();
  	  			var edate = oseReporter.Memlistgrid.getTopToolbar().findById('edate').getValue();
		   		store.setBaseParam('msc_id',msc);
		  		store.setBaseParam('start_date',sdate);
		  		store.setBaseParam('end_date',edate);
		  	}
		  }
	});


	var oseReporterMemlist_cm = new Ext.grid.ColumnModel({
		defaults:{ sortable: true},
		columns:[
			//oseReporterDaily_sm,
			new Ext.grid.RowNumberer({header:'#'}),
		    {id:'id',header: "id", width: 50, dataIndex: 'id',hidden:true},
	        {header: "User ID",  dataIndex: 'member_id',width:50},
	        {header: "First Name",  dataIndex: 'firstname'},
	        {header: "Last Name",  dataIndex: 'lastname'},
	        {header: "Company",  dataIndex: 'company'},
	        {header: "Address1",  dataIndex: 'address1'},
	        {header: "Address2",  dataIndex: 'address2'},
	        {header: "City",  dataIndex: 'city'},
	        {header: "State",  dataIndex: 'state'},
	        {header: "Country",  dataIndex: 'country'},
	        {header: "Zip",  dataIndex: 'postcode'},
	        {header: "Phone",  dataIndex: 'telephone'},
	        {header: "User Name", dataIndex: 'username'},
	        {header: "email", dataIndex: 'email'},
	        {header: "Membership Plan", dataIndex: 'msc'},
	        {header: "Start Date", dataIndex: 'start_date'},
	        {header: "End Date", dataIndex: 'end_date'},
	        {header: "Purchase Date", dataIndex: 'create_date'},
	        {header: "Subtotal", dataIndex: 'subtotal'},
	        {header: "Tax", dataIndex: 'tax'},
	        {header: "Total", dataIndex: 'total'},
	        {header: "Payment Method", dataIndex: 'payment_method'}
	  	]
  	});

	oseReporter.Memlistgrid = new Ext.grid.GridPanel({
		//title: 'Menu',
		listeners:{
			render: function(p)	{
				oseReporterMemlist_store.load();
			}
		},
		autoScroll:true,
		height: 400,
		viewConfig:{forceFit: true},

		store: oseReporterMemlist_store,
		sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
		cm: oseReporterMemlist_cm,

		bbar: new Ext.PagingToolbar({
    		pageSize: 25,
    		store: oseReporterMemlist_store,
    		plugins: new Ext.ux.grid.limit({}),
    		displayInfo: true,
		    displayMsg: 'Displaying topics {0} - {1} of {2}',
		    emptyMsg: "No topics to display"
	    }),

	    tbar:['->'
	          ,oseReporter.Memlist.mscCombo
	          ,{
				xtype:'datefield'
				,id:'sdate'
				,width:150
				,emptyText:'Membership Start Date'
				,name:'start_date'
				,format: 'Y-m-d'
				,listeners: {
			    	select: function(c,r,i)	{
	        	  		var msc = oseReporter.Memlistgrid.getTopToolbar().findById('mscCombo').getValue();
	        	  		var sdate = oseReporter.Memlistgrid.getTopToolbar().findById('sdate').getValue();
	        	  		var edate = oseReporter.Memlistgrid.getTopToolbar().findById('edate').getValue();
	        	  		oseReporter.Memlistgrid.getStore().reload({
							params:{'end_date':edate,'msc_id':msc,'start_date':sdate}
						});
			    	}
			    }
			  },{
				xtype:'datefield'
				,id:'edate'
				,width:150
				,emptyText:'Membership Expiry Date'
				,format: 'Y-m-d'
				,name:'end_date'
				,listeners: {
			    	select: function(c,r,i)	{
	        	  		var msc = oseReporter.Memlistgrid.getTopToolbar().findById('mscCombo').getValue();
	        	  		var sdate = oseReporter.Memlistgrid.getTopToolbar().findById('sdate').getValue();
	        	  		var edate = oseReporter.Memlistgrid.getTopToolbar().findById('edate').getValue();
	        	  		oseReporter.Memlistgrid.getStore().reload({
							params:{'end_date':edate,'msc_id':msc,'start_date':sdate}
						});
			    	}
			    }
			  },{
			    	text: 'Reset',
			    	handler: function()	{
				  		var msc = oseReporter.Memlistgrid.getTopToolbar().findById('mscCombo').setValue(0);
				  		var sdate = oseReporter.Memlistgrid.getTopToolbar().findById('sdate').setValue();
				  		var edate = oseReporter.Memlistgrid.getTopToolbar().findById('edate').setValue();
				  		oseReporter.Memlistgrid.getStore().reload({
							params:{'end_date':'','msc_id':'','start_date':''}
						});
			  		}
			  },{
		    	text: 'Export to CSV',
		    	id:'export',
		    	handler: function()	{
			    	if(!memlistExportWin)	{
						var memlistExportWin = new Ext.Window({
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
							    		url: 'index.php?option=com_osereporter&controller=memlist'
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
								    			var mscCombo = memlistExportWin.form.findById('msc');
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
							   		var msc = memlistExportWin.form.findById('msc').getValue();
							   		//oseReporter.Dailygrid.setDisabled(true);
							   		oseReporter.Memlist.createCsv(msc,'exportCsv');
							   		memlistExportWin.close();
							   	}
							   }]
							}]
						})

						memlistExportWin.show().alignTo(Ext.getBody(),'t-t')
					}
		    	}
		    },{
		    	text: 'Export to CSV(Including Additional Info)',
		    	id:'exportAll',
		    	handler: function()	{
			    	if(!memlistExportWin)	{
						var memlistExportWin = new Ext.Window({
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
							    	,hiddenName: 'msc_id'
							    	,width: 200
							    	,fieldLabel:'Membership Plan'
							    	,id: 'msc2'
							    	,typeAhead: true
							    	,triggerAction: 'all'
							    	,lazyRender:false
							    	,mode: 'remote'
							    	,lastQuery: ''
							    	,store:new Ext.data.Store({
							    		proxy: new Ext.data.HttpProxy({
							    		url: 'index.php?option=com_osereporter&controller=memlist'
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
								    			var mscCombo = memlistExportWin.form.findById('msc2');
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
							   		var msc = memlistExportWin.form.findById('msc2').getValue();
							   		//oseReporter.Dailygrid.setDisabled(true);
							   		oseReporter.Memlist.createCsv(msc,'exportCsvAll');
							   		memlistExportWin.close();
							   	}
							   }]
							}]
						})

						memlistExportWin.show().alignTo(Ext.getBody(),'t-t')
					}
		    	}
		    }]
	});