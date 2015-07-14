Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();

	//
	// Addon Msc Panel
	//
	var ClassificationCombo = new Ext.form.ComboBox({
			    typeAhead: true,
			    name: 'mailing.sector',
			    triggerAction: 'all',
			    lazyRender:true,
			    fieldLabel: 'Sector Classification',
			    mode: 'local',
			    store: new Ext.data.ArrayStore({
			        id: 0,
			        fields: [
			            'myId',
			            'displayText'
			        ],
			        data: [[1, 'Accrediting Group'],
							[2, 'Biotechnology/Genomics Company'],
							[3, 'Group Purchasing Organization'],
							[4, 'Government - Federal'],
							[5, 'Government - Local'],
							[6, 'Government - State'],
							[7, 'Health Information Exchange'],
							[8, 'Health Plan or Insurance Organization'],
							[9, 'Healthcare IT Apps'],
							[10, 'Healthcare IT Integration & Consulting'],
							[11, 'Healthcare System or IDN'],
							[12, 'Hospital'],
							[13, 'Laboratory'],
							[14, 'Medical Device Manufacturer'],
							[15, 'Non-Profit Association or Professional Society'],
							[16, 'Patient or Consumer Advocacy Group'],
							[17, 'Payer'],
							[18, 'Pharmaceutical Manufacturer'],
							[19, 'Physician Group'],
							[20, 'Practicing Clinician Organization'],
							[21, 'Purchasers/Employers'],
							[22, 'Public Health Organization'],
							[23, 'Quality Improvement Organization'],
							[24, 'Regional Extension Center'],
							[25, 'Research and Education Institution'],
							[26, 'Pharmacy - related organization'],
							[27, 'Standards Organization'],
							[28, 'Supply Chain Organization'],
							[29, 'Telecommunication Organization'],
							[30, 'Other']
			        	   ]
			    }),
			    valueField: 'myId',
			    displayField: 'displayText'
			});

	oseMscAddon.mailing_var1 = new Ext.FormPanel({
		autoScroll: true
		,height: 399
		,defaultType: 'textfield'
		,bodyStyle:'padding:10px'
		,labelWidth: 150
		,defaults: {width: 300}
		,buttons: [{
			text: 'save',
			handler: function(){
				oseMscAddon.mailing_var1.getForm().submit({
				    clientValidation: true
				    ,url: 'index.php?option=com_osemsc&controller=member'
				    ,params: {
				        task: 'action', action : 'member.mailing_var1.save'
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
            fieldLabel: 'Name'
            ,name: 'mailing.company'
        },ClassificationCombo,{
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
		    ,hidden:true
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
								,action:'member.mailing_var1.getItem'
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
			    ,{name: 'mailing.sector', type: 'string', mapping: 'sector'}
		  	]
	  	})

		,listeners: {
			render: function(p){
				p.findById('mailing_country').getStore().load();
			}

		}
	});