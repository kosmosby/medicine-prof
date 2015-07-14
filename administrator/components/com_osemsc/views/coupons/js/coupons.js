Ext.ns('oseMsc','oseMsc.coupons');

Ext.onReady(function(){
	Ext.QuickTips.init();
	oseMsc.msg = new Ext.App();
	oseMsc.success = function(form,action)	{
        var msg = action.result;
		oseMsc.msg.setAlert(msg.title,msg.content);
	}
	oseMsc.failure = function(form,action)	{
		if (action.failureType === Ext.form.Action.CLIENT_INVALID){
			oseMsc.msg.setAlert('Notice','Pleas check the notice in the form');
        }

		if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
           Ext.Msg.alert('Error',
            'Status:'+action.response.status+': '+
            action.response.statusText);

        }

        if (action.failureType === Ext.form.Action.SERVER_INVALID){
            var msg = action.result;
			oseMsc.msg.setAlert(msg.title,msg.content);
        }
	}

	oseMsc.coupons.sm = new Ext.grid.CheckboxSelectionModel({
		single: true
		,listeners:{
			rowselect: function(sel,i,r)	{
				oseMsc.paramPanel.fireEvent('reload',oseMsc.paramPanel,r.id,r.data.type);
				oseMsc.coupons.id = r.id;
				oseMsc.coupons.type = r.data.type;
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
            ,{id: 'id', header: 'ID',  hidden:false, dataIndex: 'id', width: 20}
            ,{id: 'code', header: 'Coupon Code',  hidden:false, dataIndex: 'code'}
            ,{id: 'discount', header: 'Discount',  hidden:false, dataIndex: 'discount'}
            ,{id: 'discount_type', header: 'Discount Type',  hidden:false, dataIndex: 'discount_type'}
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
		    {name: 'discount', type: 'int', mapping: 'discount'},
		    {name: 'discount_type', type: 'string', mapping: 'discount_type'}
		  ]),
		  autoLoad:{}
	});

	oseMsc.couponsList = new Ext.grid.GridPanel({
		id: 'coupons-list'
		,title: 'Coupon List'
		,sm: oseMsc.coupons.sm
		,cm: oseMsc.coupons.cm
		,store:oseMsc.coupons.store
		,viewConfig: {forceFit: true}
		,height: 500
		,width: 500
		,region: 'west'
		,margins: {top:5, right:5, bottom:5, left:3}
		,bbar: new Ext.PagingToolbar({
            pageSize: 25,
            store: oseMsc.coupons.store,
            displayInfo: true,
            displayMsg: 'Record {0} - {1} of {2}',
            emptyMsg: "No coupons to display",
            items:[ ]
        })
	});

	oseMsc.tbar = new Ext.Panel({
		layout: 'toolbar'
		,border: false
		,region: 'north'
		//,defaults:{bodyStyle: 'padding: 5px'}
		,items:[{
			xtype: 'button'
			,text: 'New'
			,handler:function(){
				if(!addWin)	{
					var addWin = new Ext.Window({
						title: 'New Coupon'
						,modal: true
						,items: [{
							xtype: 'form'
							,width: 350
							,bodyStyle: 'padding: 10px'
							,items:[{
								xtype: 'textfield'
								,fieldLabel: 'Title'
								,name: 'title'
								,emptyValue: 'New Coupon'
								,allowBlank: false
							},{
								xtype: 'combo'
								,fieldLabel: 'Type'
								,hiddenName: 'type'
								,typeAhead: true
							    ,triggerAction: 'all'
							    ,lazyRender:false
							    ,mode: 'local'
							    ,store: new Ext.data.ArrayStore({
							        id: 0
							        ,fields: [
							            'value'
							            ,'displayText'
							        ]
							        ,data: [
							        	['License', 'License']
							        	,['Coupon', 'Coupon']
							        	,['CS', 'Corporation Staff']
							        ]
							    })

							    ,valueField: 'value'
							    ,displayField: 'displayText'
							    ,forceSelection: true
							}]

							,buttons: [{
								text: 'Save'
								,handler: function(b){
									b.ownerCt.ownerCt.getForm().submit({
										clientValidation: true
										,url: 'index.php?option=com_oselic&controller=license',
										params: {task: 'add'}
										,success: function(form,action)	{
											oseMsc.success(form,action);
											oseMsc.couponsList.getStore().reload();
											oseMsc.couponsList.getView().refresh();
											addWin.close();
										}
										,failure: function(form,action)	{
											oseLic.failure(form,action)
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
			text: 'Remove'
			,xtype: 'button'
			,handler: function(b){
				oseMsc.paramPanel.form.getForm().submit({
					clientValidation: true
					,url: 'index.php?option=com_oselic&controller=license',
					params: {task: 'remove'}
					,success: function(form,action)	{
						oseMsc.success(form,action);
						oseMsc.couponsList.getStore().reload();
						oseMsc.couponsList.getView().refresh();

					}
					,failure: function(form,action)	{
						oseLic.failure(form,action)
					}
				})
			}
		}]
		,height: 30
	})

oseMsc.coupons.reader = new Ext.data.JsonReader({
	    root: 'result',
	    totalProperty: 'total',
	    fields:[
		    {name: 'id', type: 'int', mapping: 'id'},
		    {name: 'code', type: 'string', mapping: 'code'},
		    {name: 'amount', type: 'string', mapping: 'amount'},
		    {name: 'discount_amount', type: 'float', mapping: 'discount_amount'},
		    {name: 'discount_type', type: 'string', mapping: 'discount_type'}
	  	]
  	});

	oseMsc.paramCoupons = new Ext.FormPanel({
        frame:false
        ,ref: 'form'
        ,border:false
        ,layout:'form'
        ,labelWidth: 200
        ,reader: oseMsc.coupons.reader
        ,bodyStyle: 'padding: 10px'
        ,listeners: {
        	render: function(p){
        		p.load({
        			url: 'index.php?option=com_osemsc',
        			params: {
        			controller: 'coupons',
        			task: "getCouponsParams",
        			id: oseMsc.coupons.id,
        			type:'coupons'}
        		})
        	}
        }
        ,items: [{
        	xtype: 'fieldset'
        	,title: 'Coupon'
        	,items:[{
	            xtype:'hidden'
	            ,name: 'id'
	        },{
	            xtype:'textfield'
	            ,fieldLabel: 'Code'
	            ,name: 'code'
	            ,id: 'code'
	        },{
	            xtype: 'checkbox'
	            ,fieldLabel: 'Number of sub-licenses'
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
	        	,xtype: 'textfield'
        		,name: 'amount'
	        },
	        {
	        	fieldLabel: 'Discount'
	        	,xtype: 'textfield'
        		,name: 'discount_amount'
	        },{
	        	fieldLabel: 'Discount type'
	        	,xtype: 'radiogroup'
        		,name: 'discount_type'
        		,defaults: {name: 'discount_type'}
        		,columns:1
        		,items: [{
        			boxLabel : 'Percentage'
        			,inputValue: 'percentage'
        		},{
        			boxLabel : 'Absolute amount'
        			,inputValue: 'amount'
        			,checked: true
        		}]
	        }]
        }]

        ,buttons: [{
        	text: 'Save'
        	,handler: function(){
        		oseMsc.paramCoupons.getForm().submit({
        			url: 'index.php?option=com_oselic'
        			,params:
        			{
	        			task: 'saveLicense',
	        			controller: 'license'
        			}
        			,success: function(form,action)	{
        				var msg = action.result;
        				oseMsc.msg.setAlert(msg.title,msg.content);
        			}
        		})
        	}
        },{
        	text: 'Synchronize '
        	,handler: function(){
        		oseLic.paramCS.getForm().submit({
        			url: 'index.php?option=com_oselic&controller=license'
        			,params: {task: 'syncCS'}
        			,success: function(form,action)	{
        				var msg = action.result;
        				oseLic.msg.setAlert(msg.title,msg.content);
        			}
        		})
        	}
        }]
    });
	oseMsc.paramPanel = new Ext.Panel({
		id: 'oseMsc-params'
		,title: 'Coupon Parameters'
		,region: 'center'
		,margins: {top:5, right:5, bottom:5, left:3}
		,listeners: {
			reload: function(e,id,type)	{
				oseMsc.coupons.id = id;
				oseMsc.coupons.type = type;
				var array = e.findByType('form');
				if(array.length > 0)	{
					Ext.each(array,function(item,i,all)	{
						e.remove(item);
					})
				}
				e.load({
					url: 'index.php?option=com_osemsc'
					,params: {
						task: 'getCouponsParams',
						type: type,
						controller: 'coupons',
						id: oseMsc.coupons.id
						}
					,scripts: true
					,callback: function(el,success,response,opt)	{
						if(eval('oseMsc.paramCoupons'))	{
							oseMsc.paramPanel.add(eval('oseMsc.paramCoupons'));
							oseMsc.paramPanel.doLayout();
						}
					}
				});
			}
		}
	})


oseMsc.historyPanel = new Ext.Panel({
		id: 'oseMsc-history'
		,title: 'Coupon History'
		,region: 'east'
		,margins: {top:5, right:5, bottom:5, left:3}
		,width: 300
})

	oseMsc.panel = new Ext.Panel({
		id: 'oseMsc-panel'
		,border: false
		,layout: 'border'
		,items:[
			oseMsc.tbar
			,oseMsc.couponsList
			,oseMsc.paramPanel
			,oseMsc.historyPanel
		]
		,height: 550
		,width: '100%'
		,renderTo: 'oseMsc'
	});
})