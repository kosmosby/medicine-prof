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