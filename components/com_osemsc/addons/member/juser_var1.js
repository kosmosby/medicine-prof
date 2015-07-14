Ext.ns('oseMemMsc','oseMscAddon','oseMscAddon.juserParams');

	/// Params Setting
	oseMscAddon.juserParams.uniqueUserName= {checked:false};
	Ext.apply(Ext.form.VTypes,{
		uniqueUserName: function(val,field)	{
			var unique = oseMscAddon.juserParams.uniqueUserName;
			if(!unique.checked)	{

				Ext.Ajax.request({
	        		url: 'index.php?option=com_osemsc&controller=member'
	        		,params: {
	        			task : 'action',action:'member.juser.formValidate'
	        			,username : val
	        		}
	        		,success: function(response, opt)	{
	        			var msg = Ext.decode(response.responseText);

	        			unique =  msg;
	        			unique.checked = true;

	        			oseMscAddon.juserParams.uniqueUserName = unique;
	        			return field.validate();
	        		}
	        	});
			}	else	{

				oseMscAddon.juserParams.uniqueUserName.checked = false;

				if(!Ext.isBoolean(unique.result))	{
    				return false;
    			}	else	{
    				return true;
    			}

			}
			return true;
		}
		,uniqueUserNameText: Joomla.JText._('This_username_has_been_registered_by_other_user')
	})
	/// Params Setting End

	oseMscAddon.juser = new Ext.FormPanel({
		ref: 'form',
		id:'osemsc-member-formpanel',
		formId:'osemsc-member-form',
        frame:false,
        bodyStyle:'padding:10px',
        height: 300,
 		defaultType: 'textfield',
 		labelWidth: 150,
 		//labelAlign: 'top',
 		defaults: {width: 300,msgTarget: 'side'},
 		border: false,
        items: [
    		{
		            fieldLabel: Joomla.JText._('Email')
		        	,xtype:'textfield'
		        	,name: "juser.username"
		            ,allowBlank:false
		            ,vtype: 'email'
		            ,listeners:{
	                         blur: function(f){
	                               f.nextSibling().setValue(f.getValue())
                                 }
                            }
		        },
		        {
		            name: 'juser.email'
		            ,xtype:'hidden'
		            ,vtype:'email'
		            ,allowBlank:false
		     },

        	{itemId:'firstname',fieldLabel:Joomla.JText._('First_Name'),allowBlank:false, name:'firstname'},
        	{itemId:'lastname',fieldLabel:Joomla.JText._('Last_Name'),allowBlank:false, name:'lastname'},

        	{itemId:'passwd',fieldLabel:Joomla.JText._('Password'),allowBlank:true, name:'password', vtype:'alphanum',inputType:'password'},
        	{
        		itemId:'passwd2',fieldLabel:Joomla.JText._('Password_Confirm'),allowBlank:true, name:'password2', vtype:'alphanum',
    			inputType:'password',
    			validator :  function(val){
	        		if(val != oseMscAddon.juser.getComponent('passwd').getValue()){
	        			return oseMscAddon.juser.getComponent('passwd').fieldLabel + 'does not match';
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
			text: Joomla.JText._('Save'),
			handler: function(){
				oseMscAddon.juser.ownerCt.getEl().mask('Loading...');
        		oseMscAddon.juser.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=member',

				    params: {
				        task: 'action',action:'member.juser.save'
				    },

				    success: function(form, action) {
				    	oseMsc.formSuccess(form,action);
				    	oseMscAddon.juser.ownerCt.getEl().unmask();
				    },
				    failure: function(form, action) {
				    	if(action.result.script)	{
							eval('oseMscAddon.juser.getForm().findField'+action.result.script);
						}	else	{
							oseMsc.formFailureMB(form, action)
						}
						oseMscAddon.juser.ownerCt.getEl().unmask();
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



