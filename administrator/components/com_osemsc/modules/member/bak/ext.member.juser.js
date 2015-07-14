Ext.ns('oseMemMsc');

	oseMemMsc.juserReader = new Ext.data.JsonReader({
	    root: 'result',
	    totalProperty: 'total',

	    fields:[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'username', type: 'string', mapping: 'username'},
		    {name: 'name', type: 'string', mapping: 'name'},
		    {name: 'email', type: 'string', mapping: 'email'}
	  	]
  	});

	oseMemMsc.juser = new Ext.FormPanel({
        frame:false
        ,width: 500
        ,height: 260
        ,bodyStyle:'padding:10px'
        ,reader: oseMemMsc.juserReader
 		,defaultType: 'textfield'
 		,labelWidth: 150
 		,labelAlign: 'left'
 		,border: false
        ,items: [
    		{itemId:"uname",fieldLabel:'User Name',allowBlank:false, name:'username'},
        	{itemId:'name',fieldLabel:'Name',allowBlank:false, name:'name'},
        	{itemId:'email',fieldLabel:'Email',vtype:'email',allowBlank:false, name:'email'},
        	{itemId:'passwd',fieldLabel:'Password',allowBlank:true, name:'passwd',inputValue:'password'},
        	{itemId:'passwd2',fieldLabel:'Password Confirm',allowBlank:true, name:'passwd2',inputValue:'password'}
        	//{xtype: 'hidden',itemId:'id',name:'id'},
        ]

        ,buttons:[{
        	text: 'update',
        	handler: function(){
        		oseMemMsc.juser.getForm().submit({
				    clientValidation: true
				    ,url: 'index.php?option=com_osemsc&controller=members'

				    ,params: {
				        task: 'action',action:'member.juser.save',
				        member_id: oseMemsMsc.member_id
				    }

				    ,success: function(form, action) {
				    	oseMsc.formSuccess(form, action);

				    	oseMemsMsc.store.reload();
						oseMemsMsc.grid.getView().refresh();
				    }
				    ,failure: function(form, action) {
				    	oseMsc.formFailure(form, action);
				    }
				})
        	}
        }]

        ,listeners: {
        	render: function(c)	{
        		c.getForm().load({
        			url: 'index.php?option=com_osemsc&controller=members',
					params:{task:'action',action:'member.juser.getItem',member_id:oseMemsMsc.member_id}
        		});
        	}
        }
    })
