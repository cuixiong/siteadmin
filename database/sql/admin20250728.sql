ALTER TABLE `template`
    ADD COLUMN `is_auto_post` tinyint(1) DEFAULT 0 COMMENT '类型:是否自动发帖' AFTER `type`;


