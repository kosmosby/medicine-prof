-- Licensed to the Apache Software Foundation (ASF) under one
-- or more contributor license agreements.  See the NOTICE file
-- distributed with this work for additional information
-- regarding copyright ownership.  The ASF licenses this file
-- to you under the Apache License, Version 2.0 (the
-- "License") +  you may not use this file except in compliance
-- with the License.  You may obtain a copy of the License at
-- 
--   http://www.apache.org/licenses/LICENSE-2.0
-- 
-- Unless required by applicable law or agreed to in writing,
-- software distributed under the License is distributed on an
-- "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
-- KIND, either express or implied.  See the License for the
-- specific language governing permissions and limitations
-- under the License.

CREATE TABLE `#__om_rooms` (
  `id` int(10) unsigned NOT NULL auto_increment,  
  `name` varchar(100) NOT NULL,
  `room_id` int(10) NOT NULL,
  `owner` int(10) NOT NULL,
  `roomtype_id` int(10) NOT NULL,  
  `comment` varchar(100) NOT NULL,
  `number_of_participants` int(10) NULL,
  `is_public` tinyint(3) NOT NULL default '0',
  `appointment` tinyint(3) NOT NULL default '0',
  `is_moderated_room` tinyint(3) NOT NULL default '1',
  
  `room_validity` int(10) NOT NULL default '0',
  `date_type` date NOT NULL default 0,
  `time_type` time NOT NULL default 0,
  `duration` int(10) NOT NULL default '0',
  `repeat_type` tinyint(3) NOT NULL default '0',
  `weekday_type` int(10) NOT NULL default '0',
  
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Openmeetings: Conference Rooms' AUTO_INCREMENT=5 ;

CREATE TABLE IF NOT EXISTS `#__om_rooms_users` (
  `om_room_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,

  UNIQUE KEY `idx_room_id_user_id` (`om_room_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
