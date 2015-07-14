Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();

	//
	// Addon Msc Panel
	//
	oseMscAddon.mailing = new Ext.FormPanel({
		autoScroll: true
		,height: 399
		,defaultType: 'textfield'
		,bodyStyle:'padding:10px'
		,labelWidth: 150
		,defaults: {width: 300}
		,buttons: [{
			text: 'save',
			handler: function(){
				oseMscAddon.mailing.getForm().submit({
				    clientValidation: true
				    ,url: 'index.php?option=com_osemsc&controller=member'
				    ,params: {
				        task: 'action', action : 'member.mailing.save'
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
            fieldLabel: Joomla.JText._('Company')
            ,name: 'mailing.company'
        }, {
            fieldLabel: Joomla.JText._('Street_Address1')
            ,name: 'mailing.addr1'
            ,allowBlank:false
        },{
            fieldLabel: Joomla.JText._('Street_Address2')
            ,name: 'mailing.addr2'
        },{
            fieldLabel: Joomla.JText._('City')
            ,name: 'mailing.city'
            ,allowBlank:false
        },{
            fieldLabel: Joomla.JText._('State_Province')
            ,name: 'mailing.state'
            ,allowBlank:false
        },{
            fieldLabel: Joomla.JText._('Country')
            ,hiddenName: 'mailing.country'
            ,id: 'mailing_country'
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
			  			oseMscAddon.mailing.getForm().load({
							url: 'index.php?option=com_osemsc&controller=member'
							,waitMsg: 'Loading'
							,params:{
								task:'action'
								,action:'member.mailing.getItem'
							}
							,callback: function(el,success,response,opt)	{
								var msg = Ext.decode(response.responseText);

								if(!msg.result.country)	{
									oseMscAddon.mailing.findById('mailing_country').setValue('US');
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
            ,name: 'mailing.postcode'
            ,allowBlank:false
        }]
	    ,reader:new Ext.data.JsonReader({
		    root: 'result'
		    ,totalProperty: 'total'
		    ,idProperty: 'user_id'
		    ,fields:[
			     {name: 'mailing.company', type: 'string', mapping: 'company'}
			    ,{name: 'mailing.addr1', type: 'string', mapping: 'addr1'}
			    ,{name: 'mailing.addr2', type: 'string', mapping: 'addr2'}
			    ,{name: 'mailing.city', type: 'string', mapping: 'city'}
			    ,{name: 'mailing.state', type: 'string', mapping: 'state'}
			    ,{name: 'mailing.country', type: 'string', mapping: 'country'}
			    ,{name: 'mailing.postcode', type: 'string', mapping: 'postcode'}
		  	]
	  	})

		,listeners: {
			render: function(p){
				p.findById('mailing_country').getStore().load();
			}

		}
	});