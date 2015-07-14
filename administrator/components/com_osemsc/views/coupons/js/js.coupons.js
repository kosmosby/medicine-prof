Ext.ns('oseMsc','oseMsc.coupons');

Ext.onReady(function(){
	Ext.QuickTips.init();

	oseMsc.coupons.sm = new Ext.grid.CheckboxSelectionModel({
		single: true
		,listeners:{
			rowselect: function(sel,i,r)	{
				//oseMsc.paramPanel.fireEvent('reload',oseMsc.paramPanel,r.id,r.data.type);
				oseMsc.coupons.id = r.id;
				oseMsc.coupons.type = r.data.type;

				oseMsc.paramCoupons.getForm().reset()
				oseMsc.historyPanel.getStore().reload();
				oseMsc.paramCoupons.getForm().load({
        			url: 'index.php?option=com_osemsc&controller=coupons'
        			,params: {
	        			task: "getCouponsParams",
	        			id: oseMsc.coupons.id,
	        			type:'coupons'
        			}
        			,success: function(form,action)	{
        				var result = action.result.data;
        			}
        			,failure: function(form,action)	{
        				oseMsc.formFailureMB(form,action)
        			}
        		})
			}
		}
	});

	oseMsc.coupons.cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: false
        },
        columns: [
        	oseMsc.coupons.sm
        	,new Ext.grid.RowNumberer({header:'#'})
            ,{id: 'id', header: Joomla.JText._('ID'),  hidden:false, dataIndex: 'id', width: 20}
            ,{id: 'code', header: Joomla.JText._('Title'),  hidden:false, dataIndex: 'title'}
            //,{id: 'code', header: 'Coupon Code',  hidden:false, dataIndex: 'code'}
            ,{id: 'discount', header: Joomla.JText._('Discount'),  hidden:false, dataIndex: 'discount'}
            ,{id: 'discount_type', header: Joomla.JText._('Discount_Type'),  hidden:false, dataIndex: 'discount_type'}
        ]
    });

	oseMsc.coupons.store = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
	            url: 'index.php?option=com_osemsc',
	            method: 'POST'
	      }),
		  baseParams:{
		  	task: "getCoupons",
		  	limit: 20,
		  	controller: 'coupons'
		  	},
		  reader: new Ext.data.JsonReader({
		              // we tell the datastore where to get his data from
		    root: 'results',
		    totalProperty: 'total'
		  },[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'code', type: 'string', mapping: 'code'},
		    {name: 'title', type: 'string', mapping: 'title'},
		    {name: 'discount', type: 'string', mapping: 'discount'},
		    {name: 'discount_type', type: 'string', mapping: 'discount_type'}
		  ]),
		  autoLoad:{}
	});

	oseMsc.couponsList = new Ext.grid.GridPanel({
		id: 'coupons-list'
		,title: Joomla.JText._('Coupon_List')
		,sm: oseMsc.coupons.sm
		,cm: oseMsc.coupons.cm
		,store:oseMsc.coupons.store
		,viewConfig: {forceFit: true}
		,height: 500
		,width: 450
		,region: 'west'
		,margins: {top:5, right:5, bottom:5, left:3}
		,bbar: new Ext.PagingToolbar({
            pageSize: 25,
            store: oseMsc.coupons.store,
            displayInfo: true,
            displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
			emptyMsg: Joomla.JText._("No_topics_to_display")
        })
	});

	oseMsc.tbar = new Ext.Panel({
		layout: 'toolbar'
		,border: false
		,width: 300
		,items:[{
			xtype: 'button'
			,text: Joomla.JText._('Global_Setting')
			,hidden: true
			,handler:function(){
				if(!addWin)	{
					var addWin = new Ext.Window({
						title: Joomla.JText._('Global_Setting')
						,modal: true
						,items: [{
							xtype: 'form'
							,ref: 'form'
							,width: 500
							,labelWidth: 150
							,border: false
							,bodyStyle: 'padding: 10px'
							,items:[{
					            xtype: 'checkbox'
					            ,fieldLabel: Joomla.JText._('Wildcard')
				            	,id: 'wildcard_enabled'
				            	//,boxLabel: 'Unlimited'
				            	,name: 'wildcard_enabled'
				            	,inputValue: '1'
				            	,listeners: {
				            		check: function(cb,checked){
				        				cb.nextSibling().setDisabled(!checked);
				            		}
				            	}
					        },{
					        	fieldLabel: Joomla.JText._('Minimum_Length_Required')
					        	,xtype: 'numberfield'
				        		,name: 'wildcard_minlength'
				        		,disabled: true
				        		,minValue: 3
				        		,msgTarget: 'side'
					        }]
							,buttons: [{
								text: Joomla.JText._('Save')
								,handler: function(b){
									var cb = b.ownerCt.ownerCt.getForm().findField('wildcard_enabled');
									var params = {};
									params.task = 'save';
									params.config_type = 'coupon';

									if(Ext.value(cb.getValue(),false) === false)	{
										params.wildcard_enabled = 0;
									}
									b.ownerCt.ownerCt.getForm().submit({
										clientValidation: true
										,url: 'index.php?option=com_osemsc&controller=config'
										,params:params
										,success: function(form,action)	{
											oseMsc.formSuccess(form,action);
											addWin.close();
										}
									})
								}
							}]

							,reader: new Ext.data.JsonReader({
							    root: 'result',
							    totalProperty: 'total',
							    fields:[
								    {name: 'id', type: 'int', mapping: 'id'}
								    ,{name: 'wildcard_enabled', type: 'int', mapping: 'wildcard_enabled'}
								    ,{name: 'wildcard_minlength', type: 'string', mapping: 'wildcard_minlength'}
							  	]
						  	})
						}]
						,listeners: {
							show: function(p){
								p.form.getForm().load({
									url: 'index.php?option=com_osemsc&controller=config'
									,params:{task:'getConfig',config_type:'coupon'}
								});
							}
						}
					})
				}
				addWin.show();
			}
		},{
			xtype: 'button'
			,text: Joomla.JText._('New')
			,handler:function(){
				if(!addWin)	{
					var addWin = new Ext.Window({
						title: Joomla.JText._('New_Coupon')
						,modal: true
						,items: [{
							xtype: 'form'
							,width: 350
							,border: false
							,bodyStyle: 'padding: 10px'
							,items:[{
								xtype: 'textfield'
								,fieldLabel: Joomla.JText._('Title')
								,name: 'title'
								,emptyValue:Joomla.JText._('New_Coupon')
								,allowBlank: false
							}]

							,buttons: [{
								text: Joomla.JText._('Save')
								,handler: function(b){
									b.ownerCt.ownerCt.getForm().submit({
										clientValidation: true
										,url: 'index.php?option=com_osemsc&controller=coupons'
										,params: {task: 'save'}
										,success: function(form,action)	{
											oseMsc.formSuccess(form,action)

											oseMsc.couponsList.getStore().reload();
											oseMsc.couponsList.getView().refresh();
											addWin.close();
										}
										,failure: function(form,action)	{
											oseMsc.formFailureMB(form,action)
										}
									})
								}
							}]
						}]
					})
				}
				addWin.show();
			}
		},{
			text: Joomla.JText._('Remove')
			,xtype: 'button'
			,handler: function(b){
				oseMsc.paramCoupons.getForm().submit({
					clientValidation: true
					,url: 'index.php?option=com_osemsc&controller=coupons'
					,params: {task: 'remove'}
					,success: function(form,action)	{
						oseMsc.formSuccess(form,action)
						oseMsc.couponsList.getStore().reload();
						oseMsc.couponsList.getView().refresh();
						oseMsc.paramCoupons.getForm().reset()
					}
					,failure: function(form,action)	{
						oseMsc.formFailureMB(form,action)
					}
				})
			}
		}]
	})

	oseMsc.couponsList = new Ext.grid.GridPanel({
		id: 'coupons-list'
		,title: Joomla.JText._('Coupon_List')
		,sm: oseMsc.coupons.sm
		,cm: oseMsc.coupons.cm
		,store:oseMsc.coupons.store
		,viewConfig: {forceFit: true}
		,height: 500
		,width: '30%'
		,region: 'west'
		,margins: {top:5, right:5, bottom:5, left:3}
		,tbar: oseMsc.tbar
		,bbar: new Ext.PagingToolbar({
            pageSize: 25,
            store: oseMsc.coupons.store,
            displayInfo: true,
            displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}',
			emptyMsg: Joomla.JText._("No_topics_to_display")
        })
	});



	oseMsc.coupons.reader = new Ext.data.JsonReader({
	    root: 'result',
	    totalProperty: 'total',
	    fields:[
		    {name: 'id', type: 'int', mapping: 'id'}
		    ,{name: 'title', type: 'string', mapping: 'title'}
		    ,{name: 'code', type: 'string', mapping: 'code'}
		    ,{name: 'type', type: 'string', mapping: 'type'}
		    ,{name: 'amount', type: 'string', mapping: 'amount'}
		    ,{name: 'amount_left', type: 'string', mapping: 'amount_left'}
		    ,{name: 'amount_infinity', type: 'string', mapping: 'amount_infinity'}
		    ,{name: 'discount', type: 'float', mapping: 'discount'}
		    ,{name: 'range', type: 'string', mapping: 'params.range'}
		    ,{name: 'discount_type', type: 'string', mapping: 'discount_type'}
		    ,{name: 'data', type: 'string', mapping: 'data'}
		    ,{name: 'msc_ids', type: 'string', mapping: 'params.msc_ids'}
		    ,{name: 'currencies', type: 'string', mapping: 'params.currencies'}
	  	]
  	});

	oseMsc.paramCoupons = new Ext.FormPanel({
        title: Joomla.JText._('Coupon_Parameters')
        ,id: 'osemsc--coupon-params'
        ,margins: {top:5, right:5, bottom:5, left:3}
        ,ref: 'form'
        ,height: 430
        ,width: '35%'
        ,border: true
        ,region: 'center'
        ,labelWidth: 170
        ,reader: oseMsc.coupons.reader
        ,bodyStyle: 'padding: 5px'
        ,autoScroll: true
        ,listeners: {
        }
        ,items: [{
        	xtype: 'fieldset'
        	,title: Joomla.JText._('Coupon')
        	,items:[{
	            xtype:'hidden'
	            ,name: 'id'
	        },{
	            xtype:'textfield'
	            ,fieldLabel: Joomla.JText._('Title')
	            ,name: 'title'
	        },{
	            xtype:'textfield'
	            ,fieldLabel: Joomla.JText._('Code')
	            ,name: 'code'
	        },{
	        	xtype: 'multiselect'
	        	,fieldLabel: Joomla.JText._('Multiselect_Required')
	            ,name: 'msc_ids'
	            ,itemId: 'msc_ids'
	            ,width: 250
	            ,height: 150
	            ,autoScroll: true
	            //,allowBlank:false
	            ,store: new Ext.data.Store({
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
			                    id: 'all',
			                    title: Joomla.JText._('All')
			                };
			                var recId = s.getTotalCount(); // provide unique id
			                var p = new s.recordType(defaultData, recId); // create new record

			                s.insert(0,p);

			                oseMsc.paramCoupons.getForm().findField('msc_ids').setValue('all');
				  		}
				  	}
			  		,autoLoad:{}
				})
	            ,valueField: 'id'
			  	,displayField: 'title'
	            ,ddReorder: true
	        },{
	        	fieldLabel: Joomla.JText._('Coupon_Range')
	        	,xtype: 'radiogroup'
        		,name: 'range'
        		,defaults: {name: 'range'}
        		,columns:1
        		,items: [{
        			boxLabel : Joomla.JText._('New_Member_Only')
        			,inputValue: 'new_member_only'
        		},{
        			boxLabel : Joomla.JText._('All_Member_including_existing_members')
        			,inputValue: 'all'
        			,checked: true
        		}]
	        },{
	            xtype: 'checkbox'
	            ,fieldLabel: Joomla.JText._('Number_of_times_the_coupon_can_be_used')
            	,id: 'amount_infinity'
            	,boxLabel: 'Unlimited'
            	,name: 'amount_infinity'
            	,inputValue: '1'
            	,listeners: {
            		check: function(cb,checked){
        				cb.nextSibling().setDisabled(checked);

            		}
            	}
	        },{
	        	fieldLabel: ''
	        	,xtype: 'numberfield'
        		,name: 'amount'
	        },{
	        	fieldLabel: Joomla.JText._('Coupon_Left')
	        	,xtype: 'displayfield'
        		,name: 'amount_left'
	        },{
	        	fieldLabel: Joomla.JText._('Discount')
	        	,xtype: 'numberfield'
        		,name: 'discount'
	        },{
	        	fieldLabel: Joomla.JText._('Discount_type')
	        	,xtype: 'radiogroup'
        		,name: 'discount_type'
        		,defaults: {name: 'discount_type'}
        		,columns:1
        		,items: [{
        			boxLabel : Joomla.JText._('Percentage')
        			,inputValue: 'rate'
        		},{
        			boxLabel : Joomla.JText._('Absolute_amount')
        			,inputValue: 'amount'
        			,checked: true
        		}]
	        },
        	{
	        	xtype: 'multiselect'
	        	,fieldLabel: Joomla.JText._('Currencies_Optional')
	            ,name: 'currencies'
	            ,itemId: 'currencies'
	            ,width: 250
	            ,height: 80
	            ,store: new Ext.data.Store({
					proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc'
			            ,method: 'POST'
		      		})
		      		,baseParams:{controller:'coupons',task: 'getCurrencyList'}
			  		,reader: new Ext.data.JsonReader({
				    	root: 'results'
				    	,totalProperty: 'total'
				  	},[
					    {name: 'value', type: 'string', mapping: 'value'}
					    ,{name: 'title', type: 'string', mapping: 'title'}
				  	])
				  	,listeners:{
				  		load: function(s,r){
				  			var defaultData = {
			                    id: 'all',
			                    title: Joomla.JText._('All')
			                };
			                var recId = s.getTotalCount(); // provide unique id
			                var p = new s.recordType(defaultData, recId); // create new record
			                s.insert(0,p);
			                oseMsc.paramCoupons.getForm().findField('currencies').setValue('all');
				  		}
				  	}
			  		,autoLoad:{}
				})
	            ,valueField: 'value'
			  	,displayField: 'title'
	            ,ddReorder: true
	        }]
        }]
        ,buttons: [{
        	text: Joomla.JText._('Save')
        	,handler: function(){
        		oseMsc.paramCoupons.getForm().submit({
        			url: 'index.php?option=com_osemsc&controller=coupons'
        			,params:{task: 'save'}
        			,success: function(form,action)	{
        				oseMsc.formSuccess(form,action);
        				oseMsc.coupons.store.reload();
        			}
        		})
        	}
        }]
    });


	oseMsc.coupons.historyStore = new Ext.data.Store({
		  proxy: new Ext.data.HttpProxy({
			  	url: 'index.php?option=com_osemsc&controller=coupons',
	            method: 'POST'
	      }),
		  baseParams:{
		  	task: "getCouponHistory",
		  	limit: 25
		  	},
		  reader: new Ext.data.JsonReader({
		              // we tell the datastore where to get his data from
		    root: 'results',
		    totalProperty: 'total'
		  },[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'user_id', type: 'int', mapping: 'user_id'},
		    {name: 'username', type: 'string', mapping: 'username'},
		    {name: 'paid', type: 'string', mapping: 'paid'}
		  ]),
		  listeners: {

			  	beforeload: function(store,records,options)	{
			  		store.setBaseParam('id',oseMsc.coupons.id);

			  	}
		  }
	});

	oseMsc.coupons.historycm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: false
        },
        columns: [
        	new Ext.grid.RowNumberer({header:'#'})
            ,{id: 'id', header: Joomla.JText._('ID'),  hidden:true, dataIndex: 'id', width: 20}
            ,{id: 'user_id', header: Joomla.JText._('UserId'),  hidden:false, dataIndex: 'user_id'}
            ,{id: 'username', header: Joomla.JText._('User_Name'),  hidden:false, dataIndex: 'username'}
            ,{id: 'paid', header: Joomla.JText._('Paid'),  hidden:false, dataIndex: 'paid',
            	renderer: function(val)	{
		           	switch(val)	{
		        	case('1'):
		        		return Joomla.JText._("ose_Yes");
		        	break;
		        	default:
		       		case('0'):
		       			return Joomla.JText._('ose_No');
		       		break;
		        	}
		    	}
            }
        ]
    });

	oseMsc.coupons.historytbar = new Ext.Toolbar({
	    items: ['->', Joomla.JText._('Status'),{
        	xtype:'combo',
            hiddenName: 'paid',
            width:100,
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
		        	['-1', Joomla.JText._('All')],
					['1', Joomla.JText._("Paid")],
		        	['0', Joomla.JText._('UnPaid')]

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
		        	oseMsc.historyPanel.store.reload({
	    				params:{'paid':r.data.value}
	    			});
	    		}
	        }
        }]
	});

	oseMsc.historyPanel = new Ext.grid.GridPanel({
		id: 'oseMsc-history'
		,title: Joomla.JText._('Coupon_History')
		,region: 'east'
		,sm: new Ext.grid.RowSelectionModel()
		,cm: oseMsc.coupons.historycm
		,store:oseMsc.coupons.historyStore
		,viewConfig: {forceFit: true}
		,margins: {top:4, right:5, bottom:4, left:3}
		,width: '30%'
		,heigh: 500
		,tbar: oseMsc.coupons.historytbar
		,bbar: new Ext.PagingToolbar({
            pageSize: 25,
            store: oseMsc.coupons.historyStore
           // displayInfo: true,
            //displayMsg: 'Displaying topics {0} - {1} of {2}',
            //emptyMsg: "No coupons to display"
        })
	})

	oseMsc.panel = new Ext.Panel({
		id: 'oseMsc-panel'
		,border: false
		,layout: 'border'
		,items:[
			oseMsc.couponsList
			,oseMsc.paramCoupons
			,oseMsc.historyPanel
		]
		,height: 800
		,width: '100%'
		,renderTo: 'oseMsc'
	});
})