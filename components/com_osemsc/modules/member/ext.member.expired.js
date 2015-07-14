Ext.ns('oseMemMsc');	
	oseMemMsc.msg = new Ext.App();
	
	oseMemMsc.renew = function(mscId)	{
		if(!renewWin){
			var renewWin = new Ext.Window({
				title:' Renew Membership'
				,width: 300
				,autoHeight: true
				,modal: true
				,autoLoad:{
					url: 'index.php?option=com_osemsc&controller=member'
					,params:{task: 'getExtraJs',addon_name: 'os', addon_type: 'member'}
					,scripts: true
					,callback: function(el,success,response,opt)	{
						//alert(eval('oseMemMsc.gridMscRenewButtonForm'));
						renewWin.add(eval('oseMemMsc.gridMscRenewButtonForm'));
						oseMemMsc.gridMscRenewButtonForm.getForm().findField('msc_id').setValue(mscId);
						oseMemMsc.gridMscRenewButtonForm.getForm().findField('payment_mode').getStore().setBaseParam('msc_id',mscId);
						renewWin.doLayout();
					}
				}
			});
		}

		renewWin.show(this).alignTo(Ext.getBody(),'t-t');
	};

	oseMemMsc.cancel = function(mscId)	{
		Ext.Msg.confirm('Notice','Are you sure to cancel the membership?',function(btn, text){
			if(btn == 'yes')	{
				Ext.Ajax.request({
					url:'index.php?option=com_osemsc&controller=member',
					params:{task: 'cancelMsc',msc_id:mscId},
					success: function(response,opt)	{
						var msg = Ext.decode(response.responseText);
						if(msg.success)	{
							Ext.Msg.alert(msg.title,msg.content);
							windows.location.reload();
						}
					}
				})
			}
		});
	};

	

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
			    {name: 'start_date', type: 'datetime', mapping: 'start_date'},
			    {name: 'expired_date', type: 'datetime', mapping: 'expired_date'},
			    {name: 'status', type: 'int', mapping: 'status'},
			    {name: 'description', type: 'string', mapping: 'description'},
			    {name: 'params', type: 'string', mapping: 'params'}
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
			    {id: 'start_date', header: 'Start Date', dataIndex: 'start_date'},
			    {id: 'expired_date', header: 'Expired Date', dataIndex: 'expired_date'},
			    {
			    	id: 'status', header: 'Status', dataIndex: 'status',
			    	renderer: function(val)	{
			    		if(val == 1)	{
			    			return 'Active';
			    		}	else	{
			    			return 'Inactive';
			    		}
			    	}
			    },{
			    	id: 'enable', header: 'Enable', xtype: 'templatecolumn', dataIndex: 'msc_id,params',
			    	tpl: new Ext.XTemplate(
			    		'<a href="javascript:oseMemMsc.renew({msc_id})" class="ose-ownmsc-renew" id="ose-ownmsc-renew-{msc_id}">Renew</a>',


			    		{
					        // XTemplate configuration:
					        compiled: true,
					        disableFormats: true,
					        // member functions:
					        hasRenew: function(params)	{
					        	if(!params || params == null)
					        	{
					        		return true;
					        	}	else	{
					        		var obj = Ext.decode(params);

						            return (obj.payment_mode == 'm');
					        	}

					        }
					    }
			    	)
			    }
		    ]
		}),
	 	autoHeight: true,
	 	sm: new Ext.grid.RowSelectionModel({singleSelect:true})
	});
