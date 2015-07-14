Ext.ns('oseMscAddon');
	
	

	//
	// Addon Msc Panel
	//
	oseMscAddon.company = new Ext.form.FieldSet({
		title: 'Company Information',
		defaultType: 'textfield',
 		labelWidth: 150,
 		defaults: {width: 300,msgTarget : 'side'},
		
	    items:[{
	    	xtype: 'hidden'
	    	,name: 'companySaveSwitch'
	    	,value:'1'
	    },{
            fieldLabel: 'Company'
            ,name: 'company.company'
            ,allowBlank: false
        }, {
            fieldLabel: 'Street Address1'
            ,name: 'company.addr1'
            ,allowBlank: false
        },{
            fieldLabel: 'Street Address2'
            ,name: 'company.addr2'
        },{
            fieldLabel: 'City'
            ,name: 'company.city'
            ,allowBlank: false
        },{
            fieldLabel: 'State/Province'
            ,name: 'company.state'
            ,allowBlank: false
        },{
            fieldLabel: 'Country'
            ,hiddenName: 'company.country'
            ,id: 'company_country'
            ,xtype: 'combo'
		    ,typeAhead: true
		    //,value: 'US'
		    ,triggerAction: 'all'
		    ,lazyRender:false
		    ,lazyInit: false
		    ,listClass: 'combo-left'
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
			  			oseMscAddon.company.findById('company_country').setValue('US');
			  		}
			  	}
			})
		    ,valueField: 'code'
		    ,displayField: 'subject'
		    ,listeners: {
		  		render: function (c)	{
		  			//oseMscAddon.company.findField('company.country').setValue('US');
		  			//c.setValue('US');
		  			//c.getStore().load();
		  			
		  		}
		  	}
        },{
            fieldLabel: 'Zip/Postal Code'
            ,name: 'company.postcode'
            ,allowBlank: false
        },{
            fieldLabel: 'Phone',
            name: 'company.telephone'
        }]
		    
		
	})