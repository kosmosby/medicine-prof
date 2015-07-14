Ext.ns('oseMscAddon');
var addonMscEmailStore = function(emailType)	{
		return new Ext.data.Store({
			proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc&controller=emails',
	            method: 'POST'
	      	})
		  	,baseParams:{task: "getEmails",email_type: emailType}
		  	,reader: new Ext.data.JsonReader({
			    root: 'results',
			    totalProperty: 'total'
		  	},[
			    {name: 'id', type: 'int', mapping: 'id'},
			    {name: 'Subject', type: 'string', mapping: 'subject'}
		  	])
		  	,autoLoad:{}
		  	,listeners: {
				load: function(s)	{
	    			var defaultData = {
	    				id: '0'
	                    ,Subject: 'None'
	                };
	                var recId = s.getTotalCount(); // provide unique id
	                var p = new s.recordType(defaultData, recId); // create new record

	                s.insert(0,p);
		    	}
		  	}
		})
	};

	mscCombo = new Ext.form.ComboBox({
		id:'msc.joined_msc'
	    ,fieldLabel: Joomla.JText._('Please_select_a_membership_plan_for_this_control_function')
	    ,hiddenName: 'msc.joined_msc'
	    ,typeAhead: true
	    ,width: 350
	    ,listWidth: 350
	    ,triggerAction: 'all'
	    ,lazyRender:true
	    ,mode: 'remote'
		,emptyText: Joomla.JText._('Do_not_control')
	    ,lastQuery: ''
	    ,allowBlank: true
	    ,store: new Ext.data.Store({
			  proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc'
		            ,method: 'POST'
		      })
			  ,baseParams:{controller:"memberships", task: "getFullTree"}
			  ,reader: new Ext.data.JsonReader({
			    root: 'results'
			    ,totalProperty: 'total'
			  },[
			    {name: 'msc_id', type: 'string', mapping: 'id'}
			    ,{name: 'msc_name', type: 'string', mapping: 'title'}
			  ])
			  ,sortInfo:{field: 'msc_id', direction: "ASC"}
			  ,autoLoad:{}
		})
	    ,valueField: 'msc_id'
	    ,displayField: 'msc_name'

	});
	
	var buildEmailCombo = function(config)	{
		this.title = Ext.value(config.title);
		this.name = Ext.value(config.name);
		var type = Ext.value(config.type);
		var combo = new Ext.form.ComboBox({
			fieldLabel: this.title
			,hiddenName: this.name
			,typeAhead: true
		    ,triggerAction: 'all'
		    ,lastQuery: ''
		    ,mode: 'local'
		    ,emptyText: Joomla.JText._('MSC_NONE')
		    ,store: new Ext.data.ArrayStore({
		  		root: 'results'
		  		,idProperty: 'id'
		    	,totalProperty: 'total'
		  		,fields:[
				    {name: 'id', type: 'int', mapping: 'id'}
				    ,{name: 'subject', type: 'string', mapping: 'subject'}
				    ,{name: 'type', type: 'string', mapping: 'type'}
			  	]
			})
			,listeners: {
				render: function(c)	{
					var defaultData = {
	                    id: 0
	                    ,subject: Joomla.JText._('MSC_NONE')
	                    ,type: ''
	                };
	                var s = c.getStore();
	                var recId = s.getTotalCount(); // provide unique id
	                var p = new s.recordType(defaultData, recId++); // create new record
	                s.insert(0,p);
					ose.combo.getLocalJsonData(c,getEmails());
					c.getStore().filter([{
						fn   : function(record) {
							return record.get('type') == type || record.get('type') == '';
						},
						scope: this
					}]);
				}
			}
			,valueField: 'id'
	    	,displayField: 'subject'
		})
		return combo;
	}
	var addonMscEmailFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('Email_Templates_Setting'),
		labelWidth: 400,
		defaults:{width:300},
		items:[
			buildEmailCombo({
				title: Joomla.JText._('Email_template_for_new_member_signing_up_successfully')
				,name: 'msc.wel_email'
				,type: 'wel_email'
			})
			,buildEmailCombo({
				title: Joomla.JText._('Email_template_for_membership_cancellation')
				,name: 'msc.cancel_email'
				,type: 'cancel_email'
			})
			,buildEmailCombo({
				title: Joomla.JText._("Email_template_for_a_user_s_membership_is_going_to_expire")
				,name: 'msc.notification'
				,type: 'notification'
			})
			,buildEmailCombo({
				title: Joomla.JText._("Email_template_for_a_user_s_membership_has_expire")
				,name: 'msc.exp_email'
				,type: 'exp_email'
			})
			,buildEmailCombo({
				title: Joomla.JText._("Invitation_email_template")
				,name: 'msc.invitation'
				,type: 'invitation'
			})
	    ]
	});
	var addonMscMembershipFS = new Ext.form.FieldSet({
		title:Joomla.JText._('Membership_Joining_Control'),
		labelWidth: 400,
		defaults:{width:300},
		items:[
		       {
		    	   fieldLabel: Joomla.JText._('Join_this_membership_after_they_become_a_member')
					,xtype: 'checkbox'
					,name: 'msc.control_joining'
					,inputValue:1
			   },
		       mscCombo,
		       {
		    	   fieldLabel: Joomla.JText._('Must_the_user_s_membership_active')
					,xtype: 'checkbox'
					,name: 'msc.control_active'
					,inputValue:1
			   }
	    ]
	});
	oseMscAddon.msc = new Ext.Panel({
		bodyStyle: 'padding: 0px;',
		defaults: [{bodyStyle: 'padding: 0px'}],
		height: 700,
		buttons: [{
			text: Joomla.JText._('Save'),
			handler: function(){
				oseMscAddon.msc.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.msc.save',msc_id: oseMsc.msc_id
				    },
				    success: function(form, action) {
				    	var msg = action.result;
				    	oseMsc.msg.setAlert(msg.title,msg.content);
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
		,autoScroll: true
		,items:[{
			ref:'form',
			xtype:'form',
			labelAlign: 'left',
			autoScroll: false,
			width: 800,
		    border: false,
		    items:[{
		    	xtype:'fieldset',
		    	hidden:true,
		    	title: 'Default',
		    	labelWidth: 400,
		    	items:[{
		    		itemId:'limit-number',
		        	xtype:'numberfield',
		            fieldLabel: 'Maximum number of members in the group',
		            name: 'msc.limit_number',
		            emptyText: '-1'
		    	}]
		    },{
				xtype: 'fieldset'
				,title: Joomla.JText._('Disable_Renewing')
				,labelWidth: 150
				,items:[{
					fieldLabel: Joomla.JText._('Disable_Renewing')
					,xtype: 'checkbox'
					,name: 'msc_renew_disable'
					,inputValue:1
				}]
			},{
		    	xtype:'fieldset',
		    	labelAlign: 'top',
		    	title: Joomla.JText._('Default_Messages_to_Non_Members'),
		    	items:[{
		    		hideLabel: true
		    		,xtype:'tinymce'
		    		,name:'msc.restrict'
			        ,itemId:'restrict'
			        ,width: 640
            		,height:350
			        ,tinymceSettings: {
				        theme: "advanced",
				        skin: 'o2k7',
				        plugins: "pagebreak,style,layer,table,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
				        theme_advanced_buttons1: "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
				        theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
				        theme_advanced_buttons3: "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|",
				        theme_advanced_buttons4: "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
				        theme_advanced_toolbar_location: "top",
				        theme_advanced_toolbar_align: "left",
				        theme_advanced_statusbar_location: "bottom",
				        theme_advanced_resizing: false,
				        extended_valid_elements: "a[style|class|name|href|target|title|onclick],img[style|class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
				        template_external_list_url: "example_template_list.js"
				    }
		    	}]
		    },
		    	addonMscEmailFieldset,
		    	addonMscMembershipFS
		    ],
		    reader:new Ext.data.JsonReader({
			    root: 'result',
			    totalProperty: 'total',
			    fields:[
				    {name: 'msc.limit_number', type: 'int', mapping: 'limit_number'},
				    {name: 'msc_renew_disable', type: 'int', mapping: 'renew_disable'},
				    {name: 'msc.restrict', type: 'string', mapping: 'restrict'},
				    {name: 'msc.reg_email', type: 'int', mapping: 'reg_email'},
				    {name: 'msc.wel_email', type: 'int', mapping: 'wel_email'},
				    {name: 'msc.cancel_email', type: 'int', mapping: 'cancel_email'},
				    {name: 'msc.exp_email', type: 'int', mapping: 'exp_email'},
				    {name: 'msc.notification', type: 'int', mapping: 'notification'},
				    {name: 'msc.joined_msc', type: 'int', mapping: 'joined_msc'},
				    {name: 'msc.control_joining', type: 'int', mapping: 'control_joining'},
				    {name: 'msc.control_active', type: 'int', mapping: 'control_active'}				    
			  	]
		  	})
		}],

		listeners:{
			render: function(panel){
				//addonMscEmailStore.load();
				panel.form.getForm().load({
					//waitMsg : 'Loading...',
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'msc'}
				});
			}
		}
	});