Ext.ns('oseMscAddon');

	var addonMscRadioGroup = new Ext.Panel({
		title: ''
		,ref: 'rgPanel'
		,border: false
		//,layout: 'form'
		,autoLoad:{
			url: 'index.php?option=com_osemsc&controller=register'
			,params:{task: "getMscList"}
			,callback: function(el,success,response,opt)	{
				var result = Ext.decode(response.responseText);

			 	addonMscRadioGroup.update('');
			 	var c = new Array();
			 	Ext.each(result.results, function(item,i,all){
			 		//alert(item.title);
			 		if(item.has_trial)	{
			 			var radioBoxLabel = item.title +' - '+ item.standard_price+' for every '+item.standard_recurrence+' ('+item.trial_price+' in first '+ item.trial_recurrence+')'
			 		}	else	{
			 			var radioBoxLabel = item.title +' - '+ item.standard_price+' for every '+item.standard_recurrence
			 		}
			 		
			 		c[i] = {
			 			xtype:'radio'
			 			,boxLabel: radioBoxLabel
			 			,name: 'msc_id'
			 			,inputValue: item.id
			 			,institution: (item.cs_mode == 'institution')?true:false
			 			,checked: (i == 0) ? true: false

			 		}
			 	})

			 	addonMscRadioGroup.add({
		 			xtype:'radiogroup'
		 			,ref: 'rg'
		 			,itemId: 'rg'
		 			,id: 'msc_id'
		 			,name: 'msc_id'
		 			,columns: 1
		 			,items: c
		 			,listeners: {
		 				change: function(rg, checked)	{

		 					oseMscAddon.company.setVisible(checked.institution );
							oseMscAddon.company.setDisabled(!checked.institution );

							Ext.each(oseMscAddon.company.findByType('textfield'),function(item,i,all){
								item.setDisabled(!checked.institution );
							});
							
							

		 				}
		 				,added: function(c)	{
		 					checked = c.getValue();
		 					c.fireEvent('change',c,checked);
		 				}

		 			}
		 		});
			 	addonMscRadioGroup.doLayout();
		 	}
		}
		,items:[]
	});

	oseMscAddon.msc = new Ext.form.FieldSet({
		title: 'Membership Type'
		,items: addonMscRadioGroup//addonMscDataView
	})

