Ext.ns('oseMsc','oseMsc.reg');	
Ext.ns('oseMscAddon');
	
	//
	// Addon Msc Panel
	//
	
	if(!oseMscAddon.checkboxAmount)	{
		oseMscAddon.checkboxAmount = 0;
	}
	
	
	
	oseMsc.reg.openFaithStatement = function(id)	{
		if(!addonFaithWin)	{
			var addonFaithWin = new Ext.Window({
				width: 600
				,height: 500
				,autoScroll: true
				,autoLoad: {
					url:'index.php?option=com_osemsc&controller=register'
					,params:{'id' : id,task:"action", action: "register.faith.getFaith"}
					,callback: function(el, success, response, opt)	{
						el.update('');
						
						var info = Ext.decode(response.responseText);
						addonFaithWin.setTitle(info.subject);
						addonFaithWin.update(info.body);
					}
				}
				
			})
		}
		
		addonFaithWin.show(this).alignTo(Ext.getBody(),'t-t');
		
	}

	
	var addonFaithCheckboxGroup = new Ext.Panel({
		ref: 'rgPanel'
		,border: false
		,autoLoad:{
			url: 'index.php?option=com_osemsc&controller=register'
			,params:{task:"action", action: "register.faith.getFaiths"}
			,callback: function(el,success,response,opt)	{
				var result = Ext.decode(response.responseText);
			 	
			 	if(result.results.length > 0)	{
	  				oseMscAddon.checkboxAmount += result.results.length;
	  				oseMsc.reg.regForm.buttons[0].setDisabled(true);
	  			}
	  			
			 	addonFaithCheckboxGroup.update('');
			 	var c = new Array();
			 	Ext.each(result.results, function(item,i,all){
			 		c[i] = {
			 			xtype: 'panel'
			 			,layout: 'hbox'
			 			,border: false
			 			,defaults: {border: false}
			 			,items: [{
			 				xtype:'checkbox'
				 			,name: 'agreeToFaith'
				 			,inputValue: 1
				 			,listeners: {
				 				check: function(cb,isCheck)	{
				 					if(isCheck)	{
			 							oseMscAddon.checkboxAmount -= 1;
			 						}	else	{
			 							oseMscAddon.checkboxAmount += 1;
			 						}
			 						oseMsc.reg.regForm.buttons[0].setDisabled(oseMscAddon.checkboxAmount >= 1);
				 				}
				 			}
			 			},{
			 				//xtype : 'displayfield'
			 				html: 'I agree to <a href="javascript:oseMsc.reg.openFaithStatement('+item.id+')">'+ item.subject + '</a>'
			 				,bodyStyle: 'margin-top: 3px'
			 			}]
			 			
			 		}
			 	})
			 	
		 		
		 		addonFaithCheckboxGroup.add(c);
		 		
			 	addonFaithCheckboxGroup.doLayout();
		 	}
		}
	});
	
	oseMscAddon.faith = new Ext.form.FieldSet({
		title: 'Statement of Faith'
		,defaultType: 'textfield'
 		,defaults: {width: 300,msgTarget : 'side'}
		
	    ,items: addonFaithCheckboxGroup //[addonFaithPanel]
	});