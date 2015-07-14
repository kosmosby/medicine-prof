Ext.ns('oseMemMsc');
	oseMemMsc.msg = new Ext.App();
	oseMemMsc.gridExpander = new Ext.ux.grid.RowExpander({
	    tpl : new Ext.Template(
	        '<p><b>Company:</b> </p><br>',
	        '<p><b>Summary:</b> </p>'
	    )
	});

	oseMemMsc.gridStore = new Ext.data.Store({
		proxy: new Ext.data.HttpProxy({
		  	url: 'index.php?option=com_osemsc&controller=member',
	            method: 'POST'
	    }),
		baseParams:{task: "getOwnMsc",limit: 20}, 
			reader: new Ext.data.JsonReader({   
		              // we tell the datastore where to get his data from
				root: 'results',
				totalProperty: 'total'
			},[ 
			    {name: 'id', type: 'int', mapping: 'id'},
			    {name: 'membership', type: 'string', mapping: 'title'},
			    {name: 'start_date', type: 'datetime', mapping: 'start_date'},
			    {name: 'expired_date', type: 'datetime', mapping: 'expired_date'},
			    {name: 'status', type: 'int', mapping: 'status'},
			    
		]),
		sortInfo:{field: 'id', direction: "ASC"},
		autoLoad:{},
	});

	oseMemMsc.grid = new Ext.grid.GridPanel({
	    store: oseMemMsc.gridStore,
	    viewConfig:{forceFit:true},
	    
	    plugins: [oseMemMsc.gridExpander],
	 	colModel:new Ext.grid.ColumnModel({
		        defaults: {
		            width: 200,
		            sortable: true
		        },
		        columns: [
			        oseMemMsc.gridExpander,
				    {id: 'id', header: 'ID', dataIndex: 'id', hidden: true,hideable:true,},
				    {id: 'membership', header: 'My Subscription', dataIndex: 'membership'},
				    {id: 'start_date', header: 'Start Date', dataIndex: 'start_date'},
				    {id: 'expired_date', header: 'Expired Date', dataIndex: 'expired_date'},
				    {
				    	id: 'status', header: 'Status', dataIndex: 'status',
				    	renderer: function(val)	{
				    		if(val == 1)	{
				    			return 'Active';
				    		}	else	{
				    			return 'Inactive';
				    		}
				    	}	
				    },
				    {
				    	header: 'Action', dataIndex: 'id',
				    	renderer: function(val)	{
				    		return '<a href="javascript:void(0)">ddd</a>';
				    	}
				    },
			    ]
		}),
	 	autoHeight: true,
	 	sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
	 	
	});
	
	oseMemMsc.juserForm = new Ext.FormPanel({
		ref: 'form',
		id:'osemsc-member-formpanel',
		formId:'osemsc-member-form',
        frame:false,
        anchor: '95%',
        layout:'form',
        bodyStyle:'padding:5px 10px 0px 5px',
        autoHeight: true,
 		defaultType: 'textfield',
 		labelWidth: 150,
 		labelAlign: 'top',
 		border: false,
        items: [
    		{itemId:"uname",fieldLabel:'User Name',allowBlank:false, name:'username'},
        	{itemId:'name',fieldLabel:'First Name',allowBlank:false, name:'name'},
        	{itemId:'email',fieldLabel:'Email',vtype:'email',allowBlank:false, name:'email'},
        	{itemId:'passwd',fieldLabel:'Password',allowBlank:true, name:'password',inputType:'password'},
        	{
        		itemId:'passwd2',fieldLabel:'Password Confirm',allowBlank:true, name:'password2',
    			inputType:'password',
    			validator :  function(val){
	        		if(val != oseMemMsc.juserForm.getComponent('passwd').getValue()){
	        			return 'It does not match '+oseMemMsc.juserForm.getComponent('passwd').fieldLabel;
	        		}	else	{
	        			return true;
	        		}
	            }
        	},
        	//{xtype: 'hidden',itemId:'id',name:'id'},
        ],
        
        reader: new Ext.data.JsonReader({   
		    root: 'result',
		    totalProperty: 'total',
		    fields:[ 
			    {name: 'id', type: 'int', mapping: 'id'},
			    {name: 'username', type: 'string', mapping: 'username'},
			    {name: 'name', type: 'string', mapping: 'name'},
			    {name: 'email', type: 'string', mapping: 'email'},
		  	]
	  	}),
        
        tbar: [{
			text: 'Save',
			handler: function(){
        		oseMemMsc.juserForm.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=member',
				    
				    params: {
				        task: 'action',action:'member.juser.save', 
				    },
				    
				    success: function(form, action) {
				    	var msg = action.result;
				    	oseMemMsc.msg.setAlert(msg.title,msg.content);
				    },
				    failure: function(form, action) {
				    	
				    }
				});
        	},
		}],  
		
		
    });
    
	oseMemMsc.juser = new Ext.Panel({
		title: 'Joomla Registered User Information',
		items:[
			oseMemMsc.juserForm
		],
		
		listeners: {
			activate: function(p){
				p.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=member',
					params:{task:'action',action:'member.juser.getItem'},
				});
			}
		}
	})
    
	oseMemMsc.tabPanel = new Ext.TabPanel({
		activeTab: 0,
		defaultType:'panel',
		items:[
			oseMemMsc.juser,
		],
	});

oseMemMsc.panel = new Ext.Panel({
	labelWidth: 75, // label settings here cascade unless overridden
    frame: false,
	plain: false,
	border:false,
	items:[oseMemMsc.tabPanel],
});

