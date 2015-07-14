Ext.ns('oseMscAddon','oseMscAddon.basicParams');
	oseMscAddon.basicParams.openUploadImageWin = function()	{
		if(!uploadImgWin)	{
			var uploadImgWin = new Ext.Window({
				title: Joomla.JText._('Upload_Image')
				,bodyStyle: ''
				,width: 500
				,autoHeight: true
				,modal: true
				,items:[{
					xtype: 'form'
					,ref: 'form'
					,fileUpload: true
					,height: 300
					,border: false
					,defaults: {border: false}
					,buttons: [{
						text: Joomla.JText._('Ok')
						,handler: function()	{
							uploadImgWin.close()
						}
					},{
						text: Joomla.JText._('Cancel')
						,handler: function()	{
							oseMscAddon.basic.fireEvent('added',oseMscAddon.basic)
							uploadImgWin.close()
						}
					}]
					,items:[{
						layout: 'hbox'
						,defaults: {border: false}
						,hideLabel: true
						,items:[{
							itemId:'image'
				        	,xtype:'fileuploadfield'
			                ,fieldLabel: Joomla.JText._('Image')
			                ,height: 30
			                ,name: 'image'
			                ,width: 150
			                ,buttonOnly: true
				            ,buttonText: Joomla.JText._('Upload_image')
					        ,buttonCfg: {
				            	width: 100
				            }
			                ,listeners: {
				            	fileselected: function(fb, v){
				            		uploadImgWin.form.getForm().submit({
				            			url:'index.php?option=com_osemsc&controller=membership',
				            			params:{task:'preview'},
				            			success:function(form,action)	{
				            				var msg = action.result;

				            				if(msg.uploaded){
				            					uploadImgWin.form.findById('upload-images-view').getEl().set({'src':msg.img_path});
				            					oseMscAddon.basic.findById('images-view').getEl().set({'src':msg.img_path});
				            					oseMscAddon.basic.getForm().findField('image').setValue(msg.img_path);
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
							xtype: 'button'
							,text: Joomla.JText._('Reset')
							,bodyStyle: 'margin-left: 30px;margin-top: 5px;margin-right: 5px'
							,handler: function()	{
								uploadImgWin.form.findById('upload-images-view').getEl().set({'src':''}); 
								oseMscAddon.basic.fireEvent('added',oseMscAddon.basic)          	
							}
						},{
							html: Joomla.JText._('Preview')
							,bodyStyle: 'margin-left: 30px;margin-top: 5px;margin-right: 5px'
						},{
							height: 150
					    	,width: 150
					    	,border: true
					    	,bodyStyle: 'margin-top: 5px'
				    		,items:{
					    		xtype: 'box'
					    		,id: 'upload-images-view'
					    		,name: 'images-view'
					    		,autoEl: {
					    			tag: 'img'
					    		}
				    		}
						}]
					}]
				}]
			})
		}

		uploadImgWin.show().alignTo(Ext.getBody(),'c-c')
	}


	oseMscAddon.basicParams.formReader = new Ext.data.JsonReader({
	    root: 'result',
	    totalProperty: 'total',
	    fields:[
		    {name: 'msc_id', type: 'int', mapping: 'id'}
		    ,{name: 'title', type: 'string', mapping: 'title'}
		    ,{name: 'name', type: 'string', mapping: 'name'}
		    ,{name: 'image', type: 'string', mapping: 'image'}
		    ,{name: 'description', type: 'string', mapping: 'description'}
		    ,{name: 'showtitle', type: 'int', mapping: 'showtitle'}
		    ,{name: 'published', type: 'int', mapping: 'published'}
		    ,{name: 'ordering', type: 'int', mapping: 'ordering'}
		    ,{name: 'menuid', type: 'int', mapping: 'menuid'}
		    ,{name: 'after_payment_menuid', type: 'string', mapping: 'params.after_payment_menuid'}
	  	]
  	});

  	oseMscAddon.basicParams.orderCombo = new Ext.form.ComboBox({
  		itemId:'ordering'
        ,fieldLabel: Joomla.JText._('Ordering')
        ,hiddenName: 'ordering'
        ,editable: false
	    ,typeAhead: true
	    ,triggerAction: 'all'
	    ,lazyRender:true
	    ,mode: 'remote'
	    ,lastQuery: ''
	    ,store: new Ext.data.Store({
			  proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=membership'
		            ,method: 'POST'
		      })
			  ,baseParams:{task: "getOrder", msc_id: oseMscs.msc_id}
			  ,reader: new Ext.data.JsonReader({
			    root: 'results'
			    ,totalProperty: 'total'
			  },[
			    {name: 'Order', type: 'int', mapping: 'ordering'}
			    ,{name: 'Title', type: 'string', mapping: 'displayText'}
			  ])
			  ,sortInfo:{field: 'Order', direction: "ASC"}
			  ,listeners: {
		    	beforeload: function(s)	{
		    	}
		    }
		    ,autoLoad:{}
		})

	    ,valueField: 'Order'
	    ,displayField: 'Title'
  	});

	oseMscAddon.basicParams.loginRedirectCombo = new Ext.form.ComboBox({
  		itemId:'menuid'
  		,width: 500
        ,fieldLabel: Joomla.JText._('Login_redirection_menu_id')
        ,editable: false
        ,hiddenName: 'menuid'
	    ,typeAhead: true
	    ,triggerAction: 'all'
	    ,lazyRender:true
	    ,mode: 'remote'
		,lastQuery: ''
	    ,store: new Ext.data.Store({
			  proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=membership',
		            method: 'POST'
		      }),
			  baseParams:{task: "getloginRedirect", msc_id: oseMscs.msc_id},
			  reader: new Ext.data.JsonReader({
			    root: 'results'
			    ,totalProperty: 'total'
			    ,idProperty: 'menuid'
			  },[
			    {name: 'MenuId', type: 'int', mapping: 'menuid'},
			    {name: 'Title', type: 'string', mapping: 'displayText'}
			  ])
			  ,sortInfo:{field: 'MenuId', direction: "ASC"}
			  ,listeners: {
		    	beforeload: function(s)	{
		    	}
		    }
		    ,autoLoad:{}
		})
		//,stripCharsRe: /<[^>]*[^\/]>\|&mdash;<[^>]*[^\/]>/g
	    ,valueField: 'MenuId'
	    ,displayField: 'Title'
  	});

	oseMscAddon.basicParams.afterPaymentRedirectCombo = new Ext.form.ComboBox({
  		itemId:'after_payment_menuid'
  		,width: 500
        ,fieldLabel: Joomla.JText._('AFTER_PAYMENT_REDIRECTION_MENU_ID_IF_YOU_DO_NOT_HAVE_ANY_SPECIFIC_REQUIREMENT_JUST_LEAVE_EMPTY')
        ,editable: false
        ,emptyText: '0'
        ,hiddenName: 'after_payment_menuid'
	    ,typeAhead: true
	    ,triggerAction: 'all'
	    ,lazyRender:true
	    ,mode: 'local'
		,lastQuery: ''
	    ,store: new Ext.data.Store({
			  proxy: new Ext.data.HttpProxy({
		            url: 'index.php?option=com_osemsc&controller=membership',
		            method: 'POST'
		      }),
			  baseParams:{task: "getloginRedirect", msc_id: oseMscs.msc_id},
			  reader: new Ext.data.JsonReader({
			    root: 'results'
			    ,totalProperty: 'total'
			    ,idProperty: 'menuid'
			  },[
			    {name: 'MenuId', type: 'int', mapping: 'menuid'},
			    {name: 'Title', type: 'string', mapping: 'displayText'}
			  ])
			  ,sortInfo:{field: 'MenuId', direction: "ASC"}
			  ,listeners: {
		    	beforeload: function(s)	{
		    	}
		    }
		    ,autoLoad:{}
		})
		//,stripCharsRe: /<[^>]*[^\/]>\|&mdash;<[^>]*[^\/]>/g
	    ,valueField: 'MenuId'
	    ,displayField: 'Title'
  	});
	oseMscAddon.basic = new Ext.form.FormPanel({
	    border: false
	    ,bodyStyle:'padding:30px'
		,autoScroll: true
	    ,reader: oseMscAddon.basicParams.formReader
	    ,labelWidth: 200
	    ,buttons:[{
	    	ref:'saveBtn',
	    	text: Joomla.JText._('save'),
	    	handler: function()	{
	    		Ext.Msg.wait(Joomla.JText._('Loading'))
    			oseMscAddon.basic.getForm().submit({
				    clientValidation: true
				    ,url: 'index.php?option=com_osemsc&controller=membership'
				    ,params: {
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
	    		Ext.Msg.wait(Joomla.JText._('Initializing'),Joomla.JText._('Please_Wait'));
	    		p.getForm().load({
	    			url: 'index.php?option=com_osemsc&controller=membership'
	    			//,waitMsg: 'Please wait...'
	    			,params: {task: 'getItem', msc_id: oseMscs.msc_id}
	    			,success: function(form,action)	{
	    				Ext.Msg.hide();
	    				var img = action.result.data.image;
	    				Ext.getCmp('images-view').getEl().set({'src':img})

	    				var menu = form.findField('menuid');

	    				menu.fireEvent('beforeselect',menu,menu.getStore().getById(menu.getValue()));
	    			}
	    		})
	    	}
	    }
	    ,items:[{
	    	layout: 'hbox'
	    	//,width:
	    	,height: 120
	    	,border: false
	    	,defaults: {border: false,labelWidth: 200}
	    	,items: [{
	    		xtype: 'panel'
	    		,layout: 'form'
	    		,width: 500
	    		,items: [{
		    		itemId:'title'
		        	,xtype:'textfield'
	                ,fieldLabel: Joomla.JText._('Title')
	                ,name: 'title'
	                ,width: 200
	                ,allowBlank: false
		    	},{
		    		xtype: 'button'
		    		,text: Joomla.JText._('Upload_Image')
		    		,fieldLabel: Joomla.JText._('Image')
		    		,handler: function()	{
		    			oseMscAddon.basicParams.openUploadImageWin()
		    		}
	    		},{
			    	itemId:'published'
			    	,xtype:'checkbox'
			        ,fieldLabel: Joomla.JText._('Published')
			        ,name: 'published'
			        ,inputValue: 1
			    }]
	    	},{
	    		height: 150
		    	,width: 150
		    	,bodyStyle: 'margin-left: 30px'
	    		,items:{
		    		xtype: 'box'
		    		,id: 'images-view'
		    		,autoEl: {
		    			tag: 'img'
		    		}
	    		}
	    	}]
	    },oseMscAddon.basicParams.orderCombo
	    ,oseMscAddon.basicParams.loginRedirectCombo
	    ,oseMscAddon.basicParams.afterPaymentRedirectCombo
	    ,{
	       	xtype:'tinymce'
	        ,id:'description'
	        ,name: 'description'
	        ,fieldLabel:Joomla.JText._('Description')
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
		        extended_valid_elements: "a[name|href|target|title|onclick],img[style|class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
		        template_external_list_url: "example_template_list.js"
		    }
	    },{
	       id:'ose-msc-basicinfo-tips'
	       ,xtype: 'displayfield'
	       ,border: true
	       ,value: "<p class='tips'>"+Joomla.JText._('TIPS_PLEASE_USE_THE_READMORE_TAG_TO_SEPARATE_INTRO_TEXT_FROM_THE_CORE_DESCRIPTION')+"</p>"
	       ,width: 600
	    },{
        	id:'msc-id',
        	xtype:'hidden',
            name: 'msc_id',
            value: ''
        },{
        	itemId:'name',
        	xtype:'hidden',
            name: 'name',
            hidden:true
        },{
        	itemId:'name',
        	xtype:'hidden',
            name: 'image'
        }]

	});