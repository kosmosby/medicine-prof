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
	        			,juser_username : val,addon:'juser'
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

				if(!Ext.isBoolean(unique.success) || unique.success == false)	{
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
				text: Joomla.JText._('SUBSCRIBE')
				,id: 'submitBtnOk'
			}]
			,reader:new Ext.data.JsonReader({
			    root: 'results'
			    ,totalProperty: 'total'
			    ,idProperty: 'id'
			    ,fields:[
				    {name: 'bill.addr1', type: 'string', mapping: 'addr1'}
				    ,{name: 'bill.addr2', type: 'string', mapping: 'addr2'}
				    ,{name: 'juser.firstname', type: 'string', mapping: 'firstname'}
				    ,{name: 'juser.lastname', type: 'string', mapping: 'lastname'}
				    ,{name: 'juser.email', type: 'string', mapping: 'user_email'}
				    ,{name: 'bill.city', type: 'string', mapping: 'city'}
				    ,{name: 'bill_country', type: 'string', mapping: 'country'}
				    ,{name: 'bill_state', type: 'string', mapping: 'state'}
				    ,{name: 'bill.postcode', type: 'string', mapping: 'postcode'}
				    ,{name: 'bill.company', type: 'string', mapping: 'company'}
				    ,{name: 'bill.telephone', type: 'string', mapping: 'telephone'}
				    ,{name: 'msc_id', type: 'string', mapping: 'msc_id'}
				    ,{name: 'msc_option', type: 'string', mapping: 'msc_option'}
				    ,{name: 'ose_currency', type: 'string', mapping: 'ose_currency'}
				    ,{name: 'bill.vat_number', type: 'string', mapping: 'vat_number'}
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
						text: Joomla.JText._('SUBSCRIBE')
						,id: 'submitBtnOk'

					}]
					,reader:new Ext.data.JsonReader({
					    root: 'results'
					    ,totalProperty: 'total'
					    ,idProperty: 'id'
					    ,fields:[
						    {name: 'bill.addr1', type: 'string', mapping: 'addr1'}
						    ,{name: 'bill.addr2', type: 'string', mapping: 'addr2'}
						    ,{name: 'juser.firstname', type: 'string', mapping: 'firstname'}
						    ,{name: 'juser.lastname', type: 'string', mapping: 'lastname'}
						    ,{name: 'juser.email', type: 'string', mapping: 'user_email'}
						    ,{name: 'bill.city', type: 'string', mapping: 'city'}
						    ,{name: 'bill_country', type: 'string', mapping: 'country'}
						    ,{name: 'bill_state', type: 'string', mapping: 'state'}
						    ,{name: 'bill.postcode', type: 'string', mapping: 'postcode'}
						    ,{name: 'bill.company', type: 'string', mapping: 'company'}
						    ,{name: 'bill.telephone', type: 'string', mapping: 'telephone'}
						    ,{name: 'msc_id', type: 'string', mapping: 'msc_id'}
						    ,{name: 'msc_option', type: 'string', mapping: 'msc_option'}
						    ,{name: 'ose_currency', type: 'string', mapping: 'ose_currency'}
						    ,{name: 'bill.vat_number', type: 'string', mapping: 'vat_number'}
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
					}
					,buttons: [{
						text: Joomla.JText._('PROCEED')
						,handler: function()	{
							confirmWin.body.mask(Joomla.JText._('MEMBERSHIP_REGISTRATION_IN_PROGRESS'));
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
			});

			var validated = this.fp.getForm().isValid();

			if (validated == true)
			{
				confirmWin.show().alignTo(Ext.getBody(),'t-t');
			}
			else
			{
				oseMsc.msg.setAlert(Joomla.JText._('Notice'),Joomla.JText._('Please_check_the_notice_in_the_form'));
			}

		}

		,onPaymentSuccess: function(form,action)	{
			var msg = action.result;
			var payment_method = msg.payment_method;

			var onProcessPayment = function(payment_method)	{
				switch(payment_method)	{
					case('paypal'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('paypal_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
	
					break;
	
					case('paypal_cc'):
						//confirmWin.body.unmask();
						if(Ext.value(msg.code,false) !== false)	{
							eval(msg.code);
						}
						oseMsc.formSuccessMB(form,action,function(btn,text){
							if(btn == 'ok')	{
								if(typeof(msg.returnUrl) == 'undefined')	{
									window.location = 'index.php?option=com_osemsc&view=member'
								}	else	{
									window.location = msg.returnUrl
								}
							}
						})
					break;
	
					case('none'):
						//confirmWin.body.unmask();
						oseMsc.formSuccessMB(form,action,function(btn,text){
							if(btn == 'ok')	{
								if(typeof(msg.returnUrl) == 'undefined')	{
									window.location = 'index.php?option=com_osemsc&view=member'
								}	else	{
									window.location = msg.returnUrl
								}
							}
						})
					break;
	
					case('poffline'):
						if(typeof(msg.title) != 'undefined' && typeof(msg.content) != 'undefined'){
							oseMsc.formSuccessMB(form,action,function(btn,text){
								if(btn == 'ok')	{
									if(typeof(msg.returnUrl) == 'undefined')	{
										window.location = 'index.php?option=com_osemsc&view=member'
									}	else	{
										window.location = msg.returnUrl
									}
								}
							})
						}else{
							window.location = msg.returnUrl;
						}
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
	
					case('vpcash_cc'):
					case('vpcash'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('vpcash_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
	
					break;
	
					case('bbva'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('bbva_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
	
					break;
	
					case('gco'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('gco_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
	
					break;
	
					case('payfast'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('payfast_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
	
					break;
	
					case('ewaysh'):
						if(msg.success == false){
	
						}else{
							window.location = msg.html;
						}
					break;
	
					case('2co'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('2co_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
	
					break;
	
					case('clickbank'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('clickbank_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
	
					break;
	
					case('ccavenue'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('ccavenue_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
	
					break;
	
					case('usaepay'):
						
						if(Ext.value(msg.code,false) !== false)	{
							eval(msg.code);
						}
						if(Ext.value(msg.htmlTrack,false) !== false)	{
							eval(msg.htmlTrack);
						}
						
						if(typeof(msg.title) != 'undefined' && typeof(msg.content) != 'undefined'){
							oseMsc.formSuccessMB(form,action,function(btn,text){
								if(btn == 'ok')	{
									if(typeof(msg.returnUrl) == 'undefined')	{
										window.location = 'index.php?option=com_osemsc&view=register'
									}	else	{
										window.location = msg.returnUrl
									}
								}
							})
						}
					break;
	
					case('icepay'):
						if(typeof(msg.title) != 'undefined' && typeof(msg.content) != 'undefined' && msg.success == false){
							oseMsc.formSuccessMB(form,action,function(btn,text){
	
							})
						}else if(typeof(msg.html) != 'undefined') {
							window.location = msg.html
						}
					break;
	
					case('oospay'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('oospay_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
	
					break;
	
					case('ebs'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('ebs_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
	
					break;
	
					case('liqpay'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('liqpay_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
	
					break;
	
					case('virtualmerchant'):
						if(typeof(msg.title) != 'undefined' && typeof(msg.content) != 'undefined'){
							oseMsc.formSuccessMB(form,action,function(btn,text){
								if(btn == 'ok')	{
									if(typeof(msg.returnUrl) == 'undefined')	{
										window.location = 'index.php?option=com_osemsc&view=register'
									}	else	{
										window.location = msg.returnUrl
									}
								}
							})
						}
					break;
	
					case('realex_redirect'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('realex_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
	
					break;
	
					case('realex_remote'):
						
						if(Ext.value(msg.code,false) !== false)	{
							eval(msg.code);
						}
						if(Ext.value(msg.htmlTrack,false) !== false)	{
							eval(msg.htmlTrack);
						}
						
						if(typeof(msg.title) != 'undefined' && typeof(msg.content) != 'undefined'){
							oseMsc.formSuccessMB(form,action,function(btn,text){
								if(btn == 'ok')	{
									if(typeof(msg.returnUrl) == 'undefined')	{
										window.location = 'index.php?option=com_osemsc&view=register'
									}	else	{
										window.location = msg.returnUrl
									}
								}
							})
						}
					break;
	
					case('sisow'):
						if(typeof(msg.title) != 'undefined' && typeof(msg.content) != 'undefined'){
							oseMsc.formSuccessMB(form,action,function(btn,text){
								if(btn == 'ok')	{
									if(typeof(msg.returnUrl) == 'undefined')	{
										window.location = 'index.php?option=com_osemsc&view=register'
									}	else	{
										window.location = 'index.php?option=com_osemsc&view=register';
									}
								}
							})
						}else{
							if(typeof(msg.url) != 'undefined')
							{
								window.location = msg.url;
							}
						}
					break;
					
					case('pagseguro'):
						if(typeof(msg.title) != 'undefined' && typeof(msg.content) != 'undefined'){
							oseMsc.formSuccessMB(form,action,function(btn,text){
								if(btn == 'ok')	{
									if(typeof(msg.returnUrl) == 'undefined')	{
										window.location = 'index.php?option=com_osemsc&view=register'
									}	else	{
										window.location = 'index.php?option=com_osemsc&view=register';
									}
								}
							})
						}else{
							window.location = msg.url;
						}
	
					break;
					
					case('paygate'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('paygate_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
		
					break;
					
					case('quickpay'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('quickpay_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
		
					break;
					
					case('sagepay'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('sagepay_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
		
					break;
					
					case('alipay'):
						Ext.get('ose-payment-callback-form').update(msg.html.form,false,function(){
							Ext.get('alipay_image').dom.click();
						}).setVisibilityMode(2).setVisible(false);
		
					break;
					
					default:
						//confirmWin.body.unmask();
						if(Ext.value(msg.code,false) !== false)	{
							eval(msg.code);
						}
						if(Ext.value(msg.htmlTrack,false) !== false)	{
							eval(msg.htmlTrack);
						}
						oseMsc.formSuccessMB(form,action,function(btn,text){
							if(btn == 'ok')	{
								if(typeof(msg.returnUrl) == 'undefined')	{
									window.location = 'index.php?option=com_osemsc&view=member'
								}	else	{
									window.location = msg.returnUrl
								}
							}
						})
					break;
				}
			}
			
			if((msg.activation != false || typeof(msg.activation) == 'undefined') && payment_method != 'poffline' && payment_method != 'none')
			{
				Ext.Msg.show({
					   msg: msg.content
					   ,width: 600
					   ,buttons: {ok:Joomla.JText._('PROCEED')}
					   ,fn: function(btn,text){
							if(btn == 'ok')	{
								onProcessPayment(payment_method);
							}
						}
					   ,icon: Ext.MessageBox.INFO
				});
			}else{
				onProcessPayment(payment_method);
			}	
		}

		,onPaymentFailure: function(form,action)	{
			var msg = action.result;
			oseMsc.formFailureMB(form,action,function(){
				if( typeof(msg.script) == 'undefined' || msg.script == false )	{
					if(msg.reload == true)	{
						oseMsc.reload();
					}else{
						Ext.getCmp('osemsc-reg-confirmwin').close();
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
				if(Ext.value(item.addon_name,false))	{
					//alert(item.addon_name);

					if(getForm)	{
						var obj = eval("new "+item.addon_name+"(this.fp)")
					}else{
						var obj = eval("new "+item.addon_name+"()")
					}
					if(obj.init() !== false)	{
						posItem.add(obj.init());
					}
					//eval('this.fp.'+position).add(item.addon_name);
				}
			},this);

		}
		,render: function(div){
			this.fp.render(div);
		}
		,loadRegInfo: function()	{
			Ext.Msg.wait(Joomla.JText._('Initializing'),Joomla.JText._('Please_Wait'));
			this.fp.getForm().load({
				url: 'index.php?option=com_osemsc&controller=register'
				,waitMsg: Joomla.JText._('Initializing')
				,params: {task: 'getBillingInfo'}
				,success: function(form,action)	{
					Ext.Msg.hide();
					//alert(form.getValues().toSource());
					var c = form.findField('bill_country');
					if (c != null) {

						if (c.getValue() == '') {
							c.setValue(oseMsc.defaultSelectedCountry.code3);
							var r = c.getStore().getAt(c.getStore().find(c.valueField,oseMsc.defaultSelectedCountry.code3));
							c.fireEvent('select', c, r);
							var s = form.findField('bill_state');
							if (typeof (oseMsc.defaultSelectedState) != 'undefined') {
								if (oseMsc.defaultSelectedCountry.country_id == oseMsc.defaultSelectedState.country_id) {
									s.setValue(oseMsc.defaultSelectedState.code2);
								}
							}

						} else {
							var r = c.getStore().getAt(c.getStore().find(c.valueField,c.getValue()));

							c.fireEvent('select', c, r);

							if (typeof (action.result.data.bill_state) != 'undefined') {
								form.findField('bill_state').setValue(action.result.data.bill_state);
								oseMsc.reg.bill_state = action.result.data.bill_state;
							} else {
								if (typeof (oseMsc.defaultSelectedState) != 'undefined') {
									var s = form.findField('bill_state');
									if (oseMsc.defaultSelectedCountry.country_id == oseMsc.defaultSelectedState.country_id) {
										s.setValue(oseMsc.defaultSelectedState.code2);
									}
								}

							}
						}

						var c = form.findField('msc_id');
						var r = c.getStore().getAt(c.getStore().find('id',c.getValue()));
						c.fireEvent('select', c, r);

						if (typeof (action.result.data.msc_option) != 'undefined') {
							var s = option.getStore();
							form.findField('msc_option').setValue(action.result.data.msc_option);
							option.fireEvent('select', option, s.getAt(s.find('id', option.getValue())));

						}
					} else {
						var c = form.findField('msc_id');
						var r = c.getStore().getAt(c.getStore().find('id',c.getValue()));
						c.fireEvent('select', c, r);
						if (typeof (action.result.data.msc_option) != 'undefined') {
							var s = option.getStore();
							form.findField('msc_option').setValue(action.result.data.msc_option);
							option.fireEvent('select', option, s.getAt(s.find('id', option.getValue())));

						}
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

		,setMscList: function()	{
			this.fp.findById('membership-type-info').getComponent('msc_option').addListener('select',function(c,r,i){
				/*
				if(r.data.title.indexOf('Free')>0)
	    		{
					Ext.get('ose-reg-payment').mask();
					Ext.get('ose-reg-billinginfo').mask();
	    		}
	    		else
	    		{
	    			Ext.get('ose-reg-payment').unmask();
	    			Ext.get('ose-reg-billinginfo').unmask();
	    		}
	    		*/
			},this)
		}
	}