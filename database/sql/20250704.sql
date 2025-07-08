ALTER TABLE `contact_us`
    ADD COLUMN `ua_info` text AFTER `status`,
ADD COLUMN `referer` varchar(255) NULL AFTER `ua_info`,
ADD COLUMN `referer_alias_id` int NULL AFTER `referer`,
ADD COLUMN `ua_browser_name` varchar(255) NULL AFTER `referer_alias_id`;


ALTER TABLE `coupons`
    ADD COLUMN `price_edition_values` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT '' COMMENT '价格版本列表' AFTER `time_end`;
