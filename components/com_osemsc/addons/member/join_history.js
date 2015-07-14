Ext.ns('oseMscAddon','oseMscAddon.history');

	oseMscAddon.history.store = new Ext.data.Store({

		  proxy: new Ext.data.HttpProxy({
	          url: 'index.php?option=com_osemsc&controller=member',
	          method: 'POST'
	      }),
		  baseParams:{task: "action",action: 'member.join_history.getJoinHistory'},
		  reader: new Ext.data.JsonReader({
		  
		    root: 'results',
		    totalProperty: 'total'
		  },[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'date', type: 'string', mapping: 'date'},
		    {name: 'action', type: 'string', mapping: 'action'}
		  ]),
		  sortInfo:{field: 'id', direction: "DESC"}
	});
	
	oseMscAddon.history.cm = new Ext.grid.ColumnModel({
        defaults: {
            width: 200,
            sortable: true
        },
        columns: [
	        new Ext.grid.RowNumberer({header:'#'}),
	    {
	    	id: 'id',
            header: Joomla.JText._('ID'),
            dataIndex: 'id',
            hidden: true
            //hideable:false,
	    },{
            id: 'date',
            header: Joomla.JText._('Join_Membership_Date'),
            dataIndex: 'date'
        },{
            id: 'action',
            header: Joomla.JText._('Action'),
            dataIndex: 'action'
            ,renderer: function(val)	{
            	if(val =='cancellOrder' || val =='cancelOrder')	{
            		return Joomla.JText._('cancelled');
            	}	else	{
            		return Joomla.JText._(val);
            	}
            }
        }]
	});
	
	oseMscAddon.history.tbar = new Ext.Toolbar({
	    items: ['->', Joomla.JText._('Membership_Plans')
	    ,{
			hiddenName: 'msc_id'
            ,itemId: 'msc_id'
            ,id: 'membership'
            ,xtype: 'combo'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    ,lastQuery: ''
		    ,lazyInit: false
		    ,mode: 'remote'
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=member',
		            method: 'POST'
	      		})
			  	,baseParams:{task: "action",action: 'member.join_history.getMSCs'}
			  	,reader: new Ext.data.JsonReader({   
			    	root: 'results'
			    	,totalProperty: 'total'
			    	,idProperty: 'id'
			  	},[ 
			    {name: 'code', type: 'string', mapping: 'id'},
			    {name: 'cname', type: 'string', mapping: 'title'}
			  	])
			  	,listeners: {
			  		load: function(s,r,i)	{
			  			var comboMscId= oseMscAddon.history.tbar.getComponent('msc_id');
			    		comboMscId.setValue(r[0].get('code'));
			  			comboMscId.fireEvent('select',comboMscId,r[0],0)
			    	}
			  	}
			})
		    ,valueField: 'code'
		    ,displayField: 'cname'
		    ,listeners: {
		        select: function(c,r,i)	{
		        	//oseMscAddon.join_history.msc_id = r.data.code;
		        	oseMscAddon.history.store.setBaseParam('msc_id',r.get('code'));
		        	oseMscAddon.join_history.getBottomToolbar().doRefresh();
			    }
		    }
		}]
	});

	oseMscAddon.join_history = new Ext.grid.GridPanel({
    	id:'osemsc-history-grid',
    	//title: 'Billing History',
        store: oseMscAddon.history.store,
        viewConfig:{forceFit:true},
     	colModel:oseMscAddon.history.cm,
     	height: 500,
     	sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
     	tbar: oseMscAddon.history.tbar,
     	bbar:new Ext.PagingToolbar({
    		pageSize: 20,
    		store: oseMscAddon.history.store,
    		displayInfo: true,
	    	displayMsg: Joomla.JText._('Displaying_items')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
		    emptyMsg: Joomla.JText._("No_items_to_display")
		    ,plugins: new Ext.ux.grid.limit({})
	    })
    });
    
    oseMscAddon.history.tbar.getComponent('msc_id').getStore().load();