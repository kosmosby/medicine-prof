Ext.ns('oseMemsMsc');
	
	oseMemsMsc.MscTree = function()	{
		this.sm = new Ext.grid.RowSelectionModel({
			singleSelect: true
		})
		
		this.cm = new Ext.grid.ColumnModel({
	        defaults: {
	            width: 20,
	            sortable: false
	        },
	        columns: [
	        	{id: 'id', header: Joomla.JText._('ID'),  hidden:true, dataIndex: 'id'}
	            ,{header: Joomla.JText._('Title'), id: 'title',dataIndex: 'title',sortable: true, width: 100}
	            ,{header: Joomla.JText._('Total'), dataIndex: 'total',sortable: true, width: 50}
	        ]
	    })
	    
	    this.store = new Ext.data.Store({
		  	proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=memberships',
	            method: 'POST'
	        })
		  	,baseParams:{task: "getFullTree",limit: 20}
		  	,reader: new Ext.data.JsonReader({   
		     	root: 'results',
		     	totalProperty: 'total'
		  	},[ 
			    {name: 'id', type: 'int', mapping: 'id'}
			    ,{name: 'title', type: 'string', mapping: 'treename'}
			    ,{name: 'total', type: 'int', mapping: 'total'}
		  	])
	  		,listeners: {
			  	load: function(s,r)	{
			  		var totalSum = new Array();
			  		Ext.each(r,function(item,i,all)	{
			  			totalSum[i] = item.get('total');
			  		})
			  		var defaultData = {
	                    id: 0,
	                    title: 'All',
	                    total: Ext.sum(totalSum)
	                };
	                var recId = s.getTotalCount(); // provide unique id
	                var p = new s.recordType(defaultData, 0); // create new record

	                s.insert(0,p);
			  	}
		  	}
		})
	}
	
	oseMemsMsc.MscTree.prototype = {
		init: function()	{
			var sm = this.sm;
			var cm = this.cm;
			var store = this.store;
			
			var grid = new Ext.grid.GridPanel({
				title: Joomla.JText._('Membership_List')
				,id: 'ose-msc-list'
				,margins: {top:5, right:3, bottom:5, left:5}
				,store: store
				,region: 'west'
				,autoExpandColumn: 'title'
				,width: 300
				,height: 500
				,cm: cm
			  	,sm: sm
			  	,bbar:new Ext.PagingToolbar({
					pageSize: 20
					,store: store
					,displayInfo: true
				    ,displayMsg: ''//'Displaying items {0} - {1} of {2}'
				    ,emptyMsg: ''//"No items to display"
			    })
			});
			
			grid.getStore().load();
			return grid;
		} 
	}