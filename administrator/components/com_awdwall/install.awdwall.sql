CREATE TABLE IF NOT EXISTS `#__awd_connection` (
  `connection_id` int(11) NOT NULL AUTO_INCREMENT,
  `connect_from` int(11) NOT NULL,
  `connect_to` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `pending` tinyint(4) DEFAULT NULL,
  `msg` text,
  `created` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`connection_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_wall`
--

CREATE TABLE IF NOT EXISTS `#__awd_wall` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `type` varchar(20) DEFAULT 'text',
  `commenter_id` bigint(20) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'nophoto_n.png',
  `message` text,
  `reply` bigint(20) DEFAULT '0',
  `is_read` tinyint(1) DEFAULT '0',
  `is_pm` tinyint(4) DEFAULT '0',
  `is_reply` tinyint(4) DEFAULT '0',
  `posted_id` int(11) DEFAULT NULL,
  `wall_date` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_wall_comment_like`
--

CREATE TABLE IF NOT EXISTS `#__awd_wall_comment_like` (
  `wall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_wall_files`
--

CREATE TABLE IF NOT EXISTS `#__awd_wall_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wall_id` int(11) NOT NULL,
  `title` varchar(250) DEFAULT NULL,
  `path` varchar(250) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_wall_images`
--

CREATE TABLE IF NOT EXISTS `#__awd_wall_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wall_id` int(11) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `path` varchar(250) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_wall_links`
--

CREATE TABLE IF NOT EXISTS `#__awd_wall_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wall_id` int(11) NOT NULL,
  `title` varchar(250) DEFAULT NULL,
  `link` varchar(300) DEFAULT NULL,
  `link_img` TEXT DEFAULT NULL,
  `path` varchar(250) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_wall_mp3s`
--

CREATE TABLE IF NOT EXISTS `#__awd_wall_mp3s` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wall_id` int(11) NOT NULL,
  `title` varchar(250) DEFAULT NULL,
  `path` varchar(250) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_wall_users`
--
CREATE TABLE IF NOT EXISTS `#__awd_wall_users` (
  `user_id` int(11) NOT NULL,
  `avatar` varchar(250) DEFAULT NULL,
  `gender` tinyint(1) DEFAULT NULL,
  `birthday` varchar(30) DEFAULT NULL,
  `aboutme` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_wall_videos`
--

CREATE TABLE IF NOT EXISTS `#__awd_wall_videos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wall_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `type` varchar(200) NOT NULL DEFAULT 'file',
  `video_id` varchar(200) DEFAULT NULL,
  `description` text NOT NULL,
  `creator` int(11) unsigned NOT NULL,
  `creator_type` varchar(200) NOT NULL DEFAULT 'user',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `featured` tinyint(3) NOT NULL DEFAULT '0',
  `duration` float unsigned DEFAULT '0',
  `status` varchar(200) NOT NULL DEFAULT 'pending',
  `thumb` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `groupid` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `creator` (`creator`),
  KEY `idx_groupid` (`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `#__awd_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `description` text,
  `privacy` tinyint(4) NOT NULL DEFAULT '1',
  `image` varchar(150) DEFAULT NULL,
  `created_date` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `#__awd_groups_members` (
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `created_date` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`group_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__awd_wall_jing` (
  `id` int(11) NOT NULL auto_increment,
  `wall_id` int(11) NOT NULL,
  `jing_title` varchar(250) default NULL,
  `jing_link` varchar(300) default NULL,
  `jing_description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `#__awd_wall_videos_hwd` (
  `wall_id` bigint(20) NOT NULL,
  `hwdviodeo_id` bigint(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS  `#__awd_wall_events` (
  `id` int(11) NOT NULL auto_increment,
  `wall_id` int(11) NOT NULL,
  `title` varchar(222) NOT NULL,
  `location` varchar(222) NOT NULL,
  `start_time` varchar(222) NOT NULL,
  `end_time` varchar(222) NOT NULL,
  `description` varchar(222) NOT NULL,
  `image` varchar(222) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS  `#__awd_wall_event_attend` (
  `wall_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__awd_wall_trail` (
  `id` int(11) NOT NULL auto_increment,
  `wall_id` int(11) NOT NULL,
  `trail_title` varchar(250) default NULL,
  `trail_link` varchar(250) default NULL,
  `trail_path` varchar(250) default NULL,
  `trail_description` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `#__awd_wall_article` (
  `id` int(11) NOT NULL auto_increment,
  `wall_id` int(11) NOT NULL,
  `title` varchar(222) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(222) NOT NULL,
  `article_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `#__awd_wall_privacy` (
  `wall_id` int(11) NOT NULL,
  `privacy` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__awd_wall_bdayreminder` (
  `user_id` int(11) NOT NULL,
  `bday_user` int(11) NOT NULL,
  `read_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__awd_wall_notification` (
  `nid` bigint(20) NOT NULL auto_increment,
  `ndate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `nuser` bigint(20) NOT NULL,
  `ncreator` bigint(20) NOT NULL,
  `ntype` varchar(25) NOT NULL,
  `nwallid` bigint(20) NOT NULL,
  `ngroupid` bigint(20) NOT NULL default '0',
  `nphotoid` bigint(20) NOT NULL default '0',
  `nalbumid` bigint(20) NOT NULL default '0',
  `nread` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`nid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__awd_wall_tweets` (
  `tweet_id` int(11) NOT NULL auto_increment,
  `wall_id` int(11) NOT NULL,
  PRIMARY KEY  (`tweet_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__awd_wall_content_comment_like` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL,
  `commentid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__awd_wall_content_comments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `content_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `submitted` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__awd_wall_social_feeds` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `wallid` bigint(20) NOT NULL,
  `type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

UPDATE #__menu SET params = '{\"color1\":\"FFFFFF\",\"color2\":\"5574A0\",\"color3\":\"333333\",\"color4\":\"8C8C8C\",\"color5\":\"E8E8E8\",\"color6\":\"5574A0\",\"color7\":\"E7EBEE\",\"color8\":\"308CB6\",\"color9\":\"D1E8EA\",\"color10\":\"FFFFFF\",\"color11\":\"475875\",\"color12\":\"FFFFFF\",\"color13\":\"B0C3C5\",\"color14\":\"FFFFFF\"}' WHERE link = 'index.php?option=com_awdwall&controller=colors';

UPDATE #__extensions SET params = '{\"temp\":\"default\",\"width\":\"750\",\"email_auto\":\"0\",\"video_lightbox\":\"1\",\"image_lightbox\":\"1\",\"display_name\":\"1\",\"nof_post\":\"15\",\"nof_comment\":\"3\",\"bg_color\":\"#FFFFFF\",\"image_ext\":\"gif,png,jpg,jpge\",\"file_ext\":\"doc,docx,pdf,xls,txt\",\"privacy\":\"0\",\"nof_friends\":\"4\",\"display_online\":\"1\",\"seo_format\":\"0\",\"display_video\":\"1\",\"display_image\":\"1\",\"display_music\":\"1\",\"display_link\":\"1\",\"display_file\":\"1\",\"display_trail\":\"1\",\"dt_format\":\"g:i A l, j-M-y\",\"nof_groups\":\"1\",\"nof_invite_members\":\"10\"}' WHERE element = 'com_awdwall' and type='component';