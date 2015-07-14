Ext.ns('oseMscAddon');
Ext.ns('osemscProfile');
oseMscAddon.profile = function()	{

}
Ext.override(Ext.menu.Menu, {  
    autoWidth: function() {  
        this.width += "200px";  
    }  
	,autoHeight: function() {  
        this.height += "200px";  
    } 
});
osemscProfile.date = function(fname,pid,blank,note,value){
	var fieldname = osemscProfile.date+fname;
	if(note != '')
	{
		hide = false;
	}else{
		hide = true;
	}
	fieldname = new Ext.form.CompositeField({
		fieldLabel:fname
		,defaults: {width: 280,msgTarget : 'side'}
		,items:[{
			name: 'profile_'+pid
		  	,xtype: 'datefield'
		  	,allowBlank: blank
		  	,listeners: {
				render: function(p){
					if(value != null)
					{
						p.setValue(value);
					}					
				}
			}
         },{
    		xtype: 'box'
    		,hidden:hide
    		,autoEl: {
    			tag: 'div'
      			,style:"height: 10px;width: 15px; padding: 3px 0; float:left;background-image: url('./components/com_osemsc/assets/images/profile-info.png');"				
     		}
    		,listeners:{
    			render: function(b)	{
    				Ext.QuickTips.register({
					    target: b.getEl(),
					    anchor: 'right',
					    text: note,
					    width: 250
					});
    			}
    		}
    	}]
	});

	return fieldname;
};

