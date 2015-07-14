Ext.onReady(function(){
	
	osemscEmails.Init = new osemscEmails.Init();
	osemscEmails.Init = osemscEmails.Init.init();
	
	Ext.get('email-var-tips').setDisplayed(false);
	Ext.get('receipt-var-tips').setDisplayed(false);

	Ext.get('email-var-tips').load({
		url:'index.php?option=com_osemsc&controller=emails',
		params:{task:'getEmailTips'}
	});

	osemscEmails.grid.getSelectionModel().on('selectionchange', function(sm){
		osemscEmails.tbar.removeBtn.setDisabled(sm.getCount() < 1); // >
		osemscEmails.tbar.editBtn.setDisabled(sm.getCount() != 1); // >
	});

	osemscEmails.panel = new Ext.Panel({
		border: false
		,width: Ext.get('ose-email-list').getWidth() - 15
		,items: [osemscEmails.grid]
		,renderTo: 'ose-email-list'
	})
});