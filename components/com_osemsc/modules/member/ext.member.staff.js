Ext.ns('oseMemMsc');
	oseMemMsc.msg = new Ext.App();
	
	oseMemMsc.openAddon = function(dv, i,node, e, n)	{
		
		if(!memAddonWin)	{
			var memAddonWin = new Ext.Window({
				title: n.type.toUpperCase()+'-'+n.title
				,id: 'mem-addon-win'
				,defaults: {border:false}
				,layout: 'fit'
				,modal: true
				,width: 800
				,autoHeight: true
				,autoLoad:{
					url: 'index.php?option=com_osemsc&controller=member'
					,params:{ task:'getAddon',addon_name:n.name,addon_type:n.type }
					,scripts: true
					,callback: function(el ,success, response, options)	{
						memAddonWin.add(eval('oseMscAddon.'+n.name));
						memAddonWin.doLayout();
					}
				}
			});
		}
		
		memAddonWin.show(this);
		memAddonWin.alignTo(Ext.getBody(),'t-t');
	}
	
    oseMemMsc.extStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=member',
	            method: 'POST'
	      }),
		  baseParams:{task: "getAddons"}, 
		  reader: new Ext.data.JsonReader({   
		    root: 'results',
		    totalProperty: 'total'
		  },[ 
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'title', type: 'string', mapping: 'title'},
		    {name: 'name', type: 'string', mapping: 'name'},
		    {name: 'type', type: 'string', mapping: 'type'},
		    {name: 'addon_name', type: 'string', mapping: 'addon_name'}
		  ])
		  ,autoLoad:{}
	});
	
	oseMemMsc.extDataView = new Ext.DataView({
        store: oseMemMsc.extStore
        ,tpl  : new Ext.XTemplate(
            '<ul>',
                '<tpl for=".">',
                    '<li class="ext-addon ose-icon-{name}" title="{title}" name="{name}" type="{type}">',
                        '<a href="javascript:void(0)"><strong>{title}</strong></a>',
                    '</li>',
                '</tpl>',
            '</ul>'
        )
      
        ,id: 'mem-ext-panel'
        ,itemSelector: 'li.ext-addon'
        ,singleSelect: true
        ,multiSelect : false
        ,autoScroll  : true
        ,listeners: {
        	click: function(dv, i,node, e)	{
        		var n = oseMemMsc.extStore.data.items[i].data;
				oseMemMsc.openAddon(dv, i,node, e,n);
        	}
        }
    });

	
	oseMemMsc.ext = new Ext.Panel({
		border: false
		,items: oseMemMsc.extDataView
		//,height: 100
	});
	
	////////////////////////////////////////////////
	
	oseMemMsc.openMod = function(dv, i,node, e, n)	{
		if(!memModWin)	{
			var memModWin = new Ext.Window({
				title: n.title
				,width: 650
				,autHeight: true
				,x: 200
				,y: 50
				,layout: 'fit'
				//,items:[eval('oseMemMsc.'+n.name)]
				,autoLoad:{
					url: 'index.php?option=com_osemsc&controller=member'
					,params:{ task:'getMod',addon_name:n.name,addon_type:n.type }
					,scripts: true
					,callback: function(el ,success, response, options)	{
						memModWin.add(eval('oseMemMsc.'+n.name));
						memModWin.doLayout();
					}
				}
			})
		}
		
		
		memModWin.show(this).alignTo(Ext.getBody(),'t-t');
	}
	
	oseMemMsc.modStore = new Ext.data.ArrayStore({
		proxy   : new Ext.data.MemoryProxy()
		,fields:[ 'title','name','type'
		/*
		    {name: 'title', type: 'string', mapping: 'title'},
		    {name: 'name', type: 'string', mapping: 'name'},
		    {name: 'type', type: 'string', mapping: 'type'}
		    */
	  	]
	});
	
	oseMemMsc.modStore.loadData([
        ["Joomla User Information", 'juser','member']
        //,["Billing Info.", 'billinginfo','member']
	]);
	
	oseMemMsc.modDataView = new Ext.DataView({
        store: oseMemMsc.modStore
        ,tpl  : new Ext.XTemplate(
            '<ul>',
                '<tpl for=".">',
                    '<li class="ose-mem-mod ose-icon-{name}" title="{title}" name="{name}" type="{type}">',
                        '<a href="javascript:void(0)"><strong>{title}</strong></a>',
                    '</li>',
                '</tpl>',
            '</ul>'
        )
      
        ,id: 'mem-mod-panel'
        ,itemSelector: 'li.ose-mem-mod'
        ,singleSelect: true
        ,multiSelect : false
        ,autoScroll  : true
        ,listeners: {
        	click: function(dv, i,node, e)	{
        		var n = oseMemMsc.modStore.data.items[i].data;
				oseMemMsc.openMod(dv, i,node, e,n);
        	}
        }
    });

	
	oseMemMsc.mod = new Ext.Panel({
		border: false
		,items: oseMemMsc.modDataView
		//,height: 100
	});
	
	///////////////////////////////////////////
	

	oseMemMsc.gridStore = new Ext.data.Store({
		proxy: new Ext.data.HttpProxy({
		  	url: 'index.php?option=com_osemsc&controller=member',
	            method: 'POST'
	    }),
		baseParams:{task: "getOwnMsc",limit: 20},
			reader: new Ext.data.JsonReader({

				root: 'results',
				totalProperty: 'total'
			},[
			    {name: 'id', type: 'int', mapping: 'id'},
			    {name: 'msc_id', type: 'int', mapping: 'msc_id'},
			    {name: 'membership', type: 'string', mapping: 'title'},
			    {name: 'status', type: 'int', mapping: 'status'},
			    {name: 'description', type: 'string', mapping: 'description'}
		]),
		sortInfo:{field: 'id', direction: "ASC"},
		autoLoad:{}
	});

	oseMemMsc.grid = new Ext.grid.GridPanel({
	    store: oseMemMsc.gridStore,
	    viewConfig:{forceFit:true},

	    
	 	colModel:new Ext.grid.ColumnModel({
	        defaults: {
	            width: 200,
	            sortable: true
	        },
	        columns: [
			    {id: 'id', header: 'ID', dataIndex: 'id', hidden: true,hideable:true},
			    {id: 'membership', header: 'My Subscription', dataIndex: 'membership'},
			    {
			    	id: 'start_date', header: 'Start Date', dataIndex: 'start_date'
			    	,renderer: function(val)	{
			    		return '--/--/--';
			    	}
			    },
			    {
			    	id: 'expired_date', header: 'Expired Date', dataIndex: 'expired_date'
			    	,renderer: function(val)	{
			    		return '--/--/--';
			    	}
			    },{
			    	id: 'status', header: 'Status', dataIndex: 'status',
			    	renderer: function(val)	{
			    		if(val == 1)	{
			    			return 'Active';
			    		}	else	{
			    			return 'Inactive';
			    		}
			    	}
			    }
		    ]
		}),
	 	autoHeight: true,
	 	sm: new Ext.grid.RowSelectionModel({singleSelect:true})
	});
