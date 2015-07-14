Ext.onReady(function(){
	Ext.Msg.alert(Joomla.JText._('Thank_You'), Joomla.JText._('You_will_be_redirected_to_the_confirmation_shortly'));
	//window.location = redirectUrl;
	setTimeout("window.location = redirectUrl;",2000);
});