Ext.ns('osemscEmail');
osemscEmail.msg = new Ext.App();

	osemscEmail.reader = new Ext.data.JsonReader({
	    root: 'result',
	    totalProperty: 'total',
	    fields:[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'msc_id', type: 'int', mapping: 'msc_id'},
		    {name: 'subject', type: 'string', mapping: 'subject'},
		    {name: 'ebody', type: 'string', mapping: 'body'},
		    {name: 'type', type: 'string', mapping: 'type'},
		    {name: 'params', type: 'string', mapping: 'params'}
	  	]
  	});

	osemscEmail.form = new Ext.FormPanel({
		id:'osemsc-email-panel'
		,formId:'osemsc-email-form'
		,region: 'center'
		,border: false
        ,labelWidth: 80
        ,reader: osemscEmail.reader
 		,height: 550
 		,width: 750
 		,bodyStyle: 'padding: 5px'
        ,items: [{
        	ref:'email_id',
            xtype:'hidden',
            name: 'id',
            value:''
        },{
        	ref:'etype',
        	xtype: 'combo',
        	width: 420,
        	fieldLabel: Joomla.JText._('Type'),
        	hiddenName: 'type',
        	typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:true,
		    mode: 'local',
		    store: new Ext.data.ArrayStore({
		        id: 0,
		        fields: [
		            'myId',
		            'displayText'
		        ],
		        data: [
		        	//[0,'--------'],
		        	['reg_email', Joomla.JText._('Email_template_for_a_user_registers_an_account')],
		        	['wel_email', Joomla.JText._('Email_template_for_a_user_signs_up_a_membership_successfully')],
                    ['receipt', Joomla.JText._('Sales_Receipt')],
		        	['cancel_email', Joomla.JText._('Email_template_for_a_user_s_membership_has_been_canceled')],
		        	['cancelorder_email', Joomla.JText._('Email_template_for_cancelling_profile')],
		        	['exp_email', Joomla.JText._('Email_template_for_a_user_s_membership_that_has_expired')],
		        	['notification', Joomla.JText._('Email_template_for_a_user_s_membership_that_is_about_to_expire')],
		        	//[0,'--------'],
		        	['term',Joomla.JText._('Terms_of_Service')]
		        	,['faith',Joomla.JText._('Statement_of_Faith_for_religious_websites')]
		        	,['pay_offline',Joomla.JText._('Email_template_for_a_user_used_the_Pay_Offline_payment_method')]
		        	,['invitation',Joomla.JText._('Invitation_email_template')]
		        	//,['licseat','Email template for a user's membership that is about to expire']
		        ]
		    }),
		    valueField: 'myId',
		    displayField: 'displayText',
        	listeners: {
            	select: function(combo,r,i)	{
            		if(r.id == 'term')
            		{
            			 osemscEmail.form.getForm().findField('msc_id').setVisible(true);
            		}else{
            			 osemscEmail.form.getForm().findField('msc_id').setVisible(false);
            			 osemscEmail.form.getForm().findField('msc_id').setValue('');
            		}
            		osemscEmail.panel.getComponent('emailParams').load({
	            		url:'index.php?option=com_osemsc&controller=emails'
	            		,params:{task: 'getEmailParams',type: r.id, email_id: osemscEmail.form.getForm().findField('id').getValue()}
	            	});
            	}
            }
        },{
        	xtype: 'combo',
        	hidden:true,
        	width: 400,
        	fieldLabel: Joomla.JText._('Membership'),
        	hiddenName: 'msc_id',
        	typeAhead: true,
		    triggerAction: 'all',
		    lazyRender:true,
		    mode: 'remote',
		    store: new Ext.data.Store({
				proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc'
		            ,method: 'POST'
	      		})
	      		,baseParams:{controller:'coupons',task: 'getMscList'}
		  		,reader: new Ext.data.JsonReader({
			    	root: 'results'
			    	,totalProperty: 'total'
			  	},[
				    {name: 'id', type: 'int', mapping: 'id'}
				    ,{name: 'title', type: 'string', mapping: 'title'}
			  	])
			  	,listeners:{
			  		load: function(s,r){
			  			var defaultData = {
		                    id: '0',
		                    title: Joomla.JText._('All')
		                };
		                var recId = s.getTotalCount(); // provide unique id
		                var p = new s.recordType(defaultData, recId); // create new record

		                s.insert(0,p);
						
		                osemscEmail.form.getForm().findField('msc_id').setValue('0');
			  		}
			  	}
		  		,autoLoad:{}
			}),
		    valueField: 'id',
		    displayField: 'title'
        },{
            xtype:'textfield',
            fieldLabel: Joomla.JText._('Subject'),
            name: 'subject',
            width: 400
        },{
        	name:'ebody',
        	id:'ebody',
            xtype:'tinymce'
	        ,fieldLabel:Joomla.JText._('Description')
		    ,width: 640
            ,height:550
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
		        extended_valid_elements: "a[name|href|target|title|onclick],img[style|class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
		        template_external_list_url: "example_template_list.js"
		    }
        }],

        buttons: [{
            text: Joomla.JText._('Save'),
            handler: function(){
            	//alert(osemscEmail.form.getForm().findField('ebody').getValue())
                osemscEmail.form.getForm().submit({
                	clientValidation: true,
					url : 'index.php?option=com_osemsc&controller=emails',
					method: 'post',
					params:{'task':'save',ebody: osemscEmail.form.getForm().findField('ebody').getValue()},
					success: function(form, action){
						var msg = action.result;

						osemscEmail.msg.setAlert(msg.title,msg.content);

						if(!msg.id)	{
							osemscEmail.form.email_id.setValue(msg.id);
						}

						osemscEmails.grid.getStore().reload();
						osemscEmails.grid.getView().refresh();
					},
					failure: function(form, action){
						oseMsc.formFailure(form, action);
					}
				});
            }
        }]

    });

	osemscEmail.panel = new Ext.Panel({
		layout: 'border'
		,border: false
		,height: 750
		,items:[
			osemscEmail.form
			,{
				xtype: 'panel'
				,itemId: 'emailParams'
				,width: 230
				,height:500
				,region: 'east'
				,margins: {top:0, right:3, bottom:0, left:3}
			}
		]
	})
