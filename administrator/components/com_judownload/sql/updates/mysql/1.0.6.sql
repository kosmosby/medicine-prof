ALTER TABLE `#__judownload_plugins` 
	ADD COLUMN `extension_id` int(11) unsigned   NOT NULL after `params` , 
	ADD KEY `idx_extension_id`(`extension_id`) ;