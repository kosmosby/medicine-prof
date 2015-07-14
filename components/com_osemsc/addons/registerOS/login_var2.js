Ext.ns('oseMscAddon');

	oseMscAddon.login = function(mf){
		this.loginform = function()	{
			var form = new Ext.FormPanel({
				items:[{
					fieldLabel: Joomla.JText._('Username')
					,itemId:'username'
					,xtype: 'textfield'
					,vtype: 'alphanum'
					,name: 'username'
					,allowBlank: false
				},{
					fieldLabel: Joomla.JText._('Password')
					,itemId:'password'
					,xtype: 'textfield'
					,inputType: 'password'
					,vtype: 'alphanum'
					,name: 'password'
					,allowBlank: false
					,listeners: {
						specialkey: function(field, e){
		                    if (e.getKey() == e.ENTER) {
		                    	form.buttons[0].fireEvent('click');
		                    }
		                }
					}
				}]
				,buttons:[{
					text: Joomla.JText._('Login')
					,itemId: 'login'
					,listeners: {
						click: function()	{
							//var post = true;
							//var username = fs.getComponent('username').getValue();
							//var password = fs.getComponent('password').getValue();
							//Ext.Msg.wait('Loading...','Wait');
							form.getForm().submit({
								url: 'index.php?option=com_osemsc'
								,waitMsg: 'Loading...'
								,params:{controller:'register', view:'register', task: 'login'}
								,success: function()	{
									Ext.Msg.wait('Reloading...','Wait');
									oseMsc.reload()
								}
								,failure: oseMsc.formFailureMB
							})
							
						}
					}
				}]
				,defaults: {width: 200, msgTarget: 'side'}
				,labelWidth: 150
				,height: 150
			});
			
			return form;
		}
	}

	oseMscAddon.login.prototype = {
		init: function()	{
			var fs = new Ext.Panel({
				border: false
				//,defaults: {width: 200, msgTarget: 'side'}
				,items:[{
					xtype: 'compositefield'
					,items:[{
						xtype: 'box'
						,html: 'Already a member? Login here!'
					},{
						xtype: 'button'
						,text: 'Login'
						,handler: function()	{
							var lf = this.loginform();
							new Ext.Window({
								title: 'Login'
								,width: 500
								,autoHeight: true
								,items:[lf]
								,modal: true
							}).show().alignTo(Ext.getBody(),'b-c');
						}
						,scope: this
					}]
				}]
				,height: 100
				,bodyStyle: 'padding-top:50px;padding-left:30px'
			});
			return fs;
		}
	}