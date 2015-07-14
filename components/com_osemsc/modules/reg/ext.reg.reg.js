Ext.ns('oseMsc','oseMsc.reg','oseMsc.reg.params');
	oseMsc.msg = new Ext.App();

	oseMsc.reg.params = {checked:false};
	Ext.apply(Ext.form.VTypes,{
		uniqueUserName: function(val,field)	{
			var unique = oseMsc.reg.params;
			if(!unique.checked)	{

				Ext.Ajax.request({
	        		url: 'index.php?option=com_osemsc&controller=register'
	        		,params: {
	        			task : 'formValidate'
	        			,juser_username : val
	        		}
	        		,success: function(response, opt)	{
	        			var msg = Ext.decode(response.responseText);

	        			unique =  msg;
	        			unique.checked = true;

	        			oseMsc.reg.params = unique;
	        			return field.validate();
	        		}
	        	});
			}	else	{

				oseMsc.reg.params.checked = false;

				if(!Ext.isBoolean(unique.result))	{
    				return false;
    			}	else	{
    				return true;
    			}

			}
			return true;
		}
		,uniqueUserNameText: 'This username has been registered by other user.'
	})
	/// Params Setting End

	Ext.apply(Ext.form.VTypes,{
		validateField: function(val,field)	{
			var fieldName = field.getName();
			Ext.Ajax.request({
        		url: 'index.php?option=com_osemsc&controller=register'
        		,params: {
        			task: 'formValidate'
        			,field_name: fieldName
        			,field_value: val
        			,addon: field.addonName
        		}
        		,success: function(response, opt)	{
        			var msg = Ext.decode(response.responseText);

        			if(msg.success == true)	{
        				/*
        				field.invalidClass = 'credicartimg';
        				alert(Ext.form.MessageTargets[field.msgTarget].toSource())
        				Ext.form.MessageTargets[field.msgTarget].mark(field,'wa haha');
        				//alert(Ext.form.MessageTargets[field.msgTarget].toSource())

        				field.el.addClass(field.invalidClass);
		                var t = Ext.getDom(field.msgTarget);

		                if(t){
		                    t.innerHTML = 'wa haha';
		                    t.style.display = 'block';
		                }
		                */
		                //field.setActiveError('wa haha')
		                /*
		                Ext.QuickTips.register({
						    target: field.el,
						    title: 'My Tooltip',
						    cls: field.invalidClass,
						    text: 'This tooltip was added in code',
						    width: 100,
						    dismissDelay: 10000 // Hide after 10 seconds hover
						});
        				*/
        				//field.invaldClass = 'credicartimg';
        			}	else	{
        				field.clearInvalid();
        				field.markInvalid(msg.content);
        			}
        		}
        	});

			return true;
		}
		,validateFieldText: 'The field is invalid'
	})

	oseMsc.reg.validateField = function(val,field)	{
		var fieldName = this.getName();
		Ext.Ajax.request({
    		url: 'index.php?option=com_osemsc&controller=register'
    		,params: {
    			task: 'formValidate'
    			,field_name: fieldName
    			,field_value: val
    			,addon: this.addonName
    		}
    		,success: function(response, opt)	{
    			var msg = Ext.decode(response.responseText);

    			if(msg.success == true)	{
    				/*
    				field.invalidClass = 'credicartimg';
    				alert(Ext.form.MessageTargets[field.msgTarget].toSource())
    				Ext.form.MessageTargets[field.msgTarget].mark(field,'wa haha');
    				//alert(Ext.form.MessageTargets[field.msgTarget].toSource())

    				field.el.addClass(field.invalidClass);
	                var t = Ext.getDom(field.msgTarget);

	                if(t){
	                    t.innerHTML = 'wa haha';
	                    t.style.display = 'block';
	                }
	                */
	                //field.setActiveError('wa haha')
	                /*
	                Ext.QuickTips.register({
					    target: field.el,
					    title: 'My Tooltip',
					    cls: field.invalidClass,
					    text: 'This tooltip was added in code',
					    width: 100,
					    dismissDelay: 10000 // Hide after 10 seconds hover
					});
    				*/
    				//field.invaldClass = 'credicartimg';
    			}	else	{
    				this.clearInvalid();
    				this.markInvalid(msg.content);
    			}
    		}
    		,scope: this
    	});

		return true;
	}

	oseMsc.reg.buildForm = function()	{
		var fp = new Ext.FormPanel({
			border: false
			,id: 'payment-form-panel'
			,items:[{
	 			ref: 'regHeader'
	 			,xtype: 'panel'
	 			,border: false

	 		},{
	 			ref: 'regBody'
	 			,xtype: 'panel'
	 			,border: false

	 		},{
	 			ref: 'regFooter'
	 			,xtype: 'panel'
	 			,border: false

	 		}]
			,buttons: [{
				text: 'Register'
				,id: 'submitBtnOk'
			}]
			,reader:new Ext.data.JsonReader({
			    root: 'results'
			    ,totalProperty: 'total'
			    ,idProperty: 'id'
			    ,fields:[
				    {name: 'bill.addr1', type: 'string', mapping: 'addr1'}
				    ,{name: 'juser.firstname', type: 'string', mapping: 'firstname'}
				    ,{name: 'juser.lastname', type: 'string', mapping: 'lastname'}
				    ,{name: 'bill.city', type: 'string', mapping: 'city'}
				    ,{name: 'bill_country', type: 'string', mapping: 'country'}
				    ,{name: 'bill_state', type: 'string', mapping: 'state'}
				    ,{name: 'bill.postcode', type: 'string', mapping: 'postcode'}
				    ,{name: 'bill.company', type: 'string', mapping: 'company'}
				    ,{name: 'bill.telephone', type: 'string', mapping: 'telephone'}
				    ,{name: 'msc_id', type: 'string', mapping: 'msc_id'}
				    ,{name: 'msc_option', type: 'string', mapping: 'msc_option'}
				    ,{name: 'ose_currency', type: 'string', mapping: 'ose_currency'}
			  	]
		  	})
		})

		return fp;
	}

	oseMsc.reg.buildForm2c = function()	{
		// joomla user Fieldset
		return {
			buildForm:function(width)	{
				var fp = new Ext.FormPanel({
					border: false
					,id: 'payment-form-panel'
					,layout: 'hbox'
					,defaults:{bodyStyle: 'padding:5px',width:width/2}
					,items:[{
						ref: 'left'
						,border: false
			 		},{
			 			ref: 'right'
			 			,border: false
			 		}]
					,buttons: [{
						text: 'Subscribe'
						,id: 'submitBtnOk'

					}]
					,reader:new Ext.data.JsonReader({
					    root: 'results'
					    ,totalProperty: 'total'
					    ,idProperty: 'id'
					    ,fields:[
						    {name: 'bill.addr1', type: 'string', mapping: 'addr1'}
						    ,{name: 'juser.firstname', type: 'string', mapping: 'firstname'}
						    ,{name: 'juser.lastname', type: 'string', mapping: 'lastname'}
						    ,{name: 'bill.city', type: 'string', mapping: 'city'}
						    ,{name: 'bill_country', type: 'string', mapping: 'country'}
						    ,{name: 'bill_state', type: 'string', mapping: 'state'}
						    ,{name: 'bill.postcode', type: 'string', mapping: 'postcode'}
						    ,{name: 'bill.company', type: 'string', mapping: 'company'}
						    ,{name: 'bill.telephone', type: 'string', mapping: 'telephone'}
						    ,{name: 'msc_id', type: 'string', mapping: 'msc_id'}
						    ,{name: 'msc_option', type: 'string', mapping: 'msc_option'}
						    ,{name: 'ose_currency', type: 'string', mapping: 'ose_currency'}
						    ,{name: 'total', type: 'string', mapping: 'total'}
					  	]
				  	})
				})

				return fp;
			}
		}
	}()

