Ext.ns('oseMscAddon');

	oseMscAddon.msc = new Ext.FormPanel({
		border: false
		,labelWidth: 150
		,height: 250
		,items: [{
			fieldLabel: Joomla.JText._('Membership_Plans')
            ,hiddenName: 'msc_id'
            ,itemId: 'msc_id'
            ,id: 'company_country'
            ,xtype: 'combo'
		    ,typeAhead: true
		    ,triggerAction: 'all'
		    ,lastQuery: ''
		    ,lazyInit: false
		    ,forceSelection: true
		    ,mode: 'remote'
		    ,store: new Ext.data.Store({
		  		proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=member',
		            method: 'POST'
	      		})
			  	,baseParams:{task: "action",action: 'member.msc.getItems'}
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
			  			var comboMscId= oseMscAddon.msc.getComponent('msc_id');
			  			
			    		comboMscId.setValue(r[0].data.code);
			    		
			    		comboMscId.fireEvent('change',comboMscId,r[0].data.code);
			    	}
			  	}
			})
			
		    ,valueField: 'code'
		    ,displayField: 'cname'
		    ,listeners: {
		    	change: function(c,newV)	{
		    		Ext.Msg.wait(Joomla.JText._('Loading'),Joomla.JText._('Please_Wait'));
		    		oseMscAddon.msc.getForm().load({
						url: 'index.php?option=com_osemsc&controller=member'
						//,waitMsg: Joomla.JText._('Loading')
						,params:{task: "action",action: 'member.msc.getMscInfo', msc_id: newV}
		    			,success: function(form,action)	{
		    				Ext.Msg.hide();
		    			}	
					});
		    	}
		    }
		},{
			xtype: 'displayfield'
			,name: 'title'
			,fieldLabel: Joomla.JText._('Title')
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
		},{
			xtype: 'displayfield'
			,name: 'interval'
			,fieldLabel: Joomla.JText._('Interval')
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
			    ,{name: 'msc_option', type: 'string', mapping: 'msc_option'}
			    ,{name: 'interval', type: 'string', mapping: 'interval'}
		  	]
	  	})
	});