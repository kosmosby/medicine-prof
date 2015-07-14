Ext.ns('oseMscAddon','oseMscAddon.paymentParams');
	oseMscAddon.payment = function()	{
		this.getMonthList = function()	{
			var monthArray = Array();
			Ext.each(Date.monthNames,function(item,i,all)	{
				var n = String.leftPad(i+1,2,'0');
				monthArray[i] = [n,item];//item;
			});


			return new Ext.form.ComboBox({
				hiddenName: 'creditcard_month'
		    	,width: 100
		    	,emptyText: 'Select'
		        // create the combo instance
			    ,typeAhead: true
			    ,editable: false
			    ,triggerAction: 'all'
			    ,lazyRender:true
			    ,mode: 'local'
			    ,listClass: 'combo-left'
			    ,forceSelection: true
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
		    	,emptyText: 'Select'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,editable: false
			    ,lazyRender:true
			    ,mode: 'local'
			    ,listClass: 'combo-left'
			    ,forceSelection: true
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

				,listeners: {
					load: function(s,r,i)	{
						combo.setValue(r[0].data.code)
						combo.fireEvent('select',combo,r[0],0);
					}
				}
			})

			var combo = new Ext.form.ComboBox({
				store: comboStore
				//,fieldLabel:  Joomla.JText._('Payment_Method')
		        ,hidden: true
		        ,hiddenName: 'payment.payment_method'
		        //,id: 'payment.payment_method'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,editable: false
			    ,lazyInit: false
			    ,mode: 'remote'
			    ,lastQuery: ''
			    ,valueField: 'code'
			    ,forceSelection: true
			    ,displayField: 'cname'
		    });
		    return combo;
		};

		this.getCCForm = function() {
			var ccform = new Ext.Panel({
				defaultType: 'textfield'
		 		,labelWidth: 130
		 		,layout: 'form'
		 		,border: false
		 		,visible: false
		 		,defaults: {width: 280, msgTarget : 'side',border: false}
				,id: 'payment_method.creditcard'
				,items:[{
		            fieldLabel: Joomla.JText._('Credit_Card')
		            ,xtype: 'panel'
		            ,layout: 'hbox'
		            ,border: false
		            ,style: 'padding-left: 0px'
		            ,items:[{
						hiddenName: 'creditcard_type'
						,forceSelection: true
						,emptyText: 'Select'
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
						,listeners: {
							render: function(c)	{
								//c.setValue('VISA');
							}
						}
					},{
						cls: 'credicardimgamx'
						,border: false
					}]
		        },{
		            fieldLabel: Joomla.JText._('Name_On_Card')
		            ,name: 'creditcard_name'
		            ,allowBlank: false
		            ,vtype: 'noSpace'
		        },{
		            fieldLabel: Joomla.JText._('Credit_Card_Number')
		            ,name: 'creditcard_number'
		            ,allowBlank: false
		            ,vtype: 'alphanum'
		        },{
		            fieldLabel: Joomla.JText._('Expiration_Date')
		            ,width: 300
		            //,name: 'creditcard_expirationdate'
		            //,format: 'M-Y'
		            ,xtype: 'panel'
		            ,layout: 'hbox'
		            ,style: 'padding-left: 0px'
		            ,items:[
		            	this.getMonthList()
		            	,{
		            		xtype: 'displayfield'
		            		,html: '&nbsp;&nbsp;/&nbsp;&nbsp;'
		            	}
		            	,this.getYearList()
		            	,{
		            		xtype: 'displayfield'
		            		,html: '&nbsp;&nbsp;'+Joomla.JText._('Month_Year')
		            	}
		            ]
		        },{
		            fieldLabel: Joomla.JText._('Security_Code')
		            ,xtype: 'compositefield'
		            ,style: 'padding-left: 0px'
		            ,items:[{
						name: 'creditcard_cvv'
					  	,xtype: 'textfield'
					  	,size: 280
					  	,allowBlank: false
					  	,vtype: 'noSpace'
		            },{
	            		xtype: 'box'
	            		,autoEl: {
	            			tag: 'div'
	            			,'class': 'tip-target'
	            			,id: 'trackCallout'
	            			,style:"width: 100px; text-align:center; padding: 3px 0; border:0px dotted #99bbe8; color: #666; cursor: pointer; font:bold 11px tahoma,arial,sans-serif; float:left; text-decoration:underline;"
	            			,html: Joomla.JText._('WHAT_IS_THIS')
	            		}
	            		,listeners:{
	            			render: function(b)	{
	            				Ext.QuickTips.register({
								    target: b.getEl(),
								    anchor: 'right',
								    text: Joomla.JText._('WHAT_IS_THIS_EXP'),
								    width: 250
								});
	            			}
	            		}
	            	}]
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
	    				Ext.each(ccForm.findByType('textfield'), function(item,i,all){
			    			item.setDisabled(showSwitch);
			    		})
			    		ccForm.setVisible(true)
			    		//ccForm.getLayout().setActiveItem('payment_method.authorize')
	    			break;

	    			case('vpcash_cc'):
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
						Ext.each(ccForm.findByType('textfield'), function(item,i,all){
			    			item.setDisabled(true);
			    			if(item.allowBlank==false)
							{
								item.allowBlank=true;
							}
			    		})
						ccForm.setVisible(false)

	    				//showSwitch = true;
	    			break;
	    		}
	    		combo.ownerCt.doLayout();
			});

			combo.getStore().load();

			return new Ext.form.FieldSet({
				title: Joomla.JText._('Payment')
				,id: 'ose-reg-payment'
				,itemId:'ose-reg-payment'
				,labelWidth: 130
				//,border: false
				,defaults:{border: false}
				,items: [
					combo
					,ccForm
				]
			})
		}
	}
