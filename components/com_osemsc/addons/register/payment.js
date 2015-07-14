Ext.ns('oseMscAddon','oseMscAddon.paymentParams');

	oseMscAddon.paymentParams.month = {
    	hiddenName: 'creditcard_month'
    	,xtype:'combo'
    	,editable: false
    	,width: 80
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
    	,width: 70
		,xtype: 'combo'
		,editable: false
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
	        	['2010', '2010']
	        	,['2011', '2011']
	        	,['2012', '2012']
	        	,['2013', '2013']
	        	,['2014', '2014']
	        	,['2015', '2015']
	        	,['2016', '2016']
	        	,['2017', '2017']
	        	,['2018', '2018']
	        	,['2019', '2019']
	        	,['2020', '2020']
	        ]
	    })
	    ,valueField: 'myId'
	    ,displayField: 'displayText'
    }

    oseMscAddon.paymentParams.none = {border:false}

	oseMscAddon.paymentParams.authorize = {
		defaultType: 'textfield'
 		,border:false
 		,layout: 'form'
 		//,height: 130
 		,defaults: {msgTarget : 'side', border: false,width:200}
		,id: 'payment_method.authorize'
		//,autoHeight: true
		,items:[{
            fieldLabel: 'Credit Card'
            ,xtype: 'panel'
            ,layout: 'hbox'
            ,width: 300
            ,border: false
			,items:[{
				hiddenName: 'creditcard_type'
				,width: 120
				,xtype: 'combo'
				,editable: false
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
            		,html: ' / '
            	}
            	,oseMscAddon.paymentParams.year

            	,{
            		xtype: 'displayfield'
            		,html: '(mm/yy)'
            	}
            ]
        },{
            fieldLabel: 'Security Code'
            ,name: 'creditcard_cvv'
            ,allowBlank: false
        }]
	};

	oseMscAddon.paymentParams.paypal_cc = {
		defaultType: 'textfield'
 		//,labelWidth: 150
 		,layout: 'form'
 		//,height: 150
 		,defaults: {msgTarget : 'side', border: false,width:200}
		,id: 'payment_method.paypal_cc'
		,items:[{
            fieldLabel: 'Credit Card'
            ,xtype: 'panel'
            ,layout: 'hbox'
            ,width: 300
			,items:[{
				hiddenName: 'creditcard_type'
				,width: 120
				,xtype: 'combo'
				,editable: false
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
	}

oseMscAddon.paymentParams.eway = {
		defaultType: 'textfield'
 		//,labelWidth: 150
 		,layout: 'form'
 		//,height: 150
 		,defaults: {msgTarget : 'side', border: false,width:200}
		,id: 'payment_method.paypal_cc'
		,items:[{
            fieldLabel: 'Credit Card'
            ,xtype: 'panel'
            ,layout: 'hbox'
            ,width: 300
			,items:[{
				hiddenName: 'creditcard_type'
				,width: 120
				,xtype: 'combo'
				,editable: false
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
	}

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
        ,editable: false
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
	    		switch (c.getValue())	{
	    			case('authorize'):
	    				showSwitch = false;
			    		oseMscAddon.payment.methods.removeAll()
			    		oseMscAddon.payment.methods.add(oseMscAddon.paymentParams.authorize)
	    			break;

	    			case('paypal_cc'):
	    				showSwitch = false;
			    		oseMscAddon.payment.methods.removeAll()
			    		oseMscAddon.payment.methods.add(oseMscAddon.paymentParams.paypal_cc)
	    			break;

	    			case('eway'):
	    				showSwitch = false;
			    		oseMscAddon.payment.methods.removeAll()
			    		oseMscAddon.payment.methods.add(oseMscAddon.paymentParams.eway)
	    			break;

	    			default:
	    				oseMscAddon.payment.methods.removeAll()
	    				oseMscAddon.payment.methods.add(oseMscAddon.paymentParams.none)
	    			break;
	    		}
	    		oseMscAddon.payment.methods.doLayout();
	    		oseMsc.reg.signinForm.doLayout();
	    	}
	    }

    });

	oseMscAddon.payment = new Ext.form.FieldSet({
		title: 'Payment'
		,id: 'ose-reg-payment'
		,items: [
			oseMscAddon.paymentParams.combo
		,{
			ref:'methods'
			,xtype:'panel'
			,border:false
			,defaults:{labelWidth: 130}
			,listeners: {
				afterrender: function()	{
					oseMscAddon.paymentParams.combo.fireEvent('select',oseMscAddon.paymentParams.combo)
				}
			}
		}]
	})