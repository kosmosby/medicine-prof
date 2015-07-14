Ext.ns('oseMemMsc');
	oseMemMsc.msg = new Ext.App();
	
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
		,height: 100
	});

	
	
