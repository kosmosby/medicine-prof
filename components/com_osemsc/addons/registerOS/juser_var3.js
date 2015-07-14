Ext.ns('oseMscAddon');

	oseMscAddon.juser_var3 = function(){

	}

	oseMscAddon.juser_var3.prototype = {
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
		            ,vtype: 'noSpace'
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
		            fieldLabel: 'Email'
		            ,name: 'juser.email'
		            ,vtype:'email'
		            ,allowBlank:false
		        },{
		        	itemId: 'pwd'
		            ,fieldLabel: Joomla.JText._('Password')
		            ,name: 'juser.password1'
		            ,inputType: 'password'
		            ,allowBlank:false
		        },{
		            fieldLabel: Joomla.JText._('Password_Confirm')
		            ,name: 'juser.password2'
		            ,inputType: 'password'
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
