Ext.ns('oseMemMsc');
	
	oseMemMsc.openAddon = function(dv, i,node, e, n)	{
		
		if(!memAddonWin)	{
			var memAddonWin = new Ext.Window({
				title: n.type.toUpperCase()+'-'+n.title
				,id: 'mem-addon-win'
				,defaults: {border:false}
				,layout: 'fit'
				,modal: true
				,width: 800
				//,height: 500
				,modal: true
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
		
		memAddonWin.show();
		
		memAddonWin.alignTo(Ext.getBody(),'t-t');
	}
	
   
	
	////////////////////////////////////////////////
	
	oseMemMsc.openMod = function(dv, i,node, e, n)	{
		if(!memModWin)	{
			var memModWin = new Ext.Window({
				title: n.title
				,width: 650
				,autHeight: true
				//,x: 200
				//,y: 50
				,layout: 'fit'
				,modal: true
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
	  	]
	});
	
	oseMemMsc.modStore.loadData([
        ["My Account Information", 'juser','member']
        ,["Billing Information", 'billinginfo','member']
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
	
	oseMemMsc.loadAddon = function(addon_type,title)	{
		var oseMemMscStore = new Ext.data.Store({
			proxy: new Ext.data.HttpProxy({
		    	url: 'index.php?option=com_osemsc&controller=member',
		    	method: 'POST'
			})
			,baseParams:{task: "getAddons", addon_type: addon_type}
			,reader: new Ext.data.JsonReader({   
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
		
		var oseMemMscAddonDataView = new Ext.DataView({
	        store: oseMemMscStore
	        ,tpl  : new Ext.XTemplate(
	            '<ul>',
	                '<tpl for=".">',
	                    '<li class="ose-mem-add ose-icon-{name}" title="{title}" name="{name}" type="{type}">',
	                        '<a href="javascript:void(0)"><strong>{title}</strong></a>',
	                    '</li>',
	                '</tpl>',
	            '</ul>'
	        )
	      
	        //,id: 'mem-addon-panel'
	        ,itemSelector: 'li.ose-mem-add'
	        ,singleSelect: true
	        ,multiSelect : false
	        ,autoScroll  : true
	        ,listeners: {
	        	click: function(dv, i,node, e)	{
	        		var n = dv.getStore().data.items[i].data;
					oseMemMsc.openAddon(dv, i,node, e,n);
	        	}
	        }
	    });
		
		var oseMemMscAddonPanel = new Ext.Panel({
			border: false
			,title: title
			,height: 100
			,items: oseMemMscAddonDataView
		})
		
		
		return oseMemMscAddonPanel;
	}