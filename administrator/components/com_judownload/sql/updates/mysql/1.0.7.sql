ALTER TABLE `#__judownload_files` 
	ADD COLUMN `modified` datetime   NOT NULL DEFAULT '0000-00-00 00:00:00' after `created` , 
	CHANGE `ordering` `ordering` int(11)   NOT NULL DEFAULT 0 after `modified` , 
	CHANGE `published` `published` tinyint(3)   NOT NULL DEFAULT 0 after `ordering` ;

CREATE TABLE `#__judownload_versions`(
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