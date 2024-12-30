ALTER TABLE `yadmin`.`systems`
    ADD COLUMN `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '别名' AFTER `english_name`;

ALTER TABLE `yadmin`.`system_values`
    ADD COLUMN `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '别名' AFTER `english_name`;
