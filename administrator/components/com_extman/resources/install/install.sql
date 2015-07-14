CREATE TABLE IF NOT EXISTS `#__extman_extensions` (
  `extman_extension_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(128) NOT NULL,
  `joomla_extension_id` int(11) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  `type` varchar(32) NOT NULL,
  `manifest` text,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL DEFAULT '',
  `version` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`extman_extension_id`),
  UNIQUE KEY `identifier` (`identifier`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__extman_dependencies` (
  `extman_extension_id` int(11) unsigned NOT NULL,
  `dependent_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`extman_extension_id`,`dependent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;