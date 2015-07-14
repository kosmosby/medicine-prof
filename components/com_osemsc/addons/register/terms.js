Ext.ns('oseMsc','oseMsc.reg');	
Ext.ns('oseMscAddon');
	
	//
	// Addon Msc Panel
	//
	
	if(!oseMscAddon.checkboxAmount)	{
		oseMscAddon.checkboxAmount = 0;
	}
	
	oseMsc.reg.openTerm = function(id)	{
		if(!addonTermsWin)	{
			var addonTermsWin = new Ext.Window({
				width: 600
				,height: 500
				,autoScroll: true
				,autoLoad: {
					url:'index.php?option=com_osemsc&controller=register'
					,params:{'id' : id,task:"action", action: "register.terms.getTerm"}
					,callback: function(el, success, response, opt)	{
						el.update('');
						
						var info = Ext.decode(response.responseText);
						addonTermsWin.setTitle(info.subject);
						addonTermsWin.update(info.body);
					}
				}
			})
		}
		
		addonTermsWin.show().alignTo(Ext.getBody(),'t-t');
	}
	
	var addonTermsCheckboxGroup = new Ext.Panel({
		ref: 'rgPanel'
		,border: false
		//,layout: 'form'
		,autoLoad:{
			url: 'index.php?option=com_osemsc&controller=register'
			,params:{task:"action", action: "register.terms.getTerms"}
			,callback: function(el,success,response,opt)	{
				var result = Ext.decode(response.responseText);
			 	
			 	if(result.results.length > 0)	{
	  				oseMscAddon.checkboxAmount += result.results.length;
	  				oseMsc.reg.signinForm.getFooterToolbar().ok.setDisabled(true);
	  			}
	  			
			 	addonTermsCheckboxGroup.update('');
			 	var c = new Array();
			 	Ext.each(result.results, function(item,i,all){
			 		c[i] = {
			 			xtype: 'panel'
			 			,layout: 'hbox'
			 			,border: false
			 			,defaults: {border: false}
			 			,items:[{
				 			xtype:'checkbox'
			
				 			,name: 'agreeToTerms'
				 			,inputValue: 1
				 			,listeners: {
				 				check: function(cb,isCheck)	{
				 					if(isCheck)	{
			 							oseMscAddon.checkboxAmount -= 1;
			 						}	else	{
			 							oseMscAddon.checkboxAmount += 1;
			 						}
			 						oseMsc.reg.signinForm.getFooterToolbar().ok.setDisabled(oseMscAddon.checkboxAmount >= 1);
				 				}
				 			}
				 		},{
				 			html: 'I agree to <a href="javascript:oseMsc.reg.openTerm('+item.id+')">'+ item.subject + '</a>'
				 			,bodyStyle: 'margin-top: 3px'
				 			
				 		}]
			 		}
			 	})
			 	
			 	addonTermsCheckboxGroup.add(c);
			 	addonTermsCheckboxGroup.doLayout();
		 	}
		}
	});
	
	oseMscAddon.terms = new Ext.form.FieldSet({
		title: 'Terms of Use',
		defaultType: 'textfield',
 		//labelWidth: 150,
 		defaults: {width: 300,msgTarget : 'side'},
		
	    items: addonTermsCheckboxGroup //[addonTermsPanel]
	});