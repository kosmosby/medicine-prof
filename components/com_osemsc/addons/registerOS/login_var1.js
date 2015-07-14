Ext.ns('oseMscAddon');

	oseMscAddon.login = function(){
		function click()	{

		}
	}

	oseMscAddon.login.prototype = {
		init: function()	{
			var fs = new Ext.form.FieldSet({
				title: Joomla.JText._('Existing_User_Please_Login')
				,labelWidth: 130
				,defaults: {width: 200, msgTarget: 'side'}
				,items:[{
					fieldLabel: 'Login email'
					,itemId:'username'
					,xtype: 'textfield'
					,vtype: 'email'
					,submitValue: false
					,name: 'login_username'
					//,allowBlank: false
				},{
					fieldLabel: 'Password'
					,itemId:'password'
					,xtype: 'textfield'
					,inputType: 'password'
					,vtype: 'alphanum'
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
				,buttons:[{
					text: Joomla.JText._('Login')
					,itemId: 'login'
					,listeners: {
						click: function()	{
							var post = true;
							Ext.each(fs.findByType('textfield'),function(item,i,all){
								if(!item.isDirty() || !item.isValid())	{
									post = false;
									item.markInvalid('This field is required');
								}
							})

							if(post)	{
								var username = fs.getComponent('username').getValue();
								var password = fs.getComponent('password').getValue();
								Ext.Msg.wait('Loading...','Wait');
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
				}]
			})

			return fs;
		}
	}