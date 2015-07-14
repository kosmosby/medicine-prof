Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();

	//
	// Addon Msc Panel
	//
	oseMscAddon.billinginfo_var2 = new Ext.FormPanel({
		autoScroll: true
		,height: 399
		,defaultType: 'textfield'
		,bodyStyle:'padding:10px'
		,labelWidth: 150
		,defaults: {width: 300}
		,buttons: [{
			text: 'save',
			handler: function(){
				oseMscAddon.billinginfo_var2.getForm().submit({
				    clientValidation: true
				    ,url: 'index.php?option=com_osemsc&controller=member'
				    ,params: {
				        task: 'action', action : 'member.billinginfo_var2.save'
				    }
				    ,success: function(form, action) {
				    	oseMsc.formSuccess(form, action);
				    }
				    ,failure: function(form, action) {
				       oseMsc.formFailure(form, action);
				    }
    			})
			}
		}]

		,items:[{
            fieldLabel: Joomla.JText._('First_Name')
            ,name: 'bill.firstname'
            ,allowBlank:false
        },{
            fieldLabel: Joomla.JText._('Last_Name')
            ,name: 'bill.lastname'
            ,allowBlank:false
        },{
            fieldLabel: Joomla.JText._('Company')
            ,name: 'bill.company'
        }, {
            fieldLabel: Joomla.JText._('Street_Address1')
            ,name: 'bill.addr1'
            ,allowBlank:false
        },{
            fieldLabel: Joomla.JText._('Street_Address2')
            ,name: 'bill.addr2'
        },{
            fieldLabel: Joomla.JText._('City')
            ,name: 'bill.city'
            ,allowBlank:false
        },{
            fieldLabel: Joomla.JText._('State_Province')
            ,name: 'bill.state'
            ,allowBlank:false
        },{
            fieldLabel: Joomla.JText._('Country')
            ,hiddenName: 'bill.country'
            ,id: 'bill_country'
            ,xtype: 'combo'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    //,listClass: 'combo-left'
		    ,lazyInit: false
		    ,mode: 'remote'
		    ,forceSelection: true
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=register',
		            method: 'POST'
	      		})
			  	,baseParams:{task: "getCountry"}
			  	,reader: new Ext.data.JsonReader({
			    	root: 'results'
			    	,totalProperty: 'total'
			    	,idProperty: 'country_id'
			  	},[
			    {name: 'code', type: 'string', mapping: 'country_2_code'},
			    {name: 'cname', type: 'string', mapping: 'country_name'}
			  	])
			  	,listeners:{
			  		load: function(s)	{
			  			oseMscAddon.billinginfo_var2.getForm().load({
							url: 'index.php?option=com_osemsc&controller=member'
							,waitMsg: 'Loading'
							,params:{
								task:'action'
								,action:'member.billinginfo_var2.getItem'
							}
							,callback: function(el,success,response,opt)	{
								var msg = Ext.decode(response.responseText);

								if(!msg.result.country)	{
									oseMscAddon.billinginfo_var2.findById('bill_country').setValue('US');
								}
							}
						})

			  		}
			  	}
			})

		    ,valueField: 'code'
		    ,displayField: 'cname'
        },{
            fieldLabel: Joomla.JText._('Zip_Postal_Code')
            ,name: 'bill.postcode'
            ,allowBlank:false
        }]
	    ,reader:new Ext.data.JsonReader({
		    root: 'result'
		    ,totalProperty: 'total'
		    ,idProperty: 'user_id'
		    ,fields:[
			    {name: 'bill.firstname', type: 'string', mapping: 'firstname'}
			    ,{name: 'bill.lastname', type: 'string', mapping: 'lastname'}
			    ,{name: 'bill.company', type: 'string', mapping: 'company'}
			    ,{name: 'bill.addr1', type: 'string', mapping: 'addr1'}
			    ,{name: 'bill.addr2', type: 'string', mapping: 'addr2'}
			    ,{name: 'bill.city', type: 'string', mapping: 'city'}
			    ,{name: 'bill.state', type: 'string', mapping: 'state'}
			    ,{name: 'bill.country', type: 'string', mapping: 'country'}
			    ,{name: 'bill.postcode', type: 'string', mapping: 'postcode'}
		  	]
	  	})

		,listeners: {
			render: function(p){
				p.findById('bill_country').getStore().load();
			}

		}
	});