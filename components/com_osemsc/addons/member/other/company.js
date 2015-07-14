Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();


	//
	// Addon Msc Panel
	//
	oseMscAddon.company = new Ext.FormPanel({
		autoScroll: true
		,height: 399
		,defaultType: 'textfield'
		,bodyStyle: 'padding: 10px'
		,labelWidth: 150
		,defaults: {width: 300}
		,buttons: [{
			text: 'save',
			handler: function(){
				oseMscAddon.company.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=member',
				    params: {
				        task: 'action', action : 'member.company.save'
				    },
				    success: function(form, action) {
				    	var msg = action.result;
				    	oseMscAddon.msg.setAlert(msg.title,msg.content);
				    	
				    },
				    failure: function(form, action) {
				        switch (action.failureType) {
				            case Ext.form.Action.CLIENT_INVALID:
				                Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
				                break;
				            case Ext.form.Action.CONNECT_FAILURE:
				                Ext.Msg.alert('Failure', 'Ajax communication failed');
				                break;
				            case Ext.form.Action.SERVER_INVALID:
				               Ext.Msg.alert('Failure', action.result.msg);
				       }
				    }
    			})
			}
		}]
		
		,items:[{
            fieldLabel: 'Company',
            name: 'company.company'
        }, {
            fieldLabel: 'Street Address1',
            name: 'company.addr1'
        },{
            fieldLabel: 'Street Address2',
            name: 'company.addr2'
        },{
            fieldLabel: 'City',
            name: 'company.city'
        },{
            fieldLabel: 'State/Province',
            name: 'company.state'
        },{
             fieldLabel: 'Country'
            ,hiddenName: 'company.country'
            ,id: 'company_country'
            ,xtype: 'combo'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    ,lazyInit: false
		    ,mode: 'remote'
		    ,forceSelection: true
		    ,lastQuery: ''
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
			    {name: 'cname', type: 'string', mapping: 'country_name'}
			  	])
			  	,listeners:{
			  		load: function(s)	{
			  			oseMscAddon.company.getForm().findField('company.country').setValue('US');
			  		}
			  	}
			  	,autoLoad:{}
			})
			
		    ,valueField: 'code'
		    ,displayField: 'cname'
        },{
            fieldLabel: 'Zip/Postal Code',
            name: 'company.postcode'
        },{
            fieldLabel: 'Phone',
            name: 'company.telephone'
        }],
        
	    reader:new Ext.data.JsonReader({   
		    root: 'result',
		    totalProperty: 'total',
		    idProperty: 'company_id',
		    fields:[ 
			    {name: 'company.company', type: 'string', mapping: 'company'},
			    {name: 'company.addr1', type: 'string', mapping: 'addr1'},
			    {name: 'company.addr2', type: 'string', mapping: 'addr2'},
			    {name: 'company.city', type: 'string', mapping: 'city'},
			    {name: 'company.state', type: 'string', mapping: 'state'},
			    {name: 'company.country', type: 'string', mapping: 'country'},
			    {name: 'company.postcode', type: 'string', mapping: 'postcode'},
			    {name: 'company.telephone', type: 'string', mapping: 'telephone'}
		  	]
	  	})
		
		,listeners: {
			render: function(p){
					//oseMscAddon.company.findById('company_country').getStore().load();
			/*		
				p.getForm().load({
					url: 'index.php?option=com_osemsc&controller=member',
					params:{task:'action',action:'member.company.getItem',member_id:oseMemMsc.member_id}
				});
				*/
				p.getForm().load({
					url: 'index.php?option=com_osemsc&controller=member'
					,waitMsg: 'Loading...'
					,params:{
						task:'action'
						,action:'member.company.getItem'
					}
					,success: function(form,action)	{
						var msg = form.result;
						if(msg.length < 1)	{
							oseMscAddon.company.getForm().findField('company.country').setValue('US');
						}
					}
				})
			}
		}
	});