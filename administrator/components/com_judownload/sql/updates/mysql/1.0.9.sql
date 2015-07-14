ALTER TABLE `#__judownload_fields` 
	CHANGE `advanced_search` `advanced_search` tinyint(3) unsigned   NOT NULL DEFAULT 0 after `simple_search` ;

ALTER TABLE `#__judownload_tags` 
	ADD COLUMN `access` int(10) unsigned   NOT NULL after `description` , 
	ADD COLUMN `language` char(7)  COLLATE utf8_general_ci NOT NULL after `access` , 
	CHANGE `checked_out` `checked_out` int(11) unsigned   NOT NULL DEFAULT 0 after `language` , 
	CHANGE `checked_out_time` `checked_out_time` datetime   NOT NULL DEFAULT '0000-00-00 00:00:00' after `checked_out` , 
	CHANGE `metatitle` `metatitle` varchar(255)  COLLATE utf8_general_ci NOT NULL after `checked_out_time` , 
	CHANGE `metakeyword` `metakeyword` varchar(1024)  COLLATE utf8_general_ci NOT NULL after `metatitle` , 
	CHANGE `metadescription` `metadescription` varchar(1024)  COLLATE utf8_general_ci NOT NULL after `metakeyword` , 
	CHANGE `metadata` `metadata` varchar(2048)  COLLATE utf8_general_ci NOT NULL after `metadescription` , 
	CHANGE `ordering` `ordering` int(11)   NOT NULL DEFAULT 0 after `metadata` , 
	CHANGE `published` `published` tinyint(3)   NOT NULL DEFAULT 0 after `ordering` , 
	CHANGE `publish_up` `publish_up` datetime   NOT NULL DEFAULT '0000-00-00 00:00:00' after `published` , 
	CHANGE `publish_down` `publish_down` datetime   NOT NULL DEFAULT '0000-00-00 00:00:00' after `publish_up` , 
	CHANGE `created` `created` datetime   NOT NULL DEFAULT '0000-00-00 00:00:00' after `publish_down` , 
	CHANGE `created_by` `created_by` int(11) unsigned   NOT NULL DEFAULT 0 after `created` , 
	CHANGE `modified` `modified` datetime   NOT NULL DEFAULT '0000-00-00 00:00:00' after `created_by` , 
	CHANGE `modified_by` `modified_by` int(11) unsigned   NOT NULL DEFAULT 0 after `modified` , 
	ADD KEY `idx_access`(`access`) , 
	ADD KEY `idx_language`(`language`) ;