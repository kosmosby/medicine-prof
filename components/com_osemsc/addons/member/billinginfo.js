Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();

	//
	// Addon Msc Panel
	//
	var country = oseMsc.combo.getCountryCombo(Joomla.JText._('Country'),'bill_country',3,'local');
	var state = oseMsc.combo.getStateCombo(Joomla.JText._('State_Province'),'bill_state',2,'local');

	oseMsc.combo.getLocalJsonData(country,oseMsc.countryData);
	oseMsc.combo.getLocalJsonData(state,oseMsc.stateData);
	state.getStore().fireEvent('load',state.getStore());

	oseMsc.combo.relateCountryState(country,state,oseMsc.defaultSelectedCountry.code3);

	oseMscAddon.billinginfo = new Ext.FormPanel({
		autoScroll: true
		,height: 399
		,defaultType: 'textfield'
		,bodyStyle:'padding:10px'
		,labelWidth: 150
		,defaults: {width: 300, msgTarget: 'side'}
		,buttons: [{
			text: Joomla.JText._('save'),
			handler: function(){
				Ext.Msg.wait(Joomla.JText._('Please_Wait'),Joomla.JText._('Please_Wait'));
				oseMscAddon.billinginfo.getForm().submit({
				    clientValidation: true
				    ,url: 'index.php?option=com_osemsc&controller=member'
				   // ,waitMsg: 'Please wait...'
				    ,params: {
				        task: 'action', action : 'member.billinginfo.save'
				    }
				    ,success: function(form, action) {
				    	Ext.Msg.hide();
				    	oseMsc.formSuccess(form, action);
				    }
				    ,failure: function(form, action) {
				    	Ext.Msg.hide();
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
        },
        country,state
        /*{
            fieldLabel: Joomla.JText._('State_Province')
            ,name: 'bill.state'
            ,allowBlank:false
        },{
            fieldLabel: Joomla.JText._('Country')
            ,hiddenName: 'bill.country'
            ,id: 'bill_country'
            ,xtype: 'combo'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    //,listClass: 'combo-left'
		    ,lazyInit: false
		    ,mode: 'remote'
		    ,forceSelection: true
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=register',
		            method: 'POST'
	      		})
			  	,baseParams:{task: "getCountry"}
			  	,reader: new Ext.data.JsonReader({
			    	root: 'results'
			    	,totalProperty: 'total'
			    	,idProperty: 'country_id'
			  	},[
			    {name: 'code', type: 'string', mapping: 'country_2_code'},
			    {name: 'cname', type: 'string', mapping: 'country_name'}
			  	])
			  	,listeners:{
			  		load: function(s)	{
			  			oseMscAddon.billinginfo.getForm().load({
							url: 'index.php?option=com_osemsc&controller=member'
							,waitMsg: 'Loading'
							,params:{
								task:'action'
								,action:'member.billinginfo.getItem'
							}
							,callback: function(el,success,response,opt)	{
								var msg = Ext.decode(response.responseText);

								if(!msg.result.country)	{
									oseMscAddon.billinginfo.findById('bill_country').setValue('US');
								}
							}
						})

			  		}
			  	}
			})

		    ,valueField: 'code'
		    ,displayField: 'cname'
        }*/
        ,{
            fieldLabel: Joomla.JText._('Zip_Postal_Code')
            ,name: 'bill.postcode'
            ,allowBlank:false
        },{
		    fieldLabel: Joomla.JText._('Phone')
		    ,name: 'bill.telephone'
		    ,allowBlank: true
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
			    ,{name: 'bill.telephone', type: 'string', mapping: 'telephone'}
		  	]
	  	})

		,listeners: {
			render: function(p){
				//p.findById('bill_country').getStore().load();
				Ext.Msg.wait(Joomla.JText._('Loading'),Joomla.JText._('Please_Wait'));
				oseMscAddon.billinginfo.getForm().load({
					url: 'index.php?option=com_osemsc&controller=member'
					//,waitMsg: 'Loading'
					,params:{
						task:'action'
						,action:'member.billinginfo.getItem'
					}
					,success: function(form,action)	{
						Ext.Msg.hide();
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