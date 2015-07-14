Ext.ns('oseMscAddon');

	oseMscAddon.billinginfo_var1 = function()	{

	}

	oseMscAddon.billinginfo_var1.prototype = {
		init: function()	{
			var billinginfo1 = [
			{
		        fieldLabel: Joomla.JText._('Phone')
		       , name: 'bill.telephone'
			}];

			var country = oseMsc.combo.getCountryCombo(Joomla.JText._('Billing_Country'),'bill_country',3);
			var state = oseMsc.combo.getStateCombo(Joomla.JText._('Billing_State'),'bill_state',2);

			oseMsc.combo.getLocalJsonData(country,oseMsc.countryData);
			oseMsc.combo.getLocalJsonData(state,oseMsc.stateData);
			oseMsc.combo.relateCountryState(country,state,oseMsc.defaultSelectedCountry.code3);
			state.getStore().fireEvent('load',state.getStore(),state.getStore().getRange())
			country.fireEvent('select',country,country.getStore().getById(country.getStore().findExact('code_3',country.getValue())+1))


			return new Ext.form.FieldSet({
				defaultType: 'textfield'
				,title: Joomla.JText._('Billing_Information')
				,id:'ose-reg-billinginfo'
		 		,labelWidth: 130
				,allowBlank: false
		 		,layout: 'form'
		 		,defaults: {msgTarget : 'side',width: 280}
		 		,items: [
			{
			    fieldLabel: Joomla.JText._('Company')
			    ,name: 'bill.company'
			    ,allowBlank: false
		    	},
		    {
		            fieldLabel: Joomla.JText._('VAT_Number')
		            ,name: 'bill.vat_number'
		            ,allowBlank: true
		        },
			{
		            fieldLabel: Joomla.JText._('Billing_Address')
		            ,name: 'bill.addr1'
		            ,allowBlank: false
		            ,vtype: 'noSpace'
		        },{
		            fieldLabel: Joomla.JText._('Billing_City')
		            ,name: 'bill.city'
		            ,allowBlank: false
		            ,vtype: 'noSpace'
		        },{
		            fieldLabel: Joomla.JText._('Billing_Postal_Code')
		            ,name: 'bill.postcode'
		            ,allowBlank: false
		            ,vtype: 'noSpace'
		        }
		        ,country
		        ,state
		        ,billinginfo1
		        ]
			})
		}
	}
