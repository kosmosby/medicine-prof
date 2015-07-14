Ext.ns('oseMsc','oseMsc.config');
	oseMsc.config.msg = new Ext.App();

	oseMsc.config.member = new Ext.form.FieldSet({
		//border: false,
		title: 'Member related parameters',
		items:[{
			fieldLabel: 'Auto Login After Registration',
			xtype: 'radiogroup',
			name:'auto_login',
			defaults: {xtype: 'radio', name:'auto_login'},
			items:[
				{boxLabel: 'Yes',inputValue: '1', checked: true},
				{boxLabel: 'No',inputValue: '0'}
			]
		}]
	});

	oseMsc.config.memberReader = new Ext.data.JsonReader({
	    root: 'result',
	    totalProperty: 'total',
	    fields:[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'auto_login', type: 'string', mapping: 'auto_login'}
	  	]
  	}),

	oseMsc.config.memberForm = new Ext.form.FormPanel({
		title:'Member',
		border: false,
		labelWidth: 150,
		autoHeight: true,
		bodyStyle:'padding:10px 10px 0',
		items:[
			oseMsc.config.member
		],

		reader:	oseMsc.config.memberReader,

		buttons:[{
			text:'save',
			handler: function()	{
				oseMsc.config.memberForm.getForm().submit({
					clientValidation: true,
					url: 'index.php?option=com_osemsc&controller=config',
					params:{task:'save',config_type:'member'},
					success: function(form,action){
						var msg = action.result;
						oseMsc.config.msg.setAlert(msg.title,msg.content);
					}
				})
			}
		}],

		listeners: {
			render: function(p){
				p.getForm().load({
					url: 'index.php?option=com_osemsc&controller=config',
					params:{task:'getConfig',config_type:'member'}
				});
			}
		}
	});