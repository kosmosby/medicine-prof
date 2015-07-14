Ext.ns('oseMemMsc');

	oseMemMsc.openAddon = function(dv, i,node, e, n)	{

		if(!memAddonWin)	{
			var t= Joomla.JText._(n.title.replace(/\s/g,'_'));
			//var t = eval('Ext.value(Joomla.JText.strings.'+n.title.replace(/\s/g,'_')+',false)?Joomla.JText._('+n.title.replace(/\s/g,'_')+'):"'+n.title+'";');alert(t);
			var memAddonWin = new Ext.Window({
				title: t
				,id: 'mem-addon-win'
				,defaults: {border:false}
				,layout: 'fit'
				,modal: true
				,width: 888
				,modal: true
				,autoHeight: true
				,autoLoad:{
					url: 'index.php?option=com_osemsc&controller=member'
					,params:{ task:'getAddon',addon_name:n.name, addon_type:n.type }
					,scripts: true
					,callback: function(el ,success, response, options)	{
						memAddonWin.add(eval('oseMscAddon.'+n.name));
						memAddonWin.doLayout();
					}
				}
			});
		}

		memAddonWin.show().alignTo(Ext.getBody(),'c-c',[0,-200]);
	}

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
			,listeners:{
				load: function(s,r,i)	{
					//oseMemMscAddonPanel.setVisible(r.length > 0);
					if(r.length < 1)	{
						oseMemMscAddonPanel.body.update('<div id="info-non-corp-user">'+Joomla.JText._('Corporation_Member_Only')+'</div>');
					}
				}
			}
		});

		var oseMemMscAddonDataView = new Ext.DataView({
	        store: oseMemMscStore
	        ,tpl  : new Ext.XTemplate(
	            '<ul>',
	                '<tpl for=".">',
	                    '<li class="ose-mem-add ose-icon-{name}" title="{[Joomla.JText._(values.title.replace(/ /g,"_"))]}" name="{name}" type="{type}">',
	                        '<a href="javascript:void(0)"><strong>{[Joomla.JText._(values.title.replace(/ /g,"_"))]}</strong></a>',
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

		//alert(oseMemMscAddonDataView.getStore().getTotalCount());
		var oseMemMscAddonPanel = new Ext.form.FieldSet({
			border: false
			,title: title
			,collapsible: false
			,items: oseMemMscAddonDataView
		})


		return oseMemMscAddonPanel;
	}