Ext.onReady(function(){
	Ext.QuickTips.init();
	var mscTree = oseMemsMsc.mTree = new oseMemsMsc.MscTree().init();
	var memGrid = oseMemsMsc.grid = new oseMemsMsc.buildGrid().init();
	var memPanel = new oseMemsMsc.buildPanel().init();
	mscTree.getSelectionModel().on('selectionchange', function(sm){
		memGrid.getTopToolbar().addBtn.setDisabled(sm.getCount() < 1 || sm.getSelected().get('id') <= 0); // >
		memGrid.getTopToolbar().createBtn.setDisabled(sm.getCount() < 1 || sm.getSelected().get('id') <= 0); // >
		memGrid.getTopToolbar().renewBtn.setDisabled(sm.getCount() < 1 || sm.getSelected().get('id') <= 0); // >
	});

	mscTree.getSelectionModel().on('rowselect', function( sm, i , r ) {
		oseMemsMsc.tree_msc_id = r.get('id');
		var mscIds = new Array();

		if(r.get('id') > 0)	{
			mscIds[0] = r.get('id');
		}	else	{
			Ext.each(mscTree.getStore().getRange(),function(item,i,all)	{
				if(item.get('id') > 0)	{
					//mscIds[i-1] = item.get('id');
				}
			})
		}
		memGrid.getStore().setBaseParam('msc_id[]',mscIds);
		memGrid.getBottomToolbar().cursor = 0;
		memGrid.getBottomToolbar().doRefresh();
	});

	memGrid.getSelectionModel().on('selectionchange', function(sm,selectedNode){
		memGrid.getTopToolbar().cancelBtn.setDisabled(sm.getCount() < 1); // >
		memPanel.setDisabled(sm.getCount() < 1);
		memGrid.getTopToolbar().deleteBtn.setDisabled(sm.getCount() < 1); // >
	});

	memGrid.getSelectionModel().on('rowselect', function( sm, i , r ) {
		oseMemsMsc.member_id = r.id;
		oseMemsMsc.msc_id = r.get('msc_id');
	});

	memGrid.getTopToolbar().status.addListener('select',function(c,r,i){
		memGrid.getStore().setBaseParam('status',r.data.value);
    	memGrid.getBottomToolbar().doRefresh();
	},this)

	memGrid.getTopToolbar().createBtn.on('click',function(btn,event){
        if(!oseMemsMscCreateWin)	{
        	var oseMemsMscCreateWin = new Ext.Window({
        		title: Joomla.JText._('Create_a_member')
        		,width: 800
        		,autoHeight: true
        		,modal: true
        		,autoLoad:{
					url: 'index.php?option=com_osemsc'
					,params:{ task:'getMod', addon_name: 'create', addon_type: 'member' }
					,scripts: true
					,callback: function(el ,success, response, options)	{
						oseMemsMscCreateWin.add(eval('oseMemMsc.create'));
						oseMemsMscCreateWin.doLayout();

						oseMemMsc.create.buttons[0].on('click',function()	{
							Ext.Msg.wait('Loading');
							oseMemMsc.create.getForm().submit({
			    				url: 'index.php?option=com_osemsc&controller=members'
				    			,params: {task: 'action', action: 'member.juser.createMember',msc_id: oseMemsMsc.tree_msc_id}
				    			,waitMsg: 'Please wait ...'
				    			,success: function(form,action)	{
				    				Ext.Msg.hide();
				    				oseMsc.formSuccess(form,action);
				    				memGrid.getBottomToolbar().doRefresh();
				    				oseMsc.refreshGrid(oseMemsMsc.mTree);
				    				oseMemsMscCreateWin.close()
				    			}
				    			,failure: oseMsc.formFailureMB
			    			})
						})
					}
				}

        	})
        }

        oseMemsMscCreateWin.show().alignTo(Ext.getBody(),'t-t');
    },this);

	memGrid.getTopToolbar().cancelBtn.on('click',function(){
		Ext.Msg.confirm(Joomla.JText._('Notice'),Joomla.JText._('Are_you_sure_to_cancel_this_member_s_membership'),function(btn,txt)	{
			if(btn == 'yes')	{
				var s = memGrid.getSelectionModel().getSelections();
				var member_ids = new Array();
				for( var i =0; i < s.length; i++ ) {//>
					var r = s[i];
					member_ids[i] = r.get('member_id');
				};

		    	memGrid.getStore().remove(r);
		    	Ext.Ajax.request({
					url : 'index.php?option=com_osemsc&controller=members',
					params: { 'task':'cancelMsc','member_ids[]': member_ids, msc_id:oseMemsMsc.msc_id},
					success: function(response,opt){
						oseMsc.ajaxSuccess(response,opt);
						memGrid.getBottomToolbar().doRefresh();
					},
					failure: function(response,opt){
						oseMsc.ajaxFailure(response,opt);
						oseMemMsc.add.store.reload();
						oseMemMsc.add.grid.getView().refresh();
					}
				});
			}
		});

	})

	memGrid.getTopToolbar().deleteBtn.on('click',function(){
		Ext.Msg.confirm(Joomla.JText._('Notice'),Joomla.JText._('Please_confirm_you_would_like_to_remove_this_member_from_the_membership_plan_Please_note_that_the_user_will_only_be_removed_from_the_membership_plan_if_you_would_like_to_delete_the_user_please_access_Joomla_user_manager_to_delete_the_usre_completely'),function(btn,txt)	{
			if(btn == 'yes')	{
				var s = memGrid.getSelectionModel().getSelections();
				var member_ids = new Array();
				for( var i =0; i < s.length; i++ ) {//>
					var r = s[i];
					member_ids[i] = r.get('member_id');
				};

		    	memGrid.getStore().remove(r);
		    	Ext.Ajax.request({
					url : 'index.php?option=com_osemsc&controller=members',
					params: { 'task':'removeMember','member_ids[]': member_ids, msc_id:oseMemsMsc.msc_id},
					success: function(response,opt){
						oseMsc.ajaxSuccess(response,opt);
						memGrid.getBottomToolbar().doRefresh();
					},
					failure: function(response,opt){
						oseMsc.ajaxFailure(response,opt);
						oseMemMsc.add.store.reload();
						oseMemMsc.add.grid.getView().refresh();
					}
				});
			}
		});

	})
	// end
	oseMemsMsc.panel = new Ext.Panel({
		id: 'osemsc-members-panel',
	    baseCls:'x-plain',
	    layout:'border',
	    height: 500,
	    items:[
	    	mscTree
	    	,memGrid
	    	,memPanel
	    ]
	});
	oseMemsMsc.panel.render('com-content');
});