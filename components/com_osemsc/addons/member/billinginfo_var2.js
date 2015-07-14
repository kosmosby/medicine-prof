Ext.ns('oseMscAddon');

	var country = oseMsc.combo.getCountryCombo(Joomla.JText._('Country'),'bill_country',3,'local');
	var state = oseMsc.combo.getStateCombo(Joomla.JText._('State_Province'),'bill_state',2,'local');

	oseMsc.combo.getLocalJsonData(country,oseMsc.countryData);
	oseMsc.combo.getLocalJsonData(state,oseMsc.stateData);
	state.getStore().fireEvent('load',state.getStore());
	
	oseMsc.combo.relateCountryState(country,state,oseMsc.defaultSelectedCountry.code3);
	
	oseMscAddon.billinginfo_var2 = new Ext.FormPanel({
		autoScroll: true
		,height: 399
		,defaultType: 'textfield'
		,bodyStyle:'padding:10px'
		,labelWidth: 150
		,defaults: {width: 300}
		,buttons: [{
			text: 'save',
			handler: function(){
				oseMscAddon.billinginfo_var2.getForm().submit({
				    clientValidation: true
				    ,url: 'index.php?option=com_osemsc&controller=member'
				    ,params: {
				        task: 'action', action : 'member.billinginfo_var2.save'
				    }
				    ,success: function(form, action) {
				    	oseMsc.formSuccess(form, action);
				    }
				    ,failure: function(form, action) {
				       oseMsc.formFailure(form, action);
				    }
    			})
			}
		}]

		,items:[{
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
        }, country,state
        ,{
            fieldLabel: Joomla.JText._('Zip_Postal_Code')
            ,name: 'bill.postcode'
            ,allowBlank:false
        }]
	    ,reader:new Ext.data.JsonReader({
		    root: 'result'
		    ,totalProperty: 'total'
		    ,idProperty: 'user_id'
		    ,fields:[
			    {name: 'bill.firstname', type: 'string', mapping: 'firstname'}
			    ,{name: 'bill.lastname', type: 'string', mapping: 'lastname'}
			    ,{name: 'bill.company', type: 'string', mapping: 'company'}
			    ,{name: 'bill.addr1', type: 'string', mapping: 'addr1'}
			    ,{name: 'bill.addr2', type: 'string', mapping: 'addr2'}
			    ,{name: 'bill.city', type: 'string', mapping: 'city'}
			    ,{name: 'bill_state', type: 'string', mapping: 'state'}
			    ,{name: 'bill_country', type: 'string', mapping: 'country'}
			    ,{name: 'bill.postcode', type: 'string', mapping: 'postcode'}
		  	]
	  	})

		,listeners: {
			render: function(p){
				oseMscAddon.billinginfo_var2.getForm().load({
					url: 'index.php?option=com_osemsc&controller=member'
					,waitMsg: 'Loading'
					,params:{
						task:'action'
						,action:'member.billinginfo.getItem'
					}
					,success: function(form,action)	{
						var msg = action.result;
						
						if(msg.data['bill_country'] == '' || typeof(msg.data['bill_country']) == 'undefined')	{
							country.setValue(oseMsc.defaultSelectedCountry.code3);
						}
						
						if(msg.data['bill_state'] == '' || typeof(msg.data['bill_state']) == 'undefined')	{
							var cs = country.getStore()
							country.fireEvent('select',country,cs.getAt(cs.findExact(country.valueField,country.getValue())))
						}	else	{
							var cs = country.getStore()
							country.fireEvent('select',country,cs.getAt(cs.findExact(country.valueField,country.getValue())))
							state.setValue(msg.data['bill_state']);
						}
					}
				})
			}

		}
	});