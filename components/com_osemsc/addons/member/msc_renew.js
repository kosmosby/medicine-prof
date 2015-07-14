Ext.ns('oseMscAddon');
	var renewTpl = new Ext.XTemplate(
		'<div style="padding-top:10px;" class="x-form-item-label">',
		'<tpl if="this.isActive(status)">',
			'Information of your current membership ',
		'</tpl>',
		'<tpl if="this.isActive(status) == false">',
			Joomla.JText._('Information_of_expired_membership_information_joined_last_time'),
		'</tpl>',
		'</div>',
	    {
	        compiled: true,
	        disableFormats: true,
	        isActive: function(val){
	            return val == 'Active';
	        }
	    }
	);
	
	oseMscAddon.msc_renew = new Ext.FormPanel({
		border: false
		//,layout: 'form'
		,msgTarget: 'side'
		,labelWidth: 200
		,height: 360
		,bodyStyle: 'padding-top: 15px'
		,listeners:{
			render: function(f)	{
				f.getEl().mask(Joomla.JText._('Loading'));
			}
		}
		,items: [{
			 fieldLabel: Joomla.JText._('Membership_Plans')
            ,hiddenName: 'msc_id'
            ,allowBlank: false
            ,itemId: 'msc_id'
            ,id: 'msc_id'	
            ,xtype: 'combo'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    ,lazyInit: false
		    ,lastQuery: ''
		    ,mode: 'remote'
		    ,forceSelection: true
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=member',
		            method: 'POST'
	      		})
			  	,baseParams:{task: "action",action: 'member.msc_renew.getItems'}
			  	,reader: new Ext.data.JsonReader({   
			    	root: 'results'
			    	,totalProperty: 'total'
			    	,idProperty: 'msc_id'
			  	},[ 
			    {name: 'code', type: 'string', mapping: 'msc_id'},
			    {name: 'cname', type: 'string', mapping: 'msc_name'}
			  	])
			  	,autoLoad:{}
			  	,listeners: {
			  		load: function(s,r,i)	{
			  			var comboMscId= oseMscAddon.msc_renew.getComponent('msc_id');
			  			comboMscId.setValue(r[0].data.code);
			    		comboMscId.fireEvent('select',comboMscId,r[0],0);
			    	}
			  	}
			})
			
		    ,valueField: 'code'
		    ,displayField: 'cname'
		   	,listeners: {
		    	select: function(c,r,i)	{
		    		oseMscAddon.msc_renew.getEl().mask(Joomla.JText._('Loading'));
		    		oseMscAddon.msc_renew.msc_option.getStore().load({params:{msc_id:r.data.code}});
		    		oseMscAddon.msc_renew.getForm().load({
						url: 'index.php?option=com_osemsc&controller=payment'
						,params:{task: "action",action: 'member.msc_renew.getMscInfo', msc_id: r.data.code}
						,waitMsg: Joomla.JText._('Loading')
						,success: function(form,action){
							renewTpl.overwrite(oseMscAddon.msc_renew.info.body,action.result.data)
						}
					});
		    	}
		    }
		},{
	  		itemId:'msc_option'
	  		,ref: 'msc_option'
	  		,xtype: 'combo'
	        ,fieldLabel: Joomla.JText._('Membership_Option')
	        ,hiddenName: 'msc_option'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    ,lazyRender:true
		    ,lastQuery:''
		    ,mode: 'local'
		    ,forceSelection: true
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
	            	url: 'index.php?option=com_osemsc&controller=memberships',
		            method: 'POST'
	     	 	})
			  	,baseParams:{task: "action", action:'register.msc.getOptions',type:'renew'}
			  	,reader: new Ext.data.JsonReader({
			    	root: 'results',
				    totalProperty: 'total'
			  	},[
				    {name: 'id', type: 'string', mapping: 'id'},
				    {name: 'msc_id', type: 'string', mapping: 'msc_id'},
				    {name: 'text', type: 'string', mapping: 'title'}
			  	])
		  		,sortInfo:{field: 'id', direction: "ASC"}
			  	,listeners: {
			    	load: function(s,r,i)	{
			    		s.filter([{
							fn   : function(record) {
								return record.get('msc_id') == oseMscAddon.msc_renew.getComponent('msc_id').getValue()
							},
							scope: this
						}]);
			    		oseMscAddon.msc_renew.msc_option.setValue(s.getAt(0).get('id'));
			    		oseMscAddon.msc_renew.payment_mode.getStore().load({params:{msc_id:s.getAt(0).get('msc_id'),msc_option:s.getAt(0).get('id')}});
			    	}
			    }
			    //,autoLoad:{}
			})
	
		    ,valueField: 'id'
		    ,displayField: 'text'
	  	},{
			fieldLabel: Joomla.JText._('Renewal_Preference')
			,ref: 'payment_mode'
            ,hiddenName: 'payment_mode'
            ,allowBlank: false
            ,xtype: 'combo'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    ,lazyInit: false
		    ,lastQuery: ''
		    ,mode: 'remote'
		    ,forceSelection: true
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=register'
		            ,method: 'POST'
	      		})
			  	,baseParams:{
			  		task:'action', action :'member.msc_renew.getPaymentMode'
			  	}
			  	,reader: new Ext.data.JsonReader({   
			    	root: 'results'
			    	,totalProperty: 'total'
			  	},[ 
			    {name: 'code', type: 'string', mapping: 'value'},
			    {name: 'cname', type: 'string', mapping: 'text'}
			  	])
			  	,listeners: {
			    	load: function(s,r,i)	{
			    		oseMscAddon.msc_renew.payment_mode.setValue(r[0].data.code)
			    		oseMscAddon.msc_renew.getEl().unmask();
			    	}
			    }
			})
			
		    ,valueField: 'code'
		    ,displayField: 'cname'
			
		},{
			xtype: 'panel'
			,border: false
			,ref: 'info'
		},{
			xtype: 'displayfield'
			,name: 'title'
			,fieldLabel: Joomla.JText._('Title')
		},{
			xtype: 'displayfield'
			,name: 'payment_mode_text'
			,fieldLabel: Joomla.JText._('Billing_Preference')
		},{
			xtype: 'displayfield'
			,name: 'days_left'
			,fieldLabel: Joomla.JText._('Days_Left')
		},{
			xtype: 'displayfield'
			,name: 'status'
			,fieldLabel: Joomla.JText._('Membership_Status')
		},{
			xtype: 'displayfield'
			,name: 'start_date'
			,fieldLabel: Joomla.JText._('Start_Date')
		},{
			xtype: 'displayfield'
			,name: 'expired_date'
			,fieldLabel: Joomla.JText._('Expiration_Date')
		}]
		
		,reader: new Ext.data.JsonReader({   
		    root: 'result',
		    totalProperty: 'total',
		    fields:[ 
			    {name: 'title', type: 'string', mapping: 'title'}
			    ,{name: 'days_left', type: 'string', mapping: 'days_left'}
			    ,{name: 'status', type: 'string', mapping: 'status'}
			    ,{name: 'start_date', type: 'string', mapping: 'start_date'}
			    ,{name: 'expired_date', type: 'string', mapping: 'expired_date'}
			    ,{name: 'payment_mode_text', type: 'string', mapping: 'payment_mode_text'}
		  	]
	  	})
		
		,buttons: [{
			text: Joomla.JText._('Renew')
			,handler: function()	{
				Ext.Msg.wait(Joomla.JText._('Please_Wait'),Joomla.JText._('Loading'));
				oseMscAddon.msc_renew.getForm().submit({
					clientValidation: true
					//,waitMsg: Joomla.JText._('Loading')
					,url:'index.php?option=com_osemsc&controller=payment'
					,params:{task: 'toPaymentPage'}
					,success: function(form,action){
						Ext.Msg.hide();
						var msg = action.result;
						Ext.Msg.wait(Joomla.JText._('Please_Wait'),Joomla.JText._('Redirecting'));
						window.location = msg.link;
					}
					,failure: oseMsc.formFailureMB
				})
			}
		}]
	});
	
	//oseMscAddon.msc_renew.getComponent('msc_id').getStore().load();