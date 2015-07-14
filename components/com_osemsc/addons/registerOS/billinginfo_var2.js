Ext.ns('oseMscAddon');

	oseMscAddon.billinginfo = function(mf)	{
		this.mf = mf;
	}

	oseMscAddon.billinginfo.prototype = {
		init: function()	{
			var billinginfo1 = [{
		        fieldLabel: Joomla.JText._('Phone')
		        ,name: 'bill.telephone'
		        ,allowBlank: true
		        ,hidden: true
			}];
			var mf = this.mf;
			var country = oseMsc.combo.getCountryCombo1(Joomla.JText._('Billing_Country'),'bill_country',3,'local');
			var state = oseMsc.combo.getStateCombo1(Joomla.JText._('Billing_State'),'bill_state',2,'local');
			
			country.allowBlank = false;
			country.emptyText= 'Please Choose';
			state.allowBlank = false;
			state.emptyText= 'Please Choose';
			
			country.allowBlank = false;
			country.emptyText= 'Please Choose';
			state.allowBlank = false;
			state.emptyText= 'Please Choose';

			country.addListener('select',function(c,r,i)	{
				oseMsc.reg.bill_country = c.getValue()
			},this);

			state.addListener('select',function(c,r,i)	{
				oseMsc.reg.bill_state = c.getValue()
			},this);
			//alert(oseMsc.defaultSelectedCountry.toSource())
			oseMsc.combo.getLocalJsonData(country,oseMsc.countryData);
			oseMsc.combo.getLocalJsonData(state,oseMsc.stateData);

			//oseMsc.combo.relateCountryState(country,state,'');
			var relateCountryState=function(country,state,mf)	{
				country.addListener('blur',function(f){
				
					var r = country.getStore().getAt(country.getStore().findExact('code_3',f.getValue()));
					
					f.fireEvent('select',f,r);
				},this);
				
				country.addListener('select',function(c,r,i){
					if (r==undefined)
					{
					  return false;
					}
					var sr = r;
					state.country = r.get('country_id')
					state.getStore().filter([{
						fn   : function(record) {
							return record.get('country_id') == sr.get('country_id') || record.get('country_id') == 'all'
						},
						scope: this
					}]);

					if((c.getValue() == 'AUS' || c.getValue() == 'AU') && mf.getForm().findField('payment.payment_method').getValue() == 'eway')	{
						state.valueField = 'code_3';
					}	else	{
						state.valueField = 'code_2';
					}

					if(state.getStore().getCount() > 1)	{
						state.setValue(state.getStore().getAt(1).get(state.valueField))
					}	else	{
						state.setValue('--');
					}
				},this);

			}
			relateCountryState(country,state,mf);
			
			state.getStore().fireEvent('load',state.getStore(),state.getStore().getRange())
			//country.fireEvent('select',country,country.getStore().getById(country.getStore().findExact('code_3',country.getValue())+1))



			//oseMsc.combo.getLocalJsonData(state,oseMsc.stateData);
			//state.getStore().fireEvent('load',state.getStore(),state.getStore().getRange())

			//country.fireEvent('select',country,country.getStore().getById(country.getStore().findExact('code_3',country.getValue())+1))

			//oseMsc.combo.getLocalJsonData(state,oseMsc.stateData);
			//state.getStore().fireEvent('load',state.getStore(),state.getStore().getRange())

			return new Ext.form.FieldSet({
				defaultType: 'textfield'
				,title: Joomla.JText._('Billing_Information')
				,id:'ose-reg-billinginfo'
		 		,labelWidth: 130
		 		,layout: 'form'
		 		,defaults: {msgTarget : 'side',width: 280}
		 		,items: [{
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
		            ,name: "juser.email"
		            ,allowBlank:false
		            ,vtype: 'email'
		        }
					,country
			        ,state
			        ,billinginfo1
		        ]
			})
		}
	}

