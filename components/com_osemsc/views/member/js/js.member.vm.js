Ext.ns('oseMemMsc');
	oseMemMsc.gridMscRenewButtonForm = new Ext.FormPanel({
		ref: 'form'
		,border: false
		,bodyStyle: 'padding: 10px'
		,items:[{
			xtype: 'hidden'
			,name:'msc_id'
			//,value: mscId
		},{
			xtype:'combo',
			width: 150,
			fieldLabel: 'Renewing Mode',
            hiddenName: 'payment_mode',
		    typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:false,
		    mode: 'remote',
		    store: new Ext.data.Store({
				  proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=member',
			            method: 'POST'
			      }),
				  baseParams:{task: "getPaymentMode",msc_id:mscId}, 
				  reader: new Ext.data.JsonReader({   
				    root: 'results',
				    totalProperty: 'total'
				  },[ 
				    {name: 'value', type: 'sting', mapping: 'value'},
				    {name: 'text', type: 'string', mapping: 'text'}
				  ])
			}),
		    valueField: 'value',
		    displayField: 'text'
		}]
	
		,buttons: [{
			text: 'Renew',
			handler: function()	{
				oseMemMsc.gridMscRenewButtonForm.getForm().submit({
					url:'index.php?option=com_osemsc&controller=payment',
					params:{task: 'generateConfirm'},
					success: function(form,action){
						var msg = action.result;
						
						Ext.Ajax.request({
							url:'index.php?option=com_osemsc&controller=payment',
							params:{task: 'toPaymentVm'},
							success: function(response,opt)	{
								var msg1 = Ext.decode(response.responseText);
								
								if(msg1.success)	{
									window.location = 'index.php?option=com_osemsc&view=payment';
								
								}
							}
						});
						
					},
					failure: function(form,action){
						oseMemMsc.failure(form,action);
					}
				});
			}
		}]
	})
	
