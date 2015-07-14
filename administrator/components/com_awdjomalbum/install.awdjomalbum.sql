--
-- Table structure for table `#__awd_jomalbum`
--

CREATE TABLE IF NOT EXISTS `#__awd_jomalbum` (
  `id` bigint(20) NOT NULL auto_increment,
  `userid` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `descr` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `privacy` bigint(20) NOT NULL default '0',
  `published` tinyint(4) NOT NULL default '1',
  `created_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_jomalbum_comment`
--

CREATE TABLE IF NOT EXISTS `#__awd_jomalbum_comment` (
  `id` bigint(20) NOT NULL auto_increment,
  `photoid` bigint(20) NOT NULL,
  `comments` text NOT NULL,
  `cdate` varchar(50) NOT NULL,
  `userid` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `#__awd_jomalbum_comment_like`
--

CREATE TABLE IF NOT EXISTS `#__awd_jomalbum_comment_like` (
  `id` bigint(20) NOT NULL auto_increment,
  `commentid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_jomalbum_photos`
--

CREATE TABLE IF NOT EXISTS `#__awd_jomalbum_photos` (
  `id` bigint(20) NOT NULL auto_increment,
  `userid` bigint(20) NOT NULL,
  `albumid` bigint(20) NOT NULL,
  `image_name` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `published` tinyint(4) NOT NULL default '1',
  `upload_date` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_jomalbum_photo_like`
--

CREATE TABLE IF NOT EXISTS `#__awd_jomalbum_photo_like` (
  `id` bigint(20) NOT NULL auto_increment,
  `photoid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_jomalbum_photo_wall_like`
--

CREATE TABLE IF NOT EXISTS `#__awd_jomalbum_photo_wall_like` (
  `id` bigint(20) NOT NULL auto_increment,
  `photoid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_jomalbum_tags`
--

CREATE TABLE IF NOT EXISTS `#__awd_jomalbum_tags` (
  `id` bigint(20) NOT NULL auto_increment,
  `photoid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `taguserid` bigint(20) NOT NULL,
  `tagValue` text NOT NULL,
  `targetX` text NOT NULL,
  `targetY` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_jomalbum_userinfo`
--

CREATE TABLE IF NOT EXISTS `#__awd_jomalbum_userinfo` (
  `id` bigint(20) NOT NULL auto_increment,
  `userid` bigint(20) NOT NULL,
  `currentcity` varchar(100) NOT NULL,
  `display_currentcity` int(11) DEFAULT NULL,
  `hometown` varchar(100) NOT NULL,
  `display_hometown` int(11) DEFAULT NULL,
  `languages` varchar(500) NOT NULL,
  `display_languages` int(11) DEFAULT NULL,
  `birthday` date NOT NULL,
  `display_birthday` int(11) DEFAULT NULL,
  `aboutme` text NOT NULL,
  `display_aboutme` int(11) DEFAULT NULL,
  `skype_user` varchar(222) NOT NULL,
  `display_skype_user` int(11) DEFAULT NULL,
  `facebook_user` varchar(222) NOT NULL,
  `display_facebook_user` int(11) DEFAULT NULL,
  `twitter_user` varchar(222) NOT NULL,
  `display_twitter_user` int(11) DEFAULT NULL,
  `youtube_user` varchar(222) NOT NULL,
  `display_youtube_user` int(11) DEFAULT NULL,
  `display_twitter_post` int(11) DEFAULT NULL,
  `twitter_privacy` int(11) DEFAULT NULL,
  `latest_tweet_id` varchar(222) NOT NULL,
  `col1` varchar(222) NOT NULL,
  `display_col1` int(11) DEFAULT NULL,
  `col2` varchar(222) NOT NULL,
  `display_col2` int(11) DEFAULT NULL,
  `col3` varchar(222) NOT NULL,
  `display_col3` int(11) DEFAULT NULL,
  `col4` varchar(222) NOT NULL,
  `display_col4` int(11) DEFAULT NULL,
  `col5` varchar(222) NOT NULL,
  `display_col5` int(11) DEFAULT NULL,
  `workingat` varchar(100) NOT NULL,
  `display_workingat` tinyint(4) NOT NULL DEFAULT '1',
  `studied` varchar(100) NOT NULL,
  `display_studied` tinyint(4) NOT NULL DEFAULT '1',
  `livein` varchar(100) NOT NULL,
  `display_livein` tinyint(4) NOT NULL DEFAULT '1',
  `phone` varchar(50) NOT NULL,
  `display_phone` tinyint(4) NOT NULL DEFAULT '1',
  `cell` varchar(50) NOT NULL,
  `display_cell` tinyint(4) NOT NULL DEFAULT '1',
  `maritalstatus` varchar(20) NOT NULL,
  `display_maritalstatus` tinyint(4) NOT NULL DEFAULT '1',
  `userhighlightfields` varchar(200) NOT NULL,
  `hide_birthyear` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `#__awd_jomalbum_wall_comment`
--

CREATE TABLE IF NOT EXISTS `#__awd_jomalbum_wall_comment` (
  `id` bigint(20) NOT NULL auto_increment,
  `photoid` bigint(20) NOT NULL,
  `comments` text NOT NULL,
  `cdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `userid` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_jomalbum_wall_comment_like`
--

CREATE TABLE IF NOT EXISTS `#__awd_jomalbum_wall_comment_like` (
  `id` bigint(20) NOT NULL auto_increment,
  `commentid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__awd_jomalbum_wall_tags`
--

CREATE TABLE IF NOT EXISTS `#__awd_jomalbum_wall_tags` (
  `id` bigint(20) NOT NULL auto_increment,
  `photoid` bigint(20) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `taguserid` bigint(20) NOT NULL,
  `tagValue` text NOT NULL,
  `targetX` text NOT NULL,
  `targetY` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `#__awd_jomalbum_info_ques` (
  `id` int(11) NOT NULL auto_increment,
  `colname` varchar(222) NOT NULL,
  `value` varchar(222) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `#__awd_jomalbum_info_ques`
--


