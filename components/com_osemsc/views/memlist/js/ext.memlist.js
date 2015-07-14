Ext.ns('oseMsc');

oseMsc.Memlist = function()	{

}

oseMsc.Memlist.prototype = {
	init: function()	{
		var	store = new Ext.data.Store({
			  proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=memlist',
		            method: 'POST'
		      })
		      ,baseParams:{task: "getMemlist",'msc':osememlist_msc_id,'status':osememlist_status,limit: 20}
			  ,reader: new Ext.data.JsonReader({
			    root: 'results'
			    ,totalProperty: 'total'
			    ,idProperty: 'id'
			  },[
			    {name: 'id', type: 'int', mapping: 'id'}
			    ,{name: 'name', type: 'string', mapping: 'name'}
			    ,{name: 'email', type: 'string', mapping: 'email'}
			    ,{name: 'company', type: 'string', mapping: 'company'}
			    ,{name: 'addr1', type: 'string', mapping: 'addr1'}
			    ,{name: 'addr2', type: 'string', mapping: 'addr2'}
			    ,{name: 'city', type: 'string', mapping: 'city'}
			    ,{name: 'state', type: 'string', mapping: 'state'}
			    ,{name: 'country', type: 'string', mapping: 'country'}
			    ,{name: 'postcode', type: 'string', mapping: 'postcode'}
			    ,{name: 'telephone', type: 'string', mapping: 'telephone'}
			    
			  ])
			  ,autoLoad:{}
			  ,listeners: {
				  beforeload: function(store,records,options)	{

			  	}
			  }
		});

	var	tbar = new Ext.Toolbar({
		    items: ['->','Search '
	        ,new Ext.ux.form.SearchField({
	            store: store,
	            paramName: 'search',
	            width:150
	        })]
		});


	var	grid = new Ext.grid.GridPanel({
			store: store
			,title: 'Member List'
			//,autoHeight: true
			,height:700
			,colModel: new Ext.grid.ColumnModel({
		        defaults: {
		            width: 120,
		            sortable: false
		        },
		        columns: [
		            {id: 'id', header: Joomla.JText._('ID'),  hidden: true, dataIndex: 'id'}
		            ,new Ext.grid.RowNumberer({header:'#'})
		            ,{header: Joomla.JText._('User_Name'), dataIndex: 'name'}
		            ,{header: Joomla.JText._('Email'), dataIndex: 'email'}
		            ,{header: Joomla.JText._('Company'), dataIndex: 'company'}
		            ,{header: Joomla.JText._('Billing_Address'), dataIndex: 'addr1'}
		            ,{header: Joomla.JText._('Billing_Address_Line2'), dataIndex: 'addr2'}
		            ,{header: Joomla.JText._('Billing_City'), dataIndex: 'city'}
		            ,{header: Joomla.JText._('Billing_State'), dataIndex: 'state'}
		            ,{header: Joomla.JText._('Billing_Country'), dataIndex: 'country'}
		            ,{header: Joomla.JText._('Billing_Postal_Code'), dataIndex: 'postcode'}
		            ,{header: Joomla.JText._('Phone'), dataIndex: 'telephone'}
		        ]
		    })

		    ,viewConfig: {forceFit: true}
		    ,sm: new Ext.grid.RowSelectionModel({singleSelect:true})
		    ,tbar: tbar
		    ,bbar: new Ext.PagingToolbar({
	    		pageSize: 20
	    		,store: store
	    		,displayInfo: true
	    		,displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}'
				,emptyMsg: Joomla.JText._("No_topics_to_display")
		    })
		});
	
		return grid;
	}
}

	