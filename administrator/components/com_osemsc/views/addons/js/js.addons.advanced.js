Ext.onReady(function(){
	Ext.QuickTips.init();
	
	var Msg = new Ext.App();
	
	
	oseMsc.addon.panel = new Ext.Panel({
		border: false
		,width: Ext.get('ose-addon-list').getWidth() - 15
		,items: [oseMsc.addon.grid]
		,renderTo: 'ose-addon-list'
	})
	
	//oseMsc.addon.grid.render('com-content');
});