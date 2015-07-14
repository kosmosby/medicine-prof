Ext.ns('oseMsc','oseMscLevels');

	oseMsc.msg = new Ext.App();
	
	oseMscLevels.gridTbar = new Ext.Toolbar({
		items:[
		'->',{
			html: 'Access Level Name ',
			xtype: 'displayfield',
		},{
			xtype:'textfield',
			name:'level_name',
			id : 'level_name',
		},{
			text: 'Add',
			handler: function()	{
				Ext.Ajax.request({
					url:'index.php?option=com_osemsc&controller=levels',
					params:{task:'add', level_name: Ext.get('level_name').getValue()},
					success: function(response,opt){
						var msg = Ext.decode(response.responseText);
						oseMsc.msg.setAlert(msg.title,msg.content);
						
						if(msg.success)	{
							oseMscLevels.grid.getStore().reload();
							oseMscLevels.grid.getView().refresh();
						}
					}
				});
			}
		}],
	});
	
	oseMscLevels.store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=levels',
	            method: 'POST'
	        }),
		  baseParams:{task: "getList",limit: 20}, 
		  reader: new Ext.data.JsonReader({   
		              // we tell the datastore where to get his data from
		    root: 'results',
		    totalProperty: 'total'
		  },[ 
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'name', type: 'string', mapping: 'name'},
		  ]),
		  //sortInfo:{field: 'id', direction: "ASC"},
		  autoLoad:{},
	});
	
	oseMscLevels.cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [
	        new Ext.grid.RowNumberer({header:'#'}),
		    {id: 'id', header: 'ID', dataIndex: 'id', hidden: true,hideable:true,},
		    {id: 'name', header: 'Access Levels', dataIndex: 'name'},
		    {id: 'action', header: 'Action', dataIndex: 'id',width: 100,},
	    ]
	});
	
	oseMscLevels.grid = new Ext.grid.GridPanel({
		title: 'Access Levels',
		store: oseMscLevels.store,
		
		viewConfig:{forceFit:true},
		cm: oseMscLevels.cm,
		sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
		
		tbar: oseMscLevels.gridTbar,
		bbar: new Ext.PagingToolbar({
    		pageSize: 20,
    		store: oseMscLevels.store,
    		displayInfo: true,
		    displayMsg: 'Displaying topics {0} - {1} of {2}',
		    emptyMsg: "No topics to display",
	    }),
		
		autoHeight: true,
	});