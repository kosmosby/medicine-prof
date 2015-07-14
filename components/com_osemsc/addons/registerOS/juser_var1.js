Ext.ns('oseMscAddon');

	oseMscAddon.juser = function(){

	}

	oseMscAddon.juser.prototype = {
		init: function()	{
			var juserFieldset = new Ext.form.FieldSet({
		 		title: Joomla.JText._('User_Account'),
		 		defaultType: 'textfield',
		 		labelWidth: 130,
		 		defaults: {width: 280,msgTarget : 'side'},
		 		items:[
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
		        {
		            fieldLabel: Joomla.JText._('First_Name')
		            ,name: "juser.firstname"
		            ,allowBlank:false
		            ,vtype: 'noSpace'
		        },{
		            fieldLabel: Joomla.JText._('Last_Name')
		            ,name: "juser.lastname"
		            ,allowBlank:false
		            ,vtype: 'noSpace'
		        },{
		        	itemId: 'pwd'
		            ,fieldLabel: Joomla.JText._('Password')
		            ,name: 'juser.password1'
		            ,inputType: 'password'
		            ,vtype:'alphanum'
		            ,allowBlank:false
		        },{
		            fieldLabel: Joomla.JText._('Password_Confirm')
		            ,name: 'juser.password2'
		            ,inputType: 'password'
		            ,vtype:'alphanum'
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
