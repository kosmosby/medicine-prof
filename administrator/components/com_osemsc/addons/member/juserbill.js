Ext.ns('oseMemMsc','oseMscAddon','oseMscAddon.juserbillParams');
	
	/// Params Setting
	oseMscAddon.juserbillParams.uniqueUserName= {checked:false};
	Ext.apply(Ext.form.VTypes,{
		uniqueUserName: function(val,field)	{
			var unique = oseMscAddon.juserbillParams.uniqueUserName;
			if(!unique.checked)	{
				
				Ext.Ajax.request({
	        		url: 'index.php?option=com_osemsc&controller=members'
	        		,params: {
	        			task : 'action',action:'member.juserbill.formValidate' 
	        			,username : val
	        			,member_id: oseMscAddon.juserbill.getForm().findField('user_id').getValue()
	        		}
	        		,success: function(response, opt)	{
	        			var msg = Ext.decode(response.responseText);
	        			
	        			unique =  msg;
	        			unique.checked = true;
	        			
	        			oseMscAddon.juserbillParams.uniqueUserName = unique;
	        			return field.validate();
	        		}
	        	});
			}	else	{
			
				oseMscAddon.juserbillParams.uniqueUserName.checked = false;
				
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
	
	var country = oseMsc.combo.getCountryCombo(Joomla.JText._('Country'),'bill_country',3,'local');
	var state = oseMsc.combo.getStateCombo(Joomla.JText._('State_Province'),'bill_state',2,'local');

	oseMsc.combo.getLocalJsonData(country,oseMsc.countryData);
	oseMsc.combo.getLocalJsonData(state,oseMsc.stateData);
	state.getStore().fireEvent('load',state.getStore());

	oseMsc.combo.relateCountryState(country,state,oseMsc.defaultSelectedCountry.code3);
	
	oseMscAddon.juserbill = new Ext.FormPanel({
		ref: 'form'
		,id:'osemsc-member-formpanel'
		,formId:'osemsc-member-form'
        ,frame:false
        ,bodyStyle:'padding:10px'
        ,height: 600
 		,defaultType: 'textfield'
 		,labelWidth: 150
 		//labelAlign: 'top'
 		,defaults: {width: 300,msgTarget: 'side'}
 		,border: false
        ,items: [
    		{
    			itemId:"uname",fieldLabel:Joomla.JText._('User_Name'),allowBlank:false, name:'username'
    			,vtype: 'uniqueUserName'
    		}
        	,{itemId:'firstname',fieldLabel:Joomla.JText._('First_Name'),allowBlank:false, name:'firstname'}
        	,{itemId:'lastname',fieldLabel:Joomla.JText._('Last_Name'),allowBlank:false, name:'lastname'}
        	,{itemId:'email',fieldLabel:Joomla.JText._('Email'),vtype:'email',allowBlank:false, name:'email'}
        	,{itemId:'passwd',fieldLabel:Joomla.JText._('Password'),allowBlank:true, name:'password', vtype:'alphanum',inputType:'password'}
        	,{
        		itemId:'passwd2',fieldLabel:Joomla.JText._('Password_Confirm'),allowBlank:true, name:'password2', vtype:'alphanum',
    			inputType:'password',
    			validator :  function(val){
	        		if(val != oseMscAddon.juserbill.getComponent('passwd').getValue()){
	        			return oseMscAddon.juserbill.getComponent('passwd').fieldLabel + Joomla.JText._('does_not_match');
	        		}	else	{
	        			return true;
	        		}
	            }
        	}
        	,{itemId:'user_id',  name:'user_id', xtype: 'hidden'}
        	,{
        		name:'member_id'
    			,xtype: 'hidden'
    		},{
    	        fieldLabel: Joomla.JText._('First_Name')
    	        ,name: 'bill.firstname'
    	        ,allowBlank:false
    	    },{
    	        fieldLabel: Joomla.JText._('Last_Name')
    	        ,name: 'bill.lastname'
    	        ,allowBlank:false
    	    },{
    	        fieldLabel: Joomla.JText._('Company')
    	        ,name: 'bill.company'
    	    }, {
    	        fieldLabel: Joomla.JText._('Street_Address1')
    	        ,name: 'bill.addr1'
    	        ,allowBlank:false
    	    },{
    	        fieldLabel: Joomla.JText._('Street_Address2')
    	        ,name: 'bill.addr2'
    	    },{
    	        fieldLabel: Joomla.JText._('City')
    	        ,name: 'bill.city'
    	        ,allowBlank:false
    	    },
    	    country,state
    	    ,{
    	        fieldLabel: Joomla.JText._('Zip_Postal_Code')
    	        ,name: 'bill.postcode'
    	        ,allowBlank:false
    	    },{
    	        fieldLabel: Joomla.JText._('Phone')
    	        ,name: 'bill.telephone'
    	    }
        ]
        
        ,reader: new Ext.data.JsonReader({   
		    root: 'result'
		    ,totalProperty: 'total'
		    ,idProperty: 'user_id'
		    ,fields:[ 
			    {name: 'user_id', type: 'int', mapping: 'user_id'}
			    ,{name: 'username', type: 'string', mapping: 'username'}
			    ,{name: 'firstname', type: 'string', mapping: 'firstname'}
			    ,{name: 'lastname', type: 'string', mapping: 'lastname'}
			    ,{name: 'email', type: 'string', mapping: 'email'}
			    ,{name: 'bill.firstname', type: 'string', mapping: 'firstname'}
			    ,{name: 'bill.lastname', type: 'string', mapping: 'lastname'}
			    ,{name: 'bill.company', type: 'string', mapping: 'company'}
			    ,{name: 'bill.addr1', type: 'string', mapping: 'addr1'}
			    ,{name: 'bill.addr2', type: 'string', mapping: 'addr2'}
			    ,{name: 'bill.city', type: 'string', mapping: 'city'}
			    ,{name: 'bill_state', type: 'string', mapping: 'state'}
			    ,{name: 'bill_country', type: 'string', mapping: 'country'}
			    ,{name: 'bill.postcode', type: 'string', mapping: 'postcode'}
			    ,{name: 'bill.telephone', type: 'string', mapping: 'telephone'}
			    ,{name: 'member_id', type: 'int', mapping: 'user_id'}
		  	]
	  	})
       
        ,buttons: [{
			text: Joomla.JText._('Save')
			,handler: function(){
				
        		oseMscAddon.juserbill.getForm().submit({
				    clientValidation: true
				    ,waitMsg: 'Loading...'
				    ,url: 'index.php?option=com_osemsc&controller=members'
				    ,params: {
				        task: 'action',action:'member.juserbill.save'
				        
				    }
				    ,success: function(form, action) {
				    	oseMsc.formSuccess(form,action);
				    	oseMemsMsc.grid.getBottomToolbar().doRefresh();
				    }
				    ,failure: function(form, action) {
				    	if (action.failureType === Ext.form.Action.CLIENT_INVALID){
							Ext.Msg.alert('Notice','Pleas Check The Notice In The Form');
				        }
						
						if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
				            Ext.Msg.alert('Error',
				            'Status:'+action.response.status+': '+
				            action.response.statusText);
				        }
				        
				        if (action.failureType === Ext.form.Action.SERVER_INVALID){
				            var msg = action.result;
							if(!action.result.script)	{
	 							oseMsc.formFailureMB(form,action,function(btn,text){
		 							if(btn == 'ok')	{
		 								Ext.Msg.wait(Joomla.JText._('Redirecting'). Joomla.JText._('Please_Wait'));
		 								window.location.reload();
		 							}
		 						});
							}	else	{
								
		 						confirmWin.close();
								eval('oseMsc.reg.regForm.getForm().findField'+action.result.script.replace('username','juser.username'));
							}
				        }
				    }
				})
        	}
		}]  
		
		,listeners: {
			render: function(p){
				p.getForm().load({
					url: 'index.php?option=com_osemsc&controller=members'
					,params:{task:'action',action:'member.juserbill.getItem',member_id: oseMemsMsc.member_id}
				})
			}
		}
    });