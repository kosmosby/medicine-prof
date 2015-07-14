Ext.ns('oseMscAddon','oseMscAddon.orderParams');

	oseMscAddon.orderParams.viewPDF = function(orderId)	{
		window.open(
			'index.php?option=com_osemsc&controller=member&task=action&action=member.order.orderViewPDF&order_id='+orderId
			,'win1'
			,'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'
		);

		return false;
	}

	oseMscAddon.orderParams.view = function(orderId)	{
		if(!addonOrderViewWin)	{
			var addonOrderViewWin = new Ext.Window({
				title: Joomla.JText._('View_Invoice')
				,id: 'invoice-window'
				,width: 800
				,autoHeight: true
				,modal: true
				,items:[{
					height: 30
					,html: '<button onClick="javascript:oseMscAddon.orderParams.viewPDF('+orderId+')">'+Joomla.JText._('PDF')+'</button>'
				},{
					height: 500
					,bodyStyle: 'padding: 10px'
					,id: 'ose-invoice'
					,autoScroll: true
					,autoLoad:{
						url: 'index.php?option=com_osemsc&controller=member'
						,params:{ task:'action', action: 'member.order.orderView' , order_id : orderId}
						,callback: function(el ,success, response, options)	{
							var msg = Ext.decode(response.responseText);
							el.update(msg.body);
						}
					}
				}]

			})
		}

		addonOrderViewWin.show().alignTo(Ext.getBody(),'t-t',[0,10]);
	}


	oseMscAddon.orderParams.store = new Ext.data.Store({
		proxy: new Ext.data.HttpProxy({
		  	url: 'index.php?option=com_osemsc&controller=member'
            ,method: 'POST'
	    })
		,baseParams:{task: "action", action: 'member.order.getOrders'}
		,reader: new Ext.data.JsonReader({

			root: 'results'
			,totalProperty: 'total'
		},[
		    {name: 'order_id', type: 'int', mapping: 'order_id'}
		    ,{name: 'create_date', type: 'string', mapping: 'create_date'}
		    ,{name: 'amount', type: 'string', mapping: 'payment_price'}
		    ,{name: 'payment', type: 'string', mapping: 'payment_method'}
		    ,{name: 'order_status', type: 'string', mapping: 'order_status'}
		    ,{name: 'params', type: 'string', mapping: 'params'}
		])
		,sortInfo:{field: 'order_id', direction: "ASC"}
		,autoLoad:{}
	});

	oseMscAddon.order = new Ext.grid.GridPanel({
	    store: oseMscAddon.orderParams.store
	    ,viewConfig:{forceFit:true}
	    ,height: 390
	 	,colModel:new Ext.grid.ColumnModel({
	        defaults: {
	            width: 200
	           ,sortable: true
	        }
	        ,columns: [
			    {id: 'id', header: Joomla.JText._('ID'), dataIndex: 'id', hidden: true,hideable:true}
			    ,{id: 'create_date', header: Joomla.JText._('Date'), dataIndex: 'create_date'}
			    ,{
			    	id: 'order_id', header: Joomla.JText._('Transaction'), dataIndex: 'order_id'
			    	,renderer: function(val)	{
			    		return Joomla.JText._('ORDER_ID') + ': ' + val
			    	}
			    }
			    ,{id: 'payment', header: Joomla.JText._('Payment'), dataIndex: 'payment'}
			    ,{id: 'amount', header: Joomla.JText._('Amount'), dataIndex: 'amount'}
			    ,{id: 'order_status', header: Joomla.JText._('Status'), dataIndex: 'order_status'}
			    ,{
			    	id: 'view', header: Joomla.JText._('Action'), xtype: 'actioncolumn'
			    	,items: [{
	                    getClass: function(v, meta, rec)	{
	                    	if(rec.get('order_status') == Joomla.JText._('CONFIRMED'))	{
	                    		return 'view-col';
	                    	}
	                	}
	                    ,tooltip: Joomla.JText._('Click_to_view')
	                    ,handler: function(grid, rowIndex, colIndex) {
							oseMscAddon.orderParams.view(grid.getStore().getAt(rowIndex).get('order_id'))
	                    }
	                }]
			    }
		    ]
		})
	 	,sm: new Ext.grid.RowSelectionModel({singleSelect:true})
	 	,bbar: new Ext.PagingToolbar({
    		pageSize: 20,
    		store: oseMscAddon.orderParams.store,
    		displayInfo: true,
		    displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
		    emptyMsg: Joomla.JText._("No_topics_to_display")
	    })
	});
