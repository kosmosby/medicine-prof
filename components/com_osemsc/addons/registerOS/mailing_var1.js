Ext.ns('oseMscAddon');

	oseMscAddon.mailing = function(mf)	{
             this.mf = mf;
	}

	oseMscAddon.mailing.prototype = {
		init: function()	{
			var country = oseMsc.combo.getCountryCombo(Joomla.JText._('Billing_Country'),'mailing_country',3,'local');
			var state = oseMsc.combo.getStateCombo(Joomla.JText._('Billing_State'),'mailing_state',2,'local');

			country.addListener('select',function(c,r,i)	{
				oseMsc.reg.mailing_country = c.getValue()
			},this);

			country.setVisible(false);

			state.addListener('select',function(c,r,i)	{
				oseMsc.reg.mailing_state = c.getValue()
			},this);
			//alert(oseMsc.defaultSelectedCountry.toSource())
			oseMsc.combo.relateCountryState(country,state,oseMsc.defaultSelectedCountry.code3);

			oseMsc.combo.getLocalJsonData(country,oseMsc.countryData);

			oseMsc.combo.getLocalJsonData(state,oseMsc.stateData);
			state.getStore().fireEvent('load',state.getStore(),state.getStore().getRange());

			var billCountryArray = this.mf.find('hiddenName','bill_country');
			var billCountry = billCountryArray[0];
			billCountry.addListener('select',function(c,r,i){
				country.fireEvent('select',c,r,i);
			},this)

			var ClassificationCombo = new Ext.form.ComboBox({
			    typeAhead: true,
			    name: 'mailing.sector',
			    triggerAction: 'all',
			    lazyRender:true,
			    fieldLabel: 'Sector Classification',
			    mode: 'local',
			    store: new Ext.data.ArrayStore({
			        id: 0,
			        fields: [
			            'myId',
			            'displayText'
			        ],
			        data: [[1, 'Accrediting Group'],
							[2, 'Biotechnology/Genomics Company'],
							[3, 'Group Purchasing Organization'],
							[4, 'Government - Federal'],
							[5, 'Government - Local'],
							[6, 'Government - State'],
							[7, 'Health Information Exchange'],
							[8, 'Health Plan or Insurance Organization'],
							[9, 'Healthcare IT Apps'],
							[10, 'Healthcare IT Integration & Consulting'],
							[11, 'Healthcare System or IDN'],
							[12, 'Hospital'],
							[13, 'Laboratory'],
							[14, 'Medical Device Manufacturer'],
							[15, 'Non-Profit Association or Professional Society'],
							[16, 'Patient or Consumer Advocacy Group'],
							[17, 'Payer'],
							[18, 'Pharmaceutical Manufacturer'],
							[19, 'Physician Group'],
							[20, 'Practicing Clinician Organization'],
							[21, 'Purchasers/Employers'],
							[22, 'Public Health Organization'],
							[23, 'Quality Improvement Organization'],
							[24, 'Regional Extension Center'],
							[25, 'Research and Education Institution'],
							[26, 'Pharmacy - related organization'],
							[27, 'Standards Organization'],
							[28, 'Supply Chain Organization'],
							[29, 'Telecommunication Organization'],
							[30, 'Other']
			        	   ]
			    }),
			    valueField: 'myId',
			    displayField: 'displayText'
			});




			return new Ext.form.FieldSet(
				{
				defaultType: 'textfield'
				,title: 'Corporate Information'
				,id:'ose-reg-mailing'
		 		,labelWidth: 130
		 		,layout: 'form'
		 		,defaults: {msgTarget : 'side',width: 280}
		 		,items: [
		 		{
		        fieldLabel: 'Name'
		        ,name: 'mailing.company'
		        ,allowBlank: true
			    },
			    ClassificationCombo,
		 		{
		            fieldLabel: Joomla.JText._('Address')
		            ,name: 'mailing.addr1'
		            ,xtype: 'textarea'
		            ,allowBlank: false
		        },{
		            fieldLabel: Joomla.JText._('Billing_City')
		            ,name: 'mailing.city'
		            ,allowBlank: false
		        },{
		            fieldLabel: Joomla.JText._('Billing_Postal_Code')
		            ,name: 'mailing.postcode'
		            ,allowBlank: false
		        }
		        ,country
		        ,state
		        ,{
			        fieldLabel: Joomla.JText._('Phone')
			        ,name: 'mailing.telephone'
			        ,allowBlank: true
				}
				,{
			        fieldLabel: 'Website Address'
			        ,name: 'mailing.telephone'
			        ,vtype:'url'
			        ,allowBlank: true
				}

		        ]
			})
		}
	}

