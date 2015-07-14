Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();
	
	oseMscAddon.access = new Ext.form.FormPanel({
		title:'Access Level',
		bodyStyle:'padding:10px 10px 0',
		//defaults: {bodyStyle:'padding:10px 10px 0',},
		autoHeight:true,
		items:[{
			xtype:'fieldset',
			title:' Backend Access Level',
			items:[{
				border:false,
				autoLoad:'index.php?option=com_osemsc&controller=config&task=getGroupList',
				
			}],
		}],
		
		buttons:[{
			text: 'Save',
			handler: function()	{
				oseMscAddon.access.getForm().submit({
					url:'index.php?option=com_osemsc&controller=config',
					params: {task: 'save',config_type:'access'},
					success: function(form,action){
						var msg = action.result;
						oseMsc.config.msg.setAlert(msg.title,msg.content);
					},
					failure: function(form,action){
						var msg = action.result;
						oseMsc.config.msg.setAlert(msg.title,msg.content);
					}
				});
			}
		}],
	});