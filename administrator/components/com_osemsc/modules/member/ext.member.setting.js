Ext.ns('oseMemsMsc');
	oseMemsMsc.buildPanel = function()	{
	}
	oseMemsMsc.buildPanel.prototype = {
		init: function()	{
			var mUser = this.loadAddon('member_user',Joomla.JText._('User_Account'));
			var mCom = this.loadAddon('member_company',Joomla.JText._('Company_Information'));
			var mBill = this.loadAddon('member_billing',Joomla.JText._('Billing_Information'));
			var panel = new Ext.Panel({
				margins: {top:5, right:5, bottom:5, left:3}
				,id: 'ose-member-menu'
				,region: 'east'
				,width: 150
				,border: false
				,disabled: true
				,layout: 'accordion'
				,layoutConfig: {
			        titleCollapse: false,
			        animate: true,
			        activeOnTop: true
			    }
				,defaults: {bodyStyle: 'padding: 5px'}
				,items:[
					mUser,mCom,mBill
				]
			});
			return panel;
		}
		,openAddon : function(dv, i,node, e, n)	{
			if(!memAddonWin)	{
				var memAddonWin = new Ext.Window({
					title: n.title
					,id: 'mem-addon-win'
					,defaults: {border:false}
					,layout: 'fit'
					,modal: true
					,width: 800
					,border: false
					//,height: 500
					,x:100
					,y: 10
					,autoHeight: true
					,autoLoad:{
						url: 'index.php?option=com_osemsc&controller=members'
						,params:{ task:'getAddon',addon_name:n.name,addon_type:n.type }
						,scripts: true
						,callback: function(el ,success, response, options)	{
							memAddonWin.add(eval('oseMscAddon.'+n.name));
							memAddonWin.doLayout();
						}
					}
				});
			}
	
			memAddonWin.show(this).alignTo(Ext.getBody(),'t-t');
		}
		
		,loadAddon : function(addon_type,title)	{
			var oseMemMscStore = new Ext.data.Store({
				proxy: new Ext.data.HttpProxy({
			    	url: 'index.php?option=com_osemsc&controller=members'
			    	,method: 'POST'
				})
				,baseParams:{task: "getAddons", addon_type: addon_type}
				,reader: new Ext.data.JsonReader({
				    root: 'results',
				    totalProperty: 'total'
				},[
				    {name: 'id', type: 'int', mapping: 'id'}
				    ,{name: 'title', type: 'string', mapping: 'title'}
				    ,{name: 'name', type: 'string', mapping: 'name'}
				    ,{name: 'type', type: 'string', mapping: 'type'}
				    ,{name: 'addon_name', type: 'string', mapping: 'addon_name'}
				])
				//,autoLoad:{}
				,listeners:{
					load: function(s,r,i)	{
						//oseMemMscAddonPanel.setVisible(r.length > 0);
						if(r.length < 1)	{
							oseMemMscAddonPanel.setVisible(false);
						}
					}
				}
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
		        ,itemSelector: 'li.ose-mem-add'
		        ,singleSelect: true
		        ,multiSelect : false
		        ,autoScroll  : true
		    });
		    
		    oseMemMscAddonDataView.addListener('click',function(dv, i,node, e)	{
		    	var n = dv.getStore().data.items[i].data;
		    	this.openAddon(dv, i,node, e,n)
		    },this)

		    var oseMemMscAddonPanel = new Ext.Panel({
				border: false
				,title: title
				,collapsible: true
				,items: oseMemMscAddonDataView
				,listeners:{
					render:function()	{
						oseMemMscStore.load()
					}
				}
			})
			return oseMemMscAddonPanel;
		}
	}