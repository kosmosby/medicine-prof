Ext.onReady(function(){
	Ext.Msg.alert('Status', 'You will be redirected to the confirmation shortly.');
	//window.location = redirectUrl;
	setTimeout("window.location = redirectUrl;",2000);
});