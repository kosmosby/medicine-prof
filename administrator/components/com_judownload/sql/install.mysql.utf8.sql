/*Table structure for table `#__judownload_backend_permission` */

DROP TABLE IF EXISTS `#__judownload_backend_permission`;

CREATE TABLE `#__judownload_backend_permission` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL DEFAULT '0',
  `permission` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_groupid` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_backend_permission` */

/*Table structure for table `#__judownload_categories` */

DROP TABLE IF EXISTS `#__judownload_categories`;

CREATE TABLE `#__judownload_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL DEFAULT '',
  `parent_id` int(11) unsigned NOT NULL DEFAULT '1',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `selected_fieldgroup` int(11) NOT NULL DEFAULT '-1' COMMENT 'Extra field group id that user selected',
  `fieldgroup_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The real extra field group id(calculated for inherited value)',
  `selected_criteriagroup` int(11) NOT NULL DEFAULT '-1',
  `criteriagroup_id` int(11) unsigned NOT NULL DEFAULT '0',
  `images` text NOT NULL,
  `introtext` text NOT NULL,
  `fulltext` text NOT NULL,
  `show_item` tinyint(3) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `style_id` int(11) NOT NULL DEFAULT '-1',
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language` char(7) NOT NULL,
  `class_sfx` varchar(255) NOT NULL,
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `asset_id` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `field_ordering_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `config_params` mediumtext NOT NULL,
  `template_params` text NOT NULL,
  `plugin_params` text NOT NULL,
  `params` text NOT NULL,
  `metatitle` varchar(255) NOT NULL,
  `metakeyword` varchar(1024) NOT NULL,
  `metadescription` varchar(1024) NOT NULL,
  `metadata` varchar(2048) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_lft_rgt` (`lft`,`rgt`),
  KEY `idx_title` (`title`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`),
  KEY `idx_featured` (`featured`),
  KEY `idx_publishing` (`published`,`publish_up`,`publish_down`),
  KEY `idx_catid_published` (`id`,`published`),
  KEY `idx_level` (`level`),
  KEY `idx_fieldgroupid` (`fieldgroup_id`),
  KEY `idx_criteriagroupid` (`criteriagroup_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_categories` */

insert  into `#__judownload_categories`(`id`,`title`,`alias`,`parent_id`,`lft`,`rgt`,`level`,`selected_fieldgroup`,`fieldgroup_id`,`selected_criteriagroup`,`criteriagroup_id`,`images`,`introtext`,`fulltext`,`show_item`,`created`,`created_by`,`modified`,`modified_by`,`style_id`,`featured`,`published`,`publish_up`,`publish_down`,`language`,`class_sfx`,`access`,`asset_id`,`checked_out`,`checked_out_time`,`field_ordering_type`,`config_params`,`template_params`,`plugin_params`,`params`,`metatitle`,`metakeyword`,`metadescription`,`metadata`) values (1,'Root','root',0,0,1,0,0,0,0,0,'','','',1,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,-2,0,1,'0000-00-00 00:00:00','0000-00-00 00:00:00','*','',1,78,0,'0000-00-00 00:00:00',0,'{\"activate_maintenance\":\"0\",\"maintenance_message\":\"Download area is down for maintenance.<br \\/> Please check back again soon.\",\"number_rating_stars\":5,\"rating_star_width\":16,\"split_star\":\"2\",\"enable_document_rate\":\"1\",\"enable_doc_rate_in_comment_form\":\"1\",\"require_doc_rate_in_comment_form\":\"1\",\"can_download_can_rate\":\"0\",\"rating_interval\":86400,\"only_calculate_last_rating\":\"0\",\"rating_explanation\":\"1:Bad\\r\\n3:Poor\\r\\n5:Fair\\r\\n7:Good\\r\\n9:Excellent\",\"rating_statistic\":\"\",\"min_rates_to_show_rating\":0,\"min_rates_for_top_rated\":0,\"document_report_subjects\":\"Broken link\\r\\nCopyright infringement\\r\\nWrong category\",\"comment_report_subjects\":\"Spam\\r\\nInappropriate\",\"document_owner_use_captcha_when_report\":\"0\",\"collection_allow_vote\":\"1\",\"collection_allow_vote_down\":\"1\",\"collection_allow_owner_vote\":\"0\",\"collection_allow_guest_vote\":\"1\",\"collection_desc_limit\":500,\"logged_events\":[\"document.download\"],\"log_events_for_guest\":\"0\",\"captcha_width\":155,\"captcha_height\":50,\"captcha_length\":6,\"captcha_color\":\"#050505\",\"captcha_bg_color\":\"#ffffff\",\"captcha_line_color\":\"#707070\",\"captcha_noise_color\":\"#707070\",\"captcha_num_lines\":5,\"captcha_noise_level\":2,\"captcha_perturbation\":5,\"captcha_font\":\"AHGBold.ttf\",\"edit_account_details\":\"1\",\"public_user_dashboard\":\"0\",\"searchword_min_length\":\"3\",\"searchword_max_length\":\"30\",\"limit_string\":\"5,10,15,20,25,30,50\",\"plugin_support\":\"0\",\"activate_subscription_by_email\":\"1\",\"field_attachment_directory\":\"media\\/com_judownload\\/field_attachments\\/\",\"max_upload_files\":5,\"max_upload_file_size\":10,\"document_require_file\":\"1\",\"legal_upload_extensions\":\"bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,zip,rar\",\"check_mime_uploaded_file\":\"0\",\"image_extensions\":\"bmp,gif,jpg,png\",\"ignored_extensions\":\"\",\"legal_mime_types\":\"image\\/jpeg,image\\/gif,image\\/png,image\\/bmp,application\\/x-shockwave-flash,application\\/msword,application\\/excel,application\\/pdf,application\\/powerpoint,text\\/plain,application\\/zip\",\"auto_generate_md5_checksum\":\"2\",\"auto_generate_crc32_checksum\":\"2\",\"max_filesize_auto_generate_checksum\":100,\"allow_zip_file\":\"1\",\"allow_download_multi_docs\":\"0\",\"zip_one_file\":\"0\",\"download_zipped_file_mode\":\"temp\",\"download_one_file_no_zipped_mode\":\"temp\",\"restrict_ip_download_file\":\"1\",\"resume_download\":\"1\",\"download_multi_parts\":\"1\",\"max_download_speed\":200,\"download_interval\":5,\"no_counting_download_time\":300,\"max_wrong_password_times\":5,\"block_enter_password_time\":600,\"min_download_speed\":10,\"adjust_file_live_time\":60,\"zip_comment\":\"\",\"max_download_times_in_day\":30,\"max_download_size_in_day_mb\":500,\"max_size_tmp_download_folder\":3072,\"send_noticed_email_interval\":120,\"google_analytics_track_download\":\"0\",\"file_directory\":\"media\\/com_judownload\\/files\\/\",\"download_directory\":\"judownload\\/\",\"external_download_link_target\":\"_blank\",\"show_rule_messages\":\"modal\",\"category_fields_listview_ordering\":{\"id\":\"0\",\"title\":\"0\",\"alias\":\"0\",\"parent_id\":\"0\",\"rel_cats\":\"0\",\"access\":\"0\",\"lft\":\"0\",\"fieldgroup_id\":\"0\",\"criteriagroup_id\":\"0\",\"featured\":\"0\",\"published\":\"0\",\"show_item\":\"0\",\"description\":\"0\",\"intro_image\":\"0\",\"detail_image\":\"0\",\"publish_up\":\"0\",\"publish_down\":\"0\",\"created_by\":\"0\",\"created\":\"0\",\"modified_by\":\"0\",\"modified\":\"0\",\"style_id\":\"0\",\"layout\":\"0\",\"metatitle\":\"0\",\"metakeyword\":\"0\",\"metadescription\":\"0\",\"metadata\":\"0\",\"total_categories\":\"0\",\"total_documents\":\"0\"},\"template_upload_limit\":\"2\",\"template_image_formats\":\"gif,bmp,jpg,jpeg,png\",\"template_source_formats\":\"txt,less,ini,xml,js,php,css\",\"template_font_formats\":\"woff,ttf,otf\",\"template_compressed_formats\":\"zip\",\"allow_add_doc_to_root\":\"0\",\"reset_document_alias_when_approving\":\"1\",\"store_old_file_versions\":\"1\",\"document_owner_can_view_unpublished_document\":\"0\",\"document_owner_can_edit_document_auto_approval\":\"1\",\"auto_approval_document_threshold\":0,\"document_owner_can_edit_state_document\":\"0\",\"document_owner_can_report_document\":\"1\",\"max_recently_viewed_documents\":12,\"can_change_main_category\":\"1\",\"can_change_secondary_categories\":\"1\",\"max_cats_per_doc\":10,\"max_images_per_document\":8,\"max_tags_per_doc\":10,\"submit_document_interval\":30,\"assign_itemid_to_submit_link\":\"currentItemid\",\"predefined_itemid_for_submit_link\":0,\"max_related_documents\":12,\"related_documents_ordering\":\"drel.ordering\",\"related_documents_direction\":\"ASC\",\"imagequality\":90,\"customfilters\":\"\",\"sharpen\":\"0\",\"canvastransparency\":\"1\",\"canvascolour\":\"#ffffff\",\"document_small_image_width\":100,\"document_small_image_height\":100,\"document_small_image_zoomcrop\":\"1\",\"document_small_image_alignment\":\"c\",\"document_big_image_width\":600,\"document_big_image_height\":600,\"document_big_image_zoomcrop\":\"3\",\"document_big_image_alignment\":\"c\",\"use_watermark\":\"0\",\"watermark_image\":\"\",\"watermark_text\":\"\",\"watermark_font\":\"arial.ttf\",\"watermark_fontsize\":14,\"watermark_fontcolor\":\"#ffffff\",\"watermark_backgroundcolor\":\"#144274\",\"watermark_halign\":\"0\",\"watermark_valign\":\"0\",\"watermark_offsetx\":0,\"watermark_offsety\":0,\"watermark_opacity\":\"0.8\",\"watermark_rotate\":0,\"image_min_width\":50,\"image_min_height\":50,\"image_max_width\":1024,\"image_max_height\":1024,\"image_max_size\":400,\"document_default_icon\":\"default-document.png\",\"document_icon_width\":100,\"document_icon_height\":100,\"document_icon_zoomcrop\":\"1\",\"document_icon_alignment\":\"c\",\"category_intro_image_width\":200,\"category_intro_image_height\":200,\"category_intro_image_zoomcrop\":\"1\",\"category_intro_image_alignment\":\"c\",\"category_detail_image_width\":200,\"category_detail_image_height\":200,\"category_detail_image_zoomcrop\":\"1\",\"category_detail_image_alignment\":\"c\",\"avatar_source\":\"juavatar\",\"default_avatar\":\"default-avatar.png\",\"avatar_width\":120,\"avatar_height\":120,\"avatar_zoomcrop\":\"1\",\"avatar_alignment\":\"c\",\"collection_default_icon\":\"-1\",\"collection_icon_width\":100,\"collection_icon_height\":100,\"collection_icon_zoomcrop\":\"1\",\"collection_icon_alignment\":\"c\",\"document_image_filename_rule\":\"{image_name}\",\"document_original_image_directory\":\"media\\/com_judownload\\/images\\/gallery\\/original\\/\",\"document_small_image_directory\":\"media\\/com_judownload\\/images\\/gallery\\/small\\/\",\"document_big_image_directory\":\"media\\/com_judownload\\/images\\/gallery\\/big\\/\",\"document_icon_directory\":\"media\\/com_judownload\\/images\\/document\\/\",\"category_image_filename_rule\":\"{category}\",\"category_intro_image_directory\":\"media\\/com_judownload\\/images\\/category\\/intro\\/\",\"category_detail_image_directory\":\"media\\/com_judownload\\/images\\/category\\/detail\\/\",\"avatar_directory\":\"media\\/com_judownload\\/images\\/avatar\\/\",\"collection_icon_directory\":\"media\\/com_judownload\\/images\\/collection\\/\",\"comment_system\":\"default\",\"disqus_username\":\"\",\"show_comment_direction\":\"1\",\"comment_ordering\":\"cm.created\",\"comment_direction\":\"DESC\",\"show_comment_pagination\":\"0\",\"comment_pagination\":10,\"filter_comment_rating\":\"1\",\"filter_comment_language\":\"0\",\"max_comment_level\":5,\"auto_link_url_in_comment\":\"1\",\"nofollow_link_in_comment\":\"1\",\"trim_long_url_in_comment\":0,\"front_portion_url_in_comment\":0,\"back_portion_url_in_comment\":0,\"auto_embed_youtube_in_comment\":\"0\",\"auto_embed_vimeo_in_comment\":\"0\",\"video_width_in_comment\":360,\"video_height_in_comment\":240,\"can_download_can_comment\":\"0\",\"comment_interval\":60,\"comment_interval_in_same_document\":60,\"auto_approval_comment_threshold\":0,\"auto_approval_comment_reply_threshold\":0,\"allow_edit_comment_within\":600,\"unpublish_comment_by_reporting_threshold\":10,\"allow_vote_comment\":\"1\",\"allow_vote_down_comment\":\"1\",\"can_reply_own_comment\":\"0\",\"can_vote_own_comment\":\"0\",\"can_subscribe_own_comment\":\"1\",\"can_report_own_comment\":\"1\",\"delete_own_comment\":\"0\",\"document_owner_can_comment\":\"0\",\"document_owner_can_comment_many_times\":\"0\",\"document_owner_auto_approval_when_comment\":\"0\",\"document_owner_can_reply_comment\":\"1\",\"document_owner_auto_approval_when_reply_comment\":\"0\",\"document_owner_use_captcha_when_comment\":\"1\",\"document_owner_can_vote_comment\":\"1\",\"document_owner_can_report_comment\":\"1\",\"website_field_in_comment_form\":\"0\",\"comment_form_editor\":\"wysibb\",\"min_comment_characters\":20,\"max_comment_characters\":1000,\"bb_bold_tag\":\"Bold\",\"bb_italic_tag\":\"Italic\",\"bb_underline_tag\":\"Underline\",\"bb_img_tag\":\"Picture\",\"bb_link_tag\":\"Link\",\"bb_video_tag\":\"Video\",\"bb_color_tag\":\"Colors\",\"bb_smilebox_tag\":\"Smilebox\",\"bb_fontsize_tag\":\"Fontsize\",\"bb_bulleted_list\":\"Bulleted-list\",\"bb_numeric_list\":\"Numeric-list\",\"bb_quote_tag\":\"Quotes\",\"bb_readmore_tag\":\"Readmore\",\"bb_code_tag\":\"Code\",\"bb_align_left\":\"alignleft\",\"bb_align_center\":\"aligncenter\",\"bb_align_right\":\"alignright\",\"userid_blacklist\":\"\",\"forbidden_names\":\"\",\"forbidden_words\":\"\",\"forbidden_words_replaced_by\":\"***\",\"block_ip\":\"0\",\"ip_whitelist\":\"\",\"ip_blacklist\":\"\",\"top_comment_level\":\"all\",\"top_comments_limit\":100,\"email_attachment_directory\":\"media\\/com_judownload\\/email_attachments\\/\",\"email_upload_maxsize\":10240,\"email_upload_legal_extensions\":\"bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,zip,rar\",\"email_check_mime\":\"0\",\"email_image_legal_extensions\":\"bmp,gif,jpg,png\",\"email_ignored_extensions\":\"\",\"email_upload_legal_mime\":\"image\\/jpeg,image\\/gif,image\\/png,image\\/bmp,application\\/x-shockwave-flash,application\\/msword,application\\/excel,application\\/pdf,application\\/powerpoint,text\\/plain,application\\/zip\",\"email_embedded_files\":\"0\",\"email_charset\":\"UTF-8\",\"enable_mailq\":\"0\",\"use_mailq_default\":\"0\",\"send_mailqs_on_pageload\":\"0\",\"total_mailqs_sent_each_time\":5,\"mailq_max_attempts\":5,\"delete_error_mailq\":\"0\",\"all_categories_show_category_title\":\"1\",\"all_categories_subcategory_level\":\"-1\",\"all_categories_show_empty_category\":\"1\",\"all_categories_show_total_subcategories\":\"1\",\"all_categories_show_total_documents\":\"1\",\"all_categories_columns\":2,\"all_categories_column_class\":\"\",\"all_categories_row_class\":\"\",\"show_featured_label\":\"1\",\"show_hot_label\":\"1\",\"num_download_per_day_to_be_hot\":10,\"show_new_label\":\"1\",\"num_day_to_show_as_new\":10,\"show_updated_label\":\"1\",\"num_day_to_show_as_updated\":10,\"show_empty_field\":\"0\",\"submit_form_show_tab_file\":\"1\",\"submit_form_show_tab_changelog\":\"1\",\"submit_form_show_tab_related\":\"0\",\"submit_form_show_tab_plugin_params\":\"0\",\"submit_form_show_tab_publishing\":\"0\",\"submit_form_show_tab_style\":\"0\",\"submit_form_show_tab_meta_data\":\"0\",\"submit_form_show_tab_params\":\"0\",\"submit_form_show_tab_permissions\":\"0\",\"show_header_sort\":\"1\",\"document_pagination\":10,\"show_pagination\":\"1\",\"default_view_mode\":\"2\",\"allow_user_select_view_mode\":\"1\",\"document_columns\":2,\"document_column_class\":\"\",\"document_row_class\":\"\",\"show_download_btn_in_listview\":\"1\",\"show_report_btn_in_listview\":\"1\",\"list_alpha\":\"0-9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z\",\"top_documents_limit\":100,\"show_submit_document_btn_in_category\":\"1\",\"category_show_description\":\"1\",\"category_desc_limit\":0,\"category_show_image\":\"1\",\"category_image_width\":200,\"category_image_height\":200,\"related_category_ordering\":\"crel.ordering\",\"related_category_direction\":\"ASC\",\"show_empty_related_category\":\"1\",\"show_total_subcats_of_relcat\":\"0\",\"show_total_docs_of_relcat\":\"0\",\"related_category_show_introtext\":\"1\",\"related_category_introtext_character_limit\":500,\"related_category_show_intro_image\":\"1\",\"related_category_intro_image_width\":200,\"related_category_intro_image_height\":200,\"vcategory_related_category_columns\":2,\"vcategory_related_category_column_class\":\"\",\"vcategory_related_category_row_class\":\"\",\"subcategory_ordering\":\"title\",\"subcategory_direction\":\"ASC\",\"show_empty_subcategory\":\"1\",\"show_total_subcats_of_subcat\":\"0\",\"show_total_docs_of_subcat\":\"0\",\"subcategory_show_introtext\":\"1\",\"subcategory_introtext_character_limit\":500,\"subcategory_show_intro_image\":\"1\",\"subcategory_intro_image_width\":200,\"subcategory_intro_image_height\":200,\"vcategory_subcategory_columns\":2,\"vcategory_subcategory_column_class\":\"\",\"vcategory_subcategory_row_class\":\"\",\"display_params\":{\"doc\":{\"show_comment\":\"1\",\"fields\":{\"title\":{\"details_view\":\"1\"},\"created\":{\"details_view\":\"1\"},\"author\":{\"details_view\":\"1\"},\"cat_id\":{\"details_view\":\"1\"},\"rating\":{\"details_view\":\"1\"}}},\"cat\":{\"show_description\":\"1\"}},\"seo_replace_title_option\":\"replace\",\"seo_replace_description_option\":\"replace\",\"seo_replace_keywords_option\":\"replace\",\"seo_title_length\":64,\"seo_description_length\":160,\"seo_keywords_length\":160,\"seo_user_title\":\"{user_name}\",\"seo_user_description\":\"{meta_description}\",\"seo_user_keywords\":\"{meta_keywords}\",\"seo_collection_title\":\"{collection_title}\",\"seo_collection_description\":\"{meta_description}\",\"seo_collection_keywords\":\"{meta_keywords}\",\"seo_license_title\":\"{license_title}\",\"seo_license_description\":\"{meta_description}\",\"seo_license_keywords\":\"{meta_keywords}\",\"seo_document_title\":\"{doc_title}\",\"seo_document_description\":\"{meta_description}\",\"seo_document_keywords\":\"{meta_keywords}\",\"seo_category_title\":\"{cat_title}\",\"seo_category_description\":\"{meta_description}\",\"seo_category_keywords\":\"{meta_keywords}\",\"seo_field_title\":\"{field_title}\",\"seo_field_description\":\"{meta_description}\",\"seo_field_keywords\":\"{meta_keywords}\",\"seo_tag_title\":\"{tag_title}\",\"seo_tag_description\":\"{meta_description}\",\"seo_tag_keywords\":\"{meta_keywords}\",\"sef_category_full_path\":\"0\",\"sef_document_full_path\":\"0\",\"sef_categories\":\"categories\",\"sef_tree\":\"tree\",\"sef_featured\":\"featured\",\"sef_list_all\":\"list-all\",\"sef_list_alpha\":\"list-alpha\",\"sef_tags\":\"tags\",\"sef_tag\":\"tag\",\"sef_collections\":\"collections\",\"sef_collection\":\"collection\",\"sef_advanced_search\":\"advsearch\",\"sef_search\":\"search\",\"sef_searchby\":\"searchby\",\"sef_guest_subscribe\":\"guest-subscribe\",\"sef_license\":\"license\",\"sef_maintenance\":\"maintenance\",\"sef_documents\":\"modal-documents\",\"sef_error_download\":\"error-download\",\"sef_contact\":\"contact\",\"sef_comment_tree\":\"comment-tree\",\"sef_top_comments\":\"top-comments\",\"sef_top_documents_latest\":\"latest-documents\",\"sef_top_documents_featured\":\"top-featured-documents\",\"sef_top_documents_recent_modified\":\"recent-modified-documents\",\"sef_top_documents_recent_updated\":\"recent-updated-documents\",\"sef_top_documents_popular\":\"popular-documents\",\"sef_top_documents_most_downloaded\":\"most-downloaded-documents\",\"sef_top_documents_most_rated\":\"most-rated-documents\",\"sef_top_documents_top_rated\":\"top-rated-documents\",\"sef_top_documents_latest_rated\":\"latest-rated-documents\",\"sef_top_documents_most_commented\":\"most-commented-documents\",\"sef_top_documents_latest_commented\":\"latest-commented-documents\",\"sef_top_documents_recently_viewed\":\"recent-viewed-documents\",\"sef_top_documents_alpha_ordered\":\"alpha-ordered-documents\",\"sef_top_documents_random\":\"random-documents\",\"sef_top_documents_random_fast\":\"random-fast-documents\",\"sef_top_documents_random_featured\":\"random-featured-documents\",\"sef_add\":\"add\",\"sef_edit\":\"edit\",\"sef_delete\":\"delete\",\"sef_publish\":\"publish\",\"sef_unpublish\":\"unpublish\",\"sef_checkin\":\"checkin\",\"sef_approve\":\"approve\",\"sef_download\":\"download\",\"sef_subscribe\":\"subscribe\",\"sef_unsubscribe\":\"unsubscribe\",\"sef_activate_subscription\":\"activate-subscription\",\"sef_print\":\"print\",\"sef_download_email_attachment\":\"download-attachment\",\"sef_redirect_url\":\"redirect-url\",\"sef_dashboard\":\"dashboard\",\"sef_profile\":\"profile\",\"sef_user_documents\":\"documents\",\"sef_published\":\"published\",\"sef_unpublished\":\"unpublished\",\"sef_pending\":\"pending\",\"sef_user_subscriptions\":\"subscriptions\",\"sef_user_comments\":\"comments\",\"sef_mod_documents\":\"mod-documents\",\"sef_mod_comments\":\"mod-comments\",\"sef_mod_comment\":\"mod-comment\",\"sef_mod_pending_documents\":\"mod-pending-documents\",\"sef_mod_pending_document\":\"mod-pending-document\",\"sef_mod_pending_comments\":\"mod-pending-comments\",\"sef_mod_pending_comment\":\"mod-pending-comment\",\"sef_mod_permissions\":\"mod-permissions\",\"sef_mod_permission\":\"mod-permission\",\"sef_root_cat\":\"root\",\"sef_rss\":\"rss\",\"sef_changelogs\":\"changelogs\",\"sef_versions\":\"versions\",\"sef_latest_version\":\"latest\",\"sef_report\":\"report\",\"sef_layout\":\"layout\",\"sef_page\":\"page-\",\"sef_all\":\"all\",\"sef_new_document\":\"new-document\",\"sef_comment\":\"comment\",\"sef_component\":\"component\",\"sef_file\":\"file\",\"sef_raw_data\":\"raw-data\",\"sef_space\":\"-\",\"rss_display_icon\":\"1\",\"rss_number_items_in_feed\":10,\"rss_show_thumbnail\":\"1\",\"rss_thumbnail_source\":\"icon\",\"rss_thumbnail_alignment\":\"left\",\"rss_email\":\"none\",\"load_jquery\":\"2\",\"load_jquery_ui\":\"2\"}','','','','Root','','','');

/*Table structure for table `#__judownload_categories_relations` */

DROP TABLE IF EXISTS `#__judownload_categories_relations`;

CREATE TABLE `#__judownload_categories_relations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) unsigned NOT NULL DEFAULT '0',
  `cat_id_related` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_catid_relcatid` (`cat_id`,`cat_id_related`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_categories_relations` */

/*Table structure for table `#__judownload_changelogs` */

DROP TABLE IF EXISTS `#__judownload_changelogs`;

CREATE TABLE `#__judownload_changelogs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int(11) unsigned NOT NULL DEFAULT '0',
  `version` varchar(64) NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` mediumtext NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_docid` (`doc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_changelogs` */

/*Table structure for table `#__judownload_collections` */

DROP TABLE IF EXISTS `#__judownload_collections`;

CREATE TABLE `#__judownload_collections` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `icon` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `total_votes` int(11) unsigned NOT NULL DEFAULT '0',
  `helpful_votes` int(11) unsigned NOT NULL DEFAULT '0',
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `private` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `global` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `metatitle` varchar(255) NOT NULL,
  `metakeyword` varchar(1024) NOT NULL,
  `metadescription` varchar(1024) NOT NULL,
  `metadata` varchar(2048) NOT NULL,
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_global` (`global`),
  KEY `idx_featured` (`featured`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_alias` (`alias`),
  KEY `idx_private` (`private`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_collections` */

/*Table structure for table `#__judownload_collections_items` */

DROP TABLE IF EXISTS `#__judownload_collections_items`;

CREATE TABLE `#__judownload_collections_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int(11) unsigned NOT NULL DEFAULT '0',
  `collection_id` int(11) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_collectionid_docid` (`collection_id`,`doc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_collections_items` */

/*Table structure for table `#__judownload_comments` */

DROP TABLE IF EXISTS `#__judownload_comments`;

CREATE TABLE `#__judownload_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `guest_name` varchar(255) NOT NULL,
  `guest_email` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `total_votes` int(11) unsigned NOT NULL DEFAULT '0',
  `helpful_votes` int(11) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `approved` tinyint(3) NOT NULL DEFAULT '0',
  `approved_by` int(11) unsigned NOT NULL DEFAULT '0',
  `approved_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `doc_id` int(11) unsigned NOT NULL DEFAULT '0',
  `rating_id` int(11) unsigned NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `language` char(7) NOT NULL,
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_lft_rgt` (`lft`,`rgt`),
  KEY `idx_docid_published_approved` (`doc_id`,`published`,`approved`),
  KEY `idx_approved` (`approved`),
  KEY `idx_parentid_level` (`parent_id`,`level`),
  KEY `idx_top_comments` (`rating_id`,`published`,`approved`),
  KEY `idx_language_published_approved` (`language`,`published`,`approved`),
  KEY `idx_rgt` (`rgt`),
  KEY `idx_checkout` (`checked_out`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_comments` */

insert  into `#__judownload_comments`(`id`,`title`,`comment`,`user_id`,`guest_name`,`guest_email`,`website`,`total_votes`,`helpful_votes`,`created`,`approved`,`approved_by`,`approved_time`,`modified`,`modified_by`,`published`,`parent_id`,`lft`,`rgt`,`level`,`doc_id`,`rating_id`,`ip_address`,`language`,`checked_out`,`checked_out_time`) values (1,'Root','',0,'','','',0,0,'0000-00-00 00:00:00',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',0,1,0,0,1,0,0,0,'','',0,'0000-00-00 00:00:00');

/*Table structure for table `#__judownload_criterias` */

DROP TABLE IF EXISTS `#__judownload_criterias`;

CREATE TABLE `#__judownload_criterias` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `tooltips` varchar(512) NOT NULL,
  `weights` int(11) unsigned NOT NULL DEFAULT '1',
  `required` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_groupid` (`group_id`),
  KEY `idx_published` (`published`),
  KEY `idx_checkout` (`checked_out`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_criterias` */

/*Table structure for table `#__judownload_criterias_groups` */

DROP TABLE IF EXISTS `#__judownload_criterias_groups`;

CREATE TABLE `#__judownload_criterias_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `params` text NOT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `asset_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_published` (`published`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_criterias_groups` */

/*Table structure for table `#__judownload_criterias_values` */

DROP TABLE IF EXISTS `#__judownload_criterias_values`;

CREATE TABLE `#__judownload_criterias_values` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rating_id` int(11) unsigned NOT NULL DEFAULT '0',
  `criteria_id` int(11) unsigned NOT NULL DEFAULT '0',
  `value` float(8,6) unsigned NOT NULL DEFAULT '0.000000',
  PRIMARY KEY (`id`),
  KEY `idx_ratingid_criteriaid` (`rating_id`,`criteria_id`),
  KEY `idx_criteriaid` (`criteria_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_criterias_values` */

/*Table structure for table `#__judownload_documents` */

DROP TABLE IF EXISTS `#__judownload_documents`;

CREATE TABLE `#__judownload_documents` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `icon` varchar(255) NOT NULL,
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `author` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `url` varchar(512) NOT NULL,
  `version` varchar(64) NOT NULL,
  `license_id` int(11) unsigned NOT NULL DEFAULT '0',
  `confirm_license` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `downloads` int(11) unsigned NOT NULL DEFAULT '0',
  `external_link` varchar(512) NOT NULL,

  `style_id` int(11) NOT NULL DEFAULT '-1',
  `rating` float(8,6) unsigned NOT NULL DEFAULT '0.000000',
  `total_votes` int(11) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `approved` int(11) NOT NULL DEFAULT '0',
  `approved_by` int(11) unsigned NOT NULL DEFAULT '0',
  `approved_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language` char(7) NOT NULL,
  `download_password` varchar(255) NOT NULL,
  `class_sfx` varchar(255) NOT NULL,
  `template_params` text NOT NULL,
  `plugin_params` text NOT NULL,
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `metatitle` varchar(255) NOT NULL,
  `metakeyword` varchar(1024) NOT NULL,
  `metadescription` varchar(1024) NOT NULL,
  `metadata` varchar(2048) NOT NULL,
  `notes` text NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rating` (`rating`),
  KEY `idx_title` (`title`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`),
  KEY `idx_featured` (`featured`,`approved`,`published`,`publish_up`,`publish_down`),
  KEY `idx_user_documents` (`created_by`,`approved`,`published`,`publish_up`,`publish_down`),
  KEY `idx_publishing` (`published`,`approved`,`publish_up`,`publish_down`),
  KEY `idx_approved` (`approved`),
  FULLTEXT KEY `idx_desc` (`introtext`,`fulltext`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_documents` */

/*Table structure for table `#__judownload_documents_relations` */

DROP TABLE IF EXISTS `#__judownload_documents_relations`;

CREATE TABLE `#__judownload_documents_relations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int(11) unsigned NOT NULL DEFAULT '0',
  `doc_id_related` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_docid_reldocid` (`doc_id`,`doc_id_related`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_documents_relations` */

/*Table structure for table `#__judownload_documents_xref` */

DROP TABLE IF EXISTS `#__judownload_documents_xref`;

CREATE TABLE `#__judownload_documents_xref` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int(11) unsigned NOT NULL DEFAULT '0',
  `cat_id` int(11) unsigned NOT NULL DEFAULT '0',
  `main` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Main category',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_cat_id` (`cat_id`),
  KEY `idx_docid_main` (`doc_id`,`main`),
  KEY `idx_docid_catid` (`doc_id`,`cat_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_documents_xref` */

/*Table structure for table `#__judownload_emails` */

DROP TABLE IF EXISTS `#__judownload_emails`;

CREATE TABLE `#__judownload_emails` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `recipients` text NOT NULL,
  `cc` text NOT NULL,
  `bcc` text NOT NULL,
  `reply_to` text NOT NULL,
  `reply_to_name` text NOT NULL,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `body_html` mediumtext NOT NULL,
  `body_text` text NOT NULL,
  `mode` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `attachments` text NOT NULL,
  `event` varchar(64) NOT NULL,
  `language` char(7) NOT NULL,
  `use_mailq` tinyint(3) NOT NULL DEFAULT '-2',
  `priority` tinyint(2) unsigned NOT NULL DEFAULT '5',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `notes` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_event` (`event`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_publishing` (`published`,`publish_up`,`publish_down`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_emails` */

/*Table structure for table `#__judownload_emails_xref` */

DROP TABLE IF EXISTS `#__judownload_emails_xref`;

CREATE TABLE `#__judownload_emails_xref` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email_id` int(11) unsigned NOT NULL DEFAULT '0',
  `cat_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_emailid_catid` (`email_id`,`cat_id`),
  KEY `idx_catid` (`cat_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_emails_xref` */

/*Table structure for table `#__judownload_fields` */

DROP TABLE IF EXISTS `#__judownload_fields`;

CREATE TABLE `#__judownload_fields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL DEFAULT '0',
  `plugin_id` int(11) unsigned NOT NULL DEFAULT '0',
  `field_name` varchar(128) NOT NULL,
  `caption` varchar(255) NOT NULL DEFAULT '',
  `hide_caption` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `attributes` varchar(1024) NOT NULL,
  `predefined_values_type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `predefined_values` text NOT NULL,
  `php_predefined_values` mediumtext NOT NULL,
  `prefix_text_mod` varchar(255) NOT NULL,
  `suffix_text_mod` varchar(255) NOT NULL,
  `prefix_text_display` varchar(255) NOT NULL,
  `suffix_text_display` varchar(255) NOT NULL,
  `prefix_suffix_wrapper` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `list_view` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `details_view` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `simple_search` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `advanced_search` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `filter_search` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `allow_priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `priority` int(11) NOT NULL DEFAULT '0',
  `priority_direction` varchar(8) NOT NULL DEFAULT 'asc',
  `backend_list_view` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `backend_list_view_ordering` int(11) NOT NULL DEFAULT '0',
  `required` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  `params` text NOT NULL,
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(11) unsigned NOT NULL DEFAULT '1',
  `who_can_download_can_access` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `asset_id` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `frontend_ordering` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `metatitle` varchar(255) NOT NULL,
  `metakeyword` varchar(1024) NOT NULL,
  `metadescription` varchar(1024) NOT NULL,
  `metadata` varchar(2048) NOT NULL,
  `ignored_options` varchar(1024) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_groupid` (`group_id`),
  KEY `idx_publishing` (`published`,`publish_up`,`publish_down`),
  KEY `idx_fieldname` (`field_name`),
  KEY `idx_alias` (`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_fields` */

insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (1,1,1,'id','Id',0,'id','','',1,'','','','','','',1,0,0,1,1,0,0,26,'asc',2,39,0,'*','null',0,'0000-00-00 00:00:00',1,0,80,1,'2014-06-02 08:44:04','0000-00-00 00:00:00',1,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','required,published,publish_up,publish_down,frontend_ordering','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (2,1,2,'title','Title',0,'title','','',1,'','','','','','',1,1,1,1,1,1,1,3,'asc',2,1,1,'*','{\"size\":\"32\",\"placeholder\":\"\",\"auto_suggest\":\"1\"}',0,'0000-00-00 00:00:00',1,0,81,1,'2014-06-02 08:06:06','0000-00-00 00:00:00',2,1,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','backend_list_view,required,published,publish_up,publish_down','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (3,1,3,'alias','Alias',0,'alias','','',1,'','','','','','',1,0,0,0,0,0,0,30,'asc',0,29,0,'*','{\"size\":\"30\",\"placeholder\":\"\",\"auto_suggest\":\"0\"}',0,'0000-00-00 00:00:00',1,0,82,1,'2014-06-02 08:36:05','0000-00-00 00:00:00',3,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','required,published,publish_up,publish_down','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (4,1,4,'icon','Icon',0,'icon','','',1,'','','','','','',1,1,1,0,0,0,0,21,'asc',0,18,0,'*','{\"list_view_set_icon_dimension\":\"1\",\"list_view_icon_width\":\"100\",\"list_view_icon_height\":\"100\",\"details_view_set_icon_dimension\":\"1\",\"details_view_icon_width\":\"100\",\"details_view_icon_height\":\"100\"}',0,'0000-00-00 00:00:00',1,0,83,1,'2014-06-02 08:25:26','0000-00-00 00:00:00',5,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (5,1,5,'description','Description',0,'description','','',1,'','','','','','',1,1,1,0,1,0,0,31,'asc',0,36,1,'*','{\"width\":\"700\",\"height\":\"300\",\"cols\":\"70\",\"rows\":\"10\",\"use_editor_back_end\":\"1\",\"backend_editor\":\"tinymce\",\"use_editor_front_end\":\"1\",\"frontend_editor\":\"tinymce\",\"groups_can_use_frontend_editor\":\"1\",\"placeholder\":\"\",\"truncate\":\"1\",\"limit_char_introtext\":\"200\",\"show_introtext_in_details_view\":\"1\",\"parse_plugin\":\"0\"}',0,'0000-00-00 00:00:00',1,0,84,1,'2014-06-02 08:37:28','0000-00-00 00:00:00',6,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (6,1,6,'author','Author',0,'author','','',1,'','','','','','',1,0,1,0,1,0,0,19,'asc',0,30,0,'*','{\"size\":\"32\",\"placeholder\":\"\",\"auto_suggest\":\"0\"}',0,'0000-00-00 00:00:00',1,0,85,1,'2014-06-02 08:36:39','0000-00-00 00:00:00',7,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (7,1,7,'email','Email',0,'email','','',1,'','','','','','',1,0,0,0,0,0,0,29,'asc',0,31,0,'*','{\"size\":\"32\",\"placeholder\":\"\",\"auto_suggest\":\"0\"}',0,'0000-00-00 00:00:00',1,0,86,1,'2014-06-02 08:36:19','0000-00-00 00:00:00',8,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (8,1,8,'url','Url',0,'url','','',1,'','','','','','',1,0,1,0,0,0,0,27,'asc',0,26,0,'*','{\"link_text\":\"\",\"trim_long_url\":\"0\",\"front_portion_url\":\"0\",\"back_portion_url\":\"0\",\"strip_http\":\"1\",\"open_in\":\"_blank\",\"popup_width\":\"800\",\"popup_height\":\"500\",\"show_go_button\":\"1\",\"use_nofollow\":\"1\",\"regex\":\"\\/^(https?:\\\\\\/\\\\\\/)?([\\\\da-z\\\\.-]+)\\\\.([a-z\\\\.]{2,6})([\\\\\\/\\\\w \\\\.-]*)*\\\\\\/?$\\/\",\"invalid_message\":\"\",\"size\":\"32\",\"placeholder\":\"\"}',0,'0000-00-00 00:00:00',1,0,87,1,'0000-00-00 00:00:00','0000-00-00 00:00:00',9,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (9,1,9,'version','Version',0,'version','','',1,'1.0','','','','','',1,1,1,0,1,0,0,12,'asc',2,6,0,'*','{\"size\":\"32\",\"placeholder\":\"\",\"auto_suggest\":\"0\"}',0,'0000-00-00 00:00:00',1,0,88,1,'2014-06-02 08:25:19','0000-00-00 00:00:00',10,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (10,1,10,'license_id','License',0,'license','','',1,'','','','','','',1,0,1,0,1,0,0,18,'asc',0,16,0,'*','null',0,'0000-00-00 00:00:00',1,0,89,1,'2014-06-02 08:26:32','0000-00-00 00:00:00',12,1,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (11,1,11,'confirm_license','Confirm license',0,'confirm-license','','',1,'0','','','','','',1,0,0,0,0,0,0,20,'asc',0,17,0,'*','null',0,'0000-00-00 00:00:00',1,0,90,1,'2014-06-02 08:35:06','0000-00-00 00:00:00',13,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (12,1,12,'hits','Hits',0,'hits','','',1,'','','','','','',1,1,1,0,0,0,0,7,'asc',2,8,0,'*','{\"size\":\"32\",\"placeholder\":\"\",\"auto_suggest\":\"0\",\"is_numeric\":\"1\",\"digits_in_total\":\"11\",\"digits_after_decimal\":\"0\",\"dec_point\":\".\",\"use_thousands_sep\":\"0\",\"thousands_sep\":\",\"}',0,'0000-00-00 00:00:00',1,0,91,1,'2014-06-02 08:45:07','0000-00-00 00:00:00',14,1,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (13,1,13,'downloads','Downloads',0,'downloads','','',1,'','','','','','',1,1,1,0,1,0,0,5,'asc',2,9,0,'*','{\"size\":\"32\",\"placeholder\":\"\",\"is_numeric\":\"1\",\"digits_in_total\":\"6\",\"digits_after_decimal\":\"0\",\"dec_point\":\".\",\"use_thousands_sep\":\"0\",\"thousands_sep\":\",\"}',0,'0000-00-00 00:00:00',1,0,92,1,'2014-06-02 08:45:02','0000-00-00 00:00:00',15,1,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (14,1,14,'external_link','External link',0,'external-link','','',1,'','','','','','',1,0,0,0,0,0,0,28,'asc',0,25,0,'*','{\"size\":\"32\",\"placeholder\":\"\",\"auto_suggest\":\"0\",\"regex\":\"\\/^(https?:\\\\\\/\\\\\\/)?([\\\\da-z\\\\.-]+)\\\\.([a-z\\\\.]{2,6})([\\\\\\/\\\\w \\\\.-]*)*\\\\\\/?$\\/\"}',0,'0000-00-00 00:00:00',1,0,93,1,'2014-06-02 08:26:37','0000-00-00 00:00:00',16,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (15,1,15,'rating','Rating',0,'rating','','',1,'','','','','','',1,1,1,0,1,0,0,4,'asc',2,10,0,'*','{\"size\":\"32\",\"placeholder\":\"\",\"auto_suggest\":\"0\"}',0,'0000-00-00 00:00:00',1,0,94,1,'2014-06-02 08:42:37','0000-00-00 00:00:00',19,1,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (16,1,16,'total_votes','Total votes',0,'total-votes','','',1,'','','','','','',1,0,0,0,0,0,0,8,'asc',0,15,0,'*','{\"size\":\"32\",\"placeholder\":\"\",\"auto_suggest\":\"0\"}',0,'0000-00-00 00:00:00',1,0,95,1,'2014-06-02 08:42:31','0000-00-00 00:00:00',20,1,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (17,1,17,'created','Created',0,'created','','',1,'','','','','','',1,0,1,0,0,0,1,2,'desc',0,19,0,'*','{\"dateformat\":\"l, d F Y\",\"custom_dateformat\":\"\",\"filter\":\"USER_UTC\",\"size\":\"32\"}',0,'0000-00-00 00:00:00',1,0,96,1,'2014-06-02 08:51:59','0000-00-00 00:00:00',21,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (18,1,18,'created_by','Created by',0,'created-by','','',1,'','','','','','',1,1,1,0,0,0,0,14,'asc',0,34,0,'*','null',0,'0000-00-00 00:00:00',1,0,97,1,'2014-06-02 08:24:59','0000-00-00 00:00:00',22,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (19,1,19,'created_by_alias','Create by alias',0,'created-by-alias','','',1,'','','','','','',1,0,0,0,0,0,0,32,'asc',0,35,0,'*','{\"size\":\"32\",\"placeholder\":\"\",\"auto_suggest\":\"0\",\"tag_search\":\"0\"}',0,'0000-00-00 00:00:00',1,0,98,1,'2014-06-12 10:09:57','0000-00-00 00:00:00',23,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (20,1,20,'modified','Modified',0,'modified','','',1,'','','','','','',1,0,0,0,0,0,0,13,'asc',0,32,0,'*','{\"dateformat\":\"l, d F Y\",\"custom_dateformat\":\"\",\"filter\":\"USER_UTC\",\"size\":\"32\"}',0,'0000-00-00 00:00:00',1,0,99,1,'2014-06-02 08:37:20','0000-00-00 00:00:00',27,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (21,1,21,'modified_by','Modified by',0,'modified-by','','',1,'','','','','','',1,0,0,0,0,0,0,22,'asc',0,33,0,'*','null',0,'0000-00-00 00:00:00',1,0,100,1,'2014-06-02 08:36:55','0000-00-00 00:00:00',28,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (22,1,22,'featured','Featured',0,'featured','','',1,'0','','','','','',1,1,1,0,1,0,1,1,'desc',2,3,0,'*','null',0,'0000-00-00 00:00:00',1,0,101,1,'2014-06-02 08:44:41','0000-00-00 00:00:00',4,1,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (23,1,23,'published','Published',0,'published','','',1,'1','','','','','',1,0,0,0,0,0,0,36,'asc',2,4,0,'*','null',0,'0000-00-00 00:00:00',1,0,102,1,'2014-06-02 08:44:36','0000-00-00 00:00:00',29,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (24,1,24,'publish_up','Publish up',0,'publish-up','','',1,'','','','','','',1,1,1,0,1,0,0,11,'asc',0,20,0,'*','{\"dateformat\":\"l, d F Y\",\"custom_dateformat\":\"\",\"filter\":\"USER_UTC\",\"size\":\"32\"}',0,'0000-00-00 00:00:00',1,0,103,1,'2014-06-02 08:37:01','0000-00-00 00:00:00',30,1,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (25,1,25,'publish_down','Publish down',0,'publish-down','','',1,'','','','','','',1,0,0,0,0,0,0,23,'asc',0,24,0,'*','{\"dateformat\":\"l, d F Y\",\"custom_dateformat\":\"\",\"filter\":\"USER_UTC\",\"size\":\"32\"}',0,'0000-00-00 00:00:00',1,0,104,1,'2014-06-02 08:37:50','0000-00-00 00:00:00',31,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (26,1,26,'updated','Updated',0,'updated','','',1,'','','','','','',1,1,1,0,1,0,0,6,'asc',1,5,0,'*','{\"dateformat\":\"l, d F Y\",\"custom_dateformat\":\"\",\"filter\":\"USER_UTC\",\"size\":\"32\"}',0,'0000-00-00 00:00:00',1,0,105,1,'2014-06-02 08:38:31','0000-00-00 00:00:00',11,1,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (27,1,27,'approved','Approved',0,'approved','','',1,'1','','','','','',1,0,0,0,0,0,0,35,'asc',0,37,0,'*','null',0,'0000-00-00 00:00:00',3,0,106,1,'2014-06-02 08:38:04','0000-00-00 00:00:00',24,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (28,1,28,'approved_by','Approved by',0,'approved-by','','',1,'','','','','','',1,0,0,0,0,0,0,24,'asc',0,22,0,'*','null',0,'0000-00-00 00:00:00',1,0,107,1,'2014-06-02 08:38:09','0000-00-00 00:00:00',25,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (29,1,29,'approved_time','Approved time',0,'approved-time','','',1,'','','','','','',1,0,0,0,0,0,0,17,'asc',0,23,0,'*','{\"dateformat\":\"l, d F Y\",\"custom_dateformat\":\"\",\"filter\":\"USER_UTC\",\"size\":\"32\"}',0,'0000-00-00 00:00:00',1,0,108,1,'2014-06-02 08:38:14','0000-00-00 00:00:00',26,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (30,1,30,'language','Language',0,'language','','',1,'*','','','','','',1,0,0,0,1,0,0,16,'asc',0,14,0,'*','null',0,'0000-00-00 00:00:00',1,0,109,1,'2014-06-02 08:38:18','0000-00-00 00:00:00',32,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (31,1,31,'download_password','Password',0,'password','','',1,'','','','','','',1,0,0,0,0,0,0,34,'asc',0,21,0,'*','{\"size\":\"32\",\"placeholder\":\"\",\"auto_suggest\":\"0\"}',0,'0000-00-00 00:00:00',1,0,110,1,'2014-06-02 08:39:49','0000-00-00 00:00:00',18,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (32,1,32,'class_sfx','Class suffix',0,'class-suffix','','',1,'','','','','','',1,0,0,0,0,0,0,33,'asc',0,28,0,'*','{\"size\":\"32\",\"placeholder\":\"\",\"auto_suggest\":\"0\"}',0,'0000-00-00 00:00:00',1,0,111,1,'2014-06-02 08:39:06','0000-00-00 00:00:00',33,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (33,1,33,'access','Access',0,'access','','',1,'1','','','','','',1,0,0,0,0,0,0,25,'asc',2,7,0,'*','null',0,'0000-00-00 00:00:00',1,0,112,1,'2014-06-02 08:46:17','0000-00-00 00:00:00',17,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (34,1,34,'comments','Comments',0,'comments','','',1,'','','','','','',1,1,0,0,0,0,0,9,'asc',2,11,0,'*','null',0,'0000-00-00 00:00:00',1,0,113,1,'2014-06-02 08:44:18','0000-00-00 00:00:00',34,1,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (35,1,35,'reports','Reports',0,'reports','','',1,'','','','','','',1,0,0,0,0,0,0,15,'asc',1,12,0,'*','null',0,'0000-00-00 00:00:00',1,0,114,1,'2014-06-02 08:44:13','0000-00-00 00:00:00',35,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (36,1,36,'subscriptions','Subscriptions',0,'subscriptions','','',1,'','','','','','',1,0,0,0,0,0,0,10,'asc',1,13,0,'*','null',0,'0000-00-00 00:00:00',1,0,115,1,'2014-06-02 08:44:09','0000-00-00 00:00:00',36,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (37,1,37,'cat_id','Categories',0,'categories','','',1,'','','','','','',1,1,1,0,1,0,0,37,'asc',1,2,1,'*','null',0,'0000-00-00 00:00:00',1,0,116,1,'2014-06-02 08:39:54','0000-00-00 00:00:00',37,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','required,published,publish_up,publish_down,frontend_ordering','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (38,1,38,'tags','Tags',0,'tags','','',1,'','','','','','',1,1,1,0,1,0,0,38,'asc',0,27,0,'*','{\"tag_ordering\":\"t.title\",\"tag_direction\":\"ASC\"}',0,'0000-00-00 00:00:00',1,0,117,1,'2014-06-02 08:39:11','0000-00-00 00:00:00',38,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (39,1,50,'','Captcha',0,'captcha','','',1,'','','','','','',1,0,0,0,0,0,0,39,'asc',0,38,1,'*','{\"invalid_message\":\"\",\"size\":\"32\",\"placeholder\":\"\"}',0,'0000-00-00 00:00:00',1,0,118,1,'0000-00-00 00:00:00','0000-00-00 00:00:00',39,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','list_view,details_view,allow_priority,simple_search,advanced_search,filter_search,required,backend_list_view,frontend_ordering','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);
insert  into `#__judownload_fields`(`id`,`group_id`,`plugin_id`,`field_name`,`caption`,`hide_caption`,`alias`,`description`,`attributes`,`predefined_values_type`,`predefined_values`,`php_predefined_values`,`prefix_text_mod`,`suffix_text_mod`,`prefix_text_display`,`suffix_text_display`,`prefix_suffix_wrapper`,`list_view`,`details_view`,`simple_search`,`advanced_search`,`filter_search`,`allow_priority`,`priority`,`priority_direction`,`backend_list_view`,`backend_list_view_ordering`,`required`,`language`,`params`,`checked_out`,`checked_out_time`,`access`,`who_can_download_can_access`,`asset_id`,`published`,`publish_up`,`publish_down`,`ordering`,`frontend_ordering`,`metatitle`,`metakeyword`,`metadescription`,`metadata`,`ignored_options`,`created`,`created_by`,`modified`,`modified_by`) values (40,1,52,'gallery','Gallery',0,'gallery','','',1,'','','','','','',1,0,1,0,0,0,0,40,'asc',0,40,0,'*','',0,'0000-00-00 00:00:00',1,0,119,1,'0000-00-00 00:00:00','0000-00-00 00:00:00',40,0,'','','','{\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}','','2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);

/*Table structure for table `#__judownload_fields_groups` */

DROP TABLE IF EXISTS `#__judownload_fields_groups`;

CREATE TABLE `#__judownload_fields_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `asset_id` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `doc_metatitle` varchar(255) NOT NULL,
  `doc_metakeyword` varchar(1024) NOT NULL,
  `doc_metadescription` varchar(1024) NOT NULL,
  `field_ordering_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_published` (`published`),
  KEY `idx_checkout` (`checked_out`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_fields_groups` */

insert  into `#__judownload_fields_groups`(`id`,`name`,`description`,`access`,`params`,`asset_id`,`ordering`,`checked_out`,`checked_out_time`,`published`,`doc_metatitle`,`doc_metakeyword`,`doc_metadescription`,`field_ordering_type`,`created`,`created_by`,`modified`,`modified_by`) values (1,'Core Fields','<p>Core field group</p>',1,'',79,1,0,'0000-00-00 00:00:00',1,'','','',0,'2014-06-12 16:59:08',184,'0000-00-00 00:00:00',0);

/*Table structure for table `#__judownload_fields_ordering` */

DROP TABLE IF EXISTS `#__judownload_fields_ordering`;

CREATE TABLE `#__judownload_fields_ordering` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(11) unsigned NOT NULL DEFAULT '0',
  `type` varchar(32) NOT NULL,
  `field_id` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_itemid_type_fieldid` (`item_id`,`type`,`field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_fields_ordering` */

/*Table structure for table `#__judownload_fields_values` */

DROP TABLE IF EXISTS `#__judownload_fields_values`;

CREATE TABLE `#__judownload_fields_values` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `field_id` int(11) unsigned NOT NULL DEFAULT '0',
  `doc_id` int(11) unsigned NOT NULL DEFAULT '0',
  `value` mediumtext NOT NULL,
  `counter` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_fieldid_docid` (`field_id`,`doc_id`),
  KEY `idx_docid` (`doc_id`),
  KEY `idx_value` (`value`(8))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_fields_values` */

/*Table structure for table `#__judownload_files` */

DROP TABLE IF EXISTS `#__judownload_files`;

CREATE TABLE `#__judownload_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int(11) unsigned NOT NULL DEFAULT '0',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `rename` varchar(255) NOT NULL DEFAULT '',
  `size` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `md5_checksum` varchar(32) NOT NULL,
  `crc32_checksum` varchar(10) NOT NULL,
  `downloads` int(11) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_docid_published` (`doc_id`,`published`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_files` */

/*Table structure for table `#__judownload_files_tmp` */

DROP TABLE IF EXISTS `#__judownload_files_tmp`;

CREATE TABLE `#__judownload_files_tmp` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `included_fileids` varchar(5120) NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `file_size` int(11) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `removed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_removed` (`removed`),
  KEY `idx_userid_includedfileids` (`user_id`,`included_fileids`(32)),
  KEY `idx_filesize` (`file_size`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_files_tmp` */

/*Table structure for table `#__judownload_following` */

DROP TABLE IF EXISTS `#__judownload_following`;

CREATE TABLE `#__judownload_following` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `type` varchar(32) NOT NULL,
  `item_id` int(11) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip_address` varchar(45) NOT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_itemid_type` (`item_id`,`type`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_checkout` (`checked_out`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_following` */

/*Table structure for table `#__judownload_images` */

DROP TABLE IF EXISTS `#__judownload_images`;

CREATE TABLE `#__judownload_images` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `doc_id` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_docid` (`doc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_images` */

/*Table structure for table `#__judownload_licenses` */

DROP TABLE IF EXISTS `#__judownload_licenses`;

CREATE TABLE `#__judownload_licenses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `metatitle` varchar(255) NOT NULL,
  `metakeyword` varchar(1024) NOT NULL,
  `metadescription` varchar(1024) NOT NULL,
  `metadata` varchar(2048) NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_published` (`published`),
  KEY `idx_alias` (`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_licenses` */

/*Table structure for table `#__judownload_logs` */

DROP TABLE IF EXISTS `#__judownload_logs`;

CREATE TABLE `#__judownload_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `event` varchar(64) NOT NULL,
  `item_id` int(11) unsigned NOT NULL DEFAULT '0',
  `doc_id` int(11) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `browser` varchar(255) NOT NULL,
  `platform` varchar(255) NOT NULL,
  `user_agent` varchar(512) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `value` float(16,6) NOT NULL DEFAULT '0.000000',
  `reference` text NOT NULL COMMENT 'Reference data, for example download multi files',
  PRIMARY KEY (`id`),
  KEY `idx_docid_userid` (`doc_id`,`user_id`),
  KEY `idx_docid_ip` (`doc_id`,`ip_address`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_ip_userid` (`ip_address`,`user_id`),
  KEY `idx_itemid` (`item_id`),
  KEY `idx_date` (`date`),
  KEY `idx_value` (`value`),
  KEY `idx_reference` (`reference`(8)),
  KEY `idx_event` (`event`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_logs` */

/*Table structure for table `#__judownload_mailqs` */

DROP TABLE IF EXISTS `#__judownload_mailqs`;

CREATE TABLE `#__judownload_mailqs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email_id` int(11) unsigned NOT NULL DEFAULT '0',
  `item_id` int(11) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `send_date` int(11) unsigned NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `last_attempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_emailid` (`email_id`),
  KEY `idx_itemid` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_mailqs` */

/*Table structure for table `#__judownload_moderators` */

DROP TABLE IF EXISTS `#__judownload_moderators`;

CREATE TABLE `#__judownload_moderators` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `document_view` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `document_view_unpublished` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `document_create` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `document_edit` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `document_edit_state` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `document_edit_own` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `document_delete` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `document_delete_own` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `document_download` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `document_approve` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comment_edit` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comment_edit_state` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comment_delete` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comment_approve` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid_published` (`user_id`,`published`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_publishing` (`published`,`publish_up`,`publish_down`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_moderators` */

/*Table structure for table `#__judownload_moderators_xref` */

DROP TABLE IF EXISTS `#__judownload_moderators_xref`;

CREATE TABLE `#__judownload_moderators_xref` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mod_id` int(11) unsigned NOT NULL DEFAULT '0',
  `cat_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_modid_catid` (`mod_id`,`cat_id`),
  KEY `idx_catid` (`cat_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_moderators_xref` */

/*Table structure for table `#__judownload_plugins` */

DROP TABLE IF EXISTS `#__judownload_plugins`;

CREATE TABLE `#__judownload_plugins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL DEFAULT 'field',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `version` varchar(64) NOT NULL,
  `author` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `date` varchar(64) NOT NULL,
  `license` varchar(255) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `core` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `default` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` text NOT NULL,
  `extension_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_folder` (`folder`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_extension_id` (`extension_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_plugins` */

insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (1,'field','Core Id','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_id',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (2,'field','Core Title','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_title',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (3,'field','Core Alias','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_alias',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (4,'field','Core Icon','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_icon',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (5,'field','Core Description','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_description',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (6,'field','Core Author','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_author',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (7,'field','Core Email','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_email',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (8,'field','Core URL','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_url',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (9,'field','Core Version','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_version',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (10,'field','Core License','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_license',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (11,'field','Core Confirm license','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_confirm_license',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (12,'field','Core Hits','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_hits',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (13,'field','Core Downloads','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_downloads',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (14,'field','Core External link','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_external_link',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (15,'field','Core Rating','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_rating',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (16,'field','Core Total votes','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_total_votes',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (17,'field','Core Created','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_created',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (18,'field','Core Created by','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_created_by',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (19,'field','Core Created by alias','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_created_by_alias',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (20,'field','Core Modified','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_modified',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (21,'field','Core Modified by','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_modified_by',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (22,'field','Core Featured','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_featured',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (23,'field','Core Published','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_published',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (24,'field','Core Publish up','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_publish_up',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (25,'field','Core Publish down','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_publish_down',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (26,'field','Core Updated','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_updated',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (27,'field','Core Approved','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_approved',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (28,'field','Core Approved by','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_approved_by',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (29,'field','Core Approved time','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_approved_time',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (30,'field','Core Language','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_language',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (31,'field','Core Password','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_password',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (32,'field','Core Class suffix','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_classsfx',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (33,'field','Core Access','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_access',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (34,'field','Core Comments','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_comments',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (35,'field','Core Reports','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_reports',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (36,'field','Core Subscriptions','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_subscriptions',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (37,'field','Core Categories','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_categories',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (38,'field','Core Tags','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','core_tags',1,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (39,'field','Text','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','text',0,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (40,'field','Link','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','link',0,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (41,'field','Date Time','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','datetime',0,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (42,'field','Textarea','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','textarea',0,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (43,'field','Radio','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','radio',0,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (44,'field','Checkboxes','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','checkboxes',0,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (45,'field','Dropdown List','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','dropdownlist',0,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (46,'field','Multiple Select','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','multipleselect',0,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (47,'field','Files','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','files',0,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (48,'field','Images','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','images',0,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (49,'field','Free Text','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','freetext',0,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (50,'field','Captcha','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','captcha',0,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (51,'template','Default','Default JUDownload Template','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','18 July 2014','GNU/GPL','default',0,1,0,'0000-00-00 00:00:00','',0);
insert  into `#__judownload_plugins`(`id`,`type`,`title`,`description`,`version`,`author`,`email`,`website`,`date`,`license`,`folder`,`core`,`default`,`checked_out`,`checked_out_time`,`params`,`extension_id`) values (52,'field','Gallery','','1.0','JoomUltra','admin@joomultra.com','http://www.joomultra.com','10 Jan 2015','GNU/GPL','core_gallery',1,1,0,'0000-00-00 00:00:00','',0);

/*Table structure for table `#__judownload_rating` */

DROP TABLE IF EXISTS `#__judownload_rating`;

CREATE TABLE `#__judownload_rating` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `score` float(8,6) unsigned NOT NULL DEFAULT '0.000000',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_docid_userid` (`doc_id`,`user_id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_score` (`score`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_rating` */

/*Table structure for table `#__judownload_reports` */

DROP TABLE IF EXISTS `#__judownload_reports`;

CREATE TABLE `#__judownload_reports` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `report` text NOT NULL,
  `type` varchar(32) NOT NULL,
  `item_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `guest_name` varchar(255) NOT NULL,
  `guest_email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `read` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `admin_notes` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_itemid_type` (`item_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_reports` */

/*Table structure for table `#__judownload_subscriptions` */

DROP TABLE IF EXISTS `#__judownload_subscriptions`;

CREATE TABLE `#__judownload_subscriptions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `item_id` int(11) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip_address` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_itemid_type_published` (`item_id`,`type`,`published`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_subscriptions` */

/*Table structure for table `#__judownload_tags` */

DROP TABLE IF EXISTS `#__judownload_tags`;

CREATE TABLE `#__judownload_tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `access` int(10) unsigned NOT NULL,
  `language` char(7) NOT NULL,
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `metatitle` varchar(255) NOT NULL,
  `metakeyword` varchar(1024) NOT NULL,
  `metadescription` varchar(1024) NOT NULL,
  `metadata` varchar(2048) NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '0',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_title` (`title`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_alias` (`alias`),
  KEY `idx_publishing` (`published`,`publish_up`,`publish_down`),
  KEY `idx_access` (`access`),
  KEY `idx_language` (`language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_tags` */

/*Table structure for table `#__judownload_tags_xref` */

DROP TABLE IF EXISTS `#__judownload_tags_xref`;

CREATE TABLE `#__judownload_tags_xref` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) unsigned NOT NULL DEFAULT '0',
  `doc_id` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_tagid_docid` (`tag_id`,`doc_id`),
  KEY `idx_docid` (`doc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_tags_xref` */

/*Table structure for table `#__judownload_template_styles` */

DROP TABLE IF EXISTS `#__judownload_template_styles`;

CREATE TABLE `#__judownload_template_styles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `template_id` int(11) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `home` char(7) NOT NULL DEFAULT '0',
  `default` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_templateid` (`template_id`),
  KEY `idx_parentid` (`parent_id`),
  KEY `idx_home` (`home`),
  KEY `idx_checkout` (`checked_out`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_template_styles` */

insert  into `#__judownload_template_styles`(`id`,`title`,`template_id`,`parent_id`,`lft`,`rgt`,`level`,`home`,`default`,`checked_out`,`checked_out_time`,`created`,`created_by`,`modified`,`modified_by`,`params`) values (1,'Root',1,0,0,3,0,'0',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'');
insert  into `#__judownload_template_styles`(`id`,`title`,`template_id`,`parent_id`,`lft`,`rgt`,`level`,`home`,`default`,`checked_out`,`checked_out_time`,`created`,`created_by`,`modified`,`modified_by`,`params`) values (2,'Default',2,1,1,2,1,'1',1,0,'0000-00-00 00:00:00','0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0,'');

/*Table structure for table `#__judownload_templates` */

DROP TABLE IF EXISTS `#__judownload_templates`;

CREATE TABLE `#__judownload_templates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_lft_rgt` (`lft`,`rgt`),
  KEY `idx_parentid` (`parent_id`),
  KEY `idx_pluginid` (`plugin_id`),
  KEY `idx_checkout` (`checked_out`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_templates` */

insert  into `#__judownload_templates`(`id`,`plugin_id`,`parent_id`,`lft`,`rgt`,`level`,`checked_out`,`checked_out_time`) values (1,0,0,0,3,0,0,'0000-00-00 00:00:00');
insert  into `#__judownload_templates`(`id`,`plugin_id`,`parent_id`,`lft`,`rgt`,`level`,`checked_out`,`checked_out_time`) values (2,51,1,1,2,1,0,'0000-00-00 00:00:00');

/*Table structure for table `#__judownload_users` */

DROP TABLE IF EXISTS `#__judownload_users`;

CREATE TABLE `#__judownload_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `avatar` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `homepage` varchar(255) NOT NULL,
  `occupation` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `metatitle` varchar(255) NOT NULL,
  `metakeyword` varchar(1024) NOT NULL,
  `metadescription` varchar(1024) NOT NULL,
  `metadata` varchar(2048) NOT NULL,
  `notes` text NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_featured` (`featured`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_users` */

/*Table structure for table `#__judownload_versions` */

DROP TABLE IF EXISTS `#__judownload_versions`;

CREATE TABLE `#__judownload_versions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `doc_id` int(11) unsigned NOT NULL DEFAULT '0',
  `file_id` int(11) unsigned NOT NULL DEFAULT '0',
  `version` varchar(64) NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `file_path` varchar(255) NOT NULL DEFAULT '',
  `size` int(11) unsigned NOT NULL DEFAULT '0',
  `md5_checksum` varchar(32) NOT NULL,
  `crc32_checksum` varchar(10) NOT NULL,
  `downloads` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_docid_fileid_version` (`doc_id`,`file_id`,`version`),
  KEY `idx_fileid` (`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `#__judownload_versions` */