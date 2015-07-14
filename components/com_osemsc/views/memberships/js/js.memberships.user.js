Ext.onReady(function(){

	var mscSelectPaymentMode = function(msc_id,msc_option)	{
		if(!selectModeWin)	{
			var selectModeWin = new Ext.Window({
				width: 500
				,border: false
				,title: 'Select Payment Mode'
				,modal: true
				,items: [{
					xtype: 'form'
					,bodyStyle: 'padding: 15px'
					,msgTarget: 'side'
					,labelWidth: 150
					,height: 150
					,items: [{
						fieldLabel: 'Renewal Preference'
			            ,hiddenName: 'payment_mode'
			            ,allowBlank: false
			            ,xtype: 'combo'
					    ,typeAhead: true
					    ,triggerAction: 'all'
					    ,lazyInit: false
					    ,lastQuery: ''
					    ,mode: 'remote'
					    ,store: new Ext.data.Store({
					  		proxy: new Ext.data.HttpProxy({
					            url: 'index.php?option=com_osemsc&controller=register'
					            ,method: 'POST'
				      		})
						  	,baseParams:{
						  		task:  'getPaymentPaymentMode'
						  		,'msc_id': msc_id
						  	}
						  	,reader: new Ext.data.JsonReader({   
						    	root: 'results'
						    	,totalProperty: 'total'
						  	},[ 
						    {name: 'code', type: 'string', mapping: 'payment_mode'},
						    {name: 'cname', type: 'string', mapping: 'text'}
						  	])
						  	,autoLoad:{}
						})
						
					    ,valueField: 'code'
					    ,displayField: 'cname'
					}]
					
					,reader: new Ext.data.JsonReader({   
					    root: 'result',
					    totalProperty: 'total',
					    fields:[ 
						    {name: 'title', type: 'string', mapping: 'title'}
						    ,{name: 'days_left', type: 'string', mapping: 'days_left'}
						    ,{name: 'status', type: 'string', mapping: 'status'}
						    ,{name: 'start_date', type: 'string', mapping: 'start_date'}
						    ,{name: 'expired_date', type: 'string', mapping: 'expired_date'}
					  	]
				  	})
					
					,buttons: [{
						text: 'Renew'
						,handler: function(b)	{
							var bOwner = b.ownerCt.ownerCt;
							bOwner.getEl().mask('Loading...'),
							bOwner.getForm().submit({
								clientValidation: true,
								url: 'index.php?option=com_osemsc&controller=register',
								params:{task: 'subscribe', 'msc_id': msc_id,'msc_option':msc_option},
								success: function(form,action){
									var msg = action.result;
									Ext.Msg.wait('Please Wait...','Redirecting');
									window.location = msg.link;
								},
								failure: function(form,action){
									bOwner.getEl().unmask(),
									oseMsc.formFailure(form,action);
								}
							})
						}
					}]
				}]
			})
		}
		
		selectModeWin.show().alignTo(Ext.getBody(),'c-c');
	}
	
	Ext.select('.msc-button-select-m').on('click',function(e,t,o){
		var msc_id = t.id.replace('msc-button-select-m-','')
		var msc_option = Ext.get(t.id).findParent('.msc-first',50,true).child('.msc_options').getValue();
		mscSelectPaymentMode(msc_id,msc_option);
	});
	
	Ext.select('.msc-button-select-a').on('click',function(e,t,o){
		var msc_id = t.id.replace('msc-button-select-a-','')
		var msc_option = Ext.get(t.id).findParent('.msc-first',50,true).child('.msc_options').getValue();
		mscSelectPaymentMode(msc_id,msc_option);
		
	});
});