osemscProfile.FileUpload = function(cname,pid,blank,note,value)	{

	var FileUpladname = osemscProfile.fileupload+cname;
	if(note != '')
	{
		hide = false;
	}else{
		hide = true;
	}
	FileUpladname = new Ext.form.CompositeField({
		fieldLabel: cname
		,items:[{
    		xtype: 'button'
	    	,text: 'Upload'
	    	,handler: function()	{
	    		if(!uploadImgWin)	{
	    			var uploadImgWin = new Ext.Window({
	    				title: 'Upload File'
	    				,bodyStyle: ''
	    				,width: 300
	    				,autoHeight: true
	    				,modal: true
	    				,items:[{
	    					xtype: 'form'
	    					,ref: 'form'
	    					,fileUpload: true
	    					,height: 100
	    					,border: false
	    					,defaults: {border: false}
	    					,items:[{
	    						layout: 'hbox'
	    						,defaults: {border: false}
	    						,hideLabel: true
	    						,items:[{
	    							xtype:'fileuploadfield'
	    			                ,height: 30
	    			                ,name: 'file'
	    			                ,width: 150
	    			                ,buttonOnly: true
	    				            ,buttonText: 'Browse'
	    					        ,buttonCfg: {
	    				            	width: 100
	    				            }
	    			                ,listeners: {
	    				            	fileselected: function(fb, v){
	    				            		
	    				            		Ext.each(oseMscAddon.pform.findByType('compositefield'), function(item,i,all){
		    				    	    		Ext.each(item.items.items, function(subitem,i,all){
		    				    	    			if(subitem.getXType() == 'textfield' && subitem.id == ('profile_'+pid) && subitem.getValue() != '')	{
		    				    	    				var path = subitem.getValue();
		    				    	    				Ext.Ajax.request({
		    				    	    					url: 'index.php?option=com_osemsc&controller=register',
		    				    	    					params: {task: "action",action:'register.profile.reset','file_path':path}
		    				    	    				});
		    				    	    				subitem.setValue('');
		    						    			}
		    				    				})
		    				    	    	});
	    				            		
	    				            		uploadImgWin.form.getForm().submit({
	    				            			url:'index.php?option=com_osemsc&controller=register',
	    				            			params:{task: "action",action:'register.profile.upload','pid':pid},
	    				            			success:function(form,action)	{
	    				            				var msg = action.result;
	    				            				if(msg.uploaded){
	    				            					Ext.each(oseMscAddon.pform.findByType('compositefield'), function(item,i,all){
	    			    				    	    		Ext.each(item.items.items, function(subitem,i,all){
	    			    				    	    			if(subitem.getXType() == 'textfield' && subitem.id == ('profile_'+pid))	{
	    			    				    	    				subitem.setValue(msg.file_path);
	    			      						    			}
	    			    				    				})
	    			    				    	    	});
	    				            					oseMsc.msg.setAlert(msg.title,msg.content);
	    				            				}	else	{
	    				            					Ext.Msg.alert(msg.title,msg.content);
	    				            				}
	    				            				//uploadImgWin.close();
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
	    				    	    ,text: 'Reset'
	    				    	    ,handler: function()	{
	    				    	    	Ext.each(oseMscAddon.pform.findByType('compositefield'), function(item,i,all){
	    				    	    		Ext.each(item.items.items, function(subitem,i,all){
	    				    	    			if(subitem.getXType() == 'textfield' && subitem.id == ('profile_'+pid))	{
	    				    	    				var path = subitem.getValue();
	    				    	    				Ext.Ajax.request({
	    				    	    					url: 'index.php?option=com_osemsc&controller=register',
	    				    	    					params: {task: "action",action:'register.profile.reset','file_path':path},

	    				    	    				    success: function(response, opt) {
	    				    	    						var res = Ext.decode(response.responseText);
	    				    	    						oseMsc.msg.setAlert(res.title,res.content);

	    				    	    				    }
	    				    	    				});
	    				    	    				subitem.setValue('');
	    						    			}
	    				    				})
	    				    	    	});
	    				    	    }
	    						}]
	    					}]
	    				}]
	    			})
	    		}

	    		uploadImgWin.show().alignTo(Ext.getBody(),'c-c')
	    	}
    	},{
    		xtype:'textfield'
    		,hidden:true	
        	,id:'profile_'+pid
        	,name:'profile_'+pid
        	,listeners: {
				render: function(p){
					if(value != null)
					{
						p.setValue(value);
					}					
				}
			}
        },{
        	xtype: 'box'
        	,hidden:hide
        	,autoEl: {
        		tag: 'div'
        		,style:"height: 10px;width: 15px; padding: 3px 0; float:left;background-image: url('./components/com_osemsc/assets/images/profile-info.png');"
         	}
        	,listeners:{
        		render: function(b)	{
        			Ext.QuickTips.register({
					    target: b.getEl(),
					    anchor: 'right',
					    text: note,
					    width: 250
					});
        		}
        	}
        }]
	});
	return FileUpladname;
};
	
osemscProfile.MS = function(fname,pid,blank,note,value)	{

	var fieldname = osemscProfile.multiSelect+fname;
	if(note != '')
	{
		hide = false;
	}else{
		hide = true;
	}
	fieldname = new Ext.form.CompositeField({
		fieldLabel:fname
		,defaults: {width: 280,msgTarget : 'side'}
		,items:[{
			id:'profile_'+pid
		    ,name: 'profile_'+pid
		    ,height: 150
		  	,xtype: 'multiselect'
		  	,allowBlank: blank
		  	,store: new Ext.data.Store({
				  proxy: new Ext.data.HttpProxy({
			            url: 'index.php?option=com_osemsc&controller=register'
			            ,method: 'POST'
			      })
		    	,baseParams:{task: "action",action:'register.profile.getOptions',id:pid}
				  ,reader: new Ext.data.JsonReader({
				    root: 'results'
				    ,totalProperty: 'total'
				  },[
				    {name: 'option', type: 'string', mapping: 'option'}
				  ])
				  ,autoLoad:{}
				  ,listeners:{
					  load: function(s,r){
						  
						  if(value != null)
						  {
					  		Ext.each(oseMscAddon.pform.findByType('compositefield'), function(item,i,all){
				    	   		Ext.each(item.items.items, function(subitem,i,all){
				    	   			if(subitem.getXType() == 'multiselect' && subitem.id == ('profile_'+pid))	{
				    	   				subitem.setValue(value);
						   			}
				    			})
				    	   	});
						  }	
					  }	  				  		
				  }
			})
		    ,valueField: 'option'
		    ,displayField: 'option'

		    ,tbar:[{
		        text: Joomla.JText._('Reset'),
		        handler: function(){
		        	Ext.each(oseMscAddon.pform.findByType('compositefield'), function(item,i,all){
	    	    		Ext.each(item.items.items, function(subitem,i,all){
	    	    			if(subitem.getXType() == 'multiselect' && subitem.id == ('profile_'+pid))	{
	    	    				subitem.reset();
			    			}
	    				})
	    	    	});
		        }
		    }]
         },{
    		xtype: 'box'
    		,hidden:hide
    		,autoEl: {
    			tag: 'div'
    			,style:"height: 10px;width: 15px; padding: 3px 0; float:left;background-image: url('./components/com_osemsc/assets/images/profile-info.png');"
     		}
    		,listeners:{
    			render: function(b)	{
    				Ext.QuickTips.register({
					    target: b.getEl(),
					    anchor: 'right',
					    text: note,
					    width: 250
					});
    			}
    		}
    	}]
	});
	return fieldname;
	
	};

	osemscProfile.textarea = function(fname,pid,blank,note,value){
		var fieldname = osemscProfile.textarea+fname;
		if(note != '')
		{
			hide = false;
		}else{
			hide = true;
		}
		fieldname = new Ext.form.CompositeField({
			fieldLabel:fname
			,defaults: {width: 280,msgTarget : 'side'}
			,items:[{
				name: 'profile_'+pid
			  	,xtype: 'textarea'
			  	,allowBlank: blank
			  	,listeners: {
					render: function(p){
						if(value != null)
						{
							p.setValue(value);
						}					
					}
				}
	         },{
        		xtype: 'box'
        		,hidden:hide
        		,autoEl: {
        			tag: 'div'
        			,style:"height: 10px;width: 15px; padding: 3px 0; float:left;background-image: url('./components/com_osemsc/assets/images/profile-info.png');"
        				
         		}
        		,listeners:{
        			render: function(b)	{
        				Ext.QuickTips.register({
						    target: b.getEl(),
						    anchor: 'right',
						    text: note,
						    width: 250
						});
        			}
        		}
        	}]
		});
		return fieldname;
	};

	osemscProfile.combo = function(fname,pid,blank,note,value)	{

		var fieldname = osemscProfile.combo+fname;
		if(note != '')
		{
			hide = false;
		}else{
			hide = true;
		}
		fieldname = new Ext.form.CompositeField({
			fieldLabel:fname
			,defaults: {width: 280,msgTarget : 'side'}
			,items:[{
				hiddenName: 'profile_'+pid
			  	,xtype: 'combo'
			  	,typeAhead: true
				,triggerAction: 'all'
				,lazyRender:true
				,mode: 'remote'
				,allowBlank:blank
				,lastQuery: ''
				,store: new Ext.data.Store({
					  proxy: new Ext.data.HttpProxy({
				            url: 'index.php?option=com_osemsc&controller=register'
				            ,method: 'POST'
				      })
				   	,baseParams:{task: "action",action:'register.profile.getOptions',id:pid}
					  	,reader: new Ext.data.JsonReader({
					    root: 'results'
					    ,totalProperty: 'total'
					  },[
					    {name: 'option', type: 'string', mapping: 'option'}
					  ])
					  ,autoLoad:{}
				})

				,valueField: 'option'
				,displayField: 'option'
				,listeners: {
					render: function(p){
						if(value != null)
						{
							p.setValue(value);
						}					
					}
				}
	         },{
        		xtype: 'box'
        		,hidden:hide
        		,autoEl: {
        			tag: 'div'
        			,style:"height: 10px;width: 15px; padding: 3px 0; float:left;background-image: url('./components/com_osemsc/assets/images/profile-info.png');"
        				
         		}
        		,listeners:{
        			render: function(b)	{
        				Ext.QuickTips.register({
						    target: b.getEl(),
						    anchor: 'right',
						    text: note,
						    width: 250
						});
        			}
        		}
        	}]
		});
		return fieldname;

	};

	osemscProfile.textfield = function(fname,pid,blank,note,value){
		var fieldname = osemscProfile.textfield+fname;
		if(note != '')
		{
			hide = false;
		}else{
			hide = true;
		}
		fieldname = new Ext.form.CompositeField({
			fieldLabel:fname
			,defaults: {width: 280,msgTarget : 'side'}
			,items:[{
				name: 'profile_'+pid
			  	,xtype: 'textfield'
			  	,allowBlank: blank
			  	,listeners: {
					render: function(p){
						if(value != null)
						{
							p.setValue(value);
						}					
					}
				}
	         },{
        		xtype: 'box'
        		,hidden:hide
        		,autoEl: {
        			tag: 'div'
        			,style:"height: 10px;width: 15px; padding: 3px 0; float:left;background-image: url('./components/com_osemsc/assets/images/profile-info.png');"
        				
         		}
        		,listeners:{
        			render: function(b)	{
        				Ext.QuickTips.register({
						    target: b.getEl(),
						    anchor: 'right',
						    text: note,
						    width: 250
						});
        			}
        		}
        	}]
		});
		return fieldname;
	};

	osemscProfile.radio = function(fname,pid,radioItems,blank,note,value){

		var fieldname = osemscProfile.radio+fname;
		if(note != '')
		{
			hide = false;
		}else{
			hide = true;
		}
		fieldname = new Ext.form.CompositeField({
			fieldLabel:fname
			,defaults: {width: 280,msgTarget : 'side'}
			,items:[{
				name: 'profile_'+pid
			  	,xtype: 'radiogroup'
			  	,columns: 1
			  	,allowBlank: blank
			  	,defaults:{xtype:'radio',name:'profile_'+pid}
				,items:radioItems
				,listeners: {
					render: function(p){
						if(value != null)
						{
							p.setValue(value);
						}					
					}
				}
	         },{
        		xtype: 'box'
        		,hidden:hide
        		,autoEl: {
        			tag: 'div'
        			,style:"height: 10px;width: 15px; padding: 3px 0; float:left;background-image: url('./components/com_osemsc/assets/images/profile-info.png');"
        				
         		}
        		,listeners:{
        			render: function(b)	{
        				Ext.QuickTips.register({
						    target: b.getEl(),
						    anchor: 'right',
						    text: note,
						    width: 250
						});
        			}
        		}
        	}]
		});
		return fieldname;
	}

	oseMscAddon.profile.prototype = {
		init: function()	{
			var p =new Ext.Panel({
			border: false
	 		,labelWidth: 130
	 		//,layout: 'form'
	 		//,items:oseMscAddon.formtest
			,listeners: {
				render: function(p){

					Ext.Ajax.request({
						url: 'index.php?option=com_osemsc&controller=register',
						params: {task: "action",action:'register.profile.getList'},

					    success: function(response, opt) {
							var res = Ext.decode(response.responseText);
							var results = res.results;
							var aitems = new Array();
							for(i=0; i<results.length; i++)	{
								if(results[i].require > 0)
								{
									blank = false;
								}else{
									blank = true;
								}
								switch (results[i].type)
								{
									case('textfield'):
										aitems[i] = osemscProfile.textfield(results[i].name,results[i].id,blank,results[i].note,results[i].value);
										break;
									case('textarea'):
										aitems[i] = osemscProfile.textarea(results[i].name,results[i].id,blank,results[i].note,results[i].value);
										break;
									case('combo'):
										aitems[i] = osemscProfile.combo(results[i].name,results[i].id,blank,results[i].note,results[i].value);
										break;
									case('radio'):
										aitems[i] = osemscProfile.radio(results[i].name,results[i].id,results[i].params,blank,results[i].note,results[i].value);
										break;
									case('multiselect'):
										aitems[i] = osemscProfile.MS(results[i].name,results[i].id,blank,results[i].note,results[i].value);
										break;
									case('fileuploadfield'):
										aitems[i] = osemscProfile.FileUpload(results[i].name,results[i].id,blank,results[i].note,results[i].value);
										break;	
									case('datefield'):
										aitems[i] = osemscProfile.date(results[i].name,results[i].id,blank,results[i].note,results[i].value);
										break;	
								}

							}
							oseMscAddon.pform = new Ext.form.FieldSet({
								border: true
								,title: Joomla.JText._('Additional_Information')
								,defaults: {width: 301,msgTarget : 'side'}
						        ,labelWidth: 130
						        ,items: aitems
							});

							p.add(oseMscAddon.pform);
							p.doLayout();
					    }
					});
				}
			}

			})

			return p;
		}
	}