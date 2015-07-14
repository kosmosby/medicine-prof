Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();

	//
	// Addon Msc Panel
	//
	oseMscAddon.company = new Ext.FormPanel({
		height: 360
		,labelWidth: 150
		,defaults:{	xtype: 'textfield' , width : 300}
		,bodyStyle: 'padding: 10px'
		,buttons: [{
			text: 'save',
			handler: function(){
				oseMscAddon.company.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=member',
				    params: {
				        task: 'action', action : 'member.company.save',
				        member_id: oseMemsMsc.member_id
				    },
				    success: function(form, action) {
				    	oseMsc.formSuccess(form, action);
				    },
				    failure: function(form, action) {
				        oseMsc.formFailure(form, action);
				    }
    			});
			}
		}]

		,labelAlign: 'left'

		,items:[{
            xtype:'hidden',
            name: 'company_id'
        },{
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
            ,xtype: 'combo'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    ,lazyRender:false
		    ,mode: 'remote'
		    ,forceSelection: true
		    ,lastQuery: ''
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc',
		            method: 'POST'
	      		}),
			  	baseParams:{task: "getCountry"},
			  	reader: new Ext.data.JsonReader({
			    	root: 'results',
			    	totalProperty: 'total'
			  	},[
			    {name: 'id', type: 'string', mapping: 'country_2_code'},
			    {name: 'Subject', type: 'string', mapping: 'country_name'}
			  	])
			  	,autoLoad:{}
			  	,listeners: {
			  		load: function(s,r,i)	{
			  			oseMscAddon.company.getForm().findField('company.country').setValue('US')
			  		}
			  	}
			})
		    ,valueField: 'id'
		    ,displayField: 'Subject'
        },{
            fieldLabel: 'Zip/Postal Code',
            name: 'company.postcode'
        },{
            fieldLabel: 'Phone',
            name: 'company.telephone'
        }]
	    ,reader:new Ext.data.JsonReader({
		    root: 'result',
		    totalProperty: 'total',
		    idProperty: 'company_id',
		    fields:[
		    	{name: 'company_id', type: 'string', mapping: 'company_id'},
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
				p.getForm().load({
					url: 'index.php?option=com_osemsc&controller=member',
					params:{task:'action',action:'member.company.getItem',member_id:oseMemsMsc.member_id}
					,waitMsg: 'Loading...'
					,success: function(form,action)	{
					
					}
				});
			}
		}
	});