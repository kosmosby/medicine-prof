Ext.ns('oseMemMsc');
	//oseMemMsc.msg = new Ext.App();
	
	oseMemMsc.juser = new Ext.FormPanel({
		ref: 'form',
		id:'osemsc-member-formpanel',
		formId:'osemsc-member-form',
        frame:false,
        bodyStyle:'padding:10px',
        height: 300,
 		defaultType: 'textfield',
 		labelWidth: 150,
 		//labelAlign: 'top',
 		defaults: {width: 300},
 		border: false,
        items: [
    		{
    			itemId:"uname",fieldLabel:'User Name',allowBlank:false, name:'username'
    			,validator: function(val)	{
	            	Ext.Ajax.request({
	            		url: 'index.php?option=com_osemsc&controller=member'
	            		,params: {
	            			task : 'uniqueUserName' 
	            			,username : val
	            		}
	            		,success: function(response, opt)	{
	            			var msg = Ext.decode(response.responseText);
	            			
	            			oseMemMsc.uniqueUserName =  msg.result;
	            		}
	            	});
	            	
	            	return oseMemMsc.uniqueUserName;
	            }
    		},
        	{itemId:'firstname',fieldLabel:'First Name',allowBlank:false, name:'firstname'},
        	{itemId:'lastname',fieldLabel:'Last Name',allowBlank:false, name:'lastname'},
        	{itemId:'email',fieldLabel:'Email',vtype:'email',allowBlank:false, name:'email'},
        	{itemId:'passwd',fieldLabel:'Password',allowBlank:true, name:'password',inputType:'password'},
        	{
        		itemId:'passwd2',fieldLabel:'Password Confirm',allowBlank:true, name:'password2',
    			inputType:'password',
    			validator :  function(val){
	        		if(val != oseMemMsc.juser.getComponent('passwd').getValue()){
	        			return 'It does not match '+oseMemMsc.juser.getComponent('passwd').fieldLabel;
	        		}	else	{
	        			return true;
	        		}
	            }
        	}
        ],
        
        reader: new Ext.data.JsonReader({   
		    root: 'result',
		    totalProperty: 'total',
		    idProperty: 'user_id',
		    fields:[ 
			    {name: 'user_id', type: 'int', mapping: 'user_id'},
			    {name: 'username', type: 'string', mapping: 'username'},
			    {name: 'firstname', type: 'string', mapping: 'firstname'},
			    {name: 'lastname', type: 'string', mapping: 'lastname'},
			    {name: 'email', type: 'string', mapping: 'email'}
		  	]
	  	}),
        
        buttons: [{
			text: 'Save',
			handler: function(){
        		oseMemMsc.juser.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=member',
				    
				    params: {
				        task: 'action',action:'member.juser.save'
				    },
				    
				    success: function(form, action) {
				    	var msg = action.result;
				    	oseMemMsc.msg.setAlert(msg.title,msg.content);
				    },
				    failure: function(form, action) {
				    	oseMemMsc.failure(form,action);
				    }
				});
        	}
		}]  
		
		,listeners: {
			render: function(p){
				p.getForm().load({
					url: 'index.php?option=com_osemsc&controller=member',
					params:{task:'action',action:'member.juser.getItem'}
				});
			}
		}
    });
    
    /*
	oseMemMsc.juser = new Ext.Panel({
		title: 'Joomla Registered User Information',
		autoHeight: true,
		items:[
			oseMemMsc.juserForm
		],
		
		listeners: {
			render: function(p){
				p.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=member',
					params:{task:'action',action:'member.juser.getItem'}
				});
			}
		}
	})
    */
	
