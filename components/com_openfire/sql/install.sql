CREATE TABLE IF NOT EXISTS #__openfire_phones
(
	`id` int(10) NOT NULL AUTO_INCREMENT,
    `phone` varchar(10) NOT NULL,
    `code` varchar(4) NOT NULL,
    `verified` tinyint default 0,
    `ip_addr` varchar(50) NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;