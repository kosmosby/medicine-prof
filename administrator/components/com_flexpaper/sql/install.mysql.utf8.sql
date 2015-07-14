CREATE TABLE IF NOT EXISTS `#__flexpaper` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `catid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=155 ;

-- --------------------------------------------------------

--
-- Table structure for table `ju5ej_flexpaper_bundle`
--

CREATE TABLE IF NOT EXISTS `#__flexpaper_bundle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bundle_id` int(11) NOT NULL,
  `membership_list_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=73 ;

-- --------------------------------------------------------

--
-- Table structure for table `ju5ej_flexpaper_category`
--

CREATE TABLE IF NOT EXISTS `#__flexpaper_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catid` int(11) NOT NULL,
  `membership_list_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `catid` (`catid`,`membership_list_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=43 ;

-- --------------------------------------------------------

--
-- Table structure for table `ju5ej_flexpaper_certificate`
--

CREATE TABLE IF NOT EXISTS `#__flexpaper_certificate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cert_id` varchar(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cert_id` (`cert_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=583 ;

-- --------------------------------------------------------

--
-- Table structure for table `ju5ej_flexpaper_content`
--

CREATE TABLE IF NOT EXISTS `#__flexpaper_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `membership_list_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `show_module` tinyint(4) NOT NULL,
  `catid` int(11) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `membership_list_id` (`membership_list_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `ju5ej_flexpaper_quiz`
--

CREATE TABLE IF NOT EXISTS `#__flexpaper_quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `membership_list_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `test_id` (`test_id`,`membership_list_id`),
  UNIQUE KEY `membership_list_id` (`membership_list_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=88 ;

-- --------------------------------------------------------

--
-- Table structure for table `ju5ej_flexpaper_quiz_results`
--

CREATE TABLE IF NOT EXISTS `#__flexpaper_quiz_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` varchar(255) NOT NULL,
  `correct_answer` varchar(255) NOT NULL,
  `time` datetime NOT NULL,
  `question_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=201 ;

-- --------------------------------------------------------

--
-- Table structure for table `ju5ej_lms_questions`
--

CREATE TABLE IF NOT EXISTS `#__lms_questions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `testid` int(10) NOT NULL,
  `qtype` enum('yn','mc2','mc3','mc4','mc5','mc6','sa') NOT NULL DEFAULT 'yn',
  `question` text NOT NULL,
  `q_image` varchar(255) NOT NULL DEFAULT '',
  `a0` varchar(255) NOT NULL DEFAULT '',
  `a1` varchar(255) NOT NULL DEFAULT '',
  `a2` varchar(255) NOT NULL DEFAULT '',
  `a3` varchar(255) NOT NULL DEFAULT '',
  `a4` varchar(255) NOT NULL DEFAULT '',
  `a5` varchar(255) NOT NULL DEFAULT '',
  `img0` varchar(255) NOT NULL DEFAULT '',
  `img1` varchar(255) NOT NULL DEFAULT '',
  `img2` varchar(255) NOT NULL DEFAULT '',
  `img3` varchar(255) NOT NULL DEFAULT '',
  `img4` varchar(255) NOT NULL DEFAULT '',
  `img5` varchar(255) NOT NULL DEFAULT '',
  `answer` enum('1','2','3','4','5') NOT NULL,
  `pagebreak` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` datetime DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `testid` (`testid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=122 ;
-- --------------------------------------------------------

--
-- Table structure for table `ju5ej_lms_results`
--

CREATE TABLE IF NOT EXISTS `#__lms_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL,
  `testid` int(10) NOT NULL,
  `passed` tinyint(1) NOT NULL DEFAULT '0',
  `score` varchar(255) NOT NULL DEFAULT '',
  `fails` int(10) NOT NULL DEFAULT '0',
  `date_created` datetime DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  `params` text NOT NULL,
  `paid` tinyint(1) unsigned NOT NULL,
  `tid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`,`tid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Table structure for table `ju5ej_lms_tests`
--


CREATE TABLE IF NOT EXISTS `#__lms_tests` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `video` varchar(255) NOT NULL DEFAULT '',
  `passmark` int(11) unsigned NOT NULL DEFAULT '80',
  `date_created` datetime DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `catid` int(3) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '9999',
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  `params` text NOT NULL,
  `courseExpire` int(10) unsigned NOT NULL,
  `costOfCourse` int(10) unsigned NOT NULL,
  `courseDescription` varchar(250) DEFAULT NULL,
  `prereqCourses` varchar(250) NOT NULL,
  `vmProductName` varchar(45) NOT NULL,
  `vmProductID` int(10) NOT NULL,
  `vmCurrency` varchar(3) NOT NULL,
  `vmURL` varchar(150) NOT NULL,
  `vmPayment` varchar(45) NOT NULL,
  `paypalEmail` varchar(45) NOT NULL,
  `customPS` varchar(250) NOT NULL,
  `courseDescription2` text NOT NULL,
  `attempts_limit` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

