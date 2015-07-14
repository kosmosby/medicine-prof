Ext.ns('oseMscAddon','oseMscAddon.paymentParams');
	oseMscAddon.payment = function(fp)	{
		this.fp = fp;
		this.getMonthList = function()	{
			var monthArray = Array();
			Ext.each(Date.monthNames,function(item,i,all)	{
				var n = String.leftPad(i+1,2,'0');
				monthArray[i] = [n,item];//item;
			});


			return new Ext.form.ComboBox({
				hiddenName: 'creditcard_month'
		    	,width: 100
		        // create the combo instance
			    ,typeAhead: true
			    ,editable: false
			    ,triggerAction: 'all'
			    ,lazyRender:true
			    ,mode: 'local'
			    //,listClass: 'combo-left'
			    ,forceSelection: true
			    ,emptyText: 'Month'
			    ,msgTarget: 'side'
			    ,validator: function(val)	{
			    	//alert(Date.getMonthNumber('year'));
			    	if(/[0-9]/i.test(Date.getMonthNumber(val)) === true)	{
			    		return true;
			    	}	else	{
			    		return 'Please select month';
			    	}

			    }
			    ,store: new Ext.data.ArrayStore({
			        id: 0
			        ,fields: [
			            'myId',
			            'displayText'
			        ]
			        ,data: monthArray
			    })
			    ,valueField: 'myId'
			    ,displayField: 'displayText'
			    ,listeners: {
					render: function(c)	{
						//c.setValue('01');
					}
				}
		    });
		};

		this.getYearList = function()	{
			return new Ext.form.ComboBox({
				hiddenName: 'creditcard_year'
		    	,width: 100
		        // create the combo instance
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,editable: false
			    ,lazyRender:true
			    ,mode: 'local'
			    ,listClass: 'combo-left'
			    ,forceSelection: true
			    ,emptyText: 'Year'
			    ,validator: function(val)	{
			    	if(/[0-9]/i.test(val) === true)	{
			    		return true;
			    	}	else	{
			    		return 'Please select year';
			    	}

			    }
			    ,store: new Ext.data.ArrayStore({
			        id: 0
			        ,fields: [
			            'myId',
			            'displayText'
			        ]
			        ,data: [
			        	['2011', '2011']
			        	,['2012', '2012']
			        	,['2013', '2013']
			        	,['2014', '2014']
			        	,['2015', '2015']
			        	,['2016', '2016']
			        	,['2017', '2017']
			        	,['2018', '2018']
			        	,['2019', '2019']
			        	,['2020', '2020']
			        	,['2021', '2021']
			        ]
			    })
			    ,valueField: 'myId'
			    ,displayField: 'displayText'
			    ,listeners: {
					render: function(c)	{
						//c.setValue('2011');
					}
				}
		    });
		};

		this.getMethodCombo = function()	{
			var comboStore = new Ext.data.JsonStore({
		  		proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=register',
		            method: 'POST'
		  		})
			  	,baseParams:{task: "action",action: 'register.payment.getMethod'}
		    	,root: 'results'
		    	,totalProperty: 'total'
		  		,fields:[
				    {name: 'code', type: 'string', mapping: 'value'},
				    {name: 'cname', type: 'string', mapping: 'text'}
			  	]
				,autoLoad:{}
				,listeners: {
					load: function(s,r,i)	{
						//combo.setValue(r[0].data.code)
						//combo.fireEvent('select',combo,r[0],0);
					}
				}
			})

			var combo = new Ext.form.ComboBox({
				store: comboStore
				,fieldLabel:  Joomla.JText._('Payment_Method')
		        ,hiddenName: 'payment.payment_method'
		        //,id: 'payment.payment_method'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,editable: false
			    ,lazyInit: false
			    ,mode: 'local'
			    ,valueField: 'code'
			    ,forceSelection: true
			    ,displayField: 'cname'
			    ,emptyText: 'Please Choose'
			    ,allowBlank: false
			    ,msgTarget: 'side'
			    ,labelWidth: 150
			    ,width: 150
			    /*,validator: function(val)	{
			    	if(val == '')	{
			    		return 'Please select one payment method';
			    	}	else	{
			    		return true;
			    	}

			    }*/
			});

		    return combo
		};

		this.getCCForm = function() {
			var ccform = new Ext.Panel({
				defaultType: 'textfield'
		 		,labelWidth: 150
		 		,layout: 'form'
		 		,border: false
		 		,visible: false
		 		,defaults: {width: 280,msgTarget : 'side',border: false}
				,id: 'payment_method.creditcard'
				,items:[{
		            fieldLabel: Joomla.JText._('Credit_Card')
		            ,xtype: 'compositefield'
		            ,msgTarget: 'side'
		            //,layout: 'hbox'
		            //,width: 350
		            ,border: false
					,items:[{
						hiddenName: 'creditcard_type'
						,width: 170
						,forceSelection: true
						,xtype: 'combo'
					    ,typeAhead: true
					    ,editable: false
					    ,triggerAction: 'all'
					    ,lazyRender:true
					    ,mode: 'local'
					    ,store: new Ext.data.ArrayStore({
					        id: 0
					        ,fields: [
					            'myId',
					            'displayText'
					        ]
					        ,data: [
					        	['VISA', 'Visa']
					        	,['MC', 'MasterCard']
					        	,['amex','American Express']
					        	//,['discover','Discover Card']
					        ]
					    })
					    ,valueField: 'myId'
					    ,displayField: 'displayText'
						,allowBlank: false
						,emptyText: 'Please Choose'
						,listeners: {
							render: function(c)	{
								//c.setValue('VISA');
							}
							,select: function(c,r,i)	{
								var nextS = c.nextSibling();
								nextS.removeClass(nextS.cls);
								nextS.addClass('creditcardimg-'+r.get('myId'));
							}
						}
					},{
						//cls: 'creditcardimg-VISA'
						border: false
					}]

		        },{
		            fieldLabel: Joomla.JText._('Credit_Card_Number')
		            ,name: 'creditcard_number'
		            ,allowBlank: false
		            ,vtype: 'alphanum'
		        },{
		            fieldLabel: Joomla.JText._('Expiration_Date')
		            //,name: 'creditcard_expirationdate'
		            //,format: 'M-Y'
		            ,xtype: 'compositefield'
		            //,layout: 'hbox'
		            ,items:[
		            	this.getMonthList()
		            	,this.getYearList()
		            ]
		        }]
			});
			return ccform;
		}
	}

	oseMscAddon.payment.prototype = {
		init: function()	{
			var combo = this.getMethodCombo();
			var ccForm = this.getCCForm();

			combo.on('select',function(c,r,i)	{
				var showSwitch = false;

				//alert(c.getValue())
	    		switch (c.getValue())	{
	    			case('paypal_cc'):
	    			case('authorize'):
	    			case('eway'):
	    			case('beanstream'):
	    			case('pnw'):
	    				showSwitch = false;
	    				/*Ext.each(ccForm.findByType('textfield'), function(item,i,all){
			    			item.setDisabled(showSwitch);
			    		})
			    		ccForm.setVisible(true)*/
			    		Ext.each(ccForm.findByType('field'), function(item,i,all){
			    			if(item.getXType() != 'compositefield')	{
			    				item.setDisabled(showSwitch);
				    			item.allowBlank=false;
			    			}	else	{
			    				//item.setVisible(true);
			    				Ext.each(item.items.items, function(subitem,i,all){
			    					subitem.setDisabled(showSwitch);
					    			subitem.allowBlank=false;
			    				})
			    			}
			    		});
			    		ccForm.setVisible(true);
			    		ccForm.doLayout();
	    			break;
					case('epay'):
						Ext.each(ccForm.findByType('textfield'), function(item,i,all){
								if(item.allowBlank==false)
								{
									item.allowBlank=true;
								}
				    		})
						ccForm.setVisible(false);
					break;
	    			default:
	    				
	    				//oseMscAddon.paymentParams.method_form.getLayout().setActiveItem(0)
						Ext.each(ccForm.findByType('field'), function(item,i,all){
			    			
			    			if(item.getXType() != 'compositefield')	{
			    				item.setDisabled(true);
				    			item.allowBlank=true;
			    			}	else	{
			    				//item.setVisible(false);
			    				//alert(item.items.items.toSource());
			    				Ext.each(item.items.items, function(subitem,i,all){
			    				
			    					subitem.setDisabled(true);
					    			subitem.allowBlank=true;
			    				})
			    			}
			    		});
						ccForm.setVisible(false)

	    				//showSwitch = true;
	    			break;
	    		}
				this.fp.doLayout();
	    		combo.ownerCt.doLayout()
			},this)

			combo.getStore().load();
			Ext.each(ccForm.findByType('textfield'), function(item,i,all){
    			if(item.getXType != 'compositefield')	{
    				item.setDisabled(true);
	    			if(item.allowBlank==false)	{
						item.allowBlank=true;
					}
    			}	else	{
    				Ext.each(item.items, function(subitem,i,all){
    					subitem.setDisabled(true);
		    			if(subitem.allowBlank==false)	{
							subitem.allowBlank=true;
						}
    				})
    			}

    		})
			ccForm.setVisible(false)

			return new Ext.form.FieldSet({
				title: Joomla.JText._('Payment')
				,id: 'ose-reg-payment'
				,itemId:'ose-reg-payment'
				,labelWidth: 150
				//,border: false
				,defaults:{border: false}
				,items: [{
			    	items: [combo,{
						cls: 'credicardimgamx'
						,border: false
					}]
					,xtype: 'compositefield'
					,msgTarget: 'side'
					,width: 280
			    }
					,ccForm
				]
			})
		}
	}