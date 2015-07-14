Ext.onReady(function(){
	var Msg = new Ext.App();
	oseMsc.add.add.fieldset.getComponent('add').on('click',function(){
		oseMsc.add.add.getForm().submit({
		    //clientValidation: true,
		    url: 'index.php?option=com_osemsc&controller=memberships',
		    params: {
		        task: 'add'
		    },
		    success: function(form, action) {
		    	//alert(form);
		    	var msg = action.result;
		    	Msg.setAlert(msg.title,msg.content);
	    		oseMsc.add.win.hide();
	    		oseMscs.grid.getStore().reload();
	    		oseMscs.grid.getView().refresh();
	    		oseMsc.msc_id = msg.id;
	    		oseMscs.tbar.editBtn.fireEvent('click');
		    },
		    failure: function(form, action) {
		    	var msg = action.result;
		    	if(msg.title == 'Error')
		    	{
		    		Msg.setAlert(msg.title,msg.content);
		    	}
		    	else
		    	{
		    		Msg.setAlert('Error','Can not connect the server!');
		    	}
		    }
		});
	});
	
	/*
	 *  Add -> Extend -> Extend Button 
	 */
	oseMsc.add.extend.fieldset.getComponent('extend').on('click',function(){
		oseMsc.add.extend.getForm().submit({
		    //clientValidation: true,
		    url: 'index.php?option=com_osemsc&controller=memberships',
		    params: {
		        task: 'extend'
		    },
		    success: function(form, action) {
		    	var msg = action.result;
		    	Msg.setAlert(msg.title,msg.content);
		    	oseMsc.add.win.hide();
	    		oseMscs.grid.getStore().reload();
	    		oseMscs.grid.getView().refresh();
	    		oseMsc.msc_id = msg.id;
	    		oseMscs.tbar.editBtn.fireEvent('click');
		    },
		    failure: function(form, action) {
		    }
		});
	});
	oseMscs.panel.render('com-content');
	oseMscs.grid.getSelectionModel().on('selectionchange',function(sm,node){
		oseMscs.tbar.removeBtn.setDisabled(sm.getCount() < 1); // >
		oseMscs.tbar.editBtn.setDisabled(sm.getCount() != 1); // >
		oseMsc.property.getTopToolbar().saveBtn.setDisabled(sm.getCount() < 1); // >
		oseMsc.property.getComponent('ordering').setDisabled(sm.getCount() < 1); // >
	});
	oseMscs.grid.getSelectionModel().on('rowselect',function(e,i,r){
		oseMsc.property.getComponent('msc-id').setValue(r.id);
		oseMsc.property.getComponent('ordering').fireEvent('expand');
		oseMsc.property.getForm().load({
			url: 'index.php?option=com_osemsc',
        	params:{controller:'membership', task:'getProperty',msc_id:r.id}
		});
		oseMsc.msc_id = r.id;
	});
	
	oseMscs.tbar.addBtn.on('click',function(){
		oseMsc.add.mscCombo.getStore().reload();
        oseMsc.add.win.show(this);
	});
	oseMscs.tbar.removeBtn.on('click',function(){
		Ext.Msg.confirm('Notice','Are You Sure to Remove?',function(btn, text){
			if(btn == 'yes')	{
				var r = oseMscs.grid.getSelectionModel().getSelected();
		    	Ext.Ajax.request({
					url : 'index.php?option=com_osemsc&controller=memberships&task=remove',
					params:{msc_id:r.id},
					success: function(response, opts){
						var msg = Ext.decode(response.responseText);
						oseMscs.msg.setAlert(msg.title,msg.content);
						if(msg.title == 'Done')
						{
							oseMscs.grid.getSelectionModel().clearSelections();
							oseMscs.grid.getStore().reload();
							oseMscs.grid.getView().refresh();
						}
					},
					failure: function(){
					}
				});
			}
       });
	});
});