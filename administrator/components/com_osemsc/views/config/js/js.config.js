Ext.ns('oseMsc','oseMsc.config');
Ext.onReady(function(){
	Ext.QuickTips.init();

	oseMsc.config.tabs = new Ext.TabPanel({
		title: 'Configurations'
		,activeItem: 0
		,renderTo: 'osemsc-config'
		,border:false
		,width: Ext.get('osemsc-config').getWidth() - 15
		,items:[
			oseMsc.config.globalForm
			,oseMsc.config.paymentForm
			,oseMsc.config.emailForm
			,oseMsc.config.regForm
			,oseMsc.config.thirdPartyPanel
		]
});

});