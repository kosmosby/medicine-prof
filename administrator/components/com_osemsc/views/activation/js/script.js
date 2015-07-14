function ajaxAction(option, task, controller, username, password, ext)
  	{
	// Ajax post scanning request;
	Ext.Ajax.request({
				url : 'index.php' ,
				params : {
					option : option,
					task:task,
					controller:controller,
					view:'activation',
					username:username,
					password:password,
					ext:ext
				},
				method: 'POST',
				success: function ( result, request ) {
					msg = Ext.decode(result.responseText);
					if (msg.status!='ERROR')
					{
						Ext.Msg.alert(msg.status, msg.result,function(btn,txt){
							if(btn == 'ok')	{
									window.location='index.php?option=com_osemsc&view=memberships';
								}
						});
					}
					else
					{
						Ext.Msg.alert(msg.status, msg.result,function(btn,txt){
							if (msg.buy==1)
							{
								if(btn == 'ok')	{
									window.location='http://www.opensource-excellence.com/shop.html';
								}
							}	
						});
						
					}
			}
	});
}

Ext.onReady(function(){
	var activate = Ext.get('activate');
	activate.on('click', function(){
		var username = Ext.get('username'); 
		if (username.dom.value =='')
		{
			alert("Username cannot be empty"); 
			return false; 
		}
		var password = Ext.get('password'); 
		if (password.dom.value =='')
		{
			alert("Password cannot be empty"); 
			return false; 
		}
		ajaxAction('com_osemsc', 'activate', 'activate', username.dom.value, password.dom.value,'msc');
	});
});	
