Ext.ns('oseMsc','oseMsc.addon');

function updateAddon(addonname)
{
	if (Ext.get(addonname).dom.value=='')
	{
	return false;
	}
	Ext.get('ose-addon-list').mask();
	Ext.Ajax.request({
		url : 'index.php' ,
				params : {
				option : 'com_osemsc',
				task:'updateAddon',
				controller:'addons',
				addonname: addonname,
				status: Ext.get(addonname).dom.value
				},
				method: 'POST',
				success: function ( result, request ) {
				var msg = Ext.decode(result.responseText);
				if (msg.status=='Done')
				{
					Ext.get('ose-addon-list').unmask();
					Ext.Msg.alert(msg.status,
						msg.result,
						function (){
						window.location= "index.php?option=com_osemsc&view=addons";
						}
					);

				}else
				{
					Ext.get('ose-addon-list').unmask();
						Ext.Msg.alert(
							msg.status,
							msg.result,
							function (){
							window.location= "index.php?option=com_osemsc&view=addons";
							}
						);
				}

				}
	});
}
Ext.onReady(function(){
});