CREATE TABLE `sensitive_words_log`
(
    `id`           int                                                    NOT NULL AUTO_INCREMENT,
    `word`         varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '关键字',
    `word_id`      int                                                    NOT NULL COMMENT '关键字id',
    `status`       tinyint(1) NOT NULL DEFAULT '1',
    `sort`         int                                                    NOT NULL DEFAULT '0' COMMENT '排序',
    `product_id`   int                                                    NOT NULL DEFAULT '0' COMMENT '报告id',
    `product_name` varchar(255) COLLATE utf8mb4_bin                                DEFAULT NULL COMMENT '报告昵称',
    `product_url`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin          DEFAULT NULL COMMENT '报告url',
    `updated_at`   int                                                             DEFAULT NULL COMMENT '更新时间',
    `created_at`   int                                                    NOT NULL COMMENT '创建时间',
    `updated_by`   int                                                             DEFAULT NULL COMMENT '更新者',
    `created_by`   int                                                    NOT NULL COMMENT '创建者',
    PRIMARY KEY (`id`) USING BTREE,
    KEY            `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;
