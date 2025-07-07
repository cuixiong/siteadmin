ALTER TABLE `post_subject_strategy`
    ADD COLUMN `version` VARCHAR(255) NULL COMMENT '版本筛选' AFTER `category_ids`;


