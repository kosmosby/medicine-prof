Ext.ns('oseMscAddon');
	var addonPaymentMode1RecurrenceFieldset = new Ext.form.FieldSet({
		title:'Recurrence',
		//labelAlign: 'left',
		labelWidth: 215,
		defaults:{border: false},
		items:[{
			fieldLabel:'Eternal?',
			xtype: 'checkbox',
			inputValue: 1,
			name: 'payment.eternal'
		},{
			ref:'recurrenceMode',
			fieldLabel:'Mode',
			xtype:'radiogroup',
			columns: 2,
			name:'payment.recurrence_mode',
			defaults:{name:'payment.recurrence_mode'},
			items:[
				{boxLabel: 'Periods',inputValue: 'period', checked: true},
				{boxLabel: 'Fixed', inputValue: 'fixed'}
			],
			listeners:{
				change: function(e,checked){
					if(checked){
						e.ownerCt.getComponent('period').hide();
						e.ownerCt.getComponent('fixed').hide();
						e.ownerCt.getComponent(checked.getGroupValue()).show();
					}
				},
				
				afterrender: function(e){
					var checked = e.getValue();
					e.fireEvent('change',e,checked);
				}
			}
		},{
	    	itemId: 'period',
	    	hidden: true,
	    	border: false,
	    	
	    	items:[{
	    		xtype:'compositefield',
	    		anchor:'90%',
		    	items:[{
		    		xtype: 'displayfield',
		    		html: 'Periods: ',
		    		width: 215
		    	},{
		            name:'payment.recurrence_num'
		            ,xtype: 'numberfield'
					,emptyText: 0
		            ,width:80
		        },{
		            xtype     : 'combo',
		            width:80,
		            hiddenName: 'payment.recurrence_unit',
		            anchor:'95%',
		            mode: 'local',
				    typeAhead: true,
				    triggerAction: 'all',
				    store: new Ext.data.ArrayStore({
				        id: 'limitStore',
				        fields: ['recurrence_unit','recurrence_unit_Text'],
				        data: [
				        	['day', 'Day(s)']
				        	,['week', 'Week(s)']
				        	,['month', 'Month(s)']
				        	,['year', 'Year(s)']
				        ]
				    }),
				    valueField: 'recurrence_unit',
				    displayField: 'recurrence_unit_Text'
		        }]
	    	}]
	    },{
	    	anchor:'90%',
	    	itemId:'fixed',
	    	border: false,
	    	hidden: true,
	    	
	    	items:[{
	    		xtype:'compositefield',
	    		title:'fdf',
	    		
		    	items:[{
		    		xtype: 'displayfield',
		    		html: 'Start Date: ',
		    		width: 215
		    	},{
		            xtype     : 'datefield',
		            name	:'payment.start_date',
		            
		            format: 'Y-m-d',
		            width:150
		        },{
		    		xtype: 'displayfield',
		    		html: '00: 00'
		    	}]
	    	},{
	    		xtype:'compositefield',
		    	items:[{
		    		xtype: 'displayfield',
		    		html: 'Expired Date: ',
		    		width: 215
		    	},{
		            xtype     : 'datefield',
		            name	:'payment.expired_date',
		            format: 'Y-m-d',
		            width:150
		        },{
		    		xtype: 'displayfield',
		    		html: '24: 00'
		    	}]
	    	}]
	    }]
		
	});
	
	
	var addonPaymentMode1Fieldset = new Ext.form.FieldSet({
		labelWidth: 225,
		itemId:'M',
		title: 'Manual Renewing',
		defaultType:'textfield',
		items:[{
			fieldLabel: 'Free?',
			xtype: 'checkbox',
			inputValue: 1,
			name: 'payment.isFree'
		},
			addonPaymentMode1RecurrenceFieldset,
		{
			fieldLabel: 'Price'
			,name: 'payment.price'
			,xtype: 'numberfield'
			,emptyText: 0
			
		},{
			fieldLabel: 'Coupon Discount'
			,name: 'payment.coupon_discount'
			,xtype: 'numberfield'
			,emptyText: 0
		},{
			fieldLabel: 'Renewal Discount'
			,name: 'payment.renewal_discount'
			,xtype: 'numberfield'
			,emptyText: 0
		},{
			fieldLabel: 'Tax Rate'
			,name: 'payment.tax_rate'
			,xtype: 'numberfield'
			,emptyText: 0
		},{
			fieldLabel:'Join this membership in donation? ',
			xtype:'radiogroup',
			defaults:{name: 'payment.extra_donation'},
			//border: false,
			items:[
				{boxLabel: 'Membership Only',  inputValue: '1',checked:true},
				{boxLabel: 'Membership + Donation',  inputValue: '2'},
				{boxLabel: 'Donation Only',  inputValue: '3'}
			]
		}]
	});

	var addonPaymentMode2Fieldset = new Ext.form.FieldSet({
		title: 'Automatically Renewing',
		defaultType:'textfield',
		labelWidth: 225,
		itemId:'A',
		//autoHeigth:true,
		items:[{
			xtype:'checkbox',
			fieldLabel: 'Trial?',
			inputValue: 1,
			name: 'payment.has_trial',
			handler: function(checkbox,checked){
				addonPaymentMode2Fieldset.trialFieldSet.setVisible(checked);
			}
		},{
			ref: 'trialFieldSet',
			xtype: 'fieldset',
			title: 'Trial Setting',
			hidden: true,
			defaultType: 'textfield',
			items:[{
				fieldLabel: 'Initial charge for trial period ',
				name: 'payment.a1',
				value:''
			},{
				fieldLabel: 'Duration for trial period',
				name: 'payment.p1'
			},{
				fieldLabel: 'Duration type for trial period.(D,W,M,Y)',
				xtype: 'combo',
				anchor:'95%',
		        mode: 'local',
		        hiddenName: 'payment.t1',
			    typeAhead: true,
			    triggerAction: 'all',
    			lazyRender:true,
			    store: new Ext.data.ArrayStore({
			        id: 'limitStore',
			        fields: ['limitValue','limitText'],
			        data: [['day', 'Day(s)'], ['week', 'Week(s)'], ['month', 'Month(s)'], ['year', 'Year(s)']]
			    }),
			    valueField: 'limitValue',
			    displayField: 'limitText'
			}]
		},{
			fieldLabel: 'Recurring Amount: ',
			name: 'payment.a3'
		},{
			fieldLabel: 'Duration for Subscription period',
			name: 'payment.p3'
		},{
			fieldLabel: 'Duration type for trial period.(D,W,M,Y)',
			xtype: 'combo',
			anchor:'95%',
	        mode: 'local',
	        hiddenName: 'payment.t3',
		    typeAhead: true,
		    triggerAction: 'all',
			lazyRender:true,
		    store: new Ext.data.ArrayStore({
		        id: 'limitStore',
		        fields: ['limitValue','limitText'],
		        data: [['day', 'Day(s)'], ['week', 'Week(s)'], ['month', 'Month(s)'], ['year', 'Year(s)']]
		    }),
		    valueField: 'limitValue',
		    displayField: 'limitText'
		}]
	});

	var addonPaymentModeFieldset = new Ext.form.FieldSet({
		title:'Payment Mode',
		//ref:'mode1',
		defaultType:'textfield',
		labelWidth: 235,
		items:[{
			fieldLabel:'Payment Mode',
			xtype: 'radiogroup',
			columns: 2,
			name: 'payment.payment_mode',
			defaults: {name: 'payment.payment_mode'},
			border: false,
			items:[
				{boxLabel: 'Only Allow Manuall Renewing',  inputValue: 'm',checked:true}
				,{boxLabel: 'Allow Both',  inputValue: 'b'}
				,{boxLabel: 'Only Allow Automatically Renewing', inputValue: 'a'}
				
			],
			listeners:{
				change: function(e,checked){
					if(checked.getGroupValue() == 'b'){
						e.ownerCt.getComponent('A').show();
						e.ownerCt.getComponent('M').show();
					}else{
						e.ownerCt.getComponent('A').hide();
						e.ownerCt.getComponent('M').hide();
						e.ownerCt.getComponent(checked.getGroupValue().toUpperCase()).show();
					}
				},
				
				render: function(e){
					var checked = e.getValue();
					e.fireEvent('change',e,checked);
				}
			}
		},{
			name: 'payment.currency',
			fieldLabel: 'Currency',
			emptyText: 'USD'
		},{
			xtype: 'radiogroup',
			name: 'payment.manual_renew_mode',
			fieldLabel: 'Manual Renewing Mode',
			width: '60%',
			defaults:{xtype:'radio',name:'payment.manual_renew_mode'},
			items:[
				{boxLabel: 'Renew Instantly', inputValue: 'renew', checked:true},
				{boxLabel: 'Extend', inputValue: 'extend'}
			]
		},{
			xtype: 'radiogroup',
			name: 'payment.manual_to_automatic_mode',
			fieldLabel: 'Manual to Automatic Renewing Mode',
			width: '60%',
			defaults:{xtype:'radio',name:'payment.manual_to_automatic_mode'},
			items:[
				{boxLabel: 'Renew Instantly', inputValue: 'renew', checked:true},
				{boxLabel: 'Extend', inputValue: 'extend'}
			]
		},
			addonPaymentMode1Fieldset,
			addonPaymentMode2Fieldset
		]
	});

	var addonPayment2COFieldset = new Ext.form.FieldSet({
		title:'2Checkout Payment Gateway Integration',
		labelWidth: 235,
		defaultType:'textfield',
		items:[{
			xtype:'fieldset',
			title:'2ChekcOut Product',
			labelWidth: 225,
			items:[{
				xtype:'textfield',
				fieldLabel:'Product ID',
				name: 'payment._2co_product_id'
			}]
		}]
	});
	
	var addonPaymentDiscountFieldset = new Ext.form.FieldSet({
		labelWidth: 235,
		title: 'Discount Setting',
		items:[{
			fieldLabel: 'Discount',
			xtype: 'compositefield',
			
			items:[{
				name: 'payment.discount'
				,xtype: 'numberfield'
				,emptyText: 0
			},{
				xtype: 'combo',
				width: 100,
		        mode: 'local',
		        hiddenName: 'payment.discount_unit',
			    typeAhead: true,
			    triggerAction: 'all',
				lazyRender:true,
			    store: new Ext.data.ArrayStore({
			        fields: ['value','text'],
			        data: [['rate', 'Rate'], ['number', 'Number']]
			    }),
			    valueField: 'value',
			    displayField: 'text'
		
			}]
			
		}]
	});


	oseMscAddon.payment = new Ext.Panel({
		labelWidth: 200
		,defaults: [{labelWidth: 200}]
		,tbar: [{
			text: 'save',
			handler: function(){
				oseMscAddon.payment.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.payment.save',msc_id: oseMsc.msc_id
				    },
				    success: function(form, action) {
				    	var msg = action.result;
				    	oseMsc.msg.setAlert(msg.title,msg.content);
				    	
				    },
				    failure: function(form, action) {
				        switch (action.failureType) {
				            case Ext.form.Action.CLIENT_INVALID:
				                Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
				                break;
				            case Ext.form.Action.CONNECT_FAILURE:
				                Ext.Msg.alert('Failure', 'Ajax communication failed');
				                break;
				            case Ext.form.Action.SERVER_INVALID:
				               	var msg = action.result;
				    			oseMsc.msg.setAlert(msg.title,msg.content);
				    			break;
				       }
				    }
    			})
			}
		}]
		,autoScroll: true
		,items:[{
			xtype: 'form',
			ref: 'form',
			
			autoWidth: true,
		    border: false,
		    bodyStyle:'margin:10px',
		    defaults: [{bodyStyle:'padding:10px'}],
		    
			items:[
				addonPaymentModeFieldset,
				addonPayment2COFieldset,
				addonPaymentDiscountFieldset
			],
			
			reader:new Ext.data.JsonReader({   
			    root: 'result',
			    totalProperty: 'total',
			    fields:[
			    	{name: 'payment.recurrence_mode', type: 'string', mapping: 'recurrence_mode'},
				    {name: 'payment.recurrence_num', type: 'int', mapping: 'recurrence_num'},
				    {name: 'payment.recurrence_unit', type: 'string', mapping: 'recurrence_unit'},
				    {name: 'payment.start_date', type: 'string', mapping: 'start_date'},
				    {name: 'payment.expired_date', type: 'string', mapping: 'expired_date'}, 
			    	{name: 'payment.currency', type: 'string', mapping: 'currency'},
				    {name: 'payment.product_id', type: 'int', mapping: 'product_id'},
				    {name: 'payment.payment_mode', type: 'string', mapping: 'payment_mode'},
				    {name: 'payment.price', type: 'int', mapping: 'price'},
				    {name: 'payment.discount', type: 'int', mapping: 'discount'},
				    {name: 'payment.tax_rate', type: 'int', mapping: 'tax_rate'},
				    {name: 'payment.extra_donation', type: 'int', mapping: 'extra_donation'},
				    {name: 'payment.has_trial', type: 'int', mapping: 'has_trial'},
				    {name: 'payment.a1', type: 'int', mapping: 'a1'},
				    {name: 'payment.p1', type: 'int', mapping: 'p1'},
				    {name: 'payment.t1', type: 'string', mapping: 't1'},
				    {name: 'payment.a3', type: 'int', mapping: 'a3'},
				    {name: 'payment.p3', type: 'int', mapping: 'p3'},
				    {name: 'payment.t3', type: 'string', mapping: 't3'},
				    
				    {name: 'payment.eternal', type: 'string', mapping: 'eternal'},
				    {name: 'payment.isFree', type: 'string', mapping: 'isFree'},
				    
				    {name: 'payment.coupon_discount', type: 'int', mapping: 'coupon_discount'},
				    {name: 'payment.renewal_discount', type: 'int', mapping: 'renewal_discount'},
				    {name: 'payment.manual_renew_mode', type: 'string', mapping: 'manual_renew_mode'},
				    {name: 'payment.manual_to_automatic_mode', type: 'string', mapping: 'manual_to_automatic_mode'}
			  ]
		  	})
		  	
		  	
		}]
		
		,listeners:{
			render: function(panel){
				panel.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'payment'}
				});
			}
		}
	});


	