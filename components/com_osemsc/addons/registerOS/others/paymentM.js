Ext.ns('oseMscAddon','oseMscAddon.paymentParams');

	oseMscAddon.paymentParams.month = {
    	hiddenName: 'creditcard_month'
    	,width: 100
    	,xtype:'combo'
        // create the combo instance
	    ,typeAhead: true
	    ,triggerAction: 'all'
	    ,lazyRender:true
	    ,mode: 'local'
	    ,listClass: 'combo-left'
	    ,store: new Ext.data.ArrayStore({
	        id: 0
	        ,fields: [
	            'myId',
	            'displayText'
	        ]
	        ,data: [
	        	['01', 'January']
	        	,['02', 'February']
	        	,['03', 'March']
	        	,['04', 'April']
	        	,['05', 'May']
	        	,['06', 'June']
	        	,['07', 'July']
	        	,['08', 'August']
	        	,['09', 'September']
	        	,['10', 'October']
	        	,['11', 'November']
	        	,['12', 'December']
	        ]
	    })
	    ,valueField: 'myId'
	    ,displayField: 'displayText'
    };

	oseMscAddon.paymentParams.year = {
    	hiddenName: 'creditcard_year'
    	,width: 100
        // create the combo instance
		,xtype: 'combo'
	    ,typeAhead: true
	    ,triggerAction: 'all'
	    ,lazyRender:true
	    ,mode: 'local'
	    ,listClass: 'combo-left'
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
    }

	oseMscAddon.paymentParams.authorize = new Ext.Panel({
		defaultType: 'textfield'
 		,labelWidth: 150
 		,border:false
 		,layout: 'form'
 		,height: 140
 		,defaults: {width: 300,msgTarget : 'side', border: false}
		,id: 'payment_method.authorize'
		//,autoHeight: true
		,items:[{
            fieldLabel: 'Credit Card'
            ,xtype: 'panel'
            ,layout: 'hbox'
            ,width: 500
            ,border: false
			,items:[{
				hiddenName: 'creditcard_type'
				,width: 300
				,xtype: 'combo'
			    ,typeAhead: true
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
			        	,['discover','Discover Card']
			        ]
			    })
			    ,valueField: 'myId'
			    ,displayField: 'displayText'
				,allowBlank: false
			},{
				xtype: 'box'
				,autoEl:{
					tag: 'img'
					,src: 'components/com_osemsc/assets/images/creditcards.png'
					,html: 'adsfadsf'
				}
			}]

        },{
            fieldLabel: 'Name On Card'
            ,name: 'creditcard_name'
            ,allowBlank: false
        },{
            fieldLabel: 'Credit Card Number'
            ,name: 'creditcard_number'
            ,allowBlank: false
        },{
            fieldLabel: 'Expiration Date'
            //,name: 'creditcard_expirationdate'
            //,format: 'M-Y'
            ,xtype: 'panel'
            ,layout: 'hbox'
            ,items:[
            	oseMscAddon.paymentParams.month

            	,{
            		xtype: 'displayfield'
            		,html: '/'
            	}
            	,oseMscAddon.paymentParams.year

            	,{
            		xtype: 'displayfield'
            		,html: '(Month/Year)'
            	}
            ]
        },{
            fieldLabel: 'Security Code'
            ,name: 'creditcard_cvv'
            ,allowBlank: false
        }]
	});

	oseMscAddon.paymentParams.paypal_cc = new Ext.Panel({
		defaultType: 'textfield'
 		,labelWidth: 150
 		,layout: 'form'
 		,border: false
 		,height: 140
 		,defaults: {width: 300,msgTarget : 'side', border: false}
		,id: 'payment_method.paypal_cc'
		,items:[{
            fieldLabel: 'Credit Card'
            ,xtype: 'panel'
            ,layout: 'hbox'
            ,width: 500
			,items:[{
				hiddenName: 'creditcard_type'
				,width: 300
				,xtype: 'combo'
			    ,typeAhead: true
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
			        	,['discover','Discover Card']
			        ]
			    })
			    ,valueField: 'myId'
			    ,displayField: 'displayText'
				,allowBlank: false
			},{
				xtype: 'box'
				,autoEl:{
					tag: 'img'
					,src: 'components/com_osemsc/assets/images/creditcards.png'
					,html: 'adsfadsf'
				}
			}]

        },{
            fieldLabel: 'Name On Card'
            ,name: 'creditcard_name'
            ,allowBlank: false
        },{
            fieldLabel: 'Credit Card Number'
            ,name: 'creditcard_number'
            ,allowBlank: false
        },{
            fieldLabel: 'Expiration Date'
            ,xtype: 'panel'
            ,layout: 'hbox'
            ,border: false
            ,items:[
            	oseMscAddon.paymentParams.month

            	,{
            		xtype: 'displayfield'
            		,html: '/'

            	}
            	,oseMscAddon.paymentParams.year

            	,{
            		xtype: 'displayfield'
            		,html: '(Month/Year)'
            	}
            ]
        },{
            fieldLabel: 'Security Code'
            ,name: 'creditcard_cvv'
            ,allowBlank: false
        }]
	});

	oseMscAddon.paymentParams.eway = new Ext.Panel({
		defaultType: 'textfield'
 		,labelWidth: 150
 		,layout: 'form'
 		,border: false
 		,height: 140
 		,defaults: {width: 300,msgTarget : 'side', border: false}
		,id: 'payment_method.eway'
		,items:[{
            fieldLabel: 'Credit Card'
            ,xtype: 'panel'
            ,layout: 'hbox'
            ,width: 500
			,items:[{
				hiddenName: 'creditcard_type'
				,width: 300
				,xtype: 'combo'
			    ,typeAhead: true
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
			        	,['discover','Discover Card']
			        ]
			    })
			    ,valueField: 'myId'
			    ,displayField: 'displayText'
				,allowBlank: false
			},{
				xtype: 'box'
				,autoEl:{
					tag: 'img'
					,src: 'components/com_osemsc/assets/images/creditcards.png'
					,html: 'adsfadsf'
				}
			}]

        },{
            fieldLabel: 'Name On Card'
            ,name: 'creditcard_name'
            ,allowBlank: false
        },{
            fieldLabel: 'Credit Card Number'
            ,name: 'creditcard_number'
            ,allowBlank: false
        },{
            fieldLabel: 'Expiration Date'
            ,xtype: 'panel'
            ,layout: 'hbox'
            ,border: false
            ,items:[
            	oseMscAddon.paymentParams.month

            	,{
            		xtype: 'displayfield'
            		,html: '/'

            	}
            	,oseMscAddon.paymentParams.year

            	,{
            		xtype: 'displayfield'
            		,html: '(Month/Year)'
            	}
            ]
        },{
            fieldLabel: 'Security Code'
            ,name: 'creditcard_cvv'
            ,allowBlank: false
        }]
	});

	oseMscAddon.paymentParams.billing = new Ext.Panel({
		defaultType: 'textfield'
 		,labelWidth: 150
 		,border: false
 		,layout: 'form'
 		,defaults: {width: 300,msgTarget : 'side'}
 		,items: [{
            fieldLabel: 'Billing Address'
            ,name: 'bill.addr1'
            ,allowBlank: false
        },{
            fieldLabel: 'City'
            ,name: 'bill.city'
            ,allowBlank: false
        },{
            fieldLabel: 'State'
            ,name: 'bill.state'
            ,allowBlank: false
        },{
            fieldLabel: 'Post Code'
            ,name: 'bill.postcode'
            ,allowBlank: false
        }]
	})

	oseMscAddon.paymentParams.comboStore = new Ext.data.JsonStore({
  		proxy: new Ext.data.HttpProxy({
            url: 'index.php?option=com_osemsc&controller=payment',
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
				oseMscAddon.paymentParams.combo.setValue(r[0].data.code)
			}
		}
	})

	oseMscAddon.paymentParams.combo = new Ext.form.ComboBox({
		store: oseMscAddon.paymentParams.comboStore
		,fieldLabel: 'Payment Method'
        ,hiddenName: 'payment.payment_method'
        ,border:false
        ,xtype: 'combo'
	    ,typeAhead: true
	    ,triggerAction: 'all'
	    ,lazyInit: false
	    ,mode: 'remote'
	    ,lastQuery: ''
	    ,valueField: 'code'
	    ,displayField: 'cname'
	    ,listeners: {
	    	select: function(c)	{
	    		var showSwitch = false;

	    		Ext.each(oseMscAddon.payment.method_form.findByType('textfield'), function(item,i,all){
	    			item.setDisabled(true)
	    		})

	    		switch (c.getValue())	{
	    			case('authorize'):
	    				showSwitch = false;

	    				Ext.each(oseMscAddon.paymentParams.authorize.findByType('textfield'), function(item,i,all){
			    			item.setDisabled(showSwitch)
			    		})

			    		oseMscAddon.payment.method_form.getLayout().setActiveItem('payment_method.authorize')
	    			break;

	    			case('paypal_cc'):
	    				showSwitch = false;

	    				Ext.each(oseMscAddon.paymentParams.paypal_cc.findByType('textfield'), function(item,i,all){
			    			item.setDisabled(showSwitch)
			    		})

			    		oseMscAddon.payment.method_form.getLayout().setActiveItem('payment_method.paypal_cc')
	    			break;

	    			case('eway'):
	    				showSwitch = false;
	    				Ext.each(oseMscAddon.paymentParams.eway.findByType('textfield'), function(item,i,all){
			    			item.setDisabled(showSwitch)
			    		})
			    		oseMscAddon.payment.method_form.getLayout().setActiveItem('payment_method.eway')
	    			break;

	    			default:
	    				oseMscAddon.payment.method_form.getLayout().setActiveItem(0)

	    				//showSwitch = true;
	    			break;
	    		}



	    		oseMscAddon.payment.method_form.doLayout();
	    	}
	    }

    });

	oseMscAddon.payment = new Ext.form.FieldSet({
		title: 'Payment'
		,id: 'ose-reg-payment'
		,labelWidth: 150
		,items: [
			oseMscAddon.paymentParams.combo
		,{
			ref: 'method_form'
			,activeItem: 0
			,layout: 'card'
			,border:false
			,items: [{
				hidden: true
				,id: 'zero'
				,border: false
			}
			,oseMscAddon.paymentParams.authorize
			,oseMscAddon.paymentParams.paypal_cc
			,oseMscAddon.paymentParams.eway
			]

			,listeners: {
				afterrender: function(c)	{

		    		oseMscAddon.paymentParams.combo.fireEvent('select',oseMscAddon.paymentParams.combo);
		    	}
			}
		}
			,oseMscAddon.paymentParams.billing
		]
	})