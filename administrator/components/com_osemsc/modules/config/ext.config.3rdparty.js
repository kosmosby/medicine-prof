Ext.ns('oseMsc','oseMsc.config','oseMsc.config.thirdParty');
oseMsc.config.thirdPartyPanelInit = function()	{

}

oseMsc.config.thirdPartyPanelInit.prototype = {
		init: function()	{
			oseMsc.config.thirdParty.pap = function()	{
				return {
					init:function()	{
						var fs = new Ext.form.FieldSet({
							title: Joomla.JText._('Affiliate_Software_Integration')
							,bodyStyle:'padding:10px'
							,labelWidth: 350
							//,border: false
							,defaults: {width: 300}
							,items:[
							{   fieldLabel: Joomla.JText._('Tips'),
							    width: 900,
							    border: false,
							    html: '<div class="padding: 10px;"> WIKI: '+Joomla.JText._('Please_see_this_wiki')+' <a href="http://wiki.opensource-excellence.com/index.php?title=Post_Affiliate_Pro_and_Idev_Affiliate"  target="_blank"> '+Joomla.JText._('LINK')+' </a> '+Joomla.JText._('if_you_need_help_on_this_issue')+'</div>'
							},
							{
								fieldLabel: Joomla.JText._('Enable_Post_Affiliate_Pro_Integration')
								,xtype: 'radiogroup'
								,name: 'pap_enable'
								,defaults:{xtype:'radio',name:'pap_enable'}
								,items:[
									{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
									,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
								]
							},{
								fieldLabel: Joomla.JText._('PAP_Full_URL_eg_https_wwwyoursitecom_affiliate')
								,xtype: 'textfield'
								,name: 'pap_url'
							},{
								fieldLabel: Joomla.JText._('Account_ID_default_value_default1')
								,xtype: 'textfield'
								,name: 'pap_account_id'
							},{
								fieldLabel: Joomla.JText._('PAP_Username')
								,xtype: 'textfield'
								,name: 'pap_username'
							},{
								fieldLabel: Joomla.JText._('PAP_Password')
								,xtype: 'textfield'
								,inputType: 'pap_password'
								,vtype: 'alphanum'
								,name: 'pap_password'
							},{
								fieldLabel: Joomla.JText._('Enable_iDevAffiliate_Integration')
								,xtype: 'radiogroup'
								,name: 'idev_enable'
								,defaults:{xtype:'radio',name:'idev_enable'}
								,items:[
									{boxLabel: Joomla.JText._('ose_Yes'), inputValue: 1}
									,{boxLabel: Joomla.JText._('ose_No'), inputValue: 0, checked:true}
								]
							},{
								fieldLabel: Joomla.JText._('iDevAffiliate_Full_URL_eg_https_wwwyoursitecom_affiliate')
								,xtype: 'textfield'
								,name: 'idev_url'
							}]

						});

						var fs2 = new Ext.form.FieldSet({
							title: Joomla.JText._('MailChimp_Integration')
							,bodyStyle:'padding:10px'
							,labelWidth: 350
							,border: false
							,defaults: {width: 300}
							,items:[
							{
								fieldLabel: Joomla.JText._('MailChimp_API_Key')
								,xtype: 'textfield'
								,name: 'mailchimp_api_key'
							}]
			
						});
						
						var fs3 = new Ext.form.FieldSet({
							title: 'Google Analytics Ecommerce Tracking'
							,bodyStyle:'padding:10px'
							,labelWidth: 350
							,border: false
							,defaults: {width: 300}
							,items:[
							{
								fieldLabel: 'Account'
								,xtype: 'textfield'
								,name: 'gag_account'
							},{
								fieldLabel: 'Mode'
								,xtype: 'textfield'
								,name: 'gag_domain_mode'
								,hidden:true
								,listeners: {
							  		render: function(p){
										p.setValue(1);
									}			
								}
							},{
								fieldLabel: 'Domain'
								,xtype: 'textfield'
								,name: 'gag_domain'
							}]
			
						});
						
						var fp = new Ext.form.FormPanel({
							title: Joomla.JText._('Affiliate_Software_Integration')
							,bodyStyle:'padding:10px'
							,items: [fs,fs2,fs3]
							,reader: new Ext.data.JsonReader({
							    root: 'result',
							    totalProperty: 'total',
							    fields:[
								    {name: 'id', type: 'int', mapping: 'id'}
								    ,{name: 'pap_url', type: 'string', mapping: 'pap_url'}
								    ,{name: 'pap_enable', type: 'string', mapping: 'pap_enable'}
								    ,{name: 'pap_account_id', type: 'string', mapping: 'pap_account_id'}
								    ,{name: 'pap_username', type: 'string', mapping: 'pap_username'}
								    ,{name: 'pap_password', type: 'string', mapping: 'pap_password'}
								    ,{name: 'idev_url', type: 'string', mapping: 'idev_url'}
								    ,{name: 'idev_enable', type: 'string', mapping: 'idev_enable'}
								    ,{name: 'mailchimp_api_key', type: 'string', mapping: 'mailchimp_api_key'}
								    ,{name: 'gag_account', type: 'string', mapping: 'gag_account'}
								    ,{name: 'gag_domain_mode', type: 'string', mapping: 'gag_domain_mode'}
								    ,{name: 'gag_domain', type: 'string', mapping: 'gag_domain'}
							  	]
						  	})
							,buttons:[{
								text:Joomla.JText._('Save')
								,handler:function()	{
									if(!fp.getForm().findField('pap_enable').getValue())	{
										fp.getForm().findField('pap_enable').setValue(0)
									}
									fp.getForm().submit({
										clientValidation: true
										,url: 'index.php?option=com_osemsc&controller=config'
										,params:{task:'save',config_type:'thirdparty'}
										,success: oseMsc.formSuccess
									})
								}
								,scope: this
							}]
							,listeners: {
								render: function(p){
									p.getForm().load({
										url: 'index.php?option=com_osemsc&controller=config'
										,params:{task:'getConfig',config_type:'thirdparty'}
									});
								}
							}
						})

						return fp;
					}
				}
			}()

			
			oseMsc.config.thirdPartyPanel = new Ext.Panel({
				title: Joomla.JText._('3rd_Party')
				,bodyStyle:'padding:10px'
				//,defaults:{bodyStyle:'padding:10px'}
				,items: oseMsc.config.thirdParty.pap.init()
			});
		}
}
	