ALTER TABLE `post_subject_log`
    ADD COLUMN `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件' AFTER `type`;
