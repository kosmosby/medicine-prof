Ext.onReady(function(){
	var deviceRegButton = Ext.get('deviceRegButton');
	var regCode = Ext.get('regCode');
	hidemessages()

	deviceRegButton.on('click', function(){
	    hidemessages();
		if (regCode.dom.value =='')
		{
			Ext.get('warnmessage').show();
			Ext.get('warnmessage').update('Please enter your registration code.');
			return false;
		}
		Ext.Ajax.request({
				url : 'index.php' ,
				params : {
					option : 'com_osemsc',
					task:'registerCode',
					controller:'addons',
					regCode: regCode.dom.value
				},
				method: 'POST',
				success: function ( result, request ) {
					msg = Ext.decode(result.responseText);
					if (msg.results=='SUCCESS')
					{
					        Ext.get('tips').show();
							Ext.get('tips').update(msg.text);
							Ext.get('loadingindicator').hide();
					}
					else
					{
							Ext.get('warnmessage').show();
							Ext.get('warnmessage').update(msg.text);
							Ext.get('loadingindicator').hide();
					}
				}
			});

	 });

	 function hidemessages()
	 {
	 Ext.get('warnmessage').update('');
	 Ext.get('warnmessage').hide();
	 Ext.get('tips').update('');
	 Ext.get('tips').hide();
	 }
});
