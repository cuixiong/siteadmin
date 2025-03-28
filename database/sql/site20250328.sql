
ALTER TABLE `product_routine`
    ADD COLUMN `keywords_cn` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '关键词(中)' AFTER `keywords`,
    ADD COLUMN `keywords_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '关键词(英)' AFTER `keywords_cn`,
    ADD COLUMN `keywords_jp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '关键词(日)' AFTER `keywords_en`,
    ADD COLUMN `keywords_kr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '关键词(韩)' AFTER `keywords_jp`,
    ADD COLUMN `keywords_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '关键词(德)' AFTER `keywords_kr`;


