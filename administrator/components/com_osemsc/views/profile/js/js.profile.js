Ext.ns('oseMsc.profiles');
Ext.ns('osemscProfiles','osemscProfile');
Ext.onReady(function(){

	osemscProfiles.msg = new Ext.App();

	osemscProfiles.tbar = new Ext.Toolbar({
	    items: [{
    		ref:'addBtn',
            iconCls: 'icon-user-add',
            text: Joomla.JText._('Add'),
            handler: function(){
            	if(!modProfileWin)	{
            		var modProfileWin = new Ext.Window({
            			title: Joomla.JText._('Add_Profile_Field')
            			,modal: true
            			,width: 500
            			,autoHeight: true
            			,autoLoad: {
            				url: 'index.php?option=com_osemsc&controller=profile'
            				,params:{'task': 'getMod', type:'profile'}
            				,scripts: true
            				,callback: function(el,success,response,opt)	{
            					modProfileWin.add(osemscProfile.panel);
            					modProfileWin.doLayout();
            				}
            			}
            		});
            	}
                modProfileWin.show().alignTo(Ext.getBody(),'t-t');
            }
        },{
        	ref: 'editBtn',
            iconCls: 'icon-user-edit',
            text: Joomla.JText._('Edit'),
            disabled: true,
            handler: function(){
            	if(!modProfileWin)	{
            		var modProfileWin = new Ext.Window({
            			title: Joomla.JText._('Edit_profile_filed')
            			,modal: true
            			
            			//,height:500
            			,width: 500
            			,autoLoad: {
            				url: 'index.php?option=com_osemsc&controller=profile'
            				,params:{'task': 'getMod', type:'profile'}
            				,scripts: true
            				,callback: function(el,success,response,opt)	{
            					modProfileWin.add(osemscProfile.panel);
            					modProfileWin.doLayout();

            					var node =oseMsc.profiles.grid.getSelectionModel().getSelected();

				                osemscProfile.form.getForm().load({
				                	url: 'index.php?option=com_osemsc&controller=profile',
				                	params:{task:'getProfile',id:node.id}
				                	,success: function(form,action)	{
				                		osemscProfile.form.findById('ordering').setVisible(true);
				                		var type = osemscProfile.form.findById('type').getValue();
				                		var res = action.result;
				                		var params = res.data.params;
				                		//alert(type);
				                		switch (type)	{
					    	    			case('combo'):
					    	    			case('radio'):
					    	    			case('multiselect'):
					    	    				osemscProfile.form.findById('notice').setVisible(true);
					    	    				osemscProfile.form.findById('params').setVisible(true);
					    	    				osemscProfile.form.findById('params').setDisabled(false);
					    	   	    		break;
					    	    			case('fileuploadfield'):
					    	    				osemscProfile.form.findById('path').setVisible(true);
					    	    				osemscProfile.form.findById('path').setDisabled(false);
					    	    				osemscProfile.form.findById('path').setValue(params);
					    	   	    		break;
				                		}
				                	}
				            	});
            				}
            			}
            		});
            	}

            	modProfileWin.show().alignTo(Ext.getBody(),'t-t');
            }
        },{
        	ref: 'removeBtn',
            iconCls: 'icon-user-delete',
            text: Joomla.JText._('Remove'),
            disabled: true,
            handler: function(){
                var node = oseMsc.profiles.grid.getSelectionModel().getSelected();

            	Ext.Ajax.request({
					url : 'index.php?option=com_osemsc&controller=profile',
					params:{id:node.id, task:'remove'},
					success: function(response, opts){
						var msg = Ext.decode(response.responseText);
						osemscProfiles.msg.setAlert(msg.title,msg.content);
						oseMsc.profiles.store.remove(node);
						oseMsc.profiles.store.reload();
						oseMsc.profiles.grid.getView().refresh();
					}
				});

            }
        }]
	});

	oseMsc.profiles.store = new Ext.data.Store({
		proxy: new Ext.data.HttpProxy({
    		url: 'index.php?option=com_osemsc&controller=profile'
    		,method: 'POST'
		})
		,baseParams:{task: "getList",limit: 20}
		,reader: new Ext.data.JsonReader({
              // we tell the datastore where to get his data from
			root: 'results',
			totalProperty: 'total'
		},[
			{name: 'id', type: 'int', mapping: 'id'},
    		{name: 'name', type: 'string', mapping: 'name'},
    		{name: 'note', type: 'string', mapping: 'note'},
    		{name: 'type', type: 'string', mapping: 'type'},
   			{name: 'ordering', type: 'string', mapping: 'ordering'},
   			{name: 'published', type: 'string', mapping: 'published'},
   			{name: 'require', type: 'string', mapping: 'require'},
   			{name: 'params', type: 'string', mapping: 'params'}
  		])

		,sortInfo:{field: 'id', direction: "ASC"}
		,listeners: {
			beforeload: function(s){

			}
		}
		,autoLoad:{}
	});

	oseMsc.profiles.cm = new Ext.grid.ColumnModel({
		defaults: {
            sortable: true
            ,width: 130
        },
        columns: [
	        new Ext.grid.RowNumberer({header:'#'})
		    ,{id: 'id', header: Joomla.JText._('ID'), dataIndex: 'id', hidden: true,hideable:true}
		    ,{id: 'name', header: Joomla.JText._('Name'), dataIndex: 'name'}
		    ,{id: 'note', header: Joomla.JText._('Note'), dataIndex: 'note'}
		    ,{id: 'type', header: Joomla.JText._('Type'), dataIndex: 'type'}
		    ,{id: 'ordering', header: Joomla.JText._('Ordering'), dataIndex: 'ordering'}
		    ,{id: 'published', header: Joomla.JText._('Publish'), dataIndex: 'published'}
		    ,{id: 'require', header: Joomla.JText._('Require'), dataIndex: 'require'}
		    ,{id: 'params', header: Joomla.JText._('Params'), dataIndex: 'params', width: 400}
	    ]
	});



	oseMsc.profiles.grid = new Ext.grid.GridPanel({
		height: 500
		,autoScroll: true

		,store: oseMsc.profiles.store
		,cm:oseMsc.profiles.cm
		,sm:new Ext.grid.RowSelectionModel({singleSelect:true})
		,tbar: osemscProfiles.tbar
     	,bbar:new Ext.PagingToolbar({
    		pageSize: 20
    		,store: oseMsc.profiles.store
    		,displayInfo: true
		    ,displayMsg: Joomla.JText._('Displaying_topics')+' {0} - {1} '+Joomla.JText._('of')+' {2}'
		    ,emptyMsg: Joomla.JText._("No_topics_to_display")
	    })
	    ,listeners: {
	    	render: function(g)	{

	    	}
	    }
	});

	oseMsc.profiles.panel = new Ext.Panel({
		border: false
		,width: Ext.get('ose-profile-list').getWidth() - 15
		,items: [oseMsc.profiles.grid]
		,renderTo: 'ose-profile-list'
	})

	oseMsc.profiles.grid.getSelectionModel().on('selectionchange', function(sm){
		osemscProfiles.tbar.removeBtn.setDisabled(sm.getCount() < 1); // >
		osemscProfiles.tbar.editBtn.setDisabled(sm.getCount() != 1); // >
	});

});