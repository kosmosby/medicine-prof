Ext.ns('oseMscAddon');

	oseMscAddon.billinginfo1 = [{
        fieldLabel: 'Company'
        ,name: 'bill.company'
    },{
        fieldLabel: 'Phone'
       , name: 'bill.telephone'
	}]

	oseMscAddon.billinginfo = new Ext.form.FieldSet({
		defaultType: 'textfield'
		,title: 'Billing Information'
 		//,labelWidth: 120
 		,layout: 'form'
 		,defaults: {msgTarget : 'side'}
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
            fieldLabel: 'Country'
            ,hiddenName: 'bill.country'
            ,id: 'bill_country'
            ,xtype: 'combo'
            ,width: 155
		    ,typeAhead: true
		    //,value: 'US'
		    ,triggerAction: 'all'
		    ,lazyRender:false
		    ,lazyInit: false
		    ,listClass: 'combo-left'
		    ,lastQuery: ''
		    ,mode: 'remote'
		    ,forceSelection: true
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=register',
		            method: 'POST'
	      		}),
			  	baseParams:{task: "getCountry"},
			  	reader: new Ext.data.JsonReader({
			    	root: 'results',
			    	totalProperty: 'total'
			    	,idProperty: 'country_id'
			  	},[
			    {name: 'code', type: 'string', mapping: 'country_2_code'},
			    {name: 'subject', type: 'string', mapping: 'country_name'}
			  	])
			  	,autoLoad:{}
			  	,listeners:{
			  		load: function()	{
			  			oseMscAddon.billinginfo.findById('bill_country').setValue('US');
			  		}
			  	}
			})
		    ,valueField: 'code'
		    ,displayField: 'subject'
        },{
            fieldLabel: 'Post Code'
            ,name: 'bill.postcode'
            ,allowBlank: false
        }
        ,oseMscAddon.billinginfo1
        ]
	})