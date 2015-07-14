Ext.ns('oseMsc','oseMsc.login');

oseMsc.login.login = function()	{

}

oseMsc.login.login.prototype = {
	init: function()	{
		var form = new Ext.form.FormPanel({
			labelWidth: '150',
			border: false,
			items:[{
				xtype:'fieldset',
				defaultType: 'textfield',
				defaults:{allowBlank: false,msgTarget: 'side'},
				items:[{
					fieldLabel:  Joomla.JText._('Username')
					,name: 'username'
				},{
					fieldLabel: Joomla.JText._('Password')
					,name: 'password'
					,inputType:'password'
					,listeners: {
						specialkey: function(field, e){

		                    if (e.getKey() == e.ENTER) {
		                       form.fireEvent('login')
		                    }
		                }
					}
				}]
			}]
			,listeners: {
				login: function(f)	{
					Ext.Msg.wait(Joomla.JText._('Loading'),Joomla.JText._('Please_Wait'));
					this.getForm().submit({
						url: 'index.php?option=com_osemsc&controller=register',
						//waitMsg: Joomla.JText._('Loading'),
						params:{task:'login',view:'register'},
	 					success: function(form,action){
	 						Ext.Msg.hide();
	 						var msg = action.result;
							//oseMsc.msg.setAlert(msg.title,msg.content);

							Ext.Msg.wait(Joomla.JText._('Redirecting_please_wait'),Joomla.JText._('Login_Successfully'));
							window.location = msg.returnUrl;
	 					},
	 					failure: function(form,action){
	 						Ext.Msg.hide();
	 						if (action.failureType === Ext.form.Action.CLIENT_INVALID){
	 							Ext.Msg.alert(Joomla.JText._('Notice'),Joomla.JText._('PLEASE_DOUBLE_CHECK_THE_LOGIN_FORM_AND_LOGIN_AGAIN'));
	 				        }

	 						if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
	 				           Ext.Msg.alert('Error',
	 				            'Status:'+action.response.status+': '+
	 				            action.response.statusText);

	 				        }

	 				        if (action.failureType === Ext.form.Action.SERVER_INVALID){
	 				            var msg = action.result;
	 				            //Ext.Msg.alert(msg.title,msg.content	,func);


	 							Ext.Msg.show({
	 								title: msg.title
	 								,msg: msg.content
	 								,buttons: Ext.Msg.OK
	 								//,fn:func
	 								,closable: false
	 							});

	 				        }
	 					}
					});
				}
			}
		});
		return form;
	}
}