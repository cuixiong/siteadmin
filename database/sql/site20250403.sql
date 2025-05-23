ALTER TABLE `team_members`
    ADD COLUMN `show_product` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否首页显示' AFTER `sort`,
ADD COLUMN `region_id` int NOT NULL DEFAULT 0 COMMENT '区域id' AFTER `show_product`,
ADD COLUMN `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '区域小图标' AFTER `region_id`,
ADD COLUMN `national_flag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '国旗' AFTER `icon`,
ADD COLUMN `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '语言' AFTER `national_flag`,
ADD COLUMN `img2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '图片2' AFTER `language`,
ADD COLUMN `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '电话' AFTER `img2`;

ALTER TABLE `team_members`
    ADD COLUMN `region_name` varchar(255) NULL COMMENT '区域昵称' AFTER `region_id`;




ALTER TABLE `offices`
    ADD COLUMN `abbreviation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT '' COMMENT '办公室简称' AFTER `language_alias`;


ALTER TABLE `comments`
    ADD COLUMN `country` varchar(255) NULL COMMENT '国家' AFTER `post`;

ALTER TABLE `offices`
    ADD COLUMN `country_id` int NULL COMMENT '国家id' AFTER `area`;
