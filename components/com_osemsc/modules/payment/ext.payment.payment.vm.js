Ext.ns('oseMsc','oseMsc.payment');	
	oseMsc.msg = new Ext.App();
	
	oseMsc.payment.modeStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=payment',
	            method: 'POST',
	      }),
		  baseParams:{task: "getValidPaymentMode"}, 
		  reader: new Ext.data.JsonReader({   
		    root: 'results',
		    totalProperty: 'total'
		  },[ 
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'value', type: 'string', mapping: 'value'},
		    {name: 'text', type: 'string', mapping: 'text'},
		  ]),
	});
	
	oseMsc.payment.modeFieldset = new Ext.form.FieldSet({
		//hidden:true,
		labelWidth: 220,
		items:[{
			xtype: 'compositefield',
			ref:'compo',
			items:[{
				xtype: 'combo',
				fieldLabel: 'Membership Signup Welcome Email ',
		        hiddenName: 'payment_mode',
		        width: 200,
			    typeAhead: true,
			    triggerAction: 'all',
			    lazyRender:false,
			    mode: 'remote',
			    store: oseMsc.payment.modeStore,
			    valueField: 'value',
			    displayField: 'text',
			    
			    listeners: {
			        // delete the previous query in the beforequery event or set
			        // combo.lastQuery = null (this will reload the store the next time it expands)
			        beforequery: function(qe){
			            delete qe.combo.lastQuery;
			        }
			    }
			},{
				xtype: 'button',
				id:'payment-mode-select',
				text: 'OK',
			}],
		}],
	});
	
    oseMsc.payment.form = new Ext.form.FormPanel({
		items:[
			//oseMsc.payment.modeFieldset
			{
				xtype:'hidden',
				name: 'msc_id',
				ref:'msc_id',
			},
			{
				xtype:'hidden',
				name: 'payment_mode',
				ref:'payment_mode',
			}
		],
	});
   
	oseMsc.payment.confirmForm = new Ext.form.FormPanel({
		buttons:[{
			text:'Submit',
			handler:function()	{
				oseMsc.payment.confirmForm.getForm().submit({
					url:'index.php?option=com_osemsc&controller=payment',
					params:{task: 'toPaymentVm'},
					success: function(form,action){
						var msg = action.result;
						//alert(msg.content);
						Ext.Ajax.request({
							url:msg.content,
							method: 'POST',
							success: function(response,opt)	{
								window.location = 'index.php?page=checkout.index&ssl_redirect=1&option=com_virtuemart&Itemid=12';
							}
						});
					},
					failure: function(form,action){
						var msg = action.result;
						oseMsc.msg.setAlert(msg.title,msg.content);
						
						if(msg.returnUrl)	{
							window.location = msg.returnUrl;
						}
					}
				});
			}
		}]
	});

    oseMsc.payment.viewButton = new Ext.Button({
		text: 'Select Memberships',
		enableToggle: true,
		toggleHandler: function(btn,state)	{
			if(state)	{
				if(!oseMscListView)	{
					var oseMscListView = new Ext.Window({
						title: 'Membership Selections',
						modal: true,
						plain: false,
						width: 800,
						x:300,
						y:100,
						autoLoad:{
							url:'index.php?option=com_osemsc&controller=memberships',
							params:{ task: 'viewMscList' },
							
							callback: function(el,success,response,options)	{
								Ext.select('.msc-button-select-m').on('click',function(e,t,o){
									var msc_id = t.id.replace('msc-button-select-m-','')
									
									//oseMsc.payment.modeStore.setBaseParam('msc_id',msc_id);
									//oseMsc.payment.modeFieldset.msc_id.setValue(msc_id);
									
									Ext.Ajax.request({
										url: 'index.php?option=com_osemsc&controller=payment',
										params:{task: 'generateConfirm', 'msc_id':msc_id,payment_mode: 'm'},
										success: function(response,opt){
											var msg = Ext.decode(response.responseText);
											oseMsc.payment.confirmForm.body.update(msg.content);
											oseMscListView.close();
										},
									});
									
									
								});
								
								Ext.select('.msc-button-select-a').on('click',function(e,t,o){
									//alert(t.id.replace('msc-button-select-',''));
									var msc_id = t.id.replace('msc-button-select-a-','')
									
									//oseMsc.payment.modeStore.setBaseParam('msc_id',msc_id);
									//oseMsc.payment.modeFieldset.msc_id.setValue(msc_id);
									
									Ext.Ajax.request({
										url: 'index.php?option=com_osemsc&controller=payment',
										params:{task: 'generateConfirm', 'msc_id':msc_id,payment_mode: 'a'},
										success: function(response,opt){
											var msg = Ext.decode(response.responseText);
											oseMsc.payment.confirmForm.body.update(msg.content);
											oseMscListView.close();
										},
									});
									
								});
							}
						},
						
						listeners:	{
							close: function(w)	{
								oseMsc.payment.viewButton.toggle();
							}
						}
					});
				}
				
				
				oseMscListView.show(this);
			}	else	{
				//btn.setVisible(false);
			}
		}
	}); 
 	