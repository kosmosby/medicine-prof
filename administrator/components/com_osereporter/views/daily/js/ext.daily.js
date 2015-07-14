Ext.ns('oseReporter','oseReporter.Daily');
	oseReporter.msg = new Ext.App();

	oseReporter.Daily.createCsv = function(msc,month,year)	{
		
		window.open(
			'index.php?option=com_osereporter&controller=daily&task=exportCsv&msc_id='+msc+'&month='+month+'&year='+year
			,'win1'
			,'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'
		);

		return false;
	}

	oseReporter.Daily.mscCombo = new Ext.form.ComboBox({
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
	            url: 'index.php?option=com_osereporter&controller=daily'
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
		                //var mscCombo = oseReporter.Dailygrid.getTopToolbar().findById('mscCombo');
		                oseReporter.Daily.mscCombo.setValue(0);

		    	}
	    	}
		})
		,valueField: 'id'
		,displayField: 'title'

		,listeners: {
	    	select: function(c,r,i)	{
    			var month = oseReporter.Dailygrid.getTopToolbar().findById('monthCombo').getValue();
    			var year = oseReporter.Dailygrid.getTopToolbar().findById('yearCombo').getValue();
    			oseReporter.Dailygrid.getStore().reload({
					params:{'msc_id':r.data.id,'month':month,'year':year}
				});
	    	}
	    }

	});

	oseReporter.Daily.monthCombo = new Ext.form.ComboBox({
		width: 100,
    	id:'monthCombo',
    	hiddenName: 'month',
    	typeAhead: true,
	    triggerAction: 'all',
	    lazyRender:true,
	    mode: 'local',
	    store: new Ext.data.ArrayStore({
	        id: 0,
	        fields: [
	            'id','month'
	        ],
	        data: [
	        	['0','Select a Month'],
	        	['1','January'],
                ['2','February'],
	        	['3','March'],
	        	['4','April'],
	        	['5','May'],
	        	['6','June'],
	        	['7','July'],
	        	['8','August'],
	        	['9','September'],
	        	['10','October'],
	        	['11','November'],
	        	['12','December']
	        ]
	    }),
	    valueField: 'id',
	    displayField: 'month',

	    listeners: {
	        // delete the previous query in the beforequery event or set
	        // combo.lastQuery = null (this will reload the store the next time it expands)
	        beforequery: function(qe){
	            delete qe.combo.lastQuery;
	        },
			afterrender: function(e)	{
				e.setValue('0');
			},
			select: function(c,r,i)	{
				var msc_id = oseReporter.Dailygrid.getTopToolbar().findById('mscCombo').getValue();
				var year = oseReporter.Dailygrid.getTopToolbar().findById('yearCombo').getValue();
				oseReporter.Dailygrid.getStore().reload({
					params:{'month':r.data.id,'msc_id':msc_id,'year':year}
				});
    		}
        }

	});

	oseReporter.Daily.yearCombo = new Ext.form.ComboBox({
		hiddenName: 'year'
	    ,width: 200
	   	,id: 'yearCombo'
	    ,typeAhead: true
	    ,triggerAction: 'all'
	    ,lazyRender:false
	    ,mode: 'remote'
	    ,lastQuery: ''
	   	,store:new Ext.data.Store({
			proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osereporter&controller=daily'
           		,method: 'POST'
	      	})
			,baseParams:{task: "getYears"}
			,reader: new Ext.data.JsonReader({
				root: 'results',
				totalProperty: 'total'
			},[
			   	{name: 'id', type: 'string', mapping: 'date'},
				{name: 'date', type: 'string', mapping: 'date'}
			])
			,sortInfo:{field: 'id', direction: "ASC"}
			,autoLoad: {}
			,listeners: {
	    		load: function(s,r,i)	{
					var defaultData = {
		                    id: '0',
		                    date: 'Select a Year'
	                };
		                var recId = s.getTotalCount(); // provide unique id
		                var p = new s.recordType(defaultData, recId); // create new record
		                s.insert(0,p);
		                //var mscCombo = oseReporter.Dailygrid.getTopToolbar().findById('yearCombo');
		                oseReporter.Daily.yearCombo.setValue(0);

		    	}
	    	}
		})
		,valueField: 'id'
		,displayField: 'date'

		,listeners: {
	    	select: function(c,r,i)	{
    			var msc_id = oseReporter.Dailygrid.getTopToolbar().findById('mscCombo').getValue();
    			var month = oseReporter.Dailygrid.getTopToolbar().findById('monthCombo').getValue()
    			oseReporter.Dailygrid.getStore().reload({
					params:{'year':r.data.id,'msc_id':msc_id,'month':month}
				});
	    	}
	    }

	});

	var oseReporterDaily_sm = new Ext.grid.CheckboxSelectionModel({
		//singleSelect:true,
		listeners:{
			selectionchange: function(sm,node){
				//oseMigrator.Combinegrid.getTopToolbar().findById('combine').setDisabled(sm.getCount() < 2); // >
			}
		}
	});

	var oseReporterDaily_store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osereporter&controller=daily',
	            method: 'POST'
	      }),
		  baseParams:{task: "getList",limit: 25},
		  reader: new Ext.data.JsonReader({

		    root: 'results',
		    totalProperty: 'total',
		    idProperty: 'id'
		  },[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'msc_id', type: 'int', mapping: 'msc_id'},
		    {name: 'date', type: 'string', mapping: 'date'},
		    {name: 'msc', type: 'string', mapping: 'msc'},
		    {name: 'newmem', type: 'string', mapping: 'newmem'},
		    {name: 'expmem', type: 'string', mapping: 'expmem'},
		    {name: 'ratio', type: 'string', mapping: 'ratio'},
		    {name: 'profits', type: 'string', mapping: 'profits'},
		    {name: 'tax', type: 'string', mapping: 'tax'}
		  ]),
		  //sortInfo:{field: 'user_id', direction: "ASC"},
		  listeners: {
			  beforeload: function(store,records,options)	{
			  	var month = oseReporter.Dailygrid.getTopToolbar().findById('monthCombo').getValue();
		   		var msc = oseReporter.Dailygrid.getTopToolbar().findById('mscCombo').getValue();
		   		store.setBaseParam('msc_id',msc);
		  		store.setBaseParam('month',month);
		  	}
		  }
	});

	var expander = new Ext.grid.RowExpander({
        tpl : new Ext.XTemplate(
        '<div class="detailData">',
        '',
        '</div>'
        )
    }); 
	
	expander.on("expand",function(expander,r,body,rowIndex){
		window.testEle=body;
		var msc_id = r.get('msc_id');
		var date = r.get('date');
		if (Ext.DomQuery.select("div.x-panel-bwrap",body).length==0){
		      //alert("a");
		      var data=r.json[3];
		      var store = new Ext.data.Store({
				  proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osereporter&controller=daily',
			            method: 'POST'
			      }),
				  baseParams:{task: "getOptions",'msc_id':msc_id,'date':date,limit: 25}, 
				  reader: new Ext.data.JsonReader({   
				            
				    root: 'results',
				    totalProperty: 'total',
				    idProperty: 'option_id'
				  },[ 
				    {name: 'option_id', type: 'string', mapping: 'option_id'},
				    {name: 'option_name', type: 'string', mapping: 'option_name'},
				    {name: 'msc', type: 'string', mapping: 'msc'},
				    {name: 'newmem', type: 'string', mapping: 'newmem'},
				    {name: 'expmem', type: 'string', mapping: 'expmem'},
				    {name: 'ratio', type: 'string', mapping: 'ratio'},
				    {name: 'profits', type: 'string', mapping: 'profits'},
				    {name: 'tax', type: 'string', mapping: 'tax'}
				  ]),
				  autoLoad: {}
			});
		      
		    var cm = new Ext.grid.ColumnModel([
		      //new Ext.grid.RowNumberer({header:'#'}),
		      {header: "Option ID",dataIndex: 'option_id',hidden:true},
		      {header: "Payment Option",dataIndex: 'option_name'},
		      {header: "Membership Plan",dataIndex: 'msc',hidden:true,sortable:false,resizable:true},
		      {header: "New Members",  dataIndex: 'newmem'},
		      {header: "Expired Members",  dataIndex: 'expmem'},
		      {header: "Ratio",  dataIndex: 'ratio'},
		      {header: "Profits",  dataIndex: 'profits'},
		      {header: "Tax",  dataIndex: 'tax'}
		   ]);
		    
		   Ext.DomQuery.select("div.detailData")[0];
		   
		   var grid = new Ext.grid.GridPanel({
			 bodyStyle : 'padding: 10px',
			 viewConfig:{forceFit: true},
		     store: store,
		     cm:cm,
		     renderTo:Ext.DomQuery.select("div.detailData",body)[0],
		     autoWidth:true,
		     autoHeight:true
		   });
		  
		}
	}); 
	
	var oseReporterDaily_cm = new Ext.grid.ColumnModel({
		defaults:{ sortable: true},
		columns:[
			//oseReporterDaily_sm,
			//new Ext.grid.RowNumberer({header:'#'}),
			expander,
		    {id:'id',header: "ID", width: 200, dataIndex: 'id',hidden:true},
	        {header: "Date",  dataIndex: 'date'},
	        {header: "Membership Plan",  dataIndex: 'msc'},
	        {header: "New Members",  dataIndex: 'newmem'},
	        {header: "Expired Members",  dataIndex: 'expmem'},
	        {header: "Ratio",  dataIndex: 'ratio'},
	        {header: "Profit", width:200, dataIndex: 'profits'},
	        {header: "Tax", width:200, dataIndex: 'tax'}
	  	]
  	});

	oseReporter.Dailygrid = new Ext.grid.GridPanel({
		//title: 'Menu',
		listeners:{
			render: function(p)	{
				oseReporterDaily_store.load();
			}
		},
		autoScroll:true,
		height: 800,
		viewConfig:{forceFit: true},
		plugins:[expander],
		store: oseReporterDaily_store,
		sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
		cm: oseReporterDaily_cm,

		bbar: new Ext.PagingToolbar({
    		pageSize: 25,
    		store: oseReporterDaily_store,
    		plugins: new Ext.ux.grid.limit({}),
    		displayInfo: true,
		    displayMsg: 'Displaying topics {0} - {1} of {2}',
		    emptyMsg: "No topics to display"
	    }),

	    tbar:['->'
	          ,oseReporter.Daily.mscCombo
	          ,oseReporter.Daily.monthCombo
	          ,oseReporter.Daily.yearCombo
	          ,{
		    	text: 'Export to CSV',
		    	id:'export',
		    	handler: function()	{
			    	if(!dailyExportWin)	{
						var dailyExportWin = new Ext.Window({
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
							    		url: 'index.php?option=com_osereporter&controller=daily'
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
								    			var mscCombo = dailyExportWin.form.findById('msc');
								    		    mscCombo.setValue(0);
								    	   	}
								    	}
							    	})
							    	,valueField: 'id'
							    	,displayField: 'title'
							    },{
						    		xtype: 'combo'
								   	,hiddenName: 'year'
								   	,width: 200
								   	,fieldLabel:'Year'
								   	,id: 'yearCom'
								   	,typeAhead: true
								   	,triggerAction: 'all'
								   	,lazyRender:false
								   	,mode: 'remote'
								   	,lastQuery: ''
								   	,store:new Ext.data.Store({
								   		proxy: new Ext.data.HttpProxy({
								   		url: 'index.php?option=com_osereporter&controller=daily'
								   		,method: 'POST'
								   		})
									   	,baseParams:{task: "getYears"}
									   	,reader: new Ext.data.JsonReader({
									   		root: 'results',
									   		totalProperty: 'total'
									   	},[
									   		{name: 'id', type: 'string', mapping: 'date'}
									  		,{name: 'date', type: 'string', mapping: 'date'}
									   	])
									   	,sortInfo:{field: 'id', direction: "ASC"}
									   	,autoLoad: {}
									   	,listeners: {
									   		load: function(s,r,i)	{
									   			var defaultData = {
									   				id: '0',
									   			    date: 'All'
									   	        };
									   	        var recId = s.getTotalCount(); // provide unique id
									   			var p = new s.recordType(defaultData, recId); // create new record
									   			s.insert(0,p);
									   			var yearCombo = dailyExportWin.form.findById('yearCom');
									   		    yearCombo.setValue(0);
									   	   	}
									   	}
								    })
								    ,valueField: 'id'
								    ,displayField: 'date'
								},{
						    		xtype: 'combo'
						    		,fieldLabel:'Month'
						    		,width: 200
						    	 	,id:'monthCom'
						    	   	,hiddenName: 'month'
						    	  	,typeAhead: true
						    	    ,triggerAction: 'all'
						    	    ,lazyRender:true
						    	    ,mode: 'local'
						    	    ,store: new Ext.data.ArrayStore({
						    	        id: 0
						    	        ,fields: [
						    	            'id','month'
						    	        ]
						    	        ,data: [
						    	        	['0','All']
						    	        	,['1','January']
						    	            ,['2','February']
						    	            ,['3','March']
						    			    ,['4','April']
						    			    ,['5','May']
						    			    ,['6','June']
						    			    ,['7','July']
						    			    ,['8','August']
						    			    ,['9','September']
						    			    ,['10','October']
						    			    ,['11','November']
						    			    ,['12','December']
						    			]
						    	    })
						    		,valueField: 'id'
						    		,displayField: 'month'

						    		,listeners: {
						    	        beforequery: function(qe){
						    	            delete qe.combo.lastQuery;
						    	        }
						    			,afterrender: function(e)	{
						    				e.setValue('0');
						    			}
						            }
						    	}]

							,buttons: [{
							   	text: 'Export'
							   	,handler: function()	{
							   		var year = dailyExportWin.form.findById('yearCom').getValue();
							   		var month = dailyExportWin.form.findById('monthCom').getValue();
							   		var msc = dailyExportWin.form.findById('msc').getValue();

							   		oseReporter.Daily.createCsv(msc,month,year);
							   		dailyExportWin.close();
							   	}
							   }]
							}]
						})

						dailyExportWin.show().alignTo(Ext.getBody(),'c-c')
					}
		    	}
		    }]
	});