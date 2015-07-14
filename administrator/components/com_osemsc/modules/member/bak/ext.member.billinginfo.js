Ext.ns('oseMemMsc');
	//
	// Addon Msc Panel
	//
	oseMemMsc.billinginfo = new Ext.FormPanel({
		bodyStyle: 'padding: 10px'
		,height: 399
		,border: false
		,buttons: [{
			text: 'save',
			handler: function(){
				oseMemMsc.billinginfo.getForm().submit({
				    clientValidation: true
				    ,url: 'index.php?option=com_osemsc&controller=members'
				    ,params: {
				        task: 'action'
				        ,action : 'member.billinginfo.save'
				        ,member_id: oseMemsMsc.member_id
				    }
				    ,success: function(form, action) {
				    	oseMsc.formSuccess(form, action);
				    }
				    ,failure: function(form, action) {
				        oseMsc.formFailure(form, action);
				    }
    			});
			}
		}]
		,defaultType: 'textfield'
		,items:[{
            fieldLabel: 'First Name',
            name: 'bill.firstname',
            allowBlank:false,
        },{
            fieldLabel: 'Last Name',
            name: 'bill.lastname',
        },{
            fieldLabel: 'Company',
            name: 'bill.company',
        }, {
            fieldLabel: 'Street Address1',
            name: 'bill.addr1',
        },{
            fieldLabel: 'Street Address2',
            name: 'bill.addr2',
        },{
            fieldLabel: 'City',
            name: 'bill.city',
        },{
            fieldLabel: 'State/Province',
            name: 'bill.state',
        },{
            fieldLabel: 'Country'
            ,name: 'bill.country'
            ,xtype: 'combo'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    ,lazyRender:false
		    ,mode: 'remote'
		    ,forceSelection: true
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
			})
		    ,valueField: 'id'
		    ,displayField: 'Subject'
		    ,listeners: {
		    	render: function(c)	{
		    		c.setValue('US')
		    	}
		    }
        },{
            fieldLabel: 'Zip/Postal Code',
            name: 'bill.postcode',
        }]
	    ,reader:new Ext.data.JsonReader({
		    root: 'result',
		    totalProperty: 'total',
		    idProperty: 'user_id',
		    fields:[
			    {name: 'bill.firstname', type: 'string', mapping: 'firstname'},
			    {name: 'bill.lastname', type: 'string', mapping: 'lastname'},
			    {name: 'bill.company', type: 'string', mapping: 'company'},
			    {name: 'bill.addr1', type: 'string', mapping: 'addr1'},
			    {name: 'bill.addr2', type: 'string', mapping: 'addr2'},
			    {name: 'bill.city', type: 'string', mapping: 'city'},
			    {name: 'bill.state', type: 'string', mapping: 'state'},
			    {name: 'bill.country', type: 'string', mapping: 'country'},
			    {name: 'bill.postcode', type: 'string', mapping: 'postcode'},
		  	]
	  	})

		,listeners: {
			render: function(p){
				p.getForm().load({
					url: 'index.php?option=com_osemsc&controller=members',
					params:{task:'action',action:'member.billinginfo.getItem',member_id:oseMemsMsc.member_id},
				});
			}
		}
	});