Ext.ns('osemscEmails');

osemscEmails.Init = function()	{

}

osemscEmails.Init.prototype = {
		init: function()	{
			function ajaxAction(option, task, controller,selections)
		  	{
			var i=0;
		    ids=new Array();
			for (i=0; i < selections.length; i++)
			{
		        ids [i] = selections[i].id;
			}
			ids = Ext.encode(ids);
			// Ajax post scanning request;
			Ext.Ajax.request({
						url : 'index.php' ,
						params : {
							option : option,
							task:task,
							controller:controller,
							ids: ids
						},
						method: 'POST',
						success: function ( result, request ) {
							msg = Ext.decode(result.responseText);
							if (msg.status!='ERROR')
							{
								Ext.Msg.alert(msg.status, msg.result);
								osemscEmails.store.reload();
							}
							else
							{
								Ext.Msg.alert(Joomla.JText._('Error'), msg.result);
								osemscEmails.store.reload();
							}
						}
			});
		  }

			osemscEmails.msg = new Ext.App();
			osemscEmails.store = new Ext.data.Store({
				  //id: 'osemsc-emails-list',
				  proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=emails',
			            method: 'POST'
			        }),
				  baseParams:{task: "getEmails",limit: 20},
				  reader: new Ext.data.JsonReader({
				              // we tell the datastore where to get his data from
				    root: 'results',
				    totalProperty: 'total'
				  },[
				    {name: 'id', type: 'int', mapping: 'id'},
				    {name: 'Subject', type: 'string', mapping: 'subject'},
				    {name: 'Email-Body', type: 'string', mapping: 'body'},
				    {name: 'Type', type: 'string', mapping: 'type'}
				  ]),
				  sortInfo:{field: 'id', direction: "ASC"},
				  autoLoad: {}
			});
		/*
			osemscEmails.expander = new Ext.ux.grid.RowExpander({
		        tpl : new Ext.Template(
		            '<table>',
			            '<tr><td><b>Subject:</b> </p></td>{Subject}<td></td></tr>',
			            '<tr><td><b>Body:</b> </p></td>{Email-Body}<td></td></tr>',
		            '</table>'
		        )
		    });
		*/
			osemscEmails.cm = new Ext.grid.ColumnModel({
		        defaults: {
		            width: 200,
		            sortable: true
		        },
		        columns: [
			        //osemscEmails.expander,
			        new Ext.grid.RowNumberer({header:'#'}),
			    {
			    	id: 'id',
		            header: Joomla.JText._('ID'),
		            dataIndex: 'id',
		            hidden: true
		            //hideable:false,
			    },{
		            id: 'subject',
		            header: Joomla.JText._('Subject'),
		            dataIndex: 'Subject'
		        },{
		            id: 'type'
		            ,header: Joomla.JText._('Type')
		            ,dataIndex: 'Type'
		            ,renderer: function(val)	{
		            	switch(val)	{
		            		case('wel_email'):
		            			return Joomla.JText._('Email_template_for_a_user_signs_up_a_membership_successfully');
		            		break;

		            		case('reg_email'):
		            			return Joomla.JText._('Email_template_for_a_user_registers_an_account');
		            		break;

		            		case('cancel_email'):
		            			return Joomla.JText._('Email_template_for_a_user_s_membership_has_been_canceled');
		            		break;
		            		
		            		case('cancelorder_email'):
		            			return Joomla.JText._('Email_template_for_a_user_s_membership_subscription_has_been_canceled');
		            		break;

		            		case('exp_email'):
		            			return Joomla.JText._('Email_template_for_a_user_s_membership_that_has_expired');
		            		break;

		            		case('term'):
		            			return Joomla.JText._('Terms_of_Service');
		            		break;

		            		case('notification'):
		            			return Joomla.JText._('Email_template_for_a_user_s_membership_that_is_about_to_expire');
		            		break;

		            		case('receipt'):
		            			return Joomla.JText._('Sales_Receipt');
		            		break;
		            		
		            		case('pay_offline'):
		            			return Joomla.JText._('Email_template_for_a_user_used_the_Pay_Offline_payment_method');
		            		break;
		            		
		            		case('invitation'):
		            			return Joomla.JText._('Invitation_email_template');
		            		break;
		            	}
		            }
		        }]
			});

			osemscEmails.tbar = new Ext.Toolbar({
			    items: [{
		    		ref:'addBtn',
		            iconCls: 'icon-user-add',
		            text: Joomla.JText._('Add_new_template'),
		            handler: function(){
		            	if(!modEmailWin)	{
		            		var modEmailWin = new Ext.Window({
		            			title: Joomla.JText._('Email_Add')
		            			,modal: true
		            			,width: 1000
		            			,autoHeight: true
		            			,autoLoad: {
		            				url: 'index.php?option=com_osemsc&controller=emails'
		            				,params:{'task': 'getMod', addon_type: 'email',addon_name: 'email'}
		            				,scripts: true
		            				,callback: function(el,success,response,opt)	{
		            					modEmailWin.add(osemscEmail.panel);
		            					modEmailWin.doLayout();
		            				}
		            			}
		            		});
		            	}

		                modEmailWin.show().alignTo(Ext.getBody(),'t-t');
		            }
		        },{
		    		ref:'loadBtn',
		            iconCls: 'icon-user-load',
		            text: Joomla.JText._('Load_sample_templates'),
		            handler: function(){
						ajaxAction('com_osemsc', 'loadEmailTemplate', 'emails','');
		            }
		        },
		          {
		        	ref: 'editBtn',
		            iconCls: 'icon-user-edit',
		            text: Joomla.JText._('Edit'),
		            disabled: true,
		            handler: function(){
		            	if(!modEmailWin)	{
		            		var modEmailWin = new Ext.Window({
		            			title: Joomla.JText._('Edit_emails')
		            			,modal: true
		            			,autoHeight: true
		            			,width: 1000
		            			,autoLoad: {
		            				url: 'index.php?option=com_osemsc&controller=emails'
		            				,params:{'task': 'getMod', addon_type: 'email',addon_name: 'email'}
		            				,scripts: true
		            				,callback: function(el,success,response,opt)	{
		            					modEmailWin.add(osemscEmail.panel);
		            					modEmailWin.doLayout();

		            					var node = osemscEmails.grid.getSelectionModel().getSelected();
						            	//Ext.getCmp("osemsc-email-panel").email_id.setValue(node.id);
						                osemscEmail.form.getForm().load({
						                	url: 'index.php?option=com_osemsc&controller=emails',
						                	params:{task:'getItem',id:node.id}
						                	,success: function(form,action)	{
						                		if(osemscEmail.form.getForm().findField('type').getValue() == 'term')
						                		{
						                			 osemscEmail.form.getForm().findField('msc_id').setVisible(true);
						                		}else{
						                			 osemscEmail.form.getForm().findField('msc_id').setVisible(false);
						                		}
						                		if(osemscEmail.form.getForm().findField('type').getValue())	{
							            			osemscEmail.panel.getComponent('emailParams').load({
									            		url:'index.php?option=com_osemsc&controller=emails'
									            		,params:{task: 'getEmailParams',type: osemscEmail.form.getForm().findField('type').getValue(), email_id: osemscEmail.form.getForm().findField('id').getValue()}
									            	});
							            		}
						                	}
						            	});
		            				}
		            			}
		            		});
		            	}

		            	modEmailWin.show().alignTo(Ext.getBody(),'t-t');
		            }
		        },{
		        	ref: 'removeBtn',
		            iconCls: 'icon-user-delete',
		            text: Joomla.JText._('Remove'),
		            disabled: true,
		            handler: function(){
		                var node = osemscEmails.grid.getSelectionModel().getSelected();

		            	Ext.Ajax.request({
							url : 'index.php?option=com_osemsc&controller=emails&task=remove',
							params:{email_id:node.id, task:'remove'},
							success: function(response, opts){
								var msg = Ext.decode(response.responseText);
								osemscEmails.msg.setAlert(msg.title,msg.content);
								osemscEmails.store.remove(node);
								osemscEmails.store.reload();
								osemscEmails.grid.getView().refresh();
							}
						});

		            }
		        },
		        '->', Joomla.JText._('Type'),{
		        	ref:'email_type',
		        	xtype:'combo',
		            hiddenName: 'email_type',
		            width:420,
				    typeAhead: true,
				    triggerAction: 'all',
				    lazyRender:false,
				    mode: 'local',

				    store: new Ext.data.ArrayStore({
				        id: 0,
				        fields: [
				            'value',
				            'text'
				        ],
				        data: [
				        	['', Joomla.JText._('All')],
							['reg_email', Joomla.JText._('Email_template_for_a_user_registers_an_account')],
				        	['wel_email', Joomla.JText._('Email_template_for_a_user_signs_up_a_membership_successfully')],
		                    ['receipt', Joomla.JText._('Sales_Receipt')],
				        	['cancel_email', Joomla.JText._('Email_template_for_a_user_s_membership_has_been_canceled')],
				        	['exp_email', Joomla.JText._('Email_template_for_a_user_s_membership_that_has_expired')],
				        	['notification', Joomla.JText._('Email_template_for_a_user_s_membership_that_is_about_to_expire')],
				        	['term',Joomla.JText._('Terms_of_Service')],
				        	['pay_offline',Joomla.JText._('Email_template_for_a_user_used_the_Pay_Offline_payment_method')],
				        	['invitation',Joomla.JText._('Invitation_email_template')]
				        ]
				    }),


				    valueField: 'value',
				    displayField: 'text',

				    listeners: {
				        // delete the previous query in the beforequery event or set
				        // combo.lastQuery = null (this will reload the store the next time it expands)
				        beforequery: function(qe){
				        	delete qe.combo.lastQuery;
				        },

				        select: function(c,r,i)	{
			    			osemscEmails.store.reload({
			    				params:{email_type:r.data.value}
			    			});
			    		}
			        }
		        },'-',
		        new Ext.ux.form.SearchField({
		            store: osemscEmails.store,
		            paramName: 'search',
		            width:150
		        })
			    ]
			});

			osemscEmails.limit = new Ext.form.ComboBox({
		    	id:'osemsc-emails-limit',
			    width: 50,
			    mode: 'local',
			    typeAhead: true,
			    value: 20,
			    store: new Ext.data.ArrayStore({
			        id: 'limitStore',
			        fields: ['limitValue','limitText'],
			        data: [[5, '5'], [10, '10'], [20, '20'], [30, '30'],['all','All']]
			    }),
			    valueField: 'limitValue',
			    displayField: 'limitText',
			    listeners:{
			    	change: function(e,newV,oldV){
			    		osemscEmails.store.setBaseParam('limit',osemscEmails.limit.getValue());
			    		osemscEmails.store.reload();
			    		osemscEmails.grid.getView().refresh();
			    	}
			    }
			});

		// --------------------------------- Emails Grid ---------------------------------------- //

			osemscEmails.grid = new Ext.grid.GridPanel({
		    	id:'osemsc-emails-grid',
		    	title: Joomla.JText._('Email_and_Templates'),
		        store: osemscEmails.store,
		        viewConfig:{forceFit:true},
		        //plugins: [osemscEmails.expander],
		     	colModel:osemscEmails.cm,
		     	height: 500,

		     	listeners: {
		     			activate: function(){
		     				osemscEmails.store.reload();
		     				osemscEmails.grid.getView().refresh();
		     			}
		     	},
		     	sm: new Ext.grid.RowSelectionModel({singleSelect:true}),

		     	tbar: osemscEmails.tbar,

		     	bbar:new Ext.PagingToolbar({
		    		pageSize: 20,
		    		store: osemscEmails.store,
		    		displayInfo: true,
		    		displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
					emptyMsg: Joomla.JText._("No_topics_to_display")
			    })
		    });


		}
}

	