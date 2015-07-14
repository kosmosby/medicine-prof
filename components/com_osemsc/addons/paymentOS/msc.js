Ext.ns('oseMscAddon');
	var addonMscRadioGroup = new Ext.Panel({
		title: ''
		,ref: 'rgPanel'
		,id: 'msc_option'
		,border: false
		//,layout: 'form'
		,bodyStyle:'margin-bottom:3px; border: 0px;'
		,autoLoad:{
			url: 'index.php?option=com_osemsc&controller=payment'
			,params:{task: "getPaymentMsc"}
			,callback: function(el,success,response,opt)	{
				var result = Ext.decode(response.responseText);
			 	addonMscRadioGroup.update('');
			 	var c = new Array();
			 	Ext.each(result.results, function(item,i,all){
			 		if(item.has_trial)	{
			 			var radioBoxLabel = item.standard_price+' for every '+item.standard_recurrence+' ('+item.trial_price+' in first '+ item.trial_recurrence+')'
			 		}	else	{
			 			var radioBoxLabel = item.standard_price+' for every '+item.standard_recurrence
			 		}

			 		c[i] = {
			 			items:[{
			 				html: item.title
			 				,xtype: 'displayfield'
			 			},{
			 				xtype:'radio'
				 			,boxLabel: radioBoxLabel
				 			,name: 'msc_id'
				 			,id: 'msc_id'
				 			,inputValue: item.id
				 			,institution: (item.cs_mode == 'institution')?true:false
				 			,checked: (i == 0) ? true: false
			 			}]
			 		}
			 	})

			 	addonMscRadioGroup.add(c);

			 	addonMscRadioGroup.doLayout();
		 	}
		}
		,items:[]
	});

	oseMscAddon.msc = new Ext.form.FieldSet({
		title: 'Membership Type'
		,items: addonMscRadioGroup//addonMscDataView
	})


