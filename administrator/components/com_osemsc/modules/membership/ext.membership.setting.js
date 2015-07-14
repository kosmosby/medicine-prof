Ext.ns('oseMscs');

oseMscs.membershipSetting = function()	{

}
oseMscs.membershipSetting.prototype = {
		init: function()	{
			oseMscs.openWin = function(dv, i,node, e, n)	{
				var sel = oseMscs.grid.getSelectionModel();
				var selections = sel.getSelections();

				if(sel.hasSelection())	{
					var mscNode = selections[selections.length-1];
					oseMscs.msc_id = mscNode.id;
					oseMsc.msc_id = mscNode.id;
					if(!panelWin)	{
						var panelWin = new Ext.Window({
							title: Joomla.JText._(n.type.toUpperCase())+'-'+n.title
							,id: 'mscSetting-panelWin'
							,defaults: {border:false}
							,modal: true
							,width: 500
							,border: false
							,autoHeight: true
							,autoLoad:{
								url: 'index.php?option=com_osemsc&controller=memberships'
								,params:{ task:'getAddon',addon_name:n.name,addon_type:n.type }
								,scripts: true
								,callback: function(el ,success, response, options)	{
									panelWin.add(eval('oseMscAddon.'+n.name));
									panelWin.doLayout();
								}
							}
						});
					}
					panelWin.setWidth(1000)
					panelWin.show(dv).alignTo(Ext.getBody(),'t-t');
				}	else	{
					Ext.Msg.alert('Notice','Please select a membership plan!');
				}
			}

			oseMscs.mscSettingStore = new Ext.data.Store({
				  proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=memberships',
			            method: 'POST'
			      }),
				  baseParams:{task: "getAddons",addon_type:'panel'},
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
			});

			oseMscs.mscSettingDataView = new Ext.DataView({
		        store: oseMscs.mscSettingStore
		        ,tpl  : new Ext.XTemplate(
		            '<ul>',
		                '<tpl for=".">',
		                    '<li class="ose-msc-setting ose-icon-{name}" title="{title}" name="{name}" type="{type}">',
		                        '<a href="javascript:void(0)"><strong>{title}</strong></a>',
		                    '</li>',
		                '</tpl>',
		            '</ul>'
		        )

		        ,id: 'msc-panel'
		        ,itemSelector: 'li.ose-msc-setting'
		        ,singleSelect: true
		        ,multiSelect : false
		        ,autoScroll  : true
		        ,listeners: {
		        	click: function(dv, i,node, e)	{
		        		var n = oseMscs.mscSettingStore.data.items[i].data;
						oseMscs.openWin(dv, i,node, e,n);
		        	}
		        }
		    });

			oseMscs.mscSetting = new Ext.Panel({
				title:Joomla.JText._('Membership_Parameters_Panel')
				,border: false
				,items:oseMscs.mscSettingDataView
			});

		/////////////////////////////////////////////////////

			  oseMscs.mscBridgesStore = new Ext.data.Store({
				  proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=memberships',
			            method: 'POST'
			      }),
				  baseParams:{task: "getAddons",addon_type:'bridge'},
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
			});

			oseMscs.mscBridgesDataView = new Ext.DataView({
		        store: oseMscs.mscBridgesStore
		        ,tpl  : new Ext.XTemplate(
		            '<ul>',
		                '<tpl for=".">',
		                    '<li class="ose-msc-bridges ose-icon-{name}" title="{title}" name="{name}" type="{type}">',
		                        '<a href="javascript:void(0)"><strong>{title}</strong></a>',
		                    '</li>',
		                '</tpl>',
		            '</ul>'
		        )

		        ,id: 'msc-bridge'
		        ,itemSelector: 'li.ose-msc-bridges'
		        ,singleSelect: true
		        ,multiSelect : false
		        ,autoScroll  : true
		        ,listeners: {
		        	click: function(dv, i,node, e)	{
		        		var n = oseMscs.mscBridgesStore.data.items[i].data;
						oseMscs.openWin(dv, i,node, e,n);
		        	}
		        }
		    });



			oseMscs.mscBridges = new Ext.Panel({
				title:Joomla.JText._('MEMBERSHIP_BRIDGES_PANEL')
				,border: false
				,items:oseMscs.mscBridgesDataView
			});

			///////////////////////////////////////

			oseMscs.contentSettingStore = new Ext.data.Store({
				  proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=memberships',
			            method: 'POST'
			      }),
				  baseParams:{task: "getAddons",addon_type:'content'},
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
			});

			oseMscs.contentSettingDataView = new Ext.DataView({
		        store: oseMscs.contentSettingStore
		        ,tpl  : new Ext.XTemplate(
		            '<ul>',
		                '<tpl for=".">',
		                    '<li class="ose-content-setting ose-icon-{name}" title="{title}" name="{name}" type="{type}">',
		                        '<a href="javascript:void(0)"><strong>{title}</strong></a>',
		                    '</li>',
		                '</tpl>',
		            '</ul>'
		        )

		        ,id: 'msc-content'
		        ,itemSelector: 'li.ose-content-setting'
		        ,singleSelect: true
		        ,multiSelect : false
		        ,autoScroll  : true
		        ,listeners: {
		        	click: function(dv, i,node, e)	{
		        		var n = oseMscs.contentSettingStore.data.items[i].data;
						oseMscs.openWin(dv, i,node, e,n);
		        	}
		        }
		    });



			oseMscs.contentSetting = new Ext.Panel({
				title:Joomla.JText._('Content_Control_Setting')
				,border: false
				,items: oseMscs.contentSettingDataView
			});
		}
}