Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();

var ClassificationCombo = new Ext.form.ComboBox({
			    typeAhead: true,
			    name: 'mailing.sector',
			    id: 'mailing.sector',
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
			name:'member_id'
			,xtype: 'hidden'
		},{
            fieldLabel: 'Company'
            ,name: 'mailing.company'
        },ClassificationCombo, {
            fieldLabel: 'Street Address1'
            ,name: 'mailing.addr1'
            ,allowBlank:false
        },{
            fieldLabel: 'Street Address2'
            ,name: 'mailing.addr2'
        },{
            fieldLabel: 'City'
            ,name: 'mailing.city'
            ,allowBlank:false
        },{
            fieldLabel: 'State/Province'
            ,name: 'mailing.state'
            ,allowBlank:false
        },{
            fieldLabel: 'Country'
            ,hiddenName: 'mailing.country'
            ,id: 'mailing_country'
            ,xtype: 'combo'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    //,listClass: 'combo-left'
		    ,lazyInit: false
		    ,mode: 'remote'
		    ,lastQuery: ''
		    ,forceSelection: true
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc',
		            method: 'POST'
	      		})
			  	,baseParams:{task: "getCountry"}
			  	,reader: new Ext.data.JsonReader({
			    	root: 'results',
			    	totalProperty: 'total'
			    	,idProperty: 'country_id'
			  	},[
			    {name: 'code', type: 'string', mapping: 'country_2_code'},
			    {name: 'cname', type: 'string', mapping: 'country_name'}
			  	])
			  	,listeners:{
			  		load: function(s)	{
			  			oseMscAddon.mailing.getForm().load({
							url: 'index.php?option=com_osemsc&controller=members'
							,waitMsg: 'Loading'
							,params:{
								task:'action'
								,action:'member.mailing.getItem'
								,member_id: oseMemsMsc.member_id
							}
							,success: function(form,action)	{
								var msg = action.result;
								if(!msg.data['mailing.country'] || msg.data['mailing.country'].length < 1)	{
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
            fieldLabel: 'Zip/Postal Code'
            ,name: 'mailing.postcode'
            ,allowBlank:false
        }]
	    ,reader:new Ext.data.JsonReader({
		    root: 'result'
		    ,totalProperty: 'total'
		    ,idProperty: 'user_id'
		    ,fields:[
			    {name: 'mailing.firstname', type: 'string', mapping: 'firstname'}
			    ,{name: 'mailing.lastname', type: 'string', mapping: 'lastname'}
			    ,{name: 'mailing.company', type: 'string', mapping: 'company'}
			    ,{name: 'mailing.addr1', type: 'string', mapping: 'addr1'}
			    ,{name: 'mailing.addr2', type: 'string', mapping: 'addr2'}
			    ,{name: 'mailing.city', type: 'string', mapping: 'city'}
			    ,{name: 'mailing.state', type: 'string', mapping: 'state'}
			    ,{name: 'mailing.country', type: 'string', mapping: 'country'}
			    ,{name: 'mailing.postcode', type: 'string', mapping: 'postcode'}
			    ,{name: 'member_id', type: 'int', mapping: 'user_id'}
			    ,{name: 'mailing.sector', type: 'string', mapping: 'sector'}
		  	]
	  	})

		,listeners: {
			render: function(p){
				p.findById('mailing_country').getStore().load();
			/*
				p.getForm().load({
					url: 'index.php?option=com_osemsc&controller=member',
					params:{
						task:'action'
						,action:'member.mailing.getItem'
					}
				})
			*/
			}

		}
	});