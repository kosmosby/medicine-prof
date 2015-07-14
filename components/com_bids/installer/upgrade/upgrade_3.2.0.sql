ALTER TABLE `#__bid_users`
	ADD COLUMN `agree_tc` INT(1) NOT NULL DEFAULT '0' AFTER `paypalemail`;