Ext.ns('oseMsc.payment');

	oseMsc.payment.form = function(fp)	{
		this.fp = fp
	}

	oseMsc.payment.form.prototype = {
		buildWindow: function()	{
			//var fp = this.fp;
			var params = this.fp.getForm().getValues(false);
			params.task = 'generateConfirmDialog';

			var confirmWin = new Ext.Window({
				width: 500
				,title: Joomla.JText._('Confirmation')
				,id: 'osemsc-reg-confirmwin'
				,modal: true
				,items:[{
					border: false
					,height: 450
					,autoLoad: {
						url: 'index.php?option=com_osemsc&controller=register'
						,params: params

						/*
						var params = fp.getForm().getValues(false);

						params.task = 'generateConfirmDialog';
						alert(params.toSource())
						return {
							url: 'index.php?option=com_osemsc&controller=register'
							,params: params
							,method: 'POST'
						}
						*/
					}
					,buttons: [{
						text: Joomla.JText._('PROCEED')
						,handler: function()	{
							confirmWin.body.mask('Membership registration in progress, please wait...');
							this.fp.getForm().submit({
			 					clientValidation: true
			 					//,waitMsg: 'Waiting...'
			 					,url: 'index.php?option=com_osemsc&controller=register'
			 					,params:{task:'save'}
			 					,timeout: 120000
			 					,success: this.onPaymentSuccess
			 					,failure: this.onPaymentFailure
			 				})
						}
						,scope: this
					}]
				}]
			}).show().alignTo(Ext.getBody(),'t-t');

		}

		,onPaymentSuccess: function(form,action)	{
			var msg = action.result;
			var payment_method = msg.payment_method;

			switch(payment_method)	{
				case('paypal'):
					Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
						Ext.get('paypal_image').dom.click();
					}).setVisibilityMode(2).setVisible(false);

				break;

				case('none'):
					//confirmWin.body.unmask();
					oseMsc.formSuccessMB(form,action,function(btn,text){
						if(btn == 'ok')	{
							//window.location = 'index.php?option=com_osemsc&view=member'
							window.location = msg.returnUrl;
						}
					})
				break;

				case('poffline'):
					window.location = msg.link;
				break;

				case('epay'):
					//window.location = msg.html.url;

					Ext.get('ose-payment-callback-form').update(msg.html,false,function(){
						//alert(msg.html);
						Ext.get('epay_button').dom.click();
					}).setVisibilityMode(2).setVisible(false);

				break;

				case('pnw'):
					//window.location = msg.html.url;

					Ext.get('ose-payment-callback-form').update(msg.html,false,function(){
						//alert(msg.html);
						Ext.get('submit_button').dom.click();
					}).setVisibilityMode(2).setVisible(false);

				break;

				case('vpcash'):
					Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
						Ext.get('vpcash_image').dom.click();
					}).setVisibilityMode(2).setVisible(false);

				break;

				default:
					//confirmWin.body.unmask();
					oseMsc.formSuccessMB(form,action,function(btn,text){
						if(btn == 'ok')	{
							window.location = msg.returnUrl
							
						}
					})
				break;
			}
		}

		,onPaymentFailure: function(form,action)	{
			var msg = action.result;
			oseMsc.formFailureMB(form,action,function(){
				if( typeof(msg.script) == 'undefined' || msg.script == false )	{
					if(msg.reload == true)	{
						oseMsc.reload();
					}
				}	else	{
					Ext.getCmp('osemsc-reg-confirmwin').close();
					form.findField(msg.field).focus();
				}

			})
		}

		,onLoadAddons: function(addons,position,getForm)	{

			var posItem = eval("this.fp."+position);

			Ext.each(addons, function(item,i,all)	{
				if(item.addon_name)	{
					//alert(item.addon_name);

					if(getForm)	{
						var obj = eval("new "+item.addon_name+"(this.fp)")
					}else{
						var obj = eval("new "+item.addon_name+"()")
					}
					posItem.add(obj.init())
					//eval('this.fp.'+position).add(item.addon_name);
				}
			},this);

		}
		,render: function(div){
			this.fp.render(div);
		}
		,loadRegInfo: function()	{
			this.fp.getForm().load({
				url: 'index.php?option=com_osemsc&controller=register'
				,waitMsg: 'Initializing...'
				,params: {task: 'getBillingInfo'}
				,success: function(form,action)	{
					var c = form.findField('bill_country');
					if(c.getValue() == ''){
						c.setValue(oseMsc.defaultSelectedCountry.code3)
						var r = c.getStore().getAt(c.getStore().find('code',oseMsc.defaultSelectedCountry.code3))
						c.fireEvent('select',c,r);
					}	else	{
						var r = c.getStore().getAt(c.getStore().find('code',c.getValue()))

						c.fireEvent('select',c,r);

						if(typeof(action.result.data.bill_state) != 'undefined')	{
							form.findField('bill_state').setValue(action.result.data.bill_state)
							oseMsc.reg.bill_state = action.result.data.bill_state;
						}
					}
					
					if(Ext.value(action.result.data.total,'nonfree') == 'free')
		    		{
						Ext.get('ose-reg-payment').mask();
						Ext.get('ose-reg-billinginfo').mask();
						
						if(typeof(Ext.getCmp('ose-reg-payment')) != 'undefined')	{
			    			Ext.each(Ext.getCmp('ose-reg-payment').findByType('textfield'),function(item,i,all){
								item.setDisabled(Ext.value(action.result.data.total,'nonfree') == 'free')
							});
			    		}
		    		}
		    		else
		    		{
		    			Ext.get('ose-reg-payment').unmask();
		    			Ext.get('ose-reg-billinginfo').unmask();
		    		}
						
					
	
					if(typeof(Ext.getCmp('ose-reg-billinginfo')) != 'undefined')	{
		    			Ext.each(Ext.getCmp('ose-reg-billinginfo').findByType('textfield'),function(item,i,all){
							item.setDisabled(Ext.value(action.result.data.total,'nonfree') == 'free')
						});
		    		}
					
				}
				,failure: function()	{
					//alert('f')
				}
			});
		}
		,setClickBtnAction: function(btnId)	{
			//alert(btnId)
			switch(btnId)	{
				case('submitBtnOk'):
					this.fp.getFooterToolbar().findById(btnId).on('click',this.buildWindow,this);
				break;
			}
		}
		,setBtnActive: function(btnId,active)	{
			//alert(btnId)
			switch(btnId)	{
				case('submitBtnOk'):
					this.fp.getFooterToolbar().findById(btnId).setDisabled(!active);
				break;
			}
		}
	}