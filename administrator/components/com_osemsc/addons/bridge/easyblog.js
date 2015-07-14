Ext.ns('oseMscAddon');


	var addoneasyblogFieldset = new Ext.form.FieldSet({
		title:Joomla.JText._('EasyBlog_ACL_Assignment'),
		anchor: '95%',
		items:[{
				fieldLabel: Joomla.JText._('Enable')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_enable'
				,defaults: {xtype: 'radio', name:'easyblog_enable'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0, checked: true}
				]
		},{
				fieldLabel: Joomla.JText._('Can_write_entry')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_add_entry'
				,defaults: {xtype: 'radio', name:'easyblog_add_entry'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_publish_entry')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_publish_entry'
				,defaults: {xtype: 'radio', name:'easyblog_publish_entry'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_burn_feed')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_allow_feedburner'
				,defaults: {xtype: 'radio', name:'easyblog_allow_feedburner'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_upload_avatar')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_upload_avatar'
				,defaults: {xtype: 'radio', name:'easyblog_upload_avatar'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_manage_comments_posted_to_his_blog')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_manage_comment'
				,defaults: {xtype: 'radio', name:'easyblog_manage_comment'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_update_twitter')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_update_twitter'
				,defaults: {xtype: 'radio', name:'easyblog_update_twitter'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_update_tweetmeme')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,hidden:true
				,disabled:true
				,name:'easyblog_update_tweetmeme'
				,defaults: {xtype: 'radio', name:'easyblog_update_tweetmeme'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_delete_own_blogs')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_delete_entry'
				,defaults: {xtype: 'radio', name:'easyblog_delete_entry'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_perform_trackback_action')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_add_trackback'
				,defaults: {xtype: 'radio', name:'easyblog_add_trackback'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_contribute_to_the_frontpage')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_contribute_frontpage'
				,defaults: {xtype: 'radio', name:'easyblog_contribute_frontpage'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_create_category')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_create_category'
				,defaults: {xtype: 'radio', name:'easyblog_create_category'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_create_tag')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_create_tag'
				,defaults: {xtype: 'radio', name:'easyblog_create_tag'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_use_adsense')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_add_adsense'
				,defaults: {xtype: 'radio', name:'easyblog_add_adsense'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_use_shortcode_url')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,hidden:true
				,disabled:true
				,name:'easyblog_allow_shortcode'
				,defaults: {xtype: 'radio', name:'easyblog_allow_shortcode'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_article_support_RSS_features')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,hidden:true
				,disabled:true
				,name:'easyblog_allow_rss'
				,defaults: {xtype: 'radio', name:'easyblog_allow_rss'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_use_different_template')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,hidden:true
				,disabled:true
				,name:'easyblog_custom_template'
				,defaults: {xtype: 'radio', name:'easyblog_custom_template'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_enable_blog_privacy')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_enable_privacy'
				,defaults: {xtype: 'radio', name:'easyblog_enable_privacy'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_user_post_comment')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_allow_comment'
				,defaults: {xtype: 'radio', name:'easyblog_allow_comment'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_subscribe_to_a_blog')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_allow_subscription'
				,defaults: {xtype: 'radio', name:'easyblog_allow_subscription'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_moderate_pending_post')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_manage_pending'
				,defaults: {xtype: 'radio', name:'easyblog_manage_pending'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_upload_image_during_blog_creation_or_edit')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_upload_image'
				,defaults: {xtype: 'radio', name:'easyblog_upload_image'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_upload_category_avatar')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_upload_cavatar'
				,defaults: {xtype: 'radio', name:'easyblog_upload_cavatar'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Allow_to_update_LinkedIn')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_update_linkedin'
				,defaults: {xtype: 'radio', name:'easyblog_update_linkedin'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_change_own_blog_s_comment_settings')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_change_setting_comment'
				,defaults: {xtype: 'radio', name:'easyblog_change_setting_comment'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_change_own_blog_s_subscription_settings')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_change_setting_subscription'
				,defaults: {xtype: 'radio', name:'easyblog_change_setting_subscription'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Allows_user_to_post_as_links_into_Facebook')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_update_facebook'
				,defaults: {xtype: 'radio', name:'easyblog_update_facebook'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_delete_category')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_delete_category'
				,defaults: {xtype: 'radio', name:'easyblog_delete_category'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_moderate_all_blog_entries_from_dashboard')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_moderate_entry'
				,defaults: {xtype: 'radio', name:'easyblog_moderate_entry'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_edit_comments_from_dashboard')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_edit_comment'
				,defaults: {xtype: 'radio', name:'easyblog_edit_comment'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		},{
				fieldLabel: Joomla.JText._('Can_delete_comments_from_dashboard')
				,xtype: 'radiogroup'
				,autoWidth: true	
				,name:'easyblog_delete_comment'
				,defaults: {xtype: 'radio', name:'easyblog_delete_comment'}
				,items:[
					{boxLabel: Joomla.JText._('ose_Yes'),autoWidth: true,inputValue: 1, checked: true}
					,{boxLabel: Joomla.JText._('ose_No'),autoWidth: true,inputValue: 0}
				]
		}]
		
	});

	//
	// Addon Msc Panel
	//
	oseMscAddon.easyblog = new Ext.Panel({

		defaults: [{anchour:'95%'}],
		tbar: [{
			text: Joomla.JText._('save'),
			handler: function(){
				oseMscAddon.easyblog.form.getForm().submit({
				    clientValidation: true,
				    url: 'index.php?option=com_osemsc&controller=membership',
				    params: {
				        task: 'action', action : 'panel.easyblog.save',msc_id: oseMsc.msc_id
				    },
				    success: function(form, action) {
				    	var msg = action.result;
				    	oseMsc.msg.setAlert(msg.title,msg.content);
				    	
				    },
				    failure: function(form, action) {
				        switch (action.failureType) {
				            case Ext.form.Action.CLIENT_INVALID:
				                Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
				                break;
				            case Ext.form.Action.CONNECT_FAILURE:
				                Ext.Msg.alert('Failure', 'Ajax communication failed');
				                break;
				            case Ext.form.Action.SERVER_INVALID:
				               Ext.Msg.alert('Failure', action.result.msg);
				       }
				    }
    			})
			}
		}],
		items:[{
			ref:'form',
			xtype:'form',
			labelAlign: 'left',
			labelWidth:300,
		    bodyStyle:'padding:5px',
			autoScroll: true,
			autoWidth: true,
		    border: false,
		    height: 500,
		    defaults: [{anchour:'90%'}],
		    
		    items:[
		           addoneasyblogFieldset
		    ],
		    reader:new Ext.data.JsonReader({   
			    root: 'result',
			    totalProperty: 'total',
			    fields:[ 
				 	{name: 'easyblog_enable', type: 'int', mapping: 'enable'}
				 	,{name: 'easyblog_add_entry', type: 'int', mapping: 'add_entry'}
				 	,{name: 'easyblog_publish_entry', type: 'int', mapping: 'publish_entry'}
				 	,{name: 'easyblog_allow_feedburner', type: 'int', mapping: 'allow_feedburner'}
				 	,{name: 'easyblog_upload_avatar', type: 'int', mapping: 'upload_avatar'}
				 	,{name: 'easyblog_manage_comment', type: 'int', mapping: 'manage_comment'}
				 	,{name: 'easyblog_update_twitter', type: 'int', mapping: 'update_twitter'}
				 	,{name: 'easyblog_update_tweetmeme', type: 'int', mapping: 'update_tweetmeme'}
				 	,{name: 'easyblog_delete_entry', type: 'int', mapping: 'delete_entry'}
				 	,{name: 'easyblog_add_trackback', type: 'int', mapping: 'add_trackback'}
				 	,{name: 'easyblog_contribute_frontpage', type: 'int', mapping: 'contribute_frontpage'}
				 	,{name: 'easyblog_create_category', type: 'int', mapping: 'create_category'}
				 	,{name: 'easyblog_create_tag', type: 'int', mapping: 'create_tag'}
				 	,{name: 'easyblog_add_adsense', type: 'int', mapping: 'add_adsense'}
				 	,{name: 'easyblog_allow_shortcode', type: 'int', mapping: 'allow_shortcode'}
				 	,{name: 'easyblog_allow_rss', type: 'int', mapping: 'allow_rss'}
				 	,{name: 'easyblog_custom_template', type: 'int', mapping: 'custom_template'}
				 	,{name: 'easyblog_enable_privacy', type: 'int', mapping: 'enable_privacy'}
				 	,{name: 'easyblog_allow_comment', type: 'int', mapping: 'allow_comment'}
				 	,{name: 'easyblog_allow_subscription', type: 'int', mapping: 'allow_subscription'}
				 	,{name: 'easyblog_manage_pending', type: 'int', mapping: 'manage_pending'}
				 	,{name: 'easyblog_upload_image', type: 'int', mapping: 'upload_image'}
				 	,{name: 'easyblog_upload_cavatar', type: 'int', mapping: 'upload_cavatar'}
				 	,{name: 'easyblog_update_linkedin', type: 'int', mapping: 'update_linkedin'}
				 	,{name: 'easyblog_change_setting_comment', type: 'int', mapping: 'change_setting_comment'}
				 	,{name: 'easyblog_change_setting_subscription', type: 'int', mapping: 'change_setting_subscription'}
				 	,{name: 'easyblog_update_facebook', type: 'int', mapping: 'update_facebook'}
				 	,{name: 'easyblog_delete_category', type: 'int', mapping: 'delete_category'}
				 	,{name: 'easyblog_moderate_entry', type: 'int', mapping: 'moderate_entry'}
				 	,{name: 'easyblog_edit_comment', type: 'int', mapping: 'edit_comment'}
				 	,{name: 'easyblog_delete_comment', type: 'int', mapping: 'delete_comment'}
				 	
			  	]
		  	})
		}],
		
		listeners:{
			render: function(panel){
				panel.form.getForm().load({
					url: 'index.php?option=com_osemsc&controller=membership',
					params:{task:'getExtItem',msc_id:oseMscs.msc_id,type:'easyblog'}
				});
			}
		}
	});