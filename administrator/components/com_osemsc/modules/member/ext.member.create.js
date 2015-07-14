Ext.ns('oseMemMsc','oseMemMsc.createParams');
	
	oseMemMsc.createParams.params = {checked:false};
	Ext.apply(Ext.form.VTypes,{
		uniqueUserName: function(val,field)	{
			var unique = oseMemMsc.createParams.params;
			if(!unique.checked)	{
				
				Ext.Ajax.request({
	        		url: 'index.php?option=com_osemsc&controller=member'
	        		,params: {
	        			task : 'action',action:'member.juser.formValidate'
	        			,username : val
	        		}
	        		,success: function(response, opt)	{
	        			var msg = Ext.decode(response.responseText);
	        			
	        			unique =  msg;
	        			unique.checked = true;
	        			
	        			oseMemMsc.createParams.params = unique;
	        			return field.validate();
	        		}
	        	});
			}	else	{
			
				oseMemMsc.createParams.params.checked = false;
				
				if(!Ext.isBoolean(unique.result))	{
    				return false;
    			}	else	{
    				return true;
    			}	
    			
			}
			return true;
		}
		,uniqueUserNameText: Joomla.JText._('This_username_has_been_registered_by_other_user')
	})
	
	oseMemMsc.createParams.country  = oseMsc.combo.getCountryCombo(Joomla.JText._('Country'),'bill_country',3,'local');
	oseMemMsc.createParams.state = oseMsc.combo.getStateCombo(Joomla.JText._('State_Province'),'bill_state',2,'local');

	oseMsc.combo.getLocalJsonData(oseMemMsc.createParams.country,oseMsc.countryData);
	oseMsc.combo.getLocalJsonData(oseMemMsc.createParams.state,oseMsc.stateData);
	oseMemMsc.createParams.state.getStore().fireEvent('load',oseMemMsc.createParams.state.getStore());
	oseMsc.combo.relateCountryState(oseMemMsc.createParams.country,oseMemMsc.createParams.state, oseMsc.defaultSelectedCountry.code3);
	
	var c = oseMemMsc.createParams.country;
	var r = c.getStore().getAt(c.getStore().find(c.valueField,c.getValue()))
	c.fireEvent('select',c,r);
			
	oseMemMsc.createParams.userForm = new Ext.form.FieldSet({
		ref:'user'
		,title:Joomla.JText._('User_Info')
		,defaultType: 'textfield'
		,defaults:{ labelWidth: 150, width: 250, msgTarget: 'side'}
		,items:[
			{itemId:"uname",fieldLabel:Joomla.JText._('User_Name'),allowBlank:false, name:'username',vtype: 'uniqueUserName'},
        	{itemId:'firstname',fieldLabel:Joomla.JText._('First_Name'),allowBlank:false, name:'firstname'},
        	{itemId:'lastname',fieldLabel:Joomla.JText._('Last_Name'),allowBlank:false, name:'lastname'},
        	{itemId:'email',fieldLabel:Joomla.JText._('Email'),vtype:'email',allowBlank:false, name:'email'},
        	{itemId:'password1',fieldLabel:Joomla.JText._('Password'),allowBlank:false, name:'password',inputType: 'password'},
        	{
        		itemId:'password2', inputType: 'password'
        		,fieldLabel:Joomla.JText._('Password_Confirm'),allowBlank:true, name:'password2'
        		,scope: 'oseMemMscAdd_createForm'
        		,validator :  function(val){
        			var obj = oseMemMsc.create.getForm().findField('password1');
	        		if(val != obj.getValue()){
	        			return Joomla.JText._('It_does_not_match')+obj.fieldLabel;
	        		}	else	{
	        			return true;
	        		}
	            }
        	}
		]
	});
	
	/*oseMemMsc.createParams.companyForm = new Ext.form.FieldSet({
		title:'Company Info.'
		,defaultType: 'textfield'
		,defaults:{ labelWidth: 150, width: 250}
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
            name: 'company.postcode'
        },{
            fieldLabel: 'Phone',
            name: 'company.telephone'
        }]
	});*/
	
	oseMemMsc.createParams.billingForm = new Ext.form.FieldSet({
		title:Joomla.JText._('Billing_Info')
		,defaultType: 'textfield'
		,defaults:{ labelWidth: 150, width: 250, msgTarget: 'side'}
		,items:[{
            fieldLabel: Joomla.JText._('Company')
            ,name: 'bill.company'
        },{
            fieldLabel: Joomla.JText._('Street_Address1')
            ,name: 'bill.addr1'
        },{
            fieldLabel: Joomla.JText._('Street_Address2')
            ,name: 'bill.addr2'
        },{
            fieldLabel: Joomla.JText._('City')
            ,name: 'bill.city'
        },
        oseMemMsc.createParams.country,oseMemMsc.createParams.state
        ,{
            fieldLabel: Joomla.JText._('Zip_Postal_Code')
            ,name: 'bill.postcode'
        }]
	});
	
	oseMemMsc.mscOption = function()	{
		var c = new Ext.form.ComboBox({
			itemId:'msc_option'
			,width: 350
	  		,ref: 'msc_option'
	        ,fieldLabel: Joomla.JText._('Membership_Option')
	        ,hiddenName: 'msc_option'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    ,lazyRender:true
		    ,mode: 'local'
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
	            	url: 'index.php?option=com_osemsc&controller=members',
		            method: 'POST'
	     	 	})
			  	,baseParams:{task: 'getOptions',msc_id: oseMemsMsc.tree_msc_id}
			  	,reader: new Ext.data.JsonReader({
			    	root: 'results',
				    totalProperty: 'total'
			  	},[
				    {name: 'oid', type: 'string', mapping: 'id'},
				    {name: 'title', type: 'string', mapping: 'title'}
			  	])
		  		,sortInfo:{field: 'oid', direction: "ASC"}
			    ,listeners: {
			    	load: function(s,r)	{
			    		if(s.getCount() > 0)	{
			    			c.setValue(r[0].get('oid'))
			    			//alert(r[0].toSource())
			    		}
			    	}
			    }
			    
			})
		    ,valueField: 'oid'
		    ,displayField: 'title'
		})
		
		c.getStore().load();
		return c;
	}
	
	
	oseMemMsc.create = new Ext.FormPanel({
    	ref:'form'
    	,xtype:'form'
    	,autoScroll: true
    	,labelWidth: 150
    	,buttons: [{
    		text: Joomla.JText._('Save')
    	}]
    	,items:[oseMemMsc.mscOption(),{
    		xtype: 'tabpanel'
    		,activeTab: 0
    		,border:false
    		,height: 390
    		,bodyStyle: 'padding: 10px'
    		,items: [
				oseMemMsc.createParams.userForm
				,oseMemMsc.createParams.billingForm
			]
    		
    	}]
    })
    
   