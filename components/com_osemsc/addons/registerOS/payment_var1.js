Ext.ns('oseMscAddon','oseMscAddon.paymentParams');
	oseMscAddon.payment = function()	{
		this.getMonthList = function()	{
			var monthArray = Date.monthNames.map(function (e,i) { return ['0'+(i+1),e]; });
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
						combo.fireEvent('select',combo);
					}
				}
			})

			var combo = new Ext.form.ComboBox({
				store: comboStore
				,fieldLabel:  Joomla.JText._('Payment_Method')
		        ,hiddenName: 'payment.payment_method'
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,editable: false
			    ,hidden: true
			    ,lazyInit: false
			    ,mode: 'remote'
			    ,lastQuery: ''
			    ,valueField: 'code'
			    ,forceSelection: true
			    ,displayField: 'cname'
			    ,listeners: {
			    	select: function(c)	{

			    	}
			    }
		    });
		    return combo;
		};

		this.getCCForm = function() {
			var ccform = new Ext.Panel({
				defaultType: 'textfield'
		 		,labelWidth: 150
		 		,layout: 'form'
		 		,border: false
		 		,visible: false
		 		,defaults: {width: 280,msgTarget : 'side',border: false}
				,id: 'payment_method.authorize'
				,items:[{
		            fieldLabel: Joomla.JText._('Credit_Card')
		            ,xtype: 'panel'
		            ,layout: 'hbox'
		            ,width: 500
		            ,border: false
					,items:[{
						hiddenName: 'creditcard_type'
						,width: 155
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
						xtype: 'box'
						,autoEl:{
							tag: 'img'
							,src: 'components/com_osemsc/assets/images/creditcards.png'
							,html: 'adsfadsf'
						}
					}]

		        },{
		            fieldLabel: Joomla.JText._('Name_On_Card')
		            ,name: 'creditcard_name'
		            ,allowBlank: false
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
					  	,size: 280
					  	,allowBlank: false
					  	,vtype: 'noSpace'
		            },{
	            		xtype: 'box'
	            		,autoEl: {
	            			tag: 'div'
	            			,class: 'tip-target'
	            			,id: 'trackCallout'
	            			,style:"width: 100px;"
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
			    			item.setDisabled(showSwitch)
			    		})
			    		ccForm.setVisible(true)
			    		//ccForm.getLayout().setActiveItem('payment_method.authorize')
	    			break;

	    			default:
	    				//oseMscAddon.paymentParams.method_form.getLayout().setActiveItem(0)
						Ext.each(ccForm.findByType('textfield'), function(item,i,all){
			    			item.setDisabled(true)
			    		})
						ccForm.setVisible(false)
	    				//showSwitch = true;
	    			break;
	    		}

	    		//combo.doLayout();
	    		combo.ownerCt.doLayout()
			})

			combo.getStore().load();

			return new Ext.form.FieldSet({
				title: Joomla.JText._('Payment')
				,id: 'ose-reg-payment'
				,labelWidth: 150
				//,border: false
				,defaults:{border: false}
				,items: [
					combo
					,ccForm,
					{
						html:'<b>Payment Method: </b> Credit Card'
						,border: false
						,bodyStyle: 'text-align: left'
					}
					//,oseMscAddon.paymentParams.billing
				]
			})
		}
	}
