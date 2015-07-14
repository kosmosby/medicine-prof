Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();


	//
	// Addon Msc Panel
	//
	var country = oseMsc.combo.getCountryCombo(Joomla.JText._('Country'),'company_country',3,'local');
	var state = oseMsc.combo.getStateCombo(Joomla.JText._('State_Province'),'company_state',2,'local');

	oseMsc.combo.relateCountryState(country,state,oseMsc.defaultSelectedCountry.code3);
	
	oseMsc.combo.getLocalJsonData(country,oseMsc.countryData);
	oseMsc.combo.getLocalJsonData(state,oseMsc.stateData);
	state.getStore().fireEvent('load',state.getStore());
	
	oseMscAddon.company = new Ext.FormPanel({
		autoScroll: true
		,height: 399
		,defaultType: 'textfield'
		,bodyStyle: 'padding: 10px'
		,labelWidth: 150
		,defaults: {width: 300}
		,buttons: [{
			text: 'save',
			handler: function(){
				oseMscAddon.company.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=member',
				    params: {
				        task: 'action', action : 'member.company.save'
				    },
				    success: oseMsc.formSuccess,
				    failure: oseMsc.formFailure
    			})
			}
		}]
		
		,items:[{
            fieldLabel: 'Company',
            name: 'company.company'
        }, {
            fieldLabel: 'Street Address1',
            name: 'company.addr1'
        },{
            fieldLabel: 'Street Address2',
            name: 'company.addr2'
        },{
            fieldLabel: 'City',
            name: 'company.city'
        },country,state,{
            fieldLabel: 'Zip/Postal Code',
            name: 'company.postcode'
        },{
            fieldLabel: 'Phone',
            name: 'company.telephone'
        }],
        
	    reader:new Ext.data.JsonReader({   
		    root: 'result',
		    totalProperty: 'total',
		    idProperty: 'company_id',
		    fields:[ 
			    {name: 'company.company', type: 'string', mapping: 'company'},
			    {name: 'company.addr1', type: 'string', mapping: 'addr1'},
			    {name: 'company.addr2', type: 'string', mapping: 'addr2'},
			    {name: 'company.city', type: 'string', mapping: 'city'},
			    {name: 'company_state', type: 'string', mapping: 'state'},
			    {name: 'company_country', type: 'string', mapping: 'country'},
			    {name: 'company.postcode', type: 'string', mapping: 'postcode'},
			    {name: 'company.telephone', type: 'string', mapping: 'telephone'}
		  	]
	  	})
		
		,listeners: {
			render: function(p){
				p.getForm().load({
					url: 'index.php?option=com_osemsc&controller=member'
					,waitMsg: 'Loading...'
					,params:{
						task:'action'
						,action:'member.company.getItem'
					}
					,success: function(form,action)	{
						var msg = action.result;
						
						if(msg.data['company_country'] == '' || typeof(msg.data['company_country']) == 'undefined')	{
							country.setValue(oseMsc.defaultSelectedCountry.code3);
						}
						
						if(msg.data['company_state'] == '' || typeof(msg.data['company_state']) == 'undefined')	{
							var cs = country.getStore()
							country.fireEvent('select',country,cs.getAt(cs.findExact('code',country.getValue())))
						}	else	{
							var cs = country.getStore()
							country.fireEvent('select',country,cs.getAt(cs.findExact(country.valueField,country.getValue())))
							state.setValue(msg.data['company_state']);
						}
					}
				})
			}
		}
	});