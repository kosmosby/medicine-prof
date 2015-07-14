Ext.ns('oseMsc','oseMsc.reg');	

	oseMsc.reg.loginFieldset = new Ext.form.FieldSet({
 		title: 'Login',
 		defaultType: 'textfield',
 		//collapsible: true,
 		//collapsed: true,
 		anchor: '60%',
 		defaults: {msgTarget : 'side',},
 		items:[{
            fieldLabel: 'User Name',
            name: 'uname',
            allowBlank:false,
        },{
        	itemId: 'pwd',
            fieldLabel: 'Password',
            name: 'passwd',
            inputType: 'password',
            allowBlank:false,
        },{
        	fieldLabel: 'Remember Me?',
        	xtype: 'checkbox',
        	value: 1,
        }],
        
        buttons: [{
            text: 'Save'
        },{
            text: 'Cancel'
        }]
 	});  
 	
 	