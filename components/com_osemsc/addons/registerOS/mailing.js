Ext.ns('oseMscAddon');

	oseMscAddon.mailing = function(mf)	{
		this.mf = mf;
	}

	oseMscAddon.mailing.prototype = {
		init: function()	{
			var country = oseMsc.combo.getCountryCombo(Joomla.JText._('Billing_Country'),'mailing_country',3,'local');
			var state = oseMsc.combo.getStateCombo(Joomla.JText._('Billing_State'),'mailing_state',2,'local');

			oseMsc.combo.getLocalJsonData(country,oseMsc.countryData);
			oseMsc.combo.getLocalJsonData(state,oseMsc.stateData);

			state.getStore().fireEvent('load',state.getStore());
			oseMsc.combo.relateCountryState(country,state,oseMsc.defaultSelectedCountry.code3);

			var c = country;
			var r = c.getStore().getAt(c.getStore().find(c.valueField,c.getValue()))
			c.fireEvent('select',c,r);

			var fs = new Ext.form.FieldSet({
				defaultType: 'textfield'
				,title: Joomla.JText._('Mailing_Information')
				,id:'ose-reg-mailing'
		 		,labelWidth: 130
		 		,layout: 'form'
		 		,defaults: {msgTarget : 'side',width: 280}
		 		,items: [
		 		{
			        fieldLabel: Joomla.JText._('Company')
			        ,name: 'mailing.company'
			        ,allowBlank: true
			    },{
		            fieldLabel: Joomla.JText._('Address')
		            ,name: 'mailing.addr1'
		            ,allowBlank: false
		            ,vtype: 'noSpace'
		        },{
		            fieldLabel: Joomla.JText._('Billing_City')
		            ,name: 'mailing.city'
		            ,allowBlank: false
		            ,vtype: 'noSpace'
		        },{
		            fieldLabel: Joomla.JText._('Billing_Postal_Code')
		            ,name: 'mailing.postcode'
		            ,allowBlank: false
		            ,vtype: 'noSpace'
		        }
		        ,country
		        ,state
		        ,{
			        fieldLabel: Joomla.JText._('Phone')
			        ,name: 'mailing.telephone'
			        ,allowBlank: true
				},
				{
		 			xtype: 'checkbox'
		 			,fieldLabel: Joomla.JText._('BILLING_SAME_AS_MAILING')
		 			,handler: function(cb,checked)	{
		 				if(checked)	{
		 					Ext.each(fs.findByType('textfield'),function(item,i,all){
			 					var name = item.getName();
			 					if(name.indexOf('mailing.') >=0)	{
			 						var bName = item.getName().replace('mailing.','bill.')
			 					}	else if(name.indexOf('mailing_') >=0)	{
			 						var bName = item.getName().replace('mailing_','bill_')
			 					}

			 					var bField = this.mf.getForm().findField(bName);
			 					if(typeof(bField) != 'undefined' ||bField != '')	{
			 						//item.setValue(bField.getValue())
			 						bField.setValue(item.getValue())
			 					}

			 				},this)
		 				}
		 			}
		 			,scope: this
		 		}]
			})
			
			var option = this.mf.findById('membership-type-info').getComponent('msc_option');
			option.addListener('select',function(c,r,i)	{
				var free = r.get('isFree');
				if(typeof(Ext.getCmp('ose-reg-mailing')) != 'undefined')	{
	    			Ext.each(Ext.getCmp('ose-reg-mailing').findByType('textfield'),function(item,i,all){
						if(item.getName() == 'mailing.addr1')	{
							item.allowBlank = free;
						}
					});
	    		}
			});
			return fs;
		}
	}