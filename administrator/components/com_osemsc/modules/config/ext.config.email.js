Ext.ns('oseMsc','oseMsc.config');

oseMsc.config.emailInit = function()	{

}

oseMsc.config.emailInit.prototype = {
		init: function()	{
			oseMsc.config.msg = new Ext.App();

			var buildEmailCombo = function(config)	{
				this.title = Ext.value(config.title);
				this.name = Ext.value(config.name);
				var type = Ext.value(config.type);
				var combo = new Ext.form.ComboBox({
					fieldLabel: this.title
					,hiddenName: this.name
					,typeAhead: true
				    ,triggerAction: 'all'
				    ,lastQuery: ''
				    ,mode: 'local'
				    ,emptyText: Joomla.JText._('MSC_NONE')
				    ,store: new Ext.data.ArrayStore({
				  		root: 'results'
				  		,idProperty: 'id'
				    	,totalProperty: 'total'
				  		,fields:[
						    {name: 'id', type: 'int', mapping: 'id'}
						    ,{name: 'subject', type: 'string', mapping: 'subject'}
						    ,{name: 'type', type: 'string', mapping: 'type'}
					  	]
					})
					,listeners: {
						render: function(c)	{
							var defaultData = {
			                    id: 0
			                    ,subject: Joomla.JText._('MSC_NONE')
			                    ,type: ''
			                };
			                var s = c.getStore();
			                var recId = s.getTotalCount(); // provide unique id
			                var p = new s.recordType(defaultData, recId++); // create new record
			                s.insert(0,p);

							ose.combo.getLocalJsonData(c,getEmails());
							c.getStore().filter([{
								fn   : function(record) {
									return record.get('type') == type || record.get('type') == '';
								},
								scope: this
							}]);
						}
					}
					,valueField: 'id'
			    	,displayField: 'subject'
				})
				return combo;

			}

			var configEmailStore = new Ext.data.Store({
				  proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=emails',
			            method: 'POST'
			      }),
				  baseParams:{task: "getEmails",email_type:'reg_email'},
				  reader: new Ext.data.JsonReader({
				    root: 'results',
				    totalProperty: 'total'
				  },[
				    {name: 'id', type: 'int', mapping: 'id'},
				    {name: 'Subject', type: 'string', mapping: 'subject'}
				  ])
				  ,autoLoad:{}
			});

			oseMsc.config.email = new Ext.form.FieldSet({
				//border: false,
				title: Joomla.JText._('Email_Setting'),
				items:[

				{
					xtype:'fieldset',
					title:Joomla.JText._('Email_Template_setting'),
					defaults:{width: 200},
					items:[
						buildEmailCombo({
							title: Joomla.JText._('Sales_Receipt')
							,name: 'default_receipt'
							,type: 'receipt'
						})
						,buildEmailCombo({
							title: Joomla.JText._('Order_Cancelled_Email')
							,name: 'default_cancelorder_email'
							,type: 'cancelorder_email'
						})
						,buildEmailCombo({
							title: Joomla.JText._('Default_Registration_Email')
			           		,name: 'default_reg_email'
							,type: 'reg_email'
						})
						,buildEmailCombo({
							title: Joomla.JText._('Pay_Offline_Email')
				        	,name: 'pay_offline_email'
							,type: 'pay_offline'
						})
						,buildEmailCombo({
							title: Joomla.JText._('Order_Notification')
							,name: 'order_notification'
							,type: 'receipt'
						})
					/*,{
			            fieldLabel: 'Sales Receipt'
			            ,hiddenName: 'default_receipt'
			            ,xtype: 'combo'
					    ,typeAhead: true
					    ,triggerAction: 'all'
					    //,listClass: 'combo-left'
					    ,lazyInit: false
					    ,mode: 'remote'
					    ,store: new Ext.data.Store({
					  		 proxy: new Ext.data.HttpProxy({
						            url: 'index.php?option=com_osemsc&controller=emails',
						            method: 'POST'
						      })
							,baseParams:{task: "getEmails",email_type:'receipt'}
						  	,reader: new Ext.data.JsonReader({
						    	root: 'results',
						    	totalProperty: 'total'
						  	},[
						    {name: 'id', type: 'int', mapping: 'id'},
					    	{name: 'Subject', type: 'string', mapping: 'subject'}
						  	])
						  	,autoLoad:{}
						})

					    ,valueField: 'id'
					    ,displayField: 'Subject'
			        },{
			            fieldLabel: 'Order Cancelled Email '
			            ,hiddenName: 'default_cancelorder_email'
			            ,xtype: 'combo'
					    ,typeAhead: true
					    ,triggerAction: 'all'
					    //,listClass: 'combo-left'
					    ,lazyInit: false
					    ,mode: 'remote'
					    ,store: new Ext.data.Store({
					  		 proxy: new Ext.data.HttpProxy({
						            url: 'index.php?option=com_osemsc&controller=emails',
						            method: 'POST'
						      })
							,baseParams:{task: "getEmails",email_type:'cancelorder_email'}
						  	,reader: new Ext.data.JsonReader({
						    	root: 'results',
						    	totalProperty: 'total'
						  	},[
						    {name: 'id', type: 'int', mapping: 'id'},
					    	{name: 'Subject', type: 'string', mapping: 'subject'}
						  	])
						  	,autoLoad:{}
						})

					    ,valueField: 'id'
					    ,displayField: 'Subject'
			        },{
						xtype:'combo',
						width: 200,
			            fieldLabel: 'Default Registration Email',
			            hiddenName: 'default_reg_email',

					    typeAhead: true,
					    triggerAction: 'all',
					    lazyRender:false,
					    mode: 'remote',
					    store: configEmailStore,
					    valueField: 'id',
					    displayField: 'Subject'
					},{
			            fieldLabel: 'Pay-Offline Email'
				        ,hiddenName: 'pay_offline_email'
				        ,xtype: 'combo'
						,typeAhead: true
						,triggerAction: 'all'
						,lazyInit: false
						,mode: 'remote'
						,store: new Ext.data.Store({
							 proxy: new Ext.data.HttpProxy({
						            url: 'index.php?option=com_osemsc&controller=emails',
						            method: 'POST'
						      })
							,baseParams:{task: "getEmails",email_type:'pay_offline'}
						  	,reader: new Ext.data.JsonReader({
						    	root: 'results',
						    	totalProperty: 'total'
						  	},[
						    {name: 'id', type: 'int', mapping: 'id'},
						   	{name: 'Subject', type: 'string', mapping: 'subject'}
						  	])
						  	,autoLoad:{}
						})
					    ,valueField: 'id'
					    ,displayField: 'Subject'
				    }*/
				    ,{
						fieldLabel:Joomla.JText._('Send_Welcome_Email_at_the_first_time_only'),
						xtype: 'radiogroup',
						name:'sendWelOnlyOneTime',
						defaults: {xtype: 'radio', name:'sendWelOnlyOneTime'},
						items:[
							{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
							{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
						]
					},{
						fieldLabel: Joomla.JText._('Send_Receipt_at_the_first_time_only'),
						xtype: 'radiogroup',
						name:'sendReceiptOnlyOneTime',
						defaults: {xtype: 'radio', name:'sendReceiptOnlyOneTime'},
						items:[
							{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
							{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
						]
					},{
						fieldLabel: Joomla.JText._('Send_Receipt_for_free_membership'),
						xtype: 'radiogroup',
						name:'sendFreeReceipt',
						defaults: {xtype: 'radio', name:'sendFreeReceipt'},
						items:[
							{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
							{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
						]
					},{
						fieldLabel: Joomla.JText._('Do_not_send_email_while_batch_loading_users_into_a_membership_plan'),
						xtype: 'radiogroup',
						name:'disabledSendEmailInAdmin',
						defaults: {xtype: 'radio', name:'disabledSendEmailInAdmin'},
						items:[
							{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
							{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
						]
					}]
				},{
					xtype:'fieldset',
					title:Joomla.JText._('Email_to_admin_Group_setting'),
					defaults:{width: 200},
					items:[{
						border:false,
						width: 500,
						autoLoad:{
							method:'POST',
							url:'index.php?option=com_osemsc&controller=config&task=getEmailAdminGroupList'
						}
					 },{
						fieldLabel: Joomla.JText._('Send_Registration_Email_to_Admin'),
						xtype: 'radiogroup',
						name:'sendReg2Admin',
						defaults: {xtype: 'radio', name:'sendReg2Admin'},
						items:[
							{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
							{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
						]
					},{
						fieldLabel: Joomla.JText._('Send_Welcome_Email_to_Admin'),
						xtype: 'radiogroup',
						name:'sendWel2Admin',
						defaults: {xtype: 'radio', name:'sendWel2Admin'},
						items:[
							{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
							{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
						]
					},{
						fieldLabel: Joomla.JText._('Send_Cancel_Email_to_Admin'),
						xtype: 'radiogroup',
						name:'sendCancel2Admin',
						defaults: {xtype: 'radio', name:'sendCancel2Admin'},
						items:[
							{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1, checked: true},
							{boxLabel: Joomla.JText._('ose_No'),inputValue: 0}
						]
					},{
						fieldLabel: Joomla.JText._('Send_Expiration_Email_to_Admin'),
						xtype: 'radiogroup',
						name:'sendExp2Admin',
						defaults: {xtype: 'radio', name:'sendExp2Admin'},
						items:[
							{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
							{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
						]
					},{
						fieldLabel: Joomla.JText._('Send_Sales_Receipt_to_Admin'),
						xtype: 'radiogroup',
						name:'sendReceipt2Admin',
						defaults: {xtype: 'radio', name:'sendReceipt2Admin'},
						items:[
							{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
							{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
						]
					},{
						fieldLabel: Joomla.JText._('Send_offline_email_to_Admin'),
						xtype: 'radiogroup',
						name:'sendPayOffline2Admin',
						defaults: {xtype: 'radio', name:'sendPayOffline2Admin'},
						items:[
							{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
							{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
						]
					}]
				}]
			});

			oseMsc.config.emailReader = new Ext.data.JsonReader({
			    root: 'result'
			    ,totalProperty: 'total'
			    ,fields:[
				    {name: 'id', type: 'int', mapping: 'id'}
				    ,{name: 'default_reg_email', type: 'int', mapping: 'default_reg_email'}
				    ,{name: 'default_cancelorder_email', type: 'int', mapping: 'default_cancelorder_email'}
				    ,{name: 'sendReg2Admin', type: 'int', mapping: 'sendReg2Admin'}
				    ,{name: 'sendWel2Admin', type: 'int', mapping: 'sendWel2Admin'}
				    ,{name: 'sendCancel2Admin', type: 'int', mapping: 'sendCancel2Admin'}
				    ,{name: 'sendExp2Admin', type: 'int', mapping: 'sendExp2Admin'}
				    ,{name: 'sendReceipt2Admin', type: 'int', mapping: 'sendReceipt2Admin'}
				    ,{name: 'sendPayOffline2Admin', type: 'int', mapping: 'sendPayOffline2Admin'}
				    ,{name: 'default_receipt', type: 'int', mapping: 'default_receipt'}
				    ,{name: 'sendWelOnlyOneTime', type: 'int', mapping: 'sendWelOnlyOneTime'}
				    ,{name: 'sendReceiptOnlyOneTime', type: 'int', mapping: 'sendReceiptOnlyOneTime'}
				    ,{name: 'sendFreeReceipt', type: 'int', mapping: 'sendFreeReceipt'}
				    ,{name: 'pay_offline_email', type: 'int', mapping: 'pay_offline_email'}
				    ,{name: 'disabledSendEmailInAdmin', type:'int', mapping: 'disabledSendEmailInAdmin'}
				    ,{name: 'order_notification', type: 'int', mapping: 'order_notification'}
			  	]
		  	}),

			oseMsc.config.emailForm = new Ext.form.FormPanel({
				title:Joomla.JText._('Email'),
				border: false,
				labelWidth: 250,
				autoScroll: true,
				minHeight: 600,
				//autoHeight: true,
				bodyStyle:'padding:10px 10px 0',
				defaults:{bodyStyle:'padding:10px 10px 0'},
				items:[
					oseMsc.config.email
				],

				reader:	oseMsc.config.emailReader,

				buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.emailForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'email'},
							success: function(form,action){
								var msg = action.result;
								oseMsc.config.msg.setAlert(msg.title,msg.content);
							}
						})
					}
				}],

				listeners: {
					render: function(p){
						configEmailStore.load();
						p.getForm().load({
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'getConfig',config_type:'email'}
						});
					}
				}
			});
		}
}
	