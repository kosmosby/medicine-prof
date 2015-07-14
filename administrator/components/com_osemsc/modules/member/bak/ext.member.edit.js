Ext.ns('oseMemMsc','oseMemMsc.add','oseMemMsc.edit');

oseMemMsc.msg = new Ext.App();

/* -- Store -- */
	oseMemMsc.edit.reader = new Ext.data.JsonReader({   
					              // we tell the datastore where to get his data from
	    root: 'result',
	    totalProperty: 'total',
	   
	    fields:[ 
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'username', type: 'string', mapping: 'username'},
		    {name: 'name', type: 'string', mapping: 'name'},
		    {name: 'email', type: 'string', mapping: 'email'}
	  	]
  	});

//-- Form
	oseMemMsc.edit.form = new Ext.FormPanel({
		id:'osemsc-member-formpanel',
		formId:'osemsc-member-form',
        frame:false,
        anchor: '90%',
        width: 250,
        layout:'form',
        bodyStyle:'padding:5px 10px 0px 5px',
        reader: oseMemMsc.edit.reader,
        autoHeight: true,
 		defaultType: 'textfield',
 		labelWidth: 150,
 		labelAlign: 'top',
 		border: false,
        items: [
    		{itemId:"uname",fieldLabel:'User Name',allowBlank:false, name:'username'},
        	{itemId:'name',fieldLabel:'Name',allowBlank:false, name:'name'},
        	{itemId:'email',fieldLabel:'Email',vtype:'email',allowBlank:false, name:'email'},
        	{itemId:'passwd',fieldLabel:'Password',allowBlank:true, name:'passwd',inputValue:'password'},
        	{itemId:'passwd2',fieldLabel:'Password Confirm',allowBlank:true, name:'passwd2',inputValue:'password'}
        	//{xtype: 'hidden',itemId:'id',name:'id'},
        ],
        
        buttons:[{
        	text: 'update',
        	handler: function(){
        		oseMemMsc.edit.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=members',
				    
				    params: {
				        task: 'action',action:'member.juser.save', 
				        member_id:oseMemMsc.member_id
				    },
				    
				    success: function(form, action) {
				    	var msg = action.result;
				    	oseMemMsc.msg.setAlert(msg.title,msg.content);
				    	
				    	if(msg.member_id != '')
				    	{
				    		oseMemMsc.member_id = msg.member_id;
				    	}
				    	oseMemsMsc.store.reload();
						oseMemsMsc.grid.getView().refresh();
				    },
				    failure: function(form, action) {
				    	
				    }
				});
        	}
        }]
        
        
    });

	
	oseMemMsc.edit.basicFieldset = new Ext.form.FieldSet({
		title: 'Joomla Registered User Information',
		collapsible: true,
		items:[oseMemMsc.edit.form],
		region:'west',
		listeners: {
        	show: function(){
        		oseMemMsc.edit.form.getForm().load({
        			url: 'index.php?option=com_osemsc&controller=members',
					params:{task:'action',action:'member.juser.getItem',member_id:oseMemMsc.member_id}
        		});
        	}
        }
	});
	
	oseMemMsc.edit.tabs = new Ext.TabPanel({
		anchor: '90%',
		plain:true,
        activeTab: 0,
        region: 'center',
        deferredRender: false,
        defaults:{bodyStyle:'padding:10px'}
	});

	oseMemMsc.edit.win = new Ext.Window({
		title: 'OSE Membership Control - Member Information - Edit Member Info.',
        //layout:'fit',
        layout:'border',
        width: 900,
        height:390,
        autoScroll: true,
        //autoheight:true,
        closeAction:'hide',
        plain: true,
        modal: true,
	
        items:[
        	oseMemMsc.edit.basicFieldset,
			oseMemMsc.edit.tabs
		]
        
    });

	
