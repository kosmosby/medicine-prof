Ext.ns('oseMscAddon');

	oseMscAddon.msc_cancel = new Ext.FormPanel({
		border: false
		//,layout: 'form'
		,msgTarget: 'side'
		,labelWidth: 150
		,height: 200
		,items: [{
			 fieldLabel: Joomla.JText._('Order_ID')
            ,hiddenName: 'order_id'
            ,itemId: 'order_id'
            ,allowBlank: false
            ,xtype: 'combo'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    ,lazyInit: false
		    ,mode: 'remote'
		    ,lastQuery: ''
		    ,forceSelection: true
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=member',
		            method: 'POST'
	      		})
			  	,baseParams:{task: "action",action: 'member.order.getConfirmedOrders'}
			  	,reader: new Ext.data.JsonReader({   
			    	root: 'results'
			    	,totalProperty: 'total'
			    	,idProperty: 'order_id'
			  	},[ 
			    {name: 'code', type: 'string', mapping: 'order_id'},
			    {name: 'cname', type: 'string', mapping: 'invoice'}
			  	])
			  	,autoLoad:{}
			  	,listeners: {
		    		load: function(s,r,i)	{
		    			var comboMscId= oseMscAddon.msc_cancel.getComponent('order_id');
			  			
			    		comboMscId.setValue(r[0].data.code);

			    		comboMscId.fireEvent('select',comboMscId,r[0],0)
			    	}
		    	}
		    	,sortInfo: {
				    field: 'code',
				    direction: 'DESC' // or 'DESC' (case sensitive for local sorting)
				}
			})
			
		    ,valueField: 'code'
		    ,displayField: 'cname'
		    ,listeners: {
		    	select: function(c,r,i)	{
		    		/*oseMscAddon.msc_cancel.getForm().load({
						url: 'index.php?option=com_osemsc&controller=member'
						,waitMsg: 'Loading...'
						,params:{task: "action",action: 'member.order.getOrder', order_id: r.data.code}
						,success: function(form,action)	{
							var tpl = new Ext.XTemplate(
								'<tpl if="this.isM(payment_mode) == true"',
									'<p>'+Joomla.JText._('Manual_Renewing')+'</p>',
								'</tpl>',
								'<tpl if="this.isM(payment_mode) == false"',
									'<p>'+Joomla.JText._('Automatic_Renewing')+'</p>',
								'</tpl>',
								
							    {
							        // XTemplate configuration:
							        compiled: true,
							        disableFormats: true,
							        // member functions:
							        isM: function(val){
							            if(val == 'm')	{
							            	return true
							            }	else	{
							            	return false
							            }
							        }
							    }
							)
							//alert(action.result.data.toSource())
							tpl.overwrite(oseMscAddon.msc_cancel.getForm().findField('payment_mode').getEl(), action.result.data);
						}
					});*/
					
					oseMscAddon.msc_cancel.table.load({
						url: 'index.php?option=com_osemsc&controller=member'
						,waitMsg: Joomla.JText._('Loading')
						,params:{task: "action",action: 'member.order.getOrderMembershipTable', order_id: r.data.code}
						,callback: function(el,success,response,opt)	{
							//el.update('');
							//var html = Ext.decode(response.responseText)
							//el.update('');
							//el.updata(html)
							oseMscAddon.msc_cancel.table.doLayout()
						}
					})
					
		    	}
		    }
		},{
			xtype: 'displayfield'
			,name: 'payment_mode'
			,fieldLabel: Joomla.JText._('Billing_Preference')
			,value: '<p>'+Joomla.JText._('AUTOMATIC_RENEWING')+'</p>'
			
		},{
			xtype: 'panel'
			,fieldLabel: Joomla.JText._('Membership_Plans')
			,ref: 'table'
			,width: 600
		}]
		
		,reader: new Ext.data.JsonReader({   
		    root: 'result'
		    ,totalProperty: 'total'
		    ,idProperty: 'order_id'
		    ,fields:[ 
			    {name: 'payment_mode', type: 'string', mapping: 'payment_mode'}
		  	]
	  	})
		
		,buttons: [{
			text: Joomla.JText._('Cancel_It')
			,handler: function()	{
				oseMscAddon.msc_cancel.getEl().mask(Joomla.JText._('Loading'))
				oseMscAddon.msc_cancel.getForm().submit({
					clientValidation: true
					,url:'index.php?option=com_osemsc&controller=member'
					,params:{task: "action", action: 'member.order.cancelOrder'}
					
					,success: function(form,action){
						var msg = action.result;
						//alert(msg.toSource());
						
						switch(msg.payment_method)	{
 							case('paypal'):
 								if(msg.paypal == 'ipn')
								{
									window.location = msg.url;
								}	else	{
									Ext.Msg.wait(Joomla.JText._('Refreshing'));
									window.location.reload();
								}

 							break;
 							
 							default:
 								oseMsc.formSuccessMB(form,action,function(btn,text)	{
 									oseMscAddon.msc_cancel.getEl().unmask();
 									if(btn == 'ok')	{
 										Ext.Msg.wait(Joomla.JText._('Refreshing'));
 										window.location.reload();
 									}
 								});
 								
 							break;
 						}
						
					}
					,failure: function(form,action){
						oseMsc.formFailureMB(form,action);
						oseMscAddon.msc_cancel.getEl().unmask()
					}
				})
			}
		}]
	});