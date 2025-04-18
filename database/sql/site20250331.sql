ALTER TABLE `system_values`
    ADD COLUMN `back_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  DEFAULT '' COMMENT '备用类型' AFTER `type`,
ADD COLUMN `back_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin   COMMENT '备用内容' AFTER `back_type`;


ALTER TABLE `authorities`
    ADD COLUMN `hits` int NULL DEFAULT 0 COMMENT '点击次数' AFTER `sort`,
ADD COLUMN `real_hits` int NULL DEFAULT 0 COMMENT '真实点击' AFTER `hits`;

ALTER TABLE `authorities`
    ADD COLUMN `type` tinyint(1) NULL COMMENT '类型' AFTER `real_hits`;


