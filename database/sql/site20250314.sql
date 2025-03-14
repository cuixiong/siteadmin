
ALTER TABLE `post_subject`
    ADD COLUMN `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '关键词' AFTER `analyst`;

ALTER TABLE `post_subject`
    ADD COLUMN `has_cagr` int  COMMENT '是否有cagr数据' AFTER `change_status`;