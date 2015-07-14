Ext.ns('oseMsc','oseMsc.config');
oseMsc.config.regFormInit = function()	{
}
oseMsc.config.regFormInit.prototype = {
		init: function()	{
			oseMsc.config.msg = new Ext.App();
			oseMsc.config.regFieldSet = new Ext.form.FieldSet({
				title: Joomla.JText._('Registration_Form'),
				labelWidth: 200,
				items:[{
					fieldLabel: Joomla.JText._('Auto_Login_After_Registration'),
					xtype: 'radiogroup',
					width: 500,
					name:'auto_login',
					defaults: {xtype: 'radio', name:'auto_login'},
					items:[
						{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1, checked: true},
						{boxLabel: Joomla.JText._('ose_No'),inputValue: 0}
					]
				},{
					xtype:'radiogroup',
					name: 'register_form',
					width: 500,
		            fieldLabel: Joomla.JText._('Registration_Form'),
		            hiddenName: 'register_form',
		        	defaults: {xtype: 'radio', name: 'register_form'},
				    items:[
				    	{boxLabel: Joomla.JText._('Basic'), inputValue: 'onestep',checked: true}
				    	,{boxLabel: Joomla.JText._('Advanced_beta_stage_not_selectable'), inputValue: 'default', disabled: true}
				    ]
				},
				{
					fieldLabel: Joomla.JText._('Clear_session_after_successful_registration_cannot_be_used_with_Auto_Login_function'),
					xtype: 'radiogroup',
					width: 500,
					name:'auto_clearsession',
					defaults: {xtype: 'radio', name:'auto_clearsession'},
					items:[
						{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
						{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
					]
				},
				{
					fieldLabel: Joomla.JText._('Allow_users_to_renew_free_membership_plan'),
					xtype: 'radiogroup',
					width: 500,
					name:'allow_freerenewal',
					defaults: {xtype: 'radio', name:'allow_freerenewal'},
					items:[
						{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
						{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
					]
				},
				{
					fieldLabel: Joomla.JText._('Do_not_activate_non_paid_registered_accounts_cannot_be_used_with_Auto_Login_function'),
					xtype: 'radiogroup',
					hidden: true,
					width: 500,
					name:'disabled_non_paid',
					defaults: {xtype: 'radio', name:'disabled_non_paid'},
					items:[
						{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
						{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
					]
				},{
					xtype: 'radiogroup',
					name: 'error_registration',
					fieldLabel: Joomla.JText._('When_Error_shows_up_during_registration'),
					anchor: '100%',
					defaults:{xtype:'radio',name:'error_registration'},
					items:[
						{boxLabel: Joomla.JText._('Refresh_page'), inputValue: 'refresh'},
						{boxLabel: Joomla.JText._('Remain_in_the_same_page'), inputValue: 'remain', checked:true}
					]
				},{
					xtype: 'radiogroup',
					name: 'hide_billinginfo',
					fieldLabel: Joomla.JText._('hide_billinginfo_for_free_membership'),
					anchor: '100%',
					defaults:{xtype:'radio',name:'hide_billinginfo'},
					items:[
						{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
						{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
					]
				},{
					xtype: 'radiogroup',
					name: 'hide_payment',
					fieldLabel: Joomla.JText._('hide_payment_for_free_membership'),
					anchor: '100%',
					defaults:{xtype:'radio',name:'hide_payment'},
					items:[
						{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
						{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
					]
				},
				{
					fieldLabel: Joomla.JText._('Show_discounted_price_renewal_discount'),
					xtype: 'radiogroup',
					width: 500,
					name:'show_discounted_price',
					defaults: {xtype: 'radio', name:'show_discounted_price'},
					items:[
						{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
						{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
					]
				}]
			});

			oseMsc.config.regReader = new Ext.data.JsonReader({
			    root: 'result',
			    totalProperty: 'total',
			    fields:[
				    {name: 'id', type: 'int', mapping: 'id'},
				    {name: 'auto_login', type: 'string', mapping: 'auto_login'},
				    {name: 'auto_clearsession', type: 'string', mapping: 'auto_clearsession'},
				    {name: 'register_form', type: 'string', mapping: 'register_form'},
				    {name: 'onestep_payment_mode', type: 'string', mapping: 'onestep_payment_mode'},
				    {name: 'disabled_non_paid', type: 'string', mapping: 'disabled_non_paid'},
				    {name: 'allow_freerenewal', type: 'string', mapping: 'allow_freerenewal'},
				    {name: 'payment_method_note', type: 'string', mapping: 'payment_method_note'},
				    {name: 'hide_billinginfo', type: 'string', mapping: 'hide_billinginfo'},
				    {name: 'hide_payment', type: 'string', mapping: 'hide_payment'},
				    {name: 'enable_fblogin', type: 'int', mapping: 'enable_fblogin'},
				    {name: 'facebookapiid', type: 'string', mapping: 'facebookapiid'},
				    {name: 'facebookapisec', type: 'string', mapping: 'facebookapisec'}
			  	]
		  	}),

			oseMsc.config.regForm = new Ext.form.FormPanel({
				title:Joomla.JText._('Registration'),
				border: false,
				labelWidth: 200,
				autoHeight: true,
				bodyStyle:'padding:10px',
				defaults:{bodyStyle:'padding:10px'},
				items:[
					oseMsc.config.regFieldSet
					,{
					xtype:'fieldset',
					title: Joomla.JText._('One_Step_Membership_Selection_Setting'),
					hidden: true,
					items:{
						xtype:'radiogroup',
						name: 'onestep_payment_mode',
						width: 500,
			            fieldLabel: Joomla.JText._('Payment_Mode'),
			            hiddenName: 'onestep_payment_mode',
			        	defaults: {xtype: 'radio', name: 'onestep_payment_mode'},
					    items:[
					    	{boxLabel: Joomla.JText._('Manual_Renewing'), inputValue: 'm', checked: true}
					    	,{boxLabel: Joomla.JText._('Automatic_Renewing'), inputValue: 'a'}
					    ]
				  	 }
				},
				{
					xtype:'fieldset',
					title: Joomla.JText._('Enable_Facebook_login'),
					items:
					 [{
							fieldLabel: Joomla.JText._('Enable_Facebook_login'),
							xtype: 'radiogroup',
							width: 500,
							name:'enable_fblogin',
							defaults: {xtype: 'radio', name:'enable_fblogin'},
							items:[
								{boxLabel: Joomla.JText._('ose_Yes'),inputValue: 1},
								{boxLabel: Joomla.JText._('ose_No'),inputValue: 0, checked: true}
							]
					 },
					  {
							xtype:'textfield',
							name: 'facebookapiid',
							width: 500,
				            fieldLabel: Joomla.JText._('Facebook_API_ID')
					  },
					  {
							xtype:'textfield',
							name: 'facebookapisec',
							width: 500,
				            fieldLabel: Joomla.JText._('Facebook_API_SECRET')
					  }
					  ] 
				}
				,{
					xtype:'fieldset',
					title: Joomla.JText._('Payment_Method_Note'),
					items:{
						xtype:'textfield',
						name: 'payment_method_note',
						width: 500,
			            fieldLabel: Joomla.JText._('Payment_Method_Note'),
				  	 }
				}],

				reader:	oseMsc.config.regReader,

				buttons:[{
					text: Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.regForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'register'},
							success: function(form,action){
								var msg = action.result;
								oseMsc.config.msg.setAlert(msg.title,msg.content);
							}
						})
					}
				}],
				listeners: {
					render: function(p){
						p.getForm().load({
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'getConfig',config_type:'register'}
						});
					}
				}
			});
		}
}
	