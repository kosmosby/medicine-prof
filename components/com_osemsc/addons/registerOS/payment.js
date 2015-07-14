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
						c.setValue('01');
					}
				}
		    });
		};

		this.getYearList = function()	{
			return new Ext.form.ComboBox({
				hiddenName: 'creditcard_year'
		    	,width: 100
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
			        	['2013', '2013']
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
						c.setValue('2013');
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
				,fieldLabel:  Joomla.JText._('Payment_Method')
		        ,hiddenName: 'payment.payment_method'
		        ,id: 'payment_payment_method'
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
						,xtype: 'combo'
						,width: 150
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
					        	//,['amex','American Express']
					        	//,['discover','Discover Card']
					        ]
					    })
					    ,valueField: 'myId'
					    ,displayField: 'displayText'
						,allowBlank: false
						,listeners: {
							render: function(c)	{
								c.setValue('VISA');
							}
						}
					},{
						cls: 'credicardimg'
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
		            //,name: 'creditcard_expirationdate'
		            //,format: 'M-Y'
		            ,xtype: 'panel'
		            ,layout: 'hbox'
		            ,style: 'padding-left: 0px'
		            ,items:[
		            	this.getMonthList()
		            	,{
		            		xtype: 'displayfield'
		            		,html: '/'
		            	}
		            	,this.getYearList()
		            	,{
		            		xtype: 'displayfield'
		            		,html: Joomla.JText._('Month_Year')
		            	}
		            ]
		        },{
		            fieldLabel: Joomla.JText._('Security_Code')
		            ,xtype: 'compositefield'
		            ,style: 'padding-left: 0px'
		            ,items:[{
						name: 'creditcard_cvv'
					  	,xtype: 'textfield'
					  	,width: 150
					  	,allowBlank: false
					  	,vtype: 'noSpace'
		            },{
	            		xtype: 'box'
	            		,autoEl: {
	            			tag: 'div'
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

			var paymentFieldset = new Ext.form.FieldSet({
				title: Joomla.JText._('Payment')
				,id: 'ose-reg-payment'
				,itemId:'ose-reg-payment'
				,labelWidth: 130
				//,border: false
				,defaults:{border: false}
				,items: [
				         {
				        	 fieldLabel: Joomla.JText._('Payment_Method')
				        	 ,xtype: 'panel'
				             ,layout: 'hbox'
				        	 ,items:[
						           	combo
						           	,{
								        cls: 'paypalimg'
								        ,border: false
								        ,id:'paypallogo'	        	
							       }
						           ]
				         }
					,ccForm
					,{
						html:'<span class="notes">'+oseMsc.payment_method_note+'</span>'
						,border: false
						,bodyStyle: 'text-align: right'
						,id: 'paymetnotes'
					}
				]
			});
			
			combo.on('select',function(c,r,i)	{
				var showSwitch = false;
				if(c.getValue() == 'paypal')
				{
					Ext.each(paymentFieldset.findByType('panel'), function(item,i,all){
						if(item.id == 'paypallogo')
						{
							item.setVisible(true);
						}
		    				
		    		})
				}else{
					Ext.each(paymentFieldset.findByType('panel'), function(item,i,all){
						if(item.id == 'paypallogo')
						{
							item.setVisible(false);
						}
		    				
		    		})
				}
				var free = false;
				if(typeof(Ext.getCmp('membership-type-info')) != 'undefined') {
					var option = Ext.getCmp('membership-type-info').findById('msc-option');
					if(option != null)
					{
						free = (option.getStore().getAt(0).get('isFree')==true)?true:false;
					}	
					
				}
				//alert(c.getValue())
	    		switch (c.getValue())	{
	    			case('paypal_cc'):
	    			case('authorize'):
	    			case('eway'):
	    			case('beanstream'):
	    			//case('pnw'):
	    			case('cp'):
	    			case('usaepay'):
	    			case('realex_remote'):
	    				showSwitch = false;
	    				//Ext.each(ccForm.findByType('textfield'), function(item,i,all){
			    		//	item.setDisabled(showSwitch);
			    		//})

		    			if(typeof(Ext.getCmp('sisow-combo')) != 'undefined')
	    				{
	    					paymentFieldset.remove(Ext.getCmp('sisow-combo'));
	    					paymentFieldset.doLayout();
	    				}
	    				if(typeof(Ext.getCmp('sisow-issuerid')) != 'undefined')
	    				{
	    					paymentFieldset.remove(Ext.getCmp('sisow-issuerid'));
	    					paymentFieldset.doLayout();
	    				}
			    		Ext.each(ccForm.findByType('field'), function(item,i,all){
			    			if(item.getXType() != 'compositefield')	{
			    				item.setDisabled(free);
				    			item.allowBlank=false;
			    			}	else	{
			    				//item.setVisible(true);
			    				Ext.each(item.items.items, function(subitem,i,all){
			    					subitem.setDisabled(free);
					    			subitem.allowBlank=false;
			    				})
			    			}
			    		});
			    		ccForm.setVisible(true);
			    		ccForm.doLayout();
	    			break;

	    			case('vpcash_cc'):
					case('epay'):
					case('pnw'):
						
						if(typeof(Ext.getCmp('sisow-combo')) != 'undefined')
	    				{
	    					paymentFieldset.remove(Ext.getCmp('sisow-combo'));
	    					paymentFieldset.doLayout();
	    				}
	    				if(typeof(Ext.getCmp('sisow-issuerid')) != 'undefined')
	    				{
	    					paymentFieldset.remove(Ext.getCmp('sisow-issuerid'));
	    					paymentFieldset.doLayout();
	    				}
	    				
						Ext.each(ccForm.findByType('field'), function(item,i,all){
			    			if(item.getXType() != 'compositefield')	{
			    				if(item.allowBlank==false)
								{
									item.allowBlank=true;
								}
			    			}	else	{
			    				//item.setVisible(true);
			    				Ext.each(item.items.items, function(subitem,i,all){
					    			if(subitem.allowBlank==false)
									{
										subitem.allowBlank=true;
									}
			    				})
			    			}
			    		});
						/*Ext.each(ccForm.findByType('textfield'), function(item,i,all){
								if(item.allowBlank==false)
								{
									item.allowBlank=true;
								}
				    		})*/
						ccForm.setVisible(false);
					break;
					
					case('sisow'):
						if(typeof(Ext.getCmp('sisow-combo')) == 'undefined' || combo.getStore().getCount() == 1 && combo.startValue != 'sisow')
	    				{		
							var sisowCombo = new Ext.form.ComboBox({
		    					store: new Ext.data.ArrayStore({
		    					    id: 0
		    					    ,fields: [
		    					        'id'
		    					        ,'displayText'
		    					    ]
		    					    ,data: [
		    					    	['', 'iDEAL']
		    					    	,['mistercash', 'BanContact/MisterCash']
		    					    	,['sofort', 'DIRECTebanking']
		    					    	,['webshop', 'WebShop GiftCard']
		    					    	,['podium', 'Podium Cadeaukaart']
		    					    ]
		    					})
		    					,id:'sisow-combo'
		    					,allowBlank:false	
		    					,hiddenName: 'sisow_payment'
		    					,fieldLabel: 'Payment'
		    					,typeAhead: true
		    					,triggerAction: 'all'
		    					,lazyRender:true
		    					,mode: 'local'
		    				    ,emptyText: 'Please Choose'	
		    				    ,forceSelection: true
		    				    ,valueField: 'id'
		    					,displayField: 'displayText'
		    					,listeners: {
		    						select: function(c,r)	{
		    							var sisow_payment = c.getValue();
		    							
		    							if(sisow_payment == '')
		    							{
		    								var bankCombo = new Ext.form.ComboBox({
		    			    					store: new Ext.data.Store({
		    			    						  proxy: new Ext.data.HttpProxy({
		    			    					            url: 'index.php?option=com_osemsc&controller=register',
		    			    					            method: 'POST'
		    			    					      }),
		    			    						  baseParams:{task: "action",action:'register.payment_sisow.getIssuerList'}, 
		    			    						  reader: new Ext.data.JsonReader({   
		    			    						    root: 'results',
		    			    						    totalProperty: 'total'
		    			    						  },[ 
		    			    						    {name: 'id', type: 'string', mapping: 'id'},
		    			    						    {name: 'name', type: 'string', mapping: 'name'}
		    			    						  ]),
		    			    						  listeners:{
		    			    					        beforeload:function(){
		    			    					            Ext.Msg.wait(Joomla.JText._('Loading'),Joomla.JText._('Please_Wait'));   
		    			    					        }
		    			    						  }
		    			    					})
		    			    					,id:'sisow-issuerid'
		    			    					,allowBlank:false	
		    			    					,fieldLabel: 'Banks'
		    			    			        ,hiddenName: 'sisow_issuerid'
		    			       				    ,typeAhead: true
		    			    				    ,triggerAction: 'all'
		    			    				    ,editable: false
		    			    				    ,lazyInit: false
		    			    				    ,mode: 'remote'
		    			    				    ,lastQuery: ''
		    			    				    ,valueField: 'id'
		    			    				    ,emptyText: 'Please Choose'	
		    			    				    ,forceSelection: true
		    			    				    ,displayField: 'name'
		    			    			    });
		    								
		    		    					bankCombo.getStore().load({
		    								    callback: function(records, options, success){
		    								    	Ext.Msg.hide();
		    								      }
		    								});
		    								paymentFieldset.add(bankCombo);
		    			    				paymentFieldset.doLayout();
		    							}else{
		    								paymentFieldset.remove(Ext.getCmp('sisow-issuerid'));
		    		    					paymentFieldset.doLayout();
		    							}	
		    						}
		    					}	
		    			    });
	    					paymentFieldset.add(sisowCombo);
		    				paymentFieldset.doLayout();
	    				}
					
						Ext.each(ccForm.findByType('field'), function(item,i,all){

			    			if(item.getXType() != 'compositefield')	{
			    				item.setDisabled(true);
				    			item.allowBlank=true;
			    			}	else	{
			    				Ext.each(item.items.items, function(subitem,i,all){
	
			    					subitem.setDisabled(true);
					    			subitem.allowBlank=true;
			    				})
			    			}
						});
						ccForm.setVisible(false);
					break;
						
	    			default:
	    				
	    				if(typeof(Ext.getCmp('sisow-combo')) != 'undefined')
	    				{
	    					paymentFieldset.remove(Ext.getCmp('sisow-combo'));
	    					paymentFieldset.doLayout();
	    				}
	    				if(typeof(Ext.getCmp('sisow-issuerid')) != 'undefined')
	    				{
	    					paymentFieldset.remove(Ext.getCmp('sisow-issuerid'));
	    					paymentFieldset.doLayout();
	    				}
	    				
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
						ccForm.setVisible(false);
	    				//oseMscAddon.paymentParams.method_form.getLayout().setActiveItem(0)
						/*Ext.each(ccForm.findByType('textfield'), function(item,i,all){
			    			item.setDisabled(true);
			    			if(item.allowBlank==false)
							{
								item.allowBlank=true;
							}
			    		})
						ccForm.setVisible(false)*/

	    				//showSwitch = true;
	    			break;
	    		}

	    		combo.ownerCt.doLayout()
			})

			combo.getStore().load();

			return paymentFieldset;
		}
	}