ALTER TABLE `sync_field`
    ADD COLUMN `substitute_name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '对方网站的字段-替补字段' AFTER `name`;

