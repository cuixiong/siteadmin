ALTER TABLE `orders`
    ADD COLUMN `department` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '部门' AFTER `company`;

ALTER TABLE `users`
    ADD COLUMN `department` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '部门' AFTER `company`;


ALTER TABLE `auto_post_config`
    ADD COLUMN `type` tinyint(1) DEFAULT NULL COMMENT '站内站外' AFTER `name`;


ALTER TABLE `contact_us`
    ADD COLUMN `department` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '部门' AFTER `company`,
    ADD COLUMN `price_edition` int(14) DEFAULT NULL COMMENT '价格版本' AFTER `language_version`;

