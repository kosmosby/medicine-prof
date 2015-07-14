Ext.ns('oseMsc','oseMsc.login');

oseMsc.login.logout = function() {

}

oseMsc.login.logout.prototype = {
	init: function() {
		oseMsc.msg = new Ext.App();
		var logoutForm = new Ext.form.FormPanel({
			labelWidth: '150',

			border: false,
			items:[{
				xtype:'fieldset',
				title:Joomla.JText._('Logout'),
				anchor: '55%',
				items:[{
					xtype: 'button',
					text: Joomla.JText._('Log_Out'),
					handler: function(){
					logoutForm.getForm().submit({
						url: 'index.php?option=com_osemsc&controller=register',
						params:{task:'logout'},
						success: function(form,action){
							var msg = action.result;
							window.location = msg.returnUrl;
						},
						failure: function(form,action){
							var msg = action.result;
							oseMsc.msg.setAlert(msg.title,msg.content);
						}
					})
				}
				}]

			}]
		})
		return logoutForm;
 	}
}
 