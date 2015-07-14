Ext.ns('oseMscAddon','oseMscAddon.paymentParams');
	oseMscAddon.paymentParams.createForm = function(){
		this.openWin = function(form)	{
			this.win = new Ext.Window({
				title: Joomla.JText._('Payment_Parameter_Setting')
				,items: form
				,width: 800
				,modal: true
			})

			this.win.show().alignTo(Ext.getBody(),'t-t');
		}

		this.mfReader = function()	{
			return new Ext.data.JsonReader({
			    root: 'result'
			    ,totalProperty: 'total'
			    ,fields:[
			    	{name: 'payment_recurrence_mode', type: 'string', mapping: 'recurrence_mode'}
				    ,{name: 'id', type: 'string', mapping: 'id'}
				    ,{name: 'payment_start_date', type: 'string', mapping: 'start_date'}
				    ,{name: 'payment_expired_date', type: 'string', mapping: 'expired_date'}
				    ,{name: 'payment_has_trial', type: 'int', mapping: 'has_trial'}
				    ,{name: 'payment_a1', type: 'float', mapping: 'a1'}
				    ,{name: 'payment_p1', type: 'float', mapping: 'p1'}
				    ,{name: 'payment_t1', type: 'string', mapping: 't1'}
				    ,{name: 'payment_a3', type: 'float', mapping: 'a3'}
				    ,{name: 'payment_p3', type: 'float', mapping: 'p3'}
				    ,{name: 'payment_t3', type: 'string', mapping: 't3'}
					,{name: 'payment_optionname', type: 'string', mapping: 'optionname'}
					,{name: 'payment_rename', type: 'string', mapping: 'rename'}
				    ,{name: 'payment_eternal', type: 'int', mapping: 'eternal'}
				    ,{name: 'payment_isFree', type: 'int', mapping: 'isFree'}
				]
		  	})
		}

		this.reader = function()	{
			return new Ext.data.JsonReader({
			    root: 'result'
			    ,totalProperty: 'total'
			    ,fields:[
			    	{name: 'payment_recurrence_mode', type: 'string', mapping: 'payment_recurrence_mode'}

				    ,{name: 'payment_start_date', type: 'string', mapping: 'payment_start_date'}
				    ,{name: 'payment_expired_date', type: 'string', mapping: 'payment_expired_date'}
				    ,{name: 'payment_has_trial', type: 'int', mapping: 'payment_has_trial'}
				    ,{name: 'payment_a1', type: 'float', mapping: 'payment_a1'}
				    ,{name: 'payment_p1', type: 'float', mapping: 'payment_p1'}
				    ,{name: 'payment_t1', type: 'string', mapping: 'payment_t1'}
				    ,{name: 'payment_a3', type: 'float', mapping: 'payment_a3'}
				    ,{name: 'payment_p3', type: 'float', mapping: 'payment_p3'}
				    ,{name: 'payment_t3', type: 'string', mapping: 'payment_t3'}
					//,{name: 'payment_optionname', type: 'string', mapping: 'payment_optionname'}
				    ,{name: 'payment_eternal', type: 'int', mapping: 'payment_eternal'}
				    ,{name: 'payment_isFree', type: 'int', mapping: 'payment_isFree'}
				    ,{name: 'payment_optionname', type: 'string', mapping: 'payment_optionname'}
				]
		  	})
		}

	}

	oseMscAddon.paymentParams.createForm.prototype = {
		init: function(isNew)	{
			var tpl = this.getTpl();

			var addonPaymentHidden = new Ext.form.FieldSet({
				hidden: true
				,defaultType: 'hidden'
				,items: [
					{name: 'payment_trial'}
					,{name: 'payment_start_date'}
					,{name: 'payment_expired_date'}
					,{name: 'payment_has_trial'}
					,{name: 'payment_a1'}
					,{name: 'payment_p1'}
					,{name: 'payment_t1'}
					,{name: 'payment_a3'}
					,{name: 'payment_p3'}
					,{name: 'payment_t3'}
					,{name: 'payment_eternal'}
					,{name: 'id'}
					,{name: 'payment_recurrence_mode'}
				]
			});
			
			var addonPaymentTitleFieldset = new Ext.form.FieldSet({
				title:Joomla.JText._('Basic_Information')
				,defaults:{border: false}
				,items:[
				/*{
					fieldLabel: 'Customize Title'
					,xtype: 'checkbox'
					,width: 300
					,name: 'payment_rename'
					,inputValue: 1
					,id: 'payment.rename'
					,listeners: {
						check: function(c,chekced)	{
							if(chekced == true)	{
								c.nextSibling().setDisabled(!checked);
							}
						}
					}
				},*/
				{
					fieldLabel: Joomla.JText._('Title_Optional')
					,xtype: 'textfield'
					,width: 300
					,name: 'payment_optionname'
					,id: 'payment.optionname'
					//,disabled: true
				}]
			});
			
			var addonPaymentFreeFieldset = new Ext.form.FieldSet({
				title:Joomla.JText._('Is_this_a_free_membership_plan')
				,defaults:{border: false}
				,items:[{
					fieldLabel: Joomla.JText._('Free_Membership')
					,xtype: 'checkbox'
					,inputValue: 1
					,name: 'payment_isFree'
					,id: 'payment.isFree'
					,listeners: {

					}
				}]
			});


			var addonPaymentRecurrenceFieldset = new Ext.form.FieldSet({
				title:Joomla.JText._('Is_this_a_membership_type_with_fixed_start_and_expire_date')
				,defaults:{border: false}
				,items:[{
					layout: 'hbox'
					,defaultType: 'button'
					,defaults: {width: 300,scope:this}
					,items:[{
						text: Joomla.JText._('Recurrence_Mode')
						,handler: function()	{
							this.newRecurrenceForm(addonPaymentFormPanel);
						}

					},{
						text: Joomla.JText._('Fixed_Date_Mode')
						,handler: function()	{
							this.newFixForm(addonPaymentFormPanel)
						}
					}]
				}]
			});

			var reader =  this.mfReader();

			var addonPaymentFormPanel = new Ext.FormPanel({
				labelWidth: 300
				,height: 500
				//,defaults: {labelWidth: 200}
				,buttons: [{
					text: Joomla.JText._('save')
					,handler: function(){
						addonPaymentFormPanel.getForm().submit({
						    clientValidation: true
						    ,url: 'index.php?option=com_osemsc&controller=membership'
						    ,params: {
						        task: 'action', action : 'panel.payment.save',msc_id: oseMsc.msc_id
						    }
						    ,success: function(form, action) {
						    	oseMsc.formSuccess(form, action);

						    	oseMsc.refreshGrid(oseMscAddon.payment);
						    	oseMscAddon.payment.getSelectionModel().clearSelections();
	            				addonPaymentFormPanel.ownerCt.close()
						    }
						    ,failure: oseMsc.formFailureMB
						    ,scope:this
		    			})
					}
					,scope:this
				}]
				,autoScroll: true
				,bodyStyle: 'padding: 10px;padding-left: 20px;padding-right: 20px'
				,items:[
					addonPaymentTitleFieldset
					,addonPaymentFreeFieldset
					,addonPaymentRecurrenceFieldset
					,addonPaymentHidden
					,{
						itemId: 'tmpl'
						,xtype: 'panel'
						,border: false
						,listeners: {
							refresh: function(p,tpl,data)	{
								tpl.overwrite(p.body, data);
							}
							,render: function(p)	{
								if(!isNew)	{
									var r = {};
									r.total = 1;

									r.result = oseMscAddon.paymentParams.gridSelectedItem;

									var record = p.ownerCt.reader.readRecords(r)
									//alert(oseMscAddon.paymentParams.gridSelectedItem.toSource())
									p.fireEvent('refresh',p,tpl,record.records[0].data)
								}

								addonPaymentFormPanel.getForm().findField('payment_isFree').addListener('check',function(cb,checked)	{
									var r = {};
									r.total = 1;
									r.result = oseMscAddon.paymentParams.gridSelectedItem;

									var record = addonPaymentFormPanel.reader.readRecords(r);

									p.fireEvent('refresh',p,tpl,addonPaymentFormPanel.getForm().getValues())
								},this)


							}
						}
					}
				]
				,reader:reader
				,listeners:{
					render: function(f)	{

						if(!isNew)	{
							var r = {};
							r.total = 1;
							//oseMscAddon.paymentParams.gridSelectedItem.isFree = 1;
							r.result = oseMscAddon.paymentParams.gridSelectedItem;

							var record = f.reader.readRecords(r)

							f.getForm().setValues(record.records[0].data)

						}
					}
				}
			});

			return addonPaymentFormPanel;
		}

		,newRecurrenceForm: function(mf)	{
			var addonPaymentTipFieldset = new Ext.form.FieldSet({
				title: Joomla.JText._('Hints')
				,defaults: {border:false}
				,items:[{
					html: '1. '+Joomla.JText._('If_it_is_a_free_membership_the_price_must_be_0_only_If_you_have_to_change_the_price_please_unchecked_the_free_membership_option_first')
				}]
			});

			var addonPaymentManualFieldset = new Ext.form.FieldSet({
				title: Joomla.JText._('Is_this_a_lifetime_membership_plan_infinite')
				,defaults: {width: 150}
				//,labelWidth: 225
				,itemId:'M'
				,items:[{
					fieldLabel:Joomla.JText._('Lifetime_Membership_Intinite')
					,xtype: 'checkbox'
					,inputValue: 1
					,name: 'payment_eternal'
					,listeners: {
						check: function(cb,checked)	{
							var cv = mf.getForm().findField('payment_isFree').getValue();

							Ext.each(addonPaymentBasicFieldset.findByType('textfield'),function(item,i,all)	{
								if(item.getName() == 'payment_a3')	{

								}	else	{
									item.setDisabled(checked);
								}

							});
							Ext.each(addonPaymentAutoFieldset.findByType('checkbox'),function(item,i,all)	{

								if(checked)	{
									item.setValue(checked?0:1);
									//item.fireEvent('check',item,!checked);

									item.setDisabled(checked);
								}	else	{
									item.setDisabled(checked || cv);
									if(cv )	{
										item.fireEvent('check',item,checked);
									}
								}
							})

						}
					}
				}]
			});

			var addonPaymentBasicFieldset = new Ext.form.FieldSet({
				title: Joomla.JText._('Please_enter_the_standard_price_for_this_membership_option')
				,defaultType:'textfield'
				//,labelWidth: 225
				,defaults: {width: 150}
				,items:[{
					fieldLabel: Joomla.JText._('Membership_Price')
					,name: 'payment_a3'
					,itemId: 'payment.a3'
				},{
					fieldLabel: Joomla.JText._('Unit_of_Billing_Period')
					,xtype: 'combo'
					,id: 'standard_combo'
			        ,mode: 'local'
			        ,hiddenName: 'payment_t3'
				    ,typeAhead: true
				    ,triggerAction: 'all'
					,lazyRender:true
				    ,store: new Ext.data.ArrayStore({
				        id: 'limitStore'
				        ,fields: ['limitValue','limitText']
				        ,data: [['day', Joomla.JText._('Day_s')], ['week', Joomla.JText._('Week_s')], ['month', Joomla.JText._('Month_s')], ['year', Joomla.JText._('Year_s')]]

				    })
				    ,valueField: 'limitValue'
				    ,displayField: 'limitText'
				},{
					fieldLabel: Joomla.JText._('Length_of_Billing_Periods')
					,name: 'payment_p3'
				}]
			});

			var addonPaymentAutoFieldset = new Ext.form.FieldSet({
				title: Joomla.JText._('The_following_is_for_automatic_renewal_recurring_membership_only_it_is_not_for_manual_renewal_membership_plans')
				,defaults: {width: 150}
				//,labelWidth: 225
				,itemId:'A'
				,items:[{
					xtype:'checkbox'
					,fieldLabel: Joomla.JText._('Is_there_a_trial_period_for_this_membership_option')
					,inputValue: 1
					,name: 'payment_has_trial'
					,listeners: {
						check: function(checkbox,checked){
							checkbox.ownerCt.trialFieldSet.setVisible(checked)
						}
						,render: function(c)	{
							var cv = mf.getForm().findField('payment_isFree').getValue();
							if(cv)	{
								c.setValue(0);
							}
						}
					}
				},{
					ref: 'trialFieldSet'
					,hidden: true
					,defaultType: 'textfield'
					,width: 500
					,defaults: {width: 150}
					,border: false
					,layout: 'form'
					,items:[{
						fieldLabel: Joomla.JText._('Initial_charge_for_trial_period')
						,name: 'payment_a1'
						,value:''
					},{
						fieldLabel: Joomla.JText._('Billing_Period')
						,xtype: 'combo'
				        ,mode: 'local'
				        ,hiddenName: 'payment_t1'
					    ,typeAhead: true
					    ,triggerAction: 'all'
		    			,lazyRender:true
					    ,store: new Ext.data.ArrayStore({
					        id: 'limitStore_a'
					        ,fields: ['limitValue','limitText']
					    	,data: [['day', Joomla.JText._('Day_s')], ['week', Joomla.JText._('Week_s')], ['month', Joomla.JText._('Month_s')], ['year', Joomla.JText._('Year_s')]]
					    })
					    ,valueField: 'limitValue'
					    ,displayField: 'limitText'
					},{
						fieldLabel: Joomla.JText._('Billing_cycles')
						,name: 'payment_p1'
					}
					]
				}]
			});

			var reader = this.reader();
			var records = mf.getForm().getValues();
			var form = new Ext.FormPanel({
				items: [
					addonPaymentTipFieldset
					,addonPaymentManualFieldset
					,addonPaymentBasicFieldset
					,addonPaymentAutoFieldset
				]
				,labelWidth: 150
				,bodyStyle: 'padding:8px'
				,border: false
				,reader: reader
				,buttons: [{
					text: Joomla.JText._('OK')
					,handler: function()	{
						mf.getForm().findField('payment_recurrence_mode').setValue('period');
						var r = form.getForm().getValues();
						r.payment_eternal = (form.getForm().findField('payment_eternal').getValue())?1:0;
						r.payment_has_trial = (form.getForm().findField('payment_has_trial').getValue())?1:0;

						mf.getForm().setValues(r);
						var tpl = this.getTpl();
						this.showInfo(mf,tpl,mf.getForm().getValues());
						this.win.close();
					}
					,scope: this
				}]
				,listeners: {
					render: function(f)	{
						if(Ext.value(records.payment_isFree,0) == 1)	{
							records.payment_a3 = 0;
							f.getForm().findField('payment_a3').setReadOnly(true);
							f.getForm().findField('payment_has_trial').setDisabled(true);
						}
						f.getForm().setValues(records)
					}
				}
			})

			var win = this.openWin(form);

		}

		,newFixForm: function(mf)	{
			var addonPaymentTipFieldset = new Ext.form.FieldSet({
				title: Joomla.JText._('Hints')
				,defaults: {border:false}
				,items:[{
					html: '1. '+Joomla.JText._('If_it_is_a_free_membership_the_price_must_be_0_only_If_you_have_to_change_the_price_please_unchecked_the_free_membership_option_first')
				}]
			});

			var addonPaymentBasicFieldset = new Ext.form.FieldSet({
				title: Joomla.JText._('Please_enter_the_standard_price_for_this_membership_option')
				,defaultType:'textfield'
				,defaults: {width: 150}
				,items:[{
					fieldLabel: Joomla.JText._('Membership_Price')
					,name: 'payment_a3'
					,itemId: 'payment.a3'
				},{
					fieldLabel: Joomla.JText._('Billing_Period')
					,xtype: 'hidden'
			        ,value: 'week'
			        ,name: 'payment_p3'

				},{
					fieldLabel: Joomla.JText._('Total_Billing_Cycles')
					,xtype: 'hidden'
			        ,value: '0'
			        ,name: 'payment_t3'
				}]
			});

			var addonPaymentDateFieldset = new Ext.form.FieldSet({
				title: Joomla.JText._('Please_set_billing_period_for_this_membership_option')
				,defaultType:'textfield'
				,defaults: {width: 390}
				,items:[{
					fieldLabel:Joomla.JText._('Start_Date')
					,xtype:'compositefield'
					,items:[{
						xtype:'datefield'
						,name:'start_date'
						,format: 'Y-m-d'
						,allowBlank: false

					},{
						xtype: 'timefield'
						,name: 'start_time'
						,width:100
						,allowBlank: false
						,format: 'H:i'
						,hidden:  true
						,listeners: {
							render: function(c)	{
								c.setValue(c.getStore().getAt(0).get('field1'))
							}
						}
					}]
				},{
					fieldLabel:Joomla.JText._('Expired_Date')
					,xtype:'compositefield'
					,emptyText: 'dfdf'
					,items:[{
						xtype:'datefield'
						,format: 'Y-m-d'
						,allowBlank: false
						,name:'exp_date'
					},{
						xtype: 'timefield'
						,name: 'exp_time'
						,hidden:  true
						,format: 'H:i'
						,allowBlank: false
						,width:100
						,listeners: {
							render: function(c)	{
								c.setValue(c.getStore().getAt(c.getStore().getTotalCount()-1).get('field1'))
							}
						}
					}]
				}]
			});

			var reader = this.reader();
			var records = mf.getForm().getValues();
			var form = new Ext.FormPanel({
				items: [
					addonPaymentTipFieldset
					,addonPaymentBasicFieldset
					,addonPaymentDateFieldset
				]
				,reader: reader
				,labelWidth: 150
				,bodyStyle: 'padding:8px'
				,border: false
				,buttons: [{
					text: Joomla.JText._('OK')
					,handler: function()	{
						mf.getForm().findField('payment_recurrence_mode').setValue('fixed');
						var record = form.getForm().getValues();

						record.payment_start_date = record.start_date+' '+record.start_time+':00';
						record.payment_expired_date = record.exp_date+' '+record.exp_time+':00';


						if(form.getForm().isValid())	{
							mf.getForm().setValues(record)

							var tpl = this.getTpl();

							this.showInfo(mf,tpl,mf.getForm().getValues());

							this.win.close();
						}

					}
					,scope: this
				},{
					text: Joomla.JText._('Cancel')
					,handler: function()	{
						this.win.close();
					}
					,scope: this
				}]
				,listeners: {
					render: function(f)	{
						if(Ext.value(records.payment_isFree,0) == 1)	{
							records.payment_a3 = 0;
							f.getForm().findField('payment_a3').setReadOnly(true);
						}

						if(Ext.value(records.payment_start_date,false)){
							//alert(records.payment_start_date.replace('+',' '))
							//var t = records.payment_start_date.replace('+',' ')+':00';
							//var dt = new Date(t);
							//alert(dt.format('Y-m-d'));
							records.start_date = records.payment_start_date.replace('+00:00:00','');//dt.format('Y-m-d');
						}

						if(Ext.value(records.payment_expired_date,false)){
							//var dt = new Date(records.payment_expired_date.replace('+',' '));
							records.exp_date = records.payment_expired_date.replace('+23:45:00','');
						}
						f.getForm().setValues(records)
					}
				}
			})

			this.openWin(form);
		}

		,getTpl: function()	{
			var tpl = new Ext.XTemplate(
				'<table>',
				'<tpl if="this.isRecurrence(payment_recurrence_mode)">',
					'<tpl if="this.isLifetime(payment_eternal)">',
						'<tr><td>'+Joomla.JText._('Free_Membership')+':</td>',
						'<td> {[values.payment_isFree == 1? "'+Joomla.JText._('ose_Yes')+'" : "'+Joomla.JText._('ose_No')+'"]}</td></tr>',
						'<tr><td>'+Joomla.JText._('Standard_Price')+':</td>',
						'<td> {payment_a3}</td></tr>',
						'<tr><td>'+Joomla.JText._('Billing_Cycle')+':</td>',
						'<td>'+Joomla.JText._('Life_Time')+'</td></tr>',
					'</tpl>',

					'<tpl if="this.isLifetime(payment_eternal) == false">',
						'<tpl if="this.isTrial(payment_has_trial)">',
							'<tr><td>'+Joomla.JText._('Trial_Price')+':</td>',
							'<td> {payment_a1}</td></tr>',
							'<tr><td>'+Joomla.JText._('Trial_Length')+':</td>',
							'<td> {payment_p1} {payment_t1}</td></tr>',
							'<tr><td>'+Joomla.JText._('Standard_Price')+':</td>',
							'<td> {payment_a3}</td></tr>',
							'<tr><td>'+Joomla.JText._('Billing_Cycle')+':</td>',
							'<td> {payment_p3} {payment_t3}</td></tr>',
						'</tpl>',
						'<tpl if="this.isTrial(payment_has_trial) == false">',
							'<tr><td>'+Joomla.JText._('Free_Membership')+':</td>',
							'<td> {[values.payment_isFree == 1? "Yes" : "No"]}</td></tr>',
							'<tr><td>'+Joomla.JText._('Standard_Price')+':</td>',
							'<td> {payment_a3}</td></tr>',
							'<tr><td>'+Joomla.JText._('Billing_Cycle')+':</td>',
							'<td> {payment_p3} {payment_t3}</td></tr>',
						'</tpl>',
					'</tpl>',
				'</tpl>',

				'<tpl if="this.isRecurrence(payment_recurrence_mode) == false">',
						'<tr><td>Free Membership?:</td>',
						'<td> {[values.payment_isFree == 1? "'+Joomla.JText._('ose_Yes')+'" : "'+Joomla.JText._('ose_No')+'"]}</td></tr>',
						'<tr><td>'+Joomla.JText._('Standard_Price')+':</td>',
						'<td> {payment_a3}</td></tr>',
						'<tr><td>'+Joomla.JText._('Billing_Cycle')+':</td>',
						'<td> {[values.payment_start_date.replace("+"," ") + " - " + values.payment_expired_date.toString().replace("+"," ").replace(",","")]}</td></tr>',
				'</tpl>',
			    '</table>',
			    {
			        compiled: true,
			        disableFormats: true,
			        // member functions:
			        isRecurrence: function(val){
			            return val == 'period';
			        },
			        isLifetime: function(val){
			            return val == 1;
			        },
			        isFree: function(val){
			            return val == 1;
			        },
			        isTrial: function(val){
			            return val == 1;
			        }
			    }
			);

			return tpl;
			//var p = mf.tpl;
			//p.fireEvent('refresh',p,tpl,mf.getForm().getValues())
		}

		,showInfo: function(mf,tpl,data)	{
			mf.getComponent('tmpl').fireEvent('refresh',mf.getComponent('tmpl'),tpl,data)
		}
	}

	oseMscAddon.paymentParams.openCWin = function(isNew)	{
		var addonPaymentFormCreate = new oseMscAddon.paymentParams.createForm();

		if(!addonPaymentWin)	{
			var addonPaymentWin = new Ext.Window({
				width: 900
				,autoHeight: true
				,modal:true
				,items:[
					addonPaymentFormCreate.init(isNew)
				]
			})
		}

		addonPaymentWin.show().alignTo(Ext.getBody(),'t-t')
	}

  	oseMscAddon.paymentParams.gridSm = new Ext.grid.CheckboxSelectionModel({
		singleSelect:false
		,listeners: {
			selectionchange: function(sm)	{
				oseMscAddon.payment.getTopToolbar().editBtn.setDisabled(sm.getCount()<1)
				oseMscAddon.payment.getTopToolbar().removeBtn.setDisabled(sm.getCount()<1)
			}
			,rowselect: function(sm,i,r)	{
				oseMscAddon.paymentParams.gridSelectedItem = r.data;
			}
		}
	});

  	oseMscAddon.paymentParams.gridStore = new Ext.data.Store({
	    proxy: new Ext.data.HttpProxy({
            url: 'index.php?option=com_osemsc&controller=membership',
            method: 'POST'
      	})
	  	,baseParams:{task: "action",action: 'panel.payment.getOptions',msc_id: oseMscs.msc_id}
	  	,reader: new Ext.data.JsonReader({
		    root: 'results',
		    totalProperty: 'total'
	  	},[
		    {name: 'id', type: 'string', mapping: 'id'}
		    ,{name: 'idurl', type: 'string', mapping: 'idurl'}
		    ,{name: 'a3', type: 'string', mapping: 'a3'}
		    ,{name: 'p3', type: 'string', mapping: 'p3'}
		    ,{name: 't3', type: 'string', mapping: 't3'}
		    ,{name: 'p1', type: 'string', mapping: 'p1'}
		    ,{name: 't1', type: 'string', mapping: 't1'}
		    ,{name: 'a1', type: 'string', mapping: 'a1'}
		    ,{name: 'eternal', type: 'string', mapping: 'eternal'}
		    ,{name: 'has_trial', type: 'string', mapping: 'has_trial'}
		    ,{name: 'isFree', type: 'string', mapping: 'isFree'}
		    ,{name: 'recurrence_mode', type: 'string', mapping: 'recurrence_mode'}
		    ,{name: 'ordering', type: 'string', mapping: 'ordering'}
		    ,{name: 'optionname', type: 'string', mapping: 'optionname'}

		    ,{name: 'start_date', type: 'string', mapping: 'start_date'}
		    ,{name: 'expired_date', type: 'string', mapping: 'expired_date'}

	  	])
	  	,sort: 'ordering'
	  	,autoLoad:{}
	})

  	oseMscAddon.payment = new Ext.grid.GridPanel({
  		store: oseMscAddon.paymentParams.gridStore
  		,cm: new Ext.grid.ColumnModel({
	        defaults: {
	            sortable: false
	        },
	        columns: [
	        	oseMscAddon.paymentParams.gridSm
	        	,new Ext.grid.RowNumberer({header:'#'})
	            ,{id: 'idurl', header: Joomla.JText._('ID'),  hidden:false, dataIndex: 'idurl', width: 100}
	            ,{
			    	id: 'option', header: Joomla.JText._('Option'), xtype: 'templatecolumn', dataIndex: 'p3,t3',
			    	tpl: new Ext.Template(
			    		'<p>{optionname}</p>'
			    	)
			    }
			    ,{id: 'price', header: Joomla.JText._('Price'),  hidden:false, dataIndex: 'a3', width: 150}
	            ,{
	            	xtype: 'actioncolumn'
	                ,width: 150
	                ,align: 'center'
	                ,header: Joomla.JText._('Ordering')
	                ,items: [{
	                    getClass: function(v, meta, rec,ri,ci,s)	{
                            if (rec.get('ordering') == 1) {
                            	return 'no-arrow-col';
                            }	else	{
                            	return 'up-arrow-col';
                            }
	                	}
	                    ,tooltip: Joomla.JText._('Click_to_change')
	                    ,handler: function(grid, rowIndex, colIndex) {
	                    	var s = grid.getStore().getAt(rowIndex)

	                    	Ext.Ajax.request({
	                    		url: 'index.php?option=com_osemsc'
	                    		,params:{
	                    			controller:'memberships'
	                    			,task: 'action'
	                    			,action:'panel.payment.up'
	                    			,id:s.get('id')
	                    			,msc_id: oseMsc.msc_id
	                    		}
	                    		,callback: function(el,success,response,opt)	{
	                    			grid.getStore().reload();
	                    			grid.getView().refresh();
	                    		}
	                    	})
	                    }
	                },{
	                    getClass: function(v, meta, rec,ri,ci,s)	{
                            if (rec.get('ordering') == s.getTotalCount()) {
                            	return 'no-arrow-col';
                            }	else	{
                            	return 'down-arrow-col';
                            }

	                	}
	                    ,tooltip: Joomla.JText._('Click_to_change')
	                    ,handler: function(grid, rowIndex, colIndex) {
	                    	var s = grid.getStore().getAt(rowIndex)

	                    	Ext.Ajax.request({
	                    		url: 'index.php?option=com_osemsc'
	                    		,params:{
	                    			controller:'memberships'
	                    			,task: 'action'
	                    			,action:'panel.payment.down'
	                    			,id:s.get('id')
	                    			,msc_id: oseMsc.msc_id
	                    		}
	                    		,callback: function(el,success,response,opt)	{
	                    			grid.getStore().reload();
	                    			grid.getView().refresh();
	                    		}
	                    	})
	                    }
	                }]
                }
	        ]
	    })
  		,sm: oseMscAddon.paymentParams.gridSm
  		,bbar:new Ext.PagingToolbar({
    		pageSize: 20,
    		store: oseMscAddon.paymentParams.gridStore,
    		displayInfo: true,
    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
			emptyMsg: Joomla.JText._("No_topics_to_display")

	    })
  		,tbar: new Ext.Toolbar({
		    items: [{
	    		ref:'addBtn'
	            ,iconCls: 'icon-user-add'
	            ,text: Joomla.JText._('Add')
	            ,handler: function()	{
	            	oseMscAddon.paymentParams.openCWin(true)//.createDelegate(oseMscAddon.paymentParams)
	            }
	        },{
	        	ref: 'editBtn'
	            ,iconCls: 'icon-user-edit'
	            ,text: Joomla.JText._('Edit')
	            ,disabled: true
	            ,handler: function()	{
	            	oseMscAddon.paymentParams.openCWin(false)//.createDelegate(oseMscAddon.paymentParams)
	            }
	        },{
	        	ref: 'removeBtn'
	            ,iconCls: 'icon-user-delete'
	            ,text: Joomla.JText._('Remove')
	            ,disabled: true
	            ,handler: function()	{
	            	var sids =oseMscAddon.paymentParams.gridSm.getSelections();

		    		var ids = new Array();
		    		for(i=0;i < sids.length; i++)	{
		    			var r = sids[i];
		    			ids[i] = r.id;
		    		}
	               Ext.Ajax.request({
	            		url: 'index.php?option=com_osemsc&controller=membership'
	            		,params:{
	            			task: 'action',action: 'panel.payment.remove'
	            			,'ids[]': ids
	            			,msc_id: oseMscs.msc_id
	            		}
	            		,success: function(response, opt)	{
	            			var msg = Ext.decode(response.responseText);
	            			oseMsc.ajaxSuccess(response, opt)
	            			if(msg.success)	{
	            				//oseMscAddon.paymentParams.storeLoad();
	            				oseMscAddon.payment.getStore().reload();
	            				oseMscAddon.payment.getView().Refresh();
	            			}
	            		}
	            	})

	            }
	        },{
	        	ref: 'removeAllBtn'
	            ,iconCls: 'icon-user-delete'
	            ,text: Joomla.JText._('Reset')
	            ,handler: function()	{
	            	Ext.Ajax.request({
	            		url: 'index.php?option=com_osemsc&controller=membership'
	            		,params:{
	            			task: 'action',action: 'panel.payment.removeAll'
	            			,msc_id: oseMscs.msc_id
	            		}
	            		,success: function(response, opt)	{
	            			var msg = Ext.decode(response.responseText);
	            			oseMsc.ajaxSuccess(response, opt)
	            			if(msg.success)	{
	            				//oseMscAddon.paymentParams.storeLoad();
	            				oseMscAddon.payment.getStore().reload();
	            				oseMscAddon.payment.getView().Refresh();
	            			}
	            		}
	            	})

	            }
	        }]
		})
  		//,viewConfig: {forceFit: true}
  		,autoExpandColumn: 'option'
		,height: 500
  	})

