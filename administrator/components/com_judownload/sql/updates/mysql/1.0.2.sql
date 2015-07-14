ALTER TABLE `#__judownload_logs`   
  CHANGE `system` `platform` VARCHAR(255) CHARSET utf8 COLLATE utf8_general_ci NOT NULL,
  ADD COLUMN `user_agent` VARCHAR(512) NOT NULL AFTER `platform`;