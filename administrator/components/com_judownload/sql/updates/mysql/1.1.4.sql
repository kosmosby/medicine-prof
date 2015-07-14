ALTER TABLE `#__judownload_categories`   
  CHANGE `show_item` `show_item` TINYINT(3) DEFAULT 1  NOT NULL;

UPDATE
  `#__judownload_categories`
SET
  show_item = 1
WHERE show_item = - 1
  OR show_item = - 2;

ALTER TABLE `#__judownload_categories`   
  DROP COLUMN `layout`, 
  DROP COLUMN `layout_document`;

ALTER TABLE `#__judownload_documents`   
  DROP COLUMN `layout`;