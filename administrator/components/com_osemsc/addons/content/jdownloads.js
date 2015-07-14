Ext.ns('oseMscAddon');
	oseMscAddon.msg = new Ext.App();
	
	var addonContentJdownloadsTreegrid = new Ext.ux.tree.TreeGrid({
		ref:'tree'
        ,width: 900
        ,height: 500
        ,selModel: new Ext.tree.MultiSelectionModel({})
        //autoWidth: true,
        //boxMinHeight: 500,
        ,enableDD: true
        ,viewConfig: {forceFit: true}
    	,columns:[{
            header: Joomla.JText._('Title'),
            dataIndex: 'name',
            width: 300,
            autoWidth:true
        },{
            header: Joomla.JText._('Controlled'),
            dataIndex: 'controlled',
            width: 290
        },{
            header: Joomla.JText._('ID'),
            dataIndex: 'id',
            width: 290
        }]
        
        ,loader:new Ext.ux.tree.TreeGridLoader({
        	dataUrl: 'index.php?option=com_osemsc&controller=content',
        	baseParams:{task:'action',action:'content.jdownloads.getList'},
        	
        	listeners: {
	  			beforeload: function(loader,node,callback)	{
	  				//alert(oseMsc.msc_id);
			  		loader.baseParams.msc_id = oseMsc.msc_id;
			  	}
		  	}
        }),
        
        tbar:[{
	    	text: Joomla.JText._('Show_to_Members'),
	    	handler: function()	{
	    		var ids = oseMscAddon.jdownloads.tree.getSelectionModel().getSelectedNodes();
	    		
	    		if(ids.length < 1)	{
	    			Ext.Msg.alert(Joomla.JText._('Notice'),Joomla.JText._('Please_select_first'));
	    		}	else	{
		    		var jdc_ids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			jdc_ids[i] = r.id;
		    		}
	
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.jdownloads.changeStatus','jdc_ids[]':jdc_ids,
		    				msc_id:oseMsc.msc_id, status: '1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.jdownloads.tree.getSelectionModel().clearSelections();
		    					var root = oseMscAddon.jdownloads.tree.getRootNode();
		    					oseMscAddon.jdownloads.tree.getLoader().load(root);
		    				}
		    			}
		    		});
	    		}
	    	}
	    },{
	    	text: Joomla.JText._('Show_to_All'),
	    	handler: function()	{
	    		var ids = oseMscAddon.jdownloads.tree.getSelectionModel().getSelectedNodes();
	    		
	    		if(ids.length < 1)	{
	    			Ext.Msg.alert(Joomla.JText._('Notice'),Joomla.JText._('Please_select_first'));
	    		}	else	{
		    		var jdc_ids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			jdc_ids[i] = r.id;
		    		}
	
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.jdownloads.changeStatus','jdc_ids[]':jdc_ids,
		    				msc_id:oseMsc.msc_id, status: '0'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.jdownloads.tree.getSelectionModel().clearSelections();
		    					var root = oseMscAddon.jdownloads.tree.getRootNode();
		    					oseMscAddon.jdownloads.tree.getLoader().load(root);
		    				}
		    			}
		    		});
	    		}
	    	}
	    },{
	    	text: Joomla.JText._('Hide_to_Members'),
	    	hidden:true,
	    	handler: function()	{
	    		var ids = oseMscAddon.jdownloads.tree.getSelectionModel().getSelectedNodes();
	    		
	    		if(ids.length < 1)	{
	    			Ext.Msg.alert(Joomla.JText._('Notice'),Joomla.JText._('Please_select_first'));
	    		}	else	{
		    		var jdc_ids = new Array();
		    		for(i=0;i < ids.length; i++)	{
		    			var r = ids[i];
		    			jdc_ids[i] = r.id;
		    		}
	
		    		Ext.Ajax.request({
		    			url:'index.php?option=com_osemsc&controller=content',
		    			params:{
		    				task:'action',action:'content.jdownloads.changeStatus','jdc_ids[]':jdc_ids,
		    				msc_id:oseMsc.msc_id, status: '-1'
		    			},
		    			success: function(response,opt)	{
		    				var msg = Ext.decode(response.responseText);
		    				oseMscAddon.msg.setAlert(msg.title,msg.content);
		    				
		    				if(msg.success)	{
		    					oseMscAddon.jdownloads.tree.getSelectionModel().clearSelections();
		    					var root = oseMscAddon.jdownloads.tree.getRootNode();
		    					oseMscAddon.jdownloads.tree.getLoader().load(root);
		    				}
		    			}
		    		});
	    		}
	    	}
	    }]
    });
    

	oseMscAddon.jdownloads = new Ext.Panel({
		//title: 'Joomla  Content',
		items:[addonContentJdownloadsTreegrid],
		
		listeners:{
			render: function(p)	{
				var root = oseMscAddon.jdownloads.tree.getRootNode();
				//p.tree.getLoader().load(root);
			}
		}
	});