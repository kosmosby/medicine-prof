Ext.ns('oseMscAddon');

	oseMscAddon.billinginfo = function()	{

	}

	oseMscAddon.billinginfo.prototype = {
		init: function()	{
			var billinginfo1 = [{
		        fieldLabel: Joomla.JText._('Company')
		        ,name: 'bill.company'
		        ,allowBlank: true
		    },{
		        fieldLabel: Joomla.JText._('Phone')
		        ,name: 'bill.telephone'
		        ,allowBlank: true
			}];

			var country = oseMsc.combo.getCountryCombo(Joomla.JText._('Billing_Country'),'bill_country',3,'local');
			var state = oseMsc.combo.getStateCombo(Joomla.JText._('Billing_State'),'bill_state',2,'local');

			country.addListener('select',function(c,r,i)	{
				oseMsc.reg.bill_country = c.getValue()
			},this);

			state.addListener('select',function(c,r,i)	{
				oseMsc.reg.bill_state = c.getValue()
			},this);

			oseMsc.combo.getLocalJsonData(country,oseMsc.countryData);
			oseMsc.combo.getLocalJsonData(state,oseMsc.stateData);
			oseMsc.combo.relateCountryState(country,state,oseMsc.defaultSelectedCountry.code3);

			state.getStore().fireEvent('load',state.getStore(),state.getStore().getRange())
			country.fireEvent('select',country,country.getStore().getById(country.getStore().findExact('code_3',country.getValue())+1))


			return new Ext.form.FieldSet({
				defaultType: 'textfield'
				,title: Joomla.JText._('Billing_Information')
				,id:'ose-reg-billinginfo'
				,itemId:'ose-reg-billinginfo'
		 		,labelWidth: 130
		 		,layout: 'form'
		 		,defaults: {msgTarget : 'side',width: 280}
		 		,items: [{
		            fieldLabel: Joomla.JText._('Billing_Address')
		            ,name: 'bill.addr1'
		            ,allowBlank: false
		            ,vtype: 'noSpace'
		            ,minLength: 3
		        },{
		            fieldLabel: Joomla.JText._('Billing_Address_Line2')
		            ,name: 'bill.addr2'
		            ,allowBlank: true
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