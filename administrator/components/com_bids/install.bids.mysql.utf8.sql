SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `#__bids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `id_proxy` int(11) NOT NULL,
  `initial_bid` decimal(20,2) NOT NULL,
  `bid_price` decimal(20,2) NOT NULL DEFAULT '0.00',
  `payment` int(11) NOT NULL DEFAULT '0',
  `cancel` int(11) NOT NULL DEFAULT '0',
  `accept` tinyint(4) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_offer` (`auction_id`),
  KEY `userid` (`userid`),
  KEY `bid_price` (`bid_price`),
  KEY `id_proxy` (`id_proxy`),
  KEY `accept` (`accept`),
  CONSTRAINT `fk_bids_auction_id` FOREIGN KEY (`auction_id`) REFERENCES `#__bid_auctions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_auctions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL,
  `shortdescription` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `cat` int(11) DEFAULT NULL,
  `auction_type` int(11) NOT NULL,
  `automatic` tinyint(1) NOT NULL,
  `initial_price` decimal(20,2) NOT NULL,
  `BIN_price` decimal(20,2) DEFAULT NULL,
  `min_increase` decimal(20,2) NOT NULL,
  `reserve_price` decimal(20,2) DEFAULT NULL,
  `currency` varchar(5) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `closed_date` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `close_offer` tinyint(1) NOT NULL DEFAULT '0',
  `close_by_admin` tinyint(1) NOT NULL DEFAULT '0',
  `featured` enum('featured','none') NOT NULL DEFAULT 'none',
  `newmessages` int(1) NOT NULL DEFAULT '0',
  `payment_info` text,
  `shipment_info` text,
  `extended_counter` int(11) NOT NULL DEFAULT '0',
  `nr_items` int(11) NOT NULL DEFAULT '1',
  `quantity` int(11) DEFAULT NULL,
  `auction_nr` varchar(12) NOT NULL,
  `hits` int(11) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `payment_method` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `iAuctionNr` (`auction_nr`),
  KEY `ititle` (`title`),
  KEY `ifeatured` (`featured`),
  KEY `auction_type` (`auction_type`),
  KEY `automatic` (`automatic`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  KEY `published` (`published`),
  KEY `close_offer` (`close_offer`),
  KEY `close_by_admin` (`close_by_admin`),
  KEY `hits` (`hits`),
  KEY `cat` (`cat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL DEFAULT '',
  `simbol` char(3) NOT NULL DEFAULT '',
  `active` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__bid_cronlog` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`priority` VARCHAR(50) NOT NULL,
	`event` VARCHAR(50) NOT NULL,
	`logtime` DATETIME NOT NULL,
	`log` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_currency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  `convert` decimal(15,5) DEFAULT NULL,
  `default` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `db_name` varchar(100) NOT NULL,
  `page` varchar(100) NOT NULL,
  `ftype` varchar(150) NOT NULL,
  `compulsory` tinyint(1) NOT NULL,
  `categoryfilter` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `own_table` varchar(100) NOT NULL,
  `validate_type` varchar(100) NOT NULL,
  `css_class` varchar(100) NOT NULL,
  `style_attr` text NOT NULL,
  `search` tinyint(1) NOT NULL,
  `params` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `help` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_fields_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`),
  KEY `cid` (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_fields_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) NOT NULL,
  `option_name` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_fields_positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldid` int(11) NOT NULL,
  `templatepage` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `ordering` int(11) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fieldid`),
  KEY `templatepage` (`templatepage`),
  KEY `templatepage_position` (`templatepage`,`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_increment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `min_bid` decimal(20,2) NOT NULL,
  `max_bid` decimal(20,2) NOT NULL,
  `value` decimal(20,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  `id_proxy` int(11) NOT NULL DEFAULT '0',
  `initial_bid` DECIMAL(20,2) NOT NULL,
  `bid_price` DECIMAL(20,2) NOT NULL DEFAULT '0',
  `payment` int(11) NOT NULL DEFAULT '0',
  `cancel` int(11) NOT NULL DEFAULT '0',
  `accept` tinyint(4) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_offer` (`auction_id`),
  KEY `userid` (`userid`),
  KEY `bid_price` (`bid_price`),
  KEY `id_proxy` (`id_proxy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_mails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_type` varchar(250) DEFAULT NULL,
  `content` text,
  `subject` varchar(250) DEFAULT NULL,
  `enabled` int(11) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `mailtypes` (`mail_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL DEFAULT '0',
  `userid1` int(11) NOT NULL DEFAULT '0',
  `userid2` int(11) NOT NULL DEFAULT '0',
  `parent_message` int(11) NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wasread` int(1) DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_offer` (`auction_id`),
  KEY `userid` (`userid1`),
  KEY `userid2` (`userid2`),
  CONSTRAINT `fk_message_auctionid` FOREIGN KEY (`auction_id`) REFERENCES `#__bid_auctions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_payment_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `balance` decimal(11,2) DEFAULT NULL,
  `currency` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ixuserid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_payment_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `amount` decimal(9,2) DEFAULT NULL,
  `currency` varchar(11) DEFAULT NULL,
  `refnumber` varchar(100) DEFAULT NULL,
  `invoice` varchar(50) DEFAULT NULL,
  `ipn_response` text,
  `ipn_ip` varchar(100) DEFAULT NULL,
  `status` enum('ok','error','manual_check','cancelled','refunded') DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `orderid` int(11) NOT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ixdate` (`date`),
  KEY `ixuserid` (`userid`),
  KEY `ixstatus` (`status`),
  KEY `ixref` (`refnumber`),
  KEY `ixinvoice` (`invoice`),
  KEY `ixobjectid` (`orderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_payment_orderitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderid` int(11) DEFAULT NULL,
  `itemname` varchar(30) DEFAULT NULL,
  `itemdetails` varchar(250) DEFAULT NULL,
  `iteminfo` varchar(150) DEFAULT NULL,
  `price` decimal(11,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `quantity` int(11) DEFAULT '1',
  `params` text,
  PRIMARY KEY (`id`),
  KEY `ixorderid` (`orderid`),
  KEY `ixiteminfo` (`iteminfo`),
  KEY `ixitemname` (`itemname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_payment_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderdate` datetime DEFAULT NULL,
  `modifydate` datetime DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `order_total` decimal(11,2) DEFAULT NULL,
  `order_currency` varchar(10) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `paylogid` int(11) DEFAULT NULL,
  `params` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_paysystems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paysystem` varchar(50) DEFAULT NULL,
  `classname` varchar(50) DEFAULT NULL,
  `enabled` int(1) DEFAULT '1',
  `params` text,
  `ordering` int(11) DEFAULT NULL,
  `isdefault` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`),
  KEY `ordering` (`ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `picture` varchar(100) NOT NULL,
  `modified` date NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idoffer` (`auction_id`),
  CONSTRAINT `fk_bidpictures_auctionid` FOREIGN KEY (`auction_id`) REFERENCES `#__bid_auctions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itemname` varchar(50) DEFAULT NULL,
  `pricetype` enum('percent','fixed') DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` decimal(9,2) DEFAULT NULL,
  `currency` varchar(11) DEFAULT NULL,
  `enabled` int(1) DEFAULT NULL,
  `params` text,
  `ordering` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ixitemname` (`itemname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_pricing_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` int(11) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `itemname` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `icat` (`category`),
  KEY `ipriceitem` (`itemname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_pricing_comissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `auction_id` int(11) DEFAULT NULL,
  `bid_id` int(11) NOT NULL,
  `comission_date` datetime DEFAULT NULL,
  `amount` decimal(9,2) DEFAULT NULL,
  `currency` varchar(30) DEFAULT NULL,
  `commissionType` enum('seller','buyer') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ixuserid` (`userid`),
  KEY `ixauctionid` (`auction_id`),
  KEY `ixbidis` (`bid_id`),
  KEY `commissionType` (`commissionType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_pricing_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `purchase_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ixuserid` (`userid`),
  KEY `ixcontactid` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_profilefields_assoc` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`field` VARCHAR(50) NULL DEFAULT NULL,
	`assoc_field` VARCHAR(50) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_proxy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `max_proxy_price` varchar(200) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `latest_bid` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idauction` (`auction_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_proxy_auctionid` FOREIGN KEY (`auction_id`) REFERENCES `#__bid_auctions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voter_id` int(11) NOT NULL DEFAULT '0',
  `user_rated_id` int(11) NOT NULL DEFAULT '0',
  `rating` int(11) NOT NULL DEFAULT '0',
  `modified` date NOT NULL DEFAULT '0000-00-00',
  `review` text NOT NULL,
  `auction_id` int(11) NOT NULL DEFAULT '0',
  `rate_type` enum('auctioneer','bidder') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userrated` (`user_rated_id`),
  KEY `auctionid` (`auction_id`),
  KEY `ratetype` (`rate_type`),
  KEY `voter` (`voter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_report_auctions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `message` varchar(200) NOT NULL,
  `solved` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_offer` (`auction_id`),
  KEY `userid` (`userid`),
  CONSTRAINT `fk_reported_auctionid` FOREIGN KEY (`auction_id`) REFERENCES `#__bid_auctions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_shipment_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone` int(11) NOT NULL DEFAULT '0',
  `price` decimal(20,2) NOT NULL,
  `auction` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `zone` (`zone`),
  KEY `auction` (`auction`),
  KEY `price` (`price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_shipment_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_suggestions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `bid_price` decimal(20,2) NOT NULL,
  `modified` datetime NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `status` tinyint(2) NOT NULL COMMENT '0 rejected, 1 accepted, 2 pending',
  `parent_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `auctionid` (`auction_id`),
  KEY `replyto` (`parent_id`),
  CONSTRAINT `fk_bidsuggestions_auctionid` FOREIGN KEY (`auction_id`) REFERENCES `#__bid_auctions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `tagname` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `iauction` (`auction_id`),
  KEY `itagname` (`tagname`),
  CONSTRAINT `fk_tags_auctionid` FOREIGN KEY (`auction_id`) REFERENCES `#__bid_auctions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `surname` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(150) NOT NULL DEFAULT '',
  `city` varchar(50) NOT NULL DEFAULT '',
  `country` varchar(150) NOT NULL DEFAULT '',
  `phone` text NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `verified` int(1) DEFAULT '0',
  `isBidder` int(1) DEFAULT '0',
  `isSeller` int(1) DEFAULT '0',
  `powerseller` int(1) DEFAULT '0',
  `paypalemail` text,
  `agree_tc` INT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `ixopts` (`verified`,`isBidder`,`isSeller`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_user_settings` (
  `userid` int(11) NOT NULL,
  `settings` text NOT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_watchlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `iuserid` (`userid`),
  KEY `iauctionid` (`auction_id`),
  CONSTRAINT `fk_watchlist_auctionid` FOREIGN KEY (`auction_id`) REFERENCES `#__bid_auctions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__bid_watchlist_cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catid` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `catid_userid` (`catid`,`userid`),
  KEY `catid` (`catid`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS=1;