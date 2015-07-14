Ext.ns('oseMscAddon','oseMscAddon.history');

	oseMscAddon.history.store = new Ext.data.Store({

		  proxy: new Ext.data.HttpProxy({
	          url: 'index.php?option=com_osemsc&controller=member',
	          method: 'POST'
	      }),
		  baseParams:{task: "action",action: 'member.join_history.getJoinHistory',limit: 20},
		  reader: new Ext.data.JsonReader({
		              // we tell the datastore where to get his data from
		    root: 'results',
		    totalProperty: 'total'
		  },[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'date', type: 'string', mapping: 'date'},
		    {name: 'action', type: 'string', mapping: 'action'}
		  ]),
		  sortInfo:{field: 'id', direction: "ASC"},
		 // autoLoad: {},
		  listeners: {
		 	beforeload: function(store,records,options)	{
			  store.setBaseParam('msc_id',oseMscAddon.join_history.msc_id);
			  store.setBaseParam('member_id',oseMemsMsc.member_id);

		  	}
		  }
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
        }]
	});
	
	oseMscAddon.history.tbar = new Ext.Toolbar({
	    items: ['->', Joomla.JText._('Membership_Plan'),{
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
				  	,baseParams:{task: "action",action: 'member.join_history.getMSCs',member_id:oseMemsMsc.member_id}
				  	,reader: new Ext.data.JsonReader({   
				    	root: 'results'
				    	,totalProperty: 'total'
				    	,idProperty: 'id'
				  	},[ 
				    {name: 'code', type: 'string', mapping: 'id'},
				    {name: 'cname', type: 'string', mapping: 'title'}
				  	])
				  	,autoLoad:{}
				  	,listeners: {
				  		load: function(s,r,i)	{
				  			var comboMscId= oseMscAddon.history.tbar.getComponent('msc_id');
				  			
				    		comboMscId.setValue(r[0].data.code);
				    		
				  			comboMscId.fireEvent('select',comboMscId,r[0],0)
				    	}
				  	}
				})
				
			    ,valueField: 'code'
			    ,displayField: 'cname'
			    ,listeners: {
			    	// delete the previous query in the beforequery event or set
			        // combo.lastQuery = null (this will reload the store the next time it expands)
			        beforequery: function(qe){
			        	//delete qe.combo.lastQuery;
			        },
		
			        select: function(c,r,i)	{
			        	oseMscAddon.join_history.msc_id = r.data.code;
			        	oseMscAddon.join_history.getStore().reload();
				    	}
			    }
			}]
	});

	oseMscAddon.join_history = new Ext.grid.GridPanel({
    	id:'osemsc-history-grid',
    	//title: 'Join History',
        store: oseMscAddon.history.store,
        viewConfig:{forceFit:true},
 
     	colModel:oseMscAddon.history.cm,
     	height: 500,

     	listeners: {
     			activate: function(){
        			oseMscAddon.history.store.reload();
        			seMscAddon.join_history.getView().refresh();
     			}
     	},
     	sm: new Ext.grid.RowSelectionModel({singleSelect:true}),

     	tbar: oseMscAddon.history.tbar,

     	bbar:new Ext.PagingToolbar({
    		pageSize: 20,
    		store: oseMscAddon.history.store,
    		displayInfo: true,
    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
			emptyMsg: Joomla.JText._("No_topics_to_display")
	    })
    });