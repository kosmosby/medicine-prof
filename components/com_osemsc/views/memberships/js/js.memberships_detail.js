Ext.onReady(function(){

	var mscSelectPaymentMode = function(msc_id,msc_option)	{

		Ext.get('osemsc-list').mask('Loading...');

		Ext.Ajax.request({
			clientValidation: true,
			url: 'index.php?option=com_osemsc&controller=register',
			params:{task: 'subscribe', 'msc_id': msc_id, 'msc_option': msc_option},
			success: function(response,opt){
				var msg = Ext.decode(response.responseText);
				if(msg.success)	{
					Ext.get('osemsc-list').unmask();
					Ext.Msg.wait('Please Wait...', 'Redirecting');
					window.location = msg.link;
				}	else	{
					Ext.get('osemsc-list').unmask();
				}

			}
		})
	}

	Ext.select('.msc-intro-box').setVisibilityMode(2);
	Ext.select('.msc-intro-box').setVisible(false);


	Ext.select('.msc-intro-box .show-detail').on('click',function(e,t,o){
		//alert(Ext.get(t).toSource())
		var intro = Ext.get(t).parent('.msc-intro-box');
		//alert(intro.toSource())
		intro.setVisible(false)
		intro.next().setVisible(true)
	});

	Ext.select('.msc-desc-box .show-detail').hide();

	Ext.select('.msc-button-select-m').on('click',function(e,t,o){
		var msc_id = t.id.replace('msc-button-select-m-','')
		var msc_option = Ext.get(t.id).findParent('.msc-first',50,true).child('.msc_options').getValue();
		mscSelectPaymentMode(msc_id,msc_option);
	});

	Ext.select('.msc-button-select-a').on('click',function(e,t,o){
		var msc_id = t.id.replace('msc-button-select-a-','')
		var msc_option = Ext.get(t.id).findParent('.msc-first',50,true).child('.msc_options').getValue();
		mscSelectPaymentMode(msc_id,msc_option);

	});
});

var showDetail = function(el,isFull)	{
	alert(Ext.get(el).dom);
}