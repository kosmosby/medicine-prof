CREATE TABLE IF NOT EXISTS `#__docman_mimetypes` (
	`mimetype` VARCHAR(255) NOT NULL,
	`extension` VARCHAR(64) NOT NULL,
	PRIMARY KEY (`mimetype`, `extension`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__docman_documents` (
  `docman_document_id` SERIAL,
  `uuid` char(36) NOT NULL UNIQUE,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `docman_category_id` bigint(20) NOT NULL,
  `description` longtext,
  `image` varchar(512) NOT NULL default '',
  `storage_type` varchar(64) NOT NULL default '',
  `storage_path` varchar(512) NOT NULL default '',  
  `enabled` tinyint(1) NOT NULL default 1,
  `access` int(11) NOT NULL default -1,
  `publish_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `unpublish_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `locked_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `locked_by` bigint(20) NOT NULL default 0,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` bigint(20) NOT NULL default 0,
  `modified_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified_by` bigint(20) NOT NULL default 0,
  `params` text,
  `asset_id` INTEGER UNSIGNED NOT NULL DEFAULT 0
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__docman_categories` (
    `docman_category_id` int(11) NOT NULL AUTO_INCREMENT,
  	`uuid` char(36) NOT NULL UNIQUE,
    `title` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL UNIQUE,
    `description` text,
    `image` varchar(512) NOT NULL default '',
    `params` text,
	  `access` int(11) NOT NULL default 1,
    `access_raw` int(11) NOT NULL default -1,
    `enabled` tinyint(1) NOT NULL default 1,
    `locked_on` datetime NOT NULL default '0000-00-00 00:00:00',
    `locked_by` bigint(20) NOT NULL default 0, 
    `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
    `created_by` bigint(20) NOT NULL default 0,
    `modified_on` datetime NOT NULL default '0000-00-00 00:00:00',
    `modified_by` bigint(20) NOT NULL default 0,
    `asset_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`docman_category_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__docman_category_relations` (
  `ancestor_id` int(11) unsigned NOT NULL DEFAULT '0',
  `descendant_id` int(11) unsigned NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ancestor_id`, `descendant_id`, `level`),
  KEY `path_index` (`descendant_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__docman_category_orderings` (
  `docman_category_id` int(11) unsigned NOT NULL,
  `title` int(11) NOT NULL DEFAULT '0',
  `custom` int(11) NOT NULL DEFAULT '0',
  `created_on` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`docman_category_id`)
) DEFAULT CHARSET=utf8;
