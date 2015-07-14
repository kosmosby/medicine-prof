Ext.onReady(function(){
	oseMscs.msg = new Ext.App();
	oseMsc.msg = new Ext.App();
	oseMscs.propertyAccordian = new oseMscs.propertyAccordian();
	oseMscs.propertyAccordian = oseMscs.propertyAccordian.init();
	oseMscs.membershipSetting = new oseMscs.membershipSetting();
	oseMscs.membershipSetting = oseMscs.membershipSetting.init();
	
	oseMscs.propertyAccordian.add(oseMscs.mscSetting);
	oseMscs.propertyAccordian.add(oseMscs.mscBridges);
	oseMscs.propertyAccordian.add(oseMscs.contentSetting);
	oseMscs.propertyAccordian.doLayout();

	oseMscs.panel = new Ext.Panel({
		id: 'osemsc-memberships-panel'
	    ,border: false
	    ,layout:'border'
	    ,height: 600
	    ,items:[
	    	oseMscs.grid
	    	,oseMscs.propertyAccordian
	    ]
	    ,listeners:{
	    	render: function()	{
	    		oseMscs.mscSettingStore.load();
	    		oseMscs.mscBridgesStore.load();
	    		oseMscs.contentSettingStore.load();
	    	}
	    	,afterrender: function()	{
	    		oseMscs.propertyAccordian.getEl().mask(Joomla.JText._('PLEASE_SELECT_A_MEMBERSHIP_FIRST'));
	    	}
	    }
	});

	oseMscs.gridSm.on('rowselect' , function(sel,i,r)	{
		oseMscs.msc_id = r.id;
		oseMsc.msc_id = r.id;
	});
	oseMsc.addMsc = function(win,ctask,  catValue)	{
		Ext.Ajax.request({
			url: 'index.php?option=com_osemsc&controller=memberships',
		    params: {'task': ctask, msc_id: catValue},
		    success: function(response, opt) {
		    	var msg = Ext.decode(response.responseText);

		    	oseMsc.msg.setAlert(msg.title,msg.content);

				if(msg.success)	{
					win.hide();
					oseMscs.grid.getStore().reload();
					oseMscs.grid.getView().refresh();
				}
		    }
		})
	}
	oseMscs.grid.getTopToolbar().newBtn.on('click',function()	{
		oseMscs.newMscWin = new Ext.Window({
			title: Joomla.JText._('Create_a_new_membership')
			,width : 450
			,height: 200
			,bodyStyle:'padding:5px'
			,modal:true
			,closable: false
			,layout: 'fit'
			,border:false
		    ,items:[{
		    	xtype: 'form'
		    	,ref: 'form'
		    	,border: false
		    	,labelWidth: 150
		    	,items:[{
		    		fieldLabel: Joomla.JText._('Membership_Plan_Title')
		    		,xtype: 'textfield'
		    		,name: 'title'
		    	}]
		    	,buttons: [{
					text: Joomla.JText._('OK')
					,handler: function()	{
						oseMscs.newMscWin.form.getForm().submit({
							url: 'index.php?option=com_osemsc'
							,params:{controller:'memberships',task:'add'}
							,success: function(form,action)	{
								oseMsc.formSuccess(form,action);
								oseMscs.grid.getStore().reload();
								oseMscs.grid.getView().refresh();
								oseMscs.newMscWin.close();
							}
							,failure: function(form,action)	{
								oseMsc.formFailureMB(form,action);
							}
						})
					}
				},{
					text: Joomla.JText._('Cancel')
					,handler: function()	{
						oseMscs.newMscWin.close()
					}
				}]
		    }]

		});

		oseMscs.newMscWin.show().alignTo(Ext.getBody(),'c-c',[-50]);
	});

	oseMscs.grid.getTopToolbar().removeBtn.on('click',function()	{
		Ext.Msg.confirm(Joomla.JText._('Notice'),Joomla.JText._('Please_confirm_you_are_going_to_delete_this_membership_plan'),function(btn,txt){
			if(btn == 'yes')	{
				var sel = oseMscs.grid.getSelectionModel();
				var rows = sel.getSelections();

				var msc_ids = new Array();
				for(i=0; i<rows.length; i++)	{
					msc_ids[i] = rows[i].id;
				}
				sel.clearSelections();

				oseMscs.grid.getStore().remove(rows);

				Ext.Ajax.request({
					url: 'index.php?option=com_osemsc&controller=memberships',
				    params: {'task': 'remove', 'msc_ids[]': msc_ids},
				    success: function(response, opt) {
				    	oseMsc.ajaxSuccess(response, opt)
						sel.clearSelections();
						Ext.get('osemsc-property').mask(Joomla.JText._('Please_select_a_membership_first'));

						oseMscs.grid.getStore().reload();
						oseMscs.grid.getView().refresh();
				    }
				});
			}
		})
	});
	oseMscs.panel.render('com-content');
});