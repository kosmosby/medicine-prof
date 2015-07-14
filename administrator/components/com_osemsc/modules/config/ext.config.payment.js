Ext.ns('oseMsc','oseMsc.config');
Ext.ns('oseMsc.config.paymentParams')

oseMsc.config.paymentInit = function()	{

}

oseMsc.config.paymentInit.prototype = {
		init: function()	{
			oseMsc.config.payment = new Ext.form.FieldSet({
				title : Joomla.JText._('Payment_Setting'),
				id : 'oseMsc.config.payment',
				defaults:{bodyStyle:'padding:15px'},
				hidden: true,
				items:[{
					xtype: 'radiogroup',
					name: 'payment_system',
					defaults:{name: 'payment_system',xtype:'radio'},
					fieldLabel: Joomla.JText._('Payment_System'),
					items:[
						{boxLabel: 'Default',inputValue:'0',checked: true}
						//,{boxLabel: 'Virtuemart',inputValue:'vm'}
					],
					listeners: {
						change: function(e,checked)	{
							if(checked.getGroupValue() == 0)	{
								oseMsc.config.paymentParams.defaultMode.setVisible(true);

								var f = oseMsc.config.paymentParams.defaultMode.findByType('textfield');
								Ext.each(f,function(e,index,all){
									e.setDisabled(false);
								});

								oseMsc.config.paymentForm.doLayout();

							}	else	{
								oseMsc.config.paymentParams.defaultMode.setVisible(false);

								var f = oseMsc.config.paymentParams.defaultMode.findByType('textfield');
								Ext.each(f,function(e,index,all){
									e.setDisabled(true);
								});

								oseMsc.config.paymentForm.doLayout();
							}
						}
					}

				}]
			});

			oseMsc.config.paymentReader = new Ext.data.JsonReader({
			    root: 'result',
			    totalProperty: 'total',
			    fields:[
				    {name: 'id', type: 'int', mapping: 'id'}
				    ,{name: 'payment_system', type: 'string', mapping: 'payment_system'}
				    ,{name: 'enable_paypal', type: 'string', mapping: 'enable_paypal'}
				    ,{name: 'paypal_testmode', type: 'string', mapping: 'paypal_testmode'}
				    ,{name: 'paypal_email', type: 'string', mapping: 'paypal_email'}
				    ,{name: 'enable_gco', type: 'string', mapping: 'enable_gco'}
				    ,{name: 'gco_testmode', type: 'string', mapping: 'gco_testmode'}
				    ,{name: 'google_checkout_id', type: 'string', mapping: 'google_checkout_id'}
				    ,{name: 'google_checkout_key', type: 'string', mapping: 'google_checkout_key'}
				    ,{name: 'google_checkout_returl', type: 'string', mapping: 'google_checkout_returl'}
				    ,{name: 'google_accept_discount', type: 'string', mapping: 'google_checkout_returl'}
				    
				    ,{name: 'enable_twoco', type: 'string', mapping: 'enable_twoco'}
				    ,{name: 'twoco_testmode', type: 'string', mapping: 'twoco_testmode'}
				    ,{name: 'twocheckoutVendorId', type: 'string', mapping: 'twocheckoutVendorId'}
				    ,{name: 'twocheckoutSecret', type: 'string', mapping: 'twocheckoutSecret'}
				    ,{name: 'twocheckout_username', type: 'string', mapping: 'twocheckout_username'}
				    ,{name: 'twocheckout_password', type: 'string', mapping: 'twocheckout_password'}
				    
				    ,{name: 'enable_cc', type: 'string', mapping: 'enable_cc'}
				    ,{name: 'enable_poffline', type: 'string', mapping: 'enable_poffline'}
				    ,{name: 'poffline_art_id', type: 'string', mapping: 'poffline_art_id'}
				    ,{name: 'cc_testmode', type: 'int', mapping: 'cc_testmode'}

					,{name: 'cc_methods', type: 'string', mapping: 'cc_methods'}
				    ,{name: 'enable_authorize', type: 'string', mapping: 'enable_authorize'}
				    ,{name: 'an_merchant_email', type: 'string', mapping: 'an_merchant_email'}
				    ,{name: 'an_email_merchant', type: 'string', mapping: 'an_email_merchant'}
				    ,{name: 'an_email_customer', type: 'string', mapping: 'an_email_customer'}
				    ,{name: 'an_loginid', type: 'string', mapping: 'an_loginid'}
				    ,{name: 'an_transkey', type: 'string', mapping: 'an_transkey'}
				    ,{name: 'an_mode', type: 'string', mapping: 'an_mode'}

				    ,{name: 'paypal_api_username', type: 'string', mapping: 'paypal_api_username'}
				    ,{name: 'paypal_api_passwd', type: 'string', mapping: 'paypal_api_passwd'}
				    ,{name: 'paypal_api_signature', type: 'string', mapping: 'paypal_api_signature'}
				    ,{name: 'paypal_mode', type: 'string', mapping: 'paypal_mode'}
					,{name: 'paypal_ipvalidate', type: 'int', mapping: 'paypal_ipvalidate'}
					,{name: 'paypal_pro_mode', type: 'string', mapping: 'paypal_pro_mode'}
					,{name: 'paypal_pro_access', type: 'string', mapping: 'paypal_pro_access'}

				    ,{name: 'enable_eway', type: 'string', mapping: 'enable_eway'}
				    ,{name: 'eway_testmode', type: 'string', mapping: 'eway_testmode'}
				    ,{name: 'eWayCustomerID_AUD', type: 'string', mapping: 'eWayCustomerID_AUD'}
				    ,{name: 'eWayUsername_AUD', type: 'string', mapping: 'eWayUsername_AUD'}
				    ,{name: 'eWayPassword_AUD', type: 'string', mapping: 'eWayPassword_AUD'}

					,{name: 'eWayCustomerID_USD', type: 'string', mapping: 'eWayCustomerID_USD'}
				    ,{name: 'eWayUsername_USD', type: 'string', mapping: 'eWayUsername_USD'}
				    ,{name: 'eWayPassword_USD', type: 'string', mapping: 'eWayPassword_USD'}

				    ,{name: 'eWayCustomerID_GBP', type: 'string', mapping: 'eWayCustomerID_GBP'}
				    ,{name: 'eWayUsername_GBP', type: 'string', mapping: 'eWayUsername_GBP'}
				    ,{name: 'eWayPassword_GBP', type: 'string', mapping: 'eWayPassword_GBP'}

				    ,{name: 'eWayCustomerID_EUR', type: 'string', mapping: 'eWayCustomerID_EUR'}
				    ,{name: 'eWayUsername_EUR', type: 'string', mapping: 'eWayUsername_EUR'}
				    ,{name: 'eWayPassword_EUR', type: 'string', mapping: 'eWayPassword_EUR'}
				    //
				    ,{name: 'enable_epay', type: 'string', mapping: 'enable_epay'}
				    //,{name: 'epay_testmode', type: 'string', mapping: 'epay_testmode'}
				    ,{name: 'epay_merchantnumber', type: 'string', mapping: 'epay_merchantnumber'}
				    ,{name: 'epay_instantcapture', type: 'string', mapping: 'epay_instantcapture'}
				    ,{name: 'epay_md5', type: 'string', mapping: 'epay_md5'}
				    ,{name: 'epay_pwd', type: 'string', mapping: 'epay_pwd'}

				    ,{name: 'enable_pnw', type: 'string', mapping: 'enable_pnw'}
				    //,{name: 'epay_testmode', type: 'string', mapping: 'epay_testmode'}
				    ,{name: 'pnw_user_id', type: 'string', mapping: 'pnw_user_id'}
				    ,{name: 'pnw_project_id', type: 'string', mapping: 'pnw_project_id'}
			  		,{name: 'pnw_project_password', type: 'string', mapping: 'pnw_project_password'}

			  		,{name: 'enable_beanstream', type: 'string', mapping: 'enable_beanstream'}
				    ,{name: 'beanstream_testmode', type: 'string', mapping: 'beanstream_testmode'}
				    ,{name: 'beanstream_merchant_id', type: 'string', mapping: 'beanstream_merchant_id'}
				    ,{name: 'beanstream_username', type: 'string', mapping: 'beanstream_username'}
			  		,{name: 'beanstream_password', type: 'string', mapping: 'beanstream_password'}
			  		,{name: 'beanstream_ipn', type: 'string', mapping: 'beanstream_ipn'}
			  		,{name: 'beanstream_passcode', type: 'string', mapping: 'beanstream_passcode'}

			  		,{name: 'enable_vpcash', type: 'string', mapping: 'enable_vpcash'}
				    ,{name: 'vpcash_testmode', type: 'string', mapping: 'vpcash_testmode'}
				    ,{name: 'vpcash_account', type: 'string', mapping: 'vpcash_account'}
				    ,{name: 'vpcash_storeid', type: 'string', mapping: 'vpcash_storeid'}
				    ,{name: 'vpcash_email', type: 'string', mapping: 'vpcash_email'}
				    ,{name: 'vpcash_secureword', type: 'string', mapping: 'vpcash_secureword'}

				    ,{name: 'enable_bbva', type: 'string', mapping: 'enable_bbva'}
				    ,{name: 'bbva_testmode', type: 'string', mapping: 'bbva_testmode'}
				    ,{name: 'bbva_clave', type: 'string', mapping: 'bbva_clave'}
				    ,{name: 'bbva_comercio', type: 'string', mapping: 'bbva_comercio'}
				    ,{name: 'bbva_terminal', type: 'string', mapping: 'bbva_terminal'}
				    ,{name: 'bbva_currency', type: 'string', mapping: 'bbva_currency'}

				    ,{name: 'enable_payfast', type: 'string', mapping: 'enable_payfast'}
				    ,{name: 'payfast_testmode', type: 'string', mapping: 'payfast_testmode'}
				    ,{name: 'payfast_merchant_id', type: 'string', mapping: 'payfast_merchant_id'}
				    ,{name: 'payfast_merchant_key', type: 'string', mapping: 'payfast_merchant_key'}

				    ,{name: 'enable_ewaysh', type: 'string', mapping: 'enable_ewaysh'}
				    ,{name: 'ewaysh_testmode', type: 'string', mapping: 'ewaysh_testmode'}
				    ,{name: 'ewaysh_customer_id', type: 'string', mapping: 'ewaysh_customer_id'}
				    ,{name: 'ewaysh_username', type: 'string', mapping: 'ewaysh_username'}
				    ,{name: 'ewaysh_pagetitle', type: 'string', mapping: 'ewaysh_pagetitle'}
				    ,{name: 'ewaysh_pagedes', type: 'string', mapping: 'ewaysh_pagedes'}
				    ,{name: 'ewaysh_pagefooter', type: 'string', mapping: 'ewaysh_pagefooter'}
				    ,{name: 'ewaysh_companyname', type: 'string', mapping: 'ewaysh_companyname'}
				    ,{name: 'ewaysh_logourl', type: 'string', mapping: 'ewaysh_logourl'}
				    ,{name: 'ewaysh_banner', type: 'string', mapping: 'ewaysh_banner'}
				    ,{name: 'ewaysh_mcd', type: 'string', mapping: 'ewaysh_mcd'}
				    ,{name: 'ewaysh_language', type: 'string', mapping: 'ewaysh_language'}

				    ,{name: 'enable_clickbank', type: 'string', mapping: 'enable_clickbank'}
				    ,{name: 'clickbank_testmode', type: 'string', mapping: 'clickbank_testmode'}
				    ,{name: 'clickbank_account', type: 'string', mapping: 'clickbank_account'}
				    ,{name: 'clickbank_secret_key', type: 'string', mapping: 'clickbank_secret_key'}

				    ,{name: 'enable_ccavenue', type: 'string', mapping: 'enable_ccavenue'}
				    ,{name: 'ccavenue_testmode', type: 'string', mapping: 'ccavenue_testmode'}
				    ,{name: 'ccavenue_merchant_id', type: 'string', mapping: 'ccavenue_merchant_id'}
				    ,{name: 'ccavenue_working_key', type: 'string', mapping: 'ccavenue_working_key'}
				    ,{name: 'ccavenue_currency', type: 'string', mapping: 'ccavenue_currency'}

				    ,{name: 'enable_usaepay', type: 'string', mapping: 'enable_usaepay'}
				    ,{name: 'usaepay_testmode', type: 'string', mapping: 'usaepay_testmode'}
				    ,{name: 'usaepay_source_key', type: 'string', mapping: 'usaepay_source_key'}
				    ,{name: 'usaepay_usesandbox', type: 'string', mapping: 'usaepay_usesandbox'}
				    ,{name: 'usaepay_sendReceipt', type: 'string', mapping: 'usaepay_sendReceipt'}

				    ,{name: 'enable_icepay', type: 'string', mapping: 'enable_icepay'}
				    ,{name: 'icepay_testmode', type: 'string', mapping: 'icepay_testmode'}
				    ,{name: 'icepay_merchant_id', type: 'string', mapping: 'icepay_merchant_id'}
				    ,{name: 'icepay_secret_code', type: 'string', mapping: 'icepay_secret_code'}
				    ,{name: 'icepay_country', type: 'string', mapping: 'icepay_country'}
				    ,{name: 'icepay_lang', type: 'string', mapping: 'icepay_lang'}
				    ,{name: 'icepay_checkIP', type: 'string', mapping: 'icepay_checkIP'}
				    
				    ,{name: 'enable_ebs', type: 'string', mapping: 'enable_ebs'}
				    ,{name: 'ebs_testmode', type: 'string', mapping: 'ebs_testmode'}
				    ,{name: 'ebs_merchantID', type: 'string', mapping: 'ebs_merchantID'}
					,{name: 'ebs_secretKey', type: 'string', mapping: 'ebs_secretKey'}
					
					,{name: 'enable_liqpay', type: 'string', mapping: 'enable_liqpay'}
					,{name: 'liqpay_testmode', type: 'string', mapping: 'liqpay_testmode'}
					,{name: 'liqpay_merchant_id', type: 'string', mapping: 'liqpay_merchant_id'}
					,{name: 'liqpay_signature', type: 'string', mapping: 'liqpay_signature'}
					,{name: 'liqpay_payment', type: 'string', mapping: 'liqpay_payment'}
					,{name: 'liqpay_phone', type: 'string', mapping: 'liqpay_phone'}
					
					,{name: 'enable_virtualmerchant', type: 'string', mapping: 'enable_virtualmerchant'}
					,{name: 'virtualmerchant_testmode', type: 'string', mapping: 'virtualmerchant_testmode'}
					,{name: 'virtualmerchant_merchant_id', type: 'string', mapping: 'virtualmerchant_merchant_id'}
					,{name: 'virtualmerchant_user_id', type: 'string', mapping: 'virtualmerchant_user_id'}
					,{name: 'virtualmerchant_pin', type: 'string', mapping: 'virtualmerchant_pin'}
					,{name: 'virtualmerchant_sendCustomerEmail', type: 'string', mapping: 'virtualmerchant_sendCustomerEmail'}
					,{name: 'virtualmerchant_sendMerchantEmail', type: 'string', mapping: 'virtualmerchant_sendMerchantEmail'}
					,{name: 'virtualmerchant_merchant_email', type: 'string', mapping: 'virtualmerchant_merchant_email'}
					
					,{name: 'enable_realex', type: 'string', mapping: 'enable_realex'}
					,{name: 'realex_merchant_id', type: 'string', mapping: 'realex_merchant_id'}
					,{name: 'realex_secret', type: 'string', mapping: 'realex_secret'}
					,{name: 'realex_account', type: 'string', mapping: 'realex_account'}
					,{name: 'realex_amex_account', type: 'string', mapping: 'realex_amex_account'}
					,{name: 'realex_mode', type: 'string', mapping: 'realex_mode'}
					,{name: 'realex_type', type: 'string', mapping: 'realex_type'}
					
					,{name: 'enable_sisow', type: 'string', mapping: 'enable_sisow'}
					,{name: 'sisow_testmode', type: 'string', mapping: 'sisow_testmode'}
					,{name: 'sisow_merchant_id', type: 'string', mapping: 'sisow_merchant_id'}
					,{name: 'sisow_merchant_key', type: 'string', mapping: 'sisow_merchant_key'}
					,{name: 'sisow_shop_id', type: 'string', mapping: 'sisow_shop_id'}
					
					,{name: 'enable_pagseguro', type: 'string', mapping: 'enable_pagseguro'}
				    ,{name: 'pagseguro_account', type: 'string', mapping: 'pagseguro_account'}
				    ,{name: 'pagseguro_token', type: 'string', mapping: 'pagseguro_token'}
				    
				    ,{name: 'enable_paygate', type: 'string', mapping: 'enable_paygate'}
				    ,{name: 'paygate_id', type: 'string', mapping: 'paygate_id'}
				    ,{name: 'paygate_key', type: 'string', mapping: 'paygate_key'}
				    
				    ,{name: 'enable_quickpay', type: 'string', mapping: 'enable_quickpay'}
				    ,{name: 'quickpay_testmode', type: 'string', mapping: 'quickpay_testmode'}
				    ,{name: 'quickpay_merchant', type: 'string', mapping: 'quickpay_merchant'}
				    ,{name: 'quickpay_secret', type: 'string', mapping: 'quickpay_secret'}
				    ,{name: 'quickpay_cardtypelock', type: 'string', mapping: 'quickpay_cardtypelock'}
				    ,{name: 'quickpay_autocapture', type: 'string', mapping: 'quickpay_autocapture'}
				    ,{name: 'quickpay_autofee', type: 'string', mapping: 'quickpay_autofee'}
				    ,{name: 'quickpay_lang', type: 'string', mapping: 'quickpay_lang'}
				    
				    ,{name: 'enable_sagepay', type: 'string', mapping: 'enable_sagepay'}
				    ,{name: 'sagepay_vendorname', type: 'string', mapping: 'sagepay_vendorname'}
				    ,{name: 'sagepay_password', type: 'string', mapping: 'sagepay_password'}
				    ,{name: 'sagepay_mode', type: 'string', mapping: 'sagepay_mode'}
				    ,{name: 'sagepay_vendoremail', type: 'string', mapping: 'sagepay_vendoremail'}
				    ,{name: 'sagepay_txtype', type: 'string', mapping: 'sagepay_txtype'}
				    
				    ,{name: 'enable_alipay', type: 'string', mapping: 'enable_alipay'}
				    ,{name: 'alipay_partner', type: 'string', mapping: 'alipay_partner'}
				    ,{name: 'alipay_key', type: 'string', mapping: 'alipay_key'}
				    ,{name: 'alipay_seller_email', type: 'string', mapping: 'alipay_seller_email'}
				    ,{name: 'alipay_transport', type: 'string', mapping: 'alipay_transport'}
				    
				]
		  	});

			oseMsc.config.paymentParams.paypalForm = new Ext.FormPanel({
				title: Joomla.JText._('Paypal_Setting')
				,labelWidth: 300
				,defaults: { bodyStyle : 'padding: 5px', width: 200}
				,reader:	oseMsc.config.paymentReader
				,height: 520
				,items:[
					{   fieldLabel: Joomla.JText._('Tips'),
					    width: 900,
						html: Joomla.JText._('WIKI_a_If_you_use_Paypal_Standard_account_please_see_this_wiki')+' <a href="http://wiki.opensource-excellence.com/index.php?title=Testing_Paypal_with_OSE_Membership_V5#Setup_OSE_Membership_with_SANDBOX_Paypal_Standard_accounts"  target="_blank"> '+Joomla.JText._('LINK')+' </a>; '+Joomla.JText._('b_If_you_use_Paypal_Pro_plesae_see_this_wiki')+' <a href="http://wiki.opensource-excellence.com/index.php?title=Testing_Paypal_with_OSE_Membership_V5#Setup_OSE_Membership_with_SANDBOX_Paypal_Pro_accounts" target="_blank"> '+Joomla.JText._('LINK')+' </a>'
					},
					{
					xtype: 'radiogroup'
					,hiddenName: 'enable_paypal'
					,fieldLabel: Joomla.JText._('Enable_PayPal')
					,defaults:{xtype:'radio',name:'enable_paypal'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
					]
					,listeners: {
						change: function(rg,checked)	{
							Ext.each(oseMsc.config.paymentParams.paypalForm.findByType('textfield'),function(item,i,all)	{
								//alert(item.getXType())
								item.setDisabled(checked.getGroupValue() == 0)
							})
						}
					}
				},{
					xtype: 'combo'
					,hiddenName: 'paypal_mode'
					,id: 'paypal_mode'
					,fieldLabel: Joomla.JText._('Paypal_Mode')
					,width: 400
					,typeAhead: true
					,triggerAction: 'all'
					,lazyRender:true
					,mode: 'local'
					,store: new Ext.data.ArrayStore({
					    id: 0
					    ,fields: [
					        'id'
					        ,'displayText'
					    ]
					    ,data: [
					    	['paypal_express', Joomla.JText._('Website_Payments_Standard')]
					    	,['paypal_paypalpro', Joomla.JText._('Website_Payments_Pro')]
					    ]
					    ,listeners: {
							load: function(s)	{
								//Ext.get('paypal_mode').setValue('paypal_express')
							}
						}
					})
					,valueField: 'id'
					,displayField: 'displayText'
					,listeners: {
						render: function(c)	{
							c.setValue('paypal_express')
						}
					}
				},{
					xtype: 'radiogroup'
					,name: 'paypal_testmode'
					,fieldLabel: Joomla.JText._('Test_Mode')
					,defaults:{xtype:'radio',name:'paypal_testmode'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
					]
				},{
					xtype: 'textfield'
					,name:'paypal_email'
					,fieldLabel: Joomla.JText._('Paypal_Email')
					,width: 400
					,vtype: 'email'
				},{
					xtype: 'textfield'
					,name: 'paypal_api_username'
					//,inputType: 'password'
					,width: 400
					,fieldLabel: Joomla.JText._('Paypal_API_Username')
				},{
					xtype: 'textfield'
					,name: 'paypal_api_passwd'
					,inputType: 'password'
					,width: 400
					,fieldLabel: Joomla.JText._('Paypal_API_Password')
				},{
					xtype: 'textarea'
					,name: 'paypal_api_signature'
					,width: 400
					,height:50
					,fieldLabel: Joomla.JText._('Paypal_API_Signature')
				},{
					xtype: 'hidden'
					,name: 'paypal_ipvalidate'
					,value:0
				},{
					xtype: 'combo'
					,hiddenName: 'paypal_pro_mode'
					,id: 'paypal_pro_mode'
					,fieldLabel: Joomla.JText._('PaypalPro_Authorize_Mode')
					,width: 400
					,typeAhead: true
					,triggerAction: 'all'
					,lazyRender:true
					,mode: 'local'
					,store: new Ext.data.ArrayStore({
					    id: 0
					    ,fields: [
					        'id'
					        ,'displayText'
					    ]
					    ,data: [
					    	['aimcap_arb', Joomla.JText._('Create_the_subscription_profile_payments_charged_instantly_within_an_hour')]
					    	,['arb', Joomla.JText._('Create_the_subscription_profile_the_first_payment_charged_within_24hours')]
					    ]
					})
					,valueField: 'id'
					,displayField: 'displayText'
					,emptyText: Joomla.JText._('Please_Choose')
				},{
					xtype: 'combo'
					,hiddenName: 'paypal_pro_access'
					,id: 'paypal_pro_access'
					,fieldLabel: Joomla.JText._('User_s_membership_status_being_activated')
					,width: 400
					,typeAhead: true
					,triggerAction: 'all'
					,lazyRender:true
					,mode: 'local'
					,store: new Ext.data.ArrayStore({
					    id: 0
					    ,fields: [
					        'id'
					        ,'displayText'
					    ]
					    ,data: [
					    	['instant', Joomla.JText._('Instantly')]
					    	,['untilpaid', Joomla.JText._('Until_the_payments_received')]
					    ]
					})
					,valueField: 'id'
					,displayField: 'displayText'
					,emptyText: Joomla.JText._('Please_Choose')
				}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.paypalForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]
				/*,listeners:{
					render: function(p){
						p.getForm().load({
							url: 'index.php?option=com_osemsc&controller=config'
							,params:{task:'getConfig',config_type:'payment'}
							,success: function(form,action)	{
								var result = action.result;

								oseMsc.config.paymentParams.gcoForm.on('render',function(p){
									p.getForm().setValues(result.data);
								})
								//oseMsc.config.paymentParams.gcoForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.twocoForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.ewayForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.ccForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.otherForm.getForm().setValues(result.data);
							}
						})
					}
				}*/
			});

			oseMsc.config.paymentParams.gcoForm = new Ext.FormPanel({
				title: Joomla.JText._('Google_Checkout')
				,defaults: { bodyStyle : 'padding: 15px'}
				,labelWidth: 200
				,height: 400
				,reader:	oseMsc.config.paymentReader
				,items:[{
						xtype: 'panel'
						,title: Joomla.JText._('Tips')
						,border: false
						,items:[{
							html: Joomla.JText._('Not_support_the_trial_subscription_Supported_period_DAILY_WEEKLY_MONTHLY_YEARLY_Please_login_your_gco_account_and_go_to_Settings_Integration_then_set_the_API_Version_to_2_0')
							,border:false
						}]
					},{
					xtype: 'radiogroup'
					,inputValue: 1
					,name: 'enable_gco'
					,fieldLabel: Joomla.JText._('Enable_Google_Checkout')
					,width: 200
					,defaults:{xtype:'radio',name:'enable_gco'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
					]
					,listeners: {
						change: function(rg,checked)	{
							Ext.each(oseMsc.config.paymentParams.gcoForm.findByType('textfield'),function(item,i,all)	{
								//alert(item.getXType())
								item.setDisabled(checked.getGroupValue() == 0)
							})
						}
					}
				},{
					xtype: 'radiogroup'
					,name: 'gco_testmode'
					,fieldLabel: Joomla.JText._('Test_Mode')
					,width: 200
					,defaults:{xtype:'radio',name:'gco_testmode'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
					]
				},{
					xtype: 'textfield'
					,name:'google_checkout_id'
					,width: 400
					,fieldLabel: Joomla.JText._('Google_Checkout_Merchant_ID')
				},{
					xtype: 'textfield'
					,name:'google_checkout_key'
					,width: 400
					,fieldLabel: Joomla.JText._('Google_Checkout_Merchant_Key')
				},{
					xtype: 'radiogroup'
					,name: 'google_accept_discount'
					,fieldLabel: Joomla.JText._('Accept_Discount_For_Each_Subscription_period')
					,width: 200
					,defaults:{xtype:'radio',name:'google_accept_discount'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
					]
				}]
				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.gcoForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]
			})


			oseMsc.config.paymentParams.twocoForm = new Ext.FormPanel({
				title: Joomla.JText._('2Checkout')
				,height: 500
				,labelWidth: 200
				,reader:	oseMsc.config.paymentReader
				,defaults: { bodyStyle : 'padding: 15px'}
				,items:[{
						xtype: 'panel'
						,title: Joomla.JText._('Tips')
						,border: false
						,items:[{
							html: Joomla.JText._('Please_set_the_2Checkout_Product_Id_in_the_Membership_Management_Advance_Payment_Setting_please_login_your_2co_account_and_go_to_Account_USER_MANAGEMENT_create_a_user_and_assign_permission_API_Access_and_API_Updating_to_the_user_Then_input_the_username_and_password_in_the_form')
							,border:false
						},
						{
							html: Joomla.JText._('OSE_CLIENTS_2CO_PROMOTION_CODE')
							,border:false
						}]
					},{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_twoco'
						,fieldLabel: Joomla.JText._('Enable_2Checkout')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_twoco'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
					]
					,listeners: {
						change: function(rg,checked)	{
							Ext.each(oseMsc.config.paymentParams.twocoForm.findByType('textfield'),function(item,i,all)	{
								//alert(item.getXType())
								item.setDisabled(checked.getGroupValue() == 0)
							})
						}
					}
				},{
					xtype: 'radiogroup'
					,name: 'twoco_testmode'
					,fieldLabel: Joomla.JText._('Test_Mode')
					,width: 200
					,defaults:{xtype:'radio',name:'twoco_testmode'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
					]
				},{
					xtype: 'textfield'
					,name:'twocheckoutVendorId'
					,width: 400
					,fieldLabel: Joomla.JText._('2checkout_Vendor_ID')
				},{
					xtype: 'textfield'
					,name:'twocheckoutSecret'
					,inputType: 'password'
					,width: 400
					,fieldLabel: Joomla.JText._('2Checkout_Secret_Word')
				},{
					xtype: 'textfield'
					,name:'twocheckout_username'
					,width: 400
					,fieldLabel: Joomla.JText._('2Checkout_Username')
				},{
					xtype: 'textfield'
					,name:'twocheckout_password'
					,width: 400
					,fieldLabel: Joomla.JText._('2Checkout_Password')
					,inputType: 'password'
				}]
				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.twocoForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]
			})

			oseMsc.config.paymentParams.ccForm = new Ext.FormPanel({
				title: Joomla.JText._('Credit_Card')
				,labelWidth: 250
				,height: 650
				,reader:	oseMsc.config.paymentReader
				,defaults: { bodyStyle : 'padding: 0px; margin-bottom: 5px; '}
				,items:[{
					xtype: 'radiogroup'
					,name: 'enable_cc'
					,fieldLabel: Joomla.JText._('Enable_Credit_Card')
					,width: 200
					,defaults:{xtype:'radio',name:'enable_cc'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
					]
					,listeners: {
						change: function(rg,checked)	{
							if(checked.getGroupValue() == 0)	{
								rg.nextSibling().nextSibling().setValue('paypal_cc')
							}
						}
					}
				},{
					xtype: 'radiogroup'
					,name: 'cc_testmode'
					,fieldLabel: Joomla.JText._('Test_Mode')
					,width: 200
					,defaults:{xtype:'radio',name:'cc_testmode'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
					]
				},{
					xtype: 'radiogroup'
					,name: 'cc_methods'
					,fieldLabel: Joomla.JText._('Methods')
					,defaults:{xtype:'radio',name:'cc_methods'}
					,width:500
					,columns: 3
					,items:[
						{boxLabel: Joomla.JText._('Authorize_Net'), inputValue: 'authorize'}
						,{boxLabel: Joomla.JText._('Paypal_Credit_Card'), inputValue: 'paypal_cc'}
						,{boxLabel: Joomla.JText._('eWay'), inputValue: 'eway'}
						,{boxLabel: Joomla.JText._('ePay'), inputValue: 'epay'}
						,{boxLabel: Joomla.JText._('Payment_Network'), inputValue: 'pnw'}
						,{boxLabel: Joomla.JText._('BeanStream'), inputValue: 'beanstream'}
						,{boxLabel: Joomla.JText._('VirtualPayCash'), inputValue: 'vpcash_cc'}
						,{boxLabel: Joomla.JText._('Eway_co_nz'), inputValue: 'ewaysh'}
						,{boxLabel: Joomla.JText._('USA_ePay'), inputValue: 'usaepay'}
						,{boxLabel: Joomla.JText._('EBS'), inputValue: 'ebs'}
						,{boxLabel: Joomla.JText._('Virtual_Merchant'), inputValue: 'virtualmerchant'}
					]
					,listeners: {
						change: function(rg,checked)	{
							Ext.each(oseMsc.config.paymentParams.ccForm.findByType('textfield'),function(item,i,all)	{
								//alert(item.getXType())
								item.setDisabled(checked.getGroupValue() != 'authorize')
							})
						}
					}
				},{
					xtype: 'fieldset'
					,title: Joomla.JText._('Authorize_Net_Please_see_this_wiki_for_Authorize_net_configuration_and_testing')+' <a href="http://wiki.opensource-excellence.com/index.php?title=Authorize.net_payment_for_auto_recurring_billing_with_a_trial_period"  target="_blank"> '+Joomla.JText._('LINK')+' </a> '
					,items: [
					{
						xtype: 'textfield',
						name: 'an_merchant_email',
						fieldLabel: Joomla.JText._('Merchant_Email_Address'),
						width: 400,
						vtype:'email'
					},{
						xtype: 'combo'
						,hiddenName: 'an_mode'
						,fieldLabel: Joomla.JText._('Authorize_Mode')
						,width: 400
						,typeAhead: true
						,triggerAction: 'all'
						,lazyRender:true
						,mode: 'local'
						,store: new Ext.data.ArrayStore({
						    id: 0
						    ,fields: [
						        'id'
						        ,'displayText'
						    ]
						    ,data: [
						    	['an_aim_arb', Joomla.JText._('AIM_Authorize_ARB')]
						    	,['an_aimcap_arb', Joomla.JText._('AIM_Capture_ARB')]
						    	,['an_arb', Joomla.JText._('ARB_Only')]
						    ]
						})
						,valueField: 'id'
						,displayField: 'displayText'
						,listeners: {
							render: function(c)	{
								if(!Ext.value(c.getValue(),false))	{
									c.setValue('an_aim_arb')
								}
							}
						}
					},{
						xtype: 'radiogroup'
						,name: 'an_email_merchant'
						,width: 350
						,fieldLabel: Joomla.JText._('Email_confirmation_from_Gateway_to_Merchant')
						,defaults:{xtype:'radio',name:'an_email_merchant'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
					},{
						xtype: 'radiogroup'
						,name: 'an_email_customer'
						,fieldLabel: Joomla.JText._('Email_confirmation_from_Gateway_to_Customers')
						,defaults:{xtype:'radio',name:'an_email_customer'}
						,width: 350
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
					},{
						xtype: 'radiogroup'
						,name: 'enable_authorize'
						,fieldLabel: Joomla.JText._('Enable_Authorize_Net')
						,width: 350
						,defaults:{xtype:'radio',name:'enable_authorize'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
					},{
						xtype: 'textfield',
						name: 'an_loginid',
						width: 400,
						fieldLabel: Joomla.JText._('Authorize_net_Login_ID')
					},{
						xtype: 'textfield',
						name: 'an_transkey',
						inputType: 'password',
						width: 400,
						fieldLabel: Joomla.JText._('Authorize_net_Transaction_Key')
					}]
				}]
				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.ccForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]
			})

			oseMsc.config.paymentParams.ewayFormMulti = function(currency)	{
				return new Ext.Panel({
					title: currency
					,layout: 'form'
					,items:[{
						xtype: 'textfield'
						,name:'eWayCustomerID_'+currency
						,width: 400
						,fieldLabel: Joomla.JText._('eWay_CustomerID')
						,allowBlank: (currency == 'AUD')?false:true
					},{
						xtype: 'textfield'
						,name:'eWayUsername_'+currency
						,width: 400
						,fieldLabel: Joomla.JText._('eWay_Username')
						,allowBlank: (currency == 'AUD')?false:true
					},{
						xtype: 'textfield'
						,name:'eWayPassword_'+currency
						,inputType: 'password'
						,width: 400
						,fieldLabel: Joomla.JText._('eWay_Password')
						,allowBlank: (currency == 'AUD')?false:true
					}]
				})
			}

			oseMsc.config.paymentParams.ewayForm = new Ext.FormPanel({
				title: Joomla.JText._('eWay_Setting')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 500
				,reader:	oseMsc.config.paymentReader
				,items:[{
					xtype: 'fieldset'
					,title: Joomla.JText._('Tips')
					,border: false
					,items:[{
						html: Joomla.JText._('Make_sure_you_have_at_least_one_account_with_currency_AUD')
						,border:false
					},{
						html: Joomla.JText._('If_you_enable_this_payment_method_be_sure_the_recurrence_setting_is_not_greater_than_31')
						,border:false
					},{
						html: Joomla.JText._('When_you_enable_test_mode_there_is_no_need_to_use_your_live_account_information_Test_Creditcard_4444333322221111')
						,border:false
					}]
				},{
					xtype: 'radiogroup'
					,inputValue: 1
					,name: 'enable_eway'
					,fieldLabel: Joomla.JText._('Enable_eWay')
					,width: 200
					,defaults:{xtype:'radio',name:'enable_eway'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
					]
					,listeners: {
						change: function(rg,checked)	{
							Ext.each(oseMsc.config.paymentParams.ewayForm.findByType('textfield'),function(item,i,all)	{
								//alert(item.getXType())
								item.setDisabled(checked.getGroupValue() == 0)
							})
						}
					}
				},{
					xtype: 'radiogroup'
					,name: 'eway_testmode'
					,fieldLabel: Joomla.JText._('Test_Mode')
					,width: 200
					,defaults:{xtype:'radio',name:'eway_testmode'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
					]
				},{
					xtype: 'tabpanel'
					,activeTab: 0
					,height: 300
					,items:[
						oseMsc.config.paymentParams.ewayFormMulti('AUD')
						,oseMsc.config.paymentParams.ewayFormMulti('USD')
						,oseMsc.config.paymentParams.ewayFormMulti('GBP')
						,oseMsc.config.paymentParams.ewayFormMulti('EUR')
					]
				}]
				,buttons:[{
					text:Joomla.JText._('save')
					,handler: function()	{
						oseMsc.config.paymentParams.ewayForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]
			})

			oseMsc.config.paymentParams.epayForm = new Ext.FormPanel({
				title: Joomla.JText._('ePay_dk')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 400
				,reader:	oseMsc.config.paymentReader
				,items:[{
					xtype: 'fieldset'
					,title: Joomla.JText._('Tips')
					,border: false
					,items:[{
						html: Joomla.JText._('If_you_want_to_use_security_key_make_sure_enable_in_ePay_Admin')+'<a href="https://ssl.ditonlinebetalingssystem.dk/admin/login.asp">'+Joomla.JText._('Link')+'</a>)'
						,border:false
					}]
				},{
					xtype: 'radiogroup'
					,inputValue: 1
					,name: 'enable_epay'
					,fieldLabel: Joomla.JText._('Enable_ePay')
					,width: 200
					,defaults:{xtype:'radio',name:'enable_epay'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
					]
					,listeners: {
						change: function(rg,checked)	{
							Ext.each(oseMsc.config.paymentParams.epayForm.findByType('textfield'),function(item,i,all)	{
								//alert(item.getXType())
								item.setDisabled(checked.getGroupValue() == 0)
							})
						}
					}
				},{
					xtype: 'textfield'
					,name:'epay_merchantnumber'
					,width: 400
					,fieldLabel: Joomla.JText._('ePay_Merchant_Number')
				},{
					xtype: 'textfield'
					,name:'epay_pwd'
					,width: 400
					,inputType: 'password'
					,fieldLabel: Joomla.JText._('ePay_Password_leave_empty_if_unable')
				},{
					xtype: 'textfield'
					,name:'epay_md5'
					,width: 400
					,inputType: 'password'
					,fieldLabel: Joomla.JText._('ePay_Security_Key_leave_empty_if_unable')
				},{
					xtype: 'radiogroup'
					,name:'epay_instantcapture'
					,width: 400
					,fieldLabel: Joomla.JText._('Instant_Captured')
					,defaults:{xtype:'radio',name:'epay_instantcapture'}
					,items:[
						{boxLabel: Joomla.JText._('Is_captured_instantly'), inputValue: 1, checked:true}
						,{boxLabel: Joomla.JText._('Capture_the_payment_manually'), inputValue: 0}
					]
				}]
				,buttons:[{
					text:Joomla.JText._('save')
					,handler: function()	{
						oseMsc.config.paymentParams.epayForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]
			})

			oseMsc.config.paymentParams.pnwForm = new Ext.FormPanel({
				title: Joomla.JText._('Payment_Network_Setting')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 390
				,reader:	oseMsc.config.paymentReader
				,items:[{
					xtype: 'fieldset'
					,title: Joomla.JText._('Tips')
					,border: false
					,items:[{
						html: Joomla.JText._('1_Currency_Please_use_EUR_CHF_and_GBP_Pre_requisite_English_bank_account')
						,border:false
					},{
						html: Joomla.JText._('2_No_subscription_automatic_for_this_payment_gateway')
						,border:false
					},{
						html: Joomla.JText._('3_Can_not_use_license_and_coupon_yet')
						,border:false
					}]
				},{
					xtype: 'radiogroup'
					,inputValue: 1
					,name: 'enable_pnw'
					,fieldLabel: Joomla.JText._('Enable_Payment_Network')
					,width: 200
					,defaults:{xtype:'radio',name:'enable_pnw'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
					]
					,listeners: {
						change: function(rg,checked)	{
							Ext.each(oseMsc.config.paymentParams.pnwForm.findByType('textfield'),function(item,i,all)	{
								//alert(item.getXType())
								item.setDisabled(checked.getGroupValue() == 0)
							})
						}
					}
				},{
					xtype: 'textfield'
					,name:'pnw_user_id'
					,width: 400
					,fieldLabel: Joomla.JText._('DIRECTebankingcom_customer_number')
				},{
					xtype: 'textfield'
					,name:'pnw_project_id'
					,width: 400
					,fieldLabel: Joomla.JText._('Project_Number')
				},{
					xtype: 'textfield'
					,name:'pnw_project_password'
					,width: 400
					,inputType: 'password'
					,fieldLabel: Joomla.JText._('Project_Password')
				}]
				,buttons:[{
					text:Joomla.JText._('save')
					,handler: function()	{
						oseMsc.config.paymentParams.pnwForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]
			})

			oseMsc.config.paymentParams.beanstreamForm = new Ext.FormPanel({
				title: Joomla.JText._('BeanStream_Setting')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 500
				,reader:	oseMsc.config.paymentReader
				,items:[{
					xtype: 'panel'
					,title: Joomla.JText._('Tips')
					,border: false
					,items:[{
						html: Joomla.JText._('fill_username_and_password_from_the_Beanstream_Order_Settings_module')
						,border: false

					},{
						html: Joomla.JText._('IPN_code_is_used_to_authorize_in_the_recurring_notification_add_ipn_and_ipn_code_at_the_end_of_the_url_in_the_Beanstream_Order_Settings_module')
						,border: false
					}]
				},{
					xtype: 'radiogroup'
					,inputValue: 1
					,name: 'enable_beanstream'
					,fieldLabel: Joomla.JText._('Enable_BeanStrem')
					,width: 200
					,defaults:{xtype:'radio',name:'enable_beanstream'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
					]
					,listeners: {
						change: function(rg,checked)	{
							Ext.each(oseMsc.config.paymentParams.beanstreamForm.findByType('textfield'),function(item,i,all)	{
								//alert(item.getXType())
								item.setDisabled(checked.getGroupValue() == 0)
							})
						}
					}
				},{
					xtype: 'radiogroup'
					,inputValue: 1
					,name: 'beanstream_testmode'
					,fieldLabel: Joomla.JText._('Test_Mode')
					,width: 200
					,defaults:{xtype:'radio',name:'beanstream_testmode'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
					]
				},{
					xtype: 'textfield'
					,name:'beanstream_merchant_id'
					,width: 400
					,fieldLabel: Joomla.JText._('Merchant_ID')
				},{
					xtype: 'textfield'
					,name:'beanstream_username'
					,width: 400
					,fieldLabel: Joomla.JText._('Username')
				},{
					xtype: 'textfield'
					,name:'beanstream_password'
					,width: 400
					,inputType: 'password'
					,fieldLabel: Joomla.JText._('Password')
				},{
					xtype: 'textfield'
					,name:'beanstream_ipn'
					,width: 400
					,fieldLabel: Joomla.JText._('IPN_Code')
				},{
					xtype: 'textfield'
					,name:'beanstream_passcode'
					,width: 400
					,fieldLabel: Joomla.JText._('Passcode_for_member_cancelling_membership')
				}]
				,buttons:[{
					text:Joomla.JText._('save')
					,handler: function()	{
						oseMsc.config.paymentParams.beanstreamForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]
			})


			oseMsc.config.paymentParams.vpcashForm = new Ext.FormPanel({
				title: Joomla.JText._('VirtualPayCash')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 390
				,items:[{
						xtype: 'panel'
						,title: Joomla.JText._('Tips')
						,border: false
						,items:[{
							html: Joomla.JText._('1_Currency_Please_use_USD_EUR_and_CFA')
							,border:false
						},{
							html: Joomla.JText._('2_No_subscription_automatic_for_this_payment_gateway')
							,border:false
						}]
					},{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_vpcash'
						,fieldLabel: Joomla.JText._('Enable_virtualPayCash')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_vpcash'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.vpcashForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'radiogroup'
						,name: 'vpcash_testmode'
						,fieldLabel: Joomla.JText._('Test_Mode')
						,width: 200
						,defaults:{xtype:'radio',name:'vpcash_testmode'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'textfield'
						,name:'vpcash_account'
						,fieldLabel: Joomla.JText._('VirtualPayCash_Account')
						,width: 400
						,vtype: 'alphanum'
					},{
						xtype: 'textfield'
						,name:'vpcash_email'
						,fieldLabel: Joomla.JText._('VirtualPayCash_Email')
						,width: 400
						,vtype: 'email'
					},{
						xtype: 'numberfield'
						,name:'vpcash_storeid'
						,fieldLabel: Joomla.JText._('VirtualPayCash_Store_ID')
						,width: 400
					},{
						xtype: 'textfield'
						,name:'vpcash_secureword'
						,inputType: 'password'
						,fieldLabel: Joomla.JText._('Merchant_Secure_Word')
						,width: 400
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.vpcashForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});

			oseMsc.config.paymentParams.bbvaForm = new Ext.FormPanel({
				title: Joomla.JText._('BBVA')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 390
				,items:[{
						xtype: 'panel'
						,title: Joomla.JText._('Tips')
						,border: false
						,items:[{
							html: Joomla.JText._('1_Currency_moneda_Please_input_3_numbersFor_example_978_Euros')
							,border:false
						}]
					},{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_bbva'
						,fieldLabel: Joomla.JText._('Enable_BBVA')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_bbva'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.bbvaForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'radiogroup'
						,name: 'bbva_testmode'
						,fieldLabel: Joomla.JText._('Test_Mode')
						,width: 200
						,defaults:{xtype:'radio',name:'bbva_testmode'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'textfield'
						,name:'bbva_clave'
						,fieldLabel: Joomla.JText._('Key_code_clave')
						,width: 400
						//,vtype: 'alphanum'
					},{
						xtype: 'textfield'
						,name:'bbva_comercio'
						,fieldLabel: Joomla.JText._('Commerce_code')
						,width: 400
						,vtype: 'alphanum'
					},{
						xtype: 'textfield'
						,name:'bbva_terminal'
						,fieldLabel: Joomla.JText._('Terminal_ID')
						,width: 400
					},{
						xtype: 'numberfield'
						,name:'bbva_currency'
						,fieldLabel: Joomla.JText._('Currency_moneda')
						,width: 400
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.bbvaForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});

			oseMsc.config.paymentParams.payfastForm = new Ext.FormPanel({
				title: Joomla.JText._('PayFast')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 390
				,items:[{
						xtype: 'panel'
						,title: Joomla.JText._('Tips')
						,border: false
						,items:[{
							html: Joomla.JText._('When_you_enable_test_mode_there_is_no_need_to_use_your_live_account_information_To_test_with_the_sandbox_use_the_following_login_credentials_when_redirected_to_the_PayFast_site_Username_sbtu01_payfastcoza_Password_clientpass_Here_is_the_introduction')+' <a href="http://www.payfast.co.za/c/std/integration-guide#testing-strategy"  target="_blank">'+Joomla.JText._('LINK')+' </a>;'
							,border:false
						}]
					},{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_payfast'
						,fieldLabel: Joomla.JText._('Enable_PayFast')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_payfast'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.payfastForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'radiogroup'
						,name: 'payfast_testmode'
						,fieldLabel: Joomla.JText._('Test_Mode')
						,width: 200
						,defaults:{xtype:'radio',name:'payfast_testmode'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'textfield'
						,name:'payfast_merchant_id'
						,fieldLabel: Joomla.JText._('PayFast_Merchant_ID')
						,width: 400
						,vtype: 'alphanum'
					},{
						xtype: 'textfield'
						,name:'payfast_merchant_key'
						,fieldLabel: Joomla.JText._('PayFast_Merchant_Key')
						,width: 400
						,vtype: 'alphanum'
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.payfastForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});

			oseMsc.config.paymentParams.ewayshForm = new Ext.FormPanel({
				title: Joomla.JText._('Eway_co_nz')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 700
				,items:[{
						xtype: 'panel'
						,title: Joomla.JText._('Tips')
						,border: false
						,items:[
							{
								html: Joomla.JText._('When_you_enable_test_mode_there_is_no_need_to_use_your_live_account_information_Test_Creditcard_4444333322221111_Here_is_the_introduction')+' <a href="http://www.eway.co.nz/developer/testing-eway-payment-solution.aspx"  target="_blank"> '+Joomla.JText._('LINK')+' </a>;</br>'+Joomla.JText._('CompanyLogo_and_PageBanner_It_should_be_a_ssl_url_example_https_wwwyoursitecom_images_logogif_and_it_should_be_restricted_to_960px_X_65px')
								,border:false
							}
						]
					},{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_ewaysh'
						,fieldLabel: Joomla.JText._('Enable_Eway_co_nz')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_ewaysh'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.ewayshForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'radiogroup'
						,name: 'ewaysh_testmode'
						,fieldLabel: Joomla.JText._('Test_Mode')
						,width: 200
						,defaults:{xtype:'radio',name:'ewaysh_testmode'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'textfield'
						,name:'ewaysh_customer_id'
						,fieldLabel: Joomla.JText._('eWay_Customer_ID')
						,width: 400
						,vtype: 'alphanum'
					},{
						xtype: 'textfield'
						,name:'ewaysh_username'
						,fieldLabel: Joomla.JText._('eWay_Username')
						,width: 400
						,vtype: 'alphanum'
					},{
						xtype: 'textfield'
						,name:'ewaysh_pagetitle'
						,fieldLabel: Joomla.JText._('Page_Title')
						,width: 400
					},{
						xtype: 'textarea'
						,name:'ewaysh_pagedes'
						,fieldLabel: Joomla.JText._('Page_Description')
						,width: 400
					},{
						xtype: 'textfield'
						,name:'ewaysh_pagefooter'
						,fieldLabel: Joomla.JText._('Page_Footer')
						,width: 400
					},{
						xtype: 'textfield'
						,name:'ewaysh_companyname'
						,fieldLabel: Joomla.JText._('Company_Name')
						,width: 400
					},{
						xtype: 'textfield'
						,name:'ewaysh_logourl'
						,fieldLabel: Joomla.JText._('Company_Logo_Url')
						,width: 400
					},{
						xtype: 'textfield'
						,name:'ewaysh_banner'
						,fieldLabel: Joomla.JText._('Company_Banner')
						,width: 400
					},{
						xtype:'combo'
						,fieldLabel: Joomla.JText._('Language')
			            ,hiddenName: 'ewaysh_language'
			            ,width:400
					    ,typeAhead: true
					    ,triggerAction: 'all'
					    ,lazyRender:false
					    ,mode: 'local'

					    ,store: new Ext.data.ArrayStore({
					        id: 0,
					        fields: [
					            'value',
					            'text'
					        ],
					        data: [
					        	['EN', Joomla.JText._('English')],
								['ES', Joomla.JText._('Spanish')],
					        	['FR', Joomla.JText._('French')],
					        	['DE', Joomla.JText._('German')],
					        	['NL', Joomla.JText._('Dutch')]

					        ]
					    })
					    ,valueField: 'value'
					    ,displayField: 'text'
					},{
						xtype: 'radiogroup'
						,name: 'ewaysh_mcd'
						,fieldLabel: Joomla.JText._('Modifiable_Customer_Details')
						,width: 200
						,defaults:{xtype:'radio',name:'ewaysh_mcd'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.ewayshForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});

			oseMsc.config.paymentParams.clickbankForm = new Ext.FormPanel({
				title: Joomla.JText._('ClickBank')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 390
				,items:[{
						xtype: 'panel'
						,title: Joomla.JText._('Tips')
						,border: false
						,items:[{
							html: Joomla.JText._('1IPN_Setting_Login_you_ClickBank_account_go_to_Account_Settings_My_Site_Advanced_Tools_Instant_Notification_URL_1_Input_the_the_notification_URL_here_examplehttp_wwwyousitecom_components_com_osemsc_ipn_clickbank_notifyphp_2You_can_get_the_Secret_Key_and_generate_the_test_credit_card_under_the_Account_Settings_My_Site_Advanced_Tools_and_Testing_Your_Products_3Please_set_the_ClickBank_Item_Number_in_the_Membership_Management_Advance_Payment_Setting_Panel')
							,border:false
						}]
					},{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_clickbank'
						,fieldLabel: Joomla.JText._('Enable_ClickBank')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_clickbank'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.clickbankForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'radiogroup'
						,name: 'clickbank_testmode'
						,fieldLabel: Joomla.JText._('Test_Mode')
						,width: 200
						,defaults:{xtype:'radio',name:'clickbank_testmode'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'textfield'
						,name:'clickbank_account'
						,fieldLabel: Joomla.JText._('ClickBank_Account')
						,width: 400
						,vtype: 'alphanum'
					},{
						xtype: 'textfield'
						,name:'clickbank_secret_key'
						,fieldLabel: Joomla.JText._('ClickBank_Secret_Key')
						,width: 400
						,inputType: 'password'
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.clickbankForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});

			oseMsc.config.paymentParams.ccavenueForm = new Ext.FormPanel({
				title: Joomla.JText._('CCAvenue')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 390
				,items:[{
						xtype: 'panel'
						,title: Joomla.JText._('Tips')
						,border: false
						,items:[{
							html: ''
							,border:false
						}]
					},{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_ccavenue'
						,fieldLabel: Joomla.JText._('Enable_CCAvenue')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_ccavenue'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.ccavenueForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'radiogroup'
						,name: 'ccavenue_testmode'
						,fieldLabel: Joomla.JText._('Test_Mode')
						,width: 200
						,defaults:{xtype:'radio',name:'ccavenue_testmode'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'textfield'
						,name:'ccavenue_merchant_id'
						,fieldLabel: Joomla.JText._('CCAvenue_Merchant_Id')
						,width: 400
						,vtype: 'alphanum'
					},{
						xtype: 'textfield'
						,name:'ccavenue_working_key'
						,fieldLabel: Joomla.JText._('CCAvenue_Working_Key')
						,width: 400
						,inputType: 'password'
					},{
						xtype: 'combo'
						,hiddenName: 'ccavenue_currency'
						,id: 'ccavenue_currency'
						,fieldLabel: Joomla.JText._('Processing_Currency')
						,width: 400
						,typeAhead: true
						,triggerAction: 'all'
						,lazyRender:true
						,mode: 'local'
						,store: new Ext.data.ArrayStore({
						    id: 0
						    ,fields: [
						        'id'
						        ,'displayText'
						    ]
						    ,data: [
						        ['INR', 'INR']
						        ,['USD', 'USD']
							    ]
						})
						,valueField: 'id'
						,displayField: 'displayText'
						,emptyText: Joomla.JText._('Please_Choose')
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.ccavenueForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});

			oseMsc.config.paymentParams.usaepayForm = new Ext.FormPanel({
				title: Joomla.JText._('USA_ePay')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 390
				,items:[{
						xtype: 'panel'
						,hidden:true
						,title: Joomla.JText._('Tips')
						,border: false
						,items:[{
							html: ''
							,border:false
						}]
					},{
						xtype: 'radiogroup'
						,id: 'usaepayEnable'
						,inputValue: 1
						,name: 'enable_usaepay'
						,fieldLabel: Joomla.JText._('Enable_USA_ePay')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_usaepay'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.usaepayForm.findByType('field'),function(item,i,all)	{
									//alert(item.getXType())
									if(item.id != 'usaepayEnable')
									{
										item.setDisabled(checked.getGroupValue() == 0)
									}
								})
							}
						}
					},{
						xtype: 'radiogroup'
						,name: 'usaepay_testmode'
						,fieldLabel: Joomla.JText._('Test_Mode')
						,width: 200
						,defaults:{xtype:'radio',name:'usaepay_testmode'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'radiogroup'
						,name: 'usaepay_usesandbox'
						,fieldLabel: Joomla.JText._('Use_Sandbox')
						,width: 200
						,defaults:{xtype:'radio',name:'usaepay_usesandbox'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'textfield'
						,name:'usaepay_source_key'
						,fieldLabel: Joomla.JText._('Source_Key')
						,width: 400
						//,vtype: 'alphanum'
						,inputType: 'password'
					},{
						xtype: 'radiogroup'
						,name: 'usaepay_sendReceipt'
						,fieldLabel: Joomla.JText._('Email_Receipt_from_Gateway_to_Customers')
						,width: 200
						,defaults:{xtype:'radio',name:'usaepay_sendReceipt'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.usaepayForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});

			oseMsc.config.paymentParams.icepayForm = new Ext.FormPanel({
				title: Joomla.JText._('ICE_Pay_Beta')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 450
				,items:[{
						xtype: 'panel'
						,title: Joomla.JText._('Tips')
						,border: false
						,items:[{
							html: Joomla.JText._('By_setting_the_arguments_such_as_country_language_etc_you_will_be_able_to_influence_the_list_of_available_payment_methods_For_instance_if_you_provide_US_dollar_USD_as_the_currency_then_the_iDEAL_payment_method_will_disappear_from_the_list_as_this_payment_method_does_not_support_the_US_dollar_currency_Supported_currencies_GBPEURUSD_Please_input_the_notification_url_into_you_ICEPAY_Account_My_websites_Configure_URL_Postback_URLFor_examplehttp_wwwyoursitecom_components_com_osemsc_ipn_icepay_notifyphp')
							,border:false
						}]
					},{
						xtype: 'radiogroup'
						,id: 'icepayEnable'
						,inputValue: 1
						,name: 'enable_icepay'
						,fieldLabel: Joomla.JText._('Enable_ICE_Pay')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_icepay'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.icepayForm.findByType('field'),function(item,i,all)	{
									//alert(item.getXType())
									if(item.id != 'icepayEnable')
									{
										item.setDisabled(checked.getGroupValue() == 0)
									}
								})
							}
						}
					},{
						xtype: 'radiogroup'
						,name: 'icepay_testmode'
						,fieldLabel: Joomla.JText._('Test_Mode')
						,width: 200
						,defaults:{xtype:'radio',name:'icepay_testmode'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'textfield'
						,name:'icepay_merchant_id'
						,fieldLabel: Joomla.JText._('Merchant_ID')
						,width: 400
					},{
						xtype: 'textfield'
						,name:'icepay_secret_code'
						,fieldLabel: Joomla.JText._('Secret_code')
						,width: 400
						//,vtype: 'alphanum'
						,inputType: 'password'
					},{
						xtype: 'combo'
						,hiddenName: 'icepay_country'
						,id: 'icepay_country'
						,fieldLabel: Joomla.JText._('Country')
						,width: 400
						,typeAhead: true
						,triggerAction: 'all'
						,lazyRender:true
						,mode: 'local'
						,store: new Ext.data.ArrayStore({
						    id: 0
						    ,fields: [
						        'id'
						        ,'displayText'
						    ]
						    ,data: [
						        ['0', Joomla.JText._('Detect_country_from_user_billing_Info')]
						    	,['00', Joomla.JText._('Country_does_not_matter')]
						    	,['NL', Joomla.JText._('Netherlands')]
						    	,['AT', Joomla.JText._('Austria')]
						    	,['AU', Joomla.JText._('Australia')]
						    	,['BE', Joomla.JText._('Belgium')]
						    	,['CA', Joomla.JText._('Canada')]
						    	,['CH', Joomla.JText._('Switzerland')]
						    	,['CZ', Joomla.JText._('Czech_Republic')]
						    	,['DE', Joomla.JText._('Germany')]
						    	,['ES', Joomla.JText._('Spain')]
						    	,['FR', Joomla.JText._('France')]
						    	,['IT', Joomla.JText._('Italy')]
						    	,['LU', Joomla.JText._('Luxembourg')]
						    	,['PL', Joomla.JText._('Poland')]
						    	,['PT', Joomla.JText._('Portugal')]
						    	,['SK', Joomla.JText._('Slovakia')]
						    	,['GB', Joomla.JText._('United_Kingdom')]
						    	,['US', Joomla.JText._('United_States')]
							    ]
						})
						,valueField: 'id'
						,displayField: 'displayText'
						,emptyText: Joomla.JText._('Please_Choose')
					},{
						xtype: 'combo'
						,hiddenName: 'icepay_lang'
						,id: 'icepay_lang'
						,fieldLabel: Joomla.JText._('Language')
						,width: 400
						,typeAhead: true
						,triggerAction: 'all'
						,lazyRender:true
						,mode: 'local'
						,store: new Ext.data.ArrayStore({
						    id: 0
						    ,fields: [
						        'id'
						        ,'displayText'
						    ]
						    ,data: [
						        ['0', Joomla.JText._('Detect_language')]
						        ,['NL', Joomla.JText._('Dutch')]
						    	,['DE', Joomla.JText._('German')]
						    	,['EN', Joomla.JText._('English')]
						    	,['FR', Joomla.JText._('French')]
						    	,['ES', Joomla.JText._('Spanish')]
						    	,['IT', Joomla.JText._('Italian')]
						    	,['LV', Joomla.JText._('Latvian')]
						    	,['RU', Joomla.JText._('Russian')]
							    ]
						})
						,valueField: 'id'
						,displayField: 'displayText'
						,emptyText: Joomla.JText._('Please_Choose')
					},{
						xtype: 'radiogroup'
						,name: 'icepay_checkIP'
						,fieldLabel: Joomla.JText._('Enable_IP_Check_function_in_IPN')
						,width: 200
						,defaults:{xtype:'radio',name:'icepay_checkIP'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.icepayForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});
			
			oseMsc.config.paymentParams.ebsForm = new Ext.FormPanel({
				title: Joomla.JText._('EBS')
				,defaults: { bodyStyle : 'padding: 15px'}
				,labelWidth: 250
				,height: 300
				,reader:oseMsc.config.paymentReader
				,items:[{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_ebs'
						,fieldLabel: Joomla.JText._('Enable_EBS')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_ebs'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.ebsForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'radiogroup'
						,name: 'ebs_testmode'
						,fieldLabel: Joomla.JText._('Test_Mode')
						,width: 200
						,defaults:{xtype:'radio',name:'ebs_testmode'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
				},{
					xtype: 'textfield',
					name: 'ebs_merchantID',
					fieldLabel: Joomla.JText._('EBS_Merchant_ID'),
					width: 400
				},{
					xtype: 'textfield',
					name: 'ebs_secretKey',
					width: 400,
					fieldLabel: Joomla.JText._('EBS_Secret_Key')
				}]
				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.ebsForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]
			});

			oseMsc.config.paymentParams.liqpayForm = new Ext.FormPanel({
				title: Joomla.JText._('LiqPAY')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 390
				,items:[{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_liqpay'
						,fieldLabel: 'Enable LiqPAY'
						,width: 200
						,defaults:{xtype:'radio',name:'enable_liqpay'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.liqpayForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'radiogroup'
						,name: 'liqpay_testmode'
						,fieldLabel: Joomla.JText._('Test_Mode')
						,width: 200
						,defaults:{xtype:'radio',name:'liqpay_testmode'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'textfield'
						,name:'liqpay_merchant_id'
						,fieldLabel: Joomla.JText._('LiqPAY_Merchant_ID')
						,width: 400
						,vtype: 'alphanum'
					},{
						xtype: 'textfield'
						,name:'liqpay_signature'
						,fieldLabel: Joomla.JText._('LiqPAY_Password_Signature')
						,width: 400
					},{
						xtype: 'textfield'
						,name:'liqpay_phone'
						,fieldLabel: Joomla.JText._('LiqPAY_Receiver_Phone')
						,width: 400
					},{
						xtype:'combo'
						,fieldLabel: Joomla.JText._('Payment')
				        ,hiddenName: 'liqpay_payment'
				        ,width:400
						,typeAhead: true
						,triggerAction: 'all'
						,lazyRender:false
						,mode: 'local'
						,store: new Ext.data.ArrayStore({
						     id: 0,
						     fields: [
						        'value',
						        'text'
						     ],
						     data: [
						      	['liqpay,card', 'liqpay, card'],
								['liqpay', "liqpay"],
						       	['card', "card"]
						        ]
					    })
					    ,valueField: 'value'
					    ,displayField: 'text'
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.liqpayForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});
			
			oseMsc.config.paymentParams.virtualmerchantForm = new Ext.FormPanel({
				title: Joomla.JText._('Virtual_Merchant')
				,defaults: { bodyStyle : 'padding: 15px'}
				,labelWidth: 250
				,height: 400
				,reader:oseMsc.config.paymentReader
				,items:[{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_ebs'
						,fieldLabel: Joomla.JText._('Enable_VirtualMerchant')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_virtualmerchant'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.virtualmerchantForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'radiogroup'
						,name: 'virtualmerchant_testmode'
						,fieldLabel: Joomla.JText._('Test_Mode')
						,width: 200
						,defaults:{xtype:'radio',name:'virtualmerchant_testmode'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
				},{
					xtype: 'textfield',
					name: 'virtualmerchant_merchant_id',
					fieldLabel: Joomla.JText._('Virtual_Merchant_Merchant_ID'),
					width: 400
				},{
					xtype: 'textfield',
					name: 'virtualmerchant_user_id',
					width: 400,
					fieldLabel: Joomla.JText._('Virtual_Merchant_User_ID')
				},{
					xtype: 'textfield',
					name: 'virtualmerchant_pin',
					width: 400,
					inputType: 'password',
					fieldLabel: Joomla.JText._('Virtual_Merchant_PIN_Number')
				},{
					xtype: 'radiogroup'
					,name: 'virtualmerchant_sendCustomerEmail'
					,fieldLabel: Joomla.JText._('Send_Confirmation_Receipt_to_Customer_from_Virtual_Merchant')
					,width: 200
					,defaults:{xtype:'radio',name:'virtualmerchant_sendCustomerEmail'}
					,items:[
					    {boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
					]
				},{
					xtype: 'radiogroup'
					,name: 'virtualmerchant_sendMerchantEmail'
					,fieldLabel: Joomla.JText._('Send_Confirmation_Receipt_to_Merchant_from_Virtual_Merchant')
					,width: 200
					,defaults:{xtype:'radio',name:'virtualmerchant_sendMerchantEmail'}
					,items:[
					    {boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
					]
				},{
					xtype: 'textfield',
					name: 'virtualmerchant_merchant_email',
					width: 400,
					vtype:'email',
					fieldLabel: Joomla.JText._('Merchant_Email')
				}]
				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.virtualmerchantForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]
			});
			
			oseMsc.config.paymentParams.realexForm = new Ext.FormPanel({
				title: Joomla.JText._('Realex_Payments')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 450
				,hidden:true
				,items:[{
						xtype: 'panel'
						,title: Joomla.JText._('Tips')
						,border: false
						,items:[{
							html: Joomla.JText._('Not_support_subscription_If_you_are_planning_on_accepting_American_Express_cards_you_need_to_fill_in_the_AMEX_Account_If_you_choose_the_redirect_mode_you_should_config_the_notification_URL_in_your_account_examplehttp_wwwyousitecom_components_com_osemsc_ipn_realex_notifyphp')
							,border:false
						}]
					},{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_realex'
						,fieldLabel: Joomla.JText._('Enable_Realex_Payments')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_realex'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.realexForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'textfield'
						,name:'realex_merchant_id'
						,fieldLabel: Joomla.JText._('Realex_Payments_Merchant_ID')
						,width: 400
						,vtype: 'alphanum'
					},{
						xtype: 'textfield'
						,name:'realex_secret'
						,fieldLabel: Joomla.JText._('Realex_Payments_Secret')
						,width: 400
						,inputType: 'password'
					},{
						xtype: 'textfield'
						,name:'realex_account'
						,fieldLabel: Joomla.JText._('Realex_Payments_Account')
						,width: 400
					},{
						xtype: 'textfield'
						,name:'realex_amex_account'
						,fieldLabel: Joomla.JText._('AMEX_Account')
						,width: 400
					},{
						xtype:'combo'
						,fieldLabel: Joomla.JText._('Payment_Method')
				        ,hiddenName: 'realex_mode'
				        ,width:400
						,typeAhead: true
						,triggerAction: 'all'
						,lazyRender:false
						,mode: 'local'
						,store: new Ext.data.ArrayStore({
						     id: 0,
						     fields: [
						        'value',
						        'text'
						     ],
						     data: [
						      	['redirect', Joomla.JText._('Redirect')],
								['remote', Joomla.JText._('Remote')]
						        ]
					    })
					    ,valueField: 'value'
					    ,displayField: 'text'
					},{
						xtype:'combo'
						,fieldLabel: Joomla.JText._('Defer_Settlement')
					    ,hiddenName: 'realex_type'
					    ,width:400
						,typeAhead: true
						,triggerAction: 'all'
						,lazyRender:false
						,mode: 'local'
						,store: new Ext.data.ArrayStore({
						     id: 0,
						     fields: [
						        'val',
						        'title'
						     ],
						     data: [
						      	['0', Joomla.JText._('Defer_Settlement')],
								['1', Joomla.JText._('Settle_Immediately')]
						        ]
						})
						,valueField: 'val'
						,displayField: 'title'
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.realexForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});
			
			oseMsc.config.paymentParams.sisowForm = new Ext.FormPanel({
				title: Joomla.JText._('Sisow_Setting')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 350
				,items:[{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_sisow'
						,fieldLabel: Joomla.JText._('Enable_Sisow')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_sisow'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.sisowForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'textfield'
						,name:'sisow_merchant_id'
						,fieldLabel: Joomla.JText._('Sisow_Merchant_ID')
						,width: 400
					},{
						xtype: 'radiogroup'
						,id:'testmode'	
						,inputValue: 1
						,name: 'sisow_testmode'
						,fieldLabel: Joomla.JText._('Test_Mode')
						,width: 200
						,defaults:{xtype:'radio',name:'sisow_testmode'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
					},{
						xtype: 'textfield'
						,name:'sisow_merchant_key'
						,fieldLabel: Joomla.JText._('Sisow_Merchant_Key')
						,width: 400
						,inputType: 'password'
					},{
						xtype: 'textfield'
						,name:'sisow_shop_id'
						,fieldLabel: Joomla.JText._('Sisow_Shop_ID')
						,width: 400
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.sisowForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});
			
			oseMsc.config.paymentParams.pagseguroForm = new Ext.FormPanel({
				title: Joomla.JText._('PagSeguro_Setting')
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 200
				,items:[{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_pagseguro'
						,fieldLabel: Joomla.JText._('Enable_PagSeguro')
						,width: 200
						,defaults:{xtype:'radio',name:'enable_pagseguro'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.pagseguroForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'textfield'
						,name:'pagseguro_account'
						,fieldLabel: Joomla.JText._('PagSeguro_Account')
						,width: 400
						,vtype: 'email'
					},{
						xtype: 'textfield'
						,name:'pagseguro_token'
						,fieldLabel: Joomla.JText._('PagSeguro_Token')
						,width: 400
						,inputType: 'password'
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.pagseguroForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});
			
			oseMsc.config.paymentParams.paygateForm = new Ext.FormPanel({
				title: 'PayGate Setting'
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 400
				,items:[{
						xtype: 'panel'
						,title: Joomla.JText._('Tips')
						,border: false
						,items:[{
							html: 'Test PayGate ID:10011013800</br>Test PayGate Key:secret</br>Test Credit Cards:</br>VISA:4000000000000002</br>MasterCard:5200000000000015</br>American Express:378282246310005'
							,border: false

						}]
					},{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_paygate'
						,fieldLabel: 'Enable PayGate'
						,width: 200
						,defaults:{xtype:'radio',name:'enable_paygate'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.paygateForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'textfield'
						,name:'paygate_id'
						,fieldLabel: 'PayGate ID'
						,width: 400
					},{
						xtype: 'textfield'
						,name:'paygate_key'
						,fieldLabel: 'PayGate Key'
						,width: 400
						,inputType: 'password'
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.paygateForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});
			
			oseMsc.config.paymentParams.quickpayForm = new Ext.FormPanel({
				title: 'Quickpay'
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 550
				,items:[{
						xtype: 'panel'
						,title: 'Tips'
						,border: false
						,items:[{
							html: 'Cardtypelock:Lock payment to one or more payment methods. In doubt leave blank, or look at <a href="http://quickpay.net/features/cardtypelock/"  target="_blank"> LINK </a>.</br>Autocapture:When enabled the transaction will be enabled automatically. See <a href="http://quickpay.net/features/autocapture/"  target="_blank"> LINK </a>.</br>Autofee:When enabled the fee charged by the acquirer will be calculated and added to the transaction amount. See <a href="http://quickpay.net/features/transaction-fees/"  target="_blank"> LINK </a> for more information.</br>You can try the test account here <a href="http://quickpay.net/features/manager/"  target="_blank"> LINK </a>.'
							,border: false

						}]
					},{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_quickpay'
						,fieldLabel: 'Enable Quickpay'
						,width: 200
						,defaults:{xtype:'radio',name:'enable_quickpay'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.quickpayForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'radiogroup'
						,name: 'quickpay_testmode'
						,fieldLabel: 'Test Mode'
						,width: 200
						,defaults:{xtype:'radio',name:'quickpay_testmode'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'textfield'
						,name:'quickpay_merchant'
						,fieldLabel: 'Quickpay Merchant'
						,width: 400
					},{
						xtype: 'textfield'
						,name:'quickpay_secret'
						,fieldLabel: 'Quickpay MD5 Secret'
						,width: 400
						,inputType: 'password'
					},{
						xtype: 'textfield'
						,name:'quickpay_cardtypelock'
						,fieldLabel: 'Cardtypelock'
						,width: 400
					},{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'quickpay_autocapture'
						,fieldLabel: 'Autocapture'
						,width: 200
						,defaults:{xtype:'radio',name:'quickpay_autocapture'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'radiogroup'
						,hidden:true	
						,inputValue: 1
						,name: 'quickpay_splitpayment'
						,fieldLabel: 'Splitpayment'
						,width: 200
						,defaults:{xtype:'radio',name:'quickpay_splitpayment'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'quickpay_autofee'
						,fieldLabel: 'Autofee'
						,width: 200
						,defaults:{xtype:'radio',name:'quickpay_autofee'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
						]
					},{
						xtype: 'combo'
						,hiddenName: 'quickpay_lang'
						,fieldLabel: 'Language'
						,width: 400
						,typeAhead: true
						,triggerAction: 'all'
						,lazyRender:true
						,mode: 'local'
						,store: new Ext.data.ArrayStore({
						    id: 0
						    ,fields: [
						        'id'
						        ,'displayText'
						    ]
						    ,data: [
						    	['da', 'Danish']
						    	,['de', 'German']
						    	,['en', 'English']
						    	,['es', 'Spanish']
						    	,['fo', 'Faeroese']
						    	,['fi', 'Finnish']
						    	,['fr', 'French']
						    	,['kl', 'Greenlandish']
						    	,['it', 'Italian']
						    	,['no', 'Norwegian']
						    	,['nl', 'Dutch']
						    	,['pl', 'Polish']
						    	,['ru', 'Russian']
						    	,['sv', 'Swedish']
						    ]
						})
						,valueField: 'id'
						,displayField: 'displayText'
						,emptyText: Joomla.JText._('Please_Choose')
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.quickpayForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});
			
			oseMsc.config.paymentParams.sagepayForm = new Ext.FormPanel({
				title: 'sagepay Setting'
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 400
				,items:[{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_sagepay'
						,fieldLabel: 'Enable sagepay'
						,width: 200
						,defaults:{xtype:'radio',name:'enable_sagepay'}
						,items:[
							{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
							,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.sagepayForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'textfield'
						,name:'sagepay_vendorname'
						,fieldLabel: 'sagepay VendorName'
						,width: 400
					},{
						xtype: 'textfield'
						,name:'sagepay_password'
						,fieldLabel: 'sagepay Encryption Password'
						,width: 400
						,inputType: 'password'
					},{
						xtype: 'combo'
						,hiddenName: 'sagepay_mode'
						,fieldLabel: 'sagepay Mode'
						,width: 400
						,typeAhead: true
						,triggerAction: 'all'
						,lazyRender:true
						,mode: 'local'
						,store: new Ext.data.ArrayStore({
						    id: 0
						    ,fields: [
						        'id'
						        ,'displayText'
						    ]
						    ,data: [
						    	['simulator', 'Simulator Mode']
						    	,['test', 'Test Mode']
						    	,['live', 'Live Mode']
						    ]
						})
						,valueField: 'id'
						,displayField: 'displayText'
						,listeners: {
							render: function(c)	{
								c.setValue('simulator')
							}
						}
					},{
						xtype: 'combo'
						,hiddenName: 'sagepay_txtype'
						,fieldLabel: 'sagepay TxType'
						,width: 400
						,typeAhead: true
						,triggerAction: 'all'
						,lazyRender:true
						,mode: 'local'
						,store: new Ext.data.ArrayStore({
						    id: 0
						    ,fields: [
						        'id'
						        ,'displayText'
						    ]
						    ,data: [
						    	['PAYMENT', 'PAYMENT']
						    	,['DEFERRED', 'DEFERRED']
						    ]
						})
						,valueField: 'id'
						,displayField: 'displayText'
						,listeners: {
							render: function(c)	{
								c.setValue('PAYMENT')
							}
						}
					},{
						xtype: 'textfield'
						,name:'sagepay_vendoremail'
						,fieldLabel: 'sagepay Vendor EMail'
						,width: 400
						,vType: 'email'
					}]

				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.sagepayForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});
			
			oseMsc.config.paymentParams.alipayForm = new Ext.FormPanel({
				title: 'Alipay'
				,defaults: { bodyStyle : 'padding: 0px'}
				,labelWidth: 200
				,height: 300
				,items:[{
						xtype: 'panel'
						,title: 'Tips'
						,hidden:true	
						,border: false
						,items:[{
							html: ''
							,border: false

						}]
					},{
						xtype: 'radiogroup'
						,inputValue: 1
						,name: 'enable_alipay'
						,fieldLabel: 'Enable Alipay'
						,width: 200
						,defaults:{xtype:'radio',name:'enable_alipay'}
						,items:[
							{boxLabel: 'Yes', inputValue: 1}
							,{boxLabel: 'No', inputValue: 0, checked:true}
						]
						,listeners: {
							change: function(rg,checked)	{
								Ext.each(oseMsc.config.paymentParams.alipayForm.findByType('textfield'),function(item,i,all)	{
									//alert(item.getXType())
									item.setDisabled(checked.getGroupValue() == 0)
								})
							}
						}
					},{
						xtype: 'textfield'
						,name:'alipay_partner'
						,fieldLabel: 'Alipay Partner ID'
						,width: 400
					},{
						xtype: 'textfield'
						,name:'alipay_key'
						,fieldLabel: 'Alipay Key'
						,width: 400
						,inputType: 'password'
					},{
						xtype: 'textfield'
						,name:'alipay_seller_email'
						,fieldLabel: 'Seller Email'
						,width: 400
					},{
						xtype:'combo'
						,fieldLabel: 'Transport'
				        ,hiddenName: 'alipay_transport'
				        ,width:400
						,typeAhead: true
						,triggerAction: 'all'
						,lazyRender:false
						,mode: 'local'
					    ,store: new Ext.data.ArrayStore({
					        id: 0,
					        fields: [
					            'value',
					            'text'
					        ],
					        data: [
					        	['http', 'http'],
								['https', "https"]
						        ]
					    })
					    ,valueField: 'value'
					    ,displayField: 'text'
					    ,emptyText: 'Please Choose'	
				}]

				,buttons:[{
					text:'save',
					handler: function()	{
						oseMsc.config.paymentParams.alipayForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]

			});
			
			oseMsc.config.paymentParams.otherForm = new Ext.FormPanel({
				title: Joomla.JText._('Others')
				,defaults: { bodyStyle : 'padding: 15px'}
				,labelWidth: 250
				,height: 200
				,reader:	oseMsc.config.paymentReader
				,items:[{
					xtype: 'radiogroup'
					,name: 'enable_poffline'
					,fieldLabel: Joomla.JText._('Enable_Pay_Offline')
					,width: 200
					,defaults:{xtype:'radio',name:'enable_poffline'}
					,items:[
						{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1, checked:true}
						,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0}
					]
					,listeners: {
						change: function(rg,checked)	{
							Ext.each(oseMsc.config.paymentParams.otherForm.findByType('textfield'),function(item,i,all)	{
								//alert(item.getXType())
								item.setDisabled(checked.getGroupValue() == 0)
							})
						}
					}
				},{
					xtype: 'textfield'
					,name:'poffline_art_id'
					,fieldLabel: Joomla.JText._('Offline_Payment_Redirection_article_ID')
				}]
				,buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.paymentParams.otherForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'payment'},
							success: function(form,action){
								oseMsc.formSuccess(form,action)
							}
						})
					}
				}]
			})

			oseMsc.config.paymentParams.defaultMode = new Ext.Panel({
				id: 'oseMsc.config.paymentDefault'
				,title: Joomla.JText._('Default_Payment_Setting')
				//,collapsible: true
				,animCollapse: true
				,defaults: { bodyStyle : 'padding: 5px'}
				//,labelWidth: 220
				,autoHeight: true
				,items:[{
					xtype: 'tabpanel'
					,activeItem: 0
					,enableTabScroll: true
					//,autoHeight: true
					//,defaults: {height: 190}
					,border: false
					,items: [
						oseMsc.config.paymentParams.paypalForm
						,oseMsc.config.paymentParams.gcoForm
						,oseMsc.config.paymentParams.twocoForm
						,oseMsc.config.paymentParams.ccForm
						,oseMsc.config.paymentParams.ewayForm
						,oseMsc.config.paymentParams.epayForm
						,oseMsc.config.paymentParams.pnwForm
						,oseMsc.config.paymentParams.beanstreamForm
						,oseMsc.config.paymentParams.vpcashForm
						,oseMsc.config.paymentParams.bbvaForm
						,oseMsc.config.paymentParams.payfastForm
						,oseMsc.config.paymentParams.ewayshForm
						,oseMsc.config.paymentParams.clickbankForm
						,oseMsc.config.paymentParams.ccavenueForm
						,oseMsc.config.paymentParams.usaepayForm
						,oseMsc.config.paymentParams.icepayForm
						,oseMsc.config.paymentParams.ebsForm
						,oseMsc.config.paymentParams.liqpayForm
						//,oseMsc.config.paymentParams.virtualmerchantForm
						,oseMsc.config.paymentParams.realexForm
						,oseMsc.config.paymentParams.sisowForm
						,oseMsc.config.paymentParams.pagseguroForm
						,oseMsc.config.paymentParams.paygateForm
						,oseMsc.config.paymentParams.quickpayForm
						,oseMsc.config.paymentParams.sagepayForm
						,oseMsc.config.paymentParams.alipayForm
						,oseMsc.config.paymentParams.otherForm
					]
				}]
			});

			oseMsc.config.paymentForm = new Ext.Panel({
				title : Joomla.JText._('Payment')
				,id: 'oseMsc.config.paymentForm'
				,border: false
				,height: 730

				,bodyStyle: 'padding-left: 15px;padding-right: 15px;padding-top: 10px;padding-bottom: 10px;'
				,defaults:{bodyStyle:'padding:10px'}
				,items:[
					oseMsc.config.payment
					,oseMsc.config.paymentParams.defaultMode
				]

				,listeners: {
					render: function(p){
						oseMsc.config.paymentParams.paypalForm.getForm().load({
							url: 'index.php?option=com_osemsc&controller=config'
							,params:{task:'getConfig',config_type:'payment'}
							,success: function(form,action)	{
								var result = action.result;//Ext.decode(response.responseText);

								oseMsc.config.paymentParams.gcoForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.twocoForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.ewayForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.ccForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.otherForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.epayForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.pnwForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.beanstreamForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.vpcashForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.bbvaForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.payfastForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.ewayshForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.clickbankForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.ccavenueForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.usaepayForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.icepayForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.ebsForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.liqpayForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.realexForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.virtualmerchantForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.sisowForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.pagseguroForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.paygateForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.quickpayForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.sagepayForm.getForm().setValues(result.data);
								oseMsc.config.paymentParams.alipayForm.getForm().setValues(result.data);
								
								oseMsc.config.paymentParams.gcoForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_gco');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.twocoForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_twoco');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.ewayForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_eway');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.ccForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_cc');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.otherForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_poffline');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.epayForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_epay');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.pnwForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_pnw');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.beanstreamForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_beanstream');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.vpcashForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_vpcash');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.bbvaForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_bbva');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.payfastForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_payfast');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.ewayshForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_ewaysh');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.clickbankForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_clickbank');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.ccavenueForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_ccavenue');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.usaepayForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_usaepay');
									rg.fireEvent('change',rg,rg.getValue())
								})

								oseMsc.config.paymentParams.icepayForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_icepay');
									rg.fireEvent('change',rg,rg.getValue())
								})
								
								oseMsc.config.paymentParams.ebsForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_ebs');
									rg.fireEvent('change',rg,rg.getValue())
								})
								
								oseMsc.config.paymentParams.liqpayForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_liqpay');
									rg.fireEvent('change',rg,rg.getValue())
								})
								
								oseMsc.config.paymentParams.virtualmerchantForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_virtualmerchant');
									rg.fireEvent('change',rg,rg.getValue())
								})
								
								oseMsc.config.paymentParams.realexForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_realex');
									rg.fireEvent('change',rg,rg.getValue())
								})
								
								oseMsc.config.paymentParams.pagseguroForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_pagseguro');
									rg.fireEvent('change',rg,rg.getValue())
								})
								
								oseMsc.config.paymentParams.paygateForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_paygate');
									rg.fireEvent('change',rg,rg.getValue())
								})
								
								oseMsc.config.paymentParams.quickpayForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_quickpay');
									rg.fireEvent('change',rg,rg.getValue())
								})
								
								oseMsc.config.paymentParams.sagepayForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_sagepay');
									rg.fireEvent('change',rg,rg.getValue())
								})
						
								oseMsc.config.paymentParams.alipayForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_alipay');
									rg.fireEvent('change',rg,rg.getValue())
								})
						
								oseMsc.config.paymentParams.sisowForm.on('activate',function(p)	{
									var rg = p.getForm().findField('enable_sisow');
									rg.fireEvent('change',rg,rg.getValue())
								})
							}
						})
					}
				}
			});
		}
}
	