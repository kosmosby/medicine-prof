Ext.ns('oseMsc','oseMsc.edit');
Ext.QuickTips.init();  
oseMsc.edit.formReader = new Ext.data.JsonReader({
	    root: 'result',
	    totalProperty: 'total',
	    fields:[
		    {name: 'msc_id', type: 'int', mapping: 'id'},
		    {name: 'title', type: 'string', mapping: 'title'},
		    {name: 'name', type: 'string', mapping: 'name'},
		    {name: 'image', type: 'string', mapping: 'image'},
		    {name: 'description', type: 'string', mapping: 'description'},
		    {name: 'showtitle', type: 'int', mapping: 'showtitle'},
		    {name: 'published', type: 'int', mapping: 'published'},
		    {name: 'ordering', type: 'int', mapping: 'ordering'},
		    {name: 'menuid', type: 'int', mapping: 'menuid'}
	  	]
  	});

  	oseMsc.edit.orderCombo = new Ext.form.ComboBox({
  		itemId:'ordering',
        fieldLabel: 'Ordering',
        hiddenName: 'ordering',
        width: '90%',
	    typeAhead: true,
	    triggerAction: 'all',
	    lazyRender:true,
	    mode: 'remote',
	    store: new Ext.data.Store({
			  proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=membership',
		            method: 'POST'
		      }),
			  baseParams:{task: "getOrder", msc_id: ''},
			  reader: new Ext.data.JsonReader({
			    root: 'results',
			    totalProperty: 'total'
			  },[
			    {name: 'Order', type: 'int', mapping: 'ordering'},
			    {name: 'Title', type: 'string', mapping: 'displayText'}
			  ]),
			  sortInfo:{field: 'Order', direction: "ASC"}
			  ,listeners: {
		    	beforeload: function(s)	{
		    		if(oseMscs.gridSm.hasSelection())	{
		    			var selections = oseMscs.gridSm.getSelections();
		        		var mscNode = selections[selections.length-1];
		    			s.setBaseParam('msc_id',mscNode.id);
		        	}
		    	}
		    }
		}),

	    valueField: 'Order',
	    displayField: 'Title',

	    listeners: {
	    	beforequery: function(qe){
	        	if(!oseMscs.gridSm.hasSelection())	{
	    			return false;
	        	}
	        }
		}
  	});

	oseMsc.edit.loginRedirectCombo = new Ext.form.ComboBox({
  		itemId:'menuid',
        fieldLabel: 'Login redirection menu id',
        hiddenName: 'menuid',
        width: '90%',
	    typeAhead: true,
	    triggerAction: 'all',
	    lazyRender:true,
	    mode: 'remote',

	    store: new Ext.data.Store({
			  proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=membership',
		            method: 'POST'
		      }),
			  baseParams:{task: "getloginRedirect", msc_id: ''},
			  reader: new Ext.data.JsonReader({
			    root: 'results',
			    totalProperty: 'total'
			  },[
			    {name: 'MenuId', type: 'int', mapping: 'menuid'},
			    {name: 'Title', type: 'string', mapping: 'displayText'}
			  ]),
			  sortInfo:{field: 'MenuId', direction: "ASC"}
			  ,listeners: {
		    	beforeload: function(s)	{
		    		if(oseMscs.gridSm.hasSelection())	{
		    			var selections = oseMscs.gridSm.getSelections();
		        		var mscNode = selections[selections.length-1];
		    			s.setBaseParam('msc_id',mscNode.id);
		        	}
		    	}
		    }
		}),

	    valueField: 'MenuId',
	    displayField: 'Title',

	    listeners: {
	    	beforequery: function(qe){
	        	if(!oseMscs.gridSm.hasSelection())	{
	    			return false;
	        	}
	        }
		}
  	});

	oseMsc.edit.form = new Ext.form.FormPanel({
		labelAlign: 'top'
	    ,border: false
	    ,bodyStyle:'padding:15px'
		,autoScroll: true
	    ,reader: oseMsc.edit.formReader
	    ,fileUpload: true
	    ,labelWidth: 150
	    ,height: '100%'
	    ,tbar:['->',{
	    	ref:'saveBtn',
	    	text: 'save',
	    	handler: function()	{
	    		Ext.Msg.wait('Loading...')
    			oseMsc.edit.form.getForm().submit({
				    clientValidation: true
				    ,url: 'index.php?option=com_osemsc'
				    ,params: {
				    	controller: 'membership',
				        task: 'update'
				    }
				    ,success: function(form, action) {
				    	var msg = action.result;
				    	oseMsc.msg.setAlert(msg.title,msg.content);
						
						oseMscs.grid.getView().refresh();
				    	oseMscs.grid.getStore().reload();
	    				
				    }
				    ,failure: function(form, action) {
				        oseMsc.formFailureMB(form, action)
				    }
    			})
    			Ext.Msg.hide()
    		}
	    }]
	    ,listeners: {
	    	added: function(p)	{
	    		p.getForm().load({
	    			url: 'index.php?option=com_osemsc'
	    			,params: {controller: 'membership',task: 'getItem', msc_id: oseMscs.msc_id}
	    			,success: function(form,action)	{
	    				var img = action.result.data.image;
	    				Ext.getCmp('images-view').getEl().set({'src':img})
	    			}
	    		})
	    	}
	    }
	    ,items:[{
	    	layout: 'hbox'
	    	,height: 150
	    	,border: false
	    	,defaults: {border: false}
	    	,items: [{
	    		xtype: 'panel'
	    		,layout: 'form'
	    		,width: 400
	    		,items: [{
		    		itemId:'title'
		        	,xtype:'textfield'
	                ,fieldLabel: 'Title'
	                ,name: 'title'
	                ,width: 200
	                ,allowBlank: false
		    	},{
	    			itemId:'image'
		        	,xtype:'fileuploadfield'
	                ,fieldLabel: 'Image'
	                ,name: 'image'
	                ,buttonOnly: true
		            ,buttonText: 'Upload image'
		            ,buttonCfg: {
		                width: 100
		            }
	                ,listeners: {
		            	fileselected: function(fb, v){
		            		oseMsc.edit.form.getForm().submit({
		            			url:'index.php?option=com_osemsc&controller=membership',
		            			params:{task:'preview'},
		            			success:function(form,action)	{
		            				var msg = action.result;
		            				if(msg.uploaded){
		            					Ext.getCmp('images-view').getEl().set({'src':msg.img_path});
		            				}	else	{
		            					Ext.Msg.alert(msg.title,msg.content);
		            				}
		            			},
		            			failure:function(form,action){
		            				var msg = action.result;
		            				Ext.Msg.alert('Error','Failed!');
		            			}
		            		});
		            	}
		            }
	    		},{
			    	itemId:'show-title-only',
			    	xtype:'checkbox',
			    	hideLabel: true,
			    	hidden: true,
			        boxLabel: 'Show Title Only',
			        name: 'showtitle',
			        inputValue: 1,
			        //anchor:'95%',
			    },{
			    	itemId:'published'
			    	,xtype:'checkbox'
			    	,hideLabel: true
			        ,boxLabel: 'Published'
			        ,name: 'published'
			        ,inputValue: 1
			    }]
	    	},{
	    		height: 150
		    	,width: 150
	    		,items:{
		    		xtype: 'box'
		    		,id: 'images-view'
		    		,autoEl: {
		    			tag: 'img'
		    		}
	    		}
	    	}]
	    },{
	    	layout: 'hbox'
	    	,border: false
	    	,defaults: {layout: 'form', width: 300, border: false}
	    	,bodyStyle: 'padding-top : 20px'
	    	,items: [{
	    		items:[
			    oseMsc.edit.orderCombo
			    ]
	    	},{
	    		items:[
			    oseMsc.edit.loginRedirectCombo
			    ]
	    	}]
	    },{
	       	xtype:'htmleditor'
	        ,id:'description'
	        ,name: 'description'
	        ,fieldLabel:'Description'
	        ,height: 200
	        ,width:  600
	    },{
	       id:'ose-msc-basicinfo-tips',
	       html: "<p class='tips'>[tips: Please use the {readmore} tag to separate intro text from the core description]</p>",
	       width: 600,
	       border: 0,
	    }
	    ,{
        	id:'msc-id',
        	xtype:'hidden',
            name: 'msc_id',
            value: ''
        },{
        	itemId:'name',
        	xtype:'hidden',
            name: 'name',
            hidden:true
        }]
	});