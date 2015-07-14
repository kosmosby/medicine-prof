Ext.ns('oseMsc','oseMsc.config');

oseMsc.config.globalInit = function()	{

}

oseMsc.config.globalInit.prototype = {
		init: function()	{
			oseMsc.config.msg = new Ext.App();

			oseMsc.config.global = new Ext.form.FieldSet({
				//border: false,
				id:'ose-msc-config-global',
				title: Joomla.JText._('Global_Setting'),
				items:[{
					xtype:'compositefield',
					fieldLabel: Joomla.JText._('Msc_Extend_Mode'),
					hidden: true,
					items:[{
						hiddenName: 'msc_extend'
						,fieldLabel: Joomla.JText._('Msc_Extend_Mode')
						,xtype:'combo'
					    ,typeAhead: true
					    ,triggerAction: 'all'
					    ,lazyRender:true
					    ,mode: 'local'
					    ,store: new Ext.data.ArrayStore({
					        id: 0
					        ,fields: [
					            'myId'
					            ,'displayText'
					        ]
					        ,data: [
					        	[0, Joomla.JText._('Default')]
					        	//,['lic','License']
					        ]
					    })
					    ,valueField: 'myId'
					    ,displayField: 'displayText'
						,listeners:{
							render: function(c){
								c.setValue('0');
							}
						}
					},{
						xtype:'checkbox',
						id: 'isMscCheckbox',
						name:'is_msc_mode_customized',
						inputValue: '1',
						listeners: {
							check:function(c,checked)	{
								c.previousSibling().setDisabled(checked);
								c.nextSibling().setDisabled(!checked);
							}
						}
					},{
						xtype:'textfield',
						disabled: true,
						name:'customized_msc_mode'
					}]

				},{
					xtype:'compositefield',
					fieldLabel: Joomla.JText._('Member_Mode'),
					hidden: true,
					items:[{

						hiddenName: 'member_mode',
						xtype:'combo',
					    typeAhead: true,
					    triggerAction: 'all',
					    lazyRender:true,
					    mode: 'local',
					    store: new Ext.data.ArrayStore({
					        id: 0,
					        fields: [
					            'myId',
					            'displayText'
					        ],
					        data: [
					        	[0, Joomla.JText._('Default')]
					        ]
					    }),
					    valueField: 'myId',
					    displayField: 'displayText',
						listeners:{
							render: function(c){
								c.setValue('0');
							}
						}
					},{
						xtype:'checkbox',
						id: 'isMemberCheckbox',
						name:'is_member_mode_customized',
						inputValue: '1',
						listeners: {
							check:function(c,checked)	{
								c.previousSibling().setDisabled(checked);
								c.nextSibling().setDisabled(!checked);
							}
						}
					},{
						xtype:'textfield',
						disabled: true,
						name:'customized_member_mode'
					}]

				},{
					xtype: 'radiogroup'
					,name: 'payment_mode'
					,fieldLabel: Joomla.JText._('Membership_Renewal_Options')
					,anchor: '100%'
					,defaults:{xtype:'radio',name:'payment_mode'}
					,items:[
						{boxLabel: Joomla.JText._('Manual_Renewing_Only'), inputValue: 'm'}
						,{boxLabel:Joomla.JText._('Automatic_Renewing_Only'), inputValue: 'a'}
						,{boxLabel: Joomla.JText._('Both'), inputValue: 'b', checked: true}
					]
				},{
					xtype: 'radiogroup',
					name: 'manual_renew_mode',
					fieldLabel: Joomla.JText._('Manual_Renewing_Mode'),
					anchor: '100%',
					defaults:{xtype:'radio',name:'manual_renew_mode'},
					items:[
						{boxLabel: Joomla.JText._('Renew_Mode_Do_not_add_remaining_days_to_user_s_membership'), inputValue: 'renew', checked:true},
						{boxLabel: Joomla.JText._('Extend_Mode_Add_remaining_days_to_user_s_membership'), inputValue: 'extend'}
					]
				},{
					xtype: 'radiogroup',
					name: 'manual_to_automatic_mode',
					fieldLabel: Joomla.JText._('Manual_to_Automatic_Renewing_Mode'),
					anchor: '100%',
					defaults:{xtype:'radio',name:'manual_to_automatic_mode'},
					items:[
						{boxLabel: Joomla.JText._('Renew_Mode_Do_not_add_remaining_days_to_user_s_membership'), inputValue: 'renew', checked:true},
						{boxLabel: Joomla.JText._('Extend_Mode_Add_remaining_days_to_user_s_membership'), inputValue: 'extend'}
					]
				},{
			            fieldLabel: Joomla.JText._('Date_Format')
			            ,xtype: 'compositefield'
			            ,style: 'padding-left: 0px'
			            ,items:[{
							name: 'DateFormat'
						  	,xtype: 'textfield'
						  	,emptyText: 'Y-m-d H:i:s'	
			            },{
		            		xtype: 'box'
		            		,autoEl: {
		            			tag: 'div'
		            			,id: 'trackCallout'
		            			,style:"width: 100px; padding: 3px 0; border:0px dotted #99bbe8; color: #666; cursor: pointer; font:bold 11px tahoma,arial,sans-serif; float:left; text-decoration:underline;"
		            			,html:  '<a href="http://php.net/manual/en/function.date.php"  target="_blank"> LINK </a>'
		            		},listeners:{
		            			render: function(b)	{
		            				Ext.QuickTips.register({
									    target: b.getEl(),
									    anchor: 'right',
									    text: Joomla.JText._('Example_If_you_want_to_set_the_date_format_show_as_2011_12_31_you_should_input_Y_m_d_here_Click_the_link_to_see_more_detail_about_the_parameters'),
									    width: 250
									});
		            			}
		            		}
		            	}]
			        }]
			});

			oseMsc.config.gmap = new Ext.form.FieldSet({
				//border: false,
				title: Joomla.JText._('Google_Map'),
				items:[{
					fieldLabel: Joomla.JText._('key'),
					xtype: 'textarea',
					width: 500,
					height: 50,
					name:'gmap_key'
				}]
			});

			oseMsc.config.style = new Ext.form.FieldSet({
				//border: false,
				title: 'Style',
				items: [
				{
						html:Joomla.JText._('Please_see_this_wiki_if_you_need_to_customize_the_frontend_registration_form')+' <a href="http://wiki.opensource-excellence.com/index.php?title=How_to_customize_the_Registration_Form_layout%3F" target="_blank"> WIKI 1</a>, <a href="http://wiki.opensource-excellence.com/index.php?title=How_to_edit_css%3F" target="_blank"> WIKI 2</a>, <a href="http://wiki.opensource-excellence.com/index.php?title=How_to_remove_the_footer_%22Powered_By_OSE%22%3F" target="_blank"> WIKI 3</a> '
						,border: false
						,bodyStyle: 'text-align: left; padding: 5px; '
				},
				{
					hiddenName: 'frontend_style'
						,fieldLabel: Joomla.JText._('Frontend_Style')
						,xtype:'combo'
					    ,typeAhead: true
					    ,triggerAction: 'all'
					    ,lazyRender:true
					    ,mode: 'local'
					    ,width: 300	
					    ,store: new Ext.data.ArrayStore({
					        id: 0
					        ,fields: [
					            'myId'
					            ,'displayText'
					        ]
					        ,data: [
					        	['msc6_default', Joomla.JText._('Default')]
					        	,['s5_cleanout_org', Joomla.JText._('S5_Cleanout_Org')]
					        	,['msc5', Joomla.JText._('V5_Blue_Style')]
					        	,['msc5_red', Joomla.JText._('V5_Red_Style')]
					        	,['msc5_light',Joomla.JText._('V5_Light_style')]
					        	,['msc5_rtl',Joomla.JText._('V5_Right_to_Left_style')]
					        	,['msc5_green', Joomla.JText._('V5_Green_Style')]
					        	,['custom',Joomla.JText._('Customized_style')]
					        ]
					    })
					    ,valueField: 'myId'
					    ,displayField: 'displayText'
						,listeners:{
							render: function(c){
								c.setValue('msc5');
							}
						}
				},
				{
					hiddenName: 'backend_style'
						,fieldLabel: Joomla.JText._('Backend_Style')
						,xtype:'combo'
					    ,typeAhead: true
					    ,triggerAction: 'all'
					    ,lazyRender:true
					    ,mode: 'local'
					    ,width: 300
					    ,store: new Ext.data.ArrayStore({
					        id: 0
					        ,fields: [
					            'myId'
					            ,'displayText'
					        ]
					        ,data: [
					        	['msc5', Joomla.JText._('Default')]
					        	,['custom',Joomla.JText._('Customized_style')]
					        ]
					    })
					    ,valueField: 'myId'
					    ,displayField: 'displayText'
						,listeners:{
							render: function(c){
								c.setValue('msc5');
							}
						}
				},
				{
					hiddenName: 'show_poweredby'
						,fieldLabel: Joomla.JText._('Support_OSE_by_Showing_the_PoweredBy_message')
						,xtype:'combo'
					    ,typeAhead: true
					    ,triggerAction: 'all'
					    ,lazyRender:true
					    ,mode: 'local'
					    ,store: new Ext.data.ArrayStore({
					        id: 0
					        ,fields: [
					            'myId'
					            ,'displayText'
					        ]
					        ,data: [
					        	['1', Joomla.JText._('ose_Yes')]
					        	,['0',Joomla.JText._('ose_No')]
					        ]
					    })
					    ,valueField: 'myId'
					    ,displayField: 'displayText'
						,listeners:{
							render: function(c){
								c.setValue('1');
							}
						}
				}
				]
			});

			oseMsc.config.globalReader = new Ext.data.JsonReader({
			    root: 'result',
			    totalProperty: 'total',
			    fields:[
				    {name: 'id', type: 'int', mapping: 'id'},
				    {name: 'msc_extend', type: 'string', mapping: 'msc_extend'},
				    {name: 'is_msc_mode_customized', type: 'string', mapping: 'is_msc_mode_customized'},
				    {name: 'customized_msc_mode', type: 'string', mapping: 'customized_msc_mode'},
				    {name: 'member_mode', type: 'string', mapping: 'member_mode'},
				    {name: 'is_member_mode_customized', type: 'string', mapping: 'is_member_mode_customized'},
				    {name: 'customized_member_mode', type: 'string', mapping: 'customized_member_mode'},
				    {name: 'manual_renew_mode', type: 'string', mapping: 'manual_renew_mode'},
				    {name: 'manual_to_automatic_mode', type: 'string', mapping: 'manual_to_automatic_mode'},
				    {name: 'payment_mode', type: 'string', mapping: 'payment_mode'},
				    {name: 'gmap_key', type: 'string', mapping: 'gmap_key'},
				    {name: 'backend_style', type: 'string', mapping: 'backend_style'},
				    {name: 'frontend_style', type: 'string', mapping: 'frontend_style'},
				    {name: 'show_poweredby', type: 'string', mapping: 'show_poweredby'},
				    {name: 'DateFormat', type: 'string', mapping: 'DateFormat'}
			  	]
		  	}),

			oseMsc.config.globalForm = new Ext.form.FormPanel({
				title:Joomla.JText._('Global_Parameter'),
				border: false,
				labelWidth: 250,
				autoHeight: true,
				bodyStyle:'padding:10px 10px 0',
				items:[
					oseMsc.config.global,
					oseMsc.config.gmap,
					oseMsc.config.style
				],

				reader:	oseMsc.config.globalReader,

				buttons:[{
					text:Joomla.JText._('save'),
					handler: function()	{
						oseMsc.config.globalForm.getForm().submit({
							clientValidation: true,
							url: 'index.php?option=com_osemsc&controller=config',
							params:{task:'save',config_type:'global'},
							success: function(form,action){
								var msg = action.result;
								oseMsc.config.msg.setAlert(msg.title,msg.content);
							}
						})
					}
				}],

				listeners: {
					render: function(p){
						Ext.Msg.wait(Joomla.JText._('Loading'),Joomla.JText._('Please_wait'));
						p.getForm().load({
							url: 'index.php?option=com_osemsc&controller=config'
							,params:{task:'getConfig',config_type:'global'}
							,success: function(form,action)	{
								Ext.Msg.hide()
							}
							,failure:function(form,action){
								Ext.Msg.hide()
							}
							//,waitMsg: 'Loading...'
						});
						
					}
				}
			});
		}
}
	