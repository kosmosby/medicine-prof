Ext.ns('oseMscAddon');
Ext.ns('osemscProfile');

oseMscAddon.profile = function()	{
	
}

osemscProfile.MS = function(cname,pid,blank)	{
	
	var MSname = osemscProfile.multiSelect+cname+pid;
	MSname = new Ext.ux.form.MultiSelect({
		fieldLabel: cname
		,id:'profile_'+pid
	    ,name: 'profile_'+pid
	   // ,width: 250
	    ,height: 150
	    ,allowBlank:blank
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
	    	
	    ,tbar:[{
	        text: 'Reset',
	        handler: function(){
	    		MSname.reset();
	        }
	    }]
		
	});
	return MSname;
	};
	
	osemscProfile.textarea = function(fname,pid,blank){
		var fieldname = osemscProfile.textarea+fname+pid;
		fieldname = new Ext.form.TextArea({
			fieldLabel:fname
			,name:'profile_'+pid
			,allowBlank: blank
		});
		return fieldname;
	};
	
	osemscProfile.combo = function(cname,pid)	{
		
		var comboname = osemscProfile.combo+cname+pid;
		comboname = new Ext.form.ComboBox({
	  		fieldLabel: cname
	        ,hiddenName: 'profile_'+pid
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
	
	  	});
	
		return comboname;
	};

	osemscProfile.textfield = function(fname,pid){
		var fieldname = osemscProfile.textfield+fname+pid;
		fieldname = new Ext.form.TextField({
			fieldLabel:fname
			,name:'profile_'+pid
			,allowBlank:blank
		});
		return fieldname;
	};
	
	osemscProfile.radio = function(rname,pid,radioItems){

		var radioname = osemscProfile.radio+rname+pid;
		radioname = new Ext.form.RadioGroup({
		fieldLabel: rname
		,name: 'profile_'+pid
		,allowBlank:blank
		,defaults:{xtype:'radio',name:'profile_'+pid}
		,items:radioItems
		});
		return radioname;

		
	}
	
	oseMscAddon.profile.prototype = {
		init: function()	{
			var p =new Ext.form.FieldSet({
			border: true
			,title: 'Profile'
			,defaults: {width: 300,msgTarget : 'side'}
		    ,labelWidth: 150
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
										aitems[i] = osemscProfile.textfield(results[i].name,results[i].id,blank);
										break;
									case('textarea'):
										aitems[i] = osemscProfile.textarea(results[i].name,results[i].id,blank);
										break;
									case('combo'):
										aitems[i] = osemscProfile.combo(results[i].name,results[i].id,blank);
										break;
									case('radio'):
										aitems[i] = osemscProfile.radio(results[i].name,results[i].id,results[i].params,blank);
										break;
									case('multiselect'):
										aitems[i] = osemscProfile.MS(results[i].name,results[i].id,blank);
										break;	
								}
																
							}
				
							p.add(aitems);
							p.doLayout();
					    }
					});
				}
			}
							
			})
				
			return p;
		}
	}