Ext.ns('osemscProfile');
osemscProfile.msg = new Ext.App();

	osemscProfile.orderCombo = new Ext.form.ComboBox({
  		id:'ordering'
        ,fieldLabel: Joomla.JText._('Ordering')
        ,hiddenName: 'ordering'
        ,hidden: true
	    ,typeAhead: true
	    ,triggerAction: 'all'
	    ,lazyRender:true
	    ,mode: 'remote'
	    ,lastQuery: ''
	    ,store: new Ext.data.Store({
			  proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=profile'
		            ,method: 'POST'
		      })
			  ,baseParams:{task: "getOrder"}
			  ,reader: new Ext.data.JsonReader({
			    root: 'results'
			    ,totalProperty: 'total'
			  },[
			    {name: 'ordering', type: 'int', mapping: 'ordering'}
			    ,{name: 'Title', type: 'string', mapping: 'displayText'}
			  ])
			  ,sortInfo:{field: 'ordering', direction: "ASC"}
			  ,autoLoad:{}
		})

	    ,valueField: 'ordering'
	    ,displayField: 'Title'

  	});

	osemscProfile.reader = new Ext.data.JsonReader({
	    root: 'result',
	    totalProperty: 'total',
	    fields:[
		    {name: 'id', type: 'int', mapping: 'id'},
    		{name: 'name', type: 'string', mapping: 'name'},
    		{name: 'note', type: 'string', mapping: 'note'},
    		{name: 'type', type: 'string', mapping: 'type'},
   			{name: 'ordering', type: 'string', mapping: 'ordering'},
   			{name: 'published', type: 'string', mapping: 'published'},
   			{name: 'require', type: 'string', mapping: 'require'},
   			{name: 'params', type: 'string', mapping: 'params'}
	  	]
  	});

	osemscProfile.form = new Ext.FormPanel({
		id:'osemsc-profile-panel'
		,formId:'osemsc-profile-form'
		,border: false
        ,labelWidth: 150
        ,reader: osemscProfile.reader
 		,autoHeight: true
 		//,bodyStyle: 'padding: 10px'
 		,defaults: {msgTarget : 'side'}
        ,items: [{
              xtype:'hidden'
            ,name: 'id'
        },{
        	xtype: 'textfield'
			,fieldLabel: Joomla.JText._('User_field_title')
			,width: 300
			,name: 'name'
			,allowBlank: false
		},{
        	xtype: 'textfield'
    		,fieldLabel: Joomla.JText._('User_field_note')
    		,width: 300
    		,name: 'note'
    	},{
			xtype: 'checkbox'
			,fieldLabel: Joomla.JText._('Published')
	        ,name: 'published'
	        ,inputValue: '1'
		},{
			xtype: 'checkbox'
			,fieldLabel: Joomla.JText._('Required')
		    ,name: 'require'
		    ,inputValue: '1'
		},osemscProfile.orderCombo
		,{
			id:'type'
        	,xtype: 'combo'
        	,width: 300
        	,fieldLabel: Joomla.JText._('User_field_type')
        	,hiddenName: 'type'
        	,typeAhead: true
		    ,triggerAction: 'all'
		    ,lazyRender:true
		    ,mode: 'local'
		    ,allowBlank: false
		    ,store: new Ext.data.ArrayStore({
		        id: 0
		        ,fields: [
		            'myId'
		            ,'displayText'
		        ]
		        ,data: [
		        	['textfield', Joomla.JText._('textfield')]
		        	,['textarea', Joomla.JText._('textarea')]
                    ,['radio', Joomla.JText._('radio')]
                    ,['combo', Joomla.JText._('combo')]
                    ,['multiselect', Joomla.JText._('multiselect')]
		        	,['fileuploadfield', Joomla.JText._('fileuploadfield')]
		        	,['datefield', Joomla.JText._('datefield')]
		        ]
		    })
		    ,valueField: 'myId'
		    ,displayField: 'displayText'
        	,listeners: {
	        	select: function(c)	{
				switch (c.getValue())	{
					case('multiselect'):
	    			case('combo'):
	    			case('radio'):
	    				osemscProfile.form.findById('params').setVisible(true);
	    				osemscProfile.form.findById('notice').setVisible(true);
	    				osemscProfile.form.findById('params').setDisabled(false);
	    				osemscProfile.form.findById('path').setVisible(false);
    					osemscProfile.form.findById('path').setDisabled(true);
	   	    			break;
	    			case('textarea'):
	    			case('textfield'):	
	    			case('datefield'):	
	    				osemscProfile.form.findById('params').setVisible(false);
	    				osemscProfile.form.findById('notice').setVisible(false);
	    				osemscProfile.form.findById('params').setDisabled(true);
	    				osemscProfile.form.findById('path').setVisible(false);
    					osemscProfile.form.findById('path').setDisabled(true);
		    			break;
	    			case('fileuploadfield'):
	    				osemscProfile.form.findById('params').setVisible(false);
    					osemscProfile.form.findById('notice').setVisible(false);
    					osemscProfile.form.findById('params').setDisabled(true);
	    				osemscProfile.form.findById('path').setVisible(true);
    					osemscProfile.form.findById('path').setDisabled(false);
    					break;
		    		}
        		}
            }
        },{
        	id: 'params'
			,xtype: 'textarea'
			,hidden: true
			,disabled:true
			,width: 300
			,height: 50
			,fieldLabel: Joomla.JText._('Params_please_divide_each_option_with_a_comma')
			,name: 'params'
			,allowBlank: false

		},{
        	id: 'path'
    		,xtype: 'textfield'
    		,hidden: true
    		,disabled:true
    		,width: 300
    		,fieldLabel: Joomla.JText._('Upload_Path_eg_components_com_osemsc_fileupload')
    		,name: 'params'
    		,allowBlank: false

    	},{
			id:'notice'
			,hidden: true
			,fieldLabel: 'Note'
			,html: Joomla.JText._('Please_divide_each_option_with_a_comma')
			,border: false
			,bodyStyle: 'margin-top: 3px'
		}],
        buttons: [{
            text: Joomla.JText._('Save'),
            handler: function(){
        		osemscProfile.form.getForm().submit({
                	clientValidation: true,
					url : 'index.php?option=com_osemsc&controller=profile',
					method: 'post',
					params:{'task':'save'},
					success: function(form, action){
						var msg = action.result;
						osemscProfile.msg.setAlert(msg.title,msg.content);
						oseMsc.profiles.grid.getStore().reload();
						oseMsc.profiles.grid.getView().refresh();
					}
				});
            }
        }]
    });

	osemscProfile.panel = new Ext.Panel({
		border: false
		,autoHeight: true
		,items:osemscProfile.form
	})
