Ext.ns('oseMsc','oseMsc.reg');
Ext.ns('oseMscAddon');
	if(!oseMscAddon.checkboxAmount)	{
		oseMscAddon.checkboxAmount = 0;
	}
	oseMscAddon.terms = function(fp)	{
		this.fp = fp;
		this.getCheckGroup = function()	{
			var cg = new Ext.Panel({
				ref: 'rgPanel'
				,border: false
				,autoLoad:{
					url: 'index.php?option=com_osemsc&controller=register'
					,params:{task:"action", action: "register.terms.getTerms"}
					,scope: this
					,callback: function(el,success,response,opt)	{
						var result = Ext.decode(response.responseText);

					 	if(result.results.length > 0)	{
			  				oseMscAddon.checkboxAmount += result.results.length;
			  			}
					 	cg.update('');
					 	var c = new Array();
					 	if(typeof(Ext.getCmp('membership-type-info')) != 'undefined' && typeof(Ext.getCmp('msc_cart')) == 'undefined') {
					 		var msc_id = Ext.getCmp('membership-type-info').findById('msc-id').getValue();
					 	}
					 	Ext.each(result.results, function(item,i,all){
					 		if(item.msc_id > 0)
					 		{
					 			hide = true;
					 		}else{
					 			hide = false;
					 		}
					 		if(typeof(msc_id) != 'undefined')
							{
					 			oseMscAddon.checkboxAmount = 0;
								if(msc_id == item.msc_id)
								{
									oseMscAddon.checkboxAmount += 1;
									hide = false;
								}else if(item.msc_id == 0)
								{
									oseMscAddon.checkboxAmount+=1;
								}
							}
					 		c[i] = {
					 			xtype: 'panel'
					 			,layout: 'hbox'
					 			,border: false
					 			,defaults: {border: false}
					 			,hidden:hide
					 			,'term_msc_id':item.msc_id
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

											fp.getFooterToolbar().findById('submitBtnOk').setDisabled(oseMscAddon.checkboxAmount >= 1);
										}
						 			}
						 		},{
						 			html: Joomla.JText._('I_agree_to')+'&nbsp;'
						 			,bodyStyle: 'width: 80px; text-align: right; '
						 			,xtype: 'box'
						 		},{
						 			autoEl:{
						 				tag: 'a'
						 				,cls:'osemsc-reg-term'
						 				,href: 'javascript:void(0)'
						 				,subjectid: item.id
						 				,onClick: 'oseMscAddon.termsAssist.openTerm(this)'
						 				,html: item.subject
						 				,bodyStyle: 'left: 10px'
						 			}
						 		}]
					 		}
					 		fp.doLayout()
					 	},this)

					 	cg.add(c);
					 	cg.doLayout();
				 	}
				}
			});

			return cg;
		};
	}
	oseMscAddon.terms.prototype = {
		init: function(form)	{
			var cg = this.getCheckGroup(form);

			return new Ext.form.FieldSet({
				title: Joomla.JText._('Terms_of_Use')
				,id: 'terms-fs'
				,defaultType: 'textfield'
		 		,labelWidth: 130
		 		,defaults: {width: 380,msgTarget : 'side'}
			    ,items: cg 
			});
		}
	}
	oseMscAddon.termsAssist = function()	{
		return {
			openTerm: function(el)	{
				var addonTermsWin = new Ext.Window({
					width: 800
					,height: 500
					,bodyStyle: 'padding: 10px'
					,autoScroll: true
					,autoLoad: {
						url:'index.php?option=com_osemsc&controller=register'
						,params:{'id' : el.getAttribute('subjectid'),task:"action", action: "register.terms.getTerm"}
						,callback: function(el, success, response, opt)	{
							el.update('');
							var info = Ext.decode(response.responseText);
							addonTermsWin.setTitle(info.subject);
							addonTermsWin.update(info.body);
						}
					}
				}).show().alignTo(Ext.getBody(),'t-t');
			}
		}
	}()