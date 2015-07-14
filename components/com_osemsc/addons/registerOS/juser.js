Ext.ns('oseMscAddon','oseMscAddon.juserParams');
	/// Params Setting
	/*oseMscAddon.juserParams.uniqueUserName= {checked:false};
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
	})*/
	/// Params Setting End

	oseMscAddon.juser = function(){

	}

	oseMscAddon.juser.prototype = {
		init: function()	{
			var juserFieldset = new Ext.form.FieldSet({
		 		title: Joomla.JText._('User_Account'),
		 		defaultType: 'textfield',
		 		labelWidth: 130,
		 		defaults: {width: 280,msgTarget : 'side'},
		 		items:[{
		            fieldLabel: Joomla.JText._('Username')
		        	,xtype:'textfield'
		        	,name: "juser_username"
		            ,allowBlank:false
		            ,validationEvent: 'blur'
		            ,vtype: 'uniqueUserName'
		            ,addonName: 'juser'
		        },{
		            fieldLabel: Joomla.JText._('First_Name')
		            ,name: "juser.firstname"
		            ,allowBlank:false
		            ,vtype: 'noSpace'
		        },{
		            fieldLabel: Joomla.JText._('Last_Name')
		            ,name: "juser.lastname"
		            ,allowBlank:false
		        },{
		            fieldLabel: Joomla.JText._('Email')
		            ,name: 'juser.email'
		            ,vtype:'email'
		            ,allowBlank:false
		        },{
		        	itemId: 'pwd'
		            ,fieldLabel: Joomla.JText._('Password')
		            ,name: 'juser.password1'
		            ,inputType: 'password'
		            ,vtype:'Password'
		            ,allowBlank:false
		        },{
		            fieldLabel: Joomla.JText._('Password_Confirm')
		            ,name: 'juser.password2'
		            ,inputType: 'password'
		            ,vtype:'Password'
		            ,validateOnBlur: true
		            ,validator :  function(val){
		            	var pass = juserFieldset.getComponent('pwd');
		        		if(val != pass.getValue()){
		        			return pass.fieldLabel + ' does not match';
		        		}	else	{
		        			return true;
		        		}
		            }
		        }]
		 	});

		 	return juserFieldset
		}
	}
