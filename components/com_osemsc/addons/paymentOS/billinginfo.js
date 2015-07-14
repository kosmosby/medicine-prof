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
		       , name: 'bill.telephone'
			}];

			var country = oseMsc.combo.getCountryCombo(Joomla.JText._('Billing_Country'),'bill_country',3);
			var state = oseMsc.combo.getStateCombo(Joomla.JText._('Billing_State'),'bill_state',2);
			
			oseMsc.combo.relateCountryState(country,state,'USA');
			
			country.getStore().load()

			return new Ext.form.FieldSet({
				defaultType: 'textfield'
				,title: Joomla.JText._('Billing_Information')
		 		,labelWidth: 150
		 		,layout: 'form'
		 		,defaults: {msgTarget : 'side',width: 300}
		 		,items: [{
		            fieldLabel: Joomla.JText._('Billing_Address')
		            ,name: 'bill.addr1'
		            ,allowBlank: false
		        },{
		            fieldLabel: Joomla.JText._('Billing_City')
		            ,name: 'bill.city'
		            ,allowBlank: false
		        }
		        ,country,state,{
		            fieldLabel: Joomla.JText._('Billing_Postal_Code')
		            ,name: 'bill.postcode'
		            ,allowBlank: false
		        }
		        ,billinginfo1
		        ]
			})
		}
	}
