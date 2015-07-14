Ext.ns('oseMscAddon','oseMscAddon.mscParams');
	
	oseMscAddon.mscParams.loadParams = {
		url: 'index.php?option=com_osemsc&controller=register'
			,params:{task: "action", action: "register.msc.getMscList_M"}
			,callback: function(el,success,response,opt)	{
				var result = Ext.decode(response.responseText);

			 	el.update('');
			 	var c = new Array();
			 	Ext.each(result.results, function(item,i,all){
			 		
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
			 			,msc_option: item.msc_option
			 		}
			 	})
			 	
				if(c.length > 0)	{
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
								oseMsc.reg.regForm.getForm().findField('msc_option').setValue(checked.msc_option)
			 				}
			 				,added: function(c)	{
			 					checked = c.getValue();
			 					c.fireEvent('change',c,checked);
			 				}
	
			 			}
			 		});
				}	else	{
					addonMscRadioGroup.add({
			 			html: 'No Membership Plan'
			 			,border: false
			 		});
				}
				 	
		 		
		 		//alert(oseMsc.reg.regForm.getForm().findField('msc_option').getValue());
			 	addonMscRadioGroup.doLayout();
			 	
		 	}
	}
	
	var addonMscRadioGroup = new Ext.Panel({
		ref: 'rgPanel'
		,border: false
		,autoLoad: oseMscAddon.mscParams.loadParams
	});

	oseMscAddon.msc = new Ext.form.FieldSet({
		title: 'Membership Type'
		,items: [{
			html: '<a href="javascript:void(0)">show all memberships</a>'
			,id: 'ose-msc-list-showall'
			,border: false
			,bodyStyle: 'padding-top:3px;padding-left:3px'
			,listeners:{
				render: function(p)	{
					p.getEl().on('click',function(){
						Ext.Ajax.request({
							url: 'index.php?option=com_osemsc&controller=register'
							,params: {task: 'removeCartItem', entry_id: 0}
							,callback: function()	{
								addonMscRadioGroup.load(oseMscAddon.mscParams.loadParams);
							}
						})
					})
				}
			}
		},addonMscRadioGroup,{
			xtype: 'hidden'
			,name: 'msc_option'
		}]
	})

