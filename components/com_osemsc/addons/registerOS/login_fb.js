Ext.ns('oseMscAddon');
	oseMscAddon.login_fb = function(){
		function click()	{
		}
	}
	oseMscAddon.login_fb.prototype = {
		init: function()	{
			var fs = new Ext.form.FieldSet({
				title: Joomla.JText._('Existing_User_Please_Login')
				,labelWidth: 130
				,defaults: {width: 200, msgTarget: 'side'}
				,items:[{
					fieldLabel: Joomla.JText._('Username')
					,itemId:'username'
					,xtype: 'textfield'
					,vtype: 'noSpace'
					,submitValue: false
					,name: 'login_username'
				},{
					fieldLabel: Joomla.JText._('Password')
					,itemId:'password'
					,xtype: 'textfield'
					,inputType: 'password'
					,vtype: 'noSpace'
					,submitValue: false
					,name: 'login_password'
					,listeners: {
						specialkey: function(field, e){
		                    if (e.getKey() == e.ENTER) {
		                    	fs.getFooterToolbar().getComponent('login').fireEvent('click');
		                    }
		                }
					}
				}]
				,buttons:[
				   {
					text: Joomla.JText._('Login')
					,itemId: 'login'
					,listeners: {
						click: function()	{
							var post = true;
							Ext.each(fs.findByType('textfield'),function(item,i,all){
								if(!item.isDirty() || !item.isValid())	{
									post = false;
									item.markInvalid(Joomla.JText._('This_field_is_required'));
								}
							})

							if(post)	{
								var username = fs.getComponent('username').getValue();
								var password = fs.getComponent('password').getValue();
								Ext.Msg.wait(Joomla.JText._('Loading'),Joomla.JText._('Please_Wait'));
								Ext.Ajax.request({
									url: 'index.php?option=com_osemsc'
									,params:{controller:'register', view:'register', task: 'login', username: username, password: password}
									,callback: function(el,success,response,opt)	{
										Ext.Msg.hide();
										var msg = Ext.decode(response.responseText);
										if(msg.success)	{
											oseMsc.reload()
										}	else	{
											Ext.Msg.alert(msg.title,msg.content)
										}
									}
								})
							}
						}
					}
				},
				{
					text: Joomla.JText._('FB_Login')
					name: 'login_FB',
					cls : 'x-btn-icon',
		            iconCls: 'fbButton',
		            minWidth : 95
		            ,listeners: {
						click: function()	{
							FB.login(function(response) {
								if (response.status=='connected')
								{	
									var username = 'Facebook';
									var password = 'Facebook';
									Ext.Msg.wait(Joomla.JText._('Loading'),Joomla.JText._('Please_Wait'));
									Ext.Ajax.request({
										url: 'index.php?option=com_osemsc'
										,params:{controller:'register', view:'register', task: 'login', username: username, password: password}
										,callback: function(el,success,response,opt)	{
											Ext.Msg.hide();
											var msg = Ext.decode(response.responseText);
											if(msg.success)	{
												oseMsc.reload()
											}	else	{
												Ext.Msg.alert(msg.title,msg.content);
											}
										}
									})
								}
								else
								{
									Ext.Msg.alert('Error','Cannot connect to Facebook')
								}	
							}, {scope: 'email'});	
						}
		            }
				}]
			})

			return fs;
		}
	}